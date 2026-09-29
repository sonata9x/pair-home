<?php
// 비동기 이메일 발송 스크립트
// CLI에서만 실행 가능
if (php_sapi_name() !== 'cli') {
    exit('CLI only');
}

define('_GNUBOARD_', true);

// 상대 경로로 common.php 포함
$script_dir = dirname(__FILE__);
include_once($script_dir . '/../common.php');
include_once(G5_LIB_PATH . '/notification.lib.php');

// 커맨드라인 인자로 전달받은 데이터 (JSON)
$json_data = $argv[1] ?? '';

if (empty($json_data)) {
    exit('No data provided');
}

$data = json_decode($json_data, true);

if (!$data) {
    exit('Invalid JSON data');
}

// 이메일 발송 (동기적으로 실행되지만 백그라운드 프로세스이므로 문제없음)
send_email_for_notification($data);

exit(0);
?>
