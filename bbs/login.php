<?php
include_once('./_common.php');

$g5['title'] = '로그인';
include_once('./_head.sub.php');

$od_id = isset($_POST['od_id']) ? safe_replace_regex($_POST['od_id'], 'od_id') : '';

// url 체크
check_url_host($url);

// 이미 로그인 중이라면
if ($is_member) {
    if ($url)
        goto_url_top($url);
    else {
        $main_link = get_main_link();
        goto_url_top($main_link);
    }
}

$login_url        = login_url($url);
$login_action_url = G5_HTTPS_BBS_URL."/login_check.php";
$main_link        = get_main_link(); // 메인 링크 정의
?>

<!-- 로그인 시작 { -->
<div class="intro-container">
    <div class="site-logo">
        <?php
        $logo_url = $design['logo_image_url'];
        $use_logo = $design['use_logo'] == '1';
        ?>

        <a href="<?=$main_link?>" class="logo-link">
            <?php if ($use_logo && !empty($design['logo_image_url'])): ?>
                <img src="<?php echo htmlspecialchars($design['logo_image_url']); ?>">
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
                <div class="login_row">
                    <div class="login_input">
                        <input type="text" name="mb_id" maxlength="20" placeholder="아이디" required>
                        <input type="password" name="mb_password" maxlength="20" placeholder="비밀번호" required>
                    </div>
                    <button type="submit" class="btn_login">LOGIN</button>
                </div>

                <div class="login_bottom_row">
                    <a href="<?php echo G5_BBS_URL ?>/register.php" class="login_link">회원가입</a>
                    <div class="login_options">
                        <input type="checkbox" name="auto_login" value="1" id="login_auto_login" class="selec_chk">
                        <label for="login_auto_login">자동로그인</label>
                    </div>
                </div>
            </fieldset>
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


<?php 
run_event('member_login_tail', $login_url, $login_action_url, $member_skin_path, $url);

include_once('./_tail.sub.php');
?>