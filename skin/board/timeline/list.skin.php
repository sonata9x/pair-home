<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<section id="bo_list" class="timeline-list">
    
    <div class="bo_nav">
        <!-- 게시판 검색 시작 { -->
        <fieldset id="bo_sch">
            <legend>게시물 검색</legend>
            
            <form name="fsearch" method="get">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
            <input type="hidden" name="sca" value="<?php echo $sca ?>">
            <input type="hidden" name="sop" value="and">
            <select name="sfl" id="sfl" class="frm_input">
                <option value="wr_subject"<?php echo get_selected($sfl, 'wr_subject', true); ?>>제목</option>
                <option value="wr_content"<?php echo get_selected($sfl, 'wr_content'); ?>>내용</option>
                <option value="wr_subject||wr_content"<?php echo get_selected($sfl, 'wr_subject||wr_content'); ?>>제목+내용</option>
                <?php if (is_community_installed()) { ?>
                <option value="ch_name"<?php echo get_selected($sfl, 'ch_name'); ?>>캐릭터명</option>
                <?php } ?>
            </select>
            <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx" class="sch_input frm_input" size="25" maxlength="20" placeholder="검색어를 입력해주세요">
            <button type="submit" value="검색" class="ra0_ui_btn btn_s"><i class="fa fa-search" aria-hidden="true"></i></button>
            </form>
        </fieldset>
        <!-- } 게시판 검색 끝 -->
        
        <div class="bo_btn">
            <?php if ($write_href) { ?><a href="<?php echo $write_href ?>" class="ra0_ui_btn btn_s"><i class="fa fa-pencil" aria-hidden="true"></i></a><?php } ?>
        </div>
    </div>

    <!-- 게시판 페이지 정보 및 버튼 시작 { -->
    <!-- <div id="bo_btn_top">
        <div id="bo_list_total">
            <span>전체 <?php echo number_format($total_count) ?>건</span>
            <?php echo $page ?> 페이지
        </div>
    </div> -->
    <!-- } 게시판 페이지 정보 및 버튼 끝 -->
    
    <div class="bo_cate">
        <?php if ($is_category) { ?>
        <nav id="bo_cate">
            <h2><?php echo $board['bo_subject'] ?> 카테고리</h2>
            <ul id="bo_cate_ul">
                <?php echo $category_option ?>
            </ul>
        </nav>
        <?php } ?>
    </div>

    <div class="timeline-container">
        <?php
        for ($i=0; $i<count($list); $i++) {
            $thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'], $board['bo_gallery_width'], $board['bo_gallery_height'], false, true);
            // wr_name(작성 당시 캐릭터명)으로 캐릭터 정보 조회 (대표 캐릭터가 아닌 글 작성 캐릭터)
            $display_name = $list[$i]['wr_name'] ?: $list[$i]['name'];
            $character = (is_community_installed() && function_exists('get_character_by_name')) ? get_character_by_name($display_name) : null;

            // 멤버 프로필 사진 가져오기
            $member_photo = '';
            if ($list[$i]['mb_id']) {
                $mem = get_member($list[$i]['mb_id'], 'mb_signature');
                $member_photo = $mem['mb_signature'] ?? '';
            }
        ?>
        <article class="timeline-item <?php if ($list[$i]['is_notice']) echo 'notice'; ?>">
            <div class="timeline-header">
                <div class="profile">
                    <span class="profile-img">
                        <?php if ($character && $list[$i]['ch_img']) { ?>
                            <img src="<?php echo $list[$i]['ch_img'] ?>" alt="">
                        <?php } else if ($member_photo) { ?>
                            <img src="<?php echo $member_photo ?>" alt="">
                        <?php } else { ?>
                            <i class="fa fa-user-circle"></i>
                        <?php } ?>
                    </span>
                    <div class="profile-info">
                        <?php if ($character && $character['tt_name']) { ?>
                        <span class="character-title" style="color: <?php echo $character['tt_color'] ?>">
                            <?php if ($character['tt_icon']) { if (strpos($character['tt_icon'], '/') === 0 || strpos($character['tt_icon'], 'http') === 0) { ?><img src="<?php echo htmlspecialchars($character['tt_icon']); ?>" alt="" style="max-width:16px;max-height:16px;vertical-align:middle;"><?php } else { ?><i class="<?php echo $character['tt_icon'] ?>"></i><?php } } ?>
                            <?php echo $character['tt_name'] ?>
                        </span>
                        <?php } ?>
                        <strong class="profile-name">
                            <?php echo $display_name ?>
                            <?php if ($character && $character['rk_name']) { ?>
                            <span class="character-rank" style="color: <?php echo $character['rk_color'] ?>">
                                [<?php echo $character['rk_name'] ?>]
                            </span>
                            <?php } ?>
                        </strong>
                        <span class="profile-date"><?php echo $list[$i]['datetime2'] ?></span>
                    </div>
                </div>
                
                <div class="timeline-subject">
                    <?php if ($list[$i]['is_notice'] && !$is_admin) { ?>
                        <!-- 공지사항이고 관리자가 아닌 경우 링크 없이 텍스트만 표시 -->
                        <span><?php echo $list[$i]['subject'] ?></span>
                    <?php } else { ?>
                        <a href="<?php echo $list[$i]['href'] ?>">
                            <?php echo $list[$i]['subject'] ?>
                            <?php if ($list[$i]['comment_cnt']) { ?>
                            <span class="sound_only">댓글</span><span class="cnt_cmt">+<?php echo $list[$i]['wr_comment']; ?></span>
                            <?php } ?>
                        </a>
                    <?php } ?>
                    <?php if ($list[$i]['is_notice']) { ?>
                    <span class="notice-badge">공지</span>
                    <?php } ?>
                </div>
            </div>
            
            <div class="timeline-content">
                
                <?php if ($thumb['src']) { ?>
                <div class="timeline-thumb">
                    <a href="<?php echo $list[$i]['href'] ?>">
                        <img src="<?php echo $thumb['src'] ?>" alt="<?php echo $thumb['alt'] ?>">
                    </a>
                </div>
                <?php } ?>
                
                <div class="timeline-text">
                    <?php
                    // ra0-content div 제거
                    $preview_text = preg_replace('/<div class="ra0-content">(.*)<\/div>/s', '$1', $list[$i]['wr_content']);
                    // 먼저 strip_tags로 HTML 제거
                    $preview_text = strip_tags($preview_text);
                    // 플레이스홀더를 이미지 아이콘으로 대체 (strip_tags 후)
                    $preview_text = preg_replace('/\{이미지:\d+(?:-\d+)?\}/', '<i class="fa-solid fa-image"></i> ', $preview_text);
                    echo cut_str($preview_text, 200);
                    ?>
                </div>
            </div>
            
            <div class="timeline-footer">
                <div class="timeline-actions">
                    <button type="button" class="btn-reply" data-wr-id="<?php echo $list[$i]['wr_id'] ?>">
                        <i class="fa-solid fa-comment-dots"></i> 답글
                        <?php if (!empty($list[$i]['reply_count']) && $list[$i]['reply_count'] > 0) { ?>
                        <span class="reply-count"><?php echo $list[$i]['reply_count'] ?></span>
                        <?php } ?>
                    </button>
                    <?php
                    $like_count = get_like_count($bo_table, $list[$i]['wr_id']);
                    $is_liked = check_liked($bo_table, $list[$i]['wr_id']);
                    ?>
                    <button type="button" class="btn-favorite <?php echo $is_liked ? 'active' : ''; ?>" data-wr-id="<?php echo $list[$i]['wr_id'] ?>" data-bo-table="<?php echo $bo_table ?>">
                        <i class="<?php echo $is_liked ? 'fa-solid' : 'fa-regular'; ?> fa-star"></i>
                        <span class="favorite-count"><?php echo $like_count > 0 ? $like_count : ''; ?></span>
                    </button>
                </div>

                <?php if ($like_count > 0) {
                    $likers = get_post_likers($bo_table, $list[$i]['wr_id'], 3);
                    $liker_names = array();
                    foreach ($likers as $liker) {
                        $liker_names[] = isset($liker['mb_name']) && $liker['mb_name'] ? $liker['mb_name'] : '익명';
                    }
                    if (!empty($liker_names)) {
                ?>
                <!-- Liked by -->
                <div class="liked-by" data-wr-id="<?php echo $list[$i]['wr_id'] ?>" data-bo-table="<?php echo $bo_table ?>">
                    <i class="fa-solid fa-star"></i>
                    <span class="liked-by-text">
                        <?php
                        if ($like_count == 1) {
                            echo '<strong>' . $liker_names[0] . '</strong>님이 관심을 표시했습니다';
                        } else if ($like_count == 2) {
                            echo '<strong>' . $liker_names[0] . '</strong>님, <strong>' . $liker_names[1] . '</strong>님이 관심을 표시했습니다';
                        } else {
                            echo '<strong>' . $liker_names[0] . '</strong>님 외 <strong>' . ($like_count - 1) . '명</strong>이 관심을 표시했습니다';
                        }
                        ?>
                    </span>
                </div>
                <?php } } ?>

                <!-- 답글 목록 -->
                <div class="replies-container" id="replies-<?php echo $list[$i]['wr_id'] ?>" style="<?php echo empty($list[$i]['recent_replies']) ? 'display:none;' : ''; ?>">
                    <?php if (!empty($list[$i]['recent_replies'])) { ?>
                        <?php foreach($list[$i]['recent_replies'] as $reply) {
                            // wr_name(작성 당시 캐릭터명)으로 캐릭터 정보 조회
                            $reply_name = $reply['wr_name'];
                            $reply_character = (is_community_installed() && function_exists('get_character_by_name')) ? get_character_by_name($reply_name) : null;

                            // 답글 첨부 이미지 조회
                            $reply_files = get_file($board['bo_table'], $reply['wr_id']);
                        ?>
                        <div class="reply-item">
                            <div class="reply-header">
                                <strong>
                                    <?php echo $reply_name ?>
                                    <?php if ($reply_character && $reply_character['rk_name']) { ?>
                                    <span class="character-rank-small" style="color: <?php echo $reply_character['rk_color'] ?>">
                                        [<?php echo $reply_character['rk_name'] ?>]
                                    </span>
                                    <?php } ?>
                                </strong>
                                <span class="reply-date"><?php echo date('m-d H:i', strtotime($reply['wr_datetime'])) ?></span>
                            </div>

                            <div class="reply-content">
                                <?php
                                // ra0-content div 제거 및 플레이스홀더를 이미지 아이콘으로 대체
                                $reply_text = preg_replace('/<div class="ra0-content">(.*)<\/div>/s', '$1', $reply['wr_content']);
                                $reply_text = preg_replace('/\{이미지:\d+(?:-\d+)?\}/', '<i class="fa-solid fa-image"></i>', $reply_text);
                                echo $reply_text;
                                ?>
                                <?php
                                // 답글 이미지 개수 표시
                                if (is_array($reply_files) && !empty($reply_files)) {
                                    $image_count = 0;
                                    foreach ($reply_files as $reply_file) {
                                        if (isset($reply_file['file']) && $reply_file['file']) {
                                            $image_count++;
                                        }
                                    }
                                    if ($image_count > 0) {
                                        echo '<div class="reply-file-info">';
                                        echo '<i class="fa fa-image"></i> 이미지 첨부 '.$image_count.'장';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <?php } ?>
                    <?php } ?>
                </div>
                
                <!-- 인라인 답글 폼 -->
                <div class="reply-form-container" id="reply-form-<?php echo $list[$i]['wr_id'] ?>" style="display:none;">
                    <form class="reply-form" data-parent-id="<?php echo $list[$i]['wr_id'] ?>" enctype="multipart/form-data">
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
                            <button type="button" class="btn btn_cancel" data-wr-id="<?php echo $list[$i]['wr_id'] ?>">취소</button>
                        </div>
                    </form>
                </div>
            </div>
        </article>
        <?php } ?>
        
        <?php if (count($list) == 0) { ?>
        <div class="empty_list">게시물이 없습니다.</div>
        <?php } ?>
    </div>
    
    <?php echo $write_pages; ?>
</section>

<script>
// 답글 버튼 클릭 - 답글 목록과 폼 토글
$(document).on('click', '.btn-reply', function() {
    var wr_id = $(this).data('wr-id');
    var repliesContainer = $('#replies-' + wr_id);
    var formContainer = $('#reply-form-' + wr_id);
    
    // 다른 열린 폼들 닫기
    $('.reply-form-container').not(formContainer).hide();
    
    // 답글이 있으면 답글 목록 표시
    if (repliesContainer.children().length > 0) {
        repliesContainer.toggle();
    }
    
    // 폼 토글
    formContainer.toggle();
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
                // 답글 등록 성공 - 페이지 리로드 (이미지 표시를 위해)
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

// 관심 버튼 클릭
$(document).on('click', '.btn-favorite', function() {
    var btn = $(this);
    var wrId = btn.data('wr-id');
    var boTable = btn.data('bo-table');
    var icon = btn.find('i');
    var countSpan = btn.find('.favorite-count');

    $.ajax({
        url: '<?php echo G5_BBS_URL ?>/like_toggle.php',
        type: 'POST',
        data: {
            bo_table: boTable,
            wr_id: wrId
        },
        dataType: 'json',
        success: function(response) {
            if (response.result === 'success') {
                if (response.action === 'like') {
                    btn.addClass('active');
                    icon.removeClass('fa-regular').addClass('fa-solid');
                    // 애니메이션
                    btn.addClass('favorite-pop');
                    setTimeout(function() {
                        btn.removeClass('favorite-pop');
                    }, 300);
                } else {
                    btn.removeClass('active');
                    icon.removeClass('fa-solid').addClass('fa-regular');
                }
                countSpan.text(response.like_count > 0 ? response.like_count : '');
            } else {
                alert(response.message || '오류가 발생했습니다.');
            }
        },
        error: function() {
            alert('서버 오류가 발생했습니다.');
        }
    });
});

// Liked by 클릭 - 관심 목록 모달
$(document).on('click', '.liked-by', function() {
    var wrId = $(this).data('wr-id');
    var boTable = $(this).data('bo-table');

    $.ajax({
        url: '<?php echo G5_BBS_URL ?>/like_list.php',
        type: 'GET',
        data: { bo_table: boTable, wr_id: wrId },
        dataType: 'json',
        success: function(response) {
            if (response.result === 'success') {
                var html = '';
                if (response.likers.length > 0) {
                    response.likers.forEach(function(liker) {
                        html += '<div class="liker-item">';
                        html += '<div class="liker-avatar">';
                        if (liker.profile) {
                            html += '<img src="' + liker.profile + '" alt="">';
                        } else {
                            html += '<i class="fa-solid fa-user"></i>';
                        }
                        html += '</div>';
                        html += '<div class="liker-info">';
                        html += '<div class="liker-name">' + liker.name + '</div>';
                        html += '<div class="liker-date">' + liker.datetime + '</div>';
                        html += '</div>';
                        html += '</div>';
                    });
                } else {
                    html = '<div class="likers-empty">관심을 표시한 사람이 없습니다.</div>';
                }
                $('#likers-modal-body').html(html);
                $('#likers-modal-overlay').css('display', 'flex');
            }
        }
    });
});

// 모달 닫기
$(document).on('click', '.likers-modal-close, .likers-modal-overlay', function(e) {
    if (e.target === this) {
        $('#likers-modal-overlay').hide();
    }
});
</script>

<!-- 관심 목록 모달 -->
<div class="likers-modal-overlay" id="likers-modal-overlay">
    <div class="likers-modal" onclick="event.stopPropagation();">
        <div class="likers-modal-header">
            <h3><i class="fa-solid fa-star"></i> 관심</h3>
            <button type="button" class="likers-modal-close">&times;</button>
        </div>
        <div class="likers-modal-body" id="likers-modal-body">
        </div>
    </div>
</div>