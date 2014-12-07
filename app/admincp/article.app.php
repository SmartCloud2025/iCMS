<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: article.app.php 2406 2014-04-28 02:24:46Z coolmoo $
*/
defined('iPHP') OR exit('What are you doing?');

iPHP::app('article.table');
class articleApp{
    function __construct() {
        $this->appid       = iCMS_APP_ARTICLE;
        $this->id          = (int)$_GET['id'];
        $this->dataid      = (int)$_GET['dataid'];
        $this->categoryApp = iACP::app('category',$this->appid);
        $this->category    = $this->categoryApp->category;
        $this->_postype    = '1';
        $this->_status     = '1';
        define('TAG_APPID',$this->appid);
    }
    // function detag($tags){
    //     if($tags{0}.$tags{1}=='[['){
    //         $tagsArray = json_decode($tags);
    //         foreach ((array)$tagsArray as $k => $_tag) {
    //             $_tagArray[] = $_tag[0];
    //         }
    //         $tags =implode(',', (array)$_tagArray);
    //     }
    //     return $tags;
    // }
    function do_add(){
        $_GET['cid'] && iACP::CP($_GET['cid'],'ca','page');//添加权限
        $rs      = array();
        if($this->id){
            list($rs,$adRs) = articleTable::data($this->id,$this->dataid);
            $bodyArray      = explode('#--iCMS.PageBreak--#',$adRs['body']);
            $bodyCount      = count($bodyArray);
            iACP::CP($rs['cid'],'ce','page');//编辑权限
        }

        $bodyCount OR $bodyCount = 1;
        $cid                 = empty($rs['cid'])?(int)$_GET['cid']:$rs['cid'];
        $cata_option         = $this->categoryApp->select('ca',$cid);
        $cid && $contentprop =  unserialize($this->category[$cid]['contentprop']);

        //$metadata          = array_merge((array)$contentprop,(array)$rs['metadata']);
        $rs['pubdate']       = get_date($rs['pubdate'],'Y-m-d H:i:s');
        $rs['metadata'] && $rs['metadata'] = unserialize($rs['metadata']);

        if(empty($this->id)){
            $rs['status']  = "1";
            $rs['postype'] = "1";
            $rs['editor']  = empty(iMember::$data->nickname)?iMember::$data->username:iMember::$data->nickname;
            $rs['userid']  = iMember::$userid;
		}
        $strpos   = strpos(__REF__,'?');
        $REFERER  = $strpos===false?'':substr(__REF__,$strpos);
        $defArray = iCache::get('iCMS/defaults');
    	include iACP::view("article.add");
    }
    function do_update(){
    	$data = iACP::fields($_GET['iDT']);
        if($data){
            if(isset($data['pid'])){
                iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
                map::init('prop',$this->appid);
                $_pid = articleTable::value('pid',$this->id);
                map::diff($data['pid'],$_pid,$this->id);
            }
            articleTable::update($data,array('id'=>$this->id));
        }
    	iPHP::success('操作成功!','js:1');
    }
    function do_updateorder(){
        foreach((array)$_POST['ordernum'] as $ordernum=>$id){
            articleTable::update(compact('ordernum'),compact('id'));
        }
    }
    function do_batch(){
    	$_POST['id'] OR iPHP::alert("请选择要操作的文章");
    	$ids	= implode(',',(array)$_POST['id']);
    	$batch	= $_POST['batch'];
    	switch($batch){
    		case 'order':
		        foreach((array)$_POST['ordernum'] AS $id=>$ordernum) {
                    articleTable::update(compact('ordernum'),compact('id'));
		        }
		        iPHP::success('排序已更新!','js:1');
    		break;
    		case 'move':
		        $_POST['cid'] OR iPHP::alert("请选择目标栏目!");
                iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
                map::init('category',$this->appid);
                $cid = (int)$_POST['cid'];
                iACP::CP($cid,'ca','alert');
		        foreach((array)$_POST['id'] AS $id) {
                    $_cid = articleTable::value('cid',$id);
                    articleTable::update(compact('cid'),compact('id'));
		            if($_cid!=$cid) {
                        map::diff($cid,$_cid,$id);
                        $this->categoryApp->update_count_one($_cid,'-');
                        $this->categoryApp->update_count_one($cid);
		            }
		        }
		        iPHP::success('成功移动到目标栏目!','js:1');
            break;
            case 'scid':
                //$_POST['scid'] OR iPHP::alert("请选择目标栏目!");
                iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
                map::init('category',$this->appid);
                $scid = implode(',', (array)$_POST['scid']);
                foreach((array)$_POST['id'] AS $id) {
                    $_scid = articleTable::value('scid',$id);
                    articleTable::update(compact('scid'),compact('id'));
                    map::diff($scid,$_scid,$id);
                }
                iPHP::success('文章副栏目设置完成!','js:1');
            break;
            case 'prop':
                iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
                map::init('prop',$this->appid);
                $pid = implode(',', (array)$_POST['pid']);
                foreach((array)$_POST['id'] AS $id) {
                    $_pid = articleTable::value('pid',$id);
                    articleTable::update(compact('pid'),compact('id'));
                    map::diff($pid,$_pid,$id);
                }
                iPHP::success('文章属性设置完成!','js:1');
    		break;
    		case 'weight':
                $data = array('weight'=>_int($_POST['mweight']));
    		break;
    		case 'keyword':
    			if($_POST['pattern']=='replace') {
                    $data = array('keywords'=>iS::escapeStr($_POST['mkeyword']));
    			}elseif($_POST['pattern']=='addto') {
		        	foreach($_POST['id'] AS $id){
                        $keywords = articleTable::value('keywords',$id);
                        $keywords = $keywords?$keywords.','.iS::escapeStr($_POST['mkeyword']):iS::escapeStr($_POST['mkeyword']);
                        articleTable::update(compact('keywords'),compact('id'));
		        	}
		        	iPHP::success('文章关键字更改完成!','js:1');
    			}
    		break;
    		case 'tag':
    			iPHP::app('tag.class','static');
		     	foreach($_POST['id'] AS $id){
                    $art  = articleTable::row($id,'tags,cid');
                    $mtag = iS::escapeStr($_POST['mtag']);
			        if($_POST['pattern']=='replace') {
			        }elseif($_POST['pattern']=='addto') {
			        	$art['tags'] && $mtag = $art['tags'].','.$mtag;
			        }
			        $tags = tag::diff($mtag,$art['tags'],iMember::$userid,$id,$art['cid']);
                    $tags = addslashes($tags);
                    articleTable::update(compact('tags'),compact('id'));
		    	}
		    	iPHP::success('文章标签更改完成!','js:1');
    		break;
    		case 'thumb':
		        foreach((array)$_POST['id'] AS $id) {
		            $body	= articleTable::body($id);
                    $picurl = $this->remotepic($body,'autopic',$id);
                    $this->pic($picurl,$id);
		        }
		        iPHP::success('成功提取缩略图!','js:1');
    		break;
    		case 'dels':
    			iPHP::$break	= false;
    			ob_implicit_flush();
    			$_count	= count($_POST['id']);
				foreach((array)$_POST['id'] AS $i=>$id) {
			     	$msg= $this->delArticle($id);
			        $msg.= $this->del_msg('文章删除完成!');
					$updateMsg	= $i?true:false;
					$timeout	= ($i++)==$_count?'3':false;
					iPHP::dialog($msg,'js:parent.$("#id'.$id.'").remove();',$timeout,0,$updateMsg);
		        	ob_end_flush();
	   			}
	   			iPHP::$break	= true;
				iPHP::success('文章全部删除完成!','js:1',3,0,true);
    		break;
    		default:
				$data = iACP::fields($batch);
    	}
        articleTable::batch($data,$ids);
		iPHP::success('操作成功!','js:1');
    }
    function do_getjson(){
        $id = (int)$_GET['id'];
        $rs = articleTable::row($id);
        iPHP::json($rs);
    }
    function do_getmeta(){
        $cid = $_GET['cid'];
        $cid && $contentprop =  unserialize($this->category[$cid]['contentprop']);
        iPHP::json($contentprop);
    }
	function do_updatetitle(){
        $id          = (int)$_POST['id'];
        $cid         = (int)$_POST['cid'];
        $pid         = (int)$_POST['pid'];
        $source      = iS::escapeStr($_POST['source']);
        $title       = iS::escapeStr($_POST['title']);
        $tags        = iS::escapeStr($_POST['tags']);
        $description = iS::escapeStr($_POST['description']);

		$art = articleTable::row($id,'tags,cid');
		if($tags){
			iPHP::app('tag.class','static');
			$tags = tag::diff($tags,$art['tags'],iMember::$userid,$id,$art['cid']);
		    $tags = addslashes($tags);
        }
        $data = compact('cid','pid','title','tags','description');
		if($_POST['status']=="1"){
            $data['status'] = 1;
		}
		if($_POST['statustime']=="1"){
            $data['status']  = 1;
            $data['pubdate'] = time();
		}
        articleTable::update($data ,compact('id'));
		exit('1');
	}
    function do_preview(){
		echo articleTable::body($this->id);
    }
    function do_iCMS(){
    	iACP::$app_do="manage";
    	$this->do_manage();
    }
    function do_inbox(){
    	$this->do_manage("inbox");
    }
    function do_trash(){
        $this->_postype = 'all';
    	$this->do_manage("trash");
    }
    function do_user(){
        $this->_postype = 0;
        $this->do_manage();
    }
    function do_examine(){
        $this->_postype = 0;
        $this->do_manage("examine");
    }
    function do_off(){
        $this->_postype = 0;
        $this->do_manage("off");
    }

    function do_manage($stype='normal') {
        $cid = (int)$_GET['cid'];
        $pid = $_GET['pid'];
        //$stype OR $stype = iACP::$app_do;
        $stype_map = array(
            'inbox'   =>'0',//草稿
            'normal'  =>'1',//正常
            'trash'   =>'2',//回收站
            'examine' =>'3',//待审核
            'off'     =>'4',//未通过
        );
        $map_where = array();
        //status:[0:草稿][1:正常][2:回收][3:待审核][4:不合格]
        //postype: [0:用户][1:管理员]
        $stype && $this->_status = $stype_map[$stype];
        if(isset($_GET['pt']) && $_GET['pt']!=''){
            $this->_postype = (int)$_GET['pt'];
        }

        $sql = "WHERE `status`='{$this->_status}'";
        $this->_postype==='all' OR $sql.= " AND `postype`='{$this->_postype}'";

        if(iACP::MP("ARTICLE.VIEW")){
            $_GET['userid'] && $sql.= iPHP::where($_GET['userid'],'userid');
        }else{
            $sql.= iPHP::where(iMember::$userid,'userid');
        }

        if(isset($_GET['pid']) && $pid!='-1'){
            $uri_array['pid'] = $pid;
            if(empty($_GET['pid'])){
                $sql.= " AND `pid`=''";
            }else{
                iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
                map::init('prop',$this->appid);
                $map_where+=map::where($pid);
            }
        }

        $cp_cids = iACP::CP('__CID__','cs');//取得所有有权限的栏目ID

        if($cp_cids) {
            if(is_array($cp_cids)){
                if($cid){
                    array_search($cid,$cp_cids)===false && iACP::permission_msg('栏目[cid:'.$cid.']',$ret);
                }else{
                    $cids = $cp_cids;
                }
            }else{
                $cids = $cid;
            }
            if($_GET['sub'] && $cid){
                $cids = $this->categoryApp->get_ids($cid,true);
                array_push ($cids,$cid);
            }
            if($_GET['scid'] && $cid){
                iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
                map::init('category',$this->appid);
                $map_where+= map::where($cids);
            }else{
                $sql.= iPHP::where($cids,'cid');
            }
        }else{
            $sql.= iPHP::where('-1','cid');
        }

        if($_GET['keywords']) {
            $kws = $_GET['keywords'];
            switch ($_GET['st']) {
                case "title": $sql.=" AND `title` REGEXP '{$kws}'";break;
                case "tag":   $sql.=" AND `tags` REGEXP '{$kws}'";break;
                case "source":$sql.=" AND `source` REGEXP '{$kws}'";break;
                case "weight":$sql.=" AND `weight`='{$kws}'";break;
                case "id":    $sql.=" AND `id` REGEXP '{$kws}'";break;
                case "tkd":   $sql.=" AND CONCAT(title,keywords,description) REGEXP '{$kws}'";break;
            }
        }
        $_GET['title']     && $sql.=" AND `title` like '%{$_GET['title']}%'";
        $_GET['tag']       && $sql.=" AND `tags` REGEXP '[[:<:]]".preg_quote(rawurldecode($_GET['tag']),'/')."[[:>:]]'";
        $_GET['starttime'] && $sql.=" AND `pubdate`>='".iPHP::str2time($_GET['starttime']." 00:00:00")."'";
        $_GET['endtime']   && $sql.=" AND `pubdate`<='".iPHP::str2time($_GET['endtime']." 23:59:59")."'";
        isset($_GET['pic'])&& $sql.=" AND `haspic` ='".($_GET['pic']?1:0)."'";

        isset($_GET['userid']) && $uri_array['userid']  = (int)$_GET['userid'];
        isset($_GET['keyword'])&& $uri_array['keyword'] = $_GET['keyword'];
        isset($_GET['tag'])    && $uri_array['tag']	    = $_GET['tag'];
        isset($_GET['pt'])     && $uri_array['pt']      = $_GET['pt'];
        isset($_GET['cid'])    && $uri_array['cid']     = $_GET['cid'];
		$uri_array	&& $uri = http_build_query($uri_array);

        $orderby    = $_GET['orderby']?$_GET['orderby']:"id DESC";
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;

        if($map_where){
            $map_sql = iCMS::map_sql($map_where);
            $sql     = ",({$map_sql}) map {$sql} AND `id` = map.`iid`";
        }

        $total = iPHP::total(false,articleTable::count_sql($sql),"G");
        iPHP::pagenav($total,$maxperpage,"篇文章");

        $limit = 'LIMIT '.iPHP::$offset.','.$maxperpage;

        if($map_sql||iPHP::$offset){
            // if($map_sql){
                $ids_array = iDB::all("
                    SELECT `id` FROM `#iCMS@__article` {$sql}
                    ORDER BY {$orderby} {$limit}
                ");
                //iDB::debug(1);
                $ids = iCMS::get_ids($ids_array);
                $ids = $ids?$ids:'0';
                $sql = "WHERE `id` IN({$ids})";
            // }else{
                // $sql = ",(
                    // SELECT `id` AS aid FROM `#iCMS@__article` {$sql}
                    // ORDER BY {$orderby} {$limit}
                // ) AS art WHERE `id` = art.aid ";
            // }
            $limit = '';
        }
        $rs = iDB::all("SELECT * FROM `#iCMS@__article` {$sql} ORDER BY {$orderby} {$limit}");
        //iDB::debug(1);
        $_count = count($rs);
        include iACP::view("article.manage");
    }
    function do_save($callback=false){
        $aid         = (int)$_POST['aid'];
        $cid         = (int)$_POST['cid'];
        $userid      = (int)$_POST['userid'];
        $scid        = implode(',', (array)$_POST['scid']);
        $pid         = implode(',', (array)$_POST['pid']);
        $status      = (int)$_POST['status'];
        $chapter     = (int)$_POST['chapter'];
        $ordernum    = _int($_POST['ordernum']);
        $_cid        = iS::escapeStr($_POST['_cid']);
        $_pid        = iS::escapeStr($_POST['_pid']);
        $_scid       = iS::escapeStr($_POST['_scid']);
        $_tags       = iS::escapeStr($_POST['_tags']);
        $title       = iS::escapeStr($_POST['title']);
        $stitle      = iS::escapeStr($_POST['stitle']);
        $pic         = iS::escapeStr($_POST['pic']);
        $mpic        = iS::escapeStr($_POST['mpic']);
        $spic        = iS::escapeStr($_POST['spic']);
        $source      = iS::escapeStr($_POST['source']);
        $author      = iS::escapeStr($_POST['author']);
        $editor      = iS::escapeStr($_POST['editor']);
        $description = iS::escapeStr($_POST['description']);
        $keywords    = iS::escapeStr($_POST['keywords']);
        $tags        = str_replace('，', ',',iS::escapeStr($_POST['tags']));
        $clink       = iS::escapeStr($_POST['clink']);
        $url         = iS::escapeStr($_POST['url']);
        $tpl         = iS::escapeStr($_POST['tpl']);
        $metadata    = iS::escapeStr($_POST['metadata']);
        $metadata    = $metadata?addslashes(serialize($metadata)):'';
        $body        = (array)$_POST['body'];
        $creative    = (int)$_POST['creative'];
        iACP::CP($cid,($aid?'ce':'ca'),'alert');

        empty($_POST['pubdate']) && $_POST['pubdate'] = get_date(0,'Y-m-d H:i:s');
        $pubdate   = iPHP::str2time($_POST['pubdate']);
        $weight    = _int($_POST['weight']);
        $postype   = $_POST['postype']?$_POST['postype']:0;
        $ischapter = isset($_POST['ischapter'])?1:0;
        isset($_POST['inbox']) && $status = "0";

        empty($title)&& iPHP::alert('标题不能为空！');
        empty($cid)  && iPHP::alert('请选择所属栏目');
        empty($body) && empty($url) && iPHP::alert('文章内容不能为空！');
        $userid OR $userid = iMember::$userid;
        iFS::$userid = $userid;

        if(empty($aid) && iCMS::$config['publish']['repeatitle']) {
			articleTable::check_title($title) && iPHP::alert('该标题的文章已经存在!请检查是否重复');
		}

        if(iCMS::$config['publish']['autodesc'] && iCMS::$config['publish']['descLen'] && empty($description) && empty($url)) {
            $body_text   = implode("\n",$body);
            $body_text   = str_replace('#--iCMS.PageBreak--#',"\n",$body_text);
            $body_text   = preg_replace(array('/<p[^>]*>/is','/<[\/\!]*?[^<>]*?>/is',"/\n+/","/　+/","/^\n/"),array("\n\n",'',"\n",'',''),$body_text);
            $description = csubstr($body_text,iCMS::$config['publish']['descLen']);
            $description = addslashes($description);
            $description = str_replace('#--iCMS.PageBreak--#','',$description);
            unset($body_text);
        }

        stripos($pic, 'http://') ===false OR $pic  = iFS::http($pic);
        stripos($mpic, 'http://')===false OR $mpic = iFS::http($mpic);
        stripos($spic, 'http://')===false OR $spic = iFS::http($spic);


        $haspic   = empty($pic)?0:1;

        $SELFURL = __SELF__.$_POST['REFERER'];
        if(empty($_POST['REFERER'])||strstr($_POST['REFERER'], '=save')){
        	$SELFURL= __SELF__.'?app=article&do=manage';
        }

        $editor OR	$editor	= empty(iMember::$data->nickname)?iMember::$data->username:iMember::$data->nickname;

        // if($aid && $ischapter){
        //     $this->article_data($body,$aid);
        //     iDB::query("UPDATE `#iCMS@__article` SET `chapter`=chapter+1  WHERE `id` = '$aid'");
        //     iPHP::success('章节添加完成!','url:'.$SELFURL);
        // }
        iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
        $picdata = '';
        $ucid    = 0;

        $fields  = articleTable::fields($aid);

        if(empty($aid)) {
            $postime = $pubdate;
            $hits    = 0;
            $good = $bad = $comments = 0;
            $ischapter && $chapter = 1;
            $mobile = 0;

            $aid  = articleTable::insert(compact($fields));

            if($tags){
                iPHP::app('tag.class','static');
                tag::add($tags,$userid,$aid,$cid);
                //articleTable::update(compact('tags'),array('id'=>$aid));
            }

            map::init('prop',$this->appid);
            $pid && map::add($pid,$aid);

            map::init('category',$this->appid);
            map::add($cid,$aid);
            $scid && map::add($scid,$aid);

            $tagArray && tag::map_iid($tagArray,$aid);

            $url OR $this->article_data($body,$aid,$haspic);
            $this->categoryApp->update_count_one($cid);
            if($callback){
            	return array("code"=>$callback,'indexid'=>$aid);
            }
            $moreBtn = array(
                    array("text" =>"查看该文章","target"=>'_blank',"url"=>iURL::get('article',array(array('id'=>$aid,'url'=>$url,'cid'=>$cid,'pubdate'=>$pubdate),$this->category[$cid]))->href,"o"=>'target="_blank"'),
                    array("text" =>"编辑该文章","url"=>APP_URI."&do=add&id=".$aid),
                    array("text" =>"继续添加文章","url"=>APP_URI."&do=add&cid=".$cid),
                    array("text" =>"返回文章列表","url"=>$SELFURL),
                    array("text" =>"查看网站首页","url"=>iCMS_URL,"target"=>'_blank')
            );
            iPHP::$dialog['lock']	= true;
            iPHP::dialog('success:#:check:#:文章添加完成!<br />10秒后返回文章列表','url:'.$SELFURL,10,$moreBtn);
        }else{
			if($tags){
				iPHP::app('tag.class','static');
	            tag::diff($tags,$_tags,iMember::$userid,$aid,$cid);
            }
            $picdata = $this->picdata($pic,$mpic,$spic);

            articleTable::update(compact($fields),array('id'=>$aid));

            map::init('prop',$this->appid);
            map::diff($pid,$_pid,$aid);

            map::init('category',$this->appid);
            map::diff($cid,$_cid,$aid);
            map::diff($scid,$_scid,$aid);

            $url OR $this->article_data($body,$aid,$haspic);

            //$ischapter && $this->chapter_count($aid);
            if($_cid!=$cid) {
                $this->categoryApp->update_count_one($_cid,'-');
                $this->categoryApp->update_count_one($cid);
            }
            if($callback){
                return array("code"=>$callback,'indexid'=>$aid);
            }

   //       if(!strstr($this->category[$cid]['contentRule'],'{PHP}')&&!$this->category[$cid]['url']&&$this->category[$cid]['mode']=="1" && $status) {
			// 	$htmlApp = iACP::app('html');
			// 	$htmlApp->Article($aid);
			// }
            iPHP::success('文章编辑完成!<br />3秒后返回文章列表','url:'.$SELFURL);
        }
    }
    function do_del(){
        $msg = $this->delArticle($this->id);
        $msg.= $this->del_msg('文章删除完成!');
        $msg.= $this->del_msg('10秒后返回文章列表!');
        iPHP::$dialog['lock'] = true;
        iPHP::dialog($msg,'js:1');
    }
    function del_msg($str){
        return iPHP::msg('success:#:check:#:'.$str.'<hr />',true);
    }
    function del_pic($pic){
        //$thumbfilepath    = gethumb($pic,'','',false,true,true);
        iFS::del(iFS::fp($pic,'+iPATH'));
        $msg    = $this->del_msg($pic.'删除');
//      if($thumbfilepath)foreach($thumbfilepath as $wh=>$fp) {
//              iFS::del(iFS::fp($fp,'+iPATH'));
//              $msg.= $this->del_msg('缩略图 '.$wh.' 文件删除');
//      }
        $filename   = iFS::info($pic)->filename;
        articleTable::del_filedata($filename,'filename');
        $msg.= $this->del_msg($pic.'数据删除');
        return $msg;
    }
    function delArticle($id,$uid='0',$postype='1') {
        $id = (int)$id;
        $id OR iPHP::alert("请选择要删除的文章");
        $uid && $sql="and `userid`='$uid' and `postype`='$postype'";
        $art = articleTable::row($id,'cid,pic,tags',$sql);
        iACP::CP($art['cid'],'cd','alert');
        $frs = articleTable::select_filedata_indexid($id);
        for($i=0;$i<count($frs);$i++) {
            if($frs[$i]){
                $path   = $frs[$i]['path'].'/'.$frs[$i]['filename'].'.'.$frs[$i]['ext'];
                iFS::del(iFS::fp($frs[$i]['path'],'+iPATH'));
                $msg.=$this->del_msg($path.' 文件删除');
            }
        }
        if($art['tags']){
            iPHP::app('tag.class','static');
            $msg.=tag::del($art['tags']);
        }
        iDB::query("DELETE FROM `#iCMS@__category_map` WHERE `iid` = '$id' AND `appid` = '".$this->appid."';");
        iDB::query("DELETE FROM `#iCMS@__prop_map` WHERE `iid` = '$id' AND `appid` = '".$this->appid."' ;");

        articleTable::del_filedata($id,'indexid');
        $msg.= $this->del_msg('相关文件数据删除');
        articleTable::del_comment($id);
        $msg.= $this->del_msg('评论数据删除');
        articleTable::del($id);
        articleTable::del_data($id);
        $msg.= $this->del_msg('文章数据删除');
        $this->categoryApp->update_count_one($art['cid'],'-');
        $msg.= $this->del_msg('栏目数据更新');
        $msg.= $this->del_msg('删除完成');
        return $msg;
    }
    function chapter_count($aid){
        articleTable::chapter_count($aid);
    }

    function article_data($bodyArray,$aid=0,$haspic=0){
        $id       = (int)$_POST['adid'];
        $subtitle = iS::escapeStr($_POST['subtitle']);
        $body     = implode('#--iCMS.PageBreak--#',$bodyArray);
        $body     = preg_replace(array('/<script.+?<\/script>/is','/<form.+?<\/form>/is'),'',$body);
        isset($_POST['dellink']) && $body = preg_replace("/<a[^>].*?>(.*?)<\/a>/si", "\\1",$body);

        $fields = articleTable::data_fields($id);
        $data   = compact ($fields);
        if($id){
            articleTable::data_update($data,compact('id'));
        }else{
            $id = articleTable::data_insert($data);
        }

        $_POST['isRedirect']  && iFS::$isRedirect = true;
        $_POST['iswatermark'] && iFS::$watermark = false;

        if(isset($_POST['remote'])){
            $body = $this->remotepic($body,true,$aid);
            $body = $this->remotepic($body,true,$aid);
            $body = $this->remotepic($body,true,$aid);
            if($body && $id){
                iDB::update('article_data',array('body'=>$body),array('id'=>$id));
            }
        }

        if(isset($_POST['autopic']) && empty($haspic)){
            $picurl = $this->remotepic($body,'autopic',$aid);
            $this->pic($picurl,$aid);
        }
    }
    function pic($picurl,$aid){
        $uri = parse_url(iCMS_FS_URL);
        if (stripos($picurl,$uri['host']) !== false){
            $picdata = (array)articleTable::value('picdata',$aid);
            $picdata && $picdata = @unserialize($picdata);
            $pic = iFS::fp($picurl,'-http');
            list($width, $height, $type, $attr) = @getimagesize(iFS::fp($pic,'+iPATH'));
            $picdata['b'] = array('w'=>$width,'h'=>$height);
            $picdata = addslashes(serialize($picdata));
            $haspic  = 1;
            articleTable::update(compact('haspic','pic','picdata'),array('id'=>$aid));
        }
    }
    function remotepic($content,$remote = false,$aid=0) {
        if (!$remote) return $content;

        iFS::$forceExt = "jpg";
        $content = stripslashes($content);
        preg_match_all("/<img.*?src\s*=[\"|'](.*?)[\"|']/is", $content, $match);
        $array  = array_unique($match[1]);
        $uri    = parse_url(iCMS_FS_URL);
        $fArray = array();
        $fpArray= array();
        foreach ($array as $key => $value) {
            $value = trim($value);
            if (stripos($value,$uri['host']) === false){
                $filepath = iFS::http($value);
                if($filepath){
                    if($aid){
                        $filename = basename($filepath);
                        $filename = substr($filename,0, 32);
                        $faid     = articleTable::filedata_value($filename);
                        empty($faid) && articleTable::filedata_update_indexid($aid,$filename);
                    }
                    $value        = iFS::fp($filepath, '+http');
                    $fArray[$key] = $value;
                }
            }else{
                unset($array[$key]);
            }
            if($remote==="autopic" && $key==0){
                return $value;
            }
        }
        if($remote==="autopic" && empty($array)){
            return;
        }
        $array && $fArray && $content = str_replace($array, $fArray, $content);
        return addslashes($content);
    }
    function picdata($pic='',$mpic='',$spic=''){
        $picdata = array();
        if($pic){
            list($width, $height, $type, $attr) = @getimagesize(iFS::fp($pic,'+iPATH'));
            $picdata['b'] = array('w'=>$width,'h'=>$height);
        }
        if($mpic){
            list($width, $height, $type, $attr) = @getimagesize(iFS::fp($mpic,'+iPATH'));
            $picdata['m'] = array('w'=>$width,'h'=>$height);
        }
        if($spic){
            list($width, $height, $type, $attr) = @getimagesize(iFS::fp($spic,'+iPATH'));
            $picdata['s'] = array('w'=>$width,'h'=>$height);
        }
        return $picdata?addslashes(serialize($picdata)):'';
    }

    function do_purge(){
        //$id  = sprintf("%08s",$_GET['id']);
        $url = str_replace('/article/','/~cc/article/',$_GET['url']);
		echo $this->fopen_url($url);

		for($i=2;$i<50;$i++){
            // $url = "http://www.OOXX.com/~cc/article/".$id."_".$i.".shtml";
            // $str = $this->fopen_url($url);
			// if(!strstr($str,"Successful purge")){
			// 	break;
			// }else{
			// 	echo $str;
			// }
    	}
    }
	function fopen_url($url) {
		$uri=parse_url($url);
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT,2);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl_handle, CURLOPT_FAILONERROR,1);
		curl_setopt($curl_handle, CURLOPT_REFERER,$uri['scheme'].'://'.$uri['host']);
		curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/532.0 (KHTML, like Gecko) Chrome/3.0.195.38 Safari/532.0');
		$file_content = curl_exec($curl_handle);
		curl_close($curl_handle);
		return $file_content;
	}
}
