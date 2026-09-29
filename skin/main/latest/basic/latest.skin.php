<?php
if (!defined('_GNUBOARD_')) exit;

echo '<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">';

$list_count = (is_array($list) && $list) ? count($list) : 0;
$is_all_latest = ($bo_subject == '전체 최신글');
$display_title = $is_all_latest ? 'All Latest' : $bo_subject;
?>

<div class="basic-latest">
    <div class="latest-header">
        <h3 class="latest-title">
            <?php if ($is_all_latest): ?>
                <span><?php echo $display_title ?></span>
            <?php else: ?>
                <a href="<?php echo get_pretty_url($bo_table); ?>"><?php echo $display_title ?></a>
            <?php endif; ?>
        </h3>
        <?php if (!$is_all_latest) { ?>
            <a href="<?php echo get_pretty_url($bo_table); ?>" class="latest-more">더보기 →</a>
        <?php } ?>
    </div>

    <ul class="latest-list">
        <?php for ($i=0; $i<$list_count; $i++) {
            // 날짜 포맷 처리 (오늘이면 시간만, 이전이면 월.일)
            $datetime = strtotime($list[$i]['datetime']);
            $today = strtotime(date('Y-m-d'));

            if ($datetime >= $today) {
                $date_str = date('H:i', $datetime);
            } else {
                $date_str = date('m.d', $datetime);
            }
        ?>
        <li class="latest-item">
            <a href="<?php echo $list[$i]['href']; ?>" class="latest-link">
                <?php if ($is_all_latest) { ?>
                    <span class="board-badge"><?php echo $list[$i]['bo_subject']; ?></span>
                <?php } ?>
                <span class="item-subject">
                    <?php echo $list[$i]['subject']; ?>
                    <?php if ($list[$i]['comment_cnt']) {
                        $comment_num = str_replace(['(', ')'], '', $list[$i]['comment_cnt']);
                        echo '<span class="comment-count"><i class="fa-solid fa-message"></i>'.$comment_num.'</span>';
                    } ?>
                </span>
                <?php if (!$is_all_latest && isset($list[$i]['wr_name'])) { ?>
                    <span class="item-author"><?php echo $list[$i]['wr_name']; ?></span>
                    <!-- <span class="item-separator">/</span> -->
                <?php } ?>
                <?php if (!$is_all_latest) { ?>
                    <span class="date-badge"><?php echo $date_str; ?></span>
                <?php } else { ?>
                    <span class="item-date"><?php echo $date_str; ?></span>
                <?php } ?>
            </a>
        </li>
        <?php } ?>

        <?php if ($list_count == 0) { ?>
        <li class="latest-empty">게시물이 없습니다.</li>
        <?php } ?>
    </ul>
</div>
