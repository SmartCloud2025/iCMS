<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: files.manage.php 179 2013-03-29 03:21:28Z coolmoo $
 */
defined('iCMS') OR exit('What are you doing?'); 
iACP::head();
?>
<script type="text/javascript">
$(function(){
	$('input[type=file]').uniform();
	<?php if($this->click){?>
    $('[data-click="<?php echo $this->click;?>"]').click(function() {
    	if (this.checked) {
			var state	= window.parent.modal_<?php echo $this->callback;?>('<?php echo $this->target; ?>',this);
	    	if(!state){
	    		window.parent.iCMS_MODAL.destroy();
	    	}
    	}
    });
    <?php }?>
    $('#mkdir').click(function() {
		$.dialog({
		    follow: document.getElementById('mkdir'),
		    title: '创建新目录',
		    content: document.getElementById('mkdir-box'),
		    button: [{
		    	id: 'mkdir-btn',
		    	value: '创建',
				callback: function () {
			        var a = $("#newdirname"),n = a.val(),d=this;
			        if(n==""){
			        	alert("请输入目录名称!");
			        	a.focus();
			        	return false;
			        }else{
			        	$.post('<?php echo APP_URI; ?>&do=mkdir',{name: n,'pwd':'<?php echo $pwd;?>'},
			        	function(j){
			        		if(j.code){ 
				        		d.content(j.msg)
			        			.button({id: 'mkdir-btn',value: '完成',callback: function () {window.location.reload();}});
			        			window.setTimeout(function(){
			        				window.location.reload();
								},3000);
			        		}else{
			        			alert(j.msg);
			        			a.focus();
			        			return false;
			        		}
			        	},"json"); 
			        }
			        return false;
				}}]
		});
    });
});
</script>
<style>
.op { text-align: right !important; padding-right: 28px !important; }
#files-explorer tbody .checker { margin-left: 6px !important; }
#files-explorer .pwd { float:left; padding: 5px; margin: 6px 15px 0 10px; }
#files-explorer .pwd a { color: #fff; }
#files-explorer td { line-height: 2em; }
#mkdir-box, #upload-box { display:none; }
</style>
<?php if($this->from!='modal'){?>
<div class="iCMS-container">
  <?php } ?>
  <div class="widget-box<?php if($this->from=='modal'){?> widget-plain<?php } ?>" id="files-explorer">
    <div class="widget-title"> <span class="icon">
      <input type="checkbox" class="checkAll" data-target="#files-explorer" />
      </span>
      <h5 class="brs">文件管理</h5>
      <span class="label label-info pwd"><a href="<?php echo $URI.$parent; ?>" class="tip-bottom" title="当前路径 ">iCMS::/<?php echo $pwd;?></a></span>
      <div class="buttons"> <a href="#" class="btn btn-mini btn-success" id="mkdir"><i class="fa fa-folder"></i> 创建新目录</a> <a href="<?php echo APP_URI; ?>&do=multi&from=modal&dir=<?php echo $pwd;?>" title="上传文件" data-toggle="modal" data-meta='{"width":"98%","height":"580px"}' class="btn btn-mini btn-primary" id="upload"> <i class="fa fa-upload"></i> 上传文件</a> </div>
    </div>
    <div class="widget-content nopadding">
      <form action="<?php echo APP_FURI; ?>&do=batch" method="post" class="form-inline" id="<?php echo APP_FORMID;?>" target="iPHP_FRAME">
        <?php if(empty($dirRs) && empty($fileRs)){
          	$parentShow	= true;
          ?>
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th><i class="fa fa-arrows-v"></i></th>
              <th style="width:300px;"></th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td></td>
              <td colspan="2"><a href="<?php echo $URI.$parent; ?>"><i class="fa fa-angle-double-up"></i> 返回上级</a></td>
            </tr>
          </tbody>
        </table>
        <?php }  ?>
        <?php if($dirRs){ ?>
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th><i class="fa fa-arrows-v"></i></th>
              <th style="width:320px;">目录</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php if($dir){
          	$parentShow	= true;
          ?>
            <tr>
              <td style="padding:3px;2px;1px;2px;"><span class="label label-info">选择</span></td>
              <td colspan="2"><a href="<?php echo $URI.$parent; ?>"><i class="fa fa-angle-double-up"></i> 返回上级</a></td>
            </tr>
            <?php }  ?>
            <?php
	            $_count		= count($dirRs);
	            for($i=0;$i<$_count;$i++){
            ?>
            <tr>
              <td><input type="checkbox" value="<?php echo $dirRs[$i]['path'] ; ?>" data-click="dir"/></td>
              <td><a href="<?php echo $dirRs[$i]['url']; ?>" class="dirname"><?php echo $dirRs[$i]['name'] ; ?></a></td>
              <td class="op"><a class="btn btn-small mvr"><i class="fa fa-edit"></i> 重命名</a> <a href="<?php echo APP_URI; ?>&do=add&from=modal" class="btn btn-small" data-toggle="modal" data-meta='{"width":"600px","height":"360px"}' title="上传"><i class="fa fa-upload"></i> 上传</a> <a href="<?php echo APP_FURI; ?>&do=del&id=<?php echo $rs[$i]['id'] ; ?>&indexid=<?php echo $rs[$i]['indexid'] ; ?>" target="iPHP_FRAME" class="del btn btn-small" title='永久删除'  onclick="return confirm('确定要删除?');"/><i class="fa fa-trash-o"></i> 删除</a></td>
            </tr>
            <?php }  ?>
          </tbody>
        </table>
        <?php }  ?>
        <?php if($fileRs){ ?>
        <table class="table table-bordered table-condensed table-hover">
          <thead>
            <tr>
              <th><span class="icon">
                <input type="checkbox" class="checkAll" data-target="#files-explorer" />
                </span></th>
              <th style="width:320px;">文件名 <span class="label label-important">提示:点击多选框可选择</span></th>
              <th>类型</th>
              <th>大小</th>
              <th style="width:130px;">最后修改时间</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <?php if($parent && !$parentShow){ ?>
            <tr>
              <td></td>
              <td colspan="7"><a href="<?php echo $URI.$parent; ?>"><i class="fa fa-angle-double-up"></i> 返回上级</a></td>
            </tr>
            <?php }  ?>
            <?php 
            $_count		= count($fileRs);
            for($i=0;$i<$_count;$i++){
            	$icon	= iFS::icon($fileRs[$i]['name'],iCMS_UI);
            ?>
            <tr>
              <td><input type="checkbox" value="<?php echo $fileRs[$i]['path'] ; ?>" url="<?php echo $fileRs[$i]['url'] ; ?>"  data-click="file"/></td>
              <td><?php if (in_array(strtolower($fileRs[$i]['ext']),array('jpg','png','gif','jpeg'))){?>
                <a href="###" class="tip-right" title="<img src='<?php echo $fileRs[$i]['url'] ; ?>' width='120px'/>"><?php echo $icon ; ?> <?php echo $fileRs[$i]['name'] ; ?></a>
                <?php }else{?>
                <?php echo $icon ; ?> <?php echo $fileRs[$i]['name'] ; ?>
                <?php } ?></td>
              <td><?php echo $fileRs[$i]['ext'] ; ?></td>
              <td><?php echo $fileRs[$i]['size'] ; ?></td>
              <td><?php echo $fileRs[$i]['modified'] ; ?></td>
              <td class="op"><a class="btn btn-small" href="<?php echo $href; ?>" data-toggle="modal" title="查看"><i class="fa fa-eye"></i> 查看</a> <a class="btn btn-small" href="<?php echo $href; ?>" data-toggle="modal" title="查看"><i class="fa fa-upload"></i> 上传</a> <a class="btn btn-small" href="<?php echo $href; ?>" data-toggle="modal" title="查看"><i class="fa fa-trash-o"></i> 删除</a></td>
            </tr>
            <?php }  ?>
          </tbody>
        </table>
        <?php }  ?>
      </form>
    </div>
  </div>
  <?php if($this->from!='modal'){?>
</div>
<?php } ?>
<div id="mkdir-box">
  <input class="span2" id="newdirname" type="text" placeholder="请输入目录名称">
</div>
<?php iACP::foot();?>
