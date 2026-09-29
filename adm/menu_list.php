<?php
$sub_menu = "200100";
require_once './_common.php';

if (!$is_admin) {
    alert('관리자만 접근 가능합니다.');
}

// 메뉴테이블 생성
if (!isset($g5['menu_table'])) {
    die('<meta charset="utf-8">dbconfig.php 파일에 <strong>$g5[\'menu_table\'] = G5_TABLE_PREFIX.\'menu\';</strong> 를 추가해 주세요.');
}

if (!sql_query(" DESCRIBE {$g5['menu_table']} ", false)) {
    sql_query(
        " CREATE TABLE IF NOT EXISTS `{$g5['menu_table']}` (
                  `me_id` int(11) NOT NULL AUTO_INCREMENT,
                  `me_code` varchar(255) NOT NULL DEFAULT '',
                  `me_name` varchar(255) NOT NULL DEFAULT '',
                  `me_icon` varchar(255) NOT NULL DEFAULT '',
                  `me_link` varchar(255) NOT NULL DEFAULT '',
                  `me_target` varchar(255) NOT NULL DEFAULT '0',
                  `me_order` int(11) NOT NULL DEFAULT '0',
                  `me_level` tinyint(4) NOT NULL DEFAULT '1',
                  PRIMARY KEY (`me_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ",
        true
    );
}

$sql = " select * from {$g5['menu_table']} order by me_order, me_id ";
$result = sql_query($sql);

$g5['title'] = "메뉴설정";
require_once './admin.head.php';

$colspan = 7;
?>

<div class="local_desc01 local_desc">
    <p><strong>주의!</strong> 메뉴설정 작업 후 반드시 <strong>확인</strong>을 누르셔야 저장됩니다.</p>
    <p><strong>하위 메뉴:</strong> 메뉴명 앞에 <code>ㄴ</code>을 붙이면 하위 메뉴로 인식됩니다.</p>
    <p><strong>아이콘:</strong> Font Awesome 클래스명 입력 (예: fa-home, fa-user)</p>
</div>

<form name="fmenulist" id="fmenulist" method="post" action="./menu_list_update.php" onsubmit="return fmenulist_submit(this);">
    <input type="hidden" name="token" value="">

    <div id="menulist" class="tbl_head01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?> 목록</caption>
            <thead>
                <tr>
                    <th scope="col">메뉴</th>
                    <th scope="col">아이콘</th>
                    <th scope="col">링크</th>
                    <th scope="col">새창</th>
                    <th scope="col">순서</th>
                    <th scope="col">보이기</th>
                    <th scope="col">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                for ($i = 0; $row = sql_fetch_array($result); $i++) {
                    $bg = 'bg' . ($i % 2);
                    $search  = array('"', "'");
                    $replace = array('&#034;', '&#039;');
                    $me_name = str_replace($search, $replace, $row['me_name']);
                ?>
                    <tr class="<?php echo $bg; ?> menu_list">
                        <!-- 메뉴명 -->
                        <td class="td_category">
                            <input type="hidden" name="me_id[]" value="<?php echo $row['me_id'] ?>">
                            <input type="hidden" name="code[]" value="<?php echo substr($row['me_code'], 0, 2) ?>">
                            <label for="me_name_<?php echo $i; ?>" class="sound_only">메뉴</label>
                            <input type="text" name="me_name[]" value="<?php echo get_sanitize_input($me_name); ?>" id="me_name_<?php echo $i; ?>" class="tbl_input full_input" placeholder="텍스트 없이 아이콘만 가능">
                        </td>

                        <td class="td_mng">
                            <label for="me_icon_<?php echo $i; ?>" class="sound_only">아이콘</label>
                            <input type="text" name="me_icon[]" value="<?php echo $row['me_icon'] ?>" id="me_icon_<?php echo $i; ?>" class="tbl_input" placeholder="fa-home" style="width:80px;">
                            <?php if($row['me_icon']) { ?>
                                <i class="fa <?php echo $row['me_icon'] ?>" style="margin-left:5px; color:#666;"></i>
                            <?php } ?>
                        </td>
                        
                        <!-- 링크 -->
                        <td>
                            <label for="me_link_<?php echo $i; ?>" class="sound_only">링크<strong class="sound_only"> 필수</strong></label>
                            <input type="text" name="me_link[]" value="<?php echo $row['me_link'] ?>" id="me_link_<?php echo $i; ?>" required class="required tbl_input full_input">
                        </td>
                        
                        <!-- 새창 -->
                        <td class="td_mng">
                            <label for="me_target_<?php echo $i; ?>" class="sound_only">새창</label>
                            <select name="me_target[]" id="me_target_<?php echo $i; ?>">
                                <option value="self" <?php echo get_selected($row['me_target'], 'self', true); ?>>사용안함</option>
                                <option value="blank" <?php echo get_selected($row['me_target'], 'blank', true); ?>>사용함</option>
                            </select>
                        </td>
                        
                        <!-- 순서 -->
                        <td class="td_num">
                            <label for="me_order_<?php echo $i; ?>" class="sound_only">순서</label>
                            <input type="text" name="me_order[]" value="<?php echo $row['me_order'] ?>" id="me_order_<?php echo $i; ?>" class="tbl_input" size="5">
                        </td>
                        
                        <!-- 보이기 권한 -->
                        <td class="td_mng">
                            <label for="me_level_<?php echo $i; ?>" class="sound_only">보이기</label>
                            <select name="me_level[]" id="me_level_<?php echo $i; ?>">
                                <option value="1" <?php echo ($row['me_level'] == '1') ? 'selected' : ''; ?>>1</option>
                                <option value="2" <?php echo ($row['me_level'] == '2') ? 'selected' : ''; ?>>2</option>
                                <option value="3" <?php echo ($row['me_level'] == '3') ? 'selected' : ''; ?>>3</option>
                                <option value="4" <?php echo ($row['me_level'] == '4') ? 'selected' : ''; ?>>4</option>
                                <option value="5" <?php echo ($row['me_level'] == '5') ? 'selected' : ''; ?>>5</option>
                                <option value="6" <?php echo ($row['me_level'] == '6') ? 'selected' : ''; ?>>6</option>
                                <option value="7" <?php echo ($row['me_level'] == '7') ? 'selected' : ''; ?>>7</option>
                                <option value="8" <?php echo ($row['me_level'] == '8') ? 'selected' : ''; ?>>8</option>
                                <option value="9" <?php echo ($row['me_level'] == '9') ? 'selected' : ''; ?>>9</option>
                                <option value="10" <?php echo ($row['me_level'] == '10') ? 'selected' : ''; ?>>10</option>
                            </select>
                        </td>

                        <!-- 관리 -->
                        <td class="td_mng">
                            <button type="button" class="btn_del_menu btn_02">삭제</button>
                        </td>
                    </tr>
                <?php
                }

                if ($i == 0) {
                    echo '<tr id="empty_menu_list"><td colspan="' . $colspan . '" class="empty_table">자료가 없습니다.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="btn_fixed_top">
        <button type="button" onclick="return add_menu();" class="btn btn_02">메뉴추가</button>
        <input type="submit" name="act_button" value="확인" class="btn_submit btn">
    </div>

</form>

<script>
$(function() {
    // 아이콘 실시간 미리보기
    $(document).on("input", "input[name='me_icon[]']", function() {
        var $this = $(this);
        var icon_class = $this.val();
        var $preview = $this.next('i');
        
        if ($preview.length) {
            if (icon_class) {
                $preview.attr('class', 'fa ' + icon_class).show();
            } else {
                $preview.hide();
            }
        } else if (icon_class) {
            $this.after('<i class="fa ' + icon_class + '" style="margin-left:5px; color:#666;"></i>');
        }
    });

    $(document).on("click", ".btn_del_menu", function() {
        if (!confirm("메뉴를 삭제하시겠습니까?"))
            return false;

        $(this).closest("tr").remove();

        if ($("#menulist tr.menu_list").length < 1) {
            var list = "<tr id=\"empty_menu_list\"><td colspan=\"<?php echo $colspan; ?>\" class=\"empty_table\">자료가 없습니다.</td></tr>\n";
            $("#menulist table tbody").append(list);
        } else {
            $("#menulist tr.menu_list").each(function(index) {
                $(this).removeClass("bg0 bg1")
                    .addClass("bg" + (index % 2));
            });
        }
    });
});

function add_menu() {
    var max_code = base_convert(0, 10, 36);
    $("#menulist tr.menu_list").each(function() {
        var me_code = $(this).find("input[name='code[]']").val().substr(0, 2);
        if (max_code < me_code)
            max_code = me_code;
    });
    var url = "./menu_form.php?code=" + max_code + "&new=new";
    window.open(url, "add_menu", "left=100,top=100,width=550,height=650,scrollbars=yes,resizable=yes");
    return false;
}
function add_submenu(code) {
    var url = "./menu_form.php?code=" + code;
    window.open(url, "add_menu", "left=100,top=100,width=550,height=650,scrollbars=yes,resizable=yes");
    return false;
}
function base_convert(number, frombase, tobase) {
    //  discuss at: http://phpjs.org/functions/base_convert/
    // original by: Philippe Baumann
    // improved by: Rafał Kukawski (http://blog.kukawski.pl)
    //   example 1: base_convert('A37334', 16, 2);
    //   returns 1: '101000110111001100110100'
    return parseInt(number + '', frombase | 0)
        .toString(tobase | 0);
}
function fmenulist_submit(f) {
    var me_links = document.getElementsByName('me_link[]');
    var reg = /^javascript/;
    for (i = 0; i < me_links.length; i++) {
        if (reg.test(me_links[i].value)) {
            alert('링크에 자바스크립트문을 입력할수 없습니다.');
            me_links[i].focus();
            return false;
        }
    }
    return true;
}

function fmenulist_submit(f) {
    var me_links = document.getElementsByName('me_link[]');
    var reg = /^javascript/;

    for (i = 0; i < me_links.length; i++) {
        if (reg.test(me_links[i].value)) {
            alert('링크에 자바스크립트문을 입력할수 없습니다.');
            me_links[i].focus();
            return false;
        }
    }

    return true;
}
</script>

<?php
require_once './admin.tail.php';
?>
