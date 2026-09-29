<?php
if (!defined('_GNUBOARD_')) exit;

// 필요한 전역 변수
global $is_member, $member, $login_action_url, $urlencode;
?>

<?php if ($is_member) { ?>
    <div class="my_member_info">
        <div class="member-display">
            <!-- 회원 아이콘 영역 -->
            <div class="member-image-area">
                <?php if (!empty($member['mb_signature'])): ?>
                    <img src="<?php echo htmlspecialchars($member['mb_signature']); ?>" alt="<?php echo htmlspecialchars($member['mb_name']); ?>" class="member-portrait">
                <?php else: ?>
                    <div class="portrait-placeholder">
                        <i class="fa fa-user"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 회원 정보 영역 -->
            <div class="member-info-area">
                <div class="member_name">
                    <strong><?php echo htmlspecialchars($member['mb_name']); ?></strong>
                </div>
                <div class="member_id">
                    <?php echo htmlspecialchars($member['mb_id']); ?>
                </div>
            </div>

            <!-- 우측: 레벨 + 마이페이지 -->
            <div class="member-side">
                <div class="member_level">
                    <i class="fa fa-star"></i> Lv.<?php echo $member['mb_level']; ?>
                </div>
                <div class="member-actions">
                    <a href="<?php echo G5_URL; ?>/mypage.php" class="member-action-btn" title="마이페이지">
                        <i class="fa fa-user-cog"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- 알림 패널 (단일 탭 레이어: 멘션/공지/관심글) -->
        <div class="member-notifications">
            <?php include(G5_SKIN_PATH.'/main/member/notification_list.php'); ?>
        </div>
    </div>
<?php } else { ?>
    <div class="login_form">
        <form name="flogin" action="<?php echo $login_action_url ?>" onsubmit="return flogin_submit(this);" method="post">
        <input type="hidden" name="url" value="<?php echo $urlencode ?>">
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

<script>
// 로그인 폼 검증
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
