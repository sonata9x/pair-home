<?php
if (!defined('_GNUBOARD_')) exit;

// URL 단축 테이블 정의
if (!defined('G5_SHORT_URL_TABLE')) {
    define('G5_SHORT_URL_TABLE',  G5_TABLE_PREFIX . 'short_url');
}

// 함수 라이브러리 자동 로드
if (!function_exists('generate_short_key')) {
    include_once G5_LIB_PATH . '/short_url.lib.php';
}

// .htaccess 리라이트 규칙 추가 (Pretty URL 시스템에 통합)
add_replace('add_mod_rewrite_rules', function($html) {
    return "RewriteRule ^s/([a-zA-Z0-9]+)$ s.php?key=\$1 [QSA,L]";
}, 10);

// nginx 규칙 추가
add_replace('add_nginx_conf_rules', function($html) {
    $get_path_url = parse_url(G5_URL);
    $base_path = isset($get_path_url['path']) ? $get_path_url['path'] . '/' : '/';
    return "rewrite ^{$base_path}s/([a-zA-Z0-9]+)$ {$base_path}s.php?key=\$1 break;";
}, 10);


function check_site_login(){
	global $g5, $config, $is_member;

	$is_page_login = (strstr($_SERVER["REQUEST_URI"], 'login') == "") ? false : true;

    // 사이트가 비공개 설정일 시, 로그인 페이지를 제외한 모든 페이지에서 외부인 접근 시
	// 로그인 페이지로 이동 시킨다.
	// if (!defined('G5_IS_ADMIN') && $config['cf_visit'] == '1') {
	// 	if(!$is_member && !$is_page_login) { goto_url(G5_BBS_URL.'/login.php'); }
	// }

    // 디자인 설정 가져오기
    $design = get_design_config();

    // === use_intro는 index.php에서 처리 (iframe src 결정) ===

    // === cf_visit 체크 (비회원만 login.php로 이동) ===
    if (!defined('G5_IS_ADMIN') && $config['cf_visit'] == '1' && !$is_member) {
        $current_script = basename($_SERVER['SCRIPT_NAME']);
        $request_uri = $_SERVER['REQUEST_URI'];

        // 허용할 경로 패턴들
        $allowed_patterns = array(
            '/css/',
            '/js/',
            '/img/',
            '/skin/',
            '/data/'
        );

        // 허용할 파일들
        $allowed_files = array(
            'intro.php',
            'login.php',
            'logout.php',
            'login_check.php',
            'ajax.mb_id.php',
            'ajax.mb_email.php',
            'register.php',
            'register_email.php',
            'register_email_update.php',
            'register_form.php',
            'register_form_update.php',
            'register_form_update_mail3.php',
            'register_result.php',
            'password.php',
            'password_check.php',
            'password_lost.php',
            'password_lost2.php',
            'password_lost_certify.php',
            'password_reset.php',
            'password_reset_update.php'
        );

        $allowed_extensions = array(
            'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico'
        );

        $is_allowed = false;

        // 경로 패턴 체크
        foreach ($allowed_patterns as $pattern) {
            if (strpos($request_uri, $pattern) !== false) {
                $is_allowed = true;
                break;
            }
        }

        // 허용 파일 체크
        if (!$is_allowed && in_array($current_script, $allowed_files)) {
            $is_allowed = true;
        }

        // 확장자 체크
        if (!$is_allowed) {
            $extension = pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_EXTENSION);
            if (in_array($extension, $allowed_extensions)) {
                $is_allowed = true;
            }
        }

        // login.php로 리다이렉트
        if (!$is_allowed) {
            $is_iframe = isset($_GET['iframe_skip']) || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '/index.php') !== false);

            if ($is_iframe) {
                goto_url_top(G5_BBS_URL.'/login.php');
            } else {
                goto_url(G5_BBS_URL.'/login.php');
            }
        }
    }
}

// 메인 화면 선택지 가져오기
function get_board_options() {
	global $g5;

	$options = ['default' => '기본 (main.php)'];

	// 커뮤니티 모듈이 설치되어 있으면 커뮤니티 옵션 추가
	if (function_exists('is_community_installed') && is_community_installed()) {
		$options['community'] = '커뮤니티 (main_commu.php)';
	}

	// 로비 확장팩 설치 시 로비 옵션 추가
	if (file_exists(G5_PATH . '/extend/lobby_config.php')
	    && (!function_exists('lobby_is_installed') || lobby_is_installed())) {
		$options['lobby'] = '로비 (community/lobby/index.php)';
	}

	// 공공 로비 확장팩 설치 시 공공 로비 옵션 추가
	if (file_exists(G5_PATH . '/extend/lobby_pub_config.php')
	    && (!function_exists('lobby_pub_is_installed') || lobby_pub_is_installed())) {
		$options['lobby_pub'] = '공공 로비 (community/lobby_pub/index.php)';
	}

	$sql = "SELECT bo_table, bo_subject FROM {$g5['board_table']} ORDER BY bo_order";
	$result = sql_query($sql);
	while($row = sql_fetch_array($result)) {
		$options[$row['bo_table']] = '[게시판] ' . $row['bo_subject'];
	}

	if (isset($g5['content_table'])) {
		$sql = "SELECT co_id, co_subject FROM {$g5['content_table']} ORDER BY co_id";
		$result = sql_query($sql, false);
		if ($result) {
			while($row = sql_fetch_array($result)) {
				$options['content:' . $row['co_id']] = '[내용 관리] ' . $row['co_subject'];
			}
		}
	}

	return $options;
}

// iframe 친화적 URL 이동 함수
function goto_url_top($url) {
    $url = str_replace("&amp;", "&", $url);
    
    if (!headers_sent())
        header('Location: '.$url);
    else {
        echo '<script>';
        echo 'top.location.replace("'.$url.'");';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
        echo '</noscript>';
    }
    exit;
}

function get_main_link() {
    global $g5, $is_member;

    try {
        // design 설정 로드 (오류 시 기본값 사용)
        $design = get_design_config();

        // use_intro 조건 제거 (intro는 index.php에서 처리)

        // main_type 설정에 따라 결정
        if (isset($design['main_type'])) {
            $main_type = (string)$design['main_type'];

            if ($main_type == 'default') {
                return G5_URL.'/main.php';
            } else if ($main_type == 'store') {
                return G5_URL.'/main_store.php';
            } else if ($main_type == 'community') {
                return G5_URL.'/main_commu.php';
            } else if ($main_type == 'lobby') {
                return G5_URL.'/community/lobby/index.php';
            } else if ($main_type == 'lobby_pub') {
                return G5_URL.'/community/lobby_pub/index.php';
            } else if (strpos($main_type, 'content:') === 0) {
                $co_id = preg_replace('/[^a-z0-9_]/i', '', substr($main_type, 8));
                if ($co_id === '') {
                    return G5_URL.'/main.php';
                }
                return function_exists('get_pretty_url')
                    ? get_pretty_url('content', $co_id)
                    : G5_BBS_URL.'/content.php?co_id=' . $co_id;
            } else {
                // 게시판으로 이동
                $bo_table = preg_replace('/[^a-z0-9_]/i', '', $main_type);
                return G5_BBS_URL.'/board.php?bo_table=' . $bo_table;
            }
        }
    } catch (Exception $e) {
        // 디자인 테이블이 없거나 오류 시 기본값 사용
    }

    // 기본값은 main.php
    return G5_URL.'/main.php';
}


// ===== 게시판 공통 라이브러리 로드 =====
// 좋아요, 해시태그, 자동링크 등 공통 기능
include_once(G5_LIB_PATH . '/board_common.lib.php');
?>
