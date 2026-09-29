<?php
$sub_menu = "200300";
require_once './_common.php';

if ($w == 'u' || $w == 'd') {
    check_demo();
}

// 관리자 권한 체크
if (!$is_admin) {
    alert('관리자만 접근할 수 있습니다.');
}

check_admin_token();

$ma_id = isset($_POST['ma_id']) ? (int) $_POST['ma_id'] : 0;
$ma_subject = isset($_POST['ma_subject']) ? strip_tags(clean_xss_attributes($_POST['ma_subject'])) : '';
$ma_content = isset($_POST['ma_content']) ? $_POST['ma_content'] : '';

if ($w == '') {
    $sql = " insert {$g5['mail_table']}
                set ma_subject = '{$ma_subject}',
                     ma_content = '{$ma_content}',
                     ma_time = '" . G5_TIME_YMDHIS . "',
                     ma_ip = '{$_SERVER['REMOTE_ADDR']}' ";
    sql_query($sql);

    $ma_id = sql_insert_id();
    run_event('admin_mail_created', $ma_id);

} elseif ($w == 'u') {
    $sql = " update {$g5['mail_table']}
                set ma_subject = '{$ma_subject}',
                     ma_content = '{$ma_content}',
                     ma_time = '" . G5_TIME_YMDHIS . "',
                     ma_ip = '{$_SERVER['REMOTE_ADDR']}'
                where ma_id = '{$ma_id}' ";
    sql_query($sql);
    run_event('admin_mail_updated', $ma_id);

} elseif ($w == 'd') {
    $sql = " delete from {$g5['mail_table']} where ma_id = '{$ma_id}' ";
    sql_query($sql);
    run_event('admin_mail_deleted', $ma_id);
}

goto_url('./mail_list.php');
