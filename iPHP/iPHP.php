<?php
/**
* iPHP - i PHP Framework
* Copyright (c) 2012 iiiphp.com. All rights reserved.
*
* @author coolmoo <iiiphp@qq.com>
* @site http://www.iiiphp.com
* @licence http://www.iiiphp.com/license
* @version 1.0.1
* $Id: iPHP.php 2412 2014-05-04 09:52:07Z coolmoo $
*/
// ini_set('display_errors','OFF');
// error_reporting(0);//iPHP默认 不显示错误信息
// error_reporting(E_ALL & ~E_DEPRECATED); //Production
//define('iPHP', TRUE);
defined('iPHP') OR exit('What are you doing?');

ini_set('display_errors','ON');
error_reporting(E_ALL & ~E_NOTICE);

header('Content-Type: text/html; charset=utf-8');
header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
version_compare('5.1',phpversion(),'>') && die('您的服务器运行的 PHP 版本是'.phpversion().' 但 iPHP 要求至少 5.1。');
@ini_set('magic_quotes_sybase', 0);
@ini_set("magic_quotes_runtime",0);

define('iPHP_PATH',dirname(strtr(__FILE__,'\\','/')));

require iPHP_PATH.'/iPHP.version.php';
require iPHP_PATH.'/iPHP.define.php';

if (function_exists('memory_get_usage') && ((int) @ini_get('memory_limit') < abs(intval(iPHP_MEMORY_LIMIT))))
    @ini_set('memory_limit', iPHP_MEMORY_LIMIT);

@ini_set('date.timezone',iPHP_TIME_ZONE);//设置时区
function_exists('date_default_timezone_set') && date_default_timezone_set(iPHP_TIME_ZONE);

require iPHP_PATH.'/iPHP.compat.php';
require iPHP_PATH.'/iPHP.class.php';
iPHP::timer_start();
//security
iPHP::LoadClass("Security");
iS::filter();
iS::gp('page','GP',2);

define('__SELF__',	$_SERVER['PHP_SELF']);
define('__REF__', 	$_SERVER['HTTP_REFERER']);
define('__HOST__', 	$_SERVER['HTTP_HOST']);

iPHP::loadClass("Mysql");		//加载数据库操作类
iPHP::loadClass("FileSystem");	//加载文件操作类
iPHP::loadClass('Cache');		//加载缓存操作类
iPHP::loadClass("Template");	//加载模板操作类
