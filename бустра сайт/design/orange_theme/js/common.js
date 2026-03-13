/**
 * Универсальный плагин обратного таймера
 */
(function ($) {
    $.fn.timerOut = function (options) {
        options = $.extend({}, $.fn.timerOut.defaults, options || {});
        return $(this).each(function () {
            let _this = this,
                second = options.second,
                interval = setInterval(updateTimer, 1000);

            if (typeof (options.onStart) == 'function') {
                options.onStart.call(_this);
            }

            $(_this).html("<div class='timerOut'></div>");

            function updateTimer() {
                second--;
                $(_this).find('.timerOut').text(second);
                if (second === 0) {
                    clearInterval(interval);
                    $(_this).find('.timerOut').remove();

                    if (typeof (options.onComplete) == 'function') {
                        options.onComplete.call(_this);
                    }
                }
            }
        });
    };
    $.fn.timerOut.defaults = {
        second: 30,  // how long it should take to count between the target numbers
        onComplete: null,  // callback method for when the element finishes updating
        onStart: null,
    };
})(jQuery);

/**
 * Универсальная проверка смс кода с функцией обратного вызова
 * @param phone
 * @param code
 * @param _callBack
 */
function validateSMSCode(phone, code, _callBack) {
    $.ajax({
        url: 'ajax/sms.php?action=check',
        data: {
            code: code,
            phone: phone,
        },
        success: function (resp) {
            if (resp.error) {
                alert(resp.error);
            } else {
                _callBack(resp);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        },
    });
};

/**
 * Авторизация по Ajax, с помощью телефона и смс кода
 * @param data
 * @param _callBack
 */
function sendLogin(data, _callBack = null) {
    $.ajax({
        url: 'user/login',
        data: data,
        method: 'POST',
        success: function (resp) {
            if (typeof (_callBack) == 'function') {
                _callBack(resp);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        },
    });
}

/**
 * Валидация телефона в модальном окне
 */
function validatePhone() {
    const $_modal_phone = $("#modal_phone input[name='phone']");

    if (!$_modal_phone.inputmask('isComplete') || !$_modal_phone.valid()) {
        $("#modal_phone input[name='phone']").addClass('is-invalid').closest('div').removeClass('was-validated');
        return;
    }

    sendMetric('reachGoal', 'new_form_aprove');
    $.ajax({
            url: 'ajax/loginCodeByCall.php',
            data: $("#modal_phone input, #modal_phone textarea").serialize(),
            method: 'POST',
            beforeSend: function () {
                $("#modal_phone .modal-content").addClass('loading');
                $("#modal_phone .modal-footer button").prop('disabled', true);
                $('input[name="code"]').remove();
            },
            success: function (resp) {
                if (resp.error) {
                    if (resp.error === 'sms_time') {
                        $(".timerOutWrapper").timerOut({
                            onStart: function () {
                                $(this).removeClass('d-none')
                            },
                            onComplete: function () {
                                $(this).addClass('d-none')
                            },
                            second: resp['time_left']
                        });
                        $("#modal_phone .btn-primary").prop('disabled', false).text('Отправить повторно');
                    } else if (resp.error === 'user_blocked') {
                        $("#staticBackdropLabel").html("<i class='i bi-emoji-frown-fill text-danger fs-1'></i>");
                        $("#modal_phone .modal-body").html("<p class='text-danger text-center'>Клиент заблокирован!</p>");
                        $("#modal_phone .modal-footer").remove();
                    } else if (resp.error_type === 'user_not_find') {
                        $("#main_page_form").submit();
                    } else if (resp.soap_fault) {
                        alert(resp.error);
                    }
                } else if (resp.response === 'exist_user') {
                    const phoneNumber = $("input[name='phone']").val();
                    window.location.href = '/user/login?phone=' + encodeURIComponent(phoneNumber);
                } else if (resp.response === 'user_bee') {
                    const phoneNumber = $("input[name='phone']").val();
                    window.location.href = '/user/login?phone=' + encodeURIComponent(phoneNumber);
                } else {
                    $('#modal_input_sms_code').remove();
                    $("#modal_phone .validate_sms_wrapper").append("<div id='modal_input_sms_code'><input class='form-control' name='code' value='' /><p class='mb-0 mt-2 text-warning lh-1 font-size-small'><small id='codeInSms'>Мы вам уже звоним. Внимательно прослушайте и введите код, продиктованный голосовым роботом</small></p></div>");
                    $('input[name="code"]').inputmask({
                        mask: "9999",
                        oncomplete: function () {
                            validateSMSCode($("#modal_phone [name='phone']").val(), $("#modal_phone [name='code']").val(), function (resp) {
                                if (resp.success) {
                                    $('input[name="code"]').removeClass('is-invalid').closest('div').addClass('was-validated');
                                    $("#modal_phone .modal-content").addClass('loading');
                                    let login_data = {
                                        key: $('input[name="code"]').val(),
                                        real_phone: $("#modal_phone input[name='phone']").val(),
                                        login: 1,
                                        ajax: 1,
                                    }
                                    sendLogin(login_data, function (resp_login) {
                                        if (resp_login.redirect) {
                                            window.location.href = resp_login.redirect;
                                        }
                                        $("#modal_phone .modal-content").remove('loading');
                                    });
                                } else {
                                    $('input[name="code"]').addClass('is-invalid');
                                }
                            });
                        }
                    });

                    $(".timerOutWrapper").timerOut({
                        onComplete: function () {
                            $(this).addClass('d-none');
                            //$("#modal_phone .btn-primary").prop('disabled', false).text('Отправить повторно');
                            $("#modal_phone .validate_sms_wrapper").append("<a href='javascript:void(0)' class='w-100 font-size-small text-center text-dark' id='send_sms_href' onclick='send_sms_login()'>Отправить код по SMS</a>")
                            $("#modal_input_sms_code").hide();
                        },
                        onStart: function () {
                            $(this).removeClass('d-none')
                        },
                    });

                    if (resp['developer_code']) {
                        $('input[name="code"]').val(resp['developer_code']);
                    }
                }
            }

        ,
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        }
        ,
    }).done(function () {
        $("#modal_phone .modal-content").removeClass('loading');
    });
}

//задублировал функцию с некоторыми изменениями, что бы ничего не поломать в основной
function validatePhoneForCalculator() {
    const $_modal_phone = $("#calculator input[name='phone']");

    if (!$_modal_phone.inputmask('isComplete') || !$_modal_phone.valid()) {
        $_modal_phone.addClass('is-invalid').closest('div').removeClass('was-validated');
        return;
    }

    sendMetric('reachGoal', 'new_form_aprove');

    $.ajax({
        url: 'ajax/loginCodeByCall.php',
        data: $("#main_page_form input, #modal_phone textarea").serialize(),
        method: 'POST',
        beforeSend: function () {
            $("#calculator").addClass('loading-calculate');
            $("#get_zaim").prop('disabled', true);
            $('#main_page_form input[name="code"]').remove();
        },
        success: function (resp) {
            if (resp.error) {
                if (resp.error === 'sms_time') {
                    $(".timerOutWrapper").timerOut({
                        onStart: function () {
                            $(this).removeClass('d-none')
                        },
                        onComplete: function () {
                            $(this).addClass('d-none')
                        },
                        second: resp['time_left']
                    });
                    $("#modal_phone .btn-primary").prop('disabled', false).text('Отправить повторно');
                } else if (resp.error === 'user_blocked') {
                    $("#staticBackdropLabel").html("<i class='i bi-emoji-frown-fill text-danger fs-1'></i>");
                    $("#modal_phone .modal-body").html("<p class='text-danger text-center'>Клиент заблокирован!</p>");
                    $("#modal_phone .modal-footer").remove();
                } else if (resp.error_type === 'user_not_find') {
                    $("#main_page_form").submit();
                } else if (resp.soap_fault) {
                    alert(resp.error);
                }
            } else {
                console.log(resp)
                if (resp.response === 'exist_user' || resp.response === 'user_bee') {
                    const phoneNumber = $("#main_page_form input[name='phone']").val();
                    window.location.href = '/user/login?phone=' + encodeURIComponent(phoneNumber);
                } else {
                    $('#modal_input_sms_code').remove();
                    $("#main_page_form .validate_sms_wrapper").append("<div id='modal_input_sms_code'><input class='form-control' name='code' value='' /><p class='mb-0 mt-2 text-warning lh-1 font-size-small'><small id='codeInSms'>Мы вам уже звоним. Внимательно прослушайте и введите код, продиктованный голосовым роботом</small></p></div>");
                    $('input[name="code"]').inputmask({
                        mask: "9999",
                        oncomplete: function () {
                            validateSMSCode($("#main_page_form [name='phone']").val(), $("#main_page_form [name='code']").val(), function (resp) {
                                if (resp.success) {
                                    $('input[name="code"]').removeClass('is-invalid').closest('.col').addClass('was-validated');
                                    $("#calculator").addClass('loading-calculate');
                                    let login_data = {
                                        key: $('input[name="code"]').val(),
                                        real_phone: $("#main_page_form input[name='phone']").val(),
                                        login: 1,
                                        ajax: 1,
                                    }
                                    sendLogin(login_data, function (resp_login) {
                                        if (resp_login.redirect) {
                                            window.location.href = resp_login.redirect;
                                        }
                                        $("#calculator").remove('loading-calculate');
                                    });
                                } else {
                                    $('#main_page_form input[name="code"]').addClass('is-invalid');
                                }
                            });
                        }
                    });

                    $(".timerOutWrapper").timerOut({
                        onComplete: function () {
                            $(this).addClass('d-none');
                            //$("#modal_phone .btn-primary").prop('disabled', false).text('Отправить повторно');
                            $("#modal_phone .validate_sms_wrapper").append("<a href='javascript:void(0)' class='w-100 font-size-small text-center text-dark' id='send_sms_href' onclick='send_sms_login()'>Отправить код по SMS</a>")
                            $("#modal_input_sms_code").hide();
                        },
                        onStart: function () {
                            $(this).removeClass('d-none')
                        },
                    });

                    if (resp['developer_code']) {
                        $('#main_page_form input[name="code"]').val(resp['developer_code']);
                    }
                }
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
            alert(error);
            console.log(error);
        },
    }).done(function () {
        $("#calculator").removeClass('loading-calculate');
    });
}

/*
document.addEventListener('DOMContentLoaded', function () {
    let codes = [959, 949, 900, 901, 902, 903, 904, 905, 906, 908, 909, 910, 911, 912, 913, 914, 915, 916, 917, 918, 919, 920, 921, 922, 923, 924, 925, 926, 927, 928, 929, 930, 931, 932, 933, 934, 936, 937, 938, 939, 941, 949, 950, 951, 952, 953, 954, 955, 956, 958, 960, 961, 962, 963, 964, 965, 966, 967, 968, 969, 970, 971, 977, 978, 980, 981, 982, 983, 984, 985, 986, 987, 988, 989, 991, 992, 993, 994, 995, 996, 997, 999];
    let validateBtn = $(".validate-btn");
    $("[name='phone']").on("keyup", function () {

        let phoneNumber = $(this).val().replace(/\D/g, '');
        let operatorCode = phoneNumber.substr(1, 3);

        if (operatorCode.length === 3 && codes.includes(parseInt(operatorCode, 10))) {
            $("#phone-error").css("display", "none");
            validateBtn.prop("disabled", false);
        } else {
            $("#phone-error").css("display", "block");
            validateBtn.prop("disabled", true);
        }
    });
});
*/

/**
 * Отправка смс
 * @param phone
 */
function loginSms(phone) {
    var xhr = new XMLHttpRequest();
    var body = 'phone=' + encodeURIComponent(phone) +
        '&flag=' + encodeURIComponent('LOGIN') +
        '&check_user=' + encodeURIComponent(1)
        '&huid=' + $('body').data('hh');
    xhr.open("POST", 'ajax/send_sms.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send(body);
    $('#codeInSms').text('Мы отправили Вам код по SMS');
}

/**
 * Отправка кода по смс для входа
 */
function send_sms_login() {
    let phone = $("#modal_phone [name='phone'], #main_page_form [name='phone']").val();
    loginSms(phone);

    $(".timerOutWrapper").timerOut({
        onComplete: function () {
            $(this).addClass('d-none');
            $("#send_sms_href").show().text("Отправить код SMS повторно");
            $("#modal_input_sms_code").hide();
        },
        onStart: function () {
            $(this).removeClass('d-none');
            $("#modal_input_sms_code").show();
            $("#send_sms_href").hide();
        },
    });
}

$(".text-more-btn").on('click', function () {
    $(this).closest('.text-more').find('.text-more-content').slideToggle();
    $(this).remove();
});

$(document).ready(function () {
    const $_modal_phone = $("#modal_phone input[name='phone']");

    $("#car-deposit-popup-close").on('click', function () {
        console.log(1)
        $("#car-deposit-popup").addClass('d-none');
    });

    $("#modal_phone form").validate({
        errorElement: "div",
        rules: {
            "phone": {
                Code: true,
            }
        }
    });

    $_modal_phone.inputmask({
        mask: "+7 (999) 999-99-99",
        clearIncomplete: true,
        onKeyDown: function () {
            $(this).addClass('is-invalid').closest('div').removeClass('was-validated');
        },
        oncomplete: function () {
            if ($(this).valid()) {
                $(this).removeClass('is-invalid').closest('div').addClass('was-validated');
            }
        },
    });

    // метрика по кнопке получить займ
    $("#get_zaim").on('click', function () {
        sendMetric('reachGoal', 'main_page_get_zaim_new_design');
        const goal_id = window.percent_calculate === 0 ? 1 : 2;
        sendCustomMetric(goal_id);
    });
});

$('.action-scroll_to').on('click', function (event) {

    if (event.target.getAttribute('scroll_to') === null) {
        console.log('attribute "scroll_to" is not defined');
        return;
    }

    scrollToElement(event.target.getAttribute('scroll_to'));
});

function scrollToElement(selector) {

    let element = $(selector),
        callback = function () {
        };

    if (element.length !== 1) {
        console.log('Element to scroll to is bad, please, revise the selector. Found: ' + element.length + 'elements');
        return;
    }

    let offset = element.offset().top;

    // List of selectors and theirs callbacks
    if (selector === '#calculator_wrapper') {
        callback = function (par) {
            ym(45594498, 'reachGoal', 'scroll_to_calculator_footer_button');
        }
    }

    scrollToOffset(offset, callback);
}

function scrollToOffset(offset, callback) {
    callback = callback || function () {
    };
    $("html").stop().animate({scrollTop: offset}, 500, 'linear', callback);
}

$('.cbr_link').click(function() {
     event.preventDefault();
     
     const linkUrl = $(this).attr('href');
    
    $.ajax({
        url: 'ajax/client_action_handler.php?action=clickCbrLink',
        method: 'GET',
        success: function(response) {
            window.open(linkUrl, '_blank');
        },
        error: function(xhr, status, error) {
            window.open(linkUrl, '_blank');
        }
    });
});