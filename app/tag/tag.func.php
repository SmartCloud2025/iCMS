<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: tag.tpl.php 159 2013-03-23 04:11:53Z coolmoo $
 */
function tag_list($vars){
	$where_sql ="WHERE status='1' ";
	$map_where = array();
	if(isset($vars['tcid'])){
        iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
        map::init('category',iCMS_APP_TAG);
        //$where_sql.= map::exists($vars['tcid'],'`#iCMS@__tags`.id'); //map 表大的用exists
        $map_where+=map::where($vars['tcid']);
	}

    if($vars['pid'] && !isset($vars['pids'])){
        $where_sql.= iPHP::where($vars['pid'],'pid');
    }
	if(isset($vars['pids']) && !$vars['pid']){
        iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
        map::init('prop',iCMS_APP_TAG);
        //$where_sql.= map::exists($vars['pids'],'`#iCMS@__tags`.id'); //map 表大的用exists
        $map_where+= map::where($vars['pids']);
	}

    // if(isset($vars['cid!'])){
    // 	$ncids    = explode(',',$vars['cid!']);
    //     $vars['sub'] && $ncids+=iCMS::get_category_ids($ncids,true);
    //     $where_sql.= iPHP::where($ncids,'cid','not');
    // }
    if($vars['cid'] && !isset($vars['cids'])){
        $cid = explode(',',$vars['cid']);
        $vars['sub'] && $cid+=iCMS::get_category_ids($cid,true);
        $where_sql.= iPHP::where($cid,'cid');
    }
    if(isset($vars['cids']) && !$vars['cid']){
        $cids = explode(',',$vars['cids']);
        $vars['sub'] && $cids+=iCMS::get_category_ids($vars['cids'],true);

        if($cids){
            iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
            map::init('category',iCMS_APP_TAG);
            $map_where+=map::where($cids);
        }
    }
	$maxperpage	= isset($vars['row'])?(int)$vars['row']:"10";
	$cache_time	= isset($vars['time'])?(int)$vars['time']:-1;
	$by			= $vars['by']=='ASC'?"ASC":"DESC";
	switch ($vars['orderby']) {
		case "hot":		$order_sql=" ORDER BY `count` $by";		break;
		case "new":		$order_sql=" ORDER BY `id` $by";			break;
		case "order":	$order_sql=" ORDER BY `ordernum` $by";	break;
//		case "rand":	$order_sql=" ORDER BY rand() $by";		break;
		default:		$order_sql=" ORDER BY `id` $by";
	}
    if($map_where){
        $map_sql   = iCMS::map_sql($map_where);
        $where_sql = ",({$map_sql}) map {$where_sql} AND `id` = map.`iid`";
    }

	$offset	= 0;
	$limit  = "LIMIT {$maxperpage}";
	if($vars['page']){
		$total	= iPHP::total('sql.md5',"SELECT count(*) FROM `#iCMS@__tags` {$where_sql} ");
		iPHP::assign("tags_total",$total);
		$multi  = iCMS::page(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>iPHP::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
		$offset = $multi->offset;
		$limit  = "LIMIT {$offset},{$maxperpage}";
        iPHP::assign("tags_list_total",$total);
	}
	$hash = md5($where_sql.$order_sql.$limit);

	if($vars['cache']){
		$cache_name = iPHP_DEVICE.'/tags/'.$md5."/".(int)$GLOBALS['page'];
		$resource   = iCache::get($cache_name);
	}
    if($map_sql || $offset){
        if($vars['cache']){
			$map_cache_name = iPHP_DEVICE.'/tags_map/'.$hash;
			$ids_array      = iCache::get($map_cache_name);
        }
        if(empty($ids_array)){
            $ids_array = iDB::all("SELECT `id` FROM `#iCMS@__tags` {$where_sql} {$order_sql} {$limit}");
            iPHP_SQL_DEBUG && iDB::debug(1);
            $vars['cache'] && iCache::set($map_cache_name,$ids_array,$cache_time);
        }
        //iDB::debug(1);
        $ids       = iCMS::get_ids($ids_array);
        $ids       = $ids?$ids:'0';
        $where_sql = "WHERE `id` IN({$ids})";
        $limit     = '';
    }
    if($vars['cache']){
        $cache_name = iPHP_DEVICE.'/tags/'.$hash;
        $resource   = iCache::get($cache_name);
    }
	if(empty($resource)){
		$resource = iDB::all("SELECT * FROM `#iCMS@__tags` {$where_sql} {$order_sql} {$limit}");
		iPHP_SQL_DEBUG && iDB::debug(1);
		$resource = __tag_array($vars,$resource);
		$vars['cache'] && iCache::set($cache_name,$resource,$cache_time);
	}

	return $resource;
}

function __tag_array($vars,$resource){
	$tagApp = iPHP::app("tag");
    if($resource)foreach ($resource as $key => $value) {
		$resource[$key] = $tagApp->value($value);
    }
    return $resource;
}
