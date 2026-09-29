<?php
if (!defined("_GNUBOARD_")) exit;

/**
 * timeline 스킨 - 게시글 저장 후처리
 *
 * 썸네일 리사이징 (400x400)
 */

// 썸네일 처리 (파일 업로드된 경우만)
if (!empty($wr_img) && strpos($wr_img, 'http') !== 0) {
    // wr_img에 이미 경로가 포함되어 있는지 확인
    if (strpos($wr_img, '/') !== false) {
        // 경로가 포함된 경우: data/file/xxx/thumb_xxx.png 형식
        $thumb_file_path = G5_DATA_PATH . '/' . $wr_img;
    } else {
        // 파일명만 있는 경우: thumb_xxx.png 형식
        $thumb_file_path = G5_DATA_PATH . '/file/' . $bo_table . '/' . $wr_img;
    }

    if (file_exists($thumb_file_path)) {
        // 썸네일 리사이징 (최대 400x400)
        if (function_exists('resize_image')) {
            resize_image($thumb_file_path, 400, 400);
        }
    }
}
?>
