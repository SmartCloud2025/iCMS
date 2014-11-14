<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: favorite.tpl.php 2408 2014-04-30 18:58:23Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');

iPHP::app('user.class','static');

function favorite_list($vars=null){
	$maxperpage = isset($vars['row'])?(int)$vars['row']:"10";
	$where_sql  = "WHERE 1=1 ";
	isset($vars['userid'])&& $where_sql .= " AND `uid`='".(int)$vars['userid']."' ";
	isset($vars['fid'])   && $where_sql .= " AND `fid`='".(int)$vars['fid']."' ";
	isset($vars['mode'])  && $where_sql .= " AND `mode`='".(int)$vars['mode']."'";
	isset($vars['appid']) && $where_sql .= " AND `appid`='".(int)$vars['appid']."' ";

	$cache_time	= isset($vars['time'])?(int)$vars['time']:-1;
	$by=$vars['by']=="ASC"?"ASC":"DESC";
	switch ($vars['orderby']) {
		case 'hot':
			$order_sql = " ORDER BY `count` $by";
			break;
		default: $order_sql = " ORDER BY `id` $by";
	}

	$md5	= md5($where_sql.$order_sql);
	$offset	= 0;
	if($vars['page']){
		$total	= iPHP::total($md5,"SELECT count(*) FROM `#iCMS@__favorite` {$where_sql} ");
		iPHP::assign("fav_total",$total);
        $multi	= iCMS::page(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>iPHP::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
        $offset	= $multi->offset;
	}
	if($vars['cache']){
		$cache_name = iPHP_DEVICE.'/favorite/'.$md5."/".(int)$GLOBALS['page'];
		$resource   = iCache::get($cache_name);
	}
	if(empty($resource)){
		$rs  = iDB::all("SELECT * FROM `#iCMS@__favorite` {$where_sql} {$order_sql} LIMIT {$offset},{$maxperpage}");
		iPHP_SQL_DEBUG && iDB::debug(1);
		$resource = array();
		$vars['user'] && iPHP::app('user.class','static');
		if($rs)foreach ($rs as $key => $value) {
			$value['url']  = iPHP::router(array('/favorite/{id}/',$value['id']),iCMS_REWRITE);
			$vars['user'] && $value['user'] = user::info($value['uid'],$value['nickname']);
			if(isset($vars['loop'])){
				$resource[$key] = $value;
			}else{
				$resource[$value['id']]=$value;
			}
		}
		$vars['cache'] && iCache::set($cache_name,$resource,$cache_time);
	}
	return $resource;
}
function favorite_data($vars=null){
	$maxperpage = isset($vars['row'])?(int)$vars['row']:"10";
	$where_sql  = "WHERE 1=1 ";
	isset($vars['userid'])&& $where_sql .= " AND `uid`='".(int)$vars['userid']."' ";
	$vars['fid']          && $where_sql .= " AND `fid`='".(int)$vars['fid']."' ";
	isset($vars['appid']) && $where_sql .= " AND `appid`='".(int)$vars['appid']."' ";

	$cache_time	= isset($vars['time'])?(int)$vars['time']:-1;
	$by=$vars['by']=="ASC"?"ASC":"DESC";
	switch ($vars['orderby']) {
		default: $order_sql = " ORDER BY `id` $by";
	}

	$md5	= md5($where_sql.$order_sql);
	$offset	= 0;
	if($vars['page']){
		$total	= iPHP::total($md5,"SELECT count(*) FROM `#iCMS@__favorite_data` {$where_sql} ");
		iPHP::assign("fav_data_total",$total);
        $multi	= iCMS::page(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>iPHP::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
        $offset	= $multi->offset;
	}
	if($vars['cache']){
		$cache_name = 'favorite_data/'.$md5."/".(int)$GLOBALS['page'];
		$resource   = iCache::get($cache_name);
	}
	if(empty($resource)){
		$resource  = iDB::all("SELECT * FROM `#iCMS@__favorite_data` {$where_sql} {$order_sql} LIMIT {$offset},{$maxperpage}");
		iPHP_SQL_DEBUG && iDB::debug(1);
		// $resource = array();
		// if($rs)foreach ($rs as $key => $value) {
		// }
		$vars['cache'] && iCache::set($cache_name,$resource,$cache_time);
	}
	return $resource;
}
