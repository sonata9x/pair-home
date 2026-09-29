<?php
include_once('./_common.php');

if(!$is_admin) {
    die('{"success": false, "message": "권한이 없습니다."}');
}

$input = json_decode(file_get_contents('php://input'), true);

$name = trim($input['name']);
$description = trim($input['description']);
$settings = $input['settings'];

if(!$name || !$settings) {
    die('{"success": false, "message": "필수 정보가 누락되었습니다."}');
}

$design_preset_table = G5_TABLE_PREFIX . 'config_design_preset';
$data_json = json_encode($settings);

// SQL Injection 방지
$name = sql_real_escape_string($name);
$description = sql_real_escape_string($description);
$data_json = sql_real_escape_string($data_json);

$sql = "INSERT INTO {$design_preset_table}
        (dp_name, dp_description, dp_data, dp_type)
        VALUES ('{$name}', '{$description}', '{$data_json}', 'user')";

if(sql_query($sql)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '저장에 실패했습니다.']);
}
?>
