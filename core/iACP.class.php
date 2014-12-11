<?php

/**
 * iCMS - i Content Management System
 * Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
 *
 * @author coolmoo <idreamsoft@qq.com>
 * @site http://www.idreamsoft.com
 * @licence http://www.idreamsoft.com/license.php
 * @version 6.0.0
 * @$Id: admincp.class.php 2361 2014-02-22 01:52:39Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');

define('__ADMINCP__',	__SELF__ . '?app');
define('ACP_PATH',      iPHP_APP_DIR . '/admincp');
define('ACP_HOST',      "http://".$_SERVER['HTTP_HOST']);

define('iCMS_SUPERADMIN_UID', '1');
require iPHP_APP_CORE.'/iMember.class.php';
require iPHP_APP_CORE.'/iMenu.class.php';

iDB::$show_errors      = true;
iMember::$LOGIN_TPL    = ACP_PATH;
iMember::$AUTH         = 'ADMIN_AUTH';
iMember::$AJAX         = iPHP::PG('ajax');
iPHP::$dialog['title'] = 'iCMS';

class iACP {
    public static $apps       = NULL;
    public static $frames     = NULL;
    public static $menu       = NULL;
    public static $app        = NULL;
    public static $app_name   = NULL;
    public static $app_do     = NULL;
    public static $app_args   = NULL;
    public static $app_method = NULL;
    public static $app_tpl    = NULL;
    public static $app_path   = NULL;
    public static $app_file   = NULL;

    public static function init() {
        iMember::checkLogin();
        self::$menu = new iMenu();
        self::MP('ADMINCP','page'); //检查是否有后台权限
        self::MP('__MID__','page'); //检查菜单ID
        self::$apps  = array('home', 'category', 'pushcategory','tagcategory', 'article', 'push', 'prop', 'setting', 'filter', 'cache','tags','editor');
        iFS::$userid = iMember::$userid;
    }

	public static function frame(){
		self::$frames	= $_GET['frames']?$_GET['frames']:$_POST['frames'];
		if(empty($_GET['app']) || self::$frames) {
			include self::view("admincp");
			exit;
		}
	}
    public static function run($args = NULL,$prefix="do_") {
        self::init();
        $app = $_GET['app'];
        $app OR $app = 'home';
        //in_array($app, self::$apps) OR iPHP::throwException('运行出错！找不到应用程序:' . $app, 1001);
        $do OR $do = $_GET['do'] ? (string) $_GET['do'] : 'iCMS';
        if($_POST['action']){
            $do     = $_POST['action'];
            $prefix = 'ACTION_';
        }
        self::$app_name   = $app;
        self::$app_do     = $do;
        self::$app_method = $prefix.$do;
        self::$app_path   = ACP_PATH;
        self::$app_tpl    = ACP_PATH . '/template';
        self::$app_file   = ACP_PATH . '/' . $app . '.app.php';
        define('APP_URI', 	__ADMINCP__ . '=' . $app);
        define('APP_FURI', 	APP_URI.'&frame=iPHP');
        define('APP_DOURI', APP_URI.($do != 'iCMS' ? '&do=' . $do : ''));
        define('APP_BOXID',	self::$app_name.'-box');
		define('APP_FORMID','iCMS-'.APP_BOXID);
        is_file(self::$app_file) OR iPHP::throwException('运行出错！找不到文件: <b>' . self::$app_name . '.app.php</b>', 1002);
		iPHP::import(self::$app_file);
        $appName     = self::$app_name . 'App';
        self::$app   = new $appName();
        $app_methods = get_class_methods($appName);
        in_array(self::$app_method, $app_methods) OR iPHP::throwException('运行出错！ <b>' . self::$app_name . '</b> 类中找不到方法定义: <b>' . self::$app_method . '</b>', 1003);
        $method = self::$app_method;
        $args===null && $args = self::$app_args;
        if($args){
            if($args==='object'){
                return self::$app;
            }
            return self::$app->$method($args);
        }else{
            return self::$app->$method();
        }
    }

    public static function app($app = NULL, $arg = NULL) {
        iPHP::import(ACP_PATH . '/' . $app . '.app.php');
        if ($arg === 'import'||$arg === 'static') {
            return;
        }
        $appName = $app . 'App';
        if ($arg !== NULL) {
            return new $appName($arg);
        }
        return new $appName();
    }

    public static function view($p = NULL) {
        if ($p === NULL && self::$app_name) {
            $p = self::$app_name;
            self::$app_do && $p.='.' . self::$app_do;
        }
        return ACP_PATH . '/template/' . $p . '.php';
    }
    public static function getConfig($tid = 0, $n = NULL) {
        if ($n === NULL) {
            $rs = iDB::all("SELECT * FROM `#iCMS@__config` WHERE `tid`='$tid'");
            foreach ($rs AS $c) {
                $value = $c['value'];
                strstr($c['value'], 'a:') && $value = unserialize($c['value']);
                $config[$c['name']] = $value;
            }
            return $config;
        } else {
            $value = iDB::value("SELECT `value` FROM `#iCMS@__config` WHERE `tid`='$tid' AND `name` ='$n'");
            strstr($value, 'a:') && $value = unserialize($value);
            return $value;
        }
    }

    public static function setConfig($v, $n, $tid, $cache = false) {
        $cache && iCache::set('iCMS/' . $n, $v, 0);
        is_array($v) && $v = addslashes(serialize($v));
        iDB::query("UPDATE `#iCMS@__config` SET `value` = '$v' WHERE `tid` ='$tid' AND `name` ='$n'");
    }
    public static function cacheConfig($config=null){
    	$config===null && $config = iACP::getConfig(0);
     	$output = "<?php\ndefined('iPHP') OR exit('Access Denied');\nreturn ";
    	$output.= var_export($config,true);
    	$output.= ';';
    	iFS::write(iPHP_APP_CONF.'/config.php',$output);
	}
	public static function updateConfig($k){
		iACP::setConfig(iCMS::$config[$k],$k,0);
		iACP::cacheConfig();
	}
    public static function fields($data='') {
        $fields = array();
        $dA     = explode(',', $data);
        foreach ((array) $dA as $d) {
            list($f, $v) = explode(':', $d);
            $v == 'now' && $v = time();
            $v = (int) $v;
            $fields[$f] = $v;
        }
        return $fields;
    }
    public static function MP($p,$ret=''){
        if(self::is_superadmin()) return true;

        self::$menu->power = (array)iMember::$mpower;
        if($p==='__MID__'){
            $rt1 = $rt2 = $rt3 = true;
            self::$menu->rootid   && $rt1 = self::$menu->check_power(self::$menu->rootid);
            self::$menu->parentid && $rt2 = self::$menu->check_power(self::$menu->parentid);
            self::$menu->do_mid   && $rt3 = self::$menu->check_power(self::$menu->do_mid);
            if($rt1 && $rt2 && $rt3){
                return true;
            }
            self::permission_msg($p,$ret);
        }
        $rt = self::$menu->check_power($p);
        $rt OR self::permission_msg($p,$ret);
        return $rt;
    }
    public static function CP($p,$act='',$ret=''){
        if(self::is_superadmin()) return true;

        if($p==='__CID__'){
            foreach ((array)iMember::$cpower as $key => $_cid) {
                if(!strstr($value, ':')){
                    self::CP($_cid,$act) && $cids[] = $_cid;
                }
            }
            return $cids;
        }

        $act && $p = $p.':'.$act;

        $rt = iMember::check_power((string)$p,iMember::$cpower);
        $rt OR self::permission_msg($p,$ret);
        return $rt;
    }
    public static function permission_msg($p='',$ret=''){
        if($ret=='alert'){
            iPHP::alert('您没有相关权限!');
            exit;
        }elseif($ret=='page'){
            include self::view("admincp.permission");
            exit;
        }
    }
    public static function is_superadmin(){
        return (iMember::$data->gid===iCMS_SUPERADMIN_UID);
    }
    public static function head($navbar = true) {
        $body_class = '';
        if(iCMS::$config['other']['sidebar_enable']){
            iCMS::$config['other']['sidebar'] OR $body_class = 'sidebar-mini';
            $body_class = iPHP::get_cookie('ACP_sidebar_mini') ?'sidebar-mini':'';
        }else{
            $body_class = 'sidebar-display';
        }
        $navbar===false && $body_class = 'iframe ';

        include self::view("admincp.header");
        $navbar===true && include self::view("admincp.navbar");
    }

    public static function foot() {
        include self::view("admincp.footer");
    }
    public static function picBtnGroup($callback,$indexid=0) {
        include self::view("admincp.picBtnGroup");
    }
    public static function propBtn($field, $type = "") {
        $type OR $type = self::$app_name;
        $propArray = iCache::get("iCMS/prop/{$type}.{$field}");
        echo '<div class="btn-group">';
        echo '<a class="btn dropdown-toggle iCMS-default" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span> 选择</a>';
        if ($propArray) {
            echo '<ul class="dropdown-menu">';
            foreach ($propArray as $prop) {
                echo '<li><a href="javascript:;" data-toggle="insert" data-target="#' . $field . '">' . $prop['val'] . '</a></li>';
            }
            echo '</ul>';
        }
        echo '</div>';
    }

    public static function getProp($field, $val = NULL,/*$default=array(),*/$out = 'option', $url="",$type = "") {
        $type OR $type = self::$app_name;
        $propArray = iCache::get("iCMS/prop/{$type}.{$field}");
        $valArray  = explode(',', $val);
        if ($propArray){
            foreach ($propArray AS $k => $P) {
                if ($out == 'option') {
                    $opt.="<option value='{$P['val']}'" . (array_search($P['val'],$valArray)!==FALSE ? " selected='selected'" : '') . ">{$P['name']}[pid='{$P['val']}'] </option>";
                } elseif ($out == 'text') {
                    if (array_search($P['val'],$valArray)!==FALSE) {
                        $flag = '<i class="fa fa-flag"></i> '.$P['name'];
                        $opt .= ($url?'<a href="'.str_replace('{PID}',$P['val'],$url).'">'.$flag.'</a>':$flag).'<br />';
                    }
                }
            }
        }
        // $opt.='</select>';
        return $opt;
    }
    public static function files_modal_btn($title='',$click='file',$target='template_index',$callback='',$do='seltpl',$from='modal') {
        $href = __ADMINCP__."=files&do={$do}&from={$from}&click={$click}&target={$target}&callback={$callback}";
        $_title=$title.'文件';
        $click=='dir' && $_title=$title.'目录';
        echo '<a href="'.$href.'" class="btn files_modal" data-toggle="modal" title="选择'.$_title.'"><i class="fa fa-search"></i> 选择</a>';
    }
}
