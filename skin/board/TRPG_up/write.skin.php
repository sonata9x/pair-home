<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
echo '<link rel="stylesheet" href="'.$board_skin_url.'/write.css?v='.filemtime($board_skin_path.'/write.css').'">';
echo '<link rel="stylesheet" href="'.$board_skin_url.'/session.css">';
echo '<link rel="stylesheet" href="'.$board_skin_url.'/session.cclog.css">';
echo '<link rel="stylesheet" href="'.$board_skin_url.'/style.list.css?v='.filemtime($board_skin_path.'/style.list.css').'">';

// 세션카드 및 두상 이미지 파일 저장 경로 및 URL 설정
$trpg_img_path = G5_DATA_PATH . "/file/" . $bo_table;
$trpg_img_url  = G5_DATA_URL . "/file/" . $bo_table;

@mkdir($trpg_img_path, G5_DIR_PERMISSION);
@chmod($trpg_img_path, G5_DIR_PERMISSION);

$trpg_up_table = G5_TABLE_PREFIX . 'trpg_up';

// 수정 화면은 페이지 설정과 로그 본문 편집을 분리한다.
// 신규 작성에서는 기존처럼 설정과 본문을 한 화면에 모두 표시한다.
$trpg_edit_mode = isset($_REQUEST['trpg_edit_mode']) ? $_REQUEST['trpg_edit_mode'] : '';
if ($w === 'u' && !in_array($trpg_edit_mode, array('settings', 'content'), true)) {
    $trpg_edit_mode = 'settings';
}
$trpg_is_log_edit = ($w === 'u' && $trpg_edit_mode === 'content');
$trpg_is_page_settings_edit = ($w === 'u' && $trpg_edit_mode === 'settings');

$trpg_wr_kpc = isset($write['wr_kpc']) ? trim((string)$write['wr_kpc']) : '';
$trpg_wr_pc = isset($write['wr_pc']) ? trim((string)$write['wr_pc']) : '';

// 테이블 존재 여부 체크. DESC 쿼리 결과가 없으면 테이블이 없다는 의미입니다.
if (!sql_query("DESC {$trpg_up_table} ", false)) {
    // trpg_up 테이블이 없으면 생성합니다.
    sql_query("CREATE TABLE IF NOT EXISTS `{$trpg_up_table}` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `bo_table` varchar(255) NOT NULL DEFAULT '',
        `wr_id` int(11) NOT NULL DEFAULT '0',
        `image_type` varchar(50) NOT NULL DEFAULT '',  /* 카드, 캐릭터 이미지 타입 구분 */
        `image_url` varchar(255) NOT NULL DEFAULT '',    /* 이미지 URL 또는 경로 */
        `wr_type` varchar(20) NOT NULL DEFAULT 'upload',   /* 업로드 방식: 'upload' 또는 'url' */
        `img_use` int(11) NOT NULL DEFAULT '1',              /* 사용 여부 또는 추가 플래그 */
        `file_order` int(11) NOT NULL DEFAULT '0',       /* 파일 순서 */
        PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8", false);
}
?>

<?php
if (!$trpg_is_log_edit) {
    include_once($board_skin_path.'/write.skin.script.php'); // 페이지 설정 이미지 업로드
}

// 폰트 목록 가져오기
$fonts = [];
if (function_exists('get_font_options_for_select')) {
    $fonts = get_font_options_for_select();
}

if (!function_exists('trpg_ticket_font_options')) {
    function trpg_ticket_font_options($fonts, $current)
    {
        if (empty($fonts)) {
            return '<option value="Pretendard"'.($current === 'Pretendard' || $current === '' ? ' selected' : '').'>Pretendard</option>';
        }

        $options = '';
        if ($current !== '' && !array_key_exists($current, $fonts)) {
            $options .= '<option value="'.htmlspecialchars($current, ENT_QUOTES).'" selected style="font-family:'.htmlspecialchars($current, ENT_QUOTES).'">'.htmlspecialchars($current, ENT_QUOTES).'</option>';
        }
        foreach ($fonts as $family => $name) {
            $selected = ((string)$current === (string)$family) ? ' selected' : '';
            $options .= '<option value="'.htmlspecialchars($family, ENT_QUOTES).'"'.$selected.' style="font-family:'.htmlspecialchars($family, ENT_QUOTES).'">'.htmlspecialchars($name, ENT_QUOTES).'</option>';
        }
        return $options;
    }
}

if (!function_exists('trpg_ticket_color_value')) {
    function trpg_ticket_color_value($value, $fallback)
    {
        $value = trim((string)$value);
        return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? strtoupper($value) : $fallback;
    }
}

$trpg_subject_font = !empty($write['wr_subject_font'])
    ? $write['wr_subject_font']
    : (!empty($write['wr_8']) ? $write['wr_8'] : 'Pretendard');
$trpg_english_title_font = !empty($write['wr_english_title_font']) ? $write['wr_english_title_font'] : 'Georgia';
$trpg_subtitle_font = !empty($write['wr_subtitle_font']) ? $write['wr_subtitle_font'] : 'Pretendard';
$trpg_catchphrase_font = !empty($write['wr_catchphrase_font']) ? $write['wr_catchphrase_font'] : 'Pretendard';
$trpg_subject_color = trpg_ticket_color_value(isset($write['wr_subject_color']) ? $write['wr_subject_color'] : '', '#1E3554');
$trpg_english_title_color = trpg_ticket_color_value(isset($write['wr_english_title_color']) ? $write['wr_english_title_color'] : '', '#1E3554');
$trpg_subtitle_color = trpg_ticket_color_value(isset($write['wr_subtitle_color']) ? $write['wr_subtitle_color'] : '', '#667085');
$trpg_catchphrase_color = trpg_ticket_color_value(isset($write['wr_catchphrase_color']) ? $write['wr_catchphrase_color'] : '', '#667085');
?>

<section class="trpg_up_write" id="trpg_up_write">

<!-- 게시물 작성/수정 시작 { -->

<form name="fwrite" id="fwrite" method="post" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
<input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
<input type="hidden" name="sca" value="<?php echo $sca ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="spt" value="<?php echo $spt ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="wr_order" value="<?php echo isset($write['wr_order']) ? $write['wr_order'] : '0'; ?>">
<input type="hidden" name="trpg_edit_mode" value="<?php echo htmlspecialchars($trpg_edit_mode, ENT_QUOTES); ?>">
<input type="hidden" id="cha-image-urls" name="cha-image-urls" value="[]">
<input type="hidden" id="normal-image-urls" name="normal-image-urls" value="[]">

<?php
$option = '';
$option_hidden = '';
?>

<div class="trpg-write-box">
    <?php if ($trpg_is_log_edit) { ?>
    <!-- 로그 본문 편집에서는 그누보드 공통 수정 처리에 필요한 기존 설정값을 보존한다. -->
    <?php if ($is_category) { ?>
    <input type="hidden" name="ca_name" value="<?php echo htmlspecialchars(isset($write['ca_name']) ? $write['ca_name'] : '', ENT_QUOTES); ?>">
    <?php } ?>
    <input type="hidden" name="wr_subject" value="<?php echo htmlspecialchars(isset($write['wr_subject']) ? $write['wr_subject'] : '', ENT_QUOTES); ?>">
    <input type="hidden" name="wr_title" value="<?php echo htmlspecialchars(isset($write['wr_title']) ? $write['wr_title'] : '', ENT_QUOTES); ?>">
    <input type="hidden" name="wr_url" value="<?php echo htmlspecialchars(isset($write['wr_url']) ? $write['wr_url'] : '', ENT_QUOTES); ?>">
    <input type="hidden" name="wr_1" value="<?php echo htmlspecialchars(isset($write['wr_1']) ? $write['wr_1'] : '', ENT_QUOTES); ?>">
    <input type="hidden" name="wr_2" value="<?php echo htmlspecialchars(isset($write['wr_2']) ? $write['wr_2'] : '', ENT_QUOTES); ?>">
    <input type="hidden" name="wr_3" value="<?php echo htmlspecialchars(isset($write['wr_3']) ? $write['wr_3'] : '', ENT_QUOTES); ?>">
    <input type="hidden" name="wr_4" value="<?php echo htmlspecialchars(isset($write['wr_4']) ? $write['wr_4'] : '', ENT_QUOTES); ?>">
    <textarea name="wr_5" hidden><?php echo htmlspecialchars(isset($write['wr_5']) ? $write['wr_5'] : '', ENT_QUOTES); ?></textarea>
    <input type="hidden" name="wr_7" value="<?php echo htmlspecialchars(isset($write['wr_7']) ? $write['wr_7'] : '', ENT_QUOTES); ?>">
    <input type="hidden" name="wr_8" value="<?php echo htmlspecialchars(isset($write['wr_8']) ? $write['wr_8'] : '', ENT_QUOTES); ?>">
    <input type="hidden" name="wr_9" value="<?php echo htmlspecialchars(isset($write['wr_9']) ? $write['wr_9'] : '', ENT_QUOTES); ?>">
    <input type="hidden" name="wr_10" value="<?php echo htmlspecialchars(isset($write['wr_10']) ? $write['wr_10'] : '', ENT_QUOTES); ?>">
    <input type="hidden" name="wr_link1" value="<?php echo htmlspecialchars(isset($write['wr_link1']) ? $write['wr_link1'] : '', ENT_QUOTES); ?>">
    <input type="hidden" name="wr_link2" value="<?php echo htmlspecialchars(isset($write['wr_link2']) ? $write['wr_link2'] : '', ENT_QUOTES); ?>">
    <input type="hidden" name="wr_secret" value="<?php echo htmlspecialchars(isset($write['wr_secret']) ? $write['wr_secret'] : '', ENT_QUOTES); ?>">
    <?php if (!empty($write['wr_adult'])) { ?><input type="hidden" name="wr_adult" value="1"><?php } ?>
    <?php if (!empty($write['wr_wide'])) { ?><input type="hidden" name="wr_wide" value="1"><?php } ?>
    <?php if (!empty($write['wr_plip'])) { ?><input type="hidden" name="wr_plip" value="1"><?php } ?>
    <?php if (isset($write['wr_option']) && strpos($write['wr_option'], 'secret') !== false) { ?>
    <input type="hidden" name="secret" value="secret">
    <?php } ?>
    <?php if (isset($write['wr_option']) && strpos($write['wr_option'], 'html2') !== false) { ?>
    <input type="hidden" name="html" value="html2">
    <?php } elseif (isset($write['wr_option']) && strpos($write['wr_option'], 'html1') !== false) { ?>
    <input type="hidden" name="html" value="html1">
    <?php } ?>
    <?php } elseif ($trpg_is_page_settings_edit) { ?>
    <!-- 페이지 설정 저장 시 긴 로그 본문과 사용하지 않는 공통 필드를 그대로 보존한다. -->
    <textarea name="wr_content" hidden><?php echo htmlspecialchars(isset($write['wr_content']) ? $write['wr_content'] : '', ENT_QUOTES); ?></textarea>
    <input type="hidden" name="wr_1" value="<?php echo htmlspecialchars(isset($write['wr_1']) ? $write['wr_1'] : '', ENT_QUOTES); ?>">
    <input type="hidden" name="wr_4" value="<?php echo htmlspecialchars(isset($write['wr_4']) ? $write['wr_4'] : '', ENT_QUOTES); ?>">
    <input type="hidden" name="wr_9" value="<?php echo htmlspecialchars(isset($write['wr_9']) ? $write['wr_9'] : '', ENT_QUOTES); ?>">
    <input type="hidden" name="wr_link1" value="<?php echo htmlspecialchars(isset($write['wr_link1']) ? $write['wr_link1'] : '', ENT_QUOTES); ?>">
    <input type="hidden" name="wr_link2" value="<?php echo htmlspecialchars(isset($write['wr_link2']) ? $write['wr_link2'] : '', ENT_QUOTES); ?>">
    <?php if (isset($write['wr_option']) && strpos($write['wr_option'], 'html2') !== false) { ?>
    <input type="hidden" name="html" value="html2">
    <?php } elseif (isset($write['wr_option']) && strpos($write['wr_option'], 'html1') !== false) { ?>
    <input type="hidden" name="html" value="html1">
    <?php } ?>
    <?php } ?>

    <?php if (!$trpg_is_log_edit) { ?>
    <?php if ($trpg_is_page_settings_edit) { ?>
    <div class="title-text">로그 페이지 설정</div>
    <?php } ?>
	<div class="trpg-option">
		<?php if ($is_category) { ?>
		  <div class="nav_category">
			<div class="title-text">CATEGORY</div>
			<div class="write_category">
				<select name="ca_name" id="ca_name" required class="required">
				  <option value="">선택하세요</option>
				  <?php echo $category_option; ?>
				</select>
			</div>
		  </div>
		<?php } ?>

		<!-- RA0 표준 비밀글 시스템 -->
		<div class="nav_option">
			<div class="title-text">Option</div>
			<div class="write_option">
			<?php if ($is_secret != 2 || $is_admin) { ?>
			  <input type="checkbox" id="wr_secret_check" <?php echo (isset($write['wr_option']) && strpos($write['wr_option'], 'secret') !== false) ? 'checked' : ''; ?>>
			  <label for="wr_secret_check">비밀글</label>
			<?php } ?>
			<?php echo $option; ?>
			</div>
		</div>

		<!-- hidden input: write_update.php로 전달 -->
		<input type="hidden" name="secret" value="" id="secret_input">

		<!-- 비밀글 조회 비밀번호 입력란 -->
		<div class="set_protect" id="secret_password_area" style="display:none">
			<div class="title-text">비밀글 비밀번호</div>
			<div><input type="text" name="wr_secret" id="wr_secret_input" class="frm_input" placeholder="비밀글 조회 비밀번호" maxlength="20" value="<?php echo isset($write['wr_secret']) ? htmlspecialchars($write['wr_secret']) : ''; ?>" disabled></div>
		</div>

		<script>
		// 비밀글 체크박스 토글
		var secretCheckbox = document.getElementById('wr_secret_check');
		var secretPasswordArea = document.getElementById('secret_password_area');
		var secretPasswordInput = document.getElementById('wr_secret_input');
		var secretHiddenInput = document.getElementById('secret_input');

		if (secretCheckbox && secretPasswordArea && secretPasswordInput) {
			secretPasswordInput.disabled = true;
			secretPasswordInput.required = false;

			secretCheckbox.addEventListener('change', function() {
				if (this.checked) {
					secretPasswordArea.style.display = 'block';
					secretPasswordInput.disabled = false;
					secretPasswordInput.required = true;
				} else {
					secretPasswordArea.style.display = 'none';
					secretPasswordInput.disabled = true;
					secretPasswordInput.required = false;
					secretPasswordInput.value = '';
				}
			});

			// 수정 시 비밀글이면 비밀번호 입력창 표시
			if (secretCheckbox.checked) {
				secretPasswordArea.style.display = 'block';
				secretPasswordInput.disabled = false;
				secretPasswordInput.required = true;
			}
		}
		</script>
	</div>

	<div class="trpg-flex">
        <div class="trpg-fontf">
            <div class="title-text">제목 글꼴</div>
			<select name="wr_subject_font" id="wr_subject_font" class="subject-font">
                <?php echo trpg_ticket_font_options($fonts, $trpg_subject_font); ?>
            </select>
            <div class="trpg-text-color-control">
                <input type="color" class="trpg-text-color-picker" data-color-input="wr_subject_color" value="<?php echo $trpg_subject_color; ?>" aria-label="제목 글자색 선택">
                <input type="text" name="wr_subject_color" id="wr_subject_color" class="frm_input trpg-text-color-code" value="<?php echo $trpg_subject_color; ?>" maxlength="7" pattern="#[0-9A-Fa-f]{6}" aria-label="제목 글자색 코드">
            </div>
		</div>
        <div class="wr-subject">
			<div class="title-text">제목</div>
			<div><input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="frm_input required full" maxlength="255" placeholder="제목 입력"></div>
		</div>
	</div>
    <div class="trpg-flex">
        <div class="form-group">
            <div class="title-text">영문 제목</div>
            <div class="trpg-text-style-controls">
                <select name="wr_english_title_font" id="wr_english_title_font" class="subject-font"><?php echo trpg_ticket_font_options($fonts, $trpg_english_title_font); ?></select>
                <div class="trpg-text-color-control">
                    <input type="color" class="trpg-text-color-picker" data-color-input="wr_english_title_color" value="<?php echo $trpg_english_title_color; ?>" aria-label="영어 제목 글자색 선택">
                    <input type="text" name="wr_english_title_color" id="wr_english_title_color" class="frm_input trpg-text-color-code" value="<?php echo $trpg_english_title_color; ?>" maxlength="7" pattern="#[0-9A-Fa-f]{6}" aria-label="영어 제목 글자색 코드">
                </div>
            </div>
            <div><input type="text" name="wr_english_title" id="wr_english_title" value="<?php echo htmlspecialchars(isset($write['wr_english_title']) ? $write['wr_english_title'] : '', ENT_QUOTES); ?>" class="frm_input full" maxlength="255" placeholder="영문 제목 입력"></div>
        </div>
        <div class="wr-subtitle">
            <div class="title-text">부제</div>
            <div class="trpg-text-style-controls">
                <select name="wr_subtitle_font" id="wr_subtitle_font" class="subject-font"><?php echo trpg_ticket_font_options($fonts, $trpg_subtitle_font); ?></select>
                <div class="trpg-text-color-control">
                    <input type="color" class="trpg-text-color-picker" data-color-input="wr_subtitle_color" value="<?php echo $trpg_subtitle_color; ?>" aria-label="부제 글자색 선택">
                    <input type="text" name="wr_subtitle_color" id="wr_subtitle_color" class="frm_input trpg-text-color-code" value="<?php echo $trpg_subtitle_color; ?>" maxlength="7" pattern="#[0-9A-Fa-f]{6}" aria-label="부제 글자색 코드">
                </div>
            </div>
            <div><input type="text" name="wr_title" id="wr_title" value="<?php echo htmlspecialchars(isset($write['wr_title']) ? stripslashes($write['wr_title']) : '', ENT_QUOTES); ?>" class="frm_input full" maxlength="255" placeholder="부제 입력"></div>
        </div>
    </div>
    <div class="trpg-flex">
        <div class="form-group">
            <div class="title-text">KPC</div>
            <div><input type="text" name="wr_kpc" id="wr_kpc" value="<?php echo htmlspecialchars($trpg_wr_kpc, ENT_QUOTES); ?>" class="frm_input full" maxlength="255" placeholder="KPC 입력 (선택)"></div>
        </div>
        <div class="form-group">
            <div class="trpg-flexS"><div class="title-text">PC</div><div class="info-text">*필수</div></div>
            <div><input type="text" name="wr_pc" id="wr_pc" value="<?php echo htmlspecialchars($trpg_wr_pc, ENT_QUOTES); ?>" class="frm_input required full" maxlength="255" placeholder="PC 입력" required></div>
        </div>
    </div>
    <div class="trpg-flex">
        <div class="form-group">
            <div class="title-text">캐치프레이즈</div>
            <div class="trpg-text-style-controls">
                <select name="wr_catchphrase_font" id="wr_catchphrase_font" class="subject-font"><?php echo trpg_ticket_font_options($fonts, $trpg_catchphrase_font); ?></select>
                <div class="trpg-text-color-control">
                    <input type="color" class="trpg-text-color-picker" data-color-input="wr_catchphrase_color" value="<?php echo $trpg_catchphrase_color; ?>" aria-label="캐치프레이즈 글자색 선택">
                    <input type="text" name="wr_catchphrase_color" id="wr_catchphrase_color" class="frm_input trpg-text-color-code" value="<?php echo $trpg_catchphrase_color; ?>" maxlength="7" pattern="#[0-9A-Fa-f]{6}" aria-label="캐치프레이즈 글자색 코드">
                </div>
            </div>
            <div><textarea name="wr_catchphrase" id="wr_catchphrase" class="frm_input full trpg-catchphrase-input" maxlength="500" rows="2" placeholder="캐치프레이즈 입력"><?php echo htmlspecialchars(isset($write['wr_catchphrase']) ? $write['wr_catchphrase'] : '', ENT_QUOTES); ?></textarea></div>
        </div>
        <div class="form-group">
            <div class="title-text">진행시간</div>
            <div><input type="text" name="wr_playtime" id="wr_playtime" value="<?php echo htmlspecialchars(isset($write['wr_playtime']) ? $write['wr_playtime'] : '', ENT_QUOTES); ?>" class="frm_input full" maxlength="50" placeholder="예: 06H 30M"></div>
        </div>
    </div>
    <div class="trpg-flex"> 
        <div class="trpg-date">
            <div class="trpg-flexS"><div class="title-text">시작일</div><div class="info-text">*필수</div></div>
            <div><input type="date" name="wr_7_start" id="wr_7_start" value="<?php echo (isset($write['wr_7']) && $write['wr_7']) ? explode(' ~ ', $write['wr_7'])[0] : ''; ?>" class="frm_input" required></div>
        </div>
        <div class="trpg-date">
            <div class="trpg-flexS"><div class="title-text">종료일</div><div class="info-text"></div></div>
            <div><input type="date" name="wr_7_end" id="wr_7_end" value="<?php echo (isset($write['wr_7']) && $write['wr_7'] && strpos($write['wr_7'], ' ~ ') !== false) ? explode(' ~ ', $write['wr_7'])[1] : ''; ?>" class="frm_input"></div>

            <!-- 실제 저장될 hidden 필드 -->
            <input type="hidden" name="wr_7" id="wr_7" value="<?php echo isset($write['wr_7']) ? $write['wr_7'] : ''; ?>">
        </div>
        <div class="trpg-comple">
            <div class="title-text">완료</div>
            <div class="trpg-comple-chk">
                <input type="checkbox" name="wr_wide" id="wr_wide" value="1" <?php echo (isset($write['wr_wide']) && $write['wr_wide'] == '1') ? 'checked' : ''; ?>>
            </div>
        </div>
    </div>

	<div id="card-preview" aria-hidden="true"></div>
    <section class="trpg-ticket-preview-panel" aria-labelledby="trpg-ticket-preview-label">
        <div class="title-text" id="trpg-ticket-preview-label">티켓 미리보기</div>
        <div class="trpg-ticket-preview">
            <div class="trpg-ticket-item trpg-ticket-item-preview">
                <div class="trpg-ticket-container">
                    <div class="trpg-ticket-image">
                        <img id="trpg-preview-card-image" src="" alt="" hidden>
                    </div>
                    <div class="trpg-ticket-info">
                        <svg class="trpg-ticket-frame" viewBox="0 0 280 210" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                            <path d="M16 0 H264 A16 16 0 0 0 280 16 V194 A16 16 0 0 0 264 210 H16 A16 16 0 0 0 0 194 V16 A16 16 0 0 0 16 0 Z" fill="none" stroke="currentColor" stroke-width="1" vector-effect="non-scaling-stroke"></path>
                        </svg>
                        <div class="trpg-info">
                            <div class="trpg-ticket-rule" id="trpg-preview-rule"></div>
                            <div class="trpg-ticket-heading">
                                <div class="trpg-ticket-title-en" id="trpg-preview-english-title" aria-hidden="true"></div>
                                <div class="trpg-ticket-title"><span id="trpg-preview-subject"></span></div>
                            </div>
                            <div class="trpg-ticket-subtitle" id="trpg-preview-subtitle"></div>
                            <blockquote class="trpg-ticket-quote" id="trpg-preview-quote">
                                <span class="quote-mark" aria-hidden="true">“</span>
                                <span class="quote-text" id="trpg-preview-catchphrase"></span>
                            </blockquote>
                            <dl class="trpg-ticket-meta">
                                <dt class="trpg-preview-kpc-row">KPC</dt><dd class="trpg-preview-kpc-row" id="trpg-preview-kpc"></dd>
                                <dt>PC</dt><dd id="trpg-preview-pc"></dd>
                                <dt>DATE</dt><dd id="trpg-preview-date"></dd>
                                <dt>PLAYTIME</dt><dd id="trpg-preview-playtime"></dd>
                            </dl>
                        </div>
                        <div class="trpg-ticket-barcode">
                            <div class="barcode-img"><div class="barcode-lines"></div></div>
                            <div class="barcode-number"><?php foreach (str_split('613095478981180509493379916130954789811805') as $trpg_preview_barcode_digit) { ?><span class="hologram-text"><?php echo $trpg_preview_barcode_digit; ?></span><?php } ?></div>
                        </div>
                        <div class="trpg-ticket-completed" id="trpg-preview-completed">
                            <div class="stamp-image" style="background-image:url('<?php echo htmlspecialchars($board_skin_url, ENT_QUOTES); ?>/img/tr-stamp.png')"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

	<div class="trpg-flex trpg-flex1">
        <?php
        // 기존 카드 이미지 가져오기
        $card_image = null;
        $card_images = array();
        $sql = "SELECT * FROM {$trpg_up_table}
                WHERE bo_table = '{$bo_table}'
                AND wr_id = '{$wr_id}'
                AND image_type = 'card'
                AND img_use = '1'";
        $result = sql_query($sql);
        while ($row = sql_fetch_array($result)) {
            $card_images[] = array(
                'id' => $row['id'],
                'image_url' => $row['image_url'],
                'wr_type' => $row['wr_type']
            );
        }
        // 첫 번째 카드 이미지를 $card_image에 할당
        if (!empty($card_images)) {
            $card_image = $card_images[0];
        }
        ?>
    </div>

    <!-- 세션 카드 (업로드/URL) -->
    <div class="card-wr-img">
        <div class="media-info">
            <div class="title-text">세션 카드</div>
            <div class="info-text">*업로드된 이미지가 없을 경우 외부 링크를 출력합니다.</div>
        </div>
        <div class="form-group-card">
            <input type="file" name="trpg_card_image[]" id="trpg_card_image" multiple accept="image/*" class="form-control">
            <input type="text" name="trpg_card_url" id="trpg_card_url" value="<?php echo $card_image ? $card_image['image_url'] : ''; ?>" class="frm_input full" placeholder="세션 카드 URL 입력">
            <button type="button" class="card-file-delete" onclick="deleteCardFile()">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    </div>
    <!-- 기존 카드 이미지 정보 저장 -->
    <input type="hidden" id="card-images-data" value='<?php echo json_encode($card_images, JSON_UNESCAPED_UNICODE); ?>'>

    <script>
    function deleteCardFile() {
        // 삭제 요청 전에 현재 세션 카드 URL을 가져옵니다.
        var CardUrl = $('#trpg_card_url').val();
        if (!CardUrl) {
            alert("삭제할 세션 카드 파일이 없습니다.");
            return;
        }
        
        if (confirm("세션 카드 파일을 삭제하시겠습니까?")) {
            $.ajax({
                url: '<?php $board_skin_url;?>/file_delete.php',
                type: "POST",
                data: {
                    wr_id: "<?php $wr_id;?>",
                    fileName: CardUrl,  // file_delete.php에서 파일명을 기준으로 삭제 처리
                    bo_table: "<?php $bo_table;?>"
                },
                dataType: "json",
                success: function(response) {
                    if (response.result === "success") {
                        // 삭제 성공 시 입력 필드와 미리보기 이미지 초기화
                        $('#trpg_card_url').val('');
                        $('#card-preview').empty().show();
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert("세션 카드 삭제 요청 중 오류 발생: " + error);
                }
            });
        }
    }
    </script>

    <div class="trpg-flex">
        <!-- 배경 색상 -->
        <div class="trpg-bgcolor">
            <div class="title-text">배경 색상</div>
            <input type="text" name="wr_2" id="wr_2" value="<?php echo isset($write['wr_2']) ? $write['wr_2'] : ''; ?>" class="frm_input" placeholder="컬러 (예: #FF5733)">
            <input type="color" id="tr_bgcolor_picker" onchange="document.getElementById('wr_2').value=this.value;">
        </div>

        <!-- 제목 색상 -->
        <div class="trpg-textcolor">
            <div class="title-text">제목 색상</div>
            <input type="text" name="wr_3" id="wr_3" value="<?php echo isset($write['wr_3']) ? $write['wr_3'] : ''; ?>" class="frm_input" placeholder="컬러 (예: #FF5733)">
            <input type="color" id="tr_txtcolor_picker" onchange="document.getElementById('wr_3').value=this.value;">
        </div>
        
        <!-- BGM 링크 -->
        <div class="trpg-wr-bgm">
            <div class="title-text">BGM 링크</div>
            <div><input type="text" name="wr_10" id="wr_10" value="<?php echo isset($write['wr_10']) ? $write['wr_10'] : ''; ?>" class="frm_input full" placeholder="배경음 URL 입력"></div>
        </div>

        <!-- 배경 이미지 (URL) -->
        <div class="trpg-wr-bg">
            <div class="title-text">배경 이미지</div>
            <div><input type="text" name="wr_url" id="wr_url" value="<?php echo isset($write['wr_url']) ? $write['wr_url'] : ''; ?>" class="frm_input full" placeholder="배경 이미지 URL 입력">
            </div>
        </div>
    </div>

    <div class="trpg-flex">
        <div class="wr-subtitle">
            <div class="title-text">개요</div>
            <?php
            // 개요 에디터 (wr_5)
            // stripslashes를 제거하고 get_text만 사용 (get_text가 이미 처리함)
            $wr_5_content = isset($write['wr_5']) ? $write['wr_5'] : '';
            echo editor_html('wr_5', $wr_5_content, $is_dhtml_editor);
            ?>
            <style>
            /* 개요 에디터 높이 조정 */
            #wr_5_viewer,
            #wr_5 {
                min-height: 150px !important;
                height: 150px !important;
            }
            </style>
        </div>
    </div>

<script>
$(function() {
    // 컬러 피커 변경 시 텍스트 필드에 값 반영 및 실시간 적용
    $('#tr_bgcolor_picker').change(function() {
        var bgColor = $(this).val();
        $('#wr_2').val(bgColor);
        $('.trpg-write-box').css('background', bgColor);
    });

    // 텍스트 필드 직접 입력 시에도 실시간 적용
    $('#wr_2').on('input', function() {
        var bgColor = $(this).val();
        $('.trpg-write-box').css('background', bgColor);
    });

    // 컬러 피커 변경 시 텍스트 필드에 값 반영 및 실시간 적용
    $('#tr_txtcolor_picker').change(function() {
        var txtColor = $(this).val();
        $('#wr_3').val(txtColor);
        $('#subject-preview').css('color', txtColor); // 제목 미리보기에도 색상 적용
    });

    // 텍스트 필드 직접 입력 시에도 실시간 적용
    $('#wr_3').on('input', function() {
        var txtColor = $(this).val();
        $('#subject-preview').css('color', txtColor); // 제목 미리보기에도 색상 적용
    });

    // 페이지 로드 시 기존 색상 적용
    if ($('#wr_3').val()) {
        $('#subject-preview').css('color', $('#wr_3').val()); // 제목 미리보기에도 색상 적용
    }

    // 제목 입력 시 실시간 미리보기
    $('#wr_subject').on('input', function() {
        updateTicketPreview();
    });

    // 제목 폰트 변경 시 실시간 미리보기
    $('#wr_subject_font').on('change', function() {
        updateTicketPreview();
    });

    // 페이지 로드 시 초기 제목과 폰트 적용
    function ticketPreviewValue(selector, fallback) {
        var value = $.trim($(selector).val() || '');
        return value || fallback;
    }

    function ticketPreviewColor(selector, fallback) {
        var value = $.trim($(selector).val() || '').toUpperCase();
        return /^#[0-9A-F]{6}$/.test(value) ? value : fallback;
    }

    function updateTicketPreviewImage() {
        var url = $.trim($('input[name="trpg_card_url"]').val() || '');
        var uploadedPreview = $('#card-preview img').first().attr('src');
        var imageUrl = uploadedPreview || url;
        $('#trpg-preview-card-image').attr('src', imageUrl || '').prop('hidden', !imageUrl);
    }

    function updateTicketPreview() {
        var subject = ticketPreviewValue('#wr_subject', '제목');
        var englishTitle = ticketPreviewValue('#wr_english_title', 'ENGLISH TITLE');
        var subtitle = ticketPreviewValue('#wr_title', '부제');
        var catchphrase = ticketPreviewValue('#wr_catchphrase', '캐치프레이즈');
        var kpc = $.trim($('#wr_kpc').val() || '');
        var startDate = $.trim($('#wr_7_start').val() || '');
        var endDate = $.trim($('#wr_7_end').val() || '');

        $('#trpg-preview-rule').text(ticketPreviewValue('#ca_name', 'RULE'));
        $('#trpg-preview-subject').text(subject).css({
            fontFamily: ticketPreviewValue('#wr_subject_font', 'Pretendard'),
            color: ticketPreviewColor('#wr_subject_color', '#1E3554')
        });
        $('#trpg-preview-english-title').text(englishTitle).css({
            fontFamily: ticketPreviewValue('#wr_english_title_font', 'Georgia'),
            color: ticketPreviewColor('#wr_english_title_color', '#1E3554')
        }).toggleClass('is-long', englishTitle.length > 20 && englishTitle.length <= 30)
          .toggleClass('is-very-long', englishTitle.length > 30);
        $('#trpg-preview-subtitle').text(subtitle).css({
            fontFamily: ticketPreviewValue('#wr_subtitle_font', 'Pretendard'),
            color: ticketPreviewColor('#wr_subtitle_color', '#667085')
        });
        $('#trpg-preview-quote').css({
            fontFamily: ticketPreviewValue('#wr_catchphrase_font', 'Pretendard'),
            color: ticketPreviewColor('#wr_catchphrase_color', '#667085')
        });
        $('#trpg-preview-quote .quote-mark').css('color', ticketPreviewColor('#wr_catchphrase_color', '#667085'));
        $('#trpg-preview-catchphrase').text(catchphrase);
        $('#trpg-preview-kpc').text(kpc);
        $('.trpg-preview-kpc-row').toggle(!!kpc);
        $('#trpg-preview-pc').text(ticketPreviewValue('#wr_pc', 'PC'));
        $('#trpg-preview-date').text(startDate ? (endDate ? startDate + ' ~ ' + endDate : startDate) : 'DATE');
        $('#trpg-preview-playtime').text(ticketPreviewValue('#wr_playtime', 'PLAYTIME'));
        $('#trpg-preview-completed').toggle($('#wr_wide').is(':checked'));
        updateTicketPreviewImage();
    }

    $('.trpg-text-color-picker').on('input change', function() {
        var target = $('#' + $(this).data('color-input'));
        target.val($(this).val().toUpperCase());
        updateTicketPreview();
    });

    $('.trpg-text-color-code').on('input', function() {
        var value = $.trim($(this).val()).toUpperCase();
        $(this).val(value);
        if (/^#[0-9A-F]{6}$/.test(value)) {
            $('.trpg-text-color-picker[data-color-input="' + this.id + '"]').val(value);
            updateTicketPreview();
        }
    }).on('blur', function() {
        var picker = $('.trpg-text-color-picker[data-color-input="' + this.id + '"]');
        if (!/^#[0-9A-F]{6}$/.test($.trim($(this).val()))) {
            $(this).val(picker.val().toUpperCase());
        }
        updateTicketPreview();
    });

    $('#wr_english_title, #wr_title, #wr_catchphrase, #wr_kpc, #wr_pc, #wr_playtime, #ca_name, #wr_7_start, #wr_7_end, #wr_wide, #wr_english_title_font, #wr_subtitle_font, #wr_catchphrase_font, input[name="trpg_card_url"]').on('input change', updateTicketPreview);
    if (window.MutationObserver && document.getElementById('card-preview')) {
        new MutationObserver(updateTicketPreviewImage).observe(document.getElementById('card-preview'), { childList: true, subtree: true, attributes: true });
    }
    updateTicketPreview();

    // 페이지 로드 시 기존 값 적용
    if ($('#wr_2').val()) {
        $('.trpg-write-box').css('background-color', $('#wr_2').val());
    }

    // 시작일과 종료일이 변경될 때 wr_7 필드 업데이트
    $('#wr_7_start, #wr_7_end').on('change', function() {
        var startDate = $('#wr_7_start').val();
        var endDate = $('#wr_7_end').val();
        
        if (startDate && endDate) {
            $('#wr_7').val(startDate + ' ~ ' + endDate);
        } else if (startDate) {
            $('#wr_7').val(startDate);
        } else {
            $('#wr_7').val('');
        }
    });
    
    // 폼 제출 전에 wr_7 필드 업데이트
    $('form[name=fwrite]').on('submit', function() {
        var startDate = $('#wr_7_start').val();
        var endDate = $('#wr_7_end').val();
        
        if (startDate && endDate) {
            $('#wr_7').val(startDate + ' ~ ' + endDate);
        } else if (startDate) {
            $('#wr_7').val(startDate);
        } else {
            $('#wr_7').val('');
        }
        
        return true;
    });
    
    // 페이지 로드 시 기존 wr_7 값 파싱
    if ($('#wr_7').val()) {
        var dates = $('#wr_7').val().split(' ~ ');
        if (dates.length > 0) {
            $('#wr_7_start').val(dates[0]);
            if (dates.length > 1) {
                $('#wr_7_end').val(dates[1]);
            }
        }
    }

    // URL 이미지 데이터 처리는 write.skin.script.php에서 처리
});
</script>

    <div class="trpg-flex">
        <!-- 캐릭터 이미지 업로드 -->
        <div class="form-group">
            <div class="cha-image-upload">
                <div class="trpg-flex-c">
                    <div class="upload-option">
                        <div class="title-text media">캐릭터 이미지 업로드</div>
                        <button type="button" id="cha-upload-btn" class="upload-btn"><i class="fa-solid fa-upload"></i></button>
                    </div>
                <div class="info-text">가로 400px로 자동 리사이징됩니다.</div>
                </div>
                <div id="cha-dropzone" class="dropzone">
                    <input type="file" name="trpg_cha_image[]" id="trpg_cha_image" multiple accept="image/*" style="display: none;">
                    <div class="preview" id="cha-preview"></div>
                </div>
            </div>
            
            <!-- 기존 캐릭터 이미지 정보 저장 -->
            <?php
            // 기존 캐릭터 이미지 가져오기
            $cha_images = array();
            $sql = "SELECT * FROM {$trpg_up_table} 
                    WHERE bo_table = '{$bo_table}' 
                    AND wr_id = '{$wr_id}' 
                    AND image_type = 'cha' 
                    AND img_use = '1' 
                    ORDER BY file_order ASC";
            $result = sql_query($sql);
            while ($row = sql_fetch_array($result)) {
                $cha_images[] = array(
                    'id' => $row['id'],
                    'image_url' => $row['image_url'],
                    'file_order' => $row['file_order']
                );
            }
            ?>
            <input type="hidden" id="cha-images-data" value='<?php echo json_encode($cha_images, JSON_UNESCAPED_UNICODE); ?>'>
            <input type="hidden" id="cha-image-order" name="cha_image_order" value='<?php echo json_encode(array_column($cha_images, 'id')); ?>'>
            <input type="hidden" id="cha-image-delete" name="cha_image_delete" value='[]'>
        </div>

        <!-- 일반 이미지 업로드 -->
        <div class="form-group">
            <div class="normal-image-upload">
                <div class="trpg-flex-c">
                    <div class="upload-option">
                        <div class="title-text media">핸드아웃 등</div>
                        <button type="button" id="normal-upload-btn" class="upload-btn"><i class="fa-solid fa-upload"></i></button>
                    </div>        
                    <div class="info-text">가로 최대 700px, 세로 최대 900px로 리사이징 됩니다.</div>
                </div>
                <div id="normal-dropzone" class="dropzone">
                    <input type="file" name="trpg_normal_image[]" id="trpg_normal_image" multiple accept="image/*" style="display: none;">
                    <div class="preview" id="normal-preview"></div>
                </div>
            </div>
            
            <!-- 기존 일반 이미지 정보 저장 -->
            <?php
            // 기존 일반 이미지 가져오기
            $normal_images = array();
            $sql = "SELECT * FROM {$trpg_up_table} 
                    WHERE bo_table = '{$bo_table}' 
                    AND wr_id = '{$wr_id}' 
                    AND image_type = 'normal' 
                    AND img_use = '1' 
                    ORDER BY file_order ASC";
            $result = sql_query($sql);
            while ($row = sql_fetch_array($result)) {
                $normal_images[] = array(
                    'id' => $row['id'],
                    'image_url' => $row['image_url'],
                    'file_order' => $row['file_order']
                );
            }
            ?>
            <input type="hidden" id="normal-images-data" value='<?php echo json_encode($normal_images, JSON_UNESCAPED_UNICODE); ?>'>
            <input type="hidden" id="normal-image-order" name="normal_image_order" value='<?php echo json_encode(array_column($normal_images, 'id')); ?>'>
            <input type="hidden" id="normal-image-delete" name="normal_image_delete" value='[]'>
        </div>
    </div>
    <?php } ?>

<?php if (!$trpg_is_page_settings_edit) { ?>
<!-- 본문 작성 (RA0 에디터) -->
<div class="editor-content-container">
    <div class="title-text"><?php echo $trpg_is_log_edit ? '로그 수정' : 'HTML 본문'; ?></div>
    <div class="trpg-nearby-preview-controls">
        <button type="button" id="trpg_nearby_preview_toggle" class="ui-btn" aria-pressed="false">커서 근처 미리보기</button>
        <label>
            <input type="checkbox" id="trpg_nearby_preview_auto" checked disabled>
            자동 갱신
        </label>
        <button type="button" id="trpg_nearby_preview_refresh" class="ui-btn" disabled>지금 갱신</button>
        <span id="trpg_nearby_preview_status" aria-live="polite">미리보기 꺼짐</span>
    </div>
    <?php if ($w === '') { ?>
    <div class="trpg-html-import-options">
        <div class="trpg-html-cleanup-options" aria-label="HTML 로그 정리 옵션">
            <label>
                <input type="checkbox" id="trpg_remove_hidden_messages">
                hidden message 삭제
            </label>
            <label>
                <input type="checkbox" id="trpg_remove_duplicate_messages">
                중복 message 삭제
            </label>
        </div>
        <div class="trpg-html-file-upload">
            <label for="trpg_html_log_file" class="ui-btn">HTML 파일 선택</label>
            <input type="file" id="trpg_html_log_file" accept=".html,.htm,text/html">
            <span id="trpg_html_file_status" aria-live="polite">선택된 파일 없음</span>
        </div>
    </div>
    <div class="trpg-html-only-notice">
        HTML 로그 코드를 직접 붙여넣거나 HTML 파일을 선택하세요. 정리 옵션은 위 체크박스를 선택한 경우에만 실행됩니다.
    </div>
    <style>
    .trpg-initial-html-only .ra0-editor-toolbar,
    .trpg-initial-html-only .ra0-editor-viewer,
    .trpg-initial-html-only .ra0-editor-help {
        display: none !important;
    }

    .trpg-initial-html-only .ra0-editor-source {
        display: block !important;
        width: 100%;
        min-height: 600px;
        box-sizing: border-box;
        font-family: Consolas, Monaco, monospace;
        line-height: 1.5;
        white-space: pre;
        resize: vertical;
    }

    .trpg-html-only-notice {
        margin-bottom: 12px;
        padding: 12px 14px;
        border-radius: 6px;
        background: rgba(0, 0, 0, 0.06);
        line-height: 1.5;
    }
    </style>
    <div id="trpg_nearby_preview_shell" class="trpg-nearby-preview-shell">
        <section id="trpg_nearby_preview_pane" class="trpg-nearby-preview-pane" aria-label="커서 근처 HTML 미리보기" hidden>
            <iframe id="trpg_nearby_preview_frame" title="커서 근처 HTML 미리보기" sandbox="allow-same-origin"></iframe>
        </section>
        <div class="trpg-nearby-editor-pane">
    <div class="trpg-initial-html-only">
    <?php } ?>
    <?php if ($w !== '') { ?>
    <div id="trpg_nearby_preview_shell" class="trpg-nearby-preview-shell">
        <section id="trpg_nearby_preview_pane" class="trpg-nearby-preview-pane" aria-label="커서 근처 HTML 미리보기" hidden>
            <iframe id="trpg_nearby_preview_frame" title="커서 근처 HTML 미리보기" sandbox="allow-same-origin"></iframe>
        </section>
        <div class="trpg-nearby-editor-pane">
    <?php } ?>
    <?php
    // RA0 에디터 출력 (write.head.skin.php에서 40개 필드 병합 완료)
    echo $editor_html;
    ?>
    <?php if ($w === '') { ?>
    </div>
    <?php } ?>
        </div>
    </div>

    <!-- 분할된 내용을 저장할 hidden 필드들 -->
    <?php for ($i = 1; $i <= 40; $i++) { ?>
    <input type="hidden" id="wr_<?php echo $i; ?>_txt" name="wr_<?php echo $i; ?>_txt" value="">
    <?php } ?>
</div>
<?php } ?>

    <hr class="padding" />
    <div class="btn_confirm txt-center">
      <input type="submit" value="<?php echo $trpg_is_log_edit ? '로그 저장' : ($trpg_is_page_settings_edit ? '설정 저장' : '작성완료'); ?>" id="btn_submit" accesskey="s" class="btn_submit ui-btn point">
      <a href="./board.php?bo_table=<?php echo $bo_table ?><?php echo $w === 'u' ? '&amp;wr_id='.(int)$wr_id : ''; ?>" class="btn_cancel ui-btn">취소</a>
    </div>
	
</div><!-- write-box END -->
</form>


<script>
// 콘텐츠 청크 업로드 및 처리 함수
async function processContentInChunks() {
    // 이미 처리 중인지 확인
    if (document.getElementById('content-upload-status')) {
        return false;
    }
    
    const editor = document.getElementById('wr_editor');
    const content = editor.value;
    const chunkSize = 50000; // 청크 크기 (약 50KB)
    const totalChunks = Math.ceil(content.length / chunkSize);
    
    // 업로드 진행 상태 표시
    const statusDiv = document.createElement('div');
    statusDiv.id = 'content-upload-status';
    statusDiv.style.position = 'fixed';
    statusDiv.style.top = '50%';
    statusDiv.style.left = '50%';
    statusDiv.style.transform = 'translate(-50%, -50%)';
    statusDiv.style.padding = '20px';
    statusDiv.style.backgroundColor = 'rgba(0, 0, 0, 0.8)';
    statusDiv.style.color = 'white';
    statusDiv.style.borderRadius = '5px';
    statusDiv.style.zIndex = '9999';
    statusDiv.innerHTML = '콘텐츠 업로드 준비 중...';
    document.body.appendChild(statusDiv);
    
    try {
        // 1. 모든 청크 업로드
        for (let i = 0; i < totalChunks; i++) {
            const start = i * chunkSize;
            const end = Math.min((i + 1) * chunkSize, content.length);
            const chunk = content.substring(start, end);
            
            statusDiv.innerHTML = `콘텐츠 업로드 중... (${i+1}/${totalChunks})`;
            
            // AJAX로 청크 전송
            const formData = new FormData();
            formData.append('bo_table', '<?php echo $bo_table; ?>');
            formData.append('wr_id', '<?php echo $wr_id; ?>');
            formData.append('chunk_index', i);
            formData.append('total_chunks', totalChunks);
            formData.append('chunk_data', chunk);
            formData.append('action', 'upload');
            
            const response = await fetch('<?php echo $board_skin_url; ?>/ajax.content_chunk.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.result !== 'success') {
                throw new Error('청크 업로드 실패: ' + (result.message || '알 수 없는 오류'));
            }
        }
        
        // 2. 모든 청크가 업로드되면 처리 요청
        statusDiv.innerHTML = '콘텐츠 처리 중...';
        
        const finalFormData = new FormData();
        finalFormData.append('bo_table', '<?php echo $bo_table; ?>');
        finalFormData.append('wr_id', '<?php echo $wr_id; ?>');
        finalFormData.append('total_chunks', totalChunks);
        finalFormData.append('action', 'process');
        
        const processResponse = await fetch('<?php echo $board_skin_url; ?>/ajax.content_chunk.php', {
            method: 'POST',
            body: finalFormData
        });
        
        const processResult = await processResponse.json();
        if (processResult.result !== 'success') {
            throw new Error('콘텐츠 처리 실패: ' + (processResult.message || '알 수 없는 오류'));
        }
        
        // 3. 성공 메시지 표시 후 상태창 제거
        statusDiv.innerHTML = '콘텐츠가 성공적으로 저장되었습니다.';
        setTimeout(() => {
            statusDiv.remove();
            // 성공 후 페이지 이동 (필요한 경우)
            // window.location.href = '<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $bo_table; ?>&wr_id=<?php echo $wr_id; ?>';
        }, 2000);
        
        return true;
        
    } catch (error) {
        statusDiv.innerHTML = '오류 발생: ' + error.message;
        console.error('콘텐츠 처리 오류:', error);
        setTimeout(() => {
            statusDiv.remove();
        }, 5000);
        return false;
    }
}

function getTrpgEditorContent() {
    const viewer = document.getElementById('wr_content_viewer');
    const source = document.getElementById('wr_content');
    const sourceIsVisible = source && window.getComputedStyle(source).display !== 'none';

    if (sourceIsVisible) {
        return source.value;
    }

    if (viewer) {
        return viewer.innerHTML;
    }

    return source ? source.value : '';
}

function setTrpgEditorContent(content) {
    const viewer = document.getElementById('wr_content_viewer');
    const source = document.getElementById('wr_content');

    if (viewer) {
        viewer.innerHTML = content;
    }

    if (source) {
        source.value = content;
    }
}

function cleanInitialHtmlLog(content, removeHiddenMessages, removeDuplicateMessages) {
    const container = document.createElement('div');
    container.innerHTML = content;

    let hiddenCount = 0;
    let duplicateCount = 0;

    if (removeHiddenMessages) {
        container.querySelectorAll('.message.hidden-message').forEach(function(message) {
            message.remove();
            hiddenCount++;
        });
    }

    if (removeDuplicateMessages) {
        const seenMessageIds = new Set();
        container.querySelectorAll('.message[data-messageid]').forEach(function(message) {
            const messageId = message.getAttribute('data-messageid');

            if (!messageId) {
                return;
            }

            if (seenMessageIds.has(messageId)) {
                message.remove();
                duplicateCount++;
                return;
            }

            seenMessageIds.add(messageId);
        });
    }

    if (
        container.children.length === 1 &&
        container.firstElementChild.classList.contains('ra0-content')
    ) {
        content = container.firstElementChild.innerHTML;
    } else {
        content = container.innerHTML;
    }

    return {
        content: content,
        hiddenCount: hiddenCount,
        duplicateCount: duplicateCount
    };
}

// 폼 제출 이벤트 처리
function getTrpgNearbyMarkerPosition(html, cursorPosition) {
    const beforeCursor = html.slice(0, cursorPosition).toLowerCase();
    const lastOpenBracket = beforeCursor.lastIndexOf('<');
    const lastCloseBracket = beforeCursor.lastIndexOf('>');

    if (lastOpenBracket > lastCloseBracket) {
        const nextCloseBracket = html.indexOf('>', cursorPosition);
        return nextCloseBracket === -1 ? lastOpenBracket : nextCloseBracket + 1;
    }

    const lastStyleOpen = beforeCursor.lastIndexOf('<style');
    const lastStyleClose = beforeCursor.lastIndexOf('</style');
    const lastScriptOpen = beforeCursor.lastIndexOf('<script');
    const lastScriptClose = beforeCursor.lastIndexOf('</script');

    if (lastStyleOpen > lastStyleClose || lastScriptOpen > lastScriptClose) {
        return null;
    }

    return cursorPosition;
}

function buildTrpgNearbyPreview(html, cursorPosition, previousBodyHtml) {
    const markerText = 'TRPG_NEARBY_CURSOR';
    const markerPosition = getTrpgNearbyMarkerPosition(html, cursorPosition);
    const markedHtml = markerPosition === null
        ? html
        : html.slice(0, markerPosition) + '<!--' + markerText + '-->' + html.slice(markerPosition);
    const previewDocument = new DOMParser().parseFromString(markedHtml, 'text/html');
    const sourceStyles = Array.from(
        previewDocument.querySelectorAll('style, link[rel~="stylesheet"]')
    ).map(function(element) {
        return element.outerHTML;
    }).join('\n');

    previewDocument.querySelectorAll('script, style, link[rel~="stylesheet"]').forEach(function(element) {
        element.remove();
    });

    let markerNode = null;
    const commentWalker = previewDocument.createTreeWalker(
        previewDocument,
        NodeFilter.SHOW_COMMENT
    );
    let currentComment;

    while ((currentComment = commentWalker.nextNode())) {
        if ((currentComment.nodeValue || '').trim() === markerText) {
            markerNode = currentComment;
            break;
        }
    }

    if (!markerNode && markerPosition === null && previousBodyHtml) {
        return {
            bodyHtml: previousBodyHtml,
            sourceStyles: sourceStyles,
            blockCount: 0,
            reusedPrevious: true
        };
    }

    const preferredBlockSelector = [
        '.message',
        '[data-messageid]',
        'article',
        'section',
        'li',
        'tr',
        'blockquote'
    ].join(',');
    let targetBlock = markerNode && markerNode.parentElement
        ? markerNode.parentElement.closest(preferredBlockSelector)
        : null;

    if (!targetBlock && markerNode && markerNode.parentElement) {
        let previousElement = markerNode.previousSibling;
        let nextElement = markerNode.nextSibling;

        while (previousElement && previousElement.nodeType !== 1) {
            previousElement = previousElement.previousSibling;
        }
        while (nextElement && nextElement.nodeType !== 1) {
            nextElement = nextElement.nextSibling;
        }

        if (previousElement) {
            if (previousElement.matches(preferredBlockSelector)) {
                targetBlock = previousElement;
            } else {
                const previousBlocks = previousElement.querySelectorAll(preferredBlockSelector);
                targetBlock = previousBlocks.length
                    ? previousBlocks[previousBlocks.length - 1]
                    : null;
            }
        }

        if (!targetBlock && nextElement) {
            targetBlock = nextElement.matches(preferredBlockSelector)
                ? nextElement
                : nextElement.querySelector(preferredBlockSelector);
        }
    }

    if (!targetBlock && markerNode && markerNode.parentElement) {
        let candidate = markerNode.parentElement;
        const fallbackTags = ['DIV', 'P', 'LI', 'ARTICLE', 'SECTION', 'BLOCKQUOTE', 'TR'];

        while (candidate && candidate !== previewDocument.body) {
            if (
                fallbackTags.indexOf(candidate.tagName) !== -1 &&
                candidate.parentElement &&
                candidate.outerHTML.length < 200000
            ) {
                targetBlock = candidate;
                break;
            }
            candidate = candidate.parentElement;
        }
    }

    if (!targetBlock) {
        targetBlock = previewDocument.body.querySelector(preferredBlockSelector);
    }

    if (markerNode) {
        markerNode.remove();
    }

    if (!targetBlock) {
        if (previousBodyHtml) {
            return {
                bodyHtml: previousBodyHtml,
                sourceStyles: sourceStyles,
                blockCount: 0,
                reusedPrevious: true
            };
        }

        return {
            error: '커서 근처에서 미리 볼 HTML 블록을 찾지 못했습니다.'
        };
    }

    const blockParent = targetBlock.parentElement;
    if (!blockParent) {
        return {
            error: '선택한 HTML 블록의 상위 요소를 찾지 못했습니다.'
        };
    }

    const siblings = Array.from(blockParent.children);
    const targetIndex = siblings.indexOf(targetBlock);
    const firstIndex = Math.max(0, targetIndex - 2);
    const selectedSiblings = siblings.slice(firstIndex, targetIndex + 3);
    let previewRoot;

    if (blockParent === previewDocument.body) {
        const bodyFragment = previewDocument.createElement('div');
        selectedSiblings.forEach(function(element) {
            bodyFragment.appendChild(element.cloneNode(true));
        });
        previewRoot = bodyFragment;
    } else {
        previewRoot = blockParent.cloneNode(false);
        selectedSiblings.forEach(function(element) {
            previewRoot.appendChild(element.cloneNode(true));
        });

        let sourceAncestor = blockParent.parentElement;
        let depth = 0;
        while (sourceAncestor && sourceAncestor !== previewDocument.body && depth < 8) {
            const ancestorClone = sourceAncestor.cloneNode(false);
            ancestorClone.appendChild(previewRoot);
            previewRoot = ancestorClone;
            sourceAncestor = sourceAncestor.parentElement;
            depth++;
        }
    }

    const bodyHtml = previewRoot.outerHTML;
    if (bodyHtml.length > 250000) {
        return {
            error: '커서가 포함된 블록이 너무 큽니다. 더 안쪽의 메시지 블록에 커서를 놓아 주세요.'
        };
    }

    return {
        bodyHtml: bodyHtml,
        sourceStyles: sourceStyles,
        blockCount: selectedSiblings.length,
        reusedPrevious: false
    };
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('fwrite');
    const trpgEditMode = '<?php echo $trpg_edit_mode; ?>';
    const isInitialLogUpload = <?php echo $w === '' ? 'true' : 'false'; ?>;
    let preparedLogContent = null;
    let cleanupResult = null;
    let htmlLogFileReading = false;

    if (isInitialLogUpload) {
        const initialViewer = document.getElementById('wr_content_viewer');
        const initialSource = document.getElementById('wr_content');
        const htmlLogFileInput = document.getElementById('trpg_html_log_file');
        const htmlFileStatus = document.getElementById('trpg_html_file_status');

        if (initialViewer) {
            initialViewer.style.display = 'none';
        }

        if (initialSource) {
            initialSource.style.display = 'block';
            initialSource.placeholder = 'HTML 로그 코드를 붙여넣거나 위에서 HTML 파일을 선택하세요.';
            initialSource.setAttribute('aria-label', 'HTML 로그 코드');
        }

        if (window.RA0Editor && RA0Editor.instances['wr_content']) {
            RA0Editor.instances['wr_content'].sourceMode = true;
        }

        if (htmlLogFileInput) {
            htmlLogFileInput.addEventListener('change', function() {
                const file = this.files && this.files[0] ? this.files[0] : null;

                if (!file) {
                    if (htmlFileStatus) {
                        htmlFileStatus.textContent = '선택된 파일 없음';
                    }
                    return;
                }

                const fileName = file.name.toLowerCase();
                if (!fileName.endsWith('.html') && !fileName.endsWith('.htm') && file.type !== 'text/html') {
                    alert('HTML 또는 HTM 파일만 선택할 수 있습니다.');
                    this.value = '';
                    if (htmlFileStatus) {
                        htmlFileStatus.textContent = '선택된 파일 없음';
                    }
                    return;
                }

                if (htmlFileStatus) {
                    htmlFileStatus.textContent = file.name + ' 읽는 중...';
                }
                htmlLogFileReading = true;

                const reader = new FileReader();
                reader.onload = function() {
                    const htmlContent = typeof reader.result === 'string' ? reader.result : '';
                    setTrpgEditorContent(htmlContent);
                    preparedLogContent = null;
                    cleanupResult = null;
                    htmlLogFileReading = false;
                    scheduleNearbyPreview(false);
                    if (htmlFileStatus) {
                        htmlFileStatus.textContent = file.name + ' 불러오기 완료';
                    }
                };
                reader.onerror = function() {
                    htmlLogFileReading = false;
                    alert('HTML 파일을 읽을 수 없습니다.');
                    if (htmlFileStatus) {
                        htmlFileStatus.textContent = '파일 읽기 실패';
                    }
                };
                reader.readAsText(file);
            });
        }
    }
    
   // 폼 제출 이벤트 가로채기
    const nearbyToggle = document.getElementById('trpg_nearby_preview_toggle');
    const nearbyAuto = document.getElementById('trpg_nearby_preview_auto');
    const nearbyRefresh = document.getElementById('trpg_nearby_preview_refresh');
    const nearbyShell = document.getElementById('trpg_nearby_preview_shell');
    const nearbyPane = document.getElementById('trpg_nearby_preview_pane');
    const nearbyFrame = document.getElementById('trpg_nearby_preview_frame');
    const nearbyStatus = document.getElementById('trpg_nearby_preview_status');
    const nearbySource = document.getElementById('wr_content');
    const nearbyAutoLimit = 500 * 1024;
    let nearbyEnabled = false;
    let nearbyTimer = null;
    let nearbyPreviousSourceMode = null;
    let nearbyLastBodyHtml = '';

    function setNearbyStatus(message) {
        if (nearbyStatus) {
            nearbyStatus.textContent = message;
        }
    }

    function renderNearbyPreview() {
        if (!nearbyEnabled || !nearbySource || !nearbyFrame) {
            return;
        }

        const result = buildTrpgNearbyPreview(
            nearbySource.value,
            nearbySource.selectionStart || 0,
            nearbyLastBodyHtml
        );

        if (result.error) {
            setNearbyStatus(result.error);
            return;
        }

        nearbyLastBodyHtml = result.bodyHtml;
        const pageStyles = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
            .map(function(link) {
                return '<link rel="stylesheet" href="' +
                    String(link.href).replace(/&/g, '&amp;').replace(/"/g, '&quot;') +
                    '">';
            }).join('\n');
        const viewStyle = '<link rel="stylesheet" href="<?php echo $board_skin_url; ?>/style.css">';
        const previewDocumentHtml = [
            '<!doctype html><html><head><meta charset="utf-8">',
            '<base href="' + String(window.location.href).replace(/&/g, '&amp;').replace(/"/g, '&quot;') + '">',
            pageStyles,
            viewStyle,
            result.sourceStyles,
            '<style>html,body{min-height:100%;margin:0}body{box-sizing:border-box;padding:16px;overflow-wrap:anywhere}.trpg-nearby-preview-root{min-width:0}</style>',
            '</head><body><div class="content-box"><div class="content_area trpg-nearby-preview-root">',
            result.bodyHtml,
            '</div></div></body></html>'
        ].join('');

        nearbyFrame.srcdoc = previewDocumentHtml;
        if (result.reusedPrevious) {
            setNearbyStatus('CSS 반영 완료 · 직전 블록 유지');
        } else {
            setNearbyStatus('커서 기준 ' + result.blockCount + '개 블록');
        }
    }

    function scheduleNearbyPreview(forceRefresh) {
        if (!nearbyEnabled || !nearbySource) {
            return;
        }

        window.clearTimeout(nearbyTimer);

        if (forceRefresh) {
            renderNearbyPreview();
            return;
        }

        if (!nearbyAuto || !nearbyAuto.checked) {
            setNearbyStatus('수정됨 · 지금 갱신을 눌러 주세요');
            return;
        }

        if (nearbySource.value.length > nearbyAutoLimit) {
            nearbyAuto.checked = false;
            setNearbyStatus('500KB 초과 · 수동 갱신 모드');
            return;
        }

        setNearbyStatus('갱신 대기 중…');
        nearbyTimer = window.setTimeout(renderNearbyPreview, 800);
    }

    function enableNearbyPreview() {
        if (!nearbyShell || !nearbyPane || !nearbySource) {
            return;
        }

        const editorInstance = window.RA0Editor && RA0Editor.instances
            ? RA0Editor.instances['wr_content']
            : null;
        nearbyPreviousSourceMode = editorInstance ? !!editorInstance.sourceMode : true;

        if (editorInstance && !editorInstance.sourceMode && typeof RA0Editor.toggleSource === 'function') {
            RA0Editor.toggleSource('wr_content');
        }

        nearbyEnabled = true;
        nearbyShell.classList.add('is-active');
        nearbyPane.hidden = false;
        nearbyToggle.classList.add('is-active');
        nearbyToggle.setAttribute('aria-pressed', 'true');
        nearbyToggle.textContent = '근처 미리보기 끄기';
        nearbyAuto.disabled = false;
        nearbyRefresh.disabled = false;

        if (nearbySource.value.length > nearbyAutoLimit) {
            nearbyAuto.checked = false;
            setNearbyStatus('500KB 초과 · 수동 갱신 모드');
        }

        renderNearbyPreview();
    }

    function disableNearbyPreview() {
        if (!nearbyShell || !nearbyPane) {
            return;
        }

        window.clearTimeout(nearbyTimer);
        nearbyEnabled = false;
        nearbyShell.classList.remove('is-active');
        nearbyPane.hidden = true;
        nearbyToggle.classList.remove('is-active');
        nearbyToggle.setAttribute('aria-pressed', 'false');
        nearbyToggle.textContent = '커서 근처 미리보기';
        nearbyAuto.disabled = true;
        nearbyRefresh.disabled = true;
        nearbyFrame.srcdoc = '';
        setNearbyStatus('미리보기 꺼짐');

        const editorInstance = window.RA0Editor && RA0Editor.instances
            ? RA0Editor.instances['wr_content']
            : null;
        if (
            nearbyPreviousSourceMode === false &&
            editorInstance &&
            editorInstance.sourceMode &&
            typeof RA0Editor.toggleSource === 'function'
        ) {
            RA0Editor.toggleSource('wr_content');
        }
        nearbyPreviousSourceMode = null;
    }

    if (nearbyToggle && nearbySource && nearbyAuto && nearbyRefresh) {
        nearbyToggle.addEventListener('click', function() {
            if (nearbyEnabled) {
                disableNearbyPreview();
            } else {
                enableNearbyPreview();
            }
        });
        nearbyRefresh.addEventListener('click', function() {
            scheduleNearbyPreview(true);
        });
        nearbyAuto.addEventListener('change', function() {
            if (this.checked && nearbySource.value.length > nearbyAutoLimit) {
                this.checked = false;
                setNearbyStatus('500KB 초과 로그는 수동 갱신만 가능합니다.');
                return;
            }
            if (this.checked) {
                scheduleNearbyPreview(true);
            } else {
                setNearbyStatus('수동 갱신 모드');
            }
        });
        nearbySource.addEventListener('input', function() {
            scheduleNearbyPreview(false);
        });
        nearbySource.addEventListener('click', function() {
            scheduleNearbyPreview(false);
        });
        nearbySource.addEventListener('keyup', function(event) {
            if (
                event.key.indexOf('Arrow') === 0 ||
                event.key === 'Home' ||
                event.key === 'End' ||
                event.key === 'PageUp' ||
                event.key === 'PageDown'
            ) {
                scheduleNearbyPreview(false);
            }
        });
    }

   form.addEventListener('submit', function(e) {
        e.preventDefault(); // 기본 제출 동작 중지

        if (htmlLogFileReading) {
            alert('HTML 파일을 읽는 중입니다. 불러오기가 완료된 뒤 다시 저장하세요.');
            return false;
        }

        // 폼 유효성 검사
        if (trpgEditMode !== 'content' && !form.wr_subject.value) {
            alert("제목을 입력하세요.");
            form.wr_subject.focus();
            return false;
        }

        if (trpgEditMode !== 'content' && !form.wr_7_start.value) {
            alert("시작일을 입력하세요.");
            form.wr_7_start.focus();
            return false;
        }

        if (trpgEditMode !== 'content' && (!form.wr_pc || !form.wr_pc.value.trim())) {
            alert("PC를 입력하세요.");
            if (form.wr_pc) {
                form.wr_pc.focus();
            }
            return false;
        }

         // 비밀글 체크 시 비밀번호 필수 검증
        var secretCheckbox = document.getElementById('wr_secret_check');
        var secretPasswordInput = document.getElementById('wr_secret_input');
        var secretHiddenInput = document.getElementById('secret_input');

        if (secretCheckbox && secretHiddenInput) {
            // hidden input 동기화
            secretHiddenInput.value = secretCheckbox.checked ? 'secret' : '';

            // 비밀글 체크 시 비밀번호 필수
            if (secretCheckbox.checked && secretPasswordInput) {
                if (secretPasswordInput.value === '') {
                    alert('비밀글 조회 비밀번호를 입력해주세요.');
                    secretPasswordInput.focus();
                    return false;
                }
            }
        }

        if (trpgEditMode !== 'settings') {
            preparedLogContent = getTrpgEditorContent();

            if (isInitialLogUpload) {
                const removeHiddenMessages = document.getElementById('trpg_remove_hidden_messages');
                const removeDuplicateMessages = document.getElementById('trpg_remove_duplicate_messages');
                const shouldRemoveHidden = !!(removeHiddenMessages && removeHiddenMessages.checked);
                const shouldRemoveDuplicates = !!(removeDuplicateMessages && removeDuplicateMessages.checked);

                if (shouldRemoveHidden || shouldRemoveDuplicates) {
                    cleanupResult = cleanInitialHtmlLog(
                        preparedLogContent,
                        shouldRemoveHidden,
                        shouldRemoveDuplicates
                    );
                    preparedLogContent = cleanupResult.content;
                    setTrpgEditorContent(preparedLogContent);
                } else {
                    cleanupResult = null;
                }
            }
        }

        // 모든 콘텐츠를 청크 방식으로 처리
        handleContent();
    });
    
    // 모든 콘텐츠 처리 함수 (청크 방식)
    async function handleContent() {
        // 이미 처리 중인지 확인
        if (document.getElementById('content-upload-status')) {
            return false;
        }

        // RA0 에디터에서 content 가져오기
        const editorViewer = document.getElementById('wr_content_viewer');
        const content = preparedLogContent !== null
            ? preparedLogContent
            : (editorViewer ? editorViewer.innerHTML : '');
        
        // 상태 표시 UI 생성
        const statusDiv = document.createElement('div');
        statusDiv.id = 'content-upload-status';
        statusDiv.style.position = 'fixed';
        statusDiv.style.top = '50%';
        statusDiv.style.left = '50%';
        statusDiv.style.transform = 'translate(-50%, -50%)';
        statusDiv.style.padding = '20px';
        statusDiv.style.backgroundColor = 'rgba(0, 0, 0, 0.8)';
        statusDiv.style.color = 'white';
        statusDiv.style.borderRadius = '5px';
        statusDiv.style.zIndex = '9999';
        statusDiv.innerHTML = trpgEditMode === 'settings' ? '로그 페이지 설정 저장 중...' : '로그 저장 준비 중...';
        document.body.appendChild(statusDiv);
        
        try {
            // 1단계: 내용 없이 게시글 먼저 생성
            if (cleanupResult && (cleanupResult.hiddenCount || cleanupResult.duplicateCount)) {
                statusDiv.innerHTML = '로그 정리 완료: hidden message ' +
                    cleanupResult.hiddenCount + '개, 반복 로그 ' +
                    cleanupResult.duplicateCount + '개 삭제<br>로그 정보 확인 중...';
            } else {
                statusDiv.innerHTML = trpgEditMode === 'settings' ? '로그 페이지 설정 저장 중...' : '로그 정보 확인 중...';
            }

            // ★ FormData 생성 전에 파일 배열을 input에 설정
            if (typeof DataTransfer !== 'undefined') {
                // 캐릭터 이미지
                if (window.chaFilesArray && window.chaFilesArray.length > 0) {
                    const dtCha = new DataTransfer();
                    window.chaFilesArray.forEach(file => dtCha.items.add(file));
                    const chaInput = document.getElementById('trpg_cha_image');
                    if (chaInput) chaInput.files = dtCha.files;
                }

                // 일반 이미지
                if (window.normalFilesArray && window.normalFilesArray.length > 0) {
                    const dtNormal = new DataTransfer();
                    window.normalFilesArray.forEach(file => dtNormal.items.add(file));
                    const normalInput = document.getElementById('trpg_normal_image');
                    if (normalInput) normalInput.files = dtNormal.files;
                }
            }

            // ★ URL 이미지 hidden input 추가
            try {
                let chaUrls = JSON.parse(document.getElementById('cha-image-urls')?.value || '[]');
                if (chaUrls.length > 0) {
                    let input = document.querySelector('input[name="trpg_cha_url_images"]');
                    if (!input) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'trpg_cha_url_images';
                        form.appendChild(input);
                    }
                    input.value = chaUrls.join(',');
                }
            } catch(e) { console.error('캐릭터 URL 처리 오류:', e); }

            try {
                let normalUrls = JSON.parse(document.getElementById('normal-image-urls')?.value || '[]');
                if (normalUrls.length > 0) {
                    let input = document.querySelector('input[name="trpg_normal_url_images"]');
                    if (!input) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'trpg_normal_url_images';
                        form.appendChild(input);
                    }
                    input.value = normalUrls.join(',');
                }
            } catch(e) { console.error('일반 URL 처리 오류:', e); }

            const isUpdate = '<?php echo $w; ?>' === 'u';
            const createRequestToken = !isUpdate
                ? 'trpg-' + (
                    window.crypto && typeof window.crypto.randomUUID === 'function'
                        ? window.crypto.randomUUID()
                        : Date.now().toString(36) + '-' + Math.random().toString(36).slice(2)
                )
                : '';
            const formData = new FormData(form);
            formData.set('trpg_ajax_write', '1');
            if (createRequestToken) {
                formData.set('wr_9', createRequestToken);
            }

            if (trpgEditMode !== 'settings') {
                // 본문은 게시글 저장 뒤 청크 처리로 wr_content LONGTEXT에 교체한다.
                formData.set('wr_content', '임시 내용 - 곧 업데이트됩니다.');
            }

            let wrId = '<?php echo $wr_id; ?>';

            // 게시글 생성/수정 요청
            const submitResponse = await fetch('<?php echo $action_url ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const submitText = await submitResponse.text();
            let submitResult;

            try {
                submitResult = JSON.parse(submitText);
            } catch (parseError) {
                // 일부 서버는 AJAX 헤더를 전달하지 않고 저장 후 게시글 화면으로 리다이렉트한다.
                // fetch가 따라간 최종 URL을 정상 저장 결과로 사용한다.
                if (submitResponse.redirected && submitResponse.url) {
                    submitResult = {
                        success: true,
                        redirect_url: submitResponse.url
                    };
                } else {
                    const responseDocument = new DOMParser().parseFromString(submitText, 'text/html');
                    const validationMessage = responseDocument.querySelector('#validation_check .cbg');
                    let responseMessage = validationMessage
                        ? (validationMessage.textContent || '').replace(/\s+/g, ' ').trim()
                        : '';

                    if (!responseMessage) {
                        responseDocument.querySelectorAll('script, style').forEach(element => element.remove());
                        responseMessage = (responseDocument.body.textContent || '')
                            .replace(/\s+/g, ' ')
                            .trim()
                            .substring(0, 300);
                    }

                    throw new Error(
                        responseMessage
                            ? '게시글 저장 실패: ' + responseMessage
                            : '게시글 저장 응답을 확인할 수 없습니다.'
                    );
                }
            }

            if (!submitResponse.ok || !submitResult.success) {
                throw new Error(submitResult.message || '게시글 저장에 실패했습니다.');
            }

            const responseWrId = parseInt(submitResult.wr_id, 10);
            if (Number.isInteger(responseWrId) && responseWrId > 0) {
                wrId = String(responseWrId);
            }

            const hasValidWrId = value => /^\d+$/.test(String(value)) && parseInt(value, 10) > 0;

            if (!isUpdate) {
                // 구형 서버 응답도 지원하되, 쿼리 주소와 짧은 주소를 모두 해석한다.
                if (!hasValidWrId(wrId) && submitResult.redirect_url) {
                    const redirectUrl = new URL(submitResult.redirect_url, window.location.href);
                    wrId = redirectUrl.searchParams.get('wr_id') || '';

                    if (!hasValidWrId(wrId)) {
                        const prettyUrlMatch = redirectUrl.pathname.match(/\/(\d+)\/?$/);
                        wrId = prettyUrlMatch ? prettyUrlMatch[1] : '';
                    }
                }

                // 응답 형식과 관계없이 이번 요청의 고유 토큰으로 생성된 글을 확정하고 토큰을 삭제한다.
                try {
                    const resolveFormData = new FormData();
                    resolveFormData.set('bo_table', '<?php echo $bo_table; ?>');
                    resolveFormData.set('request_token', createRequestToken);

                    const resolveResponse = await fetch('<?php echo $board_skin_url; ?>/ajax.resolve_post_id.php', {
                        method: 'POST',
                        body: resolveFormData
                    });
                    const resolveResult = await resolveResponse.json();

                    if (resolveResponse.ok && resolveResult.success && hasValidWrId(resolveResult.wr_id)) {
                        wrId = String(resolveResult.wr_id);
                    }
                } catch (resolveError) {
                    console.error('새 게시글 ID 확인 오류:', resolveError);
                }

                if (!hasValidWrId(wrId)) {
                    throw new Error('서버가 새 게시글 ID를 반환하지 않았습니다.');
                }
            }

            console.log(isUpdate ? '게시글 수정 완료:' : '새 게시글 생성 완료:', wrId);
            console.log('처리할 게시글 ID: ' + wrId);
            
            // 2단계: 내용이 있는 경우에만 청크 업로드
            if (trpgEditMode !== 'settings' && content.trim() && content.trim() !== '임시 내용 - 곧 업데이트됩니다.') {
                const chunkSize = 300 * 1024; // 300KB
                const totalChunks = Math.ceil(content.length / chunkSize);
                
                // 청크 업로드
                for (let i = 0; i < totalChunks; i++) {
                    const start = i * chunkSize;
                    const end = Math.min((i + 1) * chunkSize, content.length);
                    const chunk = content.substring(start, end);
                    
                    statusDiv.innerHTML = `콘텐츠 업로드 중... (${i+1}/${totalChunks})`;
                    
                    const chunkFormData = new FormData();
                    chunkFormData.append('bo_table', '<?php echo $bo_table; ?>');
                    chunkFormData.append('wr_id', wrId);
                    chunkFormData.append('chunk_index', i);
                    chunkFormData.append('total_chunks', totalChunks);
                    chunkFormData.append('chunk_data', chunk);
                    chunkFormData.append('action', 'upload');
                    
                    const response = await fetch('<?php echo $board_skin_url; ?>/ajax.content_chunk.php', {
                        method: 'POST',
                        body: chunkFormData
                    });
                    
                    const result = await response.json();
                    if (result.result !== 'success') {
                        throw new Error('청크 업로드 실패: ' + (result.message || '알 수 없는 오류'));
                    }
                }
                
                // 청크 처리 요청
                statusDiv.innerHTML = '콘텐츠 처리 중...';
                
                const finalFormData = new FormData();
                finalFormData.append('bo_table', '<?php echo $bo_table; ?>');
                finalFormData.append('wr_id', wrId);
                finalFormData.append('total_chunks', totalChunks);
                finalFormData.append('action', 'process');
                
                const processResponse = await fetch('<?php echo $board_skin_url; ?>/ajax.content_chunk.php', {
                    method: 'POST',
                    body: finalFormData
                });
                
                const processResult = await processResponse.json();
                if (processResult.result !== 'success') {
                    throw new Error('콘텐츠 처리 실패: ' + (processResult.message || '알 수 없는 오류'));
                }
            }
            
            // 성공 메시지 표시 후 페이지 이동
            statusDiv.innerHTML = trpgEditMode === 'settings' ? '로그 페이지 설정이 저장되었습니다.' : '로그가 저장되었습니다.';
            setTimeout(() => {
                statusDiv.remove();
                window.location.replace('<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $bo_table; ?>&wr_id=' + wrId);
            }, 2000);
            
        } catch (error) {
            statusDiv.innerHTML = '오류 발생: ' + error.message;
            console.error('게시글 처리 오류:', error);
            setTimeout(() => {
                statusDiv.remove();
            }, 5000);
        }
    }
});
</script>

</section>
<!-- } 게시물 작성/수정 끝 -->
