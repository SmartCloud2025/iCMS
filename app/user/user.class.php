<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
// $GLOBALS['iCONFIG']['user_fs_conf']	= array(
// 	"url"=>"http://s1.ladyband.cn",
// 	"dir"=>"../pic"
// );
defined('iPHP') OR exit('What are you doing?');


//require_once iPHP_APP_DIR.'/user/msg.class.php';
define("USER_CALLBACK_URL", iCMS_API_URL);
define("USER_LOGIN_URL",    iCMS_API_URL.'&do=login');
define("USER_AUTHASH",      '#=(iCMS@'.iPHP_KEY.')=#');

class user {
	public static $cookietime = 0;
	public static $format     = false;

	public static function check($val,$field='username'){
		$uid = iDB::value("SELECT uid FROM `#iCMS@__user` where `$field`='{$val}'");
		return empty($uid)?true:$uid;
	}
	public static function follow($uid=0,$fuid=0){
		$fuid = iDB::row("SELECT `fuid` FROM `#iCMS@__user_follow` where `uid`='{$uid}' and `fuid`='$fuid' limit 1");
		return $fuid?$fuid:false;
	}
	public static function openid($uid=0){
		$pf = array();
		$rs = iDB::all("SELECT `openid`,`platform` FROM `#iCMS@__user_openid` where `uid`='{$uid}'");
		foreach ((array)$rs as $key => $value) {
			$pf[$value['platform']] = $value['openid'];
		}
		return $pf;
	}
	public static function login($v,$pass='',$t='nk'){

		$f = 'username';
		$t =='nk'	&& $f	= 'nickname';
		// $t=='qqoi' 	&& $f	= 'qqopenid';
		// $t=='wboi' 	&& $f	= 'wbopenid';
		// $t=='tboi' 	&& $f	= 'tbopenid';

		$user = iDB::row("SELECT `uid`,`nickname`,`password`,`username` FROM `#iCMS@__user` where `{$f}`='{$v}' and `password`='$pass' AND `status`='1' limit 1");
		if(empty($user)){
			return false;
		}
		self::set_cookie($user->username,$user->password,(array)$user);
		unset($user->password);
		$user->avatar = get_user($user->uid,'avatar');
		$user->urls   = get_user($user->uid,'urls');
		return $user;
	}
	public static function set_cache($uid){
		$user	= iDB::row("SELECT * FROM `#iCMS@__user` where `uid`='{$uid}'",ARRAY_A);
		iCache::set('user:'.$user['uid'],$user,0);
	}
	public static function set_cookie($username,$password,$user){
		iPHP::set_cookie('AUTH_INFO',authcode((int)$user['uid'].USER_AUTHASH.$username.USER_AUTHASH.$password,'ENCODE'),self::$cookietime);
		iPHP::set_cookie('userid',(int)$user['uid'],self::$cookietime);
		iPHP::set_cookie('nickname',str_replace('"','',json_encode($user['nickname'])),self::$cookietime);
	}
	public static function category($cid=0){
		if(empty($cid)) return false;

		$category	= iDB::row("SELECT * FROM `#iCMS@__user_category` where `cid`='".(int)$cid."' limit 1");
		return (array)$category;
	}
	public static function data($uid=0,$unpass=true){
		if(empty($uid)) return false;

		$user         = iDB::row("SELECT * FROM `#iCMS@__user` where `uid`='".(int)$uid."' limit 1");
		$user->gender = $user->gender?'male':'female';
		$user->avatar = get_user($user->uid,'avatar');
		$user->urls   = get_user($user->uid,'urls');
	   	if($unpass) unset($user->password);
	   	return $user;
	}
	public static function status($url=null,$st=null) {
		$status = false;
		$auth   = iPHP::get_cookie('AUTH_INFO');
		$userid = (int)iPHP::get_cookie('userid');
		if($auth && $userid){
			list($_userid,$_username,$_password) = explode(USER_AUTHASH,authcode($auth));
	        if($_userid==$userid){
				$user = self::data($userid,false);
				if($_username==$user->username && $_password==$user->password){
					$status = true;
				}
				unset($user->password);
	        }
		}

		if($status){
			if($url && $st=="login"){
				if(self::$format=='json'){
					return iPHP::code(1,0,$url,'json');
				}
				iPHP::gotourl($url);
			}
			return $user;
		}else{
			if($url && $st=="nologin"){
				if(self::$format=='json'){
					return iPHP::code(0,0,$url,'json');
				}
				iPHP::gotourl($url);
			}
			return false;
		}
	}
	public static function logout(){
		iPHP::set_cookie('AUTH_INFO', '',-31536000);
		iPHP::set_cookie('userid', '',-31536000);
		iPHP::set_cookie('nickname', '',-31536000);
		iPHP::set_cookie('seccode', '',-31536000);
	}
}
