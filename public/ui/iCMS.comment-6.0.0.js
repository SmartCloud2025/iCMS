(function($) {
    iCMS.comment = {
        loading:$('<div class="commentApp-spinner">正在加载，请稍等 <i class="spinner-lightgray"></i></div>'),
        like_text:'<span class="like-num" data-tip="iCMS:s:1 人觉得这个很赞"><em>1</em> <span>赞</span></span>',
        page_no:{},
        page_total:{},
        cform:function() {
            var form = $('<div class="commentApp-form">'+
                '<div class="commentApp-ipt">' +
                '<input class="commentApp-textarea form-control" type="text" placeholder="写下你的评论…">' +
                '</div>' +
                '<div class="cmt-command">' +
                '<div class="cmt-seccode">' +
                '<input type="text" maxlength="4" name="seccode" class="iCMS_seccode commentApp-seccode form-control" placeholder="验证码">'+
                '<img src="'+iCMS.api('public', "&do=seccode")+'" alt="验证码" class="iCMS_seccode_img r3" title="点击更换验证码图片"/>'+
                '</div>' +
                '<a href="javascript:;" name="addnew" class="btn btn-primary">评论</a>' +
                '<a href="javascript:;" name="closeform" class="cmt-command-cancel">取消</a>' +
                '</div>'+
                '<div class="clearfix"></div>' +
                '</div>');
            if(!this.seccode){
                $(".cmt-seccode",form).hide();
            }
            return form;
        },
        page:function(pn, a) {
            var $this = $(a),
                p = $this.parent(),
                pp = p.parent(),
                query = p.attr('data-query'),
                url = iCMS.config.API+'?'+query+'&pn='+pn;

            //$(".commentApp-list",pp).html(this.loading);
            $.get(url,function(html){
                var box = $(".commentApp-list",$(html)).html(),
                pagenav = $(".commentApp-pagenav",$(html)).html();
                $(".commentApp-list",pp).html(box);
                $(".commentApp-pagenav",pp).html(pagenav);
                iCMS.comment.iframe_height('list');
            });
        },
        iframe_height:function (a) {
            iCMS.api_iframe_height($("body"),'.commentApp-'+a+'-iframe');
        },
        reply:function (a) {
            if (!iCMS.user_status) {
                iCMS.LoginBox();
                return false;
            }
            var item = $(a).parent().parent(),
                param = iCMS.param($(a)),
                form  = this.cform().clone(),
                _form = $('.commentApp-form', item);
            if (_form.length > 0) {
                _form.remove();
                return false;
            }
            $('.commentApp-form', '.commentApp-list').remove();
            form.addClass('expanded').removeClass('commentApp-list-wrap-ft');
            $(a).parent().after(form);

            var textarea = $('.commentApp-textarea', form);
            textarea.data('param', param).focus();
            iCMS.comment.iframe_height('list');
            $('a[name="closeform"]', form).click(function(event) {
                event.preventDefault();
                textarea.val("");
                form.remove();
                iCMS.comment.iframe_height('list');
            });
            $(".iCMS_seccode_img",form).click(function(event) {
                event.preventDefault();
                $(this).attr('src', iCMS.api('public', '&do=seccode&') + Math.random());
            });
        },
        like:function (a) {
            var $this = $(a),param = iCMS.param($this);
            param["do"] = 'like';
            $.get(iCMS.api('comment'), param, function(c) {
                if (c.code) {
                    var p = $this.parent(),
                        like_num = $('.like-num em', p).text();
                    if (like_num) {
                        like_num = parseInt(like_num) + 1;
                        $('.like-num em', p).text(like_num);
                    } else {
                        $this.parent().append(iCMS.comment.like_text);
                    }
                } else {
                    iCMS.alert(c.msg);
                }
            }, 'json');
        },
        addnew:function (a,param,f) {
           if (!iCMS.user_status) {
                iCMS.LoginBox();
                return false;
            }
            var form = $(a).parent().parent(),
                textarea = $('.commentApp-textarea', form),
                seccode = $('.commentApp-seccode', form),
                data = textarea.data('param'),
                cmt_param = $.extend(param, data);

            cmt_param.action  = 'add';
            cmt_param.seccode = seccode.val();
            cmt_param.content = textarea.val();

            if (!cmt_param.content) {
                textarea.focus();
                return false;
            }
            $.post(iCMS.api('comment'), cmt_param, function(c) {
                if (c.code) {
                    if (typeof(f) === "function") {
                        f(c);
                    }
                    textarea.val("");
                    iCMS.comment.iframe_height('list');
                } else {
                    iCMS.alert(c.msg);
                }
            }, 'json');
        },
        list:function (container,iid,id,type) {
            if(!id){
                iCMS.comment.page_no[iid]++;
                if(iCMS.comment.page_total[iid]){
                    if (iCMS.comment.page_no[iid] > iCMS.comment.page_total[iid]) {
                       return false;
                    }
                }
            }
            if(type){
                list = container;
            }else{
                list = $('.commentApp-list',container);
            }
            $.get(iCMS.api('comment'),{
                    'do': 'json',
                    'iid': iid,
                    'id': (id||0),
                    'by': 'ASC',
                    page: iCMS.comment.page_no[iid]
                },
                function(json) {
                    iCMS.comment.loading.remove();
                    if (!json){
                        return false;
                    }
                    if(!id){
                        iCMS.comment.page_total[iid] = json[0].page.total;
                    }
                    $.each(json, function(i, c) {
                        var item = '<div class="commentApp-item" data-id="' + c.id + '">' +
                            '<a title="' + c.user.name + '" data-tip="iCMS:ucard:' + c.userid + '" class="cmt-item-link-avatar" href="' + c.user.url + '">' +
                            '<img src="' + c.user.avatar + '" class="cmt-item-img-avatar">' +
                            '</a>' +
                            '<div class="commentApp-content-wrap">' +
                            '<div class="commentApp-content-hd">' +
                            '<a data-tip="iCMS:ucard:' + c.userid + '" href="' + c.user.url + '" target="_blank" class="zg-link">' + c.user.name + '</a>';
                        if (c.suid == c.userid) {
                            item += '<span class="desc">（作者）</span>';
                        }
                        if (c.reply) {
                            item += '<span class="desc"> 回复 </span>' +
                                '<a data-tip="iCMS:ucard:' + c.reply.uid + '" href="' + c.reply.url + '" target="_blank" class="zg-link">' + c.reply.name + '</a>';
                        }
                        item += '</div>' +
                            '<div class="commentApp-content">' + c.content + '</div>' +
                            '<div class="commentApp-content-ft">' +
                            '<span class="date">' + c.addtime + '</span>' +
                            '<a href="javascript:;" class="reply commentApp-op-link" name="reply_comment" data-param=\'{"id":"' + c.id + '","userid":"' + c.userid + '","name":"' + c.user.name + '"}\'>' +
                            '<i class="iCMS_icon iCMS_icon_comment_reply"></i>回复</a>' +
                            '<a href="javascript:;" class="like commentApp-op-link" name="like_comment" data-param=\'{"id":"' + c.id + '","userid":"' + c.userid + '","name":"' + c.user.name + '"}\'>' +
                            '<i class="iCMS_icon iCMS_icon_comment_like"></i>赞</a>';
                        if (c.up!='0') {
                            item += '<span class="like-num" data-tip="iCMS:s:' + c.up + ' 人觉得这个很赞">' +
                                '<em>' + c.up + '</em> <span>赞</span></span>';
                        }
                        item += '<a href="javascript:;" name="iCMS-report" data-param=\'{"appid":"5","iid":"' + c.id + '","userid":"' + c.userid + '"}\' class="report commentApp-op-link needsfocus">' +
                            '<i class="iCMS_icon iCMS_icon_report"></i>举报</a>' +
                            '</div>' +
                            '</div>' +
                            '</div>';
                        if(type=="after"){
                            list.after(item);
                        }else if(type=="before"){
                            list.before(item);
                        }else{
                            list.append(item);
                        }
                        iCMS.comment.iframe_height('list');
                    });
                    iCMS.user.ucard();
                    if(!id){
                        $(".load-more",container).remove();
                        if (iCMS.comment.page_no[iid] < iCMS.comment.page_total[iid]) {
                            list.after('<a href="javascript:;" class="load-more" name="load-more"><span class="text">显示全部评论</a>');
                        }
                    }
                }, 'json');
        },
        box: function(a) {
            var $this = $(a),
                p = $this.parent(),
                pp = p.parent(),
                param = iCMS.param(p),
                box = $('.commentApp-list-wrap', pp);
            if (box.length > 0) {
                box.remove();
                return false;
            }
            // console.log(param);

            var spike = '<i class="iCMS_icon iCMS_icon_spike commentApp-bubble" style="display: inline; left: 481px;"></i>',
                box   = $('<div class="commentApp-list-wrap">'),
                list  = $('<div class="commentApp-list">'),
                form  = this.cform().clone(),
                iid   = param['iid'];
            box.html(this.loading);
            box.append(spike, list, form);
            p.after(box);
            form.addClass('commentApp-list-wrap-ft');

            //加载评论
            iCMS.comment.page_no[iid]    = 0;
            iCMS.comment.page_total[iid] = 0;
            iCMS.comment.list(box,iid);

            //----------绑定事件----------------
            form.on('focus', '.commentApp-textarea', function(event) {
                $(this).parent().parent().addClass('expanded');
            }).on('click', 'a[name="closeform"]', function(event) {
                event.preventDefault();
                var pp = $(this).parent().parent();
                pp.removeClass('expanded');
                $('.commentApp-textarea', pp).val("");
            });
            // .on('click', '.iCMS_seccode_img', function(event) {
            //     $(".iCMS_seccode_img").attr('src', iCMS.api('public', '&do=seccode&') + Math.random());
            // });
            //加载更多
            box.on('click', 'a[name="load-more"]', function(event) {
                event.preventDefault();
                $(".load-more", list).remove();
                iCMS.comment.list(box,iid);
            });
            //提交评论
            box.on('click', 'a[name="addnew"]', function(event) {
                event.preventDefault();
                var that = $(this);
                iCMS.comment.addnew(this,param,function(c){
                    var count = parseInt($('.iCMS_comment_num', $this).text());
                    $('.iCMS_comment_num', $this).text(count + 1);
                    var item = that.closest(".commentApp-item");
                    iCMS.comment.list(item,iid,c.forward,'after');
                })
            });
            //回复评论
            box.on('click', 'a[name="reply_comment"]', function(event) {
                event.preventDefault();
                iCMS.comment.reply(this);
            });
            //赞评论
            box.on('click', 'a[name="like_comment"]', function(event) {
                event.preventDefault();
                iCMS.comment.like(this);
            });
            //------------
        }
    };
})(jQuery);
$(function(){
    $(".iCMS_seccode_img,.iCMS_seccode_text").click(function(event) {
        event.preventDefault();
        $(".iCMS_seccode_img").attr('src', iCMS.api('public', '&do=seccode&') + Math.random());
    });
})
