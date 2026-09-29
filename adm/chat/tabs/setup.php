<?php
if (!defined('_GNUBOARD_')) exit;

$prefix = G5_TABLE_PREFIX;

// 기본값 정의
$default_configs = [
    'chat_enabled' => '1',
    'chat_cooldown' => '3',
    'chat_max_members' => '50',
    'chat_max_length' => '2000',
    'chat_polling_active' => '5000',
    'chat_polling_inactive' => '30000',
    'chat_create_level' => '1',
];

// 현재 설정값 가져오기
$configs = [];
$result = sql_query("SELECT cf_key, cf_value FROM `{$prefix}chat_config`");
if ($result) {
    while ($row = sql_fetch_array($result)) {
        $configs[$row['cf_key']] = $row['cf_value'];
    }
}

// 누락된 설정값 자동 추가 및 기본값 적용
foreach ($default_configs as $key => $default_value) {
    if (!isset($configs[$key])) {
        sql_query("INSERT INTO `{$prefix}chat_config` (`cf_key`, `cf_value`) VALUES ('" . sql_real_escape_string($key) . "', '" . sql_real_escape_string($default_value) . "')");
        $configs[$key] = $default_value;
    }
}

// 설정값 변수 할당
$chat_enabled = $configs['chat_enabled'];
$chat_cooldown = $configs['chat_cooldown'];
$chat_max_members = $configs['chat_max_members'];
$chat_max_length = $configs['chat_max_length'];
$chat_polling_active = $configs['chat_polling_active'];
$chat_polling_inactive = $configs['chat_polling_inactive'];
$chat_create_level = $configs['chat_create_level'];

// 통계 가져오기
$stats = [];
$stats['rooms'] = sql_fetch("SELECT COUNT(*) as cnt FROM `{$prefix}chat_room`")['cnt'] ?? 0;
$stats['messages'] = sql_fetch("SELECT COUNT(*) as cnt FROM `{$prefix}chat_message`")['cnt'] ?? 0;
$stats['today_messages'] = sql_fetch("SELECT COUNT(*) as cnt FROM `{$prefix}chat_message` WHERE DATE(msg_created_at) = CURDATE()")['cnt'] ?? 0;
$stats['active_users'] = sql_fetch("SELECT COUNT(DISTINCT mb_id) as cnt FROM `{$prefix}chat_member` WHERE cm_left_at IS NULL")['cnt'] ?? 0;
$stats['banned'] = sql_fetch("SELECT COUNT(*) as cnt FROM `{$prefix}chat_ban` WHERE cb_end IS NULL OR cb_end > NOW()")['cnt'] ?? 0;
?>

<style>
.chat-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
}
.chat-stat-card {
    background: var(--bg-secondary, #f8f9fa);
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}
.chat-stat-card .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: var(--primary-color, #333);
    margin-bottom: 4px;
}
.chat-stat-card .stat-label {
    font-size: 13px;
    color: var(--text-muted, #666);
}

.chat-config-form {
    max-width: 600px;
}
.chat-config-form .form-group {
    margin-bottom: 20px;
}
.chat-config-form label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: var(--text-color, #333);
}
.chat-config-form .form-hint {
    font-size: 12px;
    color: var(--text-muted, #666);
    margin-top: 4px;
}
.chat-config-form input[type="number"],
.chat-config-form input[type="text"],
.chat-config-form select {
    width: 100%;
    max-width: 300px;
    border: 1px solid var(--border-color, #dee2e6);
    border-radius: 6px;
    font-size: 14px;
}
.chat-config-form input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-right: 8px;
    vertical-align: middle;
}
.chat-config-form .checkbox-label {
    display: inline-flex;
    align-items: center;
    cursor: pointer;
}
.chat-config-form .btn-save {
    padding: 12px 30px;
    background: var(--primary-color, #333);
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: opacity 0.2s;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
}
.chat-config-form .btn-save:hover {
    opacity: 0.9;
}
.chat-config-form .btn-save:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color, #dee2e6);
}
</style>

<!-- 통계 -->
<h3 class="section-title">채팅 현황</h3>
<div class="chat-stats">
    <div class="chat-stat-card">
        <div class="stat-value"><?php echo number_format($stats['rooms']); ?></div>
        <div class="stat-label">총 채팅방</div>
    </div>
    <div class="chat-stat-card">
        <div class="stat-value"><?php echo number_format($stats['messages']); ?></div>
        <div class="stat-label">총 메시지</div>
    </div>
    <div class="chat-stat-card">
        <div class="stat-value"><?php echo number_format($stats['today_messages']); ?></div>
        <div class="stat-label">오늘 메시지</div>
    </div>
    <div class="chat-stat-card">
        <div class="stat-value"><?php echo number_format($stats['active_users']); ?></div>
        <div class="stat-label">활성 사용자</div>
    </div>
    <div class="chat-stat-card">
        <div class="stat-value"><?php echo number_format($stats['banned']); ?></div>
        <div class="stat-label">채팅 금지</div>
    </div>
</div>

<!-- 설정 폼 -->
<h3 class="section-title">기본 설정</h3>
<form class="chat-config-form" id="chatConfigForm" onsubmit="return saveChatConfig();">
    <div class="form-group">
        <label class="checkbox-label">
            <input type="checkbox" name="chat_enabled" value="1" <?php echo $chat_enabled == '1' ? 'checked' : ''; ?>>
            채팅 기능 활성화
        </label>
        <div class="form-hint">비활성화하면 채팅 페이지 접근이 차단됩니다.</div>
    </div>

    <div class="form-group">
        <label>메시지 쿨다운 (초)</label>
        <input type="number" name="chat_cooldown" value="<?php echo $chat_cooldown; ?>" min="0" max="60">
        <div class="form-hint">메시지 전송 간격 제한. 0이면 제한 없음.</div>
    </div>

    <div class="form-group">
        <label>최대 그룹 인원</label>
        <input type="number" name="chat_max_members" value="<?php echo $chat_max_members; ?>" min="2" max="100">
        <div class="form-hint">그룹 채팅방의 최대 참여자 수.</div>
    </div>

    <div class="form-group">
        <label>메시지 최대 길이 (자)</label>
        <input type="number" name="chat_max_length" value="<?php echo $chat_max_length; ?>" min="100" max="10000">
        <div class="form-hint">한 번에 보낼 수 있는 메시지의 최대 글자 수.</div>
    </div>

    <div class="form-group">
        <label>폴링 주기 - 활성 (ms)</label>
        <input type="number" name="chat_polling_active" value="<?php echo $chat_polling_active; ?>" min="1000" max="60000" step="1000">
        <div class="form-hint">채팅방이 열려있을 때 새 메시지 확인 주기. 1000ms = 1초.</div>
    </div>

    <div class="form-group">
        <label>폴링 주기 - 비활성 (ms)</label>
        <input type="number" name="chat_polling_inactive" value="<?php echo $chat_polling_inactive; ?>" min="5000" max="300000" step="1000">
        <div class="form-hint">채팅방 목록에서 새 메시지 확인 주기.</div>
    </div>

    <div class="form-group">
        <label>채팅방 개설 권한</label>
        <select name="chat_create_level">
            <?php for ($i = 1; $i <= 10; $i++) { ?>
            <option value="<?php echo $i; ?>" <?php echo $chat_create_level == $i ? 'selected' : ''; ?>>레벨 <?php echo $i; ?> 이상</option>
            <?php } ?>
        </select>
        <div class="form-hint">채팅방을 새로 개설할 수 있는 최소 회원 레벨. 초대받은 참여는 레벨과 무관.</div>
    </div>

    <div class="form-group">
        <button type="submit" class="btn-save" id="btnSave">
            <i class="fa fa-save"></i> 설정 저장
        </button>
    </div>
</form>

<script>
function saveChatConfig() {
    var form = document.getElementById('chatConfigForm');
    var btn = document.getElementById('btnSave');
    var formData = new FormData(form);
    formData.append('action', 'save_config');

    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> 저장 중...';

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
            btn.innerHTML = '<i class="fa fa-save"></i> 설정 저장';
        }
    })
    .catch(error => {
        alert('오류: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-save"></i> 설정 저장';
    });

    return false; // 폼 기본 제출 방지
}
</script>
