<?php
/**
 * RA0 Edition 채팅 메인 페이지
 */

include_once('./_common.php');
include_once(G5_LIB_PATH . '/chat.lib.php');

// 로그인 체크
if (!$member['mb_id']) {
    alert('로그인이 필요합니다.', G5_BBS_URL . '/login.php?url=' . urlencode(G5_URL . $_SERVER['REQUEST_URI']));
}

$mb_id = $member['mb_id'];

// 기존 설치도 채팅 페이지에 진입하면 누락된 활동 시각 컬럼을 한 번 보강한다.
chat_migrate_member_schema();

// 채팅방 개설 권한 체크
$can_create_room = can_create_chat_room($member);
$required_level = (int)get_chat_config('chat_create_level', 1);

// 특정 채팅방 접근
$cr_id = isset($_GET['cr_id']) ? (int)$_GET['cr_id'] : 0;

// 특정 회원과 1:1 채팅 시작
$target_mb_id = isset($_GET['mb_id']) ? trim($_GET['mb_id']) : '';

if ($target_mb_id && $target_mb_id != $mb_id) {
    // 기존 방이 있는지 먼저 확인
    $existing_room = chat_find_private_room($mb_id, $target_mb_id);
    if ($existing_room) {
        // 기존 방 있으면 바로 이동
        goto_url(G5_BBS_URL . '/chat.php?cr_id=' . $existing_room);
    } elseif ($can_create_room) {
        // 권한 있으면 새 방 생성
        $cr_id = chat_get_or_create_private_room($mb_id, $target_mb_id);
        if ($cr_id) {
            goto_url(G5_BBS_URL . '/chat.php?cr_id=' . $cr_id);
        }
    } else {
        alert('채팅방 개설은 레벨 ' . $required_level . ' 이상만 가능합니다.', G5_BBS_URL . '/chat.php');
    }
}

// 채팅방 접근 시 권한 체크
if ($cr_id) {
    if (!chat_is_member($cr_id, $mb_id)) {
        alert('참여 중인 채팅방이 아닙니다.', G5_BBS_URL . '/chat.php');
    }
    $current_room = chat_get_room($cr_id, $mb_id);
}

$g5['title'] = '채팅';
include_once(G5_PATH . '/head.php');
?>

<style>
/* 채팅 컨테이너 */
.chat-container {
    display: flex;
    height: calc(100vh - 200px);
    min-height: 500px;
    background: var(--card-bg-color);
    border: 1px solid var(--card-border-color);
    border-radius: var(--card-border-radius);
    overflow: hidden;
}

/* 채팅방 목록 (좌측) */
.chat-sidebar {
    width: 320px;
    min-width: 280px;
    border-right: 1px solid var(--card-border-color);
    display: flex;
    flex-direction: column;
    background: var(--container-bg-color);
}

.chat-sidebar-header {
    padding: 15px;
    border-bottom: 1px solid var(--card-border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-sidebar-header h3 {
    margin: 0;
    font-size: 1em;
}

.chat-sidebar-actions {
    display: flex;
    gap: 8px;
}

.chat-sidebar-actions button {
    padding: 6px 10px;
    background: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.85em;
}

.chat-sidebar-actions button:hover {
    opacity: 0.9;
}

.chat-room-list {
    flex: 1;
    overflow-y: auto;
    list-style: none;
    padding: 0;
    margin: 0;
}

.chat-room-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    border-bottom: 1px solid var(--card-border-color);
    cursor: pointer;
    transition: background 0.2s;
}

.chat-room-item:hover {
    background: var(--bg-glass);
}

.chat-room-item.active {
    background: var(--bg-glass-dark);
    border-left: 3px solid var(--accent-color);
}

.chat-room-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: var(--btn-secondary-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    font-size: 1.2em;
    overflow: hidden;
}

.chat-room-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.chat-room-info {
    flex: 1;
    min-width: 0;
}

.chat-room-name {
    font-weight: 600;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chat-room-preview {
    font-size: 0.85em;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chat-room-meta {
    text-align: right;
    font-size: 0.75em;
    color: var(--text-muted);
}

.chat-room-unread {
    display: inline-block;
    min-width: 20px;
    padding: 2px 6px;
    background: var(--error-color);
    color: white;
    border-radius: 10px;
    font-size: 0.75em;
    text-align: center;
    margin-top: 5px;
}

/* 채팅 영역 (우측) */
.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.chat-main-header {
    padding: 15px;
    border-bottom: 1px solid var(--card-border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--container-bg-color);
}

.chat-main-header h3 {
    margin: 0;
    font-size: 1em;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chat-member-count {
    font-size: 0.8em;
    color: var(--text-muted);
    font-weight: normal;
}

.chat-header-actions {
    display: flex;
    gap: 8px;
}

.chat-header-actions button {
    padding: 6px 10px;
    background: var(--btn-secondary-bg);
    color: var(--btn-secondary-text);
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.85em;
}

.chat-header-actions button:hover {
    opacity: 0.8;
}

.chat-header-actions button.danger {
    background: var(--error-color);
    color: white;
}

.chat-back-btn {
    display: none;
    padding: 6px 10px;
    color: var(--content-font-color);
    text-decoration: none;
    font-size: 1em;
    margin-right: 4px;
}

/* 메시지 영역 */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.chat-message {
    display: flex;
    gap: 10px;
    max-width: 80%;
}

.chat-message.mine {
    flex-direction: row-reverse;
    margin-left: auto;
}

.chat-message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--btn-secondary-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
}

.chat-message-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.chat-message-content {
    display: flex;
    flex-direction: column;
}

.chat-message.mine .chat-message-content {
    align-items: flex-end;
}

.chat-message-sender {
    font-size: 0.8em;
    color: var(--text-muted);
    margin-bottom: 3px;
}

.chat-message-bubble {
    padding: 10px 14px;
    border-radius: 16px;
    background: var(--container-bg-color);
    border: 1px solid var(--card-border-color);
    line-height: 1.5;
    word-break: break-word;
}

.chat-message.mine .chat-message-bubble {
    background: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    border: none;
}

.chat-message-time {
    font-size: 0.7em;
    color: var(--text-muted);
    margin-top: 3px;
}

.chat-message-system {
    text-align: center;
    font-size: 0.85em;
    color: var(--text-muted);
    padding: 10px;
}

/* 입력 영역 */
.chat-input-area {
    padding: 15px;
    border-top: 1px solid var(--card-border-color);
    background: var(--container-bg-color);
}

.chat-input-form {
    display: flex;
    gap: 10px;
}

.chat-input-form textarea {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid var(--card-border-color);
    border-radius: 20px;
    resize: none;
    font-size: 0.95em;
    max-height: 100px;
    background: var(--card-bg-color);
    color: var(--content-font-color);
}

.chat-input-form textarea:focus {
    outline: none;
    border-color: var(--accent-color);
}

.chat-input-form button {
    padding: 10px 20px;
    background: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    border: none;
    border-radius: 20px;
    cursor: pointer;
    font-weight: 600;
}

.chat-input-form button:hover {
    opacity: 0.9;
}

.chat-input-form button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* 빈 상태 */
.chat-empty {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    padding: 40px;
}

.chat-empty i {
    font-size: 4em;
    margin-bottom: 20px;
    opacity: 0.3;
}

/* 모달 */
.chat-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
}

.chat-modal-overlay.show {
    opacity: 1;
    visibility: visible;
}

.chat-modal {
    background: var(--card-bg-color);
    border-radius: var(--card-border-radius);
    width: 90%;
    max-width: 450px;
    max-height: 80vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.chat-modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid var(--card-border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-modal-header h3 {
    margin: 0;
}

.chat-modal-close {
    background: none;
    border: none;
    font-size: 1.5em;
    cursor: pointer;
    color: var(--text-muted);
}

.chat-modal-body {
    padding: 20px;
    overflow-y: auto;
    flex: 1;
}

.chat-modal-footer {
    padding: 15px 20px;
    border-top: 1px solid var(--card-border-color);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.chat-modal-footer button {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.chat-modal-footer .btn-primary {
    background: var(--btn-primary-bg);
    color: var(--btn-primary-text);
}

.chat-modal-footer .btn-secondary {
    background: var(--btn-secondary-bg);
    color: var(--btn-secondary-text);
}

/* 검색/입력 */
.chat-search-input {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid var(--card-border-color);
    border-radius: 4px;
    margin-bottom: 15px;
    background: var(--container-bg-color);
    color: var(--content-font-color);
}

.chat-member-select {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 250px;
    overflow-y: auto;
}

.chat-member-select-item {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    border: 1px solid var(--card-border-color);
    border-radius: 4px;
    cursor: pointer;
}

.chat-member-select-item:hover {
    background: var(--bg-glass);
}

.chat-member-select-item.selected {
    background: var(--bg-glass-dark);
    border-color: var(--accent-color);
}

.chat-member-select-item img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    margin-right: 10px;
}

.chat-member-select-item .placeholder-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--btn-secondary-bg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
}

/* 반응형 */
@media (max-width: 768px) {
    .chat-container {
        flex-direction: column;
        height: calc(100vh - 120px);
        min-height: 0;
    }

    /* 채팅방 진입 시 사이드바 숨김 */
    .chat-container.has-room .chat-sidebar {
        display: none;
    }

    /* 채팅방 미진입 시 사이드바만 표시 */
    .chat-sidebar {
        width: 100%;
        flex: 1;
        min-height: 0;
        border-right: none;
        border-bottom: 1px solid var(--card-border-color);
    }

    .chat-sidebar.hidden {
        display: none;
    }

    .chat-main {
        flex: 1;
        min-height: 0;
    }

    .chat-messages {
        min-height: 0;
    }

    .chat-back-btn {
        display: block;
    }

    .chat-message {
        max-width: 90%;
    }
}
</style>

<div class="chat-container<?php echo $cr_id ? ' has-room' : ''; ?>">
    <!-- 채팅방 목록 -->
    <div class="chat-sidebar" id="chatSidebar">
        <div class="chat-sidebar-header">
            <h3>채팅</h3>
            <?php if ($can_create_room) { ?>
            <div class="chat-sidebar-actions">
                <button onclick="openNewChatModal()" title="새 채팅">
                    <i class="fa-solid fa-plus"></i>
                </button>
                <button onclick="openGroupModal()" title="그룹 생성">
                    <i class="fa-solid fa-users"></i>
                </button>
            </div>
            <?php } ?>
        </div>
        <ul class="chat-room-list" id="chatRoomList">
            <!-- 동적 로드 -->
        </ul>
    </div>

    <!-- 채팅 영역 -->
    <div class="chat-main" id="chatMain">
        <?php if ($cr_id && $current_room) { ?>
        <!-- 채팅방 헤더 -->
        <div class="chat-main-header">
            <a href="<?php echo G5_BBS_URL; ?>/chat.php" class="chat-back-btn" title="목록으로">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h3>
                <?php echo htmlspecialchars($current_room['display_name']); ?>
                <?php if ($current_room['cr_type'] == 'group') { ?>
                <span class="chat-member-count">(<?php echo $current_room['member_count']; ?>명)</span>
                <?php } ?>
            </h3>
            <div class="chat-header-actions">
                <?php if ($current_room['cr_type'] == 'group') { ?>
                <button onclick="openInviteModal()" title="멤버 초대">
                    <i class="fa-solid fa-user-plus"></i>
                </button>
                <button onclick="openMembersModal()" title="참여자 보기">
                    <i class="fa-solid fa-users"></i>
                </button>
                <?php } ?>
                <button onclick="leaveRoom()" class="danger" title="나가기">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </div>
        </div>

        <!-- 메시지 영역 -->
        <div class="chat-messages" id="chatMessages">
            <!-- 동적 로드 -->
        </div>

        <!-- 입력 영역 -->
        <div class="chat-input-area">
            <div class="chat-input-form" id="chatInputForm">
                <textarea id="chatInput" placeholder="메시지를 입력하세요..." rows="1"
                          onkeydown="handleKeyDown(event)"></textarea>
                <button type="button" id="chatSendBtn" onclick="sendMessage(event)">전송</button>
            </div>
        </div>

        <?php } else { ?>
        <!-- 빈 상태 -->
        <div class="chat-empty">
            <i class="fa-regular fa-comments"></i>
            <p>채팅방을 선택하거나<br>새 대화를 시작하세요</p>
        </div>
        <?php } ?>
    </div>
</div>

<!-- 새 채팅 모달 -->
<div class="chat-modal-overlay" id="newChatModal">
    <div class="chat-modal">
        <div class="chat-modal-header">
            <h3>새 채팅</h3>
            <button class="chat-modal-close" onclick="closeModal('newChatModal')">&times;</button>
        </div>
        <div class="chat-modal-body">
            <input type="text" class="chat-search-input" id="searchMemberInput"
                   placeholder="캐릭터명, 회원 ID 또는 닉네임 검색..." onkeyup="searchMembers()">
            <div class="chat-member-select" id="searchMemberResults">
                <!-- 검색 결과 -->
            </div>
        </div>
        <div class="chat-modal-footer">
            <button class="btn-secondary" onclick="closeModal('newChatModal')">취소</button>
            <button class="btn-primary" onclick="startPrivateChat()">대화 시작</button>
        </div>
    </div>
</div>

<!-- 그룹 생성 모달 -->
<div class="chat-modal-overlay" id="groupModal">
    <div class="chat-modal">
        <div class="chat-modal-header">
            <h3>그룹 채팅 만들기</h3>
            <button class="chat-modal-close" onclick="closeModal('groupModal')">&times;</button>
        </div>
        <div class="chat-modal-body">
            <input type="text" class="chat-search-input" id="groupNameInput"
                   placeholder="채팅방 이름 (선택)">
            <input type="text" class="chat-search-input" id="groupSearchInput"
                   placeholder="캐릭터명 또는 회원 검색..." onkeyup="searchGroupMembers()">
            <div class="chat-member-select" id="groupSearchResults">
                <!-- 검색 결과 -->
            </div>
            <div id="selectedMembers" style="margin-top: 15px;"></div>
        </div>
        <div class="chat-modal-footer">
            <button class="btn-secondary" onclick="closeModal('groupModal')">취소</button>
            <button class="btn-primary" onclick="createGroupChat()">생성</button>
        </div>
    </div>
</div>

<!-- 멤버 초대 모달 -->
<div class="chat-modal-overlay" id="inviteModal">
    <div class="chat-modal">
        <div class="chat-modal-header">
            <h3>멤버 초대</h3>
            <button class="chat-modal-close" onclick="closeModal('inviteModal')">&times;</button>
        </div>
        <div class="chat-modal-body">
            <input type="text" class="chat-search-input" id="inviteSearchInput"
                   placeholder="캐릭터명 또는 회원 검색..." onkeyup="searchInviteMembers()">
            <div class="chat-member-select" id="inviteSearchResults">
                <!-- 검색 결과 -->
            </div>
        </div>
        <div class="chat-modal-footer">
            <button class="btn-secondary" onclick="closeModal('inviteModal')">취소</button>
            <button class="btn-primary" onclick="inviteMembers()">초대</button>
        </div>
    </div>
</div>

<!-- 참여자 목록 모달 -->
<div class="chat-modal-overlay" id="membersModal">
    <div class="chat-modal">
        <div class="chat-modal-header">
            <h3>참여자 목록</h3>
            <button class="chat-modal-close" onclick="closeModal('membersModal')">&times;</button>
        </div>
        <div class="chat-modal-body">
            <div class="chat-member-select" id="membersListContainer">
                <!-- 동적 로드 -->
            </div>
        </div>
        <div class="chat-modal-footer">
            <button class="btn-secondary" onclick="closeModal('membersModal')">닫기</button>
        </div>
    </div>
</div>

<script>
const CHAT_API = '<?php echo G5_BBS_URL; ?>/ajax.chat.php';
const MY_MB_ID = '<?php echo $mb_id; ?>';
const CURRENT_ROOM_ID = <?php echo $cr_id ?: 0; ?>;
const POLLING_INTERVAL = <?php echo (int)get_chat_config('chat_polling_active', CHAT_DEFAULT_POLLING_ACTIVE); ?>; // 활성 폴링
const POLLING_INTERVAL_LIST = <?php echo (int)get_chat_config('chat_polling_inactive', CHAT_DEFAULT_POLLING_INACTIVE); ?>; // 비활성 폴링
const CAN_CREATE_ROOM = <?php echo $can_create_room ? 'true' : 'false'; ?>;
const REQUIRED_LEVEL = <?php echo $required_level; ?>;

let lastMessageId = 0;
let pollingTimer = null;
let listPollingTimer = null;
let selectedMember = null;
let selectedGroupMembers = [];
let selectedInviteMembers = [];
let isInitialLoad = true; // 초기 로드 여부 (첫 로드 시에는 소리 안 남)

// 알림음 재생 함수 (Web Audio API)
let audioContext = null;
function playNotificationSound() {
    try {
        if (!audioContext) {
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
        }

        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        oscillator.frequency.value = 800;
        oscillator.type = 'sine';

        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.15);

        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.15);
    } catch (e) {
        // 오디오 재생 실패 무시
    }
}

// 초기화
document.addEventListener('DOMContentLoaded', function() {
    if (CURRENT_ROOM_ID) {
        // 두 요청은 병렬로 시작하고, 응답 세대값으로 오래된 배지 응답만 무시한다.
        loadRoomList();
        loadMessages();
        startPolling();

        // 스크롤 이벤트: 위로 올리면 이전 메시지 로드
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            lastChatScrollTop = chatMessages.scrollTop;
            chatMessages.addEventListener('scroll', function() {
                const currentTop = this.scrollTop;
                const isMovingUp = currentTop < lastChatScrollTop;
                lastChatScrollTop = currentTop;

                if (isMovingUp && currentTop < 100 && !isLoadingMore && hasMoreOlderMessages && oldestMessageId > 0) {
                    loadOlderMessages();
                }
            });
        }
    } else {
        loadRoomList();
    }

    startListPolling();
});

// 채팅방 목록 로드
function loadRoomList() {
    const requestUnreadRevision = unreadSyncRevision;

    return fetch(CHAT_API + '?action=get_rooms')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                renderRoomList(data.rooms);
                if (requestUnreadRevision === unreadSyncRevision) {
                    syncUnreadBadges(data);
                }
            }
            return data;
        })
        .catch(() => null);
}

// 헤더/모바일의 채팅·일반 알림 배지를 즉시 동기화
function syncUnreadBadges(data) {
    if (!data) return;

    if (Object.prototype.hasOwnProperty.call(data, 'unread_total') && typeof setChatCount === 'function') {
        setChatCount(data.unread_total);
    }

    if (Object.prototype.hasOwnProperty.call(data, 'notification_unread_total') && typeof setNotificationCount === 'function') {
        setNotificationCount(data.notification_unread_total);
    }
}

function clearCurrentRoomUnread() {
    document.querySelectorAll('.chat-room-item.active .chat-room-unread').forEach(badge => badge.remove());
}

// 채팅방 목록 렌더링
function renderRoomList(rooms) {
    const list = document.getElementById('chatRoomList');

    if (rooms.length === 0) {
        list.innerHTML = '<li style="padding: 20px; text-align: center; color: var(--text-muted);">채팅방이 없습니다</li>';
        return;
    }

    list.innerHTML = rooms.map(room => {
        const isActive = room.cr_id == CURRENT_ROOM_ID;
        const unreadCount = (isActive && currentRoomReadConfirmed)
            ? 0
            : parseInt(room.unread_count || 0, 10);
        const avatar = room.display_image
            ? `<img src="${room.display_image}" alt="">`
            : (room.cr_type === 'group' ? '<i class="fa-solid fa-users"></i>' : '<i class="fa-solid fa-user"></i>');

        return `
            <li class="chat-room-item ${isActive ? 'active' : ''}"
                onclick="location.href='chat.php?cr_id=${room.cr_id}'">
                <div class="chat-room-avatar">${avatar}</div>
                <div class="chat-room-info">
                    <div class="chat-room-name">${escapeHtml(room.display_name || '(알 수 없음)')}</div>
                    <div class="chat-room-preview">${escapeHtml(room.cr_last_message || '')}</div>
                </div>
                <div class="chat-room-meta">
                    <div>${formatTime(room.cr_last_message_at)}</div>
                    ${unreadCount > 0 ? `<span class="chat-room-unread">${unreadCount}</span>` : ''}
                </div>
            </li>
        `;
    }).join('');
}

// 메시지 로드 (초기 로드 시 최근 30개만, 폴링 시 새 메시지만)
let oldestMessageId = 0; // 가장 오래된 메시지 ID (이전 메시지 로드용)
let isLoadingMore = false;
let isLoadingAfter = false; // 새 메시지 폴링 동시 요청 가드 (중복 렌더 방지)
let hasMoreOlderMessages = true;
let lastChatScrollTop = 0;
let unreadSyncRevision = 0;
let currentRoomReadConfirmed = false;

function loadMessages(before = false) {
    if (!CURRENT_ROOM_ID) return Promise.resolve(null);

    // 폴링/전송 후 호출이 동시에 in-flight 상태로 겹치면 같은 last_id로 두 요청이 나가
    // 동일 메시지를 두 번 렌더하는 문제가 발생. after 방향은 1개만 허용.
    if (!before) {
        if (isLoadingAfter) return Promise.resolve(null);
        isLoadingAfter = true;
    }

    const isInitialRequest = !before && lastMessageId === 0;
    const requestLimit = before ? 30 : (isInitialRequest ? 30 : 50);

    const params = new URLSearchParams({
        action: 'get_messages',
        cr_id: CURRENT_ROOM_ID,
        last_id: before ? oldestMessageId : lastMessageId,
        limit: requestLimit, // 초기/이전 로드는 30개, 폴링은 50개
        direction: before ? 'before' : 'after'
    });

    return fetch(CHAT_API + '?' + params)
        .then(res => res.json())
        .then(data => {
            if (!data.success || !Array.isArray(data.messages)) {
                return data;
            }

            if (before || isInitialRequest) {
                hasMoreOlderMessages = data.messages.length === requestLimit;
            }

            if (!before) {
                currentRoomReadConfirmed = data.read_success === true;
            }

            if (Object.prototype.hasOwnProperty.call(data, 'unread_total')) {
                unreadSyncRevision++;
                syncUnreadBadges(data);
            }

            if (data.success && data.messages.length > 0) {
                renderMessages(data.messages, !before);

                // 메시지 ID 업데이트
                const msgIds = data.messages.map(m => parseInt(m.msg_id));
                const maxId = Math.max(...msgIds);
                const minId = Math.min(...msgIds);

                // 새 메시지 알림음 (초기 로드가 아니고, 새 메시지가 있고, 상대방이 보낸 경우)
                if (!before && !isInitialLoad && lastMessageId > 0) {
                    // 새로 도착한 메시지 중 내가 보낸 게 아닌 것 확인
                    const newFromOthers = data.messages.filter(m =>
                        parseInt(m.msg_id) > lastMessageId && m.mb_id !== MY_MB_ID
                    );
                    if (newFromOthers.length > 0) {
                        playNotificationSound();
                    }
                }

                if (maxId > lastMessageId) {
                    lastMessageId = maxId;
                }

                // 이전 메시지 로드용 ID 저장
                if (before || oldestMessageId === 0) {
                    oldestMessageId = minId;
                }

            }

            if (!before && data.read_success === true) {
                clearCurrentRoomUnread();
            }

            if (!before) {
                // 빈 방이어도 초기 요청이 끝났으면 이후 메시지는 새 알림으로 처리한다.
                if (isInitialLoad) {
                    isInitialLoad = false;
                }
            }

            return data;
        })
        .catch(() => null)
        .finally(() => {
            if (before) {
                isLoadingMore = false;
            } else {
                isLoadingAfter = false;
            }
        });
}

// 이전 메시지 더 로드
function loadOlderMessages() {
    if (isLoadingMore || !hasMoreOlderMessages || oldestMessageId === 0) return Promise.resolve(null);

    isLoadingMore = true;
    return loadMessages(true);
}

// 메시지 렌더링
function renderMessages(messages, append = true) {
    const container = document.getElementById('chatMessages');
    if (!container) return 0;

    // 이미 렌더된 msg_id는 제외 (race condition으로 인한 중복 append 방지)
    const existingIds = new Set();
    container.querySelectorAll('[data-msg-id]').forEach(el => {
        existingIds.add(el.getAttribute('data-msg-id'));
    });
    messages = messages.filter(m => !existingIds.has(String(m.msg_id)));
    if (messages.length === 0) return 0;

    const html = messages.map(msg => {
        if (msg.msg_type === 'system') {
            return `<div class="chat-message-system" data-msg-id="${msg.msg_id}">${escapeHtml(msg.msg_content)}</div>`;
        }

        const isMine = msg.mb_id === MY_MB_ID;
        const avatar = msg.mb_image_url
            ? `<img src="${msg.mb_image_url}" alt="">`
            : '<i class="fa-solid fa-user"></i>';
        // 캐릭터 이름 우선 표시
        const senderName = msg.display_name || msg.ch_name || msg.mb_name || msg.mb_id;

        return `
            <div class="chat-message ${isMine ? 'mine' : ''}" data-msg-id="${msg.msg_id}">
                ${!isMine ? `<div class="chat-message-avatar">${avatar}</div>` : ''}
                <div class="chat-message-content">
                    ${!isMine ? `<div class="chat-message-sender">${escapeHtml(senderName)}</div>` : ''}
                    <div class="chat-message-bubble">${escapeHtml(msg.msg_content).replace(/\n/g, '<br>')}</div>
                    <div class="chat-message-time">${formatTime(msg.msg_created_at)}</div>
                </div>
            </div>
        `;
    }).join('');

    if (append) {
        container.insertAdjacentHTML('beforeend', html);
        // 확실한 스크롤을 위해 약간 지연
        setTimeout(() => {
            container.scrollTop = container.scrollHeight;
            lastChatScrollTop = container.scrollTop;
        }, 10);
    } else {
        const oldTop = container.scrollTop;
        const oldHeight = container.scrollHeight;

        container.insertAdjacentHTML('afterbegin', html);
        container.scrollTop = oldTop + (container.scrollHeight - oldHeight);
        lastChatScrollTop = container.scrollTop;
    }

    return messages.length;
}

// 메시지 전송
function sendMessage(e) {
    if (e) e.preventDefault();

    const input = document.getElementById('chatInput');
    const btn = document.getElementById('chatSendBtn');
    const content = input.value.trim();

    if (!content || !CURRENT_ROOM_ID) return false;

    btn.disabled = true;

    fetch(CHAT_API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'send_message',
            cr_id: CURRENT_ROOM_ID,
            content: content
        })
    })
    .then(res => res.text()) // 먼저 텍스트로 받기
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                input.value = '';
                loadMessages();
            } else {
                alert(data.error || '전송 실패');
            }
        } catch (err) {
            alert('서버 응답 오류');
        }
    })
    .catch(err => {
        alert('전송 중 오류 발생');
    })
    .finally(() => {
        btn.disabled = false;
        input.focus();
    });

    return false;
}

// Enter 키 처리 (Enter: 전송, Shift+Enter: 줄바꿈)
function handleKeyDown(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        e.stopPropagation();
        sendMessage(e);
    }
}

// 폴링 시작 (채팅방)
function startPolling() {
    if (pollingTimer) clearInterval(pollingTimer);
    pollingTimer = setInterval(loadMessages, POLLING_INTERVAL);
}

// 폴링 시작 (목록)
function startListPolling() {
    if (listPollingTimer) clearInterval(listPollingTimer);
    listPollingTimer = setInterval(loadRoomList, POLLING_INTERVAL_LIST);
}

// 모달 열기/닫기
function openModal(id) {
    document.getElementById(id).classList.add('show');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

function openNewChatModal() {
    if (!CAN_CREATE_ROOM) {
        alert('채팅방 개설은 레벨 ' + REQUIRED_LEVEL + ' 이상만 가능합니다.');
        return;
    }
    selectedMember = null;
    document.getElementById('searchMemberInput').value = '';
    document.getElementById('searchMemberResults').innerHTML = '';
    openModal('newChatModal');
}

function openGroupModal() {
    if (!CAN_CREATE_ROOM) {
        alert('채팅방 개설은 레벨 ' + REQUIRED_LEVEL + ' 이상만 가능합니다.');
        return;
    }
    selectedGroupMembers = [];
    document.getElementById('groupNameInput').value = '';
    document.getElementById('groupSearchInput').value = '';
    document.getElementById('groupSearchResults').innerHTML = '';
    document.getElementById('selectedMembers').innerHTML = '';
    openModal('groupModal');
}

function openInviteModal() {
    selectedInviteMembers = [];
    document.getElementById('inviteSearchInput').value = '';
    document.getElementById('inviteSearchResults').innerHTML = '';
    openModal('inviteModal');
}

function openMembersModal() {
    const container = document.getElementById('membersListContainer');
    container.innerHTML = '<div style="text-align:center;padding:20px;color:var(--text-muted);">불러오는 중...</div>';
    openModal('membersModal');

    fetch(CHAT_API + '?action=get_members&cr_id=' + CURRENT_ROOM_ID)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.members.length > 0) {
                container.innerHTML = data.members.map(m => {
                    const avatar = (m.cp_portrait_image || m.mb_image_url)
                        ? `<img src="${m.cp_portrait_image || m.mb_image_url}" alt="">`
                        : '<div class="placeholder-avatar"><i class="fa-solid fa-user"></i></div>';
                    const displayName = m.ch_name || m.mb_name || m.mb_id;
                    const subInfo = m.ch_name ? escapeHtml(m.mb_name || m.mb_id) : escapeHtml(m.mb_id);

                    return `
                        <div class="chat-member-select-item" style="cursor:default;">
                            ${avatar}
                            <div>
                                <div>${escapeHtml(displayName)}</div>
                                <div style="font-size:0.8em;color:var(--text-muted);">${subInfo}</div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                container.innerHTML = '<div style="text-align:center;padding:20px;color:var(--text-muted);">참여자가 없습니다</div>';
            }
        })
        .catch(() => {
            container.innerHTML = '<div style="text-align:center;padding:20px;color:var(--text-muted);">불러오기 실패</div>';
        });
}

// 회원 검색
let searchTimeout = null;

function searchMembers() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const keyword = document.getElementById('searchMemberInput').value.trim();
        if (keyword.length < 1) {
            document.getElementById('searchMemberResults').innerHTML = '';
            return;
        }

        fetch(CHAT_API + '?action=search_members&keyword=' + encodeURIComponent(keyword))
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderSearchResults('searchMemberResults', data.members, 'single');
                }
            });
    }, 300);
}

function searchGroupMembers() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const keyword = document.getElementById('groupSearchInput').value.trim();
        if (keyword.length < 1) {
            document.getElementById('groupSearchResults').innerHTML = '';
            return;
        }

        fetch(CHAT_API + '?action=search_members&keyword=' + encodeURIComponent(keyword))
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderSearchResults('groupSearchResults', data.members, 'group');
                }
            });
    }, 300);
}

function searchInviteMembers() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const keyword = document.getElementById('inviteSearchInput').value.trim();
        if (keyword.length < 1) {
            document.getElementById('inviteSearchResults').innerHTML = '';
            return;
        }

        fetch(CHAT_API + '?action=search_members&keyword=' + encodeURIComponent(keyword) + '&cr_id=' + CURRENT_ROOM_ID)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderSearchResults('inviteSearchResults', data.members, 'invite');
                }
            });
    }, 300);
}

function renderSearchResults(containerId, members, mode) {
    const container = document.getElementById(containerId);

    if (members.length === 0) {
        container.innerHTML = '<div style="text-align: center; color: var(--text-muted); padding: 20px;">검색 결과가 없습니다</div>';
        return;
    }

    container.innerHTML = members.map(m => {
        const avatar = m.mb_image_url
            ? `<img src="${m.mb_image_url}" alt="">`
            : '<div class="placeholder-avatar"><i class="fa-solid fa-user"></i></div>';

        let isSelected = false;
        if (mode === 'single') {
            isSelected = selectedMember === m.mb_id;
        } else if (mode === 'group') {
            isSelected = selectedGroupMembers.includes(m.mb_id);
        } else if (mode === 'invite') {
            isSelected = selectedInviteMembers.includes(m.mb_id);
        }

        // 캐릭터 이름 우선, 없으면 회원 닉네임(mb_name)
        const displayName = m.ch_name || m.mb_name || m.mb_id;
        const subInfo = m.ch_name ? escapeHtml(m.mb_name || m.mb_id) : escapeHtml(m.mb_id);

        return `
            <div class="chat-member-select-item ${isSelected ? 'selected' : ''}"
                 onclick="selectMember('${m.mb_id}', '${mode}', this)">
                ${avatar}
                <div>
                    <div>${escapeHtml(displayName)}</div>
                    <div style="font-size: 0.8em; color: var(--text-muted);">${subInfo}</div>
                </div>
            </div>
        `;
    }).join('');
}

function selectMember(mbId, mode, el) {
    if (mode === 'single') {
        // 단일 선택
        document.querySelectorAll('#searchMemberResults .chat-member-select-item').forEach(item => {
            item.classList.remove('selected');
        });
        el.classList.add('selected');
        selectedMember = mbId;
    } else if (mode === 'group') {
        // 다중 선택
        const idx = selectedGroupMembers.indexOf(mbId);
        if (idx > -1) {
            selectedGroupMembers.splice(idx, 1);
            el.classList.remove('selected');
        } else {
            selectedGroupMembers.push(mbId);
            el.classList.add('selected');
        }
        updateSelectedMembers();
    } else if (mode === 'invite') {
        const idx = selectedInviteMembers.indexOf(mbId);
        if (idx > -1) {
            selectedInviteMembers.splice(idx, 1);
            el.classList.remove('selected');
        } else {
            selectedInviteMembers.push(mbId);
            el.classList.add('selected');
        }
    }
}

function updateSelectedMembers() {
    const container = document.getElementById('selectedMembers');
    if (selectedGroupMembers.length === 0) {
        container.innerHTML = '';
        return;
    }
    container.innerHTML = '<div style="font-size: 0.85em; color: var(--text-muted);">선택됨: ' + selectedGroupMembers.join(', ') + '</div>';
}

// 1:1 채팅 시작
function startPrivateChat() {
    if (!selectedMember) {
        alert('대화 상대를 선택해주세요.');
        return;
    }

    fetch(CHAT_API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'start_private',
            target_mb_id: selectedMember
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.href = 'chat.php?cr_id=' + data.cr_id;
        } else {
            alert(data.error || '채팅 시작 실패');
        }
    });
}

// 그룹 채팅 생성
function createGroupChat() {
    if (selectedGroupMembers.length === 0) {
        alert('초대할 멤버를 선택해주세요.');
        return;
    }

    const roomName = document.getElementById('groupNameInput').value.trim();

    fetch(CHAT_API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'create_group',
            room_name: roomName,
            member_ids: JSON.stringify(selectedGroupMembers)
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.href = 'chat.php?cr_id=' + data.cr_id;
        } else {
            alert(data.error || '그룹 생성 실패');
        }
    });
}

// 멤버 초대
function inviteMembers() {
    if (selectedInviteMembers.length === 0) {
        alert('초대할 멤버를 선택해주세요.');
        return;
    }

    fetch(CHAT_API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'invite_members',
            cr_id: CURRENT_ROOM_ID,
            member_ids: JSON.stringify(selectedInviteMembers)
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeModal('inviteModal');
            loadMessages();
        } else {
            alert(data.error || '초대 실패');
        }
    });
}

// 채팅방 나가기
function leaveRoom() {
    if (!confirm('채팅방을 나가시겠습니까?')) return;

    fetch(CHAT_API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: 'leave_room',
            cr_id: CURRENT_ROOM_ID
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.href = 'chat.php';
        } else {
            alert(data.error || '나가기 실패');
        }
    });
}

// 유틸리티
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;')
              .replace(/</g, '&lt;')
              .replace(/>/g, '&gt;')
              .replace(/"/g, '&quot;');
}

function formatTime(datetime) {
    if (!datetime || datetime === '0000-00-00 00:00:00') return '';

    const date = new Date(datetime.replace(' ', 'T'));
    const now = new Date();
    const diff = now - date;

    // 오늘이면 시간만
    if (date.toDateString() === now.toDateString()) {
        return date.getHours().toString().padStart(2, '0') + ':' +
               date.getMinutes().toString().padStart(2, '0');
    }

    // 일주일 이내면 요일
    if (diff < 7 * 24 * 60 * 60 * 1000) {
        const days = ['일', '월', '화', '수', '목', '금', '토'];
        return days[date.getDay()] + '요일';
    }

    // 그 외
    return (date.getMonth() + 1) + '/' + date.getDate();
}
</script>

<?php
include_once(G5_PATH . '/tail.php');
?>
