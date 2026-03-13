$('#credit_rating_new_user [data-href]').on('click', function (e) {
    e.preventDefault();
    $('.credit-rating-notice').show();

    let id = $(this).data('href');

    if ($(id).is(':hidden')) {
        $(id).slideToggle();
    }

    let destination = $(id).offset().top;

    $('html, body').animate({scrollTop: destination}, 600);
    sendMetric('reachGoal', 'new_credit_rating_get_rating');
});

$('#credit_rating_new_user *').on('click', function () {
    user_cr_app.skip_button_second = 5;
});

//Инициализация объекта, чтобы не было конфликтов с др. скриптами всё пишем в свойства
let user_cr_app = Object();

user_cr_app.DEFAULT_SMS_DELAY_SECONDS = 30;
user_cr_app.SUCCESS_STATUSES = ['CONFIRMED', 'AUTHORIZED'];
user_cr_app.REJECTED_STATUS = 'REJECTED';
user_cr_app.SMS_ERROR = 'Не верный код';
user_cr_app.USER_LK_URL = '/user/login';
user_cr_app.payment_id = null;
user_cr_app.error_field = $('#credit_rating_new_user .text-red');
user_cr_app.code_field = $('#credit_rating_new_user [name="sms"]');
user_cr_app.user_phone = $('#credit_rating_new_user [name="user_phone"]').val();
user_cr_app.sms_repeat_button = $('#user-cr-sms-send a');
user_cr_app.sms_timer_field = $('#user-cr-sms-send .timer-text');
user_cr_app.skip_button_elements = $('.skip-button-rating');
user_cr_app.sms_timer_second = 0;
user_cr_app.sms_timer = null;
user_cr_app.skip_button_second = 10;
user_cr_app.skip_button_timer = null;
user_cr_app.send_pay_button = $("#send-pay-cr");
user_cr_app.price = 399;

//таймер появления кнопки пропустить покупку КР
user_cr_app.init_skip_button_timer = function () {
    user_cr_app.skip_button_timer = setInterval(function () {
        if (user_cr_app.skip_button_second === 0) {
            user_cr_app.skip_button_elements.show();
            clearInterval(user_cr_app.skip_button_timer);
        }
        user_cr_app.skip_button_second--;
    }, 1000);
};

//показывает кнопку и форму отправки СМС кода
user_cr_app.showSmsBlock = function (btn) {
    $(btn).hide();
    $('#user-cr-sms-send').show();
    user_cr_app.send_sms();
};

//инициализирует шкалу
user_cr_app.setRatingBall = function (score) {
    $('#rating_arrow').css('transition-duration', '5s');

    let speed = parseInt(score),
        center = 375,
        resultDeg = 0;

    let oneDeg = 90 / center; // находим сколько 1 градус от значения 375
    let activeDeg = speed - center; // найдем сколько значение от введенного
    resultDeg = Math.ceil(oneDeg * activeDeg);

    $('#rating_arrow').css('transform', 'rotate(' + resultDeg + 'deg)');
};

// функция таймера отправки смс
user_cr_app.init_sms_timer = function (seconds) {
    user_cr_app.sms_timer_second = seconds;
    user_cr_app.sms_repeat_button.hide();
    user_cr_app.sms_timer_field.show();

    user_cr_app.sms_timer = setInterval(function () {
        if (user_cr_app.sms_timer_second === 0) {
            user_cr_app.delete_sms_timer();
        } else {
            user_cr_app.sms_timer_field.text(user_cr_app.sms_timer_second);
        }
        user_cr_app.sms_timer_second--;
    }, 1000);
};

// выключение таймера и снятие блокировок
user_cr_app.delete_sms_timer = function () {
    clearInterval(user_cr_app.sms_timer);
    user_cr_app.sms_repeat_button.show();
    user_cr_app.sms_timer_field.hide();
};

// отправка СМС
user_cr_app.send_sms = function () {
    user_cr_app.init_sms_timer(user_cr_app.DEFAULT_SMS_DELAY_SECONDS);
    sendMetric('reachGoal', 'credit_rating_get_sms_code');
    $.ajax({
        url: 'ajax/sms.php',
        data: {
            action: 'credit_rating_send',
        },
        dataType: 'json',
        success: function (resp) {
            if (resp.error) {
                user_cr_app.delete_sms_timer();
                if (resp.error === 'sms_time')
                    user_cr_app.init_sms_timer(resp.time_left);
                else
                    console.log(resp);
            } else {
                if (resp.mode === 'developer') {
                    user_cr_app.code_field.val(resp.developer_code);
                    user_cr_app.validate_sms_code();
                    user_cr_app.delete_sms_timer();
                } else {
                    console.log('response: ', resp);
                }
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        },
    });
};

// маска ввода для СМС
user_cr_app.init_mask = function () {
    user_cr_app.code_field.inputmask({
        mask: "9999",
        oncomplete: function () {
            user_cr_app.validate_sms_code();
        }
    });
};

// проверка СМС
user_cr_app.validate_sms_code = function () {
    user_cr_app.init_spinner('Происходит проверка СМС подождите...');
    let sms_code = user_cr_app.code_field.val();
    $.ajax({
        url: 'ajax/sms.php?action=check_credit_rating_sms',
        data: {
            code: sms_code,
        },
        type: 'POST',
        success: function (resp) {
            if (resp.success) {
                user_cr_app.send_pay_button.show();
                sendMetric('reachGoal', 'new_credit_rating_access_sms');
            } else {
                if (user_cr_app.error_field.is(':hidden')) {
                    user_cr_app.error_field.show();
                }
                user_cr_app.code_field.addClass('error');
                user_cr_app.error_field.text(resp.soap_fault ? resp.error : user_cr_app.SMS_ERROR);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        },
    }).done(function () {
        user_cr_app.delete_spinner();
    });
};

//инициализация спиннера загрузки
user_cr_app.init_spinner = function (text = '') {
    $('body').append('<div class="spinner_wrapper"><div class="fancybox-loading"><div></div></div><div class="spinner_text">'+ text +'</div></div>');
};

//удаление спинера загрузки
user_cr_app.delete_spinner = function () {
    $('.spinner_wrapper').remove();
};

//инициализация оплаты
user_cr_app.init_pay = function () {
    sendMetric('reachGoal', 'new_credit_rating_click_pay');
    user_cr_app.delete_spinner();
    user_cr_app.init_spinner('Происходит попытка оплаты, подождите...');
    localStorage.setItem('new_user_pay_credit_rating', '1');

    let card_id = $('[name=card_pay_id]:checked').val();
    if (card_id === 'other' || !card_id) {
        user_cr_app.other_payment(user_cr_app.price, user_cr_app.code_field.val());
    } else {
        user_cr_app.attach_card_payment(card_id, user_cr_app.price, user_cr_app.code_field.val());
    }
};

//оплата новой картой
user_cr_app.other_payment = function(amount, sms_code){
    user_cr_app.init_spinner();

    $.ajax({
        url: 'ajax/payment.php',
        async: false,
        data: {
            action: 'create_transaction',
            amount: amount,
            code_sms: sms_code,
            payment_type: 'credit_rating',
        },
        success: function(resp){
            if (!!resp.error)
            {
                user_cr_app.error_field.html(
                    'Ошибка платежа: ' + resp.error + '<br />Попробуйте выполнить платеж с другой карты'
                ).show();
                return false;
            }
            else
            {
                window.location.href = resp.PaymentURL;
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        },
    }).done(function (){
        user_cr_app.delete_spinner();
    });
};

//покупка с привязанной карты
user_cr_app.attach_card_payment = function (card_id, amount, code_sms) {
    user_cr_app.init_spinner('Происходит попытка оплаты, подождите...');
    let insurer = $("[name='insurer']").val();

    $.ajax({
        url: 'ajax/payment.php',
        data: {
            action: 'send_payment_attach',
            card_id: card_id,
            amount: amount,
            code_sms: code_sms,
            insurer: insurer,
            payment_type: 'credit_rating',
        },
        success: function (resp) {
            if (!!resp.error) {
                if(resp.soap_fault) {
                    user_cr_app.error_field.text(resp.error).show();
                } else {
                    user_cr_app.error_field.text('Возникла проблема при оплате. Пожалуйста, выберите другую карту').show();
                }
                user_cr_app.delete_spinner();
                return false;
            } else {
                user_cr_app.payment_id = resp.PaymentId;
                user_cr_app.check_state(user_cr_app.payment_id);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        },
    });
};

//скипаем покупку кредитного рейтинга
user_cr_app.skip_rating_pay = function () {
    $.ajax({
        url: 'ajax/user.php?action=skip_credit_rating',
        data: {},
        success: function (resp) {
            if (resp.success) {
                window.location.href = user_cr_app.USER_LK_URL;
            } else {
                console.log(resp);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        },
    });
};

//проверка платежа в фоновом режиме
user_cr_app.check_state = function (payment_id) {
    user_cr_app.check_timeout = setTimeout(function () {
        $.ajax({
            url: 'ajax/payment.php',
            data: {
                action: 'get_state',
                payment_id: payment_id,
            },
            success: function (resp) {
                let status = resp.Status;
                if (!!resp.error) {
                    user_cr_app.error_field.html(
                        'Ошибка проверки платежа: ' + resp.error + '<br />Попробуйте выполнить платеж с другой карты'
                    ).show();
                    user_cr_app.delete_spinner();
                } else {
                    if (user_cr_app.SUCCESS_STATUSES.includes(status)) {
                        user_cr_app.delete_spinner();
                        window.location.href = user_cr_app.USER_LK_URL;
                    } else if (status === user_cr_app.REJECTED_STATUS) {
                        user_cr_app.delete_spinner();
                        let message = resp.Message;
                        if (resp.Message == 'Превышено допустимое количество запросов авторизации операции') {
                            message = 'Попробуйте выполнить платеж с другой карты';
                        }
                        user_cr_app.error_field.html('К сожалению во время оплаты произошла ошибка. <br />' + message);
                    } else {
                        user_cr_app.check_state(payment_id);
                    }
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                alert(error);
                console.log(error);
            },
        });
    }, 5000);
};

$(document).ready(function () {
    user_cr_app.init_mask();
    user_cr_app.init_skip_button_timer();

    user_cr_app.code_field.on('blur', function () {
        user_cr_app.error_field.hide();
        $(this).removeClass('error');
    });
});
