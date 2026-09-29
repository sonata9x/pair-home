<?php
/**
 * 환경설정 대표 이미지 삭제 처리
 */

ob_start();
error_reporting(0);
ini_set('display_errors', 0);

include_once('./_common.php');

ob_clean();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

function json_response($success, $message = '') {
    echo json_encode([
        'success' => $success,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// POST 요청 체크
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, '잘못된 요청 방식입니다.');
}

// 관리자 권한 체크
if ($is_admin != 'super') {
    json_response(false, '최고관리자만 접근 가능합니다.');
}

$type = isset($_POST['type']) ? trim($_POST['type']) : '';

if ($type !== 'cf_image') {
    json_response(false, '허용되지 않은 타입입니다.');
}

try {
    // 파일 경로 (고정)
    $file_path = G5_DATA_PATH . '/design/cf_image.webp';

    // 파일 삭제
    if (file_exists($file_path)) {
        if (!unlink($file_path)) {
            json_response(false, '파일 삭제에 실패했습니다.');
        }
    }

    // DB에서 URL 삭제
    $sql = "UPDATE {$g5['config_table']} SET cf_image = ''";
    sql_query($sql);

    json_response(true, '대표 이미지가 삭제되었습니다.');

} catch (Exception $e) {
    json_response(false, '삭제 처리 중 오류가 발생했습니다.');
}
?>
