<?php
/**
* iPHP - i PHP Framework
* Copyright (c) 2012 iiiphp.com. All rights reserved.
*
* @author coolmoo <iiiphp@qq.com>
* @site http://www.iiiphp.com
* @licence http://www.iiiphp.com/license
* @version 1.0.1
 */
if (!function_exists('get_magic_quotes_gpc')) {
	function get_magic_quotes_gpc(){
		return false;
	}
}
if (!function_exists('gc_collect_cycles')) {
	function gc_collect_cycles(){
		return false;
	}
}