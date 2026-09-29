<?php
/**
 * 댓글 첨부파일 목록 가져오기
 *
 * 파라미터:
 * - bo_table: 게시판 테이블명
 * - wr_id: 댓글 ID
 */

header('Content-Type: application/json; charset=utf-8');

include_once('./_common.php');

// POST 변수 초기화
$bo_table = isset($_POST['bo_table']) ? trim($_POST['bo_table']) : '';
$wr_id = isset($_POST['wr_id']) ? intval($_POST['wr_id']) : 0;

// 필수 파라미터 체크
if (empty($bo_table) || empty($wr_id)) {
    echo json_encode(['result' => 'error', 'message' => '필수 파라미터가 누락되었습니다.']);
    exit;
}

// 게시판 존재 여부 확인
$board = sql_fetch("SELECT * FROM {$g5['board_table']} WHERE bo_table = '{$bo_table}'");
if (!$board) {
    echo json_encode(['result' => 'error', 'message' => '존재하지 않는 게시판입니다.']);
    exit;
}

// 댓글 존재 여부 확인
$write_table = $g5['write_prefix'] . $bo_table;
$comment = sql_fetch("SELECT * FROM {$write_table} WHERE wr_id = '{$wr_id}' AND wr_is_comment = '1'");

if (!$comment) {
    echo json_encode(['result' => 'error', 'message' => '존재하지 않는 댓글입니다.']);
    exit;
}

// 권한 체크 (작성자 또는 관리자만 조회 가능)
$is_owner = ($member['mb_id'] && $comment['mb_id'] === $member['mb_id']);
$is_admin_board = ($is_admin === 'super' || is_board_admin($member['mb_id'], $board));

if (!$is_owner && !$is_admin_board) {
    echo json_encode(['result' => 'error', 'message' => '파일을 조회할 권한이 없습니다.']);
    exit;
}

// 첨부파일 목록 조회 (bf_content = -1: 댓글 첨부파일)
$files = array();
$sql = "SELECT * FROM {$g5['board_file_table']}
        WHERE bo_table = '{$bo_table}'
        AND wr_id = '{$wr_id}'
        AND bf_content = '-1'
        ORDER BY bf_no";
$result = sql_query($sql);

while ($row = sql_fetch_array($result)) {
    $files[] = array(
        'bf_no' => $row['bf_no'],
        'bf_file' => $row['bf_file'],
        'bf_source' => $row['bf_source'],
        'bf_filesize' => $row['bf_filesize'],
        'url' => G5_DATA_URL . '/file/' . $bo_table . '/' . $row['bf_file']
    );
}

echo json_encode([
    'result' => 'success',
    'files' => $files,
    'file_count' => count($files)
]);
exit;
?>
