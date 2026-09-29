<?php
if (!defined('_GNUBOARD_')) exit;

$list_count = (is_array($list) && $list) ? count($list) : 0;
$is_all_latest = ($bo_subject == '전체 최신글');
?>

<style>
.terminal-latest {
    font-family: var(--content-font-family, Pretendard);
}

.terminal-latest .latest-item {
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    padding: 10px 0;
    transition: all 0.2s;
}

.terminal-latest .latest-item:last-child {
    border-bottom: none;
}

.terminal-latest .latest-item:hover {
    background: rgb(from var(--accent-color) r g b / 0.05);
    padding-left: 8px;
}

.terminal-latest .latest-link {
    display: flex;
    flex-direction: column;
    gap: 4px;
    text-decoration: none;
    color: inherit;
}

.terminal-latest .latest-subject {
    font-size: 11px;
    font-weight: 600;
    color: var(--title-font-color);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 0.2s;
}

.terminal-latest .latest-item:hover .latest-subject {
    color: var(--accent-color, #00f0ff);
}

.terminal-latest .board-name {
    display: inline-block;
    font-size: 9px;
    font-weight: 700;
    color: var(--accent-color, #00f0ff);
    background: rgb(from var(--accent-color) r g b / 0.1);
    padding: 2px 6px;
    margin-right: 6px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.terminal-latest .latest-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 9px;
    font-weight: 700;
    color: var(--primary-color);
    padding-left: 5px;
    text-transform: uppercase;
}

.terminal-latest .latest-item:hover .latest-date {
    color: var(--accent-color);
}

.terminal-latest .latest-empty {
    text-align: center;
    padding: 30px 10px;
    color: var(--gray-600, #666);
    font-size: 11px;
}

.terminal-latest .comment-count {
    font-size: 9px;
    color: var(--warning-color, #ffc107);
    margin-left: 4px;
}

.terminal-latest .latest-content {
    display: block;
    font-size: 10px;
    color: var(--content-font-color);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 2px;
    padding-left: 2px;
}
</style>

<div class="terminal-latest">
    <?php for ($i=0; $i<$list_count; $i++) { ?>
    <div class="latest-item">
        <a href="<?php echo $list[$i]['href']; ?>" class="latest-link">
            <span class="latest-subject">
                <?php if (!empty($list[$i]['bo_subject'])) { ?>
                    <span class="board-name"><?php echo $list[$i]['bo_subject']; ?></span>
                <?php } elseif (isset($bo_subject) && $bo_subject) { ?>
                    <span class="board-name"><?php echo $bo_subject; ?></span>
                <?php } ?>
                <?php echo $list[$i]['subject']; ?>
                <?php if ($list[$i]['comment_cnt']) echo '<span class="comment-count">+'.$list[$i]['comment_cnt'].'</span>'; ?>
            </span>
            <?php if (!empty($list[$i]['content'])): ?>
            <span class="latest-content"><?php echo htmlspecialchars($list[$i]['content']); ?></span>
            <?php endif; ?>
            <div class="latest-meta">
                <span class="latest-author"><?php echo $list[$i]['wr_name']; ?></span>
                <span class="latest-date"><?php echo date('m.d H:i', strtotime($list[$i]['datetime'])); ?></span>
            </div>
        </a>
    </div>
    <?php } ?>

    <?php if ($list_count == 0) { ?>
    <div class="latest-empty">NO_DATA_FOUND</div>
    <?php } ?>
</div>
