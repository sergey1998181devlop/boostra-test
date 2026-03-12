if (window.$ !== undefined) {
    $(document).ready(function () {
        // Функция для получения куки по имени
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }

        // Функция для установки куки
        function setCookie(name, value, days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/`;
        }

        // Показываем уведомление через 60 секунд если пользователь его не закрыл ранее
        setTimeout(() => {
            if (!getCookie('close-contact-me-notice')) {
                $('#contact-me-notice').show();
            }
        }, 60000);

        // Обработка нажатия кнопки "Связаться со мной"
        $('#contact-me-button').on('click', function () {
            $('#contact-me-text').hide();
            $('#contact-me-button').hide();
            $('#contact-me-wait').show();

            $.post('/user?action=contact_me');

            setTimeout(function () {
                $('#contact-me-notice').hide(400);
                setCookie('close-contact-me-notice', 'true', 30); // Сохраняем куку на 30 дней
            }, 2000);
        });

        // Обработка нажатия кнопки закрытия уведомления
        $('#close-notice-button').on('click', function () {
            $('#contact-me-notice').hide(400);
            setCookie('close-contact-me-notice', 'true', 30); // Сохраняем куку на 30 дней
        });
    });
}