<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: prop.app.php 2369 2014-03-13 16:16:29Z coolmoo $
*/
class propApp{
    function __construct() {
        $this->categoryApp = iACP::app('category','all');
        $this->category    = $this->categoryApp->category;
        $this->pid         = (int)$_GET['pid'];
    }
    function do_add(){
        $this->pid && $rs	= iDB::row("SELECT * FROM `#iCMS@__prop` WHERE `pid`='$this->pid' LIMIT 1;",ARRAY_A);
        if($_GET['act']=="copy"){
            $this->pid = 0;
            $rs['val'] = '';
        }
        include iACP::view("prop.add");
    }
    function do_save(){
        $pid      = (int)$_POST['pid'];
        $cid      = (int)$_POST['cid'];
        $ordernum = (int)$_POST['ordernum'];
        $field    = iS::escapeStr($_POST['field']);
        $name     = iS::escapeStr($_POST['name']);
        $type     = iS::escapeStr($_POST['type']);
        $val      = iS::escapeStr($_POST['val']);

		($field=='pid'&& !is_numeric($val)) && iPHP::alert('pid字段的值能用数字');
        $field OR iPHP::alert('属性字段不能为空!');
        $name OR iPHP::alert('属性名称不能为空!');
        $type OR iPHP::alert('类型不能为空!');

		$field=='pid' && $val=(int)$val;

        $fields = array('rootid','cid','field','type','ordernum', 'name', 'val');
        $data   = compact ($fields);

		if($pid){
            iDB::update('prop', $data, array('pid'=>$pid));
			$msg="属性更新完成!";
		}else{
	        iDB::value("SELECT `pid` FROM `#iCMS@__prop` where `type` ='$type' AND `val` ='$val' AND `field` ='$field' AND `cid` ='$cid'") && iPHP::alert('该类型属性值已经存在!请另选一个');
            iDB::insert('prop',$data);
	        $msg="新属性添加完成!";
		}
		$this->cache();
        iPHP::success($msg,'url:'.APP_URI);
    }
    function do_update(){
    	foreach((array)$_POST['pid'] as $tk=>$pid){
            iDB::query("update `#iCMS@__prop` set `type` = '".$_POST['type'][$tk]."', `name` = '".$_POST['name'][$tk]."', `value` = '".$_POST['value'][$tk]."' where `pid` = '$pid';");
    	}
    	$this->cache();
    	iPHP::alert('更新完成');
    }
    function do_del($id = null,$dialog=true){
    	$id===null && $id=$this->pid;
    	$id OR iPHP::alert('请选择要删除的属性!');
		iDB::query("DELETE FROM `#iCMS@__prop` WHERE `pid` = '$id';");
    	$this->cache();
    	$dialog && iPHP::success("已经删除!",'url:'.APP_URI);
    }
    function do_batch(){
        $idArray = (array)$_POST['id'];
        $idArray OR iPHP::alert("请选择要操作的属性");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
    	switch($batch){
    		case 'dels':
				iPHP::$break	= false;
	    		foreach($idArray AS $id){
	    			$this->do_del($id,false);
	    		}
	    		iPHP::$break	= true;
				iPHP::success('属性全部删除完成!','js:1');
    		break;
    		case 'refresh':
    			$this->cache();
    			iPHP::success('属性缓存全部更新完成!','js:1');
    		break;
		}
	}

    function do_iCMS(){
        $sql			= " where 1=1";
//        $cid			= (int)$_GET['cid'];
//
//        if($cid) {
//	        $cids	= $_GET['sub']?iCMS::get_category_ids($cid,true):$cid;
//	        $cids OR $cids	= $vars['cid'];
//	        $sql.= iPHP::where($cids,'cid');
//        }

        $_GET['field']&& $sql.=" AND `field`='".$_GET['field']."'";
        $_GET['field']&& $uri.='&field='.$_GET['field'];

        $_GET['type'] && $sql.=" AND `type`='".$_GET['type']."'";
        $_GET['type'] && $uri.='&type='.$_GET['type'];

        $_GET['cid']  && $sql.=" AND `cid`='".$_GET['cid']."'";
        $_GET['cid']  && $uri.='&cid='.$_GET['cid'];

        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total		= iPHP::total(false,"SELECT count(*) FROM `#iCMS@__prop` {$sql}","G");
        iPHP::pagenav($total,$maxperpage,"个属性");
        $rs     = iDB::all("SELECT * FROM `#iCMS@__prop` {$sql} order by pid DESC LIMIT ".iPHP::$offset." , {$maxperpage}");
        $_count = count($rs);
    	include iACP::view("prop.manage");
    }
    function cache(){
    	$rs	= iDB::all("SELECT * FROM `#iCMS@__prop`",ARRAY_A);
    	foreach((array)$rs AS $row) {
            $pkey = $row['cid'].$row['type'].$row['field'];
            $cidA[$row['cid']][]   = $row;
            $typeA[$row['type']][] = $row;
    		$ctfA['c'.$row['cid'].'.'.$row['type'].'.'.$row['field']][]=$row;
    		$tfA[$row['type'].'.'.$row['field']][$row['pid']]=$row;
    	}
    	foreach($ctfA AS $k=>$a){
    		iCache::set('iCMS/prop/'.$k,$a,0);
    	}
    	foreach($tfA AS $k=>$a){
    		iCache::set('iCMS/prop/'.$k,$a,0);
    	}
    	iCache::set('iCMS/prop/cid.cache',$cidA,0);
    	iCache::set('iCMS/prop/type.cache',$typeA,0);
    }
}
