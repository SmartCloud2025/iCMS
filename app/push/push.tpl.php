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
	$cacheTime	= isset($vars['time'])?(int)$vars['time']:"-1";

    $whereSQL	= " WHERE `status`='1'";

    isset($vars['userid'])    &&     $whereSQL.=" AND `userid`='{$vars['userid']}'";

    if(isset($vars['cid!'])){
        $cids	= $vars['sub']?iCMS::get_category_ids($vars['cid!'],true):$vars['cid!'];
        $cids OR $cids	= $vars['cid!'];
        $whereSQL.= iPHP::where($cids,'cid','not');
    }
    if(isset($vars['cid'])){
        $cids	= $vars['sub']?iCMS::get_category_ids($vars['cid'],true):$vars['cid'];
        $cids OR $cids	= $vars['cid'];
        $whereSQL.= iPHP::where($cids,'cid');
    }
    isset($vars['pid']) 	&& $whereSQL.= " AND `type` ='{$vars['pid']}'";
    isset($vars['pic']) 	&& $whereSQL.= " AND `ispic`='1'";
    isset($vars['nopic']) 	&& $whereSQL.= " AND `ispic`='0'";

	isset($vars['startdate'])    && $whereSQL.=" AND `addtime`>='".strtotime($vars['startdate'])."'";
	isset($vars['enddate'])     && $whereSQL.=" AND `addtime`<='".strtotime($vars['enddate'])."'";
	
	$by=$vars['by']=="ASC"?"ASC":"DESC";
    switch ($vars['orderby']) {
        case "id":		$orderSQL=" ORDER BY `id` $by";			break;
        case "addtime":	$orderSQL=" ORDER BY `addtime` $by";    break;
        case "disorder":$orderSQL=" ORDER BY `orderNum` $by";    break;
        default:        $orderSQL=" ORDER BY `id` DESC";
    }
	if($vars['cache']){
		$cacheName	= 'push/'.md5($whereSQL);
		$rs			= iCache::get($cacheName);
	}
	if(empty($rs)){
		$rs		= iDB::getArray("SELECT * FROM `#iCMS@__push`{$whereSQL} {$orderSQL} LIMIT $maxperpage");
		//echo iDB::$last_query;
        $_count	= count($rs);
        for ($i=0;$i<$_count;$i++){
			$rs[$i]['pic'] && $rs[$i]['pic']=iFS::fp($rs[$i]['pic'],'+http');
			$rs[$i]['pic2'] && $rs[$i]['pic2']=iFS::fp($rs[$i]['pic2'],'+http');
			$rs[$i]['pic2'] && $rs[$i]['pic2']=iFS::fp($rs[$i]['pic2'],'+http');
			$rs[$i]['metadata'] && $rs[$i]['metadata']=unserialize($rs[$i]['metadata']);
        }
		$vars['cache'] && iCache::set($cacheName,$rs,$cacheTime);
	}
	return $rs;
}