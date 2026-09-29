$(function(){
    var hide_menu = false;
    var mouse_event = false;
    var oldX = oldY = 0;

    $(document).mousemove(function(e) {
        if(oldX == 0) {
            oldX = e.pageX;
            oldY = e.pageY;
        }

        if(oldX != e.pageX || oldY != e.pageY) {
            mouse_event = true;
        }
    });

    // 주메뉴
    var $main_me = $(".main_me > a");
    
    // 토글 메뉴 클릭 이벤트
    $(".toggle_menu").click(function(e) {
        e.preventDefault();
        var $parent = $(this).parent();
        var $submenu = $parent.find('.sub_me_list');
        
        if($parent.hasClass('main_me_on')) {
            // 닫기
            $parent.removeClass('main_me_on main_me_over');
            submenu_hide();
        } else {
            // 열기
            $(".main_me").removeClass("main_me_over main_me_over2 main_me_on");
            $parent.addClass("main_me_over main_me_on");
            $("#hd").addClass("hd_zindex");
            menu_rearrange($parent);
            hide_menu = false;
        }
    });
    
    // 일반 링크는 기존 동작 유지
    $main_me.not('.toggle_menu').mouseover(function() {
        if(mouse_event) {
            $("#hd").addClass("hd_zindex");
            $(".main_me").removeClass("main_me_over main_me_over2 main_me_on");
            $(this).parent().addClass("main_me_over main_me_on");
            menu_rearrange($(this).parent());
            hide_menu = false;
        }
    });

    $main_me.not('.toggle_menu').mouseout(function() {
        hide_menu = true;
    });

    $(".sub_me").mouseover(function() {
        hide_menu = false;
    });

    $(".sub_me").mouseout(function() {
        hide_menu = true;
    });

    $main_me.not('.toggle_menu').focusin(function() {
        $("#hd").addClass("hd_zindex");
        $(".main_me").removeClass("main_me_over main_me_over2 main_me_on");
        $(this).parent().addClass("main_me_over main_me_on");
        menu_rearrange($(this).parent());
        hide_menu = false;
    });

    $main_me.not('.toggle_menu').focusout(function() {
        hide_menu = true;
    });

    $(".sub_me_link").focusin(function() {
        $(".main_me").removeClass("main_me_over main_me_over2 main_me_on");
        var $main_li = $(this).closest(".main_me").addClass("main_me_over main_me_on");
        menu_rearrange($(this).closest(".main_me"));
        hide_menu = false;
    });

    $(".sub_me_link").focusout(function() {
        hide_menu = true;
    });

    $('#main_menu_list>li').bind('mouseleave',function(){
        if(!$(this).hasClass('has_submenu') || !$(this).hasClass('main_me_on')) {
            submenu_hide();
        }
    });

    $(document).bind('click focusin',function(e){
        if(hide_menu && !$(e.target).closest('.main_me').length) {
            submenu_hide();
        }
    });
});

function submenu_hide() {
    $("#hd").removeClass("hd_zindex");
    $(".main_me").removeClass("main_me_over main_me_over2 main_me_on");
    $(".toggle_icon").text('▼');
}

function menu_rearrange(el)
{
    var width = $("#main_menu_list").width();
    var left = w1 = w2 = 0;
    var idx = $(".main_me").index(el);
    var max_menu_count = 0;
    var $main_me_li;

    for(i=0; i<=idx; i++) {
        $main_me_li = $(".main_me:eq("+i+")");
        w1 = $main_me_li.outerWidth();

        if($main_me_li.find(".sub_me_list").length)
            w2 = $main_me_li.find(".sub_me > a").outerWidth(true);
        else
            w2 = w1;

        if((left + w2) > width) {
            if(max_menu_count == 0)
                max_menu_count = i + 1;
        }

        if(max_menu_count > 0 && (idx + 1) % max_menu_count == 0) {
            el.removeClass("main_me_over").addClass("main_me_over2");
            left = 0;
        } else {
            left += w1;
        }
    }
}
