基本语法
=====

###判断 (if)

> 模板文件中可以使用if else等判断语句

> "**==**","**!=**","**>**","**<**","**<=**","**>=**"

> 这些是if中可以用到的比较。看看就能知道什么意思吧。

```html
<!--{if $name=="iCMS"}-->
    Hello World
<!--{elseif $name=="idreamsoft"}-->
    idreamsoft.com
<!--{else}-->
    Welcome
<!--{/if}-->
```

###循环遍历数组 (foreach)

> foreach 必须和 /foreach 成对使用，且必须指定 from 和 value 属性。

> key,start,end 属性可选

> key 索引

> start 开始行

> end 结束行

```html
<!--{foreach key=key value=alist from="$article_list"}-->
    <!--{$key}-->
    <a href="<!--{$alist.url}-->"><!--{$alist.title}--></a>
<!--{/foreach}-->
```
