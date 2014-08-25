<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: filter.php 2322 2013-12-09 04:00:09Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
iACP::head();
?>
<div class="iCMS-container">
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-cloud"></i> </span>
      <h5 class="brs">数据库</h5>
      <ul class="nav nav-tabs" id="html-tab">
        <li><a href="<?php echo APP_URI; ?>&do=backup"><i class="fa fa-cloud-download"></i> <b>备份/优化/修复</b></a></li>
        <li class="active"><a href="<?php echo APP_URI; ?>&do=recover"><i class="fa fa-upload"></i> <b>备份管理</b></a></li>
        <li><a href="<?php echo APP_URI; ?>&do=replace"><i class="fa fa-retweet"></i> <b>数据替换</b></a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th><i class="fa fa-arrows-v"></i></th>
              <th style="width:24px;"></th>
              <th>备份卷</th>
              <th class="span4">操作</th>
            </tr>
          </thead>
			<?php
	            $_count		= count($dirRs);
	            for($i=0;$i<$_count;$i++){
            ?>
          <tr id="<?php echo md5($dirRs[$i]['name']);?>">
            <td><input type="checkbox" name="dir[]" value="<?php echo $dirRs[$i]['name'] ; ?>" /></td>
            <td><?php echo $i+1 ; ?></td>
            <td><?php echo $dirRs[$i]['name'] ; ?></td>
            <td class="op"><a class="btn btn-small" href="<?php echo APP_FURI; ?>&do=download&dir=<?php echo $dirRs[$i]['name'] ; ?>" target="iPHP_FRAME"><i class="fa fa-cloud-download"></i> 下载</a> <a href="<?php echo APP_FURI; ?>&do=del&dir=<?php echo $dirRs[$i]['name'] ; ?>" target="iPHP_FRAME" class="del btn btn-small" title='永久删除'  onclick="return confirm('确定要删除?');"/><i class="fa fa-trash-o"></i> 删除</a></td>
          </tr>
          <?php } if(!$_count){  ?>
          <tr><td colspan="4"><div class="alert alert-info">居然没有备份!!!请养成良好的备份习惯!多备份有益身体健康!</div></td></tr>
          <?php }?>
        </table>
        <div class="form-actions">
        </div>
      </form>
    </div>
  </div>
</div>
<?php iACP::foot();?>
