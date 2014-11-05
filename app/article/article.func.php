<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: article.tpl.php 2408 2014-04-30 18:58:23Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');

iPHP::app('tag.class','static');
function article_list($vars){
    if($vars['loop']==="rel" && empty($vars['id'])){
        return false;
    }
    $resource  = array();
    $map_where = array();
    $status    = '1';
    isset($vars['status']) && $status = (int)$vars['status'];
    $where_sql = "WHERE `status`='{$status}'";
    $vars['call'] =='user'  && $where_sql.= " AND `postype`='0'";
    $vars['call'] =='admin' && $where_sql.= " AND `postype`='1'";
    $hidden     = iCache::get('iCMS/category/hidden');
    $hidden &&  $where_sql.=iPHP::where($hidden,'cid','not');
    $maxperpage = isset($vars['row'])?(int)$vars['row']:10;
    $cache_time = isset($vars['time'])?(int)$vars['time']:-1;
    isset($vars['userid'])&& $where_sql.= " AND `userid`='{$vars['userid']}'";
    isset($vars['weight'])   && $where_sql.= " AND `weight`='"._int($vars['weight'])."'";

    if(isset($vars['ucid']) && $vars['ucid']!=''){
        $where_sql.= " AND `ucid`='{$vars['ucid']}'";
    }

    if(isset($vars['cid!'])){
    	$ncids    = explode(',',$vars['cid!']);
        $vars['sub'] && $ncids+=iCMS::get_category_ids($ncids,true);
        $where_sql.= iPHP::where($ncids,'cid','not');
    }
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
            map::init('category',iCMS_APP_ARTICLE);
            $map_where+=map::where($cids);
        }
    }
    if($vars['pid'] && !isset($vars['pids'])){
        $pid = explode(',',$vars['pid']);
        $vars['sub'] && $pid+=iCMS::get_category_ids($pid,true);
        $where_sql.= iPHP::where($pid,'pid');
    }

    if(isset($vars['pids']) && !$vars['pid']){
        iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
        map::init('prop',iCMS_APP_ARTICLE);
        $map_where+=map::where($vars['pids']);
    }

    if(isset($vars['tids'])){
        iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
        map::init('tags',iCMS_APP_ARTICLE);
        $map_where+=map::where($vars['tids']);
    }
    if($vars['keywords']){ //最好使用 iCMS:article:search
        if(strpos($vars['keywords'],',')===false){
            $vars['keywords'] = str_replace(array('%','_'),array('\%','\_'),$vars['keywords']);
            $where_sql.= " AND CONCAT(title,keywords,description) like '%".addslashes($vars['keywords'])."%'";
           }else{
            $kws = explode(',',$vars['keywords']);
            foreach($kws AS $kwv){
                $keywords.= addslashes($kwv)."|";
            }
            $keywords = substr($keywords,0,-1);
            $where_sql.= " AND CONCAT(title,keywords,description) REGEXP '$keywords' ";
        }
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
        case "disorder":    $order_sql =" ORDER BY `ordernum` $by";      break;
        case "rand":        $order_sql =" ORDER BY rand() $by";          break;
        case "weight":      $order_sql =" ORDER BY `weight`,`ordernum` ASC";break;
        default:            $order_sql =" ORDER BY `id` DESC";
    }
    isset($vars['startdate'])&& $where_sql .= " AND `pubdate`>='".strtotime($vars['startdate'])."'";
    isset($vars['enddate'])  && $where_sql .= " AND `pubdate`<='".strtotime($vars['enddate'])."'";
    isset($vars['where'])    && $where_sql .= $vars['where'];

    if($map_where){
        $map_sql   = iCMS::map_sql($map_where);
        $where_sql = ",({$map_sql}) map {$where_sql} AND `id` = map.`iid`";
    }
    $md5    = md5($where_sql.$order_sql.$maxperpage);
    $offset = 0;
    $limit  = "LIMIT {$maxperpage}";
    if($vars['page']){
        $total_type = $vars['total_cache']?$vars['total_cache']:null;
        $total      = iPHP::total($md5,"SELECT count(*) FROM `#iCMS@__article` {$where_sql}",$total_type);
        $pagenav    = isset($vars['pagenav'])?$vars['pagenav']:"pagenav";
        $pnstyle    = isset($vars['pnstyle'])?$vars['pnstyle']:0;
        $multi      = iCMS::page(array('total_type'=>$total_type,'total'=>$total,'perpage'=>$maxperpage,'unit'=>iPHP::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
        $offset     = $multi->offset;
        $limit      = "LIMIT {$offset},{$maxperpage}";
        iPHP::assign("article_list_total",$total);
    }
    if($map_sql || $offset){
        $ids_array = iDB::all("
            SELECT `id` FROM `#iCMS@__article`
            {$where_sql} {$order_sql} {$limit}
        ");
        //iDB::debug(1);
        $ids       = iCMS::get_ids($ids_array);
        $ids       = $ids?$ids:'0';
        $where_sql = "WHERE `id` IN({$ids})";
    }
    if($vars['cache']){
        $cache_name = 'article/'.$md5."/".(int)$GLOBALS['page'];
        $resource   = iCache::get($cache_name);
    }
    // $func = '__article_array';
    // if($vars['func']=="user_home"){ //暂时只有一个选项
    //     $func = '__article_user_home_array';
    // }
    if(empty($resource)){
        $resource = iDB::all("SELECT * FROM `#iCMS@__article` {$where_sql} {$order_sql} {$limit}");
        iPHP_SQL_DEBUG && iDB::debug(1);
        $resource = __article_array($vars,$resource);
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
        $startime = strtotime($vars['startdate']);
        $enddate  = empty($vars['enddate'])?time():strtotime($vars['enddate']);
        $SPH->SetFilterRange('pubdate',$startime,$enddate);
    }
    $SPH->SetLimits($start,$maxperpage,10000);

    $orderby   = '@id DESC, @weight DESC';
    $order_sql = ' order by id DESC';

    $vars['orderby']  && $orderby  = $vars['orderby'];
    $vars['ordersql']&& $order_sql = ' order by '.$vars['ordersql'];

    $vars['pic'] && $SPH->SetFilter('haspic',array(1));
    $vars['id!'] && $SPH->SetFilter('@id',array($vars['id!']),true);

    $SPH->setSortMode(SPH_SORT_EXTENDED,$orderby);

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
    iPHP_SQL_DEBUG && iDB::debug(1);
    $resource = __article_array($vars,$resource);
    return $resource;
}

function __article_array($vars,$variable){
    $resource = array();
    if($variable){
        $articleApp = iPHP::app("article");
        $vars['category_lite'] = true;
        foreach ($variable as $key => $value) {
            $value = $articleApp->value($value,false,$vars);
            if($value===false){
                continue;
            }
            if($vars['page']){
                $value['page']  = $GLOBALS['page']?$GLOBALS['page']:1;
                $value['total'] = $total;
            }
            if($vars['archive']=="date"){
                $_date = _archive_date($value['postime']);
                //var_dump($_date);
                //$_date = get_date($value['postime'],'Ymd');
                unset($resource[$key]);
                $resource[$_date][$key] = $value;
            }else{
                $resource[$key] = $value;
            }

        }
    }
    return $resource;
}

function _archive_date($date){
    $limit = time() - $date;
    if($limit <= 86400){
        return '今天';
    }else if($limit > 86400 && $limit<=172800){
        return '昨天';
    }else{
        //return get_date($date,'dm');
        return '<span class="day">'.get_date($date,'d').'</span><span class="mon">'.get_date($date,'m').'月</span>';
    }
}
