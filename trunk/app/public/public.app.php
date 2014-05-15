<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: public.app.php 1392 2013-05-20 12:28:08Z coolmoo $
 */
class publicApp {
	public $methods	= array('seccode');
	public function API_seccode(){
		iPHP::loadClass("Seccode");
		@header("Expires: -1");
		@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
		@header("Pragma: no-cache");
		iSeccode::run();
	}
}