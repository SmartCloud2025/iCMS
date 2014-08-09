<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: comment.tpl.php 2408 2014-04-30 18:58:23Z coolmoo $
 */
function comment_list($vars){
	if ($vars['display']) {
		$vars['do'] = 'list';
		$vars['display'] OR $vars['display'] = 'default';
		if($vars['display'] != 'iframe'){
			$vars['ajax'] = true;
		}
		unset($vars['method']);
		$vars['query'] = http_build_query($vars);
		iPHP::assign('vars',$vars);
		iPHP::view("iCMS://comment/list.{$vars['display']}.htm");
		return;
	}

	if(isset($vars['vars'])){
		$_vars = $vars['vars'];
		unset($vars['vars']);
	 	$vars = array_merge($vars,$_vars);
	}
// print_r(get_class_vars("iCMS")); 
// exit;
// $a = iPHP::get_vars(iCMS::$app_name);
// var_dump($a);

	$appid    = (int)$vars['appid'];
	$whereSQL = "`appid`='$appid' AND `status`='1'";

    if(isset($vars['cid'])){
        $cids	= $vars['sub']?iCMS::get_category_ids($vars['cid'],true):$vars['cid'];
        $cids OR $cids	= $vars['cid'];
        $whereSQL.= iPHP::where($cids,'cid');
    }
	$vars['pid'] && $whereSQL .=" AND `pid`='".(int)$vars['pid']."'";
	$vars['iid'] && $whereSQL .=" AND `iid`='".(int)$vars['iid']."'";
	$vars['uid'] && $whereSQL .=" AND `uid`='".(int)$vars['uid']."'";
	$vars['id']  && $whereSQL .=" AND `id`='".(int)$vars['id']."'";
	
	$maxperpage	= isset($vars['row'])?(int)$vars['row']:"10";
	$cache_time	= isset($vars['time'])?(int)$vars['time']:-1;
	$by			= $vars['by']=='ASC'?"ASC":"DESC";
	switch ($vars['orderby']) {
		default: $orderSQL = " ORDER BY `id` $by";
	}
	$md5	= md5($whereSQL.$orderSQL);
	$offset	= 0;
	$limit  = "LIMIT {$maxperpage}";
	if($vars['page']){
		$total  = iPHP::total($md5,"SELECT count(*) FROM `#iCMS@__comment` WHERE {$whereSQL} limit 1");		
		$pgconf = array(
			'total'     => $total,
			'perpage'   => $maxperpage,
			'unit'      => iPHP::lang('iCMS:page:comment'),
			'ajax'      => $vars['ajax']?'iCMS.comment.page':FALSE,
			'nowindex'  => $GLOBALS['page'],
		);
		if($vars['display'] == 'iframe'){
			iS::gp('pn','GP',2);
			$pgconf['page_name'] = 'pn';
			$pgconf['nowindex']  = $GLOBALS['pn'];
		}

		$multi  = iCMS::page($pgconf);
		$offset = $multi->offset;
		$limit  = "LIMIT {$offset},{$maxperpage}";
		if($offset>1000){
			$whereSQL.=" AND `id` >= (SELECT `id` FROM `#iCMS@__comment` WHERE {$whereSQL} {$orderSQL} LIMIT {$offset},1)";
			$limit  = "LIMIT {$maxperpage}";
		}
		iPHP::assign("comment_total",$total);
	}
	if($vars['cache']){
		$cacheName	= 'comment/'.$md5."/".(int)$offset;
		$rs			= iCache::get($cacheName);
	}
	if(empty($rs)){
		$rs		= iDB::all("SELECT * FROM `#iCMS@__comment` WHERE {$whereSQL} {$orderSQL} {$limit}");
		iDB::debug();
		$_count	= count($rs);
		$ln		=($GLOBALS['page']-1)<0?0:$GLOBALS['page']-1;
		for ($i=0;$i<$_count;$i++){
			if($vars['date_format']){
				$rs[$i]['addtime'] = get_date($rs[$i]['addtime'],$vars['date_format']);
			}
			$rs[$i]['url']     = iCMS_API.'?app=comment&iid='.$rs[$i]['iid'].'&appid='.$rs[$i]['appid'].'&cid='.$rs[$i]['cid'];
			$rs[$i]['lou']     = $total-($i+$ln*$maxperpage);
			$rs[$i]['content'] = nl2br($rs[$i]['content']);
			$rs[$i]['user']    = array();
			$rs[$i]['user']['uid']    = $rs[$i]['uid'];
			$rs[$i]['user']['url']    = get_user($rs[$i]['uid'],"url");
			$rs[$i]['user']['avatar'] = get_user($rs[$i]['uid'],"avatar",$vars['facesize']?$vars['facesize']:0);
			$rs[$i]['user']['name']   = $rs[$i]['name'];

			$rs[$i]['reply'] = '';
			if($rs[$i]['reply_uid']){
				$rs[$i]['reply']['uid']    = $rs[$i]['reply_uid'];
				$rs[$i]['reply']['url']    = get_user($rs[$i]['reply_uid'],"url");
				$rs[$i]['reply']['avatar'] = get_user($rs[$i]['reply_uid'],"avatar",$vars['facesize']?$vars['facesize']:0);
				$rs[$i]['reply']['name']   = $rs[$i]['reply_name'];
			}
			$rs[$i]['total'] = $total;
			if($vars['page']){
				$rs[$i]['page']  = array('total'=>$multi->totalpage,'perpage'=>$multi->perpage);
			}
		}
		$vars['cache'] && iCache::set($cacheName,$rs,$cache_time);
	}
	
	return $rs;
}
function comment_form($vars){
	$ref	= $vars['ref'];
	if($ref){
		$ref===true && $ref=iCMS::$app_name;
		$rs	= iPHP::get_vars($ref);
		if($ref=="article"){
			$vars['iid']	= (int)$rs['id'];
			$vars['cid']	= (int)$rs['cid'];
			$vars['appid']	= iCMS_APP_ARTICLE;
			$vars['title']	= $rs['title'];
		}elseif($ref=="category"){
			$vars['iid']	= (int)$rs['cid'];
			$vars['cid']	= (int)$rs['rootid'];
			$vars['appid']	= iCMS_APP_CATEGORY;
			$vars['title']	= $rs['name'];
		}elseif($ref=="tag"){
			$vars['iid']	= (int)$rs['id'];
			$vars['cid']	= (int)$rs['cid'];
			$vars['appid']	= iCMS_APP_TAG;
			$vars['title']	= $rs['name'];
		}		
	}
	$vars['iid'] OR iPHP::msg('warning:#:warning:#:iCMS:comment:form 标签出错! 缺少"iid"属性或"iid"值为空.');
	$vars['cid'] OR iPHP::msg('warning:#:warning:#:iCMS:comment:form 标签出错! 缺少"cid"属性或"cid"值为空.');
	$vars['appid'] OR iPHP::msg('warning:#:warning:#:iCMS:comment:form 标签出错! 缺少"appid"属性或"appid"值为空.');
	$vars['title'] OR iPHP::msg('warning:#:warning:#:iCMS:comment:form 标签出错! 缺少"title"属性或"title"值为空.');

	switch ($vars['display']) {
		case 'frame':
			//iPHP::assign('iCMS_CFF_Id','iCMS_CFF_'.random(5));
			$vars['style'] OR $vars['style']='width:0px;height:0px;';
			$tpl	= 'form.iframe';
			break;
		default:
			$tpl	= 'form.default';
			break;
	}
	unset($vars['method']);
	$vars['query'] = http_build_query($vars);
	iPHP::assign('vars',$vars);
	return iPHP::view('iCMS://comment/'.$tpl.'.htm');
}