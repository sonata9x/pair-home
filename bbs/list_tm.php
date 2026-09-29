<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// 타임라인 테이블에 컬럼이 있는지 확인 후 추가
$field_query = "SHOW COLUMNS FROM $write_table LIKE 'tm_origin'";
$field_exists = sql_fetch($field_query);
if (!$field_exists) {
    sql_query("ALTER TABLE $write_table ADD COLUMN tm_origin INT(11) NOT NULL DEFAULT '0' AFTER wr_10", false);
    sql_query("ALTER TABLE $write_table ADD COLUMN tm_parent INT(11) NOT NULL DEFAULT '0' AFTER tm_origin", false);
    sql_query("ALTER TABLE $write_table ADD INDEX idx_timeline (tm_origin, tm_parent)", false);
}

// ─── 탭 처리 (타임라인 / 대화 / 멘션) ───
$tl_tab = isset($_GET['tab']) ? $_GET['tab'] : '';
$is_mentions_tab = false;
$is_conversations_tab = false;
$mention_where = '';
$conversation_where = '';

if ($tl_tab === 'conversations') {
    $is_conversations_tab = true;

    // 대화 WHERE: 모든 답글 (원글이 아닌 글)
    $conversation_where = " tm_origin != wr_id AND tm_origin > 0 AND wr_is_comment = 0 ";

} else if ($tl_tab === 'mentions' && isset($member['mb_id']) && $member['mb_id']) {
    $safe_mb_id_tl = sql_real_escape_string($member['mb_id']);
    $my_ch_name_for_mention = '';

    if (function_exists('is_community_installed') && is_community_installed() && function_exists('get_character')) {
        $my_ch_tl = get_character($member['mb_id']);
        if ($my_ch_tl && !empty($my_ch_tl['ch_name'])) {
            $my_ch_name_for_mention = $my_ch_tl['ch_name'];
        }
    }

    $is_mentions_tab = true;

    // 멘션 WHERE: 내 글에 대한 답글 OR 나를 @멘션한 글
    $mention_where = "(
        (tm_origin IN (SELECT wr_id FROM {$write_table} WHERE mb_id = '{$safe_mb_id_tl}' AND tm_origin = wr_id)
         AND mb_id != '{$safe_mb_id_tl}' AND wr_id != tm_origin)";

    if ($my_ch_name_for_mention) {
        $like_ch_name = str_replace(array('%', '_'), array('\\%', '\\_'), sql_real_escape_string($my_ch_name_for_mention));
        $like_at = '%@' . $like_ch_name . '%';
        $like_at_brace = '%@{' . $like_ch_name . '}%';
        $mention_where .= "
        OR (wr_content LIKE '{$like_at}' AND mb_id != '{$safe_mb_id_tl}')
        OR (wr_content LIKE '{$like_at_brace}' AND mb_id != '{$safe_mb_id_tl}')";
    }

    $mention_where .= ") AND wr_is_comment = 0";
}

// 분류 사용 여부
$is_category = false;
$category_option = '';
if ($board['bo_use_category']) {
    $is_category = true;
    $category_href = get_pretty_url($bo_table);

    $category_option .= '<li><a href="'.$category_href.'"';
    if ($sca=='')
        $category_option .= ' id="bo_cate_on"';
    $category_option .= '>전체</a></li>';

    $categories = explode('|', $board['bo_category_list']); // 구분자가 | 로 되어 있음
    for ($i=0; $i<count($categories); $i++) {
        $category = trim($categories[$i]);
        if ($category=='') continue;
        $category_option .= '<li><a href="'.(get_pretty_url($bo_table,'','sca='.urlencode($category))).'"';
        $category_msg = '';
        if ($category==$sca) { // 현재 선택된 카테고리라면
            $category_option .= ' id="bo_cate_on"';
            $category_msg = '<span class="sound_only">열린 분류 </span>';
        }
        $category_option .= '>'.$category_msg.$category.'</a></li>';
    }
}

$sop = strtolower($sop);
if ($sop != 'and' && $sop != 'or')
    $sop = 'and';

// 분류 선택 또는 검색어가 있다면
$stx = trim($stx);
$is_search_bbs = false;

// 해시태그 검색 파라미터 가져오기
$hash = get_hash_param();

if ($is_conversations_tab) {
    $is_search_bbs = false;
    $sql = " SELECT COUNT(*) AS cnt FROM {$write_table} WHERE {$conversation_where} ";
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];
} else if ($is_mentions_tab) {
    $is_search_bbs = false;
    $sql = " SELECT COUNT(DISTINCT wr_id) AS cnt FROM {$write_table} WHERE {$mention_where} ";
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];
} else if ($sca || $stx || $stx === '0' || !empty($hash)) {     //검색이면
    $is_search_bbs = true;
    
    // 해시태그 검색인 경우
    if (!empty($hash)) {
        // # 기호가 없으면 추가
        $search_hash = (strpos($hash, '#') === 0) ? $hash : '#' . $hash;
        $search_hash = sql_real_escape_string($search_hash);
        $sql_search = " wr_content LIKE '%{$search_hash}%' ";
    }
    // 캐릭터명 검색인 경우 특별 처리
    else if ($sfl == 'ch_name') {
        // 캐릭터명으로 검색시 해당 캐릭터의 mb_id를 찾아서 검색
        $search_mb_ids = array();
        $ch_sql = "SELECT mb_id FROM {$g5['community_character_table']} WHERE ch_name LIKE '%{$stx}%'";
        $ch_result = sql_query($ch_sql);
        while ($ch_row = sql_fetch_array($ch_result)) {
            $search_mb_ids[] = "'".$ch_row['mb_id']."'";
        }

        if (count($search_mb_ids) > 0) {
            $sql_search = " mb_id IN (".implode(',', $search_mb_ids).") ";
        } else {
            $sql_search = " 1=0 "; // 검색 결과 없음
        }
    } else {
        $sql_search = get_sql_search($sca, $sfl, $stx, $sop);
    }
    
    // 타임라인에서는 원글만 검색
    $sql_search .= " and (tm_origin = wr_id OR (tm_origin = 0 AND wr_parent = wr_id)) ";
    
    // 검색 결과 카운트
    $sql = " SELECT COUNT(DISTINCT wr_id) AS cnt FROM {$write_table} WHERE {$sql_search} ";
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];
} else {
    $sql_search = "";
    
    // 원글만 카운트 (tm_origin = wr_id 또는 타임라인 컬럼이 없는 경우 wr_parent = wr_id)
    $sql = " SELECT COUNT(*) AS cnt FROM {$write_table} 
             WHERE (tm_origin = wr_id OR (tm_origin = 0 AND wr_parent = wr_id))
             AND wr_is_comment = 0 ";
    $row = sql_fetch($sql);
    $total_count = $row['cnt'];
}

if(G5_IS_MOBILE) {
    $page_rows = $board['bo_mobile_page_rows'];
    $list_page_rows = $board['bo_mobile_page_rows'];
} else {
    $page_rows = $board['bo_page_rows'];
    $list_page_rows = $board['bo_page_rows'];
}

// 페이지당 행 수가 0이면 기본값 설정 (Division by zero 방지)
if ($page_rows < 1) $page_rows = 15;
if ($list_page_rows < 1) $list_page_rows = 15;

if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)

// 년도 2자리
$today2 = G5_TIME_YMD;

$list = array();
$i = 0;
$notice_count = 0;
$notice_array = array();

// 공지 처리 (대화/멘션 탭에서는 공지 표시 안 함)
if (!$is_search_bbs && !$is_mentions_tab && !$is_conversations_tab) {
    $arr_notice = explode(',', trim($board['bo_notice']));
    $from_notice_idx = ($page - 1) * $page_rows;
    if($from_notice_idx < 0)
        $from_notice_idx = 0;
    $board_notice_count = count($arr_notice);

    for ($k=0; $k<$board_notice_count; $k++) {
        if (trim($arr_notice[$k]) == '') continue;

        $row = sql_fetch(" select * from {$write_table} where wr_id = '{$arr_notice[$k]}' ");

        if (!isset($row['wr_id']) || !$row['wr_id']) continue;

        $notice_array[] = $row['wr_id'];

        if($k < $from_notice_idx) continue;

        $list[$i] = get_list($row, $board, $board_skin_url, (G5_IS_MOBILE && isset($board['bo_mobile_subject_len'])) ? $board['bo_mobile_subject_len'] : $board['bo_subject_len']);
        $list[$i]['is_notice'] = true;
        
        // 답글 수 계산
        $reply_count = sql_fetch(" SELECT COUNT(*) as cnt FROM {$write_table} WHERE tm_origin = '{$row['wr_id']}' AND wr_id != '{$row['wr_id']}' ");
        $list[$i]['reply_count'] = $reply_count['cnt'];

        $i++;
        $notice_count++;

        if($notice_count >= $list_page_rows)
            break;
    }
}

$total_page  = ceil($total_count / $page_rows);  // 전체 페이지 계산
$from_record = ($page - 1) * $page_rows; // 시작 열을 구함

// 공지글이 있으면 변수에 반영
if(!empty($notice_array)) {
    $from_record -= count($notice_array);

    if($from_record < 0)
        $from_record = 0;

    if($notice_count > 0)
        $page_rows -= $notice_count;

    if($page_rows < 0)
        $page_rows = $list_page_rows;
}

// 정렬
$sql_order = '';
if ($sst) {
    $sql_order = " order by {$sst} {$sod} ";
} else {
    $sql_order = ' order by wr_id desc ';
}

// 원글만 가져오기
if ($is_conversations_tab) {
    $sql = " SELECT * FROM {$write_table} WHERE {$conversation_where} ORDER BY wr_id DESC LIMIT {$from_record}, {$page_rows} ";
} else if ($is_mentions_tab) {
    $sql = " SELECT DISTINCT * FROM {$write_table} WHERE {$mention_where} ORDER BY wr_id DESC LIMIT {$from_record}, {$page_rows} ";
} else if ($is_search_bbs) {
    $sql = " select * from {$write_table} where {$sql_search} {$sql_order} limit {$from_record}, $page_rows ";
} else {
    // 원글만 가져오기: tm_origin = wr_id인 경우 (자기 자신이 원글)
    $sql = " select * from {$write_table}
             where (tm_origin = wr_id OR (tm_origin = 0 AND wr_parent = wr_id))
             and wr_is_comment = 0 ";
    if(!empty($notice_array))
        $sql .= " and wr_id not in (".implode(', ', $notice_array).") ";
    $sql .= " {$sql_order} limit {$from_record}, $page_rows ";
}

// 페이지의 공지개수가 목록수 보다 작을 때만 실행
if($page_rows > 0) {
    $result = sql_query($sql);

    $k = 0;

    while ($row = sql_fetch_array($result))
    {
        $list[$i] = get_list($row, $board, $board_skin_url, (G5_IS_MOBILE && isset($board['bo_mobile_subject_len'])) ? $board['bo_mobile_subject_len'] : $board['bo_subject_len']);
        
        if (strstr($sfl, 'subject')) {
            $list[$i]['subject'] = search_font($stx, $list[$i]['subject']);
        }
        
        $list[$i]['is_notice'] = false;

        if ($is_conversations_tab || $is_mentions_tab) {
            // 대화/멘션 탭: 답글 미리보기 없이 개별 항목으로 표시
            $list[$i]['reply_count'] = 0;
            $list[$i]['recent_replies'] = array();
            $list[$i]['is_reply_tab'] = true;

            // 원글 정보 (답글의 컨텍스트 표시용)
            if ($row['tm_origin'] && $row['tm_origin'] != $row['wr_id']) {
                $list[$i]['mention_origin'] = sql_fetch(" SELECT wr_id, mb_id, wr_name FROM {$write_table} WHERE wr_id = '{$row['tm_origin']}' ");
            }
        } else {
            // 답글 수 계산 (원글 기준)
            if ($row['tm_origin'] == $row['wr_id']) {
                // 타임라인 원글인 경우
                $reply_sql = " SELECT COUNT(*) as cnt FROM {$write_table} WHERE tm_origin = '{$row['wr_id']}' AND wr_id != '{$row['wr_id']}' ";
            } else {
                // 타임라인 컬럼이 없는 경우 댓글로 대체
                $reply_sql = " SELECT COUNT(*) as cnt FROM {$write_table} WHERE wr_parent = '{$row['wr_id']}' AND wr_is_comment = 1 ";
            }
            $reply_count = sql_fetch($reply_sql);
            $list[$i]['reply_count'] = $reply_count['cnt'];

            // 최근 답글 미리보기 (최신 2개)
            $list[$i]['recent_replies'] = array();
            // 원글인 경우에만 답글 가져오기
            if ($row['tm_origin'] == $row['wr_id'] || (!$row['tm_origin'] && $row['wr_parent'] == $row['wr_id'])) {
                $recent_sql = " SELECT * FROM {$write_table}
                               WHERE tm_origin = '{$row['wr_id']}' AND wr_id != '{$row['wr_id']}'
                               ORDER BY wr_id DESC
                               LIMIT 2 ";
                $recent_result = sql_query($recent_sql);
                while ($recent = sql_fetch_array($recent_result)) {
                    if ($recent['wr_name'] === '시스템') {
                        // 시스템 답글: script-text 내용만 추출
                        preg_match('/<div class="script-text">(.*?)<\/div>/s', $recent['wr_content'], $_sc_m);
                        $_sc_text = !empty($_sc_m[1]) ? html_entity_decode(strip_tags($_sc_m[1])) : strip_tags($recent['wr_content']);
                        $_preview_content = cut_str(trim($_sc_text), 50);
                    } else {
                        $_preview_content = cut_str(strip_tags($recent['wr_content']), 50);
                    }
                    $list[$i]['recent_replies'][] = array(
                        'wr_id' => $recent['wr_id'],
                        'mb_id' => $recent['mb_id'],
                        'wr_name' => $recent['wr_name'],
                        'wr_content' => $_preview_content,
                        'wr_datetime' => $recent['wr_datetime']
                    );
                }
            }
        }

        $list_num = $total_count - ($page - 1) * $list_page_rows - $notice_count;
        $list[$i]['num'] = $list_num - $k;

        $i++;
        $k++;
    }
}

g5_latest_cache_data($board['bo_table'], $list);

$paging_qstr = $qstr;
if ($tl_tab) {
    $paging_qstr .= '&amp;tab=' . urlencode($tl_tab);
}
$write_pages = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, get_pretty_url($bo_table, '', $paging_qstr.'&amp;page='));

$list_href = '';
$prev_part_href = '';
$next_part_href = '';
if ($is_search_bbs) {
    $list_href = get_pretty_url($bo_table);
}

$write_href = '';
if ($member['mb_level'] >= $board['bo_write_level']) {
    $write_href = short_url_clean(G5_BBS_URL.'/write.php?bo_table='.$bo_table);
}

$nobr_begin = $nobr_end = "";
if (preg_match("/gecko|firefox/i", $_SERVER['HTTP_USER_AGENT'])) {
    $nobr_begin = '<nobr>';
    $nobr_end   = '</nobr>';
}

// RSS 보기 사용에 체크가 되어 있어야 RSS 보기 가능 061106
$rss_href = '';
if ($board['bo_use_rss_view']) {
    $rss_href = G5_BBS_URL.'/rss.php?bo_table='.$bo_table;
}

$stx = get_text(stripslashes($stx));

include_once($board_skin_path.'/list.skin.php');