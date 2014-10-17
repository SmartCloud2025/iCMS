(function($) {
    var _iCMS = {
        pm:function(a){
            if (!iCMS.user_status) {
                iCMS.LoginBox();
                return false;
            }
            var $this  = $(a),
                box    = document.getElementById("iCMS-pm-box"),
                dialog = iCMS.dialog({title: '发送私信',content:box}),
                inbox  = $this.attr('href'),
                data   = iCMS.multiple(a),
                content = $("[name='content']", box).val('');
            $(".pm_warnmsg", box).hide();
            $('.pm_uname', box).text(data.name);
            $('.pm_inbox', box).attr("href",inbox);
            $('.cancel', box).click(function(event) {
                event.preventDefault();
                dialog.remove();
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
                    dialog.remove();
                    iCMS.alert(c.msg,c.code);
                }, 'json');
            });
        },
        report:function(a) {
            if (!iCMS.user_status) {
                iCMS.LoginBox();
                return false;
            }
            var $this = $(a),
                box   = document.getElementById("iCMS-report-box"),
                dialog = iCMS.dialog({title: '为什么举报这个评论?',content:box});
                // modal = $this.modal({
                //     title: '为什么举报这个评论?',
                //     width: "420px",
                //     html: box,
                //     scroll: true
                // });
            $("li", box).click(function(event) {
                event.preventDefault();
                $("li", box).removeClass('checked');
                $(this).addClass('checked');
                //$("[name='reason']",box).prop("checked",false);
                $("[name='reason']",this).prop("checked",true);
            });
            $('.cancel', box).click(function(event) {
                event.preventDefault();
                dialog.remove();
                //modal.destroy();
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
                    $("li", box).removeClass('checked');
                    $("[name='reason']", box).removeAttr('checked');
                    iCMS.alert(c.msg,c.code);
                    if(c.code){
                        dialog.remove();
                        //modal.destroy();
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
                var $this = $(this);
                iCMS.user.follow(this,function(c,param){
                    param.follow = (param.follow=='1'?'0':'1');
                    iCMS.param($this,param);
                    $this.removeClass((param.follow=='1'? 'follow' : 'unfollow'));
                    $this.addClass((param.follow=='1' ? 'unfollow' : 'follow'));
                });
            });

            doc.on('click', 'a[name="iCMS-report"]', function(event) {
                event.preventDefault();
                iCMS.report(this);
            });
            doc.on('click', 'a[name="iCMS-pm"]', function(event) {
                event.preventDefault();
                iCMS.pm(this);
            });
            doc.on('click', 'a[name="iCMS-follow"]', function(event) {
                event.preventDefault();
                var $this = $(this),$parent = $this.parent();
                iCMS.user.follow(this,function(){
                    $('a[name="iCMS-follow"]',$parent).removeClass('hide');
                    $this.addClass('hide');
                });
            });
        },
        LoginBox: function(a) {
            var box = document.getElementById("iCMS-login-box");
            iCMS.dialog({title: '用户登陆',content:box});
            iCMS.user.login(box);
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
                window.clearTimeout(timeOutID);
            }, function() {
                timeOutID = window.setTimeout(function() {
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
