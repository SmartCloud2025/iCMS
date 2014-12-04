<?php

/**
 * iCMS - i Content Management System
 * Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
 *
 * @author coolmoo <idreamsoft@qq.com>
 * @site http://www.idreamsoft.com
 * @licence http://www.idreamsoft.com/license.php
 * @version 6.0.0
 * @$Id: spider.app.php 156 2013-03-22 13:40:07Z coolmoo $
 */
//ini_set('memory_limit','512M');
class spiderApp {

    function __construct() {
        $this->cid   = (int) $_GET['cid'];
        $this->rid   = (int) $_GET['rid'];
        $this->pid   = (int) $_GET['pid'];
        $this->sid   = (int) $_GET['sid'];
        $this->poid  = (int) $_GET['poid'];
        $this->title = $_GET['title'];
        $this->url   = $_GET['url'];
        $this->work  = false;
    }
    function do_batch(){
        $idArray = (array)$_POST['id'];
        $idArray OR iPHP::alert("请选择要删除的项目");
        $ids     = implode(',',$idArray);
        $batch   = $_POST['batch'];
    	switch($batch){
    		case 'delurl':
				iDB::query("delete from `#iCMS@__spider_url` where `id` IN($ids);");
    		break;
    		case 'delpost':
				iDB::query("delete from `#iCMS@__spider_post` where `id` IN($ids);");
    		break;
    		case 'delproject':
				iDB::query("delete from `#iCMS@__spider_project` where `id` IN($ids);");
    		break;
    		case 'delrule':
 				iDB::query("delete from `#iCMS@__spider_rule` where `id` IN($ids);");
   			break;
		}
		iPHP::success('全部删除完成!','js:1');
	}
    function do_delspider() {
    	$this->sid OR iPHP::alert("请选择要删除的项目");
        iDB::query("delete from `#iCMS@__spider_url` where `id` = '$this->sid';");
        iPHP::success('删除完成','js:1');
    }

    function do_manage($doType = null) {
        $categoryApp = iACP::app('category',iCMS_APP_ARTICLE);
        $category    = $categoryApp->category;

        $sql = " WHERE 1=1";
        $_GET['keywords'] && $sql.="  AND `title` REGEXP '{$_GET['keywords']}'";
        $doType == "inbox" && $sql.=" AND `publish` ='0'";
        $_GET['pid'] && $sql.=" AND `pid` ='" . (int) $_GET['pid'] . "'";
        $_GET['rid'] && $sql.=" AND `rid` ='" . (int) $_GET['rid'] . "'";
        $_GET['starttime'] && $sql.=" AND `addtime`>=UNIX_TIMESTAMP('".$_GET['starttime']." 00:00:00')";
        $_GET['endtime']   && $sql.=" AND `addtime`<=UNIX_TIMESTAMP('".$_GET['endtime']." 23:59:59')";

        $sql.=$categoryApp->search_sql($this->cid);

        $ruleArray = $this->rule_opt(0, 'array');
        $postArray = $this->post_opt(0, 'array');
        $orderby = $_GET['orderby'] ? $_GET['orderby'] : "id DESC";
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total = iPHP::total(false, "SELECT count(*) FROM `#iCMS@__spider_url` {$sql}", "G");
        iPHP::pagenav($total, $maxperpage, "个网页");
        $rs = iDB::all("SELECT * FROM `#iCMS@__spider_url` {$sql} order by {$orderby} LIMIT " . iPHP::$offset . " , {$maxperpage}");
        $_count = count($rs);
        include iACP::view("spider.manage");
    }

    function do_inbox() {
        $this->do_manage("inbox");
    }

    function do_testcont() {
        $this->contTest = true;
        $this->spider_content();
    }

    function do_testrule() {
        $this->ruleTest = true;
        $this->spider_url();
    }

    function do_listpub() {
        $this->spider_url('HM');
    }
    function do_dropurl() {
    	$this->pid OR iPHP::alert("请选择要删除的项目");

    	$type	= $_GET['type'];
    	if($type=="0"){
    		$sql=" AND `publish`='0'";
    	}
        iDB::query("delete from `#iCMS@__spider_url` where `pid` = '$this->pid'{$sql};");
        iPHP::success('数据清除完成');
    }
    function do_start() {
        $a	= $this->spider_url();
        $this->do_mpublish($a);
    }
	function do_mpublish($pubArray=array()){
		iPHP::$break	= false;
		if($_POST['pub']){
			foreach((array)$_POST['pub'] as $i=>$a){
				list($cid,$pid,$rid,$url,$title)= explode('|',$a);
				$pubArray[]= array('sid'=>0,'url'=>$url,'title'=>$title,'cid'=>$cid,'rid'=>$rid,'pid'=>$pid);
			}
		}
		if(empty($pubArray)){
			iPHP::$break = true;
			iPHP::alert('暂无最新内容',0,30);
		}
		$_count	= count($pubArray);
        ob_start();
        ob_end_flush();
        ob_implicit_flush(1);
        foreach((array)$pubArray as $i=>$a){
            $this->sid   = $a['sid'];
            $this->cid   = $a['cid'];
            $this->pid   = $a['pid'];
            $this->rid   = $a['rid'];
            $this->url   = $a['url'];
            $this->title = $a['title'];
            $rs          = $this->multipublish();
            $updateMsg   = $i?true:false;
            $timeout     = ($i++)==$_count?'3':false;
			iPHP::dialog($rs['msg'], 'js:'.$rs['js'],$timeout,0,$updateMsg);
            ob_flush();
            flush();
		}
		iPHP::dialog('success:#:check:#:采集完成!',0,3,0,true);
	}
	function multipublish(){
		$a		= array();
		$code	= $this->do_publish('multi');
        //print_r($code);
        if(is_array($code)){
            $label='<span class="label label-success">发布成功!</span>';
        }else{
            $code=="-1" && $label='<span class="label label-warning">该URL的文章已经发布过!请检查是否重复</span>';
        }
        $a['msg'] = '标题:'.$this->title.'<br />URL:'.$this->url.'<br />'.$label.'<hr />';
        $a['js']  = 'parent.$("#' . md5($this->url) . '").remove();';
		return $a;
	}
    function do_publish($work = null) {
        $sid = $this->sid;
        if ($sid) {
            $sRs = iDB::row("SELECT * FROM `#iCMS@__spider_url` WHERE `id`='$this->sid' LIMIT 1;");
            $this->title = $sRs->title;
            $this->url = $sRs->url;
        }
        $hash	= md5($this->url);
        $sid	= iDB::value("SELECT `id` FROM `#iCMS@__spider_url` where `hash` = '$hash' and `publish`='1'");
        $msg	= '该URL的文章已经发布过!请检查是否重复';
        if ($sid) {
            $work===NULL && iPHP::alert($msg.' [sid:'.$sid.']', 'js:parent.$("#' . $hash . '").remove();');
            if($work=='multi'){
	            return '-1';
            }
        }elseif($title) {
            if (iDB::value("SELECT `id` FROM `#iCMS@__article` where `title` = '$title'")) {
            	if($sid){
                	iDB::query("UPDATE `#iCMS@__spider_url` SET `publish` = '1' WHERE `id` = '$sid';");
                }else{
                    $data = array(
                        'cid'=>$this->cid,'rid'=>$this->rid,'pid'=>$this->pid, 'title'=>$this->title, 'url'=>$this->url,
                        'hash'=>$hash, 'status'=>'1', 'publish'=>'1', 'addtime'=>time(), 'pubdate'=>time()
                    );
                    iDB::insert('spider_url',$data);
                }
                $work===NULL && iPHP::alert($msg, 'js:parent.$("#' . $hash . '").remove();');
	            if($work=='multi'){
					return '-1';
	            }
            }
        }
		return $this->post($work);
    }

    function post($work = null) {
        $_POST = $this->spider_content();
        if($this->work){
           if(empty($_POST['title'])){
               echo "标题不能为空\n";
               return false;
           }
           if(empty($_POST['body'])){
               echo "内容不能为空\n";
               return false;
           }
        }
        $pid          = $this->pid;
        $project      = $this->project($pid);
        $sleep        = $project['sleep'];
        $poid         = $project['poid'];
        $_POST['cid'] = $project['cid'];
        $postRs       = iDB::row("SELECT * FROM `#iCMS@__spider_post` WHERE `id`='$poid' LIMIT 1;");
        if ($postRs->post) {
            $postArray = explode("\n", $postRs->post);
            $postArray = array_filter($postArray);
            foreach ($postArray AS $key => $pstr) {
                list($pkey, $pval) = explode("=", $pstr);
                $_POST[$pkey] = trim($pval);
            }
        }
        iS::slashes($_POST);
        $app      = iACP::app($postRs->app);
        $fun      = $postRs->fun;
        $callback = $app->$fun("1001");
        if ($callback['code'] == "1001") {
            if ($this->sid) {
                iDB::query("UPDATE `#iCMS@__spider_url` SET `publish` = '1', `pubdate` = '" . time() . "' WHERE `id` = '$this->sid';");
                $work===NULL && iPHP::success("发布成功!",'js:1');
            } else {
                $hash    = md5($this->url);
                $title   = iS::escapeStr($_POST['title']);
                $url     = iS::escapeStr($_POST['reurl']);
                $indexid = $callback['indexid'];
                $data = array(
                    'cid'=>$this->cid,'rid'=>$this->rid,'pid'=>$pid,'indexid'=>$indexid,'title'=>$title, 'url'=>$url,
                    'hash'=>$hash, 'status'=>'1', 'publish'=>'1', 'addtime'=>time(), 'pubdate'=>time()
                );
                iDB::insert('spider_url',$data);
		        $work===NULL && iPHP::success("发布成功!", 'js:parent.$("#' . $hash . '").remove();');
            }
        }
        if($work=="shell"||$work=="multi"){
            $callback['work']=$work;
        	return $callback;
        }
    }
    function mkurls($url,$format,$begin,$num,$step,$zeroize,$reverse) {
        $urls = "";
        $start = (int)$begin;
        if($format==0){
            $end = $start+$num;
        }else if($format==1){
            $end = $start*pow($step,$num-1);
        }else if($format==2){
            $start = ord($begin);
            $end   = ord($num);
            $step  = 1;
        }
        $zeroize = ($zeroize=='true'?true:false);
        $reverse = ($reverse=='true'?true:false);
        //var_dump($url.','.$format.','.$begin.','.$num.','.$step,$zeroize,$reverse);
        if($reverse){
            for($i=$end;$i>=$start;){
                $id = $i;
                if($format==2){
                    $id = chr($i);
                }
                if($zeroize){
                    $len = strlen($end);
                    //$len==1 && $len=2;
                    $id  = sprintf("%0{$len}d", $i);
                }
                $urls[]=str_replace('<*>',$id,$url);
                if($format==1){
                  $i=$i/$step;
                }else{
                  $i=$i-$step;
                }
            }
        }else{
            for($i=$start;$i<=$end;){
                $id = $i;
                if($format==2){
                    $id = chr($i);
                }
                if($zeroize){
                    $len = strlen($end);
                    //$len==1 && $len=2;
                    $id  = sprintf("%0{$len}d", $i);
                }
                $urls[]=str_replace('<*>',$id,$url);
                if($format==1){
                  $i=$i*$step;
                }else{
                  $i=$i+$step;
                }
            }
        }
        return $urls;
    }
    function title_url($row,$rule){
        if($rule['mode']=="2"){
            $pq    = pq($row);
            list($title_attr,$url_attr) = explode("\n", $rule['list_url_rule']);
            $title_attr = trim($title_attr);
            $url_attr   = trim($url_attr);
            $title_attr OR $title_attr = 'text';
            $url_attr OR $url_attr = 'href';
            if($title_attr=='text'){
                $title = $pq->text();
            }else{
                $title = $pq->attr($title_attr);
            }
            $url = $pq->attr($url_attr);
        }else{
            $title = $row['title'];
            $url   = $row['url'];
        }
        $title = trim($title);
        $url   = trim($url);
        //_url_complement($baseUrl,$href)
        $url   = str_replace('<%url%>',$url, $rule['list_url']);
        $rule['list_url_clean'] && $url = $this->dataClean($rule['list_url_clean'],$url);
        $title = preg_replace('/<[\/\!]*?[^<>]*?>/is', '', $title);
        $this->title = $title;
        return array($title,$url);
    }
    function spider_url($work = NULL) {
        $pid = $this->pid;
        if ($pid) {
            $project = $this->project($pid);
            $cid = $project['cid'];
            $rid = $project['rid'];
            $prule_list_url = $project['list_url'];
        } else {
            $cid = $this->cid;
            $rid = $this->rid;
        }
        if($work=='shell'){
            echo '开始采集方案['.$pid."]\n";
        }
        $ruleA = $this->rule($rid);
        $rule = $ruleA['rule'];
        $urls = $rule['list_urls'];
        $project['urls'] && $urls = $project['urls'];

        $urlsArray  = explode("\n", $urls);
        $urlsArray  = array_filter($urlsArray);
        $_urlsArray = $urlsArray;
        $urlsList   = array();
        foreach ($_urlsArray AS $_key => $_url) {
            $_url = htmlspecialchars_decode($_url);
            preg_match('|.*<(.*)>.*|is',$_url, $_matches);
            if($_matches){
                list($format,$begin,$num,$step,$zeroize,$reverse) = explode(',',$_matches[1]);
                $url = str_replace($_matches[1], '*',trim($_matches[0]));
                $_urlsList = $this->mkurls($url,$format,$begin,$num,$step,$zeroize,$reverse);
                unset($urlsArray[$_key]);
                $urlsList = array_merge($urlsList,$_urlsList);
            }
        }
        $urlsArray = array_merge($urlsArray,$urlsList);
        unset($_urlsArray,$_key,$_url,$_matches,$_urlsList,$urlsList);
        $urlsArray  = array_unique($urlsArray);

        // $this->useragent = $rule['user_agent'];
        // $this->encoding  = $rule['curl']['encoding'];
        // $this->referer   = $rule['curl']['referer'];
        // $this->charset   = $rule['charset'];

        if(empty($urlsArray)){
            if($work=='shell'){
                echo "采集列表为空!请填写!\n";
                return false;
            }
            iPHP::alert('采集列表为空!请填写!', 'js:parent.window.iCMS_MODAL.destroy();');
        }

//    	if($this->ruleTest){
//	    	echo "<pre>";
//	    	print_r(iS::escapeStr($project));
//	    	print_r(iS::escapeStr($rule));
//	    	echo "</pre>";
//	    	echo "<hr />";
//		}
        if($rule['mode']=="2"){
            iPHP::import(iPHP_LIB.'/phpQuery.php');
            $this->ruleTest && $_GET['pq_debug'] && phpQuery::$debug =1;
        }

        $pubArray = array();
        foreach ($urlsArray AS $key => $url) {
            $url = trim($url);
            if($work=='shell'){
                echo '开始采集列表:'.$url."\n";
            }
            if ($this->ruleTest) {
                echo $url . "<br />";
            }
            $html = $this->remote($url);
            if($rule['mode']=="2"){
                $doc       = phpQuery::newDocumentHTML($html,'UTF-8');
                $list_area = $doc[trim($rule['list_area_rule'])];
                empty($rule['list_area_format']) && $rule['list_area_format'] = 'a';
                $lists     = pq($list_area)->find(trim($rule['list_area_format']));
            }else{
                $list_area_rule = $this->pregTag($rule['list_area_rule']);
                if ($list_area_rule) {
                    preg_match('|' . $list_area_rule . '|is', $html, $matches, $PREG_SET_ORDER);
                    $list_area = $matches['content'];
                } else {
                    $list_area = $html;
                }

    			$html = null;
                unset($html);

                if ($this->ruleTest) {
                    echo iS::escapeStr($rule['list_area_rule']);
    //    			echo iS::escapeStr($list_area);
                    echo "<hr />";
                }
                if ($rule['list_area_format']) {
                    $list_area = $this->dataClean($rule['list_area_format'], $list_area);
                }
                if ($this->ruleTest) {
                    echo iS::escapeStr($rule['list_area_format']);
                    echo "<hr />";
                    echo iS::escapeStr($list_area);
                    echo "<hr />";
                }

                preg_match_all('|' . $this->pregTag($rule['list_url_rule']) . '|is', $list_area, $lists, PREG_SET_ORDER);

                $list_area = null;
                unset($list_area);
            }
            //
            if ($rule['sort'] == "1") {
                //arsort($lists);
            } elseif ($rule['sort'] == "2") {
                asort($lists);
            } elseif ($rule['sort'] == "3") {
                shuffle($lists);
            }

            if ($this->ruleTest) {
                echo iS::escapeStr($rule['list_url_rule']);
                echo "<hr />";
                echo iS::escapeStr($rule['list_url']);
                echo "<hr />";
            }
			if($prule_list_url){
				$rule['list_url']	= $prule_list_url;
			}
            if ($work) {
                $listsArray[$url] = $lists;
            } else {
                foreach ($lists AS $lkey => $row) {
                    list($title,$url) = $this->title_url($row,$rule);
                    $hash  = md5($url);
                    if ($this->ruleTest) {
                        echo $title . ' (<a href="' . APP_URI . '&do=testcont&url=' . $url . '&rid=' . $rid . '&pid=' . $pid . '&title=' . urlencode($title) . '" target="_blank">测试内容规则</a>) <br />';
                        echo $url . "<br />";
                        echo $hash . "<br /><br />";
                    } else {
                        //iDB::query("INSERT INTO `#iCMS@__spider_url` (`cid`, `rid`,`pid`, `hash`, `title`, `url`, `status`, `publish`, `addtime`, `pubdate`) VALUES ('$cid', '$rid','$pid','$hash','$title', '$url', '0', '0', '" . time() . "', '0');");
                        $this->checkurl($hash) OR $pubArray[]	=array('sid'=>iDB::$insert_id,'url'=>$url,'title'=>$title,'cid'=>$cid,'rid'=>$rid,'pid'=>$pid,'hash'=>$hash);
                    }
                }
            }
        }
        if(!$work){
            return $pubArray;
        }
		$lists = null;
        unset($lists);
		gc_collect_cycles();

        if ($work) {
			if($work=="shell"){
				$urlArray	= array();
                echo '共采集到'.count($listsArray)."页\n";
				foreach ($listsArray AS $furl => $lists) {
                    echo "开始采集:".$furl." 列表 ".count($lists)."条记录\n";
					foreach ($lists AS $lkey => $row) {
                        list($title,$url) = $this->title_url($row,$rule);
                        $hash  = md5($url);
                        echo "title:".$this->title."\n";
                        echo "url:".$url."\n";
						if(!$this->checkurl($hash)){
                            echo "开始采集....";
							//$urlArray[]= $url;
                            $this->rid = $rid;
                            $this->url = $url;

                            $callback  = $this->post("shell");
							if ($callback['code'] == "1001") {
                                echo "....OK\n";
								if($project['sleep']){
									echo "sleep:".$project['sleep']."s\n";
									unset($lists[$lkey]);
									gc_collect_cycles();
									sleep($project['sleep']);
								}else{
									sleep(1);
								}
							}else{
                                echo "error\n\n";
                                continue;
								//die("error");
							}
						}else{
                            echo "采集过了\n\n";
                        }
					}
				}
				return $urlArray;
			}

            $sArrayTmp = iDB::all("SELECT `hash` FROM `#iCMS@__spider_url` where `pid`='$pid'");
            $_count = count($sArrayTmp);
            for ($i = 0; $i < $_count; $i++) {
                $sArray[$sArrayTmp[$i]['hash']] = 1;
            }
            unset($sArrayTmp);
            include iACP::view("spider.lists");
        }
    }

    function spider_content() {
		ini_get('safe_mode') OR set_time_limit(0);
        $sid = $this->sid;
        if ($sid) {
            $sRs   = iDB::row("SELECT * FROM `#iCMS@__spider_url` WHERE `id`='$sid' LIMIT 1;");
            $title = $sRs->title;
            $cid   = $sRs->cid;
            $pid   = $sRs->pid;
            $url   = $sRs->url;
            $rid   = $sRs->rid;
       } else {
            $rid   = $this->rid;
            $pid   = $this->pid;
            $title = $this->title;
            $url   = $this->url;
        }

		if($pid){
            $project        = $this->project($pid);
            $prule_list_url = $project['list_url'];
		}

        $ruleA           = $this->rule($rid);
        $rule            = $ruleA['rule'];
        $dataArray       = $rule['data'];

		if($prule_list_url){
			$rule['list_url']	= $prule_list_url;
		}

        if ($this->contTest) {
            echo "<pre>";
            print_r(iS::escapeStr($ruleA));
            print_r(iS::escapeStr($$project));
            echo "</pre><hr />";
        }

        $responses = array();
        $html = $this->remote($url);
        if(empty($html)){
            if($this->work=='shell'){
                echo '错误:001..采集 ' . $url . "文件内容为空!请检查采集规则\n";
                return false;
            }else{
                iPHP::alert('错误:001..采集 ' . $url . ' 文件内容为空!请检查采集规则');
            }
        }

//    	$http	= $this->check_content_code($html);
//
//    	if($http['match']==false){
//    		return false;
//    	}
//		$content		= $http['content'];
        $this->allHtml = "";
        $responses['reurl'] = $url;
        $rule['__url__']	= $url;
        foreach ($dataArray AS $key => $data) {
            $content = $this->content($html,$data,$rule);
            $responses[$data['name']] = $content;
            if($data['name']=='title' && empty($content)){
                $responses['title'] = $title;
            }
        }
		$html = null;
        unset($html);
        gc_collect_cycles();
        if ($this->contTest) {
            echo "<pre style='width:99%;word-wrap: break-word;'>";
            print_r(iS::escapeStr($responses));
            echo "</pre><hr />";
        }

        iFS::$CURLOPT_ENCODING = $rule['fs']['encoding'];
        $rule['fs']['referer'] && iFS::$CURLOPT_REFERER  = $rule['fs']['referer'];
        return $responses;
    }

    function content($html,$data,$rule) {
        $name = $data['name'];
        if ($data['page']) {
        	if(empty($rule['page_url'])){
        		$rule['page_url']=$rule['list_url'];
        	}
            if (empty($this->allHtml)) {
		        $page_area_rule = $this->pregTag($rule['page_area_rule']);
		        if ($page_area_rule) {
		            preg_match('|' . $page_area_rule . '|is', $html, $matches, $PREG_SET_ORDER);
		            $page_area = $matches['content'];
		        } else {
		            $page_area = $html;
		        }
            	if($rule['page_url_rule']){
            		$page_url_rule = $this->pregTag($rule['page_url_rule']);
            		preg_match_all('|' .$page_url_rule. '|is', $page_area, $page_url_matches, PREG_SET_ORDER);
            		foreach ($page_url_matches AS $pn => $row) {
            			$page_url_array[$pn] = str_replace('<%url%>', $row['url'], $rule['page_url']);
            			gc_collect_cycles();
            		}
            	}else{
                    if($rule['page_url_parse']=='<%url%>'){
                        $page_url = str_replace('<%url%>',$rule['__url__'],$rule['page_url']);
                    }else{
                		$page_url_rule = $this->pregTag($rule['page_url_parse']);
    					preg_match('|' . $page_url_rule . '|is', $rule['__url__'], $matches, $PREG_SET_ORDER);
    			        $page_url = str_replace('<%url%>', $matches['url'], $rule['page_url']);
			        }
                    $page_url_array	= array();
                    if (stripos($page_url,'<%step%>') !== false){
                        for ($pn = $rule['page_no_start']; $pn <= $rule['page_no_end']; $pn = $pn + $rule['page_no_step']) {
                            $page_url_array[$pn] = str_replace('<%step%>', $pn, $page_url);
                            gc_collect_cycles();
                        }
                    }
            	}
				unset($page_area);
		        if ($this->contTest) {
		            echo $rule['__url__'] . "<br />";
		            echo $rule['page_url'] . "<br />";
		            echo iS::escapeStr($page_url_rule);
		            echo "<hr />";
		        }
				if($this->contTest){
					echo "<pre>";
					print_r($page_url_array);
					echo "</pre><hr />";
				}
		        $this->content_right_code = trim($rule['page_url_right']);
		        $this->content_error_code = trim($rule['page_url_error']);

                $pcontent = '';
                $pcon     = '';
                foreach ($page_url_array AS $pukey => $purl) {
                    usleep(100);
                    $phtml = $this->remote($purl);
                    if ($phtml === false) {
                        break;
                    }
                    $phttp = $this->check_content_code($phtml);

                    if ($phttp['match'] == false) {
                        break;
                    }

                    $pageurl[] = $purl;
                    $pcon.= $phttp['content'];
                }
                $html.= $pcon;

                if ($this->contTest) {
                    echo "<pre>";
                    print_r($pageurl);
                    echo "</pre><hr />";
                }
            }else{
                $html = $this->allHtml;
            }
        }
        if($data['dom']){
            iPHP::import(iPHP_LIB.'/phpQuery.php');
            $this->contTest && $_GET['pq_debug'] && phpQuery::$debug =1;
            $doc     = phpQuery::newDocumentHTML($html,'UTF-8');
            list($content_dom,$content_fun,$content_attr) = explode("\n", $data['rule']);
            $content_dom  = trim($content_dom);
            $content_fun  = trim($content_fun);
            $content_attr = trim($content_attr);
            $content_fun OR $content_fun = 'html';
            if ($data['multi']) {
                $conArray = array();
                foreach ($doc[$content_dom] as $doc_key => $doc_value) {
                    if($content_attr){
                        $conArray[] = pq($doc_value)->$content_fun($content_attr);
                    }else{
                        $conArray[] = pq($doc_value)->$content_fun();
                    }
                }
                $content = implode('#--iCMS.PageBreak--#', $conArray);
            }else{
                if($content_attr){
                    $content = $doc[$content_dom]->$content_fun($content_attr);
                }else{
                    $content = $doc[$content_dom]->$content_fun();
                }
            }

            if ($this->contTest) {
                print_r(htmlspecialchars($content));
                echo "<hr />";
            }
            phpQuery::unloadDocuments();
        }else{
            $data_rule = $this->pregTag($data['rule']);
            if ($this->contTest) {
                print_r(iS::escapeStr($data_rule));
                echo "<hr />";
            }
            if (preg_match('/(<\w+>|\.\*|\.\+|\\\d|\\\w)/i', $data_rule)) {
                if ($data['multi']) {
                    preg_match_all('|' . $data_rule . '|is', $html, $matches, PREG_SET_ORDER);
                    $conArray = array();
                    foreach ((array) $matches AS $mkey => $mat) {
                        $conArray[] = $mat['content'];
                    }
                    $content = implode('#--iCMS.PageBreak--#', $conArray);
                    if ($this->contTest) {
                        print_r(htmlspecialchars($content));
                        echo "<hr />";
                    }
                } else {
                    preg_match('|' . $data_rule . '|is', $html, $matches, $PREG_SET_ORDER);
                    $content = $matches['content'];
                }
            } else {
                $content = $data_rule;
            }
        }
		$html = null;
        unset($html);

        if ($data['cleanbefor']) {
            $content = $this->dataClean($data['cleanbefor'], $content);
        }
        if ($data['cleanhtml']) {
            $content = preg_replace('/<[\/\!]*?[^<>]*?>/is', '', $content);
        }
        if ($data['format'] && $content) {
            // $_content = iPHP::cleanHtml($content);
            // trim($_content) && $content = $_content;
            $content = autoformat($content);
            $content = stripslashes($content);
            unset($_content);
        }

        if ($data['img_absolute'] && $content) {
            preg_match_all("/<img.*?src\s*=[\"|'](.*?)[\"|']/is", $content, $img_match);
            if($img_match[1]){
                $_img_array = array_unique($img_match[1]);
                $_img_urls  = array();
                foreach ((array)$_img_array as $_img_key => $_img_src) {
                    $_img_urls[$_img_key] = $this->_url_complement($rule['__url__'],$_img_src);
                }
               $content = str_replace($_img_array, $_img_urls, $content);
            }
        }

        $data['trim'] && $content = trim($content);

        if ($data['json_decode']) {
            $content = preg_replace('/&#\d{2,5};/ue', "utf8_entity_decode('\\0')", $content);
            $content = preg_replace(array('/&#x([a-fA-F0-7]{2,8});/ue', '/%u([a-fA-F0-7]{2,8})/ue', '/\\\u([a-fA-F0-7]{2,8})/ue'), "utf8_entity_decode('&#'.hexdec('\\1').';')", $content);
            $content = htmlspecialchars_decode($content);
        }
        if ($data['cleanafter']) {
            $content = $this->dataClean($data['cleanafter'], $content);
        }
        if ($data['mergepage']) {
            $_content = $content;
            preg_match_all("/<img.*?src\s*=[\"|'|\s]*(http:\/\/.*?\.(gif|jpg|jpeg|bmp|png)).*?>/is", $_content, $picArray);
            $pA = array_unique($picArray[1]);
            $pA = array_filter($pA);
            $_pcount = count($pA);
            if ($_pcount < 4) {
                $content = str_replace('#--iCMS.PageBreak--#', "", $content);
            } else {
                $contentA = explode("#--iCMS.PageBreak--#", $_content);
                $newcontent = array();
                $this->checkpage($newcontent, $contentA, 2);
                if (is_array($newcontent)) {
                    $content = array_filter($newcontent);
                    $content = implode('#--iCMS.PageBreak--#', $content);
                    //$content		= addslashes($content);
                } else {
                    //$content		= addslashes($newcontent);
                    $content = $newcontent;
                }
            }
        }
        if ($data['empty'] && empty($content)) {
            if($this->work){
                echo "\n[".$name . "内容为空!请检查,规则是否正确!]\n";
                return false;
            }else{
                $this->contTest && iPHP::$dialog['alert']='window';
                iPHP::alert($name . '内容为空!请检查,规则是否正确!!');
            }
        }
        if($data['array']){
        	return array($content);
        }
        return $content;
    }
    function _url_complement($baseUrl,$href){
        $href = trim($href);
        if (stripos($href,'http://') === false){
            if ($href{0}=='/'){
                $base_uri  = parse_url($baseUrl);
                $base_host = $base_uri['scheme'].'://'.$base_uri['host'];
                return $base_host.'/'.ltrim($href,'/');
            }else{
                $base_url  = pathinfo($baseUrl,PATHINFO_DIRNAME);
                return iFS::path($base_url.'/'.ltrim($href,'/'));
            }
        }else{
            return $href;
        }
    }
    function dataClean($rules, $content) {
        iPHP::import(iPHP_LIB.'/phpQuery.php');
        $ruleArray = explode("\n", $rules);
        foreach ($ruleArray AS $key => $rule) {
            list($_pattern, $_replacement) = explode("==", $rule);
            $_pattern     = trim($_pattern);
            $_replacement = trim($_replacement);
            $_replacement = str_replace('\n', "\n", $_replacement);
            if(strpos($_pattern, 'DOM::')!==false){
                $doc      = phpQuery::newDocumentHTML($content,'UTF-8');
                $_pattern = str_replace('DOM::','', $_pattern);
                list($pq_dom, $pq_fun,$pq_attr) = explode("::", $_pattern);
                $pq_array = pq($pq_dom);
                foreach ($pq_array as $pq_key => $pq_val) {
                    if($pq_fun){
                        if($pq_attr){
                            $pq_content = pq($pq_val)->$pq_fun($pq_attr);
                        }else{
                            $pq_content = pq($pq_val)->$pq_fun();
                        }
                    }else{
                        $pq_content = (string)pq($pq_val);
                    }
                    $pq_pattern[$pq_key]     = $pq_content;
                    $pq_replacement[$pq_key] = $_replacement;
                }
                //var_dump(array_map('htmlspecialchars', $pq_pattern));
                $content = str_replace($pq_pattern,$pq_replacement, $content);
            }else{
                $replacement[$key] = $_replacement;
                $pattern[$key] = '|' . $this->pregTag($_pattern) . '|is';
            }
        }
        if($pattern){
            return preg_replace($pattern, $replacement, $content);
        }else{
            return $content;
        }
    }

    function checkurl($hash) {
        $id = iDB::value("SELECT `id` FROM `#iCMS@__spider_url` WHERE `hash`='$hash'");
        return $id ? true : false;
    }

    function pregTag($rule) {
        $rule = trim($rule);
        $rule = str_replace("%>", "%>\n", $rule);
        preg_match_all("/<%(.+)%>/i", $rule, $matches);
        $pregArray = array_unique($matches[0]);
        $pregflip = array_flip($pregArray);

        foreach ((array)$pregflip AS $kpreg => $vkey) {
            $pregA[$vkey] = "###iCMS_PREG_" . rand(1, 1000) . '_' . $vkey . '###';
        }
        $rule = str_replace($pregArray, $pregA, $rule);
        $rule = preg_quote($rule, '|');
        $rule = str_replace($pregA, $pregArray, $rule);
        $rule = str_replace("%>\n", "%>", $rule);
        $rule = preg_replace('|<%(\w{3,20})%>|i', '(?<\\1>.*?)', $rule);
        $rule = str_replace(array('<%', '%>'), '', $rule);
        return $rule;
    }

    function rule($id) {
        $rs = iDB::row("SELECT * FROM `#iCMS@__spider_rule` WHERE `id`='$id' LIMIT 1;", ARRAY_A);
        $rs['rule'] && $rs['rule'] = stripslashes_deep(unserialize($rs['rule']));
        $rs['user_agent'] OR $rs['user_agent'] = "Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)";
        $this->useragent = $rs['rule']['user_agent'];
        $this->encoding  = $rs['rule']['curl']['encoding'];
        $this->referer   = $rs['rule']['curl']['referer'];
        $this->charset   = $rs['rule']['charset'];
        return $rs;
    }

    function do_rule() {
        if ($_GET['keywords']) {
            $sql = " WHERE `keyword` REGEXP '{$_GET['keywords']}'";
        }
        $orderby = $_GET['orderby'] ? $_GET['orderby'] : "id DESC";
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total = iPHP::total(false, "SELECT count(*) FROM `#iCMS@__spider_rule` {$sql}", "G");
        iPHP::pagenav($total, $maxperpage, "个规则");
        $rs = iDB::all("SELECT * FROM `#iCMS@__spider_rule` {$sql} order by {$orderby} LIMIT " . iPHP::$offset . " , {$maxperpage}");
        $_count = count($rs);
        include iACP::view("spider.rule");
    }

    function do_copyrule() {
        iDB::query("insert into `#iCMS@__spider_rule` (`name`, `rule`) select `name`, `rule` from `#iCMS@__spider_rule` where id = '$this->rid'");
        $rid = iDB::$insert_id;
        iPHP::success('复制完成,编辑此规则', 'url:' . APP_URI . '&do=addrule&rid=' . $rid);
    }

    function do_delrule() {
    	$this->rid OR iPHP::alert("请选择要删除的项目");
        iDB::query("delete from `#iCMS@__spider_rule` where `id` = '$this->rid';");
        iPHP::success('删除完成','js:1');
    }

    function do_addrule() {
        $rs = array();
        $this->rid && $rs = $this->rule($this->rid);
        $rs['rule'] && $rule = $rs['rule'];
        if (empty($rule['data'])) {
            $rule['data'] = array(
                array('name' => 'title', 'trim' => true, 'empty' => true),
                array('name' => 'body', 'trim' => true, 'empty' => true, 'format' => true, 'page' => true, 'multi' => true),
            );
        }
        $rule['sort'] OR $rule['sort'] = 1;
        $rule['mode'] OR $rule['mode'] = 1;
        $rule['page_no_start'] OR $rule['page_no_start'] = 1;
        $rule['page_no_end'] OR $rule['page_no_end'] = 5;
        $rule['page_no_step'] OR $rule['page_no_step'] = 1;
        include iACP::view("spider.addrule");
    }

    function do_saverule() {
        $id = (int) $_POST['id'];
        $name = iS::escapeStr($_POST['name']);
        $rule = $_POST['rule'];

        empty($name) && iPHP::alert('规则名称不能为空！');
        //empty($rule['list_area_rule']) 	&& iPHP::alert('列表区域规则不能为空！');
        if($rule['mode']!='2'){
            empty($rule['list_url_rule']) && iPHP::alert('列表链接规则不能为空！');
        }

        $rule   = addslashes(serialize($rule));
        $fields = array('name', 'rule');
        $data   = compact ($fields);
        if ($id) {
            iDB::update('spider_rule', $data, array('id'=>$id));
        } else {
            iDB::insert('spider_rule',$data);
        }
        iPHP::success('保存成功');
    }

    function rule_opt($id = 0, $output = null) {
        $rs = iDB::all("SELECT * FROM `#iCMS@__spider_rule` order by id desc");
        foreach ((array)$rs AS $rule) {
            $rArray[$rule['id']] = $rule['name'];
            $opt.="<option value='{$rule['id']}'" . ($id == $rule['id'] ? " selected='selected'" : '') . ">{$rule['name']}[id='{$rule['id']}'] </option>";
        }
        if ($output == 'array') {
            return $rArray;
        }
        return $opt;
    }

    function do_post() {
        if ($_GET['keywords']) {
            $sql = " WHERE `keyword` REGEXP '{$_GET['keywords']}'";
        }
        $orderby = $_GET['orderby'] ? $_GET['orderby'] : "id DESC";
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total = iPHP::total(false, "SELECT count(*) FROM `#iCMS@__spider_post` {$sql}", "G");
        iPHP::pagenav($total, $maxperpage, "个模块");
        $rs = iDB::all("SELECT * FROM `#iCMS@__spider_post` {$sql} order by {$orderby} LIMIT " . iPHP::$offset . " , {$maxperpage}");
        $_count = count($rs);
        include iACP::view("spider.post");
    }
    function do_delpost() {
    	$this->poid OR iPHP::alert("请选择要删除的项目");
        iDB::query("delete from `#iCMS@__spider_post` where `id` = '$this->poid';");
        iPHP::success('删除完成','js:1');
    }
    function do_addpost() {
        $this->poid && $rs = iDB::row("SELECT * FROM `#iCMS@__spider_post` WHERE `id`='$this->poid' LIMIT 1;", ARRAY_A);
        include iACP::view("spider.addpost");
    }

    function do_savepost() {
        $id     = (int) $_POST['id'];
        $name   = trim($_POST['name']);
        $app    = iS::escapeStr($_POST['app']);
        $post   = trim($_POST['post']);
        $fun    = trim($_POST['fun']);

        $fields = array('name','app','fun', 'post');
        $data   = compact ($fields);
        if ($id) {
            iDB::update('spider_post', $data, array('id'=>$id));
        } else {
            iDB::insert('spider_post',$data);
        }
        iPHP::success('保存成功', 'url:' . APP_URI . '&do=post');
    }

    function post_opt($id = 0, $output = null) {
        $rs = iDB::all("SELECT * FROM `#iCMS@__spider_post`");
        foreach ((array)$rs AS $post) {
        	$pArray[$post['id']] = $post['name'];
            $opt.="<option value='{$post['id']}'" . ($id == $post['id'] ? " selected='selected'" : '') . ">{$post['name']}:{$post['app']}[id='{$post['id']}'] </option>";
        }
        if ($output == 'array') {
            return $pArray;
        }
        return $opt;
    }

    function project($id) {
        return iDB::row("SELECT * FROM `#iCMS@__spider_project` WHERE `id`='$id' LIMIT 1;", ARRAY_A);
    }

    function do_copyproject() {
        iDB::query("INSERT INTO `#iCMS@__spider_project` (`name`, `urls`, `cid`, `rid`, `poid`, `sleep`) select `name`, `urls`, `cid`, `rid`, `poid`, `sleep` from `#iCMS@__spider_project` where id = '$this->pid'");
        $pid = iDB::$insert_id;
        iPHP::success('复制完成,编辑此方案', 'url:' . APP_URI . '&do=addproject&pid=' . $pid.'&copy=1');
    }

    function do_project() {
        $categoryApp = iACP::app('category',iCMS_APP_ARTICLE);
        $category    = $categoryApp->category;

        $sql = "where 1=1";
        if ($_GET['keywords']) {
            $sql.= " and `keyword` REGEXP '{$_GET['keywords']}'";
        }
        $sql.= $categoryApp->search_sql($this->cid);

        if ($_GET['rid']) {
            $sql.=" AND `rid` ='" . (int) $_GET['rid'] . "'";
        }
        if ($_GET['poid']) {
            $sql.=" AND `poid` ='" . (int) $_GET['poid'] . "'";
        }
        $ruleArray = $this->rule_opt(0, 'array');
        $postArray = $this->post_opt(0, 'array');
        $orderby = $_GET['orderby'] ? $_GET['orderby'] : "id DESC";
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total = iPHP::total(false, "SELECT count(*) FROM `#iCMS@__spider_project` {$sql}", "G");
        iPHP::pagenav($total, $maxperpage, "个方案");
        $rs = iDB::all("SELECT * FROM `#iCMS@__spider_project` {$sql} order by {$orderby} LIMIT " . iPHP::$offset . " , {$maxperpage}");
        $_count = count($rs);
        include iACP::view("spider.project");
    }
    function do_delproject() {
    	$this->pid OR iPHP::alert("请选择要删除的项目");
        iDB::query("delete from `#iCMS@__spider_project` where `id` = '$this->pid';");
        iPHP::success('删除完成');
    }
    function do_addproject() {
        $rs = array();
        $this->pid && $rs = $this->project($this->pid);
        $cid = empty($rs['cid']) ? $this->cid : $rs['cid'];

        $categoryApp = iACP::app('category',iCMS_APP_ARTICLE);

        $cata_option = $categoryApp->select(false,$cid);
        $rule_option = $this->rule_opt($rs['rid']);
        $post_option = $this->post_opt($rs['poid']);

        $rs['sleep'] OR $rs['sleep'] = 30;
        include iACP::view("spider.addproject");
    }

    function do_saveproject() {
        $id       = (int) $_POST['id'];
        $name     = iS::escapeStr($_POST['name']);
        $urls     = iS::escapeStr($_POST['urls']);
        $list_url = $_POST['list_url'];
        $cid      = iS::escapeStr($_POST['cid']);
        $rid      = iS::escapeStr($_POST['rid']);
        $poid     = iS::escapeStr($_POST['poid']);
        $sleep    = iS::escapeStr($_POST['sleep']);
        $auto     = iS::escapeStr($_POST['auto']);

        empty($name)&& iPHP::alert('名称不能为空！');
        empty($cid) && iPHP::alert('请选择绑定的栏目');
        empty($rid) && iPHP::alert('请选择采集规则');
        //empty($poid)	&& iPHP::alert('请选择发布规则');
        $fields = array('name', 'urls','list_url', 'cid', 'rid', 'poid', 'sleep', 'auto');
        $data   = compact ($fields);
        if ($id) {
            iDB::update('spider_project',$data,array('id'=>$id));
        } else {
            iDB::insert('spider_project',$data);
        }
        iPHP::success('完成', 'url:' . APP_URI . '&do=project');
    }

    function remote($url, $_count = 0) {
        if(empty($this->referer)){
            $uri = parse_url($url);
            $this->referer = $uri['scheme'] . '://' . $uri['host'];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_ENCODING, $this->encoding);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_REFERER, $this->referer);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_NOSIGNAL, true);
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        // curl_setopt($ch, CURLOPT_MAXREDIRS, 7);//查找次数，防止查找太深
        $responses = curl_exec($ch);
        $info = curl_getinfo($ch);
        if ($this->contTest || $this->ruleTest) {
            echo '<pre>';
            print_r($info);
            echo '</pre><hr />';
            if($_GET['breakinfo']){
            	exit();
            }
        }
        if (($info['http_code'] == 301 || $info['http_code'] == 302) && $_count < 5) {
            $_count++;
            $newurl = $info['redirect_url'];
	        if(empty($newurl)){
		    	curl_setopt($ch, CURLOPT_HEADER, 1);
		    	$header		= curl_exec($ch);
		    	preg_match ('|Location: (.*)|i',$header,$matches);
		    	$newurl 	= ltrim($matches[1],'/');
			    if(empty($newurl)) return false;

		    	if(!strstr($newurl,'http://')){
			    	$host	= $uri['scheme'].'://'.$uri['host'];
		    		$newurl = $host.'/'.$newurl;
		    	}
	        }
	        $newurl	= trim($newurl);
			curl_close($ch);
			unset($responses,$info);
            return $this->remote($url, $_count);
        }
        if ($info['http_code'] == 404 || $info['http_code'] == 500) {
			curl_close($ch);
			unset($responses,$info);
            return false;
        }

        if ((empty($responses)||empty($info['http_code'])) && $_count < 5) {
            $_count++;
            if ($this->contTest || $this->ruleTest) {
                echo $url . '<br />';
                echo "获取内容失败,重试第{$_count}次...<br />";
            }
			curl_close($ch);
			unset($responses,$info);
            return $this->remote($url, $_count);
        }
        $pos = stripos($info['content_type'], 'charset=');
        if($pos!==false){
            $content_charset = substr($info['content_type'], $pos+8);
        }

        $this->charset && $responses = $this->charsetTrans($responses,$content_charset,$this->charset);
		curl_close($ch);
		unset($info);
        if ($this->contTest || $this->ruleTest) {
            echo '<pre>';
            print_r(htmlspecialchars(substr($responses,0,500)));
            echo '</pre><hr />';
        }
        return $responses;
    }
    function charsetTrans($html,$content_charset,$encode, $out = 'UTF-8') {
        if($encode=='auto'){
            preg_match('/<meta[^>]*?charset=(["\']?)([a-zA-z0-9\-\_]+)(\1)[^>]*?>/is', $html, $charset);
            $encode = str_replace(array('"',"'"),'', trim($charset[2]));
            if($content_charset){
                $encode = $content_charset;
            }
            if(function_exists('mb_detect_encoding') && empty($encode)) {
                $encode = mb_detect_encoding($html, array("ASCII","UTF-8","GB2312","GBK","BIG5"));                var_dump('mb_detect_encoding:'.$encode);
            }
        }
        if ($this->contTest || $this->ruleTest) {
            echo 'encoding:'.$encode . '<br />';
        }
        if(strtoupper($encode)=='UTF-8'){
            return $html;
        }
        $html = preg_replace('/(<meta[^>]*?charset=(["\']?))[a-z\d_\-]*(\2[^>]*?>)/is', "\\1$out\\3", $html,1);
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($html,'UTF-8',$encode);
        } elseif (function_exists('iconv')) {
            return iconv($encode,'UTF-8', $html);
        } else {
            iPHP::throwException('charsetTrans failed, no function');
        }
    }
    function check_content_code($content) {
        if ($this->content_right_code) {
	        $matches = strpos($content, $this->content_right_code);
	        if ($matches===false) {
	            $match = false;
	            return false;
	        }
        }
        if ($this->content_error_code) {
            $_matches = strpos($content, $this->content_error_code);
            if ($_matches!==false) {
                $match = false;
                return false;
            }
        }
        $match = true;
        usleep(10);
        return compact('content', 'match');
    }

    function checkpage(&$newbody, $bodyA, $_count = 1, $nbody = "", $i = 0, $k = 0) {
        $ac = count($bodyA);
        $nbody.= $bodyA[$i];
        preg_match_all("/<img.*?src\s*=[\"|'|\s]*(http:\/\/.*?\.(gif|jpg|jpeg|bmp|png)).*?>/is", $nbody, $picArray);
        $pA = array_unique($picArray[1]);
        $pA = array_filter($pA);
        $_pcount = count($pA);
        //	print_r($_pcount);
        //	echo "\n";
        //	print_r('_count:'.$_count);
        //	echo "\n";
        //	var_dump($_pcount>$_count);
        if ($_pcount >= $_count) {
            $newbody[$k] = $nbody;
            $k++;
            $nbody = "";
        }
        $ni = $i + 1;
        if ($ni <= $ac) {
            $this->checkpage($newbody, $bodyA, $_count, $nbody, $ni, $k);
        } else {
            $newbody[$k] = $nbody;
        }
    }

}

function stripslashes_deep($value) {
    $value = is_array($value) ?
            array_map('stripslashes_deep', $value) :
            stripslashes($value);

    return $value;
}

function str_cut($str, $start, $end) {
    $content = strstr($str, $start);
    $content = substr($content, strlen($start), strpos($content, $end) - strlen($start));
    return $content;
}

function utf8_entity_decode($entity) {
    $convmap = array(0x0, 0x10000, 0, 0xfffff);
    return mb_decode_numericentity($entity, $convmap, 'UTF-8');
}
