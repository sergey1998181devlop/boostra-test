$(document).ready(function(){
    var _max_period = 16;
	$("#money-range").ionRangeSlider({
		type: "single",
		min: 1000,
		max: 30000,
		step: 1000,
		postfix: " <span>Р</span>",
		onChange: function (data) {
			calculate();
		}
	});

	$("#time-range").ionRangeSlider({
		type: "single",
		min: 5,
		max: _max_period,
		step: 1,
		postfix: " <span>дней</span>",
		onChange: function (data) {
			calculate();
		}
	});
	
	if ($("#money-range").length > 0)
        calculate();

	function calculate() {
		var amount = parseInt($("#money-range").val()),
			period = parseInt($("#time-range").val()),
			percent = parseFloat(BASE_PERCENTS) / 100,
			discount_percent = parseFloat($("#percent").val()),
			discount_period = parseInt($("#max_period").val()),
			have_close_credits = parseInt($("#have_close_credits").val()),
			now = new Date;
            
		if (period > discount_period)
        {
            var total = amount * period * percent + amount;
        }
        else
        {
            var total = amount * period * discount_percent + amount;
        }
        
		
        var payDate = new Date;

		payDate.setDate(now.getDate() + parseInt(period));
		var month = [
                'января',   'февраля', 'марта',  'апреля',
                'мая',      'июня',    'июля',   'августа',
                'сентября', 'октября', 'ноября', 'декабря'
            ][payDate.getMonth()];
        
		if (period > discount_period)
        {
            $('.period .irs-single, .period .irs-min').removeClass('green');
            $("#calculator .result, .calculate_green").removeClass('green');
            $('.period .irs-slider.single').removeClass('bg-green');
            $('.discount_title, .discount_subtitle').removeClass('green').addClass('red');
            $('.main-page-button').text('Получить займ');
        }
        else
        {
            if (location.pathname !== '/user' || $('[name="has_user_discount"]').length) {
                $('.period .irs-single').addClass('green');
            }

            $("#calculator .result, .calculate_green, .period .irs-min").addClass('green');
            $('.period .irs-slider.single').addClass('bg-green');
            $('.discount_title, .discount_subtitle').addClass('green').removeClass('red');
            $('.main-page-button').text('Получить бесплатно');
        }
        $("#calculator .result .total").html(total);
            
        $("#calculator .result .date").text(payDate.getDate() + ' ' + month);

        if ($('.js-insure-amount').length > 0)
        {
            var insure;
            if (have_close_credits == 0)
                insure = 0.33;
            else if (amount <= 2000)
                insure = 0.23;
            else if (amount <= 4000)
                insure = 0.18;
            else if (amount <= 7000)
                insure = 0.15;
            else if (amount <= 10000)
                insure = 0.14;
            else 
                insure = 0.13;

            $('.js-insure-amount').html(' составляет '+ parseInt(amount * insure)+' руб.');
            $('.js-insure-premia').html(' '+ parseInt(amount * insure * 20)+' руб.');
        }
	}

    $(".ion-btn").on('click', function (){
        if (!$(this).hasClass('ion-il')) {
            let sliderElement = $(this).closest('div').find('input.irs-hidden-input').data('ionRangeSlider'),
                isPlus = $(this).hasClass('ion-plus'),
                step = sliderElement.options.step,
                value = sliderElement.old_from,
                newValue = isPlus ? value + step : value - step;
    
            sliderElement.update({from: newValue})
            
            calculate();
        }
    });
});