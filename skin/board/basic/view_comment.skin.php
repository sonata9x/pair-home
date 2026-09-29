<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<script>
// 글자수 제한
var char_min = parseInt(<?php echo $comment_min ?>); // 최소
var char_max = parseInt(<?php echo $comment_max ?>); // 최대
</script>
<button type="button" class="cmt_btn"><i class="fa fa-comment-o"></i> 댓글 <strong><?php echo $view['wr_comment']; ?></strong></button>
<!-- 댓글 시작 { -->
<section id="bo_vc" class="comment_section">
    <h2 class="sound_only">댓글목록</h2>
    <?php
    $cmt_amt = count($list);
    for ($i=0; $i<$cmt_amt; $i++) {
        $comment_id = $list[$i]['wr_id'];
        $cmt_depth = strlen($list[$i]['wr_comment_reply']) * 50;
        $comment = $list[$i]['content'];
        /*
        if (strstr($list[$i]['wr_option'], "secret")) {
            $str = $str;
        }
        */
        $comment = preg_replace("/\[\<a\s.*href\=\"(http|https|ftp|mms)\:\/\/([^[:space:]]+)\.(mp3|wma|wmv|asf|asx|mpg|mpeg)\".*\<\/a\>\]/i", "<script>doc_write(obj_movie('$1://$2.$3'));</script>", $comment);
        // autolink, 외부 임베드, 이모티콘은 bbs/view_comment.php에서 공통 적용
        $cmt_sv = $cmt_amt - $i + 1; // 댓글 헤더 z-index 재설정 ie8 이하 사이드뷰 겹침 문제 해결
		$c_reply_href = $comment_common_url.'&amp;c_id='.$comment_id.'&amp;w=c#bo_vc_w';
		$c_edit_href = $comment_common_url.'&amp;c_id='.$comment_id.'&amp;w=cu#bo_vc_w';
        $is_comment_reply_edit = ($list[$i]['is_reply'] || $list[$i]['is_edit'] || $list[$i]['is_del']) ? 1 : 0;
	?>

	<article id="c_<?php echo $comment_id ?>" class="comment_item" <?php if ($cmt_depth) { ?>style="margin-left:<?php echo $cmt_depth ?>px"<?php } ?>>
        <div class="comment_profile">
            <?php
            if ($list[$i]['mb_id']) {
                $comment_author = get_member($list[$i]['mb_id']);
                if ($comment_author['mb_signature']) {
                    echo '<img src="'.$comment_author['mb_signature'].'" alt="'.get_text($list[$i]['wr_name']).'">';
                }
            }
            ?>
        </div>

        <div class="comment_body">

            <div class="comment_header">
	            <h2 class="sound_only">댓글</h2>
	            <strong class="comment_author"><?php echo $list[$i]['wr_name'] ?></strong>
	            <?php if ($list[$i]['mb_id']) { ?>
	            <span class="comment_id">@<?php echo $list[$i]['mb_id'] ?></span>
	            <?php } ?>
	            <span class="comment_date"><i class="fa fa-clock-o"></i> <time datetime="<?php echo date('Y-m-d\TH:i:s+09:00', strtotime($list[$i]['datetime'])) ?>"><?php echo $list[$i]['datetime'] ?></time></span>
	        </div>

	        <!-- 댓글 출력 -->
	        <div class="comment_content">
	            <?php if (strstr($list[$i]['wr_option'], "secret")) { ?><span class="badge_secret"><i class="fa fa-lock"></i> 비밀글</span><?php } ?>
	            <?php echo $comment ?>

	            <?php
	            // 댓글 첨부파일 표시
	            if (!empty($list[$i]['file']) && is_array($list[$i]['file'])) {
	                echo '<div class="comment_files" style="margin-top: 10px;">';
	                foreach ($list[$i]['file'] as $file) {
	                    if ($file['view']) {
	                        $file_ext = strtolower(pathinfo($file['bf_source'], PATHINFO_EXTENSION));
	                        $is_image = in_array($file_ext, array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'));

	                        if ($is_image) {
	                            echo '<div class="comment_file_image" style="margin: 5px 0;">';
	                            echo '<a href="'.$file['href'].'" target="_blank">';
	                            echo '<img src="'.$file['href'].'" alt="'.htmlspecialchars($file['bf_source']).'" style="max-width: 100%; height: auto; border-radius: 8px;">';
	                            echo '</a>';
	                            echo '</div>';
	                        } else {
	                            echo '<div class="comment_file_link" style="margin: 5px 0;">';
	                            echo '<a href="'.$file['href'].'" class="comment_file" download>';
	                            echo '<i class="fa fa-file"></i> '.htmlspecialchars($file['bf_source']).' ('.number_format($file['bf_filesize']).' bytes)';
	                            echo '</a>';
	                            echo '</div>';
	                        }
	                    }
	                }
	                echo '</div>';
	            }
	            ?>

	            <?php if($is_comment_reply_edit) {
	                if($w == 'cu') {
	                    $sql = " select wr_id, wr_content, mb_id from $write_table where wr_id = '$c_id' and wr_is_comment = '1' ";
	                    $cmt = sql_fetch($sql);
                        if (isset($cmt)) {
                            if (!($is_admin || ($member['mb_id'] == $cmt['mb_id'] && $cmt['mb_id']))) {
                                $cmt['wr_content'] = '';
                            }
                            $c_wr_content = $cmt['wr_content'];
                        }
	                }
				?>
	            <?php } ?>
	        </div>
	        <span id="edit_<?php echo $comment_id ?>" class="bo_vc_w"></span><!-- 수정 -->
	        <span id="reply_<?php echo $comment_id ?>" class="bo_vc_w"></span><!-- 답변 -->
	
	        <input type="hidden" value="<?php echo strstr($list[$i]['wr_option'],"secret") ?>" id="secret_comment_<?php echo $comment_id ?>">
	        <textarea id="save_comment_<?php echo $comment_id ?>" style="display:none"><?php echo get_text($list[$i]['content1'], 0) ?></textarea>
		</div>

        <?php if($is_comment_reply_edit) { ?>
		<div class="comment_options">
            <button type="button" class="btn_cm_opt"><i class="fa fa-ellipsis-h"></i></button>
        	<ul class="comment_actions_menu">
                <?php if ($list[$i]['is_reply']) { ?><li><a href="<?php echo $c_reply_href; ?>" onclick="comment_box('<?php echo $comment_id ?>', 'c'); return false;"><i class="fa fa-reply"></i> 답변</a></li><?php } ?>
                <?php if ($list[$i]['is_edit']) { ?><li><a href="<?php echo $c_edit_href; ?>" onclick="comment_box('<?php echo $comment_id ?>', 'cu'); return false;"><i class="fa fa-pencil"></i> 수정</a></li><?php } ?>
                <?php if ($list[$i]['is_del']) { ?><li><a href="<?php echo $list[$i]['del_link']; ?>" onclick="return comment_delete();"><i class="fa fa-trash-o"></i> 삭제</a></li><?php } ?>
            </ul>
        </div>
        <?php } ?>
    </article>
    <?php } ?>
    <?php if ($i == 0) { //댓글이 없다면 ?><p id="bo_vc_empty">등록된 댓글이 없습니다.</p><?php } ?>

</section>
<!-- } 댓글 끝 -->

<script>
$(function() {
    // 댓글 옵션창 열기 (이벤트 위임 방식)
    $(document).on("click", ".btn_cm_opt", function(e){
        e.stopPropagation();
        // 다른 열린 메뉴는 모두 닫기
        $(".comment_actions_menu").not($(this).siblings(".comment_actions_menu")).hide();
        // 현재 메뉴 토글
        $(this).siblings(".comment_actions_menu").toggle();
    });

    // 댓글 옵션창 닫기
    $(document).on("mouseup", function (e){
        var container = $(".comment_actions_menu");
        var button = $(".btn_cm_opt");
        if (!container.is(e.target) && container.has(e.target).length === 0 && !button.is(e.target) && button.has(e.target).length === 0) {
            container.hide();
        }
    });

    // 파일 첨부 영역 토글
    $(document).on("click", ".file_upload_info", function(e){
        e.preventDefault();
        var wrapper = $(this).siblings('.file_inputs_wrapper');
        var icon = $(this).find('.file_toggle_icon');

        if (wrapper.is(':visible')) {
            wrapper.slideUp(300);
            icon.css('transform', 'rotate(0deg)');
        } else {
            wrapper.slideDown(300);
            icon.css('transform', 'rotate(180deg)');
        }
    });
});
</script>

<?php if ($is_comment_write) {
    if($w == '')
        $w = 'c';
?>
<!-- 댓글 쓰기 시작 { -->
<aside id="bo_vc_w" class="bo_vc_w">
    <h2 class="sound_only">댓글쓰기</h2>
    <form name="fviewcomment" id="fviewcomment" action="<?php echo $comment_action_url; ?>" onsubmit="return fviewcomment_submit(this);" method="post" autocomplete="off" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>" id="w">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
    <input type="hidden" name="comment_id" value="<?php echo $c_id ?>" id="comment_id">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="is_good" value="">

    <div class="comment_form_container">
        <?php if ($is_guest) { ?>
        <div class="guest_info_row">
            <label for="wr_name" class="sound_only">이름<strong> 필수</strong></label>
            <input type="text" name="wr_name" value="<?php echo get_cookie("ck_wr_name"); ?>" id="wr_name" required class="frm_input required" placeholder="이름">
            <label for="wr_password" class="sound_only">비밀번호<strong> 필수</strong></label>
            <input type="password" name="wr_password" id="wr_password" required class="frm_input required" placeholder="비밀번호">
        </div>
        <?php } ?>

        <div class="comment_input_row">
            <div class="comment_input_column">
                <span class="sound_only">내용</span>
                <textarea id="wr_content" name="wr_content" maxlength="10000" required class="required" title="내용" placeholder="댓글내용을 입력해주세요"
                <?php if ($comment_min || $comment_max) { ?>onkeyup="check_byte('wr_content', 'char_count');"<?php } ?>><?php echo $c_wr_content; ?></textarea>

                <!-- 파일 첨부 -->
                <div class="file_upload_section" style="margin-top: 10px;">
                    <a href="#" class="file_upload_info" style="display: inline-flex; align-items: center; gap: 5px; color: var(--secondary-font-color); text-decoration: none; font-size: 0.85em;">
                        <i class="fa fa-paperclip"></i>
                        <span>파일 첨부 (최대 4개, 이미지/문서)</span>
                        <span class="file_toggle_icon" style="transition: transform 0.3s;">▼</span>
                    </a>

                    <!-- 기존 파일 목록 (수정 시) -->
                    <div class="existing_files_wrapper" style="display: none; margin-top: 10px;"></div>

                    <!-- 파일 입력 영역 -->
                    <div class="file_inputs_wrapper" style="display: none; margin-top: 10px;">
                        <input type="file" name="bf_file[]" title="파일첨부 1" class="frm_file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                        <input type="file" name="bf_file[]" title="파일첨부 2" class="frm_file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                        <input type="file" name="bf_file[]" title="파일첨부 3" class="frm_file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                        <input type="file" name="bf_file[]" title="파일첨부 4" class="frm_file" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                    </div>
                </div>
            </div>
            <div class="comment_actions">
                <?php if ($comment_min || $comment_max) { ?>
                <span class="char_counter"><span id="char_count"></span>글자</span>
                <?php } ?>
                <span class="secret_cm chk_box">
                    <input type="checkbox" name="wr_secret" value="secret" id="wr_secret" class="selec_chk">
                    <label for="wr_secret"><span></span>비밀글</label>
                </span>
                <button type="submit" id="btn_submit" class="btn_submit">댓글등록</button>
            </div>
        </div>

        <?php if ($is_guest && $captcha_html) { ?>
        <div class="captcha_row">
            <?php echo $captcha_html; ?>
        </div>
        <?php } ?>
    </div>
    <?php if ($comment_min || $comment_max) { ?><script> check_byte('wr_content', 'char_count'); </script><?php } ?>
    <script>
    $(document).on("keyup change", "textarea#wr_content[maxlength]", function() {
        var str = $(this).val()
        var mx = parseInt($(this).attr("maxlength"))
        if (str.length > mx) {
            $(this).val(str.substr(0, mx));
            return false;
        }
    });
    </script>
    </form>
</aside>

<script>
var save_before = '';
var save_html = document.getElementById('bo_vc_w').innerHTML;

function good_and_write()
{
    var f = document.fviewcomment;
    if (fviewcomment_submit(f)) {
        f.is_good.value = 1;
        f.submit();
    } else {
        f.is_good.value = 0;
    }
}

function fviewcomment_submit(f)
{
    var pattern = /(^\s*)|(\s*$)/g; // \s 공백 문자

    f.is_good.value = 0;

    var subject = "";
    var content = "";
    $.ajax({
        url: g5_bbs_url+"/ajax.filter.php",
        type: "POST",
        data: {
            "subject": "",
            "content": f.wr_content.value
        },
        dataType: "json",
        async: false,
        cache: false,
        success: function(data, textStatus) {
            subject = data.subject;
            content = data.content;
        }
    });

    if (content) {
        alert("내용에 금지단어('"+content+"')가 포함되어있습니다");
        f.wr_content.focus();
        return false;
    }

    // 양쪽 공백 없애기
    var pattern = /(^\s*)|(\s*$)/g; // \s 공백 문자
    document.getElementById('wr_content').value = document.getElementById('wr_content').value.replace(pattern, "");
    if (char_min > 0 || char_max > 0)
    {
        check_byte('wr_content', 'char_count');
        var cnt = parseInt(document.getElementById('char_count').innerHTML);
        if (char_min > 0 && char_min > cnt)
        {
            alert("댓글은 "+char_min+"글자 이상 쓰셔야 합니다.");
            return false;
        } else if (char_max > 0 && char_max < cnt)
        {
            alert("댓글은 "+char_max+"글자 이하로 쓰셔야 합니다.");
            return false;
        }
    }
    else if (!document.getElementById('wr_content').value)
    {
        alert("댓글을 입력하여 주십시오.");
        return false;
    }

    if (typeof(f.wr_name) != 'undefined')
    {
        f.wr_name.value = f.wr_name.value.replace(pattern, "");
        if (f.wr_name.value == '')
        {
            alert('이름이 입력되지 않았습니다.');
            f.wr_name.focus();
            return false;
        }
    }

    if (typeof(f.wr_password) != 'undefined')
    {
        f.wr_password.value = f.wr_password.value.replace(pattern, "");
        if (f.wr_password.value == '')
        {
            alert('비밀번호가 입력되지 않았습니다.');
            f.wr_password.focus();
            return false;
        }
    }

    <?php if($is_guest) echo chk_captcha_js();  ?>

    // 토큰 검증 제거 (2025-10-30)
    // set_comment_token(f);

    document.getElementById("btn_submit").disabled = "disabled";

    return true;
}

function comment_box(comment_id, work)
{
    var el_id,
        form_el = 'fviewcomment',
        respond = document.getElementById(form_el);

    // 댓글 아이디가 넘어오면 답변, 수정
    if (comment_id)
    {
        if (work == 'c')
            el_id = 'reply_' + comment_id;
        else
            el_id = 'edit_' + comment_id;
    }
    else
        el_id = 'bo_vc_w';

    if (save_before != el_id)
    {
        if (save_before)
        {
            document.getElementById(save_before).style.display = 'none';
        }

        document.getElementById(el_id).style.display = '';
        document.getElementById(el_id).appendChild(respond);
        //입력값 초기화
        document.getElementById('wr_content').value = '';

        // 파일 입력 초기화
        $('.file_inputs_wrapper input[type="file"]').val('');
        $('.existing_files_wrapper').html('').hide();

        // 댓글 수정
        if (work == 'cu')
        {
            document.getElementById('wr_content').value = document.getElementById('save_comment_' + comment_id).value;
            if (typeof char_count != 'undefined')
                check_byte('wr_content', 'char_count');
            if (document.getElementById('secret_comment_'+comment_id).value)
                document.getElementById('wr_secret').checked = true;
            else
                document.getElementById('wr_secret').checked = false;

            // 기존 첨부파일 불러오기
            loadCommentFiles(comment_id);
        }

        document.getElementById('comment_id').value = comment_id;
        document.getElementById('w').value = work;

        if(save_before)
            $("#captcha_reload").trigger("click");

        save_before = el_id;
    }
}

// 댓글 파일 불러오기 함수
function loadCommentFiles(comment_id) {
    $.ajax({
        url: g5_bbs_url + '/ajax.get_comment_files.php',
        type: 'POST',
        data: {
            bo_table: '<?php echo $bo_table; ?>',
            wr_id: comment_id
        },
        dataType: 'json',
        success: function(data) {
            if (data.result === 'success' && data.files.length > 0) {
                var html = '<div style="padding: 10px; background: var(--light-bg-color); border-radius: 8px;">';
                html += '<div style="font-size: 0.85em; font-weight: 600; margin-bottom: 8px; color: var(--content-font-color);">기존 첨부파일</div>';

                data.files.forEach(function(file) {
                    var fileExt = file.bf_source.split('.').pop().toLowerCase();
                    var isImage = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].indexOf(fileExt) !== -1;

                    html += '<div class="existing_file_item" style="display: flex; align-items: center; gap: 8px; padding: 6px 0; border-bottom: 1px solid var(--card-border-color);">';

                    if (isImage) {
                        html += '<img src="' + file.url + '" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">';
                    } else {
                        html += '<i class="fa fa-file" style="font-size: 24px; color: var(--secondary-font-color); width: 40px; text-align: center;"></i>';
                    }

                    html += '<span style="flex: 1; font-size: 0.85em;">' + file.bf_source + '</span>';
                    html += '<button type="button" class="btn_file_del" onclick="deleteCommentFile(' + comment_id + ', \'' + file.bf_file + '\', this); return false;" style="padding: 4px 8px; background: #ff4757; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 0.75em;">';
                    html += '<i class="fa fa-trash"></i> 삭제';
                    html += '</button>';
                    html += '</div>';
                });

                html += '</div>';
                $('.existing_files_wrapper').html(html).show();
            }
        }
    });
}

// 댓글 파일 삭제 함수
function deleteCommentFile(comment_id, fileName, btn) {
    if (!confirm('이 파일을 삭제하시겠습니까?')) {
        return false;
    }

    $.ajax({
        url: g5_bbs_url + '/ajax.file_delete.php',
        type: 'POST',
        data: {
            bo_table: '<?php echo $bo_table; ?>',
            wr_id: comment_id,
            fileName: fileName,
            bf_content: -1  // 댓글 파일은 bf_content = -1
        },
        dataType: 'json',
        success: function(data) {
            if (data.result === 'success') {
                $(btn).closest('.existing_file_item').fadeOut(300, function() {
                    $(this).remove();
                    // 파일이 모두 삭제되면 영역 숨김
                    if ($('.existing_file_item').length === 0) {
                        $('.existing_files_wrapper').hide();
                    }
                });
                alert('파일이 삭제되었습니다.');
            } else {
                alert('파일 삭제에 실패했습니다: ' + (data.message || '알 수 없는 오류'));
            }
        },
        error: function() {
            alert('파일 삭제 중 오류가 발생했습니다.');
        }
    });
}

function comment_delete()
{
    return confirm("이 댓글을 삭제하시겠습니까?");
}

comment_box('', 'c'); // 댓글 입력폼이 보이도록 처리하기위해서 추가 (root님)

</script>
<?php } ?>
<!-- } 댓글 쓰기 끝 -->
<script>
jQuery(function($) {            
    //댓글열기
    $(".cmt_btn").click(function(e){
        e.preventDefault();
        $(this).toggleClass("cmt_btn_op");
        $("#bo_vc").toggle();
    });
});
</script>
