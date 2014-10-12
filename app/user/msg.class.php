<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: msg.class.php 2349 2013-02-25 04:10:05Z coolmoo $
*/
class msg{
	//type: 0 系统 1 用户对话 2 @ 3留言
	public static function send($a = array("fuid"=>0,"funame"=>NULL,"tuid"=>0,"tuname"=>NULL,"content"=>NULL),$type=1){
		$fuid     = (int)$a['fuid'];
		$funame   = iS::escapeStr($a['funame']);
		$tuid     = (int)$a['tuid'];
		$tuname   = iS::escapeStr($a['tuname']);
		$content  = iS::escapeStr($a['content']);
		$sendtime = time();
		if($fuid && $fuid==$tuid && !$a['self']){
			return;
		}
        $fields = array('fuid', 'funame', 'tuid', 'tuname', 'content', 'type', 'sendtime', 'readtime', 'status');
        $data   = compact ($fields);
		$data['readtime'] = "0";
		$data['status']   = "1";
		iDB::insert('msgs',$data);
		if($type=="1"){
			$data['fuid']   = $tuid;
			$data['funame'] = $tuname;
			$data['tuid']   = $fuid;
			$data['tuname'] = $funame;
			iDB::insert('msgs',$data);
		}
	}
	//2 @/评论
	public static function at($a){
		self::send($a,2);
	}
	//0 系统
	public static function sysmsg($a){
		$a['fuid']   = "10000";
		$a['funame'] = "系统信息";
		self::send($a,0);
	}
}
////系统
//msg::send(
//	array(
//		"tuid"=>20018,
//		"tuname"=>'枯木',
//		"content"=>''
//	),0);
