<?php
ob_start();
include_once('./_common.php');
header('Content-Type: application/json; charset=utf-8');

$bo_table = isset($_POST['bo_table']) ? trim((string) $_POST['bo_table']) : '';
$request_token = isset($_POST['request_token']) ? trim((string) $_POST['request_token']) : '';
$response = array('success' => false, 'message' => '새 게시글을 확인할 수 없습니다.');

if (!preg_match('/^[a-zA-Z0-9_]+$/', $bo_table)) {
    $response['message'] = '게시판 정보가 올바르지 않습니다.';
} elseif (!preg_match('/^trpg-[a-zA-Z0-9-]{10,80}$/', $request_token)) {
    $response['message'] = '게시글 요청 토큰이 올바르지 않습니다.';
} else {
    $write_table = $g5['write_prefix'] . $bo_table;
    $escaped_token = sql_real_escape_string($request_token);
    $escaped_member_id = sql_real_escape_string(isset($member['mb_id']) ? $member['mb_id'] : '');

    $sql = "SELECT wr_id
            FROM {$write_table}
            WHERE wr_9 = '{$escaped_token}'
              AND wr_is_comment = 0
              AND mb_id = '{$escaped_member_id}'
              AND wr_datetime >= DATE_SUB('" . G5_TIME_YMDHIS . "', INTERVAL 10 MINUTE)
            ORDER BY wr_id DESC
            LIMIT 1";
    $row = sql_fetch($sql);

    if ($row && !empty($row['wr_id'])) {
        $resolved_wr_id = (int) $row['wr_id'];
        sql_query("UPDATE {$write_table}
                   SET wr_9 = ''
                   WHERE wr_id = '{$resolved_wr_id}'
                     AND wr_9 = '{$escaped_token}'", false);

        $response = array(
            'success' => true,
            'wr_id' => $resolved_wr_id
        );
    }
}

ob_end_clean();
echo json_encode($response);
exit;
