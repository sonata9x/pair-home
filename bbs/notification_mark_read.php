<?php
/**
 * Ajax 전용 알림 읽음 처리 엔드포인트
 *
 * JSON 응답 반환 (리다이렉트 없음)
 */

include_once('./_common.php');
include_once(G5_LIB_PATH.'/notification.lib.php');

header('Content-Type: application/json; charset=utf-8');

// 로그인 체크
if (!$member['mb_id']) {
    echo json_encode([
        'success' => false,
        'error' => 'login_required',
        'message' => '로그인이 필요합니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 카테고리별 WHERE 절 생성 헬퍼
function get_category_filter($category) {
    switch ($category) {
        case 'mention':
            return "AND noti_type IN ('reply','comment','mention')";
        case 'notice':
            return "AND noti_type NOT IN ('reply','comment','mention','like','scrap')";
        case 'interest':
            return "AND noti_type IN ('like','scrap')";
        default:
            return '';
    }
}

// 카테고리 파라미터 (없으면 전체)
$category = isset($_GET['category']) ? $_GET['category'] : '';
if ($category && !in_array($category, ['mention', 'notice', 'interest'])) {
    $category = '';
}
$category_filter = get_category_filter($category);
$category_label = $category ? ['mention' => '멘션', 'notice' => '공지', 'interest' => '관심글'][$category] . ' ' : '';

// 읽은 알림 삭제 (전체 또는 탭별)
if (isset($_GET['delete_read']) && $_GET['delete_read'] == '1') {
    $safe_mb_id = sql_real_escape_string($member['mb_id']);
    $result = sql_query("DELETE FROM {$g5['notifications_table']} WHERE mb_id = '{$safe_mb_id}' AND noti_read = 1 {$category_filter}");
    $unread_count = get_notification_count($member['mb_id']);

    echo json_encode([
        'success' => (bool)$result,
        'unread_count' => $unread_count,
        'message' => $category_label . '읽은 알림을 모두 삭제했습니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 읽음/안읽음 토글
if (isset($_GET['toggle_id'])) {
    $toggle_id = (int)$_GET['toggle_id'];
    $safe_mb_id = sql_real_escape_string($member['mb_id']);

    $noti = sql_fetch("SELECT noti_id, noti_read FROM {$g5['notifications_table']} WHERE noti_id = '{$toggle_id}' AND mb_id = '{$safe_mb_id}'");
    if (!$noti) {
        echo json_encode(['success' => false, 'message' => '알림을 찾을 수 없습니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $new_read = $noti['noti_read'] ? 0 : 1;
    $result = sql_query("UPDATE {$g5['notifications_table']} SET noti_read = '{$new_read}' WHERE noti_id = '{$toggle_id}' AND mb_id = '{$safe_mb_id}'");
    $unread_count = get_notification_count($member['mb_id'], true);

    echo json_encode([
        'success' => (bool)$result,
        'unread_count' => $unread_count,
        'is_read' => (bool)$new_read,
        'message' => $new_read ? '읽음 처리했습니다.' : '안읽음 처리했습니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 단일 알림 삭제
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $safe_mb_id = sql_real_escape_string($member['mb_id']);

    // 소유권 + 읽음 확인
    $noti = sql_fetch("SELECT noti_id, noti_read FROM {$g5['notifications_table']} WHERE noti_id = '{$delete_id}' AND mb_id = '{$safe_mb_id}'");
    if (!$noti) {
        echo json_encode(['success' => false, 'message' => '알림을 찾을 수 없습니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (!$noti['noti_read']) {
        echo json_encode(['success' => false, 'message' => '읽지 않은 알림은 삭제할 수 없습니다.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $result = sql_query("DELETE FROM {$g5['notifications_table']} WHERE noti_id = '{$delete_id}' AND mb_id = '{$safe_mb_id}'");
    $unread_count = get_notification_count($member['mb_id']);

    echo json_encode([
        'success' => (bool)$result,
        'unread_count' => $unread_count,
        'message' => '알림을 삭제했습니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 전체 읽음 처리 (전체 또는 탭별)
if (isset($_GET['read_all']) && $_GET['read_all'] == '1') {
    $safe_mb_id = sql_real_escape_string($member['mb_id']);
    if ($category_filter) {
        $result = sql_query("UPDATE {$g5['notifications_table']} SET noti_read = 1 WHERE mb_id = '{$safe_mb_id}' AND noti_read = 0 {$category_filter}");
    } else {
        $result = mark_all_notifications_read($member['mb_id']);
    }
    $unread_count = get_notification_count($member['mb_id']);

    echo json_encode([
        'success' => (bool)$result,
        'unread_count' => $unread_count,
        'message' => $category_label . '알림을 읽음 처리했습니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 단일 알림 읽음 처리
$noti_id = isset($_GET['noti_id']) ? (int)$_GET['noti_id'] : 0;

if (!$noti_id) {
    echo json_encode([
        'success' => false,
        'error' => 'invalid_id',
        'message' => '잘못된 알림 ID입니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 알림 소유권 확인
$notification = sql_fetch("SELECT noti_id FROM {$g5['notifications_table']}
                           WHERE noti_id = '{$noti_id}'
                           AND mb_id = '".sql_real_escape_string($member['mb_id'])."'");

if (!$notification) {
    echo json_encode([
        'success' => false,
        'error' => 'not_found',
        'message' => '알림을 찾을 수 없거나 권한이 없습니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 읽음 처리
$result = mark_notification_read($noti_id, $member['mb_id']);
$unread_count = get_notification_count($member['mb_id']);

echo json_encode([
    'success' => (bool)$result,
    'unread_count' => $unread_count,
    'message' => '알림을 읽음 처리했습니다.'
], JSON_UNESCAPED_UNICODE);
?>
