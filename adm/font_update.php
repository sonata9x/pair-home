<?php
include_once('./_common.php');

$config_font_table = G5_TABLE_PREFIX . 'config_font';
ra0_normalize_config_font_table($config_font_table);

$mode = $_POST['mode'];
$fo_id = (int)$_POST['fo_id'];
$fo_name = trim($_POST['fo_name']);
$fo_family = trim($_POST['fo_family']);
$fo_import = trim($_POST['fo_import']);
$fo_use = (int)$_POST['fo_use'];
$fo_order = (int)$_POST['fo_order'];
$fo_memo = trim($_POST['fo_memo']);

// stripslashes 적용 (magic_quotes 대응)
$fo_name = stripslashes($fo_name);
$fo_family = ra0_normalize_font_family($fo_family);
$fo_import = stripslashes($fo_import);
$fo_memo = stripslashes($fo_memo);

// 필수값 체크
if(!$fo_name) {
    alert('폰트명을 입력해주세요.');
}
if(!$fo_family) {
    alert('Font Family를 입력해주세요.');
}

if($mode == 'add') {
    // 중복 체크 (SQL Injection 방지)
    $sql = "SELECT fo_id FROM {$config_font_table} WHERE fo_family = '".sql_real_escape_string($fo_family)."'";
    if(sql_fetch($sql)) {
        alert('이미 등록된 Font Family입니다.');
    }
    
    // 추가 (모든 값 escape 처리)
    $sql = "INSERT INTO {$config_font_table} SET 
            fo_name = '".sql_real_escape_string($fo_name)."',
            fo_family = '".sql_real_escape_string($fo_family)."',
            fo_import = '".sql_real_escape_string($fo_import)."',
            fo_use = '{$fo_use}',
            fo_order = '{$fo_order}',
            fo_memo = '".sql_real_escape_string($fo_memo)."',
            fo_datetime = NOW()";
    
    sql_query($sql);
    alert('폰트가 추가되었습니다.', './config_font.php');
    
} else if($mode == 'edit') {
    // 존재 체크
    $sql = "SELECT fo_id FROM {$config_font_table} WHERE fo_id = '{$fo_id}'";
    if(!sql_fetch($sql)) {
        alert('존재하지 않는 폰트입니다.');
    }
    
    // 중복 체크 (자신 제외)
    $sql = "SELECT fo_id FROM {$config_font_table} WHERE fo_family = '".sql_real_escape_string($fo_family)."' AND fo_id != '{$fo_id}'";
    if(sql_fetch($sql)) {
        alert('이미 등록된 Font Family입니다.');
    }
    
    // 수정
    $sql = "UPDATE {$config_font_table} SET 
            fo_name = '".sql_real_escape_string($fo_name)."',
            fo_family = '".sql_real_escape_string($fo_family)."',
            fo_import = '".sql_real_escape_string($fo_import)."',
            fo_use = '{$fo_use}',
            fo_order = '{$fo_order}',
            fo_memo = '".sql_real_escape_string($fo_memo)."'
            WHERE fo_id = '{$fo_id}'";
    
    sql_query($sql);
    alert('폰트가 수정되었습니다.', './config_font.php');
}

alert('처리 중 오류가 발생했습니다.');
?>
