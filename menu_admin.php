<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ngannv
 * Date: 1/13/12
 * Time: 12:24 AM
 * To change this template use File | Settings | File Templates.
 */

/*Menu trang quản trị hiển thị ở 2 vị trí

: menu header
: Dashboard
*/
class menu_admin
{
    public $listMenu = false;

    public function getMenu($region = 'global')
    {
        if(isset($_COOKIE['page_img_id'])){ // luu trang cuoi cung admin xem trong quan ly anh
			$page_img = "&page_no=".$_COOKIE['page_img_id'];
		}
		else{
			$page_img = "";
		}
          
        $admin_link_list=array();
        
		if(User::is_root())
        {
			$admin_link_list['Phân vùng 1'][]=array(
									'title'	=>'Quản trị NCC',
									'des'	=>'Quản trị danh sách các nhà cung cấp',
									'url'	=>WEB_DIR.EBRewrite::formatUrl('?page=manage_shop?cmd=list_shop'),
									'class_icon'	=>'icon_shop_alibaba'
									);
			$admin_link_list['Phân vùng 1'][]=array(
									'title'	=>'Quản trị slide',
									'des'	=>'Quản trị slide trang chủ',
									'url'	=>WEB_DIR.EBRewrite::formatUrl('?page=manage_slide?cmd=listSlide'),
									'class_icon'	=>'icon_manage_slide'
									);
			$admin_link_list['Phân vùng 1'][]=array(
									'title'	=>'Quản trị khách hàng',
									'des'	=>'Quản trị danh sách khách hàng',
									'url'	=>WEB_DIR.EBRewrite::formatUrl('?page=manage_customer?cmd=listCustomer'),
									'class_icon'	=>'icon_manage_customer'
									);
			$admin_link_list['Phân vùng 1'][]=array(
									'title'	=>'Quản trị backlinks',
									'des'	=>'Quản trị danh sách backlinks',
									'url'	=>WEB_DIR.EBRewrite::formatUrl('?page=manage_backlinks?cmd=listBacklinks'),
									'class_icon'	=>'icon_manage_backlinks'
									);
			$admin_link_list['Phân vùng 1'][]=array(
									'title'	=>'Quản trị color',
									'des'	=>'Quản trị danh sách color',
									'url'	=>WEB_DIR.EBRewrite::formatUrl('?page=manage_color?cmd=list'),
									'class_icon'	=>'icon_manage_color'
									);
			$admin_link_list['Phân vùng 1'][]=array(
									'title'	=>'Quản trị đơn hàng',
									'des'	=>'Quản trị danh sách đơn hàng',
									'url'	=>WEB_DIR.EBRewrite::formatUrl('?page=manage_order?cmd=list'),
									'class_icon'	=>'mng_emptycard'
									);
			$admin_link_list['Phân vùng 1'][]=array(
									'title'	=>'Quản lý thu chi',
									'des'	=>'Quản lý thu chi',
									'url'	=>WEB_DIR.EBRewrite::formatUrl('?page=manage_rae?cmd=home'),
									'class_icon'	=>'mng_rae'
									);
			$admin_link_list['Phân vùng 1'][]=array(
									'title'	=>'Quản lý khách hàng',
									'des'	=>'Quản lý khách hàng',
									'url'	=>WEB_DIR.EBRewrite::formatUrl('?page=manage_customer'),
									'class_icon'	=>'mng_customer'
									);
			$admin_link_list['Phân vùng 1'][]=array(
									'title'	=>'Quản lý nhập hàng',
									'des'	=>'Quản lý nhập hàng',
									'url'	=>WEB_DIR.EBRewrite::formatUrl('?page=manage_import_goods'),
									'class_icon'	=>'mng_import_goods'
									);
			$admin_link_list['Phân vùng 1'][]=array(
									'title'	=>'Target Customer',
									'des'	=>'Target Customer',
									'url'	=>WEB_DIR.EBRewrite::formatUrl('?page=manage_target'),
									'class_icon'	=>'mng_target'
									);
		}


        	$admin_link_list['Phân vùng 2'][]=array(
        			'title'	=>'Quản trị danh mục',
        			'des'	=>'Quản trị danh mục',
        			'url'	=>WEB_DIR.EBRewrite::formatUrl('?page=manage_category&cmd=list'),
        			'class_icon'	=>'icon_cat_manager'
        	);
        	$admin_link_list['Phân vùng 2'][]=array(
        			'title'	=>'Quản trị Sản phẩm',
        			'des'	=>'Quản trị sản phẩm',
        			'url'	=>WEB_DIR.EBRewrite::formatUrl('?page=manage_products&cmd=default'),
        			'class_icon'	=>'Manage_Products'
        	);
        	$admin_link_list['Phân vùng 2'][]=array(
        			'title'	=>'Quản trị tag',
        			'des'	=>'Quản trị tag',
        			'url'	=>WEB_DIR.EBRewrite::formatUrl('?page=manage_tag&cmd=list'),
        			'class_icon'	=>'Manage_Tag'
        	);
        	$admin_link_list["Phân vùng 2"][]=array(
									'title'	=>'Câu hỏi thường gặp',
									'des'	=>'Danh sách câu hỏi thường gặp',
									'url'	=>WEB_DIR.EBRewrite::formatUrl('?page=manage_question&cmd=list'),
									'class_icon'	=>'icon_question'
									);
        	$admin_link_list["Phân vùng 2"][]=array(
									'title'	=>'Manage Post',
									'des'	=>'List Post',
									'url'	=>WEB_DIR.EBRewrite::formatUrl('?page=manage_post&cmd=list'),
									'class_icon'	=>'icon_post'
									);
        return $admin_link_list;
    }

    private function setRoleMenu()
    {
        if (!Roles::access(MODULE_ROLE)) {
            return false;
        }
        $menu       = array('id'          => 'mng_role',
                            'label'       => 'Phân quyền',
                            'url'         => jUrl::build('mng_role'),
                            'show_home'   => 'd14',
                            'show_header' => 0,);
        $sub_menu[] = array('id'        => 'list-role',
                            'label'     => 'Danh sách quyền',
                            'url'       => jUrl::build('mng_role', array('cmd' => 'list')),
                            'show_home' => 0,

        );
        if (Roles::access(MODULE_ROLE, ROLE_CONFIG)) {
            $sub_menu[] = array('id'        => 'list-role-system',
                                'label'     => 'Phân quyền hệ thống',
                                'url'       => jUrl::build('mng_role', array('cmd' => 'list',
                                                                             'type'=> 'system')),
                                'show_home' => 0,);

            $sub_menu[] = array('id'            => 'list-role-project',
                                'label'         => 'Phân quyền dự án',
                                'onclick'       => 'return Project.showRoleProcess();',
                                'show_home'     => 0,

            );
        }
        $menu['sub_menu'] = $sub_menu;
        unset($sub_menu);
        return $menu;
    }

}
