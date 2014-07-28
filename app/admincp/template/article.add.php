<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: article.add.php 2412 2014-05-04 09:52:07Z coolmoo $
 */
defined('iCMS') OR exit('What are you doing?'); 
iACP::head();
?>
<script type="text/javascript" charset="utf-8" src="app/editor/iCMS.editor-6.0.0.js"></script>
<script type="text/javascript" charset="utf-8" src="app/editor/ueditor/ueditor.all.min.js"></script>
<script type="text/javascript">
$(function(){
	window.iCMS.fileTypes	= "<?php echo '*.'.str_replace(',',';*.',iCMS::$config['FS']['allow_ext']);?>";
  iCMS.editor.create();
  $("#title").focus();
	$(".iCMS-editor-page").change(function(){
		$(".iCMS-editor").hide();
		$("#editor-"+this.value).show();
    iCMS.editor.create(this.value);
		iCMS.editor[this.value].focus();
		$(".iCMS-editor-page").val(this.value).trigger("chosen:updated");
	});
  iCMS.select('pid',"<?php echo $rs['pid']?$rs['pid']:0 ; ?>");
  iCMS.select('cid',"<?php echo $rs['cid']; ?>");
  iCMS.select('scid',"<?php echo $rs['scid']; ?>");
	$("#cid").change(function() {
    var cid = this.value;
		$.getJSON("<?php echo APP_URI; ?>",{'do':'getmeta','cid':cid},function(prop){
      $.each(prop,function(n,v){
        var mdId='md_'+cid+'_'+n;
        if($("#"+mdId).length==0){
          var MD_Box='<div class="MD_Box" id="'+mdId+'"><div class="input-prepend input-append">  <span class="add-on">'+v+'</span><textarea  id="md_'+n+'" name="metadata['+n+']" class="metadata span6" style="height: 100px;"></textarea><a class="btn btn-small delMD"><i class="fa fa-trash-o"></i> 删除</a></div><div class="clearfloat mb10"></div></div>';
          $("#article-add-metadata").html(MD_Box);
        }
      })
		}); 
	});
  $("#article-add-metadata").on("click",".delMD",function(){
      $(this).parent().parent().remove();
  });
  $('#ischapter').click(function(){
    var checkedStatus = $(this).prop("checked"),chapter = $("input[name=chapter]").val();
    $('#chapterText').text(checkedStatus?'章节标题':'副标题')
    if(!checkedStatus && chapter>1){
      return confirm('您之前添加过其它章节!确定要取消章节模式?');
    }
  })
	$("#iCMS-article").submit(function(){
		if($("#cid option:selected").val()=="0"){
			alert("请选择所属栏目");
			$("#cid").focus();
			return false;
		}
		if($("#title").val()==''){
			alert("标题不能为空!");
			$("#title").focus();
			return false;
		}
		if($("#url").val()==''){
			var n=$(".iCMS-editor-page:eq(0) option:first").val(),ed = iCMS.editor[n];
			if(!ed.hasContents()){
				alert("第"+n+"页内容不能为空!");
				$('#editor-'+n).show();
				$(".iCMS-editor-page").val(n).trigger("chosen:updated");
				ed.focus();
				return false;
			}
		}
    if($('#ischapter').prop("checked") && $("#subtitle").val()==''){
      alert("章节模式下 章节标题不能为空!");
      $("#subtitle").focus();
      return false;
    }
	}); 
	
});

function addEditorPage(){
	//iCMSed.cleanup(iCMSed.id);
	var index	= parseInt($(".iCMS-editor-page option:last").val()),n	= index+1;
	$(".iCMS-editor").hide();
	$("#editor-"+index).after('<div class="iCMS-editor" id="editor-'+n+'">'+unescape('%3Ctextarea type="text/plain" id="iCMS-editor-'+n+'" name="body[]"%3E%3C/3Ctextarea%3E')+'</div>');
	$(".iCMS-editor-page").append('<option value="'+n+'">第 '+n+' 页</option>').val(n).trigger("chosen:updated");
	iCMS.editor.create(n);
	iCMS.editor[n].focus();
}
function delEditorPage(){
	if($(".iCMS-editor-page:eq(0) option").length==1) return;
	
	var s = $(".iCMS-editor-page option:selected"),
    i = s.val(),p = s.prev(),n = s.next();
	if(n.length){
    var index = n.val();
	}else if(p.length){
    var index = p.val();
	}
  s.remove();
	$(".iCMS-editor-page").val(index).trigger("chosen:updated");
	$("#editor-"+index).show();
	iCMS.editor.Id	= index;
	iCMS.editor[index].focus();

	iCMS.editor[i].destroy();
  $("#editor-"+i).remove();
  $("#iCMS-editor-"+i).remove();
}
function modal_picture(el,a){
  if(!a.checked) return;
  
  var i       = iCMS.editor.Id,
  ed          = iCMS.editor[i],
  url         = $(a).attr("url");
  // if(a.checked){
  var imgObj  = {};
  imgObj.src  = url;
  imgObj._src = url;
	ed.fireEvent('beforeInsertImage', imgObj);
	ed.execCommand("insertImage", imgObj);
  _modal_dialog("继续选择");
  // }else{
  //   var html = ed.getContent(),
  //   img = '<img src="'+url+'"/>';

  //   html = html.replace(img,'');
  //   log(html);
  // }
	return true;
}
function modal_sweditor(el){
  if(!el.checked) return;

  var e    = $(el),
  image    = e.attr('_image'),
  fileType = e.attr('_fileType'),
  original = e.attr('_original'),
  url      = e.attr('url'),  
  ed       = iCMS.editor[iCMS.editor.Id];

  if(url=='undefined') return;
  var html = '<p class="attachment icon_'+fileType+'"><a href="'+url+'" target="_blank">' + original + '</a></p>';
	
  if(image=="1") html='<p><img src="'+url+'" /></p>';

	ed.execCommand("insertHTML",html);
  _modal_dialog("继续上传");
}
function _modal_dialog(cancel){
  $.dialog({
        id: 'iPHP_DIALOG',width: 360,height: 150,fixed: true,time:3000,
        title: 'iCMS - 提示信息',
        content: '<div class=\"iPHP-msg\"><span class=\"label label-success\"><i class=\"fa fa-check\"></i> 插入成功!</span></div>',
        okValue: '完成',
        ok: function () {
          window.iCMS_MODAL.destroy();
          return true;
        },
        cancelValue: cancel,
        cancel: function(){
          return true;
      }
  });  
}
</script>

<div class="iCMS-container">
  <div class="widget-box">
    <div class="widget-title"> <span class="icon"> <i class="fa fa-pencil"></i> </span>
      <h5 class="brs"><?php echo empty($this->id)?'添加':'修改' ; ?>文章</h5>
      <ul class="nav nav-tabs" id="article-add-tab">
        <li class="active"><a href="#article-add-base" data-toggle="tab"><i class="fa fa-info-circle"></i> 基本信息</a></li>
        <li><a href="#article-add-publish" data-toggle="tab"><i class="fa fa-rocket"></i> 发布设置</a></li>
        <li><a href="#article-add-metadata" data-toggle="tab"><i class="fa fa-cog"></i> 扩展属性</a></li>
      </ul>
    </div>
    <div class="widget-content nopadding iCMS-article-add">
      <form action="<?php echo APP_FURI; ?>&do=save" method="post" class="form-inline" id="iCMS-article" target="iPHP_FRAME">
        <input name="_cid" type="hidden" value="<?php echo $rs['cid'] ; ?>" />
        <input name="_scid" type="hidden" value="<?php echo $rs['scid']; ?>" />
        <input name="_tags" type="hidden" value="<?php echo $rs['tags']; ?>" />
        <input name="_pid" type="hidden" value="<?php echo $rs['pid']; ?>" />

        <input name="aid" type="hidden" value="<?php echo $this->id ; ?>" />
        <input name="adid" type="hidden" value="<?php echo $adRs['id']; ?>" />
        <input name="status" type="hidden" value="<?php echo $rs['status'] ; ?>" />
        <input name="userid" type="hidden" value="<?php echo $rs['userid'] ; ?>" />
        <input name="postype" type="hidden" value="<?php echo $rs['postype'] ; ?>" />
        <input name="REFERER" type="hidden" value="<?php echo $REFERER ; ?>" />
        <input name="chapter" type="hidden" value="<?php echo $rs['chapter']; ?>" />
        <div id="article-add" class="tab-content">
          <div id="article-add-base" class="tab-pane active">
            <div class="input-prepend"> <span class="add-on">栏 目</span>
              <?php if($cata_option){  ?>
              <select name="cid" id="cid" class="chosen-select span3">
                <option value="0"> == 请选择所属栏目 == </option>
                <?php echo $cata_option;}else{  ?>
                <select onclick="window.location.replace('<?php echo __ADMINCP__; ?>=category&do=add');">
                <option value="0"> == 暂无栏目请先添加 == </option>
                <?php }  ?>
              </select>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">副栏目</span>
              <?php if($cata_option){  ?>
              <select name="scid[]" id="scid" class="chosen-select span6" multiple="multiple"  data-placeholder="请选择副栏目(可多选)...">
                <?php echo $cata_option;}else{  ?>
                <select onclick="window.location.replace('<?php echo __ADMINCP__; ?>=category&do=add');">
                <option value="0"> == 暂无栏目请先添加 == </option>
                <?php }  ?>
              </select>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">属 性</span>
              <select name="pid[]" id="pid" class="chosen-select span6" multiple="multiple">
                <option value="0">普通文章[pid='0']</option>
                <?php echo iACP::getProp("pid") ; ?>
              </select>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标 题</span>
              <input type="text" name="title" class="span6" id="title" value="<?php echo $rs['title'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">短标题</span>
              <input type="text" name="stitle" class="span6" id="stitle" value="<?php echo $rs['stitle'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">出 处</span>
              <input type="text" name="source" class="span6" id="source" value="<?php echo $rs['source'] ; ?>"/>
              <?php iACP::propBtn("source");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">作 者</span>
              <input type="text" name="author" class="span2" id="author" value="<?php echo $rs['author'] ; ?>"/>
              <?php iACP::propBtn("author");?>
            </div>
            <div class="input-prepend"> <span class="add-on">编 辑</span>
              <input type="text" name="editor" class="span2" id="editor" value="<?php echo $rs['editor'] ; ?>"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">缩略图</span>
              <input type="text" name="pic" class="span6" id="pic" value="<?php echo $rs['pic'] ; ?>"/>
              <?php iACP::picBtnGroup("pic");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">缩略图2</span>
              <input type="text" name="mpic" class="span6" id="mpic" value="<?php echo $rs['mpic'] ; ?>"/>
              <?php iACP::picBtnGroup("mpic");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">缩略图3</span>
              <input type="text" name="spic" class="span6" id="spic" value="<?php echo $rs['spic'] ; ?>"/>
              <?php iACP::picBtnGroup("spic");?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">关键字</span>
              <input type="text" name="keywords" class="span6" id="keywords" value="<?php echo $rs['keywords'] ; ?>" onkeyup="javascript:this.value=this.value.replace(/，/ig,',');"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">标 签</span>
              <input type="text" name="tags" class="span6" id="tags" value="<?php echo $rs['tags'] ; ?>" onkeyup="javascript:this.value=this.value.replace(/，/ig,',');"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend" style="width:100%;"><span class="add-on">摘 要</span>
              <textarea name="description" id="description" class="span6" style="height: 150px;"><?php echo $rs['description'] ; ?></textarea>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append">
              <div class="btn-group">
                <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> 提交</button>
                <a class="btn" href="javascript:addEditorPage();"><i class="fa fa-file-o"></i> 新增一页</a> <a class="btn" href="javascript:delEditorPage();"><i class="fa fa-times-circle"></i> 删除当前页</a> <a class="btn" href="javascript:iCMS.editor.insPageBreak();"><i class="fa fa-ellipsis-h"></i> 插入分页符</a> <a class="btn" href="javascript:iCMS.editor.delPageBreakflag();"><i class="fa fa-ban"></i> 删除分页符</a> <a class="btn" href="javascript:iCMS.editor.cleanup();"><i class="fa fa-magic"></i> 自动排版</a> </div>
              <div class="btn-group"><a class="btn" href="<?php echo __ADMINCP__; ?>=files&do=multi&from=modal&callback=sweditor" data-toggle="modal" title="批量上传"><i class="fa fa-upload"></i> 批量上传</a> <a class="btn" href="<?php echo __ADMINCP__; ?>=files&do=picture&from=modal&click=file&callback=picture" data-toggle="modal" title="从网站选择图片"><i class="fa fa-picture-o"></i> 从网站选择</a> </div>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on"><i class="fa fa-building-o"></i> 内容</span>
              <select class="iCMS-editor-page chosen-select">
            <?php 
              $option ='';
              for($i=0;$i<$bodyCount;$i++){
                $idNum  = $i+1;
                $option .= '<option value="'.$idNum.'">第 '.$idNum.' / '.$bodyCount.' 页</option>';
              }
              echo $option;
            ?>
              </select>
            </div>
            <div class="input-prepend input-append">
              <!-- <span class="add-on wauto">
              <input name="ischapter" type="checkbox" id="ischapter" value="1" <?php if($rs['chapter']) echo 'checked="checked"'  ?>/>
              章节模式</span>  -->
              <span class="add-on wauto">
              <input name="inbox" type="checkbox" id="inbox" value="1" <?php if($rs['status']=="0")echo 'checked="checked"'  ?>/>
              存为草稿</span> 
              <span class="add-on wauto">
              <input name="remote" type="checkbox" id="remote" value="1" <?php if(iCMS::$config['publish']['remote']=="1")echo 'checked="checked"'  ?>/>
              下载远程图片</span> <span class="add-on wauto">
              <input name="dellink" type="checkbox" id="dellink" value="1"/>
              清除链接 </span> <span class="add-on wauto">
              <input name="autopic" type="checkbox" id="autopic" value="1" <?php if(iCMS::$config['publish']['autopic']=="1")echo 'checked="checked"'  ?>/>
              提取缩略图 </span> <span class="add-on wauto">
              <input name="isRedirect" type="checkbox" id="isRedirect" value="1" />
              增强图片下载 </span>
              <?php if(iCMS::$config['watermark']['enable']=="1"){ ?>
              <span class="add-on wauto">
              <input name="iswatermark" type="checkbox" id="iswatermark" value="1" />
              不添加水印</span>
              <?php }?>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on" id="chapterText">副标题</span>
                <input type="text" name="subtitle" class="span6" id="subtitle" value="<?php echo $adRs['subtitle'] ; ?>" />
              </div>
            <div class="clearfloat mb10"></div>
            <?php for($i=0;$i<$bodyCount;$i++){
                $idNum  = $i+1;
            ?>          
            <div class="iCMS-editor<?php if($i){ echo ' hide';}?>" id="editor-<?php echo $idNum;?>">
              <textarea type="text/plain" id="iCMS-editor-<?php echo $idNum;?>" name="body[]"><?php echo $bodyArray[$i];?></textarea>
            </div>
            <?php }?>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on"><i class="fa fa-building-o"></i> 内容</span>
              <select class="iCMS-editor-page chosen-select">
              <?php echo $option;?>
              </select>
            </div>
            <div class="input-prepend input-append">
              <div class="btn-group"> <a class="btn" href="javascript:addEditorPage();"><i class="fa fa-file-o"></i> 新增一页</a> <a class="btn" href="javascript:delEditorPage();"><i class="fa fa-times-circle"></i> 删除当前页</a> <a class="btn" href="javascript:iCMS.editor.insPageBreak();"><i class="fa fa-ellipsis-h"></i> 插入分页符</a> <a class="btn" href="javascript:iCMS.editor.delPageBreakflag();"><i class="fa fa-ban"></i> 删除分页符</a> <a class="btn" href="javascript:iCMS.editor.cleanup();"><i class="fa fa-magic"></i> 自动排版</a> </div>
              <div class="btn-group"><a class="btn" href="<?php echo __ADMINCP__; ?>=files&do=multi&from=modal&callback=sweditor" data-toggle="modal" title="批量上传"><i class="fa fa-upload"></i> 批量上传</a> <a class="btn" href="<?php echo __ADMINCP__; ?>=files&do=picture&from=modal&click=file&callback=picture" data-toggle="modal" title="从网站选择图片"><i class="fa fa-picture-o"></i> 从网站选择</a> </div>
            </div>
          </div>
          <div id="article-add-publish" class="tab-pane hide">
            <div class="input-prepend"> <span class="add-on">发布时间</span>
              <input id="pubdate" class="<?php echo $readonly?'':'ui-datepicker'; ?>" value="<?php echo $rs['pubdate'] ; ?>"  name="pubdate" type="text" style="width:230px" <?php echo $readonly ; ?>/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">排序</span>
              <input id="orderNum" class="span2" value="<?php echo _int($rs['orderNum']) ; ?>" name="orderNum" type="text"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">权重</span>
              <input id="top" class="span2" value="<?php echo _int($rs['top']) ; ?>" name="top" type="text"/>
            </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend input-append"> <span class="add-on">模板</span>
              <input type="text" name="tpl" class="span6" id="tpl" value="<?php echo $rs['tpl'] ; ?>"/>
              <a href="<?php echo __ADMINCP__; ?>=files&do=seltpl&from=modal&click=file&target=tpl" class="btn" data-toggle="modal" title="选择模板文件"><i class="fa fa-search"></i> 选择</a> </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">自定链接</span>
              <input type="text" name="clink" class="span6" id="clink" value="<?php echo $rs['clink'] ; ?>"/>
              <span class="help-inline">只能由英文字母、数字或_-组成(不支持中文),留空则自动以标题拼音填充</span> </div>
            <div class="clearfloat mb10"></div>
            <div class="input-prepend"> <span class="add-on">外部链接</span>
              <input type="text" name="url" class="span6 tip" title="注意:文章设置外部链接后编辑器里的内容是不会被保存的哦!" id="url" value="<?php echo $rs['url'] ; ?>"/>
               </div><span class="help-inline">不填写请留空!</span>
            <div class="clearfloat mb10"></div>
          </div>
          <div id="article-add-metadata" cid="<?php echo $rs['cid'];?>" class="tab-pane hide">
            <?php if($contentprop)foreach((array)$contentprop AS $cpKey=>$cpName){?>
            <div class="MD_Box" id="md_<?php echo $rs['cid'];?>_<?php echo $cpKey ?>">
              <div class="input-prepend input-append"> <span class="add-on tip" title="<?php echo $cpName; ?>:article.meta.<?php echo $cpKey ?>"><?php echo $cpName; ?></span>
                <textarea  id="MD_<?php echo $cpKey ?>" name="metadata[<?php echo $cpKey ?>]" class="metadata span6" style="height: 100px;"><?php echo $rs['metadata'][$cpKey];?></textarea>
                <a class="btn btn-small delMD"><i class="fa fa-trash-o"></i> 删除</a> </div>
              <div class="clearfloat mb10"></div>
            </div>
            <?php }?>
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
