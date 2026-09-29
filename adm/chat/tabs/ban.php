<?php
if (!defined('_GNUBOARD_')) exit;

$prefix = G5_TABLE_PREFIX;

// 페이징
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// 총 개수
$row = sql_fetch("SELECT COUNT(*) as cnt FROM `{$prefix}chat_ban`");
$total_count = $row['cnt'] ?? 0;
$total_page = ceil($total_count / $per_page);

// 금지 목록
$bans = [];
$sql = "SELECT b.*, m.mb_name, m.mb_nick, adm.mb_name as admin_name
        FROM `{$prefix}chat_ban` b
        LEFT JOIN `{$g5['member_table']}` m ON b.mb_id = m.mb_id
        LEFT JOIN `{$g5['member_table']}` adm ON b.cb_admin_id = adm.mb_id
        ORDER BY b.cb_created_at DESC
        LIMIT {$offset}, {$per_page}";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $bans[] = $row;
}

// 쿼리스트링
$qstr = 'tab=ban';
?>

<style>
.ban-add-form {
    background: var(--bg-secondary, #f8f9fa);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 24px;
}
.ban-add-form h3 {
    font-size: 16px;
    margin: 0 0 16px 0;
}
.ban-add-form .form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: flex-end;
}
.ban-add-form .form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.ban-add-form label {
    font-size: 13px;
    color: var(--text-muted, #666);
}
.ban-add-form input,
.ban-add-form select {
    padding: 10px 12px;
    border: 1px solid var(--border-color, #dee2e6);
    border-radius: 6px;
    font-size: 14px;
}
.ban-add-form input[type="text"] {
    width: 150px;
}
.ban-add-form .btn-add {
    padding: 10px 20px;
    background: var(--primary-color, #333);
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    height: 42px;
}
.ban-add-form .btn-add:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* 회원 검색 자동완성 */
.member-search-wrap {
    position: relative;
}
.member-autocomplete {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid var(--border-color, #dee2e6);
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    max-height: 200px;
    overflow-y: auto;
    z-index: 100;
    display: none;
}
.member-autocomplete.show {
    display: block;
}
.member-autocomplete-item {
    padding: 10px 12px;
    cursor: pointer;
    border-bottom: 1px solid var(--border-color, #dee2e6);
}
.member-autocomplete-item:last-child {
    border-bottom: none;
}
.member-autocomplete-item:hover {
    background: var(--bg-hover, #f8f9fa);
}
.member-autocomplete-item .mb-id {
    font-weight: 600;
}
.member-autocomplete-item .mb-name {
    font-size: 12px;
    color: var(--text-muted, #666);
    margin-left: 8px;
}

.ban-list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}
.ban-list-header h3 {
    font-size: 16px;
    margin: 0;
}

.ban-table {
    width: 100%;
    border-collapse: collapse;
}
.ban-table th,
.ban-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid var(--border-color, #dee2e6);
}
.ban-table th {
    background: var(--bg-secondary, #f8f9fa);
    font-weight: 600;
    font-size: 13px;
}
.ban-table td {
    font-size: 14px;
}
.ban-table tr:hover {
    background: var(--bg-hover, #f8f9fa);
}
.ban-table .ban-status {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
}
.ban-table .ban-status.active {
    background: #ffebee;
    color: #c62828;
}
.ban-table .ban-status.expired {
    background: #e8f5e9;
    color: #2e7d32;
}
.ban-table .btn-unban {
    padding: 6px 12px;
    background: #e8f5e9;
    color: #2e7d32;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
}
.ban-table .btn-unban:hover {
    background: #c8e6c9;
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
</style>

<!-- 금지 추가 폼 -->
<div class="ban-add-form">
    <h3>채팅 금지 추가</h3>
    <form id="banAddForm" onsubmit="addBan(event)">
        <div class="form-row">
            <div class="form-group member-search-wrap">
                <label>회원 ID</label>
                <input type="text" id="banMbId" name="mb_id" placeholder="회원 ID 검색" autocomplete="off" required>
                <div class="member-autocomplete" id="memberAutocomplete"></div>
            </div>
            <div class="form-group">
                <label>사유</label>
                <input type="text" name="reason" placeholder="금지 사유 (선택)" style="width:200px;">
            </div>
            <div class="form-group">
                <label>기간</label>
                <select name="duration">
                    <option value="0">영구</option>
                    <option value="1">1일</option>
                    <option value="7">7일</option>
                    <option value="30">30일</option>
                    <option value="90">90일</option>
                    <option value="365">1년</option>
                </select>
            </div>
            <button type="submit" class="btn-add" id="btnAddBan">금지 추가</button>
        </div>
    </form>
</div>

<!-- 금지 목록 -->
<div class="ban-list-header">
    <h3>금지 목록</h3>
    <span>총 <?php echo number_format($total_count); ?>명</span>
</div>

<?php if (empty($bans)) { ?>
<div class="no-data">채팅 금지 회원이 없습니다.</div>
<?php } else { ?>
<table class="ban-table">
    <thead>
        <tr>
            <th>회원</th>
            <th>사유</th>
            <th>시작일</th>
            <th>종료일</th>
            <th>상태</th>
            <th>처리자</th>
            <th>해제</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $now = date('Y-m-d H:i:s');
        foreach ($bans as $ban) {
            $is_active = ($ban['cb_end'] === null || $ban['cb_end'] > $now);
        ?>
        <tr>
            <td>
                <?php echo htmlspecialchars($ban['mb_nick'] ?: $ban['mb_name'] ?: $ban['mb_id']); ?>
                <br><small style="color:#666;"><?php echo $ban['mb_id']; ?></small>
            </td>
            <td><?php echo htmlspecialchars($ban['cb_reason'] ?: '-'); ?></td>
            <td><?php echo substr($ban['cb_start'], 0, 10); ?></td>
            <td><?php echo $ban['cb_end'] ? substr($ban['cb_end'], 0, 10) : '영구'; ?></td>
            <td>
                <span class="ban-status <?php echo $is_active ? 'active' : 'expired'; ?>">
                    <?php echo $is_active ? '금지 중' : '만료됨'; ?>
                </span>
            </td>
            <td><?php echo htmlspecialchars($ban['admin_name'] ?: $ban['cb_admin_id']); ?></td>
            <td>
                <?php if ($is_active) { ?>
                <button type="button" class="btn-unban" onclick="removeBan(<?php echo $ban['cb_id']; ?>)">해제</button>
                <?php } else { ?>
                -
                <?php } ?>
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

<script>
var searchTimeout = null;

// 회원 검색 자동완성
document.getElementById('banMbId').addEventListener('input', function() {
    var keyword = this.value.trim();
    var autocomplete = document.getElementById('memberAutocomplete');

    if (searchTimeout) clearTimeout(searchTimeout);

    if (keyword.length < 2) {
        autocomplete.classList.remove('show');
        return;
    }

    searchTimeout = setTimeout(function() {
        fetch('<?php echo G5_BBS_URL; ?>/ajax.chat.php?action=search_members&keyword=' + encodeURIComponent(keyword))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.members.length > 0) {
                var html = '';
                data.members.forEach(function(m) {
                    html += '<div class="member-autocomplete-item" onclick="selectMember(\'' + m.mb_id + '\')">';
                    html += '<span class="mb-id">' + m.mb_id + '</span>';
                    html += '<span class="mb-name">' + (m.mb_nick || m.mb_name || '') + '</span>';
                    html += '</div>';
                });
                autocomplete.innerHTML = html;
                autocomplete.classList.add('show');
            } else {
                autocomplete.classList.remove('show');
            }
        });
    }, 300);
});

function selectMember(mb_id) {
    document.getElementById('banMbId').value = mb_id;
    document.getElementById('memberAutocomplete').classList.remove('show');
}

// 외부 클릭 시 자동완성 닫기
document.addEventListener('click', function(e) {
    if (!e.target.closest('.member-search-wrap')) {
        document.getElementById('memberAutocomplete').classList.remove('show');
    }
});

// 금지 추가
function addBan(e) {
    e.preventDefault();

    var form = document.getElementById('banAddForm');
    var btn = document.getElementById('btnAddBan');
    var formData = new FormData(form);
    formData.append('action', 'add_ban');

    btn.disabled = true;
    btn.textContent = '처리 중...';

    fetch('<?php echo G5_ADMIN_URL; ?>/chat/ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('오류: ' + data.error);
            btn.disabled = false;
            btn.textContent = '금지 추가';
        }
    })
    .catch(error => {
        alert('오류: ' + error.message);
        btn.disabled = false;
        btn.textContent = '금지 추가';
    });
}

// 금지 해제
function removeBan(cb_id) {
    if (!confirm('이 회원의 채팅 금지를 해제하시겠습니까?')) return;

    fetch('<?php echo G5_ADMIN_URL; ?>/chat/ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=remove_ban&cb_id=' + cb_id
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
</script>
