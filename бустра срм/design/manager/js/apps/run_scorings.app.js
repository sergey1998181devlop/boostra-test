;function RunScoringsApp()
{
    var app = this;
    
    app.timeout;
    
    var _init_run_link = function(){
        $(document).on('click', '.js-run-scorings', function(e){
            e.preventDefault();
            
            
            var $this = $(this);
            
            if ($this.hasClass('btn-loading'))
                return false;

            var order_id = $(this).data('order');
            var type = $(this).data('type');
            var important = $(this).hasClass('js-important-scoring') ? 1 : 0;
            
            $.ajax({
                url: 'ajax/run_scorings.php',
                data: {
                    'order_id': order_id,
                    'type': type,
                    'action': 'create',
                    'important': important,
                },
                dataType: 'json',
                beforeSend: function(){
                    $this.html('Загрузка ...').addClass('btn-loading');
                },
                success: function(resp){
                    if (!!resp.success)
                    {
                        $this.html('Ожидание').removeClass('btn-loading').removeClass('btn-outline-success').addClass('btn-outline-warning');
                        $('.js-scorings-block').addClass('js-need-update')
                        _init_scorings_block();
                        
                    }
                    else if(!!resp.error && type == 'scorista')
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                        $this.html('Запустить повторно').addClass('js-important-scoring').removeClass('btn-loading');
                    }
                    else if(!!resp.error)
                    {
                        Swal.fire({
                            timer: 5000,
                            title: 'Ошибка!',
                            text: resp.error,
                            type: 'error',
                        });
                        $this.html('Запустить').removeClass('btn-loading');
                    }
                }
            })
        });
    };
    
    
    var _init_scorings_block = function(){
        clearTimeout(app.timeout);
        if ($('.js-scorings-block').hasClass('js-need-update'))
        {
            var _order_id = $('.js-scorings-block').data('order');
            app.timeout = setTimeout(function(){
                update_scoring_block(_order_id);
            }, 10000);
        }
    };
    
    var update_scoring_block = function(_order_id){
        if (typeof load_scorings === 'function') {
            load_scorings(_order_id);
        } else {
            $.ajax({
                url: 'order/'+_order_id+'?open_scorings=1',
                success: function(resp){
                    $('.js-scorings-block').html($(resp).find('.js-scorings-block').html());
                    if (!$(resp).find('.js-scorings-block').hasClass('js-need-update'))
                    {
                        $('.js-scorings-block').removeClass('js-need-update')
                    }
                    _init_scorings_block()
                }
            })
        }
    };
    
    window._init_scorings_block = _init_scorings_block;
    this._init_run_link = _init_run_link;
    
    ;(function(){
        _init_run_link();
        _init_scorings_block();
    })();
};

$(function(){
    new RunScoringsApp();
});