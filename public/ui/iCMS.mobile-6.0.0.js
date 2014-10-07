(function($) {
    iCMS.user = {
            data:{},
            data: function(param) {
                $.get(iCMS.api('user', '&do=data'), param, function(c) {
                    //if(!c.code) return false;
                    iCMS.user.data = c;
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
                    //console.log(param);
                $.post(iCMS.api('user', "&do=follow"), param, function(c) {
                    if (c.code) {
                        param['follow'] = (param['follow']=='1'?'0':'1');
                        iCMS.param($this,param);
                        $this.removeClass((param['follow']=='1'? 'follow' : 'unfollow'));
                        $this.addClass((param['follow']=='1' ? 'unfollow' : 'follow'));
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
        scrollLoad:function (contents,one,next,maxPage,callback){
          if ( !( $(contents).length && $(next).length) ){
            return false;
          };
          var $container = $(contents);
          $container.infinitescroll({
            maxPage: maxPage||3,
            clickMoreBtn:'.click_more',
            navSelector: next, // selector for the paged navigation
            nextSelector: next + ' a', // selector for the NEXT link (to page 2)
            itemSelector: contents + ' ' + one, // selector for all items you'll retrieve
            loading: {
              finishedMsg: '<a href="javascript:void(0);" class="click_more">恭喜您！居然到底了！</a>',
              msgText: '<p class="loading_wrap"><i class="loading"></i> 正在加载...</p>',
              clickMoreMsg:'<a href="javascript:void(0);" class="click_more">点击加载更多</a>',
              img: ''
            }
          },
          // trigger Masonry as a callback
          function(newElements) {
            var $newElems = $(newElements).css({
              opacity: 0
            });
            if (typeof(callback) === "function") {
                    callback($newElems);
            }
            $container.append($newElems);
            $newElems.animate({
              opacity: 1
            }, "fast", function() {
              $("#infscr-loading").fadeOut('normal');
            });
            // lazylaod
            $("img").lazyload();

          });
            return $container;
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
            doc.on("click", '.iCMS_user_logout', function(event) {
                event.preventDefault();
                iCMS.user.logout();
                return false;
            });

            $(".iCMS_seccode_img,.iCMS_seccode_text").click(function() {
                $(".iCMS_seccode_img").attr('src', iCMS.api('public', '&do=seccode&') + Math.random());
            });

            $('.tip').tooltip();
            $("img").lazyload();
            $(window).scroll(function () {
                if ($(this).scrollTop() > 100) {
                    $('#iCMS-scrollUp').fadeIn();
                } else {
                    $('#iCMS-scrollUp').fadeOut();
                }
            });
            $('#iCMS-scrollUp').click(function () {
                $('body,html').animate({
                    scrollTop: 0
                }, 800);
                return false;
            });

        },
        api_iframe_height:function(a,b){
            var a = a||window.top.$(b);
            a.height(0); //用于每次刷新时控制IFRAME高度初始化
            var height = a.contents().height();
            a.height(height);
            //window.top.$('.iCMS_API_iframe-loading').hide();
        },
        hover: function() {},
        LoginBox: function() {
            var dialog = window.top.document.getElementById("iCMS-login-dialog");
            iCMS_Login_MODAL = window.top.$(this).modal({
                width: "560px",
                html: dialog,
                scroll: true
            });

            this.user.login("#iCMS-login-dialog");
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
