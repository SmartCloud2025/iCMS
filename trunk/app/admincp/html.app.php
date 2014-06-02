<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: html.app.php 2404 2013-03-02 07:32:33Z coolmoo $
*/
class htmlApp{
    function __construct() {
		iPHP::$iTPLMode	= "html";
		$this->page	= $GLOBALS['page'];
		$this->PG	= $_POST?$_POST:$_GET;
		$this->CP	= iCMS::$config['router']['speed'];
        $mtime 		= microtime();
        $mtime 		= explode(' ', $mtime);
        $this->time_start = $mtime[1] + $mtime[0];
        $this->alltime = $_GET['alltime']?$_GET['alltime']:0;
    }
    function doindex(){
    	include iACP::view("html.index");
    }
    function docreateIndex(){
    	$indexTPL	= iCMS::$config['site']['indexTPL']	= $this->PG['indexTPL'];
    	$indexName	= iCMS::$config['site']['indexName']= $this->PG['indexName'];
    	$indexName OR $indexName ="index".iCMS::$config['router']['htmlext'];
    	iFS::filterExt($indexName,true) OR iPHP::alert('文件名后缀不合法!');
		iACP::updateConfig('site');
    	$this->CreateIndex($indexTPL,$indexName);
    }
    function CreateIndex($indexTPL,$indexName,$p=1,$loop=1){

		$_GET['loop']	&& $loop=0;
		$GLOBALS['page']	= $p+$this->page;
		$query['indexTPL']	= $indexTPL;
		$query['indexName']	= $indexName;

		$app	= iPHP::app("index");
		$htm	= $app->doiCMS($indexTPL,$indexName);
		$fpath	= iPHP::page_p2num($htm[1]->pagepath);
		$total	= $GLOBALS['iPage']['total'];
		iFS::filterExt($fpath,true) OR iPHP::alert("文件后缀不安全,禁止生成!<hr />请更改系统设置->网站URL->文件后缀");
		iFS::mkdir($htm[1]->dir);
		iFS::write($fpath,$htm[0]);
		$_total	= $total?$total:"1";
		$msg="共<span class='label label-info'>{$_total}</span>页 已生成<span class='label label-info'>".$GLOBALS['page']."</span>页,";
		
//		$surplus		= ceil($total-$p);
		if($loop<$this->CP && $GLOBALS['page']<$total) {
			$loop++;
			$p++;
			$this->CreateIndex($indexTPL,$indexName,$p,$loop);
		}
		$looptimes	= ($total-$GLOBALS['page'])/$this->CP;
		$use_time	= $this->use_time();
		$msg.="用时<span class='label label-info'>{$use_time}</span>秒";
		$query["alltime"] = $this->alltime+$use_time;
		$loopurl	= $this->loopurl($total,$query);
		if($loopurl){
			$moreBtn	= array(
				array("id"=>"btn_stop","text"=>"停止","url"=>APP_URI."&do=index"),
				array("id"=>"btn_next","text"=>"继续","src"=>$loopurl,"next"=>true)
	        );
	        $dtime		= 1;
			$all_time	= $looptimes*$use_time+$looptimes+1;
			$msg.="<hr />预计全部生成还需要<span class='label label-info'>{$all_time}</span>秒";
        }else{
			$moreBtn	= array(
				array("id"=>"btn_next","text"=>"完成","url"=>APP_URI."&do=index")
	        );
	        $dtime		= 5;
	        $msg.="<hr />已全部生成完成<hr />总共用时<span class='label label-info'>".$query["alltime"]."</span>秒";
        }
		$updateMsg	= $this->page?true:false;
		iPHP::dialog($msg,$loopurl?"src:".$loopurl:'',$dtime,$moreBtn,$updateMsg);
    }
    function docategory(){
    	$this->category		= iPHP::appClass("category",iCMS_APP_ARTICLE);
    	include iACP::view("html.category");
    }
    function docreateCategory($cid=0,$p=1,$loop=1){
		$category	= $this->PG['cid'];
		$rootid		= $this->PG['rootid'];
		$k			= (int)$this->PG['k'];
		if($k=="0" && empty($category)){
			iPHP::alert('请选择需要生成静态的栏目!');
		}elseif($category[0]=='all'){
			$rs	= iCache::get('iCMS/category.'.iCMS_APP_ARTICLE.'/cache');
			$category	= array();
			foreach((array)$rs AS $_cid=>$C){
				$C['status'] && $category[]=$C['cid'];
			}
		}

		if($k){
			$category	= iCache::get('iCMS/create.category');
		}else{
			iCache::set('iCMS/create.category',$category,0);
		}
    	
    	$_GET['loop']	&&	$loop=0;
		$GLOBALS['page']	= $p+$this->page;
		
		$len	= count($category)-1;
		$cid	= $category[$k];

		$app	= iPHP::app("category");
		$htm	= $app->category($cid);

		$fpath	= iPHP::page_p2num($htm[1]['iurl']['pagepath']);
		$total	= $GLOBALS['iPage']['total'];
		iFS::filterExt($fpath,true) OR iPHP::alert("文件后缀不安全,禁止生成!<hr />请更改栏目->URL规则设置->栏目规则");
		iFS::mkdir($htm[1]['iurl']['dir']);
		iFS::write($fpath,$htm[0]);
		$_total	= $total?$total:"1";
		$name	= $htm[1]['name'];
		$msg	= "<span class='label label-success'>{$name}</span>栏目,共<span class='label label-info'>{$_total}</span>页 已生成<span class='label label-info'>".$GLOBALS['page']."</span>页,";
//		$surplus		= ceil($total-$p);
		if($loop<$this->CP && $GLOBALS['page']<$total) {
			$loop++;
			$p++;
			$this->docreateCategory($cid,$p,$loop);
		}
		$looptimes	= ($total-$GLOBALS['page'])/$this->CP;
		$use_time	= $this->use_time();
		$msg.="用时<span class='label label-info'>{$use_time}</span>秒";
		$query["alltime"] = $this->alltime+$use_time;
		$loopurl	= $this->loopurl($total,$query);
		//	print_r($loopurl);
//		exit;
		if($loopurl){
			$moreBtn	= array(
				array("id"=>"btn_stop","text"=>"停止","url"=>APP_URI."&do=category"),
				array("id"=>"btn_next","text"=>"继续","src"=>$loopurl,"next"=>true)
	        );
	        $dtime		= 1;
			$all_time	= $looptimes*$use_time+$looptimes+1;
			$msg.="<hr /><span class='label label-success'>{$name}</span>栏目,预计全部生成还需要<span class='label label-info'>{$all_time}</span>秒";
        }else{
			$moreBtn	= array(
				array("id"=>"btn_next","text"=>"完成","url"=>APP_URI."&do=category")
	        );
	        $dtime		= 3;
	        $msg.="<hr /><span class='label label-success'>{$name}</span>栏目,已全部生成完成.总共用时<span class='label label-info'>".$query["alltime"]."</span>秒";
        	if($k<$len){
				$query["k"]		= $k+1;
				$query["alltime"]	= 0;
				$GLOBALS['page']	= 0;
				
				$loopurl	= $this->loopurl(1,$query);
		        $msg.="<hr />准备开始生成下一个栏目";
				$moreBtn	= array(
					array("id"=>"btn_stop","text"=>"停止","url"=>APP_URI."&do=category"),
					array("id"=>"btn_next","text"=>"继续","src"=>$loopurl,"next"=>true)
		        );
				$dtime		= 1;
        	}elseif($k==$len){
        		$msg.="<hr />所有栏目生成完成";
        	}
			$k>0 && $updateMsg	= true;
        }
        if($k==0){
			$updateMsg	= $this->page?true:false;
		}
		iPHP::dialog($msg,$loopurl?"src:".$loopurl:"",$dtime,$moreBtn,$updateMsg);

    }
    function doarticle(){
    	$this->category		= iPHP::appClass("category",iCMS_APP_ARTICLE);
    	include iACP::view("html.article");
    }
    function docreateArticle($aid=null){
		$category	= $this->PG['cid'];
		$startime	= $this->PG['startime'];
		$endtime	= $this->PG['endtime'];
		$startid	= $this->PG['startid'];
		$endid		= $this->PG['endid'];
		$perpage	= (int)$this->PG['perpage'];
		$offset		= (int)$this->PG['offset'];
		$orderby	= $this->PG['orderby'];
		$whereSQL	= "WHERE `status` ='1'";
    	$aid===null && $aid=$this->PG['aid'];
		if($aid){
			$title	= self::Article($aid);
			iPHP::OK($title.'<hr />生成静态完成!');
		}
		
		if($category[0]=='all'){
			$rs	= iCache::get('iCMS/category.'.iCMS_APP_ARTICLE.'/cache');
			$category	= array();
			foreach((array)$rs AS $_cid=>$C){
				$C['status'] && $category[]=$C['cid'];
			}
		}
		if($category){
			$cids	= implode(',',$category);
			$whereSQL.= " AND `cid` IN({$cids})";
		}
        $startime 	&& $whereSQL.=" AND `pubdate`>=UNIX_TIMESTAMP('{$startime} 00:00:00')";
        $endtime 	&& $whereSQL.=" AND `pubdate`<=UNIX_TIMESTAMP('{$endtime} 23:59:59')";
        $startid 	&& $whereSQL.=" AND `id`>='{$startid}'";
        $endid		&& $whereSQL.=" AND `id`<='{$endid}'";
        $perpage	OR $perpage	= $this->CP;
        $orderby	OR $orderby	= "id DESC";
        $total		= iPHP::total(false,"SELECT count(*) FROM `#iCMS@__article` {$whereSQL}","G");
        $looptimes	= ceil($total/$perpage);
        $offset		= $this->page*$perpage;
        $rs			= iDB::getArray("SELECT `id` FROM `#iCMS@__article` {$whereSQL} order by {$orderby} LIMIT {$offset},{$perpage}");
//echo iDB::$last_query;
//iDB::$last_query='explain '.iDB::$last_query;
//$explain=iDB::getRow(iDB::$last_query);
//var_dump($explain);
        $_count	= count($rs);
        $msg	= "共<span class='label label-info'>{$total}</span>篇文章,将分成<span class='label label-info'>{$looptimes}</span>次完成<hr />开始执行第<span class='label label-info'>".($this->page+1)."</span>次生成,共<span class='label label-info'>{$_count}</span>篇<hr />";
        for($i=0;$i<$_count;$i++){
			self::Article($rs[$i]['id']);
			$msg.= $rs[$i]['id'].' <i class="fa fa-check"></i> ';
        }
        $GLOBALS['page']++;
		$use_time	= $this->use_time();
		$msg.="<hr />用时<span class='label label-info'>{$use_time}</span>秒";
		$query["totalNum"]	= $total;
		$query["alltime"]	= $this->alltime+$use_time;
		$loopurl	= $this->loopurl($looptimes,$query);
		if($loopurl){
			$moreBtn	= array(
				array("id"=>"btn_stop","text"=>"停止","url"=>APP_URI."&do=article"),
				array("id"=>"btn_next","text"=>"继续","src"=>$loopurl,"next"=>true)
	        );
	        $dtime		= 1;
			$all_time	= $looptimes*$use_time+$looptimes+1;
			$msg.="<hr />预计全部生成还需要<span class='label label-info'>{$all_time}</span>秒";
        }else{
			$moreBtn	= array(
				array("id"=>"btn_next","text"=>"完成","url"=>APP_URI."&do=article")
	        );
	        $dtime		= 5;
	        $msg.="<hr />已全部生成完成<hr />总共用时<span class='label label-info'>".$query["alltime"]."</span>秒";
        }
		$updateMsg	= $this->page?true:false;
		iPHP::dialog($msg,$loopurl?"src:".$loopurl:'',$dtime,$moreBtn,$updateMsg);
    }
    function Article($id){
    	$app	= iPHP::app("article");
		$htm	= $app->article($id);
		$total	= $htm[1]->page['total'];
		
		iFS::filterExt($htm[1]->iurl->path,true) OR iPHP::alert("文件后缀不安全,禁止生成!<hr />请更改栏目->URL规则设置->内容规则");
		iFS::mkdir($htm[1]->iurl->dir);
		iFS::write($htm[1]->iurl->path,$htm[0]);
		
		if($total>2){
			for($ap=2;$ap<=$total;$ap++){
				$htm	= $app->article($id,$ap);
				$fpath	= iPHP::page_p2num($htm[1]->iurl->pagepath,$ap);
				iFS::write($fpath,$htm[0]);
			}
		}
		return $htm[1]->title;
    }
    function loopurl($total,$query2){
    	if ($total>0 && $GLOBALS['page']<$total){
    		//$p++;
    		$url	= $_SERVER["REQUEST_URI"];
		    $urlA	= parse_url($url);
		    parse_str($urlA["query"], $query);
		    $query['page']		= $GLOBALS['page'];
		    $query 				= array_merge($query, (array)$query2);
		    $urlA["query"]		= http_build_query($query);
		    $url	= $urlA["path"].'?'.$urlA["query"];
		    return $url;
			//iPHP::gotourl($url);
    	}
    }
    function use_time(){
		$mtime 		= microtime();
		$mtime 		= explode(' ', $mtime);
		$time_end 	= $mtime[1] + $mtime[0];
		return round($time_end - $this->time_start,3);
    }
}
