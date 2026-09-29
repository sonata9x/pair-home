<?php
/**
 * 좋아요/관심 토글 AJAX 핸들러
 * 모든 게시판 스킨에서 공통 사용
 */
include_once('./_common.php');
include_once(G5_LIB_PATH.'/notification.lib.php');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['result' => 'error', 'message' => '잘못된 요청입니다.']);
    exit;
}

$bo_table = isset($_POST['bo_table']) ? clean_xss_tags($_POST['bo_table']) : '';
$wr_id = isset($_POST['wr_id']) ? (int)$_POST['wr_id'] : 0;

if (!$bo_table || !$wr_id) {
    echo json_encode(['result' => 'error', 'message' => '필수 파라미터가 누락되었습니다.']);
    exit;
}

$board = get_board_db($bo_table, true);
if (!$board['bo_table']) {
    echo json_encode(['result' => 'error', 'message' => '존재하지 않는 게시판입니다.']);
    exit;
}

$like_table = G5_TABLE_PREFIX . 'like';
$ip = $_SERVER['REMOTE_ADDR'];
$mb_id = isset($member['mb_id']) ? $member['mb_id'] : '';

$is_liked = check_liked($bo_table, $wr_id, $ip);

if ($is_liked) {
    if ($mb_id) {
        $sql = "DELETE FROM {$like_table}
                WHERE bo_table = '{$bo_table}'
                AND wr_id = '{$wr_id}'
                AND mb_id = '{$mb_id}'";
    } else {
        $sql = "DELETE FROM {$like_table}
                WHERE bo_table = '{$bo_table}'
                AND wr_id = '{$wr_id}'
                AND mb_id = ''
                AND ip = '{$ip}'";
    }
    sql_query($sql);
    $action = 'unlike';
} else {
    $sql = "INSERT INTO {$like_table} (bo_table, wr_id, mb_id, ip, liked_datetime)
            VALUES ('{$bo_table}', '{$wr_id}', '{$mb_id}', '{$ip}', NOW())";
    sql_query($sql);
    $action = 'like';

    // 글 작성자에게 알림 발송 (중복 방지: 같은 사용자가 같은 글에 좋아요 알림을 이미 보냈는지 확인)
    if (function_exists('create_notification') && $mb_id) {
        $writer_mb_id = get_post_writer_mb_id($bo_table, $wr_id);
        if ($writer_mb_id && $writer_mb_id != $mb_id) {
            // 중복 알림 확인 (같은 from_mb_id, bo_table, wr_id, noti_type='like' 조합)
            global $g5;
            $noti_table = $g5['notifications_table'];
            $existing = sql_fetch("SELECT noti_id FROM {$noti_table}
                                   WHERE noti_type = 'like'
                                   AND mb_id = '".sql_real_escape_string($writer_mb_id)."'
                                   AND from_mb_id = '".sql_real_escape_string($mb_id)."'
                                   AND bo_table = '".sql_real_escape_string($bo_table)."'
                                   AND wr_id = '{$wr_id}'");

            // 기존 알림이 없는 경우에만 새 알림 생성
            if (!$existing) {
                $from_name = '';
                if (function_exists('get_character')) {
                    $from_char = get_character($mb_id);
                    if (!empty($from_char['ch_name'])) $from_name = $from_char['ch_name'];
                }
                if (!$from_name) $from_name = isset($member['mb_name']) && $member['mb_name'] ? $member['mb_name'] : '누군가';
                create_notification([
                    'noti_type' => 'like',
                    'mb_id' => $writer_mb_id,
                    'from_mb_id' => $mb_id,
                    'from_wr_name' => $from_name,
                    'bo_table' => $bo_table,
                    'wr_id' => $wr_id,
                    'noti_content' => $from_name . '님이 회원님의 글에 관심을 표시했습니다.',
                    'noti_url' => G5_BBS_URL . '/board.php?bo_table=' . $bo_table . '&wr_id=' . $wr_id
                ]);
            }
        }
    }
}

$like_count = get_like_count($bo_table, $wr_id);

echo json_encode([
    'result' => 'success',
    'action' => $action,
    'like_count' => $like_count
]);
