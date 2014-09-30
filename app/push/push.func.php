<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: push.tpl.php 1392 2013-05-20 12:28:08Z coolmoo $
 */
function push_list($vars){
	$maxperpage = isset($vars['row'])?(int)$vars['row']:"100";
	$cache_time	= isset($vars['time'])?(int)$vars['time']:"-1";

    $where_sql	= "WHERE `status`='1'";

    isset($vars['userid'])    &&     $where_sql.=" AND `userid`='{$vars['userid']}'";

    if(isset($vars['cid!'])){
        $cids	= $vars['sub']?iCMS::get_category_ids($vars['cid!'],true):$vars['cid!'];
        $cids OR $cids	= $vars['cid!'];
        $where_sql.= iPHP::where($cids,'cid','not');
    }
    if(isset($vars['cid'])){
        $cids	= $vars['sub']?iCMS::get_category_ids($vars['cid'],true):$vars['cid'];
        $cids OR $cids	= $vars['cid'];
        $where_sql.= iPHP::where($cids,'cid');
    }
    isset($vars['pid']) 	&& $where_sql.= " AND `type` ='{$vars['pid']}'";
    isset($vars['pic']) 	&& $where_sql.= " AND `haspic`='1'";
    isset($vars['nopic']) 	&& $where_sql.= " AND `haspic`='0'";

	isset($vars['startdate'])    && $where_sql.=" AND `addtime`>='".strtotime($vars['startdate'])."'";
	isset($vars['enddate'])     && $where_sql.=" AND `addtime`<='".strtotime($vars['enddate'])."'";

	$by=$vars['by']=="ASC"?"ASC":"DESC";
    switch ($vars['orderby']) {
        case "id":		$orderSQL=" ORDER BY `id` $by";			break;
        case "addtime":	$orderSQL=" ORDER BY `addtime` $by";    break;
        case "disorder":$orderSQL=" ORDER BY `ordernum` $by";    break;
        default:        $orderSQL=" ORDER BY `id` DESC";
    }
	if($vars['cache']){
        $cache_name = 'push/'.md5($where_sql);
        $resource   = iCache::get($cache_name);
	}
	if(empty($resource)){
        $resource = iDB::all("SELECT * FROM `#iCMS@__push` {$where_sql} {$orderSQL} LIMIT $maxperpage");
		//iDB::debug(1);
        if($resource)foreach ($resource as $key => $value) {
            $value['pic']     && $value['pic']  = iFS::fp($value['pic'],'+http');
            $value['pic2']    && $value['pic2'] = iFS::fp($value['pic2'],'+http');
            $value['pic2']    && $value['pic2'] = iFS::fp($value['pic2'],'+http');
            $value['metadata']&& $value['metadata'] = unserialize($value['metadata']);
            $resource[$key] = $value;
        }
		$vars['cache'] && iCache::set($cache_name,$resource,$cache_time);
	}
	return $resource;
}
