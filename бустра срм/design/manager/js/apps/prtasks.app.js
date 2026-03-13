function PrTasksApp()
{
    var app = this;

    var _init_datepicker = function(){
        $('.singledate').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'DD.MM.YYYY'
            },
        });

        // $('.js-recall').daterangepicker({
        //     singleDatePicker: true,
        //     showDropdowns: true,
        //     timePicker: true,
        //     timePickerIncrement: 15,
        //     locale: {
        //         format: 'DD.MM.YYYY hh:mm:ss'
        //     }
        // });


        $('.js-perspective').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            timePicker: true,
            timePickerIncrement: 15,
            locale: {
                format: 'DD.MM.YYYY hh:mm:ss'
            }
        });

    }

    var _init_toggle_status = function(){

        $(document).on('click', '.js-toggle-status-perspective', function(e){
            e.preventDefault();

            var task_id = $(this).data('task');

            $('#form_perspective [name=task_id]').val(task_id);
            $('#form_perspective [name=text]').text('').val('');

            $('#modal_perspective').modal();
        });

        $(document).on('submit', '#form_perspective', function(e){
            e.preventDefault();

            var $form = $(this);

            $.ajax({
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function(){

                },
                success: function(resp){
                    $('#modal_perspective').modal('hide');
                    $('#main_'+$form.find('[name=task_id]').val()).fadeOut();
                }
            });
        });

        $(document).on('click', '.js-toggle-status-recall', function(e){
            e.preventDefault();

            var task_id = $(this).data('task');

            $('#form_recall [name=task_id]').val(task_id);
            $('#form_recall [name=text]').text('').val('');

            $('#modal_recall').modal();
        });

        $(document).on('submit', '#form_recall', function(e){
            e.preventDefault();

            var $form = $(this);
            let task_id = $form.serializeArray()[0]['value'];
            $.ajax({
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function(){

                },
                success: function(resp){
                    $('#modal_recall').modal('hide');
                    if (resp.exists){
                        alert('Номер уже добавлен в дайлер')
                    }
                    var _id = $form.find('[name=task_id]').val();
                    $('#main_'+_id).html($(resp).find('#main_'+_id).html());

                    $('.js-status-'+task_id+" .dropdown-toggle").val('Перезвон')
                    $('.js-status-'+task_id+" .dropdown-toggle").css('background','#7460ee')
                    $('.js-status-'+task_id+" .dropdown-toggle").css('border','#7460ee')
                    $('.js-status-'+task_id+" .dropdown-toggle").html('Перезвон')
                    $('.js-status-'+task_id+' .dropdown-menu .js-toggle-status-recall').remove()
                    $('.js-recall').prop('checked', false);
                }
            });
        });

        $(document).on('click', '.js-toggle-status', function(e){
            e.preventDefault();

            var task_id = $(this).data('task');
            var status = $(this).data('status');

            $.ajax({
                type: 'POST',
                data: {
                    action: 'status',
                    status: status,
                    task_id: task_id
                },
                beforeSend: function(){

                },
                success: function(resp){
                    $('.js-status-'+task_id).html($(resp).find('.js-status-'+task_id).html());
                    if (status == 2 || status == 4)
                    {
                        $('#main_'+task_id).fadeOut();
                        $('#changelog_'+task_id).remove();
                    }
                }
            })

        });
    };

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
//        $(document).on('blur', '.jsgrid-filter-row input', app.filter);
        $(document).on('keyup', '.jsgrid-filter-row input', app.filter);
        $(document).on('change', '.jsgrid-filter-row select', app.filter);
    };

    app.filter = function(){
        var $form = $('#search_form');
        var _sort = $form.find('[name=sort]').val()
        var _searches = {};
        $form.find('input[type=text], select').each(function(){
            if ($(this).val() != '')
            {
                _searches[$(this).attr('name')] = $(this).val();
            }
        });
        $.ajax({
            data: {
                search: _searches,
                sort: _sort
            },
            beforeSend: function(){
            },
            success: function(resp){

                $('#basicgrid .jsgrid-grid-body').html($(resp).find('#basicgrid .jsgrid-grid-body').html());
                $('#basicgrid .jsgrid-pager-container').html($(resp).find('#basicgrid .jsgrid-pager-container').html());
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

    var _init_open_order = function(){
        $(document).on('click', '.js-open-order', function(e){
            e.preventDefault();

            var _id = $(this).data('id');
            var _number = $(this).data('number');
            var _site_id = $(this).data('site-id');
            var _uid = $(this).data('uid');

            if ($(this).hasClass('open'))
            {
                $(this).removeClass('open');
                $('.order-details').fadeOut();
            }
            else
            {
                $('.js-open-order.open').removeClass('open')
                $(this).addClass('open')

                $('.order-details').hide();
                $('#changelog_'+_id).fadeIn();

                app.load_comments(_id, _uid, _site_id);

            }
        })
    }

    app.load_comments = function(task_id, uid, site_id){
        $.ajax({
            url: 'http://manager.boostra.ru/ajax/get_info.php',
            data: {
                uid: uid,
                site_id: site_id,
                action: 'comments',
            },
            success: function(resp){
                var _comments = '';
                if (!!resp.blacklist)
                    $.each(resp.blacklist, function(k, item){
                        _comments += '<div class="d-flex flex-row comment-row">';
                        _comments += '    <div class="comment-text w-100">';
                        _comments += '        <p class="mb-1">'+item.text+'</p>';
                        _comments += '        <div class="comment-footer">';
                        _comments += '            <span class="text-muted float-right">'+item.created+'</span>';
                        _comments += '            <span class="label label-light-danger">Черный список</span>';
                        _comments += '        </div>';
                        _comments += '    </div>';
                        _comments += '</div>';

                    });
                if (!!resp.comments)
                    $.each(resp.comments, function(k, item){
                        _comments += '<div class="d-flex flex-row comment-row">';
                        _comments += '    <div class="comment-text w-100 rounded">';
                        _comments += '        <p class="mb-1">'+item.text+'</p>';
                        _comments += '        <div class="comment-footer">';
                        _comments += '            <span class="text-muted float-right">'+item.created+'</span>';
                        _comments += '            <span class="label label-light-info">'+item.block+'</span>';
                        _comments += '        </div>';
                        _comments += '    </div>';
                        _comments += '</div>';

                    })

                $('#changelog_'+task_id+' .js-comments').html(_comments)
            }
        });

    }

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

            var order_id = $('#form_add_comment [name=order_id]').val($(this).data('order'));

            var url = '/order/'+order_id;

            var task_id = $(this).find('[name=task_id]').val();
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
                            type: 'success',
                        });

                        setTimeout(function(){
                            app.load_comments(_id, _uid);
                        }, 1000);
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

    var _init_calc = function(){

        $(document).on('submit', '.js-calc-form', function(e){
            e.preventDefault();

            var $form = $(this);

            var _input = {};

            _input.payment_date = $form.find('.js-calc-payment-date').val();
            _input.calc_date = $form.find('.js-calc-input').val();

            _input.zaim_summ = $form.find('.js-calc-zaim-summ').val();
            _input.percent = $form.find('.js-calc-percent').val();
            _input.peni = 20;
            _input.ostatok_od = $form.find('.js-calc-ostatok-od').val();
            _input.ostatok_percents = $form.find('.js-calc-ostatok-percents').val();
            _input.ostatok_peni = $form.find('.js-calc-ostatok-peni').val();
            _input.allready_added = $form.find('.js-calc-allready-added').val();
            _input.prolongation_summ_insurance = $form.find('.js-calc-prolongation-summ-insurance').val();
            _input.prolongation_summ_sms = $form.find('.js-calc-prolongation-summ-sms').val();
            _input.prolongation_summ_cost = $form.find('.js-calc-prolongation-summ-cost').val();


            var d_now = new Date();
            var d_payment = new Date(_input.payment_date);
            var d_calc = new Date()
            var calc_date_parse = _input.calc_date.split('.')
            d_calc.setDate(calc_date_parse[0]);
            d_calc.setMonth(calc_date_parse[1] - 1);
            d_calc.setFullYear(calc_date_parse[2]);

            var period = inDays(d_now, d_calc);

            new_percent = parseFloat(_input.ostatok_percents) + parseFloat(_input.ostatok_od / 100 * period * _input.percent);

            var $res = '<ul>';
            $res += '<li>Проценты: '+new_percent+'</li>';
            $res += '<li>Основной долг: '+_input.ostatok_od+'</li>';
            $res += '<ul>';

            $('.js-calc-result').html($res);

console.log();


        });

        var inDays = function(d1, d2) {
            var t2 = d2.getTime();
            var t1 = d1.getTime();

            return parseInt((t2-t1)/(24*3600*1000));
        };
    };

    var _init_send_sms = function(){

        $(document).on('click', '.js-open-sms-modal', function(e){
            e.preventDefault();

            var _user_id = $(this).data('user');

            $('#modal_send_sms [name=user_id]').val(_user_id)
            $('#modal_send_sms').modal();
        });

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

                    setTimeout(function(){
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

                    }, 600)
                },
            })
        });
    };

    ;(function(){
        _init_pagination();
        _init_sortable();
        _init_filter();
        _init_open_order();
        _init_comment_form();
        _init_toggle_status();
        _init_datepicker();
        _init_calc();
        _init_send_sms();
    })();
}
$(function(){
    new PrTasksApp();
});
