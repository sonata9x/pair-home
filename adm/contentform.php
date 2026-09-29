<?php
$sub_menu = '200700';
require_once './_common.php';
require_once G5_EDITOR_LIB;

$co_id = isset($_REQUEST['co_id']) ? preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['co_id']) : '';

// 상단, 하단 파일경로 필드 추가
if (!sql_query(" select co_include_head from {$g5['content_table']} limit 1 ", false)) {
    $sql = " ALTER TABLE `{$g5['content_table']}`  ADD `co_include_head` VARCHAR( 255 ) NOT NULL ,
                                                    ADD `co_include_tail` VARCHAR( 255 ) NOT NULL ";
    sql_query($sql, false);
}

// html purifier 사용여부 필드
if (!sql_query(" select co_tag_filter_use from {$g5['content_table']} limit 1 ", false)) {
    sql_query(
        " ALTER TABLE `{$g5['content_table']}`
                    ADD `co_tag_filter_use` tinyint(4) NOT NULL DEFAULT '0' AFTER `co_content` ",
        true
    );
    sql_query(" update {$g5['content_table']} set co_tag_filter_use = '1' ");
}

// 모바일 내용 추가
if (!sql_query(" select co_mobile_content from {$g5['content_table']} limit 1", false)) {
    sql_query(
        " ALTER TABLE `{$g5['content_table']}`
                    ADD `co_mobile_content` longtext NOT NULL AFTER `co_content` ",
        true
    );
}

$html_title = "내용";
$g5['title'] = $html_title . ' 관리';
$readonly = '';

if ($w == "u") {
    $html_title .= " 수정";
    $readonly = " readonly";

    $sql = " select * from {$g5['content_table']} where co_id = '$co_id' ";
    $co = sql_fetch($sql);
    if (!$co || !$co['co_id']) {
        alert('등록된 자료가 없습니다.');
    }

    if (function_exists('check_case_exist_title')) check_case_exist_title($co, G5_CONTENT_DIR, false);

} else {
    $html_title .= ' 입력';
    $co = array(
        'co_id' => '',
        'co_subject' => '',
        'co_content' => '',
        'co_include_head' => '',
        'co_include_tail' => '',
        'co_tag_filter_use' => 0,
        'co_html' => 1
    );
}

require_once G5_ADMIN_PATH . '/admin.head.php';
?>

<form name="frmcontentform" action="./contentformupdate.php" onsubmit="return frmcontentform_check(this);" method="post" enctype="MULTIPART/FORM-DATA">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="co_html" value="1">
    <input type="hidden" name="token" value="">

    <div class="tbl_frm01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <colgroup>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th scope="row"><label for="co_id">ID</label></th>
                    <td>
                        <?php echo help('20자 이내의 영문자, 숫자, _ 만 가능합니다.'); ?>
                        <input type="text" value="<?php echo $co['co_id']; ?>" name="co_id" id="co_id" required <?php echo $readonly; ?> class="required <?php echo $readonly; ?> frm_input" size="20" maxlength="20">
                        <?php if ($w == 'u') { ?><a href="<?php echo get_pretty_url('content', $co_id); ?>" class="btn_frmline">내용확인</a><?php } ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="co_subject">제목</label></th>
                    <td><input type="text" name="co_subject" value="<?php echo htmlspecialchars2($co['co_subject']); ?>" id="co_subject" required class="frm_input required" size="90"></td>
                </tr>
                <tr>
                    <th scope="row">내용</th>
                    <td><?php echo editor_html('co_content', get_text(html_purifier($co['co_content']), 0)); ?></td>
                </tr>
                <tr>
                    <th scope="row"><label for="co_include_head">상단 파일 경로</label></th>
                    <td>
                        <?php echo help("설정값이 없으면 기본 상단 파일을 사용합니다."); ?>
                        <input type="text" name="co_include_head" value="<?php echo get_sanitize_input($co['co_include_head']); ?>" id="co_include_head" class="frm_input" size="60">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="co_include_tail">하단 파일 경로</label></th>
                    <td>
                        <?php echo help("설정값이 없으면 기본 하단 파일을 사용합니다."); ?>
                        <input type="text" name="co_include_tail" value="<?php echo get_sanitize_input($co['co_include_tail']); ?>" id="co_include_tail" class="frm_input" size="60">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="co_himg">상단이미지</label></th>
                    <td>
                        <input type="file" name="co_himg" id="co_himg">
                        <?php
                        $himg = G5_DATA_PATH . '/content/' . $co['co_id'] . '_h';
                        $himg_str = '';
                        if (file_exists($himg)) {
                            $size = @getimagesize($himg);
                            if ($size[0] && $size[0] > 750) {
                                $width = 750;
                            } else {
                                $width = $size[0];
                            }

                            echo '<input type="checkbox" name="co_himg_del" value="1" id="co_himg_del"> <label for="co_himg_del">삭제</label>';
                            $himg_str = '<img src="' . G5_DATA_URL . '/content/' . $co['co_id'] . '_h" width="' . $width . '" alt="">';
                        }
                        if ($himg_str) {
                            echo '<div class="banner_or_img">';
                            echo $himg_str;
                            echo '</div>';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="co_timg">하단이미지</label></th>
                    <td>
                        <input type="file" name="co_timg" id="co_timg">
                        <?php
                        $timg = G5_DATA_PATH . '/content/' . $co['co_id'] . '_t';
                        $timg_str = '';
                        if (file_exists($timg)) {
                            $size = @getimagesize($timg);
                            if ($size[0] && $size[0] > 750) {
                                $width = 750;
                            } else {
                                $width = $size[0];
                            }

                            echo '<input type="checkbox" name="co_timg_del" value="1" id="co_timg_del"> <label for="co_timg_del">삭제</label>';
                            $timg_str = '<img src="' . G5_DATA_URL . '/content/' . $co['co_id'] . '_t" width="' . $width . '" alt="">';
                        }
                        if ($timg_str) {
                            echo '<div class="banner_or_img">';
                            echo $timg_str;
                            echo '</div>';
                        }
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="btn_fixed_top">
        <a href="./contentlist.php" class="btn btn_02">목록</a>
        <input type="submit" value="확인" class="btn btn_submit" accesskey="s">
    </div>

</form>

<script>
    function frmcontentform_check(f) {
        errmsg = "";
        errfld = "";

        <?php echo get_editor_js('co_content'); ?>
        <?php echo chk_editor_js('co_content'); ?>

        check_field(f.co_id, "ID를 입력하세요.");
        check_field(f.co_subject, "제목을 입력하세요.");
        check_field(f.co_content, "내용을 입력하세요.");

        if (errmsg != "") {
            alert(errmsg);
            errfld.focus();
            return false;
        }

        return true;
    }
</script>

<?php
require_once G5_ADMIN_PATH . '/admin.tail.php';
