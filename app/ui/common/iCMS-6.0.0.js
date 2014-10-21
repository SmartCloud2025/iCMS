(function($) {
    window.iCMS = {
        config:{
            API: '/public/api.php',
            PUBLIC: '/',
            COOKIE: 'iCMS_',
            AUTH:'USER_AUTH',
            DIALOG:[],
        },
        init: function(options) {
            this.config = $.extend(this.config,options);
        },
        start:function(){
            var doc = $(document);
            this.user_status = this.user.status();
            if (this.user_status) {
                this.user.data();
                $("#iCMS-nav-login").hide();
                $("#iCMS-nav-profile").show();
            }
            doc.on("click", '.iCMS_user_login', function(event) {
                event.preventDefault();
                iCMS.LoginBox(true);
                return false;
            });
            doc.on("click", '.iCMS_user_logout', function(event) {
                event.preventDefault();
                iCMS.user.logout();
                return false;
            });
            doc.on("click", '.iCMS_article_do', function(event) {
                event.preventDefault();
                var param = iCMS.param($(this));
                if (param.do =='comment') {
                    iCMS.comment.box(this);
                } else if (param.do =='favorite') {
                    iCMS.user.favorite(this);
                } else if (param.do =='good'||param.do =='bad') {
                    iCMS.article.vote(this);
                }
            });
            doc.on('click', 'a[name="iCMS-report"]', function(event) {
                event.preventDefault();
                iCMS.user.report(this);
            });
            doc.on('click', 'a[name="iCMS-pm"]', function(event) {
                event.preventDefault();
                iCMS.user.pm(this);
            });
            $(".iCMS_seccode_img,.iCMS_seccode_text").click(function() {
                $(".iCMS_seccode_img").attr('src', iCMS.api('public', '&do=seccode&') + Math.random());
            });
            $(".iCMS_search_btn").click(function(event) {
                var q = $('[name="q"]',"#iCMS-search-box").val();
                if(q==""){
                    iCMS.alert("请输入关键词");
                    return false;
                }
            });

            $(".tip").tooltip();
            $("img.lazy").lazyload();
        },
        api: function(app, _do) {
            return iCMS.config.API + '?app=' + app + (_do || '');
        },
        multiple: function(a) {
            var $this = $(a),
            $parent   = $this.parent(),
            param     = iCMS.param($this),
            _param    = iCMS.param($parent);
            return $.extend(param,_param);
        },
        param: function(a,_param) {
            if(_param){
                a.attr('data-param',iCMS.json2str(_param));
                return;
            }
            var param = a.attr('data-param') || false;
            if (!param) return {};
            return $.parseJSON(param);
        },
        tip: function(el, title,placement) {
            placement = placement||el.attr('data-placement');
            var container = el.attr('data-container');
            if(container){
                $(container).empty();
            }
            el.tooltip({
              html: true,container:container||false,
              placement: placement||'right',
              trigger: 'manual',
              title:title,
            }).tooltip('show');
        },
        alert: function(msg,ok,callback) {
            var opts = ok ? {
                label: 'success',
                icon: 'check'
            } : {
                label: 'warning',
                icon: 'warning'
            }
            opts.id      = 'iPHP-DIALOG-ALERT';
            opts.content = msg;
            opts.height  = 150;
            window.top.iCMS.dialog(opts,callback);
        },
        dialog: function(options,callback) {
            var defaults = {
                id:'iCMS-DIALOG',
                title:'iCMS - 提示信息',
                width:360,height:150,
                className:'iCMS_dialog',//skin:'iCMS_dialog',
                backdropBackground:'#666',backdropOpacity: 0.5,
                fixed:true,autofocus:false,quickClose:true,
                lock:true,time: 3000,
                label:'success',icon: 'check',api:false,elemBack:'beforeremove',
            },_elemBack,timeOutID = null,
            opts = $.extend(defaults,options,iCMS.config.DIALOG);

            if(opts.follow){
                opts.fixed = false;
                opts.lock  = false;
                opts.skin = 'iCMS_tooltip_popup'
                opts.className = 'ui-popup';
                opts.backdropOpacity = 0;
            }
            var content = opts.content;
            //console.log(typeof content);
            if (content instanceof jQuery){
                opts.content = content.html();
            }else if (typeof content === "string") {
                //console.log('typeof content === "string"');
                opts.content = __msg(content);
            }else if (typeof content === "object") {
                if(content.nodeType === 1){
                    if (_elemBack) {
                        _elemBack();
                        delete _elemBack;
                    };
                    //console.log($(content).data( "events" ));
                    // artDialog 5.0.4
                    // 让传入的元素在对话框关闭后可以返回到原来的地方
                    var display = content.style.display;
                    var prev    = content.previousSibling;
                    var next    = content.nextSibling;
                    var parent  = content.parentNode;
                    _elemBack = function () {
                        if (prev && prev.parentNode) {
                            prev.parentNode.insertBefore(content, prev.nextSibling);
                        } else if (next && next.parentNode) {
                            next.parentNode.insertBefore(content, next);
                        } else if (parent) {
                            parent.appendChild(content);
                        };
                        content.style.display = display;
                        _elemBack = null;
                    };
                    opts.width   = 'auto';
                    opts.height  = 'auto';
                    opts.content = content;
                    $(content).show();
                }
            }
            opts.onclose = function(){
                __callback('close');
            };
            opts.onbeforeremove = function(){
                __callback('beforeremove');
            };
            opts.onremove = function(){
                __callback('remove');
            };
            var d = window.dialog(opts);

            //console.log(opts.api);
            if(opts.lock){
                d.showModal();
                // $(d.backdrop).addClass("ui-popup-overlay").click(function(){
                //     d.close().remove();
                // })
            }else{
                d.show(opts.follow);
                if(opts.follow){
                    //$(d.backdrop).remove();
                    // $("body").bind("click",function(){
                    //     d.close().remove();
                    // })
                }
                //$(d.backdrop).css("opacity","0");
            }
            if(opts.time){
                timeOutID = window.setTimeout(function(){
                    d.destroy();
                },opts.time);
            }
            d.destroy = function (){
                d.close().remove();
            }

            function __callback(type){
                window.clearTimeout(timeOutID);
                console.log('opts.elemBack:'+opts.elemBack,'type:'+type);
                if(opts.elemBack==type){
                    console.log('_elemBack:'+_elemBack);
                    if (_elemBack) { //删除前把元素返回原来的地方
                        _elemBack();
                    }
                }

                if (typeof(callback) === "function") {
                    callback(type);
                }
            }
            function __msg(content){
                return '<table class=\"ui-dialog-table\" align=\"center\"><tr><td valign=\"middle\">'
                +'<div class=\"iPHP-msg\">'
                +'<span class=\"label label-' + opts.label + '\">'
                +'<i class=\"fa fa-' + opts.icon + '\"></i> '
                + content
                + '</span></div>'
                +'</td></tr></table>';
            }
            // dd = $.extend(d,{
            //     content:function(c){
            //         d.content(__msg(c));
            //     }
            // });
            return d;
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
        },
        json2str:function(o){
            var arr = [];
            var fmt = function(s) {
                if (typeof s == 'object' && s != null) return iCMS.json2str(s);
                return /^(string|number)$/.test(typeof s) ? '"' + s + '"' : s;
            }
            for (var i in o)
                 arr.push('"' + i + '":'+ fmt(o[i]));
            return '{' + arr.join(',') + '}';
        },
        api_iframe_height:function(a,iframe){
            var height = a.height();
            $(iframe).height(height);
        },
        format:function(content,ubb) {
            content = content.replace(/\/"/g, '"')
                .replace(/\\\&quot;/g, "")
                .replace(/\r/g, "")
                .replace(/on(\w+)="[^"]+"/ig, "")
                .replace(/<script[^>]*?>(.*?)<\/script>/ig, "")
                .replace(/<style[^>]*?>(.*?)<\/style>/ig, "")
                .replace(/style=[" ]?([^"]+)[" ]/ig, "")
                .replace(/<a[^>]+href=[" ]?([^"]+)[" ]?[^>]*>(.*?)<\/a>/ig, "[url=$1]$2[/url]")
                .replace(/<img[^>]+src=[" ]?([^"]+)[" ]?[^>]*>/ig, "[img]$1[/img]")
                .replace(/<embed[^>]+src=[" ]?([^"]+)[" ]\s+width=[" ]?([^"]\d+)[" ]\s+height=[" ]?([^"]\d+)[" ]?[^>]*>.*?<\/embed>/ig, "[media=$2,$3]$1[/media]")
                .replace(/<embed[^>]+src=[" ]?([^"]+)[" ]?[^>]*>.*?<\/embed>/ig, "[media]$1[/media]")
                .replace(/<b[^>]*>(.*?)<\/b>/ig, "[b]$1[/b]")
                .replace(/<strong[^>]*>(.*?)<\/strong>/ig, "[b]$1[/b]")
                .replace(/<p[^>]*?>/g, "\n\n")
                .replace(/<br[^>]*?>/g, "\n")
                .replace(/<[^>]*?>/g, "");
            if(ubb){
                content = content.replace(/\n+/g, "[iCMS.N]");
                content = this.n2p(content,ubb);
                return content;
            }
            content = content.replace(/\[url=([^\]]+)\]\n(\[img\]\1\[\/img\])\n\[\/url\]/g, "$2")
                .replace(/\[img\](.*?)\[\/img\]/ig, '<p><img src="$1" /></p>')
                .replace(/\[b\](.*?)\[\/b\]/ig, '<b>$1</b>')
                .replace(/\[url=([^\]|#]+)\](.*?)\[\/url\]/g, '$2')
                .replace(/\[url=([^\]]+)\](.*?)\[\/url\]/g, '<a target="_blank" href="$1">$2</a>')
                .replace(/\n+/g, "[iCMS.N]");

            content = this.n2p(content);
            content = content.replace(/#--iCMS.PageBreak--#/g, "<!---->#--iCMS.PageBreak--#")
                .replace(/<p>\s*<p>/g, '<p>')
                .replace(/<\/p>\s*<\/p>/g, '</p>')
                .replace(/<p>\s*<\/p>/g, '')
                .replace(/<p><br\/><\/p>/g, '');
            return content;
        },
        n2p:function(cc,ubb) {
            var c = '',s = cc.split("[iCMS.N]");
            for (var i = 0; i < s.length; i++) {
                while (s[i].substr(0, 1) == " " || s[i].substr(0, 1) == "　") {
                    s[i] = s[i].substr(1, s[i].length);
                }
                if (s[i].length > 0){
                    if(ubb){
                        c += s[i] + "\n";
                    }else{
                        c += "<p>" + s[i] + "</p>";
                    }
                }
            }
            return c;
        },
    };
    // article
    iCMS.article = {
        vote: function(a) {
            var $this = $(a),data = iCMS.multiple(a);
            $.get(iCMS.api('article'), data, function(c) {
                if (c.code) {
                   var numObj = '.iCMS_'+data.do+'_num',
                       count = parseInt($(numObj, $this).text());
                    $(numObj, $this).text(count + 1);
                } else {
                    iCMS.alert(c.msg, c.code);
                    return false;
                }
            }, 'json');
        }
    };
})(jQuery);

// lazy load
(function(a){
    a.fn.lazyload=function(b){var c={attr:"data-src",container:a(window),callback:a.noop};var d=a.extend({},c,b||{});d.cache=[];a(this).each(function(){var h=this.nodeName.toLowerCase(),g=a(this).attr(d.attr);var i={obj:a(this),tag:h,url:g};d.cache.push(i)});var f=function(g){if(a.isFunction(d.callback)){d.callback.call(g.get(0))}};var e=function(){var g=d.container.height();if(a(window).get(0)===window){contop=a(window).scrollTop()}else{contop=d.container.offset().top}a.each(d.cache,function(m,n){var p=n.obj,j=n.tag,k=n.url,l,h;if(p){l=p.offset().top-contop,l+p.height();if((l>=0&&l<g)||(h>0&&h<=g)){if(k){if(j==="img"){f(p.attr("src",k))}else{p.load(k,{},function(){f(p)})}}else{f(p)}n.obj=null}}})};e();d.container.bind("scroll",e)}}
)(jQuery);

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
