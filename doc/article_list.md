iCMS模板标签
=====

##文章列表
```
<!--{iCMS:article:list
  loop      = "true"
  row       = "10"
  cid       = "1"
  cid      != "1"
  sub       = "all|true"
  pid       = "1"
  startdate = ""
  enddate   = ""
  pic       = "true"
  by        = "ASC|DESC"
  orderby   = "hot"
  keywords  = ""
  id        = "1"
  id       != "1"
  cache     = "true"
  time      = ""
  cids      = ""
  pids      = ""
  tids      = ""
  userid    = ""
  ucid      = ""
  weight    = ""
  status    = ""
  call      = "user|admin"
  nopic     = "true"
  where     = ""

  as        = ""
  start     = "0"
  step      = ""
  max       = ""
}-->
<!--{/iCMS}-->
```
###使用范围
- 所有模板

###标签内部变量
> *为系统变量

```
* <!--{$article_list.total}-->    总条数
* <!--{$article_list.prev}-->     上一条行号 (从1开始)
* <!--{$article_list.next}-->     下一条行号 (从1开始)
* <!--{$article_list.rownum}-->   行号 (从1开始)
* <!--{$article_list.index}-->    索引号 (从0开始)
* <!--{$article_list.first}-->    第一条为true 否则flase
* <!--{$article_list.last}-->     最后一条为true 否则flase

<!--{$article_list.title}-->        文章标题
<!--{$article_list.url}-->          文章网址
<!--{$article_list.description}-->  文章简介
<!--{$article_list.pubdate}-->      文章发布时间戳
<!--{$article_list.pubdate|date:'Y-m-d'}-->
<!--{$article_list.pic.url}-->      文章缩略图网址
```

```
<!--{$article_list|print_r}-->      查看所有内部变量
```

###属性介绍

|属性|可选值|说明
|-|-|-|
|loop|true|循环标记
|row|10|返回行数
|cid|1｜1,2,3|栏目ID,多项请用**,**隔开
|cid!|1｜1,2,3|排除的栏目ID,多项请用**,**隔开
|pid|1｜1,2,3|属性ID,多项请用**,**隔开
|startdate|20150101,-1,-15|指定开始时间<br />-1=1天前<br />-15=15天前<br />以此类推
|enddate|20150101,1,15|指定结束时间<br />1=1天后<br />15=15天后<br />以此类推
|pic|true|有缩略图的文章
|by|ASC｜DESC|排序方式 默认值DESC<br />ASC 从小到大 <br />DESC从大到小
|orderby|hot,week,month,comment,pubdate,disorder,weight|排序方法 <br /> hot 总点击 <br />week 周点击<br />month 月点击<br />comment 评论数<br />pubdate 发布时间<br />disorder 文章的排序 <br />weight 权重
|keywords|关键词|在(title,keywords,description)搜索关键词,数据量大时 请使用 sphinx
|id|文章ID|指定文章ID
|id!|文章ID|排除文章ID
|cache|true|启用缓存
|time|3600|缓存时间
|cids|栏目ID｜1,2,3|副栏目的ID,多项请用**,**隔开
|pids|属性值｜1,2,3|属性值,多项请用**,**隔开
|tids|标签ID｜1,2,3|标签ID,多项请用**,**隔开
|userid|用户ID|
|call|user,admin|文章用户类型
|weight|权重|文章的权重
|nopic|true|无缩略图
|where|SQL语句|如果你觉得上面的条件不够用,那自己写吧
|as|变量别名|
|start|0|开始索引号
|step|步进值|
|max|最大索引值|

- page = "true" 时  可调用分页标签

```
<!--{$iCMS.PAGE.NAV}-->
```

###常用示例

####获取 栏目ID 为 1 按总点击 从大到小 排序的文章 10条

```
<!--{iCMS:article:list loop="true" row="10" orderby="hot" cid="1"}-->
  <a href="<!--{$article_list.url}-->"><!--{$article_list.title}--></a>
<!--{/iCMS}-->
```

####获取 [$category.cid] 变量为栏目ID 按周点击 从大到小 排序的文章 10条

```
<!--{iCMS:article:list loop="true" row="10" orderby="week" cid="$category.cid"}-->
  <a href="<!--{$article_list.url}-->"><!--{$article_list.title}--></a>
<!--{/iCMS}-->
```

####获取 10个分类下 每个分类最新的10篇文章

```
<!--{iCMS:category:list loop="true" row="10"}-->
 <a href="<!--{$category_list.url}-->"><!--{$category_list.title}--></a>
  <!--{iCMS:article:list loop="true" row="10" cid="$category.cid"}-->
    <a href="<!--{$article_list.url}-->"><!--{$article_list.title}--></a>
  <!--{/iCMS}-->
<!--{/iCMS}-->
```

####获取 5个顶分类 下10个子分类 每个分类最新的10篇文章

```
<!--{iCMS:category:list loop="true" row="10"}-->
 <a href="<!--{$category_list.url}-->"><!--{$category_list.title}--></a>
  <!--{iCMS:category:list loop="true" row="10" stype="sub" cid="$category.cid" as="cate"}-->
    <a href="<!--{$cate.url}-->"><!--{$cate.title}--></a>
    <!--{iCMS:article:list loop="true" row="10" cid="$cate.cid"}-->
      <a href="<!--{$article_list.url}-->"><!--{$article_list.title}--></a>
    <!--{/iCMS}-->
  <!--{/iCMS}-->
<!--{/iCMS}-->
```

> 由于 iCMS:category:list 返回的数据默认赋值给 $category_list
嵌套循环时要使用 as 属性来改变嵌套里的变量赋值,赋值给$cate
所以子分类的数据调用变成了 $cate.title , $cate.cid

####不使用 loop="true"

- 最新 10条 有缩略图的文章

```
<!--{iCMS:article:list pic="true" row="10"}-->

<!--{$article_list|print_r}--> 可以查看数据结构
<!--{$article_list[0].title}--> 第一条标题
<!--{$article_list[1].title}--> 第二条标题 以些类推
```

- 搭配 foreach 使用

```
全部循环
<!--{foreach value=alist from="$article_list"}-->
    <a href="<!--{$alist.url}-->"><!--{$alist.title}--></a>
<!--{/foreach}-->

从第二条开始
<!--{foreach value=alist from="$article_list" start="2"}-->
    <a href="<!--{$alist.url}-->"><!--{$alist.title}--></a>
<!--{/foreach}-->

从第三条开始 第六条结束
<!--{foreach value=alist from="$article_list" start="3" end="6"}-->
    <a href="<!--{$alist.url}-->"><!--{$alist.title}--></a>
<!--{/foreach}-->

到第六条结束
<!--{foreach value=alist from="$article_list" end="6"}-->
    <a href="<!--{$alist.url}-->"><!--{$alist.title}--></a>
<!--{/foreach}-->
```
