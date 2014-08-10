<?php
/**
* iPHP - i PHP Framework
* Copyright (c) 2012 iiiphp.com. All rights reserved.
*
* @author coolmoo <iiiphp@qq.com>
* @site http://www.iiiphp.com
* @licence http://www.iiiphp.com/license
* @version 1.0.1
* @package common
* @$Id: iPHP.php 2330 2014-01-03 05:19:07Z coolmoo $
*/
defined('iPHP') OR exit('What are you doing?');

class iPHP{
	public static $pagenav      = NULL;
	public static $offset       = NULL;
	public static $break        = true;
	public static $dialogTitle  = 'iPHP';
	public static $dialogCode   = false;
	public static $dialogLock   = false;
	public static $dialogObject = 'parent.';
	public static $iTPL         = NULL;
	public static $iTPLMode     = null;
	public static $mobile       = false;
	public static $time_start   = false;

	public static function config(){
        $site   = iPHP_MULTI_SITE ? $_SERVER['HTTP_HOST']:iPHP_APP;
        if(iPHP_MULTI_DOMAIN){ //只绑定主域
            preg_match("/[^\.\/]+\.[^\.\/]+$/", $site, $matches);
            $site = $matches[0];
        }
        strpos($site, '..') === false OR exit('<h1>What are you doing?(code:0001)</h1>');

        //config.php 中开启iPHP_APP_CONF后 此处设置无效,
        define('iPHP_APP_CONF', iPHP_CONF_DIR.'/'.$site);//网站配置目录
        $app_config_file = iPHP_APP_CONF.'/config.php'; //网站配置文件
        @is_file($app_config_file) OR exit('<h1>'.iPHP_APP.' 运行出错.找不到"'.$site.'"网站的配置文件!(code:0002)</h1>');
        $config = require $app_config_file;

        //config.php 中开启后 此处设置无效
        defined('iPHP_DEBUG')       OR define('iPHP_DEBUG', $config['debug']['php']);       //程序调试模式
        defined('iPHP_TPL_DEBUG')   OR define('iPHP_TPL_DEBUG',$config['debug']['tpl']);    //模板调试
        defined('iPHP_TIME_CORRECT')OR define('iPHP_TIME_CORRECT',$config['time']['cvtime']);
        //config.php --END--

        define('iPHP_URL_404',$config['router']['404']);//404定义

        if(iPHP_DEBUG||iPHP_TPL_DEBUG){
            ini_set('display_errors','ON');
            error_reporting(E_ALL & ~E_NOTICE);
        	set_error_handler('iPHP_ERROR_HANDLER');
        }

        $timezone = $config['time']['zone'];
        $timezone OR $timezone = 'Asia/Shanghai';//设置中国时区
        @ini_set('date.timezone',$timezone);
        function_exists('date_default_timezone_set') && @date_default_timezone_set($timezone);

        self::multiple_device($config);
        return $config;
	}
	private static function multiple_device(&$config){
		$template = $config['template'];
		foreach ((array)$template['device'] as $key => $device) {
			$has_tpl = self::device_agent($device['ua']);
        	if($device['tpl'] && $has_tpl){
				$device_name = $device['name'];
				$def_tpl     = $device['tpl'];
				$def_domain  = $device['domain'];
        		break;
        	}
		}
		if(empty($def_tpl)){
			if(self::device_agent($template['mobile']['agent'])){
				$device_name = 'mobile';
				$def_tpl     = $template['mobile']['tpl'];
				$def_domain  = $template['mobile']['domain'];
			}
		}
		if(empty($def_tpl)){
			$device_name = 'pc';
			$def_tpl     = $template['pc']['tpl'];				
			$def_domain  = false;
		}
        define('iPHP_TPL_DEFAULT',$def_tpl);
        if($def_domain){
			$_router_url      = $config['router']['URL'];
			$config['router'] = str_replace($_router_url, $def_domain, $config['router']);
        }
	}
	private static function device_agent($user_agent){
		$user_agent = str_replace(',','|',preg_quote($user_agent));
		return ($user_agent && preg_match('/'.$user_agent.'/i',$_SERVER["HTTP_USER_AGENT"]));
	}
	public static function iTemplate(){
        $iTPL                    = new iTemplate();
        $iTPL->template_dir      = iPHP_TPL_DIR;
        $iTPL->compile_dir       = iPHP_TPL_CACHE;
        $iTPL->left_delimiter    = '<!--{';
        $iTPL->right_delimiter   = '}-->';
        $iTPL->register_modifier("date", "get_date");
        $iTPL->register_modifier("cut", "csubstr");
        $iTPL->register_modifier("htmlcut","htmlcut");
        $iTPL->register_modifier("count","cstrlen");
        $iTPL->register_modifier("html2txt","html2text");
        //$iTPL->register_modifier("pinyin","GetPinyin");
        $iTPL->register_modifier("unicode","get_unicode");
        $iTPL->register_modifier("small","gethumb");
        $iTPL->register_modifier("thumb","small");
        $iTPL->register_modifier("random","random");
        self::$iTPL = $iTPL;
        return $iTPL;
	}
    public static function get_vars($key=null){
        return self::$iTPL->get_template_vars($key);
    }
    public static function clear_compiled_tpl($file = null){
    	self::$iTPL->clear_compiled_tpl($file);
    }
    public static function assign($key,$value) {
        self::$iTPL->assign($key,$value);
    }
    public static function append($key, $value=null, $merge=false) {
        self::$iTPL->append($key,$value,$merge);
    }
    public static function clear($key) {
        self::$iTPL->clear_assign($key);
    }
    public static function display($tpl){
    	self::$iTPL->display($tpl);
    }
    public static function fetch($tpl){
    	return self::$iTPL->fetch($tpl);
    }
    public static function pl($tpl) {
        if(self::$iTPLMode=='html') {
            return self::$iTPL->fetch($tpl);
        }else {
            self::$iTPL->display($tpl);
            if(iPHP_DEBUG){
	            echo '<span class="label label-success">内存:'.iFS::sizeUnit(memory_get_usage()).', 执行时间:'.iPHP::timer_stop().'s, SQL执行:'.iDB::$num_queries.'次</span>';           	
            }
        }
    }
    public static function view($tpl,$p='index') {
        $tpl OR iPHP::throwException('应用程序运行出错. 请设置模板文件', 0010,'TPL');
        if(strpos($tpl,'APP:/')!==false){
            $tpl = 'file::'.self::$app_tpl."||".str_replace('APP:/','',$tpl);
            return iPHP::pl($tpl);
        }

        strpos($tpl,iPHP_APP.':/') !==false && $tpl = str_replace(iPHP_APP.':/',iPHP_APP,$tpl);
        strpos($tpl,'iTPL:/') !==false && $tpl = str_replace('iTPL:/',iPHP_TPL_DEFAULT,$tpl);
        strpos($tpl,'{iTPL}') !==false && $tpl = str_replace('{iTPL}',iPHP_TPL_DEFAULT,$tpl);

        if(@is_file(iPHP_TPL_DIR."/".$tpl)) {
            return iPHP::pl($tpl);
        }else{
        	iPHP::throwException('应用程序运行出错. 找不到模板文件 <b>' .$tpl. '</b>', 0011,'TPL');
        }
    }

	public static function PG($key){
		return isset($_POST[$key])?$_POST[$key]:$_GET[$key];
	}
	// 获取客户端IP
	public static function getIp($format=0) {
	    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
	        $onlineip = getenv('HTTP_CLIENT_IP');
	    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
	        $onlineip = getenv('HTTP_X_FORWARDED_FOR');
	    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
	        $onlineip = getenv('REMOTE_ADDR');
	    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
	        $onlineip = $_SERVER['REMOTE_ADDR'];
	    }
	    preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
	    $ip = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
	    if($format) {
	        $ips = explode('.', $ip);
	        for($i=0;$i<3;$i++) {
	            $ips[$i] = intval($ips[$i]);
	        }
	        return sprintf('%03d%03d%03d', $ips[0], $ips[1], $ips[2]);
	    } else {
	        return $ip;
	    }
	}
	//设置COOKIE
	public static function set_cookie($name, $value = "", $time = 0) {
	    $cookiedomain	= iPHP_COOKIE_DOMAIN;
	    $cookiepath		= iPHP_COOKIE_PATH;
	    $cookietime		= ($time?$time:iPHP_COOKIE_TIME);
	    $name 			= iPHP_COOKIE_PRE.'_'.$name;
	    $_COOKIE[$name] = $value;
	    setcookie($name, $value,time()+$cookietime,$cookiepath, $cookiedomain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
	}
	//取得COOKIE
	public static function get_cookie($name) {
	    $name	= iPHP_COOKIE_PRE.'_'.$name;
	    if (isset($_COOKIE[$name])) {
	        return $_COOKIE[$name];
	    }
	    return FALSE;
	}
    public static function getUniCookie($s){
		$s = str_replace('\\\u','\\u',self::get_cookie($s));
		$u = json_decode('["'.$s.'"]');
		return $u[0];
    }
    public static function import($path,$dump=false){
		$key	= str_replace(iPATH,'iPHP://',$path);
		if($dump){
			if(!isset($GLOBALS['_iPHP_REQ'][$key])){
				$GLOBALS['_iPHP_REQ'][$key] = include $path;
			}
			return $GLOBALS['_iPHP_REQ'][$key];
		}

      	if(isset($GLOBALS['_iPHP_REQ'][$key])) return;

		$GLOBALS['_iPHP_REQ'][$key] = true;
		require $path;
    }
	public static function loadClass($name,$msg=''){
		if (!class_exists($name)){
		    $path = iPHP_CORE.'/i'.$name.'.class.php';
			$msg && self::throwException($msg,0020);
		    self::import($path);
	    }
	}

    public static function app($app = NULL,$args = NULL){
    	$app_dir	= $app_name = $app;
    	if(is_array($app)){
    		$app_dir	= $app[0];
    		$app_name	= $app[1];
    	}
    	self::import(iPHP_APP_DIR.'/'.$app_dir.'/'.$app_name.'.app.php');
    	$app_name	= $app_name.'App';
    	if($args){
			return new $app_name($args);
		}
		return new $app_name();
    }
    public static function appFunc($func = NULL){
    	$func_dir	= $func_name = $func;
    	if(is_array($func)){
    		$func_dir	= $func[c0];
    		$func_name	= $func[1];
    	}
    	self::import(iPHP_APP_DIR.'/'.$func_dir.'/'.$func_name.'.tpl.php');
    }
    public static function appClass($class = NULL,$args = NULL){
    	$class_dir	= $class_name = $class;
    	if(is_array($class)){
    		$class_dir	= $class[0];
    		$class_name	= $class[1];
    	}
    	self::import(iPHP_APP_DIR.'/'.$class_dir.'/'.$class_name.'.class.php');

    	if($args==="import") return;

    	if($args){
			return new $class_name($args);
		}
		return new $class_name();
    }
	public static function throwException($msg, $code,$name='',$h404=true) {
		if(!headers_sent() && $h404){
			self::http_status(404,$code);
		}
	    trigger_error('<B>iPHP '.$name.' Fatal Error:</B>'.$msg. '(' . $code . ')',E_USER_ERROR);
	}
	public static function p2num($path,$page=false){
		$page===false && $page	= $GLOBALS['page'];
		if($page<2){
			return str_replace(array('_{P}','&p={P}'),'',$path);
		}
		return str_replace('{P}',$page,$path);
	}
	public static function page($iurl){
		if(isset($GLOBALS['iPage'])) return;

		$GLOBALS['iPage']['url']  = $iurl->pageurl;
		$GLOBALS['iPage']['html'] = array('enable'=>true,'index'=>$iurl->href,'ext'=>$iurl->ext);
	}
	public static function router($key,$static=false){
		if($static) return $key;

		$path   = iPHP_CONF_DIR.'/iRouter.config.php';
		@is_file($path) OR self::throwException($path.' not exist',0013);

		$router = self::import($path,true);

		if(is_array($key)){
			if(is_array($key[1])){
				$url = $router[$key[0]];
				preg_match_all('/\{(\w+)\}/i',$url, $matches);
				$url = str_replace($matches[0], $key[1], $url);
			}else{
				$url = preg_replace('/\{\w+\}/i',$key[1], $router[$key[0]]);
			}
			$key[2] && $url = $key[2].$url;
		}else{
			$url = $router[$key];
		}
		return $url;
	}
    public static function lang($string='') {
    	if(empty($string)) return false;

		$keyArray  = explode(':',$string);
		$count     = count($keyArray);
		list($app,$do,$key,$msg) = $keyArray;

		$fname     = $app.'.lang.php';
		$path      = iPHP_APP_CORE.'/lang/'.$fname;

		@is_file($path) OR self::throwException($fname.' not exist',0015);

		$langArray = self::import($path,true);

		switch ($count) {
			case 1:return $langArray;
			case 2:return $langArray[$do];
			case 3:return $langArray[$do][$key];
			case 4:return $langArray[$do][$key][$msg];
		}
    }
	//检查验证码
	public static function seccode($seccode,$type='F') {
	    $_seccode		= self::get_cookie('seccode');
	    $cookie_seccode = empty($_seccode)?'':authcode($_seccode, 'DECODE');
	    if(empty($cookie_seccode) || strtolower($cookie_seccode) != strtolower($seccode)) {
	        return false;
	    }else {
	        return true;
	    }
	}
	public static function http404($v,$code="",$b=true){
		if(empty($v)){
			self::http_status(404,$code);
			defined('iPHP_URL_404') && self::gotourl(iPHP_URL_404);
			$b && exit();
		}
	}

	public static function http_status($code,$ECODE='') {
	    static $_status = array(
	        // Success 2xx
	        200 => 'OK',
	        // Redirection 3xx
	        301 => 'Moved Permanently',
	        302 => 'Moved Temporarily ',  // 1.1
	        // Client Error 4xx
	        400 => 'Bad Request',
	        403 => 'Forbidden',
	        404 => 'Not Found',
	        // Server Error 5xx
	        500 => 'Internal Server Error',
	        503 => 'Service Unavailable',
	    );
	    if(isset($_status[$code])) {
	        header('HTTP/1.1 '.$code.' '.$_status[$code]);
	        // 确保FastCGI模式下正常
	        header('Status:'.$code.' '.$_status[$code]);
			$ECODE && header("X-iPHP-ECODE:".$ECODE);
	    }
	}

	public static function where($vars,$field,$not=false,$noand=false) {
		if (is_bool($vars)) return '';

	    if(is_array($vars)) {
			foreach ($vars as $key => $value) {
				$vas[] = "'".addslashes($value)."'";
			}
			$vars = implode(',',$vas);
			$sql  = $not?" NOT IN ($vars)":" IN ($vars) ";
	    }else {
			$vars = addslashes($vars);
			$sql  = $not?"<>'$vars' ":"='$vars' ";
	    }
	    $sql = "`{$field}`".$sql;
	    if($noand){
	    	return $sql;
	    }
	    $sql = ' AND '.$sql;
	    return $sql;
	}
	public static function str2time($str="0") {
		$correct     = 0;
		$str OR $str ='now';
		$time        = strtotime($str);
		(int)iPHP_TIME_CORRECT && $correct = (int)iPHP_TIME_CORRECT*60;
	    return $time+$correct;
	}
    public static function json($a,$break=true,$ret=false){
    	$callback	= $_GET['callback'];
    	header("Access-Control-Allow-Origin: ".__HOST__);
    	$json	= json_encode($a);
    	$callback && $json	=$callback.'('.$json.')';
    	if($ret){
    		return $json;
    	}
    	echo $json;
    	$break && exit();
    }
    /**
     * Starts the timer, for debugging purposes
     */
    public static function timer_start() {
        $mtime = microtime();
        $mtime = explode(' ', $mtime);
        self::$time_start = $mtime[1] + $mtime[0];
    }

    /**
     * Stops the debugging timer
     * @return int total time spent on the query, in milliseconds
     */
    public static function timer_stop() {
        $mtime = microtime();
        $mtime = explode(' ', $mtime);
        $time_end = $mtime[1] + $mtime[0];
        $time_total = $time_end - self::$time_start;
        //self::$time_start = $time_end;
        return round($time_total,4);
    }
    public static function code($code=0,$msg='',$forward='',$format=''){
    	strstr($msg,':') && $msg = self::lang($msg);
    	$a = array('code'=>$code,'msg'=>$msg,'forward'=>$forward);
    	if($format=='json'){
    		self::json($a);
    	}
        return $a;
    }
    public static function msg($info,$ret=false) {
    	list($label,$icon,$content)= explode(':#:',$info);
    	$msg = '<div class="iPHP-msg"><span class="label label-'.$label.'">';
    	$icon && $msg.= '<i class="fa fa-'.$icon.'"></i> ';
    	if(preg_match('/([a-zA-Z]+):([a-zA-Z]+)/i', $content)){
    		$lang = self::lang($content);
    		$lang && $content = $lang;
    	}
    	$msg.= $content.'</span></div>';
    	if($ret) return $msg;
    	echo $msg;
    }
	public static function js($js="js:",$ret=false) {
        $A		= explode(':',$js);
        switch ($A[0]){
        	case 'js':
				$A[1] 		&& $code	= $A[1];
				$A[1]=="0"	&& $code	= self::$dialogObject.'history.go(-1);';
				$A[1]=="1"	&& $code	= self::$dialogObject.'location.reload();';
        	break;
        	case 'url':
				$A[1]=="1" && $A[1]	= __REF__;
	        	$code	= self::$dialogObject."location.href='".$A[1]."';";
        	break;
        	case 'src':	$code	= self::$dialogObject."$('#iPHP_FRAME').attr('src','".$A[1]."');";break;
        	default:	$code	= '';
        }

        if($ret) return $code;

        echo '<script type="text/javascript">'.$code.'</script>';
        self::$break && exit();
    }
	public static function alert($msg,$js=null,$s=3) {
		self::$dialogLock = true;
		self::dialog('warning:#:warning:#:'.$msg,$js,$s);
    }
	public static function success($msg,$js=null,$s=3) {
		self::$dialogLock = true;
		self::dialog('success:#:check:#:'.$msg,$js,$s);
    }
	public static function dialog($info=array(),$js='js:',$s=3,$buttons=null,$update=false) {
		$info    = (array)$info;
		$title   = $info[1]?$info[1]:'提示信息';
		$content = $info[0];
        strstr($content,':#:') && $content=self::msg($content,true);
		$content = addslashes($content);
		$dialog  = "var dialog = ".self::$dialogObject."$.dialog({
		    id: 'iPHP_DIALOG',width: 360,height: 150,fixed: true,
		    title: '".self::$dialogTitle." - {$title}',content: '{$content}',";
		$auto_func = 'dialog.close();';
		$func      = self::js($js,true);
		if($func){
      		$dialog.='cancelValue: "确定",cancel: function(){'.$func.'return true;},';
      		$auto_func = $func.'dialog.close();';
		}
        if(is_array($buttons)) {
            foreach($buttons as $key=>$val) {
            	$val['url'] && $func 	= self::$dialogObject."location.href='{$val['url']}';";
            	$val['src'] && $func 	= self::$dialogObject."$('#iPHP_FRAME').attr('src','".$val['src']."');return false;";
                $val['top'] && $func 	= "top.window.open('{$val['url']}','_blank');";
                $val['id']	&& $id		= "id: '".$val['id']."',";
                $buttonA[]="{{$id}value: '".$val['text']."',callback: function () {".$func."}}";
                $val['next'] && $auto_func = $func;
            }
            $button	= implode(',',$buttonA);
      	}
		$dialog.="});";
        if($update){
        	$dialog	= "var dialog = ".self::$dialogObject."$.dialog.get('PHP_DIALOG');";
			$dialog.="dialog.content('{$content}');";
			$auto_func = $func;
        }
		$button	&& $dialog.="dialog.button(".$button.");";
        self::$dialogLock && $dialog.='dialog.lock();';
        $s<=30	&& $timeount	= $s*1000;
        $s>30	&& $timeount	= $s;
        $s===false && $timeount	= false;
        if($timeount){
        	$dialog.='window.setTimeout(function(){'.$auto_func.'},'.$timeount.');';
        }else{
        	$update && $dialog.=$auto_func;
        }
		echo self::$dialogCode?$dialog:'<script type="text/javascript">'.$dialog.'</script>';
        self::$break && exit();
    }

	//翻页函数
	public static function pagenav($total,$displaypg=20,$unit="条记录",$url='',$target='') {
		$displaypg = intval($displaypg);
		$page      = $GLOBALS["page"]?intval($GLOBALS["page"]):1;
		$lastpg    = ceil($total/$displaypg); //最后页，也是总页数
		$page      = min($lastpg,$page);
		$prepg     = (($page-1)<0)?"0":$page-1; //上一页
		$nextpg    = ($page==$lastpg ? 0 : $page+1); //下一页
		$url       = buildurl($url,array('totalNum'=>$total,'page'=>''));
	    self::$offset	= ($page-1)*$displaypg;
	    self::$offset<0 && self::$offset=0;
	    self::$pagenav="<ul><li><a href='{$url}1' target='_self'>首页</a></li>";
	    self::$pagenav.=$prepg?"<li><a href='{$url}$prepg' target='_self'>上一页</a></li>":'<li class="disabled"><a href="#">上一页</a></li>';
	    $flag=0;
	    for($i=$page-2;$i<=$page-1;$i++) {
	        if($i<1) continue;
	        self::$pagenav.="<li><a href='{$url}$i' target='_self'>$i</a></li>";
	    }
	    self::$pagenav.='<li class="active"><a href="#">'.$page.'</a></li>';
	    for($i=$page+1;$i<=$lastpg;$i++) {
	        self::$pagenav.="<li><a href='{$url}$i' target='_self'>$i</a></li>";
	        $flag++;
	        if($flag==4) break;
	    }
	    self::$pagenav.=$nextpg?"<li><a href='{$url}$nextpg' target='_self'>下一页</a></li>":'<li class="disabled"><a href="#">下一页</a></li>';
	    self::$pagenav.="<li><a href='{$url}$lastpg' target='_self'>末页</a></li>";
	    self::$pagenav.="<li> <span class=\"muted\">共{$total}{$unit}，{$displaypg}{$unit}/页 共{$lastpg}页</span></li>";
	    for($i=1;$i<=$lastpg;$i=$i+5) {
	        $s=$i==$page?' selected="selected"':'';
	        $select.="<option value=\"$i\"{$s}>$i</option>";
	    }
	    if($lastpg>200) {
	        self::$pagenav.="<li> <span class=\"muted\">跳到 <input type=\"text\" id=\"pageselect\" style=\"width:24px;height:12px;margin-bottom: 0px;line-height: 12px;\" /> 页 <input class=\"btn btn-small\" type=\"button\" onClick=\"window.location='{$url}'+$('#pageselect').val();\" value=\"跳转\" style=\"height: 22px;line-height: 18px;\"/></span></li>";
	    }else {
	        self::$pagenav.="<li> <span class=\"muted\">跳到 <select id=\"pageselect\" style=\"width:48px;height:20px;margin-bottom: 3px;line-height: 16px;padding: 0px\" onchange=\"window.location='{$url}'+this.value\">{$select}</select>页</span></li>";
	    }
	    self::$pagenav.='</ul>';
	    //(int)$lastpg<2 &&UCP::$pagenav='';
	}
	public static function total($tnkey,$sql,$type=null){
    	$tnkey	= substr($tnkey,8,16);
    	$total	= (int)$_GET['totalNum'];
    	if(empty($total) && $type!='G'){
//    		$total	= (int)self::get_cookie($tnkey);
    		$total	= (int)iCache::get('total/'.$tnkey);
		}
    	if(empty($total) || $GLOBALS['removeTotal']){
        	$total	= iDB::value($sql);
        	//echo iDB::$last_query;
        	if($type!='G'){
        		iCache::set('total/'.$tnkey,$total);
        		//self::set_cookie($tnkey,$total,3600);
        	}
        }
        return $total;
	}
	public static function gotourl($URL=''){
	    $URL OR $URL=__REF__;
	    if(headers_sent()){
	         echo '<meta http-equiv=\'refresh\' content=\'0;url='.$URL.'\'><script type="text/javascript">window.location.replace(\''.$URL.'\');</script>';
	   	}else {
	        header("Location: $URL");
	    }
		exit;
	}
    //获取文件夹列表
    public static function folder($dir='',$type=NULL) {
    	$dir	= trim($dir,'/');
    	$sDir	= $dir;
    	$_GET['dir'] && $gDir	= trim($_GET['dir'],'/');



//    	print_r('$dir='.$dir.'<br />');
//    	print_r('$gDir='.$gDir.'<br />');

    	//$gDir && $dir	= $gDir;

        //strstr($dir,'.')!==false	&& self::alert('What are you doing?','',1000000);
        //strstr($dir,'..')!==false	&& self::alert('What are you doing?','',1000000);


        $sDir_PATH	= iFS::path_join(iPATH,$sDir);
        $iDir_PATH	= iFS::path_join($sDir_PATH,$gDir);

//    	print_r('$sDir_PATH='.$sDir_PATH."\n");
//    	print_r('$iDir_PATH='.$iDir_PATH."\n");

		strpos($iDir_PATH,$sDir_PATH)===false && self::alert("对不起!您访问的目录有问题!");

        if (!is_dir($iDir_PATH)) {
            return false;
        }

		$url	= buildurl(false,'dir');
        if ($handle = opendir($iDir_PATH)) {
            while (false !== ($rs = readdir($handle))) {
//				print_r('$rs='.$rs."\n");
            	$filepath	= iFS::path_join($iDir_PATH,$rs);
				$filepath	= rtrim($filepath,'/');
//				print_r('$filepath='.$filepath."\n");
                $sFileType 	= filetype($filepath);
//				print_r('$sFileType='.$sFileType."\n");
				$path		= str_replace($sDir_PATH, '', $filepath);
                if ($sFileType	=="dir" && !in_array($rs,array('.','..','admincp'))) {
                    $dirArray[]	= array('path'=>$path,'name'=>$rs,'url'=>$url.urlencode($path));
                }
                if ($sFileType	=="file" && !in_array($rs,array('..','.iPHP'))) {
                	$filext		= iFS::getExt($rs);
	                $fileinfo	= array(
	                		'path'=>$path,
	                		'dir'=>dirname($path),
	                        'url'=>iFS::fp($path,'+http'),
	                        'name'=>$rs,
	                        'modified'=>get_date(filemtime($filepath),"Y-m-d H:i:s"),
	                        'md5'=>md5_file($filepath),
	                        'ext'=>$filext,
	                        'size'=>iFS::sizeUnit(filesize($filepath))
	                );
	                if($type){
	                	 in_array(strtolower($filext),$type) && $fileArray[]	= $fileinfo;
	                }else{
	                	$fileArray[]	= $fileinfo;
	                }
                }
            }
        }
		$a['DirArray']  = (array)$dirArray;
		$a['FileArray'] = (array)$fileArray;
		$a['pwd']       = str_replace($sDir_PATH, '', $iDir_PATH);
		$a['pwd']       = trim($a['pwd'],'/');
		$pos            = strripos($a['pwd'],'/');
		$a['parent']    = ltrim(substr($a['pwd'],0,$pos), '/');
		$a['URI']       = $url;
//    	print_r($a);
//    	exit;
        return $a;
    }
}

function iPHP_ERROR_HANDLER($errno, $errstr, $errfile, $errline){
    $errno = $errno & error_reporting();
    if($errno == 0) return;
    defined('E_STRICT') OR define('E_STRICT', 2048);
    defined('E_RECOVERABLE_ERROR') OR define('E_RECOVERABLE_ERROR', 4096);
    $html="<pre>\n<b>";
    switch($errno){
        case E_ERROR:              $html.="Error";                  break;
        case E_WARNING:            $html.="Warning";                break;
        case E_PARSE:              $html.="Parse Error";            break;
        case E_NOTICE:             $html.="Notice";                 break;
        case E_CORE_ERROR:         $html.="Core Error";             break;
        case E_CORE_WARNING:       $html.="Core Warning";           break;
        case E_COMPILE_ERROR:      $html.="Compile Error";          break;
        case E_COMPILE_WARNING:    $html.="Compile Warning";        break;
        case E_USER_ERROR:         $html.="User Error";             break;
        case E_USER_WARNING:       $html.="User Warning";           break;
        case E_USER_NOTICE:        $html.="User Notice";            break;
        case E_STRICT:             $html.="Strict Notice";          break;
        case E_RECOVERABLE_ERROR:  $html.="Recoverable Error";      break;
        default:                   $html.="Unknown error ($errno)"; break;
    }
    $html.=":</b> $errstr\n";
    if(function_exists('debug_backtrace')){
        //print "backtrace:\n";
        $backtrace = debug_backtrace();
        array_shift($backtrace);
        foreach($backtrace as $i=>$l){
            $html.="[$i] in function <b>{$l['class']}{$l['type']}{$l['function']}</b>";
            $l['file'] && $html.=" in <b>{$l['file']}</b>";
            $l['line'] && $html.=" on line <b>{$l['line']}</b>";
            $html.="\n";
        }
    }
    $html.="\n</pre>";
    $html	= str_replace('\\','/',$html);
    $html	= str_replace(iPATH,'iPHP://',$html);
	@header('HTTP/1.1 500 Internal Server Error');
	@header('Status: 500 Internal Server Error');
	@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	@header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	@header("Cache-Control: no-store, no-cache, must-revalidate");
	@header("Cache-Control: post-check=0, pre-check=0", false);
	@header("Pragma: no-cache");
    $_GET['frame'] OR exit($html);
    $html	= str_replace("\n",'<br />',$html);
    iPHP::$dialogLock	= true;
    iPHP::dialog(array("warning:#:warning-sign:#:".$html,'系统错误!可发邮件到 idreamsoft@qq.com 反馈错误!我们将及时处理'),'js:1',30);
    exit;
}
