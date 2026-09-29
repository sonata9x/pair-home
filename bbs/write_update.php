<?php
include_once('./_common.php');
// 썸네일 라이브러리 로드
if (file_exists(G5_LIB_PATH . '/thumbnail.lib.php')) {
    include_once(G5_LIB_PATH . '/thumbnail.lib.php');
}

// 토큰체크 비활성화 (2025-12-22)
// check_write_token($bo_table);

$g5['title'] = '게시글 저장';

$msg = array();
$uid = isset($_POST['uid']) ? preg_replace('/[^0-9]/', '', $_POST['uid']) : 0;

if($board['bo_use_category']) {
    $ca_name = isset($_POST['ca_name']) ? trim($_POST['ca_name']) : '';
    if(!$ca_name) {
        $msg[] = '<strong>분류</strong>를 선택하세요.';
    } else {
        $categories = array_map('trim', explode("|", $board['bo_category_list'].($is_admin ? '|공지' : '')));
        if(!empty($categories) && !in_array($ca_name, $categories))
            $msg[] = '분류를 올바르게 입력하세요.';

        if(empty($categories))
            $ca_name = '';
    }
} else {
    $ca_name = '';
}

$wr_subject = '';
if (isset($_POST['wr_subject'])) {
    $wr_subject = substr(trim($_POST['wr_subject']),0,255);
    $wr_subject = preg_replace("#[\\\]+$#", "", $wr_subject);
    // 이모지 및 특수문자 허용을 위해 정규화 제거
    // if (function_exists('normalize_utf8_string')) {
    //     $wr_subject = normalize_utf8_string($wr_subject);
    // }
}
if ($wr_subject == '') {
    $msg[] = '<strong>제목</strong>을 입력하세요.';
}

$wr_content = '';
if (isset($_POST['wr_content'])) {
    $wr_content = $_POST['wr_content'];

    // 에디터 이미지를 플레이스홀더로 변환 (안전장치)
    // <img data-placeholder="{이미지:0}" ...> 형태를 {이미지:0}으로 변환
    $wr_content = preg_replace('/<img\s+[^>]*?data-placeholder="({이미지:\d+})"[^>]*?>/is', '$1', $wr_content);

    // ><! 패턴 이스케이프 (HTML 주석 오인 방지)
    $wr_content = str_replace('><!' , '>&lt;!', $wr_content);

    // 이모지 및 특수문자 허용을 위해 정규화 제거
    // if (function_exists('normalize_utf8_string')) {
    //     $wr_content = normalize_utf8_string($wr_content);
    // }
}

$msg = implode('<br>', $msg);
if ($msg) {
    alert($msg);
}

$upload_max_filesize = ini_get('upload_max_filesize');

if (empty($_POST)) {
    alert("파일 또는 글내용의 크기가 서버에서 설정한 값을 넘어 오류가 발생하였습니다.\\npost_max_size=".ini_get('post_max_size')." , upload_max_filesize=".$upload_max_filesize."\\n게시판관리자 또는 서버관리자에게 문의 바랍니다.");
}

$notice_array = explode(",", $board['bo_notice']);
$wr_password = isset($_POST['wr_password']) ? $_POST['wr_password'] : '';
$bf_content = isset($_POST['bf_content']) ? (array) $_POST['bf_content'] : array();
$_POST['html'] = isset($_POST['html']) ? clean_xss_tags($_POST['html'], 1, 1) : '';
$_POST['secret'] = isset($_POST['secret']) ? clean_xss_tags($_POST['secret'], 1, 1) : '';

// 링크 처리 (JSON 데이터의 이중 이스케이프 방지)
$wr_link1 = isset($_POST['wr_link1']) ? stripslashes($_POST['wr_link1']) : '';
$wr_link2 = isset($_POST['wr_link2']) ? stripslashes($_POST['wr_link2']) : '';

if ($w == 'u' || $w == 'r') {
    $wr = get_write($write_table, $wr_id);
    if (!$wr['wr_id']) {
        alert("글이 존재하지 않습니다.\\n글이 삭제되었거나 이동하였을 수 있습니다.");
    }
}

// wr_secret 컬럼 존재 확인 및 추가 (비밀글 기능을 위해)
$field_query = "SHOW COLUMNS FROM $write_table LIKE 'wr_secret'";
$field_exists = sql_fetch($field_query);
if (!$field_exists) {
    sql_query("ALTER TABLE $write_table ADD COLUMN wr_secret tinyint(1) NOT NULL DEFAULT '0' AFTER wr_option", false);
}

// 외부에서 글을 등록할 수 있는 버그가 존재하므로 비밀글은 사용일 경우에만 가능해야 함
if (!$is_admin && !$board['bo_use_secret'] && (stripos($_POST['html'], 'secret') !== false || stripos($_POST['secret'], 'secret') !== false)) {
	alert('비밀글 미사용 게시판 이므로 비밀글로 등록할 수 없습니다.');
}

$secret = '';
// set_secret (myroom_up 등) 또는 secret (기본) 필드 처리
$secret_input = '';
if (isset($_POST['set_secret']) && $_POST['set_secret']) {
    $secret_input = $_POST['set_secret'];
} else if (isset($_POST['secret']) && $_POST['secret']) {
    $secret_input = $_POST['secret'];
}

if ($secret_input) {
    // 'secret' 또는 'member' 허용
    if(preg_match('#(secret|member)#', strtolower($secret_input), $matches))
        $secret = $matches[0];
}

// 외부에서 글을 등록할 수 있는 버그가 존재하므로 비밀글 무조건 사용일때는 관리자를 제외(공지)하고 무조건 비밀글로 등록
if (!$is_admin && $board['bo_use_secret'] == 2) {
    $secret = 'secret';
}

$html = '';
if (isset($_POST['html']) && $_POST['html']) {
    if(preg_match('#html(1|2)#', strtolower($_POST['html']), $matches))
        $html = $matches[0];
}

$notice = '';
if (isset($_POST['notice']) && $_POST['notice']) {
    $notice = $_POST['notice'];
}

for ($i=1; $i<=10; $i++) {
    $var = "wr_$i";
    $$var = "";
    if (isset($_POST['wr_'.$i]) && settype($_POST['wr_'.$i], 'string')) {
        $$var = trim($_POST['wr_'.$i]);

        // 에디터 이미지를 플레이스홀더로 변환 (wr_1~wr_10도 동일하게 처리)
        $$var = preg_replace('/<img\s+[^>]*?data-placeholder="({이미지:\d+})"[^>]*?>/is', '$1', $$var);
    }
}

// wr_order 필드 처리 (RA0 Edition 통합 정렬 시스템)
$wr_order = isset($_POST['wr_order']) ? (int)$_POST['wr_order'] : 0;

// 공통 필드 처리 (모든 스킨에서 동일하게 처리)
$wr_adult = isset($_POST['wr_adult']) && $_POST['wr_adult'] ? 1 : 0;
$wr_wide = isset($_POST['wr_wide']) && $_POST['wr_wide'] ? 1 : 0;
$wr_plip = isset($_POST['wr_plip']) ? sql_real_escape_string($_POST['wr_plip']) : '0';
$wr_url = isset($_POST['wr_url']) ? sql_real_escape_string($_POST['wr_url']) : '';
$wr_type = isset($_POST['wr_type']) ? addslashes(clean_xss_tags(trim($_POST['wr_type']))) : '';

@include_once($board_skin_path.'/write_update.head.skin.php');

run_event('write_update_before', $board, $wr_id, $w, $qstr);

if ($w == '' || $w == 'u') {

    // 외부에서 글을 등록할 수 있는 버그가 존재하므로 공지는 관리자만 등록이 가능해야 함
    if (!$is_admin && $notice) {
        alert('관리자만 공지할 수 있습니다.');
    }

    //회원 자신이 쓴글을 수정할 경우 공지가 풀리는 경우가 있음 
    if($w =='u' && !$is_admin && $board['bo_notice'] && in_array($wr['wr_id'], $notice_array)){
        $notice = 1;
    }

    // 김선용 1.00 : 글쓰기 권한과 수정은 별도로 처리되어야 함
    if($w =='u' && $member['mb_id'] && $wr['mb_id'] === $member['mb_id']) {
        ;
    } else if ($member['mb_level'] < $board['bo_write_level']) {
        alert('글을 쓸 권한이 없습니다.');
    }

} else if ($w == 'r') {

    if (in_array((int)$wr_id, $notice_array)) {
        alert('공지에는 답변 할 수 없습니다.');
    }

    if ($member['mb_level'] < $board['bo_reply_level']) {
        alert('글을 답변할 권한이 없습니다.');
    }

    // 게시글 배열 참조
    $reply_array = &$wr;

    // 최대 답변은 테이블에 잡아놓은 wr_reply 사이즈만큼만 가능합니다.
    if (strlen($reply_array['wr_reply']) == 10) {
        alert("더 이상 답변하실 수 없습니다.\\n답변은 10단계 까지만 가능합니다.");
    }

    $reply_len = strlen($reply_array['wr_reply']) + 1;
    if ($board['bo_reply_order']) {
        $begin_reply_char = 'A';
        $end_reply_char = 'Z';
        $reply_number = +1;
        $sql = " select MAX(SUBSTRING(wr_reply, $reply_len, 1)) as reply from {$write_table} where wr_num = '{$reply_array['wr_num']}' and SUBSTRING(wr_reply, {$reply_len}, 1) <> '' ";
    } else {
        $begin_reply_char = 'Z';
        $end_reply_char = 'A';
        $reply_number = -1;
        $sql = " select MIN(SUBSTRING(wr_reply, {$reply_len}, 1)) as reply from {$write_table} where wr_num = '{$reply_array['wr_num']}' and SUBSTRING(wr_reply, {$reply_len}, 1) <> '' ";
    }
    if ($reply_array['wr_reply']) $sql .= " and wr_reply like '{$reply_array['wr_reply']}%' ";
    $row = sql_fetch($sql);

    if (!$row['reply']) {
        $reply_char = $begin_reply_char;
    } else if ($row['reply'] == $end_reply_char) { // A~Z은 26 입니다.
        alert("더 이상 답변하실 수 없습니다.\\n답변은 26개 까지만 가능합니다.");
    } else {
        $reply_char = chr(ord($row['reply']) + $reply_number);
    }

    $reply = $reply_array['wr_reply'] . $reply_char;

} else {
    alert('w 값이 제대로 넘어오지 않았습니다.');
}

if ($w == '' || $w == 'r') {
    if (isset($_SESSION['ss_datetime'])) {
        if ($_SESSION['ss_datetime'] >= (G5_SERVER_TIME - $config['cf_delay_sec']) && !$is_admin)
            alert('너무 빠른 시간내에 게시물을 연속해서 올릴 수 없습니다.');
    }

    set_session("ss_datetime", G5_SERVER_TIME);
}

if (!isset($_POST['wr_subject']) || !trim($_POST['wr_subject']))
    alert('제목을 입력하여 주십시오.');

$options = array($html, $secret, $notice);

// 스킨에서 전달된 추가 옵션 (테마 등) 포함
$skin_option = isset($_POST['wr_option']) ? trim($_POST['wr_option']) : '';
if ($skin_option && !in_array($skin_option, array('html', 'secret', 'notice'))) {
    $options[] = $skin_option;
}

$wr_option = implode(',', array_filter(array_map('trim', $options)));

// 타임라인 게시판 체크
$bo_type = isset($board['bo_type']) && $board['bo_type'] ? $board['bo_type'] : 'normal';
$is_timeline = ($bo_type == 'timeline');
$tm_origin = 0;
$tm_parent = 0;
$original_tm_parent = 0; // 원본 tm_parent 값 저장 (레시피 처리용)

if ($is_timeline) {
    $tm_parent = isset($_POST['tm_parent']) ? (int)$_POST['tm_parent'] : 0;
    $original_tm_parent = $tm_parent; // 원본 값 저장
}

if ($w == '' || $w == 'r') {

    if ($member['mb_id']) {
        $mb_id = $member['mb_id'];
        // 캐릭터가 있으면 캐릭터명, 없으면 회원명 사용
        $character = null;
        if (is_community_installed() && function_exists('get_character')) {
            $character = get_character($member['mb_id']);
        }
        
        if ($character && !empty($character['ch_name'])) {
            // 커뮤니티 설정(cc_name_priority)에 따라 "캐릭터명" 또는 "캐릭터명 [오너명]"
            $display_name = function_exists('community_format_post_name')
                ? community_format_post_name($character['ch_name'], $member['mb_name'])
                : $character['ch_name'];
            $wr_name = addslashes(clean_xss_tags($display_name));
        } else {
            $wr_name = addslashes(clean_xss_tags($member['mb_name']));
        }
        $wr_password = '';
        $wr_email = addslashes($member['mb_email']);
        $wr_homepage = addslashes(clean_xss_tags($member['mb_homepage']));
    } else {
        $mb_id = '';
        // 비회원의 경우 이름이 누락되는 경우가 있음
        $wr_name = clean_xss_tags(trim($_POST['wr_name']));
        if (!$wr_name)
            alert('이름은 필히 입력하셔야 합니다.');
        $wr_password = get_encrypt_string($wr_password);
        $wr_email = get_email_address(trim($_POST['wr_email']));
        $wr_homepage = clean_xss_tags($wr_homepage);
    }

    if ($w == 'r') {
        // 답변의 원글이 비밀글이라면 비밀번호는 원글과 동일하게 넣는다.
        if ($secret)
            $wr_password = $wr['wr_password'];

        $wr_id = $wr_id . $reply;
        $wr_num = $write['wr_num'];
        $wr_reply = $reply;
    } else {
        $wr_num = 0;
        $wr_reply = '';
    }
    
    // 캐릭터 정보 준비 (위에서 이미 조회된 경우가 아니라면)
    $ch_fields = '';
    if ($member['mb_id'] && !isset($character)) {
        $character = null;
        if (is_community_installed() && function_exists('get_character')) {
            $character = get_character($member['mb_id']);
        }
    }
    
    // 캐릭터 관련 필드 추가
    if ($character && !empty($character['ch_id'])) {
        $ch_fields = ", ch_id = '{$character['ch_id']}'
                     , ch_name = '".addslashes($character['ch_name'])."'
                     , ch_img = '".addslashes($character['ch_portrait_image'])."'
                     , ch_fa_id = '{$character['fa_id']}'
                     , ch_fa_name = '".addslashes($character['fa_name'])."'
                     , ch_fa_color = '".addslashes($character['fa_color'])."'
                     , ch_rk_id = '{$character['rk_id']}'
                     , ch_rk_name = '".addslashes($character['rk_name'])."'";
        
        // 장착된 타이틀 찾기
        if (!empty($character['tt_id'])) {
            $ch_fields .= ", ch_title_id = '{$character['tt_id']}'
                          , ch_title_name = '".addslashes($character['tt_name'])."'";
        } else {
            $ch_fields .= ", ch_title_id = '0'
                          , ch_title_name = ''";
        }
    }
    
    // wr_title, wr_img 필드 추가 (timeline 전용)
    $wr_title = isset($_POST['wr_title']) ? addslashes(clean_xss_tags(trim($_POST['wr_title']))) : '';

    // wr_img 필드 존재 여부 확인 (timeline 게시판 전용 필드)
    $has_wr_img = false;
    $check_columns = sql_query("SHOW COLUMNS FROM $write_table LIKE 'wr_img'");
    if (sql_num_rows($check_columns) > 0) {
        $has_wr_img = true;
    }

    // 썸네일 이미지 처리 (wr_img 필드가 있을 때만)
    $wr_img = '';
    if ($has_wr_img) {
        $thumbnail_type = isset($_POST['thumbnail_type']) ? $_POST['thumbnail_type'] : 'file';

        if ($thumbnail_type == 'url') {
            // URL 입력 방식
            $wr_img = isset($_POST['wr_img_url']) ? clean_xss_tags(trim($_POST['wr_img_url'])) : '';
        } else {
            // 파일 업로드 방식
            if (isset($_FILES['wr_img_file']) && $_FILES['wr_img_file']['error'] == 0) {
                $tmp_file = $_FILES['wr_img_file']['tmp_name'];
                $filename = $_FILES['wr_img_file']['name'];

                // 파일 확장자 체크
                $ext = array_pop(explode('.', $filename));
                $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');

                if (in_array(strtolower($ext), $allowed_ext)) {
                    // 파일명 생성
                    $wr_img_filename = 'thumb_'.md5(uniqid(time(),true)).'.'.$ext;
                    $dest_path = G5_DATA_PATH.'/file/'.$bo_table.'/'.$wr_img_filename;

                    // 디렉토리 생성
                    @mkdir(G5_DATA_PATH.'/file/'.$bo_table, G5_DIR_PERMISSION, true);

                    // 파일 이동
                    if (move_uploaded_file($tmp_file, $dest_path)) {
                        // 썸네일 리사이징은 각 스킨의 write_update.skin.php에서 처리
                        // resize_image($dest_path, 400, 400);
                        $wr_img = $wr_img_filename;
                    }
                }
            } else if ($w == 'u' && !isset($_POST['wr_img_del'])) {
                // 수정 시 기존 이미지 유지
                $wr_img = isset($write['wr_img']) ? $write['wr_img'] : '';
            }
        }

        // 삭제 체크박스 처리
        if ($w == 'u' && isset($_POST['wr_img_del']) && $_POST['wr_img_del'] == '1') {
            // 기존 파일 삭제
            if (isset($write['wr_img']) && $write['wr_img'] && strpos($write['wr_img'], 'http') !== 0) {
                @unlink(G5_DATA_PATH.'/file/'.$bo_table.'/'.$write['wr_img']);
            }
            $wr_img = '';
        }

        $wr_img = addslashes($wr_img);
    }

    // timeline 전용 필드 SQL 구성 (필드가 있을 때만)
    $timeline_fields_sql = '';
    if ($has_wr_img) {
        $timeline_fields_sql = ", wr_title = '$wr_title', wr_img = '$wr_img', wr_type = '$wr_type'";
    }

    $sql = " insert into $write_table
                set wr_num = " . ($w == 'r' ? "'$wr_num'" : "(SELECT IFNULL(MIN(wr_num) - 1, -1) FROM $write_table as sq) ") . ",
                     wr_reply = '$wr_reply',
                     wr_comment = 0,
                     ca_name = '$ca_name',
                     wr_option = '$wr_option',
                     wr_subject = '$wr_subject'
                     $timeline_fields_sql,
                     wr_content = '$wr_content',
                     wr_link1 = '".sql_real_escape_string($wr_link1)."',
                     wr_link2 = '".sql_real_escape_string($wr_link2)."',
                     wr_hit = 0,
                     mb_id = '{$member['mb_id']}',
                     wr_password = '$wr_password',
                     wr_name = '$wr_name',
                     wr_email = '$wr_email',
                     wr_homepage = '$wr_homepage',
                     wr_datetime = '".G5_TIME_YMDHIS."',
                     wr_last = '".G5_TIME_YMDHIS."',
                     wr_ip = '{$_SERVER['REMOTE_ADDR']}',
                     wr_1 = '$wr_1',
                     wr_2 = '$wr_2',
                     wr_3 = '$wr_3',
                     wr_4 = '$wr_4',
                     wr_5 = '$wr_5',
                     wr_6 = '$wr_6',
                     wr_7 = '$wr_7',
                     wr_8 = '$wr_8',
                     wr_9 = '$wr_9',
                     wr_10 = '$wr_10',
                     wr_order = '$wr_order',
                     wr_adult = '$wr_adult',
                     wr_wide = '$wr_wide',
                     wr_plip = '$wr_plip',
                     wr_url = '$wr_url'
                     $ch_fields ";
    sql_query($sql);

    $wr_id = sql_insert_id();

    // 타임라인 처리
    if ($is_timeline) {
        if ($tm_parent > 0) {
            // 답글인 경우
            $parent = sql_fetch(" select tm_origin from $write_table where wr_id = '$tm_parent' ");
            $tm_origin = $parent['tm_origin'] ? $parent['tm_origin'] : $tm_parent;
            // 타임라인 답글은 wr_parent를 tm_parent로 설정
            sql_query(" update $write_table set wr_parent = '$tm_parent' where wr_id = '$wr_id' ");
        } else {
            // 새 글인 경우
            $tm_origin = $wr_id;
            $tm_parent = $wr_id;
            // 일반 글은 자기 자신을 부모로
            sql_query(" update $write_table set wr_parent = '$wr_id' where wr_id = '$wr_id' ");
        }
    } else {
        // 일반 게시판은 자기 자신을 부모로
        sql_query(" update $write_table set wr_parent = '$wr_id' where wr_id = '$wr_id' ");
    }

    // 타임라인 컬럼 처리
    if ($is_timeline) {
        
        // 타임라인 테이블에 컬럼이 있는지 확인 후 추가
        $field_query = "SHOW COLUMNS FROM $write_table LIKE 'tm_origin'";
        $field_exists = sql_fetch($field_query);
        if (!$field_exists) {
            sql_query("ALTER TABLE $write_table ADD COLUMN tm_origin INT(11) NOT NULL DEFAULT '0' AFTER wr_10", false);
            sql_query("ALTER TABLE $write_table ADD COLUMN tm_parent INT(11) NOT NULL DEFAULT '0' AFTER tm_origin", false);
            sql_query("ALTER TABLE $write_table ADD INDEX idx_timeline (tm_origin, tm_parent)", false);
        }
        
        sql_query(" update $write_table set tm_origin = '$tm_origin', tm_parent = '$tm_parent' where wr_id = '$wr_id' ");
    }

    // 새글 INSERT
    sql_query(" insert into {$g5['board_new_table']} ( bo_table, wr_id, wr_parent, bn_datetime, mb_id ) values ( '{$bo_table}', '{$wr_id}', '{$wr_id}', '".G5_TIME_YMDHIS."', '{$member['mb_id']}' ) ");

    // 게시글 1 증가
    sql_query("update {$g5['board_table']} set bo_count_write = bo_count_write + 1 where bo_table = '{$bo_table}'");

    // 쓰기 포인트 부여
    if ($w == '') {
        if ($notice) {
            $bo_notice = $wr_id.($board['bo_notice'] ? ",".$board['bo_notice'] : '');
            sql_query(" update {$g5['board_table']} set bo_notice = '{$bo_notice}' where bo_table = '{$bo_table}' ");
        }

        insert_point($member['mb_id'], $board['bo_write_point'], "{$board['bo_subject']} {$wr_id} 글쓰기", $bo_table, $wr_id, '쓰기');
        
        // 레시피 조합 처리 (타임라인 새 글이고 레시피가 선택된 경우)
        
        // 타임라인 새 글이면 레시피 처리 (w가 빈 문자열이고 original_tm_parent가 0이면 새 글)
        // original_tm_parent가 있으면 답글이므로 레시피 처리하지 않음
        if ($is_timeline && $w == '' && $original_tm_parent == 0) {
            // 복수 레시피 처리를 위한 JSON 데이터 확인
            $recipes_json = isset($_POST['recipes_json']) ? $_POST['recipes_json'] : '';
            $recipes_to_execute = array();
            
            if ($recipes_json) {
                // 백슬래시 제거 (magic_quotes_gpc 문제 해결)
                $recipes_json = stripslashes($recipes_json);
                $recipes_to_execute = json_decode($recipes_json, true);
            }
            
            if (!empty($recipes_to_execute)) {
                
                // community 관련 파일 포함
                if (!defined('G5_COMMUNITY_PATH') && file_exists(G5_PATH.'/community/lib/community.lib.php')) {
                    include_once(G5_PATH.'/community/lib/community.lib.php');
                }
                
                // recipe.lib.php 포함
                if (file_exists(G5_COMMUNITY_PATH.'/lib/recipe.lib.php')) {
                    try {
                        include_once(G5_COMMUNITY_PATH.'/lib/recipe.lib.php');
                        include_once(G5_COMMUNITY_PATH.'/lib/inventory.lib.php');

                    // 현재 캐릭터 조회
                    $recipe_character = function_exists('get_character') ? get_character($member['mb_id']) : null;
                    $recipe_ch_id = $recipe_character ? $recipe_character['ch_id'] : 0;

                    // 복수 레시피 실행
                    $all_results = array();
                    $total_success = 0;
                    $total_attempts = 0;
                    $acquired_items = array();

                    foreach ($recipes_to_execute as $idx => $recipe_data) {
                        $recipe_id = (int)$recipe_data['id'];
                        $count = min((int)$recipe_data['count'], 10); // 최대 10회 제한

                        // 레시피별 결과 저장
                        $recipe_results = array();
                        $recipe_info = get_recipe($recipe_id);

                        for ($i = 0; $i < $count; $i++) {
                            $total_attempts++;

                            // 레시피 실행 (ch_id 사용)
                            if (function_exists('execute_recipe') && $recipe_ch_id > 0) {
                                $result = execute_recipe($recipe_ch_id, $recipe_id);
                                
                                // 재료 부족으로 실패한 경우 중단
                                if (!$result['success'] && strpos($result['message'], '재료') !== false && strpos($result['message'], '부족') !== false) {
                                    $recipe_results[] = array(
                                        'attempt' => $i + 1,
                                        'success' => false,
                                        'message' => '재료 부족으로 중단',
                                        'roll' => 0
                                    );
                                    break;
                                }
                                
                                $recipe_results[] = array(
                                    'attempt' => $i + 1,
                                    'success' => $result['recipe_success'] ?? false,
                                    'message' => $result['message'] ?? '',
                                    'roll' => $result['roll'] ?? 0,
                                    'success_rate' => $result['success_rate'] ?? 0
                                );
                                
                                if ($result['recipe_success']) {
                                    $total_success++;
                                    // 획득 아이템 집계
                                    $item_name = $result['result_item'] ?? $recipe_info['result_item_name'];
                                    $item_qty = $result['result_quantity'] ?? 1;
                                    
                                    if (!isset($acquired_items[$item_name])) {
                                        $acquired_items[$item_name] = 0;
                                    }
                                    $acquired_items[$item_name] += $item_qty;
                                }
                            }
                        }
                        
                        $all_results[] = array(
                            'recipe_id' => $recipe_id,
                            'recipe_name' => $recipe_info['rc_name'],
                            'attempts' => $recipe_results
                        );
                    }
                    
                    // 통합 답글 생성
                    $reply_subject = "[조합 결과] 총 {$total_attempts}회 시도, {$total_success}회 성공";
                    
                    $reply_content = "<div class='recipe-batch-result'>";
                    $reply_content .= "<h3>조합 결과 요약</h3>";
                    $reply_content .= "<div style='border-bottom: 2px solid #ddd; margin: 10px 0;'></div>";
                    
                    foreach ($all_results as $recipe_result) {
                        $recipe_name = $recipe_result['recipe_name'];
                        $attempts = $recipe_result['attempts'];
                        
                        $reply_content .= "<h4>◆ {$recipe_name} (" . count($attempts) . "회)</h4>";
                        $reply_content .= "<ul style='list-style: none; padding: 0;'>";
                        
                        foreach ($attempts as $attempt) {
                            $icon = $attempt['success'] ? '✅' : '❌';
                            $status = $attempt['success'] ? '성공' : '실패';
                            $roll_info = $attempt['roll'] > 0 ? " (주사위: {$attempt['roll']}/{$attempt['success_rate']}%)" : "";
                            
                            $reply_content .= "<li>{$attempt['attempt']}회: {$icon} {$status}{$roll_info}</li>";
                        }
                        $reply_content .= "</ul>";
                    }
                    
                    $reply_content .= "<div style='border-top: 2px solid #ddd; margin: 10px 0; padding-top: 10px;'>";
                    $reply_content .= "<strong>총 결과:</strong> {$total_attempts}회 시도, {$total_success}회 성공<br>";
                    
                    if (!empty($acquired_items)) {
                        $reply_content .= "<strong>획득 아이템:</strong> ";
                        $items_str = array();
                        foreach ($acquired_items as $item => $qty) {
                            $items_str[] = "{$item} ×{$qty}";
                        }
                        $reply_content .= implode(', ', $items_str);
                    }
                    
                    $reply_content .= "</div>";
                    $reply_content .= "</div>";
                    
                    // 타임라인 답글 INSERT
                    $reply_sql = " INSERT INTO $write_table
                        SET wr_num = (SELECT IFNULL(MIN(wr_num) - 1, -1) FROM {$write_table} as sq),
                            wr_reply = '',
                            wr_parent = 0,
                            wr_is_comment = 0,
                            wr_comment = 0,
                            wr_option = 'html1',
                            wr_subject = '".sql_real_escape_string($reply_subject)."',
                            wr_content = '".sql_real_escape_string($reply_content)."',
                            mb_id = 'system',
                            wr_password = '',
                            wr_name = '시스템',
                            wr_email = '',
                            wr_homepage = '',
                            wr_datetime = '".G5_TIME_YMDHIS."',
                            wr_last = '".G5_TIME_YMDHIS."',
                            wr_ip = '{$_SERVER['REMOTE_ADDR']}',
                            wr_hit = 0,
                            wr_order = 0,
                            tm_origin = '{$wr_id}',
                            tm_parent = '{$wr_id}' ";
                    
                    $reply_result = sql_query($reply_sql);
                    
                    if ($reply_result) {
                        $reply_id = sql_insert_id();
                        
                        if ($reply_id) {
                            // wr_parent를 자기 자신으로 업데이트
                            sql_query(" UPDATE $write_table SET wr_parent = '{$reply_id}' WHERE wr_id = '{$reply_id}' ");
                            
                            // 게시글 수 증가
                            sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = bo_count_write + 1 WHERE bo_table = '{$bo_table}' ");
                            
                        }
                    }
                    
                    } catch (Exception $e) {
                        // 조합 처리 실패가 원글 등록까지 막지 않도록 둔다.
                    }
                }
            }
        }

        // ========================================
        // 융합 처리 (타임라인 새 글)
        // ========================================
        if ($is_timeline && $w == '' && $original_tm_parent == 0) {
            $fusion_json = isset($_POST['fusion_json']) ? $_POST['fusion_json'] : '';

            if ($fusion_json) {
                $fusion_json = stripslashes($fusion_json);
                $fusion_data = json_decode($fusion_json, true);

                if (is_array($fusion_data) && !empty($fusion_data['ar_id']) && !empty($fusion_data['items'])) {
                    // 커뮤니티 라이브러리 로드 (G5_COMMUNITY_PATH 보장)
                    if (!defined('G5_COMMUNITY_PATH') && file_exists(G5_PATH.'/community/lib/community.lib.php')) {
                        include_once(G5_PATH.'/community/lib/community.lib.php');
                    }

                    // 융합 + 인벤토리 라이브러리 로드
                    if (defined('G5_COMMUNITY_PATH')) {
                        if (file_exists(G5_COMMUNITY_PATH.'/lib/inventory.lib.php')) {
                            include_once(G5_COMMUNITY_PATH.'/lib/inventory.lib.php');
                        }
                        if (file_exists(G5_COMMUNITY_PATH.'/lib/fusion.lib.php')) {
                            include_once(G5_COMMUNITY_PATH.'/lib/fusion.lib.php');
                        }
                    }

                    $fusion_ch_id = 0;
                    if (function_exists('get_character')) {
                        $fusion_char = get_character($member['mb_id']);
                        $fusion_ch_id = $fusion_char ? (int)$fusion_char['ch_id'] : 0;
                    }

                    if ($fusion_ch_id > 0 && function_exists('execute_fusion')) {
                        try {
                            $fusion_result = execute_fusion($fusion_ch_id, (int)$fusion_data['ar_id'], $fusion_data['items']);

                            if ($fusion_result['success']) {
                                $is_success = $fusion_result['fusion_success'];
                                $rule_name = $fusion_result['rule_name'] ?? '융합';

                                if ($is_success) {
                                    $fusion_reply_subject = "[융합 성공] {$rule_name}";
                                } else {
                                    $fusion_reply_subject = "[융합 실패] {$rule_name}";
                                }

                                // 투입 아이템 목록
                                $input_names = array();
                                if (!empty($fusion_result['input_items'])) {
                                    foreach ($fusion_result['input_items'] as $inp) {
                                        $input_names[] = $inp['it_name'] . ($inp['count'] > 1 ? ' ×' . $inp['count'] : '');
                                    }
                                }

                                // 결과 아이템 이미지
                                $output_img = $fusion_result['output_item_img'] ?? '';
                                $output_img_url = '';
                                if ($output_img) {
                                    $output_img_url = function_exists('get_fusion_item_image_url')
                                        ? get_fusion_item_image_url($output_img)
                                        : G5_DATA_URL . '/community/item/' . ltrim($output_img, '/');
                                }
                                $output_rarity = $fusion_result['output_item_rarity'] ?? 'common';
                                $output_name = htmlspecialchars($fusion_result['output_item_name'] ?? '');
                                $roll_val = (int)($fusion_result['roll'] ?? 0);
                                $rate_val = (int)($fusion_result['success_rate'] ?? 0);
                                $status_class = $is_success ? 'fusion-success' : 'fusion-fail';

                                $fusion_reply = "<div class='fusion-result {$status_class}'>";

                                // 헤더: 성공/실패 배지 + 규칙명
                                $status_label = $is_success ? '성공' : '실패';
                                $fusion_reply .= "<div class='fusion-header'>";
                                $fusion_reply .= "<span class='fusion-badge'>{$status_label}</span>";
                                $fusion_reply .= "<span class='fusion-rule'>" . htmlspecialchars($rule_name) . "</span>";
                                $fusion_reply .= "</div>";

                                // 결과 아이템 카드
                                if ($output_name) {
                                    $fusion_reply .= "<div class='fusion-item-card rarity_{$output_rarity}'>";
                                    if ($output_img_url) {
                                        $fusion_reply .= "<img class='fusion-item-img' src='" . htmlspecialchars($output_img_url) . "' alt='' onerror=\"this.style.display='none'\">";
                                    }
                                    $fusion_reply .= "<div class='fusion-item-info'>";
                                    $fusion_reply .= "<span class='fusion-item-name'>{$output_name}</span>";
                                    if ($is_success) {
                                        $fusion_reply .= "<span class='fusion-item-desc'>획득!</span>";
                                    } else {
                                        $fusion_reply .= "<span class='fusion-item-desc'>위로 보상</span>";
                                    }
                                    $fusion_reply .= "</div>";
                                    $fusion_reply .= "</div>";
                                } else {
                                    // 실패 + 보상 없음
                                    $fusion_reply .= "<div class='fusion-item-card fusion-destroyed'>";
                                    $fusion_reply .= "<div class='fusion-item-info'>";
                                    $fusion_reply .= "<span class='fusion-item-name'>재료 소멸</span>";
                                    $fusion_reply .= "<span class='fusion-item-desc'>결과물 없이 재료가 사라졌습니다.</span>";
                                    $fusion_reply .= "</div>";
                                    $fusion_reply .= "</div>";
                                }

                                // 하단: 투입 재료 + 주사위
                                $fusion_reply .= "<div class='fusion-footer'>";
                                $fusion_reply .= "<div class='fusion-materials'>" . htmlspecialchars(implode(', ', $input_names)) . "</div>";
                                $fusion_reply .= "<div class='fusion-roll'><i class='fa fa-dice'></i> {$roll_val} / {$rate_val}%</div>";
                                $fusion_reply .= "</div>";

                                $fusion_reply .= "</div>";

                                // 시스템 답글 INSERT
                                $fusion_reply_sql = " INSERT INTO $write_table
                                    SET wr_num = (SELECT IFNULL(MIN(wr_num) - 1, -1) FROM {$write_table} as sq),
                                        wr_reply = '',
                                        wr_parent = 0,
                                        wr_is_comment = 0,
                                        wr_comment = 0,
                                        wr_option = 'html1',
                                        wr_subject = '".sql_real_escape_string($fusion_reply_subject)."',
                                        wr_content = '".sql_real_escape_string($fusion_reply)."',
                                        mb_id = 'system',
                                        wr_password = '',
                                        wr_name = '시스템',
                                        wr_email = '',
                                        wr_homepage = '',
                                        wr_datetime = '".G5_TIME_YMDHIS."',
                                        wr_last = '".G5_TIME_YMDHIS."',
                                        wr_ip = '{$_SERVER['REMOTE_ADDR']}',
                                        wr_hit = 0,
                                        wr_order = 0,
                                        tm_origin = '{$wr_id}',
                                        tm_parent = '{$wr_id}' ";

                                $fusion_reply_result = sql_query($fusion_reply_sql);
                                if ($fusion_reply_result) {
                                    $fusion_reply_id = sql_insert_id();
                                    if ($fusion_reply_id) {
                                        sql_query(" UPDATE $write_table SET wr_parent = '{$fusion_reply_id}' WHERE wr_id = '{$fusion_reply_id}' ");
                                        sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = bo_count_write + 1 WHERE bo_table = '{$bo_table}' ");
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            // 융합 처리 실패가 원글 등록까지 막지 않도록 둔다.
                        }
                    }
                }
            }
        }

        // ========================================
        // 스크립트 키워드 처리 (타임라인 새 글)
        // ========================================
        // 글 내용에서 [키워드] 패턴 감지
        preg_match_all('/\[([^\]]+)\]/', $wr_content, $script_keyword_matches);
        $script_keywords = array_unique($script_keyword_matches[1] ?? []);

        // 레시피 관련 키워드 제외 (조합 시스템과 분리)
        $recipe_related = ['조합', '제작', '만들기'];
        $script_keywords = array_filter($script_keywords, function($kw) use ($recipe_related) {
            foreach ($recipe_related as $exclude) {
                if (strpos($kw, $exclude) !== false) return false;
            }
            return true;
        });

        if (!empty($script_keywords)) {
            // script.lib.php 포함
            if (file_exists(G5_PATH.'/community/lib/script.lib.php')) {
                include_once(G5_PATH.'/community/lib/script.lib.php');
            }

            // 캐릭터 정보 조회
            $script_character = null;
            if (function_exists('get_character')) {
                $script_character = get_character($member['mb_id']);
            }
            $character_name = $script_character['ch_name'] ?? $member['mb_name'] ?? '플레이어';

            $script_table = G5_TABLE_PREFIX . 'community_script';
            $script_results = [];
            $all_parsed_texts = [];

            // 전투 컨텍스트 설정 (스크립트 파싱용)
            global $current_battle_context;
            $current_battle_context = [
                'character_id' => $script_character['ch_id'] ?? 0,
                'mb_id' => $member['mb_id']
            ];

            foreach ($script_keywords as $keyword) {
                // DB에서 스크립트 조회 (대괄호 포함/미포함 둘 다 검색)
                $keyword_with_brackets = '[' . $keyword . ']';
                $script_sql = "SELECT sk_id, sk_keyword, sk_script, sk_next_keyword, sk_required_item, sk_category
                              FROM {$script_table}
                              WHERE (sk_keyword = '".sql_real_escape_string($keyword)."'
                                     OR sk_keyword = '".sql_real_escape_string($keyword_with_brackets)."')
                              AND sk_use = 1
                              LIMIT 1";
                $script_data = sql_fetch($script_sql);

                if ($script_data && !empty($script_data['sk_script'])) {
                    // 필요 아이템 체크
                    $can_execute = true;
                    $item_check_message = '';

                    if (!empty($script_data['sk_required_item'])) {
                        if (function_exists('get_inventory_item_name')) {
                            $has_item = get_inventory_item_name($member['mb_id'], $script_data['sk_required_item']);
                            if (!$has_item) {
                                $can_execute = false;
                                $item_check_message = "[{$keyword}] 실행 실패: {$script_data['sk_required_item']}이(가) 필요합니다.";
                            }
                        }
                    }

                    if ($can_execute) {
                        // 스크립트 파싱
                        $parsed_text = '';
                        if (function_exists('parse_field_script')) {
                            $parsed = parse_field_script($script_data['sk_script'], $character_name);
                            // parse_field_script가 배열을 반환할 수도 있음
                            $parsed_text = is_array($parsed) ? ($parsed['text'] ?? '') : $parsed;
                        } else {
                            // 기본 변수 치환만 수행
                            $parsed_text = str_replace('{name}', $character_name, $script_data['sk_script']);
                        }

                        $script_results[] = [
                            'keyword' => $keyword,
                            'category' => $script_data['sk_category'] ?? '일반',
                            'parsed_text' => $parsed_text,
                            'success' => true
                        ];

                        if (!empty($parsed_text)) {
                            $all_parsed_texts[] = $parsed_text;
                        }
                    } else {
                        // 아이템 부족으로 실패
                        $script_results[] = [
                            'keyword' => $keyword,
                            'category' => $script_data['sk_category'] ?? '일반',
                            'parsed_text' => $item_check_message,
                            'success' => false
                        ];
                        $all_parsed_texts[] = $item_check_message;
                    }
                }
            }

            // 스크립트 실행 결과가 있으면 시스템 답글 생성
            if (!empty($all_parsed_texts)) {
                $script_reply_subject = "[스크립트 실행 결과]";

                $script_reply_content = "<div class='script-result'>";

                foreach ($script_results as $result) {
                    $status_icon = $result['success'] ? '✅' : '❌';
                    $script_reply_content .= "<div class='script-item'>";
                    $script_reply_content .= "<div class='script-keyword'>{$status_icon} [{$result['keyword']}]</div>";
                    $script_reply_content .= "<div class='script-text'>{$result['parsed_text']}</div>";
                    $script_reply_content .= "</div>";
                }

                $script_reply_content .= "</div>";

                // 시스템 답글 INSERT
                $script_reply_sql = " INSERT INTO $write_table
                    SET wr_num = (SELECT IFNULL(MIN(wr_num) - 1, -1) FROM {$write_table} as sq),
                        wr_reply = '',
                        wr_parent = 0,
                        wr_is_comment = 0,
                        wr_comment = 0,
                        wr_option = 'html1',
                        wr_subject = '".sql_real_escape_string($script_reply_subject)."',
                        wr_content = '".sql_real_escape_string($script_reply_content)."',
                        mb_id = 'system',
                        wr_password = '',
                        wr_name = '시스템',
                        wr_email = '',
                        wr_homepage = '',
                        wr_datetime = '".G5_TIME_YMDHIS."',
                        wr_last = '".G5_TIME_YMDHIS."',
                        wr_ip = '{$_SERVER['REMOTE_ADDR']}',
                        wr_hit = 0,
                        wr_order = 0,
                        tm_origin = '{$wr_id}',
                        tm_parent = '{$wr_id}' ";

                $script_reply_result = sql_query($script_reply_sql);

                if ($script_reply_result) {
                    $script_reply_id = sql_insert_id();

                    if ($script_reply_id) {
                        // wr_parent를 자기 자신으로 업데이트
                        sql_query(" UPDATE $write_table SET wr_parent = '{$script_reply_id}' WHERE wr_id = '{$script_reply_id}' ");

                        // 게시글 수 증가
                        sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = bo_count_write + 1 WHERE bo_table = '{$bo_table}' ");
                    }
                }
            }
        }
    } else {
        // 답변은 코멘트 포인트를 부여함
        insert_point($member['mb_id'], $board['bo_comment_point'], "{$board['bo_subject']} {$wr_id} 글답변", $bo_table, $wr_id, '쓰기');
    }
}  else if ($w == 'u') {
    // 다중 탭 지원 검증 - 비활성화 (2025-12-26)
    // 인라인 수정 폼 등 write.php를 거치지 않는 수정 방식과 충돌
    // 권한 검증은 아래에서 별도로 수행됨
    /*
    $write_token = $bo_table . '_' . $wr_id;
    $ss_write_tokens = get_session('ss_write_tokens') ?: [];

    if (!isset($ss_write_tokens[$write_token])) {
        alert('올바른 방법으로 수정하여 주십시오.', get_pretty_url($bo_table));
    }

    // 토큰 만료 체크 (24시간)
    $token_age = time() - $ss_write_tokens[$write_token];
    if ($token_age > 86400) {
        alert('수정 시간이 만료되었습니다. 다시 시도해주세요.', get_pretty_url($bo_table));
    }

    // 사용한 토큰 제거
    unset($ss_write_tokens[$write_token]);
    set_session('ss_write_tokens', $ss_write_tokens);
    */

    $return_url = get_pretty_url($bo_table, $wr_id);

    if ($is_admin) // 관리자 통과 (super, group, board 모두)
        ;
    else if ($member['mb_id']) {
        if ($member['mb_id'] != $write['mb_id'])
            alert('자신의 글이 아니므로 수정할 수 없습니다.', $return_url);
    } else {
        if ($write['mb_id'])
            alert('로그인 후 수정하세요.', G5_BBS_URL.'/login.php?url='.urlencode($return_url));
    }

    if ($member['mb_id']) {
        // 자신의 글이라면
        if ($member['mb_id'] === $wr['mb_id']) {
            $mb_id = $member['mb_id'];
            // 캐릭터가 있으면 캐릭터명(설정에 따라 "캐릭터명 [오너명]"), 없으면 회원명 사용
            if (is_community_installed() && function_exists('get_character')) {
                $character = get_character($member['mb_id']);
                if ($character && !empty($character['ch_name'])) {
                    $display_name = function_exists('community_format_post_name')
                        ? community_format_post_name($character['ch_name'], $member['mb_name'])
                        : $character['ch_name'];
                    $wr_name = addslashes(clean_xss_tags($display_name));
                } else {
                    $wr_name = addslashes(clean_xss_tags($member['mb_name']));
                }
            } else {
                $wr_name = addslashes(clean_xss_tags($member['mb_name']));
            }
            $wr_email = addslashes($member['mb_email']);
            $wr_homepage = addslashes(clean_xss_tags($member['mb_homepage']));
        } else {
            $mb_id = $wr['mb_id'];
            if(isset($_POST['wr_name']) && $_POST['wr_name'])
                $wr_name = clean_xss_tags(trim($_POST['wr_name']));
            else
                $wr_name = addslashes(clean_xss_tags($wr['wr_name']));
            if(isset($_POST['wr_email']) && $_POST['wr_email'])
                $wr_email = get_email_address(trim($_POST['wr_email']));
            else
                $wr_email = addslashes($wr['wr_email']);
            if(isset($_POST['wr_homepage']) && $_POST['wr_homepage'])
                $wr_homepage = addslashes(clean_xss_tags($_POST['wr_homepage']));
            else
                $wr_homepage = addslashes(clean_xss_tags($wr['wr_homepage']));
        }
    } else {
        $mb_id = "";
        // 비회원의 경우 이름이 누락되는 경우가 있음
        if (!trim($wr_name)) alert("이름은 필히 입력하셔야 합니다.");
        $wr_name = clean_xss_tags(trim($_POST['wr_name']));
        $wr_email = get_email_address(trim($_POST['wr_email']));
    }

    $sql_password = $wr_password ? " , wr_password = '".get_encrypt_string($wr_password)."' " : "";

    $sql_ip = '';
    if (!$is_admin)
        $sql_ip = " , wr_ip = '{$_SERVER['REMOTE_ADDR']}' ";

    // 캐릭터 정보 업데이트 준비
    $ch_update = '';
    if ($member['mb_id'] && !isset($character)) {
        $character = null;
        if (is_community_installed() && function_exists('get_character')) {
            $character = get_character($member['mb_id']);
        }
    }

    // 수정 모드에서는 ch_X 필드를 덮어쓰지 않음 (기존 값 유지)
    $allow_ch_update = false;

    if ($allow_ch_update && $character && !empty($character['ch_id'])) {
        $ch_update = " , ch_id = '{$character['ch_id']}'
                      , ch_name = '".addslashes($character['ch_name'])."'
                      , ch_img = '".addslashes($character['ch_portrait_image'])."'
                      , ch_fa_id = '{$character['fa_id']}'
                      , ch_fa_name = '".addslashes($character['fa_name'])."'
                      , ch_fa_color = '".addslashes($character['fa_color'])."'
                      , ch_rk_id = '{$character['rk_id']}'
                      , ch_rk_name = '".addslashes($character['rk_name'])."'";

        // 장착된 타이틀 찾기
        if (!empty($character['tt_id'])) {
            $ch_update .= ", ch_title_id = '{$character['tt_id']}'
                          , ch_title_name = '".addslashes($character['tt_name'])."'";
        } else {
            $ch_update .= ", ch_title_id = '0'
                          , ch_title_name = ''";
        }
    }
    
    // wr_title, wr_img 필드 추가 (timeline 전용)
    $wr_title = isset($_POST['wr_title']) ? addslashes(clean_xss_tags(trim($_POST['wr_title']))) : '';

    // wr_img 필드 존재 여부 확인 (timeline 게시판 전용 필드)
    $has_wr_img = false;
    $check_columns = sql_query("SHOW COLUMNS FROM $write_table LIKE 'wr_img'");
    if (sql_num_rows($check_columns) > 0) {
        $has_wr_img = true;
    }

    // 썸네일 이미지 처리 (wr_img 필드가 있을 때만)
    $wr_img = '';
    if ($has_wr_img) {
        $thumbnail_type = isset($_POST['thumbnail_type']) ? $_POST['thumbnail_type'] : 'file';

        if ($thumbnail_type == 'url') {
            // URL 입력 방식
            $wr_img = isset($_POST['wr_img_url']) ? clean_xss_tags(trim($_POST['wr_img_url'])) : '';
        } else {
            // 파일 업로드 방식
            if (isset($_FILES['wr_img_file']) && $_FILES['wr_img_file']['error'] == 0) {
                $tmp_file = $_FILES['wr_img_file']['tmp_name'];
                $filename = $_FILES['wr_img_file']['name'];

                // 파일 확장자 체크
                $ext = array_pop(explode('.', $filename));
                $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');

                if (in_array(strtolower($ext), $allowed_ext)) {
                    // 파일명 생성
                    $wr_img_filename = 'thumb_'.md5(uniqid(time(),true)).'.'.$ext;
                    $dest_path = G5_DATA_PATH.'/file/'.$bo_table.'/'.$wr_img_filename;

                    // 디렉토리 생성
                    @mkdir(G5_DATA_PATH.'/file/'.$bo_table, G5_DIR_PERMISSION, true);

                    // 파일 이동
                    if (move_uploaded_file($tmp_file, $dest_path)) {
                        // 썸네일 리사이징은 각 스킨의 write_update.skin.php에서 처리
                        // resize_image($dest_path, 400, 400);
                        $wr_img = $wr_img_filename;
                    }
                }
            } else if ($w == 'u' && !isset($_POST['wr_img_del'])) {
                // 수정 시 기존 이미지 유지
                $wr_img = isset($write['wr_img']) ? $write['wr_img'] : '';
            }
        }

        // 삭제 체크박스 처리
        if ($w == 'u' && isset($_POST['wr_img_del']) && $_POST['wr_img_del'] == '1') {
            // 기존 파일 삭제
            if (isset($write['wr_img']) && $write['wr_img'] && strpos($write['wr_img'], 'http') !== 0) {
                @unlink(G5_DATA_PATH.'/file/'.$bo_table.'/'.$write['wr_img']);
            }
            $wr_img = '';
        }

        $wr_img = addslashes($wr_img);
    }

    // timeline 전용 필드 SQL 구성 (필드가 있을 때만)
    $timeline_fields_sql = '';
    if ($has_wr_img) {
        $timeline_fields_sql = ", wr_title = '{$wr_title}', wr_img = '{$wr_img}', wr_type = '{$wr_type}'";
    }

    $sql = " update {$write_table}
                set ca_name = '{$ca_name}',
                     wr_option = '{$wr_option}',
                     wr_subject = '{$wr_subject}'
                     $timeline_fields_sql,
                     wr_content = '{$wr_content}',
                     wr_link1 = '".sql_real_escape_string($wr_link1)."',
                     wr_link2 = '".sql_real_escape_string($wr_link2)."',
                     mb_id = '{$mb_id}',
                     wr_name = '{$wr_name}',
                     wr_email = '{$wr_email}',
                     wr_homepage = '{$wr_homepage}',
                     wr_1 = '{$wr_1}',
                     wr_2 = '{$wr_2}',
                     wr_3 = '{$wr_3}',
                     wr_4 = '{$wr_4}',
                     wr_5 = '{$wr_5}',
                     wr_6 = '{$wr_6}',
                     wr_7 = '{$wr_7}',
                     wr_8 = '{$wr_8}',
                     wr_9 = '{$wr_9}',
                     wr_10= '{$wr_10}',
                     wr_order = '{$wr_order}',
                     wr_adult = '{$wr_adult}',
                     wr_wide = '{$wr_wide}',
                     wr_plip = '{$wr_plip}',
                     wr_url = '{$wr_url}'
                     {$ch_update}
                     {$sql_ip}
                     {$sql_password}
              where wr_id = '{$wr['wr_id']}' ";
    sql_query($sql);

    // 분류가 수정되는 경우 해당되는 코멘트의 분류명도 모두 수정함
    $sql = " update {$write_table} set ca_name = '{$ca_name}' where wr_parent = '{$wr['wr_id']}' ";
    sql_query($sql);

    $bo_notice = board_notice($board['bo_notice'], $wr_id, $notice);
    sql_query(" update {$g5['board_table']} set bo_notice = '{$bo_notice}' where bo_table = '{$bo_table}' ");

    // 글을 수정한 경우에는 제목이 달라질수도 있으니 static variable 를 새로고침합니다.
    $write = get_write( $write_table, $wr['wr_id'], false);
}

// 파일개수 체크
$file_count   = 0;
$upload_count = (isset($_FILES['bf_file']['name']) && is_array($_FILES['bf_file']['name'])) ? count($_FILES['bf_file']['name']) : 0;

for ($i=0; $i<$upload_count; $i++) {
    if($_FILES['bf_file']['name'][$i] && is_uploaded_file($_FILES['bf_file']['tmp_name'][$i]))
        $file_count++;
}

if($w == 'u') {
    $file = get_file($bo_table, $wr_id);
    if($file_count && (int)$file['count'] > $board['bo_upload_count'])
        alert('기존 파일을 삭제하신 후 첨부파일을 '.number_format($board['bo_upload_count']).'개 이하로 업로드 해주십시오.');
} else {
    if($file_count > $board['bo_upload_count'])
        alert('첨부파일을 '.number_format($board['bo_upload_count']).'개 이하로 업로드 해주십시오.');
}

// 디렉토리가 없다면 생성합니다. (퍼미션도 변경하구요.)
@mkdir(G5_DATA_PATH.'/file/'.$bo_table, G5_DIR_PERMISSION);
@chmod(G5_DATA_PATH.'/file/'.$bo_table, G5_DIR_PERMISSION);

$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));

// 가변 파일 업로드
$file_upload_msg = '';
$upload = array();

// 처리할 파일 개수 결정 (기존 파일, 새 파일, 게시판 설정 중 최대값)
$upload_file_count = 0;
if(isset($_FILES['bf_file']['name']) && is_array($_FILES['bf_file']['name'])) {
    $upload_file_count = count($_FILES['bf_file']['name']);
}

// 기존 파일 개수 확인 (수정 모드일 때)
if ($w == 'u' && $wr_id) {
    $existing_file_row = sql_fetch(" select count(*) as cnt from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' ");
    $existing_file_count = (int)$existing_file_row['cnt'];
    $upload_file_count = max($upload_file_count, $existing_file_count);
}

// 게시판 설정값도 고려
$upload_file_count = max($upload_file_count, $board['bo_upload_count']);

if($upload_file_count > 0) {
    for ($i=0; $i<$upload_file_count; $i++) {
        $upload[$i]['file']     = '';
        $upload[$i]['source']   = '';
        $upload[$i]['filesize'] = 0;
        $upload[$i]['image']    = array();
        $upload[$i]['image'][0] = 0;
        $upload[$i]['image'][1] = 0;
        $upload[$i]['image'][2] = 0;
        $upload[$i]['fileurl'] = '';
        $upload[$i]['thumburl'] = '';
        $upload[$i]['storage'] = '';

        // 삭제에 체크가 되어있다면 파일을 삭제합니다.
        if (isset($_POST['bf_file_del'][$i]) && $_POST['bf_file_del'][$i]) {
            $upload[$i]['del_check'] = true;

            $row = sql_fetch(" select * from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and bf_no = '{$i}' ");

            $delete_file = run_replace('delete_file_path', G5_DATA_PATH.'/file/'.$bo_table.'/'.str_replace('../', '', $row['bf_file']), $row);
            if( file_exists($delete_file) ){
                @unlink($delete_file);
            }
        }
        else
            $upload[$i]['del_check'] = false;

        $tmp_file  = isset($_FILES['bf_file']['tmp_name'][$i]) ? $_FILES['bf_file']['tmp_name'][$i] : '';
        $filesize  = isset($_FILES['bf_file']['size'][$i]) ? $_FILES['bf_file']['size'][$i] : 0;
        $filename  = isset($_FILES['bf_file']['name'][$i]) ? $_FILES['bf_file']['name'][$i] : '';
        $filename  = get_safe_filename($filename);

        // 서버에 설정된 값보다 큰파일을 업로드 한다면
        if ($filename) {
            $file_error = isset($_FILES['bf_file']['error'][$i]) ? $_FILES['bf_file']['error'][$i] : 4;
            if ($file_error == 1) {
                $file_upload_msg .= '\"'.$filename.'\" 파일의 용량이 서버에 설정('.$upload_max_filesize.')된 값보다 크므로 업로드 할 수 없습니다.\\n';
                continue;
            }
            else if ($file_error != 0) {
                $file_upload_msg .= '\"'.$filename.'\" 파일이 정상적으로 업로드 되지 않았습니다.\\n';
                continue;
            }
        }

        if (is_uploaded_file($tmp_file)) {
            // 관리자가 아니면서 설정한 업로드 사이즈보다 크다면 건너뜀
            if (!$is_admin && $filesize > $board['bo_upload_size']) {
                $file_upload_msg .= '\"'.$filename.'\" 파일의 용량('.number_format($filesize).' 바이트)이 게시판에 설정('.number_format($board['bo_upload_size']).' 바이트)된 값보다 크므로 업로드 하지 않습니다.\\n';
                continue;
            }

            //=================================================================\
            // 090714
            // 이미지나 플래시 파일에 악성코드를 심어 업로드 하는 경우를 방지
            // 에러메세지는 출력하지 않는다.
            //-----------------------------------------------------------------
            $timg = @getimagesize($tmp_file);
            // image type
            if ( preg_match("/\.({$config['cf_image_extension']})$/i", $filename) ||
                 preg_match("/\.({$config['cf_flash_extension']})$/i", $filename) ) {
                if ($timg['2'] < 1 || $timg['2'] > 18)
                    continue;
            }
            //=================================================================

            $upload[$i]['image'] = $timg;

            // 4.00.11 - 글답변에서 파일 업로드시 원글의 파일이 삭제되는 오류를 수정
            if ($w == 'u') {
                // 존재하는 파일이 있다면 삭제합니다.
                $row = sql_fetch(" select * from {$g5['board_file_table']} where bo_table = '$bo_table' and wr_id = '$wr_id' and bf_no = '$i' ");
                
                if(isset($row['bf_file']) && $row['bf_file']){
                    $delete_file = run_replace('delete_file_path', G5_DATA_PATH.'/file/'.$bo_table.'/'.str_replace('../', '', $row['bf_file']), $row);
                    if( file_exists($delete_file) ){
                        @unlink(G5_DATA_PATH.'/file/'.$bo_table.'/'.$row['bf_file']);
                    }
                }
            }

            // 프로그램 원래 파일명
            $upload[$i]['source'] = $filename;
            $upload[$i]['filesize'] = $filesize;

            // 아래의 문자열이 들어간 파일은 -x 를 붙여서 웹경로를 알더라도 실행을 하지 못하도록 함
            $filename = preg_replace("/\.(php|pht|phtm|htm|cgi|pl|exe|jsp|asp|inc|phar)/i", "$0-x", $filename);

            shuffle($chars_array);
            $shuffle = implode('', $chars_array);

            // 첨부파일 첨부시 첨부파일명에 공백이 포함되어 있으면 일부 PC에서 보이지 않거나 다운로드 되지 않는 현상이 있습니다. (길상여의 님 090925)
            $upload[$i]['file'] = md5(sha1($_SERVER['REMOTE_ADDR'])).'_'.substr($shuffle,0,8).'_'.replace_filename($filename);

            $dest_file = G5_DATA_PATH.'/file/'.$bo_table.'/'.$upload[$i]['file'];

            // 업로드가 안된다면 에러메세지 출력하고 죽어버립니다.
            $error_code = move_uploaded_file($tmp_file, $dest_file) or die($_FILES['bf_file']['error'][$i]);

            // 올라간 파일의 퍼미션을 변경합니다.
            chmod($dest_file, G5_FILE_PERMISSION);

            $dest_file = run_replace('write_update_upload_file', $dest_file, $board, $wr_id, $w);
            $upload[$i] = run_replace('write_update_upload_array', $upload[$i], $dest_file, $board, $wr_id, $w);
        }
    }   // end for
}   // end if

// 나중에 테이블에 저장하는 이유는 $wr_id 값을 저장해야 하기 때문입니다.
for ($i=0; $i<count($upload); $i++)
{
    $upload[$i]['source'] = sql_real_escape_string($upload[$i]['source']);
    // bf_content가 없으면 bf_no($i)와 동일하게 설정
    $bf_content[$i] = isset($bf_content[$i]) && $bf_content[$i] !== '' ? sql_real_escape_string($bf_content[$i]) : $i;
    $bf_width = isset($upload[$i]['image'][0]) ? (int) $upload[$i]['image'][0] : 0;
    $bf_height = isset($upload[$i]['image'][1]) ? (int) $upload[$i]['image'][1] : 0;
    $bf_type = isset($upload[$i]['image'][2]) ? (int) $upload[$i]['image'][2] : 0;

    $row = sql_fetch(" select count(*) as cnt from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and bf_no = '{$i}' ");
    if ($row['cnt'])
    {
        // 삭제에 체크가 있거나 파일이 있다면 업데이트를 합니다.
        // 그렇지 않다면 내용만 업데이트 합니다.
        if ($upload[$i]['del_check'] || $upload[$i]['file'])
        {
            $sql = " update {$g5['board_file_table']}
                        set bf_source = '{$upload[$i]['source']}',
                             bf_file = '{$upload[$i]['file']}',
                             bf_content = '{$bf_content[$i]}',
                             bf_fileurl = '{$upload[$i]['fileurl']}',
                             bf_thumburl = '{$upload[$i]['thumburl']}',
                             bf_storage = '{$upload[$i]['storage']}',
                             bf_filesize = '".(int)$upload[$i]['filesize']."',
                             bf_width = '".$bf_width."',
                             bf_height = '".$bf_height."',
                             bf_type = '".$bf_type."',
                             bf_datetime = '".G5_TIME_YMDHIS."'
                      where bo_table = '{$bo_table}'
                                and wr_id = '{$wr_id}'
                                and bf_no = '{$i}' ";
            sql_query($sql);
        }
        else
        {
            $sql = " update {$g5['board_file_table']}
                        set bf_content = '{$bf_content[$i]}'
                        where bo_table = '{$bo_table}'
                                  and wr_id = '{$wr_id}'
                                  and bf_no = '{$i}' ";
            sql_query($sql);
        }
    }
    else
    {
        $sql = " insert into {$g5['board_file_table']}
                    set bo_table = '{$bo_table}',
                         wr_id = '{$wr_id}',
                         bf_no = '{$i}',
                         bf_source = '{$upload[$i]['source']}',
                         bf_file = '{$upload[$i]['file']}',
                         bf_content = '{$bf_content[$i]}',
                         bf_fileurl = '{$upload[$i]['fileurl']}',
                         bf_thumburl = '{$upload[$i]['thumburl']}',
                         bf_storage = '{$upload[$i]['storage']}',
                         bf_download = 0,
                         bf_filesize = '".(int)$upload[$i]['filesize']."',
                         bf_width = '".$bf_width."',
                         bf_height = '".$bf_height."',
                         bf_type = '".$bf_type."',
                         bf_datetime = '".G5_TIME_YMDHIS."' ";
        sql_query($sql);

        run_event('write_update_file_insert', $bo_table, $wr_id, $upload[$i], $w);
    }
}

// 업로드된 파일 내용에서 가장 큰 번호를 얻어 거꾸로 확인해 가면서
// 파일 정보가 없다면 테이블의 내용을 삭제합니다.
$row = sql_fetch(" select max(bf_no) as max_bf_no from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' ");
for ($i=(int)$row['max_bf_no']; $i>=0; $i--)
{
    $row2 = sql_fetch(" select bf_file from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and bf_no = '{$i}' ");

    // 정보가 있다면 빠집니다.
    if (isset($row2['bf_file']) && $row2['bf_file']) break;

    // 그렇지 않다면 정보를 삭제합니다.
    sql_query(" delete from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and bf_no = '{$i}' ");
}

// 중간에 있는 빈 파일 레코드도 삭제 (삭제 체크박스로 삭제된 파일)
sql_query(" delete from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and (bf_file = '' or bf_file is null) ");

// 파일의 개수를 게시물에 업데이트 한다. (bf_file이 빈 문자열이 아닌 것만 카운트)
$row = sql_fetch(" select count(*) as cnt from {$g5['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and bf_file <> '' ");
sql_query(" update {$write_table} set wr_file = '{$row['cnt']}' where wr_id = '{$wr_id}' ");

// 자동저장된 레코드를 삭제한다.
sql_query(" delete from {$g5['autosave_table']} where as_uid = '{$uid}' ");
//------------------------------------------------------------------------------

// 알림 생성
if ($w == '' || $w == 'r') {
    // 알림 라이브러리 로드
    if (file_exists(G5_LIB_PATH.'/notification.lib.php')) {
        include_once(G5_LIB_PATH.'/notification.lib.php');

        // 비회원은 'guest'로 처리
        $from_mb_id = $member['mb_id'] ? $member['mb_id'] : 'guest';

        $is_timeline_reply = $is_timeline && (int)$original_tm_parent > 0;

        // 답글 알림 (비회원 포함)
        // 타임라인 답글인 경우 (원본 tm_parent가 지정되어 있으면 답글)
        if ($is_timeline_reply) {
            // 답글 작성 시 부모글 작성자에게 알림
            $parent_post = sql_fetch(" SELECT mb_id, wr_subject FROM {$write_table} WHERE wr_id = '{$original_tm_parent}' ");

            if ($parent_post && !empty($parent_post['mb_id'])) {
                // 자기 자신에게는 알림 안 보냄
                if ($parent_post['mb_id'] != $member['mb_id']) {
                    create_notification(array(
                        'noti_type' => 'reply',
                        'mb_id' => $parent_post['mb_id'],
                        'from_mb_id' => $from_mb_id,
                        'from_wr_name' => $wr_name,
                        'bo_table' => $bo_table,
                        'wr_id' => $wr_id,
                        'wr_parent' => $original_tm_parent,
                        'noti_content' => cut_str(strip_tags($wr_content), 200),
                        'noti_url' => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id.'#c_'.$wr_id
                    ));
                }
            }
        } else if ($w == 'r' && isset($write['mb_id']) && $write['mb_id']) {
            // 일반 답변글
            if ($write['mb_id'] != $member['mb_id']) {
                create_notification(array(
                    'noti_type' => 'reply',
                    'mb_id' => $write['mb_id'],
                    'from_mb_id' => $from_mb_id,
                    'from_wr_name' => $wr_name,
                    'bo_table' => $bo_table,
                    'wr_id' => $wr_id,
                    'wr_parent' => $write['wr_id'],
                    'noti_content' => cut_str(strip_tags($wr_content), 200),
                    'noti_url' => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id.'#c_'.$wr_id
                ));
            }
        }

        // 새 글 알림 (최고관리자에게) - 회원/비회원 모두
        if ($w == '' && !$is_timeline_reply && $config['cf_admin'] && $config['cf_admin'] != $member['mb_id']) {
            create_notification(array(
                'noti_type' => 'comment',
                'mb_id' => $config['cf_admin'],
                'from_mb_id' => $from_mb_id,
                'from_wr_name' => $wr_name,
                'bo_table' => $bo_table,
                'wr_id' => $wr_id,
                'wr_parent' => $wr_id,
                'noti_content' => cut_str(strip_tags($wr_content), 200),
                'noti_url' => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id
            ));
        }

        // 멘션 알림 (@이름, @{이름}, @이름 이름 감지 — 캐릭터 이름 우선, 회원명 fallback)
        $unique_mentions = function_exists('parse_mention_names') ? parse_mention_names($wr_content) : array();
        foreach ($unique_mentions as $mention_name) {
            $mentioned_member = null;
            // 캐릭터 이름으로 먼저 조회
            if (function_exists('get_character_by_name')) {
                $mentioned_ch = get_character_by_name($mention_name);
                if ($mentioned_ch && !empty($mentioned_ch['mb_id'])) {
                    $mentioned_member = array('mb_id' => $mentioned_ch['mb_id']);
                }
            }
            // 캐릭터 없으면 회원명으로 fallback
            if (!$mentioned_member) {
                $mentioned_member = sql_fetch("SELECT mb_id FROM {$g5['member_table']} WHERE mb_name = '".sql_real_escape_string($mention_name)."'");
            }
            if ($mentioned_member) {
                create_notification(array(
                    'noti_type' => 'mention',
                    'mb_id' => $mentioned_member['mb_id'],
                    'from_mb_id' => $from_mb_id,
                    'from_wr_name' => $wr_name,
                    'bo_table' => $bo_table,
                    'wr_id' => $wr_id,
                    'wr_parent' => $wr_id,
                    'noti_content' => cut_str(strip_tags($wr_content), 200),
                    'noti_url' => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id
                ));
            }
        }
    }
}

// 비밀글이라면 세션에 비밀글의 아이디를 저장한다. 자신의 글은 다시 비밀번호를 묻지 않기 위함
if ($secret) {
    if (!(isset($wr_num) && $wr_num)) {
        $write = get_write($write_table, $wr_id, true);
        $wr_num = $write['wr_num'];
    }

    set_session("ss_secret_{$bo_table}_{$wr_num}", TRUE);
}

// 비밀글 조회 비밀번호 처리 (모든 게시판 공통)
include_once(G5_LIB_PATH.'/board_secret.lib.php');
process_secret_password($write_table, $wr_id, $_POST);

// 사용자 코드 실행
@include_once($board_skin_path.'/write_update.skin.php');
@include_once($board_skin_path.'/write_update.tail.skin.php');

delete_cache_latest($bo_table);

$redirect_url = run_replace('write_update_move_url', short_url_clean(G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id.$qstr), $board, $wr_id, $w, $qstr, $file_upload_msg);

run_event('write_update_after', $board, $wr_id, $w, $qstr, $redirect_url);

// AJAX 요청인 경우 JSON으로 저장 결과 반환
$is_ajax_write = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    || (isset($_POST['trpg_ajax_write']) && $_POST['trpg_ajax_write'] === '1');
if ($is_ajax_write) {
    header('Content-Type: application/json');
    // &amp; -> & 변환 (JSON에서는 일반 & 사용)
    $clean_redirect_url = str_replace('&amp;', '&', $redirect_url);
    echo json_encode(array(
        'success' => true,
        'wr_id' => (int) $wr_id,
        'redirect_url' => $clean_redirect_url,
        'message' => $file_upload_msg
    ));
    exit;
}

if ($file_upload_msg)
    alert($file_upload_msg, $redirect_url);
else
    goto_url($redirect_url);
