<?php
header('Content-Type: application/json; charset=utf-8');
include_once('../common.php');
include_once(G5_LIB_PATH . '/pair_home.lib.php');

function pair_home_json_response($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') pair_home_json_response(array('error'=>'Method Not Allowed'), 405);
if (!$is_admin) pair_home_json_response(array('error'=>'관리자만 편집할 수 있습니다.'), 403);
$session_token = (string)get_session('ss_pair_home_token');
$request_token = (string)($_POST['token'] ?? '');
if ($session_token === '' || $request_token === '' || !hash_equals($session_token, $request_token)) {
    pair_home_json_response(array('error'=>'편집 세션이 만료되었습니다. 페이지를 새로고침해 주세요.'), 403);
}
$action = (string)($_POST['action'] ?? '');
if ($action === 'upload') {
    $kind = ($_POST['kind'] ?? '') === 'background' ? 'background' : 'asset';
    $result = pair_home_upload_image($_FILES['image'] ?? null, $kind);
    if (!empty($result['error'])) pair_home_json_response($result, 400);
    pair_home_json_response($result);
}
if ($action === 'save') {
    $decoded = json_decode((string)($_POST['layout'] ?? '[]'), true);
    if (!is_array($decoded)) pair_home_json_response(array('error'=>'위젯 배치 데이터가 올바르지 않습니다.'), 400);
    $layout = pair_home_sanitize_layout($decoded);
    $color = strtoupper((string)($_POST['background_color'] ?? '#F2ECE5'));
    if (!preg_match('/^#[0-9A-F]{6}$/', $color)) $color = '#F2ECE5';
    $background_url = pair_home_clean_url($_POST['background_image'] ?? '');
    $allowed_prefix = rtrim(G5_DATA_URL, '/') . '/pair-home/';
    if ($background_url !== '' && strpos($background_url, $allowed_prefix) !== 0) $background_url = '';
    $saved = pair_home_save_setting('pair_home_layout', json_encode($layout, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $saved = pair_home_save_setting('pair_home_background_color', $color) && $saved;
    $saved = pair_home_save_setting('pair_home_background_image', $background_url) && $saved;
    if (!$saved) pair_home_json_response(array('error'=>'설정을 저장하지 못했습니다.'), 500);
    pair_home_json_response(array('ok'=>true, 'layout'=>$layout));
}
pair_home_json_response(array('error'=>'알 수 없는 요청입니다.'), 400);
