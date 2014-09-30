<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: push.add.php 2365 2014-02-23 16:26:27Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
iACP::head();
?>
<script type="text/javascript">
$(function(){
	$("#title").focus();

	$("#iCMS-push").submit(function(){
		if($("#cid option:selected").attr("value")=="0"){
			iCMS.alert("请选择所属版块");
			$("#cid").focus();
			window.scrollTo(0,0);
			return false;
		}
		if($("#title").val()==''){
			iCMS.alert("1.标题必填!");
			$("#title").focus();
			return false;
		}
	});
});
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-plus-square"></i> </span>
      <h5><?php echo empty($id)?'添加':'修改' ; ?>推送</h5>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" enctype="multipart/form-data" class="form-inline" id="iCMS-push" target="iPHP_FRAME">
        <input name="id" type="hidden" value="<?php echo $id ; ?>" />
        <input name="userid" type="hidden" value="<?php echo $rs['userid'] ; ?>" />
        <input name="_cid" type="hidden" value="<?php echo $rs['cid'] ; ?>" />
        <div id="push-add" class="tab-content">
          <div class="input-prepend"> <span class="add-on">版块</span>
            <?php if($cata_option){  ?>
            <select name="cid" id="cid" class="chosen-select span3">
              <option value="0"> == 请选择所属版块 == </option>
              <?php echo $cata_option;}else{  ?>
              <select onclick="window.location.replace('<?php echo APP_URI; ?>category&do=add');">
              <option value="0"> == 暂无版块请先添加 == </option>
              <?php }  ?>
            </select>
          </div>
		  <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">属性</span>
            <select name="pid" id="pid" class="chosen-select span3">
              <option value="0">普通推送[pid='0']</option>
              <?php echo iACP::getProp("pid",$rs['pid']) ; ?>
            </select>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">编辑</span>
            <input id="'editor" class="span3" value="<?php echo $rs['editor'] ; ?>" name="editor" type="text"/>
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">时间</span>
            <input id="addtime" class="ui-datepicker span3" value="<?php echo $rs['addtime'] ; ?>"  name="addtime" type="text" />
          </div>
          <div class="clearfloat mb10"></div>
          <div class="input-prepend"> <span class="add-on">排序</span>
            <input id="ordernum" class="span3" value="<?php echo _int($rs['ordernum']) ; ?>" name="ordernum" type="text"/>
          </div>
          <fieldset>
            <legend>1</legend>
            <div class="input-prepend"> <span class="add-on">标 题</span>
              <input type="text" name="title" class="span6" id="title" value="<?php echo $rs['title'] ; ?>"/>
            </div>
            <span class="label label-important">必填</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">缩略图</span>
              <input type="text" name="pic" class="span6" id="pic" value="<?php echo $rs['pic'] ; ?>"/>
              <?php iACP::picBtnGroup("pic");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">链 接</span>
              <input type="text" name="url" class="span6" id="url" value="<?php echo $rs['url'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend" style="width:100%;"><span class="add-on">摘 要</span>
              <textarea name="description" id="description" class="span6" style="height: 150px;"><?php echo $rs['description'] ; ?></textarea>
            </div>
          </fieldset>
          <fieldset>
            <legend>2</legend>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标 题</span>
              <input type="text" name="title2" class="span6" id="title2" value="<?php echo $rs['title2'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">缩略图</span>
              <input type="text" name="pic2" class="span6" id="pic2" value="<?php echo $rs['pic2'] ; ?>"/>
              <?php iACP::picBtnGroup("pic2");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">链 接</span>
              <input type="text" name="url2" class="span6" id="url2" value="<?php echo $rs['url2'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend" style="width:100%;"><span class="add-on">摘 要</span>
              <textarea name="description2" id="description2" class="span6" style="height: 150px;"><?php echo $rs['description2'] ; ?></textarea>
            </div>
          </fieldset>
          <fieldset>
            <legend>3</legend>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标 题</span>
              <input type="text" name="title3" class="span6" id="title3" value="<?php echo $rs['title3'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">缩略图</span>
              <input type="text" name="pic3" class="span6" id="pic3" value="<?php echo $rs['pic3'] ; ?>"/>
              <?php iACP::picBtnGroup("pic3");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">链 接</span>
              <input type="text" name="url3" class="span6" id="url3" value="<?php echo $rs['url3'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend" style="width:100%;"><span class="add-on">摘 要</span>
              <textarea name="description3" id="description3" class="span6" style="height: 150px;"><?php echo $rs['description3'] ; ?></textarea>
            </div>
          </fieldset>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php iACP::foot();?>
