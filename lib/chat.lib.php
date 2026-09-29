<?php
/**
 * RA0 Edition 채팅 시스템 라이브러리
 * 기존 쪽지(g5_memo) 대체용 실시간 채팅
 */

if (!defined('_GNUBOARD_')) exit;

// 테이블 정의
global $g5;
$g5['chat_room_table'] = G5_TABLE_PREFIX . 'chat_room';
$g5['chat_member_table'] = G5_TABLE_PREFIX . 'chat_member';
$g5['chat_message_table'] = G5_TABLE_PREFIX . 'chat_message';
$g5['chat_config_table'] = G5_TABLE_PREFIX . 'chat_config';
$g5['chat_ban_table'] = G5_TABLE_PREFIX . 'chat_ban';

// 기본 설정값 (DB에 없을 경우 사용)
define('CHAT_DEFAULT_MAX_MEMBERS', 50);
define('CHAT_DEFAULT_COOLDOWN', 3);
define('CHAT_DEFAULT_MAX_LENGTH', 2000);
define('CHAT_DEFAULT_POLLING_ACTIVE', 15000);
define('CHAT_DEFAULT_POLLING_INACTIVE', 60000);

/**
 * 채팅 설정값 가져오기 (DB에서 로드, 캐시 사용)
 *
 * @param string $key 설정 키
 * @param mixed $default 기본값
 * @return mixed 설정값
 */
function get_chat_config($key, $default = '') {
    global $g5;
    static $chat_configs = null;

    // 테이블 존재 확인
    if (!chat_tables_exist()) {
        return $default;
    }

    // 캐시에서 로드
    if ($chat_configs === null) {
        $chat_configs = [];
        $result = sql_query("SELECT cf_key, cf_value FROM {$g5['chat_config_table']}", false);
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $chat_configs[$row['cf_key']] = $row['cf_value'];
            }
        }
    }

    return isset($chat_configs[$key]) ? $chat_configs[$key] : $default;
}

/**
 * 채팅 테이블 존재 여부 확인
 */
function chat_tables_exist() {
    global $g5;
    static $exists = null;

    if ($exists !== null) {
        return $exists;
    }

    $result = sql_query("SHOW TABLES LIKE '{$g5['chat_room_table']}'", false);
    $exists = ($result && sql_num_rows($result) > 0);

    return $exists;
}

/**
 * 채팅 참여자 테이블의 누락 컬럼 보강
 *
 * 설치/관리 화면과 채팅 전체 페이지 진입에서 호출한다. 반복 폴링에서는 실행하지 않는다.
 */
function chat_migrate_member_schema() {
    global $g5;

    if (!chat_tables_exist()) return false;

    $column = sql_query("SHOW COLUMNS FROM `{$g5['chat_member_table']}` LIKE 'cm_last_poll'", false);
    if ($column && sql_num_rows($column) > 0) {
        return true;
    }

    sql_query("ALTER TABLE `{$g5['chat_member_table']}` ADD `cm_last_poll` DATETIME DEFAULT NULL AFTER `cm_notify`", false);

    $column = sql_query("SHOW COLUMNS FROM `{$g5['chat_member_table']}` LIKE 'cm_last_poll'", false);
    return (bool)($column && sql_num_rows($column) > 0);
}

/**
 * 채팅 기능 활성화 여부
 */
function is_chat_enabled() {
    return get_chat_config('chat_enabled', '1') === '1';
}

/**
 * 채팅 금지 여부 체크
 *
 * @param string $mb_id 회원 ID
 * @return bool 금지 상태면 true
 */
function is_chat_banned($mb_id) {
    global $g5;

    if (!chat_tables_exist() || !$mb_id) {
        return false;
    }

    $now = G5_TIME_YMDHIS;
    $sql = "SELECT cb_id FROM {$g5['chat_ban_table']}
            WHERE mb_id = '" . sql_real_escape_string($mb_id) . "'
            AND cb_start <= '{$now}'
            AND (cb_end IS NULL OR cb_end > '{$now}')";
    $row = sql_fetch($sql);

    return !empty($row['cb_id']);
}

/**
 * 채팅 금지 정보 조회
 *
 * @param string $mb_id 회원 ID
 * @return array|false 금지 정보 또는 false
 */
function get_chat_ban_info($mb_id) {
    global $g5;

    if (!chat_tables_exist() || !$mb_id) {
        return false;
    }

    $now = G5_TIME_YMDHIS;
    $sql = "SELECT * FROM {$g5['chat_ban_table']}
            WHERE mb_id = '" . sql_real_escape_string($mb_id) . "'
            AND cb_start <= '{$now}'
            AND (cb_end IS NULL OR cb_end > '{$now}')";
    $row = sql_fetch($sql);

    return $row['cb_id'] ? $row : false;
}

/**
 * 채팅방 개설 권한 체크
 *
 * @param array $member 회원 정보 배열
 * @return bool 개설 가능하면 true
 */
function can_create_chat_room($member) {
    // 비로그인
    if (empty($member['mb_id'])) {
        return false;
    }

    // 테이블 미설치
    if (!chat_tables_exist()) {
        return false;
    }

    // 관리자는 항상 가능
    if ($member['mb_level'] >= 10) {
        return true;
    }

    // 채팅 금지 상태면 불가
    if (is_chat_banned($member['mb_id'])) {
        return false;
    }

    // 채팅 기능 비활성화면 불가
    if (!is_chat_enabled()) {
        return false;
    }

    // 레벨 체크
    $required_level = (int)get_chat_config('chat_create_level', 1);
    return $member['mb_level'] >= $required_level;
}

/**
 * 채팅 테이블 설치
 */
function chat_install_tables() {
    global $g5;

    // 채팅방 테이블
    $sql = "CREATE TABLE IF NOT EXISTS `{$g5['chat_room_table']}` (
        `cr_id` INT(11) NOT NULL AUTO_INCREMENT,
        `cr_type` ENUM('private', 'group') NOT NULL DEFAULT 'private',
        `cr_name` VARCHAR(100) NOT NULL DEFAULT '',
        `cr_creator_mb_id` VARCHAR(20) NOT NULL DEFAULT '',
        `cr_max_members` INT(11) NOT NULL DEFAULT 50,
        `cr_created_at` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `cr_last_message_at` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `cr_last_message` VARCHAR(255) NOT NULL DEFAULT '',
        PRIMARY KEY (`cr_id`),
        KEY `idx_creator` (`cr_creator_mb_id`),
        KEY `idx_last_message` (`cr_last_message_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    sql_query($sql, false);

    // 참여자 테이블
    $sql = "CREATE TABLE IF NOT EXISTS `{$g5['chat_member_table']}` (
        `cm_id` INT(11) NOT NULL AUTO_INCREMENT,
        `cr_id` INT(11) NOT NULL,
        `mb_id` VARCHAR(20) NOT NULL,
        `cm_joined_at` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        `cm_last_read_id` INT(11) NOT NULL DEFAULT 0,
        `cm_notify` TINYINT(1) NOT NULL DEFAULT 1,
        `cm_last_poll` DATETIME DEFAULT NULL,
        `cm_left_at` DATETIME DEFAULT NULL,
        PRIMARY KEY (`cm_id`),
        UNIQUE KEY `idx_room_member` (`cr_id`, `mb_id`),
        KEY `idx_member` (`mb_id`),
        KEY `idx_member_active` (`mb_id`, `cm_left_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    sql_query($sql, false);

    // 기존 설치에서 누락된 활동 시각 컬럼 보강
    chat_migrate_member_schema();

    // 메시지 테이블
    $sql = "CREATE TABLE IF NOT EXISTS `{$g5['chat_message_table']}` (
        `msg_id` INT(11) NOT NULL AUTO_INCREMENT,
        `cr_id` INT(11) NOT NULL,
        `mb_id` VARCHAR(20) NOT NULL,
        `msg_type` ENUM('text', 'image', 'file', 'system') NOT NULL DEFAULT 'text',
        `msg_content` TEXT NOT NULL,
        `msg_created_at` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY (`msg_id`),
        KEY `idx_room_message` (`cr_id`, `msg_id`),
        KEY `idx_room_time` (`cr_id`, `msg_created_at`),
        KEY `idx_sender` (`mb_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    sql_query($sql, false);

    return true;
}

/**
 * 테이블 존재 여부 확인 (자동 생성 제거 - 관리자 페이지에서 설치)
 */
function chat_check_tables() {
    return chat_tables_exist();
}

/**
 * 기존 1:1 채팅방 찾기 (있으면 ID, 없으면 false)
 *
 * @param string $mb_id1 회원1
 * @param string $mb_id2 회원2
 * @return int|false 채팅방 ID 또는 false
 */
function chat_find_private_room($mb_id1, $mb_id2) {
    global $g5;

    if (!$mb_id1 || !$mb_id2 || $mb_id1 == $mb_id2) {
        return false;
    }

    if (!chat_tables_exist()) {
        return false;
    }

    $sql = "SELECT cr.cr_id
            FROM {$g5['chat_room_table']} cr
            INNER JOIN {$g5['chat_member_table']} cm1 ON cr.cr_id = cm1.cr_id AND cm1.mb_id = '".sql_real_escape_string($mb_id1)."'
            INNER JOIN {$g5['chat_member_table']} cm2 ON cr.cr_id = cm2.cr_id AND cm2.mb_id = '".sql_real_escape_string($mb_id2)."'
            WHERE cr.cr_type = 'private'
            AND cm1.cm_left_at IS NULL
            AND cm2.cm_left_at IS NULL
            LIMIT 1";

    $row = sql_fetch($sql);

    return !empty($row['cr_id']) ? (int)$row['cr_id'] : false;
}

/**
 * 1:1 대화하기 링크 정보 반환
 *
 * @param string $my_mb_id   내 회원 ID
 * @param string $target_mb_id 상대 회원 ID
 * @return array|false  ['url' => 채팅 URL, 'exists' => 기존방 여부] 또는 권한 없으면 false
 */
function chat_get_private_link($my_mb_id, $target_mb_id) {
    global $member;

    if (!$my_mb_id || !$target_mb_id || $my_mb_id == $target_mb_id) {
        return false;
    }

    if (!can_create_chat_room($member)) {
        return false;
    }

    $cr_id = chat_find_private_room($my_mb_id, $target_mb_id);

    if ($cr_id) {
        return [
            'url' => G5_BBS_URL . '/chat.php?cr_id=' . $cr_id,
            'exists' => true
        ];
    }

    return [
        'url' => G5_BBS_URL . '/chat.php?mb_id=' . urlencode($target_mb_id),
        'exists' => false
    ];
}

/**
 * 1:1 채팅방 찾기 또는 생성
 *
 * @param string $mb_id1 회원1
 * @param string $mb_id2 회원2
 * @return int 채팅방 ID
 */
function chat_get_or_create_private_room($mb_id1, $mb_id2) {
    global $g5;

    chat_check_tables();

    if (!$mb_id1 || !$mb_id2 || $mb_id1 == $mb_id2) {
        return false;
    }

    // 기존 1:1 채팅방 찾기
    $existing = chat_find_private_room($mb_id1, $mb_id2);
    if ($existing) {
        return $existing;
    }

    // 새 채팅방 생성
    $sql = "INSERT INTO {$g5['chat_room_table']} SET
            cr_type = 'private',
            cr_creator_mb_id = '".sql_real_escape_string($mb_id1)."',
            cr_max_members = 2,
            cr_created_at = '".G5_TIME_YMDHIS."',
            cr_last_message_at = '".G5_TIME_YMDHIS."'";
    sql_query($sql);

    $cr_id = sql_insert_id();

    // 참여자 추가
    chat_add_member($cr_id, $mb_id1);
    chat_add_member($cr_id, $mb_id2);

    // 새 채팅방 생성 알림 (상대방에게)
    chat_notify_new_room($cr_id, $mb_id1, $mb_id2);

    return $cr_id;
}

/**
 * 그룹 채팅방 생성
 *
 * @param string $creator_mb_id 생성자
 * @param string $room_name 방 이름
 * @param array $member_ids 초대할 회원 ID 배열
 * @return int|false 채팅방 ID 또는 실패
 */
function chat_create_group_room($creator_mb_id, $room_name, $member_ids = []) {
    global $g5;

    chat_check_tables();

    if (!$creator_mb_id) {
        return false;
    }

    $room_name = trim($room_name);
    if (!$room_name) {
        $room_name = '그룹 채팅';
    }

    // 최대 인원 설정 (DB에서 로드)
    $max_members = (int)get_chat_config('chat_max_members', CHAT_DEFAULT_MAX_MEMBERS);

    // 채팅방 생성
    $sql = "INSERT INTO {$g5['chat_room_table']} SET
            cr_type = 'group',
            cr_name = '".sql_real_escape_string($room_name)."',
            cr_creator_mb_id = '".sql_real_escape_string($creator_mb_id)."',
            cr_max_members = {$max_members},
            cr_created_at = '".G5_TIME_YMDHIS."',
            cr_last_message_at = '".G5_TIME_YMDHIS."'";
    sql_query($sql);

    $cr_id = sql_insert_id();

    // 생성자 추가
    chat_add_member($cr_id, $creator_mb_id);

    // 초대 멤버 추가
    foreach ($member_ids as $mb_id) {
        if ($mb_id != $creator_mb_id) {
            chat_add_member($cr_id, $mb_id);
        }
    }

    // 시스템 메시지
    chat_send_system_message($cr_id, '채팅방이 생성되었습니다.');

    return $cr_id;
}

/**
 * 채팅방에 멤버 추가
 */
function chat_add_member($cr_id, $mb_id) {
    global $g5;

    $cr_id = (int)$cr_id;

    // 이미 참여중인지 확인
    $sql = "SELECT cm_id, cm_left_at FROM {$g5['chat_member_table']}
            WHERE cr_id = {$cr_id} AND mb_id = '".sql_real_escape_string($mb_id)."'";
    $row = sql_fetch($sql);

    if ($row['cm_id']) {
        // 퇴장했던 멤버면 다시 활성화
        if ($row['cm_left_at']) {
            sql_query("UPDATE {$g5['chat_member_table']} SET cm_left_at = NULL, cm_joined_at = '".G5_TIME_YMDHIS."' WHERE cm_id = {$row['cm_id']}");
        }
        return $row['cm_id'];
    }

    // 새로 추가
    $sql = "INSERT INTO {$g5['chat_member_table']} SET
            cr_id = {$cr_id},
            mb_id = '".sql_real_escape_string($mb_id)."',
            cm_joined_at = '".G5_TIME_YMDHIS."',
            cm_notify = 1";
    sql_query($sql);

    return sql_insert_id();
}

/**
 * 채팅방 나가기
 */
function chat_leave_room($cr_id, $mb_id) {
    global $g5;

    $cr_id = (int)$cr_id;

    $sql = "UPDATE {$g5['chat_member_table']} SET
            cm_left_at = '".G5_TIME_YMDHIS."'
            WHERE cr_id = {$cr_id}
            AND mb_id = '".sql_real_escape_string($mb_id)."'";
    sql_query($sql);

    // 시스템 메시지
    $member = get_member($mb_id);
    $name = $member['mb_name'] ? $member['mb_name'] : $mb_id;
    chat_send_system_message($cr_id, $name . '님이 채팅방을 나갔습니다.');

    return true;
}

/**
 * 메시지 전송
 *
 * @param int $cr_id 채팅방 ID
 * @param string $mb_id 발신자
 * @param string $content 내용
 * @param string $type 메시지 유형 (text, image, file, system)
 * @return int|false 메시지 ID 또는 실패
 */
function chat_send_message($cr_id, $mb_id, $content, $type = 'text') {
    global $g5;

    chat_check_tables();

    $cr_id = (int)$cr_id;
    $content = trim($content);

    if (!$cr_id || !$mb_id || !$content) {
        return false;
    }

    // 채팅 기능 비활성화 체크
    if (!is_chat_enabled()) {
        return -3; // 채팅 비활성화
    }

    // 채팅 금지 체크
    if (is_chat_banned($mb_id)) {
        return -2; // 채팅 금지 상태
    }

    // 참여자인지 확인
    if (!chat_is_member($cr_id, $mb_id)) {
        return false;
    }

    // 메시지 길이 체크
    $max_length = (int)get_chat_config('chat_max_length', CHAT_DEFAULT_MAX_LENGTH);
    if (mb_strlen($content, 'UTF-8') > $max_length) {
        $content = mb_substr($content, 0, $max_length, 'UTF-8');
    }

    // 도배 방지 (세션 기반)
    $cooldown = (int)get_chat_config('chat_cooldown', CHAT_DEFAULT_COOLDOWN);
    $cooldown_key = 'chat_cooldown_' . $mb_id;
    $last_time = isset($_SESSION[$cooldown_key]) ? $_SESSION[$cooldown_key] : 0;

    if ($cooldown > 0 && time() - $last_time < $cooldown) {
        return -1; // 쿨다운 중
    }
    $_SESSION[$cooldown_key] = time();

    // XSS 방지 (줄바꿈 보존)
    $content = clean_xss_tags($content, 0, 0, 0, 0);

    // 메시지 저장
    $sql = "INSERT INTO {$g5['chat_message_table']} SET
            cr_id = {$cr_id},
            mb_id = '".sql_real_escape_string($mb_id)."',
            msg_type = '".sql_real_escape_string($type)."',
            msg_content = '".sql_real_escape_string($content)."',
            msg_created_at = '".G5_TIME_YMDHIS."'";
    sql_query($sql);

    $msg_id = sql_insert_id();

    // 채팅방 마지막 메시지 업데이트
    $preview = mb_substr(strip_tags($content), 0, 100, 'UTF-8');
    $sql = "UPDATE {$g5['chat_room_table']} SET
            cr_last_message_at = '".G5_TIME_YMDHIS."',
            cr_last_message = '".sql_real_escape_string($preview)."'
            WHERE cr_id = {$cr_id}";
    sql_query($sql);

    // 발신자의 읽음 처리
    chat_mark_read($cr_id, $mb_id, $msg_id);

    // 다른 참여자 알림은 AJAX 엔드포인트에서 활동 상태를 확인한 뒤 발송한다.

    return $msg_id;
}

/**
 * 시스템 메시지 전송
 */
function chat_send_system_message($cr_id, $content) {
    global $g5;

    $cr_id = (int)$cr_id;

    $sql = "INSERT INTO {$g5['chat_message_table']} SET
            cr_id = {$cr_id},
            mb_id = '',
            msg_type = 'system',
            msg_content = '".sql_real_escape_string($content)."',
            msg_created_at = '".G5_TIME_YMDHIS."'";
    sql_query($sql);

    return sql_insert_id();
}

/**
 * 메시지 목록 조회
 *
 * @param int $cr_id 채팅방 ID
 * @param int $last_id 이 ID 이후의 메시지만 (폴링용)
 * @param int $limit 가져올 개수
 * @param string $direction 'after' = last_id 이후, 'before' = last_id 이전
 */
function chat_get_messages($cr_id, $last_id = 0, $limit = 50, $direction = 'after') {
    global $g5;

    if (!chat_tables_exist()) return [];

    $cr_id = (int)$cr_id;
    $last_id = (int)$last_id;
    $limit = (int)$limit;

    $where = "cr_id = {$cr_id}";

    if ($last_id > 0) {
        if ($direction == 'after') {
            $where .= " AND msg_id > {$last_id}";
            $order = "ORDER BY msg_id ASC";
        } else {
            $where .= " AND msg_id < {$last_id}";
            $order = "ORDER BY msg_id DESC";
        }
    } else {
        $order = "ORDER BY msg_id DESC";
    }

    $has_community = function_exists('is_community_installed') && is_community_installed();

    if ($has_community) {
        $character_table = G5_TABLE_PREFIX . 'community_character';
        $profile_table = G5_TABLE_PREFIX . 'community_character_profile';

        $sql = "SELECT m.*, mb.mb_name, mb.mb_signature,
                       ch.ch_name, ch.ch_id,
                       cp.cp_portrait_image
                FROM {$g5['chat_message_table']} m
                LEFT JOIN {$g5['member_table']} mb ON m.mb_id = mb.mb_id
                LEFT JOIN {$character_table} ch ON m.mb_id = ch.mb_id AND ch.ch_main = 1
                LEFT JOIN {$profile_table} cp ON ch.ch_id = cp.ch_id
                WHERE {$where}
                {$order}
                LIMIT {$limit}";
    } else {
        $sql = "SELECT m.*, mb.mb_name, mb.mb_signature,
                       NULL AS ch_name, NULL AS ch_id, NULL AS cp_portrait_image
                FROM {$g5['chat_message_table']} m
                LEFT JOIN {$g5['member_table']} mb ON m.mb_id = mb.mb_id
                WHERE {$where}
                {$order}
                LIMIT {$limit}";
    }

    $result = sql_query($sql);
    $messages = [];

    while ($row = sql_fetch_array($result)) {
        // 캐릭터 초상화 > mb_signature 순으로 이미지 설정
        if ($row['cp_portrait_image']) {
            $row['mb_image_url'] = $row['cp_portrait_image'];
        } else {
            $row['mb_image_url'] = $row['mb_signature'] ?: '';
        }
        // 캐릭터 이름이 있으면 표시명으로 사용
        $row['display_name'] = $row['ch_name'] ?: ($row['mb_name'] ?: $row['mb_id']);
        $messages[] = $row;
    }

    // before 방향이면 역순 정렬 (오래된 것부터)
    if ($direction == 'before' || $last_id == 0) {
        $messages = array_reverse($messages);
    }

    return $messages;
}

/**
 * 읽음 처리
 */
function chat_mark_read($cr_id, $mb_id, $msg_id = null) {
    global $g5;

    if (!chat_tables_exist()) return false;

    $cr_id = (int)$cr_id;

    if ($msg_id === null) {
        // 가장 최근 메시지 ID 조회
        $row = sql_fetch("SELECT MAX(msg_id) as max_id FROM {$g5['chat_message_table']} WHERE cr_id = {$cr_id}");
        $msg_id = (int)$row['max_id'];
    }

    $msg_id = max(0, (int)$msg_id);
    $safe_mb_id = sql_real_escape_string($mb_id);

    // 정상 스키마에서는 읽음 ID와 활동 시각을 함께 갱신한다.
    $sql = "UPDATE {$g5['chat_member_table']} SET
            cm_last_read_id = GREATEST(COALESCE(cm_last_read_id, 0), {$msg_id}),
            cm_last_poll = NOW()
            WHERE cr_id = {$cr_id}
            AND mb_id = '{$safe_mb_id}'
            AND cm_left_at IS NULL";
    $result = sql_query($sql, false);

    // 구버전 설치에 cm_last_poll이 없어도 읽음 처리는 실패하지 않도록 fallback한다.
    if (!$result) {
        $sql = "UPDATE {$g5['chat_member_table']} SET
                cm_last_read_id = GREATEST(COALESCE(cm_last_read_id, 0), {$msg_id})
                WHERE cr_id = {$cr_id}
                AND mb_id = '{$safe_mb_id}'
                AND cm_left_at IS NULL";
        $result = sql_query($sql, false);
    }

    return (bool)$result;
}

/**
 * 새 메시지가 없는 폴링에서도 현재 채팅방을 보고 있음을 기록
 */
function chat_touch_poll($cr_id, $mb_id) {
    global $g5;

    if (!chat_tables_exist()) return false;

    $cr_id = (int)$cr_id;
    $safe_mb_id = sql_real_escape_string($mb_id);
    $sql = "UPDATE {$g5['chat_member_table']} SET cm_last_poll = NOW()
            WHERE cr_id = {$cr_id}
            AND mb_id = '{$safe_mb_id}'
            AND cm_left_at IS NULL
            AND (cm_last_poll IS NULL OR cm_last_poll < DATE_SUB(NOW(), INTERVAL 10 SECOND))";

    return (bool)sql_query($sql, false);
}

/**
 * 참여자인지 확인
 */
function chat_is_member($cr_id, $mb_id) {
    global $g5;

    if (!chat_tables_exist()) return false;

    $cr_id = (int)$cr_id;

    $sql = "SELECT cm_id FROM {$g5['chat_member_table']}
            WHERE cr_id = {$cr_id}
            AND mb_id = '".sql_real_escape_string($mb_id)."'
            AND cm_left_at IS NULL";
    $row = sql_fetch($sql);

    return !empty($row['cm_id']);
}

/**
 * 채팅방 정보 조회
 */
function chat_get_room($cr_id, $mb_id = '') {
    global $g5;

    if (!chat_tables_exist()) return false;

    $cr_id = (int)$cr_id;

    $sql = "SELECT cr.*,
            (SELECT COUNT(*) FROM {$g5['chat_member_table']} WHERE cr_id = cr.cr_id AND cm_left_at IS NULL) as member_count
            FROM {$g5['chat_room_table']} cr
            WHERE cr.cr_id = {$cr_id}";

    $room = sql_fetch($sql);

    if (!$room['cr_id']) {
        return false;
    }

    // 1:1 채팅방이면 상대방 정보 추가
    if ($room['cr_type'] == 'private' && $mb_id) {
        $has_community = function_exists('is_community_installed') && is_community_installed();

        if ($has_community) {
            $character_table = G5_TABLE_PREFIX . 'community_character';
            $profile_table = G5_TABLE_PREFIX . 'community_character_profile';

            $sql = "SELECT cm.mb_id, mb.mb_name, mb.mb_signature,
                           ch.ch_name, ch.ch_id, cp.cp_portrait_image
                    FROM {$g5['chat_member_table']} cm
                    LEFT JOIN {$g5['member_table']} mb ON cm.mb_id = mb.mb_id
                    LEFT JOIN {$character_table} ch ON cm.mb_id = ch.mb_id AND ch.ch_main = 1
                    LEFT JOIN {$profile_table} cp ON ch.ch_id = cp.ch_id
                    WHERE cm.cr_id = {$cr_id}
                    AND cm.mb_id != '".sql_real_escape_string($mb_id)."'
                    AND cm.cm_left_at IS NULL
                    LIMIT 1";
        } else {
            $sql = "SELECT cm.mb_id, mb.mb_name, mb.mb_signature,
                           NULL AS ch_name, NULL AS ch_id, NULL AS cp_portrait_image
                    FROM {$g5['chat_member_table']} cm
                    LEFT JOIN {$g5['member_table']} mb ON cm.mb_id = mb.mb_id
                    WHERE cm.cr_id = {$cr_id}
                    AND cm.mb_id != '".sql_real_escape_string($mb_id)."'
                    AND cm.cm_left_at IS NULL
                    LIMIT 1";
        }
        $other = sql_fetch($sql);

        if ($other && $other['mb_id']) {
            $room['other_mb_id'] = $other['mb_id'];
            $room['other_mb_name'] = $other['mb_name'];
            $room['other_mb_signature'] = $other['mb_signature'];
            $room['other_ch_name'] = $other['ch_name'];
            // 캐릭터 이름 우선 표시
            $room['display_name'] = $other['ch_name'] ?: ($other['mb_name'] ?: $other['mb_id']);
        }
    } else {
        $room['display_name'] = $room['cr_name'];
    }

    // display_name이 비어있으면 참여자 이름으로 대체
    if (empty($room['display_name'])) {
        $room['display_name'] = chat_build_member_names($cr_id, $mb_id);
    }

    return $room;
}

/**
 * 채팅방 참여자 이름으로 표시명 생성
 */
function chat_build_member_names($cr_id, $exclude_mb_id = '', $limit = 3) {
    global $g5;

    if (!$cr_id || !chat_tables_exist()) return '';

    $sql = "SELECT mb.mb_name, cm.mb_id
            FROM {$g5['chat_member_table']} cm
            LEFT JOIN {$g5['member_table']} mb ON cm.mb_id = mb.mb_id
            WHERE cm.cr_id = " . (int)$cr_id . "
            AND cm.cm_left_at IS NULL";
    if ($exclude_mb_id) {
        $sql .= " AND cm.mb_id != '" . sql_real_escape_string($exclude_mb_id) . "'";
    }
    $sql .= " ORDER BY cm.cm_joined_at ASC LIMIT " . ($limit + 1);

    $result = sql_query($sql);
    $names = [];
    $total = 0;

    while ($row = sql_fetch_array($result)) {
        $total++;
        if (count($names) < $limit) {
            $names[] = $row['mb_name'] ? $row['mb_name'] : $row['mb_id'];
        }
    }

    if (empty($names)) return '';

    $display = implode(', ', $names);
    if ($total > $limit) {
        $display .= ' 외 ' . ($total - $limit) . '명';
    }

    return $display;
}

/**
 * 내 채팅방 목록 조회
 */
function chat_get_my_rooms($mb_id, $page = 1, $limit = 20) {
    global $g5;

    if (!$mb_id || !chat_tables_exist()) return [];

    $offset = ($page - 1) * $limit;

    $safe_mb_id = sql_real_escape_string($mb_id);

    $sql = "SELECT cr.*, cm.cm_last_read_id, cm.cm_notify,
            (SELECT COUNT(*) FROM {$g5['chat_message_table']} unread_msg
             WHERE unread_msg.cr_id = cr.cr_id
             AND unread_msg.msg_id > COALESCE(cm.cm_last_read_id, 0)
             AND unread_msg.mb_id != '{$safe_mb_id}') as unread_count,
            (SELECT COUNT(*) FROM {$g5['chat_member_table']}
             WHERE cr_id = cr.cr_id AND cm_left_at IS NULL) as member_count
            FROM {$g5['chat_room_table']} cr
            INNER JOIN {$g5['chat_member_table']} cm ON cr.cr_id = cm.cr_id
            WHERE cm.mb_id = '{$safe_mb_id}'
            AND cm.cm_left_at IS NULL
            ORDER BY cr.cr_last_message_at DESC
            LIMIT {$offset}, {$limit}";

    $result = sql_query($sql);
    $rooms = [];

    $has_community = function_exists('is_community_installed') && is_community_installed();
    $character_table = G5_TABLE_PREFIX . 'community_character';
    $profile_table = G5_TABLE_PREFIX . 'community_character_profile';

    while ($row = sql_fetch_array($result)) {
        // 1:1 채팅방이면 상대방 정보 추가
        if ($row['cr_type'] == 'private') {
            if ($has_community) {
                $sql2 = "SELECT cm.mb_id, mb.mb_name, mb.mb_signature,
                               ch.ch_name, ch.ch_id, cp.cp_portrait_image
                        FROM {$g5['chat_member_table']} cm
                        LEFT JOIN {$g5['member_table']} mb ON cm.mb_id = mb.mb_id
                        LEFT JOIN {$character_table} ch ON cm.mb_id = ch.mb_id AND ch.ch_main = 1
                        LEFT JOIN {$profile_table} cp ON ch.ch_id = cp.ch_id
                        WHERE cm.cr_id = {$row['cr_id']}
                        AND cm.mb_id != '".sql_real_escape_string($mb_id)."'
                        AND cm.cm_left_at IS NULL
                        LIMIT 1";
            } else {
                $sql2 = "SELECT cm.mb_id, mb.mb_name, mb.mb_signature,
                               NULL AS ch_name, NULL AS ch_id, NULL AS cp_portrait_image
                        FROM {$g5['chat_member_table']} cm
                        LEFT JOIN {$g5['member_table']} mb ON cm.mb_id = mb.mb_id
                        WHERE cm.cr_id = {$row['cr_id']}
                        AND cm.mb_id != '".sql_real_escape_string($mb_id)."'
                        AND cm.cm_left_at IS NULL
                        LIMIT 1";
            }
            $other = sql_fetch($sql2);

            if ($other && $other['mb_id']) {
                $row['other_mb_id'] = $other['mb_id'];
                $row['other_mb_name'] = $other['mb_name'];
                $row['other_mb_signature'] = $other['mb_signature'];
                $row['other_ch_name'] = $other['ch_name'];
                // 캐릭터 이름 우선 표시
                $row['display_name'] = $other['ch_name'] ?: ($other['mb_name'] ?: $other['mb_id']);

                // 캐릭터 초상화 > mb_signature 순으로
                if ($other['cp_portrait_image']) {
                    $row['display_image'] = $other['cp_portrait_image'];
                } elseif ($other['mb_signature']) {
                    $row['display_image'] = $other['mb_signature'];
                }
            }
        } else {
            $row['display_name'] = $row['cr_name'];
        }

        // display_name이 비어있으면 참여자 이름으로 대체
        if (empty($row['display_name'])) {
            $row['display_name'] = chat_build_member_names($row['cr_id'], $mb_id);
        }

        $rooms[] = $row;
    }

    return $rooms;
}

/**
 * 채팅방 참여자 목록
 */
function chat_get_members($cr_id) {
    global $g5;

    if (!chat_tables_exist()) return [];

    $cr_id = (int)$cr_id;

    $has_community = function_exists('is_community_installed') && is_community_installed();

    if ($has_community) {
        $character_table = G5_TABLE_PREFIX . 'community_character';
        $profile_table = G5_TABLE_PREFIX . 'community_character_profile';

        $sql = "SELECT cm.*, mb.mb_name, mb.mb_signature, mb.mb_datetime as mb_joined,
                       ch.ch_name, ch.ch_id, cp.cp_portrait_image
                FROM {$g5['chat_member_table']} cm
                LEFT JOIN {$g5['member_table']} mb ON cm.mb_id = mb.mb_id
                LEFT JOIN {$character_table} ch ON cm.mb_id = ch.mb_id AND ch.ch_main = 1
                LEFT JOIN {$profile_table} cp ON ch.ch_id = cp.ch_id
                WHERE cm.cr_id = {$cr_id}
                AND cm.cm_left_at IS NULL
                ORDER BY cm.cm_joined_at ASC";
    } else {
        $sql = "SELECT cm.*, mb.mb_name, mb.mb_signature, mb.mb_datetime as mb_joined,
                       NULL AS ch_name, NULL AS ch_id, NULL AS cp_portrait_image
                FROM {$g5['chat_member_table']} cm
                LEFT JOIN {$g5['member_table']} mb ON cm.mb_id = mb.mb_id
                WHERE cm.cr_id = {$cr_id}
                AND cm.cm_left_at IS NULL
                ORDER BY cm.cm_joined_at ASC";
    }

    $result = sql_query($sql);
    $members = [];

    while ($row = sql_fetch_array($result)) {
        if ($row['mb_signature']) {
            $row['mb_signature_url'] = G5_DATA_URL . '/member/' . $row['mb_signature'];
        }
        $members[] = $row;
    }

    return $members;
}

/**
 * 안 읽은 전체 채팅 개수
 */
function chat_get_unread_count($mb_id, $force = false) {
    global $g5;
    static $cache = array();

    if (!$mb_id || !chat_tables_exist()) return 0;

    if (!$force && isset($cache[$mb_id])) {
        return $cache[$mb_id];
    }

    $safe_mb_id = sql_real_escape_string($mb_id);

    $sql = "SELECT SUM(unread) as total FROM (
                SELECT
                    (SELECT COUNT(*) FROM {$g5['chat_message_table']} unread_msg
                     WHERE unread_msg.cr_id = cr.cr_id
                     AND unread_msg.msg_id > COALESCE(cm.cm_last_read_id, 0)
                     AND unread_msg.mb_id != '{$safe_mb_id}') as unread
                FROM {$g5['chat_room_table']} cr
                INNER JOIN {$g5['chat_member_table']} cm ON cr.cr_id = cm.cr_id
                WHERE cm.mb_id = '{$safe_mb_id}'
                AND cm.cm_left_at IS NULL
            ) as sub";

    $row = sql_fetch($sql);

    $cache[$mb_id] = (int)($row['total'] ?? 0);
    return $cache[$mb_id];
}

/**
 * 현재 채팅방에서 생성된 일반 알림을 읽음 처리
 */
function chat_mark_room_notifications_read($cr_id, $mb_id) {
    global $g5;

    if (!$cr_id || !$mb_id) return false;

    if (!isset($g5['notifications_table'])) {
        $notification_lib = G5_LIB_PATH . '/notification.lib.php';
        if (!file_exists($notification_lib)) return false;
        include_once($notification_lib);
    }

    if (empty($g5['notifications_table'])) return false;

    $cr_id = (int)$cr_id;
    $noti_url = '/bbs/chat.php?cr_id=' . $cr_id;
    $safe_mb_id = sql_real_escape_string($mb_id);
    $safe_noti_url = sql_real_escape_string($noti_url);

    $sql = "UPDATE {$g5['notifications_table']} SET noti_read = 1
            WHERE mb_id = '{$safe_mb_id}'
            AND noti_type = 'message'
            AND noti_url = '{$safe_noti_url}'
            AND noti_read = 0";

    $result = sql_query($sql, false);
    if (!$result) return false;

    return max(0, (int)mysqli_affected_rows($g5['connect_db']));
}

/**
 * 새 채팅방 생성 알림 (1:1)
 */
function chat_notify_new_room($cr_id, $creator_mb_id, $target_mb_id) {
    if (!function_exists('create_notification')) {
        include_once(G5_LIB_PATH . '/notification.lib.php');
    }

    $creator = get_member($creator_mb_id);
    $creator_name = $creator['mb_name'] ? $creator['mb_name'] : $creator_mb_id;

    create_notification([
        'noti_type' => 'message',
        'mb_id' => $target_mb_id,
        'from_mb_id' => $creator_mb_id,
        'from_wr_name' => $creator_name,
        'noti_content' => $creator_name . '님이 새 대화를 시작했습니다.',
        'noti_url' => '/bbs/chat.php?cr_id=' . $cr_id
    ]);
}

/**
 * 다른 참여자들에게 알림 발송
 * 채팅방을 보고 있는 유저(활성 폴링 주기의 두 배 이내)는 제외
 */
function chat_notify_members($cr_id, $sender_mb_id, $content) {
    global $g5;

    if (!chat_tables_exist()) return;

    if (!function_exists('create_notification')) {
        include_once(G5_LIB_PATH . '/notification.lib.php');
    }

    $cr_id = (int)$cr_id;
    $sender = get_member($sender_mb_id);
    // 캐릭터 이름 우선 표시
    $sender_character = function_exists('get_character') ? get_character($sender_mb_id) : null;
    $sender_name = ($sender_character && $sender_character['ch_name']) ? $sender_character['ch_name'] : ($sender['mb_name'] ?: $sender_mb_id);

    // 활성 폴링 주기의 두 배 안에 응답한 참여자는 현재 방을 보고 있는 것으로 본다.
    $polling_ms = (int)get_chat_config('chat_polling_active', CHAT_DEFAULT_POLLING_ACTIVE);
    $active_seconds = max(15, (int)ceil($polling_ms / 1000) * 2);
    $sql = "SELECT mb_id FROM {$g5['chat_member_table']}
            WHERE cr_id = {$cr_id}
            AND mb_id != '".sql_real_escape_string($sender_mb_id)."'
            AND cm_left_at IS NULL
            AND cm_notify = 1
            AND (cm_last_poll IS NULL OR cm_last_poll < DATE_SUB(NOW(), INTERVAL {$active_seconds} SECOND))";

    $result = sql_query($sql, false);

    // 구버전 스키마는 관리자 마이그레이션 전까지 중복 푸시보다 알림 생략이 안전하다.
    if (!$result) return;

    $noti_url = '/bbs/chat.php?cr_id=' . $cr_id;
    $noti_table = G5_TABLE_PREFIX . 'notifications';

    while ($row = sql_fetch_array($result)) {
        // 같은 채팅방 알림 5분 쿨다운
        $recent = sql_fetch("SELECT noti_id FROM {$noti_table}
            WHERE mb_id = '".sql_real_escape_string($row['mb_id'])."'
            AND noti_url = '".sql_real_escape_string($noti_url)."'
            AND noti_datetime > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            LIMIT 1", false);
        if (!empty($recent['noti_id'])) continue;

        create_notification([
            'noti_type' => 'message',
            'mb_id' => $row['mb_id'],
            'from_mb_id' => $sender_mb_id,
            'from_wr_name' => $sender_name,
            'noti_content' => mb_substr($content, 0, 100, 'UTF-8'),
            'noti_url' => $noti_url
        ]);
    }
}

/**
 * 회원 검색 (초대용)
 */
function chat_search_members($keyword, $exclude_mb_ids = [], $limit = 20) {
    global $g5;

    $keyword_escaped = sql_real_escape_string($keyword);

    $exclude_sql = '';
    if (!empty($exclude_mb_ids)) {
        $exclude_list = array_map(function($id) {
            return "'" . sql_real_escape_string($id) . "'";
        }, $exclude_mb_ids);
        $exclude_sql = "AND mb.mb_id NOT IN (" . implode(',', $exclude_list) . ")";
    }

    // 커뮤니티 팩 설치 시에만 캐릭터 JOIN
    $has_community = function_exists('is_community_installed') && is_community_installed();

    if ($has_community) {
        $character_table = G5_TABLE_PREFIX . 'community_character';
        $profile_table = G5_TABLE_PREFIX . 'community_character_profile';

        $sql = "SELECT mb.mb_id, mb.mb_name, mb.mb_signature,
                       ch.ch_name, ch.ch_id, cp.cp_portrait_image
                FROM {$g5['member_table']} mb
                LEFT JOIN {$character_table} ch ON mb.mb_id = ch.mb_id AND ch.ch_main = 1
                LEFT JOIN {$profile_table} cp ON ch.ch_id = cp.ch_id
                WHERE (mb.mb_id LIKE '%{$keyword_escaped}%' OR mb.mb_name LIKE '%{$keyword_escaped}%' OR ch.ch_name LIKE '%{$keyword_escaped}%')
                {$exclude_sql}
                ORDER BY mb.mb_name ASC
                LIMIT {$limit}";
    } else {
        $sql = "SELECT mb.mb_id, mb.mb_name, mb.mb_signature,
                       NULL AS ch_name, NULL AS ch_id, NULL AS cp_portrait_image
                FROM {$g5['member_table']} mb
                WHERE (mb.mb_id LIKE '%{$keyword_escaped}%' OR mb.mb_name LIKE '%{$keyword_escaped}%')
                {$exclude_sql}
                ORDER BY mb.mb_name ASC
                LIMIT {$limit}";
    }

    $result = sql_query($sql);
    $members = [];

    while ($row = sql_fetch_array($result)) {
        if (!empty($row['cp_portrait_image'])) {
            $row['mb_signature_url'] = $row['cp_portrait_image'];
        } elseif (!empty($row['mb_signature'])) {
            $row['mb_signature_url'] = G5_DATA_URL . '/member/' . $row['mb_signature'];
        }
        $members[] = $row;
    }

    return $members;
}

/**
 * 그룹 채팅방 이름 변경
 */
function chat_rename_room($cr_id, $mb_id, $new_name) {
    global $g5;

    $cr_id = (int)$cr_id;
    $room = chat_get_room($cr_id);

    if (!$room || $room['cr_type'] != 'group') {
        return false;
    }

    // 방장만 이름 변경 가능
    if ($room['cr_creator_mb_id'] != $mb_id) {
        return false;
    }

    $sql = "UPDATE {$g5['chat_room_table']} SET
            cr_name = '".sql_real_escape_string($new_name)."'
            WHERE cr_id = {$cr_id}";
    sql_query($sql);

    chat_send_system_message($cr_id, '채팅방 이름이 변경되었습니다.');

    return true;
}

/**
 * 그룹 채팅방에 멤버 초대
 */
function chat_invite_members($cr_id, $inviter_mb_id, $invite_mb_ids) {
    global $g5;

    $cr_id = (int)$cr_id;
    $room = chat_get_room($cr_id);

    if (!$room || $room['cr_type'] != 'group') {
        return false;
    }

    // 참여자 수 체크
    $current_count = $room['member_count'];
    $invite_count = count($invite_mb_ids);

    if ($current_count + $invite_count > $room['cr_max_members']) {
        return false;
    }

    $inviter = get_member($inviter_mb_id);
    $inviter_name = $inviter['mb_name'] ? $inviter['mb_name'] : $inviter_mb_id;

    $invited_names = [];
    foreach ($invite_mb_ids as $mb_id) {
        chat_add_member($cr_id, $mb_id);
        $m = get_member($mb_id);
        $invited_names[] = $m['mb_name'] ? $m['mb_name'] : $mb_id;
    }

    // 시스템 메시지
    $names = implode(', ', $invited_names);
    chat_send_system_message($cr_id, $inviter_name . '님이 ' . $names . '님을 초대했습니다.');

    return true;
}
