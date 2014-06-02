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
    function doadd1(){
    	$this->type="1";
    	$this->add();
    }
    function doadd0(){
    	$this->type="0";
    	$this->add();
    }
	function dologin() {
        $rs  = iDB::getRow("SELECT * FROM `#iCMS@__members` WHERE `uid`='".$this->uid."' LIMIT 1;");
        $ip  = iPHP::getIp();
        $sep = iPHP_AUTH_IP?'#=iCMS['.$ip.']=#':'#=iCMS=#';
        iPHP::setCookie('iCMS_AUTH',authcode($rs->username.$sep.$rs->password,'ENCODE'));
        iPHP::gotourl('http://www.ladyband.com/~iCMS/usercp.php');
	}
    function add(){
    	$group	= iACP::app("groups",$this->type);
        if($this->uid) {
            $rs			= iDB::getRow("SELECT * FROM `#iCMS@__members` WHERE `uid`='$this->uid' LIMIT 1;");
            $rs->info && $rs->info	=unserialize($rs->info);
        }
        include iACP::view("account.add");
    }
    function dosave(){
        $uid               = (int)$_POST['uid'];
        $gid               = (int)$_POST['gid'];
        $sex               = (int)$_POST['sex'];
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
			iDB::getValue("SELECT `uid` FROM `#iCMS@__members` where `username` ='$username' LIMIT 1") && iPHP::alert('该账号已经存在');
            iDB::query("INSERT INTO `#iCMS@__members`
            (`gid`, `username`, `password`, `nickname`, `realname`, `sex`, `info`, `power`, `cpower`, `regtime`, `lastip`, `lastlogintime`, `logintimes`, `post`, `type`, `status`)
VALUES ('$gid', '$username', '$password', '$nickname', '$realname', '$sex', '$info', '', '', '".time()."', '".iPHP::getIp()."', '".time()."', '0', '0', '$type', '1');");
			$msg="账号添加完成!";
        }else {
            iDB::getValue("SELECT `uid` FROM `#iCMS@__members` where `username` ='$username' AND `uid` !='$uid' LIMIT 1") && iPHP::alert('该账号已经存在');
            iDB::query("UPDATE `#iCMS@__members` SET `gid`='$gid',`sex`='$sex',`username`='$username',`nickname`='$nickname',`realname`='$realname',`info` = '$info' WHERE `uid` ='".$uid);
            $_POST['pwd'] OR iDB::query("UPDATE `#iCMS@__members` SET `password`='$pwd' WHERE `uid` ='".$uid);
            $msg="账号修改完成!";
        }
        iPHP::OK($msg,'url:'.APP_URI);
    }
    function douser(){
        $sql    = "WHERE 1=1";
        //isset($this->type)    && $sql.=" AND `type`='$this->type'";
        $_GET['gid']    && $sql.=" AND `gid`='{$_GET['gid']}'";
        
        $orderby    = $_GET['orderby']?$_GET['orderby']:"uid DESC";
        $maxperpage = (int)$_GET['perpage']>0?$_GET['perpage']:20;
        $total      = iPHP::total(false,"SELECT count(*) FROM `#iCMS@__user` {$sql}","G");
        iPHP::pagenav($total,$maxperpage,"个用户");
        $rs         = iDB::getArray("SELECT * FROM `#iCMS@__user` {$sql} order by {$orderby} LIMIT ".iPHP::$offset." , {$maxperpage}");
//echo iDB::$last_query;
//iDB::$last_query='explain '.iDB::$last_query;
//$explain=iDB::getRow(iDB::$last_query);
//var_dump($explain);
        $_count     = count($rs);
        include iACP::view("user.manage");
    }
    function doadmin(){
    	$this->type="1";
    	$this->doiCMS();
    }
    function dojob(){
		require_once iPHP_APP_CORE.'/job.class.php';
		$job	= new JOB;
		$job->countPost($this->uid);
		$month	= $job->month();
		$pmonth	= $job->month($job->pmonth['start']);
		$rs			= iDB::getRow("SELECT * FROM `#iCMS@__members` WHERE `uid`='$this->uid' LIMIT 1;");
		include iACP::view("account.job");
    }
    function doiCMS(){
    	if($_GET['job']){
    		require_once iPHP_APP_CORE.'/job.class.php';
    		$job	=new JOB;
    	}
    	$group	= iACP::app("groups",$this->type);
    	$sql	= "WHERE 1=1";
    	//isset($this->type)	&& $sql.=" AND `type`='$this->type'";
		$_GET['gid'] 	&& $sql.=" AND `gid`='{$_GET['gid']}'";
		
        $orderby    = $_GET['orderby']?$_GET['orderby']:"uid DESC";
        $maxperpage = (int)$_GET['perpage']>0?$_GET['perpage']:20;
        $total      = iPHP::total(false,"SELECT count(*) FROM `#iCMS@__members` {$sql}","G");
        iPHP::pagenav($total,$maxperpage,"个用户");
        $rs         = iDB::getArray("SELECT * FROM `#iCMS@__members` {$sql} order by {$orderby} LIMIT ".iPHP::$offset." , {$maxperpage}");
//echo iDB::$last_query;
//iDB::$last_query='explain '.iDB::$last_query;
//$explain=iDB::getRow(iDB::$last_query);
//var_dump($explain);
        $_count		= count($rs);
    	include iACP::view("account.manage");
    }
    function dobatch(){
    	$idA	= (array)$_POST['id'];
    	$idA OR iPHP::alert("请选择要操作的用户");
    	$ids	= implode(',',(array)$_POST['id']);
    	$batch	= $_POST['batch'];
    	switch($batch){
    		case 'dels':
				iPHP::$break	= false;
	    		foreach($idA AS $id){
	    			$this->dodel($id,false);
	    		}
	    		iPHP::$break	= true;
				iPHP::OK('用户全部删除完成!','js:1');
    		break;
		}
	}
    function dodel($uid = null,$dialog=true){
    	$uid===null && $uid=$this->uid;
		$uid OR iPHP::alert('请选择要删除的用户');
		$uid=="1" && iPHP::alert('不能删除超级管理员');
		iDB::query("DELETE FROM `#iCMS@__members` WHERE `uid` = '$uid'");
		$dialog && iPHP::OK('用户删除完成','js:parent.$("#tr'.$uid.'").remove();');
    }
}
