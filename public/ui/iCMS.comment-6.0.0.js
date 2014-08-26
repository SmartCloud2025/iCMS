(function($) {
    iCMS.comment = {
        loading:$('<div class="commentApp-spinner">正在加载，请稍等 <i class="spinner-lightgray"></i></div>'),
        page: function(pn, a) {
            var $this = $(a),
                p = $this.parent(),
                pp = p.parent(),
                query = p.attr('data-query'),
                url = iCMS.config.API+'?'+query+'&pn='+pn;

            $(".commentApp-list",pp).html(this.loading);
            $.get(url,function(html){
                var box = $(".commentApp-list",$(html)).html(),
                pagenav = $(".commentApp-pagenav",$(html)).html();
                $(".commentApp-list",pp).html(box);
                $(".commentApp-pagenav",pp).html(pagenav);
            });
        },
        reply:function (a,form) {
            var item = $(a).parent().parent(),
                param = iCMS.param($(a)),
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
            $('a[name="closeform"]', form).click(function(event) {
                event.preventDefault();
                textarea.val("");
                form.remove();
            });
        },
        like:function (a) {
            var $this = $(a),param = iCMS.param($this);
            param.do = 'like';
            $.get(iCMS.api('comment'), param, function(c) {
                if (c.code) {
                    var p = $this.parent(),
                        like_num = $('.like-num em', p).text();
                    if (like_num) {
                        like_num = parseInt(like_num) + 1;
                        $('.like-num em', p).text(like_num);
                    } else {
                        $this.parent().append('<span class="like-num" data-tip="iCMS:s:1 人觉得这个很赞"><em>1</em> <span>赞</span></span>')
                    }
                } else {
                    iCMS.alert(c.msg);
                }
            }, 'json');
        },
        addnew:function (a,param,f) {
            var form = $(a).parent().parent(),
                textarea = $('.commentApp-textarea', form),
                data = textarea.data('param'),
                cmt_param = $.extend(param, data);

            cmt_param.action  = 'add';
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
                } else {
                    iCMS.alert(c.msg);
                }
            }, 'json');
        },
        form:function() {
            var form = $('<div class="commentApp-form">'+
                '<div class="commentApp-ipt">' +
                '<input class="commentApp-textarea" type="text" placeholder="写下你的评论…">' +
                '</div>' +
                '<div class="cmt-command clearfix">' +
                '<a href="javascript:;" name="addnew" class="btn btn-primary">评论</a>' +
                '<a href="javascript:;" name="closeform" class="cmt-command-cancel">取消</a>' +
                '</div>'+
                '</div>');
            return form;
        },
        box: function(a) {
            var $this = $(a),
                p = $this.parent(),
                pp = p.parent(),
                param = iCMS.param(p),
                page_no = 0,page_total = 0,
                //def = '写下你的评论…',
                box = $('.commentApp-list-wrap', pp);
            if (box.length > 0) {
                box.remove();
                return false;
            }
            // console.log(param);

            var spike = '<i class="iCMS-icon iCMS-icon-spike commentApp-bubble" style="display: inline; left: 481px;"></i>',
                box = $('<div class="commentApp-list-wrap">'),
                list = $('<div class="commentApp-list">'),
                form = this.form();
            box.html(this.loading);
            box.append(spike, list, form);
            p.after(box);
            //加载评论
            comment_list();

            //----------绑定事件----------------
            form.on('focus', '.commentApp-textarea', function(event) {
                $(this).parent().parent().addClass('expanded');
            }).on('click', 'a[name="closeform"]', function(event) {
                event.preventDefault();
                var pp = $(this).parent().parent();
                pp.removeClass('expanded');
                $('.commentApp-textarea', pp).val("");
            });
            //提交评论
            box.on('click', 'a[name="addnew"]', function(event) {
                event.preventDefault();
                iCMS.comment.addnew(this,param,function(c){
                    var count = parseInt($('span', $this).text());
                    $('span', $this).text(count + 1);
                    comment_list(c.forward);
                })
            });
            //加载更多
            box.on('click', 'a[name="load-more"]', function(event) {
                event.preventDefault();
                $(".load-more", list).remove();
                comment_list();
            });

            //回复评论
            box.on('click', 'a[name="reply_comment"]', function(event) {
                event.preventDefault();
                iCMS.comment.reply(this,form.clone());
            });
            //赞评论
            list.on('click', 'a[name="like_comment"]', function(event) {
                event.preventDefault();
                iCMS.comment.like(this);
            });

            function comment_list(id) {
                if(page_total){
                    page_no++;
                    if (page_no > page_total) {
                       return false;
                    }
                }else{
                    page_no++;
                }

                $.get(iCMS.api('comment'), {
                        'do': 'json',
                        'iid': param['iid'],
                        'id': (id||0),
                        'by': 'ASC',
                        page: page_no
                    },
                    function(json) {
                        iCMS.comment.loading.remove();
                        if (!json){
                            return false;
                        }
                        page_total = json[0].page.total;
                        form.addClass('commentApp-list-wrap-ft');
                        $.each(json, function(i, c) {
                            var item = '<div class="commentApp-item" data-id="' + c.id + '">' +
                                '<a title="' + c.user.name + '" data-tip="iCMS:ucard:' + c.userid + '" class="cmt-item-link-avatar" href="' + c.user.url + '">' +
                                '<img src="' + c.user.avatar + '" class="cmt-item-img-avatar">' +
                                '</a>' +
                                '<div class="commentApp-content-wrap">' +
                                '<div class="commentApp-content-hd">' +
                                '<a data-tip="iCMS:ucard:' + c.userid + '" href="' + c.user.url + '" class="zg-link">' + c.user.name + '</a>';
                            if (c.suid == c.userid) {
                                item += '<span class="desc">（作者）</span>';
                            }
                            if (c.reply) {
                                item += '<span class="desc">回复 </span>' +
                                    '<a data-tip="iCMS:ucard:' + c.reply.uid + '" href="' + c.reply.url + '" class="zg-link">' + c.reply.name + '</a>';
                            }
                            item += '</div>' +
                                '<div class="commentApp-content">' + c.content + '</div>' +
                                '<div class="commentApp-content-ft">' +
                                '<span class="date">' + c.addtime + '</span>' +
                                '<a href="javascript:;" class="reply commentApp-op-link" name="reply_comment" data-param=\'{"id":"' + c.id + '","userid":"' + c.userid + '","name":"' + c.user.name + '"}\'>' +
                                '<i class="iCMS-icon iCMS-icon-comment-reply"></i>回复</a>' +
                                '<a href="javascript:;" class="like commentApp-op-link" name="like_comment" data-param=\'{"id":"' + c.id + '","userid":"' + c.userid + '","name":"' + c.user.name + '"}\'>' +
                                '<i class="iCMS-icon iCMS-icon-comment-like"></i>赞</a>';
                            if (c.up!='0') {
                                item += '<span class="like-num" data-tip="iCMS:s:' + c.up + ' 人觉得这个很赞">' +
                                    '<em>' + c.up + '</em> <span>赞</span></span>';
                            }
                            item += '<a href="javascript:;" name="iCMS-report" data-param=\'{"appid":"5","iid":"' + c.id + '","userid":"' + c.userid + '"}\' class="report commentApp-op-link needsfocus">' +
                                '<i class="iCMS-icon iCMS-icon-report"></i>举报</a>' +
                                '</div>' +
                                '</div>' +
                                '</div>';
                            list.append(item);
                        });
                        if (page_no < page_total) {
                            list.after('<a href="javascript:;" class="load-more" name="load-more"><span class="text">显示全部评论</a>');
                        }else{
                            $(".load-more",box).remove();
                        }

                    }, 'json');
            }
            //------------
        }
    };
})(jQuery);
