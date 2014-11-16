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
		$this->stateInfo = 'SUCCESS';
		iFS::$callback   = true;
    }
    function do_config(){
    	$config_json ='
/* 前后端通信相关的配置,注释只允许使用多行方式 */
{
    /* 上传图片配置项 */
    "imageActionName": "uploadimage", /* 执行上传图片的action名称 */
    "imageFieldName": "upfile", /* 提交的图片表单名称 */
    "imageMaxSize": 2048000, /* 上传大小限制，单位B */
    "imageAllowFiles": [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 上传图片格式显示 */
    "imageCompressEnable": true, /* 是否压缩图片,默认是true */
    "imageCompressBorder": 1600, /* 图片压缩最长边限制 */
    "imageInsertAlign": "none", /* 插入的图片浮动方式 */
    "imageUrlPrefix": "", /* 图片访问路径前缀 */
    "imagePathFormat": "",

    /* 涂鸦图片上传配置项 */
    "scrawlActionName": "uploadscrawl", /* 执行上传涂鸦的action名称 */
    "scrawlFieldName": "upfile", /* 提交的图片表单名称 */
    "scrawlPathFormat": "",
    "scrawlMaxSize": 2048000, /* 上传大小限制，单位B */
    "scrawlUrlPrefix": "", /* 图片访问路径前缀 */
    "scrawlInsertAlign": "none",

    /* 截图工具上传 */
    "snapscreenActionName": "uploadimage", /* 执行上传截图的action名称 */
    "snapscreenPathFormat": "",
    "snapscreenUrlPrefix": "", /* 图片访问路径前缀 */
    "snapscreenInsertAlign": "none", /* 插入的图片浮动方式 */

    /* 抓取远程图片配置 */
    "catcherLocalDomain": ["127.0.0.1", "localhost"],
    "catcherActionName": "catchimage", /* 执行抓取远程图片的action名称 */
    "catcherFieldName": "source", /* 提交的图片列表表单名称 */
    "catcherPathFormat": "",
    "catcherUrlPrefix": "", /* 图片访问路径前缀 */
    "catcherMaxSize": 2048000, /* 上传大小限制，单位B */
    "catcherAllowFiles": [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 抓取图片格式显示 */

    /* 上传视频配置 */
    "videoActionName": "uploadvideo", /* 执行上传视频的action名称 */
    "videoFieldName": "upfile", /* 提交的视频表单名称 */
    "videoPathFormat": "",
    "videoUrlPrefix": "", /* 视频访问路径前缀 */
    "videoMaxSize": 102400000, /* 上传大小限制，单位B，默认100MB */
    "videoAllowFiles": [
        ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
        ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid"], /* 上传视频格式显示 */

    /* 上传文件配置 */
    "fileActionName": "uploadfile", /* controller里,执行上传视频的action名称 */
    "fileFieldName": "upfile", /* 提交的文件表单名称 */
    "filePathFormat": "",
    "fileUrlPrefix": "", /* 文件访问路径前缀 */
    "fileMaxSize": 51200000, /* 上传大小限制，单位B，默认50MB */
    "fileAllowFiles": [
        ".png", ".jpg", ".jpeg", ".gif", ".bmp",
        ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
        ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
        ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
        ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
    ], /* 上传文件格式显示 */

    /* 列出指定目录下的图片 */
    "imageManagerActionName": "imageManager", /* 执行图片管理的action名称 */
    "imageManagerListPath": "", /* 指定要列出图片的目录 */
    "imageManagerListSize": 20, /* 每次列出文件数量 */
    "imageManagerUrlPrefix": "", /* 图片访问路径前缀 */
    "imageManagerInsertAlign": "none", /* 插入的图片浮动方式 */
    "imageManagerAllowFiles": [".png", ".jpg", ".jpeg", ".gif", ".bmp"], /* 列出的文件类型 */

    /* 列出指定目录下的文件 */
    "fileManagerActionName": "fileManager", /* 执行文件管理的action名称 */
    "fileManagerListPath": "", /* 指定要列出文件的目录 */
    "fileManagerUrlPrefix": "", /* 文件访问路径前缀 */
    "fileManagerListSize": 20, /* 每次列出文件数量 */
    "fileManagerAllowFiles": [
        ".png", ".jpg", ".jpeg", ".gif", ".bmp",
        ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
        ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
        ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
        ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
    ] /* 列出的文件类型 */
}
    	';
        $result = preg_replace("/\/\*[\s\S]+?\*\//", "", $config_json, true);

        if (isset($_GET["callback"])) {
            header("Access-Control-Allow-Origin: ".__HOST__); //设置允许跨域访问
            header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }
    }
    function do_imageManager(){
		$res               = iPHP::folder(iCMS::$config['FS']['dir'],array('jpg','png','gif','jpeg'));
		$res['public_url'] = iCMS_PUBLIC_URL;
		iPHP::json($res);
    }
    function do_fileManager(){
        $res               = iPHP::folder(iCMS::$config['FS']['dir']);
        $res['public_url'] = iCMS_PUBLIC_URL;
        iPHP::json($res);
    }
    function do_catchimage(){
    	$url_array = (array)$_POST['source'];
		/* 抓取远程图片 */
        $list = array();
        $uri  = parse_url(iCMS_FS_URL);
		foreach ($url_array as $_k => $imgurl) {
            if (stripos($imgurl,$uri['host']) !== false){
				unset($_array[$_k]);
			}

			$F = iFS::http($imgurl,'array');
	    	$F['code'] OR $this->stateInfo = $F['state'];
	    	$F['path'] && $url = iFS::fp($F['path'],'+http');
		    array_push($list, array(
				"state"    => $this->stateInfo,
				"url"      => $url,
				"size"     => $F["size"],
				"title"    => iS::escapeStr($info["title"]),
				"original" => iS::escapeStr($F["oname"]),
				"source"   => iS::escapeStr($imgurl)
		    ));
		}
		/* 返回抓取数据 */
		iPHP::json(array(
			'code'  => count($list) ? '1':'0',
			'state' => count($list) ? 'SUCCESS':'ERROR',
			'list'  => $list
		));
    }
    function do_uploadimage(){
		$F = iFS::upload('upfile');
    	$F['code'] OR $this->stateInfo = $F['state'];
    	$F['path'] && $url = iFS::fp($F['path'],'+http');
		iPHP::json(array(
			'title'    => iS::escapeStr($_POST['pictitle']),
			'original' => $F['oname'],
			'url'      => $url,
			'code'     => $F['code'],
			'state'    => $this->stateInfo
		));
    }
    function do_uploadfile(){
		$F	= iFS::upload('upfile');
		$F['code']	OR	$this->stateInfo = $F['state'];
		$F['path'] && $url	= iFS::fp($F['path'],'+http');
    	iPHP::json(array(
			"url"      =>$url,
			"fileType" =>$F["ext"],
			"original" =>$F["oname"],
			"state"    =>$this->stateInfo
		));
    }
    function do_uploadvideo(){
        $F  = iFS::upload('upfile');
        $F['code']  OR  $this->stateInfo = $F['state'];
        $F['path'] && $url  = iFS::fp($F['path'],'+http');
        iPHP::json(array(
            "url"      =>$url,
            "fileType" =>$F["ext"],
            "original" =>$F["oname"],
            "state"    =>$this->stateInfo
        ));
    }
    function do_uploadscrawl(){
		if ($_GET[ "action" ] == "tmpImg") { // 背景上传
			$F		= iFS::upload('upfile','scrawl/tmp');
			$F['code']	OR	$this->stateInfo = $F['state'];
			$F['path'] && $url	= iFS::fp($F['path'],'+http');
			echo "<script>parent.ue_callback('" .$url. "','" .$this->stateInfo. "')</script>";
		} else {
			$F		= iFS::base64ToFile($_POST['upfile'],'scrawl/'.get_date(0,'Y/md'));
			$F['code']	OR	$this->stateInfo = $F['state'];
			$F['path'] && $url	= iFS::fp($F['path'],'+http');
			$tmp 	= iFS::get_dir()."scrawl/tmp/";
			iFS::rmdir($tmp);
	    	iPHP::json(array(
				"url"   =>$url,
				"state" =>$this->stateInfo
			));
		}
    }
}
