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
class categoryApp{
    function __construct($appid = 1) {
        $this->cid   = (int)$_GET['cid'];
        $this->appid = iCMS_APP_ARTICLE;
        $appid          && $this->appid = (int)$appid;
        $_GET['appid']  && $this->appid = (int)$_GET['appid'];
        $this->category  = iPHP::appClass("category",$this->appid);
        $this->name_text = "栏目";
    }
    function doadd(){
        if($this->cid) {
            iMember::CP($this->cid,'Permission_Denied',APP_URI);
            $rs		= iDB::getRow("SELECT * FROM `#iCMS@__category` WHERE `cid`='$this->cid' LIMIT 1;",ARRAY_A);
            $rootid	= $rs['rootid'];
            $rs['metadata'] && $rs['metadata']=unserialize($rs['metadata']);
            $rs['contentprop'] && $rs['contentprop']=unserialize($rs['contentprop']);
            $rs['body'] = iCache::get('iCMS/category.'.$this->cid.'/body');
        }else {
            $rootid=(int)$_GET['rootid'];
            $rootid && iMember::CP($rootid,'Permission_Denied',APP_URI);
        }
        if(empty($rs)) {
            $rs                 = array();
            $rs['pid']          = '0';
            $rs['status']       = '1';
            $rs['isexamine']    = '1';
            $rs['issend']       = '1';
            $rs['orderNum']     = $rs['mode'] = '0';
            $rs['htmlext']      = '.html';
            $rs['categoryURI']  = 'category';
            $rs['categoryRule'] = '{CDIR}/index{EXT}';
            $rs['contentRule']  = '{CDIR}/{YYYY}/{MM}{DD}/{AID}{EXT}';
            $rs['metadata']     ='';
            $rs['contentprop']  ='';
	        if($rootid){
                $rootRs             = iDB::getRow("SELECT * FROM `#iCMS@__category` WHERE `cid`='".$rootid."' LIMIT 1;",ARRAY_A);
                $rs['htmlext']      = $rootRs['htmlext'];
                $rs['categoryRule'] = $rootRs['categoryRule'];
                $rs['contentRule']  = $rootRs['contentRule'];
	        }
        }
        include iACP::view("category.add");
    }
    function dosave(){
        $cid          = (int)$_POST['cid'];
        $rootid       = (int)$_POST['rootid'];
        $status       = (int)$_POST['status'];
        $isucshow     = (int)$_POST['isucshow'];
        $issend       = (int)$_POST['issend'];
        $isexamine    = (int)$_POST['isexamine'];
        $orderNum     = (int)$_POST['orderNum'];
        $mode         = (int)$_POST['mode'];
        $pid          = implode(',', (array)$_POST['pid']);
        $opid         = iS::escapeStr($_POST['opid']);;
        $name         = iS::escapeStr($_POST['name']);
        $subname      = iS::escapeStr($_POST['subname']);
        $domain       = iS::escapeStr($_POST['domain']);
        $htmlext      = iS::escapeStr($_POST['htmlext']);
        $url          = iS::escapeStr($_POST['url']);
        $password     = iS::escapeStr($_POST['password']);
        $pic          = iS::escapeStr($_POST['pic']);
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
        $bodyData     = $_POST['body'];
        $body         = $bodyData?1:0;

        ($cid && $cid==$rootid) && iPHP::alert('不能以自身做为上级'.$this->name_text);
        empty($name) && iPHP::alert($this->name_text.'名称不能为空!');
		if($metadata){
	        $md	= array();
			foreach($metadata['key'] AS $_mk=>$_mval){
				!preg_match("/[a-zA-Z0-9_\-]/",$_mval) && iPHP::alert($this->name_text.'附加属性名称只能由英文字母、数字或_-组成(不支持中文)');
				$md[$_mval]=$metadata['value'][$_mk];
			}
			$metadata	= addslashes(serialize($md));
		}
		if($contentprop){
	        $ca			= array();
			foreach($contentprop['key'] AS $_cak=>$_caval){
				$_caval OR $_caval = strtolower(pinyin($contentprop['name'][$_cak]));
				!preg_match("/[a-zA-Z0-9_\-]/",$_caval) && iPHP::alert('内容附加属性字段只能由英文字母、数字或_-组成(不支持中文)');
				$ca[$_caval]=$contentprop['name'][$_cak];
			}
			$contentprop	= addslashes(serialize($ca));
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
        $map = new map(iCMS_APP_CATEGORY);

        if(empty($cid)) {
        	$nameArray	= explode("\n",$name);
        	foreach($nameArray AS $nkey=>$_name){
        		$_name	= trim($_name);
                if(empty($_name)) continue;
		        if(empty($url)){
		            $_dir = strtolower(pinyin($_name));
		        }
	            (iDB::getValue("SELECT `dir` FROM `#iCMS@__category` where `dir` ='$_dir' AND `appid`='$this->appid'") && empty($url)) && iPHP::alert('该'.$this->name_text.'别名/目录已经存在!请另选一个');
	            iDB::query("INSERT INTO `#iCMS@__category` (`rootid`,`appid`,`orderNum`,`name`,`subname`,`password`,`title`,`keywords`,`description`,`dir`,`mode`,`domain`,`url`,`pic`,`htmlext`,`categoryURI`,`categoryRule`,`contentRule`,`urlRule`,`indexTPL`,`listTPL`,`contentTPL`,`metadata`,`contentprop`,`body`,`pid`,`isexamine`,`issend`,`isucshow`,`status`)
	    		VALUES ('$rootid','$this->appid', '$orderNum', '$_name','$subname','$password','$title','$keywords', '$description', '$_dir','$mode','$domain', '$url','$pic','$htmlext','$categoryURI','$categoryRule', '$contentRule','$urlRule','$indexTPL', '$listTPL', '$contentTPL','$metadata','$contentprop', '$body','$pid','$isexamine','$issend','$isucshow','$status')");
	    		$cid = iDB::$insert_id;
                $map->add($pid,$cid);
	            $this->category->cache(false,$this->appid);
	            $this->category->cacheOne($cid);
            }
            $msg=$this->name_text."添加完成!";
        }else {
            iMember::CP($cid,'Permission_Denied',APP_URI);
            $rootid!=$category->category[$cid]['rootid'] && iMember::CP($rootid,'Permission_Denied',APP_URI);
            iDB::getValue("SELECT `dir` FROM `#iCMS@__category` where `dir` ='$dir' AND `cid` !='$cid' AND `appid`='$this->appid'") && empty($url) &&  iPHP::alert('该'.$this->name_text.'别名/目录已经存在!请另选一个');
            iDB::query("UPDATE `#iCMS@__category` SET `rootid` = '$rootid',`orderNum` = '$orderNum',`name` = '$name',`subname` = '$subname',`password`='$password',`title` = '$title',`keywords` = '$keywords',`description` = '$description',`dir` = '$dir',`url` = '$url',`mode` = '$mode',`domain` = '$domain',`pic`='$pic',`htmlext`='$htmlext',`categoryURI`='$categoryURI',`categoryRule`='$categoryRule',`contentRule`='$contentRule',`urlRule`='$urlRule',`indexTPL` = '$indexTPL',`listTPL` = '$listTPL',`contentTPL` = '$contentTPL',`metadata` = '$metadata',`contentprop` = '$contentprop',`body` = '$body',`pid` = '$pid',`isexamine`='$isexamine',`status`='$status',`issend`='$issend',`isucshow`='$isucshow' WHERE `cid` ='$cid' ");
            $map->diff($pid,$opid,$cid);
            $this->category->cacheOne($cid);
            $msg=$this->name_text."编辑完成!";
        }
        $body && iCache::set('iCMS/category.'.$cid.'/body',$bodyData,0);
        iPHP::OK($msg,'url:'.APP_URI);
    }
    function doupdate(){
    	foreach((array)$_POST['name'] as $cid=>$name){
    		$name	= iS::escapeStr($name);
			iDB::query("UPDATE `#iCMS@__category` SET `name` = '$name',`orderNum` = '".(int)$_POST['orderNum'][$cid]."' WHERE `cid` ='".(int)$cid."' LIMIT 1");
	    	$this->category->cacheOne($cid);
    	}
    	iPHP::OK('更新完成');
    }
    function dobatch(){
        $_POST['id'] OR iPHP::alert("请选择要操作的".$this->name_text);
        $idArray = (array)$_POST['id'];
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
        switch($batch){
            case 'move':
                $tocid = (int)$_POST['tocid'];                
                $key   = array_search($tocid,$idArray);
                if($tocid) unset($idArray[$key]);//清除同ID
                foreach($idArray as $k=>$cid){
                    iDB::query("UPDATE `#iCMS@__category` SET `rootid` ='$tocid' WHERE `cid` ='$cid'"); 
                }
                $this->category->cache(true,$this->appid);
                iPHP::OK('更新完成!','js:1');
            break;
            case 'merge':
                $tocid = (int)$_POST['tocid'];
                $key   = array_search($tocid,$idArray);
                unset($idArray[$key]);//清除同ID
                foreach($idArray as $k=>$cid){
                    $this->mergecontent($tocid,$cid);
                    $this->dodel($cid,false);
                }
                $this->updateCount($tocid);
                $this->category->cache(true,$this->appid);
                iPHP::OK('更新完成!','js:1');
            break;
            case 'name':
                foreach($idArray as $k=>$cid){
                    $name   = iS::escapeStr($_POST['name'][$cid]);
                    iDB::query("UPDATE `#iCMS@__category` SET `name` = '$name' WHERE `cid` ='".(int)$cid."' LIMIT 1");
                    $this->category->cacheOne($cid);
                }
                iPHP::OK('更新完成!','js:1');
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
                foreach($idArray as $k=>$cid){
                    $this->updateCount($cid);
                }
                iPHP::OK('操作成功!','js:1');
            break;
            case 'dels':
                iPHP::$break    = false;
                foreach($idArray AS $cid){
                    $this->dodel($cid,false);
                }
                iPHP::$break    = true;
                iPHP::OK('全部删除完成!','js:1');
            break;
       }
        iDB::query("UPDATE `#iCMS@__category` SET {$sql} WHERE `cid` IN ($ids)");
        $this->category->cache(true,$this->appid);
        iPHP::OK('操作成功!','js:1');
    }
    function doupdateorder(){
    	foreach((array)$_POST['ordernum'] as $orderNum=>$cid){
            iDB::query("UPDATE `#iCMS@__category` SET `orderNum` = '".intval($orderNum)."' WHERE `cid` ='".intval($cid)."' LIMIT 1");
	    	$this->category->cacheOne($cid);
    	}
    }
    function doiCMS(){
        $tabs   = iPHP::getCookie(iACP::$app_name.'_tabs');
        $tabs=="list"?$this->dolist():$this->dotree();
    }
    function dotree() {
        iACP::$app_do   = 'tree';
        include iACP::view("category.manage");
    }
    function dolist(){
        iACP::$app_do = 'list';
        $sql          = " where `appid`='{$this->appid}'";
        if($_GET['keywords']) {
            if($_GET['st']=="name") {
                $sql.=" AND `name` REGEXP '{$_GET['keywords']}'";
            }else if($_GET['st']=="cid") {
                $sql.=" AND `cid` REGEXP '{$_GET['keywords']}'";
            }else if($_GET['st']=="tkd") {
                $sql.=" AND CONCAT(name,title,keywords,description) REGEXP '{$_GET['keywords']}'";
            }
        }
        $orderby      = $_GET['orderby']?$_GET['orderby']:"cid DESC";
        $maxperpage   = (int)$_GET['perpage']>0?$_GET['perpage']:20;
        $total        = iPHP::total(false,"SELECT count(*) FROM `#iCMS@__category` {$sql}","G");
        iPHP::pagenav($total,$maxperpage);
        $rs           = iDB::getArray("SELECT * FROM `#iCMS@__category` {$sql} order by {$orderby} LIMIT ".iPHP::$offset." , {$maxperpage}");
        $_count       = count($rs);
        include iACP::view("category.manage");
    }
    function doajaxtree(){
		$hasChildren=$_GET['hasChildren']?true:false;
	 	echo $this->tree($_GET["root"],$hasChildren);
    }
    function tree($cid =0,$hasChildren=false){
    	$cid=='source' && $cid=0;
        foreach((array)$this->category->_array[$cid] AS $root=>$C) {
	    	if(iMember::CP($C['cid'])) {
	        	$a=array();
	        	$a['id']	= $C['cid'];
	        	$a['text']	= $this->li($C,$op);
	            if($this->category->_array[$C['cid']]){
	            	if($hasChildren){
		            	$a['hasChildren']	= false;
		            	$a['expanded']		= true;
		            	$a['children']		= $this->tree($C['cid'],$hasChildren);
	            	}else{
		            	$a['hasChildren']	= true;
	            	}
	            }
	            $tr[]=$a;
		    }
        }
        if($hasChildren && $cid!='source'){ return $tr; }
        return $tr?json_encode($tr):'[]';
    }

    function li($C,$op='html') {
        if(iMember::CP($C['cid'])) {
            $readonly	= '';
            $CAction	= true;
        }else {
            $readonly	= ' readonly="true" class="readonly"';
            $CAction	= false;
            return '';
//			if($Q=='all')return false;
        }
        $tr='<div class="row-fluid status'.$C['status'].'"><span class="ordernum"><input'.$readonly.' type="text" cid="'.$C['cid'].'" name="orderNum['.$C['cid'].']" value="'.$C['orderNum'].'" style="width:32px;"/></span>';
        $tr.='<span class="name'.$modelCss.'">';
        $tr.='<input'.$readonly.($C['rootid']==0?' style="font-weight:bold"':'').' type="text" name="name['.$C['cid'].']" value="'.$C['name'].'"/> ';
        $C['status'] OR $tr.=' <i class="fa fa-eye-slash" title="隐藏'.$this->name_text.'"></i> ';
        $tr.='<span class="label label-success">cid:<a href="'.$C['iurl']->href.'" target="_blank">'.$C['cid'].'</a></span> ';
        $C['url'] && $tr.=' <span class="label label-warning">∞</span> ';
        $tr.='<span class="label label-inverse">pid:'.$C['pid'].'</span> ';
        ($C['mode'] && $C['domain']) && $tr.='<span class="label label-important">绑定域名</span> ';
        $tr.='<span class="label label-info">'.$C['count'].'条记录</span></span><span class="operation'.$modelCss.'">';
        if($CAction) {
            $tr.='<a href="'.APP_URI.'&do=add&rootid='.$C['cid'].'" class="btn btn-small"><i class="fa fa-plus-square"></i> 添加子'.$this->name_text.'</a> ';
            $tr.=$this->treebtn($C);
            $tr.='<a href="'.APP_URI.'&do=add&cid='.$C['cid'].'" title="编辑'.$this->name_text.'设置"  class="btn btn-small"><i class="fa fa-edit"></i> 编辑</a> <a href="'.APP_FURI.'&do=del&cid='.$C['cid'].'" class="btn btn-small" onClick="return confirm(\'确定要删除此'.$this->name_text.'和'.$this->name_text.'下的所有内容?\');" target="iPHP_FRAME"><i class="fa fa-trash-o"></i> 删除</a>';
        }else {
            $tr.='无权限';
        }
        $tr.='</span></div>';
        return $tr;
    }
    function dodel($cid = null,$dialog=true){
        $cid===null && $cid=(int)$_GET['cid'];
        iMember::CP($cid,'Permission_Denied',APP_URI);
    	$msg	= '请选择要删除的'.$this->name_text.'!';
        if(empty($this->category->_array[$cid])) {
            $this->delcontent($cid);
            iDB::query("DELETE FROM `#iCMS@__category` WHERE `cid` = '$cid'");
            $dialog && $this->category->cache(true,$this->appid);
            $msg	= '删除成功!';
        }else {
        	$msg	= '请先删除本'.$this->name_text.'下的子'.$this->name_text.'!';
        }
		$dialog && iPHP::OK($msg,'js:parent.$("#'.$cid.'").parent().remove();');
    }
    function reCount(){
        $rs     = iDB::getArray("SELECT `cid` FROM `#iCMS@__category` where `appid`='$this->appid'");
        $_count = count($rs);
		for ($i=0;$i<$_count;$i++) {
            $this->updateCount($rs[$i]['cid']);
		}
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
    function updateCount($cid){
        $cc = iDB::getValue("SELECT count(*) FROM `#iCMS@__article` where `cid`='$cid'");
        iDB::query("UPDATE `#iCMS@__category` SET `count` ='$cc' WHERE `cid` ='$cid'");       
    }
    function listbtn($rs){
        return '<a href="'.iURL::get('category',$rs)->href.'" class="btn btn-small"><i class="fa fa-link"></i> 访问</a> <a href="'.__ADMINCP__.'=article&do=add&cid='.$rs['cid'] .'" class="btn btn-small"><i class="fa fa-edit"></i> 添加文章</a> <a href="'.__ADMINCP__.'=article&cid='.$rs['cid'] .'" class="btn btn-small"><i class="fa fa-list-alt"></i> 文章管理</a>';
    }
    function treebtn($rs){}
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
