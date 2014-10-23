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
    	$this->apps	= array('index','article','tag','search','usercp','category','comment','favorite');
		foreach (glob(iPHP_APP_DIR."/*/*.app.php") as $filename) {
            $path_parts = pathinfo($filename);
            $dirname    = str_replace(iPHP_APP_DIR.'/','',$path_parts['dirname']);
			if (!in_array($dirname,array('admincp','usercp'))) {
                $app = str_replace('.app','',$path_parts['filename']);
				in_array($app,$this->apps) OR array_push($this->apps,$app);
			}
		}
    }
    function do_iCMS(){
    	$config	= iACP::getConfig(0);
    	$config['site']['indexName'] OR $config['site']['indexName'] = 'index';
        //$redis    = extension_loaded('redis');
        $memcache = extension_loaded('memcache');
    	include iACP::view("setting");
    }
    function do_save(){
        $config = iS::escapeStr($_POST['config']);

        $config['router']['html_ext'] = '.'.trim($config['router']['html_ext'],'.');
		iFS::filterExt($config['router']['html_ext'],true) OR iPHP::alert('网站URL设置 > 文件后缀 设置不合法!');

        $config['router']['URL']        = trim($config['router']['URL'],'/');
        $config['router']['public_url'] = trim($config['router']['public_url'],'/');
        $config['router']['user_url']   = trim($config['router']['user_url'],'/');
        $config['router']['tag_url']    = trim($config['router']['tag_url'],'/');
        $config['FS']['url']            = trim($config['FS']['url'],'/').'/';
        $config['router']['DIR']        = rtrim($config['router']['DIR'],'/').'/';
        $config['router']['html_dir']   = rtrim($config['router']['html_dir'],'/').'/';
        $config['router']['tag_dir']    = rtrim($config['router']['tag_dir'],'/').'/';

        foreach ((array)$config['open'] as $platform => $value) {
            if($value['appid'] && $value['appkey']){
                $config['open'][$platform]['enable'] = true;
            }
        }

        $config['apps']	= $this->apps;
    	foreach($config AS $n=>$v){
    		iACP::setConfig($v,$n,0);
    	}
    	iACP::cacheConfig($config);
    	iPHP::success('更新完成','js:1');
    }
    public function cache(){
        $config         = iACP::getConfig(0);
        $config['apps'] = $this->apps;
        iACP::cacheConfig($config);
    }
}
