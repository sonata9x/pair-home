<?php
include_once('./_common.php');

// 로그인 체크
if (!$member['mb_id']) {
    echo '<div class="empty-notification">로그인이 필요합니다.</div>';
    exit;
}

// 알림 라이브러리 로드
include_once(G5_LIB_PATH.'/notification.lib.php');

// global 선언
global $g5;

// 모든 알림 읽음 처리
if (isset($_GET['read_all']) && $_GET['read_all'] == '1') {
    mark_all_notifications_read($member['mb_id']);
    // 리다이렉트하지 않고 현재 페이지 유지 (include 파일이므로)
}

// 페이지네이션
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;

// 알림 목록 가져오기
$notifications = get_notifications($member['mb_id'], $page, $limit);

// 전체 알림 개수
$total_sql = "SELECT COUNT(*) as cnt FROM {$g5['notifications_table']} WHERE mb_id = '{$member['mb_id']}'";
$total_row = sql_fetch($total_sql);
$total_count = $total_row['cnt'];
$total_page = ceil($total_count / $limit);
?>

<style>
.notification-wrapper {
    width: 100%;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

.notification-header h3 {
    margin: 0;
    font-size: 1.2em;
}

.btn-read-all {
    padding: 3px 5px;
    background: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    font-size: 0.8em;
}

.btn-read-all:hover {
    opacity: 0.8;
}

.notification-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.notification-item {
    padding: 12px;
    border: 1px solid var(--container-border-color);
    border-radius: 6px;
    background: var(--container-bg-color);
    transition: all 0.2s;
    cursor: pointer;
}

.notification-item:hover {
    background: var(--card-bg-color);
}

.notification-item.unread {
    background: var(--container-bg-color);
    border-left: 3px solid var(--accent-color);
}

.notification-item.unread:hover {
    background: var(--card-bg-color);
    border-left: 3px solid var(--accent-color);
}

.notification-meta {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    font-size: 0.9em;
}

.notification-from {
    font-weight: bold;
    color: var(--accent-color);
}

.notification-date {
    font-size: 0.85em;
    color: var(--text-muted);
}

.notification-content {
    color: var(--content-font-color);
    line-height: 1.4;
    font-size: 0.95em;
    word-break: break-word;
}

.notification-type {
    display: inline-block;
    padding: 2px 6px;
    background: var(--btn-secondary-bg);
    color: var(--btn-secondary-text);
    border-radius: 3px;
    font-size: 0.75em;
    margin-right: 5px;
}

.empty-notification {
    text-align: center;
    padding: 30px 20px;
    color: var(--text-muted);
}

.notification-pagination {
    margin-top: 20px;
    text-align: center;
}

.notification-pagination a {
    display: inline-block;
    padding: 5px 10px;
    margin: 0 2px;
    border: 1px solid var(--border-color);
    border-radius: 3px;
    color: var(--content-font-color);
    text-decoration: none;
    font-size: 0.9em;
}

.notification-pagination a:hover {
    background: var(--light-bg-color);
}

.notification-pagination .current {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}
</style>

<div class="notification-wrapper">
    <?php if ($notifications) { ?>
    <div class="notification-header">
        <h3></h3>
        <a href="?read_all=1" class="btn-read-all"><i class="fa-solid fa-check"></i></a>
    </div>
    
    <ul class="notification-list">
        <?php foreach ($notifications as $noti) { 
            $type_label = '';
            switch($noti['noti_type']) {
                case 'reply':
                    $type_label = '답글';
                    break;
                case 'comment':
                    $type_label = '댓글';
                    break;
                case 'message':
                    $type_label = '쪽지';
                    break;
                case 'mention':
                    $type_label = '멘션';
                    break;
                default:
                    $type_label = '알림';
            }
        ?>
        <li class="notification-item <?php echo !$noti['noti_read'] ? 'unread' : ''; ?>"
            onclick="location.href='<?php echo G5_BBS_URL; ?>/notification_redirect.php?noti_id=<?php echo $noti['noti_id']; ?>'">
            <div class="notification-meta">
                <div>
                    <span class="notification-type <?php echo $noti['noti_type']; ?>"><?php echo $type_label; ?></span>
                    <span class="notification-from"><?php echo $noti['from_wr_name']; ?></span>
                </div>
                <span class="notification-date"><?php echo date('m/d H:i', strtotime($noti['noti_datetime'])); ?></span>
            </div>
            <div class="notification-content">
                <?php echo $noti['noti_content']; ?>
            </div>
        </li>
        <?php } ?>
    </ul>

    <?php if ($total_page > 1) { ?>
    <div class="notification-pagination">
        <?php
        $start_page = max(1, $page - 5);
        $end_page = min($total_page, $page + 5);
        
        if ($page > 1) {
            echo '<a href="?page='.($page-1).'">이전</a>';
        }
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            $class = ($i == $page) ? 'current' : '';
            echo '<a href="?page='.$i.'" class="'.$class.'">'.$i.'</a>';
        }
        
        if ($page < $total_page) {
            echo '<a href="?page='.($page+1).'">다음</a>';
        }
        ?>
    </div>
    <?php } ?>
    
    <?php } else { ?>
    <div class="empty-notification">
        <p>새로운 알림이 없습니다.</p>
    </div>
    <?php } ?>
</div>