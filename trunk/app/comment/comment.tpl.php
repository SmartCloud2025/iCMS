<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: comment.tpl.php 2408 2014-04-30 18:58:23Z coolmoo $
 */
function comment_list($vars){
	$appid		= (int)$vars['appid'];
	$whereSQL	= "`appid`='$appid' AND `status`='1'";

	isset($vars['pid']) 	&& $whereSQL.=" AND `pid`='".(int)$vars['pid']."'";
    if(isset($vars['cid'])){
        $cids	= $vars['sub']?iCMS::getIds($vars['cid'],true):$vars['cid'];
        $cids OR $cids	= $vars['cid'];
        $whereSQL.= iPHP::andSQL($cids,'cid');
    }
	$vars['iid'] && $whereSQL.=" AND `iid`='".(int)$vars['iid']."'";
	
	$maxperpage	= isset($vars['row'])?(int)$vars['row']:"10";
	$cacheTime	= isset($vars['time'])?(int)$vars['time']:-1;
	$by			= $vars['by']=='ASC'?"ASC":"DESC";
	switch ($vars['orderby']) {
		default:		$orderSQL=" ORDER BY `id` $by";
	}
	$md5	= md5($whereSQL.$orderSQL);
	$offset	= 0;
	if($vars['page']){
		$total	= iPHP::total($md5,"SELECT count(*) FROM `#iCMS@__comment` WHERE {$whereSQL} ");
		iPHP::assign("comment_total",$total);
        $multi		= iCMS::page(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>iPHP::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
        $offset		= $multi->offset;
	}
	if($vars['cache']){
		$cacheName	= 'comment/'.$md5."/".(int)$GLOBALS['page'];
		$rs			= iCache::get($cacheName);
	}
	if(empty($rs)){
		$rs		= iDB::getArray("SELECT * FROM `#iCMS@__comment` WHERE {$whereSQL} {$orderSQL} LIMIT {$offset},{$maxperpage}");
		$_count	= count($rs);
		$ln		=($GLOBALS['page']-1)<0?0:$GLOBALS['page']-1;
		for ($i=0;$i<$_count;$i++){
			$rs[$i]['url']		= iCMS::$config['router']['publicURL'].'/comment.php?indexId='.$rs[$i]['indexid'].'&mid='.$rs[$i]['mid'].'&cid='.$rs[$i]['cid'];
			$rs[$i]['lou']		= $total-($i+$ln*$maxperpage);
			$rs[$i]['content']	= nl2br($rs[$i]['contents']);
			if($vars['user']){
				$rs[$i]['user']['url']	= userinfo($rs[$i]['userid'],"url");
				$rs[$i]['user']['face']	= userinfo($rs[$i]['userid'],"face",$vars['facesize']?$vars['facesize']:0);
				$rs[$i]['user']['name']	= $rs[$i]['username'];
			}
		}
		$vars['cache'] && iCache::set($cacheName,$rs,$cacheTime);
	}
	
	return $rs;
}
function comment_form($vars){
	$ref	= $vars['ref'];
	if($ref){
		$ref===true && $ref=iCMS::$app_name;
		$rs	= iCMS::tpl_vars($ref);
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
			$tpl	= 'comment.form.iframe';
			break;
		default:
			$tpl	= 'comment.form.default';
			break;
	}
	iPHP::assign('comment',$vars);
	return iCMS::tpl('iCMS',$tpl);
}