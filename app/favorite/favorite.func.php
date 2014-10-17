<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: favorite.tpl.php 2408 2014-04-30 18:58:23Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');

iPHP::app('user.class','static');

function favorite_list($vars=null){
	$row       = isset($vars['row'])?(int)$vars['row']:"10";
	$where_sql = "WHERE `uid`='".(int)$vars['userid']."' ";
	$rs  = iDB::all("SELECT * FROM `#iCMS@__favorite` {$where_sql} ORDER BY `id` ASC LIMIT $row");
	//iDB::debug(1);
	$resource = array();
	if($rs)foreach ($rs as $key => $value) {
		$value['url'] = iPHP::router(array('/favorite/{id}/',$value['id']),iCMS_REWRITE);
		if(isset($vars['loop'])){
			$resource[$key] = $value;
		}else{
			$resource[$value['id']]=$value;
		}
	}
	return $resource;
}
