<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
echo '<link rel="stylesheet" href="'.$board_skin_url.'/style.css">';
?>

<section id="bo_w">
    <h2 class="sound_only"><?php echo $g5['title'] ?></h2>

    <!-- 게시물 작성/수정 시작 { -->
    <form name="fwrite" id="fwrite" action="<?php echo $action_url ?>" onsubmit="return fwrite_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off" style="width:<?php echo $width; ?>">
    <input type="hidden" name="uid" value="<?php echo get_uniqid(); ?>">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <?php
    $option = '';
    $option_hidden = '';
    if ($is_html || $is_secret) { 
        $option = '';
        if ($is_html) {
            if ($is_dhtml_editor) {
                $option_hidden .= '<input type="hidden" value="html1" name="html">';
            }
        }
        // RA0 접근 제어 시스템: 비밀글/멤버공개 선택
        if ($is_secret) {
            $secret_selected = '';
            $member_selected = '';

            if (isset($write['wr_option'])) {
                if (strpos($write['wr_option'], 'secret') !== false) {
                    $secret_selected = 'selected';
                }
                if (strpos($write['wr_option'], 'member') !== false) {
                    $member_selected = 'selected';
                }
            }

            if ($is_admin || $is_secret==1) {
                $option .= PHP_EOL.'<li class="chk_box">'.PHP_EOL.'<label for="set_secret">공개 설정</label>'.PHP_EOL.'<select id="set_secret" name="secret">'.PHP_EOL.'<option value="">전체공개</option>'.PHP_EOL.'<option value="secret" '.$secret_selected.'>비밀글</option>';
                if ($is_member) {
                    $option .= PHP_EOL.'<option value="member" '.$member_selected.'>멤버공개</option>';
                }
                $option .= PHP_EOL.'</select>'.PHP_EOL.'</li>';
            } else {
                $option_hidden .= '<input type="hidden" name="secret" value="secret">';
            }
        }
    }
    echo $option_hidden;
    ?>

    <?php if ($is_category) { ?>
    <div class="bo_w_select write_div">
        <label for="ca_name" class="sound_only">분류<strong>필수</strong></label>
        <select name="ca_name" id="ca_name" required>
            <option value="">분류를 선택하세요</option>
            <?php echo $category_option ?>
        </select>
    </div>
    <?php } ?>

    <div class="bo_w_info write_div">
	    <?php if ($is_name) { ?>
	        <label for="wr_name" class="sound_only">이름<strong>필수</strong></label>
	        <input type="text" name="wr_name" value="<?php echo $name ?>" id="wr_name" required class="frm_input half_input required" placeholder="이름">
	    <?php } ?>
	
	    <?php if ($is_password) { ?>
	        <label for="wr_password" class="sound_only">비밀번호<strong>필수</strong></label>
	        <input type="password" name="wr_password" id="wr_password" <?php echo $password_required ?> class="frm_input half_input <?php echo $password_required ?>" placeholder="비밀번호">
	    <?php } ?>

	    <!-- RA0 비밀글 조회 비밀번호 -->
	    <?php if ($is_secret && ($is_admin || $is_secret==1)) { ?>
	    <div id="secret_password_area" style="display:none;">
	        <label for="wr_secret">비밀글 조회 비밀번호</label>
	        <input type="password" name="wr_secret" id="wr_secret" class="frm_input half_input" placeholder="비밀글 조회 시 필요한 비밀번호" maxlength="20">
	    </div>
	    <?php } ?>
	</div>
	
    <?php if ($option) { ?>
    <div class="write_div">
        <span class="sound_only">옵션</span>
        <ul class="bo_v_option">
        <?php echo $option ?>
        </ul>
    </div>
    <?php } ?>

    <div class="bo_w_tit write_div">
        <label for="wr_subject" class="sound_only">제목<strong>필수</strong></label>
        
        <div id="autosave_wrapper" class="write_div">
            <input type="text" name="wr_subject" value="<?php echo $subject ?>" id="wr_subject" required class="frm_input full_input required" size="50" maxlength="255" placeholder="제목">
            <?php if ($is_member) { // 임시 저장된 글 기능 ?>
            <script src="<?php echo G5_JS_URL; ?>/autosave.js"></script>
            <?php if($editor_content_js) echo $editor_content_js; ?>
            <button type="button" id="btn_autosave" class="btn_frmline">임시 저장된 글 (<span id="autosave_count"><?php echo $autosave_count; ?></span>)</button>
            <div id="autosave_pop">
                <strong>임시 저장된 글 목록</strong>
                <ul></ul>
                <div><button type="button" class="autosave_close">X</button></div>
            </div>
            <?php } ?>
        </div>
        
    </div>

    <!-- 본문 + 파일 첨부 (write_file.common.php) -->
    <?php include_once(G5_BBS_PATH . '/write_file.common.php'); ?>

    <div class="btn_confirm write_div">
        <a href="<?php echo get_pretty_url($bo_table); ?>" class="btn_cancel btn">취소</a>
        <button type="submit" id="btn_submit" accesskey="s" class="btn_submit btn">작성완료</button>
    </div>
    </form>

    <script>
    // RA0 비밀글 선택 시 비밀번호 입력창 표시
    document.addEventListener("DOMContentLoaded", function() {
        var setSecretSelect = document.getElementById("set_secret");
        var secretPasswordDiv = document.getElementById("secret_password_area");
        var secretPasswordInput = document.getElementById("wr_secret");

        if (setSecretSelect && secretPasswordDiv && secretPasswordInput) {
            setSecretSelect.addEventListener("change", function() {
                if (this.value === "secret") {
                    secretPasswordDiv.style.display = "block";
                    secretPasswordInput.required = true;
                } else {
                    secretPasswordDiv.style.display = "none";
                    secretPasswordInput.required = false;
                    secretPasswordInput.value = "";
                }
            });

            // 수정 시 비밀글이면 비밀번호 입력창 표시
            if (setSecretSelect.value === "secret") {
                secretPasswordDiv.style.display = "block";
                secretPasswordInput.required = true;
            }
        }
    });

    function fwrite_submit(f)
    {
        // 비밀글 선택 시 비밀번호 필수 체크
        var setSecretSelect = document.getElementById("set_secret");
        var secretPasswordInput = document.getElementById("wr_secret");
        if (setSecretSelect && setSecretSelect.value === "secret") {
            if (secretPasswordInput && secretPasswordInput.value === "") {
                alert("비밀글 조회 비밀번호를 입력해주세요.");
                secretPasswordInput.focus();
                return false;
            }
        }

        <?php echo $editor_js; // 에디터 사용시 자바스크립트에서 내용을 폼필드로 넣어주며 내용이 입력되었는지 검사함   ?>

        document.getElementById("btn_submit").disabled = "disabled";

        return true;
    }
    </script>
</section>
<!-- } 게시물 작성/수정 끝 -->