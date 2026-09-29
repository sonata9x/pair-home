<?php
if (!defined('_GNUBOARD_')) exit;

// 알림 테이블 정의
global $g5;
$g5['notifications_table'] = G5_TABLE_PREFIX . 'notifications';
$g5['push_subscriptions_table'] = G5_TABLE_PREFIX . 'push_subscriptions';

// 알림 테이블 자동 생성
function create_notification_table() {
    global $g5;
    
    $sql = "CREATE TABLE IF NOT EXISTS `{$g5['notifications_table']}` (
        `noti_id` int(11) NOT NULL AUTO_INCREMENT,
        `noti_type` varchar(20) NOT NULL DEFAULT 'comment' COMMENT '알림 유형 (comment, reply, message, mention)',
        `mb_id` varchar(20) NOT NULL DEFAULT '' COMMENT '알림 받을 회원 ID',
        `from_mb_id` varchar(20) NOT NULL DEFAULT '' COMMENT '알림 발생시킨 회원 ID',
        `from_wr_name` varchar(255) NOT NULL DEFAULT '' COMMENT '알림 발생시킨 작성자명',
        `bo_table` varchar(20) NOT NULL DEFAULT '' COMMENT '게시판 테이블명',
        `wr_id` int(11) NOT NULL DEFAULT '0' COMMENT '관련 글 ID',
        `wr_parent` int(11) NOT NULL DEFAULT '0' COMMENT '부모글 ID',
        `noti_content` text NOT NULL COMMENT '알림 내용',
        `noti_url` varchar(255) NOT NULL DEFAULT '' COMMENT '알림 클릭시 이동 URL',
        `noti_read` tinyint(4) NOT NULL DEFAULT '0' COMMENT '읽음 여부',
        `noti_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '알림 생성 시간',
        PRIMARY KEY (`noti_id`),
        KEY `idx_mb_id` (`mb_id`, `noti_read`, `noti_datetime`),
        KEY `idx_from_mb_id` (`from_mb_id`),
        KEY `idx_board` (`bo_table`, `wr_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    sql_query($sql, false);
}

// 테이블 존재 여부 확인 함수
function check_notification_table() {
    static $checked = false;
    if (!$checked) {
        create_notification_table();
        $checked = true;
    }
}

// 알림 생성
function create_notification($data) {
    global $g5;

    check_notification_table();

    // 필수 필드 확인 (알림 받을 사람은 필수)
    if (!$data['mb_id']) {
        return false;
    }

    // 자기 자신에게는 알림 안 함 (from_mb_id가 있을 때만 체크)
    if ($data['from_mb_id'] && $data['mb_id'] == $data['from_mb_id']) {
        return false;
    }

    $sql = "INSERT INTO {$g5['notifications_table']} SET
            noti_type = '".sql_real_escape_string($data['noti_type'] ?? '')."',
            mb_id = '".sql_real_escape_string($data['mb_id'] ?? '')."',
            from_mb_id = '".sql_real_escape_string($data['from_mb_id'] ?? '')."',
            from_wr_name = '".sql_real_escape_string($data['from_wr_name'] ?? '')."',
            bo_table = '".sql_real_escape_string($data['bo_table'] ?? '')."',
            wr_id = '".(int)($data['wr_id'] ?? 0)."',
            wr_parent = '".(int)($data['wr_parent'] ?? 0)."',
            noti_content = '".sql_real_escape_string($data['noti_content'] ?? '')."',
            noti_url = '".sql_real_escape_string($data['noti_url'] ?? '')."',
            noti_datetime = '".G5_TIME_YMDHIS."'";

    $result = sql_query($sql);

    // 텔레그램 알림 발송 (관리자용)
    if ($result) {
        send_telegram_for_notification($data);
    }

    // 이메일 알림 발송 (회원용)
    if ($result) {
        send_email_for_notification($data);
    }

    // Web Push 알림 발송 (브라우저/모바일)
    if ($result) {
        send_web_push_for_notification($data);
    }

    return $result;
}

// 텔레그램 알림 발송 (조건부)
function send_telegram_for_notification($data) {
    global $g5;

    // 알림 받을 회원이 관리자인지 확인 (mb_level >= 10)
    $member = sql_fetch("SELECT mb_level FROM {$g5['member_table']} WHERE mb_id = '".sql_real_escape_string($data['mb_id'])."'");

    if (!$member || $member['mb_level'] < 10) {
        // 관리자가 아니면 텔레그램 알림 안 보냄
        return false;
    }

    // 텔레그램 설정 파일 존재 여부 확인
    $telegram_config_file = G5_ADMIN_PATH . '/notification/notification_telegram.php';
    if (!file_exists($telegram_config_file)) {
        // 텔레그램 설정이 없으면 발송 안 함
        return false;
    }

    // 텔레그램 설정 로드
    include_once($telegram_config_file);

    // 설정이 비활성화되어 있으면 발송 안 함
    if (!isset($telegram_config['enabled']) || !$telegram_config['enabled']) {
        return false;
    }

    // 알림 유형별 발송 설정 확인
    $noti_type = $data['noti_type'] ?? 'comment';

    // 하위 호환성: store_types도 체크 (order, qna, refund)
    $store_types = ['order', 'qna', 'refund'];
    if (in_array($noti_type, $store_types)) {
        // Store 알림: store_types 또는 types에서 확인
        $enabled = false;
        if (isset($telegram_config['store_types'][$noti_type])) {
            $enabled = $telegram_config['store_types'][$noti_type];
        } elseif (isset($telegram_config['types'][$noti_type])) {
            $enabled = $telegram_config['types'][$noti_type];
        }

        if (!$enabled) {
            return false;
        }
    } else {
        // 일반 알림: types에서 확인
        if (isset($telegram_config['types'][$noti_type]) && !$telegram_config['types'][$noti_type]) {
            return false;
        }
    }

    // 텔레그램 메시지 포맷
    $message = format_notification_message($data);

    // 텔레그램 발송
    return send_telegram_notification($message, $telegram_config);
}

/**
 * 텔레그램 메시지 발송 (핵심 함수)
 *
 * @param string $message 메시지 (HTML 지원)
 * @param array $telegram_config 텔레그램 설정
 * @return bool 성공 여부
 */
function send_telegram_notification($message, $telegram_config) {
    $bot_token = $telegram_config['bot_token'] ?? '';
    $chat_id = $telegram_config['chat_id'] ?? '';

    if (empty($bot_token) || empty($chat_id)) {
        return false;
    }

    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";

    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        $result = json_decode($response, true);
        return $result['ok'] ?? false;
    }

    return false;
}

/**
 * 알림 메시지 포맷팅 (유형별)
 *
 * @param array $data 알림 데이터
 * @return string 텔레그램 메시지
 */
function format_notification_message($data) {
    $noti_type = $data['noti_type'] ?? 'comment';

    // 알림 유형에 따라 포맷 함수 호출
    switch ($noti_type) {
        case 'order':
            return format_order_notification($data);
        case 'qna':
            return format_qna_notification($data);
        case 'refund':
            return format_refund_notification($data);
        default:
            return format_general_notification($data);
    }
}

/**
 * 일반 알림 포맷 (게시글, 댓글, 멘션 등)
 *
 * @param array $noti 알림 정보
 * @return string 텔레그램 메시지
 */
function format_general_notification($noti) {
    // 알림 타입별 아이콘 및 제목
    $type_info = [
        'comment' => ['icon' => '📝', 'title' => '새 게시글'],
        'reply' => ['icon' => '💬', 'title' => '새 댓글/답글'],
        'mention' => ['icon' => '👤', 'title' => '멘션'],
        'message' => ['icon' => '✉️', 'title' => '새 쪽지'],
        'like' => ['icon' => '⭐', 'title' => '관심 표시'],
        'gift' => ['icon' => '🎁', 'title' => '선물 도착'],
        'trade' => ['icon' => '🔄', 'title' => '아이템 교환']
    ];

    $type = $noti['noti_type'] ?? 'comment';
    $info = $type_info[$type] ?? ['icon' => '🔔', 'title' => '새 알림'];

    $from_name = $noti['from_wr_name'] ?? '알 수 없음';
    $from_mb_id_masked = mask_mb_id($noti['from_mb_id'] ?? '');

    $message = "{$info['icon']} <b>{$info['title']}</b>\n\n";
    $message .= "작성자: {$from_name}";

    if (!empty($noti['from_mb_id'])) {
        $message .= " ({$from_mb_id_masked})";
    }
    $message .= "\n";

    if (!empty($noti['bo_table'])) {
        $message .= "게시판: {$noti['bo_table']}\n";
    }

    // 내용 미리보기 (100자 제한)
    if (!empty($noti['noti_content'])) {
        $content_preview = mb_substr(strip_tags($noti['noti_content']), 0, 100, 'UTF-8');
        if (mb_strlen($content_preview, 'UTF-8') >= 100) {
            $content_preview .= '...';
        }
        $message .= "내용: {$content_preview}\n";
    }

    $message .= "\n";

    // 링크
    if (!empty($noti['noti_url'])) {
        // 상대 경로를 절대 경로로 변환
        $url = $noti['noti_url'];
        if (strpos($url, 'http') !== 0) {
            $url = G5_URL . $url;
        }
        $message .= "<a href=\"{$url}\">게시글 보기</a>";
    }

    return $message;
}

/**
 * 주문 발생 알림 포맷
 *
 * @param array $order 주문 정보
 * @return string 텔레그램 메시지
 */
function format_order_notification($order) {
    $mb_id_masked = mask_mb_id($order['mb_id'] ?? '');
    $admin_url = G5_ADMIN_URL . '/config_store.php?tab=orders&od_id=' . urlencode($order['od_id'] ?? '');

    $message = "🛒 <b>새 주문이 발생했습니다!</b>\n\n";
    $message .= "주문번호: <code>{$order['od_id']}</code>\n";
    $message .= "상품명: {$order['product_name']}\n";
    $message .= "구매자: {$mb_id_masked}\n";
    $message .= "금액: " . number_format($order['od_price']) . "원\n";
    $message .= "결제일시: {$order['od_paid_datetime']}\n\n";
    $message .= "<a href=\"{$admin_url}\">주문 관리</a>";

    return $message;
}

/**
 * 문의 등록 알림 포맷
 *
 * @param array $qna QnA 정보
 * @return string 텔레그램 메시지
 */
function format_qna_notification($qna) {
    $mb_id_masked = mask_mb_id($qna['mb_id'] ?? '');
    $admin_url = G5_ADMIN_URL . '/config_store.php?tab=qna&qa_id=' . (int)($qna['qa_id'] ?? 0);

    $message = "💬 <b>새 문의가 등록되었습니다!</b>\n\n";

    if (!empty($qna['product_name'])) {
        $message .= "상품: {$qna['product_name']}\n";
    }

    $message .= "제목: {$qna['qa_subject']}\n";
    $message .= "작성자: {$mb_id_masked}\n";
    $message .= "유형: " . get_qa_type_label($qna['qa_type'] ?? 'general') . "\n";
    $message .= "작성일시: {$qna['qa_datetime']}\n\n";
    $message .= "<a href=\"{$admin_url}\">문의 보기</a>";

    return $message;
}

/**
 * 환불 요청 알림 포맷
 *
 * @param array $order 주문 정보
 * @return string 텔레그램 메시지
 */
function format_refund_notification($order) {
    $mb_id_masked = mask_mb_id($order['mb_id'] ?? '');
    $admin_url = G5_ADMIN_URL . '/config_store.php?tab=orders&od_id=' . urlencode($order['od_id'] ?? '');

    $message = "💰 <b>환불 요청이 들어왔습니다!</b>\n\n";
    $message .= "주문번호: <code>{$order['od_id']}</code>\n";
    $message .= "상품명: {$order['product_name']}\n";
    $message .= "구매자: {$mb_id_masked}\n";
    $message .= "금액: " . number_format($order['od_price']) . "원\n";
    $message .= "사유: {$order['od_refund_reason']}\n\n";
    $message .= "<a href=\"{$admin_url}\">환불 처리</a>";

    return $message;
}

/**
 * 회원 ID 마스킹
 *
 * @param string $mb_id 회원 ID
 * @return string 마스킹된 ID (예: hong***)
 */
function mask_mb_id($mb_id) {
    if (empty($mb_id)) {
        return '(비회원)';
    }

    $len = mb_strlen($mb_id, 'UTF-8');

    if ($len <= 3) {
        return mb_substr($mb_id, 0, 1, 'UTF-8') . str_repeat('*', $len - 1);
    }

    $visible = (int)ceil($len / 2);
    $masked = mb_substr($mb_id, 0, $visible, 'UTF-8') . str_repeat('*', $len - $visible);

    return $masked;
}

/**
 * QnA 타입 라벨
 *
 * @param string $type 타입 (pre/post/general)
 * @return string 라벨
 */
function get_qa_type_label($type) {
    $labels = [
        'pre' => '구매 전 문의',
        'post' => '구매 후 문의',
        'general' => '일반 문의'
    ];

    return $labels[$type] ?? '일반 문의';
}

// 읽지 않은 알림 개수 가져오기 (요청 내 캐시)
function get_notification_count($mb_id, $force = false) {
    global $g5;
    static $cache = array();

    if (!$mb_id) return 0;

    if (!$force && isset($cache[$mb_id])) {
        return $cache[$mb_id];
    }

    check_notification_table();

    $sql = "SELECT COUNT(*) as cnt FROM {$g5['notifications_table']}
            WHERE mb_id = '".sql_real_escape_string($mb_id)."'
            AND noti_read = 0";
    $row = sql_fetch($sql);

    $cache[$mb_id] = isset($row['cnt']) ? (int)$row['cnt'] : 0;
    return $cache[$mb_id];
}

// 알림 목록 가져오기
function get_notifications($mb_id, $page = 1, $limit = 20) {
    global $g5;

    if (!$mb_id) return array();

    check_notification_table();

    $offset = ($page - 1) * $limit;

    $sql = "SELECT * FROM {$g5['notifications_table']}
            WHERE mb_id = '".sql_real_escape_string($mb_id)."'
            ORDER BY noti_read ASC, noti_datetime DESC
            LIMIT {$offset}, {$limit}";

    $result = sql_query($sql);
    $list = array();

    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }

    return $list;
}

// 카테고리별 읽지 않은 알림 수 가져오기
// 멘션: reply, comment, mention (사용자 간 상호작용)
// 공지: arena, duel, gift, trade, transfer, message 등 (시스템 알림)
// 관심글: like, scrap (관심을 보냈습니다 + 스크랩 글 업데이트)
function get_notification_counts_by_category($mb_id) {
    global $g5;
    if (!$mb_id) return array('mention' => 0, 'notice' => 0, 'interest' => 0);

    check_notification_table();
    $safe_id = sql_real_escape_string($mb_id);

    $sql = "SELECT
                SUM(CASE WHEN noti_type IN ('reply','comment','mention') THEN 1 ELSE 0 END) as mention_cnt,
                SUM(CASE WHEN noti_type IN ('like','scrap') THEN 1 ELSE 0 END) as interest_cnt,
                COUNT(*) as total_cnt
            FROM {$g5['notifications_table']}
            WHERE mb_id = '{$safe_id}' AND noti_read = 0";
    $row = sql_fetch($sql);

    $mention = (int)($row['mention_cnt'] ?? 0);
    $interest = (int)($row['interest_cnt'] ?? 0);
    $total = (int)($row['total_cnt'] ?? 0);

    return array(
        'mention'  => $mention,
        'notice'   => $total - $mention - $interest,
        'interest' => $interest
    );
}

// 카테고리별 알림 목록 가져오기
function get_notifications_by_category($mb_id, $category = 'mention', $limit = 5) {
    global $g5;
    if (!$mb_id) return array();

    check_notification_table();
    $safe_id = sql_real_escape_string($mb_id);

    switch ($category) {
        case 'mention':
            $where = "AND noti_type IN ('reply','comment','mention')";
            break;
        case 'notice':
            $where = "AND noti_type NOT IN ('reply','comment','mention','like','scrap')";
            break;
        case 'interest':
            $where = "AND noti_type IN ('like','scrap')";
            break;
        default:
            $where = "";
            break;
    }

    $sql = "SELECT * FROM {$g5['notifications_table']}
            WHERE mb_id = '{$safe_id}' {$where}
            ORDER BY noti_read ASC, noti_datetime DESC
            LIMIT ".(int)$limit;
    $result = sql_query($sql);
    $list = array();
    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }
    return $list;
}

// 알림 읽음 처리
function mark_notification_read($noti_id, $mb_id) {
    global $g5;

    $sql = "UPDATE {$g5['notifications_table']} SET
            noti_read = 1
            WHERE noti_id = '".(int)$noti_id."'
            AND mb_id = '".sql_real_escape_string($mb_id)."'";

    return sql_query($sql);
}

// 알림 안 읽음으로 되돌리기
function mark_notification_unread($noti_id, $mb_id) {
    global $g5;

    $sql = "UPDATE {$g5['notifications_table']} SET
            noti_read = 0
            WHERE noti_id = '".(int)$noti_id."'
            AND mb_id = '".sql_real_escape_string($mb_id)."'";

    return sql_query($sql);
}

// 모든 알림 읽음 처리
function mark_all_notifications_read($mb_id) {
    global $g5;
    
    $sql = "UPDATE {$g5['notifications_table']} SET 
            noti_read = 1 
            WHERE mb_id = '".sql_real_escape_string($mb_id)."' 
            AND noti_read = 0";
    
    return sql_query($sql);
}

// 오래된 알림 삭제 (30일 이상)
function delete_old_notifications($days = 30) {
    global $g5;

    $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));

    $sql = "DELETE FROM {$g5['notifications_table']}
            WHERE noti_datetime < '{$date}'";

    return sql_query($sql);
}

/**
 * 이메일 알림 발송 (조건부)
 *
 * @param array $data 알림 데이터
 * @return bool 성공 여부
 */
function send_email_for_notification($data) {
    global $g5, $config;

    // Store 알림은 이메일 발송 안 함 (order, qna, refund)
    $store_types = ['order', 'qna', 'refund'];
    $noti_type = $data['noti_type'] ?? 'comment';
    if (in_array($noti_type, $store_types)) {
        return false;
    }

    // 이메일 설정 파일 존재 여부 확인
    $email_config_file = G5_ADMIN_PATH . '/notification/notification_email.php';
    if (!file_exists($email_config_file)) {
        return false;
    }

    // 이메일 설정 로드
    include_once($email_config_file);

    // 설정이 비활성화되어 있으면 발송 안 함
    if (!isset($email_config['enabled']) || !$email_config['enabled']) {
        return false;
    }

    // 알림 유형별 발송 설정 확인
    if (isset($email_config['types'][$noti_type]) && !$email_config['types'][$noti_type]) {
        return false;
    }

    // 알림 받을 회원 정보 조회
    $member = sql_fetch("SELECT mb_id, mb_name, mb_email FROM {$g5['member_table']}
                         WHERE mb_id = '".sql_real_escape_string($data['mb_id'])."'");

    if (!$member || empty($member['mb_email'])) {
        return false;
    }

    // 이메일 제목
    $type_info = [
        'reply' => '새 답글',
        'comment' => '새 댓글',
        'mention' => '멘션'
    ];
    $type_label = $type_info[$noti_type] ?? '새 알림';
    $subject = '[' . $config['cf_title'] . '] ' . $type_label . ' 알림';

    // 이메일 HTML 생성
    $html = format_notification_email($data, $member);

    // 이메일 발송
    return send_email_notification($member['mb_email'], $subject, $html, $email_config);
}

/**
 * 이메일 발송 (SMTP 또는 phpmail)
 *
 * @param string $to 수신자 이메일
 * @param string $subject 제목
 * @param string $html HTML 내용
 * @param array $email_config 이메일 설정
 * @return bool 성공 여부
 */
function send_email_notification($to, $subject, $html, $email_config) {
    global $config;

    $method = $email_config['method'] ?? 'phpmail';

    // SMTP 사용
    if ($method === 'smtp') {
        return send_email_smtp($to, $subject, $html, $email_config);
    }

    // PHP mail() 사용 (기본)
    $from_name = $email_config['from_name'] ?? $config['cf_admin_email_name'];
    $from_email = $config['cf_admin_email'];

    return mailer($from_name, $from_email, $to, $subject, $html, 1);
}

/**
 * Gmail SMTP로 이메일 발송
 *
 * @param string $to 수신자
 * @param string $subject 제목
 * @param string $html HTML 내용
 * @param array $email_config SMTP 설정
 * @return bool 성공 여부
 */
function send_email_smtp($to, $subject, $html, $email_config) {
    if (empty($email_config['smtp_user']) || empty($email_config['smtp_pass'])) {
        return false;
    }

    // 그누보드 mailer 함수 사용 (PHPMailer 기반)
    require_once G5_LIB_PATH . '/mailer.lib.php';

    try {
        $mail = mailer('smtp');
        $mail->SMTPDebug = 0;
        $mail->Host = $email_config['smtp_host'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $email_config['smtp_user'];
        $mail->Password = $email_config['smtp_pass'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom($email_config['smtp_user'], $email_config['from_name'] ?? 'RA0');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        $mail->CharSet = 'utf-8';

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}

/**
 * 알림 이메일 HTML 생성
 *
 * @param array $data 알림 데이터
 * @param array $member 회원 정보
 * @return string HTML 이메일
 */
function format_notification_email($data, $member) {
    global $config;

    $site_name = $config['cf_title'];
    $noti_type = $data['noti_type'] ?? 'comment';
    $from_name = $data['from_wr_name'] ?? '알 수 없음';
    $content = strip_tags($data['noti_content'] ?? '');
    $url = $data['noti_url'] ?? '';

    // 상대 경로를 절대 경로로 변환
    if (!empty($url) && strpos($url, 'http') !== 0) {
        $url = G5_URL . $url;
    }

    // 알림 타입별 아이콘 및 제목
    $type_info = [
        'reply' => ['icon' => '💬', 'title' => '새 답글이 달렸습니다'],
        'comment' => ['icon' => '💬', 'title' => '새 댓글이 달렸습니다'],
        'mention' => ['icon' => '👤', 'title' => '회원님을 멘션했습니다'],
        'like' => ['icon' => '⭐', 'title' => '관심을 표시했습니다']
    ];

    $info = $type_info[$noti_type] ?? ['icon' => '🔔', 'title' => '새 알림이 있습니다'];

    $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $info['title'] . ' - ' . $site_name . '</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f5f5f5;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            padding: 30px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 24px;
        }
        .email-body {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        .notification-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
        }
        .from-label {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .from-name {
            color: #333;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .content-preview {
            color: #555;
            line-height: 1.6;
            font-size: 14px;
            margin: 15px 0;
            padding: 15px;
            background: white;
            border-radius: 4px;
        }
        .view-btn {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            font-weight: 600;
        }
        .email-footer {
            padding: 20px 30px;
            background: #f8f9fa;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .divider {
            height: 1px;
            background: #e9ecef;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-header">
            <h1>' . $info['icon'] . ' ' . $info['title'] . '</h1>
        </div>

        <div class="email-body">
            <p class="greeting">안녕하세요, <strong>' . htmlspecialchars($member['mb_name']) . '</strong>님</p>

            <p style="color: #666; line-height: 1.6;">
                회원님의 글에 <strong>' . htmlspecialchars($from_name) . '</strong>님이 ' .
                ($noti_type === 'reply' ? '답글을' : '댓글을') . ' 남겼습니다.
            </p>

            <div class="notification-box">
                <div class="from-label">작성자</div>
                <div class="from-name">' . htmlspecialchars($from_name) . '</div>

                <div class="divider"></div>

                <div class="content-preview">' . nl2br(htmlspecialchars(mb_substr($content, 0, 200, 'UTF-8'))) .
                (mb_strlen($content, 'UTF-8') > 200 ? '...' : '') . '</div>
            </div>

            <div style="text-align: center;">
                <a href="' . htmlspecialchars($url) . '" class="view-btn">게시글 확인하기</a>
            </div>

            <p style="color: #999; font-size: 13px; margin-top: 30px; line-height: 1.5;">
                💡 알림 설정은 마이페이지에서 변경하실 수 있습니다.<br>
                이 메일은 발신 전용입니다.
            </p>
        </div>

        <div class="email-footer">
            <p>&copy; ' . date('Y') . ' ' . $site_name . '. All rights reserved.</p>
        </div>
    </div>
</body>
</html>';

    return $html;
}

// 알림 테이블 생성 (처음 로드시 실행)
create_notification_table();

// ===================================================================
// Web Push 구독 및 발송
// ===================================================================

/**
 * Push 구독 테이블 생성
 */
function create_push_subscription_table() {
    global $g5;

    $sql = "CREATE TABLE IF NOT EXISTS `{$g5['push_subscriptions_table']}` (
        `ps_id` int(11) NOT NULL AUTO_INCREMENT,
        `mb_id` varchar(20) NOT NULL DEFAULT '' COMMENT '회원 ID',
        `endpoint` text NOT NULL COMMENT 'Push 서비스 endpoint URL',
        `endpoint_hash` char(64) NOT NULL COMMENT 'sha256(endpoint) for UNIQUE',
        `auth_key` varchar(255) NOT NULL DEFAULT '' COMMENT '구독 auth 토큰',
        `p256dh_key` varchar(255) NOT NULL DEFAULT '' COMMENT '구독 공개키',
        `user_agent` varchar(255) NOT NULL DEFAULT '' COMMENT '브라우저 정보',
        `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '활성 여부',
        `fail_count` int(11) NOT NULL DEFAULT 0 COMMENT '연속 실패 횟수',
        `last_error` varchar(255) NOT NULL DEFAULT '' COMMENT '마지막 오류',
        `ps_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '구독 일시',
        `ps_update_datetime` datetime DEFAULT NULL COMMENT '갱신 일시',
        PRIMARY KEY (`ps_id`),
        UNIQUE KEY `idx_endpoint_hash` (`endpoint_hash`),
        KEY `idx_mb_id` (`mb_id`, `is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    sql_query($sql, false);
}

/**
 * Push 구독 테이블 확인 (lazy)
 */
function check_push_subscription_table() {
    static $checked = false;
    if (!$checked) {
        create_push_subscription_table();
        $checked = true;
    }
}

/**
 * Push 구독 등록/갱신 (endpoint_hash 기준 UPSERT)
 *
 * @param string $mb_id     회원 ID
 * @param string $endpoint  Push endpoint URL
 * @param string $authKey   auth 토큰 (base64url)
 * @param string $p256dhKey 공개키 (base64url)
 * @param string $userAgent 브라우저 UA
 * @return bool
 */
function save_push_subscription($mb_id, $endpoint, $authKey, $p256dhKey, $userAgent = '') {
    global $g5;

    check_push_subscription_table();

    $endpointHash = hash('sha256', $endpoint);

    // 기존 구독 확인
    $existing = sql_fetch("SELECT ps_id, mb_id FROM {$g5['push_subscriptions_table']}
        WHERE endpoint_hash = '".sql_real_escape_string($endpointHash)."'");

    if ($existing) {
        // 갱신 (키 변경 또는 회원 변경 대응)
        $sql = "UPDATE {$g5['push_subscriptions_table']} SET
            mb_id = '".sql_real_escape_string($mb_id)."',
            endpoint = '".sql_real_escape_string($endpoint)."',
            auth_key = '".sql_real_escape_string($authKey)."',
            p256dh_key = '".sql_real_escape_string($p256dhKey)."',
            user_agent = '".sql_real_escape_string($userAgent)."',
            is_active = 1,
            fail_count = 0,
            last_error = '',
            ps_update_datetime = '".G5_TIME_YMDHIS."'
            WHERE ps_id = '".(int)$existing['ps_id']."'";
    } else {
        // 신규 등록
        $sql = "INSERT INTO {$g5['push_subscriptions_table']} SET
            mb_id = '".sql_real_escape_string($mb_id)."',
            endpoint = '".sql_real_escape_string($endpoint)."',
            endpoint_hash = '".sql_real_escape_string($endpointHash)."',
            auth_key = '".sql_real_escape_string($authKey)."',
            p256dh_key = '".sql_real_escape_string($p256dhKey)."',
            user_agent = '".sql_real_escape_string($userAgent)."',
            ps_datetime = '".G5_TIME_YMDHIS."'";
    }

    return sql_query($sql);
}

/**
 * Push 구독 해제 (비활성화)
 *
 * @param string $mb_id    회원 ID
 * @param string $endpoint Push endpoint URL
 * @return bool
 */
function deactivate_push_subscription($mb_id, $endpoint) {
    global $g5;

    check_push_subscription_table();

    $endpointHash = hash('sha256', $endpoint);

    $sql = "UPDATE {$g5['push_subscriptions_table']} SET
        is_active = 0,
        ps_update_datetime = '".G5_TIME_YMDHIS."'
        WHERE mb_id = '".sql_real_escape_string($mb_id)."'
        AND endpoint_hash = '".sql_real_escape_string($endpointHash)."'";

    return sql_query($sql);
}

/**
 * 회원의 활성 Push 구독 목록 조회
 *
 * @param string $mb_id 회원 ID
 * @return array
 */
function get_push_subscriptions($mb_id) {
    global $g5;

    check_push_subscription_table();

    $sql = "SELECT * FROM {$g5['push_subscriptions_table']}
        WHERE mb_id = '".sql_real_escape_string($mb_id)."'
        AND is_active = 1";

    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }
    return $list;
}

/**
 * 회원이 Push 구독 중인지 확인
 *
 * @param string $mb_id 회원 ID
 * @return bool
 */
function has_push_subscription($mb_id) {
    global $g5;

    check_push_subscription_table();

    $row = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['push_subscriptions_table']}
        WHERE mb_id = '".sql_real_escape_string($mb_id)."'
        AND is_active = 1");

    return (int)($row['cnt'] ?? 0) > 0;
}

/**
 * Push 구독 실패 기록
 *
 * @param int    $ps_id   구독 ID
 * @param string $error   오류 메시지
 * @param bool   $deactivate 비활성화 여부
 */
function mark_push_subscription_failed($ps_id, $error, $deactivate = false) {
    global $g5;

    $setActive = $deactivate ? ", is_active = 0" : "";

    $sql = "UPDATE {$g5['push_subscriptions_table']} SET
        fail_count = fail_count + 1,
        last_error = '".sql_real_escape_string(mb_substr($error, 0, 255, 'UTF-8'))."'
        {$setActive}
        WHERE ps_id = '".(int)$ps_id."'";

    sql_query($sql);
}

/**
 * Push 설정 파일 로드
 *
 * @return array|false 설정 배열 또는 false
 */
function get_push_config() {
    $config_file = G5_DATA_PATH . '/push/push_config.php';
    if (!file_exists($config_file)) {
        return false;
    }

    $push_config = [];
    include($config_file);

    if (empty($push_config) || empty($push_config['enabled'])) {
        return false;
    }

    return $push_config;
}

/**
 * Web Push 알림 발송 (create_notification 훅)
 *
 * 메인 트랜잭션에 영향 주지 않도록 예외를 모두 catch한다.
 *
 * @param array $data 알림 데이터
 */
function send_web_push_for_notification($data) {
    global $g5;

    try {
        // Push 설정 로드
        $push_config = get_push_config();
        if (!$push_config) {
            return;
        }

        // 알림 유형별 발송 체크 (scrap은 like와 동일 취급)
        $noti_type = $data['noti_type'] ?? 'comment';
        $check_type = ($noti_type === 'scrap') ? 'like' : $noti_type;
        if (isset($push_config['types']) && is_array($push_config['types'])) {
            if (isset($push_config['types'][$check_type]) && !$push_config['types'][$check_type]) {
                return;
            }
        }

        // 대상 회원의 활성 구독 조회
        $subscriptions = get_push_subscriptions($data['mb_id']);
        if (empty($subscriptions)) {
            return;
        }

        // WebPush 라이브러리 로드
        $webpush_file = G5_PLUGIN_PATH . '/WebPush/WebPush.php';
        if (!file_exists($webpush_file)) {
            return;
        }
        require_once($webpush_file);

        // WebPush 인스턴스 생성
        $webpush = new WebPush([
            'public_key'      => $push_config['public_key'],
            'private_key_pem' => $push_config['private_key_pem'],
            'subject'         => $push_config['subject'] ?? 'mailto:admin@example.com',
            'ttl'             => $push_config['ttl'] ?? 86400,
            'urgency'         => $push_config['urgency'] ?? 'normal',
            'timeout'         => 3,
        ]);

        // 알림 페이로드 생성
        $payload = json_encode([
            'title' => get_push_notification_title($data),
            'body'  => mb_substr(strip_tags($data['noti_content'] ?? ''), 0, 100, 'UTF-8'),
            'icon'  => G5_URL . '/img/logo.png',
            'url'   => get_push_notification_url($data),
            'type'  => $noti_type,
        ], JSON_UNESCAPED_UNICODE);

        // 각 구독에 발송
        $results = $webpush->sendToAll($subscriptions, $payload);

        // 결과 처리 (만료 구독 정리)
        foreach ($results as $item) {
            $result = $item['result'];
            $ps_id  = $item['ps_id'];

            if (!$ps_id) continue;

            if ($result['success']) {
                // 성공 시 fail_count 리셋
                sql_query("UPDATE {$g5['push_subscriptions_table']} SET fail_count = 0, last_error = '' WHERE ps_id = '".(int)$ps_id."'");
            } else {
                // 404/410: 구독 만료 → 비활성화
                $deactivate = in_array($result['statusCode'], [404, 410]);
                mark_push_subscription_failed($ps_id, $result['reason'], $deactivate);
            }
        }
    } catch (\Exception $e) {
        // Push 실패가 메인 기능에 영향 주지 않음
    } catch (\Error $e) {
        // PHP Fatal Error도 잡기
    }
}

/**
 * Push 알림 제목 생성
 */
function get_push_notification_title($data) {
    global $config;

    $type_titles = [
        'comment' => '새 댓글',
        'reply'   => '새 답글',
        'mention' => '멘션',
        'message' => '새 쪽지',
        'like'    => '관심 표시',
        'gift'    => '선물 도착',
        'trade'   => '거래 알림',
    ];

    $type = $data['noti_type'] ?? 'comment';
    $title = $type_titles[$type] ?? '새 알림';

    $from = $data['from_wr_name'] ?? '';
    if ($from) {
        $title .= ' - ' . $from;
    }

    return $title;
}

/**
 * Push 알림 클릭 URL 생성 (절대 경로)
 *  - 로비 확장팩 + 스퀘어 연결 게시판과 매칭되면 스퀘어 타래로 라우팅
 */
function get_push_notification_url($data) {
    // 1) 로비팩 설치 + 스퀘어 center_bo_table 매칭 → 스퀘어 타래로 열기
    if (file_exists(G5_PATH . '/extend/lobby_config.php')
        && function_exists('square_get')
    ) {
        $center_bo = (string)square_get('center_bo_table', '');
        $noti_bo   = (string)($data['bo_table'] ?? '');
        $noti_wr   = (int)($data['wr_id'] ?? 0);
        if ($center_bo !== '' && $noti_bo === $center_bo && $noti_wr > 0) {
            return G5_URL . '/community/lobby/square/index.php?open_thread=' . $noti_wr;
        }
    }

    // 2) 기본: 저장된 noti_url
    $url = $data['noti_url'] ?? '/';
    if (strpos($url, 'http') !== 0) {
        $url = G5_URL . $url;
    }
    return $url;
}
?>