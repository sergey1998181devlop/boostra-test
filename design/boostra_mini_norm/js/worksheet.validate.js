$(document).ready(function () {
    /********************************
     * VALIDATION RUSSIAN LOCALIZED
     *********************************/
    $.extend( $.validator.messages, {
        required: "Заполните поле.",
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
    $("input[name='phone'], input[name='landline_phone'], input[name='work_phone'], input[name='contact_person_phone'], input[name='contact_person2_phone'], input[name='contact_person3_phone']").inputmask(
        "+7 (999) 999-99-99",
        {
            oncomplete: function (e) {
                $(e.target).parent().next().find('input').focus();
            },
            clearIncomplete: true
        }
    );
    $("input[name='passportCode']").inputmask(
        "99 99 999999",
        {
            oncomplete: function (e) {
                $(e.target).parent().next().find('input').focus();
            },
            clearIncomplete: false
        }
    );
    
    $("input[name='social_fb']").inputmask({
        mask: "\\\http\\\s://www.f\\\acebook.co\\\m/[*{1,40}]",
        placeholder: ' ',
        clearIncomplete: false
    });
    $("input[name='social_inst']").inputmask({
        mask: "\\\http\\\s://www.in\\\st\\\agr\\\a\\\m.co\\\m/[*{1,40}]",
        placeholder: ' ',
        clearIncomplete: false
    });
    $("input[name='social_vk']").inputmask({
        mask: "\\\http\\\s://vk.co\\\m/[*{1,40}]",
        placeholder: ' ',
        clearIncomplete: false
    });
    $("input[name='social_ok']").inputmask({
        mask: "\\\http\\\s://www.ok.ru/profile/[*{1,40}]",
        placeholder: ' ',
        clearIncomplete: false
    });
    

    $("input[name='subdivisionCode']").inputmask(
        "999-999",
        {
            oncomplete: function (e) {
                $(e.target).parent().next().find('input').focus();
            },
            clearIncomplete: false
        }
    );
/*
    $("input[name='email']").inputmask({
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
    });*/
    
    $('.js-cirylic').keyup(function(){
        var _current = $(this).val();

        if ($(this).hasClass('js-cirylic-plus'))
            // Фильтрует поле ввода: оставляет только русские буквы, цифры и разрешенную пунктуацию
            var _replace = this.value.replace(/[^а-яё0-9\.\,\-\ №/.–«»\(\)\\]/ig,'');
        else
            // Фильтрует поле ввода: оставляет только русские буквы и пробелы
            var _replace = this.value.replace(/[^а-яё ]/ig,'');
        
        if (_replace != '')
            $(this).val(_replace[0].toUpperCase() + _replace.slice(1));
        else
            $(this).val(_replace);
        
        if (_current != _replace)
            $(this).closest('label').addClass('error').find('.error').text('Допускается ввод только русских букв');
        else
            $(this).closest('label').removeClass('error').find('.error').text('');
    });

    $('.js-uppercase').on('keyup input', function(){
        let _current = $(this).val();
        $(this).val(_current.toUpperCase());
    });

    $('.js-camelcase').on('keyup input', function(){
        let _current = $(this).val().toLowerCase();
        $(this).val(_current.charAt(0).toUpperCase() + _current.slice(1));
    });
    /*
    $('.js-code').change(function(){
        var codes = [846, 848, 900, 901, 902, 903, 904, 905, 906, 908, 909, 910, 911, 912, 913, 914, 915, 916, 917, 918, 919, 920, 921, 922, 923, 924, 925, 926, 927, 928, 929, 930, 931, 932, 933, 934, 936, 937, 938, 939, 941, 950, 951, 952, 953, 954, 955, 956, 958, 960, 961, 962, 963, 964, 965, 966, 967, 968, 969, 970, 971, 977, 978, 980, 981, 982, 983, 984, 985, 986, 987, 988, 989, 991, 992, 993, 994, 995, 996, 997, 999];
        
        if(codes.indexOf(parseInt($(this).val().substring(4, 7))) == -1) 
        {
            $(this).closest('label').addClass('error').find('.error').text('Введите корректный телефонный код');
        }
        else
        {
            $(this).closest('label').removeClass('error').find('.error').text('');
        }
    });
    */
    $('.js-digits').keyup(function(){
        var _current = $(this).val();
        var _replace = this.value.replace(/[^0-9]/ig,'');
        if (_replace != '')
            $(this).val(_replace[0].toUpperCase() + _replace.slice(1));
        else
            $(this).val(_replace);
        
        if (_current != _replace)
            $(this).closest('label').addClass('error').find('.error').text('Введите число');
        else
            $(this).closest('label').removeClass('error').find('.error').text('');
    })

    $('.js-chars').keyup(function(){
        var _current = $(this).val();
        if (_current.trim() == '') {
            $(this).val('');
            $(this).closest('label').addClass('error').find('.error').text('введите данные');
        } else {
            $(this).closest('label').removeClass('error').find('.error').text('');
        }

    })



    
    var dateFields = 'input[name="passportDate"], input[name="birthday"]';

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

    function formatDate(dateString) {
        if (dateString.includes('.')) {
            var parts = dateString.split('.');
            return `${parts[2]}-${parts[1]}-${parts[0]}`;
        }
        return dateString;
    }

    /********************************************
     * GET CURRENT AGE
     ********************************************/
    //возраст считает по дате рождения
    //dateString - дата рождения в формате '10.05.1990'
    function getAge(dateString) {
        var formattedDate = formatDate(dateString);
        var year = parseInt(formattedDate.substring(0, 4));
        var month = parseInt(formattedDate.substring(5, 7)) - 1;
        var day = parseInt(formattedDate.substring(8, 10));

        var today = new Date();
        var birthDate = new Date(year, month, day);
        var yDiff = today.getFullYear() - birthDate.getFullYear();
        var mDiff = today.getMonth() - birthDate.getMonth();
        var dDiff = today.getDate() - birthDate.getDate();

        if (mDiff < 0 || (mDiff === 0 && dDiff < 0)) {
            yDiff--;
        }

        return yDiff;
    }

    $.validator.addMethod("subdivisionCode", function(val, elem, c) {
        return /^\d{3}-\d{3}$/.test(val);
    }, "Заполните корректно поле");
    
    $.validator.addMethod("user_email", function(val, elem, c) {
        if (val === '') return true;

        const emailRegex = /^[A-Za-z0-9](?:[A-Za-z0-9_\-\.]*[A-Za-z0-9])?@([A-Za-z0-9\-]+\.)+[A-Za-z]{2,4}$/;

        // Запрет: двойные точки, точка перед @, любые символы кроме букв/цифр в начале/конце
        if (!emailRegex.test(val)) return false;
        if (/[^A-Za-z0-9]@/.test(val)) return false;  // символ перед @ не буква/цифра
        if (/\.{2,}/.test(val)) return false;         // двойные точки

        return true;
    }, "Заполните корректно поле");

    $.validator.addMethod("passportCode", function(val, elem, c) {
        return /^\d{2} \d{2} \d{6}$/.test(val);
    }, "Заполните корректно поле");

    $.validator.addMethod("Birth", function() {
        var choiseDate = $('input[name="birthday"]').val();
        console.log(choiseDate);
        var age = getAge(choiseDate);
        if(age >= 18 && age <= 71){
            $('input[name="birthday"]').removeClass('invalid-age');
            localStorage.setItem('birthDate', choiseDate);
            return true;
        }
        $('input[name="birthday"]').addClass('invalid-age')
    }, "Займы на нашем сайте выдаются от 18 до 71 лет");

    $.validator.addMethod("Code", function(val, elem) {
        var codes = [846, 848, 900, 901, 902, 903, 904, 905, 906, 908, 909, 910, 911, 912, 913, 914, 915, 916, 917, 918, 919, 920, 921, 922, 923, 924, 925, 926, 927, 928, 929, 930, 931, 932, 933, 934, 936, 937, 938, 939, 941, 950, 951, 952, 953, 954, 955, 956, 958, 960, 961, 962, 963, 964, 965, 966, 967, 968, 969, 970, 971, 977, 978, 980, 981, 982, 983, 984, 985, 986, 987, 988, 989, 991, 992, 993, 994, 995, 996, 997, 999];
        //if(codes.indexOf(parseInt(val.substring(4, 7))) != -1)
            return true;
    }, "Введите правильный код оператора");

    $.validator.addMethod("only_mobile", function(val, elem) {
        if (is_developer)
            return 1;
        else
            return val.substring(4, 5) == 9;
    }, "Введите правильный код оператора");

    /********************************
     * ADD NEW METHODS
     *********************************/
    $.validator.addMethod("russian", function(value, element) {
        return this.optional(element) || /^[а-яА-ЯёЁ ]+$/.test(value);
    }, "Допускается ввод только русских букв");
    // для двойных имен и фамилий
    $.validator.addMethod("russianFullName", function(value, element) {
        return this.optional(element) || /^[а-яА-ЯёЁ]+(-[а-яА-ЯёЁ]+)*$/.test(value);
    }, "Допускается ввод только русских букв и одного дефиса между словами");

    $.validator.addMethod("PassportDate", function(value, element) {
        var birthDateString = localStorage.getItem('birthDate');
        if (birthDateString) {
            var passportDateString = value;

            var birthDateParts = birthDateString.split('.');
            var passportDateParts = passportDateString.split('.');

            if (birthDateParts.length !== 3 || passportDateParts.length !== 3) {
                return true; // Don't validate if date format is incorrect
            }

            var birthDate = new Date(birthDateParts[2], birthDateParts[1] - 1, birthDateParts[0]);
            var passportDate = new Date(passportDateParts[2], passportDateParts[1] - 1, passportDateParts[0]);

            var today = new Date();
            if (passportDate > today) {
                return false;
            }

            if (isNaN(birthDate.getTime()) || isNaN(passportDate.getTime())) {
                return true; // Don't validate if date is invalid
            }

            var age = new Date(passportDate - birthDate).getFullYear() - 1970;

            if (age < 14) {
                return false;
            }

            var fullAge = new Date(new Date() - birthDate).getFullYear() - 1970;

            if (fullAge >= 45 && age < 45) {
                return false;
            }

            if (fullAge > 20 && age < 20) {
                return false;
            }

        }

        return true;
    }, "Некорректная дата выдачи паспорта");

});