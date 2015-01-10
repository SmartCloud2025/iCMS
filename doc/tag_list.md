iCMS模板标签
=====

##TAG列表
```
<!--{iCMS:tag:list
  loop      = "true"
  row       = "10"
  cache     = "true"
  time      = ""
  tcid       = "1"
  cid       = "1"
  cids      = ""
  sub       = "all|true"
  pid       = "1"
  pids      = ""
  by        = "ASC|DESC"
  orderby   = "hot|new|order"

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
* <!--{$tag_list.total}-->    总条数
* <!--{$tag_list.prev}-->     上一条行号 (从1开始)
* <!--{$tag_list.next}-->     下一条行号 (从1开始)
* <!--{$tag_list.rownum}-->   行号 (从1开始)
* <!--{$tag_list.index}-->    索引号 (从0开始)
* <!--{$tag_list.first}-->    第一条为true 否则flase
* <!--{$tag_list.last}-->     最后一条为true 否则flase

<!--{$tag_list.name}-->         TAG名称
<!--{$tag_list.title}-->        TAG标题
<!--{$tag_list.url}-->          TAG网址
<!--{$tag_list.description}-->  TAG简介
<!--{$tag_list.pubdate}-->      TAG发布时间戳
<!--{$tag_list.pubdate|date:'Y-m-d'}-->
<!--{$tag_list.pic.url}-->      TAG缩略图网址
```

```
<!--{$tag_list|print_r}-->      查看所有内部变量
```

###属性介绍

|属性|可选值|说明
|-|-|-|
|loop|true|循环标记
|row|10|返回行数
|cid|1｜1,2,3|栏目ID,多项请用**,**隔开
|cid!|1｜1,2,3|排除的栏目ID,多项请用**,**隔开
|pid|1｜1,2,3|属性ID,多项请用**,**隔开
|by|ASC｜DESC|排序方式 默认值DESC<br />ASC 从小到大 <br />DESC从大到小
|orderby|hot,new,order|排序方法 <br /> hot 总点击
|cache|true|启用缓存
|time|3600|缓存时间
|cids|栏目ID｜1,2,3|副栏目的ID,多项请用**,**隔开
|pids|属性值｜1,2,3|属性值,多项请用**,**隔开
|as|无|变量别名
|start|0|开始索引号
|step|1|步进值
|max|无|最大索引值

- page = "true" 时  可调用分页标签

```
<!--{$iCMS.PAGE.NAV}-->
```

###常用示例

####获取 栏目ID 为 1 按总点击 从大到小 排序的标签 10条

```
<!--{iCMS:tag:list loop="true" row="10" orderby="hot" cid="1"}-->
  <a href="<!--{$tag_list.url}-->"><!--{$tag_list.name}--></a>
<!--{/iCMS}-->
```
