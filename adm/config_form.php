<?php
$sub_menu = "100100";
require_once './_common.php';

if (!$is_admin) {
    alert('관리자만 접근 가능합니다.');
}

// https://github.com/gnuboard/gnuboard5/issues/296 이슈처리
$sql = " select * from {$g5['config_table']} limit 1";
$config = sql_fetch($sql);

if (!isset($config['cf_add_script'])) {
    sql_query(
        " ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_add_script` TEXT NOT NULL AFTER `cf_admin_email_name` ",
        true
    );
}

if (!isset($config['cf_editor'])) {
    sql_query(
        " ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_editor` VARCHAR(255) NOT NULL DEFAULT '' AFTER `cf_memo_send_point` ",
        true
    );
}


if (!isset($config['cf_mobile_pages'])) {
    sql_query(
        " ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_mobile_pages` INT(11) NOT NULL DEFAULT '0' AFTER `cf_write_pages` ",
        true
    );
    sql_query(" UPDATE `{$g5['config_table']}` SET cf_mobile_pages = '5' ", true);
}


// uniqid 테이블이 없을 경우 생성
if (!sql_query(" DESC {$g5['uniqid_table']} ", false)) {
    sql_query(
        " CREATE TABLE IF NOT EXISTS `{$g5['uniqid_table']}` (
                  `uq_id` bigint(20) unsigned NOT NULL,
                  `uq_ip` varchar(255) NOT NULL,
                  PRIMARY KEY (`uq_id`)
                ) ",
        false
    );
}

if (!sql_query(" SELECT uq_ip from {$g5['uniqid_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE {$g5['uniqid_table']} ADD `uq_ip` VARCHAR(255) NOT NULL ");
}

// 임시저장 테이블이 없을 경우 생성
if (!sql_query(" DESC {$g5['autosave_table']} ", false)) {
    sql_query(
        " CREATE TABLE IF NOT EXISTS `{$g5['autosave_table']}` (
                  `as_id` int(11) NOT NULL AUTO_INCREMENT,
                  `mb_id` varchar(20) NOT NULL,
                  `as_uid` bigint(20) unsigned NOT NULL,
                  `as_subject` varchar(255) NOT NULL,
                  `as_content` text NOT NULL,
                  `as_datetime` datetime NOT NULL,
                  PRIMARY KEY (`as_id`),
                  UNIQUE KEY `as_uid` (`as_uid`),
                  KEY `mb_id` (`mb_id`)
                ) ",
        false
    );
}

if (!isset($config['cf_admin_email'])) {
    sql_query(
        " ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_admin_email` VARCHAR(255) NOT NULL AFTER `cf_admin` ",
        true
    );
}

if (!isset($config['cf_admin_db'])) {
    sql_query(
        " ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_admin_db` VARCHAR(255) NOT NULL DEFAULT '' AFTER `cf_admin_email` ",
        true
    );
}

if (!isset($config['cf_admin_email_name'])) {
    sql_query(
        " ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_admin_email_name` VARCHAR(255) NOT NULL AFTER `cf_admin_email` ",
        true
    );
}

if (!isset($config['cf_analytics'])) {
    sql_query(
        " ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_analytics` TEXT NOT NULL AFTER `cf_intercept_ip` ",
        true
    );
}

if (!isset($config['cf_add_meta'])) {
    sql_query(
        " ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_add_meta` TEXT NOT NULL AFTER `cf_analytics` ",
        true
    );
}

if (!isset($config['cf_mobile_page_rows'])) {
    sql_query(
        " ALTER TABLE `{$g5['config_table']}`
                    ADD `cf_mobile_page_rows` int(11) NOT NULL DEFAULT '0' AFTER `cf_page_rows` ",
        true
    );
}

// 읽지 않은 메모 수 칼럼 추가
if (!isset($member['mb_memo_cnt'])) {
    sql_query(
        " ALTER TABLE `{$g5['member_table']}`
                ADD `mb_memo_cnt` int(11) NOT NULL DEFAULT '0' AFTER `mb_memo_call`",
        true
    );
}

// cf_new 필드 추가
if (!isset($config['cf_new'])) {
    sql_query("ALTER TABLE `{$g5['config_table']}` ADD COLUMN `cf_new` INT DEFAULT 1 AFTER `cf_visit`", false);
}

// cf_me_plip 필드 추가
if (!isset($config['cf_me_plip'])) {
    sql_query("ALTER TABLE `{$g5['config_table']}` ADD COLUMN `cf_me_plip` INT DEFAULT 1 COMMENT '하위메뉴 접기 (0: 펼치기, 1: 접기)' AFTER `cf_new`", false);
}

// cf_description 필드 추가 (홈페이지 설명)
if (!isset($config['cf_description'])) {
    sql_query("ALTER TABLE `{$g5['config_table']}` ADD COLUMN `cf_description` TEXT COMMENT '홈페이지 설명 (OG 메타용)' AFTER `cf_title`", false);
}

// cf_image 필드 추가 (대표 이미지)
if (!isset($config['cf_image'])) {
    sql_query("ALTER TABLE `{$g5['config_table']}` ADD COLUMN `cf_image` VARCHAR(255) DEFAULT '' COMMENT '대표 이미지 URL (OG 메타용)' AFTER `cf_description`", false);
}

// cf_noindex 필드 추가 (검색 엔진 차단)
if (!isset($config['cf_noindex'])) {
    sql_query("ALTER TABLE `{$g5['config_table']}` ADD COLUMN `cf_noindex` TINYINT(1) DEFAULT 0 COMMENT '검색 엔진 차단 (noindex, nofollow)' AFTER `cf_add_meta`", false);
}

// g5_board_new 테이블 인덱스 추가 (성능 개선)
$board_new_indexes = sql_query("SHOW INDEX FROM `{$g5['board_new_table']}` WHERE Key_name = 'idx_mb_datetime'", false);
if (!sql_num_rows($board_new_indexes)) {
    sql_query("ALTER TABLE `{$g5['board_new_table']}` ADD INDEX `idx_mb_datetime` (`mb_id`, `bn_datetime`)", false);
}

$board_new_indexes2 = sql_query("SHOW INDEX FROM `{$g5['board_new_table']}` WHERE Key_name = 'idx_bo_datetime'", false);
if (!sql_num_rows($board_new_indexes2)) {
    sql_query("ALTER TABLE `{$g5['board_new_table']}` ADD INDEX `idx_bo_datetime` (`bo_table`, `bn_datetime`)", false);
}

// FAQ 관련 설정 제거 (자캐커뮤니티에 불필요)
// SMS, 본인확인 등 복잡한 기능들 제거

$g5['title'] = '환경설정';
require_once './admin.head.php';
?>
<form name="fconfig" id="fconfig" method="post" action="./config_form_update.php" enctype="multipart/form-data">

    <!-- 히든 필드들 -->
    <input type="hidden" name="cf_use_copy_log" value="0">
    <div class="tbl_frm01 tbl_wrap">
        <table>
            <colgroup>
                <col class="grid_4">
                <col>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th scope="row"><label for="cf_title">홈페이지 제목<strong class="sound_only">필수</strong></label></th>
                    <td><input type="text" name="cf_title" value="<?php echo get_sanitize_input($config['cf_title']); ?>" id="cf_title" required class="required frm_input" size="40"></td>
                    <th scope="row"><label for="cf_admin">최고관리자<strong class="sound_only">필수</strong></label></th>
                    <td><?php echo get_member_id_select('cf_admin', 10, $config['cf_admin'], 'required') ?></td>
                </tr>
                <tr>
                    <th scope="row"><label for="cf_description">홈페이지 설명</label></th>
                    <td colspan="3">
                        <?php echo help('링크 공유 시 표시되는 기본 설명입니다.') ?>
                        <input type="text" name="cf_description" value="<?php echo get_sanitize_input($config['cf_description'] ?? ''); ?>" id="cf_description" class="frm_input" size="80" placeholder="사이트를 소개하는 짧은 설명">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cf_image_file">대표 이미지</label></th>
                    <td colspan="3">
                        <?php echo help('링크 공유 시 표시되는 기본 이미지입니다. (1200x630px 권장)') ?>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="file" name="cf_image_file" id="cf_image_file" class="frm_file" accept="image/*">
                            <?php if (!empty($config['cf_image'])): ?>
                            <button type="button" class="btn_frmline" id="btn_delete_cf_image">삭제</button>
                            <img src="<?php echo htmlspecialchars($config['cf_image']); ?>" alt="대표 이미지" style="max-width: 60px; max-height: 40px; border: 1px solid #ddd; border-radius: 4px;">
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="cf_image" id="cf_image" value="<?php echo htmlspecialchars($config['cf_image'] ?? ''); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cf_visit">사이트 공개 설정</label></th>
                    <td>
                        <select name="cf_visit" id="cf_visit" class="frm_input">
                            <option value="0"<?php echo ($config['cf_visit'] == '0') ? ' selected' : ''; ?>>전체 공개</option>
                            <option value="1"<?php echo ($config['cf_visit'] == '1') ? ' selected' : ''; ?>>멤버 공개</option>
                        </select>
                    </td>
                    <th scope="row"><label for="cf_new">회원가입 설정</label></th>
                    <td>
                        <select name="cf_new" id="cf_new" class="frm_input">
                            <option value="1"<?php echo ($config['cf_new'] == '1') ? ' selected' : ''; ?>>가능</option>
                            <option value="0"<?php echo ($config['cf_new'] == '0') ? ' selected' : ''; ?>>불가능</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cf_admin_email">관리자 메일 주소<strong class="sound_only">필수</strong></label></th>
                    <td>
                        <?php echo help('관리자가 보내고 받는 용도로 사용하는 메일 주소를 입력합니다.') ?>
                        <input type="text" name="cf_admin_email" value="<?php echo get_sanitize_input($config['cf_admin_email']); ?>" id="cf_admin_email" required class="required email frm_input" size="40">
                    </td>
                    <th scope="row"><label for="cf_admin_db">DB 관리 주소</label></th>
                    <td colspan="3">
                        <?php echo help('데이터베이스 관리 도구(phpMyAdmin 등)의 URL을 입력합니다. 예: http://localhost/phpmyadmin') ?>
                        <input type="text" name="cf_admin_db" value="<?php echo get_sanitize_input($config['cf_admin_db']); ?>" id="cf_admin_db" class="frm_input" size="80" placeholder="http://localhost/phpmyadmin">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cf_cut_name">닉네임 표시</label></th>
                    <td>
                        <input type="text" name="cf_cut_name" value="<?php echo (int) $config['cf_cut_name'] ?>" id="cf_cut_name" class="frm_input" size="5"> 자리만 표시
                    </td>
                    <th scope="row"><label for="cf_login_minutes">현재 접속자</label></th>
                    <td>
                        <?php echo help('설정값 이내의 접속자를 현재 접속자로 인정') ?>
                        <input type="text" name="cf_login_minutes" value="<?php echo (int) $config['cf_login_minutes'] ?>" id="cf_login_minutes" class="frm_input" size="3"> 분
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cf_memo_del">쪽지 삭제</label></th>
                    <td>
                        <?php echo help('설정일이 지난 쪽지 자동 삭제') ?>
                        <input type="text" name="cf_memo_del" value="<?php echo (int) $config['cf_memo_del'] ?>" id="cf_memo_del" class="frm_input" size="5"> 일
                    </td>
                    <th scope="row"><label for="cf_search_part">검색 단위</label></th>
                    <td colspan="3"><input type="text" name="cf_search_part" value="<?php echo (int) $config['cf_search_part'] ?>" id="cf_search_part" class="frm_input" size="4"> 건 단위로 검색</td>
                </tr>
                <tr>
                    <th scope="row"><label for="cf_write_pages">페이지 표시 수<strong class="sound_only">필수</strong></label></th>
                    <td><input type="text" name="cf_write_pages" value="<?php echo (int) $config['cf_write_pages'] ?>" id="cf_write_pages" required class="required numeric frm_input" size="3"> 페이지씩 표시</td>
                    <th scope="row"><label for="cf_mobile_pages">모바일 페이지 표시 수<strong class="sound_only">필수</strong></label></th>
                    <td><input type="text" name="cf_mobile_pages" value="<?php echo (int) $config['cf_mobile_pages'] ?>" id="cf_mobile_pages" required class="required numeric frm_input" size="3"> 페이지씩 표시</td>
                </tr>
                <tr>
                    <th scope="row"><label for="cf_delay_sec">글쓰기 간격<strong class="sound_only">필수</strong></label></th>
                    <td><input type="text" name="cf_delay_sec" value="<?php echo (int) $config['cf_delay_sec'] ?>" id="cf_delay_sec" required class="required numeric frm_input" size="3"> 초 지난후 가능</td>
                    <th scope="row"><label for="cf_link_target">새창 링크</label></th>
                    <td>
                        <?php echo help('글내용중 자동 링크되는 타켓을 지정합니다.') ?>
                        <select name="cf_link_target" id="cf_link_target">
                            <option value="_blank" <?php echo get_selected($config['cf_link_target'], '_blank') ?>>_blank</option>
                            <option value="_self" <?php echo get_selected($config['cf_link_target'], '_self') ?>>_self</option>
                            <option value="_top" <?php echo get_selected($config['cf_link_target'], '_top') ?>>_top</option>
                            <option value="_new" <?php echo get_selected($config['cf_link_target'], '_new') ?>>_new</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cf_editor">기본 에디터 선택</label></th>
                    <td>
                        <?php echo help('글쓰기 시 기본 에디터를 설정합니다. 스킨에 따라 적용되지 않을 수 있습니다.') ?>
                        <select name="cf_editor" id="cf_editor">
                        <?php
                        $arr = get_skin_dir('', G5_EDITOR_PATH);
                        for ($i=0; $i<count($arr); $i++) {
                            if ($i == 0) {
                                echo "<option value=\"\">기본환경설정의 에디터 사용</option>";
                            }
                            echo "<option value=\"".$arr[$i]."\"".get_selected($config['cf_editor'], $arr[$i]).">".$arr[$i]."</option>\n";
                        }
                        ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cf_image_extension">이미지 업로드 확장자</label></th>
                    <td>
                        <?php echo help('캐릭터 이미지 등 업로드 가능 확장자. | 로 구분') ?>
                        <input type="text" name="cf_image_extension" value="<?php echo get_sanitize_input($config['cf_image_extension']); ?>" id="cf_image_extension" class="frm_input" size="70">
                    </td>
                    <th scope="row"><label for="cf_movie_extension">동영상 업로드 확장자</label></th>
                    <td>
                        <?php echo help('동영상 파일 업로드 가능 확장자. | 로 구분') ?>
                        <input type="text" name="cf_movie_extension" value="<?php echo get_sanitize_input($config['cf_movie_extension']); ?>" id="cf_movie_extension" class="frm_input" size="70">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cf_filter">단어 필터링</label></th>
                    <td colspan="3">
                        <?php echo help('입력된 단어가 포함된 내용은 게시할 수 없습니다. 단어와 단어 사이는 ,로 구분합니다.') ?>
                        <textarea name="cf_filter" id="cf_filter" rows="7"><?php echo get_sanitize_input($config['cf_filter']); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cf_possible_ip">접근가능 IP</label></th>
                    <td>
                        <?php echo help('입력된 IP의 컴퓨터만 접근할 수 있습니다.<br>123.123.+ 도 입력 가능. (엔터로 구분)') ?>
                        <textarea name="cf_possible_ip" id="cf_possible_ip"><?php echo get_sanitize_input($config['cf_possible_ip']); ?></textarea>
                    </td>
                    <th scope="row"><label for="cf_intercept_ip">접근차단 IP</label></th>
                    <td>
                        <?php echo help('입력된 IP의 컴퓨터는 접근할 수 없음.<br>123.123.+ 도 입력 가능. (엔터로 구분)') ?>
                        <textarea name="cf_intercept_ip" id="cf_intercept_ip"><?php echo get_sanitize_input($config['cf_intercept_ip']); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cf_noindex">검색 엔진 차단</label></th>
                    <td colspan="3">
                        <label for="cf_noindex" style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="cf_noindex" id="cf_noindex" value="1"<?php echo ($config['cf_noindex'] ?? 0) ? ' checked' : ''; ?>>
                            <span>검색 엔진 색인 차단 (noindex, nofollow)</span>
                        </label>
                        <?php echo help('체크하면 검색 엔진에서 이 사이트를 색인하지 않도록 robots 메타태그가 자동으로 추가됩니다. 사이트가 검색 결과에 노출되지 않습니다.'); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cf_add_meta">추가 메타태그</label></th>
                    <td colspan="3">
                        <?php echo help('추가로 사용하실 meta 태그를 입력합니다. HTML 태그 사용 가능합니다.'); ?>
                        <textarea name="cf_add_meta" id="cf_add_meta"><?php echo get_text($config['cf_add_meta']); ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cf_stipulation">회원가입약관</label></th>
                    <td><textarea name="cf_stipulation" id="cf_stipulation" rows="15"><?php echo html_purifier($config['cf_stipulation']); ?></textarea></td>
                    <th scope="row"><label for="cf_privacy">개인정보처리방침</label></th>
                    <td><textarea id="cf_privacy" name="cf_privacy" rows="15"><?php echo html_purifier($config['cf_privacy']); ?></textarea></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="btn_fixed_top btn_confirm">
        <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
    </div>
</form>

<script>
// 대표 이미지 삭제 버튼
document.addEventListener('DOMContentLoaded', function() {
    const deleteBtn = document.getElementById('btn_delete_cf_image');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (!confirm('대표 이미지를 삭제하시겠습니까?')) {
                return;
            }

            fetch('config_image_delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'type=cf_image'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // 페이지 새로고침
                    location.reload();
                } else {
                    alert('삭제 실패: ' + data.message);
                }
            })
            .catch(error => {
                alert('삭제 중 오류가 발생했습니다.');
            });
        });
    }
});
</script>

<?php
if (stripos($config['cf_image_extension'], "webp") !== false) {
    if (!function_exists("imagewebp")) {
        echo '<script>' . PHP_EOL;
        echo 'alert("이 서버는 webp 이미지를 지원하고 있지 않습니다.\n이미지 업로드 확장자에서 webp 확장자를 제거해 주십시오.");' . PHP_EOL;
        echo 'document.getElementById("cf_image_extension").focus();' . PHP_EOL;
        echo '</script>' . PHP_EOL;
    }
}

require_once './admin.tail.php';
?>