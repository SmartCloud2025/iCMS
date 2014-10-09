<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: search.app.php 634 2013-04-03 06:02:53Z coolmoo $
*/
class searchApp{
    function __construct() {
    	$this->id	= (int)$_GET['id'];
    }

    function do_iCMS(){
        if($_GET['keyword']) {
			$sql =" WHERE `search` like '%{$_GET['keyword']}%'";
        }

        $orderby    = $_GET['orderby']?$_GET['orderby']:"id DESC";
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total      = iPHP::total(false,"SELECT count(*) FROM `#iCMS@__search_log` {$sql}","G");
        iPHP::pagenav($total,$maxperpage,"条记录");
        $rs     = iDB::all("SELECT * FROM `#iCMS@__search_log` {$sql} order by {$orderby} LIMIT ".iPHP::$offset." , {$maxperpage}");
        $_count = count($rs);
    	include iACP::view("search.manage");
    }
    function do_del($id = null,$dialog=true){
    	$id===null && $id=$this->id;
		$id OR iPHP::alert('请选择要删除的记录!');
		iDB::query("DELETE FROM `#iCMS@__search_log` WHERE `id` = '$id'");
		$dialog && iPHP::success('记录已经删除','js:parent.$("#tr'.$id.'").remove();');
    }
    function do_batch(){
        $idArray = (array)$_POST['id'];
        $idArray OR iPHP::alert("请选择要操作的记录");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
    	switch($batch){
    		case 'dels':
				iPHP::$break	= false;
	    		foreach($idArray AS $id){
	    			$this->do_del($id,false);
	    		}
	    		iPHP::$break	= true;
				iPHP::success('记录全部删除完成!','js:1');
    		break;
		}
	}
}
