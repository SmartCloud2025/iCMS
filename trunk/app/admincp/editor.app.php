<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: editor.app.php 2095 2013-09-26 07:48:11Z coolmoo $
*/
class editorApp{
    function __construct() {
		$this->stateInfo	= 'SUCCESS';
		iFS::$callback		= true;
    }
    function doimageManager(){
		$res              = iPHP::folder(iCMS::$config['FS']['dir'],array('jpg','png','gif','jpeg'));
		$res['publicURL'] = iCMS::$config['router']['publicURL'];
		iPHP::json($res);
    }
    function doimageUp(){
    	$F		= iFS::upload('upfile');
    	$F['code']	OR	$this->stateInfo = $F['state'];
    	
    	$F['path'] && $url	= iFS::fp($F['path'],'+http');
    	$oname	= $F['oname'];
		$title	= htmlspecialchars($_POST['pictitle'], ENT_QUOTES);
		iPHP::json(array(
					'title'=>$title,
					'original'=>$oname,
					'url'=>$url,
					'state'=>$this->stateInfo
		));
    }
    function dofileUp(){
		$F	= iFS::upload('upfile');
		$F['code']	OR	$this->stateInfo = $F['state'];
		$F['path'] && $url	= iFS::fp($F['path'],'+http');
    	iPHP::json(array(
			"url"=>$url,
			"fileType"=>$F["ext"],
			"original"=>$F["oname"],
			"state"=>$this->stateInfo
		));
    }
    function doscrawlUp(){
		if ($_GET[ "action" ] == "tmpImg") { // 背景上传
			$F		= iFS::upload('upfile','scrawl/tmp');
			$F['code']	OR	$this->stateInfo = $F['state'];
			$F['path'] && $url	= iFS::fp($F['path'],'+http');
			echo "<script>parent.ue_callback('" .$url. "','" .$this->stateInfo. "')</script>";
		} else {
			$F		= iFS::base64ToFile($_POST['content'],'scrawl/'.get_date(0,'Y/md'));
			$F['code']	OR	$this->stateInfo = $F['state'];
			$F['path'] && $url	= iFS::fp($F['path'],'+http');
			$tmp 	= iFS::get_dir()."scrawl/tmp/";
			iFS::rmdir($tmp);
	    	iPHP::json(array(
				"url"=>$url,
				"state"=>$this->stateInfo
			));
		}
    }
}