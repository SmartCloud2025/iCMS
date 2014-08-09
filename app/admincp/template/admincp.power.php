<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: groups.add.php 179 2013-03-29 03:21:28Z coolmoo $
 */
defined('iCMS') OR exit('What are you doing?'); 
?>
<link rel="stylesheet" href="<?php echo ACP_UI;?>/jquery/treeview-0.1.0.css" type="text/css" />
<script type="text/javascript" src="<?php echo ACP_UI;?>/jquery/treeview-0.1.0.js"></script>
<script type="text/javascript" src="<?php echo ACP_UI;?>/jquery/treeview-0.1.0.async.js"></script>
<script type="text/javascript">
$(function(){
    get_tree('power');
    get_tree('cpower');

    var power  = <?php echo $rs->power?$rs->power:'{}'?>,
        cpower = <?php echo $rs->cpower?$rs->cpower:'{}'?>;

    set_select(power,'<?php echo iACP::$app_name; ?>-power');
    set_select(cpower,'<?php echo iACP::$app_name; ?>-cpower');

});
function get_tree(e){
  return $("#"+e+"_tree").treeview({
      //url:'<?php echo APP_URI; ?>&do='+e+'_tree',
      collapsed: true,
      sortable: false,
      animated: "medium",
      control:"#"+e+"_treecontrol"
  });
}
function set_select(vars,el){
    if(!vars) return;
    $.each(vars, function(i,val){
      $('input[value="'+val+'"]',$("#"+el))
      .prop("checked", true).closest('.checker > span').addClass('checked');
    });  
}
</script>
<style>
.separator .checker{margin-top: -20px !important;}
</style> 
<div id="<?php echo iACP::$app_name; ?>-power" class="tab-pane hide">
  <div class="input-prepend input-append"><span class="add-on">全选</span><span class="add-on">
    <input type="checkbox" class="checkAll checkbox" data-target="#<?php echo iACP::$app_name; ?>-power"/>
    </span><button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
  </div>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend input-append">
    <span class="add-on">全局权限</span>
    <span class="add-on">::</span>
    <span class="add-on"><input type="checkbox" name="power[]" value="ADMINCP" /> 允许登陆后台</span>
  </div>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend input-append">
    <span class="add-on">文章权限</span>
    <span class="add-on">::</span>
    <span class="add-on"><input type="checkbox" name="power[]" value="ARTICLE.VIEW" /> 查看所有</span>
    <span class="add-on"><input type="checkbox" name="power[]" value="ARTICLE.EDIT" /> 编辑所有</span>
    <span class="add-on"><input type="checkbox" name="power[]" value="ARTICLE.DELETE" /> 删除所有</span>
  </div>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend input-append">
    <span class="add-on">文件权限</span>
    <span class="add-on">::</span>
    <span class="add-on"><input type="checkbox" name="power[]" value="FILE.UPLOAD" /> 上传</span>
    <span class="add-on"><input type="checkbox" name="power[]" value="FILE.MKDIR" /> 创建目录</span>
    <span class="add-on"><input type="checkbox" name="power[]" value="FILE.MANAGE" /> 管理</span>
    <span class="add-on"><input type="checkbox" name="power[]" value="FILE.BROWSE" /> 浏览</span>
    <span class="add-on"><input type="checkbox" name="power[]" value="FILE.EDIT" /> 编辑</span>
    <span class="add-on"><input type="checkbox" name="power[]" value="FILE.DELETE" /> 删除</span>
  </div>
  <div class="clearfloat"></div>
  <span class="label label-important">注:工具中的上传文件/文件管理为操作链接权限,是否有文件(上传/管理)权限以文件权限的设置为主</span>
  <div class="clearfloat mb10 solid"></div>
  <div id="power_treecontrol"> <a style="display:none;"></a> <a style="display:none;"></a> <a class="btn btn-mini btn-info" href="#">展开/收缩</a></div>
  <ul id="power_tree">
  <?php echo iACP::app('menu')->power_tree();?>
  </ul>
</div>
<div id="<?php echo iACP::$app_name; ?>-cpower" class="tab-pane hide">
  <div class="input-prepend input-append"><span class="add-on">全选</span><span class="add-on">
    <input type="checkbox" class="checkAll checkbox" data-target="#<?php echo iACP::$app_name; ?>-cpower"/>
    </span><button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
  </div>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend input-append">
    <span class="add-on">全局权限</span>
    <span class="add-on">::</span>
    <span class="add-on">允许添加顶级栏目</span>
    <span class="add-on"><input type="checkbox" name="cpower[]" value="0:a" /></span>
  </div>
  <div class="clearfloat mb10"></div>
  <div id="cpower_treecontrol"> <a style="display:none;"></a> <a style="display:none;"></a> <a class="btn btn-mini btn-info" href="#">展开/收缩</a></div>
  <ul id="cpower_tree">
    <?php echo iACP::app('category')->power_tree();?>
  </ul>
</div>
        