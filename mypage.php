<?php
include_once('./_common.php');

// 로그인 체크
if (!$member['mb_id']) {
    alert('로그인 후 이용하세요.', G5_BBS_URL.'/login.php?url='.urlencode(G5_URL.'/mypage.php'));
}

$g5['title'] = '마이페이지';

// 탭 설정 — 기본은 overview (대시보드)
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';
$valid_tabs = array('overview', 'posts', 'comments', 'favorites', 'notifications');
if (!in_array($tab, $valid_tabs)) $tab = 'overview';

include_once(G5_PATH.'/head.php');
?>

<style>
.mypage-container {
    max-width: 90%;
    margin: 0px auto;
    background: transparent;
    padding: var(--spacing-lg);
    border-radius: var(--container-border-radius);
}

@media (max-width: 768px) {
    .mypage-container {
        max-width: 100%;
        padding: 0;
    }

    .mypage-header h2 {
        font-size: 1.4em;
    }

    .tab-menu {
        flex-wrap: wrap;
    }

    .tab-button {
        padding: 8px 12px;
        font-size: 0.9em;
        flex: 1;
        min-width: auto;
        text-align: center;
    }

    .tab-content {
        border-radius: 0 0 12px 12px;
    }

    .posts-table th:first-child,
    .posts-table td:first-child {
        display: none;
    }

    .posts-table th,
    .posts-table td {
        padding: 8px 5px;
        font-size: 0.9em;
    }

    #posts-tab,
    #comments-tab {
        padding: 0;
    }

    #notifications-tab {
        padding: 10px;
    }
}

.mypage-header {
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.mypage-header h2 {
    margin: 0;
    font-size: 1.8em;
    color: var(--content-font-color);
}

.mypage-account-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.mypage-account-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    background: var(--btn-secondary-bg, var(--gray-100));
    color: var(--btn-secondary-text, var(--text-secondary));
    border: 1px solid color-mix(in srgb, var(--card-border-color) 30%, transparent);
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    text-decoration: none;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
}
.mypage-account-btn i { font-size: 11px; }
.mypage-account-btn:hover {
    background: var(--btn-primary-bg);
    border-color: var(--accent-color, var(--primary-color));
    color: var(--btn-primary-text);
}
.mypage-account-btn-danger {
    color: rgb(from var(--error-color, #ef4444) r g b / 0.85);
}
.mypage-account-btn-danger:hover {
    background: color-mix(in srgb, var(--error-color, #ef4444) 12%, transparent);
    border-color: var(--error-color, #ef4444);
    color: var(--error-color, #ef4444);
}

@media (max-width: 600px) {
    .mypage-account-btn { padding: 6px 10px; font-size: 11px; }
    .mypage-account-btn span { display: none; }
}

/* ===== 대시보드 ===== */
.mypage-back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: var(--text-secondary, var(--text-muted));
    text-decoration: none;
    margin-bottom: 6px;
    transition: color 0.15s;
}
.mypage-back-link:hover { color: var(--accent-color, var(--primary-color)); }

/* 회원 프로필 카드 */
.mypage-profile-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px 22px;
    margin-bottom: 18px;
    background: var(--container-bg-color);
    border: 1px solid color-mix(in srgb, var(--card-border-color) 25%, transparent);
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    flex-wrap: wrap;
}
.mypage-profile-card .mypage-account-actions {
    margin-left: auto;
    flex-shrink: 0;
}
.mypage-profile-avatar {
    flex: 0 0 auto;
    width: 64px; height: 64px;
    border-radius: 50%;
    background: var(--btn-secondary-bg, var(--gray-100));
    border: 2px solid color-mix(in srgb, var(--accent-color) 35%, transparent);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    color: var(--text-muted, #888);
    font-size: 28px;
}
.mypage-profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
.mypage-profile-info { flex: 1; min-width: 0; }
.mypage-profile-name {
    font-size: 18px;
    font-weight: 700;
    color: var(--content-font-color, var(--text-primary));
    margin-bottom: 6px;
}
.mypage-profile-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 12px;
    color: var(--text-muted, #888);
}
.mypage-profile-meta i { margin-right: 4px; }
.mypage-badge-admin {
    color: var(--accent-color, var(--primary-color));
    font-weight: 600;
}

/* 통계 카드 그리드 */
.mypage-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
    margin-bottom: 24px;
}
.mypage-stat-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 22px 16px;
    background: var(--container-bg-color);
    border: 1px solid color-mix(in srgb, var(--card-border-color) 25%, transparent);
    border-radius: 12px;
    text-decoration: none;
    color: var(--text-primary);
    transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s, background 0.2s;
    position: relative;
}
.mypage-stat-card:hover {
    transform: translateY(-3px);
    border-color: var(--accent-color, var(--primary-color));
    box-shadow: 0 6px 18px rgba(0,0,0,0.2);
    background: color-mix(in srgb, var(--accent-color) 5%, var(--container-bg-color));
}
.mypage-stat-icon {
    position: relative;
    font-size: 22px;
    color: var(--accent-color, var(--primary-color));
    opacity: 0.9;
}
.mypage-stat-dot {
    position: absolute;
    top: -2px; right: -6px;
    width: 8px; height: 8px;
    border-radius: 50%;
    background: var(--error-color, #ef4444);
    box-shadow: 0 0 6px rgb(from var(--error-color, #ef4444) r g b / 0.85);
}
.mypage-stat-num {
    font-size: 28px;
    font-weight: 700;
    color: var(--content-font-color, var(--text-primary));
    line-height: 1;
    margin-top: 2px;
}
.mypage-stat-label {
    font-size: 12px;
    color: var(--text-secondary, var(--text-muted));
    letter-spacing: 0.3px;
}

@media (max-width: 600px) {
    .mypage-profile-card { padding: 14px 16px; gap: 12px; }
    .mypage-profile-avatar { width: 52px; height: 52px; font-size: 22px; }
    .mypage-profile-name { font-size: 16px; }
    .mypage-profile-card .mypage-account-actions {
        margin-left: 0;
        width: 100%;
        flex-basis: 100%;
        justify-content: stretch;
    }
    .mypage-profile-card .mypage-account-btn { flex: 1; justify-content: center; }
    .mypage-stat-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .mypage-stat-card { padding: 16px 10px; }
    .mypage-stat-num { font-size: 22px; }
}

.tab-content {
    background: var(--container-bg-color);
    border-radius: 0px 12px 12px 12px;
}

.tab-menu {
    display: flex;
    gap: 1px;
}

.tab-button {
    padding: 6px 10px 4px;
    background: var(--btn-secondary-bg);
    border: none;
    border-bottom: 3px solid transparent;
    color: var(--btn-secondary-text);
    cursor: pointer;
    font-size: 1em;
    transition: all 0.3s;
    border-radius: 6px 6px 0 0;
}

.tab-button .tab-count {
    display: inline-block;
    min-width: 20px;
    padding: 1px 6px;
    margin-left: 4px;
    background: var(--accent-color);
    color: var(--white);
    border-radius: 10px;
    font-size: 0.75em;
    line-height: 1.4;
    text-align: center;
    vertical-align: middle;
}

.tab-button.active .tab-count {
    background: var(--btn-secondary-bg);
    color: var(--btn-secondary-text);
}

.tab-button:hover {
    border-bottom-color: var(--accent-color);
    transform: translateY(-1px);
}

.tab-button.active {
    color: var(--btn-secondary-bg);
    background: var(--btn-secondary-text);
    border-bottom-color: var(--container-bg-color);
}

/* 내 글 목록 스타일 */
.posts-table {
    width: 100%;
    border-collapse: collapse;
}

.posts-table thead {
    background: var(--container-bg-color);
}

.posts-table th, .posts-table td {
    padding: 12px;
    text-align: left;
}

.posts-table th {
    font-weight: bold;
    color: var(--content-font-color);
}

.posts-table tbody tr:hover {
    background: var(--light-bg-color);
}

.post-title {
    color: var(--content-font-color);
    text-decoration: none;
}

.post-title:hover {
    color: var(--accent-color);
}

.post-board {
    display: inline-block;
    padding: 2px 8px;
    background: var(--btn-secondary-bg);
    color: var(--btn-secondary-text);
    border-radius: 4px;
    font-size: 0.85em;
}

.post-type {
    display: inline-block;
    padding: 2px 6px;
    background: var(--info-light);
    color: var(--content-font-color);
    border-radius: 3px;
    font-size: 0.8em;
}

.post-date {
    color: var(--text-muted);
    font-size: 0.9em;
}

.empty-content {
    text-align: center;
    padding: 50px 20px;
    color: var(--text-muted);
}

.pagination {
    margin-top: 30px;
    text-align: center;
}

.pagination a {
    display: inline-block;
    padding: 8px 12px;
    margin: 0 3px;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    color: var(--btn-secondary-bg);
    text-decoration: none;
}

.pagination a:hover {
    background: var(--light-bg-color);
}

.pagination .current {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

#posts-tab, #comments-tab, #favorites-tab {
    padding: 5px;
}

.tab-button i.fa-star {
    color: #f5c518;
}

#notifications-tab {
    padding: 25px;
}

#notifications-tab .notification-list {
    gap: 10px;
}

#notifications-tab .btn-read-all {
    background: var(--info-color);
}
</style>

<div class="mypage-container">
    <?php
    // 탭 카운트 조회
    $my_post_count = sql_fetch("SELECT COUNT(DISTINCT bo_table, wr_parent) as cnt FROM {$g5['board_new_table']} WHERE mb_id = '{$member['mb_id']}' AND wr_id = wr_parent");
    $my_post_count = (int)$my_post_count['cnt'];

    $my_comment_count = sql_fetch("SELECT COUNT(DISTINCT bo_table, wr_id) as cnt FROM {$g5['board_new_table']} WHERE mb_id = '{$member['mb_id']}' AND wr_id != wr_parent");
    $my_comment_count = (int)$my_comment_count['cnt'];

    $my_favorite_count = (int)get_member_like_count($member['mb_id']);

    // 알림 미읽음 수
    $my_unread_count = 0;
    if (function_exists('get_notification_count')) {
        $my_unread_count = (int)get_notification_count($member['mb_id']);
    }
    ?>

    <?php if ($tab === 'overview'): ?>
    <!-- ========== 대시보드 (메인) ========== -->
    <div class="mypage-header mypage-header-overview">
        <h2>마이페이지</h2>
    </div>

    <?php
    // 회원 정보
    $mb_reg_date = !empty($member['mb_datetime']) ? substr($member['mb_datetime'], 0, 10) : '';
    $mb_level    = isset($member['mb_level']) ? (int)$member['mb_level'] : 0;
    $mb_avatar   = '';
    if (!empty($member['mb_id'])) {
        $mb = get_member($member['mb_id'], 'mb_signature');
        $mb_avatar = $mb['mb_signature'] ?? '';
    }
    ?>
    <div class="mypage-profile-card">
        <div class="mypage-profile-avatar">
            <?php if ($mb_avatar): ?>
                <img src="<?php echo htmlspecialchars($mb_avatar); ?>" alt="">
            <?php else: ?>
                <i class="fa-solid fa-user"></i>
            <?php endif; ?>
        </div>
        <div class="mypage-profile-info">
            <div class="mypage-profile-name"><?php echo htmlspecialchars($member['mb_id']); ?></div>
            <div class="mypage-profile-meta">
                <?php if ($mb_reg_date): ?><span><i class="fa-regular fa-calendar"></i> <?php echo htmlspecialchars($mb_reg_date); ?> 가입</span><?php endif; ?>
                <?php if ($mb_level >= 10): ?><span class="mypage-badge-admin"><i class="fa-solid fa-shield-halved"></i> 관리자</span><?php endif; ?>
            </div>
        </div>
        <div class="mypage-account-actions">
            <a href="<?php echo G5_BBS_URL; ?>/member_confirm.php?url=<?php echo urlencode(G5_BBS_URL.'/register_form.php'); ?>" class="mypage-account-btn">
                <i class="fa-solid fa-user-pen"></i> 정보 수정
            </a>
            <a href="<?php echo G5_BBS_URL; ?>/member_confirm.php?url=<?php echo urlencode(G5_BBS_URL.'/member_leave.php'); ?>" class="mypage-account-btn mypage-account-btn-danger">
                <i class="fa-solid fa-user-xmark"></i> 회원 탈퇴
            </a>
        </div>
    </div>

    <div class="mypage-stat-grid">
        <a href="?tab=posts" class="mypage-stat-card">
            <div class="mypage-stat-icon"><i class="fa-solid fa-pen-nib"></i></div>
            <div class="mypage-stat-num"><?php echo number_format($my_post_count); ?></div>
            <div class="mypage-stat-label">내가 쓴 글</div>
        </a>
        <a href="?tab=comments" class="mypage-stat-card">
            <div class="mypage-stat-icon"><i class="fa-solid fa-comment-dots"></i></div>
            <div class="mypage-stat-num"><?php echo number_format($my_comment_count); ?></div>
            <div class="mypage-stat-label">내가 쓴 댓글</div>
        </a>
        <a href="?tab=favorites" class="mypage-stat-card">
            <div class="mypage-stat-icon"><i class="fa-solid fa-star"></i></div>
            <div class="mypage-stat-num"><?php echo number_format($my_favorite_count); ?></div>
            <div class="mypage-stat-label">관심글</div>
        </a>
        <a href="?tab=notifications" class="mypage-stat-card">
            <div class="mypage-stat-icon"><i class="fa-solid fa-bell"></i><?php if ($my_unread_count > 0): ?><span class="mypage-stat-dot"></span><?php endif; ?></div>
            <div class="mypage-stat-num"><?php echo number_format($my_unread_count); ?></div>
            <div class="mypage-stat-label">읽지 않은 알림</div>
        </a>
    </div>

    <?php else: /* 상세 탭 */ ?>
    <div class="mypage-header">
        <a href="?tab=overview" class="mypage-back-link"><i class="fa-solid fa-arrow-left"></i> 마이페이지</a>
        <h2><?php
            $tab_titles = array(
                'posts' => '내가 쓴 글',
                'comments' => '내가 쓴 댓글',
                'favorites' => '관심글',
                'notifications' => '알림',
            );
            echo htmlspecialchars($tab_titles[$tab] ?? '');
        ?></h2>
    </div>

    <div class="tab-menu">
        <button class="tab-button <?php echo $tab == 'posts' ? 'active' : ''; ?>"
                onclick="location.href='?tab=posts'">내가 쓴 글 <span class="tab-count"><?php echo number_format($my_post_count); ?></span></button>
        <button class="tab-button <?php echo $tab == 'comments' ? 'active' : ''; ?>"
                onclick="location.href='?tab=comments'">내가 쓴 댓글 <span class="tab-count"><?php echo number_format($my_comment_count); ?></span></button>
        <button class="tab-button <?php echo $tab == 'favorites' ? 'active' : ''; ?>"
                onclick="location.href='?tab=favorites'">관심글 <span class="tab-count"><?php echo number_format($my_favorite_count); ?></span></button>
        <button class="tab-button <?php echo $tab == 'notifications' ? 'active' : ''; ?>"
                onclick="location.href='?tab=notifications'">알림</button>
    </div>
    <?php endif; ?>

    <?php if ($tab !== 'overview'): ?>
    <div class="tab-content">
        <?php if ($tab == 'posts') { ?>
        <!-- 내가 쓴 글 탭 -->
        <div id="posts-tab">
            <?php
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 15;
            $offset = ($page - 1) * $limit;
            
            // 전체 게시글 수
            $sql = "SELECT COUNT(DISTINCT bo_table, wr_parent) as cnt 
                    FROM {$g5['board_new_table']} 
                    WHERE mb_id = '{$member['mb_id']}' 
                    AND wr_id = wr_parent";
            $total_row = sql_fetch($sql);
            $total_count = $total_row['cnt'];
            $total_page = ceil($total_count / $limit);
            
            // 게시글 목록
            $sql = "SELECT bn.*, b.bo_subject 
                    FROM {$g5['board_new_table']} bn
                    LEFT JOIN {$g5['board_table']} b ON bn.bo_table = b.bo_table
                    WHERE bn.mb_id = '{$member['mb_id']}' 
                    AND bn.wr_id = bn.wr_parent
                    ORDER BY bn.bn_datetime DESC
                    LIMIT {$offset}, {$limit}";
            $result = sql_query($sql);
            
            if (sql_num_rows($result) > 0) {
            ?>
            <table class="posts-table">
                <thead>
                    <tr>
                        <th width="150">게시판</th>
                        <th>제목</th>
                        <th width="120">작성일</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                while ($row = sql_fetch_array($result)) {
                    $write_table = $g5['write_prefix'] . $row['bo_table'];
                    $write = sql_fetch("SELECT wr_subject, wr_comment FROM {$write_table} WHERE wr_id = '{$row['wr_id']}'");

                    // 게시글이 삭제되었거나 존재하지 않으면 건너뛰기
                    if (!$write || !isset($write['wr_subject'])) {
                        continue;
                    }
                ?>
                    <tr>
                        <td>
                            <span class="post-board"><?php echo $row['bo_subject']; ?></span>
                        </td>
                        <td>
                            <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $row['bo_table']; ?>&wr_id=<?php echo $row['wr_id']; ?>"
                               class="post-title">
                                <?php echo $write['wr_subject']; ?>
                                <?php if (isset($write['wr_comment']) && $write['wr_comment'] > 0) { ?>
                                    <span style="color: var(--accent-color);">[<?php echo $write['wr_comment']; ?>]</span>
                                <?php } ?>
                            </a>
                        </td>
                        <td>
                            <span class="post-date"><?php echo date('Y-m-d', strtotime($row['bn_datetime'])); ?></span>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            
            <?php if ($total_page > 1) { ?>
            <div class="pagination">
                <?php
                $start_page = max(1, $page - 5);
                $end_page = min($total_page, $page + 5);
                
                if ($page > 1) {
                    echo '<a href="?tab=posts&page='.($page-1).'">이전</a>';
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    $class = ($i == $page) ? 'current' : '';
                    echo '<a href="?tab=posts&page='.$i.'" class="'.$class.'">'.$i.'</a>';
                }
                
                if ($page < $total_page) {
                    echo '<a href="?tab=posts&page='.($page+1).'">다음</a>';
                }
                ?>
            </div>
            <?php } ?>
            
            <?php } else { ?>
            <div class="empty-content">
                <p>작성한 글이 없습니다.</p>
            </div>
            <?php } ?>
        </div>
        
        <?php } else if ($tab == 'comments') { ?>
        <!-- 내가 쓴 댓글 탭 -->
        <div id="comments-tab">
            <?php
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 15;
            $offset = ($page - 1) * $limit;
            
            // 전체 댓글 수 (타임라인 답글 포함)
            $sql = "SELECT COUNT(DISTINCT bo_table, wr_id) as cnt 
                    FROM {$g5['board_new_table']} 
                    WHERE mb_id = '{$member['mb_id']}' 
                    AND wr_id != wr_parent";
            $total_row = sql_fetch($sql);
            $total_count = $total_row['cnt'];
            $total_page = ceil($total_count / $limit);
            
            // 댓글 목록
            $sql = "SELECT bn.*, b.bo_subject, b.bo_type
                    FROM {$g5['board_new_table']} bn
                    LEFT JOIN {$g5['board_table']} b ON bn.bo_table = b.bo_table
                    WHERE bn.mb_id = '{$member['mb_id']}' 
                    AND bn.wr_id != bn.wr_parent
                    ORDER BY bn.bn_datetime DESC
                    LIMIT {$offset}, {$limit}";
            $result = sql_query($sql);
            
            if (sql_num_rows($result) > 0) {
            ?>
            <table class="posts-table">
                <thead>
                    <tr>
                        <th width="150">게시판</th>
                        <th>내용</th>
                        <th width="120">작성일</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                while ($row = sql_fetch_array($result)) {
                    $write_table = $g5['write_prefix'] . $row['bo_table'];

                    // 타임라인 답글과 일반 댓글 구분
                    if ($row['bo_type'] == 'timeline') {
                        $write = sql_fetch("SELECT wr_subject, wr_content, tm_parent FROM {$write_table} WHERE wr_id = '{$row['wr_id']}'");
                        $parent_id = ($write && isset($write['tm_parent']) && $write['tm_parent']) ? $write['tm_parent'] : $row['wr_parent'];
                        $type_label = '답글';
                    } else {
                        $write = sql_fetch("SELECT wr_content FROM {$write_table} WHERE wr_id = '{$row['wr_id']}' AND wr_is_comment = 1");
                        $parent_id = $row['wr_parent'];
                        $type_label = '댓글';
                    }

                    // 게시글이 삭제되었거나 존재하지 않으면 건너뛰기
                    if (!$write || !isset($write['wr_content'])) {
                        continue;
                    }
                ?>
                    <tr>
                        <td>
                            <span class="post-board"><?php echo $row['bo_subject']; ?></span>
                            <span class="post-type"><?php echo $type_label; ?></span>
                        </td>
                        <td>
                            <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $row['bo_table']; ?>&wr_id=<?php echo $parent_id; ?>#c_<?php echo $row['wr_id']; ?>"
                               class="post-title">
                                <?php
                                if ($row['bo_type'] == 'timeline' && isset($write['wr_subject']) && $write['wr_subject']) {
                                    echo cut_str($write['wr_subject'], 30) . ' | ';
                                }
                                echo cut_str(strip_tags($write['wr_content']), 50);
                                ?>
                            </a>
                        </td>
                        <td>
                            <span class="post-date"><?php echo date('Y-m-d', strtotime($row['bn_datetime'])); ?></span>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            
            <?php if ($total_page > 1) { ?>
            <div class="pagination">
                <?php
                $start_page = max(1, $page - 5);
                $end_page = min($total_page, $page + 5);
                
                if ($page > 1) {
                    echo '<a href="?tab=comments&page='.($page-1).'">이전</a>';
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    $class = ($i == $page) ? 'current' : '';
                    echo '<a href="?tab=comments&page='.$i.'" class="'.$class.'">'.$i.'</a>';
                }
                
                if ($page < $total_page) {
                    echo '<a href="?tab=comments&page='.($page+1).'">다음</a>';
                }
                ?>
            </div>
            <?php } ?>
            
            <?php } else { ?>
            <div class="empty-content">
                <p>작성한 댓글이 없습니다.</p>
            </div>
            <?php } ?>
        </div>

        <?php } else if ($tab == 'favorites') { ?>
        <!-- 관심글 탭 -->
        <div id="favorites-tab">
            <?php
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = 15;
            $offset = ($page - 1) * $limit;

            // 전체 관심글 수
            $total_count = get_member_like_count($member['mb_id']);
            $total_page = ceil($total_count / $limit);

            // 관심글 목록
            $likes = get_member_likes($member['mb_id'], $offset, $limit);

            if (!empty($likes)) {
            ?>
            <table class="posts-table">
                <thead>
                    <tr>
                        <th width="150">게시판</th>
                        <th>제목</th>
                        <th width="120">관심 등록일</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($likes as $like) { ?>
                    <tr>
                        <td>
                            <span class="post-board"><?php echo $like['board']['bo_subject']; ?></span>
                        </td>
                        <td>
                            <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $like['bo_table']; ?>&wr_id=<?php echo $like['wr_id']; ?>"
                               class="post-title">
                                <?php echo $like['write']['wr_subject'] ? $like['write']['wr_subject'] : cut_str(strip_tags($like['write']['wr_content']), 50); ?>
                            </a>
                        </td>
                        <td>
                            <span class="post-date"><?php echo date('Y-m-d', strtotime($like['liked_datetime'])); ?></span>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

            <?php if ($total_page > 1) { ?>
            <div class="pagination">
                <?php
                $start_page = max(1, $page - 5);
                $end_page = min($total_page, $page + 5);

                if ($page > 1) {
                    echo '<a href="?tab=favorites&page='.($page-1).'">이전</a>';
                }

                for ($i = $start_page; $i <= $end_page; $i++) {
                    $class = ($i == $page) ? 'current' : '';
                    echo '<a href="?tab=favorites&page='.$i.'" class="'.$class.'">'.$i.'</a>';
                }

                if ($page < $total_page) {
                    echo '<a href="?tab=favorites&page='.($page+1).'">다음</a>';
                }
                ?>
            </div>
            <?php } ?>

            <?php } else { ?>
            <div class="empty-content">
                <p>관심글이 없습니다.</p>
                <p style="font-size: 0.9em; margin-top: 10px;">게시글의 <i class="fa-regular fa-star"></i> 버튼을 눌러 관심글로 등록하세요.</p>
            </div>
            <?php } ?>
        </div>

        <?php } else if ($tab == 'notifications') { ?>
        <!-- 알림 탭 -->
        <div id="notifications-tab">
            <?php include_once(G5_SKIN_PATH.'/main/member/notification_list.php'); ?>
        </div>
        <?php } ?>
    </div>
    <?php endif; ?>
</div>

<?php
include_once(G5_PATH.'/tail.php');
?>
