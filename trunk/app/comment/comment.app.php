<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: comment.app.php 2406 2014-04-28 02:24:46Z coolmoo $
 */
class commentApp {
	public $methods	= array('index','ajax','post','form');
    function __construct() {}
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
    public function api_form(){
        $vars['iid']     = (int)$_GET['iid'];
        $vars['cid']     = (int)$_GET['cid'];
        $vars['appid']   = (int)$_GET['appid'];
        $vars['title']   = iS::escapeStr($_GET['title']);
        $vars['display'] = $_GET['display'];
        iPHP::assign('comment',$vars);
        return iCMS::tpl('iCMS','comment.form.default');
    }
}
