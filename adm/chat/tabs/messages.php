<?php
if (!defined('_GNUBOARD_')) exit;

// 채팅 라이브러리 로드 (테이블명 정의)
include_once(G5_LIB_PATH . '/chat.lib.php');

$prefix = G5_TABLE_PREFIX;

// 커뮤니티 확장팩이 설치된 경우에만 캐릭터 정보를 조회한다.
$chat_has_community = function_exists('is_community_installed') && is_community_installed();
$chat_character_table = G5_TABLE_PREFIX . 'community_character';
$chat_character_select = $chat_has_community ? ', ch.ch_name' : ', NULL AS ch_name';
$chat_character_join = $chat_has_community
    ? "LEFT JOIN `{$chat_character_table}` ch ON ch.mb_id = m.mb_id AND ch.ch_main = 1"
    : '';

// 검색 조건
$search_keyword = isset($_GET['search_keyword']) ? trim($_GET['search_keyword']) : '';
$search_mb_id = isset($_GET['search_mb_id']) ? trim($_GET['search_mb_id']) : '';
$search_cr_id = isset($_GET['search_cr_id']) ? (int)$_GET['search_cr_id'] : 0;
$search_date_start = isset($_GET['search_date_start']) ? $_GET['search_date_start'] : '';
$search_date_end = isset($_GET['search_date_end']) ? $_GET['search_date_end'] : '';

// 페이징
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 30;
$offset = ($page - 1) * $per_page;

// WHERE 조건 구성
$where = "1=1";

if ($search_keyword) {
    $where .= " AND m.msg_content LIKE '%" . sql_real_escape_string($search_keyword) . "%'";
}
if ($search_mb_id) {
    $where .= " AND m.mb_id LIKE '%" . sql_real_escape_string($search_mb_id) . "%'";
}
if ($search_cr_id) {
    $where .= " AND m.cr_id = {$search_cr_id}";
}
if ($search_date_start) {
    $where .= " AND DATE(m.msg_created_at) >= '" . sql_real_escape_string($search_date_start) . "'";
}
if ($search_date_end) {
    $where .= " AND DATE(m.msg_created_at) <= '" . sql_real_escape_string($search_date_end) . "'";
}

// 총 개수
$total_count = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['chat_message_table']} m WHERE {$where}")['cnt'];
$total_page = ceil($total_count / $per_page);

// 메시지 목록 (메인 캐릭터명 포함)
$messages = [];
$sql = "SELECT m.*, mb.mb_name, r.cr_name, r.cr_type{$chat_character_select}
        FROM {$g5['chat_message_table']} m
        LEFT JOIN {$g5['member_table']} mb ON m.mb_id = mb.mb_id
        {$chat_character_join}
        LEFT JOIN {$g5['chat_room_table']} r ON m.cr_id = r.cr_id
        WHERE {$where}
        ORDER BY m.msg_id DESC
        LIMIT {$offset}, {$per_page}";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $messages[] = $row;
}

// 쿼리스트링 유지
$qstr = http_build_query([
    'tab' => 'messages',
    'search_keyword' => $search_keyword,
    'search_mb_id' => $search_mb_id,
    'search_cr_id' => $search_cr_id,
    'search_date_start' => $search_date_start,
    'search_date_end' => $search_date_end
]);
?>

<style>
.chat-messages-header {
    margin-bottom: 20px;
}
.chat-messages-search {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
}
.chat-messages-search .search-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.chat-messages-search label {
    font-size: 12px;
    color: var(--text-muted, #666);
}
.chat-messages-search input,
.chat-messages-search select {
    padding: 8px 12px;
    border: 1px solid var(--border-color, #dee2e6);
    border-radius: 6px;
    font-size: 14px;
}
.chat-messages-search input[type="text"] {
    width: 150px;
}
.chat-messages-search input[type="date"] {
    width: 140px;
}
.chat-messages-search .btn-search {
    padding: 8px 16px;
    background: var(--primary-color, #333);
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    height: 38px;
}

.bulk-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 12px;
}
.bulk-actions .selected-count {
    font-size: 14px;
    color: var(--text-muted, #666);
}
.bulk-actions .btn-bulk {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
}
.bulk-actions .btn-delete-selected {
    background: #ffebee;
    color: #c62828;
}
.bulk-actions .btn-delete-old {
    background: var(--bg-secondary, #f8f9fa);
    color: var(--text-color, #333);
    border: 1px solid var(--border-color, #dee2e6);
}

.chat-messages-table {
    width: 100%;
    border-collapse: collapse;
}
.chat-messages-table th,
.chat-messages-table td {
    padding: 10px 12px;
    text-align: left;
    border-bottom: 1px solid var(--border-color, #dee2e6);
}
.chat-messages-table th {
    background: var(--bg-secondary, #f8f9fa);
    font-weight: 600;
    font-size: 13px;
}
.chat-messages-table td {
    font-size: 14px;
}
.chat-messages-table tr:hover {
    background: var(--bg-hover, #f8f9fa);
}
.chat-messages-table .msg-content {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.chat-messages-table .msg-type {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 11px;
}
.chat-messages-table .msg-type.text { background: #e8f5e9; color: #2e7d32; }
.chat-messages-table .msg-type.image { background: #e3f2fd; color: #1976d2; }
.chat-messages-table .msg-type.file { background: #fff3e0; color: #ef6c00; }
.chat-messages-table .msg-type.system { background: #f3e5f5; color: #7b1fa2; }
.chat-messages-table .btn-delete {
    padding: 4px 8px;
    background: #ffebee;
    color: #c62828;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 4px;
    margin-top: 20px;
}
.pagination a,
.pagination span {
    display: inline-block;
    padding: 8px 12px;
    border: 1px solid var(--border-color, #dee2e6);
    border-radius: 4px;
    text-decoration: none;
    color: var(--text-color, #333);
}
.pagination a:hover {
    background: var(--bg-hover, #f8f9fa);
}
.pagination .current {
    background: var(--primary-color, #333);
    color: #fff;
    border-color: var(--primary-color, #333);
}
.no-data {
    text-align: center;
    padding: 40px;
    color: var(--text-muted, #666);
}

/* 일괄 삭제 모달 */
.delete-old-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}
.delete-old-modal.show {
    display: flex;
}
.delete-old-content {
    background: #fff;
    border-radius: 8px;
    padding: 24px;
    max-width: 400px;
    width: 90%;
}
.delete-old-content h3 {
    margin: 0 0 16px 0;
    font-size: 18px;
}
.delete-old-content p {
    margin-bottom: 16px;
    color: var(--text-muted, #666);
}
.delete-old-content select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--border-color, #dee2e6);
    border-radius: 6px;
    margin-bottom: 16px;
}
.delete-old-content .btn-row {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}
.delete-old-content .btn-cancel {
    padding: 10px 20px;
    background: var(--bg-secondary, #f8f9fa);
    border: 1px solid var(--border-color, #dee2e6);
    border-radius: 6px;
    cursor: pointer;
}
.delete-old-content .btn-confirm {
    padding: 10px 20px;
    background: #c62828;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
</style>

<div style="margin-bottom:12px; padding:10px 14px; background:#fef9c3; border-left:3px solid #eab308; border-radius:4px; font-size:12px; color:#854d0e;">
    <i class="fa fa-info-circle"></i>
    여기서는 모든 채팅방을 가로질러 메시지를 검색합니다. 특정 방의 멤버·메시지를 함께 보려면 <strong><a href="?tab=rooms" style="color:#854d0e; text-decoration:underline;">채팅방 관리</a></strong> 탭을 사용하세요.
</div>

<!-- 검색 폼 -->
<div class="chat-messages-header">
    <form class="chat-messages-search" method="get" action="">
        <input type="hidden" name="tab" value="messages">
        <div class="search-group">
            <label>키워드</label>
            <input type="text" name="search_keyword" value="<?php echo htmlspecialchars($search_keyword); ?>" placeholder="메시지 내용">
        </div>
        <div class="search-group">
            <label>회원 ID</label>
            <input type="text" name="search_mb_id" value="<?php echo htmlspecialchars($search_mb_id); ?>" placeholder="발신자 ID">
        </div>
        <div class="search-group">
            <label>채팅방 ID</label>
            <input type="number" name="search_cr_id" value="<?php echo $search_cr_id ?: ''; ?>" placeholder="방 번호">
        </div>
        <div class="search-group">
            <label>시작일</label>
            <input type="date" name="search_date_start" value="<?php echo $search_date_start; ?>">
        </div>
        <div class="search-group">
            <label>종료일</label>
            <input type="date" name="search_date_end" value="<?php echo $search_date_end; ?>">
        </div>
        <button type="submit" class="btn-search">검색</button>
    </form>
</div>

<!-- 일괄 작업 -->
<div class="bulk-actions">
    <div>
        <span class="selected-count">총 <?php echo number_format($total_count); ?>개</span>
        <span id="selectedCount" style="margin-left:10px;"></span>
    </div>
    <div>
        <button type="button" class="btn-bulk btn-delete-selected" onclick="deleteSelectedMessages()">선택 삭제</button>
        <button type="button" class="btn-bulk btn-delete-old" onclick="showDeleteOldModal()">기간별 삭제</button>
    </div>
</div>

<!-- 메시지 목록 -->
<?php if (empty($messages)) { ?>
<div class="no-data">메시지가 없습니다.</div>
<?php } else { ?>
<table class="chat-messages-table">
    <thead>
        <tr>
            <th><input type="checkbox" id="checkAll" onchange="toggleAllCheckboxes()"></th>
            <th>ID</th>
            <th>채팅방</th>
            <th>발신자</th>
            <th>유형</th>
            <th>내용</th>
            <th>작성일</th>
            <th>삭제</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($messages as $msg) { ?>
        <tr>
            <td><input type="checkbox" class="msg-checkbox" value="<?php echo $msg['msg_id']; ?>"></td>
            <td><?php echo $msg['msg_id']; ?></td>
            <td>
                <a href="?tab=rooms&cr_id=<?php echo $msg['cr_id']; ?>" style="color:inherit;" title="이 채팅방으로 이동">
                    #<?php echo $msg['cr_id']; ?>
                    <?php echo $msg['cr_name'] ? ' ' . htmlspecialchars(mb_substr($msg['cr_name'], 0, 10, 'UTF-8')) : ''; ?>
                </a>
            </td>
            <td>
                <?php echo htmlspecialchars($msg['mb_id']); ?>
                <?php
                    $sub = $msg['ch_name'] ?: ($msg['mb_name'] ?: '');
                    if ($sub !== '') echo ' <span style="color:var(--gray-500,#64748b);font-size:11px;">(' . htmlspecialchars($sub) . ')</span>';
                ?>
            </td>
            <td>
                <span class="msg-type <?php echo $msg['msg_type']; ?>">
                    <?php
                    switch ($msg['msg_type']) {
                        case 'text': echo '텍스트'; break;
                        case 'image': echo '이미지'; break;
                        case 'file': echo '파일'; break;
                        case 'system': echo '시스템'; break;
                        default: echo $msg['msg_type'];
                    }
                    ?>
                </span>
            </td>
            <td class="msg-content" title="<?php echo htmlspecialchars($msg['msg_content']); ?>">
                <?php echo htmlspecialchars(mb_substr($msg['msg_content'], 0, 50, 'UTF-8')); ?>
            </td>
            <td><?php echo substr($msg['msg_created_at'], 0, 16); ?></td>
            <td>
                <button type="button" class="btn-delete" onclick="deleteMessage(<?php echo $msg['msg_id']; ?>)">삭제</button>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<!-- 페이징 -->
<?php if ($total_page > 1) { ?>
<div class="pagination">
    <?php if ($page > 1) { ?>
    <a href="?<?php echo $qstr; ?>&page=1">&laquo;</a>
    <a href="?<?php echo $qstr; ?>&page=<?php echo $page - 1; ?>">&lt;</a>
    <?php } ?>

    <?php
    $start_page = max(1, $page - 2);
    $end_page = min($total_page, $page + 2);
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $page) {
            echo '<span class="current">' . $i . '</span>';
        } else {
            echo '<a href="?' . $qstr . '&page=' . $i . '">' . $i . '</a>';
        }
    }
    ?>

    <?php if ($page < $total_page) { ?>
    <a href="?<?php echo $qstr; ?>&page=<?php echo $page + 1; ?>">&gt;</a>
    <a href="?<?php echo $qstr; ?>&page=<?php echo $total_page; ?>">&raquo;</a>
    <?php } ?>
</div>
<?php } ?>
<?php } ?>

<!-- 기간별 삭제 모달 -->
<div class="delete-old-modal" id="deleteOldModal">
    <div class="delete-old-content">
        <h3>오래된 메시지 삭제</h3>
        <p>선택한 기간 이전의 모든 메시지를 삭제합니다. 이 작업은 되돌릴 수 없습니다.</p>
        <select id="deleteDays">
            <option value="">기간 선택</option>
            <option value="30">30일 이전</option>
            <option value="60">60일 이전</option>
            <option value="90">90일 이전</option>
            <option value="180">180일 이전</option>
            <option value="365">1년 이전</option>
        </select>
        <div class="btn-row">
            <button type="button" class="btn-cancel" onclick="hideDeleteOldModal()">취소</button>
            <button type="button" class="btn-confirm" onclick="deleteOldMessages()">삭제</button>
        </div>
    </div>
</div>

<script>
function toggleAllCheckboxes() {
    var checkAll = document.getElementById('checkAll');
    var checkboxes = document.querySelectorAll('.msg-checkbox');
    checkboxes.forEach(function(cb) {
        cb.checked = checkAll.checked;
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    var checked = document.querySelectorAll('.msg-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = checked > 0 ? '(' + checked + '개 선택됨)' : '';
}

// 체크박스 변경 시
document.querySelectorAll('.msg-checkbox').forEach(function(cb) {
    cb.addEventListener('change', updateSelectedCount);
});

function deleteMessage(msg_id) {
    if (!confirm('이 메시지를 삭제하시겠습니까?')) return;

    fetch('<?php echo G5_ADMIN_URL; ?>/chat/ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=delete_messages&msg_ids[]=' + msg_id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('오류: ' + data.error);
        }
    });
}

function deleteSelectedMessages() {
    var checked = document.querySelectorAll('.msg-checkbox:checked');
    if (checked.length === 0) {
        alert('삭제할 메시지를 선택해주세요.');
        return;
    }

    if (!confirm(checked.length + '개의 메시지를 삭제하시겠습니까?')) return;

    var ids = [];
    checked.forEach(function(cb) {
        ids.push(cb.value);
    });

    fetch('<?php echo G5_ADMIN_URL; ?>/chat/ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=delete_messages&msg_ids=' + JSON.stringify(ids)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('오류: ' + data.error);
        }
    });
}

function showDeleteOldModal() {
    document.getElementById('deleteOldModal').classList.add('show');
}

function hideDeleteOldModal() {
    document.getElementById('deleteOldModal').classList.remove('show');
}

function deleteOldMessages() {
    var days = document.getElementById('deleteDays').value;
    if (!days) {
        alert('삭제할 기간을 선택해주세요.');
        return;
    }

    if (!confirm(days + '일 이전의 모든 메시지를 삭제하시겠습니까?\n이 작업은 되돌릴 수 없습니다.')) return;

    fetch('<?php echo G5_ADMIN_URL; ?>/chat/ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=delete_old_messages&days=' + days
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            hideDeleteOldModal();
            location.reload();
        } else {
            alert('오류: ' + data.error);
        }
    });
}

// 모달 외부 클릭 시 닫기
document.getElementById('deleteOldModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideDeleteOldModal();
    }
});
</script>
