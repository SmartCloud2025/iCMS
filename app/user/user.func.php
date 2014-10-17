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
	$where_sql = "WHERE `uid`='".(int)$vars['userid']."' ";
	$where_sql.= " AND `appid`='".(int)$vars['appid']."'";
	$rs  = iDB::all("SELECT * FROM `#iCMS@__user_category` {$where_sql} ORDER BY `cid` ASC LIMIT $row");
	//iDB::debug(1);
	$resource = array();
	if($rs)foreach ($rs as $key => $value) {
		if($value['appid']==iCMS_APP_ARTICLE){
			$router ='/{uid}/{cid}/';
		}else if($value['appid']==iCMS_APP_FAVORITE){
			$router ='/{uid}/fav/{cid}/';
		}
		$value['url'] = iPHP::router(array($router,array($value['uid'],$value['cid'])),iCMS_REWRITE);
		if(isset($vars['loop'])){
			$resource[$key] = $value;
		}else{
			$resource[$value['cid']]=$value;
		}
	}
	return $resource;
}
function user_follow($vars=null){
	$row = isset($vars['row'])?(int)$vars['row']:"30";
	if($vars['fuid']){
		$where_sql = "WHERE `fuid`='".$vars['fuid']."'";	//fans
		if(isset($vars['check'])){
			$follow = user::follow($vars['fuid'],'all'); //all follow
		}
	}else{
		$where_sql = "WHERE `uid`='".$vars['userid']."'";	//follow
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
		if(isset($vars['check'])){
			$value['followed'] = $follow[$value['uid']]?1:0;
		}
		$resource[$key] = $value;
	}
	//var_dump($rs);
	return $resource;
}
function user_stat($vars=null){

}
//
function user_inbox($vars=null){
	$maxperpage = 30;
	$where_sql  = "WHERE `status` ='1'";
	if($_GET['user']){
		if($_GET['user']=="10000"){
			$where_sql.= " AND `userid`='10000' AND `friend` IN ('".user::$userid."','0')";
		}else{
			$friend = (int)$_GET['user'];
			$where_sql.= " AND `userid`='".user::$userid."' AND `friend`='".$friend."'";
		}
		$group_sql = '';
		$p_fields  = 'COUNT(*)';
		$s_fields  = '*';
		iPHP::assign("msg_count",false);
	}else{
//	 	$where_sql.= " AND (`userid`='".user::$userid."' OR (`userid`='10000' AND `friend`='0'))";
	 	$where_sql.= " AND `userid`='".user::$userid."'";
		$group_sql = ' GROUP BY `friend` DESC';
		$p_fields  = 'COUNT(DISTINCT id)';
		$s_fields  = 'max(id) AS id ,COUNT(id) AS msg_count,`userid`, `friend`, `send_uid`, `send_name`, `receiv_uid`, `receiv_name`, `content`, `type`, `sendtime`, `readtime`';
	 	iPHP::assign("msg_count",true);
	}

	$offset	= 0;
	$total	= iPHP::total($md5,"SELECT {$p_fields} FROM `#iCMS@__message` {$where_sql} {$group_sql}",'nocache');
	iPHP::assign("msgs_total",$total);
    $multi	= iCMS::page(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>iPHP::lang('iCMS:page:list'),'nowindex'=>$GLOBALS['page']));
    $offset	= $multi->offset;
	$resource = iDB::all("SELECT {$s_fields} FROM `#iCMS@__message` {$where_sql} {$group_sql} ORDER BY `id` DESC LIMIT {$offset},{$maxperpage}");
	//iDB::debug(1);
	$msg_type_map = array(
		'0'=>'系统信息',
		'1'=>'私信',
		'2'=>'提醒',
		'3'=>'留言',
	);
	if($resource)foreach ($resource as $key => $value) {
		$value['sender']   = user::info($value['send_uid'],$value['send_name']);
		$value['receiver'] = user::info($value['receiv_uid'],$value['receiv_name']);
		$value['label']    = $msg_type_map[$value['type']];

		if($value['userid']==$value['send_uid']){
			$value['is_sender'] = true;
			$value['user']      = $value['receiver'];
		}
		if($value['userid']==$value['receiv_uid']){
			$value['is_sender'] = false;
			$value['user']      = $value['sender'];
		}
		$value['url'] = iPHP::router(array('/user/inbox/{uid}',$value['user']['uid']),iCMS_REWRITE);
		$resource[$key] = $value;
	}
	return $resource;
}
