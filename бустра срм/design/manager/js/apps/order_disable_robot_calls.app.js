$(function () {
    var $modal = $('#disable_robot_calls_modal');
    var $daysSelect = $modal.find('.js-disable-robot-calls-days-select');
    var $daysCustom = $modal.find('.js-disable-robot-calls-days-custom');
    var $submitBtn = $modal.find('.js-disable-robot-calls-submit');

    function formatDateEnd(iso) {
        if (!iso) return '';
        var d = new Date(iso.replace(' ', 'T'));
        if (isNaN(d.getTime())) return '';
        var day = ('0' + d.getDate()).slice(-2);
        var month = ('0' + (d.getMonth() + 1)).slice(-2);
        var year = d.getFullYear();
        var h = ('0' + d.getHours()).slice(-2);
        var m = ('0' + d.getMinutes()).slice(-2);
        return day + '.' + month + '.' + year + ' ' + h + ':' + m;
    }

    function renderDisabledState(orderId, dateEnd) {
        var until = dateEnd ? '<p class="small text-muted mb-1">Отключено до ' + dateEnd + '</p>' : '';
        return until + '<button type="button" class="btn-block btn btn-outline-success js-order-enable-robot-calls" data-order-id="' + orderId + '" title="Включить исходящие звонки робота">' +
            '<i class="fas fa-phone"></i> Включить звонки робота</button>';
    }

    function renderEnabledState(orderId) {
        return '<button type="button" class="btn-block btn btn-outline-danger js-order-disable-robot-calls" data-order-id="' + orderId + '" data-target="#disable_robot_calls_modal" data-toggle="modal" title="Добавить номера клиента в DNC-лист Voximplant">' +
            '<i class="fas fa-phone-slash"></i> Отключить звонки робота</button>';
    }

    $(document).on('click', '.js-order-disable-robot-calls', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var orderId = $btn.data('order-id');
        if (!orderId) {
            Swal.fire({ title: 'Ошибка', text: 'Не указан ID заявки', type: 'error' });
            return;
        }
        $modal.data('order-id', orderId);
        $daysSelect.val('1');
        $daysCustom.val('');
        $modal.modal('show');
    });

    $modal.on('show.bs.modal', function () {
        $submitBtn.prop('disabled', false).removeClass('loading');
    });

    $(document).on('click', '.js-disable-robot-calls-submit', function (e) {
        e.preventDefault();
        var orderId = $modal.data('order-id');
        if (!orderId) {
            Swal.fire({ title: 'Ошибка', text: 'Не указан ID заявки', type: 'error' });
            return;
        }

        var customVal = $daysCustom.val().trim();
        var days;
        if (customVal !== '') {
            days = parseInt(customVal, 10);
            if (isNaN(days) || days < 1 || days > 365) {
                Swal.fire({ title: 'Ошибка', text: 'Введите число дней от 1 до 365', type: 'error' });
                return;
            }
        } else {
            days = parseInt($daysSelect.val(), 10) || 1;
        }

        if ($submitBtn.hasClass('loading')) {
            return;
        }
        $submitBtn.addClass('loading').prop('disabled', true);

        $.ajax({
            url: '/app/orders/' + orderId + '/disable-robot-calls',
            type: 'POST',
            dataType: 'json',
            data: { days: days },
            success: function (resp) {
                if (resp && resp.success) {
                    $modal.modal('hide');
                    $submitBtn.removeClass('loading').prop('disabled', false);
                    var $block = $('.js-robot-calls-block');
                    if ($block.length) {
                        $block.html(renderDisabledState(orderId, formatDateEnd(resp.date_end)));
                    }
                    Swal.fire({
                        title: 'Готово',
                        text: resp.message || 'Исходящие звонки робота отключены',
                        type: 'success',
                    });
                } else {
                    Swal.fire({
                        title: 'Ошибка',
                        text: (resp && resp.message) ? resp.message : 'Не удалось отключить звонки',
                        type: 'error',
                    });
                    $submitBtn.removeClass('loading').prop('disabled', false);
                }
            },
            error: function (xhr) {
                var msg = 'Ошибка при обращении к серверу';
                try {
                    var data = xhr.responseJSON || (xhr.responseText ? JSON.parse(xhr.responseText) : null);
                    if (data && data.message) {
                        msg = data.message;
                    }
                } catch (err) {}
                Swal.fire({ title: 'Ошибка', text: msg, type: 'error' });
                $submitBtn.removeClass('loading').prop('disabled', false);
            }
        });
    });

    $(document).on('click', '.js-order-enable-robot-calls', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var orderId = $btn.data('order-id');
        if (!orderId) {
            Swal.fire({ title: 'Ошибка', text: 'Не указан ID заявки', type: 'error' });
            return;
        }
        if ($btn.hasClass('loading') || $btn.prop('disabled')) {
            return;
        }
        $btn.addClass('loading').prop('disabled', true);

        $.ajax({
            url: '/app/orders/' + orderId + '/enable-robot-calls',
            type: 'POST',
            dataType: 'json',
            data: {},
            success: function (resp) {
                if (resp && resp.success) {
                    var $block = $('.js-robot-calls-block');
                    if ($block.length) {
                        $block.html(renderEnabledState(orderId));
                    }
                    Swal.fire({
                        title: 'Готово',
                        text: resp.message || 'Исходящие звонки робота включены',
                        type: 'success',
                    });
                } else {
                    Swal.fire({
                        title: 'Ошибка',
                        text: (resp && resp.message) ? resp.message : 'Не удалось включить звонки',
                        type: 'error',
                    });
                    $btn.removeClass('loading').prop('disabled', false);
                }
            },
            error: function (xhr) {
                var msg = 'Ошибка при обращении к серверу';
                try {
                    var data = xhr.responseJSON || (xhr.responseText ? JSON.parse(xhr.responseText) : null);
                    if (data && data.message) {
                        msg = data.message;
                    }
                } catch (err) {}
                Swal.fire({ title: 'Ошибка', text: msg, type: 'error' });
                $btn.removeClass('loading').prop('disabled', false);
            }
        });
    });
});
