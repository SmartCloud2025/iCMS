<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: category.tpl.php 2379 2014-03-19 02:37:47Z coolmoo $
 */
function category_array($vars){
	$cid = (int)$vars['cid'];
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

	if (stripos($vars['cid'],',') !== false){
		$vars['cid'] = explode(',', $vars['cid']);
	}
	if (stripos($vars['cid!'],',') !== false){
		$vars['cid!'] = explode(',', $vars['cid!']);
	}

	isset($vars['cid']) && !isset($vars['stype']) && $where_sql.= iPHP::where($vars['cid'],'cid');
	isset($vars['cid!']) && $where_sql.= iPHP::where($vars['cid!'],'cid','not');
	switch ($vars['stype']) {
		case "top":
			$vars['cid'] && $where_sql.= iPHP::where($vars['cid'],'cid');
			$where_sql.=" AND rootid='0'";
		break;
		case "sub":
			$vars['cid'] && $where_sql.= iPHP::where($vars['cid'],'rootid');
		break;
		// case "subtop":
		// 	$vars['cid'] && $where_sql.= iPHP::where($vars['cid'],'cid');
		// break;
		case "suball":
			$where_sql.= iPHP::where(iCMS::get_category_ids($vars['cid'],false),'cid');
		break;
		case "self":
			$parent=iCache::get('iCMS/category/parent',$vars['cid']);
			$where_sql.=" AND `rootid`='$parent'";
		break;
	}
	if(isset($vars['pids'])){
		iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
		map::init('prop',iCMS_APP_CATEGORY);
		$where_sql.= map::exists($vars['pids'],'`#iCMS@__category`.cid'); //主表小 map表大
//		$map_where=map::where($vars['pids']); //主表大 map表大
//		$map_ids    = map::ids($vars['pid']);
//		$map_sql    = map::sql($vars['pid']); //map 表小的用 in
//		$where_sql.=" AND `pid` = '{$vars['pid']}'";
		//if(empty($map_ids)) return $resource;
		//$where_sql.=" AND `cid` IN ($map_ids)";
		//$where_sql.=" AND `cid` IN ($map_sql)";
	}
	if($vars['cache']){
		$cache_name = iPHP_DEVICE.'/category/'.md5($where_sql);
		$resource   = iCache::get($cache_name);
	}
	if(empty($resource)){
		$rootid_array = iCache::get('iCMS/category/rootid');
		$resource     = iDB::all("SELECT * FROM `#iCMS@__category` {$where_sql} ORDER BY `ordernum`,`cid` ASC LIMIT $row");
		iPHP_SQL_DEBUG && iDB::debug(1);
		if($resource)foreach ($resource as $key => $value) {
			$value['child'] = $rootid_array[$value['cid']]?true:false;
			$value['url']   = iURL::get('category',$value)->href;
			$value['link']  = "<a href='{$value['url']}'>{$value['name']}</a>";
	        if($value['metadata']){
	        	$mdArray=array();
	        	$value['metadata']=unserialize($value['metadata']);
	        	foreach($value['metadata'] AS $mdval){
	        		$mdArray[$mdval['key']]=$mdval['value'];
	        	}
	        	$value['metadata']=$mdArray;
	        }
	        unset($value['contentprop']);
	        $resource[$key] = $value;
		}
		$vars['cache'] && iCache::set($cache_name,$resource,$cache_time);
	}
	return $resource;
}
