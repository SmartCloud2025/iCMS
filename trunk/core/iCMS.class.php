<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: iCMS.class.php 2412 2014-05-04 09:52:07Z coolmoo $
*/
class iCMS {
    public static $config      = array();
    public static $apps        = null;
    public static $do          = null;
    public static $iCache      = null;
    public static $sphinx      = null;
    public static $app         = null;
    public static $app_name    = null;
    public static $app_do      = null;
    public static $app_method  = null;
    public static $app_tpl     = null;
    public static $app_path    = null;
    public static $app_file    = null;
    public static $app_args    = null;
    public static $app_vars    = null;
    public static $mobile      = false;
    public static $HOOK        = array();
    
	public static function init(){
        $site   = iPHP_MULTI_SITE ? $_SERVER['HTTP_HOST']:"iCMS";
        if(iPHP_MULTI_DOMAIN){ //只绑定主域 
            preg_match("/[^\.\/]+\.[^\.\/]+$/", $site, $matches);
            $site = $matches[0];
        }
        strpos($site, '..') === false OR exit('<h1>What are you doing?(code:0001)</h1>');

        //config.php 中开启iPHP_APP_CONF后 此处设置无效,
        define('iPHP_APP_CONF', iPHP_APP.'/config/'.$site);//网站配置目录    
        define('iCMS_CONF_FILE',iPHP_APP_CONF.'/config.php');   //网站配置文件
        @is_file(iCMS_CONF_FILE) OR exit('<h1>iCMS 运行出错.找不到"'.$site.'"网站的配置文件!(code:0002)</h1>');
        require iCMS_CONF_FILE;
        self::$config = $GLOBALS['iConfig'];

        //config.php 中开启后 此处设置无效
        defined('iPHP_DEBUG')       OR define('iPHP_DEBUG', self::$config['debug']['php']);       //程序调试模式
        defined('iPHP_TPL_DEBUG')   OR define('iPHP_TPL_DEBUG',self::$config['debug']['tpl']);    //模板调试
        defined('iPHP_TIME_CORRECT')OR define('iPHP_TIME_CORRECT',self::$config['time']['cvtime']);
        //config.php --END--

        define('iPHP_URL_404',self::$config['router']['404']);//404定义
        
        if(iPHP_DEBUG||iPHP_TPL_DEBUG){
            set_error_handler('iPHP_ERROR_HANDLER');
            error_reporting(E_ALL & ~E_NOTICE);
        }

        $timezone = self::$config['time']['zone'];
        empty($timezone) && $timezone = 'Asia/Shanghai';//设置中国时区
        @ini_set('date.timezone',$timezone);
        function_exists('date_default_timezone_set') && @date_default_timezone_set($timezone);

        iFS::$TABLE = '#iCMS@__filedata';
        iFS::init(self::$config['FS']);
        iCache::init(self::$config['cache']);
        iRouter::init(self::$config['router']);

        $mobile_agent = str_replace(',','|',preg_quote(self::$config['other']['mobile_agent']));
        $mobile_agent && preg_match('/'.$mobile_agent.'/i',$_SERVER["HTTP_USER_AGENT"]) && self::$mobile = true;
        $tpl_key      = (self::$config['site']['MW_TPL'] && self::$mobile)?'MW_TPL':'PC_TPL';
        define('iPHP_TPL_DEF',self::$config['site'][$tpl_key]);
        iPHP::iTPL();

        define('iCMS_PUBLIC',self::$config['router']['publicURL']);
        define('iCMS_API', iCMS_PURL.'/api.php');
        define('iCMS_URL', self::$config['router']['URL']);

        defined('iCMS_REWRITE') OR define('iCMS_REWRITE', 0);

        self::$apps   = self::$config['app'];
        self::site();
	}
    public static function site(){
        $router = self::$config['router'];
        iPHP::assign('site',array(
            "title"       => self::$config['site']['name'],
            "seotitle"    => self::$config['site']['seotitle'],
            "keywords"    => self::$config['site']['keywords'],
            "description" => self::$config['site']['description'],
            "tpl"         => self::$config['site'][(self::$mobile?'MW_TPL':'PC_TPL')],
            "icp"         => self::$config['site']['icp'],
            'url'         => $router['URL'],
            '404'         => $router['404'],
            'urls'        => array(
                "ui"     => $router['publicURL'].'/ui',
                "lib"    => $router['publicURL'].'/lib',
                "public" => $router['publicURL'],
                "user"   => $router['userURL'],
                "avatar" => rtrim(self::$config['FS']['url'],'/').'/avatar/',
                "res"    => self::$config['FS']['url'],
			)
		));
        iPHP::$dialogTitle  = self::$config['site']['name'];
    }
    /**
     * 运行应用程序
     * @param string $app 应用程序名称
     * @param string $do 动作名称
     * @return iCMS
     */
    public static function run($app = NULL,$do = NULL,$prefix="do") {
    	//empty($app) && $app	= $_GET['app']; //单一入口
    	if(empty($app)){
	    	$fi	= iFS::name(__SELF__);
    		$app= $fi['name'];
    	}
		if (!in_array($app, self::$config['app']) && iPHP_DEBUG){
			iPHP::throwException('应用程序运行出错.找不到应用程序: <b>' . $app.'</b>', 1001);
		}
        self::$app_path   = iPHP_APP.'/'.$app;
        self::$app_file   = self::$app_path.'/'.$app.'.app.php';
        is_file(self::$app_file) OR iPHP::throwException('应用程序运行出错.找不到文件: <b>' . self::$app_name.'.app.php</b>', 1002);
        $do OR $do        = $_GET['do']?(string)$_GET['do']:'iCMS';
        if($_POST['action']){
            $do     = $_POST['action'];
            $prefix = 'ACTION_';
        }
        self::$app_name   = $app;
        self::$app_do     = $do;
        self::$app_method = $prefix.$do;
        self::$app_tpl    = iPHP_APP.'/'.$app.'/template';
    	self::$app_vars   = array(
            'version'    => iCMS_VER,
            "is_mobile"  => self::$mobile,
            'API'        => iCMS_API,
            'SAPI'       => iCMS_API.'?app='.self::$app_name,
            'SECCODE'    => self::$config['router']['publicURL'].'/seccode.php?',
            'COOKIE_PRE' => iPHP_COOKIE_PRE,
            'refer'      => __REF__,
            'config'     => self::$config,
            "APP"        => array(
                'name'   => self::$app_name,
                'do'     => self::$app_do,
                'method' => self::$app_method
            ),            
        );

        iPHP::$iTPL->_iTPL_vars = self::$app_vars;

        iPHP::import(self::$app_file);
        $appName    = self::$app_name . 'App';
		self::$app	= new $appName();
		if(self::$app_do && self::$app->methods){
			in_array(self::$app_do, self::$app->methods) OR iPHP::throwException('应用程序运行出错. <b>' .self::$app_name. '</b> 类中找不到方法定义: <b>'.self::$app_method.'</b>', 1003);
			$method = self::$app_method;
			$args 	= self::$app_args;
			if ($args){
				return self::$app->$method($args);
			}else{
				return self::$app->$method();
			}
		}else{
			iPHP::throwException('应用程序运行出错. <b>' .self::$app_name. '</b> 类中 <b>do'. self::$app_do.'</b> 方法不存在', 1004);
		}

    }
    public static function API($app = NULL,$do = NULL) {
    	$app OR $app	= iS::escapeStr($_GET['app']);
    	self::run($app,null,'API_');
    }
    #------------Template-----------#
    public static function tpl($tpl,$p='index') {
        $tpl OR iPHP::throwException('应用程序运行出错. 请设置模板文件', 2000,'TPL');
        if(strpos($tpl,'APP:/')!==false){
            $tpl = 'file::'.self::$app_tpl."||".str_replace('APP:/','',$tpl);
            return iPHP::pl($tpl);
        }

        strpos($tpl,'iCMS:/') !==false && $tpl = str_replace('iCMS:/','iCMS',$tpl);
        strpos($tpl,'iTPL:/') !==false && $tpl = str_replace('iTPL:/',iPHP_TPL_DEF,$tpl);
        strpos($tpl,'{iTPL}') !==false && $tpl = str_replace('{iTPL}',iPHP_TPL_DEF,$tpl);

        if(@is_file(iPHP_TPL_DIR."/".$tpl)) {
            return iPHP::pl($tpl);
        }else{
        	iPHP::throwException('应用程序运行出错. 找不到模板文件 <b>' .$tpl. '</b>', 2001,'TPL');
        }
    }
    //------------------------------------
	public static function getIds($id = "0",$all=true) {
	    $ids	= array();
	    $cArray	= iCache::get('system/category/rootid',$id);
	    foreach((array)$cArray AS $_id) {
	        $ids[]	= $_id;
	        $all && $ids[]	= self::getIds($_id,$all);
	    }
	    $ids	= array_unique($ids);
	    $ids	= array_filter($ids);
	    return $ids;
	}
    public static function sphinx(){
    	iPHP::import(iPHP_APP_CORE.'/sphinx.class.php');
    	
		if(isset($GLOBALS['iSPH'])) return $GLOBALS['iSPH'];
		
		$hosts				= self::$config['sphinx']['hosts'];
		$GLOBALS['iSPH']	= new SphinxClient();
		if(strstr($hosts, 'unix:')){
			$hosts	= str_replace("unix://",'',$hosts);
			$GLOBALS['iSPH']->SetServer($hosts);
		}else{
			list($host,$port)=explode(':',$hosts);
			$GLOBALS['iSPH']->SetServer($host,$port);
		}
		return $GLOBALS['iSPH'];
    }
    public static function TBAPI(){
    	iPHP::import(iPHP_APP_CORE.'/tbapi.class.php');
    	
    	if(isset($GLOBALS['TBAPI'])) return $GLOBALS['TBAPI'];
    	
		$GLOBALS['TBAPI'] = new TBAPI;
		return $GLOBALS['TBAPI'];
    }
    public static function Hook($key,$value){
		self::$HOOK[$key]	= $value;
    }
    //------------------------------------
    public static function gotohtml($fp,$url='',$fmode='0') {
    	if(iPHP::$iTPLMode=='html') return;
    	
        ($fmode==1 && @is_file($fp) && stristr($fp, '.php?') === FALSE) && iPHP::gotourl($url);
    }
    //翻页函数
    public static function page($a) {
        iPHP::loadClass("Pages");
        $lang  = iPHP::lang('iCMS:page');
        $multi = new iPages($a,$lang);
        if($multi->totalpage>1) {
        	$pagenav	= $a['pagenav']?$a['pagenav']:'nav';
        	$pnstyle	= $a['pnstyle']?$a['pnstyle']:0;
            iPHP::assign('page',array('totalRow'=>$a['total'],'total'=>$multi->totalpage,'current'=>$multi->nowindex,$pagenav=>$multi->show($pnstyle)));
            iPHP::assign('iPAGE',$multi);
        }
        return $multi;
    }
    //过滤
    public static function filter(&$content){
	    $cache		= iCache::get(array('system/word.filter','system/word.disable'));
	    $filter		= $cache['system/word.filter'];//filter过滤
	    $disable    = $cache['system/word.disable'];//disable禁止
	    //禁止关键词
	    foreach ((array)$disable AS $val) {
	        if ($val && preg_match("/".preg_quote($val, '/')."/i", $content)) {
	            return $val;
	        }
	    }
	    //过滤关键词
	    foreach ((array)$filter AS $k =>$val) {
	        empty($val[1]) && $val[1]='***';
	        $val[0] && $content = preg_replace("/".preg_quote($val[0], '/')."/i",$val[1],$content);
	    }
	}
	//内链
    public static function keywords($a) {
        if(self::$config['other']['kwCount']==0) return $a;

        $keywords	= iCache::get('system/keywords');
        if($keywords){
        	foreach($keywords AS $i=>$val) {
	            if($val['times']>0) {
	                $search[]	= $val['keyword'];
	                $replace[]	= '<a class="keyword" target="_blank" href="'.$val['url'].'">'.$val['keyword'].'</a>';
	            }
           }
           return self::str_replace_limit($search, $replace, stripslashes($a),self::$config['other']['kwCount']);
        }
        return $a;
    }
    function str_replace_limit($search, $replace, $subject, $limit=-1) {
        preg_match_all ("/<a[^>]*?>(.*?)<\/a>/si", $subject, $matches);//链接不替换
        $linkArray	= array_unique($matches[0]);
        $linkArray & $linkflip	= array_flip($linkArray);
        foreach((array)$linkflip AS $linkHtml=>$linkkey){
            $linkA[$linkkey]='###iCMS_LINK_'.rand(1,1000).'_'.$linkkey.'###';
        }
        $subject = str_replace($linkArray,$linkA,$subject);

        preg_match_all ("/<[\/\!]*?[^<>]*?>/si", $subject, $matches);
        $htmArray	= (array)array_unique($matches[0]);
        $htmArray && $htmflip    = array_flip($htmArray);
        foreach((array)$htmflip AS $kHtml=>$vkey){
            $htmA[$vkey]="###iCMS_HTML_".rand(1,1000).'_'.$vkey.'###';
        }
        $subject = str_replace($htmArray,$htmA,$subject);

        // constructing mask(s)...
        if (is_array($search)) {
            foreach ($search as $k=>$v) {
                $search[$k] = '`' . preg_quote($search[$k],'`') . '`i';
            }
        }else {
            $search = '`' . preg_quote($search,'`') . '`';
        }
        // replacement
        $replace && $replaceflip	= array_flip($replace);
        foreach((array)$replaceflip AS $rk=>$replacekey){
            $replaceA[$replacekey]="###iCMS_REPLACE_".rand(1,1000).'_'.$replacekey.'###';
        }
        // replacement
        $subject = preg_replace($search, $replaceA, $subject, $limit);
        $subject = str_replace($replaceA,$replace,$subject);
//        $subject = preg_replace($search, $replace, $subject, $limit);
        $subject = str_replace($htmA,$htmArray,$subject);
        $subject = str_replace($linkA,$linkArray,$subject);
        return $subject;
    }
}
function small($sfp,$w='',$h='',$scale=true) {
    $ext    = iFS::getext($sfp);
    if(strpos($sfp,'_')!==false)
        return $sfp;
    
    if(empty($sfp)){
        $twh    =iCMS::$config["FS"]['url'].'/1x1.gif';
    }else{
        $twh    = $sfp.'_'.$w.'x'.$h.'.jpg';
    }
    echo $twh;
}
function baiduping($href) {
    $url    ='http://ping.baidu.com/ping/RPC2';
    $postvar='<methodCall>
<methodName>weblogUpdates.extendedPing</methodName>
<params>
<param>
<value><string>'.iCMS::$config['site']['name'].'</string></value>
</param>
<param>
<value><string>'.iCMS::$config['router']['URL'].'</string></value>
</param>
<param>
<value><string>'.$href.'</string></value>
</param>
<param>
<value><string>'.iCMS::$config['router']['URL'].'/s/rss.php</string></value>
</param>
</params>
</methodCall>';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postvar);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml")); 
    $res = curl_exec ($ch);
    curl_close ($ch);
    var_dump($res);
    return $res;
}
function userData($uid,$type,$size=0){
    switch($type){
        case 'avatar':
            return rtrim(iCMS::$config['FS']['url'],'/').'/'.get_avatar($uid,$size);
        break;
        case 'url':
            $url = iPHP::router(array('/{uid}/',$uid),iCMS_REWRITE);
            return rtrim(iCMS::$config['router']['userURL'],'/').$url;
        break;
        case 'urls':
            $url = rtrim(iCMS::$config['router']['userURL'],'/');
            return array(
                'home'      => iPHP::router(array('/{uid}/',$uid,$url),iCMS_REWRITE),
                'favorite'  => iPHP::router(array('/{uid}/favorite/',$uid,$url),iCMS_REWRITE),
                'share'     => iPHP::router(array('/{uid}/share/',$uid,$url),iCMS_REWRITE),
                'follower'  => iPHP::router(array('/{uid}/follower/',$uid,$url),iCMS_REWRITE),
                'following' => iPHP::router(array('/{uid}/following/',$uid,$url),iCMS_REWRITE),
            );
        break;

    }
}
function autoformat2($html){
    $html   = stripslashes($html);
    $html   = preg_replace(array(
    '/on(load|click|dbclick|mouseover|mousedown|mouseup)="[^"]+"/is',
    '/<script[^>]*?>.*?<\/script>/si',
    '/<style[^>]*?>.*?<\/style>/si',
    '/<img[^>]+src=[" ]?([^"]+)[" ]?[^>]*>/is',
    '/<br[^>]*>/i',
    '/<div[^>]*>(.*?)<\/div>/is',
    '/<p[^>]*>(.*?)<\/p>/is'
    ),array('','','',"\n[img]$1[/img]","\n","$1\n","$1\n"),$html);

    $html   = str_replace("&nbsp;",'',$html);
    $html   = str_replace("　",'',$html);

    $html   = preg_replace(array(
    '/<b[^>]*>(.*?)<\/b>/i',
    '/<strong[^>]*>(.*?)<\/strong>/i'
    ),"[b]$1[/b]",$html);

    $html   = preg_replace('/<[\/\!]*?[^<>]*?>/is','',$html);
    $html   = preg_replace (array(
    '/\[img\](.*?)\[\/img\]/is',
    '/\[b\](.*?)\[\/b\]/is',
    '/\[url=([^\]|#]+)\](.*?)\[\/url\]/is',
    '/\[url=([^\]]+)\](.*?)\[\/url\]/is',
    ),array('<img src="$1" />','<strong>$1</strong>','<a href="$1">$2</a>','<a href="$1">$2</a>'),$html);
    $_htmlArray = explode("\n",$html);
    $_htmlArray = array_map("trim", $_htmlArray);
    $_htmlArray = array_filter($_htmlArray);
    $isempty    = false;
    $emptycount = 0;
    foreach($_htmlArray as $hkey=>$_html){
        if(empty($_html)){
            $emptycount++;
            $isempty    = true;
            $emptykey   = $hkey;
        }else{
            if($emptycount>1 && !$pbkey){
                $brkey  = $emptykey;
                $isbr   = true;
                $htmlArray[$emptykey]='<p><br /></p>';
            }
            $emptycount = 0;
            $emptykey   = 0;
            $isempty    = false;
            $pbkey      = false;
            $htmlArray[$hkey]   = '<p>'.$_html.'</p>';
        }
        if($_html=="#--iCMS.PageBreak--#"){
            unset($htmlArray[$brkey]);
            $pbkey              = $hkey;
            $htmlArray[$hkey]   = $_html;
        }
    }
    reset ($htmlArray);
    if(current($htmlArray)=="<p><br /></p>"){
        $fkey   = key($htmlArray);
        unset($htmlArray[$fkey]);
    }
    $html   = implode("",$htmlArray);
    return addslashes($html);
}