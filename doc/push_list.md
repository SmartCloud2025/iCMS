iCMS模板标签
====

##推送列表
```html
<!--{iCMS:push:list
  loop      = "true"
  row       = "10"
  cache     = "true"
  time      = ""
  cid       = "1"
  cid       != "1"
  pid       = ""
  pic       = ""
  nopic     = ""
  startdate = ""
  enddate   = ""
  by        = "ASC|DESC"
  orderby   = "id|addtime|disorder"


  as    = ""
  start = "0"
  step  = ""
  max   = ""
}-->
<!--{/iCMS}-->
```
###使用范围
- 所有模板

###属性介绍
|属性|可选值|说明
|-|-|-|
|loop|true|循环标记
|row|10|返回行数
|cid|1｜1,2,3|栏目ID,多项请用**,**隔开
|cid!|1｜1,2,3|排除的栏目ID,多项请用**,**隔开
|pid|属性值|属性值
|cache|true|启用缓存
|time|3600|缓存时间
|as|无|变量别名
|start|0|开始索引号
|step|1|步进值
|max|无|最大索引值

###标签内部变量
> *为系统变量

```
* <!--{$push_list.total}-->    总条数
* <!--{$push_list.prev}-->     上一条行号 (从1开始)
* <!--{$push_list.next}-->     下一条行号 (从1开始)
* <!--{$push_list.rownum}-->   行号 (从1开始)
* <!--{$push_list.index}-->    索引号 (从0开始)
* <!--{$push_list.first}-->    第一条为true 否则flase
* <!--{$push_list.last}-->     最后一条为true 否则flase

<!--{$push_list.title}-->        标题
<!--{$push_list.title2}-->       标题
<!--{$push_list.title3}-->       标题
<!--{$push_list.url}-->          网址
<!--{$push_list.url2}-->          网址
<!--{$push_list.url3}-->          网址
<!--{$push_list.pic}-->          图片
<!--{$push_list.pic2}-->          图片
<!--{$push_list.pic3}-->          图片
<!--{$push_list.description}-->   简介
<!--{$push_list.description2}-->  简介
<!--{$push_list.description3}-->  简介
<!--{$push_list.metadata.自定义KEY}-->  自定义KEY

```

```
<!--{$push_list|print_r}-->      查看所有内部变量
```

- page = "true" 时  可调用分页标签

```
<!--{$iCMS.PAGE.NAV}-->
```

###常用示例
- 获取 10条推送

```
<!--{iCMS:push:list loop="true" row="10"}-->
 <a href="<!--{$push_list.url}-->"><!--{$push_list.title}--></a>
<!--{/iCMS}-->
```


