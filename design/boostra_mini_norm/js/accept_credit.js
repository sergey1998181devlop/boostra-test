function AcceptCredit($block, isAutoAcceptCrossOrders)
{
    var allChecked = false;
    var modalIsOpen = false;
    var app = this;
    app.order_id = $block.data('order');
    app.sms_timer;
    app.forced_sms = 0;

    $block.ready(function () {
        let $input = $('input[name="service_recurent"]');

        let recur = localStorage.getItem("recurent");

        if (recur === "0") {
            $input.val("0");
        }
    });
    
    app.open_accept_modal = function(){
        var $button = $block.find('#open_accept_modal');
        
        $('.js-noactive-error').remove();
        if ($button.hasClass('js-noactive')) {
            $button.after('<span class="js-noactive-error noactive-error">Займ можно будет получить после выдачи основного</span>');
            return false;
        }
        
        $(this).closest('#open_accept_modal_wrapper').hide();

        openChooseSbpBankModal();

        $block.find('#accept_credit').fadeIn(function() {
            $button.fadeOut();
        });

        app.send_sms();
    }

    var _init_events = function(){

        $block.find("a.micro-zaim-doc-js").mousedown(function (e) {
            e.preventDefault();
            let loanAmount = $block.find('#calculator .total').text();
            if (!loanAmount) {
                loanAmount = $block.find('#approve_max_amount').text();
            }
            let is_user_credit_doctor = $block.find("#credit_doctor_check"+app.order_id).is(':checked') ? 1 : 0;
            let newUrl = $(this).attr('href') + '&loan_amount=' + loanAmount + '&credit_doctor=' + is_user_credit_doctor;
            window.open(newUrl, '_blank');
            return false;
        })

        // рекурентные платежи
        $block.find('#service_recurent_check').live('change', function(){
            if ($(this).is(':checked'))
                $block.find('[name=service_recurent]').val(1);
            else
                $block.find('[name=service_recurent]').val(0);
        });
    
        $(document).ready(function () {
            $block.find('.toggle-conditions-accept').click(function () {
                $block.find('.conditions').toggle();
            });
        });

        var _click_counter_doc = 9;
        $block.find('#credit_doctor_check'+app.order_id).live('change', function(){
            let is_new_client = $("input[name='is_new_client']").val();
            if (_click_counter_doc > 0 && is_new_client != 1)
            {
                $block.find('#credit_doctor_check'+app.order_id).attr('checked', true);
                _click_counter_doc--;
            }
            $block.find('[name=is_user_credit_doctor]').val($(this).is(':checked') ? 1 : 0);
        });

        $block.find('#open_accept_modal').click(function(){
            app.open_accept_modal();
        });

        $block.find('#open_accept_documents').click(function(){
            $block.find('.checkbox-item').hide();
            $.magnificPopup.open({
        		items: {
        			src: '#accept'+app.order_id
        		},
        		type: 'inline',
                showCloseBtn: true
        	});
        });

        $block.find('#open_accept_documents_new').click(function(){
            $block.find('#service_insurance_div').remove();

            $block.find('.checkbox-item').hide();
            $.magnificPopup.open({
        		items: {
        			src: '#accept'+app.order_id
        		},
        		type: 'inline',
                showCloseBtn: true
        	});
        });

        $block.find('.repeat_sms').click(function(e){
            e.preventDefault();
            if (!$(this).hasClass('inactive'))
                app.send_sms();
        })

        $block.find('.js-need-verify, .js-need-verify-modal').on('change', function() {
            if ($(this).is(':checked')) {
                $(this).attr('checked', 'checked');
            } else {
                $(this).removeAttr('checked');
            }
        });

        $block.find('.js-agree-claim-value, .js-agree-all-claim-value').on('change', function() {
            let hiddenField = $('#agree_claim_value');

            if ($(this).is(':checked')) {
                $(this).attr('checked', 'checked');
                hiddenField.val('0');
            } else {
                $(this).removeAttr('checked');
                hiddenField.val('1');
            }
        });

        $block.find('#agree_all, #all_button_click').on('click', function() {
            allChecked = !allChecked;

            $block.find('input[type="checkbox"]').each(function () {
                if ($(this).attr('id') === "agree_claim_value" && $(this).is(":hidden")) {
                    return;
                }
                $(this).prop('checked', allChecked);
            });
        });


        $block.find('input[type="checkbox"]').on('change', function() {
            if ($block.find('#open_modal').is(':hidden')) {
                return
            }

            allChecked = true;
            $.each($block.find('input[type="checkbox"]').not('.js-agree-all-claim-value'), function () {
                if($(this).is(":visible") && !$(this).prop('checked')) {
                   allChecked = false;
                   $block.find('.js-agree-all-claim-value').prop('checked', false);
                   return
                }
            });

            if (allChecked) {
                $block.find('.js-agree-all-claim-value').prop('checked', true);
            }
        });

        $block.find('#show_docs_modal, #close_modal').click(function(){
            $block.find('#open_modal, #backdrop_modal').toggle();
            modalIsOpen = !modalIsOpen;
        });

        $block.find('#backdrop_modal').click(function (event) {
            if(!$(event.target).closest('#open_modal').length && !$(event.target).is('#open_modal')) {
                $block.find('#open_modal, #backdrop_modal').toggle();
                modalIsOpen = !modalIsOpen;
            }
        });

        $block.find('#accept_credit_form').submit(function(e){
            e.preventDefault();

            if ($block.find('.js-need-verify').not(':checked').length > 0) {
                $block.find('#not_checked_info').show();
                $block.find('#not_checked_info_modal').show();
                $block.find('.conditions').show();
                console.log($block.find('.js-need-verify').not(':checked').length);
                return;
            } else {
                app.check_sms();
            }
        });

        $block.find('.sms_code').on('keyup', function() {
            var _v = $(this).val();
            if (_v.length == 4) {
                var uncheckedVerifyCheckboxes = $block.find('.js-need-verify').not(':checked');

                if (uncheckedVerifyCheckboxes.length === $block.find('.js-need-verify').length) {
                    if ($block.find('input[name="is_user_credit_doctor"]').data('allowed') !== 0) {
                        $block.find('input[name="credit_doctor_check"]').prop('checked', true);
                        $block.find('input[name="is_user_credit_doctor"]').val('1');
                    }
                    if ($block.find('input[name="is_tv_medical"]').data('allowed') !== 0) {
                        $block.find('input[name="tv_medical_check"]').prop('checked', true);
                        $block.find('input[name="is_tv_medical"]').val('1');
                    }
                    $block.find('input[name="agree_claim_value"]').val('0')
                } else if (uncheckedVerifyCheckboxes.length > 0) {
                    $block.find('#not_checked_info').show();
                    $block.find('#not_checked_info_modal').show();
                } else {
                    $block.find('#not_checked_info_modal, #not_checked_info').hide();
                    app.check_sms();
                }
            }
        });

        if (!$block.find('#open_accept_modal').is(':visible')) {
            app.forced_sms = 1;
        }
    };

    app.send_sms = function(){
        var _phone = $block.find('#repeat_sms').data('phone')
        $.ajax({
            url: 'ajax/sms.php',
            data: {
                action: 'send',
                phone: _phone,
                flag: 'АСП',
                forced: app.forced_sms,
                order_id: app.order_id,
            },
            success: function(resp){
                if (!!resp.error)
                {
                    if (resp.error == 'sms_time')
                        app.set_timer(resp.time_left);
                    else
                        console.log(resp);
                }
                else
                {
                    app.set_timer(resp.time_left);
                    app.sms_sent = 1;

                    if (!!resp.developer_code)
                        modalIsOpen ? $block.find('#sms_code_modal').val(resp.developer_code) : $block.find('#sms_code').val(resp.developer_code);
                }
                app.forced_sms = 0;
            }
        });
    };

    app.check_sms = function(){
        var _data = {
            action: 'check',
            phone: $block.find('#repeat_sms').data('phone'),
            code: $block.find('#sms_code').val(),
            check_asp: 1,
            order_id: $block.find('#accept_credit_form [name=order_id]').val()
        };
        $.ajax({
            url: 'ajax/sms.php',
            data: _data,
            beforeSend: function(){
                $block.find('#sms_code').val(_data.code);
                $block.find('#accept_credit_form').addClass('loading')
            },
            success: function(resp){
                $block.find('[name=sms_code]').val(_data.code);
                if (resp.success)
                {
                    app.approve();
                }
                else
                {
                    // код не совпадает
                    if (resp.accept_try == 1)
                    {
                        $block.find('.sms-code-error').html('Код не совпадает<br />У Вас осталась последняя попытка после чего аккаунт будет заблокирован').show();
                    }
                    else if (resp.accept_try > 1)
                    {
                        $block.find('.sms-code-error').html('Код не совпадает<br />У Вас осталась попыток: '+resp.accept_try).show();
                    }
                    else
                    {
                        console.log("logout");
                        // location.href = '/account/logout'
                    }
                    $block.find('#accept_credit_form').removeClass('loading')
                }
            }

        });
    }

    app.set_timer = function(_seconds){

        clearInterval(app.sms_timer);

        app.sms_timer = setInterval(function(){
            _seconds--;
            if (_seconds > 0)
            {
                var _str = '<span>Повторно отправить код можно через '+_seconds+' сек</span>';
                $block.find('.repeat_sms').addClass('inactive').html(_str).show();
            }
            else
            {
                $block.find('.repeat_sms').removeClass('inactive').html('<a class="js-send-repeat" href="#">Отправить код еще раз</a>').show();

                clearInterval(app.sms_timer);
            }
        }, 1000);

    };

    app.approve = function(){
        var _data = $block.find('#accept_credit_form').serialize();
        $.ajax({
            url: 'ajax/accept_credit.php',
            data: _data,
            beforeSend: function (){
              console.log(_data);
            },
            success: function(resp){
                if (!!resp.error) {
                    if (!!resp.error.Message) {
                        if (resp.error.Message == 'Недостаточно средств на счете компании') {
                            alert('Произошла ошибка. Попробуйте повторить через 30 минут.');
                        } else {
                            alert(resp.error.Message);
                        }
                    } else {
                        alert(resp.error);

                        if (resp.need_reload) {
                            location.reload();
                        }
                    }

                    $block.find('#accept_credit_form').removeClass('loading');
                } else {
                    if (!isAutoAcceptCrossOrders) {
                        window.location.reload();
                    } else {
                        if (!$('.cross_order_accept #accept_credit').is(':visible')) {
                            $block.hide();
                            $('.cross_order_accept #open_accept_modal').click();
                        }
                    }
                    
                }
            }
        })
    };

    /**
     * Обновляем период и процент
     * чтобы взять период из заявки, отправьте только параметр percent или period = 0
     * @param percent
     * @param period
     * @param accept_button_name
     */
    app.updatePercentAndPeriod = function (percent, period = 0, accept_button_name = '') {
        return $.ajax({
            url: 'ajax/loan.php?action=change_period_and_percent',
            data: {
                percent,
                period,
                accept_button_name,
            },
            //async: false,
            method: 'POST',
            beforeSend: function () {
                $('body').addClass('is_loading');
            },
            success: function (resp) {
                if (resp['approved_file']) {
                    $block.find('.contract_approve_file').attr('href', resp['approved_file']);
                }

                return resp;
            },
            error: function (xhr, ajaxOptions, thrownError) {
                let error = thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText;
                alert(error);
                console.log(error);
            },
        }).done(function () {
            $('body').removeClass('is_loading');
        });
    }

    ;(function(){
        _init_events();
    })();

    return app;
}
