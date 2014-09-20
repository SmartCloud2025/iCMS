(function($) {
    iCMS.user = {
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
                    var user_home = $(".iCMS_user_home")
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
                $.post(iCMS.api('user', "&do=follow"), param, function(c) {
                    if (c.code) {
                        $this.removeClass((param['follow'] ? 'follow' : 'unfollow'));
                        $this.addClass((param['follow'] ? 'unfollow' : 'follow'));
                    } else {
                        iCMS.alert(c.msg);
                        return false;
                    }
                    // window.location.href = c.forward
                }, 'json');
            }
    };
    iCMS.article = {
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
    };
    var _iCMS = {
        report:function(a) {
            var $this = $(a),
                report_box = document.getElementById("iCMS-report-box"),
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
                    iCMS.alert(c.msg,c.code);
                    $("li", report_box).removeClass('checked');
                    $("[name='reason']", report_box).removeAttr('checked');
                    if(c.code){
                        report_modal.destroy();
                    }
                }, 'json');
            });
        },

        param: function(a) {
            var param = a.attr('data-param') || false;
            if (!param) return {};
            return $.parseJSON(param);
        },
        api: function(app, _do) {
            return iCMS.config.API + '?app=' + app + (_do || '');
        },

        run: function() {
            var doc = $(document);
            this.user_status = this.user.status();
            if (this.user_status) {
                this.user.data();
                $("#iCMS-nav-login").hide();
                $("#iCMS-nav-profile").show();
                this.hover("#iCMS-nav-profile",".iCMS_user_home", "#iCMS-user-menu", 21);
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
                if (!iCMS.user_status) {
                    iCMS.LoginBox();
                    return false;
                }
                window.top.iCMS.report(this);
            });
            $("#iCMS-seccode-img,#iCMS-seccode-text").click(function() {
                $("#iCMS-seccode-img").attr('src', iCMS.api('public', '&do=seccode&') + Math.random());
            });
            $(".iCMS_API_iframe").load(function() {
                iCMS.api_iframe_height($(this));
            });
        },
        api_iframe_height:function(a,b){
            var a = a||window.top.$(b);
            a.height(0); //用于每次刷新时控制IFRAME高度初始化
            var height = a.contents().height();
            a.height(height);
            //window.top.$('.iCMS_API_iframe-loading').hide();
        },
        LoginBox: function() {
            var loginBox = window.top.document.getElementById("iCMS-login-box");
            //console.log(typeof(loginBox));
            iCMS_Login_MODAL = window.top.$(this).modal({
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
                window.top.iCMS_MODAL = $(this).modal({
                    width: "85%",
                    height: "640px"
                });
                //$(this).parent().parent().parent().removeClass("open");
                return false;
            });
        },
    };
    iCMS = $.extend(iCMS,_iCMS);//扩展 or 替换 iCMS属性
})(jQuery);
