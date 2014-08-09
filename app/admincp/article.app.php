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
define('TAG_APPID',iCMS_APP_ARTICLE);
class articleApp{
    function __construct() {
        $this->id          = (int)$_GET['id'];
        $this->dataid      = (int)$_GET['dataid'];
        $this->categoryApp = iACP::app('category',iCMS_APP_ARTICLE);
        $this->category    = $this->categoryApp->category;
    }
    function do_add(){
        $_GET['cid'] && iACP::CP($_GET['cid'],'ca','page');//添加权限
        $rs      = array();
        if($this->id){
            $rs        = iDB::row("SELECT * FROM `#iCMS@__article` WHERE `id`='$this->id' LIMIT 1;",ARRAY_A);
            $adsql     = $this->dataid?" and `id`='{$this->dataid}'":'';
            $adRs      = iDB::row("SELECT * FROM `#iCMS@__article_data` WHERE `aid`='$this->id'{$adsql}",ARRAY_A);
            $bodyArray = explode('#--iCMS.PageBreak--#',$adRs['body']);
            $bodyCount = count($bodyArray);
            iACP::CP($rs['cid'],'ce','page');//编辑权限
        }

        $bodyCount OR $bodyCount = 1;
        $cid                 = empty($rs['cid'])?(int)$_GET['cid']:$rs['cid'];
        $cata_option         = $this->categoryApp->select('ca');
        $cid && $contentprop =  unserialize($this->category[$cid]['contentprop']);

        //$metadata          = array_merge((array)$contentprop,(array)$rs['metadata']);
        $rs['pubdate']       = get_date($rs['pubdate'],'Y-m-d H:i:s');
        $rs['metadata'] && $rs['metadata'] = unserialize($rs['metadata']);
        if($rs['tags']{0}.$rs['tags']{1}=='[['){
            $tagsArray   = json_decode($rs['tags']);
            foreach ((array)$tagsArray as $k => $_tag) {
                $_tagArray[] = $_tag[0];
            }
            $rs['tags']=implode(',', (array)$_tagArray);
        }
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
    	$sql	= iACP::iDT($_GET['iDT']);
    	$sql &&	iDB::query("UPDATE `#iCMS@__article` SET $sql WHERE `id` ='$this->id'");
    	iPHP::success('操作成功!','js:1');
    }
    function do_updateorder(){
        foreach((array)$_POST['ordernum'] as $orderNum=>$id){
            iDB::query("UPDATE `#iCMS@__article` SET `orderNum` = '".intval($orderNum)."' WHERE `id` ='".intval($id));
        }
    }
    function do_batch(){
    	$_POST['id'] OR iPHP::alert("请选择要操作的文章");
    	$ids	= implode(',',(array)$_POST['id']);
    	$batch	= $_POST['batch'];
    	switch($batch){
    		case 'order':
		        foreach((array)$_POST['orderNum'] AS $id=>$orderNum) {
		            iDB::query("UPDATE `#iCMS@__article` SET `orderNum` = '$orderNum' WHERE `id` ='$id'");
		        }
		        iPHP::success('排序已更新!','js:1');
    		break;
    		case 'move':
		        $_POST['cid'] OR iPHP::alert("请选择目标栏目!");
                iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
                map::init('category',iCMS_APP_ARTICLE);
                $cid = (int)$_POST['cid'];
                iACP::CP($cid,'ca','alert');
		        foreach((array)$_POST['id'] AS $id) {
                    $_cid = iDB::value("SELECT `cid` FROM `#iCMS@__article` where `id` ='$id'");
		            iDB::query("UPDATE `#iCMS@__article` SET cid='$cid' WHERE `id` ='$id'");
		            if($_cid!=$cid) {
                        map::diff($cid,$_cid,$id);
		                iDB::query("UPDATE `#iCMS@__category` SET `count` = count-1 WHERE `cid` ='{$_cid}' AND `count`>0");
		                iDB::query("UPDATE `#iCMS@__category` SET `count` = count+1 WHERE `cid` ='{$cid}'");
		            }
		        }
		        iPHP::success('成功移动到目标栏目!','js:1');
            break;
            case 'scid':
                //$_POST['scid'] OR iPHP::alert("请选择目标栏目!");
                iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
                map::init('category',iCMS_APP_ARTICLE);
                $scid = implode(',', (array)$_POST['scid']);
                foreach((array)$_POST['id'] AS $id) {
                    $_scid = iDB::value("SELECT `scid` FROM `#iCMS@__article` where `id` ='$id'");
                    iDB::query("UPDATE `#iCMS@__article` SET `scid`='$scid' WHERE `id` ='$id'");
                    map::diff($scid,$_scid,$id);
                }
                iPHP::success('文章副栏目设置完成!','js:1');
            break;
            case 'prop':
                // $pid = $_POST['pid'];
                // $sql ="`pid` = '$pid'";

                iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
                map::init('prop',iCMS_APP_ARTICLE);
                
                $pid = implode(',', (array)$_POST['pid']);
                foreach((array)$_POST['id'] AS $id) {
                    $_pid = iDB::value("SELECT `pid` FROM `#iCMS@__article` where `id` ='$id'");
                    iDB::query("UPDATE `#iCMS@__article` SET `pid`='$pid' WHERE `id` ='$id'");
                    map::diff($pid,$_pid,$id);
                }
                iPHP::success('文章属性设置完成!','js:1');

    		break;
    		case 'top':
                $top =_int($_POST['mtop']);
                $sql ="`top` = '$top'";
    		break;
    		case 'keyword':
    			if($_POST['pattern']=='replace') {
    				$sql	="`keywords` = '".iS::escapeStr($_POST['mkeyword'])."'";
    			}elseif($_POST['pattern']=='addto') {
		        	foreach($_POST['id'] AS $id){
                        $keywords = iDB::value("SELECT keywords FROM `#iCMS@__article` WHERE `id`='$id'");
                        $sql      ="`keywords` = '".($keywords?$keywords.','.iS::escapeStr($_POST['mkeyword']):iS::escapeStr($_POST['mkeyword']))."'";
				        iDB::query("UPDATE `#iCMS@__article` SET {$sql} WHERE `id`='$id'");
		        	}
		        	iPHP::success('文章关键字更改完成!','js:1');
    			}
    		break;
    		case 'tag':
    			iPHP::appClass('tag','import');
		     	foreach($_POST['id'] AS $id){
		    		$art=iDB::row("SELECT tags,cid FROM `#iCMS@__article` WHERE `id`='$id' LIMIT 1;");
			        if($_POST['pattern']=='replace') {
			        	$tags=iS::escapeStr($_POST['mtag']);
			        }elseif($_POST['pattern']=='addto') {
			        	$tags=$art->tags?$art->tags.','.iS::escapeStr($_POST['mtag']):iS::escapeStr($_POST['mtag']);
			        }
			        tag::diff($tags,$art->tags,iMember::$userid,$id,$this->categoryApp->rootid($art->cid));
			 		iDB::query("UPDATE `#iCMS@__article` SET `tags` = '$tags' WHERE `id`='$id'");
		    	}
		    	iPHP::success('文章标签更改完成!','js:1');
    		break;
    		case 'thumb':
		        foreach((array)$_POST['id'] AS $id) {
		            $content	= iDB::value("SELECT body FROM `#iCMS@__article_data` WHERE aid='$id'");
		            $img 	= array();
		            preg_match_all("/<img.*?src\s*=[\"|'|\s]*(http:\/\/.*?\.(gif|jpg|jpeg|bmp|png)).*?>/is",$content,$img);
		            $_array = array_unique($img[1]);
		            foreach($_array as $key =>$value) {
		                $value = iFS::fp($value,'http2iPATH');
		                if(file_exists($value)) {
							list($width, $height, $type, $attr) = getimagesize($value);
                            empty($width) && $width   = 0;
                            empty($height) && $height = 0;
		                    $value = iFS::fp($value,'-iPATH');
		                    iDB::query("UPDATE `#iCMS@__article` SET `isPic`='1',`pic` = '$value',`picwidth`='$width',`picheight`='$height' WHERE `id` = '$id'");
		                    break;
		                }
		            }
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
				iPHP::success('文章全部删除完成!','js:1');
    		break;
    		default:
				$sql	= iACP::iDT($batch);
    	}
		iDB::query("UPDATE `#iCMS@__article` SET {$sql} WHERE `id` IN ($ids)");
		iPHP::success('操作成功!','js:1');
    }
    function do_getjson(){
        $id = (int)$_GET['id'];
        $rs = iDB::row("SELECT * FROM `#iCMS@__article` WHERE id='$id' LIMIT 1;",ARRAY_A);
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

		$art	= iDB::row("SELECT `cid`,`tags` FROM `#iCMS@__article` where `id` ='$id' LIMIT 1;");
		if($tags){
			iPHP::appClass('tag','import');
			tag::diff($tags,$art->tags,iMember::$userid,$id,$this->categoryApp->rootid($art->cid));
		}

		if($_POST['status']=="1"){
			$updatesql=",`status`='1'";
		}
		if($_POST['statustime']=="1"){
			$updatesql=",`status`='1',`pubdate`='".time()."'";
		}
		iDB::query("UPDATE `#iCMS@__article` SET `cid` = '$cid',`pid` = '$pid',`title` = '$title',`tags` = '$tags',`description` = '$description' {$updatesql} WHERE `id` ='$id'");
		exit('1');
	}
    function do_preview(){
		$rs	= iDB::row("SELECT a.*,d.body,d.subtitle FROM `#iCMS@__article` as a LEFT JOIN `#iCMS@__article_data` AS d ON a.id = d.aid WHERE a.id='{$this->id}' LIMIT 1;");
		echo $rs->body;
    }
    function do_iCMS(){
    	iACP::$app_do="manage";
    	$this->do_manage();
    }
    function do_inbox(){
    	$this->do_manage("inbox");
    }
    function do_examine(){
    	$this->do_manage("examine");
    }
    function do_off(){
    	$this->do_manage("off");
    }
    function do_trash(){
    	$this->do_manage("trash");
    }

    function do_manage() {
        $cid = (int)$_GET['cid'];
        $pid = $_GET['pid'];
        $pt  = (int)$_GET['pt'];
        $pt OR $pt = 1;
        $ptsql = " AND `postype`='{$pt}'";
        $sql = " where ";
        switch(iACP::$app_do){ //postype: [0:用户][1:管理员][5:用户淘宝类文章] status:[0:草稿][1:正常][2:回收][3:审核][4:不合格]
        	case 'inbox'://草稿
                $status   = '0';
                $position = "草稿";
        	break;
         	case 'trash'://回收站
                $status   = '2';
                $position = "回收站";
        	break;
         	case 'examine'://审核
                $status   = '3';
                $position = "已审核";
        	break;
         	case 'off'://未通过
                $status   = '4';
                $position = "未通过";
        	break;
       		default:
	       		$status = '1';
		       	$cid && $position = $this->category[$cid]['name'];
		}
        $_GET['pt'] OR $ptsql = '';
        $sql.="`status` ='$status'".$ptsql;

        if(iACP::MP("ARTICLE.VIEW")){
            $_GET['userid'] && $sql.= iPHP::where($_GET['userid'],'userid');
        }else{
            $sql.= iPHP::where(iMember::$userid,'userid');
        }

        if(isset($_GET['pid']) && $pid!='-1'){
            iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
            map::init('prop',iCMS_APP_ARTICLE);
            $sql.= map::exists($pid,'`#iCMS@__article`.id'); //map 表大的用exists
            $uri_array['pid'] = $pid;
        }

        $cids = iACP::CP('__CID__','cs');//取得所有有权限的栏目ID

        if($cid) {
            $cids  = $cid;
            if($_GET['sub']){
                $cids  = $this->categoryApp->get_ids($cid,true);
                array_push ($cids,$cid);
            }
        }
      
        if($_GET['scid']){
            if($cids){
                iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
                map::init('category',iCMS_APP_ARTICLE);
                $sql.= map::exists($cids,'`#iCMS@__article`.id'); //map 表大的用exists
            }                
        }else{
            $cids OR $cids ='-1';
            $sql.= iPHP::where($cids,'cid');
        }

        if($_GET['keywords']) {
            $kws = $_GET['keywords'];
            switch ($_GET['st']) {
                case "title": $sql.=" AND `title` REGEXP '{$kws}'";break;
                case "tag":   $sql.=" AND `tags` REGEXP '{$kws}'";break;
                case "source":$sql.=" AND `source` REGEXP '{$kws}'";break;
                case "top":   $sql.=" AND `top`='{$kws}'";break;
                case "id":    $sql.=" AND `id` REGEXP '{$kws}'";break;
                case "tkd":   $sql.=" AND CONCAT(title,keywords,description) REGEXP '{$kws}'";break;
            }
        }
        $_GET['title']     && $sql.=" AND `title` like '%{$_GET['title']}%'";
        $_GET['tag']       && $sql.=" AND `tags` REGEXP '[[:<:]]".preg_quote(rawurldecode($_GET['tag']),'/')."[[:>:]]'";
        $_GET['starttime'] && $sql.=" and `pubdate`>=UNIX_TIMESTAMP('".$_GET['starttime']." 00:00:00')";
        $_GET['endtime']   && $sql.=" and `pubdate`<=UNIX_TIMESTAMP('".$_GET['endtime']." 23:59:59')";
        isset($_GET['pic'])&& $sql.=" AND `isPic` ='".($_GET['pic']?1:0)."'";
        
        isset($_GET['userid']) && $uri_array['userid']	 = (int)$_GET['userid'];
        isset($_GET['keyword'])&& $uri_array['keyword'] = $_GET['keyword'];
        isset($_GET['tag'])    && $uri_array['tag']	 = $_GET['tag'];
        isset($_GET['pt'])     && $uri_array['pt']		 = $_GET['pt'];
        isset($_GET['cid'])    && $uri_array['cid']     = $_GET['cid'];
		$uri_array	&& $uri = http_build_query($uri_array);

        $orderby	= $_GET['orderby']?$_GET['orderby']:"id DESC";
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total		= iPHP::total(false,"SELECT count(*) FROM `#iCMS@__article` {$sql}","G");
        iPHP::pagenav($total,$maxperpage,"篇文章");
        $rs     = iDB::all("SELECT * FROM `#iCMS@__article` {$sql} order by {$orderby} LIMIT ".iPHP::$offset." , {$maxperpage}");
        // iDB::debug(1);
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
        $orderNum    = _int($_POST['orderNum']);
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
        $tags        = iS::escapeStr($_POST['tags']);
        $clink       = iS::escapeStr($_POST['clink']);
        $url         = iS::escapeStr($_POST['url']);
        $tpl         = iS::escapeStr($_POST['tpl']);
        $metadata    = iS::escapeStr($_POST['metadata']);
        $metadata    = $metadata?addslashes(serialize($metadata)):'';
        $body        = (array)$_POST['body'];

        iACP::CP($cid,($aid?'ce':'ca'),'alert');

        $top         = _int($_POST['top']);
        $pubdate     = iPHP::str2time($_POST['pubdate']);

        $userid OR $userid = iMember::$userid;
        $postype   = $_POST['postype']?$_POST['postype']:0;
        $ischapter = isset($_POST['ischapter'])?1:0;
        isset($_POST['inbox'])	&&  $status = "0";

        empty($title)&& iPHP::alert('标题不能为空！');
        empty($cid)  && iPHP::alert('请选择所属栏目');
        empty($body) && empty($url) && iPHP::alert('文章内容不能为空！');

        if(empty($aid) && iCMS::$config['publish']['repeatitle']) {
			iDB::value("SELECT `id` FROM `#iCMS@__article` where `title` = '$title'") && iPHP::alert('该标题的文章已经存在!请检查是否重复');
		}
        
        // if(!iACP::MP("ARTICLE.EDIT") && $userid!=iMember::$userid){
        //     iPHP::alert('您没有权限编辑此文章');
        // }

        if(iCMS::$config['publish']['autodesc'] && iCMS::$config['publish']['descLen'] && empty($description) && empty($url)) {
            $bodyText    = implode("\n",$body);
            $bodyText    = str_replace('#--iCMS.PageBreak--#',"\n",$bodyText);
            $bodyText    = preg_replace(array('/<p[^>]*>/is','/<[\/\!]*?[^<>]*?>/is',"/\n+/","/　+/","/^\n/"),array("\n\n",'',"\n",'',''),$bodyText);
            $description = csubstr($bodyText,iCMS::$config['publish']['descLen']);
            unset($bodyText);
        }

        strstr($pic, 'http://') && $pic   = iFS::http($pic);
        strstr($mpic, 'http://') && $mpic = iFS::http($mpic);
        strstr($spic, 'http://') && $spic = iFS::http($spic);

        $isPic   = empty($pic)?0:1;

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
        if(empty($aid)) {
            $postime = $pubdate;
            $hits    = $good = $bad = $comments = 0;
            $ischapter && $chapter = 1;

            if($tags){
                iPHP::appClass('tag','import');
                $tagArray = tag::add($tags,$userid,$aid,$this->categoryApp->rootid($cid));
                $tags = addslashes(json_encode($tagArray));
            }
            $fields = array('cid','scid','orderNum', 'title', 'stitle', 'clink', 'url', 'source', 'author', 'editor', 'userid', 'pic','mpic','spic', 'picdata', 'keywords', 'tags', 'description', 'related', 'metadata', 'pubdate', 'postime', 'hits', 'comments', 'good', 'bad', 'chapter', 'pid', 'top', 'postype', 'tpl', 'status', 'isPic');
            $data   = compact ($fields);            
            $aid    = iDB::insert('article',$data);

            map::init('prop',iCMS_APP_ARTICLE);
            map::add($pid,$aid);

            map::init('category',iCMS_APP_ARTICLE);
            map::add($cid,$aid);
            map::add($scid,$aid);

            $tagArray && tag::map_iid($tagArray,$aid);

            $url OR $this->article_data($body,$aid);
            iDB::query("UPDATE `#iCMS@__category` SET `count` = count+1 WHERE `cid` ='$cid'");
            if($callback){
            	return array("code"=>$callback,'indexId'=>$aid);
            }
            $moreBtn = array(
                    array("text" =>"查看该文章","url"=>iURL::get('article',array(array('id'=>$aid,'url'=>$url,'cid'=>$cid,'pubdate'=>$pubdate),$this->category[$cid]))->href,"o"=>'target="_blank"'),
                    array("text" =>"编辑该文章","url"=>APP_URI."&do=add&id=".$aid),
                    array("text" =>"继续添加文章","url"=>APP_URI."&do=add&cid=".$cid),
                    array("text" =>"返回文章列表","url"=>$SELFURL),
                    array("text" =>"查看网站首页","url"=>"../index.php","o"=>'target="_blank"')
            );
            iPHP::$dialogLock	= true;
            iPHP::dialog('success:#:check:#:文章添加完成!<br />10秒后返回文章列表','url:'.$SELFURL,10,$moreBtn);
        }else{
			if($tags){
				iPHP::appClass('tag','import');
	            $tags = tag::diff($tags,$_tags,iMember::$userid,$aid,$this->categoryApp->rootid($cid));
			    $tags = addslashes($tags);
            }
            $picdata = $this->picdata($pic,$mpic,$spic);
            $fields  = array('cid', 'scid', 'orderNum', 'title', 'stitle', 'clink', 'url', 'source', 'author', 'editor', 'userid', 'pic','mpic','spic', 'picdata','keywords', 'tags', 'description', 'related', 'metadata', 'pubdate', 'chapter', 'pid', 'top', 'postype', 'tpl','status', 'isPic');
            $data    = compact ($fields);
            iDB::update('article', $data, array('id'=>$aid));

            map::init('prop',iCMS_APP_ARTICLE);
            map::diff($pid,$_pid,$aid);

            map::init('category',iCMS_APP_ARTICLE);
            map::diff($cid,$_cid,$aid);
            map::diff($scid,$_scid,$aid);

            $url OR $this->article_data($body,$aid);

            //$ischapter && $this->chapterCount($aid);
            if($_cid!=$cid) {
                iDB::query("UPDATE `#iCMS@__category` SET `count` = count-1 WHERE `cid` ='{$_cid}' and `count`>0");
                iDB::query("UPDATE `#iCMS@__category` SET `count` = count+1 WHERE `cid` ='$cid'");
            }

            if(!strstr($this->category[$cid]['contentRule'],'{PHP}')&&!$this->category[$cid]['url']&&$this->category[$cid]['mode']=="1" && $status) {
				$htmlApp = iACP::app('html');
				$htmlApp->Article($aid);
			}
            iPHP::success('文章编辑完成!<br />3秒后返回文章列表','url:'.$SELFURL);
        }
    }
    function do_del(){
        $msg = $this->delArticle($this->id);
        $msg.= $this->del_msg('文章删除完成!');
        $msg.= $this->del_msg('10秒后返回文章列表!');
        iPHP::$dialogLock   = true;
        iPHP::dialog($msg,'js:1');
    }
    function del_msg($str){
        return iPHP::msg('success:#:check:#:'.$str,true);
    }
    function del_pic($pic){
        //$thumbfilepath    = gethumb($pic,'','',false,true,true);
        iFS::del(iFS::fp($pic,'+iPATH'));
        $msg    = $this->del_msg($pic.'删除');
//      if($thumbfilepath)foreach($thumbfilepath as $wh=>$fp) {
//              iFS::del(iFS::fp($fp,'+iPATH'));
//              $msg.= $this->del_msg('缩略图 '.$wh.' 文件删除');
//      }
        $filename   =iFS::info($pic)->filename;
        iDB::query("DELETE FROM `#iCMS@__filedata` WHERE `filename` = '{$filename}'");
        $msg.= $this->del_msg($pic.'数据删除');
        return $msg;
    }
    function delArticle($id,$uid='0',$postype='1') {
        $id = (int)$id;
        $id OR iPHP::alert("请选择要删除的文章");
        $uid && $sql="and `userid`='$uid' and `postype`='$postype'";
        $art = iDB::row("SELECT * FROM `#iCMS@__article` WHERE id='$id' {$sql} LIMIT 1;");
        iACP::CP($art->cid,'cd','alert');
        if($art->pic) {
            $usePic = iDB::value("SELECT id FROM `#iCMS@__article` WHERE `pic`='{$art->pic}' and `id`<>'$id'");
           if(empty($usePic)) {
                $msg.= $this->del_pic($art->pic);
            }else {
                $msg.= $this->del_msg($art->pic.' 其它文章正在使用,请到文件管理删除');
            }
        }
        $frs    = iDB::all("SELECT `filename`,`path`,`ext` FROM `#iCMS@__filedata` WHERE `indexid`='$id'");
        for($i=0;$i<count($frs);$i++) {
            if($frs[$i]){
                $path   = $frs[$i]['path'].'/'.$frs[$i]['filename'].'.'.$frs[$i]['ext'];
                iFS::del(iFS::fp($frs[$i]['path'],'+iPATH'));
                $msg.=$this->del_msg($path.' 文件删除');
            }
        }
        if($art->tags){
            iPHP::appClass('tag','import');
            $msg.=tag::del($art->tags);
        }
        iDB::query("DELETE FROM `#iCMS@__filedata` WHERE `indexid`='$id'");
        $msg.= $this->del_msg('相关文件数据删除');
        iDB::query("DELETE FROM `#iCMS@__comment` WHERE iid='$id' and appid='".iCMS_APP_ARTICLE."'");
        $msg.= $this->del_msg('评论数据删除');
        iDB::query("DELETE FROM `#iCMS@__article` WHERE id='$id'");
        iDB::query("DELETE FROM `#iCMS@__article_data` WHERE `aid`='$id'");
        $msg.= $this->del_msg('文章数据删除');
        iDB::query("UPDATE `#iCMS@__category` SET `count` = count-1 WHERE `cid` ='{$art->cid}' and `count`>0");
        $msg.= $this->del_msg('栏目数据更新');
        $msg.= $this->del_msg('删除完成');
        return $msg;
    }

    function article_data($bodyArray,$aid=0,$id=0){
        $id       = (int)$_POST['adid'];
        $subtitle = iS::escapeStr($_POST['subtitle']);
        $body     = implode('#--iCMS.PageBreak--#',$bodyArray);
        $body     = preg_replace(array('/<script.+?<\/script>/is','/<form.+?<\/form>/is'),'',$body);

        $autopic  = isset($_POST['autopic']) ?true:false;
        $remote   = isset($_POST['remote']) ?true:false;
        $dellink  = isset($_POST['dellink']) ?true:false;
        $_POST['isRedirect']  && iFS::$isRedirect = true;
        $_POST['iswatermark'] && $GLOBALS['iCONFIG']['watermark']['enable'] = false;
        $dellink && $body   = preg_replace("/<a[^>].*?>(.*?)<\/a>/si", "\\1",$body);

        iFS::remotepic($body,$remote,$autopic);
        iFS::remotepic($body,$remote,$autopic);
        iFS::remotepic($body,$remote,$autopic);

        if($id){
            $sql = "UPDATE `#iCMS@__article_data` SET `subtitle` = '$subtitle', `body` = '$body' WHERE `id` = '$id';";
        }else{
            $sql = "INSERT INTO `#iCMS@__article_data` (`aid`, `subtitle`, `body`) VALUES ('$aid', '$subtitle', '$body');";
        }
        iDB::query($sql);
        $this->insert_db_pic($body,$aid,$autopic);
    }
    function chapterCount($aid){
        $count = iDB::value("SELECT count(id) FROM `#iCMS@__article_data` where `aid` = '$aid'");
        iDB::query("UPDATE `#iCMS@__article` SET `chapter`='$count'  WHERE `id` = '$aid'");
    }
    function insert_db_pic($content,$aid,$autopic) {
        $content = stripslashes($content);
        preg_match_all("/<img.*?src\s*=[\"|'](.*?)[\"|']/is",$content,$match);
        $_array = array_unique($match[1]);
        foreach($_array as $key =>$value) {
            $_value   = iFS::fp($value,'-http');
            $filename = basename($_value);
            $filename = substr($filename,0, 32);
            $pic      = iDB::value("SELECT `pic` FROM `#iCMS@__article` WHERE `id` = '$aid'");
            if($autopic && $key==0 && empty($pic)){
				$uri  = parse_url(iCMS::$config['FS']['url']);
	            if(strstr(strtolower($value),$uri['host'])){
                   $picdata = $this->picdata($_value);
	               iDB::query("UPDATE `#iCMS@__article` SET `isPic`='1',`pic` = '$_value',`picdata` = '$picdata' WHERE `id` = '$aid'");
                }
            }
            $faid = iDB::value("SELECT `indexid` FROM `#iCMS@__filedata` WHERE `filename` ='$filename'");
            empty($faid) && iDB::query("UPDATE `#iCMS@__filedata` SET `indexid` = '$aid' WHERE `filename` ='$filename'");
        }
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
		$id	= sprintf("%08s",$_GET['id']);
		$url= str_replace('/article/','/~cc/article/',$_GET['url']);
		echo $this->fopen_url($url);

		for($i=2;$i<50;$i++){
			$url	="http://www.ladyband.com/~cc/article/".$id."_".$i.".shtml";
			$str	=$this->fopen_url($url);
			if(!strstr($str,"Successful purge")){
				break;
			}else{
				echo $str;
			}
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
