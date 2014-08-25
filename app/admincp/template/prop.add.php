<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: prop.add.php 2379 2014-03-19 02:37:47Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
iACP::head();
?>
<script type="text/javascript">
$(function(){
iCMS.select('cid',"<?php echo $rs['cid'] ; ?>");
});
</script>

<div class="iCMS-container">
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-plus-square"></i> </span>
      <h5><?php echo empty($this->pid)?'添加':'修改' ; ?>属性</b></h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="iCMS-prop" target="iPHP_FRAME">
        <input name="pid" type="hidden" value="<?php echo $this->pid ; ?>" />
        <div id="<?php echo APP_BOXID;?>" class="tab-content">
          <div class="input-prepend"> <span class="add-on">所属栏目</span>
            <select name="cid" id="cid" class="span3 chosen-select">
              <option value="0"> ==== 暂无所属栏目 ==== </option>
              <?php echo $this->categoryApp->select('ca',$rs['cid'],0,1,true);?>
            </select>
          </div>
          <span class="help-inline">本属性所属的栏目</span>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">属性类型</span>
            <input type="text" name="type" class="span4" id="type" value="<?php echo $rs['type'];?>"/>
          </div>
          <span class="help-inline">article:文章 push:推送 category:栏目</span>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">属性字段</span>
            <input type="text" name="field" class="span4" id="field" value="<?php echo $rs['field'];?>"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">属性名称</span>
            <input type="text" name="name" class="span4" id="name" value="<?php echo $rs['name'];?>"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">属 性 值</span>
            <input type="text" name="val" class="span4" id="val" value="<?php echo $rs['val'];?>"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">排序</span>
            <input type="text" name="ordernum" class="span2" id="ordernum" value="<?php echo $rs['ordernum'];?>"/>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 添加</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php iACP::foot();?>
