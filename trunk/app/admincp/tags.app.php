<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: tags.app.php 2406 2014-04-28 02:24:46Z coolmoo $
*/
class tagsApp{
    function __construct() {
        $this->id          = (int)$_GET['id'];
        $this->appid       = iCMS_APP_TAG;
        $this->category    = iPHP::appClass("category",'all');
        $this->tagcategory = iPHP::appClass("category",$this->appid);
    	iPHP::appClass("tag",'break');
    }
    function doadd(){
        $this->id && $rs	= iDB::getRow("SELECT * FROM `#iCMS@__tags` WHERE `id`='$this->id' LIMIT 1;",ARRAY_A);
        $rs['metadata'] && $rs['metadata']=unserialize($rs['metadata']);
        include iACP::view('tags.add');
    }
    function doupdate(){
    	$sql	= iACP::iDT($_GET['iDT']);
    	$sql &&	iDB::query("UPDATE `#iCMS@__tags` SET $sql WHERE `id` ='$this->id' LIMIT 1 ");
    	$this->id && tag::cache($this->id,'id');
    	iPHP::OK('操作成功!','js:1');
    }
    function doiCMS(){
    	iACP::$app_method="domanage";
    	$this->domanage();
    }
    function domanage(){
        $sql			= " where 1=1";
        $_GET['keywords'] && $sql.=" AND CONCAT(name,seotitle,subtitle,keywords,description) REGEXP '{$_GET['keywords']}'";
        $cid			= (int)$_GET['cid'];
        $cid	= iMember::CP($cid)?$cid:"0";
        if($cid) {
            $cidIN=$this->category->cid($cid).$cid;
            if(isset($_GET['sub']) && strstr($cidIN,',')) {
                $sql.=" AND cid IN(".$cidIN.")";
            }else {
                $sql.=" AND cid ='$cid'";
            }
        }
        $tcid	= (int)$_GET['tcid'];
        if($tcid) {
            $tcidIN=$this->tagcategory->cid($tcid).$tcid;
            if(isset($_GET['sub']) && strstr($tcidIN,',')) {
                $sql.=" AND tcid IN(".$cidIN.")";
            }else {
                $sql.=" AND tcid ='$tcid'";
            }
        }
		isset($_GET['pic']) && $sql.=" AND `ispic` ='".($_GET['pic']?1:0)."'";
		isset($_GET['pid']) && $_GET['pid']!="-1" && $sql.=" AND `pid` ='".(int)$_GET['pid']."'";

        $orderby	=$_GET['orderby']?$_GET['orderby']:"id DESC";
        $maxperpage =(int)$_GET['perpage']>0?$_GET['perpage']:20;
        $total		= iPHP::total(false,"SELECT count(*) FROM `#iCMS@__tags` {$sql}","G");
        iPHP::pagenav($total,$maxperpage,"个标签");
        $rs			= iDB::getArray("SELECT * FROM `#iCMS@__tags` {$sql} order by {$orderby} LIMIT ".iPHP::$offset." , {$maxperpage}");
//echo iDB::$last_query;
//iDB::$last_query='explain '.iDB::$last_query;
//$explain=iDB::getRow(iDB::$last_query);
//var_dump($explain);
        $_count=count($rs);
    	include iACP::view("tags.manage");
    }
    function dosave(){
        $id          = (int)$_POST['id'];
        $uid         = (int)$_POST['uid'];
        $cid         = (int)$_POST['cid'];
        $tcid        = (int)$_POST['tcid'];
        $pid         = (int)$_POST['pid'];
        $name        = iS::escapeStr($_POST['name']);
        $subtitle    = iS::escapeStr($_POST['subtitle']);
        $tkey        = iS::escapeStr($_POST['tkey']);
        $seotitle    = iS::escapeStr($_POST['seotitle']);
        $keywords    = iS::escapeStr($_POST['keywords']);
        $pic         = iS::escapeStr($_POST['pic']);
        $description = iS::escapeStr($_POST['description']);
        $url         = iS::escapeStr($_POST['url']);
        $related     = iS::escapeStr($_POST['related']);
        $tpl         = iS::escapeStr($_POST['tpl']);
        $weight      = _int($_POST['weight']);
        $ordernum    = _int($_POST['ordernum']);
        $status      = (int)$_POST['status'];
        $ispic       = $pic?'1':'0';
        $pubdate     = time();
        $metadata    = iS::escapeStr($_POST['metadata']);

        $uid OR $uid= iMember::$uId;
        
        $name OR iPHP::alert('标签名称不能为空！');
        $cid OR iPHP::alert('请选择标签所属栏目！');

        if($metadata){
            $md = array();
            foreach($metadata['key'] AS $_mk=>$_mval){
                !preg_match("/[a-zA-Z0-9_\-]/",$_mval) && iPHP::alert($this->name_text.'附加属性名称只能由英文字母、数字或_-组成(不支持中文)');
                $md[$_mval]=$metadata['value'][$_mk];
            }
            $metadata   = addslashes(serialize($md));
        }

		if(empty($id)) {
			iDB::getValue("SELECT `id` FROM `#iCMS@__tags` where `name` = '$name'") && iPHP::alert('该标签已经存在!请检查是否重复');
		}
		if(empty($tkey) && $url){
			$tkey = substr(md5($url),8,16);
			iDB::getValue("SELECT `id` FROM `#iCMS@__tags` where `tkey` = '$tkey'") && iPHP::alert('该自定义链接已经存在!请检查是否重复');
		}
		$tkey OR $tkey = strtolower(iPHP::pinyin($name));
		strstr($pic, 'http://') && $pic = iFS::http($pic);
		
		if(empty($id)){
			iDB::query("INSERT INTO `#iCMS@__tags`
            (`uid`, `cid`, `tcid`, `pid`, `tkey`, `name`, `seotitle`, `subtitle`, `keywords`, `description`, `metadata`,`ispic`, `pic`, `url`, `related`, `count`, `weight`, `tpl`, `ordernum`, `pubdate`, `status`)
VALUES ('$uid', '$cid', '$tcid', '$pid', '$tkey', '$name', '$seotitle', '$subtitle', '$keywords', '$description', '$metadata','$ispic', '$pic', '$url', '$related', '0', '$weight', '$tpl', '$ordernum', '$pubdate', '$status');");
			$id = iDB::$insert_id;
			tag::cache($id,'id');
	        iPHP::OK('标签添加完成',"url:".APP_URI);
		}else{
			iDB::query("UPDATE `#iCMS@__tags`
SET `uid` = '$uid', `cid` = '$cid', `tcid` = '$tcid', `pid` = '$pid', `tkey` = '$tkey', `name` = '$name', `seotitle` = '$seotitle', `subtitle` = '$subtitle', `keywords` = '$keywords', `description` = '$description', `metadata` = '$metadata', `ispic` = '$ispic', `pic` = '$pic', `url` = '$url', `related` = '$related',`weight` = '$weight',`tpl` = '$tpl', `ordernum` = '$ordernum', `pubdate` = '$pubdate', `status` = '$status'
WHERE `id` = '$id';");
			tag::cache($id,'id');
        	iPHP::OK('标签更新完成',"url:".APP_URI);
		}
    }
    
    function docache(){
    	tag::cache($this->id,'id');
    	iPHP::OK("标签缓存更新成功");
    }
    function dodel($id = null,$dialog=true){
    	$id===null && $id=$this->id;
    	tag::del($id,'id');
    	$dialog && iPHP::OK("标签删除成功",'js:parent.$("#tr'.$id.'").remove();');
    }
    function dobatch(){
        $idArray = (array)$_POST['id'];
        $idArray OR iPHP::alert("请选择要操作的标签");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
    	switch($batch){
    		case 'dels':
				iPHP::$break	= false;
	    		foreach($idArray AS $id){
	    			$this->dodel($id,false);
	    		}
	    		iPHP::$break	= true;
				iPHP::OK('标签全部删除完成!','js:1');
    		break;
    		case 'move':
		        $_POST['cid'] OR iPHP::alert("请选择目标栏目!");
		        $cid	=(int)$_POST['cid'];
		        foreach($idArray AS $id) {
		            $ocid	= iDB::getValue("SELECT `cid` FROM `#iCMS@__tags` where `id` ='$id'");
		            iDB::query("UPDATE `#iCMS@__tags` SET cid='$cid' WHERE `id` ='$id'");
		            if($ocid!=$cid) {
		                iDB::query("UPDATE `#iCMS@__category` SET `count` = count-1 WHERE `cid` ='{$ocid}' AND `count`>0 LIMIT 1 ");
		                iDB::query("UPDATE `#iCMS@__category` SET `count` = count+1 WHERE `cid` ='{$cid}' LIMIT 1 ");
		            }
		        }
		        iPHP::OK('成功移动到目标栏目!','js:1');
    		break;
    		case 'mvtcid':
		        $_POST['tcid'] OR iPHP::alert("请选择目标分类!");
		        $tcid	=(int)$_POST['tcid'];
		        foreach($idArray AS $id) {
		            $otcid	= iDB::getValue("SELECT `tcid` FROM `#iCMS@__tags` where `id` ='$id'");
		            iDB::query("UPDATE `#iCMS@__tags` SET tcid='$tcid' WHERE `id` ='$id'");
		            if($otcid!=$tcid) {
		                iDB::query("UPDATE `#iCMS@__category` SET `count` = count-1 WHERE `cid` ='{$otcid}' AND `count`>0 LIMIT 1 ");
		                iDB::query("UPDATE `#iCMS@__category` SET `count` = count+1 WHERE `cid` ='{$tcid}' LIMIT 1 ");
		            }
		        }
		        iPHP::OK('成功移动到目标分类!','js:1');
    		break;
    		case 'prop':
    			$pid = $_POST['pid'];
		        $sql	="`pid` = '$pid'";
    		break;
    		case 'top':
		        $top	=_int($_POST['mtop']);
		        $sql	="`weight` = '$top'";
    		break;
    		case 'keyword':
    			if($_POST['pattern']=='replace') {
    				$sql	="`keywords` = '".iS::escapeStr($_POST['mkeyword'])."'";
    			}elseif($_POST['pattern']=='addto') {
		        	foreach($idArray AS $id){
		        		$keywords	= iDB::getValue("SELECT keywords FROM `#iCMS@__tags` WHERE `id`='$id'");
		        		$sql		="`keywords` = '".($keywords?$keywords.','.iS::escapeStr($_POST['mkeyword']):iS::escapeStr($_POST['mkeyword']))."'";
				        iDB::query("UPDATE `#iCMS@__tags` SET {$sql} WHERE `id`='$id'");
		        	}
		        	iPHP::OK('关键字更改完成!','js:1');
    			}
    		break;
    		case 'tag':
    			if($_POST['pattern']=='replace') {
    				$sql	="`related` = '".iS::escapeStr($_POST['mtag'])."'";
    			}elseif($_POST['pattern']=='addto') {
		        	foreach($idArray AS $id){
		        		$keywords	= iDB::getValue("SELECT related FROM `#iCMS@__tags` WHERE `id`='$id'");
		        		$sql		="`related` = '".($keywords?$keywords.','.iS::escapeStr($_POST['mtag']):iS::escapeStr($_POST['mtag']))."'";
				        iDB::query("UPDATE `#iCMS@__tags` SET {$sql} WHERE `id`='$id'");
		        	}
		        	iPHP::OK('相关标签更改完成!','js:1');
    			}
    		break;
    		default:
				$sql	= iACP::iDT($batch);

		}
		iDB::query("UPDATE `#iCMS@__tags` SET {$sql} WHERE `id` IN ($ids)");
		iPHP::OK('操作成功!','js:1');
	}
}
