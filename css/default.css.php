<?php
include_once('../_common.php');

// 캐시 헤더 설정
header('Content-Type: text/css; charset=utf-8');

// 디자인 설정 불러오기
$design = get_design_config();
$header_font_family = ra0_css_font_family($design['header_font_family']);
$content_font_family = ra0_css_font_family($design['content_font_family']);
$title_font_family = ra0_css_font_family($design['title_font_family']);
?>

:root {
    /* 메인 컬러 */
    --primary-color: <?php echo $design['primary_color']; ?>;
    --secondary-color: <?php echo $design['secondary_color']; ?>;
    --accent-color: <?php echo $design['accent_color']; ?>;

    /* 헤더 설정 */
    --header-bg-color: <?php echo $design['header_bg_color']; ?>;
    --header-font-color: <?php echo $design['header_font_color']; ?>;
    --header-font-family: <?php echo $header_font_family; ?>;
    --header-font-size: <?php echo $design['header_font_size']; ?>px;
    --header-height: <?php echo $design['header_height']; ?>px;
    --hd-menu-justify: <?php echo $design['header_menu_justify'] ?? 'flex-start'; ?>;
    --hd-menu-align: <?php echo $design['header_menu_align'] ?? 'flex-start'; ?>;
    --sub-menu-font-size: calc(var(--header-font-size) * 0.8);
    --sub-menu-icon-size: calc(var(--sub-menu-font-size) * 0.6);

    /* 컨테이너 & 카드 */
    --container-bg-color: <?php echo $design['container_bg_color']; ?>;
    --container-border-color: <?php echo $design['container_border_color']; ?>;
    --container-border-radius: <?php echo $design['container_border_radius']; ?>px;
    --card-bg-color: <?php echo $design['card_bg_color']; ?>;
    --card-border-color: <?php echo $design['card_border_color']; ?>;
    --card-border-radius: <?php echo $design['card_border_radius']; ?>px;

    /* 폰트 */
    --content-font-family: <?php echo $content_font_family; ?>;
    --content-font-size: <?php echo $design['content_font_size']; ?>px;
    --content-font-color: <?php echo $design['content_font_color']; ?>;
    --title-font-family: <?php echo $title_font_family; ?>;
    --title-font-size: <?php echo $design['title_font_size']; ?>px;
    --title-font-color: <?php echo $design['title_font_color']; ?>;
    --sub-title-font-size: calc(var(--title-font-size) * 0.65);

    /* 버튼 */
    --btn-primary-text: <?php echo $design['btn_primary_text']; ?>;
    --btn-primary-bg: <?php echo $design['btn_primary_bg']; ?>;
    --btn-primary-radius: <?php echo $design['btn_primary_radius']; ?>px;
    --btn-secondary-text: <?php echo $design['btn_secondary_text']; ?>;
    --btn-secondary-bg: <?php echo $design['btn_secondary_bg']; ?>;
    --btn-secondary-radius: <?php echo $design['btn_secondary_radius']; ?>px;
    --btn-accent-text: <?php echo $design['btn_accent_text']; ?>;
    --btn-accent-bg: <?php echo $design['btn_accent_bg']; ?>;
    --btn-accent-radius: <?php echo $design['btn_accent_radius']; ?>px;

    /* 폼 */
    --form-bg-color: <?php echo $design['form_bg_color']; ?>;
    --form-border-color: <?php echo $design['form_border_color']; ?>;
    --form-border-radius: <?php echo $design['form_border_radius']; ?>px;
    --form-text-color: <?php echo $design['form_text_color']; ?>;

    /* 배경 설정 */
    --bg-color: <?php echo $design['bg_color'] ?? '#171717'; ?>;
    --bg-image-url: url('<?php echo $design['bg_image_url'] ?>');
    --bg-repeat: <?php echo $design['bg_repeat'] ?? 'no-repeat'; ?>;
    --bg-position: <?php echo $design['bg_position'] ?? 'center center'; ?>;
    --bg-size: <?php echo $design['bg_size'] ?? 'cover'; ?>;

    --hp-color: #bb2727;
    --mp-color: #2673c4;
    --sp-color: #fcd34d;

    /* 그레이톤 */
    --white: #ffffff;
    --black: #000000;
    --gray-50: #fafafa;
    --gray-100: #f5f5f5;
    --gray-200: #e9e9e9;
    --gray-300: #d6d6d6;
    --gray-400: #a5a5a5;
    --gray-500: #767676;
    --gray-600: #575757;
    --gray-700: #434343;
    --gray-800: #2b2b2b;
    --gray-900: #1b1b1b;

    /* 추가 유틸리티 컬러 */
    --border-light: var(--gray-200);
    --border-medium: var(--gray-300);
    --text-primary: var(--gray-900);
    --text-secondary: var(--gray-600);
    --text-muted: var(--gray-500);

    /* Status Colors */
    --success-color: #54cd9a;
    --warning-color: #ffdb71;
    --error-color: #ff6b6b;
    --info-color: #3b82f6;

    /* 글래스 이펙트 - 극도로 세련됨 */
    --bg-primary: linear-gradient(135deg,
    rgb(from var(--bg-color) r g b / 3%) 0%,
    rgb(from var(--bg-color) r g b / 2%) 100%);

    --bg-secondary: linear-gradient(135deg,
        rgb(from var(--bg-color) r g b / 90%) 0%,
        rgb(from var(--bg-color) r g b / 80%) 100%);

    --bg-glass: rgb(from var(--bg-color) r g b / 60%);
    --bg-glass-dark: rgb(from var(--bg-color) r g b / 80%);

    --bg-overlay: rgba(17, 17, 17, 0.77);

    /* Shadow - 더 부드럽게 */
    --shadow-sm: 0 1px 2px 0 rgba(100, 116, 139, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(100, 116, 139, 0.08);
    --shadow-lg: 0 10px 15px -3px rgba(100, 116, 139, 0.06);
    --shadow-glass: 0 8px 32px rgba(31, 38, 135, 0.2);

    /* Spacing */
    --spacing-xxs: 2px;
    --spacing-xs: 4px;
    --spacing-sm: 8px;
    --spacing-md: 12px;
    --spacing-lg: 20px;
    --spacing-xl: 24px;
    --spacing-2xl: 32px;

    /* Transitions */
    --transition-fast: 0.15s ease;
    --transition-base: 0.3s ease;
    --transition-slow: 0.5s ease;
    --motion-duration-fast: 160ms;
    --motion-duration-base: 280ms;
    --motion-duration-slow: 420ms;
    --motion-ease: cubic-bezier(.16, 1, .3, 1);
    --motion-ease-pop: cubic-bezier(.2, .9, .22, 1.18);
    --motion-press: translateY(1px) scale(.985);

    /* font */
    --f-cmj: 'Chosunilbo_myungjo';
    --f-pre: Pretendard;
    --f-play: 'Playfair Display';

    --color-encrypted-text: #00ff00;
    --color-matrix-1: #00cc00;
    --color-matrix-2: #33ff33;
    --color-error-bg: #890000a0;
    --color-error-text: #ffffff;
    --color-error-shadow: rgba(255, 0, 0, 0.9);

    --color-neon: #16ebff;
    --color-neon2: #57ebff;
    --color-neon-r: #e50025;
    --color-neon-r2: #ff0029;
    --color-neon-bg: #0a0a0f;
    --color-neon-w: #e0e0e0;
}

/* 초기화 */
<?php if (empty($design['scrollbar_hidden']) || $design['scrollbar_hidden'] != '1'): ?>
html {overflow-y:auto}
<?php endif; ?>
/* Reset & Base */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

<?php if (!empty($design['cursor_url'])): ?>
/* 커스텀 커서 */
* {
    cursor: url('<?php echo $design['cursor_url']; ?>'), auto !important;
}
<?php endif; ?>

<?php if (!empty($design['cursor_hover_url'])): ?>
/* Hover 커스텀 커서 */
a:hover,
button:hover,
input[type="submit"]:hover,
input[type="button"]:hover,
[role="button"]:hover,
.btn:hover,
[onclick]:hover,
label:hover,
select:hover,
[style*="cursor: pointer"]:hover,
[style*="cursor:pointer"]:hover {
    cursor: url('<?php echo $design['cursor_hover_url']; ?>'), pointer !important;
}
<?php endif; ?>

<?php if (!empty($design['scrollbar_hidden']) && $design['scrollbar_hidden'] == '1'): ?>
/* 스크롤바 숨김 */
::-webkit-scrollbar {
    display: none;
}

* {
    -ms-overflow-style: none;  /* IE, Edge */
    scrollbar-width: none;  /* Firefox */
}
<?php else: ?>
/* 커스텀 스크롤바 */
::-webkit-scrollbar {
    width: <?php echo isset($design['scrollbar_width']) ? $design['scrollbar_width'] : '8'; ?>px;
    height: <?php echo isset($design['scrollbar_width']) ? $design['scrollbar_width'] : '8'; ?>px;
}

::-webkit-scrollbar-track {
    background: <?php echo isset($design['scrollbar_track_bg']) ? $design['scrollbar_track_bg'] : '#f1f5f9'; ?>;
    border-radius: <?php echo isset($design['scrollbar_track_radius']) ? $design['scrollbar_track_radius'] : '4'; ?>px;
}

::-webkit-scrollbar-thumb {
    background: <?php echo isset($design['scrollbar_thumb_bg']) ? $design['scrollbar_thumb_bg'] : '#94a3b8'; ?>;
    border-radius: <?php echo isset($design['scrollbar_thumb_radius']) ? $design['scrollbar_thumb_radius'] : '4'; ?>px;
}

::-webkit-scrollbar-thumb:hover {
    background: <?php echo isset($design['scrollbar_thumb_bg']) ? $design['scrollbar_thumb_bg'] : '#94a3b8'; ?>;
    filter: brightness(0.9);
}

/* Firefox 스크롤바 */
* {
    scrollbar-width: thin;
    scrollbar-color: <?php echo isset($design['scrollbar_thumb_bg']) ? $design['scrollbar_thumb_bg'] : '#94a3b8'; ?> <?php echo isset($design['scrollbar_track_bg']) ? $design['scrollbar_track_bg'] : '#f1f5f9'; ?>;
}
<?php endif; ?>

body {
    font-family: var(--content-font-family);
    font-size: var(--content-font-size);
    color: var(--content-font-color);
    background-color: var(--bg-color);
    background-image: var(--bg-image-url);
    background-repeat: var(--bg-repeat);
    background-position: var(--bg-position);
    background-size: var(--bg-size);
    background-attachment: fixed;
    min-height: 100svh;
    max-height: 100lvh;
}

@keyframes ra0-motion-pop {
    0% { transform: scale(.94); opacity: .82; }
    62% { transform: scale(1.06); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}

@keyframes ra0-motion-panel-in {
    from { transform: translateY(6px) scale(.985); opacity: 0; }
    to { transform: translateY(0) scale(1); opacity: 1; }
}

@keyframes ra0-motion-menu-item {
    from { transform: translateY(8px) scale(.92); opacity: 0; }
    to { transform: translateY(0) scale(1); opacity: 1; }
}

/* 메인 컨테이너 */
#container {
    display: flex;
    height: calc(95vh - var(--header-height));
    overflow: hidden;
    margin-top: var(--spacing-xl);
    box-sizing: border-box;
    width: 100%;
}

#container_wr {
    margin: 0 auto;
    zoom: 1;
    max-width: 1400px;
    width: 100%;
    /* overflow-y: auto; */
    overflow: visible;
}

#sidebar-panel {
    display: flex;
    flex-direction: column;
    width: 280px;
    min-width: 280px;
    backdrop-filter: blur(10px);
    gap: var(--spacing-md);
    margin-right: var(--spacing-lg);
    height: 100%; /* 추가 */
    overflow-y: auto; /* 추가 */
}

/* 또는 균등 분할을 원한다면 */
.sidebar-section {
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
    padding: var(--spacing-xl);
    background-color: var(--container-bg-color);
    border-radius: var(--container-border-radius);
    box-shadow: var(--shadow-sm);
    transition: var(--transition-base);
    border: 1px solid var(--container-border-color);
    box-sizing: border-box;
}

.sidebar-section .lat {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.sidebar-section .lat ul {
    flex: 1;
    overflow-y: auto;
}

.sidebar-section:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.sidebar-latest {
    overflow: hidden;
}

#board-zone3 {
    background: linear-gradient(135deg, var(--card-bg-color) 40%, var(--card-border-color) 100%);
    border: 1px solid var(--card-border-color);
}

/* B 영역: 메인 콘텐츠 */
#workspace-container {
    display: flex;
    flex-direction: column;
    flex: 1;
}

/* B-1: 슬라이더 영역 */
#slider-panel {
    flex: 1;
    position: relative;
    /* overflow: hidden; */
}

.slider-wrapper {
    width: 100%;
    height: 100%;
    position: relative;
    background-color: var(--card-bg-color);
}

.slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.5s ease-in-out;
}

.slide.active {
    opacity: 1;
}

.slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* B-2: 마퀴 공지 */
#onetag-notice {
    height: 40px;
    background: rgb(from var(--card-bg-color) r g b / 80%);
    color: var(--content-font-color);
    display: flex;
    align-items: center;
    overflow: hidden;
    margin: var(--spacing-sm) 0;
    padding: var(--spacing-sm) var(--spacing-md);
    backdrop-filter: blur(5px);
    border-radius: var(--card-border-radius);
    border: 1px solid var(--card-border-color);
}

.marquee-container {
    width: 100%;
    overflow: hidden;
    position: relative;
}

.marquee-content {
    display: inline-block;
    white-space: nowrap;
    padding-left: 40%;
    animation: marquee 15s linear infinite;
}

.marquee-content span {
    font-family: var(--f-pre);
    font-size: 12px;
}

@keyframes marquee {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-100%);
    }
}

/* B-3: 콘텐츠 그리드 */
#data-visualization-grid {
    height: 400px;
    display: flex;
    gap: var(--spacing-md);
}

.grid-item {
    flex: 1;
}

#analytics-widget {
    flex: 4;
}

#resource-manager {
    flex: 3.5;
}

#character-panel {
    flex: 5;
}

.card {
    height: 100%;
    padding: var(--spacing-lg);
    background-color: var(--container-bg-color);
    border: 1px solid var(--container-border-color);
    border-radius: var(--container-border-radius);
    box-shadow: var(--shadow-md);
    transition: var(--transition-base);
    backdrop-filter: blur(5px);
    display: flex;
    flex-direction: column;
}

.card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

#character-panel .card {
    background: linear-gradient(135deg, var(--card-bg-color) 40%, var(--card-border-color) 100%);
    border: 1px solid var(--card-border-color);
    overflow: scroll;
}

.image-section {
    height: 80px;
    overflow: hidden;
    border-radius: var(--card-border-radius) var(--card-border-radius) 0 0;
}

.content-section {
    flex: 1;
    padding: var(--spacing-lg);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ========== 시계/달력 영역 ========== */
.time-calendar-container {
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding: 0 !important;
    overflow: hidden;
    height: 100%;
}

/* 시계 섹션 */
.clock-section {
    height: 30%;
    display: flex;
    align-items: center;
    justify-content: center;
    border-bottom: 1px solid var(--card-border-color);
}

#current-time {
    font-family: var(--title-font-family);
    font-size: 32px;
    color: var(--title-font-color);
    letter-spacing: 3px;
    text-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
}

/* 달력 섹션 — 모든 하위 클래스는 .calendar-section 스코프로 한정 */
.calendar-section {
    padding: var(--spacing-sm);
    overflow: hidden;
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
}

/* JS가 만드는 #mini-calendar 래퍼도 flex 체인을 통과시켜야 .calendar-grid가 채울 수 있음 */
.calendar-section #mini-calendar {
    flex: 1;
    min-height: 0;
    display: flex;
    flex-direction: column;
}

.calendar-section .calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    grid-template-rows: auto;
    gap: 2px;
    flex: 1;
    min-height: 0;
}

.calendar-section .day-name {
    text-align: center;
    font-size: 10px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
	display: flex;
    align-items: center;
    justify-content: center;
}

.calendar-section .calendar-day {
    position: relative;
    min-height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    color: var(--content-font-color);
    border-radius: 4px;
    transition: var(--transition-fast);
    cursor: pointer;
}

.calendar-section .calendar-day:not(.empty):not(.today):hover {
    background-color: rgb(from var(--primary-color) r g b / 10%);
    transform: scale(1.05);
}

.calendar-section .calendar-day.today,
.calendar-section .calendar-day.today:hover {
    background: var(--accent-color);
    color: var(--white);
    font-weight: 700;
    box-shadow: 0 2px 4px rgb(from var(--accent-color) r g b / 30%);
}

.calendar-section .calendar-day.empty {
    opacity: 0;
    pointer-events: none;
}

/* ========== 소개글 영역 ========== */
.intro-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    width: 100%;
    padding: var(--spacing-xl);
    box-sizing: border-box;
}

.card.intro-container {
    flex: 1;
    min-height: auto;
    display: block;
}

.intro-container h3 {
    text-align: center;
    color: var(--content-font-color);
    font-family: var(--title-font-family);
    font-size: var(--sub-title-font-size);
    margin: 0 0 var(--spacing-md) 0;
    padding-bottom: var(--spacing-xs);
}

.intro-content {
    font-family: var(--content-font-family);
    font-size: var(--content-font-size);
    color: var(--content-font-color);
    line-height: 1.7;
    overflow-y: auto;
    text-align: center;
    width: 100%;
    max-width: 500px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

/* ===== 인트로 페이지 스타일 ===== */
.intro-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--bg-primary);
    z-index: -1;
    border-radius: var(--container-border-radius);
}

.intro-content p {
    margin: 0 0 var(--spacing-sm) 0;
}

.intro-placeholder {
    color: var(--text-muted);
    font-style: italic;
    text-align: center;
    padding: var(--spacing-xl) 0;
}

/* 캐릭터 패널 스타일 */
.member_menu {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    margin-bottom: var(--spacing-md);
}

.member_menu h3 {
    color: var(--content-font-color);
    font-family: var(--f-play);
    font-size: var(--sub-title-font-size);
}

.character_name {
    margin-bottom: var(--spacing-sm);
}

.no_character {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.no_character p {
    font-family: var(--content-font-family);
    font-size: calc(var(--content-font-size) * 0.9);
    color: rgb(from var(--content-font-color) r g b / 60%);
    font-weight: 200;
}

.character_stats {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
    font-size: 12px;
}

.btn-admin {
    background: var(--error-color);
    color: var(--gray-900);
}

.btn-admin:hover {
    background: var(--btn-accent-bg);
    color: var(--btn-accent-text);
}

.btn_01 {
    background: var(--primary-color);
    color: var(--content-font-color);
    padding: var(--spacing-sm) var(--spacing-md);
    border: none;
    border-radius: var(--btn-primary-radius);
    text-decoration: none;
    display: inline-block;
    font-size: 12px;
    transition: var(--transition-fast);
}

.btn_01:hover {
    filter: brightness(0.7);
}
/* 로그인 폼 스타일 */
.login_form h3 {
    color: var(--primary-color);
    margin-bottom: var(--spacing-md);
}

.login_row {
    display: flex;
    gap: var(--spacing-sm);
}

.login_input {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
    flex: 1;
    min-width: 0;
}

.login_row .btn_login {
    align-self: stretch;
}

.login_bottom_row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: var(--spacing-sm);
}

.login_link {
    color: rgb(from var(--content-font-color) r g b / 50%);
    text-decoration: none;
    font-size: 11px;
    transition: all 0.3s ease;
}

.login_link:hover {
    color: var(--primary-color);
}

.login_input input {
    padding: var(--spacing-sm);
    border: 1px solid var(--form-border-color);
    border-radius: var(--btn-primary-radius);
    font-size: 12px;
    transition: all 0.3s ease;
    background: var(--form-bg-color);
    color: var(--form-text-color);
}

/* 🌟 Input Hover & Focus 효과 */
.login_input input:hover {
    border-color: var(--primary-color);
    box-shadow: 0 2px 8px rgb(from var(--primary-color) r g b / 20%);
    transform: translateY(-1px);
}

.login_input input:focus {
    outline: none;
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgb(from var(--accent-color) r g b / 15%);
    transform: translateY(-2px);
}

.btn_login {
    background: var(--primary-color);
    color: var(--content-font-color);
    padding: var(--spacing-sm) var(--spacing-md);
    border: none;
    border-radius: var(--btn-primary-radius);
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    white-space: nowrap;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 1px;
}

.btn_login:hover {
    background: linear-gradient(155deg, var(--accent-color) 0%, rgb(from var(--accent-color) r g b / 60%) 20%, transparent 70%);
    filter: brightness(0.8);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgb(from var(--primary-color) r g b / 30%);
}

.btn_login:active {
    transform: translateY(0);
    box-shadow: 0 2px 6px rgb(from var(--primary-color) r g b / 20%);
}

.login_links {
    display: flex;
    font-size: 11px;
}

.login_links a {
    color: rgb(from var(--content-font-color) r g b / 50%);
    text-decoration: none;
    padding: var(--spacing-xs, 4px) var(--spacing-sm);
    border-radius: var(--btn-primary-radius);
    transition: all 0.3s ease;
}

/* 🔗 링크 hover 효과 */
.login_links a:hover {
    color: var(--primary-color);
    transform: translateY(-1px);
    text-shadow: 0 1px 2px rgb(from var(--primary-color) r g b / 20%);
}

.login_options {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-size: 11px;
    color: var(--text-muted);
}

.login_options label {
    cursor: pointer;
    user-select: none;
}

/* 💫 전체 폼 hover 효과 (선택사항) */
.login_form {
    transition: all 0.3s ease;
    width: 100%;
    max-width: 300px;
    padding: var(--spacing-lg);
    background: var(--card-bg-color);
    border-radius: var(--card-border-radius);
    box-shadow: 0 4px 12px rgb(from var(--black) r g b / 15%);
}

.login_form:hover {
    transform: scale(1.01);
    box-shadow: 0 6px 16px rgb(from var(--black) r g b / 20%);
}

/* grid-item 내부의 login_form은 배경과 그림자 제외 */
.grid-item .login_form {
    background: none;
    box-shadow: none;
    padding: 0;
}

.grid-item .login_form:hover {
    transform: none;
    box-shadow: none;
}

/* 회원 메시지 스타일 (intro.php) */
.member-message {
    width: 100%;
    max-width: 400px;
    padding: var(--spacing-xl);
    background: var(--card-bg-color);
    border-radius: var(--card-border-radius);
    box-shadow: 0 4px 12px rgb(from var(--black) r g b / 15%);
    text-align: center;
}

.member-message p {
    margin: var(--spacing-md) 0;
    color: var(--content-font-color);
    font-size: var(--content-font-size);
    line-height: 1.6;
}

.member-message p:first-child {
    font-family: var(--title-font-family);
    font-size: var(--sub-title-font-size);
    color: var(--primary-color);
    font-weight: 600;
}

.btn_main {
    display: inline-block;
    margin-top: var(--spacing-lg);
    padding: var(--spacing-md) var(--spacing-xl);
    background: var(--primary-color);
    color: var(--white);
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgb(from var(--primary-color) r g b / 30%);
}

.btn_main:hover {
    background: var(--accent-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgb(from var(--accent-color) r g b / 40%);
}

.btn_main:active {
    transform: translateY(0);
    box-shadow: 0 2px 6px rgb(from var(--primary-color) r g b / 20%);
}

/* 🎪 입력 필드 placeholder 효과 */
.login_input input::placeholder {
    color: rgb(from var(--form-text-color) r g b / 40%);
    transition: all 0.3s ease;
}

.login_input input:focus::placeholder {
    opacity: 0.6;
    transform: translateX(4px);
}

html, h1, h2, h3, h4, h5, h6, form, fieldset, img {margin:0;padding:0;border:0}
h1, h2, h3, h4, h5, h6 {
    font-size:1em;
    font-family: var(--content-font-family);
    color: var(--content-font-color);
    font-weight: 600;
}
article, aside, details, figcaption, figure, footer, header, hgroup, menu, nav, section {display:block}

ul, dl,dt,dd {margin:0;padding:0;list-style:none}
legend {position:absolute;margin:0;padding:0;font-size:0;line-height:0;text-indent:-9999em;overflow:hidden}
label, input, button, select, img {vertical-align:middle;font-size:1em}
img {
    max-width: 100%;
}
input, button {
    margin:0;
    padding:0;
    font-family: var(--content-font-family);
    font-size:1em;
}
input[type="submit"] {cursor:pointer}
button {cursor:pointer}

textarea, select {
    font-family: var(--content-font-family);
    font-size:1em;
    max-width: 100%;
    background: var(--form-bg-color);
    color: var(--form-text-color);
    border-radius: var(--form-border-radius);
    padding: var(--spacing-xs) var(--spacing-sm);
}

select {margin:0}
p {margin:0;padding:0;word-break:break-all}
hr {display:none}
pre {overflow-x:scroll;font-size:1.1em}
a {
    color: var(--primary-color);
    text-decoration: none;
    transition: var(--transition-fast);
}
a:hover {
    color: var(--accent-color);
}

*, :after, :before {
  -webkit-box-sizing:border-box;
  -moz-box-sizing:border-box;
  box-sizing:border-box;
}

input[type=text],input[type=password], textarea {
    -webkit-transition: var(--transition-base);
    -moz-transition: var(--transition-base);
    -ms-transition: var(--transition-base);
    -o-transition: var(--transition-base);
    outline:none;
}

input[type=text]:focus,input[type=password]:focus, textarea:focus,select:focus {
    border: 1px solid var(--primary-color) !important;
    box-shadow: 0 0 0 3px rgb(from var(--primary-color) r g b / 20%),
                0 0 15px rgb(from var(--primary-color) r g b / 30%);
    outline: none;
}

.placeholdersjs {color: var(--text-muted) !important}

/* 레이아웃 크기 지정 */
#tnb .inner,
#hd_menu .hd_menu_wrap,
#container_wr,
#ft_wr {width:100%;}

/* 상단 레이아웃 */
#hd_h1 {position:absolute;font-size:0;line-height:0;overflow:hidden}

#tnb {
    border-bottom:1px solid var(--border-medium);
    margin:0 auto;
}
#tnb:after {display:block;visibility:hidden;clear:both;content:""}
#tnb .inner {margin:0 auto}

/* 헤더 스타일 현대화 */
#hd {
    font-family: var(--header-font-family);
    font-size: var(--header-font-size);
    color: var(--header-font-color);
    background: var(--header-bg-color);
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1020;
    pointer-events: none; /* 뒤쪽 이벤트 차단 방지 */
}

#hd_wrapper {
    position: relative;
    margin: 0 auto;
    height: var(--header-height);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--spacing-sm) var(--spacing-lg);
    pointer-events: none; /* 뒤쪽 이벤트 차단 방지 */
}

/* 실제로 클릭 가능한 요소들만 이벤트 활성화 */
#logo,
#main_menu_list .main_me,
.main_me_link,
.hd_login {
    pointer-events: auto;
}


#footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    width: 100%;
    color: var(--header-font-color);
    font-size: 11px;
    font-family: var(--content-font-family);
    padding: 10px 20px 5px;
    pointer-events: none;
}

.footer-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 20px;
}

.footer-left {
    text-align: left;
}

.footer-left p {
    margin: 2px 0;
    color: rgb(from var(--content-font-color) r g b / 50%);
    font-weight: 200;
    line-height: 1.4;
}

.footer-links {
    display: flex;
    gap: 10px;
    align-items: flex-end;
    pointer-events: auto;
    position: relative;
    transform: translateX(-50%);
}

.footer-links a {
    color: rgb(from var(--content-font-color) r g b / 70%);
    text-decoration: none;
    font-weight: 300;
    transition: color 0.2s;
}

.footer-links a:hover {
    color: var(--primary-color);
}

.footer-links a:not(:last-child)::after {
    content: '|';
    margin-left: 10px;
    color: rgb(from var(--content-font-color) r g b / 30%);
}

.footer-right {
    text-align: right;
}

.footer-right p {
    color: rgb(from var(--content-font-color) r g b / 50%);
    font-weight: 200;
}


/* 메인메뉴 현대화 */
.connect {
    display: flex;
    align-items: center;
    color: rgb(from var(--header-font-color) r g b / 70%);
    gap: 3px;
    font-weight: 300;
    font-family: Pretendard;
    font-size: 11px;
    margin-right: 6px;
}

.connect .connect-num {
    background: var(--btn-accent-bg);
    text-decoration: none;
    padding: 0px 4px;
    border-radius: 4px;
    color: var(--btn-accent-text);
    font-weight: 600;
}

#hd_menu {
    flex: 1;
    display: flex;
    justify-content: var(--hd-menu-justify, flex-start);
    align-items: var(--hd-menu-align, flex-start);
    position: relative;
    height: calc(var(--header-height) * 0.4);
}

#main_menu_list {
    display: flex;
    align-items: center;
    margin: 0;
    padding: 0;
    list-style: none;
}

.main_me {
    position: relative;
    margin: 0 var(--spacing-xs);
}

.main_me_link {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs) ;
    padding: var(--spacing-md) var(--spacing-lg);
    color: var(--header-font-color);
    text-decoration: none;
    font-weight: 500;
    border-radius: var(--form-border-radius);
    transition: var(--transition-fast);
    white-space: nowrap;
    position: relative;
}

.main_me_link i {
    font-size: 1em;
    opacity: 0.8;
    transition: var(--transition-fast);
}

.main_me_link:hover {
    color: var(--accent-color);
    /* background-color: var(--header-bg-color); */
    transform: translateY(-1px);
}

.main_me_link:hover i {
    opacity: 1;
    transform: scale(1.1);
}

.main_me_link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: var(--accent-color);
    transition: var(--transition-fast);
    transform: translateX(-50%);
}

.main_me_link:hover::after,
.main_me.active .main_me_link::after {
    width: 80%;
}

/* 하위메뉴 표시 화살표 */
/* .main_me.has_submenu .main_me_link::before {
    content: '';
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    border-top: 4px solid currentColor;
    transition: var(--transition-base);
}

.main_me.has_submenu:hover .main_me_link::before {
    transform: translateY(-50%) rotate(180deg);
} */

/* 서브메뉴 슬라이드 애니메이션 */
.sub_me_list {
    position: absolute;
    left: 35%;
    min-width: 150px;
    /* background: rgb(from var(--header-bg-color) r g b / 50%); */
    border-radius: var(--card-border-radius);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-20px) scaleY(0);
    transform-origin: top center;
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    z-index: 1000;
    overflow: hidden;
    max-height: 0;
}

.main_me:hover .sub_me_list {
    opacity: 1;
    visibility: visible;
    transform: translateY(5px) scaleY(1);
    max-height: 500px;
}

.sub_me_box {
    margin: 0;
    list-style: none;
    transform: translateY(-10px);
    transition: transform 0.3s ease 0.1s;
    display: flex;
    flex-direction: column;
    gap: 12px;
    backdrop-filter: blur(2px);
    width: fit-content;
}

.main_me:hover .sub_me_box {
    transform: translateY(0);
}

.sub_me {
    margin: 0;
    opacity: 0;
    transform: translateX(-10px);
    animation: slideInSubmenu 0.3s ease forwards;
}

.main_me:hover .sub_me:nth-child(1) { animation-delay: 0.1s; }
.main_me:hover .sub_me:nth-child(2) { animation-delay: 0.15s; }
.main_me:hover .sub_me:nth-child(3) { animation-delay: 0.2s; }
.main_me:hover .sub_me:nth-child(4) { animation-delay: 0.25s; }
.main_me:hover .sub_me:nth-child(5) { animation-delay: 0.3s; }

@keyframes slideInSubmenu {
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.sub_me_link {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    color: rgb(from var(--header-font-color) r g b / 60%);
    /* border-left: 3px solid var(--accent-color); */
    font-family: var(--header-font-family);
    font-size: var(--sub-menu-font-size);
    text-decoration: none;
    transition: var(--transition-fast);
    position: relative;
    overflow: hidden;
}

.sub_me_link i {
    font-size: var(--sub-menu-icon-size);
    opacity: 0.7;
    transition: var(--transition-fast);
}

.sub_me_link::before {
    content: '';
    position: absolute;
    left: 12px;
    bottom: 0;
    width: 0;
    height: 40%;
    background: linear-gradient(155deg, var(--accent-color) 0%, rgb(from var(--accent-color) r g b / 60%) 10%, transparent 50%);
    transition: width 0.3s ease;
    z-index: -1;
}

.sub_me_link:hover {
    color: var(--header-font-color);
    transform: translateX(3px);
}

.sub_me_link:hover i {
    opacity: 1;
    transform: scale(1.1);
}

.sub_me_link:hover::before {
    width: 100%;
}


/* 로그인 메뉴 현대화 - 항상 우측 상단 고정 */
.hd_login {
    display: flex;
    margin: 0;
    padding: 0;
    list-style: none;
    position: fixed;
    top: var(--spacing-sm);
    right: var(--spacing-md);
    z-index: 1050;
    flex-shrink: 0;
    align-items: center;
    height: auto;
}

.hd_login li {
    margin: 0 var(--spacing-xs);
    border: none;
    padding: 0;
    float: none;
    line-height: normal;
    position: relative;
}

.hd_login li:first-child {
    border-left: none;
}

.hd_login a {
    display: flex;
    align-items: center;
    color: rgb(from var(--header-font-color) r g b / 80%);
    text-decoration: none;
    font-weight: 300;
    font-family: Pretendard;
    font-size: 11px;
}

.hd_login a:hover {
    color: rgb(from var(--header-font-color) r g b / 100%);
    filter: brightness(1.5);
}

/* 관리자 링크 스타일 */
.hd_admin a {
    color: var(--accent-color) !important;
    font-weight: 600;
}

/* 헤더 미사용/상단 위치 시 로그인 영역 */
#hd_minimal {
    position: fixed;
    z-index: 1000;
    <?php if ($design['header_position'] == 'left'): ?>
    bottom: var(--spacing-sm);
    left: var(--spacing-md);
    <?php elseif ($design['header_position'] == 'right'): ?>
    bottom: var(--spacing-sm);
    right: var(--spacing-md);
    <?php else: ?>
    top: var(--spacing-sm);
    right: var(--spacing-md);
    <?php endif; ?>
}

#hd_minimal .hd_login {
    height: auto;
}

#hd_minimal .hd_login a {
    color: var(--text-color);
}

#hd_minimal .hd_login a:hover {
    color: var(--primary-color);
}

/* 검색 영역 개선 */
.hd_sch_wr {
    float: left;
    padding: 30px 0;
    width: 445px;
    margin-left: 65px;
}

#hd_sch h3 {
    position: absolute;
    font-size: 0;
    line-height: 0;
    overflow: hidden;
}

#hd_sch {
    border-radius: var(--form-border-radius);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

#hd_sch #sch_stx {
    float: left;
    width: 385px;
    height: 45px;
    padding: 0 15px;
    border-radius: var(--form-border-radius) 0 0 var(--form-border-radius);
    background: var(--form-bg-color);
    border: 1px solid var(--form-border-color);
    border-right: 0;
    font-size: 1em;
    color: var(--form-text-color);
    font-family: var(--content-font-family);
    transition: var(--transition-base);
}

#hd_sch #sch_stx:focus {
    background: var(--white);
    border-color: var(--accent-color);
}

#hd_sch #sch_submit {
    float: left;
    width: 60px;
    height: 45px;
    border: 1px solid var(--btn-primary-bg);
    background: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    border-radius: 0 var(--form-border-radius) var(--form-border-radius) 0;
    cursor: pointer;
    font-size: 16px;
    transition: var(--transition-fast);
}

#hd_sch #sch_submit:hover {
    filter: brightness(0.7);
    transform: translateY(-1px);
}

.bo_nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: nowrap;
    max-width: 100%;
}

#bo_sch {
    max-width: 100%;
}

#bo_sch legend {
    position: absolute;
    overflow: hidden;
    width: 1px;
    height: 1px;
    clip: rect(0 0 0 0);
}

#bo_sch form {
    display: flex;
    gap: 10px;
    align-items: center;
    max-width: 100%;
}

#bo_sch .frm_input {
    height: var(--spacing-2xl);
    padding: var(--spacing-xs) var(--spacing-sm);
    color: var(--text-muted);
}

input#stx {
    flex: 1;
    max-width: 80%;
}

#bo_sch .frm_input:focus {
    border-color: var(--accent-color);
    outline: none;
}

#bo_sch .frm_input::placeholder {
    color: var(--text-muted);
}

/* 중간 레이아웃 */
#wrapper {
    width: 100%;
    min-height: 100svh;
    max-height: 100lvh;
    overflow: hidden; /* iframe 컨테이너이므로 자체 스크롤바 숨김 */
}

/* 헤더 다음에 오는 #wrapper - 헤더 위치에 따라 조정됨 (헤더 위치별 스타일에서 정의) */

#wrapper iframe#main {
    width: 100%;
    min-height: 100svh;
    max-height: 100lvh;
    background-color: transparent;
    overflow: auto;
}


/* 게시판 글쓰기 (Board Write) */
#bo_w {
}

.write_div {
    margin-bottom: 15px;
}

.bo_w_info {
    display: flex;
    gap: 10px;
}

.bo_w_info input {
    flex: 1;
}

.bo_w_select select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid var(--form-border-color);
    border-radius: var(--form-border-radius);
    background: var(--form-bg-color);
    color: var(--text-primary);
    font-size: 14px;
}

.bo_w_tit input {
    width: 100%;
}

#autosave_wrapper {
    display: flex;
    gap: 10px;
    align-items: center;
}

#autosave_wrapper input {
    flex: 1;
}

#autosave_wrapper .btn_frmline {
    padding: var(--spacing-xxs) var(--spacing-sm);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-wrap: nowrap;
    text-decoration: none;
    border: 0;
    height: 35px;
}

#btn_autosave {
    font-size: 1em;
    background: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    border-radius: var(--btn-primary-radius);
    width: 150px;
}

#btn_autosave_save {
    background: var(--btn-secondary-bg);
    color: var(--btn-secondary-text);
    border-radius: var(--btn-secondary-radius);
    width: 80px;
}

#btn_autosave_save:hover {
    filter: brightness(0.7);
    border-color: var(--primary-dark);
}

#autosave_pop {
    display: none;
    position: absolute;
    top: 16%;
    right: 0;
    padding: 15px 15px 5px;
    background: rgb(from var(--bg-color) r g b / 85%);
    border: 1px solid var(--card-border-color);
    border-radius: var(--card-border-radius);
    box-shadow: var(--box-shadow);
    color: var(--content-font-color);
    min-width: 230px;
    z-index: 100;
    backdrop-filter: blur(5px);
}

#autosave_pop ul {
    padding: 10px 5px;
}

#autosave_pop li {
    display: flex;
    gap: 5px;
    align-items: center;
    justify-content: space-between;
}

#autosave_pop .autosave_load {
    color: var(--content-font-color);
    text-decoration: none;
    font-size: 12px;
    margin-right: 5px;
}

#autosave_pop span {
    color: var(--text-muted);
    font-size: 0.8em;
}

#autosave_pop button {
    border: 0;
    background: transparent;
    color: var(--content-font-color);
    font-size: 0.9em;
    line-height: 1;
}

#autosave_pop .autosave_close {
    position: absolute;
    top: 10px;
    right: 10px;
    color: var(--content-font-color);
    filter: brightness(1.2);
}

.wr_content {
    margin: 15px 0;
}

.wr_content textarea {
    width: 100%;
    min-height: 300px;
    padding: 12px;
    border: 1px solid var(--form-border-color);
    border-radius: var(--form-border-radius);
    background: var(--form-bg);
    color: var(--text-primary);
    font-size: 14px;
    line-height: 1.6;
    resize: vertical;
}

#char_count_desc {
    padding: 10px;
    background: var(--light-bg-color);
    border-radius: var(--form-border-radius);
    font-size: 13px;
    color: var(--text-muted);
    margin-bottom: 10px;
}

#char_count_wrap {
    text-align: right;
    padding: 5px;
    font-size: 13px;
    color: var(--text-muted);
}

/* 파일 업로드 영역 */
.bo_w_flie {
    background: var(--card-bg-color);
    border-radius: var(--card-border-radius);
    margin: 15px 0;
}

.file_wr {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.lb_icon {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 15px;
    background: var(--btn-secondary-bg);
    color: var(--btn-secondary-text);
    border: 1px solid var(--border-color);
    border-radius: var(--form-border-radius);
    cursor: pointer;
    width: 170px;
}

.lb_icon:hover {
    background: var(--btn-secondary-hover);
}

.file_info {
    font-size: 13px;
    color: var(--text-muted);
}

/* 이미지 미리보기 */
.image-preview-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 15px;
}

.preview-item {
    width: 100px;
    width: 100px;
    text-align: center;
}

.preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border: 1px solid var(--card-border-color);
    border-radius: var(--card-border-radius);
}

.preview-name {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    color: var(--text-muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* 기존 첨부 파일 */
.existing-files {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--border-color);
}

.existing-files h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: var(--text-primary);
}

.existing-file-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
    padding: 10px;
    background: var(--card-bg);
    border-radius: var(--form-border-radius);
}

.existing-file-item img {
    border: 1px solid var(--border-color);
    border-radius: var(--form-border-radius);
}

.existing-file-item label {
    margin-left: 10px;
    color: var(--danger-color);
    cursor: pointer;
    font-size: 13px;
}

/* 버튼 영역 */
.btn_confirm {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 20px;
}

.btn_cancel, .btn_submit {
    padding: 10px 30px;
    border: none;
    border-radius: var(--form-border-radius);
    font-size: 14px;
    cursor: pointer;
    transition: var(--transition-fast);
}

.btn_cancel {
    background: var(--btn-secondary-bg);
    color: var(--btn-secondary-text);
    border: 1px solid var(--border-color);
}

.btn_cancel:hover {
    background: var(--btn-secondary-hover);
}

.btn_submit {
    background: var(--btn-primary-bg);
    color: var(--btn-primary-text);
}

.btn_submit:hover {
    filter: brightness(0.7);
    transform: translateY(-1px);
}

.btn_submit:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

#top_btn {
    position: fixed;
    bottom: var(--spacing-lg);
    right: var(--spacing-lg);
    width: 50px;
    height: 50px;
    line-height: 46px;
    border: 2px solid var(--gray-600);
    color: var(--gray-600);
    text-align: center;
    font-size: 15px;
    z-index: 90;
    background: var(--bg-glass);
    border-radius: var(--card-border-radius);
    backdrop-filter: blur(10px);
    transition: var(--transition-base);
}

#top_btn:hover {
    border-color: var(--accent-color);
    background: var(--accent-color);
    color: var(--white);
    transform: translateY(-2px);
}

/* 헤더 위치에 따른 스타일 */
<?php if ($design['use_header'] != '1'): ?>
/* 헤더 미사용 시 - header-height 영향 없음 */
#wrapper {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: auto;
}

#container_wr {
    padding-top: 40px;
}

<?php elseif ($design['header_position'] == 'left'): ?>
/* 좌측 헤더 */
#hd {
    width: var(--header-height);
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    overflow-y: auto;
    overflow-x: hidden;
    border-bottom: none;
    z-index: 1020;
}

#hd_wrapper {
    flex-direction: column;
    padding: var(--spacing-lg);
    height: 100%;
    justify-content: flex-start;
}

#logo {
    max-height: calc(var(--header-height) / 2);
    height: auto;
    width: var(--header-height);
    padding: var(--spacing-md);
}

#logo a {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform var(--transition-base);
}

#logo a:hover {
    transform: scale(1.05);
}

#logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: filter var(--transition-base);
}

#hd_menu {
    flex: 1;
    justify-content: flex-start;
}

#main_menu_list {
    flex-direction: column;
    width: 100%;
    align-items: stretch;
}

.main_me {
    width: calc(var(--header-height) / 2);
    margin: 0 auto;
}

.main_me_link {
    width: 100%;
    padding: var(--spacing-md);
    flex-direction: column;
    justify-content: flex-start;
}

.sub_me_list {
    position: absolute;
    top: 90%;
    left: 35%;
    min-width: 150px;
    /* background: rgb(from var(--header-bg-color) r g b / 50%); */
    border-radius: var(--card-border-radius);
    /* box-shadow: var(--shadow-lg); */
    opacity: 0;
    visibility: hidden;
    transform: translateY(-20px) scaleY(0);
    transform-origin: top center;
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    z-index: 1000;
    backdrop-filter: blur(2px);
    overflow: hidden;
    max-height: 0;
}

.main_me.has_submenu:hover .sub_me_list,
.main_me.active .sub_me_list {
    display: block;
}

/* .hd_login은 항상 우측 상단 고정 (기본 스타일 사용) */

#wrapper {
    width: 100%;
    min-height: 100svh;
    max-height: 100lvh;
    /* padding-top: 0;
    padding-left: var(--header-height); */
}

#container_wr {
    padding-top: 0;
}

/* 좌측 헤더일 때 wrapper - 헤더 오른쪽에서 시작 */
#hd ~ #wrapper {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    padding-left: var(--header-height);
    padding-top: 40px;
    overflow: auto;
}

<?php elseif ($design['header_position'] == 'right'): ?>
/* 우측 헤더 */
#hd {
    width: var(--header-height);
    height: 100vh;
    position: fixed;
    right: 0;
    top: 0;
    order: 2;
    overflow-y: auto;
    overflow-x: hidden;
    border-bottom: none;
    z-index: 1020;
}

#hd_wrapper {
    flex-direction: column;
    padding: var(--spacing-lg);
    height: 100%;
    justify-content: flex-start;
}

#logo {
    max-height: calc(var(--header-height) / 2);
    height: auto;
    width: var(--header-height);
    padding: var(--spacing-md);
}

#logo a {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform var(--transition-base);
}

#logo a:hover {
    transform: scale(1.05);
}

#logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: filter var(--transition-base);
}

#hd_menu {
    flex: 1;
    justify-content: flex-start;
}

#main_menu_list {
    flex-direction: column;
    width: 100%;
    align-items: stretch;
}

.main_me {
    width: 100%;
    margin: 0 0 var(--spacing-xs) 0;
}

.main_me_link {
    width: 100%;
    padding: var(--spacing-md);
    flex-direction: column;
    justify-content: flex-start;
}

.sub_me_list {
    position: static;
    opacity: 1;
    visibility: visible;
    transform: none;
    box-shadow: none;
    border: none;
    background: var(--gray-50);
    margin: var(--spacing-xs) 0 0 var(--spacing-lg);
    border-radius: var(--form-border-radius);
    display: none;
}

.main_me.has_submenu:hover .sub_me_list,
.main_me.active .sub_me_list {
    display: block;
}

/* .hd_login은 항상 우측 상단 고정 (기본 스타일 사용) */

#wrapper {
    width: 100%;
    min-height: 100svh;
    max-height: 100lvh;
    order: 1;
}

#container_wr {
    padding-top: 0;
}

/* 우측 헤더일 때 wrapper - 헤더 왼쪽에서 시작 */
#hd ~ #wrapper {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    padding-right: var(--header-height);
    padding-top: 40px;
    overflow: auto;
}

<?php else: ?>
/* 상단 헤더 (기본) */
/* 상단 헤더일 때 wrapper - 헤더 아래에서 시작 */
#hd ~ #wrapper {
    position: fixed;
    padding-top: var(--header-height);
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: auto;
}

<?php endif; ?>

#logo {
    height: var(--header-height);
    padding: var(--spacing-md);
    position: relative;
    z-index: 999;
}

#logo a {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: flex-start;
    transition: transform var(--transition-base);
    justify-content: flex-start;
}

#logo a:hover {
    transform: scale(1.02);
    filter: brightness(1.5);
}

#logo img {
    height: 100%;
    object-fit: contain;
    transition: filter var(--transition-base);
}

/* 버튼 스타일 현대화 */
a.btn, .btn {
    line-height: 40px;
    height: 40px;
    padding: 0 var(--spacing-lg);
    text-align: center;
    font-weight: 600;
    border: 0;
    font-size: 0.95em;
    font-family: var(--content-font-family);
    border-radius: var(--btn-primary-radius);
    transition: var(--transition-base);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-decoration: none;
}

a.btn01, button.btn01 {
    display: inline-flex;
    align-items: center;
    padding: var(--spacing-sm) var(--spacing-md);
    border: 1px solid var(--border-medium);
    background: var(--gray-50);
    color: var(--text-primary);
    text-decoration: none;
    vertical-align: middle;
    border-radius: var(--form-border-radius);
    transition: var(--transition-fast);
    font-family: var(--content-font-family);
}

a.btn01:focus, a.btn01:hover, button.btn01:hover {
    text-decoration: none;
    background: var(--gray-100);
    border-color: var(--border-medium);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

a.btn02, button.btn02 {
    display: inline-flex;
    align-items: center;
    padding: var(--spacing-sm) var(--spacing-md);
    border: 1px solid var(--btn-secondary-bg);
    background: var(--btn-secondary-bg);
    color: var(--btn-secondary-text);
    text-decoration: none;
    vertical-align: middle;
    border-radius: var(--btn-secondary-radius);
    transition: var(--transition-fast);
    font-family: var(--content-font-family);
}

a.btn02:focus, .btn02:hover, button.btn02:hover {
    text-decoration: none;
    background: var(--gray-600);
    color: var(--white);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.btn_submit {
    border: 0;
    background: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    cursor: pointer;
    border-radius: var(--btn-primary-radius);
    font-family: var(--content-font-family);
    transition: var(--transition-fast);
    font-weight: 600;
}

.btn_submit:hover {
    filter: brightness(0.7);
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.btn_close {
    border: 1px solid var(--border-light);
    cursor: pointer;
    border-radius: var(--btn-secondary-radius);
    background: var(--btn-secondary-bg);
    color: var(--btn-secondary-text);
    font-family: var(--content-font-family);
    transition: var(--transition-fast);
}

.btn_close:hover {
    background: var(--gray-100);
    transform: translateY(-1px);
}

a.btn_close {
    text-align: center;
    line-height: 50px;
}

a.btn_cancel, button.btn_cancel {
    display: inline-flex;
    align-items: center;
    background: var(--gray-500);
    color: var(--white);
    text-decoration: none;
    vertical-align: middle;
    border-radius: var(--form-border-radius);
    transition: var(--transition-fast);
    font-family: var(--content-font-family);
    border: 1px solid var(--gray-500);
}

.btn_cancel:hover {
    background: var(--gray-600);
    border-color: var(--gray-600);
    transform: translateY(-1px);
}

a.btn_frmline, button.btn_frmline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 128px;
    padding: 0 var(--spacing-sm);
    height: 40px;
    border: 0;
    background: var(--gray-700);
    border-radius: var(--form-border-radius);
    color: var(--white);
    text-decoration: none;
    vertical-align: top;
    font-family: var(--content-font-family);
    transition: var(--transition-fast);
}

a.btn_frmline:hover, button.btn_frmline:hover {
    background: var(--gray-800);
    transform: translateY(-1px);
}

button.btn_frmline {font-size: 1em}

/* 게시판용 버튼 */

.ra0_ui_btn {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    margin: 0;
    box-sizing: border-box;
    font: inherit;
    height: auto;
    line-height: 1.2;
    overflow: visible;
    text-align: center;
    text-transform: none;
    text-decoration: none;
    display: inline-block;
    font-size: 12px;
    transition: all 0.2s;
    font-family: 'Pretendard';
    padding: calc(var(--spacing-sm)*0.8) var(--spacing-sm);
    background: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    border-radius: var(--btn-primary-radius);
    border: none;
    cursor: pointer;
    align-content: center;
    justify-content: center;
    min-width: 28px;
}

.btn_s {
    background: var(--btn-secondary-bg);
    color: var(--btn-secondary-text);
    border-radius: var(--btn-secondary-radius);
}

.btn_a {
    background: var(--btn-accent-bg);
    color: var(--btn-accent-text);
    border-radius: var(--btn-accent-radius);
}

.ra0_ui_btn:hover {
    background: var(--btn-accent-bg);
    color: var(--btn-accent-text);
}

a.btn_b01, .btn_b01 {
    display: inline-flex;
    align-items: center;
    color: var(--text-muted);
    text-decoration: none;
    vertical-align: middle;
    border: 0;
    background: transparent;
    transition: var(--transition-fast);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--btn-primary-radius);
}

.btn_b01:hover, .btn_b01:hover {
    color: var(--text-primary);
    background: var(--gray-50);
}

a.btn_b02, .btn_b02 {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    vertical-align: middle;
    background: var(--btn-primary-bg);
    padding: var(--spacing-sm);
    color: var(--btn-primary-text);
    text-decoration: none;
    border: 0;
    border-radius: var(--btn-primary-radius);
    transition: var(--transition-fast);
    font-weight: 500;
}

a.btn_b02:hover, .btn_b02:hover {
    background: var(--accent-color);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

a.btn_b03, .btn_b03 {
    display: inline-flex;
    align-items: center;
    background: var(--card-bg-color);
    border: 1px solid var(--border-light);
    color: var(--text-secondary);
    text-decoration: none;
    vertical-align: middle;
    border-radius: var(--form-border-radius);
    transition: var(--transition-fast);
    padding: var(--spacing-sm) var(--spacing-md);
}

a.btn_b03:hover, .btn_b03:hover {
    background: var(--gray-50);
    border-color: var(--border-medium);
    color: var(--text-primary);
}

a.btn_b04, .btn_b04 {
    display: inline-flex;
    align-items: center;
    background: var(--card-bg-color);
    border: 1px solid var(--border-medium);
    color: var(--text-secondary);
    text-decoration: none;
    vertical-align: middle;
    border-radius: var(--form-border-radius);
    transition: var(--transition-fast);
    padding: var(--spacing-sm) var(--spacing-md);
}

a.btn_b04:hover, .btn_b04:hover {
    color: var(--text-primary);
    background: var(--gray-50);
    border-color: var(--accent-color);
}

a.btn_admin, .btn_admin {
    display: inline-flex;
    align-items: center;
    color: var(--error-color);
    text-decoration: none;
    vertical-align: middle;
    transition: var(--transition-fast);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--form-border-radius);
}

.btn_admin:hover, a.btn_admin:hover {
    color: var(--error-color);
    background: rgba(var(--error-color-rgb, 255, 107, 107), 0.1);
}

/* 기본테이블 현대화 */
.tbl_wrap table {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    background: var(--card-bg-color);
    border: 1px solid var(--border-light);
    border-radius: 6px;
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.tbl_wrap caption {
    padding: var(--spacing-md) 0;
    font-weight: 600;
    text-align: left;
    color: var(--text-primary);
    font-size: 1em;
}

.tbl_head01 {margin: 0 0 var(--spacing-lg)}
.tbl_head01 caption {padding: 0; font-size: 0; line-height: 0; overflow: hidden}

.tbl_head01 thead th {
    padding: var(--spacing-lg) var(--spacing-md);
    font-weight: 600;
    text-align: center;
    border-bottom: 2px solid var(--border-light);
    background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
    color: var(--text-primary);
    font-family: var(--content-font-family);
    font-size: 0.95em;
    position: sticky;
    top: 0;
    z-index: 10;
}

.tbl_head01 thead th input {vertical-align: middle}

.tbl_head01 tfoot th, .tbl_head01 tfoot td {
    padding: var(--spacing-md) var(--spacing-sm);
    border-top: 1px solid var(--border-medium);
    background: var(--gray-100);
    text-align: center;
    color: var(--text-primary);
    font-weight: 500;
}

.tbl_head01 tbody th {
    padding: var(--spacing-md) var(--spacing-sm);
    border-bottom: 1px solid var(--border-light);
    background: var(--gray-50);
    color: var(--text-primary);
    font-weight: 500;
}

.tbl_head01 td {
    color: var(--text-secondary);
    padding: var(--spacing-md) var(--spacing-sm);
    border-bottom: 1px solid var(--border-light);
    line-height: 1.5em;
    word-break: break-all;
    background: var(--card-bg-color);
    transition: var(--transition-fast);
}

.tbl_head01 tbody tr:hover td {
    background: var(--gray-50);
    transform: translateX(2px);
}

.tbl_head01 a {
    color: var(--text-primary);
    transition: var(--transition-fast);
    text-decoration: none;
}

.tbl_head01 a:hover {
    color: var(--accent-color);
    text-decoration: underline;
}

/* 폼 테이블 현대화 */
.tbl_frm01 {margin: 0 0 var(--spacing-lg)}
.tbl_frm01 table {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    background: var(--card-bg-color);
    border-radius: var(--card-border-radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.tbl_frm01 th {
    width: 120px;
    padding: var(--spacing-md) var(--spacing-lg);
    border: 1px solid var(--border-light);
    border-left: 0;
    background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
    text-align: left;
    color: var(--text-primary);
    font-family: var(--content-font-family);
    font-weight: 600;
    vertical-align: top;
}

.tbl_frm01 td {
    padding: var(--spacing-md) var(--spacing-lg);
    border-top: 1px solid var(--border-light);
    border-bottom: 1px solid var(--border-light);
    background: transparent;
}

.wr_content textarea, .tbl_frm01 textarea, .form_01 textarea, .frm_input {
    border: 1px solid var(--form-border-color);
    background: var(--form-bg-color);
    color: var(--form-text-color);
    vertical-align: middle;
    border-radius: var(--form-border-radius);
    padding: var(--spacing-sm) var(--spacing-md);
    font-family: var(--content-font-family);
    box-shadow: var(--shadow-sm);
    transition: var(--transition-base);
    font-size: 0.95em;
    color: var(--form-text-color);
}

textarea {
    width: 100%;
}

.wr_content textarea::placeholder,
.frm_input::placeholder {
    color: var(--text-muted);
}

.tbl_frm01 textarea {padding: var(--spacing-md)}

.full_input {width: 100%}
.half_input {width: 49.5%}
.twopart_input {width: 385px; margin-right: var(--spacing-md)}
.tbl_frm01 textarea, .write_div textarea {width: 100%; height: 120px}

.tbl_frm01 a {
    text-decoration: none;
    color: var(--accent-color);
    transition: var(--transition-fast);
}

.tbl_frm01 a:hover {
    color: var(--primary-dark);
    text-decoration: underline;
}

.tbl_frm01 .frm_file {
    display: block;
    margin-bottom: var(--spacing-sm);
}

.tbl_frm01 .frm_info {
    display: block;
    padding: 0 0 var(--spacing-sm);
    line-height: 1.4em;
    color: var(--text-muted);
    font-size: 0.9em;
}

/* 기본 리스트 현대화 */
.list_01 ul {
    border-top: 1px solid var(--border-light);
    border-radius: var(--card-border-radius);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

.list_01 li {
    border-bottom: 1px solid var(--border-light);
    background: var(--card-bg-color);
    padding: var(--spacing-lg);
    list-style: none;
    position: relative;
    transition: var(--transition-fast);
}

.list_01 li:nth-child(odd) {
    background: var(--gray-50);
}

.list_01 li:after {display: block; visibility: hidden; clear: both; content: ""}

.list_01 li:hover {
    background: var(--gray-100);
    transform: translateX(3px);
    border-left: 3px solid var(--accent-color);
}

.list_01 li.empty_li {
    text-align: center;
    padding: var(--spacing-2xl) 0;
    color: var(--text-muted);
    font-style: italic;
}

/* 폼 리스트 */
.form_01 h2 {
    font-size: 1.3em;
    color: var(--text-primary);
    font-family: var(--content-font-family);
    margin-bottom: var(--spacing-lg);
    font-weight: 600;
}

.form_01 li {margin-bottom: var(--spacing-md)}
.form_01 ul:after, .form_01 li:after {display: block; visibility: hidden; clear: both; content: ""}
.form_01 .left_input {float: left}
.form_01 .margin_input {margin-right: 1%}
.form_01 textarea {height: 120px; width: 100%}

.form_01 .frm_label {
    display: inline-block;
    width: 130px;
    color: var(--text-primary);
    font-family: var(--content-font-family);
    font-weight: 500;
    margin-bottom: var(--spacing-xs);
}

/* 자료 없는 목록 */
.empty_table {
    padding: var(--spacing-2xl) 0 !important;
    text-align: center;
    color: var(--text-muted);
    font-style: italic;
    background: var(--gray-50);
}

.empty_list {
    padding: var(--spacing-xl) 0 !important;
    color: var(--text-muted);
    text-align: center;
    font-style: italic;
}

/* 필수입력 현대화 */
.required, textarea.required {
    position: relative;
}

textarea.required:after {
    content: "*";
    color: var(--error-color);
    position: absolute;
    right: var(--spacing-sm);
    top: 50%;
    transform: translateY(-50%);
    font-size: 1.2em;
    font-weight: bold;
    pointer-events: none;
}

textarea.required:after {
    top: var(--spacing-sm);
    transform: none;
}

/* 테이블 항목별 정의 */
.td_board {width: 80px; text-align: center}
.td_category {width: 80px; text-align: center}
.td_chk {width: 40px; text-align: center}
.td_date {width: 80px; text-align: center}
.td_datetime {width: 120px; text-align: center}
.td_group {width: 80px; text-align: center}
.td_mb_id {width: 100px; text-align: center}
.td_mng {width: 80px; text-align: center}
.td_name {width: 100px; text-align: left}
.td_nick {width: 100px; text-align: center}
.td_num {width: 60px; text-align: center}
.td_numbig {width: 80px; text-align: center}
.td_stat {width: 80px; text-align: center}

.txt_active {color: var(--success-color); font-weight: 500}
.txt_done {color: var(--error-color); font-weight: 500}
.txt_expired {color: var(--text-muted)}
.txt_rdy {color: var(--success-color); font-weight: 500}

/* 검색결과 색상 */
.sch_word {
    color: var(--white);
    background: var(--accent-color);
    padding: 2px var(--spacing-sm) 3px;
    line-height: 18px;
    margin: 0 2px;
    border-radius: var(--form-border-radius);
    font-weight: 500;
}

/* 사이드뷰 현대화 */
.sv_wrap {position: relative; font-weight: normal}

.sv_wrap .sv {
    z-index: 1000;
    display: none;
    margin: var(--spacing-sm) 0 0;
    font-size: 0.9em;
    background: var(--gray-800);
    border-radius: var(--card-border-radius);
    box-shadow: var(--shadow-lg);
    backdrop-filter: blur(10px);
}

.sv_wrap .sv:before {
    content: "";
    position: absolute;
    top: -6px;
    left: 15px;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 0 6px 6px 6px;
    border-color: transparent transparent var(--gray-800) transparent;
}

.sv_wrap .sv a {
    display: inline-block;
    margin: 0;
    padding: 0 var(--spacing-md);
    line-height: 35px;
    width: 100px;
    font-weight: normal;
    color: var(--gray-300);
    transition: var(--transition-fast);
    text-align: center;
}

.sv_wrap .sv a:hover {
    background: var(--gray-900);
    color: var(--white);
}

.sv_member {color: var(--text-primary)}

.sv_on {
    display: block !important;
    position: absolute;
    top: 28px;
    left: 0px;
    width: auto;
    height: auto;
}

.sv_nojs .sv {display: block}

/* 페이징 현대화 */
.pg_wrap {
    display: inline-block;
    margin: var(--spacing-lg) 0;
    width: 100%;
}

.pg_wrap:after {display: block; visibility: hidden; clear: both; content: ""}

.pg {
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-xs);
}

.pg_page, .pg_current {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    vertical-align: middle;
    background: var(--gray-100);
    border: 1px solid var(--border-light);
    border-radius: var(--form-border-radius);
    transition: var(--transition-fast);
    min-width: 35px;
    height: 35px;
    text-decoration: none;
}

.pg a:focus, .pg a:hover {text-decoration: none}

.pg_page {
    color: var(--text-secondary);
    font-size: 0.95em;
    font-weight: 500;
}

.pg_page:hover {
    background-color: var(--gray-200);
    color: var(--text-primary);
    transform: translateY(-1px);
}

.pg_start, .pg_prev, .pg_next, .pg_end {
    background: var(--gray-100);
    padding: 0;
    border: 1px solid var(--border-light);
    border-radius: var(--form-border-radius);
    transition: var(--transition-fast);
    min-width: 35px;
    height: 35px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--text-secondary);
    text-decoration: none;
}

.pg_start::before {
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    content: "\f100"; /* 처음으로 (fa-angle-double-left) */
}

.pg_prev::before {
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    content: "\f104"; /* 이전 (fa-angle-left) */
}

.pg_next::before {
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    content: "\f105"; /* 다음 (fa-angle-right) */
}

.pg_end::before {
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    content: "\f101"; /* 마지막으로 (fa-angle-double-right) */
}


.pg_start:hover, .pg_prev:hover, .pg_end:hover, .pg_next:hover {
    background-color: var(--gray-200);
    color: var(--text-primary);
    transform: translateY(-1px);
}

.pg_current {
    display: inline-flex;
    background: var(--btn-primary-bg);
    border: 1px solid var(--btn-primary-bg);
    color: var(--btn-primary-text);
    font-weight: 600;
    min-width: 35px;
    height: 35px;
    border-radius: var(--btn-primary-radius);
    box-shadow: var(--shadow-sm);
}

/* 선택 영역 스타일 */
::selection {
    background: rgba(var(--accent-color-rgb, 74, 192, 252), 0.2);
    color: var(--text-primary);
}

::-moz-selection {
    background: rgba(var(--accent-color-rgb, 74, 192, 252), 0.2);
    color: var(--text-primary);
}


/* 슬라이더 컨테이너 */
.slider-container {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
    border-radius: var(--container-border-radius);
    box-shadow: var(--shadow-lg);
    background: var(--bg-glass);
    backdrop-filter: blur(10px);
    border: 1px solid var(--container-border-color);
}

/* 개별 슬라이드 */
.slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity var(--transition-slow);
    z-index: 1;
}

.slide.active {
    opacity: 1;
    z-index: 2;
}

/* 슬라이드 링크 */
.slide a {
    display: block;
    width: 100%;
    height: 100%;
    text-decoration: none;
    color: inherit;
    transition: transform var(--transition-base);
}

.slide a:hover {
    transform: scale(1.02);
}

/* 슬라이드 이미지 */
.slide-image {
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    position: relative;
    border-radius: var(--container-border-radius);
    overflow: hidden;
}

/* 슬라이드 오버레이 */
.slide-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        135deg,
        var(--bg-overlay) 20%,
        rgba(15, 23, 42, 0.2) 50%,
        var(--bg-overlay) 80%
    );
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 1; /* 항상 보이게 변경 */
    transition: opacity var(--transition-base);
    border-radius: var(--container-border-radius);
    mix-blend-mode: overlay;
}

/* 호버 시 더 진하게 */
.slide:hover .slide-overlay {
    opacity: 1;
    background: linear-gradient(
        135deg,
        rgba(15, 23, 42, 0.5) 0%,
        rgba(15, 23, 42, 0.3) 50%,
        rgba(15, 23, 42, 0.5) 100%
    );
}

/* 슬라이드 콘텐츠 */
.slide-content {
    text-align: center;
    color: var(--white);
    padding: var(--spacing-2xl);
    transform: translateY(var(--spacing-lg));
    transition: transform var(--transition-base);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    width: 100%;
    height: 100%;
}

.slide:hover .slide-content,
.slide.active .slide-content {
    transform: translateY(0);
}

/* 슬라이드 제목 */
.slide-title {
    font-size: calc(var(--title-font-size) * 2);
    font-weight: 600;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    font-family: var(--f-pre);
    line-height: 1;
    color: var(--white);
    text-shadow: 0 0 2px rgba(0, 0, 0, 0.4), 0 0 10px rgba(85, 85, 85, 0.7), 0 0 20px rgba(187, 187, 187, 0.3), 0 0 30px rgb(255 255 255 / 50%);
    letter-spacing: var(--spacing-lg);
}

/* 슬라이드 설명 */
.slide-description {
    font-size: calc(var(--content-font-size) * 0.9);
    font-family: var(--content-font-family);
    font-weight: 200;
    line-height: 1.5;
    letter-spacing: var(--spacing-sm);
    color: rgb(from var(--white) r g b / 100%);
    margin: 0;
    text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000, 0 0 0.3em #ffffff, 0 0 0.5em #ffffff
}

/* 슬라이더 컨트롤 버튼 */
.slider-controls {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 100%;
    display: flex;
    justify-content: space-between;
    padding: 0 var(--spacing-lg);
    z-index: 10;
    pointer-events: none;
}

.prev-btn, .next-btn {
    background: transparent;
    border: none;
    font-size: calc(var(--title-font-size) * 2);
    font-weight: normal;
    color: var(--white);
    text-shadow: 0 0 2px rgba(0, 0, 0, 0.4), 0 0 10px rgba(85, 85, 85, 0.7), 0 0 20px rgba(187, 187, 187, 0.3), 0 0 30px rgb(255 255 255 / 50%);
    cursor: pointer;
    transition: all var(--transition-base);
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: auto;
}

.prev-btn:hover,
.next-btn:hover {
    transform: scale(1.1);
}

.prev-btn:active,
.next-btn:active {
    transform: scale(0.95);
}

/* 해시태그 */
.hashtag {
    display: inline-block;
    margin: 2px 0px;
    padding: 1px 6px;
    font-size: 0.9em;
    color: var(--btn-accent-text);
    background: var(--btn-accent-bg);
    border: 0;
    border-radius: var(--btn-accent-radius);
    text-decoration: none;
    filter: drop-shadow(2px 2px rgba(0, 0, 0, 0.5));
    transition: all 0.3s ease;
    line-height: 1.6;
}

a.hashtag:hover {
    filter: brightness(1.3) drop-shadow(0 2px 1px rgba(0, 0, 0, 0.3));
    color: var(--btn-accent-text);
}

/* 링크 */

.auto_link {
    color: var(--accent-color);
    text-decoration: none;
    transition: var(--transition-fast);
}

.auto_link:hover {
    color: var(--info-color);
}

#set_secret {
    padding: 10px 30px 10px 10px;
}

/* 슬라이더 도트 네비게이션 */
.slider-dots {
    position: absolute;
    bottom: var(--spacing-lg);
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: var(--spacing-sm);
    z-index: 10;
}

.dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--gray-400);
    cursor: pointer;
    transition: all var(--transition-base);
    border: 2px solid transparent;
}

.dot:hover {
    background: var(--gray-600);
    transform: scale(1.2);
}

.dot.active {
    background: var(--accent-color);
    border-color: var(--white);
    transform: scale(1.3);
    box-shadow: var(--shadow-sm);
}

/* 애니메이션 효과 */
@keyframes slideInFromRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideInFromLeft {
    from {
        transform: translateX(-100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* 슬라이드 전환 애니메이션 클래스 */
.slide.slide-in-right {
    animation: slideInFromRight var(--transition-slow) ease-out;
}

.slide.slide-in-left {
    animation: slideInFromLeft var(--transition-slow) ease-out;
}

/* 추가 글래스 이펙트 */
.slider-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--bg-primary);
    z-index: -1;
    border-radius: var(--container-border-radius);
}

.site-logo {
    text-align: center;
    max-width: 400px;
    max-height: 400px;
    margin-bottom: var(--spacing-2xl);
    position: relative;
    z-index: 2;
}

.site-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
    transition: var(--transition-base);
}

.site-logo img:hover {
    transform: scale(1.05);
    filter: brightness(1.5) drop-shadow(0 4px 8px rgba(0, 0, 0, 0.15));
}

.ui-btn, .pg_page {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    margin: 0;
    box-sizing: border-box;
    font: inherit;
    height: auto;
    line-height: 1.5;
    overflow: visible;
    text-align: center;
    text-transform: none;
    text-decoration: none;
    display: flex;
    font-size: 12px;
    transition: all 0.2s;
    font-family: 'Pretendard';
    padding: 7px 12px;
    background: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    border: 1px solid rgb(from var(--btn-primary-text) r g b / 30%);
    border-radius: var(--btn-primary-radius);
    cursor: pointer;
    align-content: center;
    justify-content: center;
    align-items: center;
}

.ui-btn.admin {
    background: var(--btn-accent-bg);
    color: var(--btn-accent-text);
}

.ui-btn:hover {
    transform: scale(1.02);
    filter: invert(1);
}

/* character_main.php 스타일 (확장팩) */
.my_character_info .character-display {
    display: flex;
    align-items: center;
    gap: 15px;
    width: 100%;
    margin-bottom: var(--spacing-md);
}

.my_character_info .character-image-area {
    width: 80px;
    height: 80px;
    flex-shrink: 0;
}

.my_character_info .character-portrait {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.character-info-area {
    flex: 1;
}

/* member info 스타일 (RA0 기본) - 컴팩트 버전 */
.my_member_info .member-display {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: var(--spacing-sm);
    border-radius: var(--card-border-radius);
}

.my_member_info .member-image-area {
    width: 55px;
    height: 55px;
    flex-shrink: 0;
}

.my_member_info .portrait-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: var(--white);
}

.my_member_info .member-portrait {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.my_member_info .member-info-area {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.my_member_info .member_name {
    font-family: var(--title-font-family);
    font-size: 16px;
    color: var(--title-font-color);
    line-height: 1.2;
}

.my_member_info .member_id {
    font-family: var(--content-font-family);
    font-size: 12px;
    color: var(--text-muted);
    line-height: 1.2;
}

/* 우측 영역: 레벨 배지 + 마이페이지 버튼 */
.my_member_info .member-side {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}

.my_member_info .member_level {
    font-family: var(--content-font-family);
    font-size: 11px;
    color: var(--white);
    font-weight: 600;
    line-height: 1;
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    padding: 4px 8px;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgb(from var(--accent-color) r g b / 30%);
    white-space: nowrap;
}

.my_member_info .member_level i {
    margin-right: 2px;
    font-size: 10px;
}

.my_member_info .member-actions {
    display: flex;
}

.my_member_info .member-action-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 6px;
    color: var(--text-muted);
    background: var(--container-bg-color);
    border: 1px solid var(--container-border-color);
    text-decoration: none;
    font-size: 11px;
    transition: var(--transition-fast);
}

.my_member_info .member-action-btn:hover {
    color: var(--accent-color);
    border-color: var(--accent-color);
    background: var(--card-bg-color);
}

/* 알림 패널 컨테이너 */
.my_member_info .member-notifications {
    margin-top: 12px;
}

.character-inventory-panel .inventory-section {
    padding: var(--spacing-xs);
    position: relative;
}

.character-inventory-panel .inventory-title {
    margin: 0 0 var(--spacing-md) 0;
    font-size: var(--content-font-size);
    color: var(--content-font-color);
    font-weight: 600;
    font-family: var(--f-play);
    display: none;
}

.character-inventory-panel .game-inventory {
    background: transparent;
    border: none;
    padding: 0;
    max-width: none;
}

.character-inventory-panel .inventory-slot {
    width: 44px;
    height: 44px;
    border: 1px solid var(--container-border-color);
    border-radius: 4px;
    position: relative;
    cursor: pointer;
    transition: all 0.2s;
}

.character-inventory-panel .inventory-slot.character-inventory-panel .empty {
    background: var(--bg-glass);
}

.character-inventory-panel .inventory-slot.character-inventory-panel .empty:hover {
    background: var(--bg-glass);
    filter: brightness(0.5);
}

.character-inventory-panel .inventory-slot.character-inventory-panel .has-item {
    background: var(--bg-glass-dark);
    border-color: rgb(from var(--border-medium) r g b / 60%);
}

.character-inventory-panel .inventory-slot:hover {
    border-color: var(--accent-color);
    box-shadow: 0 0 5px rgba(255,255,255,0.4);
    filter: brightness(1.2);
}

/* 희귀도별 테두리 색상 */
.character-inventory-panel .inventory-slot.rarity-common {
    border: 2px solid #9d9d9d;
}

.character-inventory-panel .inventory-slot.rarity-uncommon {
    border: 2px solid #129500;
}

.character-inventory-panel .inventory-slot.rarity-rare {
    border: 2px solid #0070dd;
}

.character-inventory-panel .inventory-slot.rarity-epic {
    border: 2px solid #a335ee;
}

.character-inventory-panel .inventory-slot.rarity-legendary {
    border: 2px solid #ff8000;
    box-shadow: 0 0 8px rgba(255, 128, 0, 0.3);
}

.character-inventory-panel .item-image {
    width: 100%;
    height: 100%;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.character-inventory-panel .item-image img {
    max-width: 64px;
    max-height: 64px;
    image-rendering: pixelated; /* 도트 이미지 깨짐 방지 */
}

.character-inventory-panel .no-image {
    color: #666;
    font-size: 24px;
}

.character-inventory-panel .item-count {
    position: absolute;
    bottom: 2px;
    right: 2px;
    color: white;
    font-size: 10px;
    font-weight: bold;
    padding: 1px 3px;
    border-radius: 2px;
    line-height: 1;
    text-shadow: 0 0 1px rgba(0, 0, 0, 1), 0 0 3px rgba(85, 85, 85, 0.7), 0 0 5px rgb(0 0 0 / 80%);
}

.character-inventory-panel .item-tooltip {
    position: fixed;
    background: rgba(0,0,0,0.95);
    color: white;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #666;
    max-width: 250px;
    z-index: 1000;
    font-size: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
}

.character-inventory-panel .tooltip-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    border-bottom: 1px solid #555;
    padding-bottom: 5px;
}

.character-inventory-panel .item-name {
    font-weight: bold;
    color: #fff;
}

.character-inventory-panel .item-type {
    color: #aaa;
    font-size: 10px;
}

.character-inventory-panel .tooltip-body {
    line-height: 1.4;
}

.character-inventory-panel .item-description {
    color: #ccc;
    margin-bottom: 5px;
}

.character-inventory-panel .item-quantity {
    color: #888;
    font-size: 11px;
}

.character-tabs {
}

.character-tabs .tab-buttons {
    display: flex;
}

.character-tabs .tab-btn {
    background: none;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    transition:
        color var(--motion-duration-base) var(--motion-ease),
        background-color var(--motion-duration-base) var(--motion-ease),
        filter var(--motion-duration-fast) ease,
        transform var(--motion-duration-base) var(--motion-ease);
    position: relative;
    font-size: 13px;
    color: rgb(from var(--content-font-color) r g b / 70%);
}

.character-tabs .tab-btn:hover {
    filter: brightness(1.5);
    color: rgb(from var(--content-font-color) r g b / 100%);
}

.character-tabs .tab-btn.active {
}

.character-tabs .notification-badge {
    position: absolute;
    top: 5px;
    right: 2px;
    background: var(--accent-color);
    color: var(--white);
    border-radius: 4px;
    width: 12px;
    height: 12px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    min-width: 12px;
}

.character-tabs .tab-content {
    display: none;
}

.character-tabs .tab-content.active {
    display: block;
}

/* 알림 스타일 */
.character-tabs .notifications-list {
    max-height: 300px;
    overflow-y: auto;
}

.character-tabs .notification-section h4 {
    font-size: 14px;
    margin-bottom: 10px;
    color: #333;
    border-bottom: 1px solid #eee;
    padding-bottom: 5px;
}

.character-tabs .notification-item {
    padding: 8px 10px;
    border-radius: 4px;
    border-left: 3px solid var(--card-border-color);
}

.character-tabs .notification-item.character-tabs .unread {
    background-color: #f8f9ff;
    border-left-color: #007bff;
}

.character-tabs .notification-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
}

.character-tabs .notification-content .character-tabs .sender {
    font-weight: bold;
    color: #333;
}

.character-tabs .notification-content .character-tabs .subject {
    flex: 1;
    margin: 0 10px;
    color: #666;
}

.character-tabs .notification-content .character-tabs .date {
    color: #999;
    font-size: 11px;
}

.character-tabs .notification-more {
    text-align: center;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #eee;
}

.character-tabs .btn-more {
    font-size: 12px;
    color: #007bff;
    text-decoration: none;
}

.character-tabs .no-notifications {
    text-align: center;
    padding: 40px 20px;
    color: #999;
}

.character-tabs .no-notifications i {
    font-size: 24px;
    margin-bottom: 10px;
    display: block;
}

/* 인벤토리 로딩 */
.character-tabs .inventory-loading {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.character-tabs .inventory-loading i {
    font-size: 24px;
    margin-bottom: 10px;
    display: block;
}

.member_menu {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    justify-content: space-between;
}

.character_actions {
    height: 100%;
    width: fit-content;
}

.character_actions a {
    line-height: 1;
    height: 100%;
    padding: 0 var(--spacing-xs);
    font-weight: 600;
    border: 0;
    font-size: 0.95em;
    font-family: var(--content-font-family);
    border-radius: var(--btn-primary-radius);
    transition: var(--transition-base);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-decoration: none;
}


/* 스탯 카드 스타일 (32x32 사이즈) */
.character_stats_cards {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 10px;
}

.character-display .stat_card {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: var(--card-bg-color, #ffffff);
    border: 1px solid var(--card-border-color, #dee2e6);
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.character-display .stat_card:hover {
    background: var(--primary-light, #e3f2fd);
    border-color: var(--primary-color, #2196f3);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.character-display .stat_icon {
    font-size: 16px;
    line-height: 1;
}

/* 스탯별 색상 */
.character-display .stat_card[title*="체력"] .stat_icon { color: #dc3545; }
.character-display .stat_card[title*="정신력"] .stat_icon { color: #007bff; }
.character-display .stat_card[title*="공격"] .stat_icon { color: #fd7e14; }
.character-display .stat_card[title*="방어"] .stat_icon { color: var(--success-color); }
.character-display .stat_card[title*="민첩"] .stat_icon { color: #6f42c1; }
.character-display .stat_card[title*="지능"] .stat_icon { color: #17a2b8; }

/* 툴팁 */
.character-display .stat_card::after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.9);
    color: white;
    padding: 4px 6px;
    border-radius: 4px;
    font-size: 10px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s;
    z-index: 10;
    margin-bottom: 4px;
}

.character-display .stat_card:hover::after {
    opacity: 1;
}











/* 상점 페이지 전용 스타일 - 스코프 제한 */
.shop-page-wrapper {
    /* 모든 상점 관련 스타일을 이 래퍼 안에 포함 */
}

.shop-page-wrapper .shop-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--spacing-lg);
    background: var(--container-bg-color);
    border-radius: var(--container-border-radius);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.shop-page-wrapper .shop-header {
    display: flex;
    padding: var(--spacing-xl);
    align-items: center;
    justify-content: space-between;
}

.shop-page-wrapper .shop-title {
    color: var(--title-font-color);
    font-family: var(--title-font-family);
    font-size: var(--title-font-size);
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    font-weight: bold;
}

.shop-page-wrapper .shop-description {
    color: var(--content-font-color);
    font-family: var(--content-font-family);
    font-size: var(--content-font-size);
    line-height: 1.6;
    font-weight: normal;
}

.shop-page-wrapper .shop-tabs {
    display: flex;
    margin-bottom: var(--spacing-xl);
}

.shop-page-wrapper .shop-tab {
    flex: 1;
    padding: var(--spacing-md) var(--spacing-lg);
    background: transparent;
    color: var(--gray-300);
    border: none;
    border-radius: var(--btn-primary-radius);
    cursor: pointer;
    transition: var(--transition-base);
    font-family: var(--content-font-family);
    font-size: var(--content-font-size);
    text-align: center;
    text-decoration: none;
    display: block;
}

.shop-page-wrapper .shop-tab:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--white);
    transform: translateY(-2px);
}

.shop-page-wrapper .shop-tab.active {
    background: var(--primary-color);
    color: var(--white);
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.shop-page-wrapper .shop-currency {
    display: flex;
    justify-content: center;
}

.shop-page-wrapper .currency-item {
    padding: var(--spacing-sm) var(--spacing-sm);
    color: var(--content-font-color);
    font-family: var(--f-pre);
    font-size: var(--title-font-size);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    width: 100%;
    justify-content: center;
}

.shop-page-wrapper .currency-amount {
    color: var(--accent-color);
    font-weight: bold;
}

.shop-page-wrapper .exp-amount {
    color: var(--success-color);
}

.shop-page-wrapper .shop-items {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: var(--spacing-sm);
}

.shop-page-wrapper .shop-item {
    background: var(--card-bg-color);
    border: 1px solid var(--card-border-color);
    border-radius: var(--card-border-radius);
    padding: var(--spacing-lg);
    text-align: center;
    transition: var(--transition-base);
    backdrop-filter: blur(15px);
    position: relative;
    overflow: hidden;
}

.shop-page-wrapper .shop-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
    opacity: 0;
    transition: var(--transition-base);
}

.shop-page-wrapper .shop-item:hover {
    transform: scale(1.01);
    box-shadow: var(--shadow-glass);
    border-color: rgba(255, 255, 255, 0.2);
}

.shop-page-wrapper .shop-item:hover::before {
    opacity: 1;
}

.shop-page-wrapper .item-image {
    width: 64px;
    height: 64px;
    margin: 0 auto var(--spacing-md);
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--white);
    border-radius: var(--card-border-radius);
    border: 1px solid rgb(from var(--card-border-color) r g b / 50%);
    overflow: hidden;
}

.shop-page-wrapper .item-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
    image-rendering: pixelated;
}

.shop-page-wrapper .item-image.no-image {
    color: var(--gray-400);
    font-size: var(--spacing-md);
}

.shop-page-wrapper .item-name {
    color: var(--content-font-color);
    font-family: var(--content-font-family);
    font-size: var(--sub-title-font-size);
    font-weight: 500;
}

.shop-page-wrapper .item-price {
    margin: var(--spacing-xs) 0;
}

.shop-page-wrapper .original-price {
    color: var(--gray-400);
    font-size: calc(var(--content-font-size) * 0.9);
    text-decoration: line-through;
    display: block;
    margin-bottom: var(--spacing-xxs);
}

.shop-page-wrapper .current-price {
    color: var(--accent-color);
    font-size: calc(var(--content-font-size) * 1.3);
    font-weight: bold;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.shop-page-wrapper .discount-badge {
    background: linear-gradient(135deg, var(--error-color), #ff8787);
    color: var(--white);
    padding: var(--spacing-xxs) var(--spacing-sm);
    border-radius: 20px;
    font-size: calc(var(--content-font-size) * 0.8);
    font-weight: bold;
    display: inline-block;
    margin-top: var(--spacing-xs);
    box-shadow: var(--shadow-sm);
}

.shop-page-wrapper .item-limits {
    margin: var(--spacing-md) 0;
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
}

.shop-page-wrapper .item-limit {
    color: var(--content-font-color);
    font-size: calc(var(--content-font-size) * 0.85);
    padding: var(--spacing-xxs) var(--spacing-sm);
    background: rgba(255, 255, 255, 0.05);
    border-radius: var(--spacing-md);
    border: 1px solid rgba(255, 255, 255, 0.1);
}


/* 등급 제한 스타일 */
.shop-page-wrapper .item-limit.rank-limit {
    color: #ff6b6b;
}

/* 등급 부족으로 구매불가 시 버튼 스타일 */
.shop-page-wrapper .shop-item.rank-locked .buy-button {
    background: #666;
    cursor: not-allowed;
}

.shop-page-wrapper .shop-item.rank-locked {
    opacity: 0.8;
}

.shop-page-wrapper .buy-button {
    padding: var(--spacing-sm) var(--spacing-md);
    background: var(--btn-primary-bg);
    color: var(--btn-primary-text);
    border: none;
    border-radius: var(--btn-primary-radius);
    font-family: var(--content-font-family);
    font-size: calc(var(--content-font-size) * 0.9);
    font-weight: normal;
    cursor: pointer;
    transition: var(--transition-base);
    box-shadow: var(--shadow-md);
    text-transform: uppercase;
    margin-top: var(--spacing-sm);
}

.shop-page-wrapper .buy-button:hover:not(:disabled) {
    background: var(--accent-color);
    box-shadow: var(--shadow-lg);
}

.shop-page-wrapper .buy-button:disabled {
    background: var(--gray-600);
    color: var(--gray-400);
    cursor: not-allowed;
    opacity: 0.6;
}

.shop-page-wrapper .shop-empty {
    text-align: center;
    padding: var(--spacing-2xl);
    color: var(--gray-300);
    font-family: var(--content-font-family);
    font-size: calc(var(--content-font-size) * 1.1);
    background: var(--bg-glass);
    border-radius: var(--card-border-radius);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
}

/* 알림 아이콘 */
li.notification-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.notification-icon a {
    position: relative;
    display: inline-block;
    padding: 0;
}

.notification-icon i {
    margin-right: 4px;
}

#notification-count {
    position: absolute;
    top: -4px;
    right: -5px;
    background: #ff4444;
    color: var(--white);
    border-radius: 4px;
    padding: 0px;
    font-size: 0.75em;
    font-weight: bold;
    min-width: 10px;
    text-align: center;
    line-height: 1.2;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(255, 68, 68, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(255, 68, 68, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(255, 68, 68, 0);
    }
}

/* 모달 스타일 - 별도 스코프 */
.shop-buy-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(5px);
}

.shop-buy-modal .modal-content {
    background: var(--bg-glass-dark);
    margin: 10% auto;
    padding: var(--spacing-2xl);
    border: 1px solid rgba(255, 255, 255, 0.2);
    width: 90%;
    max-width: 500px;
    border-radius: var(--card-border-radius);
    backdrop-filter: blur(20px);
    box-shadow: var(--shadow-glass);
}

.shop-buy-modal .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
}

.shop-buy-modal .modal-title {
    color: var(--white);
    font-family: var(--title-font-family);
    font-size: calc(var(--title-font-size) * 0.8);
    margin: 0;
}

.shop-buy-modal .close {
    color: var(--gray-400);
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: var(--transition-fast);
    line-height: 1;
}

.shop-buy-modal .close:hover {
    color: var(--white);
}

.shop-buy-modal .modal-text {
    color: var(--gray-200);
    font-family: var(--content-font-family);
    font-size: var(--content-font-size);
    margin-bottom: var(--spacing-lg);
    line-height: 1.6;
}

.shop-buy-modal .quantity-input {
    margin: var(--spacing-lg) 0;
}

.shop-buy-modal .quantity-input label {
    color: var(--white);
    font-family: var(--content-font-family);
    display: block;
    margin-bottom: var(--spacing-sm);
}

.shop-buy-modal .quantity-input input {
    width: 80px;
    padding: var(--spacing-sm);
    background: var(--bg-glass);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--form-border-radius);
    color: var(--white);
    font-family: var(--content-font-family);
    text-align: center;
}

.shop-buy-modal .modal-buttons {
    display: flex;
    gap: var(--spacing-md);
    justify-content: flex-end;
    margin-top: var(--spacing-xl);
}

.shop-buy-modal .modal-button {
    padding: var(--spacing-md) var(--spacing-xl);
    border: none;
    border-radius: var(--btn-primary-radius);
    font-family: var(--content-font-family);
    font-size: var(--content-font-size);
    cursor: pointer;
    transition: var(--transition-base);
    min-width: 80px;
}

.shop-buy-modal .modal-button.cancel {
    background: var(--gray-600);
    color: var(--white);
}

.shop-buy-modal .modal-button.cancel:hover {
    background: var(--gray-500);
}

.shop-buy-modal .modal-button.confirm {
    background: linear-gradient(135deg, var(--btn-primary-bg), var(--primary-dark));
    color: var(--btn-primary-text);
    font-weight: 600;
}

.shop-buy-modal .modal-button.confirm:hover {
    background: linear-gradient(135deg, var(--primary-dark), var(--btn-primary-bg));
    transform: translateY(-1px);
}


/* 반응형 */
@media (max-width: 768px) {
    #hd ~ #wrapper {
        top: 0;
        left: 0;
        right: 0;
        padding-left: 0;
        padding-right: 0;
        padding-top: 0; /* 모바일에서 헤더 숨김 시 패딩 제거 */
    }

    #container_wr {
        padding: 30px 0;
    }

    #container {
        flex-direction: column-reverse;
        overflow-y: scroll;
        margin: 0;
        height: auto;
        min-height: 100svh;
        max-height: 100lvh;
    }

    #sidebar-panel {
        width: 100%;
        flex-direction: row;
        height: auto;
        margin-top: var(--spacing-sm);
        gap: var(--spacing-sm);
    }

    .sidebar-section {
        padding: var(--spacing-xs);
    }

    #data-visualization-grid {
        height: auto;
        flex-direction: column-reverse;
    }

    #data-visualization-grid #analytics-widget {
        display: none;
    }

    #performance-metrics {
        max-width: none;
        flex: 1;
    }

    .slider-container {
    }

    .slide-title {
        font-size: calc(var(--title-font-size) * 0.8);
    }

    .slide-description {
        font-size: calc(var(--content-font-size) * 0.9);
    }

    .slide-content {
        padding: var(--spacing-xl);
    }

    .prev-btn,
    .next-btn {
        width: 40px;
        height: 40px;
        font-size: calc(var(--content-font-size) * 1.2);
    }

    .slider-controls {
        padding: 0 var(--spacing-md);
    }

    .slider-dots {
        bottom: var(--spacing-md);
        padding: var(--spacing-xs) var(--spacing-sm);
    }

    .character-inventory-panel .inventory-grid {
        grid-template-columns: repeat(13, 1fr);
    }

    #hd_wrapper {
        height: fit-content;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        padding: 0;
    }

    #logo a {
        height: fit-content;
        width: fit-content;
    }

    #logo img {
        height: fit-content;
        width: calc(var(--header-height) * 1.2);
    }

    #logo {
        height: fit-content;
        padding: var(--spacing-md);
        position: relative;
        z-index: 999;
    }

    .hd_login {
        height: fit-content;
    }

    #main_menu_list {
        flex-wrap: wrap;
    }

    /* 로그인 폼 모바일 */
    .login_form {
        max-width: 100%;
        box-sizing: border-box;
    }

    .login_row {
        flex-direction: column;
    }

    .login_row .btn_login {
        width: 100%;
        padding: var(--spacing-sm);
    }

    .intro-container {
        padding: var(--spacing-md);
    }

    .intro-content {
        max-width: 100%;
    }
}

@media (max-width: 480px) {
    .slider-container {
        height: 250px;
    }

    .slide-title {
        font-size: calc(var(--title-font-size) * 0.6);
    }

    .slide-description {
        font-size: calc(var(--content-font-size) * 0.8);
    }

    .slide-content {
        padding: var(--spacing-lg);
    }

    .dot {
        width: 10px;
        height: 10px;
    }

    .prev-btn,
    .next-btn {
        width: 35px;
        height: 35px;
        font-size: var(--content-font-size);
    }
}



/* Shop Item Tooltip Styles */
.shop-item-tooltip {
    position: fixed;
    background: rgb(from var(--container-bg-color) r g b / 90%);
    color: var(--white);
    padding: 10px;
    border-radius: 6px;
    border: 1px solid rgb(from var(--container-border-color) r g b / 40%);
    max-width: 250px;
    z-index: 99999;
    font-size: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.5);
    pointer-events: none;
}

.shop-item-tooltip .tooltip-header {
    border-bottom: 1px solid rgb(from var(--container-border-color) r g b / 40%);
    padding-bottom: 5px;
    margin-bottom: 5px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.shop-item-tooltip .item-name {
    color: var(--white);
    font-weight: bold;
    font-size: 13px;
}

.shop-item-tooltip .item-type {
    color: rgb(from var(--content-font-color) r g b / 70%);
    font-size: 11px;
}

.shop-item-tooltip .tooltip-body {
    padding-top: 5px;
}

.shop-item-tooltip .item-description {
    color: rgb(from var(--content-font-color) r g b / 90%);
    line-height: 1.4;
    margin-bottom: 8px;
}

.shop-item-tooltip .item-price {
    color: var(--accent-color);
    font-weight: bold;
    margin-top: 5px;
    padding-top: 5px;
    border-top: 1px solid #333;
}


.my_character_info .character-faction {
    position: absolute;
    right: 15px;
    top: 12px;
    /* padding: 1px 5px; */
    /* background: rgba(0, 0, 0, 0.7); */
    /* border-radius: 4px; */
    font-size: 10px;
    font-weight: bold;
    /* border: 1px solid rgba(255, 255, 255, 0.2); */
    backdrop-filter: blur(10px);
}


.my_character_info .character-rank {
    display: inline-block;
    margin-left: 4px;
    padding: 2px 5px;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 5px;
    font-size: 10px;
    font-weight: bold;
    border: 1px solid currentColor;
    opacity: 0.9;
}

.my_character_info .character_name {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    margin-bottom: calc(var(--spacing-xs) * 1);
}

.my_character_info .stat-content {
    display: flex;
    align-items: center;
    gap: 3px;
}

.my_character_info .character_name strong {
    font-size: 16px;
    color: var(--content-font-color);
    filter: brightness(1.5);
}

.my_character_info .character-name-rank {
    display: flex;
}

.my_character_info .bar-fill {
    height: 100%;
    border-radius: var(--form-border-radius);
    background: var(--hp-color);
    transition: width 0.5s ease;
    border: 1px solid var(--card-border-color);
    position: relative;
    flex: 1;
}

.my_character_info .hp-bar, .my_character_info .mp-bar {
    height: 10px;
    overflow: hidden;
    border-radius: var(--form-border-radius);
    margin-bottom: var(--spacing-xs);
    display: flex;
    align-items: center;
    gap: 5px;
}

.my_character_info .mp-bar .bar-fill {
    background: var(--mp-color);
}

.my_character_info .vital-text {
    font-size: calc(var(--content-font-size) * 0.5);
    font-weight: 600;
    color: var(--white);
    font-family: var(--content-font-family);
    /* position: absolute; */
    z-index: 10;
    line-height: 1.6;
    /* transform: translateX(7px); */
     text-shadow:
      -1px -1px 0 #000,
       1px -1px 0 #000,
      -1px  1px 0 #000,
       1px  1px 0 #000;
}

.my_character_info .character-arousals {
    display: flex;
    margin-bottom: calc(var(--spacing-xs) * 1);
}

.my_character_info .stat-item.arousal-item {
    display: flex;
    align-items: center;
    gap: 5px;
    flex: 1;
}

.my_character_info .stat-icon {
    width: 25px;
    height: 25px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--content-font-size);
    background: var(--container-bg-color);
    border: 1px solid var(--container-border-color);
    display: none;
}

.my_character_info .stat-content .stat-value {
    font-size: calc(var(--content-font-size) * 1.05);
    font-weight: 500;
    color: var(--content-font-color);
    font-family: var(--content-font-family);
}

.my_character_info .stat-content .stat-label {
    color: rgb(from var(--content-font-color) r g b / 70%);
    font-size: 8px;
    font-weight: 500;
}

/* .my_character_info .attack-arousal .stat-icon {
    background: var(--error-color);
    color: var(--white);
}

.my_character_info .defense-arousal .stat-icon {
    background: var(--info-color);
    color: var(--white);
} */


.flex_place {
    display: flex;
    align-items: center;
    flex-direction: row;
    flex-wrap: nowrap;
    gap: 5px;
}

.flex_space {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.flex_space a.btn {
    text-decoration: none !important;
    color: var(--white);
}

.flex_end {
    width: 100%;
    display: inline-flex;
    justify-content: flex-end;
    align-items: flex-end;
    gap: 5px;
}

.flex_c {
    display: flex !important;
    flex-direction: column !important;
}

.flex_c_5 {
    display: flex !important;
    gap: 5px !important;
    flex-direction: column !important;
}

.flex_1 {
    flex: 1;
}

.sound_only {
    display: none;
}

/* ========================================
   모바일 메뉴 스타일 (미디어 쿼리 기반)
======================================== */

/* 기본적으로 모바일 메뉴 버튼과 사이드바 숨김 (PC) */
.mobile_menu_btn {
    display: none;
}

.mobile_sidebar,
.mobile_overlay {
    display: none;
}

/* 768px 이하: 모바일 모드 */
@media (max-width: 768px) {
    /* 햄버거 메뉴 버튼 표시 */
        .mobile_menu_btn {
        display: flex;
        align-items: center;
        justify-content: center;
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1001;
        width: 45px;
        height: 45px;
        background: var(--container-bg-color);
        border: 1px solid var(--container-border-color);
        color: var(--content-font-color);
        font-size: 1.3em;
        border-radius: var(--container-border-radius);
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgb(from var(--black) r g b / 20%);
    }

    .mobile_menu_btn:hover {
        background: var(--btn-accent-bg);
        color: var(--btn-accent-text);
        transform: scale(1.05);
    }

    #hd {
        display: none !important;
    }

    /* PC 메뉴 및 로고 숨김 */
    #hd_wrapper #hd_menu {
        display: none !important;
    }

    #hd_wrapper #logo {
        display: none !important;
    }

    .hd_login {
        display: none !important;
    }

    /* 모바일 사이드바 */
    .mobile_sidebar {
        display: block;
        position: fixed;
        top: 0;
        left: -300px;
        width: 280px;
        height: 100%;
        background: var(--card-bg-color);
        box-shadow: 4px 0 12px rgb(from var(--black) r g b / 15%);
        z-index: 1002;
        overflow-y: auto;
        transition: left 0.3s ease;
    }

    .mobile_sidebar.active {
        left: 0;
    }

    /* 사이드바 헤더 */
    .mobile_sidebar_header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
    }

    /* 모바일 홈 버튼 */
    .mobile_home_btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: transparent;
        border: none;
        cursor: pointer;
        border-radius: 6px;
        transition: background 0.2s ease;
        text-decoration: none;
    }

    .mobile_home_btn i {
        font-size: 22px;
        color: var(--accent-color);
    }

    .mobile_home_btn:hover {
        background: var(--container-bg-color);
    }

    .mobile_menu_close {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: transparent;
        border: none;
        cursor: pointer;
        border-radius: 6px;
        transition: background 0.2s ease;
    }

    .mobile_menu_close i {
        font-size: 24px;
        color: var(--content-font-color);
    }

    .mobile_menu_close:hover {
        background: var(--container-bg-color);
    }

    /* 모바일 메뉴 리스트 */
    .mobile_sidebar_nav {
        padding: 5px 0;
    }

    .mobile_menu_list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .mobile_menu_item {
    }

    .mobile-menu-separator {
        height: 10px;
        border: none;
    }

    .mobile_menu_link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 15px;
        color: var(--content-font-color);
        font-family: var(--header-font-family);
        font-size: var(--header-font-size);
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .mobile_menu_link i {
        margin-right: 10px;
    }

    .mobile_menu_link span {
        flex: 1;
    }

    .mobile_menu_link:hover {
        background: var(--container-bg-color);
        color: var(--primary-color);
    }

    /* 하위메뉴 화살표 */
    .submenu_arrow {
        transition: transform 0.3s ease;
        margin-left: auto;
    }

    .submenu_arrow.active {
        transform: rotate(180deg);
    }

    /* 모바일 하위메뉴 */
    .mobile_submenu {
        list-style: none;
        margin: 0;
        padding: 0;
        max-height: 0;
        overflow: hidden;
        background: var(--container-bg-color);
        transition: max-height 0.3s ease;
    }

    .mobile_submenu.active {
        max-height: 500px;
    }

    .mobile_submenu_item {
        border-bottom: 1px solid var(--border-light);
    }

    .mobile_submenu_item:last-child {
        border-bottom: none;
    }

    .mobile_submenu_link {
        display: flex;
        align-items: center;
        padding: 12px 20px 12px 40px;
        color: var(--text-muted);
        font-size: calc(var(--header-font-size) * 0.9);
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .mobile_submenu_link i {
        margin-right: 8px;
        font-size: calc(var(--header-font-size) * 0.75);
    }

    .mobile_submenu_link:hover {
        background: var(--card-bg-color);
        color: var(--primary-color);
        padding-left: 45px;
    }

    /* 모바일 사용자 영역 */
    .mobile_user_area {
        margin-top: 20px;
        padding: 20px;
        border-top: 2px solid var(--border-light);
        background: var(--container-bg-color);
    }

    .mobile_user_info {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        padding: 12px;
        background: var(--card-bg-color);
        border-radius: 8px;
    }

    .mobile_user_info i {
        font-size: 20px;
        color: var(--primary-color);
        margin-right: 10px;
    }

    .mobile_user_info span {
        font-family: var(--header-font-family);
        font-size: var(--header-font-size);
        font-weight: 600;
        color: var(--content-font-color);
    }

    /* 모바일 접속자 수 */
    .mobile_connect {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        padding: 10px 12px;
        background: var(--container-bg-color);
        border-radius: 6px;
        border: 1px solid var(--border-light);
    }

    .mobile_connect i {
        font-size: 16px;
        color: var(--accent-color);
        margin-right: 8px;
    }

    .mobile_connect a,
    .mobile_connect span {
        font-size: calc(var(--header-font-size) * 0.85);
        color: var(--content-font-color);
        text-decoration: none;
    }

    .mobile_connect a:hover {
        color: var(--primary-color);
    }

    .mobile_user_menu {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .mobile_user_menu li {
    }

    .mobile_user_menu li:last-child {
        border-bottom: none;
    }

    .mobile_user_menu a {
        display: flex;
        align-items: center;
        padding: 12px 10px;
        color: var(--content-font-color);
        font-size: calc(var(--header-font-size) * 0.9);
        text-decoration: none;
        transition: all 0.2s ease;
        border-radius: 6px;
    }

    .mobile_user_menu a i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }

    .mobile_user_menu a:hover {
        background: var(--card-bg-color);
        color: var(--primary-color);
        padding-left: 15px;
    }

    .mobile_user_menu .badge {
        margin-left: auto;
        background: var(--error-color);
        color: var(--white);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    /* 모바일 오버레이 */
    .mobile_overlay {
        display: block;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgb(from var(--black) r g b / 50%);
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    .mobile_overlay.active {
        opacity: 1;
        visibility: visible;
    }

    /* 게시판 네비게이션 영역 모바일 대응 */
    .bo_nav {
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
    }

    #bo_sch {
        width: 100%;
    }

    #bo_sch form {
        flex-wrap: wrap;
    }

    #bo_sch select[name="sfl"] {
        width: 100%;
        margin-bottom: 5px;
    }

    input#stx {
        flex: 1;
        min-width: 0;
        max-width: 100%;
    }

    .bo_btn {
        width: 100%;
        text-align: center;
    }

    .bo_btn a {
        width: 100%;
        justify-content: center;
    }

    .footer-content {
        display: flex;
        align-items: flex-start;
        flex-direction: column;
        font-size: 9px;
        gap: 5px;
    }

    .footer-links {
        transform: none;
    }
}

/* ===== autolink 자동 변환 스타일 ===== */

/* 비디오 반응형 wrapper (16:9 비율) */
.video-wrapper,
.auto_link_video.ra0_embed_iframe {
    position: relative;
    width: 100%;
    max-width: 640px;
    aspect-ratio: 16 / 9;
    overflow: hidden;
    border-radius: var(--card-border-radius);
    background: var(--black);
}

.video-wrapper iframe,
.auto_link_video.ra0_embed_iframe iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

.ra0_uploaded_video {
    display: block;
    width: 100%;
    max-width: 720px;
    height: auto;
    border-radius: var(--card-border-radius);
    background: var(--black);
}

.ra0_embed {
    max-width: 100%;
}

.ra0_embed_audio {
    width: 100%;
    max-width: 640px;
}

.ra0_embed_audio iframe {
    width: 100%;
    border: 0;
    border-radius: var(--card-border-radius);
    background: var(--bg-secondary, #f5f5f5);
}

.ra0_embed_spotify iframe {
    min-height: 152px;
}

.ra0_embed_soundcloud iframe {
    min-height: 166px;
}

.ra0_embed_social {
    display: flex;
    justify-content: center;
    width: 100%;
    max-width: 560px;
}

.ra0_embed_social iframe {
    max-width: 100%;
}

.ra0_embed_twitter .twitter-tweet,
.ra0_embed_bluesky .bluesky-embed {
    margin: 0 auto !important;
}

.comment_content .auto_link_video.ra0_embed_iframe,
.comment_content .ra0_uploaded_video,
.comment_content .ra0_embed_audio {
    max-width: 480px;
}

.comment_content .ra0_embed_social {
    max-width: 500px;
}

/* 자동 링크 이미지 */
.auto_link_image {
    max-width: 100%;
    height: auto;
}

/* ===== 통합 위젯 툴바 ===== */
.widget-toolbar {
    position: fixed;
    bottom: 4%;
    left: 1%;
    z-index: 99990;
    display: flex;
    flex-direction: column-reverse;
    align-items: center;
    gap: 10px;
}

.widget-toolbar-toggle {
    width: 35px;
    height: 35px;
    border: none;
    border-radius: 8px;
    background: var(--btn-primary-bg, #64748b);
    color: var(--btn-primary-text, #fff);
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: calc(var(--content-font-size) * 1.2);
}

.widget-toolbar-toggle:hover {
    transform: scale(1.05);
    filter: brightness(1.1);
}

.widget-toolbar-toggle.active {
    background: var(--btn-accent-bg, #137bea);
}

.widget-toolbar-menu {
    display: none;
    flex-direction: column;
    gap: 6px;
    max-height: 400px;
}

.widget-toolbar-menu.show {
    display: flex;
    flex-wrap: wrap;
    max-width: 90px;
}

.widget-toolbar-item {
    width: 30px;
    height: 30px;
    border: none;
    border-radius: 50%;
    background: var(--btn-secondary-bg, #f1f5f9);
    color: var(--btn-secondary-text, #64748b);
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9em;
    text-decoration: none;
    position: relative;
}

.widget-toolbar-item:hover {
    background: var(--btn-accent-bg, #137bea);
    color: var(--btn-accent-text, #fff);
}

.widget-toolbar-item.active {
    background: var(--btn-accent-bg, #137bea);
    color: var(--btn-accent-text, #fff);
}

.toolbar-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    min-width: 16px;
    height: 16px;
    padding: 0 4px;
    background: #ef4444;
    color: #fff;
    font-size: 10px;
    font-weight: bold;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ========================================
   프로필 컨테이너 (라공 에디션 / 내 캐릭터 탭)
======================================== */

/* .card 내부에 profile-container가 있을 때 padding 제거 */
#character-panel .card {
    padding: 0;
}

.profile-container {
    display: flex;
    flex-direction: column;
    height: 100%;
}

/* 상단 프로필 탭 버튼 */
.profile-tab-buttons {
    display: flex;
    border-bottom: 1px solid var(--border-light, #2b2b2b);
    background: var(--card-bg-color, rgba(0,0,0,0.3));
    border-radius: var(--card-border-radius, 8px) var(--card-border-radius, 8px) 0 0;
}

.profile-tab-btn {
    flex: 1;
    padding: 12px 16px;
    background: transparent;
    border: none;
    color: var(--text-muted, #767676);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    position: relative;
}

.profile-tab-btn:hover {
    color: var(--content-font-color, #fff);
    background: rgba(255,255,255,0.05);
}

.profile-tab-btn.active {
    color: var(--accent-color, #137bea);
    background: rgba(255,255,255,0.08);
}

.profile-tab-btn .notification-badge {
    background: var(--error-color, #ef4444);
    color: #fff;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    font-weight: bold;
    min-width: 16px;
    text-align: center;
}

/* 프로필 탭 콘텐츠 영역 */
.profile-tab-contents {
    flex: 1;
    overflow: hidden;
}

.profile-tab-content {
    display: none;
    height: 100%;
    overflow-y: auto;
}

.profile-tab-content.active {
    display: block;
}

/* 로그인 폼 중앙 정렬 및 padding */
#character-panel .card .login_form {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
    height: 100%;
}

/* 회원/캐릭터 정보 영역 padding */
.my_member_info,
.my_character_info {
    padding: 12px;
}

/* 인벤토리 그리드 */
.character-inventory-section .inventory-grid,
.character-inventory-panel .inventory-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
}

/* ========================================
   내 캐릭터 탭 (캐릭터 없음 상태)
======================================== */
.no_character {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    text-align: center;
}

.no_character .no-character-icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
    margin-bottom: 16px;
    color: var(--text-muted, #767676);
    font-size: 28px;
}

.no_character p {
    margin: 0 0 8px 0;
    color: var(--content-font-color, #fff);
    font-size: 14px;
}

.no_character .sub-text {
    color: var(--text-muted, #767676);
    font-size: 12px;
    margin-bottom: 20px;
}

.no_character .btn_01 {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 12px 24px;
    background: var(--accent-color, #137bea);
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s ease;
}

.no_character .btn_01:hover {
    background: var(--primary-color, #0d5bb5);
    transform: translateY(-2px);
}

/* RA0 micro motion */
:where(
    a.btn,
    .btn,
    .btn_01,
    .btn_login,
    .btn_main,
    .btn_submit,
    .btn_close,
    .btn_cancel,
    .btn_frmline,
    .ra0_ui_btn,
    .btn_b01,
    .btn_b02,
    .btn_b03,
    .btn_b04,
    .btn_admin,
    .ui-btn,
    .pg_page,
    .pg_current,
    .pg_start,
    .pg_prev,
    .pg_next,
    .pg_end,
    .prev-btn,
    .next-btn,
    .character-tabs .tab-btn,
    .profile-tab-btn,
    .shop-page-wrapper .shop-tab,
    .shop-page-wrapper .buy-button,
    .shop-buy-modal .modal-button,
    .widget-toolbar-toggle,
    .widget-toolbar-item,
    .mobile_menu_link,
    .mobile_submenu_link,
    .mobile_user_menu a
) {
    transition-property: transform, color, background-color, border-color, box-shadow, filter, opacity;
    transition-duration: var(--motion-duration-base);
    transition-timing-function: var(--motion-ease);
}

:where(
    a.btn,
    .btn,
    .btn_01,
    .btn_login,
    .btn_main,
    .btn_submit,
    .btn_close,
    .btn_cancel,
    .btn_frmline,
    .ra0_ui_btn,
    .btn_b01,
    .btn_b02,
    .btn_b03,
    .btn_b04,
    .btn_admin,
    .ui-btn,
    .pg_page,
    .pg_start,
    .pg_prev,
    .pg_next,
    .pg_end,
    .character-tabs .tab-btn,
    .profile-tab-btn,
    .shop-page-wrapper .shop-tab,
    .shop-page-wrapper .buy-button,
    .shop-buy-modal .modal-button,
    .widget-toolbar-toggle,
    .widget-toolbar-item,
    .mobile_menu_link,
    .mobile_submenu_link,
    .mobile_user_menu a
):active:not(:disabled) {
    transform: var(--motion-press);
}

.pg_current,
.character-tabs .notification-badge,
.profile-tab-btn .notification-badge,
.toolbar-badge {
    animation: ra0-motion-pop var(--motion-duration-slow) var(--motion-ease-pop);
}

.character-tabs .tab-btn::after,
.profile-tab-btn::after {
    content: "";
    position: absolute;
    left: 50%;
    right: 50%;
    bottom: 0;
    height: 2px;
    border-radius: 999px;
    background: var(--accent-color);
    opacity: 0;
    transition:
        left var(--motion-duration-base) var(--motion-ease),
        right var(--motion-duration-base) var(--motion-ease),
        opacity var(--motion-duration-fast) ease;
}

.character-tabs .tab-btn:hover::after,
.character-tabs .tab-btn.active::after,
.profile-tab-btn:hover::after,
.profile-tab-btn.active::after {
    left: 14px;
    right: 14px;
    opacity: 1;
}

.character-tabs .tab-content.active,
.profile-tab-content.active {
    animation: ra0-motion-panel-in var(--motion-duration-slow) var(--motion-ease);
}

.widget-toolbar-toggle:hover {
    transform: translateY(-1px) scale(1.06);
}

.widget-toolbar-toggle.active {
    animation: ra0-motion-pop var(--motion-duration-slow) var(--motion-ease-pop);
}

.widget-toolbar-toggle i,
.widget-toolbar-item i {
    transition: transform var(--motion-duration-base) var(--motion-ease);
}

.widget-toolbar-toggle.active i {
    transform: rotate(90deg);
}

.widget-toolbar-menu.show {
    animation: ra0-motion-panel-in var(--motion-duration-base) var(--motion-ease);
}

.widget-toolbar-menu.show .widget-toolbar-item {
    animation: ra0-motion-menu-item var(--motion-duration-slow) var(--motion-ease-pop) both;
}

.widget-toolbar-menu.show .widget-toolbar-item:nth-child(2) { animation-delay: 30ms; }
.widget-toolbar-menu.show .widget-toolbar-item:nth-child(3) { animation-delay: 60ms; }
.widget-toolbar-menu.show .widget-toolbar-item:nth-child(4) { animation-delay: 90ms; }
.widget-toolbar-menu.show .widget-toolbar-item:nth-child(5) { animation-delay: 120ms; }
.widget-toolbar-menu.show .widget-toolbar-item:nth-child(6) { animation-delay: 150ms; }

.widget-toolbar-item:hover {
    transform: translateY(-1px) scale(1.08);
    box-shadow: var(--shadow-md);
}

.widget-toolbar-item:hover i {
    transform: scale(1.08);
}

.prev-btn:active {
    transform: translateX(-3px) scale(.95);
}

.next-btn:active {
    transform: translateX(3px) scale(.95);
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 1ms !important;
        animation-iteration-count: 1 !important;
        scroll-behavior: auto !important;
        transition-duration: 1ms !important;
    }
}

/* 헤더 로고/메뉴 표시 설정 */
<?php if ($design['show_logo_in_header'] != '1'): ?>
#logo {
    display: none !important;
}
<?php endif; ?>

<?php if ($design['show_menu_in_header'] != '1'): ?>
#hd_menu,
.mobile_menu_btn,
.mobile_sidebar,
.mobile_overlay {
    display: none !important;
}
<?php endif; ?>
