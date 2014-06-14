<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: comment.app.php 2406 2014-04-28 02:24:46Z coolmoo $
 */
class commentApp {
	public $methods	= array('like','json','add','report','form','list');
    function __construct() {
        $this->userid   = (int)iPHP::getCookie('userid');
        $this->nickname = iS::escapeStr(iPHP::getUniCookie('nickname'));
        $this->id       = (int)$_GET['id'];
    }
    public function API_list(){
        $vars['iid']     = (int)$_GET['iid'];
        $vars['cid']     = (int)$_GET['cid'];
        $vars['appid']   = (int)$_GET['appid'];
        $vars['display'] = $_GET['display'];
        iPHP::assign('comment',$vars);
        return iPHP::view('iCMS://comment/list.default.htm');
    }
    public function API_form(){
        $vars['iid']     = (int)$_GET['iid'];
        $vars['cid']     = (int)$_GET['cid'];
        $vars['appid']   = (int)$_GET['appid'];
        $vars['title']   = iS::escapeStr($_GET['title']);
        $vars['display'] = $_GET['display'];
        iPHP::assign('comment',$vars);
        return iPHP::view('iCMS://comment/form.default.htm');
    }
    public function API_like(){
        $this->id OR iPHP::code(0,'iCMS:article:empty_id',0,'json');
        $lckey = 'like_comment_'.$this->id;
        $like  = (int)iPHP::getCookie($lckey);
        $like && iPHP::code(0,'iCMS:comment:!like',0,'json');
        iDB::query("UPDATE `#iCMS@__comment` SET `up`=up+1 WHERE `id`='$this->id'");
        iPHP::setCookie($lckey,$this->userid,86400);
        iPHP::code(1,'iCMS:comment:like',0,'json');
    }
    public function API_json(){
        iPHP::assign('appid',iCMS_APP_ARTICLE);
        iPHP::assign('id',(int)$_GET['id']);
        iPHP::assign('iid',(int)$_GET['iid']);
        iPHP::view('iCMS://comment/api.json.htm');
    }
    public function ACTION_add(){
        $iid        = (int)$_POST['iid'];
        $cid        = (int)$_POST['cid'];
        $suid       = (int)$_POST['suid'];
        $reply_uid  = (int)$_POST['uid'];
        $reply_name = iS::escapeStr($_POST['name']);
        $title      = iS::escapeStr($_POST['title']);
        $content    = iS::escapeStr($_POST['content']);
        $iid OR iPHP::code(0,'iCMS:article:empty_id',0,'json');
        $content OR iPHP::code(0,'iCMS:comment:empty',0,'json');

        $addtime = time();
        $ip      = iPHP::getIp();
        iDB::query("INSERT INTO `#iCMS@__comment`
            (`appid`, `cid`, `iid`,`suid`, `title`,`uid`, `name`,  `content`, `reply_uid`,`reply_name`, `addtime`, `status`, `up`, `down`, `ip`, `quote`, `floor`)
VALUES ('".iCMS_APP_ARTICLE."', '$cid', '$iid','$suid', '$title', '$this->userid', '$this->nickname', '$content', '$reply_uid','$reply_name', '$addtime', '1', '0', '0', '$ip', '0', '0');");
        iDB::query("UPDATE `#iCMS@__article` SET comments=comments+1 WHERE `id` ='{$iid}' limit 1");
        $id = iDB::$insert_id;
        iPHP::code(1,'iCMS:comment:success',$id,'json');
    }
    public function ACTION_report(){
        $iid     = (int)$_POST['id'];
        $uid     = (int)$_POST['uid'];
        $reason  = (int)$_POST['reason'];
        $content = iS::escapeStr($_POST['content']);

        $iid OR iPHP::code(0,'iCMS:error',0,'json');
        $reason OR $content OR iPHP::code(0,'iCMS:comment:reason_empty',0,'json');

        $addtime = time();
        $ip      = iPHP::getIp();
        iDB::query("INSERT INTO `#iCMS@__report`
        (`appid`, `userid`, `iid`, `uid`, `reason`, `content`, `ip`, `addtime`, `status`)
 VALUES ('".iCMS_APP_COMMENT."', '$this->userid', '$iid', '$uid','$reason', '$content', '$ip', '$addtime', '0');");
        $id = iDB::$insert_id;
        iPHP::code(1,'iCMS:comment:reason_success',$id,'json');
    }
    //---------------------------
    public function doAjax($a = null) {
    	$type	= $_GET['type'];
		in_array($type, array("detail")) OR die();
		
    	$ln		= ($GLOBALS['page']-1)<0?0:$GLOBALS['page']-1;
    	$id		= (int)$_GET['id'];
    	$total	= iDB::getValue("SELECT count(*) FROM `#iCMS@__{$type}_cmt` WHERE `indexId`='$id' AND `status`='1'");
    	$rs		= iDB::getArray("SELECT * FROM `#iCMS@__{$type}_cmt` WHERE `indexId`='$id' AND `status`='1' ORDER BY id DESC LIMIT 10");
        $_count=count($rs);
        for ($i=0;$i<$_count;$i++){
        	$rs[$i]['lou']			= $total-($i+$ln*$maxperpage);
            $rs[$i]['user']['url']	= userData($rs[$i]['userid'],'url');
            $rs[$i]['user']['name']	= $rs[$i]['nickname'];
            $rs[$i]['user']['id']	= $rs[$i]['userid'];
            $rs[$i]['user']['face']	= userData($rs[$i]['userid'],'face',48);
            $rs[$i]['date']			= get_date($rs[$i]['addtime'],"m月d日 H:m");
            
        }
    	echo $_GET['callback'].'('.json_encode($rs).')';
    }

    public function doPost($a = null) {
        $type     = $_POST['type'];
        in_array($type, array("detail")) OR die();
        
        $uId      = (int)$_POST['uId'];
        $indexId  = (int)$_POST['indexId'];
        $itemId   = (int)$_POST['itemId'];
        $content  = iS::escapeStr($_POST['content']);
        
        $userid   = iPHP::getCookie('userid');
        $nickname = iPHP::getUniCookie('nickname');
		
		empty($userid) && iPHP::json(array('code'=>0,'msg'=>'nologin'));
	    empty($content) && iPHP::json(array('code'=>0,'msg'=>'你也可以顺便说点什么 O(∩_∩)O'));
	    
		iDB::query("INSERT INTO `#iCMS@__{$type}_cmt`
            (`uId`,`indexId`, `itemId`, `userid`, `nickname`, `content`, `type`, `addtime`, `status`)
VALUES ('$uId','$indexId', '$itemId', '$userid', '$nickname', '$content', '$type', '".time()."', '1');");
		
		iDB::query("UPDATE `#iCMS@__{$type}_list` SET `comment` = comment+1 WHERE `id` = '$indexId';");
		iPHP::json(array('code'=>1,'msg'=>''));
    }
}
