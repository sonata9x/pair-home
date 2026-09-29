<?php
/**
 * 라공 생태계 버전 알림 — 관리자 배너 (설계안 §4)
 *
 * 관리자(adm) 화면 상단에 에디션/확장팩/스킨/웹 클리퍼의 새 버전 소식을 띄운다.
 * 체크는 admin_common 훅(HTML 출력 전), 배너는 tail_sub 훅(모든 adm 페이지 하단 발화).
 * 원격 호출은 lib/version_notify.lib.php가 일 1회 캐시로 관리한다.
 *
 * @package RA0Edition
 * @since 1.7.2
 */

if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 관리자 영역의 관리자에게만 — 프런트 요청은 여기서 즉시 탈출 (추가 비용 0)
if (!defined('G5_IS_ADMIN') || empty($is_admin)) return;

include_once(G5_LIB_PATH . '/version_notify.lib.php');

/**
 * 버전 체크 실행 — 캐시 만료 시에만 원격 호출(일 1회), HTML 출력 전 수행
 */
function ra0_update_notice_check()
{
    global $is_admin;
    if (!defined('G5_IS_ADMIN') || empty($is_admin)) return;

    ra0_update_summary(true);
}
add_event('admin_common', 'ra0_update_notice_check');

/**
 * 배너 출력 — fixed 상단 바, 항목별 받기 링크 + 닫기(dismiss 쿠키 30일)
 */
function ra0_update_notice_banner()
{
    global $is_admin;
    if (!defined('G5_IS_ADMIN') || empty($is_admin)) return;

    $summary = ra0_update_summary(false); // admin_common에서 계산된 결과 재사용
    if (empty($summary['items']) || $summary['hash'] === '') return;

    // dismiss — 같은 버전 조합이면 억제, 새 버전이 나오면 해시가 달라져 자동 재노출
    $hash = $summary['hash'];
    if (isset($_COOKIE['ra0_update_dismiss']) && $_COOKIE['ra0_update_dismiss'] === $hash) return;

    $items = $summary['items'];
    $lines = array();

    if (isset($items['edition'])) {
        $e = $items['edition'];
        $text = '라공 에디션 <strong>' . htmlspecialchars($e['latest']) . '</strong> 출시'
              . ' (현재 ' . htmlspecialchars($e['current']) . ')';
        if ($e['notice'] !== '') $text .= ' — ' . htmlspecialchars($e['notice']);
        $lines[] = array('text' => $text, 'url' => $e['url']);
    }

    if (isset($items['kit'])) {
        $k = $items['kit'];
        if ($k['status'] === 'unknown') {
            $text = '커뮤니티 확장팩 — 버전 확인 불가. 최신 확장팩부터 버전 표기가 도입되었습니다. 업데이트를 권장합니다';
        } else {
            $text = '커뮤니티 확장팩 <strong>' . htmlspecialchars($k['latest']) . '</strong> 배포'
                  . ' (현재 ' . htmlspecialchars($k['current']) . ')';
            if ($k['notice'] !== '') $text .= ' — ' . htmlspecialchars($k['notice']);
        }
        $lines[] = array('text' => $text, 'url' => $k['url']);
    }

    if (isset($items['clipper'])) {
        $c = $items['clipper'];
        $text = '웹 클리퍼 서버 플러그인 <strong>' . htmlspecialchars($c['latest']) . '</strong> 출시'
              . ' (현재 ' . htmlspecialchars($c['current']) . ')';
        $lines[] = array('text' => $text, 'url' => $c['url']);
    }

    if (isset($items['skins'])) {
        foreach ($items['skins'] as $s) {
            $text = '스킨 <strong>' . htmlspecialchars($s['id']) . '</strong> '
                  . htmlspecialchars($s['latest']) . ' 배포 (현재 ' . htmlspecialchars($s['current']) . ')';
            $lines[] = array('text' => $text, 'url' => $s['url']);
        }
    }

    if (!$lines) return;

    // 스타일은 인라인으로 동봉 — css/admin_extend_*.css 자동 로드 글롭은
    // G5_CSS_PATH 미정의 구버전 코어(1.7.1 이하)에서 fatal을 유발하므로 쓰지 않는다
?>
<style>
#ra0_update_notice{position:fixed;top:0;left:0;right:0;z-index:100000;background:#2b3a55;color:#eef1f6;font-size:13px;line-height:1.6;box-shadow:0 2px 8px rgba(0,0,0,0.25)}
#ra0_update_notice .ra0_un_inner{position:relative;max-width:1200px;margin:0 auto;padding:9px 90px 9px 16px}
#ra0_update_notice .ra0_un_title{display:inline-block;margin-bottom:2px;font-weight:700;color:#ffd97a}
#ra0_update_notice .ra0_un_list{margin:0;padding:0;list-style:none}
#ra0_update_notice .ra0_un_list li{margin:1px 0}
#ra0_update_notice .ra0_un_list strong{color:#fff}
#ra0_update_notice .ra0_un_get{display:inline-block;margin-left:8px;padding:0 8px;border:1px solid rgba(255,255,255,0.45);border-radius:3px;color:#fff;text-decoration:none;font-size:12px}
#ra0_update_notice .ra0_un_get:hover{background:rgba(255,255,255,0.12);text-decoration:none}
#ra0_update_notice #ra0_update_notice_close{position:absolute;top:50%;right:14px;transform:translateY(-50%);padding:4px 10px;border:1px solid rgba(255,255,255,0.35);border-radius:3px;background:transparent;color:#cfd6e2;font-size:12px;cursor:pointer}
#ra0_update_notice #ra0_update_notice_close:hover{background:rgba(255,255,255,0.1);color:#fff}
</style>
<div id="ra0_update_notice" role="status">
    <div class="ra0_un_inner">
        <span class="ra0_un_title">라공 업데이트 소식</span>
        <ul class="ra0_un_list">
        <?php foreach ($lines as $line) { ?>
            <li>
                <?php echo $line['text']; // 항목별 htmlspecialchars 처리 완료 ?>
                <?php if ($line['url'] !== '' && preg_match('#^https?://#i', $line['url'])) { ?>
                    <a href="<?php echo htmlspecialchars($line['url']); ?>" target="_blank" rel="noopener" class="ra0_un_get">받기</a>
                <?php } ?>
            </li>
        <?php } ?>
        </ul>
        <button type="button" id="ra0_update_notice_close" aria-label="알림 닫기">닫기 &times;</button>
    </div>
</div>
<script>
(function() {
    var btn = document.getElementById('ra0_update_notice_close');
    if (!btn) return;
    btn.addEventListener('click', function() {
        var d = new Date();
        d.setDate(d.getDate() + 30);
        document.cookie = 'ra0_update_dismiss=<?php echo $hash; // md5 hex — 이스케이프 불필요 ?>; expires=' + d.toUTCString() + '; path=/; SameSite=Lax';
        var el = document.getElementById('ra0_update_notice');
        if (el && el.parentNode) el.parentNode.removeChild(el);
    });
})();
</script>
<?php
}
add_event('tail_sub', 'ra0_update_notice_banner');
