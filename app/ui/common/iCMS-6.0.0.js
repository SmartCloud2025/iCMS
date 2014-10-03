(function($) {
    window.iCMS = {
        config:{
            API: '/public/api.php',
            PUBLIC: '/',
            COOKIE: 'iCMS_',
            AUTH:'USER_AUTH',
        },
        init: function(options) {
            this.config = $.extend(this.config,options);
        },
        modal: function() {
            //console.log($(window).width(),$(window).height());
            $('[data-toggle="modal"]').on("click",function() {
                event.preventDefault();
                window.top.iCMS_MODAL = $(this).modal({width: "85%",height: "640px",overflow:true});
                $(this).parent().parent().parent().removeClass("open");
                return false;
            });
        },
        tip: function(el, title,placement) {
            placement = placement||el.attr('data-placement');
            el.tooltip({
              html: true,container:el.attr('data-container')||false,
              placement: placement||'right',
              trigger: 'manual',
              title:title,
            }).tooltip('show');
        },
        alert: function(msg, ok) {
            var opts = ok ? {
                label: 'success',
                icon: 'check'
            } : {
                label: 'warning',
                icon: 'warning'
            }
            window.top.iCMS.dialog(msg, opts);
        },
        dialog: function(msg, options) {
            var defaults = {
                    id: 'iPHP-DIALOG',
                    title: 'iCMS - 提示信息',
                    width: 360,
                    height: 150,
                    fixed: true,
                    lock: true,
                    time: 3000,
                    label: 'success',
                    icon: 'check'
                },
                opts = $.extend(defaults, options);
            //console.log(opts);
            if (msg.jquery) opts.content = msg.html();
            if (typeof msg == "string" && !opts.content) {
                opts.content = '<div class=\"iPHP-msg\"><span class=\"label label-' + opts.label + '\"><i class=\"fa fa-' + opts.icon + '\"></i> ' + msg + '</span></div>';
            }else{
                opts.content = msg;
            }
            return $.dialog(opts);
        },
        setcookie: function(cookieName, cookieValue, seconds, path, domain, secure) {
            var expires = new Date();
            expires.setTime(expires.getTime() + seconds);
            cookieName = this.config.COOKIE + '_' + cookieName;
            document.cookie = escape(cookieName) + '=' + escape(cookieValue) + (expires ? '; expires=' + expires.toGMTString() : '') + (path ? '; path=' + path : '/') + (domain ? '; domain=' + domain : '') + (secure ? '; secure' : '');
        },
        getcookie: function(name) {
            name = this.config.COOKIE + '_' + name;
            var cookie_start = document.cookie.indexOf(name);
            var cookie_end = document.cookie.indexOf(";", cookie_start);
            return cookie_start == -1 ? '' : unescape(document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length)));
        },
        random: function(len) {
            len = len || 16;
            var chars = "abcdefhjmnpqrstuvwxyz23456789ABCDEFGHJKLMNPQRSTUVWYXZ",
                code = '';
            for (i = 0; i < len; i++) {
                code += chars.charAt(Math.floor(Math.random() * chars.length))
            }
            return code;
        },
        imgFix: function(im, x, y) {
            x = x || 99999
            y = y || 99999
            im.removeAttribute("width");
            im.removeAttribute("height");
            if (im.width / im.height > x / y && im.width > x) {
                im.height = im.height * (x / im.width)
                im.width = x
                im.parentNode.style.height = im.height * (x / im.width) + 'px'
            } else if (im.width / im.height <= x / y && im.height > y) {
                im.width = im.width * (y / im.height)
                im.height = y
                im.parentNode.style.height = y + 'px'
            }
        },
        json2str:function(o){
            var arr = [];
            var fmt = function(s) {
                if (typeof s == 'object' && s != null) return iCMS.json2str(s);
                return /^(string|number)$/.test(typeof s) ? '"' + s + '"' : s;
            }
            for (var i in o)
                 arr.push('"' + i + '":'+ fmt(o[i]));
            return '{' + arr.join(',') + '}';
        }
    };
})(jQuery);

(function($) {
    $.fn.modal = function(options) {
        var im = $(this),
            defaults = {
                width: "360px",
                height: "auto",
                title: im.attr('title') || "iCMS 提示",
                href: im.attr('href') || false,
                target: im.attr('data-target') || "#iCMS-MODAL",
                zIndex: im.attr('data-zIndex') || false,
                overflow: im.attr('data-overflow') || false,
            };

        var meta = im.attr('data-meta') ? $.parseJSON(im.attr('data-meta')) : {};
        var opts = $.extend(defaults, options, meta);
        var mOverlay = $('<div id="modal-overlay" class="modal-overlayBG"></div>');

        return im.each(function() {

            var m = $(opts.target),
                mBody = m.find(".modal-body"),
                mTitle = m.find(".modal-title");
            opts.title && mTitle.html(opts.title);
            mBody.empty();

            if (opts.overflow) $("body").css({
                "overflow-y": "hidden"
            });

            if (opts.html) {
                var html = opts.html;
                if (typeof opts.html == "object") {
                    if (opts.html.jquery) {
                        opts.html.show();
                        html = opts.html.html();
                    } else {
                        opts.html.style.display = 'block';
                    }
                }
                mBody.html(html).css({
                    "overflow-y": "auto"
                });
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
                opts.zIndex && m.css({
                    "cssText": 'z-index:' + opts.zIndex + '!important'
                });
                m.css({
                    width: opts.width
                });
                mBody.height(opts.height);
                var left = ($(window).width() - m.width()) / 2,
                    top = ($(window).height() - m.height()) / 2;
                m.css({
                    "position": "fixed",
                    left: left + "px",
                    top: top + "px"
                });

                //console.log({left:left+"px",top:top+"px"});

            };
            im.destroy = function() {
                window.stop ? window.stop() : document.execCommand("Stop");
                m.hide().removeClass('in');
                mOverlay.remove();
                m.find(".modal-title").html("iCMS 提示");
                if (opts.overflow) {
                    $("body").css({
                        "overflow-y": "auto"
                    });
                }
            };
            im.size(opts);
            m.show().addClass('in');
            return im;
        });
    }
})(jQuery);

function pad(num, n) {
    num = num.toString();
    return Array(n > num.length ? (n - ('' + num).length + 1) : 0).join(0) + num;
}

$(function(){
    if(!placeholderSupport()){   // 判断浏览器是否支持 placeholder
        $('[placeholder]').focus(function() {
            var input = $(this);
            if (input.val() == input.attr('placeholder')) {
                input.val('');
                input.removeClass('placeholder');
            }
        }).blur(function() {
            var input = $(this);
            if (input.val() == '' || input.val() == input.attr('placeholder')) {
                input.addClass('placeholder');
                input.val(input.attr('placeholder'));
            }
        }).blur();
    };
})

function placeholderSupport() {
    return 'placeholder' in document.createElement('input');
}
