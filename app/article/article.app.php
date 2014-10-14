<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: article.app.php 2408 2014-04-30 18:58:23Z coolmoo $
 */
class articleApp {
	public $methods	= array('iCMS','article','hits','good','bad','like_comment','comment');
    public function __construct() {}

    public function do_iCMS($a = null) {
    	return $this->article((int)$_GET['id'],isset($_GET['p'])?(int)$_GET['p']:1);
    }
    public function API_hits($id = null){
        $id===null && $id = (int)$_GET['id'];
        if($id){
            $sql = iCMS::hits_sql();
            iDB::query("UPDATE `#iCMS@__article` SET {$sql} WHERE `id` ='$id'");
        }
    }
    public function API_good(){
        $this->vote('good');
    }
    public function API_bad(){
        $this->vote('bad');
    }
    private function vote($_do){
        iPHP::app('user.class','static');
        user::get_cookie() OR iPHP::code(0,'iCMS:!login',0,'json');

        $aid = (int)$_GET['iid'];
        $aid OR iPHP::code(0,'iCMS:article:empty_id',0,'json');

        $ackey = 'article_'.$_do.'_'.$aid;
        $vote  = (int)iPHP::get_cookie($ackey);
        $vote && iPHP::code(0,'iCMS:article:!'.$_do,0,'json');

        if($_do=='good'){
            $sql = '`good`=good+1';
        }else{
            $sql = '`bad`=bad+1';
        }
        //empty($sql) && iPHP::code(0,'iCMS:error',0,'json');

        iDB::query("UPDATE `#iCMS@__article` SET {$sql} WHERE `id` ='{$aid}' limit 1");
        iPHP::set_cookie($ackey,user::$userid,86400);
        iPHP::code(1,'iCMS:article:'.$_do,0,'json');

    }
    public function article($id,$page=1,$tpl=true){
        $article = iDB::row("SELECT * FROM `#iCMS@__article` WHERE id='".(int)$id."' AND `status` ='1' LIMIT 1;",ARRAY_A);
        if($article['url']) {
            if(iPHP::$iTPL_MODE=="html") {
                return false;
            }else {
            	$this->API_hits($id);
                iPHP::gotourl($article['url']);
            }
        }
        $article && $article_data = iDB::row("SELECT body,subtitle FROM `#iCMS@__article_data` WHERE aid='".(int)$id."' LIMIT 1;",ARRAY_A);

        empty($article) && iPHP::throwException('运行出错！找不到文章: <b>ID:'. $id.'</b>', 10001);
        $vars = array(
            'tags'          =>true,
            'user'          =>true,
            'meta'          =>true,
            'prev_next'     =>true,
            'category_lite' =>false,
        );
        $article = $this->value($article,$article_data,$vars,$page,$tpl);

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
    public function value($article,$art_data="",$vars=array(),$page=1,$tpl=false){

        $article['appid'] = iCMS_APP_ARTICLE;

        // $categoryApp = iPHP::app("category");
        // $category    = $categoryApp->category($article['cid'],false);
        $category = iCache::get('iCMS/category/'.$article['cid']);

        if($category['status']==0) return false;

        if(iPHP::$iTPL_MODE=="html" && $tpl && (strstr($category['contentRule'],'{PHP}')||$category['outurl'])) return false;

        $_iurlArray      = array($article,$category);
        $article['iurl'] = iURL::get('article',$_iurlArray,$page);
        $article['url']  = $article['iurl']->href;
        $article['link'] = "<a href='{$article['url']}'>{$article['title']}</a>";

        ($tpl && $category['mode']=='1') && iCMS::gotohtml($article['iurl']->path,$article['iurl']->href);

        if($vars['category_lite']){
            $article['category'] = iCMS::get_category_lite($category);
        }else{
            $article['category'] = $category;
        }

        if($art_data){
            $pageurl = $article['iurl']->pageurl;
            $art_data['body'] = $this->ubb($art_data['body']);

            preg_match_all("/<img.*?src\s*=[\"|'|\s]*(http:\/\/.*?\.(gif|jpg|jpeg|bmp|png)).*?>/is",$art_data['body'],$pic_array);
            $p_array = array_unique($pic_array[1]);
            if($p_array)foreach($p_array as $key =>$_pic) {
                $article['pics'][$key] = trim($_pic);
            }
            $body     = explode('#--iCMS.PageBreak--#',$art_data['body']);
            $count    = count($body);
            $total    = $count+1;
            $article['body']     = $this->keywords($body[intval($page-1)]);
            $article['subtitle'] = $art_data['subtitle'];
            unset($art_data);

            if($total>1) {
                $flag    = 0;
                $num_nav = '';
                for($i=$page-3;$i<=$page-1;$i++) {
                    if($i<1) continue;
                    $num_nav.="<a href='".iPHP::p2num($pageurl,$i)."' target='_self'>$i</a>";
                    $flag++;
                }
                $num_nav.='<span class="current">'.$page.'</span>';
                for($i=$page+1;$i<=$total;$i++) {
                    $num_nav.="<a href='".iPHP::p2num($pageurl,$i)."' target='_self'>$i</a>";
                    $flag++;
                    if($flag==6)break;
                }

                $index_nav = '<a href="'.$article['url'].'" class="first" target="_self">'.iPHP::lang('iCMS:page:index').'</a>';
                $prev_url  = iPHP::p2num($pageurl,($page-1>1)?$page-1:1);
                $prev_nav  = '<a href="'.$prev_url.'" class="prev" target="_self">'.iPHP::lang('iCMS:page:prev').'</a>';
                $next_url  = iPHP::p2num($pageurl,(($total-$page>0)?$page+1:$page));
                $next_nav  ='<a href="'.$next_url.'" class="next" target="_self">'.iPHP::lang('iCMS:page:next').'</a>';
                $end_nav   ='<a href="'.iPHP::p2num($pageurl,$total).'" class="end" target="_self">共'.$total.'页</a>';
                $text_nav  = $index_nav.$prev_nav.'<span class="current">第'.$page.'页</span>'.$next_nav.$end_nav;
                $pagenav   = $index_nav.$prev_nav.$num_nav.$next_nav.$end_nav;
            }
            $article['page'] = array(
                'total'   => $total,
                'count'   => $count,
                'current' => $page,
                'num'     => $num_nav,
                'text'    => $text_nav,
                'nav'     => $pagenav,
                'prev'    => $prev_url,
                'next'    => $next_url,
                'end'     => ($total==$page?true:false)
            );
            unset($index_nav,$prev_nav,$num_nav,$next_nav,$end_nav,$pagenav);
            if($page<$total){
                $img_array = array_unique($pic_array[0]);
                foreach($img_array as $key =>$img){
                    $article['body'] = str_replace($img,'<p align="center"><a href="'.$next_url.'"><b>'.iPHP::lang('iCMS:article:clicknext').'</b></a></p>
                    <p align="center"><a href="'.$next_url.'" title="'.$article['title'].'">'.$img.'</a></p>',$article['body']);
                }
            }
        }

        if($vars['prev_next']){
            $article['prev'] = iPHP::lang('iCMS:article:first');
            $article['next'] = iPHP::lang('iCMS:article:last');
            $prers = iDB::row("SELECT * FROM `#iCMS@__article` WHERE `id` < '{$article['id']}' AND `cid`='{$article['cid']}' AND `status`='1' order by id DESC LIMIT 1;");
            $prers && $article['prev']  = '<a href="'.iURL::get('article',array((array)$prers,$category))->href.'" class="prev" target="_self">'.$prers->title.'</a>';
            $nextrs = iDB::row("SELECT * FROM `#iCMS@__article` WHERE `id` > '{$article['id']}'  and `cid`='{$article['cid']}' AND `status`='1' order by id ASC LIMIT 1;");
            $nextrs && $article['next'] = '<a href="'.iURL::get('article',array((array)$nextrs,$category))->href.'" class="next" target="_self">'.$nextrs->title.'</a>';
        }

        if($vars['tags']){
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
        }

        if($vars['meta']){
            if($article['metadata']){
                $article['meta'] = unserialize($article['metadata']);
                unset($article['metadata']);
            }
        }
        if($vars['user']){
            iPHP::app('user.class','static');
            if($article['postype']){
                $article['user'] = user::empty_info($article['userid'],'#'.$article['editor']);
            }else{
                $article['user'] = user::info($article['userid'],$article['author']);
            }
        }


        if(strstr($article['source'], '|')){
            list($s_name,$s_url) = explode('|',$article['source']);
            $article['source']   = '<a href="'.$s_url.'" target="_blank">'.$s_name.'</a>';
        }

        $article['hits'] = array(
            'script' => iCMS_API.'?app=article&do=hits&cid='.$article['cid'].'&id='.$article['id'],
            'count'  => $article['hits']
        );
        $article['comment'] = array(
            'url'   => iCMS_API."?app=comment&do=article&appid={$article['appid']}&iid={$article['id']}&cid={$article['cid']}",
            'count' => $article['comments']
        );

        $article['picdata'] && $article['picdata'] = unserialize($article['picdata']);
        $article['pic']   = get_pic($article['pic']);
        $article['mpic']  = get_pic($article['mpic']);
        $article['spic']  = get_pic($article['spic']);
        $article['param'] = array(
            "appid" => $article['appid'],
            "iid"   => $article['id'],
            "cid"   => $article['cid'],
            "suid"  => $article['userid'],
            "title" => $article['title'],
            "url"   => $article['url']
        );

        return $article;
    }
    private function ubb($content){
        if(strpos($content, '[img]')!==false){
            $content = stripslashes($content);
            preg_match_all("/\[img\][\"|'|\s]*(http:\/\/.*?\.(gif|jpg|jpeg|bmp|png))\[\/img\]/is",$content,$img_array);
            if($img_array[1]){
                foreach ($img_array[1] as $key => $src) {
                    $imgs[$key] = '<p><img src="'.$src.'" /></p>';
                }
                $content = str_replace($img_array[0],$imgs, $content);
            }
        }
        return $content;
    }
    //内链
    private static function keywords($content) {
        if(iCMS::$config['other']['kwCount']==0) return $content;

        $keywords   = iCache::get('iCMS/keywords');
        if($keywords){
            foreach($keywords AS $i=>$val) {
                if($val['times']>0) {
                    $search[]  = $val['keyword'];
                    $replace[] = '<a class="keyword" target="_blank" href="'.$val['url'].'">'.$val['keyword'].'</a>';
                }
           }
           return iCMS::str_replace_limit($search, $replace,stripslashes($content),iCMS::$config['other']['kwCount']);
        }
        return $content;
    }
}
