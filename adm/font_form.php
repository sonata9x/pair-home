<?php
include_once('./_common.php');

$config_font_table = G5_TABLE_PREFIX . 'config_font';

// 수정 모드
$fo_id = isset($_GET['fo_id']) ? (int)$_GET['fo_id'] : 0;
$mode = $fo_id ? 'edit' : 'add';

if($fo_id) {
    $sql = "SELECT * FROM {$config_font_table} WHERE fo_id = '{$fo_id}'";
    $row = sql_fetch($sql);
    if(!$row) {
        alert('존재하지 않는 폰트입니다.');
    }
} else {
    $row = array(
        'fo_family' => '',
        'fo_name' => '',
        'fo_import' => '',
        'fo_use' => 1,
        'fo_order' => 0,
        'fo_memo' => ''
    );
}

include_once(G5_ADMIN_PATH.'/admin.head.php');
?>

<div class="local_desc01 local_desc">
    <p><?php echo $mode == 'edit' ? '폰트를 수정합니다.' : '새 폰트를 추가합니다.' ?></p>
</div>

<form name="fform" method="post" action="./font_update.php">
<input type="hidden" name="mode" value="<?php echo $mode ?>">
<input type="hidden" name="fo_id" value="<?php echo $fo_id ?>">

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption>폰트 정보</caption>
    <colgroup>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="fo_name">폰트명</label></th>
        <td>
            <input type="text" name="fo_name" value="<?php echo htmlspecialchars($row['fo_name']) ?>" id="fo_name" required class="frm_input" size="30">
            <span class="frm_info">예: Pretendard, Noto Sans KR</span>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="fo_family">Font Family</label></th>
        <td>
            <input type="text" name="fo_family" value="<?php echo htmlspecialchars(ra0_normalize_font_family($row['fo_family'])) ?>" id="fo_family" required class="frm_input" size="60">
            <span class="frm_info">예: Pretendard, Chosunilbo_myungjo</span>
        </td>
    </tr>
    <tr>
    <th scope="row"><label for="fo_import">폰트 불러오기 코드</label></th>
    <td>
        <textarea name="fo_import" id="fo_import" rows="6" class="frm_textbox"><?php echo stripslashes(htmlspecialchars($row['fo_import'])) ?></textarea>
            <div class="frm_info">
                <strong>1. @import 방식:</strong><br>
                @import url("https://fonts.googleapis.com/css2?family=Noto+Sans+KR&display=swap");<br><br>

                <strong>2. @font-face 방식:</strong><br>
                @font-face {<br>
                &nbsp;&nbsp;font-family: 'CustomFont';<br>
                &nbsp;&nbsp;src: url('/fonts/CustomFont.woff2') format('woff2'),<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;url('/fonts/CustomFont.woff') format('woff');<br>
                &nbsp;&nbsp;font-weight: normal;<br>
                &nbsp;&nbsp;font-style: normal;<br>
                &nbsp;&nbsp;font-display: swap;<br>
                }
            </div>
        </td>
    </tr>
    <tr>
        <th scope="row">사용여부</th>
        <td>
            <input type="radio" name="fo_use" value="1" id="fo_use_1" <?php echo $row['fo_use'] ? 'checked' : '' ?>>
            <label for="fo_use_1">사용</label>
            <input type="radio" name="fo_use" value="0" id="fo_use_0" <?php echo !$row['fo_use'] ? 'checked' : '' ?>>
            <label for="fo_use_0">사용안함</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="fo_order">정렬순서</label></th>
        <td>
            <input type="number" name="fo_order" value="<?php echo $row['fo_order'] ?>" id="fo_order" class="frm_input" size="10">
            <span class="frm_info">숫자가 작을수록 먼저 표시됩니다.</span>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="fo_memo">메모</label></th>
        <td>
            <textarea name="fo_memo" id="fo_memo" rows="3" class="frm_textbox"><?php echo htmlspecialchars($row['fo_memo']) ?></textarea>
        </td>
    </tr>
    </tbody>
    </table>
</div>

<div class="btn_confirm01 btn_confirm">
    <input type="submit" value="확인" class="btn_submit">
    <a href="./config_font.php" class="btn btn_02">목록</a>
</div>
</form>

<?php include_once(G5_ADMIN_PATH.'/admin.tail.php'); ?>
