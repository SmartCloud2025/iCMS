<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: search.app.php 1392 2013-05-20 12:28:08Z coolmoo $
 */
class searchApp {
	public $methods	= array('iCMS');
	public function API_iCMS(){
        return $this->search();
	}
    public function search($a = null) {
        $q  = htmlspecialchars(rawurldecode($_GET['q']));
        $encode = mb_detect_encoding($q, array("ASCII","UTF-8","GB2312","GBK","BIG5"));
        if(strtoupper($encode)!='UTF-8'){
            if (function_exists('iconv')) {
                $q  = iconv($encode,'UTF-8//IGNORE', $q);
            } elseif (function_exists('mb_convert_encoding')) {
                $q  = mb_convert_encoding($q,'UTF-8//IGNORE',$encode);
            }
        }
        $q  = iS::escapeStr($q);

        //empty($q) && iPHP::throw404('应用程序运行出错.亲!搜点什么吧!!', 60001);
        $fwd = iCMS::filter($q);
        $fwd && iPHP::throw404('非法搜索词!', 60002);

        $search['title']   = stripslashes($q);
        $search['keyword'] = $q;
        $tpl = '{iTPL}/search.htm';
        $q && $this->slog($q);
        iPHP::assign("search",$search);
        return iPHP::view($tpl,'search');
    }
    private function slog($search){
        $sid    = iDB::value("SELECT `id` FROM `#iCMS@__search_log` WHERE `search` = '$search' LIMIT 1");
        if($sid){
            iDB::query("UPDATE `#iCMS@__search_log` SET `times` = times+1 WHERE `id` = '$sid';");
        }else{
            iDB::query("INSERT INTO `#iCMS@__search_log` (`search`, `times`, `addtime`) VALUES ('$search', '1', '".time()."');");
        }
    }
}
