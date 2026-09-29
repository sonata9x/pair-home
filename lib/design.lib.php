<?php
if (!defined('_GNUBOARD_')) exit;

function ra0_normalize_font_family($font_family) {
    $font_family = trim(stripslashes((string)$font_family));
    if ($font_family === '') {
        return '';
    }

    $font_family = html_entity_decode($font_family, ENT_QUOTES, 'UTF-8');

    if (strpos($font_family, ',') !== false) {
        $font_family = trim(explode(',', $font_family, 2)[0]);
    }

    if (strlen($font_family) >= 2) {
        $quote = $font_family[0];
        if (($quote === '"' || $quote === "'") && substr($font_family, -1) === $quote) {
            $font_family = substr($font_family, 1, -1);
        }
    }

    return trim($font_family);
}

function ra0_css_font_family($font_family) {
    $font_family = ra0_normalize_font_family($font_family);
    if ($font_family === '') {
        return 'Pretendard';
    }

    if (preg_match('/^(serif|sans-serif|monospace|cursive|fantasy|system-ui)$/i', $font_family)) {
        return $font_family;
    }

    if (preg_match('/^[A-Za-z_][A-Za-z0-9_-]*$/', $font_family)) {
        return $font_family;
    }

    return "'" . str_replace("'", "\\'", $font_family) . "'";
}

function ra0_normalize_config_font_table($config_font_table) {
    $result = sql_query("SELECT * FROM `{$config_font_table}` ORDER BY fo_order, fo_id", false);
    if (!$result) {
        return;
    }

    while ($font = sql_fetch_array($result)) {
        $normalized_family = ra0_normalize_font_family($font['fo_family']);
        if (!$normalized_family || $normalized_family === $font['fo_family']) {
            continue;
        }

        $normalized_sql = sql_real_escape_string($normalized_family);
        $old_id = (int)$font['fo_id'];
        $dup = sql_fetch("SELECT * FROM `{$config_font_table}` WHERE fo_family = '{$normalized_sql}' AND fo_id != '{$old_id}'");

        if ($dup) {
            $keep_old = ((int)$font['fo_order'] <= (int)$dup['fo_order']);
            if ($keep_old) {
                $dup_id = (int)$dup['fo_id'];
                sql_query("DELETE FROM `{$config_font_table}` WHERE fo_id = '{$dup_id}'", false);
                sql_query("UPDATE `{$config_font_table}` SET fo_family = '{$normalized_sql}' WHERE fo_id = '{$old_id}'", false);
            } else {
                sql_query("DELETE FROM `{$config_font_table}` WHERE fo_id = '{$old_id}'", false);
            }
        } else {
            sql_query("UPDATE `{$config_font_table}` SET fo_family = '{$normalized_sql}' WHERE fo_id = '{$old_id}'", false);
        }
    }
}

// 기존 폰트 함수들
function get_all_fonts() {
    $config_font_table = G5_TABLE_PREFIX . 'config_font';
    $sql = "SELECT * FROM {$config_font_table} ORDER BY fo_order, fo_id";
    return sql_query($sql);
}

function get_active_fonts() {
    $config_font_table = G5_TABLE_PREFIX . 'config_font';
    $sql = "SELECT * FROM {$config_font_table} WHERE fo_use = '1' ORDER BY fo_order";
    return sql_query($sql);
}

function get_font_imports() {
    $result = get_active_fonts();
    $imports = [];
    
    while($row = sql_fetch_array($result)) {
        if(!empty($row['fo_import'])) {
            $imports[] = $row['fo_import'];
        }
    }
    
    return implode("\n", $imports);
}

function get_font_options_for_select() {
    $result = get_active_fonts();
    $options = [];
    
    while($row = sql_fetch_array($result)) {
        $options[ra0_normalize_font_family($row['fo_family'])] = $row['fo_name'];
    }
    
    return $options;
}

function get_design_config($key = '', $default = '') {
    static $design_config = null;
    
    if ($design_config === null) {
        $design_config = [];
        $config_design_table = G5_TABLE_PREFIX . 'config_design';
        
        // 테이블이 존재하는지 확인
        if (sql_query("DESC {$config_design_table}", false)) {
            $sql = "SELECT cd_key, cd_value FROM {$config_design_table}";
            $result = sql_query($sql);
            
            while ($row = sql_fetch_array($result)) {
                $design_config[$row['cd_key']] = $row['cd_value'];
            }
        }
        
        // 기본값 설정
        $defaults = [
            'use_logo' => '1',
            'logo_image_url' => '', 
            'use_intro' => '0',
            'use_header' => '1',
            'main_type' => 'default',
            'layout_type' => '1col',
            'header_position' => 'top',
            
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
            
            'primary_color' => '#64748b',
            'secondary_color' => '#78716c',
            'accent_color' => '#137bea',
            
            'header_bg_color' => '#ffffff',
            'header_font_color' => '#1e293b',
            'header_font_family' => 'Pretendard',
            'header_font_size' => '16',
            'header_height' => '80',
            
            'container_bg_color' => '#f8fafc',
            'container_border_color' => '#e2e8f0',
            'container_border_radius' => '12',
            'card_bg_color' => '#ffffff',
            'card_border_color' => '#e2e8f0',
            'card_border_radius' => '8',
            
            'title_font_family' => 'Playfair Display',
            'title_font_size' => '24',
            'title_font_color' => '#1e293b',
            'content_font_family' => 'Pretendard',
            'content_font_size' => '14',
            'content_font_color' => '#475569',
            
            'btn_primary_text' => '#ffffff',
            'btn_primary_bg' => '#64748b',
            'btn_primary_radius' => '6',
            'btn_secondary_text' => '#64748b',
            'btn_secondary_bg' => '#ffffff',
            'btn_secondary_radius' => '6',
            'btn_accent_text' => '#ffffff',
            'btn_accent_bg' => '#137bea',
            'btn_accent_radius' => '6',
            
            'form_bg_color' => '#ffffff',
            'form_border_color' => '#e2e8f0',
            'form_border_radius' => '8',
            'form_text_color' => '#374151',
            
            'bg_image_url' => '',
            'bg_color' => '#ffffff',
            'bg_repeat' => 'no-repeat',
            'bg_position' => 'center center',
            'bg_size' => 'cover',

            // 표시 설정
            'show_logo_in_header' => '1',
            'show_menu_in_header' => '1',

            // BGM 스킨
            'bgm_skin' => ''
        ];

        
        foreach ($defaults as $k => $v) {
            if (!isset($design_config[$k])) {
                $design_config[$k] = $v;
            }
        }

        foreach (['header_font_family', 'title_font_family', 'content_font_family'] as $font_key) {
            if (isset($design_config[$font_key])) {
                $design_config[$font_key] = ra0_normalize_font_family($design_config[$font_key]);
            }
        }
    }
    
    if ($key) {
        return isset($design_config[$key]) ? $design_config[$key] : $default;
    }
    
    return $design_config;
}

?>
