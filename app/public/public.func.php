<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: public.tpl.php 1392 2013-05-20 12:28:08Z coolmoo $
 */
function public_ui($vars=null){
	isset($vars['script']) OR $vars['script'] = true;
	iPHP::assign("ui",$vars);
	echo iPHP::view('iCMS://public.ui.htm');
}
function public_common($vars=null){
	echo iPHP::view('iCMS://public.common.htm');
}
function public_crontab(){
	echo '<img src="'.iCMS_API.'?app=public&do=crontab&'.$_SERVER['REQUEST_TIME'].'" id="iCMS_public_crontab"/>';
}
