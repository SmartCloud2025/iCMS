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
define("USER_LOGIN_URL",    iCMS_API_URL.'&do=login');
define("USER_AUTHASH",      '#=(iCMS@'.iPHP_KEY.'@iCMS)=#');
class user {
	public static $userid     = 0;
	public static $nickname   = '';
	public static $cookietime = 0;
	public static $format     = false;
	private static $AUTH      = 'USER_AUTH';

	public static function router($uid,$type,$size=0){
	    switch($type){
	        case 'avatar':return iCMS_FS_URL.get_user_file($uid,$size);break;
	        case 'url':   return iPHP::router(array('/{uid}/',$uid),iCMS_REWRITE);break;
	        case 'urls':
	            return array(
					'inbox'    => iPHP::router(array('/user/inbox/{uid}',$uid),iCMS_REWRITE),
					'home'     => iPHP::router(array('/{uid}/',$uid),iCMS_REWRITE),
					'comment'  => iPHP::router(array('/{uid}/comment/',$uid),iCMS_REWRITE),
					//'favorite' => iPHP::router(array('/{uid}/favorite/',$uid),iCMS_REWRITE),
					//'share'    => iPHP::router(array('/{uid}/share/',$uid),iCMS_REWRITE),
					'fans'     => iPHP::router(array('/{uid}/fans/',$uid),iCMS_REWRITE),
					'follow'   => iPHP::router(array('/{uid}/follow/',$uid),iCMS_REWRITE),
	            );
	        break;
	    }
	}
	public static function empty_info($uid,$name){
        return array(
			'uid'    => $uid,
			'name'   => $name,
			//'inbox'   => 'javascript:;',
			'url'    => 'javascript:;',
			'avatar' => 'about:blank',
			'link'   => '<a href="javascript:;">'.$name.'</a>',
			'at'     => '<a href="javascript:;">'.$name.'</a>',
        );
	}
	public static function info($uid,$name,$size=0){
		if(empty($uid)){
			return self::empty_info($uid, $name);
		}
		$url = self::router($uid,"url");
		return array(
			'uid'    => $uid,
			'name'   => $name,
			//'inbox'  => $urls['inbox'],
			'url'    => $url,
			'avatar' => self::router($uid,"avatar",$size?$size:0),
			'at'     => '<a href="'.$url.'" target="_blank" data-tip="iCMS:ucard:'.$uid.'">@'.$name.'</a>',
			'link'   => '<a href="'.$url.'" target="_blank" data-tip="iCMS:ucard:'.$uid.'">'.$name.'</a>',
		);
	}
	public static function check($val,$field='username'){
		$uid = iDB::value("SELECT uid FROM `#iCMS@__user` where `$field`='{$val}'");
		return empty($uid)?true:$uid;
	}
	public static function follow($uid=0,$fuid=0){
		if($uid==='all'){ //all fans
			$rs = iDB::all("SELECT `uid` AS `F`,`name` AS `N` FROM `#iCMS@__user_follow` where `fuid`='{$fuid}'");
		}
		if($fuid==='all'){ // all follow
			$rs = iDB::all("SELECT `fuid` AS `F`,`fname` AS `N` FROM `#iCMS@__user_follow` where `uid`='{$uid}'");
		}
		if(isset($rs)){
			foreach ((array)$rs as $key => $value) {
				$follow[$value['F']] = $value['N'];
			}
			return $follow;
		}
		$fuid = iDB::row("SELECT `fuid` FROM `#iCMS@__user_follow` where `uid`='{$uid}' and `fuid`='$fuid' limit 1");
		return $fuid?$fuid:false;
	}
	public static function update_count($uid=0,$count=0,$field='article',$math='+'){
		$math=='-' && $sql = " AND `{$field}`>0";
		iDB::query("UPDATE `#iCMS@__user` SET `{$field}` = {$field}{$math}{$count} WHERE `uid`='{$uid}' {$sql} LIMIT 1;");
	}
	public static function openid($uid=0){
		$pf = array();
		$rs = iDB::all("SELECT `openid`,`platform` FROM `#iCMS@__user_openid` where `uid`='{$uid}'");
		foreach ((array)$rs as $key => $value) {
			$pf[$value['platform']] = $value['openid'];
		}
		return $pf;
	}
	public static function set_cache($uid){
		$user	= iDB::row("SELECT * FROM `#iCMS@__user` where `uid`='{$uid}'",ARRAY_A);
		iCache::set('iCMS:user:'.$user['uid'],$user,0);
	}
	public static function category($cid=0){
		if(empty($cid)) return false;

		$category	= iDB::row("SELECT * FROM `#iCMS@__user_category` where `cid`='".(int)$cid."' limit 1");
		return (array)$category;
	}
	public static function get($uid=0,$unpass=true){
		if(empty($uid)) return false;

		$user = iDB::row("SELECT * FROM `#iCMS@__user` where `uid`='".(int)$uid."' AND `status`='1' limit 1");
		if(empty($user)){
			return false;
		}
		$user->gender = $user->gender?'male':'female';
		$user->avatar = self::router($user->uid,'avatar');
		$user->urls   = self::router($user->uid,'urls');
		$user->url    = $user->urls['home'];
		$user->inbox  = $user->urls['inbox'];
	   	if($unpass) unset($user->password);
	   	return $user;
	}
    public static function data($uid=0){
    	if(empty($uid)){
    		return false;
    	}
        $data = iDB::row("SELECT * FROM `#iCMS@__user_data` where `uid`='{$uid}' limit 1;");
        //iDB::debug(1);
        if($data){
            if($data->coverpic){
                $data->coverpic = iFS::fp($data->coverpic,'+http');
            }else{
                $data->coverpic = iCMS_PUBLIC_URL.iCMS::$config['user']['coverpic'];
            }
            $data->enterprise && $data->enterprise = unserialize($data->enterprise);
        }
        return $data;
    }
	public static function login($val,$pass='',$fm='un'){
		$field_map = array(
			'id' =>'uid',
			'nk' =>'nickname',
			'un' =>'username',
			'qq' =>'qqopenid',
			'wb' =>'wbopenid',
			'tb' =>'tbopenid',
		);
		$field = $field_map[$fm];
		$field OR $field = 'username';

		$user = iDB::row("SELECT `uid`,`nickname`,`password`,`username`,`status` FROM `#iCMS@__user` where `{$field}`='{$val}' and `password`='$pass' limit 1");
		if(empty($user)){
			return false;
		}
		if((int)$user->status!=1){
			return $user->status;
		}
		self::set_cookie($user->username,$user->password,(array)$user);
		return true;
	}
	public static function get_cookie($unpw=false) {
		$auth     = authcode(iPHP::get_cookie(self::$AUTH));
		$userid   = authcode(iPHP::get_cookie('userid'));
		$nickname = authcode(iPHP::get_cookie('nickname'));

		list($_userid,$_username,$_password,$_nickname) = explode(USER_AUTHASH,$auth);

		if((int)$userid===(int)$_userid && $nickname===$_nickname){
			self::$userid   = (int)$_userid;
			self::$nickname = $_nickname;
			$u = array('userid'=>self::$userid,'nickname'=>self::$nickname);
			if($unpw){
				$u['username'] = $_username;
				$u['password'] = $_password;
			}
			return $u;
		}
		//self::logout();
		return false;
	}
	public static function set_cookie($username,$password,$user){
		iPHP::set_cookie(self::$AUTH, authcode((int)$user['uid'].USER_AUTHASH.$username.USER_AUTHASH.$password.USER_AUTHASH.$user['nickname'].USER_AUTHASH.$user['status'],'ENCODE'),self::$cookietime);
		iPHP::set_cookie('userid',    authcode($user['uid'],'ENCODE'),self::$cookietime);
		iPHP::set_cookie('nickname',  authcode($user['nickname'],'ENCODE'),self::$cookietime);
	}
	public static function status($url=null,$st=null) {
		$status = false;
		$auth   = self::get_cookie(true);

		if($auth){
			$user = self::get($auth['userid'],false);
			if($auth['username']==$user->username && $auth['password']==$user->password){
				$status = true;
			}
			unset($user->password);
		}
		unset($auth);

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
		iPHP::set_cookie(self::$AUTH, '',-31536000);
		iPHP::set_cookie('userid', '',-31536000);
		iPHP::set_cookie('nickname', '',-31536000);
		iPHP::set_cookie('seccode', '',-31536000);
	}
}
