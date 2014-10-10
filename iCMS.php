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
define('iPHP',TRUE);
define('iPHP_APP','iCMS'); //应用名
define('iPATH',dirname(strtr(__FILE__,'\\','/'))."/");
//框架初始化
require iPATH.'config.php';			//框架初始化配置
require iPATH.'iPHP/iPHP.php';		//iPHP框架文件
require iPHP_APP_CORE.'/iCMS.define.php';
require iPHP_APP_CORE.'/iCMS.version.php';
require iPHP_APP_CORE.'/iCMS.class.php';
require iPHP_APP_CORE.'/iCMS.func.php';
require iPHP_APP_CORE.'/iURL.class.php';

iCMS::init();
