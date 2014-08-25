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
<script type="text/javascript">
$(function(){
	$("#<?php echo APP_FORMID;?>").batch();
});
</script>
<div class="iCMS-container">
  <div class="widget-box" id="<?php echo APP_BOXID;?>">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-cloud"></i> </span>
      <h5 class="brs">数据库</h5>
      <ul class="nav nav-tabs" id="html-tab">
        <li class="active"><a href="<?php echo APP_URI; ?>&do=backup"><i class="fa fa-cloud-download"></i> <b>备份/优化/修复</b></a></li>
        <li><a href="<?php echo APP_URI; ?>&do=recover"><i class="fa fa-upload"></i> <b>备份管理</b></a></li>
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
              <th>表名</th>
              <th>行数</th>
              <th>数据</th>
              <th>索引</th>
              <th>大小</th>
              <th>创建</th>
              <th>最后更新</th>
              <th>字符集</th>
              <th>备注</th>
            </tr>
          </thead>
          <?php for($i=0;$i<$_count;$i++){
	    	$table	= $rs[$i]['Name'];
	    	//preg_match("/^".preg_quote(iPHP_DB_PREFIX,'/')."/i" ,$name)
    	  ?>
          <tr>
            <td><input type="checkbox" name="table[]" value="<?php echo $table ; ?>" /></td>
            <td><?php echo $i+1 ; ?></td>
            <td><?php echo $table ; ?></td>
            <td><?php echo $rs[$i]['Rows'] ; ?></td>
            <td><?php echo iFS::sizeUnit($rs[$i]['Data_length']) ; ?></td>
            <td><?php echo iFS::sizeUnit($rs[$i]['Index_length']) ; ?></td>
            <td><?php echo iFS::sizeUnit($rs[$i]['Data_length']+$rs[$i]['Index_length']) ; ?></td>
            <td><?php echo $rs[$i]['Create_time'] ; ?></td>
            <td><?php echo $rs[$i]['Update_time'] ; ?></td>
            <td><?php echo $rs[$i]['Collation'] ; ?></td>
            <td><?php echo $rs[$i]['Comment'] ; ?></td>
          </tr>
          <?php }  ?>
          <tr>
            <td colspan="11">
              <div class="input-prepend input-append mt20"> <span class="add-on">全选
                <input type="checkbox" class="checkAll checkbox" data-target="#<?php echo APP_BOXID;?>" />
                </span>
                <div class="btn-group dropup" id="batch"> <a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"><i class="fa fa-wrench"></i> 批 量 操 作 </a><a class="btn dropdown-toggle" data-toggle="dropdown" tabindex="-1"> <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    <li><a data-toggle="batch" data-action="backup"><i class="fa fa-upload"></i> 备份表</a></li>
              		<li class="divider"></li>
                    <li><a data-toggle="batch" data-action="optimize"><i class="fa fa-gavel"></i> 优化表</a></li>
                    <li><a data-toggle="batch" data-action="repair"><i class="fa fa-wrench"></i> 修复表</a></li>
                  </ul>
                </div>
              </div></td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>
<div class="iCMS-batch">
  <div id="backupBatch"><div class="input-prepend input-append"><span class="add-on">分卷备份</span><input name="sizelimit" type="text" value="2048" class="span1"><span class="add-on">KB</span><span class="add-on">每个分卷文件长度</span> </div></div>
</div>
<?php iACP::foot();?>
