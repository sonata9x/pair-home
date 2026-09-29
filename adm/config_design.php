<?php
$sub_menu = "100200";
include_once('./_common.php');

// 디자인 설정 테이블 생성
$config_design_table = G5_TABLE_PREFIX . 'config_design';
if (!sql_query("DESC {$config_design_table}", false)) {
    sql_query("CREATE TABLE IF NOT EXISTS `{$config_design_table}` (
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

// 디자인 프리셋 테이블 생성
$design_preset_table = G5_TABLE_PREFIX . 'config_design_preset';
if (!sql_query("DESC {$design_preset_table}", false)) {
    sql_query("CREATE TABLE IF NOT EXISTS `{$design_preset_table}` (
        `dp_id` int(11) NOT NULL AUTO_INCREMENT,
        `dp_name` varchar(100) NOT NULL COMMENT '프리셋명',
        `dp_description` text COMMENT '프리셋 설명',
        `dp_data` longtext NOT NULL COMMENT '설정 데이터 (JSON)',
        `dp_preview` varchar(255) COMMENT '미리보기 이미지',
        `dp_type` enum('system','user') NOT NULL DEFAULT 'user' COMMENT '프리셋 타입',
        `dp_is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '기본 프리셋 여부',
        `dp_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일시',
        PRIMARY KEY (`dp_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8", false);
    
    // 기본 시스템 프리셋 추가
    $system_presets = [
        [
            'name' => '클래식 블루',
            'description' => '모던하고 깔끔한 블루 톤 디자인',
            'data' => [
                // 기본 설정
                'favicon_url' => '',
                'use_logo' => '1',
                'logo_image_url' => '',
                'site_bgm_url' => '',
                'use_intro' => '1',
                'use_header' => '1',
                'main_type' => 'default',
                'main_notice_text' => '한 줄 공지를 입력하여 주십시오.',
                'main_intro_text' => '',
                'main_board_1' => '',
                'main_board_2' => '',

                // 슬라이드 이미지 설정
                'slide_image_1' => '',
                'slide_image_2' => '',
                'slide_image_3' => '',
                'slide_title_1' => '',
                'slide_title_2' => '',
                'slide_title_3' => '',
                'slide_desc_1' => '',
                'slide_desc_2' => '',
                'slide_desc_3' => '',
                'slide_link_1' => '',
                'slide_link_2' => '',
                'slide_link_3' => '',
                'slide_link_target_1' => '_self',
                'slide_link_target_2' => '_self',
                'slide_link_target_3' => '_self',
                
                // 색상 설정
                'primary_color' => '#64748b',
                'secondary_color' => '#78716c',
                'accent_color' => '#137bea',
                
                // 헤더 설정
                'header_position' => 'top',
                'header_bg_color' => '#334155',
                'header_font_color' => '#ffffff',
                'header_font_family' => 'Pretendard',
                'header_font_size' => '16',
                'header_height' => '80',
                'header_menu_justify' => 'flex-start',
                'header_menu_align' => 'flex-start',

                // 컨테이너 & 카드
                'container_bg_color' => '#f1f5f9',
                'container_border_color' => '#e2e8f0',
                'container_border_radius' => '12',
                'card_bg_color' => '#ffffff',
                'card_border_color' => '#e2e8f0',
                'card_border_radius' => '8',
                
                // 폰트 설정
                'title_font_family' => 'Gothic A1',
                'title_font_size' => '24',
                'title_font_color' => '#1e293b',
                'content_font_family' => 'Pretendard',
                'content_font_size' => '14',
                'content_font_color' => '#475569',
                
                // 버튼 설정
                'btn_primary_bg' => '#64748b',
                'btn_primary_text' => '#ffffff',
                'btn_primary_radius' => '6',
                'btn_secondary_bg' => '#ffffff',
                'btn_secondary_text' => '#64748b',
                'btn_secondary_radius' => '6',
                'btn_accent_bg' => '#137bea',
                'btn_accent_text' => '#ffffff',
                'btn_accent_radius' => '6',
                
                // 입력폼
                'form_bg_color' => '#ffffff',
                'form_text_color' => '#374151',
                'form_border_color' => '#e2e8f0',
                'form_border_radius' => '8',
                
                // 배경 설정
                'bg_image_url' => '',
                'bg_color' => '#f7f7f7',
                'bg_repeat' => 'no-repeat',
                'bg_position' => 'center center',
                'bg_size' => 'cover',

                // 인터랙션 설정
                'cursor_url' => '',
                'cursor_hover_url' => '',
                'click_sound_url' => '',
                'click_sound_volume' => '50',
                'disable_rightclick' => '0',

                // 스크롤바 설정
                'scrollbar_hidden' => '0',
                'scrollbar_width' => '8',
                'scrollbar_track_bg' => '#f1f5f9',
                'scrollbar_track_radius' => '4',
                'scrollbar_thumb_bg' => '#94a3b8',
                'scrollbar_thumb_radius' => '4'
            ],
            'type' => 'system'
        ],
        [
            'name' => '모던 다크',
            'description' => '세련된 다크 테마 디자인',
            'data' => [
                // 기본 설정
                'favicon_url' => '',
                'use_logo' => '1',
                'logo_image_url' => '',
                'site_bgm_url' => '',
                'use_intro' => '1',
                'use_header' => '1',
                'main_type' => 'default',
                'main_notice_text' => '한 줄 공지를 입력하여 주십시오.',
                'main_intro_text' => '',
                'main_board_1' => '',
                'main_board_2' => '',

                // 슬라이드 이미지 설정
                'slide_image_1' => '',
                'slide_image_2' => '',
                'slide_image_3' => '',
                'slide_title_1' => '',
                'slide_title_2' => '',
                'slide_title_3' => '',
                'slide_desc_1' => '',
                'slide_desc_2' => '',
                'slide_desc_3' => '',
                'slide_link_1' => '',
                'slide_link_2' => '',
                'slide_link_3' => '',
                'slide_link_target_1' => '_self',
                'slide_link_target_2' => '_self',
                'slide_link_target_3' => '_self',
                
                // 색상 설정
                'primary_color' => '#1f2937',
                'secondary_color' => '#4b5563',
                'accent_color' => 'rgba(92, 6, 29, 1.00)',
                
                // 헤더 설정
                'header_position' => 'top',
                'header_bg_color' => 'rgba(23, 25, 29, 1.00)',
                'header_font_color' => '#ffffff',
                'header_font_family' => 'Playfair Display',
                'header_font_size' => '16',
                'header_height' => '70',
                'header_menu_justify' => 'flex-start',
                'header_menu_align' => 'flex-start',

                // 컨테이너 & 카드
                'container_bg_color' => '#222428',
                'container_border_color' => '#37393e',
                'container_border_radius' => '12',
                'card_bg_color' => '#11141a',
                'card_border_color' => '#212535',
                'card_border_radius' => '8',
                
                // 폰트 설정
                'title_font_family' => 'Playfair Display',
                'title_font_size' => '28',
                'title_font_color' => '#ffffff',
                'content_font_family' => 'Pretendard',
                'content_font_size' => '14',
                'content_font_color' => '#d1d5db',
                
                // 버튼 설정
                'btn_primary_bg' => '#3b82f6',
                'btn_primary_text' => '#ffffff',
                'btn_primary_radius' => '8',
                'btn_secondary_bg' => '#4b5563',
                'btn_secondary_text' => '#ffffff',
                'btn_secondary_radius' => '8',
                'btn_accent_bg' => '#10b981',
                'btn_accent_text' => '#ffffff',
                'btn_accent_radius' => '8',
                
                // 입력폼
                'form_bg_color' => '#374151',
                'form_text_color' => '#f3f4f6',
                'form_border_color' => '#4b5563',
                'form_border_radius' => '8',
                
                // 배경 설정
                'bg_image_url' => '',
                'bg_color' => '#171717',
                'bg_repeat' => 'no-repeat',
                'bg_position' => 'center center',
                'bg_size' => 'cover',

                // 인터랙션 설정
                'cursor_url' => '',
                'cursor_hover_url' => '',
                'click_sound_url' => '',
                'click_sound_volume' => '50',
                'disable_rightclick' => '0',

                // 스크롤바 설정
                'scrollbar_hidden' => '0',
                'scrollbar_width' => '8',
                'scrollbar_track_bg' => '#1f2937',
                'scrollbar_track_radius' => '4',
                'scrollbar_thumb_bg' => '#4b5563',
                'scrollbar_thumb_radius' => '4'
            ],
            'type' => 'system'
        ],
        [
            'name' => '미니멀 화이트',
            'description' => '깔끔하고 단순한 화이트 테마',
            'data' => [
                // 기본 설정
                'favicon_url' => '',
                'use_logo' => '1',
                'logo_image_url' => '',
                'site_bgm_url' => '',
                'use_intro' => '0',
                'use_header' => '1',
                'main_type' => 'default',
                'main_notice_text' => '한 줄 공지를 입력하여 주십시오.',
                'main_intro_text' => '',
                'main_board_1' => '',
                'main_board_2' => '',
                
                // 슬라이드 이미지 설정
                'slide_image_1' => '',
                'slide_image_2' => '',
                'slide_image_3' => '',
                'slide_title_1' => '',
                'slide_title_2' => '',
                'slide_title_3' => '',
                'slide_desc_1' => '',
                'slide_desc_2' => '',
                'slide_desc_3' => '',
                'slide_link_1' => '',
                'slide_link_2' => '',
                'slide_link_3' => '',
                'slide_link_target_1' => '_self',
                'slide_link_target_2' => '_self',
                'slide_link_target_3' => '_self',
                
                // 색상 설정
                'primary_color' => '#ffffff',
                'secondary_color' => '#64748b',
                'accent_color' => '#ef4444',
                
                // 헤더 설정
                'header_position' => 'top',
                'header_bg_color' => '#ffffff',
                'header_font_color' => '#1e293b',
                'header_font_family' => 'Pretendard',
                'header_font_size' => '15',
                'header_height' => '60',
                'header_menu_justify' => 'flex-start',
                'header_menu_align' => 'flex-start',

                // 컨테이너 & 카드
                'container_bg_color' => '#ffffff',
                'container_border_color' => '#f1f5f9',
                'container_border_radius' => '0',
                'card_bg_color' => '#ffffff',
                'card_border_color' => '#f1f5f9',
                'card_border_radius' => '4',
                
                // 폰트 설정
                'title_font_family' => 'Pretendard',
                'title_font_size' => '20',
                'title_font_color' => '#1e293b',
                'content_font_family' => 'Pretendard',
                'content_font_size' => '14',
                'content_font_color' => '#475569',
                
                // 버튼 설정
                'btn_primary_bg' => '#1e293b',
                'btn_primary_text' => '#ffffff',
                'btn_primary_radius' => '4',
                'btn_secondary_bg' => '#ffffff',
                'btn_secondary_text' => '#64748b',
                'btn_secondary_radius' => '4',
                'btn_accent_bg' => '#ef4444',
                'btn_accent_text' => '#ffffff',
                'btn_accent_radius' => '4',
                
                // 입력폼
                'form_bg_color' => '#ffffff',
                'form_text_color' => '#374151',
                'form_border_color' => '#e2e8f0',
                'form_border_radius' => '4',
                
                // 배경 설정
                'bg_image_url' => '',
                'bg_color' => '#ffffff',
                'bg_repeat' => 'no-repeat',
                'bg_position' => 'center center',
                'bg_size' => 'auto',

                // 인터랙션 설정
                'cursor_url' => '',
                'cursor_hover_url' => '',
                'click_sound_url' => '',
                'click_sound_volume' => '50',
                'disable_rightclick' => '0',

                // 스크롤바 설정
                'scrollbar_hidden' => '0',
                'scrollbar_width' => '8',
                'scrollbar_track_bg' => '#f1f5f9',
                'scrollbar_track_radius' => '4',
                'scrollbar_thumb_bg' => '#94a3b8',
                'scrollbar_thumb_radius' => '4'
            ],
            'type' => 'system'
        ]
    ];

    foreach($system_presets as $index => $preset) {
        $is_default = ($index === 0) ? 1 : 0;
        
        // 여기서 JSON 인코딩 수행
        $json_data = json_encode($preset['data'], JSON_UNESCAPED_UNICODE);
        
        // JSON 인코딩 오류 체크
        if (json_last_error() !== JSON_ERROR_NONE) {
            continue;
        }
        
        // SQL 인젝션 방지
        $name = sql_real_escape_string($preset['name']);
        $description = sql_real_escape_string($preset['description']);
        $json_data = sql_real_escape_string($json_data);
        $type = sql_real_escape_string($preset['type']);
        
        sql_query("INSERT INTO {$design_preset_table} 
                (dp_name, dp_description, dp_data, dp_type, dp_is_default) 
                VALUES ('{$name}', '{$description}', '{$json_data}', '{$type}', '$is_default')", false);
    }
}

// 폰트 테이블 생성 및 기본 데이터 삽입
$config_font_table = G5_TABLE_PREFIX . 'config_font';
if (!sql_query("DESC {$config_font_table}", false)) {
    sql_query("CREATE TABLE IF NOT EXISTS `{$config_font_table}` (
        `fo_id` int(11) NOT NULL AUTO_INCREMENT,
        `fo_family` varchar(200) NOT NULL COMMENT 'font-family 값 (키로 사용)',
        `fo_name` varchar(100) NOT NULL COMMENT '폰트 이름 (표시용)',
        `fo_import` text COMMENT '@import 구문',
        `fo_use` tinyint(1) NOT NULL DEFAULT '1' COMMENT '사용여부',
        `fo_order` int(11) NOT NULL DEFAULT '0' COMMENT '정렬순서',
        `fo_memo` text COMMENT '메모',
        `fo_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`fo_id`),
        UNIQUE KEY `fo_family` (`fo_family`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8", false);
}

// 기본 폰트 삽입: 기존 설치본은 fo_family 기준으로 보강만 하고, 기존 값은 덮어쓰지 않는다.
$fonts = [
    [
        'family' => 'Pretendard',
        'name' => 'Pretendard',
        'import' => '@import url("https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.8/dist/web/static/pretendard.css");',
        'use' => 1,
        'order' => 1,
        'memo' => '기본 한글 폰트'
    ],
    [
        'family' => 'Playfair Display',
        'name' => 'Playfair Display',
        'import' => '@import url("https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap");',
        'use' => 1,
        'order' => 2,
        'memo' => '영문 세리프 폰트'
    ],
    [
        'family' => 'Courier Prime',
        'name' => 'Courier Prime',
        'import' => '@import url("https://fonts.googleapis.com/css2?family=Courier+Prime:ital,wght@0,400;0,700;1,400;1,700&display=swap");',
        'use' => 1,
        'order' => 3,
        'memo' => '영문 타자기 폰트'
    ],
    [
        'family' => 'Bebas Neue',
        'name' => 'Bebas Neue',
        'import' => '@import url("https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap");',
        'use' => 1,
        'order' => 3,
        'memo' => '영문 압축 제목 폰트'
    ],
    [
        'family' => 'Chosunilbo_myungjo',
        'name' => '조선일보명조', 
        'import' => '@font-face {
        font-family: \'Chosunilbo_myungjo\';
        src: url(\'https://fastly.jsdelivr.net/gh/projectnoonnu/noonfonts_one@1.0/Chosunilbo_myungjo.woff\') format(\'woff\');
        font-weight: normal;
        font-style: normal;
    }',
        'use' => 1,
        'order' => 5,
        'memo' => '한글 명조 폰트'
    ],
    [
        'family' => 'ChosunSm',
        'name' => '조선신명조',
        'import' => '@font-face { font-family: "ChosunSm"; src: url("https://fastly.jsdelivr.net/gh/projectnoonnu/noonfonts_20-04@1.1/ChosunSm.woff") format("woff"); font-weight: normal; font-style: normal; }',
        'use' => 1,
        'order' => 6,
        'memo' => '한글 명조 폰트'
    ],
    [
        'family' => 'Noto Serif SC',
        'name' => 'Noto Serif SC',
        'import' => '@import url("https://fonts.googleapis.com/css2?family=Noto+Serif+SC:wght@200..900&display=swap");',
        'use' => 1,
        'order' => 4,
        'memo' => '중국어 폰트'
    ],
    [
        'family' => 'Zen Antique',
        'name' => 'Zen Antique',
        'import' => '@import url("https://fonts.googleapis.com/css2?family=Zen+Antique&display=swap");',
        'use' => 1,
        'order' => 3,
        'memo' => '일본어 폰트'
    ],
    [
        'family' => 'AndeulScienceWeak',
        'name' => '안될과학약체',
        'import' => '@font-face {
        font-family: \'AndeulScienceWeak\';
        src: url(\'https://cdn.jsdelivr.net/gh/projectnoonnu/noonfonts_2205-2@1.0/Yak.woff2\') format(\'woff2\');
        font-weight: normal;
        font-style: normal;
        font-display: swap;
    }',
        'use' => 1,
        'order' => 100,
        'memo' => '한글 손글씨 폰트'
    ]
];

foreach($fonts as $font) {
    $family_escaped = sql_real_escape_string($font['family']);
    $name_escaped = sql_real_escape_string($font['name']);
    $import_escaped = sql_real_escape_string($font['import']);
    $memo_escaped = sql_real_escape_string($font['memo']);
    
    sql_query("INSERT IGNORE INTO `{$config_font_table}` 
        (`fo_family`, `fo_name`, `fo_import`, `fo_use`, `fo_order`, `fo_memo`) VALUES 
        ('{$family_escaped}', '{$name_escaped}', '{$import_escaped}', {$font['use']}, {$font['order']}, '{$memo_escaped}')");
}

// 기존 설치본에 저장된 font-family stack 값을 폰트 키 값으로 정규화
$font_family_keys = ['header_font_family', 'title_font_family', 'content_font_family'];

ra0_normalize_config_font_table($config_font_table);

$design_font_result = sql_query("SELECT cd_id, cd_value FROM `{$config_design_table}` WHERE cd_key IN ('header_font_family', 'title_font_family', 'content_font_family')", false);
if ($design_font_result) {
    while ($font_setting = sql_fetch_array($design_font_result)) {
        $normalized_value = ra0_normalize_font_family($font_setting['cd_value']);
        if ($normalized_value !== $font_setting['cd_value']) {
            $normalized_sql = sql_real_escape_string($normalized_value);
            $cd_id = (int)$font_setting['cd_id'];
            sql_query("UPDATE `{$config_design_table}` SET cd_value = '{$normalized_sql}' WHERE cd_id = '{$cd_id}'", false);
        }
    }
}

$preset_result = sql_query("SELECT dp_id, dp_data FROM `{$design_preset_table}`", false);
if ($preset_result) {
    while ($preset = sql_fetch_array($preset_result)) {
        $preset_data = json_decode($preset['dp_data'], true);
        if (!is_array($preset_data)) {
            continue;
        }

        $changed = false;
        foreach ($font_family_keys as $font_key) {
            if (isset($preset_data[$font_key])) {
                $normalized_value = ra0_normalize_font_family($preset_data[$font_key]);
                if ($normalized_value !== $preset_data[$font_key]) {
                    $preset_data[$font_key] = $normalized_value;
                    $changed = true;
                }
            }
        }

        if ($changed) {
            $json_data = sql_real_escape_string(json_encode($preset_data, JSON_UNESCAPED_UNICODE));
            $dp_id = (int)$preset['dp_id'];
            sql_query("UPDATE `{$design_preset_table}` SET dp_data = '{$json_data}' WHERE dp_id = '{$dp_id}'", false);
        }
    }
}

// 인터랙션 설정 필드 자동 추가
$interaction_fields = [
    'cursor_url' => "ALTER TABLE `{$config_design_table}` ADD COLUMN `cursor_url` VARCHAR(255) DEFAULT '' COMMENT '커서 이미지 URL'",
    'cursor_hover_url' => "ALTER TABLE `{$config_design_table}` ADD COLUMN `cursor_hover_url` VARCHAR(255) DEFAULT '' COMMENT 'hover 커서 이미지 URL'",
    'click_sound_url' => "ALTER TABLE `{$config_design_table}` ADD COLUMN `click_sound_url` VARCHAR(255) DEFAULT '' COMMENT '클릭음 파일 URL'",
    'click_sound_volume' => "ALTER TABLE `{$config_design_table}` ADD COLUMN `click_sound_volume` TINYINT(3) DEFAULT 50 COMMENT '클릭음 볼륨 (0-100)'",
    'disable_rightclick' => "ALTER TABLE `{$config_design_table}` ADD COLUMN `disable_rightclick` TINYINT(1) DEFAULT 0 COMMENT '우클릭 금지 (0:허용, 1:금지)'"
];

foreach ($interaction_fields as $field => $alter_sql) {
    // 각 필드의 존재 여부 확인
    $check_sql = "SHOW COLUMNS FROM `{$config_design_table}` LIKE '{$field}'";
    $result = sql_query($check_sql, false);

    // 필드가 없으면 추가
    if (!sql_num_rows($result)) {
        sql_query($alter_sql, false);
    }
}

// 스크롤바 설정 필드 자동 추가
$scrollbar_fields = [
    'scrollbar_hidden' => "ALTER TABLE `{$config_design_table}` ADD COLUMN `scrollbar_hidden` TINYINT(1) DEFAULT 0 COMMENT '스크롤바 숨김 (0:표시, 1:숨김)'",
    'scrollbar_width' => "ALTER TABLE `{$config_design_table}` ADD COLUMN `scrollbar_width` VARCHAR(10) DEFAULT '8' COMMENT '스크롤바 너비 (px)'",
    'scrollbar_track_bg' => "ALTER TABLE `{$config_design_table}` ADD COLUMN `scrollbar_track_bg` VARCHAR(50) DEFAULT '#f1f5f9' COMMENT '스크롤바 트랙 배경색'",
    'scrollbar_track_radius' => "ALTER TABLE `{$config_design_table}` ADD COLUMN `scrollbar_track_radius` VARCHAR(10) DEFAULT '4' COMMENT '스크롤바 트랙 border-radius (px)'",
    'scrollbar_thumb_bg' => "ALTER TABLE `{$config_design_table}` ADD COLUMN `scrollbar_thumb_bg` VARCHAR(50) DEFAULT '#94a3b8' COMMENT '스크롤바 썸 배경색'",
    'scrollbar_thumb_radius' => "ALTER TABLE `{$config_design_table}` ADD COLUMN `scrollbar_thumb_radius` VARCHAR(10) DEFAULT '4' COMMENT '스크롤바 썸 border-radius (px)'"
];

foreach ($scrollbar_fields as $field => $alter_sql) {
    $check_sql = "SHOW COLUMNS FROM `{$config_design_table}` LIKE '{$field}'";
    $result = sql_query($check_sql, false);

    if (!sql_num_rows($result)) {
        sql_query($alter_sql, false);
    }
}

$g5['title'] = '디자인 설정';
include_once(G5_ADMIN_PATH.'/admin.head.php');
?>

<div class="local_desc01 local_desc">
    <p>웹사이트의 전체적인 디자인을 설정합니다. 프리셋을 사용하여 빠르게 디자인을 변경하거나 사용자 정의 설정을 저장할 수 있습니다.</p>
</div>

<?php
// 현재 설정값 불러오기 - get_design_config 사용으로 변경
function get_setting_value($key, $default = '') {
    return get_design_config($key, $default);
}

// 라디오/체크박스 선택 함수
function get_is_checked($key, $value, $default = '') {
    $current = get_design_config($key, $default);
    return $current == $value ? 'checked' : '';
}

// 셀렉트박스 선택 함수  
function get_is_selected($key, $value, $default = '') {
    $current = get_design_config($key, $default);
    if (in_array($key, ['header_font_family', 'title_font_family', 'content_font_family'], true)) {
        $current = ra0_normalize_font_family($current);
        $value = ra0_normalize_font_family($value);
    }
    return $current == $value ? 'selected' : '';
}

// 게시판 목록 가져오기 함수 추가
function get_board_list() {
    $boards = [];
    $sql = "SELECT bo_table, bo_subject FROM " . G5_TABLE_PREFIX . "board ORDER BY bo_subject";
    $result = sql_query($sql);
    
    while ($row = sql_fetch_array($result)) {
        $boards[$row['bo_table']] = $row['bo_subject'];
    }
    
    return $boards;
}
?>


<div class="config_design_con">

<!-- 프리셋 관리 영역 -->
<div class="preset_section">
    <h3>🎨 디자인 프리셋</h3>
    
    <div class="preset_controls">
        <div class="preset_load">
            <select id="preset_select" class="frm_input">
                <option value="">프리셋 선택</option>
                <?php
                $preset_sql = "SELECT * FROM {$design_preset_table} ORDER BY dp_type DESC, dp_datetime DESC";
                $preset_result = sql_query($preset_sql);
                while($preset = sql_fetch_array($preset_result)):
                ?>
                <option value="<?php echo $preset['dp_id'] ?>" data-type="<?php echo $preset['dp_type'] ?>" data-name="<?php echo htmlspecialchars($preset['dp_name']) ?>" data-description="<?php echo htmlspecialchars($preset['dp_description']) ?>">
                    <?php echo $preset['dp_type'] == 'system' ? '[시스템] ' : '[사용자] ' ?>
                    <?php echo $preset['dp_name'] ?>
                </option>
                <?php endwhile; ?>
            </select>
            <button type="button" id="load_preset" class="btn btn_02">불러오기</button>
            <button type="button" id="edit_preset" class="btn btn_03">수정</button>
            <button type="button" id="delete_preset" class="btn btn_frmline">삭제</button>
        </div>

        <div class="preset_save">
            <input type="text" id="preset_name" placeholder="프리셋명 입력" class="frm_input" style="width:150px;">
            <input type="text" id="preset_description" placeholder="설명 (선택사항)" class="frm_input" style="width:200px;">
            <button type="button" id="save_preset" class="btn btn_01">현재 설정 저장</button>
        </div>
    </div>
</div>

<form name="fform" method="post" action="./design_update.php" enctype="multipart/form-data">

<!-- 탭 네비게이션 -->
<div class="tab_navigation">
    <button type="button" class="tab_btn active" data-tab="layout">기본 설정</button>
    <button type="button" class="tab_btn" data-tab="main">메인 편집</button>
</div>

<!-- 레이아웃 탭 -->
<div class="tab_content active" id="layout_tab">
    <div class="design_group_con">
    <!-- 기본 설정 -->
        <div class="design_group">
            <h3>기본 설정</h3>
            <div class="container_grid">
                <div class="setting_item">
                    <label for="favicon_file">파비콘</label>
                    <div class="file_input_with_delete">
                        <input type="file" name="favicon_file" id="favicon_file" class="frm_file" accept="image/x-icon,image/png,image/svg+xml">
                        <button type="button" class="btn_delete_image" data-target="favicon" style="display:<?php echo get_setting_value('favicon_url', '') ? 'inline-block' : 'none' ?>;">삭제</button>
                    </div>
                    <?php if(get_setting_value('favicon_url', '')): ?>
                    <div class="favicon_preview" id="favicon_preview">
                        <img src="<?php echo get_setting_value('favicon_url', '') ?>" alt="파비콘 미리보기" style="width: 32px; height: 32px;">
                    </div>
                    <?php endif; ?>
                    <input type="hidden" name="favicon_url" id="favicon_url" value="<?php echo get_setting_value('favicon_url', '') ?>">
                </div>
                <div class="setting_item">
                    <label for="site_bgm_url">배경음악</label>
                    <div class="url_input_group" style="flex-direction: row">
                        <input type="text" name="site_bgm_url" id="site_bgm_url" class="frm_input" placeholder="YouTube 재생목록 또는 단일 영상 URL" value="<?php echo get_setting_value('site_bgm_url', '') ?>">
                        <button type="button" class="btn_delete_url" data-target="site_bgm_url">삭제</button>
                    </div>
                </div>
                <div class="setting_item">
                    <label for="bgm_skin">BGM 스킨</label>
                    <select name="bgm_skin" id="bgm_skin" class="frm_input">
                        <option value="">기본 스타일</option>
                        <?php
                        $bgm_skin_dir = G5_SKIN_PATH . '/bgm';
                        if (is_dir($bgm_skin_dir)) {
                            $skins = scandir($bgm_skin_dir);
                            foreach ($skins as $skin) {
                                if ($skin === '.' || $skin === '..') continue;
                                if (is_dir($bgm_skin_dir . '/' . $skin)) {
                                    $selected = get_setting_value('bgm_skin', '') === $skin ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($skin) . '" ' . $selected . '>' . htmlspecialchars($skin) . '</option>';
                                }
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="setting_item">
                    <label>대문 사용</label>
                    <div class="radio_group">
                        <label><input type="radio" name="use_intro" value="1" <?php echo get_is_checked('use_intro', '1', '1') ?>> 사용</label>
                        <label><input type="radio" name="use_intro" value="0" <?php echo get_is_checked('use_intro', '0', '1') ?>> 사용안함</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- 로고 & 헤더 설정 그리드 -->
        <div class="design_container_flex">
            <!-- 로고 설정 -->
            <div class="design_group">
                <h3>로고 설정</h3>
                <div class="setting_item">
                    <label>로고 사용</label>
                    <div class="radio_group">
                        <label><input type="radio" name="use_logo" value="1" <?php echo get_is_checked('use_logo', '1', '1') ?> class="toggle_trigger" data-target="logo_settings"> 사용</label>
                        <label><input type="radio" name="use_logo" value="0" <?php echo get_is_checked('use_logo', '0', '1') ?> class="toggle_trigger" data-target="logo_settings"> 사용안함</label>
                    </div>
                    <div class="check_group">
                        <label class="checkbox_label">
                            <input type="checkbox" name="show_logo_in_header" id="show_logo_in_header" value="1" <?php echo get_setting_value('show_logo_in_header', '1') == '1' ? 'checked' : '' ?>>
                            <span>헤더에 로고(#logo) 표시</span>
                        </label>
                    </div>
                </div>

                <div id="logo_settings" class="toggle_settings" style="display: <?php echo get_setting_value('use_logo', '1') == '1' ? 'block' : 'none' ?>;">
                    <div class="setting_item">
                        <div class="grid_2fr">
                            <div class="flex_c_5 flex_1">
                                <label for="logo_image_file">로고 파일 선택</label>
                                <div class="file_input_with_delete">
                                    <input type="file" name="logo_image_file" id="logo_image_file" class="frm_file" accept="image/*">
                                    <button type="button" class="btn_delete_image" data-target="logo" style="display:<?php echo get_setting_value('logo_image_url', '') ? 'inline-block' : 'none' ?>;">삭제</button>
                                </div>
                            </div>

                            <div class="flex_c_5 flex_1">
                                <label for="logo_image_url">또는 URL 입력</label>
                                <div class="url_input_with_delete">
                                    <input type="url" name="logo_image_url" id="logo_image_url" class="frm_input" placeholder="https://example.com/image.jpg" value="<?php echo get_setting_value('logo_image_url', '') ?>">
                                    <button type="button" class="btn_delete_url" data-target="logo_image_url">삭제</button>
                                </div>
                            </div>
                            <div class="image_preview full-width" id="logo_preview">
                                <?php if(get_setting_value('logo_image_url', '')): ?>
                                <img src="<?php echo get_setting_value('logo_image_url', '') ?>" alt="로고 미리보기" class="preview_image">
                                <?php else: ?>
                                <div class="image_preview_placeholder">로고 이미지를 선택해주세요</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 헤더 설정 -->
            <div class="design_group">
                <h3>헤더 설정</h3>
                <div class="setting_item">
                    <label>헤더 사용</label>
                    <div class="radio_group">
                        <label><input type="radio" name="use_header" value="1" <?php echo get_is_checked('use_header', '1', '1') ?> class="toggle_trigger" data-target="header_settings"> 사용</label>
                        <label><input type="radio" name="use_header" value="0" <?php echo get_is_checked('use_header', '0', '1') ?> class="toggle_trigger" data-target="header_settings"> 사용안함</label>
                    </div>
                </div>

                <div id="header_settings" class="toggle_settings" style="display: <?php echo get_setting_value('use_header', '1') == '1' ? 'block' : 'none' ?>;">
                    <div class="setting_item">
                        <div class="grid_2fr">
                            <div class="flex_c_5">
                                <label for="header_position">헤더 위치</label>
                                <select name="header_position" id="header_position" class="frm_input">
                                    <option value="top" <?php echo get_is_selected('header_position', 'top', 'top') ?>>상단</option>
                                    <option value="left" <?php echo get_is_selected('header_position', 'left', 'top') ?>>좌측</option>
                                    <option value="right" <?php echo get_is_selected('header_position', 'right', 'top') ?>>우측</option>
                                </select>
                            </div>

                            <div class="number_item">
                                <label for="header_height">헤더 높이</label>
                                <div class="number_input_group">
                                    <input type="number" name="header_height" value="<?php echo get_setting_value('header_height', '80') ?>" id="header_height" class="frm_input">
                                    <span>px</span>
                                </div>
                            </div>
                        </div>
                        <div class="check_group">
                            <label class="checkbox_label">
                                <input type="checkbox" name="show_menu_in_header" id="show_menu_in_header" value="1" <?php echo get_setting_value('show_menu_in_header', '1') == '1' ? 'checked' : '' ?>>
                                <span>헤더에 메뉴 표시(#hd_menu)</span>
                            </label>
                        </div>
                    </div>
                    <div class="setting_item">
                        <div class="grid_2fr">
                            <div class="flex_c_5">
                                <label for="header_font_family">헤더 폰트</label>
                                <select name="header_font_family" id="header_font_family" class="frm_input">
                                    <?php foreach(get_font_options_for_select() as $value => $text): ?>
                                    <option value="<?php echo htmlspecialchars($value, ENT_QUOTES) ?>" <?php echo get_is_selected('header_font_family', html_entity_decode($value), 'Pretendard') ?>>
                                        <?php echo $text ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="number_item">
                                <label for="header_font_size">폰트 크기</label>
                                <div class="number_input_group">
                                    <input type="number" name="header_font_size" value="<?php echo get_setting_value('header_font_size', '16') ?>" id="header_font_size" class="frm_input">
                                    <span>px</span>
                                </div>
                            </div>
                            <div class="color_item">
                                <label for="header_bg_color">배경색</label>
                                <div class="color-picker-container">
                                    <input type="color" id="header_bg_color_picker" class="color-picker" value="<?php
                                        $current_color = get_setting_value('header_bg_color', '#ffffff');
                                        echo (strpos($current_color, '#') === 0) ? $current_color : '#ffffff';
                                    ?>">
                                    <input type="range" id="header_bg_color_alpha" class="alpha-slider" min="0" max="100" value="100">
                                    <input type="text" name="header_bg_color" id="header_bg_color" class="color-display"
                                        value="<?php echo get_setting_value('header_bg_color', '#ffffff') ?>">
                                </div>
                            </div>

                            <div class="color_item">
                                <label for="header_font_color">폰트 색상</label>
                                <div class="color-picker-container">
                                    <input type="color" id="header_font_color_picker" class="color-picker" value="<?php
                                        $current_color = get_setting_value('header_font_color', '#1e293b');
                                        echo (strpos($current_color, '#') === 0) ? $current_color : '#1e293b';
                                    ?>">
                                    <input type="range" id="header_font_color_alpha" class="alpha-slider" min="0" max="100" value="100">
                                    <input type="text" name="header_font_color" id="header_font_color" class="color-display"
                                        value="<?php echo get_setting_value('header_font_color', '#1e293b') ?>">
                                </div>
                            </div>

                            <div class="flex_c_5">
                                <label for="header_menu_justify">메뉴 주축 정렬</label>
                                <select name="header_menu_justify" id="header_menu_justify" class="frm_input">
                                    <option value="flex-start" <?php echo get_is_selected('header_menu_justify', 'flex-start', 'flex-start') ?>>시작</option>
                                    <option value="center" <?php echo get_is_selected('header_menu_justify', 'center', 'flex-start') ?>>중앙</option>
                                    <option value="flex-end" <?php echo get_is_selected('header_menu_justify', 'flex-end', 'flex-start') ?>>끝</option>
                                </select>
                            </div>

                            <div class="flex_c_5">
                                <label for="header_menu_align">메뉴 교차 정렬</label>
                                <select name="header_menu_align" id="header_menu_align" class="frm_input">
                                    <option value="flex-start" <?php echo get_is_selected('header_menu_align', 'flex-start', 'flex-start') ?>>시작</option>
                                    <option value="center" <?php echo get_is_selected('header_menu_align', 'center', 'flex-start') ?>>중앙</option>
                                    <option value="flex-end" <?php echo get_is_selected('header_menu_align', 'flex-end', 'flex-start') ?>>끝</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 배경 설정 -->
        <div class="design_group">
            <h3>배경 설정</h3>
            <div class="setting_item">
                <div class="grid_2fr">
                    <div class="file_input_group flex_c_5">
                        <label for="bg_image_file">배경 이미지 파일</label>
                        <div class="file_input_with_delete">
                            <input type="file" name="bg_image_file" id="bg_image_file" class="frm_file" accept="image/*">
                            <button type="button" class="btn_delete_image" data-target="bg" style="display:<?php echo get_setting_value('bg_image_url', '') ? 'inline-block' : 'none' ?>;">삭제</button>
                        </div>
                    </div>

                    <div class="url_input_group flex_c_5">
                        <label for="bg_image_url">또는 URL 입력</label>
                        <div class="url_input_with_delete">
                            <input type="url" name="bg_image_url" id="bg_image_url" class="frm_input" placeholder="https://example.com/image.jpg" value="<?php echo get_setting_value('bg_image_url', '') ?>">
                            <button type="button" class="btn_delete_url" data-target="bg_image_url">삭제</button>
                        </div>
                    </div>

                    <div class="grid_3fr">
                        <div class="flex_c_5">
                            <label for="bg_repeat">반복</label>
                            <select name="bg_repeat" id="bg_repeat" class="frm_input">
                                <option value="no-repeat" <?php echo get_is_selected('bg_repeat', 'no-repeat', 'no-repeat') ?>>반복 안함</option>
                                <option value="repeat" <?php echo get_is_selected('bg_repeat', 'repeat', 'no-repeat') ?>>반복</option>
                                <option value="repeat-x" <?php echo get_is_selected('bg_repeat', 'repeat-x', 'no-repeat') ?>>가로 반복</option>
                                <option value="repeat-y" <?php echo get_is_selected('bg_repeat', 'repeat-y', 'no-repeat') ?>>세로 반복</option>
                            </select>
                        </div>

                        <div class="flex_c_5">
                            <label for="bg_position">위치</label>
                            <select name="bg_position" id="bg_position" class="frm_input">
                                <option value="center center" <?php echo get_is_selected('bg_position', 'center center', 'center center') ?>>중앙</option>
                                <option value="left top" <?php echo get_is_selected('bg_position', 'left top', 'center center') ?>>좌상단</option>
                                <option value="center top" <?php echo get_is_selected('bg_position', 'center top', 'center center') ?>>상단</option>
                                <option value="right top" <?php echo get_is_selected('bg_position', 'right top', 'center center') ?>>우상단</option>
                                <option value="left center" <?php echo get_is_selected('bg_position', 'left center', 'center center') ?>>좌측</option>
                                <option value="right center" <?php echo get_is_selected('bg_position', 'right center', 'center center') ?>>우측</option>
                                <option value="left bottom" <?php echo get_is_selected('bg_position', 'left bottom', 'center center') ?>>좌하단</option>
                                <option value="center bottom" <?php echo get_is_selected('bg_position', 'center bottom', 'center center') ?>>하단</option>
                                <option value="right bottom" <?php echo get_is_selected('bg_position', 'right bottom', 'center center') ?>>우하단</option>
                            </select>
                        </div>

                        <div class="flex_c_5">
                            <label for="bg_size">크기</label>
                            <select name="bg_size" id="bg_size" class="frm_input">
                                <option value="cover" <?php echo get_is_selected('bg_size', 'cover', 'cover') ?>>덮기 (cover)</option>
                                <option value="contain" <?php echo get_is_selected('bg_size', 'contain', 'cover') ?>>맞춤 (contain)</option>
                                <option value="auto" <?php echo get_is_selected('bg_size', 'auto', 'cover') ?>>원본 크기</option>
                                <option value="100% 100%" <?php echo get_is_selected('bg_size', '100% 100%', 'cover') ?>>늘이기</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex_c_5">
                        <label for="bg_color">배경색</label>
                        <div class="color-picker-container">
                            <input type="color" id="bg_color_picker" class="color-picker" value="<?php
                                $current_color = get_setting_value('bg_color', '#f7f7f7');
                                echo (strpos($current_color, '#') === 0) ? $current_color : '#f7f7f7';
                            ?>">
                            <input type="range" id="bg_color_alpha" class="alpha-slider" min="0" max="100" value="100">
                            <input type="text" name="bg_color" id="bg_color" class="color-display"
                                value="<?php echo get_setting_value('bg_color', '#f7f7f7') ?>">
                        </div>
                    </div>

                    <div class="image_preview full-width" id="bg_preview">
                        <?php if(get_setting_value('bg_image_url', '')): ?>
                        <img src="<?php echo get_setting_value('bg_image_url', '') ?>" alt="배경 이미지 미리보기" class="preview_image">
                        <?php else: ?>
                        <div class="image_preview_placeholder">배경 이미지를 선택해주세요</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 인터랙션 설정 -->
        <div class="design_group">
            <h3>인터랙션 설정</h3>
            <div class="setting_item">
                <!-- 기본 커서 설정 -->
                <div class="grid_2fr">
                    <div class="flex_c_5 flex_1">
                        <label for="cursor_file">기본 커서 이미지</label>
                        <div class="file_input_with_delete">
                            <input type="file" name="cursor_file" id="cursor_file" class="frm_file" accept="image/png,image/x-icon,.cur">
                            <button type="button" class="btn_delete_image" data-target="cursor" style="display:<?php echo get_setting_value('cursor_url', '') ? 'inline-block' : 'none' ?>;">삭제</button>
                        </div>
                        <span class="frm_info">32x32px 이하 PNG/CUR/ICO 권장</span>
                    </div>

                    <div class="flex_c_5 flex_1">
                        <label for="cursor_url">또는 URL 입력</label>
                        <div class="url_input_with_delete">
                            <input type="url" name="cursor_url" id="cursor_url" class="frm_input" placeholder="비어있으면 기본 커서 사용" value="<?php echo get_setting_value('cursor_url', '') ?>">
                            <button type="button" class="btn_delete_url" data-target="cursor_url">삭제</button>
                        </div>
                    </div>
                </div>

                <!-- Hover 커서 설정 -->
                <div class="grid_2fr">
                    <div class="flex_c_5 flex_1">
                        <label for="cursor_hover_file">Hover 커서 이미지</label>
                        <div class="file_input_with_delete">
                            <input type="file" name="cursor_hover_file" id="cursor_hover_file" class="frm_file" accept="image/png,image/x-icon,.cur">
                            <button type="button" class="btn_delete_image" data-target="cursor_hover" style="display:<?php echo get_setting_value('cursor_hover_url', '') ? 'inline-block' : 'none' ?>;">삭제</button>
                        </div>
                        <span class="frm_info">링크/버튼 위에 마우스 올릴 때</span>
                    </div>

                    <div class="flex_c_5 flex_1">
                        <label for="cursor_hover_url">또는 URL 입력</label>
                        <div class="url_input_with_delete">
                            <input type="url" name="cursor_hover_url" id="cursor_hover_url" class="frm_input" placeholder="비어있으면 기본 hover 사용" value="<?php echo get_setting_value('cursor_hover_url', '') ?>">
                            <button type="button" class="btn_delete_url" data-target="cursor_hover_url">삭제</button>
                        </div>
                    </div>
                </div>

                <!-- 클릭음 설정 -->
                <div class="grid_2fr">
                    <div class="flex_c_5 flex_1">
                        <label for="click_sound_file">클릭음 파일</label>
                        <div class="file_input_with_delete">
                            <input type="file" name="click_sound_file" id="click_sound_file" class="frm_file" accept="audio/mpeg,audio/ogg,audio/wav,.mp3,.ogg,.wav">
                            <button type="button" class="btn_delete_image" data-target="click_sound" style="display:<?php echo get_setting_value('click_sound_url', '') ? 'inline-block' : 'none' ?>;">삭제</button>
                        </div>
                        <span class="frm_info">MP3/OGG/WAV, 짧은 효과음 권장</span>
                    </div>

                    <div class="flex_c_5 flex_1">
                        <label for="click_sound_url">또는 URL 입력</label>
                        <div class="url_input_with_delete">
                            <input type="url" name="click_sound_url" id="click_sound_url" class="frm_input" placeholder="비어있으면 클릭음 없음" value="<?php echo get_setting_value('click_sound_url', '') ?>">
                            <button type="button" class="btn_delete_url" data-target="click_sound_url">삭제</button>
                        </div>
                    </div>
                </div>

                <!-- 클릭음 볼륨 -->
                <div class="flex_c_5">
                    <label for="click_sound_volume">클릭음 볼륨 (<span id="volume_display"><?php echo get_setting_value('click_sound_volume', '50') ?></span>%)</label>
                    <div class="range_input_group">
                        <input type="range" name="click_sound_volume" id="click_sound_volume" min="0" max="100" value="<?php echo get_setting_value('click_sound_volume', '50') ?>" class="volume_slider">
                        <button type="button" id="test_click_sound" class="btn btn_03" style="margin-left: 10px;">미리듣기</button>
                    </div>
                </div>

                <!-- 우클릭 금지 설정 -->
                <div class="flex_c_5" style="margin-top: 20px;">
                    <label>우클릭 금지</label>
                    <div class="check_group">
                        <label class="checkbox_label">
                            <input type="checkbox" name="disable_rightclick" id="disable_rightclick" value="1" <?php echo get_setting_value('disable_rightclick', '0') == '1' ? 'checked' : '' ?>>
                            <span>마우스 오른쪽 버튼 클릭 금지 (PC/모바일)</span>
                        </label>
                    </div>
                    <span class="frm_info">이미지 불펌 방지용. 관리자(최고관리자)는 우클릭이 허용됩니다.</span>
                </div>
            </div>
        </div>

        <!-- 스크롤바 설정 -->
        <div class="design_group">
            <h3>스크롤바 설정</h3>
            <div class="setting_item">
                <!-- 스크롤바 숨김 설정 -->
                <div class="flex_c_5" style="margin-bottom: 20px;">
                    <label>스크롤바 표시</label>
                    <div class="check_group">
                        <label class="checkbox_label">
                            <input type="checkbox" name="scrollbar_hidden" id="scrollbar_hidden" value="1" <?php echo get_setting_value('scrollbar_hidden', '0') == '1' ? 'checked' : '' ?>>
                            <span>스크롤바 숨기기 (브라우저 기본 스크롤바 비표시)</span>
                        </label>
                    </div>
                    <span class="frm_info">체크 시 스크롤바가 숨겨집니다. webkit 기반 브라우저(Chrome, Safari, Edge)에서 적용됩니다.</span>
                </div>

                <!-- 스크롤바 커스텀 설정 (숨기지 않을 때만 표시) -->
                <div id="scrollbar_custom_settings" class="design_container_flex" style="<?php echo get_setting_value('scrollbar_hidden', '0') == '1' ? 'display:none;' : ''; ?>">
                        <div class="number_item">
                            <label for="scrollbar_width">너비</label>
                            <div class="number_input_group">
                                <input type="number" name="scrollbar_width" id="scrollbar_width" class="frm_input" min="4" max="20" value="<?php echo get_setting_value('scrollbar_width', '8') ?>">
                                <span class="unit">px</span>
                            </div>
                        </div>
                        <div class="number_item">
                            <label for="scrollbar_track_radius">트랙 모서리</label>
                            <div class="number_input_group">
                                <input type="number" name="scrollbar_track_radius" id="scrollbar_track_radius" class="frm_input"  min="0" max="20" value="<?php echo get_setting_value('scrollbar_track_radius', '4') ?>">
                                <span class="unit">px</span>
                            </div>
                        </div>
                        <div class="number_item">
                            <label for="scrollbar_thumb_radius">핸들 모서리</label>
                            <div class="number_input_group">
                                <input type="number" name="scrollbar_thumb_radius" id="scrollbar_thumb_radius" class="frm_input" min="0" max="20" value="<?php echo get_setting_value('scrollbar_thumb_radius', '4') ?>">
                                <span class="unit">px</span>
                            </div>
                        </div>
                        <div class="color_item">
                            <label for="scrollbar_track_bg">트랙 배경색</label>
                            <div class="color-picker-container">
                                <input type="color" id="scrollbar_track_bg_picker" class="color-picker" value="<?php
                                    $track_bg = get_setting_value('scrollbar_track_bg', '#f1f5f9');
                                    echo (strpos($track_bg, '#') === 0) ? $track_bg : '#f1f5f9';
                                ?>">
                                <input type="range" id="scrollbar_track_bg_alpha" class="alpha-slider" min="0" max="100" value="<?php
                                    $track_bg = get_setting_value('scrollbar_track_bg', '#f1f5f9');
                                    if (strpos($track_bg, 'rgba') !== false) {
                                        preg_match('/rgba\([^,]+,[^,]+,[^,]+,([^)]+)\)/', $track_bg, $matches);
                                        echo isset($matches[1]) ? (floatval($matches[1]) * 100) : 100;
                                    } else {
                                        echo 100;
                                    }
                                ?>">
                                <input type="text" name="scrollbar_track_bg" id="scrollbar_track_bg" class="color-display" value="<?php echo get_setting_value('scrollbar_track_bg', '#f1f5f9') ?>">
                            </div>
                        </div>
                        <div class="color_item">
                            <label for="scrollbar_thumb_bg">핸들 배경색</label>
                            <div class="color-picker-container">
                                <input type="color" id="scrollbar_thumb_bg_picker" class="color-picker" value="<?php
                                    $thumb_bg = get_setting_value('scrollbar_thumb_bg', '#94a3b8');
                                    echo (strpos($thumb_bg, '#') === 0) ? $thumb_bg : '#94a3b8';
                                ?>">
                                <input type="range" id="scrollbar_thumb_bg_alpha" class="alpha-slider" min="0" max="100" value="<?php
                                    $thumb_bg = get_setting_value('scrollbar_thumb_bg', '#94a3b8');
                                    if (strpos($thumb_bg, 'rgba') !== false) {
                                        preg_match('/rgba\([^,]+,[^,]+,[^,]+,([^)]+)\)/', $thumb_bg, $matches);
                                        echo isset($matches[1]) ? (floatval($matches[1]) * 100) : 100;
                                    } else {
                                        echo 100;
                                    }
                                ?>">
                                <input type="text" name="scrollbar_thumb_bg" id="scrollbar_thumb_bg" class="color-display" value="<?php echo get_setting_value('scrollbar_thumb_bg', '#94a3b8') ?>">
                            </div>
                        </div>
                </div>
            </div>
        </div>

        <div class="design_container_flex">
            <div class="design_group">
                <h3>색상 설정</h3>
                <div class="setting_item flex_c">
                    <div class="color_item">
                        <label for="primary_color">색상 설정</label>
                        <div class="color-picker-container">
                            <input type="color" id="primary_color_picker" class="color-picker" value="<?php 
                                $current_color = get_setting_value('primary_color', '#64748b');
                                echo (strpos($current_color, '#') === 0) ? $current_color : '#64748b';
                            ?>">
                            <input type="range" id="primary_alpha" class="alpha-slider" min="0" max="100" value="<?php 
                                $current_color = get_setting_value('primary_color', '#64748b');
                                if (strpos($current_color, 'rgba') !== false) {
                                    preg_match('/rgba\([^,]+,[^,]+,[^,]+,([^)]+)\)/', $current_color, $matches);
                                    echo isset($matches[1]) ? (floatval($matches[1]) * 100) : 100;
                                } else {
                                    echo 100;
                                }
                            ?>">
                            <input type="text" name="primary_color" id="primary_color" class="color-display"
                                value="<?php echo get_setting_value('primary_color', '#64748b') ?>" 
                                placeholder="transparent, #ffffff, rgba(255,255,255,0.8)">
                        </div>
                    </div>
                    
                    <div class="color_item">
                        <label for="secondary_color">서브 컬러</label>
                        <div class="color-picker-container">
                            <input type="color" id="secondary_color_picker" class="color-picker" value="<?php 
                                $current_color = get_setting_value('secondary_color', '#78716c');
                                echo (strpos($current_color, '#') === 0) ? $current_color : '#78716c';
                            ?>">
                            <input type="range" id="secondary_color_alpha" class="alpha-slider" min="0" max="100" value="100">
                            <input type="text" name="secondary_color" id="secondary_color" class="color-display"
                                value="<?php echo get_setting_value('secondary_color', '#78716c') ?>">
                        </div>
                    </div>
                    
                    <div class="color_item">
                        <label for="accent_color">강조 컬러</label>
                        <div class="color-picker-container">
                            <input type="color" id="accent_color_picker" class="color-picker" value="<?php 
                                $current_color = get_setting_value('accent_color', '#137bea');
                                echo (strpos($current_color, '#') === 0) ? $current_color : '#137bea';
                            ?>">
                            <input type="range" id="accent_color_alpha" class="alpha-slider" min="0" max="100" value="100">
                            <input type="text" name="accent_color" id="accent_color" class="color-display"
                                value="<?php echo get_setting_value('accent_color', '#137bea') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="design_group">
                <h3>컨테이너 설정</h3>
                <div class="setting_item flex_c">
                    <div class="color_item">
                        <label for="container_bg_color">배경색</label>
                        <div class="color-picker-container">
                            <input type="color" id="container_bg_color_picker" class="color-picker" value="<?php 
                                $current_color = get_setting_value('container_bg_color', '#f1f5f9');
                                echo (strpos($current_color, '#') === 0) ? $current_color : '#f1f5f9';
                            ?>">
                            <input type="range" id="container_bg_color_alpha" class="alpha-slider" min="0" max="100" value="100">
                            <input type="text" name="container_bg_color" id="container_bg_color" class="color-display"
                                value="<?php echo get_setting_value('container_bg_color', '#f1f5f9') ?>">
                        </div>
                    </div>
                                        
                    <div class="color_item">
                        <label for="container_border_color">테두리 색상</label>
                        <div class="color-picker-container">
                            <input type="color" id="container_border_color_picker" class="color-picker" value="<?php 
                                $current_color = get_setting_value('container_border_color', '#e2e8f0');
                                echo (strpos($current_color, '#') === 0) ? $current_color : '#e2e8f0';
                            ?>">
                            <input type="range" id="container_border_color_alpha" class="alpha-slider" min="0" max="100" value="100">
                            <input type="text" name="container_border_color" id="container_border_color" class="color-display"
                                value="<?php echo get_setting_value('container_border_color', '#e2e8f0') ?>">
                        </div>
                    </div>
                    
                    <div class="number_item">
                        <label for="container_border_radius">모서리 둥글기</label>
                        <div class="number_input_group">
                            <input type="number" name="container_border_radius" value="<?php echo get_setting_value('container_border_radius', '12') ?>" id="container_border_radius" class="frm_input">
                            <span>px</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="design_group">
            <h3>카드 설정</h3>
                <div class="setting_item flex_c">
                    <div class="color_item">
                        <label for="card_bg_color">배경색</label>
                        <div class="color-picker-container">
                            <input type="color" id="card_bg_color_picker" class="color-picker" value="<?php 
                                $current_color = get_setting_value('card_bg_color', '#ffffff');
                                echo (strpos($current_color, '#') === 0) ? $current_color : '#ffffff';
                            ?>">
                            <input type="range" id="card_bg_color_alpha" class="alpha-slider" min="0" max="100" value="100">
                            <input type="text" name="card_bg_color" id="card_bg_color" class="color-display"
                                value="<?php echo get_setting_value('card_bg_color', '#ffffff') ?>">
                        </div>
                    </div>

                    <!-- 카드 테두리색 -->
                    <div class="color_item">
                        <label for="card_border_color">테두리 색상</label>
                        <div class="color-picker-container">
                            <input type="color" id="card_border_color_picker" class="color-picker" value="<?php 
                                $current_color = get_setting_value('card_border_color', '#e2e8f0');
                                echo (strpos($current_color, '#') === 0) ? $current_color : '#e2e8f0';
                            ?>">
                            <input type="range" id="card_border_color_alpha" class="alpha-slider" min="0" max="100" value="100">
                            <input type="text" name="card_border_color" id="card_border_color" class="color-display"
                                value="<?php echo get_setting_value('card_border_color', '#e2e8f0') ?>">
                        </div>
                    </div>
                    
                    <div class="number_item">
                        <label for="card_border_radius">모서리 둥글기</label>
                        <div class="number_input_group">
                            <input type="number" name="card_border_radius" value="<?php echo get_setting_value('card_border_radius', '8') ?>" id="card_border_radius" class="frm_input">
                            <span>px</span>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="design_group">
                <h3>제목 폰트 설정</h3>
                <div class="setting_item flex_c">
                    <div class="flex_c_5 flex_1">
                        <label for="title_font_family">제목 폰트</label>
                        <select name="title_font_family" id="title_font_family" class="frm_input">
                            <?php foreach(get_font_options_for_select() as $value => $text): ?>
                            <option value="<?php echo htmlspecialchars($value, ENT_QUOTES) ?>" <?php echo get_is_selected('title_font_family', html_entity_decode($value), 'Gothic A1') ?>>
                                <?php echo $text ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="flex_c_5 flex_1">
                        <label for="title_font_size">제목 크기</label>
                        <div class="number_input_group styleX">
                            <input type="number" name="title_font_size" value="<?php echo get_setting_value('title_font_size', '24') ?>" id="title_font_size" class="frm_input">
                            <span>px</span>
                        </div>
                    </div>
                    <div class="flex_c_5">
                        <label for="title_font_color">제목 색상</label>
                        <div class="color-picker-container">
                            <input type="color" id="title_font_color_picker" class="color-picker" value="<?php 
                                $current_color = get_setting_value('title_font_color', '#1e293b');
                                echo (strpos($current_color, '#') === 0) ? $current_color : '#1e293b';
                            ?>">
                            <input type="range" id="title_font_color_alpha" class="alpha-slider" min="0" max="100" value="100">
                            <input type="text" name="title_font_color" id="title_font_color" class="color-display"
                                value="<?php echo get_setting_value('title_font_color', '#1e293b') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="design_group">   
                <h3>본문 폰트 설정</h3>
                <div class="setting_item flex_c">
                    <div class="flex_c_5">
                        <label for="content_font_family">본문 폰트</label>
                        <select name="content_font_family" id="content_font_family" class="frm_input">
                            <?php foreach(get_font_options_for_select() as $value => $text): ?>
                            <option value="<?php echo htmlspecialchars($value, ENT_QUOTES) ?>" <?php echo get_is_selected('content_font_family', html_entity_decode($value), 'Pretendard') ?>>
                                <?php echo $text ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="flex_c_5">
                        <label for="content_font_size">본문 크기</label>
                        <div class="number_input_group styleX">
                            <input type="number" name="content_font_size" value="<?php echo get_setting_value('content_font_size', '14') ?>" id="content_font_size" class="frm_input">
                            <span>px</span>
                        </div>
                    </div>

                    <div class="flex_c_5">
                        <label for="content_font_color">본문 색상</label>
                        <div class="color-picker-container">
                            <input type="color" id="content_font_color_picker" class="color-picker" value="<?php 
                                $current_color = get_setting_value('content_font_color', '#475569');
                                echo (strpos($current_color, '#') === 0) ? $current_color : '#475569';
                            ?>">
                            <input type="range" id="content_font_color_alpha" class="alpha-slider" min="0" max="100" value="100">
                            <input type="text" name="content_font_color" id="content_font_color" class="color-display"
                                value="<?php echo get_setting_value('content_font_color', '#475569') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 요소 설정 -->
        <div class="design_group">
            <h3>요소 설정</h3>
            <div class="container_grid">

                <div class="container_section">
                <h4>입력폼 설정</h4>
                    <div class="setting_item styleX">
                        <div class="color_item">
                            <label for="form_bg_color">배경색</label>
                            <div class="color-picker-container">
                                <input type="color" id="form_bg_color_picker" class="color-picker" value="<?php 
                                    $current_color = get_setting_value('form_bg_color', '#ffffff');
                                    echo (strpos($current_color, '#') === 0) ? $current_color : '#ffffff';
                                ?>">
                                <input type="range" id="form_bg_color_alpha" class="alpha-slider" min="0" max="100" value="100">
                                <input type="text" name="form_bg_color" id="form_bg_color" class="color-display"
                                    value="<?php echo get_setting_value('form_bg_color', '#ffffff') ?>">
                            </div>
                        </div>

                        <div class="color_item">
                            <label for="form_border_color">테두리</label>
                            <div class="color-picker-container">
                                <input type="color" id="form_border_color_picker" class="color-picker" value="<?php 
                                    $current_color = get_setting_value('form_border_color', '#e2e8f0');
                                    echo (strpos($current_color, '#') === 0) ? $current_color : '#e2e8f0';
                                ?>">
                                <input type="range" id="form_border_color_alpha" class="alpha-slider" min="0" max="100" value="100">
                                <input type="text" name="form_border_color" id="form_border_color" class="color-display"
                                    value="<?php echo get_setting_value('form_border_color', '#e2e8f0') ?>">
                            </div>
                        </div>

                        <div class="color_item">
                            <label for="form_text_color">텍스트</label>
                            <div class="color-picker-container">
                                <input type="color" id="form_text_color_picker" class="color-picker" value="<?php 
                                    $current_color = get_setting_value('form_text_color', '#374151');
                                    echo (strpos($current_color, '#') === 0) ? $current_color : '#374151';
                                ?>">
                                <input type="range" id="form_text_color_alpha" class="alpha-slider" min="0" max="100" value="100">
                                <input type="text" name="form_text_color" id="form_text_color" class="color-display"
                                    value="<?php echo get_setting_value('form_text_color', '#374151') ?>">
                            </div>
                        </div>
                        
                        <div class="flex_c_5">
                            <label for="form_border_radius">모서리 둥글기</label>
                            <div class="number_input_group styleX">
                                <input type="number" name="form_border_radius" value="<?php echo get_setting_value('form_border_radius', '8') ?>" id="form_border_radius" class="frm_input">
                                <span>px</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="button_section">
                    <h4>기본 버튼</h4>
                    <div class="button_controls">
                        <div class="color_item">
                            <label for="btn_primary_bg">배경색</label>
                            <div class="color-picker-container">
                                <input type="color" id="btn_primary_bg_picker" class="color-picker" value="<?php 
                                    $current_color = get_setting_value('btn_primary_bg', '#64748b');
                                    echo (strpos($current_color, '#') === 0) ? $current_color : '#64748b';
                                ?>">
                                <input type="range" id="btn_primary_bg_alpha" class="alpha-slider" min="0" max="100" value="100">
                                <input type="text" name="btn_primary_bg" id="btn_primary_bg" class="color-display"
                                    value="<?php echo get_setting_value('btn_primary_bg', '#64748b') ?>">
                            </div>
                        </div>
                        
                        <div class="color_item">
                            <label for="btn_primary_text">텍스트 색상</label>
                            <div class="color-picker-container">
                                <input type="color" id="btn_primary_text_picker" class="color-picker" value="<?php 
                                    $current_color = get_setting_value('btn_primary_text', '#ffffff');
                                    echo (strpos($current_color, '#') === 0) ? $current_color : '#ffffff';
                                ?>">
                                <input type="range" id="btn_primary_text_alpha" class="alpha-slider" min="0" max="100" value="100">
                                <input type="text" name="btn_primary_text" id="btn_primary_text" class="color-display"
                                    value="<?php echo get_setting_value('btn_primary_text', '#ffffff') ?>">
                            </div>
                        </div>
                        
                        <div class="number_item">
                            <label for="btn_primary_radius">모서리 둥글기</label>
                            <div class="number_input_group">
                                <input type="number" name="btn_primary_radius" value="<?php echo get_setting_value('btn_primary_radius', '6') ?>" id="btn_primary_radius" class="frm_input">
                                <span>px</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="button_preview">
                        <button type="button" class="btn_preview" id="primary_preview">Primary Button</button>
                    </div>
                </div>
                
                <div class="button_section">
                    <h4>서브 버튼</h4>
                    <div class="button_controls">
                        <div class="color_item">
                            <label for="btn_secondary_bg">배경색</label>
                            <div class="color-picker-container">
                                <input type="color" id="btn_secondary_bg_picker" class="color-picker" value="<?php 
                                    $current_color = get_setting_value('btn_secondary_bg', '#ffffff');
                                    echo (strpos($current_color, '#') === 0) ? $current_color : '#ffffff';
                                ?>">
                                <input type="range" id="btn_secondary_bg_alpha" class="alpha-slider" min="0" max="100" value="100">
                                <input type="text" name="btn_secondary_bg" id="btn_secondary_bg" class="color-display"
                                    value="<?php echo get_setting_value('btn_secondary_bg', '#ffffff') ?>">
                            </div>
                        </div>
                        
                        <div class="color_item">
                            <label for="btn_secondary_text">텍스트 색상</label>
                            <div class="color-picker-container">
                                <input type="color" id="btn_secondary_text_picker" class="color-picker" value="<?php 
                                    $current_color = get_setting_value('btn_secondary_text', '#64748b');
                                    echo (strpos($current_color, '#') === 0) ? $current_color : '#64748b';
                                ?>">
                                <input type="range" id="btn_secondary_text_alpha" class="alpha-slider" min="0" max="100" value="100">
                                <input type="text" name="btn_secondary_text" id="btn_secondary_text" class="color-display"
                                    value="<?php echo get_setting_value('btn_secondary_text', '#64748b') ?>">
                            </div>
                        </div>
                        
                        <div class="number_item">
                            <label for="btn_secondary_radius">모서리 둥글기</label>
                            <div class="number_input_group">
                                <input type="number" name="btn_secondary_radius" value="<?php echo get_setting_value('btn_secondary_radius', '6') ?>" id="btn_secondary_radius" class="frm_input">
                                <span>px</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="button_preview">
                        <button type="button" class="btn_preview" id="secondary_preview">Secondary Button</button>
                    </div>
                </div>
                
                <div class="button_section">
                    <h4>강조 버튼</h4>
                    <div class="button_controls">
                        <div class="color_item">
                            <label for="btn_accent_bg">배경색</label>
                            <div class="color-picker-container">
                                <input type="color" id="btn_accent_bg_picker" class="color-picker" value="<?php 
                                    $current_color = get_setting_value('btn_accent_bg', '#137bea');
                                    echo (strpos($current_color, '#') === 0) ? $current_color : '#137bea';
                                ?>">
                                <input type="range" id="btn_accent_bg_alpha" class="alpha-slider" min="0" max="100" value="100">
                                <input type="text" name="btn_accent_bg" id="btn_accent_bg" class="color-display"
                                    value="<?php echo get_setting_value('btn_accent_bg', '#137bea') ?>">
                            </div>
                        </div>
                        
                        <div class="color_item">
                            <label for="btn_accent_text">텍스트 색상</label>
                            <div class="color-picker-container">
                                <input type="color" id="btn_accent_text_picker" class="color-picker" value="<?php 
                                    $current_color = get_setting_value('btn_accent_text', '#ffffff');
                                    echo (strpos($current_color, '#') === 0) ? $current_color : '#ffffff';
                                ?>">
                                <input type="range" id="btn_accent_text_alpha" class="alpha-slider" min="0" max="100" value="100">
                                <input type="text" name="btn_accent_text" id="btn_accent_text" class="color-display"
                                    value="<?php echo get_setting_value('btn_accent_text', '#ffffff') ?>">
                            </div>
                        </div>
                        
                        <div class="number_item">
                            <label for="btn_accent_radius">모서리 둥글기</label>
                            <div class="number_input_group">
                                <input type="number" name="btn_accent_radius" value="<?php echo get_setting_value('btn_accent_radius', '6') ?>" id="btn_accent_radius" class="frm_input">
                                <span>px</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="button_preview">
                        <button type="button" class="btn_preview" id="accent_preview">Accent Button</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 메인 편집 탭 -->
<div class="tab_content" id="main_tab">
    <div class="design_group_con">   
        <div class="design_group_con design_group">
            <h3>메인 페이지 설정</h3>
            <div class="design_container_flex">
                <div class="setting_item">
                    <label for="main_type">메인 화면</label>
                    <select name="main_type" id="main_type" class="frm_input">
                        <?php foreach(get_board_options() as $value => $text): ?>
                        <option value="<?php echo htmlspecialchars($value, ENT_QUOTES) ?>" <?php echo get_is_selected('main_type', $value, 'default') ?>><?php echo htmlspecialchars($text, ENT_QUOTES) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="setting_item">
                    <label for="main_board_1">메인 게시판 1</label>
                    <select name="main_board_1" id="main_board_1" class="frm_input">
                        <option value="">선택안함</option>
                        <?php
                        $boards = get_board_list();
                        foreach($boards as $bo_table => $bo_subject):
                        ?>
                        <option value="<?php echo $bo_table ?>" <?php echo get_is_selected('main_board_1', $bo_table, '') ?>>
                            <?php echo $bo_subject ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="setting_item">
                    <label for="main_board_2">메인 게시판 2</label>
                    <select name="main_board_2" id="main_board_2" class="frm_input">
                        <option value="">선택안함</option>
                        <?php foreach($boards as $bo_table => $bo_subject): ?>
                        <option value="<?php echo $bo_table ?>" <?php echo get_is_selected('main_board_2', $bo_table, '') ?>>
                            <?php echo $bo_subject ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="setting_item main_notice_item">
                <label for="main_notice_text">한 줄 공지</label>
                <input type="text" name="main_notice_text" id="main_notice_text" class="frm_input"
                    value="<?php echo get_setting_value('main_notice_text', '한 줄 공지를 입력하여 주십시오.') ?>"
                    placeholder="메인 페이지 상단에 표시될 공지사항">
            </div>

            <div class="setting_item">
                <label for="main_intro_text">소개글</label>
                <textarea name="main_intro_text" id="main_intro_text" class="frm_textarea" rows="5"
                    placeholder="메인 페이지에 표시될 사이트 소개글을 입력하세요"><?php echo get_setting_value('main_intro_text', '') ?></textarea>
            </div>
        </div>

        <!-- 슬라이드 이미지 설정 -->
        <div class="design_group_con design_group">
                <h3>슬라이드 이미지 설정</h3>
                <div class="design_container_flex">
                    <?php for($i = 1; $i <= 3; $i++): ?>
                    <div class="slide_item">
                        <h4>슬라이드 <?php echo $i ?></h4>
                        
                        <div class="slide_content">
                            <div class="slide_image_section">
                                <div class="file_input_group">
                                    <label for="slide_image_file_<?php echo $i ?>">이미지 파일</label>
                                    <div class="file_input_with_delete">
                                        <input type="file" name="slide_image_file_<?php echo $i ?>" id="slide_image_file_<?php echo $i ?>" class="frm_file" accept="image/*">
                                        <button type="button" class="btn_delete_image" data-target="slide_<?php echo $i ?>" style="display:<?php echo get_setting_value('slide_image_'.$i, '') ? 'inline-block' : 'none' ?>;">삭제</button>
                                    </div>
                                </div>
                                
                                <div class="url_input_group">
                                    <label for="slide_image_<?php echo $i ?>">또는 이미지 URL</label>
                                    <div class="url_input_with_delete">
                                        <input type="url" name="slide_image_<?php echo $i ?>" id="slide_image_<?php echo $i ?>" class="frm_input" 
                                            placeholder="https://example.com/image.jpg" 
                                            value="<?php echo get_setting_value('slide_image_'.$i, '') ?>">
                                        <button type="button" class="btn_delete_url" data-target="slide_image_<?php echo $i ?>">삭제</button>
                                    </div>
                                </div>
                                
                                <div class="slide_preview" id="slide_preview_<?php echo $i ?>">
                                    <?php if(get_setting_value('slide_image_'.$i, '')): ?>
                                    <img src="<?php echo get_setting_value('slide_image_'.$i, '') ?>" alt="슬라이드 <?php echo $i ?> 미리보기" class="preview_image">
                                    <?php else: ?>
                                    <div class="slide_preview_placeholder">슬라이드 <?php echo $i ?> 이미지</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="slide_text_section">
                                <div class="text_input_group">
                                    <label for="slide_title_<?php echo $i ?>">제목</label>
                                    <input type="text" name="slide_title_<?php echo $i ?>" id="slide_title_<?php echo $i ?>" class="frm_input" 
                                        placeholder="슬라이드 제목 (선택사항)" 
                                        value="<?php echo get_setting_value('slide_title_'.$i, '') ?>">
                                </div>
                                
                                <div class="text_input_group">
                                    <label for="slide_desc_<?php echo $i ?>">설명</label>
                                    <textarea name="slide_desc_<?php echo $i ?>" id="slide_desc_<?php echo $i ?>" class="frm_textarea" 
                                        placeholder="슬라이드 설명 (선택사항)" rows="3"><?php echo get_setting_value('slide_desc_'.$i, '') ?></textarea>
                                </div>
                                
                                <div class="link_input_group">
                                    <label for="slide_link_<?php echo $i ?>">링크 URL</label>
                                    <input type="url" name="slide_link_<?php echo $i ?>" id="slide_link_<?php echo $i ?>" class="frm_input" 
                                        placeholder="https://example.com (선택사항)" 
                                        value="<?php echo get_setting_value('slide_link_'.$i, '') ?>">
                                </div>
                                
                                <div class="link_target_group">
                                    <label for="slide_link_target_<?php echo $i ?>">링크 열기 방식</label>
                                    <select name="slide_link_target_<?php echo $i ?>" id="slide_link_target_<?php echo $i ?>" class="frm_input">
                                        <option value="_self" <?php echo get_is_selected('slide_link_target_'.$i, '_self', '_self') ?>>현재 창</option>
                                        <option value="_blank" <?php echo get_is_selected('slide_link_target_'.$i, '_blank', '_self') ?>>새 창</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="btn_confirm01 btn_confirm">
    <button type="submit" class="btn btn_01">설정 저장</button>
    <a href="<?php echo G5_ADMIN_URL ?>" class="btn btn_02">관리자 홈</a>
</div>

</form>

</div>

<script>
// 탭 전환 기능
document.querySelectorAll('.tab_btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const targetTab = this.dataset.tab;
        
        // 모든 탭 버튼과 콘텐츠 비활성화
        document.querySelectorAll('.tab_btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab_content').forEach(c => c.classList.remove('active'));
        
        // 선택된 탭 활성화
        this.classList.add('active');
        document.getElementById(targetTab + '_tab').classList.add('active');
    });
});

// 컬러피커 기능
function setupColorPicker(colorId) {
    const picker = document.getElementById(colorId + '_picker');
    const alpha = document.getElementById(colorId + '_alpha');
    const display = document.getElementById(colorId);
    
    if (!picker || !display) return;
    
    // 초기값 설정
    initializeColorPicker(colorId);
    
    function updateColor() {
        const color = picker.value;
        const alphaValue = alpha ? alpha.value / 100 : 1;
        
        if (alphaValue < 1) {
            const r = parseInt(color.substr(1, 2), 16);
            const g = parseInt(color.substr(3, 2), 16);
            const b = parseInt(color.substr(5, 2), 16);
            display.value = `rgba(${r}, ${g}, ${b}, ${alphaValue.toFixed(2)})`;
        } else {
            display.value = color;
        }
        
        updatePreview(colorId);
    }
    
    picker.addEventListener('input', updateColor);
    if (alpha) alpha.addEventListener('input', updateColor);
    
    // display 입력 필드 직접 수정 시에도 동기화
    display.addEventListener('input', function() {
        syncPickerFromDisplay(colorId);
    });
}

// 초기값 설정 함수
function initializeColorPicker(colorId) {
    const picker = document.getElementById(colorId + '_picker');
    const alpha = document.getElementById(colorId + '_alpha');
    const display = document.getElementById(colorId);
    
    if (!picker || !display) return;
    
    const currentValue = display.value;
    
    if (currentValue === 'transparent') {
        picker.value = '#ffffff';
        if (alpha) alpha.value = 0;
    } else if (currentValue.includes('rgba')) {
        const match = currentValue.match(/rgba\((\d+),\s*(\d+),\s*(\d+),\s*([\d.]+)\)/);
        if (match) {
            const [, r, g, b, a] = match;
            const hexColor = '#' + [r, g, b].map(x => {
                const hex = parseInt(x).toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            }).join('');
            
            picker.value = hexColor;
            if (alpha) alpha.value = Math.round(parseFloat(a) * 100);
        }
    } else if (currentValue.startsWith('#')) {
        picker.value = currentValue;
        if (alpha) alpha.value = 100;
    } else {
        // 기본값 설정
        picker.value = '#ffffff';
        if (alpha) alpha.value = 100;
    }
}

// display 필드에서 picker로 동기화
function syncPickerFromDisplay(colorId) {
    const picker = document.getElementById(colorId + '_picker');
    const alpha = document.getElementById(colorId + '_alpha');
    const display = document.getElementById(colorId);
    
    if (!picker || !display) return;
    
    const value = display.value;
    
    if (value === 'transparent') {
        if (alpha) alpha.value = 0;
    } else if (value.includes('rgba')) {
        const match = value.match(/rgba\((\d+),\s*(\d+),\s*(\d+),\s*([\d.]+)\)/);
        if (match) {
            const [, r, g, b, a] = match;
            const hexColor = '#' + [r, g, b].map(x => {
                const hex = parseInt(x).toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            }).join('');
            
            picker.value = hexColor;
            if (alpha) alpha.value = Math.round(parseFloat(a) * 100);
        }
    } else if (value.startsWith('#')) {
        picker.value = value;
        if (alpha) alpha.value = 100;
    }
}

// 컬러 리셋 기능 수정
function resetColor(colorId) {
    const display = document.getElementById(colorId);
    const picker = document.getElementById(colorId + '_picker');
    const alpha = document.getElementById(colorId + '_alpha');
    
    display.value = 'transparent';
    picker.value = '#ffffff';
    if (alpha) alpha.value = 0;
    
    updatePreview(colorId);
}

// 미리보기 업데이트
function updatePreview(colorId) {
    const value = document.getElementById(colorId).value;
    
    // 버튼 미리보기 업데이트
    if (colorId.includes('btn_')) {
        updateButtonPreview(colorId);
    }
}

// 모든 컬러피커 업데이트 함수도 수정
function updateAllColorPickers(settings) {
    const colorInputs = [
        'primary_color', 'secondary_color', 'accent_color',
        'header_bg_color', 'header_font_color',
        'container_bg_color', 'container_border_color',
        'card_bg_color', 'card_border_color',
        'title_font_color', 'content_font_color', 'form_text_color',
        'btn_primary_bg', 'btn_primary_text',
        'btn_secondary_bg', 'btn_secondary_text',
        'btn_accent_bg', 'btn_accent_text',
        'form_bg_color', 'form_border_color', 'bg_color'
    ];
    
    colorInputs.forEach(colorId => {
        if (settings[colorId]) {
            const display = document.getElementById(colorId);
            if (display) {
                display.value = settings[colorId];
                // 동기화 실행
                syncPickerFromDisplay(colorId);
                updatePreview(colorId);
            }
        }
    });
}

// 모든 이미지 미리보기 업데이트
function updateAllImagePreviews(settings) {
    // 로고 이미지
    if (settings.logo_image_url) {
        const logoPreview = document.getElementById('logo_preview');
        if (logoPreview) {
            showImagePreview(logoPreview, settings.logo_image_url);
        }
    }
    
    // 배경 이미지
    if (settings.bg_image_url) {
        const bgPreview = document.getElementById('bg_preview');
        if (bgPreview) {
            showImagePreview(bgPreview, settings.bg_image_url);
        }
    }
    
    // 슬라이드 이미지들
    for (let i = 1; i <= 3; i++) {
        if (settings['slide_image_' + i]) {
            const slidePreview = document.getElementById('slide_preview_' + i);
            if (slidePreview) {
                showImagePreview(slidePreview, settings['slide_image_' + i]);
            }
        }
    }
}

// 모든 버튼 미리보기 업데이트
function updateAllButtonPreviews() {
    ['primary', 'secondary', 'accent'].forEach(type => {
        updateButtonPreview('btn_' + type + '_bg');
    });
}

// 버튼 미리보기 업데이트
function updateButtonPreview(colorId) {
    const type = colorId.includes('primary') ? 'primary' : 
                 colorId.includes('secondary') ? 'secondary' : 'accent';
    
    const preview = document.getElementById(type + '_preview');
    if (!preview) return;
    
    const bgColorElement = document.getElementById('btn_' + type + '_bg');
    const textColorElement = document.getElementById('btn_' + type + '_text');
    const radiusElement = document.getElementById('btn_' + type + '_radius');
    
    if (bgColorElement) {
        const bgColor = bgColorElement.value;
        preview.style.backgroundColor = bgColor;
        preview.style.borderColor = bgColor;
    }
    
    if (textColorElement) {
        preview.style.color = textColorElement.value;
    }
    
    if (radiusElement) {
        preview.style.borderRadius = radiusElement.value + 'px';
    }
}


// 이미지 미리보기 기능
function setupImagePreview(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);

    if (!input || !preview) return;

    if (input.type === 'file') {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    showImagePreview(preview, e.target.result);
                    // 삭제 버튼 표시 (로고, 배경 이미지만)
                    showDeleteButton(inputId);
                };
                reader.readAsDataURL(file);
            }
        });
    } else if (input.type === 'url' || input.type === 'text') {
        input.addEventListener('input', function() {
            if (this.value.trim()) {
                showImagePreview(preview, this.value);
                // 삭제 버튼 표시 (로고, 배경 이미지만)
                showDeleteButton(inputId);
            } else {
                hideImagePreview(preview);
                // 삭제 버튼 숨기기
                hideDeleteButton(inputId);
            }
        });
    }
}

function showImagePreview(preview, src) {
    preview.innerHTML = `<img src="${src}" alt="미리보기" class="preview_image">`;
    preview.classList.add('has_image');
}

function hideImagePreview(preview) {
    const placeholder = preview.dataset.placeholder || '이미지를 선택해주세요';
    preview.innerHTML = `<div class="image_preview_placeholder">${placeholder}</div>`;
    preview.classList.remove('has_image');
}

// 삭제 버튼 표시/숨김
function showDeleteButton(inputId) {
    let deleteBtn = null;
    if (inputId === 'logo_image_file' || inputId === 'logo_image_url') {
        deleteBtn = document.querySelector('.btn_delete_image[data-target="logo"]');
        if (!deleteBtn) deleteBtn = document.querySelector('.btn_delete_url[data-target="logo_image_url"]');
    } else if (inputId === 'bg_image_file' || inputId === 'bg_image_url') {
        deleteBtn = document.querySelector('.btn_delete_image[data-target="bg"]');
        if (!deleteBtn) deleteBtn = document.querySelector('.btn_delete_url[data-target="bg_image_url"]');
    }
    if (deleteBtn) deleteBtn.style.display = 'inline-block';
}

function hideDeleteButton(inputId) {
    let deleteBtn = null;
    if (inputId === 'logo_image_file' || inputId === 'logo_image_url') {
        deleteBtn = document.querySelector('.btn_delete_image[data-target="logo"]');
        if (!deleteBtn) deleteBtn = document.querySelector('.btn_delete_url[data-target="logo_image_url"]');
    } else if (inputId === 'bg_image_file' || inputId === 'bg_image_url') {
        deleteBtn = document.querySelector('.btn_delete_image[data-target="bg"]');
        if (!deleteBtn) deleteBtn = document.querySelector('.btn_delete_url[data-target="bg_image_url"]');
    }
    if (deleteBtn) deleteBtn.style.display = 'none';
}

// 삭제 버튼 기능
function setupDeleteButtons() {
    document.querySelectorAll('.btn_delete_url').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = this.dataset.target;
            const input = document.getElementById(target);

            // 로고, 배경, 슬라이드 이미지, 커서, 클릭음인 경우 서버에서 삭제 처리
            if (target === 'logo_image_url' || target === 'bg_image_url' ||
                target.startsWith('slide_image_') ||
                target === 'cursor_url' ||
                target === 'cursor_hover_url' ||
                target === 'click_sound_url') {

                if (!confirm('파일을 삭제하시겠습니까?\n삭제된 파일은 복구할 수 없습니다.')) {
                    return;
                }

                // AJAX로 서버에 삭제 요청
                fetch('design_image_delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'setting_key=' + encodeURIComponent(target)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        if (input) {
                            input.value = '';
                            input.dispatchEvent(new Event('input'));
                        }
                        // 미리보기 숨기기
                        const previewId = target.replace('_url', '').replace('_image', '') + '_preview';
                        const preview = document.getElementById(previewId);
                        if (preview) hideImagePreview(preview);
                    } else {
                        alert('삭제 실패: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('삭제 중 오류가 발생했습니다: ' + error.message);
                });
            } else {
                // 일반 URL 입력 필드는 그냥 비우기
                if (input) {
                    input.value = '';
                    input.dispatchEvent(new Event('input'));
                }
            }
        });
    });

    document.querySelectorAll('.btn_delete_image').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = this.dataset.target;

            // 서버에서 삭제 처리할 대상인지 확인
            let settingKey = null;
            if (target === 'logo') {
                settingKey = 'logo_image_url';
            } else if (target === 'bg') {
                settingKey = 'bg_image_url';
            } else if (target.startsWith('slide_')) {
                // slide_1 -> slide_image_1
                settingKey = target.replace('slide_', 'slide_image_');
            } else if (target === 'cursor') {
                settingKey = 'cursor_url';
            } else if (target === 'cursor_hover') {
                settingKey = 'cursor_hover_url';
            } else if (target === 'click_sound') {
                settingKey = 'click_sound_url';
            }

            // 서버 삭제 처리가 필요한 경우
            if (settingKey) {
                if (!confirm('파일을 삭제하시겠습니까?\n삭제된 파일은 복구할 수 없습니다.')) {
                    return;
                }

                // AJAX로 서버에 삭제 요청
                fetch('design_image_delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'setting_key=' + encodeURIComponent(settingKey)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        // 입력 필드 비우기
                        let fileInputName = '';
                        if (target === 'logo' || target === 'bg') {
                            fileInputName = target + '_image_file';
                        } else if (target.startsWith('slide_')) {
                            fileInputName = settingKey.replace('slide_image_', 'slide_image_file_');
                        } else if (target === 'cursor') {
                            fileInputName = 'cursor_file';
                        } else if (target === 'cursor_hover') {
                            fileInputName = 'cursor_hover_file';
                        } else if (target === 'click_sound') {
                            fileInputName = 'click_sound_file';
                        }

                        const fileInput = document.querySelector(`input[type="file"][name="${fileInputName}"]`);
                        const urlInput = document.getElementById(settingKey);
                        if (fileInput) fileInput.value = '';
                        if (urlInput) {
                            urlInput.value = '';
                            urlInput.dispatchEvent(new Event('input'));
                        }
                        // 미리보기 숨기기
                        const preview = document.getElementById(target + '_preview') ||
                                       document.getElementById(target.replace('slide_', 'slide_preview_'));
                        if (preview) hideImagePreview(preview);
                        // 삭제 버튼 숨기기
                        this.style.display = 'none';
                    } else {
                        alert('삭제 실패: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('삭제 중 오류가 발생했습니다: ' + error.message);
                });
            } else {
                // 일반 이미지는 그냥 입력 필드 비우기
                const fileInput = document.querySelector(`input[type="file"][data-target="${target}"]`);
                const urlInput = document.querySelector(`input[type="url"][data-target="${target}"]`);
                const preview = document.getElementById(target + '_preview') ||
                               document.getElementById(target.replace('_', '_preview_'));

                if (fileInput) fileInput.value = '';
                if (urlInput) {
                    urlInput.value = '';
                    urlInput.dispatchEvent(new Event('input'));
                }
                if (preview) hideImagePreview(preview);
            }
        });
    });
}

// 프리셋 데이터를 폼에 적용하는 함수
function applyPresetToForm(settings) {
    Object.keys(settings).forEach(key => {
        const element = document.querySelector(`[name="${key}"]`);
        if (element) {
            if (element.type === 'radio') {
                const radio = document.querySelector(`[name="${key}"][value="${settings[key]}"]`);
                if (radio) radio.checked = true;
            } else if (element.type === 'checkbox') {
                element.checked = settings[key] === '1';
            } else {
                element.value = settings[key];
            }
            
            // 이벤트 트리거 (미리보기 업데이트용)
            element.dispatchEvent(new Event('input'));
            element.dispatchEvent(new Event('change'));
        }
    });
    
    // 컬러피커 업데이트
    updateAllColorPickers(settings);
    
    // 이미지 미리보기 업데이트
    updateAllImagePreviews(settings);
    
    // 버튼 미리보기 업데이트
    updateAllButtonPreviews();
}

// load_preset 이벤트 리스너 수정
document.getElementById('load_preset').addEventListener('click', function() {
    const presetId = document.getElementById('preset_select').value;
    if (!presetId) {
        alert('프리셋을 선택해주세요.');
        return;
    }
    
    if (confirm('현재 설정이 프리셋으로 변경됩니다. 계속하시겠습니까?')) {
        fetch('./design_preset_load.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'preset_id=' + presetId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                applyPresetToForm(data.settings); // 폼에 적용
                alert('프리셋이 적용되었습니다.');
            } else {
                alert('프리셋 적용 중 오류가 발생했습니다: ' + (data.message || ''));
            }
        })
        .catch(() => {
            alert('프리셋 적용 중 오류가 발생했습니다.');
        });
    }
});


// 프리셋 수정
document.getElementById('edit_preset').addEventListener('click', function() {
    const select = document.getElementById('preset_select');
    const selectedOption = select.options[select.selectedIndex];

    if (!select.value) {
        alert('수정할 프리셋을 선택해주세요.');
        return;
    }

    // 시스템 프리셋은 수정 불가
    if (selectedOption.dataset.type === 'system') {
        alert('시스템 프리셋은 수정할 수 없습니다.');
        return;
    }

    const currentName = selectedOption.dataset.name;
    const currentDesc = selectedOption.dataset.description;

    const newName = prompt('프리셋명을 입력하세요:', currentName);
    if (newName === null) return; // 취소
    if (!newName.trim()) {
        alert('프리셋명을 입력해주세요.');
        return;
    }

    const newDesc = prompt('설명을 입력하세요 (선택사항):', currentDesc);
    if (newDesc === null) return; // 취소

    fetch('./design_preset_update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'preset_id=' + select.value + '&name=' + encodeURIComponent(newName.trim()) + '&description=' + encodeURIComponent(newDesc.trim())
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('프리셋이 수정되었습니다.');
            location.reload();
        } else {
            alert('프리셋 수정 중 오류가 발생했습니다: ' + (data.message || ''));
        }
    })
    .catch(() => {
        alert('프리셋 수정 중 오류가 발생했습니다.');
    });
});

// 프리셋 삭제
document.getElementById('delete_preset').addEventListener('click', function() {
    const select = document.getElementById('preset_select');
    const selectedOption = select.options[select.selectedIndex];

    if (!select.value) {
        alert('삭제할 프리셋을 선택해주세요.');
        return;
    }

    // 시스템 프리셋은 삭제 불가
    if (selectedOption.dataset.type === 'system') {
        alert('시스템 프리셋은 삭제할 수 없습니다.');
        return;
    }

    const presetName = selectedOption.dataset.name;

    if (!confirm(`'${presetName}' 프리셋을 삭제하시겠습니까?\n이 작업은 되돌릴 수 없습니다.`)) {
        return;
    }

    fetch('./design_preset_delete.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'preset_id=' + select.value
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('프리셋이 삭제되었습니다.');
            location.reload();
        } else {
            alert('프리셋 삭제 중 오류가 발생했습니다: ' + (data.message || ''));
        }
    })
    .catch(() => {
        alert('프리셋 삭제 중 오류가 발생했습니다.');
    });
});

document.getElementById('save_preset').addEventListener('click', function() {
    const name = document.getElementById('preset_name').value.trim();
    const description = document.getElementById('preset_description').value.trim();

    if (!name) {
        alert('프리셋명을 입력해주세요.');
        return;
    }

    if (confirm('현재 설정을 프리셋으로 저장하시겠습니까?')) {
        // 현재 폼의 모든 데이터 수집 (JSON 객체로 변환)
        const form = document.querySelector('form[name="fform"]');
        const settings = {};

        // 모든 input, select, textarea 수집 (파일 필드 제외)
        const formElements = form.querySelectorAll('input:not([type="file"]), select, textarea');
        formElements.forEach(element => {
            if (element.name) {
                // 라디오/체크박스는 체크된 것만
                if (element.type === 'radio' || element.type === 'checkbox') {
                    if (element.checked) {
                        settings[element.name] = element.value;
                    }
                } else {
                    settings[element.name] = element.value;
                }
            }
        });

        // JSON 데이터 구성
        const jsonData = {
            name: name,
            description: description,
            settings: settings
        };

        fetch('./design_preset_save.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(jsonData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('프리셋이 저장되었습니다.');
                document.getElementById('preset_name').value = '';
                document.getElementById('preset_description').value = '';
                location.reload(); // 프리셋 목록 새로고침
            } else {
                alert('프리셋 저장 중 오류가 발생했습니다: ' + (data.message || ''));
            }
        })
        .catch(() => {
            alert('프리셋 저장 중 오류가 발생했습니다.');
        });
    }
});

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    // 컬러피커 설정
    const colorInputs = [
        'primary_color', 'secondary_color', 'accent_color',
        'header_bg_color', 'header_font_color',
        'container_bg_color', 'container_border_color',
        'card_bg_color', 'card_border_color',
        'title_font_color', 'content_font_color', 'form_text_color',
        'btn_primary_bg', 'btn_primary_text',
        'btn_secondary_bg', 'btn_secondary_text',
        'btn_accent_bg', 'btn_accent_text',
        'form_bg_color', 'form_border_color', 'bg_color'
    ];
    
    colorInputs.forEach(setupColorPicker);
    
    // 이미지 미리보기 설정
    setupImagePreview('logo_image_file', 'logo_preview');
    setupImagePreview('logo_image_url', 'logo_preview');
    setupImagePreview('bg_image_file', 'bg_preview');
    setupImagePreview('bg_image_url', 'bg_preview');
    
    // 슬라이드 이미지 미리보기
    for (let i = 1; i <= 3; i++) {
        setupImagePreview('slide_image_file_' + i, 'slide_preview_' + i);
        setupImagePreview('slide_image_' + i, 'slide_preview_' + i);
    }
    
    // 삭제 버튼 설정
    setupDeleteButtons();
    
    // 초기 미리보기 업데이트
    updateButtonPreview('btn_primary_bg');
    updateButtonPreview('btn_secondary_bg');
    updateButtonPreview('btn_accent_bg');

    // 토글 기능 설정
    document.querySelectorAll('.toggle_trigger').forEach(function(radio) {
        radio.addEventListener('change', function() {
            const targetId = this.getAttribute('data-target');
            const targetElement = document.getElementById(targetId);

            if (targetElement) {
                if (this.value === '1') {
                    targetElement.style.display = 'block';
                } else {
                    targetElement.style.display = 'none';
                }
            }
        });
    });

    // 클릭음 볼륨 슬라이더 업데이트
    const volumeSlider = document.getElementById('click_sound_volume');
    const volumeDisplay = document.getElementById('volume_display');

    if (volumeSlider && volumeDisplay) {
        volumeSlider.addEventListener('input', function() {
            volumeDisplay.textContent = this.value;
        });
    }

    // 클릭음 미리듣기 버튼
    const testSoundBtn = document.getElementById('test_click_sound');
    if (testSoundBtn) {
        testSoundBtn.addEventListener('click', function() {
            const soundUrl = document.getElementById('click_sound_url').value;
            const volume = document.getElementById('click_sound_volume').value / 100;

            if (!soundUrl) {
                alert('클릭음 파일 URL을 먼저 입력해주세요.');
                return;
            }

            try {
                const testAudio = new Audio(soundUrl);
                testAudio.volume = volume;
                testAudio.play().catch(function() {
                    alert('사운드 파일을 재생할 수 없습니다. URL을 확인해주세요.');
                });
            } catch (error) {
                alert('사운드 파일 형식이 올바르지 않습니다.');
            }
        });
    }

    // 스크롤바 설정 - 숨김 체크박스 토글
    const scrollbarHidden = document.getElementById('scrollbar_hidden');
    const scrollbarCustomSettings = document.getElementById('scrollbar_custom_settings');

    if (scrollbarHidden && scrollbarCustomSettings) {
        scrollbarHidden.addEventListener('change', function() {
            scrollbarCustomSettings.style.display = this.checked ? 'none' : 'block';
        });
    }

    // 스크롤바 색상 피커 동기화 (alpha slider 포함)
    const scrollbarColorPickers = [
        { picker: 'scrollbar_track_bg_picker', alpha: 'scrollbar_track_bg_alpha', input: 'scrollbar_track_bg' },
        { picker: 'scrollbar_thumb_bg_picker', alpha: 'scrollbar_thumb_bg_alpha', input: 'scrollbar_thumb_bg' }
    ];

    scrollbarColorPickers.forEach(function(item) {
        const picker = document.getElementById(item.picker);
        const alpha = document.getElementById(item.alpha);
        const input = document.getElementById(item.input);

        if (picker && alpha && input) {
            function updateScrollbarColor() {
                const hex = picker.value;
                const alphaValue = alpha.value / 100;

                if (alphaValue < 1) {
                    const r = parseInt(hex.substr(1, 2), 16);
                    const g = parseInt(hex.substr(3, 2), 16);
                    const b = parseInt(hex.substr(5, 2), 16);
                    input.value = `rgba(${r}, ${g}, ${b}, ${alphaValue.toFixed(2)})`;
                } else {
                    input.value = hex;
                }
            }

            picker.addEventListener('input', updateScrollbarColor);
            alpha.addEventListener('input', updateScrollbarColor);

            input.addEventListener('input', function() {
                const val = this.value.trim();
                if (val.match(/^#[0-9A-Fa-f]{6}$/)) {
                    picker.value = val;
                    alpha.value = 100;
                } else if (val.match(/^rgba?\(/)) {
                    const match = val.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([\d.]+))?\)/);
                    if (match) {
                        const r = parseInt(match[1]).toString(16).padStart(2, '0');
                        const g = parseInt(match[2]).toString(16).padStart(2, '0');
                        const b = parseInt(match[3]).toString(16).padStart(2, '0');
                        picker.value = '#' + r + g + b;
                        alpha.value = match[4] ? Math.round(parseFloat(match[4]) * 100) : 100;
                    }
                }
            });
        }
    });
});
</script>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>
                                                
                            
