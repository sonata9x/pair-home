<?php
if (!defined('_GNUBOARD_')) exit;

// TRPG_up 전용: wr_content 에디터만 ra0-content 클래스 제거
// wr_5 (개요)는 ra0-content 유지
$editor_html = str_replace(
    'class="ra0-editor-viewer ra0-content"',
    'class="ra0-editor-viewer"',
    $editor_html
);

// TRPG_up 전용: 수정 모드에서 wr_content 내용을 에디터에 로드
if (($w == 'u' || $w == 'r') && !empty($content)) {
    // HTML을 JavaScript 문자열로 안전하게 변환
    $content_js = str_replace(['\\', "'", "\r", "\n"], ['\\\\', "\\'", '', '\\n'], $content);

    $editor_html .= "
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 에디터가 초기화된 후 내용 주입
        setTimeout(function() {
            var viewer = document.getElementById('wr_content_viewer');
            var source = document.getElementById('wr_content');
            if (viewer && source) {
                viewer.innerHTML = '{$content_js}';
                source.value = viewer.innerHTML;
            }
        }, 100);
    });
    </script>
    ";
}

// TRPG_up 전용: wr_content 에디터에서 ra0-content wrapper 제거
$editor_html .= "
<script>
// wr_content 에디터 초기화 후 ra0-content wrapper 제거 옵션 설정
document.addEventListener('DOMContentLoaded', function() {
    // RA0Editor 초기화가 완료될 때까지 대기
    setTimeout(function() {
        if (window.RA0Editor && RA0Editor.instances['wr_content']) {
            // wr_content 에디터에 noWrapper 플래그 설정
            RA0Editor.instances['wr_content'].noWrapper = true;
        }
    }, 200);
});
</script>
";

// TRPG_up 전용: 에디터 저장 로직
// wr_content: ra0-content wrapper 없이 순수 HTML만 저장
// wr_5 (개요): 기본 RA0 에디터 방식 (ra0-content wrapper 포함)
$editor_js = "
// wr_content 에디터 - wrapper 없이 저장
var ra0_viewer_wr_content = document.getElementById('wr_content_viewer');
var ra0_source_wr_content = document.getElementById('wr_content');
if (ra0_viewer_wr_content && ra0_source_wr_content) {
    var clonedContent = ra0_viewer_wr_content.cloneNode(true);
    var codeTags = clonedContent.querySelectorAll('code');
    codeTags.forEach(function(code) {
        var text = code.textContent;
        code.textContent = '';
        code.innerHTML = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#039;');
    });
    // TRPG_up: ra0-content wrapper 제거 (순수 HTML만 저장)
    ra0_source_wr_content.value = clonedContent.innerHTML;
}
";

// RA0 에디터 사용 시에만 get_editor_js 호출
if ($config['cf_editor'] == 'ra0-editor') {
    $editor_js .= get_editor_js('wr_content', $is_dhtml_editor);
    $editor_js .= get_editor_js('wr_5', $is_dhtml_editor);
} else {
    // 다른 에디터 사용 시 기본 처리
    $editor_js .= chk_editor_js('wr_content', $is_dhtml_editor);
    $editor_js .= chk_editor_js('wr_5', $is_dhtml_editor);
}
?>
