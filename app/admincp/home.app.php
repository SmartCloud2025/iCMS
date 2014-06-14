<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: home.app.php 2412 2014-05-04 09:52:07Z coolmoo $
*/
class homeApp{
    function __construct() {}
    function dologout(){
    	iMember::logout();
    	iPHP::OK('注销成功!','url:'.__SELF__);
    }
    function doiCMS(){
        //数据统计
        $rs=iDB::getArray("SHOW FULL TABLES FROM `".iPHP_DB_NAME."` WHERE table_type = 'BASE TABLE';");
        foreach($rs as $k=>$val) {
            if(strstr(iPHP_DB_PREFIX, $val['Tables_in_'.iPHP_DB_NAME])===false) {
                $iTable[]=strtoupper($val['Tables_in_'.iPHP_DB_NAME]);
            }else {
                $oTable[]=$val['Tables_in_'.iPHP_DB_NAME];
            }
        }
        $content_datasize = 0;
        $tables = iDB::getArray("SHOW TABLE STATUS");
        $_count	= count($tables);
        for ($i=0;$i<$_count;$i++) {
            $tableName	= strtoupper($tables[$i]['Name']);
            if(in_array($tableName,$iTable)) {
                $datasize += $tables[$i]['Data_length'];
                $indexsize += $tables[$i]['Index_length'];
                if (stristr(strtoupper(iPHP_DB_PREFIX."article,".iPHP_DB_PREFIX."category,".iPHP_DB_PREFIX."comment,".iPHP_DB_PREFIX."article_data"),$tableName)) {
                    $content_datasize += $tables[$i]['Data_length']+$tables[$i]['Index_length'];
                }
            }
        }
        $acc = iDB::getValue("SELECT count(*) FROM #iCMS@__category WHERE `appid`='".iCMS_APP_ARTICLE."'");
        $tac = iDB::getValue("SELECT count(*) FROM #iCMS@__category WHERE `appid`='".iCMS_APP_TAG."'");
        $pac = iDB::getValue("SELECT count(*) FROM #iCMS@__category WHERE `appid`='".iCMS_APP_PUSH."'");
        
        $ac  = iDB::getValue("SELECT count(*) FROM #iCMS@__article");
        $ac0 = iDB::getValue("SELECT count(*) FROM #iCMS@__article WHERE `status`='0'");
        $ac2 = iDB::getValue("SELECT count(*) FROM #iCMS@__article WHERE `status`='2'");
        
        $ctc = iDB::getValue("SELECT count(*) FROM #iCMS@__comment");
        $tc  = iDB::getValue("SELECT count(*) FROM #iCMS@__tags");
        $kc  = iDB::getValue("SELECT count(*) FROM #iCMS@__keywords");
        $pc  = iDB::getValue("SELECT count(*) FROM #iCMS@__push");
        $uc  = iDB::getValue("SELECT count(*) FROM #iCMS@__user");
        $fdc  = iDB::getValue("SELECT count(*) FROM #iCMS@__filedata");
        $lc  = iDB::getValue("SELECT count(*) FROM #iCMS@__links");

    	include iACP::view("home");
    }
    function okORno($o) {
        return $o?'<font color=green><b>√</b></font>':'<font color=red><b>×</b></font>';
    }
}
