<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: user.tpl.php 1392 2013-05-20 12:28:08Z coolmoo $
 */
//$iURL.user.bind
defined('iPHP') OR exit('What are you doing?');

return array(
	'/user'                     => iCMS_URL.'/user.php',
	'/user/home'                => iCMS_URL.'/user.php?do=home',
	'/user/publish'             => iCMS_URL.'/user.php?do=manage&pg=publish',
	'/user/article'        		=> iCMS_URL.'/user.php?do=manage&pg=article',
	'/user/category'            => iCMS_URL.'/user.php?do=manage&pg=category',
	'/user/comment'             => iCMS_URL.'/user.php?do=manage&pg=comment',
	'/user/inbox'             	=> iCMS_URL.'/user.php?do=manage&pg=inbox',
	'/user/inbox/{uid}'         => iCMS_URL.'/user.php?do=manage&pg=inbox&uid={uid}',

	'/user/manage'              => iCMS_URL.'/user.php?do=manage',
	'/user/manage/favorite'     => iCMS_URL.'/user.php?do=manage&pg=favorite',
	'/user/manage/share'        => iCMS_URL.'/user.php?do=manage&pg=share',
	'/user/manage/fans'         => iCMS_URL.'/user.php?do=manage&pg=fans',
	'/user/manage/follow'       => iCMS_URL.'/user.php?do=manage&pg=follow',

	'/user/profile'             => iCMS_URL.'/user.php?do=profile',
	'/user/profile/base'        => iCMS_URL.'/user.php?do=profile&pg=base',
	'/user/profile/avatar'      => iCMS_URL.'/user.php?do=profile&pg=avatar',
	'/user/profile/setpassword' => iCMS_URL.'/user.php?do=profile&pg=setpassword',
	'/user/profile/bind'        => iCMS_URL.'/user.php?do=profile&pg=bind',
	'/user/profile/custom'      => iCMS_URL.'/user.php?do=profile&pg=custom',

	'/api'                      => iCMS_API,
	'/api/comment'              => iCMS_API.'?app=comment',
	'/api/seccode'              => iCMS_API.'?app=public&do=seccode',
	'/api/agreement'       		=> iCMS_API.'?app=public&do=agreement',
	'/api/search'               => iCMS_API.'?app=search',

	'/api/user'                 => iCMS_API.'?app=user',
	'/api/user/ucard'           => iCMS_API.'?app=user&do=ucard',
	'/api/user/register'        => iCMS_API.'?app=user&do=register',
	'/api/user/logout'          => iCMS_API.'?app=user&do=logout',
	'/api/user/login'           => iCMS_API.'?app=user&do=login',
	'/api/user/login/qq'        => iCMS_API.'?app=user&do=login&sign=qq',
	'/api/user/login/wb'        => iCMS_API.'?app=user&do=login&sign=wb',
	'/api/user/login/wx'        => iCMS_API.'?app=user&do=login&sign=wx',
	'/api/user/findpwd'         => iCMS_API.'?app=user&do=findpwd',
	'/api/user/follow'          => iCMS_API.'?app=user&do=follow',
	'/api/user/check'           => iCMS_API.'?app=user&do=check',

	'/{uid}/'          => iCMS_URL.'/user.php?do=home&uid={uid}',
	'/{uid}/comment/'  => iCMS_URL.'/user.php?do=comment&uid={uid}',
	'/{uid}/share/'    => iCMS_URL.'/user.php?do=share&uid={uid}',
	'/{uid}/favorite/' => iCMS_URL.'/user.php?do=favorite&uid={uid}',
	'/{uid}/fans/'     => iCMS_URL.'/user.php?do=fans&uid={uid}',
	'/{uid}/follow/'   => iCMS_URL.'/user.php?do=follow&uid={uid}',
	'/{uid}/{cid}/'    => iCMS_URL.'/user.php?do=home&uid={uid}&cid={cid}',
);
