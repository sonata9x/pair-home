<?php
if (!defined('_GNUBOARD_')) exit;

$ra0_version = 'Version ' . G5_GNUBOARD_VER ;
run_event('pre_tail');
?>

    </div> <!-- } container_wr 끝 -->
</div> <!-- } wrapper 끝 -->

<!-- 하단 시작 -->
<footer id="footer">
    <div class="footer-content">
        <div class="footer-left">
            <p><?php echo $ra0_version; ?></p>
        </div>
        <div class="footer-right">
            <p>&copy; 2025 RA0 Edition. All rights reserved.</p>
        </div>
    </div>
</footer>

<?php if ($is_member) { ?>
<script>
var notificationCountRevision = 0;
var chatCountRevision = 0;

function setCounterBadges(linkSelector, badgeSelector, count, badgeId) {
    var numericCount = parseInt(count, 10) || 0;
    var links = $(linkSelector);
    var badges = $(badgeSelector);

    if (numericCount <= 0) {
        badges.remove();
        return;
    }

    badges.text(numericCount);
    links.each(function() {
        var link = $(this);
        if (link.find(badgeSelector).length) return;

        var isMobile = link.closest('.mobile_user_menu').length > 0;
        var badge = $('<span></span>')
            .addClass(isMobile ? 'badge' : 'notification-badge')
            .addClass(badgeSelector.replace('.', ''))
            .text(numericCount);

        if (!isMobile && badgeId && !document.getElementById(badgeId)) {
            badge.attr('id', badgeId);
        }

        link.append(badge);
    });
}

function setNotificationCount(count) {
    notificationCountRevision++;
    $('#notification-bell').addClass('js-notification-count-link');
    $('#notification-count').addClass('js-notification-count-badge');
    setCounterBadges('.js-notification-count-link', '.js-notification-count-badge', count, 'notification-count');
}

function setChatCount(count) {
    chatCountRevision++;
    $('#chat-link').addClass('js-chat-count-link');
    $('#chat-count').addClass('js-chat-count-badge');
    setCounterBadges('.js-chat-count-link', '.js-chat-count-badge', count, 'chat-count');
}

// 알림 개수 업데이트 함수
function updateNotificationCount() {
    var requestRevision = notificationCountRevision;
    $.ajax({
        url: '<?php echo G5_BBS_URL; ?>/ajax.notification_count.php',
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            if (data.success && requestRevision === notificationCountRevision) {
                setNotificationCount(data.count);
            }
        }
    });
}

// 채팅 안 읽은 개수 업데이트 함수
function updateChatCount() {
    var requestRevision = chatCountRevision;
    $.ajax({
        url: '<?php echo G5_BBS_URL; ?>/ajax.chat.php?action=unread_count',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success && requestRevision === chatCountRevision) {
                setChatCount(data.count);
            }
        }
    });
}

// 페이지 포커스 상태 추적
var isPageFocused = true;
var updateInterval = null;

// 포커스 상태에 따라 업데이트 주기 조절
function startNotificationCheck() {
    if (updateInterval) clearInterval(updateInterval);

    if (isPageFocused) {
        // 페이지가 활성화되어 있을 때는 3분마다
        updateInterval = setInterval(function() {
            updateNotificationCount();
            updateChatCount();
        }, 180000); // 3분
    } else {
        // 페이지가 비활성화되어 있을 때는 10분마다
        updateInterval = setInterval(function() {
            updateNotificationCount();
            updateChatCount();
        }, 600000); // 10분
    }
}

// 페이지 포커스 이벤트 리스너
$(window).on('focus', function() {
    isPageFocused = true;
    updateNotificationCount();
    updateChatCount();
    startNotificationCheck();
});

$(window).on('blur', function() {
    isPageFocused = false;
    startNotificationCheck();
});

// 초기 시작 (3분 후 첫 체크)
setTimeout(function() {
    updateNotificationCount();
    updateChatCount();
    startNotificationCheck();
}, 180000);

// 알림 읽음 처리 함수 (mypage.php에서 사용)
function markNotificationRead(noti_id, callback) {
    $.ajax({
        url: '<?php echo G5_BBS_URL; ?>/ajax.notification_read.php',
        type: 'POST',
        data: { noti_id: noti_id },
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                // 알림 개수 업데이트
                setNotificationCount(data.unread_count);

                if (callback) callback(data);
            }
        }
    });
}
</script>
<?php } ?>

<?php
run_event('tail');
include_once(G5_PATH.'/tail.sub.php');
?>