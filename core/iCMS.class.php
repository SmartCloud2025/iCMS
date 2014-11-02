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
    public static $hooks       = array();

	public static function init(){
        self::config();
        iFS::init(self::$config['FS'],self::$config['watermark'],'filedata');
        iCache::init(self::$config['cache']);
        iURL::init(self::$config['router']);
        iPHP::iTemplate();

        iPHP_DEBUG      && iDB::$show_errors = true;
        iPHP_TPL_DEBUG  && iPHP::clear_compiled_tpl();

        define('iCMS_DIR',       self::$config['router']['DIR']);
        define('iCMS_URL',       self::$config['router']['URL']);
        define('iCMS_PUBLIC_URL',self::$config['router']['public_url']);
        define('iCMS_USER_URL',  self::$config['router']['user_url']);
        define('iCMS_FS_URL',    self::$config['FS']['url']);
        define('iCMS_REWRITE',   self::$config['router']['rewrite']);
        define('iCMS_API',       iCMS_PUBLIC_URL.'/api.php');
        define('iCMS_UI',        iCMS_DIR.'app/ui/common');
        define('iCMS_UI_URL',    iCMS_URL.'/app/ui/common');
        self::$apps = self::$config['apps'];
        self::assign_site();
	}
    private static function config(){
        $site   = iPHP_MULTI_SITE ? $_SERVER['HTTP_HOST']:iPHP_APP;
        if(iPHP_MULTI_DOMAIN){ //只绑定主域
            preg_match("/[^\.\/][\w\-]+\.[^\.\/]+$/", $site, $matches);
            $site = $matches[0];
        }
        strpos($site, '..') === false OR exit('<h1>What are you doing?(code:001)</h1>');

        //config.php 中开启iPHP_APP_CONF后 此处设置无效,
        define('iPHP_APP_CONF', iPHP_CONF_DIR.'/'.$site);//网站配置目录
        $app_config_file = iPHP_APP_CONF.'/config.php'; //网站配置文件
        @is_file($app_config_file) OR exit('<h1>'.iPHP_APP.' 运行出错.找不到"'.$site.'"网站的配置文件!(code:002)</h1>');
        $config = require $app_config_file;

        //config.php 中开启后 此处设置无效
        defined('iPHP_DEBUG')        OR define('iPHP_DEBUG', $config['debug']['php']);       //程序调试模式
        defined('iPHP_TPL_DEBUG')    OR define('iPHP_TPL_DEBUG',$config['debug']['tpl']);    //模板调试
        defined('iPHP_SQL_DEBUG')    OR define('iPHP_SQL_DEBUG',$config['debug']['sql']);    //模板调试
        defined('iPHP_TIME_CORRECT') OR define('iPHP_TIME_CORRECT',$config['time']['cvtime']);
        //config.php --END--

        define('iPHP_URL_404',$config['router']['404']);//404定义
        ini_set('display_errors','OFF');
        error_reporting(0);

        if(iPHP_DEBUG||iPHP_TPL_DEBUG){
            ini_set('display_errors','ON');
            error_reporting(E_ALL & ~E_NOTICE);
        }

        $timezone = $config['time']['zone'];
        $timezone OR $timezone = 'Asia/Shanghai';//设置中国时区
        @ini_set('date.timezone',$timezone);
        function_exists('date_default_timezone_set') && @date_default_timezone_set($timezone);

        self::multiple_device($config);
        self::$config = $config;
        //var_dump(self::$config['template']);
    }
    //多终端适配
    private static function multiple_device(&$config){
        $template = $config['template'];
        foreach ((array)$template['device'] as $key => $device) {
            $has_tpl = self::device_agent($device['ua']);
            if($device['tpl'] && $has_tpl){
                $device_name = $device['name'];
                $device_tpl  = $device['tpl'];
                $domain      = $device['domain'];
                break;
            }
        }

        //检查是否移动设备
        if(self::device_agent($template['mobile']['agent'])){
            $device_name = 'mobile';
            $mobile_tpl  = $template['mobile']['tpl'];
            $domain      = $template['mobile']['domain'];
        }

        if($device_tpl){ //设备模板
            $def_tpl = $device_tpl;
        }else{ //没有设置设备模板 但是移动设备
            $mobile_tpl && $def_tpl = $mobile_tpl;
        }

        if(empty($def_tpl)){
            $device_name = 'pc';
            $def_tpl     = $template['pc']['tpl'];
            $domain      = false;
        }
        define('iPHP_DEFAULT_TPL',$def_tpl);
        define('iPHP_MOBILE_TPL',$mobile_tpl);
        define('iPHP_DEVICE',$device_name);
        if($domain){
            $config['router'] = str_replace($config['router']['URL'], $domain, $config['router']);
        }
    }
    private static function device_agent($user_agent){
        $user_agent = str_replace(',','|',preg_quote($user_agent));
        return ($user_agent && preg_match('/'.$user_agent.'/i',$_SERVER["HTTP_USER_AGENT"]));
    }
    public static function assign_site(){
        $site          = self::$config['site'];
        $site['title'] = self::$config['site']['name'];
        $site['404']   = self::$config['router']['404'];
        $site['url']   = iCMS_URL;
        $site['tpl']   = iPHP_DEFAULT_TPL;
        $site['urls']  = array(
            "public" => iCMS_PUBLIC_URL,
            "user"   => iCMS_USER_URL,
            "res"    => iCMS_FS_URL,
            "ui"     => iCMS_PUBLIC_URL.'/ui',
            "avatar" => iCMS_FS_URL.'avatar/',
        );
        iPHP::assign('site',$site);
        iPHP::$dialog['title']  = self::$config['site']['name'];
    }
    /**
     * 运行应用程序
     * @param string $app 应用程序名称
     * @param string $do 动作名称
     * @return iCMS
     */
    public static function run($app = NULL,$do = NULL,$args = NULL,$prefix="do_") {
    	//empty($app) && $app	= $_GET['app']; //单一入口
    	if(empty($app)){
            $fi  = iFS::name(__SELF__);
            $app = $fi['name'];
    	}
		if (!in_array($app, self::$apps) && iPHP_DEBUG){
			iPHP::throwException('运行出错！找不到应用程序: <b>' . $app.'</b>', '0001');
		}
        self::$app_path   = iPHP_APP_DIR.'/'.$app;
        self::$app_file   = self::$app_path.'/'.$app.'.app.php';
        is_file(self::$app_file) OR iPHP::throwException('运行出错！找不到文件: <b>' . $app.'.app.php</b>', '0002');
        if($do===NULL){
            $do = iPHP_APP;
            $_GET['do'] && $do = iS::escapeStr($_GET['do']);
        }
        if($_POST['action']){
            $do     = iS::escapeStr($_POST['action']);
            $prefix = 'ACTION_';
        }

        self::$app_name   = $app;
        self::$app_do     = $do;
        self::$app_method = $prefix.$do;
        self::$app_tpl    = iPHP_APP_DIR.'/'.$app.'/template';
    	self::$app_vars   = array(
            'VERSION'    => iCMS_VER,
            "MOBILE"     => iPHP::$mobile,
            'API'        => iCMS_API,
            'UI'         => iCMS_UI,
            'UI_URL'     => iCMS_UI_URL,
            'SAPI'       => iCMS_API.'?app='.self::$app_name,
            'COOKIE_PRE' => iPHP_COOKIE_PRE,
            'REFER'      => __REF__,
            'CONFIG'     => self::$config,
            "APP"        => array(
                'NAME'   => self::$app_name,
                'DO'     => self::$app_do,
                'METHOD' => self::$app_method
            ),
            "APPID"        => array(
                'ARTICLE'  => iCMS_APP_ARTICLE,
                'CATEGORY' => iCMS_APP_CATEGORY,
                'TAG'      => iCMS_APP_TAG,
                'PUSH'     => iCMS_APP_PUSH,
                'COMMENT'  => iCMS_APP_COMMENT,
                'PROP'     => ICMS_APP_PROP,
                'MESSAGE'  => iCMS_APP_MESSAGE,
                'FAVORITE' => iCMS_APP_FAVORITE,
                'USER'     => iCMS_APP_USER,
            ),

        );
        define('iCMS_API_URL', iCMS_API.'?app='.self::$app_name);
        iPHP::$iTPL->_iTPL_VARS = self::$app_vars;
        self::$app = iPHP::app($app);
		if(self::$app_do && self::$app->methods){
			in_array(self::$app_do, self::$app->methods) OR iPHP::throwException('运行出错！ <b>' .self::$app_name. '</b> 类中找不到方法定义: <b>'.self::$app_method.'</b>', '0003');
			$method = self::$app_method;
            $args===null && $args = self::$app_args;
			if($args){
                if($args==='object'){
                    return self::$app;
                }
				return self::$app->$method($args);
			}else{
				return self::$app->$method();
			}
		}else{
			iPHP::throwException('运行出错！ <b>' .self::$app_name. '</b> 类中 <b>'. self::$app_method.'</b> 方法不存在', '0004');
		}

    }
    public static function API($app = NULL,$do = NULL) {
        $app OR $app = iS::escapeStr($_GET['app']);
    	self::run($app,null,null,'API_');
    }
    //------------------------------------
    public static function hits_sql($all=true){
        $timeline = self::timeline();
        //var_dump($timeline);
        $pieces = array();
        $all && $pieces[] = '`hits` = hits+1';
        foreach ($timeline as $key => $bool) {
            $field = "hits_{$key}";
            if($key=='yday'){
                if($bool==1){
                    $pieces[]="`hits_yday` = hits_today";
                }elseif ($bool>1) {
                    $pieces[]="`hits_yday` = 0";
                }
                continue;
            }
            $pieces[]="`{$field}` = ".($bool?"{$field}+1":'1');
        }
        return implode(',', $pieces);
    }
    public static function timeline(){
        $_timeline = iCache::get('iCMS/timeline');
        //list($_today,$_week,$_month) = $_timeline ;
        $time     = $_SERVER['REQUEST_TIME'];
        $today    = get_date($time,"Ymd");
        $yday     = get_date($time-86400+1,"Ymd");
        $week     = get_date($time,"YW");
        $month    = get_date($time,"Ym");
        $timeline = array($today,$week,$month);
        $_timeline[1]==$today OR iCache::set('iCMS/timeline',$timeline,86400);
        //var_dump($_timeline,$timeline);
        return array(
            'yday'  => ($today-$_timeline[0]),
            'today' => ($_timeline[0]==$today),
            'week'  => ($_timeline[1]==$week),
            'month' => ($_timeline[2]==$month),
        );
    }
    public static function app_ref($app_name=true,$out=false) {
        $app_name===true && $app_name = self::$app_name;
        $rs    = iPHP::get_vars($app_name);
        $param = array();
        switch ($app_name) {
            case 'article':
                $param = array(
                    'suid'  => (int)$rs['userid'],
                    'iid'   => (int)$rs['id'],
                    'cid'   => (int)$rs['cid'],
                    'appid' => iCMS_APP_ARTICLE,
                    'title' => $rs['title'],
                );
            break;
            case 'category':
                $param = array(
                    'suid'  => (int)$rs['userid'],
                    'iid'   => (int)$rs['cid'],
                    'cid'   => (int)$rs['rootid'],
                    'appid' => iCMS_APP_CATEGORY,
                    'title' => $rs['name'],
                );
            break;
            case 'tag':
                $param = array(
                    'suid'  => (int)$rs['uid'],
                    'iid'   => (int)$rs['id'],
                    'cid'   => (int)$rs['cid'],
                    'appid' => iCMS_APP_TAG,
                    'title' => $rs['name'],
                );
            break;
        }
        // if($out==='js'){
        //     if($param){
        //          echo '<script type="text/javascript"> var comment_param = '.json_encode($param).';</script>';
        //     }
        //     return;
        // }
        return $param;
    }

    public static function get_category_ids($cid = "0",$all=true,$root_array=null) {
        $root_array OR $root_array = iCache::get('iCMS/category/rootid');
        $cids = array();
        is_array($cid) OR $cid = explode(',', $cid);
        foreach($cid AS $_id) {
            $cids+=(array)$root_array[$_id];
        }
        if($all){
            foreach((array)$cids AS $_cid) {
                $root_array[$_cid] && $cids+= self::get_category_ids($_cid,$all,$root_array);
            }
        }
        $cids = array_unique($cids);
        $cids = array_filter($cids);
        return $cids;
    }
    public static function sphinx(){
    	iPHP::import(iPHP_APP_CORE.'/sphinx.class.php');

		if(isset($GLOBALS['iSPH'])) return $GLOBALS['iSPH'];

		$hosts				= self::$config['sphinx']['host'];
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
    public static function hooks($key,$array){
        self::$hooks[$key]  = $array;
    }
    //------------------------------------
    public static function gotohtml($fp,$url='') {
        if(iPHP::$iTPL_MODE=='html'||empty($url)||stristr($url, '.php?')||iPHP_DEVICE!='pc') return;

        @is_file($fp) && iPHP::gotourl($url);
    }
    //翻页函数
    public static function page($a) {
        iPHP::loadClass("Pages");
        $lang   = iPHP::lang('iCMS:page');
        $iPages = new iPages($a,$lang);
        if($iPages->totalpage>1) {
            $pagenav = $a['pagenav']?strtoupper($a['pagenav']):'NAV';
            $pnstyle = $a['pnstyle']?$a['pnstyle']:0;
            iPHP::$iTPL->_iTPL_VARS['PAGE']  = array(
                $pagenav  =>$iPages->show($pnstyle),
                'COUNT'   =>$a['total'],
                'TOTAL'   =>$iPages->totalpage,
                'CURRENT' =>$iPages->nowindex,
                'NEXT'    =>$iPages->next_page()
            );
            iPHP::$iTPL->_iTPL_VARS['PAGES'] = $iPages;
        }
        return $iPages;
    }
    public static function set_html_url($iurl){
        if(isset($GLOBALS['iPage'])) return;

        $GLOBALS['iPage']['url']  = $iurl->pageurl;
        $GLOBALS['iPage']['html'] = array('enable'=>true,'index'=>$iurl->href,'ext'=>$iurl->ext);
    }
    //过滤
    public static function filter(&$content){
	    $cache		= iCache::get(array('iCMS/word.filter','iCMS/word.disable'));
	    $filter		= $cache['iCMS/word.filter'];//filter过滤
	    $disable    = $cache['iCMS/word.disable'];//disable禁止
        //禁止关键词
        $subject = $content;
        $pattern = '/(~|`|!|@|\#|\$|%|\^|&|\*|\(|\)|\-|=|_|\+|\{|\}|\[|\]|;|:|"|\'|<|>|\?|\/|,|\.|\s|\n|。|，|、|；|：|？|！|…|-|·|ˉ|ˇ|¨|‘|“|”|々|～|‖|∶|＂|＇|｀|｜|〃|〔|〕|〈|〉|《|》|「|」|『|』|．|〖|〗|【|】|（|）|［|］|｛|｝|°|′|″|＄|￡|￥|‰|％|℃|¤|￠|○|§|№|☆|★|○|●|◎|◇|◆|□|■|△|▲|※|→|←|↑|↓|〓|＃|＆|＠|＾|＿|＼|№|)*/i';
        $subject = preg_replace($pattern, '', $subject);
        foreach ((array)$disable AS $val) {
            $val = trim($val);
            if(strpos($val,'::')!==false){
                list($tag,$start,$end) = explode('::',$val);
                if($tag=='NUM'){
                    $subject = cnum($subject);
                    if (preg_match('/\d{'.$start.','.$end.'}/i', $subject)) {
                        return $val;
                    }
                }
            }else{
                if ($val && preg_match("/".preg_quote($val, '/')."/i", $subject)) {
                    return $val;
                }
            }
        }
        //过滤关键词
        foreach ((array)$filter AS $k =>$val) {
            empty($val[1]) && $val[1]='***';
            $val[0] && $content = preg_replace("/".preg_quote($val[0], '/')."/i",$val[1],$content);
        }
    }

    public static function str_replace_limit($search, $replace, $subject, $limit=-1) {
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
