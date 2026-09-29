<?php
$sub_menu = "100200";
include_once('./_common.php');

// 관리자 권한 체크
if (!$is_admin) {
    alert('관리자만 접근할 수 있습니다.');
}

header('Content-Type: application/json');

$preset_id = isset($_POST['preset_id']) ? (int)$_POST['preset_id'] : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

if (!$preset_id) {
    echo json_encode(['success' => false, 'message' => '프리셋 ID가 필요합니다.']);
    exit;
}

if (!$name) {
    echo json_encode(['success' => false, 'message' => '프리셋명을 입력해주세요.']);
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
    echo json_encode(['success' => false, 'message' => '시스템 프리셋은 수정할 수 없습니다.']);
    exit;
}

// 프리셋명과 설명 업데이트
$name = sql_real_escape_string($name);
$description = sql_real_escape_string($description);

$update_sql = "UPDATE {$design_preset_table}
               SET dp_name = '{$name}',
                   dp_description = '{$description}'
               WHERE dp_id = '{$preset_id}'";

if (sql_query($update_sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => '데이터베이스 업데이트 실패']);
}
?>
