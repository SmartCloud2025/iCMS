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
    	iACP::$menu->get_array();
    }
    function do_add(){
    	$id	= $_GET['id'];
        if($id) {
            $rs		= iDB::row("SELECT * FROM `#iCMS@__menu` WHERE `id`='$id' LIMIT 1;",ARRAY_A);
            $rootid	= $rs['rootid'];
        }else{
        	$rootid	= $_GET['rootid'];
        }
        include iACP::view("menu.add");
    }
    function do_addseparator(){
    	$rootid	= $_GET['rootid'];
    	$class	= $rootid?'divider':'divider-vertical';
    	iDB::query("INSERT INTO `#iCMS@__menu` (`rootid`,`app`,`class`) VALUES($rootid,'separator','$class');");
    	iACP::$menu->cache();
    	iPHP::success('添加完成');
    }
    function do_updateorder(){
    	foreach((array)$_POST['ordernum'] as $ordernum=>$id){
            iDB::query("UPDATE `#iCMS@__menu` SET `ordernum` = '".intval($ordernum)."' WHERE `id` ='".intval($id)."' LIMIT 1");
    	}
		iACP::$menu->cache();
    }
    function do_iCMS(){
    	iACP::$app_method="domanage";
    	$_GET['tab'] OR $_GET['tab']="tree";
    	$this->do_manage();
    }
    function do_manage($doType=null) {
        include iACP::view("menu.manage");
    }
    function power_tree($id=0){
        $li   = '';
        foreach((array)iACP::$menu->root_array[$id] AS $root=>$M) {
            $li.= '<li>';
            $li.= $this->power_holder($M);
            if(iACP::$menu->child_array[$M['id']]){
                $li.= '<ul>';
                $li.= $this->power_tree($M['id']);
                $li.= '</ul>';
            }
            $li.= '</li>';
        }
        return $li;
    }
    function power_holder($M) {
        $name ='<span class="add-on">'.$M['name'].'</span>';
        if($M['app']=='separator'){
            $name ='<span class="add-on tip" title="分隔符权限,仅为UI美观">───分隔符───</span>';
        }
        return '<div class="input-prepend input-append li2">
        <span class="add-on"><input type="checkbox" name="power[]" value="'.$M['id'].'"></span>
        '.$name.'
        </div>';
    }

    function do_ajaxtree(){
		$expanded = $_GET['expanded']?true:false;
	 	echo $this->tree($_GET["root"],$expanded);
    }

    function tree($id =0,$expanded=false,$func='li'){
    	$id=='source' && $id=0;
        foreach((array)iACP::$menu->root_array[$id] AS $root=>$M) {
        	$a			= array();
        	$a['id']	= $M['id'];
        	$a['text']	= $this->$func($M);
            if(iACP::$menu->child_array[$M['id']]){
            	if($expanded){
                    $a['hasChildren'] = false;
                    $a['expanded']    = true;
                    $a['children']    = $this->tree($M['id'],$expanded,$func);
            	}else{
                    $a['hasChildren'] = true;
            	}
            }
            $tr[]=$a;
        }
        if($expanded && $id!='source'){ return $tr; }
        return $tr?json_encode($tr):'[]';
    }
    function li($M) {
    	if($M['app']=='separator'){
    		return '<span class="operation"><a href="'.APP_FURI.'&do=del&id='.$M['id'].'" class="btn btn-danger btn-small" onClick="return confirm(\'确定要删除此菜单?\');" target="iPHP_FRAME"><i class="fa fa-trash-o"></i> 删除</a></span><div class="separator"><span class="ordernum" style="display:none;"><input type="text" data-id="'.$M['id'].'" name="ordernum['.$M['id'].']" value="'.$M['ordernum'].'"/></span> </div>';
    	}
        $M['rootid']==0 && $bold =' style="font-weight:bold"';
        $tr='<div class="row-fluid">
        <span class="ordernum" style="display:none;"><input type="text" data-id="'.$M['id'].'" name="ordernum['.$M['id'].']" value="'.$M['ordernum'].'" style="width:32px;"/></span>
        <span class="name"'.$bold.'>'.$M['name'].'</span><span class="operation">';
        $tr.='
        <a href="'.APP_URI.'&do=copy&id='.$M['id'].'" title="复制本菜单设置"  class="btn btn-small" target="iPHP_FRAME"><i class="fa fa-copy"></i> 复制</a>
        <a href="'.APP_URI.'&do=add&rootid='.$M['id'].'" class="btn btn-info btn-small"><i class="fa fa-plus-square"></i> 子菜单</a>
        <a href="'.APP_FURI.'&do=addseparator&rootid='.$M['id'].'" class="btn btn-success btn-small" target="iPHP_FRAME"><i class="fa fa-minus-square"></i> 分隔符</a>
        <a href="'.APP_URI.'&do=add&id='.$M['id'].'" title="编辑菜单设置"  class="btn btn-primary btn-small"><i class="fa fa-edit"></i> 编辑</a>
        <a href="'.APP_FURI.'&do=del&id='.$M['id'].'" class="btn btn-danger btn-small" onClick="return confirm(\'确定要删除此菜单?\');" target="iPHP_FRAME"><i class="fa fa-trash-o"></i> 删除</a></span></div>';
        return $tr;
    }
    function do_copy() {
        $id = $_GET['id'];
        $field = '`rootid`, `ordernum`, `app`, `name`, `title`, `href`, `icon`, `class`, `a_class`, `target`, `caret`, `data-toggle`, `data-meta`, `data-target`';
        iDB::query("insert into `#iCMS@__menu` ({$field}) select {$field} from `#iCMS@__menu` where id = '$id'");
        $nid = iDB::$insert_id;
        iPHP::success('复制完成,编辑此菜单', 'url:' . APP_URI . '&do=add&id=' . $nid);
    }
    function do_save(){
        $id          = $_POST['id'];
        $rootid      = $_POST['rootid'];
        $app         = $_POST['app'];
        $name        = $_POST['name'];
        $title       = $_POST['title'];
        $href        = $_POST['href'];
        $a_class     = $_POST['a_class'];
        $icon        = $_POST['icon'];
        $target      = $_POST['target'];
        $data_toggle = $_POST['data-toggle'];
        $ordernum    = $_POST['ordernum'];
        $class       = '';
        $caret       = '';
        $data_meta   = $_POST['data-meta'];
        $data_target = '';

    	if($data_toggle=="dropdown"){
    		$class		= 'dropdown';
    		$a_class	= 'dropdown-toggle';
    		$caret		= '<b class="caret"></b>';
    	}else if($data_toggle=="modal"){
    		$data_meta	OR	$data_meta	= '{"width":"800px","height":"600px"}';
    		$data_target = '#iCMS-MODAL';
    	}
        $fields = array('rootid', 'ordernum', 'app', 'name', 'title', 'href', 'icon', 'class', 'a_class', 'target', 'caret', 'data-toggle', 'data-meta', 'data-target');
        $data   = compact ($fields);

		if($id){
            iDB::update('menu', $data, array('id'=>$id));
    		$msg = "编辑完成!";
    	}else{
            iDB::insert('menu',$data);
			$msg = "添加完成!";
    	}
		iACP::$menu->cache();
		iPHP::success($msg,'url:' . APP_URI . '&do=manage');
    }
    function do_del(){
        $id		= (int)$_GET['id'];
        if(empty(iACP::$menu->root_array[$id])) {
            iDB::query("DELETE FROM `#iCMS@__menu` WHERE `id` = '$id'");
            iACP::$menu->cache();
            $msg	= '删除成功!';
        }else {
        	$msg	= '请先删除本菜单下的子菜单!';
        }
		iPHP::dialog($msg,'js:parent.$("#'.$id.'").remove();');
    }
    function select($currentid="0",$id="0",$level = 1) {
        foreach((array)iACP::$menu->root_array[$id] AS $root=>$M) {
			$t=$level=='1'?"":"├ ";
			$selected=($currentid==$M['id'])?"selected":"";
			if($M['app']=='separator'){
				$M['name']	= "─────────────";
				$M['id']	= "-1";
			}
			$text	= str_repeat("│　", $level-1).$t.$M['name'];
			$option.="<option value='{$M['id']}' $selected>{$text}</option>";
			iACP::$menu->child_array[$M['id']] && $option.=$this->select($currentid,$M['id'],$level+1);
        }
        return $option;
    }
}
