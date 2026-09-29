/**
 * RA0 Edition Service Worker — Web Push 알림 수신
 */

self.addEventListener('push', function(event) {
    var data = {};

    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data = { title: '새 알림', body: event.data.text() };
        }
    }

    var title = data.title || '새 알림';
    var options = {
        body: data.body || '',
        icon: data.icon || '/img/logo.png',
        badge: data.badge || '/img/logo.png',
        data: {
            url: data.url || '/'
        },
        tag: data.type || 'notification',
        renotify: true
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();

    var url = event.notification.data && event.notification.data.url
        ? event.notification.data.url
        : '/';

    event.waitUntil(
        // 기존 탭 focus 우선, 없으면 새 탭 열기
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(function(clientList) {
                // 동일 URL 탭이 있으면 focus
                for (var i = 0; i < clientList.length; i++) {
                    var client = clientList[i];
                    if (client.url === url && 'focus' in client) {
                        return client.focus();
                    }
                }

                // 같은 origin 탭이 있으면 navigate + focus
                for (var i = 0; i < clientList.length; i++) {
                    var client = clientList[i];
                    if ('navigate' in client) {
                        return client.navigate(url).then(function(c) {
                            return c.focus();
                        });
                    }
                }

                // 탭이 없으면 새로 열기
                return clients.openWindow(url);
            })
    );
});
