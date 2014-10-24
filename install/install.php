<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: install.php 2330 2014-01-03 05:19:07Z coolmoo $
*/
define('iPHP',TRUE);
define('iPHP_APP','iCMS'); //应用名
define('iPATH',dirname(strtr(__FILE__,'\\','/'))."/../");
//框架初始化
require iPATH.'iPHP/iPHP.php';//iPHP框架文件

$action = $_POST['action'];
if($action=='install'){
	$db_host     = trim($_POST['DB_HOST']);
	$db_user     = trim($_POST['DB_USER']);
	$db_password = trim($_POST['DB_PASSWORD']);
	$db_name     = trim($_POST['DB_NAME']);
	$db_prefix   = trim($_POST['DB_PREFIX']);

	$router_dir  = $_POST['ROUTER_DIR'];
	$router_url  = $_POST['ROUTER_URL'];

	$db_host OR iPHP::alert("请填写数据库服务器地址",'js:top.$("#DB_HOST").focus();');
	$db_user OR iPHP::alert("请填写数据库用户名",'js:top.$("#DB_USER").focus();');
	$db_password OR iPHP::alert("请填写数据库密码",'js:top.$("#DB_PASSWORD").focus();');
	$db_name OR iPHP::alert("请填写数据库名",'js:top.$("#DB_NAME").focus();');
	strstr($db_prefix, '.') && iPHP::alert("您指定的数据表前缀包含点字符，请返回修改");

	$link = @mysql_connect($db_host,$db_user,$db_password);
	$link OR iPHP::alert("数据库连接出错");
	@mysql_select_db($db_name,$link) OR iPHP::alert("数据库{$db_name}不存在");


	$config_file = iPATH.'config.php';
	$content     = iFS::read($config_file,false);
	$content     = preg_replace("/define\(\'iPHP_DB_NAME\',\s*\'.*?\'\)/is", 		"define('iPHP_DB_NAME',    '$db_name')",     $content);
	$content     = preg_replace("/define\(\'iPHP_DB_USER\',\s*\'.*?\'\)/is", 		"define('iPHP_DB_USER',    '$db_user')", 	 $content);
	$content     = preg_replace("/define\(\'iPHP_DB_PASSWORD\',\s*\'.*?\'\)/is", 	"define('iPHP_DB_PASSWORD','$db_password')", $content);
	$content     = preg_replace("/define\(\'iPHP_DB_HOST\',\s*\'.*?\'\)/is", 		"define('iPHP_DB_HOST',    '$db_host')",     $content);
	$content     = preg_replace("/define\(\'iPHP_DB_PREFIX\',\s*\'.*?\'\)/is", 		"define('iPHP_DB_PREFIX',  '$db_prefix')",   $content);
	$content     = preg_replace("/define\(\'iPHP_KEY\',\s*\'.*?\'\)/is", 			"define('iPHP_KEY',        '".random(32)."')",$content);
	$content     = preg_replace("/define\(\'iPHP_KEY\',\s*\'.*?\'\)/is", 			"define('iPHP_KEY',        '".random(32)."')",$content);
	$parse = parse_url($router_url);
	$host  = $parse['host'];
	preg_match("/[^\.\/][\w\-]+\.[^\.\/]+$/", $host, $matches);
	$domain  = $matches[0]?'.'.$matches[0]:"";
	$content = preg_replace("/define\(\'iPHP_COOKIE_DOMAIN\',\s*\'.*?\'\)/is", 			"define('iPHP_COOKIE_DOMAIN','$domain')",$content);
	iFS::write($config_file,$content,false);
	require_once ($config_file);
	require_once (iPATH.'install/sql_map.php');
}
