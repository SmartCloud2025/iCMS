(function($) {
    var _iCMS = {
        run: function() {
            iCMS.start();
            var doc = $(document);
            this.user.ucard();
            if (this.user_status) {
                this.hover($(".iCMS_user_home"),20,-10);
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
            iCMS.user.login(box);
            iCMS.dialog({title: '用户登陆',content:box,elemBack:'remove'});
        },
        hover: function(a, t, l) {
            var timeOutID = null,t = t || 0, l = l || 0,
            pop = a.parent().find('.popover');
            a.hover(function() {
                $(".popover").hide();
                var position = $(this).position();
                pop.show().css({
                    top: position.top + t,
                    left: position.left + l
                });
                window.clearTimeout(timeOutID);
            }, function() {
                timeOutID = window.setTimeout(function() {
                    pop.hide();
                }, 2500);
            });
            pop.hover(function() {
                window.clearTimeout(timeOutID);
                $(this).show();
            }, function() {
                $(this).hide();
            });
        }
    };
    window.iCMS = $.extend(window.iCMS,_iCMS);//扩展 or 替换 iCMS方法
})(jQuery);
