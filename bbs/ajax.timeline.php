<?php
include_once('./_common.php');

// AJAX JSON 응답 → 에러 출력 차단 (PHP Warning이 JSON을 깨뜨림)
ini_set('display_errors', 0);
error_reporting(0);

// JSON 헤더 설정
header('Content-Type: application/json; charset=utf-8');

$bo_table = isset($_REQUEST['bo_table']) ? preg_replace('/[^a-z0-9_]/i', '', $_REQUEST['bo_table']) : '';

if (!$bo_table) {
    die(json_encode(array('error' => '게시판이 지정되지 않았습니다.')));
}

$board = sql_fetch(" select * from {$g5['board_table']} where bo_table = '$bo_table' ");
$write_table = $g5['write_prefix'] . $bo_table;

// 타임라인 게시판만 사용 가능
if (!isset($board['bo_type']) || $board['bo_type'] != 'timeline') {
    die(json_encode(array('error' => '타임라인 게시판이 아닙니다.')));
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$response = array();

switch($action) {
    case 'write_reply':
        global $config;

        // 답글 작성
        if ($member['mb_level'] < $board['bo_write_level']) {
            $response['error'] = '글을 쓸 권한이 없습니다.';
            break;
        }
        
        $tm_parent = isset($_POST['tm_parent']) ? (int)$_POST['tm_parent'] : 0;
        $wr_content = isset($_POST['wr_content']) ? trim($_POST['wr_content']) : '';

        // ><! 패턴 이스케이프 (HTML 주석 오인 방지)
        $wr_content = str_replace('><!' , '>&lt;!', $wr_content);

        if (!$tm_parent) {
            $response['error'] = '부모글이 지정되지 않았습니다.';
            break;
        }
        
        if (!$wr_content) {
            $response['error'] = '내용을 입력해주세요.';
            break;
        }
        
        // 부모글 정보 가져오기
        $parent = sql_fetch(" SELECT * FROM {$write_table} WHERE wr_id = '{$tm_parent}' ");
        if (!$parent['wr_id']) {
            $response['error'] = '부모글이 존재하지 않습니다.';
            break;
        }
        
        // tm_origin 결정
        if ($parent['tm_origin']) {
            $tm_origin = $parent['tm_origin'];
        } else {
            $tm_origin = $parent['wr_parent'] == $parent['wr_id'] ? $parent['wr_id'] : $parent['wr_parent'];
        }
        
        // 제목은 원글 제목 유지
        $wr_subject = $parent['wr_subject'];
        
        // 글 작성
        $wr_num = 0;
        $wr_reply = '';
        
        if ($member['mb_id']) {
            $mb_id = $member['mb_id'];
            // 캐릭터가 있으면 캐릭터명, 없으면 회원명 사용
            $character = null;
            if (is_community_installed() && function_exists('get_character')) {
                $character = get_character($member['mb_id']);
            }

            if ($character && !empty($character['ch_name'])) {
                // 커뮤니티 설정(cc_name_priority)에 따라 "캐릭터명" 또는 "캐릭터명 [오너명]"
                $display_name = function_exists('community_format_post_name')
                    ? community_format_post_name($character['ch_name'], $member['mb_name'])
                    : $character['ch_name'];
                $wr_name = addslashes(clean_xss_tags($display_name));
            } else {
                $wr_name = addslashes(clean_xss_tags($member['mb_name']));
            }
            $wr_password = '';
            $wr_email = addslashes($member['mb_email']);
        } else {
            $mb_id = '';
            $wr_name = isset($_POST['wr_name']) ? clean_xss_tags(trim($_POST['wr_name'])) : '';
            $wr_password = isset($_POST['wr_password']) ? get_encrypt_string($_POST['wr_password']) : '';
            $wr_email = isset($_POST['wr_email']) ? get_email_address(trim($_POST['wr_email'])) : '';
            
            if (!$wr_name) {
                $response['error'] = '이름을 입력해주세요.';
                break;
            }
        }
        
        // wr_num 값 구하기 (타래 답글은 원글의 wr_num 사용)
        $origin_row = sql_fetch(" SELECT wr_num FROM {$write_table} WHERE wr_id = '{$tm_origin}' ");
        if ($origin_row && $origin_row['wr_num']) {
            $wr_num = (int)$origin_row['wr_num'];
        } else {
            // 원글을 찾을 수 없는 경우 새로운 wr_num 생성
            $wr_num_result = sql_fetch(" SELECT IFNULL(MIN(wr_num), 0) - 1 as min_num FROM {$write_table} ");
            $wr_num = (int)$wr_num_result['min_num'];
        }

        $sql = " INSERT INTO {$write_table}
                    SET wr_num = '{$wr_num}',
                        wr_reply = '',
                        wr_comment = 0,
                        wr_is_comment = 0,
                        ca_name = '',
                        wr_option = '',
                        wr_subject = '".sql_real_escape_string($wr_subject)."',
                        wr_content = '".sql_real_escape_string($wr_content)."',
                        wr_link1 = '',
                        wr_link2 = '',
                        wr_hit = 0,
                        mb_id = '{$mb_id}',
                        wr_password = '{$wr_password}',
                        wr_name = '{$wr_name}',
                        wr_email = '{$wr_email}',
                        wr_homepage = '',
                        wr_datetime = '".G5_TIME_YMDHIS."',
                        wr_last = '".G5_TIME_YMDHIS."',
                        wr_ip = '{$_SERVER['REMOTE_ADDR']}',
                        tm_origin = '{$tm_origin}',
                        tm_parent = '{$tm_parent}',
                        wr_1 = '',
                        wr_2 = '',
                        wr_3 = '',
                        wr_4 = '',
                        wr_5 = '',
                        wr_6 = '',
                        wr_7 = '',
                        wr_8 = '',
                        wr_9 = '',
                        wr_10 = '' ";

        $insert_result = sql_query($sql);
        if (!$insert_result) {
            $response['error'] = '답글 등록에 실패했습니다.';
            break;
        }

        $wr_id = sql_insert_id();

        if (!$wr_id) {
            // 마지막에 삽입된 레코드 조회
            $last_row = sql_fetch(" SELECT * FROM {$write_table} ORDER BY wr_id DESC LIMIT 1 ");
            if ($last_row && $last_row['wr_name'] == $wr_name && $last_row['wr_content'] == $wr_content) {
                $wr_id = $last_row['wr_id'];
            } else {
                $response['error'] = '답글 ID 생성에 실패했습니다.';
                break;
            }
        }
        
        // 부모 아이디 업데이트 (wr_parent는 자기 자신)
        sql_query(" UPDATE {$write_table} SET wr_parent = '{$wr_id}' WHERE wr_id = '{$wr_id}' ");

        // 원글의 wr_last 갱신 (SNS식 bump 정렬: 최신 답글 있는 글이 위로)
        sql_query(" UPDATE {$write_table} SET wr_last = '".G5_TIME_YMDHIS."' WHERE wr_id = '{$tm_origin}' ");

        // 새글 INSERT
        sql_query(" INSERT INTO {$g5['board_new_table']} (bo_table, wr_id, wr_parent, bn_datetime, mb_id) 
                    VALUES ('{$bo_table}', '{$wr_id}', '{$wr_id}', '".G5_TIME_YMDHIS."', '{$member['mb_id']}') ");
        
        // 게시글 수 증가
        sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = bo_count_write + 1 WHERE bo_table = '{$bo_table}' ");

        // 파일 업로드 처리 (최대 2개)
        @mkdir(G5_DATA_PATH.'/file/'.$bo_table, G5_DIR_PERMISSION, true);
        @chmod(G5_DATA_PATH.'/file/'.$bo_table, G5_DIR_PERMISSION);

        $file_count = 0;

        if (isset($_FILES['bf_file']['name']) && is_array($_FILES['bf_file']['name'])) {
            $max_files = min(2, count($_FILES['bf_file']['name'])); // 최대 2개

            for ($i = 0; $i < $max_files; $i++) {
                if (!isset($_FILES['bf_file']['name'][$i]) || !$_FILES['bf_file']['name'][$i]) {
                    continue;
                }

                if (!is_uploaded_file($_FILES['bf_file']['tmp_name'][$i])) {
                    continue;
                }

                $tmp_file = $_FILES['bf_file']['tmp_name'][$i];
                $filesize = $_FILES['bf_file']['size'][$i];
                $filename = $_FILES['bf_file']['name'][$i];
                $filename = preg_replace("/\.(php|phtml|phtm|htm|cgi|pl|exe|jsp|asp|inc)$/i", "$0-x", $filename);

                // 파일 크기 체크 (게시판 설정값 사용)
                $max_filesize = $board['bo_upload_size'] * 1024; // KB를 바이트로 변환
                if ($filesize > $max_filesize) {
                    continue;
                }

                // 파일 확장자 체크
                $timg = false;
                if (preg_match("/\.({$config['cf_image_extension']})$/i", $filename)) {
                    $timg = @getimagesize($tmp_file);
                    if (!$timg || $timg[2] < 1 || $timg[2] > 18) {
                        continue;
                    }
                } else {
                    continue; // 이미지가 아니면 건너뛰기
                }

                // 파일명 생성
                $shuffle = md5(rand(1,999999999).time());
                $bf_file = md5(sha1($_SERVER['REMOTE_ADDR'])).'_'.substr($shuffle,0,8).'_'.$filename;
                $dest_file = G5_DATA_PATH.'/file/'.$bo_table.'/'.$bf_file;

                // 파일 저장
                if (move_uploaded_file($tmp_file, $dest_file)) {
                    chmod($dest_file, G5_FILE_PERMISSION);

                    $bf_source = addslashes($filename);
                    $bf_filesize = (int)$filesize;
                    $bf_width = $timg && isset($timg[0]) ? (int)$timg[0] : 0;
                    $bf_height = $timg && isset($timg[1]) ? (int)$timg[1] : 0;
                    $bf_type = $timg && isset($timg[2]) ? (int)$timg[2] : 0;

                    // 파일 테이블에 INSERT
                    $sql = " INSERT INTO {$g5['board_file_table']}
                                SET bo_table = '{$bo_table}',
                                    wr_id = '{$wr_id}',
                                    bf_no = '{$i}',
                                    bf_source = '{$bf_source}',
                                    bf_file = '{$bf_file}',
                                    bf_content = '',
                                    bf_fileurl = '',
                                    bf_thumburl = '',
                                    bf_storage = '',
                                    bf_download = 0,
                                    bf_filesize = '{$bf_filesize}',
                                    bf_width = '{$bf_width}',
                                    bf_height = '{$bf_height}',
                                    bf_type = '{$bf_type}',
                                    bf_datetime = '".G5_TIME_YMDHIS."' ";
                    sql_query($sql);

                    $file_count++;
                }
            }

            // 파일 개수 업데이트
            if ($file_count > 0) {
                sql_query(" UPDATE {$write_table} SET wr_file = '{$file_count}' WHERE wr_id = '{$wr_id}' ");
            }
        }

        // URL 이미지 처리
        $image_urls_json = isset($_POST['image_urls_json']) ? $_POST['image_urls_json'] : '';
        if ($image_urls_json) {
            $image_urls = json_decode(stripslashes($image_urls_json), true);
            if (is_array($image_urls) && !empty($image_urls)) {
                $url_count = 0;
                $max_url = 2 - $file_count; // 파일 + URL 합쳐서 최대 2개
                foreach ($image_urls as $img_url) {
                    if ($url_count >= $max_url) break;
                    $img_url = trim($img_url);
                    if (!$img_url || !preg_match('/^https?:\/\//i', $img_url)) continue;

                    // URL 이미지 다운로드
                    $img_data = @file_get_contents($img_url);
                    if (!$img_data) continue;

                    // 이미지 타입 확인
                    $tmp_path = tempnam(sys_get_temp_dir(), 'img_');
                    file_put_contents($tmp_path, $img_data);
                    $timg = @getimagesize($tmp_path);
                    if (!$timg || !in_array($timg[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP))) {
                        @unlink($tmp_path);
                        continue;
                    }

                    // 확장자 결정
                    $ext_map = array(IMAGETYPE_GIF => 'gif', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp');
                    $ext = $ext_map[$timg[2]] ?? 'jpg';
                    $shuffle = md5(rand(1,999999999).time());
                    $bf_file = md5(sha1($_SERVER['REMOTE_ADDR'])).'_'.substr($shuffle,0,8).'_url.'.$ext;
                    $dest_file = G5_DATA_PATH.'/file/'.$bo_table.'/'.$bf_file;

                    if (rename($tmp_path, $dest_file)) {
                        chmod($dest_file, G5_FILE_PERMISSION);
                        $bf_no = $file_count + $url_count;
                        $bf_filesize = strlen($img_data);
                        $bf_width = $timg[0] ?? 0;
                        $bf_height = $timg[1] ?? 0;
                        $bf_type = $timg[2] ?? 0;

                        sql_query("INSERT INTO {$g5['board_file_table']}
                            SET bo_table = '{$bo_table}',
                                wr_id = '{$wr_id}',
                                bf_no = '{$bf_no}',
                                bf_source = 'url_image.{$ext}',
                                bf_file = '{$bf_file}',
                                bf_content = '',
                                bf_fileurl = '".sql_real_escape_string($img_url)."',
                                bf_thumburl = '',
                                bf_storage = '',
                                bf_download = 0,
                                bf_filesize = '{$bf_filesize}',
                                bf_width = '{$bf_width}',
                                bf_height = '{$bf_height}',
                                bf_type = '{$bf_type}',
                                bf_datetime = '".G5_TIME_YMDHIS."'");
                        $url_count++;
                    } else {
                        @unlink($tmp_path);
                    }
                }

                if ($url_count > 0) {
                    $total_files = $file_count + $url_count;
                    sql_query("UPDATE {$write_table} SET wr_file = '{$total_files}' WHERE wr_id = '{$wr_id}'");
                }
            }
        }

        // 포인트 부여
        if ($member['mb_id']) {
            insert_point($member['mb_id'], $board['bo_comment_point'], "{$board['bo_subject']} {$wr_id} 답글", $bo_table, $wr_id, '쓰기');
        }
        
        // 알림 생성 (답글 작성시)
        if ($member['mb_id'] && file_exists(G5_LIB_PATH.'/notification.lib.php')) {
            include_once(G5_LIB_PATH.'/notification.lib.php');

            // 부모글 작성자에게 알림
            if ($parent['mb_id'] && $parent['mb_id'] != $member['mb_id']) {
                create_notification(array(
                    'noti_type' => 'reply',
                    'mb_id' => $parent['mb_id'],
                    'from_mb_id' => $member['mb_id'],
                    'from_wr_name' => $wr_name,
                    'bo_table' => $bo_table,
                    'wr_id' => $wr_id,
                    'wr_parent' => $tm_parent,
                    'noti_content' => cut_str(strip_tags($wr_content), 200),
                    'noti_url' => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$wr_id
                ));
            }

            // 멘션 알림 (@이름, @{이름}, @이름 이름 감지 — 캐릭터 이름 우선, 회원명 fallback)
            $unique_mentions = function_exists('parse_mention_names') ? parse_mention_names($wr_content) : array();
            foreach ($unique_mentions as $mention_name) {
                $mentioned_member = null;
                if (function_exists('get_character_by_name')) {
                    $mentioned_ch = get_character_by_name($mention_name);
                    if ($mentioned_ch && !empty($mentioned_ch['mb_id'])) {
                        $mentioned_member = array('mb_id' => $mentioned_ch['mb_id']);
                    }
                }
                if (!$mentioned_member) {
                    $mentioned_member = sql_fetch("SELECT mb_id FROM {$g5['member_table']} WHERE mb_name = '".sql_real_escape_string($mention_name)."'");
                }
                if ($mentioned_member && $mentioned_member['mb_id'] != $member['mb_id']) {
                    create_notification(array(
                        'noti_type' => 'mention',
                        'mb_id' => $mentioned_member['mb_id'],
                        'from_mb_id' => $member['mb_id'],
                        'from_wr_name' => $wr_name,
                        'bo_table' => $bo_table,
                        'wr_id' => $wr_id,
                        'wr_parent' => $tm_origin,
                        'noti_content' => cut_str(strip_tags($wr_content), 200),
                        'noti_url' => G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$tm_origin.'#c_'.$wr_id
                    ));
                }
            }
        }
        
        // ========================================
        // 스크립트 키워드 처리 (타임라인 답글)
        // ========================================
        if (is_community_installed()) {
            // script.lib.php 명시적 로드 (field_config.php 없는 환경 대비)
            if (!function_exists('parse_field_script') && defined('G5_COMMUNITY_LIB_PATH')) {
                $script_lib = G5_COMMUNITY_LIB_PATH . '/script.lib.php';
                if (file_exists($script_lib)) {
                    include_once($script_lib);
                }
            }

            $script_table = G5_TABLE_PREFIX . 'community_script';

            // community_script 테이블 존재 여부 체크 (미설치 환경 방어)
            $_st_check = sql_query("SHOW TABLES LIKE '{$script_table}'", false);
            $script_table_exists = ($_st_check && sql_num_rows($_st_check) > 0);

            if ($script_table_exists) {
                // 답글 내용에서 [키워드] 패턴 감지
                preg_match_all('/\[([^\]]+)\]/', $wr_content, $script_keyword_matches);

                if (!empty($script_keyword_matches[1])) {
                    $processed_keywords = array();
                    $system_replies = array();

                    foreach ($script_keyword_matches[1] as $keyword) {
                        $keyword = trim($keyword);
                        if (empty($keyword) || in_array($keyword, $processed_keywords)) {
                            continue;
                        }
                        $processed_keywords[] = $keyword;

                        // 스크립트 조회 (대괄호 포함/미포함 둘 다 검색)
                        $keyword_with_brackets = '[' . $keyword . ']';
                        $script_sql = " SELECT * FROM {$script_table}
                                        WHERE (sk_keyword = '".sql_real_escape_string($keyword)."'
                                               OR sk_keyword = '".sql_real_escape_string($keyword_with_brackets)."')
                                        AND sk_use = 1
                                        LIMIT 1 ";
                        $script_row = sql_fetch($script_sql);

                        if ($script_row && $script_row['sk_id']) {
                            // 캐릭터명 가져오기
                            $character_name = '';
                            if ($mb_id) {
                                $char = get_character($mb_id);
                                if ($char && !empty($char['ch_name'])) {
                                    $character_name = $char['ch_name'];
                                }
                            }
                            if (empty($character_name)) {
                                $character_name = $wr_name;
                            }

                            // 스크립트 컨텍스트 초기화 (HP/아이템 관련 구문 정상 처리)
                            if ($mb_id && isset($char['ch_id']) && function_exists('init_script_context')) {
                                init_script_context($char['ch_id']);
                            }

                            // 스크립트 파싱
                            $script_text = $script_row['sk_script'];
                            if (function_exists('parse_field_script')) {
                                $parsed = parse_field_script($script_text, $character_name);
                            } else {
                                $parsed = $script_text;
                            }

                            // 파싱 결과 처리 (배열 또는 문자열)
                            if (is_array($parsed)) {
                                $parsed_text = $parsed['parsed_text'] ?? $script_text;
                            } else {
                                $parsed_text = $parsed;
                            }

                            // 시스템 답글 내용 생성
                            $system_reply_content = '<div class="script-result">';
                            $system_reply_content .= '<div class="script-keyword"><i class="fa fa-scroll"></i> [' . htmlspecialchars($keyword) . ']</div>';
                            $system_reply_content .= '<div class="script-text">' . nl2br(htmlspecialchars($parsed_text)) . '</div>';
                            $system_reply_content .= '</div>';

                            $system_replies[] = array(
                                'keyword' => $keyword,
                                'content' => $system_reply_content
                            );
                        }
                    }

                    // 시스템 답글 등록
                    if (!empty($system_replies)) {
                        foreach ($system_replies as $sys_reply) {
                            $sys_wr_num = $wr_num;

                            $sys_sql = " INSERT INTO {$write_table}
                                        SET wr_num = '{$sys_wr_num}',
                                            wr_reply = '',
                                            wr_comment = 0,
                                            wr_is_comment = 0,
                                            ca_name = '',
                                            wr_option = 'html1',
                                            wr_subject = '" . sql_real_escape_string($parent['wr_subject']) . "',
                                            wr_content = '" . sql_real_escape_string($sys_reply['content']) . "',
                                            wr_link1 = '',
                                            wr_link2 = '',
                                            wr_hit = 0,
                                            mb_id = '',
                                            wr_password = '',
                                            wr_name = '시스템',
                                            wr_email = '',
                                            wr_homepage = '',
                                            wr_datetime = '".G5_TIME_YMDHIS."',
                                            wr_last = '".G5_TIME_YMDHIS."',
                                            wr_ip = '',
                                            tm_origin = '{$tm_origin}',
                                            tm_parent = '{$wr_id}',
                                            wr_1 = '',
                                            wr_2 = '',
                                            wr_3 = '',
                                            wr_4 = '',
                                            wr_5 = '',
                                            wr_6 = '',
                                            wr_7 = '',
                                            wr_8 = '',
                                            wr_9 = '',
                                            wr_10 = '' ";
                            sql_query($sys_sql);
                            $sys_wr_id = sql_insert_id();

                            if ($sys_wr_id) {
                                sql_query(" UPDATE {$write_table} SET wr_parent = '{$sys_wr_id}' WHERE wr_id = '{$sys_wr_id}' ");
                                sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = bo_count_write + 1 WHERE bo_table = '{$bo_table}' ");
                            }
                        }
                    }
                }
            }
        }

        // ========================================
        // [대련] 키워드 처리 (답글에서 대련 공격)
        // ========================================
        if ($member['mb_id'] && preg_match('/\[대련\]/', $wr_content)) {
            $duels_table = G5_TABLE_PREFIX . 'community_duels';
            $_dt_check = sql_query("SHOW TABLES LIKE '{$duels_table}'", false);
            if ($_dt_check && sql_num_rows($_dt_check) > 0) {
                // 원글에 연결된 active 대련 조회
                $active_duel = sql_fetch("SELECT * FROM {$duels_table} WHERE du_board = '".sql_real_escape_string($bo_table)."' AND du_wr_id = '{$tm_origin}' AND du_status = 'active'");
                if ($active_duel && $active_duel['du_turn'] == $member['mb_id']) {
                    // duel.lib.php 로드
                    if (!function_exists('process_duel_action')) {
                        $duel_lib = G5_PATH . '/community/lib/duel.lib.php';
                        if (file_exists($duel_lib)) include_once($duel_lib);
                    }
                    if (function_exists('process_duel_action')) {
                        $duel_result = process_duel_action($active_duel['du_id'], $member['mb_id'], 'attack');

                        // 대련 결과를 시스템 답글로 생성
                        if ($duel_result['success']) {
                            $di = $duel_result['damage_info'] ?? null;
                            $attacker_ch = function_exists('get_character') ? get_character($member['mb_id']) : null;
                            $attacker_name = $attacker_ch ? $attacker_ch['ch_name'] : $member['mb_id'];

                            $defender_id = ($member['mb_id'] == $active_duel['du_challenger']) ? $active_duel['du_opponent'] : $active_duel['du_challenger'];
                            $defender_ch = function_exists('get_character') ? get_character($defender_id) : null;
                            $defender_name = $defender_ch ? $defender_ch['ch_name'] : $defender_id;

                            $duel_html = '<div class="duel-result">';
                            $duel_html .= '<div class="duel-result-header"><span class="duel-icon">⚔️</span> 대련</div>';
                            if ($di) {
                                $crit_text = !empty($di['is_critical']) ? ' <span class="duel-critical">★ 크리티컬!</span>' : '';
                                $duel_html .= '<div class="duel-result-detail">';
                                $duel_html .= '<strong>' . htmlspecialchars($attacker_name) . '</strong>의 공격! ';
                                $duel_html .= '🎲 ' . $di['atk_base'] . '+' . $di['atk_roll'] . ' vs ' . $di['def_base'] . '+' . $di['def_roll'];
                                $duel_html .= ' → <strong>' . $di['damage'] . ' 데미지</strong>' . $crit_text;
                                $duel_html .= '</div>';
                                $duel_html .= '<div class="duel-result-hp">';
                                $duel_html .= htmlspecialchars($defender_name) . ' HP: ' . ($duel_result['new_hp'] ?? 0);
                                $duel_html .= '</div>';
                            }

                            // 대련 종료 시
                            if (isset($duel_result['action']) && $duel_result['action'] == 'end') {
                                $winner_ch = function_exists('get_character') ? get_character($duel_result['winner']) : null;
                                $winner_name_text = $winner_ch ? $winner_ch['ch_name'] : $duel_result['winner'];
                                $duel_html .= '<div class="duel-result-end">🏆 승자: <strong>' . htmlspecialchars($winner_name_text) . '</strong></div>';
                                if (!empty($duel_result['reward'])) {
                                    $rw = $duel_result['reward'];
                                    if ($rw['winner_exp'] > 0) $duel_html .= '<div class="duel-result-reward">승리 보상: EXP +' . $rw['winner_exp'] . '</div>';
                                    if ($rw['loser_exp'] > 0) $duel_html .= '<div class="duel-result-reward">참가 보상: EXP +' . $rw['loser_exp'] . '</div>';
                                }
                            }
                            $duel_html .= '</div>';

                            // 시스템 답글 삽입
                            $duel_sys_sql = " INSERT INTO {$write_table}
                                SET wr_num = '{$wr_num}',
                                    wr_reply = '', wr_comment = 0, wr_is_comment = 0,
                                    ca_name = '', wr_option = 'html1',
                                    wr_subject = '" . sql_real_escape_string($parent['wr_subject']) . "',
                                    wr_content = '" . sql_real_escape_string($duel_html) . "',
                                    wr_link1 = '', wr_link2 = '', wr_hit = 0,
                                    mb_id = '', wr_password = '',
                                    wr_name = '대련',
                                    wr_email = '', wr_homepage = '',
                                    wr_datetime = '".G5_TIME_YMDHIS."',
                                    wr_last = '".G5_TIME_YMDHIS."',
                                    wr_ip = '',
                                    tm_origin = '{$tm_origin}',
                                    tm_parent = '{$wr_id}',
                                    wr_1 = '', wr_2 = '', wr_3 = '', wr_4 = '', wr_5 = '',
                                    wr_6 = '', wr_7 = '', wr_8 = '', wr_9 = '', wr_10 = '' ";
                            sql_query($duel_sys_sql);
                            $duel_sys_id = sql_insert_id();
                            if ($duel_sys_id) {
                                sql_query(" UPDATE {$write_table} SET wr_parent = '{$duel_sys_id}' WHERE wr_id = '{$duel_sys_id}' ");
                                sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = bo_count_write + 1 WHERE bo_table = '{$bo_table}' ");
                            }

                            // 상대방에게 턴 알림
                            if (isset($duel_result['next_turn']) && function_exists('create_notification')) {
                                include_once(G5_LIB_PATH.'/notification.lib.php');
                                create_notification(array(
                                    'noti_type' => 'duel',
                                    'mb_id' => $duel_result['next_turn'],
                                    'from_mb_id' => $member['mb_id'],
                                    'from_wr_name' => $attacker_name,
                                    'bo_table' => $bo_table,
                                    'wr_id' => $tm_origin,
                                    'wr_parent' => $tm_origin,
                                    'noti_content' => $attacker_name . '님이 공격했습니다. 당신의 턴입니다!',
                                    'noti_url' => G5_BBS_URL . '/board.php?bo_table=' . $bo_table . '&wr_id=' . $tm_origin
                                ));
                            }
                        }
                    }
                }
            }
        }

        // ========================================
        // [항복] 키워드 처리 (답글에서 대련 항복)
        // ========================================
        if ($member['mb_id'] && preg_match('/\[항복\]/', $wr_content)) {
            $duels_table = G5_TABLE_PREFIX . 'community_duels';
            $_dt_check2 = sql_query("SHOW TABLES LIKE '{$duels_table}'", false);
            if ($_dt_check2 && sql_num_rows($_dt_check2) > 0) {
                $active_duel2 = sql_fetch("SELECT * FROM {$duels_table} WHERE du_board = '".sql_real_escape_string($bo_table)."' AND du_wr_id = '{$tm_origin}' AND du_status = 'active'");
                if ($active_duel2 && $active_duel2['du_turn'] == $member['mb_id']) {
                    if (!function_exists('process_duel_action')) {
                        $duel_lib = G5_PATH . '/community/lib/duel.lib.php';
                        if (file_exists($duel_lib)) include_once($duel_lib);
                    }
                    if (function_exists('process_duel_action')) {
                        $surr_result = process_duel_action($active_duel2['du_id'], $member['mb_id'], 'surrender');

                        if ($surr_result['success']) {
                            $surr_ch = function_exists('get_character') ? get_character($member['mb_id']) : null;
                            $surr_name = $surr_ch ? $surr_ch['ch_name'] : $member['mb_id'];

                            $winner_ch2 = function_exists('get_character') ? get_character($surr_result['winner']) : null;
                            $winner_name2 = $winner_ch2 ? $winner_ch2['ch_name'] : $surr_result['winner'];

                            $surr_html = '<div class="duel-result">';
                            $surr_html .= '<div class="duel-result-header"><span class="duel-icon">🏳️</span> 항복</div>';
                            $surr_html .= '<div class="duel-result-detail"><strong>' . htmlspecialchars($surr_name) . '</strong>이(가) 항복했습니다.</div>';
                            $surr_html .= '<div class="duel-result-end">🏆 승자: <strong>' . htmlspecialchars($winner_name2) . '</strong></div>';
                            if (!empty($surr_result['reward'])) {
                                $rw2 = $surr_result['reward'];
                                if ($rw2['winner_exp'] > 0) $surr_html .= '<div class="duel-result-reward">승리 보상: EXP +' . $rw2['winner_exp'] . '</div>';
                                if ($rw2['loser_exp'] > 0) $surr_html .= '<div class="duel-result-reward">참가 보상: EXP +' . $rw2['loser_exp'] . '</div>';
                            }
                            $surr_html .= '</div>';

                            $surr_sys_sql = " INSERT INTO {$write_table}
                                SET wr_num = '{$wr_num}',
                                    wr_reply = '', wr_comment = 0, wr_is_comment = 0,
                                    ca_name = '', wr_option = 'html1',
                                    wr_subject = '" . sql_real_escape_string($parent['wr_subject']) . "',
                                    wr_content = '" . sql_real_escape_string($surr_html) . "',
                                    wr_link1 = '', wr_link2 = '', wr_hit = 0,
                                    mb_id = '', wr_password = '',
                                    wr_name = '항복',
                                    wr_email = '', wr_homepage = '',
                                    wr_datetime = '".G5_TIME_YMDHIS."',
                                    wr_last = '".G5_TIME_YMDHIS."',
                                    wr_ip = '',
                                    tm_origin = '{$tm_origin}',
                                    tm_parent = '{$wr_id}',
                                    wr_1 = '', wr_2 = '', wr_3 = '', wr_4 = '', wr_5 = '',
                                    wr_6 = '', wr_7 = '', wr_8 = '', wr_9 = '', wr_10 = '' ";
                            sql_query($surr_sys_sql);
                            $surr_sys_id = sql_insert_id();
                            if ($surr_sys_id) {
                                sql_query(" UPDATE {$write_table} SET wr_parent = '{$surr_sys_id}' WHERE wr_id = '{$surr_sys_id}' ");
                                sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = bo_count_write + 1 WHERE bo_table = '{$bo_table}' ");
                            }
                        }
                    }
                }
            }
        }

        // ========================================
        // [대전] 키워드 처리 (답글에서 아레나 공격)
        // ========================================
        if ($member['mb_id'] && preg_match('/\[대전\]/', $wr_content)) {
            $arena_table = G5_TABLE_PREFIX . 'community_arena';
            $_ar_check = sql_query("SHOW TABLES LIKE '{$arena_table}'", false);
            if ($_ar_check && sql_num_rows($_ar_check) > 0) {
                $active_arena = sql_fetch("SELECT * FROM {$arena_table} WHERE ar_board = '".sql_real_escape_string($bo_table)."' AND ar_wr_id = '{$tm_origin}' AND ar_status = 'active'");
                if ($active_arena && $active_arena['ar_current_turn_mb'] == $member['mb_id']) {
                    if (!function_exists('process_arena_attack')) {
                        $arena_lib = G5_PATH . '/community/lib/arena.lib.php';
                        if (file_exists($arena_lib)) include_once($arena_lib);
                    }
                    if (function_exists('process_arena_attack')) {
                        $arena_result = process_arena_attack($active_arena['ar_id'], $member['mb_id']);

                        if ($arena_result['success']) {
                            $ar_attacker = $arena_result['attacker'];
                            $ar_targets = $arena_result['targets'] ?? array();
                            $attacker_name = $ar_attacker['am_ch_name'];

                            $arena_html = '<div class="duel-result arena-result">';
                            $arena_html .= '<div class="duel-result-header"><span class="duel-icon">⚔️</span> 아레나</div>';
                            $arena_html .= '<div class="duel-result-detail"><strong>' . htmlspecialchars($attacker_name) . '</strong>의 공격! 🎲</div>';

                            foreach ($ar_targets as $tgt) {
                                $crit_text = !empty($tgt['is_critical']) ? ' <span class="duel-critical">★ 크리티컬!</span>' : '';
                                $ko_text = !empty($tgt['ko']) ? ' <span class="arena-ko-text">☠️ KO!</span>' : '';
                                $dmg_text = ($tgt['damage'] > 0)
                                    ? $tgt['atk_final'] . '(' . $ar_attacker['am_attack'] . '+' . $tgt['atk_roll'] . ') vs ' . $tgt['def_final'] . '(' . $tgt['def_base'] . '+' . $tgt['def_roll'] . ') = <strong>' . $tgt['damage'] . ' 데미지</strong>' . $crit_text
                                    : $tgt['atk_final'] . '(' . $ar_attacker['am_attack'] . '+' . $tgt['atk_roll'] . ') vs ' . $tgt['def_final'] . '(' . $tgt['def_base'] . '+' . $tgt['def_roll'] . ') = <em>무시</em>';

                                $arena_html .= '<div class="arena-target-result">';
                                $arena_html .= '→ <strong>' . htmlspecialchars($tgt['ch_name']) . '</strong>: ' . $dmg_text;
                                $arena_html .= '<br>&nbsp;&nbsp;&nbsp;' . htmlspecialchars($tgt['ch_name']) . ' HP: ' . $tgt['hp_after'] . $ko_text;
                                $arena_html .= '</div>';
                            }

                            // 종료 시
                            if (isset($arena_result['action']) && $arena_result['action'] == 'end') {
                                $win_team_label = ($arena_result['winner_team'] === 'a') ? 'A' : 'B';
                                $arena_html .= '<div class="duel-result-end">🏆 ' . $win_team_label . '팀 승리!</div>';
                                if (!empty($arena_result['reward'])) {
                                    $rw = $arena_result['reward'];
                                    if (!empty($rw['winners'])) {
                                        foreach ($rw['winners'] as $wr) {
                                            $arena_html .= '<div class="duel-result-reward">승리 보상: ' . htmlspecialchars($wr['ch_name']) . ' EXP +' . $wr['exp'] . '</div>';
                                        }
                                    }
                                    if (!empty($rw['losers'])) {
                                        foreach ($rw['losers'] as $lr) {
                                            $arena_html .= '<div class="duel-result-reward">참가 보상: ' . htmlspecialchars($lr['ch_name']) . ' EXP +' . $lr['exp'] . '</div>';
                                        }
                                    }
                                }
                            } else {
                                // 다음 턴 안내
                                $next_mb = $arena_result['next_turn'] ?? '';
                                if ($next_mb) {
                                    $next_member_table = G5_TABLE_PREFIX . 'community_arena_members';
                                    $safe_next = sql_real_escape_string($next_mb);
                                    $next_am = sql_fetch("SELECT am_ch_name FROM {$next_member_table} WHERE ar_id = {$active_arena['ar_id']} AND am_mb_id = '{$safe_next}'");
                                    $next_name = $next_am ? $next_am['am_ch_name'] : $next_mb;
                                    $arena_html .= '<div class="arena-next-turn">다음 턴: <strong>' . htmlspecialchars($next_name) . '</strong> (라운드 ' . ($arena_result['round'] ?? 1) . ')</div>';
                                }
                            }
                            $arena_html .= '</div>';

                            // 시스템 답글 삽입
                            $arena_sys_sql = " INSERT INTO {$write_table}
                                SET wr_num = '{$wr_num}',
                                    wr_reply = '', wr_comment = 0, wr_is_comment = 0,
                                    ca_name = '', wr_option = 'html1',
                                    wr_subject = '" . sql_real_escape_string($parent['wr_subject']) . "',
                                    wr_content = '" . sql_real_escape_string($arena_html) . "',
                                    wr_link1 = '', wr_link2 = '', wr_hit = 0,
                                    mb_id = '', wr_password = '',
                                    wr_name = '아레나',
                                    wr_email = '', wr_homepage = '',
                                    wr_datetime = '".G5_TIME_YMDHIS."',
                                    wr_last = '".G5_TIME_YMDHIS."',
                                    wr_ip = '',
                                    tm_origin = '{$tm_origin}',
                                    tm_parent = '{$wr_id}',
                                    wr_1 = '', wr_2 = '', wr_3 = '', wr_4 = '', wr_5 = '',
                                    wr_6 = '', wr_7 = '', wr_8 = '', wr_9 = '', wr_10 = '' ";
                            sql_query($arena_sys_sql);
                            $arena_sys_id = sql_insert_id();
                            if ($arena_sys_id) {
                                sql_query(" UPDATE {$write_table} SET wr_parent = '{$arena_sys_id}' WHERE wr_id = '{$arena_sys_id}' ");
                                sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = bo_count_write + 1 WHERE bo_table = '{$bo_table}' ");
                            }
                        }
                    }
                }
            }
        }

        // 답글 정보 반환
        $new_reply = sql_fetch(" SELECT * FROM {$write_table} WHERE wr_id = '{$wr_id}' ");

        if (!$new_reply) {
            $response['error'] = '답글이 등록되었으나 조회에 실패했습니다. 페이지를 새로고침해주세요.';
            break;
        }

        $response['success'] = true;
        $response['reply'] = array(
            'wr_id' => isset($new_reply['wr_id']) ? $new_reply['wr_id'] : '',
            'wr_name' => isset($new_reply['wr_name']) ? $new_reply['wr_name'] : '',
            'wr_content' => isset($new_reply['wr_content']) ? (function_exists('render_mentions') ? render_mentions(conv_content($new_reply['wr_content'], 0)) : conv_content($new_reply['wr_content'], 0)) : '',
            'wr_datetime' => isset($new_reply['wr_datetime']) ? $new_reply['wr_datetime'] : '',
            'mb_id' => isset($new_reply['mb_id']) ? $new_reply['mb_id'] : ''
        );

        break;
        
    case 'get_replies':
        // 답글 목록 가져오기
        $tm_origin = isset($_GET['tm_origin']) ? (int)$_GET['tm_origin'] : 0;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        if (!$tm_origin) {
            $response['error'] = '원글이 지정되지 않았습니다.';
            break;
        }
        
        // 답글 목록
        $sql = " SELECT * FROM {$write_table} 
                 WHERE tm_origin = '{$tm_origin}' 
                 ORDER BY wr_id ASC 
                 LIMIT {$offset}, {$limit} ";
        
        $result = sql_query($sql);
        $replies = array();
        
        while ($row = sql_fetch_array($result)) {
            $replies[] = array(
                'wr_id' => $row['wr_id'],
                'tm_parent' => $row['tm_parent'],
                'wr_name' => $row['wr_name'],
                'mb_id' => $row['mb_id'],
                'wr_content' => function_exists('render_mentions') ? render_mentions(conv_content($row['wr_content'], 0)) : conv_content($row['wr_content'], 0),
                'wr_datetime' => $row['wr_datetime'],
                'is_origin' => ($row['tm_origin'] == $row['wr_id'])
            );
        }
        
        // 전체 답글 수
        $total_sql = " SELECT COUNT(*) as cnt FROM {$write_table} WHERE tm_origin = '{$tm_origin}' ";
        $total_row = sql_fetch($total_sql);
        
        $response['success'] = true;
        $response['replies'] = $replies;
        $response['total'] = $total_row['cnt'];
        $response['page'] = $page;
        $response['total_pages'] = ceil($total_row['cnt'] / $limit);
        
        break;
        
    case 'delete_reply':
        global $config;

        // 답글 삭제
        $wr_id = isset($_POST['wr_id']) ? (int)$_POST['wr_id'] : 0;
        
        if (!$wr_id) {
            $response['error'] = '삭제할 글이 지정되지 않았습니다.';
            break;
        }
        
        $write = sql_fetch(" SELECT * FROM {$write_table} WHERE wr_id = '{$wr_id}' ");
        
        if (!$write['wr_id']) {
            $response['error'] = '글이 존재하지 않습니다.';
            break;
        }
        
        // 삭제 권한 확인
        if ($is_admin || ($member['mb_id'] && $member['mb_id'] == $write['mb_id'])) {
            // 답글에 대한 답글이 있는지 확인
            $child_count = sql_fetch(" SELECT COUNT(*) as cnt FROM {$write_table} WHERE tm_parent = '{$wr_id}' AND wr_id != '{$wr_id}' ");

            if ($child_count['cnt'] > 0) {
                $response['error'] = '이 글에 답글이 있어 삭제할 수 없습니다.';
                break;
            }

            // 첨부 파일 삭제
            $sql2 = " SELECT * FROM {$g5['board_file_table']} WHERE bo_table = '{$bo_table}' AND wr_id = '{$wr_id}' ";
            $result2 = sql_query($sql2);
            while ($row2 = sql_fetch_array($result2)) {
                $delete_file = G5_DATA_PATH.'/file/'.$bo_table.'/'.str_replace('../', '', $row2['bf_file']);
                if (file_exists($delete_file)) {
                    @unlink($delete_file);
                }

                // 썸네일 삭제
                if (preg_match("/\.({$config['cf_image_extension']})$/i", $row2['bf_file'])) {
                    if (function_exists('delete_board_thumbnail')) {
                        delete_board_thumbnail($bo_table, $row2['bf_file']);
                    }
                }
            }

            // 에디터 썸네일 삭제
            if (function_exists('delete_editor_thumbnail')) {
                delete_editor_thumbnail($write['wr_content']);
            }

            // 파일 테이블 레코드 삭제
            sql_query(" DELETE FROM {$g5['board_file_table']} WHERE bo_table = '{$bo_table}' AND wr_id = '{$wr_id}' ");

            // 포인트 삭제
            if ($write['mb_id']) {
                if (function_exists('delete_point')) {
                    if (!delete_point($write['mb_id'], $bo_table, $wr_id, '쓰기')) {
                        insert_point($write['mb_id'], $board['bo_comment_point'] * (-1), "{$board['bo_subject']} {$wr_id} 답글삭제", $bo_table, $wr_id, '삭제');
                    }
                }
            }

            // 게시글 삭제
            sql_query(" DELETE FROM {$write_table} WHERE wr_id = '{$wr_id}' ");
            sql_query(" DELETE FROM {$g5['board_new_table']} WHERE bo_table = '{$bo_table}' AND wr_id = '{$wr_id}' ");

            // 게시글 수 감소
            sql_query(" UPDATE {$g5['board_table']} SET bo_count_write = bo_count_write - 1 WHERE bo_table = '{$bo_table}' ");

            $response['success'] = true;
            $response['message'] = '삭제되었습니다.';
        } else {
            $response['error'] = '삭제 권한이 없습니다.';
        }
        
        break;
        
    default:
        $response['error'] = '올바른 요청이 아닙니다.';
}

echo json_encode($response);
