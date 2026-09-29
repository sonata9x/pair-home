
<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php'); // 썸네일 함수 포함

// 제목 길이 설정 (게시판 설정에서 가져오거나 기본값 사용)
$subject_len = isset($board['bo_subject_len']) ? $board['bo_subject_len'] : 100;

// ★ RA0 Edition 기본 list.php의 $list 변수를 그대로 사용
// bbs/list.php에서 이미 wr_order 정렬과 페이지네이션이 처리되어 있음

add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.list.css?v='.filemtime($board_skin_path.'/style.list.css').'">', 0);

// 세션카드 이미지 관련 설정
$trpg_up_table = G5_TABLE_PREFIX . 'trpg_up';
// 버전관리
$upskin_version = 'v260129_0';
$upskin_id = 'TRPG_up';

?>

<?php
$upcategory = '';
if ($board['bo_use_category']) {
    $is_category = true;
    $category_href = G5_BBS_URL.'/board.php?bo_table='.$bo_table;

    $upcategory .= '<li><a href="'.$category_href.'" class="upcate';
    if ($sca=='') {
        $upcategory .= ' point'; // 선택된 경우 point 클래스 추가
        $upcategory .= '" id="up_cate_on"';
    }
    $upcategory .= '">ALL</a></li>';

    $categories = explode('|', $board['bo_category_list']); // 구분자가 , 로 되어 있음
    for ($i=0; $i<count($categories); $i++) {
        $category = trim($categories[$i]);
        if ($category=='') continue;
        $upcategory .= '<li><a href="'.($category_href."&amp;sca=".urlencode($category)).'" class="upcate';
        if ($category==$sca) { // 현재 선택된 카테고리라면
            $upcategory .= ' point';
            $upcategory .= '" id="up_cate_on"';
        } else {
            $upcategory .= '"';
        }
        $upcategory .= '>'.$category.'</a></li>';
    }
}
?>

<!-- 게시판 시작 { -->
<div class="trpg-board-container">
<hr class="padding">
<?php if($board['bo_content_head']) { ?>
	<div class="board-notice">
		<?php stripslashes($board['bo_content_head']);?>
	</div><hr class="padding" />
<?php } ?>

<div class="trpg-guide">
    <div class="trpg-sch">
        <!-- 게시판 검색 시작 { -->
        <fieldset id="bo_sch" class="txt-center">
            <legend>게시물 검색</legend>

            <form name="fsearch" method="get" style="display: flex; gap: 3px;">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
            <input type="hidden" name="sca" value="<?php echo $sca ?>">
            <input type="hidden" name="sop" value="and">
            <select name="sfl" id="sfl" class="trpg-sch-select">
                <option value="wr_subject"<?php echo get_selected($sfl, 'wr_subject', true); ?>>제목</option>
            </select>
            <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx" class="trpg-sch-input" size="15" maxlength="20">
            <button type="submit" class="trpg-btn ui sch"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
        </fieldset>
    <!-- } 게시판 검색 끝 -->
    </div>
    <div class="trpg-ui-btn">
    <?php if ($list_href) { ?><a href="<?php echo $list_href ?>" class="trpg-btn ui list"><i class="fa-solid fa-list"></i></a><?php } ?>
    <?php if ($write_href) { ?><a href="<?php echo $write_href ?>" class="trpg-btn ui write"><i class="fa-solid fa-pen-to-square"></i></a><?php } ?>
    <?php if ($is_admin) { ?><button type="button" id="edit-order-btn" class="trpg-btn ui edit"><i class="fa-solid fa-sort"></i></button><?php } ?>
    <?php if($admin_href){?><div class="trpg-btn admin"><a href="<?php echo $admin_href?>" target="_blank"><i class="fa-solid fa-gear"></i></a></div><?php } ?>
    </div>
</div>

<!-- 게시판 카테고리 시작 -->
<?php if ($is_category) { ?>
<nav class="trpg-board-category">
	<ul class="upcategories">
		<?php echo $upcategory ?>
	</ul>
</nav>
<?php } ?>
<!-- 게시판 카테고리 끝 -->

<form name="fboardlist" id="fboardlist" action="./board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
<input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="spt" value="<?php echo $spt ?>">
<input type="hidden" name="sca" value="<?php echo $sca ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="sw" value="">

<!-- 티켓형 리스트 영역 시작 -->
<div class="trpg-ticket-list" id="sortable">
    <?php for ($i=0; $i<count($list); $i++) { ?>
        <?php
        // 바코드 생성 (전체 폭을 채우는 42자리 숫자)
        $barcode = '';
        for ($j = 0; $j < 42; $j++) {
            $barcode .= mt_rand(0, 9);
        }
        
        // 세션 완료 여부 확인
        $is_completed = ($list[$i]['wr_wide'] == '1') ? true : false;
        
        // 스탬프는 위치를 고정하고 회전 각도만 조금씩 다르게 표시한다.
        if ($is_completed) {
            $stamp_rotate = mt_rand(-8, 8);
        }
        
        // 세션 카드 이미지 가져오기
        $card_image = '';
        $sql = "SELECT * FROM {$trpg_up_table} 
                WHERE bo_table = '{$bo_table}' 
                AND wr_id = '{$list[$i]['wr_id']}' 
                AND image_type = 'card' 
                AND img_use = '1'
                LIMIT 1";
        $card_result = sql_query($sql);
        $card_row = sql_fetch_array($card_result);
        if ($card_row) {
            $card_image = $card_row['image_url'];
        }

        // get_list()의 href에는 &amp;가 포함될 수 있으므로 출력 전에 실제 URL로 한 번 복원한다.
        $ticket_href = !empty($list[$i]['href'])
            ? html_entity_decode($list[$i]['href'], ENT_QUOTES, 'UTF-8')
            : G5_BBS_URL . '/board.php?bo_table=' . rawurlencode($bo_table) . '&wr_id=' . (int) $list[$i]['wr_id'];
        $rule_name = trim((string)(isset($list[$i]['ca_name']) ? $list[$i]['ca_name'] : ''));
        $subject = trim((string)(isset($list[$i]['wr_subject']) ? $list[$i]['wr_subject'] : ''));
        $subtitle = trim((string)(isset($list[$i]['wr_title']) ? $list[$i]['wr_title'] : ''));
        $english_title = trim((string)(isset($list[$i]['wr_english_title']) ? $list[$i]['wr_english_title'] : ''));
        $catchphrase = trim((string)(isset($list[$i]['wr_catchphrase']) ? $list[$i]['wr_catchphrase'] : ''));
        $playtime = trim((string)(isset($list[$i]['wr_playtime']) ? $list[$i]['wr_playtime'] : ''));
        $date = trim((string)(isset($list[$i]['wr_7']) ? $list[$i]['wr_7'] : ''));
        $kpc = trim((string)(isset($list[$i]['wr_kpc']) ? $list[$i]['wr_kpc'] : ''));
        $pc = trim((string)(isset($list[$i]['wr_pc']) ? $list[$i]['wr_pc'] : ''));

        $sanitize_ticket_font = function ($value) {
            return trim((string)preg_replace('/[^\p{L}\p{N}\s,_-]/u', '', (string)$value));
        };
        $sanitize_ticket_color = function ($value) {
            $value = trim((string)$value);
            return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? strtoupper($value) : '';
        };
        $subject_font = $sanitize_ticket_font(isset($list[$i]['wr_subject_font']) ? $list[$i]['wr_subject_font'] : '');
        if ($subject_font === '') {
            $subject_font = $sanitize_ticket_font(isset($list[$i]['wr_8']) ? $list[$i]['wr_8'] : '');
        }
        if ($subject_font === '') {
            $subject_font = 'Pretendard';
        }
        $english_title_font = $sanitize_ticket_font(isset($list[$i]['wr_english_title_font']) ? $list[$i]['wr_english_title_font'] : '');
        $subtitle_font = $sanitize_ticket_font(isset($list[$i]['wr_subtitle_font']) ? $list[$i]['wr_subtitle_font'] : '');
        $catchphrase_font = $sanitize_ticket_font(isset($list[$i]['wr_catchphrase_font']) ? $list[$i]['wr_catchphrase_font'] : '');
        $subject_color = $sanitize_ticket_color(isset($list[$i]['wr_subject_color']) ? $list[$i]['wr_subject_color'] : '');
        $english_title_color = $sanitize_ticket_color(isset($list[$i]['wr_english_title_color']) ? $list[$i]['wr_english_title_color'] : '');
        $subtitle_color = $sanitize_ticket_color(isset($list[$i]['wr_subtitle_color']) ? $list[$i]['wr_subtitle_color'] : '');
        $catchphrase_color = $sanitize_ticket_color(isset($list[$i]['wr_catchphrase_color']) ? $list[$i]['wr_catchphrase_color'] : '');
        $subject_style = "font-family:'".htmlspecialchars($subject_font, ENT_QUOTES)."',sans-serif;";
        $english_title_style = $english_title_font !== '' ? "font-family:'".htmlspecialchars($english_title_font, ENT_QUOTES)."',serif;" : '';
        $subtitle_style = $subtitle_font !== '' ? "font-family:'".htmlspecialchars($subtitle_font, ENT_QUOTES)."',sans-serif;" : '';
        $catchphrase_style = $catchphrase_font !== '' ? "font-family:'".htmlspecialchars($catchphrase_font, ENT_QUOTES)."',sans-serif;" : '';
        if ($subject_color !== '') $subject_style .= 'color:'.$subject_color.';';
        if ($english_title_color !== '') $english_title_style .= 'color:'.$english_title_color.';';
        if ($subtitle_color !== '') $subtitle_style .= 'color:'.$subtitle_color.';';
        if ($catchphrase_color !== '') $catchphrase_style .= 'color:'.$catchphrase_color.';';

        // 인용 부호는 CSS/마크업에서 한 번만 출력한다.
        if ($catchphrase !== '') {
            $catchphrase = preg_replace('/^[\s"\'“”‘’]+|[\s"\'“”‘’]+$/u', '', $catchphrase);
        }

        $title_font = !empty($list[$i]['wr_8']) ? $list[$i]['wr_8'] : 'Pretendard';
        $title_font = preg_replace('/[^a-zA-Z0-9가-힣ㄱ-ㅎㅏ-ㅣ _-]/u', '', $title_font);
        if ($title_font === '') {
            $title_font = 'Pretendard';
        }

        $english_title_class = '';
        $english_title_length = function_exists('mb_strlen') ? mb_strlen($english_title, 'UTF-8') : strlen($english_title);
        if ($english_title_length > 30) {
            $english_title_class = ' is-very-long';
        } elseif ($english_title_length > 20) {
            $english_title_class = ' is-long';
        }
        ?>
        <div class="trpg-ticket-item" data-id="<?php echo (int)$list[$i]['wr_id']; ?>" data-href="<?php echo htmlspecialchars($ticket_href, ENT_QUOTES); ?>" onclick="location.href=this.dataset.href;" style="cursor: pointer;">
            <?php if ($is_checkbox) { ?>
                <div class="trpg-ticket-chk" onclick="event.stopPropagation();">
                    <input type="checkbox" name="chk_wr_id[]" value="<?php echo (int)$list[$i]['wr_id']; ?>" id="chk_wr_id_<?php echo $i; ?>">
                </div>
            <?php } ?>
            <div class="trpg-ticket-container">
                
                <!-- 세션 카드 이미지 -->
                <div class="trpg-ticket-image">
                    <?php if ($card_image !== '') { ?>
                    <img src="<?php echo htmlspecialchars($card_image, ENT_QUOTES); ?>" alt="" loading="lazy">
                    <?php } ?>
                </div>
                
                <!-- 티켓 정보 영역 -->
                <div class="trpg-ticket-info">
                    <svg class="trpg-ticket-frame" viewBox="0 0 280 210" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                        <path d="M16 0 H264 A16 16 0 0 0 280 16 V194 A16 16 0 0 0 264 210 H16 A16 16 0 0 0 0 194 V16 A16 16 0 0 0 16 0 Z" fill="none" stroke="currentColor" stroke-width="1" vector-effect="non-scaling-stroke"></path>
                    </svg>
                    <div class="trpg-info">
                        <?php if ($rule_name !== '') { ?>
                        <div class="trpg-ticket-rule"><?php echo htmlspecialchars($rule_name, ENT_QUOTES); ?></div>
                        <?php } ?>

                        <div class="trpg-ticket-heading">
                            <?php if ($english_title !== '') { ?>
                            <div class="trpg-ticket-title-en<?php echo $english_title_class; ?>" style="<?php echo $english_title_style; ?>" aria-hidden="true"><?php echo htmlspecialchars($english_title, ENT_QUOTES); ?></div>
                            <?php } ?>
                            <div class="trpg-ticket-title">
                                <a href="<?php echo htmlspecialchars($ticket_href, ENT_QUOTES); ?>" style="<?php echo $subject_style; ?>" onclick="event.stopPropagation();"><?php echo htmlspecialchars($subject, ENT_QUOTES); ?></a>
                            </div>
                        </div>

                        <?php if ($subtitle !== '') { ?>
                        <div class="trpg-ticket-subtitle" style="<?php echo $subtitle_style; ?>"><?php echo htmlspecialchars($subtitle, ENT_QUOTES); ?></div>
                        <?php } ?>

                        <?php if ($catchphrase !== '') { ?>
                        <blockquote class="trpg-ticket-quote" style="<?php echo $catchphrase_style; ?>">
                            <span class="quote-mark" style="<?php echo $catchphrase_color !== '' ? 'color:'.$catchphrase_color.';' : ''; ?>" aria-hidden="true">“</span>
                            <span class="quote-text"><?php echo htmlspecialchars($catchphrase, ENT_QUOTES); ?></span>
                        </blockquote>
                        <?php } ?>

                        <dl class="trpg-ticket-meta">
                            <?php if ($kpc !== '') { ?>
                            <dt>KPC</dt><dd><?php echo htmlspecialchars($kpc, ENT_QUOTES); ?></dd>
                            <?php } ?>
                            <?php if ($pc !== '') { ?>
                            <dt>PC</dt><dd><?php echo htmlspecialchars($pc, ENT_QUOTES); ?></dd>
                            <?php } ?>
                            <?php if ($date !== '') { ?>
                            <dt>DATE</dt><dd><?php echo htmlspecialchars($date, ENT_QUOTES); ?></dd>
                            <?php } ?>
                            <?php if ($playtime !== '') { ?>
                            <dt>PLAYTIME</dt><dd><?php echo htmlspecialchars($playtime, ENT_QUOTES); ?></dd>
                            <?php } ?>
                        </dl>
                    </div>
                    
                    <div class="trpg-ticket-barcode">
                        <div class="barcode-img">
                            <div class="barcode-lines"></div>
                        </div>
                        <div class="barcode-number"><?php echo $barcode; ?></div>
                    </div>

                    <?php if ($is_completed) { ?>
                        <div class="trpg-ticket-completed" style="transform: rotate(<?php echo (int)$stamp_rotate; ?>deg);">
                            <div class="stamp-image" style="background-image: url('<?php echo htmlspecialchars($board_skin_url, ENT_QUOTES); ?>/img/tr-stamp.png');"></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>
    
    <?php if (count($list) == 0) { ?>
        <div class="empty-list">게시물이 없습니다.</div>
    <?php } ?>
</div>
<!-- 티켓형 리스트 영역 끝 -->

<?php if ($is_checkbox) { ?>
<div class="trpg-chk-option">
        <div class="chk_all">
            <input type="checkbox" id="chkall" onclick="if (this.checked) all_checked(true); else all_checked(false);">
        </div> 
	<button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value" class="select-trash-btn"><i class="fa-solid fa-trash"></i></button>
</div>
<?php } ?>

</form>
	
<!-- 페이지 -->
<?php echo $write_pages; ?>

</div>

<script src="<?php echo G5_JS_URL; ?>/board_order.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // RA0 Edition 통합 정렬 시스템 초기화
    <?php if ($is_admin) { ?>
    // 페이지 오프셋 계산 (페이지네이션 고려)
    var page = <?php echo $page ? $page : 1; ?>;
    var pageRows = <?php echo $board['bo_page_rows'] ? $board['bo_page_rows'] : 15; ?>;
    var pageOffset = (page - 1) * pageRows;

    // board_order.js의 initBoardOrder 함수를 커스터마이즈하여 페이지 오프셋 적용
    var $sortable = $("#sortable");
    var $btn = $("#edit-order-btn");
    var editing = false;

    // sortable 초기화
    $sortable.sortable({
        placeholder: "sortable-placeholder",
        forcePlaceholderSize: true,
        disabled: true
    });

    // 정렬 버튼 클릭 이벤트
    $btn.on("click", function() {
        if (!editing) {
            // 편집 시작
            $sortable.sortable("enable");
            $(this).html('<i class="fa-solid fa-floppy-disk"></i>').addClass("editing");
            editing = true;
            $sortable.addClass("editing");
        } else {
            // 편집 종료 및 저장
            $sortable.sortable("disable");

            var sortedIDs = $sortable.sortable("toArray", { attribute: "data-id" });

            // 페이지 오프셋을 고려한 순서 배열 생성
            var orderedData = [];
            for (var i = 0; i < sortedIDs.length; i++) {
                orderedData.push({
                    wr_id: sortedIDs[i],
                    wr_order: pageOffset + i
                });
            }

            // AJAX로 순서 저장 (vanilla JS XMLHttpRequest 사용)
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo G5_BBS_URL; ?>/update_order.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            alert(response.message || "정렬 순서가 저장되었습니다.");
                            setTimeout(function() {
                                location.reload();
                            }, 500);
                        } else {
                            alert(response.message || "정렬 저장에 실패했습니다.");
                        }
                    } catch (e) {
                        console.error('응답 파싱 오류:', e);
                        alert('순서 저장 중 오류가 발생했습니다.');
                    }
                } else {
                    console.error('HTTP 오류:', xhr.status);
                    alert('순서 저장 중 네트워크 오류가 발생했습니다.');
                }
            };

            xhr.onerror = function() {
                console.error('네트워크 오류');
                alert('순서 저장 중 네트워크 오류가 발생했습니다.');
            };

            // order_data 배열을 PHP 배열 형식으로 전송
            var postData = 'bo_table=<?php echo $bo_table; ?>';
            for (var i = 0; i < orderedData.length; i++) {
                postData += '&order_data[' + i + '][wr_id]=' + encodeURIComponent(orderedData[i].wr_id);
                postData += '&order_data[' + i + '][wr_order]=' + encodeURIComponent(orderedData[i].wr_order);
            }
            xhr.send(postData);

            $(this).html('<i class="fa-solid fa-sort"></i>').removeClass("editing");
            editing = false;
            $sortable.removeClass("editing");
        }
    });
    <?php } ?>

    // 바코드 번호에 홀로그램 효과 적용
    const barcodeNumbers = document.querySelectorAll('.barcode-number');
    
    barcodeNumbers.forEach(function(element) {
        const text = element.textContent || element.innerText;
        if (text) {
            // 각 문자를 개별 span으로 감싸서 홀로그램 효과 적용
            let formattedText = '';
            for (let i = 0; i < text.length; i++) {
                formattedText += `<span class="hologram-text">${text[i]}</span>`;
            }
            element.innerHTML = formattedText;
        }
    });
    
    // 바코드 라인 생성
    const barcodeImgs = document.querySelectorAll('.barcode-img');
    barcodeImgs.forEach(function(container) {
        const barcodeLines = container.querySelector('.barcode-lines');
        if (barcodeLines) {
            barcodeLines.innerHTML = ''; // 기존 내용 비우기
            
            // 컨테이너 너비 가져오기
            const containerWidth = container.offsetWidth;
            
            // 랜덤한 바코드 라인 생성 (너비를 채울 만큼 충분히)
            let totalWidth = 0;
            while (totalWidth < containerWidth) {
                const width = Math.random() * 3 + 1; // 1-4px 사이의 랜덤 너비
                const margin = Math.random() * 2 + 1; // 1-3px 사이의 랜덤 간격
                const line = document.createElement('div');
                line.className = 'barcode-line';
                line.style.width = `${width}px`;
                line.style.marginRight = `${margin}px`;
                // 홀로그램 효과는 CSS에서 .barcode-line 클래스로 정의됨 (style.list.css)

                barcodeLines.appendChild(line);
                totalWidth += width + margin;
            }
        }
    });

    // 모달
	$("#open-write-modal").click(function() {
		$("#trpg-write-modal").fadeIn(300);
	});

	// 모달 관련 기능 초기화
	function initModal() {
		// 모달 외부 클릭 시 닫기
		$(window).click(function(event) {
			if ($(event.target).is('#trpg-write-modal')) {
				$('#trpg-write-modal').hide();
			}
		});
	}

	// 페이지 로드 시 초기화
	$(document).ready(function() {
		initModal(); // 모달 초기화 함수 호출
	});
});
</script>


<?php if ($is_checkbox) { ?>
<script>
function all_checked(sw) {
	var f = document.fboardlist;

	for (var i=0; i<f.length; i++) {
		if (f.elements[i].name == "chk_wr_id[]")
			f.elements[i].checked = sw;
	}
}

function fboardlist_submit(f) {
	var chk_count = 0;

	for (var i=0; i<f.length; i++) {
		if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
			chk_count++;
	}

	if (!chk_count) {
		alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
		return false;
	}

	if(document.pressed == "선택복사") {
		select_copy("copy");
		return;
	}

	if(document.pressed == "선택이동") {
		select_copy("move");
		return;
	}

	if(document.pressed == "선택삭제") {
		if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다\n\n답변글이 있는 게시글을 선택하신 경우\n답변글도 선택하셔야 게시글이 삭제됩니다."))
			return false;

		f.removeAttribute("target");
		f.action = "./board_list_update.php";
	}

	return true;
}

// 선택한 게시물 복사 및 이동
function select_copy(sw) {
	var f = document.fboardlist;

	if (sw == "copy")
		str = "복사";
	else
		str = "이동";

	var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

	f.sw.value = sw;
	f.target = "move";
	f.action = "./move.php";
	f.submit();
}
</script>
<?php } ?>

</div>
<!-- } 게시판 목록 끝 -->
