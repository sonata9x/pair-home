<?php
/**
 * Push 알림 테스트 발송 (관리자용 진단 도구)
 *
 * 각 단계별 결과를 JSON으로 반환하여 브라우저에서 진단 가능
 */
$sub_menu = "100900";
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

if (!$is_admin) {
    die(json_encode(['error' => '관리자만 접근 가능합니다.']));
}

include_once(G5_LIB_PATH . '/notification.lib.php');

$steps = [];
$success = true;

// ── Step 1: Push 설정 파일 확인 ──
$config_file = G5_DATA_PATH . '/push/push_config.php';
if (!file_exists($config_file)) {
    $steps[] = ['step' => '설정 파일', 'status' => 'FAIL', 'detail' => '파일 없음: ' . $config_file];
    die(json_encode(['success' => false, 'steps' => $steps]));
}
$steps[] = ['step' => '설정 파일', 'status' => 'OK', 'detail' => $config_file];

// ── Step 2: 설정 로드 ──
$push_config = [];
include($config_file);

if (empty($push_config)) {
    $steps[] = ['step' => '설정 로드', 'status' => 'FAIL', 'detail' => '$push_config 비어있음'];
    die(json_encode(['success' => false, 'steps' => $steps]));
}

$steps[] = ['step' => '설정 로드', 'status' => 'OK', 'detail' => json_encode([
    'enabled' => $push_config['enabled'] ?? false,
    'has_public_key' => !empty($push_config['public_key']),
    'has_private_key' => !empty($push_config['private_key_pem']),
    'public_key_len' => strlen($push_config['public_key'] ?? ''),
    'subject' => $push_config['subject'] ?? '(없음)',
    'ttl' => $push_config['ttl'] ?? '(없음)',
    'urgency' => $push_config['urgency'] ?? '(없음)',
], JSON_UNESCAPED_UNICODE)];

if (empty($push_config['enabled'])) {
    $steps[] = ['step' => 'Push 활성화', 'status' => 'FAIL', 'detail' => 'Push가 비활성화 상태'];
    die(json_encode(['success' => false, 'steps' => $steps]));
}
$steps[] = ['step' => 'Push 활성화', 'status' => 'OK', 'detail' => ''];

// ── Step 3: 관리자 구독 조회 ──
check_push_subscription_table();
$subscriptions = get_push_subscriptions($member['mb_id']);

if (empty($subscriptions)) {
    $steps[] = ['step' => '구독 조회', 'status' => 'FAIL', 'detail' => '관리자(' . $member['mb_id'] . ')의 활성 구독이 없습니다. 먼저 알림 페이지에서 브라우저 알림을 켜주세요.'];
    die(json_encode(['success' => false, 'steps' => $steps]));
}

$sub_info = [];
foreach ($subscriptions as $sub) {
    $sub_info[] = [
        'ps_id' => $sub['ps_id'],
        'endpoint_start' => substr($sub['endpoint'], 0, 60) . '...',
        'p256dh_len' => strlen($sub['p256dh_key']),
        'auth_len' => strlen($sub['auth_key']),
        'user_agent' => mb_substr($sub['user_agent'], 0, 50, 'UTF-8'),
    ];
}
$steps[] = ['step' => '구독 조회', 'status' => 'OK', 'detail' => count($subscriptions) . '개 구독 발견 | ' . json_encode($sub_info, JSON_UNESCAPED_UNICODE)];

// ── Step 4: WebPush 라이브러리 로드 ──
$webpush_file = G5_PLUGIN_PATH . '/WebPush/WebPush.php';
if (!file_exists($webpush_file)) {
    $steps[] = ['step' => '라이브러리', 'status' => 'FAIL', 'detail' => '파일 없음: ' . $webpush_file];
    die(json_encode(['success' => false, 'steps' => $steps]));
}
require_once($webpush_file);
$steps[] = ['step' => '라이브러리', 'status' => 'OK', 'detail' => 'WebPush, VAPID, Encryption, Utils 로드됨'];

// ── Step 5: 키 검증 ──
try {
    $pubKeyBytes = WebPushUtils::base64urlDecode($push_config['public_key']);
    $pubKeyLen = strlen($pubKeyBytes);
    $pubKeyFirstByte = bin2hex($pubKeyBytes[0] ?? '');

    $steps[] = ['step' => '공개키 검증', 'status' => ($pubKeyLen === 65 && $pubKeyFirstByte === '04') ? 'OK' : 'WARN',
        'detail' => "길이={$pubKeyLen}바이트 (65 필요), 첫바이트=0x{$pubKeyFirstByte} (04 필요)"];

    $privKey = openssl_pkey_get_private($push_config['private_key_pem']);
    if (!$privKey) {
        $steps[] = ['step' => '개인키 검증', 'status' => 'FAIL', 'detail' => 'PEM 로드 실패: ' . openssl_error_string()];
        die(json_encode(['success' => false, 'steps' => $steps]));
    }
    $privDetails = openssl_pkey_get_details($privKey);
    $steps[] = ['step' => '개인키 검증', 'status' => 'OK', 'detail' => '타입=' . ($privDetails['type'] == OPENSSL_KEYTYPE_EC ? 'EC' : $privDetails['type']) . ', 커브=' . ($privDetails['ec']['curve_name'] ?? '알수없음')];
} catch (\Exception $e) {
    $steps[] = ['step' => '키 검증', 'status' => 'FAIL', 'detail' => $e->getMessage()];
    die(json_encode(['success' => false, 'steps' => $steps]));
}

// ── Step 6: 첫 번째 구독의 키 검증 ──
$testSub = $subscriptions[0];
try {
    $uaPublic = WebPushUtils::base64urlDecode($testSub['p256dh_key']);
    $uaAuth = WebPushUtils::base64urlDecode($testSub['auth_key']);

    $steps[] = ['step' => '구독 키 검증', 'status' => 'OK',
        'detail' => "p256dh: " . strlen($uaPublic) . "바이트 (65 필요), 첫바이트=0x" . bin2hex($uaPublic[0] ?? '') . " | auth: " . strlen($uaAuth) . "바이트 (16 필요)"];

    if (strlen($uaPublic) !== 65) {
        $steps[] = ['step' => '구독 키 길이', 'status' => 'FAIL', 'detail' => 'p256dh 키 길이가 65바이트가 아님: ' . strlen($uaPublic)];
    }
    if (strlen($uaAuth) !== 16) {
        $steps[] = ['step' => '구독 auth 길이', 'status' => 'FAIL', 'detail' => 'auth 토큰 길이가 16바이트가 아님: ' . strlen($uaAuth)];
    }
} catch (\Exception $e) {
    $steps[] = ['step' => '구독 키 검증', 'status' => 'FAIL', 'detail' => $e->getMessage()];
}

// ── Step 7: 암호화 테스트 ──
$testPayload = json_encode([
    'title' => '테스트 알림',
    'body'  => '이것은 Push 알림 테스트입니다.',
    'icon'  => G5_URL . '/img/logo.png',
    'url'   => G5_URL . '/',
    'type'  => 'test',
], JSON_UNESCAPED_UNICODE);

try {
    $encrypted = WebPushEncryption::encrypt($testPayload, $testSub['p256dh_key'], $testSub['auth_key']);
    $steps[] = ['step' => '암호화', 'status' => 'OK', 'detail' => 'body=' . strlen($encrypted['body']) . '바이트, encoding=' . $encrypted['headers']['Content-Encoding']];
} catch (\Exception $e) {
    $steps[] = ['step' => '암호화', 'status' => 'FAIL', 'detail' => $e->getMessage()];
    die(json_encode(['success' => false, 'steps' => $steps]));
} catch (\Error $e) {
    $steps[] = ['step' => '암호화', 'status' => 'FAIL', 'detail' => '치명 오류: ' . $e->getMessage()];
    die(json_encode(['success' => false, 'steps' => $steps]));
}

// ── Step 8: VAPID 헤더 생성 테스트 ──
try {
    $endpoint = $testSub['endpoint'];
    $parsed = parse_url($endpoint);
    $audience = $parsed['scheme'] . '://' . $parsed['host'];

    $vapidHeaders = VAPID::getHeaders(
        $audience,
        $push_config['subject'] ?? 'mailto:admin@example.com',
        $push_config['public_key'],
        $push_config['private_key_pem']
    );
    $authHeader = $vapidHeaders['Authorization'];
    $steps[] = ['step' => 'VAPID 헤더', 'status' => 'OK', 'detail' => 'audience=' . $audience . ' | Authorization 길이=' . strlen($authHeader)];
} catch (\Exception $e) {
    $steps[] = ['step' => 'VAPID 헤더', 'status' => 'FAIL', 'detail' => $e->getMessage()];
    die(json_encode(['success' => false, 'steps' => $steps]));
}

// ── Step 9: 실제 발송 ──
try {
    $webpush = new WebPush([
        'public_key'      => $push_config['public_key'],
        'private_key_pem' => $push_config['private_key_pem'],
        'subject'         => $push_config['subject'] ?? 'mailto:admin@example.com',
        'ttl'             => $push_config['ttl'] ?? 86400,
        'urgency'         => $push_config['urgency'] ?? 'normal',
        'timeout'         => 5,  // 테스트는 5초
    ]);

    $result = $webpush->send($testSub, $testPayload);

    $statusText = $result['success'] ? 'OK' : 'FAIL';
    $steps[] = ['step' => '발송', 'status' => $statusText,
        'detail' => 'HTTP ' . $result['statusCode'] . ' | success=' . ($result['success'] ? 'Y' : 'N') . ' | reason=' . ($result['reason'] ?? '')];

    if (!$result['success']) {
        $success = false;
        // 추가 진단
        if ($result['statusCode'] === 0) {
            $steps[] = ['step' => '진단', 'status' => 'INFO', 'detail' => 'HTTP 0 = cURL 연결 실패 (DNS/네트워크/타임아웃/SSL)'];
        } elseif ($result['statusCode'] === 401 || $result['statusCode'] === 403) {
            $steps[] = ['step' => '진단', 'status' => 'INFO', 'detail' => 'VAPID 인증 실패 — 키가 잘못되었거나 구독과 불일치'];
        } elseif ($result['statusCode'] === 404 || $result['statusCode'] === 410) {
            $steps[] = ['step' => '진단', 'status' => 'INFO', 'detail' => '구독 만료 — 브라우저에서 알림을 다시 켜주세요'];
        } elseif ($result['statusCode'] === 413) {
            $steps[] = ['step' => '진단', 'status' => 'INFO', 'detail' => '페이로드 크기 초과'];
        }
    }
} catch (\Exception $e) {
    $steps[] = ['step' => '발송', 'status' => 'FAIL', 'detail' => '예외: ' . $e->getMessage()];
    $success = false;
} catch (\Error $e) {
    $steps[] = ['step' => '발송', 'status' => 'FAIL', 'detail' => '치명 오류: ' . $e->getMessage()];
    $success = false;
}

echo json_encode(['success' => $success, 'steps' => $steps], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
