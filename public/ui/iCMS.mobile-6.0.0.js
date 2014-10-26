(function($) {
    var _iCMS = {
        scrollLoad:function (contents,one,next,maxPage,callback){
          if ( !( $(contents).length && $(next).length) ){
            return false;
          };
          var $container = $(contents);
          $container.infinitescroll({
            showPageNum:5,
            maxPage: maxPage||100,
            clickMoreBtn:'.click_more',
            navSelector: next, // selector for the paged navigation
            nextSelector: next + ' a', // selector for the NEXT link (to page 2)
            itemSelector: contents + ' ' + one, // selector for all items you'll retrieve
            loading: {
              finishedMsg: '<button class="click_more btn btn-success btn-lg btn-block"><i class="fa fa-gift"></i> 恭喜您！居然到底了！</button>',
              msgText: '<p class="loading_wrap"><i class="fa fa-spinner"></i> 正在加载...</p>',
              clickMoreMsg:'<button class="click_more btn btn-primary btn-lg btn-block"><i class="fa fa-cloud-download"></i> 点击加载更多</button>',
              img: '',
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
                iCMS.user.follow(this);
                return false;
            });

            this.navbar_box();
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
    iCMS.config.DIALOG = {width:320,height:120};
    iCMS = $.extend(iCMS,_iCMS);//扩展 or 替换 iCMS方法
})(jQuery);
