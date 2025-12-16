function LoanApp()
{
    var app = this;
    
    var DEBUG = 1;
    
    app.$form;
    app.sms_timer;
    app.sms_delay = 0;
    
    app.init = function(){

        app.$form = $('#loan_form');

    };
    
    app.init_soglasie = function(){
        $('.js-open-soglasie').click(function(e){
            
            var _url = 'preview/';
            _url += $(this).data('tpl')+'?';
            _url += 'params[lastname]='+$('#lastname').val()+'&'
            _url += 'params[firstname]='+$('#firstname').val()+'&';
            _url += 'params[patronymic]='+$('#patronymic').val()+'&';
            _url += 'params[phone]='+$('#phone').val()+'&';
            _url += 'params[birth]='+$('#birthday').val();
            
            $(this).attr('href', _url);
            
            return true;
        });
    };
    
    app.init_events = function(){
        
        // клик по кнопке "Отправить еще раз"
        app.$form.find('.js-send-again').click(function(e){
            e.preventDefault();
            if ($(this).is(':visible'))
            {
                _send_code(0);
            }
        });
        
        // клик по кнопке "Отправить код"
        app.$form.find('.js-send-code').click(function(e){
            e.preventDefault();
            if ($(this).is(':visible'))
            {
                $(".mini-stages .progress-bar").css('width', '32%');
                $(".mini-stages .progress-bs span").text('+ 32% к вероятности одобрения займа');
                $(".mini-stages ul li:eq(1)").addClass('current');

                if (app.$form.valid())
                    _send_code(1);
            }
        });
        
        // открытие модалки с правилами
        app.$form.find('#accept_link').click(function(e){
            e.preventDefault();
        	$.magnificPopup.open({
        		items: {
        			src: '#accept'
        		},
        		type: 'inline',
                showCloseBtn: false
        	});
        });
        
        // изменение номера телефона
        app.$form.find('[name=phone]').change(function(){
            $(this).closest('label').removeClass('error');
            
            app.$form.find('.js-info-block').hide();
            app.$form.find('[name=code]').val('');
            app.$form.find('.js-send-block').show()
            app.$form.find('.js-send-code').show()
        });
        
        // изменение поля с кодом
        app.$form.find('.js-input-code').keyup(function(){
            
            var _v = $(this).val();
            if (_v.length == 4)
               _check_code();
        });
        
        // рекурентные платежи
        $('#service_recurent_check').live('change', function(){
            if ($(this).is(':checked'))
                app.$form.find('[name=service_recurent]').val(1);
            else
                app.$form.find('[name=service_recurent]').val(0);
        });
        
        // смс информирование
        $('#service_sms_check').live('change', function(){
            if ($(this).is(':checked'))
                app.$form.find('[name=service_sms]').val(1);
            else
                app.$form.find('[name=service_sms]').val(0);
        });
        
        // страховка
        $('#service_insurance_check').live('change', function(){
            if ($(this).is(':checked'))
                app.$form.find('[name=service_insurance]').val(1);
            else
                app.$form.find('[name=service_insurance]').val(0);      
        });
        
        // причина отказа
        $('#service_reason_check').live('change', function(){
            if ($(this).is(':checked'))
                app.$form.find('[name=service_reason]').val(1);
            else
                app.$form.find('[name=service_reason]').val(0);
        });

        // кредитный доктор
        $('#service_doctor_check').live('change', function(){
            if ($(this).is(':checked'))
                app.$form.find('[name=service_doctor]').val(1);
            else
                app.$form.find('[name=service_doctor]').val(0);
        });
        
//        app.$form.find('input').change(_check_form)
    };
    
    var _check_code = function(){
        
        var _code = app.$form.find('.js-input-code').val();
        var _phone = app.$form.find('.js-input-phone').val();
    
        $.ajax({
            url: 'ajax/loan.php',
            data: {
                action: 'check_code',
                phone: _phone,
                code: _code
            },
            beforeSend: function(){
                app.$form.addClass('loading');
            },
            success: function(resp){
                if (!!resp.error)
                {
                    app.$form.find('.js-code-block').addClass('error');
                    app.$form.find('.js-code-block').find('.error').text(resp.soap_fault ? resp.error : 'Код не совпадает');
                    
                    app.$form.find('#phone_checked').val('0')
                }
                else if (!!resp.success)
                {
                    app.$form.find('.js-send-repeat').hide();
                    app.$form.find('.js-send-again').hide();
                    app.$form.find('.js-send-text').hide();
                    app.$form.find('.js-code-block').hide();
                    app.$form.find('.js-send-block').hide();
                    app.$form.find('.js-accept-block').fadeIn();
                    app.$form.find('.js-submit-block').fadeIn();
                    
                    app.$form.find('#phone_checked').val('1')
                    
                    app.$form.find('.js-info-block').removeClass('error').addClass('text-left').html('<span class="success"> Телефон подтвержден</span>').fadeIn();
                }
            },
            complete: function(){
                app.$form.removeClass('loading');
            }
        });
    };
    
    var _send_code = function(need_check){
        
        var _phone = app.$form.find('[name=phone]').val();
        var _lastname = app.$form.find('[name=lastname]').val();
        var _firstname = app.$form.find('[name=firstname]').val();
        var _patronymic = app.$form.find('[name=patronymic]').val();
        var _birth = app.$form.find('[name=birthday]').val();
        var g_recaptcha_response = app.$form.find('[name=g-recaptcha-response]').text();
        
        var senddata = {
            action: 'send_code',
            need_check: need_check,
            phone: _phone,
            lastname: _lastname,
            firstname: _firstname,
            patronymic: _patronymic,
            birth: _birth,
            'g-recaptcha-response': g_recaptcha_response
        };
        
        if (app.$form.find('.js-need-verify').not(':checked').length > 0)
        {
            app.$form.find('.js-accept-block').addClass('error').find('span.error').html('Необходимо согласиться с обработкой персональных данных').show();
        }
        else
        {
            $.ajax({
                url: 'ajax/loan.php?action=send_code',
                type: 'POST',
                data: app.$form.serialize(),
                beforeSend: function(){
                    app.$form.addClass('loading');
                },
                success: function(resp){
                    
                    if (!!resp.error)
                    {
                        if (resp.error == 'user_blocked')
                        {
                            var _str = '<span class="error-text">Пользователь с таким номером уже зарегистрован.<br />С Вами свяжется Клиентский Центр.</span>';
                            app.$form.find('.js-info-block').addClass('error').html(_str).fadeIn();
                            app.$form.find('.js-send-code').hide();
                            app.$form.find('#recaptcha_register').hide();
                        }
                        else if (resp.error == 'user_removed')
                        {
                            var _str = '<span class="error-text">Аккаунт с таким номером удален.<br /></span>';
                            app.$form.find('.js-info-block').addClass('error').html(_str).fadeIn();
                            app.$form.find('.js-send-code').hide();
                            app.$form.find('#recaptcha_register').hide();
                        }
                        else if (resp.error == 'fio_removed')
                        {
                            var _str = '<span class="error-text">Аккаунт на Ваше имя удален.<br /></span>';
                            app.$form.find('.js-info-block').addClass('error').html(_str).fadeIn();
                            app.$form.find('.js-send-code').hide();
                            app.$form.find('#recaptcha_register').hide();
                        }
                        else if (resp.error == 'user_exists')
                        {
                            var _str = '<span class="error-text">Пользователь с таким номером уже зарегистрован.<br /><a class="error-a" href="user/login?phone='+_phone+'">Воспользуйтесь формой входа для клиентов</a></span>';
                            app.$form.find('.js-info-block').addClass('error').html(_str).fadeIn();
                            app.$form.find('.js-send-code').hide();
                            app.$form.find('#recaptcha_register').hide();
                        }
                        else if (resp.error == 'sms_time')
                        {
                            app.sms_delay = resp.sms_time;
                            _run_sms_timer();
                        }
                        else if (resp.error == 'recaptcha_error')
                        {
                            var _str = '<span class="error-text">Вы не прошли проверку "Я не робот".</span>';
                            app.$form.find('.js-info-block').addClass('error').html(_str).fadeIn();
                        }
                        else if (resp.error == 'dont_work')
                        {
                            var _str = '<span class="error-text">Новые заявки принимаются с 7:00 до 17:00(мск)<br />Попробуйте в рабочее время.</span>';
                            app.$form.find('.js-info-block').addClass('error').html(_str).fadeIn();
                            app.$form.find('.js-send-code').hide();
                        }
                        else
                        {  
                            app.$form.find('.js-info-block').addClass('error').html(resp.error).fadeIn();
                            app.$form.find('.js-send-code').show();
                            
                            if (!!DEBUG)
                                console.error(resp.error);
                        }
                    } 
                    else
                    {
                        if (!!DEBUG)
                            console.info(resp);
                        
                        app.$form.find('.js-info-block').removeClass('error').html('').hide();
                        
                        app.$form.find('.step1').hide();
                        app.$form.find('.step2').fadeIn();
                        $('.js-base-title').hide();
                        $('.js-sms-title').fadeIn();
                        $('.js-phone-number').html(_phone)
                            /*
                        app.$form.find('.js-send-block').show();
                        app.$form.find('.js-code-block').show();  
                        app.$form.find('.js-send-code').hide();   
                        */

                        sendMetric('reachGoal', 'etap-kontakty');
                        
                        if (!!resp.code)
                            app.$form.find('.js-input-code').val(resp.code);
                        
                        app.$form.find('.js-input-code').focus();
                        app.sms_delay = resp.sms_time;
                        _run_sms_timer();
                    }
                },
                complete: function(){
                    app.$form.removeClass('loading');
                }
            })
        }    
    };
    
    
    app.init_masks = function(){
        
        app.$form.find('[name=email]').inputmask({
            mask: "*{1,20}[.*{1,20}][.*{1,20}][.*{1,20}]@*{1,20}[.*{2,6}][.*{1,2}]",
            greedy: false,
            definitions: {
                '*': {
                    validator: "[0-9A-Za-z!#$%&'*+/=?^_`{|}~\-]",
                    cardinality: 1,
                    casing: "lower",
                    clearIncomplete: false
                }
            }
        });

        app.$form.find('[name=phone]').inputmask(
            "+7 (999) 999-99-99",
            {
                oncomplete: function (e) {
                    $(e.target).parent().next().find('input').focus();
                },
                clearIncomplete: false
            }
        );
        
        

    };
    
    app.run_validate = function(){
         app.$form.validate({
            errorElement: "span",
    		rules: {
    			"firstname": {
    				russian: true
    			},
    			"lastname": {
    				russian: true
    			},
    			"patronymic": {
    				russian: true
    			},
/*
                "email": {
                    user_email:true
                },
*/
    			"birthday": {
    				Birth: true
    			},
    			"phone": {
    				only_mobile: true
    			}
    		},
            submitHandler: function(form) {
                
                if (app.$form.find('.js-input-accept').length > 0 && !app.$form.find('.js-input-accept').is(':checked'))
                {
                    app.$form.find('.js-accept-block').addClass('error');
                }
                else if (app.$form.find('.js-input-code').val() == '' || app.$form.find('#phone_checked').val() == 0)
                {
                    app.$form.find('.js-code-block').addClass('error').fadeIn();
                }
                else if ($('.js-need-verify-modal').not(':checked').length > 0)
                {
                	$.magnificPopup.open({
                		items: {
                			src: '#accept'
                		},
                		type: 'inline',
                        showCloseBtn: false
                	});
                    $('#modal_error').show();

                    return false;
                }
                else 
                {
                    app.$form.find('.js-accept-block').removeClass('error');

                    sendMetric('reachGoal', 'etap-telephone');
                    app.$form.addClass('loading')
                    form.submit();
                }
            }
         });
    };
    
    var _check_form = function(){
        if (!!DEBUG)
            console.log( "Valid: " + app.$form.valid() );


    }
    
    var _run_sms_timer = function(){
        
        clearInterval(app.sms_timer);
        
        app.sms_timer = setInterval(function(){
            app.sms_delay--;
            if (app.sms_delay > 0)
            {
                app.$form.find('.js-send-again').hide();
                app.$form.find('.js-send-repeat').show();
                
                var _str = '<span>Повторный запрос кода через '+app.sms_delay+' сек</span>';
                app.$form.find('.js-send-repeat').html(_str).show();
            }
            else
            {
                app.$form.find('.js-send-again').text('Выслать повторно').show();
                app.$form.find('.js-send-repeat').hide();
                
                clearInterval(app.sms_timer);
            }
        }, 1000);
        
    };
    
    ;(function(){
        
        app.init();
        app.init_masks();
        app.init_events();
        
        app.init_soglasie();
        
        app.run_validate();
    })();
    
    
//    console.log(app.$form.find('[name=email]').data('email'))
    if (app.$form.find('[name=email]').data('email') != '')
        app.$form.find('[name=email]').val(app.$form.find('[name=email]').data('email'))
};


$(function(){
    if ($('#loan_form').length > 0)
        new LoanApp();
});