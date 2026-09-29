<?php
$sub_menu = "100100";
require_once './_common.php';

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

if (!$_POST) {
    alert('잘못된 접근입니다.');
}

check_admin_token();

// 기본 유효성 검사
$cf_title = strip_tags(clean_xss_attributes($_POST['cf_title'] ?? ''));
$cf_admin = clean_xss_tags($_POST['cf_admin'] ?? '', 1, 1);

if (!$cf_title) {
    alert('홈페이지 제목을 입력해주세요.');
}

$mb = get_member($cf_admin);
if (!$mb['mb_id']) {
    alert('최고관리자 회원아이디가 존재하지 않습니다.');
}

// IP 차단 검사
if (isset($_POST['cf_intercept_ip']) && $_POST['cf_intercept_ip']) {
    $patterns = explode("\n", trim($_POST['cf_intercept_ip']));
    foreach ($patterns as $pattern) {
        $pattern = trim($pattern);
        if (empty($pattern)) continue;
        
        $pattern = str_replace(".", "\.", $pattern);
        $pattern = str_replace("+", "[0-9\.]+", $pattern);
        
        if (preg_match("/^{$pattern}$/", $_SERVER['REMOTE_ADDR'])) {
            alert("현재 접속 IP가 차단될 수 있습니다.");
        }
    }
}

// cf_image 파일 업로드 처리
$cf_image_url = $_POST['cf_image'] ?? '';

if (isset($_FILES['cf_image_file']) && $_FILES['cf_image_file']['error'] == 0) {
    $upload_dir = G5_DATA_PATH . '/design/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // 고정 파일명으로 저장 (덮어쓰기)
    $file_name = 'cf_image.webp';
    $upload_path = $upload_dir . $file_name;

    // 기존 파일 삭제
    if (file_exists($upload_path)) {
        @unlink($upload_path);
    }

    // 이미지 변환 및 저장
    $tmp_file = $_FILES['cf_image_file']['tmp_name'];
    $image_info = getimagesize($tmp_file);

    if ($image_info) {
        $mime = $image_info['mime'];

        // 원본 이미지 로드
        switch ($mime) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($tmp_file);
                break;
            case 'image/png':
                $source = imagecreatefrompng($tmp_file);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($tmp_file);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($tmp_file);
                break;
            default:
                $source = null;
        }

        if ($source) {
            // webp로 저장
            if (function_exists('imagewebp')) {
                imagewebp($source, $upload_path, 85);
            } else {
                // webp 미지원 시 원본 이동
                move_uploaded_file($tmp_file, $upload_path);
            }
            imagedestroy($source);
            $cf_image_url = G5_DATA_URL . '/design/' . $file_name;
        }
    }
}

$update_fields = array(
    'cf_title' => strip_tags(clean_xss_attributes($_POST['cf_title'] ?? '')),
    'cf_description' => strip_tags(clean_xss_attributes($_POST['cf_description'] ?? '')),
    'cf_image' => $cf_image_url,
    'cf_admin' => clean_xss_tags($_POST['cf_admin'] ?? '', 1, 1),
    'cf_visit' => (int) ($_POST['cf_visit'] ?? 1),
    'cf_new' => (int) ($_POST['cf_new'] ?? 1),
    'cf_admin_email' => strip_tags(clean_xss_attributes($_POST['cf_admin_email'] ?? '')),
    'cf_admin_db' => strip_tags(clean_xss_attributes($_POST['cf_admin_db'] ?? '')),
    'cf_admin_email_name' => strip_tags(clean_xss_attributes($_POST['cf_admin_email_name'] ?? '')),
    'cf_cut_name' => (int) ($_POST['cf_cut_name'] ?? 0),
    'cf_login_minutes' => (int) ($_POST['cf_login_minutes'] ?? 10),
    'cf_memo_del' => (int) ($_POST['cf_memo_del'] ?? 180),
    'cf_write_pages' => (int) ($_POST['cf_write_pages'] ?? 10),
    'cf_mobile_pages' => (int) ($_POST['cf_mobile_pages'] ?? 5),
    'cf_delay_sec' => (int) ($_POST['cf_delay_sec'] ?? 30),
    'cf_link_target' => strip_tags(clean_xss_attributes($_POST['cf_link_target'] ?? '_blank')),
    'cf_search_part' => (int) ($_POST['cf_search_part'] ?? 10000),
    'cf_image_extension' => strip_tags(clean_xss_attributes($_POST['cf_image_extension'] ?? '')),
    'cf_movie_extension' => strip_tags(clean_xss_attributes($_POST['cf_movie_extension'] ?? '')),
    'cf_filter' => strip_tags(clean_xss_attributes($_POST['cf_filter'] ?? '')),
    'cf_possible_ip' => strip_tags(clean_xss_attributes(trim($_POST['cf_possible_ip'] ?? ''))),
    'cf_intercept_ip' => strip_tags(clean_xss_attributes(trim($_POST['cf_intercept_ip'] ?? ''))),
    'cf_add_meta' => $_POST['cf_add_meta'] ?? '',  // 메타태그는 HTML 허용 (strip_tags 제거)
    'cf_noindex' => isset($_POST['cf_noindex']) ? 1 : 0,
    'cf_stipulation' => $_POST['cf_stipulation'] ?? '',  // common.php에서 이미 이스케이프됨
    'cf_privacy' => $_POST['cf_privacy'] ?? '',  // common.php에서 이미 이스케이프됨
    'cf_editor' => strip_tags(clean_xss_attributes($_POST['cf_editor'] ?? '')),
    'cf_use_copy_log' => (int) ($_POST['cf_use_copy_log'] ?? 0)
);

// SQL 업데이트 쿼리 생성
// 주의: common.php에서 $_POST에 이미 sql_escape_string (addslashes) 적용됨
// mysqli_real_escape_string 재적용 시 이중 이스케이프 발생하므로 사용하지 않음
$set_clauses = array();
foreach ($update_fields as $field => $value) {
    if (is_int($value)) {
        $set_clauses[] = "{$field} = {$value}";
    } else {
        $set_clauses[] = "{$field} = '{$value}'";
    }
}

$sql = "UPDATE {$g5['config_table']} SET " . implode(', ', $set_clauses);
sql_query($sql);

alert('설정이 저장되었습니다.', './config_form.php');
?>
