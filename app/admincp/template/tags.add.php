<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: tags.add.php 2393 2014-04-09 13:14:23Z coolmoo $
 */
defined('iCMS') OR exit('What are you doing?'); 
iACP::head();
?>
<script type="text/javascript">
$(function(){
	iCMS.select('cid',"<?php echo $rs['cid'] ; ?>");
	iCMS.select('tcid',"<?php echo $rs['tcid'] ; ?>");
	iCMS.select('pid',"<?php echo $rs['pid']?$rs['pid']:0 ; ?>");
	iCMS.select('status',"<?php echo $rs['status'] ; ?>");
	$("#iCMS-tags").submit(function(){
		if($("#cid option:selected").val()=="0"){
			alert("请选择所属栏目");
			$("#cid").focus();
			return false;
		}
		if($("#name").val()==''){
			alert("标签名称不能为空!");
			$("#name").focus();
			return false;
		}
	});
  $(document).on("click",".delprop",function(){
      $(this).parent().parent().remove();
  });
  $(".addprop").click(function(){
    var href = $(this).attr("href");
    var tb  = $(href),tbody=$("tbody",tb);
    var ntr=$(".aclone",tb).clone(true).removeClass("hide aclone");
    $('input',ntr).removeAttr("disabled");
    ntr.appendTo(tbody);
    return false;
  });
});
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-pencil"></i> </span>
      <h5 class="brs"><?php echo ($id?'添加':'修改'); ?>标签</h5>
      <ul class="nav nav-tabs" id="tag-add-tab">
        <li class="active"><a href="#tag-add-base" data-toggle="tab"><i class="fa fa-info-circle"></i> 基本信息</a></li>
        <li><a href="#tag-add-metadata" data-toggle="tab"><i class="fa fa-cog"></i> 扩展属性</a></li>
      </ul>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="iCMS-tags" target="iPHP_FRAME">
        <input name="id" type="hidden" value="<?php echo $this->id ; ?>" />
        <input name="uid" type="hidden" value="<?php echo $rs['uid'] ; ?>" />

        <input name="_cid" type="hidden" value="<?php echo $rs['cid'] ; ?>" />
        <input name="_tcid" type="hidden" value="<?php echo $rs['tcid'] ; ?>" />
        <input name="_pid" type="hidden" value="<?php echo $rs['pid'] ; ?>" />

        <div id="tags-add" class="tab-content">
          <div id="tag-add-base" class="tab-pane active">
            <div class="input-prepend"> <span class="add-on">所属栏目</span>
              <select name="cid" id="cid" class="chosen-select span6" multiple="multiple" data-placeholder="请选择栏目(可多选)...">
                <option value="0"> ==== 默认 ==== </option>
                <?php echo $this->categoryApp->select('ca',$rs['cid'],0,1,true);?>
              </select>
            </div>
            <span class="help-inline">本标签所属的栏目</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标签分类</span>
              <select name="tcid[]" id="tcid" class="chosen-select span6" multiple="multiple" data-placeholder="请选择标签分类(可多选)...">
                <option value="0"> ==== 默认分类 ==== </option>
                <?php echo $this->tagcategory->select('ca',$rs['tcid'],0,1,true);?>
              </select>
            </div>
            <span class="help-inline">本标签所属的标签分类</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标签属性</span>
              <select name="pid[]" id="pid" class="chosen-select span6" multiple="multiple" data-placeholder="请选择标签属性(可多选)...">
                <option value="0">普通标签[pid='0']</option>
                <?php echo iACP::getProp("pid") ; ?>
              </select>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标签名称</span>
              <input type="text" name="name" class="span6" id="name" value="<?php echo $rs['name'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">唯一标识</span>
              <input type="text" name="tkey" class="span6" id="tkey" value="<?php echo $rs['tkey'] ; ?>"/>
            </div>
            <span class="help-inline">用于伪静态或者静态生成 唯一性<br />
            留空则系统按名称拼音生成</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">SEO 标题</span>
              <input type="text" name="seotitle" class="span6" id="seotitle" value="<?php echo $rs['seotitle'] ; ?>" />
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">副 标 题</span>
              <input type="text" name="subtitle" class="span6" id="subtitle" value="<?php echo $rs['subtitle'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">关 键 字</span>
              <input type="text" name="keywords" class="span6" id="keywords" value="<?php echo $rs['keywords'] ; ?>" onkeyup="javascript:this.value=this.value.replace(/，/ig,',');"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">缩 略 图</span>
              <input type="text" name="pic" class="span6" id="pic" value="<?php echo $rs['pic'] ; ?>"/>
              <?php iACP::picBtnGroup("pic");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标签描述</span>
              <textarea name="description" id="description" class="span6" style="height: 150px;width:600;"><?php echo $rs['description'] ; ?></textarea>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">自定义链接</span>
              <input type="text" name="url" class="span6" id="url" value="<?php echo $rs['url'] ; ?>"/>
            </div>
            <span class="help-inline">填写自定义链接后,标签唯一标识将会由系统生成</span>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">相关标签</span>
              <input type="text" name="related" class="span6" id="related" value="<?php echo $rs['related'] ; ?>" onkeyup="javascript:this.value=this.value.replace(/，/ig,',');"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">标签模板</span>
              <input type="text" name="tpl" class="span3" id="tpl" value="<?php echo $rs['tpl'] ; ?>"/>
            <a href="<?php echo __ADMINCP__; ?>=files&do=seltpl&from=modal&click=file&target=tpl" class="btn" data-toggle="modal" title="选择模板文件"><i class="fa fa-search"></i> 选择</a> </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标签权重</span>
              <input type="text" name="weight" class="span1" id="weight" value="<?php echo $rs['weight'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标签排序</span>
              <input id="ordernum" class="span1" value="<?php echo $rs['ordernum'] ; ?>" name="ordernum" type="text"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标签状态</span>
              <div class="switch" data-on-label="启用" data-off-label="禁用">
                <input type="checkbox" data-type="switch" name="status" id="status" <?php echo $rs['status']?'checked':''; ?>/>
              </div>
            </div>
          </div>
          <div id="tag-add-metadata" class="tab-pane hide">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>名称<span>(只能由英文字母、数字或_-组成(不支持中文))<span></th>
                  <th>值</th>
                </tr>
              </thead>
              <tbody>
                <?php if($rs['metadata'])foreach((array)$rs['metadata'] AS $mKey=>$mvalue){?>
                <tr>
                  <td><input name="metadata[key][]" type="text" value="<?php echo $mKey;?>" class="span3 tip" title="tag.meta.<?php echo $mKey;?>"/></td>
                  <td><input name="metadata[value][]" type="text" value="<?php echo $mvalue;?>" class="span6" />
                <a class="btn delAttr"><i class="fa fa-trash-o"></i> 删除</a></td>
              </tr>
              <?php }?>
            </tbody>
            <tfoot>
            <tr class="hide aclone">
              <td><input name="metadata[key][]" type="text" disabled="disabled" class="span3" value=""/></td>
              <td><input name="metadata[value][]" type="text" disabled="disabled" class="span6" value="" />
            <a class="btn delprop"><i class="fa fa-trash-o"></i> 删除</a></td>
          </tr>
          <tr>
            <td colspan="2"><a href="#tag-add-metadata" class="btn addprop"/>增加加附加属性</a></td>
          </tr>
          </tfoot>
        </table>
      </div>
    </div>
    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
    </div>
  </form>
</div>
</div>
</div>
