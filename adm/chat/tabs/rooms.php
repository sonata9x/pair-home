<?php
if (!defined('_GNUBOARD_')) exit;

$prefix = G5_TABLE_PREFIX;

// 커뮤니티 확장팩이 설치된 경우에만 캐릭터 정보를 조회한다.
$chat_has_community = function_exists('is_community_installed') && is_community_installed();
$chat_character_table = G5_TABLE_PREFIX . 'community_character';
$chat_character_select = $chat_has_community ? ', ch.ch_name' : ', NULL AS ch_name';

// 좌측 방 목록 검색
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'name';
$search_keyword = isset($_GET['search_keyword']) ? trim($_GET['search_keyword']) : '';
$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';

$where = "1=1";
if ($filter_type) {
    $where .= " AND r.cr_type = '" . sql_real_escape_string($filter_type) . "'";
}
if ($search_keyword) {
    if ($search_type == 'member') {
        $where .= " AND r.cr_id IN (SELECT cr_id FROM `{$prefix}chat_member` WHERE mb_id LIKE '%" . sql_real_escape_string($search_keyword) . "%')";
    } else {
        $where .= " AND r.cr_name LIKE '%" . sql_real_escape_string($search_keyword) . "%'";
    }
}

// 좌측: 방 목록
$rooms = [];
$sql = "SELECT r.*,
        (SELECT COUNT(*) FROM `{$prefix}chat_member` WHERE cr_id = r.cr_id AND cm_left_at IS NULL) as member_count,
        (SELECT COUNT(*) FROM `{$prefix}chat_message` WHERE cr_id = r.cr_id) as message_count
        FROM `{$prefix}chat_room` r
        WHERE {$where}
        ORDER BY r.cr_last_message_at DESC
        LIMIT 200";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $rooms[] = $row;
}

// 우측: 선택된 방
$selected_cr_id = isset($_GET['cr_id']) ? (int)$_GET['cr_id'] : 0;
$selected_room = null;
$selected_members = [];
$selected_messages = [];
$selected_msg_total = 0;

// 메시지 영역 필터
$msg_keyword = isset($_GET['msg_keyword']) ? trim($_GET['msg_keyword']) : '';
$msg_type_filter = isset($_GET['msg_type']) ? $_GET['msg_type'] : '';
$msg_date_start = isset($_GET['msg_date_start']) ? $_GET['msg_date_start'] : '';
$msg_date_end = isset($_GET['msg_date_end']) ? $_GET['msg_date_end'] : '';
$per_page = 100;

if ($selected_cr_id) {
    $selected_room = sql_fetch("SELECT * FROM `{$prefix}chat_room` WHERE cr_id = {$selected_cr_id}");
    if (!$selected_room['cr_id']) {
        $selected_room = null;
        $selected_cr_id = 0;
    }
}

if ($selected_room) {
    // 멤버 (참여중) + 메인 캐릭터명
    $chat_character_join = $chat_has_community
        ? "LEFT JOIN `{$chat_character_table}` ch ON ch.mb_id = cm.mb_id AND ch.ch_main = 1"
        : '';
    $sql = "SELECT cm.*, m.mb_name{$chat_character_select}
            FROM `{$prefix}chat_member` cm
            LEFT JOIN `{$g5['member_table']}` m ON cm.mb_id = m.mb_id
            {$chat_character_join}
            WHERE cm.cr_id = {$selected_cr_id} AND cm.cm_left_at IS NULL
            ORDER BY cm.cm_joined_at ASC";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        $selected_members[] = $row;
    }

    // 메시지 검색 조건
    $msg_where = "msg.cr_id = {$selected_cr_id}";
    if ($msg_keyword !== '') {
        $msg_where .= " AND msg.msg_content LIKE '%" . sql_real_escape_string($msg_keyword) . "%'";
    }
    if ($msg_type_filter) {
        $msg_where .= " AND msg.msg_type = '" . sql_real_escape_string($msg_type_filter) . "'";
    }
    if ($msg_date_start) {
        $msg_where .= " AND DATE(msg.msg_created_at) >= '" . sql_real_escape_string($msg_date_start) . "'";
    }
    if ($msg_date_end) {
        $msg_where .= " AND DATE(msg.msg_created_at) <= '" . sql_real_escape_string($msg_date_end) . "'";
    }

    $cnt = sql_fetch("SELECT COUNT(*) as cnt FROM `{$prefix}chat_message` msg WHERE {$msg_where}");
    $selected_msg_total = (int)$cnt['cnt'];

    $chat_character_join = $chat_has_community
        ? "LEFT JOIN `{$chat_character_table}` ch ON ch.mb_id = msg.mb_id AND ch.ch_main = 1"
        : '';
    $sql = "SELECT msg.*, m.mb_name{$chat_character_select}
            FROM `{$prefix}chat_message` msg
            LEFT JOIN `{$g5['member_table']}` m ON msg.mb_id = m.mb_id
            {$chat_character_join}
            WHERE {$msg_where}
            ORDER BY msg.msg_id DESC
            LIMIT {$per_page}";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        $selected_messages[] = $row;
    }
}

$msg_types = [
    'text'   => '텍스트',
    'image'  => '이미지',
    'file'   => '파일',
    'system' => '시스템',
];

// 표시명: mb_id (캐릭터명 우선, 없으면 mb_name)
function chat_user_label($mb_id, $mb_name = '', $ch_name = '') {
    $sub = $ch_name !== '' ? $ch_name : ($mb_name !== '' ? $mb_name : '');
    $html = htmlspecialchars($mb_id);
    if ($sub !== '') {
        $html .= ' <span style="color:var(--gray-500,#64748b);font-size:11px;">(' . htmlspecialchars($sub) . ')</span>';
    }
    return $html;
}

// 좌측 검색폼 hidden 유지용
function chat_qs($overrides = []) {
    $base = [
        'tab' => 'rooms',
        'search_type' => $_GET['search_type'] ?? '',
        'search_keyword' => $_GET['search_keyword'] ?? '',
        'filter_type' => $_GET['filter_type'] ?? '',
        'cr_id' => $_GET['cr_id'] ?? '',
    ];
    $merged = array_merge($base, $overrides);
    $merged = array_filter($merged, function($v) { return $v !== '' && $v !== null; });
    return http_build_query($merged);
}
?>

<style>
.chat-rooms-wrap {
    display: flex;
    gap: 16px;
    margin-top: 8px;
}
.chat-rooms-left {
    width: 320px;
    flex-shrink: 0;
}
.chat-rooms-right {
    flex: 1;
    min-width: 0;
}

.panel {
    background: var(--white, #fff);
    border: 1px solid var(--gray-200, #e2e8f0);
    border-radius: 6px;
    overflow: hidden;
}
.panel-head {
    padding: 10px 14px;
    background: var(--gray-100, #f1f5f9);
    border-bottom: 1px solid var(--gray-200, #e2e8f0);
    font-weight: 600;
    font-size: 13px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
}

/* 좌측 검색폼 */
.left-search {
    padding: 10px 12px;
    border-bottom: 1px solid var(--gray-200, #e2e8f0);
    background: var(--white, #fff);
}
.left-search .row {
    display: flex;
    gap: 4px;
    margin-bottom: 6px;
}
.left-search select,
.left-search input {
    padding: 6px 8px;
    border: 1px solid var(--gray-300, #cbd5e1);
    border-radius: 4px;
    font-size: 12px;
}
.left-search input[type="text"] {
    flex: 1;
    min-width: 0;
}
.left-search .btn-search {
    padding: 6px 12px;
    background: var(--accent-color, #137bea);
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
}

/* 좌측 방 목록 */
.room-list-scroll {
    max-height: 600px;
    overflow-y: auto;
}
.room-item {
    display: block;
    padding: 10px 14px;
    border-bottom: 1px solid var(--gray-200, #e2e8f0);
    text-decoration: none;
    color: var(--gray-800, #1e293b);
    transition: background 0.15s;
}
.room-item:hover {
    background: var(--gray-50, #f8fafc);
}
.room-item.active {
    background: var(--accent-alpha, rgba(19, 123, 234, 0.08));
    border-left: 3px solid var(--accent-color, #137bea);
    padding-left: 11px;
}
.room-item .room-row1 {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 6px;
}
.room-item .room-name {
    font-weight: 600;
    font-size: 13px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    flex: 1;
    min-width: 0;
}
.room-item .room-type-badge {
    flex-shrink: 0;
    padding: 1px 6px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 500;
}
.room-type-badge.private { background: #e3f2fd; color: #1976d2; }
.room-type-badge.group   { background: #f3e5f5; color: #7b1fa2; }
.room-item .room-row2 {
    margin-top: 4px;
    font-size: 11px;
    color: var(--gray-500, #64748b);
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

/* 우측 헤더 */
.detail-head {
    padding: 12px 16px;
    background: var(--gray-100, #f1f5f9);
    border-bottom: 1px solid var(--gray-200, #e2e8f0);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}
.detail-head .room-title {
    font-size: 15px;
    font-weight: 600;
    color: var(--gray-800, #1e293b);
}
.detail-head .room-meta {
    font-size: 12px;
    color: var(--gray-500, #64748b);
    margin-left: 6px;
}
.btn-rename {
    background: none;
    border: none;
    color: var(--gray-500, #64748b);
    cursor: pointer;
    font-size: 12px;
    padding: 2px 6px;
}
.btn-rename:hover { color: var(--accent-color, #137bea); }
.room-name-input {
    padding: 4px 8px;
    border: 1px solid var(--accent-color, #137bea);
    border-radius: 4px;
    font-size: 14px;
    width: 240px;
}
.btn-delete-room {
    padding: 6px 12px;
    background: #fee2e2;
    color: #b91c1c;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
}

/* 멤버 칩 영역 */
.member-strip {
    padding: 10px 16px;
    background: var(--white, #fff);
    border-bottom: 1px solid var(--gray-200, #e2e8f0);
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}
.member-strip .label {
    font-size: 12px;
    color: var(--gray-500, #64748b);
    margin-right: 4px;
}
.member-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px 3px 10px;
    background: var(--gray-100, #f1f5f9);
    border-radius: 12px;
    font-size: 12px;
    color: var(--gray-700, #334155);
}
.member-chip .btn-kick {
    background: none;
    border: none;
    color: #b91c1c;
    cursor: pointer;
    font-size: 11px;
    padding: 0 2px;
    line-height: 1;
}

/* 메시지 검색 바 */
.msg-toolbar {
    padding: 10px 16px;
    background: var(--white, #fff);
    border-bottom: 1px solid var(--gray-200, #e2e8f0);
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    align-items: center;
}
.msg-toolbar input,
.msg-toolbar select {
    padding: 5px 8px;
    border: 1px solid var(--gray-300, #cbd5e1);
    border-radius: 4px;
    font-size: 12px;
}
.msg-toolbar input[type="text"] { width: 160px; }
.msg-toolbar input[type="date"] { width: 130px; }
.msg-toolbar .btn {
    padding: 5px 12px;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
}
.msg-toolbar .btn-apply { background: var(--accent-color, #137bea); color: #fff; }
.msg-toolbar .btn-reset { background: var(--gray-100, #f1f5f9); color: var(--gray-700, #334155); border: 1px solid var(--gray-200, #e2e8f0); }

/* 메시지 표 */
.msg-table-wrap {
    background: var(--white, #fff);
    max-height: 500px;
    overflow-y: auto;
}
.msg-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.msg-table thead th {
    position: sticky;
    top: 0;
    background: var(--gray-50, #f8fafc);
    padding: 8px 10px;
    text-align: left;
    font-weight: 600;
    font-size: 12px;
    color: var(--gray-700, #334155);
    border-bottom: 1px solid var(--gray-200, #e2e8f0);
}
.msg-table tbody td {
    padding: 8px 10px;
    border-bottom: 1px solid var(--gray-200, #e2e8f0);
    vertical-align: top;
}
.msg-table tbody tr:hover { background: var(--gray-50, #f8fafc); }
.msg-type {
    display: inline-block;
    padding: 1px 6px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: 500;
}
.msg-type.text   { background: #e8f5e9; color: #2e7d32; }
.msg-type.image  { background: #e3f2fd; color: #1976d2; }
.msg-type.file   { background: #fff3e0; color: #ef6c00; }
.msg-type.system { background: #f3e5f5; color: #7b1fa2; }
.msg-content-cell {
    max-width: 0;
    word-break: break-word;
    white-space: pre-wrap;
}
.btn-msg-del {
    padding: 3px 8px;
    background: #fee2e2;
    color: #b91c1c;
    border: none;
    border-radius: 3px;
    font-size: 11px;
    cursor: pointer;
}

/* 메시지 푸터 */
.msg-footer {
    padding: 12px 16px;
    background: var(--gray-50, #f8fafc);
    border-top: 1px solid var(--gray-200, #e2e8f0);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}
.msg-footer .left { font-size: 12px; color: var(--gray-600, #475569); }
.msg-footer .right { display: flex; gap: 6px; }
.btn-bulk {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
}
.btn-bulk.danger { background: #fee2e2; color: #b91c1c; }
.btn-bulk.muted  { background: var(--gray-100, #f1f5f9); color: var(--gray-700, #334155); border: 1px solid var(--gray-200, #e2e8f0); }
.btn-load-more {
    width: 100%;
    margin-top: 8px;
    padding: 8px;
    background: var(--gray-100, #f1f5f9);
    border: 1px solid var(--gray-200, #e2e8f0);
    color: var(--gray-700, #334155);
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
}
.btn-load-more:hover { background: var(--gray-200, #e2e8f0); }

/* 빈 상태 */
.empty-state {
    padding: 60px 20px;
    text-align: center;
    color: var(--gray-500, #64748b);
}
.empty-state i {
    font-size: 40px;
    opacity: 0.3;
    margin-bottom: 12px;
}
</style>

<div class="chat-rooms-wrap">
    <!-- 좌: 채팅방 목록 -->
    <div class="chat-rooms-left">
        <div class="panel">
            <div class="panel-head">
                <span><i class="fa fa-comments"></i> 채팅방</span>
                <span style="font-weight:400; font-size:11px; color:var(--gray-500,#64748b);">
                    <?php echo count($rooms); ?>개<?php echo count($rooms) >= 200 ? ' (200개 표시)' : ''; ?>
                </span>
            </div>

            <div class="left-search">
                <form method="get" action="">
                    <input type="hidden" name="tab" value="rooms">
                    <?php if ($selected_cr_id) { ?>
                    <input type="hidden" name="cr_id" value="<?php echo $selected_cr_id; ?>">
                    <?php } ?>
                    <div class="row">
                        <select name="filter_type">
                            <option value="">전체</option>
                            <option value="private" <?php echo $filter_type == 'private' ? 'selected' : ''; ?>>1:1</option>
                            <option value="group" <?php echo $filter_type == 'group' ? 'selected' : ''; ?>>그룹</option>
                        </select>
                        <select name="search_type">
                            <option value="name" <?php echo $search_type == 'name' ? 'selected' : ''; ?>>방 이름</option>
                            <option value="member" <?php echo $search_type == 'member' ? 'selected' : ''; ?>>참여자 ID</option>
                        </select>
                    </div>
                    <div class="row">
                        <input type="text" name="search_keyword" value="<?php echo htmlspecialchars($search_keyword); ?>" placeholder="검색어">
                        <button type="submit" class="btn-search">검색</button>
                    </div>
                </form>
            </div>

            <div class="room-list-scroll">
                <?php if (empty($rooms)) { ?>
                <div style="padding:30px 16px; text-align:center; color:var(--gray-500,#64748b); font-size:13px;">
                    채팅방이 없습니다.
                </div>
                <?php } else { ?>
                    <?php foreach ($rooms as $room) {
                        $is_active = ($selected_cr_id == $room['cr_id']);
                        $row_qs = chat_qs(['cr_id' => $room['cr_id']]);
                        $display_name = $room['cr_name'] ?: ('방 #' . $room['cr_id']);
                    ?>
                    <a href="?<?php echo $row_qs; ?>" class="room-item <?php echo $is_active ? 'active' : ''; ?>">
                        <div class="room-row1">
                            <span class="room-name"><?php echo htmlspecialchars($display_name); ?></span>
                            <span class="room-type-badge <?php echo $room['cr_type']; ?>">
                                <?php echo $room['cr_type'] == 'private' ? '1:1' : '그룹'; ?>
                            </span>
                        </div>
                        <div class="room-row2">
                            <span><i class="fa fa-user"></i> <?php echo number_format($room['member_count']); ?></span>
                            <span><i class="fa fa-envelope"></i> <?php echo number_format($room['message_count']); ?></span>
                            <span style="margin-left:auto;">
                                <?php echo $room['cr_last_message_at'] ? substr($room['cr_last_message_at'], 5, 11) : substr($room['cr_created_at'], 5, 11); ?>
                            </span>
                        </div>
                    </a>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- 우: 선택된 방 상세 -->
    <div class="chat-rooms-right">
        <?php if (!$selected_room) { ?>
        <div class="panel">
            <div class="empty-state">
                <i class="fa fa-comments"></i>
                <p>좌측에서 채팅방을 선택하면<br>멤버와 메시지를 확인할 수 있습니다.</p>
            </div>
        </div>
        <?php } else { ?>
        <div class="panel">
            <!-- 방 헤더 -->
            <div class="detail-head">
                <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                    <span class="room-title" id="roomTitleText" data-cr-id="<?php echo $selected_cr_id; ?>">
                        <?php echo htmlspecialchars($selected_room['cr_name'] ?: ('방 #' . $selected_cr_id)); ?>
                    </span>
                    <button type="button" class="btn-rename" onclick="startRename()" title="이름 변경"><i class="fa fa-pencil"></i></button>
                    <span class="room-meta">
                        <?php echo $selected_room['cr_type'] == 'private' ? '1:1' : '그룹'; ?>
                        · 생성자 <?php echo htmlspecialchars($selected_room['cr_creator_mb_id']); ?>
                        · 메시지 <?php echo number_format($selected_msg_total); ?><?php
                            if ($msg_keyword !== '' || $msg_type_filter || $msg_date_start || $msg_date_end) echo ' (필터)';
                        ?>
                    </span>
                </div>
                <button type="button" class="btn-delete-room" onclick="deleteRoom()">
                    <i class="fa fa-trash"></i> 채팅방 삭제
                </button>
            </div>

            <!-- 멤버 스트립 -->
            <div class="member-strip">
                <span class="label"><i class="fa fa-users"></i> 참여자 <?php echo count($selected_members); ?>명</span>
                <?php if (empty($selected_members)) { ?>
                <span style="font-size:12px; color:var(--gray-500,#64748b);">참여 중인 멤버가 없습니다.</span>
                <?php } else { ?>
                    <?php foreach ($selected_members as $mb) { ?>
                    <span class="member-chip">
                        <?php echo chat_user_label($mb['mb_id'], $mb['mb_name'] ?? '', $mb['ch_name'] ?? ''); ?>
                        <button type="button" class="btn-kick" onclick="kickMember('<?php echo htmlspecialchars($mb['mb_id'], ENT_QUOTES); ?>')" title="강제 퇴장">
                            <i class="fa fa-times"></i>
                        </button>
                    </span>
                    <?php } ?>
                <?php } ?>
            </div>

            <!-- 메시지 검색 바 -->
            <form method="get" action="" class="msg-toolbar">
                <input type="hidden" name="tab" value="rooms">
                <input type="hidden" name="cr_id" value="<?php echo $selected_cr_id; ?>">
                <?php if ($search_keyword) { ?>
                <input type="hidden" name="search_keyword" value="<?php echo htmlspecialchars($search_keyword); ?>">
                <input type="hidden" name="search_type" value="<?php echo htmlspecialchars($search_type); ?>">
                <?php } ?>
                <?php if ($filter_type) { ?>
                <input type="hidden" name="filter_type" value="<?php echo htmlspecialchars($filter_type); ?>">
                <?php } ?>
                <input type="text" name="msg_keyword" value="<?php echo htmlspecialchars($msg_keyword); ?>" placeholder="메시지 내용 검색">
                <select name="msg_type">
                    <option value="">전체 유형</option>
                    <?php foreach ($msg_types as $k => $v) { ?>
                    <option value="<?php echo $k; ?>" <?php echo $msg_type_filter == $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                    <?php } ?>
                </select>
                <input type="date" name="msg_date_start" value="<?php echo htmlspecialchars($msg_date_start); ?>">
                <span style="font-size:12px; color:var(--gray-500,#64748b);">~</span>
                <input type="date" name="msg_date_end" value="<?php echo htmlspecialchars($msg_date_end); ?>">
                <button type="submit" class="btn btn-apply">적용</button>
                <?php if ($msg_keyword !== '' || $msg_type_filter || $msg_date_start || $msg_date_end) { ?>
                <a href="?<?php echo chat_qs(['cr_id' => $selected_cr_id]); ?>" class="btn btn-reset" style="text-decoration:none;">초기화</a>
                <?php } ?>
            </form>

            <!-- 메시지 표 -->
            <form id="msgForm">
                <div class="msg-table-wrap">
                    <table class="msg-table">
                        <thead>
                            <tr>
                                <th style="width:30px;"><input type="checkbox" id="checkAllMsg" onchange="toggleAllMsg(this)"></th>
                                <th style="width:120px;">발신자</th>
                                <th style="width:60px;">유형</th>
                                <th>내용</th>
                                <th style="width:130px;">시간</th>
                                <th style="width:50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="msgTbody">
                            <?php if (empty($selected_messages)) { ?>
                            <tr>
                                <td colspan="6" style="padding:40px; text-align:center; color:var(--gray-500,#64748b);">
                                    <?php echo ($msg_keyword !== '' || $msg_type_filter || $msg_date_start || $msg_date_end) ? '검색 결과가 없습니다.' : '메시지가 없습니다.'; ?>
                                </td>
                            </tr>
                            <?php } else { ?>
                                <?php foreach ($selected_messages as $msg) { ?>
                                <tr>
                                    <td><input type="checkbox" class="msg-cb" value="<?php echo $msg['msg_id']; ?>"></td>
                                    <td><?php echo chat_user_label($msg['mb_id'], $msg['mb_name'] ?? '', $msg['ch_name'] ?? ''); ?></td>
                                    <td><span class="msg-type <?php echo $msg['msg_type']; ?>"><?php echo $msg_types[$msg['msg_type']] ?? $msg['msg_type']; ?></span></td>
                                    <td class="msg-content-cell"><?php echo nl2br(htmlspecialchars($msg['msg_content'])); ?></td>
                                    <td style="font-size:11px; color:var(--gray-500,#64748b);"><?php echo substr($msg['msg_created_at'], 0, 16); ?></td>
                                    <td><button type="button" class="btn-msg-del" onclick="deleteOneMessage(<?php echo $msg['msg_id']; ?>)"><i class="fa fa-trash"></i></button></td>
                                </tr>
                                <?php } ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="msg-footer">
                    <div class="left">
                        <span id="loadedCount"><?php echo count($selected_messages); ?></span> / <?php echo number_format($selected_msg_total); ?>건
                        <span id="selectedCount" style="margin-left:10px;"></span>
                    </div>
                    <div class="right">
                        <button type="button" class="btn-bulk danger" onclick="deleteSelected()">선택 삭제</button>
                        <button type="button" class="btn-bulk muted" onclick="showDeleteOldModal()">기간별 삭제</button>
                    </div>
                </div>

                <?php if ($selected_msg_total > $per_page && count($selected_messages) > 0) { ?>
                <div style="padding: 0 16px 12px;">
                    <button type="button" class="btn-load-more" id="loadMoreBtn"
                        data-offset="<?php echo $per_page; ?>"
                        data-total="<?php echo $selected_msg_total; ?>"
                        onclick="loadMoreMessages()">
                        <i class="fa fa-arrow-down"></i> 더보기 (<?php echo number_format($selected_msg_total - count($selected_messages)); ?>건 남음)
                    </button>
                </div>
                <?php } ?>
            </form>
        </div>
        <?php } ?>
    </div>
</div>

<!-- 기간별 삭제 모달 -->
<div id="deleteOldModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
    <div style="background:#fff; border-radius:8px; padding:24px; max-width:400px; width:90%;">
        <h3 style="margin:0 0 16px;">오래된 메시지 삭제</h3>
        <p style="margin-bottom:12px; color:var(--gray-600,#475569); font-size:13px;">
            이 채팅방에서 선택한 기간 이전 메시지를 삭제합니다. 되돌릴 수 없습니다.
        </p>
        <select id="deleteDays" style="width:100%; padding:10px; border:1px solid var(--gray-300,#cbd5e1); border-radius:6px; margin-bottom:16px;">
            <option value="">기간 선택</option>
            <option value="30">30일 이전</option>
            <option value="60">60일 이전</option>
            <option value="90">90일 이전</option>
            <option value="180">180일 이전</option>
            <option value="365">1년 이전</option>
        </select>
        <div style="display:flex; gap:8px; justify-content:flex-end;">
            <button type="button" onclick="hideDeleteOldModal()" style="padding:10px 20px; background:var(--gray-100,#f1f5f9); border:1px solid var(--gray-200,#e2e8f0); border-radius:6px; cursor:pointer;">취소</button>
            <button type="button" onclick="deleteOldInRoom()" style="padding:10px 20px; background:#b91c1c; color:#fff; border:none; border-radius:6px; cursor:pointer;">삭제</button>
        </div>
    </div>
</div>

<script>
var CR_ID = <?php echo (int)$selected_cr_id; ?>;
var AJAX_URL = '<?php echo G5_ADMIN_URL; ?>/chat/ajax.php';

var msgFilter = {
    msg_keyword: <?php echo json_encode($msg_keyword); ?>,
    msg_type:    <?php echo json_encode($msg_type_filter); ?>,
    msg_date_start: <?php echo json_encode($msg_date_start); ?>,
    msg_date_end:   <?php echo json_encode($msg_date_end); ?>
};
var MSG_TYPES = <?php echo json_encode($msg_types, JSON_UNESCAPED_UNICODE); ?>;

function escapeHtml(s) {
    if (s == null) return '';
    var d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

/* === 방 이름 인라인 편집 === */
function startRename() {
    var titleEl = document.getElementById('roomTitleText');
    if (!titleEl || titleEl.dataset.editing === '1') return;
    titleEl.dataset.editing = '1';

    var current = titleEl.textContent.trim();
    if (current.indexOf('방 #') === 0) current = '';

    var input = document.createElement('input');
    input.type = 'text';
    input.className = 'room-name-input';
    input.value = current;
    input.maxLength = 100;
    input.placeholder = '채팅방 이름';

    titleEl.style.display = 'none';
    titleEl.parentNode.insertBefore(input, titleEl);
    input.focus();
    input.select();

    function finish(save) {
        if (!save) {
            input.remove();
            titleEl.style.display = '';
            titleEl.dataset.editing = '0';
            return;
        }
        var newName = input.value.trim();
        fetch(AJAX_URL, {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: 'action=update_room_name&cr_id=' + CR_ID + '&cr_name=' + encodeURIComponent(newName)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                titleEl.textContent = newName || ('방 #' + CR_ID);
            } else {
                alert('오류: ' + data.error);
            }
            input.remove();
            titleEl.style.display = '';
            titleEl.dataset.editing = '0';
        })
        .catch(err => {
            alert('오류: ' + err.message);
            input.remove();
            titleEl.style.display = '';
            titleEl.dataset.editing = '0';
        });
    }

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); finish(true); }
        if (e.key === 'Escape') finish(false);
    });
    input.addEventListener('blur', function() { finish(true); });
}

/* === 강퇴 === */
function kickMember(mb_id) {
    if (!confirm(mb_id + ' 회원을 이 채팅방에서 강제 퇴장시키겠습니까?')) return;
    fetch(AJAX_URL, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'action=kick_member&cr_id=' + CR_ID + '&mb_id=' + encodeURIComponent(mb_id)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('오류: ' + data.error);
        }
    });
}

/* === 채팅방 삭제 === */
function deleteRoom() {
    if (!confirm('이 채팅방을 삭제하시겠습니까?\n모든 메시지와 참여자 기록이 함께 삭제됩니다.')) return;
    fetch(AJAX_URL, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'action=delete_room&cr_id=' + CR_ID
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.href = '?tab=rooms';
        } else {
            alert('오류: ' + data.error);
        }
    });
}

/* === 메시지 선택 === */
function toggleAllMsg(cb) {
    document.querySelectorAll('.msg-cb').forEach(function(c){ c.checked = cb.checked; });
    updateSelectedCount();
}
function updateSelectedCount() {
    var n = document.querySelectorAll('.msg-cb:checked').length;
    var el = document.getElementById('selectedCount');
    if (el) el.textContent = n > 0 ? '(' + n + '개 선택됨)' : '';
}
document.addEventListener('change', function(e) {
    if (e.target.classList && e.target.classList.contains('msg-cb')) updateSelectedCount();
});

function deleteOneMessage(msg_id) {
    if (!confirm('이 메시지를 삭제하시겠습니까?')) return;
    fetch(AJAX_URL, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'action=delete_messages&msg_ids[]=' + msg_id
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert('오류: ' + data.error);
    });
}

function deleteSelected() {
    var checked = document.querySelectorAll('.msg-cb:checked');
    if (!checked.length) { alert('삭제할 메시지를 선택해주세요.'); return; }
    if (!confirm(checked.length + '개의 메시지를 삭제하시겠습니까?')) return;
    var ids = [];
    checked.forEach(function(c){ ids.push(c.value); });
    fetch(AJAX_URL, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'action=delete_messages&msg_ids=' + JSON.stringify(ids)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert('오류: ' + data.error);
    });
}

/* === 기간 삭제 (현재 방 한정) === */
function showDeleteOldModal() {
    document.getElementById('deleteOldModal').style.display = 'flex';
}
function hideDeleteOldModal() {
    document.getElementById('deleteOldModal').style.display = 'none';
}
function deleteOldInRoom() {
    var days = document.getElementById('deleteDays').value;
    if (!days) { alert('기간을 선택해주세요.'); return; }
    if (!confirm(days + '일 이전 메시지를 이 채팅방에서 삭제하시겠습니까?\n되돌릴 수 없습니다.')) return;
    fetch(AJAX_URL, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'action=delete_old_messages&days=' + days + '&cr_id=' + CR_ID
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { alert(data.message); location.reload(); }
        else alert('오류: ' + data.error);
    });
}

/* === 더보기 페이징 === */
function loadMoreMessages() {
    var btn = document.getElementById('loadMoreBtn');
    if (!btn) return;
    var offset = parseInt(btn.dataset.offset, 10);
    var total = parseInt(btn.dataset.total, 10);

    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> 로딩 중...';

    var params = new URLSearchParams({
        action: 'load_room_messages',
        cr_id: CR_ID,
        offset: offset,
        limit: 100,
        msg_keyword: msgFilter.msg_keyword || '',
        msg_type:    msgFilter.msg_type || '',
        msg_date_start: msgFilter.msg_date_start || '',
        msg_date_end:   msgFilter.msg_date_end || ''
    });

    fetch(AJAX_URL + '?' + params.toString())
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            alert('오류: ' + (data.error || '불러오기 실패'));
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-arrow-down"></i> 다시 시도';
            return;
        }
        var tbody = document.getElementById('msgTbody');
        data.messages.forEach(function(msg) {
            var tr = document.createElement('tr');
            var typeLabel = MSG_TYPES[msg.msg_type] || msg.msg_type;
            var sub = msg.ch_name || msg.mb_name || '';
            var senderHtml = escapeHtml(msg.mb_id);
            if (sub) senderHtml += ' <span style="color:var(--gray-500,#64748b);font-size:11px;">(' + escapeHtml(sub) + ')</span>';
            tr.innerHTML =
                '<td><input type="checkbox" class="msg-cb" value="' + msg.msg_id + '"></td>' +
                '<td>' + senderHtml + '</td>' +
                '<td><span class="msg-type ' + msg.msg_type + '">' + escapeHtml(typeLabel) + '</span></td>' +
                '<td class="msg-content-cell">' + escapeHtml(msg.msg_content).replace(/\n/g, '<br>') + '</td>' +
                '<td style="font-size:11px; color:var(--gray-500,#64748b);">' + (msg.msg_created_at || '').substring(0,16) + '</td>' +
                '<td><button type="button" class="btn-msg-del" onclick="deleteOneMessage(' + msg.msg_id + ')"><i class="fa fa-trash"></i></button></td>';
            tbody.appendChild(tr);
        });

        var newOffset = offset + data.messages.length;
        var loaded = parseInt(document.getElementById('loadedCount').textContent, 10) + data.messages.length;
        document.getElementById('loadedCount').textContent = loaded;
        btn.dataset.offset = newOffset;

        var remaining = total - loaded;
        if (remaining > 0 && data.messages.length > 0) {
            btn.innerHTML = '<i class="fa fa-arrow-down"></i> 더보기 (' + remaining.toLocaleString() + '건 남음)';
            btn.disabled = false;
        } else {
            btn.parentElement.style.display = 'none';
        }
    })
    .catch(err => {
        alert('오류: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-arrow-down"></i> 다시 시도';
    });
}

/* 모달 외부 클릭 시 닫기 */
document.getElementById('deleteOldModal').addEventListener('click', function(e) {
    if (e.target === this) hideDeleteOldModal();
});
</script>
