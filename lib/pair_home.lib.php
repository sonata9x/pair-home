<?php
if (!defined('_GNUBOARD_')) exit;

function pair_home_default_layout() {
    return array(
        array('id'=>'pair-title','type'=>'text','x'=>7,'y'=>8,'w'=>42,'h'=>14,'rotation'=>0,'z'=>4,'data'=>array('text'=>'OUR PAIR HOME','link'=>'')),
        array('id'=>'pair-image','type'=>'image','x'=>10,'y'=>27,'w'=>32,'h'=>48,'rotation'=>-2,'z'=>2,'data'=>array('src'=>'','shape'=>'rounded','alt'=>'페어 이미지','link'=>'')),
        array('id'=>'pair-dday','type'=>'dday','x'=>61,'y'=>15,'w'=>24,'h'=>18,'rotation'=>2,'z'=>3,'data'=>array('title'=>'함께한 시간','date'=>date('Y-m-d'),'mode'=>'since')),
        array('id'=>'pair-category','type'=>'category','x'=>58,'y'=>43,'w'=>28,'h'=>33,'rotation'=>0,'z'=>3,'data'=>array('title'=>'MENU','items'=>array(
            array('label'=>'PROFILE','url'=>''), array('label'=>'DIARY','url'=>''), array('label'=>'GALLERY','url'=>'')
        )))
    );
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
    $allowed_types = array('image', 'bgm', 'dday', 'sticker', 'category', 'text');
    $allowed_shapes = array('square', 'rounded', 'circle');
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
            }
        } elseif ($type === 'text') {
            $clean_data['text'] = mb_substr(strip_tags((string)($data['text'] ?? '텍스트')), 0, 500);
            $clean_data['link'] = pair_home_clean_url($data['link'] ?? '');
        } elseif ($type === 'dday') {
            $clean_data['title'] = mb_substr(strip_tags((string)($data['title'] ?? 'D-DAY')), 0, 60);
            $date = (string)($data['date'] ?? date('Y-m-d'));
            $clean_data['date'] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : date('Y-m-d');
            $clean_data['mode'] = ($data['mode'] ?? 'since') === 'until' ? 'until' : 'since';
        } elseif ($type === 'bgm') {
            $clean_data['title'] = mb_substr(strip_tags((string)($data['title'] ?? 'BGM')), 0, 100);
            $clean_data['src'] = pair_home_clean_url($data['src'] ?? '');
        } elseif ($type === 'category') {
            $clean_data['title'] = mb_substr(strip_tags((string)($data['title'] ?? 'MENU')), 0, 60);
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
        $result[] = array(
            'id'=>preg_replace('/[^a-zA-Z0-9_-]/', '', (string)($widget['id'] ?? ('widget-'.$index))),
            'type'=>$type,
            'x'=>pair_home_clean_number($widget['x'] ?? 10, 0, 96, 10),
            'y'=>pair_home_clean_number($widget['y'] ?? 10, 0, 96, 10),
            'w'=>pair_home_clean_number($widget['w'] ?? 20, 4, 96, 20),
            'h'=>pair_home_clean_number($widget['h'] ?? 20, 4, 96, 20),
            'rotation'=>pair_home_clean_number($widget['rotation'] ?? 0, -180, 180, 0),
            'z'=>(int)pair_home_clean_number($widget['z'] ?? ($index+1), 1, 999, $index+1),
            'data'=>$clean_data
        );
    }
    return $result;
}

function pair_home_save_setting($key, $value) {
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
    return array('url'=>G5_DATA_URL . '/pair-home/' . basename($destination));
}
