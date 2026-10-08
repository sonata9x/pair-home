<?php
if (!defined('_GNUBOARD_')) exit;
include_once G5_LIB_PATH.'/pair_board.lib.php';
$pair_design=pair_board_design_context();$card=pair_board_first_image($bo_table,$wr_id);$tracks=pair_log_get_tracks($bo_table,$wr_id);$waiting_key=trim((string)($view['wr_6'] ?? ''));$waiting=$tracks[$waiting_key] ?? null;
$srcdoc=pair_log_build_srcdoc((string)$view['wr_content'],$tracks,$pair_design['accent']);
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/pair-board.css?v='.@filemtime(G5_PATH.'/css/pair-board.css').'">',0);add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css?v='.@filemtime($board_skin_path.'/style.css').'">',1);
?>
<article class="pair-board pair-log-view" data-theme="<?php echo pair_board_escape($pair_design['theme']); ?>" style="<?php echo pair_board_escape(pair_board_style_attribute($pair_design)); ?>">
    <header class="pair-board-head"><div><h1><?php echo pair_board_escape($view['wr_subject']); ?></h1></div><div class="pair-board-actions"><a class="pair-board-button" href="<?php echo $list_href; ?>">목록</a><?php if ($update_href) { ?><a class="pair-board-button" href="<?php echo $update_href; ?>" aria-label="로그 설정">•••</a><?php } ?></div></header>
    <?php if ($card['url']) { ?><figure class="pair-log-session-card pair-board-card"><img src="<?php echo pair_board_escape($card['url']); ?>" alt="<?php echo pair_board_escape($card['alt']); ?>"></figure><?php } ?>
    <?php if (trim((string)($view['wr_5'] ?? ''))!=='') { ?><section class="pair-log-overview pair-board-card"><h2>OVERVIEW</h2><p><?php echo nl2br(pair_board_escape($view['wr_5'])); ?></p></section><?php } ?>
    <?php if ($waiting) { ?><section class="pair-log-waiting pair-board-card"><div><small>WAITING BGM</small><strong><?php echo pair_board_escape($waiting['title']); ?></strong><?php if ($waiting['artist']) { ?><span><?php echo pair_board_escape($waiting['artist']); ?></span><?php } ?></div><audio controls preload="metadata" src="<?php echo pair_board_escape($waiting['audio_url']); ?>"></audio></section><?php } ?>
    <?php if ($tracks) { ?><details class="pair-log-playlist pair-board-card"><summary>PLAYLIST · <?php echo count($tracks); ?></summary><ol><?php foreach ($tracks as $track) { ?><li><span><strong><?php echo pair_board_escape($track['title']); ?></strong><small><?php echo pair_board_escape($track['artist']); ?></small></span><audio controls preload="none" src="<?php echo pair_board_escape($track['audio_url']); ?>"></audio></li><?php } ?></ol></details><?php } ?>
    <iframe class="pair-log-document pair-board-card" id="pair-log-document" sandbox="allow-scripts allow-popups" title="<?php echo pair_board_escape($view['wr_subject']); ?> 로그 본문" srcdoc="<?php echo pair_board_escape($srcdoc); ?>"></iframe>
</article>
<script>
(function(){var frame=document.getElementById('pair-log-document'),outside=[].slice.call(document.querySelectorAll('.pair-log-view > :not(.pair-log-document) audio'));function pauseOutside(except){outside.forEach(function(audio){if(audio!==except)audio.pause()})}outside.forEach(function(audio){audio.addEventListener('play',function(){pauseOutside(audio);if(frame.contentWindow)frame.contentWindow.postMessage({type:'pair-log-pause'},'*')})});addEventListener('message',function(event){if(event.source!==frame.contentWindow||!event.data)return;if(event.data.type==='pair-log-size'){var height=Math.max(240,Math.min(100000,Number(event.data.height)||0));frame.style.height=height+'px'}if(event.data.type==='pair-log-play')pauseOutside(null)});})();
</script>
