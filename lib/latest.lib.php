<?php
if (!defined('_GNUBOARD_')) exit;

// 최신글 추출 (간소화 버전)
function latest($skin_dir='', $bo_table='', $rows=10, $subject_len=30, $cache_time=1, $options='')
{
    global $g5, $latest_skin_path, $latest_skin_url;

    // 스킨 경로 설정
    if (!$skin_dir) $skin_dir = 'basic';
    $latest_skin_path = G5_SKIN_PATH.'/main/latest/'.$skin_dir;
    $latest_skin_url = G5_SKIN_URL.'/main/latest/'.$skin_dir;

    $time_unit = 3600;  // 1시간으로 고정

    $caches = false;

    // 캐시 시간이 0이면 캐시 사용 안 함 (즉시 반영)
    if(G5_USE_CACHE && $cache_time > 0) {
        $cache_file_name = "latest-{$bo_table}-{$rows}-{$subject_len}-".g5_cache_secret_key();
        $caches = g5_get_cache($cache_file_name, (int) $time_unit * (int) $cache_time);
        $cache_list = isset($caches['list']) ? $caches['list'] : array();
    }

    if( $caches === false ){
        $list = array();
        $board = get_board_db($bo_table, true);

        if( ! $board ){
            return '';
        }

        $bo_subject = get_text($board['bo_subject']);
        $tmp_write_table = $g5['write_prefix'] . $bo_table;

        // 타임라인 게시판인 경우 원본 글만 가져오기 (tm_origin = wr_id)
        $timeline_condition = '';
        if (isset($board['bo_type']) && $board['bo_type'] === 'timeline') {
            $timeline_condition = ' AND tm_origin = wr_id';
        }

        $sql = " SELECT wr_id, wr_num, wr_reply, wr_parent, wr_subject, wr_name, wr_datetime, wr_comment, wr_option
                 FROM {$tmp_write_table}
                 WHERE wr_is_comment = 0
                   AND wr_subject != ''
                   {$timeline_condition}
                 ORDER BY wr_datetime DESC
                 LIMIT 0, {$rows} ";
        
        $result = sql_query($sql);
        for ($i=0; $row = sql_fetch_array($result); $i++) {
            $is_secret = strpos($row['wr_option'], 'secret') !== false;

            $list[$i] = array(
                'wr_id' => $row['wr_id'],
                'wr_parent' => $row['wr_parent'],
                'wr_comment' => $row['wr_comment'],
                'bo_table' => $bo_table,
                'bo_subject' => $bo_subject,
                'subject' => $is_secret ? '비밀글입니다.' : conv_subject($row['wr_subject'], $subject_len, "…"),
                'wr_name' => $row['wr_name'],
                'name' => $row['wr_name'],
                'datetime' => $row['wr_datetime'],
                'datetime2' => substr($row['wr_datetime'], 2, 14),
                'href' => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&amp;wr_id='.$row['wr_id'],
                'comment_cnt' => $row['wr_comment'] ? '('.$row['wr_comment'].')' : '',
                'is_reply' => ($row['wr_reply'] != '') ? true : false,
                'reply_depth' => strlen($row['wr_reply']),
                'is_secret' => $is_secret
            );
        }

        // 캐시 시간이 0이면 캐시 저장 안 함
        if(G5_USE_CACHE && $cache_time > 0) {
            $caches = array(
                'list' => $list,
                'bo_subject' => sql_escape_string($bo_subject),
            );
            g5_set_cache($cache_file_name, $caches, (int) $time_unit * (int) $cache_time);
        }
    } else {
        $list = $cache_list;
        $bo_subject = (is_array($caches) && isset($caches['bo_subject'])) ? $caches['bo_subject'] : '';
    }

    ob_start();
    include $latest_skin_path.'/latest.skin.php';
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}

// 전체 게시판에서 최신글 불러오기
function latest_all($skin_dir='', $rows=10, $subject_len=30, $cache_time=1, $exclude_tables='')
{
    global $g5, $latest_skin_path, $latest_skin_url;

    // 스킨 경로 설정
    if (!$skin_dir) $skin_dir = 'basic';
    $latest_skin_path = G5_SKIN_PATH.'/main/latest/'.$skin_dir;
    $latest_skin_url = G5_SKIN_URL.'/main/latest/'.$skin_dir;

    $time_unit = 3600;

    $caches = false;
    $cache_key = "latest_all-{$rows}-{$subject_len}-{$exclude_tables}";

    // 캐시 시간이 0이면 캐시 사용 안 함 (즉시 반영)
    if(G5_USE_CACHE && $cache_time > 0) {
        $cache_file_name = $cache_key."-".g5_cache_secret_key();
        $caches = g5_get_cache($cache_file_name, (int) $time_unit * (int) $cache_time);
        $cache_list = isset($caches['list']) ? $caches['list'] : array();
    }

    if( $caches === false ){
        $list = array();

        // 제외할 게시판 처리
        $exclude_where = '';
        if($exclude_tables) {
            $exclude_arr = explode(',', $exclude_tables);
            $exclude_where = " AND bo_table NOT IN ('".implode("','", array_map('trim', $exclude_arr))."') ";
        }

        // 모든 게시판 조회 (읽기 권한 9 미만인 게시판만)
        $board_sql = "SELECT bo_table, bo_subject, bo_type FROM {$g5['board_table']} WHERE bo_read_level < 9 {$exclude_where} ORDER BY bo_order";
        $board_result = sql_query($board_sql);

        $union_queries = array();
        while($board = sql_fetch_array($board_result)) {
            $write_table = $g5['write_prefix'] . $board['bo_table'];

            // 테이블 존재 확인
            $table_check = sql_query("SHOW TABLES LIKE '{$write_table}'", false);
            if(sql_num_rows($table_check) > 0) {
                // Collation 문제 해결: CONVERT 함수 사용
                $bo_table_escaped = sql_escape_string($board['bo_table']);
                $bo_subject_escaped = sql_escape_string($board['bo_subject']);

                // 타임라인 게시판인 경우 원본 글만 가져오기
                $timeline_condition = '';
                if (isset($board['bo_type']) && $board['bo_type'] === 'timeline') {
                    $timeline_condition = ' AND tm_origin = wr_id';
                }

                $union_queries[] = "
                    SELECT CONVERT('{$bo_table_escaped}' USING utf8mb4) COLLATE utf8mb4_general_ci as bo_table,
                           CONVERT('{$bo_subject_escaped}' USING utf8mb4) COLLATE utf8mb4_general_ci as bo_subject,
                           wr_id, wr_num,
                           CONVERT(wr_reply USING utf8mb4) COLLATE utf8mb4_general_ci as wr_reply,
                           wr_parent,
                           CONVERT(wr_subject USING utf8mb4) COLLATE utf8mb4_general_ci as wr_subject,
                           CONVERT(wr_name USING utf8mb4) COLLATE utf8mb4_general_ci as wr_name,
                           CONVERT(LEFT(wr_content, 200) USING utf8mb4) COLLATE utf8mb4_general_ci as wr_content,
                           CONVERT(wr_option USING utf8mb4) COLLATE utf8mb4_general_ci as wr_option,
                           wr_datetime, wr_comment
                    FROM {$write_table}
                    WHERE wr_is_comment = 0
                      AND wr_subject != ''
                      {$timeline_condition}
                ";
            }
        }

        if(!empty($union_queries)) {
            $sql = "SELECT * FROM (" . implode(' UNION ALL ', $union_queries) . ") as latest_posts
                    ORDER BY wr_datetime DESC
                    LIMIT 0, {$rows}";

            $result = sql_query($sql, false);

            for ($i=0; $row = sql_fetch_array($result); $i++) {
                $is_secret = isset($row['wr_option']) && strpos($row['wr_option'], 'secret') !== false;

                // HTML 태그 제거 후 내용 미리보기 생성
                $content_preview = '';
                if ($is_secret) {
                    $content_preview = '';
                } else if (!empty($row['wr_content'])) {
                    $content_preview = strip_tags($row['wr_content']);
                    $content_preview = preg_replace('/\s+/', ' ', $content_preview); // 연속 공백 제거
                    $content_preview = trim($content_preview);
                    $content_preview = mb_substr($content_preview, 0, 50, 'UTF-8');
                    if (mb_strlen(strip_tags($row['wr_content']), 'UTF-8') > 50) {
                        $content_preview .= '…';
                    }
                }

                $list[$i] = array(
                    'wr_id' => $row['wr_id'],
                    'wr_parent' => $row['wr_parent'],
                    'wr_comment' => $row['wr_comment'],
                    'bo_table' => $row['bo_table'],
                    'bo_subject' => $row['bo_subject'],
                    'subject' => $is_secret ? '비밀글입니다.' : conv_subject($row['wr_subject'], $subject_len, "…"),
                    'content' => $content_preview,
                    'wr_name' => $row['wr_name'],
                    'name' => $row['wr_name'],
                    'datetime' => $row['wr_datetime'],
                    'datetime2' => substr($row['wr_datetime'], 2, 8),
                    'href' => G5_BBS_URL.'/board.php?bo_table='.$row['bo_table'].'&amp;wr_id='.$row['wr_id'],
                    'comment_cnt' => $row['wr_comment'] ? '('.$row['wr_comment'].')' : '',
                    'is_reply' => ($row['wr_reply'] != '') ? true : false,
                    'reply_depth' => strlen($row['wr_reply']),
                    'is_secret' => $is_secret
                );
            }
        }

        // 캐시 시간이 0이면 캐시 저장 안 함
        if(G5_USE_CACHE && $cache_time > 0) {
            $caches = array('list' => $list);
            g5_set_cache($cache_file_name, $caches, (int) $time_unit * (int) $cache_time);
        }
    } else {
        $list = $cache_list;
    }

    // 전체 최신글용 변수 설정
    $bo_subject = '전체 최신글';

    ob_start();
    include $latest_skin_path.'/latest.skin.php';
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}

/**
 * 최신 상품 목록 조회 (store_gallery용)
 *
 * @param string $bo_table 게시판 ID (기본값: 'shop')
 * @param int $rows 조회할 상품 개수
 * @param array $options 추가 옵션 (category, cache_time 등)
 * @return array 상품 목록 배열
 */
function get_latest_products($bo_table = 'shop', $rows = 6, $options = array())
{
    global $g5;

    $cache_time = isset($options['cache_time']) ? (int)$options['cache_time'] : 1;
    $category = isset($options['category']) ? trim($options['category']) : '';

    $time_unit = 3600;
    $caches = false;

    // 캐시 처리
    if(G5_USE_CACHE && $cache_time > 0) {
        $cache_key = "latest_products-{$bo_table}-{$rows}-{$category}";
        $cache_file_name = $cache_key."-".g5_cache_secret_key();
        $caches = g5_get_cache($cache_file_name, (int) $time_unit * (int) $cache_time);
    }

    if( $caches === false ){
        $list = array();
        $board = get_board_db($bo_table, true);

        if( ! $board ){
            return array();
        }

        $write_table = $g5['write_prefix'] . $bo_table;

        // 카테고리 필터
        $category_where = '';
        if($category) {
            $category_where = " AND ca_name = '".sql_escape_string($category)."' ";
        }

        $sql = " SELECT wr_id, wr_subject, wr_content, wr_datetime, wr_hit, wr_comment,
                        wr_1, wr_2, wr_3, ca_name, wr_img
                 FROM {$write_table}
                 WHERE wr_is_comment = 0
                   AND wr_subject != ''
                   AND (wr_2 IS NULL OR wr_2 = '' OR wr_2 = '0')
                   AND (wr_option IS NULL OR wr_option NOT LIKE '%secret%')
                   {$category_where}
                 ORDER BY wr_datetime DESC
                 LIMIT 0, {$rows} ";

        $result = sql_query($sql);

        // 썸네일 라이브러리 로드
        if (file_exists(G5_LIB_PATH . '/thumbnail.lib.php')) {
            include_once(G5_LIB_PATH . '/thumbnail.lib.php');
        }

        for ($i=0; $row = sql_fetch_array($result); $i++) {
            // 가격 정보 파싱
            $original_price = !empty($row['wr_1']) ? (int)$row['wr_1'] : 0;
            $discount = !empty($row['wr_2']) ? trim($row['wr_2']) : '';
            $final_price = $original_price;
            $discount_amount = 0;
            $is_on_sale = false;

            if ($discount) {
                if (strpos($discount, '%') !== false) {
                    // 퍼센트 할인
                    $discount_rate = (int)str_replace('%', '', $discount);
                    $discount_amount = floor($original_price * $discount_rate / 100);
                } else {
                    // 금액 할인
                    $discount_amount = (int)$discount;
                }
                $final_price = $original_price - $discount_amount;
                $is_on_sale = true;
            }

            // 썸네일 URL 생성 (wr_img 필드만 사용)
            $thumb_url = '';
            if (!empty($row['wr_img'])) {
                // URL이나 경로가 포함된 경우 그대로 사용, 파일명만 있으면 경로 추가
                if (preg_match('/^(https?:\/\/|\/)/i', $row['wr_img']) || strpos($row['wr_img'], '/') !== false) {
                    $thumb_url = $row['wr_img'];
                } else {
                    $thumb_url = G5_DATA_URL . '/file/' . $bo_table . '/' . $row['wr_img'];
                }
            }

            $list[$i] = array(
                'wr_id' => $row['wr_id'],
                'subject' => conv_subject($row['wr_subject'], 60, "…"),
                'datetime' => $row['wr_datetime'],
                'hit' => $row['wr_hit'],
                'comment' => $row['wr_comment'],
                'href' => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$row['wr_id'],
                'category' => $row['ca_name'],
                'tags' => !empty($row['wr_3']) ? explode(',', $row['wr_3']) : array(),
                // 가격 정보
                'original_price' => $original_price,
                'discount' => $discount,
                'final_price' => $final_price,
                'discount_amount' => $discount_amount,
                'is_on_sale' => $is_on_sale,
                'discount_rate' => ($original_price > 0 && $discount_amount > 0)
                    ? floor(($discount_amount / $original_price) * 100) : 0,
                // 썸네일
                'thumb' => $thumb_url
            );
        }

        // 캐시 저장
        if(G5_USE_CACHE && $cache_time > 0) {
            g5_set_cache($cache_file_name, $list, (int) $time_unit * (int) $cache_time);
        }
    } else {
        $list = $caches;
    }

    return $list;
}

/**
 * 할인 상품 목록 조회 (SALE)
 *
 * @param string $bo_table 게시판 ID (기본값: 'shop')
 * @param int $rows 조회할 상품 개수
 * @param array $options 추가 옵션
 * @return array 상품 목록 배열
 */
function get_sale_products($bo_table = 'shop', $rows = 6, $options = array())
{
    global $g5;

    $cache_time = isset($options['cache_time']) ? (int)$options['cache_time'] : 1;

    $time_unit = 3600;
    $caches = false;

    // 캐시 처리
    if(G5_USE_CACHE && $cache_time > 0) {
        $cache_key = "sale_products-{$bo_table}-{$rows}";
        $cache_file_name = $cache_key."-".g5_cache_secret_key();
        $caches = g5_get_cache($cache_file_name, (int) $time_unit * (int) $cache_time);
    }

    if( $caches === false ){
        $list = array();
        $board = get_board_db($bo_table, true);

        if( ! $board ){
            return array();
        }

        $write_table = $g5['write_prefix'] . $bo_table;

        // wr_2 필드에 값이 있는 것만 (할인 상품)
        $sql = " SELECT wr_id, wr_subject, wr_content, wr_datetime, wr_hit, wr_comment,
                        wr_1, wr_2, wr_3, ca_name, wr_img
                 FROM {$write_table}
                 WHERE wr_is_comment = 0
                   AND wr_subject != ''
                   AND wr_2 IS NOT NULL
                   AND wr_2 != ''
                   AND (wr_option IS NULL OR wr_option NOT LIKE '%secret%')
                 ORDER BY wr_datetime DESC
                 LIMIT 0, {$rows} ";

        $result = sql_query($sql);

        for ($i=0; $row = sql_fetch_array($result); $i++) {
            // 가격 정보 파싱
            $original_price = !empty($row['wr_1']) ? (int)$row['wr_1'] : 0;
            $discount = trim($row['wr_2']);
            $final_price = $original_price;
            $discount_amount = 0;

            if (strpos($discount, '%') !== false) {
                // 퍼센트 할인
                $discount_rate = (int)str_replace('%', '', $discount);
                $discount_amount = floor($original_price * $discount_rate / 100);
            } else {
                // 금액 할인
                $discount_amount = (int)$discount;
            }
            $final_price = $original_price - $discount_amount;

            // 썸네일 URL 생성 (wr_img 필드만 사용)
            $thumb_url = '';
            if (!empty($row['wr_img'])) {
                // URL이나 경로가 포함된 경우 그대로 사용, 파일명만 있으면 경로 추가
                if (preg_match('/^(https?:\/\/|\/)/i', $row['wr_img']) || strpos($row['wr_img'], '/') !== false) {
                    $thumb_url = $row['wr_img'];
                } else {
                    $thumb_url = G5_DATA_URL . '/file/' . $bo_table . '/' . $row['wr_img'];
                }
            }

            $list[$i] = array(
                'wr_id' => $row['wr_id'],
                'subject' => conv_subject($row['wr_subject'], 60, "…"),
                'datetime' => $row['wr_datetime'],
                'hit' => $row['wr_hit'],
                'comment' => $row['wr_comment'],
                'href' => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$row['wr_id'],
                'category' => $row['ca_name'],
                'tags' => !empty($row['wr_3']) ? explode(',', $row['wr_3']) : array(),
                // 가격 정보
                'original_price' => $original_price,
                'discount' => $discount,
                'final_price' => $final_price,
                'discount_amount' => $discount_amount,
                'is_on_sale' => true,
                'discount_rate' => ($original_price > 0 && $discount_amount > 0)
                    ? floor(($discount_amount / $original_price) * 100) : 0,
                // 썸네일
                'thumb' => $thumb_url
            );
        }

        // 캐시 저장
        if(G5_USE_CACHE && $cache_time > 0) {
            g5_set_cache($cache_file_name, $list, (int) $time_unit * (int) $cache_time);
        }
    } else {
        $list = $caches;
    }

    return $list;
}

/**
 * 인기 상품 목록 조회 (판매량 순)
 *
 * @param string $bo_table 게시판 ID (기본값: 'shop')
 * @param int $rows 조회할 상품 개수
 * @param array $options 추가 옵션
 * @return array 상품 목록 배열
 */
function get_popular_products($bo_table = 'shop', $rows = 6, $options = array())
{
    global $g5;

    $cache_time = isset($options['cache_time']) ? (int)$options['cache_time'] : 1;

    $time_unit = 3600;
    $caches = false;

    // 캐시 처리
    if(G5_USE_CACHE && $cache_time > 0) {
        $cache_key = "popular_products-{$bo_table}-{$rows}";
        $cache_file_name = $cache_key."-".g5_cache_secret_key();
        $caches = g5_get_cache($cache_file_name, (int) $time_unit * (int) $cache_time);
    }

    if( $caches === false ){
        $list = array();
        $board = get_board_db($bo_table, true);

        if( ! $board ){
            return array();
        }

        $write_table = $g5['write_prefix'] . $bo_table;

        // wr_store_sales 필드로 정렬 (판매량 순)
        $sql = " SELECT wr_id, wr_subject, wr_content, wr_datetime, wr_hit, wr_comment,
                        wr_1, wr_2, wr_3, ca_name, wr_img, wr_store_sales
                 FROM {$write_table}
                 WHERE wr_is_comment = 0
                   AND wr_subject != ''
                   AND wr_store_sales > 0
                   AND (wr_option IS NULL OR wr_option NOT LIKE '%secret%')
                 ORDER BY wr_store_sales DESC, wr_datetime DESC
                 LIMIT 0, {$rows} ";

        $result = sql_query($sql);

        for ($i=0; $row = sql_fetch_array($result); $i++) {
            // 가격 정보 파싱
            $original_price = !empty($row['wr_1']) ? (int)$row['wr_1'] : 0;
            $discount = !empty($row['wr_2']) ? trim($row['wr_2']) : '';
            $final_price = $original_price;
            $discount_amount = 0;
            $is_on_sale = false;

            if ($discount) {
                if (strpos($discount, '%') !== false) {
                    $discount_rate = (int)str_replace('%', '', $discount);
                    $discount_amount = floor($original_price * $discount_rate / 100);
                } else {
                    $discount_amount = (int)$discount;
                }
                $final_price = $original_price - $discount_amount;
                $is_on_sale = true;
            }

            // 썸네일 URL 생성
            $thumb_url = '';
            if (!empty($row['wr_img'])) {
                if (preg_match('/^(https?:\/\/|\/)/i', $row['wr_img']) || strpos($row['wr_img'], '/') !== false) {
                    $thumb_url = $row['wr_img'];
                } else {
                    $thumb_url = G5_DATA_URL . '/file/' . $bo_table . '/' . $row['wr_img'];
                }
            }

            $list[$i] = array(
                'wr_id' => $row['wr_id'],
                'subject' => conv_subject($row['wr_subject'], 60, "…"),
                'datetime' => $row['wr_datetime'],
                'hit' => $row['wr_hit'],
                'comment' => $row['wr_comment'],
                'href' => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$row['wr_id'],
                'category' => $row['ca_name'],
                'tags' => !empty($row['wr_3']) ? explode(',', $row['wr_3']) : array(),
                'sales_count' => $row['wr_store_sales'],
                // 가격 정보
                'original_price' => $original_price,
                'discount' => $discount,
                'final_price' => $final_price,
                'discount_amount' => $discount_amount,
                'is_on_sale' => $is_on_sale,
                'discount_rate' => ($original_price > 0 && $discount_amount > 0)
                    ? floor(($discount_amount / $original_price) * 100) : 0,
                // 썸네일
                'thumb' => $thumb_url
            );
        }

        // 캐시 저장
        if(G5_USE_CACHE && $cache_time > 0) {
            g5_set_cache($cache_file_name, $list, (int) $time_unit * (int) $cache_time);
        }
    } else {
        $list = $caches;
    }

    return $list;
}

/**
 * MD 추천 상품 목록 조회
 *
 * @param string $bo_table 게시판 ID (기본값: 'shop')
 * @param int $rows 조회할 상품 개수
 * @param array $options 추가 옵션
 * @return array 상품 목록 배열
 */
function get_recommended_products($bo_table = 'shop', $rows = 4, $options = array())
{
    global $g5;

    $cache_time = isset($options['cache_time']) ? (int)$options['cache_time'] : 1;

    $time_unit = 3600;
    $caches = false;

    // 캐시 처리
    if(G5_USE_CACHE && $cache_time > 0) {
        $cache_key = "recommended_products-{$bo_table}-{$rows}";
        $cache_file_name = $cache_key."-".g5_cache_secret_key();
        $caches = g5_get_cache($cache_file_name, (int) $time_unit * (int) $cache_time);
    }

    if( $caches === false ){
        $list = array();
        $board = get_board_db($bo_table, true);

        if( ! $board ){
            return array();
        }

        $write_table = $g5['write_prefix'] . $bo_table;

        // wr_recommend = 1인 상품만
        $sql = " SELECT wr_id, wr_subject, wr_content, wr_datetime, wr_hit, wr_comment,
                        wr_1, wr_2, wr_3, ca_name, wr_img, wr_recommend
                 FROM {$write_table}
                 WHERE wr_is_comment = 0
                   AND wr_subject != ''
                   AND wr_recommend = 1
                   AND (wr_option IS NULL OR wr_option NOT LIKE '%secret%')
                 ORDER BY wr_datetime DESC
                 LIMIT 0, {$rows} ";

        $result = sql_query($sql);

        for ($i=0; $row = sql_fetch_array($result); $i++) {
            // 가격 정보 파싱
            $original_price = !empty($row['wr_1']) ? (int)$row['wr_1'] : 0;
            $discount = !empty($row['wr_2']) ? trim($row['wr_2']) : '';
            $final_price = $original_price;
            $discount_amount = 0;
            $is_on_sale = false;

            if ($discount) {
                if (strpos($discount, '%') !== false) {
                    $discount_rate = (int)str_replace('%', '', $discount);
                    $discount_amount = floor($original_price * $discount_rate / 100);
                } else {
                    $discount_amount = (int)$discount;
                }
                $final_price = $original_price - $discount_amount;
                $is_on_sale = true;
            }

            // 썸네일 URL 생성
            $thumb_url = '';
            if (!empty($row['wr_img'])) {
                if (preg_match('/^(https?:\/\/|\/)/i', $row['wr_img']) || strpos($row['wr_img'], '/') !== false) {
                    $thumb_url = $row['wr_img'];
                } else {
                    $thumb_url = G5_DATA_URL . '/file/' . $bo_table . '/' . $row['wr_img'];
                }
            }

            $list[$i] = array(
                'wr_id' => $row['wr_id'],
                'subject' => conv_subject($row['wr_subject'], 60, "…"),
                'datetime' => $row['wr_datetime'],
                'hit' => $row['wr_hit'],
                'comment' => $row['wr_comment'],
                'href' => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$row['wr_id'],
                'category' => $row['ca_name'],
                'tags' => !empty($row['wr_3']) ? explode(',', $row['wr_3']) : array(),
                // 가격 정보
                'original_price' => $original_price,
                'discount' => $discount,
                'final_price' => $final_price,
                'discount_amount' => $discount_amount,
                'is_on_sale' => $is_on_sale,
                'discount_rate' => ($original_price > 0 && $discount_amount > 0)
                    ? floor(($discount_amount / $original_price) * 100) : 0,
                // 썸네일
                'thumb' => $thumb_url
            );
        }

        // 캐시 저장
        if(G5_USE_CACHE && $cache_time > 0) {
            g5_set_cache($cache_file_name, $list, (int) $time_unit * (int) $cache_time);
        }
    } else {
        $list = $caches;
    }

    return $list;
}

/**
 * 최근 구매 내역 조회 (실시간 알림용)
 *
 * @param int $rows 조회할 개수
 * @return array 구매 내역 배열
 */
function get_recent_purchases($rows = 10)
{
    global $g5;

    if (!defined('G5_STORE_ORDER_TABLE')) {
        return array();
    }

    $list = array();

    // 최근 결제 완료된 주문 조회
    $sql = " SELECT o.od_id, o.mb_id, o.bo_table, o.wr_id, o.od_datetime,
                    w.wr_subject, m.mb_name
             FROM " . G5_STORE_ORDER_TABLE . " o
             LEFT JOIN " . $g5['member_table'] . " m ON o.mb_id = m.mb_id
             LEFT JOIN " . $g5['write_prefix'] . "shop w ON o.wr_id = w.wr_id
             WHERE o.od_status = 'paid'
             ORDER BY o.od_paid_datetime DESC
             LIMIT 0, {$rows} ";

    $result = sql_query($sql);

    while ($row = sql_fetch_array($result)) {
        // 익명화 처리 (개인정보 보호)
        $display_name = mb_substr($row['mb_name'], 0, 1) . str_repeat('*', mb_strlen($row['mb_name']) - 1);

        $list[] = array(
            'od_id' => $row['od_id'],
            'mb_id' => $row['mb_id'],
            'display_name' => $display_name,
            'product_name' => conv_subject($row['wr_subject'], 30, "…"),
            'datetime' => $row['od_datetime'],
            'time_ago' => get_time_ago($row['od_datetime']),
            'href' => G5_BBS_URL.'/board.php?bo_table='.$row['bo_table'].'&wr_id='.$row['wr_id']
        );
    }

    return $list;
}

/**
 * 시간 경과 표시 (방금, 1분 전, 1시간 전 등)
 *
 * @param string $datetime 날짜시간 문자열
 * @return string 경과 시간 문자열
 */
function get_time_ago($datetime)
{
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) {
        return '방금';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . '분 전';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . '시간 전';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . '일 전';
    } else {
        return date('Y-m-d', $time);
    }
}
?>
