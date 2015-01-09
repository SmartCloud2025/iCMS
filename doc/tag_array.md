iCMS模板标签
====

##TAG信息
```html
<!--{$tag}-->
```
###使用范围

- TAG页 (tag.index.htm)




###数据结构
```html
Array(
    [id]          => 标签 ID
    [uid]         => 添加者ID
    [cid]         => 栏目ID
    [tcid]        => 标签分类ID
    [pid]         => 属性
    [tkey]        => 唯一标识
    [name]        => 标签
    [seotitle]    => 标签标题
    [subtitle]    => 副标题
    [keywords]    => 关键字
    [description] => 标签描述
    [metadata]    => Array( 附加属性
        [自定义KEY] => 值
    )

    [haspic]   => 是否有图
    [pic]      => Array(
            [src]    => 图片地址 2015/01-06/10/ed29c2163a7cd9335b96b515beeee864.jpg
            [url]    => 图片网址 http://www.00xx.com/res/2015/01-06/10/ed29c2163a7cd9335b96b515beeee864.jpg
            [width]  => 图片宽
            [height] => 图片高
    )

    [url]      => 标签URL
    [related]  => 相关
    [comments] => 评论数
    [count]    => 点击数
    [weight]   => 权重
    [tpl]      => 模板
    [ordernum] => 排序
    [pubdate]  => 发布时间
    [link]     => 标签链接
    [appid]    => 应用ID

    .....
    以上只是列出常用属性

    <!--{$tag|print_r}--> 可查看所有属性
)
```
###调用方式

```html
标签名称:<!--{$tag.name}-->
标签URL:<!--{$tag.url}-->
标签link:<!--{$tag.link}--> 等于 <a href="<!--{$tag.url}-->" target="_blank"><!--{$tag.name}--></a>
标签附加属性:<!--{$tag.metadata.自定义KEY}-->
```
