<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: category.app.php 2406 2014-04-28 02:24:46Z coolmoo $
*/
defined('iPHP') OR exit('What are you doing?');
iPHP::app('category.class','include');
class categoryApp extends category{
    function __construct($appid = 1) {
        $this->name_text = "栏目";
        $this->appid     = iCMS_APP_ARTICLE;
        $this->cid       = (int)$_GET['cid'];
        $appid          && $this->appid = $appid;
        $_GET['appid']  && $this->appid = (int)$_GET['appid'];
        parent::__construct($this->appid);
    }
    function do_add(){
        if($this->cid) {
            iACP::CP($this->cid,'e','page');
            $rs		= iDB::row("SELECT * FROM `#iCMS@__category` WHERE `cid`='$this->cid' LIMIT 1;",ARRAY_A);
            $rootid	= $rs['rootid'];
            $rs['metadata'] && $rs['metadata']=unserialize($rs['metadata']);
            $rs['contentprop'] && $rs['contentprop']=unserialize($rs['contentprop']);
            $rs['body'] = iCache::get('iCMS/category/'.$this->cid.'.body');
        }else {
            $rootid = (int)$_GET['rootid'];
            $rootid && iACP::CP($rootid,'a','page');
        }
        if(empty($rs)) {
            $rs = array(
                'pid'          => '0',
                'status'       => '1',
                'isexamine'    => '1',
                'issend'       => '1',
                'ordernum'     => '0',
                'mode'         => '0',
                'htmlext'      => '.html',
                'categoryURI'  => 'category',
                'categoryRule' => '{CDIR}/index{EXT}',
                'contentRule'  => '{CDIR}/{YYYY}/{MM}{DD}/{ID}{EXT}',
                'indexTPL'     => '{iTPL}/category.index.htm',
                'listTPL'      => '{iTPL}/category.list.htm',
                'contentTPL'   => '{iTPL}/article.htm',
                'metadata'     => '',
                'contentprop'  => '',
            );
	        if($rootid){
                $rootRs = iDB::row("SELECT * FROM `#iCMS@__category` WHERE `cid`='".$rootid."' LIMIT 1;",ARRAY_A);
                $rs['htmlext']      = $rootRs['htmlext'];
                $rs['categoryRule'] = $rootRs['categoryRule'];
                $rs['contentRule']  = $rootRs['contentRule'];
                $rs['indexTPL']     = $rootRs['indexTPL'];
                $rs['listTPL']      = $rootRs['listTPL'];
                $rs['contentTPL']   = $rootRs['contentTPL'];
	        }
        }
        include iACP::view("category.add");
    }
    function do_save(){
        $appid        = $this->appid;
        $cid          = (int)$_POST['cid'];
        $rootid       = (int)$_POST['rootid'];
        $status       = (int)$_POST['status'];
        $isucshow     = (int)$_POST['isucshow'];
        $issend       = (int)$_POST['issend'];
        $isexamine    = (int)$_POST['isexamine'];
        $ordernum     = (int)$_POST['ordernum'];
        $mode         = (int)$_POST['mode'];
        $pid          = implode(',', (array)$_POST['pid']);
        $_pid         = iS::escapeStr($_POST['_pid']);
        $_rootid_hash = iS::escapeStr($_POST['_rootid_hash']);
        $name         = iS::escapeStr($_POST['name']);
        $subname      = iS::escapeStr($_POST['subname']);
        $domain       = iS::escapeStr($_POST['domain']);
        $htmlext      = iS::escapeStr($_POST['htmlext']);
        $url          = iS::escapeStr($_POST['url']);
        $password     = iS::escapeStr($_POST['password']);
        $pic          = iS::escapeStr($_POST['pic']);
        $mpic         = iS::escapeStr($_POST['mpic']);
        $spic         = iS::escapeStr($_POST['spic']);
        $dir          = iS::escapeStr($_POST['dir']);
        $title        = iS::escapeStr($_POST['title']);
        $keywords     = iS::escapeStr($_POST['keywords']);
        $description  = iS::escapeStr($_POST['description']);
        $categoryURI  = iS::escapeStr($_POST['categoryURI']);
        $categoryRule = iS::escapeStr($_POST['categoryRule']);
        $contentRule  = iS::escapeStr($_POST['contentRule']);
        $urlRule      = iS::escapeStr($_POST['urlRule']);
        $indexTPL     = iS::escapeStr($_POST['indexTPL']);
        $listTPL      = iS::escapeStr($_POST['listTPL']);
        $contentTPL   = iS::escapeStr($_POST['contentTPL']);
        $metadata     = iS::escapeStr($_POST['metadata']);
        $contentprop  = iS::escapeStr($_POST['contentprop']);
        $body         = $_POST['body'];
        $hasbody      = $body?1:0;

        if($_rootid_hash){
            $_rootid = authcode($_rootid_hash);
            if($rootid!=$_rootid){
                iPHP::alert('非法数据提交!');
            }else{
                iACP::CP($_rootid,'a','alert');
                exit;
            }
        }
        ($cid && $cid==$rootid) && iPHP::alert('不能以自身做为上级'.$this->name_text);
        empty($name) && iPHP::alert($this->name_text.'名称不能为空!');
		if($metadata){
	        $md	= array();
			foreach($metadata['key'] AS $_mk=>$_mval){
				!preg_match("/[a-zA-Z0-9_\-]/",$_mval) && iPHP::alert($this->name_text.'附加属性名称只能由英文字母、数字或_-组成(不支持中文)');
				$md[$_mval] = $metadata['value'][$_mk];
			}
			$metadata = addslashes(serialize($md));
		}
		if($contentprop){
	        $ca = array();
			foreach($contentprop['key'] AS $_cak=>$_caval){
				$_caval OR $_caval = strtolower(pinyin($contentprop['name'][$_cak]));
				!preg_match("/[a-zA-Z0-9_\-]/",$_caval) && iPHP::alert('内容附加属性字段只能由英文字母、数字或_-组成(不支持中文)');
				$ca[$_caval]=$contentprop['name'][$_cak];
			}
			$contentprop = addslashes(serialize($ca));
		}

        if(empty($dir) && empty($url)) {
            $dir = strtolower(pinyin($name));
        }

        if($mode=="2"){
        	if(strpos($categoryRule,'{CDIR}')=== FALSE && strpos($categoryRule,'{CID}')=== FALSE){
        		iPHP::alert('伪静态模式下版块URL规则<hr />必需要有<br />{CDIR}版块目录<br />或者<br />{CID}版块ID');
        	}
        	if(strpos($contentRule,'{ID}')=== FALSE && strpos($contentRule,'{0xID}')=== FALSE){
        		iPHP::alert('伪静态模式下内容URL规则<hr />必需要有<br />{ID}文章ID <br />或者<br />{0xID}文章ID补零<br />');
        	}
        }
        iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
        map::init('prop',iCMS_APP_CATEGORY);

        $fields = array('rootid','appid','ordernum','name','subname','password','title','keywords','description','dir','mode','domain','url','pic','mpic','spic','htmlext','categoryURI','categoryRule','contentRule','urlRule','indexTPL','listTPL','contentTPL','metadata','contentprop','hasbody','pid','isexamine','issend','isucshow','status');
        $data   = compact ($fields);

        if(empty($cid)) {
            iACP::CP($rootid,'a','alert');
        	$nameArray	= explode("\n",$name);
        	foreach($nameArray AS $nkey=>$_name){
        		$_name	= trim($_name);
                if(empty($_name)) continue;

		        empty($url) && $_dir = strtolower(pinyin($_name));
                $this->check_dir($_dir,$appid,$url);
                $data['name']       = $_name;
                $data['dir']        = $_dir;
                $data['userid']     = iMember::$userid;
                $data['creator']    = iMember::$nickname;
                $data['createtime'] = time();
                $data['count']      = '0';
                $data['comments']   = '0';

                $cid = iDB::insert('category',$data);
                $pid && map::add($pid,$cid);
	            $this->cache(false,$this->appid);
	            $this->cahce_one($cid);
            }
            $msg=$this->name_text."添加完成!";
        }else {
            iACP::CP($cid,'e','alert');
            $this->check_dir($dir,$appid,$url,$cid);
            iDB::update('category', $data, array('cid'=>$cid));
            map::diff($pid,$_pid,$cid);
            $this->cahce_one($cid);
            $msg=$this->name_text."编辑完成!";
        }
        $hasbody && iCache::set('iCMS/category/'.$cid.'.body',$body,0);
        iPHP::success($msg,'url:'.APP_URI);
    }

    function do_update(){
    	foreach((array)$_POST['name'] as $cid=>$name){
    		$name	= iS::escapeStr($name);
			iDB::query("UPDATE `#iCMS@__category` SET `name` = '$name',`ordernum` = '".(int)$_POST['ordernum'][$cid]."' WHERE `cid` ='".(int)$cid."' LIMIT 1");
	    	$this->cahce_one($cid);
    	}
    	iPHP::success('更新完成');
    }
    function do_batch(){
        $_POST['id'] OR iPHP::alert("请选择要操作的".$this->name_text);
        $id_array = (array)$_POST['id'];
        $ids      = implode(',',$id_array);
        $batch    = $_POST['batch'];
        switch($batch){
            case 'move':
                $tocid = (int)$_POST['tocid'];
                $key   = array_search($tocid,$id_array);
                if($tocid) unset($id_array[$key]);//清除同ID
                foreach($id_array as $k=>$cid){
                    iDB::query("UPDATE `#iCMS@__category` SET `rootid` ='$tocid' WHERE `cid` ='$cid'");
                }
                $this->cache(true,$this->appid);
                iPHP::success('更新完成!','js:1');
            break;
            case 'merge':
                $tocid = (int)$_POST['tocid'];
                $key   = array_search($tocid,$id_array);
                unset($id_array[$key]);//清除同ID
                foreach($id_array as $k=>$cid){
                    $this->mergecontent($tocid,$cid);
                    $this->do_del($cid,false);
                }
                $this->update_count($tocid);
                $this->cache(true,$this->appid);
                iPHP::success('更新完成!','js:1');
            break;
            case 'name':
                foreach($id_array as $k=>$cid){
                    $name   = iS::escapeStr($_POST['name'][$cid]);
                    iDB::query("UPDATE `#iCMS@__category` SET `name` = '$name' WHERE `cid` ='".(int)$cid."' LIMIT 1");
                    $this->cahce_one($cid);
                }
                iPHP::success('更新完成!','js:1');
            break;
            case 'status':
                $val = (int)$_POST['status'];
                $sql ="`status` = '$val'";
            break;
            case 'mode':
                $val = (int)$_POST['mode'];
                $sql ="`mode` = '$val'";
            break;
            case 'categoryRule':
                $val = iS::escapeStr($_POST['categoryRule']);;
                $sql ="`categoryRule` = '$val'";
            break;
            case 'contentRule':
                $val = iS::escapeStr($_POST['contentRule']);;
                $sql ="`contentRule` = '$val'";
            break;
            case 'urlRule':
                $val = iS::escapeStr($_POST['urlRule']);;
                $sql ="`urlRule` = '$val'";
            break;
            case 'indexTPL':
                $val = iS::escapeStr($_POST['indexTPL']);;
                $sql ="`indexTPL` = '$val'";
            break;
            case 'listTPL':
                $val = iS::escapeStr($_POST['listTPL']);;
                $sql ="`listTPL` = '$val'";
            break;
            case 'contentTPL':
                $val = iS::escapeStr($_POST['contentTPL']);;
                $sql ="`contentTPL` = '$val'";
            break;
            case 'recount':
                foreach($id_array as $k=>$cid){
                    $this->update_count($cid);
                }
                iPHP::success('操作成功!','js:1');
            break;
            case 'dels':
                iPHP::$break    = false;
                foreach($id_array AS $cid){
                    iACP::CP($cid,'d','alert');
                    $this->do_del($cid,false);
                }
                iPHP::$break    = true;
                iPHP::success('全部删除完成!','js:1');
            break;
       }
        iDB::query("UPDATE `#iCMS@__category` SET {$sql} WHERE `cid` IN ($ids)");
        $this->cache(true,$this->appid);
        iPHP::success('操作成功!','js:1');
    }
    function do_updateorder(){
    	foreach((array)$_POST['ordernum'] as $ordernum=>$cid){
            iDB::query("UPDATE `#iCMS@__category` SET `ordernum` = '".intval($ordernum)."' WHERE `cid` ='".intval($cid)."' LIMIT 1");
	    	$this->cahce_one($cid);
    	}
    }
    function do_iCMS(){
        $tabs = iPHP::get_cookie(iACP::$app_name.'_tabs');
        $tabs=="list"?$this->do_list():$this->do_tree();
    }
    function do_tree() {
        iACP::$app_do = 'tree';
        include iACP::view("category.manage");
    }
    function do_list(){
        iACP::$app_do = 'list';
        $sql  = " where `appid`='{$this->appid}'";
        $cids = iACP::CP('__CID__');
        $sql.= iPHP::where($cids,'cid');

        if($_GET['keywords']) {
            if($_GET['st']=="name") {
                $sql.=" AND `name` REGEXP '{$_GET['keywords']}'";
            }else if($_GET['st']=="cid") {
                $sql.=" AND `cid` REGEXP '{$_GET['keywords']}'";
            }else if($_GET['st']=="tkd") {
                $sql.=" AND CONCAT(name,title,keywords,description) REGEXP '{$_GET['keywords']}'";
            }
        }
        $orderby    = $_GET['orderby']?$_GET['orderby']:"cid DESC";
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total      = iPHP::total(false,"SELECT count(*) FROM `#iCMS@__category` {$sql}","G");
        iPHP::pagenav($total,$maxperpage);
        $rs     = iDB::all("SELECT * FROM `#iCMS@__category` {$sql} order by {$orderby} LIMIT ".iPHP::$offset." , {$maxperpage}");
        $_count = count($rs);
        include iACP::view("category.manage");
    }
    function do_del($cid = null,$dialog=true){
        $cid===null && $cid=(int)$_GET['cid'];
        iACP::CP($cid,'d','alert');
        $msg    = '请选择要删除的'.$this->name_text.'!';

        if(empty($this->_array[$cid])) {
            $this->delcontent($cid);
            iDB::query("DELETE FROM `#iCMS@__category` WHERE `cid` = '$cid'");
            iDB::query("DELETE FROM `#iCMS@__category_map` WHERE `node` = '$cid' AND `appid` = '".$this->appid."';");
            iDB::query("DELETE FROM `#iCMS@__prop_map` WHERE `iid` = '$cid' AND `appid` = '".iCMS_APP_CATEGORY."' ;");
            $dialog && $this->cache(true,$this->appid);
            $msg = '删除成功!';
        }else {
            $msg = '请先删除本'.$this->name_text.'下的子'.$this->name_text.'!';
        }
        $dialog && iPHP::success($msg,'js:parent.$("#'.$cid.'").remove();');
    }
    function do_ajaxtree(){
		$expanded=$_GET['expanded']?true:false;
	 	echo $this->tree($_GET["root"],$expanded);
    }
    function do_cache(){
        $this->cache(true,$this->appid);
        iPHP::success('更新完成');
    }
    function search_sql($cid,$field='cid'){
        if($cid) {
            $cids  = $cid;
            if($_GET['sub']){
                $cids  = $this->get_ids($cid,true);
                array_push ($cids,$cid);
            }
        }
        $cids && $sql= iPHP::where($cids,$field);
        return $sql;
    }
    function power_tree($cid=0){
        $li   = '';
        foreach((array)$this->_array[$cid] AS $root=>$C) {
            $li.= '<li>';
            $li.= $this->power_holder($C);
            if($this->_array[$C['cid']]){
                $li.= '<ul>';
                $li.= $this->power_tree($C['cid']);
                $li.= '</ul>';
            }
            $li.= '</li>';
        }
        return $li;
    }
    function power_holder($C) {
        $app_array = array(
            iCMS_APP_ARTICLE =>'<i class="fa fa-file-text"></i>',
            iCMS_APP_TAG     =>'<i class="fa fa-tags"></i>',
            iCMS_APP_PUSH    =>'<i class="fa fa-thumb-tack"></i>',
        );
        $div = '
        <div class="input-prepend input-append li2">
            <span class="add-on">'.$app_array[$C['appid']].'</span>
            <span class="add-on">'.$C['name'].'</span>
            <span class="add-on"><input type="checkbox" name="cpower[]" value="'.$C['cid'].'"> 查询</span>
            <span class="add-on tip" title="添加子'.$this->name_text.'的权限"><input type="checkbox" name="cpower[]" value="'.$C['cid'].':a" /> 添加</span>
            <span class="add-on"><input type="checkbox" name="cpower[]" value="'.$C['cid'].':e" /> 编辑</span>
            <span class="add-on"><input type="checkbox" name="cpower[]" value="'.$C['cid'].':d" /> 删除</span>
        </div>';
        $C['appid']==='1' && $div.= ' <div class="input-prepend input-append li2"><span class="add-on">内容权限</span>
            <span class="add-on"><input type="checkbox" class="checkbox" name="cpower[]" value="'.$C['cid'].':cs" /> 查询</span>
            <span class="add-on"><input type="checkbox" name="cpower[]" value="'.$C['cid'].':ca" /> 添加</span>
            <span class="add-on"><input type="checkbox" name="cpower[]" value="'.$C['cid'].':ce" /> 编辑</span>
            <span class="add-on"><input type="checkbox" name="cpower[]" value="'.$C['cid'].':cd" /> 删除</span>
        </div>';
        return $div;
    }
    function tree($cid = 0,$expanded=false,$ret=false){
    	$cid=='source' && $cid=0;
        $html = array();
        foreach((array)$this->_array[$cid] AS $root=>$C) {
            if(!iACP::CP($C['cid'])){
                if($this->_array[$C['cid']]){
                    $a    = $this->tree($C['cid'],true,true);
                    $html = array_merge($html,$a);
                }
            }else{
                $a = array('id'=>$C['cid'],'text'=>$this->li($C));
                if($this->_array[$C['cid']]){
                    if($expanded){
                        $a['hasChildren'] = false;
                        $a['expanded']    = true;
                        $a['children']    = $this->tree($C['cid'],$expanded,$ret);
                    }else{
                        $a['hasChildren'] = true;
                    }
                }
                $a && $html[] = $a;
            }
        }
        if($ret||($expanded && $cid!='source')){
            return $html;
        }

        //var_dump($html);
        return $html?json_encode($html):'[]';
    }

    function li($C) {
        $html='<div class="row-fluid status'.$C['status'].'"><span class="ordernum"><input'.$readonly.' type="text" cid="'.$C['cid'].'" name="ordernum['.$C['cid'].']" value="'.$C['ordernum'].'" style="width:32px;"/></span>';
        $html.='<span class="name">';
        $html.='<input'.$readonly.($C['rootid']==0?' style="font-weight:bold"':'').' type="text" name="name['.$C['cid'].']" value="'.$C['name'].'"/> ';
        $C['status'] OR $html.=' <i class="fa fa-eye-slash" title="隐藏'.$this->name_text.'"></i> ';
        $html.='<span class="label label-success">cid:<a href="'.$C['iurl']->href.'" target="_blank">'.$C['cid'].'</a></span> ';
        $C['url'] && $html.=' <span class="label label-warning">∞</span>';
        $C['pid'] && $html.=' <span class="label label-inverse">pid:'.$C['pid'].'</span>';
        ($C['mode'] && $C['domain']) && $html.=' <span class="label label-important">绑定域名</span>';
        $html.=' <span class="label label-info">'.$C['count'].'条记录</span>';
        $C['creator'] && $html.=' <span class="label">创建者:'.$C['creator'].'</span>';
        $html.='</span><span class="operation">';
        iACP::CP($C['cid'],'a')  && $html.='<a href="'.APP_URI.'&do=add&rootid='.$C['cid'].'" class="btn btn-small"><i class="fa fa-plus-square"></i> 添加子'.$this->name_text.'</a> ';
        $html.=$this->treebtn($C);
        iACP::CP($C['cid'],'e') && $html.='<a href="'.APP_URI.'&do=add&cid='.$C['cid'].'" title="编辑'.$this->name_text.'设置"  class="btn btn-small"><i class="fa fa-edit"></i> 编辑</a> ';
        iACP::CP($C['cid'],'d') && $html.='<a href="'.APP_FURI.'&do=del&cid='.$C['cid'].'" class="btn btn-small" onClick="return confirm(\'确定要删除此'.$this->name_text.'和'.$this->name_text.'下的所有内容?\');" target="iPHP_FRAME"><i class="fa fa-trash-o"></i> 删除</a>';
        $html.='</span></div>';
        return $html;
    }
    function check_dir($dir,$appid,$url,$cid=0){
        $sql ="SELECT `dir` FROM `#iCMS@__category` where `dir` ='$dir' AND `appid`='$appid'";
        $cid && $sql.=" AND `cid` !='$cid'";
        iDB::value($sql) && empty($url) && iPHP::alert('该'.$this->name_text.'静态目录已经存在!<br />请重新填写(URL规则设置->静态目录)');
    }

    function recount(){
        $rs = iDB::all("SELECT `cid` FROM `#iCMS@__category` where `appid`='$this->appid'");
        foreach ((array)$rs as $key => $value) {
            $this->update_count($value[$i]['cid']);
        }
    }
    function get_ids($cid = "0",$all=true) {
        $cids   = array();
        foreach((array)$this->rootid[$cid] AS $_cid) {
            if(!iACP::CP($_cid,'cs')) continue;

            $cids[] = $_cid;
            $all && $cids[]  = $this->get_ids($_cid,$all);
        }
        $cids = array_unique($cids);
        $cids = array_filter($cids);
        return $cids;
    }

    function select($permission='',$_cid="0",$cid="0",$level = 1,$url=false) {
        foreach((array)$this->_array[$cid] AS $root=>$C) {
            if(iACP::CP($C['cid'],$permission) && $C['status']) {
                $tag      = ($level=='1'?"":"├ ");
                $selected = ($_cid==$C['cid'])?"selected":"";
                $text     = str_repeat("│　", $level-1).$tag.$C['name']."[cid:{$C['cid']}][pid:{$C['pid']}]".($C['url']?"[∞]":"");
                ($C['url'] && !$url) && $selected ='disabled';
                $option.="<option value='{$C['cid']}' $selected>{$text}</option>";
            }
            $this->rootid[$C['cid']] && $option.=$this->select($permission,$_cid,$C['cid'],$level+1,$url);
        }
        return $option;
    }

    //接口
    function delcontent($cid){

    }
    function merge($tocid,$cid){
        iDB::query("UPDATE `#iCMS@__article` SET `cid` ='$tocid' WHERE `cid` ='$cid'");
        iDB::query("UPDATE `#iCMS@__tags` SET `cid` ='$tocid' WHERE `cid` ='$cid'");
        //iDB::query("UPDATE `#iCMS@__push` SET `cid` ='$tocid' WHERE `cid` ='$cid'");
        iDB::query("UPDATE `#iCMS@__prop` SET `cid` ='$tocid' WHERE `cid` ='$cid'");
    }
    function update_count($cid){
        $cc = iDB::value("SELECT count(*) FROM `#iCMS@__article` where `cid`='$cid'");
        iDB::query("UPDATE `#iCMS@__category` SET `count` ='$cc' WHERE `cid` ='$cid'");
    }
    function listbtn($rs){
        $a='<a href="'.iURL::get('category',$rs)->href.'" class="btn btn-small"><i class="fa fa-link"></i> 访问</a> ';
        iACP::CP($rs['cid'],'ca') && $a.='<a href="'.__ADMINCP__.'=article&do=add&cid='.$rs['cid'] .'" class="btn btn-small"><i class="fa fa-edit"></i> 添加文章</a> ';
        iACP::CP($rs['cid'],'cs') && $a.='<a href="'.__ADMINCP__.'=article&cid='.$rs['cid'] .'&sub=on" class="btn btn-small"><i class="fa fa-list-alt"></i> 文章管理</a> ';
        return $a;
    }
    function treebtn($rs){
        return $this->listbtn($rs);
    }
    function batchbtn(){
        return '<li><a data-toggle="batch" data-action="mode"><i class="fa fa-cogs"></i> 访问模式</a></li>
                <li class="divider"></li>
                <li><a data-toggle="batch" data-action="categoryRule"><i class="fa fa-link"></i> '.$this->name_text.'规则</a></li>
                <li><a data-toggle="batch" data-action="contentRule"><i class="fa fa-link"></i> 内容规则</a></li>
                <li><a data-toggle="batch" data-action="urlRule"><i class="fa fa-link"></i> 其它规则</a></li>
                <li class="divider"></li>
                <li><a data-toggle="batch" data-action="indexTPL"><i class="fa fa-columns"></i> 首页模板</a></li>
                <li><a data-toggle="batch" data-action="listTPL"><i class="fa fa-columns"></i> 列表模板</a></li>
                <li><a data-toggle="batch" data-action="contentTPL"><i class="fa fa-columns"></i> 内容模板</a></li>';
    }
}
