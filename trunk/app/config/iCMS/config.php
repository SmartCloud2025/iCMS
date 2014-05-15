<?php
defined('iPHP') OR exit('Access Denied');
$GLOBALS['iConfig']	= array (
  'site' => 
  array (
    'name' => 'iCMS V6',
    'seotitle' => '',
    'keywords' => '',
    'description' => '',
    'icp' => '',
    'PC_TPL' => 'default',
    'MW_TPL' => 'mobile',
    'indexTPL' => '{iTPL}/index.htm',
    'indexName' => 'index',
  ),
  'debug' => 
  array (
    'php' => '1',
    'tpl' => '1',
  ),
  'router' => 
  array (
    'DIR' => 'trunk',
    404 => 'http://v6.icms.com',
    'publicURL' => 'http://v6.icms.com/trunk/public',
    'userURL' => 'http://v6.icms.com/trunk',
    'URL' => 'http://v6.icms.com/trunk',
    'htmldir' => '/html/',
    'htmlext' => '.html',
    'speed' => '50',
    'tagURL' => 'http://v6.idreamsoft.com',
    'tagRule' => '{PHP}',
    'tagDir' => '/',
  ),
  'cache' => 
  array (
    'enable' => '1',
    'engine' => 'file',
    'host' => 'cache',
    'time' => '300',
    'compress' => '1',
  ),
  'FS' => 
  array (
    'url' => 'http://v6.icms.com/trunk/res',
    'dir' => 'res',
    'dir_format' => 'Y/m-d/H',
    'allow_ext' => 'gif,jpg,rar,swf,jpeg,png',
  ),
  'thumb' => 
  array (
    'enable' => '1',
    'width' => '140',
    'height' => '140',
  ),
  'watermark' => 
  array (
    'enable' => '1',
    'width' => '140',
    'height' => '140',
    'pos' => '0',
    'x' => '0',
    'y' => '0',
    'img' => 'watermark.png',
    'text' => 'iCMS',
    'fontsize' => '12',
    'color' => '#000000',
    'transparent' => '80',
    'thumb' => '1',
  ),
  'user' => 
  array (
    'register' => '1',
    'regseccode' => '1',
    'login' => '1',
    'loginseccode' => '0',
    'agreement' => '',
  ),
  'openapi' => 
  array (
    'QQ' => 
    array (
      'appid' => '140',
      'appkey' => '140',
    ),
  ),
  'publish' => 
  array (
    'autoformat' => '0',
    'remote' => '0',
    'autopic' => '0',
    'autodesc' => '1',
    'descLen' => '100',
    'autoPage' => '0',
    'AutoPageLen' => '1000',
    'repeatitle' => '0',
    'showpic' => '1',
  ),
  'comment' => 
  array (
    'enable' => '1',
    'examine' => '1',
    'seccode' => '0',
  ),
  'time' => 
  array (
    'zone' => 'Asia/Shanghai',
    'cvtime' => '0',
    'dateformat' => 'Y-m-d H:i:s',
  ),
  'other' => 
  array (
    'mobile_agent' => 'WAP,Smartphone,Mobile,UCWEB,Opera Mini,Windows CE,Symbian,SAMSUNG,iPhone,Android,BlackBerry,HTC,Mini,LG,SonyEricsson,J2ME,MOT',
    'CLsplit' => ',',
    'kwCount' => '1',
    'sidebar_enable' => '1',
    'sidebar' => '1',
  ),
  'system' => 
  array (
    'patch' => '2',
  ),
  'sphinx' => 
  array (
    'host' => '127.0.0.1:9312',
    'index' => 'iCMS_article iCMS_article_delta',
  ),
  'app' => 
  array (
    0 => 'index',
    1 => 'article',
    2 => 'tag',
    3 => 'search',
    4 => 'usercp',
    5 => 'category',
    6 => 'comment',
    7 => 'public',
    8 => 'user',
  ),
);