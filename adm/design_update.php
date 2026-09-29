<?php
include_once('./_common.php');

if (!$is_admin) {
    alert('관리자만 접근 가능합니다.');
}

$config_design_table = G5_TABLE_PREFIX . 'config_design';

// 설정 데이터 배열
$design_settings = [
    // 기본 설정
    'favicon_url' => $_POST['favicon_url'] ?? '',
    'use_logo' => $_POST['use_logo'] ?? '1',
    'logo_image_url' => $_POST['logo_image_url'] ?? '',
    'site_bgm_url' => $_POST['site_bgm_url'] ?? '',
    'use_intro' => $_POST['use_intro'] ?? '1',
    'use_header' => $_POST['use_header'] ?? '1',
    'main_type' => $_POST['main_type'] ?? 'default',

    // 메인 페이지 설정
    'main_notice_text' => stripslashes($_POST['main_notice_text'] ?? '한 줄 공지를 입력하여 주십시오.'),
    'main_intro_text' => stripslashes($_POST['main_intro_text'] ?? ''),
    'main_board_1' => $_POST['main_board_1'] ?? '',
    'main_board_2' => $_POST['main_board_2'] ?? '',

    // 슬라이드 이미지 + 링크 추가
    'slide_image_1' => $_POST['slide_image_1'] ?? '',
    'slide_image_2' => $_POST['slide_image_2'] ?? '',
    'slide_image_3' => $_POST['slide_image_3'] ?? '',
    'slide_title_1' => $_POST['slide_title_1'] ?? '',
    'slide_title_2' => $_POST['slide_title_2'] ?? '',
    'slide_title_3' => $_POST['slide_title_3'] ?? '',
    'slide_desc_1' => $_POST['slide_desc_1'] ?? '',
    'slide_desc_2' => $_POST['slide_desc_2'] ?? '',
    'slide_desc_3' => $_POST['slide_desc_3'] ?? '',
    'slide_link_1' => $_POST['slide_link_1'] ?? '',  
    'slide_link_2' => $_POST['slide_link_2'] ?? '',  
    'slide_link_3' => $_POST['slide_link_3'] ?? '',  
    'slide_link_target_1' => $_POST['slide_link_target_1'] ?? '_self',  
    'slide_link_target_2' => $_POST['slide_link_target_2'] ?? '_self',  
    'slide_link_target_3' => $_POST['slide_link_target_3'] ?? '_self',  
    
    // 메인 컬러
    'primary_color' => $_POST['primary_color'] ?? '#64748b',
    'secondary_color' => $_POST['secondary_color'] ?? '#78716c',
    'accent_color' => $_POST['accent_color'] ?? '#137bea',
    
    // 헤더 설정
    'header_position' => $_POST['header_position'] ?? 'top',
    'header_bg_color' => $_POST['header_bg_color'] ?? '#ffffff',
    'header_font_color' => $_POST['header_font_color'] ?? '#1e293b',
    'header_font_family' => ra0_normalize_font_family($_POST['header_font_family'] ?? 'Pretendard'),
    'header_font_size' => $_POST['header_font_size'] ?? '16',
    'header_height' => $_POST['header_height'] ?? '80',
    'header_menu_justify' => $_POST['header_menu_justify'] ?? 'flex-start',
    'header_menu_align' => $_POST['header_menu_align'] ?? 'flex-start',
    
    // 컨테이너 & 카드
    'container_bg_color' => $_POST['container_bg_color'] ?? '#f8fafc',
    'container_border_color' => $_POST['container_border_color'] ?? '#e2e8f0',
    'container_border_radius' => $_POST['container_border_radius'] ?? '12',
    'card_bg_color' => $_POST['card_bg_color'] ?? '#ffffff',
    'card_border_color' => $_POST['card_border_color'] ?? '#e2e8f0',
    'card_border_radius' => $_POST['card_border_radius'] ?? '8',

    
    // 폰트 설정 (누락된 부분 추가)
    'title_font_family' => ra0_normalize_font_family($_POST['title_font_family'] ?? 'Pretendard'),
    'title_font_size' => $_POST['title_font_size'] ?? '24',
    'title_font_color' => $_POST['title_font_color'] ?? '#1e293b',
    'content_font_family' => ra0_normalize_font_family($_POST['content_font_family'] ?? 'Pretendard'),
    'content_font_size' => $_POST['content_font_size'] ?? '14',
    'content_font_color' => $_POST['content_font_color'] ?? '#475569',
    
    // 버튼 설정
    'btn_primary_text' => $_POST['btn_primary_text'] ?? '#ffffff',
    'btn_primary_bg' => $_POST['btn_primary_bg'] ?? '#64748b',
    'btn_primary_radius' => $_POST['btn_primary_radius'] ?? '6',
    'btn_secondary_text' => $_POST['btn_secondary_text'] ?? '#64748b',
    'btn_secondary_bg' => $_POST['btn_secondary_bg'] ?? '#ffffff',
    'btn_secondary_radius' => $_POST['btn_secondary_radius'] ?? '6',
    'btn_accent_text' => $_POST['btn_accent_text'] ?? '#ffffff',
    'btn_accent_bg' => $_POST['btn_accent_bg'] ?? '#137bea',
    'btn_accent_radius' => $_POST['btn_accent_radius'] ?? '6',
    
    // 폼 설정
    'form_bg_color' => $_POST['form_bg_color'] ?? '#ffffff',
    'form_border_color' => $_POST['form_border_color'] ?? '#e2e8f0',
    'form_border_radius' => $_POST['form_border_radius'] ?? '8',
    'form_text_color' => $_POST['form_text_color'] ?? '#374151',
    
    // 배경 설정
    'bg_image_url' => $_POST['bg_image_url'] ?? '',
    'bg_color' => $_POST['bg_color'] ?? '#171717',
    'bg_repeat' => $_POST['bg_repeat'] ?? 'no-repeat',
    'bg_position' => $_POST['bg_position'] ?? 'center center',
    'bg_size' => $_POST['bg_size'] ?? 'cover',

    // 인터랙션 설정
    'cursor_url' => $_POST['cursor_url'] ?? '',
    'cursor_hover_url' => $_POST['cursor_hover_url'] ?? '',
    'click_sound_url' => $_POST['click_sound_url'] ?? '',
    'click_sound_volume' => $_POST['click_sound_volume'] ?? '50',
    'disable_rightclick' => isset($_POST['disable_rightclick']) ? '1' : '0',

    // 스크롤바 설정
    'scrollbar_hidden' => isset($_POST['scrollbar_hidden']) ? '1' : '0',
    'scrollbar_width' => $_POST['scrollbar_width'] ?? '8',
    'scrollbar_track_bg' => $_POST['scrollbar_track_bg'] ?? '#f1f5f9',
    'scrollbar_track_radius' => $_POST['scrollbar_track_radius'] ?? '4',
    'scrollbar_thumb_bg' => $_POST['scrollbar_thumb_bg'] ?? '#94a3b8',
    'scrollbar_thumb_radius' => $_POST['scrollbar_thumb_radius'] ?? '4',

    // 표시 설정
    'show_logo_in_header' => isset($_POST['show_logo_in_header']) ? '1' : '0',
    'show_menu_in_header' => isset($_POST['show_menu_in_header']) ? '1' : '0',

    // BGM 스킨
    'bgm_skin' => $_POST['bgm_skin'] ?? ''
];

// 파비콘 파일 업로드 처리
if (isset($_FILES['favicon_file']) && $_FILES['favicon_file']['error'] == 0) {
    $upload_dir = G5_DATA_PATH . '/design/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_name = 'favicon_' . time() . '_' . $_FILES['favicon_file']['name'];
    $upload_path = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['favicon_file']['tmp_name'], $upload_path)) {
        $design_settings['favicon_url'] = G5_DATA_URL . '/design/' . $file_name;
    }
}

// 로고 이미지 파일 업로드 처리
if (isset($_FILES['logo_image_file']) && $_FILES['logo_image_file']['error'] == 0) {
    $upload_dir = G5_DATA_PATH . '/design/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_name = 'logo_' . time() . '_' . $_FILES['logo_image_file']['name'];
    $upload_path = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['logo_image_file']['tmp_name'], $upload_path)) {
        $design_settings['logo_image_url'] = G5_DATA_URL . '/design/' . $file_name;
    }
}

// 슬라이드 이미지 업로드 처리 (3개)
for ($i = 1; $i <= 3; $i++) {
    $file_key = "slide_image_file_{$i}";
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] == 0) {
        $upload_dir = G5_DATA_PATH . '/design/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = "slide_{$i}_" . time() . '_' . $_FILES[$file_key]['name'];
        $upload_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $upload_path)) {
            $design_settings["slide_image_{$i}"] = G5_DATA_URL . '/design/' . $file_name;
        }
    }
}

// 배경 이미지 파일 업로드 처리
if (isset($_FILES['bg_image_file']) && $_FILES['bg_image_file']['error'] == 0) {
    $upload_dir = G5_DATA_PATH . '/design/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_name = 'bg_' . time() . '_' . $_FILES['bg_image_file']['name'];
    $upload_path = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['bg_image_file']['tmp_name'], $upload_path)) {
        $design_settings['bg_image_url'] = G5_DATA_URL . '/design/' . $file_name;
    }
}

// 커서 이미지 파일 업로드 처리
if (isset($_FILES['cursor_file']) && $_FILES['cursor_file']['error'] == 0) {
    $upload_dir = G5_DATA_PATH . '/design/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_name = 'cursor_' . time() . '_' . $_FILES['cursor_file']['name'];
    $upload_path = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['cursor_file']['tmp_name'], $upload_path)) {
        $design_settings['cursor_url'] = G5_DATA_URL . '/design/' . $file_name;
    }
}

// Hover 커서 이미지 파일 업로드 처리
if (isset($_FILES['cursor_hover_file']) && $_FILES['cursor_hover_file']['error'] == 0) {
    $upload_dir = G5_DATA_PATH . '/design/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_name = 'cursor_hover_' . time() . '_' . $_FILES['cursor_hover_file']['name'];
    $upload_path = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['cursor_hover_file']['tmp_name'], $upload_path)) {
        $design_settings['cursor_hover_url'] = G5_DATA_URL . '/design/' . $file_name;
    }
}

// 클릭음 파일 업로드 처리
if (isset($_FILES['click_sound_file']) && $_FILES['click_sound_file']['error'] == 0) {
    $upload_dir = G5_DATA_PATH . '/design/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_name = 'click_sound_' . time() . '_' . $_FILES['click_sound_file']['name'];
    $upload_path = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['click_sound_file']['tmp_name'], $upload_path)) {
        $design_settings['click_sound_url'] = G5_DATA_URL . '/design/' . $file_name;
    }
}

// 데이터베이스에 저장
foreach ($design_settings as $key => $value) {
    $value = sql_real_escape_string($value); // SQL 인젝션 방지
    $sql = "INSERT INTO $config_design_table (cd_key, cd_value, cd_group, cd_name, cd_type)
            VALUES ('$key', '$value', 'design', '$key', 'text')
            ON DUPLICATE KEY UPDATE cd_value = '$value'";
    sql_query($sql);
}

// CSS 캐시 파일 삭제 (새로고침을 위해)
$cache_files = glob(G5_DATA_PATH . '/cache/css_*');
foreach ($cache_files as $file) {
    @unlink($file);
}

alert('디자인 설정이 저장되었습니다.', './config_design.php');
?>
