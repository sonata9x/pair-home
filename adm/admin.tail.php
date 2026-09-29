<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

// 그누보드5.4.5.5 버전과 영카트5.4.5.5.1 버전이 통합됨에 따라 그누보드 버전만 표시
// $print_version = defined('G5_YOUNGCART_VER') ? 'YoungCart Version '.G5_YOUNGCART_VER : 'Version '.G5_GNUBOARD_VER;
$print_version = ($is_admin == 'super') ? 'Version ' . G5_GNUBOARD_VER : '';
?>

</div>
<footer id="ft">
    <p>
        Copyright &copy; <?php echo $_SERVER['HTTP_HOST']; ?>. All rights reserved. <?php echo $print_version; ?><br>
        <button type="button" class="scroll_top"><span class="top_img"></span><span class="top_txt">TOP</span></button>
    </p>
</footer>
</div>

</div>

<script>
    $(".scroll_top").click(function() {
        $("body,html").animate({
            scrollTop: 0
        }, 400);
    })
</script>

<!-- <p>실행시간 : <?php echo get_microtime() - $begin_time; ?> -->

<script src="<?php echo G5_ADMIN_URL ?>/admin.js?ver=<?php echo G5_JS_VER; ?>"></script>
<script>
    $(function() {

        var admin_head_height = $("#hd_top").height() + $("#container_title").height() + 5;
        var hide_menu = false;
        var mouse_event = false;
        var oldX = oldY = 0;

        $(document).mousemove(function(e) {
            if (oldX == 0) {
                oldX = e.pageX;
                oldY = e.pageY;
            }

            if (oldX != e.pageX || oldY != e.pageY) {
                mouse_event = true;
            }
        });

        // 주메뉴
        var $ra0 = $(".ra0_1dli > a");
        $ra0.mouseover(function() {
            if (mouse_event) {
                $(".ra0_1dli").removeClass("ra0_1dli_over ra0_1dli_over2 ra0_1dli_on");
                $(this).parent().addClass("ra0_1dli_over ra0_1dli_on");
                menu_rearrange($(this).parent());
                hide_menu = false;
            }
        });

        $ra0.mouseout(function() {
            hide_menu = true;
        });

        $(".ra0_2dli").mouseover(function() {
            hide_menu = false;
        });

        $(".ra0_2dli").mouseout(function() {
            hide_menu = true;
        });

        $ra0.focusin(function() {
            $(".ra0_1dli").removeClass("ra0_1dli_over ra0_1dli_over2 ra0_1dli_on");
            $(this).parent().addClass("ra0_1dli_over ra0_1dli_on");
            menu_rearrange($(this).parent());
            hide_menu = false;
        });

        $ra0.focusout(function() {
            hide_menu = true;
        });

        $(".ra0_2da").focusin(function() {
            $(".ra0_1dli").removeClass("ra0_1dli_over ra0_1dli_over2 ra0_1dli_on");
            var $ra0_li = $(this).closest(".ra0_1dli").addClass("ra0_1dli_over ra0_1dli_on");
            menu_rearrange($(this).closest(".ra0_1dli"));
            hide_menu = false;
        });

        $(".ra0_2da").focusout(function() {
            hide_menu = true;
        });

        $('#ra0_1dul>li').bind('mouseleave', function() {
            submenu_hide();
        });

        $(document).bind('click focusin', function() {
            if (hide_menu) {
                submenu_hide();
            }
        });

        // 폰트 리사이즈 쿠키있으면 실행
        var font_resize_act = get_cookie("ck_font_resize_act");
        if (font_resize_act != "") {
            font_resize("container", font_resize_act);
        }
    });

    function submenu_hide() {
        $(".ra0_1dli").removeClass("ra0_1dli_over ra0_1dli_over2 ra0_1dli_on");
    }

    function menu_rearrange(el) {
        var width = $("#ra0_1dul").width();
        var left = w1 = w2 = 0;
        var idx = $(".ra0_1dli").index(el);

        for (i = 0; i <= idx; i++) {
            w1 = $(".ra0_1dli:eq(" + i + ")").outerWidth();
            w2 = $(".ra0_2dli > a:eq(" + i + ")").outerWidth(true);

            if ((left + w2) > width) {
                el.removeClass("ra0_1dli_over").addClass("ra0_1dli_over2");
            }

            left += w1;
        }
    }
</script>

<?php
require_once G5_PATH . '/tail.sub.php';
