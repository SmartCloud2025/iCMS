<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: iMenu.class.php 2334 2014-01-04 12:18:19Z coolmoo $
*/
class iMenu {
	public $menuArray = array();
	public $MArray    = array();
	//public $doMid     = 0;
	public $rootid    = 0;
	//public $appMid    = 0;
	public $MUri      = array();
	private $rootA    = array();

	function __construct() {
		$this->get_array(true);
		$this->menuArray = iCache::get('iCMS/iMenu/menuArray');
		$this->MArray    = iCache::get('iCMS/iMenu/MArray');
		$this->rootA     = iCache::get('iCMS/iMenu/rootA');
		$this->subA      = iCache::get('iCMS/iMenu/subA');
		$this->parent    = iCache::get('iCMS/iMenu/parent');
		$this->MUri      = iCache::get('iCMS/iMenu/MUri');

		$app          = $_GET['app']?$_GET['app']:'home';
		$this->appURI = $this->MUri[$app];
		$_GET['do'] && $this->doURI = $app.'&do='.$_GET['do'];
		$_GET['tab']&& $this->doURI = $app.'&tab='.$_GET['tab'];
		$this->doMid = $this->appURI[$this->doURI];
		$this->doMid OR $this->doMid = $this->appURI[$app];
		$this->doMid OR $this->doMid = $this->appURI['#'];

		$this->rootid   = $this->rootid($this->doMid);
		$this->parentid = $this->parent[$this->doMid];
		//var_dump($this->doMid,$this->parentid,$this->rootid);
		//exit;
		//$this->parentid==$this->rootid && $this->parentid=$this->doMid;
		$this->menuArray OR $this->cache();
	}

	function get_array($cache=false){
		$rs	= iDB::all("SELECT * FROM `#iCMS@__menu` ORDER BY `orderNum` , `id` ASC",ARRAY_A);
		foreach((array)$rs AS $M) {
			$menuArray[$M['id']]            = $M;
			$MArray[$M['rootid']][$M['id']] = $M;
			$rootA[$M['rootid']][$M['id']]  = $M['id'];
			$parent[$M['id']]               = $M['rootid'];
	        $M['app']!='separator' && $subA[$M['rootid']][$M['id']]	= $M['id'];
			$MUri[$M['app']][$M['href']] = $M['id'];
			$MUri[$M['app']]['#']        = $M['rootid'];
		}
		if($cache){
			iCache::set('iCMS/iMenu/menuArray',	$menuArray,0);
	        iCache::set('iCMS/iMenu/MArray',	$MArray,0);
	        iCache::set('iCMS/iMenu/rootA',	$rootA,0);
	        iCache::set('iCMS/iMenu/subA',	$subA,0);
	        iCache::set('iCMS/iMenu/parent',	$parent,0);
	        iCache::set('iCMS/iMenu/MUri',	$MUri,0);
		}
	}
	function cache(){
		$this->get_array(true);
	}
	function rootid($id){
		$rootid = $this->parent[$id];
		if(!$rootid){
			return $id;
		}
		return $this->rootid($rootid);
	}
	function h1(){
		if($this->rootid){
			$a	= $this->menuArray[$this->rootid];
			echo $a['name'];
		}
	}
	function breadcrumb(){
		echo $this->a($this->rootid);
		if($this->rootid!=$this->parentid){
			echo $this->a($this->parentid);
		}
		echo $this->a($this->doMid);
	}
	function a($id){
		$a	= $this->menuArray[$id];
		$a['href'] &&	$href	= __ADMINCP__.'='.$a['href'];
		if(strstr($a['href'], 'http://')||strstr($a['href'], '#')) $href = $a['href'];
		$a['href']=='__SELF__' && $href = __SELF__;
		$a['icon'] && $icon='<i class="'.$a['icon'].'"></i> ';

		echo '<a href="'.$href.'">'.$icon.' '.$a['name'].'</a>';
	}
	function sidebar(){
		return $this->show('sidebar',$this->rootid,1);
	}
	function show($mType='nav',$id="0",$level = 0){
		$nav	= '';
		foreach((array)$this->MArray[$id] AS $rootid=>$M) {
			$nav.= $this->li($mType,$M['id'],$level);
		}
		return $nav;
	}
	function subcount($id){
		$_count	= count($this->subA[$id]);
		foreach((array)$this->subA[$id] AS $rootid=>$_id) {
			if($this->rootA[$_id]){
				$_count+=$this->subcount($_id);
			}
		}
		return $_count;
	}
	function li($mType,$id,$level = 1){
		$a		= $this->menuArray[$id];
		if($a['app']=="separator"){
			return '<li class="'.$a['class'].'"></li>';
		}

		$a['href'] && $href	= __ADMINCP__.'='.$a['href'];
		$a['target']=='iPHP_FRAME' && $href.='&frame=iPHP';

		if(strstr($a['href'], 'http://')||strstr($a['href'], '#')) $href = $a['href'];

		$a['href']=='__SELF__' && $href = __SELF__;
		$a['href']=='#' && $href = 'javascript:;';

		$isSM	= count($this->rootA[$id]);

		if($isSM && $level && $mType=='nav'){
			$a['class']	= 'dropdown-submenu';
		}
		if($mType=='sidebar' && $isSM && $level==1){
			$href		= 'javascript:;';
			$a['class']	= 'submenu';
			$label		= '<span class="label">'.$this->subcount($id).'</span>';
		}

		$li = '<li class="'.$a['class'].'" title="'.$a['name'].'" data-level="'.$level.'" data-menu="m'.$id.'">';

		$aa = '<a href="'.$href.'"';
		$a['title'] 		&& $aa.= ' title="'.$a['title'].'"';
		$a['a_class'] 		&& $aa.= ' class="'.$a['a_class'].'"';
		$a['target'] 		&& $aa.= ' target="'.$a['target'].'"';

		if($mType=='sidebar' && $a['data-toggle']=='modal'){
			$aa.= ' data-toggle="'.$a['data-toggle'].'"';
		}elseif($mType=='nav'){
			$a['data-toggle'] 	&& $aa.= ' data-toggle="'.$a['data-toggle'].'"';
		}
		$a['data-target'] 	&& $aa.= ' data-target="'.$a['data-target'].'"';
		$a['data-meta'] 	&& $aa.= " data-meta='".$a['data-meta']."'";
		$aa.=">";
		$li.=$aa;
		$a['icon'] && $li.='<i class="'.$a['icon'].'"></i> ';
		$li.='<span>'.$a['name'].'</span>'.$label;
		$a['caret'] && $li.=$a['caret'];
		$li.='</a>';
		if($isSM){
			$SMli	= '';
			foreach((array)$this->MArray[$id] AS $rootid=>$M) {
				$SMli.= $this->li($mType,$M['id'],$level+1);
			}
			$mType=='nav'		&& $SMul='<ul class="dropdown-menu">'.$SMli.'</ul>';
			if($mType=='sidebar'){
				$SMul = $level>1?$SMli:'<ul style="display: none;">'.$SMli.'</ul>';
			}
		}
		$li.=$SMul.'</li>';
		return $li;
	}
	function permission(){

	}
}