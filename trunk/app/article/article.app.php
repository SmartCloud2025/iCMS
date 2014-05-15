<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: article.app.php 2408 2014-04-30 18:58:23Z coolmoo $
 */
class articleApp {
	public $methods	= array('iCMS');
    function __construct() {}
    public function doiCMS($a = null) {
    	return $this->article((int)$_GET['id'],isset($_GET['p'])?(int)$_GET['p']:1);
    }
    public function article($id,$page=1,$tpl=true){
        $aRs		= iDB::getRow("SELECT * FROM #iCMS@__article WHERE id='".(int)$id."' AND `status` ='1' LIMIT 1;",ARRAY_A);
        if($aRs['url']) {
            if(iPHP::$iTPLMode=="html") {
                return false;
            }else {
            	iDB::query("UPDATE `#iCMS@__article` SET hits=hits+1 WHERE `id` ='$id'");
                iPHP::gotourl($aRs['url']);
            }
        }
        if($aRs){
	        $dRs	= iDB::getRow("SELECT body,subtitle FROM #iCMS@__article_data WHERE aid='".(int)$id."' LIMIT 1;",ARRAY_A);
	        $rs		= (Object)array_merge($aRs,$dRs);
	        unset($dRs);
        }
        unset($aRs);
        empty($rs) && iPHP::throwException('应用程序运行出错.找不到该文章: <b>ID:'. $id.'</b>', 4001);
        
        $categoryApp	= iPHP::app("category");
        $category		= $categoryApp->category($rs->cid,false);
        
        if($category['status']==0) return false;
        
        if(iPHP::$iTPLMode=="html" && (strstr($category['contentRule'],'{PHP}')||$category['outurl']||$category['mode']==0)) return false;
    
        
        $_iurlArray	= array((array)$rs,$category);
        $rs->iurl	= iRouter::url('article',$_iurlArray,$page);
        $pageurl	= $rs->iurl->pageurl;
        $rs->url    = $rs->iurl->href;
        $tpl && iCMS::gotohtml($rs->iurl->path,$rs->iurl->href,$category['mode']);
        $picbody	=	preg_replace('/<div\sclass="ke_items">.*?<\/ul>\s*<\/div>/is', '', $rs->body);
        preg_match_all("/<img.*?src\s*=[\"|'|\s]*(http:\/\/.*?\.(gif|jpg|jpeg|bmp|png)).*?>/is",$picbody,$picArray);
        $pA = array_unique($picArray[1]);
        foreach($pA as $key =>$pVal) {
            $rs->photo[]=trim($pVal);
        }
        $body            = explode('#--iCMS.PageBreak--#',$rs->body);
        $pagetotal       = count($body);
        $rs->pagetotal   = $pagetotal+1;
        $rs->body        = iCMS::keywords($body[intval($page-1)]);
        $rs->pagecurrent = $page;
        if($rs->pagetotal>1) {
            $ppHref      = iPHP::page_p2num($pageurl,($page-1>1)?$page-1:1);
            $rs->pagenav = '<a href="'.$ppHref.'" class="prev" target="_self">'.iPHP::lang('iCMS:page:prev').'</a> ';
            for($i=1;$i<=$rs->pagetotal;$i++) {
                $cls=($i==$page)?"current":"page";
                $rs->pagenav.='<a href="'.iPHP::page_p2num($pageurl,$i).'" class="'.$cls.'" target="_self">'.$i.'</a>';
            }
            $npHref      = iPHP::page_p2num($pageurl,(($rs->pagetotal-$page>0)?$page+1:$page));
            $rs->pagenav.='<a href="'.$npHref.'" class="next" target="_self">'.iPHP::lang('iCMS:page:next').'</a>';
        }
       $rs->page = array('total'=>$rs->pagetotal,'ototal'=>$pagetotal,'current'=>$rs->pagecurrent,'nav'=>$rs->pagenav,'prev'=>$ppHref,'next'=>$npHref);
        if($page<$rs->pagetotal){
            $imgA = array_unique($picArray[0]);
            foreach($imgA as $key =>$img){
                $rs->body =str_replace($img,'<p align="center"><a href="'.$npHref.'"><b>'.iPHP::lang('iCMS:article:clicknext').'</b></a></p>
                <p align="center"><a href="'.$npHref.'" title="'.$rs->title.'">'.$_img.'</a></p>',$rs->body);
            }
        }
        if($rs->tags) {
            $tagArray	= explode(',',$rs->tags);
            foreach($tagArray AS $tk=>$tag) {
				$rs->tagArray[$tk]['name']	= $tag;
				$rs->tagArray[$tk]['url']	= iRouter::url('tag',$tag);
				$rs->tagslink.='<a href="'.$rs->tagArray[$tk]['url'].'" class="tags" target="_blank">'.$rs->tagArray[$tk]['name'].'</a> ';
            }
            $_tc	= count($tagArray);
            if($_tc>3){
	            $relTags = array_slice($tagArray,0,3);
            }else{
	            $relTags = $tagArray;
            }
            $relTags = implode(',',$relTags);
        }
        iPHP::assign('relTags',$relTags?$relTags:$category['name']);

        $rs->rel	= $rs->related;
        
        if(strstr($rs->source, '|')){
            list($sourceName,$sourceUrl) = explode('|',$rs->source);
            $rs->source                  = '<a href="'.$sourceUrl.'" target="_blank">'.$sourceName.'</a>';
        }
        
        if($rs->metadata){
        	$rs->meta	= unserialize($rs->metadata);
        	unset($rs->metadata);
        }
        $rs->link	= "<a href='{$rs->url}'>{$rs->title}</a>";

//        $rs->prev=iPHP::lang('iCMS:article:first');
//        $prers=iDB::getRow("SELECT * FROM `#iCMS@__article` WHERE `id` < '{$rs->id}' AND `cid`='{$rs->cid}' AND `status`='1' order by id DESC LIMIT 1;");
//        $prers && $rs->prev='<a href="'.iRouter::url('article',array((array)$prers,$category))->href.'" class="prev" target="_self">'.$prers->title.'</a>';
//        $rs->next=iPHP::lang('iCMS:article:last');
//        $nextrs = iDB::getRow("SELECT * FROM `#iCMS@__article` WHERE `id` > '{$rs->id}'  and `cid`='{$rs->cid}' AND `status`='1' order by id ASC LIMIT 1;");
//        $nextrs && $rs->next='<a href="'.iRouter::url('article',array((array)$nextrs,$category))->href.'" class="next" target="_self">'.$nextrs->title.'</a>';

        $publicURL   = iCMS::$config['router']['publicURL'];
        $rs->comment = array('url'=>$publicURL."/api.php?app=comment&iid={$rs->id}&cid={$rs->cid}",'count'=>$rs->comments);
        if($category['mode']) {
            $rs->script['hits']    = "<script type=\"text/javascript\" src=\"{$publicURL}/api.php?app=article&do=hits&cid={$rs->cid}&id={$rs->id}\"></script>";
            $rs->script['digg']    = "<script type=\"text/javascript\" src=\"{$publicURL}/api.php?app=article&do=digg&id={$rs->id}\"></script>";
            $rs->script['comment'] = "<script type=\"text/javascript\" src=\"{$publicURL}/api.php?app=article&do=comment&id={$rs->id}\"></script>";
        }else {
            iPHP::$iTPLMode!='html' && iDB::query("UPDATE `#iCMS@__article` SET hits=hits+1 WHERE `id` ='{$rs->id}'");
        }
		$rs->pic && $rs->pic_url=iFS::fp($rs->pic,'+http');
        $rs->appid	= iCMS_APP_ARTICLE;
        
        iPHP::assign('article',(array)$rs);
        
        if($tpl) {
            $articletpl	= empty($rs->tpl)?$category['contentTPL']:$rs->tpl;
            strstr($tpl,'.htm') && $articletpl	= $tpl;
            $html	= iCMS::tpl($articletpl,'article');
            if(iPHP::$iTPLMode=="html") return array($html,$rs);
        }
    }
}
