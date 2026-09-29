<?php
// 모든 출력 버퍼링 시작
ob_start();

// 에러 출력 억제
error_reporting(0);
ini_set('display_errors', 0);

include_once('./_common.php');

// 출력 버퍼 정리
ob_clean();

// JSON 헤더 설정
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// JSON 응답 함수
function json_response($success, $message = '', $data = null) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// 실제 파일 삭제 함수
function delete_design_file($file_url) {
    if (empty($file_url)) {
        return true; // 빈 URL은 삭제할 것이 없음
    }
    
    // URL에서 파일명 추출
    $filename = basename($file_url);
    
    // 실제 파일 경로 구성
    $file_path = G5_DATA_PATH . '/design/' . $filename;
    
    // 파일이 존재하는지 확인
    if (file_exists($file_path)) {
        // 파일 삭제 시도
        if (unlink($file_path)) {
            return true;
        } else {
            return false;
        }
    }
    
    return true; // 파일이 없으면 삭제 성공으로 간주
}

// POST 요청 체크
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, '잘못된 요청 방식입니다.');
}

// 관리자 권한 체크는 _common.php에서 처리됨

// 파라미터 체크
$setting_key = isset($_POST['setting_key']) ? trim($_POST['setting_key']) : '';

if (empty($setting_key)) {
    json_response(false, '설정 키가 없습니다.');
}

// 허용된 설정 키 목록 (보안을 위해)
$allowed_keys = [
    'logo_image_url',
    'bg_image_url',
    'slide_image_1',
    'slide_image_2',
    'slide_image_3',
    'cursor_url',
    'click_sound_url'
];

if (!in_array($setting_key, $allowed_keys)) {
    json_response(false, '허용되지 않은 설정 키입니다.');
}

try {
    // 먼저 현재 저장된 URL 가져오기 (파일 삭제를 위해)
    $current_url = '';
    
    if (function_exists('get_design_config')) {
        $current_url = get_design_config($setting_key);
    } else {
        // 직접 데이터베이스에서 조회
        $config_design_table = G5_TABLE_PREFIX . 'config_design';
        $row = sql_fetch("SELECT cd_value FROM {$config_design_table} WHERE cd_key = '{$setting_key}'");
        if ($row) {
            $current_url = $row['cd_value'];
        }
    }
    
    // 실제 파일 삭제
    $file_deleted = delete_design_file($current_url);
    
    if (!$file_deleted) {
        json_response(false, '파일 삭제에 실패했습니다.');
    }
    
    // 데이터베이스에서 URL 삭제
    if (function_exists('set_design_config')) {
        $result = set_design_config($setting_key, '');
    } else {
        // 직접 데이터베이스 처리
        $config_design_table = G5_TABLE_PREFIX . 'config_design';
        
        // 기존 설정이 있는지 확인
        $existing = sql_fetch("SELECT cd_id FROM {$config_design_table} WHERE cd_key = '{$setting_key}'");
        
        if ($existing) {
            // 업데이트
            $result = sql_query("UPDATE {$config_design_table} SET cd_value = '' WHERE cd_key = '{$setting_key}'");
        } else {
            // 새로 삽입 (빈 값으로)
            $result = sql_query("INSERT INTO {$config_design_table} (cd_key, cd_value, cd_group, cd_name, cd_type) 
                               VALUES ('{$setting_key}', '', 'design', '{$setting_key}', 'text')");
        }
    }
    
    if ($result) {
        $deleted_filename = basename($current_url);
        json_response(true, "파일과 URL이 성공적으로 삭제되었습니다.\n삭제된 파일: {$deleted_filename}");
    } else {
        json_response(false, '데이터베이스 업데이트에 실패했습니다.');
    }
    
} catch (Exception $e) {
    // 에러 로그 기록 (선택사항)
    if (function_exists('write_log')) {
        write_log("Design image delete error: " . $e->getMessage());
    }
    
    json_response(false, '삭제 처리 중 오류가 발생했습니다: ' . $e->getMessage());
}
?>
