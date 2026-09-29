<?php
include_once('./_common.php');

// AJAX 요청 감지
$is_ajax = (isset($_POST['ajax']) && $_POST['ajax'] == '1') ||
           (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

if ($w == 's') {
    $qstr = 'bo_table='.$bo_table.'&amp;sfl='.$sfl.'&amp;stx='.$stx.'&amp;sop='.$sop.'&amp;wr_id='.$wr_id.'&amp;page='.$page;

    $wr = get_write($write_table, $wr_id);

    // 글이 존재하지 않는 경우
    if (!$wr || !$wr['wr_id']) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '글(또는 댓글)이 존재하지 않습니다.']);
            exit;
        } else {
            alert('글이 존재하지 않습니다.');
        }
    }

    // wr_secret 우선 확인 (비밀글 조회 비밀번호)
    $password_valid = false;
    if (!empty($wr['wr_secret'])) {
        // wr_secret이 있으면 평문 비교
        if ($wr_password === $wr['wr_secret']) {
            $password_valid = true;
        }
    } else {
        // wr_secret이 없으면 기존 방식 (wr_password 암호화 비교)
        if( !$wr['wr_password'] && $wr['mb_id'] ){
            if ( $mb = get_member($wr['mb_id']) ){
                $wr['wr_password'] = $mb['mb_password'];
            }
        }
        if (check_password($wr_password, $wr['wr_password'])) {
            $password_valid = true;
        }
    }

    if (!$password_valid) {
        run_event('password_is_wrong', 'bbs', $wr, $qstr);
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '비밀번호가 틀립니다.']);
            exit;
        } else {
            alert('비밀번호가 틀립니다.');
        }
    }

    // 세션에 아래 정보를 저장. 하위번호는 비밀번호없이 보아야 하기 때문임.
    $ss_name = 'ss_secret_'.$bo_table.'_'.$wr['wr_num'];
    set_session($ss_name, TRUE);

    // AJAX 요청이면 JSON 응답
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'wr_id' => $wr_id,
            'message' => '비밀번호 검증 성공',
            'post_html' => $wr['wr_content']
        ]);
        exit;
    }

} else if ($w == 'sc') {
    $qstr = 'bo_table='.$bo_table.'&amp;sfl='.$sfl.'&amp;stx='.$stx.'&amp;sop='.$sop.'&amp;wr_id='.$wr_id.'&amp;page='.$page;

    $wr = get_write($write_table, $wr_id);

    // 댓글이 존재하지 않는 경우
    if (!$wr || !$wr['wr_id']) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '댓글이 존재하지 않습니다.']);
            exit;
        } else {
            alert('댓글이 존재하지 않습니다.');
        }
    }

    // wr_secret 우선 확인 (비밀 댓글 조회 비밀번호)
    $password_valid = false;
    if (!empty($wr['wr_secret'])) {
        // wr_secret이 있으면 평문 비교
        if ($wr_password === $wr['wr_secret']) {
            $password_valid = true;
        }
    } else {
        // wr_secret이 없으면 기존 방식 (wr_password 암호화 비교)
        if( !$wr['wr_password'] && $wr['mb_id'] ){
            if ( $mb = get_member($wr['mb_id']) ){
                $wr['wr_password'] = $mb['mb_password'];
            }
        }
        if (check_password($wr_password, $wr['wr_password'])) {
            $password_valid = true;
        }
    }

    if (!$password_valid){
        run_event('password_is_wrong', 'bbs', $wr, $qstr);
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '비밀번호가 틀립니다.']);
            exit;
        } else {
            alert('비밀번호가 틀립니다.');
        }
    }

    // 세션에 아래 정보를 저장. 하위번호는 비밀번호없이 보아야 하기 때문임.
    $ss_name = 'ss_secret_comment_'.$bo_table.'_'.$wr['wr_id'];
    set_session($ss_name, TRUE);

    // AJAX 요청이면 JSON 응답
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'wr_id' => $wr_id,
            'message' => '비밀번호 검증 성공',
            'post_html' => $wr['wr_content']
        ]);
        exit;
    }

} else
    alert('w 값이 제대로 넘어오지 않았습니다.');

goto_url(short_url_clean(G5_HTTP_BBS_URL.'/board.php?'.$qstr));