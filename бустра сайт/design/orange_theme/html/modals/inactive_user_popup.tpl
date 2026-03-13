<style>
    #inactive-user-popup {
        display: none; /* По умолчанию скрыт */
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7); /* Полупрозрачный черный фон */
        z-index: 999; /* Ниже баннера, но выше остального контента */
        justify-content: center;
        align-items: center;
        animation: fadeIn 0.5s ease-out forwards; /* Анимация появления контейнера */
    }

    /* Анимация появления контейнера */
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    /* Основные стили для баннера */
    .banner {
        background: linear-gradient(135deg, #4facfe, #038aee);
        color: white;
        padding: 20px;
        position: relative;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        max-width: 360px;
        font-family: Arial, sans-serif;
        z-index: 1000;
        transform: scale(0); /* Начальное состояние: уменьшен */
        animation: scaleIn 0.5s ease-out 1s forwards; /* Анимация scale с задержкой 1 секунда */
    }

    /* Анимация увеличения баннера */
    @keyframes scaleIn {
        from {
            transform: scale(0);
        }
        to {
            transform: scale(1);
        }
    }

    /* Заголовок баннера */
    .banner h2 {
        margin: 0;
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    /* Текст предложения */
    .banner p {
        margin: 0;
        font-size: 16px;
        line-height: 1.5;
        margin-bottom: 20px;
    }

    /* Кнопка "Получить" */
    .banner a {
        background: white;
        color: #4facfe;
        border: none;
        padding: 12px 24px;
        font-size: 16px;
        font-weight: bold;
        border-radius: 25px;
        cursor: pointer;
        width: 100%;
        transition: background 0.3s ease, transform 0.2s ease;
        text-decoration: none;
        display: block;
        text-align: center;
    }

    /* Эффект при наведении на кнопку */
    .banner a:hover {
        background: #f0f0f0;
        transform: scale(1.05);
    }

    /* Эффект при нажатии на кнопку */
    .banner a:active {
        transform: scale(0.95);
    }

    .inactive-user-popup_btn {
        display: flex;
        justify-content: end;
        width: 100%;
        cursor: initial;
    }

    .inactive-user-popup_btn__svg {
        cursor: pointer;
        padding-bottom: .5em;
    }
</style>
<div id="inactive-user-popup">
    <!-- Всплывающий баннер -->
    <div class="banner">
        <button class="inactive-user-popup_btn">
            <svg onclick="closePopup()" class="inactive-user-popup_btn__svg" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <h2>Персональное предложение</h2>
        <p>Заем 25 000 р. под 0% на 16 дней</p>
        <a onclick="return onPopupClick(this)" href="/init_user?amount=25000&period=16">Получить</a>
    </div>
</div>
<script type="text/javascript">
    // Пример JavaScript для показа попапа через 10 секунд неактивности
    let inactivityTime = 0;
    const popup = document.getElementById('inactive-user-popup');

    function showPopup() {
        popup.style.display = 'flex'; // Показываем попап
        checkFloatingHeroBtnVisibility(); // Показывать/скрывать кнопку на главной
    }

    function closePopup() {
        clearInterval(popUpInterval);
        popup.remove();
        checkFloatingHeroBtnVisibility(); // Показывать/скрывать кнопку на главной
    }

    function resetInactivityTimer() {
        inactivityTime = 0;
    }

    function onPopupClick(el) {
        submitOrder(el.href);
        return false;
    }

    // Закрытие попапа при клике вне его области
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.banner') && popup.style.display === 'flex') {
            closePopup();
        }
    });

    // Таймер неактивности
    /*
    let popUpInterval = setInterval(() => {
        inactivityTime++;
        if (inactivityTime > 10) { // 10 секунд неактивности
            showPopup();
            clearInterval(popUpInterval);
        }
    }, 1000);
*/
    // Сброс таймера при взаимодействии с сайтом
    document.addEventListener('mousemove', resetInactivityTimer);
    document.addEventListener('keypress', resetInactivityTimer);
    document.addEventListener('click', resetInactivityTimer);
</script>
