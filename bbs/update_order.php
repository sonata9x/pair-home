<?php
/**
 * RA0 Edition - 통합 게시판 순서 변경 처리
 *
 * 모든 게시판에서 공통으로 사용하는 순서 변경 처리 파일
 * wr_order 필드를 사용하여 게시글 순서를 관리합니다.
 */

include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

$bo_table = isset($_POST['bo_table']) ? trim($_POST['bo_table']) : '';
$order = isset($_POST['order']) ? $_POST['order'] : array();
$order_data = isset($_POST['order_data']) ? $_POST['order_data'] : array();

// 필수 파라미터 체크
if (!$bo_table) {
    echo json_encode(['success' => false, 'message' => 'bo_table 파라미터가 누락되었습니다.']);
    exit;
}

// 페이지 오프셋을 고려한 order_data가 있으면 우선 사용
if (is_array($order_data) && count($order_data) > 0) {
    // order_data 사용 (페이지네이션 고려)
} else if (is_array($order) && count($order) > 0) {
    // 기존 order 사용 (하위 호환성)
} else {
    echo json_encode(['success' => false, 'message' => '정렬 데이터가 올바르지 않습니다.']);
    exit;
}

// 게시판 정보 확인
$board = sql_fetch("SELECT bo_admin FROM {$g5['board_table']} WHERE bo_table = '{$bo_table}'");

if (!$board) {
    echo json_encode(['success' => false, 'message' => '게시판이 존재하지 않습니다.']);
    exit;
}

// 권한 체크 (관리자 또는 게시판 관리자만 가능)
$is_board_admin = false;
if ($is_member && $board['bo_admin']) {
    $admin_array = explode(',', $board['bo_admin']);
    foreach ($admin_array as $admin) {
        if (trim($admin) == $member['mb_id']) {
            $is_board_admin = true;
            break;
        }
    }
}

if (!$is_admin && !$is_board_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다. 관리자만 정렬을 변경할 수 있습니다.']);
    exit;
}

// 게시판 테이블 확인
$write_table = $g5['write_prefix'] . $bo_table;

if (!sql_query("DESC {$write_table}", false)) {
    echo json_encode(['success' => false, 'message' => '게시판 테이블이 존재하지 않습니다.']);
    exit;
}

// wr_order 필드 자동 추가 (없으면)
$temp = sql_fetch("SHOW COLUMNS FROM {$write_table} LIKE 'wr_order'");
if (!$temp) {
    sql_query("ALTER TABLE {$write_table} ADD wr_order INT(11) NOT NULL DEFAULT '0', ADD KEY wr_order (wr_order)", false);
}

// 정렬 순서 업데이트
$updated = 0;
$failed = 0;

// 페이지네이션을 고려한 order_data가 있으면 사용
if (is_array($order_data) && count($order_data) > 0) {
    foreach ($order_data as $item) {
        $wr_id = (int)$item['wr_id'];
        $wr_order = (int)$item['wr_order'];

        if ($wr_id > 0) {
            $sql = "UPDATE {$write_table} SET wr_order = '{$wr_order}' WHERE wr_id = '{$wr_id}'";
            if (sql_query($sql)) {
                $updated++;
            } else {
                $failed++;
            }
        }
    }
} else {
    // 기존 방식 (하위 호환성)
    foreach ($order as $position => $wr_id) {
        $wr_id = (int)$wr_id;
        $position = (int)$position;

        if ($wr_id > 0) {
            $sql = "UPDATE {$write_table} SET wr_order = '{$position}' WHERE wr_id = '{$wr_id}'";
            if (sql_query($sql)) {
                $updated++;
            } else {
                $failed++;
            }
        }
    }
}

echo json_encode([
    'success' => true,
    'message' => "{$updated}개 게시글의 순서가 변경되었습니다.",
    'updated' => $updated,
    'failed' => $failed,
    'bo_table' => $bo_table
]);
?>
