<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: filter.php 2322 2013-12-09 04:00:09Z coolmoo $
 */
defined('iCMS') OR exit('What are you doing?'); 
iACP::head();
?>
<div class="iCMS-container">
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-cloud"></i> </span>
      <h5 class="brs">数据库</h5>
      <ul class="nav nav-tabs" id="html-tab">
        <li><a href="<?php echo APP_URI; ?>&do=backup"><i class="fa fa-cloud-download"></i> <b>备份/优化/修复</b></a></li>
        <li><a href="<?php echo APP_URI; ?>&do=recover"><i class="fa fa-upload"></i> <b>备份管理</b></a></li>
        <li class="active"><a href="<?php echo APP_URI; ?>&do=replace"><i class="fa fa-retweet"></i> <b>数据替换</b></a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=query" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <div class="tab-content">
          <div class="alert alert-info mb10">批量替换属直接对操作数据库，存在一定危险性，请慎用!!!</div>
          <div class="input-prepend"> <span class="add-on">字段</span>
            <select name="field" id="field" class="chosen-select">
              <option value="title">标题</option>
              <option value="clink">自定义链接</option>
              <option value="comments">评论数</option>
              <option value="pic">缩略图</option>
              <option value="cid">栏目</option>
              <option value="tkd">标题/关键字/简介</option>
              <option value="body">内容</option>
            </select>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend" style="width:100%;"><span class="add-on">查找</span>
            <textarea name="pattern" id="pattern" class="span6" style="height: 150px;"></textarea>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend" style="width:100%;"><span class="add-on">替换</span>
            <textarea name="replacement" id="replacement" class="span6" style="height: 150px;"></textarea>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend" style="width:100%;"><span class="add-on">条件</span>
            <textarea name="where" id="where" class="span6" style="height: 150px;"></textarea>
          </div>
    	  <span class="help-inline">只支持SQL语句</span>
          <div class="form-actions">
            <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 执 行</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<?php iACP::foot();?>
