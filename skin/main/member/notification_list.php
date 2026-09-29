<?php
if (!defined('_GNUBOARD_')) exit;

// 로그인 체크
if (!$member['mb_id']) {
    echo '<div class="empty-notification">로그인이 필요합니다.</div>';
    return;
}

// 알림 라이브러리 로드
include_once(G5_LIB_PATH.'/notification.lib.php');

// global 선언
global $g5;

// 페이지네이션
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;

// 현재 탭 정보 유지 (mypage.php에서 include될 때)
$current_tab = isset($_GET['tab']) ? htmlspecialchars($_GET['tab'], ENT_QUOTES, 'UTF-8') : '';
$page_base_url = $current_tab ? "?tab={$current_tab}&page=" : "?page=";

// 내부 서브탭: 멘션 / 공지 / 관심글
$noti_sub = isset($_GET['noti_sub']) ? $_GET['noti_sub'] : 'mention';
if (!in_array($noti_sub, array('mention', 'notice', 'interest'))) $noti_sub = 'mention';

// 서브탭별 필터
$safe_mb_id = sql_real_escape_string($member['mb_id']);
switch ($noti_sub) {
    case 'mention':
        $where_filter = "AND noti_type IN ('reply','comment','mention')";
        break;
    case 'notice':
        $where_filter = "AND noti_type NOT IN ('reply','comment','mention','like','scrap')";
        break;
    case 'interest':
        $where_filter = "AND noti_type IN ('like','scrap')";
        break;
}

// 알림 개수 (서브탭별)
$total_sql = "SELECT COUNT(*) as cnt FROM {$g5['notifications_table']} WHERE mb_id = '{$safe_mb_id}' {$where_filter}";
$total_row = sql_fetch($total_sql);
$total_count = $total_row['cnt'];
$total_page = ceil($total_count / $limit);

// 각 서브탭 안읽은 수 (단일 쿼리)
$unread_row = sql_fetch("SELECT
    SUM(CASE WHEN noti_type IN ('reply','comment','mention') THEN 1 ELSE 0 END) as mention_cnt,
    SUM(CASE WHEN noti_type IN ('like','scrap') THEN 1 ELSE 0 END) as interest_cnt,
    COUNT(*) as total_cnt
    FROM {$g5['notifications_table']} WHERE mb_id = '{$safe_mb_id}' AND noti_read = 0");
$mention_unread_cnt = (int)($unread_row['mention_cnt'] ?? 0);
$interest_unread_cnt = (int)($unread_row['interest_cnt'] ?? 0);
$notice_unread_cnt = (int)($unread_row['total_cnt'] ?? 0) - $mention_unread_cnt - $interest_unread_cnt;

// 알림 목록 (서브탭 필터 적용)
$offset = ($page - 1) * $limit;
$noti_sql = "SELECT * FROM {$g5['notifications_table']}
        WHERE mb_id = '{$safe_mb_id}' {$where_filter}
        ORDER BY noti_read ASC, noti_datetime DESC
        LIMIT {$offset}, {$limit}";
$noti_result = sql_query($noti_sql);
$notifications = array();
while ($row = sql_fetch_array($noti_result)) {
    $notifications[] = $row;
}

// 서브탭 URL 빌더
$sub_base = $current_tab ? "?tab={$current_tab}" : "?";
$mention_url = $sub_base . ($current_tab ? '&' : '') . 'noti_sub=mention';
$notice_url = $sub_base . ($current_tab ? '&' : '') . 'noti_sub=notice';
$interest_url = $sub_base . ($current_tab ? '&' : '') . 'noti_sub=interest';
$page_base_url = $sub_base . ($current_tab ? '&' : '') . 'noti_sub=' . $noti_sub . '&page=';
?>

<style>
.notification-wrapper {
    width: 100%;
}

.notification-header {
    display: none;
}

.notification-header h3 {
    margin: 0;
    font-size: 1.2em;
}

.notification-header-btns {
    display: flex;
    gap: 6px;
    margin-left: auto;
    padding-left: 8px;
}

.btn-read-all, .btn-delete-read {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 5px 10px;
    background: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    font-size: 0.8em;
    line-height: 1;
    white-space: nowrap;
}

.btn-delete-read {
    background: var(--btn-secondary-bg);
    color: var(--btn-secondary-text);
}

.btn-read-all:hover, .btn-delete-read:hover {
    opacity: 0.8;
}

.noti-action-btns {
    position: absolute;
    top: 8px;
    right: 8px;
    display: none;
    gap: 3px;
}

.notification-item:hover .noti-action-btns {
    display: flex;
}

.noti-action-btns button {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: none;
    border: 1px solid var(--border-color);
    border-radius: 3px;
    color: var(--text-muted);
    cursor: pointer;
    font-size: 10px;
    padding: 0;
    line-height: 1;
}

.noti-toggle-btn:hover {
    color: var(--accent-color);
    border-color: var(--accent-color);
}

.noti-delete-btn:hover {
    color: #ff4444;
    border-color: #ff4444;
}

.notification-item {
    position: relative;
}

.notification-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 5px;
    overflow: hidden;
}

.notification-item {
    padding: 12px;
    border: 1px solid var(--container-border-color);
    border-radius: 6px;
    background: var(--container-bg-color);
    transition: background 0.2s, border 0.2s;
    cursor: pointer;
    overflow: hidden;
    user-select: none;
    -webkit-user-select: none;
}

.notification-item .noti-inner {
    position: relative;
    transition: transform 0.25s ease;
}

.notification-item.swiping .noti-inner {
    transition: none;
}

.noti-swipe-hint {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    width: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75em;
    font-weight: 600;
    border-radius: 0 6px 6px 0;
    opacity: 0;
    transition: opacity 0.2s;
    pointer-events: none;
}

.notification-item.unread .noti-swipe-hint {
    background: var(--btn-secondary-bg, #e9ecef);
    color: var(--btn-secondary-text, #555);
}

.notification-item:not(.unread) .noti-swipe-hint {
    background: var(--accent-color, #10b981);
    color: var(--white, #fff);
}

.notification-item.swiping .noti-swipe-hint {
    opacity: 1;
}

.notification-item:hover {
    background: var(--container-bg-color);
}

.notification-item.unread {
    background: var(--card-bg-color);
    border-left: 3px solid var(--accent-color);
}

.notification-item.unread:hover {
    background: var(--container-bg-color);
    border-left: 3px solid var(--accent-color);
}

.notification-meta {
    display: flex;
    align-items: center;
    gap: 5px;
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

.noti-subtab-menu {
    display: flex;
    align-items: stretch;
    gap: 0;
    margin-bottom: 12px;
    border-bottom: 1px solid var(--container-border-color);
}

.noti-subtab {
    padding: 8px 14px;
    font-size: 0.9em;
    color: var(--text-muted);
    text-decoration: none;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
}

.noti-subtab-menu .notification-header-btns {
    align-self: center;
    padding-bottom: 4px;
}

.noti-subtab:hover {
    color: var(--content-font-color);
}

.noti-subtab.active {
    color: var(--accent-color);
    border-bottom-color: var(--accent-color);
    font-weight: bold;
}

.noti-subtab .noti-badge {
    display: inline-block;
    min-width: 16px;
    padding: 1px 5px;
    margin-left: 4px;
    background: var(--accent-color);
    color: var(--white);
    border-radius: 8px;
    font-size: 0.7em;
    line-height: 1.4;
    text-align: center;
    vertical-align: middle;
    font-weight: normal;
}

.empty-notification {
    text-align: center;
    padding: 30px 20px;
    color: var(--text-muted);
}

/* Push 토글 */
.push-toggle-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    margin-bottom: 10px;
    background: var(--container-bg-color);
    border: 1px solid var(--container-border-color);
    border-radius: 6px;
}
.push-toggle-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9em;
    color: var(--content-font-color);
}
.push-toggle-label i {
    color: var(--accent-color);
}
.push-toggle-status {
    font-size: 0.8em;
    color: var(--text-muted);
}
.push-switch {
    position: relative;
    width: 40px;
    height: 22px;
}
.push-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.push-switch .slider {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: var(--border-color, #ccc);
    border-radius: 22px;
    cursor: pointer;
    transition: background 0.3s;
}
.push-switch .slider::before {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    left: 3px;
    bottom: 3px;
    background: #fff;
    border-radius: 50%;
    transition: transform 0.3s;
}
.push-switch input:checked + .slider {
    background: var(--accent-color, #10b981);
}
.push-switch input:checked + .slider::before {
    transform: translateX(18px);
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
    <!-- Push 알림 토글 -->
    <div class="push-toggle-wrap" id="push-toggle-wrap" style="display:none;">
        <div>
            <div class="push-toggle-label">
                <i class="fa-solid fa-bell"></i>
                브라우저 알림
            </div>
            <div class="push-toggle-status" id="push-status">확인 중...</div>
        </div>
        <label class="push-switch">
            <input type="checkbox" id="push-toggle" onchange="RA0Push.toggle()">
            <span class="slider"></span>
        </label>
    </div>
    <script>
    // Push 지원 + SW 등록 완료 시에만 토글 표시
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof RA0Push !== 'undefined' && 'serviceWorker' in navigator && 'PushManager' in window) {
            // SW 등록 완료 대기 후 토글 표시
            var checkReady = setInterval(function() {
                if (RA0Push.swRegistration) {
                    clearInterval(checkReady);
                    document.getElementById('push-toggle-wrap').style.display = 'flex';
                    RA0Push.updateUI();
                }
            }, 200);
            // 최대 3초 대기
            setTimeout(function() { clearInterval(checkReady); }, 3000);
        }
    });
    </script>

    <?php
    $sub_labels = ['mention' => '멘션', 'notice' => '공지', 'interest' => '관심글'];
    $current_label = $sub_labels[$noti_sub] ?? '';
    ?>

    <div class="noti-subtab-menu">
        <a href="<?php echo $mention_url; ?>" class="noti-subtab <?php echo $noti_sub === 'mention' ? 'active' : ''; ?>">
            <i class="fa-solid fa-at"></i> 멘션
            <?php if ($mention_unread_cnt > 0) { ?><span class="noti-badge"><?php echo $mention_unread_cnt; ?></span><?php } ?>
        </a>
        <a href="<?php echo $notice_url; ?>" class="noti-subtab <?php echo $noti_sub === 'notice' ? 'active' : ''; ?>">
            <i class="fa-solid fa-bullhorn"></i> 공지
            <?php if ($notice_unread_cnt > 0) { ?><span class="noti-badge"><?php echo $notice_unread_cnt; ?></span><?php } ?>
        </a>
        <a href="<?php echo $interest_url; ?>" class="noti-subtab <?php echo $noti_sub === 'interest' ? 'active' : ''; ?>">
            <i class="fa-solid fa-heart"></i> 관심글
            <?php if ($interest_unread_cnt > 0) { ?><span class="noti-badge"><?php echo $interest_unread_cnt; ?></span><?php } ?>
        </a>
        <div class="notification-header-btns">
            <button type="button" class="btn-read-all" onclick="markAllNotificationsAsRead()" title="<?php echo $current_label; ?> 모두 읽음">
                <i class="fa-solid fa-check"></i>
            </button>
            <button type="button" class="btn-delete-read" onclick="deleteReadNotifications()" title="<?php echo $current_label; ?> 읽은 알림 삭제">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    </div>

    <?php if ($notifications) { ?>
    <ul class="notification-list">
        <?php foreach ($notifications as $noti) {
            $type_label = '';
            switch($noti['noti_type']) {
                case 'reply':    $type_label = '답글'; break;
                case 'comment':  $type_label = '댓글'; break;
                case 'mention':  $type_label = '멘션'; break;
                case 'like':     $type_label = '관심'; break;
                case 'scrap':    $type_label = '관심글'; break;
                case 'gift':     $type_label = '선물'; break;
                case 'trade':    $type_label = '거래'; break;
                case 'transfer': $type_label = '송금'; break;
                case 'arena':    $type_label = '아레나'; break;
                case 'duel':     $type_label = '듀얼'; break;
                case 'message':  $type_label = '쪽지'; break;
                default:         $type_label = '시스템';
            }
        ?>
        <li class="notification-item <?php echo !$noti['noti_read'] ? 'unread' : ''; ?>"
            data-noti-id="<?php echo $noti['noti_id']; ?>"
            data-noti-url="<?php echo htmlspecialchars($noti['noti_url']); ?>">
            <div class="noti-swipe-hint"><?php echo !$noti['noti_read'] ? '읽음' : '안읽음'; ?></div>
            <div class="noti-inner">
                <div class="noti-action-btns">
                    <button type="button" class="noti-toggle-btn" onclick="toggleNotificationRead(event)" title="<?php echo $noti['noti_read'] ? '안읽음으로' : '읽음으로'; ?>"><i class="fa-solid <?php echo $noti['noti_read'] ? 'fa-eye-slash' : 'fa-eye'; ?>"></i></button>
                    <button type="button" class="noti-delete-btn" onclick="deleteNotification(event, <?php echo $noti['noti_id']; ?>)" title="삭제"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="notification-meta">
                    <div>
                        <span class="notification-type <?php echo $noti['noti_type']; ?>"><?php echo $type_label; ?></span>
                        <span class="notification-from"><?php echo htmlspecialchars($noti['from_wr_name']); ?></span>
                    </div>
                    <span class="notification-date"><?php echo date('m/d H:i', strtotime($noti['noti_datetime'])); ?></span>
                </div>
                <div class="notification-content">
                    <?php echo htmlspecialchars($noti['noti_content']); ?>
                </div>
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
            echo '<a href="'.$page_base_url.($page-1).'">이전</a>';
        }

        for ($i = $start_page; $i <= $end_page; $i++) {
            $class = ($i == $page) ? 'current' : '';
            echo '<a href="'.$page_base_url.$i.'" class="'.$class.'">'.$i.'</a>';
        }

        if ($page < $total_page) {
            echo '<a href="'.$page_base_url.($page+1).'">다음</a>';
        }
        ?>
    </div>
    <?php } ?>

    <?php } else { ?>
    <div class="empty-notification">
        <?php
        $empty_messages = array('mention' => '멘션 알림이 없습니다.', 'notice' => '공지 알림이 없습니다.', 'interest' => '관심글 알림이 없습니다.');
        ?>
        <p><?php echo $empty_messages[$noti_sub] ?? '새로운 알림이 없습니다.'; ?></p>
    </div>
    <?php } ?>
</div>

<script>
// 알림 클릭 이벤트 (Ajax 처리)
document.querySelectorAll('.notification-item').forEach(item => {
    item.addEventListener('click', function() {
        const notiId = this.dataset.notiId;
        const notiUrl = this.dataset.notiUrl;

        if (!notiId) return;

        // Ajax로 읽음 처리
        fetch('<?php echo G5_URL; ?>/bbs/notification_mark_read.php?noti_id=' + notiId)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // 읽음 표시 UI 업데이트
                    this.classList.remove('unread');

                    // 배지 업데이트
                    updateNotificationBadge(data.unread_count);

                    // URL 이동 (iframe_skip 파라미터 추가)
                    if (notiUrl) {
                        let redirectUrl = notiUrl;
                        if (redirectUrl.indexOf('iframe_skip') === -1) {
                            redirectUrl += (redirectUrl.indexOf('?') > -1 ? '&' : '?') + 'iframe_skip=1';
                        }
                        location.href = redirectUrl;
                    }
                } else {
                    alert(data.message || '알림 처리에 실패했습니다.');
                }
            })
            .catch(() => {
                alert('알림 처리 중 오류가 발생했습니다.');
            });
    });
});

// 현재 서브탭
var currentNotiSub = '<?php echo $noti_sub; ?>';
var subLabels = { mention: '멘션', notice: '공지', interest: '관심글' };

// 모두 읽음 처리 (탭별)
function markAllNotificationsAsRead() {
    var label = subLabels[currentNotiSub] || '';
    if (!confirm(label + ' 알림을 모두 읽음 처리하시겠습니까?')) {
        return;
    }

    fetch('<?php echo G5_URL; ?>/bbs/notification_mark_read.php?read_all=1&category=' + currentNotiSub)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                });
                updateNotificationBadge(data.unread_count);
                // 서브탭 뱃지 제거
                var activeTab = document.querySelector('.noti-subtab.active .noti-badge');
                if (activeTab) activeTab.remove();
            } else {
                alert('알림 처리 실패: ' + data.message);
            }
        })
        .catch(() => {
            alert('알림 처리 중 오류가 발생했습니다.');
        });
}

// 단일 알림 삭제
function deleteNotification(event, notiId) {
    event.stopPropagation();

    fetch('<?php echo G5_URL; ?>/bbs/notification_mark_read.php?delete_id=' + notiId)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const item = event.target.closest('.notification-item');
                if (item) {
                    item.style.transition = 'opacity 0.2s';
                    item.style.opacity = '0';
                    setTimeout(() => item.remove(), 200);
                }
                updateNotificationBadge(data.unread_count);
            } else {
                alert(data.message);
            }
        })
        .catch(() => {
            alert('알림 삭제 중 오류가 발생했습니다.');
        });
}

// 읽은 알림 삭제 (탭별)
function deleteReadNotifications() {
    var label = subLabels[currentNotiSub] || '';
    if (!confirm(label + ' 읽은 알림을 모두 삭제하시겠습니까?')) return;

    fetch('<?php echo G5_URL; ?>/bbs/notification_mark_read.php?delete_read=1&category=' + currentNotiSub)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.querySelectorAll('.notification-item:not(.unread)').forEach(item => item.remove());
                updateNotificationBadge(data.unread_count);
            } else {
                alert(data.message);
            }
        })
        .catch(() => {
            alert('알림 삭제 중 오류가 발생했습니다.');
        });
}

// 알림 배지 업데이트 함수
function updateNotificationBadge(count) {
    const badge = document.querySelector('.notification-badge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }
}

// 읽음/안읽음 토글 (item 요소 또는 event 전달)
function toggleNotificationRead(itemOrEvent) {
    var item;
    if (itemOrEvent instanceof Event) {
        itemOrEvent.stopPropagation();
        item = itemOrEvent.target.closest('.notification-item');
    } else {
        item = itemOrEvent;
    }
    if (!item) return;

    var notiId = item.dataset.notiId;
    if (!notiId || item.dataset.toggling) return;
    item.dataset.toggling = '1';

    fetch('<?php echo G5_URL; ?>/bbs/notification_mark_read.php?toggle_id=' + notiId)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            delete item.dataset.toggling;
            if (!data.success) return;

            var hint = item.querySelector('.noti-swipe-hint');
            var toggleBtn = item.querySelector('.noti-toggle-btn');
            if (data.is_read) {
                item.classList.remove('unread');
                if (hint) hint.textContent = '안읽음';
                if (toggleBtn) { toggleBtn.title = '안읽음으로'; toggleBtn.innerHTML = '<i class="fa-solid fa-eye-slash"></i>'; }
            } else {
                item.classList.add('unread');
                if (hint) hint.textContent = '읽음';
                if (toggleBtn) { toggleBtn.title = '읽음으로'; toggleBtn.innerHTML = '<i class="fa-solid fa-eye"></i>'; }
            }
            updateNotificationBadge(data.unread_count);
        })
        .catch(function() { delete item.dataset.toggling; });
}

// 스와이프 제스처 (터치 + 마우스 드래그)
(function() {
    var THRESHOLD = 60;
    var MAX_SHIFT = 80;

    document.querySelectorAll('.notification-item').forEach(function(item) {
        var startX = 0, startY = 0, deltaX = 0, tracking = false, locked = false, swiped = false;
        var inner = item.querySelector('.noti-inner');

        function onStart(x, y) {
            startX = x; startY = y; deltaX = 0;
            tracking = true; locked = false; swiped = false;
        }
        function onMove(x, y, prevent) {
            if (!tracking) return;
            var dx = x - startX, dy = y - startY;
            if (!locked) {
                if (Math.abs(dy) > Math.abs(dx)) { tracking = false; return; }
                locked = true;
            }
            if (dx > 0) { deltaX = 0; return; }
            if (prevent) prevent();
            deltaX = dx;
            var shift = Math.min(Math.abs(deltaX), MAX_SHIFT);
            item.classList.add('swiping');
            inner.style.transform = 'translateX(-' + shift + 'px)';
        }
        function onEnd() {
            if (!tracking && !deltaX) return;
            tracking = false;
            item.classList.remove('swiping');
            inner.style.transform = '';
            if (Math.abs(deltaX) >= THRESHOLD) {
                swiped = true;
                toggleNotificationRead(item);
            }
        }

        // 터치
        item.addEventListener('touchstart', function(e) { var t = e.touches[0]; onStart(t.clientX, t.clientY); }, { passive: true });
        item.addEventListener('touchmove', function(e) { var t = e.touches[0]; onMove(t.clientX, t.clientY, function(){ e.preventDefault(); }); }, { passive: false });
        item.addEventListener('touchend', onEnd);

        // 마우스
        item.addEventListener('mousedown', function(e) {
            if (e.target.closest('.noti-action-btns')) return;
            onStart(e.clientX, e.clientY);
            e.preventDefault();

            function mm(e) { onMove(e.clientX, e.clientY); }
            function mu() { onEnd(); document.removeEventListener('mousemove', mm); document.removeEventListener('mouseup', mu); }
            document.addEventListener('mousemove', mm);
            document.addEventListener('mouseup', mu);
        });

        // 스와이프 직후 클릭 방지
        item.addEventListener('click', function(e) {
            if (swiped) { e.stopImmediatePropagation(); swiped = false; }
        }, true);
    });
})();
</script>
