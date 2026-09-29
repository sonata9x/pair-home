/**
 * RA0 Edition Web Push 구독 관리
 *
 * Service Worker 등록 + Push 구독/해제 + 서버 동기화
 * 권한 요청은 사용자 제스처(버튼 클릭) 시에만 실행
 */

var RA0Push = {
    swRegistration: null,
    applicationServerKey: null,

    /**
     * 초기화: SW 등록 + 서버에서 공개키 로드
     * head.sub.php에서 호출됨
     */
    init: function(serverKey) {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            return;
        }

        // HTTPS 체크
        if (location.protocol !== 'https:') {
            return;
        }

        this.applicationServerKey = serverKey;

        navigator.serviceWorker.register(g5_url + '/sw.js')
            .then(function(reg) {
                RA0Push.swRegistration = reg;
                RA0Push.updateUI();
            })
            .catch(function() {
            });
    },

    /**
     * Push 구독 토글 (사용자 클릭에 의해 호출)
     */
    toggle: function() {
        if (!this.swRegistration) return;

        var self = this;

        this.swRegistration.pushManager.getSubscription()
            .then(function(subscription) {
                if (subscription) {
                    // 이미 구독 중 → 해제
                    self.unsubscribe(subscription);
                } else {
                    // 미구독 → 구독
                    self.subscribe();
                }
            });
    },

    /**
     * Push 구독 시작
     */
    subscribe: function() {
        var self = this;

        var serverKey = this.urlBase64ToUint8Array(this.applicationServerKey);

        this.swRegistration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: serverKey
        })
        .then(function(subscription) {
            // 서버에 구독 정보 전송
            self.sendSubscriptionToServer(subscription, 'subscribe');
        })
        .catch(function(err) {
            if (Notification.permission === 'denied') {
                if (typeof RA0Notify !== 'undefined') {
                    RA0Notify.toast('브라우저 설정에서 알림이 차단되어 있습니다.', 'warning');
                }
            }
            self.updateUI();
        });
    },

    /**
     * Push 구독 해제
     */
    unsubscribe: function(subscription) {
        var self = this;

        subscription.unsubscribe()
            .then(function() {
                self.sendSubscriptionToServer(subscription, 'unsubscribe');
            })
            .catch(function() {
                self.updateUI();
            });
    },

    /**
     * 서버에 구독 정보 전송
     */
    sendSubscriptionToServer: function(subscription, action) {
        var self = this;
        var keys = subscription.toJSON().keys || {};

        var body = 'action=' + action
            + '&endpoint=' + encodeURIComponent(subscription.endpoint)
            + '&auth=' + encodeURIComponent(keys.auth || '')
            + '&p256dh=' + encodeURIComponent(keys.p256dh || '');

        // CSRF 토큰이 있으면 포함
        if (typeof g5_csrf_token !== 'undefined' && g5_csrf_token) {
            body += '&token=' + encodeURIComponent(g5_csrf_token);
        }

        fetch(g5_bbs_url + '/ajax.push_subscribe.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                var msg = action === 'subscribe'
                    ? '브라우저 알림이 활성화되었습니다.'
                    : '브라우저 알림이 비활성화되었습니다.';
                if (typeof RA0Notify !== 'undefined') {
                    RA0Notify.toast(msg, 'success');
                }
            } else {
                if (typeof RA0Notify !== 'undefined') {
                    RA0Notify.toast(data.error || '처리 중 오류가 발생했습니다.', 'error');
                }
            }
            self.updateUI();
        })
        .catch(function() {
            self.updateUI();
        });
    },

    /**
     * UI 상태 갱신 (토글 버튼 등)
     */
    updateUI: function() {
        var toggle = document.getElementById('push-toggle');
        var status = document.getElementById('push-status');

        if (!toggle || !this.swRegistration) return;

        var self = this;

        this.swRegistration.pushManager.getSubscription()
            .then(function(subscription) {
                var isSubscribed = !!subscription;

                toggle.checked = isSubscribed;

                if (status) {
                    if (!('PushManager' in window)) {
                        status.textContent = '이 브라우저는 Push를 지원하지 않습니다.';
                    } else if (Notification.permission === 'denied') {
                        status.textContent = '브라우저 설정에서 알림이 차단되어 있습니다.';
                        toggle.disabled = true;
                    } else {
                        status.textContent = isSubscribed ? '알림 수신 중' : '알림 꺼짐';
                    }
                }
            });
    },

    /**
     * base64url → Uint8Array 변환 (applicationServerKey용)
     */
    urlBase64ToUint8Array: function(base64String) {
        var padding = '='.repeat((4 - base64String.length % 4) % 4);
        var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        var rawData = atob(base64);
        var outputArray = new Uint8Array(rawData.length);
        for (var i = 0; i < rawData.length; i++) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }
};
