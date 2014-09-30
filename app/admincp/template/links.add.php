<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: links.add.php 179 2013-03-29 03:21:28Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
iACP::head();
?>
<style type="text/css">
.add-on { width: 70px; }
</style>

<div class="iCMS-container">
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-plus-square"></i> </span>
      <h5><?php echo empty($this->id)?'添加':'修改' ; ?>网站</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="iCMS-links" target="iPHP_FRAME">
        <input name="id" type="hidden" value="<?php echo $this->id; ?>" />
        <div id="<?php echo APP_BOXID;?>" class="tab-content">
          <div class="input-prepend"> <span class="add-on">分类</span>
            <input type="text" name="sortid" class="span1" id="sortid" value="<?php echo $rs['sortid'] ; ?>"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">网站</span>
            <input type="text" name="name" class="span3" id="name" value="<?php echo $rs['name'] ; ?>"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">logo</span>
            <input type="text" name="logo" class="span6" id="logo" value="<?php echo $rs['logo'] ; ?>"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">链接</span>
            <input type="text" name="url" class="span6" id="url" value="<?php echo $rs['url'] ; ?>"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend" style="width:100%;"><span class="add-on">介绍</span>
            <textarea name="desc" id="desc" class="span6" style="height: 150px;"><?php echo $rs['desc'] ; ?></textarea>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">排序</span>
            <input type="text" name="ordernum" class="span1" id="ordernum" value="<?php echo $rs['ordernum'] ; ?>"/>
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
