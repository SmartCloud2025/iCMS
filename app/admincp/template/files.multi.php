<?php /**
 * @package iCMS
 * @copyright 2007-2010, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 * @$Id: filter.php 2003 2013-07-22 07:27:56Z coolmoo $
 */
defined('iCMS') OR exit('What are you doing?'); 
iACP::head(false);
?>
<link rel="stylesheet" type="text/css" href="<?php echo ACP_UI;?>/webuploader/webuploader.css" />
<link rel="stylesheet" type="text/css" href="<?php echo ACP_UI;?>/webuploader/ui/style.css" />
<div id="uploader">
    <div class="queueList">
        <div id="dndArea" class="placeholder">
            <div id="filePicker"></div>
            <p>或将照片拖到这里，单次最多可选300张</p>
        </div>
    </div>
    <div class="statusBar" style="display:none;">
        <div class="webuploader_progress">
            <span class="text">0%</span>
            <span class="percentage"></span>
        </div>
        <div class="info"></div>
        <div class="btns">
            <div id="filePicker2"></div><div class="uploadBtn">开始上传</div>
        </div>
    </div>
</div>
<script type="text/javascript" src="<?php echo ACP_UI;?>/webuploader/webuploader.js"></script>
<script type="text/javascript">
(function( $ ){
    // 当domReady的时候开始初始化
    $(function() {
        var $wrap = $('#uploader'),

            // 图片容器
            $queue = $( '<ul class="filelist"></ul>' )
                .appendTo( $wrap.find( '.queueList' ) ),

            // 状态栏，包括进度和控制按钮
            $statusBar = $wrap.find( '.statusBar' ),

            // 文件总体选择信息。
            $info = $statusBar.find( '.info' ),

            // 上传按钮
            $upload = $wrap.find( '.uploadBtn' ),

            // 没选择文件之前的内容。
            $placeHolder = $wrap.find( '.placeholder' ),

            $progress = $statusBar.find( '.webuploader_progress' ).hide(),

            // 添加的文件数量
            fileCount = 0,

            // 添加的文件总大小
            fileSize = 0,

            // 优化retina, 在retina下这个值是2
            ratio = window.devicePixelRatio || 1,

            // 缩略图大小
            thumbnailWidth = 110 * ratio,
            thumbnailHeight = 110 * ratio,

            // 可能有pedding, ready, uploading, confirm, done.
            state = 'pedding',

            // 所有文件的进度信息，key为file id
            percentages = {},
            // 判断浏览器是否支持图片的base64
            isSupportBase64 = ( function() {
                var data = new Image();
                var support = true;
                data.onload = data.onerror = function() {
                    if( this.width != 1 || this.height != 1 ) {
                        support = false;
                    }
                }
                data.src = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==";
                return support;
            } )(),

            // 检测是否已经安装flash，检测flash的版本
            flashVersion = ( function() {
                var flashVer = NaN;
                var ua = navigator.userAgent;

                if ( window.ActiveXObject ) {
                    var swf = new ActiveXObject( 'ShockwaveFlash.ShockwaveFlash' );

                    if ( swf ) {
                        flashVer = Number( swf.GetVariable( '$version' ).split(' ')[ 1 ].replace(/\,/g, '.').replace( /^(\d+\.\d+).*$/, '$1') );
                    }
                }
                else {
                    if ( navigator.plugins && navigator.plugins.length > 0 ) {
                        var swf = navigator.plugins[ 'Shockwave Flash' ];

                        if ( swf ) {
                            var arr = swf.description.split(' ');
                            for ( var i = 0, len = arr.length; i < len; i++ ) {
                                var ver = Number( arr[ i ] );

                                if ( !isNaN( ver ) ) {
                                    flashVer = ver;
                                    break;
                                }
                            }
                        }
                    }
                }

                return flashVer;
            } )(),
            supportTransition = (function(){
                var s = document.createElement('p').style,
                    r = 'transition' in s ||
                            'WebkitTransition' in s ||
                            'MozTransition' in s ||
                            'msTransition' in s ||
                            'OTransition' in s;
                s = null;
                return r;
            })(),

            // WebUploader实例
            uploader;

        if ( !WebUploader.Uploader.support() ) {
            // if ( isNaN( flashVersion ) || flashVersion < 11 ) {
            //     if ( confirm( '您尚未安装flash播放器或当前flash player 的版本过低于 11，请升级!' ) ) {
            //         // 做自己的处理，比如跳转到http://get.adobe.com/cn/flashplayer
            //     }
            //     return;
            // }
            alert( 'Web Uploader 不支持您的浏览器！');
            throw new Error( 'WebUploader does not support the browser you are using.' );
        }

        // 实例化
        uploader = WebUploader.create({
            pick: {
                id: '#filePicker',
                label: '点击选择图片'
            },
            formData: {
                uid: 123
            },
            dnd: '#dndArea',
            paste: '#uploader',
            swf: '<?php echo ACP_UI;?>/webuploader/Uploader.swf',
            chunked: false,
            chunkSize: 512 * 1024,
            // runtimeOrder: 'flash',
            sendAsBinary: true,
            server: '../../server/fileupload.php',
            // server: 'http://liaoxuezhi.fe.baidu.com/webupload/fileupload.php',
            // server: 'http://www.2betop.net/fileupload.php',
            //

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
        });

        // 拖拽时不接受 js, txt 文件。
        uploader.on( 'dndAccept', function( items ) {
            var denied = false,
                len = items.length,
                i = 0,
                // 修改js类型
                unAllowed = 'text/plain;application/javascript ';

            for ( ; i < len; i++ ) {
                // 如果在列表里面
                if ( ~unAllowed.indexOf( items[ i ].type ) ) {
                    denied = true;
                    break;
                }
            }

            return !denied;
        });

        // uploader.on('filesQueued', function() {
        //     uploader.sort(function( a, b ) {
        //         if ( a.name < b.name )
        //           return -1;
        //         if ( a.name > b.name )
        //           return 1;
        //         return 0;
        //     });
        // });

        // 添加“添加文件”的按钮，
        uploader.addButton({
            id: '#filePicker2',
            label: '继续添加'
        });

        uploader.on('ready', function() {
            window.uploader = uploader;
        });

        // 当有文件添加进来时执行，负责view的创建
        function addFile( file ) {
            var $li = $( '<li id="' + file.id + '">' +
                    '<p class="title">' + file.name + '</p>' +
                    '<p class="imgWrap"></p>'+
                    '<p class="webuploader_progress"><span></span></p>' +
                    '</li>' ),

                $btns = $('<div class="file-panel">' +
                    '<span class="cancel">删除</span>' +
                    '<span class="rotateRight">向右旋转</span>' +
                    '<span class="rotateLeft">向左旋转</span></div>').appendTo( $li ),
                $prgress = $li.find('p.webuploader_progress span'),
                $wrap = $li.find( 'p.imgWrap' ),
                $info = $('<p class="error"></p>'),

                showError = function( code ) {
                    switch( code ) {
                        case 'exceed_size':
                            text = '文件大小超出';
                            break;

                        case 'interrupt':
                            text = '上传暂停';
                            break;

                        default:
                            text = '上传失败，请重试';
                            break;
                    }

                    $info.text( text ).appendTo( $li );
                };

            if ( file.getStatus() === 'invalid' ) {
                showError( file.statusText );
            } else {
                // @todo lazyload
                $wrap.text( '预览中' );
                uploader.makeThumb( file, function( error, src ) {
                    var img;

                    if ( error ) {
                        $wrap.text( '不能预览' );
                        return;
                    }

                    if( isSupportBase64 ) {
                        img = $('<img src="'+src+'">');
                        $wrap.empty().append( img );
                    } else {
                        $.ajax('../../server/preview.php', {
                            method: 'POST',
                            data: src,
                            dataType:'json'
                        }).done(function( response ) {
                            if (response.result) {
                                img = $('<img src="'+response.result+'">');
                                $wrap.empty().append( img );
                            } else {
                                $wrap.text("预览出错");
                            }
                        });
                    }
                }, thumbnailWidth, thumbnailHeight );

                percentages[ file.id ] = [ file.size, 0 ];
                file.rotation = 0;
            }

            file.on('statuschange', function( cur, prev ) {
                if ( prev === 'progress' ) {
                    $prgress.hide().width(0);
                } else if ( prev === 'queued' ) {
                    $li.off( 'mouseenter mouseleave' );
                    $btns.remove();
                }

                // 成功
                if ( cur === 'error' || cur === 'invalid' ) {
                    console.log( file.statusText );
                    showError( file.statusText );
                    percentages[ file.id ][ 1 ] = 1;
                } else if ( cur === 'interrupt' ) {
                    showError( 'interrupt' );
                } else if ( cur === 'queued' ) {
                    percentages[ file.id ][ 1 ] = 0;
                } else if ( cur === 'progress' ) {
                    $info.remove();
                    $prgress.css('display', 'block');
                } else if ( cur === 'complete' ) {
                    $li.append( '<span class="success"></span>' );
                }

                $li.removeClass( 'state-' + prev ).addClass( 'state-' + cur );
            });

            $li.on( 'mouseenter', function() {
                $btns.stop().animate({height: 30});
            });

            $li.on( 'mouseleave', function() {
                $btns.stop().animate({height: 0});
            });

            $btns.on( 'click', 'span', function() {
                var index = $(this).index(),
                    deg;

                switch ( index ) {
                    case 0:
                        uploader.removeFile( file );
                        return;

                    case 1:
                        file.rotation += 90;
                        break;

                    case 2:
                        file.rotation -= 90;
                        break;
                }

                if ( supportTransition ) {
                    deg = 'rotate(' + file.rotation + 'deg)';
                    $wrap.css({
                        '-webkit-transform': deg,
                        '-mos-transform': deg,
                        '-o-transform': deg,
                        'transform': deg
                    });
                } else {
                    $wrap.css( 'filter', 'progid:DXImageTransform.Microsoft.BasicImage(rotation='+ (~~((file.rotation/90)%4 + 4)%4) +')');
                    // use jquery animate to rotation
                    // $({
                    //     rotation: rotation
                    // }).animate({
                    //     rotation: file.rotation
                    // }, {
                    //     easing: 'linear',
                    //     step: function( now ) {
                    //         now = now * Math.PI / 180;

                    //         var cos = Math.cos( now ),
                    //             sin = Math.sin( now );

                    //         $wrap.css( 'filter', "progid:DXImageTransform.Microsoft.Matrix(M11=" + cos + ",M12=" + (-sin) + ",M21=" + sin + ",M22=" + cos + ",SizingMethod='auto expand')");
                    //     }
                    // });
                }


            });

            $li.appendTo( $queue );
        }

        // 负责view的销毁
        function removeFile( file ) {
            var $li = $('#'+file.id);

            delete percentages[ file.id ];
            updateTotalProgress();
            $li.off().find('.file-panel').off().end().remove();
        }

        function updateTotalProgress() {
            var loaded = 0,
                total = 0,
                spans = $progress.children(),
                percent;

            $.each( percentages, function( k, v ) {
                total += v[ 0 ];
                loaded += v[ 0 ] * v[ 1 ];
            } );

            percent = total ? loaded / total : 0;


            spans.eq( 0 ).text( Math.round( percent * 100 ) + '%' );
            spans.eq( 1 ).css( 'width', Math.round( percent * 100 ) + '%' );
            updateStatus();
        }

        function updateStatus() {
            var text = '', stats;

            if ( state === 'ready' ) {
                text = '选中' + fileCount + '张图片，共' +
                        WebUploader.formatSize( fileSize ) + '。';
            } else if ( state === 'confirm' ) {
                stats = uploader.getStats();
                if ( stats.uploadFailNum ) {
                    text = '已成功上传' + stats.successNum+ '张照片至XX相册，'+
                        stats.uploadFailNum + '张照片上传失败，<a class="retry" href="#">重新上传</a>失败图片或<a class="ignore" href="#">忽略</a>'
                }

            } else {
                stats = uploader.getStats();
                text = '共' + fileCount + '张（' +
                        WebUploader.formatSize( fileSize )  +
                        '），已上传' + stats.successNum + '张';

                if ( stats.uploadFailNum ) {
                    text += '，失败' + stats.uploadFailNum + '张';
                }
            }

            $info.html( text );
        }

        function setState( val ) {
            var file, stats;

            if ( val === state ) {
                return;
            }

            $upload.removeClass( 'state-' + state );
            $upload.addClass( 'state-' + val );
            state = val;

            switch ( state ) {
                case 'pedding':
                    $placeHolder.removeClass( 'element-invisible' );
                    $queue.hide();
                    $statusBar.addClass( 'element-invisible' );
                    uploader.refresh();
                    break;

                case 'ready':
                    $placeHolder.addClass( 'element-invisible' );
                    $( '#filePicker2' ).removeClass( 'element-invisible');
                    $queue.show();
                    $statusBar.removeClass('element-invisible');
                    uploader.refresh();
                    break;

                case 'uploading':
                    $( '#filePicker2' ).addClass( 'element-invisible' );
                    $progress.show();
                    $upload.text( '暂停上传' );
                    break;

                case 'paused':
                    $progress.show();
                    $upload.text( '继续上传' );
                    break;

                case 'confirm':
                    $progress.hide();
                    $upload.text( '开始上传' ).addClass( 'disabled' );

                    stats = uploader.getStats();
                    if ( stats.successNum && !stats.uploadFailNum ) {
                        setState( 'finish' );
                        return;
                    }
                    break;
                case 'finish':
                    stats = uploader.getStats();
                    if ( stats.successNum ) {
                        alert( '上传成功' );
                    } else {
                        // 没有成功的图片，重设
                        state = 'done';
                        location.reload();
                    }
                    break;
            }

            updateStatus();
        }

        uploader.onUploadProgress = function( file, percentage ) {
            var $li = $('#'+file.id),
                $percent = $li.find('.progress span');

            $percent.css( 'width', percentage * 100 + '%' );
            percentages[ file.id ][ 1 ] = percentage;
            updateTotalProgress();
        };

        uploader.onFileQueued = function( file ) {
            fileCount++;
            fileSize += file.size;

            if ( fileCount === 1 ) {
                $placeHolder.addClass( 'element-invisible' );
                $statusBar.show();
            }

            addFile( file );
            setState( 'ready' );
            updateTotalProgress();
        };

        uploader.onFileDequeued = function( file ) {
            fileCount--;
            fileSize -= file.size;

            if ( !fileCount ) {
                setState( 'pedding' );
            }

            removeFile( file );
            updateTotalProgress();

        };

        uploader.on( 'all', function( type ) {
            var stats;
            switch( type ) {
                case 'uploadFinished':
                    setState( 'confirm' );
                    break;

                case 'startUpload':
                    setState( 'uploading' );
                    break;

                case 'stopUpload':
                    setState( 'paused' );
                    break;

            }
        });

        uploader.onError = function( code ) {
            alert( 'Eroor: ' + code );
        };

        $upload.on('click', function() {
            if ( $(this).hasClass( 'disabled' ) ) {
                return false;
            }

            if ( state === 'ready' ) {
                uploader.upload();
            } else if ( state === 'paused' ) {
                uploader.upload();
            } else if ( state === 'uploading' ) {
                uploader.stop();
            }
        });

        $info.on( 'click', '.retry', function() {
            uploader.retry();
        } );

        $info.on( 'click', '.ignore', function() {
            alert( 'todo' );
        } );

        $upload.addClass( 'state-' + state );
        updateTotalProgress();
    });

})( jQuery );    


    
</script>

<?php iACP::foot();exit(0);?>
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
			window.parent.modal_<?php echo $_GET['callback'];?>(this)
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
