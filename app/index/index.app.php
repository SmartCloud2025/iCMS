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
    function do_iCMS($indexTpl=null,$indexRule=null) {
        $indexRule  OR $indexRule = iCMS::$config['site']['indexRule'];
        $indexTpl	OR $indexTpl  = iCMS::$config['template']['index'];
        $indexRule .= iCMS::$config['router']['html_ext'];
        $iurl       = iURL::get('index',array('urlRule'=>$indexRule));
        iCMS::gotohtml($iurl->path,$iurl->href,iCMS::$config['site']['mode']);
        (iCMS::$config['site']['mode']||iPHP::$iTPL_mode=="html") && iPHP::page($iurl);
        $html = iPHP::view($indexTpl);
        if(iPHP::$iTPL_mode=="html") return array($html,$iurl);
    }
}
