<?php
$sub_menu = "100100";
require_once './_common.php';

check_demo();

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

// POST 데이터가 있는지 확인
if (!$_POST) {
    alert('잘못된 접근입니다.');
}

// 폼에서 전송되는 필드들만 처리
$update_fields = array(
    'cf_use_email_certify' => (int) ($_POST['cf_use_email_certify'] ?? 0),
    'cf_email_use' => (int) ($_POST['cf_email_use'] ?? 0),
    'cf_register_level' => (int) ($_POST['cf_register_level'] ?? 2),
    'cf_register_point' => (int) ($_POST['cf_register_point'] ?? 0),
    'cf_leave_day' => (int) ($_POST['cf_leave_day'] ?? 30),
    'cf_prohibit_id' => strip_tags(clean_xss_attributes($_POST['cf_prohibit_id'] ?? '')),
    'cf_prohibit_email' => strip_tags(clean_xss_attributes($_POST['cf_prohibit_email'] ?? ''))
);

// SQL 업데이트 쿼리 생성
$set_clauses = array();
foreach ($update_fields as $field => $value) {
    if (is_int($value)) {
        $set_clauses[] = "{$field} = {$value}";
    } else {
        $set_clauses[] = "{$field} = '" . sql_real_escape_string($value) . "'";
    }
}

$sql = "UPDATE {$g5['config_table']} SET " . implode(', ', $set_clauses);
sql_query($sql);

// 성공 메시지와 함께 이전 페이지로 이동
alert('설정이 저장되었습니다.', './member_config.php');
?>
