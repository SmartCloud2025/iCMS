(function($) {
    var _iCMS = {
        pm:function(a){
            var $this  = $(a),$parent = $this.parent(),
                box    = document.getElementById("iCMS-pm-box"),
                dialog = $.dialog({content: box,id:'iPHP-DIALOG',title: '发送私信',lock:true}),
                inbox  = $this.attr('href'),
                param  = iCMS.param($this),
                _param = iCMS.param($parent),
                data   = $.extend(param,_param),
                content = $("[name='content']", box);
            $(".pm_warnmsg", box).hide();
            content.val('');
            $('.pm_uname', box).text(data.name);
            $('.pm_inbox', box).attr("href",inbox);
            $('.cancel', box).click(function(event) {
                event.preventDefault();
                dialog.close();
            });
            $('[name="send"]', box).click(function(event) {
                event.preventDefault();
                data.content = content.val();
                if (!data.content) {
                    content.focus();
                    $(".pm_warnmsg", box).show();
                    return false;
                }
                data.action = 'pm';
                $.post(iCMS.api('user'), data, function(c) {
                    dialog.close();
                    iCMS.alert(c.msg,c.code);
                }, 'json');
            });
        },
        report:function(a) {
            var $this = $(a),
                box   = document.getElementById("iCMS-report-box"),
                modal = $this.modal({
                    title: '为什么举报这个评论?',
                    width: "460px",
                    html: box,
                    scroll: true
                });
            $("li", box).click(function(event) {
                event.preventDefault();
                $("li", box).removeClass('checked');
                $(this).addClass('checked');
                //$("[name='reason']",box).prop("checked",false);
                $("[name='reason']",this).prop("checked",true);
            });
            $('.cancel', box).click(function(event) {
                event.preventDefault();
                modal.destroy();
            });
            $('[name="ok"]', box).click(function(event) {
                event.preventDefault();
                var data = iCMS.param($this),
                content = $("[name='content']", box);
                data.reason = $("[name='reason']:checked", box).val();
                if (!data.reason) {
                    iCMS.alert("请选择举报的原因");
                    return false;
                }
                if (data.reason == "0") {
                    data.content = content.val();
                    if (!data.content) {
                        iCMS.alert("请填写举报的原因");
                        return false;
                    }
                }
                data.action = 'report';
                $.post(iCMS.api('user'), data, function(c) {
                    content.val('');
                    iCMS.alert(c.msg,c.code);
                    $("li", box).removeClass('checked');
                    $("[name='reason']", box).removeAttr('checked');
                    if(c.code){
                        modal.destroy();
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
                iCMS.user.follow(this,function(c,$this,param){
                    param.follow = (param.follow=='1'?'0':'1');
                    iCMS.param($this,param);
                    $this.removeClass((param.follow=='1'? 'follow' : 'unfollow'));
                    $this.addClass((param.follow=='1' ? 'unfollow' : 'follow'));
                });
            });
            doc.on("click", '.iCMS_article_do', function(event) {
                event.preventDefault();
                var param = iCMS.param($(this));
                if (param.do =='comment') {
                    iCMS.comment.box(this);
                } else if (param.do =='good'||param.do =='bad') {
                    iCMS.article.vote(this);
                }
            });

            doc.on('click', 'a[name="iCMS-report"]', function(event) {
                event.preventDefault();
                if (!iCMS.user_status) {
                    iCMS.LoginBox();
                    return false;
                }
                window.top.iCMS.report(this);
            });
            doc.on('click', 'a[name="iCMS-pm"]', function(event) {
                event.preventDefault();
                if (!iCMS.user_status) {
                    iCMS.LoginBox();
                    return false;
                }
                window.top.iCMS.pm(this);
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
