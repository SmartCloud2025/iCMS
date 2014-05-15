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
error_reporting(E_ALL ^ E_NOTICE);	//默认关闭所有错误显示
header('Content-Type: text/html; charset=utf-8');
header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
version_compare('5.1',phpversion(),'>') && die('您的服务器运行的 PHP 版本是'.phpversion().' 但 iPHP 要求至少 5.1。');

defined('iPHP') OR define('iPHP', TRUE);
defined('iPATH') OR define('iPATH',dirname(strtr(__FILE__,'\\','/'))."/");

require iPATH.'iPHP/iPHP.version.php';
require iPATH.'iPHP/iPHP.define.php';
require iPATH.'iPHP/iPHP.compat.php';
require iPATH.'iPHP/iPHP.class.php';
//security
iPHP::LoadClass("Security");
iS::filter();
iS::gp('page','GP',2);

define('__SELF__',	$_SERVER['PHP_SELF']);
define('__REF__', 	$_SERVER['HTTP_REFERER']);
define('__HOST__', 	$_SERVER['HTTP_HOST']);