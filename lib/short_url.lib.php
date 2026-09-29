<?php
/**
 * RA0 Edition - URL 단축 시스템
 *
 * 게시글 공유를 위한 짧은 URL 생성 및 관리
 */

if (!defined('_GNUBOARD_')) exit;

/**
 * Base62 문자열 생성 (0-9, a-z, A-Z)
 *
 * @param int $length 생성할 키 길이 (기본 6자)
 * @return string
 */
function generate_short_key($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $key = '';
    $max = strlen($characters) - 1;

    for ($i = 0; $i < $length; $i++) {
        $key .= $characters[random_int(0, $max)];
    }

    return $key;
}

/**
 * 짧은 URL 생성 (DB에 저장)
 *
 * @param string $url 원본 URL
 * @param string $bo_table 게시판 테이블 (선택)
 * @param int $wr_id 게시글 ID (선택)
 * @param int $max_attempts 중복 시 재시도 횟수
 * @return string|false 생성된 짧은 키 또는 false
 */
function create_short_url($url, $bo_table = null, $wr_id = null, $max_attempts = 10) {
    $url = sql_real_escape_string(trim($url));
    $bo_table = $bo_table ? sql_real_escape_string($bo_table) : null;
    $wr_id = $wr_id ? (int)$wr_id : null;

    if (empty($url)) {
        return false;
    }

    // 중복 방지를 위해 최대 10번 시도
    for ($i = 0; $i < $max_attempts; $i++) {
        $key = generate_short_key(6);

        // 중복 체크
        $check_sql = "SELECT su_id FROM " . G5_SHORT_URL_TABLE . " WHERE su_key = '$key'";
        $check = sql_fetch($check_sql);

        if (!$check) {
            // 중복되지 않으면 삽입
            $insert_sql = "
                INSERT INTO " . G5_SHORT_URL_TABLE . "
                SET su_key = '$key',
                    su_url = '$url',
                    su_bo_table = " . ($bo_table ? "'$bo_table'" : "NULL") . ",
                    su_wr_id = " . ($wr_id ? "'$wr_id'" : "NULL") . ",
                    su_datetime = NOW()
            ";

            $result = sql_query($insert_sql, false);

            if ($result) {
                return $key;
            }
        }
    }

    return false;
}

/**
 * 게시글에 대한 짧은 URL 조회/생성
 * 이미 생성된 짧은 URL이 있으면 반환, 없으면 새로 생성
 *
 * @param string $bo_table 게시판 테이블
 * @param int $wr_id 게시글 ID
 * @return string|false 짧은 키 또는 false
 */
function get_short_url($bo_table, $wr_id) {
    $bo_table = sql_real_escape_string($bo_table);
    $wr_id = (int)$wr_id;

    if (empty($bo_table) || $wr_id <= 0) {
        return false;
    }

    // 이미 생성된 짧은 URL이 있는지 확인
    $sql = "
        SELECT su_key
        FROM " . G5_SHORT_URL_TABLE . "
        WHERE su_bo_table = '$bo_table'
          AND su_wr_id = '$wr_id'
        ORDER BY su_datetime DESC
        LIMIT 1
    ";
    $row = sql_fetch($sql);

    if ($row) {
        return $row['su_key'];
    }

    // 없으면 새로 생성
    $original_url = G5_BBS_URL . '/board.php?bo_table=' . $bo_table . '&wr_id=' . $wr_id;
    return create_short_url($original_url, $bo_table, $wr_id);
}

/**
 * 짧은 키로 원본 URL 조회
 *
 * @param string $key 짧은 URL 키
 * @return array|false URL 정보 또는 false
 */
function resolve_short_url($key) {
    $key = sql_real_escape_string(trim($key));

    if (empty($key)) {
        return false;
    }

    $sql = "
        SELECT su_url, su_bo_table, su_wr_id
        FROM " . G5_SHORT_URL_TABLE . "
        WHERE su_key = '$key'
    ";
    $row = sql_fetch($sql);

    return $row ? $row : false;
}

/**
 * 짧은 URL 전체 주소 생성
 *
 * @param string $key 짧은 URL 키
 * @return string 전체 짧은 URL
 */
function get_full_short_url($key) {
    return G5_URL . '/s/' . $key;
}
