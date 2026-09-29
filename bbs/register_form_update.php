<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/register.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

// 리퍼러 체크
referer_check();

if (!($w == '' || $w == 'u')) {
    alert('w 값이 제대로 넘어오지 않았습니다.');
}

// CAPTCHA 검증 임시 비활성화 (HTTP 환경 문제로)
// if (run_replace('register_member_chk_captcha', !chk_captcha(), $w)) {
//     alert('자동등록방지 숫자가 틀렸습니다.');
// }

if($w == 'u')
    $mb_id = isset($_SESSION['ss_mb_id']) ? trim($_SESSION['ss_mb_id']) : '';
else if($w == '')
    $mb_id = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
else
    alert('잘못된 접근입니다', G5_URL);

if(!$mb_id)
    alert('회원아이디 값이 없습니다. 올바른 방법으로 이용해 주십시오.');

$mb_password    = isset($_POST['mb_password']) ? trim($_POST['mb_password']) : '';
$mb_password_re = isset($_POST['mb_password_re']) ? trim($_POST['mb_password_re']) : '';
$mb_name        = isset($_POST['mb_name']) ? trim($_POST['mb_name']) : '';
$mb_email       = isset($_POST['mb_email']) ? trim($_POST['mb_email']) : '';
$mb_homepage    = isset($_POST['mb_homepage']) ? trim($_POST['mb_homepage']) : '';
$mb_signature   = isset($_POST['mb_signature'])    ? trim($_POST['mb_signature'])   : "";
$mb_1           = isset($_POST['mb_1'])             ? trim($_POST['mb_1'])           : "";
$mb_2           = isset($_POST['mb_2'])             ? trim($_POST['mb_2'])           : "";
$mb_3           = isset($_POST['mb_3'])             ? trim($_POST['mb_3'])           : "";
$mb_4           = isset($_POST['mb_4'])             ? trim($_POST['mb_4'])           : "";
$mb_5           = isset($_POST['mb_5'])             ? trim($_POST['mb_5'])           : "";
$mb_6           = isset($_POST['mb_6'])             ? trim($_POST['mb_6'])           : "";
$mb_7           = isset($_POST['mb_7'])             ? trim($_POST['mb_7'])           : "";
$mb_8           = isset($_POST['mb_8'])             ? trim($_POST['mb_8'])           : "";
$mb_9           = isset($_POST['mb_9'])             ? trim($_POST['mb_9'])           : "";
$mb_10          = isset($_POST['mb_10'])            ? trim($_POST['mb_10'])          : "";

$mb_name        = clean_xss_tags($mb_name, 1, 1);
$mb_email       = get_email_address($mb_email);
$mb_homepage    = sql_real_escape_string(clean_xss_tags($mb_homepage, 1, 1));

run_event('register_form_update_before', $mb_id, $w);

if ($w == '' || $w == 'u') {

    if ($msg = empty_mb_id($mb_id))         alert($msg, "", true, true); // alert($msg, $url, $error, $post);
    if ($msg = valid_mb_id($mb_id))         alert($msg, "", true, true);
    if ($msg = count_mb_id($mb_id))         alert($msg, "", true, true);

    // 이름, 닉네임에 utf-8 이외의 문자가 포함됐다면 오류
    // 서버환경에 따라 정상적으로 체크되지 않을 수 있음.
    $tmp_mb_name = iconv('UTF-8', 'UTF-8//IGNORE', $mb_name);
    if($tmp_mb_name != $mb_name) {
        alert('이름을 올바르게 입력해 주십시오.');
    }
    // 비밀번호를 체크하는 상태의 기본값은 true이며, 비밀번호를 체크하지 않으려면 hook 을 통해 false 값으로 바꿔야 합니다.
    $is_check_password = run_replace('register_member_password_check', true, $mb_id, $mb_name, $mb_email, $w);

    if ($is_check_password){
        if ($w == '' && !$mb_password)
            alert('비밀번호가 넘어오지 않았습니다.');
        if ($w == '' && $mb_password != $mb_password_re)
            alert('비밀번호가 일치하지 않습니다.');
    }
}

// 사용자 코드 실행
@include_once($member_skin_path.'/register_form_update.head.skin.php');

//===============================================================
if ($w == '') {
    $sql_certify = ""; // 변수 초기화
    
    $sql = " insert into {$g5['member_table']}
                set mb_id = '{$mb_id}',
                     mb_password = '".get_encrypt_string($mb_password)."',
                     mb_name = '{$mb_name}',
                     mb_email = '{$mb_email}',
                     mb_homepage = '{$mb_homepage}',
                     mb_signature = '{$mb_signature}',
                     mb_today_login = '".G5_TIME_YMDHIS."',
                     mb_datetime = '".G5_TIME_YMDHIS."',
                     mb_ip = '{$_SERVER['REMOTE_ADDR']}',
                     mb_level = '{$config['cf_register_level']}',
                     mb_login_ip = '{$_SERVER['REMOTE_ADDR']}',
                     mb_1 = '{$mb_1}',
                     mb_2 = '{$mb_2}',
                     mb_3 = '{$mb_3}',
                     mb_4 = '{$mb_4}',
                     mb_5 = '{$mb_5}',
                     mb_6 = '{$mb_6}',
                     mb_7 = '{$mb_7}',
                     mb_8 = '{$mb_8}',
                     mb_9 = '{$mb_9}',
                     mb_10 = '{$mb_10}'
                     {$sql_certify} ";

    // 이메일 인증을 사용하지 않는다면 이메일 인증시간을 바로 넣는다
    if (!$config['cf_use_email_certify'])
        $sql .= " , mb_email_certify = '".G5_TIME_YMDHIS."' ";
    sql_query($sql);

    // 회원가입 포인트 부여
    insert_point($mb_id, $config['cf_register_point'], '회원가입 축하', '@member', $mb_id, '회원가입');

    // 메일인증 사용하지 않는 경우에만 로그인
    if (!$config['cf_use_email_certify']) {
        set_session('ss_mb_id', $mb_id);
        if(function_exists('update_auth_session_token')) update_auth_session_token(G5_TIME_YMDHIS);
    }

    set_session('ss_mb_reg', $mb_id);
    
} else if ($w == 'u') {
    if (!trim(get_session('ss_mb_id')))
        alert('로그인 되어 있지 않습니다.');

    if (trim($_POST['mb_id']) != $mb_id)
        alert("로그인된 정보와 수정하려는 정보가 틀리므로 수정할 수 없습니다.\\n만약 올바르지 않은 방법을 사용하신다면 바로 중지하여 주십시오.");

    // 기존 회원 정보를 가져와서 이전 이메일 확인
    $mb = get_member($mb_id);
    $old_email = $mb['mb_email'];

    $sql_password = "";
    if ($mb_password)
        $sql_password = " , mb_password = '".get_encrypt_string($mb_password)."' ";

    // 이전 메일주소와 수정한 메일주소가 틀리다면 인증을 다시 해야하므로 값을 삭제
    $sql_email_certify = '';
    if ($old_email != $mb_email && $config['cf_use_email_certify'])
        $sql_email_certify = " , mb_email_certify = '' ";

    $sql = " update {$g5['member_table']}
                set mb_name = '{$mb_name}',
                    mb_email = '{$mb_email}',
                    mb_homepage = '{$mb_homepage}',
                    mb_signature = '{$mb_signature}',
                    mb_1 = '{$mb_1}',
                    mb_2 = '{$mb_2}',
                    mb_3 = '{$mb_3}',
                    mb_4 = '{$mb_4}',
                    mb_5 = '{$mb_5}',
                    mb_6 = '{$mb_6}',
                    mb_7 = '{$mb_7}',
                    mb_8 = '{$mb_8}',
                    mb_9 = '{$mb_9}',
                    mb_10 = '{$mb_10}'
                    {$sql_password}
                    {$sql_email_certify}
              where mb_id = '$mb_id' ";
    sql_query($sql);
}



// 인증메일 발송
// 신규가입($w == '')이거나 이메일이 변경된 경우에 인증메일 발송
$old_email = isset($old_email) ? $old_email : '';
if ($config['cf_use_email_certify'] && $config['cf_email_use'] && ($w == '' || $old_email != $mb_email)) {
    $subject = '['.$config['cf_title'].'] 인증확인 메일입니다.';

    // 어떠한 회원정보도 포함되지 않은 일회용 난수를 생성하여 인증에 사용
    $mb_md5 = md5(pack('V*', rand(), rand(), rand(), rand()));

    sql_query(" update {$g5['member_table']} set mb_email_certify2 = '$mb_md5' where mb_id = '$mb_id' ");

    $certify_href = G5_BBS_URL.'/email_certify.php?mb_id='.$mb_id.'&amp;mb_md5='.$mb_md5;

    ob_start();
    include_once ('./register_form_update_mail3.php');
    $content = ob_get_contents();
    ob_end_clean();
    
    $content = run_replace('register_form_update_mail_certify_content', $content, $mb_id);

    mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $mb_email, $subject, $content, 1);

    run_event('register_form_update_send_certify_mail', $config['cf_admin_email_name'], $config['cf_admin_email'], $mb_email, $subject, $content);
}


// 신규회원 쿠폰발생
// if($w == '' && isset($default['de_member_reg_coupon_use']) && $default['de_member_reg_coupon_use'] && $default['de_member_reg_coupon_term'] > 0 && $default['de_member_reg_coupon_price'] > 0) {
//     $j = 0;
//     $create_coupon = false;

//     do {
//         $cp_id = get_coupon_id();

//         $sql3 = " select count(*) as cnt from {$g5['g5_shop_coupon_table']} where cp_id = '$cp_id' ";
//         $row3 = sql_fetch($sql3);

//         if(!$row3['cnt']) {
//             $create_coupon = true;
//             break;
//         } else {
//             if($j > 20)
//                 break;
//         }
//     } while(1);

//     if($create_coupon) {
//         $cp_subject = '신규 회원가입 축하 쿠폰';
//         $cp_method = 2;
//         $cp_target = '';
//         $cp_start = G5_TIME_YMD;
//         $cp_end = date("Y-m-d", (G5_SERVER_TIME + (86400 * ((int)$default['de_member_reg_coupon_term'] - 1))));
//         $cp_type = 0;
//         $cp_price = $default['de_member_reg_coupon_price'];
//         $cp_trunc = 1;
//         $cp_minimum = $default['de_member_reg_coupon_minimum'];
//         $cp_maximum = 0;

//         $sql = " INSERT INTO {$g5['g5_shop_coupon_table']}
//                     ( cp_id, cp_subject, cp_method, cp_target, mb_id, cp_start, cp_end, cp_type, cp_price, cp_trunc, cp_minimum, cp_maximum, cp_datetime )
//                 VALUES
//                     ( '$cp_id', '$cp_subject', '$cp_method', '$cp_target', '$mb_id', '$cp_start', '$cp_end', '$cp_type', '$cp_price', '$cp_trunc', '$cp_minimum', '$cp_maximum', '".G5_TIME_YMDHIS."' ) ";

//         $res = sql_query($sql, false);

//         if($res)
//             set_session('ss_member_reg_coupon', 1);
//     }
// }


// 사용자 코드 실행
@include_once ($member_skin_path.'/register_form_update.tail.skin.php');

if ($msg)
    echo '<script>alert(\''.$msg.'\');</script>';

run_event('register_form_update_after', $mb_id, $w);

if ($w == '') {
    goto_url(G5_HTTP_BBS_URL.'/register_result.php');
} else if ($w == 'u') {
    $row  = sql_fetch(" select mb_password from {$g5['member_table']} where mb_id = '{$member['mb_id']}' ");
    $tmp_password = $row['mb_password'];

    if ($old_email != $mb_email && $config['cf_use_email_certify']) {
        set_session('ss_mb_id', '');
        alert('회원 정보가 수정 되었습니다.\n\nE-mail 주소가 변경되었으므로 다시 인증하셔야 합니다.', G5_URL);
    } else {
        echo '
        <!doctype html>
        <html lang="ko">
        <head>
        <meta charset="utf-8">
        <title>회원정보수정</title>
        <body>
        <form name="fregisterupdate" method="post" action="'.G5_HTTP_BBS_URL.'/register_form.php">
        <input type="hidden" name="w" value="u">
        <input type="hidden" name="mb_id" value="'.$mb_id.'">
        <input type="hidden" name="mb_password" value="'.$tmp_password.'">
        <input type="hidden" name="is_update" value="1">
        </form>
        <script>
        alert("회원 정보가 수정 되었습니다.");
        document.fregisterupdate.submit();
        </script>
        </body>
        </html>';
    }
}
