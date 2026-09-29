<?php
header('Content-Type: application/json; charset=utf-8');

include_once('./_common.php');

// POST 변수 초기화
$wr_id    = isset($_POST['wr_id']) ? intval($_POST['wr_id']) : 0;
$fileName = isset($_POST['fileName']) ? urldecode(trim($_POST['fileName'])) : '';
$bo_table = isset($_POST['bo_table']) ? trim($_POST['bo_table']) : '';

// 필수 파라미터 체크
if (empty($wr_id) || empty($fileName) || empty($bo_table)) {
    echo json_encode(['result' => 'error', 'message' => '필수 파라미터가 누락되었습니다.']);
    exit;
}

// trpg_up 테이블 이름 설정
$trpg_up_table = G5_TABLE_PREFIX . 'trpg_up';

// $fileName이 전체 URL이라면 basename()으로 고유 파일명만 취함
$baseFileName = basename($fileName);
$file_path = G5_DATA_PATH . '/file/' . $bo_table . '/' . $baseFileName;
$debugFile = __DIR__ . '/debug.log';

// 1. 실제 파일 삭제
if (file_exists($file_path)) {
    if (unlink($file_path)) {
        file_put_contents($debugFile, "FILE DELETED: $file_path\n", FILE_APPEND);
    } else {
        $error = error_get_last();
        file_put_contents($debugFile, "UNLINK ERROR: " . print_r($error, true) . "\n", FILE_APPEND);
        echo json_encode(['result' => 'error', 'message' => '파일 삭제에 실패했습니다.']);
        exit;
    }
} else {
    file_put_contents($debugFile, "WARNING: File not found - $file_path\n", FILE_APPEND);
}

// 2. trpg_up 테이블에서 해당 레코드 찾기 및 삭제
$sql = "DELETE FROM {$trpg_up_table} 
        WHERE bo_table = '{$bo_table}' 
          AND wr_id = '{$wr_id}' 
          AND image_url LIKE '%{$baseFileName}'";
sql_query($sql);
file_put_contents($debugFile, "Deleted DB record with image_url like %{$baseFileName}\n", FILE_APPEND);

// 3. 남은 이미지들의 순서 재정렬 (각 이미지 타입별로)
$image_types = ['card', 'cha', 'normal'];
foreach ($image_types as $type) {
    $order = 0;
    $result = sql_query("SELECT id FROM {$trpg_up_table} 
                        WHERE bo_table = '{$bo_table}' 
                        AND wr_id = '{$wr_id}' 
                        AND image_type = '{$type}' 
                        AND img_use = '1'
                        ORDER BY file_order ASC");
    
    while ($row = sql_fetch_array($result)) {
        sql_query("UPDATE {$trpg_up_table} SET file_order = '{$order}' WHERE id = '{$row['id']}'");
        $order++;
    }
    
    file_put_contents($debugFile, "Reindexed file_order for image_type {$type}\n", FILE_APPEND);
}

// 4. 최종적으로 남은 파일 목록을 반환
$final_files = array();
$result = sql_query("SELECT image_type, image_url FROM {$trpg_up_table} 
                    WHERE bo_table = '{$bo_table}' 
                    AND wr_id = '{$wr_id}' 
                    AND img_use = '1'
                    ORDER BY image_type, file_order ASC");

while ($row = sql_fetch_array($result)) {
    if (!isset($final_files[$row['image_type']])) {
        $final_files[$row['image_type']] = [];
    }
    $final_files[$row['image_type']][] = $row['image_url'];
}

echo json_encode([
    'result' => 'success', 
    'message' => '파일 삭제 및 DB 업데이트 완료', 
    'files' => $final_files
]);
exit;
?>
