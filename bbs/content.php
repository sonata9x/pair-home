<?php
include_once('./_common.php');

$co_id = isset($_GET['co_id']) ? preg_replace('/[^a-z0-9_]/i', '', $_GET['co_id']) : 0;
$co_seo_title = isset($_GET['co_seo_title']) ? clean_xss_tags($_GET['co_seo_title'], 1, 1) : '';

// dbconfig파일에 $g5['content_table'] 배열변수가 있는지 체크
if (!isset($g5['content_table'])) {
    die('<meta charset="utf-8">관리자 모드에서 게시판관리->내용 관리를 먼저 확인해 주세요.');
}

// 내용
if ($co_seo_title) {
    $co = get_content_by_field($g5['content_table'], 'content', 'co_seo_title', generate_seo_title($co_seo_title));
    $co_id = isset($co['co_id']) ? $co['co_id'] : 0;
} else {
    $co = get_content_db($co_id);
}

if (!(isset($co['co_seo_title']) && $co['co_seo_title']) && isset($co['co_id']) && $co['co_id']) {
    seo_title_update($g5['content_table'], $co['co_id'], 'content');
}

if (!(isset($co['co_id']) && $co['co_id']))
    alert('등록된 내용이 없습니다.');

$g5['title'] = $co['co_subject'];

if ($co['co_include_head'] && is_include_path_check($co['co_include_head']))
    @include_once($co['co_include_head']);
else
    include_once('./_head.php');

// KVE-2019-0828 취약점 내용
$co['co_tag_filter_use'] = 1;
$str = conv_content($co['co_content'], $co['co_html'], $co['co_tag_filter_use']);

// $src 를 $dst 로 변환
$src = $dst = array();
$src[] = "/{{홈페이지제목}}/";
$dst[] = $config['cf_title'];
$str = preg_replace($src, $dst, $str);

// 관리자 수정 버튼
if ($is_admin)
    echo '<div class="ctt_admin"><a href="'.G5_ADMIN_URL.'/contentform.php?w=u&amp;co_id='.$co_id.'" class="btn_admin btn"><span class="sound_only">내용 수정</span><i class="fa fa-cog fa-spin fa-fw"></i></a></div>';

// 상단 이미지
$himg = G5_DATA_PATH.'/content/'.$co_id.'_h';
if (file_exists($himg))
    echo '<div id="ctt_himg" class="ctt_img"><img src="'.G5_DATA_URL.'/content/'.$co_id.'_h" alt=""></div>';

// 내용 출력
echo '<div id="ctt" class="ctt">'.$str.'</div>';

// 하단 이미지
$timg = G5_DATA_PATH.'/content/'.$co_id.'_t';
if (file_exists($timg))
    echo '<div id="ctt_timg" class="ctt_img"><img src="'.G5_DATA_URL.'/content/'.$co_id.'_t" alt=""></div>';

if ($co['co_include_tail'] && is_include_path_check($co['co_include_tail']))
    @include_once($co['co_include_tail']);
else
    include_once('./_tail.php');