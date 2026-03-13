function BononSettingsApp()
{
    var app = this;

    var _init_events = function(){
        // при изменении сайта
        $(document).on('change', 'select[name="sites_list"]', function(e) {
            const site_id = document.querySelector('select[name="sites_list"]').selectedOptions[0].value;
            window.location.href = window.location.origin + window.location.pathname + '?site_id=' + site_id
        });

        // редактирование записи
        $(document).on('click', '.js-edit-item', function(e){
            e.preventDefault();

            var $item = $(this).closest('.js-item');

            $item.find('.js-visible-view').hide();
            $item.find('.js-visible-edit').fadeIn();
        });

        $(document).on('click', '.js-edit-token', function(e){
            e.preventDefault();

            var $item = $(this).closest('.js-token');

            $item.find('.js-visible-view').hide();
            $item.find('.js-visible-edit').fadeIn();
        });

        // Удаление записи
        $(document).on('click', '.js-delete-item', function(e){
            e.preventDefault();

            var $item = $(this).closest('.js-item');

            var _id = $item.find('.js-text-id').text();

            Swal.fire({
                text: "Вы действительно хотите удалить этот источник?",
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
                            id: _id
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

            var _id = $item.find('.js-text-id').text();
            var _utm_source = $item.find('[name=utm_source]').val();
            var _utm_medium = $item.find('[name=utm_medium]').val();
            var _chance = $item.find('[name=chance]').val();

            $.ajax({
                type: 'POST',
                data: {
                    action: 'update',
                    id: _id,
                    utm_source: _utm_source,
                    utm_medium: _utm_medium,
                    chance: _chance,
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
                        $item.find('[name=id]').val(resp.id);
                        $item.find('.js-text-id').html(resp.id);

                        $item.find('[name=utm_source]').val(resp.utm_source);
                        if (resp.utm_source === '*')
                            $item.find('.js-text-utm_source').html('Любой <span class="text-muted">(*)</span>');
                        else
                            $item.find('.js-text-utm_source').html(resp.utm_source);

                        $item.find('[name=utm_medium]').val(resp.utm_medium);
                        if (resp.utm_medium === '*')
                            $item.find('.js-text-utm_medium').html('Любой <span class="text-muted">(*)</span>');
                        else
                            $item.find('.js-text-utm_medium').html(resp.utm_medium);

                        $item.find('[name=chance]').val(resp.chance);
                        if (resp.chance == 0)
                            $item.find('.js-text-chance').html('Всегда');
                        else if (resp.chance == 1)
                            $item.find('.js-text-chance').html('50%');
                        else
                            $item.find('.js-text-chance').html('<span class="text-danger">На паузе</span>');

                        $item.find('.js-visible-edit').hide();
                        $item.find('.js-visible-view').fadeIn();
                    }
                }
            });

        });

        // Сохранение редактируемой записи
        $(document).on('click', '.js-confirm-edit-token', function(e){
            e.preventDefault();

            const type_titles = {
                'bonon-nk': 'НК',
                'bonon-pk': 'ПК',
                'bonon-nk-acc': 'НК из ЛК',
            }

            var $item = $(this).closest('.js-token');

            var _id = $item.find('.js-text-id').text().trim();
            var _name = $item.find('[name=token_name]').val();
            var _body = $item.find('[name=token_body]').val();
            var _type = $item.find('[name=token_type]').val();
            var _state = $item.find('[name=token_state]').val();

            $.ajax({
                type: 'POST',
                data: {
                    action: 'update-token',
                    id: _id,
                    name: _name,
                    body: _body,
                    type: _type,
                    state: _state,
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
                        $item.find('[name=id]').val(resp.id);
                        $item.find('.js-text-id').html(resp.id);

                        $item.find('[name=token_name]').val(resp.name);
                        $item.find('.js-text-token_name').text(resp.name);

                        $item.find('[name=token_body]').val(resp.token);
                        $item.find('.js-text-token_body').text(resp.token.substr(0, 70) + '...');

                        $item.find('[name=token_type]').val(resp.app);
                        $item.find('.js-text-token_type').text(type_titles[resp.app]);

                        $item.find('[name=token_state]').val(resp.enabled);
                        $item.find('.js-text-token_state').text(resp.enabled == '0' ? 'Отключен' : 'Включен');

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

        // Отмена редактирования записи
        $(document).on('click', '.js-cancel-edit-token', function(e){
            e.preventDefault();

            var $item = $(this).closest('.js-token');

            $item.find('.js-visible-edit').hide();
            $item.find('.js-visible-view').fadeIn();
        });

        // Открытие окна для добавления
        $(document).on('click', '.js-open-add-modal', function(e){
            e.preventDefault();

            $('#modal_add_item').find('.alert').hide();
            $('#modal_add_item').find('[name=utm_source]').val('');
            $('#modal_add_item').find('[name=utm_medium]').val('*');

            $('#modal_add_item').modal();

            $('#modal_add_item').find('[name=utm_source]').focus();
        });

        // Открытие окна для добавления
        $(document).on('click', '.js-open-add-token-modal', function(e){
            e.preventDefault();

            $('#modal_add_token').find('.alert').hide();
            $('#modal_add_token').find('[name=token_name]').val('');
            $('#modal_add_token').find('[name=token_body]').val('');

            $('#modal_add_token').modal();

            $('#modal_add_token').find('[name=token_name]').focus();
        });

        // Сохранение новой записи
        $(document).on('submit', '#form_add_item', function(e){
            e.preventDefault();

            var $form = $(this);

            var _utm_source = $form.find('[name=utm_source]').val();
            var _utm_medium = $form.find('[name=utm_medium]').val();
            var _chance = $form.find('[name=chance]').val();

            $.ajax({
                type: 'POST',
                data: {
                    action: 'add',
                    utm_source: _utm_source,
                    utm_medium: _utm_medium,
                    chance: _chance,
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

        // Сохранение новой записи
        $(document).on('submit', '#form_add_token', function(e){
            e.preventDefault();

            var $form = $(this);

            var _name = $form.find('[name=token_name]').val();
            var _body = $form.find('[name=token_body]').val();
            var _type = $form.find('[name=token_type]').val();
            var _state = $form.find('[name=token_state]').val();

            $.ajax({
                type: 'POST',
                data: {
                    action: 'add-token',
                    name: _name,
                    body: _body,
                    type: _type,
                    state: _state,
                },
                success: function(resp){
                    if (!!resp.error) {
                        $form.find('.alert').removeClass('alert-success').addClass('alert-danger').html(resp.error).fadeIn();
                    } else {
                        location.reload();
                    }
                }
            });
        });

        $('[name="bonon_enabled"]').on('change', function () {
            let value = $(this).prop('checked') ? 1 : 0;

            $.ajax({
                data: { action: 'toggle-bonon', value },
                dataType: 'json',
                method : 'POST',
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function(){
                    $('.preloader').hide();
                }
            });
        });
    };

    ;(function(){
    _init_events();
})();
};
$(function(){
    new BononSettingsApp();
})
