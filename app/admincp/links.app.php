<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: links.app.php 634 2013-04-03 06:02:53Z coolmoo $
*/
class linksApp{
    function __construct() {
    	$this->id	= (int)$_GET['id'];
    }
    function do_add(){
        if($this->id) {
            $rs = iDB::row("SELECT * FROM `#iCMS@__links` WHERE `id`='$this->id' LIMIT 1;",ARRAY_A);
        }else{
            $rs['keyword'] = $_GET['keyword'];
            $rs['url']     = $_GET['url'];
        }
        include iACP::view("links.add");
    }
    function do_save(){
		$id			= (int)$_POST['id'];
		$sortid		= (int)$_POST['sortid'];
		$name		= iS::escapeStr($_POST['name']);
		$logo		= iS::escapeStr($_POST['logo']);
		$url		= iS::escapeStr($_POST['url']);
		$desc		= iS::escapeStr($_POST['desc']);
		$ordernum	= (int)$_POST['ordernum'];

        $name 	OR iPHP::alert('网站不能为空!');
        $url 	OR iPHP::alert('链接不能为空!');
        $fields = array('sortid', 'name', 'logo', 'url', 'desc', 'ordernum');
        $data   = compact ($fields);
        if(empty($id)) {
            iDB::value("SELECT `id` FROM `#iCMS@__links` where `name` ='$name'") && iPHP::alert('该网站已经存在!');
            iDB::insert('links',$data);
            $msg="网站添加完成!";
        }else {
            iDB::value("SELECT `id` FROM `#iCMS@__links` where `name` ='$name' AND `id` !='$id'") && iPHP::alert('该网站已经存在!');
            iDB::update('links', $data, array('id'=>$id));
            $msg="网站编辑完成!";
        }
        iPHP::success($msg,'url:'.APP_URI);
    }

    function do_iCMS(){
        if($_GET['keywords']) {
			$sql=" WHERE CONCAT(name,url) REGEXP '{$_GET['keywords']}'";
        }
        if($_GET['sortid']) {
			$sql=" WHERE `sortid` = '{$_GET['sortid']}'";
        }

        $orderby	=$_GET['orderby']?$_GET['orderby']:"id DESC";
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
		$total		= iPHP::total(false,"SELECT count(*) FROM `#iCMS@__links` {$sql}","G");
        iPHP::pagenav($total,$maxperpage,"个网站");
        $rs     = iDB::all("SELECT * FROM `#iCMS@__links` {$sql} order by {$orderby} LIMIT ".iPHP::$offset." , {$maxperpage}");
        $_count = count($rs);
    	include iACP::view("links.manage");
    }
    function do_del($id = null,$dialog=true){
    	$id===null && $id=$this->id;
		$id OR iPHP::alert('请选择要删除的网站!');
		iDB::query("DELETE FROM `#iCMS@__links` WHERE `id` = '$id'");
		$dialog && iPHP::success('网站已经删除','js:parent.$("#tr'.$id.'").remove();');
    }
    function do_batch(){
        $idArray = (array)$_POST['id'];
        $idArray OR iPHP::alert("请选择要操作的网站");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
    	switch($batch){
    		case 'dels':
				iPHP::$break	= false;
	    		foreach($idArray AS $id){
	    			$this->do_del($id,false);
	    		}
	    		iPHP::$break	= true;
				iPHP::success('网站全部删除完成!','js:1');
    		break;
		}
	}
}
