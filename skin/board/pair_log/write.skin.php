<?php
if (!defined('_GNUBOARD_')) exit;
include_once G5_LIB_PATH.'/pair_board.lib.php';
$pair_design=pair_board_design_context();
$tracks=$w==='u'?pair_log_get_tracks($bo_table,$wr_id):array();
$track_text=pair_log_tracks_to_text($tracks);
$card=$w==='u'?pair_board_first_image($bo_table,$wr_id):array('url'=>'','alt'=>'');
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/pair-board.css?v='.@filemtime(G5_PATH.'/css/pair-board.css').'">',0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css?v='.@filemtime($board_skin_path.'/style.css').'">',1);
?>
<section class="pair-board pair-log-write" data-theme="<?php echo pair_board_escape($pair_design['theme']); ?>" style="<?php echo pair_board_escape(pair_board_style_attribute($pair_design)); ?>">
<header class="pair-board-head"><div><h1><?php echo $w==='u'?'로그 설정':'새 로그 업로드'; ?></h1><p><?php echo $w==='u'?'HTML 로그 원문은 수정되지 않습니다.':'HTML 파일과 세션 정보를 한 번에 등록합니다.'; ?></p></div></header>
<form class="pair-board-form pair-board-card" name="fwrite" id="fwrite" action="<?php echo $action_url; ?>" onsubmit="return pairLogSubmit(this)" method="post" enctype="multipart/form-data" autocomplete="off">
    <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>"><input type="hidden" name="w" value="<?php echo $w; ?>"><input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>"><input type="hidden" name="wr_id" value="<?php echo $wr_id; ?>"><input type="hidden" name="page" value="<?php echo $page; ?>"><input type="hidden" name="pair_log_settings" value="1"><input type="hidden" name="wr_content" value="PAIR LOG HTML"><input type="hidden" name="wr_8" value="1">
    <?php if ($is_name) { ?><label class="pair-board-field"><span>이름</span><input type="text" name="wr_name" value="<?php echo pair_board_escape($name); ?>" required maxlength="255"></label><?php } ?>
    <?php if ($is_password) { ?><label class="pair-board-field"><span>비밀번호</span><input type="password" name="wr_password" <?php echo $password_required; ?> maxlength="255"></label><?php } ?>
    <?php if ($w==='') { ?><div class="pair-board-form-grid">
        <label class="pair-board-field"><span>시나리오명</span><input type="text" name="wr_subject" value="<?php echo pair_board_escape($subject); ?>" required maxlength="255"></label>
        <label class="pair-board-field"><span>라이터</span><input type="text" name="wr_1" value="<?php echo pair_board_escape($write['wr_1'] ?? ''); ?>" maxlength="255"></label>
        <label class="pair-board-field"><span>플레이타임</span><input type="text" name="wr_2" value="<?php echo pair_board_escape($write['wr_2'] ?? ''); ?>" maxlength="120" placeholder="약 6시간"></label>
        <label class="pair-board-field"><span>플레이 날짜</span><input type="text" name="wr_3" value="<?php echo pair_board_escape($write['wr_3'] ?? ''); ?>" maxlength="120" placeholder="2026.10.08"></label>
    </div><?php } else { ?><input type="hidden" name="wr_subject" value="<?php echo pair_board_escape($subject); ?>"><div class="pair-log-locked-meta"><strong><?php echo pair_board_escape($subject); ?></strong><span><?php echo pair_board_escape(trim(implode(' · ',array_filter(array($write['wr_1'] ?? '',$write['wr_2'] ?? '',$write['wr_3'] ?? ''))))); ?></span><small>목록 정보와 HTML 원문은 최초 등록 후 변경하지 않습니다.</small></div><?php } ?>
    <label class="pair-board-field"><span>개요</span><textarea name="wr_5" maxlength="3000"><?php echo pair_board_escape($write['wr_5'] ?? ''); ?></textarea></label>
    <label class="pair-board-field"><span>세션카드 원본</span><input type="file" name="bf_file[]" accept="image/jpeg,image/png,image/webp,image/gif"><input type="text" name="bf_content[]" value="<?php echo pair_board_escape($card['alt']); ?>" placeholder="세션카드 대체 텍스트"></label>
    <?php if ($card['url']) { ?><div class="pair-log-current-card"><img src="<?php echo pair_board_escape($card['url']); ?>" alt=""><label><input type="checkbox" name="bf_file_del[0]" value="1"> 기존 세션카드 삭제</label></div><?php } ?>
    <?php $focus=array_map('floatval',explode('|',(string)($write['wr_9'] ?? '1|50|50'))); ?>
    <fieldset class="pair-log-crop">
        <legend>목록 16:9 썸네일 크롭</legend>
        <div class="pair-log-crop-preview" data-crop-preview><?php if ($card['url']) { ?><img src="<?php echo pair_board_escape($card['url']); ?>" alt="목록 크롭 미리보기"><?php } else { ?><span>세션카드를 선택하면 미리보기가 표시됩니다.</span><?php } ?></div>
        <div class="pair-log-crop-controls"><label>확대 <output><?php echo $focus[0] ?? 1; ?>×</output><input type="range" min="1" max="3" step=".05" value="<?php echo $focus[0] ?? 1; ?>" data-crop="zoom"></label><label>가로 초점 <output><?php echo $focus[1] ?? 50; ?>%</output><input type="range" min="0" max="100" step="1" value="<?php echo $focus[1] ?? 50; ?>" data-crop="x"></label><label>세로 초점 <output><?php echo $focus[2] ?? 50; ?>%</output><input type="range" min="0" max="100" step="1" value="<?php echo $focus[2] ?? 50; ?>" data-crop="y"></label></div>
        <input type="hidden" name="wr_9" value="<?php echo pair_board_escape($write['wr_9'] ?? '1|50|50'); ?>">
    </fieldset>
    <?php if ($w==='') { ?><label class="pair-board-field"><span>HTML 로그 파일</span><input type="file" name="pair_log_html" accept=".html,.htm,text/html" required><p class="pair-board-help">최대 10MB. 스크립트·폼·iframe은 제거되며 문서 구조와 스타일은 격리된 본문 안에서 유지됩니다.</p></label><?php } ?>
    <label class="pair-board-field"><span>플레이리스트</span><textarea name="pair_log_tracks" class="pair-log-tracks" spellcheck="false" placeholder="chapter-1|곡명|아티스트|음원 URL|표지 URL"><?php echo pair_board_escape($track_text); ?></textarea><p class="pair-board-help">한 줄에 `키|곡명|아티스트|음원 URL|표지 URL`. HTML의 `&lt;!-- PAIR_BGM:chapter-1 --&gt;` 위치에 플레이어가 들어갑니다.</p></label>
    <label class="pair-board-field"><span>대기 BGM 트랙 키</span><input type="text" name="wr_6" value="<?php echo pair_board_escape($write['wr_6'] ?? ''); ?>" maxlength="80" placeholder="waiting"><p class="pair-board-help">플레이리스트 첫 칸의 키를 입력합니다. 비우면 대기 BGM을 표시하지 않습니다.</p></label>
    <?php if ($is_category) { ?><label class="pair-board-field"><span>분류</span><select name="ca_name" required><option value="">선택</option><?php echo $category_option; ?></select></label><?php } ?>
    <?php if ($is_use_captcha) echo $captcha_html; ?>
    <div class="pair-board-actions"><a class="pair-board-button" href="<?php echo get_pretty_url($bo_table); ?>">취소</a><button class="pair-board-button is-primary" type="submit"><?php echo $w==='u'?'설정 저장':'로그 등록'; ?></button></div>
</form></section>
<script>
(function(){var form=document.getElementById('fwrite'),hidden=form.querySelector('[name=wr_9]'),inputs=form.querySelectorAll('[data-crop]'),preview=form.querySelector('[data-crop-preview]'),file=form.querySelector('[name="bf_file[]"]'),objectUrl='';function sync(){var values={zoom:1,x:50,y:50};inputs.forEach(function(input){values[input.dataset.crop]=input.value;input.parentNode.querySelector('output').textContent=input.value+(input.dataset.crop==='zoom'?'×':'%')});hidden.value=[values.zoom,values.x,values.y].join('|');var image=preview.querySelector('img');if(image){image.style.objectPosition=values.x+'% '+values.y+'%';image.style.transform='scale('+values.zoom+')';image.style.transformOrigin=values.x+'% '+values.y+'%'}}inputs.forEach(function(input){input.addEventListener('input',sync)});file.addEventListener('change',function(){if(objectUrl)URL.revokeObjectURL(objectUrl);if(!file.files||!file.files[0])return;objectUrl=URL.createObjectURL(file.files[0]);preview.innerHTML='<img alt="목록 크롭 미리보기">';preview.querySelector('img').src=objectUrl;sync()});sync()})();
function pairLogSubmit(form){form.querySelector('[type=submit]').disabled=true;return true;}
</script>
