function CreditRatingApp() {
    var app = this;

    const SUCCESS_STATUSES = ['CONFIRMED', 'AUTHORIZED'];
    const REJECTED_STATUS = 'REJECTED';

    app.button = $('.get-credit-rating-button');
    app.button_small = $('.get-credit-rating-small-button');
    app.result_wrapper = $('.credit-rating-result');
    app.cards_wrapper = $('.credit-rating-cards');
    app.error_field = $('.credit-rating-error');

    app.price = 399;
    app.sms_cooldown = 30;

    app.agreed_all = false;

    app.error_timer_second = 0;
    app.init_error_timer = function () {
        app.error_timer = setInterval(function (){
            if (app.error_timer_second === 0) {
                clearInterval(app.error_timer);
                $('.credit-rating-check-sms').removeClass('disabled');
                $('.sms-code-error').hide();
            } else {
                $('.sms-code-error span').text(app.error_timer_second);
            }
            app.error_timer_second--;
        }, 1000);
    };

    app.init = function () {
        app.init_get_rating_button_on_click();
    };

    app.init_get_rating_button_on_click = function () {
        var click_handler = function() {
            window.credit_rating_button_pressed = true;

            app.result_wrapper.html('<div class="fancybox-loading"><div></div></div>');

            $.post('/user?action=credit_rating_form_submitted', {}, function (data) {
                app.result_wrapper.html(data);
                setTimeout(app.init_mask, 200);
                setTimeout(app.init_send_sms_button, 200);
                setTimeout(app.init_check_sms_button, 200);
            });
        };

        app.button.click(click_handler);
        app.button_small.click(click_handler);
    }

    app.init_mask = function () {
        const $code_field = $('[name="credit_rating_sms"]');
        $code_field.inputmask({
            mask: "9999",
            oncomplete: function() {
                $('.credit-rating-check-sms').removeClass('disabled');
            }
        });
    };

    app.init_check_sms_button = function() {
        $('.credit-rating-check-sms').click(function () {
            const $code_field = $('[name="credit_rating_sms"]');
            if (!$code_field.inputmask('isComplete') || $(this).hasClass('disabled')) {
                console.log('return');
                return;
            }

            if (is_developer) {
                console.info('ym reachGoal credit_doctor_button3');
            } else {
                ym(45594498, 'reachGoal', 'credit_doctor_button3');
            }

            app.init_spinner('Происходит проверка СМС подождите...');

            setTimeout(function (){
                $.post('ajax/sms.php?action=check_credit_rating_sms', {
                    'code': $code_field.val()
                }, function (answer) {
                    localStorage.setItem('send_credit_rating_sms', (new Date()).getTime().toString())
                    app.delete_spinner();

                    if (answer.success === 0) {
                        app.set_time_click_sms();
                        return;
                    }

                    $('.credit-rating-sms-block').hide();

                    //app.cards_wrapper = $('.credit-rating-cards');
                    //app.cards_wrapper.html('<div class="credit-rating-pay button medium">Оплатить</div>');
                    setTimeout(app.send_pay_rating, 200);
                });
            }, 1500);
        });
    }

    app.init_send_sms_button = function () {
        let $sms_button = $('.credit-rating-send-sms');

        $sms_button.click(function () {
            if ($(this).hasClass('disabled')) {
                $('.button-errors').css('visibility', 'visible');
                return;
            }

            $(this).hide();

            if (is_developer) {
                console.info('ym reachGoal credit_doctor_button2');
            } else {
                ym(45594498, 'reachGoal', 'credit_doctor_button2');
            }

            let $sms_form = $('.credit-rating-sms-block form');
            if ($sms_form.css('display') === 'none') {
                $sms_form.show();
            }

            app.set_time_click_sms();

            $.post('/user?action=credit_rating_send_sms');
        });
    };

    app.set_time_click_sms = function () {
        const last_sent_sms_time = localStorage.getItem('send_credit_rating_sms');
        const cooldown_ms = app.sms_cooldown * 1000;

        let current_time = (new Date());
        if (last_sent_sms_time != null && parseInt(last_sent_sms_time) + cooldown_ms > current_time.getTime()) {
            let seconds_left = cooldown_ms - (current_time.getTime() - parseInt(last_sent_sms_time));
            seconds_left = (seconds_left - (seconds_left % 1000)) / 1000;
            $('.sms-code-error').html('Вы ввели неверный код <div><small>Отправить код повторно можно через <b><span>' + seconds_left + '</span></b> секунд</small></div>').show();
            $('.credit-rating-check-sms').addClass('disabled').text('Получить код ещё раз');
            app.error_timer_second = seconds_left;
            app.init_error_timer();
            return;
        }
    };

    app.send_pay_rating = function () {
        const $code_field = $('[name="credit_rating_sms"]');
        let card_id = $('[name=card_pay_id]:checked').val();
        let use_b2p = parseInt($("[name='use_b2p']").val());
        if (card_id == 'other' || !card_id) {
            app.other_payment(app.price, $code_field.val(), use_b2p);
        } else {
            app.attach_card_payment(card_id, app.price, $code_field.val(), use_b2p);
        }

        if (is_developer) {
            console.info('ym reachGoal credit_doctor_button4');
        } else {
            ym(45594498, 'reachGoal', 'credit_doctor_button4');
        }
    };

    app.init_payment_button = function() {
        const pay_button = $('.credit-rating-pay');
        pay_button.click(function () {
            app.send_pay_rating();
        });
    };

    app.check_state = function(payment_id){
        app.check_timeout = setTimeout(function(){
            $.ajax({
                url: 'ajax/payment.php',
                data: {
                    action: 'get_state',
                    payment_id: payment_id,
                },
                success: function(resp){
                    let status = resp.Status;
                    app.error_field = $('.credit-rating-error');
                    console.log(resp)
                    if (!!resp.error)
                    {
                        app.error_field.html(
                            'Ошибка проверки платежа: ' + resp.error + '<br />Попробуйте выполнить платеж с другой карты'
                        ).show();
                    }
                    else
                    {
                        if (SUCCESS_STATUSES.includes(status))
                        {
                            if (is_developer) {
                                console.info('ym reachGoal credit_doctor_button5');
                            } else {
                                ym(45594498, 'reachGoal', 'credit_doctor_button5');
                            }

                            $('.credit_rating_wrapper').closest('.panel').prepend('<div id="success_message_pay">' + resp.Message + '</div>');

                            let destination = $('#success_message_pay').offset().top;
                            $('html, body').animate({ scrollTop: destination }, 600);
                            $('#user_get_zaim_form').fadeIn();
                            $('.warning-credit-text').remove();
                            //location.href = '/user/docs?action=credit_rating_paid&payment_id=' + payment_id;
                        } else if (status === REJECTED_STATUS) {
                            var message = resp.Message;
                            if (resp.Message == 'Превышено допустимое количество запросов авторизации операции') {
                                message = 'Попробуйте выполнить платеж с другой карты';
                            }

                            app.error_field.html('К сожалению во время оплаты произошла ошибка. <br />' + message);
                        } else {
                            app.check_state(payment_id);
                        }
                    }
                    app.delete_spinner();
                }
            })
        }, 5000);
    };

    app.attach_card_payment = function(card_id, amount, code_sms, use_b2p){
        app.init_spinner('Происходит попытка оплаты, подождите...');
        let insurer = $("[name='insurer']").val();

        $.ajax({
            url: !!use_b2p ? 'ajax/b2p_payment.php' : 'ajax/payment.php',
            data: {
                action: !!use_b2p ? 'get_payment_link' : 'send_payment_attach',
                card_id: card_id,
                amount: amount,
                code_sms: code_sms,
                insurer: insurer,
                credit_rating_type: 2,
            },
            success: function(resp){
                if (!!resp.error)
                {
                    $('.payment-block').removeClass('loading').addClass('error');
                    if(resp.soap_fault) {
                        $('.payment-block-error p').html(resp.error);
                    } else {
                        $('.payment-block-error p').html('Возникла проблема при оплате. Пожалуйста, выберите другую карту');
                    }
                    app.delete_spinner();
                    return false;
                } else if(!!use_b2p && resp.payment_link) {
                    app.delete_spinner();
                    window.location.href = resp.payment_link;
                } else {
                    app.payment_id = resp.PaymentId;
                    app.check_state(app.payment_id);
                }
            }
        });
    };

    app.init_spinner = function (text = '') {
        $('.credit-rating-content').append('<div class="spinner_wrapper"><div class="fancybox-loading"><div></div></div><div class="spinner_text">'+ text +'</div></div>');
    };

    app.delete_spinner = function () {
        $('.spinner_wrapper').remove();
    };

    app.other_payment = function(amount, sms_code, use_b2p){
        app.init_spinner();

        $.ajax({
            url: !!use_b2p ? 'ajax/b2p_payment.php' : 'ajax/payment.php',
            data: {
                action: !!use_b2p ? 'attach_card' : 'create_transaction',
                amount: amount,
                code_sms: sms_code,
                payment_type: 'credit_rating_after_rejection',
            },
            success: function(resp){
                if (!!resp.error)
                {
                    app.error_field.html(
                        'Ошибка платежа: ' + resp.error + '<br />Попробуйте выполнить платеж с другой карты'
                    ).show();
                    app.delete_spinner();

                    return false;
                }
                else
                {
                    document.cookie = "go_credit_rating_paid="+app.payment_id+"; path=/;";
                    window.location.href = use_b2p ? resp.link : resp.PaymentURL;
                }
            }
        })
    };

    (function () {
        app.init();
    })();
}

$(function () {
    new CreditRatingApp();
});