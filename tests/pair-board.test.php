<?php
define('_GNUBOARD_', true);
function clean_xss_tags($value) { return (string)$value; }
require dirname(__DIR__).'/lib/pair_board.lib.php';

function pair_board_assert($condition, $message) {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$board_style = pair_board_style_attribute(array('accent'=>'#AABBCC','background'=>'#DDEEFF','background_image'=>'https://example.com/background image.jpg'));
pair_board_assert(strpos($board_style,'--pair-board-accent:#AABBCC') !== false && strpos($board_style,'--pair-board-page-bg:#DDEEFF') !== false, 'board style must inherit the main palette');
pair_board_assert(strpos($board_style,'background image.jpg') !== false, 'board style must preserve the sanitized main background image');

if (!class_exists('DOMDocument')) {
    fwrite(STDOUT, "SKIP: DOM extension is unavailable\n");
    exit(0);
}

$source = '<!doctype html><html><head><style>.entry{color:red}</style><script>alert(1)</script></head><body onclick="alert(2)"><h1>Chapter</h1><!-- PAIR_BGM:chapter-1|2장 BGM --><a href="javascript:alert(3)">bad</a><iframe src="https://example.com"></iframe></body></html>';
$clean = pair_log_sanitize_html($source);
pair_board_assert(stripos($clean, '<script') === false, 'uploaded scripts must be removed');
pair_board_assert(stripos($clean, 'onclick=') === false, 'inline event handlers must be removed');
pair_board_assert(stripos($clean, '<iframe') === false, 'uploaded iframes must be removed');
pair_board_assert(stripos($clean, 'javascript:') === false, 'javascript URLs must be removed');
pair_board_assert(strpos($clean, 'PAIR_BGM:chapter-1') !== false, 'BGM markers must survive sanitizing');
pair_board_assert(strpos($clean, '.entry{color:red}') !== false, 'document styles must be preserved');

$tracks = array('chapter-1'=>array('track_key'=>'chapter-1','title'=>'Theme','artist'=>'Artist','audio_url'=>'https://cdn.example.com/theme.mp3','cover_url'=>''));
$srcdoc = pair_log_build_srcdoc($source, $tracks, '#AABBCC');
pair_board_assert(strpos($srcdoc, '2장 BGM') !== false, 'marker label must be rendered');
pair_board_assert(strpos($srcdoc, 'https://cdn.example.com/theme.mp3') !== false, 'track URL must be rendered');
pair_board_assert(strpos($srcdoc, 'PAIR_BGM:chapter-1') === false, 'marker must be replaced');
pair_board_assert(substr_count(strtolower($srcdoc), '<script>') === 1, 'only the controlled runtime script may remain');
pair_board_assert(stripos($srcdoc, '</script>') !== false, 'controlled runtime script must close normally');

$parsed = pair_log_parse_tracks("waiting|Waiting|Composer|https://cdn.example.com/w.mp3|https://cdn.example.com/w.jpg\ninvalid key|Nope|X|javascript:alert(1)|");
pair_board_assert(count($parsed) === 1 && $parsed[0]['track_key'] === 'waiting', 'playlist parser must reject invalid/dangerous tracks');

fwrite(STDOUT, "pair-board PHP tests passed\n");
