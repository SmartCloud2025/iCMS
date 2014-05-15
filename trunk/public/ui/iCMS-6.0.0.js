(function($) {
    window.iCMS = {
        config:{'COOKIE_PRE':'iCMS_'},
        user:{
            uid:function(){
                return iCMS.getcookie('userid');
            },
            nickname:function(){
                var nickname = iCMS.getcookie('nickname');
                return unescape(nickname.replace(/\\u/gi, '%u'));
            },
            auth:function(){
                return iCMS.getcookie('AUTH_INFO');
            },
            data:function(a){
                $.get(iCMS.api('user')+"&do=data", a,function(c) {
                    //if(!c.code) return false;
                    var userhome = $(".iCMS_user_home")
                    userhome.attr("href",c.url);
                    $(".avatar",userhome).attr("src",c.avatar);
                    $(".name",userhome).text(c.nickname);
                },'json');  
            },
            logout:function() {
                $.get(iCMS.api('user')+"&do=logout",function(c) {
                    window.location.href = c.forward
                },'json');   
            },
            status:function() {
                return iCMS.getcookie('AUTH_INFO') ? true : false;
            },
            follow:function(a){
                var b=$(a),fuid = b.attr('data-uid'),fname = b.attr('data-fname'),df = parseInt(b.attr('data-followed'));
                $.get(iCMS.api('user')+"&do=follow",{df:df,'fuid':fuid,'fname':fname},
                function(c) {
                    //console.log(c);
                    if(c.code){
                        b.attr('data-followed',(df?0:1));
                        b.removeClass((df?'unfollow':'follow'));
                        b.addClass((df?'follow':'unfollow'));   
                    }else{
                        alert(c.msg);
                        //iCMS.dialog(c.msg);
                        return false;
                    }
                    // window.location.href = c.forward
                },'json');
            },
            avatar:function(size, uid) {
                size = size || 24;
                uid  = uid || iCMS.user.uid;
                var nuid = pad(uid,7),
                dir1     = nuid.substr(0, 3),
                dir2     = nuid.substr(3, 2);
                avatar   = this.config.avatar + dir1 + '/'+ dir2 + '/' + uid + '.jpg_' + size + 'x' + size + '.jpg';
                return avatar;
            },
        }, 
        article:{
            do:function(a){
                var b=$(a),iid = b.attr('data-iid'),d = b.attr('data-do');

                $.get(iCMS.api('article'),{'do':d,'iid':iid},function(c) {
                    //console.log(c);
                    //iCMS.dialog(c.msg);
                    alert(c.msg);
                    if(c.code){
                        if(d=='good'||d=='comment'){
                            var count = parseInt($('span',b).text());
                            $('span',b).text(count+1);                            
                        }
                    }else{
                        return false;
                    }
                },'json');
            },
            comment_mini_box:function(a){
                $('.comment_mini_box').remove();
                var b   = $(a),p = b.parent(),iid = b.attr('data-iid');
                var box = $('<div class="comment_mini_box">');
                box.html('<div class="input-append">'+
                    '<input class="comment_text span4" type="text">'+
                    '<a href="###" class="btn">评论</a></div>'
                );
                p.after(box);
                $(box).on("click",'.btn',function() {
                    event.preventDefault();
                    var param = {
                        'action':'comment','iid':iid,'title':b.attr('data-title'),
                        'content':$('.comment_text',box).val()
                    };
                    //console.log(param);
                    if(!param.content){
                        alert("写点东西吧!亲!!");
                        $('.comment_text',box).focus();
                        return false;
                    }
                    $.post(iCMS.api('article'),param,function(c) {
//                        console.log(c);
                        alert(c.msg);
                        if(c.code){
                            var count = parseInt($('span',b).text());
                            $('span',b).text(count+1);
                            box.remove();
                        }else{
                            return false;
                        }
                    },'json'); 
                });

            }
        },
        api:function(app){
            return iCMS.config.API+'?app='+app;
        },
    	Init:function(){
            this.user_status = this.user.status();
            // console.log(this.user_status);
            if(this.user_status){
                this.user.data();
                //this.userinfo();
                $("#iCMS_nav_login").hide();
                $("#iCMS_nav_profile").show();
                this.hover(".iCMS_user_home", "#iCMS_user_menu",21);
            }
            $(document).on("click",'.iCMS_user_follow',function() {
                event.preventDefault();
                if(!iCMS.user_status){
                    iCMS.LoginBox(); 
                    return false;
                }
                iCMS.user.follow(this);
                return false;
            });
            $(document).on("click",'.iCMS_article_do',function() {
                event.preventDefault();
                if(!iCMS.user_status){
                    iCMS.LoginBox(); 
                    return false;
                }
                var d = $(this).attr('data-do');
                if(d=='comment'){
                    iCMS.article.comment_mini_box(this);
                }else{
                    iCMS.article.do(this);
                }
                return false;
            });
            $(document).on("click",'.iCMS_user_logout',function() {
                event.preventDefault();
                iCMS.user.logout();
                return false;
            });
            $(document).on("click",'.iCMS_LoginBox',function() {
            	event.preventDefault();
                iCMS.LoginBox();
                return false;
            });

            $("#iCMS_seccode_img,#iCMS_seccode_text").click(function(){
                $("#iCMS_seccode_img").attr('src',iCMS.api('public')+'?app=public&do=seccode&'+Math.random());
            });
            $(".iCMS_API_iframe").load(function(){ 
                $(this).height(0); //用于每次刷新时控制IFRAME高度初始化 
                var height = $(this).contents().height(); 
                $(this).height(height); 
            }); 
    	},

        LoginBox:function(){
            var loginBox    = $('#iCMS_Login_Box');
            //console.log(typeof(loginBox));
            window.iCMS_Login_MODAL = $(this).modal({width:"560px",height: "240px",html:loginBox,scroll:true});
        },

        hover:function(a, b, t, l) {
            var timeOutID = null;
            t = t || 0, l = l || 0;
            $(a).hover(function() {
                var position = $(this).position();
                $(b).show().css({top: position.top + t,left: position.left + l});
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
            $('[data-toggle="modal"]').on("click", function() {
            	event.preventDefault();
                window.iCMS_MODAL = $(this).modal({width: "85%",height: "640px"});
                //$(this).parent().parent().parent().removeClass("open");
                return false;
            });
        },
        setcookie: function(cookieName, cookieValue, seconds, path, domain, secure) {
            var expires = new Date();
            expires.setTime(expires.getTime() + seconds);
            cookieName = this.config.COOKIE_PRE+'_'+cookieName;
            document.cookie = escape(cookieName) + '=' + escape(cookieValue) 
            + (expires ? '; expires=' + expires.toGMTString() : '') 
            + (path ? '; path=' + path : '/') 
            + (domain ? '; domain=' + domain : '') 
            + (secure ? '; secure' : '');
        },
        getcookie: function(name) {
            name             = this.config.COOKIE_PRE+'_'+name;
            var cookie_start = document.cookie.indexOf(name);
            var cookie_end   = document.cookie.indexOf(";", cookie_start);
            return cookie_start == -1 ? '' : unescape(document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length)));
        },
        random: function(len) {
    	    len = len||16;
    	    var chars 	= "abcdefhjmnpqrstuvwxyz23456789ABCDEFGHJKLMNPQRSTUVWYXZ",code	= '';
    	    for ( i = 0; i < len; i++ ) {
    	        code += chars.charAt( Math.floor( Math.random() * chars.length ) )
    	    }
    	    return code;
    	},
    	imgFix:function (im, x, y) {
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
            width: "360px",height: "300px",
            title: im.attr('title') || "iCMS 提示",
            href: im.attr('href')||false,
            target: im.attr('data-target') || "#iCMS_MODAL",
            zIndex: im.attr('data-zIndex')||false,
            overflow: im.attr('data-overflow')||false,
        };
      
        var meta = im.attr('data-meta')?$.parseJSON(im.attr('data-meta')):{};
        var opts = $.extend(defaults,options,meta);
        var mOverlay = $('<div id="modal-overlay" class="modal-overlayBG"></div>');

        return im.each(function() {

            var m = $(opts.target), 
            mBody = m.find(".modal-body"), 
            mTitle = m.find(".modal-header h3");
            opts.title && mTitle.html(opts.title);
            mBody.empty();

            if(opts.overflow){
                $("body").css({"overflow-y": "hidden"});
            }
            
            if (opts.html) {
                var html = opts.html;
                if(typeof(opts.html)=="object"){
                    html = opts.html.html();
                }
                mBody.html(html).css({"overflow-y": "auto"});
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
                opts.zIndex && m.css({"cssText":'z-index:'+opts.zIndex + '!important'});
                m.css({width: opts.width});
                mBody.height(opts.height);
                var left = ($(window).width() - m.width()) / 2, 
                top = ($(window).height() - m.height()) / 2;
                m.css({left: left + "px",top: top + "px"})
                .css({"position": "fixed"});
                
            //console.log({left:left+"px",top:top+"px"});

            };
            im.destroy = function() {
                window.stop ? window.stop() : document.execCommand("Stop");
                m.hide();
                mOverlay.remove();
                m.find(".modal-header h3").html("iCMS 提示");
                if(opts.overflow){
                    $("body").css({"overflow-y": "auto"});
                }
            };
            im.size(opts);
            m.show();
            return im;
        });
    }
})(jQuery);

function pad(num, n) {  
    num=num.toString();
    return Array(n>num.length?(n-(''+num).length+1):0).join(0)+num;  
}