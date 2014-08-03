if ($.browser.msie && ($.browser.version == "6.0" || $.browser.version == "7.0") && !$.support.style) {
    alert("系统检测到您使用的是IE内核的浏览器!!\n\nIE内核的浏览器访问本页面可能会出现各种不可预料的错误!!\n\n为了您更好的使用本页面\n\n推荐使用 Chrome,FireFox 等浏览器\n\n如使用 搜狗 或者 360 浏览器的请切换成 极速模式!");
}
(function($) {
    $.fn.extend({
        batch: function(opt) {
            var im   = $(this),_this = this,
                action   = $('<input type="hidden" name="batch">'),
                content  = $('<div class="hide"></div>').appendTo(im),
                defaults = {
					move: function(){
						var select	= $("#cid").clone().show()
							//.removeClass("chosen-select")
							.attr("id",iCMS.random(3));
						$("option:first",select).remove();
						return select;
					},
					prop: function(){
						var select	= $("#pid").clone().show()
							//.removeClass("chosen-select")
                            .attr("name",'pid[]')
                            .attr("multiple",'multiple')
							.attr("id",iCMS.random(3));
						$("option:first",select).remove();
						return select;
					},
				},
				options	= $.extend(defaults, opt);
					
			im.dialog = function(title,obj,h){
				window.batch_dialog	= $.dialog({id: 'batch',width:"320px",lock: true,
					title:title,content:obj,
				    okValue: '确定',ok: function () {
				    	content.html($(obj).clone(true));
					    im.submit();
				    	return true;
				    },
				    cancelValue: "取消",cancel: function(){
				    	action.val(0);
				    	content.empty();
					    return true;
					}
				});
			}
			$('[data-toggle="batch"]').click(function(){
				if($("tbody input:checkbox:checked",im).length==0){
					alert("请选择要操作项目!");
					return true;
				}
				var a = $(this),b = this,
                    act   = a.attr('data-action'),
                    ab    = $('#'+act+'Batch'),
                    ret   = document.getElementById(act+'Batch'),
                    title = a.text();
					action.val(act).appendTo(im);
                    if(ret==null){
    					if(typeof options[act]=="undefined"){
    						ret	= '<div class="iPHP-msg"><span class="label label-warning"><i class="icon-warning-sign icon-white"></i> 确定要'+$.trim(title)+"?</span></div>";
    					}else{
                            var ret = document.createElement("div");
                            $(ret).html(options[act]());
    					}
                    }
					im.dialog(title,ret);
			});
            return im;
        }
    })
})(jQuery);

(function($) {
    $.fn.modal = function(options) {
        var im = $(this), 
        defaults = {
        	width: "360px",height: "300px",
            title: im.attr('title') || "iCMS 提示",
            href: im.attr('href')||false,
            target: im.attr('data-target') || "#iCMS_MODAL",
            zIndex: im.attr('data-zIndex')||false,
        };
      
        $("body").css({"overflow-y": "hidden"});
        var meta = im.attr('data-meta')?$.parseJSON(im.attr('data-meta')):{};
        var opts = $.extend(defaults,options,meta);
        var mOverlay = $('<div id="modal-overlay" class="modal-overlayBG"></div>');
        return im.each(function() {
            var m = $(opts.target), 
            mBody = m.find(".modal-body"), 
            mTitle = m.find(".modal-header h3");
            opts.title && mTitle.html(opts.title);
            mBody.empty();
            
            if (opts.html) {
            	var html = opts.html;
            	if(typeof(opts.html)=="object"){
            		html = opts.html.clone(true).show();
            	}
                mBody.html(html).css({"overflow-y": "auto"});
            } else if (opts.href) {
                var mFrame = $('<iframe class="modal-iframe" frameborder="no" allowtransparency="true" scrolling="auto" hidefocus="" src="' + opts.href + '"></iframe>');
                mFrameFix = $('<div id="modal-iframeFix" class="modal-iframeFix"></div>');
                mFrame.appendTo(mBody);
                mFrameFix.appendTo(mBody);
            }
            mOverlay.insertBefore(m).click(function() {
                im.destroy();
            });
            $('[data-dismiss="modal"][aria-hidden="true"]').on('click', function() {
                im.destroy();
            });
            im.size = function(o) {
                var opts = $.extend(opts, o);
                opts.zIndex && m.css({"cssText":'z-index:'+opts.zIndex + '!important'});
                m.css({width: opts.width});
                mBody.height(opts.height);
                var left = ($(window).width() - m.width()) / 2, 
                top = ($(window).height() - m.height()) / 2;
                m.css({left: left + "px",top: top + "px"})
                .css({"position": "fixed"});
                
            //console.log({left:left+"px",top:top+"px"});

            };
            im.destroy = function() {
                window.stop ? window.stop() : document.execCommand("Stop");
                m.hide();
                mOverlay.remove();
                m.find(".modal-header h3").html("iCMS 提示");
                $("body").css({"overflow-y": "auto"});
            };
            im.size(opts);
            m.show();
            return im;
        });
    }
})(jQuery);

(function($) {
    $.fn.extend({
        insertContent: function(myValue, t) {
            event.preventDefault();
            var $t = $(this)[0];
            
            if (document.selection) { //ie
                this.focus();
                var sel = document.selection.createRange();
                sel.text = myValue;
                this.focus();
                sel.moveStart('character', -l);
                var wee = sel.text.length;
                if (arguments.length == 2) {
                    var l = $t.value.length;
                    sel.moveEnd("character", wee + t);
                    t <= 0 ? sel.moveStart("character", wee - 2 * t - myValue.length) : sel.moveStart("character", wee - t - myValue.length);
                    sel.select();
                }
            } else if ($t.selectionStart || $t.selectionStart == '0') {
                var startPos = $t.selectionStart;
                var endPos = $t.selectionEnd;
                var scrollTop = $t.scrollTop;
                $t.value = $t.value.substring(0, startPos) + myValue + $t.value.substring(endPos, $t.value.length);
                this.focus();
                $t.selectionStart = startPos + myValue.length;
                $t.selectionEnd = startPos + myValue.length;
                $t.scrollTop = scrollTop;
                if (arguments.length == 2) {
                    $t.setSelectionRange(startPos - t, $t.selectionEnd + t);
                    this.focus();
                }
            } 
            else {
                this.value += myValue;
                this.focus();
            }
        }
    })
})(jQuery);

//https://github.com/spencertipping/jquery.fix.clone/blob/master/jquery.fix.clone.js
(function (original) {
  jQuery.fn.clone = function () {
    var result           = original.apply(this, arguments),
        my_textareas     = this.find('textarea').add(this.filter('textarea')),
        result_textareas = result.find('textarea').add(result.filter('textarea')),
        my_selects       = this.find('select').add(this.filter('select')),
        result_selects   = result.find('select').add(result.filter('select'));

    for (var i = 0, l = my_textareas.length; i < l; ++i) $(result_textareas[i]).val($(my_textareas[i]).val());
    for (var i = 0, l = my_selects.length;   i < l; ++i) {
      for (var j = 0, m = my_selects[i].options.length; j < m; ++j) {
        if (my_selects[i].options[j].selected === true) {
          result_selects[i].options[j].selected = true;
        }
      }
    }
    return result;
  };
}) (jQuery.fn.clone);

window.iCMS = {
    select: function(a, v) {
        var va = v.split(',');
        $.each(va, function(i,val){      
          $("#" + a+" option[value='"+val+"']").attr("selected", true);
        });      
        $("#"+a).trigger("chosen:updated");
    },
    modal: function() {
        $('[data-toggle="modal"]').on("click",function() {
            window.iCMS_MODAL = $(this).modal({width: "85%",height: "640px"});
            $(this).parent().parent().parent().removeClass("open");
            return false;
        });
    },
    setcookie: function(cookieName, cookieValue, seconds, path, domain, secure) {
        var expires = new Date();
        expires.setTime(expires.getTime() + seconds);
        document.cookie = escape(cookieName) + '=' + escape(cookieValue) 
        + (expires ? '; expires=' + expires.toGMTString() : '') 
        + (path ? '; path=' + path : '/') 
        + (domain ? '; domain=' + domain : '') 
        + (secure ? '; secure' : '');
    },
    getcookie: function(name) {
        var cookie_start = document.cookie.indexOf(name);
        var cookie_end = document.cookie.indexOf(";", cookie_start);
        return cookie_start == -1 ? '' : unescape(document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length)));
    },
    random: function(len) {
	    len = len||16;
	    var chars 	= "abcdefhjmnpqrstuvwxyz23456789ABCDEFGHJKLMNPQRSTUVWYXZ",code	= '';
	    for ( i = 0; i < len; i++ ) {
	        code += chars.charAt( Math.floor( Math.random() * chars.length ) )
	    }
	    return code;
	} 
}
