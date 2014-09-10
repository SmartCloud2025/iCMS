<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: filter.php 2003 2013-07-22 07:27:56Z coolmoo $
 */
defined('iPHP') OR exit('What are you doing?');
iACP::head(false);
?>
<style>
#uploadProgress .status { display:none; padding: 4px 5px; font-size: 12px; line-height: 20px; }
#uploadProgress .progress { margin-bottom:0px; display:none; }
#uploadProgress td { line-height: 24px; }
#uploadProgress .clipboard { margin-left:10px; }
.form-actions { margin-bottom:0px; }
</style>
<div class="widget-box  widget-plain">
  <div class="widget-title"> <span class="icon">
    <input type="checkbox" class="checkAll" data-target="#files-swfupload" />
    </span>
    <h5>文件列表</h5>
    <span class="label label-success" id="FilesUploaded" num="0">0个文件已上传</span> </div>
  <div class="widget-content nopadding">
    <table class="table table-bordered table-condensed table-hover"  id="files-swfupload">
      <thead>
        <tr>
          <th><i class="fa fa-arrows-v"></i></th>
          <th style="width:24px;"></th>
          <th>文件名</th>
          <th style="width:30px;">类型</th>
          <th style="width:60px;">大小</th>
          <th class="span4">上传进度</th>
          <th>操作</th>
        </tr>
      </thead>
      <tbody id="uploadProgress">
      </tbody>
    </table>
    <div class="form-actions mt0"> <a id="spanButtonPlaceHolder"></a>
      <div class="input-prepend input-append" style="margin-bottom: 33px;"> <span class="add-on"><input type="checkbox" id="watermark" value="0"> 不添加水印</span>
      <a id="startUpload" class="btn btn-primary disabled"><i class="fa fa-upload"></i> 开始上传</a> <a id="cancelUpload" class="btn"><i class="fa fa-ban"></i> 取消</a> </div>
      <?php if($_GET['callback']){?>
      <div class="pull-right"><a id="select" class="btn btn-primary btn-large disabled"><i class="fa fa-check"></i> 确认选择</a></div>
      <?php }?>
    </div>
  </div>
</div>
<script type="text/javascript" src="<?php echo ACP_UI;?>/ZeroClipboard-1.2.3.min.js"></script>
<script type="text/javascript" src="<?php echo ACP_UI;?>/swfupload-2.2.0/swfupload.js"></script>
<script type="text/javascript" src="<?php echo ACP_UI;?>/swfupload-2.2.0/swfupload.queue.js"></script>
<script type="text/javascript" src="<?php echo ACP_UI;?>/swfupload-2.2.0/fileprogress.js"></script>
<script type="text/javascript" src="<?php echo ACP_UI;?>/swfupload-2.2.0/callbacks.js"></script>
<script type="text/javascript">
    var APP_URI = '<?php echo APP_URI; ?>',watermark=$("#watermark").prop("checked");
    var swfupload,
        filesList=[];
    window.onload = function () {
        var settings = {
            upload_url:'<?php echo APP_URI; ?>&do=upload&format=json',	//附件上传服务器地址
            file_post_name:'upfile',      								//向后台提交的表单名
            flash_url:"<?php echo ACP_UI;?>/swfupload-2.2.0/swfupload.swf",
            flash9_url:"<?php echo ACP_UI;?>/swfupload-2.2.0/swfupload_fp9.swf",
            post_params:{"watermark":watermark,"udir":"<?php echo $_GET['dir']; ?>"},
            file_size_limit:"<?php echo $file_size_limit; ?>",			//文件大小限制，此处仅是前端flash选择时候的限制，具体还需要和后端结合判断
            file_types:"<?php echo '*.'.str_replace(',',';*.',iCMS::$config['FS']['allow_ext']);?>", //允许的扩展名，多个扩展名之间用分号隔开，支持*通配符
            file_types_description:"All Files",                      	//扩展名描述
            file_upload_limit:"<?php echo $file_upload_limit; ?>",		//单次可同时上传的文件数目
            file_queue_limit:"<?php echo $file_queue_limit; ?>",		//队列中可同时上传的文件数目
            custom_settings:{                                         	//自定义设置，用户可在此向服务器传递自定义变量
                progressTarget:"uploadProgress",
                startUploadId:"startUpload"
            },
            //debug:true,

            // 按钮设置
            button_image_url:"<?php echo ACP_UI;?>/swfupload-2.2.0/uploadbtn.png",
            button_width:"144",
            button_height:"41",
            button_placeholder_id:"spanButtonPlaceHolder",
            button_window_mode:"transparent",

            // 所有回调函数 in handlers.js
            swfupload_preload_handler:preLoad,
            swfupload_load_failed_handler:loadFailed,
            file_queued_handler:fileQueued,
            file_queue_error_handler:fileQueueError,
            //选择文件完成回调
            file_dialog_complete_handler:function(numFilesSelected, numFilesQueued) {
                var me = this;        //此处的this是swfupload对象
                if (numFilesQueued > 0) {
					$('#'+this.customSettings.startUploadId).removeClass("disabled")
                    .on("click",function(){
                        me.startUpload();
                        $(this).addClass("disabled")
                    })
                }
            },
            upload_start_handler:uploadStart,
            upload_progress_handler:uploadProgress,
            upload_error_handler:uploadError,
            upload_success_handler:function (file, serverData) {
                try{
                    var info = eval("("+serverData+")");
                }catch(e){}
                var progress = new FileProgress(file, this.customSettings.progressTarget);
                if(info.state=="SUCCESS"){
                    progress.setComplete(info);
                    progress.setStatus("上传成功!");
                    progress.toggleCancel(true,this,'从成功队列中移除');
                    $("#select").removeClass("disabled")
                }else{
                    progress.setError();
                    progress.setStatus(info.state);
                    progress.toggleCancel(true,this,'移除保存失败文件');
                }
            },
            //upload_resize_start_handler:uploadResize,
            //上传完成回调
            upload_complete_handler:uploadComplete,
            //队列完成回调
            queue_complete_handler:queueComplete
        };
        swfupload = new SWFUpload( settings );
    };
    $("#cancelUpload").click(function() {
    	swfupload.cancelQueue();
    })
    <?php if($_GET['callback']){?>
    $("#select").click(function() {
    	if($(this).hasClass("disabled")){
    		return;
    	}
    	var checked = $('input:checkbox:checked', $('#uploadProgress'));

    	if(!checked.length) alert("您没有选择任何文件!");

	    checked.each(function() {
			window.top.modal_<?php echo $_GET['callback'];?>(this)
	    });
	    //swfupload.destroy();
    })
    <?php }?>
    ZeroClipboard.setDefaults( { moviePath: '<?php echo ACP_UI;?>/ZeroClipboard-1.2.3.swf' } );
	$("#global-zeroclipboard-html-bridge").click(function() {
		alert("复制成功!");
	})
</script>
<?php iACP::foot();?>
