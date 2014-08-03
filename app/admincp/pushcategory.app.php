<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: pushcategory.app.php 2374 2014-03-17 11:46:13Z coolmoo $
*/
defined('iPHP') OR exit('What are you doing?');
iACP::app('category','import');
class pushcategoryApp extends categoryApp {
    function __construct() {
        parent::__construct(iCMS_APP_PUSH);
        $this->name_text    = "版块";
    }
    function do_add(){
        if($this->cid) {
            iMember::CP($this->cid,'Permission_Denied',APP_URI);
            $rs		= iDB::row("SELECT * FROM `#iCMS@__category` WHERE `cid`='$this->cid' LIMIT 1;",ARRAY_A);
            $rootid	= $rs['rootid'];
        }else {
            $rootid=(int)$_GET['rootid'];
            $rootid && iMember::CP($rootid,'Permission_Denied',APP_URI);
        }
        if(empty($rs)) {
            $rs=array();
            $rs['status']		= '1';
            $rs['orderNum']		= '0';
        }
        include iACP::view("pushcategory.add");
    }
    function merge($tocid,$cid){
        iDB::query("UPDATE `#iCMS@__push` SET `cid` ='$tocid' WHERE `cid` ='$cid'"); 
    }
    function updateCount($cid){
        $cc = iDB::value("SELECT count(*) FROM `#iCMS@__push` where `cid`='$cid'");
        iDB::query("UPDATE `#iCMS@__category` SET `count` ='$cc' WHERE `cid` ='$cid'");       
    }
    function listbtn($rs){}
    function treebtn($C){
        return '<a href="'.__ADMINCP__.'=push&do=add&cid='.$C['cid'].'" class="btn btn-small"><i class="fa fa-edit"></i> 推送</a> <a href="'.__ADMINCP__.'=push&do=manage&cid='.$C['cid'].'&sub=on" class="btn btn-small"><i class="fa fa-list-alt"></i> 推送管理</a> ';
    }
    function batchbtn(){}
}
