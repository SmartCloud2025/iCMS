<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: iMember.class.php 2279 2013-11-17 17:19:12Z coolmoo $
*/
class iMember{
    public static $userid       = 0;
    public static $data         = array();
    public static $nickname     = NULL;
    public static $group        = array();
    public static $ajax         = false;
    public static $mpower       = array();
    public static $cpower       = array();
    public static $AUTH         = 'iCMS_AUTH';
    public static $LOGIN_TPL    = './';
    private static $login_count = 0;

    public static function check($a,$p) {
    	if(empty($a) && empty($p)) {
        	self::LoginPage();
    	}

        self::$data = iDB::row("SELECT * FROM `#iCMS@__members` WHERE `username`='{$a}' AND `password`='{$p}' AND `status`='1' LIMIT 1;");
        self::$data OR self::LoginPage();
        unset(self::$data->password);
        self::$data->info && self::$data->info	= unserialize(self::$data->info);
        self::$userid   = self::$data->uid;
        self::$nickname = self::$data->nickname?self::$data->nickname:self::$data->username;
        
        self::$group  = iDB::row("SELECT * FROM `#iCMS@__group` WHERE `gid`='".self::$data->gid."' LIMIT 1;");
        self::$mpower = self::use_power(self::$group->power,self::$data->power);
        self::$cpower = self::use_power(self::$group->cpower,self::$data->cpower);

        return self::$data;
    }
    //登陆验证
    public static function checkLogin() {
//        self::$login_count = (int)authcode(get_cookie('iCMS_LOGIN_COUNT'),'DECODE');
//        if(self::$login_count>iCMS_LOGIN_COUNT) exit();

        $a   = iS::escapeStr($_POST['username']);
        $p   = iS::escapeStr($_POST['password']);
        $ip  = iPHP::getIp();
        $sep = iPHP_AUTH_IP?'#=iCMS['.$ip.']=#':'#=iCMS=#';
        if(empty($a) && empty($p)) {
            $auth       = iPHP::get_cookie(self::$AUTH);
            list($a,$p) = explode($sep,authcode($auth,'DECODE'));
            return self::check($a,$p);
        }else {
            $p   = md5($p);
            $crs = self::check($a,$p);
            iDB::query("UPDATE `#iCMS@__members` SET `lastip`='".$ip."',`lastlogintime`='".time()."',`logintimes`=logintimes+1 WHERE `uid`='".self::$userid."'");
            iPHP::set_cookie(self::$AUTH,authcode($a.$sep.$p,'ENCODE'));
        	self::$ajax && iPHP::json(array('code'=>1));
            return $crs;
        }
    }

	//登陆页
	public static function LoginPage(){
		self::$ajax && iPHP::json(array('code'=>0));
        iPHP::set_cookie(self::$AUTH,'',-31536000);
		include self::$LOGIN_TPL.'/template/login.php';
		exit;
	}
	//注销
	public static function logout(){
		iPHP::set_cookie(self::$AUTH,'',-31536000);
	}
    //检查栏目权限
    public static function CP($p=NULL,$T="F",$url=__REF__) {
        if(self::$data->gid=="1") return TRUE;
//         var_dump($p);
// exit;
        if(self::check_power($p,self::$cpower)) {
            return TRUE;
        }else{
            if($T=='F') {
                return FALSE;
            }else {
                exit("Permission denied!");
            }
        };
    }
    //检查后台权限
    public static function MP($p=NULL,$T="Permission_Denied",$url=__REF__) {
//         if(self::$data->gid=="1") return TRUE;

        if(self::check_power($p,self::$mpower)) {
            return TRUE;
        }else {
            if($T=='F') {
                return FALSE;
            }else {
                exit("Permission denied!");
            }
        }
    }
    private static function check_power($p,$power){
        return is_array($p)?array_intersect($p,$power):in_array($p,$power);
    }
	private static function use_power($p1,$p2){
        if($p1){ //用户独立权限优先
            return json_decode($p1);
        }elseif($p2){
            return json_decode($p2);
        }
        return false;
	}
}
?>