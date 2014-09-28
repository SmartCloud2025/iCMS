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

	public static function Init(){
        self::$config = iPHP::config();
        iFS::init(self::$config['FS'],'filedata');
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
        define('iCMS_REWRITE',   self::$config['app']['rewrite']);
        define('iCMS_API',       iCMS_PUBLIC_URL.'/api.php');
        define('iCMS_UI',        iCMS_DIR.'app/ui/common');
        define('iCMS_UI_URL',    iCMS_URL.'/app/ui/common');
        self::$apps = self::$config['apps'];
        self::assign_site();
	}
    public static function assign_site(){
        iPHP::assign('site',array(
            "title"       => self::$config['site']['name'],
            "seotitle"    => self::$config['site']['seotitle'],
            "keywords"    => self::$config['site']['keywords'],
            "description" => self::$config['site']['description'],
            "icp"         => self::$config['site']['icp'],
            '404'         => self::$config['router']['404'],
            'url'         => iCMS_URL,
            "tpl"         => iPHP_TPL_DEFAULT,
            'urls'        => array(
                    "public" => iCMS_PUBLIC_URL,
                    "user"   => iCMS_USER_URL,
                    "res"    => iCMS_FS_URL,
                    "ui"     => iCMS_PUBLIC_URL.'/ui',
                    "avatar" => iCMS_FS_URL.'avatar/',
			)
		));
        iPHP::$dialog_title  = self::$config['site']['name'];
    }
    /**
     * 运行应用程序
     * @param string $app 应用程序名称
     * @param string $do 动作名称
     * @return iCMS
     */
    public static function run($app = NULL,$do = NULL,$prefix="do_") {
    	//empty($app) && $app	= $_GET['app']; //单一入口
    	if(empty($app)){
            $fi  = iFS::name(__SELF__);
            $app = $fi['name'];
    	}
		if (!in_array($app, self::$apps) && iPHP_DEBUG){
			iPHP::throwException('运行出错！找不到应用程序: <b>' . $app.'</b>', 0001);
		}
        self::$app_path   = iPHP_APP_DIR.'/'.$app;
        self::$app_file   = self::$app_path.'/'.$app.'.app.php';
        is_file(self::$app_file) OR iPHP::throwException('运行出错！找不到文件: <b>' . self::$app_name.'.app.php</b>', 0002);
        $do OR $do        = $_GET['do']?(string)$_GET['do']:iPHP_APP;
        if($_POST['action']){
            $do     = $_POST['action'];
            $prefix = 'ACTION_';
        }
        self::$app_name   = $app;
        self::$app_do     = $do;
        self::$app_method = $prefix.$do;
        self::$app_tpl    = iPHP_APP_DIR.'/'.$app.'/template';
    	self::$app_vars   = array(
            'version'    => iCMS_VER,
            "is_mobile"  => iPHP::$mobile,
            'API'        => iCMS_API,
            'UI'         => iCMS_UI,
            'UI_URL'     => iCMS_UI_URL,
            'SAPI'       => iCMS_API.'?app='.self::$app_name,
            'COOKIE_PRE' => iPHP_COOKIE_PRE,
            'refer'      => __REF__,
            'config'     => self::$config,
            "APP"        => array(
                'name'   => self::$app_name,
                'do'     => self::$app_do,
                'method' => self::$app_method
            ),
        );
        define('iCMS_API_URL', iCMS_API.'?app='.self::$app_name);

        iPHP::$iTPL->_iTPL_VARS = self::$app_vars;
        self::$app = iPHP::app($app);
		if(self::$app_do && self::$app->methods){
			in_array(self::$app_do, self::$app->methods) OR iPHP::throwException('运行出错！ <b>' .self::$app_name. '</b> 类中找不到方法定义: <b>'.self::$app_method.'</b>', 0003);
			$method = self::$app_method;
			$args 	= self::$app_args;
			if ($args){
				return self::$app->$method($args);
			}else{
				return self::$app->$method();
			}
		}else{
			iPHP::throwException('运行出错！ <b>' .self::$app_name. '</b> 类中 <b>do'. self::$app_do.'</b> 方法不存在', 0004);
		}

    }
    public static function API($app = NULL,$do = NULL) {
        $app OR $app = iS::escapeStr($_GET['app']);
    	self::run($app,null,'API_');
    }
    //------------------------------------
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
    public static function get_category_lite($c){
        $category = array();
        $category['name']        = $c['name'];
        $category['description'] = $c['description'];
        $category['sname']       = $c['subname'];
        $category['pic']         = $c['pic'];
        $category['url']         = $c['iurl']->href;
        $category['link']        = "<a href='{$category['url']}'>{$c['name']}</a>";
        return $category;
    }
    public static function get_category_ids($cid = "0",$all=true) {
        $cids   = array();
        $cArray = iCache::get('iCMS/category/rootid',$cid);
        foreach((array)$cArray AS $_cid) {
            $cids[] = $_cid;
            $all && $cids[]  = self::get_category_ids($_cid,$all);
        }
        $cids = array_unique($cids);
        $cids = array_filter($cids);
        return $cids;
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
    public static function hooks($key,$array){
        self::$hooks[$key]  = $array;
    }
    //------------------------------------
    public static function gotohtml($fp,$url='',$fmode='0') {
    	if(iPHP::$iTPL_MODE=='html') return;

        ($fmode==1 && @is_file($fp) && stristr($fp, '.php?') === FALSE) && iPHP::gotourl($url);
    }
    //翻页函数
    public static function page($a) {
        iPHP::loadClass("Pages");
        $lang   = iPHP::lang('iCMS:page');
        $iPages = new iPages($a,$lang);
        if($iPages->totalpage>1) {
            $pagenav = $a['pagenav']?$a['pagenav']:'nav';
            $pnstyle = $a['pnstyle']?$a['pnstyle']:0;
            iPHP::$iTPL->_iTPL_VARS['page']  = array('count'=>$a['total'],'total'=>$iPages->totalpage,'current'=>$iPages->nowindex,$pagenav=>$iPages->show($pnstyle));
            iPHP::$iTPL->_iTPL_VARS['PAGES'] = $iPages;
        }
        return $iPages;
    }
    public static function setpage($iurl){
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
	//内链
    public static function keywords($a) {
        if(self::$config['other']['kwCount']==0) return $a;

        $keywords	= iCache::get('iCMS/keywords');
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
