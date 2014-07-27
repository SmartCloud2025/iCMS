<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: iUser.class.php 2279 2013-11-17 17:19:12Z coolmoo $
*/
class iUser{
    public static $uId         = 0;
    public static $Rs          = array();
    public static $nickname    = NULL;
    public static $group       = array();
    public static $ajax        = false;
    public static $cpower      = array();
    public static $power       = array();
    public static $AUTH        = 'iCMS_AUTH';
    public static $LOGIN_TPL   = './';
    private static $loginCount = 0;

    function check($a,$p) {
    	if(empty($a) && empty($p)) {
        	self::LoginPage();
    	}

        self::$Rs = iDB::row("SELECT * FROM `#iCMS@__users` WHERE `username`='{$a}' AND `password`='{$p}' AND `status`='1' LIMIT 1;");
        self::$Rs OR self::LoginPage();
        self::$Rs->info && self::$Rs->info	= unserialize(self::$Rs->info);
        self::$uId		= self::$Rs->uid;
        self::$group	= iDB::row("SELECT * FROM `#iCMS@__group` WHERE `gid`='".self::$Rs->gid."' LIMIT 1;");
        self::$power 	= self::smerge(self::$group->power,self::$Rs->power);
        self::$cpower	= self::smerge(self::$group->cpower,self::$Rs->cpower);
		self::$nickname	= self::$Rs->nickname?self::$Rs->nickname:self::$Rs->username;
        return self::$Rs;
    }

	function avatar($size="24"){
		$_dir	= ceil(self::$uId/500);
		$avatar	= 'avatar/'.$_dir.'/'.self::$uId.'_'.$size.'.gif';
		if(file_exists(iFS::fp($avatar,'+iPATH'))){
			return iFS::fp($avatar);
		}else{
			return iCMS::$config['router']['publicURL'].'/common/avatar_'.$size.'.gif';
		}
	}
    //检查栏目权限
    function CP($p=NULL,$T="F",$url=__REF__) {
        if(self::$Rs->gid=="1")
            return TRUE;

        if(is_array($p)?array_intersect($p,self::$cpower):in_array($p,self::$cpower)) {
            return TRUE;
        }else {
            if($T=='F') {
                return FALSE;
            }else {
                exit("Permission denied!");
            }
        };
    }
    //检查后台权限
    function MP($p=NULL,$T="Permission_Denied",$url=__REF__) {
        if(self::$Rs->gid=="1")
            return TRUE;

        if(is_array($p)?array_intersect($p,self::$power):in_array($p,self::$power)) {
            return TRUE;
        }else {
            if($T=='F') {
                return FALSE;
            }else {
                exit("Permission denied!");
            }
        }
    }
    //登陆验证
    function checklogin() {
//        self::$loginCount = (int)authcode(get_cookie('iCMS_LOGIN_COUNT'),'DECODE');
//        if(self::$loginCount>iCMS_LOGIN_COUNT) exit();

 		$a	= $_POST['username'];
		$p	= $_POST['password'];
       	$ip = iPHP::getIp();
        $sep= iPHP_AUTH_IP?'#=iCMS['.$ip.']=#':'#=iCMS=#';
        if(empty($a) && empty($p)) {
            $auth	= iPHP::get_cookie(self::$AUTH);
            list($a,$p)	= explode($sep,authcode($auth,'DECODE'));
            return self::check($a,$p);
        }else {
        	$p	= md5($p);
            $crs= self::check($a,$p);
            iDB::query("UPDATE `#iCMS@__users` SET `lastip`='".$ip."',`lastlogintime`='".time()."',`logintimes`=logintimes+1 WHERE `uid`='".self::$uId."'");
            iPHP::set_cookie(self::$AUTH,authcode($a.$sep.$p,'ENCODE'));
        	self::$ajax && iPHP::json(array('code'=>1));
            return $crs;
        }
    }
    
	//登陆页
	function LoginPage(){
		self::$ajax && iPHP::json(array('code'=>0));
        iPHP::set_cookie(self::$AUTH,'',-31536000);
		include self::$LOGIN_TPL.'/template/login.php';
		exit;
	}
	//注销
	function logout(){
		iPHP::set_cookie(self::$AUTH,'',-31536000);
	}
	function smerge($s1,$s2){
		$a	= array();
		$s1 && $a[]=$s1;
		$s2 && $a[]=$s2;
		$s	= implode(',',$a);
		return explode(',',$s);
	}
}
?>