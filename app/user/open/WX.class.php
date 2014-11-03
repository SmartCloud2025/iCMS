<?php
class WX {
	public static $appid  = '';
	public static $appkey = '';
	public static $scope  = "snsapi_login";
	public static $openid = '';
	public static $url    = '';

	public static function login(){
	    $state = md5(uniqid(rand(), TRUE)); //CSRF protection
	    iPHP::set_cookie("WX_STATE",authcode($state,'ENCODE'));
	    $login_url = "https://open.weixin.qq.com/connect/qrconnect?response_type=code"
	        . "&appid=" . self::$appid
	        . "&redirect_uri=" . urlencode(self::$url)
	        . "&state=" .$state
	        . "&scope=".self::$scope;
	    header("Location:$login_url");
	}
	public static function callback(){
		$state	= authcode(iPHP::get_cookie("WX_STATE"), 'DECODE');
		if($_GET['state']!=$state && empty($_GET['code'])){
			self::login();
			exit;
		}

        $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?grant_type=authorization_code&"
            . "appid=" . self::$appid
            . "&secret=" . self::$appkey
            . "&code=" . $_GET["code"];

        $response = self::get_url_contents($token_url);
        $token    = json_decode($response, true);
		if ( is_array($token) && !isset($token['errcode']) ) {
			iPHP::set_cookie("WX_ACCESS_TOKEN",	authcode($token['access_token'],'ENCODE'));
	    	iPHP::set_cookie("WX_REFRESH_TOKEN",authcode($token['refresh_token'],'ENCODE'));
		    iPHP::set_cookie("WX_OPENID",		authcode($token['openid'],'ENCODE'));
		    self::$openid = $token['openid'];
		} else {
			self::login();
			exit;
		}
	}
	public static function get_openid(){
		self::$openid  = authcode(iPHP::get_cookie("WX_OPENID"), 'DECODE');
		return self::$openid;
	}
	public static function get_user_info(){
		$access_token  = authcode(iPHP::get_cookie("WX_ACCESS_TOKEN"), 'DECODE');
		$openid        = authcode(iPHP::get_cookie("WX_OPENID"), 'DECODE');
		$get_user_info = "https://api.weixin.qq.com/sns/userinfo?"
	        . "access_token=" . $access_token
	        . "&openid=" .$openid;

		$info = self::get_url_contents($get_user_info);
		$arr  = json_decode($info, true);
		$arr['avatar'] = $arr['headimgurl'];
		$arr['gender'] = $arr['sex'];
	    return $arr;
	}
	public static function cleancookie(){
		iPHP::set_cookie('WX_ACCESS_TOKEN', '',-31536000);
		iPHP::set_cookie('WX_OPENID', '',-31536000);
		iPHP::set_cookie('WX_STATE', '',-31536000);
	}
	public static function get_url_contents($url){
		$result =  file_get_contents($url);
	    // $ch = curl_init();
	    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    // curl_setopt($ch, CURLOPT_URL, $url);
	    // $result =  curl_exec($ch);
	    // curl_close($ch);
	    return $result;
	}
}
