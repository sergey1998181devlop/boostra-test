$(document).ready(function () {
    /********************************
     * VALIDATION RUSSIAN LOCALIZED
     *********************************/
    $.extend( $.validator.messages, {
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
        maxlength: $.validator.format( "Пожалуйста, введите не больше {0} символов." ),
        minlength: $.validator.format( "Пожалуйста, введите не меньше {0} символов." ),
        rangelength: $.validator.format( "Пожалуйста, введите значение длиной от {0} до {1} символов." ),
        range: $.validator.format( "Пожалуйста, введите число от {0} до {1}." ),
        max: $.validator.format( "Пожалуйста, введите число, меньшее или равное {0}." ),
        min: $.validator.format( "Пожалуйста, введите число, большее или равное {0}." )
    } );

    /********************************
     * INPUT MASK
     *********************************/
    $("input[name='login[phone]']").inputmask("+7 (999) 999-99-99");

	$("input:not(:checkbox), textarea, select", "#login").each(function(i, v) {
		var placeholder = $(v).attr('placeholder');
		$(v).attr('placeholder', '');
		$(v).parent().append('<span class="floating-label">' + placeholder + '</span>');
	});

    $.validator.addMethod("Code", function() {
        var codes = [900, 901, 902, 903, 904, 905, 906, 908, 909, 910, 911, 912, 913, 914, 915, 916, 917, 918, 919, 920, 921, 922, 923, 924, 925, 926, 927, 928, 929, 930, 931, 932, 933, 934, 936, 937, 938, 939, 941, 950, 951, 952, 953, 954, 955, 956, 958, 960, 961, 962, 963, 964, 965, 966, 967, 968, 969, 970, 971, 977, 978, 980, 981, 982, 983, 984, 985, 986, 987, 988, 989, 991, 992, 993, 994, 995, 996, 997, 999];
        //if(codes.indexOf(parseInt($('input[name="login[phone]"]').val().substring(4, 7))) != -1)
            return true;
    }, "Введите правильный код оператора");

    $.validator.addMethod("Key", function() {
        var val = $('input[name="login[key]"]').val();
        var key = parseInt(val);
        if(!isNaN(parseFloat(val)) && isFinite(val) && key >= 1000 && key <= 999999) return true;
    }, "Введите код в правильном формате");

    var validator = $("#login #send").validate({
            errorElement: "span",
            rules: {
                "login[phone]": {
                    Code: true
                }
            }
        }),
        validator2 = $("#login #check").validate({
            errorElement: "span",
            rules: {
                "login[key]": {
                    Key: true
                }
            }
        });

    $('#send').submit(function(event) {
        if ($(this).valid()) {
            $('#send').hide();
            $('#check').fadeIn();
        }
        event.preventDefault();
    });
});