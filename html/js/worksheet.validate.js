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
    /********************************************
     * GET CURRENT AGE
     ********************************************/
    //возраст считает по дате рождения
    //dateString - дата рождения в формате '10.05.1990'
    function getAge(dateString) {
        var year = parseInt(dateString.substring(0,4));
        var month = parseInt(dateString.substring(5,7));
        var day = parseInt(dateString.substring(8,10));


        var today = new Date();
        var birthDate = new Date(year, month , day);
        var yDiff = today.getFullYear() - birthDate.getFullYear();
        var mDiff = today.getMonth() - birthDate.getMonth();
        var dDiff = today.getDate() - birthDate.getDate();

        if (mDiff <= 0) {
            if (dDiff <= 0) {
                yDiff--;
            }
        }

        return yDiff;
    }
    /********************************
     * INPUT MASK
     *********************************/
    $("input[name='sheet[phone]'], input[name='sheet[adding[1][phone]]'], input[name='sheet[adding[2][phone]]']").inputmask(
        "+7 (999) 999-99-99",
        {
            oncomplete: function (e) {
                $(e.target).parent().next().find('input').focus();
            },
            clearIncomplete: true
        }
    );
    $("input[name='sheet[passportCode]']").inputmask(
        "99 99 999999",
        {
            oncomplete: function (e) {
                $(e.target).parent().next().find('input').focus();
            },
            clearIncomplete: true
        }
    );
    $("input[name='sheet[subdivisionCode]']").inputmask(
        "999-999",
        {
            oncomplete: function (e) {
                $(e.target).parent().next().find('input').focus();
            },
            clearIncomplete: true
        }
    );

    $("input[name='sheet[email]']").inputmask({
        mask: "*{1,20}[.*{1,20}][.*{1,20}][.*{1,20}]@*{1,20}[.*{2,6}][.*{1,2}]",
        greedy: false,
        definitions: {
            '*': {
                validator: "[0-9A-Za-z!#$%&'*+/=?^_`{|}~\-]",
                cardinality: 1,
                casing: "lower",
                clearIncomplete: true
            }
        }
    });
    var dateFields = 'input[name="sheet[passportDate]"], input[name="sheet[birthday]"]';

    $(dateFields).inputmask(
        "dd.mm.yyyy",
        {
            oncomplete: function (e) {
                $(e.target).parent().next().find('input').focus();
            },
            "placeholder": "дд.мм.гггг",
            clearIncomplete: true,
            "onincomplete": function () {
                $(this).removeClass('valid').addClass('error');
            }
        });

    function getAge(dateString) {
        var year = parseInt(dateString.substring(0,4));
        var month = parseInt(dateString.substring(5,7));
        var day = parseInt(dateString.substring(8,10));


        var today = new Date();
        var birthDate = new Date(year, month , day);
        var yDiff = today.getFullYear() - birthDate.getFullYear();
        var mDiff = today.getMonth() - birthDate.getMonth();
        var dDiff = today.getDate() - birthDate.getDate();

        if (mDiff <= 0) {
            if (dDiff <= 0) {
                yDiff--;
            }
        }

        return yDiff;
    }


    $.validator.addMethod("Birth", function() {
        var choiseDate = $('input[name="sheet[birthday]"]').val();
        var age = getAge(choiseDate);
        if(age >= 21 && age <= 71) return true;
    }, "Займы на нашем сайте выдаются от 21 до 71 лет");

    /*
    $.validator.addMethod("Code", function() {
        var codes = [900, 901, 902, 903, 904, 905, 906, 908, 909, 910, 911, 912, 913, 914, 915, 916, 917, 918, 919, 920, 921, 922, 923, 924, 925, 926, 927, 928, 929, 930, 931, 932, 933, 934, 936, 937, 938, 939, 941, 950, 951, 952, 953, 954, 955, 956, 958, 960, 961, 962, 963, 964, 965, 966, 967, 968, 969, 970, 971, 977, 978, 980, 981, 982, 983, 984, 985, 986, 987, 988, 989, 991, 992, 993, 994, 995, 996, 997, 999];
        if(codes.indexOf(parseInt($('input[name="sheet[phone]"]').val().substring(4, 7))) != -1) return true;
    }, "Введите правильный код оператора");
    */
    /********************************
     * ADD NEW METHODS
     *********************************/
    $.validator.addMethod("russian", function(value, element) {
        return this.optional(element) || /^[а-яА-ЯёЁ ]+$/.test(value);
    }, "Допускается ввод только русских букв");
});