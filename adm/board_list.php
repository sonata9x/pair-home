<?php
$sub_menu = "200200";
require_once './_common.php';

if (!$is_admin) {
    alert('관리자만 접근 가능합니다.');
}

// bo_type 필드 자동 추가
$field_query = "SHOW COLUMNS FROM {$g5['board_table']} LIKE 'bo_type'";
$field_exists = sql_fetch($field_query);
if (!$field_exists) {
    sql_query("ALTER TABLE {$g5['board_table']} ADD COLUMN bo_type VARCHAR(20) NOT NULL DEFAULT 'normal' AFTER bo_table", false);
}

$sql_common = " from {$g5['board_table']} a ";
$sql_search = " where (1) ";

// 그룹 시스템 제거됨 - 그룹 테이블 조인 및 그룹 관리자 권한 확인 제거
/*
if ($is_admin != "super") {
    $sql_common .= " , {$g5['group_table']} b ";
    $sql_search .= " and (a.gr_id = b.gr_id and b.gr_admin = '{$member['mb_id']}') ";
}
*/

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "bo_table":
            $sql_search .= " ($sfl like '$stx%') ";
            break;
        case "a.gr_id":
            // 그룹 시스템 제거됨 - 그룹 ID 검색 무력화
            $sql_search .= " (1=0) "; // 항상 거짓
            break;
        default:
            $sql_search .= " ($sfl like '%$stx%') ";
            break;
    }
    $sql_search .= " ) ";
}

// 기본 정렬을 출력 순서로 변경
if (!$sst) {
    $sst  = "a.bo_order, a.bo_table";  // 그룹 시스템 제거됨 - gr_id 정렬 제거
    $sod = "desc";
}
$sql_order = " order by $sst $sod ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) {
    $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
}
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$listall = '<a href="' . $_SERVER['SCRIPT_NAME'] . '" class="ov_listall">전체목록</a>';

$g5['title'] = '게시판관리';
require_once './admin.head.php';

$colspan = 12; // 컬럼 수 조정

// 레벨 옵션 생성 함수
function get_level_options($selected_level = 1) {
    $options = '';
    for ($i = 1; $i <= 10; $i++) {
        $selected = ($selected_level == $i) ? 'selected' : '';
        $options .= "<option value=\"{$i}\" {$selected}>{$i}</option>";
    }
    return $options;
}
?>

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <span class="btn_ov01"><span class="ov_txt">생성된 게시판수</span><span class="ov_num"> <?php echo number_format($total_count) ?>개</span></span>
</div>

<form name="fsearch" id="fsearch" class="local_sch01 local_sch" method="get">
    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="bo_table" <?php echo get_selected($sfl, "bo_table", true); ?>>TABLE</option>
        <option value="bo_subject" <?php echo get_selected($sfl, "bo_subject"); ?>>제목</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx ?>" id="stx" required class="required frm_input">
    <input type="submit" value="검색" class="btn_submit">
</form>

<form name="fboardlist" id="fboardlist" action="./board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="<?php echo isset($token) ? $token : ''; ?>">

    <div class="tbl_head01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr>
                    <th scope="col">
                        <label for="chkall" class="sound_only">게시판 전체</label>
                        <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
                    </th>
                    <th scope="col"><?php echo subject_sort_link('bo_table') ?>TABLE</a></th>
                    <th scope="col"><?php echo subject_sort_link('bo_type', '', 'desc') ?>타입</a></th>
                    <th scope="col"><?php echo subject_sort_link('bo_skin', '', 'desc') ?>스킨</a></th>
                    <th scope="col"><?php echo subject_sort_link('bo_subject') ?>제목</a></th>
                    <th scope="col">목록<br>권한</th>
                    <th scope="col">읽기<br>권한</th>
                    <th scope="col">쓰기<br>권한</th>
                    <th scope="col">댓글<br>권한</th>
                    <th scope="col"><?php echo subject_sort_link('bo_order') ?>출력<br>순서</a></th>
                    <th scope="col">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {
                    $one_update = '<a href="./board_form.php?w=u&amp;bo_table=' . $row['bo_table'] . '&amp;' . $qstr . '" class="btn btn_03">수정</a>';
                    $one_copy = '<a href="./board_copy.php?bo_table=' . $row['bo_table'] . '" class="board_copy btn btn_02" target="win_board_copy">복사</a>';

                    $bg = 'bg' . ($i % 2);
                ?>

                    <tr class="<?php echo $bg; ?>">
                        <td class="td_chk">
                            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo get_text($row['bo_subject']) ?></label>
                            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i ?>">
                        </td>
                        <td>
                            <input type="hidden" name="board_table[<?php echo $i ?>]" value="<?php echo $row['bo_table'] ?>">
                            <a href="<?php echo get_pretty_url($row['bo_table']) ?>"><?php echo $row['bo_table'] ?></a>
                        </td>
                        <td>
                            <label for="bo_type_<?php echo $i; ?>" class="sound_only">타입</label>
                            <select name="bo_type[<?php echo $i ?>]" id="bo_type_<?php echo $i ?>" class="tbl_input">
                                <option value="normal" <?php echo get_selected($row['bo_type'], 'normal'); ?>>일반</option>
                                <option value="timeline" <?php echo get_selected($row['bo_type'], 'timeline'); ?>>타임라인</option>
                            </select>
                        </td>
                        <td>
                            <label for="bo_skin_<?php echo $i; ?>" class="sound_only">스킨</label>
                            <?php echo get_skin_select('board', 'bo_skin_' . $i, "bo_skin[$i]", $row['bo_skin']); ?>
                        </td>
                        <td>
                            <label for="bo_subject_<?php echo $i; ?>" class="sound_only">게시판 제목<strong class="sound_only"> 필수</strong></label>
                            <input type="text" name="bo_subject[<?php echo $i ?>]" value="<?php echo get_text($row['bo_subject']) ?>" id="bo_subject_<?php echo $i ?>" required class="required tbl_input bo_subject full_input" size="10">
                        </td>
                        <td class="td_numsmall">
                            <label for="bo_list_level_<?php echo $i; ?>" class="sound_only">목록 권한</label>
                            <select name="bo_list_level[<?php echo $i ?>]" id="bo_list_level_<?php echo $i ?>" class="tbl_input">
                                <?php echo get_level_options($row['bo_list_level'] ?: 1); ?>
                            </select>
                        </td>
                        <td class="td_numsmall">
                            <label for="bo_read_level_<?php echo $i; ?>" class="sound_only">읽기 권한</label>
                            <select name="bo_read_level[<?php echo $i ?>]" id="bo_read_level_<?php echo $i ?>" class="tbl_input">
                                <?php echo get_level_options($row['bo_read_level'] ?: 1); ?>
                            </select>
                        </td>
                        <td class="td_numsmall">
                            <label for="bo_write_level_<?php echo $i; ?>" class="sound_only">쓰기 권한</label>
                            <select name="bo_write_level[<?php echo $i ?>]" id="bo_write_level_<?php echo $i ?>" class="tbl_input">
                                <?php echo get_level_options($row['bo_write_level'] ?: 1); ?>
                            </select>
                        </td>
                        <td class="td_numsmall">
                            <label for="bo_comment_level_<?php echo $i; ?>" class="sound_only">댓글 권한</label>
                            <select name="bo_comment_level[<?php echo $i ?>]" id="bo_comment_level_<?php echo $i ?>" class="tbl_input">
                                <?php echo get_level_options($row['bo_comment_level'] ?: 1); ?>
                            </select>
                        </td>
                        <td class="td_numsmall">
                            <label for="bo_order_<?php echo $i; ?>" class="sound_only">출력<br>순서</label>
                            <input type="text" name="bo_order[<?php echo $i ?>]" value="<?php echo $row['bo_order'] ?>" id="bo_order_<?php echo $i ?>" class="tbl_input" size="2">
                        </td>
                        <td class="td_mng td_mng_m">
                            <?php echo $one_update ?>
                            <?php echo $one_copy ?>
                        </td>
                    </tr>
                <?php
                }
                if ($i == 0) {
                    echo '<tr><td colspan="' . $colspan . '" class="empty_table">자료가 없습니다.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="btn_fixed_top">
        <input type="submit" name="act_button" value="선택수정" onclick="document.pressed=this.value" class="btn_02 btn">
        <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn_02 btn">
        <a href="./board_form.php" id="bo_add" class="btn_01 btn">게시판 추가</a>
    </div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'] . '?' . $qstr . '&amp;page='); ?>

<script>
    function fboardlist_submit(f) {
        if (!is_checked("chk[]")) {
            alert(document.pressed + " 하실 항목을 하나 이상 선택하세요.");
            return false;
        }

        if (document.pressed == "선택삭제") {
            if (!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
                return false;
            }
        }

        return true;
    }

    $(function() {
        $(".board_copy").click(function() {
            window.open(this.href, "win_board_copy", "left=100,top=100,width=550,height=450");
            return false;
        });
    });
</script>

<?php
require_once './admin.tail.php';
?>
