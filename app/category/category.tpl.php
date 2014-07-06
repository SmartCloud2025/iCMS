<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: category.tpl.php 2379 2014-03-19 02:37:47Z coolmoo $
 */
function category_array($vars){
	$cid         = (int)$vars['cid'];
	return iPHP::app("category")->category($cid,false);
}
function category_list($vars){
	$appid     = isset($vars['appid'])?(int)$vars['appid']:iCMS_APP_ARTICLE;
	$row       = isset($vars['row'])?(int)$vars['row']:"100";
	$cacheTime = isset($vars['time'])?(int)$vars['time']:"-1";
	$status    = isset($vars['status'])?(int)$vars['status']:"1";
	$whereSQL  =" WHERE `appid`='$appid' AND `status`='$status'";

	if($vars['pid']){
		iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
		$map = new map(iCMS_APP_CATEGORY);
		$ids = $map->ids($vars['pid']);
//		$whereSQL.=" AND `pid` = '{$vars['pid']}'";
		$whereSQL.=" AND `cid` IN ($ids)";
	}
	isset($vars['mode']) && $whereSQL.=" AND `mode` = '{$vars['mode']}'";
	
	isset($vars['cid']) && !isset($vars['stype']) && $whereSQL.= iPHP::andSQL($vars['cid'],'cid');
	isset($vars['cid!']) && $whereSQL.= iPHP::andSQL($vars['cid!'],'cid','not');
	switch ($vars['stype']) {
		case "top":	
			$vars['cid'] && $whereSQL.= iPHP::andSQL($vars['cid'],'cid');
			$whereSQL.=" AND rootid='0'";
		break;
		case "sub":	
			$vars['cid'] && $whereSQL.=" AND `rootid` = '{$vars['cid']}'";
		break;
		case "subtop":	
			$vars['cid'] && $whereSQL.= iPHP::andSQL($vars['cid'],'cid');
		break;
		case "subone":	
			$whereSQL.= iPHP::andSQL(iCMS::getIds($vars['cid'],false),'cid');
		break;
		case "self":
			$parent=iCache::get('iCMS/category/parent',$vars['cid']);
			$whereSQL.=" AND `rootid`='$parent'";
		break;
	}
	if($vars['cache']){
		$cacheName	= 'category/'.md5($whereSQL);
		$rs			= iCache::get($cacheName);
	}
	if(empty($rs)){
		$rootidA= iCache::get('iCMS/category/rootid');
		$rs		= iDB::getArray("SELECT * FROM `#iCMS@__category`{$whereSQL} ORDER BY `orderNum`,`cid` ASC LIMIT $row");
		//iDB::debug(1);
		$_count	= count($rs);
		for ($i=0;$i<$_count;$i++){
			$rs[$i]['child'] = $rootidA[$rs[$i]['cid']]?true:false;
			$rs[$i]['url']   = iURL::get('category',$rs[$i])->href;
			$rs[$i]['link']  = "<a href='{$rs[$i]['url']}'>{$rs[$i]['name']}</a>";
	        if($rs[$i]['metadata']){
	        	$mdArray=array();
	        	$rs[$i]['metadata']=unserialize($rs[$i]['metadata']);
	        	foreach($rs[$i]['metadata'] AS $mdval){
	        		$mdArray[$mdval['key']]=$mdval['value'];
	        	}
	        	$rs[$i]['metadata']=$mdArray;
	        }
	        unset($rs[$i]['contentprop']);
		}
		$vars['cache'] && iCache::set($cacheName,$rs,$cacheTime);
	}
	return $rs;
}