<?php
/**
* iPHP - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.0.0
* @$Id: iPages.class.php 1608 2013-06-13 06:45:35Z coolmoo $
*/
//$GLOBALS['iPage']['url']="/index_";
//$GLOBALS['iPage']['html']['enable']=true;
class iPages {
	/**
	* config ,public
	*/
	public $page_name = "page";//page标签，用来控制url页。比如说xxx.php?page=2中的page
	public $is_ajax   = false;//是否支持AJAX分页模式 

	/**
	* private
	*
	*/ 
	public $pagebarnum =8;//控制记录条的个数。
	public $totalpage  =0;//总页数
	public $ajax_fun   ='';//AJAX动作名
	public $nowindex   =1;//当前页
	public $url        ="";//url地址头
	public $offset     =0;
	public $config     ='';
	public $lang       ='';
	/**
	* constructor构造函数
	*
	* @param array $array['total'],$array['perpage'],$array['pn'],$array['unit'],$array['nowindex'],$array['url'],$array['ajax'],$array['pnName']...
	*/
	function __construct($_config,$lang=null){
 		array_key_exists('total',$_config) OR $this->error('need a param of total',1001);
		$this->total     = (int)$_config['total'];
		$nowindex        = $_config['nowindex']?(int)$_config['nowindex']:1;
		$this->perpage   = $_config['perpage']?(int)$_config['perpage']:10;
		$url             = isset($_config['url'])?$_config['url']:($GLOBALS['iPage']['url']?$GLOBALS['iPage']['url']:$_SERVER['REQUEST_URI']);
		$this->totalpage = ceil($this->total/$this->perpage);
		$GLOBALS['iPage']['total'] = (int)$this->totalpage;	
		if($this->totalpage<1) return;
		$_config['page_name'] && $this->set('page_name',$_config['page_name']);//设置pagename
		$this->html          = $GLOBALS['iPage']['html'];
		$this->lang          = array('index'=>'INDEX','prev'=>'PREV','next'=>'NEXT','last'=>'LAST','other'=>'Total','unit'=>'Page','list'=>'Articles','sql'=>'Records','tag'=>'Tags','comment'=>'Comments','message'=>'Messages');
		$lang && $this->lang = $lang;
		$this->unit          = $_config['unit']?$_config['unit']:$this->lang['sql'];
		$this->_set_nowindex($nowindex);//设置当前页
		$this->_set_url($url);//设置链接地址
		$this->nowindex      = min($this->totalpage,$this->nowindex);
		$this->offset        = (int)($this->nowindex-1<0?0:$this->nowindex-1)*$this->perpage;
		$_config['ajax'] && $this->ajax($_config['ajax']);//打开AJAX模式
	}

	/**
	* 设定类中指定变量名的值，如果改变量不属于这个类，将throw一个exception
	*
	* @param string $var
	* @param string $value
	*/
	function set($var,$value){
		if(in_array($var,get_object_vars($this)))
	 		$this->$var=$value;
		else
			$this->error("does not belong to PB_Page!",1002);
	}
	function get($var){
		if(in_array($var,get_object_vars($this)))
	 		return $this->$var;
		else
			$this->error(" does not belong to PB_Page!",1003);
	}
	
	/**
	* 打开倒AJAX模式
	*
	* @param string $action 默认ajax触发的动作。
	*/
	function ajax($action){
		$this->is_ajax  = true;
		$this->ajax_fun = $action;
	}
	
	
	/**
	* 获取显示"下一页"的代码
	* 
	* @param string $style
	* @return string
	*/
	function next_page($style='next_page',$target='_self'){
		if($this->nowindex<$this->totalpage){
			return $this->_get_link($this->_get_url($this->nowindex+1),$this->lang['next'],$style,$target);
		}
		return '<span class="'.$style.'">'.$this->lang['next'].'</span>';
	}

	/**
	* 获取显示“上一页”的代码
	*
	* @param string $style
	* @return string
	*/
	function pre_page($style='pre_page',$target='_self'){
		if($this->nowindex>1){
			return $this->_get_link($this->_get_url($this->nowindex-1),$this->lang['prev'],$style,$target);
		}
		return '<span class="'.$style.'">'.$this->lang['prev'].'</span>';
	}

	/**
	* 获取显示“首页”的代码
	*
	* @return string
	*/
	function first_page($style='index_page',$target='_self'){
		if($this->nowindex==1){
	  		return '<span class="'.$style.'">'.$this->lang['index'].'</span>';
		}
		return $this->_get_link($this->_get_url(1),$this->lang['index'],$style,$target);
	}

	/**
	* 获取显示“尾页”的代码
	*
	* @return string
	*/
	function last_page($style='last_page',$target='_self'){
		if($this->nowindex==$this->totalpage){
	 		 return '<span class="'.$style.'">'.$this->lang['last'].'</span>';
		}
		return $this->_get_link($this->_get_url($this->totalpage),$this->lang['last'],$style,$target);
	}

	function nowbar($style='',$nowindex_style='page_nowindex',$target='_self'){
		$plus=ceil($this->pagebarnum/2);
		if($this->pagebarnum-$plus+$this->nowindex>$this->totalpage)
			$plus=($this->pagebarnum-$this->totalpage+$this->nowindex);
		$begin=$this->nowindex-$plus+1;
		$begin=($begin>=1)?$begin:1;
		$return='';
		for($i=$begin;$i<$begin+$this->pagebarnum;$i++){
			if($i<=$this->totalpage){
				if($i!=$this->nowindex)
		    		$return.=$this->_get_text($this->_get_link($this->_get_url($i),$i,$style,$target));
				else 
		    	$return.=$this->_get_text('<span class="'.$nowindex_style.'">'.$i.'</span>');
			}else{
				break;
			}
			$return.="\n";
		}
		unset($begin);
		return $return;
	}
	
	/**
	* 获取显示跳转按钮的代码
	*
	* @return string
	*/
	function select($style='page_select'){
		$return='<select class="'.$style.'" name="Page_Select" onchange="window.location.href=this.value">';
		for($i=1;$i<=$this->totalpage;$i++){
			$url = $this->_get_url($i);
			if($i==$this->nowindex){
				$return.='<option value="'.$url.'" selected>'.$i.'</option>';
			}else{
				$return.='<option value="'.$url.'">'.$i.'</option>';
			}
		}
		unset($i);
		$return.='</select>';
		return $return;
	}
	//文字 说明
	function bartext($style='bartext'){
		return '<span class="'.$style.'">'.$this->lang['other'].$this->total.$this->unit.' '.$this->totalpage.$this->lang['unit'].'</span>';
//		return '<span class="'.$style.'">'.$this->lang['other'].$this->total.$this->unit.'，'.$this->perpage.$this->unit.'/'.$this->lang['unit'].' '.$this->lang['other'].$this->totalpage.$this->lang['unit'].'</span>';
	}

	/**
	* 获取mysql 语句中limit需要的值
	*
	* @return string
	*/
	function offset(){
		return $this->offset;
	}

	/**
	* 控制分页显示风格（你可以增加相应的风格）
	*
	* @param int $mode
	* @return string
	*/
	function show($mode=0){
		switch ($mode){
			case '1':
				return $this->pre_page().$this->nowbar().$this->next_page().$this->lang['di'].$this->select().$this->lang['unit'];
				break;
			case '2':
				return $this->first_page().$this->pre_page().$this->lang['format_left'].$this->lang['di'].$this->nowindex.$this->lang['unit'].$this->lang['format_right'].$this->next_page().$this->last_page().$this->lang['di'].$this->select().$this->lang['unit'];
				break;
			case '3':
				return $this->first_page().$this->pre_page().$this->nowbar().$this->next_page().$this->last_page();
				break;
			case '4':
				return $this->pre_page().$this->nowbar().$this->next_page();
				break;
			case '5':
				return $this->nowbar();
				break;
			case '6':
				return $this->pre_page().$this->next_page();
				break;
			default:
				return $this->first_page().$this->pre_page().$this->nowbar().$this->next_page().$this->last_page().$this->bartext();
				break;
		}
	}
/*----------------private function (私有方法)-----------------------------------------------------------*/
	/**
	* 设置url头地址
	* @param: String $url
	* @return boolean
	*/
	function _set_url($url=""){
		if($this->html['enable']){
			$this->url	= $url;
		}else{
			$urlArray	= parse_url($url);
			$query		= $urlArray["query"];
			parse_str($query, $output);
			$output[$this->page_name]="";
			$urlArray["query"]	= http_build_query($output);
			$this->url			= $urlArray["path"]."?".$urlArray["query"].'{P}';
		}
	}
	
	/**
	* 设置当前页面
	*
	*/
	function _set_nowindex($nowindex){
		if(empty($nowindex)){
			//系统获取
			if(isset($_GET[$this->page_name])){
				$this->nowindex=intval($_GET[$this->page_name]);
			}
		}else{
	  		//手动设置
			$this->nowindex=intval($nowindex);
		}
	}

	
	/**
	* 为指定的页面返回地址值
	*
	* @param int $pageno
	* @return string $url
	*/
	function _get_url($pageno=1){
		if($this->is_ajax) return (int)$pageno;
		if($pageno<2){
			$url	= $this->url;
			if(!$this->html['enable']){
				$url	= str_replace(array('?page={P}','&page={P}'),'',$this->url);
			}
			return str_replace('_{P}','',$url);
		}
		return str_replace('{P}',$pageno,$this->url);
	}

	/**
	* 获取分页显示文字，比如说默认情况下_get_text('<a href="">1</a>')将返回[<a href="">1</a>]
	*
	* @param String $str
	* @return string $url
	*/ 
	function _get_text($str){
		return $this->lang['format_left'].$str.$this->lang['format_right'];
	}

	
	/**
	* 获取链接地址
	*/
	function _get_link($url,$text,$style='',$target=''){
		$style	&& $style	= 'class="'.$style.'"';
		$target OR $target	= '_self';
		if($this->is_ajax){
	  		//如果是使用AJAX模式
			return '<a '.$style.' href="javascript:'.$this->ajax_fun.'(\''.$url.'\',this);">'.$text.'</a>';
		}else{
			return '<a '.$style.' href="'.$url.'" target="'.$target.'">'.$text.'</a>';
		}
	}
	
	
	/**
	* 出错处理方式
	*/
	function error($msg,$code){
		trigger_error($msg . '(' . $code . ')');
	}
}
