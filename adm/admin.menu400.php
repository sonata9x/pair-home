<?php
/**
 * 커뮤니티 확장팩 관리자 메뉴
 *
 * 이 메뉴는 Community Extension Pack이 설치된 경우에만 표시됩니다.
 * /community/ 폴더가 존재하지 않으면 이 메뉴는 자동으로 숨겨집니다.
 *
 * 확장팩별 조건:
 * - 필드 시스템: extend/field_config.php 존재 시
 * - 레이드 시스템: extend/raid_config.php 존재 시
 */

// 커뮤니티 모듈이 설치되어 있는지 확인
if (file_exists(G5_PATH . '/extend/community_config.php')) {

    $has_economy_ext_admin = file_exists(G5_ADMIN_PATH . '/community/economy/attend/attend.php')
                           || file_exists(G5_ADMIN_PATH . '/community/economy/salary/salary.php')
                           || file_exists(G5_ADMIN_PATH . '/community/economy/gacha/gacha.php')
                           || file_exists(G5_ADMIN_PATH . '/community/economy/auction/auction.php')
                           || file_exists(G5_ADMIN_PATH . '/community/economy/exchange/exchange.php')
                           || file_exists(G5_ADMIN_PATH . '/community/economy/betting/betting.php');

    // 메뉴 구성 (순서대로)
    $menu['menu400'] = array(
        // 1. 커뮤니티 관리 (메인)
        array('400000', '커뮤니티관리', G5_ADMIN_URL . '/config_community.php', 'community'),

        // 2. 기본 설정
        array('400100', '기본설정', G5_ADMIN_URL . '/config_community.php?tab=setup', 'community_config'),

        // 2-1. 기타 관리 (QnA / 정산 / 활동량)
        array('400150', '기타관리', G5_ADMIN_URL . '/config_community.php?tab=qna', 'community_qna'),

        // 3. 캐릭터 관리
        array('400200', '캐릭터관리', G5_ADMIN_URL . '/config_community.php?tab=character', 'community_character'),

        // 4. 재화 관리
        array('400300', '재화관리', G5_ADMIN_URL . '/config_community.php?tab=economy', 'community_economy'),
    );

    // 4-1. 경제 관리 (경제 활동 모듈 설치 시)
    if ($has_economy_ext_admin) {
        $menu['menu400'][] = array('400310', '경제관리', G5_ADMIN_URL . '/config_community.php?tab=economy_ext', 'community_economy_ext');
    }

    // 5. 스크립트 관리
    $menu['menu400'][] = array('400400', '스크립트관리', G5_ADMIN_URL . '/config_community.php?tab=script', 'community_script');

    // 11. 로비 관리 (로비 확장팩 설치 시) — 스퀘어 관리도 하위 탭으로 포함
    if (file_exists(G5_PATH . '/extend/lobby_config.php')) {
        $menu['menu400'][] = array('400950', '로비관리', G5_ADMIN_URL . '/community/lobby/', 'community_lobby');
    }

    // 도감 관리 (도감 확장팩 설치 시) — community/lib/codex.lib.php가 코어 모듈 마커
    if (file_exists(G5_PATH . '/community/lib/codex.lib.php')) {
        $menu['menu400'][] = array('400410', '도감관리', G5_ADMIN_URL . '/community/codex/', 'community_codex');
    }

    // 마이룸 관리 (모험팩 의존)
    if (file_exists(G5_PATH . '/extend/tilemap_config.php')) {
        $menu['menu400'][] = array('400420', '마이룸관리', G5_ADMIN_URL . '/community/myroom/', 'community_myroom');
    }

    // 퀘스트 관리 (퀘스트 확장팩 설치 시)
    if (file_exists(G5_PATH . '/extend/quest_config.php')) {
        $menu['menu400'][] = array('400350', '퀘스트관리', G5_ADMIN_URL . '/config_community.php?tab=quest', 'community_quest');
    }

    // 필드 관리 (필드 확장팩 설치 시)
    if (file_exists(G5_PATH . '/extend/field_config.php')) {
        $menu['menu400'][] = array('400500', '필드관리', G5_ADMIN_URL . '/config_community.php?tab=field', 'community_field');
    }

    // 모험 관리 (모험 확장팩 설치 시) — 필드의 비주얼 스킨
    if (file_exists(G5_PATH . '/extend/tilemap_config.php')) {
        $menu['menu400'][] = array('400550', '모험관리', G5_ADMIN_URL . '/community/tilemap/', 'community_tilemap');
    }

    // 7. 던전 관리 (던전 확장팩 설치 시)
    if (file_exists(G5_PATH . '/extend/dungeon_config.php')) {
        $menu['menu400'][] = array('400600', '던전관리', G5_ADMIN_URL . '/community/dungeon/', 'community_dungeon');
    }

    // 8. 레이드 관리 (레이드 확장팩 설치 시)
    if (file_exists(G5_PATH . '/extend/raid_config.php')) {
        $menu['menu400'][] = array('400800', '레이드관리', G5_ADMIN_URL . '/community/raid/', 'community_raid');
    }

    // 9. 전투 관리 (전투 확장팩 설치 시)
    if (file_exists(G5_PATH . '/extend/combat_config.php')) {
        $menu['menu400'][] = array('400900', '전투관리', G5_ADMIN_URL . '/community/combat/', 'community_combat');
    }

    // 10. 탐험 관리 (탐험 확장팩 설치 시)
    // sub_menu ID는 400대(앞 3자리 '400')로 두어야 menu400 그룹 활성화(substr 비교) 로직과 매칭됨
    if (file_exists(G5_PATH . '/extend/tile_config.php')) {
        $menu['menu400'][] = array('400700', '탐험관리', G5_ADMIN_URL . '/community/tile/', 'community_tile');
    }

}
// 커뮤니티 모듈이 없으면 menu400은 등록되지 않음 (메뉴 자동 숨김)
?>
