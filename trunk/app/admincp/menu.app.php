<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: menu.app.php 2090 2013-09-25 00:37:33Z coolmoo $
*/
class menuApp{
    function __construct() {
    	$this->menu	= new iMenu();
    	$this->menu->getArray();
    }
    function doadd(){
    	$id	= $_GET['id'];
        if($id) {
            $rs		= iDB::getRow("SELECT * FROM `#iCMS@__menu` WHERE `id`='$id' LIMIT 1;",ARRAY_A);
            $rootid	= $rs['rootid'];
        }else{
        	$rootid	= $_GET['rootid'];
        }
        include iACP::tpl("menu.add");
    }
    function doaddseparator(){
    	$rootid	= $_GET['rootid'];
    	$class	= $rootid?'divider':'divider-vertical';
    	iDB::query("INSERT INTO `#iCMS@__menu` (`rootid`,`app`,`class`) VALUES($rootid,'separator','$class');");
    	$this->menu->cache();
    	iPHP::OK('添加完成');
    }
    function doupdateorder(){
    	foreach((array)$_POST['ordernum'] as $orderNum=>$id){
            iDB::query("UPDATE `#iCMS@__menu` SET `orderNum` = '".intval($orderNum)."' WHERE `id` ='".intval($id)."' LIMIT 1");
    	}
		$this->menu->cache();
    }
    function doiCMS(){
    	iACP::$app_method="domanage";
    	$_GET['tab'] OR $_GET['tab']="tree";
    	$this->domanage();
    }
    function domanage($doType=null) {
        include iACP::tpl("menu.manage");
    }
    function doajaxtree(){
		$hasChildren=$_GET['hasChildren']?true:false;
	 	echo $this->tree($_GET["root"],$hasChildren);
    }
    function tree($id =0,$hasChildren=false){
    	$id=='source' && $id=0;
        foreach((array)$this->menu->MArray[$id] AS $root=>$M) {
        	$a			= array();
        	$a['id']	= $M['id'];
        	$a['text']	= $this->li($M);
            if($this->menu->subA[$M['id']]){
            	if($hasChildren){
	            	$a['hasChildren']	= false;
	            	$a['expanded']		= true;
	            	$a['children']		= $this->tree($M['id'],$hasChildren);
            	}else{
	            	$a['hasChildren']	= true;
            	}
            }
            $tr[]=$a;
        }
        if($hasChildren && $id!='source'){ return $tr; }
        return $tr?json_encode($tr):'[]';
    }
    function li($M) {
    	if($M['app']=='separator'){
    		return '<span class="operation"><a href="'.APP_FURI.'&do=del&id='.$M['id'].'" class="btn btn-danger btn-small" onClick="return confirm(\'确定要删除此菜单?\');" target="iPHP_FRAME"><i class="fa fa-trash-o"></i> 删除</a></span><div class="separator"><span class="ordernum" style="display:none;"><input type="text" data-id="'.$M['id'].'" name="orderNum['.$M['id'].']" value="'.$M['orderNum'].'"/></span> </div>';
    	}
        $M['rootid']==0 && $bold =' style="font-weight:bold"';
        $tr='<div class="row-fluid"><span class="ordernum" style="display:none;"><input type="text" data-id="'.$M['id'].'" name="orderNum['.$M['id'].']" value="'.$M['orderNum'].'" style="width:32px;"/></span>
        <span class="name"'.$bold.'>'.$M['name'].'</span><span class="operation">';
        $tr.='<a href="'.APP_URI.'&do=add&rootid='.$M['id'].'" class="btn btn-info btn-small"><i class="fa fa-plus-square"></i> 子菜单</a>
        <a href="'.APP_FURI.'&do=addseparator&rootid='.$M['id'].'" class="btn btn-success btn-small" target="iPHP_FRAME"><i class="fa fa-minus-square"></i> 分隔符</a> <a href="'.APP_URI.'&do=add&id='.$M['id'].'" title="编辑菜单设置"  class="btn btn-primary btn-small"><i class="fa fa-edit"></i> 编辑</a> <a href="'.APP_FURI.'&do=del&id='.$M['id'].'" class="btn btn-danger btn-small" onClick="return confirm(\'确定要删除此菜单?\');" target="iPHP_FRAME"><i class="fa fa-trash-o"></i> 删除</a></span></div>';
        return $tr;
    }
    function dosave(){
        $id          = $_POST['id'];
        $rootid      = $_POST['rootid'];
        $app         = $_POST['app'];
        $name        = $_POST['name'];
        $title       = $_POST['title'];
        $href        = $_POST['href'];
        $icon        = $_POST['icon'];
        $target      = $_POST['target'];
        $data_toggle = $_POST['data-toggle'];
        $orderNum    = $_POST['orderNum'];
        $class       = '';
        $a_class     = '';
        $caret       = '';
        $data_meta   = $_POST['data-meta'];
        $data_target = '';
    	
    	if($data_toggle=="dropdown"){
    		$class		= 'dropdown';
    		$a_class	= 'dropdown-toggle';
    		$caret		= '<b class="caret"></b>';
    	}else if($data_toggle=="modal"){
    		$data_meta	OR	$data_meta	= '{"width":"800px","height":"600px"}';
    		$data_target	= '#iCMS_DIALOG';
    	}
		if($id){
    		iDB::query("UPDATE `#iCMS@__menu` SET `rootid`='$rootid', `orderNum`='$orderNum', `app`='$app', `name`='$name', `title`='$title', `href`='$href', `icon`='$icon', `class`='$class', `a_class`='$a_class', `target`='$target', `caret`='$caret', `data-toggle`='$data_toggle', `data-meta`='$data_meta', `data-target`='$data_target' WHERE `id`='$id';");
    		$msg	= "编辑完成!";
    	}else{
	    	iDB::query("INSERT INTO `#iCMS@__menu` (`rootid`, `orderNum`, `app`, `name`, `title`, `href`, `icon`, `class`, `a_class`, `target`, `caret`, `data-toggle`, `data-meta`, `data-target`) VALUES ('$rootid', '$orderNum', '$app', '$name', '$title', '$href', '$icon', '$class', '$a_class', '$target', '$caret', '$data_toggle', '$data_meta', '$data_target');");
			$msg	= "添加完成!";
    	}
		$this->menu->cache();
		iPHP::OK($msg,'url:' . APP_URI . '&do=manage');
    }
    function dodel(){
        $id		= (int)$_GET['id'];
        if(empty($this->menu->MArray[$id])) {
            iDB::query("DELETE FROM `#iCMS@__menu` WHERE `id` = '$id'");
            $this->menu->cache();
            $msg	= '删除成功!';
        }else {
        	$msg	= '请先删除本菜单下的子菜单!';
        }
		iPHP::dialog($msg,'js:parent.$("#'.$id.'").remove();');
    }
    function select($currentid="0",$id="0",$level = 1) {
        foreach((array)$this->menu->MArray[$id] AS $root=>$M) {
			$t=$level=='1'?"":"├ ";
			$selected=($currentid==$M['id'])?"selected='selected'":"";
			if($M['app']=='separator'){
				$M['name']	= "─────────────";
				$M['id']	= "-1";
			}
			$text	= str_repeat("│　", $level-1).$t.$M['name'];
			$option.="<option value='{$M['id']}' $selected>{$text}</option>";
			$this->menu->subA[$M['id']] && $option.=$this->select($currentid,$M['id'],$level+1);
        }
        return $option;
    }
}
