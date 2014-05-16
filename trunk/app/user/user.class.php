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
define("USER_LOGIN_URL",    iCMS::$app_vars['SAPI'].'&do=login');
define("USER_CALLBACK_URL", iCMS::$app_vars['SAPI']);
//define("USER_AVATAR_PATH",  "/data3/home/guang.ladyband.com/pic");
require_once iPHP_APP.'/user/msg.class.php';
class user {
	public static $cookietime = 0;
	public static $format     = false;

	function check($val,$field='username'){
		$check = iDB::getValue("SELECT uid FROM `#iCMS@__user` where `$field`='{$val}'");
		return empty($check)?true:false;
	}
	function follow($uid=0,$fuid=0){
		$check = iDB::getRow("SELECT `fuid` FROM `#iCMS@__user_follow` where `uid`='{$uid}' and `fuid`='$fuid' limit 1");
		return $check?true:false;
	}
	function openid($uid=0){
		$pf = array();
		$rs = iDB::getArray("SELECT `openid`,`platform` FROM `#iCMS@__user_openid` where `uid`='{$uid}'");
		foreach ((array)$rs as $key => $value) {
			$pf[$value['platform']] = $value['openid'];
		}
		return $pf;
	}
	function login($v,$pass='',$t='nk'){

		$f = 'username';
		$t =='nk'	&& $f	= 'nickname';
		// $t=='qqoi' 	&& $f	= 'qqopenid';
		// $t=='wboi' 	&& $f	= 'wbopenid';
		// $t=='tboi' 	&& $f	= 'tbopenid';

		$user     = iDB::getRow("SELECT `uid`,`nickname`,`password`,`username` FROM `#iCMS@__user` where `{$f}`='{$v}' and `password`='$pass' limit 1");
		if(empty($user)){
			return false;
		}
		self::set_cookie($user->username,$user->password,(array)$user);
		unset($user->password);
		$user->avatar = userData($user->uid,'avatar');
		$user->urls   = userData($user->uid,'urls');
		return $user;
	}
	function setCache($uid){
		$user	= iDB::getRow("SELECT * FROM `#iCMS@__user` where `uid`='{$uid}'",ARRAY_A);
		iCache::set('user:'.$user['uid'],$user,0);
	}
	function set_cookie($a,$p,$user){
		$authash = '#=(iCMS@'.iPHP_KEY.')=#';
		iPHP::setCookie('AUTH_INFO',authcode((int)$user['uid'].$authash.$a.$authash.$p,'ENCODE'),self::$cookietime);
		iPHP::setCookie('userid',(int)$user['uid'],self::$cookietime);
		iPHP::setCookie('nickname',str_replace('"','',json_encode($user['nickname'])),self::$cookietime);
	}
	function category($cid=0){
		if(empty($cid)) return false;

		$category	= iDB::getRow("SELECT * FROM `#iCMS@__user_category` where `cid`='".(int)$cid."' limit 1");
		return (array)$category;
	}
	function data($uid=0,$unpass=true){
		if(empty($uid)) return false;

		$user         = iDB::getRow("SELECT * FROM `#iCMS@__user` where `uid`='".(int)$uid."' limit 1");
		$user->sex    = $user->gender?'male':'female';
		$user->avatar = userData($user->uid,'avatar');
		$user->urls   = userData($user->uid,'urls');
	   	if($unpass) unset($user->password);
	   	return $user;
	}
	function status($url=null,$st=null) {
		$status = false;
		$auth   = iPHP::getCookie('AUTH_INFO');
		$userid = (int)iPHP::getCookie('userid');
		if($auth && $userid){
			$authash = '#=(iCMS@'.iPHP_KEY.')=#';
			list($_userid,$_username,$_password) = explode($authash,authcode($auth));

	        if($_userid==$userid){
				$user	= self::data($userid,false);
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
	function logout(){
		iPHP::setCookie('AUTH_INFO', '',-31536000);
		iPHP::setCookie('userid', '',-31536000);
		iPHP::setCookie('nickname', '',-31536000);
		iPHP::setCookie('seccode', '',-31536000);
	}
}
