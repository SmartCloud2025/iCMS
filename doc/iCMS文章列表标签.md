#iCMS模板标签
##文章列表
```
<!--{iCMS:article:list
  loop      = "true"
  row       = "10"
  cid       = "1"
  cid      != "1"
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
内部变量 *为系统变量
* <!--{$article_list.total}-->    //总条数
* <!--{$article_list.prev}-->     //上一条行号 (从1开始)
* <!--{$article_list.next}-->     //下一条行号 (从1开始)
* <!--{$article_list.rownum}-->   //行号 (从1开始)
* <!--{$article_list.index}-->    //索引号 (从0开始)
* <!--{$article_list.first}-->    //第一条为true 否则flase
* <!--{$article_list.last}-->     //最后一条为true 否则flase

<!--{$article_list.title}-->        //文章标题
<!--{$article_list.url}-->          //文章网址
<!--{$article_list.description}-->  //文章简介
<!--{$article_list.pubdate}-->      //文章发布时间戳
<!--{$article_list.pubdate|date:'Y-m-d'}-->

<!--{$article_list|print_r}-->      //查看所有内部变量

<!--{/iCMS}-->
```
###属性介绍
|属性|可选值|说明
|-|-|-|
|loop|true|循环标记
|row|10|返回行数
|cid|1｜1,2,3|栏目ID,多项请用**,**隔开
|cid!|1｜1,2,3|排除的栏目ID,多项请用**,**隔开
|pid|1｜1,2,3|属性ID,多项请用**,**隔开
|startdate|20150101,-1,-15|指定开始时间,-1=1天前,-15=15天前,以此类推
|enddate|20150101,1,15|指定结束时间,1=1天后,15=15天后,以此类推
|pic|true|有缩略图的文章
|by|ASC｜DESC|排序方式 默认值DESC, ASC 从小到大  DESC从大到小
|orderby|hot,week,month,comment,pubdate,disorder,weight|排序方法 hot 总点击 ,week 周点击,month 月点击,comment 评论数,pubdate 发布时间  disorder 文章的排序 weight 权重
|keywords|关键词|在(title,keywords,description)搜索关键词,数据量大时 请使用 sphinx
|id|文章ID|指定文章ID
|id!|文章ID|排除文章ID
|cache|true|启用缓存
|cids|栏目ID｜1,2,3|副栏目的ID,多项请用**,**隔开
|pids|属性值｜1,2,3|属性值,多项请用**,**隔开
|tids|标签ID｜1,2,3|标签ID,多项请用**,**隔开
|userid|用户ID|
|call|user,admin|文章用户类型
|weight|权重|文章的权重
|nopic|true|无缩略图
|where|SQL语句|如果你觉得上面的条件不够用,那自己写吧
|start|0|开始索引号
|step|步进值|
|max|最大索引值


###常用示例
```
<!--{iCMS:article:list loop="true" row="10" orderby="hot" cid="1"}-->
获取 栏目ID 为 1 按总点击 从大到小 排序的文章 10条
<!--{/iCMS}-->
```
```
<!--{iCMS:article:list loop="true" row="10" orderby="week" cid="$category.cid"}-->
获取 [$category.cid] 变量为栏目ID 按周点击 从大到小 排序的文章 10条
<!--{/iCMS}-->
```
