const pdnExcess = new Object({});
pdnExcess.data = new Object({});
pdnExcess.step = 0;
pdnExcess.DEFAULT_SMS_DELAY_SECONDS = 30;
pdnExcess.sms_timer_second = 0;

pdnExcess.removePreloader = function () {
    $('body').removeClass('loading');
}

pdnExcess.setPreloader = function () {
    $('body').addClass('loading');
}

// функция таймера отправки смс
pdnExcess.init_sms_timer = function (seconds) {
    pdnExcess.sms_timer_second = seconds;
    pdnExcess.sms_repeat_button.hide();
    pdnExcess.SMSTimerField.show();

    pdnExcess.sms_timer = setInterval(function () {
        if (pdnExcess.sms_timer_second === 0) {
            pdnExcess.delete_sms_timer();
            pdnExcess.sms_repeat_button.show();
        } else {
            pdnExcess.SMSTimerField.text(pdnExcess.sms_timer_second);
        }
        pdnExcess.sms_timer_second--;
    }, 1000);
};

// выключение таймера и снятие блокировок
pdnExcess.delete_sms_timer = function () {
    clearInterval(pdnExcess.sms_timer);
    pdnExcess.sms_repeat_button.show();
    pdnExcess.SMSTimerField.hide();
};

pdnExcess.sendSms = function () {
    $.ajax({
        url: 'ajax/sms.php',
        data: {
            action: 'send',
            phone: $('input[name="pdn_excess_sms_phone"]').val(),
            flag: 'АСП'
        },
        dataType: 'json',
        beforeSend: function () {
            $(pdnExcess.sms_code_field).closest('.sms_code_wrapper').removeClass('has-error has-success');
            pdnExcess.init_sms_timer(pdnExcess.DEFAULT_SMS_DELAY_SECONDS);
            pdnExcess.setPreloader();
        },
        success: function (resp) {
            if (resp['error']) {
                if (resp['time_left']) {
                    pdnExcess.delete_sms_timer();
                    pdnExcess.init_sms_timer(resp['time_left']);
                }
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        },
    }).done(function () {
        pdnExcess.removePreloader();
    });
};

// проверка СМС
pdnExcess.validate_sms_code = function () {
    let sms_code = pdnExcess.sms_code_field.val();
    if (sms_code.replaceAll('_', '').length < 4) {
        pdnExcess.wrong_code.hide();
        pdnExcess.short_code.show();
        return;
    }

    pdnExcess.wrong_code.hide();
    pdnExcess.short_code.hide();

    $.ajax({
        url: 'ajax/sms.php?action=check_pdn_excess',
        data: {
            code: sms_code,
            phone: $('input[name="pdn_excess_sms_phone"]').val()
        },
        type: 'GET',
        success: function (resp) {
            if (resp.success) {
                $.magnificPopup.close();
            } else {
                pdnExcess.wrong_code.show();
                pdnExcess.short_code.hide();
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
pdnExcess.init_sms_block = function () {
    $('.accept_modal').hide();
    $('#agreement-sms-block').show();
    pdnExcess.sms_code_field = $("input[name='pdn_excess_sms_code']");
    pdnExcess.sms_code_field.inputmask({
        mask: "9999",
        placeholder: "",
        oncomplete: function () {
            pdnExcess.validate_sms_code();
        },
        onKeyDown: function () {
            pdnExcess.wrong_code.hide();
            pdnExcess.short_code.hide();
        }
    });
    pdnExcess.SMSTimerField = $("#agreement-sms-timer");

    pdnExcess.sms_repeat_button = $("#pdn_excess-sms-repeat");
    pdnExcess.sms_repeat_button.on('click', function (e) {
        e.preventDefault();
        pdnExcess.sendSms();
    })

    pdnExcess.access_sms_button = $("#pdn_access_sms");
    pdnExcess.access_sms_button.on('click', pdnExcess.validate_sms_code);

    pdnExcess.wrong_code = $('#pdn_wrong_code');
    pdnExcess.short_code = $('#pdn_short_code');

    pdnExcess.sendSms();
};

$('.modal_sms_footer .accept_modal button').click(function () {
    pdnExcess.init_sms_block();
})