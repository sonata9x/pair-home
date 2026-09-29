<?php
// v1.4.7 (2026-02-16) 댓글 알림 대상을 모든 관리자(mb_level>=10)에서 최고관리자(cf_admin)로 변경
define('G5_CAPTCHA', true);
include_once('./_common.php');
if (file_exists(G5_CAPTCHA_PATH.'/captcha.lib.php')) {
    include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');
}

// 토큰 검증 제거 (2025-10-30)
// 권한 체크는 하단에서 진행됨
// $comment_token = trim(get_session('ss_comment_token'));
// set_session('ss_comment_token', '');
// if(empty($_POST['token']) || !$comment_token || $comment_token != $_POST['token'])
//     alert('올바른 방법으로 이용해 주십시오.');

// 090710
if (substr_count($wr_content, "&#") > 50) {
    alert('내용에 올바르지 않은 코드가 다수 포함되어 있습니다.');
    exit;
}

// ><! 패턴 이스케이프 (HTML 주석 오인 방지)
$wr_content = str_replace('><!' , '>&lt;!', $wr_content);

$w = isset($_POST['w']) ? clean_xss_tags($_POST['w']) : '';
$wr_name  = isset($_POST['wr_name']) ? clean_xss_tags(trim($_POST['wr_name'])) : '';
$wr_secret = isset($_POST['wr_secret']) ? clean_xss_tags($_POST['wr_secret']) : '';
$wr_email = $wr_subject = '';
$reply_array = array();

$wr_1 = isset($_POST['wr_1']) ? $_POST['wr_1'] : '';
$wr_2 = isset($_POST['wr_2']) ? $_POST['wr_2'] : '';
$wr_3 = isset($_POST['wr_3']) ? $_POST['wr_3'] : '';
$wr_4 = isset($_POST['wr_4']) ? $_POST['wr_4'] : '';
$wr_5 = isset($_POST['wr_5']) ? $_POST['wr_5'] : '';
$wr_6 = isset($_POST['wr_6']) ? $_POST['wr_6'] : '';
$wr_7 = isset($_POST['wr_7']) ? $_POST['wr_7'] : '';
$wr_8 = isset($_POST['wr_8']) ? $_POST['wr_8'] : '';
$wr_9 = isset($_POST['wr_9']) ? $_POST['wr_9'] : '';
$wr_10 = isset($_POST['wr_10']) ? $_POST['wr_10'] : '';
$wr_facebook_user = isset($_POST['wr_facebook_user']) ? clean_xss_tags($_POST['wr_facebook_user'], 1, 1) : '';
$wr_twitter_user = isset($_POST['wr_twitter_user']) ? clean_xss_tags($_POST['wr_twitter_user'], 1, 1) : '';
$wr_homepage = isset($_POST['wr_homepage']) ? clean_xss_tags($_POST['wr_homepage'], 1, 1) : '';

if (!empty($_POST['wr_email']))
    $wr_email = get_email_address(trim($_POST['wr_email']));

@include_once($board_skin_path.'/write_comment_update.head.skin.php');

// 비회원의 경우 이름이 누락되는 경우가 있음
if ($is_guest) {
    if ($wr_name == '')
        alert('이름은 필히 입력하셔야 합니다.');
    // 캡챠 비활성화 (2025-12-22)
    // if(function_exists('chk_captcha') && !chk_captcha())
    //     alert('자동등록방지 숫자가 틀렸습니다.');
}

if ($w == "c" || $w == "cu") {
    if ($member['mb_level'] < $board['bo_comment_level'])
        alert('댓글을 쓸 권한이 없습니다.');
}
else
    alert('w 값이 제대로 넘어오지 않았습니다.');

// 세션의 시간 검사
// 4.00.15 - 댓글 수정시 연속 게시물 등록 메시지로 인한 오류 수정
if ($w == 'c' && !$is_admin && isset($_SESSION['ss_datetime']) && $_SESSION['ss_datetime'] >= (G5_SERVER_TIME - $config['cf_delay_sec']))
    alert('너무 빠른 시간내에 게시물을 연속해서 올릴 수 없습니다.');

set_session('ss_datetime', G5_SERVER_TIME);

$wr = get_write($write_table, $wr_id);
if (empty($wr['wr_id']))
    alert("글이 존재하지 않습니다.\\n글이 삭제되었거나 이동하였을 수 있습니다.");

// wr_secret 컬럼 존재 확인 및 추가 (비밀글 기능을 위해)
$field_query = "SHOW COLUMNS FROM $write_table LIKE 'wr_secret'";
$field_exists = sql_fetch($field_query);
if (!$field_exists) {
    sql_query("ALTER TABLE $write_table ADD COLUMN wr_secret tinyint(1) NOT NULL DEFAULT '0' AFTER wr_option", false);
}

// "인터넷옵션 > 보안 > 사용자정의수준 > 스크립팅 > Action 스크립팅 > 사용 안 함" 일 경우의 오류 처리
// 이 옵션을 사용 안 함으로 설정할 경우 어떤 스크립트도 실행 되지 않습니다.
//if (!trim($_POST["wr_content"])) die ("내용을 입력하여 주십시오.");

$post_wr_password = '';
if ($is_member)
{
    $mb_id = $member['mb_id'];
    // 댓글도 게시글과 동일한 이름 규칙 — 캐릭터가 있으면 캐릭터명(설정에 따라 "캐릭터명 [오너명]"), 없으면 회원명
    $comment_character = null;
    if (function_exists('is_community_installed') && is_community_installed() && function_exists('get_character')) {
        $comment_character = get_character($member['mb_id']);
    }
    if ($comment_character && !empty($comment_character['ch_name'])) {
        $display_name = function_exists('community_format_post_name')
            ? community_format_post_name($comment_character['ch_name'], $member['mb_name'])
            : $comment_character['ch_name'];
        $wr_name = addslashes(clean_xss_tags($display_name));
    } else {
        $wr_name = addslashes(clean_xss_tags($member['mb_name']));
    }
    $wr_password = '';
    $wr_email = addslashes($member['mb_email']);
    $wr_homepage = addslashes(clean_xss_tags($member['mb_homepage']));
}
else
{
    $mb_id = '';
    // 비회원 이름도 addslashes 처리
    $wr_name = addslashes($wr_name);
    $post_wr_password = $wr_password;
    $wr_password = get_encrypt_string($wr_password);
}

if ($w == 'c') // 댓글 입력
{
    /*
    if ($member[mb_point] + $board[bo_comment_point] < 0 && !$is_admin)
        alert('보유하신 포인트('.number_format($member[mb_point]).')가 없거나 모자라서 댓글쓰기('.number_format($board[bo_comment_point]).')가 불가합니다.\\n\\n포인트를 적립하신 후 다시 댓글을 써 주십시오.');
    */
    // 댓글쓰기 포인트설정시 회원의 포인트가 음수인 경우 댓글을 쓰지 못하던 버그를 수정 (곱슬최씨님)
    $tmp_point = ($member['mb_point'] > 0) ? $member['mb_point'] : 0;
    if ($tmp_point + $board['bo_comment_point'] < 0 && !$is_admin)
        alert('보유하신 포인트('.number_format($member['mb_point']).')가 없거나 모자라서 댓글쓰기('.number_format($board['bo_comment_point']).')가 불가합니다.\\n\\n포인트를 적립하신 후 다시 댓글을 써 주십시오.');

    // 댓글 답변
    if ($comment_id)
    {
        $reply_array = get_write($write_table, $comment_id, true);
        if (!$reply_array || !$reply_array['wr_id'])
            alert('답변할 댓글이 없습니다.\\n\\n답변하는 동안 댓글이 삭제되었을 수 있습니다.');

        if($wr['wr_parent'] != $reply_array['wr_parent'])
            alert('댓글을 등록할 수 없습니다.');

        $tmp_comment = $reply_array['wr_comment'];

        if (strlen($reply_array['wr_comment_reply']) == 5)
            alert('더 이상 답변하실 수 없습니다.\\n\\n답변은 5단계 까지만 가능합니다.');

        $reply_len = strlen($reply_array['wr_comment_reply']) + 1;
        if ($board['bo_reply_order']) {
            $begin_reply_char = 'A';
            $end_reply_char = 'Z';
            $reply_number = +1;
            $sql = " select MAX(SUBSTRING(wr_comment_reply, $reply_len, 1)) as reply
                        from $write_table
                        where wr_parent = '$wr_id'
                        and wr_comment = '$tmp_comment'
                        and SUBSTRING(wr_comment_reply, $reply_len, 1) <> '' ";
        }
        else
        {
            $begin_reply_char = 'Z';
            $end_reply_char = 'A';
            $reply_number = -1;
            $sql = " select MIN(SUBSTRING(wr_comment_reply, $reply_len, 1)) as reply
                        from $write_table
                        where wr_parent = '$wr_id'
                        and wr_comment = '$tmp_comment'
                        and SUBSTRING(wr_comment_reply, $reply_len, 1) <> '' ";
        }
        if ($reply_array['wr_comment_reply'])
            $sql .= " and wr_comment_reply like '{$reply_array['wr_comment_reply']}%' ";
        $row = sql_fetch($sql);

        if (!$row['reply'])
            $reply_char = $begin_reply_char;
        else if ($row['reply'] == $end_reply_char) // A~Z은 26 입니다.
            alert('더 이상 답변하실 수 없습니다.\\n\\n답변은 26개 까지만 가능합니다.');
        else
            $reply_char = chr(ord($row['reply']) + $reply_number);

        $tmp_comment_reply = $reply_array['wr_comment_reply'] . $reply_char;
    }
    else
    {
        $sql = " select max(wr_comment) as max_comment from $write_table
                    where wr_parent = '$wr_id' and wr_is_comment = 1 ";
        $row = sql_fetch($sql);
        //$row[max_comment] -= 1;
        $row['max_comment'] += 1;
        $tmp_comment = $row['max_comment'];
        $tmp_comment_reply = '';
    }

    $wr_subject = get_text(stripslashes($wr['wr_subject']));

    $sql = " insert into $write_table
                set ca_name = '{$wr['ca_name']}',
                     wr_option = '$wr_secret',
                     wr_num = '{$wr['wr_num']}',
                     wr_reply = '',
                     wr_parent = '$wr_id',
                     wr_is_comment = 1,
                     wr_comment = '$tmp_comment',
                     wr_comment_reply = '$tmp_comment_reply',
                     wr_subject = '',
                     wr_content = '$wr_content',
                     mb_id = '$mb_id',
                     wr_password = '$wr_password',
                     wr_name = '$wr_name',
                     wr_email = '$wr_email',
                     wr_homepage = '$wr_homepage',
                     wr_datetime = '".G5_TIME_YMDHIS."',
                     wr_last = '',
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
                     wr_10 = '$wr_10' ";
    sql_query($sql);

    $comment_id = sql_insert_id();

    // 파일 업로드 처리
    $file_count = 0;
    if (isset($_FILES['bf_file'])) {
        $upload_max_filesize = ini_get('upload_max_filesize');

        @mkdir(G5_DATA_PATH.'/file/'.$bo_table, G5_DIR_PERMISSION, true);
        @chmod(G5_DATA_PATH.'/file/'.$bo_table, G5_DIR_PERMISSION);

        $chars_array = array_merge(range(0,9), range('a','z'), range('A','Z'));

        for ($i=0; $i<count($_FILES['bf_file']['name']); $i++) {
            if ($i >= 4) break; // 최대 4개

            if ($_FILES['bf_file']['error'][$i] == 0 && is_uploaded_file($_FILES['bf_file']['tmp_name'][$i])) {
                // 파일 확장자 체크
                $tmp_file = $_FILES['bf_file']['tmp_name'][$i];
                $filename = $_FILES['bf_file']['name'][$i];
                $filesize = $_FILES['bf_file']['size'][$i];

                // 이미지 확장자만 허용
                $ext = strtolower(array_pop(explode('.', $filename)));
                $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');

                if (!in_array($ext, $allowed_ext)) {
                    continue;
                }

                // 파일명 생성
                shuffle($chars_array);
                $shuffle_str = implode('', $chars_array);
                $bf_file = abs(ip2long($_SERVER['REMOTE_ADDR'])) . '_' . substr($shuffle_str,0,8) . '_' . str_replace('%', '', urlencode(str_replace(' ', '_', $filename)));
                $dest_path = G5_DATA_PATH.'/file/'.$bo_table.'/'.$bf_file;

                // 파일 이동
                if (move_uploaded_file($tmp_file, $dest_path)) {
                    @chmod($dest_path, G5_FILE_PERMISSION);

                    // board_file 테이블에 저장
                    $sql2 = "INSERT INTO {$g5['board_file_table']}
                            SET bo_table = '{$bo_table}',
                                wr_id = '{$comment_id}',
                                bf_no = '{$file_count}',
                                bf_source = '" . addslashes($filename) . "',
                                bf_file = '{$bf_file}',
                                bf_download = 0,
                                bf_content = '-1',
                                bf_filesize = '{$filesize}',
                                bf_width = 0,
                                bf_height = 0,
                                bf_type = 0,
                                bf_datetime = '".G5_TIME_YMDHIS."'";
                    sql_query($sql2);

                    $file_count++;
                }
            }
        }

        // 파일 개수 업데이트
        if ($file_count > 0) {
            sql_query("UPDATE {$write_table} SET wr_file = '{$file_count}' WHERE wr_id = '{$comment_id}'");
        }
    }

    // 원글에 댓글수 증가 & 마지막 시간 반영
    sql_query(" update $write_table set wr_comment = wr_comment + 1, wr_last = '".G5_TIME_YMDHIS."' where wr_id = '$wr_id' ");

    // 새글 INSERT
    sql_query(" insert into {$g5['board_new_table']} ( bo_table, wr_id, wr_parent, bn_datetime, mb_id ) values ( '$bo_table', '$comment_id', '$wr_id', '".G5_TIME_YMDHIS."', '{$member['mb_id']}' ) ");

    // 댓글 1 증가
    sql_query(" update {$g5['board_table']} set bo_count_comment = bo_count_comment + 1 where bo_table = '$bo_table' ");

    // 포인트 부여
    insert_point($member['mb_id'], $board['bo_comment_point'], "{$board['bo_subject']} {$wr_id}-{$comment_id} 댓글쓰기", $bo_table, $comment_id, '댓글');

    // 메일발송 사용
    if ($config['cf_email_use'] && $board['bo_use_email'])
    {
        // 관리자의 정보를 얻고
        $super_admin = get_admin('super');
        $group_admin = get_admin('group');
        $board_admin = get_admin('board');

        $wr_content = nl2br(get_text(stripslashes("원글\n{$wr['wr_subject']}\n\n\n댓글\n$wr_content")));

        $warr = array( ''=>'입력', 'u'=>'수정', 'r'=>'답변', 'c'=>'댓글 ', 'cu'=>'댓글 수정' );
        $str = $warr[$w];

        $subject = '['.$config['cf_title'].'] '.$board['bo_subject'].' 게시판에 '.$str.'글이 올라왔습니다.';
        // 4.00.15 - 메일로 보내는 댓글의 바로가기 링크 수정
        $link_url = get_pretty_url($bo_table, $wr_id, $qstr."#c_".$comment_id);

        include_once(G5_LIB_PATH.'/mailer.lib.php');

        ob_start();
        include_once ('./write_update_mail.php');
        $content = ob_get_contents();
        ob_end_clean();

        $array_email = array();
        // 게시판관리자에게 보내는 메일
        if ($config['cf_email_wr_board_admin']) $array_email[] = $board_admin['mb_email'];
        // 게시판그룹관리자에게 보내는 메일
        if ($config['cf_email_wr_group_admin']) $array_email[] = $group_admin['mb_email'];
        // 최고관리자에게 보내는 메일
        if ($config['cf_email_wr_super_admin']) $array_email[] = $super_admin['mb_email'];

        // 원글게시자에게 보내는 메일
        if ($config['cf_email_wr_write']) $array_email[] = $wr['wr_email'];

        // 댓글 쓴 모든이에게 메일 발송이 되어 있다면 (자신에게는 발송하지 않는다)
        if ($config['cf_email_wr_comment_all']) {
            $sql = " select distinct wr_email from {$write_table}
                        where wr_email not in ( '{$wr['wr_email']}', '{$member['mb_email']}', '' )
                        and wr_parent = '$wr_id' ";
            $result = sql_query($sql);
            while ($row=sql_fetch_array($result))
                $array_email[] = $row['wr_email'];
        }

        // 중복된 메일 주소는 제거
        $unique_email = array_unique($array_email);
        $unique_email = array_values($unique_email);
        for ($i=0; $i<count($unique_email); $i++) {
            mailer($wr_name, $wr_email, $unique_email[$i], $subject, $content, 1);
        }
    }
}
else if ($w == 'cu') // 댓글 수정
{
    $sql = " select mb_id, wr_password, wr_comment, wr_comment_reply from $write_table
                where wr_id = '$comment_id' ";
    $comment = $reply_array = sql_fetch($sql);
    $tmp_comment = $reply_array['wr_comment'];

    $len = strlen($reply_array['wr_comment_reply']);
    if ($len < 0) $len = 0;
    $comment_reply = substr($reply_array['wr_comment_reply'], 0, $len);
    //print_r2($GLOBALS); exit;

    if ($is_admin == 'super' || $is_admin == 'admin') // 최고관리자/관리자 통과
        ;
    else if ($is_admin == 'group') { // 그룹관리자
        $mb = get_member($comment['mb_id']);
        if ($member['mb_id'] === $group['gr_admin']) { // 자신이 관리하는 그룹인가?
            if ($member['mb_level'] >= $mb['mb_level']) // 자신의 레벨이 크거나 같다면 통과
                ;
            else
                alert('그룹관리자의 권한보다 높은 회원의 댓글이므로 수정할 수 없습니다.');
        } else
            alert('자신이 관리하는 그룹의 게시판이 아니므로 댓글을 수정할 수 없습니다.');
    } else if ($is_admin == 'board') { // 게시판관리자이면
        $mb = get_member($comment['mb_id']);
        if (is_board_admin($member['mb_id'], $board)) { // 자신이 관리하는 게시판인가?
            if ($member['mb_level'] >= $mb['mb_level']) // 자신의 레벨이 크거나 같다면 통과
                ;
            else
                alert('게시판관리자의 권한보다 높은 회원의 댓글이므로 수정할 수 없습니다.');
        } else
            alert('자신이 관리하는 게시판이 아니므로 댓글을 수정할 수 없습니다.');
    } else if ($member['mb_id']) {
        if ($member['mb_id'] !== $comment['mb_id'])
            alert('자신의 글이 아니므로 수정할 수 없습니다.');
    } else {
        if( !($comment['mb_id'] === '' && $comment['wr_password'] && check_password($post_wr_password, $comment['wr_password'])) )
            alert('댓글을 수정할 권한이 없습니다.');
    }

    $sql = " select count(*) as cnt from $write_table
                where wr_comment_reply like '$comment_reply%'
                and wr_id <> '$comment_id'
                and wr_parent = '$wr_id'
                and wr_comment = '$tmp_comment'
                and wr_is_comment = 1 ";
    $row = sql_fetch($sql);
    if ($row['cnt'] && !$is_admin)
        alert('이 댓글와 관련된 답변댓글이 존재하므로 수정 할 수 없습니다.');

    $sql_ip = "";
    if (!$is_admin)
        $sql_ip = " , wr_ip = '{$_SERVER['REMOTE_ADDR']}' ";

    $sql_secret = " , wr_option = '$wr_secret' ";

    $sql = " update $write_table
                set wr_subject = '$wr_subject',
                     wr_content = '$wr_content',
                     wr_1 = '$wr_1',
                     wr_2 = '$wr_2',
                     wr_3 = '$wr_3',
                     wr_4 = '$wr_4',
                     wr_5 = '$wr_5',
                     wr_6 = '$wr_6',
                     wr_7 = '$wr_7',
                     wr_8 = '$wr_8',
                     wr_9 = '$wr_9',
                     wr_10 = '$wr_10'
                     $sql_ip
                     $sql_secret
              where wr_id = '$comment_id' ";

    sql_query($sql);
}

// 댓글 알림 생성 (비회원 포함)
if (($w == 'c' || $w == 'cu') && file_exists(G5_LIB_PATH.'/notification.lib.php')) {
    include_once(G5_LIB_PATH.'/notification.lib.php');

    if ($w == 'c') {
        // 댓글 작성 시에만 알림 (수정 시에는 알림 안 보냄)

        // 알림을 받을 회원 목록 (중복 방지)
        $notify_members = array();

        // 1. 원글 작성자 정보 가져오기
        $original_post = sql_fetch("SELECT mb_id FROM {$write_table} WHERE wr_id = '{$wr_id}'");

        if (!empty($original_post['mb_id']) && $original_post['mb_id'] != $member['mb_id']) {
            $notify_members[$original_post['mb_id']] = $original_post['mb_id'];
        }

        // 2. 최고관리자 추가
        $admins = sql_query("SELECT mb_id FROM {$g5['member_table']} WHERE mb_id = '".sql_real_escape_string($config['cf_admin'])."'");
        while ($admin = sql_fetch_array($admins)) {
            if ($admin['mb_id'] != $member['mb_id']) {
                $notify_members[$admin['mb_id']] = $admin['mb_id'];
            }
        }

        // 알림 생성
        $from_mb_id = $member['mb_id'] ? $member['mb_id'] : 'guest';
        foreach ($notify_members as $mb_id) {
            create_notification(array(
                'noti_type' => 'reply',
                'mb_id' => $mb_id,
                'from_mb_id' => $from_mb_id,
                'from_wr_name' => $wr_name,
                'bo_table' => $bo_table,
                'wr_id' => $comment_id,
                'wr_parent' => $wr_id,
                'noti_content' => cut_str(strip_tags($wr_content), 200),
                'noti_url' => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id.'#c_'.$comment_id
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
                    'wr_id' => $comment_id,
                    'wr_parent' => $wr_id,
                    'noti_content' => cut_str(strip_tags($wr_content), 200),
                    'noti_url' => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id.'#c_'.$comment_id
                ));
            }
        }
    }
}

// 사용자 코드 실행
@include_once($board_skin_path.'/write_comment_update.skin.php');
@include_once($board_skin_path.'/write_comment_update.tail.skin.php');

delete_cache_latest($bo_table);

$redirect_url = short_url_clean(G5_HTTP_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr['wr_parent'].'&amp;'.$qstr.'&amp;#c_'.$comment_id);

run_event('comment_update_after', $board, $wr_id, $w, $qstr, $redirect_url, $comment_id, $reply_array);

goto_url($redirect_url);