<?php
if (!defined('_GNUBOARD_')) exit;
?>
<style>
/* ========================================
   FILE UPLOAD - DROPZONE
   ======================================== */

.dropzone-custom {
    border: 2px dashed #cbd5e1;
    border-radius: 8px;
    text-align: center;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 5px;
    padding: 30px 20px;
}

.dropzone-custom:hover {
    border-color: #94a3b8;
    background: #f1f5f9;
}

.dropzone-custom.dragover {
    border-color: #3b82f6;
    background: #eff6ff;
    transform: scale(1.02);
}

.dz-message {
    margin: 0;
}

.dz-message i {
    font-size: 48px;
    color: #94a3b8;
    display: block;
    margin-bottom: 15px;
}

.dz-message p {
    font-size: 16px;
    color: #475569;
    margin: 0 0 8px 0;
    font-weight: 500;
}

.dz-note {
    font-size: 13px;
    color: #94a3b8;
}

/* 파일 미리보기 그리드 */
.board-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

/* 파일 미리보기 카드 */
.board-card {
    position: relative;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #ffffff;
    transition: all 0.2s ease;
    overflow: hidden;
    cursor: pointer;
    height: 100px;
}

.board-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.board-card:active {
    transform: scale(0.98);
}

/* 이미지 영역 */
.board-card .file-preview-image {
    width: 100%;
    aspect-ratio: 1;
    overflow: hidden;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
}

.board-card .file-preview-image img,
.board-card > img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* 파일 아이콘 (이미지가 아닌 경우) */
.board-card .file-icon {
    width: 100%;
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
}

.board-card .file-icon i {
    font-size: 48px;
    color: #94a3b8;
}

/* 플레이스홀더 라벨 */
.board-card .file-placeholder {
    padding: 8px;
    text-align: center;
    font-size: 12px;
    font-weight: 500;
    color: #64748b;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

/* 삭제 버튼 - 오른쪽 상단 */
.board-card .btn-remove-file,
.board-card .btn-remove-existing {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: none;
    background: rgba(220, 38, 38, 0.9);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    opacity: 0;
    z-index: 10;
}

.board-card:hover .btn-remove-file,
.board-card:hover .btn-remove-existing {
    opacity: 1;
}

.board-card .btn-remove-file:hover,
.board-card .btn-remove-existing:hover {
    background: rgba(220, 38, 38, 1);
    transform: scale(1.1);
}

/* 플레이스홀더 라벨 - 왼쪽 하단 */
.board-card .file-placeholder-label {
    position: absolute;
    bottom: 6px;
    left: 6px;
    padding: 4px 8px;
    background: rgba(0, 0, 0, 0.75);
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    z-index: 10;
    pointer-events: none;
}

.file-deleted {
    opacity: 0.5;
    pointer-events: none;
}

/* 숨겨진 파일 입력 */
#hiddenFileInputs {
    display: none;
}

.hidden-file-input {
    display: none;
}

/* 에디터 툴바 버튼 */
.editor-toolbar {
    display: flex;
    gap: 8px;
    align-items: center;
}

.btn-tile-wrap {
    padding: 6px 12px;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-tile-wrap:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
}

.btn-tile-wrap:active {
    transform: scale(0.98);
}

.btn-tile-wrap i {
    font-size: 14px;
}

/* 에디터 내 타일 미리보기 - view와 동일한 구조 */
.editor-tile-preview {
    position: relative;
    padding: 5px;
    background: #f8f9fa;
    border: 2px dashed #cbd5e1;
    border-radius: 8px;
    max-height: 250px;
    overflow: hidden;
}

.editor-tile-preview::before {
    content: 'TILE';
    position: absolute;
    top: -10px;
    left: 10px;
    font-size: 10px;
    font-weight: 700;
    color: #94a3b8;
    background: white;
    padding: 2px 8px;
    border-radius: 3px;
    font-family: monospace;
    border: 1px solid #cbd5e1;
    z-index: 1;
}
</style>

<!-- 본문 (wr_content) -->
<div class="form-group">
    <label for="wr_content" class="form-label">본문</label>
    <?php echo $editor_html; ?>
</div>

<!-- 첨부 파일 -->
<?php if ($is_file) { ?>
    <div class="form-group">
        <label class="form-label">파일 첨부</label>
        <!-- Dropzone 영역 -->
        <div id="fileDropzone" class="dropzone-custom">
            <div class="dz-message">
                <i class="fas fa-cloud-upload-alt"></i>
                <p>이미지를 드래그하거나 클릭하여 업로드</p>
                <span class="dz-note">최대 <?php echo $board['bo_upload_count']; ?>개 / <?php echo $upload_max_filesize; ?></span>
            </div>
        </div>
        <!-- 기존 파일 미리보기 (수정 모드) -->
        <?php
        $max_bf_no = -1;
        if ($w == 'u' && $wr_id) {
            // 기존 파일의 최대 bf_no 계산
            $max_bf_no_row = sql_fetch("SELECT IFNULL(MAX(bf_no), -1) as max_no FROM {$g5['board_file_table']} WHERE bo_table = '{$bo_table}' AND wr_id = '{$wr_id}'");
            $max_bf_no = (int)$max_bf_no_row['max_no'];
        }
        ?>
        <?php if ($w == 'u' && isset($file) && is_array($file)) { ?>
            <div id="boardFiles" class="board-grid">
                <?php
                // bf_content >= 0인 파일만 가져와서 bf_content 순으로 정렬
                $existing_files = array();
                foreach ($file as $i => $f) {
                    if (isset($f['file']) && $f['file']) {
                        $sql = "SELECT bf_no, bf_content FROM {$g5['board_file_table']}
                                WHERE bo_table = '{$bo_table}'
                                AND wr_id = '{$wr_id}'
                                AND bf_file = '" . sql_real_escape_string($f['file']) . "'
                                AND bf_content >= 0";
                        $result = sql_fetch($sql);

                        if ($result) {
                            $existing_files[] = array(
                                'bf_no' => (int)$result['bf_no'],
                                'bf_content' => (int)$result['bf_content'],
                                'file' => $f['file'],
                                'source' => isset($f['source']) ? $f['source'] : '',
                            );
                        }
                    }
                }

                // bf_content 순으로 정렬
                usort($existing_files, function($a, $b) {
                    return $a['bf_content'] - $b['bf_content'];
                });

                // 0부터 순차적으로 인덱스 부여하여 표시
                foreach ($existing_files as $idx => $f) {
                    $file_path = G5_DATA_URL.'/file/'.$bo_table.'/'.$f['file'];
                    $is_image = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $f['file']);
                ?>
                    <div class="board-card"
                         data-bf-no="<?php echo $f['bf_no']; ?>"
                         data-bf-file="<?php echo htmlspecialchars($f['file']); ?>"
                         data-index="<?php echo $idx; ?>"
                         data-is-existing="true">
                        <?php if ($is_image) { ?>
                            <img src="<?php echo $file_path; ?>" alt="<?php echo htmlspecialchars($f['source']); ?>">
                        <?php } else { ?>
                            <div class="file-icon">
                                <i class="fas fa-file"></i>
                            </div>
                        <?php } ?>
                        <div class="file-placeholder-label">{이미지:<?php echo $idx; ?>}</div>
                        <button type="button" class="btn-remove-existing" data-bf-file="<?php echo htmlspecialchars($f['file']); ?>">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php
                }
                ?>
            </div>
        <?php } ?>
        <!-- 실제 폼 제출용 숨겨진 파일 입력 -->
        <div id="hiddenFileInputs"></div>
        <div class="form-help">
            <i class="fas fa-info-circle"></i> 본문에 삽입 가능. 최대 <?php echo $board['bo_upload_count']; ?>개 파일
        </div>
    </div>
<?php } ?>

<!-- 이미지 크기 설정 모달 -->
<div id="imageSizeModal" class="image-size-modal" style="display: none;">
    <div class="modal-overlay" onclick="closeImageSizeModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>이미지 크기 설정</h3>
            <button type="button" class="btn-close" onclick="closeImageSizeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>가로 사이즈를 입력하세요 (픽셀 단위). 비워두면 원본 크기로 표시됩니다.</p>
            <div class="form-group">
                <label for="imageWidthInput">가로 크기 (px)</label>
                <input type="number" id="imageWidthInput" class="form-control" placeholder="예: 500" min="50" max="2000" step="10">
            </div>
            <div class="size-presets">
                <button type="button" class="btn-preset" onclick="setImageWidth('')">원본</button>
                <button type="button" class="btn-preset" onclick="setImageWidth('300')">300px</button>
                <button type="button" class="btn-preset" onclick="setImageWidth('500')">500px</button>
                <button type="button" class="btn-preset" onclick="setImageWidth('800')">800px</button>
                <button type="button" class="btn-preset" onclick="setImageWidth('1000')">1000px</button>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closeImageSizeModal()">취소</button>
            <button type="button" class="btn-primary" onclick="applyImageSize()">확인</button>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const form = $('#fwrite');
    const maxFiles = <?php echo $board['bo_upload_count']; ?>;
    const maxBfNo = <?php echo $max_bf_no; ?>; // 기존 파일의 최대 bf_no
    let fileArray = []; // 새로 추가된 File 객체만 저장

    // ========== 핵심 함수: 모든 파일 재정렬 (0부터 연속) ==========
    function reorderAllFiles() {
        const allCards = $('.board-card');
        const totalCount = allCards.length;
        const viewer = document.getElementById('wr_content_viewer');

        if (!viewer) {
            // 에디터가 없으면 기본 방식으로 처리
            allCards.each(function(newIndex) {
                $(this).attr('data-index', newIndex);
                $(this).find('.file-placeholder-label').text(`{이미지:${newIndex}}`);
            });
            return;
        }

        // 1단계: oldIndex → newIndex 매핑 생성
        const indexMap = {}; // oldIndex => newIndex
        allCards.each(function(newIndex) {
            const oldIndex = parseInt($(this).attr('data-index'));
            indexMap[oldIndex] = newIndex;

            // 카드 업데이트
            $(this).attr('data-index', newIndex);
            $(this).find('.file-placeholder-label').text(`{이미지:${newIndex}}`);
        });

        // 2단계: 에디터 내 플레이스홀더 업데이트
        const placeholders = viewer.querySelectorAll('.image-placeholder');
        placeholders.forEach(placeholder => {
            const oldIndex = parseInt(placeholder.getAttribute('data-index'));
            const newIndex = indexMap[oldIndex];

            if (newIndex !== undefined) {
                const width = placeholder.getAttribute('data-width');
                renderImagePlaceholder(placeholder, newIndex, width);
            }
        });

        // input 이벤트 발생 (한 번만)
        const event = new Event('input', { bubbles: true });
        viewer.dispatchEvent(event);

        // 플레이스홀더 조작 재설정
        setupImagePlaceholderControls();
    }

    // 현재 전체 파일 개수 가져오기 (기존 + 새)
    function getTotalFileCount() {
        return $('.board-card').length;
    }

    function renderImagePlaceholder(span, index, width = '') {
        if (!span) return span;

        const normalizedWidth = width || '';
        const imageUrl = getPreviewImageUrl(index);
        const displayText = normalizedWidth ? `이미지:${index} (${normalizedWidth}px)` : `이미지:${index}`;

        span.className = 'image-placeholder';
        span.setAttribute('data-index', index);
        span.setAttribute('data-width', normalizedWidth);
        span.setAttribute('contenteditable', 'false');
        span.innerHTML = '';

        if (imageUrl) {
            const thumb = document.createElement('span');
            thumb.className = 'image-placeholder-thumb';
            thumb.style.backgroundImage = `url(${JSON.stringify(imageUrl)})`;
            span.appendChild(thumb);
        }

        const label = document.createElement('span');
        label.className = 'image-placeholder-label-inline';
        label.textContent = displayText;
        span.appendChild(label);

        return span;
    }

    function createImagePlaceholder(index, width = '') {
        return renderImagePlaceholder(document.createElement('span'), index, width);
    }

    function refreshImagePlaceholders(viewer) {
        if (!viewer) return;

        viewer.querySelectorAll('.image-placeholder').forEach(placeholder => {
            const index = placeholder.getAttribute('data-index');
            const width = placeholder.getAttribute('data-width') || '';
            renderImagePlaceholder(placeholder, index, width);
        });
    }

    function convertLegacyTileSyntax(content) {
        if (!content || content.indexOf('{tile}') === -1) return content;

        return content.replace(/\{tile\}([\s\S]*?)\{\/tile\}/g, function(match, innerContent) {
            const items = innerContent.match(/<span\b[^>]*class=(?:"[^"]*\bimage-placeholder\b[^"]*"|'[^']*\bimage-placeholder\b[^']*')[^>]*>[\s\S]*?<\/span>|<img\b[^>]*>|\{이미지:\d+(?:-\d+)?\}/gi);

            if (!items || !items.length) {
                return innerContent;
            }

            return '<div class="tile-grid editor-tile-preview" contenteditable="false" data-tile="true">' +
                items.map(function(item) {
                    return '<div class="tile-item">' + item + '</div>';
                }).join('') +
                '</div>';
        });
    }

    function refreshTilePreviews(viewer) {
        if (!viewer) return;

        viewer.querySelectorAll('.tile-grid').forEach(tile => {
            tile.classList.add('editor-tile-preview');
            tile.setAttribute('contenteditable', 'false');
            tile.setAttribute('data-tile', 'true');
        });
    }

    // ========== 클립보드 이미지 붙여넣기 ==========

    // 에디터에 paste 이벤트 리스너 추가
    const editor = document.getElementById('wr_content_viewer');
    if (editor) {
        editor.addEventListener('paste', function(e) {
            const items = e.clipboardData.items;

            for (let i = 0; i < items.length; i++) {
                const item = items[i];

                // 이미지 타입인 경우
                if (item.type.indexOf('image') !== -1) {
                    e.preventDefault(); // 기본 붙여넣기 동작 방지

                    const blob = item.getAsFile();

                    if (fileArray.length >= maxFiles) {
                        alert('최대 ' + maxFiles + '개의 파일만 업로드할 수 있습니다.');
                        return;
                    }

                    // blob을 File 객체로 변환
                    const timestamp = new Date().getTime();
                    const fileName = 'clipboard_' + timestamp + '.png';
                    const file = new File([blob], fileName, { type: 'image/png' });

                    // 파일 배열에 추가
                    fileArray.push(file);
                    // 현재 전체 파일 개수가 새 파일의 인덱스
                    const newIndex = getTotalFileCount();

                    // 미리보기 추가
                    addFilePreview(file, newIndex);
                    updateMessage();

                    break; // 첫 번째 이미지만 처리
                }
            }
        });
    }

    // ========== 타일 기능 ==========

    // 타일로 감싸기 버튼 클릭
    $(document).on('click', '.btn-tile-wrap', function(e) {
        e.preventDefault();

        const viewer = document.getElementById('wr_content_viewer');
        if (!viewer) {
            alert('에디터를 찾을 수 없습니다.');
            return;
        }

        const selection = window.getSelection();
        if (selection.rangeCount === 0) {
            alert('이미지를 선택해주세요.');
            return;
        }

        const range = selection.getRangeAt(0);

        // 선택 영역이 viewer 내부에 있는지 확인
        let node = range.commonAncestorContainer;
        let isInViewer = false;
        while (node) {
            if (node === viewer) {
                isInViewer = true;
                break;
            }
            node = node.parentNode;
        }

        if (!isInViewer) {
            alert('에디터 내에서 이미지를 선택해주세요.');
            return;
        }

        // 실제 DOM에서 선택된 요소들을 찾기 (이미지 또는 플레이스홀더 배지)
        const container = range.commonAncestorContainer;
        let selectedItems = [];

        // 선택 영역 내의 모든 이미지와 플레이스홀더 찾기
        if (container.nodeType === Node.ELEMENT_NODE) {
            const images = Array.from(container.querySelectorAll('img.editor-preview-image'));
            const placeholders = Array.from(container.querySelectorAll('.image-placeholder'));
            selectedItems = [...images, ...placeholders];
        } else if (container.parentNode) {
            const images = Array.from(container.parentNode.querySelectorAll('img.editor-preview-image'));
            const placeholders = Array.from(container.parentNode.querySelectorAll('.image-placeholder'));
            selectedItems = [...images, ...placeholders];
        }

        // 선택 범위 내에 있는 요소만 필터링
        selectedItems = selectedItems.filter(item => {
            return selection.containsNode(item, true);
        });

        if (selectedItems.length < 1) {
            alert('플레이스홀더를 선택해주세요.');
            return;
        }

        // 타일 컨테이너 생성 (view와 동일한 구조)
        const tileDiv = document.createElement('div');
        tileDiv.className = 'tile-grid editor-tile-preview';
        tileDiv.contentEditable = 'false';
        tileDiv.setAttribute('data-tile', 'true');

        // 첫 번째 요소의 부모 요소 찾기 (삽입 위치)
        const firstItem = selectedItems[0];
        const insertionPoint = firstItem.parentNode;

        // 요소들을 tile-item으로 감싸서 타일에 이동
        selectedItems.forEach(item => {
            // tile-item으로 감싸기 (view와 동일)
            const tileItem = document.createElement('div');
            tileItem.className = 'tile-item';

            // 플레이스홀더 배지인 경우 그대로 이동
            if (item.classList.contains('image-placeholder')) {
                tileItem.appendChild(item);
            } else {
                // 이미지인 경우 기존 로직
                if (!item.getAttribute('data-placeholder')) {
                    const imgSrc = item.getAttribute('src');
                    const boardCard = document.querySelector('.board-card img[src="' + imgSrc + '"]');
                    if (boardCard) {
                        const card = boardCard.closest('.board-card');
                        const index = card ? card.getAttribute('data-index') : null;
                        if (index) {
                            item.setAttribute('data-placeholder', '{이미지:' + index + '}');
                        }
                    }
                }
                tileItem.appendChild(item);
            }

            tileDiv.appendChild(tileItem);
        });

        // 타일을 첫 번째 이미지가 있던 위치에 삽입
        insertionPoint.insertBefore(tileDiv, insertionPoint.firstChild);

        // 빈 <p> 태그 정리
        viewer.querySelectorAll('p').forEach(p => {
            if (p.innerHTML.trim() === '' || p.innerHTML.trim() === '<br>') {
                p.remove();
            }
        });

        // 타일 뒤에 개행 추가
        const p = document.createElement('p');
        p.innerHTML = '<br>';
        if (tileDiv.nextSibling) {
            insertionPoint.insertBefore(p, tileDiv.nextSibling);
        } else {
            insertionPoint.appendChild(p);
        }

        // 선택 해제
        selection.removeAllRanges();

        // input 이벤트 발생 (source 자동 업데이트)
        const event = new Event('input', { bubbles: true });
        viewer.dispatchEvent(event);
    });

    // RA0 Editor viewer 내용 변경 시 source에 저장 (모든 에디터에 적용)
    function setupEditorSync(viewerId, sourceId) {
        const viewer = document.getElementById(viewerId);
        const source = document.getElementById(sourceId);

        if (viewer && source) {
            viewer.addEventListener('input', function() {
                syncEditorSource(viewer, source, sourceId);
            });
        }
    }

    function syncEditorSource(viewer, source, sourceId) {
        if (!viewer || !source) return;

        if (
            typeof RA0Editor !== 'undefined' &&
            RA0Editor.instances &&
            RA0Editor.instances[sourceId] &&
            typeof RA0Editor.syncToSource === 'function'
        ) {
            RA0Editor.syncToSource(sourceId);
            return;
        }

        if (typeof RA0Editor !== 'undefined' && typeof RA0Editor.serializeViewerContent === 'function') {
            source.value = RA0Editor.serializeViewerContent(viewer);
            return;
        }

        source.value = '<div class="ra0-content">' + viewer.innerHTML + '</div>';
    }

    // 모든 에디터에 적용 (wr_content, wr_1~wr_10)
    setupEditorSync('wr_content_viewer', 'wr_content');
    for (let i = 1; i <= 10; i++) {
        setupEditorSync('wr_' + i + '_viewer', 'wr_' + i);
    }

    // ========== 파일 업로드 처리 ==========

    // 드래그 앤 드롭 이벤트
    const dropzone = $('#fileDropzone');

    dropzone.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('dragover');
    });

    dropzone.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
    });

    dropzone.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');

        const files = e.originalEvent.dataTransfer.files;
        handleFiles(files);
    });

    // 클릭으로 파일 선택
    dropzone.on('click', function() {
        $('<input type="file" multiple accept="image/*">').on('change', function() {
            handleFiles(this.files);
        }).click();
    });

    // 파일 처리
    function handleFiles(files) {
        const currentTotal = getTotalFileCount();
        if (currentTotal + files.length > maxFiles) {
            alert('최대 ' + maxFiles + '개의 파일만 업로드할 수 있습니다.');
            return;
        }

        for (let i = 0; i < files.length; i++) {
            const file = files[i];

            // 이미지 파일 확인
            if (!file.type.startsWith('image/')) {
                alert(file.name + '은(는) 이미지 파일이 아닙니다.');
                continue;
            }

            if (getTotalFileCount() >= maxFiles) break;

            fileArray.push(file);
            // 현재 전체 파일 개수가 새 파일의 인덱스
            const newIndex = getTotalFileCount();
            addFilePreview(file, newIndex);
        }

        updateMessage();
    }

    // 파일 미리보기 이미지 URL 가져오기
    function getPreviewImageUrl(index) {
        const boardCard = $('.board-card[data-index="' + index + '"]');
        if (boardCard.length) {
            const imgSrc = boardCard.find('img').attr('src');
            return imgSrc;
        }

        return null;
    }

    // 에디터에 이미지 삽입
    function insertImageToEditor(imageUrl, index) {
        const viewer = document.getElementById('wr_content_viewer');
        if (!viewer) return;

        if (!index) return;

        // 에디터에 포커스
        viewer.focus();

        // 현재 선택 영역(커서 위치) 가져오기
        const selection = window.getSelection();

        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);

            // 커서가 viewer 내부에 있는지 확인
            let node = range.commonAncestorContainer;
            let isInViewer = false;

            while (node) {
                if (node === viewer) {
                    isInViewer = true;
                    break;
                }
                node = node.parentNode;
            }

            if (isInViewer) {
                const span = createImagePlaceholder(index);

                range.deleteContents();
                range.insertNode(span);

                // 플레이스홀더 뒤에 공백 추가 (커서 이동용)
                const space = document.createTextNode('\u00A0');
                range.setStartAfter(span);
                range.insertNode(space);
                range.setStartAfter(space);
                range.collapse(true);
                selection.removeAllRanges();
                selection.addRange(range);
            } else {
                // 커서가 viewer 밖이면 끝에 추가
                const p = document.createElement('p');
                p.appendChild(createImagePlaceholder(index));
                viewer.appendChild(p);
            }
        } else {
            // 선택 영역이 없으면 끝에 추가
            const p = document.createElement('p');
            p.appendChild(createImagePlaceholder(index));
            viewer.appendChild(p);
        }

        // input 이벤트 발생 (source 자동 업데이트)
        const event = new Event('input', { bubbles: true });
        viewer.dispatchEvent(event);

        // 플레이스홀더 조작 재설정
        setupImagePlaceholderControls();
    }

    // 파일 미리보기 추가
    function addFilePreview(file, index) {
        // blob URL 사용 (메모리 효율적)
        const imageUrl = URL.createObjectURL(file);
        const placeholder = `{이미지:${index}}`;

        const preview = $('<div>', {
            class: 'board-card',
            'data-index': index,
            'data-is-existing': 'false',
            'data-blob-url': imageUrl // blob URL 저장 (나중에 정리용)
        }).html(`
            <img src="${imageUrl}" alt="${file.name}">
            <div class="file-placeholder-label">${placeholder}</div>
            <button type="button" class="btn-remove-file">
                <i class="fas fa-times"></i>
            </button>
        `);

        // 그리드 컨테이너에 추가
        let gridContainer = $('#boardFiles');
        if (!gridContainer.length) {
            // 그리드가 없으면 생성
            gridContainer = $('<div id="boardFiles" class="board-grid"></div>');
            dropzone.after(gridContainer);
        }
        gridContainer.append(preview);

        // 에디터에 이미지 자동 삽입 제거 (더블클릭으로 수동 삽입만 사용)
        // insertImageToEditor(imageUrl, index);
    }

    // 파일 크기 포맷팅
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // 플레이스홀더 방식 폐기: 재정렬 불필요
    function reorderPlaceholdersInEditor() {
        // 실제 이미지 경로를 사용하므로 재정렬 불필요
        return;
    }

    // 새 파일 삭제
    $(document).on('click', '.btn-remove-file', function() {
        const item = $(this).closest('.board-card');
        const index = parseInt(item.data('index'));
        // fileArray에서 제거
        // 새 파일들만 필터링하여 인덱스 찾기
        const newFileCards = $('.board-card[data-is-existing="false"]');
        const newFileIndex = newFileCards.index(item);

        if (newFileIndex >= 0 && newFileIndex < fileArray.length) {
            fileArray.splice(newFileIndex, 1);
        }

        // DOM에서 제거
        item.remove();

        // 모든 파일 재정렬 (0부터 연속)
        reorderAllFiles();

        updateMessage();
    });

    // 기존 파일 삭제 (수정 모드) - 즉시 삭제 및 재정렬
    $(document).on('click', '.btn-remove-existing', function() {
        const item = $(this).closest('.board-card');
        const fileName = $(this).data('bf-file');
        const fileIndex = parseInt(item.data('index'));

        if (!fileName) {
            alert('삭제할 파일 정보가 없습니다.');
            return;
        }

        if (confirm('파일을 즉시 삭제하시겠습니까?')) {
            $.ajax({
                url: '<?php echo G5_BBS_URL; ?>/ajax.file_delete.php',
                type: 'POST',
                data: {
                    wr_id: '<?php echo $wr_id; ?>',
                    fileName: fileName,
                    bo_table: '<?php echo $bo_table; ?>',
                    clear_wr_link1: false,
                    clear_wr_link2: false,
                    reorder: true  // 재정렬 요청
                },
                dataType: 'json',
                success: function(response) {
                    if (response.result === 'success') {
                        // 에디터에서 해당 이미지 제거
                        const viewer = document.getElementById('wr_content_viewer');
                        if (viewer) {
                            // 실제 경로 이미지 제거 (data-placeholder 없음)
                            const imgs = viewer.querySelectorAll('img.editor-preview-image');
                            imgs.forEach(img => {
                                if (img.src.includes(fileName)) {
                                    img.remove();
                                }
                            });

                            // input 이벤트 발생
                            const event = new Event('input', { bubbles: true });
                            viewer.dispatchEvent(event);
                        }

                        item.fadeOut(300, function() {
                            $(this).remove();

                            // 모든 파일 재정렬 (0부터 연속)
                            reorderAllFiles();
                        });
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('파일 삭제 중 오류 발생: ' + error);
                }
            });
        }
    });

    // 메시지 업데이트
    function updateMessage() {
        if (fileArray.length > 0) {
            dropzone.addClass('has-files');
        } else {
            dropzone.removeClass('has-files');
        }
    }

    // 파일 순서 업데이트
    function updateFileOrder() {
    }

    // 마지막으로 포커스된 에디터 추적
    let lastFocusedEditor = null;
    $(document).on('focus', '[id$="_viewer"]', function() {
        lastFocusedEditor = this;
    });

    // 첨부파일 카드 더블클릭 시 에디터 커서 위치에 플레이스홀더 텍스트 삽입
    $(document).on('dblclick', '.board-card', function(e) {
        e.preventDefault();
        const index = $(this).data('index');
        // 현재 선택된 에디터 찾기 (커서가 있는 에디터)
        let viewer = null;
        const selection = window.getSelection();

        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            let node = range.commonAncestorContainer;

            // 커서가 있는 에디터 찾기
            while (node && node !== document.body) {
                if (node.id && node.id.endsWith('_viewer')) {
                    viewer = node;
                    break;
                }
                node = node.parentNode;
            }
        }

        // 선택된 에디터가 없으면 마지막 포커스된 에디터 사용
        if (!viewer) {
            viewer = lastFocusedEditor || document.getElementById('wr_content_viewer');
        }

        if (!viewer) return;

        viewer.focus();

        // 플레이스홀더 배지 엘리먼트 생성
        const span = createImagePlaceholder(index);

        // 커서 위치에 삽입
        const currentSelection = window.getSelection();
        if (currentSelection.rangeCount > 0) {
            const range = currentSelection.getRangeAt(0);
            range.deleteContents();
            range.insertNode(span);

            // 커서를 플레이스홀더 뒤로 이동
            range.setStartAfter(span);
            range.collapse(true);
            currentSelection.removeAllRanges();
            currentSelection.addRange(range);
        } else {
            // 선택 영역이 없으면 끝에 추가
            viewer.appendChild(span);
        }

        // input 이벤트 발생
        const event = new Event('input', { bubbles: true });
        viewer.dispatchEvent(event);

        // 플레이스홀더 조작 재설정
        setupImagePlaceholderControls();
    });

    // ========== 플레이스홀더 조작 ==========
    let currentEditingPlaceholder = null;

    function setupImagePlaceholderControls() {
        const viewer = document.getElementById('wr_content_viewer');
        if (!viewer) return;

        const placeholders = viewer.querySelectorAll('.image-placeholder');
        placeholders.forEach(placeholder => {
            placeholder.ondblclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                openImageSizeModal(this);
            };
        });
    }

    // ========== 이미지 크기 설정 모달 ==========
    function openImageSizeModal(placeholderElement) {
        currentEditingPlaceholder = placeholderElement;
        const currentWidth = placeholderElement.getAttribute('data-width');

        // 현재 설정된 너비를 입력창에 표시
        const input = document.getElementById('imageWidthInput');
        input.value = currentWidth || '';
        input.focus();

        // 모달 표시
        const modal = document.getElementById('imageSizeModal');
        modal.style.display = 'flex';
    }

    function closeImageSizeModal() {
        const modal = document.getElementById('imageSizeModal');
        modal.style.display = 'none';
        currentEditingPlaceholder = null;
    }

    function setImageWidth(width) {
        const input = document.getElementById('imageWidthInput');
        input.value = width;
    }

    function applyImageSize() {
        if (!currentEditingPlaceholder) return;

        const input = document.getElementById('imageWidthInput');
        const width = input.value.trim();
        const index = currentEditingPlaceholder.getAttribute('data-index');

        // 플레이스홀더 업데이트
        renderImagePlaceholder(currentEditingPlaceholder, index, width);

        // input 이벤트 발생 (source 자동 업데이트)
        const viewer = document.getElementById('wr_content_viewer');
        if (viewer) {
            const event = new Event('input', { bubbles: true });
            viewer.dispatchEvent(event);
        }

        closeImageSizeModal();
    }

    // Enter 키로 확인
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('imageWidthInput');
        if (input) {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    applyImageSize();
                }
            });
        }
    });

    // 전역 함수로 노출 (HTML onclick에서 사용)
    window.openImageSizeModal = openImageSizeModal;
    window.closeImageSizeModal = closeImageSizeModal;
    window.setImageWidth = setImageWidth;
    window.applyImageSize = applyImageSize;

    // ========== 드래그 앤 드롭 순서 변경 ==========
    const gridContainer = $('#boardFiles');
    if (gridContainer.length) {
        gridContainer.sortable({
            items: '.board-card',
            cursor: 'move',
            opacity: 0.7,
            placeholder: 'board-card-placeholder',
            update: function(event, ui) {
                reorderAllFiles();
            }
        });
    }

    // 폼 제출 전 파일 처리
    form.on('submit', function(e) {
        // 제출 직전에 에디터 내용을 강제로 한 번 더 동기화 (안전장치)
        syncEditorSource(
            document.getElementById('wr_content_viewer'),
            document.getElementById('wr_content'),
            'wr_content'
        );

        // 파일 업로드 처리
        if (fileArray.length > 0 || $('.board-card[data-is-existing="true"]').length > 0) {
            e.preventDefault();

            const formData = new FormData(this);

            // 기존 bf_file[], bf_content[] 제거
            formData.delete('bf_file[]');

            // bf_content도 모두 제거 후 재생성
            const keysToDelete = [];
            for (let [key] of formData.entries()) {
                if (key.startsWith('bf_content[')) {
                    keysToDelete.push(key);
                }
            }
            keysToDelete.forEach(key => formData.delete(key));

            <?php if ($w == 'u') { ?>
            // 수정 모드: 모든 파일을 순서대로 정렬하여 전송
            const allCards = $('.board-card');
            const existingFileMap = {}; // bf_no => {filename, newIndex}
            let newFileIndex = 0;

            // 1단계: 모든 카드를 순회하며 정보 수집
            allCards.each(function(idx) {
                const isExisting = $(this).attr('data-is-existing') === 'true';
                const newIndex = parseInt($(this).attr('data-index')); // 재정렬된 인덱스 (0부터)

                if (isExisting) {
                    // 기존 파일
                    const bfNo = parseInt($(this).attr('data-bf-no'));
                    const fileName = $(this).attr('data-bf-file');
                    existingFileMap[bfNo] = { fileName, newIndex };
                }
            });

            // 2단계: 0부터 maxBfNo까지 빈 슬롯 + 기존 파일 bf_content 업데이트
            for (let i = 0; i <= maxBfNo; i++) {
                formData.append('bf_file[]', new Blob(), '');

                if (existingFileMap[i] !== undefined) {
                    // 기존 파일의 bf_content를 새 인덱스로 업데이트
                    formData.append('bf_content[' + i + ']', existingFileMap[i].newIndex);
                }
                // 삭제된 파일은 bf_content를 전송하지 않음 (write_update.php에서 자동 삭제)
            }

            // 3단계: 새 파일 추가
            const newFileCards = $('.board-card[data-is-existing="false"]');
            newFileCards.each(function(idx) {
                const newIndex = parseInt($(this).attr('data-index'));
                const file = fileArray[idx];

                if (file) {
                    const newBfNo = maxBfNo + 1 + idx;
                    formData.append('bf_file[]', file);
                    formData.append('bf_content[' + newBfNo + ']', newIndex);
                }
            });
            <?php } else { ?>
            // 신규 작성: 새 파일만 순서대로 추가
            fileArray.forEach(function(file, idx) {
                if (file) {
                    formData.append('bf_file[]', file);
                    formData.append('bf_content[' + idx + ']', idx); // bf_content는 0부터
                }
            });
            <?php } ?>

            // AJAX로 전송
            const actionUrl = form.attr('action');
            $.ajax({
                url: actionUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response, textStatus, xhr) {
                    let redirectUrl = '<?php echo get_pretty_url($bo_table); ?>';

                    // JSON 응답 처리
                    if (response && typeof response === 'object' && response.redirect_url) {
                        redirectUrl = response.redirect_url;
                        if (response.message) {
                            alert(response.message);
                        }
                    }
                    window.location.href = redirectUrl;
                },
                error: function(xhr, status, error) {
                    // 302는 에러가 아님 (성공적인 리다이렉트)
                    if (xhr.status === 302 || xhr.status === 0) {
                        // Location 헤더에서 리다이렉트 URL 가져오기
                        const redirectUrl = xhr.getResponseHeader('Location');
                        if (redirectUrl) {
                            window.location.href = redirectUrl;
                        } else {
                            // Location 헤더가 없으면 기본 페이지로
                            window.location.href = '<?php echo get_pretty_url($bo_table); ?>';
                        }
                    } else {
                        alert('업로드 중 오류가 발생했습니다.');
                    }
                }
            });

            return false;
        }

        return true;
    });

    // 에디터 초기화: 플레이스홀더를 스타일링된 배지로 표시
    function initializeEditorContent() {
        const viewer = document.getElementById('wr_content_viewer');
        if (!viewer) return;

        let content = viewer.innerHTML;

        // 기존 {tile}...{/tile} 문법을 수정 화면에서 타일 영역으로 복원
        content = convertLegacyTileSyntax(content);

        // {이미지:숫자} 또는 {이미지:숫자-너비} 패턴을 배지로 표시
        content = content.replace(/\{이미지:(\d+)(?:-(\d+))?\}/g, function(match, index, width) {
            return `<span class="image-placeholder" data-index="${index}" data-width="${width || ''}" contenteditable="false"></span>`;
        });

        viewer.innerHTML = content;
        refreshTilePreviews(viewer);
        refreshImagePlaceholders(viewer);

        // 플레이스홀더 조작 재설정
        setupImagePlaceholderControls();
    }

    // 페이지 로드 시 에디터 초기화 (플레이스홀더 → 실제 이미지)
    document.addEventListener('ra0editor:content-loaded', function(e) {
        if (e.target && e.target.id === 'wr_content_viewer') {
            initializeEditorContent();
        }
    });

    setTimeout(function() {
        initializeEditorContent();
    }, 300);
});
</script>
