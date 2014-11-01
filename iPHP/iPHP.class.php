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
	public static $pagenav    = NULL;
	public static $offset     = NULL;
	public static $break      = true;
	public static $dialog     = array();
	public static $iTPL       = NULL;
	public static $iTPL_MODE  = null;
	public static $mobile     = false;
	public static $time_start = false;


	public static function iTemplate(){
        self::$iTPL = new iTemplate();
        self::$iTPL->template_dir      = iPHP_TPL_DIR;
        self::$iTPL->compile_dir       = iPHP_TPL_CACHE;
        self::$iTPL->left_delimiter    = '<!--{';
        self::$iTPL->right_delimiter   = '}-->';
        self::$iTPL->register_modifier("date", "get_date");
        self::$iTPL->register_modifier("cut", "csubstr");
        self::$iTPL->register_modifier("htmlcut","htmlcut");
        self::$iTPL->register_modifier("cnlen","cstrlen");
        self::$iTPL->register_modifier("html2txt","html2text");
        //self::$iTPL->register_modifier("pinyin","GetPinyin");
        self::$iTPL->register_modifier("unicode","get_unicode");
        self::$iTPL->register_modifier("small","gethumb");
        self::$iTPL->register_modifier("thumb","small");
        self::$iTPL->register_modifier("random","random");
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
        if(self::$iTPL_MODE=='html') {
            return self::$iTPL->fetch($tpl);
        }else {
            self::$iTPL->display($tpl);
            if(iPHP_DEBUG){
	            //echo '<span class="label label-success">内存:'.iFS::sizeUnit(memory_get_usage()).', 执行时间:'.iPHP::timer_stop().'s, SQL执行:'.iDB::$num_queries.'次</span>';
            }
        }
    }
    public static function view($tpl,$p='index') {
        $tpl OR iPHP::throwException('运行出错！ 请设置模板文件', '001','TPL');
        if(strpos($tpl,'APP:/')!==false){
            $tpl = 'file::'.self::$app_tpl."||".str_replace('APP:/','',$tpl);
        }else{
        	$tpl = self::$iTPL->get_tpl($tpl);
        }
        if(@is_file(iPHP_TPL_DIR."/".$tpl)) {
            return iPHP::pl($tpl);
        }else{
        	iPHP::throwException('运行出错！ 找不到模板文件 <b>' .$tpl. '</b>', '002','TPL');
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
		    if(@is_file($path)) {
		    	self::import($path);
		    }else{
		    	$msg OR $msg = 'file '.$path.' not exist';
				self::throwException($msg,0020);
		    }
	    }
	}

    public static function app($app = NULL,$args = NULL){
		$app_dir   = $app_name = $app;
		$file_type = 'app';
    	if(strpos($app,'.')!==false){
    		$app = explode('.', $app);
    		list($app_dir,$app_name,$file_type) = $app;
    		if(empty($file_type)){
    			$file_type= $app_name;
    			$app_name = $app_dir;
    		}
    	}
    	switch ($file_type) {
    		case 'class': $obj_name = $app_name;break;
    		case 'table':
				$obj_name = $app_name.'Table';
				$args     = "static";
    		break;
    		case 'func':
				$args     = "include";
    		break;
    		default:$obj_name = $app_name.'App';break;
    	}

    	self::import(iPHP_APP_DIR.'/'.$app_dir.'/'.$app_name.'.'.$file_type.'.php');

    	if($args==="include"||$args==="static") return;

    	if($args){
			return new $obj_name($args);
		}
		return new $obj_name();
    }
    public static function QRcode($content){
        self::import(iPHP_LIB.'/phpqrcode.php');
		$content = iS::escapeStr($content);
		$expires = 86400;
        header("Cache-Control: maxage=".$expires);
        header('Last-Modified: '.gmdate('D, d M Y H:i:s',time()).' GMT');
        header('Expires: '.gmdate('D, d M Y H:i:s',time()+$expires).' GMT');
        header('Content-type: image/png');
        QRcode::png($content, false, 'L', 6, 2);
    }
    public static function Markdown($content){
    	self::import(iPHP_LIB.'/Parsedown.php');
		$Parsedown = new Parsedown();
		$content   = str_replace(array(
		'#--iCMS.Markdown--#',
		'#--iCMS.PageBreak--#'
		),array('','@--iCMS.PageBreak--@'),$content);
		$content   = $Parsedown->text($content);
		$content   = str_replace('@--iCMS.PageBreak--@','#--iCMS.PageBreak--#',$content);
    	return $content;
    }
    public static function cleanHtml($content){
    	$content = stripslashes($content);

    	//echo $content,"\n\n\n\n\n\n\n\n";
    	self::import(iPHP_LIB.'/htmlpurifier-4.6.0/HTMLPurifier.auto.php');
		$config = HTMLPurifier_Config::createDefault();
		//$config->set('Cache.SerializerPath',iPHP_APP_CACHE);
		$config->set('Core.Encoding', 'UTF-8'); //字符编码

		//允许属性 div table tr td br元素
		$config->set('HTML.AllowedElements',array(
		    'ul'=>true,'ol'=>true,'li'=>true,
		    'br'=>true,'hr'=>true,'div'=>true,'p'=>true,
		    'strong'=>true,'em'=>true,'span'=>true,
		    'blockquote'=>true,'sub'=>true,'sup'=>true,
		    'img'=>true,'a'=>true,'embed'=>true,
		));
		// $config->set('HTML.AllowedAttributes', array(
		//     'img.src',
		//     'a.href','a.target',
		//     'embed.play','embed.loop', 'embed.menu',
		// ));
		$config->set('AutoFormat.AutoParagraph',true);
		$config->set('HTML.TidyLevel','medium');
		$config->set('Cache.DefinitionImpl',null);
		$config->set('AutoFormat.RemoveEmpty', true);
		//配置 允许flash
        $config->set('HTML.SafeEmbed',true);
        $config->set('HTML.SafeObject',true);
        $config->set('Output.FlashCompat',true);
		//允许<a>的target属性
		$def = $config->getHTMLDefinition(true);
        $def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
        $def->addAttribute('embed', 'play,', 'Enum#true,false');
        $def->addAttribute('embed', 'loop', 'Enum#true,false');
        $def->addAttribute('embed', 'menu', 'Enum#true,false');
        $def->addAttribute('embed', 'allowfullscreen', 'Enum#true,false');

		$htmlPurifier = new HTMLPurifier($config);
		$content = $htmlPurifier->purify($content);
		return addslashes($content);
    }

	public static function throwException($msg,$code,$name='',$h404=true) {
		if(!headers_sent() && $h404){
			self::http_status(404,$code);
		}
	    trigger_error(iPHP_APP.' '.$msg. '(' . $code . ')',E_USER_ERROR);
	}
	public static function p2num($path,$page=false){
		$page===false && $page	= $GLOBALS['page'];
		if($page<2){
			return str_replace(array('_{P}','&p={P}'),'',$path);
		}
		return str_replace('{P}',$page,$path);
	}

	public static function router($key,$static=false){
		if($static){
			$router = false;
		}else{
			$path   = iPHP_CONF_DIR.'/router.config.php';
			@is_file($path) OR self::throwException($path.' not exist',0013);
			$router = self::import($path,true);
		}
		return self::router_url($key,$router);
	}
	private static function router_url($key,$router=null){
		if(is_array($key)){
			$url = $router?$router[$key[0]]:$key[0];
			if(is_array($key[1])){ /* 多个{} 例:/{uid}/{cid}/ */
				preg_match_all('/\{(\w+)\}/i',$url, $matches);
				$url = str_replace($matches[0], $key[1], $url);
			}else{
				$url = preg_replace('/\{\w+\}/i',$key[1], $url);
			}
			$key[2] && $url = $key[2].$url;
		}else{
			$url = $router?$router[$key]:$key;
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
	    $_seccode = self::get_cookie('seccode');
	    $_seccode && $cookie_seccode = authcode($_seccode, 'DECODE');
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
		if (is_bool($vars)||empty($vars)) return '';

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
    public static function json($a,$break=true,$ret=false){
    	header("Access-Control-Allow-Origin: ".__HOST__);
    	$json	= json_encode($a);
    	$_GET['callback'] && $json = $_GET['callback'].'('.$json.')';
    	$_GET['script'] && exit("<script>{$json};</script>");
    	if($ret){
    		return $json;
    	}
    	echo $json;
    	$break && exit();
    }
    public static function js_callback($a,$callback=null,$node='parent'){
    	$callback===null && $callback = $_GET['callback'];
    	empty($callback) && $callback = 'callback';
    	$json = json_encode($a);
    	echo "<script>window.{$node}.{$callback}($json);</script>";
    	exit;
    }
    public static function code($code=0,$msg='',$forward='',$format=''){
    	strstr($msg,':') && $msg = self::lang($msg);
    	$a = array('code'=>$code,'msg'=>$msg,'forward'=>$forward);
    	if($format=='json'){
    		self::json($a);
    	}
        return $a;
    }
    public static function warning($info) {
    	iPHP::msg('warning:#:warning:#:'.$info);
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
				$A[1]=="0"	&& $code	= 'iTOP.history.go(-1);';
				$A[1]=="1"	&& $code	= 'iTOP.location.reload();';
        	break;
        	case 'url':
				$A[1]=="1" && $A[1]	= __REF__;
	        	$code = "iTOP.location.href='".$A[1]."';";
        	break;
        	case 'src':	$code = "iTOP.$('#iPHP_FRAME').attr('src','".$A[1]."');";break;
        	default:	$code = '';
        }

        if($ret) return $code;

        echo '<script type="text/javascript">'.$code.'</script>';
        self::$break && exit();
    }
	public static function alert($msg,$js=null,$s=3) {
		self::$dialog = array(
			'lock'   =>true,
			'width'  =>360,
			'height' =>120,
		);
		self::dialog('warning:#:warning:#:'.$msg,$js,$s);
    }
	public static function success($msg,$js=null,$s=3) {
		self::$dialog = array(
			'lock'   =>true,
			'width'  =>360,
			'height' =>120,
		);
		self::dialog('success:#:check:#:'.$msg,$js,$s);
    }
	public static function dialog($info=array(),$js='js:',$s=3,$buttons=null,$update=false) {
		$info    = (array)$info;
		$title   = $info[1]?$info[1]:'提示信息';
		$content = $info[0];
        strstr($content,':#:') && $content=self::msg($content,true);
		$content = addslashes('<table class="ui-dialog-table" align="center"><tr><td valign="middle">'.$content.'</td></tr></table>');
//		$content = addslashes($content);

//		$dialog = "var options = {id:'iPHP-DIALOG',width:320,height:110,autofocus:false,";
		//print_r(self::$dialog);
		$options = array(
			"id:'iPHP-DIALOG'","time:null",
			"title:'".(self::$dialog['title']?self::$dialog['title']:iPHP_APP)." - {$title}'",
			"lock:".(self::$dialog['lock']?'true':'false'),
			"width:'".(self::$dialog['width']?self::$dialog['width']:'auto')."'",
			"height:'".(self::$dialog['height']?self::$dialog['height']:'auto')."'",
			"api:'iPHP'",
		);
		//$content && $options[]="content:'{$content}'";
		$auto_func = 'd.close().remove();';
		$func      = self::js($js,true);
		if($func){
			$buttons OR $options[] ='okValue: "确 定",ok: function(){'.$func.';},';
			$auto_func = $func.'d.close().remove();';
		}
        if(is_array($buttons)) {
            $okbtn ="{value:'确 定',callback:function(){".$func."},autofocus: true}";
            foreach($buttons as $key=>$val) {
                $val['id']	 && $id   = "id:'".$val['id']."',";
            	$val['url']  && $func = "iTOP.location.href='{$val['url']}';";
            	$val['src']  && $func = "iTOP.$('#iPHP_FRAME').attr('src','{$val['src']}');return false;";
                $val['target'] && $func = "iTOP.window.open('{$val['url']}','_blank');";

                $buttonA[]="{".$id."value:'".$val['text']."',callback:function(){".$func."}}";
                $val['next'] && $auto_func = $func;
            }
			//$buttonA[] = $okbtn;
			$button    = implode(",",$buttonA);
      	}
      	$dialog = 'var iTOP = window.top,';
		if($update){
			$dialog   .= "d = iTOP.dialog.get('iPHP-DIALOG');";
			$auto_func = $func;
		}else{
			$dialog.= 'options = {'.implode(',', $options).'},d = iTOP.iCMS.dialog(options);';
	        // if(self::$dialog_lock){
	        // 	$dialog.='d.showModal();';
	        // }else{
	        // 	$dialog.='d.show();';
	        // }
    	}
		$button && $dialog.= "d.button([$button]);";
		$content&& $dialog.= "d.content('$content');";

        $s<=30	&& $timeout		= $s*1000;
        $s>30	&& $timeout		= $s;
        $s===false && $timeout	= false;
        if($timeout){
        	$dialog.='window.setTimeout(function(){'.$auto_func.'},'.$timeout.');';
        }else{
        	$update && $dialog.= $auto_func;
        }
		echo self::$dialog['code']?$dialog:'<script>'.$dialog.'</script>';
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
		$url       = buildurl($url,array('total_num'=>$total,'page'=>''));
	    self::$offset	= ($page-1)*$displaypg;
	    self::$offset<0 && self::$offset=0;
	    self::$pagenav="<ul><li><a href='{$url}1' target='_self'>首页</a></li>";
	    self::$pagenav.=$prepg?"<li><a href='{$url}$prepg' target='_self'>上一页</a></li>":'<li class="disabled"><a href="javascript:;">上一页</a></li>';
	    $flag=0;
	    for($i=$page-2;$i<=$page-1;$i++) {
	        if($i<1) continue;
	        self::$pagenav.="<li><a href='{$url}$i' target='_self'>$i</a></li>";
	    }
	    self::$pagenav.='<li class="active"><a href="javascript:;">'.$page.'</a></li>';
	    for($i=$page+1;$i<=$lastpg;$i++) {
	        self::$pagenav.="<li><a href='{$url}$i' target='_self'>$i</a></li>";
	        $flag++;
	        if($flag==4) break;
	    }
	    self::$pagenav.=$nextpg?"<li><a href='{$url}$nextpg' target='_self'>下一页</a></li>":'<li class="disabled"><a href="javascript:;">下一页</a></li>';
	    self::$pagenav.="<li><a href='{$url}$lastpg' target='_self'>末页</a></li>";
	    self::$pagenav.="<li> <span class=\"muted\">共{$total}{$unit}，{$displaypg}{$unit}/页 共{$lastpg}页</span></li>";
	    for($i=1;$i<=$lastpg;$i=$i+5) {
	        $s=$i==$page?' selected="selected"':'';
	        $select.="<option value=\"$i\"{$s}>$i</option>";
	    }
	    if($lastpg>200) {
	        self::$pagenav.="<li> <span class=\"muted\">跳到 <input type=\"text\" id=\"pageselect\" style=\"width:24px;height:12px;margin-bottom: 0px;line-height: 12px;\" /> 页 <input class=\"btn btn-small\" type=\"button\" onClick=\"window.location='{$url}'+$('#pageselect').val();\" value=\"跳转\" style=\"height: 22px;line-height: 18px;\"/></span></li>";
	    }else {
	        self::$pagenav.="<li> <span class=\"muted\">跳到 <select id=\"pageselect\" style=\"width:48px;height:20px;margin-bottom: 3px;line-height: 16px;padding: 0px\" onchange=\"window.location='{$url}'+this.value\">{$select}</select> 页</span></li>";
	    }
	    self::$pagenav.='</ul>';
	    //(int)$lastpg<2 &&UCP::$pagenav='';
	}
	public static function total($tnkey,$sql,$type=null){
		$tnkey = substr($tnkey,8,16);
		$total = (int)$_GET['total_num'];
    	if(empty($total) && $type===null &&!isset($_GET['total_cahce'])){
    		$total = (int)iCache::get('total/'.$tnkey);
		}
    	if(empty($total) ||$type==='nocache'||isset($_GET['total_cahce'])){
        	$total = iDB::value($sql);
        	if($type===null){
        		iCache::set('total/'.$tnkey,$total);
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
                $sFileType 	= @filetype($filepath);
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
        case E_USER_ERROR:         $html.="iPHP Error";             break;
        case E_USER_WARNING:       $html.="iPHP Warning";           break;
        case E_USER_NOTICE:        $html.="iPHP Notice";            break;
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
	$html = str_replace('\\','/',$html);
	$html = str_replace(iPATH,'iPHP://',$html);
	@header('HTTP/1.1 500 Internal Server Error');
	@header('Status: 500 Internal Server Error');
	@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	@header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	@header("Cache-Control: no-store, no-cache, must-revalidate");
	@header("Cache-Control: post-check=0, pre-check=0", false);
	@header("Pragma: no-cache");
    $_GET['frame'] OR exit($html);
    $html = str_replace("\n",'<br />',$html);
    iPHP::$dialog['lock'] = true;
    iPHP::dialog(array("warning:#:warning-sign:#:{$html}",'系统错误!可发邮件到 idreamsoft@qq.com 反馈错误!我们将及时处理'),'js:1',30);
    exit;
}
