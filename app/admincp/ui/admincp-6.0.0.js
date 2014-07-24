$(function() {
    iCMS.modal();
    $(':checkbox[data-type!=switch],:radio[data-type!=switch]').uniform();
    $('.ui-datepicker').datepicker({format: 'yyyy-mm-dd'});
    $('.tip').tooltip({html: 'html'});
    $('.tip-left').tooltip({placement: 'left',html: 'html'});
    $('.tip-right').tooltip({placement: 'right',html: 'html'});
    $('.tip-top').tooltip({placement: 'top',html: 'html'});
    $('.tip-bottom').tooltip({placement: 'bottom',html: 'html'});
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
        	iCMS.setcookie('iCMS_APC_sidebar_mini',0);
            b.removeClass('sidebar-mini');
        } else {
        	iCMS.setcookie('iCMS_APC_sidebar_mini',1);
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

	var e=$('#'+el)||$('.'+el);
    var val = a.value.replace(iCMS.DEFTPL+'/', "{iTPL}/");
	e.val(val);
    return 'off';
}