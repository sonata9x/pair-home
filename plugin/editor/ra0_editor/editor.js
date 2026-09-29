/**
 * RA0 Editor
 * contenteditable 기반 뷰어 에디터
 */

// autosave.js 연동용 전역 함수
function get_editor_wr_content() {
    const viewer = document.getElementById('wr_content_viewer');
    const source = document.getElementById('wr_content');
    // 뷰어가 표시되어 있으면 뷰어 내용 반환
    if (viewer && viewer.style.display !== 'none') {
        if (typeof RA0Editor !== 'undefined' && typeof RA0Editor.serializeViewerContent === 'function') {
            return RA0Editor.serializeViewerContent(viewer);
        }
        return viewer.innerHTML;
    }
    // 소스 모드일 경우 textarea 값 반환
    return source ? source.value : '';
}

function put_editor_wr_content(content) {
    const viewer = document.getElementById('wr_content_viewer');
    const source = document.getElementById('wr_content');
    const cleanContent = (typeof RA0Editor !== 'undefined' && typeof RA0Editor.extractRa0Content === 'function')
        ? RA0Editor.extractRa0Content(content)
        : content;
    if (viewer) {
        viewer.innerHTML = cleanContent;
        viewer.dispatchEvent(new CustomEvent('ra0editor:content-loaded', {
            bubbles: true,
            detail: { sourceId: 'wr_content' }
        }));
    }
    if (source) {
        source.value = content;
    }
}

const RA0Editor = {
    instances: {},

    extractRa0Content: function(content) {
        if (!content) return '<p><br></p>';

        const temp = document.createElement('div');
        temp.innerHTML = content;
        const wrapper = temp.querySelector('.ra0-content');
        if (wrapper) {
            return wrapper.innerHTML;
        }

        return String(content).replace(/<!--[\s\S]*?-->/g, '');
    },

    init: function(id) {
        const viewer = document.getElementById(id + '_viewer');
        const source = document.getElementById(id);
        const sourceToggle = document.getElementById('source-toggle_' + id);

        if (!viewer || !source) return;

        this.instances[id] = {
            viewer: viewer,
            source: source,
            sourceMode: false,
            // Undo/Redo
            history: [],
            historyIndex: -1,
            historyTimer: null,
            isUndoRedo: false
        };

        // 초기 내용 로드 (ra0-content wrapper 제거)
        let content = source.value || '<p><br></p>';
        content = this.extractRa0Content(content);
        viewer.innerHTML = content;

        // Prism.js 하이라이팅 적용
        setTimeout(() => {
            if (window.Prism) {
                Prism.highlightAllUnder(viewer);
            }
        }, 10);

        // 이벤트 바인딩
        this.bindToolbar(id);
        this.bindSourceToggle(id, sourceToggle);
        this.bindViewerSync(id);
        this.bindColorPickers(id);
        this.bindFileUpload(id);

        // 자동 삽입되는 불필요한 span 스타일 정리
        this.bindStyleCleaner(id, viewer);

        // Undo/Redo 초기 상태 저장
        this.saveHistory(id);
    },

    // 브라우저가 자동 삽입하는 불필요한 span 스타일 제거
    bindStyleCleaner: function(id, viewer) {
        const self = this;

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        self.cleanUnwantedStyles(node);
                    }
                });
            });
        });

        observer.observe(viewer, {
            childList: true,
            subtree: true
        });
        // MutationObserver만 사용 (input 이벤트에서 전체 검사는 무거움)
    },

    cleanUnwantedStyles: function(element) {
        // font-family, font-size만 가진 span 제거 (브라우저 자동 삽입된 것만)
        const spans = element.querySelectorAll('span[style]');
        spans.forEach((span) => {
            // data-ra0-font 속성이 있으면 사용자가 의도적으로 적용한 글꼴이므로 유지
            if (span.hasAttribute('data-ra0-font')) {
                return;
            }

            const style = (span.getAttribute('style') || '').toLowerCase();

            // color나 background가 있으면 유지 (의도적인 스타일)
            if (style.includes('color') || style.includes('background')) {
                return;
            }

            // font-family나 font-size만 있는 span은 제거 (브라우저 자동 삽입)
            if (style.includes('font-family') || style.includes('font-size')) {
                const parent = span.parentNode;
                if (parent) {
                    while (span.firstChild) {
                        parent.insertBefore(span.firstChild, span);
                    }
                    parent.removeChild(span);
                }
            }
        });

        // div에 적용된 불필요한 font-family, font-size도 제거
        // 단, text-align, margin, padding 등 블록 스타일이 있으면 유지
        element.querySelectorAll('div[style], p[style]').forEach((el) => {
            const style = (el.getAttribute('style') || '').toLowerCase();

            // 유지해야 할 스타일이 있으면 font-family/font-size만 제거하고 나머지 유지
            const keepStyles = ['color', 'background', 'text-align', 'text-indent', 'margin', 'padding', 'direction', 'line-height'];
            const hasKeepStyle = keepStyles.some(s => style.includes(s));

            if (hasKeepStyle) {
                // 중요한 스타일이 있으면 font 관련만 제거
                el.style.removeProperty('font-family');
                el.style.removeProperty('font-size');
            } else {
                // 중요한 스타일이 없으면 font 관련 제거 후 빈 스타일 제거
                el.style.removeProperty('font-family');
                el.style.removeProperty('font-size');
                if (!el.getAttribute('style') || el.getAttribute('style').trim() === '') {
                    el.removeAttribute('style');
                }
            }
        });
    },

    getImagePlaceholderText: function(element) {
        if (!element) return '';

        if (element.matches && element.matches('img[data-placeholder]')) {
            return element.getAttribute('data-placeholder') || '';
        }

        const index = element.getAttribute('data-index');
        if (index === null || index === '') return '';

        const width = element.getAttribute('data-width');
        return width ? `{이미지:${index}-${width}}` : `{이미지:${index}}`;
    },

    serializeEditorDom: function(root) {
        const cloned = root.cloneNode(true);

        cloned.querySelectorAll('[contenteditable]').forEach(el => {
            el.removeAttribute('contenteditable');
        });

        cloned.querySelectorAll('.image-placeholder').forEach(placeholder => {
            const text = this.getImagePlaceholderText(placeholder);
            placeholder.replaceWith(document.createTextNode(text));
        });

        cloned.querySelectorAll('img[data-placeholder]').forEach(img => {
            const text = this.getImagePlaceholderText(img);
            img.replaceWith(document.createTextNode(text));
        });

        return cloned;
    },

    serializeViewerContent: function(viewer) {
        if (!viewer) return '<div class="ra0-content"></div>';
        const cloned = this.serializeEditorDom(viewer);
        let cleanedHtml = cloned.innerHTML.replace(/<!--[\s\S]*?-->/g, '');
        cleanedHtml = cleanedHtml.replace(/><!/g, '>&lt;!');
        return '<div class="ra0-content">' + cleanedHtml + '</div>';
    },

    sanitizePastedHtml: function(html) {
        const temp = document.createElement('div');
        temp.innerHTML = html;

        const wrapper = temp.querySelector('.ra0-content');
        const root = wrapper || temp;

        root.querySelectorAll('script, style, iframe, object, embed, link, meta, base, form, input, button, textarea, select, option').forEach(el => {
            el.remove();
        });

        const safeStyleProps = [
            'color', 'background-color', 'font-weight', 'font-style',
            'text-decoration', 'text-decoration-line', 'text-align',
            'text-indent', 'margin-left', 'line-height'
        ];
        const allowedDataAttrs = new Set(['data-placeholder', 'data-index', 'data-width', 'data-tile', 'data-ra0-font']);
        const uriAttrs = new Set(['href', 'src', 'xlink:href']);

        root.querySelectorAll('*').forEach(el => {
            Array.from(el.attributes).forEach(attr => {
                const name = attr.name.toLowerCase();
                const value = attr.value.trim();

                if (name === 'id' || name.startsWith('on') || name === 'srcdoc') {
                    el.removeAttribute(attr.name);
                    return;
                }

                if (name.startsWith('data-') && !allowedDataAttrs.has(name)) {
                    el.removeAttribute(attr.name);
                    return;
                }

                if (uriAttrs.has(name) && /^(javascript|vbscript|data):/i.test(value)) {
                    el.removeAttribute(attr.name);
                }
            });

            if (el.hasAttribute('style')) {
                const kept = {};
                safeStyleProps.forEach(prop => {
                    const val = el.style.getPropertyValue(prop);
                    if (!val || val === 'initial' || val === 'inherit' || val === 'normal') return;
                    if (/url\s*\(|expression\s*\(|javascript:|vbscript:|behavior\s*:/i.test(val)) return;
                    kept[prop] = val;
                });
                el.removeAttribute('style');
                Object.entries(kept).forEach(([prop, val]) => {
                    el.style.setProperty(prop, val);
                });
                if (!el.getAttribute('style') || !el.getAttribute('style').trim()) {
                    el.removeAttribute('style');
                }
            }
        });

        return root.innerHTML.replace(/<!--[\s\S]*?-->/g, '').replace(/><!/g, '>&lt;!');
    },

    bindToolbar: function(id) {
        const wrapper = document.getElementById('ra0_editor_' + id);
        if (!wrapper) return;

        const self = this;
        let savedRange = null;

        // 드롭다운 메뉴 아이템 mousedown (선택 영역 저장)
        wrapper.addEventListener('mousedown', (e) => {
            const dropdownItem = e.target.closest('.dropdown-item');
            if (dropdownItem) {
                e.preventDefault(); // 선택 영역 유지

                // 현재 선택 영역 저장
                const selection = window.getSelection();
                if (selection.rangeCount > 0) {
                    savedRange = selection.getRangeAt(0).cloneRange();
                }
            }

            const menuBtn = e.target.closest('.toolbar-btn');
            if (menuBtn && (menuBtn.dataset.action === 'quoteMenu' || menuBtn.dataset.action === 'hrMenu')) {
                e.preventDefault(); // 선택 영역 유지

                // 현재 선택 영역 저장
                const selection = window.getSelection();
                if (selection.rangeCount > 0) {
                    savedRange = selection.getRangeAt(0).cloneRange();
                }
            }
        });

        // 드롭다운 메뉴 아이템 클릭
        wrapper.addEventListener('click', (e) => {
            // 드롭다운 메뉴 내부의 버튼 클릭
            const dropdownItem = e.target.closest('.dropdown-item');

            if (dropdownItem) {
                e.preventDefault();
                e.stopPropagation();

                // 저장된 선택 영역 복원
                if (savedRange) {
                    const selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(savedRange);
                }

                const action = dropdownItem.dataset.action;
                if (action) {
                    self.execAction(id, action);

                    // 메뉴 닫기
                    const palette = dropdownItem.closest('.color-palette');
                    if (palette) {
                        palette.classList.remove('show');
                    }

                    // 뷰어에 포커스 복원
                    const viewer = document.getElementById(id + '_viewer');
                    if (viewer) viewer.focus();
                }
                return;
            }

            const btn = e.target.closest('.toolbar-btn');
            if (!btn || btn.id.startsWith('source-toggle')) return;

            e.preventDefault();

            const cmd = btn.dataset.cmd;
            const action = btn.dataset.action;
            const value = btn.dataset.value;

            if (cmd) {
                self.execCommand(cmd, value);
            } else if (action === 'quoteMenu' || action === 'hrMenu') {
                // 메뉴 토글
                const pickerWrapper = btn.closest('.color-picker-wrapper');
                if (pickerWrapper) {
                    const palette = pickerWrapper.querySelector('.color-palette');

                    // 다른 메뉴 닫기
                    wrapper.querySelectorAll('.color-palette').forEach(p => {
                        if (p !== palette) p.classList.remove('show');
                    });

                    if (palette) {
                        palette.classList.toggle('show');
                        self.positionDropdown(btn, palette);
                    }
                }
            } else if (action) {
                self.execAction(id, action);
            }
        });
    },

    bindSourceToggle: function(id, btn) {
        if (!btn) return;

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleSource(id);
        });
    },

    bindViewerSync: function(id) {
        const inst = this.instances[id];
        if (!inst) return;

        // divider 클릭 시 커서를 다음 요소로 이동
        inst.viewer.addEventListener('click', (e) => {
            const divider = e.target.closest('[class^="divider-"]');
            if (divider) {
                e.preventDefault();
                const selection = window.getSelection();
                const nextEl = divider.nextElementSibling;

                if (nextEl) {
                    // 다음 요소로 커서 이동
                    const range = document.createRange();
                    range.setStart(nextEl, 0);
                    range.collapse(true);
                    selection.removeAllRanges();
                    selection.addRange(range);
                } else {
                    // 다음 요소가 없으면 새 p 태그 생성
                    const newP = document.createElement('p');
                    newP.innerHTML = '<br>';
                    divider.parentNode.appendChild(newP);

                    const range = document.createRange();
                    range.setStart(newP, 0);
                    range.collapse(true);
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
            }
        });

        // <code> 태그 내에서 특수문자 입력/붙여넣기 시 자동 변환
        inst.viewer.addEventListener('beforeinput', (e) => {
            const selection = window.getSelection();
            if (!selection.rangeCount) return;

            const range = selection.getRangeAt(0);
            const node = range.startContainer;

            // 인라인 코드 또는 코드 블록 체크
            const codeElement = node.nodeType === Node.TEXT_NODE
                ? node.parentElement?.closest('code')
                : node.closest('code');

            if (!codeElement) return;

            // 타이핑으로 입력
            if (e.inputType === 'insertText') {
                const char = e.data;
                if (char === '<' || char === '>' || char === '&') {
                    e.preventDefault();

                    // code 내부에서는 실제 문자를 텍스트 노드로 보존한다.
                    // 저장 시 innerHTML 직렬화 과정에서 필요한 이스케이프가 적용된다.
                    const textNode = document.createTextNode(char);
                    range.deleteContents();
                    range.insertNode(textNode);

                    // 커서를 텍스트 노드 뒤로 이동
                    range.setStartAfter(textNode);
                    range.setEndAfter(textNode);
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
            }
            // 붙여넣기
            else if (e.inputType === 'insertFromPaste') {
                // 클립보드에서 텍스트 가져오기
                const pastedText = e.dataTransfer?.getData('text/plain');
                if (pastedText === undefined) return;

                e.preventDefault();

                // code 내부에는 HTML 조각이 아니라 순수 텍스트로 삽입
                const fragment = document.createDocumentFragment();
                fragment.appendChild(document.createTextNode(pastedText));
                range.deleteContents();
                range.insertNode(fragment);

                // 커서를 끝으로 이동
                range.collapse(false);
                selection.removeAllRanges();
                selection.addRange(range);
            }
        });

        // viewer 내용 변경 시 source에 동기화
        const self = this;
        let syncTimeout = null;
        const scheduleIdle = window.requestIdleCallback
            ? function(fn) { requestIdleCallback(fn, { timeout: 1000 }); }
            : function(fn) { setTimeout(fn, 0); };

        inst.viewer.addEventListener('input', () => {
            // 글자 수는 즉시 업데이트
            self.updateCharCount(id);

            // Undo/Redo 히스토리 저장 (1초 debounce)
            if (!inst.isUndoRedo) {
                clearTimeout(inst.historyTimer);
                inst.historyTimer = setTimeout(() => {
                    self.saveHistory(id);
                }, 1000);
            }

            // 소스 동기화: debounce 500ms + requestIdleCallback
            if (syncTimeout) clearTimeout(syncTimeout);
            syncTimeout = setTimeout(() => {
                scheduleIdle(() => {
                    self.syncToSource(id);
                });
            }, 500);
        });

        // 초기 글자 수 표시
        setTimeout(() => {
            this.updateCharCount(id);
        }, 100);

        // 키보드 단축키 & Enter/Tab/자동 기호 변환
        inst.viewer.addEventListener('keydown', (e) => {
            // Ctrl+Z: Undo
            if (e.key === 'z' && (e.ctrlKey || e.metaKey) && !e.shiftKey) {
                e.preventDefault();
                self.undo(id);
                return;
            }

            // Ctrl+Y / Ctrl+Shift+Z: Redo
            if ((e.key === 'y' && (e.ctrlKey || e.metaKey)) ||
                (e.key === 'z' && (e.ctrlKey || e.metaKey) && e.shiftKey)) {
                e.preventDefault();
                self.redo(id);
                return;
            }

            // Ctrl+K: 링크
            if (e.key === 'k' && (e.ctrlKey || e.metaKey)) {
                e.preventDefault();
                self.insertLink();
                return;
            }

            // @mention 드롭다운이 열려있으면 에디터가 키를 가로채지 않음
            if ((e.key === 'Enter' || e.key === 'Tab' || e.key === 'ArrowUp' || e.key === 'ArrowDown' || e.key === 'Escape')
                && document.querySelector('.mention-dropdown') && document.querySelector('.mention-dropdown').offsetParent !== null) {
                return;
            }

            // Enter 키: 브라우저 기본 동작 (br 삽입), divider/list 안에서만 특별 처리
            if (e.key === 'Enter' && !e.shiftKey) {
                const selection = window.getSelection();
                if (!selection.rangeCount) return;

                const range = selection.getRangeAt(0);
                let node = range.startContainer;

                if (node.nodeType === Node.TEXT_NODE) {
                    node = node.parentElement;
                }
                if (!node) node = inst.viewer;

                // divider 내에서는 밖으로 빠져나감
                const divider = node.closest ? node.closest('[class^="divider-"]') : null;
                if (divider) {
                    e.preventDefault();
                    const newP = document.createElement('p');
                    newP.innerHTML = '<br>';
                    divider.parentNode.insertBefore(newP, divider.nextSibling);
                    const newRange = document.createRange();
                    newRange.setStart(newP, 0);
                    newRange.collapse(true);
                    selection.removeAllRanges();
                    selection.addRange(newRange);
                    inst.viewer.dispatchEvent(new Event('input', { bubbles: true }));
                    return;
                }

                // li 안에서 Enter 처리
                const li = (node.closest ? node.closest('li') : null)
                         || (range.startContainer.nodeType === Node.TEXT_NODE && range.startContainer.parentElement ? range.startContainer.parentElement.closest('li') : null);
                if (li && li.closest('ul, ol')) {
                    // 빈 li에서 Enter → 리스트 탈출
                    const liText = li.textContent.replace(/\u200B/g, '').trim();
                    if (!liText && !li.querySelector('img')) {
                        e.preventDefault();
                        const listEl = li.closest('ul, ol');
                        const newP = document.createElement('p');
                        newP.innerHTML = '<br>';
                        li.remove();
                        if (!listEl.querySelector('li')) {
                            listEl.parentNode.insertBefore(newP, listEl.nextSibling);
                            listEl.remove();
                        } else {
                            listEl.parentNode.insertBefore(newP, listEl.nextSibling);
                        }
                        const newRange = document.createRange();
                        newRange.setStart(newP, 0);
                        newRange.collapse(true);
                        selection.removeAllRanges();
                        selection.addRange(newRange);
                        inst.viewer.dispatchEvent(new Event('input', { bubbles: true }));
                        return;
                    }
                    // 내용 있는 li에서 Enter → 브라우저 기본 동작 (새 li 생성)
                    return;
                }

                // 일반 텍스트에서 Enter: 브라우저 기본 동작 사용 (br 삽입)
            }

            // Tab 키: 들여쓰기
            if (e.key === 'Tab') {
                e.preventDefault();

                const selection = window.getSelection();
                if (!selection.rangeCount) return;

                const range = selection.getRangeAt(0);
                const node = range.commonAncestorContainer;

                // 리스트 항목인지 확인
                const li = node.nodeType === Node.TEXT_NODE ? node.parentElement.closest('li') : node.closest('li');

                if (li) {
                    // 리스트 들여쓰기/내어쓰기
                    if (e.shiftKey) {
                        document.execCommand('outdent', false, null);
                    } else {
                        document.execCommand('indent', false, null);
                    }
                } else {
                    // 일반 텍스트 들여쓰기
                    const block = node.nodeType === Node.TEXT_NODE ? node.parentElement : node;
                    const currentMargin = parseInt(window.getComputedStyle(block).marginLeft) || 0;

                    if (e.shiftKey) {
                        // Shift+Tab: 내어쓰기
                        const newMargin = Math.max(0, currentMargin - 20);
                        block.style.marginLeft = newMargin + 'px';
                    } else {
                        // Tab: 들여쓰기
                        block.style.marginLeft = (currentMargin + 20) + 'px';
                    }
                }

                // input 이벤트 발생
                inst.viewer.dispatchEvent(new Event('input', { bubbles: true }));
            }

            // 자동 기호 변환: -> → →
            if (e.key === ' ') {
                setTimeout(() => {
                    const selection = window.getSelection();
                    if (!selection.rangeCount) return;

                    const range = selection.getRangeAt(0);
                    const node = range.startContainer;

                    if (node.nodeType === Node.TEXT_NODE) {
                        const text = node.textContent;
                        const offset = range.startOffset;

                        // -> 체크
                        if (text.substring(offset - 3, offset) === '-> ') {
                            node.textContent = text.substring(0, offset - 3) + '→ ' + text.substring(offset);
                            range.setStart(node, offset - 1);
                            range.setEnd(node, offset - 1);
                            selection.removeAllRanges();
                            selection.addRange(range);
                        }
                        // ㄴ 체크
                        else if (text.substring(offset - 2, offset) === 'ㄴ ') {
                            node.textContent = text.substring(0, offset - 2) + '↳ ' + text.substring(offset);
                            range.setStart(node, offset - 1);
                            range.setEnd(node, offset - 1);
                            selection.removeAllRanges();
                            selection.addRange(range);
                        }
                    }
                }, 0);
            }
        });

        // 붙여넣기 시 안전한 스타일만 보존
        inst.viewer.addEventListener('paste', (e) => {
            const selection = window.getSelection();
            if (selection.rangeCount) {
                const range = selection.getRangeAt(0);
                const node = range.startContainer;
                const codeElement = node.nodeType === Node.TEXT_NODE
                    ? node.parentElement?.closest('code')
                    : node.closest?.('code');

                if (codeElement) {
                    e.preventDefault();
                    const text = (e.clipboardData || window.clipboardData).getData('text/plain') || '';
                    const textNode = document.createTextNode(text);
                    range.deleteContents();
                    range.insertNode(textNode);
                    range.setStartAfter(textNode);
                    range.setEndAfter(textNode);
                    selection.removeAllRanges();
                    selection.addRange(range);
                    self.saveHistory(id);
                    inst.viewer.dispatchEvent(new Event('input', { bubbles: true }));
                    return;
                }
            }

            e.preventDefault();

            const html = (e.clipboardData || window.clipboardData).getData('text/html');
            const text = (e.clipboardData || window.clipboardData).getData('text/plain');

            let cleanHtml;
            if (html) {
                cleanHtml = self.sanitizePastedHtml(html);

            } else if (text) {
                // 순수 텍스트 → HTML 이스케이프 후 줄바꿈 처리
                cleanHtml = text
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\n/g, '<br>');
            } else {
                cleanHtml = '';
            }

            if (cleanHtml) {
                document.execCommand('insertHTML', false, cleanHtml);
            }

            // 히스토리 즉시 저장
            self.saveHistory(id);
            inst.viewer.dispatchEvent(new Event('input', { bubbles: true }));
        });

    },

    bindFileUpload: function(id) {
        const self = this;
        const viewer = document.getElementById(id + '_viewer');
        if (!viewer) return;

        // 페이지 내의 모든 파일 입력 필드 찾기
        const fileInputs = document.querySelectorAll('input[type="file"][name="bf_file[]"]');

        fileInputs.forEach((input, index) => {
            input.addEventListener('change', function(e) {
                if (this.files && this.files.length > 0) {
                    const file = this.files[0];

                    // 이미지 파일인지 확인
                    if (file.type.startsWith('image/')) {
                        // bf_file의 순서 (bf_content)를 사용
                        const fileIndex = Array.from(fileInputs).indexOf(this);

                        // 에디터 끝에 이미지 플레이스홀더 추가
                        self.appendImagePlaceholder(id, fileIndex);
                    }
                }
            });
        });
    },

    appendImagePlaceholder: function(id, imageIndex) {
        const viewer = document.getElementById(id + '_viewer');
        if (!viewer) return;

        const placeholder = `{이미지:${imageIndex}}`;

        // 현재 내용 확인
        const currentContent = viewer.innerHTML;

        // 이미 같은 플레이스홀더가 있는지 확인
        if (currentContent.includes(placeholder)) {
            return; // 이미 있으면 추가하지 않음
        }

        // 에디터 끝에 추가 (contenteditable 제거하여 텍스트처럼 선택 가능하게)
        const html = `<p><span class="image-tag">${placeholder}</span></p>`;
        viewer.innerHTML += html;

        // source에도 동기화
        const inst = this.instances[id];
        if (inst && !inst.sourceMode) {
            const content = '<div class="ra0-content">' + viewer.innerHTML + '</div>';
            inst.source.value = content;
        }

        // input 이벤트 발생시켜서 동기화
        const event = new Event('input', { bubbles: true });
        viewer.dispatchEvent(event);
    },

    bindColorPickers: function(id) {
        const wrapper = document.getElementById('ra0_editor_' + id);
        if (!wrapper) return;

        const self = this; // this 참조 저장
        const pickers = wrapper.querySelectorAll('.color-picker-wrapper');
        let savedRange = null; // 선택 영역 저장

        pickers.forEach(pickerWrapper => {
            const btn = pickerWrapper.querySelector('.color-btn');
            const palette = pickerWrapper.querySelector('.color-palette');
            const action = btn.dataset.action;

            btn.addEventListener('mousedown', (e) => {
                e.preventDefault(); // 선택 영역 유지
            });

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                // 현재 선택 영역 저장
                const selection = window.getSelection();
                if (selection.rangeCount > 0) {
                    savedRange = selection.getRangeAt(0).cloneRange();
                }

                // 다른 팔레트 닫기
                wrapper.querySelectorAll('.color-palette').forEach(p => {
                    if (p !== palette) p.classList.remove('show');
                });

                palette.classList.toggle('show');
                self.positionDropdown(btn, palette);
            });

            palette.addEventListener('mousedown', (e) => {
                e.preventDefault(); // 선택 영역 유지
            });

            palette.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();

                // 색상 팔레트 (textColor, bgColor)
                const colorSpan = e.target.closest('span[data-color]');
                if (colorSpan) {
                    const color = colorSpan.dataset.color;

                    // 저장된 선택 영역 복원
                    if (savedRange) {
                        const selection = window.getSelection();
                        selection.removeAllRanges();
                        selection.addRange(savedRange);
                    }

                    if (action === 'textColor') {
                        self.applyTextColor(color);
                    } else if (action === 'bgColor') {
                        self.applyBgColor(color);
                    }

                    palette.classList.remove('show');

                    // 뷰어에 포커스 복원
                    const viewer = document.getElementById(id + '_viewer');
                    if (viewer) viewer.focus();
                    return;
                }

                // 드롭다운 메뉴 (quoteMenu, hrMenu, fontMenu)
                const dropdownItem = e.target.closest('.dropdown-item');
                if (dropdownItem) {
                    const itemAction = dropdownItem.dataset.action;

                    // 저장된 선택 영역 복원
                    if (savedRange) {
                        const selection = window.getSelection();
                        selection.removeAllRanges();
                        selection.addRange(savedRange);
                    }

                    if (itemAction === 'setFont') {
                        // 폰트 설정
                        const fontFamily = dropdownItem.dataset.fontFamily;
                        if (fontFamily) {
                            self.applyFont(fontFamily);
                        }
                    } else if (itemAction) {
                        self.execAction(id, itemAction);
                    }

                    palette.classList.remove('show');

                    // 뷰어에 포커스 복원
                    const viewer = document.getElementById(id + '_viewer');
                    if (viewer) viewer.focus();
                }
            });
        });

        // 외부 클릭 시 팔레트 닫기
        document.addEventListener('click', () => {
            wrapper.querySelectorAll('.color-palette').forEach(p => {
                p.classList.remove('show');
            });
        });
    },

    positionDropdown: function(btn, palette) {
        if (!btn || !palette || !palette.classList.contains('show')) return;
        if (!palette.classList.contains('case-format-menu')) return;

        const rect = btn.getBoundingClientRect();
        const declaredWidth = parseFloat(palette.style.width) || palette.offsetWidth || 300;
        const margin = 8;
        const left = Math.min(
            Math.max(margin, rect.left),
            Math.max(margin, window.innerWidth - declaredWidth - margin)
        );
        const availableHeight = Math.max(180, window.innerHeight - rect.bottom - margin * 2);

        palette.style.position = 'fixed';
        palette.style.top = Math.round(rect.bottom + 4) + 'px';
        palette.style.left = Math.round(left) + 'px';
        palette.style.right = 'auto';
        palette.style.maxHeight = Math.floor(availableHeight) + 'px';
        palette.style.overflowY = 'auto';
        palette.style.zIndex = '9999';
    },

    applyTextColor: function(color) {
        document.execCommand('styleWithCSS', false, true);
        document.execCommand('foreColor', false, color);
        document.execCommand('styleWithCSS', false, false); // 되돌리기
    },

    applyBgColor: function(color) {
        document.execCommand('styleWithCSS', false, true);
        document.execCommand('backColor', false, color);
        document.execCommand('styleWithCSS', false, false); // 되돌리기
    },

    applyFont: function(fontFamily) {
        const selection = window.getSelection();
        if (!selection.rangeCount) return;

        const range = selection.getRangeAt(0);

        // 선택된 텍스트가 없으면 무시
        if (range.collapsed) return;

        const viewer = this.getViewerFromRange(range);
        const root = range.commonAncestorContainer.nodeType === Node.TEXT_NODE
            ? range.commonAncestorContainer.parentNode
            : range.commonAncestorContainer;
        const textRanges = [];
        const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
            acceptNode: (node) => {
                if (!node.nodeValue || !range.intersectsNode(node)) {
                    return NodeFilter.FILTER_REJECT;
                }

                const selectedStart = node === range.startContainer ? range.startOffset : 0;
                const selectedEnd = node === range.endContainer ? range.endOffset : node.nodeValue.length;
                if (selectedStart >= selectedEnd) {
                    return NodeFilter.FILTER_REJECT;
                }

                return NodeFilter.FILTER_ACCEPT;
            }
        });

        if (root.nodeType === Node.TEXT_NODE && range.intersectsNode(root)) {
            textRanges.push({
                node: root,
                start: root === range.startContainer ? range.startOffset : 0,
                end: root === range.endContainer ? range.endOffset : root.nodeValue.length
            });
        } else {
            let node;
            while ((node = walker.nextNode())) {
                textRanges.push({
                    node: node,
                    start: node === range.startContainer ? range.startOffset : 0,
                    end: node === range.endContainer ? range.endOffset : node.nodeValue.length
                });
            }
        }

        let lastSpan = null;
        textRanges.forEach((item) => {
            if (!item.node.parentNode || item.start >= item.end) return;

            const textRange = document.createRange();
            textRange.setStart(item.node, item.start);
            textRange.setEnd(item.node, item.end);

            const span = document.createElement('span');
            span.style.fontFamily = fontFamily;
            span.setAttribute('data-ra0-font', 'true'); // 의도적인 글꼴 적용 표시
            span.appendChild(textRange.extractContents());
            textRange.insertNode(span);
            lastSpan = span;
        });

        if (!lastSpan) return;

        range.setStartAfter(lastSpan);
        range.setEndAfter(lastSpan);
        range.collapse(false);
        selection.removeAllRanges();
        selection.addRange(range);

        if (viewer) {
            viewer.dispatchEvent(new Event('input', { bubbles: true }));
        }
    },

    execCommand: function(cmd, value = null) {
        // 정렬 명령일 경우 styleWithCSS를 false로 설정하여 불필요한 inline style 방지
        const alignCommands = ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'];
        if (alignCommands.includes(cmd)) {
            document.execCommand('styleWithCSS', false, false);
        }

        document.execCommand(cmd, false, value);

        // 정렬 명령 후 불필요한 font-size inline style 제거
        if (alignCommands.includes(cmd)) {
            this.cleanupAlignmentStyles();
        }
    },

    cleanupAlignmentStyles: function() {
        // 현재 선택 영역의 부모 요소들에서 불필요한 font-size style 제거
        const selection = window.getSelection();
        if (!selection.rangeCount) return;

        const range = selection.getRangeAt(0);
        let node = range.commonAncestorContainer;

        // 텍스트 노드면 부모로 이동
        if (node.nodeType === Node.TEXT_NODE) {
            node = node.parentElement;
        }

        // 부모 요소들 순회하며 font-size style 제거
        while (node && node.nodeType === Node.ELEMENT_NODE) {
            if (node.style && node.style.fontSize) {
                node.style.removeProperty('font-size');
                // style 속성이 비어있으면 속성 자체 제거 (다른 스타일이 있으면 유지)
                const remainingStyle = node.getAttribute('style');
                if (!remainingStyle || remainingStyle.trim() === '') {
                    node.removeAttribute('style');
                }
            }
            node = node.parentElement;
        }
    },

    execAction: function(id, action) {
        const actions = {
            'emoticon': () => {
                if (typeof RA0Emoticon !== 'undefined') {
                    RA0Emoticon.showPopup(document.getElementById(id + '_viewer'));
                }
            },
            'spoiler': () => this.insertSpoiler(),
            'quoteType1': () => this.insertQuote('type1'),
            'quoteType2': () => this.insertQuote('type2'),
            'quoteType3': () => this.insertQuote('type3'),
            'quoteBlock': () => this.insertQuote('block'),
            'formatH2': () => this.execCommand('formatBlock', 'h2'),
            'formatH3': () => this.execCommand('formatBlock', 'h3'),
            'formatH4': () => this.execCommand('formatBlock', 'h4'),
            'alignLeft': () => this.execCommand('justifyLeft'),
            'alignCenter': () => this.execCommand('justifyCenter'),
            'alignRight': () => this.execCommand('justifyRight'),
            'alignFull': () => this.execCommand('justifyFull'),
            'caseDoorGate': () => this.insertCaseFormat('doorGate'),
            'caseDoorWindow': () => this.insertCaseFormat('doorWindow'),
            'caseDoorEpisode': () => this.insertCaseFormat('doorEpisode'),
            'caseDoorSignal': () => this.insertCaseFormat('doorSignal'),
            'caseDoorEvidence': () => this.insertCaseFormat('doorEvidence'),
            'caseDoorQuiet': () => this.insertCaseFormat('doorQuiet'),
            'caseDoorStamp': () => this.insertCaseFormat('doorStamp'),
            'caseDoorLedger': () => this.insertCaseFormat('doorLedger'),
            'caseDoorWitness': () => this.insertCaseFormat('doorWitness'),
            'caseDoorIndex': () => this.insertCaseFormat('doorIndex'),
            'caseDecoMarker': () => this.insertCaseFormat('decoMarker'),
            'caseDecoRoute': () => this.insertCaseFormat('decoRoute'),
            'caseDecoTab': () => this.insertCaseFormat('decoTab'),
            'caseDecoSeal': () => this.insertCaseFormat('decoSeal'),
            'caseDecoThread': () => this.insertCaseFormat('decoThread'),
            'caseDecoTime': () => this.insertCaseFormat('decoTime'),
            'caseDecoFlag': () => this.insertCaseFormat('decoFlag'),
            'caseDecoDotline': () => this.insertCaseFormat('decoDotline'),
            'caseDecoTrace': () => this.insertCaseFormat('decoTrace'),
            'caseDeviceMark': () => this.insertCaseFormat('deviceMark'),
            'caseDeviceWave': () => this.insertCaseFormat('deviceWave'),
            'caseDeviceWhisper': () => this.insertCaseFormat('deviceWhisper'),
            'caseDeviceMono': () => this.insertCaseFormat('deviceMono'),
            'caseDeviceDot': () => this.insertCaseFormat('deviceDot'),
            'caseDeviceGlow': () => this.insertCaseFormat('deviceGlow'),
            'caseDeviceMemo': () => this.insertCaseFormat('deviceMemo'),
            'caseDeviceLog': () => this.insertCaseFormat('deviceLog'),
            'caseDeviceLogError': () => this.insertCaseFormat('deviceLogError'),
            'caseDeviceLogSuccess': () => this.insertCaseFormat('deviceLogSuccess'),
            'caseDeviceSystem': () => this.insertCaseFormat('deviceSystem'),
            'caseDeviceAlert': () => this.insertCaseFormat('deviceAlert'),
            'hrFull': () => this.insertHR('full'),
            'hrShort': () => this.insertHR('short'),
            'hrDotFull': () => this.insertHR('dot-full'),
            'hrDotShort': () => this.insertHR('dot-short'),
            'fold': () => this.insertFold(id),
            'link': () => this.insertLink(),
            'removeStyles': () => this.removeStyles(id),
            'smallText': () => this.insertSmallText(),
            'codeBlock': () => this.insertCodeBlock(),
            'cleanupTags': () => this.cleanupTags(id),
            'autoIndent': () => this.toggleAutoIndent(id),
            'textColor': () => {}, // 색상 팔레트에서 처리
            'bgColor': () => {},    // 색상 팔레트에서 처리
            'quoteMenu': () => {},  // 메뉴만 열림
            'hrMenu': () => {},     // 메뉴만 열림
            'caseTitleMenu': () => {},    // 제목 서식 메뉴만 열림
            'caseSubtitleMenu': () => {}, // 부제 서식 메뉴만 열림
            'caseDeviceMenu': () => {},   // 요소 서식 메뉴만 열림
            'paragraphMenu': () => {},    // 문단 서식 메뉴만 열림
            'alignMenu': () => {},        // 정렬 메뉴만 열림
            'fontMenu': () => {},   // 메뉴만 열림
            'setFont': () => {}     // 폰트 선택 (bindColorPickers에서 처리)
        };

        if (actions[action]) {
            actions[action]();
        }
    },

    removeStyles: function(id) {
        const viewer = document.getElementById(id + '_viewer');
        if (!viewer) return;

        const selection = window.getSelection();
        let isSelection = false;
        let targetElement;
        let parentToRemove = null;

        // 서식 태그 목록
        const formatTags = ['H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'B', 'STRONG', 'I', 'EM', 'U', 'S', 'DEL', 'STRIKE', 'SUB', 'SUP', 'MARK', 'SMALL', 'BIG', 'FONT', 'SPAN'];

        // 선택 영역이 있으면 선택된 부분만, 없으면 전체
        if (selection.rangeCount > 0 && !selection.isCollapsed) {
            const range = selection.getRangeAt(0);

            // 선택 영역의 부모 중 서식 태그가 있는지 확인
            let node = range.commonAncestorContainer;
            if (node.nodeType === Node.TEXT_NODE) {
                node = node.parentNode;
            }

            // 부모를 타고 올라가며 서식 태그 찾기
            while (node && node !== viewer) {
                if (formatTags.includes(node.tagName)) {
                    parentToRemove = node;
                    break;
                }
                node = node.parentNode;
            }

            if (parentToRemove) {
                // 서식 태그 전체를 대상으로
                const temp = document.createElement('div');
                temp.innerHTML = parentToRemove.innerHTML;
                targetElement = temp;
                isSelection = true;
            } else {
                const fragment = range.cloneContents();
                const temp = document.createElement('div');
                temp.appendChild(fragment);
                targetElement = temp;
                isSelection = true;
            }
        } else {
            targetElement = viewer.cloneNode(true);
        }

        // HTML을 텍스트로 변환
        let html = targetElement.innerHTML;

        // 1. 블록 요소들을 줄바꿈으로 변환
        html = html.replace(/<\/(div|p|h[1-6]|ul|ol|li|table|tr|td|th|blockquote|pre|section|article|header|footer|nav|aside)>/gi, '\n');

        // 2. br 태그를 줄바꿈으로 변환
        html = html.replace(/<br\s*\/?>/gi, '\n');

        // 3. 모든 HTML 태그 제거
        html = html.replace(/<[^>]*>?/g, '');

        // 4. HTML 엔티티 디코드
        const textarea = document.createElement('textarea');
        textarea.innerHTML = html;
        let plainText = textarea.value;

        // 5. 연속된 줄바꿈 정리 (3개 이상 -> 2개로)
        plainText = plainText.replace(/\n{3,}/g, '\n\n');

        // 6. 앞뒤 공백 제거
        plainText = plainText.trim();

        // 7. 줄바꿈을 <br>로 변환
        const cleanHtml = plainText.replace(/\n/g, '<br>');

        // 선택 영역이 있었다면 교체, 없으면 전체 교체
        if (isSelection) {
            if (parentToRemove) {
                // 서식 태그 전체를 텍스트로 교체
                const textNode = document.createTextNode(plainText);
                parentToRemove.parentNode.replaceChild(textNode, parentToRemove);
            } else if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                range.deleteContents();

                // HTML로 삽입
                const temp = document.createElement('div');
                temp.innerHTML = cleanHtml;
                const frag = document.createDocumentFragment();
                while (temp.firstChild) {
                    frag.appendChild(temp.firstChild);
                }
                range.insertNode(frag);
            }
        } else {
            viewer.innerHTML = cleanHtml;
        }

        // input 이벤트 트리거 (동기화)
        viewer.dispatchEvent(new Event('input', { bubbles: true }));
    },

    insertSmallText: function() {
        const selection = window.getSelection();
        if (!selection.rangeCount) return;

        const range = selection.getRangeAt(0);
        const text = range.toString() || '작은 글씨';

        const span = document.createElement('span');
        span.className = 'text-small';
        span.textContent = text;

        range.deleteContents();
        range.insertNode(span);

        // 커서를 span 뒤로 이동
        range.setStartAfter(span);
        range.collapse(true);
        selection.removeAllRanges();
        selection.addRange(range);
    },

    insertSpoiler: function() {
        const selection = window.getSelection();
        if (!selection.rangeCount) return;

        const range = selection.getRangeAt(0);
        const text = range.toString() || '스포일러 내용';

        const span = document.createElement('span');
        span.className = 'spoiler';
        span.textContent = text;

        range.deleteContents();
        range.insertNode(span);

        range.setStartAfter(span);
        range.collapse(true);
        selection.removeAllRanges();
        selection.addRange(range);

        const viewer = span.closest('.ra0-editor-viewer');
        if (viewer) {
            viewer.dispatchEvent(new Event('input', { bubbles: true }));
        }
    },

    insertQuote: function(type = 'default') {
        const selection = window.getSelection();
        const text = this.escapeHtml(selection.toString() || '인용 내용을 입력하세요').replace(/\n/g, '<br>');

        // 블록 인용 타입들
        if (type === 'type1' || type === 'type2' || type === 'type3') {
            const html = `<blockquote class="blockquote-${type}"><p>${text}</p></blockquote><p><br></p>`;
            this.execCommand('insertHTML', html);
        } else if (type === 'block') {
            // 기본 블록 인용 (좌측 선)
            const html = `<blockquote>${text}</blockquote><p><br></p>`;
            this.execCommand('insertHTML', html);
        } else {
            // 인라인 인용
            let openQuote = '"';
            let closeQuote = '"';

            if (type === 'info') {
                openQuote = '『';
                closeQuote = '』';
            } else if (type === 'warning') {
                openQuote = '「';
                closeQuote = '」';
            }

            const html = `<span class="inline-quote">${openQuote}${text}${closeQuote}</span>`;
            this.execCommand('insertHTML', html);
        }
    },

    escapeHtml: function(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    },

    getViewerFromRange: function(range) {
        if (!range) return null;
        const node = range.commonAncestorContainer.nodeType === Node.TEXT_NODE
            ? range.commonAncestorContainer.parentElement
            : range.commonAncestorContainer;
        return node && node.closest ? node.closest('.ra0-editor-viewer') : null;
    },

    getClosestEditableBlock: function(node, viewer) {
        if (!node || !viewer) return null;
        const element = node.nodeType === Node.TEXT_NODE ? node.parentElement : node;
        if (!element || !element.closest) return null;

        const block = element.closest('p, div, h1, h2, h3, h4, h5, h6, blockquote, pre, li, details, section, article');
        return block && viewer.contains(block) ? block : null;
    },

    isEmptyEditableBlock: function(element) {
        if (!element) return false;
        const text = (element.textContent || '').replace(/\u00a0/g, '').trim();
        const hasMedia = !!element.querySelector('img, video, iframe, table, hr, details');
        return text === '' && !hasMedia;
    },

    moveCursorInto: function(viewer, target) {
        if (!viewer || !target) return;

        const selection = window.getSelection();
        const range = document.createRange();

        if (target.nodeType === Node.TEXT_NODE) {
            range.setStart(target, target.nodeValue.length);
        } else {
            range.selectNodeContents(target);
            range.collapse(true);
        }

        selection.removeAllRanges();
        selection.addRange(range);
        viewer.focus();
    },

    insertBlockHTML: function(html) {
        const selection = window.getSelection();
        if (!selection.rangeCount) {
            this.execCommand('insertHTML', html);
            return;
        }

        const range = selection.getRangeAt(0);
        const viewer = this.getViewerFromRange(range);
        if (!viewer) {
            this.execCommand('insertHTML', html);
            return;
        }

        const temp = document.createElement('div');
        temp.innerHTML = html;
        const nodes = Array.from(temp.childNodes);
        const focusNode = nodes[nodes.length - 1] || null;
        let block = this.getClosestEditableBlock(range.startContainer, viewer);

        if (!range.collapsed) {
            range.deleteContents();
            block = block && viewer.contains(block) ? block : null;
        }

        if (block && block !== viewer) {
            if (this.isEmptyEditableBlock(block)) {
                block.replaceWith(...nodes);
            } else {
                block.after(...nodes);
            }
        } else {
            const fragment = document.createDocumentFragment();
            nodes.forEach((node) => fragment.appendChild(node));
            range.insertNode(fragment);
        }

        this.moveCursorInto(viewer, focusNode);
        viewer.dispatchEvent(new Event('input', { bubbles: true }));
    },

    getSelectedCaseText: function(defaultText) {
        const selection = window.getSelection();
        const selectedText = selection && !selection.isCollapsed ? selection.toString().trim() : '';
        return this.escapeHtml(selectedText || defaultText).replace(/\n/g, '<br>');
    },

    insertInlineHTML: function(html) {
        const selection = window.getSelection();
        if (!selection.rangeCount) return;

        const range = selection.getRangeAt(0);
        const anchor = range.commonAncestorContainer.nodeType === Node.TEXT_NODE
            ? range.commonAncestorContainer.parentElement
            : range.commonAncestorContainer;
        const viewer = anchor && anchor.closest ? anchor.closest('.ra0-editor-viewer') : null;
        if (!viewer) return;

        range.deleteContents();

        const fragment = range.createContextualFragment(html);
        const lastNode = fragment.lastChild;
        range.insertNode(fragment);

        if (lastNode) {
            range.setStartAfter(lastNode);
            range.collapse(true);
            selection.removeAllRanges();
            selection.addRange(range);
        }

        viewer.dispatchEvent(new Event('input', { bubbles: true }));
    },

    insertCaseFormat: function(type) {
        const templates = {
            doorGate: `<h2 class="ra0-door-gate"><span>${this.getSelectedCaseText('제목')}</span><small>CASE 01 / 부제 영역</small></h2><p><br></p>`,
            doorWindow: `<h2 class="ra0-door-window"><span>CASE</span><b>${this.getSelectedCaseText('제목')}</b></h2><p><br></p>`,
            doorEpisode: `<h2 class="ra0-door-episode"><small>EP.01</small><span>${this.getSelectedCaseText('제목')}</span></h2><p><br></p>`,
            doorSignal: `<h2 class="ra0-door-signal"><span>[ RA0:LOG ]</span><b>${this.getSelectedCaseText('제목')}</b></h2><p><br></p>`,
            doorEvidence: `<h2 class="ra0-door-evidence"><small>EVIDENCE 05</small><span>${this.getSelectedCaseText('제목')}</span></h2><p><br></p>`,
            doorQuiet: `<h2 class="ra0-door-quiet">${this.getSelectedCaseText('제목')}</h2><p><br></p>`,
            doorStamp: `<h2 class="ra0-door-stamp">${this.getSelectedCaseText('제목')}</h2><p><br></p>`,
            doorLedger: `<h2 class="ra0-door-ledger">${this.getSelectedCaseText('제목')}</h2><p><br></p>`,
            doorWitness: `<h2 class="ra0-door-witness"><em>WIT</em><span>${this.getSelectedCaseText('제목')}</span><small>witness statement / 부제 영역</small></h2><p><br></p>`,
            doorIndex: `<h2 class="ra0-door-index"><span>10</span><b>${this.getSelectedCaseText('제목 10')}</b></h2><p><br></p>`,
            decoMarker: `<h3 class="ra0-deco-marker">${this.getSelectedCaseText('부제')}</h3><p><br></p>`,
            decoRoute: `<h3 class="ra0-deco-route">${this.getSelectedCaseText('부제')}</h3><p><br></p>`,
            decoTab: `<h3 class="ra0-deco-tab"><em>STATEMENT</em><span>${this.getSelectedCaseText('부제')}</span></h3><p><br></p>`,
            decoSeal: `<h3 class="ra0-deco-seal">${this.getSelectedCaseText('부제')}</h3><p><br></p>`,
            decoThread: `<h3 class="ra0-deco-thread">${this.getSelectedCaseText('부제')}</h3><p><br></p>`,
            decoTime: `<h3 class="ra0-deco-time"><span>20:26 / </span>${this.getSelectedCaseText('부제')}</h3><p><br></p>`,
            decoFlag: `<h3 class="ra0-deco-flag"><em>SCENE</em><span>${this.getSelectedCaseText('부제')}</span></h3><p><br></p>`,
            decoDotline: `<h3 class="ra0-deco-dotline">${this.getSelectedCaseText('부제와 구분선')}</h3><p><br></p>`,
            decoTrace: `<h3 class="ra0-deco-trace"><em>TRACE</em><span>${this.getSelectedCaseText('부제')}</span></h3><p><br></p>`,
            deviceMark: `<span class="ra0-device-mark">${this.getSelectedCaseText('내용')}</span>`,
            deviceWave: `<span class="ra0-device-wave">${this.getSelectedCaseText('내용')}</span>`,
            deviceWhisper: `<span class="ra0-device-whisper">${this.getSelectedCaseText('내용')}</span>`,
            deviceMono: `<span class="ra0-device-mono">${this.getSelectedCaseText('내용')}</span>`,
            deviceDot: `<span class="ra0-device-dot">${this.getSelectedCaseText('내용')}</span>`,
            deviceGlow: `<span class="ra0-device-glow">${this.getSelectedCaseText('내용')}</span>`,
            deviceMemo: `<div class="ra0-device-memo">${this.getSelectedCaseText('메모, 주석, 추가 설명')}</div><p><br></p>`,
            deviceLog: `<div class="ra0-device-log ra0-log-primary"><span>제목</span><p>${this.getSelectedCaseText('내용')}</p></div><p><br></p>`,
            deviceLogError: `<div class="ra0-device-log ra0-log-error"><span>제목</span><p>${this.getSelectedCaseText('내용')}</p></div><p><br></p>`,
            deviceLogSuccess: `<div class="ra0-device-log ra0-log-success"><span>제목</span><p>${this.getSelectedCaseText('내용')}</p></div><p><br></p>`,
            deviceSystem: `<div class="ra0-device-system">SYSTEM: ${this.getSelectedCaseText('내용')}</div><p><br></p>`,
            deviceAlert: `<div class="ra0-device-alert"><b>!</b><p>${this.getSelectedCaseText('주의, 경고, 안내 등')}</p></div><p><br></p>`
        };

        if (templates[type]) {
            const blockTypes = [
                'doorGate', 'doorWindow', 'doorEpisode', 'doorSignal', 'doorEvidence',
                'doorQuiet', 'doorStamp', 'doorLedger', 'doorWitness', 'doorIndex',
                'decoMarker', 'decoRoute', 'decoTab', 'decoSeal', 'decoThread',
                'decoTime', 'decoFlag', 'decoDotline', 'decoTrace',
                'deviceMemo', 'deviceLog', 'deviceLogError', 'deviceLogSuccess',
                'deviceSystem', 'deviceAlert'
            ];

            if (blockTypes.includes(type)) {
                this.insertBlockHTML(templates[type]);
            } else {
                this.insertInlineHTML(templates[type]);
            }
        }
    },

    insertHR: function(type = 'full') {
        let className = 'divider-full';
        if (type === 'short') className = 'divider-short';
        if (type === 'dot-full') className = 'divider-dot-full';
        if (type === 'dot-short') className = 'divider-dot-short';

        // hr 태그 사용 (void 요소라서 복제 안됨)
        const html = `<hr class="${className}"><p><br></p>`;
        this.execCommand('insertHTML', html);
    },

    insertFold: function(id) {
        const viewer = document.getElementById(id + '_viewer');
        if (!viewer) return;

        // 간단한 구조로 변경 - 모두 편집 가능
        const html = `<details class="fold">
<summary>제목을 입력하세요</summary>
<div class="fold-content">내용을 입력하세요</div>
</details><p><br></p>`;

        this.execCommand('insertHTML', html);
    },

    insertLink: function() {
        const url = prompt('링크 URL을 입력하세요:', 'https://');
        if (!url) return;

        this.execCommand('createLink', url);
    },

    insertInlineCode: function() {
        const selection = window.getSelection();
        if (!selection.rangeCount) return;

        const range = selection.getRangeAt(0);
        const text = range.toString() || '코드';

        // HTML로 직접 삽입 (HTML 엔티티 변환)
        const escapedText = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const html = `<code>${escapedText}</code>&nbsp;`;

        // HTML 삽입
        range.deleteContents();
        const fragment = range.createContextualFragment(html);
        range.insertNode(fragment);

        // 커서를 code 태그 뒤로 이동
        range.collapse(false);
        selection.removeAllRanges();
        selection.addRange(range);
    },

    insertCodeBlock: function() {
        // 현재 선택 영역 저장
        const selection = window.getSelection();
        if (selection.rangeCount) {
            this.savedRange = selection.getRangeAt(0).cloneRange();
        }

        // 모달 열기
        this.openCodeBlockModal();
    },

    openCodeBlockModal: function() {
        const modal = document.getElementById('code-block-modal');
        if (modal) {
            // 폼 초기화
            document.getElementById('code-language').value = 'javascript';
            document.getElementById('code-content').value = '';

            // 모달 표시
            modal.style.display = 'flex';

            // 코드 입력란에 포커스
            setTimeout(() => {
                document.getElementById('code-content').focus();
            }, 100);

            // 키보드 이벤트 등록 (한 번만)
            if (!this.modalKeyHandlerAttached) {
                const codeTextarea = document.getElementById('code-content');

                // ESC 키로 모달 닫기
                modal.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        this.closeCodeBlockModal();
                    }
                });

                // Ctrl+Enter로 삽입
                codeTextarea.addEventListener('keydown', (e) => {
                    if (e.ctrlKey && e.key === 'Enter') {
                        e.preventDefault();
                        this.insertCodeBlockFromModal();
                    }
                });

                // 오버레이 클릭시 모달 닫기
                const overlay = modal.querySelector('.modal-overlay');
                if (overlay) {
                    overlay.addEventListener('click', () => {
                        this.closeCodeBlockModal();
                    });
                }

                this.modalKeyHandlerAttached = true;
            }
        }
    },

    closeCodeBlockModal: function() {
        const modal = document.getElementById('code-block-modal');
        if (modal) {
            modal.style.display = 'none';

            // 폼 초기화
            document.getElementById('code-language').value = 'javascript';
            document.getElementById('code-content').value = '';
        }
    },

    insertCodeBlockFromModal: function() {
        const language = document.getElementById('code-language').value;
        const code = document.getElementById('code-content').value;

        if (!code.trim()) {
            alert('코드를 입력해주세요.');
            document.getElementById('code-content').focus();
            return;
        }

        // 저장된 선택 영역 복원
        const selection = window.getSelection();
        if (this.savedRange) {
            selection.removeAllRanges();
            selection.addRange(this.savedRange);
        }

        if (!selection.rangeCount) return;

        const range = selection.getRangeAt(0);

        // 코드 이스케이프
        const escapedCode = code
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Prism.js 형식의 코드 블록 HTML
        const html = `<pre><code class="language-${language}">${escapedCode}</code></pre><p><br></p>`;

        range.deleteContents();
        const fragment = range.createContextualFragment(html);
        range.insertNode(fragment);

        // Prism.js 하이라이팅 적용
        setTimeout(() => {
            if (window.Prism) {
                Prism.highlightAll();
            }
        }, 10);

        range.collapse(false);
        selection.removeAllRanges();
        selection.addRange(range);

        // 모달 닫기
        this.closeCodeBlockModal();
    },


    toggleSource: function(id) {
        const inst = this.instances[id];
        if (!inst) return;

        inst.sourceMode = !inst.sourceMode;

        if (inst.sourceMode) {
            // 뷰어에서 소스로 전환: 커서 위치 저장
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                inst.savedCursorPosition = this.getTextPositionFromRange(inst.viewer, range);
            }

            // 소스 모드: 뷰어 숨기고 textarea 표시
            const content = '<div class="ra0-content">' + inst.viewer.innerHTML + '</div>';
            inst.source.value = content;
            inst.viewer.style.display = 'none';
            inst.source.style.display = 'block';

            // 저장된 위치로 커서 이동
            if (inst.savedCursorPosition !== undefined) {
                setTimeout(() => {
                    this.setCursorPositionInSource(inst.source, content, inst.savedCursorPosition);
                    inst.source.focus();
                }, 10);
            }
        } else {
            // 소스에서 뷰어로 전환: textarea 커서 위치 저장
            inst.savedSourcePosition = inst.source.selectionStart;

            // 뷰어 모드: textarea 숨기고 뷰어 표시
            let content = inst.source.value;

            // ra0-content wrapper 제거
            if (content.includes('class="ra0-content"')) {
                const temp = document.createElement('div');
                temp.innerHTML = content;
                const wrapper = temp.querySelector('.ra0-content');
                if (wrapper) {
                    content = wrapper.innerHTML;
                }
            }

            // HTML 주석 제거
            content = content.replace(/<!--[\s\S]*?-->/g, '');

            inst.viewer.innerHTML = content;
            inst.viewer.style.display = 'block';
            inst.source.style.display = 'none';

            // Prism.js 하이라이팅 재적용
            setTimeout(() => {
                if (window.Prism) {
                    Prism.highlightAllUnder(inst.viewer);
                }
            }, 10);

            // 저장된 위치로 뷰어 커서 이동
            if (inst.savedSourcePosition !== undefined) {
                setTimeout(() => {
                    this.setCursorPositionInViewer(inst.viewer, content, inst.savedSourcePosition);
                }, 20);
            }
        }
    },

    // 뷰어(contenteditable)에서 텍스트 위치 계산
    getTextPositionFromRange: function(container, range) {
        const walker = document.createTreeWalker(
            container,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );

        let charCount = 0;
        let node;

        while (node = walker.nextNode()) {
            if (node === range.startContainer) {
                return charCount + range.startOffset;
            }
            charCount += node.textContent.length;
        }

        return charCount;
    },

    // 소스(textarea)에 커서 설정
    setCursorPositionInSource: function(textarea, htmlContent, textPosition) {
        // HTML을 텍스트 위치로 변환
        let htmlPos = 0;
        let textPos = 0;

        while (htmlPos < htmlContent.length && textPos < textPosition) {
            if (htmlContent[htmlPos] === '<') {
                // HTML 태그 건너뛰기
                while (htmlPos < htmlContent.length && htmlContent[htmlPos] !== '>') {
                    htmlPos++;
                }
                if (htmlPos < htmlContent.length) htmlPos++;
            } else {
                // HTML 엔티티 처리
                if (htmlContent.substr(htmlPos, 4) === '&lt;' ||
                    htmlContent.substr(htmlPos, 4) === '&gt;') {
                    htmlPos += 4;
                    textPos++;
                } else if (htmlContent.substr(htmlPos, 5) === '&amp;') {
                    htmlPos += 5;
                    textPos++;
                } else if (htmlContent.substr(htmlPos, 6) === '&nbsp;') {
                    htmlPos += 6;
                    textPos++;
                } else {
                    htmlPos++;
                    textPos++;
                }
            }
        }

        textarea.setSelectionRange(htmlPos, htmlPos);
    },

    // 뷰어(contenteditable)에 커서 설정
    setCursorPositionInViewer: function(viewer, htmlContent, sourcePosition) {
        // 소스 위치를 텍스트 위치로 변환
        let htmlPos = 0;
        let textPos = 0;

        while (htmlPos < htmlContent.length && htmlPos < sourcePosition) {
            if (htmlContent[htmlPos] === '<') {
                while (htmlPos < htmlContent.length && htmlContent[htmlPos] !== '>') {
                    htmlPos++;
                }
                if (htmlPos < htmlContent.length) htmlPos++;
            } else {
                if (htmlContent.substr(htmlPos, 4) === '&lt;' ||
                    htmlContent.substr(htmlPos, 4) === '&gt;') {
                    htmlPos += 4;
                    textPos++;
                } else if (htmlContent.substr(htmlPos, 5) === '&amp;') {
                    htmlPos += 5;
                    textPos++;
                } else if (htmlContent.substr(htmlPos, 6) === '&nbsp;') {
                    htmlPos += 6;
                    textPos++;
                } else {
                    htmlPos++;
                    textPos++;
                }
            }
        }

        // 뷰어에서 텍스트 위치로 커서 설정
        const range = document.createRange();
        const selection = window.getSelection();

        const walker = document.createTreeWalker(
            viewer,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );

        let charCount = 0;
        let node;

        while (node = walker.nextNode()) {
            const nodeLength = node.textContent.length;
            if (charCount + nodeLength >= textPos) {
                const offset = Math.min(textPos - charCount, nodeLength);
                range.setStart(node, offset);
                range.collapse(true);

                selection.removeAllRanges();
                selection.addRange(range);
                break;
            }
            charCount += nodeLength;
        }
    },

    // 태그 정리 - br 기반으로 p 태그 생성
    // br 1개 = 줄바꿈 (같은 문단 내)
    // br 2개 연속 = 새 문단 (</p><p>)
    cleanupTags: function(id) {
        const viewer = document.getElementById(id + '_viewer');
        if (!viewer) return;

        const blockTags = new Set([
            'P', 'DIV', 'BLOCKQUOTE', 'PRE', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6',
            'UL', 'OL', 'TABLE', 'HR', 'DETAILS', 'SECTION', 'ARTICLE'
        ]);
        const hasMeaningfulNode = (element) => {
            const text = (element.textContent || '').replace(/\u00a0/g, '').trim();
            return text !== '' || !!element.querySelector('img, video, iframe, table, hr, details, .image-placeholder');
        };
        const normalizeEmptyParagraph = (paragraph) => {
            if (!hasMeaningfulNode(paragraph)) {
                paragraph.innerHTML = '<br>';
            }
        };

        const fragment = document.createDocumentFragment();
        let paragraph = null;
        const flushParagraph = () => {
            if (!paragraph) return;
            normalizeEmptyParagraph(paragraph);
            fragment.appendChild(paragraph);
            paragraph = null;
        };

        Array.from(viewer.childNodes).forEach(node => {
            if (node.nodeType === Node.TEXT_NODE && node.nodeValue.replace(/\u00a0/g, '').trim() === '') {
                return;
            }

            if (node.nodeType === Node.ELEMENT_NODE && blockTags.has(node.tagName)) {
                flushParagraph();
                if (node.tagName === 'P') {
                    normalizeEmptyParagraph(node);
                }
                fragment.appendChild(node);
                return;
            }

            if (!paragraph) {
                paragraph = document.createElement('p');
            }
            paragraph.appendChild(node);
        });
        flushParagraph();

        viewer.replaceChildren(fragment);

        let previousEmptyParagraph = false;
        Array.from(viewer.children).forEach(child => {
            if (child.tagName !== 'P') {
                previousEmptyParagraph = false;
                return;
            }

            const isEmpty = !hasMeaningfulNode(child);
            if (isEmpty && previousEmptyParagraph) {
                child.remove();
                return;
            }

            if (isEmpty) {
                child.innerHTML = '<br>';
            }
            previousEmptyParagraph = isEmpty;
        });

        // input 이벤트 발생 (동기화)
        viewer.dispatchEvent(new Event('input', { bubbles: true }));
    },

    // 컨텐츠 정규화 (들여쓰기 적용 전 호출)
    normalizeContent: function(id) {
        // cleanupTags를 호출
        this.cleanupTags(id);
    },

    // 자동 들여쓰기 토글 (모든 문단 첫 글자에 들여쓰기 적용/제거)
    toggleAutoIndent: function(id) {
        const viewer = document.getElementById(id + '_viewer');
        if (!viewer) return;

        // 먼저 태그 정리 (p 태그로 감싸기)
        this.normalizeContent(id);

        // 현재 들여쓰기 상태 확인 (첫 번째 p/div 기준)
        let firstBlock = null;
        for (const child of viewer.children) {
            const tag = child.tagName.toLowerCase();
            if (['p', 'div'].includes(tag)) {
                firstBlock = child;
                break;
            }
        }
        const isIndented = firstBlock && firstBlock.style.textIndent === '1em';

        // 모든 직계 자식에 들여쓰기 적용/제거
        Array.from(viewer.children).forEach(child => {
            // 제외할 태그들
            const tag = child.tagName.toLowerCase();
            if (['blockquote', 'pre', 'hr', 'details', 'ul', 'ol', 'table'].includes(tag)) {
                return;
            }
            // divider 클래스도 제외
            if (child.className && child.className.includes('divider')) {
                return;
            }

            if (isIndented) {
                // 들여쓰기 제거
                child.style.textIndent = '';
                if (!child.getAttribute('style') || child.getAttribute('style').trim() === '') {
                    child.removeAttribute('style');
                }
            } else {
                // 들여쓰기 적용
                child.style.textIndent = '1em';
            }
        });

        // input 이벤트 발생 (동기화)
        viewer.dispatchEvent(new Event('input', { bubbles: true }));

        // 버튼 활성 상태 토글
        const btn = document.querySelector(`#ra0_editor_${id} [data-action="autoIndent"]`);
        if (btn) {
            btn.classList.toggle('active', !isIndented);
        }
    },

    // ── Undo/Redo ──

    saveHistory: function(id) {
        const inst = this.instances[id];
        if (!inst || inst.isUndoRedo) return;

        const html = inst.viewer.innerHTML;

        // 같은 내용이면 저장하지 않음
        if (inst.history.length > 0 && inst.history[inst.historyIndex] === html) return;

        // 현재 위치 이후의 히스토리 삭제 (undo 후 새 입력 시)
        inst.history = inst.history.slice(0, inst.historyIndex + 1);
        inst.history.push(html);

        // 최대 50개
        if (inst.history.length > 50) {
            inst.history.shift();
        }

        inst.historyIndex = inst.history.length - 1;
    },

    undo: function(id) {
        const inst = this.instances[id];
        if (!inst || inst.historyIndex <= 0) return;

        inst.isUndoRedo = true;
        inst.historyIndex--;
        inst.viewer.innerHTML = inst.history[inst.historyIndex];
        inst.isUndoRedo = false;

        this.moveCursorToEnd(inst.viewer);
        this.syncToSource(id);
        this.updateCharCount(id);
    },

    redo: function(id) {
        const inst = this.instances[id];
        if (!inst || inst.historyIndex >= inst.history.length - 1) return;

        inst.isUndoRedo = true;
        inst.historyIndex++;
        inst.viewer.innerHTML = inst.history[inst.historyIndex];
        inst.isUndoRedo = false;

        this.moveCursorToEnd(inst.viewer);
        this.syncToSource(id);
        this.updateCharCount(id);
    },

    moveCursorToEnd: function(el) {
        const range = document.createRange();
        const sel = window.getSelection();
        range.selectNodeContents(el);
        range.collapse(false);
        sel.removeAllRanges();
        sel.addRange(range);
        el.focus();
    },

    // ── 소스 동기화 ──

    syncToSource: function(id) {
        const inst = this.instances[id];
        if (!inst || inst.sourceMode) return;

        // 불필요한 font-size: 1.1em inline style 자동 제거
        inst.viewer.querySelectorAll('[style*="font-size: 1.1em"], [style*="font-size:1.1em"]').forEach(el => {
            el.style.fontSize = '';
            if (!el.getAttribute('style') || el.getAttribute('style').trim() === '') {
                el.removeAttribute('style');
            }
        });

        inst.source.value = this.serializeViewerContent(inst.viewer);
    },

    // 글자 수 세기 (HTML 태그 제외, 순수 텍스트만)
    getCharCount: function(id) {
        const inst = this.instances[id];
        if (!inst) return 0;

        // 소스 모드일 때는 textarea 값에서 HTML 제거 후 계산
        let text;
        if (inst.sourceMode) {
            const temp = document.createElement('div');
            temp.innerHTML = inst.source.value;
            text = temp.textContent || temp.innerText || '';
        } else {
            text = inst.viewer.textContent || inst.viewer.innerText || '';
        }

        // 공백 제외 글자 수
        const charCountNoSpace = text.replace(/\s/g, '').length;
        // 공백 포함 글자 수
        const charCountWithSpace = text.length;

        return {
            withSpace: charCountWithSpace,
            noSpace: charCountNoSpace
        };
    },

    // 글자 수 UI 업데이트
    updateCharCount: function(id) {
        const countEl = document.getElementById('char_count_' + id);
        if (!countEl) return;

        const counts = this.getCharCount(id);
        countEl.textContent = `${counts.noSpace}자 (공백 포함: ${counts.withSpace}자)`;
    }
};
