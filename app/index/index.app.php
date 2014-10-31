<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: index.app.php 2406 2014-04-28 02:24:46Z coolmoo $
 */
class indexApp {
	public $methods	= array('iCMS','index');
    public function __construct() {}
    public function do_iCMS($a = null) {
        return $this->index($a);
    }
    public function API_iCMS(){
        return $this->do_iCMS();
    }
    public function index($a = null){
        $index_name = $a[1]?$a[1]:iCMS::$config['template']['index_name'];
        $index_tpl  = $a[0]?$a[0]:iCMS::$config['template']['index'];
        $index_name OR $index_name = 'index';
        $iurl = iURL::get('index',array('urlRule'=>$index_name.iCMS::$config['router']['html_ext']));
        iCMS::$config['template']['index_mode'] && iCMS::gotohtml($iurl->path,$iurl->href);
        iPHP::$iTPL_MODE=="html" && iCMS::set_html_url($iurl);
        $html = iPHP::view($index_tpl);
        if(iPHP::$iTPL_MODE=="html") return array($html,$iurl);
    }
}
