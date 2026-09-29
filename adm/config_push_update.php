<?php
/**
 * RA0 Edition 푸시 알림 설정 저장 처리
 */
$sub_menu = "100900";
include_once('./_common.php');

if (!$is_admin) {
    alert('관리자만 접근 가능합니다.');
}

// CSRF 토큰 검증
check_token();

$action = $_POST['action'] ?? '';

// Push 설정 디렉토리 확인/생성
$push_dir = G5_DATA_PATH . '/push';
if (!is_dir($push_dir)) {
    @mkdir($push_dir, 0755, true);

    // .htaccess로 웹 접근 차단
    $htaccess = $push_dir . '/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Order deny,allow\nDeny from all\n");
    }
}

$config_file = $push_dir . '/push_config.php';

// 기존 설정 로드
$push_config = [];
if (file_exists($config_file)) {
    include($config_file);
}

switch ($action) {
    case 'generate_keys':
    case 'regenerate_keys':
        // VAPID 키 생성
        require_once(G5_PLUGIN_PATH . '/WebPush/VAPID.php');

        try {
            $keys = VAPID::generateKeys();

            $push_config['public_key']      = $keys['publicKey'];
            $push_config['private_key_pem'] = $keys['privateKeyPem'];

            // 키 재생성 시 기존 설정 유지, 없으면 기본값
            if (!isset($push_config['enabled'])) {
                $push_config['enabled'] = false;
            }
            if (!isset($push_config['subject'])) {
                $push_config['subject'] = 'mailto:' . ($config['cf_admin_email'] ?? 'admin@example.com');
            }
            if (!isset($push_config['ttl'])) {
                $push_config['ttl'] = 86400;
            }
            if (!isset($push_config['urgency'])) {
                $push_config['urgency'] = 'normal';
            }
            if (!isset($push_config['types'])) {
                $push_config['types'] = [
                    'comment' => true,
                    'reply'   => true,
                    'mention' => true,
                    'message' => true,
                    'like'    => false,
                    'gift'    => true,
                    'trade'   => true,
                ];
            }

            save_push_config($config_file, $push_config);

            // 키 재생성 시 기존 구독 전부 비활성화
            if ($action === 'regenerate_keys') {
                include_once(G5_LIB_PATH . '/notification.lib.php');
                check_push_subscription_table();
                sql_query("UPDATE {$g5['push_subscriptions_table']} SET is_active = 0");
            }

            goto_url(G5_ADMIN_URL . '/config_push.php?msg=generated');
        } catch (Exception $e) {
            alert('키 생성 실패: ' . $e->getMessage());
        }
        break;

    case 'save_config':
        // 설정 저장
        $push_config['enabled'] = !empty($_POST['enabled']);
        $push_config['subject'] = 'mailto:' . ($config['cf_admin_email'] ?? 'admin@example.com');
        $push_config['ttl']     = max(0, (int)($_POST['ttl'] ?? 86400));
        $push_config['urgency'] = in_array($_POST['urgency'] ?? '', ['very-low', 'low', 'normal', 'high'])
            ? $_POST['urgency'] : 'normal';

        // 알림 유형별 설정
        $all_types = ['comment', 'reply', 'mention', 'message', 'like', 'gift', 'trade'];
        $push_config['types'] = [];
        foreach ($all_types as $type) {
            $push_config['types'][$type] = !empty($_POST['types'][$type]);
        }

        save_push_config($config_file, $push_config);
        goto_url(G5_ADMIN_URL . '/config_push.php?msg=saved');
        break;

    default:
        alert('잘못된 요청입니다.');
}

/**
 * Push 설정을 PHP 파일로 저장
 */
function save_push_config($file, $cfg) {
    $content = "<?php\n";
    $content .= "if (!defined('_GNUBOARD_')) exit;\n";
    $content .= "// Web Push 설정 (자동 생성 - 직접 수정하지 마세요)\n";
    $content .= "// 생성일: " . date('Y-m-d H:i:s') . "\n";
    $content .= "\$push_config = " . var_export($cfg, true) . ";\n";

    if (file_put_contents($file, $content) === false) {
        alert('설정 파일 저장 실패: ' . $file);
    }
}
