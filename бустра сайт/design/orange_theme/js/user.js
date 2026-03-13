$(document).ready(function(){
	
    $('.js-individual-pay').click(function(e){
        var order_id = $(this).data('order')
        $.ajax({
            url: '/ajax/payment.php?action=create_transaction_ip&order_id='+order_id,
            success: function(resp){
                if(resp.error) {
                    alert(resp.error)
                } else {
                    location.href = resp.PaymentURL
                }
            }
        })
    })
    
    // cdoctor открытие модального окна
    $('.js-cdoctor-modal-open').click(function(){
   	    $.magnificPopup.open({
    		items: {
    			src: '#cdoctor_modal'
    		},
    		type: 'inline',
            showCloseBtn: true
    	});

    })

    // события нажатия кнопок оплаты
    $('.js-save-click').click(function(e){
        var _user_id = $(this).data('user');
        var _event = $(this).data('event');
        
        $.ajax({
            url: 'ajax/events.php',
            data: {
                user_id: _user_id,
                event: _event
            },
            success: function(){
                return true;
            }
        });
        
    });
    
    // удаление кабинета
    $('#remove_account').click(function(e){
        e.preventDefault();
        
       	$.magnificPopup.open({
    		items: {
    			src: '#confirm_remove_account'
    		},
    		type: 'inline',
            showCloseBtn: true
    	});
    });
    $('#close_modal_remove').click(function(){
        $.magnificPopup.close();
    });
    $('#confirm_remove').click(function(){
        location.href = '/user/delete_account';
    })
    
    // рекурентные платежи
    $('#service_recurent_check').live('change', function(){
        var _check = $(this).is(':checked') ? 1 : 0;
        $('#repeat_loan_form [name=service_recurent]').val(_check);
    });
    
    // смс информирование
    $('#service_sms_check').live('change', function(){
        var _check = $(this).is(':checked') ? 1 : 0;
        $('#repeat_loan_form [name=service_sms]').val(_check);
    });
    
    // страховка
    var _click_counter = 9;
    
    $('#service_insurance_check').live('change', function(){
        let is_new_client = $("input[name='is_new_client']").val();
        if (_click_counter > 0 && is_new_client != 1)
        {
            $('#service_insurance_check').attr('checked', true);
            _click_counter--;
        }
        else
        {
            var _check = $(this).is(':checked') ? 1 : 0;

            $('#repeat_loan_form [name=service_insurance]').val(_check);            
        }
    });
    
    // причина отказа
    $('#service_reason_check').live('change', function(){
        var _check = $(this).is(':checked') ? 1 : 0;
        $('#repeat_loan_form [name=service_reason]').val(_check);
    });

    // кредитный доктор
    $('#service_doctor_check').live('change', function(){
        var _check = $(this).is(':checked') ? 1 : 0;
        $('#repeat_loan_form [name=service_doctor]').val(_check);
    });

    $('.js-close-autodebit').click(function(e){
        $.magnificPopup.close();
    });
    
    $('#repeat_loan_form').submit(function(e){
        
        var $form = $(this);
        
        if ($form.hasClass('loading'))
            return false;
            
        /** перепроверяем галочки **/        
        // рекурентные платежи
        var _recurent_check = $('#service_recurent_check').is(':checked') ? 1 : 0;
        $('#repeat_loan_form [name=service_recurent]').val(_recurent_check);
        // смс информирование
        var _sms_check = $('#service_sms_check').is(':checked') ? 1 : 0;
        $('#repeat_loan_form [name=service_sms]').val(_sms_check);
        // страховка
        var _insurance_check = $('#service_insurance_check').is(':checked') ? 1 : 0;
        $('#repeat_loan_form [name=service_insurance]').val(_insurance_check);
        // причина отказа
        var _reason_check = $('#service_reason_check').is(':checked') ? 1 : 0;
        $('#repeat_loan_form [name=service_reason]').val(_reason_check);
        // кредитный доктор
        var _doctor_check = $('#service_doctor_check').is(':checked') ? 1 : 0;
        $('#repeat_loan_form [name=service_doctor]').val(_doctor_check);


        var date = new Date();
        var local_time = parseInt(date.getTime() / 1000);
console.info('local_time', local_time);
        $('#local_time').val(local_time);


        if ($('#repeat_loan_terms').is(':checked'))
        {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function(){
                    $form.addClass('loading')
                },
                success: function(resp){
                    location.reload()
                }
            })
    
        }
        else
        {
            $('.js-accept-block').addClass('error');
            e.preventDefault();
        }
    });
    
    $('.js-autodebit').click(function(e){
        e.preventDefault();
        
        var _number = $(this).data('number');
        var _card_id = $(this).data('card');
        
        var ajax_data = {};
        
        $('#autodebit .alert-block').hide();
        $('#autodebit .actions').show();
        
        if ($(this).hasClass('js-detach'))
        {
            $('#autodebit #detach_block').show();
            $('#autodebit #attach_block').hide();
            $('#autodebit #detach_block .autodebit_card_number').html(_number);
            
            $('#autodebit_form [name=card_attach]').val('');
            $('#autodebit_form [name=card_detach]').val(_card_id);
        }
        else
        {
            $('#autodebit #detach_block').hide();
            $('#autodebit #attach_block').show();
            $('#autodebit #attach_block .autodebit_card_number').html(_number);
            
            $('#autodebit_form [name=card_attach]').val(_card_id);
            if ($('.js-detach').length > 0)
            {
                $('#autodebit_form [name=card_detach]').val($('.js-detach').data('card'));
            }
            else
            {
                $('#autodebit_form [name=card_detach]').val('');
            }
        }
        
    	$.magnificPopup.open({
    		items: {
    			src: '#autodebit'
    		},
    		type: 'inline',
            showCloseBtn: true
    	});
        
    });
    
    $('#autodebit_form').submit(function(e){
        e.preventDefault();
        
        var $form = $(this);
        var _card_detach = $form.find('[name=card_detach]').val() || 0;
        var _card_attach = $form.find('[name=card_attach]').val() || 0;
        
        $.ajax({
            url: 'ajax/autodebit.php',
            data: $form.serialize(),
            beforeSend: function(){
                $form.addClass('loading');
            },
            success: function(resp){
                console.log(resp)
                
                if (!!resp.error)
                {
                    $form.find('.alert').addClass('alert-danger').removeClass('alert-success').html(resp.error);
                    $form.find('.alert-block').fadeIn();
                }
                else
                {
                    $('#autodebit #detach_block').hide();
                    $('#autodebit #attach_block').hide();
                    $('#autodebit .actions').hide();
                    
                    if (resp.success == 'CARD DETACHED')
                    {
                        var _str = 'Автоплатежи по карте удалены';
                        $form.find('.alert').removeClass('alert-danger').addClass('alert-success').html(_str);
                        $form.find('.alert-block').fadeIn();
                    }
                    if (resp.success == 'CARD ATTACHED')
                    {
                        var _str = 'Автоплатежи привязаны к карте';
                        $form.find('.alert').removeClass('alert-danger').addClass('alert-success').html(_str);
                        $form.find('.alert-block').fadeIn();
                    }
                    
                    if (_card_detach != 0)
                        $('#card_list [data-card="'+_card_detach+'"]').removeClass('toggle-link-on js-detach')
                    if (_card_attach != 0)
                        $('#card_list [data-card="'+_card_attach+'"]').addClass('toggle-link-on js-detach')
                    
                }
                
                $form.removeClass('loading');
                
            }
        })
    });
    
    
    $(".ajax_delete_card").click(function(e){
		e.preventDefault();
        
        console.log($(this).data('user_id'));
		console.log($(this).data('card_id'));
    	
        $.ajax({
            url: "/ajax/remove_card.php",
            type: "post",
            data: 'CardId='+$(this).data('card_id')+'&CustomerId='+$(this).data('user_id'),
            success:function(data){
                 console.log(data);
                 location.reload();
        	}
        });
    });
/** старая оплата в кабинете через карту 
    $(".payment_button").click(function(e){
    	 $.ajax({
            url: "/ajax/payment_link.php",
            type: "post",
            data: 'order_id='+$(".payment_amount").data('order_id')+'&amount='+$(".payment_amount").val()+'&customer='+$(".payment_amount").data('user_id')+'&payment_method='+$("#payment_method").val(),
        		success:function(data){
        		//$('.payment_button').attr("href", data);
        // location.href = data;
        
        		console.log(data);
        			}
        });
    });
*/    
    $(".get_new_loan").click(function(e){
    	e.preventDefault();
    	$(this).hide();
    	$(".loan_form").show();
	});
	
	$('.view-contract').click(function(e){
        
        var $this = $(this);
        var _number = $(this).data('number');

        $.ajax({
            url: 'ajax/get_contract.php',
            async: false,
            data: {
                number: _number
            },
            beforeSend: function(){
                $this.addClass('loading');
            },
            success: function(resp){
                if (!!resp.error)
                {
                    e.preventDefault();
                    alert('Файл не удалось загрузить!');
                }
                else
                {
                    $this.attr('href', '/files/contracts/'+resp[0]);
                    return true;
                }
            },
            complete: function(){
                $this.removeClass('loading')
            }
        });
	});

    function set_cookie_and_redirect() {
        if (window.credit_rating_button_pressed) {
            return;
        }

        document.cookie = 'kreditoff_redirected=true; max-age=' + 60*60*24;
        window.location = 'https://kreditoff-net.ru';
    }

    let is_user_on_page = true;
    let timeout_passed = false;
    document.addEventListener('visibilitychange', function (event) {
        is_user_on_page = !document.hidden;

        if (is_user_on_page && timeout_passed) {
            set_cookie_and_redirect();
        }
    });

    if (
        ($('.has-reason-block').length || $('.first_time_visit_after_rejection').length)
        && !document.cookie.split('; ').find(cookie => cookie.startsWith('kreditoff_redirected'))
    ) {
        setTimeout(function () {
            timeout_passed = true;

            if (is_user_on_page) {
                set_cookie_and_redirect();
            }
        }, 15000);
    }
});