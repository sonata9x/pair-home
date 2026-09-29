<?php
include_once('./_common.php');

check_admin_token();

if (!$is_admin) {
    alert('관리자만 접근 가능합니다.');
}

$bo_table = isset($_GET['bo_table']) ? preg_replace('/[^a-z0-9_]/i', '', $_GET['bo_table']) : '';
if (!$bo_table) {
    alert('게시판 TABLE명이 없습니다.');
}

$write_table = $g5['write_prefix'] . $bo_table;

// 게시판 정보 확인
$board = get_board_db($bo_table, true);
if (!$board) {
    alert('게시판 정보가 없습니다. 먼저 게시판을 생성해주세요.');
}

// 테이블이 이미 존재하는지 확인
$table_exists = sql_query("SHOW TABLES LIKE '{$write_table}'", false);
$table_row = sql_fetch_array($table_exists);
if ($table_row) {
    alert('테이블이 이미 존재합니다.', './board_table_check.php?bo_table='.$bo_table);
}

// sql_write.sql 읽어서 테이블 생성
$file = file('./sql_write.sql');
if (!$file) {
    alert('sql_write.sql 파일을 찾을 수 없습니다.');
}

$file = get_db_create_replace($file);
$sql = implode("\n", $file);
$sql = str_replace('__TABLE_NAME__', $write_table, $sql);
$sql = str_replace(';', '', $sql);

// 테이블 생성 실행
$result = sql_query($sql, false);

// 테이블이 생성되었는지 확인
$table_check = sql_query("SHOW TABLES LIKE '{$write_table}'", false);
$table_created = sql_fetch_array($table_check);

if ($table_created) {
    alert('테이블이 성공적으로 생성되었습니다.', './board_table_check.php?bo_table='.$bo_table);
} else {
    alert('테이블 생성 실패\n\nPHPMyAdmin에서 직접 실행해보세요:\n\n' . $sql);
}
?>
