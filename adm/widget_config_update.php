<?php
$sub_menu = '100130';
include_once('./_common.php');

// 관리자 권한 체크
if (!$is_admin) {
    alert('관리자만 접근할 수 있습니다.');
}

// 위젯 라이브러리 로드
include_once(G5_LIB_PATH.'/widget.lib.php');

$wg_id = isset($_POST['wg_id']) ? trim($_POST['wg_id']) : '';
$action = isset($_POST['action']) ? trim($_POST['action']) : '';

if (empty($wg_id)) {
    alert('위젯 ID가 없습니다.');
}

switch ($action) {
    case 'activate':
        // 위젯 활성화
        $data = array(
            'wg_name' => isset($_POST['wg_name']) ? $_POST['wg_name'] : $wg_id,
            'wg_desc' => isset($_POST['wg_desc']) ? $_POST['wg_desc'] : '',
            'wg_version' => isset($_POST['wg_version']) ? $_POST['wg_version'] : '1.0',
            'wg_position' => isset($_POST['wg_position']) ? $_POST['wg_position'] : 'header',
            'wg_location' => isset($_POST['wg_location']) ? $_POST['wg_location'] : 'main',
            'wg_order' => isset($_POST['wg_order']) ? (int)$_POST['wg_order'] : 0,
            'wg_use' => 1,
            'wg_config' => ''
        );

        if (save_widget($wg_id, $data)) {
            alert('위젯이 활성화되었습니다.', './widget_config.php');
        } else {
            alert('위젯 활성화에 실패했습니다.');
        }
        break;

    case 'deactivate':
        // 위젯 비활성화
        $widget = get_widget_info($wg_id);
        if ($widget) {
            $data = array(
                'wg_name' => $widget['wg_name'],
                'wg_desc' => $widget['wg_desc'],
                'wg_version' => $widget['wg_version'],
                'wg_position' => isset($_POST['wg_position']) ? $_POST['wg_position'] : $widget['wg_position'],
                'wg_location' => isset($_POST['wg_location']) ? $_POST['wg_location'] : (isset($widget['wg_location']) ? $widget['wg_location'] : 'main'),
                'wg_order' => isset($_POST['wg_order']) ? (int)$_POST['wg_order'] : $widget['wg_order'],
                'wg_use' => 0,
                'wg_config' => $widget['wg_config']
            );

            if (save_widget($wg_id, $data)) {
                alert('위젯이 비활성화되었습니다.', './widget_config.php');
            } else {
                alert('위젯 비활성화에 실패했습니다.');
            }
        } else {
            alert('위젯 정보를 찾을 수 없습니다.');
        }
        break;

    case 'delete':
        // 위젯 삭제
        if (delete_widget($wg_id)) {
            alert('위젯이 삭제되었습니다.', './widget_config.php');
        } else {
            alert('위젯 삭제에 실패했습니다.');
        }
        break;

    default:
        alert('잘못된 요청입니다.');
}
?>
