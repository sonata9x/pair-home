<?php
$sub_menu = "100300";
include_once('./_common.php');

$config_font_table = G5_TABLE_PREFIX . 'config_font';
ra0_normalize_config_font_table($config_font_table);

// 폰트 목록 가져오기
$result = get_all_fonts();

$g5['title'] = '폰트 관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');
?>

<div class="local_desc01 local_desc">
    <p>웹사이트에서 사용할 폰트를 관리합니다. 기본 폰트는 <strong>Pretendard</strong>입니다.</p>
</div>

<div class="btn_add01 btn_add">
    <a href="./font_form.php" class="btn btn_01">폰트 추가</a>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption>폰트 관리 목록</caption>
    <thead>
    <tr>
        <th scope="col">순서</th>
        <th scope="col">폰트명</th>
        <th scope="col">Font Family</th>
        <th scope="col">사용</th>
        <th scope="col">관리</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if($result && sql_num_rows($result) > 0) {
        $i = 0;
        while($row = sql_fetch_array($result)) {
            $i++;
    ?>
    <tr>
        <td class="td_num"><?php echo $row['fo_order'] ?></td>
        <td class="td_left">
            <strong><?php echo htmlspecialchars($row['fo_name']) ?></strong>
            <?php if($row['fo_memo']): ?>
            <div class="font_memo"><?php echo nl2br(htmlspecialchars($row['fo_memo'])) ?></div>
            <?php endif; ?>
        </td>
        <?php $font_family = ra0_normalize_font_family($row['fo_family']); ?>
        <td class="td_left font_preview" style="font-family: <?php echo htmlspecialchars(ra0_css_font_family($font_family), ENT_QUOTES) ?>">
            <?php echo htmlspecialchars($font_family) ?>
        </td>
        <td><?php echo $row['fo_use'] ? '<span class="txt_true">사용</span>' : '<span class="txt_false">미사용</span>' ?></td>
        <td>
            <a href="./font_form.php?fo_id=<?php echo $row['fo_id'] ?>" class="btn btn_03">수정</a>
            <a href="./font_delete.php?fo_id=<?php echo $row['fo_id'] ?>" onclick="return confirm('정말 삭제하시겠습니까?')" class="btn btn_02">삭제</a>
        </td>
    </tr>
    <?php 
        }
    } else {
    ?>
    <tr>
        <td colspan="5" class="empty_table">등록된 폰트가 없습니다.</td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
</div>

<?php include_once(G5_ADMIN_PATH.'/admin.tail.php'); ?>
