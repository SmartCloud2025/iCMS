<?php
class QQ {
	public static $appid	= 203432;
	public static $appkey	= '4ff15cb55015171e61036f2c87cdb3b7';
	public static $callback	= "/login?callback=qq";
	public static $scope	= "get_user_info,add_topic,add_one_blog,add_album,upload_pic,list_album,add_share,check_page_fans,do_like,get_tenpay_address,get_info,get_other_info,get_fanslist,get_idolist,add_idol";
	public static $openid	= '';

	function __construct() {
	}
	function login(){
	    $state = md5(uniqid(rand(), TRUE)); //CSRF protection
	    iPHP::setCookie("QQ_STATE",authcode($state,'ENCODE'));
	    $login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=" 
	        . self::$appid . "&redirect_uri=" . urlencode(CALLBACK_URL.self::$callback)
	        . "&state=" .$state
	        . "&scope=".self::$scope;
	    header("Location:$login_url");
	}
	function callback(){
		$state	= authcode(iPHP::getCookie("QQ_STATE"), 'DECODE');
		if($_GET['state']!=$state){
			//die('QQ.api.err::1000');
			self::login();
			exit;
		}
		
        $token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"
            . "client_id=" . self::$appid. "&redirect_uri=" . urlencode(CALLBACK_URL.self::$callback)
            . "&client_secret=" . self::$appkey. "&code=" . $_GET["code"];

        $response = self::get_url_contents($token_url);
        if (strpos($response, "callback") !== false){
            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
            $msg = json_decode($response);
            
            isset($msg->error) && self::login();
            
        }
        $params = array();
        parse_str($response, $params);
		iPHP::setCookie("QQ_access_token",authcode($params["access_token"],'ENCODE'));
        self::openId($params["access_token"]);
	}
	function openId($access_token=""){
		$access_token	= authcode(iPHP::getCookie("QQ_access_token"), 'DECODE');
	    $graph_url = "https://graph.qq.com/oauth2.0/me?access_token=".$access_token;
	    $str  = self::get_url_contents($graph_url);
	    if (strpos($str, "callback") !== false){
	        $lpos = strpos($str, "(");
	        $rpos = strrpos($str, ")");
	        $str  = substr($str, $lpos + 1, $rpos - $lpos -1);
	    }

	    $user = json_decode($str);
	    isset($user->error) && self::login();

	    //print_r($user);
	    //debug
	    //echo("Hello " . $user->openid);
	    //set openid to session
	    self::$openid	= $user->openid;
	    iPHP::setCookie("QQ_openid",authcode($user->openid,'ENCODE'));
	}
	function get_user_info(){
		$access_token	= authcode(iPHP::getCookie("QQ_access_token"), 'DECODE');
		$openid	= authcode(iPHP::getCookie("QQ_openid"), 'DECODE');
	    $get_user_info = "https://graph.qq.com/user/get_user_info?"
	        . "access_token=" . $access_token
	        . "&oauth_consumer_key=" .self::$appid
	        . "&openid=" .$openid
	        . "&format=json";

	    $info = self::get_url_contents($get_user_info);
	    $arr = json_decode($info, true);
	    $arr['avatar']	= $arr['figureurl_2'];
	    $arr['gender']	= $arr['gender']=="ÄÐ"?'1':0;
	    return $arr;
	}
	function cleancookie(){
		iPHP::setCookie('QQ_access_token', '',-31536000);
		iPHP::setCookie('QQ_openid', '',-31536000);
		iPHP::setCookie('QQ_STATE', '',-31536000);
	}
	function get_url_contents($url){
		$result =  file_get_contents($url);
		
//	    $ch = curl_init();
//	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//	    curl_setopt($ch, CURLOPT_URL, $url);
//	    $result =  curl_exec($ch);
//	    curl_close($ch);
	    return $result;
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
