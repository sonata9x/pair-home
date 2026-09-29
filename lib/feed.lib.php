<?php
/**
 * RA0 Edition 이웃 피드 시스템 라이브러리
 *
 * @package RA0Edition
 * @since 1.0
 */
if (!defined('_GNUBOARD_')) exit;

// 테이블 상수 정의
if (!defined('G5_FEED_NEIGHBOR_TABLE')) {
    define('G5_FEED_NEIGHBOR_TABLE', G5_TABLE_PREFIX . 'feed_neighbor');
}

// 캐시 유효 시간 (초)
if (!defined('FEED_CACHE_TIME')) {
    define('FEED_CACHE_TIME', 600); // 10분
}

/**
 * 피드 노출 게시판 목록 조회
 *
 * @return array 게시판 목록
 */
function get_feed_boards()
{
    global $g5;

    // bo_feed_use 컬럼 존재 여부 확인
    $column_check = sql_query("SHOW COLUMNS FROM {$g5['board_table']} LIKE 'bo_feed_use'", false);
    $has_feed_column = ($column_check && sql_num_rows($column_check) > 0);

    $where = "bo_read_level = 1";
    if ($has_feed_column) {
        $where .= " AND bo_feed_use = 1";
    }

    $sql = "SELECT bo_table, bo_subject, bo_type
            FROM {$g5['board_table']}
            WHERE {$where}
            ORDER BY bo_order, bo_table";
    $result = sql_query($sql);

    $boards = array();
    while ($row = sql_fetch_array($result)) {
        $boards[] = $row;
    }

    return $boards;
}

/**
 * 피드용 최신글 조회
 *
 * @param int $limit 조회할 글 수
 * @return array 글 목록
 */
function get_feed_items($limit = 20)
{
    global $g5, $config;

    $boards = get_feed_boards();
    if (empty($boards)) {
        return array();
    }

    $union_queries = array();

    foreach ($boards as $board) {
        $write_table = $g5['write_prefix'] . $board['bo_table'];

        // 테이블 존재 확인
        $table_check = sql_query("SHOW TABLES LIKE '{$write_table}'", false);
        if (!$table_check || sql_num_rows($table_check) == 0) {
            continue;
        }

        $bo_table_escaped = sql_escape_string($board['bo_table']);
        $bo_subject_escaped = sql_escape_string($board['bo_subject']);

        // 타임라인 게시판인 경우 tm_origin 컬럼 존재 확인
        $timeline_condition = '';
        if (isset($board['bo_type']) && $board['bo_type'] === 'timeline') {
            // tm_origin 컬럼 존재 여부 확인
            $col_check = sql_query("SHOW COLUMNS FROM {$write_table} LIKE 'tm_origin'", false);
            if ($col_check && sql_num_rows($col_check) > 0) {
                $timeline_condition = ' AND tm_origin = wr_id';
            }
        }

        // wr_img 컬럼 존재 확인
        $wr_img_field = "'' as wr_img";
        $img_check = sql_query("SHOW COLUMNS FROM {$write_table} LIKE 'wr_img'", false);
        if ($img_check && sql_num_rows($img_check) > 0) {
            $wr_img_field = "wr_img";
        }

        // wr_img 처리 (COLLATE 통일)
        if ($wr_img_field === "wr_img") {
            $wr_img_select = "CONVERT(IFNULL(wr_img, '') USING utf8mb4) COLLATE utf8mb4_general_ci as wr_img";
        } else {
            $wr_img_select = "CONVERT('' USING utf8mb4) COLLATE utf8mb4_general_ci as wr_img";
        }

        $union_queries[] = "
            SELECT
                CONVERT('{$bo_table_escaped}' USING utf8mb4) COLLATE utf8mb4_general_ci as bo_table,
                CONVERT('{$bo_subject_escaped}' USING utf8mb4) COLLATE utf8mb4_general_ci as bo_subject,
                wr_id,
                CONVERT(wr_subject USING utf8mb4) COLLATE utf8mb4_general_ci as wr_subject,
                CONVERT(wr_content USING utf8mb4) COLLATE utf8mb4_general_ci as wr_content,
                CONVERT(wr_name USING utf8mb4) COLLATE utf8mb4_general_ci as wr_name,
                wr_datetime,
                {$wr_img_select}
            FROM {$write_table}
            WHERE wr_is_comment = 0
              AND (wr_option IS NULL OR wr_option = '' OR wr_option NOT LIKE '%secret%')
              {$timeline_condition}
        ";
    }

    if (empty($union_queries)) {
        return array();
    }

    $sql = "SELECT * FROM (" . implode(' UNION ALL ', $union_queries) . ") as feed_posts
            ORDER BY wr_datetime DESC
            LIMIT 0, " . (int)$limit;

    $result = sql_query($sql, false);

    if (!$result) {
        return array();
    }

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $items[] = array(
            'id' => $row['wr_id'],
            'board' => $row['bo_subject'],
            'board_id' => $row['bo_table'],
            'title' => get_text($row['wr_subject']),
            'summary' => get_feed_summary($row),
            'author' => $row['wr_name'],
            'thumbnail' => get_feed_thumbnail($row),
            'date' => date('c', strtotime($row['wr_datetime'])),
            'link' => G5_URL . '/bbs/board.php?bo_table=' . $row['bo_table'] . '&wr_id=' . $row['wr_id']
        );
    }

    return $items;
}

/**
 * 피드 요약 생성
 *
 * @param array $write 글 데이터
 * @return string 요약 텍스트
 */
function get_feed_summary($write)
{
    // wr_title 필드가 있으면 사용 (일부 스킨에서 사용)
    if (!empty($write['wr_title'])) {
        return get_text(mb_substr($write['wr_title'], 0, 100, 'UTF-8'));
    }

    // wr_content에서 HTML 제거 후 앞부분 추출
    $content = strip_tags($write['wr_content']);
    $content = preg_replace('/\s+/', ' ', $content);
    $content = trim($content);

    return get_text(mb_substr($content, 0, 100, 'UTF-8'));
}

/**
 * 피드 썸네일 URL 생성
 *
 * @param array $write 글 데이터
 * @return string|null 썸네일 URL
 */
function get_feed_thumbnail($write)
{
    if (empty($write['wr_img'])) {
        return null;
    }

    $wr_img = $write['wr_img'];

    // URL이면 그대로 반환
    if (preg_match('/^https?:\/\//i', $wr_img)) {
        return $wr_img;
    }

    // 경로가 포함되어 있으면 G5_URL 붙여서 반환
    if (strpos($wr_img, '/') !== false) {
        return G5_URL . $wr_img;
    }

    // 파일명만 있으면 data/file 경로 추가
    return G5_DATA_URL . '/file/' . $write['bo_table'] . '/' . $wr_img;
}

/**
 * 이웃 목록 조회
 *
 * @return array 이웃 목록
 */
function get_all_neighbors()
{
    // 테이블 존재 확인
    if (!feed_neighbor_table_exists()) {
        return array();
    }

    $sql = "SELECT * FROM " . G5_FEED_NEIGHBOR_TABLE . " ORDER BY fn_site_name";
    $result = sql_query($sql);

    $neighbors = array();
    while ($row = sql_fetch_array($result)) {
        $neighbors[] = $row;
    }

    return $neighbors;
}

/**
 * 외부 피드 가져오기
 *
 * @param string $url 피드 URL
 * @param int $timeout 타임아웃 (초)
 * @param bool $return_error 오류 정보 반환 여부
 * @return array|false 피드 데이터 또는 실패 (return_error=true면 오류 배열)
 */
function fetch_neighbor_feed($url, $timeout = 30, $return_error = false)
{
    $error_result = function($msg) use ($return_error) {
        if ($return_error) {
            return array('error' => $msg);
        }
        return false;
    };

    // URL 검증
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return $error_result('올바른 URL 형식이 아닙니다.');
    }

    $json = false;
    $error_msg = '';

    // cURL 사용 (권장)
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'User-Agent: RA0Edition/1.0'
            )
        ));
        $json = curl_exec($ch);
        $curl_error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curl_error) {
            return $error_result('cURL 오류: ' . $curl_error);
        }

        if ($http_code === 0) {
            return $error_result('서버에 연결할 수 없습니다.');
        }

        if ($http_code >= 400) {
            return $error_result('HTTP 오류: ' . $http_code);
        }
    }
    // file_get_contents 대체
    else {
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'timeout' => $timeout,
                'header' => "Accept: application/json\r\n" .
                            "User-Agent: RA0Edition/1.0\r\n"
            ),
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false
            )
        ));
        $json = @file_get_contents($url, false, $context);

        if ($json === false) {
            return $error_result('서버에 연결할 수 없습니다. (file_get_contents 실패)');
        }
    }

    if ($json === false || empty($json)) {
        return $error_result('빈 응답을 받았습니다.');
    }

    // BOM(Byte Order Mark) 제거
    $json = preg_replace('/^\xEF\xBB\xBF/', '', $json);

    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return $error_result('JSON 파싱 오류: ' . json_last_error_msg());
    }

    if (!$data || !isset($data['items'])) {
        return $error_result('피드 형식이 올바르지 않습니다. (items 없음)');
    }

    return $data;
}

/**
 * 피드 URL 유효성 검증
 *
 * @param string $url 검증할 URL
 * @return array 검증 결과 [valid => bool, site_name => string, error => string]
 */
function validate_feed_url($url)
{
    $result = array(
        'valid' => false,
        'site_name' => '',
        'error' => ''
    );

    // URL 형식 검증
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $result['error'] = '올바른 URL 형식이 아닙니다.';
        return $result;
    }

    // 피드 가져오기 시도 (오류 정보 포함)
    $feed = fetch_neighbor_feed($url, 30, true);

    // 오류 발생
    if (isset($feed['error'])) {
        $result['error'] = $feed['error'];
        return $result;
    }

    // 피드 데이터가 없음
    if (!$feed) {
        $result['error'] = '피드를 가져올 수 없습니다.';
        return $result;
    }

    $result['valid'] = true;
    $result['site_name'] = isset($feed['site_name']) ? $feed['site_name'] : parse_url($url, PHP_URL_HOST);

    return $result;
}

/**
 * 모든 이웃 피드 통합 조회 (캐싱 적용)
 *
 * @param int $limit 조회할 총 개수
 * @return array 통합 피드 목록
 */
function get_all_neighbor_feeds($limit = 50)
{
    $neighbors = get_all_neighbors();
    if (empty($neighbors)) {
        return array();
    }

    $all_items = array();

    foreach ($neighbors as $neighbor) {
        // 캐시 확인
        $cache_data = get_feed_cache($neighbor['fn_id']);

        if ($cache_data !== false) {
            // 캐시 사용
            $feed = $cache_data;
        } else {
            // 외부 요청
            $feed = fetch_neighbor_feed($neighbor['fn_feed_url']);

            if ($feed) {
                // 캐시 저장
                set_feed_cache($neighbor['fn_id'], $feed);
            }
        }

        if ($feed && isset($feed['items'])) {
            foreach ($feed['items'] as $item) {
                $item['source_name'] = $neighbor['fn_site_name'];
                $item['source_url'] = $neighbor['fn_site_url'];
                $all_items[] = $item;
            }
        }
    }

    // 날짜순 정렬
    usort($all_items, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    // 개수 제한
    return array_slice($all_items, 0, $limit);
}

/**
 * 피드 캐시 조회
 *
 * @param int $neighbor_id 이웃 ID
 * @return array|false 캐시 데이터 또는 false
 */
function get_feed_cache($neighbor_id)
{
    $cache_file = G5_DATA_PATH . '/cache/feed_' . (int)$neighbor_id . '.php';

    if (!file_exists($cache_file)) {
        return false;
    }

    // 캐시 유효성 확인
    $mtime = filemtime($cache_file);
    if (time() - $mtime > FEED_CACHE_TIME) {
        @unlink($cache_file);
        return false;
    }

    $data = @include($cache_file);

    if (!$data || !is_array($data)) {
        return false;
    }

    return $data;
}

/**
 * 피드 캐시 저장
 *
 * @param int $neighbor_id 이웃 ID
 * @param array $data 캐시할 데이터
 * @return bool 성공 여부
 */
function set_feed_cache($neighbor_id, $data)
{
    $cache_dir = G5_DATA_PATH . '/cache';

    if (!is_dir($cache_dir)) {
        @mkdir($cache_dir, 0755, true);
    }

    $cache_file = $cache_dir . '/feed_' . (int)$neighbor_id . '.php';
    $content = '<?php if (!defined("_GNUBOARD_")) exit; return ' . var_export($data, true) . ';';

    return (bool)@file_put_contents($cache_file, $content);
}

/**
 * 피드 캐시 삭제
 *
 * @param int $neighbor_id 이웃 ID (0이면 전체)
 */
function clear_feed_cache($neighbor_id = 0)
{
    $cache_dir = G5_DATA_PATH . '/cache';

    if ($neighbor_id > 0) {
        $file = $cache_dir . '/feed_' . (int)$neighbor_id . '.php';
        if (file_exists($file)) {
            @unlink($file);
        }
    } else {
        // 전체 피드 캐시 삭제
        $files = glob($cache_dir . '/feed_*.php');
        if ($files) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }
}

/**
 * 이웃 테이블 존재 확인
 *
 * @return bool 존재 여부
 */
function feed_neighbor_table_exists()
{
    $result = sql_query("SHOW TABLES LIKE '" . G5_FEED_NEIGHBOR_TABLE . "'", false);
    return ($result && sql_num_rows($result) > 0);
}

/**
 * bo_feed_use 컬럼 존재 확인
 *
 * @return bool 존재 여부
 */
function feed_column_exists()
{
    global $g5;
    $result = sql_query("SHOW COLUMNS FROM {$g5['board_table']} LIKE 'bo_feed_use'", false);
    return ($result && sql_num_rows($result) > 0);
}

/**
 * 피드 시스템 설치
 *
 * @return array 설치 결과 [success => bool, message => string]
 */
function install_feed_system()
{
    global $g5;

    $result = array('success' => false, 'message' => '');

    // 1. bo_feed_use 컬럼 추가
    if (!feed_column_exists()) {
        $sql = "ALTER TABLE {$g5['board_table']} ADD bo_feed_use TINYINT(1) NOT NULL DEFAULT '1' COMMENT '피드 노출 여부'";
        if (!sql_query($sql, false)) {
            $result['message'] = 'bo_feed_use 컬럼 추가 실패';
            return $result;
        }
    }

    // 2. 이웃 테이블 생성
    if (!feed_neighbor_table_exists()) {
        $sql = "CREATE TABLE IF NOT EXISTS `" . G5_FEED_NEIGHBOR_TABLE . "` (
            `fn_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '고유 ID',
            `fn_site_name` varchar(255) NOT NULL DEFAULT '' COMMENT '사이트명',
            `fn_site_url` varchar(500) NOT NULL DEFAULT '' COMMENT '사이트 URL',
            `fn_feed_url` varchar(500) NOT NULL DEFAULT '' COMMENT '피드 API URL',
            `fn_created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '등록일',
            `fn_updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
            PRIMARY KEY (`fn_id`),
            UNIQUE KEY `fn_site_url` (`fn_site_url`(191))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='이웃 사이트 목록'";

        if (!sql_query($sql, false)) {
            $result['message'] = '이웃 테이블 생성 실패';
            return $result;
        }
    }

    $result['success'] = true;
    $result['message'] = '피드 시스템이 설치되었습니다.';

    return $result;
}
