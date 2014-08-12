(function($) {
    window.iCMS = {
        config: {
            'COOKIE_PRE': 'iCMS_'
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
                return iCMS.getcookie('AUTH_INFO');
            },
            data: function(param) {
                $.get(iCMS.api('user', '&do=data'), param, function(c) {
                    //if(!c.code) return false;
                    var userhome = $(".iCMS-user-home")
                    userhome.attr("href", c.url);
                    $(".avatar", userhome).attr("src", c.avatar);
                    $(".name", userhome).text(c.nickname);
                }, 'json');
            },
            logout: function() {
                $.get(iCMS.api('user', "&do=logout"), function(c) {
                    window.location.href = c.forward
                }, 'json');
            },
            status: function() {
                return iCMS.getcookie('AUTH_INFO') ? true : false;
            },
            follow: function(a) {
                var $this = $(a),
                    param = iCMS.param($this);
                param['follow'] = $this.hasClass('follow') ? 1 : 0;
                $.get(iCMS.api('user', "&do=follow"), param, function(c) {
                    if (c.code) {
                        $this.removeClass((param['follow'] ? 'follow' : 'unfollow'));
                        $this.addClass((!param['follow'] ? 'follow' : 'unfollow'));
                    } else {
                        iCMS.alert(c.msg);
                        //iCMS.dialog(c.msg);
                        return false;
                    }
                    // window.location.href = c.forward
                }, 'json');
            },
            avatar: function(size, uid) {
                size = size || 24;
                uid = uid || iCMS.user.uid;
                var nuid = pad(uid, 7),
                    dir1 = nuid.substr(0, 3),
                    dir2 = nuid.substr(3, 2);
                avatar = this.config.avatar + dir1 + '/' + dir2 + '/' + uid + '.jpg_' + size + 'x' + size + '.jpg';
                return avatar;
            },
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
                        var count = parseInt($('span', b).text());
                        $('span', b).text(count + 1);
                    } else {
                        return false;
                    }
                }, 'json');
            }
        },
        comment:{
            page:function (pn,a) {
                var $this = $(a),
                    p = $this.parent(),
                    pp = p.parent(),
                    query = p.attr('data-query');

                //url
                console.log(a,p,query);
            },
            box: function(a) {
                var $this = $(a),
                    p = $this.parent(),
                    pp = p.parent(),
                    param = iCMS.param(p),
                    page = 1,
                    def = '写下你的评论…',
                    box = $('.commentApp-box', pp);
                if (box.length > 0) {
                    box.remove();
                    return false;
                }
                // console.log(param);

                var spike = '<i class="iCMS-icon iCMS-icon-spike commentApp-bubble" style="display: inline; left: 481px;"></i>',
                    box = $('<div class="commentApp-box">'),
                    list = $('<div class="commentApp-list">'),
                    form = $('<div class="commentApp-form">');
                form.html('<div class="commentApp-ipt">' +
                    '<input class="commentApp-textarea" type="text" value="' + def + '">' +
                    '</div>' +
                    '<div class="app-command clearfix">' +
                    '<a href="#" name="addnew" class="btn btn-primary">评论</a>' +
                    '<a href="###" name="closeform" class="app-command-cancel">取消</a>' +
                    '</div>'
                );
                var loging = $('<div class="commentApp-spinner">正在加载，请稍等 <i class="spinner-lightgray"></i></div>');
                box.html(loging);
                box.append(spike, list, form);
                p.after(box);
                //加载评论
                comment_list();

                //----------绑定事件----------------
                //box.on('focus', 'commentApp-textarea', function(event) {
                $('.commentApp-textarea', box).focus(function() {
                    form.addClass('expanded');
                    if (this.value == def) this.value = '';

                    $(this).css({
                        color: '#222'
                    });
                    //}).on('blur', 'commentApp-textarea', function(event) {
                }).blur(function() {
                    close_form();
                });

                //关闭评论
                $('a[name="closeform"]', box).click(function(event) {
                    event.preventDefault();
                    form.removeClass('expanded');
                    close_form(true);
                });
                //提交评论
                box.on('click', 'a[name="addnew"]', function(event) {
                    //$('a[name="addnew"]',box).click(function() {
                    event.preventDefault();
                    var cform = $(this).parent().parent(),
                        textarea = $('.commentApp-textarea', cform),
                        cparam = comment_param(textarea);

                    if (!cparam.content) {
                        iCMS.alert("请填写内容");
                        textarea.focus();
                        return false;
                    }
                    $.post(iCMS.api('comment'), cparam, function(c) {
                        if (c.code) {
                            var count = parseInt($('span', $this).text());
                            $('span', $this).text(count + 1);
                            textarea.val(def).css({
                                color: '#999'
                            });
                            comment_list(0, c.forward);
                        } else {
                            iCMS.alert(c.msg);
                        }
                    }, 'json');
                });
                list.on('click', 'a[name="load-more"]', function(event) {
                    event.preventDefault();
                    $(".load-more", list).remove();
                    comment_list(page);
                });

                //回复评论
                list.on('click', 'a[name="reply_comment"]', function(event) {
                    event.preventDefault();
                    var item = $(this).parent().parent(),
                        reply_param = iCMS.param($(this)),
                        item_form = $('.commentApp-form', item);

                    if (item_form.length > 0) {
                        item_form.remove();
                        return false;
                    }
                    item_form = form.clone();
                    item_form.addClass('expanded').removeClass('commentApp-box-ft');
                    $(this).parent().after(item_form);


                    $('.commentApp-textarea', item_form).data('reply_param', reply_param)
                        .val("").focus().css({
                            color: '#222'
                        });
                    $('a[name="closeform"]', item_form).click(function(event) {
                        event.preventDefault();
                        item_form.remove();
                    });
                });
                //赞评论
                list.on('click', 'a[name="like_comment"]', function(event) {
                    event.preventDefault();
                    var $this = $(this),
                        like_param = iCMS.param($this);
                    like_param.do = 'like';
                    $.get(iCMS.api('comment'), like_param, function(c) {
                        //                        console.log(c);
                        if (c.code) {
                            var p = $this.parent(),
                                like_num = $('.like-num em', p).text();
                            if (like_num == "") {
                                $this.parent().append('<span class="like-num" data-tip="iCMS:s:1 人觉得这个很赞"><em>1</em> <span>赞</span></span>')
                            } else {
                                like_num = parseInt(like_num) + 1;
                                $('.like-num em', p).text(like_num);
                            }
                        } else {
                            iCMS.alert(c.msg);
                        }
                    }, 'json');
                });
                //举报评论
                list.on('click', 'a[name="report_comment"]', function(event) {
                    event.preventDefault();
                    var $this = $(this),
                        report_box = document.getElementById("iCMS-comment-report"),
                        _REPORT_MODAL = $(this).modal({
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
                        _REPORT_MODAL.destroy();
                    });
                    $('[name="ok"]', report_box).click(function(event) {
                        var report_param = iCMS.param($this);
                        report_param['reason'] = $("[name='reason']:checked", report_box).val();
                        if (report_param['reason'] == undefined) {
                            iCMS.alert("请选择举报的原因");
                            return false;
                        }
                        if (report_param['reason'] == "0") {
                            report_param['content'] = $("[name='content']", report_box).val();
                            if (report_param['content'] == "") {
                                iCMS.alert("请填写举报的原因");
                                return false;
                            }
                        }
                        report_param.action = 'report';

                        $.post(iCMS.api('comment'), report_param, function(c) {
                            $("[name='content']", report_box).val('');
                            iCMS.alert(c.msg, c.code);
                        }, 'json');
                        return false;
                    });

                    //iCMS.dialog(report_box,opts); 
                });

                function comment_param(textarea) {
                    var reply_param = textarea.data('reply_param'),
                        content = textarea.val(),
                        vars = {
                            'action': 'add',
                            'content': (content == def ? '' : content)
                        };
                    return $.extend(vars, param, reply_param);
                }

                function close_form(d, p) {
                    var textarea = $('.commentApp-textarea', (p || box));
                    if (textarea.val() == "" || d) {
                        textarea.val(def).css({
                            color: '#999'
                        });
                    }
                }

                function comment_list(pageNum, id) {
                    pageNum = pageNum || 1;
                    $.get(iCMS.api('comment'), {
                            'do': 'json',
                            'iid': param['iid'],
                            'id': id,
                            'by': 'ASC',
                            page: pageNum
                        },
                        function(json) {
                            loging.remove();
                            if (!json)
                                return false;

                            var totalpage = 0;
                            form.addClass('commentApp-box-ft');
                            $.each(json, function(i, c) {
                                //console.log(c.reply);
                                var item = '<div class="commentApp-item" data-id="' + c.id + '">' +
                                    '<a title="' + c.user.name + '" data-tip="iCMS:ucard:' + c.user.uid + '" class="app-item-link-avatar" href="' + c.user.url + '">' +
                                    '<img src="' + c.user.avatar + '" class="app-item-img-avatar">' +
                                    '</a>' +
                                    '<div class="commentApp-content-wrap">' +
                                    '<div class="commentApp-hd">' +
                                    '<a data-tip="iCMS:ucard:' + c.user.uid + '" href="' + c.user.url + '" class="zg-link">' + c.user.name + '</a>';
                                if (c.suid == c.uid) {
                                    item += '<span class="desc">（作者）</span>';
                                }
                                if (c.reply) {
                                    item += '<span class="desc">回复 </span>' +
                                        '<a data-tip="iCMS:ucard:' + c.reply.uid + '" href="' + c.reply.url + '" class="zg-link">' + c.reply.name + '</a>';
                                }
                                item += '</div>' +
                                    '<div class="commentApp-content">' + c.content + '</div>' +
                                    '<div class="commentApp-ft">' +
                                    '<span class="date">' + c.addtime + '</span>' +
                                    '<a href="javascript:;" class="reply commentApp-op-link" name="reply_comment" data-param=\'{"uid":"' + c.user.uid + '","name":"' + c.user.name + '"}\'>' +
                                    '<i class="iCMS-icon iCMS-icon-comment-reply"></i>回复</a>' +
                                    '<a href="javascript:;" class="like commentApp-op-link" name="like_comment" data-param=\'{"id":"' + c.id + '"}\'>' +
                                    '<i class="iCMS-icon iCMS-icon-comment-like"></i>赞</a>';
                                if (c.up > 1) {
                                    item += '<span class="like-num" data-tip="iCMS:s:' + c.up + ' 人觉得这个很赞">' +
                                        '<em>' + c.up + '</em> <span>赞</span></span>';
                                }
                                item += '<a href="javascript:;" name="report_comment" data-param=\'{"id":"' + c.id + '","uid":"' + c.user.uid + '"}\' class="report commentApp-op-link needsfocus">' +
                                    '<i class="iCMS-icon iCMS-icon-no-help"></i>举报</a>' +
                                    '</div>' +
                                    '</div>' +
                                    '</div>';
                                list.append(item);
                                //console.log(c.page.perpage,i,i+1);
                                if (json.length == (i + 1)) {
                                    totalpage = c.page.total;
                                    console.log(totalpage, page);
                                    if (totalpage > 1) {
                                        page = pageNum + 1;
                                        if (page > totalpage) {
                                            page = totalpage;
                                        } else {
                                            list.append('<a href="javascript:;" class="load-more" name="load-more"><span class="text">显示全部评论</a>');
                                        }
                                    }
                                }
                            });

                        }, 'json');
                }
                //------------
            }
        },
        param: function(a) {
            var param = a.attr('data-param') || false;
            if (!param) return {};
            return $.parseJSON(param);
        },
        api: function(app, ido) {
            return iCMS.config.API + '?app=' + app + (ido || '');
        },
        Init: function() {
            this.user_status = this.user.status();
            // console.log(this.user_status);
            if (this.user_status) {
                this.user.data();
                //this.userinfo();
                $("#iCMS-nav-login").hide();
                $("#iCMS-nav-profile").show();
                this.hover(".iCMS-user-home", "#iCMS-user-menu", 21);
            }
            $(document).on("click", '.iCMS-user-follow', function(event) {
                event.preventDefault();
                if (!iCMS.user_status) {
                    iCMS.LoginBox();
                    return false;
                }
                iCMS.user.follow(this);
                return false;
            });
            $(document).on("click", '.iCMS-article-do', function(event) {
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
            $(document).on("click", '.iCMS-user-logout', function(event) {
                event.preventDefault();
                iCMS.user.logout();
                return false;
            });
            $(document).on("click", '.iCMS-user-login', function(event) {
                event.preventDefault();
                iCMS.LoginBox();
                return false;
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

        hover: function(a, b, t, l) {
            var timeOutID = null;
            t = t || 0, l = l || 0;
            $(a).hover(function() {
                var position = $(this).position();
                $(b).show().css({
                    top: position.top + t,
                    left: position.left + l
                });
            }, function() {
                timeOutID = setTimeout(function() {
                    $(b).hide();
                }, 1000);
            });
            $(b).hover(function() {
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
            cookieName = this.config.COOKIE_PRE + '_' + cookieName;
            document.cookie = escape(cookieName) + '=' + escape(cookieValue) + (expires ? '; expires=' + expires.toGMTString() : '') + (path ? '; path=' + path : '/') + (domain ? '; domain=' + domain : '') + (secure ? '; secure' : '');
        },
        getcookie: function(name) {
            name = this.config.COOKIE_PRE + '_' + name;
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
                    left: left + "px",
                    top: top + "px"
                })
                    .css({
                        "position": "fixed"
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