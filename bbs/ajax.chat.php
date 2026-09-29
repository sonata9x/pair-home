<?php
/**
 * RA0 Edition 채팅 API
 * AJAX 엔드포인트
 */

include_once('./_common.php');
include_once(G5_LIB_PATH . '/chat.lib.php');

// PHP 경고/출력이 JSON을 오염하지 않도록 버퍼링
ob_start();
error_reporting(0);

header('Content-Type: application/json; charset=utf-8');

// JSON 응답 헬퍼 (버퍼 정리 후 출력)
function chat_json($data) {
    ob_clean();
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// 로그인 체크
if (!$member['mb_id']) {
    chat_json(['success' => false, 'error' => '로그인이 필요합니다.']);
}

$mb_id = $member['mb_id'];
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {

    /**
     * 채팅방 목록 조회
     */
    case 'get_rooms':
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $rooms = chat_get_my_rooms($mb_id, $page);

        chat_json([
            'success' => true,
            'rooms' => $rooms,
            'unread_total' => chat_get_unread_count($mb_id)
        ]);
        break;

    /**
     * 채팅방 정보 조회
     */
    case 'get_room':
        $cr_id = isset($_GET['cr_id']) ? (int)$_GET['cr_id'] : 0;

        if (!$cr_id) {
            chat_json(['success' => false, 'error' => '채팅방 ID가 필요합니다.']);
        }

        // 참여자 체크
        if (!chat_is_member($cr_id, $mb_id)) {
            chat_json(['success' => false, 'error' => '참여 중인 채팅방이 아닙니다.']);
        }

        $room = chat_get_room($cr_id, $mb_id);
        $members = chat_get_members($cr_id);

        chat_json([
            'success' => true,
            'room' => $room,
            'members' => $members
        ]);
        break;

    /**
     * 메시지 목록 조회 (폴링)
     */
    case 'get_messages':
        $cr_id = isset($_GET['cr_id']) ? (int)$_GET['cr_id'] : 0;
        $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
        $limit = isset($_GET['limit']) ? max(1, min((int)$_GET['limit'], 100)) : 50;
        $direction = (isset($_GET['direction']) && $_GET['direction'] === 'before') ? 'before' : 'after';

        if (!$cr_id) {
            chat_json(['success' => false, 'error' => '채팅방 ID가 필요합니다.']);
        }

        // 참여자 체크
        if (!chat_is_member($cr_id, $mb_id)) {
            chat_json(['success' => false, 'error' => '참여 중인 채팅방이 아닙니다.']);
        }

        $messages = chat_get_messages($cr_id, $last_id, $limit, $direction);

        // 읽음 처리 및 현재 방 활동 시각 갱신
        if (!empty($messages)) {
            $max_id = max(array_column($messages, 'msg_id'));
            $read_success = chat_mark_read($cr_id, $mb_id, $max_id);
        } else {
            $read_success = chat_touch_poll($cr_id, $mb_id);
        }

        $response = [
            'success' => true,
            'messages' => $messages,
            'count' => count($messages),
            'read_success' => $read_success
        ];

        // 알림 생성과 메시지 폴링이 엇갈려도 다음 빈 폴링에서 현재 방 알림을 정리한다.
        $cleared_notifications = false;
        if ($direction === 'after') {
            $cleared_notifications = chat_mark_room_notifications_read($cr_id, $mb_id);
        }

        // 첫 진입, 새 메시지 수신, 늦게 생성된 알림 정리 시에만 COUNT를 다시 계산한다.
        $should_sync_unread = ($direction === 'after' && (
            $last_id === 0
            || !empty($messages)
            || (is_int($cleared_notifications) && $cleared_notifications > 0)
        ));
        if ($should_sync_unread) {
            $response['unread_total'] = chat_get_unread_count($mb_id, true);

            if (function_exists('get_notification_count')) {
                $response['notification_unread_total'] = get_notification_count($mb_id, true);
            }
        }

        chat_json($response);
        break;

    /**
     * 메시지 전송
     */
    case 'send_message':
        $cr_id = isset($_POST['cr_id']) ? (int)$_POST['cr_id'] : 0;
        $content = isset($_POST['content']) ? stripslashes(trim($_POST['content'])) : '';
        $msg_type = isset($_POST['msg_type']) ? $_POST['msg_type'] : 'text';

        if (!$cr_id || !$content) {
            chat_json(['success' => false, 'error' => '채팅방 ID와 내용이 필요합니다.']);
        }

        // 메시지 길이 제한 (DB 설정에서 로드)
        $max_length = (int)get_chat_config('chat_max_length', CHAT_DEFAULT_MAX_LENGTH);
        if (mb_strlen($content, 'UTF-8') > $max_length) {
            chat_json(['success' => false, 'error' => '메시지는 ' . number_format($max_length) . '자까지 입력 가능합니다.']);
        }

        $msg_id = chat_send_message($cr_id, $mb_id, $content, $msg_type);

        if ($msg_id === false) {
            chat_json(['success' => false, 'error' => '메시지 전송에 실패했습니다.']);
        }

        if ($msg_id === -1) {
            $cooldown = (int)get_chat_config('chat_cooldown', CHAT_DEFAULT_COOLDOWN);
            chat_json(['success' => false, 'error' => '메시지는 ' . $cooldown . '초에 한 번만 보낼 수 있습니다.', 'cooldown' => true]);
        }

        if ($msg_id === -2) {
            $ban_info = get_chat_ban_info($mb_id);
            $msg = '채팅이 금지된 상태입니다.';
            if ($ban_info && $ban_info['cb_end']) {
                $msg .= ' (' . substr($ban_info['cb_end'], 0, 10) . '까지)';
            }
            chat_json(['success' => false, 'error' => $msg, 'banned' => true]);
        }

        if ($msg_id === -3) {
            chat_json(['success' => false, 'error' => '채팅 기능이 비활성화되어 있습니다.', 'disabled' => true]);
        }

        // 채팅방을 보고 있지 않은 참여자에게 알림
        chat_notify_members($cr_id, $mb_id, $content);

        chat_json([
            'success' => true,
            'msg_id' => $msg_id
        ]);
        break;

    /**
     * 1:1 채팅 시작 (방 생성 또는 기존 방 반환)
     */
    case 'start_private':
        $target_mb_id = isset($_POST['target_mb_id']) ? trim($_POST['target_mb_id']) : '';

        if (!$target_mb_id) {
            chat_json(['success' => false, 'error' => '대화 상대를 선택해주세요.']);
        }

        if ($target_mb_id == $mb_id) {
            chat_json(['success' => false, 'error' => '자기 자신과는 대화할 수 없습니다.']);
        }

        // 상대방 존재 확인
        $target = get_member($target_mb_id);
        if (!$target['mb_id']) {
            chat_json(['success' => false, 'error' => '존재하지 않는 회원입니다.']);
        }

        // 채팅방 개설 권한 체크 (기존 방 재사용 시에는 체크 안함)
        $existing_room = chat_find_private_room($mb_id, $target_mb_id);
        if (!$existing_room && !can_create_chat_room($member)) {
            $required_level = (int)get_chat_config('chat_create_level', 1);
            chat_json(['success' => false, 'error' => '채팅방 개설은 레벨 ' . $required_level . ' 이상만 가능합니다.', 'no_permission' => true]);
        }

        $cr_id = chat_get_or_create_private_room($mb_id, $target_mb_id);

        if (!$cr_id) {
            chat_json(['success' => false, 'error' => '채팅방 생성에 실패했습니다.']);
        }

        chat_json([
            'success' => true,
            'cr_id' => $cr_id
        ]);
        break;

    /**
     * 그룹 채팅방 생성
     */
    case 'create_group':
        // 채팅방 개설 권한 체크
        if (!can_create_chat_room($member)) {
            $required_level = (int)get_chat_config('chat_create_level', 1);
            chat_json(['success' => false, 'error' => '채팅방 개설은 레벨 ' . $required_level . ' 이상만 가능합니다.', 'no_permission' => true]);
        }

        $room_name = isset($_POST['room_name']) ? trim($_POST['room_name']) : '';
        $member_ids = isset($_POST['member_ids']) ? $_POST['member_ids'] : [];

        if (!is_array($member_ids)) {
            $member_ids = json_decode(stripslashes($member_ids), true) ?: [];
        }

        $cr_id = chat_create_group_room($mb_id, $room_name, $member_ids);

        if (!$cr_id) {
            chat_json(['success' => false, 'error' => '채팅방 생성에 실패했습니다.']);
        }

        chat_json([
            'success' => true,
            'cr_id' => $cr_id
        ]);
        break;

    /**
     * 채팅방 나가기
     */
    case 'leave_room':
        $cr_id = isset($_POST['cr_id']) ? (int)$_POST['cr_id'] : 0;

        if (!$cr_id) {
            chat_json(['success' => false, 'error' => '채팅방 ID가 필요합니다.']);
        }

        $result = chat_leave_room($cr_id, $mb_id);

        chat_json([
            'success' => $result
        ]);
        break;

    /**
     * 멤버 초대 (그룹)
     */
    case 'invite_members':
        $cr_id = isset($_POST['cr_id']) ? (int)$_POST['cr_id'] : 0;
        $member_ids = isset($_POST['member_ids']) ? $_POST['member_ids'] : [];

        if (!$cr_id) {
            chat_json(['success' => false, 'error' => '채팅방 ID가 필요합니다.']);
        }

        if (!is_array($member_ids)) {
            $member_ids = json_decode(stripslashes($member_ids), true) ?: [];
        }

        if (empty($member_ids)) {
            chat_json(['success' => false, 'error' => '초대할 멤버를 선택해주세요.']);
        }

        $result = chat_invite_members($cr_id, $mb_id, $member_ids);

        if (!$result) {
            chat_json(['success' => false, 'error' => '초대에 실패했습니다. 인원 제한을 확인해주세요.']);
        }

        chat_json([
            'success' => true
        ]);
        break;

    /**
     * 채팅방 참여자 목록
     */
    case 'get_members':
        $cr_id = isset($_GET['cr_id']) ? (int)$_GET['cr_id'] : 0;

        if (!$cr_id || !chat_is_member($cr_id, $mb_id)) {
            chat_json(['success' => false, 'message' => '권한이 없습니다.']);
        }

        $members = chat_get_members($cr_id);

        // 이미지 URL 정리
        foreach ($members as &$m) {
            if (!empty($m['cp_portrait_image'])) {
                $m['mb_image_url'] = $m['cp_portrait_image'];
            } elseif (!empty($m['mb_signature'])) {
                $m['mb_image_url'] = $m['mb_signature'];
            }
        }
        unset($m);

        chat_json([
            'success' => true,
            'members' => $members
        ]);
        break;

    /**
     * 회원 검색 (초대용)
     */
    case 'search_members':
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        $cr_id = isset($_GET['cr_id']) ? (int)$_GET['cr_id'] : 0;

        if (mb_strlen($keyword, 'UTF-8') < 1) {
            chat_json(['success' => true, 'members' => []]);
        }

        // 현재 채팅방 멤버는 제외
        $exclude = [$mb_id];
        if ($cr_id) {
            $current_members = chat_get_members($cr_id);
            foreach ($current_members as $m) {
                $exclude[] = $m['mb_id'];
            }
        }

        $keyword_escaped = sql_real_escape_string($keyword);

        // exclude 조건
        $exclude_sql = '';
        if (!empty($exclude)) {
            $exclude_list = implode("','", array_map('sql_real_escape_string', $exclude));
            $exclude_sql = " AND mb.mb_id NOT IN ('{$exclude_list}')";
        }

        // 커뮤니티 팩 설치 시에만 캐릭터 JOIN — 미설치 시 community_character 테이블이 없어 SQL fatal
        $has_community = function_exists('is_community_installed') && is_community_installed();

        if ($has_community) {
            $character_table = G5_TABLE_PREFIX . 'community_character';
            $profile_table = G5_TABLE_PREFIX . 'community_character_profile';

            $sql = "SELECT mb.mb_id, mb.mb_name, mb.mb_signature,
                           ch.ch_name, ch.ch_id,
                           cp.cp_portrait_image
                    FROM {$g5['member_table']} mb
                    LEFT JOIN {$character_table} ch ON mb.mb_id = ch.mb_id AND ch.ch_main = 1
                    LEFT JOIN {$profile_table} cp ON ch.ch_id = cp.ch_id
                    WHERE (mb.mb_id LIKE '%{$keyword_escaped}%' OR mb.mb_name LIKE '%{$keyword_escaped}%' OR ch.ch_name LIKE '%{$keyword_escaped}%')
                    AND mb.mb_leave_date = ''
                    AND mb.mb_intercept_date = ''
                    {$exclude_sql}
                    LIMIT 20";
        } else {
            $sql = "SELECT mb.mb_id, mb.mb_name, mb.mb_signature,
                           NULL AS ch_name, NULL AS ch_id, NULL AS cp_portrait_image
                    FROM {$g5['member_table']} mb
                    WHERE (mb.mb_id LIKE '%{$keyword_escaped}%' OR mb.mb_name LIKE '%{$keyword_escaped}%')
                    AND mb.mb_leave_date = ''
                    AND mb.mb_intercept_date = ''
                    {$exclude_sql}
                    LIMIT 20";
        }

        $result = sql_query($sql);
        $members = [];
        while ($row = sql_fetch_array($result)) {
            // 캐릭터 초상화 > mb_signature 순으로 이미지 설정
            if (!empty($row['cp_portrait_image'])) {
                $row['mb_image_url'] = $row['cp_portrait_image'];
            } elseif (!empty($row['mb_signature'])) {
                $row['mb_image_url'] = $row['mb_signature'];
            }
            $members[] = $row;
        }

        chat_json([
            'success' => true,
            'members' => $members
        ]);
        break;

    /**
     * 채팅방 이름 변경 (그룹)
     */
    case 'rename_room':
        $cr_id = isset($_POST['cr_id']) ? (int)$_POST['cr_id'] : 0;
        $new_name = isset($_POST['new_name']) ? trim($_POST['new_name']) : '';

        if (!$cr_id || !$new_name) {
            chat_json(['success' => false, 'error' => '채팅방 ID와 이름이 필요합니다.']);
        }

        $result = chat_rename_room($cr_id, $mb_id, $new_name);

        if (!$result) {
            chat_json(['success' => false, 'error' => '이름 변경 권한이 없거나 실패했습니다.']);
        }

        chat_json([
            'success' => true
        ]);
        break;

    /**
     * 알림 설정 변경
     */
    case 'toggle_notify':
        $cr_id = isset($_POST['cr_id']) ? (int)$_POST['cr_id'] : 0;
        $notify = isset($_POST['notify']) ? (int)$_POST['notify'] : 1;

        if (!$cr_id) {
            chat_json(['success' => false, 'error' => '채팅방 ID가 필요합니다.']);
        }

        global $g5;
        $sql = "UPDATE {$g5['chat_member_table']} SET
                cm_notify = {$notify}
                WHERE cr_id = {$cr_id}
                AND mb_id = '".sql_real_escape_string($mb_id)."'";
        sql_query($sql);

        chat_json([
            'success' => true,
            'notify' => $notify
        ]);
        break;

    /**
     * 읽음 처리
     */
    case 'mark_read':
        $cr_id = isset($_POST['cr_id']) ? (int)$_POST['cr_id'] : 0;

        if (!$cr_id || !chat_is_member($cr_id, $mb_id)) {
            chat_json(['success' => false, 'error' => '참여 중인 채팅방이 아닙니다.']);
        }

        if (!chat_mark_read($cr_id, $mb_id)) {
            chat_json(['success' => false, 'error' => '읽음 처리에 실패했습니다.']);
        }

        chat_mark_room_notifications_read($cr_id, $mb_id);

        $response = [
            'success' => true,
            'unread_total' => chat_get_unread_count($mb_id, true)
        ];

        if (function_exists('get_notification_count')) {
            $response['notification_unread_total'] = get_notification_count($mb_id, true);
        }

        chat_json($response);
        break;

    /**
     * 안 읽은 메시지 전체 개수
     */
    case 'unread_count':
        chat_json([
            'success' => true,
            'count' => chat_get_unread_count($mb_id)
        ]);
        break;

    default:
        chat_json(['success' => false, 'error' => '잘못된 요청입니다.']);
}
