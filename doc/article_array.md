iCMS模板标签
====

##文章信息
```html
<!--{$article}-->
```
###使用范围

- 文章模板




###数据结构
```html
Array(
    [id]       => 文章ID
    [cid]      => 栏目ID
    [scid]     => 副栏目ID
    [ucid]     => 用户分类ID
    [pid]      => 自定义属性值
    [ordernum] => 排序
    [title]    => 标题
    [stitle]   => 短标题
    [clink]    => 自定义链接
    [url]      => 链接
    [source]   => 出处
    [author]   => 作者
    [editor]   => 编辑
    [userid]   => 编辑/用户ID
    [haspic]   => 是否有图
    [pic]      => Array(
            [src]    => 图片地址 2015/01-06/10/ed29c2163a7cd9335b96b515beeee864.jpg
            [url]    => 图片网址 http://www.00xx.com/res/2015/01-06/10/ed29c2163a7cd9335b96b515beeee864.jpg
            [width]  => 图片宽
            [height] => 图片高
    )

    [mpic]        => 同 pic
    [spic]        => 同 pic

    [keywords]    => 关键字
    [tags]        => 标签
    [description] => 简介
    [related]     => 相关
    [metadata]    => 附加内容
    [pubdate]     => 发布时间
    [postime]     => 提交时间
    [tpl]         => 模板
    [hits]        => Array(
        [script] => 点击统计脚本 http://www.ooxx.com/public/api.php?app=article&do=hits&cid=1&id=1
        [count]  => 总点击
        [today]  => 今天点击
        [yday]   => 昨天点击
        [week]   => 周点击
        [month]  => 月点击
    )
    [hits_today] => 今天点击
    [hits_yday]  => 昨天点击
    [hits_week]  => 周点击
    [hits_month] => 月点击

    [favorite] => 收藏数
    [comments] => 评论数
    [good]     => 支持
    [bad]      => 反对
    [creative] => 文章类型 1原创 0转贴
    [weight]   => 权重
    [mobile]   => 是否手机发布
    [postype]  => 用户类型 1管理 0用户
    [status]   => 状态
    [appid]    => 应用ID

    [link]     => 文章链接
    [body]     => 正文
    [subtitle] =>副标题
    [taoke]    => 是否有淘宝链接
    [page]     => Array ( 分页
            [total]   => 总页数
            [count]   => 实际页数
            [current] => 当前页
            [num]     => 分页数字代码
            [text]    => 文本代码
            [nav]     => 分页代码
            [prev]    => 上一页URL
            [next]    => 下一页URL
            [last]    => 是否最后一页
            [end]     => 是否最后一页 一般使用这个
    )

    [tags_fname] => 第一个TAG
    [tag_array] => Array( TAG信息
        [0] =>Array(
            [name] => TAG
            [url]  => TAG URL
            [link] => TAG链接
        )
    )
    [tags_link] => TAG链接
    [user] => Array( 用户信息
            [uid]    => 编辑/用户ID
            [name]   => #iCMS.V6 (#开头管理 @开头用户)
            [url]    => javascript:; 管理无链接 用户为主页链接
            [avatar] => about:blank 头像
            [link]   => #iCMS.V6 用户链接
            [at]     => #iCMS.V6 用户链接
    )

    [comment] => Array (
        [url]   => 评论页URL http://www.ooxx.com/public/api.php?app=article&do=comment&appid=1&iid=1&cid=1
        [count] => 评论数
    )

    .....
    以上只是列出常用属性

    <!--{$article|print_r}--> 可查看所有属性
)
```
###调用方式

```html
文章名称:<!--{$article.title}-->
文章URL:<!--{$article.url}-->
文章link:<!--{$article.link}--> 等于 <a href="<!--{$article.url}-->" target="_blank"><!--{$article.title}--></a>
文章附加属性:<!--{$article.metadata.自定义KEY}-->
文章分页:<!--{$article.page.nav}-->
```
