iCMS模板标签
====
##搜索信息
```html
<!--{$search}-->
```
###使用范围
- 所有模板

###数据结构
```html
Array(
    [title]   => 搜索词
    [keyword] => 搜索词
)
```
###调用方式

```html
搜索词:<!--{$search.title}-->
```

###示例:
```html
注:一定要用$search.keyword
<!--{iCMS:article:list loop="true" page="true" row="10" keywords="$search.keyword"}-->

<!--{/iCMS}-->
```
