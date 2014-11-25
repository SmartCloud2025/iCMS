<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: menu.manage.php 2070 2013-09-09 15:34:49Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
iACP::head();
?>
<link rel="stylesheet" href="./app/admincp/ui/jquery/treeview-0.1.0.css" type="text/css" />
<link rel="stylesheet" href="./app/admincp/ui/jquery/ui-1.10.3.min.css" type="text/css" />
<script type="text/javascript" src="./app/admincp/ui/jquery/ui-1.10.3.min.js"></script>
<script type="text/javascript" src="./app/admincp/ui/jquery/treeview-0.1.0.js"></script>
<script type="text/javascript" src="./app/admincp/ui/jquery/treeview-0.1.0.async.js"></script>
<script type="text/javascript">
var upordurl="<?php echo APP_URI; ?>&do=updateorder";
$(function(){
    $("#tree").treeview({
    	url:'<?php echo APP_URI; ?>&do=ajaxtree&expanded=0',
        collapsed: false,
        sortable: true,
        animated: "medium",
        control:"#treecontrol",
    }).sortable({
        helper: "clone",
        placeholder: "ui-state-highlight",
        delay: 100,
        start: function(event, ui) {
            $(ui.item).show().css({'opacity': 0.5});
        },
        stop: function(event, ui) {
            $(ui.item).css({'opacity': 1});
            var pt = ui.item.parent();
            var ord = $(".ordernum > input",pt);
            var ordernum = new Array();
            ord.each(function(i) {
                $(this).val(i);
            	var id = $(this).attr("data-id");
            	ordernum.push(id);
            });
            $.post(upordurl,{ordernum: ordernum});
        }
    }).disableSelection();
});
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-list"></i> </span>
      <h5>后台菜单</h5>
      <div id="treecontrol"> <a style="display:none;"></a> <a style="display:none;"></a> <a class="btn btn-mini btn-info" href="javascript:;">展开/收缩</a></div>
      <a style="margin: 10px;" class="btn btn-mini" href="<?php echo __ADMINCP__; ?>=cache&do=menu" target="iPHP_FRAME"><i class="fa fa-refresh"></i> 更新缓存</a>
    </div>
    <div class="widget-content nopadding">
      <div id="menu-list" class="tab-content">
        <div id="menu-tree" class="row-fluid menu-treeview">
          <ul id="tree">
            <p id="tree-loading"><img src="./app/admincp/ui/loading.gif" />
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
<?php iACP::foot();?>
