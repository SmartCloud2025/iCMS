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
	$appid      = isset($vars['appid'])?(int)$vars['appid']:iCMS_APP_ARTICLE;
	$row        = isset($vars['row'])?(int)$vars['row']:"100";
	$cache_time = isset($vars['time'])?(int)$vars['time']:"-1";
	$status     = isset($vars['status'])?(int)$vars['status']:"1";
	$where_sql  =" WHERE `appid`='$appid' AND `status`='$status'";
	$resource   = array();
	isset($vars['mode']) && $where_sql.=" AND `mode` = '{$vars['mode']}'";	
	isset($vars['cid']) && !isset($vars['stype']) && $where_sql.= iPHP::andSQL($vars['cid'],'cid');
	isset($vars['cid!']) && $where_sql.= iPHP::andSQL($vars['cid!'],'cid','not');
	switch ($vars['stype']) {
		case "top":	
			$vars['cid'] && $where_sql.= iPHP::andSQL($vars['cid'],'cid');
			$where_sql.=" AND rootid='0'";
		break;
		case "sub":	
			$vars['cid'] && $where_sql.=" AND `rootid` = '{$vars['cid']}'";
		break;
		case "subtop":	
			$vars['cid'] && $where_sql.= iPHP::andSQL($vars['cid'],'cid');
		break;
		case "subone":	
			$where_sql.= iPHP::andSQL(iCMS::getIds($vars['cid'],false),'cid');
		break;
		case "self":
			$parent=iCache::get('iCMS/category/parent',$vars['cid']);
			$where_sql.=" AND `rootid`='$parent'";
		break;
	}
	if(isset($vars['pid'])){
		iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
		map::$table = 'prop';
		map::$appid = iCMS_APP_CATEGORY;
//		$map_ids    = map::ids($vars['pid']);		
//		$map_sql    = map::sql($vars['pid']); //map 表小的用 in
		$where_sql.= map::exists($vars['pid'],'`#iCMS@__category`.cid'); //map 表大的用exists

//		$where_sql.=" AND `pid` = '{$vars['pid']}'";
		//if(empty($map_ids)) return $resource;
		//$where_sql.=" AND `cid` IN ($map_ids)";
		//$where_sql.=" AND `cid` IN ($map_sql)";
	}
	if($vars['cache']){
		$cache_name = 'category/'.md5($where_sql);
		$resource   = iCache::get($cache_name);
	}
	if(empty($resource)){
		$rootid_array = iCache::get('iCMS/category/rootid');
		$resource     = iDB::getArray("SELECT * FROM `#iCMS@__category` {$where_sql} ORDER BY `orderNum`,`cid` ASC LIMIT $row");
		iDB::debug(1);
		$_count	= count($resource);
		for ($i=0;$i<$_count;$i++){
			$resource[$i]['child'] = $rootid_array[$resource[$i]['cid']]?true:false;
			$resource[$i]['url']   = iURL::get('category',$resource[$i])->href;
			$resource[$i]['link']  = "<a href='{$resource[$i]['url']}'>{$resource[$i]['name']}</a>";
	        if($resource[$i]['metadata']){
	        	$mdArray=array();
	        	$resource[$i]['metadata']=unserialize($resource[$i]['metadata']);
	        	foreach($resource[$i]['metadata'] AS $mdval){
	        		$mdArray[$mdval['key']]=$mdval['value'];
	        	}
	        	$resource[$i]['metadata']=$mdArray;
	        }
	        unset($resource[$i]['contentprop']);
		}
		$vars['cache'] && iCache::set($cache_name,$resource,$cache_time);
	}
	return $resource;
}