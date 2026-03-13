/* Новогодняя акция - баннер */
$(function() {
    const $allBanners = $('.newyear-promo-banner');
    if (!$allBanners.length) return;

    // Помечаем все баннеры как скрытые изначально
    $allBanners.hide();

    // При загрузке страницы проверяем состояние активации для каждого баннера
    let firstActiveBannerIndex = -1;
    
    $allBanners.each(function(index) {
        const $banner = $(this);
        const $getDiscountBtn = $banner.find('[data-action="get_discount"]');
        
        // Если кнопка "Получить скидку" не видна (т.е. акция уже активирована)
        const isActivated = $getDiscountBtn.length === 0 || !$getDiscountBtn.is(':visible');
        
        if (!isActivated) {
            // Если это первый неактивированный баннер - показываем его
            if (firstActiveBannerIndex === -1) {
                firstActiveBannerIndex = index;
                $banner.show();
            }
        }
    });
    
    // Если все баннеры активированы, показываем последний
    if (firstActiveBannerIndex === -1 && $allBanners.length > 0) {
        $allBanners.last().show();
    }

    // Инициализируем обработчики для каждого баннера
    $allBanners.each(function() {
        initBannerHandlers($(this));
    });
});

// Функция инициализации обработчиков для баннера
function initBannerHandlers($banner) {
    const orderId = $banner.data('order-id');
    const userId = $banner.data('user-id');
    const $countdownEl = $banner.find('.newyear-promo-banner__countdown');
    
    // Удаляем старые обработчики, чтобы избежать дублирования
    $banner.off('click', '[data-action="get_discount"]');
    $banner.off('click', '[data-action="pay"]');
    
    // Обработчик кнопки получения скидки
    $banner.on('click', '[data-action="get_discount"]', function() {
        const $btn = $(this);
        // Делаем все кнопки в баннере disabled
        $btn.prop('disabled', true);

        // Получаем актуальные значения из data-атрибутов баннера (на случай, если они изменились после обновления)
        const currentOrderId = $banner.data('order-id');
        const currentUserId = $banner.data('user-id');
        
        // Используем актуальные значения, если они есть, иначе используем сохраненные
        const finalOrderId = currentOrderId || orderId;
        const finalUserId = currentUserId || userId;

        let remaining = null;

        if ($countdownEl.length) {
            // Получаем оставшееся время из таймера
            remaining = parseInt($countdownEl.data('remaining') || 0);

            // Проверяем, не истекло ли время акции
            if (remaining <= 0) {
                alert('Время действия акции истекло');
                $banner.find('button').prop('disabled', false);
                $banner.hide();
                return;
            }
        }
        
        $.ajax({
            url: '/newyear_promo',
            method: 'POST',
            data: {
                action: 'get_discount',
                order_id: finalOrderId,
                user_id: finalUserId,
                remaining_time: remaining
            },
                dataType: 'json',
            success: function(data) {
                if (data.success) {
                    // Запрашиваем обновленный HTML баннера
                    $.ajax({
                        url: '/newyear_promo',
                        method: 'POST',
                        data: {
                            action: 'get_banner_html',
                            order_id: finalOrderId,
                            user_id: finalUserId
                        },
                        dataType: 'json',
                        success: function(bannerData) {
                            let $updatedBanner = $banner;
                            
                            if (bannerData.success && bannerData.html) {
                                // Заменяем содержимое текущего баннера на обновленный HTML
                                const $newBanner = $(bannerData.html);
                                $banner.replaceWith($newBanner);
                                
                                // Инициализируем таймер для обновленного баннера, если он есть
                                $updatedBanner = $newBanner;
                                const $countdownEl = $updatedBanner.find('.newyear-promo-banner__countdown');
                                if ($countdownEl.length) {
                                    initCountdown($updatedBanner, $countdownEl);
                                }
                                
                                // Инициализируем обработчики для обновленного баннера
                                initBannerHandlers($updatedBanner);

                                // Небольшая задержка, чтобы DOM обновился после replaceWith
                                setTimeout(function() {
                                    $('.newyear-promo-banner').show();
                                }, 1000);
                            }
                        },
                        error: function() {
                            console.error('Error getting banner HTML');
                            // В случае ошибки просто скрываем текущий баннер и показываем следующий
                            $banner.hide();
                            const $allBanners = $('.newyear-promo-banner');
                            $allBanners.each(function() {
                                const $nextBanner = $(this);
                                if ($nextBanner.is($banner)) return;
                                if ($nextBanner.is(':visible')) return;
                                
                                const $nextGetDiscountBtn = $nextBanner.find('[data-action="get_discount"]');
                                const isNextActivated = $nextGetDiscountBtn.length === 0 || !$nextGetDiscountBtn.is(':visible');
                                
                                if (!isNextActivated) {
                                    $nextBanner.show();
                                    return false;
                                }
                            });
                        }
                    });
                } else {
                        let errorMessage = 'Произошла ошибка';
                        if (data.error === 'promo_expired') {
                            errorMessage = 'Время действия акции истекло';
                            $banner.hide();
                        } else if (data.error === 'promo_not_found') {
                            errorMessage = 'Акция не найдена';
                        }
                        alert(errorMessage);
                        $banner.find('button').prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Произошла ошибка при получении скидки');
                    $banner.find('button').prop('disabled', false);
                }
            });
        });

        // Обработчик кнопки оплаты
        $banner.on('click', '[data-action="pay"]', function() {
            const $btn = $(this);
            // Делаем все кнопки в баннере disabled
            $btn.prop('disabled', true);
            
            // Получаем orderId и userId из data-атрибутов баннера (на случай, если они изменились после обновления)
            const currentOrderId = $('.newyear-promo-banner').data('order-id');
            const currentUserId = $('.newyear-promo-banner').data('user-id');
            
            // Используем актуальные значения, если они есть, иначе используем сохраненные
            const finalOrderId = currentOrderId || orderId;
            const finalUserId = currentUserId || userId;
            
            // Получаем оставшееся время из таймера
            const remaining = parseInt($countdownEl.data('remaining') || 0);
            
            // Проверяем, не истекло ли время акции
            if (remaining <= 0) {
                alert('Время действия акции истекло');
                $banner.find('button').prop('disabled', false);
                $banner.hide();
                return;
            }
            
            $.ajax({
                url: '/newyear_promo',
                method: 'POST',
                data: {
                    action: 'pay_click',
                    order_id: finalOrderId,
                    user_id: finalUserId,
                    remaining_time: remaining
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        // Отправляем форму из баннера
                        const $form = $btn.closest('form');
                        if ($form.length) {
                            $form.submit();
                        } else {
                            // Если формы нет, ищем форму по ID
                            const $formById = $('#user_pay_form_1_' + finalOrderId);
                            if ($formById.length) {
                                $formById.submit();
                            } else {
                                // Fallback: перезагружаем страницу
                                location.reload();
                            }
                        }
                    } else {
                        let errorMessage = 'Произошла ошибка';
                        if (data.error === 'promo_expired') {
                            errorMessage = 'Время действия акции истекло';
                            $banner.hide();
                        } else if (data.error === 'promo_not_found') {
                            errorMessage = 'Акция не найдена';
                        }
                        alert(errorMessage);
                        $banner.find('button').prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Произошла ошибка');
                    $banner.find('button').prop('disabled', false);
                }
            });
        });

    // Инициализируем таймер для баннера
    if ($countdownEl.length) {
        initCountdown($banner, $countdownEl);
    }
}

// Функция инициализации таймера обратного отсчета
function initCountdown($banner, $countdownEl) {
    const remaining = parseInt($countdownEl.data('remaining') || 0);
    if (remaining > 0) {
        let timeLeft = remaining;
        // Теперь у нас 6 отдельных input'ов: часы (2), минуты (2), секунды (2)
        const $digits = $countdownEl.find('.newyear-promo-banner__countdown-digit');
        const $hours1 = $digits.eq(0);  // Первая цифра часов
        const $hours2 = $digits.eq(1);  // Вторая цифра часов
        const $minutes1 = $digits.eq(2); // Первая цифра минут
        const $minutes2 = $digits.eq(3); // Вторая цифра минут
        const $seconds1 = $digits.eq(4); // Первая цифра секунд
        const $seconds2 = $digits.eq(5); // Вторая цифра секунд

        function updateCountdown() {
            // Сначала показываем текущее значение времени
            const hours = Math.floor(timeLeft / 3600);
            const minutes = Math.floor((timeLeft % 3600) / 60);
            const seconds = timeLeft % 60;

            const hoursStr = String(hours).padStart(2, '0');
            const minutesStr = String(minutes).padStart(2, '0');
            const secondsStr = String(seconds).padStart(2, '0');

            $hours1.val(hoursStr[0]);
            $hours2.val(hoursStr[1]);
            $minutes1.val(minutesStr[0]);
            $minutes2.val(minutesStr[1]);
            $seconds1.val(secondsStr[0]);
            $seconds2.val(secondsStr[1]);

            // Обновляем data-remaining для передачи с фронта при кликах на кнопки
            $countdownEl.data('remaining', timeLeft);

            // Если время истекло (timeLeft === 0), показываем "00:00:00" и скрываем баннер
            if (timeLeft <= 0) {
                $banner.hide();
                return;
            }

            // Уменьшаем время для следующей итерации
            timeLeft--;
            
            // Планируем следующее обновление
            setTimeout(updateCountdown, 1000);
        }

        updateCountdown();
    }
}

// Обработчик активации акции при нажатии на "Погасить заём полностью"
$(function() {
    $(document).on('click', '.newyear-activate-on-click', function(e) {
        const $btn = $(this);
        const orderId = $('.newyear-promo-banner').data('order-id');
        const userId = $('.newyear-promo-banner').data('user-id');
        const formId = $btn.data('form-id');
        
        if (!orderId || !userId) return;
        
        // Проверяем, есть ли активная акция, но не активированная
        const $banner = $('.newyear-promo-banner');
        if ($banner.length) {
            const $getDiscountBtn = $banner.find('[data-action="get_discount"]');
            if ($getDiscountBtn.length && $getDiscountBtn.is(':visible')) {
                // Если акция не активирована, активируем её перед переходом
                e.preventDefault();
                e.stopPropagation();
                
                $btn.prop('disabled', true);
                
                $.ajax({
                    url: '/newyear_promo',
                    method: 'POST',
                    data: {
                        action: 'get_discount',
                        order_id: orderId,
                        user_id: userId
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            // Перезагружаем страницу, чтобы обновить данные
                            if (formId) {
                                // Если есть formId, перезагружаем страницу, чтобы форма обновилась с учетом скидки
                                location.reload();
                            } else {
                                // Ищем форму, к которой относится кнопка, и отправляем её
                                const $form = $btn.closest('form');
                                if ($form.length) {
                                    $form.submit();
                                } else {
                                    // Если формы нет, просто перезагружаем страницу
                                    location.reload();
                                }
                            }
                        } else {
                            alert(data.error || 'Произошла ошибка');
                            $btn.prop('disabled', false);
                        }
                    },
                    error: function() {
                        alert('Произошла ошибка при активации скидки');
                        $btn.prop('disabled', false);
                    }
                });
            }
        }
    });
});
