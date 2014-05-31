<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: user.tpl.php 1392 2013-05-20 12:28:08Z coolmoo $
 */
//$iURL.user.bind
return array(
	'/user'           => '/user.php',
	// '/user/login'     => '/user.php?do=login',
	// '/user/logout'    => '/user.php?do=logout',
	// '/user/register'  => '/user.php?do=register',
	// '/user/findpwd'   => '/user.php?do=findpwd',
	// '/user/agreement' => '/user.php?do=agreement',

	'/user/article'  => '/user.php?do=article',
	'/user/category' => '/user.php?do=category',
	'/user/home'     => '/user.php?do=home',
	'/user/favorite' => '/user.php?do=favorite',
	'/user/publish'  => '/user.php?do=publish',

	'/user/manage'           => '/user.php?do=manage',
	'/user/manage/category'  => '/user.php?do=manage&pg=category',
	'/user/manage/favorite'  => '/user.php?do=manage&pg=favorite',
	'/user/manage/share'     => '/user.php?do=manage&pg=share',
	'/user/manage/follower'  => '/user.php?do=manage&pg=follower',
	'/user/manage/following' => '/user.php?do=manage&pg=following',


	'/user/profile'             => '/user.php?do=profile',
	'/user/profile/base'        => '/user.php?do=profile&pg=base',
	'/user/profile/avatar'      => '/user.php?do=profile&pg=avatar',
	'/user/profile/setpassword' => '/user.php?do=profile&pg=setpassword',
	'/user/profile/bind'        => '/user.php?do=profile&pg=bind',
	'/user/profile/custom'      => '/user.php?do=profile&pg=custom',

	'/api'         => '/public/api.php',
	'/api/seccode' => '/public/api.php?app=public&do=seccode',
	'/api/comment' => '/public/api.php?app=comment',

	'/api/user'          => '/public/api.php?app=user',		
	'/api/user/register' => '/public/api.php?app=user&do=register',
	'/api/user/login'    => '/public/api.php?app=user&do=login',
	'/api/user/login/qq' => '/public/api.php?app=user&do=login&sign=qq',
	'/api/user/login/wb' => '/public/api.php?app=user&do=login&sign=wb',
	'/api/user/login/wx' => '/public/api.php?app=user&do=login&sign=wx',
	'/api/user/findpwd'  => '/public/api.php?app=user&do=findpwd',	
	'/api/user/follow'   => '/public/api.php?app=user&do=follow',
	'/api/user/check'    => '/public/api.php?app=user&do=check',

	'/{uid}/'           => '/user.php?do=home&uid={uid}',
	'/{uid}/share/'     => '/user.php?do=share&uid={uid}',
	'/{uid}/favorite/'  => '/user.php?do=favorite&uid={uid}',
	'/{uid}/follower/'  => '/user.php?do=follower&uid={uid}',
	'/{uid}/following/' => '/user.php?do=following&uid={uid}',
	'/{uid}/{cid}/'     => '/user.php?do=home&uid={uid}&cid={cid}',

	'/article/{id}/'	=> '/index.php?do=article&id={id}',
);