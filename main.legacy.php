<?php
include_once('./_common.php');
define('_MAIN_', true);
define('_IFRAME_', true);

if (!isset($_GET['iframe_skip']) && !isset($_SERVER['HTTP_REFERER'])) {
    goto_url_top(G5_URL.'/index.php');
    exit;
}

include_once(G5_LIB_PATH.'/pair_home.lib.php');
$saved_layout = json_decode(get_design_config('pair_home_layout', ''), true);
$pair_layout = pair_home_sanitize_layout(is_array($saved_layout) ? $saved_layout : pair_home_default_layout());
$pair_background_color = get_design_config('pair_home_background_color', '#F2ECE5');
if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $pair_background_color)) $pair_background_color = '#F2ECE5';
$pair_background_image = pair_home_clean_url(get_design_config('pair_home_background_image', ''));
$pair_frame_theme = get_design_config('pair_home_frame_theme', 'diary');
if (!in_array($pair_frame_theme, array('web', 'diary', 'mac'), true)) $pair_frame_theme = 'diary';
$pair_token = '';
if ($is_admin) {
    $pair_token = (string)get_session('ss_pair_home_token');
    if ($pair_token === '') {
        $pair_token = bin2hex(random_bytes(24));
        set_session('ss_pair_home_token', $pair_token);
    }
}

include_once(G5_PATH.'/head.sub.php');
$pair_bootstrap = array(
    'admin'=>(bool)$is_admin,
    'apiUrl'=>G5_URL.'/api/pair_home.php',
    'token'=>$pair_token,
    'backgroundColor'=>strtoupper($pair_background_color),
    'backgroundImage'=>$pair_background_image,
    'frameTheme'=>$pair_frame_theme,
    'widgets'=>$pair_layout
);
?>
<link rel="stylesheet" href="<?php echo G5_CSS_URL; ?>/pair-home.css?v=<?php echo @filemtime(G5_PATH.'/css/pair-home.css'); ?>">
<link rel="stylesheet" href="<?php echo G5_CSS_URL; ?>/pair-home-editor.css?v=<?php echo @filemtime(G5_PATH.'/css/pair-home-editor.css'); ?>">

<div class="pair-home-shell" id="pair-home-shell">
    <section class="pair-home-frame theme-<?php echo $pair_frame_theme; ?>" id="pair-home-frame" aria-label="페어홈 꾸밈 영역">
        <header class="pair-theme-chrome" aria-hidden="true">
            <span class="pair-theme-controls"><i></i><i></i><i></i></span>
            <span class="pair-theme-title">PAIR HOME</span>
            <span class="pair-theme-window-buttons"><i></i><i></i><i></i></span>
        </header>
        <div class="pair-theme-page-split" aria-hidden="true"></div>
        <div class="pair-theme-binding" aria-hidden="true">
            <?php for ($pair_ring = 0; $pair_ring < 7; $pair_ring++) { ?><i></i><?php } ?>
        </div>
        <div class="pair-theme-tabs" aria-hidden="true">
            <span class="pair-theme-tab"><svg viewBox="0 0 24 24"><path d="m3 10 9-7 9 7M5 9v12h14V9M9 21v-8h6v8"/></svg></span>
            <span class="pair-theme-tab"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg></span>
            <span class="pair-theme-tab"><svg viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg></span>
            <span class="pair-theme-tab"><svg viewBox="0 0 24 24"><path d="M12 5v16M12 5Q6 1 2 4v15q5-2 10 2 5-4 10-2V4q-4-3-10 1Z"/></svg></span>
            <span class="pair-theme-tab"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8" cy="8" r="2"/><path d="m3 18 6-6 4 4 4-6 4 7"/></svg></span>
        </div>
        <div class="pair-home-canvas" id="pair-home-canvas" aria-label="페어홈 메인"></div>
    </section>
    <?php if ($is_admin) { ?>
    <button type="button" class="pair-editor-toggle" id="pair-editor-toggle">꾸미기</button>
    <div class="pair-editor-bar" id="pair-editor-bar" aria-label="위젯 추가 도구">
        <button type="button" data-add="image">이미지 프레임</button>
        <button type="button" data-add="bgm">BGM</button>
        <button type="button" data-add="dday">디데이</button>
        <button type="button" data-add="sticker">스티커</button>
        <button type="button" data-add="category">카테고리</button>
        <button type="button" data-add="text">텍스트</button>
        <button type="button" id="pair-background-open">배경</button>
        <button type="button" id="pair-reset" disabled>변경 취소</button>
        <button type="button" class="pair-primary" id="pair-save">저장</button>
    </div>
    <aside class="pair-editor-panel" id="pair-editor-panel" aria-label="위젯 설정"></aside>
    <input type="file" id="pair-asset-upload" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
    <input type="file" id="pair-background-upload" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
    <input type="file" id="pair-audio-upload" accept="audio/mpeg,audio/ogg,audio/wav,audio/mp4,.mp3,.ogg,.wav,.m4a" hidden>
    <div class="pair-editor-status" id="pair-editor-status" role="status" aria-live="polite"></div>
    <?php } ?>
</div>

<script type="application/json" id="pair-home-bootstrap"><?php echo json_encode($pair_bootstrap, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP); ?></script>
<script src="<?php echo G5_JS_URL; ?>/pair-home.js?v=<?php echo @filemtime(G5_PATH.'/js/pair-home.js'); ?>"></script>
<?php include_once(G5_PATH.'/tail.sub.php'); ?>
