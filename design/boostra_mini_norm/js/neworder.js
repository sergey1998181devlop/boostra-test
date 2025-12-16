_cb = function (res) {
    if (res.type) {
        $('#check-' + res.type).show();
        $('[name=photo\\[' + res.type + '\\]]').val('');
        $('#file-' + res.type)[0].outerHTML = $('#file-' + res.type)[0].outerHTML;
    }
    if (res.msg) {
        alert(res.msg);
    }
};
var Neworder = {
    check_mobile: function (phone, cb) {
        var phone_clear = phone.replace(/\D/g, '');
        var data = 'ajax/cart.php?phone=' + phone_clear;//+ "&e_mail=" + $('#e_mail').val();
        $.post('', data, function (res) {
            var result = $.parseJSON(res);
            cb();

            //$('#sms_count').text('Осталось попыток: ' + (2 - result['sms_count']));
            if (result['send']) {
                $('#mobile_code').attr('disabled', false);
                $('#sms_result_false').hide();
                $('#sms_result_true').show();
                $('#sms_result_true').text(result['desc']);
            } else {
                $('#mobile_code').attr('disabled', true);
                $('#sms_result_true').hide();
                $('#sms_result_false').show();
                $('#sms_result_false').text(result['desc']);
            }
        });
    },
    check_sms_code: function (code, cb) {
        var data = 'action=checkSmsCode&mobile_code=' + code;
        $.post('', data, function (res) {
            var result = $.parseJSON(res);
            if (result['mobile_code']) {
                $('#sms_result_true').hide();
                $('#sms_result_false').show();
                $('#sms_result_false').text(result['mobile_code']);
            } else {
                //yaCounter18908710.reachGoal('SMS_CONFIRMED');
                $('#sms_result_false').hide();
                $('#sms_result_true').show();
                $('#sms_result_true').text("Код совпал");
                cb();
            }
        });
    },
    //copyAddress: function () {
        //copyAddress();
    //},
    saveStep: function (step, cb) {
        var f1 = $('form#questionnaire');
        //copyAddress();

        $('small.err,span.err', f1).hide();

        $.post('?act=Step' + step, $(f1).serialize(), function (res) {
            if (res.error) {
                var err_txt = '';
                for (var i in res.error) {
                    var el = $('[name=' + i + ']', f1);
                    var err_el = $('#err-' + i + '', f1);
                    if (el.length > 0 && err_el.length > 0) {
                        el.addClass('error').attr('title', res.error[i]);
                        err_el.html(res.error[i]).show();
                    } else {
                        err_txt += res.error[i] + "\n";
                    }
                }
                if (err_txt) {
                    //console.log();
                    alert(err_txt);
                }
            } else {
                yaCounter18908710.reachGoal('STEP_' + step);
                cb();
            }
        }, 'json');
    }, create: function (cb) {
        var f1 = $('form#questionnaire');
        //copyAddress();

        $('small.err,span.err', f1).hide();

        $.post($(f1).attr('action'), $(f1).serialize(), function (res) {
            if (res.error) {
                var err_txt = '';
                for (var i in res.error) {
                    var el = $('[name=' + i + ']', f1);
                    var err_el = $('#err-' + i + '', f1);
                    if (el.length > 0 && err_el.length > 0) {
                        el.addClass('error').attr('title', res.error[i]);
                        err_el.html(res.error[i]).show();
                    } else {
                        err_txt += res.error[i] + "\n";
                    }
                }
                if (err_txt) {
                    //console.log();
                    alert(err_txt);
                }
            } else {
                document.location.href = res.redirect;
            }
        }, 'json');
    }
};
$(function () {

    $('body').on('change', '[name^=file]', function () {
        $(this.form).submit();
    });

    var lPaytype_val = parseInt($('#paytype').val());
    $(".paytype_radio[paytype-id=" + lPaytype_val + "]").find('.paytype_radio_ok').show();

    $(".paytype_radio").click(function () {
        $('#paytype').val($(this).attr('paytype-id'));
        $('.paytype_radio_ok').hide();
        $(this).find('.paytype_radio_ok').show();
    });

    $('#mobile_number,.mobile,.phone').inputmask("+7 (999) 999-99-99");
    $('#pasport_number').inputmask("9999-999999");
    $('#pasport_ovd_zip,#subdivision_code').inputmask("999 999");
    $('#pasport_ovd').keyup(function (e) {
        var val = $(this).val();
        val = val.replace(new RegExp('[^а-я,Ёё\\s\-\.]', 'i'), '');
        $(this).val(val);
    });
    $('#birthday, #pasport_date, #prop_date, #next_zp_date, .date').inputmask("99.99.9999");
    $('#work_dohod').inputmask("999999999999", {"placeholder": " "});
    $('#tin,#inn').inputmask("999999999999");
    $('#snils').inputmask("999-999-999-99");
    $('#oms_num').inputmask("9999999999999999");
    $('#drivers_licence').inputmask("99 ** 999999");
    $('#ym_number').inputmask("999999999999999");
    $('#card_number').inputmask("99999999999999999999");
    $('#card_cvv').inputmask("999");

    $('#card_owner').keyup(function (e) {
        var val = $(this).val();
        val = val.replace(new RegExp('[^a-z\\s]', 'i'), '');
        $(this).val(val);
    });

    $(".chkb_correct").click(function (e) {
        tryToggleCheckMobileBrn();
    });

    $('#sync').click(function () {
        $("#prg").toggleClass("hide");
    });
    

    function tryToggleCheckMobileBrn() {
        var lCheckMobileBtnEnabled = true;
        $(".chkb_correct").each(function (aIndex, aElem) {
            if (!$(aElem).prop("checked")) {
                lCheckMobileBtnEnabled = false;
                return false;
            }
        });
        $('.check_mobile').prop("disabled", !lCheckMobileBtnEnabled);
    }
    tryToggleCheckMobileBrn();

    var checkConfirm = function () {
        var confirm_checkbox = ['civilization', 'agree', 'personal_data'];
        for (var i = 0; i < confirm_checkbox.length; i++) {
            var el = $('[name=' + confirm_checkbox[i] + ']');
            if (!el.prop('checked')) {
                var text = el.parents('label').text();
                $('#sms_result_false').text('Не установлена галочка ' + text).show();
                return false;
            }
        }
        return true;
    };


    $('.check_mobile').on('click', function (e) {

        if (!checkConfirm()) {
            return false;
        }


        console.log('checked');
        var but = this;
        var phone = $('#mobile_number,#phone_mobile').val();
        var phone_clear = phone.replace(/\D/g, '');

        if (phone_clear.length !== 11) {
            $('#sms_result_false').show();
            $('#sms_result_false').text('Некорректный номер');
        } else {
            $(this).prop("disabled", true);
            $(this).data('text', $(this).text()).text("Обработка...");

            var data = 'action=smsoferta&phone=' + phone_clear;//+ "&e_mail=" + $('#e_mail').val();
            $.post('', data, function (res) {
                $('#confirm').fadeIn();
                var t = 60;
                var timer = function () {
                    t--;
                    if (t < 0) {
                        $('.check_mobile').attr('disabled', false).val('Отправить код');
                        $("#confirm_timer").text("00:00");

                    } else {
                        if (t < 10) {
                            $("#confirm_timer").text("00:0" + t);
                        } else {
                            $("#confirm_timer").text("00:" + t);
                        }
                        setTimeout(timer, 1000);
                    }
                };
                setTimeout(timer, 1000);

                var result = $.parseJSON(res);
                $('#sms_count').text('Осталось попыток: ' + (2 - result['sms_count']));
                if (result['send']) {
                    $('#mobile_code').attr('disabled', false);
                    $('#sms_result_false').hide();
                    $('#sms_result_true').show();
                    $('#sms_result_true').text(result['desc']);
                } else {
                    $('#mobile_code').attr('disabled', true);
                    $('#sms_result_true').hide();
                    $('#sms_result_false').show();
                    $('#sms_result_false').text(result['desc']);
                }
                $(but).text($(but).data('text'));
            });

        }
        return false;
    });

    $('#showSmsModal').click(function () {

        var f1 = $('form#questionnaire');
       // copyAddress();
        $('#rules_agree').text('');
        $('.input-field_text', f1).removeClass('error').removeAttr('title');
        $('small.err,span.err', f1).hide();

        $.post($(f1).attr('action'), $(f1).serialize(), function (res) {
            if (res.error) {
                var err_txt = '';
                for (var i in res.error) {
                    var el = $('[name=' + i + ']', f1);
                    var err_el = $('#err-' + i + '', f1);
                    if (el.length > 0 && err_el.length > 0) {
                        el.addClass('error').attr('title', res.error[i]);
                        err_el.html(res.error[i]).show();
                    } else {
                        err_txt += res.error[i];
                    }
                    if (i == 'network') {
                        $('.social-err').show();
                    }
                }
                if (err_txt) {
                    //alert(err_txt);
                }
                if ($('.error:visible').length > 0) {
                    $('html, body').animate({scrollTop: $('.error:visible').offset().top - $('header').height() - 30}, 500);
                }

            } else {
                $('#confirm').fadeIn();

                $('html, body').animate({scrollTop: $('#confirm').offset().top + 300}, 500);

                $('#showSmsModal').hide();
                yaCounter18908710.reachGoal('poluchit');
            }
        }, 'json');

    });

    $('#create_loan').click(function () {
        if (!checkConfirm()) {
            return false;
        }

        var data = 'action=checkSmsCode&mobile_code=' + $('#mobile_code').val();
        $.post('', data, function (res) {
            var result = $.parseJSON(res);
            if (result['mobile_code']) {
                $('#sms_result_true').hide();
                $('#sms_result_false').show();
                $('#sms_result_false').text(result['mobile_code']);
            } else {
                $('#sms_result_false').hide();
                $('#sms_result_true').show();
                $('#sms_result_true').text("Код совпал");
                document.location.href = result['redirect'];
            }
        });

        return false;
    });


    function resultmessage(result) {
        if (result != '') {
            alert(result);
        } else {
            alert('На указанный Вами номер было выслано смс с кодом подтверждения');
        }
    }



    $('#copy_address').prop("disabled", true);
    $('#sync').on('click', function () {
        $('#prog_city').val('');
        $('#prog_street').val('');
        $('#prog_home').val('');
        $('#prog_home_build').val('');
        $('#prog_flat').val('');
        $('#prog_phone').val('');
        $('#prog_region').val('');
        $('#prog_zip').val('');
        $('#prog_city_type').val('');
        $('#prog_street_type_long').val('');
        $('#prog_street_type_short').val('');

        return false;
    });

    $('.ajax').submit(function () {

        if (false && $('.chkb_correct:not(:checked)').length > 0) {
            $('#rules_agree').text('Вы должны принять правила оферты!');
        } else {
            var f1 = $('form#questionnaire');
            //copyAddress();
            $('#rules_agree').text('');
            $('.input-field_text', f1).removeClass('error').removeAttr('title');
            $('small.err,span.err', f1).hide();

            $.post($(f1).attr('action'), $(f1).serialize(), function (res) {
                if (res.error) {
                    var err_txt = '';
                    for (var i in res.error) {
                        var el = $('[name=' + i + ']', f1);
                        var err_el = $('#err-' + i + '', f1);
                        if (el.length > 0 && err_el.length > 0) {
                            el.addClass('error').attr('title', res.error[i]);
                            err_el.html(res.error[i]).show();
                        } else {
                            err_txt += res.error[i] + "\n";
                        }
                    }
                    if (err_txt) {
                        //console.log();
                        alert(err_txt);
                    }
                    $('html, body').animate({scrollTop: $('.error:visible').offset().top - $('header').height() - 30}, 500);
                } else {
                    document.location.href = res.redirect;
                }
            }, 'json');
        }
        return false;
    });
    $('#rules_of_graining_loans').attr("checked", false);



    $('.soc-href').click(function () {
        var name = this.href.replace(new RegExp('.*#'), '');
        if (name) {
            $.post('/neworder/social/?name=' + name, $('#questionnaire').serialize(), function (res) {
                if (res.redirect) {
                    document.location.href = res.redirect;
                }
            }, 'json');
        }
        return false;
    });
});