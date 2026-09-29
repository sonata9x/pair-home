<?php
if (!defined('_GNUBOARD_')) exit;

/**
 * RA0 Editor
 * 치환자 호환 경량 에디터
 */

function editor_html($id, $content, $is_dhtml_editor = true)
{
    global $g5, $config, $w, $board;

    $editor_url = G5_EDITOR_URL . '/ra0_editor';
    $content_css_url = G5_CSS_URL . '/ra0-content.css';
    $editor_css_ver = defined('G5_CSS_VER') ? G5_CSS_VER : '1';
    $editor_js_ver = defined('G5_JS_VER') ? G5_JS_VER : '1';
    $content_height = isset($config['cf_editor_height']) ? $config['cf_editor_height'] : '400px';

    // 폰트 목록 가져오기
    $fonts = [];
    if (function_exists('get_font_options_for_select')) {
        $fonts = get_font_options_for_select();
    }

    // 슬래시 제거
    // $content = stripslashes($content);

    $html = <<<EOD
    <div class="ra0-editor-wrapper" id="ra0_editor_{$id}">
        <!-- 도구 모음 -->
        <div class="ra0-editor-toolbar">
            <!-- 폰트 -->
EOD;

    if (!empty($fonts)) {
        $html .= <<<EOD
            <div class="toolbar-group">
                <div class="color-picker-wrapper">
                    <button type="button" class="toolbar-btn toolbar-btn-wide color-btn" data-action="fontMenu" title="폰트">
                        <i class="fas fa-font"></i>
                        <span class="btn-text">폰트</span>
                        <i class="fas fa-caret-down"></i>
                    </button>
                    <div class="color-palette dropdown-menu font-menu" style="grid-template-columns: 1fr; width: 180px; max-height: 300px; overflow-y: auto;">
EOD;
        foreach ($fonts as $family => $name) {
            $family_escaped = htmlspecialchars($family);
            $name_escaped = htmlspecialchars($name);
            $html .= <<<EOD
                        <button type="button" data-action="setFont" data-font-family="{$family_escaped}" class="dropdown-item font-item">
                            <div class="font-preview" style="font-family: {$family_escaped};">{$name_escaped}</div>
                        </button>
EOD;
        }
        $html .= <<<EOD
                    </div>
                </div>
            </div>
EOD;
    }

    $html .= <<<EOD

            <!-- 문단 -->
            <div class="toolbar-group">
                <div class="color-picker-wrapper">
                    <button type="button" class="toolbar-btn toolbar-btn-wide color-btn" data-action="paragraphMenu" title="문단 서식">
                        <i class="fas fa-heading"></i>
                        <span class="btn-text">문단</span>
                        <i class="fas fa-caret-down"></i>
                    </button>
                    <div class="color-palette dropdown-menu paragraph-format-menu" style="grid-template-columns: 1fr; width: 220px;">
                        <button type="button" data-action="formatH2" class="dropdown-item"><b>H1</b><span class="paragraph-preview paragraph-preview-h1">큰 제목</span></button>
                        <button type="button" data-action="formatH3" class="dropdown-item"><b>H2</b><span class="paragraph-preview paragraph-preview-h2">중간 제목</span></button>
                        <button type="button" data-action="formatH4" class="dropdown-item"><b>H3</b><span class="paragraph-preview paragraph-preview-h3">작은 제목</span></button>
                        <button type="button" data-action="smallText" class="dropdown-item"><b>P</b><span class="paragraph-preview paragraph-preview-small">작은 글씨</span></button>
                    </div>
                </div>
            </div>

            <!-- 텍스트 스타일 -->
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-cmd="bold" title="굵게">
                    <i class="fas fa-bold"></i>
                </button>
                <button type="button" class="toolbar-btn" data-cmd="italic" title="기울임">
                    <i class="fas fa-italic"></i>
                </button>
                <button type="button" class="toolbar-btn" data-cmd="underline" title="밑줄">
                    <i class="fas fa-underline"></i>
                </button>
                <button type="button" class="toolbar-btn" data-cmd="strikeThrough" title="취소선">
                    <i class="fas fa-strikethrough"></i>
                </button>
            </div>

            <!-- 정렬 -->
            <div class="toolbar-group">
                <div class="color-picker-wrapper">
                    <button type="button" class="toolbar-btn color-btn" data-action="alignMenu" title="정렬">
                        <i class="fas fa-align-left"></i>
                        <i class="fas fa-caret-down"></i>
                    </button>
                    <div class="color-palette dropdown-menu align-menu" style="grid-template-columns: 1fr; width: 150px;">
                        <button type="button" data-action="alignLeft" class="dropdown-item"><i class="fas fa-align-left"></i><span>왼쪽 정렬</span></button>
                        <button type="button" data-action="alignCenter" class="dropdown-item"><i class="fas fa-align-center"></i><span>가운데 정렬</span></button>
                        <button type="button" data-action="alignRight" class="dropdown-item"><i class="fas fa-align-right"></i><span>오른쪽 정렬</span></button>
                        <button type="button" data-action="alignFull" class="dropdown-item"><i class="fas fa-align-justify"></i><span>양쪽 정렬</span></button>
                    </div>
                </div>
            </div>

            <!-- 리스트 -->
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-action="autoIndent" title="자동 들여쓰기 (문단 첫 글자)">
                    <i class="fas fa-indent"></i>
                </button>
                <button type="button" class="toolbar-btn" data-cmd="insertUnorderedList" title="글머리 기호">
                    <i class="fas fa-list-ul"></i>
                </button>
                <button type="button" class="toolbar-btn" data-cmd="insertOrderedList" title="번호 매기기">
                    <i class="fas fa-list-ol"></i>
                </button>
            </div>

            <!-- 서식 지우기 -->
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-action="removeStyles" title="서식 지우기">
                    <i class="fas fa-eraser"></i>
                </button>
                <button type="button" class="toolbar-btn" data-action="cleanupTags" title="태그 정리 (p 태그로 감싸기)">
                    <i class="fas fa-broom"></i>
                </button>
            </div>

            <!-- 색상 -->
            <div class="toolbar-group">
                <div class="color-picker-wrapper">
                    <button type="button" class="toolbar-btn color-btn" data-action="textColor" title="글자색">
                        <i class="fa-solid fa-palette" style="color:#6366f1;"></i>
                    </button>
                    <div class="color-palette" style="grid-template-columns: repeat(5, 24px);">
                        <span data-color="#A60303" style="background:#A60303"></span>
                        <span data-color="#D95829" style="background:#D95829"></span>
                        <span data-color="#D9BB29" style="background:#D9BB29"></span>
                        <span data-color="#308C50" style="background:#308C50"></span>
                        <span data-color="#0554F2" style="background:#0554F2"></span>
                        <span data-color="#8612A6" style="background:#8612A6"></span>
                        <span data-color="#8C8681" style="background:#8C8681"></span>
                        <span data-color="#595652" style="background:#595652"></span>
                        <span data-color="#262524" style="background:#262524"></span>
                        <span data-color="#000000" style="background:#000000"></span>
                    </div>
                </div>
                <div class="color-picker-wrapper">
                    <button type="button" class="toolbar-btn color-btn" data-action="bgColor" title="배경색">
                        <span style="position:relative; display:inline-block;">
                            <i class="fa-solid fa-fill-drip" style="color:#333;"></i>
                            <span style="position:absolute; bottom:0; left:0; right:0; height:6px; background:#FFF4CC; border-radius:2px;"></span>
                        </span>
                    </button>
                    <div class="color-palette" style="grid-template-columns: repeat(5, 24px);">
                        <span data-color="transparent" style="background:#FFFFFF;border:1px solid #ddd">×</span>
                        <span data-color="#FFE5E5" style="background:#FFE5E5"></span>
                        <span data-color="#FFEADD" style="background:#FFEADD"></span>
                        <span data-color="#FFF4CC" style="background:#FFF4CC"></span>
                        <span data-color="#E3F5E9" style="background:#E3F5E9"></span>
                        <span data-color="#E5F0FF" style="background:#E5F0FF"></span>
                        <span data-color="#F3E5F8" style="background:#F3E5F8"></span>
                        <span data-color="#F5F5F4" style="background:#F5F5F4"></span>
                        <span data-color="#E8E8E7" style="background:#E8E8E7"></span>
                        <span data-color="#D9D9D8" style="background:#D9D9D8"></span>
                    </div>
                </div>
            </div>

            <!-- 특수 기능 -->
            <div class="toolbar-group">
                <div class="color-picker-wrapper">
                    <button type="button" class="toolbar-btn toolbar-btn-wide color-btn" data-action="caseTitleMenu" title="제목 서식">
                        <i class="fa-solid fa-star"></i>
                        <span class="btn-text">제목</span>
                        <i class="fas fa-caret-down"></i>
                    </button>
                    <div class="color-palette dropdown-menu case-format-menu" style="grid-template-columns: 1fr; width: 320px; max-height: 420px; overflow-y: auto;">
                        <button type="button" data-action="caseDoorGate" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>제목 + 부제</small></div>
                            <div class="case-mini-preview mini-door-gate"><i></i><b>제목</b><em>CASE 01 / 부제 영역</em></div>
                        </button>
                        <button type="button" data-action="caseDoorWindow" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>파일철</small></div>
                            <div class="case-mini-preview mini-door-window"><em>LABEL</em><b>제목</b></div>
                        </button>
                        <button type="button" data-action="caseDoorEpisode" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>회차 표식</small></div>
                            <div class="case-mini-preview mini-door-episode"><em>LABEL</em><b>제목</b><i></i></div>
                        </button>
                        <button type="button" data-action="caseDoorSignal" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>신호 헤더</small></div>
                            <div class="case-mini-preview mini-door-signal"><em>[ LABEL ]</em><b>제목</b></div>
                        </button>
                        <button type="button" data-action="caseDoorEvidence" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>증거 파일</small></div>
                            <div class="case-mini-preview mini-door-evidence"><em>LABEL 05</em><b>제목</b></div>
                        </button>
                        <button type="button" data-action="caseDoorQuiet" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>종결선</small></div>
                            <div class="case-mini-preview mini-door-quiet"><b>제목</b></div>
                        </button>
                        <button type="button" data-action="caseDoorStamp" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>도장</small></div>
                            <div class="case-mini-preview mini-door-stamp"><b>제목</b></div>
                        </button>
                        <button type="button" data-action="caseDoorLedger" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>장부 표식</small></div>
                            <div class="case-mini-preview mini-door-ledger"><i></i><b>제목</b></div>
                        </button>
                        <button type="button" data-action="caseDoorWitness" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>증언 파일</small></div>
                            <div class="case-mini-preview mini-door-witness"><em>TAG</em><b>제목</b><small>CASE 01 / 부제 영역</small></div>
                        </button>
                        <button type="button" data-action="caseDoorIndex" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>번호표</small></div>
                            <div class="case-mini-preview mini-door-index"><em>10</em><b>제목</b></div>
                        </button>
                    </div>
                </div>
                <div class="color-picker-wrapper">
                    <button type="button" class="toolbar-btn toolbar-btn-wide color-btn" data-action="caseSubtitleMenu" title="부제 서식">
                        <i class="fa-regular fa-star"></i>
                        <span class="btn-text">부제</span>
                        <i class="fas fa-caret-down"></i>
                    </button>
                    <div class="color-palette dropdown-menu case-format-menu" style="grid-template-columns: 1fr; width: 300px; max-height: 380px; overflow-y: auto;">
                        <button type="button" data-action="caseDecoMarker" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>점 표식</small></div>
                            <div class="case-mini-preview mini-deco-marker"><i></i><b>부제</b></div>
                        </button>
                        <button type="button" data-action="caseDecoRoute" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>동선</small></div>
                            <div class="case-mini-preview mini-deco-route"><i></i><b>부제</b></div>
                        </button>
                        <button type="button" data-action="caseDecoTab" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>진술 태그</small></div>
                            <div class="case-mini-preview mini-deco-tab"><em>STATEMENT</em><b>부제</b></div>
                        </button>
                        <button type="button" data-action="caseDecoSeal" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>확인 표식</small></div>
                            <div class="case-mini-preview mini-deco-seal"><b>부제</b></div>
                        </button>
                        <button type="button" data-action="caseDecoThread" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>연결 기록</small></div>
                            <div class="case-mini-preview mini-deco-thread"><em>#</em><b>부제</b><i></i></div>
                        </button>
                        <button type="button" data-action="caseDecoTime" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>시간</small></div>
                            <div class="case-mini-preview mini-deco-time"><em>20:26</em><b>부제</b></div>
                        </button>
                        <button type="button" data-action="caseDecoFlag" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>장면 깃발</small></div>
                            <div class="case-mini-preview mini-deco-flag"><em>SCENE</em><b>부제</b></div>
                        </button>
                        <button type="button" data-action="caseDecoDotline" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>구분선</small></div>
                            <div class="case-mini-preview mini-deco-dotline"><i></i><b>부제와 구분선</b><i></i></div>
                        </button>
                        <button type="button" data-action="caseDecoTrace" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>추적</small></div>
                            <div class="case-mini-preview mini-deco-trace"><em>TRACE</em><b>부제</b></div>
                        </button>
                    </div>
                </div>
                <div class="color-picker-wrapper">
                    <button type="button" class="toolbar-btn toolbar-btn-wide color-btn" data-action="caseDeviceMenu" title="요소 서식">
                        <i class="fa-solid fa-star-half-stroke"></i>
                        <span class="btn-text">요소</span>
                        <i class="fas fa-caret-down"></i>
                    </button>
                    <div class="color-palette dropdown-menu case-format-menu" style="grid-template-columns: 1fr; width: 300px; max-height: 340px; overflow-y: auto;">
                        <button type="button" data-action="spoiler" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>스포일러</small></div>
                            <div class="case-mini-preview mini-device-spoiler"><b>스포일러</b></div>
                        </button>
                        <button type="button" data-action="caseDeviceMark" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>형광 표시</small></div>
                            <div class="case-mini-preview mini-device-mark">본문 중간에 <b>형광 강조</b>로 표시</div>
                        </button>
                        <button type="button" data-action="caseDeviceWave" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>물결 밑줄</small></div>
                            <div class="case-mini-preview mini-device-wave">물결 밑줄</div>
                        </button>
                        <button type="button" data-action="caseDeviceWhisper" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>작은 톤</small></div>
                            <div class="case-mini-preview mini-device-whisper">속삭임</div>
                        </button>
                        <button type="button" data-action="caseDeviceMono" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>코드형 표식</small></div>
                            <div class="case-mini-preview mini-device-mono">CASE-01 코드라벨</div>
                        </button>
                        <button type="button" data-action="caseDeviceDot" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>양쪽 점</small></div>
                            <div class="case-mini-preview mini-device-dot">양쪽 점</div>
                        </button>
                        <button type="button" data-action="caseDeviceGlow" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>발광 강조</small></div>
                            <div class="case-mini-preview mini-device-glow">발광 강조</div>
                        </button>
                    </div>
                </div>
                <div class="color-picker-wrapper">
                    <button type="button" class="toolbar-btn toolbar-btn-wide color-btn" data-action="quoteMenu" title="블록 서식">
                        <i class="fas fa-quote-left"></i>
                        <span class="btn-text">블록</span>
                        <i class="fas fa-caret-down"></i>
                    </button>
                    <div class="color-palette dropdown-menu case-format-menu quote-format-menu" style="grid-template-columns: 1fr; width: 340px; max-height: 460px; overflow-y: auto;">
                        <button type="button" data-action="quoteType1" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>큰따옴표 박스</small></div>
                            <div class="case-mini-preview mini-quote mini-quote-type1"><b>&ldquo;</b><i></i></div>
                        </button>
                        <button type="button" data-action="quoteType2" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>겹낫표 박스</small></div>
                            <div class="case-mini-preview mini-quote mini-quote-type2"><b>『</b><i></i><b>』</b></div>
                        </button>
                        <button type="button" data-action="quoteType3" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>낫표 박스</small></div>
                            <div class="case-mini-preview mini-quote mini-quote-type3"><b>「</b><i></i><b>」</b></div>
                        </button>
                        <button type="button" data-action="quoteBlock" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>기본 인용</small></div>
                            <div class="case-mini-preview mini-quote mini-quote-line"><span>내용</span></div>
                        </button>
                        <button type="button" data-action="caseDeviceMemo" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>포스트잇</small></div>
                            <div class="case-mini-preview mini-block-memo">메모, 주석</div>
                        </button>
                        <button type="button" data-action="caseDeviceLog" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>기록</small></div>
                            <div class="case-mini-preview mini-block-log"><em>제목</em><b>내용</b></div>
                        </button>
                        <button type="button" data-action="caseDeviceLogError" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>오류 로그</small></div>
                            <div class="case-mini-preview mini-block-log mini-block-error"><em>제목</em><b>내용</b></div>
                        </button>
                        <button type="button" data-action="caseDeviceLogSuccess" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>완료 로그</small></div>
                            <div class="case-mini-preview mini-block-log mini-block-success"><em>제목</em><b>내용</b></div>
                        </button>
                        <button type="button" data-action="caseDeviceSystem" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>시스템</small></div>
                            <div class="case-mini-preview mini-block-system">SYSTEM / 내용</div>
                        </button>
                        <button type="button" data-action="caseDeviceAlert" class="dropdown-item format-preview-item">
                            <div class="format-item-head"><small>경고</small></div>
                            <div class="case-mini-preview mini-block-alert"><em>!</em><b>내용</b></div>
                        </button>
                    </div>
                </div>
                <div class="color-picker-wrapper">
                    <button type="button" class="toolbar-btn color-btn" data-action="hrMenu" title="구분선">
                        <i class="fas fa-minus"></i>
                        <i class="fas fa-caret-down"></i>
                    </button>
                    <div class="color-palette dropdown-menu" style="grid-template-columns: 1fr; width: 80px;">
                        <button type="button" data-action="hrFull" class="dropdown-item">
                            <div class="hr-preview hr-full-preview"></div>
                        </button>
                        <button type="button" data-action="hrShort" class="dropdown-item">
                            <div class="hr-preview hr-short-preview"></div>
                        </button>
                        <button type="button" data-action="hrDotFull" class="dropdown-item">
                            <div class="hr-preview hr-dot-full-preview"></div>
                        </button>
                        <button type="button" data-action="hrDotShort" class="dropdown-item">
                            <div class="hr-preview hr-dot-short-preview"></div>
                        </button>
                    </div>
                </div>
                <button type="button" class="toolbar-btn" data-action="fold" title="접기">
                    <i class="fas fa-caret-square-down"></i>
                </button>
                <button type="button" class="toolbar-btn" data-action="codeBlock" title="코드 블록">
                    <i class="fas fa-file-code"></i>
                </button>
            </div>

            <!-- 삽입 -->
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" data-action="link" title="링크">
                    <i class="fas fa-link"></i>
                </button>
                <button type="button" class="toolbar-btn btn-tile-wrap" data-action="tileWrap" title="타일로 감싸기">
                    <i class="fas fa-th"></i>
                </button>
                <button type="button" class="toolbar-btn" data-action="emoticon" title="이모티콘">
                    <i class="fas fa-smile"></i>
                </button>
            </div>

            <!-- 유틸리티 -->
            <div class="toolbar-group">
                <button type="button" class="toolbar-btn" id="source-toggle_{$id}" title="HTML 소스 보기">
                    <i class="fas fa-code"></i>
                </button>
            </div>
        </div>

        <!-- 편집 영역 (뷰어 모드) -->
        <div class="ra0-editor-viewer ra0-content"
             id="{$id}_viewer"
             contenteditable="true"
             style="min-height:{$content_height}"></div>

        <!-- HTML 소스 (숨김) -->
        <textarea name="{$id}"
                  id="{$id}"
                  class="ra0-editor-source"
                  style="display:none;height:{$content_height}">{$content}</textarea>

        <!-- 하단 바: 도움말 + 글자 수 -->
        <div class="ra0-editor-footer">
            <div class="ra0-editor-help">
                <small><i class="fas fa-info-circle"></i> Ctrl+Z 되돌리기 · Ctrl+Y 다시실행 · Ctrl+K 링크 · <i class="fas fa-code"></i> HTML 소스</small>
            </div>
            <div class="ra0-editor-charcount">
                <small id="char_count_{$id}">0자 (공백 포함: 0자)</small>
            </div>
        </div>
    </div>

EOD;

    // CSS/JS는 한 번만 로드 (정적 변수로 체크)
    static $assets_loaded = false;
    if (!$assets_loaded) {
        $html .= <<<EOD
    <link rel="stylesheet" href="{$content_css_url}?ver={$editor_css_ver}">
    <link rel="stylesheet" href="{$editor_url}/editor.css?ver={$editor_css_ver}">
    <!-- Prism.js는 head.sub.php에서 공통 로드 -->
    <script src="{$editor_url}/editor.js?ver={$editor_js_ver}"></script>

    <!-- 코드 블록 삽입 모달 (페이지당 한 번만 로드) -->
    <div id="code-block-modal" class="code-block-modal" style="display:none;">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>코드 블록 삽입</h3>
                <button type="button" class="btn-close" onclick="RA0Editor.closeCodeBlockModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="code-language">언어 선택</label>
                    <select id="code-language" class="form-control">
                        <option value="markup">HTML</option>
                        <option value="css">CSS</option>
                        <option value="javascript" selected>JavaScript</option>
                        <option value="php">PHP</option>
                        <option value="python">Python</option>
                        <option value="java">Java</option>
                        <option value="c">C</option>
                        <option value="cpp">C++</option>
                        <option value="csharp">C#</option>
                        <option value="ruby">Ruby</option>
                        <option value="go">Go</option>
                        <option value="rust">Rust</option>
                        <option value="sql">SQL</option>
                        <option value="bash">Bash</option>
                        <option value="json">JSON</option>
                        <option value="yaml">YAML</option>
                        <option value="markdown">Markdown</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="code-content">코드 입력</label>
                    <textarea id="code-content" class="form-control" rows="10" placeholder="여기에 코드를 입력하세요..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <span class="keyboard-hint">Ctrl+Enter로 빠른 삽입 | ESC로 닫기</span>
                <div class="modal-buttons">
                    <button type="button" class="btn-secondary" onclick="RA0Editor.closeCodeBlockModal()">취소</button>
                    <button type="button" class="btn-primary" onclick="RA0Editor.insertCodeBlockFromModal()">삽입</button>
                </div>
            </div>
        </div>
    </div>
EOD;
        $assets_loaded = true;
    }

    // 각 에디터마다 초기화는 개별적으로
    $html .= <<<EOD
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        RA0Editor.init('{$id}');
    });
    </script>
EOD;

    return $html;
}

// 에디터 자바스크립트 (폼 제출 시 필요)
function get_editor_js($id, $is_dhtml_editor = true)
{
    if ($is_dhtml_editor) {
        // 뷰어 내용을 textarea로 동기화 (ra0-content wrapper 추가)
        return "
        var ra0_viewer_{$id} = document.getElementById('{$id}_viewer');
        var ra0_source_{$id} = document.getElementById('{$id}');
        if (ra0_viewer_{$id} && ra0_source_{$id}) {
            var ra0_synced_{$id} = false;
            if (typeof RA0Editor !== 'undefined' && RA0Editor.instances && RA0Editor.instances['{$id}'] && typeof RA0Editor.syncToSource === 'function') {
                RA0Editor.syncToSource('{$id}');
                ra0_synced_{$id} = true;
            } else if (typeof RA0Editor !== 'undefined' && typeof RA0Editor.serializeViewerContent === 'function') {
                ra0_source_{$id}.value = RA0Editor.serializeViewerContent(ra0_viewer_{$id});
                ra0_synced_{$id} = true;
            }

            if (!ra0_synced_{$id}) {
            // <code> 태그 내용을 HTML 엔티티로 변환
            var clonedContent = ra0_viewer_{$id}.cloneNode(true);
            var codeTags = clonedContent.querySelectorAll('code');
            codeTags.forEach(function(code) {
                var text = code.textContent;
                code.textContent = ''; // 초기화
                code.innerHTML = text
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            });

            // contenteditable 속성 제거 (에디터 내부 요소용)
            clonedContent.querySelectorAll('[contenteditable]').forEach(function(el) {
                el.removeAttribute('contenteditable');
            });

            var content = '<div class=\"ra0-content\">' + clonedContent.innerHTML + '</div>';

            // ><! 패턴 이스케이프 (HTML 주석 오인 방지)
            content = content.replace(/><!/g, '>&lt;!');

            ra0_source_{$id}.value = content;
            }
        }
        ";
    }
    return "";
}

// textarea 값이 비어있는지 검사
function chk_editor_js($id, $is_dhtml_editor = true)
{
    if ($is_dhtml_editor) {
        return "
        var ra0_source_{$id} = document.getElementById('{$id}');
        if (ra0_source_{$id}) {
            var content = ra0_source_{$id}.value.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, '').trim();
            if (!content) {
                alert('내용을 입력해 주십시오.');
                return false;
            }
        }
        ";
    } else {
        return "
        var {$id}_editor = document.getElementById('{$id}');
        if (!{$id}_editor.value.trim()) {
            alert('내용을 입력해 주십시오.');
            {$id}_editor.focus();
            return false;
        }
        ";
    }
}

// 에디터 자동저장 스크립트
function editor_get_autosave_script($id, $is_dhtml_editor = true)
{
    return "";
}
?>
