(function($) {
    window.iCMS.article = {
        good: function(a) {
            var $this = $(a),
                p = $this.parent(),
                param = iCMS.param(p);
            param['do'] = 'good';
            $.get(iCMS.api('article'), param, function(c) {
                iCMS.alert(c.msg, c.code);
                if (c.code) {
                    var count = parseInt($('span', b).text());
                    $('span', b).text(count + 1);
                } else {
                    return false;
                }
            }, 'json');
        },
        comment_box: function(a) {
            var $this = $(a),
                p = $this.parent(),
                pp = p.parent(),
                param = iCMS.param(p),
                page = 1,
                def = '写下你的评论…',
                box = $('.commentApp-box', pp);
            if (box.length > 0) {
                box.remove();
                return false;
            }
            // console.log(param);

            var spike = '<i class="iCMS_icon iCMS_icon_spike commentApp-bubble" style="display: inline; left: 481px;"></i>',
                box = $('<div class="commentApp-box">'),
                list = $('<div class="commentApp-list">'),
                form = $('<div class="commentApp-form">');
            form.html('<div class="commentApp-ipt">' +
                '<input class="commentApp-textarea" type="text" value="' + def + '">' +
                '</div>' +
                '<div class="app-command clearfix">' +
                '<a href="#" name="addnew" class="btn btn-primary">评论</a>' +
                '<a href="###" name="closeform" class="app-command-cancel">取消</a>' +
                '</div>'
            );
            var loging = $('<div class="commentApp-spinner">正在加载，请稍等 <i class="spinner-lightgray"></i></div>');
            box.html(loging);
            box.append(spike, list, form);
            p.after(box);
            //加载评论
            comment_list();

            //----------绑定事件----------------
            //box.on('focus', 'commentApp-textarea', function(event) {
            $('.commentApp-textarea', box).focus(function() {
                form.addClass('expanded');
                if (this.value == def) this.value = '';

                $(this).css({
                    color: '#222'
                });
                //}).on('blur', 'commentApp-textarea', function(event) {
            }).blur(function() {
                close_form();
            });

            //关闭评论
            $('a[name="closeform"]', box).click(function(event) {
                event.preventDefault();
                form.removeClass('expanded');
                close_form(true);
            });
            //提交评论
            box.on('click', 'a[name="addnew"]', function(event) {
                //$('a[name="addnew"]',box).click(function() {
                event.preventDefault();
                var cform = $(this).parent().parent(),
                    textarea = $('.commentApp-textarea', cform),
                    cparam = comment_param(textarea);

                if (!cparam.content) {
                    iCMS.alert("请填写内容");
                    textarea.focus();
                    return false;
                }
                $.post(iCMS.api('comment'), cparam, function(c) {
                    if (c.code) {
                        var count = parseInt($('span', $this).text());
                        $('span', $this).text(count + 1);
                        textarea.val(def).css({
                            color: '#999'
                        });
                        comment_list(0, c.forward);
                    } else {
                        iCMS.alert(c.msg);
                    }
                }, 'json');
            });
            list.on('click', 'a[name="load-more"]', function(event) {
                event.preventDefault();
                $(".load-more", list).remove();
                comment_list(page);
            });

            //回复评论
            list.on('click', 'a[name="reply_comment"]', function(event) {
                event.preventDefault();
                var item = $(this).parent().parent(),
                    reply_param = iCMS.param($(this)),
                    item_form = $('.commentApp-form', item);

                if (item_form.length > 0) {
                    item_form.remove();
                    return false;
                }
                item_form = form.clone();
                item_form.addClass('expanded').removeClass('commentApp-box-ft');
                $(this).parent().after(item_form);


                $('.commentApp-textarea', item_form).data('reply_param', reply_param)
                    .val("").focus().css({
                        color: '#222'
                    });
                $('a[name="closeform"]', item_form).click(function(event) {
                    event.preventDefault();
                    item_form.remove();
                });
            });
            //赞评论
            list.on('click', 'a[name="like_comment"]', function(event) {
                event.preventDefault();
                var $this = $(this),
                    like_param = iCMS.param($this);
                like_param.do = 'like';
                $.get(iCMS.api('comment'), like_param, function(c) {
                    //                        console.log(c);
                    if (c.code) {
                        var p = $this.parent(),
                            like_num = $('.like-num em', p).text();
                        if (like_num == "") {
                            $this.parent().append('<span class="like-num" data-tip="iCMS:s:1 人觉得这个很赞"><em>1</em> <span>赞</span></span>')
                        } else {
                            like_num = parseInt(like_num) + 1;
                            $('.like-num em', p).text(like_num);
                        }
                    } else {
                        iCMS.alert(c.msg);
                    }
                }, 'json');
            });
            //举报评论
            list.on('click', 'a[name="report_comment"]', function(event) {
                event.preventDefault();
                var $this = $(this),
                    report_box = document.getElementById("iCMS-comment-report"),
                    _REPORT_MODAL = $(this).modal({
                        title: '为什么举报这个评论?',
                        width: "460px",
                        html: report_box,
                        scroll: true
                    });
                $("li", report_box).click(function(event) {
                    $("li", report_box).removeClass('checked');
                    $(this).addClass('checked');
                });
                $('[name="cancel"]', report_box).click(function(event) {
                    _REPORT_MODAL.destroy();
                });
                $('[name="ok"]', report_box).click(function(event) {
                    var report_param = iCMS.param($this);
                    report_param['reason'] = $("[name='reason']:checked", report_box).val();
                    if (report_param['reason'] == undefined) {
                        iCMS.alert("请选择举报的原因");
                        return false;
                    }
                    if (report_param['reason'] == "0") {
                        report_param['content'] = $("[name='content']", report_box).val();
                        if (report_param['content'] == "") {
                            iCMS.alert("请填写举报的原因");
                            return false;
                        }
                    }
                    report_param.action = 'report';

                    $.post(iCMS.api('comment'), report_param, function(c) {
                        $("[name='content']", report_box).val('');
                        iCMS.alert(c.msg, c.code);
                    }, 'json');
                    return false;
                });

                //iCMS.dialog(report_box,opts);
            });

            function comment_param(textarea) {
                var reply_param = textarea.data('reply_param'),
                    content = textarea.val(),
                    vars = {
                        'action': 'add',
                        'content': (content == def ? '' : content)
                    };
                return $.extend(vars, param, reply_param);
            }

            function close_form(d, p) {
                var textarea = $('.commentApp-textarea', (p || box));
                if (textarea.val() == "" || d) {
                    textarea.val(def).css({
                        color: '#999'
                    });
                }
            }

            function comment_list(pageNum, id) {
                pageNum = pageNum || 1;
                $.get(iCMS.api('comment'), {
                        'do': 'json',
                        'iid': param['iid'],
                        'id': id,
                        page: pageNum
                    },
                    function(json) {
                        loging.remove();
                        if (!json)
                            return false;

                        var totalpage = 0;
                        form.addClass('commentApp-box-ft');
                        $.each(json, function(i, c) {
                            //console.log(c.reply);
                            var item = '<div class="commentApp-item" data-id="' + c.id + '">' +
                                '<a title="' + c.user.name + '" data-tip="iCMS:ucard:' + c.user.uid + '" class="app-item-link-avatar" href="' + c.user.url + '">' +
                                '<img src="' + c.user.avatar + '" class="app-item-img-avatar">' +
                                '</a>' +
                                '<div class="commentApp-content-wrap">' +
                                '<div class="commentApp-hd">' +
                                '<a data-tip="iCMS:ucard:' + c.user.uid + '" href="' + c.user.url + '" class="zg-link">' + c.user.name + '</a>';
                            if (c.suid == c.uid) {
                                item += '<span class="desc">（作者）</span>';
                            }
                            if (c.reply) {
                                item += '<span class="desc">回复 </span>' +
                                    '<a data-tip="iCMS:ucard:' + c.reply.uid + '" href="' + c.reply.url + '" class="zg-link">' + c.reply.name + '</a>';
                            }
                            item += '</div>' +
                                '<div class="commentApp-content">' + c.content + '</div>' +
                                '<div class="commentApp-ft">' +
                                '<span class="date">' + c.addtime + '</span>' +
                                '<a href="javascript:;" class="reply commentApp-op-link" name="reply_comment" data-param=\'{"uid":"' + c.user.uid + '","name":"' + c.user.name + '"}\'>' +
                                '<i class="iCMS_icon iCMS_icon_comment_reply"></i>回复</a>' +
                                '<a href="javascript:;" class="like commentApp-op-link" name="like_comment" data-param=\'{"id":"' + c.id + '"}\'>' +
                                '<i class="iCMS_icon iCMS_icon_comment_like"></i>赞</a>';
                            if (c.up > 1) {
                                item += '<span class="like-num" data-tip="iCMS:s:' + c.up + ' 人觉得这个很赞">' +
                                    '<em>' + c.up + '</em> <span>赞</span></span>';
                            }
                            item += '<a href="javascript:;" name="report_comment" data-param=\'{"id":"' + c.id + '","uid":"' + c.user.uid + '"}\' class="report commentApp-op-link needsfocus">' +
                                '<i class="iCMS_icon iCMS_icon_report"></i>举报</a>' +
                                '</div>' +
                                '</div>' +
                                '</div>';
                            list.append(item);
                            //console.log(c.page.perpage,i,i+1);
                            if (json.length == (i + 1)) {
                                totalpage = c.page.total;
                                console.log(totalpage, page);
                                if (totalpage > 1) {
                                    page = pageNum + 1;
                                    if (page > totalpage) {
                                        page = totalpage;
                                    } else {
                                        list.append('<a href="javascript:;" class="load-more" name="load-more"><span class="text">显示全部评论</a>');
                                    }
                                }
                            }
                        });

                    }, 'json');
            }
            //------------
        },
        Init: function() {
            $(document).on("click", '.iCMS-article-do', function(event) {
                event.preventDefault();
                if (!iCMS.user_status) {
                    iCMS.LoginBox();
                    return false;
                }
                var param = iCMS.param($(this));
                if (param.do =='comment') {
                    iCMS.article.comment_box(this);
                } else if (param.do =='good') {
                    iCMS.article.good(this);
                }
                return false;
            });
        },
    };
})(jQuery);
