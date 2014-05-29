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
    function __construct($appid=1) {
    	$this->appid	= 1;
    	$appid && $this->appid	= $appid;
    	$_GET['appid'] && $this->appid	= (int)$_GET['appid'];
    }
    public function doiCMS($tpl = 'index') {
		$cid	= (int)$_GET['cid'];
		$dir	= iS::escapeStr($_GET['dir']);
		if(empty($cid) && $dir){
			$cid	= iCache::get('iCMS/category/dir2cid',$dir);
		}
		empty($cid) && iPHP::throwException('应用程序运行出错.找不到该栏目: '.($dir?"<b>dir:$dir</b>":"<b>cid:$id</b>").' 请确认栏目是否存在', 3001);
		
    	return $this->category($cid,$tpl);
    }
    public function category($id,$tpl='index') {
        $rs	= iCache::get('iCMS/category/'.$id);

       	$rs OR iPHP::throwException('应用程序运行出错.找不到该栏目: <b>cid:'. $id.'</b> 请更新栏目缓存或者确认栏目是否存在', 3002);
       	$rs['outurl']	= $rs['url'];
        if($tpl && $rs['outurl']) return iPHP::gotourl($rs['outurl']);
		
		$iurl			= $rs['iurl'];
        $rs['iurl']    	= (array)$iurl;
        $rs['url']    	= $iurl->href;
        $rs['link']		= "<a href='{$rs['url']}'>{$rs['name']}</a>";
        $rs['nav']		= $this->nav($rs);
        $rootidA		= iCache::get('iCMS/category/rootid');
        $rs['subid']    = $rootidA[$id];
        $rs['subids']   = implode(',',(array)$rs['subid']);

        $rs['parent']	= array();
        if($rs['rootid']){
        	$rs['parent']			= iCache::get('iCMS/category/'.$rs['rootid']);
	        $rs['parent']['url']	= $rs['parent']['iurl']->href;
	        $rs['parent']['link']	= "<a href='{$rs['parent']['url']}'>{$rs['parent']['name']}</a>";
        }
        if($rs['password']){
        	$categoryAuth	= iPHP::getCookie('categoryAuth_'.$id);
        	list($CA_cid,$CA_psw)	= explode('#=iCMS!=#',authcode($categoryAuth,'DECODE'));
        	if($CA_psw!=md5($rs['password'])){
        		iPHP::assign('forward',__REF__);
	        	iPHP::view('{iTPL}/category.password.htm','category.password');
	        	exit;
        	}
        }
        
        ($rs['mode'] && $tpl) && iPHP::page($iurl);
        
		iPHP::assign('category',$rs);
        if($tpl) {
            iCMS::gotohtml($iurl->path,$iurl->href,$rs['mode']);
            if(strstr($tpl,'.htm')){
            	return iPHP::view($tpl,'category');
            }
            $GLOBALS['page']>1 && $tpl='list';
            $html	= iPHP::view($rs[$tpl.'TPL'],'category.'.$tpl);
            if(iPHP::$iTPLMode=="html") return array($html,$rs);
        }else{
        	return $rs;
        }
    }
    function nav($C) {
        if($C) {
        	$iurl	= (array)$C['iurl'];
            $_nav	= "<a href='{$iurl['href']}'>{$C['name']}</a>";
            if($C['rootid']){
            	$rc	= iCache::get('iCMS/category/'.$C['rootid']);
            	$nav.=$this->nav($rc).iPHP::lang('iCMS:navTag');
        	}
            $nav.= $_nav;
        }
        return $nav;
    }
}
