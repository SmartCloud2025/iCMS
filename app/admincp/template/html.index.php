<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: pushforum.add.php 2404 2013-03-02 07:32:33Z coolmoo $
 */
defined('iCMS') OR exit('What are you doing?'); 
iACP::head();
?>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-file"></i> </span>
      <h5 class="brs">生成静态</h5>
      <ul class="nav nav-tabs" id="html-tab">
        <li class="active"><a href="#"><i class="fa fa-floppy-o"></i> <b>首页</b></a></li>
        <li><a href="<?php echo APP_URI; ?>&do=category"><i class="fa fa-floppy-o"></i> <b>栏目</b></a></li>
        <li><a href="<?php echo APP_URI; ?>&do=article"><i class="fa fa-floppy-o"></i> <b>文章</b></a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=createIndex" method="post" class="form-inline" id="iCMS-html" target="iPHP_FRAME">
        <div id="html-add" class="tab-content">
          <div class="input-prepend input-append"> <span class="add-on">主页模板</span>
            <input type="text" name="indexTPL" class="span3" id="indexTPL" value="<?php echo iCMS::$config['site']['indexTPL'] ; ?>"/>
            <a href="<?php echo __ADMINCP__; ?>=files&do=seltpl&from=modal&click=file&target=indexTPL" class="btn" data-toggle="modal" title="选择模板文件"><i class="fa fa-search"></i> 选择</a> </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">文 件 名</span>
            <input type="text" name="indexName" class="span3" id="indexName" value="<?php echo iCMS::$config['site']['indexName'] ; ?>"/>
          </div>
          <span class="help-inline"><?php echo iCMS::$config['router']['html_ext'] ; ?> 首页文件名,一般为<span class="label label-important">index</span> 不用填写文件后缀名</span> </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 生成</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php iACP::foot();?>
