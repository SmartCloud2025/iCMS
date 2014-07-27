<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: keywords.app.php 2374 2014-03-17 11:46:13Z coolmoo $
*/
class keywordsApp{
    function __construct() {
    	$this->id	= (int)$_GET['id'];
    }
    function do_add(){
        if($this->id) {
            iMember::CP($this->cid,'Permission_Denied',APP_URI);
            $rs			= iDB::row("SELECT * FROM `#iCMS@__keywords` WHERE `id`='$this->id' LIMIT 1;",ARRAY_A);
        }else{
        	$rs['keyword']	= $_GET['keyword'];
        	$rs['url']		= $_GET['url'];
        }
        include iACP::view("keywords.add");
    }
    function do_save(){
		$id		= (int)$_POST['id'];
		$keyword= iS::escapeStr($_POST['keyword']);
		$url	= iS::escapeStr($_POST['url']);
		$times	= (int)$_POST['times'];

        $keyword OR iPHP::alert('关键词不能为空!');
        $url 	OR iPHP::alert('链接不能为空!');
        $fields = array('keyword', 'url', 'times');
        $data   = compact ($fields);

        if(empty($id)) {
            iDB::value("SELECT `id` FROM `#iCMS@__keywords` where `keywtimesord` ='$keyword'") && iPHP::alert('该关键词已经存在!');
            iDB::insert('keywords',$data);
            $this->cache();
            $msg="关键词添加完成!";
        }else {
            iMember::CP($id,'Permission_Denied',APP_URI);
            iDB::value("SELECT `id` FROM `#iCMS@__keywords` where `keyword` ='$keyword' AND `id` !='$id'") && iPHP::alert('该关键词已经存在!');
            iDB::update('keywords', $data, array('id'=>$id));
            $this->cache();
            $msg="关键词编辑完成!";
        }
        iPHP::success($msg,'url:'.APP_URI);
    }

    function do_iCMS(){
        if($_GET['keywords']) {
			$sql=" WHERE `keyword` REGEXP '{$_GET['keywords']}'";
        }
        $orderby	=$_GET['orderby']?$_GET['orderby']:"id DESC";
        $maxperpage =(int)$_GET['perpage']>0?$_GET['perpage']:20;
		$total		= iPHP::total(false,"SELECT count(*) FROM `#iCMS@__keywords` {$sql}","G");
        iPHP::pagenav($total,$maxperpage,"个关键词");
        $rs     = iDB::all("SELECT * FROM `#iCMS@__keywords` {$sql} order by {$orderby} LIMIT ".iPHP::$offset." , {$maxperpage}");
        $_count = count($rs);
    	include iACP::view("keywords.manage");
    }
    function do_del($id = null,$dialog=true){
    	$id===null && $id=$this->id;
		$id OR iPHP::alert('请选择要删除的关键词!');
		iDB::query("DELETE FROM `#iCMS@__keywords` WHERE `id` = '$id'");
		$this->cache();
		$dialog && iPHP::success('关键词已经删除','js:parent.$("#tr'.$id.'").remove();');
    }
    function do_batch(){
        $idArray = (array)$_POST['id'];
        $idArray OR iPHP::alert("请选择要操作的关键词");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
    	switch($batch){
    		case 'dels':
				iPHP::$break	= false;
	    		foreach($idArray AS $id){
	    			$this->do_del($id,false);
	    		}
	    		iPHP::$break	= true;
				iPHP::success('关键词全部删除完成!','js:1');
    		break;
		}
	}
    function cache(){
    	$rs	= iDB::all("SELECT * FROM `#iCMS@__keywords` ORDER BY CHAR_LENGTH(`keyword`) DESC");
		iCache::set('iCMS/keywords',$rs,0);
    }
}
