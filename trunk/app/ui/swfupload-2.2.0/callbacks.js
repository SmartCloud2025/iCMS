var lang={
    'uploadSuccess':'上传成功!',
    'delSuccessFile':'从成功队列中移除',
    'delFailSaveFile':'移除保存失败文件',
    'statusPrompt':' 个文件已上传！ ',
    'flashVersionError':'当前Flash版本过低，请更新FlashPlayer后重试！',
    'flashLoadingError':'Flash加载失败!请检查路径或网络状态',
    'fileUploadReady':'等待上传……',
    'delUploadQueue':'从上传队列中移除',
    'limitPrompt1':'单次不能选择超过',
    'limitPrompt2':'个文件！请重新选择！',
    'delFailFile':'移除失败文件',
    'fileSizeLimit':'文件大小超出限制！',
    'emptyFile':'空文件无法上传！',
    'fileTypeError':'文件类型错误！',
    'unknownError':'未知错误！',
    'fileUploading':'上传中，请等待……',
    'cancelUpload':'取消上传',
    'netError':'网络错误',
    'failUpload':'上传失败!',
    'serverIOError':'服务器IO错误！',
    'noAuthority':'无权限！',
    'fileNumLimit':'上传个数限制',
    'failCheck':'验证失败，本次上传被跳过！',
    'fileCanceling':'取消中，请等待……',
    'stopUploading':'上传已停止……'
};
/* Demo Note:  This demo uses a FileProgress class that handles the UI for displaying the file name and percent complete.
The FileProgress class is not part of SWFUpload.
*/


/* **********************
   Event Handlers
   These are my custom event handlers to make my
   web application behave the way I went when SWFUpload
   completes different tasks.  These aren't part of the SWFUpload
   package.  They are part of my application.  Without these none
   of the actions SWFUpload makes will show up in my application.
   ********************** */
function preLoad() {
	if (!this.support.loading) {
		alert(lang.flashVersionError);
		return false;
	}
    return true;
}
function loadFailed() {
	alert(lang.flashLoadingError);
}

function fileQueued(file) {
	try {
		var hash		= escape(file.name+file.size);
		if($("tr","#"+this.customSettings.progressTarget).hasClass(hash)){
			this.cancelUpload(file.id);
			return false
		}
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus(lang.fileUploadReady);
		progress.toggleCancel(true, this,lang.delUploadQueue);
	} catch (ex) {
		this.debug(ex);
	}
}

function fileQueueError(file, errorCode, message) {
	try {
		if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
			alert(lang.limitPrompt1+ this.settings.file_queue_limit +  lang.limitPrompt2);
			return;
		}

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
        progress.toggleCancel(true, this,lang.delFailFile);

		switch (errorCode) {
		case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
			progress.setStatus(lang.fileSizeLimit);
			this.debug("Error Code: File too big, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
			progress.setStatus(lang.emptyFile);
			this.debug("Error Code: Zero byte file, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
			progress.setStatus(lang.fileTypeError);
			this.debug("Error Code: Invalid File Type, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		default:
			if (file !== null) {
				progress.setStatus(lang.unknownError);
			}
			this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}



function uploadStart(file) {
	try {
		/* I don't want to do any file validation or anything,  I'll just update the UI and
		return true to indicate that the upload should start.
		It's important to update the UI here because in Linux no uploadProgress events are called. The best
		we can do is say we are uploading.
		 */
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus(lang.fileUploading);
		progress.toggleCancel(true, this,lang.cancelUpload);
	}catch (ex) {
		this.debug(ex);
	}
	return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
	try {
		var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setProgress(percent);
		//progress.setStatus(lang.fileUploading);
	} catch (ex) {
		this.debug(ex);
	}
}


function uploadError(file, errorCode, message) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
		//progress.toggleCancel(false);

		switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
			progress.setStatus(lang.netError + message);
			this.debug("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
			progress.setStatus(lang.failUpload);
			this.debug("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.IO_ERROR:
			progress.setStatus(lang.serverIOError);
			this.debug("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
			progress.setStatus(lang.noAuthority);
			this.debug("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			progress.setStatus(lang.fileNumLimit);
			this.debug("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
			progress.setStatus(lang.failCheck);
			this.debug("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			// If there aren't any files left (they were all cancelled) disable the cancel button
//			if (this.getStats().files_queued === 0) {
//				document.getElementById(this.customSettings.cancelButtonId).disabled = true;
//			}
			progress.setStatus(lang.fileCanceling);
			progress.setCancelled();
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			progress.setStatus(lang.stopUploading);
			break;
		default:
			progress.setStatus(lang.unknownError + errorCode);
			this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function uploadComplete(file) {
//	console.log(this.getStats());
//	if (this.getStats().files_queued === 0) {
//		document.getElementById(this.customSettings.cancelButtonId).disabled = true;
//	}
}

// This event comes from the Queue Plugin
function queueComplete(numFilesUploaded) {
	var a=$("#FilesUploaded"),num=parseInt(a.attr("num"));
	a.html(((num?num:0) + numFilesUploaded) +lang.statusPrompt)
}

function uploadResize(file, width, height, encoding, quality){
	console.log("\n\n\n\nuploadResize\n\n\n\n",file, width, height, encoding, quality)
}