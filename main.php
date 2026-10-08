<?php
include_once('./_common.php');
define('_MAIN_', true);
define('_IFRAME_', true);

if (!isset($_GET['iframe_skip']) && !isset($_SERVER['HTTP_REFERER'])) {
    goto_url_top(G5_URL.'/index.php');
    exit;
}

include_once(G5_LIB_PATH.'/pair_home_grid.lib.php');
// The earlier freeform themes remain in main.legacy.php with their original data.
$pair_grid_document = json_decode(get_design_config('pair_home_grid_document', ''), true);
$pair_grid_document = is_array($pair_grid_document) ? $pair_grid_document : array();
if (isset($pair_grid_document['widgets']) && is_array($pair_grid_document['widgets'])) {
    $pair_layout_source = $pair_grid_document['widgets'];
    if ((int)($pair_grid_document['version'] ?? 0) < 4) {
        $pair_layout_source = pair_home_grid_migrate_12_to_30($pair_layout_source);
    }
} else {
    $pair_layout_source = pair_home_grid_default_layout();
}
$pair_layout = pair_home_grid_sanitize_layout($pair_layout_source);
$pair_background_color = $pair_grid_document['backgroundColor'] ?? '#E8F0F4';
if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $pair_background_color)) $pair_background_color = '#E8F0F4';
$pair_background_image = pair_home_clean_url($pair_grid_document['backgroundImage'] ?? '');
$pair_representative_color = strtoupper((string)($pair_grid_document['representativeColor'] ?? '#7FAFD1'));
if (!preg_match('/^#[0-9A-F]{6}$/', $pair_representative_color)) $pair_representative_color = '#7FAFD1';
$pair_default_theme = (string)($pair_grid_document['defaultTheme'] ?? 'flat');
if (!in_array($pair_default_theme, array('flat','line','bold','soft','pixel','glass'), true)) $pair_default_theme = 'flat';
$pair_token = '';
if ($is_admin) {
    $pair_token = (string)get_session('ss_pair_home_token');
    if ($pair_token === '') {
        $pair_token = bin2hex(random_bytes(24));
        set_session('ss_pair_home_token', $pair_token);
    }
}

$pair_boards = array();
if ($is_admin && !empty($g5['board_table'])) {
    $pair_board_result = sql_query("SELECT bo_table, bo_subject FROM {$g5['board_table']} ORDER BY bo_order, bo_table", false);
    if ($pair_board_result) {
        while ($pair_board_row = sql_fetch_array($pair_board_result)) {
            $pair_board_id = preg_replace('/[^a-zA-Z0-9_]/', '', (string)($pair_board_row['bo_table'] ?? ''));
            if ($pair_board_id === '') continue;
            $pair_board_label = trim(strip_tags((string)($pair_board_row['bo_subject'] ?? '')));
            if ($pair_board_label === '') $pair_board_label = $pair_board_id;
            $pair_boards[] = array(
                'id'=>$pair_board_id,
                'label'=>mb_substr($pair_board_label, 0, 60),
                'url'=>G5_BBS_URL.'/board.php?bo_table='.rawurlencode($pair_board_id)
            );
        }
    }
}

include_once(G5_PATH.'/head.sub.php');
$pair_bootstrap = array(
    'admin'=>(bool)$is_admin,
    'apiUrl'=>G5_URL.'/api/pair_home.php',
    'token'=>$pair_token,
    'backgroundColor'=>strtoupper($pair_background_color),
    'backgroundImage'=>$pair_background_image,
    'representativeColor'=>$pair_representative_color,
    'defaultTheme'=>$pair_default_theme,
    'layoutMode'=>'grid',
    'boards'=>$pair_boards,
    'widgets'=>$pair_layout
);
?>
<link rel="stylesheet" href="<?php echo G5_CSS_URL; ?>/pair-home.css?v=<?php echo @filemtime(G5_PATH.'/css/pair-home.css'); ?>">
<link rel="stylesheet" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/variable/pretendardvariable-dynamic-subset.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/galmuri/dist/galmuri.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Gowun+Batang:wght@400;700&amp;family=Gowun+Dodum&amp;family=IBM+Plex+Sans+KR:wght@300;400;500;600;700&amp;family=Nanum+Pen+Script&amp;family=Noto+Sans+KR:wght@300;400;500;600;700&amp;family=Noto+Serif+KR:wght@400;500;600;700&amp;display=swap">
<link rel="stylesheet" href="<?php echo G5_CSS_URL; ?>/pair-home-editor.css?v=<?php echo @filemtime(G5_PATH.'/css/pair-home-editor.css'); ?>">

<link rel="stylesheet" href="<?php echo G5_CSS_URL; ?>/pair-home-grid.css?v=<?php echo @filemtime(G5_PATH.'/css/pair-home-grid.css'); ?>">

<div class="pair-home-shell pair-grid-shell" id="pair-home-shell">
    <section class="pair-home-frame pair-grid-frame" id="pair-home-frame" aria-label="페어홈 블록 배치 영역">
        <div class="pair-home-canvas" id="pair-home-canvas" aria-label="페어홈 메인 · 30열 격자"></div>
    </section>
    <?php if ($is_admin) { ?>
    <button type="button" class="pair-editor-toggle" id="pair-editor-toggle" aria-label="꾸미기" title="꾸미기">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4l11-11a2.8 2.8 0 0 0-4-4L4 16v4Z"/><path d="m13.5 6.5 4 4M4 20l3-1-2-2-1 3Z"/></svg>
        <span class="pair-editor-toggle-label">꾸미기</span>
    </button>
    <div class="pair-editor-bar" id="pair-editor-bar" aria-label="위젯 추가 도구">
        <button type="button" data-add="profile">프로필</button>
        <button type="button" data-add="image">이미지 프레임</button>
        <button type="button" data-add="webframe">홈페이지 프레임</button>
        <button type="button" data-add="bgm">BGM</button>
        <button type="button" data-add="dday">디데이</button>
        <button type="button" data-add="sticker">스티커</button>
        <button type="button" data-add="category">카테고리</button>
        <button type="button" data-add="text">텍스트</button>
        <button type="button" data-add="separator">구분선</button>
        <button type="button" data-add="label">라벨</button>
        <button type="button" data-add="linkbanner">연결 배너</button>
        <button type="button" data-add="calendar">캘린더</button>
        <button type="button" id="pair-background-open">배경</button>
        <button type="button" id="pair-reset" disabled>변경 취소</button>
        <button type="button" class="pair-primary" id="pair-save">저장</button>
    </div>
    <p class="pair-grid-help">블록은 한 칸씩 이동 · 모서리로 크기 조절 · 스티커는 자유롭게</p>
    <aside class="pair-editor-panel" id="pair-editor-panel" aria-label="위젯 설정"></aside>
    <input type="file" id="pair-asset-upload" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
    <input type="file" id="pair-background-upload" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
    <input type="file" id="pair-audio-upload" accept="audio/mpeg,audio/ogg,audio/wav,audio/mp4,.mp3,.ogg,.wav,.m4a" hidden>
    <div class="pair-editor-status" id="pair-editor-status" role="status" aria-live="polite"></div>
    <?php } ?>
</div>

<script type="application/json" id="pair-home-bootstrap"><?php echo json_encode($pair_bootstrap, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP); ?></script>
<script src="<?php echo G5_JS_URL; ?>/pair-home-grid-layout.js?v=<?php echo @filemtime(G5_PATH.'/js/pair-home-grid-layout.js'); ?>"></script>
<script src="<?php echo G5_JS_URL; ?>/pair-home-grid.js?v=<?php echo @filemtime(G5_PATH.'/js/pair-home-grid.js'); ?>"></script>
<?php include_once(G5_PATH.'/tail.sub.php'); ?>
