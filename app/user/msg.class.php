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
	public function send($a = array("fuid"=>0,"funame"=>NULL,"tuid"=>0,"tuname"=>NULL,"content"=>NULL),$type=1){
		$fuid    = (int)$a['fuid'];
		$funame  = $a['funame'];
		$tuid    = (int)$a['tuid'];
		$tuname  = $a['tuname'];
		$content = $a['content'];
		$_time   = time();
		if($fuid && $fuid==$tuid && !$a['self']){
			return;
		}
		iDB::query("insert into `#iCMS@__msgs`
            (`fuid`, `funame`, `tuid`, `tuname`, `content`, `type`, `sendtime`, `readtime`, `status`)
values ('$fuid', '$funame', '$tuid', '$tuname', '$content', '$type', '$_time', '0', '1');");
		
		if($type=="1"){
			iDB::query("insert into `#iCMS@__msgs`
			            (`fuid`, `funame`, `tuid`, `tuname`, `content`, `type`, `sendtime`, `readtime`, `status`)
			values ('$tuid', '$tuname', '$fuid', '$funame', '$content', '$type', '$_time', '0', '1');");
		}
	}
	//2 @/评论
	public function at($a){
		self::send($a,2);
	}
	//0系统
	public function sysmsg($a){
		$a['fuid']   = "1000";
		$a['funame'] = "系统信息";
		self::send($a,0);
	}
}
////系统test
//msg::send(
//	array(
//		"tuid"=>20018,
//		"tuname"=>'枯木',
//		"content"=>''
//	),0);
