<?php
/**
 * RA0 Edition 푸시 알림 관리
 */
$sub_menu = "100900";
include_once('./_common.php');

if (!$is_admin) {
    alert('관리자만 접근 가능합니다.');
}

// 알림 라이브러리 로드
include_once(G5_LIB_PATH . '/notification.lib.php');

// Push 설정 로드
$push_config = [];
$config_file = G5_DATA_PATH . '/push/push_config.php';
if (file_exists($config_file)) {
    include($config_file);
}

// 구독 통계
check_push_subscription_table();
$total_subs = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['push_subscriptions_table']}");
$active_subs = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['push_subscriptions_table']} WHERE is_active = 1");
$failed_subs = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['push_subscriptions_table']} WHERE fail_count > 0 AND is_active = 1");

$g5['title'] = '푸시 알림 관리';
include_once('./admin.head.php');

$is_enabled = !empty($push_config['enabled']);
$has_keys = !empty($push_config['public_key']) && !empty($push_config['private_key_pem']);
?>

<style>
.push-admin-wrap {
    max-width: 800px;
}
.push-section {
    background: var(--card-bg, #fff);
    border: 1px solid var(--border-color, #dee2e6);
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 20px;
}
.push-section h3 {
    font-size: 16px;
    margin: 0 0 16px 0;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border-color, #dee2e6);
    display: flex;
    align-items: center;
    gap: 8px;
}
.push-field {
    margin-bottom: 16px;
}
.push-field label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    font-size: 13px;
    color: var(--text-color, #333);
}
.push-field input[type="text"],
.push-field input[type="email"],
.push-field select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid var(--border-color, #dee2e6);
    border-radius: 4px;
    font-size: 13px;
    box-sizing: border-box;
}
.push-field textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid var(--border-color, #dee2e6);
    border-radius: 4px;
    font-size: 12px;
    font-family: monospace;
    resize: vertical;
    box-sizing: border-box;
}
.push-field .help {
    font-size: 12px;
    color: var(--text-muted, #888);
    margin-top: 4px;
}
.push-key-display {
    background: var(--bg-secondary, #f8f9fa);
    padding: 10px 14px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 12px;
    word-break: break-all;
    color: var(--text-color, #333);
}
.push-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 8px;
}
.push-stat-card {
    text-align: center;
    padding: 16px;
    background: var(--bg-secondary, #f8f9fa);
    border-radius: 6px;
}
.push-stat-card .num {
    font-size: 28px;
    font-weight: 700;
    color: var(--primary-color, #333);
}
.push-stat-card .label {
    font-size: 12px;
    color: var(--text-muted, #888);
    margin-top: 4px;
}
.push-type-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 8px;
}
.push-type-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    background: var(--bg-secondary, #f8f9fa);
    border-radius: 4px;
    font-size: 13px;
}
.push-type-item input[type="checkbox"] {
    margin: 0;
}
.btn-push {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
    transition: opacity 0.2s;
}
.btn-push:hover { opacity: 0.9; }
.btn-push:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-push-primary {
    background: var(--primary-color, #333);
    color: #fff;
}
.btn-push-danger {
    background: #dc3545;
    color: #fff;
}
.btn-push-secondary {
    background: var(--bg-secondary, #e9ecef);
    color: var(--text-color, #333);
}
.push-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}
.push-status {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}
.push-status.on {
    background: #d4edda;
    color: #155724;
}
.push-status.off {
    background: #f8d7da;
    color: #721c24;
}
.push-msg {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 13px;
}
.push-msg.success {
    background: #d4edda;
    color: #155724;
}
.push-msg.error {
    background: #f8d7da;
    color: #721c24;
}
</style>

<div class="push-admin-wrap">
    <h2 class="h2_tit">푸시 알림 관리</h2>

    <?php if (isset($_GET['msg'])) { ?>
    <div class="push-msg <?php echo in_array($_GET['msg'], ['saved', 'generated']) ? 'success' : 'error'; ?>">
        <?php
        $msgs = [
            'saved'     => '설정이 저장되었습니다.',
            'generated' => 'VAPID 키가 생성되었습니다.',
            'error'     => '오류가 발생했습니다.',
        ];
        echo $msgs[$_GET['msg']] ?? '처리되었습니다.';
        ?>
    </div>
    <?php } ?>

    <!-- VAPID 키 섹션 -->
    <div class="push-section">
        <h3><i class="fa fa-key"></i> VAPID 키 관리</h3>

        <?php if ($has_keys) { ?>
        <div class="push-field">
            <label>공개키 (Application Server Key)</label>
            <div class="push-key-display"><?php echo htmlspecialchars($push_config['public_key']); ?></div>
            <p class="help">이 키는 클라이언트 JavaScript에서 Push 구독 시 사용됩니다.</p>
        </div>

        <form method="post" action="<?php echo G5_ADMIN_URL; ?>/config_push_update.php" onsubmit="return confirm('키를 재생성하면 기존 모든 구독이 무효화됩니다. 계속하시겠습니까?');">
            <input type="hidden" name="token" value="<?php echo get_token(); ?>">
            <input type="hidden" name="action" value="regenerate_keys">
            <button type="submit" class="btn-push btn-push-danger">
                <i class="fa fa-refresh"></i> 키 재생성 (주의: 기존 구독 무효화)
            </button>
        </form>
        <?php } else { ?>
        <p style="color: var(--text-muted, #666); margin-bottom: 16px;">
            Push 알림을 사용하려면 먼저 VAPID 키를 생성해야 합니다.
        </p>
        <form method="post" action="<?php echo G5_ADMIN_URL; ?>/config_push_update.php">
            <input type="hidden" name="token" value="<?php echo get_token(); ?>">
            <input type="hidden" name="action" value="generate_keys">
            <button type="submit" class="btn-push btn-push-primary">
                <i class="fa fa-key"></i> VAPID 키 생성
            </button>
        </form>
        <?php } ?>
    </div>

    <?php if ($has_keys) { ?>
    <!-- 설정 폼 -->
    <form method="post" action="<?php echo G5_ADMIN_URL; ?>/config_push_update.php">
        <input type="hidden" name="token" value="<?php echo get_token(); ?>">
        <input type="hidden" name="action" value="save_config">

        <!-- 기본 설정 -->
        <div class="push-section">
            <h3>
                <i class="fa fa-cog"></i> 기본 설정
                <span class="push-status <?php echo $is_enabled ? 'on' : 'off'; ?>">
                    <?php echo $is_enabled ? 'ON' : 'OFF'; ?>
                </span>
            </h3>

            <div class="push-field">
                <label>
                    <input type="checkbox" name="enabled" value="1" <?php echo $is_enabled ? 'checked' : ''; ?>>
                    Push 알림 활성화
                </label>
                <p class="help">체크하면 새 알림 발생 시 회원의 브라우저/모바일로 Push 알림을 발송합니다.</p>
            </div>

            <div class="push-field">
                <label>TTL (초)</label>
                <input type="text" name="ttl" value="<?php echo (int)($push_config['ttl'] ?? 86400); ?>" placeholder="86400">
                <p class="help">Push 서비스가 알림을 보관하는 최대 시간 (초). 기본 86400초 (24시간).</p>
            </div>

            <div class="push-field">
                <label>긴급도</label>
                <select name="urgency">
                    <?php
                    $urgencies = ['very-low' => '매우 낮음', 'low' => '낮음', 'normal' => '보통', 'high' => '높음'];
                    $current_urgency = $push_config['urgency'] ?? 'normal';
                    foreach ($urgencies as $val => $label) {
                        $selected = ($current_urgency === $val) ? 'selected' : '';
                        echo "<option value=\"{$val}\" {$selected}>{$label}</option>";
                    }
                    ?>
                </select>
                <p class="help">Push 알림의 우선순위입니다.</p>
            </div>
        </div>

        <!-- 알림 유형 설정 -->
        <div class="push-section">
            <h3><i class="fa fa-bell"></i> 알림 유형별 발송 설정</h3>
            <p class="help" style="margin-bottom: 12px;">체크된 유형만 Push 알림으로 발송됩니다.</p>

            <div class="push-type-grid">
                <?php
                $types = [
                    'comment' => '댓글',
                    'reply'   => '답글',
                    'mention' => '멘션',
                    'message' => '쪽지',
                    'like'    => '관심글',
                    'gift'    => '선물',
                    'trade'   => '거래',
                ];
                foreach ($types as $type_key => $type_label) {
                    $checked = !isset($push_config['types'][$type_key]) || $push_config['types'][$type_key] ? 'checked' : '';
                    echo '<label class="push-type-item">';
                    echo '<input type="checkbox" name="types['.$type_key.']" value="1" '.$checked.'>';
                    echo htmlspecialchars($type_label);
                    echo '</label>';
                }
                ?>
            </div>
        </div>

        <!-- 구독 통계 -->
        <div class="push-section">
            <h3><i class="fa fa-chart-bar"></i> 구독 현황</h3>

            <div class="push-stats">
                <div class="push-stat-card">
                    <div class="num"><?php echo number_format((int)$total_subs['cnt']); ?></div>
                    <div class="label">전체 구독</div>
                </div>
                <div class="push-stat-card">
                    <div class="num"><?php echo number_format((int)$active_subs['cnt']); ?></div>
                    <div class="label">활성 구독</div>
                </div>
                <div class="push-stat-card">
                    <div class="num"><?php echo number_format((int)$failed_subs['cnt']); ?></div>
                    <div class="label">오류 구독</div>
                </div>
            </div>
        </div>

        <div class="push-actions">
            <button type="submit" class="btn-push btn-push-primary">
                <i class="fa fa-save"></i> 설정 저장
            </button>
        </div>
    </form>
    <?php } ?>
</div>

<?php
include_once('./admin.tail.php');
?>
