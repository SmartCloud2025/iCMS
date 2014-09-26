<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: category.app.php 2412 2014-05-04 09:52:07Z coolmoo $
 */
class categoryApp{
	public $methods	= array('iCMS');
    function __construct($appid = iCMS_APP_ARTICLE) {
    	$this->appid = iCMS_APP_ARTICLE;
    	$appid && $this->appid = $appid;
    	$_GET['appid'] && $this->appid	= (int)$_GET['appid'];
    }
    public function do_iCMS($tpl = 'index') {
        $cid = (int)$_GET['cid'];
        $dir = iS::escapeStr($_GET['dir']);
		if(empty($cid) && $dir){
			$cid	= iCache::get('iCMS/category/dir2cid',$dir);
            empty($cid) && iPHP::throwException('运行出错！找不到该栏目<b>dir:'.$dir.'</b> 请更新栏目缓存或者确认栏目是否存在', 20002);
		}
    	return $this->category($cid,$tpl);
    }
    public function category($id,$tpl='index') {
        $category = iCache::get('iCMS/category/'.$id);

       	$category OR iPHP::throwException('运行出错！找不到该栏目<b>cid:'. $id.'</b> 请更新栏目缓存或者确认栏目是否存在', 20001);
        if($tpl && $category['outurl']) return iPHP::gotourl($category['outurl']);

        $iurl = $category['iurl'];
        $tpl && iCMS::gotohtml($iurl->path,$iurl->href,$category['mode']);

        $category['iurl']   = (array)$iurl;
        $category['subid']  = iCache::get('iCMS/category/rootid',$id);
        $category['subids'] = implode(',',(array)$category['subid']);

        $category['parent'] = array();
        if($category['rootid']){
            $_parent = iCache::get('iCMS/category/'.$category['rootid']);
            $category['parent'] = iCMS::get_category_lite($_parent);
            unset($_parent);
        }
        if($category['password']){
            $categoryAuth         = iPHP::get_cookie('categoryAuth_'.$id);
            list($CA_cid,$CA_psw) = explode('#=iCMS!=#',authcode($categoryAuth,'DECODE'));
        	if($CA_psw!=md5($category['password'])){
        		iPHP::assign('forward',__REF__);
	        	iPHP::view('{iTPL}/category.password.htm','category.password');
	        	exit;
        	}
        }

        $category['hasbody'] && $category['body'] = iCache::get('iCMS/category/'.$category['cid'].'.body');
        ($category['mode'] && $tpl) && iCMS::setpage($iurl);
        $category['appid']  = iCMS_APP_CATEGORY;

        if($tpl) {
            iCMS::hooks('enable_comment',true);
            iPHP::assign('category',$category);

            if(strstr($tpl,'.htm')){
            	return iPHP::view($tpl,'category');
            }
            $GLOBALS['page']>1 && $tpl='list';
            $html	= iPHP::view($category[$tpl.'TPL'],'category.'.$tpl);
            if(iPHP::$iTPL_mode=="html") return array($html,$category);
        }else{
        	return $category;
        }
    }

}
