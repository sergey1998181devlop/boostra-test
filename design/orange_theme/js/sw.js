self.addEventListener('push', function(event) {
    var notification = event.data.json();

    var icon = '/design/boostra_mini_norm/img/favicon144x144.png';

    event.waitUntil(
        self.registration.showNotification(notification.title, {
            body: notification.body,
            icon: icon,
            data: {
                notifyUrl: notification.url
            }
        })
    );
});

self.addEventListener('notificationclick', function(event) {
    event.notification.close();
    event.waitUntil(
        clients.openWindow(event.notification.data.notifyUrl)
    );
});