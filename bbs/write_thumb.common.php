<?php
if (!defined('_GNUBOARD_')) exit;

/**
 * 게시판 썸네일 업로드 공용 컴포넌트
 *
 * wr_img 필드를 사용하는 게시판에서 사용
 * - timeline 게시판
 * - store_gallery 등
 *
 * 사용 방법:
 * <?php include_once(G5_BBS_PATH . '/write_thumb.common.php'); ?>
 */

$current_wr_img = isset($write['wr_img']) ? $write['wr_img'] : '';
$thumbnail_type = isset($_POST['thumbnail_type']) ? $_POST['thumbnail_type'] : 'file';
?>

<div class="form-group">
    <label for="wr_img" class="form-label">썸네일 이미지</label>

    <!-- 업로드 방식 선택 -->
    <div class="thumbnail-type-selector">
        <label style="margin-right: 15px;">
            <input type="radio" name="thumbnail_type" value="file" <?php echo $thumbnail_type == 'file' ? 'checked' : ''; ?>>
            파일 업로드
        </label>
        <label>
            <input type="radio" name="thumbnail_type" value="url" <?php echo $thumbnail_type == 'url' ? 'checked' : ''; ?>>
            URL 입력
        </label>
    </div>

    <div class="thumbnail-upload-section">
        <!-- 파일 업로드 -->
        <div class="thumbnail-upload-method" id="thumbnail-file" style="<?php echo $thumbnail_type == 'url' ? 'display:none;' : ''; ?>">
            <input type="file" name="wr_img_file" id="wr_img_file" class="frm_input" accept="image/*">
            <div class="form-help">이미지 파일을 선택하세요.</div>
        </div>

        <!-- URL 입력 -->
        <div class="thumbnail-upload-method" id="thumbnail-url" style="<?php echo $thumbnail_type == 'file' ? 'display:none;' : ''; ?>">
            <input type="text" name="wr_img_url" id="wr_img_url" class="frm_input"
                   value="<?php echo $thumbnail_type == 'url' && $current_wr_img ? $current_wr_img : ''; ?>"
                   placeholder="https://example.com/image.jpg">
            <div class="form-help">외부 이미지 URL을 입력하세요.</div>
        </div>
    </div>

    <!-- 현재 썸네일 (수정 모드) -->
    <?php if ($w == 'u' && $current_wr_img) { ?>
        <div class="current-thumbnail" id="current-thumbnail">
            <label>현재 썸네일:</label><br>
            <?php
            $thumb_url = '';
            if (strpos($current_wr_img, 'http') === 0) {
                $thumb_url = $current_wr_img;
            } else {
                $thumb_url = G5_DATA_URL . '/file/' . $bo_table . '/' . $current_wr_img;
            }
            ?>
            <div style="position: relative; display: inline-block;">
                <img src="<?php echo $thumb_url; ?>" alt="현재 썸네일" id="thumbnail-preview" style="max-width: 200px; border: 1px solid #ddd; padding: 5px; display: block;">
                <button type="button" class="btn-remove-thumbnail" onclick="deleteThumbnailFile()" style="position: absolute; top: 5px; right: 5px; width: 28px; height: 28px; border-radius: 50%; border: none; background: rgba(220, 38, 38, 0.9); color: white; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <input type="hidden" name="wr_img_keep" id="wr_img_keep" value="<?php echo htmlspecialchars($current_wr_img); ?>">
        </div>
    <?php } ?>
</div>

<script>
// 썸네일 업로드 방식 전환
document.querySelectorAll('input[name="thumbnail_type"]').forEach(radio => {
    radio.addEventListener('change', function() {
        if (this.value === 'file') {
            document.getElementById('thumbnail-file').style.display = 'block';
            document.getElementById('thumbnail-url').style.display = 'none';
        } else {
            document.getElementById('thumbnail-file').style.display = 'none';
            document.getElementById('thumbnail-url').style.display = 'block';
        }
    });
});

// 썸네일 파일 삭제
window.deleteThumbnailFile = function() {
    const thumbnailKeep = $('#wr_img_keep').val();
    if (!thumbnailKeep) {
        alert('삭제할 썸네일이 없습니다.');
        return;
    }

    if (!confirm('썸네일을 삭제하시겠습니까?')) {
        return;
    }

    $.ajax({
        url: '<?php echo G5_BBS_URL; ?>/ajax.file_delete.php',
        type: 'POST',
        data: {
            wr_id: '<?php echo $wr_id; ?>',
            fileName: thumbnailKeep,
            bo_table: '<?php echo $bo_table; ?>',
            clear_wr_img: true
        },
        dataType: 'json',
        success: function(response) {
            if (response.result === 'success') {
                $('#current-thumbnail').fadeOut(300, function() {
                    $(this).remove();
                });
                $('#wr_img_keep').val('');
                alert('썸네일이 삭제되었습니다.');
            } else {
                alert('썸네일 삭제 실패: ' + (response.message || '알 수 없는 오류'));
            }
        },
        error: function(xhr, status, error) {
            alert('썸네일 삭제 중 오류 발생: ' + error);
        }
    });
};
</script>
