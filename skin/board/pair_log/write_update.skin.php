<?php
if (!defined('_GNUBOARD_')) exit;
include_once G5_LIB_PATH.'/pair_board.lib.php';
if ($w === '' && isset($_FILES['pair_log_html']) && $_FILES['pair_log_html']['error'] === UPLOAD_ERR_OK) {
    $raw = file_get_contents($_FILES['pair_log_html']['tmp_name']);
    $clean = pair_log_sanitize_html($raw === false ? '' : $raw);
    if ($clean === '') alert('HTML 로그를 읽거나 정리하지 못했습니다.');
    sql_query("UPDATE {$write_table} SET wr_content='".sql_real_escape_string($clean)."', wr_7='".sql_real_escape_string($wr_7)."', wr_8='1' WHERE wr_id='".(int)$wr_id."'");
}
$tracks = pair_log_parse_tracks($_POST['pair_log_tracks'] ?? '');
pair_log_save_tracks($bo_table,$wr_id,$tracks);
?>
