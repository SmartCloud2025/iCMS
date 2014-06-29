<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
defined('TAG_APPID') OR define('TAG_APPID',0);

class tag {
	function data($fv=0,$field='name',$limit=0){
		$sql      = $fv ? "where `$field`='$fv'":'';
		$limitSQL = $limit ? "LIMIT $limit ":'';
	    return iDB::getArray("SELECT * FROM `#iCMS@__tags` {$sql} order by id DESC {$limitSQL}");
	}
	function cache($value=0,$field='id'){
		$rs     = self::data($value,$field);
		$_count = count($rs);
	    for($i=0;$i<$_count;$i++) {
			$C              = iCache::get('iCMS/category/'.$rs[$i]['cid']);
			$TC             = iCache::get('iCMS/category/'.$rs[$i]['tcid']);
			$rs[$i]['iurl'] = iURL::get('tag',array($rs[$i],$C,$TC));
			$rs[$i]['url']  = $rs[$i]['iurl']->href;
			$tkey           = self::tkey($rs[$i]['cid']);
	        iCache::set($tkey,$rs[$i],0);
	    }
	}
    function tkey($cid){
		$ncid = abs(intval($cid));
		$ncid = sprintf("%08d", $ncid);
		$dir1 = substr($ncid, 0, 2);
		$dir2 = substr($ncid, 2, 3);
		$tkey = $dir1.'/'.$dir2.'/'.$cid;
        return 'iCMS/tags/'.$tkey;
    }
    function getag($key='tags',&$array,$C,$TC){
    	if(empty($array[$key])) return;

		$strLink	= '';
        $strArray	= explode(',',$array[$key]);

        foreach($strArray AS $k=>$name){
        	$name				= trim($name);
        	$_cache				= self::getCache($name,$C['cid'],$TC['cid']);
			$strA[$k]['name']	= $name;
			$strA[$k]['url']	= $_cache['url']?$_cache['url']:iCMS::$config['router']['publicURL'].'/search.php?q='.$name;
			$strLink.='<a href="'.$strA[$k]['url'].'" target="_self">'.$strA[$k]['name'].'</a> ';
        }
        $search	= $C['name'];
        
        $sstrA	= $strArray;
        count($strArray)>3 && $sstrA = array_slice($strArray,0,3);
        $sstr	= implode(',',$sstrA);
        $sstr && $search = $sstr;
        
        $array[$key.'link']		= $strLink;
        $array[$key.'_array']	= $strA;
        $array['search'][$key]	= $search;
        
        return array(
        	$key.'link'		=> $strLink,
        	$key.'_array'	=> $strA,
        	'search'		=> array($key=>$search)
        );
    }

//	function geturl($name,$cid){
//		$category	= iCache::get('iCMS/category/'.$cid);
//		$rs			= self::data($value,'name');
//		return iURL::get('tag',array($rs[$i],$C));
//	}
	function getCache($tid){
		$tkey	= self::tkey($tid);
		return iCache::get($tkey);
	}

    function delCache($tid) {
		$ids = implode(',',(array)$tid);
		iDB::query("DELETE FROM `#iCMS@__tags` WHERE `id` in ($ids) ");
		$c   = count($tid);
        for($i=0;$i<$c;$i++) {
			$tkey = self::tkey($tid[$i]);
			iCache::delete($tkey);
        }
    }

	public function add($tags,$uid="0",$iid="0",$cid='0',$tcid='0') {
		$a        = explode(',',$tags);
		$c        = count($a);
		$tagArray = array();
	    for($i=0;$i<$c;$i++) {
	        $tagArray[$i] = self::update($a[$i],$uid,$iid,$cid,$tcid);
	    }
	    return json_encode($tagArray);
	}
	public function update($tag,$uid="0",$iid="0",$cid='0',$tcid='0') {
	    if(empty($tag)) return;
	    
	    $tid	= iDB::getValue("SELECT `id` FROM `#iCMS@__tags` WHERE `name`='$tag'");
	    if($tid) {
	        $tlid = iDB::getValue("SELECT `id` FROM `#iCMS@__tag_map` WHERE `iid`='$iid' and `tid`='$tid' and `appid`='".TAG_APPID."'");
	        if(empty($tlid)) {
	            iDB::query("INSERT INTO `#iCMS@__tag_map` (`iid`, `tid`, `appid`) VALUES ('$iid', '$tid', '".TAG_APPID."')");
	            iDB::query("UPDATE `#iCMS@__tags` SET  `count`=count+1,`pubdate`='".time()."'  WHERE `id`='$tid'");
	        }
	    }else {
	        $tkey	= iPHP::pinyin($tag,iCMS::$config['other']['CLsplit']);
	        iDB::query("INSERT INTO `#iCMS@__tags`
            (`uid`, `cid`, `tcid`, `pid`, `tkey`, `name`, `seotitle`, `subtitle`, `keywords`, `description`, `ispic`, `pic`, `url`, `related`, `count`, `weight`, `tpl`, `ordernum`, `pubdate`, `status`)
VALUES ('$uid', '$cid', '$tcid', '0', '$tkey', '$tag', '', '', '', '', '', '', '', '', '1', '0', '', '0', '".time()."', '1');");
	        $tid = iDB::$insert_id;
	        self::cache($tag);
	        iDB::query("INSERT INTO `#iCMS@__tag_map` (`iid`, `tid`, `appid`) VALUES ('$iid', '$tid', '".TAG_APPID."')");
	    }
	    return array($tag,$tid,$cid,$tcid);
	}
	function diff($Ntags,$Otags,$uid="0",$iid="0",$cid='0',$tcid='0') {
		$N        = explode(',', $Ntags);
		$O        = explode(',', $Otags);
		$diff     = array_diff_values($N,$O);
		$tagArray = array();
	    foreach((array)$N AS $i=>$tag) {//新增
            $tagArray[$i] = self::update($tag,$uid,$iid,$cid,$tcid);
		}
	    foreach((array)$diff['-'] AS $tag) {//减少
	        $tA	= iDB::getRow("SELECT `id`,`count` FROM `#iCMS@__tags` WHERE `name`='$tag' LIMIT 1;");
	        if($tA->count<=1) {
	        	//$iid && $sql="AND `iid`='$iid'";
	            iDB::query("DELETE FROM `#iCMS@__tags`  WHERE `name`='$tag'");
	            iDB::query("DELETE FROM `#iCMS@__tag_map` WHERE `tid`='$tA->id'");
	        }else {
	            iDB::query("UPDATE `#iCMS@__tags` SET  `count`=count-1,`pubdate`='".time()."'  WHERE `name`='$tag' and `count`>0");
	            iDB::query("DELETE FROM `#iCMS@__tag_map` WHERE `iid`='$iid' and `tid`='$tA->id' AND `appid`='".TAG_APPID."'");
	        }
	   }
	   return json_encode($tagArray);
	}
	function del($tags,$field='name',$iid=0){
	    $tagArray	= explode(",",$tags);
	    $iid && $sql="AND `iid`='$iid'";
	    foreach($tagArray AS $k=>$v) {
	    	$tagA	= iDB::getRow("SELECT * FROM `#iCMS@__tags` WHERE `$field`='$v' LIMIT 1;");
	    	$tRS	= iDB::getArray("SELECT `iid` FROM `#iCMS@__tag_map` WHERE `tid`='$tagA->id' AND `appid`='".TAG_APPID."' {$sql}");
	    	foreach((array)$tRS AS $TL) {
	    		$idA[]=$TL['iid'];
	    	}
	    	// if($idA){
		    // 	iPHP::appClass("model","break");
		    // 	$table	= model::table(TAG_APPID);
	    	// 	$ids	= implode(',',$idA);
		    // 	iDB::query("UPDATE `#iCMS@__$table` SET `tags`=REPLACE(tags, '$tagA->name,',''),`tags`=REPLACE(tags, ',$tagA->name','') WHERE id IN($ids)");
	    	// }
            iDB::query("DELETE FROM `#iCMS@__tags`  WHERE `$field`='$v'");
            iDB::query("DELETE FROM `#iCMS@__tag_map` WHERE tid='$tagA->id' AND `appid`='".TAG_APPID."' {$sql}");
            $ckey	= self::tkey($tagA->cid);
            iCache::delete($ckey);
	    }
	}
}