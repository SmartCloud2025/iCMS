(function($) {
    iCMS.user = {
            OPENID:{},
            info:{},
            data: function(param) {
                $.get(iCMS.api('user', '&do=data'), param, function(c) {
                    if(!c.code) {
                        window.top.location.href = c.forward
                        return false
                    };
                    iCMS.user.info = c;
                    iCMS.user.set_info(c,$(".iCMS_user_home"));
                }, 'json');
            },
            set_info:function(c,el){
                var a = el.attr("href", c.url);
                $(".name", a).text(c.nickname);
                $(".avatar", a).attr("src", c.avatar);
            },
            logout: function() {
                $.get(iCMS.api('user', "&do=logout"),{'forward':window.top.location.href},
                function(c) {
                    window.top.location.reload();
                    //window.top.location.href = c.forward
                }, 'json');
            },
            status: function() {
                return iCMS.getcookie(iCMS.config.AUTH) ? true : false;
            },
            ucard:function(){
              var ucardCache = {};
              $("[data-tip^='iCMS:ucard']").poshytip({
                className:'iCMS_tooltip',
                alignTo:'target',alignX:'center',
                offsetX:0,offsetY:5,
                fade:false,slide:false,
                content: function(updateCallback) {
                    var uid = $(this).attr('data-tip').replace('iCMS:ucard:','');
                    if(uid){
                        $.get(iCMS.api('user', "&do=ucard"),{'uid':uid},
                          function(container) {
                            updateCallback(container);
                        });
                        return '<div class="tip_info"><img src="'+iCMS.config.PUBLIC+'/ui/img/lightgray-loading.gif"><span> 用户信息加载中……</span></div>';
                    }
                }
              });
            },
            follow: function(a,callback) {
                if (!iCMS.user_status) {
                  iCMS.LoginBox();
                  return false;
                }
                var data = iCMS.multiple(a);
                data.action = "follow";
                $.post(iCMS.api('user'), data, function(c) {
                    if (c.code) {
                        if (typeof(callback) === "function") {
                            callback(c,data);
                        }
                    } else {
                        iCMS.alert(c.msg);
                        return false;
                    }
                    // window.location.href = c.forward
                }, 'json');
            },
            pm:function(a){
                if (!iCMS.user_status) {
                    iCMS.LoginBox();
                    return false;
                }
                var $this  = $(a),
                    box    = document.getElementById("iCMS-pm-box"),
                    dialog = iCMS.dialog({title: '发送私信',content:box,elemBack:'remove'}),
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
                _title    = $this.attr('title')||'为什么举报这个评论?',
                box       = document.getElementById("iCMS-report-box"),
                dialog    = iCMS.dialog({title:_title,content:box,elemBack:'remove'});
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
                });
                $('[name="ok"]', box).click(function(event) {
                    event.preventDefault();
                    var data    = iCMS.param($this),
                    content     = $("[name='content']", box);
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
                        }
                    }, 'json');
                });
            },
            favorite: function(a,callback) {
                if (!iCMS.user_status) {
                    iCMS.LoginBox();
                    return false;
                }
                var $this = $(a),
                box    = document.getElementById("iCMS-favorite-box"),
                dialog = iCMS.dialog({title: '添加到收藏夹',content:box,elemBack:'remove'});
                //console.log(dialog);
                $(".favorite_list_content",box).empty();
                $('.cancel', box).click(function(event) {
                    event.preventDefault();
                    dialog.remove();
                });
                $('.create,.cancel_create', box).click(function(event) {
                    event.preventDefault();
                    if($(this).hasClass('create')){
                        dialog.title("创建新收藏夹");
                    }else{
                        dialog.title("添加到收藏夹");
                    }
                    $(".favorite_create",box).toggle();
                    $(".favorite_list",box).toggle();
                });

                $('[name="create"]', box).click(function(event){
                    event.preventDefault();
                    var data = {
                        'action':'create',
                        'title':$('[name="title"]',box).val(),
                        'description':$('[name="description"]',box).val(),
                        'mode':$('[name="mode"]:checked',box).val(),
                    }
                    if(data.title==""){
                        $('[name="title"]',box).focus();
                        $('.fav_add_title_error',box).text('请填写标题').show();
                        return false;
                    }
                    $.post(iCMS.api('favorite'), data, function(c) {
                        $(".favorite_create",box).toggle();
                        $(".favorite_list",box).toggle();
                        if(c.code){
                            item = __item({
                                'id':c.forward,
                                'title':data.title,
                                'count':0,'follow':0,
                            });
                            $(".favorite_list_content",box).append(item);
                        }else{
                            $('.fav_add_title_error',box).text(c.msg).show();
                        }
                        //iCMS.alert(c.msg,c.code);
                    }, 'json');
                });

                function __item(val){
                    return '<a class="favo-list-item-link r5 " href="javascript:;" data-fid="'+val.id+'">'
                    +'<span class="favo-list-item-title">'+val.title+'</span>'
                    +'<span class="meta gray">'
                        +'<span class="num">'+val.count+'</span> 篇文章'
                        +'<span class="bull">•</span> '+val.follow+' 人关注'
                    +'</span></a><div class="clearfix mt10"></div>';
                }

                $.get(iCMS.api('favorite',"&do=list"),function(json) {
                    var item ='';
                    $.each(json, function(i,val){
                        item+=__item(val);
                    });
                    $(".favorite_list_content",box).html(item);
                },'json');

                $(box).on("click", '.favo-list-item-link', function(event) {
                    console.log(this);
                    var $this = $(this),
                    data = iCMS.multiple(a),
                    num  = parseInt($('.num',$this).text());
                    data.fid    = $this.attr('data-fid');
                    if($this.hasClass('active')){
                        data.action = 'delete';
                    }else{
                        data.action = 'add';
                    }
                    $.post(iCMS.api('favorite'),data,function(c) {
                        if(c.code){
                            if($this.hasClass('active')){
                                $('.num',$this).text(num-1);
                                $this.removeClass('active');
                            }else{
                                $('.num',$this).text(num+1);
                                $this.addClass('active');
                            }
                        }else{
                            iCMS.alert(c.msg);
                        }
                    },'json');
                });
            },
            noavatar: function() {
                var img = event.srcElement;
                img.src = iCMS.config.PUBLIC+'/ui/avatar.gif';
            },
            nocover: function(t) {
                var img = event.srcElement;
                var name= 'coverpic'
                if(t=='m'){
                    name = 'm_coverpic';
                }
                img.src = img.src = iCMS.config.PUBLIC+'/ui/'+name+'.jpg';
            },
    };
})(jQuery);
