<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: comment.app.php 2406 2014-04-28 02:24:46Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');

class commentApp {
	public $methods	= array('like','json','add','form','list','goto');
    function __construct() {
        $this->id = (int)$_GET['id'];
    }
    public function API_goto(){
        $appid = (int)$_GET['appid'];
        $iid   = (int)$_GET['iid'];

        iPHP::import(iPHP_APP_CORE .'/iAPP.class.php');
        $url = app::get_url($appid,$iid);
        iPHP::gotourl($url);
    }
    public function API_list(){
        $_GET['_display'] = $_GET['display'];
        $_GET['display']  = 'default';
        iPHP::app('comment.func');
        return comment_list($_GET);
    }
    public function API_form(){
        $_GET['_display'] = $_GET['display'];
        $_GET['display']  = 'default';
        iCMS::hooks('enable_comment',true);
        iPHP::app('comment.func');
        return comment_form($_GET);
    }

    public function API_like(){
        iPHP::app('user.class','static');
        user::get_cookie() OR iPHP::code(0,'iCMS:!login',0,'json');

        $this->id OR iPHP::code(0,'iCMS:article:empty_id',0,'json');
        $lckey = 'like_comment_'.$this->id;
        $like  = (int)iPHP::get_cookie($lckey);
        $like && iPHP::code(0,'iCMS:comment:!like',0,'json');
        iDB::query("UPDATE `#iCMS@__comment` SET `up`=up+1 WHERE `id`='$this->id'");
        iPHP::set_cookie($lckey,user::$userid,86400);
        iPHP::code(1,'iCMS:comment:like',0,'json');
    }
    public function API_json(){
        $vars = array(
            'appid'       => iCMS_APP_ARTICLE,
            'id'          => (int)$_GET['id'],
            'iid'         => (int)$_GET['iid'],
            'date_format' => 'Y-m-d H:i'
        );
        $_GET['by'] && $vars['by'] = iS::escapeStr($_GET['by']);
        $_GET['date_format'] && $vars['date_format'] = iS::escapeStr($_GET['date_format']);
        iPHP::assign('vars',$vars);
        iPHP::view('iCMS://comment/api.json.htm');
    }
    public function ACTION_add(){
        iPHP::app('user.class','static');
        user::get_cookie() OR iPHP::code(0,'iCMS:!login',0,'json');

        $appid      = (int)$_POST['appid'];
        $iid        = (int)$_POST['iid'];
        $cid        = (int)$_POST['cid'];
        $suid       = (int)$_POST['suid'];
        $reply_id   = (int)$_POST['id'];
        $reply_uid  = (int)$_POST['userid'];
        $reply_name = iS::escapeStr($_POST['name']);
        $title      = iS::escapeStr($_POST['title']);
        $content    = iS::escapeStr($_POST['content']);
        $iid OR iPHP::code(0,'iCMS:article:empty_id',0,'json');
        $content OR iPHP::code(0,'iCMS:comment:empty',0,'json');

        $appid OR $appid = iCMS_APP_ARTICLE;
        $addtime = time();
        $ip      = iPHP::getIp();
        $userid  = user::$userid;
        $name    = user::$nickname;
        $status  = '1';
        $up      = '0';
        $down    = '0';
        $quote   = '0';
        $floor   = '0';

        $fields = array('appid', 'cid', 'iid','suid', 'title','userid', 'name',  'content', 'reply_id','reply_uid','reply_name', 'addtime', 'status', 'up', 'down', 'ip', 'quote', 'floor');
        $data   = compact ($fields);
        $id     = iDB::insert('comment',$data);
        iDB::query("UPDATE `#iCMS@__article` SET comments=comments+1 WHERE `id` ='{$iid}' limit 1");
        iPHP::code(1,'iCMS:comment:success',$id,'json');
    }

}
