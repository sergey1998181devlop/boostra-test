$(document).ready(function() {
	var steps = $("#steps").find("fieldset"),
		current = 0,
		validator = $("#worksheet form").validate({
			errorElement: "span",
			rules: {
				"sheet[name]": {
					russian: true
				},
				"sheet[birthday]": {
					Birth: true
				},
				"sheet[phone]": {
					Code: true
				}
			}
		}),
		fields = $("#steps").find("input").length,
		heading = {
			0: "Она короткая. Мы не будем никому звонить.",
			1: "Осталось ввести паспортные данные и адрес.",
			2: "Мы уже проверяем анкету, вам осталось заполнить лишь адрес.",
			3: "Мы уже проверяем Вашу заявку!"
		},
		progress = {
			0: "личные данные",
			1: "паспортные данные",
			2: "адрес",
			3: ""
		};

	$('#worksheet input').focus(function () {
		progressSlider();
	})

	render(current);
	checkForm($("#worksheet .checkbox input#equal").checked);

	$("input:not(:checkbox), textarea, select", "fieldset").each(function(i, v) {
		var placeholder = $(v).attr('placeholder');
		$(v).attr('placeholder', '');
		$(v).parent().append('<span class="floating-label">' + placeholder + '</span>');
	});

	$("input.adding", "fieldset").each(function(i, v) {
		adding(v);
	});

	$("#worksheet .checkbox input#equal").change(function() {
		checkForm(this.checked);
	});

	$('#worksheet a.prev').click(function() {
		render(--current);
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
			console.log(v);
			console.log(valid);
		});

		if (!valid) {
			return;
		}

		if (current == 0) {
			$("#check .time").countdown(60);
			$.magnificPopup.open({
				items: {
					src: '#check'
				},
				type: 'inline'
			});
			return;
		}

		render(++current);

		if (current == $("#steps fieldset").length - 1) {
			$('body').addClass('wait');
		}
	});

	$('#check form').submit(function (event) {
		event.preventDefault();
        validator = $("#check form").validate({
            errorElement: "span"
        });
        if ($("#check form").valid()) {
        	var magnificPopup = $.magnificPopup.instance; 
			magnificPopup.close();
			render(++current);
        }
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
		var valid = $("#worksheet").find("input.valid").length;
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
});