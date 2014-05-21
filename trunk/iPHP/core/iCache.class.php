<?php
/**
* iPHP - i PHP Framework
* Copyright (c) 2012 iiiphp.com. All rights reserved.
*
* @author coolmoo <iiiphp@qq.com>
* @site http://www.iiiphp.com
* @licence http://www.iiiphp.com/license
* @version 1.0.1
* @package iCache
* @$Id: iCache.class.php 2408 2014-04-30 18:58:23Z coolmoo $
*/
//array(
//	'enable'	=> $GLOBALS['iCONFIG']['cache']['enable'],
//	'engine'	=> $GLOBALS['iCONFIG']['cache']['engine'],
//	'host'		=> $GLOBALS['iCONFIG']['cache']['host'],
//	'port'		=> $GLOBALS['iCONFIG']['cache']['port'],
//	'db'		=> $GLOBALS['iCONFIG']['cache']['db'],
//	'compress'	=> $GLOBALS['iCONFIG']['cache']['compress'],
//	'time'		=> $GLOBALS['iCONFIG']['cache']['time'],
//)
class iCache{
    public static $link      = null;
    protected static $config = null;

	public static function init($config){
		self::$config	= $config;
		if(!self::$config['enable']){
			return;
		}
		if(isset($GLOBALS['iCache']['link'])){
			self::$link	= $GLOBALS['iCache']['link'];
			return self::$link;
		}
		self::$config['engine'] OR self::$config['engine']='file';
		self::$config['reset']	&& self::$link	= null;
		
        if(self::$link===null){
        	switch(self::$config['engine']){
        		case 'memcached':
        			require iPHP_CORE.'/memcached.class.php';
                    $_servers   = explode("\n",str_replace(array("\r"," "),"",self::$config['host']));
                    self::$link = new memcached(array(
                        'servers'            => $_servers,
                        'compress_threshold' => 10240,
                        'persistant'         => false,
                        'debug'              => false,
                        'compress'           => self::$config['compress']
	                ));
	                unset($_servers);
        		break;
        		case 'redis':
        			require iPHP_CORE.'/redis.class.php';
                    list($hosts,$db)  = explode('@',trim(self::$config['host']));
                    list($host,$port) = explode(':',$hosts);
        			if(strstr($hosts, 'unix:')){
        				$host	= $hosts;
        				$port	= 0;
        			}
                    $db         = (int)str_replace('db:','',$db);
                    $db OR $db  = 1;
                    self::$link = new Redis(array(
					    'host'     => $host,
					    'port'     => $port,
					    'db'       => $db
					));
        		break;
        		case 'file':
        			require iPHP_CORE.'/iFileCache.class.php';
                    list($dirs,$level) = explode(':',self::$config['host']);
                    $level OR $level   = 0;
                    self::$link = new iFC(array(
						'dirs'    => $dirs,
						'level'   => $level,
						'compress'=> self::$config['compress']
	                ));
        		break;
        	}
        	$GLOBALS['iCache']['link'] = self::$link;
        }
	}
    public static function get($keys,$ckey=NULL){
        $_keys=implode('',(array)$keys);
        if(!self::$config['enable']){
        	if(strstr($_keys,'system')===false){
        		return NULL;
        	}else{
        		self::sysCache();
        	}
        }
        if(!isset($GLOBALS['iCache'][$_keys])){
            $GLOBALS['iCache'][$_keys]=is_array($keys)?
                    self::$link->get_multi($keys):
                    self::$link->get($keys);
        }
        return $ckey===NULL?$GLOBALS['iCache'][$_keys]:$GLOBALS['iCache'][$_keys][$ckey];
    }
    public static function set($keys,$res,$cachetime="-1") {
        if(!self::$config['enable']){
        	if(strstr($keys,'system')===false){
        		return NULL;
        	}else{
        		self::sysCache();
        	}
        }
        if(self::$config['engine']=='memcached') {
            self::$link->delete($keys);
        }
        self::$link->add($keys,$res,($cachetime!="-1"?$cachetime:self::$config['time']));
        return $this;
    }
    public static function delete($key='', $time = 0){
    	self::$link->delete($key,$time);
    }
    public static function getsys($keys,$ckey=NULL){
    	if(is_array($keys)){
    		foreach($keys AS $k){
    			$_keys[]='iCMS/'.$k;
    		}
    		$keys=$_keys;
    	}
    	return self::get($keys,$ckey);
    }
    public static function sysCache(){
		if(!isset($GLOBALS['iFileCache_class_php'])){
			$GLOBALS['iFileCache_class_php'] = true;
			require iPHP_CORE.'/iFileCache.class.php';
		}
	    self::$link	= new iFC(array(
            'dirs'     => 'cache',
            'level'    => 0,
            'compress' => 1
	    ));
	}
	public static function redis(){
    	if(self::$config['engine']!='redis'){
            $_config['enable'] = true;
            $_config['reset']  = true;
            $_config['engine'] = 'redis';
            $_config['host']   = '127.0.0.1:6379@db:1';
            $_config['time']   = '86400';
			iCache::init($_config);
		}
	}
}
