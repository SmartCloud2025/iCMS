<?php
class TB {
	public static $appid	= 21181857;
	public static $appkey	= '4ec6477b9129b78db88d6107b4cbd39f';
	public static $callback	= "/login?callback=tb";
	public static $scope	= "promotion,item,usergrade";
	public static $openid	= '';
	public static $info		= '';

	function __construct() {
	}
	function login(){
	    $state = md5(uniqid(rand(), TRUE)); //CSRF protection
	    iPHP::set_cookie("TB_STATE",authcode($state,'ENCODE'));
	    $login_url = "https://oauth.taobao.com/authorize?response_type=code&client_id="
	        . self::$appid . "&redirect_uri=" . urlencode(CALLBACK_URL.self::$callback)
	        . "&state=" .$state
	        . "&scope=".self::$scope;
	    header("Location:$login_url");
	}
	function callback(){
		$state	= authcode(iPHP::get_cookie("TB_STATE"), 'DECODE');
		if($_GET['state']!=$state){
			self::login();
			exit;
		}
		
        $POST_FIELDS = "grant_type=authorization_code&"
            . "client_id=" . self::$appid. "&redirect_uri=" . urlencode(CALLBACK_URL.self::$callback)
            . "&client_secret=" . self::$appkey. "&code=" . $_GET["code"];

        $response	= self::postUrl('https://oauth.taobao.com/token',$POST_FIELDS);
	    self::$info	= json_decode($response, true);
	    if(self::$info['error']){
			self::login();
			exit;
	    }
	    self::$openid	= self::$info['taobao_user_id'];
	}
	function get_user_info(){
		$user['nickname']=self::$info['taobao_user_nick'];
		$user['gender']	=0; //$user['gender']=="??"?'1':0;
		$user['avatar']	=''; //$user['figureurl_2'];
		return $user;
	}
	function cleancookie(){
		iPHP::set_cookie('TB_STATE', '',-31536000);
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
