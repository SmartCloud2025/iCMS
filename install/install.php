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
require iPATH.'iPHP/iPHP.php';//iPHP框架文件
$lock_file = iPATH.'cache/install.lock';

file_exists($lock_file) && iPHP::alert('请先删除 cache/install.lock 这个文件。','js:top.callback();');

$action = $_POST['action'];
if($action=='install'){
	$db_host     = trim($_POST['DB_HOST']);
	$db_user     = trim($_POST['DB_USER']);
	$db_password = trim($_POST['DB_PASSWORD']);
	$db_name     = trim($_POST['DB_NAME']);
	$db_prefix   = iS::escapeStr(trim($_POST['DB_PREFIX']));
	$router_dir  = rtrim($_POST['ROUTER_DIR'],'/').'/';
	$router_url  = trim($_POST['ROUTER_URL'],'/');

	$admin_name     = trim($_POST['ADMIN_NAME']);
	$admin_password = trim($_POST['ADMIN_PASSWORD']);


	// define('iPHP_DB_HOST',$db_host);	// 服务器名或服务器ip,一般为localhost
	// define('iPHP_DB_USER',$db_user);		// 数据库用户
	// define('iPHP_DB_PASSWORD',$db_password);//数据库密码
	// define('iPHP_DB_NAME',$db_name);		// 数据库名
	// define('iPHP_DB_PREFIX',$db_prefix);	// 表名前缀, 同一数据库安装多个请修改此处

	// require iPATH.'iPHP/iPHP.php';//iPHP框架文件

	$db_host OR iPHP::alert("请填写数据库服务器地址",'js:top.callback("#DB_HOST");');
	$db_user OR iPHP::alert("请填写数据库用户名",'js:top.callback("#DB_USER");');
	$db_password OR iPHP::alert("请填写数据库密码",'js:top.callback("#DB_PASSWORD");');
	$db_name OR iPHP::alert("请填写数据库名",'js:top.callback("#DB_NAME");');
	strstr($db_prefix, '.') && iPHP::alert("您指定的数据表前缀包含点字符，请返回修改",'js:top.callback("#DB_PREFIX");');
	//preg_match('/([a-zA-z\_]+)/is', $db_prefix) OR iPHP::alert("您指定的数据表前缀包含非法字符，请返回修改",'js:top.callback("#DB_PREFIX");');

	$admin_name OR iPHP::alert("请填写超级管理员账号",'js:top.callback("#ADMIN_NAME");');
	$admin_password OR iPHP::alert("请填写超级管理员密码",'js:top.callback("#ADMIN_PASSWORD");');
	strlen($admin_password)<6 && iPHP::alert("请填写超级管理员密码",'js:top.callback("#ADMIN_PASSWORD");');


	$mysql_link = @mysql_connect($db_host,$db_user,$db_password);
	$mysql_link OR iPHP::alert("数据库连接出错",'js:top.callback();');
	mysql_query("SET NAMES '".iPHP_DB_CHARSET."'");
	@mysql_select_db($db_name,$mysql_link) OR iPHP::alert("数据库{$db_name}不存在",'js:top.callback("#DB_NAME");');

	$config  = iPATH.'config.php';
	$content = iFS::read($config,false);
	$content = preg_replace("/define\(\'iPHP_DB_HOST\',\'.*?\'\)/is", 		"define('iPHP_DB_HOST','$db_host')",     $content);
	$content = preg_replace("/define\(\'iPHP_DB_USER\',\'.*?\'\)/is", 		"define('iPHP_DB_USER','$db_user')", 	 $content);
	$content = preg_replace("/define\(\'iPHP_DB_PASSWORD\',\'.*?\'\)/is", 	"define('iPHP_DB_PASSWORD','$db_password')", $content);
	$content = preg_replace("/define\(\'iPHP_DB_NAME\',\'.*?\'\)/is", 		"define('iPHP_DB_NAME','$db_name')",     $content);
	$content = preg_replace("/define\(\'iPHP_DB_PREFIX\',\'.*?\'\)/is", 	"define('iPHP_DB_PREFIX','$db_prefix')",   $content);
	$content = preg_replace("/define\(\'iPHP_KEY\',\'.*?\'\)/is", 			"define('iPHP_KEY','".random(32)."')",$content);

	$parse   = parse_url($router_url);
	$host    = $parse['host'];
	preg_match("/[^\.\/][\w\-]+\.[^\.\/]+$/", $host, $matches);
	$domain  = $matches[0]?'.'.$matches[0]:"";
	$content = preg_replace("/define\(\'iPHP_COOKIE_DOMAIN\',\s*\'.*?\'\)/is","define('iPHP_COOKIE_DOMAIN','$domain')",$content);
	iFS::write($config,$content,false);
//开始安装 数据库

	$sql_file = 'iCMS.V6.sql';
	is_readable($sql_file) OR iPHP::alert('数据库文件不存在或者读取失败','js:top.callback();');
	//require_once ($config);
	$sql = iFS::read($sql_file);
	$sql = str_replace('#iCMS@__',$db_prefix,$sql);
	run_query($sql,$mysql_link);

//设置超级管理员
	$admin_password = md5($admin_password);
	mysql_query("
		UPDATE `{$db_prefix}members`
		SET `username` = '{$admin_name}', `password` = '{$admin_password}'
		WHERE `uid` = '1';
	");

//配置程序
	$result = mysql_query("SELECT * FROM `{$db_prefix}config` WHERE `tid`='0'",$mysql_link);
    while ($c = mysql_fetch_array($result)) {
        $value = $c['value'];
        strstr($c['value'], 'a:') && $value = unserialize($c['value']);
        $setting[$c['name']] = $value;
    }


	$setting['router']['URL']        = $router_url;
	$setting['router']['DIR']        = $router_dir;
	$setting['router']['public_url'] = $router_url.'/public';
	$setting['router']['user_url']   = $router_url.'/usercp';
	$setting['router']['404']        = $router_url.'/public/404.html';
	$setting['router']['tag_url']    = $router_url;

	$setting['FS']['url']            = $router_url.'/res/';

	$setting['template']['mobile']['domain']     = $router_url;
	$setting['template']['device'][0]['domain']  = $router_url;

	foreach($setting AS $n=>$v){
        is_array($v) && $v = addslashes(serialize($v));
        mysql_query("UPDATE `{$db_prefix}config` SET `value` = '$v' WHERE `tid` ='0' AND `name` ='$n'",$mysql_link);
	}

 	$output = "<?php\ndefined('iPHP') OR exit('Access Denied');\nreturn ";
	$output.= var_export($setting,true);
	$output.= ';';
	iFS::write(iPATH.'conf/iCMS/config.php',$output,false);
//写入数据库配置<hr />开始安装数据库<hr />数据库安装完成<hr />设置超级管理员<hr />更新网站缓存<hr />
	iFS::write($lock_file,'iCMS.'.time(),false);
	iFS::rmdir(iPATH.'install');
	iPHP::success("安装完成",'js:top.install.step4();');
}
function run_query($sql,$mysql_link) {
	$sql      = str_replace("\r", "\n", $sql);
	$resource = array();
	$num      = 0;
	$sql_array = explode(";\n", trim($sql));
    foreach($sql_array as $query) {
        $queries = explode("\n", trim($query));
        foreach($queries as $query) {
            $resource[$num] .= $query[0] == '#' ? '' : $query;
        }
        $num++;
    }
    unset($sql);
    foreach($resource as $key=>$query) {
        $query = trim($query);
        $query && mysql_query($query,$mysql_link);
    }
}

