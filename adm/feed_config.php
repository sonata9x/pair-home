<?php
/**
 * 피드 설정 - 게시판별 피드 노출 설정
 */
$sub_menu = '200400';
require_once './_common.php';

if (!$is_admin) {
    alert('관리자만 접근할 수 있습니다.');
}

// 피드 라이브러리 로드
include_once(G5_LIB_PATH . '/feed.lib.php');

$g5['title'] = '피드 설정';
include_once './admin.head.php';

// 시스템 설치 여부 확인
$is_installed = feed_column_exists();

// 설치 처리
if (isset($_POST['install']) && !$is_installed) {
    $result = install_feed_system();
    if ($result['success']) {
        alert($result['message'], './feed_config.php');
    } else {
        alert('설치 실패: ' . $result['message']);
    }
}

// 설정 저장 처리
if (isset($_POST['act']) && $_POST['act'] == 'update' && $is_installed) {
    $bo_tables = isset($_POST['bo_table']) ? $_POST['bo_table'] : array();
    $feed_uses = isset($_POST['bo_feed_use']) ? $_POST['bo_feed_use'] : array();

    foreach ($bo_tables as $bo_table) {
        $bo_table = sql_real_escape_string($bo_table);
        $feed_use = in_array($bo_table, $feed_uses) ? 1 : 0;

        sql_query("UPDATE {$g5['board_table']} SET bo_feed_use = '{$feed_use}' WHERE bo_table = '{$bo_table}'");
    }

    alert('피드 설정이 저장되었습니다.', './feed_config.php');
}

// 게시판 목록 조회
if ($is_installed) {
    $sql = "SELECT bo_table, bo_subject, bo_read_level, bo_feed_use
            FROM {$g5['board_table']}
            ORDER BY bo_order, bo_table";
    $result = sql_query($sql);
}
?>

<style>
.feed-config {
    max-width: 900px;
}

.feed-info {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.feed-info h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #333;
}

.feed-info p {
    margin: 0;
    font-size: 13px;
    color: #666;
    line-height: 1.6;
}

.feed-info code {
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 12px;
}

.feed-url-box {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px 15px;
    margin-top: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.feed-url-box input {
    flex: 1;
    border: none;
    font-size: 13px;
    color: #333;
    background: transparent;
}

.btn-copy-url {
    padding: 6px 12px;
    background: var(--primary-color, #667eea);
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
}

.btn-copy-url:hover {
    background: #5568d3;
}

.feed-table-wrapper {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 20px;
}

.feed-table {
    width: 100%;
    border-collapse: collapse;
}

.feed-table thead {
    background: #f8f9fa;
}

.feed-table th {
    padding: 8px 5px;
    text-align: left;
    font-weight: 600;
    color: #555;
    font-size: 13px;
    border-bottom: 1px solid #dee2e6;
}

.feed-table th.center {
    text-align: center;
}

.feed-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 13px;
}

.feed-table td.center {
    text-align: center;
}

.feed-table tbody tr:hover {
    background: #f8f9fa;
}

.feed-table .disabled {
    color: #999;
    background: #fafafa;
}

.feed-table .disabled td {
    opacity: 0.7;
}

.level-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 500;
}

.level-badge.public {
    background: #d4edda;
    color: #155724;
}

.level-badge.private {
    background: #fff3cd;
    color: #856404;
}

.btn-save {
    padding: 10px 24px;
    background: var(--primary-color, #667eea);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    justify-content: center;
    align-items: center;
}

.btn-save:hover {
    background: #5568d3;
}

.install-notice {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    margin: 50px 0;
}

.install-notice h2 {
    margin-bottom: 5px;
    color: #333;
}

.install-notice p {
    margin-bottom: 20px;
    color: #666;
}

.btn-install {
    padding: 12px 30px;
    background: var(--primary-color, #667eea);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    justify-content: center;
    align-items: center;
}

.btn-install:hover {
    background: #5568d3;
}
</style>

<?php if (!$is_installed) { ?>
<div class="feed-config">
    <div class="install-notice">
        <h2>피드 시스템 설치</h2>
        <p>피드 시스템을 사용하려면 먼저 설치가 필요합니다.</p>
        <form method="post">
            <button type="submit" name="install" class="btn-install">지금 설치하기</button>
        </form>
    </div>
</div>
<?php } else { ?>
<div class="feed-config">
    <!-- 피드 정보 -->
    <div class="feed-info">
        <h4>이 사이트의 피드 API</h4>
        <p>
            다른 라공 에디션 사이트에서 이 피드 URL을 등록하면 이 사이트의 새 글을 확인할 수 있습니다.<br>
            <strong>읽기 권한이 1(비회원 공개)</strong>이고 <strong>피드 노출이 활성화</strong>된 게시판의 글만 피드에 포함됩니다.
        </p>
        <div class="feed-url-box">
            <input type="text" id="feed-url" value="<?php echo G5_URL; ?>/api/feed.php" readonly>
            <button type="button" class="btn-copy-url" onclick="copyFeedUrl()">복사</button>
        </div>
    </div>

    <!-- 게시판 목록 -->
    <form method="post">
        <input type="hidden" name="act" value="update">

        <div class="feed-table-wrapper">
            <table class="feed-table">
                <thead>
                    <tr>
                        <th width="50" class="center">피드</th>
                        <th width="120">게시판 ID</th>
                        <th>게시판명</th>
                        <th width="100" class="center">읽기 권한</th>
                        <th width="200">상태</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = sql_fetch_array($result)) {
                        $is_public = ($row['bo_read_level'] == 1);
                        $disabled_class = !$is_public ? 'disabled' : '';
                    ?>
                    <tr class="<?php echo $disabled_class; ?>">
                        <td class="center">
                            <input type="hidden" name="bo_table[]" value="<?php echo $row['bo_table']; ?>">
                            <input type="checkbox" name="bo_feed_use[]" value="<?php echo $row['bo_table']; ?>"
                                <?php echo $row['bo_feed_use'] ? 'checked' : ''; ?>
                                <?php echo !$is_public ? 'disabled' : ''; ?>>
                        </td>
                        <td><?php echo htmlspecialchars($row['bo_table']); ?></td>
                        <td><?php echo htmlspecialchars($row['bo_subject']); ?></td>
                        <td class="center">
                            <span class="level-badge <?php echo $is_public ? 'public' : 'private'; ?>">
                                레벨 <?php echo $row['bo_read_level']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!$is_public) { ?>
                                <span style="color: #999; font-size: 12px;">읽기 권한이 1이 아니므로 피드 불가</span>
                            <?php } elseif ($row['bo_feed_use']) { ?>
                                <span style="color: #28a745; font-size: 12px;">피드에 노출됨</span>
                            <?php } else { ?>
                                <span style="color: #6c757d; font-size: 12px;">피드에서 제외됨</span>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div style="text-align: right;">
            <button type="submit" class="btn-save">설정 저장</button>
        </div>
    </form>
</div>

<script>
function copyFeedUrl() {
    var input = document.getElementById('feed-url');
    input.select();
    input.setSelectionRange(0, 99999);

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(input.value).then(function() {
            alert('피드 URL이 복사되었습니다.');
        });
    } else {
        document.execCommand('copy');
        alert('피드 URL이 복사되었습니다.');
    }
}
</script>
<?php } ?>

<?php
include_once './admin.tail.php';
?>
