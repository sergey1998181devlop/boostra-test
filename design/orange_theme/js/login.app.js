/* global viberBotName, tlgBotName */

function LoginApp()
{
    var app = this;

    var _init = function () {

    };

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
                        $('#check').addClass('error').html('<p><span class="error" style="display:block!important">' + data.error + '</span></p><br /><br /><p><a href="neworder" class="button big">Заявка на займ</a></p>');

                    }
                }
            })
        });

    };

    app.init_validators = function () {

        $("input[name='phone']").inputmask("+7 (999) 999-99-99");

        $.extend($.validator.messages, {
            required: "Данное поле необходимо заполнить.",
        });

        $.validator.addMethod("Code", function () {
            
            var codes = [900, 901, 902, 903, 904, 905, 906, 908, 909, 910, 911, 912, 913, 914, 915, 916, 917, 918, 919, 920, 921, 922, 923, 924, 925, 926, 927, 928, 929, 930, 931, 932, 933, 934, 936, 937, 938, 939, 941, 950, 951, 952, 953, 954, 955, 956, 958, 960, 961, 962, 963, 964, 965, 966, 967, 968, 969, 970, 971, 977, 978, 980, 981, 982, 983, 984, 985, 986, 987, 988, 989, 991, 992, 993, 994, 995, 996, 997, 999];
            
            if ($('input[name="phone"]').val() == '+7 (940) 710-67-27')
                return true;
            //if (codes.indexOf(parseInt($('input[name="phone"]').val().substring(4, 7))) != -1)
                return true;
        }, "Введите правильный код оператора");

        $.validator.addMethod("Key", function () {
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

    app.init_buttons = function () {

        $('#send').submit(function (event) {
            event.preventDefault();
            if (is_developer || $(this).valid()) {
                var phone = $('input[name="phone"]').val();
                send_sms_code(phone, 0, $(this), function () {
                    $('#send').hide();
                    $('input[name="real_phone"]').val(phone);
                    $('#check').fadeIn();

                });
            }
        });

        $('#check button').click(function (event) {
            event.preventDefault();
            if (is_developer || $('#check').valid()) {
                var phone = $('input[name="phone"]').val();
                var sms = $('input[name="key"]').val();
                $('#check').submit();
            }
        });

        $(document).on('click', 'a.new_sms', function (event) {
            event.preventDefault();
            if ($(this).hasClass('fast_send'))
            {
                var phone = $('input[name="phone"]').val();
                send_sms_code(phone, 1, false, function () {});
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

    function send_sms_code(phone, repeat = 0, $form = false, _callback) {

        var phone_clear = phone.replace(/\D/g, '');
        var timeinterval;
        var noactive_period = 30;
        var $repeat_sms = $('.repeat_sms');

        if (!!$form)
        {
            $form.append('<input type="hidden" name="" value="" />');
            $form.append('<input type="hidden" name="repeat" value="' + repeat + '" />');
            $form.append('<input type="hidden" name="check_user" value="1" />');
            $form.append('<input type="hidden" name="flag" value="LOGIN" />');
            var _data = $form.serialize();
        } else
        {
            var _data = {
                phone: phone_clear,
                repeat: repeat,
                check_user: 1,
                flag: 'LOGIN',
            };
        }

        $.ajax({
            type: "POST",
            url: "ajax/loginCodeByCall.php",
            data: _data,
            async: false,
            dataType: 'json',
            success: function (data) {
                setInterval(loginFormForMessangers, 1000);
                if (!!data.error)
                {
                    $('[name=key]').closest('label').remove();
                    $('#check').addClass('error').html('<p><span class="error" style="display:block!important">' + data.error + '</span></p><br /><br /><p><a href="neworder" class="button big">Заявка на займ</a></p>');
                } else if (!!data.time_error)
                {
                    $('[name=key]').closest('label').remove();
                    $('#check').addClass('error').html('<p><span class="error" style="display:block!important">' + data.time_error + '</span></p>');
                }
                else if (!!data.captcha_error)
                {
                    $('#captcha_error').remove();
                    $('#recaptcha_register').css('border', 'apx solid #ff1111');
                    $('#recaptcha_register').append('<div id="captcha_error" style="color:#f11">Вы не прошли проверку</div>');
                    return false;
                } 
                else
                {
                    $("#check_title").html('Мы вам уже звоним. Внимательно прослушайте и введите код, продиктованный голосовым роботом');
                    timeinterval = setInterval(function () {
                        noactive_period--;
                        //console.info(noactive_period)
                        if (noactive_period > 0)
                        {
                            $repeat_sms.html('<span>Отправить код еще раз через ' + noactive_period + ' сек</span>');
                        } else
                        {
                            $repeat_sms.html('');
                            clearInterval(timeinterval);
                        }
                    }, 1000);

                }

                _callback()

                if (!!data.developer_code)
                {
                    $('#check [name=key]').val(data.developer_code);
                }
                console.log(data);
            }
        });
    }

    ;
    (function () {
        _init();
        app.init_validators();
        app.init_input_label();
        app.init_buttons();
        app.init_messengers();
    })();
}

let timeForLoginInMessangers = 30;

function loginFormForMessangers() {
    let block = document.getElementById('loginMessangers');
    if (timeForLoginInMessangers > 0) {
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

function loginSms(phone) {
    var xhr = new XMLHttpRequest();
    var body = 'phone=' + encodeURIComponent(phone) +
            '&flag=' + encodeURIComponent('LOGIN') +
            '&check_user=' + encodeURIComponent(1);
    xhr.open("POST", 'ajax/send_sms.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send(body);
    $('#codeInSms').html('Мы Вам отправили код по SMS');
}

$(function () {
    new LoginApp();
});
