<?php
/**
 * RA0 Edition - 이모티콘 시스템 라이브러리
 *
 * 관리자가 등록한 이모티콘을 /이름 형식으로 사용
 * 게시글/댓글 출력 시 이미지로 변환
 */

if (!defined('_GNUBOARD_')) exit;

// 이모티콘 테이블명
define('RA0_EMO_TABLE', G5_TABLE_PREFIX . 'ra0_emo');

// 이모티콘 저장 경로
define('RA0_EMO_PATH', G5_DATA_PATH . '/emoticon');
define('RA0_EMO_URL', G5_DATA_URL . '/emoticon');

// =====================================================
// 테이블 초기화
// =====================================================

/**
 * 이모티콘 테이블 생성
 */
function init_emoticon_table() {
    $table = RA0_EMO_TABLE;

    // 테이블 존재 여부 체크
    $sql = "SHOW TABLES LIKE '{$table}'";
    $result = sql_fetch($sql);

    if (!$result) {
        // 테이블 생성
        $create_sql = "CREATE TABLE {$table} (
            emo_id INT AUTO_INCREMENT PRIMARY KEY,
            emo_name VARCHAR(50) NOT NULL UNIQUE COMMENT '이모티콘 이름 (슬래시 제외)',
            emo_file VARCHAR(255) NOT NULL COMMENT '저장된 파일명',
            emo_category VARCHAR(50) DEFAULT NULL COMMENT '카테고리',
            emo_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_emo_name (emo_name),
            INDEX idx_emo_category (emo_category)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";
        sql_query($create_sql);
    }
}

// =====================================================
// 이모티콘 조회
// =====================================================

/**
 * 모든 이모티콘 목록 가져오기 (정적 캐싱)
 *
 * @return array 이모티콘 배열 [emo_name => row]
 */
function get_emoticons() {
    static $emoticons = null;

    if ($emoticons === null) {
        $emoticons = [];
        $table = RA0_EMO_TABLE;

        $sql = "SELECT * FROM {$table} ORDER BY emo_name";
        $result = sql_query($sql);

        while ($row = sql_fetch_array($result)) {
            $emoticons[$row['emo_name']] = $row;
        }
    }

    return $emoticons;
}

/**
 * 이모티콘 검색 (API용)
 *
 * @param string $keyword 검색어
 * @param int $limit 최대 개수
 * @return array 검색 결과
 */
function search_emoticons($keyword = '', $limit = 20) {
    $emoticons = get_emoticons();
    $results = [];

    $keyword = trim($keyword);

    foreach ($emoticons as $name => $emo) {
        // 키워드가 없으면 전체, 있으면 이름에 포함된 것만
        if (empty($keyword) || mb_strpos($name, $keyword) !== false) {
            $results[] = [
                'name' => $emo['emo_name'],
                'code' => '/' . $emo['emo_name'],
                'image' => RA0_EMO_URL . '/' . $emo['emo_file'],
                'category' => $emo['emo_category']
            ];

            if (count($results) >= $limit) break;
        }
    }

    return $results;
}

/**
 * 단일 이모티콘 조회
 *
 * @param int $emo_id 이모티콘 ID
 * @return array|false
 */
function get_emoticon($emo_id) {
    $table = RA0_EMO_TABLE;
    $emo_id = (int)$emo_id;

    $sql = "SELECT * FROM {$table} WHERE emo_id = '{$emo_id}'";
    return sql_fetch($sql);
}

/**
 * 이모티콘 이름 중복 체크
 *
 * @param string $name 이모티콘 이름
 * @param int $exclude_id 제외할 ID (수정 시)
 * @return bool 중복이면 true
 */
function is_emoticon_exists($name, $exclude_id = 0) {
    $table = RA0_EMO_TABLE;
    $name = sql_real_escape_string($name);
    $exclude_id = (int)$exclude_id;

    $sql = "SELECT emo_id FROM {$table} WHERE emo_name = '{$name}'";
    if ($exclude_id > 0) {
        $sql .= " AND emo_id != '{$exclude_id}'";
    }

    $row = sql_fetch($sql);
    return !empty($row['emo_id']);
}

// =====================================================
// 이모티콘 변환
// =====================================================

/**
 * 콘텐츠의 /이름 패턴을 이모티콘 이미지로 변환
 *
 * @param string $content 변환할 콘텐츠
 * @return string 변환된 콘텐츠
 */
function convert_emoticon($content) {
    $emoticons = get_emoticons();

    if (empty($emoticons)) {
        return $content;
    }

    // code/pre 태그 보호 (autolink 패턴과 동일)
    $code_blocks = [];
    $code_index = 0;

    // <pre>...</pre> 보호
    $content = preg_replace_callback('/<pre[^>]*>.*?<\/pre>/is', function($matches) use (&$code_blocks, &$code_index) {
        $placeholder = "___EMO_PRE_BLOCK_{$code_index}___";
        $code_blocks[$placeholder] = $matches[0];
        $code_index++;
        return $placeholder;
    }, $content);

    // <code>...</code> 보호
    $content = preg_replace_callback('/<code[^>]*>.*?<\/code>/is', function($matches) use (&$code_blocks, &$code_index) {
        $placeholder = "___EMO_CODE_BLOCK_{$code_index}___";
        $code_blocks[$placeholder] = $matches[0];
        $code_index++;
        return $placeholder;
    }, $content);

    // /이름 패턴 변환 (한글, 영문, 숫자, _ 허용)
    $content = preg_replace_callback('/\/([가-힣a-zA-Z0-9_]+)/', function($matches) use ($emoticons) {
        $name = $matches[1];

        if (isset($emoticons[$name])) {
            $emo = $emoticons[$name];
            $url = RA0_EMO_URL . '/' . $emo['emo_file'];
            $alt = htmlspecialchars('/' . $name, ENT_QUOTES);
            return '<img src="' . $url . '" alt="' . $alt . '" class="ra0-emoticon">';
        }

        // 매칭 안 되면 원본 반환
        return $matches[0];
    }, $content);

    // 보호된 태그 복원
    foreach ($code_blocks as $placeholder => $original) {
        $content = str_replace($placeholder, $original, $content);
    }

    return $content;
}

// =====================================================
// 이모티콘 관리
// =====================================================

/**
 * 이모티콘 이미지 저장
 *
 * @param array $file $_FILES 배열의 단일 파일
 * @return string|false 저장된 파일명 또는 실패 시 false
 */
function save_emoticon_image($file) {
    // 디렉토리 생성
    if (!is_dir(RA0_EMO_PATH)) {
        mkdir(RA0_EMO_PATH, 0755, true);
    }

    // 허용 확장자
    $allowed_ext = ['gif', 'png', 'webp', 'jpg', 'jpeg'];

    // 파일 정보
    $tmp_file = $file['tmp_name'];
    $original_name = $file['name'];
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    // 확장자 체크
    if (!in_array($ext, $allowed_ext)) {
        return false;
    }

    // MIME 타입 체크
    $image_info = getimagesize($tmp_file);
    if (!$image_info) {
        return false;
    }

    $allowed_mime = ['image/gif', 'image/png', 'image/webp', 'image/jpeg'];
    if (!in_array($image_info['mime'], $allowed_mime)) {
        return false;
    }

    // 크기 체크 (400x400 초과 시 거부)
    if ($image_info[0] > 400 || $image_info[1] > 400) {
        return false;
    }

    // 랜덤 파일명 생성
    $new_filename = uniqid('emo_') . '_' . time() . '.' . $ext;
    $upload_path = RA0_EMO_PATH . '/' . $new_filename;

    // 파일 이동
    if (!move_uploaded_file($tmp_file, $upload_path)) {
        return false;
    }

    return $new_filename;
}

/**
 * 이모티콘 등록
 *
 * @param string $name 이모티콘 이름
 * @param string $file 파일명
 * @param string $category 카테고리 (선택)
 * @return int|false 생성된 ID 또는 실패 시 false
 */
function insert_emoticon($name, $file, $category = '') {
    $table = RA0_EMO_TABLE;

    $name = sql_real_escape_string($name);
    $file = sql_real_escape_string($file);
    $category = sql_real_escape_string($category);

    $sql = "INSERT INTO {$table} (emo_name, emo_file, emo_category, emo_created_at)
            VALUES ('{$name}', '{$file}', '{$category}', NOW())";

    $result = sql_query($sql, false);

    if ($result) {
        return sql_insert_id();
    }

    return false;
}

/**
 * 이모티콘 수정
 *
 * @param int $emo_id 이모티콘 ID
 * @param string $name 이모티콘 이름
 * @param string $file 파일명 (빈 값이면 유지)
 * @param string $category 카테고리
 * @return bool
 */
function update_emoticon($emo_id, $name, $file = '', $category = '') {
    $table = RA0_EMO_TABLE;

    $emo_id = (int)$emo_id;
    $name = sql_real_escape_string($name);
    $category = sql_real_escape_string($category);

    $sql = "UPDATE {$table} SET
            emo_name = '{$name}',
            emo_category = '{$category}'";

    if (!empty($file)) {
        $file = sql_real_escape_string($file);
        $sql .= ", emo_file = '{$file}'";
    }

    $sql .= " WHERE emo_id = '{$emo_id}'";

    return sql_query($sql, false) ? true : false;
}

/**
 * 이모티콘 삭제
 *
 * @param int $emo_id 이모티콘 ID
 * @return bool
 */
function delete_emoticon($emo_id) {
    $table = RA0_EMO_TABLE;
    $emo_id = (int)$emo_id;

    // 기존 파일 삭제
    $emo = get_emoticon($emo_id);
    if ($emo && !empty($emo['emo_file'])) {
        $file_path = RA0_EMO_PATH . '/' . $emo['emo_file'];
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
    }

    // DB 삭제
    $sql = "DELETE FROM {$table} WHERE emo_id = '{$emo_id}'";
    return sql_query($sql, false) ? true : false;
}

/**
 * 이모티콘 이름 검증
 *
 * @param string $name 검증할 이름
 * @return bool 유효하면 true
 */
function validate_emoticon_name($name) {
    // 빈 값 체크
    if (empty($name)) {
        return false;
    }

    // 길이 체크 (1~50자)
    if (mb_strlen($name) < 1 || mb_strlen($name) > 50) {
        return false;
    }

    // 허용 문자: 한글, 영문, 숫자, 밑줄
    if (!preg_match('/^[가-힣a-zA-Z0-9_]+$/', $name)) {
        return false;
    }

    return true;
}

// =====================================================
// 자동 초기화
// =====================================================

// 테이블 자동 생성
init_emoticon_table();
