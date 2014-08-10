<?php
/**
* iPHP - i PHP Framework
* Copyright (c) 2012 iiiphp.com. All rights reserved.
*
* @author coolmoo <iiiphp@qq.com>
* @site http://www.iiiphp.com
* @licence http://www.iiiphp.com/license
* @version 1.0.1
* @package iURL
* @$Id: iURL.class.php 2408 2014-04-30 18:58:23Z coolmoo $
*/
class iURL {
    public static $config   = null;
    public static $uriArray = null;
	public static function init($config){
		self::$config	= $config;
	}
    private static function CPDIR($cid="0") {
        $C    = iCache::get('iCMS/category/'.$cid);
        $C['rootid'] && $dir.=self::CPDIR($C['rootid']);
        $dir.='/'.$C['dir'];
        return $dir;
    }

    private static function domain($cid="0",$akey='dir') {
        $ii       = new stdClass();
        $C        = iCache::get('iCMS/category/'.$cid);
        $rootid   = $C['rootid'];
        $ii->sdir = $C[$akey];
        if($rootid && empty($C['domain'])) {
            $dm         = self::domain($rootid);
            $ii->pd     = $dm->pd;
            $ii->domain = $dm->domain;
            $ii->pdir   = $dm->pdir.'/'.$C[$akey];
            $ii->dmpath = $dm->dmpath.'/'.$C[$akey];
        }else {
            $ii->pd     = $ii->pdir   = $ii->sdir;
            $ii->dmpath = $ii->domain = $C['domain']?(strstr($C['domain'],'http://')?$C['domain']:'http://'.$C['domain']):'';
        }
        return $ii;
    }

    public static function rule($a) {
    	$b	= $a[1];
    	$c	= self::$uriArray;
        switch($b) {
            case 'ID':		$e = $c['id'];break;
            case '0xID':	$e = sprintf("%08s",$c['id']);break;
            case '0x3ID':	$e = substr(sprintf("%08s",$c['id']), 0, 4);break;
            case '0x3,2ID':	$e = substr(sprintf("%08s",$c['id']), 4, 2);break;
            case 'NAME':	$e = urlencode(iS::escapeStr($c['name'][0]));break;
            case 'TITLE':	$e = urlencode(iS::escapeStr($c['title'][0]));break;
            case 'CID':		$e = is_array($c['cid'])?$c['cid'][0]:$c['cid'];break;
            case '0xCID':	$e = sprintf("%08s",$c['cid'][0]);break;
            case 'CDIR':	$e = is_array($c['dir'])?$c['dir'][0]:$c['dir'];break;
            case 'CPDIR':	$e = substr(self::CPDIR($c['cid'][0]),1);break;
            case 'TIME':	$e = $c['pubdate'][0];break;
            case 'YY':		$e = get_date($c['pubdate'],'y');break;
            case 'YYYY':	$e = get_date($c['pubdate'],'Y');break;
            case 'M':		$e = get_date($c['pubdate'],'n');break;
            case 'MM':		$e = get_date($c['pubdate'],'m');break;
            case 'D':		$e = get_date($c['pubdate'],'j');break;
            case 'DD':		$e = get_date($c['pubdate'],'d');break;
            case 'ZH_CN':	$e = $c['name'][0];break;
            case 'TCID':	$e = $c['tcid'];break;
            case 'TCDIR':	$e = $c['dir'][1];break;
            case 'TKEY':	$e = $c['tkey'];break;
            case 'EXT':		$e = empty($c['html.ext'])?self::$config['html_ext']:$c['html.ext'];break;
            case 'MD5':		$e = md5($c['id']);$e=substr(md5($e),8,16);break;
        }
        return $e;
    }
    public static function get($uri,$a=array()) {

        $i        = new stdClass();
        $sURL     = self::$config['URL'];
        $html_dir = self::$config['html_dir'];
        switch($uri) {
            case 'http':
                $i->href = $a['url'];
                $url     = $a['urlRule'];
                $_a      = (array)$a;
                $a       = array_merge_recursive((array)$a,(array)$a);
                $a['id'] = $_a['id'];
                break;
            case 'category':
                $i->href= $a['url'];
                $url	= $a['mode']==0?'{PHP}':$a['categoryRule'];
                ($a['password'] && $a['mode'] ==1) && $url = '{PHP}';
                $a		= array_merge_recursive((array)$a,(array)$a);
                break;
            case in_array($uri,array('article','content')):
                $i->href= $a[0]['url'];
                $url   	= $a[1]['mode']==0?'{PHP}':$a[1]['contentRule'];
                ($a[1]['password'] && $a[1]['mode']==1) && $url = '{PHP}';
                $a      = array_merge_recursive((array)$a[0],(array)$a[1],(array)$a[1]);
                break;
            case 'tag':
                $html_dir = self::$config['tag_dir'];
                $sURL     = self::$config['tag_url'];
                $i->href  = $a[0]['url'];
                $a        = array_merge_recursive((array)$a[0],(array)$a[1],(array)$a[2]);
                self::ma('URL.Rule','urlRule',$a);
                $url = $a['URL.Rule'];
                $url OR $url = self::$config['tag_rule'];
                break;
             default:$url = $a['urlRule'];
        }
        self::ma('html.ext','html_ext',$a);

//var_dump($a);

        if($i->href) return $i;

        if(strstr($url,'{PHP}')===false) {
        	self::$uriArray	= $a;
        	strstr($url,'{') && $url = preg_replace_callback ("/\{(.*?)\}/",'__iurl_rule__',$url);
//var_dump($url);
            $i->path    = iFS::path(iPATH.$html_dir.$url);
            $i->href    = iFS::path($sURL.'/'.$url);
//var_dump($i);
			$pathA 		= pathinfo($i->path);
//var_dump($pathA);

//            if(in_array($uri,array('article','content'))) {
//                $i->path    = FS::path($Curl->dmdir.'/'.$url);
//                $i->href    = FS::path($Curl->domain.'/'.$url);
//            }
            $i->hdir    = pathinfo($i->href,PATHINFO_DIRNAME);
            $i->dir		= $pathA['dirname'];
            $i->file    = $pathA['basename'];
            $i->name    = $pathA['filename'];
            $i->name OR $i->name=$i->file;
            $i->ext		= '.'.$pathA['extension'];
//var_dump($GLOBALS['page']);
//print_r($i);
//var_dump($pathA);

            if(empty($i->file)||substr($url,-1)=='/'||empty($pathA['extension'])) {
                $i->name    = 'index';
                $i->ext     = self::$config['html_ext'];
				$a['html.ext'] && $i->ext 	= $a['html.ext'];
                $i->file    = $i->name.$i->ext;
                $i->path    = $i->path.'/'.$i->file;
                $i->dir		= dirname($i->path);
                $i->hdir    = dirname($i->href.'/'.$i->file);
            }
            if(strstr($i->file,'{P}')===false) {
				$i->pfile	= $i->name."_{P}".$i->ext;
			}
	        if($uri=="http"||strstr($url,'http://')){
	        	$hi->href    = $url;
                $hi->ext     = $i->ext;
	            $hi->pageurl = $hi->href.'/'.$i->pfile ;
	        	return $hi;
	        }
//print_r($i);
//exit;
			if($uri=='category') {
                $m    = self::domain($a['cid'][1]);
                if($m->domain) {
                    $i->href 	= str_replace($i->hdir,$m->dmpath,$i->href);
                    $i->hdir 	= $m->dmpath;
                    $__dir__ 	= $i->dir.'/'.$m->pdir;
                    $i->path 	= str_replace($i->dir,$__dir__,$i->path);
                    $i->dir  	= $__dir__;
                    $i->dmdir	= iFS::path_join(iPATH,$html_dir.'/'.$m->pd);
                    $bits       = parse_url($i->href);
                    $i->domain  = $bits['scheme'].'://'.$bits['host'];
                }else {
                    $i->dmdir   = iFS::path_join(iPATH,$html_dir);
                    $i->domain  = $sURL;
                }
		        if(strstr($a['domain'][1],'http://')){
		        	$i->href    = $a['domain'][1];
		        }
            }
            $i->pageurl = $i->hdir.'/'.$i->pfile ;
            $i->pagepath= $i->dir.'/'.$i->pfile;
//            $i->href	= str_replace('{P}',$p,$i->href);
//            $i->path	= str_replace('{P}',$p,$i->path);
//            $i->file	= str_replace('{P}',$p,$i->file);
//            $i->name	= str_replace('{P}',$p,$i->name);
//print_r($i);
//exit;
//            var_dump($i);
        }else {
        	$url	= $uri.'.php?';
	        switch($uri){
	            case 'category':
		            $a['categoryURI'][1] && $url = $a['categoryURI'][1].'.php?';
		            $url.='cid='.$a['cid'][1];
		            $a['type'][1] && $url.="&type=".$a['type'][1];
	            break;
	            case 'article':
	            	$url.='id='.$a['id'];
	            	$i->pageurl	= $url.'&p={P}';
					strstr($i->pageurl,'http://') OR $i->pageurl = rtrim($sURL,'/').'/'.$i->pageurl;
	            break;
	            case 'tag':$url.='id='.$a['id'];break;
	        }
			strstr($url,'http://') OR $url	= rtrim($sURL,'/').'/'.$url;
            $i->href	= $url;
        }
        return $i;
    }
    private static function ma($n,$o,&$a){
		$a[$n]	= $a[$o];
		if(is_array($a[$o])){
    		$a[$n] = $a[$o][0];
			$a[$o][1]!="" && $a[$n] = $a[$o][1];
		}
		return $a[$n];
    }
}
function __iurl_rule__($a){
	return iURL::rule($a);
}