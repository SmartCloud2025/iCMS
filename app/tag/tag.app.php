<?php

/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: tag.app.php 141 2013-03-13 14:01:43Z coolmoo $
 */
class tagApp {

    public $methods = array('iCMS');

    function __construct() {}

    function doiCMS($a = null) {
        if ($_GET['name']) {
            $name  = $_GET['name'];
            mb_check_encoding($name, "UTF-8") OR $name = mb_convert_encoding($name, "UTF-8", "gbk");
            $val   = iS::escapeStr($name);
            $field = 'name';
        } elseif ($_GET['tkey']) {
            $field = 'tkey';
            $val   = iS::escapeStr($_GET['tkey']);
        } elseif ($_GET['id']) {
            $field = 'id';
            $val   = (int)$_GET['id'];            
        }
        return $this->tag($val, $field);
    }

    function tag($val, $field = 'name', $tpl = 'tag') {
        $val OR iPHP::throwException('应用程序运行出错.TAG不能为空', 6001);
        $ftags	= false;
		if($field == "name"){
			$rs = iDB::getRow("SELECT * FROM `#iCMS@__ftags` where `name`='$val' AND `status`='1' LIMIT 1;", ARRAY_A);
			$rs	&&	$ftags	= true;
		}
		if(empty($rs)){
        	$rs = iDB::getRow("SELECT * FROM `#iCMS@__tags` where `$field`='$val' LIMIT 1;", ARRAY_A);
		}
        if ($field == "name" && empty($rs)) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: http://www.ladyband.com/search?q=' . $val . '&t=a');
            exit;
        }

        iPHP::http404($rs, 'TAG:empty');

        $category = $tCategory = array();
        if ($rs['cid']) {
            $categoryApp = iPHP::app("category");
            $category = $categoryApp->category($rs['cid'], false);
        }

        if ($rs['pid'] == "1" && $rs['tkey'] && $category && $field == "name" && $ftags==false) {
            $url = "http://www.ladyband.com/" . $category['dir'] . '/' . $rs['tkey'];
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $url);
            exit;
        }
        if ($rs['tcid']) {
            $tCategory = iCache::get('iCMS/category/' . $rs['tcid']);
        }
        $rs['iurl'] = iURL::get('tag', array($rs, $category, $tCategory));

        $rs['url'] OR $rs['url'] = $rs['iurl']->href;


        $category['mode'] && iPHP::page($rs['iurl']);

        if ($rs['related']) {
            $relArray = explode(',', $rs['related']);
            iPHP::assign('relArray', $relArray);
        }
        iPHP::assign("tag", $rs);
        if ($tpl) {
            if ($_GET['debug']) {
                iCMS::clear_compiled_tpl('2013/zt.htm');
            }
            if (strstr($tpl, '.htm')) {
                return iPHP::view($tpl, 'tag');
            }
            return iPHP::view($rs['tpl'] ? $rs['tpl'] : '{iTPL}/tag.htm', 'tag');
        } else {
            return $rs;
        }
    }

}