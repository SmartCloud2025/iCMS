<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: comment.tpl.php 2408 2014-04-30 18:58:23Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');

iPHP::app('user.class','static');
function comment_array($vars){
	$where_sql = " `status`='1'";

	isset($vars['id'])    &&  $where_sql.= " AND `id`='".(int)$vars['id']."'";
	isset($vars['userid']) && $where_sql.= " AND `uid`='".(int)$vars['userid']."'";

	$rs = iDB::row("SELECT * FROM `#iCMS@__comment` WHERE {$where_sql} LIMIT 1;",ARRAY_A);
	//iDB::debug(1);
	if($rs){
		$rs['user'] = user::info($rs['uid'],$rs['name']);
	}
	return $rs;
}
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

	$where_sql = " `status`='1'";
	if(isset($vars['appid'])){
		$appid    = (int)$vars['appid'];
		$where_sql.= " AND `appid`='$appid'";
	}
    if(isset($vars['cid'])){
        $cids	= $vars['sub']?iCMS::get_category_ids($vars['cid'],true):$vars['cid'];
        $cids OR $cids	= $vars['cid'];
        $where_sql.= iPHP::where($cids,'cid');
    }
    isset($vars['userid'])&& $where_sql.= " AND `uid`='{$vars['userid']}'";

	$vars['pid'] && $where_sql .=" AND `pid`='".(int)$vars['pid']."'";
	$vars['iid'] && $where_sql .=" AND `iid`='".(int)$vars['iid']."'";
	$vars['uid'] && $where_sql .=" AND `uid`='".(int)$vars['uid']."'";
	$vars['id']  && $where_sql .=" AND `id`='".(int)$vars['id']."'";

	$maxperpage	= isset($vars['row'])?(int)$vars['row']:"10";
	$cache_time	= isset($vars['time'])?(int)$vars['time']:-1;
	$by			= $vars['by']=='ASC'?"ASC":"DESC";
	switch ($vars['orderby']) {
		default: $order_sql = " ORDER BY `id` $by";
	}
	$md5	= md5($where_sql.$order_sql);
	$offset	= 0;
	$limit  = "LIMIT {$maxperpage}";
	if($vars['page']){
		$total  = iPHP::total($md5,"SELECT count(*) FROM `#iCMS@__comment` WHERE {$where_sql} limit 1");
		$pgconf = array(
			'total'     => $total,
			'perpage'   => $maxperpage,
			'unit'      => iPHP::lang('iCMS:page:comment'),
			'ajax'      => $vars['ajax']?'iCMS.comment.page':FALSE,
			'nowindex'  => $GLOBALS['page'],
		);
		if($vars['display'] == 'iframe' || $vars['ajax']){
			iS::gp('pn','GP',2);
			$pgconf['page_name'] = 'pn';
			$pgconf['nowindex']  = $GLOBALS['pn'];
		}

		$multi  = iCMS::page($pgconf);
		$offset = $multi->offset;
		$limit  = "LIMIT {$offset},{$maxperpage}";
		if($offset>1000){
			//$where_sql.=" AND `id` >= (SELECT `id` FROM `#iCMS@__comment` WHERE {$where_sql} {$order_sql} LIMIT {$offset},1)";
			//$limit  = "LIMIT {$maxperpage}";
		}
		iPHP::assign("comment_total",$total);
	}
	if($vars['cache']){
		$cacheName	= 'comment/'.$md5."/".(int)$offset;
		$rs			= iCache::get($cacheName);
	}
	if(empty($rs)){
		$rs		= iDB::all("SELECT * FROM `#iCMS@__comment` WHERE {$where_sql} {$order_sql} {$limit}");
		//iDB::debug(1);
		$_count	= count($rs);
		$ln		=($GLOBALS['page']-1)<0?0:$GLOBALS['page']-1;
		for ($i=0;$i<$_count;$i++){
			if($vars['date_format']){
				$rs[$i]['addtime'] = get_date($rs[$i]['addtime'],$vars['date_format']);
			}
			$rs[$i]['url']     = iCMS_API.'?app=comment&iid='.$rs[$i]['iid'].'&appid='.$rs[$i]['appid'].'&cid='.$rs[$i]['cid'];
			$rs[$i]['lou']     = $total-($i+$ln*$maxperpage);
			$rs[$i]['content'] = nl2br($rs[$i]['content']);
			$rs[$i]['user']    = user::info($rs[$i]['uid'],$rs[$i]['name'],$vars['facesize']);
			$rs[$i]['reply_uid'] && $rs[$i]['reply'] = user::info($rs[$i]['reply_uid'],$rs[$i]['reply_name'],$vars['facesize']);

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
		switch ($ref) {
			case 'article':
				$vars['suid']	= (int)$rs['userid'];
				$vars['iid']	= (int)$rs['id'];
				$vars['cid']	= (int)$rs['cid'];
				$vars['appid']	= iCMS_APP_ARTICLE;
				$vars['title']	= $rs['title'];
			break;
			case 'category':
				$vars['suid']	= (int)$rs['userid'];
				$vars['iid']	= (int)$rs['cid'];
				$vars['cid']	= (int)$rs['rootid'];
				$vars['appid']	= iCMS_APP_CATEGORY;
				$vars['title']	= $rs['name'];
			break;
			case 'tag':
				$vars['suid']	= (int)$rs['uid'];
				$vars['iid']	= (int)$rs['id'];
				$vars['cid']	= (int)$rs['cid'];
				$vars['appid']	= iCMS_APP_TAG;
				$vars['title']	= $rs['name'];
			break;
		}
	}
	$vars['iid']   OR iPHP::msg('warning:#:warning:#:iCMS:comment:form 标签出错! 缺少"iid"属性或"iid"值为空.');
	$vars['cid']   OR iPHP::msg('warning:#:warning:#:iCMS:comment:form 标签出错! 缺少"cid"属性或"cid"值为空.');
	$vars['appid'] OR iPHP::msg('warning:#:warning:#:iCMS:comment:form 标签出错! 缺少"appid"属性或"appid"值为空.');
	$vars['title'] OR iPHP::msg('warning:#:warning:#:iCMS:comment:form 标签出错! 缺少"title"属性或"title"值为空.');

	switch ($vars['display']) {
		case 'iframe':
			iPHP::assign('style',$vars['style']);
			$tpl        = 'form.iframe';
			$vars['do'] ='form_iframe';
			break;
		default:
			$tpl = 'form.default';
			break;
	}
	unset($vars['method']);
	$vars['query'] = http_build_query($vars);
	iPHP::assign('vars',$vars);
	return iPHP::view('iCMS://comment/'.$tpl.'.htm');
}
