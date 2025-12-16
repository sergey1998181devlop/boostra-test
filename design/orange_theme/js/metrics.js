const counter_number = 45594498;

function sendMetric(type, action) {
    if (!is_developer) {
        ym(counter_number, type, action);
    } else {
        console.info('ym ' + type + ' ' + action);
    }

    $.ajax({
        url: 'ajax/save_metrics.php',
        data: {
            type, 
            action
        }
    });
}

// клик по кнопке "Минимальный платеж" в ЛК
$(document).on('click', '.payment_button[data-event="1"]', function (){
    sendMetric('reachGoal', 'minpayment');
});

// клик по кнопке "Минимальный платеж - Принять" в ЛК в модалке
$(document).on('click', '#prolongation_accept', function (){
    sendMetric('reachGoal', 'minpayment-accept');
});

// клик по кнопке "Минимальный платеж - Отказаться" в ЛК в модалке
$(document).on('click', '#prolongation_cancel', function (){
    sendMetric('reachGoal', 'minpayment-refuse');
});

// клик по кнопке "Погасить займ" в ЛК
$(document).on('click', '.payment_button[data-event="2"]', function (){
    sendMetric('reachGoal', 'repay');
});

// клик по кнопке "Погасить займ" вторая кнопка в ЛК
$(document).on('click', '.payment_button[data-event="5"]', function (){
    sendMetric('reachGoal', 'repay-full');
});

// клик по кнопке "Оплатить другую сумму" в ЛК
$(document).on('click', '.payment_button[data-event="6"]', function (){
    sendMetric('reachGoal', 'other-amount');
});

// клик по кнопке "Оплатить другую сумму - Оплатить" в ЛК
$(document).on('click', '.payment_button[data-event="7"]', function (){
    sendMetric('reachGoal', 'other-amount-go');
});

// клик по кнопке оплата по цессии
$(document).on('click', '.button_login_wrapper a[href="user/passport"]', function (){
    sendMetric('reachGoal', 'gocession');
});

// клик по кнопке входа в ЛК по цессии
$(document).on('click', '#send-passport button', function (){
    sendMetric('reachGoal', 'entercession');
});

// клик по кнопке оплатить по цессии в ЛК
$(document).on('click', '[id^="get_payment_link_"]', function (){
    sendMetric('reachGoal', 'paycession');
});

// клик по кнопке Viber
$(document).on('click', '#vb-messenger', function (){
    sendMetric('reachGoal', 'messenger_vb');
});

// клик по кнопке WatsApp
$(document).on('click', '#wa-messenger', function (){
    sendMetric('reachGoal', 'messenger_wa');
});

// клик по кнопке TeleGram
$(document).on('click', '#tg-messenger', function (){
    sendMetric('reachGoal', 'messenger_tg');
});

// клик по кнопке Vkontakte
$(document).on('click', '#vk-messenger', function (){
    sendMetric('reachGoal', 'messenger_vk');
});

// клик по ссылке в баннере Кредитный доктор
$(document).on('click', '.credit-doctor-banner a', function (){
    sendMetric('reachGoal','cd_go_to_url')
});

