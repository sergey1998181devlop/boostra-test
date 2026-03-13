/* global viberBotName, tlgBotName */
let config = { dev: false };

import('./config/config.js')
    .then(module => {
        config = new module.default();
        useConfig(config);
    })
    .catch(err => {
        console.error('Error loading module:', err);
    });

function useConfig(config) {
    new LoginApp(config);
}

function runInterval()
{
    var timeinterval;
    var noactive_period = 60;
    
    timeinterval = setInterval(function () {
        noactive_period--;
        //console.info(noactive_period)
        if (noactive_period > 0)
        {
            $('.repeat_sms').html('<span>Отправить код еще раз через ' + noactive_period + ' сек</span>');
        } else         {
            $('.repeat_sms').html('<a href="#" class="new_sms fast_send">Отправить код через смс</a>');
            clearInterval(timeinterval);
        }
    }, 1000);

}

function LoginApp(config)
{
    var app = this;
    var _init = function () {
        app.active_view = app.LOGIN_BY_SMS_VIEW;
        const scriptElement = document.createElement('script');
        scriptElement.src = 'https://smartcaptcha.yandexcloud.net/captcha.js?render=onload';
        scriptElement.onload = initSmartCaptcha;
        scriptElement.onerror = function (error) {
            console.log('Error Smart captcha script error: ', error);
        };
        document.body.appendChild(scriptElement);
    };

    app.RESET_PASSWORD_VIEW = 'RESET_PASSWORD_VIEW';
    app.FIRST_PAGE_VIEW = 'FIRST_PAGE_VIEW';
    app.EDIT_PASSWORD_VIEW = 'EDIT_PASSWORD_VIEW';
    app.LOGIN_PASSWORD_VIEW = 'LOGIN_PASSWORD_VIEW';
    app.ADD_PASSWORD_VIEW = 'ADD_PASSWORD_VIEW';
    app.LOGIN_BY_SMS_VIEW = 'LOGIN_BY_SMS_VIEW';
    app.CHECK_SMS_CODE_LOGIN_ACTION = 'CHECK_SMS_CODE_LOGIN_ACTION';

    app.init_messengers = function () {
        $(document).on('click', '.js-login-btn', function (event) {
            var $this = $(this);

            if ($this.hasClass('loading'))
                return false;

            if (!$(this).closest('form').valid())
                return false;

            var _messenger = $this.data('messenger');
            var _phone = $('input[name="phone"]').val();

            $.ajax({
                url: 'ajax/send_sms.php',
                async: false,
                type: 'POST',
                data: {
                    messenger: _messenger,
                    phone: _phone
                },
                beforeSend: function () {
                    $this.addClass('loading');
                },
                success: function (resp) {
                    if (!!resp.link)
                    {
                        $('#send').hide();
                        $('input[name="real_phone"]').val(_phone);
                        $('#check').fadeIn();

                        if (_messenger == 'whatsapp')
                        {
                            event.preventDefault();
                            $.ajax({
                                url: 'https://www.boostra.ru/chats.php?api=whatsapp&method=sendCode&phone=' + _phone
                            })
                        } else
                        {
                            $this.attr('href', resp.link);
                        }


                        $("#check_title").html('Введите код из мессенджера');
                        $('.repeat_sms').html('<a href="#" class="new_sms fast_send">Отправить код через смс</a>');


                        return true;
                    } else if (!!resp.error)
                    {
                        event.preventDefault();

                        $('[name=key]').closest('label').remove();
                        $('#check').addClass('error').html('<p><span class="error" style="display:block!important">' + data.error + '</span></p><br /><br /><p><a href="init_user" class="button big">Заявка на займ</a></p>');

                    }
                }
            })
        });

    };

    app.init_validators = function () {

        $("input[name='phone']").inputmask({
            mask: "+7 (999) 999-99-99",
            clearIncomplete: true,
            oncomplete: function () {
                if (!window.loginPhoneInput) {
                    sendMetric('reachGoal', 'podtverdil_tel');
                    window.loginPhoneInput = 1;
                }
            },
        });

        $.extend($.validator.messages, {
            required: "Данное поле необходимо заполнить.",
        });

        $.validator.addMethod("NoCyrillicLetters", function (value, element, param) {
            if (config.dev) {
                return true;
            }
            return this.optional(element) || /^[^а-яё]+$/iu.test(value);
        });

        $.validator.addMethod("Code", function () {
            if (config.dev) {
                return true;
            }
            var codes = [
                900, 901, 902, 903, 904, 905, 906, 908, 909,
                910, 911, 912, 913, 914, 915, 916, 917, 918, 919,
                920, 921, 922, 923, 924, 925, 926, 927, 928, 929,
                930, 931, 932, 933, 934, 936, 937, 938, 939,
                941, 949,
                950, 951, 952, 953, 954, 955, 956, 958, 959,
                960, 961, 962, 963, 964, 965, 966, 967, 968, 969, 970,
                971, 977, 978,
                980, 981, 982, 983, 984, 985, 986, 987, 988, 989,
                990, 991, 992, 993, 994, 995, 996, 997, 999
            ];

            if ($('input[name="phone"]').val() == '+7 (940) 710-67-27')
                return true;
            //if (codes.indexOf(parseInt($('input[name="phone"]').val().substring(4, 7))) != -1)
                return true;
        }, "Введите правильный код оператора");

        $.validator.addMethod("Key", function () {
            if (config.dev) {
                return true;
            }
            var val = $('input[name="key"]').val();
            var key = parseInt(val);
            if (!isNaN(parseFloat(val)) && isFinite(val) /*&& key >= 1000 && key <= 999999*/)
                return true;
        }, "Введите код в правильном формате");

        var validator = $("#login #send").validate({
                errorElement: "span",
                rules: {
                    "phone": {
                        Code: true
                    }
                }
            }),
            validator2 = $("#login #check").validate({
                errorElement: "span",
                rules: {
                    "key": {
                        Key: true
                    }
                }
            });
    };

    app.init_input_label = function () {
        $("input:not(:checkbox), textarea, select", "#login").each(function (i, v) {
            var placeholder = $(v).attr('placeholder');
            if (placeholder != null)
            {
                $(v).attr('placeholder', '');
                $(v).parent().append('<span class="floating-label">' + placeholder + '</span>');
            }
        });

    };

    /**
     * Инициализация экрана восстановления пароля
     * Клик по ссылке "забыли пароль"
     */
    app.init_reset_password = function () {
        $("#login .alert").remove();
        app.active_view = app.RESET_PASSWORD_VIEW;
        $("#login_form_title").text("Восстановление пароля");
        $("#send #login_form_footer").hide();
        $("#send #login_form_description").text("Введите номер телефона, который Вы указали при регистрации. " +
            "На него мы отправим код, после чего Вы сможете установить новый пароль");
        $("#send #wrapper_fields #label_password").hide();
        $('[name="password"]').rules("remove");
    }

    /**
     * Отправка кода для подтверждения изменения пароля
     */
    app.reset_password_action = function () {
        $("#login .alert").remove();
        let phone = $('input[name="phone"]').val();
        $('#send').addClass('loading');

        send_sms_code(phone, 0, false, 'reset_password_action', function () {
            $('#send, #check [type="submit"]')
                .removeClass('loading')
                .hide();

            $('input[name="real_phone"]').val(phone);
            $('#check').fadeIn();
        });
    }

    /**
     * Инициализация окна с входом по паролю после первого экрана
     */
    app.init_login_page_view = function () {
        $("#label_password").remove();
        $("#login_form_footer").empty();

        $("#send #login_form_description").text("Введите номер телефона, который Вы указали при регистрации, и пароль");
        $("#send #wrapper_fields").append('<label id="label_password">\n' +
            '<div class="form-control">' +
            '<input autocomplete="on" type="password" name="password" required value=""/>' +
            '<i class="toggle-password input-icon bi bi-eye-slash"></i>' +
            '</div>\n' +
            '<p class="password-warning">Внимание: после 6 неудачных попыток ввода пароля, личный кабинет будет заблокирован.</p>' +
            '</label>');

        $("#send #login_form_footer").append('<div>' +
            '<a href="javascript:void(0);" id="login_form_link_reset">Забыли пароль?</a>' +
            '</div>');

        app.init_password();
    }

    /**
     * Событие на первом экране, проверяет есть ли пароль у пользователя
     */
    app.first_page_action = function () {
        let phone = $('input[name="phone"]').val();
        $.ajax({
            type: "POST",
            url: "ajax/user.php?action=has_user_password",
            data: {
                phone,
            },
            dataType: 'json',
            success: function (json) {
                if(json['success']) {
                    // если у пользователя есть пароль
                    app.init_login_page_view();
                    app.active_view = app.LOGIN_PASSWORD_VIEW;
                } else {

                    if (json['error'] === 'user_blocked') {
                        $("#login_form_title").html('<i style="font-size: 3rem !important;" class="i bi-emoji-frown-fill text-red"></i>');
                        $("#send").empty().html('<p class="text-red">Личный кабинет недоступен!</p>');
                    } else {
                        // если у пользователя нет ранее созданного пароля
                        $("#send #login_form_description").text("Для повышения уровня защиты Ваших персональных данных мы перешли на формат авторизации по логину и паролю.\n" +
                            "Введите код из смс и приступите к созданию пароля.\n" +
                            "Это быстро - мы обещаем:)");

                        send_sms_code(phone, 0, $('#send'), 'first_page_action', function () {
                            $('#send').hide();
                            $('input[name="real_phone"]').val(phone);
                            $('#check').fadeIn();
                            app.active_view = app.ADD_PASSWORD_VIEW;
                        });
                    }
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
     * Инициализируем поле пароль
     */
    app.init_password = function (placeholder = 'Пароль') {
        const $_password = $('[name="password"]');

        $_password.rules("remove");
        $_password.rules("add", {
            required: true,
            minlength: 4,
            maxlength: 24,
            NoCyrillicLetters: !config.dev,
            messages: {
                minlength: jQuery.validator.format("Введите не менее {0} символов"),
                maxlength: jQuery.validator.format("Введите не более {0} символов"),
                NoCyrillicLetters: jQuery.validator.format("Недопустимые символы"),
            }
        });

        // Костыль для Placeholder т.к. иногда тормозит плагин validate
        $('#label_password .floating-label').remove();
        $('#label_password input').after('<span class="floating-label">'+ placeholder +'</span>');
    }

    /**
     * Окно восстановление пароля - событие после отправки смс кода
     */
    app.reset_password_check_action = function () {
        let phone = $('input[name="phone"]').val(),
            $form_check = $('#check'),
            sms = $('input[name="key"]').val();

        validateSMSCode(phone, sms, function (resp){
            $('input[name="key"]').attr('readonly', false)
            $form_check.removeClass('loading');
            $("#login .alert").remove();

            if (!!resp.success) {
                $('#send').fadeIn();
                $form_check.fadeOut();

                $("#send #login_form_description").text("Придумайте новый пароль, запишите и храните в надёжном месте");
                $("#send #wrapper_fields #label_password").show();

                const $_login_form_phone_label = $('#send #login_form_phone');
                $_login_form_phone_label.find('div').hide();
                $_login_form_phone_label.find('input[name="phone"]').rules('remove', 'required');

                app.init_password('Введите новый пароль');
                app.active_view = app.EDIT_PASSWORD_VIEW;
            } else {
                app.add_error_block('Код введен неверно');
            }
        });
    }

    /**
     * Событие отправки нового пароля
     */
    app.edit_password_action = function () {
        let phone = $('input[name="phone"]').val(),
            password = $('input[name="password"]').val();

        $.ajax({
            type: "POST",
            url: "ajax/user.php?action=edit_user_password",
            data: {
                phone,
                password,
            },
            dataType: 'json',
            success: function (json) {
                if(json['success']) {
                    $("#send #login_form_description").text("Пароль сохранён. Авторизуйтесь повторно на сайте для перехода в личный кабинет");
                    $("#login_form_title").text("Вход в личный кабинет заёмщика");

                    const $_login_form_phone_label = $('#send #login_form_phone');
                    $_login_form_phone_label.find('div').show();
                    $_login_form_phone_label.find('input[name="phone"]').rules('add', {required: true});

                    app.init_password();

                    app.active_view = app.LOGIN_PASSWORD_VIEW;
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
     * Добавляет блок с ошибками
     * @param text
     */
    app.add_error_block = function (text = '') {
        $("#login .alert").remove();
        $("#login .wrapper").prepend('<div class="alert alert-danger" role="alert">' + text + '</div>');
    }

    /**
     * Логинемся по логину и паролю
     */
    app.login_password_action = function () {
        let phone = $('input[name="phone"]').val(),
            password = $('input[name="password"]').val();

        $.ajax({
            type: "POST",
            url: "ajax/user.php?action=login_user_password",
            data: {
                phone,
                password,
            },
            dataType: 'json',
            beforeSend: function () {
                $('#send').addClass('loading');
            },
            success: function (json) {
                localStorage.removeItem('showModalAsp')
                delete localStorage.graceValue
                delete localStorage.graceData
                delete localStorage.graceButton
                if(json['success']) {
                    window.location = json['redirect_url'];
                } else if (json['error'] === 'doubling_phone') {
                    $("#login_form_title").html('<i style="font-size: 3rem !important;" class="i bi-emoji-frown-fill text-red"></i>');
                    $("#send").empty().html('<p class="text-red">Пользователь с таким номером уже зарегистрован.<br />С Вами свяжется Клиентский Центр.</p>');
                } else {
                    app.add_error_block(json['error']);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                alert(error);
                console.log(error);
            },
        }).done(function () {
            $('#send').removeClass('loading');
        });
    }

    /**
     * Событие создания нового пароля
     */
    app.add_password_code_action = function () {
        $('#check').hide();
        $('#send').show();

        $("#send #login_form_description").text("Для повышения уровня защиты Ваших персональных данных мы перешли на формат авторизации по логину и паролю. " +
            "Придумайте и сохраните свой новый пароль." +
            " В дальнейшем Вы будете его использовать при авторизации");

        $("#send #wrapper_fields").append('<label id="label_password">\n' +
            '<div class="form-control">' +
            '<input autocomplete="on" type="password" name="password" required value=""/>' +
            '<i class="toggle-password input-icon bi bi-eye-slash"></i>' +
            '</div>\n' +
            '</label>');

        app.init_password('Введите пароль');
    };

    /**
     * Добавляет новый пароль пользователю
     */
    app.add_password_action = function () {
        let phone = $('input[name="phone"]').val(),
            password = $('input[name="password"]').val();

        $.ajax({
            type: "POST",
            url: "ajax/user.php?action=add_user_password",
            data: {
                phone,
                password,
            },
            dataType: 'json',
            success: function (json) {
                if(json['success']) {
                    app.init_login_page_view();
                    app.active_view = app.LOGIN_PASSWORD_VIEW;
                } else {
                    app.add_error_block(json['error']);
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
     * Отправка смс, при способе авторизации через смс
     */
    app.login_by_sms_action = function (action) {
        let phone = $('input[name="phone"]').val();
        let smart_token = ($("[name='smart-token']").val() || window._loginState?.['smart-token']);

        if(!smart_token) {
            alert('Необходимо подтвердить, что вы не робот!')
            return false
        }

        $('#send').addClass('loading');
        send_sms_code(phone, 0, $('#send'), (action || 'login_by_sms_action'), function (responseData) {
            const {response} = responseData;
            if (response === 'exist_user' || response === 'user_bee') {
                loginSms(phone, function () {
                    app.active_view = app.CHECK_SMS_CODE_LOGIN_ACTION;
                    $('#send').hide();
                    $('input[name="real_phone"]').val(phone);
                    $('#check').fadeIn();
                });
            } else {
                $('#send').removeClass('loading');
            }
        });

        return true
    }

    /**
     * Событие, когда человек прошел проверку СМС при логине
     */
    app.check_sms_code_login_action = function () {
        let sms_code = $('input[name="key"]').val(),
            phone = $('input[name="phone"]').val();
        let asp_input = $('#inp-asp-checkbox').val()
        $.ajax({
            url: 'ajax/sms.php',
            data: {
                phone: phone,
                action: 'check_login',
                code: sms_code,
                asp_input: asp_input,
            },
            success: function (resp) {
                $('#check .alert').remove();
                if (resp.success) {
                    sendMetric('reachGoal', 'vvel_sms_vhod');
                    $('#check')
                        .empty()
                        .html("<p>Происходит переход в личный кабинет</p>");
                    window.location.href = "/user";
                } else {
                    $('input[name="key"]')
                        .removeClass('valid')
                        .addClass('error')
                        .removeAttr('readonly');
                    $('#check').prepend('<div class="alert alert-danger">' + (resp.error || 'Введите корректный код из смс') + '</div>');
                }
            },
            complete: function () {
                $('#check').removeClass('loading');
            }
        });
    }

    /**
     * События кнопок
     */
    app.init_buttons = function () {

        // ссылка забыли пароль
        $(document).on('click', '#login_form_link_reset', function () {
            app.init_reset_password();
        });

        // события формы с телефоном или паролем
        $('#send').submit(function (event) {
            $("#login .alert").remove();
            if(app.active_view !== app.LOGIN_BY_SMS_VIEW || window.same_tab_login) {
                event.preventDefault();
            } else {
                document.querySelector('.big.new-button').disabled = true
            }
            if (is_developer || $(this).valid()) {
                console.log(app.active_view, 'app.active_view')
                switch (app.active_view) {
                    case app.FIRST_PAGE_VIEW:
                        app.first_page_action();
                        break;
                    case app.RESET_PASSWORD_VIEW:
                        app.reset_password_action();
                        break;
                    case app.EDIT_PASSWORD_VIEW:
                        app.edit_password_action();
                        break;
                    case app.LOGIN_PASSWORD_VIEW:
                        app.login_password_action();
                        break;
                    case app.ADD_PASSWORD_VIEW:
                        app.add_password_action();
                        break;
                    case app.LOGIN_BY_SMS_VIEW:
                        if(!app.login_by_sms_action()) {
                            document.querySelector('.big.new-button').disabled = false
                            event.preventDefault();
                        }
                        break;
                }
            }
        });

        // события с формы ввода кода смс
        $('#check').submit(function (event) {
            $("#login .alert").remove();
            event.preventDefault();
            if (is_developer || $('#check').valid()) {
                switch (app.active_view) {
                    case app.ADD_PASSWORD_VIEW:
                        app.add_password_code_action();
                        break;
                    case app.RESET_PASSWORD_VIEW:
                        app.reset_password_check_action();
                        break;
                    case app.CHECK_SMS_CODE_LOGIN_ACTION:
                        app.check_sms_code_login_action();
                        break;
                    default:
                        $('#check').submit();
                }
            }
        });

        $(document).on('click', 'a.new_sms', function (event) {
            event.preventDefault();
            if ($(this).hasClass('fast_send'))
            {
                var phone = $('input[name="phone"]').val();
                loginSms(phone)
            } else
            {
                $('#check').hide();
                $('#send').fadeIn();
            }
        });

        $(document).on('keyup', '[name=key]', function (event) {
            var _v = $(this).val();
            if (_v.length == 4)
            {
                $(this).attr('readonly', true)
                $('#check').addClass('loading').submit();
            }
        });

    };

    function onLoginResponse(data, _callback, _data) 
    {
        if (!!data.error) {
            const url_href_redirect = data.error_type === 'user_not_find' ? '/' : '/init_user';
            $('[name=key]').closest('label').remove();
            $('#send').addClass('error').html('<p><span class="error" style="display:block!important">' + data.error + '</span></p><br /><br /><p><a href="' + url_href_redirect + '" class="button big">Заявка на займ</a></p>');
        } else if (!!data.time_error) {
            $('[name=key]').closest('label').remove();
            $('#send').addClass('error').html('<p><span class="error" style="display:block!important">' + data.time_error + '</span></p>');
        } else if (!!data.captcha_error) {
            initSmartCaptcha(true);
        } else if (data.response === 'user_bee') {
        } else {
            $("#check_title").html('Мы вам уже звоним. Внимательно прослушайте и введите код, продиктованный голосовым роботом');

            runInterval();
        }

        if (data.asp_additional_doc) {
            if ($('#smart-captcha-loan-container').length > 0) {
                $('#smart-captcha-loan-container').after('<div class="asp_additional_doc-div">' +
                    '<input id ="inp-asp-checkbox" type="checkbox" checked value="1">' +
                    '<label for="">Принимаю <a  href = "files/asp/Agreement_to_different_Frequency_Interactions.pdf" target="_blank">соглашение</a> на иную частоту взаимодействия.</label>' +
                    '</div>');
            } else {
                console.error('Element #smart-captcha-loan-container not found in the DOM.');
            }
        }

        _callback(data)

        if (!!data.developer_code) {
            $('#check [name=key]').val(data.developer_code);
        }
        console.log(data);
    }

    function send_sms_code(phone, repeat = 0, $form = false, action = '', _callback) {

        var phone_clear = phone.replace(/\D/g, '');
        var huid = $('body').data('hh');

        const page_action = ($('input[name="page_action"]').val() || window._loginState?.['page_action']);
        const smart_token = ($("[name='smart-token']").val() || window._loginState?.['smart-token']);

        if (!!$form) {
            $form.append('<input type="hidden" name="" value="" />');
            $form.append('<input type="hidden" name="repeat" value="' + repeat + '" />');
            $form.append('<input type="hidden" name="check_user" value="1" />');
            $form.append('<input type="hidden" name="flag" value="LOGIN" />');
            $form.append('<input type="hidden" name="huid" value="'+huid+'" />');
            var _data = $form.serializeArray();

            _data.push({ name: 'page_action', value: page_action });
            _data.push({ name: 'smart-token', value: smart_token});

            if(action == 'login_by_sms_action' && !window.same_tab_login) {
                localStorage.setItem('login_state', JSON.stringify(_data))
                setTimeout(() => {
                    _data.forEach(input => {
                        switch(input.name) {
                            case 'phone':
                                if(localStorage.getItem('partnerRedirectEnabled')) {
                                    invokeShopview('bonon-background-login:pk', window.login_partner_href)
                                    sendMetric('reachGoal', 'decline_monitoring_13')
                                    localStorage.removeItem('partnerRedirectEnabled')
                                    window.location.href = window.login_partner_href + window.href_append + '&p=' + input.value.replace(/\D/g, '');
                                }
                                break;
                        }
                    })
                }, 1000)
                return
            }
        } else {
            var _data = {
                phone: phone_clear,
                repeat: repeat,
                check_user: 1,
                flag: 'LOGIN',
                huid: huid,
                'smart-token': smart_token,
            };
        }

        $.ajax({
            type: "POST",
            url: "ajax/loginCodeByCall.php",
            data: _data,
            async: false,
            dataType: 'json',
            success: data => onLoginResponse(data, _callback, _data)
        });
    }

    $(document).on('click', '#inp-asp-checkbox', function () {
        if ($(this).is(':checked')) {
            $(this).val('1');
        } else {
            $(this).val('0');
        }
    });


    (function () {
        _init();

        app.init_validators();
        app.init_input_label();
        app.init_buttons();
        app.init_messengers();

        let login_state = JSON.parse(localStorage.getItem('login_state'))
        if(login_state) {
            window._loginState = {}
            login_state.forEach(field => {
                $(`input[name="${field.name}"]`).val(field.value)
                window._loginState[field.name] = field.value
            })
            localStorage.removeItem('login_state')
            localStorage.setItem('partnerRedirectEnabled', 1)
            $('#smart-captcha-loan-container').hide()
            app.login_by_sms_action('login_by_sms_action_2');
        } else {
            $('#send').removeClass('loading');
        }
    })();
}

(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const phoneNumber = urlParams.get('phone');
    if (phoneNumber) {
        $("#phoneInput").val(phoneNumber);
    }
})();

let timeForLoginInMessangers = 30;

function loginFormForMessangers(delay) {
    let block = document.getElementById('loginMessangers');
    if (delay === true && timeForLoginInMessangers > 0) {
        timeForLoginInMessangers--;
    } else {
        console.log();
        if (block.style.display === 'none') {
            block.style.display = 'block';
        }
    }
}


function loginMessangers(messanger) {
    let phone = $('input[name="phone"]').val().replace(/\D/g, '');
    let url;
    if (messanger === 'wa') {
        url = loginWhatsApp('https://www.boostra.ru/chats.php?api=whatsapp&method=sendCode&phone=' + phone);
    } else if (messanger === 'vi') {
        url = 'viber://pa?chatURI=' + viberBotName + '&context=sendCode&text=/start ' + phone;
    } else if (messanger === 'tg') {
        url = 'tg://resolve?domain=' + tlgBotName + '&start=/start ' + phone;
    } else if (messanger === 'sms') {
        url = '#';
        loginSms(phone);
    }
    if (url !== '#') {
        window.open(url);
    }
}

async function loginWhatsApp(url) {
    await fetch(url);
    return '#';
}

/**
 * Инициализация каптчи
 */
 function initSmartCaptcha (addAnimation = false) {
    if (window.smartCaptcha) {
        const container = document.getElementById('smart-captcha-loan-container');

        const widgetSmartCaptchaId = window.smartCaptcha.render(container, {
            sitekey: container.dataset.sitekey,
            hl: 'ru',
        });

        if(!JSON.parse(localStorage.getItem('login_state')) && !window._loginState) {
            if (addAnimation && typeof addAnimation === 'boolean') {
                $(container).show().addClass('animate-blink');
            } else {
                $(container).show().removeClass('animate-blink');
            }
        }
    }
}

function loginSms(phone, _callback = null) {
    let smart_token_element = $("#smart-captcha-loan-container [name='smart-token']");
    var xhr = new XMLHttpRequest();
    var body = 'phone=' + encodeURIComponent(phone) +
        '&flag=' + encodeURIComponent('LOGIN') +
        '&check_user=' + encodeURIComponent(1);
    if(window._loginState && window._loginState['smart-token']) {
        body += '&smart-token=' + window._loginState['smart-token']
    } else if (!!smart_token_element.length) {
        body += '&smart-token=' + encodeURIComponent(smart_token_element.val());
    }

    xhr.open("POST", 'ajax/send_sms.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send(body);

    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            const response = JSON.parse(xhr.responseText);
            if (!response.captcha) {
                $('#smart-captcha-loan-container').removeClass('animate-blink');
                $("#check_title").html('Введите код <b>№' + response.number_sms + '</b>, отправленный в смс');
                runInterval();
                if (_callback) {
                    _callback(response);
                }
            } else {
                if (response.captcha === 'init' || response.captcha === 're_init') {
                    initSmartCaptcha(true);
                }
            }
        }
    }
}

