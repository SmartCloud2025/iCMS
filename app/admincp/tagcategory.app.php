<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: tagcategory.app.php 2372 2014-03-16 07:24:56Z coolmoo $
*/
defined('iPHP') OR exit('What are you doing?');
iACP::app('category','import');
class tagcategoryApp extends categoryApp {
    function __construct() {
        parent::__construct(iCMS_APP_TAG);
        $this->name_text    = "分类";
    }
    function merge($tocid,$cid){
        iDB::query("UPDATE `#iCMS@__tags` SET `tcid` ='$tocid' WHERE `tcid` ='$cid'"); 
    }
    function update_count($cid){
        $cc = iDB::value("SELECT count(*) FROM `#iCMS@__tags` where `tcid`='$cid'");
        iDB::query("UPDATE `#iCMS@__category` SET `count` ='$cc' WHERE `cid` ='$cid'");       
    }
    function listbtn($rs){}
    function treebtn($C){
        return '<a href="'.__ADMINCP__.'=tags&do=add&tcid='.$C['cid'].'" class="btn btn-small"><i class="fa fa-edit"></i> 标签</a> <a href="'.__ADMINCP__.'=tags&do=manage&tcid='.$C['cid'].'&sub=on" class="btn btn-small"><i class="fa fa-list-alt"></i> 标签管理</a> ';
    }

}
