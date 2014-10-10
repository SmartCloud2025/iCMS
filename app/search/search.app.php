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
        if(!mb_check_encoding($q,"UTF-8")){
            $q  = mb_convert_encoding($q,"UTF-8","gbk");
        }
        $q  = iS::escapeStr($q);

        empty($q) && iPHP::throwException('应用程序运行出错.亲!搜点什么吧!!', 60001);

        $search['title']   = stripslashes($q);
        $search['keyword'] = $q;
        $tpl = '{iTPL}/search.htm';

        // $this->surl     = iPHP::router('/api/search');//"http://www.ladyband.com/search?q=".$search['name'].'&t='.$_GET['t'];
        // $GLOBALS['iPage']['url']  = $this->surl.'&page={P}';
        // $GLOBALS['iPage']['html'] = array('enable'=>true,'index'=>$this->surl,'ext'=>"");
        //
        //iCMS::value('callback',$_GET['callback']);
        $this->slog($q);

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
