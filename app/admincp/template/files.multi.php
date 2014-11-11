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
<script type="text/javascript">
var settings = {
    pick: {
        id: '#filePicker',
        label: '点击选择图片'
    },
    fileVal:'upfile',
    formData: {"watermark":<?php echo iCMS::$config['watermark']['enable']?'true':'false'; ?>,"udir":"<?php echo $_GET['dir']; ?>"},
    dnd: '#dndArea',
    paste: '#uploader',
    swf: './app/admincp/ui/webuploader/Uploader.swf',
    chunked: false,
    chunkSize: 512 * 1024,
    server: '<?php echo APP_URI; ?>&do=upload&format=json',
    // runtimeOrder: 'flash',

    // accept: {
    //     title: 'Images',
    //     extensions: 'gif,jpg,jpeg,bmp,png',
    //     mimeTypes: 'image/*'
    // },

    // 禁掉全局的拖拽功能。这样不会出现图片拖进页面的时候，把图片打开。
    disableGlobalDnd: true,
    fileNumLimit: 300,
    fileSizeLimit: 200 * 1024 * 1024,    // 200 M
    fileSingleSizeLimit: 50 * 1024 * 1024    // 50 M
}
</script>
<link rel="stylesheet" href="./app/admincp/ui/webuploader/webuploader.css" type="text/css" />
<link rel="stylesheet" href="./app/admincp/ui/webuploader/style.css" type="text/css" />
<script type="text/javascript" src="./app/admincp/ui/webuploader/webuploader.min.js"></script>
<script type="text/javascript" src="./app/admincp/ui/webuploader/upload.js"></script>

<div class="widget-box  widget-plain">
  <div class="widget-title"> <span class="icon">
    <input type="checkbox" class="checkAll" data-target="#files-swfupload" />
    </span>
    <h5>文件列表</h5>
    <span class="label label-success" id="FilesUploaded" num="0" style="margin-top: 10px;">0个文件已上传</span> </div>
    <div class="widget-content nopadding">
        <div id="uploader">
            <div class="queueList">
                <div id="dndArea" class="placeholder">
                    <div id="filePicker"></div>
                    <p>或将照片拖到这里，单次最多可选300张</p>
                </div>
            </div>
            <div class="statusBar" style="display:none;">
                <div class="progressBar">
                    <span class="text">0%</span>
                    <span class="percentage"></span>
                </div>
                <div class="info"></div>
                <div class="btns">
                    <div class="input-prepend"><span class="add-on">水印</span>
                      <div class="switch" data-on-label="添加" data-off-label="不添加">
                        <input type="checkbox" data-type="switch" name="watermark" id="watermark" <?php echo iCMS::$config['watermark']['enable']?'checked':''; ?>/>
                      </div>
                    </div>
                    <div id="filePicker2"></div>
                    <div class="uploadBtn"><i class="fa fa-upload"></i> 开始上传</div>
                </div>
            </div>
        </div>
    </div>
  </div>
</div>
<?php iACP::foot();?>
