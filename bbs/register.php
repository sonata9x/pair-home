<?php
include_once('./_common.php');

// 로그인중인 경우 회원가입 할 수 없습니다.
if ($is_member) {
    goto_url(G5_URL);
}

// 회원가입 가능 여부 체크
if ($config['cf_new'] == '0') {
    alert('현재 회원가입을 받지 않습니다.');
}

// 세션을 지웁니다.
set_session("ss_mb_reg", "");

$g5['title'] = '회원가입';
include_once('./_head.php');

$register_action_url = G5_BBS_URL.'/register_form.php';
include_once($member_skin_path.'/register.skin.php');

include_once('./_tail.php');
