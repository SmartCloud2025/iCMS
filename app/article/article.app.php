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
    function __construct() {}

    public function do_iCMS($a = null) {
    	return $this->article((int)$_GET['id'],isset($_GET['p'])?(int)$_GET['p']:1);
    }
    public function API_good(){
        iPHP::app('user.class','static');
        user::get_cookie() OR iPHP::code(0,'iCMS:!login',0,'json');

        $aid = (int)$_GET['iid'];
        $aid OR iPHP::code(0,'iCMS:article:empty_id',0,'json');
        $ackey = 'article_good_'.$aid;
        $good  = (int)iPHP::get_cookie($ackey);
        $good && iPHP::code(0,'iCMS:article:!good',0,'json');
        iDB::query("UPDATE `#iCMS@__article` SET `good`=good+1 WHERE `id` ='{$aid}' limit 1");
        iPHP::set_cookie($ackey,user::$userid,86400);
        iPHP::code(1,'iCMS:article:good',0,'json');
    }

    public function article($id,$page=1,$tpl=true){
        $article = iDB::row("SELECT * FROM `#iCMS@__article` WHERE id='".(int)$id."' AND `status` ='1' LIMIT 1;",ARRAY_A);
        if($article['url']) {
            if(iPHP::$iTPL_MODE=="html") {
                return false;
            }else {
            	iDB::query("UPDATE `#iCMS@__article` SET hits=hits+1 WHERE `id` ='$id'");
                iPHP::gotourl($article['url']);
            }
        }
        $article && $article_data = iDB::row("SELECT body,subtitle FROM `#iCMS@__article_data` WHERE aid='".(int)$id."' LIMIT 1;",ARRAY_A);

        empty($article) && iPHP::throwException('运行出错！找不到文章: <b>ID:'. $id.'</b>', 10001);

        $article = $this->value($article, $article_data,true,$page,$tpl);

        if($article===false) return false;

        unset($article_data);

        if($tpl) {
            iCMS::hooks('enable_comment',true);
            iPHP::assign('article',$article);
            iPHP::assign('category',$article['category']);
            $article_tpl = empty($article['tpl'])?$article['category']['contentTPL']:$article['tpl'];
            strstr($tpl,'.htm') && $article_tpl	= $tpl;
            $html	= iPHP::view($article_tpl,'article');
            if(iPHP::$iTPL_MODE=="html") return array($html,$article);
        }else{
            return $article;
        }
    }
    public function value($article,$art_data="",$prev_next=false,$page=1,$tpl=false){

        // $categoryApp = iPHP::app("category");
        // $category    = $categoryApp->category($article['cid'],false);
        $category = iCache::get('iCMS/category/'.$article['cid']);

        if($category['status']==0) return false;

        if(iPHP::$iTPL_MODE=="html" && (strstr($category['contentRule'],'{PHP}')||$category['outurl']||$category['mode']==0)) return false;

        $_iurlArray      = array($article,$category);
        $article['iurl'] = iURL::get('article',$_iurlArray,$page);
        $article['url']  = $article['iurl']->href;
        $article['link'] = "<a href='{$article['url']}'>{$article['title']}</a>";
        $tpl && iCMS::gotohtml($article['iurl']->path,$article['iurl']->href,$category['mode']);
        $article['category'] = $category;
        if($art_data){
            $pageurl = $article['iurl']->pageurl;
            preg_match_all("/<img.*?src\s*=[\"|'|\s]*(http:\/\/.*?\.(gif|jpg|jpeg|bmp|png)).*?>/is",$art_data['body'],$pic_array);
            $photo_array = array_unique($pic_array[1]);
            if($photo_array)foreach($photo_array as $key =>$pVal) {
                $article['photo'][$key] = trim($pVal);
            }
            $body     = explode('#--iCMS.PageBreak--#',$art_data['body']);
            $count    = count($body);
            $total    = $count+1;
            $article['body']     = iCMS::keywords($body[intval($page-1)]);
            $article['subtitle'] = $art_data['subtitle'];
            unset($art_data);

            if($total>1) {
                $article['pagenav'] = '<a href="'.$article['url'].'" class="first" target="_self">'.iPHP::lang('iCMS:page:index').'</a>';
                $article['pagenav'].= '<a href="'.iPHP::p2num($pageurl,($page-1>1)?$page-1:1).'" class="prev" target="_self">'.iPHP::lang('iCMS:page:prev').'</a>';
                $flag=0;
                for($i=$page-3;$i<=$page-1;$i++) {
                    if($i<1) continue;
                    $article['pagenav'].="<a href='".iPHP::p2num($pageurl,$i)."' target='_self'>$i</a>";
                    $flag++;
                }
                $article['pagenav'].='<span class="current">'.$page.'</span>';
                for($i=$page+1;$i<=$total;$i++) {
                    $article['pagenav'].="<a href='".iPHP::p2num($pageurl,$i)."' target='_self'>$i</a>";
                    $flag++;
                    if($flag==6)break;
                }
                $next_url    = iPHP::p2num($pageurl,(($total-$page>0)?$page+1:$page));
                $article['pagenav'].='<a href="'.$next_url.'" class="next" target="_self">'.iPHP::lang('iCMS:page:next').'</a>';
                $article['pagenav'].='<a href="'.iPHP::p2num($pageurl,$total).'" class="end" target="_self">共'.$total.'页</a>';
            }
            $article['page'] = array(
                'total'   => $total,
                'count'   => $count,
                'current' => $page,
                'nav'     => $article['pagenav'],
                'prev'    => $ppHref,
                'next'    => $npHref,
                'end'     => ($total==$page?true:false)
            );
            if($page<$total){
                $img_array = array_unique($pic_array[0]);
                foreach($img_array as $key =>$img){
                    $article['body'] = str_replace($img,'<p align="center"><a href="'.$next_url.'"><b>'.iPHP::lang('iCMS:article:clicknext').'</b></a></p>
                    <p align="center"><a href="'.$next_url.'" title="'.$article['title'].'">'.$img.'</a></p>',$article['body']);
                }
            }
        }
        if($article['tags']) {
            $tagApp   = iPHP::app("tag");
            $tagArray = $tagApp->decode($article['tags']);
            $article['tags'] = array();
            foreach((array)$tagArray AS $tk=>$tag) {
                $article['tags'][$tk]['name'] = $tag['name'];
                $article['tags'][$tk]['url']  = $tag['url'];
                $article['tags'][$tk]['link'] = $tag['link'];
                $article['tags_link'].= $tag['link'];
            }
            $_tc = count($tagArray);
            if($_tc>3){
                $relTags = array_slice($article['tags'],0,3);
            }else{
                $relTags = $article['tags'];
            }
            $relTags = implode(',',$relTags);
            unset($tagApp,$tagArray);
        }
        $article['relTags'] = $relTags?$relTags:$category['name'];
        $article['rel']     = $article['related'];

        if(strstr($article['source'], '|')){
            list($s_name,$s_url) = explode('|',$article['source']);
            $article['source']   = '<a href="'.$s_url.'" target="_blank">'.$s_name.'</a>';
        }

        if($article['metadata']){
            $article['meta'] = unserialize($article['metadata']);
            unset($article['metadata']);
        }
        if($prev_next){
            $article['prev'] = iPHP::lang('iCMS:article:first');
            $article['next'] = iPHP::lang('iCMS:article:last');
            $prers = iDB::row("SELECT * FROM `#iCMS@__article` WHERE `id` < '{$article['id']}' AND `cid`='{$article['cid']}' AND `status`='1' order by id DESC LIMIT 1;");
            $prers && $article['prev']  = '<a href="'.iURL::get('article',array((array)$prers,$category))->href.'" class="prev" target="_self">'.$prers->title.'</a>';
            $nextrs = iDB::row("SELECT * FROM `#iCMS@__article` WHERE `id` > '{$article['id']}'  and `cid`='{$article['cid']}' AND `status`='1' order by id ASC LIMIT 1;");
            $nextrs && $article['next'] = '<a href="'.iURL::get('article',array((array)$nextrs,$category))->href.'" class="next" target="_self">'.$nextrs->title.'</a>';
        }

        $article['comment'] = array(
            'url'   => iCMS_API."?app=comment&iid={$article['id']}&cid={$article['cid']}",
            'count' => $article['comments']
        );
        if($category['mode']) {
            $article['script'] = array(
                'hits'    => '<script type="text/javascript" src="'.iCMS_API.'?app=article&do=hits&cid='.$article['cid'].'&id='.$article['id'].'"></script>',
                'digg'    => '<script type="text/javascript" src="'.iCMS_API.'?app=article&do=digg&id='.$article['id'].'"></script>',
                'comment' => '<script type="text/javascript" src="'.iCMS_API.'?app=article&do=comment&id='.$article['id'].'"></script>',
            );
        }else {
            iPHP::$iTPL_MODE!='html' && iDB::query("UPDATE `#iCMS@__article` SET hits=hits+1 WHERE `id` ='{$article['id']}'");
        }
        $article['pic']   = get_pic($article['pic']);
        $article['mpic']  = get_pic($article['mpic']);
        $article['spic']  = get_pic($article['spic']);
        $article['appid'] = iCMS_APP_ARTICLE;
        return $article;
    }

}
