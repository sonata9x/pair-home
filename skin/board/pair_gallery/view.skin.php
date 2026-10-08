<?php
if (!defined('_GNUBOARD_')) exit;
include_once G5_LIB_PATH.'/pair_board.lib.php';
$pair_design = pair_board_design_context();
$image = pair_board_first_image($bo_table, $wr_id);
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/pair-board.css?v='.@filemtime(G5_PATH.'/css/pair-board.css').'">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css?v='.@filemtime($board_skin_path.'/style.css').'">', 1);
?>
<article class="pair-board pair-gallery-view" data-theme="<?php echo pair_board_escape($pair_design['theme']); ?>" style="<?php echo pair_board_escape(pair_board_style_attribute($pair_design)); ?>">
    <header class="pair-board-head"><div><h1><?php echo pair_board_escape($view['wr_subject']); ?></h1><p><?php echo pair_board_escape(trim(implode(' · ',array_filter(array($view['wr_1'] ?? '',$view['wr_2'] ?? ''))))); ?></p></div><div class="pair-board-actions"><a class="pair-board-button" href="<?php echo $list_href; ?>">목록</a><?php if ($update_href) { ?><a class="pair-board-button" href="<?php echo $update_href; ?>">•••</a><?php } ?></div></header>
    <div class="pair-gallery-view-image pair-board-card"><?php if ($image['url']) { ?><img src="<?php echo pair_board_escape($image['url']); ?>" alt="<?php echo pair_board_escape(($view['wr_3'] ?? '') ?: $view['wr_subject']); ?>"><?php } ?></div>
    <?php if (trim(strip_tags((string)$view['wr_content'])) !== '') { ?><div class="pair-gallery-view-copy pair-board-card"><?php echo nl2br(pair_board_escape(strip_tags($view['wr_content']))); ?></div><?php } ?>
</article>
