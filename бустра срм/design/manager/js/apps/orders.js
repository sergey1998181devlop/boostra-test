function OrdersApp()
{
    var app = this;
    
    var _init = function(){
        _init_open_order();
        _init_pagination();
        _init_sortable();
        _init_filter();
        
        _init_mango_call();
        _init_scorista_run();
        
        _init_image_rotate();
        
        _init_accept_loan();
        
        _init_filters();
        initSaveFieldForm();
//        _init_open_image_popup();
    };
    
    
    var _init_accept_loan = function(){
        $(document).on('click', '.js-accept-order-list', function(e){
            e.preventDefault();
            
            var $btn = $(this);
            var $row = $btn.closest('.js-order-row');
            
            var _order_id = $(this).data('order');
            var _login = $(this).data('manager');

            if ($btn.hasClass('loading'))
                return false;
    
            $.ajax({
                type: 'POST',
                url: '/order/'+_order_id,
                data: {
                    action: 'accept',
                    order_id: _order_id
                },
                beforeSend: function(){
                    $btn.addClass('loading');
                    $btn.prop('disabled', true);
                },
                success: function(resp){
                    if (!!resp.error)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                    }
                    else if (!!resp.success)
                    {
                        
                        $btn.hide();
                        $('.js-order-manager-'+_order_id).html(_login)
                        $row.find('.js-order-notaccepted').hide();
                        $row.find('.js-order-accepted').fadeIn();
                        
                        Swal.fire({
                            timer: 5000,
                            text: resp.success,
                            type: 'success',
                        });
                    }
                    else
                    {
                        console.info(resp);
                    }
                }
            }).done(function () {
                $btn.prop('disabled', false);
                $btn.removeClass('loading');
            })
        });
    };
    

    var _init_filters = function(){
        
        $(document).on('click', '.js-filter-status a:not(.js-site-tab-filter)', function(e){
            e.preventDefault();

            var _link = $(this).attr('href');

            app.load(_link, true, (resp) => {
                $('.js-count-order-page').html($(resp).find('.js-order-row').length || 0);
            });
        });
        
    }
    
    var _init_image_rotate = function(){
        $(document).on('click', '.mpf-rotate', function(e){
            e.preventDefault();
            e.stopPropagation();
            
            var $img = $('.mpf-img');
            if ($img.hasClass('rotate90'))
            {
                $img.removeClass('rotate90').addClass('rotate180');
            }
            else if ($img.hasClass('rotate180'))
            {
                $img.removeClass('rotate180').addClass('rotate270');
            }
            else if ($img.hasClass('rotate270'))
            {
                $img.removeClass('rotate270');
            }
            else
            {
                $img.addClass('rotate90');
            }
        });
    }
    
    var _init_scorista_run = function(){
        $(document).on('click', '.js-scorista-run', function(e){
            
            var _order_id = $(this).data('order')
            
            $.ajax({
                url: 'ajax/scorista.php',
                data: {
                    action: 'create',
                    order_id: _order_id
                },
                beforeSend: function(){
                    
                },
                success: function(resp){

                    if (!!resp.status)
                    {
                        if (resp.status == 'ERROR')
                        {
                            Swal.fire(
                                'Ошибка',
                                'Не достаточно данных для проведения скоринг-тестов.',
                                'error'
                            )
                        }
                        else
                        {
                            
                            Swal.fire(
                                'Запрос отправлен',
                                'Время получения ответа от 30 секунд до 2 минут.<br />Идентификатор запроса: '+resp.requestid,
                                'success'
                            );
                            setTimeout(function(){
                                _scorista_get_result(resp.requestid);
                            }, 15000);
                            
                        }
                    }
                    else
                    {
                        Swal.fire(
                            'Ошибка',
                            '',
                            'error'
                        )
                    }
                }
            })
            
        })
    };
    
    var _scorista_get_result = function(request_id){
        $.ajax({
            url: 'ajax/scorista.php',
            data: {
                action: 'result',
                request_id : request_id
            },
            beforeSend: function(){
                
            },
            success: function(resp){
                if (!!resp.status)
                {
                    if (resp.status == 'ERROR')
                    {
                        Swal.fire(
                            'Ошибка',
                            resp.error.message,
                            'error'
                        );
                    }
                    else if (resp.status == 'DONE')
                    {
                        if (resp.data.decision.decisionName == 'Отказ')
                        {
                            Swal.fire(
                                'Скоринг тест завершен',
                                'Результат: Отказ<br />Скорбалл: '+resp.data.additional.summary.score,
                                'warning'
                            );
                        }
                        else
                        {
                            Swal.fire(
                                'Скоринг тест завершен',
                                'Результат: '+resp.data.decision.decisionName+'<br />Скорбалл: '+resp.data.additional.summary.score,
                                'success'
                            );
                        }
                        
                    }
                    else
                    {
                        setTimeout(function(){
                            _scorista_get_result(request_id);
                        }, 5000);
                    }
                }
                else
                {
                    
console.log(resp);   
                }             
            }
        })
    };

    var _init_mango_call = function(){
        
        
        
    };
    
    var _init_open_order = function(){
        $(document).on('click', '.js-open-order', function(e){
            e.preventDefault();
            
            if ($(this).hasClass('open'))
            {
                $(this).removeClass('open')
                $('.order-details').remove();
            }
            else
            {
                $(this).addClass('open')
                
                var _id = $(this).data('id');
                var _row = $(this).closest('.jsgrid-row');
                
                $('.order-details').remove();
                
                $.ajax({
                    url: 'order/'+_id,
                    data: {
                        ajax: 1
                    },
                    beforeSend: function(){
                        
                    },
                    success: function(resp){
                        _row.after('<tr class="order-details"><td colspan="12"></td></tr>');
                        $('.order-details td').html($(resp).find('#order_wrapper'));
                        
                        
                        
                        new OrderApp()
                    }
                })
            }
        })
    }
    
    var _init_pagination = function(){
        $(document).on('click', '.jsgrid-pager a', function(e){
            e.preventDefault();
            
            var _url = $(this).attr('href');
            
            app.load(_url, true, (resp) => {
                $('.js-count-order-page').html($(resp).find('.js-order-row').length || 0);
            });
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
        if ($('#not-received-loans-span')){
            $(document).on('blur', '.jsgrid-filter-row input', app.filter);
        }else {
            $(document).on('keyup', '.jsgrid-filter-row input', app.filter);
        }
        $(document).on('change', '.jsgrid-filter-row select', app.filter);
    };
    
    app.filter = function(){
        var $form = $('#search_form');
        var _sort = $form.find('[name=sort]').val()
        var _searches = {};
        $form.find('input[type=text]').each(function(){
            if ($(this).val() != '')
            {
                _searches[$(this).attr('name')] = $(this).val();
            }
        });     
        $form.find('select').each(function(){
            if ($(this).val() != '')
            {
                _searches[$(this).attr('name')] = $(this).val();
            }
        });     
        var filter_status = $('#filter_status').val() || '';
        var filter_client = $('#filter_client').val() || '';

        var site_id = $('.js-site-tab-filter.active').attr('data-site') || $('body').attr('data-site_id');
        if (site_id === 'all') {
            site_id = null;
        }

        $.ajax({
            data: {
                search: _searches,
                sort: _sort,
                status: filter_status,
                client: filter_client,
                site_id: site_id,
            },
            beforeSend: function(){
                $('.jsgrid-load-shader').show();
                $('.jsgrid-load-panel').show();
            },
            success: function(resp){
                
                $('#basicgrid .jsgrid-grid-body').html($(resp).find('#basicgrid .jsgrid-grid-body').html());
                $('#basicgrid .jsgrid-pager-container').html($(resp).find('#basicgrid .jsgrid-pager-container').html() || '');
                $('#basicgrid .jsgrid-header-row').html($(resp).find('#basicgrid .jsgrid-header-row').html());
                
                $('.js-filter-status').html($(resp).find('.js-filter-status').html());
                
            }
        }).done(function () {
            $('.jsgrid-load-shader').hide();
            $('.jsgrid-load-panel').hide();
        })
    
    };
    
    app.load = function(_url, loading, callback = false){

        // Обработка URL для удаления дублирующихся параметров
        if (_url.includes('?')) {
            var urlParts = _url.split('?');
            var baseUrl = urlParts[0];
            var queryString = urlParts[1];

            if (queryString) {
                var params = new URLSearchParams(queryString);
                var uniqueParams = new URLSearchParams();

                // Перебираем параметры и оставляем только последние значения
                params.forEach((value, key) => {
                    uniqueParams.set(key, value);
                });

                _url = baseUrl + '?' + uniqueParams.toString();
            }
        }


        history.pushState(null, '', _url);
       var _split = _url.split('?');
       // Проверяем наличие параметра site_id в URL и устанавливаем его в body
       if (_split[1]) {
           var params = new URLSearchParams(_split[1]);
           var siteId = params.get('site_id');
           if (siteId) {
               $('body').attr('data-site_id', siteId);
           }
       }

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

                $('#basicgrid .jsgrid-grid-body').html($(resp).find('#basicgrid .jsgrid-grid-body').html());
                $('#basicgrid .jsgrid-pager-container').html($(resp).find('#basicgrid .jsgrid-pager-container').html() || '');
                $('#basicgrid .jsgrid-header-row').html($(resp).find('#basicgrid .jsgrid-header-row').html());

                $('.js-filter-status').html($(resp).find('.js-filter-status').html());
                
                if (loading)
                {
                    $('html, body').animate({
                        scrollTop: $("#basicgrid").offset().top-80  
                    }, 1000);
                    
                    $('.jsgrid-load-shader').hide();
                    $('.jsgrid-load-panel').hide();
                }

                if(callback) {
                    callback(resp);
                }
                
            }
        })
    };

    let initSaveFieldForm = function () {
        $(document).on('change', '.field-to-save', function () {
            $(this).parents('form.common_save_order_field_form').submit();
        })

        $(document).on('submit', '.common_save_order_field_form', function (e) {
            e.preventDefault();

            let $form = $(this);
            let $fieldToSave = $form.find('.field-to-save');

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
                success: function (resp) {
                    if (resp.success) {
                        Swal.fire({
                            timer: 5000,
                            title: 'Изменения сохранены',
                            type: 'success'
                        });
                    } else {
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
        
        _init();
        
        if (location.hash != '#' && location.hash != '')
        {
            app.load((location.href).replace('#', '?'));
        }
        
    })();
};

new OrdersApp();