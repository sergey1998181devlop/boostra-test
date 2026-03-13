function TicketGuideApp()
{
    let init = function () {

        // редактирование записи
        $(document).on('click', '.js-edit-item', function (e) {
            e.preventDefault();
            let $item = $(this).closest('.js-item');
            $item.find('.js-visible-view').hide();
            $item.find('.js-visible-edit').fadeIn();
        });

        // Удаление записи
        $(document).on('click', '.js-delete-item', function (e) {
            e.preventDefault();
            let $item = $(this).closest('.js-item');
            let ticketId = $item.find('[name=id]').val();
            let ticketSubject = $item.find('[name=subject]').val();

            Swal.fire({
                text: 'Вы действительно хотите удалить тему `' + ticketSubject + '`?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#DD6B55',
                confirmButtonText: 'Да, удалить!',
                cancelButtonText: 'Отмена',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        type: 'POST',
                        data: {
                            action: 'delete',
                            id: ticketId
                        },
                        success: function () {
                            $item.remove();
                            Swal.fire({
                                timer: 5000,
                                text: 'Тема удалена!',
                                type: 'success',
                            });
                        }
                    })
                }
            });
        });

        // Сохранение редактируемой записи
        $(document).on('click', '.js-confirm-edit-item', function (e) {
            e.preventDefault();

            let $item = $(this).closest('.js-item');
            let ticketId = $item.find('[name=id]').val();
            let ticketSubject = $item.find('[name=subject]').val();
            let ticketDescription = $item.find('[name=description]').val();

            $.ajax({
                type: 'POST',
                data: {
                    action: 'update',
                    id: ticketId,
                    subject: ticketSubject,
                    description: ticketDescription,
                },
                success: function (resp) {
                    if (!!resp.error) {
                        Swal.fire({
                            text: resp.error,
                            type: 'error',
                        });
                    } else {
                        $item.find('[name=subject]').val(resp.subject);
                        $item.find('.js-text-ticket-subject').html(resp.subject);
                        $item.find('[name=description]').val(resp.description);
                        $item.find('.js-text-ticket-description').html(resp.description);

                        $item.find('.js-visible-edit').hide();
                        $item.find('.js-visible-view').fadeIn();
                    }
                }
            });
        });

        // Отмена редактирования записи
        $(document).on('click', '.js-cancel-edit-item', function (e) {
            e.preventDefault();

            let $item = $(this).closest('.js-item');

            $item.find('.js-visible-edit').hide();
            $item.find('.js-visible-view').fadeIn();
        });

        // Открытие окна для добавления
        $(document).on('click', '.js-open-add-modal', function (e) {
            e.preventDefault();

            $('#modal_add_item').find('.alert').hide();
            $('#modal_add_item').find('[name=subject]').val('');
            $('#modal_add_item').find('[name=description]').val('');

            $('#modal_add_item').modal();
            $('#modal_add_item').find('[name=subject]').focus();
        });

        // Сохранение новой записи
        $(document).on('submit', '#form_add_item', function (e) {
            e.preventDefault();

            let $form = $(this);

            let ticketSubject = $form.find('[name=subject]').val();
            let ticketDescription = $form.find('[name=description]').val();

            $.ajax({
                type: 'POST',
                data: {
                    action: 'add',
                    subject: ticketSubject,
                    description: ticketDescription,
                },
                success: function (resp) {
                    if (!!resp.error) {
                        $form.find('.alert').removeClass('alert-success').addClass('alert-danger').html(resp.error).fadeIn();
                    } else {
                        let new_row = '<tr class="js-item">';
                        new_row += '<td><div class="js-text-id">' + resp.id + '</div></td>';
                        new_row += '<td>';
                        new_row += '<div class="js-visible-view js-text-ticket-subject">' + resp.name + '</div>';
                        new_row += '<div class="js-visible-edit" style="display:none">';
                        new_row += '<input type="hidden" name="id" value="' + resp.id + '" />';
                        new_row += '<input type="text" class="form-control form-control-sm" name="name" value="' + resp.name + '" />';
                        new_row += '</div>';
                        new_row += '</td>';
                        new_row += '<td>';
                        new_row += '<div class="js-visible-view js-text-ticket-description">' + resp.description + '</div>';
                        new_row += '<div class="js-visible-edit" style="display:none">';
                        new_row += '<input type="text" class="form-control form-control-sm" name="description" value="' + resp.description + '" />';
                        new_row += '</div>';
                        new_row += '</td>';
                        new_row += '<td>';
                        new_row += '<div class="js-visible-view js-text-ticket-created">' + resp.created + '</div>';
                        new_row += '<div class="js-visible-edit" style="display:none">';
                        new_row += '<input type="text" class="form-control form-control-sm" name="created" value="' + resp.created + '" />';
                        new_row += '</div>';
                        new_row += '</td>';
                        new_row += '<td class="text-right">';
                        new_row += '<div class="js-visible-view">';
                        new_row += '<a href="#" class="text-info js-edit-item" title="Редактировать"><i class=" fas fa-edit"></i></a> ';
                        new_row += '<a href="#" class="text-danger js-delete-item" title="Удалить"><i class="far fa-trash-alt"></i></a>';
                        new_row += '</div>';
                        new_row += '<div class="js-visible-edit" style="display:none">';
                        new_row += '<a href="#" class="text-success js-confirm-edit-item" title="Сохранить"><i class="fas fa-check-circle"></i></a> ';
                        new_row += '<a href="#" class="text-danger js-cancel-edit-item" title="Отменить"><i class="fas fa-times-circle"></i></a>';
                        new_row += '</div>';
                        new_row += '</td>';
                        new_row += '</tr>';

                        $('#table-body').append(new_row);

                        $('#modal_add_item').modal('hide');
                        Swal.fire({
                            timer: 5000,
                            text: 'Тема "' + resp.subject + '" добавлена!',
                            type: 'success',
                        });
                    }
                }
            });
        });
    };

    ;(function () {
    init();
})();
};

$(function () {
    new TicketGuideApp();
});

