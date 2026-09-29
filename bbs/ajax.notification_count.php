<?php
include_once('../_common.php');

header('Content-Type: application/json');

// 로그인 체크
if (!$member['mb_id']) {
    die(json_encode(array('error' => '로그인이 필요합니다.', 'count' => 0)));
}

// 알림 라이브러리 로드
include_once(G5_LIB_PATH.'/notification.lib.php');

// 읽지 않은 알림 개수 반환
$unread_count = get_notification_count($member['mb_id']);

echo json_encode(array(
    'success' => true,
    'count' => $unread_count
));
?>