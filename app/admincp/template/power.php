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

    set_select(power,'tab-power');
    set_select(cpower['cid'],'tab-cpower');
    set_select(cpower['do'],'tab-cpower');
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
<div id="tab-power" class="tab-pane hide">
  <div class="input-prepend input-append"><span class="add-on">全选</span><span class="add-on">
    <input type="checkbox" class="checkAll checkbox" data-target="#tab-power"/>
    </span><button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
  </div>
  <div class="clearfloat mb10"></div>
  <div class="input-prepend input-append">
    <span class="add-on">允许登陆后台 <input type="checkbox" name="power[]" value="ADMINCP" /></span>
  </div>
  <div class="clearfloat mb10 solid"></div>
  <div id="power_treecontrol"> <a style="display:none;"></a> <a style="display:none;"></a> <a class="btn btn-mini btn-info" href="#">展开/收缩</a></div>
  <ul id="power_tree">
  <?php echo iACP::app('menu')->power_tree();?>
  </ul>
</div>
<div id="tab-cpower" class="tab-pane hide">
  <div class="input-prepend input-append"><span class="add-on">全选</span><span class="add-on">
    <input type="checkbox" class="checkAll checkbox" data-target="#tab-cpower"/>
    </span><button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
  </div>
  <div class="clearfloat mb10"></div>
  <div id="cpower_treecontrol"> <a style="display:none;"></a> <a style="display:none;"></a> <a class="btn btn-mini btn-info" href="#">展开/收缩</a></div>
  <ul id="cpower_tree">
    <?php echo iACP::app('category')->power_tree();?>
  </ul>
</div>
        