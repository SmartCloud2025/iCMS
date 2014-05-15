<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: setting.app.php 2365 2014-02-23 16:26:27Z coolmoo $
*/
class settingApp{
    function __construct() {
    	$this->capp	= array('index','article','tag','search','usercp','category','comment');
		foreach (glob(iPHP_APP."/*/*.app.php") as $filename) {
			$path_parts = pathinfo($filename);
			$dirname	= str_replace(iPHP_APP.'/','',$path_parts['dirname']);
			if (!in_array($dirname,array('admincp','usercp'))) {
				$app	= str_replace('.app','',$path_parts['filename']);
				if (!in_array($app,$this->capp)){
					array_push($this->capp,$app);
				}
			}
		}
    }
    function doiCMS(){
    	$config	= iACP::getConfig(0);
    	$config['site']['indexName'] OR $config['site']['indexName'] = 'index';
    	include iACP::tpl("setting");
    }
    function dosave(){
    	$config		= iS::escapeStr($_POST['config']);
		iFS::filterExt($config['router']['htmlext'],true) OR iPHP::alert('网站URL设置 > 文件后缀 设置不合法!');
    	$config['app']	= $this->capp;
    	foreach($config AS $n=>$v){
    		iACP::setConfig($v,$n,0);
    	}
    	iACP::cacheConfig($config);
    	iPHP::OK('更新完成');
    }
    public function cache(){
        $config        = iACP::getConfig(0);
        $config['app'] = $this->capp;
        iACP::cacheConfig($config);
    }
}
