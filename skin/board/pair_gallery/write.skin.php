<?php
if (!defined('_GNUBOARD_')) exit;
include_once G5_LIB_PATH.'/pair_board.lib.php';
$pair_design = pair_board_design_context();
$existing = $w === 'u' ? pair_board_first_image($bo_table, $wr_id) : array('url'=>'','alt'=>'');
add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/pair-board.css?v='.@filemtime(G5_PATH.'/css/pair-board.css').'">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css?v='.@filemtime($board_skin_path.'/style.css').'">', 1);
?>
<section class="pair-board pair-gallery pair-gallery-write" data-theme="<?php echo pair_board_escape($pair_design['theme']); ?>" style="<?php echo pair_board_escape(pair_board_style_attribute($pair_design)); ?>">
<header class="pair-board-head"><div><h1><?php echo $w==='u'?'작품 정보 수정':'새 작품'; ?></h1><p>이미지 비율은 목록과 팝업에서 그대로 유지됩니다.</p></div></header>
<form class="pair-board-form pair-board-card" name="fwrite" id="fwrite" action="<?php echo $action_url; ?>" onsubmit="return pairGallerySubmit(this)" method="post" enctype="multipart/form-data" autocomplete="off">
    <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>"><input type="hidden" name="w" value="<?php echo $w; ?>"><input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>"><input type="hidden" name="wr_id" value="<?php echo $wr_id; ?>"><input type="hidden" name="sca" value="<?php echo $sca; ?>"><input type="hidden" name="page" value="<?php echo $page; ?>">
    <?php if ($is_name) { ?><label class="pair-board-field"><span>이름</span><input type="text" name="wr_name" value="<?php echo pair_board_escape($name); ?>" required maxlength="255"></label><?php } ?>
    <?php if ($is_password) { ?><label class="pair-board-field"><span>비밀번호</span><input type="password" name="wr_password" <?php echo $password_required; ?> maxlength="255"></label><?php } ?>
    <label class="pair-board-field"><span>작품명</span><input type="text" name="wr_subject" value="<?php echo pair_board_escape($subject); ?>" required maxlength="255"></label>
    <label class="pair-board-field"><span>작품 설명</span><textarea name="wr_content" maxlength="3000"><?php echo pair_board_escape($content); ?></textarea></label>
    <div class="pair-board-form-grid">
        <label class="pair-board-field"><span>작가·커미션 크레디트</span><input type="text" name="wr_1" value="<?php echo pair_board_escape($write['wr_1'] ?? ''); ?>" maxlength="255"></label>
        <label class="pair-board-field"><span>작품 날짜</span><input type="text" name="wr_2" value="<?php echo pair_board_escape($write['wr_2'] ?? ''); ?>" maxlength="80" placeholder="2026.10.08"></label>
        <label class="pair-board-field"><span>대체 텍스트</span><input type="text" name="wr_3" value="<?php echo pair_board_escape($write['wr_3'] ?? ''); ?>" maxlength="255"></label>
        <label class="pair-board-field"><span>목록 크기</span><select name="wr_4"><option value="normal">일반</option><option value="wide"<?php echo ($write['wr_4'] ?? '')==='wide'?' selected':''; ?>>넓게</option></select></label>
    </div>
    <label class="pair-board-field"><span>작품 이미지</span><input type="file" name="bf_file[]" accept="image/jpeg,image/png,image/webp,image/gif"<?php echo $w===''?' required':''; ?>><input type="text" name="bf_content[]" value="<?php echo pair_board_escape($existing['alt']); ?>" placeholder="첨부 이미지 캡션"></label>
    <?php if ($existing['url'] !== '') { ?><div class="pair-gallery-current"><img src="<?php echo pair_board_escape($existing['url']); ?>" alt=""><label><input type="checkbox" name="bf_file_del[0]" value="1"> 기존 이미지 삭제</label></div><?php } ?>
    <?php if ($is_category) { ?><label class="pair-board-field"><span>분류</span><select name="ca_name" required><option value="">선택</option><?php echo $category_option; ?></select></label><?php } ?>
    <?php if ($is_secret) { ?><label class="pair-board-field"><span>공개 설정</span><select name="secret"><option value="">전체 공개</option><option value="secret"<?php echo strpos((string)($write['wr_option'] ?? ''),'secret')!==false?' selected':''; ?>>비밀글</option><?php if ($is_member) { ?><option value="member"<?php echo strpos((string)($write['wr_option'] ?? ''),'member')!==false?' selected':''; ?>>멤버 공개</option><?php } ?></select></label><?php } ?>
    <?php if ($is_use_captcha) echo $captcha_html; ?>
    <div class="pair-board-actions"><a class="pair-board-button" href="<?php echo get_pretty_url($bo_table); ?>">취소</a><button class="pair-board-button is-primary" type="submit">저장</button></div>
</form>
</section>
<script>function pairGallerySubmit(form){var button=form.querySelector('[type=submit]');button.disabled=true;return true;}</script>
