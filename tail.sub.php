<?php
if (!defined('_GNUBOARD_')) exit;
?>

<?php run_event('tail_sub'); ?>

<?php
// 위젯 시스템 - footer 위치 출력 (iframe 내부 제외)
$is_iframe = (
    (isset($_SERVER['HTTP_SEC_FETCH_DEST']) && $_SERVER['HTTP_SEC_FETCH_DEST'] == 'iframe') ||
    (defined('_IFRAME_') && _IFRAME_)
);

if (!$is_iframe && file_exists(G5_LIB_PATH.'/widget.lib.php')) {
    include_once(G5_LIB_PATH.'/widget.lib.php');
    display_widgets('footer');
}
?>

<?php
// iframe 내부일 경우 마우스 위치를 부모로 전달
if (isset($_GET['iframe_skip']) && $_GET['iframe_skip'] == '1') {
?>
<script>
// iframe 내부에서 마우스 위치를 부모로 전달
(function() {
    if (!window.parent || window.parent === window) return;

    document.addEventListener('mousemove', function(e) {
        window.parent.postMessage({
            type: 'mousePosition',
            x: e.clientX + window.frameElement.getBoundingClientRect().left,
            y: e.clientY + window.frameElement.getBoundingClientRect().top
        }, '*');
    }, { passive: true });

    // 클릭 이벤트도 전달
    document.addEventListener('click', function(e) {
        window.parent.postMessage({
            type: 'mouseClick',
            x: e.clientX + window.frameElement.getBoundingClientRect().left,
            y: e.clientY + window.frameElement.getBoundingClientRect().top
        }, '*');
    });
})();
</script>
<?php
}
?>

</body>
</html>
<?php echo html_end(); ?>
