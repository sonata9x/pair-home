<?php
/**
 * 게시판 파일 삭제 API (공용)
 *
 * - 업로드된 파일 삭제
 * - board_file 테이블에서 레코드 삭제
 * - wr_link1, wr_link2, wr_img 필드 초기화 (선택사항)
 * - ⚠️ bf_no, bf_content 재정렬 안 함 (RA0 Edition {이미지:N} 플레이스홀더 시스템)
 *
 * 파라미터:
 * - bo_table: 게시판 테이블명
 * - wr_id: 게시글 ID
 * - fileName: 삭제할 파일명
 * - bf_no: 파일 번호 (선택)
 * - bf_content: 파일 컨텐츠 번호 (선택, 지정 시 해당 bf_content만 삭제)
 * - clear_wr_link1: wr_link1 필드 초기화 여부 (true/false, 기본 false)
 * - clear_wr_link2: wr_link2 필드 초기화 여부 (true/false, 기본 false)
 * - clear_wr_img: wr_img 필드 초기화 여부 (true/false, 기본 false)
 * - clear_wr_url: wr_url 필드 초기화 여부 (true/false, 기본 false)
 *
 * 사용 예시:
 * - wr_img: 썸네일 이미지 (대부분의 게시판에서 사용)
 * - wr_link1, wr_link2: 각 게시판의 커스텀 용도
 */

header('Content-Type: application/json; charset=utf-8');

include_once('./_common.php');

// POST 변수 초기화
$wr_id = isset($_POST['wr_id']) ? intval($_POST['wr_id']) : 0;
$fileName = isset($_POST['fileName']) ? urldecode(trim($_POST['fileName'])) : '';
$bf_no = isset($_POST['bf_no']) ? intval($_POST['bf_no']) : 0;
$bf_content = isset($_POST['bf_content']) ? intval($_POST['bf_content']) : null; // bf_content 조건 (옵션)
$bo_table = isset($_POST['bo_table']) ? trim($_POST['bo_table']) : '';
$clear_wr_link1 = isset($_POST['clear_wr_link1']) ? (bool)$_POST['clear_wr_link1'] : false;
$clear_wr_link2 = isset($_POST['clear_wr_link2']) ? (bool)$_POST['clear_wr_link2'] : false;
$clear_wr_img = isset($_POST['clear_wr_img']) ? (bool)$_POST['clear_wr_img'] : false;
$clear_wr_url = isset($_POST['clear_wr_url']) ? (bool)$_POST['clear_wr_url'] : false;

// 필수 파라미터 체크
if (empty($wr_id) || empty($fileName) || empty($bo_table)) {
    echo json_encode(['result' => 'error', 'message' => '필수 파라미터가 누락되었습니다.']);
    exit;
}

// 게시판 존재 여부 확인
$board = sql_fetch("SELECT * FROM " . G5_TABLE_PREFIX . "board WHERE bo_table = '{$bo_table}'");
if (!$board) {
    echo json_encode(['result' => 'error', 'message' => '존재하지 않는 게시판입니다.']);
    exit;
}

// 권한 체크 (작성자 또는 관리자만 삭제 가능)
$write_table = G5_TABLE_PREFIX . 'write_' . $bo_table;
$write = sql_fetch("SELECT * FROM {$write_table} WHERE wr_id = '{$wr_id}'");

if (!$write) {
    echo json_encode(['result' => 'error', 'message' => '존재하지 않는 게시글입니다.']);
    exit;
}

// 권한 체크
$is_owner = ($member['mb_id'] && $write['mb_id'] === $member['mb_id']);
$is_admin_board = (!empty($is_admin) || is_board_admin($member['mb_id'], $board));

if (!$is_owner && !$is_admin_board) {
    echo json_encode(['result' => 'error', 'message' => '파일을 삭제할 권한이 없습니다.']);
    exit;
}

// $fileName이 전체 URL이라면 basename()으로 고유 파일명만 취함
$fileName = basename($fileName);
$file_path = G5_DATA_PATH . '/file/' . $bo_table . '/' . $fileName;

// 1. 실제 파일 삭제
if (file_exists($file_path)) {
    if (!@unlink($file_path)) {
        echo json_encode(['result' => 'error', 'message' => '파일 삭제에 실패했습니다.']);
        exit;
    }
}

// 2. wr_link1 필드 초기화 (요청 시)
// 예: store_gallery의 썸네일, 다른 게시판의 커스텀 용도
if ($clear_wr_link1) {
    $sql = "UPDATE {$write_table} SET wr_link1 = '' WHERE wr_id = '{$wr_id}'";
    sql_query($sql);
}

// 2-1. wr_link2 필드 초기화 (요청 시)
// 예: store_gallery의 다운로드 파일, 다른 게시판의 커스텀 용도
if ($clear_wr_link2) {
    $sql = "UPDATE {$write_table} SET wr_link2 = '' WHERE wr_id = '{$wr_id}'";
    sql_query($sql);
}

// 2-2. wr_img 필드 초기화 (요청 시)
// 예: timeline, store_gallery의 썸네일
if ($clear_wr_img) {
    // wr_img 필드 존재 여부 확인
    $check_columns = sql_query("SHOW COLUMNS FROM {$write_table} LIKE 'wr_img'");
    if (sql_num_rows($check_columns) > 0) {
        $sql = "UPDATE {$write_table} SET wr_img = '' WHERE wr_id = '{$wr_id}'";
        sql_query($sql);
    }
}

// 2-3. wr_url 필드 초기화 (요청 시)
// 예: myroom_up의 배경 이미지
if ($clear_wr_url) {
    $sql = "UPDATE {$write_table} SET wr_url = '' WHERE wr_id = '{$wr_id}'";
    sql_query($sql);
}

// 3. board_file 테이블에서 해당 레코드 삭제
$file_table = G5_TABLE_PREFIX . 'board_file';
$sql = "DELETE FROM {$file_table}
        WHERE bo_table = '{$bo_table}'
          AND wr_id = '{$wr_id}'
          AND bf_file = '" . sql_real_escape_string($fileName) . "'";

// bf_content 조건 추가 (옵션)
if ($bf_content !== null) {
    $sql .= " AND bf_content = " . intval($bf_content);
}

sql_query($sql);

// 4. bf_content 재정렬 (0부터 연속) - RA0 Edition v1.0.3
// bf_content >= 0인 파일만 대상 (썸네일 제외)
$reorder_sql = "SELECT bf_no, bf_content FROM {$file_table}
                WHERE bo_table = '{$bo_table}'
                  AND wr_id = '{$wr_id}'
                  AND bf_content >= 0
                ORDER BY CAST(bf_content AS SIGNED) ASC";
$reorder_result = sql_query($reorder_sql);

$new_index = 0;
while ($row = sql_fetch_array($reorder_result)) {
    // bf_content를 0부터 순차적으로 재정렬
    if ((int)$row['bf_content'] !== $new_index) {
        $update_sql = "UPDATE {$file_table}
                       SET bf_content = '{$new_index}'
                       WHERE bo_table = '{$bo_table}'
                         AND wr_id = '{$wr_id}'
                         AND bf_no = '{$row['bf_no']}'";
        sql_query($update_sql);
    }
    $new_index++;
}

// 5. 남은 파일 목록 반환
$files = array();
$result = sql_query("SELECT * FROM {$file_table}
                     WHERE bo_table = '{$bo_table}' AND wr_id = '{$wr_id}'
                     AND bf_content >= 0
                     ORDER BY CAST(bf_content AS SIGNED) ASC");

while ($row = sql_fetch_array($result)) {
    $files[] = [
        'bf_no' => $row['bf_no'],
        'bf_content' => $row['bf_content'],
        'bf_file' => $row['bf_file'],
        'bf_source' => $row['bf_source'],
        'url' => G5_DATA_URL . '/file/' . $bo_table . '/' . $row['bf_file']
    ];
}

// 게시글의 파일 개수 업데이트
$file_count = count($files);
sql_query("UPDATE {$write_table} SET wr_file = '{$file_count}' WHERE wr_id = '{$wr_id}'");

// 관련 필드 정보 확인하여 반환 (wr_link1, wr_link2, wr_img)
$wr_link1 = '';
$wr_link2 = '';
$wr_img = '';
$wr_url = '';

// wr_img 컬럼 존재 여부 확인
$has_wr_img = false;
$check_columns = sql_query("SHOW COLUMNS FROM {$write_table} LIKE 'wr_img'");
if (sql_num_rows($check_columns) > 0) {
    $has_wr_img = true;
}

// 필드 조회
$select_fields = "wr_link1, wr_link2, wr_url";
if ($has_wr_img) {
    $select_fields .= ", wr_img";
}
$link_result = sql_fetch("SELECT {$select_fields} FROM {$write_table} WHERE wr_id = '{$wr_id}'");

if ($link_result) {
    if (!empty($link_result['wr_link1'])) {
        $wr_link1 = $link_result['wr_link1'];
    }
    if (!empty($link_result['wr_link2'])) {
        $wr_link2 = $link_result['wr_link2'];
    }
    if (!empty($link_result['wr_url'])) {
        $wr_url = $link_result['wr_url'];
    }
    if ($has_wr_img && !empty($link_result['wr_img'])) {
        $wr_img = $link_result['wr_img'];
    }
}

echo json_encode([
    'result' => 'success',
    'message' => '파일이 삭제되었습니다.',
    'files' => $files,
    'file_count' => $file_count,
    'wr_link1' => $wr_link1,
    'wr_link2' => $wr_link2,
    'wr_url' => $wr_url,
    'wr_img' => $wr_img,
    'thumbnail' => $wr_img  // 호환성을 위해 thumbnail도 함께 반환
]);
exit;
?>
