<?php
$sub_menu = '200300';
require_once './_common.php';

if (!$is_admin) {
    alert('관리자만 접근할 수 있습니다.');
}

$g5['title'] = '단축 주소 관리';
include_once './admin.head.php';

// 테이블 존재 여부 확인
$table_exists = sql_query("SHOW TABLES LIKE '" . G5_SHORT_URL_TABLE . "'", false);
$is_installed = ($table_exists && sql_num_rows($table_exists) > 0);

// 설치 처리
if (isset($_POST['install']) && !$is_installed) {
    $sql_file = G5_PATH . '/install/short_url.sql';
    if (file_exists($sql_file)) {
        $sql = file_get_contents($sql_file);
        $result = sql_query($sql, false);
        if ($result) {
            alert('URL 단축 시스템이 설치되었습니다.', './short_url_list.php');
        } else {
            alert('설치 중 오류가 발생했습니다.');
        }
    } else {
        alert('SQL 파일을 찾을 수 없습니다.');
    }
}

// 삭제 처리
if (isset($_POST['act']) && $_POST['act'] == 'delete' && $is_installed) {
    $su_ids = isset($_POST['chk']) ? $_POST['chk'] : array();
    if (count($su_ids) > 0) {
        foreach ($su_ids as $su_id) {
            $su_id = (int)$su_id;
            sql_query("DELETE FROM " . G5_SHORT_URL_TABLE . " WHERE su_id = '$su_id'");
        }
        alert(count($su_ids) . '개의 짧은 주소가 삭제되었습니다.', './short_url_list.php');
    }
}

// 페이징
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rows_per_page = 20;
$offset = ($page - 1) * $rows_per_page;

// 검색
$search_key = isset($_GET['search_key']) ? clean_xss_tags($_GET['search_key'], 1, 1) : '';
$search_value = isset($_GET['search_value']) ? clean_xss_tags($_GET['search_value'], 1, 1) : '';

$where = '';
if ($search_key && $search_value) {
    $search_value_escape = sql_real_escape_string($search_value);
    if ($search_key == 'su_key') {
        $where = " WHERE su_key LIKE '%$search_value_escape%'";
    } elseif ($search_key == 'su_url') {
        $where = " WHERE su_url LIKE '%$search_value_escape%'";
    } elseif ($search_key == 'su_bo_table') {
        $where = " WHERE su_bo_table = '$search_value_escape'";
    }
}

if ($is_installed) {
    // 통계
    $stats = sql_fetch("
        SELECT
            COUNT(*) as total_count,
            COUNT(CASE WHEN DATE(su_datetime) = CURDATE() THEN 1 END) as today_count,
            COUNT(CASE WHEN DATE(su_datetime) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as week_count,
            COUNT(CASE WHEN DATE(su_datetime) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as month_count
        FROM " . G5_SHORT_URL_TABLE . " $where
    ");

    // 목록
    $total_count = $stats['total_count'];
    $total_pages = ceil($total_count / $rows_per_page);

    $sql = "SELECT * FROM " . G5_SHORT_URL_TABLE . " $where ORDER BY su_datetime DESC LIMIT $offset, $rows_per_page";
    $result = sql_query($sql);
}
?>

<style>
/* 단축 주소 관리 레이아웃 */
.shorturl-management {
}

/* 통계 카드 */
.shorturl-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

@media (max-width: 1200px) {
    .shorturl-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.stat-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.stat-card.primary {
    background: linear-gradient(135deg, var(--primary-color, #667eea) 0%, var(--accent-color, #764ba2) 100%);
    color: white;
    border: none;
}

.stat-label {
    font-size: 12px;
    color: #666;
    margin-bottom: 8px;
}

.stat-card.primary .stat-label {
    color: rgba(255,255,255,0.9);
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #333;
}

.stat-card.primary .stat-value {
    color: white;
}

/* 설치 화면 */
.install-notice {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    margin: 50px 0;
}

.install-notice-icon {
    font-size: 48px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.install-notice h2 {
    margin-bottom: 15px;
    color: #856404;
}

.install-notice p {
    margin-bottom: 20px;
    line-height: 1.6;
    color: #666;
}

.install-info {
    text-align: left;
    display: inline-block;
    margin: 20px 0;
}

.install-info ul {
    padding-left: 20px;
}

.install-info li {
    margin: 8px 0;
    color: #666;
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
    padding: 12px 30px;
    background: var(--primary-color, #667eea);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-install:hover {
    background: #5568d3;
}

/* 액션 바 */
.shorturl-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    border-radius: 6px;
    gap: 15px;
    flex-wrap: wrap;
}

.shorturl-actions h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.shorturl-search {
    display: flex;
    gap: 8px;
    align-items: center;
}

.shorturl-search select,
.shorturl-search input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
}

.shorturl-search input {
    min-width: 200px;
}

.btn-search {
    padding: 8px 16px;
    background: var(--primary-color, #667eea);
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-search:hover {
    background: #5568d3;
}

.btn-reset {
    padding: 8px 16px;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}

.btn-reset:hover {
    background: #5a6268;
}

/* 테이블 */
.shorturl-table-wrapper {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 20px;
}

.shorturl-table {
    width: 100%;
    border-collapse: collapse;
}

.shorturl-table thead {
    background: #f8f9fa;
}

.shorturl-table th {
    text-align: left;
    font-weight: 600;
    color: #555;
    font-size: 11px;
    border-bottom: 1px solid #dee2e6;
}

.shorturl-table th.center {
    text-align: center;
}

.shorturl-table td {
    padding: 0px 5px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 10px;
}

.shorturl-table td.center {
    text-align: center;
}

.shorturl-table tbody tr:hover {
    background: #f8f9fa;
}

/* 짧은 키 */
.short-key {
    font-family: 'Courier New', monospace;
    font-weight: 600;
    color: var(--primary-color, #667eea);
    font-size: 13px;
}

/* 짧은 URL */
.short-url {
    font-size: 10px;
    color: #666;
}

/* 원본 URL */
.original-url {
    font-size: 10px;
    color: #666;
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
}

/* 버튼 */
.btn-copy {
    padding: 0px 8px;
    background: var(--primary-color, #667eea);
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    height: 25px;
}

.btn-copy:hover {
    background: #5568d3;
}

.btn-delete-selected {
    padding: 8px 16px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-delete-selected:hover {
    background: #c82333;
}

/* 빈 상태 */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-state-icon {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
}

/* 페이지네이션 */
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
    padding: 20px 0;
}

.pagination a,
.pagination .current {
    display: inline-block;
    min-width: 36px;
    height: 36px;
    line-height: 36px;
    text-align: center;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    font-size: 14px;
    text-decoration: none;
    color: #555;
    background: #fff;
}

.pagination a:hover {
    background: #f8f9fa;
    border-color: var(--primary-color, #667eea);
    color: var(--primary-color, #667eea);
}

.pagination .current {
    background: var(--primary-color, #667eea);
    color: white;
    border-color: var(--primary-color, #667eea);
}
</style>

<?php if (!$is_installed) { ?>
    <div class="shorturl-management">
        <div class="install-notice">
            <div class="install-notice-icon">📎</div>
            <h2>URL 단축 시스템 설치</h2>
            <p>게시글 공유를 위한 짧은 주소 생성 기능이 아직 설치되지 않았습니다.</p>

            <div class="install-info">
                <ul>
                    <li>테이블: <code><?php echo G5_SHORT_URL_TABLE; ?></code> 생성</li>
                    <li>기능: 게시글 공유용 짧은 주소 자동 생성</li>
                    <li>형식: <code><?php echo G5_URL; ?>/s/{짧은키}</code></li>
                </ul>
            </div>

            <form method="post">
                <button type="submit" name="install" class="btn-install">지금 설치하기</button>
            </form>
        </div>
    </div>
<?php } else { ?>
    <div class="shorturl-management">
        <!-- 통계 카드 -->
        <div class="shorturl-stats-grid">
            <div class="stat-card primary">
                <div class="stat-label">총 생성 개수</div>
                <div class="stat-value"><?php echo number_format($stats['total_count']); ?>개</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">오늘 생성</div>
                <div class="stat-value"><?php echo number_format($stats['today_count']); ?>개</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">이번 주</div>
                <div class="stat-value"><?php echo number_format($stats['week_count']); ?>개</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">이번 달</div>
                <div class="stat-value"><?php echo number_format($stats['month_count']); ?>개</div>
            </div>
        </div>

        <!-- 액션 바 -->
        <div class="shorturl-actions">
            <h3>📋 단축 주소 목록</h3>

            <form method="get" class="shorturl-search">
                <select name="search_key">
                    <option value="su_key" <?php echo $search_key == 'su_key' ? 'selected' : ''; ?>>짧은 키</option>
                    <option value="su_url" <?php echo $search_key == 'su_url' ? 'selected' : ''; ?>>원본 URL</option>
                    <option value="su_bo_table" <?php echo $search_key == 'su_bo_table' ? 'selected' : ''; ?>>게시판</option>
                </select>
                <input type="text" name="search_value" value="<?php echo htmlspecialchars($search_value); ?>" placeholder="검색어 입력">
                <button type="submit" class="btn-search">검색</button>
                <?php if ($search_value) { ?>
                    <a href="./short_url_list.php" class="btn-reset">초기화</a>
                <?php } ?>
            </form>
        </div>

        <!-- 테이블 -->
        <form method="post" name="flist">
            <input type="hidden" name="act" value="delete">

            <?php if ($total_count > 0) { ?>
            <div style="margin-bottom: 10px;">
                <button type="submit" class="btn-delete-selected" onclick="return confirm('선택한 항목을 삭제하시겠습니까?');">선택 삭제</button>
            </div>
            <?php } ?>

            <div class="shorturl-table-wrapper">
                <table class="shorturl-table">
                    <thead>
                        <tr>
                            <th class="center" width="40">
                                <input type="checkbox" onclick="if (this.checked) { var chks = document.getElementsByName('chk[]'); for (var i=0; i<chks.length; i++) { chks[i].checked = true; } } else { var chks = document.getElementsByName('chk[]'); for (var i=0; i<chks.length; i++) { chks[i].checked = false; } }">
                            </th>
                            <th width="80">짧은 키</th>
                            <th width="180">짧은 주소</th>
                            <th>원본 URL</th>
                            <th width="100">게시판</th>
                            <th width="80">글 ID</th>
                            <th width="140">생성일시</th>
                            <th class="center" width="80">관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && sql_num_rows($result) > 0) {
                            while ($row = sql_fetch_array($result)) {
                                $short_url = G5_URL . '/s/' . $row['su_key'];
                        ?>
                        <tr>
                            <td class="center">
                                <input type="checkbox" name="chk[]" value="<?php echo $row['su_id']; ?>">
                            </td>
                            <td>
                                <span class="short-key"><?php echo htmlspecialchars($row['su_key']); ?></span>
                            </td>
                            <td>
                                <div class="short-url"><?php echo htmlspecialchars($short_url); ?></div>
                            </td>
                            <td>
                                <a href="<?php echo htmlspecialchars($row['su_url']); ?>" target="_blank" class="original-url" title="<?php echo htmlspecialchars($row['su_url']); ?>">
                                    <?php echo htmlspecialchars($row['su_url']); ?>
                                </a>
                            </td>
                            <td><?php echo $row['su_bo_table'] ? htmlspecialchars($row['su_bo_table']) : '-'; ?></td>
                            <td class="center"><?php echo $row['su_wr_id'] ? $row['su_wr_id'] : '-'; ?></td>
                            <td><?php echo substr($row['su_datetime'], 0, 16); ?></td>
                            <td class="center">
                                <button type="button" class="btn-copy" onclick="copyToClipboard('<?php echo $short_url; ?>')">복사</button>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                        ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="empty-state-icon">🔗</div>
                                    <p><?php echo $search_value ? '검색 결과가 없습니다.' : '생성된 짧은 주소가 없습니다.'; ?></p>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </form>

        <!-- 페이징 -->
        <?php if ($total_pages > 1) { ?>
        <div class="pagination">
            <?php
            $query_string = '';
            if ($search_key && $search_value) {
                $query_string = '&search_key=' . urlencode($search_key) . '&search_value=' . urlencode($search_value);
            }

            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $page) {
                    echo '<span class="current">' . $i . '</span>';
                } else {
                    echo '<a href="?page=' . $i . $query_string . '">' . $i . '</a>';
                }
            }
            ?>
        </div>
        <?php } ?>
    </div>

    <script>
    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                alert('짧은 주소가 복사되었습니다:\n' + text);
            }, function() {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    }

    function fallbackCopy(text) {
        var textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = 0;
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            alert('짧은 주소가 복사되었습니다:\n' + text);
        } catch (err) {
            alert('복사 실패. 수동으로 복사해주세요:\n' + text);
        }
        document.body.removeChild(textarea);
    }
    </script>
<?php } ?>

<?php
include_once './admin.tail.php';
?>
