<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: article.tpl.php 2408 2014-04-30 18:58:23Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');

iPHP::app('tag.class','include');
function article_list($vars){
    if($vars['loop']==="rel" && empty($vars['id'])){
        return false;
    }
    $resource  = array();
    $status    = '1';
    isset($vars['status']) && $status = (int)$vars['status'];
    $where_sql = " `status`='{$status}'";
    $vars['call'] =='user'  && $where_sql.= " AND `postype`='0'";
    $vars['call'] =='admin' && $where_sql.= " AND `postype`='1'";
    $hidden    = iCache::get('iCMS/category/hidden');
    $hidden &&  $where_sql.=iPHP::where($hidden,'cid','not');
    $maxperpage = isset($vars['row'])?(int)$vars['row']:10;
    $cache_time = isset($vars['time'])?(int)$vars['time']:-1;
    isset($vars['userid'])&& $where_sql.= " AND `userid`='{$vars['userid']}'";
    isset($vars['top'])   && $where_sql.= " AND `top`='"._int($vars['top'])."'";


    if(isset($vars['cid!'])){
    	$ncids    = $vars['cid!'];
    	if($vars['sub']){
        	$ncids	= iCMS::get_category_ids($vars['cid!'],true);
        	array_push ($ncids,$vars['cid!']);
        }
        $where_sql.= iPHP::where($ncids,'cid','not');
    }
    if($vars['cid'] && !isset($vars['cids'])){
        $cid    = $vars['cid'];
        if($vars['sub']){
            $cid  = iCMS::get_category_ids($vars['cid'],true);
            array_push ($cid,$vars['cid']);
        }
        $where_sql.= iPHP::where($cid,'cid');
    }
    if(isset($vars['cids']) && !$vars['cid']){
        $cids = $vars['cids'];
        if($vars['sub']){
            $cids  = iCMS::get_category_ids($vars['cids'],true);
            array_push ($cids,$vars['cids']);
        }
        if($cids){
            iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
            map::init('category',iCMS_APP_ARTICLE);
            $where_sql.= map::exists($cids,'`#iCMS@__article`.id'); //map 表大的用exists
        }
    }
    if(isset($vars['pid'])){
        iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
        map::init('prop',iCMS_APP_ARTICLE);
        $where_sql.= map::exists($vars['pid'],'`#iCMS@__article`.id'); //map 表大的用exists
    }

    $vars['id'] && $where_sql.= iPHP::where($vars['id'],'id');
    $vars['id!']&& $where_sql.= iPHP::where($vars['id!'],'id','not');
    $by=$vars['by']=="ASC"?"ASC":"DESC";
    isset($vars['pic'])  && $where_sql.= " AND `haspic`='1'";
    isset($vars['nopic'])&& $where_sql.= " AND `haspic`='0'";

    switch ($vars['orderby']) {
        case "id":          $order_sql =" ORDER BY `id` $by";            break;
        case "hot":         $order_sql =" ORDER BY `hits` $by";          break;
        case "comment":     $order_sql =" ORDER BY `comments` $by";      break;
        case "pubdate":     $order_sql =" ORDER BY `pubdate` $by";       break;
        case "disorder":    $order_sql =" ORDER BY `orderNum` $by";      break;
        case "rand":        $order_sql =" ORDER BY rand() $by";          break;
        case "top":         $order_sql =" ORDER BY `top`,`orderNum` ASC";break;
        default:            $order_sql =" ORDER BY `id` DESC";
    }
    isset($vars['startdate'])&& $where_sql .= " AND `pubdate`>='".strtotime($vars['startdate'])."'";
    isset($vars['enddate'])  && $where_sql .= " AND `pubdate`<='".strtotime($vars['enddate'])."'";
    isset($vars['where'])    && $where_sql .= $vars['where'];
    $md5    = md5($where_sql.$order_sql.$maxperpage);
    $offset = 0;
    $limit  = "LIMIT {$maxperpage}";
    if($vars['page']){
        $total_type = $vars['total_cache']?$vars['total_cache']:null;
        $total      = iPHP::total($md5,"SELECT count(*) FROM `#iCMS@__article` WHERE {$where_sql}",$total_type);
        $pagenav    = isset($vars['pagenav'])?$vars['pagenav']:"pagenav";
        $pnstyle    = isset($vars['pnstyle'])?$vars['pnstyle']:0;
        $multi      = iCMS::page(array('total_type'=>$total_type,'total'=>$total,'perpage'=>$maxperpage,'unit'=>iPHP::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
        $offset     = $multi->offset;
        $limit      = "LIMIT {$offset},{$maxperpage}";
        // if($offset>1000){
        //     $where_sql.=" AND `id` >= (SELECT `id` FROM `#iCMS@__article` WHERE {$where_sql} {$order_sql} LIMIT {$offset},1)";
        //     $limit  = "LIMIT {$maxperpage}";
        // }
        iPHP::assign("article_list_total",$total);
    }
    if($vars['cache']){
        $cache_name = 'article/'.$md5."/".(int)$GLOBALS['page'];
        $resource   = iCache::get($cache_name);
    }
    if(empty($resource)){
        $resource = iDB::all("SELECT * FROM `#iCMS@__article` WHERE {$where_sql} {$order_sql} {$limit}");
        //iDB::debug(1);
        $resource = __article($vars,$resource);
        $vars['cache'] && iCache::set($cache_name,$resource,$cache_time);
    }
    //print_r($resource);
    return $resource;
}
function article_search($vars){
    $resource = array();
    $hidden   = iCache::get('iCMS/category/hidden');
    $hidden &&  $where_sql .=iPHP::where($hidden,'cid','not');
    $SPH    = iCMS::sphinx();
    $SPH->init();
    $SPH->SetArrayResult(true);
    if(isset($vars['weights'])){
        //weights='title:100,tags:80,keywords:60,name:50'
        $wa=explode(',',$vars['weights']);
        foreach($wa AS $wk=>$wv){
            $waa=explode(':',$wv);
            $FieldWeights[$waa[0]]=$waa[1];
        }
        $FieldWeights OR $FieldWeights=array("title" => 100,"tags" => 80,"name" => 60,"keywords" => 40);
        $SPH->SetFieldWeights($FieldWeights);
    }


    $page       = (int)$_GET['page'];
    $maxperpage = isset($vars['row'])?(int)$vars['row']:10;
    $start      = ($page && isset($vars['page']))?($page-1)*$maxperpage:0;
    $SPH->SetMatchMode(SPH_MATCH_EXTENDED);
    if($vars['mode']){
        $vars['mode'] =="SPH_MATCH_BOOLEAN" && $SPH->SetMatchMode(SPH_MATCH_BOOLEAN);
        $vars['mode'] =="SPH_MATCH_ANY" && $SPH->SetMatchMode(SPH_MATCH_ANY);
        $vars['mode'] =="SPH_MATCH_PHRASE" && $SPH->SetMatchMode(SPH_MATCH_PHRASE);
        $vars['mode'] =="SPH_MATCH_ALL" && $SPH->SetMatchMode(SPH_MATCH_ALL);
        $vars['mode'] =="SPH_MATCH_EXTENDED" && $SPH->SetMatchMode(SPH_MATCH_EXTENDED);
    }

    isset($vars['userid']) && $SPH->SetFilter('userid',array($vars['userid']));
    isset($vars['postype'])&& $SPH->SetFilter('postype',array($vars['postype']));

    if(isset($vars['cid'])){
        $cids    = $vars['sub']?iCMS::get_category_ids($vars['cid'],true):$vars['cid'];
        $cids OR $cids = (array)$vars['cid'];
        $cids    = array_map("intval", $cids);
        $SPH->SetFilter('cid',$cids);
    }
    if(isset($vars['startdate'])){
        $startime    =strtotime($vars['startdate']);
        $enddate    =empty($vars['enddate'])?time():strtotime($vars['enddate']);
        $SPH->SetFilterRange('pubdate',$startime,$enddate);
    }
    $SPH->SetLimits($start,$maxperpage,10000);

    $orderBy   = '@id DESC, @weight DESC';
    $order_sql = ' order by id DESC';

    $vars['orderBy']  && $orderBy  = $vars['orderBy'];
    $vars['order_sql']&& $order_sql= ' order by '.$vars['order_sql'];

    $vars['pic'] && $SPH->SetFilter('haspic',array(1));
    $vars['id!'] && $SPH->SetFilter('@id',array($vars['id!']),true);

    $SPH->setSortMode(SPH_SORT_EXTENDED,$orderBy);

    $query    = $vars['q'];
    $vars['acc']&& $query = '"'.$vars['q'].'"';
    $vars['@']  && $query = '@('.$vars['@'].') '.$query;

    $res = $SPH->Query($query,iCMS::$config['sphinx']['index']);

    if (is_array($res["matches"])){
        foreach ( $res["matches"] as $docinfo ){
            $aid[]=$docinfo['id'];
        }
        $aids=implode(',',(array)$aid);
    }
    if(empty($aids)) return;

    $where_sql=" `id` in($aids)";
    $offset    = 0;
    if($vars['page']){
        $total = $res['total'];
        iPHP::assign("article_search_total",$total);
        $pagenav = isset($vars['pagenav'])?$vars['pagenav']:"pagenav";
        $pnstyle = isset($vars['pnstyle'])?$vars['pnstyle']:0;
        $multi   = iCMS::page(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>iPHP::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
        $offset  = $multi->offset;
    }
    $resource = iDB::all("SELECT * FROM `#iCMS@__article` WHERE {$where_sql} {$order_sql} LIMIT {$maxperpage}");
    $resource = __article($vars,$resource);
    return $resource;
}

function __article($vars,$variable){
    if($variable)foreach ($variable as $key => $value) {
        if($vars['page']){
            $value['page']  = $GLOBALS['page']?$GLOBALS['page']:1;
            $value['total'] = $total;
        }
        $value['picdata']&& $picdata = unserialize($value['picdata']);
        $value['pic']  = get_pic($value['pic'],$picdata["b"]);
        $value['mpic'] = get_pic($value['mpic'],$picdata["m"]);
        $value['spic'] = get_pic($value['spic'],$picdata["s"]);

        $category	= iCache::get('iCMS/category/'.$value['cid']);
        $value['category']['name']  = $category['name'];
        $value['category']['sname'] = $category['subname'];
        $value['category']['url']   = $category['iurl']->href;
        $value['category']['link']  = "<a href='{$value['category']['url']}'>{$value['category']['name']}</a>";
        $value['url']               = iURL::get('article',array($value,$category))->href;
        $value['link']              = "<a href='{$value['url']}'>{$value['title']}</a>";
        $value['commentUrl']        = iCMS_API."?app=comment&indexId=".$value['id']."&categoryId=".$value['cid'];
        if($vars['user']){
            iPHP::app('user.class','static');
            $value['user'] = user::info($value['userid'],$value['author']);
        }
		if($vars['meta']){
            $value['metadata'] && $value['metadata'] = unserialize($value['metadata']);
        }
        //$value['description'] && $value['description'] = nl2br($value['description']);
        if($vars['tags']){
        	$value['tags'] = tag::getag('tags',$value,$category);
        }
        unset($value['picdata'],$value['metadata'],$value['tags']);
        $resource[$key] = $value;
    }
    return $resource;
}

