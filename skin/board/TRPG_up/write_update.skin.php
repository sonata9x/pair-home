<?php
if (!defined("_GNUBOARD_")) exit; // 보안상 필요함

// if (!$wr_num) $wr_num = $write['wr_num'];

$trpg_edit_mode = isset($_POST['trpg_edit_mode']) ? $_POST['trpg_edit_mode'] : '';
$trpg_kpc = trim((string)(isset($_POST['wr_kpc']) ? $_POST['wr_kpc'] : ''));
$trpg_pc = trim((string)(isset($_POST['wr_pc']) ? $_POST['wr_pc'] : ''));
$trpg_style_fonts = array();
foreach (array('wr_subject_font', 'wr_english_title_font', 'wr_subtitle_font', 'wr_catchphrase_font') as $trpg_font_field) {
    $trpg_style_fonts[$trpg_font_field] = trim((string)(isset($_POST[$trpg_font_field]) ? $_POST[$trpg_font_field] : ''));
}
$trpg_style_colors = array();
foreach (array('wr_subject_color', 'wr_english_title_color', 'wr_subtitle_color', 'wr_catchphrase_color') as $trpg_color_field) {
    $trpg_color_value = strtoupper(trim((string)(isset($_POST[$trpg_color_field]) ? $_POST[$trpg_color_field] : '')));
    $trpg_style_colors[$trpg_color_field] = preg_match('/^#[0-9A-F]{6}$/', $trpg_color_value) ? $trpg_color_value : '';
}

// 로그 본문 전용 수정에서는 페이지 설정과 이미지 정보를 건드리지 않는다.
// 실제 긴 본문은 게시글 저장 뒤 ajax.content_chunk.php에서 교체한다.
if ($trpg_edit_mode !== 'content') {
    // 게시글 설정 업데이트
    $sql = "UPDATE {$write_table} SET
        wr_subject = '" . sql_real_escape_string(isset($_POST['wr_subject']) ? $_POST['wr_subject'] : '') . "',          /* 제목 */
        wr_title   = '" . sql_real_escape_string(isset($_POST['wr_title']) ? $_POST['wr_title'] : '') . "',               /* 부제목 */
        wr_english_title = '" . sql_real_escape_string(isset($_POST['wr_english_title']) ? $_POST['wr_english_title'] : '') . "', /* 영문 제목 */
        wr_catchphrase = '" . sql_real_escape_string(isset($_POST['wr_catchphrase']) ? $_POST['wr_catchphrase'] : '') . "', /* 캐치프레이즈 */
        wr_playtime = '" . sql_real_escape_string(isset($_POST['wr_playtime']) ? $_POST['wr_playtime'] : '') . "',       /* 진행시간 */
        wr_kpc = '" . sql_real_escape_string($trpg_kpc) . "',                                                           /* KPC */
        wr_pc = '" . sql_real_escape_string($trpg_pc) . "',                                                             /* PC */
        wr_subject_font = '" . sql_real_escape_string($trpg_style_fonts['wr_subject_font']) . "',
        wr_subject_color = '" . sql_real_escape_string($trpg_style_colors['wr_subject_color']) . "',
        wr_english_title_font = '" . sql_real_escape_string($trpg_style_fonts['wr_english_title_font']) . "',
        wr_english_title_color = '" . sql_real_escape_string($trpg_style_colors['wr_english_title_color']) . "',
        wr_subtitle_font = '" . sql_real_escape_string($trpg_style_fonts['wr_subtitle_font']) . "',
        wr_subtitle_color = '" . sql_real_escape_string($trpg_style_colors['wr_subtitle_color']) . "',
        wr_catchphrase_font = '" . sql_real_escape_string($trpg_style_fonts['wr_catchphrase_font']) . "',
        wr_catchphrase_color = '" . sql_real_escape_string($trpg_style_colors['wr_catchphrase_color']) . "',
        wr_content = '" . sql_real_escape_string(isset($_POST['wr_content']) ? $_POST['wr_content'] : '') . "',
        wr_adult   = '" . (isset($_POST['wr_adult']) ? 1 : 0) . "',      /* 여분 */
        wr_wide    = '" . (isset($_POST['wr_wide']) ? 1 : 0) . "',       /* 완료 여부 */
        wr_plip    = '" . (isset($_POST['wr_plip']) ? 1 : 0) . "',       /* 여분 */
        wr_url     = '" . sql_real_escape_string(isset($_POST['wr_url']) ? $_POST['wr_url'] : '') . "',             /* 배경 이미지 URL */
        wr_1       = '" . sql_real_escape_string(isset($_POST['wr_1']) ? $_POST['wr_1'] : '') . "',               /* 여분 */
        wr_2       = '" . sql_real_escape_string(isset($_POST['wr_2']) ? $_POST['wr_2'] : '') . "',               /* 본문 배경 컬러 */
        wr_3       = '" . sql_real_escape_string(isset($_POST['wr_3']) ? $_POST['wr_3'] : '') . "',               /* 본문 폰트 컬러 */
        wr_4       = '" . sql_real_escape_string(isset($_POST['wr_4']) ? $_POST['wr_4'] : '') . "',               /* 여분 */
        wr_5       = '" . sql_real_escape_string(isset($_POST['wr_5']) ? $_POST['wr_5'] : '') . "',               /* 개요 */
        wr_7       = '" . sql_real_escape_string(isset($_POST['wr_7']) ? $_POST['wr_7'] : '') . "',               /* 날짜 */
        wr_8       = '" . sql_real_escape_string($trpg_style_fonts['wr_subject_font']) . "',               /* 이전 버전 호환용 제목 글꼴 */
        wr_9       = '" . sql_real_escape_string(isset($_POST['wr_9']) ? $_POST['wr_9'] : '') . "',               /* 여분 */
        wr_10      = '" . sql_real_escape_string(isset($_POST['wr_10']) ? $_POST['wr_10'] : '') . "',              /* BGM 링크 */
        wr_order   = '" . sql_real_escape_string(isset($_POST['wr_order']) ? $_POST['wr_order'] : '0') . "',           /* 정렬 순서 */
        wr_last = '" . G5_TIME_YMDHIS . "'                                           /* 마지막 수정 시간 */
        WHERE wr_id='{$wr_id}'";
    sql_query($sql);
} else {
    // 본문 전용 수정도 마지막 수정 시간에는 반영한다.
    sql_query("UPDATE {$write_table} SET wr_last = '" . G5_TIME_YMDHIS . "' WHERE wr_id = '{$wr_id}'");
}

// TRPG 업로드 테이블 설정
$trpg_up_table = G5_TABLE_PREFIX . 'trpg_up';
$trpg_img_path = G5_DATA_PATH . "/file/" . $bo_table;
$trpg_img_url = G5_DATA_URL . "/file/" . $bo_table;

// 디렉토리가 없으면 생성
@mkdir($trpg_img_path, G5_DIR_PERMISSION);
@chmod($trpg_img_path, G5_DIR_PERMISSION);

if ($trpg_edit_mode !== 'content') {
// 1. 세션 카드 이미지 처리
// 1-1. 파일 업로드 방식
if (isset($_FILES['trpg_card_image']) && !empty($_FILES['trpg_card_image']['name'][0])) {
    // 기존 카드 이미지 삭제 (한 개만 유지)
    sql_query("UPDATE {$trpg_up_table} SET img_use = '0' WHERE bo_table = '{$bo_table}' AND wr_id = '{$wr_id}' AND image_type = 'card'");
    
    $file = $_FILES['trpg_card_image'];
    
    // 파일명 생성 및 저장
    $filename = "card_" . uniqid() . "." . pathinfo($file['name'][0], PATHINFO_EXTENSION);
    $upload_file = $trpg_img_path . "/" . $filename;
    $image_url = $trpg_img_url . "/" . $filename;
    
    // 이미지 업로드
    if (move_uploaded_file($file['tmp_name'][0], $upload_file)) {
        // 파일 권한 설정
        @chmod($upload_file, G5_FILE_PERMISSION);
        
        // DB에 저장
        $sql = "INSERT INTO {$trpg_up_table} 
                (bo_table, wr_id, image_type, image_url, wr_type, img_use, file_order) 
                VALUES 
                ('{$bo_table}', '{$wr_id}', 'card', '{$image_url}', 'upload', '1', '0')";
        sql_query($sql);
    }
}

// 1-2. URL 입력 방식
else if (isset($_POST['trpg_card_url']) && trim($_POST['trpg_card_url']) !== '') {
    // 기존 카드 이미지 삭제 (한 개만 유지)
    sql_query("UPDATE {$trpg_up_table} SET img_use = '0' WHERE bo_table = '{$bo_table}' AND wr_id = '{$wr_id}' AND image_type = 'card'");
    $card_url = trim($_POST['trpg_card_url']);
    
    // DB에 저장
    sql_query("INSERT INTO {$trpg_up_table}
              (bo_table, wr_id, image_type, image_url, wr_type, img_use, file_order)
              VALUES
              ('{$bo_table}', '{$wr_id}', 'card', '".sql_real_escape_string($card_url)."', 'url', '1', '0')");
}

// 2. 캐릭터 이미지 처리
// 2-1. 이미지 삭제 처리
if (!empty($_POST['cha_image_delete'])) {
    $delete_ids = json_decode($_POST['cha_image_delete'], true);
    if (is_array($delete_ids) && count($delete_ids) > 0) {
        foreach ($delete_ids as $id) {
            // 파일 정보 가져오기
            $file_info = sql_fetch("SELECT * FROM {$trpg_up_table} WHERE id = '{$id}' AND bo_table = '{$bo_table}' AND wr_id = '{$wr_id}'");
            
            // 업로드 파일인 경우 실제 파일도 삭제
            if ($file_info['wr_type'] == 'upload') {
                $file_path = str_replace(G5_DATA_URL, G5_DATA_PATH, $file_info['image_url']);
                @unlink($file_path);
            }
            
            // DB에서 삭제 표시
            sql_query("UPDATE {$trpg_up_table} SET img_use = '0' WHERE id = '{$id}'");
        }
    }
}

// 2-2. 이미지 순서 업데이트
if (!empty($_POST['cha_image_order'])) {
    $order_ids = json_decode($_POST['cha_image_order'], true);
    if (is_array($order_ids) && count($order_ids) > 0) {
        foreach ($order_ids as $index => $id) {
            sql_query("UPDATE {$trpg_up_table} SET file_order = '{$index}' WHERE id = '{$id}' AND bo_table = '{$bo_table}' AND wr_id = '{$wr_id}'");
        }
    }
}

// 2-3. 새 캐릭터 이미지 업로드
if (isset($_FILES['trpg_cha_image']) && !empty($_FILES['trpg_cha_image']['name'][0])) {
    // 현재 최대 순서 값 가져오기
    $max_order = sql_fetch("SELECT MAX(file_order) as max_order FROM {$trpg_up_table} WHERE bo_table = '{$bo_table}' AND wr_id = '{$wr_id}' AND image_type = 'cha' AND img_use = '1'");
    $order_start = $max_order['max_order'] ? $max_order['max_order'] + 1 : 0;
    
    $files = $_FILES['trpg_cha_image'];
    $file_count = count($files['name']);
    
    for ($i = 0; $i < $file_count; $i++) {
        if (!empty($files['name'][$i])) {
            // 파일명 생성 및 저장
            $filename = "cha_" . uniqid() . "." . pathinfo($files['name'][$i], PATHINFO_EXTENSION);
            $upload_file = $trpg_img_path . "/" . $filename;
            $image_url = $trpg_img_url . "/" . $filename;
            
            // 이미지 업로드
            if (move_uploaded_file($files['tmp_name'][$i], $upload_file)) {
                // 이미지 리사이징 함수 호출
                resize_image_trpg($upload_file, 400, 0, true);
                
                // DB에 저장
                sql_query("INSERT INTO {$trpg_up_table} 
                      (bo_table, wr_id, image_type, image_url, wr_type, img_use, file_order) 
                      VALUES 
                      ('{$bo_table}', '{$wr_id}', 'cha', '{$image_url}', 'upload', '1', '{$order_start}')");
                $order_start++;
            }
        }
    }
}

// 3. 일반 이미지(핸드아웃) 처리
// 3-1. 이미지 삭제 처리
if (!empty($_POST['normal_image_delete'])) {
    $delete_ids = json_decode($_POST['normal_image_delete'], true);
    if (is_array($delete_ids) && count($delete_ids) > 0) {
        foreach ($delete_ids as $id) {
            // 파일 정보 가져오기
            $file_info = sql_fetch("SELECT * FROM {$trpg_up_table} WHERE id = '{$id}' AND bo_table = '{$bo_table}' AND wr_id = '{$wr_id}'");
            
            // 업로드 파일인 경우 실제 파일도 삭제
            if ($file_info['wr_type'] == 'upload') {
                $file_path = str_replace(G5_DATA_URL, G5_DATA_PATH, $file_info['image_url']);
                @unlink($file_path);
            }
            
            // DB에서 삭제 표시
            sql_query("UPDATE {$trpg_up_table} SET img_use = '0' WHERE id = '{$id}'");
        }
    }
}

// 3-2. 이미지 순서 업데이트
if (!empty($_POST['normal_image_order'])) {
    $order_ids = json_decode($_POST['normal_image_order'], true);
    if (is_array($order_ids) && count($order_ids) > 0) {
        foreach ($order_ids as $index => $id) {
            sql_query("UPDATE {$trpg_up_table} SET file_order = '{$index}' WHERE id = '{$id}' AND bo_table = '{$bo_table}' AND wr_id = '{$wr_id}'");
        }
    }
}

// 3-3. 새 일반 이미지 업로드
if (isset($_FILES['trpg_normal_image']) && !empty($_FILES['trpg_normal_image']['name'][0])) {
    // 현재 최대 순서 값 가져오기
    $max_order = sql_fetch("SELECT MAX(file_order) as max_order FROM {$trpg_up_table} WHERE bo_table = '{$bo_table}' AND wr_id = '{$wr_id}' AND image_type = 'normal' AND img_use = '1'");
    $order_start = $max_order['max_order'] ? $max_order['max_order'] + 1 : 0;
    
    $files = $_FILES['trpg_normal_image'];
    $file_count = count($files['name']);
    
    for ($i = 0; $i < $file_count; $i++) {
        if (!empty($files['name'][$i])) {
            // 파일명 생성 및 저장
            $filename = "normal_" . uniqid() . "." . pathinfo($files['name'][$i], PATHINFO_EXTENSION);
            $upload_file = $trpg_img_path . "/" . $filename;
            $image_url = $trpg_img_url . "/" . $filename;
            
            // 이미지 업로드
            if (move_uploaded_file($files['tmp_name'][$i], $upload_file)) {
                // 이미지 리사이징 함수 호출 (최대 가로 700px, 세로 900px)
                resize_image_trpg($upload_file, 700, 900, false);
                
                // DB에 저장
                sql_query("INSERT INTO {$trpg_up_table} 
                          (bo_table, wr_id, image_type, image_url, wr_type, img_use, file_order) 
                          VALUES 
                          ('{$bo_table}', '{$wr_id}', 'normal', '{$image_url}', 'upload', '1', '{$order_start}')");
                $order_start++;
            }
        }
    }
}

// 2-4. URL 방식으로 추가된 캐릭터 이미지 처리
if (!empty($_POST['trpg_cha_url_images'])) {
    $cha_urls = explode(',', $_POST['trpg_cha_url_images']);
    if (is_array($cha_urls) && count($cha_urls) > 0) {
        // 현재 최대 순서 값 가져오기
        $max_order = sql_fetch("SELECT MAX(file_order) as max_order FROM {$trpg_up_table} WHERE bo_table = '{$bo_table}' AND wr_id = '{$wr_id}' AND image_type = 'cha' AND img_use = '1'");
        $order_start = $max_order['max_order'] ? $max_order['max_order'] + 1 : 0;
        
        foreach ($cha_urls as $url) {
            if (empty(trim($url))) continue;  // 빈 URL 건너뛰기
            
            // DB에 저장
            sql_query("INSERT INTO {$trpg_up_table}
                      (bo_table, wr_id, image_type, image_url, wr_type, img_use, file_order)
                      VALUES
                      ('{$bo_table}', '{$wr_id}', 'cha', '".sql_real_escape_string(trim($url))."', 'url', '1', '{$order_start}')");
            $order_start++;
        }
    }
}

// 3-4. URL 방식으로 추가된 일반 이미지 처리
if (!empty($_POST['trpg_normal_url_images'])) {
    $normal_urls = explode(',', $_POST['trpg_normal_url_images']);
    if (is_array($normal_urls) && count($normal_urls) > 0) {
        // 현재 최대 순서 값 가져오기
        $max_order = sql_fetch("SELECT MAX(file_order) as max_order FROM {$trpg_up_table} WHERE bo_table = '{$bo_table}' AND wr_id = '{$wr_id}' AND image_type = 'normal' AND img_use = '1'");
        $order_start = $max_order['max_order'] ? $max_order['max_order'] + 1 : 0;
        
        foreach ($normal_urls as $url) {
            if (empty(trim($url))) continue;  // 빈 URL 건너뛰기
            
            // DB에 저장
            sql_query("INSERT INTO {$trpg_up_table}
                      (bo_table, wr_id, image_type, image_url, wr_type, img_use, file_order)
                      VALUES
                      ('{$bo_table}', '{$wr_id}', 'normal', '".sql_real_escape_string(trim($url))."', 'url', '1', '{$order_start}')");
            $order_start++;
        }
    }
}

// // 파일 업로드 처리 (기본 그누보드 방식 사용)
// if (isset($_FILES['bf_file']) && count($_FILES['bf_file']['name']) > 0) {
//     upload_file($bo_table, $wr_id, $_FILES['bf_file']);
// }

// // 파일 업로드 오류 처리
// if ($file_upload_msg) {
//     alert($file_upload_msg, G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table);
// }
}

/**
 * 이미지 리사이징 함수
 * 
 * @param string $file_path 이미지 파일 경로
 * @param int $max_width 최대 가로 크기 (0이면 제한 없음)
 * @param int $max_height 최대 세로 크기 (0이면 제한 없음)
 * @param bool $force_width 가로 크기에 맞춰 강제 리사이징 여부
 * @return bool 성공 여부
 */
function resize_image_trpg($file_path, $max_width = 0, $max_height = 0, $force_width = false) {
    if (!file_exists($file_path)) {
        return false;
    }
    
    // 이미지 정보 가져오기
    $image_info = @getimagesize($file_path);
    if (!$image_info) {
        return false;
    }
    
    $width = $image_info[0];
    $height = $image_info[1];
    $mime_type = $image_info['mime'];
    
    // 리사이징이 필요한지 확인
    $need_resize = false;
    
    if ($force_width && $max_width > 0 && $width > $max_width) {
        // 가로 강제 리사이징 모드
        $need_resize = true;
    } else if ($max_width > 0 && $max_height > 0) {
        // 가로, 세로 모두 제한이 있는 경우
        if ($width > $max_width || $height > $max_height) {
            $need_resize = true;
        }
    } else if ($max_width > 0 && $width > $max_width) {
        // 가로만 제한이 있는 경우
        $need_resize = true;
    } else if ($max_height > 0 && $height > $max_height) {
        // 세로만 제한이 있는 경우
        $need_resize = true;
    }
    
    if (!$need_resize) {
        return true;
    }
    
    // 원본 이미지 생성
    $src_image = null;
    
    switch ($mime_type) {
        case 'image/jpeg':
            $src_image = @imagecreatefromjpeg($file_path);
            break;
        case 'image/png':
            $src_image = @imagecreatefrompng($file_path);
            break;
        case 'image/gif':
            $src_image = @imagecreatefromgif($file_path);
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $src_image = @imagecreatefromwebp($file_path);
            }
            break;
        default:
            return false;
    }
    
    if (!$src_image) {
        return false;
    }
    
    // 새 크기 계산
    $new_width = $width;
    $new_height = $height;
    
    if ($force_width && $max_width > 0) {
        // 가로 강제 리사이징 모드
        if ($width > $max_width) {
            $new_width = $max_width;
            $new_height = round(($height * $max_width) / $width);
        }
    } else {
        // 비율 유지 리사이징
        if ($max_width > 0 && $max_height > 0) {
            // 가로, 세로 모두 제한이 있는 경우
            $ratio_w = $max_width / $width;
            $ratio_h = $max_height / $height;
            $ratio = min($ratio_w, $ratio_h);
            
            if ($ratio < 1) {
                $new_width = round($width * $ratio);
                $new_height = round($height * $ratio);
            }
        } else if ($max_width > 0 && $width > $max_width) {
            // 가로만 제한이 있는 경우
            $new_width = $max_width;
            $new_height = round(($height * $max_width) / $width);
        } else if ($max_height > 0 && $height > $max_height) {
            // 세로만 제한이 있는 경우
            $new_height = $max_height;
            $new_width = round(($width * $max_height) / $height);
        }
    }
    
    // 요청에 따라 이미지를 늘리지 않음
    if ($new_width > $width) $new_width = $width;
    if ($new_height > $height) $new_height = $height;
    
    // 리사이징이 필요한지 다시 확인
    if ($new_width == $width && $new_height == $height) {
        imagedestroy($src_image);
        return true;
    }
    
    // 새 이미지 생성
    $dst_image = imagecreatetruecolor($new_width, $new_height);
    
    // PNG 및 GIF 투명도 유지
    if ($mime_type == 'image/png' || $mime_type == 'image/gif') {
        imagealphablending($dst_image, false);
        imagesavealpha($dst_image, true);
        $transparent = imagecolorallocatealpha($dst_image, 255, 255, 255, 127);
        imagefilledrectangle($dst_image, 0, 0, $new_width, $new_height, $transparent);
    }
    
    // 리사이징
    if (!imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height)) {
        imagedestroy($src_image);
        imagedestroy($dst_image);
        return false;
    }
    
    // 원본 이미지 메모리 해제
    imagedestroy($src_image);
    
    // 리사이징된 이미지 저장
    $save_result = false;
    
    switch ($mime_type) {
        case 'image/jpeg':
            $save_result = @imagejpeg($dst_image, $file_path, 90);
            break;
        case 'image/png':
            $save_result = @imagepng($dst_image, $file_path, 9);
            break;
        case 'image/gif':
            $save_result = @imagegif($dst_image, $file_path);
            break;
        case 'image/webp':
            if (function_exists('imagewebp')) {
                $save_result = @imagewebp($dst_image, $file_path, 80);
            } else {
                $save_result = @imagepng($dst_image, $file_path, 9);
            }
            break;
    }
    
    // 새 이미지 메모리 해제
    imagedestroy($dst_image);
    
    return $save_result;
}

goto_url(G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.$qstr."#log_".$wr_id);
?>
