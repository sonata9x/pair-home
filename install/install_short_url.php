<?php
/**
 * RA0 Edition - URL 단축 시스템 설치
 *
 * 이 파일을 웹 브라우저에서 직접 실행하거나,
 * short_url.sql 파일을 phpMyAdmin에서 직접 실행할 수 있습니다.
 */

// 보안: 관리자만 실행 가능
$sub_menu = '';
require_once '../common.php';

if (!$is_admin) {
    alert('관리자만 접근할 수 있습니다.', G5_URL);
}

$g5['title'] = 'URL 단축 시스템 설치';
include_once G5_ADMIN_PATH.'/admin.head.php';

$sql_file = __DIR__ . '/short_url.sql';
$installed = false;
$error_msg = '';

// 이미 설치되어 있는지 확인
$table_exists = sql_query("SHOW TABLES LIKE '" . G5_SHORT_URL_TABLE . "'", false);
$already_installed = ($table_exists && sql_num_rows($table_exists) > 0);

// POST 요청 시 설치 진행
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {
    if ($already_installed) {
        $error_msg = '이미 설치되어 있습니다.';
    } else {
        $sql = file_get_contents($sql_file);
        if ($sql) {
            $result = sql_query($sql, false);
            if ($result) {
                $installed = true;
            } else {
                $error_msg = 'SQL 실행 오류가 발생했습니다.';
            }
        } else {
            $error_msg = 'SQL 파일을 읽을 수 없습니다.';
        }
    }
}
?>

<style>
.install-container {
    max-width: 800px;
    margin: 50px auto;
    padding: 30px;
    background: var(--card-bg-color, #fff);
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.install-header {
    text-align: center;
    margin-bottom: 30px;
}
.install-header h1 {
    font-size: 24px;
    margin-bottom: 10px;
}
.install-status {
    padding: 20px;
    border-radius: 6px;
    margin-bottom: 20px;
    text-align: center;
}
.install-status.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.install-status.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
.install-status.warning {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}
.install-info {
    margin: 20px 0;
    line-height: 1.8;
}
.install-info h3 {
    margin-top: 20px;
    margin-bottom: 10px;
    font-size: 16px;
}
.install-info ul {
    padding-left: 20px;
}
.install-info code {
    background: #f4f4f4;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
}
.btn-install {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 15px;
    background: var(--primary-color, #007bff);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 20px;
}
.btn-install:hover {
    opacity: 0.9;
}
.btn-install:disabled {
    background: #6c757d;
    cursor: not-allowed;
}
</style>

<div class="install-container">
    <div class="install-header">
        <h1>📎 URL 단축 시스템 설치</h1>
        <p>게시글 공유를 위한 짧은 URL 생성 기능을 설치합니다.</p>
    </div>

    <?php if ($installed) { ?>
        <div class="install-status success">
            <h2>✅ 설치가 완료되었습니다!</h2>
            <p>이제 모든 게시글에서 '공유' 버튼을 통해 짧은 URL을 생성할 수 있습니다.</p>
        </div>
        <a href="<?php echo G5_ADMIN_URL; ?>" class="btn-install">관리자 페이지로 돌아가기</a>
    <?php } elseif ($already_installed) { ?>
        <div class="install-status warning">
            <h2>⚠️ 이미 설치되어 있습니다</h2>
            <p>URL 단축 시스템이 이미 설치되어 있습니다.</p>
        </div>
        <a href="<?php echo G5_ADMIN_URL; ?>" class="btn-install">관리자 페이지로 돌아가기</a>
    <?php } else { ?>
        <?php if ($error_msg) { ?>
            <div class="install-status error">
                <h2>❌ 오류 발생</h2>
                <p><?php echo $error_msg; ?></p>
            </div>
        <?php } ?>

        <div class="install-info">
            <h3>📋 설치 내용</h3>
            <ul>
                <li>테이블: <code><?php echo G5_SHORT_URL_TABLE; ?></code></li>
                <li>기능: 게시글 공유용 짧은 URL 생성</li>
                <li>경로: <code>/s/{짧은키}</code> 형식으로 접근</li>
            </ul>

            <h3>🔧 테이블 구조</h3>
            <ul>
                <li><code>su_key</code>: 짧은 URL 키 (6자, base62)</li>
                <li><code>su_url</code>: 원본 URL</li>
                <li><code>su_bo_table</code>, <code>su_wr_id</code>: 게시판 정보</li>
                <li><code>su_datetime</code>: 생성일시</li>
            </ul>

            <h3>📌 사용 방법</h3>
            <ul>
                <li>게시글 보기 화면에서 '공유' 버튼 클릭</li>
                <li>짧은 URL이 자동 생성되어 복사 가능</li>
                <li>SNS, 메신저 등에 간편하게 공유</li>
            </ul>
        </div>

        <form method="post">
            <button type="submit" name="install" class="btn-install">설치하기</button>
        </form>
    <?php } ?>
</div>

<?php
include_once G5_ADMIN_PATH.'/admin.tail.php';
?>
