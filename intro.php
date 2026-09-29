<?php
define('_INTRO_', true);
include_once('./_common.php');

// 로그인한 회원은 메인으로 이동
if ($is_member) {
    $main_link = get_main_link();

    // iframe 내부에서는 PostMessage로 메인 이동
    if (isset($_GET['iframe_skip'])) {
        echo "<script>
        if (window.parent && window.parent !== window) {
            window.parent.postMessage({type: 'navigate', url: '".$main_link."'}, '*');
        } else {
            location.href = '".$main_link."';
        }
        </script>";
        exit;
    }
}

include_once(G5_PATH.'/head.sub.php');
?>

<div class="intro-container">
    <div class="site-logo">
        <?php
        $logo_url = $design['logo_image_url'];
        $use_logo = $design['use_logo'] == '1';
        $main_link = get_main_link();
        ?>

        <a href="<?=$main_link?>" class="logo-link">
            <?php if ($use_logo): ?>
                <img src="<?php echo htmlspecialchars($design['logo_image_url'] ?? G5_IMG_URL.'/logo.png'); ?>" alt="<?php echo $config['cf_title']; ?>">
            <?php else: ?>
                <span class="logo-text"><?php echo $config['cf_title']; ?></span>
            <?php endif; ?>
        </a>
    </div>

    <div class="intro-content">
        <?php if ($config['cf_visit'] == '1') { ?>
        <!-- 비회원: 로그인 폼 (비공개 설정일 때만 표시) -->
        <div class="login_form">
            <form name="flogin" action="<?php echo $login_action_url ?>" onsubmit="return flogin_submit(this);" method="post">
            <fieldset>
                <div class="login_input">
                    <input type="text" name="mb_id" maxlength="20" placeholder="아이디" required>
                    <input type="password" name="mb_password" maxlength="20" placeholder="비밀번호" required>
                </div>
                <button type="submit" class="btn_login">LOGIN</button>
            </fieldset>
            </form>
            <div class="login_links">
                <a href="<?php echo G5_BBS_URL ?>/register.php">회원가입</a>
                <a href="<?php echo G5_BBS_URL ?>/password_lost.php">비밀번호 찾기</a>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<script>
function flogin_submit(f) {
    if (!f.mb_id.value) {
        alert('아이디를 입력해 주세요.');
        f.mb_id.focus();
        return false;
    }
    
    if (!f.mb_password.value) {
        alert('비밀번호를 입력해 주세요.');
        f.mb_password.focus();
        return false;
    }
    
    return true;
}
</script>

<script>
$(document).ready(function(){
    // 메뉴 위젯 숨기기
    parent.$('.tf-menu-widget').hide();
    parent.$('.tf-menu-mobile-btn').hide();
});

// 페이지 벗어날 때 다시 보이기
$(window).on("beforeunload", function(){
    parent.$('.tf-menu-widget').show();
    parent.$('.tf-menu-mobile-btn').show();
});
</script>

<?php include_once(G5_PATH.'/tail.sub.php'); ?>