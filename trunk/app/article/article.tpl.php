<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: article.tpl.php 2408 2014-04-30 18:58:23Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
iPHP::appClass("tag",'break');
function article_list($vars){
    if($vars['loop']==="rel" && empty($vars['id'])){
        return false;
    }
    $whereSQL = " `status`='1'";
    $hidden   = iCache::get('system/category/hidden');
    $hidden &&  $whereSQL.=iPHP::andSQL($hidden,'cid','not');
    $maxperpage=isset($vars['row'])?(int)$vars['row']:10;
    $cacheTime =isset($vars['time'])?(int)$vars['time']:-1;
    isset($vars['userid']) && $whereSQL .=" AND `userid`='{$vars['userid']}'";
    isset($vars['author']) && $whereSQL .=" AND `author`='{$vars['author']}'";
    isset($vars['top']) && $whereSQL    .=" AND `top`='"._int($vars['top'])."'";
    $vars['call']=='user' && $whereSQL.=" AND `postype`='0'";
    $vars['call']=='admin' && $whereSQL.=" AND `postype`='1'";
    $vars['scid'] && $whereSQL   .=" AND `scid`='{$vars['scid']}'";


   
    if(isset($vars['cid!'])){
    	$ncids    = $vars['cid!'];
    	if($vars['sub']){
        	$ncids	= iCMS::getIds($vars['cid!'],true);
        	array_push ($ncids,$vars['cid!']);
        }
        $whereSQL.= iPHP::andSQL($ncids,'cid','not');
    }
    if(isset($vars['cid'])){
    	$cids    = $vars['cid'];
    	if($vars['sub']){
        	$cids	= iCMS::getIds($vars['cid'],true);
        	array_push ($cids,$vars['cid']);
        }
        $whereSQL.= iPHP::andSQL($cids,'cid');
    }
    isset($vars['pid']) && $whereSQL.= " AND `pid` ='{$vars['pid']}'";
    $vars['id'] && $whereSQL.= iPHP::andSQL($vars['id'],'id');
    $vars['id!'] && $whereSQL.= iPHP::andSQL($vars['id!'],'id','not');
    $by=$vars['by']=="ASC"?"ASC":"DESC";
    if($vars['keywords']){
        if(strpos($vars['keywords'],',')===false){
             $vars['keywords']=str_replace(array('%','_'),array('\%','\_'),$vars['keywords']);
            $whereSQL.= " AND CONCAT(title,keywords,description) like '%".addslashes($vars['keywords'])."%'";
           }else{
            $kw=explode(',',$vars['keywords']);
            foreach($kw AS $v){
                $keywords.=addslashes($v)."|";
            }
            $keywords=substr($keywords,0,-1);
            $whereSQL.= "  And CONCAT(title,keywords,description) REGEXP '$keywords' ";
        }
    }
    isset($vars['pic']) && $whereSQL.= " AND `isPic`='1'";
    isset($vars['nopic']) && $whereSQL.= " AND `isPic`='0'";
    switch ($vars['orderby']) {
        case "id":          $orderSQL =" ORDER BY `id` $by";            break;
        case "hot":         $orderSQL =" ORDER BY `hits` $by";          break;
        case "comment":     $orderSQL =" ORDER BY `comments` $by";      break;
        case "pubdate":     $orderSQL =" ORDER BY `pubdate` $by";       break;
        case "disorder":    $orderSQL =" ORDER BY `orderNum` $by";      break;
        case "rand":        $orderSQL =" ORDER BY rand() $by";          break;
        case "top":         $orderSQL =" ORDER BY `top`,`orderNum` ASC";break;
        default:            $orderSQL =" ORDER BY `id` DESC";
    }
    isset($vars['startdate'])    && $whereSQL.=" AND `pubdate`>='".strtotime($vars['startdate'])."'";
    isset($vars['enddate'])     && $whereSQL.=" AND `pubdate`<='".strtotime($vars['enddate'])."'";
    isset($vars['where'])        && $whereSQL.=$vars['where'];
    
    $md5    = md5($whereSQL.$orderSQL.$maxperpage);
    $offset    = 0;
    if($vars['page']){
        $total   = iPHP::total($md5,"SELECT count(*) FROM `#iCMS@__article` WHERE {$whereSQL}");
        $pagenav = isset($vars['pagenav'])?$vars['pagenav']:"pagenav";
        $pnstyle = isset($vars['pnstyle'])?$vars['pnstyle']:0;
        $multi   = iCMS::page(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>iPHP::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
        $offset  = $multi->offset;
        iPHP::assign("article_list_total",$total);
    }
    if($vars['cache']){
        $cacheName ='article/'.$md5."/".(int)$GLOBALS['page'];
        $rs        =iCache::get($cacheName);
    }
    if(empty($rs)){
        $rs = iDB::getArray("SELECT * FROM `#iCMS@__article` WHERE {$whereSQL} {$orderSQL} LIMIT {$offset} , {$maxperpage}");
        iDB::debug();
        $rs = article_array($vars,$rs);
        $vars['cache'] && iCache::set($cacheName,$rs,$cacheTime);
    }
    return $rs;
}
function article_search($vars){
    $hidden = iCache::get('system/category/hidden');
    $hidden &&  $whereSQL .=iPHP::andSQL($hidden,'cid','not');
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
    

    $page        = (int)$_GET['page'];
    $maxperpage    = isset($vars['row'])?(int)$vars['row']:10;
    $start         = ($page && isset($vars['page']))?($page-1)*$maxperpage:0;
    $SPH->SetMatchMode(SPH_MATCH_EXTENDED);
    if($vars['mode']){
        $vars['mode'] =="SPH_MATCH_BOOLEAN" && $SPH->SetMatchMode(SPH_MATCH_BOOLEAN);
        $vars['mode'] =="SPH_MATCH_ANY" && $SPH->SetMatchMode(SPH_MATCH_ANY);
        $vars['mode'] =="SPH_MATCH_PHRASE" && $SPH->SetMatchMode(SPH_MATCH_PHRASE);
        $vars['mode'] =="SPH_MATCH_ALL" && $SPH->SetMatchMode(SPH_MATCH_ALL);
        $vars['mode'] =="SPH_MATCH_EXTENDED" && $SPH->SetMatchMode(SPH_MATCH_EXTENDED);
    }
    
    isset($vars['userid']) && $SPH->SetFilter('userid',array($vars['userid']));
    isset($vars['postype']) && $SPH->SetFilter('postype',array($vars['postype']));
    
    if(isset($vars['cid'])){
        $cids    = $vars['sub']?iCMS::getIds($vars['cid'],true):$vars['cid'];
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
    
    $orderBy    = '@id DESC, @weight DESC';
    $orderSQL    = ' order by id DESC';
    
    $vars['orderBy']     && $orderBy    = $vars['orderBy'];
    $vars['orderSQL']     && $orderSQL= ' order by '.$vars['orderSQL'];

    $vars['pic'] && $SPH->SetFilter('isPic',array(1));
    $vars['id!'] && $SPH->SetFilter('@id',array($vars['id!']),true);
    
    $SPH->setSortMode(SPH_SORT_EXTENDED,$orderBy);
    
    $query    = $vars['q'];
    $vars['acc']     &&     $query    = '"'.$vars['q'].'"';
    $vars['@']         &&     $query    = '@('.$vars['@'].') '.$query;

    $res = $SPH->Query($query,iCMS::$config['sphinx']['index']);
    
    if (is_array($res["matches"])){
        foreach ( $res["matches"] as $docinfo ){
            $aid[]=$docinfo['id'];
        }
        $aids=implode(',',(array)$aid);
    }
    if(empty($aids)) return;
    
    $whereSQL=" `id` in($aids)";
    $offset    = 0;
    if($vars['page']){
        $total    = $res['total'];
        iPHP::assign("article_search_total",$total);
        $pagenav= isset($vars['pagenav'])?$vars['pagenav']:"pagenav";
        $pnstyle= isset($vars['pnstyle'])?$vars['pnstyle']:0;
        $multi    = iCMS::page(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>iPHP::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
        $offset    = $multi->offset;
    }
    $rs    = iDB::getArray("SELECT * FROM `#iCMS@__article` WHERE {$whereSQL} {$orderSQL} LIMIT {$maxperpage}");
    $rs    = article_array($vars,$rs);
    return $rs;
}

function article_array($vars,$rs){
    $_count        = count($rs);
    for ($i=0;$i<$_count;$i++){
        if($vars['page']){
            $rs[$i]['page']  = $GLOBALS['page']?$GLOBALS['page']:1;
            $rs[$i]['total'] = $total;
        }
        if(isset($vars['picWidth']) && isset($vars['picHeight']) && $rs[$i]['pic']){
                $im = bitscale(array("tw"  => $vars['picWidth'],"th" => $vars['picHeight'],"w"  =>$rs[$i]['picwidth'] ,"h" =>$rs[$i]['picheight']));
                $rs[$i]['img']=$im;
        }
        $rs[$i]['pic'] && $rs[$i]['pic']=iFS::fp($rs[$i]['pic'],'+http');

        $category	= iCache::get('system/category/'.$rs[$i]['cid']);
        $rs[$i]['category']['name']    = $category['name'];
        $rs[$i]['category']['subname'] = $category['subname'];
        $rs[$i]['category']['url']     = $category['iurl']->href;
        $rs[$i]['category']['link']    = "<a href='{$rs[$i]['category']['url']}'>{$rs[$i]['category']['name']}</a>";
        $rs[$i]['url']                 = iRouter::url('article',array($rs[$i],$category))->href;
        $rs[$i]['link']                = "<a href='{$rs[$i]['url']}'>{$rs[$i]['title']}</a>";
        $rs[$i]['commentUrl']          = iCMS::$config['router']['publicURL']."/comment.php?indexId=".$rs[$i]['id']."&categoryId=".$rs[$i]['cid'];
        if($vars['user']){
            $rs[$i]['user']['url']  = "/u/".$rs[$i]['userid'];
            $rs[$i]['user']['name'] = $rs[$i]['author'];
            $rs[$i]['user']['id']   = $rs[$i]['userid'];
        }
        // if($vars['urls']){
        //     $rs[$i]['urls']['url']      = "/u/".$rs[$i]['userid'];
        //     $rs[$i]['urls']['url']      = "/u/".$rs[$i]['userid'];
        //     $rs[$i]['urls']['url']      = "/u/".$rs[$i]['userid'];
        // }
		if($vars['meta']){
            $rs[$i]['metadata'] && $rs[$i]['metadata'] = unserialize($rs[$i]['metadata']);
        }
        $rs[$i]['description'] && $rs[$i]['description'] = nl2br($rs[$i]['description']);
        if($vars['tags']){
        	tag::getag('tags',$rs[$i],$category);
        }
    }
    return $rs;
}
