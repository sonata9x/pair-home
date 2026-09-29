<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

echo '<link rel="stylesheet" href="'.$board_skin_url.'/style.css">';
?>
<!-- 게시물 읽기 시작 { -->

<article id="bo_v" class="view_article" style="width:<?php echo $width; ?>">
    <!-- 제목 영역 -->
    <header class="view_header">
        <?php if ($category_name) { ?>
        <span class="bo_v_cate"><?php echo $view['ca_name']; ?></span>
        <?php } ?>
        <h2 class="bo_v_tit"><?php echo cut_str(get_text($view['wr_subject']), 70); ?></h2>
    </header>

    <!-- 작성자 정보 + 버튼 영역 -->
    <div class="view_meta">
        <div class="meta_author">
            <div class="author_profile">
                <?php
                if ($view['mb_id']) {
                    $author = get_member($view['mb_id']);
                    if ($author['mb_signature']) {
                        echo '<img src="'.$author['mb_signature'].'" alt="'.get_text($view['name']).'">';
                    }
                }
                ?>
            </div>
            <div class="author_info">
                <div class="author_line">
                    <strong class="author_name"><?php echo $view['name'] ?></strong>
                    <?php if ($view['mb_id']) { ?>
                    <span class="author_id">@<?php echo $view['mb_id'] ?></span>
                    <?php } ?>
                </div>
                <span class="view_date"><i class="fa fa-clock-o"></i> <?php echo date("Y-m-d H:i", strtotime($view['wr_datetime'])) ?></span>
            </div>
        </div>

        <!-- 게시물 버튼 -->
        <div id="bo_v_top" class="view_buttons_inline">
	        <?php ob_start(); ?>

	        <ul class="btn_bo_user bo_v_com">
				<li><a href="<?php echo $list_href ?>" class="btn_b01 btn" title="목록"><i class="fa fa-list" aria-hidden="true"></i><span class="sound_only">목록</span></a></li>
                <li><a href="<?php echo G5_BBS_URL; ?>/share_popup.php?bo_table=<?php echo $bo_table; ?>&wr_id=<?php echo $wr_id; ?>" class="btn_b01 btn" title="공유" onclick="window.open(this.href, 'share_popup', 'width=600,height=500,scrollbars=yes'); return false;"><i class="fa fa-share-alt" aria-hidden="true"></i><span class="sound_only">공유</span></a></li>
	            <?php if ($reply_href) { ?><li><a href="<?php echo $reply_href ?>" class="btn_b01 btn" title="답변"><i class="fa fa-reply" aria-hidden="true"></i><span class="sound_only">답변</span></a></li><?php } ?>
	            <?php if ($update_href) { ?><li><a href="<?php echo $update_href ?>" class="btn_b01 btn" title="수정"><i class="fa fa-pencil" aria-hidden="true"></i><span class="sound_only">수정</span></a></li><?php } ?>
	        	<?php if($update_href || $delete_href || $copy_href || $move_href || $search_href) { ?>
	        	<li>
	        		<button type="button" class="btn_more_opt is_view_btn btn_b01 btn"><i class="fa fa-ellipsis-v" aria-hidden="true"></i><span class="sound_only">게시판 리스트 옵션</span></button>
		        	<ul class="more_opt is_view_btn"> 
			            <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href ?>" onclick="del(this.href); return false;">삭제</a></li><?php } ?>
			            <?php if ($copy_href) { ?><li><a href="<?php echo $copy_href ?>" onclick="board_move(this.href); return false;">복사</a></li><?php } ?>
			            <?php if ($move_href) { ?><li><a href="<?php echo $move_href ?>" onclick="board_move(this.href); return false;">이동</a></li><?php } ?>
			        </ul> 
	        	</li>
	        	<?php } ?>
	        </ul>
	        <script>

            jQuery(function($){
                // 게시판 보기 버튼 옵션
				$(".btn_more_opt.is_view_btn").on("click", function(e) {
                    e.stopPropagation();
				    $(".more_opt.is_view_btn").toggle();
				});
                $(document).on("click", function (e) {
                    if(!$(e.target).closest('.is_view_btn').length) {
                        $(".more_opt.is_view_btn").hide();
                    }
                });
            });
            </script>
	        <?php
	        $link_buttons = ob_get_contents();
	        ob_end_flush();
			?>
        </div>
    </div>
    <!-- } 작성자 정보 + 버튼 끝 -->

    <!-- 본문 영역 -->
    <section class="view_content_wrap">
        <!-- 본문 내용 시작 { -->
        <div id="bo_v_con">
        <?php
        // 사용되지 않은 이미지 상단 출력
        if (!empty($view['unused_images'])) {
            echo '<div class="unused-images-area">';
            foreach ($view['unused_images'] as $img_html) {
                echo $img_html;
            }
            echo '</div>';
        }

        // rich_content는 이미 view.php에서 autolink 적용됨
        echo $view['rich_content'];
        ?>
        </div>
        <!-- } 본문 내용 끝 -->

        <?php if ($is_signature) { ?><p><?php echo $signature ?></p><?php } ?>

    </section>

    <?php
    $cnt = 0;
    if ($view['file']['count']) {
        for ($i=0; $i<count($view['file']); $i++) {
            if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view'])
                $cnt++;
        }
    }
	?>

    <?php if($cnt) { ?>
    <!-- 첨부파일 시작 { -->
    <section id="bo_v_file">
        <h2>첨부파일</h2>
        <ul>
        <?php
        // 가변 파일
        for ($i=0; $i<count($view['file']); $i++) {
            if (isset($view['file'][$i]['source']) && $view['file'][$i]['source'] && !$view['file'][$i]['view']) {
         ?>
            <li>
               	<i class="fa fa-folder-open" aria-hidden="true"></i>
                <a href="<?php echo $view['file'][$i]['href'];  ?>" class="view_file_download">
                    <strong><?php echo $view['file'][$i]['source'] ?></strong> <?php echo $view['file'][$i]['content'] ?> (<?php echo $view['file'][$i]['size'] ?>)
                </a>
                <br>
                <span class="bo_v_file_cnt"><?php echo $view['file'][$i]['download'] ?>회 다운로드 | DATE : <?php echo $view['file'][$i]['datetime'] ?></span>
            </li>
        <?php
            }
        }
         ?>
        </ul>
    </section>
    <!-- } 첨부파일 끝 -->
    <?php } ?>

    <?php if(isset($view['link']) && array_filter($view['link'])) { ?>
    <!-- 관련링크 시작 { -->
    <section id="bo_v_link">
        <h2>관련링크</h2>
        <ul>
        <?php
        // 링크
        $cnt = 0;
        for ($i=1; $i<=count($view['link']); $i++) {
            if ($view['link'][$i]) {
                $cnt++;
                $link = cut_str($view['link'][$i], 70);
            ?>
            <li>
                <i class="fa fa-link" aria-hidden="true"></i>
                <a href="<?php echo $view['link_href'][$i] ?>" target="_blank">
                    <strong><?php echo $link ?></strong>
                </a>
                <br>
                <span class="bo_v_link_cnt"><?php echo $view['link_hit'][$i] ?>회 연결</span>
            </li>
            <?php
            }
        }
        ?>
        </ul>
    </section>
    <!-- } 관련링크 끝 -->
    <?php } ?>
    
    <?php if ($prev_href || $next_href) { ?>
    <ul class="bo_v_nb">
        <?php if ($prev_href) { ?><li class="btn_prv"><span class="nb_tit"><i class="fa fa-chevron-up" aria-hidden="true"></i> 이전글</span><a href="<?php echo $prev_href ?>"><?php echo $prev_wr_subject;?></a> <span class="nb_date"><?php echo str_replace('-', '.', substr($prev_wr_date, '2', '8')); ?></span></li><?php } ?>
        <?php if ($next_href) { ?><li class="btn_next"><span class="nb_tit"><i class="fa fa-chevron-down" aria-hidden="true"></i> 다음글</span><a href="<?php echo $next_href ?>"><?php echo $next_wr_subject;?></a>  <span class="nb_date"><?php echo str_replace('-', '.', substr($next_wr_date, '2', '8')); ?></span></li><?php } ?>
    </ul>
    <?php } ?>

    <?php
    // 코멘트 입출력
    include_once(G5_BBS_PATH.'/view_comment.php');
	?>
</article>
<!-- } 게시판 읽기 끝 -->

<script>
<?php if ($board['bo_download_point'] < 0) { ?>
$(function() {
    $("a.view_file_download").click(function() {
        if(!g5_is_member) {
            alert("다운로드 권한이 없습니다.\n회원이시라면 로그인 후 이용해 보십시오.");
            return false;
        }

        var msg = "파일을 다운로드 하시면 포인트가 차감(<?php echo number_format($board['bo_download_point']) ?>점)됩니다.\n\n포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.\n\n그래도 다운로드 하시겠습니까?";

        if(confirm(msg)) {
            var href = $(this).attr("href")+"&js=on";
            $(this).attr("href", href);

            return true;
        } else {
            return false;
        }
    });
});
<?php } ?>

function board_move(href)
{
    window.open(href, "boardmove", "left=50, top=50, width=500, height=550, scrollbars=1");
}
</script>

<script>
// 타일 그리드 높이 정렬
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tile-grid').forEach(function(grid) {
        const images = grid.querySelectorAll('.tile-item img');
        if (images.length === 0) return;

        let allLoaded = 0;
        images.forEach(function(img) {
            if (img.complete) {
                allLoaded++;
            } else {
                img.addEventListener('load', function() {
                    allLoaded++;
                    if (allLoaded === images.length) {
                        adjustTileHeights(grid);
                    }
                });
            }
        });

        if (allLoaded === images.length) {
            adjustTileHeights(grid);
        }
    });

    function adjustTileHeights(grid) {
        const images = grid.querySelectorAll('.tile-item img');
        if (images.length === 0) return;

        let minHeight = Infinity;

        // 가장 짧은 높이 찾기
        images.forEach(function(img) {
            if (img.naturalHeight < minHeight) {
                minHeight = img.naturalHeight;
            }
        });

        // 임시로 minHeight 적용하여 총 너비 계산
        let totalWidth = 0;
        images.forEach(function(img) {
            const aspectRatio = img.naturalWidth / img.naturalHeight;
            const calculatedWidth = minHeight * aspectRatio;
            totalWidth += calculatedWidth;
        });

        // gap 값 추가 (이미지 개수 - 1) * 10px
        totalWidth += (images.length - 1) * 10;

        // 컨테이너 너비 확인
        const containerWidth = grid.offsetWidth;

        // 총 너비가 컨테이너를 초과하면 높이를 비례적으로 축소
        let finalHeight = minHeight;
        if (totalWidth > containerWidth) {
            finalHeight = Math.floor(minHeight * (containerWidth / totalWidth));
        }

        // 모든 이미지에 최종 높이 적용
        images.forEach(function(img) {
            img.style.height = finalHeight + 'px';
            img.style.width = 'auto';
            img.style.maxWidth = 'none';
        });
    }
});
</script>
<!-- } 게시글 읽기 끝 -->