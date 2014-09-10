(function($) {
    window.iCMS = {
        comment:{},
        config: function(options) {
            var defaults = {
                API: '/public/api.php',
                PUBLIC: '/',
                COOKIE: 'iCMS_',
                AUTH:'USER_AUTH',
            }
            this.config = $.extend(defaults,options);
        },
        user: {
            uid: function() {
                return iCMS.getcookie('userid');
            },
            nickname: function() {
                var nickname = iCMS.getcookie('nickname');
                return unescape(nickname.replace(/\\u/gi, '%u'));
            },
            auth: function() {
                return iCMS.getcookie(iCMS.config.AUTH);
            },
            data: function(param) {
                $.get(iCMS.api('user', '&do=data'), param, function(c) {
                    //if(!c.code) return false;
                    var user_home = $(".iCMS-user-home")
                    user_home.attr("href", c.url);
                    $(".avatar", user_home).attr("src", c.avatar);
                    $(".name", user_home).text(c.nickname);
                }, 'json');
            },
            logout: function() {
                $.get(iCMS.api('user', "&do=logout"), function(c) {
                    window.location.href = c.forward
                }, 'json');
            },
            status: function() {
                return iCMS.getcookie(iCMS.config.AUTH) ? true : false;
            },
            follow: function(a) {
                var $this = $(a),
                    param = iCMS.param($this);
                param['follow'] = $this.hasClass('follow') ? 1 : 0;
                $.get(iCMS.api('user', "&do=follow"), param, function(c) {
                    if (c.code) {
                        $this.removeClass((param['follow'] ? 'follow' : 'unfollow'));
                        $this.addClass((param['follow'] ? 'unfollow' : 'follow'));
                    } else {
                        iCMS.alert(c.msg);
                        //iCMS.dialog(c.msg);
                        return false;
                    }
                    // window.location.href = c.forward
                }, 'json');
            }
        },
        report:function(a) {
            var $this = $(a),
                report_box = document.getElementById("iCMS-comment-report"),
                report_modal = $this.modal({
                    title: '为什么举报这个评论?',
                    width: "460px",
                    html: report_box,
                    scroll: true
                });
            $("li", report_box).click(function(event) {
                $("li", report_box).removeClass('checked');
                $(this).addClass('checked');
            });
            $('[name="cancel"]', report_box).click(function(event) {
                report_modal.destroy();
            });
            $('[name="ok"]', report_box).click(function(event) {
                event.preventDefault();
                var report_param = iCMS.param($this),
                content = $("[name='content']", report_box);
                report_param['reason'] = $("[name='reason']:checked", report_box).val();
                if (!report_param['reason']) {
                    iCMS.alert("请选择举报的原因");
                    return false;
                }
                if (report_param['reason'] == "0") {
                    report_param['content'] = content.val();
                    if (!report_param['content']) {
                        iCMS.alert("请填写举报的原因");
                        return false;
                    }
                }
                report_param.action = 'report';
                $.post(iCMS.api('user'), report_param, function(c) {
                    content.val('');
                    iCMS.alert(c.msg, c.code);
                    if(c.code){
                        report_modal.destroy();
                    }
                }, 'json');
            });
        },
        article: {
            good: function(a) {
                var $this = $(a),
                    p = $this.parent(),
                    param = iCMS.param(p);
                param['do'] = 'good';
                $.get(iCMS.api('article'), param, function(c) {
                    iCMS.alert(c.msg, c.code);
                    if (c.code) {
                        var count = parseInt($('span', $this).text());
                        $('span', $this).text(count + 1);
                    } else {
                        return false;
                    }
                }, 'json');
            }
        },
        param: function(a) {
            var param = a.attr('data-param') || false;
            if (!param) return {};
            return $.parseJSON(param);
        },
        api: function(app, _do) {
            return this.config.API + '?app=' + app + (_do || '');
        },

        run: function() {
            var doc = $(document);
            this.user_status = this.user.status();
            if (this.user_status) {
                this.user.data();
                $("#iCMS-nav-login").hide();
                $("#iCMS-nav-profile").show();
                this.hover("#iCMS-nav-profile",".iCMS-user-home", "#iCMS-user-menu", 21);
            }
            doc.on("click", '.iCMS-user-follow', function(event) {
                event.preventDefault();
                if (!iCMS.user_status) {
                    iCMS.LoginBox();
                    return false;
                }
                iCMS.user.follow(this);
                return false;
            });
            doc.on("click", '.iCMS-article-do', function(event) {
                event.preventDefault();
                if (!iCMS.user_status) {
                    iCMS.LoginBox();
                    return false;
                }
                var param = iCMS.param($(this));
                if (param.do =='comment') {
                    iCMS.comment.box(this);
                } else if (param.do =='good') {
                    iCMS.article.good(this);
                }
                return false;
            });
            doc.on("click", '.iCMS-user-logout', function(event) {
                event.preventDefault();
                iCMS.user.logout();
                return false;
            });
            doc.on("click", '.iCMS-user-login', function(event) {
                event.preventDefault();
                iCMS.LoginBox();
                return false;
            });
            doc.on('click', 'a[name="iCMS-report"]', function(event) {
                event.preventDefault();
                iCMS.report(this);
            });
            $("#iCMS-seccode-img,#iCMS-seccode-text").click(function() {
                $("#iCMS-seccode-img").attr('src', iCMS.api('public', '&do=seccode&') + Math.random());
            });
            $(".iCMS-API-iframe").load(function() {
                $(this).height(0); //用于每次刷新时控制IFRAME高度初始化
                var height = $(this).contents().height();
                $(this).height(height);
            });
        },
        alert: function(msg, ok) {
            var opts = ok ? {
                label: 'success',
                icon: 'check'
            } : {
                label: 'warning',
                icon: 'warning'
            }
            iCMS.dialog(msg, opts);
        },
        dialog: function(msg, options, _parent) {
            var a = window,
                defaults = {
                    id: 'iPHP-DIALOG',
                    title: 'iCMS - 提示信息',
                    content: msg,
                    width: 360,
                    height: 150,
                    fixed: true,
                    lock: true,
                    time: 300000,
                    label: 'success',
                    icon: 'check'
                },
                opts = $.extend(defaults, options);
            _parent = _parent || false;
            //console.log(opts);
            if (_parent) a = window.parent

            if (msg.jquery) opts.content = msg.html();
            if (typeof msg == "string") {
                opts.content = '<div class=\"iPHP-msg\"><span class=\"label label-' + opts.label + '\"><i class=\"fa fa-' + opts.icon + '\"></i> ' + msg + '</span></div>';
            }
            var dialog = a.$.dialog(opts);
        },
        LoginBox: function() {
            //var loginBox    = $('#iCMS-login-box');
            var loginBox = document.getElementById("iCMS-login-box");
            //console.log(typeof(loginBox));
            window.iCMS_Login_MODAL = $(this).modal({
                width: "560px",
                html: loginBox,
                scroll: true
            });
        },

        hover: function(p,a, b, t, l) {
            var timeOutID = null,pp=$(p),
            t = t || 0, l = l || 0;

            $(a,pp).hover(function() {
                var position = $(this).position();
                $(b,pp).show().css({
                    top: position.top + t,
                    left: position.left + l
                });
            }, function() {
                timeOutID = setTimeout(function() {
                    $(b,pp).hide();
                }, 2500);
            });
            $(b,pp).hover(function() {
                window.clearTimeout(timeOutID);
                $(this).show();
            }, function() {
                $(this).hide();
            });
        },
        modal: function() {
            $('[data-toggle="modal"]').on("click", function(event) {
                event.preventDefault();
                window.iCMS_MODAL = $(this).modal({
                    width: "85%",
                    height: "640px"
                });
                //$(this).parent().parent().parent().removeClass("open");
                return false;
            });
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
                mTitle = m.find(".modal-header h3");
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
                m.hide();
                mOverlay.remove();
                m.find(".modal-header h3").html("iCMS 提示");
                if (opts.overflow) {
                    $("body").css({
                        "overflow-y": "auto"
                    });
                }
            };
            im.size(opts);
            m.show();
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
