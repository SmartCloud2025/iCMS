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
defined('iPHP') OR exit('What are you doing?');

iPHP::app('tag.class','static');
class tagsApp{
    function __construct() {
        $this->appid       = iCMS_APP_TAG;
        $this->id          = (int)$_GET['id'];
        $this->tagcategory = iACP::app('tagcategory');
        $this->categoryApp = iACP::app('category','all');
    }
    function do_add(){
        $this->id && $rs = iDB::row("SELECT * FROM `#iCMS@__tags` WHERE `id`='$this->id' LIMIT 1;",ARRAY_A);
        $rs['metadata'] && $rs['metadata']=unserialize($rs['metadata']);
        include iACP::view('tags.add');
    }
    function do_update(){
        if($this->id){
            $data = iACP::fields($_GET['iDT']);
            $data && iDB::update("tags",$data,array('id'=>$this->id));
            tag::cache($this->id,'id');
            iPHP::success('操作成功!','js:1');
        }
    }
    function do_iCMS(){
    	iACP::$app_method="domanage";
    	$this->do_manage();
    }
    function do_manage(){
        $sql  = " where 1=1";
        $cid  = (int)$_GET['cid'];
        $tcid = (int)$_GET['tcid'];
        $pid  = (int)$_GET['pid'];

        $_GET['keywords'] && $sql.=" AND CONCAT(name,seotitle,subtitle,keywords,description) REGEXP '{$_GET['keywords']}'";

        $sql.= $this->categoryApp->search_sql($cid);
        $sql.= $this->tagcategory->search_sql($tcid,'tcid');

        isset($_GET['pic']) && $sql.=" AND `haspic` ='".($_GET['pic']?1:0)."'";
        if(isset($_GET['pid']) && $pid!='-1'){
            $uri_array['pid'] = $pid;
            if($_GET['pid']==0){
                $sql.= " AND `pid`=''";
            }else{
                iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
                map::init('prop',$this->appid);
                $map_where = map::where($pid);
            }
        }
        if($map_where){
            $map_sql = iCMS::map_sql($map_where);
            $sql     = ",({$map_sql}) map {$sql} AND `id` = map.`iid`";
        }

        $orderby	= $_GET['orderby']?$_GET['orderby']:"id DESC";
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total		= iPHP::total(false,"SELECT count(*) FROM `#iCMS@__tags` {$sql}","G");
        iPHP::pagenav($total,$maxperpage,"个标签");
        $limit  = 'LIMIT '.iPHP::$offset.','.$maxperpage;
        if($map_sql||iPHP::$offset){
            $ids_array = iDB::all("
                SELECT `id` FROM `#iCMS@__tags` {$sql}
                ORDER BY {$orderby} {$limit}
            ");
            //iDB::debug(1);
            $ids   = iCMS::get_ids($ids_array);
            $ids   = $ids?$ids:'0';
            $sql   = "WHERE `id` IN({$ids})";
            $limit = '';
        }
        $rs     = iDB::all("SELECT * FROM `#iCMS@__tags` {$sql} ORDER BY {$orderby} {$limit}");
        $_count = count($rs);
    	include iACP::view("tags.manage");
    }
    function do_import(){
        $_POST['cid'] OR iPHP::alert('请选择标签所属栏目！');
        iFS::$checkFileData           = false;
        iFS::$config['allow_ext']     = 'txt';
        iFS::$config['yun']['enable'] = false;
        $F    = iFS::upload('upfile');
        $path = $F['RootPath'];
        if($path){
            $contents = file_get_contents($path);
            $encode   = mb_detect_encoding($contents, array("ASCII","UTF-8","GB2312","GBK","BIG5"));
            if(strtoupper($encode)!='UTF-8'){
                if (function_exists('mb_convert_encoding')) {
                    $contents = mb_convert_encoding($contents,'UTF-8',$encode);
                } elseif (function_exists('iconv')) {
                    $contents = iconv($encode,'UTF-8', $contents);
                }else{
                    iPHP::alert('请把文件编码转换成UTF-8！');
                }
            }
            if($contents){
                iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
                $fields   = array('uid', 'cid', 'tcid', 'pid', 'tkey', 'name', 'seotitle', 'subtitle', 'keywords', 'description', 'metadata','haspic', 'pic', 'url', 'related', 'count', 'weight', 'tpl', 'ordernum', 'pubdate', 'status');
                $cid      = implode(',', (array)$_POST['cid']);
                $tcid     = implode(',', (array)$_POST['tcid']);
                $pid      = implode(',', (array)$_POST['pid']);
                $variable = explode("\n", $contents);
                $msg = array();
                foreach ($variable as $key => $name) {
                    $name = trim($name);
                    if(empty($name)){
                        $msg['empty']++;
                        continue;
                    }
                    $name = preg_replace('/<[\/\!]*?[^<>]*?>/is','',$name);
                    $name = addslashes($name);
                    if(iDB::value("SELECT `id` FROM `#iCMS@__tags` where `name` = '$name'")){
                        $msg['has']++;
                        continue;
                    }
                    $tkey    = strtolower(pinyin($name));
                    $uid     = iMember::$userid;
                    $haspic  = '0';
                    $status  = '1';
                    $pubdate = time();
                    $data    = compact ($fields);
                    $id = iDB::insert('tags',$data);

                    map::init('prop',$this->appid);
                    $pid && map::add($pid,$id);

                    map::init('category',$this->appid);
                    $cid  && map::add($cid,$id);
                    $tcid && map::add($tcid,$id);
                    $msg['success']++;
                }
            }
            @unlink($path);
            iPHP::success('标签导入完成<br />空标签:'.(int)$msg['empty'].'个<br />已经存在标签:'.(int)$msg['has'].'个<br />成功导入标签:'.(int)$msg['success'].'个');
        }
    }
    function do_save(){
        $id          = (int)$_POST['id'];
        $uid         = (int)$_POST['uid'];
        $cid         = implode(',', (array)$_POST['cid']);
        $tcid        = implode(',', (array)$_POST['tcid']);
        $pid         = implode(',', (array)$_POST['pid']);
        $_cid        = iS::escapeStr($_POST['_cid']);
        $_tcid       = iS::escapeStr($_POST['_tcid']);
        $_pid        = iS::escapeStr($_POST['_pid']);
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
        $haspic       = $pic?'1':'0';
        $pubdate     = time();
        $metadata    = iS::escapeStr($_POST['metadata']);

        $uid OR $uid= iMember::$userid;

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
			iDB::value("SELECT `id` FROM `#iCMS@__tags` where `name` = '$name'") && iPHP::alert('该标签已经存在!请检查是否重复');
		}
		if(empty($tkey) && $url){
			$tkey = substr(md5($url),8,16);
			iDB::value("SELECT `id` FROM `#iCMS@__tags` where `tkey` = '$tkey'") && iPHP::alert('该自定义链接已经存在!请检查是否重复');
		}
		$tkey OR $tkey = strtolower(pinyin($name));
		strstr($pic, 'http://') && $pic = iFS::http($pic);
		iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');

        $fields = array('uid', 'cid', 'tcid', 'pid', 'tkey', 'name', 'seotitle', 'subtitle', 'keywords', 'description', 'metadata','haspic', 'pic', 'url', 'related', 'count', 'weight', 'tpl', 'ordernum', 'pubdate', 'status');
        $data   = compact ($fields);

		if(empty($id)){
            $data['count']    ='0';
            $data['comments'] ='0';
            $id = iDB::insert('tags',$data);
			tag::cache($id,'id');

            map::init('prop',$this->appid);
            $pid && map::add($pid,$id);

            map::init('category',$this->appid);
            map::add($cid,$id);
            $tcid && map::add($tcid,$id);

	        iPHP::success('标签添加完成',"url:".APP_URI);
		}else{
            unset($data['count'],$data['comments']);
            iDB::update('tags', $data, array('id'=>$id));
			tag::cache($id,'id');

            map::init('prop',$this->appid);
            map::diff($pid,$_pid,$id);

            map::init('category',$this->appid);
            map::diff($cid,$_cid,$id);
            map::diff($tcid,$_tcid,$id);
        	iPHP::success('标签更新完成',"url:".APP_URI);
		}
    }

    function do_cache(){
    	tag::cache($this->id,'id');
    	iPHP::success("标签缓存更新成功");
    }
    function do_del($id = null,$dialog=true){
    	$id===null && $id=$this->id;
        iDB::query("DELETE FROM `#iCMS@__category_map` WHERE `iid` = '$id' AND `appid` = '".$this->appid."';");
        iDB::query("DELETE FROM `#iCMS@__prop_map` WHERE `iid` = '$id' AND `appid` = '".$this->appid."' ;");

    	tag::del($id,'id');
    	$dialog && iPHP::success("标签删除成功",'js:parent.$("#tr'.$id.'").remove();');
    }
    function do_batch(){
        $idArray = (array)$_POST['id'];
        $idArray OR iPHP::alert("请选择要操作的标签");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
    	switch($batch){
    		case 'dels':
				iPHP::$break	= false;
	    		foreach($idArray AS $id){
	    			$this->do_del($id,false);
	    		}
	    		iPHP::$break	= true;
				iPHP::success('标签全部删除完成!','js:1');
    		break;
    		case 'move':
		        $_POST['cid'] OR iPHP::alert("请选择目标栏目!");
                iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
                map::init('category',$this->appid);
		        $cid = (int)$_POST['cid'];
		        foreach($idArray AS $id) {
                    $_cid = iDB::value("SELECT `cid` FROM `#iCMS@__tags` where `id` ='$id'");
                    iDB::update("tags",compact('cid'),compact('id'));
		            if($_cid!=$cid) {
                        map::diff($cid,$_cid,$id);
                        $this->categoryApp->update_count_one($_cid,'-');
                        $this->categoryApp->update_count_one($cid);
		            }
		        }
		        iPHP::success('成功移动到目标栏目!','js:1');
    		break;
    		case 'mvtcid':
		        $_POST['tcid'] OR iPHP::alert("请选择目标分类!");
                iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
                map::init('category',$this->appid);
		        $tcid = (int)$_POST['tcid'];
		        foreach($idArray AS $id) {
                    $_tcid = iDB::value("SELECT `tcid` FROM `#iCMS@__tags` where `id` ='$id'");
                    iDB::update("tags",compact('tcid'),compact('id'));
		            if($_tcid!=$tcid) {
                        map::diff($tcid,$_tcid,$id);
                        $this->categoryApp->update_count_one($_tcid,'-');
                        $this->categoryApp->update_count_one($tcid);
		            }
		        }
		        iPHP::success('成功移动到目标分类!','js:1');
    		break;
    		case 'prop':
                iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
                map::init('prop',$this->appid);
                $pid = implode(',', (array)$_POST['pid']);
                foreach((array)$_POST['id'] AS $id) {
                    $_pid = iDB::value("SELECT pid FROM `#iCMS@__tags` WHERE `id`='$id'");;
                    iDB::update("tags",compact('pid'),compact('id'));
                    map::diff($pid,$_pid,$id);
                }
                iPHP::success('属性设置完成!','js:1');
    		break;
    		case 'weight':
		        $weight	=_int($_POST['mweight']);
		        $sql	="`weight` = '$weight'";
    		break;
            case 'tpl':
                $tpl = iS::escapeStr($_POST['mtpl']);
                $sql = "`tpl` = '$tpl'";
            break;
    		case 'keyword':
    			if($_POST['pattern']=='replace') {
    				$sql	="`keywords` = '".iS::escapeStr($_POST['mkeyword'])."'";
    			}elseif($_POST['pattern']=='addto') {
		        	foreach($idArray AS $id){
                        $keywords = iDB::value("SELECT keywords FROM `#iCMS@__tags` WHERE `id`='$id'");
                        $sql      ="`keywords` = '".($keywords?$keywords.','.iS::escapeStr($_POST['mkeyword']):iS::escapeStr($_POST['mkeyword']))."'";
				        iDB::query("UPDATE `#iCMS@__tags` SET {$sql} WHERE `id`='$id'");
		        	}
		        	iPHP::success('关键字更改完成!','js:1');
    			}
    		break;
    		case 'tag':
    			if($_POST['pattern']=='replace') {
    				$sql	="`related` = '".iS::escapeStr($_POST['mtag'])."'";
    			}elseif($_POST['pattern']=='addto') {
		        	foreach($idArray AS $id){
		        		$keywords	= iDB::value("SELECT related FROM `#iCMS@__tags` WHERE `id`='$id'");
		        		$sql		="`related` = '".($keywords?$keywords.','.iS::escapeStr($_POST['mtag']):iS::escapeStr($_POST['mtag']))."'";
				        iDB::query("UPDATE `#iCMS@__tags` SET {$sql} WHERE `id`='$id'");
		        	}
		        	iPHP::success('相关标签更改完成!','js:1');
    			}
    		break;
    		default:
                iPHP::alert('请选择要操作项!','js:1');
				// $data = iACP::fields($batch);
    //             foreach($idArray AS $id) {
    //                 $data && iDB::update("tags",$data,array('id'=>$id));
    //             }
		}
        $sql && iDB::query("UPDATE `#iCMS@__tags` SET {$sql} WHERE `id` IN ($ids)");
		iPHP::success('操作成功!','js:1');
	}
}
