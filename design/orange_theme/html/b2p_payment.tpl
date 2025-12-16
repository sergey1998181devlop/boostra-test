{$body_class = "gray" scope=parent}
 
{$add_order_css_js = true scope=parent}

{literal}
<style>
    [name="card_id"] {
        display:none
    }
    .payment-card-list {
        padding: 0 2rem;
    }
</style>
<script>

function PaymentApp()
{
    var app = this;
    
    app.payment_id;
    
    app.init = function(){
console.log('init')    
        
        $('.cancel_payment').click(function(){
            location.href = 'user';
        });
        
        $('#confirm_payment').click(function(e){
            app.confirm_payment(e);
        });
        
        $('#gpay').click(function(e){
            $('[name=card_id] [value=other]').attr('checked', true);
            app.confirm_payment(e);
        });
        
        $('.exitpool_button').click(function(e){
            e.preventDefault();
            
            app.send_exitpool();
        })
    };
    
    app.send_exitpool = function(){
        if ($('[name=payment_exitpool]:checked').length > 0)
        {
            var variant_id = $('[name=payment_exitpool]:checked').val();
            $.ajax({
                type: 'POST',
                url: '/ajax/exitpool.php',
                data: {
                    action: 'payment_exitpool',
                    variant_id: variant_id
                },
                beforeSend: function(){
                    $('.payment-block').addClass('loading');
                },
                success: function(){
                    location.href = 'user';
                }
            })
        }
        else
        {
            alert('Выберите вариант ответа');
        }
    }
    
    app.confirm_payment = function(e){
        
        var amount = $('[name=amount]').val();
        
        var insure = $('[name=insure]').val();
        var prolongation = $('[name=prolongation]').val();
        var code_sms = $('[name=code_sms]').val();
        var number = $('[name=number]').val();
        var user_id = $('[name=user_id]').val();
        
        if (amount > 0)
        {            
            if ($('[name=card_id]:checked').length > 0)
            {
                $('.payment-block-title').removeClass('error');
                $('.payment-block').addClass('loading');
                
                var $btn = $('#confirm_payment')
                var $gbtn = $('#gpay')

                var card_id = $('[name=card_id]:checked').val();
                
                $.ajax({
                    url: 'ajax/b2p_payment.php',
                    async: false,
                    data: {
                        action: 'get_payment_link',
                        amount: amount,
                        prolongation: prolongation, 
                        code_sms: code_sms,
                        insure: insure,
                        card_id: card_id,
                        number: number,
                        user_id: user_id
                    },
                    success: function(resp){
                        
                        if (!!resp.error)
                        {
                            $('.payment-block').removeClass('loading').addClass('error');
                            $('.payment-block-error p').html('Ошибка: '+resp.error);
                            e.preventDefault();
                            return false;                
                        }
                        else
                        {
                            app.payment_id = resp.payment_id;
                            app.check_state(app.payment_id);
//                            document.cookie = "go_payment=1; path=/;";

                            $btn.attr('href', resp.payment_link);
                            $gbtn.attr('href', resp.payment_link);
                            
                            
                            
                            return true;
                        }
                        
                    }
                })
            }
            else
            {
                $('.payment-block-title').addClass('error');
            }
        }
        else
        {
            $('.payment-block').removeClass('loading').addClass('error');
            $('.payment-block-error p').html('Сумма должна быть больше нуля.');
            
            e.preventDefault();
            return false;
        }
    };
    
    
    app.check_state = function(payment_id){
        app.check_timeout = setTimeout(function(){
            $.ajax({
                url: 'ajax/b2p_payment.php',
                data: {
                    action: 'get_state',
                    payment_id: app.payment_id,
                },
                success: function(resp){
console.log(resp)
                    if (!!resp.error)
                    {
                        $('.payment-block').removeClass('loading').addClass('error');
                        $('.payment-block-error p').html('Ошибка: '+resp.error);

                    }
                    else
                    {
                        if (resp.Status == 'CONFIRMED')
                        {
                            if ($('.payment-block-exitpool').length > 0)
                            {
                                $('.payment-block').removeClass('loading').addClass('exitpool');
                            }
                            else
                            {
                                $('.payment-block').removeClass('loading').addClass('success');
                                $('.js-payment-block-success p').html('Спасибо, оплата принята.');
                            }
                        }
                        else if (resp.Status == 'REJECTED')
                        {
                            $('.payment-block').removeClass('loading').addClass('error');
                            $('.payment-block-error p').html('Не получилось оплатить<br />'+resp.Message);
                        }
                        else
                        {
                            app.check_state();
                            
                        }
                    }
                }
            })
        }, 5000);
    }
    
    ;(function(){
        app.init();
    })();
};
$(function(){
    new PaymentApp();
})


  
</script>

{/literal}

<section id="private">
	<div>
		<div class="page-title">Подтверждение платежа</div>
        <div class="payment-block">
            
            <input type="hidden" name="amount" value="{$amount}" />

            <input type="hidden" name="user_id" value="{$user->id}" />
            <input type="hidden" name="number" value="{$number}" />
            <input type="hidden" name="insure" value="{$insure}" />
            <input type="hidden" name="prolongation" value="{$prolongation}" />
            <input type="hidden" name="code_sms" value="{$code_sms}" />
            
            <div class="payment-block-loading"></div>
            
            <div class="payment-block-success js-payment-block-success">
                <p>Оплата прошла успешно</p>
                <button  class="button big button-inverse cancel_payment" type="button">Продолжить</button>
            </div>
            <div class="payment-block-error">
                <p>Не удалось оплатить</p>
                <button  class="button big button-inverse cancel_payment" type="button">Продолжить</button>
            </div>
            
            <div class="payment-block-main">
                <p class="payment-block-title">Выберите карту для оплаты</p>
                
                <ul class="payment-card-list">
    	       		{foreach $cards as $card}
                    <li>
                        <input type="radio" name="card_id" id="card_{$card->id}" value="{$card->id}" {if $card@first}checked="true"{/if} />
                        <label for="card_{$card->id}">
                            <strong>{$card->pan}</strong>
                            <span>{$card->expdate}</span>
                        </label>
                    <br />
                    </li> 
                    {/foreach}
                    <li>
                        <input type="radio" id="card_other" name="card_id" value="other" {if !$cards}checked="true"{/if} />
                        <label for="card_other"><strong>Другая карта</strong></label>
                    </li> 
                </ul>
                
                {*}
                <a href="#" target="_blank" class="button big" id="gpay" type="button"></a>
                {*}
                
                <div class="payment-amount">
                    {$amount} руб
    
                    {if $error}
                    <div class="error" style="font-size:1rem;color:#f11;">
                        {$error}
                    </div>
                    {/if}
    
                </div>
                
                <div class="payment-actions">
                    <p class="loading-text">
                        Подождите, пока выполняется запрос
                        <button class="button big button-inverse cancel_payment" type="button">Отмена</button>
                    </p>
                    <button  class="button big button-inverse cancel_payment" type="button">Отменить</button>
                    <a href="javascript:void(0)" class="button big" id="confirm_payment" type="button">Оплатить</a>
                </div>
            </div>
            
            {if $have_exitpool}
            <div class="payment-block-exitpool">
                <div class="payment-block-exitpool-success">Оплата прошла успешно</div>
                <p class="payment-block-title">Скажите пожалуйста, по какой причине Вы не смогли оплатить заём вовремя?</p>
                <p><small>Опрос анонимный</small></p>
                <ul class="payment-card-list">
                    {foreach $exitpool_variants as $variant}
                    <li>
                        <input type="radio" id="payment_exitpool_{$variant->id}" name="payment_exitpool" value="{$variant->id}" />
                        <label for="payment_exitpool_{$variant->id}"><strong>{$variant->variant}</strong></label>
                    </li>
                    {/foreach}
                </ul>
                <button  class="button big button-inverse exitpool_button" type="button">Продолжить</button>
            </div>
            {/if}

        </div>
        
                
	</div>
</section>