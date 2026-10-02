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
if ($action === 'upload_audio') {
    $result = pair_home_upload_audio($_FILES['audio'] ?? null);
    if (!empty($result['error'])) pair_home_json_response($result, 400);
    pair_home_json_response($result);
}
if ($action === 'save_grid') {
    include_once(G5_LIB_PATH . '/pair_home_grid.lib.php');
    $decoded = json_decode(stripslashes((string)($_POST['layout'] ?? '[]')), true);
    if (!is_array($decoded)) pair_home_json_response(array('error'=>'블록 배치 데이터가 올바르지 않습니다.'),400);
    $layout = pair_home_grid_sanitize_layout($decoded);
    $error = pair_home_grid_layout_error($layout);
    if ($error !== '') pair_home_json_response(array('error'=>$error),400);
    $color = strtoupper((string)($_POST['background_color'] ?? '#E8F0F4'));
    if (!preg_match('/^#[0-9A-F]{6}$/',$color)) $color = '#E8F0F4';
    $image = pair_home_clean_url($_POST['background_image'] ?? '');
    if ($image !== '' && strpos($image,rtrim(G5_DATA_URL,'/').'/pair-home/') !== 0) $image = '';
    $representative_color = strtoupper((string)($_POST['representative_color'] ?? '#7FAFD1'));
    if (!preg_match('/^#[0-9A-F]{6}$/',$representative_color)) $representative_color = '#7FAFD1';
    $default_theme = (string)($_POST['default_theme'] ?? 'flat');
    if (!in_array($default_theme,array('flat','line','bold','soft','pixel','glass'),true)) $default_theme = 'flat';
    // A separate document keeps all legacy layouts and theme settings intact.
    $document = array('version'=>4,'backgroundColor'=>$color,'backgroundImage'=>$image,'representativeColor'=>$representative_color,'defaultTheme'=>$default_theme,'widgets'=>$layout);
    if (!pair_home_save_setting('pair_home_grid_document',json_encode($document,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))) {
        pair_home_json_response(array('error'=>'격자 배치를 저장하지 못했습니다.'),500);
    }
    pair_home_json_response(array('ok'=>true,'layout'=>$layout));
}
if ($action === 'save') {
    // common.php의 공통 입력 필터가 JSON의 따옴표와 역슬래시도 이스케이프하므로
    // JSON 본문만 한 번 복원한 뒤 구조를 검증한다.
    $layout_json = stripslashes((string)($_POST['layout'] ?? '[]'));
    $decoded = json_decode($layout_json, true);
    if (!is_array($decoded)) {
        pair_home_json_response(array('error'=>'위젯 배치 데이터가 올바르지 않습니다: '.json_last_error_msg()), 400);
    }
    $layout = pair_home_sanitize_layout($decoded);
    $color = strtoupper((string)($_POST['background_color'] ?? '#F2ECE5'));
    if (!preg_match('/^#[0-9A-F]{6}$/', $color)) $color = '#F2ECE5';
    $background_url = pair_home_clean_url($_POST['background_image'] ?? '');
    $allowed_prefix = rtrim(G5_DATA_URL, '/') . '/pair-home/';
    if ($background_url !== '' && strpos($background_url, $allowed_prefix) !== 0) $background_url = '';
    $frame_theme = (string)($_POST['frame_theme'] ?? 'diary');
    if (!in_array($frame_theme, array('web', 'diary', 'mac'), true)) $frame_theme = 'diary';
    $saved = pair_home_save_setting('pair_home_layout', json_encode($layout, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $saved = pair_home_save_setting('pair_home_background_color', $color) && $saved;
    $saved = pair_home_save_setting('pair_home_background_image', $background_url) && $saved;
    $saved = pair_home_save_setting('pair_home_frame_theme', $frame_theme) && $saved;
    if (!$saved) pair_home_json_response(array('error'=>'설정을 저장하지 못했습니다.'), 500);
    pair_home_json_response(array('ok'=>true, 'layout'=>$layout));
}
pair_home_json_response(array('error'=>'알 수 없는 요청입니다.'), 400);
