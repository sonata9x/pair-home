<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 타임라인 테이블에 컬럼이 있는지 확인 후 추가
$field_query = "SHOW COLUMNS FROM $write_table LIKE 'tm_origin'";
$field_exists = sql_fetch($field_query);
if (!$field_exists) {
    sql_query("ALTER TABLE $write_table ADD COLUMN tm_origin INT(11) NOT NULL DEFAULT '0' AFTER wr_10", false);
    sql_query("ALTER TABLE $write_table ADD COLUMN tm_parent INT(11) NOT NULL DEFAULT '0' AFTER tm_origin", false);
    sql_query("ALTER TABLE $write_table ADD INDEX idx_timeline (tm_origin, tm_parent)", false);
}

// 게시글 읽기 정보
$view = get_view($write, $board, $board_skin_url);

if (strstr($sfl, 'subject'))
    $view['subject'] = search_font($stx, $view['subject']);

// ra0-content div 래퍼 제거 (저장 시 자동 추가된 것)
$raw_content = $view['wr_content'];
$raw_content = preg_replace('/<div class="ra0-content">(.*)<\/div>/s', '$1', $raw_content);

// 본문 처리 및 사용되지 않은 이미지 추출
// 순서: 사용안한이미지추출 → convert_placeholders_to_images → conv_content → autolink
$unused_images = array();
$view['content'] = process_content_with_unused_images(
    $raw_content,
    $bo_table,
    $view['wr_id'],
    $board['bo_use_dhtml_editor'],
    $unused_images
);

if (strstr($sfl, 'content'))
    $view['content'] = search_font($stx, $view['content']);

// rich_content는 처리된 content (이미 autolink 적용됨)
$view['rich_content'] = $view['content'];

// 사용되지 않은 이미지를 view에 저장
$view['unused_images'] = $unused_images;

// 스레드 구조 가져오기
function get_timeline_thread($write_table, $origin_id, $current_id = 0) {
    $threads = array();
    
    // 원글인지 확인
    if ($origin_id) {
        // 타임라인 컬럼이 있는 경우
        $sql = " SELECT * FROM {$write_table} 
                 WHERE tm_origin = '{$origin_id}' 
                 ORDER BY wr_id ASC ";
    } else {
        // 타임라인 컬럼이 없는 경우 (댓글로 대체)
        $sql = " SELECT * FROM {$write_table} 
                 WHERE wr_parent = '{$current_id}' 
                 ORDER BY wr_comment, wr_comment_reply ";
    }
    
    $result = sql_query($sql);
    
    while ($row = sql_fetch_array($result)) {
        $threads[] = $row;
    }
    
    return $threads;
}

// 스레드를 트리 구조로 변환
function build_thread_tree($threads) {
    $tree = array();
    $lookup = array();
    
    // 먼저 모든 스레드를 ID로 인덱싱
    foreach ($threads as $thread) {
        $thread['children'] = array();
        $lookup[$thread['wr_id']] = $thread;
    }
    
    // 부모-자식 관계 설정
    foreach ($lookup as $id => &$thread) {
        if ($thread['tm_parent'] && $thread['tm_parent'] != $thread['wr_id']) {
            // 부모가 있으면 부모의 children에 추가
            if (isset($lookup[$thread['tm_parent']])) {
                $lookup[$thread['tm_parent']]['children'][] = &$thread;
            }
        } else if ($thread['tm_parent'] == $thread['wr_id'] || $thread['tm_origin'] == $thread['wr_id']) {
            // 원글이면 트리의 루트에 추가
            $tree[] = &$thread;
        }
    }
    
    return $tree;
}

// 현재 글이 원글인지 답글인지 확인
$is_origin = false;
$origin_id = 0;

if ($view['tm_origin']) {
    // 타임라인 컬럼이 있는 경우
    $origin_id = $view['tm_origin'];
    $is_origin = ($view['tm_origin'] == $view['wr_id']);
} else {
    // 타임라인 컬럼이 없는 경우
    $is_origin = ($view['wr_parent'] == $view['wr_id'] && $view['wr_is_comment'] == 0);
    if ($is_origin) {
        $origin_id = $view['wr_id'];
    } else {
        // 댓글인 경우 원글 찾기
        $origin = sql_fetch(" SELECT * FROM {$write_table} WHERE wr_id = '{$view['wr_parent']}' ");
        $origin_id = $origin['wr_id'];
    }
}

// 전체 스레드 가져오기
if ($origin_id) {
    $thread_list = get_timeline_thread($write_table, $origin_id, $view['wr_id']);
} else {
    $thread_list = array($view);
}

// 트리 구조로 변환
if ($view['tm_origin']) {
    $thread_tree = build_thread_tree($thread_list);
} else {
    // 타임라인 컬럼이 없으면 플랫 리스트로 표시
    $thread_tree = $thread_list;
}

// 이전글, 다음글 (원글 기준으로만)
$sql_prev_next = " (tm_origin = tm_parent OR (tm_origin = 0 AND tm_parent = 0 AND wr_parent = wr_id)) AND wr_is_comment = 0 ";

// 이전글
$sql = " select wr_id, wr_subject, wr_datetime from {$write_table} 
         where wr_id < '{$view['wr_id']}' 
         and {$sql_prev_next} ";
if (!empty($board['bo_notice'])) {
    $sql .= " and wr_id not in (".str_replace(',', "','", "'".$board['bo_notice']."'").") ";
}
$sql .= " order by wr_id desc limit 1 ";
$prev = sql_fetch($sql);

// 다음글
$sql = " select wr_id, wr_subject, wr_datetime from {$write_table} 
         where wr_id > '{$view['wr_id']}' 
         and {$sql_prev_next} ";
if (!empty($board['bo_notice'])) {
    $sql .= " and wr_id not in (".str_replace(',', "','", "'".$board['bo_notice']."'").") ";
}
$sql .= " order by wr_id limit 1 ";
$next = sql_fetch($sql);

// 이전글 링크
$prev_href = '';
if (isset($prev['wr_id']) && $prev['wr_id']) {
    $prev_wr_subject = get_text(cut_str($prev['wr_subject'], 255));
    $prev_href = get_pretty_url($bo_table, $prev['wr_id'], $qstr);
    $prev_wr_date = $prev['wr_datetime'];
}

// 다음글 링크
$next_href = '';
if (isset($next['wr_id']) && $next['wr_id']) {
    $next_wr_subject = get_text(cut_str($next['wr_subject'], 255));
    $next_href = get_pretty_url($bo_table, $next['wr_id'], $qstr);
    $next_wr_date = $next['wr_datetime'];
}

// 쓰기 링크
$write_href = '';
if ($member['mb_level'] >= $board['bo_write_level']) {
    $write_href = short_url_clean(G5_BBS_URL.'/write.php?bo_table='.$bo_table);
}

// 답변 링크 (타임라인에서는 답글, 글쓰기 권한과 동일하게 적용)
$reply_href = '';
if ($member['mb_level'] >= $board['bo_write_level']) {
    $reply_href = short_url_clean(G5_BBS_URL.'/write.php?w=r&amp;bo_table='.$bo_table.'&amp;wr_id='.$view['wr_id'].$qstr);
}

// 수정, 삭제 링크
$update_href = $delete_href = '';
// 로그인중이고 자신의 글이라면 또는 관리자라면 비밀번호를 묻지 않고 바로 수정, 삭제 가능
if (($member['mb_id'] && ($member['mb_id'] === $write['mb_id'])) || $is_admin) {
    $update_href = short_url_clean(G5_BBS_URL.'/write.php?w=u&amp;bo_table='.$bo_table.'&amp;wr_id='.$write['wr_id'].'&amp;page='.$page.$qstr);
    set_session('ss_delete_token', $token = uniqid(time()));
    $delete_href = G5_BBS_URL.'/delete.php?bo_table='.$bo_table.'&amp;wr_id='.$write['wr_id'].'&amp;token='.$token.'&amp;page='.$page.urldecode($qstr);
}
else if (!$write['mb_id']) { // 회원이 쓴 글이 아니라면
    $update_href = G5_BBS_URL.'/password.php?w=u&amp;bo_table='.$bo_table.'&amp;wr_id='.$write['wr_id'].'&amp;page='.$page.$qstr;
    $delete_href = G5_BBS_URL.'/password.php?w=d&amp;bo_table='.$bo_table.'&amp;wr_id='.$write['wr_id'].'&amp;page='.$page.$qstr;
}

// 최고, 그룹관리자라면 글 복사, 이동 가능
$copy_href = $move_href = '';
if ($write['wr_reply'] == '' && ($is_admin == 'super' || $is_admin == 'group')) {
    $copy_href = G5_BBS_URL.'/move.php?sw=copy&amp;bo_table='.$bo_table.'&amp;wr_id='.$write['wr_id'].'&amp;page='.$page.$qstr;
    $move_href = G5_BBS_URL.'/move.php?sw=move&amp;bo_table='.$bo_table.'&amp;wr_id='.$write['wr_id'].'&amp;page='.$page.$qstr;
}

$scrap_href = '';
if ($member['mb_id'] && isset($board['bo_use_scrap']) && $board['bo_use_scrap']) {
    $scrap_href = G5_BBS_URL.'/scrap_popin.php?bo_table='.$bo_table.'&amp;wr_id='.$write['wr_id'];
}

$search_href = get_pretty_url($bo_table, '', $qstr);

include_once($board_skin_path.'/view.skin.php');