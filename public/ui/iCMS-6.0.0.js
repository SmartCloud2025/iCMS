(function($) {
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


        run: function() {
            iCMS.start();
            var doc = $(document);
            this.user.ucard();
            if (this.user_status) {
                this.hover(".iCMS_user_home",20,-10);
            }
            doc.on("click", '.iCMS_user_follow', function(event) {
                event.preventDefault();
                if (!iCMS.user_status) {
                    iCMS.LoginBox();
                    return false;
                }
                iCMS.user.follow(this);
                return false;
            });
            doc.on("click", '.iCMS_article_do', function(event) {
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

            doc.on('click', 'a[name="iCMS-report"]', function(event) {
                event.preventDefault();
                if (!iCMS.user_status) {
                    iCMS.LoginBox();
                    return false;
                }
                window.top.iCMS.report(this);
            });
        },
        LoginBox: function(a) {
            var dialog = window.top.document.getElementById("iCMS-login-dialog");
            iCMS_Login_MODAL = window.top.$(this).modal({
                width: "560px",
                html: dialog,
                scroll: true
            });
            this.user.login("#iCMS-login-dialog");
        },
        hover: function(a, t, l) {
            var timeOutID = null,t = t || 0, l = l || 0,
            b = $(a).parent().find('.popover');
            $(a).hover(function() {
                var position = $(this).position();
                $(b).show().css({
                    top: position.top + t,
                    left: position.left + l
                });
            }, function() {
                timeOutID = setTimeout(function() {
                    $(b).hide();
                }, 2500);
            });
            $(b).hover(function() {
                window.clearTimeout(timeOutID);
                $(this).show();
            }, function() {
                $(this).hide();
            });
        },
    };
    iCMS = $.extend(iCMS,_iCMS);//扩展 or 替换 iCMS方法
})(jQuery);
