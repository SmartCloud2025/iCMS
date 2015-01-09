##include

> include 标签用于在当前模板中包含其它模板. 当前模板中的变量在被包含的模板中可用. 必须指定 file 属性，该属性指明模板资源的位置.

> 如果设置了 assign 属性，该属性对应的变量名用于保存待包含模板的输出，这样待包含模板的输出就不会直接显示了。


```html
<!--{include file="模板资源" import="true|html"}-->
import="true" // 模板资源 当做文件包含
import="html" // 模板资源 当做html字符包含

```


**调用方式**

- 当前模板目录下的 header.htm 同级

```html
<!--{include file="./header.htm"}-->
```

- iCMS目录下的模板 public.ui.htm 绝对路径

```html
<!--{include file="/iCMS/public.ui.htm"}-->
<!--{include file="iCMS://public.ui.htm"}-->
```

- {iTPL} 系统配置默认模板

```html
<!--{include file="{iTPL}/footer.htm"}-->
```
