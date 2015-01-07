<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
class category {
    public $category   = array();
    public $_array     = array();
    public $rootid     = array();

    public function __construct($appid=iCMS_APP_ARTICLE) {
        $this->appid = $appid;
        $sql         = "WHERE `appid`='$this->appid'";
        $this->appid === 'all' && $sql='';
        $rs          = iDB::all("SELECT * FROM `#iCMS@__category` {$sql} ORDER BY `ordernum` , `cid` ASC",ARRAY_A);
        foreach((array)$rs AS $C) {
			$C['iurl']	= iURL::get('category',$C);
            $this->_array[$C['rootid']][$C['cid']] = $C;
            $this->rootid[$C['rootid']][$C['cid']] = $C['cid'];
            $this->category[$C['cid']] = $C;
            $this->parent[$C['cid']]   = $C['rootid'];
        }
        foreach ((array)$this->_array as $rootid => $_array) {
            uasort($_array, "order_num");
            $this->_array[$rootid] = $_array;
        }
    }
    public function cache($one=false,$appid=null) {
    	$rs	= iDB::all("SELECT * FROM `#iCMS@__category` ORDER BY `ordernum` , `cid` ASC",ARRAY_A);
    	foreach((array)$rs AS $C) {
	        $C = $this->C($C);
			$one && $this->cahce_one($C);

            $appidArray[$C['appid']] = $C['appid'];
            $parent[$C['cid']]       = $C['rootid'];
            $dir2cid[$C['dir']]      = $C['cid'];
            $rootid[$C['rootid']][$C['cid']] = $C['cid'];
            $C['status'] OR $hidden[]        = $C['cid'];
            $cache[$C['appid']][$C['cid']]   = $C;
            $array[$C['appid']][$C['rootid']][$C['cid']] = $C;
    	}

    	if($appid===null){
	    	foreach($appidArray AS $_appid) {
		        iCache::set('iCMS/category.'.$_appid.'/cache',$cache[$_appid],0);
		        iCache::set('iCMS/category.'.$_appid.'/array',$array[$_appid],0);
	    	}
    	}else{
	        iCache::set('iCMS/category.'.$appid.'/cache',$cache[$appid],0);
	        iCache::set('iCMS/category.'.$appid.'/array',$array[$appid],0);
    	}
        iCache::set('iCMS/category/rootid',	$rootid,0);
        iCache::set('iCMS/category/parent',	$parent,0);
        iCache::set('iCMS/category/dir2cid',$dir2cid,0);
        iCache::set('iCMS/category/hidden',	$hidden,0);
    }
    public function cahce_one($C=null){
    	if(!is_array($C)){
    		$C = iDB::row("SELECT * FROM `#iCMS@__category` where `cid`='$C' LIMIT 1;",ARRAY_A);
			$C = $this->C($C);
    	}
		iCache::delete('iCMS/category/'.$C['cid']);
		iCache::set('iCMS/category/'.$C['cid'],$C,0);
    }
    public function C($C){
	    if($C['metadata']){
	    	$mdArray	= array();
	    	$_metadata	= unserialize($C['metadata']);
	    	foreach($_metadata as $key => $value){
	    		$mdArray[$key] = $value;
	    	}
	    	$C['metadata']=$mdArray;
	    }
		return $C;
    }
    public function rootid($cid="0"){
    	$rootid = $this->parent[$cid];
    	return $rootid?$this->rootid($rootid):$cid;
    }
    public function update_count_one($cid,$math='+'){
        $math=='-' && $sql = " AND `count`>0";
        iDB::query("UPDATE `#iCMS@__category` SET `count` = count".$math."1 WHERE `cid` ='$cid' {$sql}");
    }

}
