<?php
include_once(__DIR__ . '/../_common.php');
include_once(G5_LIB_PATH . '/chat.lib.php');

if (!$is_admin) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => true, 'message' => '관리자만 접근할 수 있습니다.'], JSON_UNESCAPED_UNICODE);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {

    /**
     * 테이블 설치
     */
    case 'install_tables':
        $logs = [];
        $install_success = true;
        $prefix = G5_TABLE_PREFIX;

        // 1. chat_room 테이블
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}chat_room` (
            `cr_id` INT(11) NOT NULL AUTO_INCREMENT,
            `cr_type` ENUM('private', 'group') NOT NULL DEFAULT 'private' COMMENT '채팅방 유형',
            `cr_name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '채팅방 이름',
            `cr_creator_mb_id` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '생성자',
            `cr_max_members` INT(11) NOT NULL DEFAULT 50 COMMENT '최대 인원',
            `cr_created_at` DATETIME DEFAULT NULL COMMENT '생성일',
            `cr_last_message_at` DATETIME DEFAULT NULL COMMENT '마지막 메시지 시간',
            `cr_last_message` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '마지막 메시지 미리보기',
            PRIMARY KEY (`cr_id`),
            KEY `idx_type` (`cr_type`),
            KEY `idx_last_message` (`cr_last_message_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        sql_query($sql, false);
        $logs[] = 'chat_room 테이블 생성 완료';

        // 2. chat_member 테이블
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}chat_member` (
            `cm_id` INT(11) NOT NULL AUTO_INCREMENT,
            `cr_id` INT(11) NOT NULL COMMENT '채팅방 ID',
            `mb_id` VARCHAR(20) NOT NULL COMMENT '회원 ID',
            `cm_joined_at` DATETIME DEFAULT NULL COMMENT '참여일',
            `cm_last_read_id` INT(11) NOT NULL DEFAULT 0 COMMENT '마지막 읽은 메시지 ID',
            `cm_notify` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '알림 설정',
            `cm_last_poll` DATETIME DEFAULT NULL COMMENT '마지막 채팅방 활동 시각',
            `cm_left_at` DATETIME DEFAULT NULL COMMENT '퇴장일 (NULL=참여중)',
            PRIMARY KEY (`cm_id`),
            UNIQUE KEY `idx_room_member` (`cr_id`, `mb_id`),
            KEY `idx_mb_id` (`mb_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        sql_query($sql, false);
        $logs[] = 'chat_member 테이블 생성 완료';

        if (chat_migrate_member_schema()) {
            $logs[] = 'chat_member 스키마 확인 완료';
        } else {
            $logs[] = 'chat_member 스키마 보강 실패: DB 권한을 확인해주세요.';
            $install_success = false;
        }

        // 3. chat_message 테이블
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}chat_message` (
            `msg_id` INT(11) NOT NULL AUTO_INCREMENT,
            `cr_id` INT(11) NOT NULL COMMENT '채팅방 ID',
            `mb_id` VARCHAR(20) NOT NULL COMMENT '발신자',
            `msg_type` ENUM('text', 'image', 'file', 'system') NOT NULL DEFAULT 'text' COMMENT '메시지 유형',
            `msg_content` TEXT NOT NULL COMMENT '메시지 내용',
            `msg_created_at` DATETIME DEFAULT NULL COMMENT '작성일',
            PRIMARY KEY (`msg_id`),
            KEY `idx_room_message` (`cr_id`, `msg_id`),
            KEY `idx_created` (`msg_created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        sql_query($sql, false);
        $logs[] = 'chat_message 테이블 생성 완료';

        // 4. chat_config 테이블
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}chat_config` (
            `cf_id` INT(11) NOT NULL AUTO_INCREMENT,
            `cf_key` VARCHAR(50) NOT NULL COMMENT '설정 키',
            `cf_value` TEXT NOT NULL COMMENT '설정 값',
            PRIMARY KEY (`cf_id`),
            UNIQUE KEY `idx_key` (`cf_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        sql_query($sql, false);
        $logs[] = 'chat_config 테이블 생성 완료';

        // 5. chat_ban 테이블
        $sql = "CREATE TABLE IF NOT EXISTS `{$prefix}chat_ban` (
            `cb_id` INT(11) NOT NULL AUTO_INCREMENT,
            `mb_id` VARCHAR(20) NOT NULL COMMENT '금지 회원',
            `cb_reason` VARCHAR(255) DEFAULT '' COMMENT '금지 사유',
            `cb_start` DATETIME NOT NULL COMMENT '금지 시작일',
            `cb_end` DATETIME DEFAULT NULL COMMENT '금지 종료일 (NULL=영구)',
            `cb_admin_id` VARCHAR(20) NOT NULL COMMENT '처리 관리자',
            `cb_created_at` DATETIME NOT NULL COMMENT '등록일',
            PRIMARY KEY (`cb_id`),
            UNIQUE KEY `idx_mb_id` (`mb_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        sql_query($sql, false);
        $logs[] = 'chat_ban 테이블 생성 완료';

        // 6. 기본 설정값 삽입
        $default_configs = [
            ['chat_enabled', '1'],
            ['chat_cooldown', '3'],
            ['chat_max_members', '50'],
            ['chat_max_length', '2000'],
            ['chat_polling_active', '5000'],
            ['chat_polling_inactive', '30000'],
            ['chat_create_level', '1'],
        ];

        foreach ($default_configs as $cfg) {
            $sql = "INSERT IGNORE INTO `{$prefix}chat_config` (`cf_key`, `cf_value`) VALUES ('" . sql_real_escape_string($cfg[0]) . "', '" . sql_real_escape_string($cfg[1]) . "')";
            sql_query($sql, false);
        }
        $logs[] = '기본 설정값 초기화 완료';

        echo json_encode([
            'success' => $install_success,
            'logs' => $logs,
            'error' => $install_success ? '' : '채팅 테이블 스키마를 완성하지 못했습니다. DB 권한을 확인해주세요.'
        ], JSON_UNESCAPED_UNICODE);
        break;

    /**
     * 설정 저장
     */
    case 'save_config':
        $configs = [
            'chat_enabled' => isset($_POST['chat_enabled']) ? '1' : '0',
            'chat_cooldown' => isset($_POST['chat_cooldown']) ? (int)$_POST['chat_cooldown'] : 3,
            'chat_max_members' => isset($_POST['chat_max_members']) ? (int)$_POST['chat_max_members'] : 50,
            'chat_max_length' => isset($_POST['chat_max_length']) ? (int)$_POST['chat_max_length'] : 2000,
            'chat_polling_active' => isset($_POST['chat_polling_active']) ? (int)$_POST['chat_polling_active'] : 5000,
            'chat_polling_inactive' => isset($_POST['chat_polling_inactive']) ? (int)$_POST['chat_polling_inactive'] : 30000,
            'chat_create_level' => isset($_POST['chat_create_level']) ? (int)$_POST['chat_create_level'] : 1,
        ];

        $prefix = G5_TABLE_PREFIX;
        foreach ($configs as $key => $value) {
            $sql = "INSERT INTO `{$prefix}chat_config` (`cf_key`, `cf_value`) VALUES ('" . sql_real_escape_string($key) . "', '" . sql_real_escape_string($value) . "')
                    ON DUPLICATE KEY UPDATE `cf_value` = '" . sql_real_escape_string($value) . "'";
            sql_query($sql);
        }

        echo json_encode(['success' => true, 'message' => '설정이 저장되었습니다.']);
        break;

    /**
     * 채팅방 삭제
     */
    case 'delete_room':
        $cr_id = isset($_POST['cr_id']) ? (int)$_POST['cr_id'] : 0;

        if (!$cr_id) {
            die(json_encode(['success' => false, 'error' => '채팅방 ID가 필요합니다.']));
        }

        $prefix = G5_TABLE_PREFIX;

        // 메시지 삭제
        sql_query("DELETE FROM `{$prefix}chat_message` WHERE cr_id = {$cr_id}");
        // 멤버 삭제
        sql_query("DELETE FROM `{$prefix}chat_member` WHERE cr_id = {$cr_id}");
        // 채팅방 삭제
        sql_query("DELETE FROM `{$prefix}chat_room` WHERE cr_id = {$cr_id}");

        echo json_encode(['success' => true, 'message' => '채팅방이 삭제되었습니다.']);
        break;

    /**
     * 회원 강제 퇴장
     */
    case 'kick_member':
        $cr_id = isset($_POST['cr_id']) ? (int)$_POST['cr_id'] : 0;
        $mb_id = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';

        if (!$cr_id || !$mb_id) {
            die(json_encode(['success' => false, 'error' => '채팅방 ID와 회원 ID가 필요합니다.']));
        }

        $prefix = G5_TABLE_PREFIX;
        $now = date('Y-m-d H:i:s');

        sql_query("UPDATE `{$prefix}chat_member` SET cm_left_at = '{$now}' WHERE cr_id = {$cr_id} AND mb_id = '" . sql_real_escape_string($mb_id) . "'");

        echo json_encode(['success' => true, 'message' => '회원이 퇴장 처리되었습니다.']);
        break;

    /**
     * 메시지 삭제
     */
    case 'delete_messages':
        $msg_ids = isset($_POST['msg_ids']) ? $_POST['msg_ids'] : [];

        if (!is_array($msg_ids)) {
            $msg_ids = json_decode($msg_ids, true) ?: [];
        }

        if (empty($msg_ids)) {
            die(json_encode(['success' => false, 'error' => '삭제할 메시지가 없습니다.']));
        }

        $prefix = G5_TABLE_PREFIX;
        $ids = array_map('intval', $msg_ids);
        $ids_str = implode(',', $ids);

        sql_query("DELETE FROM `{$prefix}chat_message` WHERE msg_id IN ({$ids_str})");

        echo json_encode(['success' => true, 'message' => count($ids) . '개의 메시지가 삭제되었습니다.']);
        break;

    /**
     * 기간별 메시지 일괄 삭제 (cr_id 지정 시 해당 방만)
     */
    case 'delete_old_messages':
        $days = isset($_POST['days']) ? (int)$_POST['days'] : 0;
        $cr_id = isset($_POST['cr_id']) ? (int)$_POST['cr_id'] : 0;

        if ($days <= 0) {
            die(json_encode(['success' => false, 'error' => '삭제 기간을 선택해주세요.']));
        }

        $prefix = G5_TABLE_PREFIX;
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        global $g5;
        $where_room = $cr_id > 0 ? " AND cr_id = {$cr_id}" : '';
        sql_query("DELETE FROM `{$prefix}chat_message` WHERE msg_created_at < '{$date}'{$where_room}");
        $deleted = mysqli_affected_rows($g5['connect_db']);

        echo json_encode(['success' => true, 'message' => $deleted . '개의 메시지가 삭제되었습니다.']);
        break;

    /**
     * 채팅방 메시지 페이징 (더보기)
     */
    case 'load_room_messages':
        $cr_id = isset($_REQUEST['cr_id']) ? (int)$_REQUEST['cr_id'] : 0;
        $offset = isset($_REQUEST['offset']) ? max(0, (int)$_REQUEST['offset']) : 0;
        $limit = isset($_REQUEST['limit']) ? max(1, min(500, (int)$_REQUEST['limit'])) : 100;

        if (!$cr_id) {
            die(json_encode(['success' => false, 'error' => '채팅방 ID가 필요합니다.']));
        }

        $msg_keyword = isset($_REQUEST['msg_keyword']) ? trim($_REQUEST['msg_keyword']) : '';
        $msg_type = isset($_REQUEST['msg_type']) ? $_REQUEST['msg_type'] : '';
        $msg_date_start = isset($_REQUEST['msg_date_start']) ? $_REQUEST['msg_date_start'] : '';
        $msg_date_end = isset($_REQUEST['msg_date_end']) ? $_REQUEST['msg_date_end'] : '';

        $prefix = G5_TABLE_PREFIX;
        $where = "msg.cr_id = {$cr_id}";
        if ($msg_keyword !== '') {
            $where .= " AND msg.msg_content LIKE '%" . sql_real_escape_string($msg_keyword) . "%'";
        }
        if ($msg_type) {
            $where .= " AND msg.msg_type = '" . sql_real_escape_string($msg_type) . "'";
        }
        if ($msg_date_start) {
            $where .= " AND DATE(msg.msg_created_at) >= '" . sql_real_escape_string($msg_date_start) . "'";
        }
        if ($msg_date_end) {
            $where .= " AND DATE(msg.msg_created_at) <= '" . sql_real_escape_string($msg_date_end) . "'";
        }

        // 커뮤니티 확장팩이 설치된 경우에만 캐릭터 정보를 조회한다.
        $chat_has_community = function_exists('is_community_installed') && is_community_installed();
        $chat_character_table = G5_TABLE_PREFIX . 'community_character';
        $chat_character_select = $chat_has_community ? ', ch.ch_name' : ', NULL AS ch_name';
        $chat_character_join = $chat_has_community
            ? "LEFT JOIN `{$chat_character_table}` ch ON ch.mb_id = msg.mb_id AND ch.ch_main = 1"
            : '';

        $sql = "SELECT msg.msg_id, msg.cr_id, msg.mb_id, msg.msg_type, msg.msg_content, msg.msg_created_at, m.mb_name{$chat_character_select}
                FROM `{$prefix}chat_message` msg
                LEFT JOIN `{$g5['member_table']}` m ON msg.mb_id = m.mb_id
                {$chat_character_join}
                WHERE {$where}
                ORDER BY msg.msg_id DESC
                LIMIT {$offset}, {$limit}";
        $result = sql_query($sql);
        $messages = [];
        while ($row = sql_fetch_array($result)) {
            $messages[] = $row;
        }

        echo json_encode(['success' => true, 'messages' => $messages], JSON_UNESCAPED_UNICODE);
        break;

    /**
     * 채팅 금지 추가
     */
    case 'add_ban':
        $mb_id = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
        $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 0; // 0 = 영구

        if (!$mb_id) {
            die(json_encode(['success' => false, 'error' => '회원 ID가 필요합니다.']));
        }

        // 회원 존재 확인
        $mb = get_member($mb_id);
        if (!$mb['mb_id']) {
            die(json_encode(['success' => false, 'error' => '존재하지 않는 회원입니다.']));
        }

        $prefix = G5_TABLE_PREFIX;
        $now = date('Y-m-d H:i:s');
        $end = $duration > 0 ? date('Y-m-d H:i:s', strtotime("+{$duration} days")) : 'NULL';
        $end_sql = $duration > 0 ? "'{$end}'" : "NULL";

        $sql = "INSERT INTO `{$prefix}chat_ban` (`mb_id`, `cb_reason`, `cb_start`, `cb_end`, `cb_admin_id`, `cb_created_at`)
                VALUES ('" . sql_real_escape_string($mb_id) . "', '" . sql_real_escape_string($reason) . "', '{$now}', {$end_sql}, '" . sql_real_escape_string($member['mb_id']) . "', '{$now}')
                ON DUPLICATE KEY UPDATE
                `cb_reason` = '" . sql_real_escape_string($reason) . "',
                `cb_start` = '{$now}',
                `cb_end` = {$end_sql},
                `cb_admin_id` = '" . sql_real_escape_string($member['mb_id']) . "'";
        sql_query($sql);

        echo json_encode(['success' => true, 'message' => '채팅 금지가 적용되었습니다.']);
        break;

    /**
     * 채팅 금지 해제
     */
    case 'remove_ban':
        $cb_id = isset($_POST['cb_id']) ? (int)$_POST['cb_id'] : 0;

        if (!$cb_id) {
            die(json_encode(['success' => false, 'error' => '금지 ID가 필요합니다.']));
        }

        $prefix = G5_TABLE_PREFIX;
        sql_query("DELETE FROM `{$prefix}chat_ban` WHERE cb_id = {$cb_id}");

        echo json_encode(['success' => true, 'message' => '채팅 금지가 해제되었습니다.']);
        break;

    /**
     * 채팅방 이름 변경 (관리자)
     */
    case 'update_room_name':
        $cr_id = isset($_POST['cr_id']) ? (int)$_POST['cr_id'] : 0;
        $new_name = isset($_POST['cr_name']) ? stripslashes(trim($_POST['cr_name'])) : '';

        if (!$cr_id) {
            die(json_encode(['success' => false, 'error' => '채팅방 ID가 필요합니다.']));
        }

        if (mb_strlen($new_name, 'UTF-8') > 100) {
            die(json_encode(['success' => false, 'error' => '채팅방 이름은 100자 이하로 입력해주세요.']));
        }

        $prefix = G5_TABLE_PREFIX;
        $room = sql_fetch("SELECT cr_id FROM `{$prefix}chat_room` WHERE cr_id = {$cr_id}");
        if (!$room['cr_id']) {
            die(json_encode(['success' => false, 'error' => '채팅방을 찾을 수 없습니다.']));
        }

        sql_query("UPDATE `{$prefix}chat_room` SET cr_name = '".sql_real_escape_string($new_name)."' WHERE cr_id = {$cr_id}");

        echo json_encode(['success' => true, 'message' => '채팅방 이름이 변경되었습니다.', 'cr_name' => $new_name]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => '잘못된 요청입니다.']);
}
