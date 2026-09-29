<?php
if (!defined('_GNUBOARD_')) exit;
include_once(G5_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/connect.lib.php'); // 현재 접속자용

// 알림/채팅 개수 미리 로드 (헤더 사용 여부와 관계없이)
$unread_count = 0;
$chat_unread_count = 0;
if ($is_member) {
    if (file_exists(G5_LIB_PATH.'/notification.lib.php')) {
        include_once(G5_LIB_PATH.'/notification.lib.php');
        $unread_count = get_notification_count($member['mb_id']);
    }
    if (file_exists(G5_LIB_PATH.'/chat.lib.php')) {
        include_once(G5_LIB_PATH.'/chat.lib.php');
        $chat_unread_count = chat_get_unread_count($member['mb_id']);
    }
}

// 메뉴 데이터 미리 로드 (모바일 메뉴에서도 사용)
$menu_datas = get_menu_db(0, true);
$grouped_menus = [];
$current_parent = null;
$i = 0;

foreach($menu_datas as $row) {
    if(empty($row)) continue;

    if(strpos($row['me_name'], 'ㄴ') === 0) {
        if($current_parent !== null) {
            $row['me_name'] = ltrim($row['me_name'], 'ㄴ');
            $grouped_menus[$current_parent]['sub'][] = $row;
        }
    } else {
        $current_parent = $i;
        $grouped_menus[$i] = $row;
        $grouped_menus[$i]['sub'] = [];
    }
    $i++;
}
?>
<!-- 상단 시작 -->
<?php
// ?lbc=CODE&lbi=N 이 있으면 카테고리 topbar 로 #hd 대체 (iframe 없이 직접 네비게이션)
$__use_category_topbar = false;
$__lbc_code = '';
$__lbc_idx  = 0;
$__lbc_cat  = null;

// ?lbw=1&lbwi=N 이 있으면 세계관 topbar
$__use_world_topbar = false;
$__lbw_boards = array();
$__lbw_idx    = 0;

if ($is_member
    && (!empty($_GET['lbc']) || !empty($_GET['lbw']))
    && file_exists(G5_PATH . '/extend/lobby_config.php')
    && file_exists(G5_COMMUNITY_LIB_PATH . '/lobby.lib.php')
) {
    include_once(G5_COMMUNITY_LIB_PATH . '/lobby.lib.php');

    // main_type === 'lobby' 일 때만 활성화 (lobby_pub 등 다른 메인일 땐 비활성)
    if (function_exists('lobby_is_main_page') && lobby_is_main_page()) {
        if (!empty($_GET['lbc'])) {
            $__lbc_code = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$_GET['lbc']);
            if ($__lbc_code && function_exists('lobby_get_category')) {
                $__lbc_cat = lobby_get_category($__lbc_code);
                if ($__lbc_cat && !empty($__lbc_cat['items'])) {
                    $__lbc_idx = isset($_GET['lbi']) ? (int)$_GET['lbi'] : 0;
                    if ($__lbc_idx < 0 || $__lbc_idx >= count($__lbc_cat['items'])) $__lbc_idx = 0;
                    $__use_category_topbar = true;
                }
            }
        } else if (!empty($_GET['lbw']) && function_exists('lobby_get_world_boards')) {
            $__lbw_boards = lobby_get_world_boards();
            if (!empty($__lbw_boards)) {
                $__lbw_idx = isset($_GET['lbwi']) ? (int)$_GET['lbwi'] : 0;
                if ($__lbw_idx < 0 || $__lbw_idx >= count($__lbw_boards)) $__lbw_idx = 0;
                $__use_world_topbar = true;
            }
        }
    }
}

// 일반 게시판 topbar — 카테고리/세계관 컨텍스트가 없을 때 모든 board 페이지에 기본 topbar
// main_type === 'lobby' 일 때만 활성화
$__use_board_topbar = false;
if ($is_member
    && empty($__is_iframe_embed)
    && empty($GLOBALS['__skip_lobby_site_header'])
    && empty($__use_category_topbar)
    && empty($__use_world_topbar)
    && !empty($bo_table) && !empty($board)
    && file_exists(G5_PATH . '/extend/lobby_config.php')
    && file_exists(G5_COMMUNITY_LIB_PATH . '/lobby.lib.php')
) {
    include_once(G5_COMMUNITY_LIB_PATH . '/lobby.lib.php');
    if (function_exists('lobby_is_installed') && lobby_is_installed()
        && function_exists('lobby_is_main_page') && lobby_is_main_page()) {
        $__use_board_topbar = true;
    }
}

// 로비를 사이트 메인으로 사용 중이면 사이트 공통 #hd 대신 로비 헤더를 출력
// 단, iframe / field / 카테고리 / 세계관 / 게시판 topbar 우선일 때는 skip
$__use_lobby_site_header = false;
if ($is_member
    && empty($__is_iframe_embed)
    && empty($GLOBALS['__skip_lobby_site_header'])
    && empty($__use_category_topbar)
    && empty($__use_world_topbar)
    && empty($__use_board_topbar)
    && file_exists(G5_PATH . '/extend/lobby_config.php')
    && file_exists(G5_COMMUNITY_LIB_PATH . '/lobby.lib.php')
) {
    include_once(G5_COMMUNITY_LIB_PATH . '/lobby.lib.php');
    if (function_exists('lobby_is_main_page') && lobby_is_main_page() && lobby_is_installed()) {
        include G5_COMMUNITY_PATH . '/lobby/_partials/site_header.php';
        $__use_lobby_site_header = !empty($GLOBALS['__lobby_site_header_rendered']);
    }
}

// 카테고리/세계관/일반 게시판 topbar 출력 (사이드바 포함)
if ($__use_category_topbar || $__use_world_topbar || $__use_board_topbar) {
    if ($__use_category_topbar) {
        include G5_COMMUNITY_PATH . '/lobby/_partials/category_topbar.php';
    } else if ($__use_world_topbar) {
        include G5_COMMUNITY_PATH . '/lobby/_partials/world_topbar.php';
    } else {
        include G5_COMMUNITY_PATH . '/lobby/_partials/board_topbar.php';
    }
    include G5_COMMUNITY_PATH . '/lobby/_partials/menu_sidebar.php';
    // header_actions chrome (패널/CSS/JS) — topbar 의 backdrop-filter containing
    // block 영향을 피하려고 topbar 마크업 밖에 출력. actions HTML 이 출력된 경우에만
    // 내부 가드를 통과한다.
    include G5_COMMUNITY_PATH . '/lobby/_partials/header_actions_chrome.php';
    echo '<script src="' . G5_COMMUNITY_URL . '/lobby/lobby.js?v=' . @filemtime(G5_COMMUNITY_PATH . '/lobby/lobby.js') . '"></script>';
}
?>
<?php if (!$__use_lobby_site_header && !$__use_category_topbar && !$__use_world_topbar && !$__use_board_topbar && $design['use_header'] == '1') { ?>
<div id="hd">
    <div id="hd_wrapper">
        <?php if ($design['use_logo'] == '1') { ?>
        <div id="logo">
            <a href="<?php echo G5_URL; ?>" onclick="
                if(window.parent && window.parent !== window) {
                    window.parent.postMessage({type: 'navigate', url: '<?php echo G5_URL; ?>'}, '*');
                    return false;
                }
                return true;
            ">
                <?php if (!empty($design['logo_image_url'])) { ?><img src="<?php echo htmlspecialchars($design['logo_image_url']); ?>"><?php } ?>
            </a>
        </div>
        <?php } ?>
        <div id="hd_menu">
            <ul id="main_menu_list">
                <?php
                $menu_zindex = 999;

                // 메뉴 출력
                foreach($grouped_menus as $idx => $row) {
                    $has_sub = !empty($row['sub']);
                    $add_class = $has_sub ? 'main_me_plus has_submenu' : '';

                    // 구분선인 경우 여백 추가
                    if ($row['me_name'] == '구분선') {
                        echo '<li class="menu-separator" style="height: 20px; border: none;"></li>';
                        continue;
                    }
                ?>
                <li class="main_me <?php echo $add_class; ?>" style="z-index:<?php echo $menu_zindex--; ?>">
                    <?php if($has_sub) { ?>
                        <!-- 하위메뉴가 있으면 토글 버튼 -->
                        <a href="javascript:void(0);" class="main_me_link toggle_menu" data-target="submenu-<?php echo $idx; ?>">
                            <?php if(!empty($row['me_icon'])) { ?><i class="fa <?php echo $row['me_icon']; ?>"></i><?php } ?>
                            <?php if(!empty($row['me_name'])) { ?><?php echo !empty($row['me_icon']) ? ' ' : ''; ?><?php echo $row['me_name']; ?><?php } ?>
                        </a>
                    <?php } else { ?>
                        <!-- 하위메뉴가 없으면 일반 링크 -->
                        <a href="<?php echo $row['me_link']; ?>" target="_<?php echo $row['me_target']; ?>" class="main_me_link">
                            <?php if(!empty($row['me_icon'])) { ?><i class="fa <?php echo $row['me_icon']; ?>"></i><?php } ?>
                            <?php if(!empty($row['me_name'])) { ?><?php echo !empty($row['me_icon']) ? ' ' : ''; ?><?php echo $row['me_name']; ?><?php } ?>
                        </a>
                    <?php } ?>

                    <?php if($has_sub) { ?>
                        <div class="sub_me_list" id="submenu-<?php echo $idx; ?>">
                            <ul class="sub_me_box">
                                <?php foreach($row['sub'] as $row2) { ?>
                                    <li class="sub_me">
                                        <a href="<?php echo $row2['me_link']; ?>" target="_<?php echo $row2['me_target']; ?>" class="sub_me_link">
                                            <?php if(!empty($row2['me_icon'])) { ?>
                                                <i class="fa <?php echo $row2['me_icon']; ?>"></i>
                                            <?php } elseif(!empty($row2['me_name'])) { ?>
                                                <i class="fa-solid fa-angle-right"></i>
                                            <?php } ?>
                                            <?php if(!empty($row2['me_name'])) { ?><?php echo (!empty($row2['me_icon']) || empty($row2['me_name'])) ? ' ' : ''; ?><?php echo $row2['me_name']; ?><?php } ?>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
                </li>
                <?php } ?>
            </ul>
        </div>
        <ul class="hd_login">
            <?php if ($is_member) { ?>
                <div class="connect">
                    <i class="fa-solid fa-users"></i>
                    <?php if ($is_admin) { ?>
                        <a href="<?php echo G5_BBS_URL ?>/current_connect.php" class="connect-num">
                    <?php } else { ?>
                        <span class="connect-num">
                    <?php } ?>
                        <?php echo connect(); // 현재 접속자수 ?>
                    <?php if ($is_admin) { ?>
                        </a>
                    <?php } else { ?>
                        </span>
                    <?php } ?>
                </div>
            <?php } ?>
            <?php if ($is_member) { ?>
            <li class="chat-icon">
                <a href="<?php echo G5_BBS_URL ?>/chat.php" id="chat-link" class="js-chat-count-link">
                    <i class="fa-solid fa-comment"></i>
                    <?php if ($chat_unread_count > 0) { ?>
                    <span class="notification-badge js-chat-count-badge" id="chat-count"><?php echo $chat_unread_count; ?></span>
                    <?php } ?>
                </a>
            </li>
            <li class="notification-icon">
                <a href="<?php echo G5_URL ?>/mypage.php" id="notification-bell" class="js-notification-count-link">
                    <i class="fa-solid fa-bell"></i>
                    <?php if ($unread_count > 0) { ?>
                    <span class="notification-badge js-notification-count-badge" id="notification-count"><?php echo $unread_count; ?></span>
                    <?php } ?>
                </a>
            </li>
            <li class="feed-icon">
                <a href="<?php echo G5_BBS_URL ?>/neighbor_feed.php" title="이웃 새글">
                    <i class="fa-solid fa-rss"></i>
                </a>
            </li>
            <li><a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=<?php echo G5_BBS_URL ?>/register_form.php">정보수정</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/logout.php">로그아웃</a></li>
            <?php if ($is_admin) { ?>
            <li class="hd_admin"><a href="<?php echo correct_goto_url(G5_ADMIN_URL); ?>">관리자</a></li>
            <?php } } else { ?>
            <li><a href="<?php echo G5_BBS_URL ?>/register.php">회원가입</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/login.php">로그인</a></li>
            <?php } ?>
        </ul>
    </div>

</div>
<!-- 상단 끝 -->
<?php } else if (!$__use_lobby_site_header && !$__use_category_topbar && !$__use_world_topbar && !$__use_board_topbar) { ?>
<!-- 헤더 미사용 시에도 로그인/알림 영역만 표시 -->
<div id="hd_minimal">
    <ul class="hd_login">
        <?php if ($is_member) { ?>
            <div class="connect">
                <i class="fa-solid fa-users"></i>
                <?php if ($is_admin) { ?>
                    <a href="<?php echo G5_BBS_URL ?>/current_connect.php" class="connect-num">
                <?php } else { ?>
                    <span class="connect-num">
                <?php } ?>
                    <?php echo connect(); ?>
                <?php if ($is_admin) { ?>
                    </a>
                <?php } else { ?>
                    </span>
                <?php } ?>
            </div>
            <li class="chat-icon">
                <a href="<?php echo G5_BBS_URL ?>/chat.php" id="chat-link" class="js-chat-count-link">
                    <i class="fa-solid fa-comment"></i>
                    <?php if ($chat_unread_count > 0) { ?>
                    <span class="notification-badge js-chat-count-badge" id="chat-count"><?php echo $chat_unread_count; ?></span>
                    <?php } ?>
                </a>
            </li>
            <li class="notification-icon">
                <a href="<?php echo G5_URL ?>/mypage.php" id="notification-bell" class="js-notification-count-link">
                    <i class="fa-solid fa-bell"></i>
                    <?php if ($unread_count > 0) { ?>
                    <span class="notification-badge js-notification-count-badge" id="notification-count"><?php echo $unread_count; ?></span>
                    <?php } ?>
                </a>
            </li>
            <li class="feed-icon">
                <a href="<?php echo G5_BBS_URL ?>/neighbor_feed.php" title="이웃 새글">
                    <i class="fa-solid fa-rss"></i>
                </a>
            </li>
            <li><a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=<?php echo G5_BBS_URL ?>/register_form.php">정보수정</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/logout.php">로그아웃</a></li>
            <?php if ($is_admin) { ?>
            <li class="hd_admin"><a href="<?php echo correct_goto_url(G5_ADMIN_URL); ?>">관리자</a></li>
            <?php } ?>
        <?php } else { ?>
            <li><a href="<?php echo G5_BBS_URL ?>/register.php">회원가입</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/login.php">로그인</a></li>
        <?php } ?>
    </ul>
</div>
<?php } ?>

<?php if (!$__use_lobby_site_header && !$__use_category_topbar && !$__use_world_topbar && !$__use_board_topbar && $design['use_header'] == '1') { ?>
<!-- 모바일 햄버거 메뉴 버튼 -->
<button type="button" id="mobile_menu_btn" class="mobile_menu_btn" aria-label="메뉴 열기">
    <i class="fa-solid fa-bars"></i>
</button>

<!-- 모바일 사이드바 메뉴 -->
<div id="mobile_sidebar" class="mobile_sidebar">
    <div class="mobile_sidebar_header">
        <a href="<?php echo G5_URL; ?>" class="mobile_home_btn" aria-label="홈으로" onclick="
            if(window.parent && window.parent !== window) {
                window.parent.postMessage({type: 'navigate', url: '<?php echo G5_URL; ?>'}, '*');
                return false;
            }
            return true;
        ">
            <i class="fa-solid fa-home"></i>
        </a>
        <button type="button" id="mobile_menu_close" class="mobile_menu_close" aria-label="메뉴 닫기">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <nav class="mobile_sidebar_nav">
        <ul class="mobile_menu_list">
            <?php
            // 동일한 메뉴 구조 사용
            foreach($grouped_menus as $idx => $row) {
                $has_sub = !empty($row['sub']);

                // 구분선인 경우 여백 추가
                if ($row['me_name'] == '구분선') {
                    echo '<li class="mobile-menu-separator"></li>';
                    continue;
                }
            ?>
            <li class="mobile_menu_item <?php echo $has_sub ? 'has_submenu' : ''; ?>">
                <?php if($has_sub) { ?>
                    <!-- 하위메뉴가 있으면 토글 버튼 -->
                    <a href="javascript:void(0);" class="mobile_menu_link toggle_mobile_submenu" data-target="mobile-submenu-<?php echo $idx; ?>">
                        <?php if(!empty($row['me_icon'])) { ?><i class="fa <?php echo $row['me_icon']; ?>"></i><?php } ?>
                        <span><?php echo $row['me_name']; ?></span>
                        <i class="fa-solid fa-chevron-down submenu_arrow"></i>
                    </a>
                    <ul class="mobile_submenu" id="mobile-submenu-<?php echo $idx; ?>">
                        <?php foreach($row['sub'] as $row2) { ?>
                            <li class="mobile_submenu_item">
                                <a href="<?php echo $row2['me_link']; ?>" target="_<?php echo $row2['me_target']; ?>" class="mobile_submenu_link">
                                    <?php if(!empty($row2['me_icon'])) { ?>
                                        <i class="fa <?php echo $row2['me_icon']; ?>"></i>
                                    <?php } else { ?>
                                        <i class="fa-solid fa-angle-right"></i>
                                    <?php } ?>
                                    <span><?php echo $row2['me_name']; ?></span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <!-- 하위메뉴가 없으면 일반 링크 -->
                    <a href="<?php echo $row['me_link']; ?>" target="_<?php echo $row['me_target']; ?>" class="mobile_menu_link">
                        <?php if(!empty($row['me_icon'])) { ?><i class="fa <?php echo $row['me_icon']; ?>"></i><?php } ?>
                        <span><?php echo $row['me_name']; ?></span>
                    </a>
                <?php } ?>
            </li>
            <?php } ?>
        </ul>

        <!-- 로그인/회원정보 영역 -->
        <div class="mobile_user_area">
            <?php if ($is_member) { ?>
                <div class="mobile_user_info">
                    <i class="fa-solid fa-user"></i>
                    <span><?php echo $member['mb_name']; ?>님</span>
                </div>

                <!-- 접속자 수 -->
                <div class="mobile_connect">
                    <i class="fa-solid fa-users"></i>
                    <?php if ($is_admin) { ?>
                        <a href="<?php echo G5_BBS_URL ?>/current_connect.php" class="connect-num">
                            접속자 <?php echo connect(); ?>
                        </a>
                    <?php } else { ?>
                        <span class="connect-text">접속자 <?php echo connect(); ?></span>
                    <?php } ?>
                </div>

                <ul class="mobile_user_menu">
                    <li><a href="<?php echo G5_BBS_URL ?>/chat.php" class="js-chat-count-link"><i class="fa-solid fa-comment"></i> 채팅 <?php if ($chat_unread_count > 0) { ?><span class="badge js-chat-count-badge"><?php echo $chat_unread_count; ?></span><?php } ?></a></li>
                    <li><a href="<?php echo G5_URL ?>/mypage.php" class="js-notification-count-link"><i class="fa-solid fa-bell"></i> 알림 <?php if ($unread_count > 0) { ?><span class="badge js-notification-count-badge"><?php echo $unread_count; ?></span><?php } ?></a></li>
                    <li><a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=<?php echo G5_BBS_URL ?>/register_form.php"><i class="fa-solid fa-gear"></i> 정보수정</a></li>
                    <?php if ($is_admin) { ?>
                    <li><a href="<?php echo correct_goto_url(G5_ADMIN_URL); ?>"><i class="fa-solid fa-shield"></i> 관리자</a></li>
                    <?php } ?>
                    <li><a href="<?php echo G5_BBS_URL ?>/logout.php"><i class="fa-solid fa-right-from-bracket"></i> 로그아웃</a></li>
                </ul>
            <?php } else { ?>
                <ul class="mobile_user_menu">
                    <li><a href="<?php echo G5_BBS_URL ?>/register.php"><i class="fa-solid fa-user-plus"></i> 회원가입</a></li>
                    <li><a href="<?php echo G5_BBS_URL ?>/login.php"><i class="fa-solid fa-right-to-bracket"></i> 로그인</a></li>
                </ul>
            <?php } ?>
        </div>
    </nav>
</div>
<?php
    // 헤더가 출력되었음을 표시 (위젯 중복 방지)
    $GLOBALS['hd_menu_loaded'] = true;
?>

<!-- 모바일 메뉴 오버레이 -->
<div id="mobile_overlay" class="mobile_overlay"></div>
<?php } ?>

<!-- 콘텐츠 시작 -->
<div id="wrapper">
    <div id="container_wr">

<?php if (!empty($design['click_sound_url'])): ?>
<!-- 클릭음 JavaScript -->
<script>
(function() {
    const clickSound = new Audio('<?php echo $design['click_sound_url']; ?>');
    clickSound.volume = <?php echo (isset($design['click_sound_volume']) ? $design['click_sound_volume'] : 50) / 100; ?>;

    document.addEventListener('click', function() {
        clickSound.currentTime = 0;
        clickSound.play().catch(function() {
        });
    });
})();
</script>
<?php endif; ?>

<?php if (!empty($design['disable_rightclick']) && $design['disable_rightclick'] == '1' && !$is_admin): ?>
<!-- 우클릭 금지 JavaScript (관리자 제외) -->
<script>
(function() {
    // PC: 우클릭 금지
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });

    // 모바일: 길게 누르기 금지 (이미지 저장 메뉴 방지)
    document.addEventListener('touchstart', function(e) {
        if (e.target.tagName === 'IMG') {
            e.target.style.pointerEvents = 'none';
            setTimeout(function() {
                e.target.style.pointerEvents = 'auto';
            }, 500);
        }
    }, { passive: true });

    // 이미지 드래그 금지
    document.addEventListener('dragstart', function(e) {
        if (e.target.tagName === 'IMG') {
            e.preventDefault();
            return false;
        }
    });
})();
</script>
<style>
/* 이미지 선택 금지 */
img {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    -webkit-touch-callout: none;
}
</style>
<?php endif; ?>
