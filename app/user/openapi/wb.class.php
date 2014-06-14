<?php
class WB {
	public static $appid	= 4207390454;
	public static $appkey	= 'be57bf24cafe2509a17b2179495bb402';
	public static $callback	= "/login?callback=wb";
	public static $scope	= "promotion,item,usergrade";
	public static $openid	= '';
	private static $info	= '';

	function __construct() {
	}
	function login(){
	    $state = md5(uniqid(rand(), TRUE)); //CSRF protection
	    iPHP::setCookie("WB_STATE",authcode($state,'ENCODE'));
	    $login_url = "https://api.weibo.com/oauth2/authorize?response_type=code&client_id="
	        . self::$appid . "&redirect_uri=" . urlencode(CALLBACK_URL.self::$callback)
	        . "&state=" .$state
	        . "&scope=".self::$scope;
	    header("Location:$login_url");
	}
	function callback(){
		$state	= authcode(iPHP::getCookie("WB_STATE"), 'DECODE');
		if($_GET['state']!=$state){
			self::login();
			exit;
		}
		
        $POST_FIELDS = "grant_type=authorization_code&"
            . "client_id=" . self::$appid. "&redirect_uri=" . urlencode(CALLBACK_URL.self::$callback)
            . "&client_secret=" . self::$appkey. "&code=" . $_GET["code"];

        $response	= self::postUrl('https://api.weibo.com/oauth2/access_token',$POST_FIELDS);
		$token = json_decode($response, true);
		if ( is_array($token) && !isset($token['error']) ) {
			iPHP::setCookie("WB_access_token",	authcode($token['access_token'],'ENCODE'));
	    	iPHP::setCookie("WB_refresh_token",	authcode($token['refresh_token'],'ENCODE'));
		    iPHP::setCookie("WB_openid",			authcode($token['uid'],'ENCODE'));
		    self::$openid			= $token['uid'];
		} else {
			self::login();
			exit;
		}
	}
	function get_user_info(){
		$access_token	= authcode(iPHP::getCookie("WB_access_token"), 'DECODE');
		$refresh_token	= authcode(iPHP::getCookie("WB_refresh_token"), 'DECODE');
		//$openid			= authcode(iPHP::getCookie("QQ_openid"), 'DECODE');
	    $url = "https://api.weibo.com/2/users/show.json?uid=".self::$openid;
	    $info = self::get_url_contents($url,$access_token);
	    $arr = json_decode($info, true);
	    $arr['nickname']= $arr['screen_name'];
	    $arr['avatar']	= $arr['avatar_large'];
	    $arr['gender']	= $arr['gender']=="m"?'1':'0';
	    return $arr;
	}
	function cleancookie(){
		iPHP::setCookie('WB_STATE', '',-31536000);
	}
	function get_url_contents($url,$access_token=""){
		$headers[] = "Authorization: OAuth2 ".$access_token;
		$headers[] = "API-RemoteIP: " . $_SERVER['REMOTE_ADDR'];
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Sae T OAuth2 v0.1');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers );
	    curl_setopt($ch, CURLOPT_URL, $url);
	    $result =  curl_exec($ch);
	    curl_close($ch);
	    return $result;
	}

	function postUrl($url, $POSTFIELDS) {
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS);
	    $res = curl_exec ($ch);
	    curl_close ($ch);
	    return $res;
	}
	function _header($URL='') {
	    if(!headers_sent()) {
	        header("Location: $URL");
	        exit;
	    }else {
	        echo '<meta http-equiv=\'refresh\' content=\'0;url='.$URL.'\'><script type="text/JavaScript">window.location.replace(\''.$URL.'\');</script>';
	        exit;
	    }
	}
}
