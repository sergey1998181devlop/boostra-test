$(document).ready(function() {

    $('#accept_link').click(function(e){
        e.preventDefault();
    	$.magnificPopup.open({
    		items: {
    			src: '#accept'
    		},
    		type: 'inline',
            showCloseBtn: true
    	});
    })

	var steps = $("#steps").find("fieldset"),
	current = 0,
	validator = $("#worksheet form").validate({
		errorElement: "span",
		rules: {
			"name='name'": {
				russian: true
			},
			"name='birthday'": {
				Birth: true
			},
			"name='phone'": {
				Code: true
			}
		}
	}),
	fields = $("#steps").find("input").length,
	heading = {
		0: "Она короткая. Всего 3 минуты.",
		1: "Финишная прямая.",
		//2: "Мы уже проверяем анкету, вам осталось заполнить лишь адрес."
	},
	progress = {
		0: "личные данные",
		1: "условия",
		//2: "адрес"
	};

	$('#worksheet input').focus(function () {
		progressSlider();
	});

	$('#worksheet .checkbox input[type=checkbox]').change(function () {
		progressSlider();
	});

	render(current);
	checkForm($("#worksheet .checkbox input#equal").checked);

	$("input:not(:checkbox), textarea, select", "fieldset").each(function(i, v) {
		var placeholder = $(v).attr('placeholder');
		if(placeholder != null)
		{
			$(v).attr('placeholder', '');
			$(v).parent().append('<span class="floating-label">' + placeholder + '</span>');
		}
	});

	$("input.adding", "fieldset").each(function(i, v) {
		adding(v);
	});

	$("#worksheet .checkbox input#equal").change(function() {
		checkForm(this.checked);
	});

	$('#worksheet a.prev').click(function(e) {
		e.preventDefault();
		render(--current);
	});

	$('input#citizen, input#conditions').change(function(){
		if($('input#citizen').prop('checked') && $('input#conditions').prop('checked'))
			$('button#doit').prop('disabled', false);
		else
			$('button#doit').prop('disabled', true);
	});

	

	$("#worksheet .register input").keyup(function() {
		if ($("#worksheet .checkbox input#equal").attr("checked")) {
			prefild();
		}
	});

	$("input.adding").keyup(function() {
		adding(this);
	});

	$("#steps .next .button").click(function() {
		var valid = true;
		$("input, select, checkbox, textarea", "fieldset.current").each(function(i, v){
			valid = validator.element(v) && valid;
			//console.log(v);
			//console.log(valid);
		});

		if (!valid || ($(this).prop('id') == 'doit')) {
			return;
		}

		if (current == 0) {
            
            if ($('#accept_check').length > 0)
            {
                if (!$('#accept_check').is(':checked'))
                {
                    $('#accept_check').closest('label').addClass('error');
                    return;
                }
            }
            
			//$("#check .time").countdown(60);
			$.magnificPopup.open({
				items: {
					src: '#check'
				},
				type: 'inline'
			});
			var phone = $('input[name="phone"]').val();

			send_sms_code(phone, false);
			return;
		}

		render(++current);

		if (current == $("#steps fieldset").length - 1) {
			$('body').addClass('wait');
		}
	});

	$(document).on('click', 'a.new_sms', function (event) {
		event.preventDefault();

		var phone = $('input[name="phone"]').val();
		send_sms_code(phone, false);
		$('#check input[name="sign[code]"]').val('');
		return;
	})

	$('#check form').submit(function (event) {
		event.preventDefault();
		
		validator = $("#check form").validate({
			errorElement: "span"
		});
		if ($("#check form").valid()) {
			var phone = $('input[name="phone"]').val();
			var sms = $('input[type="tel"][name="sign[code]"]').val();
			check_sms_code(phone, sms);			
		}
	});

	$('#neworder').submit(function (event) {
		event.preventDefault();
		$('#doit').prop('disabled', true).html('Идет отправка');
		$.ajax({
		   type: "POST",
		   url: $(this).attr('action'),
           data: $(this).serialize(), // serializes the form's elements.
           success: function(data)
           {
           	   console.log(data);
           	   $('.pixel_metric').html($(data).find('.pixel_metric').html());
               render(++current);
           }
        });
	});

	function checkForm (flag) {
		if (flag) {
			$("#worksheet .living").animate({height: 0}, 350, "linear", function() {
				$("#worksheet .living").hide();
				$("#worksheet .living input").addClass("valid");
				progressSlider();
				$("#worksheet .living").css("height", "auto");
			});
			prefild();
			return;
		}
		$("#worksheet .living").fadeIn();
	}

	function prefild() {
		$("input:not(:checkbox)", "#worksheet .register").each(function(i, v) {
			$("input[rel='" + $(v).attr("rel") + "']", "#worksheet .living").val($(v).val());
			var elem = $("#worksheet .living input[rel='" + $(v).attr("rel") + "']");
			if (elem.hasClass('sup') && elem.val) {
				elem.removeClass('sup');
			}
		});
	}

	function render(index) {
		progressSlider();
		$("#steps fieldset").removeClass("current");
		$("#steps fieldset:eq(" + index + ")").addClass("current");
	}

	function progressSlider() {
		$('#worksheet h5').text(heading[current]);
		$('.progress .irs-single').text(progress[current]);
		var valid = $("#worksheet").find("input.valid").length + $('#worksheet .checkbox input[type=checkbox]:checked').length;
		var left = (valid * 100) / fields;
		console.log(valid + ' ' + fields);
		if (left == 100) {
			left = 98;
		}
		$('.progress .irs-bar').css('width', left + '%');
		$('.progress .irs-slider').css('left', left + '%');
		$('.progress .irs-single').css('left', 12 + (left / 100) * $('.progress  .irs').width() - $('.progress .irs-single').width() / 2 + 'px');
	}

	function adding(elem) {
		if (elem.value) {
			$(elem).removeClass('sup');
			return;
		}
		$(elem).addClass('sup');
	}

	function send_sms_code(phone, repeat=false){

		var phone_clear = phone.replace(/\D/g, '');
        var timeinterval;
        var noactive_period = 600;
		var $repeat_sms = $('.repeat_sms');
        
		//console.log(phone_clear);
		//console.log(repeat);
        $.ajax({
			type: "POST",
			url: "ajax/send_sms.php",
			data: {phone: phone_clear, repeat: repeat},
			dataType: 'json',
			success: function(data){
				console.log(data);
                
                timeinterval = setInterval(function(){
                    noactive_period--;
                    if (noactive_period > 0)
                    {
                        $repeat_sms.html('<span>Отправить код еще раз '+noactive_period+'сек</span>');
                    }
                    else
                    {
                        $repeat_sms.html('<a href="#" class="new_sms">Отправить код еще раз</a>');
                        clearInterval(timeinterval);
                    }
                }, 1000);
			}
		});
	}

	function check_sms_code(phone, sms){
		var phone_clear = phone.replace(/\D/g, '');
		var sms_clear = sms.replace(/\D/g, '');

		$.ajax({
			type: "POST",
			url: "ajax/check_sms.php",
			data: {phone: phone_clear, sms: sms_clear},
			dataType: 'json',
			success: function(data){
				if (data.sms) {
				    
                    if (!!data.user)
                    {
                        location.href = '/user';
                    }
                    else if (!!data.error)
                    {
                        if (data.error == 'blocked')
                        {
                            $('#check form').html('<div id="confirm_error">С вами свяжется Клиентский Центр. </div>')                            
                        }
                        else if (data.error == 'different')
                        {
                            $('#check form #confirm_error').remove();
                            $('#check form').prepend('<div id="confirm_error">Не правильный код подтверждения</div>')
                        }
                        else
                        {
                            $('#check form #confirm_error').remove();
                            $('#check form').prepend('<div id="confirm_error">'+data.error+'</div>')                            
                        }                        
                    }
                    else
                    {
    					var magnificPopup = $.magnificPopup.instance; 
    					magnificPopup.close();
    					$('input[name="check_sms"]').val(data.sms);
    					render(++current);
				    }
                } else if(data.error) {
					$('#check form #confirm_error').remove();
					$('#check form').prepend('<div id="confirm_error">'+data.error+'</div>')                            
				}
			}//,
			//error: function(jqXHR, textStatus, errorThrown){
			//	console.log(jqXHR);
			//	console.log(textStatus);
			//	console.log(errorThrown);
			//}
		});
	}
});