<?php
/**
 * RA0 Edition - 이모티콘 관리
 */
$sub_menu = "100500";
include_once('./_common.php');

if (!$is_admin) {
    alert('관리자만 접근 가능합니다.');
}

// 이모티콘 라이브러리 로드
include_once(G5_LIB_PATH . '/emoticon.lib.php');

$g5['title'] = '이모티콘 관리';

// 처리 모드
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
$emo_id = isset($_REQUEST['emo_id']) ? (int)$_REQUEST['emo_id'] : 0;

// =====================================================
// POST 처리
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emo_name = isset($_POST['emo_name']) ? trim($_POST['emo_name']) : '';
    $emo_category = isset($_POST['emo_category']) ? trim($_POST['emo_category']) : '';

    // 이름에서 슬래시 제거
    $emo_name = ltrim($emo_name, '/');

    // 이름 검증
    if (!validate_emoticon_name($emo_name)) {
        alert('이모티콘 이름은 한글, 영문, 숫자, 밑줄만 사용할 수 있습니다. (1~50자)');
    }

    // 삭제
    if ($mode === 'delete' && $emo_id > 0) {
        if (delete_emoticon($emo_id)) {
            alert('이모티콘이 삭제되었습니다.', G5_ADMIN_URL . '/config_emoticon.php');
        } else {
            alert('이모티콘 삭제에 실패했습니다.');
        }
    }

    // 수정
    if ($mode === 'update' && $emo_id > 0) {
        // 중복 체크
        if (is_emoticon_exists($emo_name, $emo_id)) {
            alert('이미 사용 중인 이모티콘 이름입니다.');
        }

        // 이미지 업로드 (선택)
        $new_file = '';
        if (isset($_FILES['emo_file']) && $_FILES['emo_file']['error'] === 0) {
            $new_file = save_emoticon_image($_FILES['emo_file']);
            if (!$new_file) {
                alert('이미지 업로드에 실패했습니다. (gif, png, webp, jpg 허용, 400x400 이하)');
            }

            // 기존 파일 삭제
            $old_emo = get_emoticon($emo_id);
            if ($old_emo && !empty($old_emo['emo_file'])) {
                $old_path = RA0_EMO_PATH . '/' . $old_emo['emo_file'];
                if (file_exists($old_path)) {
                    @unlink($old_path);
                }
            }
        }

        if (update_emoticon($emo_id, $emo_name, $new_file, $emo_category)) {
            alert('이모티콘이 수정되었습니다.', G5_ADMIN_URL . '/config_emoticon.php');
        } else {
            alert('이모티콘 수정에 실패했습니다.');
        }
    }

    // 등록
    if ($mode === 'insert') {
        // 중복 체크
        if (is_emoticon_exists($emo_name)) {
            alert('이미 사용 중인 이모티콘 이름입니다.');
        }

        // 이미지 업로드 (필수)
        if (!isset($_FILES['emo_file']) || $_FILES['emo_file']['error'] !== 0) {
            alert('이모티콘 이미지를 선택해주세요.');
        }

        $new_file = save_emoticon_image($_FILES['emo_file']);
        if (!$new_file) {
            alert('이미지 업로드에 실패했습니다. (gif, png, webp, jpg 허용, 400x400 이하)');
        }

        if (insert_emoticon($emo_name, $new_file, $emo_category)) {
            alert('이모티콘이 등록되었습니다.', G5_ADMIN_URL . '/config_emoticon.php');
        } else {
            // 실패 시 업로드된 파일 삭제
            @unlink(RA0_EMO_PATH . '/' . $new_file);
            alert('이모티콘 등록에 실패했습니다.');
        }
    }
}

// =====================================================
// 수정 모드 데이터 조회
// =====================================================
$edit_emo = null;
if ($mode === 'edit' && $emo_id > 0) {
    $edit_emo = get_emoticon($emo_id);
    if (!$edit_emo) {
        alert('존재하지 않는 이모티콘입니다.');
    }
}

// =====================================================
// 목록 조회
// =====================================================
$table = RA0_EMO_TABLE;
$sql = "SELECT * FROM {$table} ORDER BY emo_name";
$result = sql_query($sql);
$emoticon_list = [];
while ($row = sql_fetch_array($result)) {
    $emoticon_list[] = $row;
}

include_once(G5_ADMIN_PATH . '/admin.head.php');
?>

<style>
.emoticon-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
    margin-top: 20px;
}
.emoticon-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    text-align: center;
    transition: box-shadow 0.2s;
}
.emoticon-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.emoticon-card img {
    max-width: 64px;
    max-height: 64px;
    margin-bottom: 12px;
}
.emoticon-card .emo-name {
    font-weight: 600;
    margin-bottom: 4px;
}
.emoticon-card .emo-code {
    font-family: monospace;
    color: #64748b;
    font-size: 0.9em;
    margin-bottom: 8px;
}
.emoticon-card .emo-category {
    font-size: 0.8em;
    color: #94a3b8;
    margin-bottom: 12px;
}
.emoticon-card .emo-actions {
    display: flex;
    gap: 8px;
    justify-content: center;
}
.emoticon-form {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}
.emoticon-form h3 {
    margin-top: 0;
    margin-bottom: 16px;
    font-size: 1.1em;
}
.emoticon-form .form-row {
    display: flex;
    gap: 16px;
    align-items: flex-end;
    flex-wrap: wrap;
}
.emoticon-form .form-group {
    flex: 1;
    min-width: 150px;
}
.emoticon-form label {
    display: block;
    margin-bottom: 4px;
    font-weight: 500;
    font-size: 0.9em;
}
.emoticon-form input[type="text"],
.emoticon-form input[type="file"] {
    width: 100%;
    padding: 8px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    font-size: 0.95em;
}
.emoticon-form .preview-box {
    width: 64px;
    height: 64px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
}
.emoticon-form .preview-box img {
    max-width: 100%;
    max-height: 100%;
}
.empty-state {
    text-align: center;
    padding: 40px;
    color: #64748b;
}
</style>

<div class="local_desc01 local_desc">
    <p>게시글/댓글에서 사용할 이모티콘을 관리합니다. 사용자는 <code>/이름</code> 형식으로 입력합니다.</p>
</div>

<!-- 등록/수정 폼 -->
<form name="femoticon" action="<?php echo G5_ADMIN_URL ?>/config_emoticon.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="mode" value="<?php echo $edit_emo ? 'update' : 'insert' ?>">
<?php if ($edit_emo): ?>
<input type="hidden" name="emo_id" value="<?php echo $edit_emo['emo_id'] ?>">
<?php endif; ?>

<div class="emoticon-form">
    <h3><?php echo $edit_emo ? '이모티콘 수정' : '이모티콘 등록' ?></h3>
    <div class="form-row">
        <?php if ($edit_emo && !empty($edit_emo['emo_file'])): ?>
        <div class="form-group" style="flex:0;">
            <label>현재 이미지</label>
            <div class="preview-box">
                <img src="<?php echo RA0_EMO_URL . '/' . $edit_emo['emo_file'] ?>" alt="">
            </div>
        </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="emo_name">이모티콘 이름 <span style="color:#e53e3e">*</span></label>
            <input type="text" name="emo_name" id="emo_name" value="<?php echo $edit_emo ? htmlspecialchars($edit_emo['emo_name']) : '' ?>" required placeholder="예: ㅋㅋ, 신남, smile">
        </div>

        <div class="form-group">
            <label for="emo_file">이미지 <?php echo $edit_emo ? '(변경 시)' : '<span style="color:#e53e3e">*</span>' ?></label>
            <input type="file" name="emo_file" id="emo_file" accept="image/gif,image/png,image/webp,image/jpeg" <?php echo $edit_emo ? '' : 'required' ?>>
        </div>

        <div class="form-group">
            <label for="emo_category">카테고리</label>
            <input type="text" name="emo_category" id="emo_category" value="<?php echo $edit_emo ? htmlspecialchars($edit_emo['emo_category']) : '' ?>" placeholder="예: 표정, 동물">
        </div>

        <div class="form-group" style="flex:0;">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn_01"><?php echo $edit_emo ? '수정' : '등록' ?></button>
            <?php if ($edit_emo): ?>
            <a href="<?php echo G5_ADMIN_URL ?>/config_emoticon.php" class="btn btn_02">취소</a>
            <?php endif; ?>
        </div>
    </div>
    <p style="margin-top:10px; font-size:0.85em; color:#64748b;">
        허용 확장자: gif, png, webp, jpg / 최대 크기: 400x400px
    </p>
</div>
</form>

<!-- 이모티콘 목록 -->
<h3>등록된 이모티콘 (<?php echo count($emoticon_list) ?>개)</h3>

<?php if (empty($emoticon_list)): ?>
<div class="empty-state">
    <p>등록된 이모티콘이 없습니다.</p>
</div>
<?php else: ?>
<div class="emoticon-grid">
    <?php foreach ($emoticon_list as $emo): ?>
    <div class="emoticon-card">
        <img src="<?php echo RA0_EMO_URL . '/' . $emo['emo_file'] ?>" alt="<?php echo htmlspecialchars($emo['emo_name']) ?>">
        <div class="emo-name"><?php echo htmlspecialchars($emo['emo_name']) ?></div>
        <div class="emo-code">/<?php echo htmlspecialchars($emo['emo_name']) ?></div>
        <?php if (!empty($emo['emo_category'])): ?>
        <div class="emo-category"><?php echo htmlspecialchars($emo['emo_category']) ?></div>
        <?php endif; ?>
        <div class="emo-actions">
            <a href="<?php echo G5_ADMIN_URL ?>/config_emoticon.php?mode=edit&emo_id=<?php echo $emo['emo_id'] ?>" class="btn btn_03 btn_small">수정</a>
            <form action="<?php echo G5_ADMIN_URL ?>/config_emoticon.php" method="post" style="display:inline" onsubmit="return confirm('정말 삭제하시겠습니까?')">
                <input type="hidden" name="mode" value="delete">
                <input type="hidden" name="emo_id" value="<?php echo $emo['emo_id'] ?>">
                <input type="hidden" name="emo_name" value="<?php echo htmlspecialchars($emo['emo_name']) ?>">
                <button type="submit" class="btn btn_02 btn_small">삭제</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include_once(G5_ADMIN_PATH . '/admin.tail.php'); ?>
