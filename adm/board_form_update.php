<?php
$sub_menu = "200200";
include_once('./_common.php');

if ($w == 'u') {
    check_demo();
}

check_admin_token();

$gr_id = ''; // 그룹 시스템 제거됨
$bo_admin = isset($_POST['bo_admin']) ? preg_replace('/[^a-z0-9_\, \|\#]/i', '', $_POST['bo_admin']) : '';
$bo_subject = isset($_POST['bo_subject']) ? strip_tags(clean_xss_attributes($_POST['bo_subject'])) : '';

// 그룹 시스템 제거됨 - 그룹 ID 검증 제거
if (!$bo_table) {
    alert('게시판 TABLE명은 반드시 입력하세요.');
}
if (!preg_match("/^([A-Za-z0-9_]{1,20})$/", $bo_table)) {
    alert('게시판 TABLE명은 공백없이 영문자, 숫자, _ 만 사용 가능합니다. (20자 이내)');
}
if (!$bo_subject) {
    alert('게시판 제목을 입력하세요.');
}

// 게시판명이 금지된 단어로 되어 있으면
if ($w == '' && in_array($bo_table, get_bo_table_banned_word())) {
    alert('입력한 게시판 TABLE명을 사용할수 없습니다. 다른 이름으로 입력해 주세요.');
}

$bo_include_head = isset($_POST['bo_include_head']) ? preg_replace(array("#[\\\]+$#", "#(<\?php|<\?)#i"), "", substr($_POST['bo_include_head'], 0, 255)) : '';
$bo_include_tail = isset($_POST['bo_include_tail']) ? preg_replace(array("#[\\\]+$#", "#(<\?php|<\?)#i"), "", substr($_POST['bo_include_tail'], 0, 255)) : '';

// 관리자가 자동등록방지를 사용해야 할 경우
if ($board && (isset($board['bo_include_head']) && $board['bo_include_head'] !== $bo_include_head || $board['bo_include_tail'] !== $bo_include_tail) && function_exists('get_admin_captcha_by') && get_admin_captcha_by()) {
    if (file_exists(G5_CAPTCHA_PATH . '/captcha.lib.php')) {
        include_once(G5_CAPTCHA_PATH . '/captcha.lib.php');
    }

    if (function_exists('chk_captcha') && !chk_captcha()) {
        alert('자동등록방지 숫자가 틀렸습니다.');
    }
}

if ($file = $bo_include_head) {
    $file_ext = pathinfo($file, PATHINFO_EXTENSION);
    if (!$file_ext || !in_array($file_ext, array('php', 'htm', 'html')) || !preg_match('/^.*\.(php|htm|html)$/i', $file)) {
        alert('상단 파일 경로의 확장자는 php, htm, html 만 허용합니다.');
    }
}

if ($file = $bo_include_tail) {
    $file_ext = pathinfo($file, PATHINFO_EXTENSION);
    if (!$file_ext || !in_array($file_ext, array('php', 'htm', 'html')) || !preg_match('/^.*\.(php|htm|html)$/i', $file)) {
        alert('하단 파일 경로의 확장자는 php, htm, html 만 허용합니다.');
    }
}

if (!is_include_path_check($bo_include_head, 1)) {
    alert('상단 파일 경로에 포함시킬수 없는 문자열이 있습니다.');
}

if (!is_include_path_check($bo_include_tail, 1)) {
    alert('하단 파일 경로에 포함시킬수 없는 문자열이 있습니다.');
}

if (function_exists('filter_input_include_path')) {
    $bo_include_head = filter_input_include_path($bo_include_head);
    $bo_include_tail = filter_input_include_path($bo_include_tail);
}

$board_path = G5_DATA_PATH . '/file/' . $bo_table;

// 게시판 디렉토리 생성
@mkdir($board_path, G5_DIR_PERMISSION);
@chmod($board_path, G5_DIR_PERMISSION);

// 디렉토리에 있는 파일의 목록을 보이지 않게 한다.
$file = $board_path . '/index.php';
if ($f = @fopen($file, 'w')) {
    @fwrite($f, '');
    @fclose($f);
    @chmod($file, G5_FILE_PERMISSION);
}

// 분류에 & 나 = 는 사용이 불가하므로 2바이트로 바꾼다.
$src_char = array('&', '=');
$dst_char = array('＆', '〓');
$bo_category_list = isset($_POST['bo_category_list']) ? str_replace($src_char, $dst_char, $_POST['bo_category_list']) : '';
$str_bo_category_list = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\/\^\*]/", "", (string)$bo_category_list);

// 필요한 필드들만 처리
$bo_use_category = isset($_POST['bo_use_category']) ? (int) $_POST['bo_use_category'] : 0;
$bo_use_sideview = isset($_POST['bo_use_sideview']) ? (int) $_POST['bo_use_sideview'] : 0;
$bo_use_secret = isset($_POST['bo_use_secret']) ? (int) $_POST['bo_use_secret'] : 0;
$bo_use_dhtml_editor = isset($_POST['bo_use_dhtml_editor']) ? (int) $_POST['bo_use_dhtml_editor'] : 0;
$bo_list_level = isset($_POST['bo_list_level']) ? (int) $_POST['bo_list_level'] : 0;
$bo_read_level = isset($_POST['bo_read_level']) ? (int) $_POST['bo_read_level'] : 0;
$bo_write_level = isset($_POST['bo_write_level']) ? (int) $_POST['bo_write_level'] : 0;
// $bo_reply_level = isset($_POST['bo_reply_level']) ? (int) $_POST['bo_reply_level'] : 0; // 삭제된 컬럼
$bo_comment_level = isset($_POST['bo_comment_level']) ? (int) $_POST['bo_comment_level'] : 0;
$bo_html_level = isset($_POST['bo_html_level']) ? (int) $_POST['bo_html_level'] : 0;
// $bo_link_level = isset($_POST['bo_link_level']) ? (int) $_POST['bo_link_level'] : 0; // 삭제된 컬럼
$bo_upload_level = isset($_POST['bo_upload_level']) ? (int) $_POST['bo_upload_level'] : 0;
$bo_download_level = isset($_POST['bo_download_level']) ? (int) $_POST['bo_download_level'] : 0;
// $bo_count_modify = isset($_POST['bo_count_modify']) ? (int) $_POST['bo_count_modify'] : 0; // 삭제된 컬럼
// $bo_count_delete = isset($_POST['bo_count_delete']) ? (int) $_POST['bo_count_delete'] : 0; // 삭제된 컬럼
$bo_read_point = isset($_POST['bo_read_point']) ? (int) $_POST['bo_read_point'] : 0;
$bo_write_point = isset($_POST['bo_write_point']) ? (int) $_POST['bo_write_point'] : 0;
$bo_comment_point = isset($_POST['bo_comment_point']) ? (int) $_POST['bo_comment_point'] : 0;
$bo_download_point = isset($_POST['bo_download_point']) ? (int) $_POST['bo_download_point'] : 0;
$bo_skin = isset($_POST['bo_skin']) ? clean_xss_tags($_POST['bo_skin'], 1, 1) : '';
$bo_page_rows = isset($_POST['bo_page_rows']) ? (int) $_POST['bo_page_rows'] : 0;
$bo_subject_len = isset($_POST['bo_subject_len']) ? (int) $_POST['bo_subject_len'] : 0;
// $bo_new = isset($_POST['bo_new']) ? (int) $_POST['bo_new'] : 0; // 삭제된 컬럼
// $bo_hot = isset($_POST['bo_hot']) ? (int) $_POST['bo_hot'] : 0; // 삭제된 컬럼
$bo_image_width = isset($_POST['bo_image_width']) ? (int) $_POST['bo_image_width'] : 0;
$bo_gallery_cols = isset($_POST['bo_gallery_cols']) ? (int) $_POST['bo_gallery_cols'] : 0;
$bo_gallery_width = isset($_POST['bo_gallery_width']) ? (int) $_POST['bo_gallery_width'] : 0;
$bo_gallery_height = isset($_POST['bo_gallery_height']) ? (int) $_POST['bo_gallery_height'] : 0;
$bo_table_width = isset($_POST['bo_table_width']) ? (int) $_POST['bo_table_width'] : 0;
$bo_upload_count = isset($_POST['bo_upload_count']) ? (int) $_POST['bo_upload_count'] : 0;
$bo_upload_size = isset($_POST['bo_upload_size']) ? (int) $_POST['bo_upload_size'] : 0;
$bo_reply_order = isset($_POST['bo_reply_order']) ? (int) $_POST['bo_reply_order'] : 0;
$bo_use_search = isset($_POST['bo_use_search']) ? (int) $_POST['bo_use_search'] : 0;
$bo_sort_field = isset($_POST['bo_sort_field']) ? clean_xss_tags($_POST['bo_sort_field'], 1, 1) : '';
$bo_order = isset($_POST['bo_order']) ? (int) $_POST['bo_order'] : 0;
$bo_type = isset($_POST['bo_type']) ? clean_xss_tags($_POST['bo_type'], 1, 1) : 'normal';
$bo_content_head = isset($_POST['bo_content_head']) ? $_POST['bo_content_head'] : '';
$bo_content_tail = isset($_POST['bo_content_tail']) ? $_POST['bo_content_tail'] : '';
$bo_insert_content = isset($_POST['bo_insert_content']) ? $_POST['bo_insert_content'] : '';

if (strpbrk($bo_skin, "?%*:|\"<>") !== false) {
    alert('스킨 디렉토리명 오류!');
}

// 여분필드 처리 (이중 이스케이프 방지)
for ($i = 1; $i <= 10; $i++) {
    ${'bo_' . $i . '_subj'} = isset($_POST['bo_' . $i . '_subj']) ? addslashes(stripslashes($_POST['bo_' . $i . '_subj'])) : '';
    ${'bo_' . $i} = isset($_POST['bo_' . $i]) ? addslashes(stripslashes($_POST['bo_' . $i])) : '';
}

$sql_common = " bo_subject = '{$bo_subject}',
                bo_type = '{$bo_type}',
                bo_admin = '{$bo_admin}',
                bo_list_level = '{$bo_list_level}',
                bo_read_level = '{$bo_read_level}',
                bo_write_level = '{$bo_write_level}',
                bo_comment_level = '{$bo_comment_level}',
                bo_html_level = '{$bo_html_level}',
                bo_upload_level = '{$bo_upload_level}',
                bo_download_level = '{$bo_download_level}',
                bo_read_point = '{$bo_read_point}',
                bo_write_point = '{$bo_write_point}',
                bo_comment_point = '{$bo_comment_point}',
                bo_download_point = '{$bo_download_point}',
                bo_use_category = '{$bo_use_category}',
                bo_category_list = '{$str_bo_category_list}',
                bo_use_sideview = '{$bo_use_sideview}',
                bo_use_secret = '{$bo_use_secret}',
                bo_use_dhtml_editor = '{$bo_use_dhtml_editor}',
                bo_table_width = '{$bo_table_width}',
                bo_subject_len = '{$bo_subject_len}',
                bo_page_rows = '{$bo_page_rows}',
                bo_image_width = '{$bo_image_width}',
                bo_skin = '{$bo_skin}',
                bo_gallery_cols = '{$bo_gallery_cols}',
                bo_gallery_width = '{$bo_gallery_width}',
                bo_gallery_height = '{$bo_gallery_height}',
                bo_upload_count = '{$bo_upload_count}',
                bo_upload_size = '{$bo_upload_size}',
                bo_reply_order = '{$bo_reply_order}',
                bo_use_search = '{$bo_use_search}',
                bo_sort_field = '{$bo_sort_field}',
                bo_order = '{$bo_order}',
                bo_insert_content = '{$bo_insert_content}',
                bo_1_subj = '{$bo_1_subj}',
                bo_2_subj = '{$bo_2_subj}',
                bo_3_subj = '{$bo_3_subj}',
                bo_4_subj = '{$bo_4_subj}',
                bo_5_subj = '{$bo_5_subj}',
                bo_6_subj = '{$bo_6_subj}',
                bo_7_subj = '{$bo_7_subj}',
                bo_8_subj = '{$bo_8_subj}',
                bo_9_subj = '{$bo_9_subj}',
                bo_10_subj = '{$bo_10_subj}',
                bo_1 = '{$bo_1}',
                bo_2 = '{$bo_2}',
                bo_3 = '{$bo_3}',
                bo_4 = '{$bo_4}',
                bo_5 = '{$bo_5}',
                bo_6 = '{$bo_6}',
                bo_7 = '{$bo_7}',
                bo_8 = '{$bo_8}',
                bo_9 = '{$bo_9}',
                bo_10 = '{$bo_10}' ";

// 최고 관리자인 경우에만 수정가능
if ($is_admin === 'super') {
    $sql_common .= ", bo_include_head = '" . sql_real_escape_string($bo_include_head) . "',
                      bo_include_tail = '" . sql_real_escape_string($bo_include_tail) . "',
                      bo_content_head = '{$bo_content_head}',
                      bo_content_tail = '{$bo_content_tail}' ";
}

if ($w == '') {
    $row = sql_fetch(" select count(*) as cnt from {$g5['board_table']} where bo_table = '{$bo_table}' ");
    if ($row['cnt']) {
        alert($bo_table . ' 은(는) 이미 존재하는 TABLE 입니다.');
    }

    $sql = " insert into {$g5['board_table']}
                set bo_table = '{$bo_table}',
                    bo_count_write = '0',
                    bo_count_comment = '0',
                    $sql_common ";
    sql_query($sql);

    // 게시판 테이블 생성
    $file = file('./sql_write.sql');
    $file = get_db_create_replace($file);

    $sql = implode("\n", $file);
    $create_table = $g5['write_prefix'] . $bo_table;

    // sql_board.sql 파일의 테이블명을 변환
    $source = array('/__TABLE_NAME__/', '/;/');
    $target = array($create_table, '');
    $sql = preg_replace($source, $target, $sql);
    sql_query($sql, false);
    
} elseif ($w == 'u') {
    // 게시판의 글 수
    $sql = " select count(*) as cnt from {$g5['write_prefix']}{$bo_table} where wr_is_comment = 0 ";
    $row = sql_fetch($sql);
    $bo_count_write = $row['cnt'];

    // 게시판의 코멘트 수
    $sql = " select count(*) as cnt from {$g5['write_prefix']}{$bo_table} where wr_is_comment = 1 ";
    $row = sql_fetch($sql);
    $bo_count_comment = $row['cnt'];

    // 글수 조정
    if (isset($_POST['proc_count'])) {
        $sql = " select a.wr_id, (count(b.wr_parent) - 1) as cnt from {$g5['write_prefix']}{$bo_table} a, {$g5['write_prefix']}{$bo_table} b where a.wr_id=b.wr_parent and a.wr_is_comment=0 group by a.wr_id ";
        $result = sql_query($sql);
        for ($i = 0; $row = sql_fetch_array($result); $i++) {
            sql_query(" update {$g5['write_prefix']}{$bo_table} set wr_comment = '{$row['cnt']}' where wr_id = '{$row['wr_id']}' ");
        }
    }

    $sql = " update {$g5['board_table']}
                set bo_count_write = '{$bo_count_write}',
                    bo_count_comment = '{$bo_count_comment}',
                    {$sql_common}
              where bo_table = '{$bo_table}' ";
    sql_query($sql);
}

delete_cache_latest($bo_table);

if (function_exists('get_admin_captcha_by')) {
    get_admin_captcha_by('remove');
}

run_event('admin_board_form_update', $bo_table, $w);

goto_url("./board_form.php?w=u&bo_table={$bo_table}&amp;{$qstr}");
?>
