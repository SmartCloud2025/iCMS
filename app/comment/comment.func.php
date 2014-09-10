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
	isset($vars['userid']) && $where_sql.= " AND `userid`='".(int)$vars['userid']."'";

	$rs = iDB::row("SELECT * FROM `#iCMS@__comment` WHERE {$where_sql} LIMIT 1;",ARRAY_A);
	//iDB::debug(1);
	if($rs){
		$rs['user'] = user::info($rs['userid'],$rs['name']);
	}
	return $rs;
}
function comment_list_display($vars){
	$vars['do']          = 'list';
	$vars['page_ajax']   = 1;
	$vars['total_cahce'] = 1;
	$tpl = 'list.default';
	if($vars['display'] == 'iframe'){
		$vars['page_ajax'] = 0;
		$tpl = 'list.iframe';
	}
	isset($vars['_display']) && $vars['display'] = $vars['_display'];
	unset($vars['method'],$vars['_display']);
	$vars['query'] = http_build_query($vars);
	$vars['param'] = iCMS::app_ref(true);
	iPHP::assign('comment_vars',$vars);
	iPHP::view("iCMS://comment/{$tpl}.htm");
}
function comment_list($vars){
	if ($vars['display'] && empty($vars['loop'])) {
		return comment_list_display($vars);
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
    isset($vars['userid'])&& $where_sql.= " AND `userid`='{$vars['userid']}'";

	$vars['pid'] && $where_sql .=" AND `pid`='".(int)$vars['pid']."'";
	$vars['iid'] && $where_sql .=" AND `iid`='".(int)$vars['iid']."'";
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
			'ajax'      => $vars['page_ajax']?'iCMS.comment.page':FALSE,
			'nowindex'  => $GLOBALS['page'],
		);
		if($vars['display'] == 'iframe' || $vars['page_ajax']){
			iS::gp('pn','GP',2);
			$pgconf['page_name'] = 'pn';
			$pgconf['nowindex']  = $GLOBALS['pn'];
		}

		$multi  = iCMS::page($pgconf);
		$offset = $multi->offset;
		$limit  = "LIMIT {$offset},{$maxperpage}";
		// if($offset>1000){
			//$where_sql.=" AND `id` >= (SELECT `id` FROM `#iCMS@__comment` WHERE {$where_sql} {$order_sql} LIMIT {$offset},1)";
			//$limit  = "LIMIT {$maxperpage}";
		// }
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
		$ln		= ($GLOBALS['page']-1)<0?0:$GLOBALS['page']-1;
		for ($i=0;$i<$_count;$i++){
			if($vars['date_format']){
				$rs[$i]['addtime'] = get_date($rs[$i]['addtime'],$vars['date_format']);
			}
			$rs[$i]['url']     = iCMS_API.'?app=comment&iid='.$rs[$i]['iid'].'&appid='.$rs[$i]['appid'].'&cid='.$rs[$i]['cid'];
			$rs[$i]['lou']     = $total-($i+$ln*$maxperpage);
			$rs[$i]['content'] = nl2br($rs[$i]['content']);
			$rs[$i]['user']    = user::info($rs[$i]['userid'],$rs[$i]['name'],$vars['facesize']);
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
	if($vars['ref']){
		$_vars = iCMS::app_ref($vars['ref']);
		unset($vars['ref']);
		$vars  = array_merge($vars,$_vars);
	}
	$vars['iid']   OR iPHP::msg('warning:#:warning:#:iCMS:comment:form 标签出错! 缺少"iid"属性或"iid"值为空.');
	$vars['cid']   OR iPHP::msg('warning:#:warning:#:iCMS:comment:form 标签出错! 缺少"cid"属性或"cid"值为空.');
	$vars['appid'] OR iPHP::msg('warning:#:warning:#:iCMS:comment:form 标签出错! 缺少"appid"属性或"appid"值为空.');
	$vars['title'] OR iPHP::msg('warning:#:warning:#:iCMS:comment:form 标签出错! 缺少"title"属性或"title"值为空.');
	switch ($vars['display']) {
		case 'iframe':
			$tpl        = 'form.iframe';
			$vars['do'] = 'form';
			break;
		default:
			isset($vars['_display']) && $vars['display'] = $vars['_display'];
			$vars['param'] = array(
				'suid'  => $vars['suid'],
				'iid'   => $vars['iid'],
				'cid'   => $vars['cid'],
				'appid' => $vars['appid'],
				'title' => $vars['title'],
			);
			$tpl = 'form.default';
			break;
	}
	unset($vars['method'],$vars['_display']);
	$vars['query'] = http_build_query($vars);
	iPHP::assign('comment_vars',$vars);
	return iPHP::view('iCMS://comment/'.$tpl.'.htm');
}
