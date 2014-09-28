<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: article.table.php 2408 2014-04-30 18:58:23Z coolmoo $
 */
class articleTable {
    public static function count_sql($sql=''){
        return "SELECT count(*) FROM `#iCMS@__article` {$sql}";
    }
    public static function select($sql='',$orderby='',$offset=0,$maxperpage=10){
        $rs = iDB::all("SELECT * FROM `#iCMS@__article` {$sql} order by {$orderby} LIMIT {$offset},{$maxperpage}");
        //iDB::debug(1);
        return $rs;
    }
    public static function fields($update=false){
        $fields  = array('cid', 'scid','ucid','pid',
            'title', 'stitle','keywords', 'tags', 'description','source',
            'author', 'editor', 'userid',
            'haspic','pic','mpic','spic', 'picdata',
            'related', 'metadata', 'pubdate', 'chapter', 'url','clink',
            'orderNum','top', 'postype', 'creative', 'tpl','status');

        $update OR $fields = array_merge ($fields,array('postime', 'hits','hits_toady','hits_toady','hits_yday','hits_week','hits_month','hits_year','comments', 'good', 'bad'));

        return $fields;
    }
    public static function chapterCount($aid){
        $count = iDB::value("SELECT count(id) FROM `#iCMS@__article_data` where `aid` = '$aid'");
        iDB::query("UPDATE `#iCMS@__article` SET `chapter`='$count'  WHERE `id` = '$aid'");
    }
    public static function check_title($title){
        return iDB::value("SELECT `id` FROM `#iCMS@__article` where `title` = '$title'");
    }
    public static function value($field='id',$id=0){
        if(empty($id)){
            return;
        }
        return iDB::value("SELECT {$field} FROM `#iCMS@__article` WHERE `id`='$id';",ARRAY_A);
    }
    public static function row($id=0,$field='*',$sql=''){
        return iDB::row("SELECT {$field} FROM `#iCMS@__article` WHERE `id`='$id' {$sql} LIMIT 1;",ARRAY_A);
    }
    public static function data($id=0,$adid=0,$userid=0){
        $userid && $sql = " AND `userid`='$userid'";
        $rs    = iDB::row("SELECT * FROM `#iCMS@__article` WHERE `id`='$id' {$sql} LIMIT 1;",ARRAY_A);
        if($rs){
            $aid   = $rs['id'];
            $adsql = $adid?" AND `id`='{$adid}'":'';
            $adrs  = iDB::row("SELECT * FROM `#iCMS@__article_data` WHERE `aid`='$aid' {$adsql}",ARRAY_A);
        }
        return array($rs,$adrs);
    }
    public static function body($id=0){
        return iDB::value("SELECT body FROM `#iCMS@__article_data` WHERE aid='$id'");
    }

    public static function batch($data,$ids){
        if(empty($ids)){
            return;
        }
        foreach ( array_keys($data) as $k ){
            $bits[] = "`$k` = '$data[$k]'";
        }
        iDB::query("UPDATE `#iCMS@__article` SET " . implode( ', ', $bits ) . " WHERE `id` IN ($ids)");
    }
    public static function insert($data){
        return iDB::insert('article',$data);
    }
    public static function update($data,$where){
        return iDB::update('article',$data,$where);
    }
// --------------------------------------------------
    public static function data_fields($update=false){
        $fields  = array('subtitle', 'body');
        $update OR $fields  = array_merge ($fields,array('aid'));
        return $fields;
    }
    public static function data_insert($data){
        return iDB::insert('article_data',$data);
    }
    public static function data_update($data,$where){
        return iDB::update('article_data',$data,$where);
    }

    public static function del($id){
        iDB::query("DELETE FROM `#iCMS@__article` WHERE id='$id'");

    }
    public static function del_data($id){
        iDB::query("DELETE FROM `#iCMS@__article_data` WHERE `aid`='$id'");
    }

    public static function del_filedata($var='',$field='indexid'){
        iDB::query("DELETE FROM `#iCMS@__filedata` WHERE `$field` = '{$var}'");
    }
    public static function select_filedata_indexid($indexid='',$fields='filename`,`path`,`ext'){
        return iDB::all("SELECT `$fields` FROM `#iCMS@__filedata` WHERE `indexid`='$indexid'");
    }
    public static function filedata_value($var='',$field='filename',$var_field='indexid'){
        return iDB::value("SELECT `$var_field` FROM `#iCMS@__filedata` WHERE `$field` ='$var'");
    }
    public static function filedata_update_indexid($indexid='',$filename=''){
        return iDB::query("UPDATE `#iCMS@__filedata` SET `indexid` = '$indexid' WHERE `filename` ='$filename'");
    }
    public static function del_comment($iid){
        iDB::query("DELETE FROM `#iCMS@__comment` WHERE iid='$iid' and appid='".iCMS_APP_ARTICLE."'");
    }


}

