<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: filter.php 2365 2014-02-23 16:26:27Z coolmoo $
 */
defined('iCMS') OR exit('What are you doing?'); 
iACP::head();
?>

<div class="iCMS-container">
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-filter"></i> </span>
      <ul class="nav nav-tabs" id="filter-tab">
        <li class="active"><a href="#tab-disable" data-toggle="tab"><i class="fa fa-strikethrough"></i> 禁用词</a></li>
        <li><a href="#tab-filter" data-toggle="tab"><i class="fa fa-umbrella"></i> 过滤词</a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="iCMS-filter" target="iPHP_FRAME">
        <div id="filter" class="tab-content">
          <div id="tab-disable" class="tab-pane active">
            <textarea name="disable" class="span6" style="height: 150px;"><?php echo implode("\r\n",(array)$disable) ; ?></textarea>
          </div>
          <div id="tab-filter" class="tab-pane hide">
            <textarea name="filter" class="span6" style="height: 150px;"><?php echo implode("\r\n",(array)$filterArray) ; ?></textarea>
          </div>
          <span class="help-inline">每行一个<br />
          过滤词格式:过滤词=***</span> </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php iACP::foot();?>
