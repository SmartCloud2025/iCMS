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
	public $menu_array = array();
	public $root_array = array();
	public $menu_uri   = array();
	public $rootid     = 0;
	public $parentid   = 0;
	public $do_mid     = 0;
	public $power      = array();
	private $app_uri   = '';
	private $do_uri    = '';

	function __construct() {
		$this->menu_array  = iCache::get('iCMS/iMenu/menu_array');
		$this->root_array  = iCache::get('iCMS/iMenu/root_array');
		$this->child_array = iCache::get('iCMS/iMenu/child_array');
		$this->parent      = iCache::get('iCMS/iMenu/parent');
		$this->menu_uri    = iCache::get('iCMS/iMenu/menu_uri');

		$app           = $_GET['app']?$_GET['app']:'home';
		$this->app_uri = $this->menu_uri[$app];
		$_GET['do'] && $this->do_uri = $app.'&do='.$_GET['do'];
		$_GET['tab']&& $this->do_uri = $app.'&tab='.$_GET['tab'];
		$this->do_mid = $this->app_uri[$this->do_uri];
		$this->do_mid OR $this->do_mid = $this->app_uri[$app];
		$this->do_mid OR $this->do_mid = $this->app_uri['#'];

		$this->rootid   = $this->rootid($this->do_mid);
		$this->parentid = $this->parent[$this->do_mid];
		$this->menu_array OR $this->cache();
	}

	function get_array($cache=false){
		$rs	= iDB::all("SELECT * FROM `#iCMS@__menu` ORDER BY `ordernum` , `id` ASC",ARRAY_A);
		foreach((array)$rs AS $M) {
			$this->menu_array[$M['id']]               = $M;
			$this->root_array[$M['rootid']][$M['id']] = $M;
			$this->parent[$M['id']]                   = $M['rootid'];
	        $M['app']!='separator' && $this->child_array[$M['rootid']][$M['id']] = $M['id'];
			$this->menu_uri[$M['app']][$M['href']] = $M['id'];
			$this->menu_uri[$M['app']]['#']        = $M['rootid'];
		}
		foreach ((array)$this->root_array as $rid => $array) {
			uasort($array, "order_num");
			$this->root_array[$rid] = $array;
		}
		if($cache){
			iCache::set('iCMS/iMenu/menu_array',	$this->menu_array,0);
	        iCache::set('iCMS/iMenu/root_array',	$this->root_array,0);
	        iCache::set('iCMS/iMenu/child_array',	$this->child_array,0);
	        iCache::set('iCMS/iMenu/parent',		$this->parent,0);
	        iCache::set('iCMS/iMenu/menu_uri',		$this->menu_uri,0);
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
			$a	= $this->menu_array[$this->rootid];
			echo $a['name'];
		}
	}
	function breadcrumb(){
		$this->a($this->rootid);
		if($this->parentid!=$this->rootid){
			$this->a($this->parentid);
		}
		if($this->do_mid!=$this->parentid  && $this->do_mid!=$this->rootid){
			$this->a($this->do_mid);
		}
	}
	function a($id){
		$a	= $this->menu_array[$id];
		if(empty($a)) return;

		$a['href'] &&	$href	= __ADMINCP__.'='.$a['href'];
		if(strstr($a['href'], 'http://')||strstr($a['href'], '#')) $href = $a['href'];
		$a['href']=='__SELF__' && $href = __SELF__;
		$a['icon'] && $icon='<i class="'.$a['icon'].'"></i> ';
		$link = '<a href="'.$href.'"';
		$a['title']  && $link.= ' title="'.$a['title'].'"';
		$a['a_class']&& $link.= ' class="'.$a['a_class'].'"';
		$link.='>';
		echo $link.$icon.' '.$a['name'].'</a>';
	}
	function sidebar(){
		return $this->show('sidebar',$this->rootid,1);
	}
	function show($mType='nav',$id="0",$level = 0){
		$nav	= '';
		foreach((array)$this->root_array[$id] AS $rootid=>$M) {
			$nav.= $this->li($mType,$M['id'],$level);
		}
		return $nav;
	}
	function subcount($id){
		$_count	= count($this->child_array[$id]);
		foreach((array)$this->child_array[$id] AS $rootid=>$_id) {
			if($this->root_array[$_id]){
				$_count+=$this->subcount($_id);
			}
		}
		return $_count;
	}
	function li($mType,$id,$level = 1){
		if(!iACP::MP($id)) return false;

		$a		= $this->menu_array[$id];
		if($a['app']=="separator"){
			return '<li class="'.$a['class'].'"></li>';
		}


		$a['href'] && $href	= __ADMINCP__.'='.$a['href'];
		$a['target']=='iPHP_FRAME' && $href.='&frame=iPHP';

		if(strstr($a['href'], 'http://')||strstr($a['href'], '#')) $href = $a['href'];

		$a['href']=='__SELF__' && $href = __SELF__;
		$a['href']=='#' && $href = 'javascript:;';

		$isSM	= count($this->root_array[$id]);

		if($isSM && $level && $mType=='nav'){
			$a['class']	= 'dropdown-submenu';
		}
		if($mType=='sidebar' && $isSM && $level==1){
			$href		= 'javascript:;';
			$a['class']	= 'submenu';
			$label		= '<span class="label">'.$this->subcount($id).'</span>';
		}

		$li = '<li class="'.$a['class'].'" title="'.$a['name'].'" data-level="'.$level.'" data-menu="m'.$id.'">';

		$link = '<a href="'.$href.'"';
		$a['title']  && $link.= ' title="'.$a['title'].'"';
		$a['a_class']&& $link.= ' class="'.$a['a_class'].'"';
		$a['target'] && $link.= ' target="'.$a['target'].'"';

		if($mType=='sidebar' && $a['data-toggle']=='modal'){
			$link.= ' data-toggle="'.$a['data-toggle'].'"';
		}elseif($mType=='nav'){
			$a['data-toggle'] 	&& $link.= ' data-toggle="'.$a['data-toggle'].'"';
		}
		$a['data-target']&& $link.= ' data-target="'.$a['data-target'].'"';
		$a['data-meta']  && $link.= " data-meta='".$a['data-meta']."'";
		$link.=">";
		$li.=$link;
		$a['icon'] && $li.='<i class="'.$a['icon'].'"></i> ';
		$li.='<span>'.$a['name'].'</span>'.$label;
		$a['caret'] && $li.=$a['caret'];
		$li.='</a>';
		if($isSM){
			$SMli	= '';
			foreach((array)$this->root_array[$id] AS $rootid=>$M) {
				$SMli.= $this->li($mType,$M['id'],$level+1);
			}
			$mType =='nav' && $SMul='<ul class="dropdown-menu">'.$SMli.'</ul>';
			if($mType=='sidebar'){
				$SMul = $level>1?$SMli:'<ul style="display: none;">'.$SMli.'</ul>';
			}
		}
		$li.=$SMul.'</li>';
		return $li;
	}

    function check_power($p){
    	return is_array($p)?array_intersect((string)$p,$this->power):in_array((string)$p,$this->power);
    }
}
