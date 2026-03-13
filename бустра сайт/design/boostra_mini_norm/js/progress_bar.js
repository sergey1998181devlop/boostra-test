/* global jQuery */
(function ($) {
    var BUCKET_TEXT = [
        'Автоматические уведомления о просрочке',
        'Договор переходит в работу отделу взыскания. Пролонгация недоступна.',
        'Подготовка к передаче договора в коллекторское агентство',
        'Договор передан в коллекторское агентство'
    ];
    var DONUT = [80, 50, 20, 5];

    function bucketClass(day){
        day = +day || 0;
        if (day > 31) return 'bucket-31plus';
        if (day >= 10) return 'bucket-11-30';
        if (day >= 4)  return 'bucket-4-10';
        return 'bucket-1-3';
    }
    function bucketIndex(day){
        if (day > 31) return 3;
        if (day >= 10) return 2;
        if (day >= 4)  return 1;
        return 0;
    }

    // ---------- ВАЖНО: надёжная расстановка тиков ----------
    function placeTicks($wrap){
        var $slider = $wrap.find('.overdue-progress__slider');
        var min = +($slider.attr('min')||0),
            max = +($slider.attr('max')||31);
        var $ticksWrapper = $wrap.find('.overdue-progress__ticks');

        $ticksWrapper.css({width: $slider.width()+'px'})

        $wrap.find('.overdue-progress__tick').each(function(i){
            var value = +$(this).data('value');
            const ua = (navigator.userAgent || '').toLowerCase();
            const isMobile = /android|iphone|ipad|ipod|mobile/.test(ua)
                || (window.matchMedia && matchMedia('(pointer:coarse)').matches);

            if (i === 0) {
                $(this).css('left', 0);
                return;
            }

            if (i === $wrap.find('.overdue-progress__tick').length - 1) {
                $(this).css('right', isMobile ? '5px' : '10px');
                return;
            }

            if (value < min) value = min;
            if (value > max) value = max;

            var pct = (Math.max(0, Math.min(max, value)) / max * 100).toFixed(2);

            if (i === 1) {
                pct = isMobile ? parseFloat(pct) + 4 : parseFloat(pct) + 3;
            } else {
                pct = isMobile ? parseFloat(pct) + 3 : parseFloat(pct) + 2;
            }

            $(this).css('left', pct + '%');
        });
    }

    // ждём стабильной ширины слайдера, чтобы placeTicks не сработал на «нулевой» layout
    function placeTicksWhenReady($wrap){
        var $slider = $wrap.find('.overdue-progress__slider');
        var prev = -1, sameCount = 0;
        function wait(){
            var w = Math.round($slider.outerWidth());
            if (w > 0 && w === prev) sameCount++;
            else sameCount = 0;
            prev = w;
            if (sameCount >= 1) { // два подряд одинаковых измерения
                placeTicks($wrap);
            } else {
                requestAnimationFrame(wait);
            }
        }
        requestAnimationFrame(wait);
    }

    // наблюдаем перестройки (смена шрифта/адаптив/контент)
    function observeResize($wrap){
        var $slider = $wrap.find('.overdue-progress__slider');
        if (!$slider.length || !('ResizeObserver' in window)) return;
        var ro = new ResizeObserver(function(){ placeTicks($wrap); });
        ro.observe($slider[0]);
        // сохраним, чтобы GC не съел в некоторых браузерах
        $wrap.data('pb_ro', ro);
    }

    function setBucketClass($root, day){
        $root.removeClass('bucket-1-3 bucket-4-10 bucket-11-30 bucket-31plus')
            .addClass(bucketClass(day));
        $root.find('.overdue-progress__info')
            .removeClass('bucket-1-3 bucket-4-10 bucket-11-30 bucket-31plus')
            .addClass(bucketClass(day));
    }

    function applyDay($root, $slider, day){
        var min = parseFloat($slider.attr('min')) || 0;
        var max = parseFloat($slider.attr('max')) || 31;
        var span = (max - min) || 1;
        var val  = Math.max(min, Math.min(max, +day || min));

        setBucketClass($root, val);
        var idx = bucketIndex(val);
        $root.find('.overdue-progress__message').text(BUCKET_TEXT[idx]);
        $root.find('.overdue-progress__donut-value').text(DONUT[idx] + '%');

        var pct = ((val - min) / span) * 100;
        $slider.css('background-size', pct.toFixed(2) + '% 100%');
        $root[0].style.setProperty('--progress', DONUT[idx] + '%');
    }

    function logFirst($root, day){
        var onceKey = 'overdue:first:' + $root.data('order-id');
        if (window.slider_interact || localStorage.getItem(onceKey)) return;
        localStorage.setItem(onceKey, '1');

        $.post('/overdue_slider/log', {
            user_id: Number($root.data('user-id') || 0),
            order_id: Number($root.data('order-id') || 0),
            action: 'slider_first',
            overdue_day: Number(day || 0)
        });
    }
    function logInfo($root){
        var onceKey = 'overdue:i:' + $root.data('order-id');
        if (window.click_info || localStorage.getItem(onceKey)) return;
        localStorage.setItem(onceKey, '1');

        var day = $root.find('.overdue-progress__slider').val() || 0;
        $.post('/overdue_slider/log', {
            user_id: Number($root.data('user-id') || 0),
            order_id: Number($root.data('order-id') || 0),
            action: 'info_click',
            overdue_day: Number(day || 0)
        });
    }

    $(function(){
        $('.overdue-progress').each(function(){
            var $root = $(this);
            var $slider = $root.find('.overdue-progress__slider');
            var $info = $root.find('.overdue-progress__info');
            var $tip = $root.find('.overdue-progress__tooltip');

            // 1) ждём стабильной ширины, затем выставляем тики
            placeTicksWhenReady($root);
            observeResize($root);

            // 2) инициализация значения
            var initDay = +($root.data('overdue-days') || 0);
            var min = parseFloat($slider.attr('min')) || 0;
            var max = parseFloat($slider.attr('max')) || 31;
            initDay = Math.max(min, Math.min(max, initDay));
            applyDay($root, $slider, initDay);

            // 3) обработчики
            var interacted = false;
            $slider.on('input change', function(){
                var day = parseFloat(this.value) || min;
                day = Math.max(min, Math.min(max, day));
                applyDay($root, $slider, day);
                if (!interacted) { interacted = true; logFirst($root, day); }
            });

            $info.off('click').on('click', function(){
                if ($tip.is(':visible')) $tip.attr('hidden', true).hide();
                else $tip.removeAttr('hidden').show();
                logInfo($root);
            });

            $root.find('.overdue-progress__donut').off('click').on('click', function(){
                if ($tip[0].hasAttribute('hidden')) {
                    $tip.text('Чем больше просрочка, тем ниже вероятность одобрения нового займа')
                        .removeAttr('hidden').show();
                } else {
                    $tip.attr('hidden', 1).hide();
                }
                logInfo($root);
            });
        });

        // на всякий случай — после полной загрузки страницы (шрифты/стили)
        $(window).on('load', function(){
            $('.overdue-progress').each(function(){ placeTicks($(this)); });
        });
    });

})(jQuery);
