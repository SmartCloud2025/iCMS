<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: tag.tpl.php 159 2013-03-23 04:11:53Z coolmoo $
 */
function tag_list($vars){
	$where_sql=" status='1'";

	if(isset($vars['tcid'])){
        iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
        map::init('category',iCMS_APP_TAG);
        $where_sql.= map::exists($vars['tcid'],'`#iCMS@__tags`.id'); //map 表大的用exists
	}
	if(isset($vars['pid'])){
        iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
        map::init('prop',iCMS_APP_TAG);
        $where_sql.= map::exists($vars['pid'],'`#iCMS@__tags`.id'); //map 表大的用exists
	}

    if(isset($vars['cid!'])){
    	$ncids    = explode(',',$vars['cid!']);
        $vars['sub'] && $ncids+=iCMS::get_category_ids($ncids,true);
        $where_sql.= iPHP::where($ncids,'cid','not');
    }
    if(isset($vars['cid'])){
        $cid = explode(',',$vars['cid']);
        $vars['sub'] && $cid+=iCMS::get_category_ids($cid,true);
        $where_sql.= iPHP::where($cid,'cid');
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
	$md5	= md5($where_sql.$order_sql);
	$offset	= 0;
	if($vars['page']){
		$total	= iPHP::total($md5,"SELECT count(*) FROM `#iCMS@__tags` WHERE {$where_sql} ");
		iPHP::assign("tags_total",$total);
        $multi	= iCMS::page(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>iPHP::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
        $offset	= $multi->offset;
	}
	if($vars['cache']){
		$cache_name = 'tags/'.$md5."/".(int)$GLOBALS['page'];
		$resource   = iCache::get($cache_name);
	}
	if(empty($resource)){
		$resource = iDB::all("SELECT * FROM `#iCMS@__tags` WHERE {$where_sql} {$order_sql} LIMIT {$offset},{$maxperpage}");
		//iDB::debug(1);
		$resource = __tag_array($vars,$resource);
		$vars['cache'] && iCache::set($cache_name,$resource,$cache_time);
	}

	return $resource;
}
function tag_flist($vars){
	$where_sql=" status='1'";

	isset($vars['tcid']) 	&& $where_sql.=" AND `tcid`='".(int)$vars['tcid']."'";
	isset($vars['pid']) 	&& $where_sql.=" AND `pid`='".(int)$vars['pid']."'";

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
	$md5	= md5($where_sql.$order_sql);
	$offset	= 0;
	if($vars['page']){
		$total	= iPHP::total($md5,"SELECT count(*) FROM `#iCMS@__ftags` WHERE {$where_sql} ");
		iPHP::assign("tags_total",$total);
        $multi	= iCMS::page(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>iPHP::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
        $offset	= $multi->offset;
	}
	if($vars['cache']){
		$cache_name = 'tags/'.$md5."/".(int)$GLOBALS['page'];
		$resource   = iCache::get($cache_name);
	}
	if(empty($resource)){
		iPHP::app('tag.class','static');
		$resource = iDB::all("SELECT * FROM `#iCMS@__ftags` WHERE {$where_sql} {$order_sql} LIMIT {$offset},{$maxperpage}");
		$resource = __tag_array($vars,$resource);
		$vars['cache'] && iCache::set($cache_name,$resource,$cache_time);
	}

	return $resource;
}
function tag_search($vars){
	$SPH	= iCMS::sphinx();
	$SPH->init();
	$SPH->SetArrayResult(true);
	if(isset($vars['weights'])){
		//weights='title:100,tags:80,keywords:60,name:50'
		$wa=explode(',',$vars['weights']);
		foreach($wa AS $wk=>$wv){
			$waa=explode(':',$wv);
			$FieldWeights[$waa[0]]=$waa[1];
		}
		$SPH->SetFieldWeights($FieldWeights);
	}
	$page		= (int)$_GET['page'];
    $maxperpage	= isset($vars['row'])?(int)$vars['row']:10;
	$start 		= ($page && isset($vars['page']))?($page-1)*$maxperpage:0;
	$SPH->SetMatchMode(SPH_MATCH_EXTENDED);
	if($vars['mode']){
		$vars['mode']=="SPH_MATCH_BOOLEAN" && $SPH->SetMatchMode(SPH_MATCH_BOOLEAN);
		$vars['mode']=="SPH_MATCH_ANY" && $SPH->SetMatchMode(SPH_MATCH_ANY);
		$vars['mode']=="SPH_MATCH_PHRASE" && $SPH->SetMatchMode(SPH_MATCH_PHRASE);
		$vars['mode']=="SPH_MATCH_ALL" && $SPH->SetMatchMode(SPH_MATCH_ALL);
		$vars['mode']=="SPH_MATCH_EXTENDED" && $SPH->SetMatchMode(SPH_MATCH_EXTENDED);
	}

    if(isset($vars['cid'])){
    	$cids	= $vars['sub']?iCMS::get_category_ids($vars['cid'],true):$vars['cid'];
    	$cids OR $cids = (array)$vars['cid'];
        $cids	= array_map("intval", $cids);
		$SPH->SetFilter('cid',$cids);
    }
    if(isset($vars['startdate'])){
		$startime = strtotime($vars['startdate']);
		$enddate  = empty($vars['enddate'])?time():strtotime($vars['enddate']);
    	$SPH->SetFilterRange('pubdate',$startime,$enddate);
    }
	$SPH->SetLimits($start,$maxperpage,10000);

	$orderBy  = '@id DESC, @weight DESC';
	$order_sql = ' order by id DESC';

	$vars['orderBy'] && $orderBy	= $vars['orderBy'];
	$vars['order_sql']&& $order_sql= ' order by '.$vars['order_sql'];

	$vars['pic'] && $SPH->SetFilter('haspic',array(1));
	$vars['id!'] && $SPH->SetFilter('@id',array($vars['id!']),true);

	$SPH->setSortMode(SPH_SORT_EXTENDED,$orderBy);

	$query = $vars['q'];
	$vars['acc']&& 	$query	= '"'.$vars['q'].'"';
	$vars['@']  && 	$query	= '@('.$vars['@'].') '.$query;

	$res = $SPH->Query($query,"ladyband_tag");

	if (is_array($res["matches"])){
		foreach ( $res["matches"] as $docinfo ){
			$tid[] = $docinfo['id'];
		}
		$tids=implode(',',(array)$tid);
	}
	if(empty($tids)) return;

	$where_sql = " `id` in($tids)";
	$offset    = 0;
	if($vars['page']){
		$total   = $res['total'];
		$pagenav = isset($vars['pagenav'])?$vars['pagenav']:"pagenav";
		$pnstyle = isset($vars['pnstyle'])?$vars['pnstyle']:0;
		$multi   = iCMS::page(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>iPHP::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
		$offset  = $multi->offset;
	}
	$resource = iDB::all("SELECT * FROM `#iCMS@__tags` WHERE {$where_sql} {$order_sql} LIMIT {$maxperpage}");
	$resource = __tag_array($vars,$resource);
	return $resource;
}
function __tag_array($vars,$resource){
	$tagApp = iPHP::app("tag");
    if($resource)foreach ($resource as $key => $value) {
		$resource[$key] = $tagApp->value($value);
    }
    return $resource;
}
