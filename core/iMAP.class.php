<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
class map {
	public static $table = 'prop';
	public static $field = 'node';
	public static $appid = '1';

	public static function init($table = 'prop',$appid='1',$field = 'node'){
		self::$table = $table;
		self::$field = $field;
		self::$appid = $appid;
		return self;
	}
	public static function table(){
		return'#iCMS@__'.self::$table.'_map';
	}
	public static function del($nodes,$iid="0") {
		$_array   = explode(',',$nodes);
		$_count   = count($_array);
		$varArray = array();
	    for($i=0;$i<$_count;$i++) {
	    	$_node = $_array[$i];
			iDB::query("DELETE FROM `".self::table()."` WHERE `".self::$field."`='$_node' AND `iid`='$iid' AND `appid`='".self::$appid."'");
	    }
	}
	public static function add($nodes,$iid="0") {
		$_array   = explode(',',$nodes);
		$_count   = count($_array);
		$varArray = array();
	    for($i=0;$i<$_count;$i++) {
	        $varArray[$i] = self::addnew($_array[$i],$iid);
	    }
	    return json_encode($varArray);
	}
	public static function addnew($node,$iid="0") {
		$has = iDB::value("SELECT `id` FROM `".self::table()."` WHERE `".self::$field."`='$node' AND `iid`='$iid' AND `appid`='".self::$appid."' LIMIT 1");
	    if(!$has) {
	        iDB::query("INSERT INTO `".self::table()."` (`".self::$field."`,`iid`, `appid`) VALUES ('$node','$iid','".self::$appid."')");
	    }
	    //return array($vars,$tid,$cid,$tcid);
	}
	public static function diff($Nnodes,$Onodes,$iid="0") {
		$N         = explode(',', $Nnodes);
		$O         = explode(',', $Onodes);
		$diff      = array_diff_values($N,$O);
		$varsArray = array();
	    foreach((array)$N AS $i=>$_node) {//新增
            $varsArray[$i] = self::addnew($_node,$iid);
		}
	    foreach((array)$diff['-'] AS $_node) {//减少
	        iDB::query("DELETE FROM `".self::table()."` WHERE `".self::$field."`='$_node' AND `iid`='$iid' AND `appid`='".self::$appid."'");
	   }
	   return json_encode($varsArray);
	}
	public static function ids($nodes=0){
		if(empty($nodes)) return false;

		$sql = self::sql($nodes);
		$all = iDB::all($ids.'Limit 10000');
		return iCMS::get_ids($all,'iid');
	}
	public static function where($nodes=0){
		if(empty($nodes)) return false;

		if(!is_array($nodes) && strstr($nodes, ',')){
			$nodes = explode(',', $nodes);
		}
		$table     = self::table();
		$where_sql = iPHP::where(self::$appid,'appid',false,true,$table);
		$where_sql.= iPHP::where($nodes,self::$field,false,false,$table);
		return array($table=>$where_sql);
	}

	public static function sql($nodes=0){
		if(empty($nodes)) return false;

		if(!is_array($nodes) && strstr($nodes, ',')){
			$nodes = explode(',', $nodes);
		}
		$where_sql = iPHP::where(self::$appid,'appid',false,true);
		$where_sql.= iPHP::where($nodes,self::$field);
		return "SELECT `iid` FROM ".self::table()." WHERE {$where_sql}";
	}

	public static function exists($nodes=0,$iid=''){
		if(empty($nodes)) return false;

		$sql = self::sql($nodes)." AND iid =".$iid;
		return ' AND exists ('.$sql.')';
	}
	public static function multi($nodes=0,$iid=''){

	}
}
