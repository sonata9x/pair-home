<?php
/**
 * 좋아요 목록 조회 AJAX 핸들러
 * 관리자만 접근 가능
 */
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['result' => 'error', 'message' => '잘못된 요청입니다.']);
    exit;
}

$bo_table = isset($_GET['bo_table']) ? clean_xss_tags($_GET['bo_table']) : '';
$wr_id = isset($_GET['wr_id']) ? (int)$_GET['wr_id'] : 0;

if (!$bo_table || !$wr_id) {
    echo json_encode(['result' => 'error', 'message' => '필수 파라미터가 누락되었습니다.']);
    exit;
}

// 관리자 체크
$is_admin = is_admin($member['mb_id']);
if (!$is_admin) {
    echo json_encode(['result' => 'error', 'message' => '권한이 없습니다.']);
    exit;
}

$board = get_board_db($bo_table, true);
if (!$board['bo_table']) {
    echo json_encode(['result' => 'error', 'message' => '존재하지 않는 게시판입니다.']);
    exit;
}

// 모든 좋아요 사용자 조회 (제한 없이)
$likers = get_post_likers($bo_table, $wr_id, 1000);

$result_likers = array();
foreach ($likers as $liker) {
    $result_likers[] = array(
        'mb_id' => $liker['mb_id'] ?: '',
        'mb_name' => $liker['mb_name'] ?: '익명',
        'mb_signature' => $liker['mb_signature'] ?: '',
        'liked_datetime' => $liker['liked_datetime']
    );
}

echo json_encode([
    'result' => 'success',
    'likers' => $result_likers,
    'total' => count($result_likers)
]);
