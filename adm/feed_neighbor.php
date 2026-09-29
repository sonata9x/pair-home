<?php
/**
 * 이웃 관리 - 다른 라공 에디션 사이트 등록/삭제
 */
$sub_menu = '200500';
require_once './_common.php';

if (!$is_admin) {
    alert('관리자만 접근할 수 있습니다.');
}

// 피드 라이브러리 로드
include_once(G5_LIB_PATH . '/feed.lib.php');

$g5['title'] = '이웃 관리';
include_once './admin.head.php';

// 테이블 존재 여부 확인
$is_installed = feed_neighbor_table_exists();

// 설치 처리
if (isset($_POST['install']) && !$is_installed) {
    $result = install_feed_system();
    if ($result['success']) {
        alert($result['message'], './feed_neighbor.php');
    } else {
        alert('설치 실패: ' . $result['message']);
    }
}

// 이웃 추가 처리
if (isset($_POST['act']) && $_POST['act'] == 'add' && $is_installed) {
    $site_url = isset($_POST['site_url']) ? trim($_POST['site_url']) : '';

    if (empty($site_url)) {
        alert('사이트 URL을 입력해주세요.');
    }

    // URL 정규화
    $site_url = preg_replace('/\/$/', '', $site_url); // 끝 슬래시 제거

    // 프로토콜이 없으면 https:// 추가
    if (!preg_match('/^https?:\/\//i', $site_url)) {
        $site_url = 'https://' . $site_url;
    }

    // 피드 URL 생성
    $feed_url = $site_url . '/api/feed.php';

    // 중복 체크
    $exists = sql_fetch("SELECT fn_id FROM " . G5_FEED_NEIGHBOR_TABLE . " WHERE fn_site_url = '" . sql_real_escape_string($site_url) . "'");
    if ($exists) {
        alert('이미 등록된 사이트입니다.');
    }

    // 피드 유효성 검증
    $validation = validate_feed_url($feed_url);

    if (!$validation['valid']) {
        alert('피드 연결 실패: ' . $validation['error']);
    }

    // 등록
    $sql = "INSERT INTO " . G5_FEED_NEIGHBOR_TABLE . " SET
            fn_site_name = '" . sql_real_escape_string($validation['site_name']) . "',
            fn_site_url = '" . sql_real_escape_string($site_url) . "',
            fn_feed_url = '" . sql_real_escape_string($feed_url) . "',
            fn_created_at = NOW()";

    if (sql_query($sql)) {
        alert("이웃 '{$validation['site_name']}'이(가) 등록되었습니다.", './feed_neighbor.php');
    } else {
        alert('등록 중 오류가 발생했습니다.');
    }
}

// 삭제 처리
if (isset($_POST['act']) && $_POST['act'] == 'delete' && $is_installed) {
    $fn_ids = isset($_POST['chk']) ? $_POST['chk'] : array();

    if (count($fn_ids) > 0) {
        foreach ($fn_ids as $fn_id) {
            $fn_id = (int)$fn_id;
            // 캐시 삭제
            clear_feed_cache($fn_id);
            // 데이터 삭제
            sql_query("DELETE FROM " . G5_FEED_NEIGHBOR_TABLE . " WHERE fn_id = '{$fn_id}'");
        }
        alert(count($fn_ids) . '개의 이웃이 삭제되었습니다.', './feed_neighbor.php');
    }
}

// 캐시 새로고침
if (isset($_GET['refresh']) && $is_installed) {
    $fn_id = (int)$_GET['refresh'];
    if ($fn_id > 0) {
        clear_feed_cache($fn_id);
        alert('캐시가 삭제되었습니다. 다음 요청 시 새로 가져옵니다.', './feed_neighbor.php');
    }
}

// 전체 캐시 새로고침
if (isset($_GET['refresh_all']) && $is_installed) {
    clear_feed_cache(0);
    alert('모든 피드 캐시가 삭제되었습니다.', './feed_neighbor.php');
}

// 이웃 목록 조회
if ($is_installed) {
    $sql = "SELECT * FROM " . G5_FEED_NEIGHBOR_TABLE . " ORDER BY fn_site_name";
    $result = sql_query($sql);
    $total_count = sql_num_rows($result);
}
?>

<style>
.neighbor-management {
    max-width: 1000px;
}

/* 추가 폼 */
.neighbor-add-form {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.neighbor-add-form h4 {
    margin: 0 0 15px 0;
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.neighbor-add-form .form-row {
    display: flex;
    gap: 10px;
    align-items: center;
}

.neighbor-add-form input[type="text"] {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.neighbor-add-form input[type="text"]:focus {
    border-color: var(--primary-color, #667eea);
    outline: none;
}

.btn-add {
    padding: 10px 20px;
    background: var(--primary-color, #667eea);
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    white-space: nowrap;
}

.btn-add:hover {
    background: #5568d3;
}

.form-help {
    margin-top: 10px;
    font-size: 12px;
    color: #666;
}

/* 액션 바 */
.neighbor-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    gap: 15px;
}

.neighbor-actions h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
    display: flex;
    flex-direction: row;
    gap: 5px;
    align-items: flex-end;
}

.action-buttons {
    display: flex;
    gap: 10px;
}

.btn-refresh-all {
    padding: 8px 16px;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
}

.btn-refresh-all:hover {
    background: #5a6268;
    color: white;
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
}

.btn-delete-selected:hover {
    background: #c82333;
}

/* 테이블 */
.neighbor-table-wrapper {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

.neighbor-table {
    width: 100%;
    border-collapse: collapse;
}

.neighbor-table thead {
    background: #f8f9fa;
}

.neighbor-table th {
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
    color: #555;
    font-size: 13px;
    border-bottom: 1px solid #dee2e6;
}

.neighbor-table th.center {
    text-align: center;
}

.neighbor-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 13px;
}

.neighbor-table td.center {
    text-align: center;
}

.neighbor-table tbody tr:hover {
    background: #f8f9fa;
}

.site-name {
    font-weight: 600;
    color: #333;
}

.site-url {
    font-size: 12px;
    color: #666;
}

.btn-refresh {
    padding: 4px 10px;
    background: #17a2b8;
    color: white;
    border: none;
    border-radius: 3px;
    font-size: 11px;
    cursor: pointer;
    text-decoration: none;
}

.btn-refresh:hover {
    background: #138496;
    color: white;
}

.btn-view {
    padding: 4px 10px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 3px;
    font-size: 11px;
    cursor: pointer;
    text-decoration: none;
}

.btn-view:hover {
    background: #218838;
    color: white;
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

/* 설치 화면 */
.install-notice {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    margin: 50px 0;
}

.install-notice h2 {
    margin-bottom: 15px;
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
}

.btn-install:hover {
    background: #5568d3;
}

/* 통계 */
.neighbor-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.stat-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 16px 20px;
    min-width: 120px;
}

.stat-card.primary {
    background: linear-gradient(135deg, var(--primary-color, #667eea) 0%, var(--accent-color, #764ba2) 100%);
    color: white;
    border: none;
}

.stat-label {
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
}

.stat-card.primary .stat-label {
    color: rgba(255,255,255,0.9);
}

.stat-value {
    font-size: 14px;
    font-weight: 500;
    color: #333;
}

.stat-card.primary .stat-value {
    color: white;
}
</style>

<?php if (!$is_installed) { ?>
<div class="neighbor-management">
    <div class="install-notice">
        <h2>이웃 피드 시스템 설치</h2>
        <p>이웃 관리 기능을 사용하려면 먼저 설치가 필요합니다.</p>
        <form method="post">
            <button type="submit" name="install" class="btn-install">지금 설치하기</button>
        </form>
    </div>
</div>
<?php } else { ?>
<div class="neighbor-management">
    <!-- 이웃 추가 폼 -->
    <div class="neighbor-add-form">
        <h4>이웃 추가</h4>
        <form method="post">
            <input type="hidden" name="act" value="add">
            <div class="form-row">
                <input type="text" name="site_url" placeholder="이웃 사이트 URL (예: https://example.com)" required>
                <button type="submit" class="btn-add">추가</button>
            </div>
            <p class="form-help">
                * 라공 에디션이 설치된 사이트만 등록할 수 있습니다. (피드 API가 있어야 함)
            </p>
        </form>
    </div>

    <!-- 액션 바 -->
    <div class="neighbor-actions">
        <h3>이웃 목록<div class="stat-value"><?php echo $total_count; ?>명</div></h3>
        <div class="action-buttons">
            <a href="?refresh_all=1" class="btn-refresh-all" onclick="return confirm('모든 이웃 피드 캐시를 삭제하시겠습니까?');">전체 캐시 새로고침</a>
        </div>
    </div>

    <!-- 이웃 목록 -->
    <form method="post" name="flist">
        <input type="hidden" name="act" value="delete">

        <?php if ($total_count > 0) { ?>
        <div style="margin-bottom: 10px;">
            <button type="submit" class="btn-delete-selected" onclick="return confirm('선택한 이웃을 삭제하시겠습니까?');">선택 삭제</button>
        </div>
        <?php } ?>

        <div class="neighbor-table-wrapper">
            <table class="neighbor-table">
                <thead>
                    <tr>
                        <th class="center" width="40">
                            <input type="checkbox" onclick="if (this.checked) { var chks = document.getElementsByName('chk[]'); for (var i=0; i<chks.length; i++) { chks[i].checked = true; } } else { var chks = document.getElementsByName('chk[]'); for (var i=0; i<chks.length; i++) { chks[i].checked = false; } }">
                        </th>
                        <th>사이트</th>
                        <th width="300">피드 URL</th>
                        <th width="140">등록일</th>
                        <th class="center" width="200">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && sql_num_rows($result) > 0) {
                        sql_data_seek($result, 0);
                        while ($row = sql_fetch_array($result)) {
                    ?>
                    <tr>
                        <td class="center">
                            <input type="checkbox" name="chk[]" value="<?php echo $row['fn_id']; ?>">
                        </td>
                        <td>
                            <div class="site-name"><?php echo htmlspecialchars($row['fn_site_name']); ?></div>
                            <div class="site-url">
                                <a href="<?php echo htmlspecialchars($row['fn_site_url']); ?>" target="_blank">
                                    <?php echo htmlspecialchars($row['fn_site_url']); ?>
                                </a>
                            </div>
                        </td>
                        <td>
                            <span style="font-size: 11px; color: #666;">
                                <?php echo htmlspecialchars($row['fn_feed_url']); ?>
                            </span>
                        </td>
                        <td><?php echo substr($row['fn_created_at'], 0, 10); ?></td>
                        <td class="center">
                            <a href="<?php echo htmlspecialchars($row['fn_feed_url']); ?>" target="_blank" class="btn-view">피드 보기</a>
                            <a href="?refresh=<?php echo $row['fn_id']; ?>" class="btn-refresh" onclick="return confirm('이 이웃의 캐시를 새로고침하시겠습니까?');">캐시 삭제</a>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-state-icon">🏠</div>
                                <p>등록된 이웃이 없습니다.</p>
                                <p style="font-size: 12px; margin-top: 5px;">위 폼에서 다른 라공 에디션 사이트를 등록해보세요.</p>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </form>
</div>
<?php } ?>

<?php
include_once './admin.tail.php';
?>
