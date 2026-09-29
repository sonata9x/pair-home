<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 로그인 시작 { -->
<div class="intro-container">
    <div class="site-logo">
        <?php 
        $logo_url = $design['logo_image_url'];
        $use_logo = $design['use_logo'] == '1';
        ?>
        
        <a href="<?=$main_link?>" class="logo-link">
            <?php if ($use_logo): ?>
                <img src="<?php echo htmlspecialchars($design['logo_image_url'] ?? G5_IMG_URL.'/logo.png'); ?>">
            <?php else: ?>
                <span class="logo-text"><?php echo $config['cf_title']; ?></span>
            <?php endif; ?>
        </a>
    </div>
        
    <div class="intro-content">
        <div class="login_form">
            <form name="flogin" action="<?php echo $login_action_url ?>" onsubmit="return flogin_submit(this);" method="post">
            <input type="hidden" name="url" value="<?php echo $login_url ?>">
            
            <fieldset>
                <div class="login_input">
                    <input type="text" name="mb_id" maxlength="20" placeholder="아이디" required>
                    <input type="password" name="mb_password" maxlength="20" placeholder="비밀번호" required>
                </div>

                <div class="login_options">
                    <input type="checkbox" name="auto_login" value="1" id="login_auto_login" class="selec_chk">
                    <label for="login_auto_login">자동로그인</label>
                </div>

                <button type="submit" class="btn_login">LOGIN</button>
            </fieldset>

            <div class="login_links">
                <a href="<?php echo G5_BBS_URL ?>/register.php">회원가입</a>
                <a href="<?php echo G5_BBS_URL ?>/password_lost.php">비밀번호 찾기</a>
            </div>
            </form>
        </div>
    </div>
</div>



<script>
jQuery(function($){
    $("#login_auto_login").click(function(){
        if (this.checked) {
            this.checked = confirm("자동로그인을 사용하시면 다음부터 회원아이디와 비밀번호를 입력하실 필요가 없습니다.\n\n공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?");
        }
    });
});

function flogin_submit(f)
{
    if( $( document.body ).triggerHandler( 'login_sumit', [f, 'flogin'] ) !== false ){
        return true;
    }
    return false;
}
</script>
<!-- } 로그인 끝 -->

