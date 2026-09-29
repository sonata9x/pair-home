<?php
if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 키트 관리자 메뉴
 *
 * RE_KIT이 설치된 경우에만 표시됩니다.
 */

if (file_exists(G5_PATH . '/extend/re_config.php')) {
    $menu['menu700'] = array(
        array('700000', '키트관리', G5_ADMIN_URL . '/re/index.php', 'kit'),
        array('700100', '관계 갈래 관리', G5_ADMIN_URL . '/re/index.php', 'kit_re'),
    );
}
?>
