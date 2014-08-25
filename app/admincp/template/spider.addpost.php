<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: spider.php 586 2013-04-02 14:44:18Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
iACP::head();
?>
<script type="text/javascript">
$(function(){
iCMS.select('app',"<?php echo $rs['app'] ; ?>");
});

</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-plus-square"></i> </span>
      <h5><?php echo empty($this->poid)?'添加':'修改' ; ?>发布模块</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=savepost" method="post" class="form-inline" id="iCMS-spider" target="iPHP_FRAME">
        <input name="id" type="hidden" value="<?php echo $this->poid ; ?>" />
        <div id="addpost" class="tab-content">
          <div class="input-prepend"><span class="add-on">应用</span>
            <select name="app" id="app" class="chosen-select span2">
              <option value="article"> 文章系统 </option>
            </select>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"><span class="add-on">名称</span>
            <input type="text" name="name" class="span6" id="name" value="<?php echo $rs['name']; ?>"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"><span class="add-on">发布项</span>
            <textarea name="post" id="post" class="span6" style="height: 90px;"><?php echo $rs['post'] ; ?></textarea>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"><span class="add-on">函数名</span>
            <input type="text" name="fun" class="span6" id="fun" value="<?php echo $rs['fun']; ?>"/>
          </div>
          <div class="clearfloat mb10"></div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php iACP::foot();?>
