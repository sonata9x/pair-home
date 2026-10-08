<?php
if (!defined('_GNUBOARD_')) exit;
include_once G5_LIB_PATH.'/pair_board.lib.php';
$pair_design = pair_board_design_context();
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/pair-board.css?v='.@filemtime(G5_PATH.'/css/pair-board.css').'">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css?v='.@filemtime($board_skin_path.'/style.css').'">', 1);
?>
<section class="pair-board pair-log-list" data-theme="<?php echo pair_board_escape($pair_design['theme']); ?>" style="<?php echo pair_board_escape(pair_board_style_attribute($pair_design)); ?>">
    <header class="pair-board-head"><div><h1><?php echo pair_board_escape($board['bo_subject']); ?></h1><p><?php echo number_format($total_count); ?> SESSION LOGS</p></div><div class="pair-board-actions"><?php if ($admin_href) { ?><a class="pair-board-button" href="<?php echo $admin_href; ?>" aria-label="게시판 설정">⚙</a><?php } ?><?php if ($write_href) { ?><a class="pair-board-button is-primary" href="<?php echo $write_href; ?>">로그 올리기</a><?php } ?></div></header>
    <div class="pair-log-grid">
    <?php for ($i=0; $i<count($list); $i++) {
        $item=$list[$i];$image=pair_board_first_image($bo_table,$item['wr_id']);
        $focus=array_map('floatval',explode('|',(string)($item['wr_9'] ?? '1|50|50')));$zoom=max(1,min(3,$focus[0] ?? 1));$x=max(0,min(100,$focus[1] ?? 50));$y=max(0,min(100,$focus[2] ?? 50));
        $can_edit=$is_admin||($is_member&&!empty($item['mb_id'])&&$member['mb_id']===$item['mb_id']);
        $edit=G5_BBS_URL.'/write.php?w=u&amp;bo_table='.rawurlencode($bo_table).'&amp;wr_id='.(int)$item['wr_id'].'&amp;page='.(int)$page;
    ?>
        <article class="pair-log-card pair-board-card">
            <a class="pair-log-card-link" href="<?php echo $item['href']; ?>">
                <span class="pair-log-thumb"><?php if ($image['url']) { ?><img src="<?php echo pair_board_escape($image['url']); ?>" alt="" loading="lazy" style="object-position:<?php echo $x; ?>% <?php echo $y; ?>%;transform:scale(<?php echo $zoom; ?>);transform-origin:<?php echo $x; ?>% <?php echo $y; ?>%"><?php } else { ?><span>NO SESSION CARD</span><?php } ?></span>
                <span class="pair-log-card-copy"><strong><?php echo pair_board_escape($item['wr_subject']); ?></strong><span><?php echo pair_board_escape($item['wr_1'] ?? ''); ?></span><small><?php echo pair_board_escape(trim(implode(' · ',array_filter(array($item['wr_2'] ?? '',$item['wr_3'] ?? ''))))); ?></small></span>
            </a>
            <?php if ($can_edit) { ?><a class="pair-log-settings" href="<?php echo $edit; ?>" aria-label="로그 설정">•••</a><?php } ?>
        </article>
    <?php } ?>
    </div>
    <?php if (count($list)===0) { ?><div class="pair-board-card pair-board-empty">아직 등록된 로그가 없습니다.</div><?php } ?>
    <div class="pair-board-pagination"><?php echo $write_pages; ?></div>
</section>
