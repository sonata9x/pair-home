<?php
/**
 * RA0 Edition - 게시판 공통 라이브러리
 * 좋아요, 해시태그, 자동링크 등 공통 기능
 */

if (!defined('_GNUBOARD_')) exit;

// 이모티콘 라이브러리 로드
if (file_exists(G5_LIB_PATH . '/emoticon.lib.php')) {
    include_once(G5_LIB_PATH . '/emoticon.lib.php');
}

/**
 * 게시글 출력용 텍스트 후처리
 */
if (!function_exists('format_output_text')) {
    function format_output_text($html) {
        $parts = preg_split('/(<[^>]+>)/', (string)$html, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (!is_array($parts)) return (string)$html;

        foreach ($parts as $idx => $part) {
            if ($part === '' || $part[0] === '<') continue;

            $parts[$idx] = preg_replace_callback('/\[(x|X|\s*)\]/', function($matches) {
                $mark = strtolower(trim((string)($matches[1] ?? '')));
                return $mark === 'x' ? '☑' : '☐';
            }, $part);
        }

        return implode('', $parts);
    }
}

// =====================================================
// 좋아요 시스템
// =====================================================

/**
 * 좋아요 테이블 생성 (ra0_like)
 */
function init_like_table() {
    global $g5;

    $like_table =  G5_TABLE_PREFIX .  'like';

    // 테이블 존재 여부 체크
    $sql = "SHOW TABLES LIKE '{$like_table}'";
    $result = sql_fetch($sql);

    if (!$result) {
        // 테이블이 없으면 생성
        $create_sql = "CREATE TABLE {$like_table} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            bo_table VARCHAR(50) NOT NULL,
            wr_id INT(11) NOT NULL,
            mb_id VARCHAR(20) NOT NULL DEFAULT '',
            ip VARCHAR(50) NOT NULL,
            liked_datetime DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_like (bo_table, wr_id, mb_id, ip),
            KEY idx_bo_table (bo_table),
            KEY idx_wr_id (wr_id),
            KEY idx_mb_id (mb_id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4";
        sql_query($create_sql);
    } else {
        // 테이블이 있으면 mb_id 컬럼 존재 여부 체크
        $col_sql = "SHOW COLUMNS FROM {$like_table} LIKE 'mb_id'";
        $col_result = sql_fetch($col_sql);
        if (!$col_result) {
            // mb_id 컬럼 추가
            sql_query("ALTER TABLE {$like_table} ADD COLUMN mb_id VARCHAR(20) NOT NULL DEFAULT '' AFTER wr_id");
            sql_query("ALTER TABLE {$like_table} ADD INDEX idx_mb_id (mb_id)");
            // UNIQUE KEY 재설정 (기존 삭제 후 새로 생성)
            sql_query("ALTER TABLE {$like_table} DROP INDEX unique_like");
            sql_query("ALTER TABLE {$like_table} ADD UNIQUE KEY unique_like (bo_table, wr_id, mb_id, ip)");
        }
    }
}

/**
 * 좋아요 수 가져오기
 */
function get_like_count($bo_table, $wr_id) {
    global $g5;

    $like_table =  G5_TABLE_PREFIX . 'like';
    $sql = "SELECT COUNT(*) as cnt FROM {$like_table}
            WHERE bo_table = '{$bo_table}' AND wr_id = '{$wr_id}'";
    $row = sql_fetch($sql);
    return $row['cnt'];
}

/**
 * 좋아요 여부 확인 (회원: mb_id, 비회원: IP 기반)
 */
function check_liked($bo_table, $wr_id, $ip = null) {
    global $g5, $member;

    if ($ip === null) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    $like_table =  G5_TABLE_PREFIX . 'like';
    $mb_id = isset($member['mb_id']) ? $member['mb_id'] : '';

    if ($mb_id) {
        // 회원: mb_id로 체크
        $sql = "SELECT id FROM {$like_table}
                WHERE bo_table = '{$bo_table}'
                AND wr_id = '{$wr_id}'
                AND mb_id = '{$mb_id}'";
    } else {
        // 비회원: IP로 체크
        $sql = "SELECT id FROM {$like_table}
                WHERE bo_table = '{$bo_table}'
                AND wr_id = '{$wr_id}'
                AND mb_id = ''
                AND ip = '{$ip}'";
    }
    $row = sql_fetch($sql);

    return isset($row['id']) && $row['id'] ? true : false;
}

/**
 * 회원의 관심글 목록 가져오기
 */
function get_member_likes($mb_id, $offset = 0, $limit = 20) {
    global $g5;

    $like_table = G5_TABLE_PREFIX . 'like';
    $likes = array();

    $sql = "SELECT l.*, w.wr_subject, w.wr_content, w.wr_datetime, w.mb_id as writer_mb_id, w.wr_name
            FROM {$like_table} l
            LEFT JOIN {$g5['write_prefix']}__bo_table__ w ON l.wr_id = w.wr_id
            WHERE l.mb_id = '{$mb_id}'
            ORDER BY l.liked_datetime DESC
            LIMIT {$offset}, {$limit}";

    // 각 게시판별로 조회해야 함
    $sql = "SELECT bo_table, wr_id, liked_datetime
            FROM {$like_table}
            WHERE mb_id = '{$mb_id}'
            ORDER BY liked_datetime DESC
            LIMIT {$offset}, {$limit}";

    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        $write_table = $g5['write_prefix'] . $row['bo_table'];
        $write = sql_fetch("SELECT wr_id, wr_subject, wr_content, wr_datetime, mb_id, wr_name FROM {$write_table} WHERE wr_id = '{$row['wr_id']}'");
        if ($write) {
            $row['write'] = $write;
            $row['board'] = get_board_db($row['bo_table']);
            $likes[] = $row;
        }
    }

    return $likes;
}

/**
 * 회원의 관심글 수 가져오기
 */
function get_member_like_count($mb_id) {
    $like_table = G5_TABLE_PREFIX . 'like';
    $row = sql_fetch("SELECT COUNT(*) as cnt FROM {$like_table} WHERE mb_id = '{$mb_id}'");
    return (int)$row['cnt'];
}

/**
 * 게시글에 관심 누른 사람 목록 가져오기
 */
function get_post_likers($bo_table, $wr_id, $limit = 20) {
    global $g5;

    $like_table = G5_TABLE_PREFIX . 'like';
    $likers = array();

    $sql = "SELECT l.mb_id, l.ip, l.liked_datetime, m.mb_name, m.mb_signature
            FROM {$like_table} l
            LEFT JOIN {$g5['member_table']} m ON l.mb_id = m.mb_id
            WHERE l.bo_table = '{$bo_table}' AND l.wr_id = '{$wr_id}'
            ORDER BY l.liked_datetime DESC
            LIMIT {$limit}";

    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        $likers[] = $row;
    }

    return $likers;
}

/**
 * 게시글 작성자 mb_id 가져오기
 */
function get_post_writer_mb_id($bo_table, $wr_id) {
    global $g5;

    $write_table = $g5['write_prefix'] . $bo_table;
    $row = sql_fetch("SELECT mb_id FROM {$write_table} WHERE wr_id = '{$wr_id}'");

    return $row['mb_id'] ?? '';
}

// =====================================================
// 해시태그 시스템
// =====================================================

/**
 * 해시태그 검색 파라미터 가져오기
 */
function get_hash_param() {
    return isset($_GET['hash']) ? clean_xss_tags($_GET['hash']) : '';
}

// =====================================================
// 자동링크 시스템
// =====================================================

/**
 * 외부 요청용 URL의 SSRF 안전성 검증
 * http/https 외 스킴 차단 + DNS 해석 후 사설/예약 대역
 * (127/8, 10/8, 172.16/12, 192.168/16, 169.254/16, ::1 등) 차단
 *
 * @param string $url 검사할 URL
 * @return bool 외부 요청에 안전하면 true
 */
function is_safe_external_url($url) {
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    $parts = parse_url($url);
    if (!$parts || empty($parts['host'])) {
        return false;
    }

    $scheme = strtolower($parts['scheme'] ?? '');
    if ($scheme !== 'http' && $scheme !== 'https') {
        return false;
    }

    // user:pass@host 형태는 호스트 파싱 혼동 우회에 쓰이므로 차단
    if (isset($parts['user']) || isset($parts['pass'])) {
        return false;
    }

    $host = trim($parts['host'], '[]');

    if (filter_var($host, FILTER_VALIDATE_IP)) {
        $ips = [$host];
    } else {
        // 도메인은 DNS 해석 결과의 모든 IP를 검사.
        // 해석 불가(10진수/8진수 IP 등 변칙 표기 포함)는 차단
        $ips = [];
        $records = @dns_get_record($host, DNS_A | DNS_AAAA);
        if (is_array($records)) {
            foreach ($records as $r) {
                if (!empty($r['ip'])) $ips[] = $r['ip'];
                if (!empty($r['ipv6'])) $ips[] = $r['ipv6'];
            }
        }
        if (!$ips) {
            return false;
        }
    }

    foreach ($ips as $ip) {
        // IPv4-mapped IPv6(::ffff:a.b.c.d 등)는 내장 IPv4를 꺼내 검사 (filter_var 우회 방지)
        if (strpos($ip, ':') !== false) {
            $bin = @inet_pton($ip);
            if ($bin === false || strlen($bin) !== 16) {
                return false;
            }
            if (substr($bin, 0, 10) === str_repeat("\x00", 10)
                && (substr($bin, 10, 2) === "\xff\xff" || substr($bin, 10, 2) === "\x00\x00")) {
                $ip = inet_ntop(substr($bin, 12, 4));
            }
        }

        // 사설(10/8, 172.16/12, 192.168/16, fc00::/7)
        // + 예약(0/8, 127/8, 169.254/16, 240/4, ::1, fe80::/10) 대역 차단
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }
        // filter_var가 통과시키는 CGNAT(100.64/10)·멀티캐스트(224/4) 대역 차단
        if (strpos($ip, ':') === false && ($long = ip2long($ip)) !== false) {
            if (($long & 0xFFC00000) === 0x64400000) return false; // 100.64.0.0/10
            if (($long & 0xF0000000) === 0xE0000000) return false; // 224.0.0.0/4
        }
    }

    return true;
}

/**
 * 외부 URL의 OG 메타데이터 파싱
 *
 * @param string $url 파싱할 URL
 * @return array|false OG 데이터 배열 또는 실패 시 false
 */
function fetch_og_metadata($url) {
    // 캐시 디렉토리
    $cache_dir = G5_DATA_PATH . '/cache/og';
    if (!is_dir($cache_dir)) {
        @mkdir($cache_dir, G5_DIR_PERMISSION, true);
    }

    // 캐시 파일 (URL 해시 기반 — 성공 24시간, 실패 1시간 유효)
    $cache_file = $cache_dir . '/' . md5($url) . '.json';
    if (file_exists($cache_file)) {
        $cache_age = time() - filemtime($cache_file);
        $cached = json_decode(@file_get_contents($cache_file), true);
        if ($cached) {
            if (!empty($cached['fail'])) {
                // 실패 네거티브 캐시 — 1시간 동안 재시도하지 않음 (매 조회 5초 블로킹 방지)
                if ($cache_age < 3600) return false;
            } elseif ($cache_age < 86400) {
                return $cached;
            }
        }
    }

    // HTTP 요청 — 자동 리다이렉트를 끄고 홉마다 SSRF 재검증하며 수동 추적
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'Mozilla/5.0 (compatible; RA0Bot/1.0)',
            'follow_location' => 0,
            'ignore_errors' => true
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true
        ]
    ]);

    $current_url = $url;
    $html = false;
    for ($hop = 0; $hop <= 3; $hop++) {
        // 홉마다 재검증 — 정상 URL이 내부망으로 리다이렉트하는 우회 차단
        if (!is_safe_external_url($current_url)) {
            $html = false;
            break;
        }

        $http_response_header = null;
        $html = @file_get_contents($current_url, false, $context);

        $status = 0;
        $location = '';
        foreach ((array)$http_response_header as $h) {
            if (preg_match('/^HTTP\/[\d.]+\s+(\d+)/', $h, $m)) {
                $status = (int)$m[1];
                $location = '';
            } elseif (preg_match('/^Location:\s*(\S+)/i', $h, $m)) {
                $location = $m[1];
            }
        }

        if ($status >= 300 && $status < 400 && $location !== '') {
            // 상대 경로 Location은 절대 URL로 변환
            if (!preg_match('#^https?://#i', $location)) {
                $p = parse_url($current_url);
                $base = $p['scheme'] . '://' . $p['host'] . (isset($p['port']) ? ':' . $p['port'] : '');
                if (strpos($location, '//') === 0) {
                    $location = $p['scheme'] . ':' . $location;
                } elseif ($location[0] === '/') {
                    $location = $base . $location;
                } else {
                    $dir = isset($p['path']) ? preg_replace('#/[^/]*$#', '/', $p['path']) : '/';
                    $location = $base . $dir . $location;
                }
            }
            $current_url = $location;
            $html = false;
            continue;
        }

        if ($status < 200 || $status >= 300) {
            $html = false;
        }
        break;
    }

    if (!$html) {
        // 실패도 1시간 네거티브 캐시 — 글 조회마다 5초 블로킹 재시도 방지
        @file_put_contents($cache_file, json_encode(['fail' => 1]));
        return false;
    }

    // OG 메타데이터 추출
    $og = [
        'title' => '',
        'description' => '',
        'image' => '',
        'site_name' => '',
        'url' => $url
    ];

    // og:title
    if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m) ||
        preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:title["\'][^>]*>/i', $html, $m)) {
        $og['title'] = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    } elseif (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $m)) {
        $og['title'] = html_entity_decode(trim($m[1]), ENT_QUOTES, 'UTF-8');
    }

    // og:description
    if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m) ||
        preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:description["\'][^>]*>/i', $html, $m)) {
        $og['description'] = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    } elseif (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) {
        $og['description'] = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    }

    // og:image
    if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m) ||
        preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\'][^>]*>/i', $html, $m)) {
        $og['image'] = $m[1];
    }

    // og:site_name
    if (preg_match('/<meta[^>]+property=["\']og:site_name["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i', $html, $m) ||
        preg_match('/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:site_name["\'][^>]*>/i', $html, $m)) {
        $og['site_name'] = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
    } else {
        // 사이트명이 없으면 도메인으로 대체
        $parsed = parse_url($url);
        $og['site_name'] = $parsed['host'] ?? '';
    }

    // 캐시 저장 — 성공은 24시간, 파싱 실패(title 없음)는 1시간 네거티브 캐시
    if ($og['title']) {
        @file_put_contents($cache_file, json_encode($og, JSON_UNESCAPED_UNICODE));
        return $og;
    }

    @file_put_contents($cache_file, json_encode(['fail' => 1]));
    return false;
}

/**
 * 링크 카드 HTML 생성
 *
 * @param string $url URL
 * @param array $og OG 메타데이터
 * @return string 링크 카드 HTML
 */
function render_link_card($url, $og) {
    $title = htmlspecialchars($og['title'] ?: $url, ENT_QUOTES);
    $desc = htmlspecialchars($og['description'] ?: '', ENT_QUOTES);
    $site = htmlspecialchars($og['site_name'] ?: parse_url($url, PHP_URL_HOST), ENT_QUOTES);
    $image = $og['image'] ? htmlspecialchars($og['image'], ENT_QUOTES) : '';

    $html = '<a href="' . htmlspecialchars($url, ENT_QUOTES) . '" class="link-card" target="_blank" rel="noopener noreferrer">';
    if ($image) {
        $html .= '<div class="link-card-image"><img src="' . $image . '" alt="" loading="lazy"></div>';
    }
    $html .= '<div class="link-card-content">';
    $html .= '<div class="link-card-title">' . $title . '</div>';
    if ($desc) {
        $html .= '<div class="link-card-desc">' . $desc . '</div>';
    }
    $html .= '<div class="link-card-site">' . $site . '</div>';
    $html .= '</div></a>';

    return $html;
}

/**
 * 내부 게시글 미리보기 카드 렌더링
 *
 * @param string $bo_table 게시판 테이블명
 * @param int $wr_id 글 번호
 * @return string|false 카드 HTML 또는 실패 시 false
 */
function render_internal_post_card($bo_table, $wr_id) {
    global $g5;

    $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', $bo_table);
    $wr_id = (int)$wr_id;
    if (!$bo_table || !$wr_id) return false;

    $write_table = $g5['write_prefix'] . $bo_table;

    // 테이블 존재 확인
    $table_check = sql_fetch("SHOW TABLES LIKE '{$write_table}'");
    if (!$table_check) return false;

    $row = sql_fetch("SELECT wr_id, wr_subject, wr_content, wr_name, wr_img,
                             wr_datetime, mb_id
                      FROM {$write_table}
                      WHERE wr_id = '{$wr_id}' AND wr_is_comment = 0");
    if (!$row) return false;

    // ch_name 컬럼이 있으면 사용
    $ch_name = '';
    $col_check = sql_fetch("SHOW COLUMNS FROM {$write_table} LIKE 'ch_name'");
    if ($col_check) {
        $ch_row = sql_fetch("SELECT ch_name FROM {$write_table} WHERE wr_id = '{$wr_id}'");
        if ($ch_row && !empty($ch_row['ch_name'])) $ch_name = $ch_row['ch_name'];
    }

    // 게시판 이름
    $board = sql_fetch("SELECT bo_subject FROM {$g5['board_table']} WHERE bo_table = '".sql_real_escape_string($bo_table)."'");

    // 작성자: ch_name > wr_name
    $author = ($ch_name ?: $row['wr_name']) ?: '익명';

    // 썸네일: wr_img > 첫 번째 첨부 이미지
    $thumb = '';
    if (!empty($row['wr_img'])) {
        if (strpos($row['wr_img'], 'http') === 0) {
            $thumb = $row['wr_img'];
        } else {
            $thumb = G5_DATA_URL.'/file/'.$bo_table.'/'.$row['wr_img'];
        }
    } else {
        $file = sql_fetch("SELECT bf_file FROM {$g5['board_file_table']}
                           WHERE bo_table='".sql_real_escape_string($bo_table)."'
                           AND wr_id='{$wr_id}' AND bf_type IN (1,2,3)
                           ORDER BY bf_no LIMIT 1");
        if ($file) $thumb = G5_DATA_URL.'/file/'.$bo_table.'/'.$file['bf_file'];
    }

    // 발췌 ({이미지:N}, {비디오:N} 플레이스홀더 제거 후 추출)
    $raw_for_excerpt = preg_replace('/\{(?:이미지|비디오|tile)(?::\d+)?\}/u', '', $row['wr_content']);
    $excerpt = cut_str(strip_tags($raw_for_excerpt), 120);
    $url = G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id;

    // <p> 안에서 사용될 수 있으므로 블록 요소 대신 span 사용
    $html = '<a href="'.htmlspecialchars($url, ENT_QUOTES).'" class="post-card" target="_blank" rel="noopener noreferrer">';
    if ($thumb) {
        $html .= '<span class="post-card-thumb"><img src="'.htmlspecialchars($thumb, ENT_QUOTES).'" alt="" loading="lazy"></span>';
    }
    $html .= '<span class="post-card-body">';
    $html .= '<span class="post-card-title">'.htmlspecialchars($row['wr_subject'], ENT_QUOTES).'</span>';
    if ($excerpt) {
        $html .= '<span class="post-card-excerpt">'.$excerpt.'</span>';
    }
    $html .= '<span class="post-card-meta">';
    $html .= '<span class="post-card-author">'.htmlspecialchars($author, ENT_QUOTES).'</span>';
    if ($board) {
        $html .= ' · <span class="post-card-board">'.htmlspecialchars($board['bo_subject'], ENT_QUOTES).'</span>';
    }
    $html .= '</span></span></a>';

    return $html;
}

/**
 * 외부 콘텐츠 자동 임베드 HTML 생성
 *
 * 허용된 공식 임베드 공급자만 처리하고, 실패하면 기존 URL 링크 흐름으로 넘긴다.
 *
 * @param string $url 원본 URL
 * @return string|false 임베드 HTML 또는 실패 시 false
 */
function render_external_embed($url) {
    $url = normalize_external_embed_url($url);

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    if ($html = render_twitter_embed($url)) return $html;
    if ($html = render_youtube_embed($url)) return $html;
    if ($html = render_vimeo_embed($url)) return $html;
    if ($html = render_spotify_embed($url)) return $html;
    if ($html = render_soundcloud_embed($url)) return $html;
    if ($html = render_bluesky_embed($url)) return $html;

    return false;
}

function normalize_external_embed_url($url) {
    $url = trim(html_entity_decode($url, ENT_QUOTES, 'UTF-8'));
    $url = preg_replace('/^(?:<|&lt;)\s*/i', '', $url);
    $url = preg_replace('/\s*(?:>|&gt;)$/i', '', $url);
    $url = preg_replace('/[\.\,\!\?\)\]\}]+$/', '', $url);

    if (preg_match('#^(?:twitter\.com|www\.twitter\.com|mobile\.twitter\.com|x\.com|www\.x\.com|youtube\.com|www\.youtube\.com|m\.youtube\.com|music\.youtube\.com|youtube-nocookie\.com|www\.youtube-nocookie\.com|youtu\.be|vimeo\.com|www\.vimeo\.com|player\.vimeo\.com|open\.spotify\.com|soundcloud\.com|www\.soundcloud\.com|m\.soundcloud\.com|bsky\.app|www\.bsky\.app)/#i', $url)) {
        $url = 'https://'.$url;
    }

    return $url;
}

function embed_attr($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function embed_host($url) {
    $host = parse_url($url, PHP_URL_HOST);
    return $host ? strtolower($host) : '';
}

function embed_query($url) {
    $query_string = parse_url($url, PHP_URL_QUERY);
    $query = array();
    if ($query_string) {
        parse_str($query_string, $query);
    }
    return $query;
}

function is_image_file($filename) {
    $path = parse_url((string)$filename, PHP_URL_PATH);
    if (!$path) $path = (string)$filename;
    return (bool)preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $path);
}

function is_video_file($filename) {
    $path = parse_url((string)$filename, PHP_URL_PATH);
    if (!$path) $path = (string)$filename;
    return (bool)preg_match('/\.(mp4|m4v|webm|ogv|ogg|mov)$/i', $path);
}

function video_mime_type($filename) {
    $path = strtolower(parse_url((string)$filename, PHP_URL_PATH) ?: (string)$filename);
    if (preg_match('/\.webm$/', $path)) return 'video/webm';
    if (preg_match('/\.(ogv|ogg)$/', $path)) return 'video/ogg';
    if (preg_match('/\.mov$/', $path)) return 'video/quicktime';
    return 'video/mp4';
}

function render_uploaded_video($url, $source = '', $class = '', $max_width = 0) {
    $classes = trim('ra0_embed ra0_uploaded_video '.$class);
    $label = $source ? $source : '동영상 파일';
    $mime = video_mime_type($url);
    $style = $max_width > 0 ? ' style="max-width: '.(int)$max_width.'px;"' : '';

    $html = '<video class="'.embed_attr($classes).'"'.$style.' controls preload="metadata" playsinline>';
    $html .= '<source src="'.embed_attr($url).'" type="'.embed_attr($mime).'">';
    $html .= '<a href="'.embed_attr($url).'" target="_blank" rel="noopener noreferrer">'.embed_attr($label).'</a>';
    $html .= '</video>';
    return $html;
}

function get_uploaded_videos($bo_table, $wr_id) {
    global $g5;

    $videos = array();
    $sql = "SELECT bf_no, bf_file, bf_source
            FROM {$g5['board_file_table']}
            WHERE bo_table = '{$bo_table}'
            AND wr_id = '{$wr_id}'
            AND bf_file <> ''
            ORDER BY bf_no ASC";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        if (!is_video_file($row['bf_file'])) continue;

        $videos[] = array(
            'url' => G5_DATA_URL . '/file/' . $bo_table . '/' . $row['bf_file'],
            'source' => $row['bf_source']
        );
    }

    return $videos;
}

function render_iframe_embed($src, $title, $provider_class, $allow = '') {
    $html = '<div class="auto_link_video ra0_embed ra0_embed_iframe '.$provider_class.'">';
    $html .= '<iframe src="'.embed_attr($src).'" title="'.embed_attr($title).'" loading="lazy" frameborder="0"';
    if ($allow) {
        $html .= ' allow="'.embed_attr($allow).'"';
    }
    $html .= ' allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>';
    $html .= '</div>';
    return $html;
}

function parse_youtube_seconds($value) {
    if ($value === null || $value === '') return 0;
    if (is_numeric($value)) return max(0, (int)$value);

    $seconds = 0;
    if (preg_match_all('/(\d+)(h|m|s)/i', (string)$value, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $num = (int)$match[1];
            $unit = strtolower($match[2]);
            if ($unit === 'h') $seconds += $num * 3600;
            if ($unit === 'm') $seconds += $num * 60;
            if ($unit === 's') $seconds += $num;
        }
    }
    return $seconds;
}

function extract_youtube_id($url) {
    $host = embed_host($url);
    $path = trim(parse_url($url, PHP_URL_PATH) ?: '', '/');
    $parts = $path === '' ? array() : explode('/', $path);
    $query = embed_query($url);

    if (!in_array($host, array('youtube.com', 'www.youtube.com', 'm.youtube.com', 'music.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com', 'youtu.be'), true)) {
        return '';
    }

    if (!empty($query['v']) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $query['v'])) {
        return $query['v'];
    }

    if ($host === 'youtu.be' && !empty($parts[0]) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $parts[0])) {
        return $parts[0];
    }

    foreach (array('embed', 'v', 'shorts', 'live') as $prefix) {
        $index = array_search($prefix, $parts, true);
        if ($index !== false && !empty($parts[$index + 1]) && preg_match('/^[a-zA-Z0-9_-]{11}$/', $parts[$index + 1])) {
            return $parts[$index + 1];
        }
    }

    return '';
}

function render_youtube_embed($url) {
    $video_id = extract_youtube_id($url);
    if (!$video_id) return false;

    $query = embed_query($url);
    $fragment = parse_url($url, PHP_URL_FRAGMENT);
    $start = 0;
    if (isset($query['start'])) $start = parse_youtube_seconds($query['start']);
    if (!$start && isset($query['t'])) $start = parse_youtube_seconds($query['t']);
    if (!$start && $fragment && preg_match('/(?:^|&)t=([^&]+)/', $fragment, $m)) {
        $start = parse_youtube_seconds($m[1]);
    }

    $src = 'https://www.youtube.com/embed/'.$video_id;
    if ($start > 0) {
        $src .= '?start='.$start;
    }

    return render_iframe_embed(
        $src,
        'YouTube video player',
        'ra0_embed_youtube',
        'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share'
    );
}

function render_vimeo_embed($url) {
    $host = embed_host($url);
    if (!in_array($host, array('vimeo.com', 'www.vimeo.com', 'player.vimeo.com'), true)) {
        return false;
    }

    $path = trim(parse_url($url, PHP_URL_PATH) ?: '', '/');
    $parts = $path === '' ? array() : explode('/', $path);
    $video_id = '';

    for ($i = count($parts) - 1; $i >= 0; $i--) {
        if (preg_match('/^\d+$/', $parts[$i])) {
            $video_id = $parts[$i];
            break;
        }
    }

    if (!$video_id) return false;

    return render_iframe_embed(
        'https://player.vimeo.com/video/'.$video_id,
        'Vimeo video player',
        'ra0_embed_vimeo',
        'autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share'
    );
}

function render_spotify_embed($url) {
    $host = embed_host($url);
    if ($host !== 'open.spotify.com') return false;

    $path = trim(parse_url($url, PHP_URL_PATH) ?: '', '/');
    $parts = $path === '' ? array() : explode('/', $path);
    $parts = array_values(array_filter($parts, function($part) {
        return $part !== '' && strpos($part, 'intl-') !== 0;
    }));

    if (!empty($parts[0]) && $parts[0] === 'embed') {
        array_shift($parts);
    }

    $type = $parts[0] ?? '';
    $id = $parts[1] ?? '';
    $allowed_types = array('track', 'album', 'playlist', 'artist', 'episode', 'show');

    if (!in_array($type, $allowed_types, true) || !preg_match('/^[a-zA-Z0-9]+$/', $id)) {
        return false;
    }

    $src = 'https://open.spotify.com/embed/'.$type.'/'.$id;
    return '<div class="ra0_embed ra0_embed_audio ra0_embed_spotify"><iframe src="'.embed_attr($src).'" title="Spotify embedded player" loading="lazy" frameborder="0" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"></iframe></div>';
}

function render_soundcloud_embed($url) {
    $host = embed_host($url);
    if (!preg_match('/(^|\.)soundcloud\.com$/', $host)) {
        return false;
    }

    $src = 'https://w.soundcloud.com/player/?url='.rawurlencode($url).'&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true';
    return '<div class="ra0_embed ra0_embed_audio ra0_embed_soundcloud"><iframe src="'.embed_attr($src).'" title="SoundCloud embedded player" loading="lazy" frameborder="0" allow="autoplay"></iframe></div>';
}

function render_twitter_embed($url) {
    $host = embed_host($url);
    if (!in_array($host, array('twitter.com', 'www.twitter.com', 'mobile.twitter.com', 'x.com', 'www.x.com'), true)) {
        return false;
    }

    $path = parse_url($url, PHP_URL_PATH) ?: '';
    if (!preg_match('#/(?:[^/]+/status(?:es)?|i/web/status)/(\d+)#i', $path, $matches)) {
        return false;
    }

    $tweet_id = $matches[1];
    $canonical = 'https://twitter.com/i/status/'.$tweet_id;
    return '<div class="ra0_embed ra0_embed_social ra0_embed_twitter"><blockquote class="twitter-tweet" data-width="500" data-lang="ko" data-dnt="true" style="margin: 10px auto;"><a href="'.embed_attr($canonical).'"></a></blockquote></div>';
}

function render_external_embed_fallback($url) {
    $url = normalize_external_embed_url($url);
    if (!filter_var($url, FILTER_VALIDATE_URL)) return '';

    $host = embed_host($url);
    $label = '외부 콘텐츠 보기';
    if (strpos($host, 'twitter.com') !== false || strpos($host, 'x.com') !== false) $label = 'X/Twitter 게시글 보기';
    if (strpos($host, 'bsky.app') !== false) $label = 'Bluesky 게시글 보기';
    if (strpos($host, 'youtube.com') !== false || strpos($host, 'youtu.be') !== false) $label = 'YouTube 영상 보기';
    if (strpos($host, 'vimeo.com') !== false) $label = 'Vimeo 영상 보기';
    if (strpos($host, 'spotify.com') !== false) $label = 'Spotify 콘텐츠 보기';
    if (strpos($host, 'soundcloud.com') !== false) $label = 'SoundCloud 콘텐츠 보기';

    return '<a href="'.embed_attr($url).'" class="auto_link ra0_embed_fallback" target="_blank" rel="noopener noreferrer">'.$label.'</a>';
}

function fetch_bluesky_oembed($url) {
    $cache_dir = G5_DATA_PATH . '/cache/oembed';
    if (!is_dir($cache_dir)) {
        @mkdir($cache_dir, G5_DIR_PERMISSION, true);
    }

    $cache_file = $cache_dir . '/' . md5('bluesky:'.$url) . '.json';
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 86400) {
        $cached = json_decode(file_get_contents($cache_file), true);
        if ($cached) return $cached;
    }

    $endpoint = 'https://embed.bsky.app/oembed?url='.rawurlencode($url).'&format=json&maxwidth=600';
    $context = stream_context_create(array(
        'http' => array(
            'timeout' => 5,
            'user_agent' => 'Mozilla/5.0 (compatible; RA0Bot/1.0)',
            'follow_location' => true,
            'max_redirects' => 3
        ),
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false
        )
    ));

    $json = @file_get_contents($endpoint, false, $context);
    if (!$json) return false;

    $data = json_decode($json, true);
    if (!is_array($data) || empty($data['html'])) return false;

    @file_put_contents($cache_file, json_encode($data, JSON_UNESCAPED_UNICODE));
    return $data;
}

function render_bluesky_embed($url) {
    $host = embed_host($url);
    $path = parse_url($url, PHP_URL_PATH) ?: '';

    if ($host !== 'bsky.app' && $host !== 'www.bsky.app') return false;
    if (!preg_match('#^/profile/([^/]+)/post/([a-z0-9]+)#i', $path, $path_matches)) return false;

    $profile = rawurldecode($path_matches[1]);
    $post_id = $path_matches[2];
    if (!preg_match('/^(?:did:[a-z0-9:.]+|[a-z0-9][a-z0-9.-]*\.[a-z0-9.-]+)$/i', $profile)) return false;

    $embed = fetch_bluesky_oembed($url);
    if (!$embed || empty($embed['html'])) {
        $pending_uri = 'at://'.$profile.'/app.bsky.feed.post/'.$post_id;
        $html = '<div class="ra0_embed ra0_embed_social ra0_embed_bluesky">';
        $html .= '<blockquote class="bluesky-embed" data-bluesky-uri-pending="'.embed_attr($pending_uri).'">';
        $html .= '<a href="'.embed_attr($url).'" target="_blank" rel="noopener noreferrer">Bluesky 게시글 보기</a>';
        $html .= '</blockquote></div>';
        return $html;
    }

    if (!preg_match('/data-bluesky-uri=["\']([^"\']+)["\']/i', $embed['html'], $uri_match) ||
        !preg_match('/data-bluesky-cid=["\']([^"\']+)["\']/i', $embed['html'], $cid_match)) {
        return false;
    }

    $uri = html_entity_decode($uri_match[1], ENT_QUOTES, 'UTF-8');
    $cid = html_entity_decode($cid_match[1], ENT_QUOTES, 'UTF-8');

    if (strpos($uri, 'at://') !== 0 || !preg_match('/^[a-z0-9]+$/i', $cid)) {
        return false;
    }

    $html = '<div class="ra0_embed ra0_embed_social ra0_embed_bluesky">';
    $html .= '<blockquote class="bluesky-embed" data-bluesky-uri="'.embed_attr($uri).'" data-bluesky-cid="'.embed_attr($cid).'">';
    $html .= '<a href="'.embed_attr($url).'" target="_blank" rel="noopener noreferrer">Bluesky 게시글 보기</a>';
    $html .= '</blockquote></div>';
    return $html;
}

/**
 * 짧은 공유 URL(/s/{key}, /s.php?key=...)을 내부 게시글 카드로 렌더링
 *
 * @param string $url 짧은 공유 URL
 * @return string|false 카드 HTML 또는 실패 시 false
 */
function render_internal_short_post_card($url) {
    if (!function_exists('resolve_short_url') || !defined('G5_SHORT_URL_TABLE')) return false;
    if (!function_exists('render_internal_post_card')) return false;

    $url = html_entity_decode(trim((string)$url), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if ($url === '') return false;

    $path = (string)parse_url($url, PHP_URL_PATH);
    $query = (string)parse_url($url, PHP_URL_QUERY);
    $key = '';

    if (preg_match('#(?:^|/)s/([A-Za-z0-9]+)/*$#', $path, $m)) {
        $key = $m[1];
    } elseif (preg_match('#(?:^|/)s\.php$#', $path)) {
        $params = array();
        parse_str(str_replace('&amp;', '&', $query), $params);
        if (!empty($params['key'])) {
            $key = preg_replace('/[^A-Za-z0-9]/', '', (string)$params['key']);
        }
    }

    if ($key === '') return false;

    $resolved = resolve_short_url($key);
    if (!$resolved) return false;

    $bo_table = isset($resolved['su_bo_table']) ? preg_replace('/[^a-zA-Z0-9_]/', '', (string)$resolved['su_bo_table']) : '';
    $wr_id = isset($resolved['su_wr_id']) ? (int)$resolved['su_wr_id'] : 0;

    if ($bo_table === '' || $wr_id <= 0) {
        $target_url = isset($resolved['su_url']) ? html_entity_decode((string)$resolved['su_url'], ENT_QUOTES | ENT_HTML5, 'UTF-8') : '';
        $target_query = (string)parse_url($target_url, PHP_URL_QUERY);
        if ($target_query !== '') {
            $target_params = array();
            parse_str(str_replace('&amp;', '&', $target_query), $target_params);
            $bo_table = !empty($target_params['bo_table']) ? preg_replace('/[^a-zA-Z0-9_]/', '', (string)$target_params['bo_table']) : '';
            $wr_id = !empty($target_params['wr_id']) ? (int)$target_params['wr_id'] : 0;
        }
    }

    if ($bo_table === '' || $wr_id <= 0) return false;

    return render_internal_post_card($bo_table, $wr_id);
}

/**
 * URL 자동링크 변환
 *
 * @param string $text 변환할 텍스트
 * @param string $bo_table 게시판 ID (내부 링크 처리용)
 * @return string 변환된 텍스트
 */
function autolink($text, $bo_table = '') {
    // 0. <code>, <pre> 태그 내용을 임시로 보호
    $code_blocks = array();
    $code_index = 0;

    // <pre>...</pre> 먼저 보호 (중첩된 <code> 포함)
    $text = preg_replace_callback('/<pre[^>]*>.*?<\/pre>/is', function($matches) use (&$code_blocks, &$code_index) {
        $placeholder = "___CODE_BLOCK_{$code_index}___";
        $code_blocks[$placeholder] = $matches[0];
        $code_index++;
        return $placeholder;
    }, $text);

    // <code>...</code> 보호 (단독 인라인 코드용)
    $text = preg_replace_callback('/<code[^>]*>.*?<\/code>/is', function($matches) use (&$code_blocks, &$code_index) {
        $placeholder = "___CODE_BLOCK_{$code_index}___";
        $code_blocks[$placeholder] = $matches[0];
        $code_index++;
        return $placeholder;
    }, $text);

    // 기존 HTML 태그 보호 (img, a, iframe - 이미 변환된 태그 중복 변환 방지)
    $html_tags = array();
    $html_index = 0;

    // <img> 태그 보호
    $text = preg_replace_callback('/<img[^>]*>/is', function($matches) use (&$html_tags, &$html_index) {
        $placeholder = "___HTML_TAG_{$html_index}___";
        $html_tags[$placeholder] = $matches[0];
        $html_index++;
        return $placeholder;
    }, $text);

    // <a>...</a> 태그 보호
    $text = preg_replace_callback('/<a[^>]*>.*?<\/a>/is', function($matches) use (&$html_tags, &$html_index) {
        $placeholder = "___HTML_TAG_{$html_index}___";
        $html_tags[$placeholder] = $matches[0];
        $html_index++;
        return $placeholder;
    }, $text);

    // <iframe>...</iframe> 태그 보호
    $text = preg_replace_callback('/<iframe[^>]*>.*?<\/iframe>/is', function($matches) use (&$html_tags, &$html_index) {
        $placeholder = "___HTML_TAG_{$html_index}___";
        $html_tags[$placeholder] = $matches[0];
        $html_index++;
        return $placeholder;
    }, $text);

    // <video>...</video> 태그 보호
    $text = preg_replace_callback('/<video\b[^>]*>.*?<\/video>/is', function($matches) use (&$html_tags, &$html_index) {
        $placeholder = "___HTML_TAG_{$html_index}___";
        $html_tags[$placeholder] = $matches[0];
        $html_index++;
        return $placeholder;
    }, $text);

    $protect_html = function($html) use (&$html_tags, &$html_index) {
        $placeholder = "___HTML_TAG_{$html_index}___";
        $html_tags[$placeholder] = $html;
        $html_index++;
        return $placeholder;
    };

    // 1. 기존 비디오[URL] 문법 호환 처리
    $video_bracket_pattern = '/비디오\[(https?:\/\/[^\]]+)\]/i';
    $text = preg_replace_callback($video_bracket_pattern, function($matches) use ($protect_html) {
        $url = trim($matches[1]);

        if ($embed = render_external_embed($url)) return $protect_html($embed);
        return '비디오 링크 오류';
    }, $text);

    // 2. 이미지[URL] 패턴 처리
    $image_bracket_pattern = '/이미지\[(https?:\/\/[^\]]+)\]/i';
    $text = preg_replace_callback($image_bracket_pattern, function($matches) {
        $url = $matches[1];
        return '<img src="' . $url . '" class="auto_link_image" alt="image" style="max-width: 100%; height: auto;">';
    }, $text);

    // 2.5. 링크[URL] 패턴 처리 (OG 메타데이터 기반 링크 카드)
    $link_bracket_pattern = '/링크\[(https?:\/\/[^\]]+)\]/i';
    $text = preg_replace_callback($link_bracket_pattern, function($matches) {
        $url = trim($matches[1]);
        $og = fetch_og_metadata($url);
        if ($og) {
            return render_link_card($url, $og);
        }
        // OG 파싱 실패 시 일반 링크로 표시
        return '<a href="' . htmlspecialchars($url, ENT_QUOTES) . '" class="auto_link" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($url, ENT_QUOTES) . '</a>';
    }, $text);

    // 2.8. 허용된 외부 임베드 URL 처리 (https:// 없는 twitter.com/... 형태 포함)
    $external_embed_pattern = '#(?<![A-Za-z0-9_.-])(?:&lt;|<)?((?:https?://)?(?:(?:www\.|mobile\.)?(?:twitter\.com|x\.com)/(?:[A-Za-z0-9_]+/status(?:es)?|i/web/status)/\d+[^\s<>"\']*|(?:www\.|m\.|music\.)?(?:youtube\.com|youtube-nocookie\.com)/(?:watch\?[^\s<>"\']*v=[A-Za-z0-9_-]{11}[^\s<>"\']*|embed/[A-Za-z0-9_-]{11}[^\s<>"\']*|shorts/[A-Za-z0-9_-]{11}[^\s<>"\']*|live/[A-Za-z0-9_-]{11}[^\s<>"\']*)|youtu\.be/[A-Za-z0-9_-]{11}[^\s<>"\']*|(?:www\.)?vimeo\.com/(?:[^/\s<>"\']+/)*\d+[^\s<>"\']*|player\.vimeo\.com/video/\d+[^\s<>"\']*|open\.spotify\.com/(?:intl-[a-z]{2}/)?(?:track|album|playlist|artist|episode|show)/[A-Za-z0-9]+[^\s<>"\']*|(?:www\.|m\.)?soundcloud\.com/[^\s<>"\']+/[^\s<>"\']+|(?:www\.)?bsky\.app/profile/[^\s<>"\']+/post/[a-z0-9]+[^\s<>"\']*))(?:&gt;|>)?#i';
    $text = preg_replace_callback($external_embed_pattern, function($matches) use ($protect_html) {
        $embed = render_external_embed($matches[1]);
        if ($embed) return $protect_html($embed);
        $fallback = render_external_embed_fallback($matches[1]);
        return $fallback ? $protect_html($fallback) : $matches[0];
    }, $text);

    // 3. URL 패턴 (http, https) - 이미 HTML 태그 안에 있는 URL은 제외
    // Negative lookbehind: href=" 또는 src=" 뒤가 아닌 경우만
    $url_pattern = '/(?<!href=")(?<!src=")(https?:\/\/[^\s<>"\']+)/i';

    // URL을 자동 변환
    $text = preg_replace_callback($url_pattern, function($matches) use ($protect_html) {
        $url = $matches[1];

        // 짧은 내부 공유 URL → 미리보기 카드
        if (function_exists('render_internal_short_post_card')) {
            $card = render_internal_short_post_card($url);
            if ($card) return $card;
        }

        // 내부 게시글 URL → 미리보기 카드
        if (preg_match('/board\.php\?bo_table=([a-zA-Z0-9_]+)&(?:amp;)?wr_id=(\d+)/', $url, $bm)) {
            if (function_exists('render_internal_post_card')) {
                $card = render_internal_post_card($bm[1], (int)$bm[2]);
                if ($card) return $card;
            }
        }

        if ($embed = render_external_embed($url)) return $protect_html($embed);

        // 이미지 확장자 체크 → img 태그
        if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $url)) {
            return '<img src="' . $url . '" class="auto_link_image" alt="image" style="max-width: 100%; height: auto;">';
        }

        // 일반 URL → "URL" 텍스트로 링크 표시
        return '<a href="' . htmlspecialchars($url, ENT_QUOTES) . '" class="auto_link" target="_blank" rel="noopener noreferrer">URL</a>';
    }, $text);

    // 4. 해시태그 자동링크 (제일 마지막에 처리 - URL 안의 #과 충돌 방지)
    // HTML 태그 내부는 처리하지 않음 (style 속성 내 색상코드 보호)
    $parts = preg_split('/(<[^>]+>)/s', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as &$part) {
        // HTML 태그가 아닌 텍스트 부분만 처리
        if (strpos($part, '<') !== 0) {
            $hashtag_pattern = '/#([가-힣a-zA-Z][^\s#<>]*)/u';
            $part = preg_replace_callback($hashtag_pattern, function($matches) use ($bo_table) {
                $tag = $matches[1];
                // 태그에서 끝의 특수문자 제거 (;, !, ?, . 등)
                $clean_tag = preg_replace('/[;\!\?\.\,\)\]]+$/', '', $tag);
                // 16진수 색상코드(3자리, 4자리, 6자리, 8자리) 체크
                if (preg_match('/^[0-9a-fA-F]{3,8}$/', $clean_tag)) {
                    return '#' . $tag;
                }
                // 순수 숫자는 해시태그로 변환하지 않음
                if (preg_match('/^\d+/', $clean_tag)) {
                    return '#' . $tag;
                }
                $link = G5_BBS_URL . '/board.php?bo_table=' . $bo_table . '&hash=' . urlencode($tag);
                return '<a href="' . $link . '" class="hashtag">#' . $tag . '</a>';
            }, $part);
        }
    }
    unset($part);
    $text = implode('', $parts);

    // 4.5. @멘션 하이라이트
    $parts = preg_split('/(<[^>]+>)/s', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as &$part) {
        if (strpos($part, '<') !== 0) {
            $part = preg_replace_callback('/@([a-zA-Z0-9가-힣_]+)/u', function($matches) {
                $name = $matches[1];
                return '<span class="mention">@' . htmlspecialchars($name, ENT_QUOTES) . '</span>';
            }, $part);
        }
    }
    unset($part);
    $text = implode('', $parts);

    // 5. 보호했던 HTML 태그 복원 (img, a, iframe)
    foreach ($html_tags as $placeholder => $original) {
        $text = str_replace($placeholder, $original, $text);
    }

    // 6. 보호했던 <code>, <pre> 태그 복원
    foreach ($code_blocks as $placeholder => $original) {
        $text = str_replace($placeholder, $original, $text);
    }

    return $text;
}

/**
 * 해시태그 자동링크 변환
 *
 * @param string $text 변환할 텍스트
 * @param string $bo_table 게시판 ID
 * @return string 변환된 텍스트
 */
function autolink_hashtag($text, $bo_table) {
    // HTML 태그 내부는 처리하지 않음 (style 속성 내 색상코드 보호)
    $parts = preg_split('/(<[^>]+>)/s', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parts as &$part) {
        // HTML 태그가 아닌 텍스트 부분만 처리
        if (strpos($part, '<') !== 0) {
            $hashtag_pattern = '/#([가-힣a-zA-Z][^\s#<>]*)/u';
            $part = preg_replace_callback($hashtag_pattern, function($matches) use ($bo_table) {
                $tag = $matches[1];
                // 태그에서 끝의 특수문자 제거
                $clean_tag = preg_replace('/[;\!\?\.\,\)\]]+$/', '', $tag);
                // 16진수 색상코드(3자리, 4자리, 6자리, 8자리) 체크
                if (preg_match('/^[0-9a-fA-F]{3,8}$/', $clean_tag)) {
                    return '#' . $tag;
                }
                // 순수 숫자는 해시태그로 변환하지 않음
                if (preg_match('/^\d+/', $clean_tag)) {
                    return '#' . $tag;
                }
                $link = G5_BBS_URL . '/board.php?bo_table=' . $bo_table . '&hash=' . urlencode($tag);
                return '<a href="' . $link . '" class="hashtag">#' . $tag . '</a>';
            }, $part);
        }
    }
    unset($part);
    return implode('', $parts);
}

/**
 * 사용되지 않은 이미지/동영상 추출
 *
 * @param string $content 본문 내용
 * @param string $bo_table 게시판 ID
 * @param int $wr_id 글 번호
 * @return array 사용되지 않은 이미지/동영상 HTML 배열
 */
function get_unused_images($content, $bo_table, $wr_id) {
    global $g5;

    // 1. 모든 첨부 미디어 파일 가져오기 (bf_content >= 0)
    $all_media = array();
    $sql = "SELECT bf_no, bf_file, bf_source, bf_content
            FROM {$g5['board_file_table']}
            WHERE bo_table = '{$bo_table}'
            AND wr_id = '{$wr_id}'
            AND bf_content >= 0
            ORDER BY CAST(bf_content AS SIGNED) ASC";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        $all_media[] = array(
            'file' => $row['bf_file'],
            'source' => $row['bf_source'],
            'content' => (int)$row['bf_content']
        );
    }

    // 2. 본문에서 사용된 플레이스홀더 추출
    preg_match_all('/\{이미지:(\d+)(?:-\d+)?\}/', $content, $image_matches);
    preg_match_all('/\{비디오:(\d+)(?:-\d+)?\}/u', $content, $video_matches);
    $used_image_indices = array_map('intval', $image_matches[1]);
    $used_video_indices = array_map('intval', $video_matches[1]);

    // 3. 사용되지 않은 이미지/동영상 찾기
    $unused_images = array();
    $video_index = 0;
    foreach ($all_media as $media) {
        $file_path = G5_DATA_URL . '/file/' . $bo_table . '/' . $media['file'];

        if (is_image_file($media['file'])) {
            if (!in_array($media['content'], $used_image_indices)) {
                $unused_images[] = "<img src='" . $file_path . "'
                                          alt='" . htmlspecialchars($media['source'], ENT_QUOTES) . "'
                                          class='unused-image'
                                          style='max-width: 100%; height: auto;'>";
            }
        } elseif (is_video_file($media['file'])) {
            if (!in_array($video_index, $used_video_indices)) {
                $unused_images[] = render_uploaded_video($file_path, $media['source'], 'unused-video');
            }
            $video_index++;
        }
    }

    return $unused_images;
}

/**
 * 본문 처리 및 사용되지 않은 이미지 추출
 *
 * @param string $content 본문 내용
 * @param string $bo_table 게시판 ID
 * @param int $wr_id 글 번호
 * @param int $bo_use_dhtml_editor HTML 에디터 사용 여부
 * @param array &$unused_images 사용되지 않은 이미지/동영상 HTML 배열 (참조)
 * @return string 처리된 본문
 */
function process_content_with_unused_images($content, $bo_table, $wr_id, $bo_use_dhtml_editor, &$unused_images) {
    // 1. 사용되지 않은 이미지/동영상 추출 (플레이스홀더 변환 전)
    $unused_images = get_unused_images($content, $bo_table, $wr_id);

    // 2. 플레이스홀더를 실제 이미지/동영상으로 변환
    $content = convert_placeholders_to_images($content, $bo_table, $wr_id);
    $content = convert_placeholders_to_videos($content, $bo_table, $wr_id);

    // 3. conv_content() 호출 (HTML 정리, XSS 방지 등)
    $content = conv_content($content, $bo_use_dhtml_editor);

    // 4. autolink() 호출 (URL 자동 링크, 해시태그, 이미지[URL], 비디오[URL], 외부 콘텐츠 임베드)
    $content = autolink($content, $bo_table);

    // 5. 아이템 태그 변환 ([item:이름] → 인라인 뱃지)
    $content = render_item_tags($content);

    // 6. 이모티콘 변환 (/이름 → 이미지)
    if (function_exists('convert_emoticon')) {
        $content = convert_emoticon($content);
    }

    // 7. 출력 텍스트 후처리 ([]/[x] 등)
    $content = format_output_text($content);

    return $content;
}

/**
 * [item:아이템명] 문법을 인라인 아이템 뱃지로 변환
 */
function render_item_tags($content) {
    if (strpos($content, '[item:') === false) return $content;

    static $cache = array();

    return preg_replace_callback('/\[item:([^\]]+)\]/', function($matches) use (&$cache) {
        $item_name = trim($matches[1]);

        if (isset($cache[$item_name])) {
            $item = $cache[$item_name];
        } else {
            $safe = sql_real_escape_string($item_name);
            $item = sql_fetch("SELECT it_id, it_name, it_img, it_rarity, it_description FROM " . G5_TABLE_PREFIX . "community_item WHERE it_name = '{$safe}' AND it_use = 1 LIMIT 1");
            $cache[$item_name] = $item;
        }

        if (!$item || !$item['it_id']) {
            return '<span class="item-tag item-tag-unknown">' . htmlspecialchars($item_name) . '</span>';
        }

        $rarity = $item['it_rarity'] ?: 'common';
        $name = htmlspecialchars($item['it_name']);
        $desc = htmlspecialchars($item['it_description'] ?: '');
        $img_html = '';
        if ($item['it_img']) {
            $img_url = G5_DATA_URL . '/community/item/' . $item['it_img'];
            $img_html = '<img src="' . $img_url . '" alt="" class="item-tag-icon">';
        }

        return '<span class="item-tag rarity_' . $rarity . '" data-item-desc="' . $desc . '">' . $img_html . $name . '</span>';
    }, $content);
}

/**
 * [item:아이템명] 문법에서 이름만 추출 (프리뷰용)
 */
function strip_item_tags($text) {
    return preg_replace('/\[item:([^\]]+)\]/', '$1', $text);
}

// =====================================================
// 자동 초기화
// =====================================================

// 좋아요 테이블 자동 생성
init_like_table();

// =====================================================
// 파일 처리 시스템
// =====================================================

/**
 * 파일명 안전화 함수
 *
 * 업로드되는 파일명에서 위험한 문자를 제거하고 안전한 파일명으로 변환합니다.
 * - UTF-8 문자를 ASCII로 변환
 * - 특수문자 제거 (영문, 숫자, ., _, - 만 허용)
 * - 연속된 언더스코어 제거
 *
 * @param string $filename 원본 파일명
 * @return string 안전화된 파일명
 */
function sanitize_filename($filename) {
    $filename = basename($filename);
    $filename = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $filename);
    $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);
    $filename = preg_replace('/_+/', '_', $filename);
    return $filename;
}

/**
 * 읽기 시: {이미지:N} 플레이스홀더를 실제 <img> 태그로 변환
 * bf_content 기반으로 정확한 이미지 매칭
 *
 * @param string $content 본문 내용
 * @param string $bo_table 게시판 테이블명
 * @param int $wr_id 게시글 ID
 * @return string 변환된 본문
 */
function convert_placeholders_to_images($content, $bo_table, $wr_id) {
    global $g5;

    // bf_file 테이블에서 파일 정보 가져오기
    $sql = "SELECT bf_no, bf_file, bf_source, bf_content
            FROM {$g5['board_file_table']}
            WHERE bo_table = '{$bo_table}'
            AND wr_id = '{$wr_id}'
            AND bf_content >= 0
            ORDER BY bf_content ASC";
    $result = sql_query($sql);

    $image_files = array();
    while ($row = sql_fetch_array($result)) {
        $index = (int)$row['bf_content'];
        $file_path = G5_DATA_URL . '/file/' . $bo_table . '/' . $row['bf_file'];

        if (is_image_file($row['bf_file'])) {
            $image_files[$index] = "<img src='" . $file_path . "'
                                          alt='" . htmlspecialchars($row['bf_source'], ENT_QUOTES) . "'
                                          class='content-image'
                                          style='max-width: 100%; width: fit-content; height: auto;'>";
        }
    }

    // 일반 이미지 처리 ({이미지:0} 또는 {이미지:0-500} 크기 지정)
    $content = preg_replace_callback('/\{이미지:(\d+)(?:-(\d+))?\}/', function($matches) use ($image_files) {
        $index = (int)$matches[1];
        $width = isset($matches[2]) ? (int)$matches[2] : null;

        if (!isset($image_files[$index])) {
            return $matches[0]; // 이미지 없으면 원본 그대로
        }

        if ($width) {
            $img = $image_files[$index];
            // style='max-width: 100%; ...' -> style='max-width: {width}px; ...'
            $img = str_replace("style='max-width: 100%;", "style='max-width: {$width}px;", $img);
            return $img;
        }

        return $image_files[$index];
    }, $content);

    // HTML span 플레이스홀더 처리 (<span class="image-placeholder" data-index="N" data-width="">이미지:N</span>)
    $content = preg_replace_callback('/<span[^>]*class=["\']image-placeholder["\'][^>]*data-index=["\'](\d+)["\'][^>]*(?:data-width=["\'](\d*)["\'])?[^>]*>[^<]*<\/span>/i', function($matches) use ($image_files) {
        $index = (int)$matches[1];
        $width = isset($matches[2]) && $matches[2] !== '' ? (int)$matches[2] : null;

        if (!isset($image_files[$index])) {
            return $matches[0]; // 이미지 없으면 원본 그대로
        }

        if ($width) {
            $img = $image_files[$index];
            $img = str_replace("style='max-width: 100%;", "style='max-width: {$width}px;", $img);
            return $img;
        }

        return $image_files[$index];
    }, $content);

    // 타일형 처리 ({tile}...{/tile})
    $content = preg_replace_callback('/\{tile\}(.*?)\{\/tile\}/s', function($matches) {
        $inner_content = $matches[1];

        // 내부의 모든 <img> 태그 찾기
        preg_match_all('/<img[^>]*>/i', $inner_content, $img_matches);

        if (empty($img_matches[0])) {
            // 이미지가 없으면 원본 그대로 반환
            return $inner_content;
        }

        $tile_images = $img_matches[0];

        $html = '<div class="tile-grid">';
        foreach ($tile_images as $img_html) {
            // tile-item 컨테이너로 감싸기 (클릭 이벤트 유지)
            $html .= '<div class="tile-item">' . $img_html . '</div>';
        }
        $html .= '</div>';

        return $html;
    }, $content);

    return $content;
}

/**
 * 읽기 시: {비디오:N} 플레이스홀더를 N번째 첨부 동영상으로 변환
 *
 * @param string $content 본문 내용
 * @param string $bo_table 게시판 테이블명
 * @param int $wr_id 게시글 ID
 * @return string 변환된 본문
 */
function convert_placeholders_to_videos($content, $bo_table, $wr_id) {
    $videos = get_uploaded_videos($bo_table, $wr_id);
    if (empty($videos)) return $content;

    return preg_replace_callback('/\{비디오:(\d+)(?:-(\d+))?\}/u', function($matches) use ($videos) {
        $index = (int)$matches[1];
        $width = isset($matches[2]) ? (int)$matches[2] : 0;

        if (!isset($videos[$index])) {
            return $matches[0];
        }

        return render_uploaded_video($videos[$index]['url'], $videos[$index]['source'], 'content-video', $width);
    }, $content);
}

/**
 * 첨부파일에서 첫 번째 이미지 URL 가져오기
 *
 * @param string $bo_table 게시판 테이블명
 * @param int $wr_id 게시글 ID
 * @return string 이미지 URL (없으면 빈 문자열)
 */
if (!function_exists('getFirstImageFromPost')) {
    function getFirstImageFromPost($bo_table, $wr_id) {
        global $g5;

        // 1. 첨부파일에서 첫 번째 이미지 찾기 (bf_content >= 0)
        $file_sql = "SELECT bf_file FROM " . $g5['board_file_table'] . "
                     WHERE bo_table = '{$bo_table}' AND wr_id = '{$wr_id}'
                     AND bf_content >= 0
                     AND bf_type IN (1, 2, 3, 18)
                     ORDER BY bf_no LIMIT 1";
        $file = sql_fetch($file_sql);

        if ($file && $file['bf_file']) {
            return G5_DATA_URL . '/file/' . $bo_table . '/' . $file['bf_file'];
        }

        return '';
    }
}

/**
 * 썸네일 URL 가져오기 (우선순위: wr_img > 첫번째 이미지)
 *
 * @param string $bo_table 게시판 테이블명
 * @param array $post 게시글 배열 (wr_img, wr_id 포함)
 * @return string 썸네일 URL
 */
if (!function_exists('getThumbnailUrl')) {
    function getThumbnailUrl($bo_table, $post) {
        if (!empty($post['wr_img'])) {
            // wr_img 경로 처리
            if (strpos($post['wr_img'], 'http') === 0) {
                return $post['wr_img'];
            } else {
                return G5_DATA_URL . '/file/' . $bo_table . '/' . $post['wr_img'];
            }
        }

        return getFirstImageFromPost($bo_table, $post['wr_id']);
    }
}

// =====================================================
// 이미지 최적화 (리사이즈 + WebP 변환)
// =====================================================

/**
 * 이미지 최적화: 가로 리사이즈 + 중앙 크롭(옵션) + WebP 90 변환
 * GIF는 애니메이션 보존을 위해 변환하지 않음
 *
 * @param string $src_path 원본 파일 경로
 * @param int    $max_width 최대 가로 (0이면 리사이즈 안 함)
 * @param array  $options   추가 옵션
 *   - crop      (bool) 중앙 정사각 크롭 여부 (기본 false)
 *   - crop_size (int)  크롭 크기 (crop=true일 때)
 *   - quality   (int)  WebP 품질 (기본 90)
 * @return string|false 변환된 파일 경로 또는 실패
 */
function optimize_uploaded_image($src_path, $max_width = 0, $options = array()) {
    $info = @getimagesize($src_path);
    if (!$info) return false;

    $orig_w = $info[0];
    $orig_h = $info[1];
    $mime   = $info['mime'];

    // GIF는 애니메이션 보존을 위해 변환하지 않음
    if ($mime === 'image/gif') return false;

    switch ($mime) {
        case 'image/jpeg': $src = @imagecreatefromjpeg($src_path); break;
        case 'image/png':  $src = @imagecreatefrompng($src_path);  break;
        case 'image/webp': $src = @imagecreatefromwebp($src_path); break;
        default: return false;
    }
    if (!$src) return false;

    $do_crop   = !empty($options['crop']);
    $crop_size = isset($options['crop_size']) ? (int)$options['crop_size'] : 0;
    $quality   = isset($options['quality'])   ? (int)$options['quality']   : 90;

    // 1단계: 리사이즈 (가로 기준, 비율 유지)
    $new_w = $orig_w;
    $new_h = $orig_h;
    if ($max_width > 0 && $orig_w > $max_width) {
        $ratio = $max_width / $orig_w;
        $new_w = $max_width;
        $new_h = (int)round($orig_h * $ratio);

        $resized = imagecreatetruecolor($new_w, $new_h);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $src, 0, 0, 0, 0, $new_w, $new_h, $orig_w, $orig_h);
        imagedestroy($src);
        $src = $resized;
    }

    // 2단계: 중앙 크롭 (옵션)
    if ($do_crop && $crop_size > 0) {
        $cx = max(0, (int)floor(($new_w - $crop_size) / 2));
        $cy = max(0, (int)floor(($new_h - $crop_size) / 2));
        $cw = min($crop_size, $new_w);
        $ch = min($crop_size, $new_h);

        $cropped = imagecreatetruecolor($cw, $ch);
        imagealphablending($cropped, false);
        imagesavealpha($cropped, true);
        imagecopyresampled($cropped, $src, 0, 0, $cx, $cy, $cw, $ch, $cw, $ch);
        imagedestroy($src);
        $src = $cropped;
    }

    // 3단계: WebP 변환
    $webp_path = preg_replace('/\.[^.]+$/', '.webp', $src_path);
    $result = imagewebp($src, $webp_path, $quality);
    imagedestroy($src);

    if (!$result) return false;

    // 원본이 webp가 아니면 원본 삭제
    if (realpath($src_path) !== realpath($webp_path)) {
        @unlink($src_path);
    }

    @chmod($webp_path, G5_FILE_PERMISSION);
    return $webp_path;
}

