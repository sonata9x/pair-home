<?php
/**
 * 관심 누른 사람 목록 AJAX 핸들러
 */
include_once('./_common.php');

header('Content-Type: application/json; charset=utf-8');

$bo_table = isset($_GET['bo_table']) ? clean_xss_tags($_GET['bo_table']) : '';
$wr_id = isset($_GET['wr_id']) ? (int)$_GET['wr_id'] : 0;

if (!$bo_table || !$wr_id) {
    echo json_encode(['result' => 'error', 'message' => '필수 파라미터가 누락되었습니다.']);
    exit;
}

$likers = get_post_likers($bo_table, $wr_id, 50);

$list = array();
foreach ($likers as $liker) {
    $name = '';
    $portrait = '';
    if ($liker['mb_id']) {
        if (function_exists('get_character')) {
            $lk_char = get_character($liker['mb_id']);
            if (!empty($lk_char['ch_name'])) $name = $lk_char['ch_name'];
            if (!empty($lk_char['ch_portrait_image'])) $portrait = $lk_char['ch_portrait_image'];
        }
        if (!$name) $name = $liker['mb_name'] ? $liker['mb_name'] : '';
    } else {
        $name = '익명';
    }

    $list[] = [
        'mb_id' => $liker['mb_id'],
        'name' => $name,
        'profile' => $liker['mb_signature'] ?? '',
        'portrait' => $portrait,
        'datetime' => date('Y-m-d H:i', strtotime($liker['liked_datetime']))
    ];
}

echo json_encode([
    'result' => 'success',
    'count' => count($list),
    'likers' => $list
]);
