<?php
if (!defined('_GNUBOARD_')) exit;
include_once G5_LIB_PATH.'/pair_board.lib.php';
$pair_design = pair_board_design_context();
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/pair-board.css?v='.@filemtime(G5_PATH.'/css/pair-board.css').'">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css?v='.@filemtime($board_skin_path.'/style.css').'">', 1);
?>
<section class="pair-board pair-gallery" data-theme="<?php echo pair_board_escape($pair_design['theme']); ?>" style="<?php echo pair_board_escape(pair_board_style_attribute($pair_design)); ?>">
    <header class="pair-board-head">
        <div><h1><?php echo pair_board_escape($board['bo_subject']); ?></h1><p><?php echo number_format($total_count); ?> ARTWORKS</p></div>
        <div class="pair-board-actions">
            <?php if ($admin_href) { ?><a class="pair-board-button" href="<?php echo $admin_href; ?>" aria-label="게시판 설정">⚙</a><?php } ?>
            <?php if ($write_href) { ?><a class="pair-board-button is-primary" href="<?php echo $write_href; ?>">사진 올리기</a><?php } ?>
        </div>
    </header>
    <?php if ($is_category) { ?><nav class="pair-gallery-categories" aria-label="분류"><?php echo $category_option; ?></nav><?php } ?>
    <div class="pair-gallery-grid" id="pair-gallery-grid">
    <?php for ($i=0; $i<count($list); $i++) {
        $item = $list[$i];
        $image = pair_board_first_image($bo_table, $item['wr_id']);
        $description = trim(strip_tags((string)($item['list_content'] ?? $item['wr_content'] ?? '')));
        $credit = trim((string)($item['wr_1'] ?? ''));
        $art_date = trim((string)($item['wr_2'] ?? ''));
        $alt = trim((string)($item['wr_3'] ?? '')) ?: ($image['alt'] ?: $item['wr_subject']);
        $wide = (($item['wr_4'] ?? '') === 'wide');
        $can_edit = $is_admin || ($is_member && !empty($item['mb_id']) && $member['mb_id'] === $item['mb_id']);
        $edit_url = G5_BBS_URL.'/write.php?w=u&amp;bo_table='.rawurlencode($bo_table).'&amp;wr_id='.(int)$item['wr_id'].'&amp;page='.(int)$page;
    ?>
        <article class="pair-gallery-item pair-board-card<?php echo $wide?' is-wide':''; ?>" data-gallery-item>
            <?php if ($image['url'] !== '') { ?>
            <button type="button" class="pair-gallery-open" data-src="<?php echo pair_board_escape($image['url']); ?>" data-title="<?php echo pair_board_escape($item['wr_subject']); ?>" data-description="<?php echo pair_board_escape($description); ?>" data-meta="<?php echo pair_board_escape(trim(implode(' · ',array_filter(array($credit,$art_date))))); ?>">
                <img src="<?php echo pair_board_escape($image['url']); ?>" alt="<?php echo pair_board_escape($alt); ?>" loading="lazy">
                <span class="pair-gallery-overlay"><strong><?php echo pair_board_escape($item['wr_subject']); ?></strong><?php if ($description !== '') { ?><span><?php echo pair_board_escape($description); ?></span><?php } ?><small><?php echo pair_board_escape(trim(implode(' · ',array_filter(array($credit,$art_date))))); ?></small></span>
            </button>
            <?php } else { ?><div class="pair-board-empty">이미지를 등록해 주세요.</div><?php } ?>
            <?php if ($can_edit) { ?><a class="pair-gallery-settings" href="<?php echo $edit_url; ?>" aria-label="작품 정보 수정">•••</a><?php } ?>
        </article>
    <?php } ?>
    </div>
    <?php if (count($list) === 0) { ?><div class="pair-board-card pair-board-empty">아직 등록된 작품이 없습니다.</div><?php } ?>
    <div class="pair-board-pagination"><?php echo $write_pages; ?></div>
</section>
<dialog class="pair-gallery-dialog" id="pair-gallery-dialog" aria-label="작품 크게 보기">
    <button type="button" class="pair-gallery-close" aria-label="닫기">×</button>
    <div class="pair-gallery-dialog-media"><img src="" alt=""></div>
    <div class="pair-gallery-dialog-copy"><strong></strong><p></p><small></small></div>
</dialog>
<script>
(function(){
  var grid=document.getElementById('pair-gallery-grid'),dialog=document.getElementById('pair-gallery-dialog');if(!grid||!dialog)return;
  function place(item){var img=item.querySelector('img');if(!img||!img.naturalWidth)return;var width=item.getBoundingClientRect().width,height=width*img.naturalHeight/img.naturalWidth;item.style.setProperty('--gallery-row-span',Math.ceil((height+12)/8));}
  grid.querySelectorAll('[data-gallery-item] img').forEach(function(img){if(img.complete)place(img.closest('[data-gallery-item]'));else img.addEventListener('load',function(){place(img.closest('[data-gallery-item]'));});});
  addEventListener('resize',function(){grid.querySelectorAll('[data-gallery-item]').forEach(place);});
  grid.addEventListener('click',function(event){var button=event.target.closest('.pair-gallery-open');if(!button)return;var image=dialog.querySelector('img');image.src=button.dataset.src;image.alt=button.dataset.title||'';dialog.querySelector('strong').textContent=button.dataset.title||'';dialog.querySelector('p').textContent=button.dataset.description||'';dialog.querySelector('small').textContent=button.dataset.meta||'';dialog.showModal();document.documentElement.classList.add('pair-gallery-modal-open');});
  function close(){dialog.close();document.documentElement.classList.remove('pair-gallery-modal-open');}
  dialog.querySelector('.pair-gallery-close').addEventListener('click',close);dialog.addEventListener('click',function(e){if(e.target===dialog)close();});dialog.addEventListener('close',function(){document.documentElement.classList.remove('pair-gallery-modal-open');});
})();
</script>
