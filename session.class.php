<?
if (eregi(basename( __FILE__ ), $_SERVER['PHP_SELF'])) {
	print "<h1>Incorrect access</h1>You cannot access this file directly.";
	exit();
}
//Hoang DT 18-11-07
/*
Creat ss table

CREATE TABLE IF NOT EXISTS `"._SESS_TABLE."` (
  `session_id` varchar(32) character set latin1 collate latin1_bin NOT NULL default '',
  `session_expires` int(10) unsigned NOT NULL default '0',
  `session_data` text character set latin1 NOT NULL,
  `session_ip` varchar(36) character set latin1 NOT NULL default '0',
  `session_referer` varchar(255) character set latin1 collate latin1_bin NOT NULL default '',
  `page` varchar(50) character set latin1 default NULL,
  `page_id` int(11) NOT NULL default '0',
  `category_id` int(11) NOT NULL default '0',
  `item_type` int(11) NOT NULL default '0',
  `item_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `user_name` varchar(50) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`session_id`),
  KEY `session_idx` (`session_expires`,`session_ip`,`session_referer`,`user_id`,`page`,`category_id`,`item_type`,`item_id`),
  KEY `session_id_idx` (`session_id`),
  KEY `session_ol_indx` (`session_expires`,`user_id`),
  KEY `session_ol_detail_idx` (`session_expires`,`page`,`item_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

*/

class EBSession{
    /**
     * a database connection resource
     * @var resource
     */
    // mysql-handle
	var $result,$data=array(),$effect_rows = 0;
	var $replicate = false; // for read query
    /**
     * Open the session
     * @return bool
     */
	function __construct() {
		//nothing to do
		//set max life time to 24'

/*		session_set_save_handler(array(&$this, 'open'),
		                         array(&$this, 'close'),
		                         array(&$this, 'read'),
		                         array(&$this, 'write'),
		                         array(&$this, 'destroy'),
		                         array(&$this, 'gc'));	
		session_start();*/
	}
	
  	function query($sql,$call_pos=''){
  		$result = DB::query($sql,$call_pos);
  		$this->effect_rows = DB::affected_rows();
    	return $result;
  	}
  	    
    function open() {
		return true;
    }
    /**
     * Close the session
     * @return bool
     */
    function close() {
        $this->gc();
        DB::close();//close database-connection
        return true;
    }

    /**
     * Read the session
     * @param int session id
     * @return string string of the sessoin
     */
    function read($session_id){
        //$sql = sprintf("SELECT `session_data` FROM "._SESS_TABLE." WHERE session_id = '%s'", $session_id);
        $sql = "SELECT  session_data, session_referer, page, page_id, item_id, login_type, open_id FROM "._SESS_TABLE." WHERE session_id = '$session_id'";
        $result = $this->query($sql,__LINE__.__FILE__);
        if (mysql_num_rows($result)) {
            $this->data = mysql_fetch_assoc($result);
            if( $this->data)        
            	return  $this->data['session_data'];
        }
        return '';
    }

    /**
     * Write the session
     * @param int session id
     * @param string data of the session
     */
    function write($session_id, $data){
    	$page     ='';
    	$page_id  =0;
    	
    	$user_id   = isset($_SESSION['user_id'])?(int)$_SESSION['user_id']:0;
		$user_name = isset($_SESSION['user_name'])?$_SESSION['user_name']:0;
		
		$login_type = (isset($_SESSION['openid_url']) && $_SESSION['openid_url']!='')?1:0;
		$open_id    = (isset($_SESSION['openid_url']) && $_SESSION['openid_url']!='')?$_SESSION['openid_url']:'';
    	
    	if(class_exists('EnBac')){
    		$page		=EnBac::$page['name'];
    		$page_id	=(int)EnBac::$page['id'];
    	}
    	
    	if(($page=='ItemDetail' || $page=='item_detail') && isset($_GET['id']) && $_GET['id']){
    		$item_id=(int)$_GET['id'];
    	}
    	else{
    		$item_id=0;
    	}
    	
    	if(in_array(basename($_SERVER['PHP_SELF']),array('ebxml.php','ajax.php','captcha.php'))){
    		$ref_url='';
    	}
    	else{
			EnBacLib::check_uri();
    		$ref_url=CGlobal::$query_string;
    	}
    	
    	if($this->data){
    		if($ref_url=='' || in_array(basename($_SERVER['PHP_SELF']),array('ebxml.php','ajax.php','captcha.php'))){
	    		$ref_url=stripslashes($this->data['session_referer']);
	    	}
    		
    		if(!$page) 					$page 		= $this->data['page'] ;
    		if(!$page_id) 				$page_id 	= (int)$this->data['page_id'] ;
    		if(!$item_id) 				$item_id 	= (int)$this->data['item_id'] ;
    		
    		$sql = "UPDATE "._SESS_TABLE." SET
											  	session_expires = ".TIME_NOW.", 
											  	session_data 	= '$data',
											  	session_referer = '".addslashes($ref_url)."',
											  	session_ip 		= '".$_SERVER['SERVER_ADDR'].'::'.$_SERVER['REMOTE_ADDR']."', 
											  	page 			= '$page',	
											  	page_id 		= $page_id,
											  	category_id 	= ".CGlobal::$curCategory.",
											  	item_id 		= $item_id,
											  	user_id 		= '$user_id',
											  	user_name		= '$user_name',
											  	login_type		= '$login_type',
											  	open_id			= '$open_id'
											WHERE session_id 	= '$session_id'";
    	}
		else{
    		$sql = "INSERT INTO "._SESS_TABLE." 
    						  (
    						  	session_id, 
    						  	session_expires,  
    						  	session_data,  
    						  	session_referer, 
    						  	session_ip,   
    						  	page,  
    						  	page_id, 
    						  	category_id, 
    						  	item_id, 
    						  	user_id,  
    						 	user_name,
							  	login_type,
							  	open_id
    						  	) 
                       VALUES (
                       			'$session_id', 				
                       			".TIME_NOW.", 			
                       			'$data', 			   
                       			'".addslashes($ref_url)."', 	   
                       			'".$_SERVER['SERVER_ADDR'].'::'.$_SERVER['REMOTE_ADDR']."',	
                       			'$page',	  
                       			$page_id,		   
                       			".CGlobal::$curCategory.", 		 
                       			$item_id, 	  
                       			$user_id ,		
                       			'$user_name',		
	                   			$login_type ,		
	                   			'$open_id')";
    	}
                       
 		$this->query($sql,__LINE__.__FILE__);
        
 		if(User::is_login() && MEMCACHE_ON){//Nếu đã đăng nhập
			$user = User::$current->data;
			if($user && !isset($user['last_login']) || (isset($user['last_login']) && $user['last_login']<(TIME_NOW-300))){
				DB::query("UPDATE account SET last_login=".TIME_NOW." WHERE id={$user['id']}");
				
				$user_memcache = eb_memcache::do_get("user:{$user['id']}");
				if($user_memcache){
					$user_memcache['last_login'] = TIME_NOW;
					eb_memcache::do_put("user:{$user['id']}", $user_memcache);
				}
			}
		}
		
		if($this->effect_rows){//if row was created, return true
			return true;
		}
        return false;//an unknown error occured
    }

    /**
     * Destoroy the session
     * @param int session id
     * @return bool
     */
    function destroy($session_id){
        $sql = "DELETE FROM "._SESS_TABLE." WHERE `session_id` = '$session_id'";
        $this->query($sql,__LINE__.__FILE__);
       
        if($this->effect_rows){//if row was created, return true
			return true;
		}
        return false;//an unknown error occured
	}

    /**
     * Garbage Collector
     * @param int life time (sec.)
     * @return bool
     * @see session.gc_divisor      100
     * @see session.gc_maxlifetime 1440
     * @see session.gc_probability    1
     * @usage execution rate 1/100
     *        (session.gc_probability/session.gc_divisor)
     */
    function gc(){
		return true;
/*        $sql = "DELETE FROM "._SESS_TABLE." WHERE `session_expires` < ".(time() - _SESS_TIME_EXPIRE);
        $this->query($sql,__LINE__.__FILE__);                       
        if($this->effect_rows){//if row was created, return true
			return true;
		}*/
        return false;//an unknown error occured
	}
}

//maybe we create new function for handling this
function init_session_cookies($path="/", $domain="") {
  if ($domain=='localhost') $domain='';
  if (function_exists('session_set_cookie_params')) {
    session_set_cookie_params(0, $path, $domain);
  } else {
    ini_set('session.cookie_lifetime', '0');
    ini_set('session.cookie_path', $path);
    ini_set('session.cookie_domain', $domain);
  }
}


$cookie_path = '/';

if(eregi("enbac.com", $_SERVER['HTTP_HOST'])){
	$cookie_domain = '.enbac.com'; //or any valid domain
}
else{
	$cookie_domain = "localhost";
}

init_session_cookies($cookie_path, $cookie_domain);


//ini_set('session.save_handler', 'user');
//ini_set('session.gc_divisor',100);
//ini_set('session.gc_maxlifetime', 28800);
//ini_set('session.gc_probability',    1);	

$session =& new EBSession();

session_set_save_handler(array(&$session, 'open'),
                         array(&$session, 'close'),
                         array(&$session, 'read'),
                         array(&$session, 'write'),
                         array(&$session, 'destroy'),
                         array(&$session, 'gc'));

// below sample main
session_start();
//session_regenerate_id(true);
?>