<?php
/**
* iPHP - i PHP Framework
* Copyright (c) 2012 iiiphp.com. All rights reserved.
*
* @author coolmoo <iiiphp@qq.com>
* @site http://www.iiiphp.com
* @licence http://www.iiiphp.com/license
* @version 1.0.1
* @package iRouter
* @$Id: iRouter.class.php 2408 2014-04-30 18:58:23Z coolmoo $
*/
class iRouter {
	public static $config	= null;
	public static $uriConfig= null;
	public static function init($config){
		self::$config	= $config;
	}

}
