if (window.$ !== undefined) {
    function urlBase64ToUint8Array(base64String) {
        const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, "+")
            .replace(/_/g, "/");

        const rawData = atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; i++) {
            outputArray[i] = rawData.charCodeAt(i);
        }

        return outputArray;
    }

    function requestPermission(registration) {
        if (Notification.permission === 'granted') {
            subscribeToNotifications(registration);
            return;
        }

        Notification.requestPermission().then(function(permission) {
            if (permission === 'granted' && navigator.serviceWorker) {
                if (registration.active) {
                    subscribeToNotifications(registration);
                }
            }
        });
    }

    function subscribeToNotifications(registration) {
        registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(window.applicationServerKey)
        }).then(function(subscription) {
            const formData = new FormData();
            formData.append('subscription', JSON.stringify(subscription));

            return fetch('/user?action=notification_subscribe', {
                method: 'POST',
                body: formData,
            });
        }).then(function(response) {
            return response.json().then(function(data) {
                if (data.success) {
                    console.log('Subscription data successfully sent to the server. Response:', data);
                } else {
                    console.error('Failed to send subscription data to the server. Response:', data);
                }
            });
        }).catch(function(error) {
            console.error('Error during push subscription:', error);
        });
    }

    $(document).ready(function () {
        if (window.registration && Notification.permission !== "denied") {
            requestPermission(window.registration);
        }
    });
}