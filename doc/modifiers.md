##模板标签调节器

> 允许在任何以 **$** 开头的标签中使用调节器对得到的值进行处理，形式为：

- modifiers调节器可带参数

```html
<!--{$变量名称|modifiers}-->
```


**调用方式**

- 将标签的值改为大写(只对英文有效)

```html
<!--{$site.title|upper}-->
```

- 当标签的值为空时用参数填充

```html
<!--{$site.title|default:"参数"}-->
```

- 格式化时间

```html
<!--{$article_list.pubdate|date:'时间格式'}-->
<!--{$article_list.pubdate|date:'Y-m-d'}-->
<!--{$article_list.pubdate|date:'Y-m-d H:i:s'}-->
<!--{$iCMS.NOW|date:'Y-m-d H:i:s'}-->
```

- 内容截取

```html
<!--{$article_list.description|cut:'字符数':'超过部分显示字符'}-->
<!--{$article_list.description|cut:'50':'...'}-->
```

- 清除html格式

```html
<!--{$article_list.description|html2txt}-->
```

- 缩略图

```html
<!--{$article_list.pic.url|thumb:"宽度":"长度"}-->
<!--{$article_list.pic.url|thumb:"140":"140"}-->
```

- 多调节器 (先清除html格式然后在截取长度50 超过部分显示...)

```html
<!--{$article_list.description|html2txt||cut:'50':'...'}-->
```
