<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ngannv
 * Date: 9/13/12
 * Time: 2:26 PM
 * To change this template use File | Settings | File Templates.
 */
require_once ROOT_PATH . "includes/sohapay/class_payment.php";
class ebPayment
{
    private $verify_url = FALSE;
    private static  $instance = FALSE;

    public function __construct()
    {
       self::$instance =& $this;
    }
    public static function getInstance(){
        if(!self::$instance){
            new ebPayment();
        }
        return self::$instance;
    }

    function ebBuyProcess()
    {

        $pg               = new PG_checkout();
        $check            = $pg->verifyReturnUrl();
        $this->verify_url = $check;
        if (!$this->verify_url) {
            return FALSE;
        }
        $payment_time = strtotime(Url::get('payment_time'));
        $payment_type = (int)Url::get('payment_type');
        $error_text   = Url::get('error_text', '');
        $payEmail     = Url::get('cust_email');
        $order_id     = Url::get('order_id');
        $order_update = $order_id;
        global $display;
        if (isset($_SESSION['curUserSaleId'])) {
            $title = "Không tìm thấy giao dịch hoặc giao dịch đã được thực hiện";
            if ($error_text == '') {
                $title = "THANH TOÁN THÀNH CÔNG";
                //Gửi sms đến user thanh toán online thành công
                if (!isset($_SESSION['cartUserBuyInfo']['phone'])) {
                    $_SESSION['cartUserBuyInfo']['phone'] = $_SESSION['cartUserBuyInfo']['pay_cod_phone'];
                }
                $_SESSION['inforUserPayship'] = $_SESSION['cartUserBuyInfo'];
                $user_inf                     = $_SESSION['inforUserPayship'];
                $phone                        = $user_inf['phone'];

                if (($payEmail && isset($_SESSION['curUserSaleId']))) {
                    $userSale  = User::getUser($_SESSION['curUserSaleId']);
                    $emailSale = $userSale['email'];
                    if (EBP_MERCHANT_SITE_CODE == 'test') //For test
                    {
                        // $test['phone'] = TEST_SMS;
                        $emailSale = TEST_EMAIL;
                    }
                    $display->add('eb_url', WEB_ROOT);
                    $emailBuy = $_SESSION['cartUserBuyInfo']['pay_cod_email']; /*EMAIL CỦA NGƯỜI MUA*/
                    /*GỬI MAIL CHO THẰNG MUA*/
                    if (isset($_SESSION['ebCart'][$_SESSION['curUserSaleId']])) {
                        /*Giỏ hàng mua của thằng bán có ID = $_SESSION['curUserSaleId']*/
                        $listCart = $_SESSION['ebCart'][$_SESSION['curUserSaleId']];
                        $display->add('inforUser', $_SESSION['cartUserBuyInfo']);
                        $tplVar['mail_type'] = 1;
                        $display->add('eb_url', WEB_ROOT);
                        $display->add('create_date', date("d-m-Y", TIME_NOW));
                        $display->add('pay_type', 'Thanh toán online dùng Visa, Master Card, thẻ ATM, tài khoản có Internet Banking');
                        $display->add('listCart', $listCart);
                        $date_time = date('m', TIME_NOW);
                        $display->add('order_id', "pay_$date_time" . "_" . $order_id);
                        $display->add('user_name', $_SESSION['cartUserBuyInfo']['pay_cod_name']);
                        $content_email = $display->getTemplateAjax('email_alert', $tplVar, TRUE, 'eb_buy/');
                        System::sendEBEmail($emailBuy, "[ÉnBạc]Bạn đã hoàn thành việc đặt hàng trên ÉnBạc ", $content_email);
                        /*END GUI MAIL CHO NGUOI MUA*/

                        /*GUI MAIL CHO NGUOI BAN*/
                        $display->add('mail_type', "owner_shop");
                        $display->add('user_name', $userSale['full_name']);
                        $content_email = $display->getTemplateAjax('email_alert', FALSE, TRUE, 'eb_buy/');
                        System::sendEBEmail($emailSale, "[ÉnBạc] Bạn có 1 đơn hàng mới trong shop ", $content_email);
                    }
                }
                if ((EnBacLib::check_mobile($phone) && isset($_SESSION['curUserSaleId']))) {
                    require_once(ROOT_PATH . 'includes/nusoap/nusoap.php');
                    $wsdl         = 'http://222.255.8.122:8888/wsbr/wsdl/APIBR.wsdl';
                    $wsUser       = 'hungnn';
                    $wsPassword   = 'hungnn';
                    $command_code = 'EB';
                    $content      = 'Enbac';
                    $content .= 'da nhan duoc don hang ' . $order_id . ' cua ban. He thong se xu ly va nhan tin thong bao tiep cho ban. Neu ban can them thong tin, vui long goi: 0969819171';
                    $soapclient = new SoapClient($wsdl);

                    try {
                        $res = $soapclient->sendTextBR(array(
                            'phone_number' => $phone,
                            'command_code' => $command_code,
                            'info'         => $content,
                            'user'         => $wsUser,
                            'pass'         => $wsPassword
                        ));

                        $arr = (array)$res;
                        eDB::switchDB(1); //Connect DB pns
                        $pnsLogSms             = array();
                        $pnsLogSms['order_id'] = $order_id;
                        $pnsLogSms['date']     = TIME_NOW;
                        if (isset($arr['sendTextBRReturn']) && $arr['sendTextBRReturn']) {
                            $pnsLogSms['status'] = 1;
                        } else {
                            $pnsLogSms['status'] = 2;
                        }
                        $pnsLogSms['note']      = $content;
                        $pnsLogSms['user_name'] = 'System';
                        $pnsLogSms['phone']     = $phone;
                        eDB::insert('log_sms', $pnsLogSms, basename(__FILE__), ':' . __LINE__);
                        eDB::close();


                    } catch (Exception $e) {
                        if (DEBUG) {
                            echo $e->getTraceAsString() . '<br />';
                            echo $e->getMessage();
                        }
                    }
                }
                //End gửi sms



                if ($order_update) {
                    eDB::switchDB(1); //Connect DB pns
                    $str_paytype  = "";
                    if ($payment_type) {
                        $str_paytype = "payment_type = $payment_type, ";
                    }
                    $sql = "UPDATE orders SET $str_paytype status = 1, pay_status = 2, send_mail = 2 WHERE id = $order_update";
                    eDB::runQuery($sql, basename(__FILE__), ':' . __LINE__);
                }

                if (isset($_SESSION['curUserSaleId'])) {
                    $curUserSaleId = $_SESSION['curUserSaleId'];
                    if (isset($_SESSION['ebCart']) && isset($_SESSION['ebCart'][$curUserSaleId])) {
                        unset($_SESSION['ebCart'][$curUserSaleId]);
                    }
                    unset($_SESSION['curUserSaleId']);
                }
                unset($_SESSION['numberListCart']);
            } else {
                if (Url::get('order_id')) {
                    eDB::switchDB(1); //Connect DB pns
                    $order_update = Url::get('order_id');
                    $str_paytype  = "";
                    if ($payment_type) {
                        $str_paytype = "payment_type = $payment_type, ";
                    }
                    $sql = "UPDATE orders SET $str_paytype error_text = '" . $error_text . "' WHERE id = $order_update";
                    eDB::runQuery($sql, basename(__FILE__), ':' . __LINE__);
                    eDB::close();
                }
            }
        }

    }
}
