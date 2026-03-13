$(function () {
    $('#save-sound-ticket-notice').on('click', function () {
        var $btn = $(this);
        var soundValue = $('#sound_ticket_notice').val() || '';
        var checkInterval = parseInt($('#check_interval_sec').val()) || 10;
        var remindInterval = parseInt($('#remind_interval_min').val()) || 15;

        if (checkInterval < 1 || remindInterval < 1) {
            Swal.fire({
                type: 'error',
                title: 'Ошибка',
                text: 'Интервалы должны быть больше 0'
            });
            return;
        }

        Swal.fire({
            title: 'Сохранение...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });
        Swal.showLoading();

        $.ajax({
            url: '/tickets/settings',
            method: 'POST',
            data: {
                action: 'save_sound_ticket_notice',
                sound_ticket_notice: soundValue,
                check_interval_sec: checkInterval,
                remind_interval_min: remindInterval
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        type: 'success',
                        title: 'Успешно',
                        text: response.message || 'Настройки звукового уведомления сохранены',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        type: 'error',
                        title: 'Ошибка',
                        text: response.message || 'Не удалось сохранить настройку'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    type: 'error',
                    title: 'Ошибка',
                    text: 'Не удалось сохранить настройку'
                });
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });
});