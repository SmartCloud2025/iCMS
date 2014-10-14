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
    function do_imageManager(){
		$res               = iPHP::folder(iCMS::$config['FS']['dir'],array('jpg','png','gif','jpeg'));
		$res['public_url'] = iCMS_PUBLIC_URL;
		iPHP::json($res);
    }
    function do_getremote(){
    	$urls = $_POST['urls'];
    	$url_array = explode('ue_separate_ue', $urls);
		/* 抓取远程图片 */
		$list = array();
		$uri = parse_url(iCMS::$config['FS']['url']);
		foreach ($url_array as $_k => $imgurl) {
			if (strstr(strtolower($imgurl), $uri['host'])){
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
    function do_imageUp(){
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
    function do_fileUp(){
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
    function do_scrawlUp(){
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
				"url"   =>$url,
				"state" =>$this->stateInfo
			));
		}
    }
}
