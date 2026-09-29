<?php
if (!defined('_GNUBOARD_')) exit;

// add_stylesheet('<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">', 0);
echo '<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">';

$list_count = (is_array($list) && $list) ? count($list) : 0;
$is_all_latest = ($bo_subject == '전체 최신글');
?>


<div class="sidebar-latest">
    <div class="latest-title">
        <?php if ($is_all_latest): ?>
            <span><?php echo $bo_subject ?></span>
        <?php else: ?>
            <a href="<?php echo get_pretty_url($bo_table); ?>"><?php echo $bo_subject ?></a>
        <?php endif; ?>
    </div>
    
    <ul class="latest-list">
        <?php for ($i=0; $i<$list_count; $i++) { ?>
        <li class="latest-item">
            <a href="<?php echo $list[$i]['href']; ?>" class="latest-link">
                <span class="latest-subject">
                    <?php if ($is_all_latest) { ?>
                        <span class="board-name"><?php echo $list[$i]['bo_subject']; ?></span>
                    <?php } ?>
                    <?php echo $list[$i]['subject']; ?>
                    <?php if (!$is_all_latest && $list[$i]['comment_cnt']) echo '<span class="comment-count">'.$list[$i]['comment_cnt'].'</span>'; ?>
                </span>
                <span class="latest-date"><?php echo date('m.d', strtotime($list[$i]['datetime'])); ?></span>
            </a>
        </li>
        <?php } ?>
        
        <?php if ($list_count == 0) { ?>
        <li class="latest-empty">게시물이 없습니다.</li>
        <?php } ?>
    </ul>
    
    <?php if (!$is_all_latest) { ?>
        <a href="<?php echo get_pretty_url($bo_table); ?>" class="latest-more">더보기</a>
    <?php } ?>

</div>
