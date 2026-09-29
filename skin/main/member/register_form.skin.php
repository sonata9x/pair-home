<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 스킨 CSS 로드
echo '<link rel="stylesheet" href="'.$member_skin_url.'/style.css">';
echo '<script src="'.G5_JS_URL.'/jquery.register_form.js"></script>';
?>

<!-- 회원가입/정보수정 폼 시작 { -->
<div class="register">
    <div id="register_form">
        
        <div class="register_form_inner">
            <form id="fregisterform" name="fregisterform" action="<?php echo $register_action_url ?>" onsubmit="return fregisterform_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="w" value="<?php echo $w ?>">
                <input type="hidden" name="agree" value="<?php echo $agree ?>">
                <input type="hidden" name="agree2" value="<?php echo $agree2 ?>">
                <input type="hidden" name="token" value="<?php echo get_session('ss_token'); ?>">
                
                <ul class="form_01">
                    <!-- 아이디 -->
                    <li>
                        <label for="reg_mb_id">아이디 (필수)</label>
                        <input type="text" name="mb_id" value="<?php echo $member['mb_id'] ?>" id="reg_mb_id" class="frm_input" 
                               <?php echo $required ?> <?php echo $readonly ?> 
                               minlength="3" maxlength="20" placeholder="영문자, 숫자, _ 만 입력 가능 (3자 이상)">
                        <span class="frm_info">영문자, 숫자, _ 만 입력 가능. 최소 3자 이상 입력하세요.</span>
                        <span id="msg_mb_id"></span>
                    </li>

                    <!-- 비밀번호 -->
                    <li>
                        <label for="reg_mb_password">비밀번호 (필수)</label>
                        <input type="password" name="mb_password" id="reg_mb_password" class="frm_input"
                               <?php echo $required ?> minlength="3" maxlength="20" placeholder="비밀번호">
                    </li>

                    <!-- 비밀번호 확인 -->
                    <li>
                        <label for="reg_mb_password_re">비밀번호 확인 (필수)</label>
                        <input type="password" name="mb_password_re" id="reg_mb_password_re" class="frm_input"
                               <?php echo $required ?> minlength="3" maxlength="20" placeholder="비밀번호 확인">
                    </li>

                    <!-- 이름 -->
                    <li>
                        <label for="reg_mb_name">이름 (필수)</label>
                        <input type="text" id="reg_mb_name" name="mb_name" class="frm_input"
                               value="<?php echo get_text($member['mb_name']) ?>" 
                               <?php echo $required ?> placeholder="이름">
                    </li>

                    <!-- 이메일 -->
                    <li>
                        <label for="reg_mb_email">E-mail (필수)</label>
                        <input type="email" name="mb_email" class="frm_input"
                               value="<?php echo isset($member['mb_email'])?$member['mb_email']:''; ?>"
                               id="reg_mb_email" required maxlength="100" placeholder="이메일 주소">
                        <input type="hidden" name="old_email" value="<?php echo $member['mb_email'] ?>">
                        <?php if ($config['cf_use_email_certify']) { ?>
                        <span class="frm_info">
                            <?php if ($w=='') { echo "이메일로 발송된 내용을 확인한 후 인증하셔야 회원가입이 완료됩니다."; } ?>
                            <?php if ($w=='u') { echo "이메일 주소를 변경하시면 다시 인증하셔야 합니다."; } ?>
                        </span>
                        <?php } ?>
                    </li>

                    <!-- 홈페이지 -->
                    <li>
                        <label for="reg_mb_homepage">홈페이지</label>
                        <input type="text" name="mb_homepage" class="frm_input"
                               value="<?php echo isset($member['mb_homepage']) ? get_text($member['mb_homepage']) : ''; ?>"
                               id="reg_mb_homepage" maxlength="255" placeholder="https://example.com">
                        <span class="frm_info">개인 홈페이지나 SNS 링크를 입력하세요. (선택사항)</span>
                    </li>

                    <!-- 프로필 이미지 URL -->
                    <li>
                        <label for="reg_mb_signature">프로필 이미지 URL</label>
                        <input type="url" name="mb_signature" class="frm_input"
                               value="<?php echo isset($member['mb_signature']) ? get_text($member['mb_signature']) : ''; ?>"
                               id="reg_mb_signature" maxlength="255" placeholder="https://example.com/image.jpg">
                        <span class="frm_info">외부 이미지 링크를 입력하세요. (선택사항)</span>
                    </li>

                    <?php if (function_exists('get_community_config')): ?>
                    <!-- 출생년도 (커뮤니티 팩) -->
                    <li>
                        <label for="reg_mb_1">출생년도</label>
                        <input type="text" name="mb_1" class="frm_input"
                               value="<?php echo isset($member['mb_1']) ? get_text($member['mb_1']) : ''; ?>"
                               id="reg_mb_1" maxlength="4" placeholder="예: 00, 2000">
                        <span class="frm_info">태어난 년도를 입력해 주세요.</span>
                    </li>
                    <?php endif; ?>
                </ul>

                <!-- 버튼 -->
                <div class="btn_confirm_reg">
                    <button type="submit" id="btn_submit" class="reg_btn_submit">
                        <?php echo $w==''?'회원가입':'정보수정'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// 폼 검증
function fregisterform_submit(f) {
    // 아이디 검사
    if (f.w.value == "") {
        var msg = reg_mb_id_check();
        if (msg) {
            alert(msg);
            f.mb_id.select();
            return false;
        }
    }

    // 비밀번호 검사
    if (f.w.value == "") {
        if (f.mb_password.value.length < 3) {
            alert("비밀번호를 3글자 이상 입력하십시오.");
            f.mb_password.focus();
            return false;
        }
    }

    if (f.mb_password.value != f.mb_password_re.value) {
        alert("비밀번호가 같지 않습니다.");
        f.mb_password_re.focus();
        return false;
    }

    // 이름 검사
    if (f.w.value == "") {
        if (f.mb_name.value.length < 1) {
            alert("이름을 입력하십시오.");
            f.mb_name.focus();
            return false;
        }
    }

    // 이메일 검사
    if ((f.w.value == "") || (f.w.value == "u" && f.mb_email.defaultValue != f.mb_email.value)) {
        var msg = reg_mb_email_check();
        if (msg) {
            alert(msg);
            f.mb_email.select();
            return false;
        }
    }

    document.getElementById("btn_submit").disabled = "disabled";
    return true;
}
</script>

<!-- } 회원정보 입력/수정 끝 -->
