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
    'widgets'=>$pair_layout
);
?>
<link rel="stylesheet" href="<?php echo G5_CSS_URL; ?>/pair-home.css?v=<?php echo @filemtime(G5_PATH.'/css/pair-home.css'); ?>">

<div class="pair-home-shell" id="pair-home-shell">
    <div class="pair-home-canvas" id="pair-home-canvas" aria-label="페어홈 메인"></div>
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
        <button type="button" class="pair-primary" id="pair-save">저장</button>
    </div>
    <aside class="pair-editor-panel" id="pair-editor-panel" aria-label="위젯 설정"></aside>
    <input type="file" id="pair-asset-upload" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
    <input type="file" id="pair-background-upload" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
    <div class="pair-editor-status" id="pair-editor-status" role="status" aria-live="polite"></div>
    <?php } ?>
</div>

<script type="application/json" id="pair-home-bootstrap"><?php echo json_encode($pair_bootstrap, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP); ?></script>
<script src="<?php echo G5_JS_URL; ?>/pair-home.js?v=<?php echo @filemtime(G5_PATH.'/js/pair-home.js'); ?>"></script>
<?php include_once(G5_PATH.'/tail.sub.php'); ?>
