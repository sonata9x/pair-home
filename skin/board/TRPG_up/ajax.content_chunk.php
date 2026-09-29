<?php
ob_start(); // 출력 버퍼링 시작
include_once('./_common.php');
header('Content-Type: application/json');

// 필수 파라미터 확인
if (empty($_POST['bo_table'])) {
    ob_end_clean(); // 버퍼 비우기
    echo json_encode(['result' => 'error', 'message' => '필수 파라미터가 누락되었습니다.']);
    exit;
}

$bo_table = $_POST['bo_table'];
$wr_id = isset($_POST['wr_id']) ? (int)$_POST['wr_id'] : 0;

// 임시 저장 디렉토리 생성
$temp_dir = G5_DATA_PATH.'/temp';
if (!is_dir($temp_dir)) {
    @mkdir($temp_dir, 0755);
}

// 세션 ID를 사용하여 고유한 임시 파일 이름 생성
$session_id = session_id();

// 액션 확인: 청크 업로드 또는 처리 완료
$action = isset($_POST['action']) ? $_POST['action'] : 'upload';

// 응답 변수 초기화
$response = [];

if ($action === 'upload') {
    // 청크 업로드 처리
    if (!isset($_POST['chunk_index']) || !isset($_POST['total_chunks']) || !isset($_POST['chunk_data'])) {
        $response = ['result' => 'error', 'message' => '청크 업로드에 필요한 파라미터가 누락되었습니다.'];
    } else {
        $chunk_index = (int)$_POST['chunk_index'];
        $total_chunks = (int)$_POST['total_chunks'];
        $chunk_data = stripslashes($_POST['chunk_data']); // addslashes 제거
        
        // wr_id 검증 - 수정 모드에서는 반드시 필요
        if ($wr_id <= 0) {
            $response = ['result' => 'error', 'message' => '유효한 게시글 ID가 필요합니다.'];
        } else {
            // 청크 데이터 저장
            $temp_file = $temp_dir.'/content_'.$session_id.'_'.$bo_table.'_'.$wr_id.'_chunk_'.$chunk_index.'.tmp';
            $bytes_written = file_put_contents($temp_file, $chunk_data);
            
            if ($bytes_written === false) {
                $response = ['result' => 'error', 'message' => '청크 데이터 저장에 실패했습니다.'];
            } else {
                $response = ['result' => 'success', 'chunk_index' => $chunk_index, 'bytes' => $bytes_written, 'wr_id' => $wr_id];
            }
        }
    }
} elseif ($action === 'process') {
    // 모든 청크 처리
    if (!isset($_POST['total_chunks'])) {
        $response = ['result' => 'error', 'message' => '처리에 필요한 파라미터가 누락되었습니다.'];
    } else if ($wr_id <= 0) {
        $response = ['result' => 'error', 'message' => '처리를 위한 유효한 게시글 ID가 필요합니다.'];
    } else {
        $total_chunks = (int)$_POST['total_chunks'];
        
        // 게시글 존재 여부 확인
        $write_table = $g5['write_prefix'] . $bo_table;
        $sql = "SELECT COUNT(*) AS cnt FROM {$write_table} WHERE wr_id = '{$wr_id}'";
        $row = sql_fetch($sql);
        
        if ($row['cnt'] == 0) {
            $response = ['result' => 'error', 'message' => '해당 게시글이 존재하지 않습니다.'];
        } else {
            // 모든 청크 파일 읽어서 합치기
            $full_content = '';
            $missing_chunks = [];
            
            for ($i = 0; $i < $total_chunks; $i++) {
                $temp_file = $temp_dir.'/content_'.$session_id.'_'.$bo_table.'_'.$wr_id.'_chunk_'.$i.'.tmp';
                
                if (file_exists($temp_file)) {
                    $chunk_data = file_get_contents($temp_file);
                    if ($chunk_data !== false) {
                        $full_content .= $chunk_data;
                        
                        // 읽은 후 임시 파일 삭제
                        @unlink($temp_file);
                    } else {
                        $missing_chunks[] = $i;
                    }
                } else {
                    $missing_chunks[] = $i;
                }
            }
            
            if (!empty($missing_chunks)) {
                $response = ['result' => 'error', 'message' => '일부 청크 파일이 누락되었습니다. 다시 시도해주세요.', 'missing_chunks' => $missing_chunks];
            } else {
                // 내용이 있는 경우에만 처리
                if ($full_content) {
                    $escaped_content = sql_real_escape_string($full_content);
                    $query = "UPDATE " . $write_table . " SET wr_content = '" . $escaped_content . "' WHERE wr_id = '{$wr_id}'";
                    $update_success = sql_query($query, false);
                    
                    if ($update_success) {
                        $response = ['result' => 'success', 'message' => '내용이 성공적으로 저장되었습니다.', 'wr_id' => $wr_id];
                    } else {
                        $response = ['result' => 'error', 'message' => '내용 업데이트에 실패했습니다.'];
                    }
                } else {
                    $response = ['result' => 'error', 'message' => '저장할 내용이 없습니다.'];
                }
            }
        }
    }
} else {
    $response = ['result' => 'error', 'message' => '알 수 없는 액션입니다.'];
}

// 버퍼 비우기 후 JSON 응답 출력
ob_end_clean();
echo json_encode($response);
?>
