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
        $this->uid      = (int)$_GET['id'];
        $this->groupApp = iACP::app('groups',1);
    }

    function do_job(){
		require_once iPHP_APP_CORE.'/iJob.class.php';
		$job	= new JOB;
        $this->uid OR $this->uid = iMember::$userid;
		$job->count_post($this->uid);
        $month  = $job->month();
        $pmonth = $job->month($job->pmonth['start']);
        $rs     = iDB::row("SELECT * FROM `#iCMS@__members` WHERE `uid`='$this->uid' LIMIT 1;");
		include iACP::view("account.job");
    }
    function do_edit(){
        $this->uid = iMember::$userid;
        $this->do_add();
    }
    function do_add(){
        if($this->uid) {
            $rs = iDB::row("SELECT * FROM `#iCMS@__members` WHERE `uid`='$this->uid' LIMIT 1;");
            $rs->info && $rs->info = unserialize($rs->info);
        }
        include iACP::view("account.add");
    }
    function do_iCMS(){
    	if($_GET['job']){
    		require_once iPHP_APP_CORE.'/iJob.class.php';
    		$job	=new JOB;
    	}
    	$sql	= "WHERE 1=1";
    	//isset($this->type)	&& $sql.=" AND `type`='$this->type'";
		$_GET['gid'] && $sql.=" AND `gid`='{$_GET['gid']}'";
        $orderby    = $_GET['orderby']?$_GET['orderby']:"uid DESC";
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total      = iPHP::total(false,"SELECT count(*) FROM `#iCMS@__members` {$sql}","G");
        iPHP::pagenav($total,$maxperpage,"个用户");
        $rs         = iDB::all("SELECT * FROM `#iCMS@__members` {$sql} order by {$orderby} LIMIT ".iPHP::$offset." , {$maxperpage}");
        $_count		= count($rs);
    	include iACP::view("account.manage");
    }
    function do_save(){
        $uid      = (int)$_POST['uid'];
        $gender   = (int)$_POST['gender'];
        $type     = $_POST['type'];
        $username = iS::escapeStr($_POST['uname']);
        $nickname = iS::escapeStr($_POST['nickname']);
        $realname = iS::escapeStr($_POST['realname']);
        $power    = $_POST['power']?json_encode($_POST['power']):'';
        $cpower   = $_POST['cpower']?json_encode($_POST['cpower']):'';
        $gid      = 0;
        $info     = array();
        $info['icq']       = iS::escapeStr($_POST['icq']);
        $info['home']      = iS::escapeStr($_POST['home']);
        $info['year']      = intval($_POST['year']);
        $info['month']     = intval($_POST['month']);
        $info['day']       = intval($_POST['day']);
        $info['from']      = iS::escapeStr($_POST['from']);
        $info['signature'] = iS::escapeStr($_POST['signature']);
        $info              = addslashes(serialize($info));
        $_POST['pwd'] && $password = md5($_POST['pwd']);

        $username OR iPHP::alert('账号不能为空');

        if(iACP::is_superadmin()){
            $gid = (int)$_POST['gid'];
        }else{
            isset($_POST['gid']) && iPHP::alert('您没有权限更改角色');
        }

        $fields = array('gid','gender','username','nickname','realname','power', 'cpower','info');
        $data   = compact ($fields);
        if(empty($uid)) {
            iDB::value("SELECT `uid` FROM `#iCMS@__members` where `username` ='$username' LIMIT 1") && iPHP::alert('该账号已经存在');
            $_data = compact(array('password','regtime', 'lastip', 'lastlogintime', 'logintimes', 'post', 'type', 'status'));
            $_data['regtime']       = time();
            $_data['lastip']        = iPHP::getIp();
            $_data['lastlogintime'] = time();
            $_data['status']        = '1';
            $data = array_merge($data, $_data);
            iDB::insert('members',$data);
            $msg="账号添加完成!";
        }else {
            iDB::value("SELECT `uid` FROM `#iCMS@__members` where `username` ='$username' AND `uid` !='$uid' LIMIT 1") && iPHP::alert('该账号已经存在');
            iDB::update('members', $data, array('uid'=>$uid));
            $password && iDB::query("UPDATE `#iCMS@__members` SET `password`='$password' WHERE `uid` ='".$uid."'");
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
