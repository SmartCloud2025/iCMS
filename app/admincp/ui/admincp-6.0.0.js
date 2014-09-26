// if ($.browser.msie && ($.browser.version == "6.0" || $.browser.version == "7.0") && !$.support.style) {
//     alert("系统检测到您使用的是IE内核的浏览器!!\n\nIE内核的浏览器访问本页面可能会出现各种不可预料的错误!!\n\n为了您更好的使用本页面\n\n推荐使用 Chrome,FireFox 等浏览器\n\n如使用 搜狗 或者 360 浏览器的请切换成 极速模式!");
// }
$(function() {
    var _iCMS = {
        select: function(el, v) {
            var va = v.split(',');
            $.each(va, function(i,val){
              $("#" + el+" option[value='"+val+"']").attr("selected", true);
            });
            $("#"+el).trigger("chosen:updated");
        },
        checked: function(el){
            $(el).prop("checked",true).closest('.checker > span').addClass('checked');
        },
    }
    iCMS = $.extend(iCMS,_iCMS);//扩展 or 替换 iCMS属性
    iCMS.modal();
    $(':checkbox[data-type!=switch],:radio[data-type!=switch]').uniform();
    $('.ui-datepicker').datepicker({format: 'yyyy-mm-dd'});
    $('.tip').tooltip({html:true});
    $('.tip-left').tooltip({placement:'left',html:true});
    $('.tip-right').tooltip({placement:'right',html:true});
    $('.tip-top').tooltip({placement:'top',html:true});
    $('.tip-bottom').tooltip({placement:'bottom',html:true});
    $(".chosen-select").chosen({disable_search_threshold: 30});
    $(".checkAll").click(function() {
        var target = $(this).attr('data-target'), checkedStatus = $(this).prop("checked");
        //$('input:checkbox',$(target)).prop("checked",checkedStatus);
        $(".checkAll").prop("checked", checkedStatus);
        $('input:checkbox', $(target)).each(function() {
            this.checked = checkedStatus;
            if (checkedStatus == this.checked) {
                $(this).closest('.checker > span').removeClass('checked');
            }
            if (this.checked) {
                $(this).closest('.checker > span').addClass('checked');
            }
        });
    });
    $(document).on("click",'[data-toggle="insert"]',function() {
        var a = $(this), data = a.data('insert'),
        href = a.attr('href'), target = a.attr('data-target'),
        val = a.text();
        $(target).val(val);
        a.parent().parent().parent().removeClass("open");
        //console.log();
        return false;
    });
    $('[data-toggle="createpass"]').on("click",function() {
        var a = $(this),target = a.attr('data-target');
        $(target).val(iCMS.random(8));
        return false;
    });
    $(document).on("click",'[data-toggle="insertContent"]',function() {
        var a = $(this), data = a.data('insertContent'),
        href   = a.attr('href'),
        target = a.attr('data-target'),
        mode   = a.attr('data-mode'),
        val    = a.text();
        if(mode=="replace"){
            $(target).val(href);
        }else{
            $(target).insertContent(href);
        }
        return false;
    });
    $.scrollUp({
        scrollName: 'scrollUp', // Element ID
        topDistance: '40', // Distance from top before showing element (px)
        topSpeed: 300, // Speed back to top (ms)
        animation: 'fade', // Fade, slide, none
        animationInSpeed: 200, // Animation in speed (ms)
        animationOutSpeed: 200, // Animation out speed (ms)
        scrollText: '', // Text for element
        activeOverlay: false // Set CSS color to display scrollUp active point, e.g '#00FFFF'
    });
    $('.submenu > a').click(function(e) {
        e.preventDefault();
        var submenu = $(this).siblings('ul');
        var li = $(this).parents('li');
        var submenus = $('#sidebar li.submenu ul');
        var submenus_parents = $('#sidebar li.submenu');
        if (li.hasClass('open')) {
            if (($(window).width() > 768) || ($(window).width() < 479)) {
                submenu.slideUp();
            } else {
                submenu.fadeOut(250);
            }
            li.removeClass('open');
        } else {
            if (($(window).width() > 768) || ($(window).width() < 479)) {
                submenus.slideUp();
                submenu.slideDown();
            } else {
                submenus.fadeOut(250);
                submenu.fadeIn(250);
            }
            submenus_parents.removeClass('open');
            li.addClass('open');
        }
    });

    $('#sidebar > a').click(function(e) {
	    var ul = $('#sidebar > ul');
        e.preventDefault();
        var sidebar = $('#sidebar');
        if (sidebar.hasClass('open')) {
            sidebar.removeClass('open');
            ul.slideUp(250);
        } else {
            sidebar.addClass('open');
            ul.slideDown(250);
        }
    });
    $('#sidebar > #mini').click(function() {
        var b = $('body');
        if (b.hasClass('sidebar-mini')) {
        	iCMS.setcookie('ACP_sidebar_mini',0);
            b.removeClass('sidebar-mini');
        } else {
        	iCMS.setcookie('ACP_sidebar_mini',1);
            b.addClass('sidebar-mini');
        }
        return false;
    });
})
function log(a) {
    try {
        console.log(a);
    } catch (e) {
    // not support console method (ex: IE)
    }
}
function modal_icms(el,a){
	if(!el) return;
	if(!a.checked) return;

	var e = $('#'+el)||$('.'+el);
    var val = a.value.replace(iCMS.config.DEFTPL+'/', "{iTPL}/");
	e.val(val);
    return 'off';
}

//批量操作
(function($) {
    $.fn.extend({
        batch: function(opt) {
            var im   = $(this),_this = this,
                action   = $('<input type="hidden" name="batch">'),
                content  = $('<div class="hide"></div>').appendTo(im),
                defaults = {
                    move: function(){
                        var select  = $("#cid").clone().show()
                            //.removeClass("chosen-select")
                            .attr("id",iCMS.random(3));
                        $("option:first",select).remove();
                        return select;
                    },
                    prop: function(){
                        var select  = $("#pid").clone().show()
                            //.removeClass("chosen-select")
                            .attr("name",'pid[]')
                            .attr("multiple",'multiple')
                            .attr("id",iCMS.random(3));
                        $("option:first",select).remove();
                        return select;
                    },
                },
                options = $.extend(defaults, opt);

            im.dialog = function(title,obj,h){
                window.batch_dialog = $.dialog({id: 'iCMS-batch',width:"320px",lock: true,
                    title:title,content:obj,
                    okValue: '确定',ok: function () {
                        content.html($(obj).clone(true));
                        im.submit();
                        return true;
                    },
                    cancelValue: "取消",cancel: function(){
                        action.val(0);
                        content.empty();
                        return true;
                    }
                });
            }
            $('[data-toggle="batch"]').click(function(){
                if($("tbody input:checkbox:checked",im).length==0){
                    alert("请选择要操作项目!");
                    return true;
                }
                var a = $(this),b = this,
                    act   = a.attr('data-action'),
                    ab    = $('#'+act+'Batch'),
                    ret   = document.getElementById(act+'Batch'),
                    title = a.text();
                    action.val(act).appendTo(im);
                    if(ret==null){
                        if(typeof options[act]=="undefined"){
                            ret = '<div class="iPHP-msg"><span class="label label-warning"><i class="icon-warning-sign icon-white"></i> 确定要'+$.trim(title)+"?</span></div>";
                        }else{
                            var ret = document.createElement("div");
                            $(ret).html(options[act]());
                        }
                    }
                    im.dialog(title,ret);
            });
            return im;
        }
    })
})(jQuery);
//插入内容
(function($) {
    $.fn.extend({
        insertContent: function(myValue, t) {
            event.preventDefault();
            var $t = $(this)[0];

            if (document.selection) { //ie
                this.focus();
                var sel = document.selection.createRange();
                sel.text = myValue;
                this.focus();
                sel.moveStart('character', -l);
                var wee = sel.text.length;
                if (arguments.length == 2) {
                    var l = $t.value.length;
                    sel.moveEnd("character", wee + t);
                    t <= 0 ? sel.moveStart("character", wee - 2 * t - myValue.length) : sel.moveStart("character", wee - t - myValue.length);
                    sel.select();
                }
            } else if ($t.selectionStart || $t.selectionStart == '0') {
                var startPos = $t.selectionStart;
                var endPos = $t.selectionEnd;
                var scrollTop = $t.scrollTop;
                $t.value = $t.value.substring(0, startPos) + myValue + $t.value.substring(endPos, $t.value.length);
                this.focus();
                $t.selectionStart = startPos + myValue.length;
                $t.selectionEnd = startPos + myValue.length;
                $t.scrollTop = scrollTop;
                if (arguments.length == 2) {
                    $t.setSelectionRange(startPos - t, $t.selectionEnd + t);
                    this.focus();
                }
            }
            else {
                this.value += myValue;
                this.focus();
            }
        }
    })
})(jQuery);
