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
    public function API_iCMS(){
        return $this->do_iCMS();
    }
    public function do_iCMS($a = null) {
        if ($_GET['name']) {
            $name   = $_GET['name'];
            $encode = mb_detect_encoding($name, array("ASCII","UTF-8","GB2312","GBK","BIG5"));
            $encode!='UTF-8' && $name = mb_convert_encoding($name,"UTF-8",$encode);

            $val   = iS::escapeStr($name);
            $field = 'name';
        } elseif ($_GET['tkey']) {
            $field = 'tkey';
            $val   = iS::escapeStr($_GET['tkey']);
        } elseif ($_GET['id']) {
            $field = 'id';
            $val   = (int)$_GET['id'];
        }else{
            iPHP::throw404('标签请求出错', 30001);
        }
        return $this->tag($val, $field);
    }

    public function tag($val, $field = 'name', $tpl = 'tag') {
        $val OR iPHP::throw404('运行出错！TAG不能为空', 30002);
        $tag = iDB::row("SELECT * FROM `#iCMS@__tags` where `$field`='$val' AND `status`='1'  LIMIT 1;", ARRAY_A);
        if(empty($tag)){
            if($tpl){
                iPHP::throw404('运行出错！找不到标签: <b>'.$field.':'. $val.'</b>', 30003);
            }else{
                return false;
            }
        }
        $tag = $this->value($tag);
        if ($tpl) {
            iCMS::hooks('enable_comment',true);
            iPHP::assign("tag", $tag);
            iPHP::assign('category',$tag['category']);
            iPHP::assign('tag_category',$tag['tag_category']);
            if (strstr($tpl, '.htm')) {
                return iPHP::view($tpl, 'tag');
            }
            $html = iPHP::view($tag['tpl'] ? $tag['tpl'] : '{iTPL}/tag.index.htm', 'tag');
            if(iPHP::$iTPL_MODE=="html") return array($html,$tag);
        }else{
            return $tag;
        }
    }
    public function value($tag) {
        $categoryApp = iPHP::app("category");
        if($tag['cid']){
            $category        = $categoryApp->category($tag['cid'],false);
            $tag['category'] = $categoryApp->get_lite($category);
        }
        if($tag['tcid']){
            $tag_category        = $categoryApp->category($tag['tcid'],false);
            $tag['tag_category'] = $categoryApp->get_lite($tag_category);
        }

        $tag['iurl'] = iURL::get('tag', array($tag, $category, $tag_category));
        $tag['url'] OR $tag['url'] = $tag['iurl']->href;
        $tag['link']  = '<a href="'.$tag['url'].'" class="tag" target="_blank">'.$tag['name'].'</a>';

        if($category['mode'] && stripos($tag['url'], '.php?')===false){
            iCMS::set_html_url($tag['iurl']);
        }

        $tag['related']  && $tag['relArray'] = explode(',', $tag['related']);
        $tag['appid'] = iCMS_APP_TAG;
        $tag['pic']   = get_pic($tag['pic']);
        return $tag;
    }
    public function get_array($tags) {
        if(empty($tags)){
            return;
        }
        $array  = explode(',', $tags);
        foreach ($array as $key => $tag) {
            $tag && $tag_array[$key] = $this->tag($tag,'name',false);
        }
        return $tag_array;
    }

}
