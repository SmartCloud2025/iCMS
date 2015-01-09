iCMS模板标签
====
##系统信息
```html
<!--{$iCMS}-->
```
###使用范围
- 所有模板

###数据结构
- 注意大小写

```html
Array(
  [VERSION]    => iCMS 版本
  [MOBILE]     => 是否手机端
  [API]        => 系统API接口网址
  [UI]         => 系统UI目录
  [UI_URL]     => 系统UI目录 网址
  [SAPI]       => 当前应用URL
  [COOKIE_PRE] => cookies前缀
  [REFER]      => 访问来路
  [CONFIG]     => 系统配置 (array)
  [APP]        => array( 当前应用
      [NAME]   => 当前应用名
      [DO]     => 当前应用请求的方法名
      [METHOD] => 当前应用请求的方法
  )
 [APPID]       => array( 应用ID
      [ARTICLE]  => int 1
      [CATEGORY] => int 2
      [TAG]      => int 3
      [PUSH]     => int 4
      [COMMENT]  => int 5
      [PROP]     => int 6
      [MESSAGE]  => int 7
      [FAVORITE] => int 8
      [USER]     => int 9
  )
)
```
###调用方式
```html
iCMS版本:<!--{$iCMS.VERSION}-->
系统API接口网址:<!--{$iCMS.API}-->
当前应用名:<!--{$iCMS.APP.NAME}-->
应用ID:<!--{$iCMS.APPID.ARTICLE}-->
当前时间截:<!--{$iCMS.NOW}-->
```
