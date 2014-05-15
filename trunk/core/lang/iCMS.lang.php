<?php
/**
* iPHP - i PHP Framework
* Copyright (c) 2012 iiiphp.com. All rights reserved.
*
* @author coolmoo <iiiphp@qq.com>
* @site http://www.iiiphp.com
* @licence http://www.iiiphp.com/license
* @version 1.0.1
* $Id: define.php 2393 2014-04-09 13:14:23Z coolmoo $
*/
defined('iPHP') OR exit('Access Denied');
return array(
	'error'=>'哎呀呀呀！非常抱歉,居然出错了！<br />请稍候再试试,我们的程序猿正在努力修复中...',
	'page'=>array(
		'index'   =>'首页',
		'prev'    =>'上一页',
		'next'    =>'下一页',
		'last'    =>'末页',
		'other'   =>'共',
		'unit'    =>'页',
		'list'    =>'篇文章',
		'sql'     =>'条记录',
		'tag'     =>'个标签',
		'comment' =>'条评论',
		'message' =>'条留言'
	),
	'article'=>array(
		'first'     =>'已经是第一篇',
		'last'      =>'已经是最后一篇',
		'clicknext' =>'点击图片进入下一页',
		'empty_id'	=>'亲!不好意思,居然出错了！',
		'!good' =>'亲!您已经点过赞了啦 ！',
		'good'  =>'亲!谢谢你的赞',
	),
	'comment'=> array(
		'empty'=>'请输入内容！',
		//'error'=>'验证码不对哦!认真看清楚!要不换一张试试。',
		'success'=>'评论成功!'
	),
	'seccode'=> array(
		'empty'=>'请输入验证码！',
		'error'=>'验证码不对哦!认真看清楚!要不换一张试试。',
	),
	//导航
	'navTag'=>' »  ',
);