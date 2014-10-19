<?php
defined('iPHP') OR exit('Access Denied');
return array (
  'site' => 
  array (
    'name' => 'iCMS V6',
    'seotitle' => '网站标题',
    'keywords' => '',
    'description' => '',
    'icp' => '',
  ),
  'debug' => 
  array (
    'php' => '1',
    'tpl' => '1',
  ),
  'template' => 
  array (
    'index_mode' => '1',
    'index' => '{iTPL}/index.htm',
    'index_name' => 'index',
    'pc' => 
    array (
      'tpl' => 'default',
    ),
    'mobile' => 
    array (
      'agent' => 'WAP,Smartphone,Mobile,UCWEB,Opera Mini,Windows CE,Symbian,SAMSUNG,iPhone,Android,BlackBerry,HTC,Mini,LG,SonyEricsson,J2ME,MOT',
      'domain' => 'http://v6.icms.com',
      'tpl' => 'mobile',
    ),
    'device' => 
    array (
      0 => 
      array (
        'name' => 'iPad',
        'ua' => 'iPad',
        'domain' => 'http://ipad.idreamsoft.com',
        'tpl' => 'iPad',
      ),
    ),
  ),
  'router' => 
  array (
    'URL' => 'http://v6.icms.com',
    'DIR' => '/',
    404 => 'http://v6.icms.com/404.html',
    'public_url' => 'http://v6.icms.com/public',
    'user_url' => 'http://v6.icms.com/u',
    'html_dir' => 'html/',
    'html_ext' => '.html',
    'speed' => '50',
    'rewrite' => '0',
    'tag_url' => 'http://v6.icms.com',
    'tag_rule' => '{PHP}',
    'tag_dir' => '/',
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
    'url' => 'http://v6.icms.com/res/',
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
    'coverpic' => '/ui/coverpic.jpg',
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
  'apps' => 
  array (
    0 => 'index',
    1 => 'article',
    2 => 'tag',
    3 => 'search',
    4 => 'usercp',
    5 => 'category',
    6 => 'comment',
    7 => 'favorite',
    8 => 'public',
    9 => 'user',
  ),
);