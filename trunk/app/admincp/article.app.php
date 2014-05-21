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
class articleApp{
    function __construct() {
        $this->id       = (int)$_GET['id'];
        $this->dataid   = (int)$_GET['dataid'];
        $this->category = iPHP::appClass("category",iCMS_APP_ARTICLE);
        define('TAG_APPID',iCMS_APP_ARTICLE);
    }
    function doadd(){
        $rs      = array();      
        if($this->id){
            $rs        = iDB::getRow("SELECT * FROM `#iCMS@__article` WHERE `id`='$this->id' LIMIT 1;",ARRAY_A);
            $adsql     = $this->dataid?" and `id`='{$this->dataid}'":'';
            $adRs      = iDB::getRow("SELECT * FROM `#iCMS@__article_data` WHERE `aid`='$this->id'{$adsql}",ARRAY_A);
            $bodyArray = explode('#--iCMS.PageBreak--#',$adRs['body']);
            $bodyCount = count($bodyArray);
        }
        $bodyCount OR $bodyCount = 1;
        $cid                 = empty($rs['cid'])?(int)$_GET['cid']:$rs['cid'];
        $cata_option         = $this->category->select($cid,0,1,0);
        $cid && $contentprop =  unserialize($this->category->category[$cid]['contentprop']);

        //$metadata          = array_merge((array)$contentprop,(array)$rs['metadata']);
        $rs['pubdate']       = get_date($rs['pubdate'],'Y-m-d H:i:s');
        $rs['metadata'] && $rs['metadata'] = unserialize($rs['metadata']);
        if($rs['tags']{0}.$rs['tags']{1}=='[['){
            $tagsArray   = json_decode($rs['tags']);
            foreach ((array)$tagsArray as $k => $_tag) {
                $_tagArray[]=$_tag[0];
            }
            $rs['tags']=implode(',', (array)$_tagArray);
        }
        if(empty($this->id)){
            $rs['status']  = "1";
            $rs['postype'] = "1";
            $rs['editor']  = empty(iMember::$Rs->nickname)?iMember::$Rs->username:iMember::$Rs->nickname;
            $rs['userid']  = iMember::$uId;
		}
        $strpos   = strpos(__REF__,'?');
        $REFERER  = $strpos===false?'':substr(__REF__,$strpos);
        $defArray = iCache::get('iCMS/defaults');
    	include iACP::tpl("article.add");
    }
    function doupdate(){
    	$sql	= iACP::iDT($_GET['iDT']);
    	$sql &&	iDB::query("UPDATE `#iCMS@__article` SET $sql WHERE `id` ='$this->id'");
    	iPHP::OK('操作成功!','js:1');
    }
    function doupdateorder(){
        foreach((array)$_POST['ordernum'] as $orderNum=>$id){
            iDB::query("UPDATE `#iCMS@__article` SET `orderNum` = '".intval($orderNum)."' WHERE `id` ='".intval($id));
        }
    }
    function dobatch(){
    	$_POST['id'] OR iPHP::alert("请选择要操作的文章");
    	$ids	= implode(',',(array)$_POST['id']);
    	$batch	= $_POST['batch'];
    	switch($batch){
    		case 'order':
		        foreach((array)$_POST['orderNum'] AS $id=>$orderNum) {
		            iDB::query("UPDATE `#iCMS@__article` SET `orderNum` = '$orderNum' WHERE `id` ='$id'");
		        }
		        iPHP::OK('排序已更新!','js:1');
    		break;
    		case 'move':
		        $_POST['cid'] OR iPHP::alert("请选择目标栏目!");
		        $cid	=(int)$_POST['cid'];
		        foreach((array)$_POST['id'] AS $id) {
		            $ocid	= iDB::getValue("SELECT `cid` FROM `#iCMS@__article` where `id` ='$id'");
		            iDB::query("UPDATE `#iCMS@__article` SET cid='$cid' WHERE `id` ='$id'");
		            if($ocid!=$cid) {
		                iDB::query("UPDATE `#iCMS@__category` SET `count` = count-1 WHERE `cid` ='{$ocid}' AND `count`>0");
		                iDB::query("UPDATE `#iCMS@__category` SET `count` = count+1 WHERE `cid` ='{$cid}'");
		            }
		        }
		        iPHP::OK('成功移动到目标栏目!','js:1');
    		break;
    		case 'prop':
                $pid = $_POST['pid'];
                $sql ="`pid` = '$pid'";
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
		        		$keywords	= iDB::getValue("SELECT keywords FROM `#iCMS@__article` WHERE `id`='$id'");
		        		$sql		="`keywords` = '".($keywords?$keywords.','.iS::escapeStr($_POST['mkeyword']):iS::escapeStr($_POST['mkeyword']))."'";
				        iDB::query("UPDATE `#iCMS@__article` SET {$sql} WHERE `id`='$id'");
		        	}
		        	iPHP::OK('文章关键字更改完成!','js:1');
    			}
    		break;
    		case 'tag':
    			iPHP::appClass("tag",'break');
		     	foreach($_POST['id'] AS $id){
		    		$art=iDB::getRow("SELECT tags,cid FROM `#iCMS@__article` WHERE `id`='$id' LIMIT 1;");
			        if($_POST['pattern']=='replace') {
			        	$tags=iS::escapeStr($_POST['mtag']);
			        }elseif($_POST['pattern']=='addto') {
			        	$tags=$art->tags?$art->tags.','.iS::escapeStr($_POST['mtag']):iS::escapeStr($_POST['mtag']);
			        }
			        tag::diff($tags,$art->tags,iMember::$uId,$id,$this->category->rootid($art->cid));
			 		iDB::query("UPDATE `#iCMS@__article` SET `tags` = '$tags' WHERE `id`='$id'");
		    	}
		    	iPHP::OK('文章标签更改完成!','js:1');
    		break;
    		case 'thumb':
		        foreach((array)$_POST['id'] AS $id) {
		            $content	= iDB::getValue("SELECT body FROM `#iCMS@__article_data` WHERE aid='$id'");
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
		        iPHP::OK('成功提取缩略图!','js:1');
    		break;
    		case 'dels':
    			iPHP::$break	= false;
    			ob_implicit_flush();
    			$_count	= count($_POST['id']);
				foreach((array)$_POST['id'] AS $i=>$id) {
			     	$msg= $this->delArticle($id);
			        $msg.= $this->msg('文章删除完成!');
					$updateMsg	= $i?true:false;
					$timeout	= ($i++)==$_count?'3':false;
					iPHP::dialog($msg,'js:parent.$("#id'.$id.'").remove();',$timeout,0,$updateMsg);
		        	ob_end_flush();
	   			}
	   			iPHP::$break	= true;
				iPHP::OK('文章全部删除完成!','js:1');
    		break;
    		default:
				$sql	= iACP::iDT($batch);
    	}
		iDB::query("UPDATE `#iCMS@__article` SET {$sql} WHERE `id` IN ($ids)");
		iPHP::OK('操作成功!','js:1');
    }
    function dogetjson(){
		$id		= (int)$_GET['id'];
		$rs		= iDB::getRow("SELECT * FROM `#iCMS@__article` WHERE id='$id' LIMIT 1;",ARRAY_A);
		
    }
    function dogetmeta(){
        $cid = $_GET['cid'];
        $cid && $contentprop =  unserialize($this->category->category[$cid]['contentprop']);
        iPHP::json($contentprop);
    }
	function doupdatetitle(){
        $id          = (int)$_POST['id'];
        $cid         = (int)$_POST['cid'];
        $pid         = (int)$_POST['pid'];
        $source      = iS::escapeStr($_POST['source']);
        $title       = iS::escapeStr($_POST['title']);
        $tags        = iS::escapeStr($_POST['tags']);
        $description = iS::escapeStr($_POST['description']);
		
		$art	= iDB::getRow("SELECT `cid`,`tags` FROM `#iCMS@__article` where `id` ='$id' LIMIT 1;");
		if($tags){
			iPHP::appClass("tag",'break');
			tag::diff($tags,$art->tags,iMember::$uId,$id,$this->category->rootid($art->cid));
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
    function dopreview(){
		$rs	= iDB::getRow("SELECT a.*,d.body,d.subtitle FROM #iCMS@__article as a LEFT JOIN #iCMS@__article_data AS d ON a.id = d.aid WHERE a.id='{$this->id}' LIMIT 1;");
		echo $rs->body;
    }
    function doiCMS(){
    	iACP::$app_do="manage";
    	$this->domanage();
    }
    function doinbox(){
    	$this->domanage("inbox");
    }
    function doexamine(){
    	$this->domanage("examine");
    }
    function dooff(){
    	$this->domanage("off");
    }
    function dotrash(){
    	$this->domanage("trash");
    }
    function msg($str){
    	return iPHP::msg('success:#:check:#:'.$str,true);
    }
	function delpic($pic){
	    //$thumbfilepath	= gethumb($pic,'','',false,true,true);
	    iFS::del(iFS::fp($pic,'+iPATH'));
	    $msg	= $this->msg($pic.'删除');
//	    if($thumbfilepath)foreach($thumbfilepath as $wh=>$fp) {
//	            iFS::del(iFS::fp($fp,'+iPATH'));
//	            $msg.= $this->msg('缩略图 '.$wh.' 文件删除');
//	    }
	    $filename	=iFS::info($pic)->filename;
	    iDB::query("DELETE FROM `#iCMS@__filedata` WHERE `filename` = '{$filename}'");
	    $msg.= $this->msg($pic.'数据删除');
	    return $msg;
	}
	function delArticle($id,$uid='0',$postype='1') {
	    $id	=(int)$id;
	    $id OR iPHP::alert("请选择要删除的文章");
	    $uid && $sql="and `userid`='$uid' and `postype`='$postype'";
	    $art	= iDB::getRow("SELECT * FROM `#iCMS@__article` WHERE id='$id' {$sql} LIMIT 1;");
	    if($art->pic) {
	        $usePic	= iDB::getValue("SELECT id FROM `#iCMS@__article` WHERE `pic`='{$art->pic}' and `id`<>'$id'");
	       if(empty($usePic)) {
	            $msg.= $this->delpic($art->pic);
	        }else {
	            $msg.= $this->msg($art->pic.' 其它文章正在使用,请到文件管理删除');
	        }
	    }
	    $frs	= iDB::getArray("SELECT `filename`,`path`,`ext` FROM `#iCMS@__filedata` WHERE `indexid`='$id'");
	    for($i=0;$i<count($frs);$i++) {
	        if($frs[$i]){
	        	$path	= $frs[$i]['path'].'/'.$frs[$i]['filename'].'.'.$frs[$i]['ext'];
	            iFS::del(iFS::fp($frs[$i]['path'],'+iPATH'));
	            $msg.=$this->msg($path.' 文件删除');
	        }
	    }
		if($art->tags){
			iPHP::appClass("tag",'break');
            $msg.=tag::del($art->tags);
		}
	    iDB::query("DELETE FROM `#iCMS@__filedata` WHERE `indexid`='$id'");
	    $msg.= $this->msg('相关文件数据删除');
	    iDB::query("DELETE FROM `#iCMS@__comment` WHERE iid='$id' and appid='".iCMS_APP_ARTICLE."'");
	    $msg.= $this->msg('评论数据删除');
	    iDB::query("DELETE FROM `#iCMS@__article` WHERE id='$id'");
	    iDB::query("DELETE FROM `#iCMS@__article_data` WHERE `aid`='$id'");
	    $msg.= $this->msg('文章数据删除');
	    iDB::query("UPDATE `#iCMS@__category` SET `count` = count-1 WHERE `cid` ='{$art->cid}' and `count`>0");
	    $msg.= $this->msg('栏目数据更新');
	    $msg.= $this->msg('删除完成');
	    return $msg;
	}
    function dodel(){
    	$msg = $this->delArticle($this->id);
        $msg.= $this->msg('文章删除完成!');
        $msg.= $this->msg('10秒后返回文章列表!');
        iPHP::$dialogLock	= true;
    	iPHP::dialog($msg,'js:1');
    }
    function domanage($doType=null) {
        $mtime         = microtime();
        $mtime         = explode(' ', $mtime);
        $time_start    = $mtime[1] + $mtime[0];
        $cid           = (int)$_GET['cid'];
        $sql           = " where ";
        $this->postype = (int)$_GET['pt'];
        switch($doType){ //postype: [0:用户][1:管理员][5:用户淘宝类文章] status:[0:草稿][1:正常][2:回收][3:审核][4:不合格]
        	case 'inbox'://草稿
        		$sql.="`status` ='0'".$this->postype();
        		if(iMember::$Rs->gid!=1){
        			$sql.=" AND `userid`='".iMember::$uId."'";
        		}
        		$position="草稿";
        	break;
         	case 'trash'://回收站
        		$sql.="`status` ='2'".$this->postype();
        		$position="回收站";
        	break;
         	case 'examine'://审核
        		$sql.="`status` ='3'".$this->postype();
        		$position="已审核";
        	break;
         	case 'off'://未通过
        		$sql.="`status` ='4'".$this->postype();
        		$position="未通过";
        	break;
       		default:
//	       		$sql.=" `status` ='1'".$this->postype();
	       		$sql.=" `status` ='1'";
	       		$this->postype && $sql.=$this->postype();
	       		
		       	$cid && $position=$this->category->category[$cid]['name'];
		}
		
        if($_GET['keywords']) {
            if($_GET['st']=="title") {
                $sql.=" AND `title` REGEXP '{$_GET['keywords']}'";
            }else if($_GET['st']=="tag") {
                $sql.=" AND `tags` REGEXP '{$_GET['keywords']}'";
            }else if($_GET['st']=="source") {
                $sql.=" AND `source` REGEXP '{$_GET['keywords']}'";
            }else if($_GET['st']=="top") {
                $sql.=" AND `top`='{$_GET['keywords']}'";
            }else if($_GET['st']=="id") {
                $sql.=" AND `id` REGEXP '{$_GET['keywords']}'";
            }else if($_GET['st']=="tkd") {
                $sql.=" AND CONCAT(title,keywords,description) REGEXP '{$_GET['keywords']}'";
            }
        }
        $_GET['title'] 			&& $sql .=" AND `title` like '%{$_GET['title']}%'";
        $_GET['tag'] 			&& $sql .=" AND `tags` REGEXP '[[:<:]]".preg_quote(rawurldecode($_GET['tag']),'/')."[[:>:]]'";
        
        isset($_GET['pid']) && $_GET['pid']!='-1' && $sql.=" AND `pid` ='".$_GET['pid']."'";
        
        if(iMember::$Rs->gid==1){
	        $_GET['userid'] && $sql.=" AND `userid`='".(int)$_GET['userid']."'";
        }else{
        	if(!iMember::MP(array("Allow_Edit_Article","Allow_View_Article"),"F")||$doType=="inbox"||$doType=="trash"){
	         	$sql.=" AND `userid`='".iMember::$uId."'";
	       	}
        }
        $cid	= iMember::CP($cid)?$cid:"0";
        if($cid) {
            $cidIN=$this->category->cid($cid).$cid;
            if(isset($_GET['sub']) && strstr($cidIN,',')) {
                $sql.=" AND cid IN(".$cidIN.")";
            }else {
                $sql.=" AND cid ='$cid'";
            }
            //$sql.=" OR `vlink` REGEXP '[[:<:]]".preg_quote($cid, '/')."[[:>:]]')";
        }else {
            iMember::$cpower && $sql.=" AND cid IN(".implode(',',(array)iMember::$cpower).")";
        }

		isset($_GET['pic']) && $sql.=" AND `isPic` ='".($_GET['pic']?1:0)."'";

        $_GET['starttime'] 	&& $sql.=" and `pubdate`>=UNIX_TIMESTAMP('".$_GET['starttime']." 00:00:00')";
        $_GET['endtime'] 	&& $sql.=" and `pubdate`<=UNIX_TIMESTAMP('".$_GET['endtime']." 23:59:59')";
		$uriA	= array();
        isset($_GET['userid']) 	&& $uriA['userid']	= (int)$_GET['userid'];
        isset($_GET['keyword']) && $uriA['keyword']	= $_GET['keyword'];
        isset($_GET['tag']) 	&& $uriA['tag']		= $_GET['tag'];
        isset($_GET['pt']) 		&& $uriA['pt']		= $_GET['pt'];
        isset($_GET['cid']) 	&& $uriA['cid']		= $_GET['cid'];
        (isset($_GET['pid']) 	&& $_GET['pid']!='-1') && $uriA['pid']=$_GET['pid'];
		$uriA	&& $uri=http_build_query($uriA);

        $orderby	= $_GET['orderby']?$_GET['orderby']:"id DESC";
        $maxperpage = (int)$_GET['perpage']>0?$_GET['perpage']:20;
        $total		= iPHP::total(false,"SELECT count(*) FROM `#iCMS@__article` {$sql}","G");
        iPHP::pagenav($total,$maxperpage,"篇文章");
        $rs			= iDB::getArray("SELECT * FROM `#iCMS@__article` {$sql} order by {$orderby} LIMIT ".iPHP::$offset." , {$maxperpage}");
//echo iDB::$last_query;
//iDB::$last_query='explain '.iDB::$last_query;
//$explain=iDB::getRow(iDB::$last_query);
//var_dump($explain);
        $_count		= count($rs);
        include iACP::tpl("article.manage");
//		$mtime = microtime();
//		$mtime = explode(' ', $mtime);
//		$time_end = $mtime[1] + $mtime[0];
//		echo  "<h1>".($time_end - $time_start);
    }
    function dosave($callback=false){
        $aid         = (int)$_POST['aid'];
        $cid         = (int)$_POST['cid'];
        $userid      = (int)$_POST['userid'];
        $pid         = (int)$_POST['pid'];
        $status      = (int)$_POST['status'];
        $chapter     = (int)$_POST['chapter'];
        $orderNum    = _int($_POST['orderNum']);
        $title       = iS::escapeStr($_POST['title']);
        $stitle      = iS::escapeStr($_POST['stitle']);
        $pic         = iS::escapeStr($_POST['pic']);
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

        $top         = _int($_POST['top']);
        $pubdate     = iPHP::str2time($_POST['pubdate']);       

        $userid OR $userid = iMember::$uId;
        $postype   = $_POST['postype']?$_POST['postype']:0;
        $ischapter = isset($_POST['ischapter'])?1:0;
        isset($_POST['inbox'])	&&  $status = "0";
        
        empty($title)   && iPHP::alert('标题不能为空！');
        empty($cid)     && iPHP::alert('请选择所属栏目');
        empty($body)    && empty($url) && iPHP::alert('文章内容不能为空！');

        if(empty($aid) && iCMS::$config['publish']['repeatitle']) {
			iDB::getValue("SELECT `id` FROM `#iCMS@__article` where `title` = '$title'") && iPHP::alert('该标题的文章已经存在!请检查是否重复');
		}
        if(iMember::$Rs->gid!=1){
	    	if(!iMember::MP("Allow_Edit_Article","F") && $userid!=iMember::$uId){
	    		iPHP::alert('此文章已禁止编辑');
	    	}
    	}

        if(iCMS::$config['publish']['autodesc'] && iCMS::$config['publish']['descLen'] && empty($description) && empty($url)) {
            $bodyText    = implode("\n",$body);
            $bodyText    = str_replace('#--iCMS.PageBreak--#',"\n",$bodyText);
            $bodyText    = preg_replace(array('/<p[^>]*>/is','/<[\/\!]*?[^<>]*?>/is',"/\n+/","/　+/","/^\n/"),array("\n\n",'',"\n",'',''),$bodyText);
            $description = csubstr($bodyText,iCMS::$config['publish']['descLen']);
            unset($bodyText);
        }
               
        strstr($pic, 'http://') && $pic = iFS::http($pic);
        
        $isPic   = empty($pic)?0:1;       
        $SELFURL = __SELF__.$_POST['REFERER'];
        if(empty($_POST['REFERER'])||strstr($_POST['REFERER'], '=save')){
        	$SELFURL= __SELF__.'?app=article&do=manage';
        }
        
        $editor OR	$editor	= empty(iMember::$Rs->nickname)?iMember::$Rs->username:iMember::$Rs->nickname;
        
        // if($aid && $ischapter){
        //     $this->article_data($body,$aid);
        //     iDB::query("UPDATE `#iCMS@__article` SET `chapter`=chapter+1  WHERE `id` = '$aid'");
        //     iPHP::OK('章节添加完成!','url:'.$SELFURL);
        // }

        if(empty($aid)) {
            $postime  = $pubdate;
            $hits     = $good = $bad = $comments = 0;
            $picwidth = $picheight = 0;
            $ischapter && $chapter = 1;

            if($tags){
                iPHP::appClass("tag",'break');
                $tags = tag::add($tags,$userid,$aid,$this->category->rootid($cid));
                $tags = addslashes($tags);
            }

            iDB::query("INSERT INTO `#iCMS@__article` 
            	   (`cid`, `orderNum`, `title`, `stitle`, `clink`, `url`, `source`, `author`, `editor`, `userid`, `pic`, `picwidth`, `picheight`, `keywords`, `tags`, `description`, `related`, `metadata`, `pubdate`, `postime`, `hits`, `comments`, `good`, `bad`, `chapter`, `pid`, `top`, `postype`, `tpl`, `status`, `isPic`)
			VALUES ('$cid', '$orderNum', '$title', '$stitle', '$clink', '$url', '$source', '$author', '$editor', '$userid', '$pic', '$picwidth', '$picheight', '$keywords', '$tags', '$description', '$related', '$metadata', '$pubdate', '$postime', '$hits', '$comments', '$good', '$bad', '$chapter', '$pid', '$top', '$postype', '$tpl', '$status', '$isPic');");
            
            $aid	= iDB::$insert_id;
            $url OR $this->article_data($body,$aid);

            iDB::query("UPDATE `#iCMS@__category` SET `count` = count+1 WHERE `cid` ='$cid'");
            
            if($callback){
            	return array("code"=>$callback,'indexId'=>$aid);
            }
            $moreBtn = array(
                    array("text" =>"查看该文章","url"=>iRouter::url('article',array(array('id'=>$aid,'url'=>$url,'cid'=>$cid,'pubdate'=>$pubdate),$this->category->category[$cid]))->href,"o"=>'target="_blank"'),
                    array("text" =>"编辑该文章","url"=>APP_URI."&do=add&id=".$aid),
                    array("text" =>"继续添加文章","url"=>APP_URI."&do=add&cid=".$cid),
                    array("text" =>"返回文章列表","url"=>$SELFURL),
                    array("text" =>"查看网站首页","url"=>"../index.php","o"=>'target="_blank"')
            );
            iPHP::$dialogLock	= true;
            iPHP::dialog('success:#:check:#:文章添加完成!<br />10秒后返回文章列表','url:'.$SELFURL,10,$moreBtn);
        }else{
            $art = iDB::getRow("SELECT `cid`,`tags` FROM `#iCMS@__article` where `id` ='$aid' LIMIT 1;");
			if($tags){
				iPHP::appClass("tag",'break');
	            $tags = tag::diff($tags,$art->tags,iMember::$uId,$aid,$this->category->rootid($cid));
			    $tags = addslashes($tags);
            }
            $pic && list($picwidth, $picheight, $_type, $_attr) = @getimagesize(iFS::fp($pic,'+iPATH'));

			iDB::query("UPDATE `#iCMS@__article` 
			SET `cid` = '$cid', `orderNum` = '$orderNum', `title` = '$title', `stitle` = '$stitle', `clink` = '$clink', `url` = '$url', `source` = '$source', `author` = '$author', `editor` = '$editor', `userid` = '$userid', `pic` = '$pic', `picwidth` = '$picwidth', `picheight` = '$picheight', `keywords` = '$keywords', `tags` = '$tags', `description` = '$description', `related` = '$related', `metadata` = '$metadata', `pubdate` = '$pubdate', `chapter` = '$chapter', `pid` = '$pid', `top` = '$top', `postype` = '$postype', `tpl` = '$tpl',`status` = '$status', `isPic` = '$isPic'
			WHERE `id` = '$aid';");
            $url OR $this->article_data($body,$aid);
            //$ischapter && $this->chapterCount($aid);
            if($art->cid!=$cid) {
                iDB::query("UPDATE `#iCMS@__category` SET `count` = count-1 WHERE `cid` ='{$art->cid}' and `count`>0");
                iDB::query("UPDATE `#iCMS@__category` SET `count` = count+1 WHERE `cid` ='$cid'");
            }

            if(!strstr($this->category->category[$cid]['contentRule'],'{PHP}')&&!$this->category->category[$cid]['url']&&$this->category->category[$cid]['mode']=="1" && $status) {
				$htmlApp = iACP::app("html");
				$htmlApp->Article($aid);
			}
            iPHP::OK('文章编辑完成!<br />3秒后返回文章列表','url:'.$SELFURL);
        }
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
        $count = iDB::getValue("SELECT count(id) FROM `#iCMS@__article_data` where `aid` = '$aid'");
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
            $pic      = iDB::getValue("SELECT `pic` FROM `#iCMS@__article` WHERE `id` = '$aid'");
            if($autopic && $key==0 && empty($pic)){
				$uri  = parse_url(iCMS::$config['FS']['url']);
	            if(strstr(strtolower($value),$uri['host'])){
	               list($picwidth, $picheight, $type, $attr) = @getimagesize(iFS::fp($_value,'+iPATH'));
	               iDB::query("UPDATE `#iCMS@__article` SET `isPic`='1',`pic` = '$_value',`picwidth` = '$picwidth',`picheight` = '$picheight' WHERE `id` = '$aid'");
                }
            }
            $faid = iDB::getValue("SELECT `indexid` FROM `#iCMS@__filedata` WHERE `filename` ='$filename'");
            empty($faid) && iDB::query("UPDATE `#iCMS@__filedata` SET `indexid` = '$aid' WHERE `filename` ='$filename'");
        }
    }
    function postype(){
    	$this->postype OR $this->postype = 1;
    	return " AND `postype`='{$this->postype}'";
    }
    function dopurge(){
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
