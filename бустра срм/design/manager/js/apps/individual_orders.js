function IndividualOrdersApp()
{
    var app = this;
    
    var _init = function(){

        _init_pagination();
        _init_sortable();
        _init_filter();
        
        _init_mango_call();
                
        _init_accept_loan();
        
        _init_filters();
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
                url: '/individual_order/'+_order_id,
                data: {
                    action: 'accept',
                    order_id: _order_id
                },
                beforeSend: function(){
                    $btn.addClass('loading');
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
            })
        });
    };
    

    var _init_filters = function(){
        
        $(document).on('click', '.js-filter-status a', function(e){
            e.preventDefault();
            
            var _link = $(this).attr('href');
            
            app.load(_link);
        });
        
    }
    
    ;
    
    var _init_mango_call = function(){
        
        
        
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

        $.ajax({
            data: {
                search: _searches,
                sort: _sort,
                status: filter_status,
                client: filter_client
            },
            beforeSend: function(){
            },
            success: function(resp){
                
                $('#basicgrid .jsgrid-grid-body').html($(resp).find('#basicgrid .jsgrid-grid-body').html());
                $('#basicgrid .jsgrid-pager-container').html($(resp).find('#basicgrid .jsgrid-pager-container').html() || '');
                $('#basicgrid .jsgrid-header-row').html($(resp).find('#basicgrid .jsgrid-header-row').html());
                
                $('.js-filter-status').html($(resp).find('.js-filter-status').html());
                
            }
        })
    
    };
    
    app.load = function(_url, loading){
        
        var _split = _url.split('?');
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
                
                if (!!_split[1])
                    location.hash = _split[1];
                
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
    
    ;(function(){
        
        _init();
        
        if (location.hash != '#' && location.hash != '')
        {
            app.load((location.href).replace('#', '?'));
        }
        
    })();
};

new IndividualOrdersApp();