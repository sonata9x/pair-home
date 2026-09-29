<?php
include_once('../_common.php');

header('Content-Type: application/json');

// 로그인 체크
if (!$member['mb_id']) {
    die(json_encode(array('error' => '로그인이 필요합니다.')));
}

// 알림 라이브러리 로드
include_once(G5_LIB_PATH.'/notification.lib.php');

$noti_id = isset($_POST['noti_id']) ? (int)$_POST['noti_id'] : 0;
$unread  = !empty($_POST['unread']);

if (!$noti_id) {
    die(json_encode(array('error' => '알림 ID가 필요합니다.')));
}

// 알림 읽음/안읽음 처리
if ($unread) {
    $result = mark_notification_unread($noti_id, $member['mb_id']);
} else {
    $result = mark_notification_read($noti_id, $member['mb_id']);
}

if ($result) {
    // 남은 읽지 않은 알림 개수 반환
    $unread_count = get_notification_count($member['mb_id']);
    echo json_encode(array(
        'success'      => true,
        'unread_count' => $unread_count,
        'is_unread'    => $unread ? 1 : 0,
    ));
} else {
    echo json_encode(array('error' => '알림 처리 실패'));
}
?>