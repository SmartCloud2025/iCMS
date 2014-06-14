<?php
defined('iPHP') OR exit('Access Denied');
/**
 * Basic Security Filter Service
 * @author liuhui@2010-6-30 zzZero.L@2010-9-15
 * @status building
 * @from phpwind
 */
class iS {
	/**
	 * 整型数过滤
	 * @param $param
	 * @return int
	 */
	function int($param) {
		return intval($param);
	}
	/**
	 * 字符过滤
	 * @param $param
	 * @return string
	 */
	function str($param) {
		return trim($param);
	}
	/**
	 * 是否对象
	 * @param $param
	 * @return boolean
	 */
	function isObj($param) {
		return is_object($param) ? true : false;
	}
	/**
	 * 是否数组
	 * @param $params
	 * @return boolean
	 */
	function isArray($params) {
		return (!is_array($params) || !count($params)) ? false : true;
	}
	/**
	 * 变量是否在数组中存在
	 * @param $param
	 * @param $params
	 * @return boolean
	 */
	function inArray($param, $params) {
		return (!in_array((string)$param, (array)$params)) ? false : true;
	}
	/**
	 * 是否是布尔型
	 * @param $param
	 * @return boolean
	 */
	function isBool($param) {
		return is_bool($param) ? true : false;
	}
	/**
	 * 是否是数字型
	 * @param $param
	 * @return boolean
	 */
	function isNum($param) {
		return is_numeric($param) ? true : false;
	}
	
	/**
	 * html转换输出
	 * @param $param
	 * @return string
	 */
	function htmlEscape($param) {
		return trim(htmlspecialchars($param, ENT_QUOTES));
	}
	/**
	 * 过滤标签
	 * @param $param
	 * @return string
	 */
	function stripTags($param) {
		return trim(strip_tags($param));
	}
	/**
	 * 初始化$_GET/$_POST全局变量
	 * @param $keys
	 * @param $method
	 * @param $cvtype
	 */
	public static function gp($keys, $method = null, $cvtype = 1,$istrim = true) {
		!is_array($keys) && $keys = array($keys);
		foreach ($keys as $key) {
			if ($key == 'GLOBALS') continue;
			$GLOBALS[$key] = NULL;
			if ($method != 'P' && isset($_GET[$key])) {
				$GLOBALS[$key] = $_GET[$key];
			} elseif ($method != 'G' && isset($_POST[$key])) {
				$GLOBALS[$key] = $_POST[$key];
			}
			if (isset($GLOBALS[$key]) && !empty($cvtype) || $cvtype == 2) {
				$GLOBALS[$key] = iS::escapeChar($GLOBALS[$key], $cvtype == 2, $istrim);
			}
		}
	}

	/**
	 * 指定key获取$_GET/$_POST变量
	 * @param $key
	 * @param $method
	 */
	public static function getGP($key, $method = null) {
		if ($method == 'G' || $method != 'P' && isset($_GET[$key])) {return $_GET[$key];}
		return $_POST[$key];
	}
	/**
	 * 全局变量过滤
	 */
	function filter() {
		$allowed = array('GLOBALS' => 1,'_GET' => 1,'_POST' => 1,'HTTP_RAW_POST_DATA' => 1,'_COOKIE' => 1,'_FILES' => 1,'_SERVER' => 1,'_APP' => 1);
		foreach ($GLOBALS as $key => $value) {
			if (!isset($allowed[$key])) {
				$GLOBALS[$key] = null;
				unset($GLOBALS[$key]);
			}
		}

		if (!get_magic_quotes_gpc()) {
			iS::slashes($_POST);
			iS::slashes($_GET);
			iS::slashes($_COOKIE);
		}
		iS::getServer(array('HTTP_REFERER','HTTP_HOST','HTTP_X_FORWARDED_FOR','HTTP_USER_AGENT',
							'HTTP_CLIENT_IP','HTTP_SCHEME','HTTPS','PHP_SELF',
							'REQUEST_URI','REQUEST_METHOD','REMOTE_ADDR','SCRIPT_NAME',
							'SERVER_SOFTWARE','REQUEST_TIME',
							'QUERY_STRING','argv','argc'));

	}

	/**
	 * 路径转换
	 * @param $fileName
	 * @param $ifCheck
	 * @return string
	 */
	function escapePath($fileName, $ifCheck = true) {
		if (!iS::_escapePath($fileName, $ifCheck)) {
			exit('Access Denied');
		}
		return $fileName;
	}
	/**
	 * 私用路径转换
	 * @param $fileName
	 * @param $ifCheck
	 * @return boolean
	 */
	function _escapePath($fileName, $ifCheck = true) {
		$tmpname = strtolower($fileName);
		$tmparray = array('://',"\0");
		$ifCheck && $tmparray[] = '..';
		if (str_replace($tmparray, '', $tmpname) != $tmpname) {
			return false;
		}
		return true;
	}
	/**
	 * 目录转换
	 * @param unknown_type $dir
	 * @return string
	 */
	function escapeDir($dir) {
		$dir = str_replace(array("'",'#','=','`','$','%','&',';'), '', $dir);
		return rtrim(preg_replace('/(\/){2,}|(\\\){1,}/', '/', $dir), '/');
	}
	/**
	 * 通用多类型转换
	 * @param $mixed
	 * @param $isint
	 * @param $istrim
	 * @return mixture
	 */
	public static function escapeChar($mixed, $isint = false, $istrim = false) {
		if (is_array($mixed)) {
			foreach ($mixed as $key => $value) {
				$mixed[$key] = iS::escapeChar($value, $isint, $istrim);
			}
		} elseif ($isint) {
			$mixed = (int) $mixed;
		} elseif (!is_numeric($mixed) && ($istrim ? $mixed = trim($mixed) : $mixed) && $mixed) {
			$mixed = iS::escapeStr($mixed);
		}
		return $mixed;
	}
	/**
	 * 字符转换
	 * @param $string
	 * @return string
	 */
	public static function escapeStr($string) {
	    if(is_array($string)) {
	        foreach($string as $key => $val) {
	            $string[$key] = iS::escapeStr($val);
	        }
	    } else {
			$string = str_replace(array('%00','\\0'), '', $string); //modified@2010-7-5
			$string = str_replace(array('&', '"',"'", '<', '>'), array('&amp;', '&quot;','&#039;', '&lt;', '&gt;'), $string);
			$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',$string);
	    }
	    return $string;
	}
	/**
	 * 变量检查
	 * @param $var
	 */
	function checkVar(&$var) {
		if (is_array($var)) {
			foreach ($var as $key => $value) {
				iS::checkVar($var[$key]);
			}
		} elseif (str_replace(array('<iframe','<meta','<script'), '', $var) != $var) {
			die('XXS');
		}else{
			$var = str_replace(array('..',')','<','='), array('&#46;&#46;','&#41;','&#60;','&#61;'), $var);
		}
	}

	/**
	 * 变量转义
	 * @param $array
	 */
	public static function slashes(&$array) {
		if (is_array($array)) {
			foreach ($array as $key => $value) {
				if (is_array($value)) {
					iS::slashes($array[$key]);
				} else {
					$array[$key] = addslashes($value);
				}
			}
		}
	}

	/**
	 * 获取服务器变量
	 * @param $keys
	 * @return string
	 */
	function getServer($keys) {
		// Fix for IIS when running with PHP ISAPI
		if ( empty($_SERVER['REQUEST_URI'] ) || ( php_sapi_name() != 'cgi-fcgi' && preg_match( '/^Microsoft-IIS\//',$_SERVER['SERVER_SOFTWARE'] ) ) ) {
		    if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
		       $_SERVER['REQUEST_URI'] =$_SERVER['HTTP_X_ORIGINAL_URL'];
		    }else if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
		       $_SERVER['REQUEST_URI'] =$_SERVER['HTTP_X_REWRITE_URL'];
		    }else {
		        // Use ORIG_PATH_INFO if there is no PATH_INFO
		        if ( !isset($_SERVER['PATH_INFO']) && isset($_SERVER['ORIG_PATH_INFO']) )
		           $_SERVER['PATH_INFO'] =$_SERVER['ORIG_PATH_INFO'];

		        // Some IIS + PHP configurations puts the script-name in the path-info (No need to append it twice)
		        if ( isset($_SERVER['PATH_INFO']) ) {
		            if ($_SERVER['PATH_INFO'] ==$_SERVER['SCRIPT_NAME'] )
		               $_SERVER['REQUEST_URI'] =$_SERVER['PATH_INFO'];
		            else
		               $_SERVER['REQUEST_URI'] =$_SERVER['SCRIPT_NAME'] .$_SERVER['PATH_INFO'];
		        }

		        // Append the query string if it exists and isn't null
		        if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
		           $_SERVER['REQUEST_URI'] .= '?' .$_SERVER['QUERY_STRING'];
		        }
		    }
		}

		// Fix for PHP as CGI hosts that set SCRIPT_FILENAME to something ending in php.cgi for all requests
		if ( isset($_SERVER['SCRIPT_FILENAME']) && ( strpos($_SERVER['SCRIPT_FILENAME'], 'php.cgi') == strlen($_SERVER['SCRIPT_FILENAME']) - 7 ) )
		   $_SERVER['SCRIPT_FILENAME'] =$_SERVER['PATH_TRANSLATED'];

		// Fix for ther PHP as CGI hosts
		if (strpos($_SERVER['SCRIPT_NAME'], 'php.cgi') !== false) 
		    unset($_SERVER['PATH_INFO']);

		if ( empty($_SERVER['PHP_SELF']) )
		   $_SERVER['PHP_SELF'] = preg_replace("/(\?.*)?$/",'',$_SERVER["REQUEST_URI"]);
		

		foreach ($_SERVER as $key=>$sval){
			if (in_array($key, $keys)) {
				$_SERVER[$key] = str_replace(array('<','>','"',"'",'%3C','%3E','%22','%27','%3c','%3e'), '',$sval);
			}else{
				unset($_SERVER[$key]);				
			}
		}
	}
}