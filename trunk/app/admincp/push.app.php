<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: push.app.php 2393 2014-04-09 13:14:23Z coolmoo $
*/
class pushApp{
    function __construct() {
    	$this->id	= (int)$_GET['id'];
        $this->pushcategory	= iPHP::appClass("category",iCMS_APP_PUSH);
    }
    function doadd(){
        $id		= (int)$_GET['id'];
        $rs		= array();
        $_GET['title'] 	&& $rs['title']	= $_GET['title'];
        $_GET['pic'] 	&& $rs['pic']	= $_GET['pic'];
        $_GET['url'] 	&& $rs['url']	= $_GET['url'];
        
        $_GET['title2']	&& $rs['title2']= $_GET['title2'];
        $_GET['pic2'] 	&& $rs['pic2']	= $_GET['pic2'];
        $_GET['url2'] 	&& $rs['url2']	= $_GET['url2'];
        
        $_GET['title3']	&& $rs['title3']= $_GET['title3'];
        $_GET['pic3'] 	&& $rs['pic3']	= $_GET['pic3'];
        $_GET['url3'] 	&& $rs['url3']	= $_GET['url3'];

        $id && $rs	= iDB::getRow("SELECT * FROM `#iCMS@__push` WHERE `id`='$id' LIMIT 1;",ARRAY_A);
        empty($rs['editor']) && $rs['editor']=empty(iMember::$Rs->nickname)?iMember::$Rs->username:iMember::$Rs->nickname;
        empty($rs['userid']) && $rs['userid']=iMember::$uId;
        $rs['addtime']	= $id?get_date(0,"Y-m-d H:i:s"):get_date($rs['addtime'],'Y-m-d H:i:s');
        $cid			= empty($rs['cid'])?(int)$_GET['cid']:$rs['cid'];
        $cata_option	= $this->pushcategory->select($cid,0,1,1);

        empty($rs['userid']) && $rs['userid']=iMember::$uId;
        $strpos 	= strpos(__REF__,'?');
        $REFERER 	= $strpos===false?'':substr(__REF__,$strpos);
    	include iACP::tpl("push.add");
    }

    function doiCMS(){
    	iACP::$app_method="domanage";
    	$this->domanage();
    }
    function domanage($doType=null) {
        $mtime      = microtime();
        $mtime      = explode(' ', $mtime);
        $time_start = $mtime[1] + $mtime[0];
        $cid        = (int)$_GET['cid'];
        $sql        = " where ";
        switch($doType){ //status:[0:草稿][1:正常][2:回收][3:审核][4:不合格]
        	case 'inbox'://草稿
        		$sql.="`status` ='0'";
        		if(iMember::$Rs->gid!=1){
        			$sql.=" AND `userid`='".iMember::$uId."'";
        		}
        		$position="草稿";
        	break;
         	case 'trash'://回收站
        		$sql.="`status` ='2'";
        		$position="回收站";
        	break;
         	case 'examine'://审核
        		$sql.="`status` ='3'";
        		$position="已审核";
        	break;
         	case 'off'://未通过
        		$sql.="`status` ='4'";
        		$position="未通过";
        	break;
       		default:
	       		$sql.=" `status` ='1'";
		       	$cid && $position=$this->pushcategory->category[$cid]['name'];
		}
		
        if($_GET['keywords']) {
			$sql.=" AND CONCAT(title,title2,title3) REGEXP '{$_GET['keywords']}'";
        }
        $cid=iMember::CP($cid)?$cid:"0";
        if($cid) {
            $cidIN=$this->pushcategory->cid($cid).$cid;
            if(isset($_GET['sub']) && strstr($cidIN,',')) {
                $sql.=" AND cid IN(".$cidIN.")";
            }else {
                $sql.=" AND cid ='$cid'";
            }
            //$sql.=" OR `vlink` REGEXP '[[:<:]]".preg_quote($cid, '/')."[[:>:]]')";
        }else {
            iMember::$cpower && $sql.=" AND cid IN(".implode(',',(array)iMember::$cpower).")";
        }
        isset($_GET['nopic'])   && $sql.=" AND `isPic` ='0'";
        $_GET['starttime'] 	&& $sql.=" and `addtime`>=UNIX_TIMESTAMP('".$_GET['starttime']." 00:00:00')";
        $_GET['endtime'] 	&& $sql.=" and `addtime`<=UNIX_TIMESTAMP('".$_GET['endtime']." 23:59:59')";


        isset($_GET['userid']) && $uri.='&userid='.(int)$_GET['userid'];
        isset($_GET['keyword']) && $uri.='&keyword='.$_GET['keyword'];
        isset($_GET['pid']) && $uri.='&pid='.$_GET['pid'];
        isset($_GET['cid']) && $uri.='&cid='.$_GET['cid'];
        (isset($_GET['pid']) && $_GET['pid']!='-1') && $uri.='&pid='.$_GET['at'];

        $orderby    =$_GET['orderby']?$_GET['orderby']:"id DESC";
        $maxperpage =(int)$_GET['perpage']>0?$_GET['perpage']:20;
        $total      = iPHP::total(false,"SELECT count(*) FROM `#iCMS@__push` {$sql}","G");
        iPHP::pagenav($total,$maxperpage,"条记录");
        $rs         =iDB::getArray("SELECT * FROM `#iCMS@__push` {$sql} order by {$orderby} LIMIT ".iPHP::$offset." , {$maxperpage}");
//echo iDB::$last_query;
//iDB::last_query='explain '.iDB::$last_query;
//$explain=iDB::getRow(iDB::last_query);
//var_dump($explain);
        $_count=count($rs);
        include iACP::tpl("push.manage");
//		$mtime = microtime();
//		$mtime = explode(' ', $mtime);
//		$time_end = $mtime[1] + $mtime[0];
//		echo  "<h1>".($time_end - $time_start);
    }
    function dosave(){
        $id			= (int)$_POST['id'];
        $cid		= (int)$_POST['cid'];
        $userid		= (int)$_POST['userid'];
        $pid		= (int)$_POST['pid'];
        $editor		= iS::escapeStr($_POST['editor']);
        $orderNum	= _int($_POST['orderNum']);
        $addtime	= iPHP::str2time($_POST['addtime']);
        
        
        
        $title		= iS::escapeStr($_POST['title']);
        $pic		= $this->getpic($_POST['pic']);
        $description= iS::escapeStr($_POST['description']);
        $url		= iS::escapeStr($_POST['url']);
        
        $title2		= iS::escapeStr($_POST['title2']);
        $pic2		= $this->getpic($_POST['pic2']);
        $description2= iS::escapeStr($_POST['description2']);
        $url2		= iS::escapeStr($_POST['url2']);
        
        $title3		= iS::escapeStr($_POST['title3']);
        $pic3		= $this->getpic($_POST['pic3']);
        $description3= iS::escapeStr($_POST['description3']);
        $url3		= iS::escapeStr($_POST['url3']);
        
        $metadata	= iS::escapeStr($_POST['metadata']);
        $metadata	= $metadata?addslashes(serialize($metadata)):'';

		empty($userid) && $userid=iMember::$uId;
        empty($title) && iPHP::alert('1.标题必填');
        empty($cid) && iPHP::alert('请选择所属栏目');

        $ispic	= empty($pic)?0:1;
        
        $status	= 1;
        if(empty($id)) {
			iDB::query("INSERT INTO `#iCMS@__push` (`cid`, `rootid`, `pid`, `ispic`, `editor`, `userid`, `title`, `pic`, `url`, `description`, `title2`, `pic2`, `url2`, `description2`, `title3`, `pic3`, `url3`, `description3`, `orderNum`, `metadata`, `addtime`,`hits`, `status`)
VALUES ('$cid', '0', '$pid', '$ispic', '$editor', '$userid', '$title', '$pic', '$url', '$description', '$title2', '$pic2', '$url2', '$description2', '$title3', '$pic3', '$url3', '$description3', '$orderNum', '$metadata', '$addtime','$hits', '$status');");
            iDB::query("UPDATE `#iCMS@__category` SET `count` = count+1 WHERE `cid` ='$cid' LIMIT 1 ");
            iPHP::OK('推送完成','url:'.APP_URI);
        }else{
            $OP	= iDB::getRow("SELECT `cid` FROM `#iCMS@__push` where `id` ='$id' LIMIT 1;");
			iDB::query("UPDATE `#iCMS@__push` SET `cid` = '$cid', `pid` = '$pid', `ispic` = '$ispic', `editor` = '$editor', `userid` = '$userid', `title` = '$title', `pic` = '$pic', `url` = '$url', `description` = '$description', `title2` = '$title2', `pic2` = '$pic2', `url2` = '$url2', `description2` = '$description2', `title3` = '$title3', `pic3` = '$pic3', `url3` = '$url3', `description3` = '$description3', `orderNum` = '$orderNum', `metadata` = '$metadata', `addtime` = '$addtime', `status` = '$status'
WHERE `id` = '$id';");
            if($OP->cid!=$cid) {
                iDB::query("UPDATE `#iCMS@__category` SET `count` = count-1 WHERE `cid` ='{$OP->cid}' and `count`>0 LIMIT 1 ");
                iDB::query("UPDATE `#iCMS@__category` SET `count` = count+1 WHERE `cid` ='$cid' LIMIT 1 ");
            }
            iPHP::OK('编辑完成!','url:'.APP_URI);
        }
    }
	function getpic($path){
		$uri 	= parse_url(iCMS::$config['FS']['url']);
        $pic	= iS::escapeStr($path);
        
	    if(strstr(strtolower($pic),$uri['host'])){
	    	$pic = iFS::fp($pic,"-http");
	    }else{
			strstr($pic, 'http://') && $pic = iFS::http($pic);
		}
		return $pic;
	}
    function dodel($id = null,$dialog=true){
    	$id===null && $id=$this->id;
		$id OR iPHP::alert('请选择要删除的推送');
		iDB::query("DELETE FROM `#iCMS@__push` WHERE `id` = '$id'");
		$dialog && iPHP::OK('推送删除完成','js:parent.$("#tr'.$id.'").remove();');
    }
    function dobatch(){
        $idArray = (array)$_POST['id'];
        $idArray OR iPHP::alert("请选择要删除的推送");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
    	switch($batch){
    		case 'dels':
				iPHP::$break	= false;
	    		foreach($idArray AS $id){
	    			$this->dodel($id,false);
	    		}
	    		iPHP::$break	= true;
				iPHP::OK('全部删除完成!','js:1');
    		break;
		}
	}
}
