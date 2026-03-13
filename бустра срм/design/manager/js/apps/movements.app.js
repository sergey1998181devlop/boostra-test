;function MovementsApp()
{
    var app = this;
    
    app.myModal;
    
    var _init = function(){
        
        var options = {
            
        };
        app.myModal = new bootstrap.Modal(document.getElementById('loan_operations'), options)
        
        $(document).on('click', '#loan_operations .btn-close', function(e){
            app.myModal.hide();
            
        });
        
        $(document).on('click', '.js-get-movements', function(e){
            e.preventDefault();
            
            var $btn = $(this);
            var _number = $btn.data('number');

            if ($btn.hasClass('loading'))
                return false;
                 
            var no_peni = $btn.hasClass('js-no-peni');
            
            $.ajax({
                url: 'ajax/get_info.php',
                data: {
                    action: 'movements',
                    number: _number
                },
                beforeSend: function(){
                    $btn.addClass('loading');
                },
                success: function(resp){
                    console.log(resp);
                    
                    var _html = create_html(resp, no_peni);
                    
                    var _title = 'Операции по договору '+_number;
                    
                    display_response(_html, _title);
                    
                    $btn.removeClass('loading');
                }
            })
        })
        
    };
    
    var create_html = function(resp, no_peni){
        var _html = '';
        
        if (resp.length > 0)
        {
            _html += '<div class="container-fluid p-0">';
            _html += '<div class="row">';
            _html += '<div class="col-12">';
            _html += '<div class="card">';
            _html += '<div class="card-body p-0">';
            _html += '<table class="table table-hover">';
            _html += '<tr>';
            _html += '<th class="text-center"><small>Дата</small></th>';
            _html += '<th class="text-center"><small>Начальный Остаток</small></th>';
            _html += '<th class="text-center"><small>Начислено ОД</small></th>';
            _html += '<th class="text-center"><small>Оплачено ОД</small></th>';
            _html += '<th class="text-center"><small>Начислено Процент</small></th>';
            _html += '<th class="text-center"><small>Оплачено Процент</small></th>';
            if (!no_peni)
            {
                _html += '<th class="text-center"><small>Начислено Пени</small></th>';
                _html += '<th class="text-center"><small>Оплачено Пени</small></th>';
                _html += '<th class="text-center"><small>Начислено Ответ-ть</small></th>';
                _html += '<th class="text-center"><small>Оплачено Ответ-ть</small></th>';
            }
            _html += '<th class="text-center"><small>Конечный Остаток</small></th>';
            _html += '<th class="text-center"><small>Условная Оплата</small></th>';
            _html += '</tr>';
            $.each(resp, function(k, item){
                _html += '<tr>';
                _html += '<td class="text-center pt-1 pb-1">'+item.date+'</td>';
                _html += '<td class="text-center pt-1 pb-1">'+item.start_total_summ+'</td>';
                _html += '<td class="text-center pt-1 pb-1">'+item.added_body_summ+'</td>';
                _html += '<td class="text-center pt-1 pb-1">'+item.paid_body_summ+'</td>';
                _html += '<td class="text-center pt-1 pb-1">'+item.added_percents_summ+'</td>';
                _html += '<td class="text-center pt-1 pb-1">'+item.paid_percents_summ+'</td>';
                if (!no_peni)
                {
                    _html += '<td class="text-center pt-1 pb-1">'+item.added_peni_summ+'</td>';
                    _html += '<td class="text-center pt-1 pb-1">'+item.paid_peni_summ+'</td>';
                    _html += '<td class="text-center pt-1 pb-1">'+item.added_charge_summ+'</td>';
                    _html += '<td class="text-center pt-1 pb-1">'+item.paid_charge_summ+'</td>';
                }
                _html += '<td class="text-center pt-1 pb-1">'+item.finish_total_summ+'</td>';
                _html += '<td class="text-center pt-1 pb-1">'+(item.conditional ? 'Да' : 'Нет')+'</td>';
                _html += '</tr>';
            });
            _html += '</table>';
            _html += '</div>';
            _html += '</div>';
            _html += '</div>';
            _html += '</div>';
            _html += '</div>';
        }
        else
        {
            _html += '<h3 class="text-danger>Нет доступных операций</h3>';
        }
        
        return _html;
    };
    
    var display_response = function(_html, _title){
        
        $('#loan_operations_title').html(_title);
        
        $('#loan_operations .modal-body').html(_html);
        
        app.myModal.show()
    }
    
    ;(function(){
        _init();
    })();
};
$(function(){
    new MovementsApp();
})