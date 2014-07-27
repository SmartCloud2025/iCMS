<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: account.app.php 634 2013-04-03 06:02:53Z coolmoo $
*/
class accountApp{
    function __construct() {
    	$this->uid	= (int)$_GET['id'];
    }
    function do_adduser(){

    }

    function add(){

    }

    function do_user(){
        $group = iACP::app("groups",0);
        $sql   = "WHERE 1=1";
        $_GET['gid'] && $sql.=" AND `gid`='{$_GET['gid']}'";        
        $orderby    = $_GET['orderby']?$_GET['orderby']:"uid DESC";
        $maxperpage = (int)$_GET['perpage']>0?$_GET['perpage']:20;
        $total      = iPHP::total(false,"SELECT count(*) FROM `#iCMS@__user` {$sql}","G");
        iPHP::pagenav($total,$maxperpage,"个用户");
        $rs     = iDB::all("SELECT * FROM `#iCMS@__user` {$sql} order by {$orderby} LIMIT ".iPHP::$offset." , {$maxperpage}");
        $_count = count($rs);
        include iACP::view("user.manage");
    }

    function do_job(){
		require_once iPHP_APP_CORE.'/job.class.php';
		$job	= new JOB;
		$job->countPost($this->uid);
		$month	= $job->month();
		$pmonth	= $job->month($job->pmonth['start']);
		$rs			= iDB::row("SELECT * FROM `#iCMS@__members` WHERE `uid`='$this->uid' LIMIT 1;");
		include iACP::view("account.job");
    }

    function do_addadmin(){
        $group  = iACP::app("groups",$this->type);
        if($this->uid) {
            $rs         = iDB::row("SELECT * FROM `#iCMS@__members` WHERE `uid`='$this->uid' LIMIT 1;");
            $rs->info && $rs->info  =unserialize($rs->info);
        }
        include iACP::view("account.add");
    }
    function do_admin(){
    	if($_GET['job']){
    		require_once iPHP_APP_CORE.'/job.class.php';
    		$job	=new JOB;
    	}
    	$group	= iACP::app("groups",1);
    	$sql	= "WHERE 1=1";
    	//isset($this->type)	&& $sql.=" AND `type`='$this->type'";
		$_GET['gid'] && $sql.=" AND `gid`='{$_GET['gid']}'";
        $orderby    = $_GET['orderby']?$_GET['orderby']:"uid DESC";
        $maxperpage = (int)$_GET['perpage']>0?$_GET['perpage']:20;
        $total      = iPHP::total(false,"SELECT count(*) FROM `#iCMS@__members` {$sql}","G");
        iPHP::pagenav($total,$maxperpage,"个用户");
        $rs         = iDB::all("SELECT * FROM `#iCMS@__members` {$sql} order by {$orderby} LIMIT ".iPHP::$offset." , {$maxperpage}");
        $_count		= count($rs);
    	include iACP::view("account.manage");
    }
    function do_save(){
        $uid               = (int)$_POST['uid'];
        $gid               = (int)$_POST['gid'];
        $gender            = (int)$_POST['sex'];
        $type              = $_POST['type'];
        $username          = iS::escapeStr($_POST['uname']);
        $nickname          = iS::escapeStr($_POST['nickname']);
        $realname          = iS::escapeStr($_POST['realname']);
        $info['icq']       = intval($_POST['icq']);
        $info['home']      = iS::escapeStr(stripslashes($_POST['home']));
        $info['year']      = intval($_POST['year']);
        $info['month']     = intval($_POST['month']);
        $info['day']       = intval($_POST['day']);
        $info['from']      = iS::escapeStr(stripslashes($_POST['from']));
        $info['signature'] = iS::escapeStr(stripslashes($_POST['signature']));
        $info              = addslashes(serialize($info));
        $_POST['pwd'] && $password = md5($_POST['pwd']);

        $username OR iPHP::alert('账号不能为空');

        if(empty($uid)) {
            iDB::value("SELECT `uid` FROM `#iCMS@__members` where `username` ='$username' LIMIT 1") && iPHP::alert('该账号已经存在');
            $fields = array('gid', 'username', 'password', 'nickname', 'realname', 'gender', 'info', 'power', 'cpower', 'regtime', 'lastip', 'lastlogintime', 'logintimes', 'post', 'type', 'status');
            $data   = compact ($fields);
            $data['regtime']       = time();
            $data['lastip']        = iPHP::getIp();
            $data['lastlogintime'] = time();
            $data['status']        = '1';
            iDB::insert('members',$data);
            $msg="账号添加完成!";
        }else {
            iDB::value("SELECT `uid` FROM `#iCMS@__members` where `username` ='$username' AND `uid` !='$uid' LIMIT 1") && iPHP::alert('该账号已经存在');
            $fields = array('gid','gender','username','nickname','realname','info');
            $data   = compact ($fields);
            iDB::update('members', $data, array('uid'=>$uid));
            $password && iDB::query("UPDATE `#iCMS@__members` SET `password`='$pwd' WHERE `uid` ='".$uid);
            $msg="账号修改完成!";
        }
        iPHP::success($msg,'url:'.APP_URI);
    }
    function do_batch(){
    	$idA	= (array)$_POST['id'];
    	$idA OR iPHP::alert("请选择要操作的用户");
    	$ids	= implode(',',(array)$_POST['id']);
    	$batch	= $_POST['batch'];
    	switch($batch){
    		case 'dels':
                iPHP::$break = false;
	    		foreach($idA AS $id){
	    			$this->do_del($id,false);
	    		}
                iPHP::$break = true;
				iPHP::success('用户全部删除完成!','js:1');
    		break;
		}
	}
    function do_del($uid = null,$dialog=true){
    	$uid===null && $uid=$this->uid;
		$uid OR iPHP::alert('请选择要删除的用户');
		$uid=="1" && iPHP::alert('不能删除超级管理员');
		iDB::query("DELETE FROM `#iCMS@__members` WHERE `uid` = '$uid'");
		$dialog && iPHP::success('用户删除完成','js:parent.$("#tr'.$uid.'").remove();');
    }
}
