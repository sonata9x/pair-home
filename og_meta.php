<?php
/**
 * OG 메타 태그 출력
 * 링크 공유 시 미리보기용
 */

if (!defined('_GNUBOARD_')) exit;

// 기본값 설정
$og_title = $g5['title'] ?? $config['cf_title'];
$og_desc = $config['cf_description'] ?? $config['cf_title'];
$og_image = $config['cf_image'] ?? '';
$og_url = G5_URL . $_SERVER['REQUEST_URI'];
$og_site_name = $config['cf_title'];

// 게시판 글 페이지인 경우 해당 글 정보로 덮어쓰기
if (isset($write) && is_array($write) && !empty($write['wr_id'])) {
    // 제목
    if (!empty($write['wr_subject'])) {
        $og_title = strip_tags($write['wr_subject']);
    }

    // 설명: 부제 > 본문
    if (!empty($write['wr_title'])) {
        $og_desc = strip_tags($write['wr_title']);
    } elseif (!empty($write['wr_content'])) {
        $og_desc = strip_tags(cut_str($write['wr_content'], 100, '...'));
    }

    // 이미지: wr_img > 첨부파일 > wr_link1
    if (!empty($write['wr_img'])) {
        $og_image = (strpos($write['wr_img'], 'http') === 0)
            ? $write['wr_img']
            : G5_DATA_URL . '/file/' . $bo_table . '/' . $write['wr_img'];
    } elseif (isset($bo_table) && $bo_table) {
        $og_file = sql_fetch("SELECT bf_file FROM {$g5['board_file_table']} WHERE bo_table = '{$bo_table}' AND wr_id = '{$write['wr_id']}' ORDER BY bf_no LIMIT 1");
        if ($og_file && $og_file['bf_file']) {
            $og_image = G5_DATA_URL . '/file/' . $bo_table . '/' . $og_file['bf_file'];
        } elseif (!empty($write['wr_link1']) && strpos($write['wr_link1'], 'http') === 0) {
            $og_image = $write['wr_link1'];
        }
    }
}
?>
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?php echo htmlspecialchars($og_site_name); ?>">
<meta property="og:title" content="<?php echo htmlspecialchars($og_title); ?>">
<meta property="og:description" content="<?php echo htmlspecialchars($og_desc); ?>">
<meta property="og:url" content="<?php echo htmlspecialchars($og_url); ?>">
<?php if ($og_image): ?>
<meta property="og:image" content="<?php echo htmlspecialchars($og_image); ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo htmlspecialchars($og_title); ?>">
<meta name="twitter:description" content="<?php echo htmlspecialchars($og_desc); ?>">
<?php if ($og_image): ?>
<meta name="twitter:image" content="<?php echo htmlspecialchars($og_image); ?>">
<?php endif; ?>
