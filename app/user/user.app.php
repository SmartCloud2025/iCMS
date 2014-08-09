<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: user.app.php 2353 2014-02-13 04:04:49Z coolmoo $
 */
iPHP::appClass('user',"import");
class userApp {
    public $methods = array('iCMS','home','article','publish','manage','profile','data','check','follow','login','logout','register','agreement');
    public $openid  = null;
    function __construct() {
        $this->uid      = (int)$_GET['uid'];
        $this->userid   = (int)iPHP::get_cookie('userid');
        $this->nickname = iS::escapeStr(iPHP::getUniCookie('nickname'));
        $this->openid   = iS::escapeStr($_GET['openid']);
        $this->forward  = iPHP::get_cookie('forward');
        $this->forward OR $this->forward = iCMS_URL;
        // iFS::config($GLOBALS['iCONFIG']['user_fs_conf']);
        iFS::$userid    = $this->userid;
        iPHP::assign('openid',$this->openid);
    }
    public function do_iCMS($a = null) {}
    public function do_home(){
        $this->user(true);
        $u['category'] = user::category((int)$_GET['cid']);
        iPHP::append('user',$u,true);
        return iPHP::view('iTPL://user/home.htm');
    }
    public function do_manage(){
        $pgArray   = array('publish','category','article','comment','favorite','share','follow','fans');
        $pg        = iS::escapeStr($_GET['pg']);
        $pg OR $pg ='article';
        if (in_array ($pg,$pgArray)) {
            $this->user(true);
            iPHP::assign('pg',$pg);
            return iPHP::view("iTPL://user/manage.htm");         
        }
    }
    private function act_profile_base(){
        $unick         = iS::escapeStr($_POST['unick']);
        $sex           = iS::escapeStr($_POST['sex']);
        $weibo         = iS::escapeStr($_POST['weibo']);
        $province      = iS::escapeStr($_POST['province']);
        $city          = iS::escapeStr($_POST['city']);
        $year          = iS::escapeStr($_POST['year']);
        $month         = iS::escapeStr($_POST['month']);
        $day           = iS::escapeStr($_POST['day']);
        $constellation = iS::escapeStr($_POST['constellation']);
        $profession    = iS::escapeStr($_POST['profession']);
        $isSeeFigure   = iS::escapeStr($_POST['isSeeFigure']);
        $height        = iS::escapeStr($_POST['height']);
        $weight        = iS::escapeStr($_POST['weight']);
        $bwhB          = iS::escapeStr($_POST['bwhB']);
        $bwhW          = iS::escapeStr($_POST['bwhW']);
        $bwhH          = iS::escapeStr($_POST['bwhH']);
        $pskin         = iS::escapeStr($_POST['pskin']);
        $phair         = iS::escapeStr($_POST['phair']);
        $shoesize      = iS::escapeStr($_POST['shoesize']);
        $personstyle   = iS::escapeStr($_POST['personstyle']);
        $slogan        = iS::escapeStr($_POST['slogan']);
        $unickEdit     = 0;
        
        $personstyle == iPHP::lang('user:profile:personstyle') && $personstyle="";
        $slogan      == iPHP::lang('user:profile:slogan')   && $slogan="";
        $pskin       == iPHP::lang('user:profile:pskin') && $pskin="";
        $phair       == iPHP::lang('user:profile:phair') && $phair="";
        
        // $user   = iDB::row("SELECT `nickname` FROM `#iCMS@__user` where `uid`='{$this->userid}'");
        // if($unick!=$user->nickname){
        //     $unickEdit  = 1;
        //     iDB::query("UPDATE `#iCMS@__user` SET `nickname` = '$unick' WHERE `uid` = '{$this->userid}';");
        // }
        
        //$this->userinfo();
        
    //  iDB::$show_errors = true;
        $uid = iDB::value("SELECT `uid` FROM `#iCMS@__user_data` where `uid`='{$this->userid}' limit 1");
        if($uid){
            iDB::query("UPDATE `#iCMS@__user_data`
SET `weibo` = '$weibo', `province` = '$province', `city` = '$city', `year` = '$year', `month` = '$month', `day` = '$day', `constellation` = '$constellation', `profession` = '$profession', `isSeeFigure` = '$isSeeFigure', `height` = '$height', `weight` = '$weight', `bwhB` = '$bwhB', `bwhW` = '$bwhW', `bwhH` = '$bwhH', `pskin` = '$pskin', `phair` = '$phair', `shoesize` = '$shoesize', `personstyle` = '$personstyle', `slogan` = '$slogan', `unickEdit` = '$unickEdit', `coverpic` = '$coverpic'
WHERE `uid` = '$this->userid';");
            echo iDB::$last_query;
        }else{
            iDB::query("INSERT INTO `#iCMS@__user_data`
            (`uid`, `realname`, `mobile`, `enterprise`, `address`, `zip`, `weibo`, `province`, `city`, `year`, `month`, `day`, `constellation`, `profession`, `isSeeFigure`, `height`, `weight`, `bwhB`, `bwhW`, `bwhH`, `pskin`, `phair`, `shoesize`, `personstyle`, `slogan`, `unickEdit`, `coverpic`, `tb_nick`, `tb_buyer_credit`, `tb_seller_credit`, `tb_type`, `is_golden_seller`)
values ('$this->userid', '$realname', '$mobile', '$enterprise', '$address', '$zip', 'weibo', '$province', '$city', '$year', '$month', '$day', '$constellation', '$profession', '$isSeeFigure', '$height', '$weight', '$bwhB', '$bwhW', '$bwhH', '$pskin', '$phair', '$shoesize', '$personstyle', '$slogan', '$unickEdit', '$coverpic', '$tb_nick', '$tb_buyer_credit', '$tb_seller_credit', '$tb_type', '$is_golden_seller');");
        }
        iPHP::success('user:profile:success');
    }
    private function act_profile_avatar(){
        iFS::$watermark     = false;
        iFS::$checkFileData = true;
        $avatardir = dirname(get_avatar($this->userid));
        $F         = iFS::upload('avatar',$avatardir,$this->userid,'jpg');
        $F OR iPHP::code(0,'user:iCMS:error',0,'json');
        $avatarurl = iFS::fp($F['path'],'+http');
        iPHP::code(1,'user:profile:avatar',$avatarurl,'json');
    }

    private function act_profile_setpassword(){

        iPHP::seccode($_POST['validCode']) OR iPHP::alert('iCMS:seccode:error');
        
        $oldPwd     = md5($_POST['oldPwd']);
        $newPwd1    = md5($_POST['newPwd1']);
        $newPwd2    = md5($_POST['newPwd2']);
        
        $newPwd1!=$newPwd2 && iPHP::alert("user:password:unequal");
        
        $password = iDB::value("SELECT `password` FROM `#iCMS@__user` where `uid`='{$this->userid}' limit 1");
        $oldPwd!=$password && iPHP::alert("user:password:original");
        iDB::query("UPDATE `#iCMS@__user` SET `password` = '$newPwd1' WHERE `uid` = '{$this->userid}';");
        iPHP::alert("user:password:modified",'js:parent.location.reload();');
    }
    public function ACTION_profile(){
        user::status(USER_LOGIN_URL,"nologin");

        $pgArray = array('base','avatar','setpassword','bind','custom');
        $pg      = iS::escapeStr($_POST['pg']);
        $funname ='act_profile_'.$pg;
        //var_dump($funname);
        if (in_array ($pg,$pgArray)) {
            $this->$funname();
        }
    }
    public function do_profile(){
        $pgArray   = array('base','avatar','setpassword','bind','custom');
        $pg        = iS::escapeStr($_GET['pg']);
        $pg OR $pg ='base';
        if (in_array ($pg,$pgArray)) {
            $this->user(true);
            iPHP::assign('pg',$pg);
            if($pg=='bind'){
                $platform = user::openid($this->userid);
                iPHP::assign('platform',$platform);
            }
            return iPHP::view("iTPL://user/profile.htm");         
        }
    }
    private function user($ud=false){
        $status = array('logined'=>false,'followed'=>false,'isme'=>false);
        if($this->uid){
            $user = user::data($this->uid);
            iPHP::http404($user,"user:".$uid);
        }

        $me = user::status();
        if(empty($me) && empty($user)){
            iPHP::set_cookie('forward', '',-31536000);
            //user::status(USER_LOGIN_URL,"nologin");
            iPHP::gotourl(USER_LOGIN_URL);
        }
        if($me){
            $status['logined']    = true;
            $status['followed']   = (int)user::follow($me->uid,$user->uid);
            empty($user) && $user = $me;
            if($user->uid == $me->uid){
                $status['isme'] = true;
                $user           = $me;
            }
            iPHP::assign('me',(array)$me);
        }
        //var_dump($user,);
        //var_dump($user);
        iPHP::assign('status', $status);
        iPHP::assign('user',   (array)$user);
        $ud && iPHP::assign('userdata',(array)$this->userdata());
    }
    private function userdata(){
        $userdata = iDB::row("SELECT * FROM `#iCMS@__user_data` where `uid`='{$this->userid}' limit 1;");
        $userdata OR iDB::query("INSERT INTO `#iCMS@__user_data` (`uid`,`realname`,`mobile`,`enterprise`,`address`,`zip`,`weibo`,`province`,`city`,`year`,`month`,`day`,`constellation`,`profession`,`isSeeFigure`,`height`,`weight`,`bwhB`,`bwhW`,`bwhH`,`pskin`,`phair`,`shoesize`,`personstyle`,`slogan`,`unickEdit`,`coverpic`,`tb_nick`,`tb_buyer_credit`,`tb_seller_credit`,`tb_type`,`is_golden_seller`) VALUES ( '$this->userid','','','','','','','','','','','','','','1','0','0','0','0','0','','','0','','','0','','','','','','');");
        $userdata->coverpic  && $userdata->coverpic   = iFS::fp($userdata->coverpic,'+http');
        $userdata->enterprise&& $userdata->enterprise = unserialize($userdata->enterprise);
        return $userdata;
    }

    public function ACTION_login(){
        iCMS::$config['user']['login'] OR iPHP::code(0,'user:login:forbidden','uname','json');
        
        $uname    = iS::escapeStr($_POST['uname']);
        $pass     = md5(trim($_POST['pass']));
        $remember = (bool)$_POST['remember']?ture:false;
        $seccode  = iS::escapeStr($_POST['seccode']);
        $pos      = strpos ($uname,'@');
        $a        = iPHP::code(0,'user:login:error','uname');

        if(iCMS::$config['user']['loginseccode']){
            iPHP::seccode($seccode) OR iPHP::code(0,'iCMS:seccode:error','seccode','json');
        }
        $remember && user::$cookietime = 14*86400;
        $user = user::login($uname,$pass,$pos===false?'nk':'');

        $user && $a = iPHP::code(1,0,$this->forward);
        iPHP::json($a);
    }

    public function ACTION_register(){
        if(!iCMS::$config['user']['register']){
            exit(iPHP::lang('user:register:forbidden'));
        }
        $username    = iS::escapeStr($_POST['username']);
        $nickname    = iS::escapeStr($_POST['nickname']);
        $gender      = ($_POST['sex']=='girl'?0:1);
        $password    = md5(trim($_POST['password']));
        $rstpassword = md5(trim($_POST['rstpassword']));
        $refer       = iS::escapeStr($_POST['refer']);
        $seccode     = iS::escapeStr($_POST['seccode']);
        $openid      = iS::escapeStr($_POST['openid']);
        $type        = iS::escapeStr($_POST['type']);
        $agreement   = $_POST['agreement'];


        $username OR iPHP::code(0,'user:register:username:empty','username','json');
        preg_match("/^[\w\-\.]+@[\w\-]+(\.\w+)+$/i",$username) OR iPHP::code(0,'user:register:username:error','username','json');
        user::check($username,'username') OR iPHP::code(0,'user:register:username:exist','username','json');
        
        $nickname OR iPHP::code(0,'user:register:nickname:empty','nickname','json');
        (cstrlen($nickname)>20 || cstrlen($nickname)<4) && iPHP::code(0,'user:register:nickname:error','nickname','json'); 
        user::check($nickname,'nickname') OR iPHP::code(0,'user:register:nickname:exist','nickname','json');

        trim($_POST['password']) OR iPHP::code(0,'user:password:empty','password','json');
        trim($_POST['rstpassword']) OR iPHP::code(0,'user:password:rst_empty','rstpassword','json');
        $password==$rstpassword OR iPHP::code(0,'user:password:unequal','password','json');

        if(iCMS::$config['user']['regseccode']){
            iPHP::seccode($seccode) OR iPHP::code(0,'iCMS:seccode:error','seccode','json');
        }
        //print_r($_POST);
        $regip   = iS::escapeStr(iPHP::getIp());
        $regdate = time();
        iDB::query("INSERT INTO `#iCMS@__user`
               (`gid`, `username`, `nickname`, `password`, `gender`, `fans`, `follow`, `credit`, `regip`, `regdate`, `lastloginip`, `lastlogintime`, `type`)
        VALUES ('0', '$username', '$nickname', '$password', '$gender', '0', '0', '0', '$regip', '$regdate', '', '', '0');");
        $uid    = iDB::$insert_id;
        user::set_cookie($username,$password,array('uid'=>$uid,'username'=>$username,'nickname'=>$nickname));
        //user::setCache($uid);
        iPHP::set_cookie('forward', '',-31536000);
        iPHP::json(array('code'=>1,'forward'=>$this->forward));
    }
    public function API_check(){
        $name  = iS::escapeStr($_GET['name']);
        $value = iS::escapeStr($_GET['value']);
        $a     = iPHP::code(1);
        switch ($name) {
            case 'username':
                if(!preg_match("/^[\w\-\.]+@[\w\-]+(\.\w+)+$/i", $value)){
                    $a = iPHP::code(0,'user:register:username:error');
                }else{
                    user::check($value,'username') OR $a = iPHP::code(0,'user:register:username:exist');
                }
                break;
            case 'nickname':
                if(preg_match("/\d/", $value{0})||cstrlen($value)>20||cstrlen($value)<4){
                    $a = iPHP::code(0,'user:register:nickname:error');
                }else{
                    user::check($value,'nickname') OR $a = iPHP::code(0,'user:register:nickname:exist');
                }
                break;
            case 'password':
                strlen($value)<6 && $a=iPHP::code(0,'user:password:error');
            break;        
            case 'seccode':
                iPHP::seccode($value) OR $a = iPHP::code(0,'iCMS:seccode:error');
            break;        
        }
        iPHP::json($a);
    }
    public function API_follow(){
        $user   = user::status();
        $user OR iPHP::code(0,0,$this->forward,'json');

        $uid   = (int)$user->uid;
        $name  = $user->nickname;
        
        $fuid   = (int)$_GET['fuid'];
        $fname  = $_GET['fname'];
        $follow = (bool)$_GET['follow'];
        
        if($follow){
            $check = user::follow($uid,$fuid);
            $uid==$fuid && iPHP::code(0,'user:follow:self',0,'json');

            if($check){
                iPHP::code(1,'user:follow:success',0,'json');
            }else{
                iDB::query("INSERT INTO `#iCMS@__user_follow` (`uid`,`name`,`fuid`,`fname`) VALUES ('$uid','$name','$fuid','$fname');");
                iPHP::code(1,'user:follow:success',0,'json');
            }            
        }else{
            iDB::query("DELETE FROM `#iCMS@__user_follow` WHERE `uid` = '$uid' AND `fuid`='$fuid' LIMIT 1;");
            iPHP::code(1,0,0,'json');
        }
    }
    public function API_register(){
        if(iCMS::$config['user']['register']){
            iPHP::set_cookie('forward',$this->forward);
            user::status($this->forward,"login");
            return iPHP::view('iTPL://user/register.htm');
        }
        //exit(iPHP::lang('user:register:forbidden'));
        iPHP::view('iTPL://user/register.close.htm');
    }
    public function API_data($uid=0){
        //$uid OR $uid  = $this->userid;
        $user   = user::status();
        $user OR $user = iPHP::code(0,0,$this->forward);
        iPHP::json($user);
    }
    public function API_logout(){
        user::logout();
        iPHP::code(1,0,$this->forward,'json');
    } 
    public function API_login(){
        if(iCMS::$config['user']['login']){
            iPHP::set_cookie('forward',$this->forward);
            user::status($this->forward,"login");
            return iPHP::view('iTPL://user/login.htm');
        }
        iPHP::view('iTPL://user/login.close.htm');
    }
    public function API_agreement(){
    	return iPHP::view('iTPL://user/agreement.htm');
    }    
}
