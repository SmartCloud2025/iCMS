(function($) {
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

        run: function() {
            iCMS.start();

            var doc = $(document);
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
            this.navbar_box();
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
        navbar_box:function(){
            var touchmove_handler = function (event) {
                  event.preventDefault();
            };
            $("#iCMS-menu-box").on('show.bs.collapse', function () {
              document.body.addEventListener('touchmove',touchmove_handler, false);
            }).on('hide.bs.collapse', function () {
              document.body.removeEventListener('touchmove',touchmove_handler, false);
            })
            $(".menu_right","#iCMS-menu-box").click(function(event) {
              $("#iCMS-menu-box").collapse('hide');
            });
        },
        LoginBox: function(a) {
            if(!a){
                if(!confirm('您还没有登录,请先登录?')){
                    return;
                }
            }
            window.top.document.location.href = iCMS.api("user","&do=login&forward="+window.top.document.URL);
        },
        hover: function() {},
    };
    iCMS = $.extend(iCMS,_iCMS);//扩展 or 替换 iCMS方法
})(jQuery);