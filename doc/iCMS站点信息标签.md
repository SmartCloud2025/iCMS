#iCMS模板标签
##站点信息
```
<!--{$site}-->
```
###可用属性
```
Array(
    [name]        => 网站名称
    [seotitle]    => 网站标题
    [keywords]    => 关键字
    [description] => 网站描述
    [icp]         => 备案号
    [title]       => 网站名称 (name的别名)
    [404]         => 404页面 网址
    [url]         => 网站网址
    [tpl]         => 桌面端模板目录
    [urls]        => Array(
        [public] => 公共资源URL
        [user]   => 用户URL
        [res]    => 附件URL
        [ui]     => 系统UI URL
        [avatar] => 用户头像URL
    )
)
```
###调用方式
例如:
```
网站名称:<!--{$site.name}-->
公共资源URL:<!--{$site.urls.public}-->
```
