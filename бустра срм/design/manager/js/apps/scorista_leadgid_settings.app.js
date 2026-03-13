function ScoristaLeadgidSettingsApp()
{
    var app = this;

    const PARAMS_IN_CHECK = {
        'utm_source': 'Лидген <span class="text-muted">(utm_source)</span>',
        'utm_medium': 'Вебмастер <span class="text-muted">(utm_medium)</span>',
        'have_close_credits': 'Тип',
        'scorista_ball': 'Балл',
        'min_ball': 'Мин. Балл',
        'amount': 'Рекомендуемая сумма',
    };

    var current_page = window.location.pathname.split('/').pop();
    if (current_page == 'approve_amount_settings') {
        PARAMS_IN_CHECK.amount = 'Добавляемая сумма';
    }

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

            var _id = $item.find('.js-text-id').text();
            var _utm_source = $item.find('[name=utm_source]').val();

            Swal.fire({
                text: "Вы действительно хотите удалить utm_source `"+_utm_source+"`?",
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
            var _type = $item.find('[name=type]').val();
            var _utm_source = $item.find('[name=utm_source]').val();
            var _utm_medium = $item.find('[name=utm_medium]').val();
            var _have_close_credits = $item.find('[name=have_close_credits]').val();
            var _min_ball = $item.find('[name=min_ball]').val();
            var _amount = $item.find('[name=amount]').val();

            $.ajax({
                type: 'POST',
                data: {
                    action: 'update',
                    id: _id,
                    type: _type,
                    utm_source: _utm_source,
                    utm_medium: _utm_medium,
                    have_close_credits: _have_close_credits,
                    min_ball: _min_ball,
                    amount: _amount,
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
                        if (resp.type) {
                            $item.find('[name=type]').val(resp.type);
                            if (resp.type === 0)
                                $item.find('.js-text-type').html('<span class="text-danger">При отказе</span>');
                            else
                                $item.find('.js-text-type').html('<span class="text-success">После одобрения</span>');
                        }

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

                        $item.find('[name=have_close_credits]').val(resp.have_close_credits);
                        if (resp.have_close_credits == 1)
                            $item.find('.js-text-have_close_credits').html('ПК <span class="text-muted">(1)</span>');
                        else
                            $item.find('.js-text-have_close_credits').html('НК <span class="text-muted">(0)</span>');

                        $item.find('[name=min_ball]').val(resp.min_ball);
                        if (resp.min_ball > 0)
                            $item.find('.js-text-min_ball').html(`>= ${resp.min_ball}`);
                        else
                            $item.find('.js-text-min_ball').html(`<= ${Math.abs(resp.min_ball)}`);

                        $item.find('[name=amount]').val(resp.amount);
                        if (resp.amount == 0)
                            $item.find('.js-text-amount').html('<span class="text-danger"><strong>Отказ по заявке</strong></span>');
                        else
                            $item.find('.js-text-amount').html(resp.amount);

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
            $('#modal_add_item').find('[name=utm_source]').val('');
            $('#modal_add_item').find('[name=utm_medium]').val('*');
            $('#modal_add_item').find('[name=have_close_credits]').val('');
            $('#modal_add_item').find('[name=min_ball]').val('');
            $('#modal_add_item').find('[name=amount]').val('');

            $('#modal_add_item').modal();

            $('#modal_add_item').find('[name=utm_source]').focus();
        });

        // Сохранение новой записи
        $(document).on('submit', '#form_add_item', function(e){
            e.preventDefault();

            var $form = $(this);

            var _type = $form.find('[name=type]').val();
            var _utm_source = $form.find('[name=utm_source]').val();
            var _utm_medium = $form.find('[name=utm_medium]').val();
            var _have_close_credits = $form.find('[name=have_close_credits]').val();
            var _min_ball = $form.find('[name=min_ball]').val();
            var _amount = $form.find('[name=amount]').val();

            $.ajax({
                type: 'POST',
                data: {
                    action: 'add',
                    type: _type,
                    utm_source: _utm_source,
                    utm_medium: _utm_medium,
                    have_close_credits: _have_close_credits,
                    min_ball: _min_ball,
                    amount: _amount,
                },
                beforeSend: function () {
                    $('#modal_add_item button').prop('disabled', true);
                },
                success: function(resp){
                    if (!!resp.error)
                    {
                        $('#modal_add_item button').prop('disabled', false);
                        $form.find('.alert').removeClass('alert-success').addClass('alert-danger').html(resp.error).fadeIn();
                    }
                    else
                    {
                        $('#config-table').load(document.URL +  ' #config-table', function() {
                            $('#modal_add_item button').prop('disabled', false);
                            $('#modal_add_item').modal('hide');
                        });
                    }
                }
            });
        });

        //Открытие добавления стоп-фактора
        $(document).on('click', '.js-open-add-factor-modal', function(e){
            e.preventDefault();

            $('#modal_add_factor').find('.alert').hide();
            $('#modal_add_factor').find('[name=factor]').val('');
            $('#modal_add_factor').find('[name=comment]').val('');

            $('#modal_add_factor').modal();

            $('#modal_add_factor').find('[name=factor]').focus();
        });

        //Добавление стоп-фактора
        $(document).on('submit', '#form_add_factor', function(e){
            e.preventDefault();

            var $form = $(this);

            var _factor = $form.find('[name=factor]').val();
            var _comment = $form.find('[name=comment]').val();

            $.ajax({
                type: 'POST',
                data: {
                    action: 'add_factor',
                    factor: _factor,
                    comment: _comment,
                },
                beforeSend: function () {
                    $('#modal_add_factor button').prop('disabled', true);
                },
                success: function(resp){
                    if (!!resp.error)
                    {
                        $('#modal_add_factor button').prop('disabled', false);
                        $form.find('.alert').removeClass('alert-success').addClass('alert-danger').html(resp.error).fadeIn();
                    }
                    else
                    {
                        $('#factors-table').load(document.URL +  ' #factors-table', function() {
                            $('#modal_add_factor button').prop('disabled', false);
                            $('#modal_add_factor').modal('hide');
                        });
                    }
                }
            });
        });

        // Удаление стоп-фактора
        $(document).on('click', '.js-delete-factor', function(e){
            e.preventDefault();

            var $item = $(this).closest('.js-item');

            var _factor_original = $item.find('.js-text-factor').text();

            Swal.fire({
                text: "Вы действительно хотите удалить стоп-фактор `"+_factor_original+"`?",
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
                            action: 'delete_factor',
                            factor: _factor_original
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

        // Сохранение редактируемого фактора
        $(document).on('click', '.js-confirm-edit-factor', function(e){
            e.preventDefault();

            var $item = $(this).closest('.js-item');

            var _factor_original = $item.find('.js-text-factor').text();
            var _factor = $item.find('[name=factor]').val();
            var _comment = $item.find('[name=comment]').val();

            $.ajax({
                type: 'POST',
                data: {
                    action: 'update_factor',
                    factor: _factor_original,
                    new_factor: _factor,
                    comment: _comment,
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
                        $item.find('[name=factor]').val(resp.factor);
                        $item.find('.js-text-factor').html(resp.factor);

                        $item.find('[name=comment]').val(resp.comment);
                        $item.find('.js-text-comment').html(resp.comment);

                        $item.find('.js-visible-edit').hide();
                        $item.find('.js-visible-view').fadeIn();
                    }
                }
            });
        });

        $('[name="leadgid_scorista_enabled"], [name="approve_amount_settings_enabled"]').on('change', function (){
            let key = $(this).attr('name'),
                value = $(this).prop('checked') ? 1 : 0;

            updateSettings(key, value);
        });
    };

    var _init_logs_filter = function(){
        $(document).on('change', '.jsgrid-filter-row select, .jsgrid-filter-row input', app.logsFilter);
    };

    app.logsFilter = function(){
        let $_logDiv = $("#logs-grid");
        $_logDiv.addClass('data-loading');

        var $form = $('#js-logs-search');
        var _searches = {};
        $form.find('input[type=text], select').each(function(){
            if ($(this).val() != '')
            {
                _searches[$(this).attr('name')] = $(this).val();
            }
        });
        $.ajax({
            data: {
                logs_filter: _searches,
            },
            success: function(resp){
                $('#logs-grid .jsgrid-grid-body').html($(resp).find('#logs-grid .jsgrid-grid-body').html());
            }
        }).done(function () {
            $_logDiv.removeClass('data-loading');
        });
    };

    var _init_open_changelog = function(){
        $(document).on('click', '.js-open-changelog', function(e){
            e.preventDefault();

            var _id = $(this).data('id');

            if ($(this).hasClass('open'))
            {
                $(this).removeClass('open');
                $('.changelog-details').fadeOut();
            }
            else
            {
                $('.js-open-changelog.open').removeClass('open')
                $(this).addClass('open')

                $('.changelog-details').hide();
                $('#changelog_'+_id).fadeIn();
            }
        })
    }

    // Проверка как работает лидген
    $(document).on('click', '.js-check', function(e){
        let $checkButton = $(this);
        e.preventDefault();

        var _type = $('[name=check-type]').val();
        var _utm_source = $('[name=check-utm_source]').val();
        var _utm_medium = $('[name=check-utm_medium]').val();
        var _have_close_credits = $('[name=check-have_close_credits]').val();
        var _scorista_ball = $('[name=check-scorista_ball]').val();

        $.ajax({
            type: 'POST',
            data: {
                action: 'check',
                type: _type,
                utm_source: _utm_source,
                utm_medium: _utm_medium,
                have_close_credits: _have_close_credits,
                scorista_ball: _scorista_ball,
            },
            beforeSend: function () {
                $checkButton.prop('disabled', true);
                $('#check-result').html('Поиск подходящей настройки...')
            },
            complete: function() {
                $checkButton.prop('disabled', false);
            },
            success: function(resp){
                let html = '<div class="text-white"><h4>Параметры заявки для проверки</h4>';

                Object.entries(resp.request).forEach(entry => {
                    const [key, value] = entry;

                    let displayKey = key;
                    if (key in PARAMS_IN_CHECK)
                        displayKey = PARAMS_IN_CHECK[key];

                    let displayValue = value;
                    if (key === 'have_close_credits') {
                        if (value == 1)
                            displayValue = 'ПК <span class="text-muted">(1)</span>';
                        else
                            displayValue = 'НК <span class="text-muted">(0)</span>';
                    }

                    html += `<strong>${displayKey}</strong>: ${displayValue}<br>`;
                });

                if (!resp.response) {
                    html += '<h4 class="mt-2">Подходящая настройка не найдена</h4>'
                }
                else {
                    html += '<h4 class="mt-2">Найденная настройка</h4>'
                    Object.entries(resp.response).forEach(entry => {
                        const [key, value] = entry;

                        let displayKey = key;
                        if (key in PARAMS_IN_CHECK)
                            displayKey = PARAMS_IN_CHECK[key];

                        let displayValue = value;
                        if (value === '*' && (key === 'utm_source' || key === 'utm_medium')) {
                            displayValue = 'Любой';
                        }
                        else if (key === 'have_close_credits') {
                            if (value == 1)
                                displayValue = 'ПК <span class="text-muted">(1)</span>';
                            else
                                displayValue = 'НК <span class="text-muted">(0)</span>';
                        }
                        else if (key === 'amount' && value == 0) {
                            displayValue = '<span class="text-danger">Отказ по заявке</span>'
                        }

                        html += `<strong>${displayKey}</strong>: ${displayValue}<br>`;
                    });
                }


                html += '</div>';
                $('#check-result').html(html);
            }
        });

    });

    ;(function(){
        _init_events();
        _init_logs_filter();
        _init_open_changelog();
    })();
};
$(function(){
    new ScoristaLeadgidSettingsApp();
})
