<?php
/**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: files.manage.php 179 2013-03-29 03:21:28Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
iACP::head(false);
?>
<script type="text/javascript">
$(function() {
    $("#upload").click(function() {
        $("input[name=upfile]").click();
    })
    $("input[name=upfile]").change(function() {
        $("form").submit();
    })
})
function callback(obj) {
	var state	= window.top.modal_<?php echo $this->callback;?>('<?php echo $this->target;?>',obj);
	if(!state){
		window.top.iCMS_MODAL.destroy();
	}
}
</script>
<?php if($this->from!='modal'){?>

<div class="iCMS-container">
  <?php } ?>
  <?php if ($rs) { ?>
  <div class="widget-box<?php if($this->from=='modal'){?> widget-plain<?php } ?>" id="files-add">
    <div class="widget-title">
      <h5 class="brs">文件信息 </h5>
    </div>
    <div class="widget-content nopadding">
      <table class="table table-bordered table-condensed table-hover">
        <tbody>
          <tr>
            <td style="width:74px;">文 件 名</td>
            <td><?php echo $rs->filename; ?>.<?php echo $rs->ext; ?></td>
          </tr>
          <tr>
            <td>文件路径</td>
            <td><?php echo $rs->path; ?></td>
          </tr>
          <tr>
            <td>原文件名</td>
            <td><?php echo $rs->ofilename; ?></td>
          </tr>
          <tr>
            <td>文件类型</td>
            <td><?php echo iFS::icon($rs->filename . '.' . $rs->ext,ACP_UI); ?> .<?php echo $rs->ext; ?></td>
          </tr>
          <tr>
            <td>保存方式</td>
            <td><?php echo $rs->type ? "远程" : "本地上传"; ?></td>
          </tr>
          <tr>
            <td>保存时间</td>
            <td><?php echo get_date($rs->time, 'Y-m-d H:i:s'); ?></td>
          </tr>
        </tbody>
      </table>
      <?php } ?>
      <div class="form-actions mt0 mb0">
        <form action="<?php echo APP_FURI; ?>&do=upload&id=<?php echo $this->id; ?>" method="post" enctype="multipart/form-data" target="iPHP_FRAME">
          <input type="file" name="upfile" class="hide">
          <input type="hidden" name="udir" value="<?php echo $_GET['dir']; ?>">
          <div class="input-prepend input-append"> <span class="add-on">不添加水印</span><span class="add-on">
            <input type="checkbox" name="watermark" value="0">
            </span><a id="upload" class="btn btn-primary"><i class="fa fa-upload"></i> 选择文件</a></div>
        </form>
      </div>
    </div>
  </div>
  <?php if($this->from!='modal'){?>
</div>
<?php } ?>
<?php iACP::foot(); ?>
