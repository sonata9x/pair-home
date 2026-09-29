<?php
$sub_menu = "200100";
require_once './_common.php';

check_demo();

if (!$is_admin) {
    alert('관리자만 접근 가능합니다.');
}

check_admin_token();

// 이전 메뉴정보 삭제
$sql = " delete from {$g5['menu_table']} ";
sql_query($sql);

$count = isset($_POST['me_name']) ? count($_POST['me_name']) : 0;

for ($i = 0; $i < $count; $i++) {
    $_POST = array_map_deep('trim', $_POST);

    if (preg_match('/^javascript/i', preg_replace('/[ ]{1,}|[\t]/', '', $_POST['me_link'][$i]))) {
        $_POST['me_link'][$i] = G5_URL;
    }

    $_POST['me_link'][$i] = is_array($_POST['me_link']) ? clean_xss_tags(clean_xss_attributes(preg_replace('/[ ]{2,}|[\t]/', '', $_POST['me_link'][$i]), 1)) : '';
    $_POST['me_link'][$i] = html_purifier($_POST['me_link'][$i]);

    $me_name = is_array($_POST['me_name']) ? strip_tags($_POST['me_name'][$i]) : '';
    $me_icon = is_array($_POST['me_icon']) ? strip_tags($_POST['me_icon'][$i]) : '';
    $me_link = (preg_match('/^javascript/i', $_POST['me_link'][$i]) || preg_match('/script:/i', $_POST['me_link'][$i])) ? G5_URL : strip_tags(clean_xss_attributes($_POST['me_link'][$i]));
    $me_target = is_array($_POST['me_target']) ? strip_tags($_POST['me_target'][$i]) : 'self';
    $me_order = is_array($_POST['me_order']) ? (int)$_POST['me_order'][$i] : 0;
    $me_level = is_array($_POST['me_level']) ? (int)$_POST['me_level'][$i] : 1;

    // 메뉴명과 아이콘이 둘 다 비어있거나, 링크가 비어있으면 건너뛰기
    if ((!$me_name && !$me_icon) || !$me_link) {
        continue;
    }

    // 메뉴 코드 생성 (간단한 순차 코드)
    $sql = " select MAX(CAST(me_code as UNSIGNED)) as max_code from {$g5['menu_table']} ";
    $row = sql_fetch($sql);
    $me_code = $row['max_code'] ? $row['max_code'] + 1 : 1;

    // 메뉴 등록
    $sql = " insert into {$g5['menu_table']}
                set me_code         = '" . sprintf('%02d', $me_code) . "',
                    me_name         = '" . sql_real_escape_string($me_name) . "',
                    me_icon         = '" . sql_real_escape_string($me_icon) . "',
                    me_link         = '" . sql_real_escape_string($me_link) . "',
                    me_target       = '" . sql_real_escape_string($me_target) . "',
                    me_order        = '" . $me_order . "',
                    me_level        = '" . $me_level . "' ";
    sql_query($sql);
}

run_event('admin_menu_list_update');

goto_url('./menu_list.php');
?>
