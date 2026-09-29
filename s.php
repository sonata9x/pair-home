<?php
/**
 * RA0 Edition - URL 단축 리다이렉트 스크립트
 *
 * /s.php?key=abc123 또는 /s/abc123 (htaccess 설정 시) 형식으로 접근
 * 짧은 URL 키를 원본 URL로 리다이렉트
 */

include_once './_common.php';

// 짧은 키 가져오기
$key = '';

// 1. GET 파라미터로 전달된 경우 (/s.php?key=abc123)
if (isset($_GET['key']) && $_GET['key']) {
    $key = clean_xss_tags($_GET['key'], 1, 1);
}
// 2. PATH_INFO로 전달된 경우 (/s.php/abc123)
elseif (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO']) {
    $path = trim($_SERVER['PATH_INFO'], '/');
    $key = clean_xss_tags($path, 1, 1);
}
// 3. REQUEST_URI 파싱 (/s/abc123)
elseif (isset($_SERVER['REQUEST_URI'])) {
    $uri = $_SERVER['REQUEST_URI'];
    // /s/ 또는 /s.php/ 제거
    if (preg_match('#/s(?:\.php)?/([a-zA-Z0-9]+)#', $uri, $matches)) {
        $key = $matches[1];
    }
}

// 키가 없으면 홈으로
if (!$key) {
    alert('잘못된 짧은 URL입니다.', G5_URL);
}

// 짧은 URL 조회
if (function_exists('resolve_short_url')) {
    $result = resolve_short_url($key);

    if ($result && isset($result['su_url'])) {
        $target_url = $result['su_url'];

        // URL에서 게시글 정보 추출 (예: bbs/board.php?bo_table=xxx&wr_id=123)
        // 모든 요청에 OG 메타 태그 포함 HTML 반환 (SNS 미리보기용)
        if (preg_match('/bo_table=([^&]+).*wr_id=(\d+)/', $target_url, $matches)) {
            $bo_table = $matches[1];
            $wr_id = $matches[2];

            // 게시판 정보 조회
            $board = sql_fetch("SELECT * FROM " . G5_TABLE_PREFIX . "board WHERE bo_table = '{$bo_table}'");
            if ($board) {
                $write_table = G5_TABLE_PREFIX . 'write_' . $bo_table;
                $write = sql_fetch("SELECT * FROM {$write_table} WHERE wr_id = '{$wr_id}'");

                if ($write) {
                    // 썸네일 이미지 찾기 (SNS 미리보기용 절대 URL 필요)
                    // 단계별 fallback: 한 단계에서 못 찾으면 다음으로
                    $thumbnail = '';

                    // 1순위: wr_img (전체 URL 또는 파일명)
                    if (!empty($write['wr_img'])) {
                        $thumbnail = (strpos($write['wr_img'], 'http') === 0)
                            ? $write['wr_img']
                            : G5_DATA_URL . '/file/' . $bo_table . '/' . $write['wr_img'];
                    }

                    // 2순위: 본문 첨부 이미지 (이미지 확장자만, bf_content 순)
                    if (!$thumbnail) {
                        $img_sql = "SELECT bf_file FROM " . G5_TABLE_PREFIX . "board_file
                                    WHERE bo_table = '" . sql_real_escape_string($bo_table) . "'
                                      AND wr_id = '{$wr_id}'
                                      AND bf_content >= 0
                                      AND (LOWER(bf_file) LIKE '%.jpg'
                                           OR LOWER(bf_file) LIKE '%.jpeg'
                                           OR LOWER(bf_file) LIKE '%.png'
                                           OR LOWER(bf_file) LIKE '%.gif'
                                           OR LOWER(bf_file) LIKE '%.webp')
                                    ORDER BY CAST(bf_content AS SIGNED) ASC, bf_no ASC
                                    LIMIT 1";
                        $file = sql_fetch($img_sql);
                        if ($file && !empty($file['bf_file'])) {
                            $thumbnail = G5_DATA_URL . '/file/' . $bo_table . '/' . $file['bf_file'];
                        }
                    }

                    // 3순위: RA0_URLS 마커 (신 형식 [RA0_URLS:...] / 구 형식 <!--RA0_URLS:...--> 모두 지원)
                    if (!$thumbnail) {
                        if (preg_match('/\[RA0_URLS:(.*?)\]/', $write['wr_content'], $url_matches)
                            || preg_match('/<!--RA0_URLS:(.*?)-->/', $write['wr_content'], $url_matches)) {
                            $url_images = array_filter(explode('|', $url_matches[1]));
                            if (!empty($url_images[0])) {
                                $thumbnail = $url_images[0];
                            }
                        }
                    }

                    // 3.5순위: 본문 인라인 <img src="..."> (DHTML 에디터 게시판용)
                    if (!$thumbnail && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $write['wr_content'], $img_matches)) {
                        $thumbnail = $img_matches[1];
                    }

                    // 4순위: 본문에 YouTube URL이 있으면 썸네일 추출
                    if (!$thumbnail && preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $write['wr_content'], $yt_matches)) {
                        $thumbnail = 'https://img.youtube.com/vi/' . $yt_matches[1] . '/maxresdefault.jpg';
                    }

                    // 5순위: wr_link1 (외부 이미지 링크)
                    if (!$thumbnail && !empty($write['wr_link1'])) {
                        $thumbnail = (strpos($write['wr_link1'], 'http') === 0)
                            ? $write['wr_link1']
                            : G5_URL . $write['wr_link1'];
                    }

                    // 6순위: 환경설정 대표 이미지 (cf_image)
                    if (!$thumbnail && !empty($config['cf_image'])) {
                        $thumbnail = $config['cf_image'];
                    }

                    // 제목: 글 제목 사용, 없으면 사이트 제목(cf_title)
                    $title = !empty($write['wr_subject'])
                        ? strip_tags($write['wr_subject'])
                        : strip_tags($config['cf_title']);

                    // 설명: 부제(wr_title) > 본문(wr_content) > 환경설정 설명(cf_description)
                    if (!empty($write['wr_title'])) {
                        $description = strip_tags($write['wr_title']);
                    } elseif (!empty($write['wr_content'])) {
                        $description = strip_tags(cut_str($write['wr_content'], 100, '...'));
                    } elseif (!empty($config['cf_description'])) {
                        $description = strip_tags($config['cf_description']);
                    } else {
                        $description = strip_tags($config['cf_title']);
                    }

                    // 짧은 URL (SNS 공유용)
                    $short_url = G5_URL . '/s/' . $key;

                    // 봇 user-agent 감지 (OG 스크래퍼는 redirect 없이 메타만 보여주기)
                    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
                    $is_bot = preg_match('/(Twitterbot|facebookexternalhit|LinkedInBot|Slackbot|Discordbot|TelegramBot|WhatsApp|kakaotalk|Googlebot|bingbot|Pinterest|Embedly)/i', $ua);

                    // 이미지가 HTTPS인지 (트위터 권장)
                    $secure_thumbnail = $thumbnail;
                    if ($thumbnail && strpos($thumbnail, 'http://') === 0) {
                        $secure_thumbnail = preg_replace('#^http://#', 'https://', $thumbnail);
                    }

                    // OG 메타 태그 HTML 출력
                    echo '<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="' . htmlspecialchars($title) . '">
    <meta property="og:description" content="' . htmlspecialchars($description) . '">
    <meta property="og:url" content="' . htmlspecialchars($short_url) . '">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="' . htmlspecialchars($config['cf_title']) . '">';

                    if ($thumbnail) {
                        echo '
    <meta property="og:image" content="' . htmlspecialchars($thumbnail) . '">
    <meta property="og:image:secure_url" content="' . htmlspecialchars($secure_thumbnail) . '">
    <meta property="og:image:alt" content="' . htmlspecialchars($title) . '">';
                    }

                    echo '
    <meta name="twitter:card" content="' . ($thumbnail ? 'summary_large_image' : 'summary') . '">
    <meta name="twitter:url" content="' . htmlspecialchars($short_url) . '">
    <meta name="twitter:title" content="' . htmlspecialchars($title) . '">
    <meta name="twitter:description" content="' . htmlspecialchars($description) . '">';

                    if ($thumbnail) {
                        echo '
    <meta name="twitter:image" content="' . htmlspecialchars($secure_thumbnail) . '">
    <meta name="twitter:image:alt" content="' . htmlspecialchars($title) . '">';
                    }

                    echo '
    <title>' . htmlspecialchars($title) . '</title>';

                    // 봇이 아니면 즉시 리다이렉트, 봇이면 메타만 보여주고 끝
                    if (!$is_bot) {
                        echo '
    <meta http-equiv="refresh" content="0;url=' . htmlspecialchars($target_url) . '">
    <script>window.location.replace(' . json_encode($target_url) . ');</script>';
                    }

                    echo '
</head>
<body>
    <p><a href="' . htmlspecialchars($target_url) . '">' . htmlspecialchars($title) . '</a></p>
</body>
</html>';
                    exit;
                }
            }
        }

        // 게시글이 아니거나 정보를 찾지 못한 경우
        // 환경설정 기본값으로 OG 메타 태그 출력 후 리다이렉트
        $fallback_title = strip_tags($config['cf_title']);
        $fallback_desc = !empty($config['cf_description']) ? strip_tags($config['cf_description']) : $fallback_title;
        $fallback_image = !empty($config['cf_image']) ? $config['cf_image'] : '';
        $short_url = G5_URL . '/s/' . $key;

        echo '<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="' . htmlspecialchars($fallback_title) . '">
    <meta property="og:description" content="' . htmlspecialchars($fallback_desc) . '">
    <meta property="og:url" content="' . htmlspecialchars($short_url) . '">';

        if ($fallback_image) {
            echo '
    <meta property="og:image" content="' . htmlspecialchars($fallback_image) . '">';
        }

        echo '
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="' . htmlspecialchars($config['cf_title']) . '">
    <meta name="twitter:card" content="' . ($fallback_image ? 'summary_large_image' : 'summary') . '">';

        if ($fallback_image) {
            echo '
    <meta name="twitter:image" content="' . htmlspecialchars($fallback_image) . '">';
        }

        echo '
    <meta http-equiv="refresh" content="0;url=' . htmlspecialchars($target_url) . '">
    <script>window.location.replace(' . json_encode($target_url) . ');</script>
    <title>' . htmlspecialchars($fallback_title) . '</title>
</head>
<body>
    <p><a href="' . htmlspecialchars($target_url) . '">redirect</a></p>
</body>
</html>';
        exit;
    }
}

// URL을 찾지 못한 경우 홈으로
alert('존재하지 않는 짧은 URL입니다.', G5_URL);
