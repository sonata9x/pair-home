<?php
include_once('./_common.php');

// JSON 헤더 설정
header('Content-Type: application/json; charset=utf-8');

// 관리자 권한 체크
if(!$is_admin) {
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

// POST 데이터 확인
if(!isset($_POST['preset_id']) || empty($_POST['preset_id'])) {
    echo json_encode(['success' => false, 'message' => '프리셋 ID가 없습니다.']);
    exit;
}

$preset_id = (int)$_POST['preset_id'];

// 테이블명 정의
$design_preset_table = G5_TABLE_PREFIX . 'config_design_preset';

// 프리셋 데이터 조회
$sql = "SELECT dp_data FROM {$design_preset_table} WHERE dp_id = '{$preset_id}'";
$result = sql_fetch($sql);

if($result && !empty($result['dp_data'])) {
    $settings = json_decode($result['dp_data'], true);
    
    // JSON 디코딩 오류 체크
    if(json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'JSON 데이터 오류: ' . json_last_error_msg()]);
        exit;
    }
    
    echo json_encode(['success' => true, 'settings' => $settings]);
} else {
    echo json_encode(['success' => false, 'message' => '프리셋을 찾을 수 없습니다.']);
}
exit;
?>
