<?php
if (!defined('_GNUBOARD_')) exit;

// 현재 접속자수 출력
function connect()
{
    global $config, $g5, $connect_skin_path;

    // 회원, 방문객 카운트 (관리자 비포함)
    // $sql = " select sum(IF(mb_id<>'',1,0)) as mb_cnt, count(*) as total_cnt from {$g5['login_table']} where mb_id <> '{$config['cf_admin']}' ";
    // 회원, 방문객 카운트 (관리자 포함)
    $sql = " select sum(IF(mb_id<>'',1,0)) as mb_cnt, count(*) as total_cnt from {$g5['login_table']} ";
    $row = sql_fetch($sql);

    ob_start();
    include_once ($connect_skin_path.'/connect.skin.php');
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}

// 현재 접속자 목록 반환
function connect_list()
{
    global $config, $g5, $member;

    $list = array();

    // 로그인 테이블에서 접속자 정보 가져오기
    $sql = " select * from {$g5['login_table']} order by lo_datetime desc ";
    $result = sql_query($sql);

    $num = 1;
    while ($row = sql_fetch_array($result)) {
        // 회원 정보 가져오기
        $ch_name = '';
        $ch_portrait = '';
        if ($row['mb_id']) {
            $mb = get_member($row['mb_id']);
            $name = $mb['mb_name'] ? $mb['mb_name'] : $mb['mb_name'];
            $mb_signature = $mb['mb_signature'] ?? '';

            // 대표 캐릭터 정보
            if (function_exists('get_character')) {
                $ch = get_character($row['mb_id']);
                if ($ch && !empty($ch['ch_name'])) {
                    $ch_name = $ch['ch_name'];
                    $ch_portrait = $ch['ch_portrait_image'] ?? '';
                }
            }
        } else {
            $name = '비회원';
            $mb_signature = '';
        }

        $list[] = array(
            'num' => $num++,
            'mb_id' => $row['mb_id'],
            'name' => $name,
            'mb_signature' => $mb_signature,
            'ch_name' => $ch_name,
            'ch_portrait' => $ch_portrait,
            'lo_location' => $row['lo_location'],
            'lo_url' => $row['lo_url'],
            'lo_datetime' => $row['lo_datetime']
        );
    }

    return $list;
}
?>
