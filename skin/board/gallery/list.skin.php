<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
echo '<link rel="stylesheet" href="'.$board_skin_url.'/style.css">';

// 게시판 설정에서 가져온 값
$gallery_cols = $board['bo_gallery_cols'] ?? 4;
$gallery_width = $board['bo_gallery_width'] ?? 202;
$gallery_height = $board['bo_gallery_height'] ?? 150;
$table_width = $board['bo_table_width'] ?? 100;
?>

<!-- 게시판 목록 시작 { -->
<div id="bo_gall" style="width:<?php echo $table_width ?>%">

    <!-- 게시판 카테고리 시작 { -->
    <?php if ($is_category) { ?>
    <nav id="bo_cate">
        <h2><?php echo $board['bo_subject'] ?> 카테고리</h2>
        <ul id="bo_cate_ul">
            <?php echo $category_option ?>
        </ul>
    </nav>
    <?php } ?>
    <!-- } 게시판 카테고리 끝 -->

    <!-- 게시판 페이지 정보 및 버튼 시작 { -->
    <div class="bo_gall_top">
        <div id="bo_list_total">
            <span>Total <?php echo number_format($total_count) ?>건</span>
            <?php echo $page ?> 페이지
        </div>

        <div id="bo_gall_act">
            <ul class="btn_bo_user">
                <?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_admin btn" title="관리자"><i class="fa fa-cog fa-spin fa-fw"></i><span class="sound_only">관리자</span></a></li><?php } ?>
                <?php if ($rss_href) { ?><li><a href="<?php echo $rss_href ?>" class="btn_b01 btn" title="RSS"><i class="fa fa-rss" aria-hidden="true"></i><span class="sound_only">RSS</span></a></li><?php } ?>
                <li>
                    <button type="button" class="btn_bo_sch btn_b01 btn" title="게시판 검색"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">게시판 검색</span></button>
                </li>
                <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b01 btn" title="글쓰기"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sound_only">글쓰기</span></a></li><?php } ?>
            </ul>
        </div>
    </div>
    <!-- } 게시판 페이지 정보 및 버튼 끝 -->

    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="sw" value="">

    <!-- 갤러리 그리드 시작 { -->
    <ul id="gall_ul" class="gall_row" style="--gallery-cols: <?php echo $gallery_cols ?>;">
        <?php
        for ($i=0; $i<count($list); $i++) {
            // 비밀글 여부 확인
            $is_secret = strpos($list[$i]['wr_option'], 'secret') !== false;

            // 썸네일 가져오기
            $thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'], $gallery_width, $gallery_height, false, true);

            // 썸네일이 없으면 (비밀글이거나 파일이 없는 경우) 직접 조회
            if (!$thumb['src'] && $is_secret) {
                // 비밀글의 경우 직접 파일 조회
                $file_sql = "SELECT bf_file, bf_source FROM {$g5['board_file_table']}
                             WHERE bo_table = '{$board['bo_table']}' AND wr_id = '{$list[$i]['wr_id']}'
                             AND bf_type IN (1, 2, 3, 18)
                             ORDER BY bf_no ASC LIMIT 1";
                $file_row = sql_fetch($file_sql);

                if ($file_row && $file_row['bf_file']) {
                    // 썸네일 경로 생성
                    $thumb_path = G5_DATA_PATH.'/file/'.$board['bo_table'].'/thumb-'.$file_row['bf_file'];
                    $thumb_url = G5_DATA_URL.'/file/'.$board['bo_table'].'/thumb-'.$file_row['bf_file'];

                    // 썸네일이 없으면 원본 사용
                    if (!file_exists($thumb_path)) {
                        $thumb_url = G5_DATA_URL.'/file/'.$board['bo_table'].'/'.$file_row['bf_file'];
                    }

                    $img_url = $thumb_url;
                } else {
                    $img_url = G5_IMG_URL.'/no_image.png';
                }
            } else if ($thumb['src']) {
                $img_url = $thumb['src'];
            } else {
                $img_url = G5_IMG_URL.'/no_image.png';
            }
        ?>
        <li class="gall_li <?php if ($list[$i]['is_notice']) echo 'notice'; ?>">
            <?php if ($is_checkbox) { ?>
            <div class="gall_chk">
                <input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>" class="selec_chk">
                <label for="chk_wr_id_<?php echo $i ?>">
                    <span></span>
                    <b class="sound_only"><?php echo $list[$i]['subject'] ?></b>
                </label>
            </div>
            <?php } ?>

            <div class="gall_box">
                <a href="<?php echo $list[$i]['href'] ?>" class="gall_img_link">
                    <div class="gall_img">
                        <img src="<?php echo $img_url ?>" alt="<?php echo get_text($list[$i]['wr_subject']) ?>">

                        <?php if ($is_category && $list[$i]['ca_name']) { ?>
                        <span class="gall_cate_badge"><?php echo $list[$i]['ca_name'] ?></span>
                        <?php } ?>

                        <?php if ($list[$i]['is_notice']) { ?>
                        <span class="gall_notice_badge">공지</span>
                        <?php } ?>

                        <?php if ($is_secret) { ?>
                        <span class="gall_secret_badge"><i class="fa fa-lock"></i></span>
                        <?php } ?>
                    </div>
                </a>

                <div class="gall_text_href">
                    <div class="gall_title_row">
                        <?php if ($list[$i]['icon_new']) { ?>
                        <span class="gall_new_badge">N</span>
                        <?php } ?>
                        <a href="<?php echo $list[$i]['href'] ?>" class="gall_subject">
                            <?php echo get_text(cut_str($list[$i]['wr_subject'], 40)) ?>
                        </a>
                        <?php if ($list[$i]['wr_comment']) { ?>
                        <span class="gall_comment_badge"><?php echo $list[$i]['wr_comment'] ?></span>
                        <?php } ?>
                    </div>

                    <div class="gall_info">
                        <span class="gall_name"><?php echo $list[$i]['name'] ?></span>
                        <span class="gall_date"><?php echo $list[$i]['datetime2'] ?></span>
                    </div>

                    <?php if ($board['bo_use_good'] && $list[$i]['wr_good']) { ?>
                    <div class="gall_meta">
                        <span class="gall_good"><i class="fa fa-thumbs-o-up"></i> <?php echo $list[$i]['wr_good'] ?></span>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </li>
        <?php } ?>
        <?php if (count($list) == 0) { ?>
        <li class="empty_list">
            <p>게시물이 없습니다.</p>
        </li>
        <?php } ?>
    </ul>
    <!-- } 갤러리 그리드 끝 -->

    <?php if ($is_checkbox) { ?>
    <div class="bo_fx">
        <div class="btn_bo_adm">
            <button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_b01">선택삭제</button>
            <button type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value" class="btn btn_b01">선택복사</button>
            <button type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value" class="btn btn_b01">선택이동</button>
        </div>
    </div>
    <?php } ?>
    </form>

    <!-- 페이지 -->
    <div class="paginate_wrap">
        <?php echo $write_pages; ?>
    </div>
    <!-- 페이지 -->

    <!-- 게시판 검색 시작 { -->
    <div class="bo_sch_wrap">
        <fieldset class="bo_sch">
            <h3>검색</h3>
            <form name="fsearch" method="get">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
            <input type="hidden" name="sca" value="<?php echo $sca ?>">
            <input type="hidden" name="sop" value="and">
            <label for="sfl" class="sound_only">검색대상</label>
            <select name="sfl" id="sfl">
                <?php echo get_board_sfl_select_options($sfl); ?>
            </select>
            <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
            <div class="sch_bar">
                <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx" class="sch_input" size="25" maxlength="20" placeholder=" 검색어를 입력해주세요">
                <button type="submit" value="검색" class="sch_btn"><i class="fa fa-search" aria-hidden="true"></i><span class="sound_only">검색</span></button>
            </div>
            <button type="button" class="bo_sch_cls" title="닫기"><i class="fa fa-times" aria-hidden="true"></i><span class="sound_only">닫기</span></button>
            </form>
        </fieldset>
        <div class="bo_sch_bg"></div>
    </div>
    <script>
    jQuery(function($){
        // 게시판 검색
        $(".btn_bo_sch").on("click", function() {
            $(".bo_sch_wrap").toggle();
        })
        $('.bo_sch_bg, .bo_sch_cls').click(function(){
            $('.bo_sch_wrap').hide();
        });
    });
    </script>
    <!-- } 게시판 검색 끝 -->
</div>

<?php if($is_checkbox) { ?>
<noscript>
<p>자바스크립트를 사용하지 않는 경우<br>별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
</noscript>
<?php } ?>

<?php if ($is_checkbox) { ?>
<script>
function all_checked(sw) {
    var f = document.fboardlist;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]")
            f.elements[i].checked = sw;
    }
}

function fboardlist_submit(f) {
    var chk_count = 0;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
            chk_count++;
    }

    if (!chk_count) {
        alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택복사") {
        select_copy("copy");
        return;
    }

    if(document.pressed == "선택이동") {
        select_copy("move");
        return;
    }

    if(document.pressed == "선택삭제") {
        if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다\n\n답변글이 있는 게시글을 선택하신 경우\n답변글도 선택하셔야 게시글이 삭제됩니다."))
            return false;

        f.removeAttribute("target");
        f.action = g5_bbs_url+"/board_list_update.php";
    }

    return true;
}

// 선택한 게시물 복사 및 이동
function select_copy(sw) {
    var f = document.fboardlist;

    if (sw == "copy")
        str = "복사";
    else
        str = "이동";

    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

    f.sw.value = sw;
    f.target = "move";
    f.action = g5_bbs_url+"/move.php";
    f.submit();
}
</script>
<?php } ?>
<!-- } 게시판 목록 끝 -->
