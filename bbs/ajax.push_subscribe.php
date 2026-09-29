<?php
/**
 * Push 구독 등록/해제 AJAX 엔드포인트
 *
 * POST: 구독 등록/갱신 (action=subscribe)
 * POST: 구독 해제 (action=unsubscribe)
 * GET:  구독 상태 조회 (action=status)
 */
include_once('../_common.php');

header('Content-Type: application/json');

// 로그인 체크
if (!$member['mb_id']) {
    die(json_encode(['success' => false, 'error' => '로그인이 필요합니다.']));
}

// CSRF 토큰 검증
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (function_exists('check_ajax_token')) {
        check_ajax_token();
    }
}

// 알림 라이브러리 로드
include_once(G5_LIB_PATH . '/notification.lib.php');

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'subscribe':
        $endpoint  = trim($_POST['endpoint'] ?? '');
        $authKey   = trim($_POST['auth'] ?? '');
        $p256dhKey = trim($_POST['p256dh'] ?? '');

        // 필수값 검증
        if (empty($endpoint) || empty($authKey) || empty($p256dhKey)) {
            die(json_encode(['success' => false, 'error' => '구독 정보가 부족합니다.']));
        }

        // endpoint 스킴 검증 (https만 허용)
        if (strpos($endpoint, 'https://') !== 0) {
            die(json_encode(['success' => false, 'error' => '유효하지 않은 endpoint입니다.']));
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $result = save_push_subscription(
            $member['mb_id'],
            $endpoint,
            $authKey,
            $p256dhKey,
            mb_substr($userAgent, 0, 255, 'UTF-8')
        );

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => '구독 저장에 실패했습니다.']);
        }
        break;

    case 'unsubscribe':
        $endpoint = trim($_POST['endpoint'] ?? '');

        if (empty($endpoint)) {
            die(json_encode(['success' => false, 'error' => 'endpoint가 필요합니다.']));
        }

        $result = deactivate_push_subscription($member['mb_id'], $endpoint);

        echo json_encode(['success' => true]);
        break;

    case 'status':
        $subscribed = has_push_subscription($member['mb_id']);

        echo json_encode([
            'success'    => true,
            'subscribed' => $subscribed,
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => '잘못된 요청입니다.']);
}
