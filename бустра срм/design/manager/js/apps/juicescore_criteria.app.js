function JuicescoreCriteriaApp()
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

            var _name = $item.find('.js-text-name').text();

            Swal.fire({
                text: "Вы действительно хотите удалить критерий `"+_name+"`?",
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
                            name: _name
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

            var _name = $item.find('.js-text-name').text();
            var _required_ball = $item.find('[name=required_ball]').val();

            $.ajax({
                type: 'POST',
                data: {
                    action: 'update',
                    name: _name,
                    required_ball: _required_ball,
                },
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
                        $item.find('[name=name]').val(resp.name);
                        $item.find('.js-text-name').html(resp.name);
                        $item.find('[name=required_ball]').val(resp.required_ball);
                        $item.find('.js-text-required_ball').html(resp.required_ball);

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
            $('#modal_add_item').find('[name=name]').val('');
            $('#modal_add_item').find('[name=required_ball]').val('');

            $('#modal_add_item').modal();

            $('#modal_add_item').find('[name=utm_source]').focus();
        });

        // Сохранение новой записи
        $(document).on('submit', '#form_add_item', function(e){
            e.preventDefault();

            var $form = $(this);

            var _name = $form.find('[name=name]').val();
            var _required_ball = $form.find('[name=required_ball]').val();

            $.ajax({
                type: 'POST',
                data: {
                    action: 'add',
                    name: _name,
                    required_ball: _required_ball,
                },
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
    new JuicescoreCriteriaApp();
})
