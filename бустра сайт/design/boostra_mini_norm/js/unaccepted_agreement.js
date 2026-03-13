const unacceptedAgreement = new Object({});
unacceptedAgreement.data = new Object({});
unacceptedAgreement.step = 0;
unacceptedAgreement.DEFAULT_SMS_DELAY_SECONDS = 30;
unacceptedAgreement.sms_timer_second = 0;

unacceptedAgreement.removePreloader = function () {
    $('body').removeClass('loading');
}

unacceptedAgreement.setPreloader = function () {
    $('body').addClass('loading');
}

// функция таймера отправки смс
unacceptedAgreement.init_sms_timer = function (seconds) {
    unacceptedAgreement.sms_timer_second = seconds;
    unacceptedAgreement.sms_repeat_button.hide();
    unacceptedAgreement.SMSTimerField.show();

    unacceptedAgreement.sms_timer = setInterval(function () {
        if (unacceptedAgreement.sms_timer_second === 0) {
            unacceptedAgreement.delete_sms_timer();
            unacceptedAgreement.sms_repeat_button.show();
        } else {
            unacceptedAgreement.SMSTimerField.text(unacceptedAgreement.sms_timer_second);
        }
        unacceptedAgreement.sms_timer_second--;
    }, 1000);
};

// выключение таймера и снятие блокировок
unacceptedAgreement.delete_sms_timer = function () {
    clearInterval(unacceptedAgreement.sms_timer);
    unacceptedAgreement.sms_repeat_button.show();
    unacceptedAgreement.SMSTimerField.hide();
};

unacceptedAgreement.sendSms = function () {
    $.ajax({
        url: 'ajax/sms.php',
        data: {
            action: 'send',
            phone: $('input[name="agreement_sms_phone"]').val(),
            flag: 'АСП'
        },
        dataType: 'json',
        beforeSend: function () {
            $(unacceptedAgreement.sms_code_field).closest('.sms_code_wrapper').removeClass('has-error has-success');
            unacceptedAgreement.init_sms_timer(unacceptedAgreement.DEFAULT_SMS_DELAY_SECONDS);
            unacceptedAgreement.setPreloader();
        },
        success: function (resp) {
            if (resp['error']) {
                if (resp['time_left']) {
                    unacceptedAgreement.delete_sms_timer();
                    unacceptedAgreement.init_sms_timer(resp['time_left']);
                }
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        },
    }).done(function () {
        unacceptedAgreement.removePreloader();
    });
};

// проверка СМС
unacceptedAgreement.validate_sms_code = function () {
    let sms_code = unacceptedAgreement.sms_code_field.val();
    if (sms_code.replaceAll('_', '').length < 4) {
        unacceptedAgreement.wrong_code.hide();
        unacceptedAgreement.short_code.show();
        return;
    }

    unacceptedAgreement.wrong_code.hide();
    unacceptedAgreement.short_code.hide();

    $.ajax({
        url: 'ajax/sms.php?action=check_agreement_acceptance',
        data: {
            code: sms_code,
            phone: $('input[name="agreement_sms_phone"]').val()
        },
        type: 'GET',
        success: function (resp) {
            if (resp.success) {
                $('#unaccepted_agreement').hide();
                $('#accepted_agreement').show();
            } else {
                unacceptedAgreement.wrong_code.show();
                unacceptedAgreement.short_code.hide();
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        },
    });
};

// подгрузка блока с вводом СМС кода
unacceptedAgreement.init_sms_block = function () {
    $('.agreement_acceptance').hide();
    $('#agreement-sms-block').show();
    unacceptedAgreement.sms_code_field = $("input[name='agreement_sms_code']");
    unacceptedAgreement.sms_code_field.inputmask({
        mask: "9999",
        oncomplete: function () {
            unacceptedAgreement.validate_sms_code();
        },
        onKeyDown: function () {
            unacceptedAgreement.wrong_code.hide();
            unacceptedAgreement.short_code.hide();
        }
    });
    unacceptedAgreement.SMSTimerField = $("#agreement-sms-timer");

    unacceptedAgreement.sms_repeat_button = $("#agreement-sms-repeat");
    unacceptedAgreement.sms_repeat_button.on('click', function (e) {
        e.preventDefault();
        unacceptedAgreement.sendSms();
    })

    unacceptedAgreement.access_sms_button = $("#agreement_access_sms");
    unacceptedAgreement.access_sms_button.on('click', unacceptedAgreement.validate_sms_code);

    unacceptedAgreement.wrong_code = $('#agreement_wrong_code');
    unacceptedAgreement.short_code = $('#agreement_short_code');

    unacceptedAgreement.sendSms();
};

$('.agreement_acceptance button').click(function () {
    unacceptedAgreement.init_sms_block();
})
