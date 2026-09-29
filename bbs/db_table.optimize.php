<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 최고관리자일 때만 실행
if($config['cf_admin'] != $member['mb_id'] || $is_admin != 'super')
    return;

// 실행일 비교
if(isset($config['cf_optimize_date']) && $config['cf_optimize_date'] >= G5_TIME_YMD)
    return;

// 자캐 커뮤니티용 간소화 - 필수 정리만 실행

// 설정일이 지난 쪽지 삭제
if($config['cf_memo_del'] > 0) {
    $sql = " delete from {$g5['memo_table']} where DATEDIFF('".G5_TIME_YMDHIS."', me_send_datetime) > '{$config['cf_memo_del']}' ";
    sql_query($sql);
}

// 탈퇴회원 자동 삭제  
if($config['cf_leave_day'] > 0) {
    $sql = " select mb_id from {$g5['member_table']}
                where DATEDIFF('".G5_TIME_YMDHIS."', mb_leave_date) > '{$config['cf_leave_day']}'
                  and mb_memo not like '%삭제함%' ";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        // 회원자료 삭제
        member_delete($row['mb_id']);
    }
}

// 실행일 기록
if(isset($config['cf_optimize_date'])) {
    sql_query(" update {$g5['config_table']} set cf_optimize_date = '".G5_TIME_YMD."' ");
}

// 불필요한 시스템들 제거됨:
// - 방문자 통계, 인기검색어, 최근게시물, 음성캡챠