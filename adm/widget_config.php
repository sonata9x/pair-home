<?php
$sub_menu = '100130';
include_once('./_common.php');

// 관리자 권한 체크
if (!$is_admin) {
    alert('관리자만 접근할 수 있습니다.');
}

$g5['title'] = '위젯 관리';
include_once('./admin.head.php');

// 위젯 라이브러리 로드
include_once(G5_LIB_PATH.'/widget.lib.php');

// 위젯 테이블명 정의 (없으면 기본값 설정)
if (!isset($g5['widget_table'])) {
    $g5['widget_table'] = G5_TABLE_PREFIX.'widget';
}

// 위젯 테이블이 없으면 자동 생성
$table_check = sql_query("SHOW TABLES LIKE '{$g5['widget_table']}'", false);
if (!$table_check || sql_num_rows($table_check) == 0) {
    $create_sql = "CREATE TABLE IF NOT EXISTS `{$g5['widget_table']}` (
      `wg_id` varchar(50) NOT NULL,
      `wg_name` varchar(100) NOT NULL,
      `wg_desc` text NOT NULL,
      `wg_version` varchar(20) NOT NULL DEFAULT '1.0',
      `wg_position` varchar(20) NOT NULL,
      `wg_location` varchar(10) NOT NULL DEFAULT 'main' COMMENT 'top: iframe 외부(최상위), main: iframe 내부',
      `wg_order` int(11) NOT NULL DEFAULT '0',
      `wg_use` tinyint(4) NOT NULL DEFAULT '0',
      `wg_config` text NOT NULL,
      `wg_datetime` datetime NOT NULL,
      PRIMARY KEY (`wg_id`),
      KEY `wg_position` (`wg_position`, `wg_order`),
      KEY `wg_use` (`wg_use`),
      KEY `wg_location` (`wg_location`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    sql_query($create_sql, false);
} else {
    // 기존 테이블에 wg_location 필드 추가 (없으면)
    $column_check = sql_fetch("SHOW COLUMNS FROM `{$g5['widget_table']}` LIKE 'wg_location'", false);
    if (!$column_check) {
        sql_query("ALTER TABLE `{$g5['widget_table']}`
                   ADD COLUMN `wg_location` varchar(10) NOT NULL DEFAULT 'main' COMMENT 'top: iframe 외부(최상위), main: iframe 내부' AFTER `wg_position`,
                   ADD KEY `wg_location` (`wg_location`)", false);
    }
}

// 위젯 디렉토리가 없으면 생성
if (!is_dir(G5_WIDGET_PATH)) {
    @mkdir(G5_WIDGET_PATH, 0755, true);
}

// 설치 가능한 위젯 스캔
$available_widgets = scan_widgets();

// 현재 등록된 위젯 목록
$sql = "SELECT * FROM {$g5['widget_table']} ORDER BY wg_position ASC, wg_order ASC";
$result = sql_query($sql);
$installed_widgets = array();
while ($row = sql_fetch_array($result)) {
    $installed_widgets[$row['wg_id']] = $row;
}

// 위치 옵션
$positions = array(
    'header' => '헤더',
    'footer' => '푸터',
    'sidebar_left' => '좌측 사이드바',
    'sidebar_right' => '우측 사이드바',
    'content_top' => '본문 상단',
    'content_bottom' => '본문 하단'
);

// 출력 위치 옵션
$locations = array(
    'top' => 'Top',
    'main' => 'Main'
);
?>

<style>
.widget-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 15px;
    margin-top: 10px;
}

.widget-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px 20px;
    background: #fff;
}

.widget-card.active {
    border-color: #4CAF50;
    background: #f1f8f4;
}

.widget-card h3 {
    font-size: 1.2em;
}

.widget-card .widget-id {
    font-size: 0.8em;
    color: #666;
    margin-bottom: 5px;
}

.widget-card .widget-desc {
    font-size: 1em;
    color: #333;
    margin-bottom: 10px;
    line-height: 1.5;
}

.widget-card .widget-info {
    font-size: 12px;
    color: #999;
    margin-bottom: 10px;
}

.widget-controls {
    display: flex;
    gap: 5px;
    align-items: center;
}

.widget-controls select {
    flex: 1;
    padding: 5px;
}

.widget-controls input[type="number"] {
    width: 60px;
    padding: 5px;
}

.widget-controls button {
    padding: 5px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-activate {
    background: #4CAF50;
    color: white;
}

.btn-deactivate {
    background: #f44336;
    color: white;
}

.btn-settings {
    background: #2196F3;
    color: white;
}

.tbl_head01 {
    margin-top: 30px;
}

.alert {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.alert-info {
    background: #e3f2fd;
    border: 1px solid #2196F3;
    color: #1976d2;
}

.alert-warning {
    background: #fff3e0;
    border: 1px solid #ff9800;
    color: #f57c00;
}
</style>

<div class="local_desc01 local_desc">
    <p>
        <strong>/html/widget/</strong> 디렉토리에 위젯 폴더를 추가하면 자동으로 감지됩니다.<br>
        각 위젯 폴더는 <strong>widget_*</strong> 형식의 이름을 가져야 합니다.<br>
        예: <code>/html/widget/widget_menu/</code>
    </p>
</div>

<?php if (empty($available_widgets)) { ?>
    <div class="alert alert-warning">
        <strong>알림:</strong> 설치 가능한 위젯이 없습니다. <code>/html/widget/</code> 디렉토리에 위젯을 추가하세요.
    </div>
<?php } ?>

<h2>설치 가능한 위젯</h2>

<div class="widget-list">
    <?php foreach ($available_widgets as $widget_id => $widget_info) {
        $is_installed = isset($installed_widgets[$widget_id]);
        $is_active = $is_installed && $installed_widgets[$widget_id]['wg_use'] == 1;

        $current_position = $is_installed ? $installed_widgets[$widget_id]['wg_position'] : 'header';
        $current_location = $is_installed ? $installed_widgets[$widget_id]['wg_location'] : (isset($widget_info['default_location']) ? $widget_info['default_location'] : 'main');
        $current_order = $is_installed ? $installed_widgets[$widget_id]['wg_order'] : 0;
    ?>
    <div class="widget-card <?php echo $is_active ? 'active' : ''; ?>">
        <h3><?php echo htmlspecialchars($widget_info['name']); ?></h3>
        <div class="widget-id">ID: <?php echo htmlspecialchars($widget_id); ?></div>
        <div class="widget-desc">
            <?php echo htmlspecialchars($widget_info['description']); ?>
        </div>
        <div class="widget-info">
            버전: <?php echo htmlspecialchars($widget_info['version']); ?>
            <?php if (isset($widget_info['author'])) { ?>
                | 제작자: <?php echo htmlspecialchars($widget_info['author']); ?>
            <?php } ?>
        </div>

        <form method="post" action="./widget_config_update.php">
            <input type="hidden" name="wg_id" value="<?php echo $widget_id; ?>">
            <input type="hidden" name="wg_name" value="<?php echo htmlspecialchars($widget_info['name']); ?>">
            <input type="hidden" name="wg_desc" value="<?php echo htmlspecialchars($widget_info['description']); ?>">
            <input type="hidden" name="wg_version" value="<?php echo htmlspecialchars($widget_info['version']); ?>">

            <div class="widget-controls">
                <?php if (isset($widget_info['position_required']) && $widget_info['position_required']) { ?>
                    <select name="wg_position" <?php echo !$is_installed ? 'disabled' : ''; ?>>
                        <?php foreach ($positions as $pos_value => $pos_label) { ?>
                            <option value="<?php echo $pos_value; ?>" <?php echo $current_position == $pos_value ? 'selected' : ''; ?>>
                                <?php echo $pos_label; ?>
                            </option>
                        <?php } ?>
                    </select>

                    <select name="wg_location" <?php echo !$is_installed ? 'disabled' : ''; ?>>
                        <?php foreach ($locations as $loc_value => $loc_label) { ?>
                            <option value="<?php echo $loc_value; ?>" <?php echo $current_location == $loc_value ? 'selected' : ''; ?>>
                                <?php echo $loc_label; ?>
                            </option>
                        <?php } ?>
                    </select>

                    <input type="number" name="wg_order" value="<?php echo $current_order; ?>"
                           placeholder="순서" <?php echo !$is_installed ? 'disabled' : ''; ?>>
                <?php } ?>

                <?php if ($is_active) { ?>
                    <button type="submit" name="action" value="deactivate" class="btn-deactivate">
                        비활성화
                    </button>
                <?php } else { ?>
                    <button type="submit" name="action" value="activate" class="btn-activate">
                        활성화
                    </button>
                <?php } ?>

                <?php if (isset($widget_info['config_page']) && $widget_info['config_page']) { ?>
                    <button type="button" class="btn-settings" onclick="openWidgetSettings('<?php echo $widget_id; ?>')">
                        설정
                    </button>
                <?php } ?>
            </div>
        </form>
    </div>
    <?php } ?>
</div>

<?php if (!empty($installed_widgets)) { ?>
<h2>활성 위젯 목록</h2>

<div class="tbl_head01 tbl_wrap">
    <table>
        <thead>
        <tr>
            <th>위젯 ID</th>
            <th>위젯명</th>
            <th>위치</th>
            <th>출력 레벨</th>
            <th>순서</th>
            <th>상태</th>
            <th>등록일시</th>
            <th>관리</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($installed_widgets as $widget) { ?>
        <tr>
            <td class="td_mng">
                <code><?php echo htmlspecialchars($widget['wg_id']); ?></code>
            </td>
            <td><?php echo htmlspecialchars($widget['wg_name']); ?></td>
            <td><?php echo $positions[$widget['wg_position']] ?? $widget['wg_position']; ?></td>
            <td><?php echo $locations[$widget['wg_location']] ?? $widget['wg_location']; ?></td>
            <td><?php echo $widget['wg_order']; ?></td>
            <td>
                <?php if ($widget['wg_use']) { ?>
                    <span style="color: #4CAF50; font-weight: bold;">● 활성</span>
                <?php } else { ?>
                    <span style="color: #999;">○ 비활성</span>
                <?php } ?>
            </td>
            <td><?php echo $widget['wg_datetime']; ?></td>
            <td class="td_mng">
                <form method="post" action="./widget_config_update.php" style="display:inline;">
                    <input type="hidden" name="wg_id" value="<?php echo $widget['wg_id']; ?>">
                    <button type="submit" name="action" value="delete"
                            onclick="return confirm('이 위젯을 삭제하시겠습니까?');"
                            class="btn btn_03">삭제</button>
                </form>
            </td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<?php } ?>

<!-- 위젯 설정 모달 -->
<div id="widget-settings-modal" class="widget-modal" style="display: none;">
    <div class="widget-modal-overlay" onclick="closeWidgetSettings()"></div>
    <div class="widget-modal-content">
        <div class="widget-modal-header">
            <h3 id="widget-modal-title">위젯 설정</h3>
            <button class="widget-modal-close" onclick="closeWidgetSettings()">&times;</button>
        </div>
        <div class="widget-modal-body">
            <iframe id="widget-settings-iframe" frameborder="0"></iframe>
        </div>
    </div>
</div>

<style>
.widget-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.widget-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.widget-modal-content {
    position: relative;
    background: #ffffff45;
    border-radius: 8px;
    width: 90%;
    max-width: 800px;
    height: 90%;
    max-height: 800px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    z-index: 1;
    backdrop-filter: blur(5px);
}

.widget-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.widget-modal-header h3 {
    margin: 0;
    font-size: 18px;
    color: #333;
}

.widget-modal-close {
    background: none;
    border: none;
    font-size: 28px;
    color: #999;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s;
}

.widget-modal-close:hover {
    color: #333;
}

.widget-modal-body {
    flex: 1;
    overflow: hidden;
    padding: 0;
}

.widget-modal-body iframe {
    width: 100%;
    height: 100%;
    border: none;
}
</style>

<script>
function openWidgetSettings(widgetId) {
    // 위젯별 설정 페이지 경로 (모달용)
    const settingsUrl = '<?php echo G5_URL; ?>/widget/' + widgetId + '/settings_modal.php';

    // 모달 표시
    const modal = document.getElementById('widget-settings-modal');
    const iframe = document.getElementById('widget-settings-iframe');
    const title = document.getElementById('widget-modal-title');

    if (!modal) {
        return;
    }

    // iframe에 설정 페이지 로드
    iframe.src = settingsUrl;

    // 모달 제목 설정 (위젯 이름으로)
    title.textContent = widgetId.replace('widget_', '') + ' 설정';

    // 모달 표시
    modal.style.display = 'flex';

    // body 스크롤 방지
    document.body.style.overflow = 'hidden';
}

function closeWidgetSettings() {
    const modal = document.getElementById('widget-settings-modal');
    const iframe = document.getElementById('widget-settings-iframe');

    // 모달 숨김
    modal.style.display = 'none';

    // iframe 초기화
    iframe.src = 'about:blank';

    // body 스크롤 복원
    document.body.style.overflow = '';

    // 페이지 새로고침 (설정 변경 반영)
    location.reload();
}

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('widget-settings-modal');
        if (modal.style.display === 'flex') {
            closeWidgetSettings();
        }
    }
});

// iframe 내부에서 창 닫기 요청 받기
window.addEventListener('message', function(e) {
    if (e.data === 'closeWidgetSettings') {
        closeWidgetSettings();
    }
});
</script>

<?php
include_once('./admin.tail.php');
?>
