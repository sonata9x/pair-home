<?php
// 댓글 토큰 AJAX - 사용 중단 (2025-10-30)
// 토큰 검증 방식에서 권한 기반 검증으로 변경되어 더 이상 사용되지 않음

include_once('./_common.php');

// $ss_name = 'ss_comment_token';
// set_session($ss_name, '');
// $token = _token();
// set_session($ss_name, $token);
// die(json_encode(array('token'=>$token)));

// 호환성을 위해 빈 응답 반환
die(json_encode(array('token'=>'')));
