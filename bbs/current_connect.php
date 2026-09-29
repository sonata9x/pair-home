<?php
include_once('./_common.php');

$g5['title'] = '현재접속자';
include_once('./_head.php');

// 현재접속자 정보
include_once(G5_LIB_PATH.'/connect.lib.php');
$list = connect_list();

include_once($connect_skin_path.'/current_connect.skin.php');

include_once('./_tail.php');
?>