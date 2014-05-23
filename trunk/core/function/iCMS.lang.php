<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: iCMS.push.php 148 2013-03-14 16:15:12Z coolmoo $
 */
function iCMS_lang($vars){
	if(empty($vars['key']))return;
	
	echo iPHP::lang($vars['key']);
}