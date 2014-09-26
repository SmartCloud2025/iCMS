<?php
/**
* iPHP - i PHP Framework
* Copyright (c) 2012 iiiphp.com. All rights reserved.
*
* @author coolmoo <iiiphp@qq.com>
* @site http://www.iiiphp.com
* @licence http://www.iiiphp.com/license
* @version 1.0.1
 */
if (!function_exists('get_magic_quotes_gpc')) {
	function get_magic_quotes_gpc(){
		return false;
	}
}
if (!function_exists('gc_collect_cycles')) {
	function gc_collect_cycles(){
		return false;
	}
}

function buildurl($url=false,$qs='') {
	$url	OR $url	= $_SERVER["REQUEST_URI"];
	$urlA	= parse_url($url);
	parse_str($urlA['query'], $query1);
    $query2 = $qs;
    is_array($qs) OR parse_str($qs, $query2);
	$query         = array_merge($query1,$query2);
	$urlA['query'] = http_build_query($query);
	$nurl          = glue_url($urlA);
	return $nurl?$nurl:$url;
}
function glue_url($parsed) {
    if (!is_array($parsed)) return false;

	$uri = isset($parsed['scheme']) ? $parsed['scheme'].':'.((strtolower($parsed['scheme']) == 'mailto') ? '':'//'): '';
	$uri .= isset($parsed['user']) ? $parsed['user'].($parsed['pass']? ':'.$parsed['pass']:'').'@':'';
	$uri .= isset($parsed['host']) ? $parsed['host'] : '';
	$uri .= isset($parsed['port']) ? ':'.$parsed['port'] : '';
	$uri .= isset($parsed['path']) ? $parsed['path'] : '';
	$uri .= isset($parsed['query']) ? '?'.$parsed['query'] : '';
	$uri .= isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';
	return $uri;
}


function bitscale($a) {
	$a['th']==0 && $a['th']=9999;
	if( $a['w']/$a['h'] > $a['tw']/$a['th']  && $a['w'] >$a['tw'] ){
		$a['h'] = ceil($a['h'] * ($a['tw']/$a['w']));
		$a['w'] = $a['tw'];
	}else if( $a['w']/$a['h'] <= $a['tw']/$a['th'] && $a['h'] >$a['th']){
		$a['w'] = ceil($a['w'] * ($a['th']/$a['h']));
		$a['h'] = $a['th'];
	}
	return $a;
}
function num10K($num){
    if($num<10000){
        return $num;
    }else{
        return round($num/10000,1) . 'K';
    }
}
function format_date($date,$isShowDate=true){
    $limit = time() - $date;
    if($limit < 60){
        return '刚刚';
    }
    if($limit >= 60 && $limit < 3600){
        return floor($limit/60) . '分钟之前';
    }
    if($limit >= 3600 && $limit < 86400){
        return floor($limit/3600) . '小时之前';
    }
    if($limit >= 86400 and $limit<259200){
        return floor($limit/86400) . '天之前';
    }
    if($limit >= 259200 and $isShowDate){
        return get_date($date,'Y-m-d H:i');
    }else{
        return '';
    }
}
// 格式化时间
function get_date($timestamp=0,$format='') {
	$correct = 0;
	$format OR $format            = iPHP_DATE_FORMAT;
	$timestamp OR $timestamp      = time();
	(int)iPHP_TIME_CORRECT && $correct = (int)iPHP_TIME_CORRECT*60;
    return date($format,$timestamp+$correct);
}
//中文长度
function cstrlen($str) {
    return csubstr($str,'strlen');
}
//中文截取
function csubstr($str,$len,$end=''){
	$len!='strlen' && $len=$len*2;
    //获取总的字节数
    $ll = strlen($str);
    //字节数
    $i = 0;
    //显示字节数
    $l = 0;
    //返回的字符串
    $s = $str;
    while ($i < $ll)  {
        //获取字符的asscii
        $byte = ord($str{$i});
        //如果是1字节的字符
        if ($byte < 0x80)  {
            $l++;
            $i++;
        }elseif ($byte < 0xe0){  //如果是2字节字符
            $l += 2;
            $i += 2;
        }elseif ($byte < 0xf0){   //如果是3字节字符
            $l += 2;
            $i += 3;
        }else{  //其他，基本用不到
            $l += 2;
            $i += 4;
        }
        if($len!='strlen'){
	        //如果显示字节达到所需长度
	        if ($l >= $len){
	            //截取字符串
	            $s = substr($str, 0, $i);
	            //如果所需字符串字节数，小于原字符串字节数
	            if($i < $ll){
	                //则加上省略符号
	                $s = $s . $end; break;
	            }
	            //跳出字符串截取
	            break;
	        }
        }
    }
    //返回所需字符串
    return $len!='strlen'?$s:$l;
}

//截取HTML
function htmlcut($content,$maxlen=300,$suffix=FALSE) {
	$content   = preg_split("/(<[^>]+?>)/si",$content, -1,PREG_SPLIT_NO_EMPTY| PREG_SPLIT_DELIM_CAPTURE);
	$wordrows  = 0;
	$outstr    = "";
	$wordend   = false;
	$beginTags = 0;
	$endTags   = 0;
    foreach($content as $value) {
        if (trim($value)=="") continue;

        if (strpos(";$value","<")>0) {
            if (!preg_match("/(<[^>]+?>)/si",$value) && cstrlen($value)<=$maxlen) {
                $wordend=true;
                $outstr.=$value;
            }
            if ($wordend==false) {
                $outstr.=$value;
                if (!preg_match("/<img([^>]+?)>/is",$value)&& !preg_match("/<param([^>]+?)>/is",$value)&& !preg_match("/<!([^>]+?)>/is",$value)&& !preg_match("/<br([^>]+?)>/is",$value)&& !preg_match("/<hr([^>]+?)>/is",$value)&&!preg_match("/<\/([^>]+?)>/is",$value)) {
                    $beginTags++;
                }else {
                    if (preg_match("/<\/([^>]+?)>/is",$value,$matches)) {
                        $endTags++;
                    }
                }
            }else {
                if (preg_match("/<\/([^>]+?)>/is",$value,$matches)) {
                    $endTags++;
                    $outstr.=$value;
                    if ($beginTags==$endTags && $wordend==true) break;
                }else {
                    if (!preg_match("/<img([^>]+?)>/is",$value) && !preg_match("/<param([^>]+?)>/is",$value) && !preg_match("/<!([^>]+?)>/is",$value) && !preg_match("/<[br|BR]([^>]+?)>/is",$value) && !preg_match("/<hr([^>]+?)>/is",$value)&& !preg_match("/<\/([^>]+?)>/is",$value)) {
                        $beginTags++;
                        $outstr.=$value;
                    }
                }
            }
        }else {
            if (is_numeric($maxlen)) {
                $curLength=cstrlen($value);
                $maxLength=$curLength+$wordrows;
                if ($wordend==false) {
                    if ($maxLength>$maxlen) {
                        $outstr.=csubstr($value,$maxlen-$wordrows,FALSE,0);
                        $wordend=true;
                    }else {
                        $wordrows=$maxLength;
                        $outstr.=$value;
                    }
                }
            }else {
                if ($wordend==false) $outstr.=$value;
            }
        }
    }
    while(preg_match("/<([^\/][^>]*?)><\/([^>]+?)>/is",$outstr)) {
        $outstr=preg_replace_callback("/<([^\/][^>]*?)><\/([^>]+?)>/is","strip_empty_html",$outstr);
    }
    if (strpos(";".$outstr,"[html_")>0) {
        $outstr=str_replace("[html_&lt;]","<",$outstr);
        $outstr=str_replace("[html_&gt;]",">",$outstr);
    }
    if($suffix&&cstrlen($outstr)>=$maxlen)$outstr.="．．．";
    return $outstr;
}
//去掉多余的空标签
function strip_empty_html($matches) {
    $arr_tags1=explode(" ",$matches[1]);
    if ($arr_tags1[0]==$matches[2]) {
        return "";
    }else {
        $matches[0]=str_replace("<","[html_&lt;]",$matches[0]);
        $matches[0]=str_replace(">","[html_&gt;]",$matches[0]);
        return $matches[0];
    }
}

function sechtml($string) {
	$search  = array("/\s+/","/<(\/?)(script|iframe|style|object|html|body|title|link|meta|\?|\%)([^>]*?)>/isU","/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU");
	$replace = array(" ","&lt;\\1\\2\\3&gt;","\\1\\2",);
	$string  = preg_replace ($search, $replace, $string);
    return $string;
}
//HTML TO TEXT
function html2text($string) {
    if(is_array($string)) {
        foreach($string as $key => $val) {
            $string[$key] = html2text($val);
        }
    } else {
		$search  = array ("'<script[^>]*?>.*?</script>'si","'<[\/\!]*?[^<>]*?>'si","'([\r\n])[\s]+'","'&(quot|#34);'i","'&(amp|#38);'i","'&(lt|#60);'i","'&(gt|#62);'i","'&(nbsp|#160);'i","'&(iexcl|#161);'i","'&(cent|#162);'i","'&(pound|#163);'i","'&(copy|#169);'i","'&#(\d+);'e");
		$replace = array ("", "", "\\1", "\"", "&", "<", ">", " ", chr(161), chr(162), chr(163), chr(169), "chr(\\1)");
		$string  = preg_replace ($search, $replace, $string);
    }
    return $string;
}
function html2js($string) {
    if(is_array($string)) {
        foreach($string as $key => $val) {
            $string[$key] = html2js($val);
        }
    } else {
        $string = str_replace(array("\n","\r","\\","\""), array(' ',' ',"\\\\","\\\""), $string);
    }
    return $string;
}
function dhtmlspecialchars($string) {
    if(is_array($string)) {
        foreach($string as $key => $val) {
            $string[$key] = dhtmlspecialchars($val);
        }
    } else {
    	$string = str_replace(array("\0","%00"),'',$string);
        $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
                str_replace(array('&', '"',"'", '<', '>'), array('&amp;', '&quot;','&#039;', '&lt;', '&gt;'), $string));
    }
    return $string;
}
function unhtmlspecialchars($string) {
    if(is_array($string)) {
        foreach($string as $key => $val) {
            $string[$key] = unhtmlspecialchars($val);
        }
    } else {
        $string = str_replace (array('&amp;','&#039;','&quot;','&lt;','&gt;'), array('&','\'','\"','<','>'), $string );
    }
    return $string;
}

function random($length, $numeric = 0) {
    if($numeric) {
        $hash = sprintf('%0'.$length.'d', rand(0, pow(10, $length) - 1));
    } else {
		$hash  = '';
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
		$max   = strlen($chars) - 1;
        for($i = 0; $i < $length; $i++) {
            $hash .= $chars[rand(0, $max)];
        }
    }
    return $hash;
}
function get_user_dir($uid,$dir='avatar'){
    $nuid = abs(intval($uid));
    $nuid = sprintf("%08d", $nuid);
    $dir1 = substr($nuid, 0, 3);
    $dir2 = substr($nuid, 3, 2);
    $path = $dir.'/'.$dir1.'/'.$dir2;
    return $path;
}
function get_user_file($uid,$size=0,$dir='avatar') {
    $path = get_user_dir($uid,$dir).'/'.$uid.".jpg";
	if ($size) {
		$path.= '_'.$size.'x'.$size.'.jpg';
	}
	return $path;
}

function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length   = 8;
	$key           = md5($key ? $key : iPHP_KEY);
	$keya          = md5(substr($key, 0, 16));
	$keyb          = md5(substr($key, 16, 16));
	$keyc          = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey      = $keya.md5($keya.$keyc);
	$key_length    = strlen($cryptkey);

	$string        = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result        = '';
	$box           = range(0, 255);

	$rndkey        = array();
    for($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for($j = $i = 0; $i < 256; $i++) {
		$j       = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp     = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
    }

    for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a       = ($a + 1) % 256;
		$j       = ($j + $box[$a]) % 256;
		$tmp     = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result  .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if($operation == 'DECODE') {
        if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}
function strEX($haystack, $needle) {
    return !(strpos($haystack, $needle) === FALSE);
}
function array_diff_values($N, $O){
 	$diff['+'] = array_diff($N, $O);
 	$diff['-'] = array_diff($O, $N);
    return $diff;
}
function _int($n) {
    return 0-$n;
}
function get_dir_name($path=null){
	if (!empty($path)) {
		if (strpos($path,'\\')!==false) {
			return substr($path,0,strrpos($path,'\\')).'/';
		} elseif (strpos($path,'/')!==false) {
			return substr($path,0,strrpos($path,'/')).'/';
		}
	}
	return './';
}
function get_unicode($string){
	if(empty($string)) return;

	$array = (array)$string;
	$json  = json_encode($array);
	return str_replace(array('["','"]'), '', $json);
}
function utf2uni($c) {
    switch(strlen($c)) {
        case 1:
            return ord($c);
        case 2:
            $n = (ord($c[0]) & 0x3f) << 6;
            $n += ord($c[1]) & 0x3f;
            return $n;
        case 3:
            $n = (ord($c[0]) & 0x1f) << 12;
            $n += (ord($c[1]) & 0x3f) << 6;
            $n += ord($c[2]) & 0x3f;
            return $n;
        case 4:
            $n = (ord($c[0]) & 0x0f) << 18;
            $n += (ord($c[1]) & 0x3f) << 12;
            $n += (ord($c[2]) & 0x3f) << 6;
            $n += ord($c[3]) & 0x3f;
            return $n;
    }
}
function pinyin($str,$split="",$pn=true) {
    if(!isset($GLOBALS["iPHP.PY"])) {
        $GLOBALS["iPHP.PY"] = unserialize(gzuncompress(iFS::read(iPHP_PATH.'/pinyin.table')));
    }
    preg_match_all('/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/',trim($str),$match);
    $s = $match[0];
    $c = count($s);
    for ($i=0;$i<$c;$i++) {
        $uni = strtoupper(dechex(utf2uni($s[$i])));
        if(strlen($uni)>2) {
			$pyArr = $GLOBALS["iPHP.PY"][$uni];
			$py    = is_array($pyArr)?$pyArr[0]:$pyArr;
            $pn && $py=str_replace(array('1','2','3','4','5'), '', $py);
            $zh && $split && $R[]=$split;
			$R[]  = strtolower($py);
			$zh   = true;
			$az09 = false;
        }else if(preg_match("/[a-z0-9]/i",$s[$i])) {
            $zh && $i!=0 && !$az09 && $split && $R[]=$split;
			$R[]  = $s[$i];
			$zh   = true;
			$az09 = true;
        }else {
            $sp=true;
            if($split){
                if($s[$i]==' ') {
                    $R[]=$sp?'':$split;
                    $sp=false;
                }else {
                    $R[]=$sp?$split:'';
                    $sp=true;
                }
            }else {
                $R[]='';
            }
			$zh   = false;
			$az09 = false;
        }
    }
    return str_replace(array('Üe','Üan','Ün','lÜ','nÜ'),array('ue','uan','un','lv','nv'),implode('',(array)$R));
}
