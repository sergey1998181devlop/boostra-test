{$meta_title = "Способ получения займа" scope=parent}


<div id="sbp-bank-selection-container">
    <div class="sbp-selection-wrapper">
        <div class="sbp-header">
            <h2 class="sbp-title">Способ получения займа</h2>
        </div>

        <div class="sbp-notification">
            <div class="sbp-notification-content">
                <div class="sbp-notification-title">Для получения займа, пожалуйста, укажите свой банк</div>
                <div class="sbp-notification-text">
                    Убедитесь, что в банковском приложении включена функция "Разрешить входящие переводы по СБП". Не обязательно устанавливать банк основным.
                </div>
            </div>
        </div>

        <div class="sbp-method-selection">
            <!-- Статический способ получения СБП -->
            <div class="sbp-payment-method-static">
                <div class="sbp-payment-icon">
                    <img src="design/boostra_mini_norm/img/sbp_logo.png" alt="СБП" class="sbp-logo">
                </div>
                <span class="sbp-payment-text">Система Быстрых Платежей</span>
            </div>

            <!-- Выпадающий список банков СБП -->
            <div class="sbp-banks-container" id="sbp-banks-container">
                <div class="sbp-bank-dropdown">
                    <div class="sbp-bank-selected" id="sbp-bank-selected">
                        <span class="sbp-selected-text">Выберите банк для получения займа</span>
                        <svg class="sbp-dropdown-arrow" width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 1L6 6L11 1" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    <div class="sbp-bank-dropdown-list" id="sbp-bank-dropdown-list" style="display: none;">
                        <!-- Поле поиска банков -->
                        <div class="sbp-bank-search">
                            <input type="text"
                                   id="sbp-bank-search-input"
                                   placeholder="Поиск банка..."
                                   class="sbp-search-input"
                                   autocomplete="off">
                            <div class="sbp-search-icon">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M21 21L16.514 16.506M19 10.5C19 15.194 15.194 19 10.5 19S2 15.194 2 10.5 5.806 2 10.5 2 19 5.806 19 10.5Z" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Список банков -->
                        <div class="sbp-banks-list" id="sbp-banks-list">
                            {if !empty($sbp_banks)}
                                {foreach $sbp_banks as $bank}
                                    <div class="sbp-bank-option" data-bank-id="{$bank->id}" data-bank-title="{$bank->title|escape}">
                                        <div class="sbp-bank-logo">
                                            <img src="https://sub.nspk.ru/proxyapp/logo/bank{$bank->id}.png"
                                                 alt="{$bank->title|escape}">
                                        </div>
                                        <span class="sbp-bank-title">{$bank->title}</span>
                                        <svg width="15" height="22" viewBox="0 0 15 22" style="width: 9px;">
                                            <path fill="currentColor" fill-opacity="0.7"
                                                  d="m4.431 21.4057 9.896-8.745c.8973-.7929.8973-2.0786 0-2.8715L3.922.5943C3.0253-.198 1.5692-.198.6725.5943s-.8967 2.0791 0 2.8715L9.453 11.225l-8.2713 7.3093c-.8967.7924-.8967 2.0791 0 2.8715.8967.7924 2.3527.7924 3.2494 0Z"></path>
                                        </svg>
                                    </div>
                                {/foreach}
                                <!-- Блок "Банк не найден" -->
                                <div class="sbp-no-results" id="sbp-no-results" style="display: none;">
                                    <p>Банк не найден</p>
                                    <small>Попробуйте изменить запрос</small>
                                </div>
                            {else}
                                <!-- Заглушка если банки не загружены -->
                                <div class="sbp-no-banks">
                                    <p>Список банков СБП загружается...</p>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Предупреждение о номере телефона -->
        <div class="sbp-warning">
            <div class="sbp-warning-icon">⚠️</div>
            <div class="sbp-warning-content">
                <div class="sbp-warning-title">Внимание!</div>
                <div class="sbp-warning-text">
                    Убедитесь, что привязанный номер телефона
                    <strong>{if $user->phone_mobile}{$user->phone_mobile}{else}указанный в заявке{/if}</strong>
                    совпадает с номером телефона СБП в выбранном банке!
                </div>
            </div>
        </div>

        <!-- Кнопка продолжить -->
        <div class="sbp-continue-section">
            <button id="sbp-continue-btn" class="sbp-continue-button" disabled>
                Продолжить
            </button>
        </div>
    </div>
</div>

<style>
    {literal}
    * {
        box-sizing: border-box;
    }

    #sbp-bank-selection-container {
        /* background: #f8f9fa; */
        min-height: 100vh;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }

    .sbp-selection-wrapper {
        max-width: 650px;
        margin: 0 auto;
        border-radius: 16px;
    }

    /* Заголовок */
    .sbp-header {
        text-align: center;
        margin-bottom: 24px;
    }


    .sbp-title {
        font-size: 24px;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
        line-height: 1.2;
    }

    .sbp-notification {
        background: #98FB98;
        border: 1px solid #006400;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 24px;
        text-align: left;
    }

    .sbp-notification-title {
        font-weight: 600;
        color: black;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .sbp-notification-text {
        color: black;
        font-size: 13px;
        line-height: 1.4;
    }

    .sbp-method-selection {
        margin-bottom: 12px;
    }

    .sbp-payment-method-static {
        display: flex;
        align-items: center;
        padding: 16px;
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        margin-bottom: 16px;
    }

    .sbp-payment-icon {
        width: 70px;
        height: 40px;
        margin-right: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sbp-payment-text {
        flex: 1;
        font-size: 14px;
        font-weight: 600;
        color: #1a1a1a;
    }

    .sbp-logo {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .sbp-banks-container {
        margin-top: 16px;
        padding: 0;
        background: #f8f9fa;
        border-radius: 12px;
        border: 1px solid #e0e0e0;
    }

    .sbp-bank-dropdown {
        position: relative;
    }

    .sbp-bank-selected {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 16px;
        color: #333;
    }

    .sbp-bank-selected:hover {
        border-color: #2196f3;
        background-color: #f3f8ff;
    }

    .sbp-bank-selected.active {
        border-color: #2196f3;
        background-color: #f3f8ff;
    }

    .sbp-bank-selected.active .sbp-dropdown-arrow {
        transform: rotate(180deg);
    }

    .sbp-selected-text {
        font-size: 14px;
        font-weight: 600;
        flex: 1;
    }

    .sbp-dropdown-arrow {
        margin-left: 12px;
        transition: transform 0.2s ease;
    }

    .sbp-bank-dropdown-list {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 2px solid #2196f3;
        border-radius: 12px;
        margin-top: 4px;
        max-height: 350px;
        overflow: hidden;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(33,150,243,0.15);
    }

    .sbp-bank-search {
        position: relative;
        padding: 12px;
        border-bottom: 1px solid #e0e0e0;
        background: #f8f9fa;
        border-radius: 12px 12px 0 0;
    }

    .sbp-search-input {
        width: 100%;
        padding: 10px 40px 10px 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        background: white;
        outline: none;
        transition: border-color 0.2s ease;
        box-sizing: border-box;
        /* Предотвращаем масштабирование на Android при фокусе */
        font-size: 16px;
    }

    .sbp-search-input:focus {
        border-color: #2196f3;
        box-shadow: 0 0 0 2px rgba(33,150,243,0.1);
    }

    .sbp-search-icon {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .sbp-banks-list {
        height: 280px;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 0;
        margin: 0;
        -webkit-overflow-scrolling: touch;
        touch-action: pan-y;
        position: relative;
        scrollbar-width: thin;
        overscroll-behavior: contain;
        /* Дополнительные свойства для стабильности */
        will-change: scroll-position;
        contain: layout;
        /* Специально для Android */
        -webkit-transform: translate3d(0,0,0);
        transform: translate3d(0,0,0);
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
    }

    .sbp-banks-list::-webkit-scrollbar {
        width: 6px;
    }

    .sbp-banks-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .sbp-banks-list::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .sbp-banks-list::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    .sbp-bank-option {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        min-height: 56px;
        flex-shrink: 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .sbp-bank-option:hover {
        background-color: #f3f8ff;
    }

    .sbp-bank-option.selected {
        background-color: #2196f3;
        color: white;
    }

    .sbp-bank-logo {
        width: 40px;
        height: 28px;
        margin-right: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sbp-bank-logo img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .sbp-bank-title {
        flex: 1;
        font-size: 14px;
        font-weight: 500;
        color: inherit;
    }

    .sbp-bank-option svg {
        margin-left: 8px;
        opacity: 0.7;
    }

    .sbp-bank-option.selected .sbp-bank-title {
        color: white;
    }

    .sbp-bank-option.selected svg path {
        fill: white;
    }

    .sbp-no-banks {
        text-align: center;
        padding: 20px;
        color: #666;
        font-style: italic;
    }

    .sbp-warning {
        display: flex;
        align-items: flex-start;
        padding: 16px;
        background: #fff5f5;
        border: 1px solid #ffcdd2;
        border-radius: 12px;
        text-align: left;
    }

    .sbp-warning-icon {
        font-size: 20px;
        margin-right: 12px;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .sbp-warning-content {
        flex: 1;
    }

    .sbp-warning-title {
        font-size: 14px;
        font-weight: 600;
        color: #d32f2f;
        margin-bottom: 4px;
    }

    .sbp-warning-text {
        font-size: 13px;
        color: #d32f2f;
        line-height: 1.4;
    }

    .sbp-dropdown-arrow {
        margin-left: 8px;
        flex-shrink: 0;
    }

    /* Кнопка продолжить */
    .sbp-continue-section {
        text-align: center;
        margin-top: 24px;
    }

    .sbp-continue-button {
        width: 100%;
        max-width: 300px;
        padding: 16px 24px;
        background: #2196f3;
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .sbp-continue-button:hover:not(:disabled) {
        background: #1976d2;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(33,150,243,0.3);
    }

    .sbp-continue-button:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    @media (max-width: 768px) {
        #sbp-bank-selection-container {
            padding: 12px;
        }

        .sbp-title {
            font-size: 20px;
        }

        .sbp-bank-selected {
            font-size: 14px;
            padding: 12px;
        }

        .sbp-bank-dropdown-list {
            max-height: 300px;
        }

        .sbp-banks-list {
            height: 220px;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            touch-action: pan-y;
            overscroll-behavior: contain;
            position: relative;
        }

        .sbp-bank-option {
            padding: 12px;
            min-height: 48px;
        }

        .sbp-bank-title {
            font-size: 13px;
        }

        .sbp-continue-button {
            font-size: 14px;
            padding: 14px 20px;
        }
    }

    @media (max-width: 480px) {
        .sbp-bank-selected {
            font-size: 13px;
            padding: 10px;
        }

        .sbp-bank-dropdown-list {
            max-height: 260px;
        }

        .sbp-banks-list {
            height: 180px;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            touch-action: pan-y;
            overscroll-behavior: contain;
            position: relative;
        }

        .sbp-banks-list::-webkit-scrollbar {
            width: 3px;
        }

        .sbp-banks-list::-webkit-scrollbar-track {
            background: #f5f5f5;
            border-radius: 1px;
        }

        .sbp-banks-list::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 1px;
        }

        .sbp-bank-option {
            padding: 12px 10px;
            min-height: 44px;
        }

        .sbp-bank-title {
            font-size: 12px;
        }

        .sbp-search-input {
            font-size: 16px;
        }
    }

    @media (max-width: 320px) {
        .sbp-bank-dropdown-list {
            max-height: 240px;
        }

        .sbp-banks-list {
            height: 160px;
            overflow-y: auto;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            touch-action: pan-y;
            overscroll-behavior: contain;
            position: relative;
        }

        .sbp-bank-option {
            padding: 10px 8px;
            min-height: 42px;
        }
    }

    /* Специальные стили для Android устройств */
    .android-device .sbp-banks-list {
        -webkit-transform: translate3d(0,0,0) !important;
        transform: translate3d(0,0,0) !important;
        will-change: scroll-position !important;
        contain: layout style paint !important;
        -webkit-backface-visibility: hidden !important;
        backface-visibility: hidden !important;
        -webkit-perspective: 1000px !important;
        perspective: 1000px !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        -webkit-overflow-scrolling: touch !important;
        touch-action: pan-y !important;
        overscroll-behavior: contain !important;
    }

    /* Добавляем невидимый элемент для Android чтобы активировать скролл */
    .android-device .sbp-banks-list::after {
        content: '';
        display: block;
        height: 1px;
        width: 100%;
        opacity: 0;
        pointer-events: none;
    }

    /* Фикс для прокрутки при открытой клавиатуре на Android */
    .android-device.keyboard-open .sbp-bank-dropdown-list {
        position: fixed !important;
        top: 60px !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        max-height: none !important;
        height: calc(100vh - 60px) !important;
        margin: 0 !important;
        border-radius: 0 !important;
        z-index: 10000 !important;
    }

    .android-device.keyboard-open .sbp-banks-list {
        height: calc(100% - 60px) !important;
        max-height: none !important;
        overflow-y: scroll !important;
        -webkit-overflow-scrolling: touch !important;
        /* Добавляем pointer-events для гарантии обработки касаний */
        pointer-events: auto !important;
    }

    .android-device.keyboard-open .sbp-bank-search {
        position: sticky !important;
        top: 0 !important;
        z-index: 10 !important;
        background: #f8f9fa !important;
    }

    /* Альтернативный стиль для открытой клавиатуры */
    .android-keyboard-visible .sbp-banks-list {
        /* Принудительно активируем pointer-events */
        pointer-events: auto !important;
        touch-action: pan-y !important;
        /* Создаем новый stacking context */
        z-index: 9999 !important;
        position: relative !important;
    }

    /* Добавляем невидимый слой для активации скролла */
    .android-keyboard-visible .sbp-banks-list::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: -1;
        pointer-events: none;
    }

    {/literal}
</style>

<script>
    {literal}
    $(document).ready(function() {

        // Определяем Android и добавляем класс
        const isAndroid = /Android/i.test(navigator.userAgent);
        if (isAndroid) {
            $('body').addClass('android-device');
            $('#sbp-bank-selection-container').addClass('android-device');

            // Отслеживаем изменение высоты viewport (клавиатура)
            let initialViewportHeight = window.innerHeight;
            let keyboardVisible = false;

            $(window).on('resize', function() {
                const currentHeight = window.innerHeight;
                const heightDifference = initialViewportHeight - currentHeight;

                // Если высота уменьшилась больше чем на 100px - скорее всего клавиатура открыта
                if (heightDifference > 100 && !keyboardVisible) {
                    keyboardVisible = true;
                    $('body').addClass('android-keyboard-visible');

                    // Активируем скролл принудительно
                    const banksList = document.getElementById('sbp-banks-list');
                    if (banksList) {
                        banksList.style.pointerEvents = 'auto';
                        banksList.style.touchAction = 'pan-y';
                        banksList.style.overflowY = 'scroll';
                        banksList.style.webkitOverflowScrolling = 'touch';
                    }
                } else if (heightDifference < 50 && keyboardVisible) {
                    keyboardVisible = false;
                    $('body').removeClass('android-keyboard-visible');
                }
            });
        }

        let selectedBankId = null;
        let selectedBankTitle = null;
        let selectedPaymentType = 'sbp'; // Всегда СБП

        // Обработка клика по выпадающему списку банков
        $('#sbp-bank-selected').click(function() {
            $(this).toggleClass('active');
            $('#sbp-bank-dropdown-list').slideToggle(200, function() {
                // Фокус на поле поиска при открытии списка
                if ($('#sbp-bank-dropdown-list').is(':visible')) {
                    // Для Android активируем скролл сразу
                    setTimeout(function() {
                        const banksList = document.getElementById('sbp-banks-list');
                        if (banksList) {
                            // Для Android активируем скролл несколькими способами
                            if (isAndroid) {
                                // Способ 1: Проверяем есть ли уже активатор
                                if (!banksList.querySelector('.android-scroll-activator')) {
                                    const tempElement = document.createElement('div');
                                    tempElement.style.height = '2px'; // Увеличиваем до 2px
                                    tempElement.style.visibility = 'hidden';
                                    tempElement.style.pointerEvents = 'none';
                                    tempElement.className = 'android-scroll-activator';

                                    // Добавляем в конец списка
                                    banksList.appendChild(tempElement);
                                }

                                // Способ 2: Принудительно устанавливаем overflow
                                banksList.style.overflowY = 'scroll';
                                banksList.style.webkitOverflowScrolling = 'touch';

                                // Способ 3: Микро-прокрутка
                                setTimeout(() => {
                                    banksList.scrollTop = 1;
                                    setTimeout(() => {
                                        banksList.scrollTop = 0;
                                    }, 10);
                                }, 50);
                            }

                            // Фокус на поиск только на десктопе
                            if (window.innerWidth > 768 && !isAndroid) {
                                $('#sbp-bank-search-input').focus();
                            }
                        }
                    }, 150);
                }
            });
        });

        // Обработка выбора банка
        $(document).on('click', '.sbp-bank-option', function(e) {
            e.preventDefault();

            $('.sbp-bank-option').removeClass('selected');
            $(this).addClass('selected');

            selectedBankId = $(this).data('bank-id');
            selectedBankTitle = $(this).data('bank-title');

            updateSelectedBankDisplay(selectedBankId, selectedBankTitle);

            $('#sbp-bank-selected').removeClass('active');
            $('#sbp-bank-dropdown-list').slideUp(200);

            // Очищаем поле поиска
            $('#sbp-bank-search-input').val('');
            filterBanks('');

            checkFormValidity();
        });

        // Обработка поиска банков с активацией прокрутки
        $('#sbp-bank-search-input').on('input', function() {
            const searchTerm = $(this).val().toLowerCase().trim();
            filterBanks(searchTerm);

            // Активация прокрутки после фильтрации
            setTimeout(function() {
                const banksList = document.getElementById('sbp-banks-list');
                if (banksList) {
                    banksList.style.overflowY = 'auto';
                    banksList.style.webkitOverflowScrolling = 'touch';
                    banksList.scrollTop += 1;
                    banksList.scrollTop -= 1;
                }
            }, 50);
        });

        // Предотвращаем закрытие списка при клике по полю поиска
        $('#sbp-bank-search-input').on('click focus', function(e) {
            e.stopPropagation();

            // Для Android - обрабатываем открытие клавиатуры
            if (isAndroid) {
                // Добавляем класс при фокусе
                $('body').addClass('keyboard-open');

                // Перемещаем список в фиксированную позицию
                setTimeout(function() {
                    const banksList = document.getElementById('sbp-banks-list');
                    if (banksList) {
                        // Принудительно активируем скролл
                        banksList.style.overflowY = 'scroll';
                        banksList.style.webkitOverflowScrolling = 'touch';
                        banksList.style.touchAction = 'pan-y';

                        // Принудительно добавляем transform для активации аппаратного ускорения
                        banksList.style.transform = 'translateZ(0)';
                    }
                }, 200);
            }
        });

        // Убираем класс при потере фокуса
        $('#sbp-bank-search-input').on('blur', function() {
            if (isAndroid) {
                setTimeout(function() {
                    $('body').removeClass('keyboard-open');
                }, 300);
            }
        });

        // Обработка клавиш в поле поиска
        $('#sbp-bank-search-input').keydown(function(e) {
            // ESC - закрываем список
            if (e.keyCode === 27) {
                $('#sbp-bank-selected').removeClass('active');
                $('#sbp-bank-dropdown-list').slideUp(200);
                $(this).val('');
                filterBanks('');
            }
            // Enter - выбираем первый видимый банк
            else if (e.keyCode === 13) {
                const firstVisibleBank = $('.sbp-bank-option:visible').first();
                if (firstVisibleBank.length) {
                    firstVisibleBank.click();
                }
                e.preventDefault();
            }
        });

        // Функция фильтрации банков
        function filterBanks(searchTerm) {
            let visibleCount = 0;

            $('.sbp-bank-option').each(function() {
                const bankTitle = $(this).data('bank-title').toLowerCase();

                if (bankTitle.includes(searchTerm)) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            // Показываем/скрываем сообщение "Ничего не найдено"
            if (visibleCount === 0 && searchTerm !== '') {
                $('#sbp-no-results').show();
            } else {
                $('#sbp-no-results').hide();
            }
        }

        // Функция обновления отображения выбранного банка
        function updateSelectedBankDisplay(bankId, bankTitle) {
            const selectedElement = $('#sbp-bank-selected');

            const newContent = `
            <div style="display: flex; align-items: center; flex: 1;">
                <div class="sbp-bank-logo" style="width: 40px; height: 28px; margin-right: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <img src="https://sub.nspk.ru/proxyapp/logo/bank${bankId}.png"
                         alt="${bankTitle}"
                         style="max-width: 100%; max-height: 100%; object-fit: contain;">
                </div>
                <span class="sbp-selected-text" style="flex: 1;">${bankTitle}</span>
            </div>
            <svg class="sbp-dropdown-arrow" width="12" height="8" viewBox="0 0 12 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 1L6 6L11 1" stroke="#666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        `;

            selectedElement.html(newContent);
        }

        // Закрытие списков при клике вне них
        $(document).click(function(e) {
            if (!$(e.target).closest('.sbp-bank-dropdown').length) {
                $('#sbp-bank-selected').removeClass('active');
                $('#sbp-bank-dropdown-list').slideUp(200);
                // Очищаем поле поиска при закрытии
                $('#sbp-bank-search-input').val('');
                filterBanks('');

                // Удаляем временные элементы для Android
                if (isAndroid) {
                    $('.android-scroll-activator').remove();
                }
            }
        });

        // Проверка валидности формы
        function checkFormValidity() {
            const isBankSelected = selectedBankId !== null;

            if (isBankSelected) {
                $('#sbp-continue-btn').prop('disabled', false);
            } else {
                $('#sbp-continue-btn').prop('disabled', true);
            }
        }

        // Обработка кнопки продолжить
        $('#sbp-continue-btn').click(function() {
            if ($(document).find('.js-need-verify').not(':checked').length > 0) {
                $(document).find('#not_checked_info').show();
                return
            }

            if (!selectedBankId) {
                alert('Выберите банк для получения займа');
                return;
            }

            // Блокируем кнопку и показываем индикатор загрузки
            $('#sbp-continue-btn').prop('disabled', true).text('Сохранение...');

            // Отправка данных на сервер
            $.ajax({
                url: 'ajax/choose_card.php',
                data: {
                    action: 'choose_bank',
                    bank_id: selectedBankId,
                    order_id: {/literal}{$order->id}{literal}
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        // Показываем успешное сообщение
                        $('#sbp-continue-btn').text('Банк сохранен!');

                        // Через 1 секунду перезагружаем страницу для продолжения процесса
                        setTimeout(function() {
                            window.location.reload();
                        }, 500);
                    } else {
                        alert(response.message || 'Произошла ошибка при выборе банка СБП');
                        $('#sbp-continue-btn').prop('disabled', false).text('Продолжить');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    console.error('Response:', xhr.responseText);

                    let errorMessage = 'Произошла ошибка при обработке запроса.';

                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {

                    }

                    alert(errorMessage);
                    $('#sbp-continue-btn').prop('disabled', false).text('Продолжить');
                }
            });
        });


        // Инициализация: показываем контейнер с банками СБП по умолчанию
        $('#sbp-banks-container').show();

        // Специальное решение для Android - гарантированная активация скролла
        if (isAndroid) {
            // Добавляем обработчики для решения проблемы с клавиатурой
            const banksListElement = document.getElementById('sbp-banks-list');
            if (banksListElement) {
                let touchStartY = 0;
                let lastScrollTop = 0;

                // Обработка начала касания
                banksListElement.addEventListener('touchstart', function(e) {
                    touchStartY = e.touches[0].clientY;
                    lastScrollTop = this.scrollTop;

                    // Если есть класс keyboard-open, активируем скролл
                    if (document.body.classList.contains('keyboard-open') ||
                        document.body.classList.contains('android-keyboard-visible')) {
                        this.style.overflowY = 'scroll';
                        this.style.webkitOverflowScrolling = 'touch';
                    }
                }, { passive: true });

                // Обработка движения пальца
                banksListElement.addEventListener('touchmove', function(e) {
                    if (document.body.classList.contains('keyboard-open') ||
                        document.body.classList.contains('android-keyboard-visible')) {
                        const touchY = e.touches[0].clientY;
                        const deltaY = touchStartY - touchY;

                        // Программный скролл если автоматический не работает
                        if (Math.abs(deltaY) > 5) {
                            this.scrollTop = lastScrollTop + deltaY;
                            e.preventDefault();
                        }
                    }
                }, { passive: false });
            }

            // Используем MutationObserver для отслеживания появления списка
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                        const dropdownList = document.getElementById('sbp-bank-dropdown-list');
                        const banksList = document.getElementById('sbp-banks-list');

                        if (dropdownList && banksList && dropdownList.style.display !== 'none') {
                            // Список стал видимым - активируем скролл
                            setTimeout(function() {
                                // Принудительный overflow
                                banksList.style.overflowY = 'scroll';
                                banksList.style.webkitOverflowScrolling = 'touch';

                                // Добавляем дополнительный padding если его нет
                                if (!banksList.style.paddingBottom || banksList.style.paddingBottom === '0px') {
                                    banksList.style.paddingBottom = '1px';
                                }

                                // Микро-прокрутка
                                banksList.scrollTop = 1;
                                setTimeout(() => banksList.scrollTop = 0, 10);
                            }, 100);
                        }
                    }
                });
            });

            // Начинаем наблюдение за дропдауном
            const dropdownList = document.getElementById('sbp-bank-dropdown-list');
            if (dropdownList) {
                observer.observe(dropdownList, { attributes: true });
            }
        }
    });
    {/literal}
</script>