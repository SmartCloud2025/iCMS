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
	$row      = isset($vars['row'])?(int)$vars['row']:"10";
	$whereSQL = " WHERE `uid`='".$vars['userid']."'";
	$rs       = iDB::all("SELECT * FROM `#iCMS@__user_category`{$whereSQL} ORDER BY `cid` ASC LIMIT $row");
	$_count   = count($rs);
	//iDB::debug(1);
	for ($i=0;$i<$_count;$i++){
		$rs[$i]['url']	= iPHP::router(array('/{uid}/{cid}/',array($rs[$i]['uid'],$rs[$i]['cid'])),iCMS_REWRITE);
		isset($vars['array']) && $array[$rs[$i]['cid']]=$rs[$i];
	}
	if(isset($vars['array'])){
		return $array;
	}
	return $rs;
}
function user_follow($vars=null){
	$row = isset($vars['row'])?(int)$vars['row']:"30";
	if($vars['fuid']){
		$whereSQL = " WHERE `fuid`='".$vars['fuid']."'";
	}else{
		$whereSQL = " WHERE `uid`='".$vars['userid']."'";
	}
	$rs     = iDB::all("SELECT * FROM `#iCMS@__user_follow`{$whereSQL} LIMIT $row");
	$_count = count($rs);
	//iDB::debug();
	for ($i=0;$i<$_count;$i++){
		if($vars['fuid']){
			$rs[$i]['avatar'] = user::router($rs[$i]['uid'],'avatar');
			$rs[$i]['url']    = user::router($rs[$i]['uid'],'url');
		}else{
			$rs[$i]['avatar'] = user::router($rs[$i]['fuid'],'avatar');
			$rs[$i]['url']    = user::router($rs[$i]['fuid'],'url');
			$rs[$i]['uid']    = $rs[$i]['fuid'];
			$rs[$i]['name']   = $rs[$i]['fname'];
		}
	}
	//var_dump($rs);
	return $rs;
}
function user_stat($vars=null){

}
