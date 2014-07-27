<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: filter.app.php 2372 2014-03-16 07:24:56Z coolmoo $
*/
class filterApp{
    function __construct() {
    }
    function do_iCMS(){
        $filter = iACP::getConfig(1,'word.filter');
        $disable = iACP::getConfig(1,'word.disable');
        foreach((array)$filter AS $k=>$val) {
            $filterArray[$k]=implode("=",(array)$val);
        }
    	include iACP::view("filter");
    }
    function do_save(){
        $disable	= explode("\n",iS::escapeStr($_POST['disable']));
        $filter		= explode("\n",iS::escapeStr($_POST['filter']));
        foreach($filter AS $k=> $val) {
            $filterArray[$k]=explode("=",$val);
        }
        iACP::setConfig($filterArray,'word.filter',1,true);
        iACP::setConfig($disable,'word.disable',1,true);
        iPHP::success('更新完成');
    }
    function cache(){
        $filter		= iACP::getConfig(1,'word.filter');
        $disable 	= iACP::getConfig(1,'word.disable');
        foreach((array)$filter AS $k=>$val) {
            $filterArray[$k]=implode("=",(array)$val);
        }
    	iCache::set('iCMS/word.filter',$filter,0);
    	iCache::set('iCMS/word.disable',$filterArray,0);
    }
}
