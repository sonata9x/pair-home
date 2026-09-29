<?php
/**
 * 스토어 확장팩 관리자 메뉴
 * 이 메뉴는 Store Extension kit이 설치된 경우에만 표시됩니다.
 */

// 스토어 모듈이 설치되어 있는지 확인
if (file_exists(G5_PATH . '/extend/store_config.php')) {
    $menu['menu500'] = array (
        array('500000', '쇼핑몰관리', G5_ADMIN_URL.'/config_store.php', 'store'),
        array('500100', '기본설정', G5_ADMIN_URL.'/config_store.php?tab=setup', 'store_setup'),
        array('500200', '게시판&쿠폰', G5_ADMIN_URL.'/config_store.php?tab=manage', 'store_manage'),
        array('500300', '포인트관리', G5_ADMIN_URL.'/config_store.php?tab=point', 'store_point'),
        array('500400', '주문관리', G5_ADMIN_URL.'/config_store.php?tab=orders', 'store_orders'),
        array('500600', '매출통계', G5_ADMIN_URL.'/config_store.php?tab=stats', 'store_stats'),
    );
}
// 쇼핑몰 모듈이 없으면 menu500은 등록되지 않음 (메뉴 자동 숨김)
?>