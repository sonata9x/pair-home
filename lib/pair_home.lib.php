<?php
if (!defined('_GNUBOARD_')) exit;

function pair_home_default_layout() {
    return array();
}

function pair_home_clean_url($url) {
    $url = trim((string)$url);
    if ($url === '' || preg_match('/^(?:javascript|data|vbscript):/i', $url)) return '';
    return clean_xss_tags($url, 1, 1);
}

function pair_home_clean_number($value, $min, $max, $default) {
    if (!is_numeric($value)) return $default;
    return max($min, min($max, (float)$value));
}

function pair_home_sanitize_layout($layout) {
    if (!is_array($layout)) return pair_home_default_layout();
    $allowed_types = array('image', 'webframe', 'bgm', 'dday', 'sticker', 'category', 'text', 'separator', 'label', 'linkbanner', 'calendar');
    $allowed_shapes = array('square', 'rounded', 'circle');
    $allowed_ratios = array('free', '1:1', '2:3', '3:2', '3:4', '4:3', '9:16', '16:9');
    $result = array();
    foreach (array_slice($layout, 0, 150) as $index => $widget) {
        if (!is_array($widget) || !in_array($widget['type'] ?? '', $allowed_types, true)) continue;
        $type = $widget['type'];
        $data = is_array($widget['data'] ?? null) ? $widget['data'] : array();
        $clean_data = array();
        if ($type === 'image' || $type === 'sticker') {
            $clean_data['src'] = pair_home_clean_url($data['src'] ?? '');
            $clean_data['alt'] = mb_substr(strip_tags((string)($data['alt'] ?? '')), 0, 100);
            $clean_data['link'] = pair_home_clean_url($data['link'] ?? '');
            if ($type === 'image') {
                $shape = (string)($data['shape'] ?? 'rounded');
                $clean_data['shape'] = in_array($shape, $allowed_shapes, true) ? $shape : 'rounded';
                $clean_data['radius'] = pair_home_clean_number($data['radius'] ?? 18, 0, 120, 18);
                $ratio = (string)($data['ratio'] ?? 'free');
                $clean_data['ratio'] = in_array($ratio, $allowed_ratios, true) ? $ratio : 'free';
                $clean_data['zoom'] = pair_home_clean_number($data['zoom'] ?? 1, 1, 3, 1);
                $clean_data['focus_x'] = pair_home_clean_number($data['focus_x'] ?? 50, 0, 100, 50);
                $clean_data['focus_y'] = pair_home_clean_number($data['focus_y'] ?? 50, 0, 100, 50);
            } else {
                $clean_data['opacity'] = pair_home_clean_number($data['opacity'] ?? 1, 0.1, 1, 1);
                $clean_data['flip_x'] = !empty($data['flip_x']);
                $clean_data['aspect'] = pair_home_clean_number($data['aspect'] ?? 0, 0, 20, 0);
            }
        } elseif ($type === 'webframe') {
            $clean_data['show_address'] = !isset($data['show_address']) || !empty($data['show_address']);
            $clean_data['address'] = mb_substr(strip_tags((string)($data['address'] ?? 'pair-home.local')), 0, 120);
            $body_style = (string)($data['body_style'] ?? 'panel');
            $clean_data['body_style'] = $body_style === 'transparent' ? 'transparent' : 'panel';
            $content_mode = (string)($data['content_mode'] ?? 'empty');
            $clean_data['content_mode'] = in_array($content_mode, array('empty','image','text'), true) ? $content_mode : 'empty';
            $clean_data['src'] = pair_home_clean_url($data['src'] ?? '');
            $clean_data['body_text'] = mb_substr(strip_tags((string)($data['body_text'] ?? '')), 0, 3000);
            $body_font = (string)($data['body_font'] ?? 'pretendard');
            $body_fonts = array('sans','serif','handwriting','pretendard','noto-sans','noto-serif','gowun-dodum','gowun-batang','nanum-pen','ibm-plex');
            $clean_data['body_font'] = in_array($body_font,$body_fonts,true) ? $body_font : 'pretendard';
            $clean_data['body_font_size'] = pair_home_clean_number($data['body_font_size'] ?? 16, 10, 80, 16);
            $body_align = (string)($data['body_align'] ?? 'left');
            $clean_data['body_align'] = in_array($body_align,array('left','center','right'),true) ? $body_align : 'left';
            $clean_data['body_zoom'] = pair_home_clean_number($data['body_zoom'] ?? 1, 1, 3, 1);
            $clean_data['body_focus_x'] = pair_home_clean_number($data['body_focus_x'] ?? 50, 0, 100, 50);
            $clean_data['body_focus_y'] = pair_home_clean_number($data['body_focus_y'] ?? 50, 0, 100, 50);
        } elseif ($type === 'text') {
            $clean_data['text'] = mb_substr(strip_tags((string)($data['text'] ?? '텍스트')), 0, 500);
            $clean_data['link'] = pair_home_clean_url($data['link'] ?? '');
            $font = (string)($data['font'] ?? 'serif');
            $fonts = array('sans', 'serif', 'handwriting', 'pretendard', 'noto-sans', 'noto-serif', 'gowun-dodum', 'gowun-batang', 'nanum-pen', 'ibm-plex');
            $clean_data['font'] = in_array($font, $fonts, true) ? $font : 'pretendard';
            $clean_data['font_size'] = pair_home_clean_number($data['font_size'] ?? 48, 12, 160, 48);
            $color = strtoupper((string)($data['color'] ?? '#282326'));
            $clean_data['color'] = preg_match('/^#[0-9A-F]{6}$/', $color) ? $color : '#282326';
            $clean_data['weight'] = ($data['weight'] ?? 'normal') === 'bold' ? 'bold' : 'normal';
            $align = (string)($data['align'] ?? 'left');
            $clean_data['align'] = in_array($align, array('left', 'center', 'right'), true) ? $align : 'left';
            $clean_data['line_height'] = pair_home_clean_number($data['line_height'] ?? 1.05, 0.8, 2.5, 1.05);
        } elseif ($type === 'separator') {
            $orientation = (string)($data['orientation'] ?? 'horizontal');
            $clean_data['orientation'] = $orientation === 'vertical' ? 'vertical' : 'horizontal';
            $line_style = (string)($data['line_style'] ?? 'solid');
            $clean_data['line_style'] = in_array($line_style, array('solid','dashed','dotted','double'), true) ? $line_style : 'solid';
            $clean_data['thickness'] = pair_home_clean_number($data['thickness'] ?? 1, 1, 8, 1);
            $color = strtoupper((string)($data['line_color'] ?? '#7FAFD1'));
            $clean_data['line_color'] = preg_match('/^#[0-9A-F]{6}$/', $color) ? $color : '#7FAFD1';
            $clean_data['line_color_source'] = ($data['line_color_source'] ?? 'site') === 'custom' ? 'custom' : 'site';
        } elseif ($type === 'label') {
            $clean_data['text'] = mb_substr(strip_tags((string)($data['text'] ?? 'LABEL')), 0, 120);
            $shape = (string)($data['shape'] ?? 'rectangle');
            $clean_data['shape'] = in_array($shape, array('rectangle','pill','tab'), true) ? $shape : 'rectangle';
            $align = (string)($data['align'] ?? 'center');
            $clean_data['align'] = in_array($align, array('left','center','right'), true) ? $align : 'center';
        } elseif ($type === 'linkbanner') {
            $clean_data['title'] = mb_substr(strip_tags((string)($data['title'] ?? 'LINK')), 0, 100);
            $clean_data['src'] = pair_home_clean_url($data['src'] ?? '');
            $clean_data['link'] = pair_home_clean_url($data['link'] ?? '');
            $clean_data['alt'] = mb_substr(strip_tags((string)($data['alt'] ?? '')), 0, 100);
            $clean_data['image_fit'] = ($data['image_fit'] ?? 'contain') === 'cover' ? 'cover' : 'contain';
            $clean_data['show_title'] = !empty($data['show_title']);
            $clean_data['new_tab'] = !empty($data['new_tab']);
        } elseif ($type === 'calendar') {
            $clean_data['title'] = mb_substr(strip_tags((string)($data['title'] ?? 'CALENDAR')), 0, 60);
            $mode = (string)($data['calendar_mode'] ?? 'current');
            $clean_data['calendar_mode'] = $mode === 'fixed' ? 'fixed' : 'current';
            $clean_data['year'] = (int)pair_home_clean_number($data['year'] ?? date('Y'), 1900, 2200, (int)date('Y'));
            $clean_data['month'] = (int)pair_home_clean_number($data['month'] ?? date('n'), 1, 12, (int)date('n'));
            $clean_data['highlights'] = mb_substr(preg_replace('/[^0-9,\-]/', '', (string)($data['highlights'] ?? '')), 0, 500);
            $clean_data['events'] = mb_substr(strip_tags((string)($data['events'] ?? '')), 0, 3000);
        } elseif ($type === 'dday') {
            $clean_data['title'] = mb_substr(strip_tags((string)($data['title'] ?? 'D-DAY')), 0, 60);
            $date = (string)($data['date'] ?? date('Y-m-d'));
            $clean_data['date'] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : date('Y-m-d');
            $clean_data['mode'] = ($data['mode'] ?? 'since') === 'until' ? 'until' : 'since';
            $clean_data['include_today'] = !isset($data['include_today']) || !empty($data['include_today']);
            $clean_data['show_date'] = !isset($data['show_date']) || !empty($data['show_date']);
            $style = (string)($data['style'] ?? 'lcd');
            $clean_data['style'] = in_array($style, array('lcd', 'label', 'plain'), true) ? $style : 'lcd';
            $accent = strtoupper((string)($data['accent'] ?? '#B8FF3D'));
            $clean_data['accent'] = preg_match('/^#[0-9A-F]{6}$/', $accent) ? $accent : '#B8FF3D';
        } elseif ($type === 'bgm') {
            $clean_data['title'] = mb_substr(strip_tags((string)($data['title'] ?? 'BGM')), 0, 100);
            $clean_data['artist'] = mb_substr(strip_tags((string)($data['artist'] ?? '')), 0, 100);
            $clean_data['src'] = pair_home_clean_url($data['src'] ?? '');
            $clean_data['cover_src'] = pair_home_clean_url($data['cover_src'] ?? '');
            $clean_data['cover_zoom'] = pair_home_clean_number($data['cover_zoom'] ?? 1, 1, 3, 1);
            $clean_data['cover_focus_x'] = pair_home_clean_number($data['cover_focus_x'] ?? 50, 0, 100, 50);
            $clean_data['cover_focus_y'] = pair_home_clean_number($data['cover_focus_y'] ?? 50, 0, 100, 50);
            $player_style = (string)($data['player_style'] ?? 'mini');
            $clean_data['player_style'] = in_array($player_style, array('mini','mini-pixel','cover','cover-pixel','album','album-pixel','lp','lp-pixel','sleeve','sleeve-pixel','backdrop','backdrop-pixel','deck'), true) ? $player_style : 'mini';
            $clean_data['loop'] = !empty($data['loop']);
            $clean_data['volume'] = pair_home_clean_number($data['volume'] ?? 0.8, 0, 1, 0.8);
        } elseif ($type === 'category') {
            $clean_data['title'] = mb_substr(strip_tags((string)($data['title'] ?? 'MENU')), 0, 60);
            $style = (string)($data['style'] ?? 'buttons');
            $clean_data['style'] = in_array($style, array('buttons', 'directory', 'plain'), true) ? $style : 'buttons';
            $layout = (string)($data['layout'] ?? 'vertical');
            $clean_data['layout'] = $layout === 'horizontal' ? 'horizontal' : 'vertical';
            $clean_data['new_tab'] = !empty($data['new_tab']);
            $accent = strtoupper((string)($data['accent'] ?? '#FF4B39'));
            $clean_data['accent'] = preg_match('/^#[0-9A-F]{6}$/', $accent) ? $accent : '#FF4B39';
            $clean_data['items'] = array();
            if (is_array($data['items'] ?? null)) {
                foreach (array_slice($data['items'], 0, 30) as $item) {
                    if (!is_array($item)) continue;
                    $label = mb_substr(strip_tags((string)($item['label'] ?? '')), 0, 60);
                    if ($label === '') continue;
                    $clean_data['items'][] = array('label'=>$label, 'url'=>pair_home_clean_url($item['url'] ?? ''));
                }
            }
        }
        $width = pair_home_clean_number($widget['w'] ?? 20, 4, 96, 20);
        $height = pair_home_clean_number($widget['h'] ?? 20, 4, 96, 20);
        $x = pair_home_clean_number($widget['x'] ?? 10, 0, 96, 10);
        $y = pair_home_clean_number($widget['y'] ?? 10, 0, 96, 10);
        $result[] = array(
            'id'=>preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($widget['id'] ?? ('widget-'.$index))),
            'type'=>$type,
            'x'=>min($x, 100 - $width),
            'y'=>min($y, 100 - $height),
            'w'=>$width,
            'h'=>$height,
            'rotation'=>pair_home_clean_number($widget['rotation'] ?? 0, -180, 180, 0),
            'z'=>(int)pair_home_clean_number($widget['z'] ?? ($index+1), 1, 999, $index+1),
            'locked'=>!empty($widget['locked']),
            'data'=>$clean_data
        );
    }
    return $result;
}

function pair_home_ensure_setting_table() {
    static $ready = null;
    if ($ready !== null) return $ready;

    $table = G5_TABLE_PREFIX . 'config_design';
    if (!sql_query("DESC {$table}", false)) {
        sql_query("CREATE TABLE IF NOT EXISTS `{$table}` (
            `cd_id` int(11) NOT NULL AUTO_INCREMENT,
            `cd_key` varchar(100) NOT NULL COMMENT '설정 키',
            `cd_value` text COMMENT '설정 값',
            `cd_group` varchar(50) NOT NULL COMMENT '설정 그룹',
            `cd_name` varchar(100) NOT NULL COMMENT '설정명',
            `cd_type` varchar(20) NOT NULL DEFAULT 'text' COMMENT '입력 타입',
            `cd_order` int(11) NOT NULL DEFAULT '0' COMMENT '정렬순서',
            PRIMARY KEY (`cd_id`),
            UNIQUE KEY `cd_key` (`cd_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8", false);
    }

    $ready = (bool)sql_query("DESC {$table}", false);
    return $ready;
}

function pair_home_save_setting($key, $value) {
    if (!pair_home_ensure_setting_table()) return false;
    $table = G5_TABLE_PREFIX . 'config_design';
    $key = sql_real_escape_string($key);
    $value = sql_real_escape_string($value);
    return sql_query("INSERT INTO {$table} (cd_key, cd_value, cd_group, cd_name, cd_type)
        VALUES ('{$key}', '{$value}', 'pair_home', '{$key}', 'text')
        ON DUPLICATE KEY UPDATE cd_value = '{$value}'", false);
}

function pair_home_image_resource($path, $mime) {
    if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) return @imagecreatefromjpeg($path);
    if ($mime === 'image/png' && function_exists('imagecreatefrompng')) return @imagecreatefrompng($path);
    if ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) return @imagecreatefromwebp($path);
    return false;
}

function pair_home_upload_image($file, $kind = 'asset') {
    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return array('error'=>'이미지 업로드에 실패했습니다.');
    if (($file['size'] ?? 0) > 20 * 1024 * 1024) return array('error'=>'이미지는 20MB 이하만 업로드할 수 있습니다.');
    $info = @getimagesize($file['tmp_name']);
    $allowed = array('image/jpeg','image/png','image/webp','image/gif');
    if (!$info || !in_array($info['mime'], $allowed, true)) return array('error'=>'JPG, PNG, WebP, GIF 이미지만 사용할 수 있습니다.');
    $upload_dir = G5_DATA_PATH . '/pair-home/';
    if (!is_dir($upload_dir) && !@mkdir($upload_dir, 0755, true)) return array('error'=>'이미지 저장 폴더를 만들 수 없습니다.');
    $max = $kind === 'background' ? 2560 : 1600;
    $width = (int)$info[0]; $height = (int)$info[1];
    $scale = min(1, $max / max(1, $width), $max / max(1, $height));
    $base = ($kind === 'background' ? 'background_' : 'asset_') . date('YmdHis') . '_' . bin2hex(random_bytes(4));
    if ($info['mime'] === 'image/gif') {
        $destination = $upload_dir . $base . '.gif';
        if (!@move_uploaded_file($file['tmp_name'], $destination)) return array('error'=>'이미지를 저장하지 못했습니다.');
    } else {
        $source = pair_home_image_resource($file['tmp_name'], $info['mime']);
        if (!$source) return array('error'=>'서버에서 이 이미지 형식을 처리할 수 없습니다.');
        $target_width = max(1, (int)round($width * $scale));
        $target_height = max(1, (int)round($height * $scale));
        $target = imagecreatetruecolor($target_width, $target_height);
        imagealphablending($target, false); imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefilledrectangle($target, 0, 0, $target_width, $target_height, $transparent);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $target_width, $target_height, $width, $height);
        if (function_exists('imagewebp')) {
            $destination = $upload_dir . $base . '.webp'; $saved = @imagewebp($target, $destination, 86);
        } else {
            $destination = $upload_dir . $base . '.png'; $saved = @imagepng($target, $destination, 7);
        }
        imagedestroy($source); imagedestroy($target);
        if (!$saved) return array('error'=>'이미지를 저장하지 못했습니다.');
    }
    return array(
        'url'=>G5_DATA_URL . '/pair-home/' . basename($destination),
        'width'=>$width,
        'height'=>$height
    );
}

function pair_home_upload_audio($file) {
    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return array('error'=>'음원 업로드에 실패했습니다.');
    }
    if (($file['size'] ?? 0) > 50 * 1024 * 1024) {
        return array('error'=>'음원은 50MB 이하만 업로드할 수 있습니다.');
    }

    $extension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
    $allowed_extensions = array('mp3', 'ogg', 'wav', 'm4a');
    if (!in_array($extension, $allowed_extensions, true)) {
        return array('error'=>'MP3, OGG, WAV, M4A 음원만 사용할 수 있습니다.');
    }

    $mime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = (string)finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        }
    }
    $allowed_mimes = array(
        'audio/mpeg', 'audio/mp3', 'audio/ogg', 'application/ogg',
        'audio/wav', 'audio/x-wav', 'audio/wave', 'audio/mp4',
        'audio/x-m4a', 'video/mp4', 'application/octet-stream'
    );
    if ($mime !== '' && !in_array($mime, $allowed_mimes, true)) {
        return array('error'=>'올바른 음원 파일인지 확인해 주세요.');
    }

    $upload_dir = G5_DATA_PATH . '/pair-home/audio/';
    if (!is_dir($upload_dir) && !@mkdir($upload_dir, 0755, true)) {
        return array('error'=>'음원 저장 폴더를 만들 수 없습니다.');
    }
    $filename = 'bgm_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = $upload_dir . $filename;
    if (!@move_uploaded_file($file['tmp_name'], $destination)) {
        return array('error'=>'음원을 저장하지 못했습니다.');
    }

    return array('url'=>G5_DATA_URL . '/pair-home/audio/' . $filename);
}
