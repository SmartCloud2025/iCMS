<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: pushcategory.add.php 2379 2014-03-19 02:37:47Z coolmoo $
 */
defined('iCMS') OR exit('What are you doing?'); 
iACP::head();
?>
<script type="text/javascript">
$(function(){
	iCMS.select('pid',"<?php echo $rs['pid'] ; ?>");
});
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-plus-square"></i> </span>
      <h5><?php echo empty($this->cid)?'添加':'修改' ; ?>版块</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="iCMS-pushcategory" target="iPHP_FRAME">
        <input name="status" type="hidden" value="<?php echo $rs['status']  ; ?>" />
        <input name="cid" type="hidden" value="<?php echo $rs['cid']  ; ?>" />
        <div id="pushcategory-add" class="tab-content">
          <div class="input-prepend"> <span class="add-on">上级版块</span>
            <?php if(iMember::CP($rootid) || empty($rootid)) {   ?>
            <select name="rootid" class="chosen-select">
              <option value="0">======顶级版块=====</option>
              <?php echo $this->category->select($rootid,0,1,'all',true);?>
            </select>
            <?php }else {  ?>
            <input name="rootid" id="rootid" type="hidden" value="<?php echo $rootid ; ?>" />
            <input readonly="true" value="<?php echo $this->category->category[$rootid]['name'] ; ?>" type="text" class="txt" />
            <?php }  ?>
          </div>
          <span class="help-inline">本版块的上级版块或分类</span>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">版块属性</span>
            <select name="pid" id="pid" class="chosen-select">
              <option value="0">普通版块[pid='0']</option>
              <?php echo iACP::getProp("pid",$rs['pid']) ; ?>
            </select>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">版块名称</span>
            <input type="text" name="name" class="span6" id="name" value="<?php echo $rs['name'] ; ?>"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">版块目录</span>
            <input type="text" name="dir" class="span6" id="dir" value="<?php echo $rs['dir'] ; ?>"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">版块链接</span>
            <input type="text" name="url" class="span6" id="url" value="<?php echo $rs['url'] ; ?>"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">版块排序</span>
            <input id="orderNum" class="span1" value="<?php echo $rs['orderNum'] ; ?>" name="orderNum" type="text"/>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php iACP::foot();?>
