<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: iCMS.prop.php 179 2013-03-29 03:21:28Z coolmoo $
 */
function iCMS_prop($vars){
	$type	= $vars['type'];
	$field	= $vars['field'];
	$cid	= $vars['cid'];
	$pkey	= $type.'.'.$field;
	$cid &&	$pkey	= 'c'.$cid.'.'.$type.'.'.$field;
	$propArray 	= iCache::get("system/prop/{$pkey}");
	$propArray && sort($propArray);
	$offset		= $vars['start']?$vars['start']:0;
	$vars['row'] && $propArray = array_slice($propArray, 0, $vars['row']);
	return $propArray;
}