$(document).ready(function () {
    /********************************
     * VALIDATION RUSSIAN LOCALIZED
     *********************************/
    $.extend($.validator.messages, {
        required: "Данное поле необходимо заполнить.",
        remote: "Пожалуйста, введите правильное значение.",
        email: "Пожалуйста, введите корректный адрес электронной почты.",
        url: "Пожалуйста, введите корректный URL.",
        date: "Пожалуйста, введите корректную дату.",
        dateISO: "Пожалуйста, введите корректную дату в формате ISO.",
        number: "Пожалуйста, введите число.",
        digits: "Пожалуйста, вводите только цифры.",
        creditcard: "Пожалуйста, введите правильный номер кредитной карты.",
        equalTo: "Пожалуйста, введите такое же значение ещё раз.",
        extension: "Пожалуйста, выберите файл с правильным расширением.",
        maxlength: $.validator.format("Пожалуйста, введите не больше {0} символов."),
        minlength: $.validator.format("Пожалуйста, введите не меньше {0} символов."),
        rangelength: $.validator.format("Пожалуйста, введите значение длиной от {0} до {1} символов."),
        range: $.validator.format("Пожалуйста, введите число от {0} до {1}."),
        max: $.validator.format("Пожалуйста, введите число, меньшее или равное {0}."),
        min: $.validator.format("Пожалуйста, введите число, большее или равное {0}.")
    });

    /********************************
     * INPUT MASK
     *********************************/
    $("input[name='phone']").inputmask("+7 (999) 999-99-99");

    $("input:not(:checkbox), textarea, select", "#login").each(function (i, v) {
        var placeholder = $(v).attr('placeholder');
        if (placeholder != null)
        {
            $(v).attr('placeholder', '');
            $(v).parent().append('<span class="floating-label">' + placeholder + '</span>');
        }
    });

    $.validator.addMethod("Code", function () {
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
        //if (codes.indexOf(parseInt($('input[name="phone"]').val().substring(4, 7))) != -1)
            return true;
    }, "Введите правильный код оператора");

    $.validator.addMethod("Key", function () {
        var val = $('input[name="key"]').val();
        var key = parseInt(val);
        if (!isNaN(parseFloat(val)) && isFinite(val) && key >= 1000 && key <= 999999)
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

    $('#send').submit(function (event) {
        event.preventDefault();
        if (1 || $(this).valid()) {        
            var phone = $('input[name="phone"]').val();
            send_sms_code(phone, 0, $(this), function(){
                $('#send').hide();
                $('input[name="real_phone"]').val(phone);
                $('#check').fadeIn();
                
            });
        }
    });
    $('#check button').click(function (event) {
        event.preventDefault();
        if (1 || $('#check').valid()) {
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
            send_sms_code(phone, 1, false, function(){});
        } else
        {
            $('#check').hide();
            $('#send').fadeIn();
        }
    });


});
var _whatsapp = 1;

function send_sms_code(phone, repeat = 0, $form = false, _callback) {

    var phone_clear = phone.replace(/\D/g, '');
    var timeinterval;
    var noactive_period = 600;
    var $repeat_sms = $('.repeat_sms');
    
    if (!!$form)
    {
        $form.append('<input type="hidden" name="" value="" />');
        $form.append('<input type="hidden" name="repeat" value="'+repeat+'" />');
        $form.append('<input type="hidden" name="check_user" value="1" />');
        $form.append('<input type="hidden" name="flag" value="LOGIN" />');
        $form.append('<input type="hidden" name="whatsapp" value="'+_whatsapp+'" />');
        var _data = $form.serialize();
    }
    else
    {
        var _data = {
            phone: phone_clear,
            repeat: repeat,
            check_user: 1,
            flag: 'LOGIN',
            whatsapp: _whatsapp,
        }; 
    }
    
    $.ajax({
        type: "POST",
        url: "ajax/loginCodeByCall.php",
        data: _data,
        async: false,
        dataType: 'json',
        success: function (data) {

            if (!!data.error) {
                if(data.error == 'recaptcha_error') {
                    $('#send').addClass('error').find('span.error').remove()
                    $('#recaptcha_register').after('<p><span class="error" style="display:block!important">' + 'Вы не прошли проверку "Я не робот"' + '</span></p>');
                } else if(data.soap_fault) {
                    $('#send').addClass('error').find('span.error').remove()
                    $('#recaptcha_register').after('<p><span class="error" style="display:block!important">' + data.error + '</span></p>');
                }
                
                return false;
            }

            {
                if (!!_whatsapp)
                {
                    _whatsapp = 0;
                    $("#check_title").html('<span>\n\
                                Мы отправили код на ваш\n\
                                <img style="width: 20px; margin-left: 10px;" src="/design/boostra_mini_norm/edu/images/whatsapp.svg"> WhatsApp<br />\n\
                                <p style="color: #C6C1C1; margin-bottom: 10px;">Не пришел код на WhatsApp?</p>\n\
                                <a href="#" style="text-decoration:underline;margin-left:0;padding-top:0" class="new_sms fast_send ">\n\
                                    Получить в sms-сообщении\n\
                                </a>\n\
                            </span>');
                } 
                else
                {
                    if (!!data.error)
                    {
                        $('[name=key]').closest('label').remove();
                        $('#check').addClass('error').html('<p><span class="error" style="display:block!important">' + data.error + '</span></p><br /><br /><p><a href="neworder" class="button big">Заявка на займ</a></p>');
                    } 
                    else if (!!data.time_error)
                    {
                        $('[name=key]').closest('label').remove();
                        $('#check').addClass('error').html('<p><span class="error" style="display:block!important">' + data.time_error + '</span></p>');
                    } 
                    else
                    {
                        $("#check_title").html('Мы вам уже звоним. Внимательно прослушайте и введите код, продиктованный голосовым роботом');
                        timeinterval = setInterval(function () {
                            noactive_period--;
                            //console.info(noactive_period)
                            if (noactive_period > 0)
                            {
                                $repeat_sms.html('<span>Отправить код еще раз ' + noactive_period + 'сек</span>');
                            } else
                            {
                                $repeat_sms.html('<a href="#" class="new_sms">Отправить код еще раз</a>');
                                clearInterval(timeinterval);
                            }
                        }, 1000);
    
                    }
                }
                _callback()
            }
            
            if (!!data.developer_code)
            {
                $('#check [name=key]').val(data.developer_code);
            }
            console.log(data);
        }
    });
}

function check_sms_code(phone, sms) {
    var phone_clear = phone.replace(/\D/g, '');
    var sms_clear = sms.replace(/\D/g, '');

    //console.log(sms_clear, phone_clear)
    $.ajax({
        type: "POST",
        url: "ajax/check_sms.php",
        data: {phone: phone_clear, sms: sms_clear},
        dataType: 'json',
        success: function (data) {
            return data;
        }
    });
}