<?php
$sub_menu = "300100";
require_once './_common.php';

require_once './admin.head.php';
?>

<form name="fconfigform" id="fconfigform" method="post" action="./member_config_update.php">
    <!-- 토큰 제거 -->
    
    <!-- 회원설정 탭 -->
    <div id="tab_member" class="config_content">
        <h2 class="h2_frm">회원가입 설정</h2>
        <div class="local_desc02 local_desc">
            <p>캐릭터 등록자(회원)의 가입 설정을 관리합니다.</p>
        </div>

        <div class="tbl_frm01 tbl_wrap">
            <table>
                <caption>회원가입 설정</caption>
                <colgroup>
                    <col class="grid_4">
                    <col>
                    <col class="grid_4">
                    <col>
                </colgroup>
                <tbody>
                    <!-- <tr>
                        <th scope="row"><label for="cf_use_email_certify">메일인증 사용</label></th>
                        <td>
                            <?php echo help('메일에 배달된 인증 주소를 클릭하여야 회원으로 인정합니다.'); ?>
                            <input type="checkbox" name="cf_use_email_certify" value="1" id="cf_use_email_certify" <?php echo $config['cf_use_email_certify'] ? 'checked' : ''; ?> onchange="updateEmailUse()"> 사용
                            <input type="hidden" name="cf_email_use" id="cf_email_use" value="<?php echo $config['cf_use_email_certify'] ? '1' : '0'; ?>">
                        </td>
                    </tr> -->
                    <tr>
                        <th scope="row"><label for="cf_register_level">회원가입시 권한</label></th>
                        <td><?php echo get_member_level_select('cf_register_level', 1, 9, $config['cf_register_level']) ?></td>
                        <!-- <th scope="row"><label for="cf_register_point">회원가입시 포인트</label></th>
                        <td><input type="text" name="cf_register_point" value="<?php echo (int) $config['cf_register_point'] ?>" id="cf_register_point" class="frm_input" size="5"> 점</td> -->
                        <th scope="row"><label for="cf_leave_day">회원탈퇴후 삭제일</label></th>
                        <td colspan="3"><input type="text" name="cf_leave_day" value="<?php echo (int) $config['cf_leave_day'] ?>" id="cf_leave_day" class="frm_input" size="2"> 일 후 자동 삭제</td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="cf_prohibit_id">아이디,닉네임 금지단어</label></th>
                        <td>
                            <?php echo help('회원아이디, 닉네임으로 사용할 수 없는 단어를 정합니다. 쉼표 (,) 로 구분') ?>
                            <textarea name="cf_prohibit_id" id="cf_prohibit_id" rows="5"><?php echo get_sanitize_input($config['cf_prohibit_id']); ?></textarea>
                        </td>
                        <th scope="row"><label for="cf_prohibit_email">입력 금지 메일</label></th>
                        <td>
                            <?php echo help('입력 받지 않을 도메인을 지정합니다. 엔터로 구분 ex) hotmail.com') ?>
                            <textarea name="cf_prohibit_email" id="cf_prohibit_email" rows="5"><?php echo get_sanitize_input($config['cf_prohibit_email']); ?></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="btn_fixed_top btn_confirm">
        <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
    </div>
</form>

<script>
function updateEmailUse() {
    var certifyCheckbox = document.getElementById('cf_use_email_certify');
    var emailUseField = document.getElementById('cf_email_use');
    
    // 메일인증 사용이 체크되면 메일 발송도 자동으로 1로 설정
    if (certifyCheckbox.checked) {
        emailUseField.value = '1';
    } else {
        emailUseField.value = '0';
    }
}
</script>

<?php
require_once './admin.tail.php';
?>