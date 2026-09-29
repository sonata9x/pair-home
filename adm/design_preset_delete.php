<?php
$sub_menu = "100200";
include_once('./_common.php');

// 관리자 권한 체크
if (!$is_admin) {
    alert('관리자만 접근할 수 있습니다.');
}

header('Content-Type: application/json');

$preset_id = isset($_POST['preset_id']) ? (int)$_POST['preset_id'] : 0;

if (!$preset_id) {
    echo json_encode(['success' => false, 'message' => '프리셋 ID가 필요합니다.']);
    exit;
}

$design_preset_table = G5_TABLE_PREFIX . 'config_design_preset';

// 시스템 프리셋인지 확인
$check_sql = "SELECT dp_type FROM {$design_preset_table} WHERE dp_id = '{$preset_id}'";
$check_result = sql_fetch($check_sql);

if (!$check_result) {
    echo json_encode(['success' => false, 'message' => '존재하지 않는 프리셋입니다.']);
    exit;
}

if ($check_result['dp_type'] === 'system') {
    echo json_encode(['success' => false, 'message' => '시스템 프리셋은 삭제할 수 없습니다.']);
    exit;
}

// 프리셋 삭제
$delete_sql = "DELETE FROM {$design_preset_table} WHERE dp_id = '{$preset_id}'";

if (sql_query($delete_sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '데이터베이스 삭제 실패']);
}
?>
