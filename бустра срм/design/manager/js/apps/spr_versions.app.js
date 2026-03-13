function SprVersionsApp()
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

        // Сохранение редактируемой записи
        $(document).on('click', '.js-confirm-edit-item', function(e){
            e.preventDefault();

            var $item = $(this).closest('.js-item');

            var _id = $item.find('.js-text-id').text();
            var _description = $item.find('[name=description]').val();

            $.ajax({
                type: 'POST',
                data: {
                    action: 'update',
                    id: _id,
                    description: _description,
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
                        $item.find('[name=description]').val(_description);
                        $item.find('.js-text-description').html(_description);

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
            $('#modal_add_item').modal();
            $('#modal_add_item').find('[name=description]').focus();
        });

        // Сохранение новой записи
        $(document).on('submit', '#form_add_item', function(e){
            e.preventDefault();

            var $form = $(this);

            var _description = $form.find('[name=description]').val();

            $.ajax({
                type: 'POST',
                data: {
                    action: 'add',
                    description: _description,
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
    new SprVersionsApp();
})
