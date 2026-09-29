<?php
include_once('./_common.php');

if (!$member['mb_id'])
    alert('회원만 접근하실 수 있습니다.');

if ($is_admin == 'super')
    alert('최고 관리자는 탈퇴할 수 없습니다');

$post_mb_password = isset($_POST['mb_password']) ? trim($_POST['mb_password']) : '';

if (!($post_mb_password && check_password($post_mb_password, $member['mb_password'])))
    alert('비밀번호가 틀립니다.');

// 회원탈퇴일을 저장
// RA0 에디션은 mb_certify, mb_adult 컬럼을 제거함 (common.php:131 참고).
// 원본 그누보드처럼 UPDATE에 포함하면 "Unknown column" 에러로 쿼리 전체가 실패하여
// 탈퇴 처리가 안 되고 alert만 뜨는 버그가 있으므로 RA0 스키마와 정합되는 컬럼만 갱신한다.
//
// member_delete() 와 동일하게 mb_password 와 mb_level 도 초기화한다.
// - mb_password='': mb_leave_date 가드가 어떤 이유로든 무력화되더라도 로그인 불가
// - mb_level=1: 잔여 관리자 권한 제거
$date = date("Ymd");
$sql = " update {$g5['member_table']} set mb_leave_date = '{$date}', mb_password = '', mb_level = 1, mb_memo = '".date('Ymd', G5_SERVER_TIME)." 탈퇴함\n".sql_real_escape_string($member['mb_memo'])."' where mb_id = '{$member['mb_id']}' ";
sql_query($sql);

run_event('member_leave', $member);

// 3.09 수정 (로그아웃)
unset($_SESSION['ss_mb_id']);

if (!$url)
    $url = G5_URL;

//소셜로그인 해제
if(function_exists('social_member_link_delete')){
    social_member_link_delete($member['mb_id']);
}

alert(''.$member['mb_name'].'님께서는 '. date("Y년 m월 d일") .'에 회원에서 탈퇴 하셨습니다.', $url);