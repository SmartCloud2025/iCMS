<?php
class WB {
	public static $appid  = '';
	public static $appkey = '';
	public static $scope  = "promotion,item,usergrade";
	public static $openid = '';
	public static $url    = '';
	private static $info  = '';


	public static function login(){
	    $state = md5(uniqid(rand(), TRUE)); //CSRF protection
	    iPHP::set_cookie("WB_STATE",authcode($state,'ENCODE'));
	    $login_url = "https://api.weibo.com/oauth2/authorize?response_type=code&client_id="
	        . self::$appid . "&redirect_uri=" . urlencode(self::$url)
	        . "&state=" .$state
	        . "&scope=".self::$scope;
		header("Location:$login_url");
	}
	public static function callback(){
		$state	= authcode(iPHP::get_cookie("WB_STATE"), 'DECODE');
		if($_GET['state']!=$state && empty($_GET['code'])){
			self::login();
			exit;
		}

        $POST_FIELDS = "grant_type=authorization_code&"
            . "client_id=" . self::$appid. "&redirect_uri=" . urlencode(self::$url)
            . "&client_secret=" . self::$appkey. "&code=" . $_GET["code"];

		$response = self::postUrl('https://api.weibo.com/oauth2/access_token',$POST_FIELDS);
		$token    = json_decode($response, true);
		if ( is_array($token) && !isset($token['error']) ) {
			iPHP::set_cookie("WB_ACCESS_TOKEN",	authcode($token['access_token'],'ENCODE'));
	    	iPHP::set_cookie("WB_REFRESH_TOKEN",authcode($token['refresh_token'],'ENCODE'));
		    iPHP::set_cookie("WB_OPENID",		authcode($token['uid'],'ENCODE'));
		    self::$openid = $token['uid'];
		} else {
			self::login();
			exit;
		}
	}
	public static function get_openid(){
		self::$openid  = authcode(iPHP::get_cookie("WB_OPENID"), 'DECODE');
		return self::$openid;
	}
	public static function get_user_info(){
		$access_token  = authcode(iPHP::get_cookie("WB_ACCESS_TOKEN"), 'DECODE');
		$refresh_token = authcode(iPHP::get_cookie("WB_REFRESH_TOKEN"), 'DECODE');
		self::$openid  = authcode(iPHP::get_cookie("WB_OPENID"), 'DECODE');
		$url  = "https://api.weibo.com/2/users/show.json?uid=".self::$openid;
		$info = self::get_url_contents($url,$access_token);
		$arr  = json_decode($info, true);
		$arr['nickname'] = $arr['screen_name'];
		$arr['avatar']   = $arr['avatar_large'];
		$arr['gender']   = $arr['gender']=="m"?'1':'0';
	    return $arr;
	}
	public static function cleancookie(){
		iPHP::set_cookie('WB_ACCESS_TOKEN', '',-31536000);
		iPHP::set_cookie('WB_REFRESH_TOKEN', '',-31536000);
		iPHP::set_cookie('WB_OPENID', '',-31536000);
		iPHP::set_cookie('WB_STATE', '',-31536000);
	}
	public static function get_url_contents($url,$access_token=""){
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

	public static function postUrl($url, $POSTFIELDS) {
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_USERAGENT, 'Sae T OAuth2 v0.1');
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS);
		$res  = curl_exec ($ch);
		//$info = curl_getinfo($ch);
	    curl_close ($ch);
	    return $res;
	}
}
