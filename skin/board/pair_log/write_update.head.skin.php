<?php
if (!defined('_GNUBOARD_')) exit;
include_once G5_LIB_PATH.'/pair_board.lib.php';
if ($w === 'u' && !empty($_POST['pair_log_settings'])) {
    $original = sql_fetch("SELECT wr_subject,wr_content,wr_1,wr_2,wr_3,wr_7,wr_8 FROM {$write_table} WHERE wr_id='".(int)$wr_id."'");
    $wr_subject = sql_real_escape_string((string)($original['wr_subject'] ?? ''));
    $wr_content = sql_real_escape_string((string)($original['wr_content'] ?? ''));
    $wr_1 = sql_real_escape_string((string)($original['wr_1'] ?? ''));
    $wr_2 = sql_real_escape_string((string)($original['wr_2'] ?? ''));
    $wr_3 = sql_real_escape_string((string)($original['wr_3'] ?? ''));
    $wr_7 = (string)($original['wr_7'] ?? '');
    $wr_8 = (string)($original['wr_8'] ?? '1');
}
if ($w === '') {
    $file = $_FILES['pair_log_html'] ?? null;
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) alert('HTML 로그 파일을 선택하세요.');
    $extension = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension,array('html','htm'),true)) alert('HTML 파일만 업로드할 수 있습니다.');
    if ((int)$file['size'] > 10*1024*1024) alert('HTML 로그는 10MB 이하만 업로드할 수 있습니다.');
    $wr_7 = mb_substr(basename((string)$file['name']),0,255);
    $wr_8 = '1';
}
?>
