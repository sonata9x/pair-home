<?php
include_once('./_common.php');

if(!$is_admin) {
    die('{"success": false, "message": "권한이 없습니다."}');
}

$preset_id = (int)$_POST['preset_id'];
if(!$preset_id) {
    die('{"success": false, "message": "프리셋 ID가 없습니다."}');
}

$design_preset_table = G5_TABLE_PREFIX . 'design_preset';
$sql = "SELECT * FROM {$design_preset_table} WHERE dp_id = {$preset_id}";
$result = sql_query($sql);
$preset = sql_fetch_array($result);

if(!$preset) {
    die('{"success": false, "message": "프리셋을 찾을 수 없습니다."}');
}

$settings = json_decode($preset['dp_data'], true);

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'preset' => $preset,
    'settings' => $settings
]);
?>
