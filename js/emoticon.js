/**
 * RA0 Edition - 이모티콘 자동완성
 * MSN 메신저 스타일 UI
 */
const RA0Emoticon = {
    cache: null,
    popup: null,
    autocomplete: null,
    currentTarget: null,
    savedRange: null,
    selectedIndex: -1,

    // 초기화
    init: function() {
        this.createPopup();
        this.createAutocomplete();
        this.loadEmoticons();
        this.bindEvents();
    },

    // 이모티콘 목록 로드 (캐싱)
    loadEmoticons: function() {
        if (this.cache) return Promise.resolve(this.cache);

        const apiUrl = (typeof g5_bbs_url !== 'undefined' ? g5_bbs_url : '/bbs') + '/emoticon_ajax.php';

        return fetch(apiUrl)
            .then(res => res.json())
            .then(data => {
                if (data.emoticons) {
                    this.cache = data.emoticons;
                }
                return this.cache || [];
            })
            .catch(() => {
                return [];
            });
    },

    // 전체 목록 팝업 생성
    createPopup: function() {
        if (document.getElementById('emoticon-popup')) return;

        const popup = document.createElement('div');
        popup.id = 'emoticon-popup';
        popup.className = 'emoticon-popup';
        popup.innerHTML = `
            <div class="emoticon-popup-header">
                <span class="emoticon-popup-title">이모티콘</span>
                <button type="button" class="emoticon-popup-close">&times;</button>
            </div>
            <div class="emoticon-popup-search">
                <input type="text" placeholder="검색..." autocomplete="off">
            </div>
            <div class="emoticon-popup-list"></div>
        `;
        document.body.appendChild(popup);
        this.popup = popup;

        // 닫기 버튼
        popup.querySelector('.emoticon-popup-close').addEventListener('click', () => {
            this.hidePopup();
        });

        // 검색 입력
        popup.querySelector('.emoticon-popup-search input').addEventListener('input', (e) => {
            this.renderPopupList(e.target.value);
        });

        // 이모티콘 클릭
        popup.querySelector('.emoticon-popup-list').addEventListener('click', (e) => {
            const item = e.target.closest('.emoticon-popup-item');
            if (item) {
                this.insertEmoticon(item.dataset.code);
                this.hidePopup();
            }
        });

        // 외부 클릭 시 닫기
        document.addEventListener('click', (e) => {
            if (this.popup.classList.contains('show') &&
                !this.popup.contains(e.target) &&
                !e.target.closest('[data-action="emoticon"]')) {
                this.hidePopup();
            }
        });
    },

    // 인라인 자동완성 생성
    createAutocomplete: function() {
        if (document.getElementById('emoticon-autocomplete')) return;

        const ac = document.createElement('div');
        ac.id = 'emoticon-autocomplete';
        ac.className = 'emoticon-autocomplete';
        document.body.appendChild(ac);
        this.autocomplete = ac;

        // 아이템 클릭
        ac.addEventListener('click', (e) => {
            const item = e.target.closest('.emoticon-autocomplete-item');
            if (item) {
                this.completeAutocomplete(item.dataset.code);
            }
        });
    },

    // 팝업 표시
    showPopup: function(targetElement) {
        this.currentTarget = targetElement;

        // 팝업 열기 전 커서 위치 저장 (팝업 클릭 시 포커스 이탈로 range가 리셋되는 문제 방지)
        if (targetElement.contentEditable === 'true') {
            const selection = window.getSelection();
            this.savedRange = (selection.rangeCount > 0) ? selection.getRangeAt(0).cloneRange() : null;
        } else {
            this.savedRange = null;
        }

        this.loadEmoticons().then(() => {
            const rect = targetElement.getBoundingClientRect();

            this.popup.style.top = (rect.bottom + window.scrollY + 5) + 'px';
            this.popup.style.left = Math.min(rect.left, window.innerWidth - 340) + 'px';
            this.popup.classList.add('show');

            this.renderPopupList('');
            this.popup.querySelector('.emoticon-popup-search input').value = '';
            this.popup.querySelector('.emoticon-popup-search input').focus();
        });
    },

    // 팝업 숨기기
    hidePopup: function() {
        if (this.popup) {
            this.popup.classList.remove('show');
        }
    },

    // 팝업 목록 렌더링
    renderPopupList: function(filter) {
        const list = this.popup.querySelector('.emoticon-popup-list');
        const emoticons = this.cache || [];

        let filtered = emoticons;
        if (filter) {
            const lowerFilter = filter.toLowerCase();
            filtered = emoticons.filter(emo =>
                emo.name.toLowerCase().includes(lowerFilter) ||
                emo.code.toLowerCase().includes(lowerFilter)
            );
        }

        if (filtered.length === 0) {
            list.innerHTML = '<div class="emoticon-popup-empty">검색 결과가 없습니다.</div>';
            return;
        }

        // 카테고리별 그룹화
        const categories = {};
        filtered.forEach(emo => {
            const cat = emo.category || '기본';
            if (!categories[cat]) categories[cat] = [];
            categories[cat].push(emo);
        });

        let html = '';
        for (const [cat, items] of Object.entries(categories)) {
            html += `<div class="emoticon-category">
                <div class="emoticon-category-title">${this.escapeHtml(cat)}</div>
                <div class="emoticon-popup-grid">`;
            items.forEach(emo => {
                html += `<div class="emoticon-popup-item" data-code="${this.escapeHtml(emo.name)}" title="${this.escapeHtml(emo.code)}">
                    <img src="${emo.image}" alt="${this.escapeHtml(emo.name)}">
                </div>`;
            });
            html += `</div></div>`;
        }

        list.innerHTML = html;
    },

    // 이모티콘 삽입
    insertEmoticon: function(name) {
        const target = this.currentTarget;
        if (!target) return;

        const text = '/' + name + ' ';

        // contenteditable (ra0-editor)
        if (target.contentEditable === 'true') {
            target.focus();
            const selection = window.getSelection();

            // 팝업 열기 전에 저장해둔 커서 위치 복원
            if (this.savedRange) {
                selection.removeAllRanges();
                selection.addRange(this.savedRange);
                this.savedRange = null;
            }

            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                range.deleteContents();
                const textNode = document.createTextNode(text);
                range.insertNode(textNode);
                range.setStartAfter(textNode);
                range.collapse(true);
                selection.removeAllRanges();
                selection.addRange(range);
            } else {
                target.innerHTML += text;
            }
            target.dispatchEvent(new Event('input', { bubbles: true }));
        }
        // textarea
        else if (target.tagName === 'TEXTAREA' || target.tagName === 'INPUT') {
            const start = target.selectionStart;
            const end = target.selectionEnd;
            target.value = target.value.substring(0, start) + text + target.value.substring(end);
            target.selectionStart = target.selectionEnd = start + text.length;
            target.focus();
        }
    },

    // 이벤트 바인딩
    bindEvents: function() {
        const self = this;

        // 에디터/textarea에 자동완성 바인딩
        document.querySelectorAll('.ra0-editor-viewer, textarea[name="wr_content"], textarea.emoticon-enabled').forEach(el => {
            self.bindAutocomplete(el);
        });

        // MutationObserver로 동적 요소 감지
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        const targets = node.querySelectorAll ? node.querySelectorAll('.ra0-editor-viewer, textarea[name="wr_content"]') : [];
                        targets.forEach(el => self.bindAutocomplete(el));

                        if (node.classList && (node.classList.contains('ra0-editor-viewer') ||
                            (node.tagName === 'TEXTAREA' && node.name === 'wr_content'))) {
                            self.bindAutocomplete(node);
                        }
                    }
                });
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    },

    // 자동완성 바인딩
    bindAutocomplete: function(element) {
        if (element.dataset.emoticonBound) return;
        element.dataset.emoticonBound = 'true';

        const self = this;
        let acActive = false;
        let acStart = 0;

        element.addEventListener('input', function(e) {
            self.loadEmoticons().then(() => {
                const text = self.getTextBeforeCursor(element);
                const match = text.match(/\/([가-힣a-zA-Z0-9_]*)$/);

                if (match) {
                    if (!acActive) {
                        acActive = true;
                        acStart = text.length - match[0].length;
                    }
                    self.currentTarget = element;
                    self.showAutocomplete(element, match[1]);
                } else {
                    if (acActive) {
                        acActive = false;
                        self.hideAutocomplete();
                    }
                }
            });
        });

        element.addEventListener('keydown', function(e) {
            if (!self.autocomplete.classList.contains('show')) return;

            if (e.key === 'Escape') {
                e.preventDefault();
                acActive = false;
                self.hideAutocomplete();
            } else if (e.key === 'Tab' || e.key === 'Enter') {
                const selected = self.autocomplete.querySelector('.emoticon-autocomplete-item.selected');
                if (selected) {
                    e.preventDefault();
                    self.completeAutocomplete(selected.dataset.code);
                    acActive = false;
                }
            } else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                self.navigateAutocomplete(e.key === 'ArrowDown' ? 1 : -1);
            }
        });

        element.addEventListener('blur', function(e) {
            setTimeout(() => {
                if (!self.autocomplete.contains(document.activeElement)) {
                    acActive = false;
                    self.hideAutocomplete();
                }
            }, 150);
        });
    },

    // 커서 앞 텍스트 가져오기
    getTextBeforeCursor: function(element) {
        if (element.tagName === 'TEXTAREA' || element.tagName === 'INPUT') {
            return element.value.substring(0, element.selectionStart);
        } else {
            // contenteditable
            const selection = window.getSelection();
            if (!selection.rangeCount) return '';

            const range = selection.getRangeAt(0);
            const preRange = range.cloneRange();
            preRange.selectNodeContents(element);
            preRange.setEnd(range.startContainer, range.startOffset);
            return preRange.toString();
        }
    },

    // 자동완성 표시
    showAutocomplete: function(element, keyword) {
        const emoticons = this.cache || [];
        const filtered = emoticons.filter(emo =>
            emo.name.toLowerCase().startsWith(keyword.toLowerCase()) ||
            emo.name.toLowerCase().includes(keyword.toLowerCase())
        ).slice(0, 8);

        if (filtered.length === 0) {
            this.hideAutocomplete();
            return;
        }

        let html = '';
        filtered.forEach((emo, i) => {
            html += `<div class="emoticon-autocomplete-item ${i === 0 ? 'selected' : ''}" data-code="${this.escapeHtml(emo.name)}">
                <img src="${emo.image}" alt="${this.escapeHtml(emo.name)}">
                <span class="emoticon-autocomplete-code">${this.escapeHtml(emo.code)}</span>
                <span class="emoticon-autocomplete-name">${this.escapeHtml(emo.name)}</span>
            </div>`;
        });
        this.autocomplete.innerHTML = html;

        // 위치 계산
        const rect = this.getCaretRect(element);
        this.autocomplete.style.top = (rect.bottom + 5) + 'px';
        this.autocomplete.style.left = Math.min(rect.left, window.innerWidth - 300) + 'px';
        this.autocomplete.classList.add('show');
        this.selectedIndex = 0;
    },

    // 자동완성 숨기기
    hideAutocomplete: function() {
        if (this.autocomplete) {
            this.autocomplete.classList.remove('show');
        }
        this.selectedIndex = -1;
    },

    // 자동완성 탐색
    navigateAutocomplete: function(direction) {
        const items = this.autocomplete.querySelectorAll('.emoticon-autocomplete-item');
        if (items.length === 0) return;

        items.forEach(item => item.classList.remove('selected'));

        this.selectedIndex += direction;
        if (this.selectedIndex < 0) this.selectedIndex = items.length - 1;
        if (this.selectedIndex >= items.length) this.selectedIndex = 0;

        items[this.selectedIndex].classList.add('selected');
    },

    // 자동완성 완료
    completeAutocomplete: function(name) {
        const target = this.currentTarget;
        if (!target) return;

        const text = '/' + name + ' ';

        if (target.tagName === 'TEXTAREA' || target.tagName === 'INPUT') {
            const value = target.value;
            const cursorPos = target.selectionStart;
            const match = value.substring(0, cursorPos).match(/\/[가-힣a-zA-Z0-9_]*$/);
            if (match) {
                const start = cursorPos - match[0].length;
                target.value = value.substring(0, start) + text + value.substring(cursorPos);
                target.selectionStart = target.selectionEnd = start + text.length;
                target.focus();
            }
        } else {
            // contenteditable
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                const textNode = range.startContainer;
                if (textNode.nodeType === Node.TEXT_NODE) {
                    const nodeText = textNode.textContent;
                    const match = nodeText.substring(0, range.startOffset).match(/\/[가-힣a-zA-Z0-9_]*$/);
                    if (match) {
                        const replaceStart = range.startOffset - match[0].length;
                        textNode.textContent = nodeText.substring(0, replaceStart) + text + nodeText.substring(range.startOffset);

                        const newRange = document.createRange();
                        newRange.setStart(textNode, replaceStart + text.length);
                        newRange.collapse(true);
                        selection.removeAllRanges();
                        selection.addRange(newRange);
                    }
                }
            }
            target.dispatchEvent(new Event('input', { bubbles: true }));
        }

        this.hideAutocomplete();
    },

    // 커서 좌표
    getCaretRect: function(element) {
        if (element.tagName === 'TEXTAREA' || element.tagName === 'INPUT') {
            const rect = element.getBoundingClientRect();
            return {
                top: rect.top + window.scrollY,
                bottom: rect.bottom + window.scrollY,
                left: rect.left
            };
        } else {
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                const rect = range.getBoundingClientRect();
                return {
                    top: rect.top + window.scrollY,
                    bottom: rect.bottom + window.scrollY,
                    left: rect.left
                };
            }
            const rect = element.getBoundingClientRect();
            return {
                top: rect.top + window.scrollY,
                bottom: rect.top + window.scrollY + 20,
                left: rect.left
            };
        }
    },

    // HTML 이스케이프
    escapeHtml: function(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
};

// 페이지 로드 시 초기화
document.addEventListener('DOMContentLoaded', function() {
    RA0Emoticon.init();
});
