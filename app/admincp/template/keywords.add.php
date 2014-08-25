<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: keywords.add.php 2365 2014-02-23 16:26:27Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
iACP::head();
?>
<style type="text/css">
.add-on { width: 70px; }
</style>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-plus-square"></i> </span>
      <h5><?php echo empty($this->id)?'添加':'修改' ; ?>关键词</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="iCMS-keywords" target="iPHP_FRAME">
        <input name="id" type="hidden" value="<?php echo $this->id; ?>" />
        <div id="keywords-add" class="tab-content">
          <div class="input-prepend"> <span class="add-on">关键词</span>
            <input type="text" name="keyword" class="span3" id="keyword" value="<?php echo $rs['keyword'] ; ?>"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">链 接</span>
            <input type="text" name="url" class="span6" id="url" value="<?php echo $rs['url'] ; ?>"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">替换次数</span>
            <input type="text" name="times" class="span1" id="times" value="<?php echo (int)$rs['times'] ; ?>"/>
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
