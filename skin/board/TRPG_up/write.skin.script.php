<?php
if (!defined('_GNUBOARD_')) exit;
?>

<script>
// 캐릭터 이미지와 일반 이미지 업로드를 위한 개선된 코드
$(document).ready(function() {
    // 삭제된 이미지 ID 저장 배열 초기화
    let deletedChaImages = [];
    let deletedNormalImages = [];

    // ★ 파일을 별도 배열로 관리 (브라우저 덮어쓰기 방지)
    // window 객체에 노출하여 submit 핸들러에서 접근 가능하도록 함
    window.chaFilesArray = [];
    window.normalFilesArray = [];

    // 캐릭터 이미지 드롭존 처리
    setupDropzone('cha-dropzone', 'trpg_cha_image', 'cha-preview', updateChaImageOrder, 'cha');

    // 일반 이미지 드롭존 처리
    setupDropzone('normal-dropzone', 'trpg_normal_image', 'normal-preview', updateNormalImageOrder, 'normal');

    // 드롭존 설정 함수
    function setupDropzone(dropzoneId, inputId, previewId, updateOrderFunc, fileType) {
        const dropzone = $(`#${dropzoneId}`);
        const input = document.getElementById(inputId);
        const preview = $(`#${previewId}`);

        // 드래그 오버 이벤트
        dropzone.on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });

        // 드래그 리브 이벤트
        dropzone.on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });

        // 드롭 이벤트
        dropzone.on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');

            const droppedFiles = Array.from(e.originalEvent.dataTransfer.files);

            // 파일 배열에 추가
            addFilesToArray(droppedFiles, fileType);

            // 미리보기 생성
            createPreviews(droppedFiles, previewId, updateOrderFunc, fileType);
        });

        // 파일 선택 시 이벤트
        $(`#${inputId}`).on('change', function(e) {
            const selectedFiles = Array.from(e.target.files);

            // 파일 배열에 추가
            addFilesToArray(selectedFiles, fileType);

            // 미리보기 생성
            createPreviews(selectedFiles, previewId, updateOrderFunc, fileType);

            // input 초기화 (같은 파일 재선택 가능하도록)
            e.target.value = '';
        });
    }

    // 파일을 배열에 추가하는 함수
    function addFilesToArray(newFiles, fileType) {
        newFiles.forEach(file => {
            if (file.type.match('image.*')) {
                if (fileType === 'cha') {
                    window.chaFilesArray.push(file);
                } else {
                    window.normalFilesArray.push(file);
                }
            }
        });
    }

    // 파일 배열에서 제거하는 함수
    function removeFileFromArray(fileName, fileType) {
        if (fileType === 'cha') {
            window.chaFilesArray = window.chaFilesArray.filter(f => f.name !== fileName);
        } else {
            window.normalFilesArray = window.normalFilesArray.filter(f => f.name !== fileName);
        }
    }
    
    // 미리보기 생성 함수
    function createPreviews(files, previewId, updateOrderFunc, fileType) {
        files.forEach(function(file) {
            if (file.type.match('image.*')) {
                // 파일 크기 표시를 위한 함수
                function formatFileSize(bytes) {
                    if (bytes < 1024) return bytes + ' B';
                    else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                    else return (bytes / 1048576).toFixed(1) + ' MB';
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const container = $('<div class="preview-item new"></div>')
                        .attr('data-file', file.name)
                        .attr('data-filetype', fileType);

                    const img = $('<img>').attr('src', e.target.result).attr('alt', '이미지');
                    const removeBtn = $('<button type="button" class="delete-btn"><i class="fa-solid fa-times"></i></button>');

                    // 파일 정보 표시 추가
                    const fileInfo = $('<div class="file-info"></div>')
                        .html(`<span class="file-name">${file.name}</span> <span class="file-size">(${formatFileSize(file.size)})</span>`);

                    removeBtn.on('click', function() {
                        if (confirm('이미지를 삭제하시겠습니까?')) {
                            container.remove();

                            // 파일 배열에서 제거
                            removeFileFromArray(file.name, fileType);

                            // 순서 업데이트
                            updateOrderFunc();
                        }
                    });

                    container.append(img).append(removeBtn).append(fileInfo);
                    $(`#${previewId}`).append(container);

                    // 순서 업데이트
                    updateOrderFunc();
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // 업로드 버튼 클릭 이벤트 추가
    $('#cha-upload-btn').on('click', function(e) {
        e.preventDefault();
        $('#trpg_cha_image').click();
    });

    $('#normal-upload-btn').on('click', function(e) {
        e.preventDefault();
        $('#trpg_normal_image').click();
    });

    // 세션 카드 이미지 처리 개선
    $('#card-upload-btn').on('click', function() {
        $('#trpg_card_image').click();
    });

    $('#trpg_card_image').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            function formatFileSize(bytes) {
                if (bytes < 1024) return bytes + ' B';
                else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                else return (bytes / 1048576).toFixed(1) + ' MB';
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewHtml = `
                    <div class="card-preview-container">
                        <img src="${e.target.result}" alt="세션 카드">
                        <div class="file-info">
                            <span class="file-name">${file.name}</span> 
                            <span class="file-size">(${formatFileSize(file.size)})</span>
                        </div>
                    </div>
                `;
                $('#card-preview').html(previewHtml);
                $('#trpg_card_url').val(''); // URL 입력 필드 초기화
            };
            reader.readAsDataURL(file);
        }
    });

    // 세션 카드 이미지 로드 함수
    function loadExistingCardImage() {
        try {
            // 데이터가 비어있는지 확인
            if (!$('#card-images-data').val() || $('#card-images-data').val() === '[]') {
                $('#card-preview').empty();
                return;
            }
            
            const cardImagesData = JSON.parse($('#card-images-data').val());
            
            if (cardImagesData && cardImagesData.length > 0) {
                const cardImage = cardImagesData[0]; // 첫 번째 이미지만 사용
                
                // 이미지 URL을 입력 필드에 설정
                $('input[name="trpg_card_url"]').val(cardImage.image_url);
                
                // 파일명 추출 (URL에서 마지막 부분)
                let fileName = cardImage.image_url.split('/').pop();
                // 파일명이 너무 길면 잘라내기
                if (fileName.length > 20) {
                    fileName = fileName.substring(0, 17) + '...';
                }
                
                // 미리보기 생성
                const previewHtml = `
                    <div class="card-preview-container">
                        <img src="${cardImage.image_url}" alt="세션 카드">
                        <div class="file-info">
                            <span class="file-name">${fileName}</span>
                            <span class="file-type">(${cardImage.wr_type === 'url' ? 'URL' : '업로드 파일'})</span>
                        </div>
                    </div>
                `;
                $('#card-preview').html(previewHtml);
            } else {
                $('#card-preview').empty();
            }
        } catch (e) {
            $('#card-preview').html('<div class="alert alert-danger">세션 카드 데이터 로드 중 오류가 발생했습니다.</div>');
        }
    }

    // 페이지 로드 시 세션 카드 이미지 로드
    loadExistingCardImage();
    
    // 기존 캐릭터 이미지 로드
    loadExistingImages('cha-images-data', 'cha-preview', 'cha-image-delete', updateChaImageOrder, deletedChaImages);
    
    // 기존 일반 이미지 로드
    loadExistingImages('normal-images-data', 'normal-preview', 'normal-image-delete', updateNormalImageOrder, deletedNormalImages);
    
    // 기존 이미지 로드 함수 수정
    function loadExistingImages(dataId, previewId, deleteInputId, updateOrderFunc) {
        const imagesData = JSON.parse($(`#${dataId}`).val() || '[]');
        const preview = $(`#${previewId}`);
        
        imagesData.forEach(function(image) {
            const container = $('<div class="preview-item existing"></div>')
                .attr('data-id', image.id)
                .attr('data-url', image.image_url);
            
            const img = $('<img>').attr('src', image.image_url).attr('alt', '이미지');
            
            // 삭제 버튼 생성
            const deleteBtn = $('<button type="button" class="delete-btn"><i class="fa-solid fa-times"></i></button>');
            
            deleteBtn.on('click', function(e) {
                // 이벤트 전파 중지 및 기본 동작 방지
                e.preventDefault();
                e.stopPropagation();
                
                if (confirm('이 이미지를 삭제하시겠습니까?')) {
                    // AJAX로 서버에 삭제 요청
                    $.ajax({
                        url: '<?=$board_skin_url;?>/file_delete.php',
                        type: 'POST',
                        data: {
                            bo_table: document.querySelector('input[name="bo_table"]').value,
                            wr_id: document.querySelector('input[name="wr_id"]').value,
                            fileName: image.image_url
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.result === 'success') {
                                // 삭제 성공 시 UI에서 제거
                                container.remove();
                                
                                // 이미지 순서 업데이트
                                updateOrderFunc();
                            } else {
                                alert('이미지 삭제 실패: ' + response.message);
                            }
                        },
                        error: function() {
                            alert('서버 오류로 이미지 삭제에 실패했습니다.');
                        }
                    });
                }
            });
            
            // 파일명 추출
            const fileName = image.image_url.split('/').pop();
            
            // 파일 정보 표시 추가
            const fileInfo = $('<div class="file-info"></div>')
                .html(`<span class="file-name">${fileName}</span> <span class="file-type">(${image.wr_type === 'url' ? 'URL' : '업로드 파일'})</span>`);
            
            container.append(img);
            container.append(deleteBtn);
            container.append(fileInfo);
            preview.append(container);
        });
        
        // 드래그 앤 드롭 정렬 기능
        preview.sortable({
            update: function() {
                updateOrderFunc();
            }
        });
    }
    
    // URL 입력 기능 추가
    addUrlInputField('cha-dropzone', 'cha-preview', updateChaImageOrder);
    addUrlInputField('normal-dropzone', 'normal-preview', updateNormalImageOrder);
    
    // URL 입력 필드 추가 함수 수정
    function addUrlInputField(dropzoneId, previewId, updateOrderFunc) {
        const urlInputContainer = $('<div class="url-input-container"></div>');
        const urlInput = $('<input type="text" class="frm_input" placeholder="이미지 URL 입력">');
        const addUrlBtn = $('<button type="button" class="add-url-btn"><i class="fa-solid fa-plus"></i> URL 추가</button>');
        
        urlInputContainer.append(urlInput).append(addUrlBtn);
        $(`#${dropzoneId}`).prepend(urlInputContainer);
        
        // URL 추가 버튼 클릭 시 처리
        addUrlBtn.on('click', function() {
            const url = urlInput.val().trim();
            if (url) {
                // URL 유효성 검사
                if (!isValidImageUrl(url)) {
                    // URL이 유효하지 않아도 사용자가 원하면 추가 가능
                    if (!confirm('이미지 URL이 아닌 것 같습니다. 그래도 추가하시겠습니까?')) {
                        return;
                    }
                }

                // 미리보기 추가
                const container = $('<div class="preview-item url-item"></div>')
                    .attr('data-url', url);

                const img = $('<img>').attr('alt', '이미지');
                const removeBtn = $('<button type="button" class="delete-btn"><i class="fa-solid fa-times"></i></button>');

                // 이미지 로드 실패 시 대체 이미지
                img.on('error', function() {
                    $(this).attr('src', 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjBmMGYwIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdG9taC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPuydtOuvuOyngCDroZzrk5wg7Iuk7YiwPC90ZXh0Pjwvc3ZnPg==');
                });

                // src 설정 (이벤트 핸들러 설정 후)
                img.attr('src', url);

                // 파일명 추출
                const fileName = url.split('/').pop() || 'image';

                // 파일 정보 표시 추가
                const fileInfo = $('<div class="file-info"></div>')
                    .html(`<span class="file-name">${fileName}</span> <span class="file-type">(URL)</span>`);

                removeBtn.on('click', function() {
                    if (confirm('이미지를 삭제하시겠습니까?')) {
                        container.remove();
                        updateOrderFunc();
                    }
                });

                container.append(img).append(removeBtn).append(fileInfo);
                $(`#${previewId}`).append(container);
                updateOrderFunc();

                // 입력 필드 초기화
                urlInput.val('');
            } else {
                alert('URL을 입력해주세요.');
            }
        });
        // URL 입력 필드 추가 (이름 통일)
        const urlsInputId = previewId === 'cha-preview' ? 'cha-image-urls' : 'normal-image-urls';
        if ($(`#${urlsInputId}`).length === 0) {
            $(`<input type="hidden" id="${urlsInputId}" name="${urlsInputId}" value="[]">`).appendTo('#fwrite');
        }
    }
    
    // URL 유효성 검사 함수 개선
    function isValidImageUrl(url) {
        // 기본 URL 패턴 검사
        if (!url || url.trim() === '') return false;

        // data URI 허용
        if (url.startsWith('data:image/')) return true;

        // HTTP/HTTPS URL 검사
        if (!url.match(/^https?:\/\//i)) return false;

        // 이미지 확장자 확인 (확장자가 있는 경우)
        const hasImageExt = url.match(/\.(jpeg|jpg|gif|png|webp|bmp|svg)($|\?)/i);

        // 이미지 호스팅 서비스 패턴 확인
        const imageHostPatterns = [
            /imgur\.com/i,
            /imgbb\.com/i,
            /postimg\./i,
            /cloudinary\.com/i,
            /discordapp\.com\/attachments/i,
            /discord\.com\/attachments/i,
            /media\.discordapp\.net/i,
            /googleusercontent\.com/i,
            /pinimg\.com/i,
            /tumblr\.com/i,
            /twimg\.com/i,
            /photobucket\.com/i,
            /flickr\.com/i,
            /staticflickr\.com/i,
            /wp\.com/i,
            /blogspot\.com/i,
            /githubusercontent\.com/i
        ];

        const isImageHost = imageHostPatterns.some(pattern => pattern.test(url));

        // 확장자가 있거나 알려진 이미지 호스팅 서비스면 허용
        return hasImageExt || isImageHost;
    }
    
    // 파일 업로드 영역 클릭 시 파일 선택 다이얼로그 표시
    $('#cha-dropzone').on('click', function(e) {
        if (e.target === this || $(e.target).hasClass('preview')) {
            $('#cha-upload-btn').click();
        }
    });
    
    $('#normal-dropzone').on('click', function(e) {
        if (e.target === this || $(e.target).hasClass('preview')) {
            $('#normal-upload-btn').click();
        }
    });
    
    // 붙여넣기 이벤트 처리
    $(document).on('paste', function(e) {
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (let item of items) {
            if (item.type.indexOf('image') !== -1) {
                const file = item.getAsFile();
                
                // 현재 포커스된 영역에 따라 이미지 추가 대상 결정
                let targetInputId, targetPreviewId, updateOrderFunc;
                
                if ($('#cha-dropzone').is(':hover') || $('#cha-preview').is(':hover')) {
                    targetInputId = 'trpg_cha_image';
                    targetPreviewId = 'cha-preview';
                    updateOrderFunc = updateChaImageOrder;
                } else if ($('#normal-dropzone').is(':hover') || $('#normal-preview').is(':hover')) {
                    targetInputId = 'trpg_normal_image';
                    targetPreviewId = 'normal-preview';
                    updateOrderFunc = updateNormalImageOrder;
                } else {
                    // 기본값: 캐릭터 이미지 영역
                    targetInputId = 'trpg_cha_image';
                    targetPreviewId = 'cha-preview';
                    updateOrderFunc = updateChaImageOrder;
                }
                
                // 파일을 input에 추가
                addFilesToInput(document.getElementById(targetInputId), [file]);
                
                // 미리보기 생성
                createPreviews([file], targetPreviewId, updateOrderFunc);
            }
        }
    });
});

// 캐릭터 이미지 순서 업데이트 함수
function updateChaImageOrder() {
    let order = [];
    let urlList = [];
    
    $('#cha-preview .preview-item').each(function() {
        const id = $(this).data('id');
        const url = $(this).data('url');
        
        if (id) {
            order.push(id);
        } else if (url && $(this).hasClass('url-item')) {
            urlList.push(url);
        }
    });
    
    $('#cha-image-order').val(JSON.stringify(order));
    $('#cha-image-urls').val(JSON.stringify(urlList));
}

// 일반 이미지 순서 업데이트 함수
function updateNormalImageOrder() {
    let order = [];
    let urlList = [];

    $('#normal-preview .preview-item').each(function() {
        const id = $(this).data('id');
        const url = $(this).data('url');

        if (id) {
            order.push(id);
        } else if (url && $(this).hasClass('url-item')) {
            urlList.push(url);
        }
    });

    $('#normal-image-order').val(JSON.stringify(order));
    $('#normal-image-urls').val(JSON.stringify(urlList));
}

// ==================== 폼 제출 시 파일 및 URL 이미지 데이터 처리 ====================
// 전역 파일 배열 참조 (window 객체 사용)
$(document).on('submit', '#fwrite', function(e) {

    // ★ 파일 배열을 input에 설정 (DataTransfer 사용)
    if (typeof DataTransfer !== 'undefined') {
        // 캐릭터 이미지
        if (window.chaFilesArray && window.chaFilesArray.length > 0) {
            const dtCha = new DataTransfer();
            window.chaFilesArray.forEach(file => dtCha.items.add(file));

            const chaInput = document.getElementById('trpg_cha_image');
            if (chaInput) {
                chaInput.files = dtCha.files;
            }
        }

        // 일반 이미지
        if (window.normalFilesArray && window.normalFilesArray.length > 0) {
            const dtNormal = new DataTransfer();
            window.normalFilesArray.forEach(file => dtNormal.items.add(file));

            const normalInput = document.getElementById('trpg_normal_image');
            if (normalInput) {
                normalInput.files = dtNormal.files;
            }
        }
    }

    // URL 이미지를 서버가 기대하는 형식으로 변환 (JSON 배열 → 쉼표 구분 문자열)

    // 캐릭터 이미지 URL
    try {
        let chaUrls = JSON.parse($('#cha-image-urls').val() || '[]');
        if (chaUrls.length > 0) {
            if ($('input[name="trpg_cha_url_images"]').length === 0) {
                $('<input type="hidden" name="trpg_cha_url_images">').appendTo('#fwrite');
            }
            $('input[name="trpg_cha_url_images"]').val(chaUrls.join(','));
        }
    } catch(err) {
        console.error('캐릭터 URL 이미지 변환 오류:', err);
    }

    // 일반 이미지 URL
    try {
        let normalUrls = JSON.parse($('#normal-image-urls').val() || '[]');
        if (normalUrls.length > 0) {
            if ($('input[name="trpg_normal_url_images"]').length === 0) {
                $('<input type="hidden" name="trpg_normal_url_images">').appendTo('#fwrite');
            }
            $('input[name="trpg_normal_url_images"]').val(normalUrls.join(','));
        }
    } catch(err) {
        console.error('일반 URL 이미지 변환 오류:', err);
    }
});

</script>
