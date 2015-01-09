iCMS模板标签
====

##分类列表
```html
<!--{iCMS:category:list
  loop  = "true"
  row   = "10"
  cid   = "1"
  cid  != "1"
  cache = "true"
  time  = ""
  appid = ""
  pids  = ""
  stype = ""

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
|pids|属性值｜1,2,3|属性值,多项请用**,**隔开
|cache|true|启用缓存
|time|3600|缓存时间
|appid|2,3,4|应用ID
|mode|2,3,4|应用ID
|stype|top,sub,self,suball|top:顶级栏目 <br /> sub:子级栏目 <br /> self:同级栏目 <br /> suball:所有子级栏目
|as|变量别名|
|start|0|开始索引号
|step|步进值|
|max|最大索引值|

###标签内部变量
> *为系统变量

```
* <!--{$category_list.total}-->    总条数
* <!--{$category_list.prev}-->     上一条行号 (从1开始)
* <!--{$category_list.next}-->     下一条行号 (从1开始)
* <!--{$category_list.rownum}-->   行号 (从1开始)
* <!--{$category_list.index}-->    索引号 (从0开始)
* <!--{$category_list.first}-->    第一条为true 否则flase
* <!--{$category_list.last}-->     最后一条为true 否则flase

<!--{$category_list.name}-->         分类名称
<!--{$category_list.title}-->        分类标题
<!--{$category_list.url}-->          分类网址
<!--{$category_list.description}-->  分类简介

```

```
<!--{$category_list|print_r}-->      查看所有内部变量
```

- page = "true" 时  可调用分页标签

```
<!--{$iCMS.PAGE.NAV}-->
```

###常用示例
- 获取 10个分类

```
<!--{iCMS:category:list loop="true" row="10"}-->
 <a href="<!--{$category_list.url}-->"><!--{$category_list.title}--></a>
<!--{/iCMS}-->
```

- 获取 栏目ID [1] 下 10个子分类

```
<!--{iCMS:category:list loop="true" row="10" stype="sub" cid="1"}-->
  <a href="<!--{$category_list.url}-->"><!--{$category_list.title}--></a>
<!--{/iCMS}-->
```

