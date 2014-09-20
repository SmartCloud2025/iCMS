<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: user.tpl.php 1392 2013-05-20 12:28:08Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');

iPHP::app('user.class','static');
function user_list($vars=null){
	//return iPHP::view('iCMS','public.js');
}
function user_category($vars=null){
	$row       = isset($vars['row'])?(int)$vars['row']:"10";
	$where_sql = "WHERE `uid`='".$vars['userid']."'";
	$resource  = iDB::all("SELECT * FROM `#iCMS@__user_category` {$where_sql} ORDER BY `cid` ASC LIMIT $row");
	//iDB::debug(1);
	if($resource)foreach ($resource as $key => $value) {
		$value['url']	= iPHP::router(array('/{uid}/{cid}/',array($value['uid'],$value['cid'])),iCMS_REWRITE);
		isset($vars['array']) && $array[$value['cid']]=$value;
		$resource[$key] = $value;
	}
	if(isset($vars['array'])){
		return $array;
	}
	return $resource;
}
function user_follow($vars=null){
	$row = isset($vars['row'])?(int)$vars['row']:"30";
	if($vars['fuid']){
		$where_sql = "WHERE `fuid`='".$vars['fuid']."'";
	}else{
		$where_sql = "WHERE `uid`='".$vars['userid']."'";
	}
	$resource = iDB::all("SELECT * FROM `#iCMS@__user_follow` {$where_sql} LIMIT $row");
	//iDB::debug();
	if($resource)foreach ($resource as $key => $value) {
		if($vars['fuid']){
			$value['avatar'] = user::router($value['uid'],'avatar');
			$value['url']    = user::router($value['uid'],'url');
		}else{
			$value['avatar'] = user::router($value['fuid'],'avatar');
			$value['url']    = user::router($value['fuid'],'url');
			$value['uid']    = $value['fuid'];
			$value['name']   = $value['fname'];
		}
		$resource[$key] = $value;
	}
	//var_dump($rs);
	return $resource;
}
function user_stat($vars=null){

}
