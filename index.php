<?php
include_once('./_common.php');
define('_INDEX_', true);

// use_intro 설정 확인하여 iframe src 결정
if ($design['use_intro'] == '1' && !$is_member) {
    // intro를 사용하고 비회원이면 intro.php 표시
    $iframe_src = G5_URL.'/intro.php';
} else {
    // 그 외에는 메인 링크
    $iframe_src = get_main_link();
}

// iframe_skip 파라미터 추가 (이미 ?가 있으면 &를 사용)
if (strpos($iframe_src, '?') !== false) {
    $iframe_src .= '&iframe_skip=1';
} else {
    $iframe_src .= '?iframe_skip=1';
}

include_once('./head.sub.php');
?>

<!-- BGM -->
<?php if(isset($design['site_bgm_url']) && $design['site_bgm_url']) { ?>
<?php include_once(G5_PATH.'/bgm.php'); ?>
<?php } ?>

<div id="wrapper">
    <iframe
        src="<?=$iframe_src?>"
        name="frm_main"
        id="main"
        frameborder="0"
        scrolling="auto"
        allowTransparency="true">
    </iframe>
</div>

<script>
// iframe에서 postMessage로 네비게이션 요청 받기
window.addEventListener('message', function(event) {
    if (event.data.type === 'navigate') {
        var url = event.data.url;

        // iframe_skip 파라미터 추가
        if (url.indexOf('?') > -1) {
            url = url + '&iframe_skip=1';
        } else {
            url = url + '?iframe_skip=1';
        }

        document.getElementById('main').src = url;
    }
});
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>
