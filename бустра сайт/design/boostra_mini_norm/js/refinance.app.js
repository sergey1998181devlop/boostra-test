function RefinanceApp($block)
{
    const app = this;
    app.$block = $block;

    const baseParams = {
        percent: parseFloat(app.$block.find('.refinance_percent_field').val()),
        everypayment: parseFloat(app.$block.find('.refinance_everypayment_field').val()),
        first_pay: parseInt(app.$block.find('.refinance_first_pay_field').val()),
        pay_period: parseInt(app.$block.find('.refinance_pay_period_field').val()),
        pay_day: app.$block.find('#refinance-date').val()
    };

    function debounce(callback, timeout = 500) {
        if (typeof callback !== 'function') {
            throw new Error('callback must be a function');
        }

        var timer;
        return function (...args){
            if (timer) clearTimeout(timer);

            timer = setTimeout(function () {
                callback(...args);
            }, timeout);
        };
    }

    function updateUI(params) {
        app.$block.find('#everypayment').text(params.everypayment);
        app.$block.find('#pay_period').text(params.pay_period);
        app.$block.find('.refinance_percent').text(params.percent);
        app.$block.find('#first_pay').text(params.first_pay);
    }

    let sms_timer;
    const set_timer = function (_seconds) {
        clearInterval(sms_timer);
        sms_timer = setInterval(function () {
            _seconds--;
            if (_seconds > 0) {
                var _str = '<span>Повторно отправить код можно через ' + _seconds + 'сек</span>';
                app.$block.find('.js-repeat-autoconfirm-sms').addClass('inactive').html(_str).show();
            } else {
                app.$block.find('.js-repeat-autoconfirm-sms').removeClass('inactive')
                    .html('<a class="js-send-repeat" href="#">Отправить код еще раз</a>').show();

                clearInterval(sms_timer);
            }
        }, 1000);
    };

    function setDefaultDate() {
        if (app.$block.find('.js-step-3').is(':visible')) {
            return;
        }
        app.$block.find('.js-step-2').hide();
        app.$block.find('.js-step-3').fadeIn();

        // Устанавливаем значение по умолчанию для поля даты
        const $dateInput = app.$block.find('#refinance-date');
        $dateInput.data('default', $dateInput.val());
    }

    var fetchNewSchedule = debounce(function (pay_term, pay_day) {
        $.ajax({
            url: '/user?action=refinance_get_params',
            method: 'POST',
            data: {
                pay_term: pay_term,
                pay_day: pay_day,
                order_id: app.$block.find('.order_id').val()
            },
            success: function (response) {
                response = JSON.parse(response);

                if (response.success) {
                    baseParams.everypayment = response.params.everypayment;
                    baseParams.first_pay = response.params.first_pay;
                    baseParams.pay_period = response.params.pay_period;

                    app.$block.find('.refinance_everypayment_field').val(response.params.everypayment);
                    app.$block.find('.refinance_first_pay_field').val(response.params.first_pay);
                    app.$block.find('.refinance_pay_period_field').val(response.params.pay_period);

                    updateUI(baseParams);
                } else {
                    var $refinanceError = app.$block.find('.refinance-list .refinance-error');
                    $refinanceError.text(response.error || 'Ошибка получения данных');
                    $refinanceError.fadeIn(300).delay(2000).fadeOut(300, function () {
                        $refinanceError.text('');
                    });
                }
            }
        })
    }, 1000);

    function pluralizeMonths(month) {
        month = Math.abs(month) % 100;
        const mod = month % 10;
        if (month > 10 && month < 20) {
            return 'месяцев';
        }
        if (mod > 1 && mod < 5) {
            return 'месяца';
        }
        if (mod === 1) {
            return 'месяц';
        }
        return 'месяцев';
    }

    function sendSms() {
        $.ajax({
            url: 'ajax/sms.php',
            data: {
                action: 'refinance_send',
            },
            success: function(resp){
                resp = typeof resp === 'string' ? JSON.parse(resp) : resp;

                if (!!resp.error)
                {
                    if (resp.error == 'sms_time') {
                        set_timer(resp.time_left);
                        setDefaultDate();
                    }
                }
                else
                {
                    set_timer(resp.time_left);
                    setDefaultDate();
                }
            }
        });
    }

    const _init = function () {

        if (app.$block.find('.js-step-3').is(':visible')) {
            sendSms();
        }

        app.$block.find('.toggle-conditions-accept').on('click', function (e) {
            e.preventDefault();

            $(this).siblings('.conditions').slideToggle('fast');
        });

        app.$block.find('.refinance-button').on('click',function () {
            app.$block.find('.js-step-1').hide();
            app.$block.find('.js-step-2').fadeIn();
            $(this).hide();

            // Находим кнопку по всем классам
            let $button = $('.payment_button.green.button.big.get_prolongation_modal.js-save-click');
            let $minPaymentInfo = app.$block.find('.min_payment_info');

            if ($button.length) {
                $button.each(function () {
                    var $button = $(this);

                    if ($button.hasClass('js-next-step-3')) return;

                    $button.removeClass('green');
                    $button.removeClass('big');
                    $button.addClass('button-inverse');
                });
            }

            if ($minPaymentInfo.length) {
                $minPaymentInfo.css({
                    'border': '2px solid rgb(44, 43, 57)',
                    'color': 'rgb(44, 43, 57)',
                });
            }

        });

        app.$block.find('#refinanceSlider').on('input', function () {
            let months = parseInt($(this).val());
            let pay_term = months * 30;

            let monthsString = pluralizeMonths(months);

            app.$block.find('#monthsValue').text(months + ' ' + monthsString);

            fetchNewSchedule(pay_term, app.$block.find('#refinance-date').val());
        });

        app.$block.find('.js-next-step-3').click(function () {
            var $self = $(this);
            $self.prop('disabled', true);

            if (app.$block.find('.refinance-container .payment-card-list input[type="radio"]:checked').length === 0) {
                var $errorInfo = $self.siblings('.error-info');
                $errorInfo.text('Выберите способ оплаты');
                $errorInfo.fadeIn(300).delay(3000).fadeOut(300, function () {
                    $errorInfo.text('');
                });

                $self.prop('disabled', false);
                return;
            }

            sendSms();
        });

        const $dateInput = app.$block.find('#refinance-date');

        // Обработчик при изменении значения (когда поле теряет фокус)
        $dateInput.on('change', function () {
            const min = parseInt($(this).attr('min')) || 1;
            const max = parseInt($(this).attr('max')) || 31;
            let value = parseInt($(this).val());

            // Если ввод не число или вне диапазона — корректируем
            if (isNaN(value)) {
                $(this).val(min);
            } else {
                $(this).val(Math.max(min, Math.min(max, value)));
            }

            let months = parseInt(app.$block.find('#refinanceSlider').val());
            let pay_term = months * 30;

            let monthsString = pluralizeMonths(months);

            app.$block.find('#monthsValue').text(months + ' ' + monthsString);

            fetchNewSchedule(pay_term, $(this).val());
        });
        // Обработчик при вводе (в реальном времени)
        $dateInput.on('input', function () {
            const min = parseInt($(this).attr('min')) || 1;
            const max = parseInt($(this).attr('max')) || 31;
            let value = parseInt($(this).val());

            if (!isNaN(value)) {
                $(this).val(Math.max(min, Math.min(max, value)));

                let months = parseInt(app.$block.find('#refinanceSlider').val());
                let pay_term = months * 30;

                let monthsString = pluralizeMonths(months);

                app.$block.find('#monthsValue').text(months + ' ' + monthsString);

                fetchNewSchedule(pay_term, $(this).val());
            }
        });

        $dateInput.datepicker({
            onSelect: function () {
                var date = $(this).datepicker('getDate');
                $(this).val(date.getDate());

                let months = parseInt(app.$block.find('#refinanceSlider').val());
                let pay_term = months * 30;

                let monthsString = pluralizeMonths(months);

                app.$block.find('#monthsValue').text(months + ' ' + monthsString);

                fetchNewSchedule(pay_term, $(this).val());
            }
        })

        app.$block.find('.js-apply-refinance').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            let $errorInfo = app.$block.find('#refinance-confirm-form .error-info');

            if (app.$block.find('.js-need-verify:checked').length != app.$block.find('.js-need-verify').length) {
                $errorInfo.text('Нужно согласиться с условиями');
                $errorInfo.fadeIn(300).delay(3000).fadeOut(300, function () {
                    $errorInfo.text('');
                });
                return;
            }

            let $self = $(this);
            let months = parseInt(app.$block.find('#refinanceSlider').val());
            let pay_term = months * 30;
            let cardId = app.$block.find('.refinance-container .payment-card-list input[type="radio"]:checked').val();
            let code = app.$block.find('#refinance_sms_code').val();
            let local_time = Math.floor((new Date()).getTime() / 1000);
            $self.prop('disabled', true);

            $.post('/user?action=refinance_scoring_aksi', {
                order_id: app.$block.find('.order_id').val(),
                pay_term: pay_term,
                pay_day: app.$block.find('#refinance-date').val(),
                card_id: cardId,
                code: code,
                local_time: local_time
            }, function (response) {
                response = typeof response === 'string' ? JSON.parse(response) : response;
                $self.prop('disabled', false);

                if (!response.success) {
                    if (response.error || response.result.error) {
                        $errorInfo.text(response.error || response.result.error);
                    } else {
                        $errorInfo.text('Ошибка скоринга');
                    }

                    $errorInfo.fadeIn(300).delay(3000).fadeOut(300, function () {
                        $errorInfo.text('');
                    });
                    return;
                }

                var $paymentForm = app.$block.find('.form-payment');
                var cardId = app.$block.find('.refinance_card_id').length
                    ? app.$block.find('.refinance_card_id').val()
                    : app.$block.find('.refinance-container .payment-card-list input[type="radio"]:checked').val();

                $paymentForm.find('.number').val(response.contract.number);
                $paymentForm.find('.order_id').val(response.order.id);
                $paymentForm.find('.amount').val(response.first_pay);
                $paymentForm.find('.sms_code').val(app.$block.find('#refinance_sms_code').val());
                $paymentForm.find('.card_id').val(cardId);
                $paymentForm.find('.refinance').val(response.refinance_amount);

                $paymentForm.submit();
            });
        });
    };

    ;(function () {
        _init();
    })();
}

$(function () {
    $('.js-refinance-block').each(function () {
        new RefinanceApp($(this));
    });
});