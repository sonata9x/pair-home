<?php
if (!defined('_GNUBOARD_')) exit;

include_once __DIR__.'/pair_home.lib.php';

function pair_board_design_context() {
    static $context = null;
    if ($context !== null) return $context;
    $document = json_decode(get_design_config('pair_home_grid_document', ''), true);
    $document = is_array($document) ? $document : array();
    $theme = (string)($document['defaultTheme'] ?? 'flat');
    if (!in_array($theme, array('flat','line','bold','soft','pixel','glass'), true)) $theme = 'flat';
    $accent = strtoupper((string)($document['representativeColor'] ?? '#7FAFD1'));
    if (!preg_match('/^#[0-9A-F]{6}$/', $accent)) $accent = '#7FAFD1';
    $background = strtoupper((string)($document['backgroundColor'] ?? '#E8F0F4'));
    if (!preg_match('/^#[0-9A-F]{6}$/', $background)) $background = '#E8F0F4';
    $background_image = pair_home_clean_url($document['backgroundImage'] ?? '');
    return $context = array('theme'=>$theme, 'accent'=>$accent, 'background'=>$background, 'background_image'=>$background_image);
}

function pair_board_style_attribute($context) {
    $accent = preg_match('/^#[0-9A-F]{6}$/',(string)($context['accent'] ?? '')) ? $context['accent'] : '#7FAFD1';
    $background = preg_match('/^#[0-9A-F]{6}$/',(string)($context['background'] ?? '')) ? $context['background'] : '#E8F0F4';
    $image = pair_home_clean_url($context['background_image'] ?? '');
    $image_value = $image === '' ? 'none' : 'url('.json_encode($image, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).')';
    return '--pair-board-accent:'.$accent.';--pair-board-page-bg:'.$background.';--pair-board-page-image:'.$image_value;
}

function pair_board_escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function pair_board_first_image($bo_table, $wr_id) {
    global $g5;
    $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$bo_table);
    $wr_id = (int)$wr_id;
    if ($bo_table === '' || $wr_id < 1) return array('url'=>'','alt'=>'');
    $row = sql_fetch("SELECT bf_file, bf_source, bf_content FROM {$g5['board_file_table']} WHERE bo_table='".sql_real_escape_string($bo_table)."' AND wr_id='{$wr_id}' AND bf_file<>'' ORDER BY bf_no ASC LIMIT 1", false);
    if (empty($row['bf_file'])) return array('url'=>'','alt'=>'');
    return array(
        'url'=>G5_DATA_URL.'/file/'.rawurlencode($bo_table).'/'.rawurlencode($row['bf_file']),
        'alt'=>trim((string)($row['bf_content'] ?: $row['bf_source']))
    );
}

function pair_log_track_table() {
    global $g5;
    return $g5['table_prefix'].'pair_log_track';
}

function pair_log_ensure_track_table() {
    $table = pair_log_track_table();
    return (bool)sql_query("CREATE TABLE IF NOT EXISTS `{$table}` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `bo_table` varchar(80) NOT NULL DEFAULT '',
        `wr_id` int(11) NOT NULL DEFAULT '0',
        `track_key` varchar(80) NOT NULL DEFAULT '',
        `title` varchar(180) NOT NULL DEFAULT '',
        `artist` varchar(180) NOT NULL DEFAULT '',
        `audio_url` text NOT NULL,
        `cover_url` text NOT NULL,
        `sort_order` int(11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`),
        UNIQUE KEY `pair_log_track_key` (`bo_table`,`wr_id`,`track_key`),
        KEY `pair_log_post` (`bo_table`,`wr_id`,`sort_order`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4", false);
}

function pair_log_parse_tracks($text) {
    $tracks = array();
    foreach (preg_split('/\r?\n/', (string)$text) as $line) {
        if (trim($line) === '') continue;
        $parts = array_map('trim', explode('|', $line, 5));
        $key = preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($parts[0] ?? ''));
        $url = pair_home_clean_url($parts[3] ?? '');
        if ($key === '' || $url === '' || isset($tracks[$key])) continue;
        $tracks[$key] = array(
            'track_key'=>$key,
            'title'=>mb_substr(strip_tags((string)($parts[1] ?? $key)),0,180),
            'artist'=>mb_substr(strip_tags((string)($parts[2] ?? '')),0,180),
            'audio_url'=>$url,
            'cover_url'=>pair_home_clean_url($parts[4] ?? '')
        );
        if (count($tracks) >= 100) break;
    }
    return array_values($tracks);
}

function pair_log_save_tracks($bo_table, $wr_id, $tracks) {
    $table = pair_log_track_table();
    if (!pair_log_ensure_track_table()) return false;
    $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$bo_table);
    $wr_id = (int)$wr_id;
    sql_query("DELETE FROM {$table} WHERE bo_table='".sql_real_escape_string($bo_table)."' AND wr_id='{$wr_id}'");
    foreach (array_values($tracks) as $index=>$track) {
        sql_query("INSERT INTO {$table} SET bo_table='".sql_real_escape_string($bo_table)."', wr_id='{$wr_id}', track_key='".sql_real_escape_string($track['track_key'])."', title='".sql_real_escape_string($track['title'])."', artist='".sql_real_escape_string($track['artist'])."', audio_url='".sql_real_escape_string($track['audio_url'])."', cover_url='".sql_real_escape_string($track['cover_url'])."', sort_order='".(int)$index."'");
    }
    return true;
}

function pair_log_get_tracks($bo_table, $wr_id) {
    $tracks = array();
    $table = pair_log_track_table();
    $bo_table = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$bo_table);
    $wr_id = (int)$wr_id;
    $result = sql_query("SELECT track_key,title,artist,audio_url,cover_url FROM {$table} WHERE bo_table='".sql_real_escape_string($bo_table)."' AND wr_id='{$wr_id}' ORDER BY sort_order,id", false);
    if (!$result) return $tracks;
    while ($row = sql_fetch_array($result)) $tracks[$row['track_key']] = $row;
    return $tracks;
}

function pair_log_tracks_to_text($tracks) {
    $lines = array();
    foreach ($tracks as $track) $lines[] = implode('|', array($track['track_key'],$track['title'],$track['artist'],$track['audio_url'],$track['cover_url']));
    return implode("\n", $lines);
}

function pair_log_sanitize_html($html) {
    $html = str_replace("\0", '', (string)$html);
    if ($html === '' || !class_exists('DOMDocument')) return '';
    $previous = libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_HTML_NODEFDTD|LIBXML_HTML_NOIMPLIED);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    if (!$loaded) return '';
    $forbidden = array('script','iframe','object','embed','form','input','button','textarea','select','option','meta','base','link','frame','frameset','applet');
    foreach ($forbidden as $tag) {
        $nodes = array();
        foreach ($dom->getElementsByTagName($tag) as $node) $nodes[] = $node;
        foreach ($nodes as $node) if ($node->parentNode) $node->parentNode->removeChild($node);
    }
    $xpath = new DOMXPath($dom);
    foreach ($xpath->query('//*') as $node) {
        $remove = array();
        foreach ($node->attributes as $attribute) {
            $name = strtolower($attribute->name);
            $value = trim($attribute->value);
            if (strpos($name, 'on') === 0 || in_array($name,array('srcdoc','formaction'),true)) $remove[] = $attribute->name;
            if (in_array($name,array('href','src','poster','xlink:href'),true) && preg_match('/^(?:javascript|vbscript|data:text\/html)/i',$value)) $remove[] = $attribute->name;
        }
        foreach (array_unique($remove) as $name) $node->removeAttribute($name);
        if (strtolower($node->nodeName) === 'a') {
            $node->setAttribute('rel','noopener noreferrer');
            if ($node->getAttribute('target') === '') $node->setAttribute('target','_blank');
        }
    }
    $clean = $dom->saveHTML();
    return preg_replace('/<\?xml[^>]+>\s*/i', '', $clean);
}

function pair_log_player_markup($track, $label='') {
    if (!$track || empty($track['audio_url'])) return '';
    $title = $label !== '' ? $label : ($track['title'] ?: $track['track_key']);
    return '<section class="pair-log-cue"><div class="pair-log-cue-copy"><strong>'.pair_board_escape($title).'</strong>'.($track['artist'] !== ''?'<span>'.pair_board_escape($track['artist']).'</span>':'').'</div><audio controls preload="metadata" src="'.pair_board_escape($track['audio_url']).'"></audio></section>';
}

function pair_log_build_srcdoc($html, $tracks, $accent='#7FAFD1') {
    $clean = pair_log_sanitize_html($html);
    $clean = preg_replace_callback('/<!--\s*PAIR_BGM:([a-zA-Z0-9_-]+)(?:\|([^>]*?))?\s*-->/', function($match) use ($tracks) {
        $key = $match[1];
        $label = html_entity_decode(trim((string)($match[2] ?? '')), ENT_QUOTES|ENT_HTML5, 'UTF-8');
        return isset($tracks[$key]) ? pair_log_player_markup($tracks[$key], $label) : '';
    }, $clean);
    $accent = preg_match('/^#[0-9A-Fa-f]{6}$/',$accent) ? $accent : '#7FAFD1';
    $style = '<style>html,body{margin:0;padding:0;background:transparent;color:inherit}body{font-family:Pretendard,"Noto Sans KR",sans-serif;overflow-wrap:anywhere}img,video{max-width:100%;height:auto}table{max-width:100%;border-collapse:collapse}.pair-log-cue{display:grid;grid-template-columns:minmax(0,1fr) minmax(180px,42%);align-items:center;gap:14px;margin:24px 0;padding:12px 14px;border:1px solid '.$accent.';border-radius:6px;background:#fff}.pair-log-cue-copy{display:grid;gap:3px}.pair-log-cue-copy strong{color:'.$accent.'}.pair-log-cue-copy span{font-size:12px;opacity:.68}.pair-log-cue audio{width:100%}@media(max-width:560px){.pair-log-cue{grid-template-columns:1fr}}</style>';
    $script = '<script>(function(){var audios=[].slice.call(document.querySelectorAll("audio"));audios.forEach(function(audio){audio.addEventListener("play",function(){audios.forEach(function(other){if(other!==audio)other.pause()});parent.postMessage({type:"pair-log-play"},"*")})});addEventListener("message",function(e){if(e.data&&e.data.type==="pair-log-pause")audios.forEach(function(a){a.pause()})});function size(){parent.postMessage({type:"pair-log-size",height:Math.max(document.body.scrollHeight,document.documentElement.scrollHeight)},"*")}if(window.ResizeObserver)new ResizeObserver(size).observe(document.body);addEventListener("load",size)})();</script>';
    if (stripos($clean,'</head>') !== false) $clean = preg_replace('/<\/head>/i',$style.'</head>',$clean,1); else $clean = $style.$clean;
    if (stripos($clean,'</body>') !== false) $clean = preg_replace('/<\/body>/i',$script.'</body>',$clean,1); else $clean .= $script;
    return $clean;
}
