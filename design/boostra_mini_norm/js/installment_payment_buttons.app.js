function InstallmentPaymentButtonsApp($block)
{
    var app = this;
    
    function _init(){
        app.pdp = $block.data('pdp');
        app.chdp = $block.data('chdp');
        app.need_accept = $block.data('need-accept');
        app.next_payment = $block.data('next-payment');
        app.phone = $block.data('phone');
    };
    
    function _init_documents(){
        $block.find('.js-il-document-link').click(function(e){
            if (!$(this).hasClass('ready')) {
            
                var user_id = $block.data('user-id'),
                    href = $(this).data('href'),
                    contract_number = $block.data('contract-number'),
                    contract_date = $block.data('contract-date'),
                    payment_amount = $block.find('.js-il-chdp-amount').val();
                
                var link = href
                    +'?user_id='+user_id
                    +'&params[contract_number]='+contract_number
                    +'&params[contract_date]='+contract_date
                    +'&params[payment_amount]='+payment_amount;
                $(this).addClass('ready').attr('href', link);

                return true;
            }
        });
    };
    
    function _init_chdp(){
        var $chdp_form = $block.find('.js-il-chdp-form')
        var $chdp_button = $block.find('.js-il-chdp-button');
        var $chdp_accept_block = $block.find('.js-il-chdp-accept-block');
        var $checkbox = $block.find('.js-il-chdp-checkbox');
        var $warning = $block.find('.js-il-warning-message');
        var $amount_input = $block.find('.js-il-chdp-amount');

        $amount_input.on('input', function(){
            _check_chdp_amount();
        });

        $checkbox.change(function(){
            if ($(this).is(':checked')) {
                $warning.hide();
            } else {
                var current_amount = parseFloat($amount_input.val()) || 0;
                var rec_amount = parseFloat($amount_input.data('rec')) || 0;
                if (current_amount > rec_amount) {
                    $warning.show();
                }
            }
            _check_chdp_amount();
        });
        
        $chdp_button.click(function(){
            $block.find('.js-il-chdp-button').hide();
            $chdp_accept_block.show();
            _send_chdp_sms();
        })
        
        $block.find('.js-il-chdp-code-repeat').click(function(e){
            e.preventDefault();
            if (!$(this).hasClass('inactive')) {
                _send_chdp_sms();
            }
        });
        
        $block.find('.js-il-chdp-code-button').click(function(){
            if (!$(this).hasClass('loading')) {
                _check_chdp_sms();
            }
        });
        
        $block.find('.js-il-chdp-amount').blur(function(){
            _check_chdp_amount();
        });
        $block.find('.js-il-chdp-amount').keyup(function(){
            _check_chdp_amount();
        });

        $block.find('.js-open-chdp-form').click(function(e){
            e.preventDefault();
            var formId = $(this).data('form-id');
            if (formId) {
                $('#' + formId).fadeIn('fast', function(){
                    var $checkboxBlock = $block.find('.js-il-chdp-checkbox-block');
                    $checkboxBlock.show();
                    _check_chdp_amount();
                });
                $(this).hide();
            }
        });

        if ($chdp_form.is(':visible')) {
            _check_chdp_amount();
        }
    };
    
    function _check_chdp_amount(){
        var amount_val = $block.find('.js-il-chdp-amount').val();
        var current_amount = parseFloat(amount_val) || 0;
        var rec_amount = parseFloat($block.find('.js-il-chdp-amount').data('rec')) || 0;
        var $warning = $block.find('.js-il-warning-message');
        var $checkbox = $block.find('.js-il-chdp-checkbox');
        var $button = $block.find('.js-il-chdp-button');

        if (amount_val && amount_val.trim() !== '' && !isNaN(current_amount) && current_amount > 0 && current_amount >= 1) {
            $button.prop('disabled', false);
        } else {
            $button.prop('disabled', true);
        }

        if (current_amount < rec_amount) {
            $block.find('.js-il-chdp-amount-error').html('Суммы не хватит для погашения текущего платежа');
            $warning.hide();
        } else {
            $block.find('.js-il-chdp-amount-error').html("&nbsp;");

            if (!$checkbox.is(':checked')) {
                $warning.show();
            } else {
                $warning.hide();
            }
        }
    };
    
    function _init_pdp(){
        var $pdp_form = $block.find('.js-il-pdp-form');
        var $pdp_button = $block.find('.js-il-pdp-button');
        var $pdp_accept_block = $block.find('.js-il-pdp-accept-block');
        var $pdp_checkbox = $block.find('.js-il-pdp-checkbox');
        var $pdp_code_button = $block.find('.js-il-pdp-code-button');

        $pdp_code_button.prop('disabled', true);
        $pdp_checkbox.change(function(){
            $pdp_code_button.prop('disabled', !$(this).is(':checked'));
        });

        if ($pdp_checkbox.is(':checked')) {
            $pdp_code_button.prop('disabled', false);
        }
        
        $pdp_button.click(function(){
            if (!app.need_accept) {
                $pdp_form.submit();
            } else {
                $pdp_accept_block.show();
                _send_pdp_sms();
            }
        })
        
        $block.find('.js-il-pdp-code-repeat').click(function(e){
            e.preventDefault();
            if (!$(this).hasClass('inactive')) {
                _send_pdp_sms();
            }
        });
        
        $block.find('.js-il-pdp-code-button').click(function(){
            if (!$(this).hasClass('loading') && $pdp_checkbox.is(':checked')) {
                _check_pdp_sms();
            }
        })
        
    };

    function _check_pdp_sms(){
        var _data = {
            action: 'check',
            phone: app.phone,
            code: $block.find('.js-il-pdp-code').val(),
        };
        $.ajax({
            url: 'ajax/sms.php',
            data: _data,
            beforeSend: function(){
                $block.find('.js-il-pdp-code-button').addClass('loading')
            },
            success: function(resp){
                if (resp.success)
                {
                    $block.find('.js-il-pdp-form').submit();
                }
                else
                {
                    // код не совпадает
                    if (resp.accept_try == 1) {
                        $block.find('.js-il-pdp-code-error').html('Код не совпадает<br />У Вас осталась последняя попытка после чего аккаунт будет заблокирован').show();
                    } else if (resp.accept_try > 1) {
                        $block.find('.js-il-pdp-code-error').html('Код не совпадает<br />У Вас осталась попыток: '+resp.accept_try).show();
                    } else {
                        $block.find('.js-il-pdp-code-error').html('Код не совпадает').show();                        
                    }
                    $block.find('.js-il-pdp-code-button').removeClass('loading')
                }
            }

        });
    
    }
    
    function _send_pdp_sms(){
        $.ajax({
            url: 'ajax/sms.php',
            data: {
                action: 'send',
                phone: app.phone,
                flag: 'АСП'
            },
            success: function(resp){
                if (!!resp.error)
                {
                    if (resp.error == 'sms_time')
                        app.set_pdp_timer(resp.time_left);
                    else
                        console.log(resp);
                }
                else
                {
                    app.set_pdp_timer(resp.time_left || 0);

                    if (!!resp.developer_code)
                        $block.find('.js-il-pdp-code').val(resp.developer_code);
                }
            }
        });
        
    }
    
    function _check_chdp_sms(){
        var _data = {
            action: 'check',
            phone: app.phone,
            code: $block.find('.js-il-chdp-code').val(),
        };
        $.ajax({
            url: 'ajax/sms.php',
            data: _data,
            beforeSend: function(){
                $block.find('.js-il-chdp-code-button').addClass('loading')
            },
            success: function(resp){
                if (resp.success)
                {
                    $block.find('.js-il-chdp-form').submit();
                }
                else
                {
                    // код не совпадает
                    if (resp.accept_try == 1) {
                        $block.find('.js-il-chdp-code-error').html('Код не совпадает<br />У Вас осталась последняя попытка после чего аккаунт будет заблокирован').show();
                    } else if (resp.accept_try > 1) {
                        $block.find('.js-il-chdp-code-error').html('Код не совпадает<br />У Вас осталась попыток: '+resp.accept_try).show();
                    } else {
                        $block.find('.js-il-chdp-code-error').html('Код не совпадает').show();                        
                    }
                    $block.find('.js-il-chdp-code-button').removeClass('loading')
                }
            }

        });
    
    }
    
    function _send_chdp_sms(){
        $.ajax({
            url: 'ajax/sms.php',
            data: {
                action: 'send',
                phone: app.phone,
                flag: 'АСП'
            },
            success: function(resp){
                if (!!resp.error)
                {
                    if (resp.error == 'sms_time')
                        app.set_chdp_timer(resp.time_left);
                    else
                        console.log(resp);
                }
                else
                {
                    app.set_chdp_timer(resp.time_left || 0);

                    if (!!resp.developer_code)
                        $block.find('.js-il-chdp-code').val(resp.developer_code);
                }
            }
        });
        
    }

    function _formatTimeRemaining(seconds) {
        var minutes = Math.floor(seconds / 60);
        var secs = seconds % 60;
        return minutes > 0 ? minutes + ' мин ' + secs + ' сек' : secs + ' сек';
    }
    
    app.set_pdp_timer = function(_seconds){

        clearInterval(app.pdp_timer);

        if (_seconds <= 0) {
            $block.find('.js-il-pdp-code-repeat').removeClass('inactive').html('отправить код еще раз').show();
            return;
        }

        app.pdp_timer = setInterval(function(){
            _seconds--;
            if (_seconds > 0)
            {
                var timeStr = _formatTimeRemaining(_seconds);
                $block.find('.js-il-pdp-code-repeat').addClass('inactive').html('Повторно отправить код можно через ' + timeStr).show();
            }
            else
            {
                $block.find('.js-il-pdp-code-repeat').removeClass('inactive').html('отправить код еще раз').show();

                clearInterval(app.pdp_timer);
            }
        }, 1000);

    };

    app.set_chdp_timer = function(_seconds){

        clearInterval(app.chdp_timer);

        if (_seconds <= 0) {
            $block.find('.js-il-chdp-code-repeat').removeClass('inactive').html('отправить код еще раз').show();
            return;
        }

        app.chdp_timer = setInterval(function(){
            _seconds--;
            if (_seconds > 0)
            {
                var timeStr = _formatTimeRemaining(_seconds);
                $block.find('.js-il-chdp-code-repeat').addClass('inactive').html('Повторно отправить код можно через ' + timeStr).show();
            }
            else
            {
                $block.find('.js-il-chdp-code-repeat').removeClass('inactive').html('отправить код еще раз').show();

                clearInterval(app.chdp_timer);
            }
        }, 1000);

    };

    ;(function(){
        _init();
        _init_chdp();
        _init_pdp();
        _init_documents();

        var $chdp_form = $block.find('.js-il-chdp-form');
        if ($chdp_form.length && $chdp_form.is(':visible')) {
            setTimeout(function() {
                _check_chdp_amount();
            }, 100);
        }
    })();
}
$(function(){
    $('.js-il-payment-buttons').each(function(){
        new InstallmentPaymentButtonsApp($(this));
    })
})