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

    public function __construct() {}

    public function do_iCMS($a = null) {
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

    public function decode($tags) {
        if(empty($tags)){
            return;
        }
        $array  = json_decode($tags);
        foreach ($array as $key => $value) {
            $tag_array[$key] = $this->tag($value[1],'id',false);
        }
        return $tag_array;
    }

    public function tag($val, $field = 'name', $tpl = 'tag') {
        $val OR iPHP::throwException('运行出错！TAG不能为空', 30001);
        $tag = iDB::row("SELECT * FROM `#iCMS@__tags` where `$field`='$val' LIMIT 1;", ARRAY_A);

        iPHP::http404($tag, 'TAG:empty');
        $tag = $this->value($tag);
        if ($tpl) {
            iCMS::hooks('enable_comment',true);
            iPHP::assign("tag", $tag);
            if (strstr($tpl, '.htm')) {
                return iPHP::view($tpl, 'tag');
            }
            $html = iPHP::view($tag['tpl'] ? $tag['tpl'] : '{iTPL}/tag.index.htm', 'tag');
            if(iPHP::$iTPL_mode=="html") return array($html,$tag);
        }else{
            return $tag;
        }
    }
    public function value($tag) {
        if($tag['cid']){
            $category        = iCache::get('iCMS/category/' . $tag['cid']);
            $tag['category'] = iCMS::get_category_lite($category);
        }
        if($tag['tcid']){
            $tag_category        = iCache::get('iCMS/category/' . $tag['tcid']);
            $tag['tag_category'] = iCMS::get_category_lite($tag_category);
        }
        $tag['iurl'] = iURL::get('tag', array($tag, $category, $tag_category));
        $tag['url'] OR $tag['url'] = $tag['iurl']->href;
        $tag['link']  = '<a href="'.$tag['url'].'" class="tag" target="_blank">'.$tag['name'].'</a>';
        $category['mode']&& iCMS::setpage($tag['iurl']);
        $tag['related']  && $tag['relArray'] = explode(',', $tag['related']);
        $tag['appid'] = iCMS_APP_TAG;
        $tag['pic']   = get_pic($tag['pic']);
        return $tag;
    }
}
