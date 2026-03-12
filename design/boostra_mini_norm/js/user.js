$(document).ready(function(){

    $('#is_need_reassign_block .modal_title .close-modal').hide();
    $.magnificPopup.close()

    $('.confirm-card-form').submit(function (e) {
        e.preventDefault();

        var $self = $(this);
        var organization_id = $self.find('[name="organization_id"]').val();

        $.ajax({
            url: 'ajax/b2p_payment.php',
            data: {
                action: 'attach_card',
                organization_id: organization_id,
            },
            success: function (resp) {
                if (!!resp.link) {
                    location.href = resp.link;
                    return true;
                } else {
                    var $errorBlock = $self.find('.request-error-block');

                    $errorBlock.html(resp.error);
                    $errorBlock.show();

                    $self.find('.error-block').hide();

                    e.preventDefault();
                    return false;
                }
            }
        })
    });

    $('.js-individual-pay').click(function(e){
        var order_id = $(this).data('order')
        $.ajax({
            url: '/ajax/payment.php?action=create_transaction_ip&order_id='+order_id,
            success: function(resp){
                if(resp.error) {
                    alert(resp.error);
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
    $('#service_recurent_check').on('change', function() {
        var _check = $(this).is(':checked') ? 1 : 0;
        $('#repeat_loan_form [name=service_recurent]').val(_check);
    });

    // смс информирование
    $('#service_sms_check').on('change', function() {
        var _check = $(this).is(':checked') ? 1 : 0;
        $('#repeat_loan_form [name=service_sms]').val(_check);
    });

    // страховка
    var _click_counter = 9;

    $('#service_insurance_check').on('change', function() {
        let is_new_client = $("input[name='is_new_client']").val();
        if (_click_counter > 0 && is_new_client != 1) {
            $('#service_insurance_check').prop('checked', true);
            _click_counter--;
        } else {
            var _check = $(this).is(':checked') ? 1 : 0;
            $('#repeat_loan_form [name=service_insurance]').val(_check);
        }
    });

    // причина отказа
    $('#service_reason_check').on('change', function() {
        var _check = $(this).is(':checked') ? 1 : 0;
        $('#repeat_loan_form [name=service_reason]').val(_check);
    });

    // кредитный доктор
    $('#service_doctor_check').on('change', function() {
        var _check = $(this).is(':checked') ? 1 : 0;
        $('#repeat_loan_form [name=service_doctor]').val(_check);
    });

    $('.js-need-value').on('change', function() {
        var isChecked = $(this).is(':checked');
        $(this).val(isChecked ? 1 : 0);
    });

    $('.js-close-autodebit').click(function(e) {
        $.magnificPopup.close();
    });


    $('.js-need-value').change(function() {
        var $checkbox = $(this);
        var newValue = $checkbox.is(':checked') ? '1' : '0';
        $checkbox.val(newValue);
    });

    $('#repeat_loan_form').submit(function(e) {
        e.preventDefault();

        var $form = $(this);

        if ($form.hasClass('loading')) {
            return false;
        }

        updateFormValues();

        var allChecked = validateCheckboxes('.js-need-value');

        if (!allChecked) {
            handleErrors(allChecked);
            return;
        }

        updateLocalTime();
        submitForm($form);
    });

    $('#repeat_loan_form_approve').submit(function(e) {
        e.preventDefault();

        var $form = $(this);

        if ($form.hasClass('loading')) {
            return false;
        }

        var allChecked = validateCheckboxes('.js-need-value');

        if (!allChecked) {
            handleErrors(allChecked);
            return;
        }

        updateLocalTime();
        submitForm($form);
    });

    function updateFormValues() {
        $('#repeat_loan_form [name=service_recurent]').val($('#service_recurent_check').is(':checked') ? 1 : 0);
        $('#repeat_loan_form [name=service_sms]').val($('#service_sms_check').is(':checked') ? 1 : 0);
        $('#repeat_loan_form [name=service_insurance]').val($('#service_insurance_check').is(':checked') ? 1 : 0);
        $('#repeat_loan_form [name=service_reason]').val($('#service_reason_check').is(':checked') ? 1 : 0);
        $('#repeat_loan_form [name=service_doctor]').val($('#service_doctor_check').is(':checked') ? 1 : 0);
    }

    function validateCheckboxes(selector, checkForValue = true) {
        var allChecked = true;
        $(selector).each(function() {
            var value = $(this).val();
            if ((checkForValue && value !== '1') || (!checkForValue && value === '1')) {
                allChecked = false;
                $(this).siblings('label').addClass('error');
            } else {
                $(this).siblings('label').removeClass('error');
            }
        });
        return allChecked;
    }

    function handleErrors(allChecked) {
        $('.js-accept-block').toggleClass('error', !allChecked);
        $('#not_checked_info').toggle(!allChecked);
        $('.conditions').show();
    }

    function updateLocalTime() {
        var local_time = Math.floor(new Date().getTime() / 1000);
        $('#local_time').val(local_time);
    }

    function submitForm($form) {
        if ($form.hasClass('js-autoconfirm-form')) {
            autoconfirm($form);
        } else {
            $.ajax({
                type: 'POST',
                data: $form.serialize(),
                beforeSend: function() {
                    $form.addClass('loading');
                },
                success: function(resp) {
                    let is_new_client = $("input[name='is_new_client']").val();
                    if (is_new_client != 1) {
                        sendMetric('reachGoal', 'podal_zayavku_lk_pk');
                    } else {
                        sendMetric('reachGoal', 'podal_zayavku_lk_nk');
                    }
    
                    location.reload();
                }
            });
        }
    }
    
    function autoconfirm($form) {
        $.magnificPopup.open({
            items: {src: '.autoconfirm_sms_block'},
            type: 'inline',
            showCloseBtn: false,
            modal: true,
        }); 
        send_sms();       
    }
    
    send_sms = function(){
        var _phone = $('.js-autoconfirm-block').data('phone');
        $.ajax({
            url: 'ajax/sms.php',
            data: {
                action: 'send',
                phone: _phone,
                flag: 'autoconfirm',
            },
            success: function(resp){
                if (!!resp.error)
                {
                    if (resp.error == 'sms_time')
                        set_timer(resp.time_left);
                    else
                        console.log(resp);
                }
                else
                {
                    set_timer(resp.time_left);
                }
            }
        });
    };

    check_sms = function(){
        var _data = {
            action: 'check_autoconfirm',
            phone: $('.js-autoconfirm-block').data('phone'),
            code: $('.js-autoconfirm-sms').val(),
        };
        $.ajax({
            url: 'ajax/sms.php',
            data: _data,
            beforeSend: function(){
                $('.js-autoconfirm-block').addClass('loading')
            },
            success: function(resp){
                if (resp.success) {
                    $('.js-autoconfirm-form').removeClass('js-autoconfirm-form');
                    $('.js-autoconfirm-block').removeClass('error');
                    $('.js-autoconfirm-error').html('');
                    $('#repeat_loan_form_approve [name="sms"]').val(_data.code);
                    
                    submitForm($('#repeat_loan_form_approve'));
                } else {
                    // код не совпадает
                    $('.js-autoconfirm-block').removeClass('loading');
                    $('.js-autoconfirm-error').html(resp.error)
                    $('.js-autoconfirm-block').addClass('error');
                }
            }

        });
    }
    
    var sms_timer;
    set_timer = function(_seconds){

        clearInterval(sms_timer);

        sms_timer = setInterval(function(){
            _seconds--;
            if (_seconds > 0)
            {
                var _str = '<span>Повторно отправить код можно через '+_seconds+'сек</span>';
                $('.js-repeat-autoconfirm-sms').addClass('inactive').html(_str).show();
            }
            else
            {
                $('.js-repeat-autoconfirm-sms').removeClass('inactive')
                    .html('<a class="js-send-repeat" href="#">Отправить код еще раз</a>').show();

                clearInterval(sms_timer);
            }
        }, 1000);

    };
    
    $('.js-repeat-autoconfirm-sms').click(function(e){
        e.preventDefault();
        
        if ($('.js-repeat-autoconfirm-sms').hasClass('inactive')) {
            return false;
        }
        send_sms();
    });

    $('.js-autoconfirm-sms').keyup(function() {
        var _v = $(this).val();
        if (_v.length == 4) {
            check_sms();
        }
    });
    
    $('.js-dogovor-link').click(function(e){        
        $('.js-autoconfirm-form').removeClass('js-autoconfirm-form');
        $.cookie('autoconfirm_disabled', 1);
        sendMetric('reachGoal','read_dogovor_pk');
            
        if (!_href) {
            var _href = $(this).attr('href');
        }
        let _params = {
            percent: BASE_PERCENTS,
            period: $('#time-range').val(),
            amount: $('#money-range').val(),
        };
        let string_params = '';
        $.each(_params, function(k, v){
            string_params += '&params['+k+']='+v;
        });
        $(this).attr('href', _href + string_params);
        return true;
    });
    
    $('.js-autodebit').click(function(e){
        e.preventDefault();
        
        var _number = $(this).data('number');
        var _card_id = $(this).data('card');
        var _card_type = $(this).data('type');
        
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
        
        $('#autodebit_form [name=card_type]').val(_card_type);
    	
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
    $(".get_new_loan").click(function(e) {
        e.preventDefault();
        if ($(this).attr('is_need_reassign')) {
            $.magnificPopup.open({
                items: {
                    src: '#is_need_reassign_block'
                },
                type: 'inline',
                showCloseBtn: true,
                closeOnBgClick: true,
                enableEscapeKey: false,
            });

            console.log('need reassign')
        }

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

        /*if (is_user_on_page && timeout_passed) {
            set_cookie_and_redirect();
        }*/
    });

    if (
        ($('.has-reason-block').length || $('.first_time_visit_after_rejection').length)
        && !document.cookie.split('; ').find(cookie => cookie.startsWith('kreditoff_redirected'))
    ) {
        setTimeout(function () {
            timeout_passed = true;

            /*if (is_user_on_page) {
                set_cookie_and_redirect();
            }*/
        }, 15000);
    }


    if (!localStorage.showModalAsp) {
        let today = new Date();

        let utcTime = today.getTime() + today.getTimezoneOffset() * 60000;
        let moscowTime = new Date(utcTime + 3 * 60 * 60000);

        let date = moscowTime.getFullYear() + '-' +
            (moscowTime.getMonth() + 1).toString().padStart(2, '0') + '-' +
            moscowTime.getDate().toString().padStart(2, '0');
        let time = moscowTime.getHours().toString().padStart(2, '0') + ":" +
            moscowTime.getMinutes().toString().padStart(2, '0');

        localStorage.showModalAsp = date + ' ' + time;
    }
    function openModal(){
        let user_id = $('.user-id').data('id')

        $.ajax({
            url: 'ajax/additional_agreement.php',
            data: {
                user_id: user_id,
            },
            success: function(resp){
                if (resp){
                    $('#show-modal-asp').val(resp)

                    if (resp != 0 ){
                        $.magnificPopup.open({
                            items: {
                                src: '#asp_sms'
                            },
                            type: 'inline',
                            showCloseBtn: false,
                            modal: true,
                        });
                        $('#asp_sms').css('display','block')
                    }
                }
            }
        });
    }

    setInterval(function () {
        let today = new Date();
        let date = today.getFullYear()+'-'+(today.getMonth()+1)+'-'+today.getDate();
        let time = (today.getHours() - 1) + ":" + today.getMinutes();
        if (localStorage.showModalAsp == date+' '+time ) {
            // if (true) {
            openModal();
            let time = today.getHours() + ":" + today.getMinutes();
            localStorage.showModalAsp = date+' '+time
        }

    },60000)

    // REMOVED: Модальное окно арбитражного соглашения на 9-й день просрочки
    // Документы теперь подписываются при получении займа (LEGAL-243)
    // function openArbitrationModal(){
    //     let user_id = $('input[name=user_id]').val();
    //     let arbitrModal = $('#arbitr');
    //
    //     if (user_id === undefined) {
    //         return false;
    //     }
    //
    //     if (arbitrModal.length === 0) {
    //         return false;
    //     }
    //
    //     $.ajax({
    //         url: 'ajax/arbitration_agreement.php',
    //         data: { user_id },
    //         success: function(resp) {
    //             if (resp && resp !== 0 && arbitrModal){
    //                 $('#show-modal-asp').val(resp)
    //
    //                 $.magnificPopup.open({
    //                     items: {
    //                         src: '#arbitr'
    //                     },
    //                     type: 'inline',
    //                     showCloseBtn: false,
    //                     modal: true,
    //                     callbacks: {
    //                         close: function() {
    //                             setTimeout(openArbitrationModal, 600000);
    //                         }
    //                     }
    //                 });
    //                 arbitrModal.css('display','block')
    //             }
    //         }
    //     });
    // }
    //
    // openArbitrationModal();

    $('#newlk_form').submit(function(){
        let user_id = $(this).data('user');
        let url = '/ajax/filestorage.php?user_id='+user_id;
        $.get(url);
    });

    function startCountdown() {
        let time = localStorage.getItem('countdownTime') ? parseInt(localStorage.getItem('countdownTime')) : 180; // 3 минуты в секундах
        const minutesElement = $('#minutes');
        const secondsElement = $('#seconds');

        function updateCountdown() {
            let minutes = Math.floor(time / 60);
            let seconds = time % 60;

            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;

            minutesElement.text(minutes);
            secondsElement.text(seconds);

            time--;

            if (time < 0) {
                time = 180;
            }

            localStorage.setItem('countdownTime', time);
        }

        setInterval(updateCountdown, 1000);
    }

    startCountdown();

    $('.wheel-open').click(function () {
        var $self = $(this);
        var $wheelWrapper = $self.parents('.parent-wrapper').find('.wheel-wrap');

        $wheelWrapper.empty();

        if ($wheelWrapper.length > 1) {
            $('.wheel-wrap').each(function () {
                if (!$wheelWrapper.is(this)) {
                    $(this).remove();
                }
            });
        }

        $.post('/wheel', {}, function (data) {
            data = typeof data === 'string' ? JSON.parse(data) : data;

            if (data.html) {
                $wheelWrapper.html(data.html);
                $('.wheel-open').each(function () {
                    $(this).remove();
                });
                $wheelWrapper.show();
            }
        });
    })

});