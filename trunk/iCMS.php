<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* $Id: iCMS.php 2408 2014-04-30 18:58:23Z coolmoo $
*/
define('iCMS',TRUE); //应用名
define('iPATH',dirname(strtr(__FILE__,'\\','/'))."/");
//框架初始化
require iPATH.'config.php';		//框架初始化配置
require iPATH.'iPHP.php';		//iPHP框架文件

iPHP::loadClass("Mysql");		//加载数据库操作类
iPHP::loadClass("FileSystem");	//加载文件操作类
iPHP::loadClass('Cache');		//加载缓存操作类
iPHP::loadClass("Template");	//加载模板操作类
//iPHP::loadClass("Router");		//加载URL路由

require iPHP_APP_CORE.'/iCMS.define.php';
require iPHP_APP_CORE.'/iCMS.version.php';
require iPHP_APP_CORE.'/iCMS.class.php';
require iPHP_APP_CORE.'/iRouter.class.php';

iCMS::init();
iPHP_DEBUG      && iDB::$show_errors = true;
iPHP_TPL_DEBUG  && iPHP::clear_compiled_tpl();
