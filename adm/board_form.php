<?php
$sub_menu = "200200";
require_once './_common.php';
require_once G5_EDITOR_LIB;

// 그룹 시스템 제거됨 - 그룹 생성 요구 제거
/*
$sql = " select count(*) as cnt from {$g5['group_table']} ";
$row = sql_fetch($sql);
if (!$row['cnt']) {
    alert('게시판그룹이 한개 이상 생성되어야 합니다.', './boardgroup_form.php');
}
*/

$html_title = '게시판';

$board_default = array(
'bo_type'=>'normal',
'bo_use_category'=>0,
'bo_category_list'=>'',
'bo_admin'=>'',
'bo_list_level'=>0,
'bo_read_level'=>0,
'bo_write_level'=>0,
'bo_comment_level'=>0,
'bo_upload_level'=>0,
'bo_download_level'=>0,
'bo_html_level'=>0,
'bo_use_sideview'=>0,
'bo_order'=>0,
'bo_include_head'=>'_head.php',
'bo_include_tail'=>'_tail.php',
);

for ($i = 0; $i <= 10; $i++) {
    $board_default['bo_'.$i.'_subj'] = '';
    $board_default['bo_'.$i] = '';
}

$board = array_merge($board_default, $board);

run_event('adm_board_form_before', $board, $w);

$required = "";
$readonly = "";
$sound_only = "";
$required_valid = "";
if ($w == '') {
    $html_title .= ' 생성';

    $required = 'required';
    $required_valid = 'alnum_';
    $sound_only = '<strong class="sound_only">필수</strong>';

    $board['bo_read_point'] = $config['cf_read_point'];
    $board['bo_write_point'] = $config['cf_write_point'];
    $board['bo_comment_point'] = $config['cf_comment_point'];
    $board['bo_download_point'] = $config['cf_download_point'];

    $board['bo_gallery_cols'] = 4;
    $board['bo_gallery_width'] = 202;
    $board['bo_gallery_height'] = 150;
    $board['bo_table_width'] = 100;
    $board['bo_page_rows'] = $config['cf_page_rows'];
    $board['bo_subject_len'] = 60;
    $board['bo_upload_count'] = 2;
    $board['bo_upload_size'] = 1048576;
    $board['bo_reply_order'] = 1;
    $board['bo_image_width'] = 600;
    $board['bo_skin'] = 'basic';
    // 그룹 시스템 제거됨 - gr_id 필드 완전 제거
    // $board['gr_id'] = $gr_id;
    $board['bo_use_secret'] = 0;
} elseif ($w == 'u') {
    $html_title .= ' 수정';

    if (!$board['bo_table']) {
        alert('존재하지 않은 게시판 입니다.');
    }
    
    // 그룹 시스템 제거됨 - 기존 데이터에서 gr_id 제거
    if (isset($board['gr_id'])) {
        unset($board['gr_id']);
    }

    // 그룹 시스템 제거됨 - 그룹 관리자 권한 확인 제거
    /*
    if ($is_admin == 'group') {
        if ($member['mb_id'] != $group['gr_admin']) {
            alert('그룹이 틀립니다.');
        }
    }
    */

    $readonly = 'readonly';
}

// 그룹 시스템 제거됨 - 그룹 선택 및 그룹 정보 가져오기 제거
/*
if (!isset($gr_id) || !$gr_id) {
    // 기본 그룹 선택 (첫 번째 그룹)
    $sql = " select gr_id from {$g5['group_table']} order by gr_id limit 1 ";
    $row = sql_fetch($sql);
    $gr_id = $row['gr_id'] ?? '';
}

if ($gr_id && !is_array($gr_id)) {
    $group = get_group($gr_id, true);
}
*/
// 그룹 시스템 완전 제거됨


$g5['title'] = $html_title;
require_once './admin.head.php';
?>

<form name="fboardform" id="fboardform" action="./board_form_update.php" onsubmit="return fboardform_submit(this)" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">
<input type="hidden" name="bo_subject_len" value="60">

<!-- 필수 히든 필드들만 유지 -->
<input type="hidden" name="bo_use_search" value="1">
<input type="hidden" name="bo_order" value="<?php echo $board['bo_order'] ?>">
<!-- 그룹 시스템 제거됨 - gr_id 히든 필드 제거 -->
<!-- <input type="hidden" name="gr_id" value="<?php echo isset($gr_id) ? $gr_id : ''; ?>"> -->

<section id="anc_bo_basic">
    <h2 class="h2_frm">게시판 기본 설정</h2>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>게시판 기본 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="bo_table">TABLE<?php echo $sound_only ?></label></th>
            <td>
                <input type="text" name="bo_table" value="<?php echo $board['bo_table'] ?>" id="bo_table" <?php echo $required ?> <?php echo $readonly ?> class="frm_input <?php echo $readonly ?> <?php echo $required ?> <?php echo $required_valid ?>" maxlength="20">
                <?php if ($w == '') { ?>
                    영문자, 숫자, _ 만 가능 (공백없이 20자 이내)
                <?php } else { ?>
                    <a href="<?php echo get_pretty_url($board['bo_table']) ?>" class="btn_frmline">게시판 바로가기</a>
                    <a href="./board_list.php?<?php echo $qstr;?>" class="btn_frmline">목록으로</a>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_subject">게시판 제목<strong class="sound_only">필수</strong></label></th>
            <td>
                <input type="text" name="bo_subject" value="<?php echo get_text($board['bo_subject']) ?>" id="bo_subject" required class="required frm_input" size="80" maxlength="120">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_type">게시판 타입</label></th>
            <td>
                <?php echo help('게시판의 형태를 선택합니다. 타임라인은 트위터와 같은 스레드 형태로 동작합니다.') ?>
                <select id="bo_type" name="bo_type">
                    <option value="normal" <?php echo get_selected($board['bo_type'], 'normal'); ?>>일반 게시판</option>
                    <option value="timeline" <?php echo get_selected($board['bo_type'], 'timeline'); ?>>타임라인</option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_category_list">분류</label></th>
            <td>
                <?php echo help('분류와 분류 사이는 | 로 구분하세요. (예: 질문|답변) 첫자로 #은 입력하지 마세요. (예: #질문|#답변 [X])'."\n".'분류명에 일부 특수문자 ()/ 는 사용할수 없습니다.'); ?>
                <input type="text" name="bo_category_list" value="<?php echo get_text($board['bo_category_list']) ?>" id="bo_category_list" class="frm_input" size="70">
                <input type="checkbox" name="bo_use_category" value="1" id="bo_use_category" <?php echo $board['bo_use_category']?'checked':''; ?>>
                <label for="bo_use_category">사용</label>
            </td>
        </tr>
        <?php if ($w == 'u') { ?>
        <tr>
            <th scope="row"><label for="proc_count">카운트 조정</label></th>
            <td>
                <?php echo help('현재 원글수 : '.number_format($board['bo_count_write']).', 현재 댓글수 : '.number_format($board['bo_count_comment'])."\n".'게시판 목록에서 글의 번호가 맞지 않을 경우에 체크하십시오.') ?>
                <input type="checkbox" name="proc_count" value="1" id="proc_count">
            </td>
        </tr>
        <?php } ?>
        </tbody>
        </table>
    </div>
</section>

<section id="anc_bo_auth">
    <h2 class="h2_frm">게시판 권한 설정</h2>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>게시판 권한 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="bo_admin">게시판 관리자</label></th>
            <td>
                <input type="text" name="bo_admin" value="<?php echo $board['bo_admin'] ?>" id="bo_admin" class="frm_input" maxlength="255" style="width:300px;">
                <span class="help-text" style="color:#888; font-size:12px; margin-left:5px;">여러 명인 경우 쉼표로 구분 (예: admin1,admin2,admin3)</span>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_list_level">목록보기 권한</label></th>
            <td>
                <?php echo help('권한 1은 비회원, 2 이상 회원입니다. 권한은 10 이 가장 높습니다.') ?>
                <?php echo get_member_level_select('bo_list_level', 1, 10, $board['bo_list_level']) ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_read_level">글읽기 권한</label></th>
            <td>
                <?php echo get_member_level_select('bo_read_level', 1, 10, $board['bo_read_level']) ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_write_level">글쓰기 권한</label></th>
            <td>
                <?php echo get_member_level_select('bo_write_level', 1, 10, $board['bo_write_level']) ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_comment_level">댓글쓰기 권한</label></th>
            <td>
                <?php echo get_member_level_select('bo_comment_level', 1, 10, $board['bo_comment_level']) ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_upload_level">업로드 권한</label></th>
            <td>
                <?php echo get_member_level_select('bo_upload_level', 1, 10, $board['bo_upload_level']) ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_download_level">다운로드 권한</label></th>
            <td>
                <?php echo get_member_level_select('bo_download_level', 1, 10, $board['bo_download_level']) ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_html_level">HTML 쓰기 권한</label></th>
            <td>
                <?php echo get_member_level_select('bo_html_level', 1, 10, $board['bo_html_level']) ?>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<section id="anc_bo_function">
    <h2 class="h2_frm">게시판 기능 설정</h2>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>게시판 기능 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="bo_use_sideview">글쓴이 사이드뷰</label></th>
            <td>
                <input type="checkbox" name="bo_use_sideview" value="1" id="bo_use_sideview" <?php echo $board['bo_use_sideview']?'checked':''; ?>>
                사용 (글쓴이 클릭시 나오는 레이어 메뉴)
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_secret">비밀글 사용</label></th>
            <td>
                <?php echo help('"체크박스"는 글작성시 비밀글 체크가 가능합니다. "무조건"은 작성되는 모든글을 비밀글로 작성합니다. (관리자는 체크박스로 출력합니다.) 스킨에 따라 적용되지 않을 수 있습니다.') ?>
                <select id="bo_use_secret" name="bo_use_secret">
                    <?php echo option_selected(0, $board['bo_use_secret'], "사용하지 않음"); ?>
                    <?php echo option_selected(1, $board['bo_use_secret'], "체크박스"); ?>
                    <?php echo option_selected(2, $board['bo_use_secret'], "무조건"); ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_dhtml_editor">DHTML 에디터 사용</label></th>
            <td>
                <?php echo help('글작성시 내용을 DHTML 에디터 기능으로 사용할 것인지 설정합니다. 스킨에 따라 적용되지 않을 수 있습니다.') ?>
                <input type="checkbox" name="bo_use_dhtml_editor" value="1" <?php echo $board['bo_use_dhtml_editor']?'checked':''; ?> id="bo_use_dhtml_editor">
                사용
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_upload_count">파일 업로드 개수<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('게시물 한건당 업로드 할 수 있는 파일의 최대 개수 (0 은 파일첨부 사용하지 않음)') ?>
                <input type="text" name="bo_upload_count" value="<?php echo $board['bo_upload_count'] ?>" id="bo_upload_count" required class="required numeric frm_input" size="4">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_upload_size">파일 업로드 용량<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('최대 '.ini_get("upload_max_filesize").' 이하 업로드 가능, 1 MB = 1,048,576 bytes') ?>
                업로드 파일 한개당 <input type="text" name="bo_upload_size" value="<?php echo $board['bo_upload_size'] ?>" id="bo_upload_size" required class="required numeric frm_input"  size="10"> bytes 이하
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<section id="anc_bo_design">
    <h2 class="h2_frm">게시판 디자인/양식</h2>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>게시판 디자인/양식</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="bo_skin">스킨 디렉토리<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo get_skin_select('board', 'bo_skin', 'bo_skin', $board['bo_skin'], 'required'); ?>
            </td>
        </tr>
        <?php if ($is_admin === 'super') {   // 슈퍼관리자인 경우에만 수정 가능 ?>
        <tr>
            <th scope="row"><label for="bo_include_head">상단 파일 경로</label></th>
            <td>
                <input type="text" name="bo_include_head" value="<?php echo get_sanitize_input($board['bo_include_head']); ?>" id="bo_include_head" class="frm_input" size="50">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_include_tail">하단 파일 경로</label></th>
            <td>
                <input type="text" name="bo_include_tail" value="<?php echo get_sanitize_input($board['bo_include_tail']); ?>" id="bo_include_tail" class="frm_input" size="50">
            </td>
        </tr>
        <?php }     //end if $is_admin === 'super' ?>
        <tr>
            <th scope="row"><label for="bo_content_head">게시판 상단 공지</label></th>
            <td>
                <?php echo editor_html('bo_content_head', get_text(html_purifier($board['bo_content_head'] ?? ''), 0)); ?>
                <small style="color:#888;">게시판 목록 최상단에 표시됩니다. HTML 사용 가능.</small>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_page_rows">페이지당 목록 수<strong class="sound_only">필수</strong></label></th>
            <td>
                <input type="text" name="bo_page_rows" value="<?php echo $board['bo_page_rows'] ?>" id="bo_page_rows" required class="required numeric frm_input" size="4">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_gallery_cols">갤러리 이미지 수<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('갤러리 형식의 게시판 목록에서 이미지를 한줄에 몇장씩 보여 줄 것인지를 설정하는 값') ?>
                <input type="text" name="bo_gallery_cols" value="<?php echo $board['bo_gallery_cols'] ?>" id="bo_gallery_cols" required class="required numeric frm_input" size="4">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_gallery_width">갤러리 이미지 폭<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('갤러리 형식의 게시판 목록에서 썸네일 이미지의 폭을 설정하는 값') ?>
                <input type="text" name="bo_gallery_width" value="<?php echo $board['bo_gallery_width'] ?>" id="bo_gallery_width" required class="required numeric frm_input" size="4">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_gallery_height">갤러리 이미지 높이<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('갤러리 형식의 게시판 목록에서 썸네일 이미지의 높이를 설정하는 값') ?>
                <input type="text" name="bo_gallery_height" value="<?php echo $board['bo_gallery_height'] ?>" id="bo_gallery_height" required class="required numeric frm_input" size="4">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_table_width">게시판 폭<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('100 이하는 %') ?>
                <input type="text" name="bo_table_width" value="<?php echo $board['bo_table_width'] ?>" id="bo_table_width" required class="required numeric frm_input" size="4">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_image_width">이미지 폭 크기<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('게시판에서 출력되는 이미지의 폭 크기') ?>
                <input type="text" name="bo_image_width" value="<?php echo $board['bo_image_width'] ?>" id="bo_image_width" required class="required numeric frm_input" size="4"> 픽셀
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_reply_order">답변 달기</label></th>
            <td>
                <select id="bo_reply_order" name="bo_reply_order">
                    <option value="1"<?php echo get_selected($board['bo_reply_order'], 1, true); ?>>나중에 쓴 답변 아래로 달기 (기본)
                    <option value="0"<?php echo get_selected($board['bo_reply_order'], 0); ?>>나중에 쓴 답변 위로 달기
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_sort_field">기본 정렬</label></th>
            <td>
                <?php echo help('게시판 목록의 기본 정렬 순서를 설정합니다.'."\n".'수동 정렬을 선택하면 관리자가 순서를 직접 변경할 수 있습니다.'); ?>
                <select id="bo_sort_field" name="bo_sort_field">
                <?php
                $sort_fields = get_board_sort_fields($board);
                foreach($sort_fields as $sort) {
                    $selected = ($board['bo_sort_field'] == $sort[0]) ? ' selected' : '';
                    echo '<option value="'.$sort[0].'"'.$selected.'>'.$sort[1].'</option>'.PHP_EOL;
                }
                ?>
                </select>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<section id="anc_bo_point">
    <h2 class="h2_frm">게시판 포인트 설정</h2>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>게시판 포인트 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="bo_read_point">글읽기 포인트<strong class="sound_only">필수</strong></label></th>
            <td>
                <input type="text" name="bo_read_point" value="<?php echo $board['bo_read_point'] ?>" id="bo_read_point" required class="required frm_input" size="5">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_write_point">글쓰기 포인트<strong class="sound_only">필수</strong></label></th>
            <td>
                <input type="text" name="bo_write_point" value="<?php echo $board['bo_write_point'] ?>" id="bo_write_point" required class="required frm_input" size="5">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_comment_point">댓글쓰기 포인트<strong class="sound_only">필수</strong></label></th>
            <td>
                <input type="text" name="bo_comment_point" value="<?php echo $board['bo_comment_point'] ?>" id="bo_comment_point" required class="required frm_input" size="5">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_download_point">다운로드 포인트<strong class="sound_only">필수</strong></label></th>
            <td>
                <input type="text" name="bo_download_point" value="<?php echo $board['bo_download_point'] ?>" id="bo_download_point" required class="required frm_input" size="5">
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<section id="anc_bo_extra">
    <h2 class="h2_frm">게시판 여분필드 설정</h2>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>게시판 여분필드 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <?php for ($i=1; $i<=10; $i++) { ?>
        <tr>
            <th scope="row">여분필드<?php echo $i ?></th>
            <td class="td_extra">
                <label for="bo_<?php echo $i ?>_subj">여분필드 <?php echo $i ?> 제목</label>
                <input type="text" name="bo_<?php echo $i ?>_subj" id="bo_<?php echo $i ?>_subj" value="<?php echo get_text($board['bo_'.$i.'_subj']) ?>" class="frm_input">
                <label for="bo_<?php echo $i ?>">여분필드 <?php echo $i ?> 값</label>
                <textarea name="bo_<?php echo $i ?>" id="bo_<?php echo $i ?>" class="frm_input extra-value-input" rows="4"><?php echo get_text($board['bo_'.$i]) ?></textarea>
            </td>
        </tr>
        <?php } ?>
        </tbody>
        </table>
    </div>
</section>

<div class="btn_fixed_top">
    <?php if ($bo_table && $w) { ?>
        <a href="./board_copy.php?bo_table=<?php echo $board['bo_table']; ?>" id="board_copy" target="win_board_copy" class="btn_02 btn">게시판복사</a>
        <a href="<?php echo get_pretty_url($board['bo_table']); ?>" class="btn_02 btn">게시판 바로가기</a>
        <a href="./board_thumbnail_delete.php?bo_table=<?php echo $board['bo_table'].'&amp;'.$qstr;?>" onclick="return delete_confirm2('게시판 썸네일 파일을 삭제하시겠습니까?');" class="btn_02 btn">게시판 썸네일 삭제</a>
    <?php } ?>
    <input type="submit" value="확인" class="btn_submit btn btn_01" accesskey="s">
</div>

</form>

<script>
$(function(){
    $("#board_copy").click(function(){
        window.open(this.href, "win_board_copy", "left=10,top=10,width=500,height=400");
        return false;
    });
});

function board_copy(bo_table) {
    window.open("./board_copy.php?bo_table="+bo_table, "BoardCopy", "left=10,top=10,width=500,height=200");
}

function fboardform_submit(f)
{
    <?php echo get_editor_js('bo_content_head'); ?>

    <?php
    if (!$w) {
        $js_array = get_bo_table_banned_word();
        echo "var banned_array = ". json_encode($js_array) . ";\n";
    }
    ?>

    // 게시판명이 금지된 단어로 되어 있으면
    if( (typeof banned_array != 'undefined') && jQuery.inArray(f.bo_table.value, banned_array) !== -1 ){
        alert("입력한 게시판 TABLE명을 사용할수 없습니다. 다른 이름으로 입력해 주세요.");
        return false;
    }

    return true;
}
</script>

<?php
require_once './admin.tail.php';
?>
