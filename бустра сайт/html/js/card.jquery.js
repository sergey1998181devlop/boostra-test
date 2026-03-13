$(document).ready(function(){
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


    $("input[name='card[number]']").inputmask({
        "mask": "*{16,19}",
        definitions: {
            '*': {
                validator: "[0-9]",
                cardinality: 1
            }
        },
        clearIncomplete: true
    });

    $("input[name='card[number]']").on('keyup keydown', function () {
        var firstVal_1 = $(this).val().substr(0,1),
            firstVal_2 = $(this).val().substr(0,2);

        if (firstVal_1 == '4') {
            $('.logos .card').removeClass('active');
            $('.logos .visa').addClass('active');
        } else if ($(this).val().length > 16) {
            $('.logos .card').removeClass('active');
            $('.logos .maestro').addClass('active');
        } else if (firstVal_2 == '51' || firstVal_2 == '52' || firstVal_2 == '53' || firstVal_2 == '54' || firstVal_2 == '55') {
            $('.logos .card').removeClass('active');
            $('.logos .master').addClass('active');
        } else {
            $('.logos .card').addClass('active');
        }
    });

    /********************************
     * MASK: CARD NAME
     *********************************/
    $("input[name='card[holder]']").inputmask({
        "mask": "*{1,30} *{1,30}",
        clearIncomplete: true,
        definitions: {
            '*': {
                validator: "[A-Za-z]",
                cardinality: 1,
                casing: "upper"
            }
        }
    });

    /********************************
     * MASK: CARD TERM
     *********************************/
    $("input[name='card[month]']").inputmask(
        "99",
        {
            oncomplete: function (e) {
                $(e.target).next('input').focus();
            },
            "placeholder": "мм",
            clearIncomplete: true,
            "onincomplete": function () {
                $(this).removeClass('valid').addClass('error');
            }
        }
    );

    $("input[name='card[year]']").inputmask(
        "99",
        {
            oncomplete: function (e) {
                $(e.target).parent().parent().next().find('input').focus();
            },
            "placeholder": "гг",
            clearIncomplete: true,
            "onincomplete": function () {
                $(this).removeClass('valid').addClass('error');
            }
        }
    );

    /********************************
     * MASK: CARD CCV CODE
     *********************************/
    $("input[name='card[cvc]']").inputmask({
        "mask": "999",
        clearIncomplete: true,
        "placeholder": "cvc"
    });

    $("input[name='confirm[sum]']").inputmask(
        "Regex", { regex: "^[0-9]?$|^10$" }
    );

	$("#private form").submit(function (event) {
		event.preventDefault();
        validator = $("#private form").validate({
            errorElement: "span",
            rules: {
                "card[month]": {
                    Month: true
                }
            }
        });

        if (!$("#private form").valid()) {
            return;
        }

		$("#check .time").countdown(60);

		$.magnificPopup.open({
			items: {
				src: '#check' 
			},
			type: 'inline'
		});
	});


    $.validator.addMethod("Month", function(value, element) {
        if(value > 0 && value < 13) return true;
    }, "Укажите правильный номер месяца");

	$("#check form").submit(function (event) {
		event.preventDefault();
        validator = $("#check form").validate({
            errorElement: "span"
        });
        if ($("#check form").valid()) {
            $.magnificPopup.open({
                items: {
                    src: '#checked' 
                },
                type: 'inline'
            });
        }
	});

    $("#check .buble-open").hover(
        function () {
            $("#check .buble").fadeIn();
        },
        function (){
            $("#check .buble").hide();
        }
    );
});