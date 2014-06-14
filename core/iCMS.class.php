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
        iPHP::iTPL();

        iPHP_DEBUG      && iDB::$show_errors = true;
        iPHP_TPL_DEBUG  && iPHP::clear_compiled_tpl();
        
        define('iCMS_PUBLIC', self::$config['router']['publicURL']);
        define('iCMS_API', iCMS_PUBLIC.'/api.php');
        define('iCMS_URL', self::$config['router']['URL']);
        define('iCMS_REWRITE', self::$config['app']['rewrite']);
        self::$apps   = self::$config['apps'];
        self::assign_site();
	}
    public static function assign_site(){
        $router = self::$config['router'];
        iPHP::assign('site',array(
            "title"       => self::$config['site']['name'],
            "seotitle"    => self::$config['site']['seotitle'],
            "keywords"    => self::$config['site']['keywords'],
            "description" => self::$config['site']['description'],
            "tpl"         => self::$config['site'][(iPHP::$mobile?'MW_TPL':'PC_TPL')],
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
            $fi  = iFS::name(__SELF__);
            $app = $fi['name'];
    	}
		if (!in_array($app, self::$apps) && iPHP_DEBUG){
			iPHP::throwException('应用程序运行出错.找不到应用程序: <b>' . $app.'</b>', 1001);
		}
        self::$app_path   = iPHP_APP_DIR.'/'.$app;
        self::$app_file   = self::$app_path.'/'.$app.'.app.php';
        is_file(self::$app_file) OR iPHP::throwException('应用程序运行出错.找不到文件: <b>' . self::$app_name.'.app.php</b>', 1002);
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
        iPHP::$iTPL->_iTPL_VARS = self::$app_vars;

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
    //------------------------------------
	public static function getIds($id = "0",$all=true) {
	    $ids	= array();
	    $cArray	= iCache::get('iCMS/category/rootid',$id);
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
    public static function hooks($key,$array){
        self::$hooks['app'] = $key;
        self::$hooks[$key]  = $array;
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
	    $cache		= iCache::get(array('iCMS/word.filter','iCMS/word.disable'));
	    $filter		= $cache['iCMS/word.filter'];//filter过滤
	    $disable    = $cache['iCMS/word.disable'];//disable禁止
        //禁止关键词
        $subject = $content;
        $pattern = '/(~|`|!|@|\#|\$|%|\^|&|\*|\(|\)|\-|=|_|\+|\{|\}|\[|\]|;|:|"|\'|<|>|\?|\/|,|\.|\s|\n|。|，|、|；|：|？|！|…|-|·|ˉ|ˇ|¨|‘|“|”|々|～|‖|∶|＂|＇|｀|｜|〃|〔|〕|〈|〉|《|》|「|」|『|』|．|〖|〗|【|】|（|）|［|］|｛|｝|°|′|″|＄|￡|￥|‰|％|℃|¤|￠|○|§|№|☆|★|○|●|◎|◇|◆|□|■|△|▲|※|→|←|↑|↓|〓|＃|＆|＠|＾|＿|＼|№|)*/i';
        $subject = preg_replace($pattern, '', $subject);
        foreach ((array)$disable AS $val) {
            if(strpos($val,'::')!==false){
                list($tag,$start,$end) = explode('::',$val);
                if($tag=='NUM'){
                    $subject = cnNum($subject);
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
function cnNum($subject){
    $searchList = array(
        array('ⅰ','ⅱ','ⅲ','ⅳ','ⅴ','ⅵ','ⅶ','ⅷ','ⅸ','ⅹ'),
        array('㈠','㈡','㈢','㈣','㈤','㈥','㈦','㈧','㈨','㈩'),
        array('①','②','③','④','⑤','⑥','⑦','⑧','⑨','⑩'),
        array('一','二','三','四','五','六','七','八','九','十'),
        array('零','壹','贰','叁','肆','伍','陆','柒','捌','玖','拾'),
        array('Ⅰ','Ⅱ','Ⅲ','Ⅳ','Ⅴ','Ⅵ','Ⅶ','Ⅷ','Ⅸ','Ⅹ','Ⅺ','Ⅻ'),
        array('⑴','⑵','⑶','⑷','⑸','⑹','⑺','⑻','⑼','⑽','⑾','⑿','⒀','⒁','⒂','⒃','⒄','⒅','⒆','⒇'),
        array('⒈','⒉','⒊','⒋','⒌','⒍','⒎','⒏','⒐','⒑','⒒','⒓','⒔','⒕','⒖','⒗','⒘','⒙','⒚','⒛')
    );
    $replace = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20);
    foreach ($searchList as $key => $search) {
        $subject = str_replace($search, $replace, $subject);
    }

    return $subject;
}