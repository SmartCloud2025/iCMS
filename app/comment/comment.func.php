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
		$rs['user'] = user::info($rs['userid'],$rs['username']);
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
	$vars['param'] = array(
		'suid'  => $vars['suid'],
		'iid'   => $vars['iid'],
		'cid'   => $vars['cid'],
		'appid' => $vars['appid'],
		'title' => $vars['title'],
	);
	iPHP::assign('comment_vars',$vars);
	echo iPHP::view("iCMS://comment/{$tpl}.htm");
}
function comment_list($vars){
	if ($vars['display'] && empty($vars['loop'])) {
		if(empty($vars['_display'])){
			$_vars = iCMS::app_ref(true);
			$vars  = array_merge($vars,$_vars);
		}
		return comment_list_display($vars);
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
		isset($vars['total_cache']) && $_GET['total_cahce'] = true;
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

		isset($vars['total_cache']) && $pgconf['total_type'] = $vars['total_cache'];

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
		$cache_name = 'comment/'.$md5."/".(int)$offset;
		$resource   = iCache::get($cache_name);
	}
	if(empty($resource)){
		$resource		= iDB::all("SELECT * FROM `#iCMS@__comment` WHERE {$where_sql} {$order_sql} {$limit}");
		//iDB::debug(1);
		$ln		= ($GLOBALS['page']-1)<0?0:$GLOBALS['page']-1;
		if($resource)foreach ($resource as $key => $value) {
			if($vars['date_format']){
				$value['addtime'] = get_date($value['addtime'],$vars['date_format']);
			}
			$value['url']     = iCMS_API.'?app=comment&do=goto&iid='.$value['iid'].'&appid='.$value['appid'].'&cid='.$value['cid'];
			$value['lou']     = $total-($i+$ln*$maxperpage);
			$value['content'] = nl2br($value['content']);
			$value['user']    = user::info($value['userid'],$value['username'],$vars['facesize']);
			$value['reply_uid'] && $value['reply'] = user::info($value['reply_uid'],$value['reply_name'],$vars['facesize']);

			$value['total'] = $total;
			if($vars['page']){
				$value['page']  = array('total'=>$multi->totalpage,'perpage'=>$multi->perpage);
			}
	        $value['param'] = array(
				"appid"  => iCMS_APP_COMMENT,
				"id"     => $value['id'],
				"userid" => $value['userid'],
				"name"   => $value['username'],
	        );
			$resource[$key] = $value;
		}
		$vars['cache'] && iCache::set($cache_name,$resource,$cache_time);
	}

	return $resource;
}
function comment_form($vars){
	if(!iCMS::$hooks['enable_comment']){
		iPHP::warning('此页面禁止调用 iCMS&#x3a;comment&#x3a;form 标签！');
	}
	if($vars['ref']){
		$_vars = iCMS::app_ref($vars['ref']);
		unset($vars['ref']);
		$vars  = array_merge($vars,$_vars);
	}
	$vars['iid']   OR iPHP::warning('iCMS&#x3a;comment&#x3a;form 标签出错! 缺少"iid"属性或"iid"值为空.');
	$vars['cid']   OR iPHP::warning('iCMS&#x3a;comment&#x3a;form 标签出错! 缺少"cid"属性或"cid"值为空.');
	$vars['appid'] OR iPHP::warning('iCMS&#x3a;comment&#x3a;form 标签出错! 缺少"appid"属性或"appid"值为空.');
	$vars['title'] OR iPHP::warning('iCMS&#x3a;comment&#x3a;form 标签出错! 缺少"title"属性或"title"值为空.');
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
	echo iPHP::view('iCMS://comment/'.$tpl.'.htm');
}
