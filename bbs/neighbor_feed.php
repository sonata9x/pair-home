<?php
/**
 * 이웃 피드 전용 페이지
 *
 * 등록된 이웃 사이트의 새 글을 전체 화면으로 보여줍니다.
 *
 * @package RA0Edition
 * @since 1.0
 */
include_once('./_common.php');

// 피드 라이브러리 로드
include_once(G5_LIB_PATH . '/feed.lib.php');

$g5['title'] = '이웃 새글';

// 페이징
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;

// 필터
$source = isset($_GET['source']) ? clean_xss_tags($_GET['source']) : '';

// 이웃 목록
$neighbors = get_all_neighbors();

// 피드 가져오기
$all_items = get_all_neighbor_feeds(100);

// 소스 필터링
if ($source) {
    $all_items = array_filter($all_items, function($item) use ($source) {
        return $item['source_name'] === $source;
    });
    $all_items = array_values($all_items);
}

// 페이징 적용
$total_count = count($all_items);
$total_pages = ceil($total_count / $per_page);
$offset = ($page - 1) * $per_page;
$feed_items = array_slice($all_items, $offset, $per_page);

// 새로고침 요청 처리
if (isset($_GET['refresh'])) {
    clear_feed_cache(0);
    $redirect_url = G5_BBS_URL . '/neighbor_feed.php';
    if ($source) $redirect_url .= '?source=' . urlencode($source);
    echo "<script>location.href = '{$redirect_url}';</script>";
    exit;
}

include_once(G5_PATH . '/head.php');
?>

<link rel="stylesheet" href="<?php echo G5_CSS_URL; ?>/feed.css">

<div class="nf-container">
    <!-- 헤더 -->
    <header class="nf-header">
        <div class="nf-header-left">
            <h1 class="nf-title">NEW<span>FEED</span></h1>
            <span class="nf-subtitle hidden-mobile">STREAMS // NEIGHBOR_DATA</span>
        </div>
        <div class="nf-header-right">
            <span class="nf-phase"><?php echo date('M Y'); ?></span>
            <div class="nf-bars">
                <?php for ($i = 0; $i < 5; $i++) { ?><span></span><?php } ?>
            </div>
        </div>
    </header>

    <!-- 컨트롤 -->
    <div class="nf-controls">
        <div class="nf-stats">
            <div class="stat">
                <span class="stat-label">Neighbors</span>
                <span class="stat-value"><?php echo count($neighbors); ?></span>
            </div>
            <div class="stat">
                <span class="stat-label">Posts</span>
                <span class="stat-value"><?php echo $total_count; ?></span>
            </div>
        </div>

        <div class="nf-filter">
            <select onchange="location.href='?source=' + encodeURIComponent(this.value)">
                <option value="">ALL SOURCES</option>
                <?php foreach ($neighbors as $n) { ?>
                    <option value="<?php echo htmlspecialchars($n['fn_site_name']); ?>"
                            <?php echo $source === $n['fn_site_name'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($n['fn_site_name']); ?>
                    </option>
                <?php } ?>
            </select>
            <a href="?refresh=1<?php echo $source ? '&source=' . urlencode($source) : ''; ?>" class="nf-refresh">Refresh</a>
        </div>
    </div>

    <!-- 피드 그리드 -->
    <div class="nf-grid">
        <?php if (empty($feed_items)) { ?>
            <div class="nf-empty">
                <div class="nf-empty-icon">[ ]</div>
                <h3><?php echo $source ? 'No posts from this source' : 'No neighbor posts'; ?></h3>
                <p>
                    <?php if (empty($neighbors)) { ?>
                        Register neighbors in admin panel.
                    <?php } else { ?>
                        Check back later.
                    <?php } ?>
                </p>
            </div>
        <?php } else { ?>
            <?php
            $idx = 0;
            foreach ($feed_items as $item) {
                $idx++;
                $item_id = str_pad($idx + $offset, 2, '0', STR_PAD_LEFT);
                $item_time = date('H:i', strtotime($item['date']));
                $board_name = $item['board'] ?? 'POST';
            ?>
            <article class="nf-card">
                <div class="nf-card-watermark"><?php echo htmlspecialchars($board_name); ?></div>

                <div class="nf-card-topbar">
                    <div class="nf-card-topbar-left">
                        <span class="nf-card-id">ID:<?php echo $item_id; ?></span>
                        <span class="nf-card-category"><?php echo htmlspecialchars($board_name); ?></span>
                    </div>
                    <div class="nf-card-topbar-right">
                        <span class="nf-card-time"><?php echo $item_time; ?></span>
                        <span class="nf-card-live"></span>
                    </div>
                </div>

                <div class="nf-card-content">
                    <div class="nf-card-main">
                        <div class="nf-card-text">
                            <h2 class="nf-card-title">
                                <a href="<?php echo htmlspecialchars($item['link']); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </a>
                            </h2>
                            <?php if (!empty($item['summary'])) { ?>
                                <p class="nf-card-excerpt"><?php echo htmlspecialchars($item['summary']); ?></p>
                            <?php } ?>
                        </div>

                        <?php if (!empty($item['thumbnail'])) { ?>
                        <div class="nf-card-thumb">
                            <img src="<?php echo htmlspecialchars($item['thumbnail']); ?>" alt="" loading="lazy">
                        </div>
                        <?php } ?>
                    </div>

                    <div class="nf-card-footer">
                        <div class="nf-card-author">
                            <span class="nf-card-author-label">Contributor</span>
                            <span class="nf-card-author-name">@<?php echo htmlspecialchars($item['author']); ?></span>
                        </div>

                        <div class="nf-card-actions">
                            <a href="?source=<?php echo urlencode($item['source_name']); ?>" title="<?php echo htmlspecialchars($item['source_name']); ?>">
                                <?php echo htmlspecialchars($item['source_name']); ?>
                            </a>
                            <a href="<?php echo htmlspecialchars($item['link']); ?>" target="_blank" class="nf-card-link" title="Open">↗</a>
                        </div>
                    </div>
                </div>
            </article>
            <?php } ?>
        <?php } ?>
    </div>

    <!-- 페이지네이션 -->
    <?php if ($total_pages > 1) { ?>
    <div class="nf-pagination">
        <?php
        $query = $source ? '&source=' . urlencode($source) : '';
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $page) {
                echo '<span class="current">' . $i . '</span>';
            } else {
                echo '<a href="?page=' . $i . $query . '">' . $i . '</a>';
            }
        }
        ?>
    </div>
    <?php } ?>

    <!-- 푸터 -->
    <footer class="nf-footer">
        <div class="nf-footer-text">[ END OF STREAM ]</div>
        <div class="nf-footer-bars">
            <?php for ($i = 0; $i < 20; $i++) { ?><span></span><?php } ?>
        </div>
        <div class="nf-footer-time">LOCAL_TIME: <span id="nf-clock"></span></div>
    </footer>
</div>

<script>
(function() {
    function updateClock() {
        var now = new Date();
        var h = String(now.getHours()).padStart(2, '0');
        var m = String(now.getMinutes()).padStart(2, '0');
        var s = String(now.getSeconds()).padStart(2, '0');
        var el = document.getElementById('nf-clock');
        if (el) el.textContent = h + ':' + m + ':' + s;
    }
    updateClock();
    setInterval(updateClock, 1000);
})();
</script>

<?php
include_once(G5_PATH . '/tail.php');
?>
