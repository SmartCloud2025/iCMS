/*
	A simple class for displaying file information and progress
	Note: This is a demonstration only and not part of SWFUpload.
	Note: Some have had problems adapting this class in IE7. It may not be suitable for your application.
*/

// Constructor
// file is a SWFUpload file object
// targetID is the HTML element id attribute that the FileProgress HTML structure will be added to.
// Instantiating a new FileProgress object with an existing file will reuse/update the existing DOM elements
function FileProgress(file, targetID) {
	this.PID		= file.id;
	this.wrapper	= $("#"+this.PID);
	if (this.wrapper.length==0) {
		var fileType	= file.type.toUpperCase().replace('.','');
		var tds		= 
			'<td class="url"><input type="checkbox" class="url" value=""/></td>'+
			'<td class="index">'+(file.index+1)+'</td>'+
			'<td class="name">'+file.name+'</td>'+
			'<td class="type">'+fileType+'</td>'+
			'<td class="size">'+sizeUnit(file.size)+'</td>'+
			'<td><span class="status label label-info"></span><div class="progress progress-info progress-striped"><div class="bar"></div></div></td>'+
			'<td><a class="btn btn-small cancel">移除</a></td>';
		this.wrapper = $("<tr>")
			.addClass("wrapper")
			.attr("id",file.id).addClass(escape(file.name+file.size))
			.append(tds);
		
		$('input[type=checkbox]',this.wrapper).uniform();
		
		this.wrapper.appendTo("#"+targetID);
	} else {
		this.reset();
	}
	this.setTimer(null);
}
function sizeUnit(size) {
    var SU = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'],n = 0;
    while (size >= 1024) {
        size /= 1024;
        n++;
    }
    return size.toFixed(2)+' '+SU[n];
}
FileProgress.prototype.setTimer = function (timer) {
	this.wrapper["FP_TIMER"] = timer;
};
FileProgress.prototype.getTimer = function (timer) {
	return this.wrapper["FP_TIMER"] || null;
};

FileProgress.prototype.reset = function () {
	this.wrapper.show();
	this.appear();	
};

FileProgress.prototype.setProgress = function (percentage) {
	var progress=$('.progress',this.wrapper)
	$('.status',this.wrapper).hide();
	progress.show().addClass("active");
	$('.bar',this.wrapper).width(percentage + "%").text(percentage + "%");
	if(percentage==100){
		progress.removeClass("active");
	}
	this.appear();	
};
FileProgress.prototype.setComplete = function (info) {
	this.wrapper.data("info",info);
	$('.url',this.wrapper).val(info.value)
	.attr('_original',info.original)
	.attr('_fileType',info.fileType)
	.attr('_image',info.image)
	.attr('url',info.url);
	var clipboard	= $('<a class="btn btn-small clipboard" data-clipboard-text="'+info.url+'">复制</a>');
	var clip 		= new ZeroClipboard(clipboard);
	clip.on('complete', function(client, args) {
        alert("复制成功!\n\n" + args.text );
	});
	$('.cancel',this.wrapper).after(clipboard);
	$('.progress',this.wrapper).hide();
	$('.status',this.wrapper).removeClass("label-info").addClass("label-success");
	var oSelf = this;
	this.setTimer(setTimeout(function () {
		//oSelf.disappear();  //隐藏已完成列表
	}, 10000));
};
FileProgress.prototype.setError = function () {
	var oSelf = this;
	this.setTimer(setTimeout(function () {
		//oSelf.disappear();
	}, 3000));
};
FileProgress.prototype.setCancelled = function () {
	$('.bar',this.wrapper).hide();
	var oSelf = this;
	this.setTimer(setTimeout(function () {
		oSelf.disappear();
	}, 1000));
};
FileProgress.prototype.setStatus = function (status) {
	$('.status',this.wrapper).text(status).show();
};

// Show/Hide the cancel button
FileProgress.prototype.toggleCancel = function (show, swfUploadInstance,message) {
    var cancelBtn = $('.cancel',this.wrapper);
	if(show){
		cancelBtn.show();
	}else{
		cancelBtn.hide();
	}
	cancelBtn.text(message?message:"取消上传");

	if (swfUploadInstance){
        var me = this;
		cancelBtn.on("click", function() {
			var info	= me.wrapper.data("info");
			if(info){
				$.getJSON(APP_URI+'&do=del&ajax=1',{'id':info.fid},function(ret){
					me.setStatus(ret.msg);
					if(ret.code){
						swfUploadInstance.cancelUpload(me.PID);
						me.disappear();
					}
				});
			}else{
				swfUploadInstance.cancelUpload(me.PID);
				me.disappear();
			}
			return false;
		});
	}
};

FileProgress.prototype.appear = function () {
	if (this.getTimer() !== null) {
		clearTimeout(this.getTimer());
		this.setTimer(null);
	}
};

// Fades out and clips away the FileProgress box.
FileProgress.prototype.disappear = function () {
	//this.wrapper.fadeOut("slow");
	this.wrapper.remove();
	this.setTimer(null);
};