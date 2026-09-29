<?php
include_once('./_common.php');

$config_font_table = G5_TABLE_PREFIX . 'config_font';
$fo_id = (int)$_GET['fo_id'];

if(!$fo_id) {
    alert('잘못된 접근입니다.');
}

// 존재 체크
$sql = "SELECT * FROM {$config_font_table} WHERE fo_id = '{$fo_id}'";
$row = sql_fetch($sql);
if(!$row) {
    alert('존재하지 않는 폰트입니다.');
}

// 삭제
$sql = "DELETE FROM {$config_font_table} WHERE fo_id = '{$fo_id}'";
sql_query($sql);

alert('폰트가 삭제되었습니다.', './config_font.php');
?>
