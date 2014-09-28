<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: index.app.php 2406 2014-04-28 02:24:46Z coolmoo $
 */
class indexApp {
	public $methods	= array('iCMS');
    function __construct() {}
    function do_iCMS($index_tpl=null,$index_rule=null) {
        $index_rule OR $index_rule = iCMS::$config['site']['index_rule'];
        $index_tpl	OR $index_tpl  = iCMS::$config['template']['index'];
        $index_rule.= iCMS::$config['router']['html_ext'];
        $iurl = iURL::get('index',array('urlRule'=>$index_rule));
        iCMS::gotohtml($iurl->path,$iurl->href,iCMS::$config['site']['mode']);
        iPHP::$iTPL_MODE=="html" && iCMS::setpage($iurl);
        $html = iPHP::view($index_tpl);
        if(iPHP::$iTPL_MODE=="html") return array($html,$iurl);
    }
}
