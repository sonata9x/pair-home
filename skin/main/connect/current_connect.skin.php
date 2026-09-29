<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$has_community = function_exists('get_character_profile_url');
$has_chat = function_exists('chat_get_private_link');
?>

<style>
#current_connect {
    max-width: 960px;
    margin: 0 auto;
    padding: var(--spacing-lg);
}

#current_connect .connect_header {
    display: flex;
    align-items: baseline;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-md);
    padding-bottom: var(--spacing-sm);
    border-bottom: 1px solid var(--container-border-color);
}

#current_connect .connect_header h2 {
    font-family: var(--title-font-family);
    font-size: 1.1em;
    color: var(--title-font-color);
    margin: 0;
}

#current_connect .connect_count {
    font-size: 0.85em;
    color: var(--text-muted);
}

#current_connect .connect_count strong {
    color: var(--accent-color);
    font-weight: 700;
}

#current_connect ul {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: var(--spacing-xs);
}

#current_connect li {
    background: var(--container-bg-color);
    border: 1px solid var(--container-border-color);
    border-radius: 6px;
    padding: 5px 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: border-color var(--transition-fast);
    font-size: 0.88em;
    color: var(--content-font-color);
    position: relative;
    overflow: visible;
    backdrop-filter: blur(4px);
}

#current_connect li:hover {
    border-color: var(--accent-color);
}

#current_connect .crt_profile {
    flex-shrink: 0;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    overflow: hidden;
    background: var(--container-bg-color);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--container-border-color);
}

#current_connect .crt_profile img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

#current_connect .crt_profile .crt_no_img {
    font-size: 12px;
    color: var(--text-muted);
    line-height: 1;
}

#current_connect .crt_info {
    min-width: 0;
    flex: 1;
    display: flex;
    align-items: center;
    gap: 4px;
}

#current_connect .crt_name {
    font-weight: 600;
    color: var(--content-font-color);
    white-space: nowrap;
    transition: color var(--transition-fast);
}

#current_connect li[data-has-menu] {
    cursor: pointer;
}

#current_connect li[data-has-menu]:hover .crt_name {
    color: var(--accent-color);
}

/* 컨텍스트 메뉴 (body에 붙음) */
#crt_context_menu {
    display: none;
    position: fixed;
    z-index: 10000;
    min-width: 130px;
    padding: 4px 0;
    background: var(--card-bg-color);
    border: 1px solid var(--container-border-color);
    border-radius: 8px;
    box-shadow: 0 6px 4px rgb(from var(--gray-900) r g b / 0.3);
    backdrop-filter: blur(4px);
}

#crt_context_menu a,
#crt_context_menu span.crt-sv-disabled {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 7px 12px;
    color: var(--content-font-color);
    text-decoration: none;
    font-size: 0.85em;
    white-space: nowrap;
}

#crt_context_menu a:hover {
    background: rgba(255,255,255,0.06);
}

#crt_context_menu a i,
#crt_context_menu span i {
    width: 14px;
    text-align: center;
    opacity: 0.7;
}

#crt_context_menu span.crt-sv-disabled {
    color: var(--text-muted);
    cursor: default;
    font-style: italic;
}

/* 캐릭터 없는 경우 이름 스타일 */
#current_connect .crt_name_plain {
    font-weight: 600;
    color: var(--content-font-color);
    white-space: nowrap;
}

#current_connect .crt_mb_id {
    font-size: 0.8em;
    color: var(--text-muted);
    white-space: nowrap;
    opacity: 0.7;
}

#current_connect .crt_lct {
    display: none;
}

/* 관리자용: 위치 표시 */
#current_connect li.crt_show_loc .crt_lct {
    display: inline;
    font-size: 0.8em;
    color: var(--text-muted);
    margin-left: 4px;
}

#current_connect .crt_lct a {
    color: var(--accent-color);
    text-decoration: none;
}

#current_connect .empty_li {
    width: 100%;
    text-align: center;
    padding: var(--spacing-xl);
    color: var(--text-muted);
    font-size: 0.9em;
    background: var(--container-bg-color);
    border: 1px dashed var(--container-border-color);
    border-radius: var(--card-border-radius);
}

@media (max-width: 768px) {
    #current_connect { padding: var(--spacing-md); }
}
</style>

<!-- 현재접속자 목록 시작 { -->
<div id="current_connect">
    <div class="connect_header">
        <h2>현재 접속자</h2>
        <span class="connect_count"><strong><?php echo count($list); ?></strong>명</span>
    </div>

    <ul>
    <?php
    for ($i=0; $i<count($list); $i++) {
        // 캐릭터 우선, 없으면 회원 정보
        $has_char = !empty($list[$i]['ch_name']);
        $display_name = $has_char ? $list[$i]['ch_name'] : $list[$i]['name'];
        $display_img = '';
        if ($has_char && !empty($list[$i]['ch_portrait'])) {
            $display_img = $list[$i]['ch_portrait'];
        } elseif (!empty($list[$i]['mb_signature'])) {
            $display_img = $list[$i]['mb_signature'];
        }

        $location = $list[$i]['lo_location'];
        $show_loc = ($list[$i]['lo_url'] && $is_admin == 'super');
        if ($show_loc) {
            $display_location = '<a href="'.$list[$i]['lo_url'].'">'.$location.'</a>';
        } else {
            $display_location = $location;
        }

        $mb_id = $list[$i]['mb_id'];
    ?>
        <li<?php if ($show_loc) echo ' class="crt_show_loc"'; ?><?php if ($has_community && $mb_id) echo ' data-has-menu="1"'; ?>>
            <span class="crt_profile">
                <?php if ($display_img): ?>
                    <img src="<?php echo htmlspecialchars($display_img); ?>" alt="<?php echo htmlspecialchars($display_name); ?>">
                <?php else: ?>
                    <span class="crt_no_img"><i class="fa fa-user"></i></span>
                <?php endif; ?>
            </span>
            <div class="crt_info">
                <span class="crt_name"><?php echo htmlspecialchars($display_name); ?></span>
                <?php if ($has_char && $mb_id): ?>
                <span class="crt_mb_id">@<?php echo htmlspecialchars($mb_id); ?></span>
                <?php endif; ?>
                <?php if ($show_loc): ?>
                <span class="crt_lct"><?php echo $display_location; ?></span>
                <?php endif; ?>
            </div>
            <?php if ($has_community && $mb_id):
                if ($has_char) {
                    $menu_html = '<a href="'.get_character_profile_url($mb_id).'"><i class="fa fa-id-card"></i> 프로필 보기</a>';
                } else {
                    $menu_html = '<span class="crt-sv-disabled"><i class="fa fa-id-card"></i> 프로필 없음</span>';
                }
                // 대화하기 (본인 제외)
                if ($has_chat && $mb_id != $member['mb_id']) {
                    $chat_link = chat_get_private_link($member['mb_id'], $mb_id);
                    if ($chat_link) {
                        $menu_html .= '<a href="'.$chat_link['url'].'"><i class="fa fa-comment"></i> 대화하기</a>';
                    }
                }
            ?>
            <input type="hidden" class="crt-menu-data" value="<?php echo htmlspecialchars($menu_html); ?>">
            <?php endif; ?>
        </li>
    <?php
    }
    if ($i == 0) {
        echo '<li class="empty_li">현재 접속자가 없습니다</li>';
    }
    ?>
    </ul>
</div>
<!-- } 현재접속자 목록 끝 -->
<div id="crt_context_menu"></div>

<script>
$(function() {
    var $menu = $('#crt_context_menu');

    $(document).on('click', '#current_connect li[data-has-menu]', function(e) {
        e.stopPropagation();
        var $li = $(this);
        var html = $li.find('.crt-menu-data').val();
        var rect = $li[0].getBoundingClientRect();

        $menu.html(html).css({
            display: 'block',
            top: rect.bottom - 8,
            left: rect.right - $menu.outerWidth()
        });
    });

    $(document).on('click', function() {
        $menu.hide();
    });

    $(document).on('click', '#crt_context_menu', function(e) {
        e.stopPropagation();
    });
});
</script>
