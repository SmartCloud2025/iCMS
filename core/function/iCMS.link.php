<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
function iCMS_link($vars){
	$limit 		= isset($vars['row'])?(int)$vars['row']:"100";
	$cacheTime 	= isset($vars['time'])?(int)$vars['time']:-1;
	
	switch($vars['type']){
		case "text":$sql[]=" `logo`='' ";break;
		case "logo":$sql[]=" `logo`!='' ";break;
	}
	isset($vars['sortid']) && $sql[]=" sortid='".$vars['sortid']."'";
	$sql && $where ='WHERE '.implode(' AND ',$sql);
	$iscache	= true;
	if($vars['cache']==false||isset($vars['page'])){
		$iscache= false;
		$rs 	= '';
	}else{
		$cacheName	= 'links/'.md5($sql);
		$rs			= iCache::get($cacheName);
	}
	if(empty($rs)){
		$rs=iDB::all("SELECT * FROM `#iCMS@__links`{$where} ORDER BY orderNum ASC,id ASC LIMIT 0 , $limit");
		$iscache && iCache::set($cacheName,$rs,$cacheTime);
	}
	return $rs;
}