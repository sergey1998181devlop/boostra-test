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

    $('.scrollbar-inner').scrollbar({
		"scrollx": $('.scroll-element.scroll-x'),
		"scrolly": $('.scroll-element.scroll-y')
    });

	$("#private form").submit(function (event) {
		event.preventDefault();
		if ($("#private form input[type='checkbox']").attr("checked") != 'checked') {
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


    $.validator.addMethod("Key", function() {
        var val = $('input[name="sign[code]"]').val();
        var key = parseInt(val);
        if(!isNaN(parseFloat(val)) && isFinite(val) && key >= 1000 && key <= 999999) return true;
    }, "Введите код в правильном формате");


	$("#check form").submit(function (event) {
		event.preventDefault();
        validator = $("#check form").validate({
            errorElement: "span",
            rules: {
                "sign[code]": {
                    Key: true
                }
            }
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
});