<?php
if (!defined('_GNUBOARD_')) exit;

/**
 * RA0 Edition - 위젯 시스템 라이브러리
 *
 * 사용법:
 * <?php
 * include_once(G5_LIB_PATH.'/widget.lib.php');
 * display_widgets('header'); // header 위치의 위젯들 출력
 * ?>
 */

// 위젯 디렉토리 경로
define('G5_WIDGET_PATH', G5_PATH.'/widget');
define('G5_WIDGET_URL', G5_URL.'/widget');

// 위젯 테이블명 정의 (없으면 기본값 설정)
if (!isset($g5['widget_table'])) {
    $g5['widget_table'] = G5_TABLE_PREFIX.'widget';
}

/**
 * 특정 위치의 활성화된 위젯 목록 가져오기
 *
 * @param string $position 위치 (header, footer, sidebar_left, sidebar_right, content_top, content_bottom)
 * @param string $location 출력 위치 (top: iframe 외부, main: iframe 내부, null: 모두)
 * @return array 위젯 목록
 */
function get_widgets($position, $location = null) {
    global $g5;

    $sql = "SELECT * FROM {$g5['widget_table']}
            WHERE wg_position = '".sql_real_escape_string($position)."'
            AND wg_use = 1";

    // location 필터 추가
    if ($location !== null) {
        $sql .= " AND wg_location = '".sql_real_escape_string($location)."'";
    }

    $sql .= " ORDER BY wg_order ASC, wg_id ASC";

    $result = sql_query($sql);
    $widgets = array();

    while ($row = sql_fetch_array($result)) {
        $widgets[] = $row;
    }

    return $widgets;
}

/**
 * 위젯 로드 및 출력
 *
 * @param array $widget 위젯 정보 배열
 * @return void
 */
function load_widget($widget) {
    global $g5, $config, $member, $is_member, $is_admin;

    $widget_id = $widget['wg_id'];
    $widget_path = G5_WIDGET_PATH.'/'.$widget_id;
    $widget_file = $widget_path.'/widget.php';

    // 위젯 파일 존재 확인
    if (!file_exists($widget_file)) {
        return;
    }

    // 위젯 설정 JSON 디코드
    $wg_config = $widget['wg_config']; // 원본 JSON 문자열 전달
    $wg_location = isset($widget['wg_location']) ? $widget['wg_location'] : 'main';

    // 위젯 출력 (location 정보 전달)
    echo '<div class="widget widget-'.$widget_id.'" data-widget-id="'.$widget_id.'" data-widget-location="'.$wg_location.'">';

    // CSS 파일 있으면 로드
    $css_file = $widget_path.'/style.css';
    if (file_exists($css_file)) {
        echo '<link rel="stylesheet" href="'.G5_WIDGET_URL.'/'.$widget_id.'/style.css">';
    }

    // 위젯 본문 출력
    include $widget_file;

    // JS 파일 있으면 로드
    $js_file = $widget_path.'/script.js';
    if (file_exists($js_file)) {
        echo '<script src="'.G5_WIDGET_URL.'/'.$widget_id.'/script.js"></script>';
    }

    echo '</div>'; // .widget
}

/**
 * 특정 위치의 모든 위젯 출력
 *
 * @param string $position 위치
 * @param string $location 출력 위치 필터 (top/main/null)
 * @return void
 */
function display_widgets($position, $location = null) {
    $widgets = get_widgets($position, $location);

    if (empty($widgets)) {
        return;
    }

    echo '<div class="widget-container widget-position-'.$position.'">';

    foreach ($widgets as $widget) {
        load_widget($widget);
    }

    echo '</div>'; // .widget-container
}

/**
 * /html/widget/ 폴더를 스캔하여 설치 가능한 위젯 목록 반환
 *
 * @return array 위젯 목록 (폴더명 => 위젯 정보)
 */
function scan_widgets() {
    $widget_dir = G5_WIDGET_PATH;

    if (!is_dir($widget_dir)) {
        return array();
    }

    $widgets = array();
    $dirs = glob($widget_dir.'/widget_*', GLOB_ONLYDIR);

    foreach ($dirs as $dir) {
        $widget_id = basename($dir);
        $config_file = $dir.'/config.php';

        // config.php 있으면 로드
        if (file_exists($config_file)) {
            $widget_info = include $config_file;

            if (is_array($widget_info)) {
                $widget_info['id'] = $widget_id;
                $widgets[$widget_id] = $widget_info;
            }
        } else {
            // config.php 없으면 기본 정보
            $widgets[$widget_id] = array(
                'id' => $widget_id,
                'name' => $widget_id,
                'description' => '위젯 설명이 없습니다.',
                'version' => '1.0',
                'author' => 'Unknown'
            );
        }
    }

    return $widgets;
}

/**
 * 위젯 정보 가져오기
 *
 * @param string $widget_id 위젯 ID
 * @return array|false 위젯 정보 또는 false
 */
function get_widget_info($widget_id) {
    global $g5;

    $sql = "SELECT * FROM {$g5['widget_table']} WHERE wg_id = '".sql_real_escape_string($widget_id)."'";
    return sql_fetch($sql);
}

/**
 * 위젯 추가/수정
 *
 * @param string $widget_id 위젯 ID
 * @param array $data 위젯 데이터
 * @return bool 성공 여부
 */
function save_widget($widget_id, $data) {
    global $g5;

    $existing = get_widget_info($widget_id);

    // 필수 필드 기본값
    $wg_name = isset($data['wg_name']) ? $data['wg_name'] : $widget_id;
    $wg_desc = isset($data['wg_desc']) ? $data['wg_desc'] : '';
    $wg_version = isset($data['wg_version']) ? $data['wg_version'] : '1.0';
    $wg_position = isset($data['wg_position']) ? $data['wg_position'] : 'header';
    $wg_location = isset($data['wg_location']) ? $data['wg_location'] : 'main';
    $wg_order = isset($data['wg_order']) ? (int)$data['wg_order'] : 0;
    $wg_use = isset($data['wg_use']) ? (int)$data['wg_use'] : 0;
    $wg_config = isset($data['wg_config']) ? $data['wg_config'] : '';

    // JSON이 아니면 JSON으로 변환
    if (!empty($wg_config) && !is_string($wg_config)) {
        $wg_config = json_encode($wg_config);
    }

    if ($existing) {
        // 수정
        $sql = "UPDATE {$g5['widget_table']} SET
                wg_name = '".sql_real_escape_string($wg_name)."',
                wg_desc = '".sql_real_escape_string($wg_desc)."',
                wg_version = '".sql_real_escape_string($wg_version)."',
                wg_position = '".sql_real_escape_string($wg_position)."',
                wg_location = '".sql_real_escape_string($wg_location)."',
                wg_order = '{$wg_order}',
                wg_use = '{$wg_use}',
                wg_config = '".sql_real_escape_string($wg_config)."'
                WHERE wg_id = '".sql_real_escape_string($widget_id)."'";
    } else {
        // 추가
        $sql = "INSERT INTO {$g5['widget_table']} SET
                wg_id = '".sql_real_escape_string($widget_id)."',
                wg_name = '".sql_real_escape_string($wg_name)."',
                wg_desc = '".sql_real_escape_string($wg_desc)."',
                wg_version = '".sql_real_escape_string($wg_version)."',
                wg_position = '".sql_real_escape_string($wg_position)."',
                wg_location = '".sql_real_escape_string($wg_location)."',
                wg_order = '{$wg_order}',
                wg_use = '{$wg_use}',
                wg_config = '".sql_real_escape_string($wg_config)."',
                wg_datetime = '".G5_TIME_YMDHIS."'";
    }

    return sql_query($sql) ? true : false;
}

/**
 * 위젯 삭제
 *
 * @param string $widget_id 위젯 ID
 * @return bool 성공 여부
 */
function delete_widget($widget_id) {
    global $g5;

    $sql = "DELETE FROM {$g5['widget_table']} WHERE wg_id = '".sql_real_escape_string($widget_id)."'";
    return sql_query($sql) ? true : false;
}
