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
	'profile'=>array(
		'success' =>'修改完成！',
		'avatar'  =>'头像上传成功！'
	),
	'follow'=>array(
		'success'=>'已关注！',
		'failure'=>'关注失败！',
		'self'=>'不能关注自己！',
	),	
	'login'=>array(
		'def_uname'=>'邮箱 或 昵称',
		'error'=>'用户名或者密码错误！',
		'forbidden'=>'系统已关闭登陆功能！',
	),

	'register' => array(
		'forbidden'=>'系统已经关闭注册功能！',
		'nickname'=> array(
			'empty'=>'请填写昵称！',			
			'error'=>'昵称只能4~20位，每个中文字算2位字符。',
			'exist'=>'昵称已经被注册了,请换个再试试。',
		),
		'username'=> array(
			'empty'=>'请填写电子邮箱！',			
			'error'=>'电子邮箱格式不正确！',
			'exist'=>'邮件地址已经注册过了,请直接登陆或者换个邮件再试试。',
		),
	),
	'password'=> array(
		'original'=>'原密码错误！',
		'modified'=>'修改完成！',
		'empty'=>'请填写密码！',
		'new'=>'请填写新的密码！',
		'rst_empty'=>'请重复输入一次密码！',
		'error'=>'密码太短啦，至少要6位哦',
		'unequal'=>'密码与确认密码不一致！',
	),
);