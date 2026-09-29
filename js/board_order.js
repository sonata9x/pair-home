/**
 * RA0 Edition - 통합 게시판 순서 변경 JavaScript
 *
 * 모든 게시판에서 공통으로 사용하는 드래그 앤 드롭 순서 변경 기능
 *
 * 사용법:
 * <script src="/js/board_order.js"></script>
 * <script>
 * initBoardOrder("게시판ID");
 * </script>
 */

/**
 * 게시판 순서 변경 초기화
 *
 * @param {string} boardTable - 게시판 ID (bo_table)
 * @param {object} options - 옵션 객체
 * @param {string} options.sortableId - sortable 컨테이너 ID (기본값: "sortable")
 * @param {string} options.btnId - 정렬 버튼 ID (기본값: "edit-order-btn")
 * @param {string} options.containerClass - 편집 모드 시 추가할 클래스 (기본값: "editing")
 * @param {string} options.handle - 드래그 핸들 (기본값: null, 전체 아이템)
 * @param {number} options.pageOffset - 페이지 시작 인덱스 (기본값: 0, 예: 2페이지 5개씩이면 5)
 * @param {function} options.onSuccess - 성공 시 콜백
 * @param {function} options.onError - 실패 시 콜백
 */
function initBoardOrder(boardTable, options) {
    // 기본 옵션
    var defaults = {
        sortableId: "sortable",
        btnId: "edit-order-btn",
        containerClass: "editing",
        handle: null,
        pageOffset: 0,
        onSuccess: null,
        onError: null
    };

    // 옵션 병합
    var settings = Object.assign({}, defaults, options || {});

    var $sortable = $("#" + settings.sortableId);
    var $btn = $("#" + settings.btnId);

    if ($sortable.length === 0) {
        return;
    }

    if ($btn.length === 0) {
        return;
    }

    // sortable 설정
    var sortableOptions = {
        placeholder: "sortable-placeholder",
        forcePlaceholderSize: true,
        disabled: true, // 처음에는 비활성화
        update: function(event, ui) {
            // 드래그 후 순서 변경 시 호출 (자동 저장 옵션)
        }
    };

    // 핸들 옵션이 있으면 추가
    if (settings.handle) {
        sortableOptions.handle = settings.handle;
    }

    // sortable 초기화
    $sortable.sortable(sortableOptions);

    var editing = false;

    // 정렬 버튼 클릭 이벤트
    $btn.on("click", function() {
        if (!editing) {
            // 편집 시작
            $sortable.sortable("enable");
            $(this).text("저장").addClass(settings.containerClass);
            editing = true;
            $sortable.addClass(settings.containerClass);
        } else {
            // 편집 종료 및 저장
            $sortable.sortable("disable");

            var sortedIDs = $sortable.sortable("toArray", { attribute: "data-id" });

            // 페이지 오프셋을 적용한 order_data 생성
            var orderData = [];
            for (var idx = 0; idx < sortedIDs.length; idx++) {
                orderData.push({
                    wr_id: sortedIDs[idx],
                    wr_order: settings.pageOffset + idx
                });
            }

            // AJAX로 순서 저장
            $.ajax({
                url: g5_bbs_url + "/update_order.php",
                type: "POST",
                data: {
                    order_data: orderData,
                    bo_table: boardTable
                },
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        alert(response.message || "정렬 순서가 저장되었습니다.");

                        // 성공 콜백 실행
                        if (settings.onSuccess && typeof settings.onSuccess === 'function') {
                            settings.onSuccess(response);
                        }

                        // 페이지 새로고침 (정렬 반영)
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    } else {
                        alert(response.message || "정렬 저장에 실패했습니다.");

                        // 실패 콜백 실행
                        if (settings.onError && typeof settings.onError === 'function') {
                            settings.onError(response);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    alert("순서 저장 중 오류가 발생했습니다.");

                    // 실패 콜백 실행
                    if (settings.onError && typeof settings.onError === 'function') {
                        settings.onError({ message: error });
                    }
                }
            });

            $(this).text("정렬").removeClass(settings.containerClass);
            editing = false;
            $sortable.removeClass(settings.containerClass);
        }
    });
}

/**
 * 간단한 사용법을 위한 래퍼 함수
 * jQuery 없이 바닐라 JS만 사용 가능하도록 확장 가능
 */
if (typeof window.initBoardOrder === 'undefined') {
    window.initBoardOrder = initBoardOrder;
}
