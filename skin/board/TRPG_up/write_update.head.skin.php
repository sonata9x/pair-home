<?php
if (!defined('_GNUBOARD_')) exit;

$trpg_edit_mode = isset($_POST['trpg_edit_mode']) ? $_POST['trpg_edit_mode'] : '';

// 로그 본문만 수정할 때는 페이지 설정 필수값을 검사하지 않는다.
if ($trpg_edit_mode !== 'content') {
    $trpg_pc = trim((string)(isset($_POST['wr_pc']) ? $_POST['wr_pc'] : ''));

    if ($trpg_pc === '') {
        alert('PC를 입력하세요.');
    }
}
