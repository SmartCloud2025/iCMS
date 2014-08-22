<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: article.app.php 2408 2014-04-30 18:58:23Z coolmoo $
 */
class articleApp {
	public $methods	= array('iCMS','good','like_comment','comment');
    function __construct() {
        $this->userid   = (int)iPHP::get_cookie('userid');
        $this->nickname = iS::escapeStr(iPHP::getUniCookie('nickname'));
    }
    public function do_iCMS($a = null) {
    	return $this->article((int)$_GET['id'],isset($_GET['p'])?(int)$_GET['p']:1);
    }
    public function API_good(){
        $aid = (int)$_GET['iid'];
        $aid OR iPHP::code(0,'iCMS:article:empty_id',0,'json');
        $ackey = 'article_good_'.$aid;
        $good  = (int)iPHP::get_cookie($ackey);
        $good && iPHP::code(0,'iCMS:article:!good',0,'json');
        iDB::query("UPDATE `#iCMS@__article` SET `good`=good+1 WHERE `id` ='{$aid}' limit 1");
        iPHP::set_cookie($ackey,$this->userid,86400);
        iPHP::code(1,'iCMS:article:good',0,'json');
    }

    public function article($id,$page=1,$tpl=true){
        $aRs = iDB::row("SELECT * FROM `#iCMS@__article` WHERE id='".(int)$id."' AND `status` ='1' LIMIT 1;",ARRAY_A);
        if($aRs['url']) {
            if(iPHP::$iTPL_mode=="html") {
                return false;
            }else {
            	iDB::query("UPDATE `#iCMS@__article` SET hits=hits+1 WHERE `id` ='$id'");
                iPHP::gotourl($aRs['url']);
            }
        }
        if($aRs){
            $dRs = iDB::row("SELECT body,subtitle FROM `#iCMS@__article_data` WHERE aid='".(int)$id."' LIMIT 1;",ARRAY_A);
            $rs  = (Object)array_merge($aRs,$dRs);
	        unset($dRs);
        }
        unset($aRs);
        empty($rs) && iPHP::throwException('应用程序运行出错.找不到该文章: <b>ID:'. $id.'</b>', 4001);

        $categoryApp	= iPHP::app("category");
        $category		= $categoryApp->category($rs->cid,false);

        if($category['status']==0) return false;

        if(iPHP::$iTPL_mode=="html" && (strstr($category['contentRule'],'{PHP}')||$category['outurl']||$category['mode']==0)) return false;

        $_iurlArray = array((array)$rs,$category);
        $rs->iurl   = iURL::get('article',$_iurlArray,$page);
        $pageurl    = $rs->iurl->pageurl;
        $rs->url    = $rs->iurl->href;
        $tpl && iCMS::gotohtml($rs->iurl->path,$rs->iurl->href,$category['mode']);
        //$picbody	=	preg_replace('/<div\sclass="ke_items">.*?<\/ul>\s*<\/div>/is', '', $rs->body);
        preg_match_all("/<img.*?src\s*=[\"|'|\s]*(http:\/\/.*?\.(gif|jpg|jpeg|bmp|png)).*?>/is",$rs->body,$pic_array);
        $photo_array = array_unique($pic_array[1]);
        if($photo_array)foreach($photo_array as $key =>$pVal) {
            $rs->photo[]=trim($pVal);
        }
        $body     = explode('#--iCMS.PageBreak--#',$rs->body);
        $count    = count($body);
        $total    = $count+1;
        $rs->body = iCMS::keywords($body[intval($page-1)]);
        $current  = $page;
        if($total>1) {
            $rs->pagenav = '<a href="'.$rs->url.'" class="first" target="_self">'.iPHP::lang('iCMS:page:index').'</a>';
            $rs->pagenav.= '<a href="'.iPHP::p2num($pageurl,($page-1>1)?$page-1:1).'" class="prev" target="_self">'.iPHP::lang('iCMS:page:prev').'</a>';
            $flag=0;
            for($i=$page-3;$i<=$page-1;$i++) {
                if($i<1) continue;
                $rs->pagenav.="<a href='".iPHP::p2num($pageurl,$i)."' target='_self'>$i</a>";
                $flag++;
            }
            $rs->pagenav.='<span class="current">'.$page.'</span>';
            for($i=$page+1;$i<=$total;$i++) {
                $rs->pagenav.="<a href='".iPHP::p2num($pageurl,$i)."' target='_self'>$i</a>";
                $flag++;
                if($flag==6)break;
            }
            $next_url    = iPHP::p2num($pageurl,(($total-$page>0)?$page+1:$page));
            $rs->pagenav.='<a href="'.$next_url.'" class="next" target="_self">'.iPHP::lang('iCMS:page:next').'</a>';
            $rs->pagenav.='<a href="'.iPHP::p2num($pageurl,$total).'" class="end" target="_self">共'.$total.'页</a>';
        }
       $rs->page = array('total'=>$total,'count'=>$count,'current'=>$current,'nav'=>$rs->pagenav,'prev'=>$ppHref,'next'=>$npHref);
        if($page<$total){
            $img_array = array_unique($pic_array[0]);
            foreach($img_array as $key =>$img){
                $rs->body =str_replace($img,'<p align="center"><a href="'.$next_url.'"><b>'.iPHP::lang('iCMS:article:clicknext').'</b></a></p>
                <p align="center"><a href="'.$next_url.'" title="'.$rs->title.'">'.$img.'</a></p>',$rs->body);
            }
        }
        if($rs->tags) {
            $tagArray	= explode(',',$rs->tags);
            foreach($tagArray AS $tk=>$tag) {
				$rs->tagArray[$tk]['name']	= $tag;
				$rs->tagArray[$tk]['url']	= iURL::get('tag',$tag);
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
        $rs->relTags = $relTags?$relTags:$category['name'];
        $rs->rel     = $rs->related;

        if(strstr($rs->source, '|')){
            list($sourceName,$sourceUrl) = explode('|',$rs->source);
            $rs->source                  = '<a href="'.$sourceUrl.'" target="_blank">'.$sourceName.'</a>';
        }

        if($rs->metadata){
        	$rs->meta	= unserialize($rs->metadata);
        	unset($rs->metadata);
        }
        $rs->link	= "<a href='{$rs->url}'>{$rs->title}</a>";

       $rs->prev            = iPHP::lang('iCMS:article:first');
       $prers               = iDB::row("SELECT * FROM `#iCMS@__article` WHERE `id` < '{$rs->id}' AND `cid`='{$rs->cid}' AND `status`='1' order by id DESC LIMIT 1;");
       $prers && $rs->prev  = '<a href="'.iURL::get('article',array((array)$prers,$category))->href.'" class="prev" target="_self">'.$prers->title.'</a>';
       $rs->next            = iPHP::lang('iCMS:article:last');
       $nextrs              = iDB::row("SELECT * FROM `#iCMS@__article` WHERE `id` > '{$rs->id}'  and `cid`='{$rs->cid}' AND `status`='1' order by id ASC LIMIT 1;");
       $nextrs && $rs->next = '<a href="'.iURL::get('article',array((array)$nextrs,$category))->href.'" class="next" target="_self">'.$nextrs->title.'</a>';

        $rs->comment = array('url'=>iCMS_API."?app=comment&iid={$rs->id}&cid={$rs->cid}",'count'=>$rs->comments);
        if($category['mode']) {
            $rs->script['hits']    = '<script type="text/javascript" src="'.iCMS_API.'?app=article&do=hits&cid='.$rs->cid.'&id='.$rs->id.'"></script>';
            $rs->script['digg']    = '<script type="text/javascript" src="'.iCMS_API.'?app=article&do=digg&id='.$rs->id.'"></script>';
            $rs->script['comment'] = '<script type="text/javascript" src="'.iCMS_API.'?app=article&do=comment&id='.$rs->id.'"></script>';
        }else {
            iPHP::$iTPL_mode!='html' && iDB::query("UPDATE `#iCMS@__article` SET hits=hits+1 WHERE `id` ='{$rs->id}'");
        }
        $rs->pic   = get_pic($rs->pic);
        $rs->mpic  = get_pic($rs->mpic);
        $rs->spic  = get_pic($rs->spic);
        $rs->appid = iCMS_APP_ARTICLE;

        // iCMS::hooks('article',array(
        //     'appid' => $rs->appid,
        //     'cid'   => $rs->cid,
        //     'ctype' => $rs->cid,
        //     'iid'   => $rs->id,
        //     'title' => $rs->title,
        // ));
        iPHP::assign('article',(array)$rs);

        if($tpl) {
            $articletpl	= empty($rs->tpl)?$category['contentTPL']:$rs->tpl;
            strstr($tpl,'.htm') && $articletpl	= $tpl;
            $html	= iPHP::view($articletpl,'article');
            if(iPHP::$iTPL_mode=="html") return array($html,$rs);
        }
    }
}
