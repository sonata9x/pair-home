<?php
/**
 * RA0 Edition 채팅 관리
 */
$sub_menu = "100400";
include_once('./_common.php');
include_once(G5_LIB_PATH . '/chat.lib.php');

if (!$is_admin) {
    alert('관리자만 접근 가능합니다.');
}

// 채팅 테이블 존재 여부 확인
$chat_room_table = G5_TABLE_PREFIX . 'chat_room';
$check_table = sql_query("SHOW TABLES LIKE '{$chat_room_table}'", false);
$is_installed = ($check_table && sql_num_rows($check_table) > 0);
$chat_schema_ready = $is_installed ? chat_migrate_member_schema() : true;

// 현재 탭
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'setup';
$valid_tabs = ['setup', 'rooms', 'messages', 'ban'];
if (!in_array($current_tab, $valid_tabs)) {
    $current_tab = 'setup';
}

$g5['title'] = '채팅 관리';
include_once('./admin.head.php');
?>

<style>
.chat-admin-wrap {
    max-width: 1200px;
}
.chat-admin-tabs {
    display: flex;
    gap: 0;
    margin-bottom: 20px;
    border-bottom: 2px solid var(--border-color, #dee2e6);
}
.chat-admin-tabs a {
    padding: 12px 24px;
    text-decoration: none;
    color: var(--text-color, #666);
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    transition: all 0.2s;
}
.chat-admin-tabs a:hover {
    color: var(--primary-color, #333);
    background: var(--bg-hover, #f8f9fa);
}
.chat-admin-tabs a.active {
    color: var(--primary-color, #333);
    border-bottom-color: var(--primary-color, #333);
    font-weight: 600;
}
.chat-admin-content {
    background: var(--card-bg, #fff);
    border: 1px solid var(--border-color, #dee2e6);
    border-radius: 8px;
    padding: 24px;
}

/* 설치 화면 */
.chat-install-wrap {
    text-align: center;
    padding: 60px 20px;
}
.chat-install-wrap h2 {
    font-size: 24px;
    margin-bottom: 16px;
    color: var(--text-color, #333);
}
.chat-install-wrap p {
    color: var(--text-muted, #666);
    margin-bottom: 30px;
}
.chat-install-wrap .btn-install {
    display: inline-flex;
    padding: 14px 40px;
    background: var(--primary-color, #333);
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: opacity 0.2s;
    align-items: center;
    justify-content: center;
    gap: 5px;
}
.chat-install-wrap .btn-install:hover {
    opacity: 0.9;
}
.chat-install-wrap .btn-install:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
.install-log {
    margin-top: 30px;
    text-align: left;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    background: var(--bg-secondary, #f8f9fa);
    border-radius: 6px;
    padding: 16px;
    font-family: monospace;
    font-size: 13px;
    max-height: 200px;
    overflow-y: auto;
    display: none;
}
.install-log.show {
    display: block;
}
.install-log .log-item {
    padding: 4px 0;
    border-bottom: 1px solid var(--border-color, #dee2e6);
}
.install-log .log-item:last-child {
    border-bottom: none;
}
.install-log .log-success {
    color: #28a745;
}
.install-log .log-error {
    color: #dc3545;
}
</style>

<div class="chat-admin-wrap">
    <h2 class="h2_tit">채팅 관리</h2>

    <?php if ($is_installed && !$chat_schema_ready) { ?>
    <div class="local_desc01 local_desc">
        <p>채팅 읽음 처리용 DB 컬럼을 추가하지 못했습니다. DB 계정의 ALTER 권한을 확인해주세요.</p>
    </div>
    <?php } ?>

    <?php if (!$is_installed) { ?>
    <!-- 미설치 상태: 설치 화면 -->
    <div class="chat-admin-content">
        <div class="chat-install-wrap">
            <h2>채팅 시스템 설치</h2>
            <p>채팅 기능을 사용하려면 먼저 데이터베이스 테이블을 생성해야 합니다.</p>
            <button type="button" class="btn-install" onclick="installChatTables()">
                <i class="fa fa-database"></i> 테이블 생성 및 설치
            </button>
            <div class="install-log" id="installLog"></div>
        </div>
    </div>

    <script>
    function installChatTables() {
        var btn = document.querySelector('.btn-install');
        var log = document.getElementById('installLog');

        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> 설치 중...';
        log.classList.add('show');
        log.innerHTML = '<div class="log-item">설치를 시작합니다...</div>';

        fetch('<?php echo G5_ADMIN_URL; ?>/chat/ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=install_tables'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.logs) {
                    data.logs.forEach(function(item) {
                        log.innerHTML += '<div class="log-item log-success">' + item + '</div>';
                    });
                }
                log.innerHTML += '<div class="log-item log-success">설치가 완료되었습니다!</div>';
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                log.innerHTML += '<div class="log-item log-error">오류: ' + data.error + '</div>';
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-database"></i> 테이블 생성 및 설치';
            }
        })
        .catch(error => {
            log.innerHTML += '<div class="log-item log-error">오류: ' + error.message + '</div>';
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-database"></i> 테이블 생성 및 설치';
        });
    }
    </script>

    <?php } else { ?>
    <!-- 설치됨: 탭 UI -->
    <div class="chat-admin-tabs">
        <a href="?tab=setup" class="<?php echo $current_tab === 'setup' ? 'active' : ''; ?>">
            <i class="fa fa-cog"></i> 기본 설정
        </a>
        <a href="?tab=rooms" class="<?php echo $current_tab === 'rooms' ? 'active' : ''; ?>">
            <i class="fa fa-comments"></i> 채팅방 관리
        </a>
        <a href="?tab=messages" class="<?php echo $current_tab === 'messages' ? 'active' : ''; ?>">
            <i class="fa fa-search"></i> 메시지 검색
        </a>
        <a href="?tab=ban" class="<?php echo $current_tab === 'ban' ? 'active' : ''; ?>">
            <i class="fa fa-ban"></i> 채팅 금지
        </a>
    </div>

    <div class="chat-admin-content">
        <?php
        $tab_file = G5_ADMIN_PATH . '/chat/tabs/' . $current_tab . '.php';
        if (file_exists($tab_file)) {
            include_once($tab_file);
        } else {
            echo '<p>탭 파일을 찾을 수 없습니다.</p>';
        }
        ?>
    </div>
    <?php } ?>
</div>

<?php
include_once('./admin.tail.php');
?>
