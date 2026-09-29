/**
 * RA0 Notification System
 * alert(), confirm() 대체용 예쁜 알림 시스템
 */

const RA0Notify = {
    /**
     * 토스트 알림 (자동으로 사라짐)
     * @param {string} message - 메시지
     * @param {string} type - success, error, info, warning
     * @param {number} duration - 지속시간 (ms)
     */
    toast: function(message, type = 'info', duration = 3000) {
        const toast = document.createElement('div');
        toast.className = `ra0-toast ra0-toast-${type}`;

        const icons = {
            success: '✓',
            error: '✕',
            info: 'ℹ',
            warning: '⚠'
        };

        toast.innerHTML = `
            <div class="ra0-toast-icon">${icons[type] || icons.info}</div>
            <div class="ra0-toast-message">${message}</div>
        `;

        document.body.appendChild(toast);

        // 애니메이션
        setTimeout(() => toast.classList.add('show'), 10);

        // 자동 제거
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    /**
     * Confirm 모달 (Promise 반환)
     * @param {string} message - 메시지
     * @param {object} options - { title, confirmText, cancelText }
     * @returns {Promise<boolean>}
     */
    confirm: function(message, options = {}) {
        return new Promise((resolve) => {
            const {
                title = '알림',
                confirmText = '확인',
                cancelText = '취소'
            } = options;

            const overlay = document.createElement('div');
            overlay.className = 'ra0-modal-overlay';

            overlay.innerHTML = `
                <div class="ra0-modal">
                    <div class="ra0-modal-header">
                        <h3>${title}</h3>
                    </div>
                    <div class="ra0-modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="ra0-modal-footer">
                        <button class="ra0-btn ra0-btn-cancel">${cancelText}</button>
                        <button class="ra0-btn ra0-btn-confirm">${confirmText}</button>
                    </div>
                </div>
            `;

            document.body.appendChild(overlay);
            setTimeout(() => overlay.classList.add('show'), 10);

            const modal = overlay.querySelector('.ra0-modal');
            const btnConfirm = modal.querySelector('.ra0-btn-confirm');
            const btnCancel = modal.querySelector('.ra0-btn-cancel');

            const close = (result) => {
                overlay.classList.remove('show');
                setTimeout(() => overlay.remove(), 300);
                resolve(result);
            };

            btnConfirm.addEventListener('click', () => close(true));
            btnCancel.addEventListener('click', () => close(false));
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) close(false);
            });
        });
    },

    /**
     * Alert 모달
     * @param {string} message - 메시지
     * @param {object} options - { title, buttonText }
     * @returns {Promise<void>}
     */
    alert: function(message, options = {}) {
        return new Promise((resolve) => {
            const {
                title = '알림',
                buttonText = '확인'
            } = options;

            const overlay = document.createElement('div');
            overlay.className = 'ra0-modal-overlay';

            overlay.innerHTML = `
                <div class="ra0-modal">
                    <div class="ra0-modal-header">
                        <h3>${title}</h3>
                    </div>
                    <div class="ra0-modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="ra0-modal-footer">
                        <button class="ra0-btn ra0-btn-confirm">${buttonText}</button>
                    </div>
                </div>
            `;

            document.body.appendChild(overlay);
            setTimeout(() => overlay.classList.add('show'), 10);

            const modal = overlay.querySelector('.ra0-modal');
            const btnConfirm = modal.querySelector('.ra0-btn-confirm');

            const close = () => {
                overlay.classList.remove('show');
                setTimeout(() => overlay.remove(), 300);
                resolve();
            };

            btnConfirm.addEventListener('click', close);
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) close();
            });
        });
    }
};

// 전역으로 노출
window.RA0Notify = RA0Notify;
