<?php
/**
 * 게임 관리자 메뉴
 *
 * 이 메뉴는 Game Extension이 설치된 경우에만 표시됩니다.
 * /game/ 폴더와 extend/game_config.php가 존재해야 합니다.
 */

// 게임 모듈이 설치되어 있는지 확인
if (file_exists(G5_PATH . '/extend/game_config.php')) {
    $game_menu = array(
        // 메인 게임 관리
        array('600000', '게임관리', G5_ADMIN_URL . '/config_game.php', 'game'),
    );

    // 룰렛이 설치되어 있으면 메뉴 추가
    if (file_exists(G5_PATH . '/game/lib/roulette.lib.php')) {
        $game_menu[] = array('600100', '룰렛', G5_ADMIN_URL . '/game/roulette.php', 'game_roulette');
    }

    // 향후 추가될 게임들
    // if (file_exists(G5_PATH . '/game/lib/poker.lib.php')) {
    //     $game_menu[] = array('600200', '포커', G5_ADMIN_URL . '/game/poker.php', 'game_poker');
    // }
    // if (file_exists(G5_PATH . '/game/lib/gacha.lib.php')) {
    //     $game_menu[] = array('600300', '가챠', G5_ADMIN_URL . '/game/gacha.php', 'game_gacha');
    // }

    $menu['menu600'] = $game_menu;
}
// 게임 모듈이 없으면 menu600은 등록되지 않음 (메뉴 자동 숨김)
?>
