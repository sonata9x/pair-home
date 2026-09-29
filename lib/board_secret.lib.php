<?php
if (!defined('_GNUBOARD_')) exit;

/**
 * RA0 Edition - 게시글 접근 제어 라이브러리
 *
 * - 비밀글 (wr_option: 'secret', RA0 표준)
 * - 멤버공개 (wr_option: 'member', RA0 확장)
 * - 성인글 (wr_adult: 1, RA0 확장)
 */

/**
 * 비밀글 조회 비밀번호 처리 함수
 *
 * @param string $write_table 게시판 테이블명 (예: g5_write_free)
 * @param int $wr_id 게시글 ID
 * @param array $post_data $_POST 데이터
 * @return bool 성공 여부
 */
function process_secret_password($write_table, $wr_id, $post_data = null) {
    if ($post_data === null) {
        $post_data = $_POST;
    }

    // wr_secret 필드 타입 확인 및 변경 (tinyint → varchar)
    $field_info = sql_fetch("SHOW COLUMNS FROM {$write_table} LIKE 'wr_secret'");
    if ($field_info && strpos($field_info['Type'], 'tinyint') !== false) {
        sql_query("ALTER TABLE {$write_table} MODIFY COLUMN wr_secret varchar(255) NOT NULL DEFAULT ''", false);
    }

    // wr_secret 필드 업데이트 (비밀글 조회 비밀번호)
    $secret_password = '';
    if (isset($post_data['secret']) && $post_data['secret'] == 'secret' && isset($post_data['wr_secret'])) {
        $secret_password = sql_real_escape_string(trim($post_data['wr_secret']));
    }

    // wr_secret 필드 업데이트
    $sql = "UPDATE {$write_table} SET wr_secret = '{$secret_password}' WHERE wr_id='{$wr_id}'";
    $result = sql_query($sql);

    return $result ? true : false;
}

/**
 * 게시판 테이블에 wr_secret 필드가 존재하는지 확인
 *
 * @param string $write_table 게시판 테이블명
 * @return bool 존재 여부
 */
function check_secret_field_exists($write_table) {
    $field_info = sql_fetch("SHOW COLUMNS FROM {$write_table} LIKE 'wr_secret'");
    return $field_info ? true : false;
}

/**
 * 게시글 접근 권한 체크
 *
 * @param array $post 게시글 데이터 ($view 또는 $list의 개별 항목)
 * @param string $bo_table 게시판 테이블명
 * @return array ['can_view' => bool, 'redirect_url' => string, 'reason' => string]
 */
function check_post_access($post, $bo_table) {
    global $member, $is_admin;

    $result = array(
        'can_view' => true,
        'redirect_url' => '',
        'reason' => ''
    );

    // 회원 여부 확인
    $is_member = !empty($member['mb_id']);

    // 작성자 여부 확인
    $is_owner = ($is_member && isset($post['mb_id']) && $member['mb_id'] === $post['mb_id']);

    // 관리자 또는 작성자는 모든 글 조회 가능
    if ($is_admin || $is_owner) {
        return $result;
    }

    // 1. 비밀글 체크 (RA0 표준)
    if (is_secret_post($post)) {
        $session_key = 'ss_secret_'.$bo_table.'_'.$post['wr_num'];
        if (!get_session($session_key)) {
            $result['can_view'] = false;
            $result['redirect_url'] = G5_BBS_URL."/password_check.php?w=s&bo_table=".$bo_table."&wr_id=".$post['wr_id'];
            $result['reason'] = 'secret';
            return $result;
        }
    }

    // 2. 멤버공개 체크 (RA0 확장)
    if (is_member_only_post($post)) {
        if (!$is_member) {
            $result['can_view'] = false;
            $result['redirect_url'] = G5_BBS_URL."/login.php?url=".urlencode(G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$post['wr_id']);
            $result['reason'] = 'member_only';
            return $result;
        }
    }

    // 3. 성인글 체크 (RA0 확장)
    // if (is_adult_post($post)) {
    //     if (!$is_admin && !check_adult_cert()) {
    //         // 성인 인증이 안 되어 있으면
    //         if (!$is_member) {
    //             $result['can_view'] = false;
    //             $result['redirect_url'] = G5_BBS_URL."/login.php";
    //             $result['reason'] = 'adult_need_login';
    //             return $result;
    //         }
    //     }
    // }

    return $result;
}

/**
 * 비밀글 여부 확인
 */
function is_secret_post($post) {
    return isset($post['wr_option']) && strpos($post['wr_option'], 'secret') !== false;
}

/**
 * 멤버공개글 여부 확인
 */
function is_member_only_post($post) {
    return isset($post['wr_option']) && strpos($post['wr_option'], 'member') !== false;
}

/**
 * 성인글 여부 확인
 */
function is_adult_post($post) {
    return isset($post['wr_adult']) && $post['wr_adult'] == '1';
}

/**
 * 성인 인증 여부 확인
 */
function check_adult_cert() {
    return get_session('ss_adult_cert') ? true : false;
}

/**
 * 성인 인증 설정
 */
function set_adult_cert() {
    set_session('ss_adult_cert', true);
}

/**
 * 비밀글 세션 설정
 */
function set_secret_session($bo_table, $wr_num) {
    $session_key = 'ss_secret_'.$bo_table.'_'.$wr_num;
    set_session($session_key, true);
}

/**
 * 접근 제어 HTML 출력 (비밀글/멤버공개 표시)
 */
function print_access_form($post, $bo_table, $qstr = '') {
    $access = check_post_access($post, $bo_table);

    if ($access['can_view']) {
        return '';
    }

    $html = '';

    if ($access['reason'] == 'secret') {
        $html .= '<div class="access-control secret-post">';
        $html .= '    <div class="access-icon">🔒</div>';
        $html .= '    <div class="access-message">비밀글입니다</div>';
        $html .= '    <form method="post" action="'.G5_BBS_URL.'/password_check.php" class="access-form">';
        $html .= '        <input type="hidden" name="w" value="s">';
        $html .= '        <input type="hidden" name="bo_table" value="'.$bo_table.'">';
        $html .= '        <input type="hidden" name="wr_id" value="'.$post['wr_id'].'">';
        if ($qstr) {
            $html .= '        <input type="hidden" name="qstr" value="'.$qstr.'">';
        }
        $html .= '        <input type="password" name="wr_password" placeholder="비밀번호를 입력하세요" required class="access-password">';
        $html .= '        <button type="submit" class="access-submit">확인</button>';
        $html .= '    </form>';
        $html .= '</div>';

    } else if ($access['reason'] == 'member_only') {
        $html .= '<div class="access-control member-only-post">';
        $html .= '    <div class="access-icon">👥</div>';
        $html .= '    <div class="access-message">멤버공개 글입니다</div>';
        $html .= '    <div class="access-description">로그인 후 이용해주세요.</div>';
        $html .= '    <a href="'.$access['redirect_url'].'" class="access-login-btn">로그인</a>';
        $html .= '</div>';

    } else if ($access['reason'] == 'adult_need_login') {
        $html .= '<div class="access-control adult-post">';
        $html .= '    <div class="access-icon">🔞</div>';
        $html .= '    <div class="access-message">성인 콘텐츠입니다</div>';
        $html .= '    <div class="access-description">로그인이 필요합니다.</div>';
        $html .= '    <a href="'.$access['redirect_url'].'" class="access-login-btn">로그인</a>';
        $html .= '</div>';
    }

    return $html;
}

/**
 * 접근 제어 리다이렉트 (view.skin.php 상단에서 사용)
 */
function redirect_if_no_access($post, $bo_table, $qstr = '') {
    $access = check_post_access($post, $bo_table);

    if (!$access['can_view']) {
        if ($access['reason'] == 'secret') {
            $url = $access['redirect_url'];
            if ($qstr) {
                $url .= '&'.$qstr;
            }
            goto_url($url);

        } else if ($access['reason'] == 'member_only') {
            alert("멤버공개 글입니다. 로그인 후 이용해주세요.", $access['redirect_url']);

        } else if ($access['reason'] == 'adult_need_login') {
            alert("성인 콘텐츠입니다. 로그인이 필요합니다.", $access['redirect_url']);
        }
    }
}
