<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: user.app.php 2353 2014-02-13 04:04:49Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');

iPHP::app('user.class','static');
class userApp {
    public $methods = array('iCMS','home','article','publish','manage','profile','data','hits','check','follow','login','logout','register','add_category','upload','imageUp','delete','report','ucard');
    public $openid  = null;
    public $user    = array();
    public $me      = array();
    function __construct() {
        user::get_cookie();
        $this->uid      = (int)$_GET['uid'];
        $this->openid   = iS::escapeStr($_GET['openid']);
        $this->forward  = iPHP::get_cookie('forward');
        $this->forward OR $this->forward = iCMS_URL;
        // iFS::config($GLOBALS['iCONFIG']['user_fs_conf']);
        iFS::$userid = user::$userid;
        iPHP::assign('openid',$this->openid);
    }
    public function do_iCMS($a = null) {}
    public function do_home(){
        $this->user(true);
        $u['category'] = user::category((int)$_GET['cid']);
        iPHP::append('user',$u,true);
        iPHP::view('iCMS://user/home.htm');
    }
    public function do_manage(){
        $pgArray   = array('publish','category','article','comment','favorite','share','follow','fans');
        $pg        = iS::escapeStr($_GET['pg']);
        $pg OR $pg ='article';
        if (in_array ($pg,$pgArray)) {
            if($_GET['pg']=='comment'){
                $app_array  = iCache::get('iCMS/app/cache_id');
                iPHP::assign('iAPP',$app_array);
            }
            $this->user(true);
            $funname ='__do_manage_'.$pg;
            $class_methods  =  get_class_methods(__CLASS__);
            in_array($funname,$class_methods) && $this->$funname();
            iPHP::assign('pg',$pg);
            iPHP::view("iCMS://user/manage.htm");
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
                $platform = user::openid(user::$userid);
                iPHP::assign('platform',$platform);
            }
            iPHP::view("iCMS://user/profile.htm");
        }
    }

    private function user($ud=false){
        $status = array('logined'=>false,'followed'=>false,'isme'=>false);
        if($this->uid){ // &uid=
            $this->user = user::get($this->uid);
            iPHP::http404($this->user,"user:".$this->uid);
        }

        $this->me = user::status(); //判断是否登陆

        if(empty($this->me) && empty($this->user)){
            iPHP::set_cookie('forward', '',-31536000);
            iPHP::gotourl(USER_LOGIN_URL);
        }

        if($this->me){
            $status['logined']    = true;
            $status['followed']   = (int)user::follow($this->me->uid,$this->user->uid);
            empty($this->user) && $this->user = $this->me;
            if($this->user->uid == $this->me->uid){
                $status['isme'] = true;
                $this->user     = $this->me;
            }
            iPHP::assign('me',(array)$this->me);
        }
        $this->user->hits_script = iCMS_API.'?app=user&do=hits&uid='.$this->user->uid;
        iPHP::assign('status', $status);
        iPHP::assign('user',   (array)$this->user);
        $ud && iPHP::assign('userdata',(array)user::data($this->user->uid));
    }

    private function __do_manage_article(){
        iPHP::assign('status',isset($_GET['status'])?(int)$_GET['status']:'1');
        iPHP::assign('cid',(int)$_GET['cid']);
        iPHP::assign('article',array(
            'manage' => iPHP::router('/user/article'),
            'edit'   => iPHP::router('/user/publish'),
        ));
        iPHP::app('user.func');
        iPHP::assign('category',user_category(array('userid'=>user::$userid,'array'=>true)));
    }
    private function __do_manage_publish(){
        $id    = (int)$_GET['id'];
        iPHP::app('article.table');
        list($article,$article_data) = articleTable::data($id,0,user::$userid);
        $cid = empty($article['cid'])?(int)$_GET['cid']:$article['cid'];
        iPHP::assign('article',$article);
        iPHP::assign('article_data',$article_data);
        iPHP::assign('option',$this->select('',$cid));
    }
    private function __action_manage_category(){
        $uid        = user::$userid;
        $name_array = (array)$_POST['name'];
        $cid_array  = (array)$_POST['_cid'];
        foreach ($name_array as $cid => $name) {
            iDB::query("
                UPDATE `#iCMS@__user_category`
                SET `name` = '$name'
                WHERE `cid` = '{$cid}' AND `uid`='".user::$userid."';");
        }
        foreach ($cid_array as $key => $_cid) {
            if(!$name_array[$_cid]){
                iDB::query("
                    UPDATE `#iCMS@__article`
                    SET `ucid` = '0'
                    WHERE `userid`='".user::$userid."';");
                iDB::query("
                    DELETE FROM `#iCMS@__user_category`
                    WHERE `cid` = '$_cid' AND `uid`='".user::$userid."';");
            }
        }

        $newname = iS::escapeStr($_POST['newname']);
        $newname && iDB::insert('user_category',array('uid'=>user::$userid, 'name'=>$newname, 'count'=>0));

        iPHP::success('user:category:update','js:1');
    }

    private function __action_manage_publish(){
        $aid         = (int)$_POST['id'];
        $cid         = (int)$_POST['cid'];
        $_cid        = (int)$_POST['_cid'];
        $ucid        = (int)$_POST['ucid'];
        $_ucid       = (int)$_POST['_ucid'];
        $title       = iS::escapeStr($_POST['title']);
        $source      = iS::escapeStr($_POST['source']);
        $keywords    = iS::escapeStr($_POST['keywords']);
        $description = iS::escapeStr($_POST['description']);
        $body        = $_POST['body'];
        $creative    = (int)$_POST['creative'];
        $userid      = user::$userid;
        $author      = user::$nickname;
        $editor      = user::$nickname;

        empty($title)&& iPHP::alert('标题不能为空！');
        empty($cid)  && iPHP::alert('请选择所属栏目！');
        empty($body) && iPHP::alert('文章内容不能为空！');

        $fwd = iCMS::filter($title);
        $fwd && iPHP::alert('user:publish:filter_title');
        $fwd = iCMS::filter($description);
        $fwd && iPHP::alert('user:publish:filter_desc');
        $fwd = iCMS::filter($body);
        $fwd && iPHP::alert('user:publish:filter_body');


        $pubdate  = time();
        $postype  = "0";

        $category = iCache::get('iCMS/category/'.$cid);
        $status   = $category['isexamine']?3:1;

        iPHP::import(iPHP_APP_CORE .'/iMAP.class.php');
        iPHP::app('article.table');
        $fields      = articleTable::fields($aid);
        $data_fields = articleTable::data_fields($aid);
        if(empty($aid)) {
            $postime = $pubdate;
            $chapter = $hits = $good = $bad = $comments = 0;

            $data    = compact ($fields);
            $aid     = articleTable::insert($data);
            $article_data = compact ($data_fields);
            articleTable::data_insert($article_data);

            map::init('category',iCMS_APP_ARTICLE);
            map::add($cid,$aid);
            iDB::query("UPDATE `#iCMS@__user_category` SET `count` = count+1 WHERE `cid` = '$ucid' AND `uid`='".user::$userid."';");
            user::update_count(user::$userid,1,'article');
            $lang = array(
                '1'=>'user:article:add_success',
                '3'=>'user:article:add_examine',
            );
        }else{
            articleTable::update(compact($fields),array('id'=>$aid));
            articleTable::data_update(compact ($data_fields),array('aid'=>$aid));
            map::init('category',iCMS_APP_ARTICLE);
            map::diff($cid,$_cid,$aid);
            if($ucid!=$_ucid){
                iDB::query("UPDATE `#iCMS@__user_category` SET `count` = count+1 WHERE `cid` = '$ucid' AND `uid`='".user::$userid."';");
                iDB::query("UPDATE `#iCMS@__user_category` SET `count` = count-1 WHERE `cid` = '$_ucid' AND `uid`='".user::$userid." AND `count`>0';");
            }
            $lang = array(
                '1'=>'user:article:update_success',
                '3'=>'user:article:update_examine',
            );
        }
        $url = str_replace(iCMS_URL,'',iPHP::router('/user/article'));
        iPHP::success($lang[$status],'url:'.$url);
    }
    public function ACTION_manage(){
        $this->me = user::status(USER_LOGIN_URL,"nologin");

        $pgArray = array('publish','category','article','comment','favorite','share','follow','fans');
        $pg      = iS::escapeStr($_POST['pg']);
        $funname ='__action_manage_'.$pg;
        $methods = get_class_methods(__CLASS__);
        if (in_array ($pg,$pgArray) && in_array ($funname,$methods)) {
            $this->$funname();
        }
    }

    private function __action_profile_base(){
        $nickname      = iS::escapeStr($_POST['nickname']);
        $gender        = iS::escapeStr($_POST['gender']);
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

        $personstyle == iPHP::lang('user:profile:personstyle') && $personstyle = "";
        $slogan      == iPHP::lang('user:profile:slogan')      && $slogan      = "";
        $pskin       == iPHP::lang('user:profile:pskin')       && $pskin       = "";
        $phair       == iPHP::lang('user:profile:phair')       && $phair       = "";


        if($nickname!=user::$nickname){
            $has_nick = iDB::value("SELECT uid FROM `#iCMS@__user` where `nickname`='{$nickname}' AND `uid` <> '".user::$userid."'");
            $has_nick && iPHP::alert('user:profile:nickname');
            $userdata = user::data(user::$userid);
            if($userdata->unickEdit>1){
                iPHP::alert('user:profile:unickEdit');
            }
            iDB::query("UPDATE `#iCMS@__user` SET `nickname` = '$nickname' WHERE `uid` = '".user::$userid."';");
            $unickEdit = 1;
        }
        if($gender!=$this->me->gender){
            iDB::query("UPDATE `#iCMS@__user` SET `gender` = '$gender' WHERE `uid` = '".user::$userid."';");
        }

        $uid    = iDB::value("SELECT `uid` FROM `#iCMS@__user_data` where `uid`='".user::$userid."' limit 1");

        $fields = array('weibo', 'province', 'city', 'year', 'month', 'day', 'constellation', 'profession', 'isSeeFigure', 'height', 'weight', 'bwhB', 'bwhW', 'bwhH', 'pskin', 'phair', 'shoesize', 'personstyle', 'slogan', 'unickEdit', 'coverpic');
        if($uid){
            $data = compact ($fields);
            iDB::update('user_data', $data, array('uid'=>user::$userid));
        }else{
            $uid     = user::$userid;
            $_fields = array('uid', 'realname', 'mobile', 'enterprise', 'address', 'zip','tb_nick', 'tb_buyer_credit', 'tb_seller_credit', 'tb_type', 'is_golden_seller');
            $fields  = array_merge($fields,$_fields);
            $data    = compact ($fields);
            iDB::insert('user_data',$data);
        }
        iPHP::success('user:profile:success');
    }
    private function __action_profile_custom(){
        iFS::$watermark     = false;
        iFS::$checkFileData = false;
        $dir = get_user_dir(user::$userid,'coverpic');
        $F   = iFS::upload('upfile',$dir,user::$userid,'jpg');
        $F['code'] && iDB::update('user_data',array('coverpic'=>$F["path"]),array('uid'=>user::$userid));
        $url = iFS::fp($F['path'],'+http');
        if($_POST['format']=='json'){
            iPHP::code(1,'user:profile:custom',$url,'json');
        }
        $array = array(
            "value"    => $F["path"],
            "url"      => $url,
            "fid"      => $F["fid"],
            "fileType" => $F["ext"],
            "image"    => in_array($F["ext"],array('gif','jpg','jpeg','png'))?1:0,
            "original" => $F["oname"],
            "state"    => ($F['code']?'SUCCESS':$F['state'])
        );
       iPHP::js_callback($array);
    }
    private function __action_profile_avatar(){
        iFS::$watermark     = false;
        iFS::$checkFileData = false;
        $dir = get_user_dir(user::$userid);
        $F   = iFS::upload('avatar',$dir,user::$userid,'jpg');
        $F OR iPHP::code(0,'user:iCMS:error',0,'json');
        $avatarurl = iFS::fp($F['path'],'+http');
        iPHP::code(1,'user:profile:avatar',$avatarurl,'json');
    }

    private function __action_profile_setpassword(){

        iPHP::seccode($_POST['validCode']) OR iPHP::alert('iCMS:seccode:error');

        $oldPwd     = md5($_POST['oldPwd']);
        $newPwd1    = md5($_POST['newPwd1']);
        $newPwd2    = md5($_POST['newPwd2']);

        $newPwd1!=$newPwd2 && iPHP::alert("user:password:unequal");

        $password = iDB::value("SELECT `password` FROM `#iCMS@__user` where `uid`='".user::$userid."' limit 1");
        $oldPwd!=$password && iPHP::alert("user:password:original");
        iDB::query("UPDATE `#iCMS@__user` SET `password` = '$newPwd1' WHERE `uid` = '".user::$userid."';");
        iPHP::alert("user:password:modified",'js:parent.location.reload();');
    }

    public function ACTION_profile(){
        $this->me = user::status(USER_LOGIN_URL,"nologin");

        $pgArray = array('base','avatar','setpassword','bind','custom');
        $pg      = iS::escapeStr($_POST['pg']);
        $funname ='__action_profile_'.$pg;
        $methods = get_class_methods(__CLASS__);
        if (in_array ($pg,$pgArray) && in_array ($funname,$methods)) {
            $this->$funname();
        }
    }

    public function ACTION_login(){
        iCMS::$config['user']['login'] OR iPHP::code(0,'user:login:forbidden','uname','json');

        $uname    = iS::escapeStr($_POST['uname']);
        $pass     = md5(trim($_POST['pass']));
        $remember = (bool)$_POST['remember']?ture:false;
        $seccode  = iS::escapeStr($_POST['seccode']);

        if(iCMS::$config['user']['loginseccode']){
            iPHP::seccode($seccode) OR iPHP::code(0,'iCMS:seccode:error','seccode','json');
        }
        $remember && user::$cookietime = 14*86400;
        $user = user::login($uname,$pass,(strpos($uname,'@')===false?'nk':'un'));
        if($user===true){
            iPHP::code(1,0,$this->forward,'json');
        }else{
            // $lang = 'user:login:error';
            // $user && $lang.='_status_'.$user;
            iPHP::code(0,'user:login:error','uname','json');
        }
    }

    public function ACTION_register(){
        iCMS::$config['user']['register'] OR exit(iPHP::lang('user:register:forbidden'));

        $username    = iS::escapeStr($_POST['username']);
        $nickname    = iS::escapeStr($_POST['nickname']);
        $gender      = ($_POST['gender']=='girl'?0:1);
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

        $regip   = iS::escapeStr(iPHP::getIp());
        $regdate = time();
        $gid     = 0;
        $fans    = $follow = $article = $comments = $share = $credit = $type = 0;
        $lastloginip = $lastlogintime = '';
        $status = 1;
        $fields = array('gid', 'username', 'nickname', 'password', 'gender', 'fans', 'follow', 'article', 'comments','share', 'credit', 'regip', 'regdate', 'lastloginip', 'lastlogintime', 'type', 'status');
        $data   = compact ($fields);
        $uid    = iDB::insert('user',$data);
        user::set_cookie($username,$password,array('uid'=>$uid,'username'=>$username,'nickname'=>$nickname,'status'=>$status));
        //user::set_cache($uid);
        iPHP::set_cookie('forward', '',-31536000);
        iPHP::json(array('code'=>1,'forward'=>$this->forward));
    }
    public function ACTION_add_category(){
        $uid  = user::$userid;
        $name = iS::escapeStr($_POST['name']);
        empty($name) && iPHP::code(0,'user:category:empty','add_category','json');
        $fwd  = iCMS::filter($name);
        $fwd && iPHP::code(0,'user:category:filter','add_category','json');
        $max  = iDB::value("SELECT COUNT(cid) FROM `#iCMS@__user_category` WHERE `uid`='$uid'");
        $max >=10 && iPHP::code(0,'user:category:max','add_category','json');
        $count  = 0;
        $fields = array('uid', 'name', 'count');
        $data   = compact ($fields);
        $cid    = iDB::insert('user_category',$data);
        $cid && iPHP::code(1,'user:category:success',$cid,'json');
        iPHP::code(0,'user:category:failure',0,'json');
    }
    public function ACTION_report(){
        iPHP::app('user.class','static');
        user::get_cookie() OR iPHP::code(0,'iCMS:!login',0,'json');

        $iid     = (int)$_POST['iid'];
        $uid     = (int)$_POST['uid'];
        $appid   = (int)$_POST['appid'];
        $reason  = (int)$_POST['reason'];
        $content = iS::escapeStr($_POST['content']);

        $iid OR iPHP::code(0,'iCMS:error',0,'json');
        $reason OR $content OR iPHP::code(0,'iCMS:report:reason_empty',0,'json');

        $addtime = time();
        $ip      = iPHP::getIp();
        $userid  = user::$userid;
        $status  = 0;

        $fields = array('appid', 'userid', 'iid', 'uid', 'reason', 'content', 'ip', 'addtime', 'status');
        $data   = compact ($fields);
        $id     = iDB::insert('user_report',$data);
        iPHP::code(1,'iCMS:report:reason_success',$id,'json');
    }
    private function __action_delete_comment(){
        $id = (int)$_POST['id'];
        $id OR iPHP::code(0,'iCMS:error',0,'json');
        $comment = iDB::row("SELECT `appid`,`iid` FROM `#iCMS@__comment` WHERE `userid` = '".user::$userid."' AND `id`='$id' LIMIT 1;");

        iPHP::import(iPHP_APP_CORE .'/iAPP.class.php');
        $table = app::get_table($comment->appid);

        iDB::query("UPDATE {$table['name']} SET comments = comments-1 WHERE `comments`>0 AND `{$table['primary']}`='{$comment->iid}' LIMIT 1;");

        iDB::query("DELETE FROM `#iCMS@__comment` WHERE `userid` = '".user::$userid."' AND `id`='$id' LIMIT 1;");
        user::update_count(user::$userid,1,'comments','-');
        iPHP::code(1,0,0,'json');
    }
    private function __action_delete_article(){
        $id = (int)$_POST['id'];
        $id OR iPHP::code(0,'iCMS:error',0,'json');
        iDB::query("UPDATE `#iCMS@__article` SET `status` ='2' WHERE `userid` = '".user::$userid."' AND `id`='$id' LIMIT 1;");
        iPHP::code(1,0,0,'json');
    }
    public function ACTION_delete(){
        $this->me = user::status(USER_LOGIN_URL,"nologin");

        $pgArray = array('comment','article');
        $pg      = iS::escapeStr($_POST['pg']);
        $funname ='__action_delete_'.$pg;
        $methods = get_class_methods(__CLASS__);
        if (in_array ($pg,$pgArray) && in_array ($funname,$methods)) {
            $this->$funname();
        }
    }
    public function API_hits($uid = null){
        $uid===null && $uid = (int)$_GET['uid'];
        if($uid){
            $sql = iCMS::hits_sql();
            iDB::query("UPDATE `#iCMS@__user_data` SET {$sql} WHERE `uid` ='$uid'");
        }
    }
    public function API_check(){
        $name  = iS::escapeStr($_GET['name']);
        $value = iS::escapeStr($_GET['value']);
        $a     = iPHP::code(1,'',$name);
        switch ($name) {
            case 'username':
                if(!preg_match("/^[\w\-\.]+@[\w\-]+(\.\w+)+$/i", $value)){
                    $a = iPHP::code(0,'user:register:username:error','username');
                }else{
                    user::check($value,'username') OR $a = iPHP::code(0,'user:register:username:exist','username');
                }
                break;
            case 'nickname':
                if(preg_match("/\d/", $value{0})||cstrlen($value)>20||cstrlen($value)<4){
                    $a = iPHP::code(0,'user:register:nickname:error','username');
                }else{
                    user::check($value,'nickname') OR $a = iPHP::code(0,'user:register:nickname:exist','username');
                }
                break;
            case 'password':
                strlen($value)<6 && $a=iPHP::code(0,'user:password:error','username');
            break;
            case 'seccode':
                iPHP::seccode($value) OR $a = iPHP::code(0,'iCMS:seccode:error','username');
            break;
        }
        iPHP::json($a);
    }
    public function API_follow(){
        $user   = user::status();
        $user OR iPHP::code(0,0,$this->forward,'json');

        $uid    = (int)$user->uid;
        $name   = $user->nickname;
        $fuid   = (int)$_POST['uid'];
        $fname  = iS::escapeStr($_POST['name']);
        $follow = (bool)$_POST['follow'];

        if($follow){ //1 取消关注
            iDB::query("DELETE FROM `#iCMS@__user_follow` WHERE `uid` = '$uid' AND `fuid`='$fuid' LIMIT 1;");
            user::update_count($uid,1,'follow','-');
            user::update_count($fuid,1,'fans','-');
            iPHP::code(1,0,0,'json');
        }else{
            $check = user::follow($uid,$fuid);
            $uid==$fuid && iPHP::code(0,'user:follow:self',0,'json');

            if($check){
                iPHP::code(1,'user:follow:success',0,'json');
            }else{
                $fields = array('uid','name','fuid','fname');
                $data   = compact ($fields);
                iDB::insert('user_follow',$data);
                user::update_count($uid,1,'follow');
                user::update_count($fuid,1,'fans');
                iPHP::code(1,'user:follow:success',0,'json');
            }
        }
    }
    public function API_register(){
        if(iCMS::$config['user']['register']){
            iPHP::set_cookie('forward',$this->forward);
            user::status($this->forward,"login");
            iPHP::view('iCMS://user/register.htm');
        }else{
            iPHP::view('iCMS://user/register.close.htm');
        }
    }
    public function API_data($uid=0){
        $user   = user::status();
        $user OR $user = iPHP::code(0,0,$this->forward);
        $array = array(
            'uid'      => $user->uid,
            'url'      => $user->url,
            'avatar'   => $user->avatar,
            'nickname' => $user->nickname
        );
        iPHP::json($array);
    }
    public function API_logout(){
        user::logout();
        iPHP::code(1,0,$this->forward,'json');
    }
    public function API_login(){
        if(iCMS::$config['user']['login']){
            iPHP::set_cookie('forward',$this->forward);
            user::status($this->forward,"login");
            iPHP::view('iCMS://user/login.htm');
        }else{
            iPHP::view('iCMS://user/login.close.htm');
        }
    }

    public function API_imageUp(){
        $stateInfo ='SUCCESS';
        $F         = iFS::upload('upfile');
        $F['code']  OR  $stateInfo = $F['state'];

        $F['path'] && $url  = iFS::fp($F['path'],'+http');
        iPHP::json(array(
            'title'        => htmlspecialchars($_POST['pictitle'], ENT_QUOTES),
            'originalName' => $F['oname'],
            'url'          => $url,
            'state'        => $stateInfo
        ));
    }
    public function API_ucard(){
        $this->user(true);
        iPHP::view('iCMS://user/card.htm');
    }
    function select($permission='',$_cid="0",$cid="0",$level = 1) {
        $array = iCache::get('iCMS/category.'.iCMS_APP_ARTICLE.'/array');
        foreach((array)$array[$cid] AS $root=>$C) {
            if($C['status'] && $C['isucshow'] && $C['issend'] && empty($C['outurl'])) {
                $tag      = ($level=='1'?"":"├ ");
                $selected = ($_cid==$C['cid'])?"selected":"";
                $text     = str_repeat("│　", $level-1).$tag.$C['name']."[cid:{$C['cid']}][pid:{$C['pid']}]".($C['outurl']?"[∞]":"");
                $C['isexamine'] && $text.= '[审核]';
                $option.="<option value='{$C['cid']}' $selected>{$text}</option>";
            }
           $array[$C['cid']] && $option.=$this->select($permission,$_cid,$C['cid'],$level+1,$url);
        }
        return $option;
    }

}
