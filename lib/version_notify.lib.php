<?php
/**
 * 라공 생태계 버전 알림 — 공용 체크 라이브러리
 *
 * ra0.kr의 중앙 매니페스트(version.json)를 일 1회 받아와
 * 에디션 본체 / 확장팩(KIT) / 게시판 스킨 / 웹 클리퍼의 새 버전을 판정한다.
 *
 * 원칙 (설계안 §3·§8):
 * - 원격 호출은 관리자(adm) 요청 + 캐시 만료일 때만. 프런트 요청에서는 절대 호출하지 않는다.
 * - 캐시는 fetch_og_metadata와 같은 원시 파일 캐시(G5_USE_CACHE 무관):
 *   성공 86400초(일 1회), 실패 {"fail":1} 네거티브 캐시 3600초 (ra0.kr 다운 시 재시도 폭주 방지).
 * - 외부로 보내는 정보 없음 — 고정 URL bare GET뿐. TLS 검증 항상 켬.
 * - data/cache 쓰기 불가 호스팅에서는 원격 호출 자체를 포기하고 조용히 비활성화된다
 *   (캐시 없이는 매 요청 원격 호출 폭주를 막을 수 없으므로).
 *
 * @package RA0Edition
 * @since 1.7.2
 */

if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (!defined('RA0_UPDATE_MANIFEST_URL')) {
    define('RA0_UPDATE_MANIFEST_URL', 'https://ra0.kr/version.json');
}

/**
 * 매니페스트 캐시 파일 경로
 */
function ra0_update_cache_file()
{
    return G5_DATA_PATH . '/cache/ra0_update.json';
}

/**
 * 중앙 매니페스트 조회 (캐시 우선)
 *
 * @param bool $allow_remote true면 캐시 만료 시 원격 갱신 허용 (관리자 요청에서만 true로 호출할 것)
 * @return array|false 매니페스트 배열 또는 false (미수신/실패 캐시)
 */
function ra0_update_manifest($allow_remote = false)
{
    static $memo = null;
    if ($memo !== null) return $memo;

    $cache_dir  = G5_DATA_PATH . '/cache';
    $cache_file = ra0_update_cache_file();

    $stale_data = false; // 만료됐지만 마지막으로 성공한 매니페스트 (원격 불가 시 폴백)

    if (file_exists($cache_file)) {
        $cache_age = time() - filemtime($cache_file);
        $cached = json_decode(@file_get_contents($cache_file), true);
        if (is_array($cached)) {
            if (!empty($cached['fail'])) {
                // 실패 네거티브 캐시 — 1시간 동안 재시도하지 않음
                if ($cache_age < 3600) {
                    $memo = false;
                    return false;
                }
            } elseif ($cache_age < 86400) {
                $memo = $cached;
                return $cached;
            } else {
                $stale_data = $cached;
            }
        }
    }

    if (!$allow_remote) {
        // 원격 금지 경로(프런트/클리퍼 중계)에서는 스테일이라도 마지막 성공 데이터를 쓴다
        $memo = $stale_data;
        return $memo;
    }

    // 캐시 디렉터리 준비 — 쓰기 불가면 원격 호출 포기 (조용히 비활성)
    if (!is_dir($cache_dir)) {
        @mkdir($cache_dir, G5_DIR_PERMISSION, true);
    }
    if (!is_dir($cache_dir) || !is_writable($cache_dir)) {
        $memo = $stale_data;
        return $memo;
    }

    $data = ra0_update_fetch_manifest();
    if ($data === false) {
        @file_put_contents($cache_file, json_encode(array('fail' => 1)));
        $memo = false;
        return false;
    }

    @file_put_contents($cache_file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    $memo = $data;
    return $data;
}

/**
 * 매니페스트 원격 수신 — 타임아웃 3초, TLS 검증 켬
 *
 * curl 우선: 공유호스팅(닷홈 등)에서 allow_url_fopen 꺼짐·CA 번들 문제로
 * file_get_contents의 HTTPS가 막히는 환경이 실재한다 (sra0 실서버 확인, 2026-06-12).
 * feed.lib.php의 curl 우선 관례를 따르되 TLS 검증은 끄지 않는다.
 * curl 확장이 없을 때만 file_get_contents 사용 — 어느 쪽이든 시간 상한 3초.
 *
 * @return array|false
 */
function ra0_update_fetch_manifest()
{
    $ua = 'RA0Edition/' . (defined('G5_GNUBOARD_VER') ? G5_GNUBOARD_VER : '0');

    $body = false;
    $status = 0;

    if (function_exists('curl_init')) {
        $ch = curl_init(RA0_UPDATE_MANIFEST_URL);
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => 3,
            CURLOPT_USERAGENT      => $ua,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ));
        $body = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    } else {
        $context = stream_context_create(array(
            'http' => array(
                'timeout'       => 3,
                'user_agent'    => $ua,
                'ignore_errors' => true,
            ),
            'ssl' => array(
                'verify_peer'      => true,
                'verify_peer_name' => true,
            ),
        ));
        $http_response_header = null;
        $body = @file_get_contents(RA0_UPDATE_MANIFEST_URL, false, $context);
        foreach ((array)$http_response_header as $h) {
            if (preg_match('/^HTTP\/[\d.]+\s+(\d+)/', $h, $m)) $status = (int)$m[1];
        }
    }

    if ($body === false || $status < 200 || $status >= 300) return false;

    $data = json_decode($body, true);
    if (!is_array($data) || empty($data['schema'])) return false;

    return $data;
}

/**
 * 에디션 본체 새 버전 판정 (semver)
 *
 * @return array|false 구버전이면 정보 배열, 최신이면 false
 */
function ra0_update_check_edition($manifest)
{
    if (empty($manifest['edition']['latest']) || !defined('G5_GNUBOARD_VER')) return false;

    $latest = (string)$manifest['edition']['latest'];
    if (!version_compare(G5_GNUBOARD_VER, $latest, '<')) return false;

    return array(
        'current' => G5_GNUBOARD_VER,
        'latest'  => $latest,
        'url'     => isset($manifest['edition']['url']) ? (string)$manifest['edition']['url'] : '',
        'notice'  => isset($manifest['edition']['notice']) ? (string)$manifest['edition']['notice'] : '',
    );
}

/**
 * 확장팩(Community_KIT) 새 버전 판정
 *
 * 판정 매트릭스 (설계안 §6 — 오탐 불가능):
 * - KIT 미설치(테이블 없음)            → false
 * - 설치 + RA0_KIT_VERSION 정의       → YYMMDD 비교, 구버전이면 status=outdated
 * - 설치 + 상수 미정의(버전 파일 없음) → 확정 구버전, status=unknown
 *
 * @return array|false
 */
function ra0_update_check_kit($manifest)
{
    if (empty($manifest['kit']['latest'])) return false;
    if (!function_exists('is_community_installed') || !is_community_installed()) return false;

    $info = array(
        'latest' => (string)$manifest['kit']['latest'],
        'url'    => isset($manifest['kit']['url']) ? (string)$manifest['kit']['url'] : '',
        'notice' => isset($manifest['kit']['notice']) ? (string)$manifest['kit']['notice'] : '',
    );

    if (!defined('RA0_KIT_VERSION')) {
        // 버전 표기 도입(260611) 이전 구버전 확정
        $info['status']  = 'unknown';
        $info['current'] = '';
        return $info;
    }

    if ((int)preg_replace('/\D/', '', $info['latest']) > (int)preg_replace('/\D/', '', RA0_KIT_VERSION)) {
        $info['status']  = 'outdated';
        $info['current'] = RA0_KIT_VERSION;
        return $info;
    }

    return false;
}

/**
 * 웹 클리퍼 서버 플러그인 새 버전 판정 (semver)
 *
 * 클리퍼 lib는 extend가 아니어서 adm 요청에 로드되지 않으므로
 * 파일 머리에서 버전 상수를 토큰 파싱한다.
 *
 * @return array|false 미설치/버전 표기 없음(조용히 스킵)/최신이면 false
 */
function ra0_update_check_clipper($manifest)
{
    if (empty($manifest['web_clipper']['server'])) return false;

    $lib_file = G5_LIB_PATH . '/clipper.lib.php';
    if (!file_exists($lib_file)) return false;

    $current = '';
    if (defined('RA0_CLIPPER_SERVER_VERSION')) {
        $current = RA0_CLIPPER_SERVER_VERSION;
    } else {
        $head = @file_get_contents($lib_file, false, null, 0, 8192);
        if ($head && preg_match('/define\(\s*[\'"]RA0_CLIPPER_SERVER_VERSION[\'"]\s*,\s*[\'"]([0-9][^\'"]*)[\'"]/', $head, $m)) {
            $current = $m[1];
        }
    }
    if ($current === '') return false;

    $latest = (string)$manifest['web_clipper']['server'];
    if (!version_compare($current, $latest, '<')) return false;

    return array(
        'current' => $current,
        'latest'  => $latest,
        'url'     => isset($manifest['web_clipper']['url']) ? (string)$manifest['web_clipper']['url'] : '',
    );
}

/**
 * 사용 중인 게시판 스킨들의 새 버전 판정 (스캔 결과는 일 1회 캐시)
 *
 * @return array 구버전 스킨 목록 (없으면 빈 배열)
 */
function ra0_update_check_skins($manifest)
{
    if (empty($manifest['skins']) || !is_array($manifest['skins'])) return array();

    $cache_file    = G5_DATA_PATH . '/cache/ra0_update_skins.json';
    $manifest_file = ra0_update_cache_file();

    if (file_exists($cache_file)) {
        $fresh = (time() - filemtime($cache_file)) < 86400;
        // 매니페스트가 더 새로 수신됐으면 재스캔
        if ($fresh && file_exists($manifest_file) && filemtime($manifest_file) > filemtime($cache_file)) {
            $fresh = false;
        }
        if ($fresh) {
            $cached = json_decode(@file_get_contents($cache_file), true);
            if (is_array($cached) && isset($cached['outdated']) && is_array($cached['outdated'])) {
                return $cached['outdated'];
            }
        }
    }

    $outdated = ra0_update_scan_skins($manifest['skins']);
    @file_put_contents($cache_file, json_encode(array('outdated' => $outdated), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    return $outdated;
}

/**
 * 실제 스킨 스캔 — bo_skin에 사용 중인 스킨만 (설계안 §5 판정 순서)
 *
 * @param array $manifest_skins 매니페스트 skins 맵 ($upskin_id → {latest, url})
 * @return array
 */
function ra0_update_scan_skins($manifest_skins)
{
    global $g5;

    $outdated = array();
    if (!function_exists('sql_query') || empty($g5['board_table'])) return $outdated;

    $result = sql_query("SELECT DISTINCT bo_skin FROM {$g5['board_table']}", false);
    if (!$result) return $outdated;

    $seen = array();
    while ($row = sql_fetch_array($result)) {
        $bo_skin = isset($row['bo_skin']) ? trim($row['bo_skin']) : '';
        if ($bo_skin === '' || isset($seen[$bo_skin])) continue;
        $seen[$bo_skin] = true;
        if (!preg_match('/^[A-Za-z0-9_.-]+$/', $bo_skin)) continue; // 경로형 스킨값 방어

        $skin_dir = G5_SKIN_PATH . '/board/' . $bo_skin;
        if (!is_dir($skin_dir)) continue;

        $info = ra0_update_read_skin_info($skin_dir, $bo_skin);
        if ($info['version'] === '') continue;            // 판정 불가 — 조용히 제외
        if (!isset($manifest_skins[$info['id']])) continue; // 매니페스트에 없는 스킨 — 식별 불가, 스킵

        $entry  = $manifest_skins[$info['id']];
        $latest_raw = isset($entry['latest']) ? (string)$entry['latest'] : '';
        $cur    = ra0_update_skin_ver_parse($info['version']);
        $latest = ra0_update_skin_ver_parse($latest_raw);
        if (!$cur || !$latest) continue;

        if ($latest[0] > $cur[0] || ($latest[0] === $cur[0] && $latest[1] > $cur[1])) {
            $outdated[] = array(
                'skin'    => $bo_skin,
                'id'      => $info['id'],
                'current' => $info['version'],
                'latest'  => $latest_raw,
                'url'     => isset($entry['url']) ? (string)$entry['url'] : '',
            );
        }
    }

    return $outdated;
}

/**
 * 스킨 1개의 식별자·버전 판독
 *
 * 판정 순서: ① $upskin_version 표준 변수 ② $xxx_version 변형 흡수
 * ③ 폴더명 끝 날짜 폴백 (운영자들이 폴더명 날짜를 지우는 경우가 많아 최후 순위)
 *
 * @return array {id: string, version: string('' = 판정 불가)}
 */
function ra0_update_read_skin_info($skin_dir, $bo_skin)
{
    $id = '';
    $version = '';
    $variant = '';

    $files = @glob($skin_dir . '/*.php');
    foreach ((array)$files as $file) {
        $size = @filesize($file);
        if (!$size || $size > 65536) continue; // 64KB 초과 제외
        $src = @file_get_contents($file);
        if (!$src) continue;

        if ($id === '' && preg_match('/\$upskin_id\s*=\s*[\'"]([A-Za-z0-9_-]+)[\'"]/', $src, $m)) {
            $id = $m[1];
        }
        if ($version === '' && preg_match('/\$upskin_version\s*=\s*[\'"](v?\d{6}(?:_\d+)?)[\'"]/', $src, $m)) {
            $version = $m[1];
        }
        if ($version === '' && $variant === '' && preg_match('/\$[A-Za-z_]\w*_version\s*=\s*[\'"](v?\d{6}(?:_\d+)?)[\'"]/', $src, $m)) {
            $variant = $m[1]; // 변형 변수 — 표준이 끝내 없으면 사용
        }
        if ($version !== '' && $id !== '') break; // 첫 매치에서 중단
    }

    if ($version === '' && $variant !== '') $version = $variant;
    if ($version === '' && preg_match('/_(\d{6}(?:_\d+)?)$/', $bo_skin, $m)) $version = $m[1];
    if ($id === '') $id = preg_replace('/_\d{6}(?:_\d+)?$/', '', $bo_skin);

    return array('id' => $id, 'version' => $version);
}

/**
 * 스킨 버전 문자열 파싱 — 'v260609_0' / '260609' → array(날짜, 리비전)
 *
 * @return array|false
 */
function ra0_update_skin_ver_parse($ver)
{
    if (!preg_match('/^v?(\d{6})(?:_(\d+))?$/', (string)$ver, $m)) return false;
    return array((int)$m[1], isset($m[2]) ? (int)$m[2] : 0);
}

/**
 * 클리퍼 중계용 — 캐시된 매니페스트의 web_clipper 항목만 읽는다
 *
 * 클리퍼 API 요청 경로에서 호출되므로 원격 호출은 절대 하지 않는다.
 * 캐시가 만료됐어도 마지막 성공 데이터를 반환한다 (관리자 방문 시 갱신됨).
 *
 * @return array|false {server, extension, url} 또는 false
 */
function ra0_update_latest_clipper()
{
    $cache_file = ra0_update_cache_file();
    if (!file_exists($cache_file)) return false;

    $data = json_decode(@file_get_contents($cache_file), true);
    if (!is_array($data) || !empty($data['fail']) || empty($data['web_clipper'])) return false;

    return $data['web_clipper'];
}

/**
 * 매니페스트 버전 조합 해시 — dismiss 쿠키 키 (새 버전이 나오면 값이 달라져 자동 재노출)
 */
function ra0_update_version_hash($manifest)
{
    $parts = array(
        isset($manifest['edition']['latest'])        ? (string)$manifest['edition']['latest']        : '',
        isset($manifest['web_clipper']['server'])    ? (string)$manifest['web_clipper']['server']    : '',
        isset($manifest['web_clipper']['extension']) ? (string)$manifest['web_clipper']['extension'] : '',
        isset($manifest['kit']['latest'])            ? (string)$manifest['kit']['latest']            : '',
    );
    if (!empty($manifest['skins']) && is_array($manifest['skins'])) {
        $skins = $manifest['skins'];
        ksort($skins);
        foreach ($skins as $sid => $s) {
            $parts[] = $sid . ':' . (isset($s['latest']) ? (string)$s['latest'] : '');
        }
    }
    return md5(implode('|', $parts));
}

/**
 * 종합 판정 — 관리자 배너가 쓰는 단일 진입점 (요청 내 1회만 계산)
 *
 * @param bool $allow_remote admin_common 훅에서만 true
 * @return array {items: {edition?, kit?, clipper?, skins?}, hash: string} — 매니페스트 미수신이면 items 빈 배열
 */
function ra0_update_summary($allow_remote = false)
{
    static $memo = null;
    if ($memo !== null) return $memo;

    $manifest = ra0_update_manifest($allow_remote);
    if (!$manifest) {
        $memo = array('items' => array(), 'hash' => '');
        return $memo;
    }

    $items = array();
    if ($edition = ra0_update_check_edition($manifest)) $items['edition'] = $edition;
    if ($kit     = ra0_update_check_kit($manifest))     $items['kit'] = $kit;
    if ($clipper = ra0_update_check_clipper($manifest)) $items['clipper'] = $clipper;
    $skins = ra0_update_check_skins($manifest);
    if ($skins) $items['skins'] = $skins;

    $memo = array('items' => $items, 'hash' => ra0_update_version_hash($manifest));
    return $memo;
}
