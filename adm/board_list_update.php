<?php
$sub_menu = "200200";
require_once './_common.php';

check_demo();

$post_count_chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;
$chk            = (isset($_POST['chk']) && is_array($_POST['chk'])) ? $_POST['chk'] : array();
$act_button     = isset($_POST['act_button']) ? strip_tags($_POST['act_button']) : '';
$board_table    = (isset($_POST['board_table']) && is_array($_POST['board_table'])) ? $_POST['board_table'] : array();

if (!$post_count_chk) {
    alert($act_button . " 하실 항목을 하나 이상 체크하세요.");
}

check_admin_token();

if ($act_button === "선택수정") {
    foreach ($chk as $k) {
        $k = (int) $k; // 체크된 인덱스

        // 그룹 시스템 제거됨 - gr_id 처리 무력화
        // $post_gr_id = isset($_POST['gr_id'][$k]) ? clean_xss_tags($_POST['gr_id'][$k], 1, 1) : '';
        $post_gr_id = ''; // 기본값으로 설정
        $post_bo_skin = isset($_POST['bo_skin'][$k]) ? clean_xss_tags($_POST['bo_skin'][$k], 1, 1) : '';
        
        // 권한 레벨 처리
        $post_bo_list_level = isset($_POST['bo_list_level'][$k]) ? (int)$_POST['bo_list_level'][$k] : 1;
        $post_bo_read_level = isset($_POST['bo_read_level'][$k]) ? (int)$_POST['bo_read_level'][$k] : 1;
        $post_bo_write_level = isset($_POST['bo_write_level'][$k]) ? (int)$_POST['bo_write_level'][$k] : 1;
        $post_bo_comment_level = isset($_POST['bo_comment_level'][$k]) ? (int)$_POST['bo_comment_level'][$k] : 1;
        
        $post_bo_order = isset($_POST['bo_order'][$k]) ? (int)$_POST['bo_order'][$k] : 0;
        $post_bo_type = isset($_POST['bo_type'][$k]) ? clean_xss_tags($_POST['bo_type'][$k], 1, 1) : 'normal';
        $post_board_table = isset($_POST['board_table'][$k]) ? clean_xss_tags($_POST['board_table'][$k], 1, 1) : '';

        // 그룹 시스템 제거됨 - 그룹 관리자 권한 확인 무력화
        /*
        if ($is_admin != 'super') {
            $sql = " select count(*) as cnt from {$g5['board_table']} a, {$g5['group_table']} b
                      where a.gr_id = '" . sql_real_escape_string($post_gr_id) . "'
                        and a.gr_id = b.gr_id
                        and b.gr_admin = '{$member['mb_id']}' ";
            $row = sql_fetch($sql);
            if (!$row['cnt']) {
                alert('최고관리자가 아닌 경우 다른 관리자의 게시판(' . $post_board_table . ')은 수정이 불가합니다.');
            }
        }
        */

        $p_bo_subject = isset($_POST['bo_subject'][$k]) ? strip_tags(clean_xss_attributes($_POST['bo_subject'][$k])) : '';

        // 그룹 시스템 제거됨 - gr_id 업데이트 제거
        $sql = " update {$g5['board_table']}
                    set bo_subject          = '" . sql_real_escape_string($p_bo_subject) . "',
                        bo_type             = '" . sql_real_escape_string($post_bo_type) . "',
                        bo_skin             = '" . sql_real_escape_string($post_bo_skin) . "',
                        bo_list_level       = '" . $post_bo_list_level . "',
                        bo_read_level       = '" . $post_bo_read_level . "',
                        bo_write_level      = '" . $post_bo_write_level . "',
                        bo_comment_level    = '" . $post_bo_comment_level . "',
                        bo_order            = '" . $post_bo_order . "'
                  where bo_table            = '" . sql_real_escape_string($post_board_table) . "' ";

        sql_query($sql);
    }
} elseif ($act_button === "선택삭제") {
    if (!$is_admin) {
        alert('게시판 삭제는 관리자만 가능합니다.');
    }

    // _BOARD_DELETE_ 상수를 선언해야 board_delete.inc.php 가 정상 작동함
    define('_BOARD_DELETE_', true);

    foreach ($chk as $k) {
        $k = (int) $k; // 체크된 인덱스

        // include 전에 $bo_table 값을 반드시 넘겨야 함
        $tmp_bo_table = isset($_POST['board_table'][$k]) ? trim(clean_xss_tags($_POST['board_table'][$k], 1, 1)) : '';

        if (preg_match("/^[A-Za-z0-9_]+$/", $tmp_bo_table)) {
            $bo_table = $tmp_bo_table; // board_delete.inc.php에서 사용할 변수
            include './board_delete.inc.php';
        }
    }
}

run_event('admin_board_list_update', $act_button, $chk, $board_table, $qstr);

goto_url('./board_list.php?' . $qstr);
?>
