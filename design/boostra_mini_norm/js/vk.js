const VKID = window.VKIDSDK;
const VK_APP_ID = 51920149;

VKID.Config.set({
    app: VK_APP_ID, // Идентификатор приложения.
    redirectUrl: location.protocol + '//' + location.host + location.pathname, // Адрес для перехода после авторизации.
});

// Создание экземпляра кнопки.
const oneTap = new VKID.OneTap();

// Получение контейнера из разметки.
const container = document.getElementById('js-vkid-onetap');
if (container) {
    if (container) {
        oneTap.render({
            container,
            scheme: 'light',
            lang: 0,
            skin: 'primary'
        });
    }
}