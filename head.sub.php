<?php
if (!defined('_GNUBOARD_')) exit;

check_site_login($is_member);

if (!isset($g5['title'])) {
    $g5['title'] = $config['cf_title'];
    $g5_head_title = $g5['title'];
} else {
    $g5_head_title = implode(' | ', array_filter(array($g5['title'], $config['cf_title'])));
}

$g5['title'] = strip_tags($g5['title']);
$g5_head_title = strip_tags($g5_head_title);

// 리다이렉트 로직 완전 제거

$login_action_url = G5_HTTP_BBS_URL."/login_check.php";

// iframe 보안 헤더 추가 - 외부 도메인에서 iframe 로드 차단
header('X-Frame-Options: SAMEORIGIN');
header('Content-Security-Policy: frame-ancestors \'self\'');


if (defined('_INDEX_')) { 
$main_link=get_main_link();
	echo "<script>if(parent && parent!=this) location.href='".$main_link."';</script>";
}
?>

<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<?php
// 검색 엔진 차단 설정
if (!empty($config['cf_noindex'])) {
    echo '<meta name="robots" content="noindex, nofollow">'.PHP_EOL;
}
// 추가 메타태그
if($config['cf_add_meta'])
    echo $config['cf_add_meta'].PHP_EOL;
?>
<title><?php echo $g5_head_title; ?></title>
<?php include_once(G5_PATH.'/og_meta.php'); ?>

<?php
// 파비콘 출력
if (!empty($design['favicon_url'])) {
    // 파일 확장자 확인
    $favicon_ext = strtolower(pathinfo($design['favicon_url'], PATHINFO_EXTENSION));
    $favicon_type = 'image/x-icon'; // 기본값

    if ($favicon_ext === 'png') {
        $favicon_type = 'image/png';
    } elseif ($favicon_ext === 'svg') {
        $favicon_type = 'image/svg+xml';
    } elseif ($favicon_ext === 'ico') {
        $favicon_type = 'image/x-icon';
    }

    echo '<link rel="icon" type="'.$favicon_type.'" href="'.htmlspecialchars($design['favicon_url']).'">'.PHP_EOL;
    echo '<link rel="shortcut icon" type="'.$favicon_type.'" href="'.htmlspecialchars($design['favicon_url']).'">'.PHP_EOL;
}
?>

<link rel="stylesheet" href="<?php echo G5_CSS_URL ?>/fonts.css.php">

<?php
if (defined('G5_IS_ADMIN')) {
    echo '<link rel="stylesheet" href="'.G5_CSS_URL.'/admin.css?ver='.G5_CSS_VER.'">'.PHP_EOL;
} else {
    echo '<link rel="stylesheet" href="'.G5_CSS_URL.'/default.css.php?ver='.G5_CSS_VER.'">'.PHP_EOL;
    echo '<link rel="stylesheet" href="'.G5_CSS_URL.'/ra0-content.css?ver='.G5_CSS_VER.'">'.PHP_EOL;
}

/* =======================================================
   iframe 모드 감지 — 감지 시 전역으로 배경/헤더/푸터 투명화
   - ?iframe=1 쿼리 파라미터 (명시적 opt-in)
   - Referer 에 iframe=1 포함 (POST→redirect→GET 체인 유지)
   - 페이지 이동 후에도 상태 유지: 내부 링크/폼에 iframe=1 자동 전파
   ※ Sec-Fetch-Dest 는 루트 /index.php 의 메인 iframe 에도 해당되므로 트리거로 쓰지 않음.
   ======================================================= */
$__is_iframe_embed = false;
if (!defined('G5_IS_ADMIN')) {
    if (!empty($_GET['iframe']) || !empty($_POST['iframe'])) {
        $__is_iframe_embed = true;
    } else {
        $__ref = $_SERVER['HTTP_REFERER'] ?? '';
        if ($__ref && preg_match('/[?&]iframe=1(&|$)/', $__ref)) $__is_iframe_embed = true;
    }
}
if ($__is_iframe_embed) {
    echo '<style id="iframe-embed-reset">';
    echo 'html{background-image:none!important;background-color:transparent!important;}';
    echo 'body{background:transparent!important;min-width:0!important;padding-top:0!important;}';
    echo '#hd,#ft,#hd_minimal,#mobile_menu_btn,#mobile_sidebar,#mobile_overlay,';
    echo '.lobby-header,.lobby-site-header,#lb-profile-menu,#sq-notif-panel,#sq-inventory-panel{display:none!important;}';
    // #container 는 기본 95vh+overflow:hidden 을 해제해 iframe 내부에서 콘텐츠가 잘리지 않게,
    // #wrapper 는 뷰포트 높이로 바운드 + overflow:auto 로 iframe 내부 스크롤이 정상 작동하도록.
    echo 'html,body{height:100%;}';
    echo '#wrapper{background:transparent!important;min-width:0!important;height:100vh!important;max-height:100dvh!important;overflow:auto!important;display:block!important;margin:0!important;}';
    echo '#container_wr{background:transparent!important;min-width:0!important;height:auto!important;max-height:none!important;overflow:visible!important;display:block!important;margin:0 auto!important;}';
    echo '#container{background:transparent!important;min-width:0!important;height:auto!important;max-height:none!important;overflow:visible!important;display:block!important;margin:0!important;}';
    echo '</style>';
    echo "<script>(function(){";
    echo "function sameOrigin(u){try{var p=new URL(u,location.href);return p.origin===location.origin?p:null;}catch(_){return null;}}";
    echo "function tagUrl(u){if(!u.searchParams.has('iframe'))u.searchParams.set('iframe','1');return u.toString();}";
    echo "document.addEventListener('click',function(e){";
    echo "var a=e.target.closest&&e.target.closest('a[href]');if(!a)return;";
    echo "var h=a.getAttribute('href');if(!h||/^(javascript:|mailto:|tel:|#)/i.test(h))return;";
    echo "if(a.target==='_blank')return;";
    echo "var u=sameOrigin(a.href);if(!u)return;a.href=tagUrl(u);";
    echo "},true);";
    echo "document.addEventListener('submit',function(e){";
    echo "var f=e.target;if(!f||f.tagName!=='FORM')return;";
    echo "var act=f.getAttribute('action')||location.href;";
    echo "var u=sameOrigin(act);if(!u)return;f.setAttribute('action',tagUrl(u));";
    echo "if(!f.querySelector('input[name=\"iframe\"]')){";
    echo "var i=document.createElement('input');i.type='hidden';i.name='iframe';i.value='1';f.appendChild(i);}";
    echo "},true);";
    echo "})();</script>";
}
?>

<script>
// 전역변수
var g5_url       = "<?php echo G5_URL ?>";
var g5_bbs_url   = "<?php echo G5_BBS_URL ?>";
var g5_is_member = "<?php echo isset($is_member)?$is_member:''; ?>";
var g5_is_admin  = "<?php echo isset($is_admin)?$is_admin:''; ?>";
var g5_is_mobile = "<?php echo G5_IS_MOBILE ?>";
var g5_bo_table  = "<?php echo isset($bo_table)?$bo_table:''; ?>";
var g5_sca       = "<?php echo isset($sca)?$sca:''; ?>";
var g5_editor    = "<?php echo ($config['cf_editor'] && $board['bo_use_dhtml_editor'])?$config['cf_editor']:''; ?>";
var g5_cookie_domain = "<?php echo G5_COOKIE_DOMAIN ?>";
<?php if(defined('G5_IS_ADMIN')) { ?>
var g5_admin_url = "<?php echo G5_ADMIN_URL; ?>";
<?php } ?>
</script>

<!-- 아이콘 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<!-- jQuery 3.7.1 -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- jQuery UI 1.14.2 (드래그 앤 드롭, 정렬 등) -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.14.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.14.2/jquery-ui.min.js"></script>

<!-- Prism.js 1.29.0 (코드 하이라이팅) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism-tomorrow.min.css">
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/prism.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>

<!-- 외부 콘텐츠 자동 임베드 -->
<script src="https://platform.twitter.com/widgets.js" defer></script>
<script src="https://embed.bsky.app/static/embed.js" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var embeds = document.querySelectorAll('.bluesky-embed[data-bluesky-uri-pending]');
    if (!embeds.length || !window.fetch) return;

    embeds.forEach(function(embed) {
        var pendingUri = embed.getAttribute('data-bluesky-uri-pending');
        if (!pendingUri || embed.dataset.blueskyResolving === '1') return;

        embed.dataset.blueskyResolving = '1';
        fetch('https://public.api.bsky.app/xrpc/app.bsky.feed.getPostThread?uri=' + encodeURIComponent(pendingUri) + '&depth=0', {
            credentials: 'omit'
        })
        .then(function(response) {
            if (!response.ok) throw new Error('Bluesky response error');
            return response.json();
        })
        .then(function(data) {
            var uri = data && data.thread && data.thread.post && data.thread.post.uri;
            if (!uri || uri.indexOf('at://') !== 0) throw new Error('Bluesky URI missing');

            embed.setAttribute('data-bluesky-uri', uri);
            embed.removeAttribute('data-bluesky-uri-pending');
            if (window.bluesky && typeof window.bluesky.scan === 'function') {
                window.bluesky.scan(embed.parentNode || document);
            }
        })
        .catch(function() {
            embed.classList.add('bluesky-embed-failed');
        });
    });
});
</script>

<!-- 기존 그누보드 스크립트 -->
<script src="<?php echo G5_JS_URL ?>/jquery.menu.js?ver=<?php echo G5_JS_VER ?>"></script>
<script src="<?php echo G5_JS_URL ?>/common.js?ver=<?php echo G5_JS_VER ?>"></script>
<script src="<?php echo G5_JS_URL ?>/wrest.js?ver=<?php echo G5_JS_VER ?>"></script>
<script src="<?php echo G5_JS_URL ?>/mobile_menu.js?ver=<?php echo G5_JS_VER ?>"></script>

<!-- RA0 알림 시스템 -->
<link rel="stylesheet" href="<?php echo G5_URL ?>/css/notification.css">
<script src="<?php echo G5_URL ?>/js/notification.js"></script>

<!-- RA0 이모티콘 시스템 -->
<link rel="stylesheet" href="<?php echo G5_URL ?>/css/emoticon.css">
<script src="<?php echo G5_URL ?>/js/emoticon.js"></script>

<?php
if(!defined('G5_IS_ADMIN'))
    echo $config['cf_add_script'];
?>

<script>//iframe src URL 호출하는 JAVA 로직
if(!parent || parent==this) $('html').addClass('single');
</script>

<!-- PWA Manifest -->
<link rel="manifest" href="<?php echo G5_URL; ?>/manifest.json">

<?php
// Web Push (로그인 회원 + HTTPS + 프론트만)
if (!defined('G5_IS_ADMIN') && !empty($is_member) && (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) {
    // Push 설정에서 공개키 로드
    $push_config_file = G5_DATA_PATH . '/push/push_config.php';
    $push_config_head = [];
    if (file_exists($push_config_file)) {
        include($push_config_file);
        $push_config_head = $push_config ?? [];
    }

    if (!empty($push_config_head['enabled']) && !empty($push_config_head['public_key'])) {
        echo '<script src="'.G5_URL.'/js/push.js"></script>'.PHP_EOL;
        echo '<script>'.PHP_EOL;
        echo 'document.addEventListener("DOMContentLoaded", function() {'.PHP_EOL;
        echo '    RA0Push.init("'.htmlspecialchars($push_config_head['public_key'], ENT_QUOTES).'");'.PHP_EOL;
        echo '});'.PHP_EOL;
        echo '</script>'.PHP_EOL;
    }
}
?>

</head>
<body>


<?php
// 위젯 시스템 - 모든 위젯 로드 (각 위젯이 출력 위치를 내부에서 판단)
// head.sub.php는 모든 위젯을 include하고, 각 위젯은 wg_location 설정에 따라 출력 여부를 결정
if (file_exists(G5_LIB_PATH.'/widget.lib.php')) {
    include_once(G5_LIB_PATH.'/widget.lib.php');
    display_widgets('header'); // location 필터 없음 - 모든 위젯 로드
}

// 통합 위젯 툴바 (위젯 로드 후 출력) - adm 페이지에서는 제외
$is_admin_page = (strpos($_SERVER['SCRIPT_NAME'], '/adm/') !== false);
if (!$is_admin_page && (!isset($GLOBALS['widget_toolbar_loaded']) || !$GLOBALS['widget_toolbar_loaded'])) {
    $GLOBALS['widget_toolbar_loaded'] = true;

    // 위젯 config.php에서 toggle_btn 설정 수집
    $widget_toggle_btns = array();
    $widget_dir = G5_PATH . '/widget';
    $widget_folders = glob($widget_dir . '/widget_*', GLOB_ONLYDIR);

    foreach ($widget_folders as $folder) {
        $config_file = $folder . '/config.php';
        if (file_exists($config_file)) {
            $wg_cfg = include($config_file);
            if (!empty($wg_cfg['toggle_btn'])) {
                if (!empty($wg_cfg['toggle_admin_only']) && !$is_admin) {
                    continue;
                }
                $widget_toggle_btns[] = $wg_cfg;
            }
        }
    }
?>
<!-- 통합 위젯 툴바 -->
<?php if ($is_admin || $is_member || !empty($widget_toggle_btns)) { ?>
<div id="widget-toolbar" class="widget-toolbar">
    <button type="button" id="widget-toolbar-toggle" class="widget-toolbar-toggle" aria-label="위젯 메뉴">
        <i class="fa-solid fa-gear"></i>
    </button>
    <div id="widget-toolbar-menu" class="widget-toolbar-menu">
        <?php if ($is_admin) { ?>
        <a href="<?php echo correct_goto_url(G5_ADMIN_URL); ?>" class="widget-toolbar-item" title="관리자">
            <i class="fa-solid fa-gear"></i>
        </a>
        <?php } ?>
        <?php if ($is_member) { ?>
        <a href="<?php echo G5_BBS_URL; ?>/chat.php" class="widget-toolbar-item" title="채팅">
            <i class="fa-solid fa-comment"></i>
        </a>
        <a href="<?php echo G5_BBS_URL; ?>/neighbor_feed.php" class="widget-toolbar-item" title="이웃 새글">
            <i class="fa-solid fa-rss"></i>
        </a>
        <a href="<?php echo G5_URL; ?>/mypage.php?tab=notifications" class="widget-toolbar-item" title="알림">
            <i class="fa-solid fa-bell"></i>
        </a>
        <?php } ?>
        <?php foreach ($widget_toggle_btns as $btn) {
            $btn_id = htmlspecialchars($btn['toggle_id'] ?? '', ENT_QUOTES);
            $btn_icon = htmlspecialchars($btn['toggle_icon'] ?? 'fa-solid fa-cube', ENT_QUOTES);
            $btn_title = htmlspecialchars($btn['toggle_title'] ?? '', ENT_QUOTES);
            $btn_target = htmlspecialchars($btn['toggle_target'] ?? '', ENT_QUOTES);
            $btn_type = htmlspecialchars($btn['toggle_type'] ?? 'visibility', ENT_QUOTES);
            $btn_function = htmlspecialchars($btn['toggle_function'] ?? '', ENT_QUOTES);
        ?>
        <button type="button" class="widget-toolbar-item widget-toggle-btn"
                id="<?php echo $btn_id; ?>"
                title="<?php echo $btn_title; ?>"
                data-target="<?php echo $btn_target; ?>"
                data-type="<?php echo $btn_type; ?>"
                data-function="<?php echo $btn_function; ?>">
            <i class="<?php echo $btn_icon; ?>"></i>
        </button>
        <?php } ?>
    </div>
</div>
<?php } ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var toolbar = document.getElementById('widget-toolbar');
    var toggleBtn = document.getElementById('widget-toolbar-toggle');
    var menu = document.getElementById('widget-toolbar-menu');

    if (!toolbar || !toggleBtn || !menu) return;

    // iframe#main 내부에서만 툴바 표시
    var isInsideIframe = window.self !== window.top;
    var isMainIframe = false;
    if (isInsideIframe) {
        try {
            var parentFrames = window.parent.document.getElementsByTagName('iframe');
            for (var i = 0; i < parentFrames.length; i++) {
                if (parentFrames[i].contentWindow === window.self && parentFrames[i].id === 'main') {
                    isMainIframe = true;
                    break;
                }
            }
        } catch (e) {}
    }

    // iframe#main이 아니면 숨김 (부모 문서 또는 다른 iframe)
    if (!isInsideIframe || !isMainIframe) {
        toolbar.style.display = 'none';
        return;
    }

    // 메인 토글 버튼
    toggleBtn.addEventListener('click', function() {
        menu.classList.toggle('show');
        toggleBtn.classList.toggle('active');
    });

    // 외부 클릭 시 닫기
    document.addEventListener('click', function(e) {
        if (!toolbar.contains(e.target)) {
            menu.classList.remove('show');
            toggleBtn.classList.remove('active');
        }
    });

    // 위젯 토글 버튼 처리
    document.querySelectorAll('.widget-toggle-btn').forEach(function(btn) {
        var targetId = btn.dataset.target;
        var toggleType = btn.dataset.type;
        var toggleFunction = btn.dataset.function;

        btn.addEventListener('click', function() {
            if (toggleType === 'custom' && toggleFunction) {
                var parts = toggleFunction.split('.');
                var obj = window;
                for (var i = 0; i < parts.length - 1; i++) {
                    obj = obj && obj[parts[i]] ? obj[parts[i]] : null;
                }
                if (obj && typeof obj[parts[parts.length - 1]] === 'function') {
                    obj[parts[parts.length - 1]]();
                } else {
                    var target = document.getElementById(targetId);
                    if (target) {
                        var isOpen = target.style.right === '0px';
                        target.style.right = isOpen ? '-400px' : '0px';
                        btn.classList.toggle('active', !isOpen);
                    }
                }
            } else {
                var target = document.getElementById(targetId);
                if (target) {
                    var isVisible = window.getComputedStyle(target).display !== 'none';
                    target.style.display = isVisible ? 'none' : 'flex';
                    btn.classList.toggle('active', !isVisible);
                }
            }
        });

        // 이벤트 리스너 (위젯에서 상태 변경 시 버튼 동기화)
        window.addEventListener('sticker-panel-toggle', function(e) {
            if (targetId === 'sticker-side-panel') {
                btn.classList.toggle('active', e.detail.visible);
            }
        });
    });
});
</script>
<?php } ?>

