<?php
/**
 * RA0 Edition - Latest Skin: Diary (다이어리 카드 스타일)
 * 참고 이미지: DIARY LOG 섹션 - 아이콘 + 제목 + 내용 요약
 */
if (!defined('_GNUBOARD_')) exit;

echo '<link rel="stylesheet" href="'.$latest_skin_url.'/style.css">';

$list_count = (is_array($list) && $list) ? count($list) : 0;
$is_all_latest = ($bo_subject == '전체 최신글');
$display_title = $is_all_latest ? 'DIARY LOG' : strtoupper($bo_subject);

// 첫 번째 글 가져오기 (메인 표시용)
$main_post = ($list_count > 0) ? $list[0] : null;

// 내용 미리보기 (wr_content에서 가져오기)
$content_preview = '';
if ($main_post) {
    global $g5;
    $write_table = $g5['write_prefix'] . $main_post['bo_table'];
    $write = sql_fetch("SELECT wr_content FROM {$write_table} WHERE wr_id = {$main_post['wr_id']}");
    if ($write) {
        $content_preview = strip_tags($write['wr_content']);
        $content_preview = mb_substr($content_preview, 0, 80) . '...';
    }
}
?>

<div class="diary-latest">
    <div class="latest-header">
        <span class="header-icon"><i class="fa fa-book-open"></i></span>
        <h3 class="latest-title"><?php echo $display_title; ?></h3>
    </div>

    <?php if ($main_post) { ?>
    <a href="<?php echo $main_post['href']; ?>" class="main-post">
        <h4 class="post-title"><?php echo $main_post['subject']; ?></h4>
        <?php if ($content_preview) { ?>
        <p class="post-content"><?php echo htmlspecialchars($content_preview); ?></p>
        <?php } ?>
        <div class="post-meta">
            <span class="post-date"><?php echo date('Y.m.d H:i', strtotime($main_post['datetime'])); ?></span>
        </div>
    </a>
    <?php } else { ?>
    <div class="latest-empty">
        <i class="fa fa-feather"></i>
        <span>아직 작성된 글이 없습니다.</span>
    </div>
    <?php } ?>
</div>
