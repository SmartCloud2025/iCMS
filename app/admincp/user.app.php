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
class userApp{
    function __construct() {
        $this->uid      = (int)$_GET['id'];
        $this->groupApp = iACP::app('groups',0);
    }
    function do_add(){
        if($this->uid) {
            $rs = iDB::row("SELECT * FROM `#iCMS@__user` WHERE `uid`='$this->uid' LIMIT 1;");
            if($rs){
                $userdata = iDB::row("SELECT * FROM `#iCMS@__user_data` WHERE `uid`='$this->uid' LIMIT 1;");
            }
        }
        include iACP::view("user.add");
    }
    function do_login(){
        if($this->uid) {
            $user = iDB::row("SELECT * FROM `#iCMS@__user` WHERE `uid`='$this->uid' LIMIT 1;",ARRAY_A);
            iPHP::app('user.class','static');
            user::set_cookie($user['username'],$user['password'],$user);
            $url = iPHP::router(array('/{uid}/',$this->uid));
            iPHP::gotourl($url);
        }
    }
    function do_iCMS(){
        $sql   = "WHERE 1=1";
        $_GET['gid'] && $sql.=" AND `gid`='{$_GET['gid']}'";
        $orderby    = $_GET['orderby']?$_GET['orderby']:"uid DESC";
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total      = iPHP::total(false,"SELECT count(*) FROM `#iCMS@__user` {$sql}","G");
        iPHP::pagenav($total,$maxperpage,"个用户");
        $rs     = iDB::all("SELECT * FROM `#iCMS@__user` {$sql} order by {$orderby} LIMIT ".iPHP::$offset." , {$maxperpage}");
        $_count = count($rs);
        include iACP::view("user.manage");
    }
    function do_save(){
        $uid      = (int)$_POST['uid'];
        $gid      = (int)$_POST['gid'];
        $gender   = (int)$_POST['sex'];
        $type     = $_POST['type'];
        $username = iS::escapeStr($_POST['uname']);
        $nickname = iS::escapeStr($_POST['nickname']);
        $realname = iS::escapeStr($_POST['realname']);
        $power    = $_POST['power']?json_encode($_POST['power']):'';
        $cpower   = $_POST['cpower']?json_encode($_POST['cpower']):'';
        $info     = array();
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
            $fields = array('gid','gender','username','nickname','realname','power', 'cpower','info');
            $data   = compact ($fields);
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
		iDB::query("DELETE FROM `#iCMS@__user` WHERE `uid` = '$uid'");
		$dialog && iPHP::success('用户删除完成','js:parent.$("#tr'.$uid.'").remove();');
    }
}
