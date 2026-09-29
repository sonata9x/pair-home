<?php
$menu['menu200'] = array(
    array('200000', '게시판설정', G5_ADMIN_URL . '/menu_list.php',   'config'),
    array('200100', '메뉴설정', G5_ADMIN_URL . '/menu_list.php',     'cf_menu', 1),
    array('200200', '게시판관리', '' . G5_ADMIN_URL . '/board_list.php', 'bbs_board'),
    array('200300', '단축 주소 관리', G5_ADMIN_URL . '/short_url_list.php', 'bbs_shorturl'),
    array('200400', '피드 설정', G5_ADMIN_URL . '/feed_config.php', 'bbs_feed'),
    array('200500', '이웃 관리', G5_ADMIN_URL . '/feed_neighbor.php', 'bbs_feed_neighbor'),
    array('200700', '내용 관리', G5_ADMIN_URL . '/contentlist.php', 'bbs_content'),
);