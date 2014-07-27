<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: groups.app.php 634 2013-04-03 06:02:53Z coolmoo $
*/
class groupsApp{
	public $group = NULL;
	public $all   = NULL;
    function __construct($type=null) {
    	$this->gid	= (int)$_GET['gid'];
    	$type!==null && $sql=" and `type`='$type'";
		$rs		= iDB::all("SELECT * FROM `#iCMS@__group` where 1=1{$sql} ORDER BY `orderNum` , `gid` ASC",ARRAY_A);
		$_count	= count($rs);
		for ($i=0;$i<$_count;$i++){
			$this->all[$rs[$i]['gid']]      = $rs[$i];
			$this->group[$rs[$i]['type']][] = $rs[$i];
		}
    }
    function do_add(){
        $this->gid && $rs = iDB::row("SELECT * FROM `#iCMS@__group` WHERE `gid`='$this->gid' LIMIT 1;");
        include iACP::view("groups.add");
    }
	function select($type="0",$currentid=NULL){
		if($this->group[$type])foreach($this->group[$type] AS $G){
			$selected=($currentid==$G['gid'])?" selected='selected'":'';
			$option.="<option value='{$G['gid']}'{$selected}>".$G['name']."[GID:{$G['gid']}] </option>";
		}
		return $option;
	}
    function do_iCMS(){
    	$rs		= iDB::all("SELECT * FROM `#iCMS@__group` ORDER BY `type` , `gid` ASC",ARRAY_A);
		$_count	= count($rs);
    	include iACP::view("groups.manage");
    }
    function do_del($gid = null,$dialog=true){
    	$gid===null && $gid=$this->gid;
		$gid OR iPHP::alert('请选择要删除的用户组');
		$gid=="1" && iPHP::alert('不能删除超级管理员组');
		iDB::query("DELETE FROM `#iCMS@__group` WHERE `gid` = '$gid'");
		$dialog && iPHP::success('用户组删除完成','js:parent.$("#tr'.$gid.'").remove();');
    }
    function do_batch(){
        $idArray = (array)$_POST['id'];
    	$idArray OR iPHP::alert("请选择要删除的用户组");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
    	switch($batch){
    		case 'dels':
				iPHP::$break	= false;
	    		foreach($idArray AS $id){
	    			$this->do_del($id,false);
	    		}
	    		iPHP::$break	= true;
				iPHP::success('全部删除完成!','js:1');
    		break;
		}
	}
	function do_save(){
		$gid  = intval($_POST['gid']);
		$type = intval($_POST['type']);
		$name = iS::escapeStr($_POST['name']);
		$name OR iPHP::alert('角色名不能为空');
		$fields = array('name', 'orderNum', 'power', 'cpower', 'type');
		$data   = compact ($fields);
		if($gid){
            iDB::update('group', $data, array('gid'=>$gid));
			$msg = "角色修改完成!";
		}else{
			iDB::insert('group',$data);
			$msg = "角色添加完成!";
		}
		iPHP::success($msg,'url:'.APP_URI);
	}
}
