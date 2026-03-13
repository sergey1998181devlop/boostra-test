$(document).ready(function(){
    function setTimeGetMoney() {
        const date = new Date;
        date.setMinutes(date.getMinutes() + 20);
        let minutes = date.getMinutes();
        if (minutes < 10) {
            minutes = '0' + minutes;
        }
        $(".get_money_time").text(date.getHours() + ':' + minutes);
    }

    setTimeGetMoney();

    $(".ion_slider_wrapper input").ionRangeSlider({
        skin: "round",
        type: "single",
        onStart: function(data) {
            calculate(data);
        },
        onChange: function(data) {
            calculate(data);
        }
    });

    function calculate(data) {

        // костыль от задвоения периода на маленьких экранах
        if (window.innerWidth < 576) {
            if (data.from === data.min && data.input.attr('name') === 'period') {
                $(data.input).closest('.ion_slider_wrapper').find('.irs-min').hide();
            } else {
                $(data.input).closest('.ion_slider_wrapper').find('.irs-min').show();
            }
        }

        let element_current = $('.' + data.input.attr('name') + '_current'),
            postfix = data.input.attr('name') === 'amount' ? ' ₽' : ' дней';

        // меняем текст в блоках
        element_current.text(data.from + postfix);

        let amount = parseInt($('.ion_slider_wrapper [name="amount"]').val()),
            period = parseInt($('.ion_slider_wrapper [name="period"]').val()),
			percent = parseFloat(BASE_PERCENTS) / 100,
            discount_percent = parseFloat($("#percent").val()),
            discount_period = parseInt($("#max_period").val()),
            now = new Date;

        let percent_calculate = period > discount_period ? percent : discount_percent,
            total = Math.round(amount * period * percent_calculate + amount),
            total_without_discount = Math.round(amount * period * percent + amount);

        window.percent_calculate = percent_calculate;
        $.cookie('percent_calculate', percent_calculate, { expires: 365, path: '/' });

        if (period > discount_period) {
            $("#calculator_wrapper").get(0).style.setProperty("--calculate_green", "25, 135, 84");
            $("#get_zaim").text('Получить').removeClass('orange');
            $(".amount_discount").hide();
            $("#calculator .amount_percent").removeClass('orange');
        } else {
            $("#calculator_wrapper").get(0).style.removeProperty("--calculate_green");
            $("#get_zaim").text('Получить бесплатно').addClass('orange');
            $(".amount_discount").text(total_without_discount + ' ₽').show();
            $("#calculator .amount_percent").addClass('orange');
        }

        const payDate = new Date;
        payDate.setDate(now.getDate() + period);

        const month = [
            'января', 'февраля', 'марта', 'апреля',
            'мая', 'июня', 'июля', 'августа',
            'сентября', 'октября', 'ноября', 'декабря'
        ][payDate.getMonth()];

        $("#calculator .amount_total").html(total + ' ₽');
        $("#calculator .period_end_data").text(payDate.getDate() + ' ' + month);
        $("#calculator .amount_percent").text((percent_calculate * 100) + '%');
    }
});
