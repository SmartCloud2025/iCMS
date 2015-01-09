iCMS模板标签
====

##分类信息
```html
<!--{$category}-->
<!--{$tag_category}-->
```
###使用范围
- 分类模板
- 文章模板
- TAG模板
- **$tag_category** 只能在TAG模板使用



###数据结构
```html
Array(
    [cid]         => 分类ID
    [rootid]      => 分类父级ID
    [pid]         => 属性值
    [appid]       => 应用ID [文章分类:2] [标签分类:3] [推送分类:4]
    [name]        => 分类名
    [subname]     => 分类别名
    [ordernum]    => 排序
    [title]       => 分类标题
    [keywords]    => 分类关键词
    [description] => 分类简介
    [dir]         => 分类目录
    [url]         => 分类URL
    [pic]         => Array(
        [src] => 分类缩略图 (2015/01-07/10/34c7b34696a67d535d540682e428e420.jpg)
        [url] => 分类缩略图网址 (http://www.ooxx.com/res/2015/01-07/10/34c7b34696a67d535d540682e428e420.jpg)
    )
    [mpic]     => Array( 同上 )
    [spic]     => Array( 同上 )
    [count]    => 分类内容总数
    [hasbody]  => 是否有大文本
    [body]     => 大文本段
    [metadata] => Array( 附加属性
        [自定义KEY] => 值
    )
    .....
    以上只是列出常用属性

    <!--{$category|print_r}--> 可查看所有属性
)
```
###调用方式

```html
分类名称:<!--{$category.name}-->
分类父级:<!--{$category.parent}--> (用法同 <!--{$category}-->)
分类父级名称:<!--{$category.parent.name}-->
分类导航:<!--{$category.nav}-->
子分类ID:<!--{$category.subids}-->
分类URL:<!--{$category.url}-->
分类link:<!--{$category.link}--> 等于 <a href="<!--{$category.url}-->" target="_blank"><!--{$category.name}--></a>
分类附加属性:<!--{$category.metadata.自定义KEY}-->
```
