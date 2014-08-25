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
defined('iPHP') OR exit('What are you doing?');

return array(
	'profile'=>array(
		'success'     =>'修改完成！',
		'avatar'      =>'头像上传成功！',
		'custom'      =>'更新封面成功！',
		'personstyle' =>'多个标签之间请用逗号隔开',
		'slogan'      =>"随便写点什么，让大家了解你吧。",
		'pskin'       =>"请选择",
		'phair'       =>"请选择",
		'unickEdit'	  =>"你已经修改过昵称了！",
		'nickname'	  =>"昵称已存在,请换个再试试。",
	),
	'follow'=>array(
		'success' =>'已关注！',
		'failure' =>'关注失败！',
		'self'    =>'不能关注自己！',
	),
	'login'=>array(
		'def_uname' =>'邮箱 或 昵称',
		'error'     =>'用户名或者密码错误！',
		'forbidden' =>'系统已关闭登陆功能！',
	),
	'category'=>array(
		'empty'   =>'请输入分类名称!',
		'filter'  =>'分类名称包含被系统屏蔽的字符，请重新填写!',
		'max'     =>'最多只能创建10个分类!',
		'success' =>'更新成功！',
	),
	'article'=>array(
		'add_success'    =>'添加成功!',
		'add_examine'    =>'添加成功!本栏目需要审核后才能正常显示',
		'update_success' =>'更新成功！',
		'update_examine' =>'更新成功！本栏目需要审核后才能正常显示',
	),
	'publish'=>array(
		'filter_title' =>'标题中包含被系统屏蔽的字符，请重新填写。',
		'filter_desc'  =>'简介中包含被系统屏蔽的字符，请重新填写。',
		'filter_body'  =>'内容中包含被系统屏蔽的字符，请重新填写。',
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
		'original'  =>'原密码错误！',
		'modified'  =>'修改完成！',
		'empty'     =>'请填写密码！',
		'new'       =>'请填写新的密码！',
		'rst_empty' =>'请重复输入一次密码！',
		'error'     =>'密码太短啦，至少要6位哦',
		'unequal'   =>'密码与确认密码不一致！',
	),
);
