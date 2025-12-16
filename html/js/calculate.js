$(document).ready(function(){
	$("#money-range").ionRangeSlider({
		type: "single",
		min: 1000,
		max: 15000,
		step: 1000,
		postfix: " <span>Р</span>",
		onChange: function (data) {
			calculate();
		}
	});

	$("#time-range").ionRangeSlider({
		type: "single",
		min: 7,
		max: 15,
		step: 1,
		postfix: " <span>дней</span>",
		onChange: function (data) {
			calculate();
		}
	});
	
	calculate();

	function calculate() {
		var amount = parseInt($("#money-range").val()),
			period = parseInt($("#time-range").val()),
			percent = 2 / 100,
			now = new Date;
		var total = amount * period * percent + amount;
		var payDate = new Date;

		payDate.setDate(now.getDate() + parseInt(period));
		var month = [
                'января',   'февраля', 'марта',  'апреля',
                'мая',      'июня',    'июля',   'августа',
                'сентября', 'октября', 'ноября', 'декабря'
            ][payDate.getMonth()];

        $("#calculator .result .total").text(total);
        $("#calculator .result .date").text(payDate.getDate() + ' ' + month);
	}
});