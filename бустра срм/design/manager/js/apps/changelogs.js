function ChangelogsApp()
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
        $(document).on('keyup', '.jsgrid-filter-row input', app.filter);
        $(document).on('change', '.jsgrid-filter-row select', app.filter);
    };
    
    app.filter = function(){
        let $_logDiv = $("#logs");
        $_logDiv.addClass('data-loading');

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
        }).done(function () {
            $_logDiv.removeClass('data-loading');
        });
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
            }
        })
    }

    
    ;(function(){
        _init_pagination();
        _init_sortable();
        _init_filter();
        _init_open_order();
    })();
}
new ChangelogsApp();