<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/register.lib.php');

run_event('register_form_before');

// 불법접근을 막도록 토큰생성
$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);

if ($w == "") {

    // 회원 로그인을 한 경우 회원가입 할 수 없다
    // 경고창이 뜨는것을 막기위해 아래의 코드로 대체
    // alert("이미 로그인중이므로 회원 가입 하실 수 없습니다.", "./");
    if ($is_member) {
        goto_url(G5_URL);
    }

    // 리퍼러 체크
    referer_check();

    // 약관 동의 체크 (iframe 환경 고려)
    $agree_check = isset($_POST['agree']) ? $_POST['agree'] : (isset($_SESSION['ss_agree']) ? $_SESSION['ss_agree'] : '');
    $agree2_check = isset($_POST['agree2']) ? $_POST['agree2'] : (isset($_SESSION['ss_agree2']) ? $_SESSION['ss_agree2'] : '');
    
    if (!$agree_check) {
        alert('회원가입약관의 내용에 동의하셔야 회원가입 하실 수 있습니다.', G5_BBS_URL.'/register.php');
    }

    if (!$agree2_check) {
        alert('개인정보 수집 및 이용의 내용에 동의하셔야 회원가입 하실 수 있습니다.', G5_BBS_URL.'/register.php');
    }
    
    // 세션에 동의 정보 저장 (다음 단계에서 사용)
    set_session('ss_agree', $agree_check);
    set_session('ss_agree2', $agree2_check);

    $agree  = preg_replace('#[^0-9]#', '', $_POST['agree']);
    $agree2 = preg_replace('#[^0-9]#', '', $_POST['agree2']);

    // 삭제된 개인정보 컬럼들 (mb_birth, mb_sex) - 주석처리
    // $member['mb_birth'] = '';
    // $member['mb_sex']   = '';
    $member['mb_name']  = '';
    $member['mb_homepage'] = '';
    // if (isset($_POST['birth'])) {
    //     $member['mb_birth'] = $_POST['birth'];
    // }
    // if (isset($_POST['sex'])) {
    //     $member['mb_sex']   = $_POST['sex'];
    // }
    if (isset($_POST['mb_name'])) {
        $member['mb_name']  = $_POST['mb_name'];
    }

    $g5['title'] = '회원 가입';

} else if ($w == 'u') {

    if ($is_admin == 'super')
        alert('관리자의 회원정보는 관리자 화면에서 수정해 주십시오.', G5_URL);

    if (!$is_member)
        alert('로그인 후 이용하여 주십시오.', G5_URL);

    if ($member['mb_id'] != $_POST['mb_id'])
        alert('로그인된 회원과 넘어온 정보가 서로 다릅니다.');

    /*
    if (!($member[mb_password] == sql_password($_POST[mb_password]) && $_POST[mb_password]))
        alert("비밀번호가 틀립니다.");

    // 수정 후 다시 이 폼으로 돌아오기 위해 임시로 저장해 놓음
    set_session("ss_tmp_password", $_POST[mb_password]);
    */
    
    if($_POST['mb_id'] && ! (isset($_POST['mb_password']) && $_POST['mb_password'])){
        if( ! $is_social_login_modify ){
            alert('비밀번호를 입력해 주세요.');
        }
    }

    if (isset($_POST['mb_password'])) {
        // 수정된 정보를 업데이트후 되돌아 온것이라면 비밀번호가 암호화 된채로 넘어온것임
        if (isset($_POST['is_update']) && $_POST['is_update']) {
            $tmp_password = $_POST['mb_password'];
            $pass_check = ($member['mb_password'] === $tmp_password);
        } else {
            $pass_check = check_password($_POST['mb_password'], $member['mb_password']);
        }

        if (!$pass_check)
            alert('비밀번호가 틀립니다.');
    }

    $g5['title'] = '회원 정보 수정';

    set_session("ss_reg_mb_name", $member['mb_name']);
    // 삭제된 hp 컬럼 - 주석처리
    // set_session("ss_reg_mb_hp", $member['mb_hp']);

    $member['mb_email']       = get_text($member['mb_email']);
    $member['mb_homepage']    = get_text($member['mb_homepage'] ?? '');
    // 삭제된 개인정보 컬럼들 - 주석처리
    // $member['mb_birth']       = get_text($member['mb_birth']);
    // $member['mb_tel']         = get_text($member['mb_tel']);
    // $member['mb_hp']          = get_text($member['mb_hp']);
    // 삭제된 주소 및 서명 컴럼 - 주석처리
    // $member['mb_addr1']       = get_text($member['mb_addr1']);
    // $member['mb_addr2']       = get_text($member['mb_addr2']);
    // $member['mb_signature']   = get_text($member['mb_signature']);
    // 삭제된 추천인 컬럼 - 주석처리
    // $member['mb_recommend']   = get_text($member['mb_recommend']);
    // 삭제된 프로필 컴럼 - 주석처리
    // $member['mb_profile']     = get_text($member['mb_profile']);
    $member['mb_1']           = get_text($member['mb_1']);
    $member['mb_2']           = get_text($member['mb_2']);
    $member['mb_3']           = get_text($member['mb_3']);
    $member['mb_4']           = get_text($member['mb_4']);
    $member['mb_5']           = get_text($member['mb_5']);
    $member['mb_6']           = get_text($member['mb_6']);
    $member['mb_7']           = get_text($member['mb_7']);
    $member['mb_8']           = get_text($member['mb_8']);
    $member['mb_9']           = get_text($member['mb_9']);
    $member['mb_10']          = get_text($member['mb_10']);
} else {
    alert('w 값이 제대로 넘어오지 않았습니다.');
}

include_once('./_head.php');

// 삭제된 회원아이콘/이미지 관련 코드 - 주석처리
// 회원아이콘 경로
// $mb_icon_path = G5_DATA_PATH.'/member/'.substr($member['mb_id'],0,2).'/'.get_mb_icon_name($member['mb_id']).'.gif';
// $mb_icon_filemtile = (defined('G5_USE_MEMBER_IMAGE_FILETIME') && G5_USE_MEMBER_IMAGE_FILETIME && file_exists($mb_icon_path)) ? '?'.filemtime($mb_icon_path) : '';
// $mb_icon_url  = G5_DATA_URL.'/member/'.substr($member['mb_id'],0,2).'/'.get_mb_icon_name($member['mb_id']).'.gif'.$mb_icon_filemtile;

// 회원이미지 경로
// $mb_img_path = G5_DATA_PATH.'/member_image/'.substr($member['mb_id'],0,2).'/'.get_mb_icon_name($member['mb_id']).'.gif';
// $mb_img_filemtile = (defined('G5_USE_MEMBER_IMAGE_FILETIME') && G5_USE_MEMBER_IMAGE_FILETIME && file_exists($mb_img_path)) ? '?'.filemtime($mb_img_path) : '';
// $mb_img_url  = G5_DATA_URL.'/member_image/'.substr($member['mb_id'],0,2).'/'.get_mb_icon_name($member['mb_id']).'.gif'.$mb_img_filemtile;

$register_action_url = G5_BBS_URL.'/register_form_update.php';

// iframe 환경에서 온 경우 iframe_skip 파라미터 추가
if (isset($_GET['iframe_skip'])) {
    $register_action_url .= '?iframe_skip=1';
}
// 삭제된 닉네임 관련 설정 - 기본값으로 설정
$required = ($w=='') ? 'required' : '';
$readonly = ($w=='u') ? 'readonly' : '';
// 삭제된 본인인증 관련 설정들 - 단순화
$name_readonly = ($w=='u') ? 'readonly' : ''; // 수정시에만 읽기전용
// 삭제된 hp 관련 설정들 - 주석처리
// $hp_required = ($config['cf_req_hp'] || (($config['cf_cert_use'] && $config['cf_cert_req']) && ($config['cf_cert_hp'] || $config['cf_cert_simple']) && $member['mb_certify'] != "ipin")) ? 'required':'';
// $hp_readonly = (($config['cf_cert_use'] && $config['cf_cert_req']) && ($config['cf_cert_hp'] || $config['cf_cert_simple']) && $member['mb_certify'] != "ipin") ? 'readonly':'';

$agree  = isset($_REQUEST['agree']) ? preg_replace('#[^0-9]#', '', $_REQUEST['agree']) : '';
$agree2 = isset($_REQUEST['agree2']) ? preg_replace('#[^0-9]#', '', $_REQUEST['agree2']) : '';

include_once($member_skin_path.'/register_form.skin.php');

run_event('register_form_after', $w, $agree, $agree2);

include_once('./_tail.php');
