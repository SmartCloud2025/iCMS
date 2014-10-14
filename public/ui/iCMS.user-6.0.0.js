(function($) {
    iCMS.user = {
            data:{},
            data: function(param) {
                $.get(iCMS.api('user', '&do=data'), param, function(c) {
                    if(!c.code) {
                        window.top.location.href = c.forward
                        return false
                    };
                    iCMS.user.data = c;
                    var user_home = $(".iCMS_user_home")
                    user_home.attr("href", c.url);
                    $(".name", user_home).text(c.nickname);
                    $(".avatar", user_home).attr("src", c.avatar);
                }, 'json');
            },
            logout: function() {
                $.get(iCMS.api('user', "&do=logout"), function(c) {
                    window.top.location.href = c.forward
                }, 'json');
            },
            status: function() {
                return iCMS.getcookie(iCMS.config.AUTH) ? true : false;
            },
            ucard:function(){
              $("[data-tip^='iCMS:ucard']").poshytip({
                className: 'iCMS_tooltip',
                alignTo: 'target',alignX: 'center',
                offsetX: 0,offsetY: 5,
                fade: false,slide: false,
                content: function(updateCallback) {
                    $.get(iCMS.api('user', "&do=ucard"),
                      {'uid': $(this).attr('data-tip').replace('iCMS:ucard:','')},
                      function(html) {
                        updateCallback(html);
                    });
                    return '<div class="tip_info"><img src="'+iCMS.config.PUBLIC+'/ui/img/lightgray-loading.gif"><span> 用户信息加载中……</span></div>';
                }
              });
            },
            follow: function(a,callback) {
                if (!iCMS.user_status) {
                  iCMS.LoginBox();
                  return false;
                }
                var $this = $(a),data = iCMS.multiple(a);
                $.post(iCMS.api('user','&do=follow'), data, function(c) {
                    if (c.code) {
                        if (typeof(callback) === "function") {
                                callback(c,$this,data);
                        }
                    } else {
                        iCMS.alert(c.msg);
                        return false;
                    }
                    // window.location.href = c.forward
                }, 'json');
            },
            noavatar: function() {
                var img = event.srcElement;
                img.src = iCMS.config.PUBLIC+'/ui/avatar.gif';
            },
            nocover: function() {
                var img = event.srcElement;
                img.src = iCMS.config.PUBLIC+'/ui/coverpic.jpg';
            },
    };
})(jQuery);
