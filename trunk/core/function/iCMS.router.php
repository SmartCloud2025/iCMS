<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: iCMS.push.php 148 2013-03-14 16:15:12Z coolmoo $
 */
function iCMS_router($vars){
	if(empty($vars['url'])){
		echo 'javascript:;';
		return;
	}
	$ukey = $vars['url'];
	unset($vars['url'],$vars['app']);
//	var_dump($vars);
	$url = iPHP::router($ukey,iCMS_REWRITE);
	if($url){
		echo iCMS::$config['router']['URL'].$url;		
	}else{
		echo '###';
	}
}