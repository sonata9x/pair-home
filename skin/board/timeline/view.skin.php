<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<article id="bo_v" class="timeline-view">
    <!-- 스레드 표시 -->
    <div class="timeline-thread">
        <?php 
        // 스레드 렌더링 함수
        function render_thread($thread, $board_skin_url, $bo_table, $current_id = 0, $depth = 0, $parent_thread = null) {
            global $member, $is_admin, $board, $write_table;
            
            $is_current = ($thread['wr_id'] == $current_id);
            $indent_class = $depth > 0 ? 'thread-reply' : 'thread-origin';

            // wr_name(작성 당시 캐릭터명)으로 캐릭터 정보 조회 (대표 캐릭터가 아닌 글 작성 캐릭터)
            $thread_display_name = $thread['wr_name'];
            $thread_character = (is_community_installed() && function_exists('get_character_by_name')) ? get_character_by_name($thread_display_name) : null;

            // Get member profile photo
            $thread_member_photo = '';
            if ($thread['mb_id']) {
                $thread_mem = get_member($thread['mb_id'], 'mb_signature');
                $thread_member_photo = $thread_mem['mb_signature'] ?? '';
            }

            // Get parent info for mention
            $parent_mention = '';
            if ($parent_thread && $thread['tm_parent'] && $thread['tm_parent'] != $thread['tm_origin']) {
                // wr_name 사용 (글 작성 당시 저장된 이름)
                $parent_name = $parent_thread['wr_name'];
                $parent_mention = '@' . $parent_name;
            }
        ?>
        <div id="c_<?php echo $thread['wr_id'] ?>" class="thread-item <?php echo $indent_class ?> <?php echo $is_current ? 'current' : '' ?>">
            <div class="thread-header">
                <div class="profile">
                    <span class="profile-img">
                        <?php if ($thread_character && $thread_character['ch_portrait_image']) { ?>
                            <img src="<?php echo $thread_character['ch_portrait_image'] ?>" alt="">
                        <?php } else if ($thread_member_photo) { ?>
                            <img src="<?php echo $thread_member_photo ?>" alt="">
                        <?php } else { ?>
                            <i class="fa fa-user-circle"></i>
                        <?php } ?>
                    </span>
                    <div class="profile-info flex_end">
                        <div class="flex_c">
                            <?php if ($thread_character && $thread_character['tt_name']) { ?>
                            <span class="character-title" style="color: <?php echo $thread_character['tt_color'] ?>">
                                <?php if ($thread_character['tt_icon']) { if (strpos($thread_character['tt_icon'], '/') === 0 || strpos($thread_character['tt_icon'], 'http') === 0) { ?><img src="<?php echo htmlspecialchars($thread_character['tt_icon']); ?>" alt="" style="max-width:16px;max-height:16px;vertical-align:middle;"><?php } else { ?><i class="<?php echo $thread_character['tt_icon'] ?>"></i><?php } } ?>
                                <?php echo $thread_character['tt_name'] ?>
                            </span>
                            <?php } ?>
                            <strong class="profile-name">
                                <?php echo $thread_display_name ?>
                                <?php if ($thread_character && $thread_character['rk_name']) { ?>
                                <span class="character-rank" style="color: <?php echo $thread_character['rk_color'] ?>">
                                    [<?php echo $thread_character['rk_name'] ?>]
                                </span>
                                <?php } ?>
                            </strong>
                        </div>
                        <span class="profile-date"><?php echo date("Y-m-d H:i", strtotime($thread['wr_datetime'])) ?></span>
                    </div>
                </div>
                
                <?php if ($is_admin || ($member['mb_id'] && $member['mb_id'] == $thread['mb_id'])) { ?>
                <div class="thread-actions">
                    <a href="<?php echo G5_BBS_URL ?>/write.php?w=u&bo_table=<?php echo $bo_table ?>&wr_id=<?php echo $thread['wr_id'] ?>" class="ra0_ui_btn btn_s">
                        <i class="fa fa-edit"></i>
                    </a>
                    <button type="button" class="ra0_ui_btn btn-delete" data-wr-id="<?php echo $thread['wr_id'] ?>">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
                <?php } ?>
            </div>
            
            <div class="thread-content">
                <?php if ($parent_mention) { ?>
                <span class="reply-mention"><?php echo $parent_mention ?></span>
                <?php } ?>

                <?php
                // ra0-content div 래퍼 제거 (저장 시 자동 추가된 것)
                $raw_thread_content = $thread['wr_content'];
                $raw_thread_content = preg_replace('/<div class="ra0-content">(.*)<\/div>/s', '$1', $raw_thread_content);

                // 본문 처리 및 사용되지 않은 이미지 추출
                $thread_unused_images = array();
                $thread_content = process_content_with_unused_images(
                    $raw_thread_content,
                    $bo_table,
                    $thread['wr_id'],
                    $board['bo_use_dhtml_editor'],
                    $thread_unused_images
                );

                // 사용되지 않은 이미지 상단 출력
                if (!empty($thread_unused_images)) {
                    echo '<div class="unused-images-area">';
                    foreach ($thread_unused_images as $img_html) {
                        echo $img_html;
                    }
                    echo '</div>';
                }

                echo $thread_content;
                ?>
            </div>
            
            <div class="thread-footer">
                <?php if ($member['mb_level'] >= $board['bo_write_level']) { ?>
                <button type="button" class="btn-reply-inline" data-wr-id="<?php echo $thread['wr_id'] ?>">
                    <i class="fa fa-reply"></i>
                </button>
                <?php } ?>
            </div>
            
            <div class="reply-form-container" id="reply-form-<?php echo $thread['wr_id'] ?>" style="display:none;">
                <form class="reply-form" data-parent-id="<?php echo $thread['wr_id'] ?>" enctype="multipart/form-data">
                    <?php if (!$member['mb_id']) { ?>
                    <div class="reply-guest">
                        <input type="text" name="wr_name" placeholder="이름" required class="frm_input">
                        <input type="password" name="wr_password" placeholder="비밀번호" required class="frm_input">
                    </div>
                    <?php } ?>
                    <textarea name="wr_content" placeholder="답글을 입력하세요" required></textarea>
                    <div class="reply-file-upload">
                        <label>이미지 첨부 (최대 2개)</label>
                        <input type="file" name="bf_file[]" accept="image/*" class="reply-file-input">
                        <input type="file" name="bf_file[]" accept="image/*" class="reply-file-input">
                    </div>
                    <div class="reply-buttons">
                        <button type="submit" class="btn btn_submit">등록</button>
                        <button type="button" class="btn btn_cancel" data-wr-id="<?php echo $thread['wr_id'] ?>">취소</button>
                    </div>
                </form>
            </div>
            
            <?php
            // 자식 스레드 렌더링
            if (!empty($thread['children'])) {
                foreach ($thread['children'] as $child) {
                    render_thread($child, $board_skin_url, $bo_table, $current_id, $depth + 1, $thread);
                }
            }
            ?>
        </div>
        <?php
        }
        
        // 스레드 트리 렌더링
        if (isset($thread_tree) && is_array($thread_tree)) {
            foreach ($thread_tree as $thread) {
                render_thread($thread, $board_skin_url, $bo_table, $view['wr_id']);
            }
        }
        ?>
    </div>
    
    <div id="bo_v_bot">
        <!-- 링크 버튼 시작 { -->
        <ul class="bo_v_com">
            <li><a href="<?php echo G5_BBS_URL; ?>/share_popup.php?bo_table=<?php echo $bo_table; ?>&wr_id=<?php echo $wr_id; ?>" class="ra0_ui_btn btn_s" onclick="window.open(this.href, 'share_popup', 'width=600,height=500,scrollbars=yes'); return false;">공유</a></li>
            <?php if ($update_href) { ?><li><a href="<?php echo $update_href ?>" class="ra0_ui_btn btn_s">수정</a></li><?php } ?>
            <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href ?>" class="ra0_ui_btn btn_a" onclick="del(this.href); return false;">삭제</a></li><?php } ?>
            <?php if ($copy_href) { ?><li><a href="<?php echo $copy_href ?>" class="ra0_ui_btn" onclick="board_move(this.href); return false;">복사</a></li><?php } ?>
            <?php if ($move_href) { ?><li><a href="<?php echo $move_href ?>" class="ra0_ui_btn" onclick="board_move(this.href); return false;">이동</a></li><?php } ?>
            <?php if ($search_href) { ?><li><a href="<?php echo $search_href ?>" class="ra0_ui_btn btn_s">목록</a></li><?php } ?>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="ra0_ui_btn btn_s">글쓰기</a></li><?php } ?>
        </ul>
        <!-- } 링크 버튼 끝 -->
    </div>
</article>

<script>
// 답글 버튼 클릭
$(document).on('click', '.btn-reply-inline', function() {
    var wr_id = $(this).data('wr-id');
    $('.reply-form-container').hide();
    $('#reply-form-' + wr_id).show();
});

// 취소 버튼 클릭
$(document).on('click', '.btn_cancel', function() {
    var wr_id = $(this).data('wr-id');
    $('#reply-form-' + wr_id).hide();
});

// 답글 폼 제출
$(document).on('submit', '.reply-form', function(e) {
    e.preventDefault();

    var form = $(this);
    var parent_id = form.data('parent-id');

    // FormData 객체 생성 (파일 업로드 지원)
    var formData = new FormData(form[0]);
    formData.append('action', 'write_reply');
    formData.append('bo_table', '<?php echo $bo_table ?>');
    formData.append('tm_parent', parent_id);

    $.ajax({
        url: '<?php echo G5_BBS_URL ?>/ajax.timeline.php',
        type: 'POST',
        data: formData,
        processData: false,  // 파일 업로드 시 필수
        contentType: false,  // 파일 업로드 시 필수
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('답글이 등록되었습니다.');
                location.reload();
            } else {
                alert(response.error || '오류가 발생했습니다.');
            }
        },
        error: function() {
            alert('서버 오류가 발생했습니다.');
        }
    });
});

// 삭제 버튼 클릭
$(document).on('click', '.btn-delete', function() {
    if (!confirm('정말 삭제하시겠습니까?')) return;
    
    var wr_id = $(this).data('wr-id');
    
    $.ajax({
        url: '<?php echo G5_BBS_URL ?>/ajax.timeline.php',
        type: 'POST',
        data: {
            action: 'delete_reply',
            bo_table: '<?php echo $bo_table ?>',
            wr_id: wr_id
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('삭제되었습니다.');
                location.reload();
            } else {
                alert(response.error || '오류가 발생했습니다.');
            }
        },
        error: function() {
            alert('서버 오류가 발생했습니다.');
        }
    });
});

function board_move(href)
{
    window.open(href, "boardmove", "left=50, top=50, width=500, height=550, scrollbars=1");
}

// 알림 클릭 시 해당 답글로 스크롤
$(function() {
    var hash = window.location.hash;
    var $target = hash ? $(hash) : $('.thread-item.current');
    if ($target.length && !$target.hasClass('thread-origin')) {
        setTimeout(function() {
            $target[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            $target.addClass('highlight');
            setTimeout(function() { $target.removeClass('highlight'); }, 2000);
        }, 200);
    }
});
</script>

<!-- 게시글 보기 끝 -->