<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: comment.app.php 634 2013-04-03 06:02:53Z coolmoo $
*/
iPHP::app('user.class','static');
class commentApp{
    function __construct() {
    }
    function do_iCMS($appid=0){
    	iPHP::import(iPHP_APP_CORE .'/iAPP.class.php');
        $this->categoryApp = iACP::app('category','all');
        $this->category    = $this->categoryApp->category;

        $sql = "WHERE 1=1";
		if($appid ||$_GET['appid']){
			$_GET['appid'] && $appid=(int)$_GET['appid'];
			$sql.= " AND `appid`='$appid'";
		}
		$_GET['iid']   && $sql.= " AND `iid`='".(int)$_GET['iid']."'";
		if($_GET['cid']){
            $cid = (int)$_GET['cid'];
            if(isset($_GET['sub'])){
                $cids  = $this->categoryApp->get_ids($cid,true);
                array_push ($cids,$cid);
                $sql.=" AND cid IN(".implode(',', $cids).")";
            }else{
                $sql.=" AND cid ='$cid'";
            }
        }
		$_GET['userid']&& $sql.= " AND `userid`='".(int)$_GET['userid']."'";
		$_GET['ip']    && $sql.= " AND `ip`='".$_GET['ip']."'";
        if($_GET['keywords']) {
            $sql.="  AND CONCAT(username,title) REGEXP '{$_GET['keywords']}'";
        }

        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total		= iPHP::total(false,"SELECT count(*) FROM `#iCMS@__comment` {$sql}","G");
        iPHP::pagenav($total,$maxperpage,"条评论");
        $rs     = iDB::all("SELECT * FROM `#iCMS@__comment` {$sql} order by id DESC LIMIT ".iPHP::$offset." , {$maxperpage}");
        $_count = count($rs);
    	include iACP::view("comment.manage");
    }
    function do_article(){
    	$this->do_iCMS(iCMS_APP_ARTICLE);
    }
    function do_manage($appid=0){
    	$this->do_iCMS($appid);
    }
    function do_get_reply(){
    	$_GET['id'] OR exit("请选择要操作的评论");
    	$id = (int)$_GET['id'];
        $comment = iDB::row("SELECT * FROM `#iCMS@__comment` WHERE `id`='$id' LIMIT 1");
        empty($comment) && exit('<div class="claerfix mb10"></div>评论已被删除');
        echo nl2br($comment->content);
        echo '<div class="claerfix mb10"></div>';
        echo '<span class="label">'.get_date($comment->addtime,'Y-m-d H:i:s').'</span> ';
        echo '<span class="label label-info"><i class="fa fa-thumbs-o-up"></i> '.$comment->up.'</span>';

    }
    function do_del($id = null,$dialog=true){
    	$id===null && $id=(int)$_GET['id'];
    	$id OR iPHP::alert('请选择要删除的评论!');
    	$comment = iDB::row("SELECT * FROM `#iCMS@__comment` WHERE `id`='$id' LIMIT 1");

        iPHP::import(iPHP_APP_CORE .'/iAPP.class.php');
        $table = app::get_table($comment->appid);

        iDB::query("UPDATE {$table['name']} SET comments = comments-1 WHERE `comments`>0 AND `{$table['primary']}`='{$comment->iid}' LIMIT 1;");
        iDB::query("UPDATE `#iCMS@__user` SET comments = comments-1 WHERE `comments`>0 AND `uid`='{$comment->userid}' LIMIT 1;");
		iDB::query("DELETE FROM `#iCMS@__comment` WHERE `id` = '$id';");

        $dialog && iPHP::success('评论删除完成','js:parent.$("#id-'.$id.'").remove();');
    }
    function do_batch(){
        $idArray = (array)$_POST['id'];
        $idArray OR iPHP::alert("请选择要操作的评论");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
    	switch($batch){
    		case 'dels':
				iPHP::$break	= false;
	    		foreach($idArray AS $id){
	    			$this->do_del($id,false);
	    		}
	    		iPHP::$break	= true;
				iPHP::success('评论全部删除完成!','js:1');
    		break;
		}
	}
}
