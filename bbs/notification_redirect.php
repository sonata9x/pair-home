<?php
/**
 * 알림 클릭 처리 전용 엔드포인트
 *
 * 알림을 읽음 처리하고 해당 URL로 리다이렉트
 */

include_once('./_common.php');
include_once(G5_LIB_PATH.'/notification.lib.php');

// 로그인 체크
if (!$member['mb_id']) {
    alert('로그인이 필요합니다.', G5_BBS_URL.'/login.php');
}

// 알림 ID 확인
$noti_id = isset($_GET['noti_id']) ? (int)$_GET['noti_id'] : 0;

if (!$noti_id) {
    alert('잘못된 접근입니다.');
}

// 알림 정보 조회
$notification = sql_fetch("SELECT * FROM {$g5['notifications_table']}
                           WHERE noti_id = '{$noti_id}'
                           AND mb_id = '".sql_real_escape_string($member['mb_id'])."'");

if (!$notification) {
    alert('알림을 찾을 수 없습니다.');
}

// 알림 읽음 처리
mark_notification_read($noti_id, $member['mb_id']);

// URL 검증 및 리다이렉트
$redirect_url = $notification['noti_url'];

// URL이 없으면 메인으로
if (empty($redirect_url)) {
    // iframe_skip 파라미터 추가
    $redirect_url = G5_URL . '/main.php?iframe_skip=1';
    goto_url($redirect_url);
}

// iframe_skip 파라미터 추가 (아직 없는 경우만)
if (strpos($redirect_url, 'iframe_skip') === false) {
    if (strpos($redirect_url, '?') !== false) {
        $redirect_url .= '&iframe_skip=1';
    } else {
        $redirect_url .= '?iframe_skip=1';
    }
}

// 게시판/게시글 존재 여부 확인 (bo_table이 있는 경우)
if (!empty($notification['bo_table'])) {
    $bo_table = $notification['bo_table'];

    // 게시판 존재 확인
    $board = sql_fetch("SELECT bo_table FROM {$g5['board_table']} WHERE bo_table = '".sql_real_escape_string($bo_table)."'");

    if (!$board) {
        // 게시판이 없으면 메인으로
        alert('게시판이 존재하지 않습니다.', G5_URL . '/main.php?iframe_skip=1');
    }

    // 게시글 존재 확인
    // URL에서 실제 접근할 wr_id 추출 (가장 정확한 방법)
    $write_table = $g5['write_prefix'] . $bo_table;
    $check_id = null;

    // URL에서 wr_id 파라미터 추출
    if (preg_match('/[?&]wr_id=(\d+)/', $redirect_url, $matches)) {
        $check_id = (int)$matches[1];
    }

    // URL에서 추출 실패 시 DB 데이터 사용
    if (!$check_id) {
        if (!empty($notification['wr_id'])) {
            $check_id = $notification['wr_id'];
        } else if (!empty($notification['wr_parent'])) {
            $check_id = $notification['wr_parent'];
        }
    }

    // 게시글 존재 확인
    if ($check_id) {
        $write = sql_fetch("SELECT wr_id FROM {$write_table} WHERE wr_id = '{$check_id}'");

        if (!$write) {
            // 게시글이 없으면 해당 게시판으로
            alert('게시글이 삭제되었습니다.', G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&iframe_skip=1');
        }
    }
}

// URL이 내부인지 외부인지 판단
$is_internal = false;

// 상대 경로면 내부 URL
if (strpos($redirect_url, 'http') !== 0) {
    $is_internal = true;
} else {
    // 절대 경로: 현재 도메인과 비교
    $current_host = $_SERVER['HTTP_HOST'];
    $redirect_host = parse_url($redirect_url, PHP_URL_HOST);

    if ($redirect_host === $current_host) {
        $is_internal = true;
    }
}

// 내부 URL: iframe 내부에서 이동
// 외부 URL: 상위 프레임에서 새 창으로 열기
if ($is_internal) {
    goto_url($redirect_url);
} else {
    // 외부 URL은 새 창으로 열기
    ?>
    <script>
    window.open('<?php echo $redirect_url; ?>', '_blank');
    // 현재 창은 메인으로 이동
    location.replace('<?php echo G5_URL; ?>/main.php?iframe_skip=1');
    </script>
    <?php
    exit;
}
?>
