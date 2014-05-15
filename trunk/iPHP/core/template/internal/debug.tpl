<table border=0 width=100%>
	<tr bgcolor=#cccccc><th colspan=2 align="left">Template Lite Debug Console</th></tr>
	<tr bgcolor=#cccccc><td colspan=2 align="left"><b>Included templates (load time in seconds):</b></td></tr>
	<!--{foreach key=key value=templates from=$_debug_tpls}-->
	<tr bgcolor=<!--{if $key % 2}-->#eeeeee<!--{else}-->#fafafa<!--{/if}-->>
	<td colspan=2 align="left"><tt><!--{for start=0 stop=$_debug_tpls[$key].depth}-->&nbsp;&nbsp;&nbsp;<!--{/for}-->
	<font color=<!--{if $_debug_tpls[$key].type eq "template"}-->brown<!--{elseif $_debug_tpls[$key].type eq "insert"}-->black<!--{else}-->green<!--{/if}-->>
	<!--{$_debug_tpls[$key].filename}--></font><!--{if isset($_debug_tpls[$key].exec_time)}-->
	(<!--{$_debug_tpls[$key].exec_time|string_format:"%.5f"}--> seconds)<!--{if $key eq 0}--> (total)<!--{/if}-->
	<!--{/if}--></tt></td></tr>
	<!--{foreachelse}-->
	<tr bgcolor=#eeeeee><td colspan=2 align="left"><tt><i>No template assigned</i></tt></td></tr>
	<!--{/foreach}-->
	<tr bgcolor=#cccccc><td colspan=2 align="left"><b>Assigned template variables:</b></td></tr>
	<!--{foreach key=key value=vars from=$_debug_keys}-->
	<tr bgcolor=<!--{if $key % 2}-->#eeeeee<!--{else}-->#fafafa<!--{/if}-->>
	<td valign=top align="left"><tt><font color=blue>{$<!--{$_debug_keys[$key]}-->}</font></tt></td>
	<td nowrap align="left"><tt><font color=green><!--{$_debug_vals[$key]|@debug_print_var}--></font></tt></td></tr>
	<!--{foreachelse}-->
	<tr bgcolor=#eeeeee><td colspan=2 align="left"><tt><i>No template variables assigned</i></tt></td></tr>
	<!--{/foreach}-->
</table>