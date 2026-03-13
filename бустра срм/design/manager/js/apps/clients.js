function ClientsApp()
{
    var app = this;

    var _init_pagination = function(){
        $(document).on('click', '.jsgrid-pager a', function(e){
            e.preventDefault();

            var _url = $(this).attr('href');

            app.load(_url, true);
        });
    };

    var _init_sortable = function(){
        $(document).on('click', '.jsgrid-header-sortable a', function(e){
            e.preventDefault();

            var _url = $(this).attr('href');

            app.load(_url, true);
        });
    };

    var _init_filter = function(){
        $(document).on('blur', '.jsgrid-filter-row input', app.filter);
        //$(document).on('keyup', '.jsgrid-filter-row input', app.filter);
        $(document).on('change', '.jsgrid-filter-row select', app.filter);
    };

    app.filter = function(){
        var _url = window.location.href;
        var $form = $('#search_form');
        var _sort = $form.find('[name=sort]').val();
        var _searches = {};
        $form.find('input[type=text], select').each(function(){
            if ($(this).val() != '')
            {
                _searches[$(this).attr('name')] = $(this).val();
            }
        });
        $.ajax({
            url: _url,
            data: {
                search: _searches,
                sort: _sort
            },
            beforeSend: function(){
                var preloaderTable = $('.preloader-table');
                if (preloaderTable.length) {
                    preloaderTable.show();
                } else {
                    $('.preloader').show();
                }
            },
            success: function(resp){

                $('#basicgrid .jsgrid-grid-body').html($(resp).find('#basicgrid .jsgrid-grid-body').html());
                $('#basicgrid .jsgrid-pager-container').html($(resp).find('#basicgrid .jsgrid-pager-container').html());

                $('.preloader, .preloader-table').hide();
            }
        })

    };

    app.load = function(_url, loading){
        $.ajax({
            url: _url,
            beforeSend: function(){
                if (loading)
                {
                    $('.jsgrid-load-shader').show();
                    $('.jsgrid-load-panel').show();
                }
            },
            success: function(resp){

                $('#basicgrid').html($(resp).find('#basicgrid').html());

                if (loading)
                {
                    $('html, body').animate({
                        scrollTop: $("#basicgrid").offset().top-80
                    }, 1000);

                    $('.jsgrid-load-shader').hide();
                    $('.jsgrid-load-panel').hide();
                }

            }
        })
    };

    var _init_comment_form = function(){

        $(document).on('click', '.js-open-comment-form', function(e){
            e.preventDefault();

            $('#form_add_comment [name=order_id]').val($(this).data('order'));
            $('#form_add_comment [name=user_id]').val($(this).data('user'));
            $('#form_add_comment [name=task_id]').val($(this).data('task'));
            $('#form_add_comment [name=uid]').val($(this).data('uid'));
            $('#form_add_comment [name=text]').text('').val('');

            $('#modal_add_comment').modal();
        });

        $(document).on('submit', '#form_add_comment', function(e){
            e.preventDefault();

            var $form = $(this);

            var uid = $(this).find('[name=uid]').val();

            $.ajax({
                url: $form.attr('action'),
                data: $form.serialize(),
                type: 'POST',
                success: function(resp){
                    if (resp.success)
                    {
                        $('#modal_add_comment').modal('hide');

                        Swal.fire({
                            timer: 5000,
                            title: 'Комментарий добавлен.',
                            type: 'success'
                        }).then(function() {
                            app.filter();
                        });
                    }
                    else
                    {
                        Swal.fire({
                            text: resp.error,
                            type: 'error',
                        });

                    }
                }
            })
        })
    }



    var _init_send_sms = function(){

        $(document).on('click', '.js-send-sms', function(e){
            e.preventDefault();

            $('.js-sms-form [name=type]').val('sms')
            $('.js-sms-form').submit();
        });
        $(document).on('click', '.js-send-viber', function(e){
            e.preventDefault();

            $('.js-sms-form [name=type]').val('viber')
            $('.js-sms-form').submit();
        });
        $(document).on('click', '.js-send-whatsapp', function(e){
            e.preventDefault();

            $('.js-sms-form [name=type]').val('whatsapp')
            $('.js-sms-form').submit();
        });

        $(document).on('click', '.js-open-sms-modal', function(e){
            e.preventDefault();

            var _user_id = $(this).data('user');

            $('#modal_send_sms [name=user_id]').val(_user_id)
            $('#modal_send_sms').modal();
        });

        $(document).on('submit', '.js-sms-form', function(e){
            e.preventDefault();

            var $form = $(this);

            var _user_id = $form.find('[name=user_id]').val();

            if ($form.hasClass('loading'))
                return false;


            $.ajax({
                url: 'client/'+_user_id,
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function(){
                    $form.addClass('loading')
                },
                success: function(resp){
                    $form.removeClass('loading');
                    $('#modal_send_sms').modal('hide');

                    if (!!resp.error)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                    }
                    else
                    {
                        Swal.fire({
                            timer: 5000,
                            title: '',
                            text: 'Сообщение отправлено',
                            type: 'success',
                        });
                    }
                },
            })
        });
    };

    var _init_save_field_form = function() {
        $(document).on('change', '.field-to-save', function(){
            $(this).parents('form.common_save_field_form').submit();
        })

        $(document).on('submit', '.common_save_field_form', function(e){
            e.preventDefault();

            var $form = $(this);
            var $fieldToSave = $form.find('.field-to-save');

            if ($fieldToSave.length === 0) {
                return;
            }

            $.ajax({
                url: $form.attr('action'),
                data: {
                    action: 'save_select',
                    field: $fieldToSave.attr('name'),
                    value: $fieldToSave.val()
                },
                type: 'POST',
                success: function(resp){
                    if (resp.success)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Изменения сохранены',
                            type: 'success'
                        });
                    }
                    else
                    {
                        Swal.fire({
                            text: resp.error,
                            type: 'error'
                        });
                    }
                }
            })
        })
    }


    ;(function(){
        _init_pagination();
        _init_sortable();
        _init_filter();
        _init_comment_form();
        _init_send_sms();
        _init_save_field_form();
    })();
}
new ClientsApp();
