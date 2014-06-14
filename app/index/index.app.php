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
    function doiCMS($indexTpl=null,$indexRule=null) {
        $indexRule  OR $indexRule	= iCMS::$config['site']['indexRule'];
        $indexTpl	OR $indexTpl	= iCMS::$config['site']['indexTPL'];
        $indexRule .= iCMS::$config['router']['htmlext'];
		$iurl		= iURL::get('index',array('urlRule'=>$indexRule));
        iCMS::gotohtml($iurl->path,$iurl->href,iCMS::$config['site']['mode']);
        (iCMS::$config['site']['mode']||iPHP::$iTPLMode=="html") && iPHP::page($iurl);
        $html	= iPHP::view($indexTpl);
        if(iPHP::$iTPLMode=="html") return array($html,$iurl);
    }
}
