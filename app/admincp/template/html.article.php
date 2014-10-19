<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: pushforum.add.php 2404 2013-03-02 07:32:33Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
iACP::head();
?>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-file"></i> </span>
      <h5 class="brs">生成静态</h5>
      <ul class="nav nav-tabs" id="html-tab">
        <li><a href="<?php echo APP_URI; ?>&do=index"><i class="fa fa-floppy-o"></i> <b>首页</b></a></li>
        <li><a href="<?php echo APP_URI; ?>&do=category"><i class="fa fa-floppy-o"></i> <b>栏目</b></a></li>
        <li class="active"><a href="javascript:;"><i class="fa fa-floppy-o"></i> <b>文章</b></a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo __SELF__ ; ?>" method="get" class="form-inline" id="iCMS-html" target="iPHP_FRAME">
        <input type="hidden" name="app" value="<?php echo iACP::$app_name;?>" />
        <input type="hidden" name="do" value="createArticle" />
        <div id="html-add" class="tab-content">
          <div class="input-prepend input-append"> <span class="add-on">按栏目</span>
            <select name="cid[]" multiple="multiple" class="span3" size="15">
              <option value="all">所 有 栏 目</option>
              <optgroup label="======================================"></optgroup>
              <?php echo $this->categoryApp->select('cs');?>
            </select>
          </div>
          <hr>
          <div class="input-prepend input-append"><span class="add-on">按时间</span> <span class="add-on"><i class="fa fa-calendar"></i></span>
            <input type="text" class="span2 ui-datepicker" name="startime" value="<?php echo $_GET['startime'] ; ?>" placeholder="开始时间" />
            <span class="add-on"><i class="fa fa-minus"></i></span>
            <input type="text" class="span2 ui-datepicker" name="endtime" value="<?php echo $_GET['endtime'] ; ?>" placeholder="结束时间" />
            <span class="add-on"><i class="fa fa-calendar"></i></span> </div>
          <hr>
          <div class="input-prepend input-append"><span class="add-on">按文章ID</span> <span class="add-on">起始ID</span>
            <input type="text" name="startid" class="span1" id="startId"/>
            <span class="add-on"><i class="fa fa-arrows-h"></i></span> <span class="add-on">结束ID</span>
            <input type="text" name="endid" class="span1" id="endid"/>
            <span class="add-on"><i class="fa fa-filter"></i></span> </div>
          <hr>
          <div class="input-prepend"> <span class="add-on">生成顺序</span>
            <select name="orderby" id="orderby" class="chosen-select span2">
              <option value="">默认排序</option>
              <optgroup label="降序">
              <option value="id DESC">ID[降序]</option>
              <option value="hits DESC">点击[降序]</option>
              <option value="good DESC">顶[降序]</option>
              <option value="postime DESC">时间[降序]</option>
              <option value="pubdate DESC">发布时间[降序]</option>
              <option value="comments DESC">评论[降序]</option>
              </optgroup>
              <optgroup label="升序">
              <option value="id ASC">ID[升序]</option>
              <option value="hits ASC">点击[升序]</option>
              <option value="good ASC">顶[升序]</option>
              <option value="postime ASC">时间[升序]</option>
              <option value="pubdate ASC">发布时间[降序]</option>
              <option value="comments ASC">评论[升序]</option>
              </optgroup>
            </select>
          </div>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 开始</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php iACP::foot();?>
