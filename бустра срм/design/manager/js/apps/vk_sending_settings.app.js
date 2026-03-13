
function VkSendingSettingsApp()
{
    var DEFAULT_VALUES = {
        send_hour: 19,
        day_from: -1,
        day_to: 0,
        age_from: 18,
        age_to: 90,
        gender: 'any',
        scorista_ball_from: 1,
        scorista_ball_to: 1000,
        scorista_decision: 'any'
    };

    var _InitEvents = function () {
        // Открытие редактора (Редактирование)
        $(document).on('click', '.js-edit-item', function (e) {
            e.preventDefault();
            var $modal =  $('#modal_editor');
            var $item = $(this).closest('.js-item');
            var id = $item.data('id');

            $.ajax({
                type: 'POST',
                data: {
                    action: 'load',
                    id: id
                },
                success: function (resp) {
                    $modal.find('input[type="text"]').each(function () {
                        var valName = $(this).attr('name');
                        if (resp[valName] !== undefined)
                            $(this).val(resp[valName]);
                        else
                            $(this).val('');
                    });
                    $('#modal_editor').modal();
                }
            });
        });

        // Открытие редактора (Добавление)
        $(document).on('click', '.js-open-add-modal', function (e) {
            e.preventDefault();
            var $modal =  $('#modal_editor');

            $modal.find('input[type="text"]').each(function () {
                $(this).val('');
                var valName = $(this).attr('name');
                if (DEFAULT_VALUES[valName] !== undefined)
                    $(this).val(DEFAULT_VALUES[valName]);
            });

            $modal.modal();
        });

        // Удаление записи
        $(document).on('click', '.js-delete-item', function (e) {
            e.preventDefault();

            var $item = $(this).closest('.js-item');
            var id = $item.data('id');

            Swal.fire({
                text: "Вы действительно хотите удалить это сообщение?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Да, удалить!",
                cancelButtonText: "Отмена",
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading()

            }).then((result) => {

                if (result.value) {
                    $.ajax({
                        type: 'POST',
                        data: {
                            action: 'delete',
                            id: id
                        },
                        success: function () {

                            $item.remove();

                            Swal.fire({
                                timer: 5000,
                                text: 'Удалено!',
                                type: 'success',
                            });
                        }
                    });
                }
            });
        });

        // Включение/выключение конкретного сообщения
        $(document).on('click', '.js-toggle-item', function (e) {
            e.preventDefault();

            var $item = $(this).closest('.js-item');
            var id = $item.data('id');
            var enabled = $(this).data('enabled');
            $.ajax({
                type: 'POST',
                data: {
                    action: 'toggle',
                    id: id,
                    enabled: enabled
                },
                success: function () {
                    Swal.fire({
                        timer: 2000,
                        text: '',
                        type: 'success',
                    });

                    location.reload();
                }
            });
        });

        // Сохранение записи (Редактирование/Добавление)
        $(document).on('submit', '#form_editor', function (e) {
            e.preventDefault();

            var data = {};
            var $form = $(this);
            $form.find('input[type="text"]').each(function () {
                var valName = $(this).attr('name');
                data[valName] = $(this).val();
            });

            $.ajax({
                type: 'POST',
                data: {
                    ...data,
                    action: 'save',
                },
                success: function (resp) {
                    if (!!resp.error) {
                        $form.find('.alert').removeClass('alert-success').addClass('alert-danger').html(resp.error).fadeIn();
                    } else {
                        location.reload();
                    }
                }
            });
        });

        // Включение/выключение рассылок в настройках бота
        $('[name="vk_bot_enabled"]').on('change', function () {
            let key = $(this).attr('name'),
                value = $(this).prop('checked') ? 1 : 0;

            updateSettings(key, value);
        });
    };


    ;(function(){
        _InitEvents();
    })();
}

$(function(){
    new VkSendingSettingsApp();
});
