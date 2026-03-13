function GeneralTableApp()
{
    var app = this;

    var _init_events = function(){
        // редактирование записи
        $(document).on('click', '.js-edit-item', function(e){
            e.preventDefault();

            var $item = $(this).closest('.js-item');

            $item.find('.js-visible-view').hide();
            $item.find('.js-visible-edit').fadeIn();
        });

        // Удаление записи
        $(document).on('click', '.js-delete-item', function(e){
            e.preventDefault();

            var $item = $(this).closest('.js-item');

            var id = $item.find('.js-col-' + ID_COLUMN).text();

            Swal.fire({
                text: "Вы действительно хотите удалить запись?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Да, удалить!",
                cancelButtonText: "Отмена",
                showLoaderOnConfirm: true,
                allowOutsideClick: () => !Swal.isLoading()

            }).then((result) => {

                if (result.value)
                {
                    $.ajax({
                        type: 'POST',
                        data: {
                            action: 'delete',
                            [ID_COLUMN]: id
                        },
                        success: function(){

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

        // Сохранение редактируемой записи
        $(document).on('click', '.js-confirm-edit-item', function(e){
            e.preventDefault();

            var $item = $(this).closest('.js-item');

            var data = {};
            for (const key in COLUMNS) {
                data[key] = $item.find(`[name=${key}]`).val();
            }
            data['action'] = 'update';

            $.ajax({
                type: 'POST',
                data: data,
                success: function(resp){
                    if (!!resp.error)
                    {
                        Swal.fire({
                            text: resp.error,
                            type: 'error',
                        });
                    }
                    else
                    {
                        for (const key in COLUMNS) {
                            if (!resp.hasOwnProperty(key)) {
                                continue;
                            }

                            $item.find(`[name=${key}]`).val(resp[key]);
                            $item.find(`.js-col-${key}`).html(resp[key]);
                        }

                        $item.find('.js-visible-edit').hide();
                        $item.find('.js-visible-view').fadeIn();
                    }
                }
            });

        });

        // Отмена редактирования записи
        $(document).on('click', '.js-cancel-edit-item', function(e){
            e.preventDefault();

            var $item = $(this).closest('.js-item');

            $item.find('.js-visible-edit').hide();
            $item.find('.js-visible-view').fadeIn();
        });

        // Открытие окна для добавления
        $(document).on('click', '.js-open-add-modal', function(e){
            e.preventDefault();

            $('#modal_add_item').find('.alert').hide();

            for (const key in COLUMNS) {
                $('#modal_add_item').find(`[name=${key}]`).val('');
            }

            $('#modal_add_item').modal();
        });

        // Сохранение новой записи
        $(document).on('submit', '#form_add_item', function(e){
            e.preventDefault();

            var $form = $(this);
            data = {};
            for (const key in COLUMNS) {
                data[key] = $form.find(`[name=${key}]`).val();
            }
            data['action'] = 'add';
            $.ajax({
                type: 'POST',
                data: data,
                success: function(resp){
                    if (!!resp.error)
                    {
                        $form.find('.alert').removeClass('alert-success').addClass('alert-danger').html(resp.error).fadeIn();
                    }
                    else
                    {
                        location.reload();
                    }
                }
            });
        });
    };

    ;(function(){
    _init_events();
})();
};

$(function(){
    new GeneralTableApp();
})
