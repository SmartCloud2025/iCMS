<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
class category {
    public $category = array();
    public $_array   = array();
    private $rootid  = array();

    function __construct($appid=1) {
       $this->appid = $appid;
       $sql         = "WHERE `appid`='$this->appid'";
       $this->appid === 'all' && $sql='';
       $rs          = iDB::getArray("SELECT * FROM `#iCMS@__category` {$sql} ORDER BY `orderNum` , `cid` ASC",ARRAY_A);
//echo iDB::$last_query;
//iDB::$last_query='explain '.iDB::$last_query;
//$explain=iDB::getRow(iDB::$last_query);
//print_r($explain);
//exit;
        foreach((array)$rs AS $C) {
			$C['iurl']	= iURL::get('category',$C);
            $this->category[$C['cid']] =
            $this->_array[$C['rootid']][$C['cid']] = $C;
            $this->rootid[$C['rootid']][$C['cid']] = $C['cid'];
            $this->parent[$C['cid']]=$C['rootid'];
        }
    }
    function cache($one=false,$appid=null) {
    	$rs	= iDB::getArray("SELECT * FROM `#iCMS@__category` ORDER BY `orderNum` , `cid` ASC",ARRAY_A);
    	foreach((array)$rs AS $C) {
	        $C = $this->C($C);
			$one && $this->cacheOne($C);

    		$rootid[$C['rootid']][$C['cid']] = $C['cid'];
    		$parent[$C['cid']]	= $C['rootid'];
    		$dir2cid[$C['dir']]	= $C['cid'];
    		$C['status'] OR $hidden[]	=$C['cid'];
    		$appidArray[$C['appid']]=$C['appid'];
    		$cache[$C['appid']][$C['cid']]=$C;
    		$array[$C['appid']][$C['rootid']][$C['cid']]=$C;
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
        iCache::set('iCMS/category/dir2cid',	$dir2cid,0);
        iCache::set('iCMS/category/hidden',	$hidden,0);
    }
    function cacheOne($C=null){
    	if(!is_array($C)){
    		$C = iDB::getRow("SELECT * FROM `#iCMS@__category` where `cid`='$C' LIMIT 1;",ARRAY_A);
			$C = $this->C($C);
    	}
		iCache::delete('iCMS/category/'.$C['cid']);
		iCache::set('iCMS/category/'.$C['cid'],$C,0);
    }
    function C($C){
	    if($C['metadata']){
	    	$mdArray	= array();
	    	$_metadata	= unserialize($C['metadata']);
	    	foreach($_metadata AS $mdval){
	    		$mdArray[$mdval['key']]=$mdval['value'];
	    	}
	    	$C['metadata']=$mdArray;
	    }
		$C['iurl']	= iURL::get('category',$C);
		return $C;
    }
    function rootid($cid="0"){
    	$rootid = $this->parent[$cid];
    	return $rootid?$this->rootid($rootid):$cid;
    }
    function cid($cid = "0",$pid=NULL) {
        foreach((array)$this->_array[$cid] AS $root=>$C) {
            if(iMember::CP($C['cid']) && empty($C['url'])) {
                if($pid===NULL) {
                    $ID.=$C['cid'].",";
                }else {
                    $pid==$C['pid'] && $ID.=$C['cid'].",";
                }
            }
            $this->rootid[$C['cid']] && $ID.=$this->cid($C['cid'],$pid);
        }
//    	var_dump(array_intersect($p,iMember::cpower));
        return $ID;
    }
    function select($currentid="0",$cid="0",$level = 1,$pid=NULL,$url=NULL) {
        foreach((array)$this->_array[$cid] AS $root=>$C) {

        	if(!$C['status']) continue;

            if(iMember::CP($C['cid'])) {
                $t=$level=='1'?"":"├ ";
                $selected=($currentid==$C['cid'])?"selected='selected'":"";
                $text=str_repeat("│　", $level-1).$t.$C['name']."[cid:{$C['cid']}][pid:{$C['pid']}]".($C['url']?"[∞]":"");
                if(empty($C['url'])){
                    if($pid===NULL||$pid=='all'){
						$option.="<option value='{$C['cid']}' $selected>{$text}</option>";
                    }else{
                    	if($C['appid']==0){
                        	$pid==$C['pid'] && $option.="<option value='{$C['cid']}' $selected>{$text}</option>";
                        }else{
                        	$option.="<option value='{$C['cid']}' $selected>{$text}</option>";
                        }
                    }
                }else{
                    if($url){
                        $option.="<option value='{$C['cid']}' $selected>{$text}</option>";
                    }else {
                        $option.="<optgroup label=\"{$text}\"></optgroup>";
                    }
                }
            }
            $this->rootid[$C['cid']] && $option.=$this->select($currentid,$C['cid'],$level+1,$pid,$mid,$url);
        }
        return $option;
    }
    function user_select($currentid="0",$cid="0",$level = 1,$pid=NULL,$url=NULL) {
        foreach((array)$this->_array[$cid] AS $root=>$C) {

        	if(!$C['status']) continue;

        	if($C['isucshow']){
                $t=$level=='1'?"":"├ ";
                $selected=($currentid==$C['cid'])?"selected='selected'":"";
                $text=str_repeat("│　", $level-1).$t.$C['name']."[cid:{$C['cid']}]";
                if($C['issend']){
                    if(empty($C['url'])){
                        if($pid===NULL||$pid=='all'){
							$option.="<option value='{$C['cid']}' $selected>{$text}</option>";
                        }else {
                            $pid==$C['pid'] && $option.="<option value='{$C['cid']}' $selected>{$text}</option>";
                        }
                    }else{
                        if($url){
                            $option.="<option value='{$C['cid']}' $selected>{$text}</option>";
                        }else {
                            $option.="<optgroup label=\"{$text}\"></optgroup>";
                        }
                    }
                }else{
                	$option.="<optgroup label=\"{$text}\"></optgroup>";
                }
            }
            $this->rootid[$C['cid']] && $option.=$this->user_select($currentid,$C['cid'],$level+1,$pid,$mid);
        }
        return $option;
    }
}