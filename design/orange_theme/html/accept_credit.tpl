<p class="accept_message">
    Поздравляем! По вашей заявке одобрено {$user->order['approved_amount']} руб.
    <br />
    {if 0 && $user_discount}
    Вы можете принять решение до {$user_discount->end_date|date}.
    {else}
    Вы можете принять решение до {$user->order['approved_period']}.
    {/if}
    <br />
    {if ($user->service_insurance == 0 && $user->order['have_close_credits'] == 0 || $user->order['is_default_way'] == 1 || $user->order['is_discount_way'] == 1 )}
        <div id="test_dev" style="display:none">
                <button type="button" class="button big gray">Получить заём test</button>
        </div>
        <style>
                                        .new_buttons .col {
                                            display: inline-block;
                                            margin: 0 15px;
                                            width: 45%;
                                            vertical-align: top;
                                        }
                                        @media screen and (max-width: 768px){
                                            .new_buttons .col {
                                                width: 100%;
                                                padding-bottom: 20px;
                                                border-bottom: 1px solid;
                                            }
                                        }
                                        .new_buttons .buttons {
                                            height: 100px;
                                            line-height: 100px;
                                            margin-bottom: 20px;
                                        }
                                        .new_buttons .buttons .button {
                                            background: -webkit-linear-gradient(356deg, rgb(33, 202, 80) 0%, rgb(66, 242, 115) 95%, rgb(68, 245, 117) 100%);
                                            background: -o-linear-gradient(356deg, rgb(33, 202, 80) 0%, rgb(66, 242, 115) 95%, rgb(68, 245, 117) 100%);
                                            background: -ms-linear-gradient(356deg, rgb(33, 202, 80) 0%, rgb(66, 242, 115) 95%, rgb(68, 245, 117) 100%);
                                            background: -moz-linear-gradient(356deg, rgb(33, 202, 80) 0%, rgb(66, 242, 115) 95%, rgb(68, 245, 117) 100%);
                                            background: linear-gradient(94deg, rgb(33, 202, 80) 0%, rgb(66, 242, 115) 95%, rgb(68, 245, 117) 100%);
                                            border: none;
                                        }
                                        .new_buttons .descriptions {
                                            text-align: justify;
                                        }
                                        .new_buttons .descriptions a {
                                            text-decoration: underline;
                                        }
        </style>
        <div class="new_buttons" id="new_buttons">
                <div class="col">
                    <div class="buttons">
                        <button type="button" class="button big" id="test_open_accept_green">Получить заём на льготных условиях *</button>
                    </div>
                    <div class="descriptions">
                        Нажатием на эту кнопку выражаю свое желание заключить договор страхования
                        соответственно
                        <a href="https://boostra.ru/files/docs/pravila--195-kombinirovannogo-strahovaniya-ot-neschastnyh-sluchaev-i-boleznej.pdf" target="_blank">правилам</a>
                        и
                        <a href="https://www.boostra.ru/files/docs/Памятка об услуге страхования.pdf" target="_blank">памятке,</a>
                        страховая премия <span class="js-insure-amount">
                        {$approved_amount = $user->order['approved_amount']|replace:' ':''}
                        {if $approved_amount <= 2000}
                            {$insure_flow = 0.23}
                        {elseif $approved_amount <= 4000}
                            {$insure_flow = 0.18}
                        {elseif $approved_amount <= 7000}
                            {$insure_flow = 0.15}
                        {elseif $approved_amount <= 10000}
                            {$insure_flow = 0.14}
                        {elseif $smarty.cookies['utm_source']=='sms'} 
                            {$insure_flow = 0.33}
                        {else} 
                            {$insure_flow = 0.13}
                        {/if}
                        составляет {$approved_amount * $insure_flow} руб.
                    </span>, срок страхования 30 дней, страховая сумма <span class="js-insure-premia">{$approved_amount * $insure_flow * 20} руб</span> 
                        <span>
                            Суммы расчитываются исходя из суммы займа, указанные в заявке.
                        </span>
                        <br>
                        <br>
                        <span>
                            * Льгота в виде скидки {$amount_of_discount}% предоставляется на ежедневную процентную ставку, которая после скидки составляет {$discount_rate}% в день.
                        </span>
                    </div>
                </div>
                <div class="col">
                    <div class="buttons">
                        <div type="button" style="text-decoration: underline;" id="test_open_accept_gray">Заём на общих условиях на {$configured_term} дней *</div>
                    </div>
                    <div class="descriptions">
                        Обязуюсь исполнить обязательства по займу без возникновения просроченной задолженности.
                        Беру на себя возможные риски, связанные с потерей моей трудоспособности в случае моей
                        смерти, а также получения инвалидности (I) и (II) групп.
                        <br>
                        <br>
                        <span>
                            * Cтавка по займу 1% в день.
                        </span>
                    </div>
                </div>
        </div>
    {else}
        <button type="button" class="button big green" id="open_accept_modal">Получить заём</button>
    {/if}
</p>

{literal}
<style>
</style>
{/literal}
<div id="accept_credit" style="display:none">
    <form id="accept_credit_form" onsubmit="ym(45594498,'reachGoal','click_cash'); return true;">
        
        {foreach $cards as $card}            
            {if $card->id == $user->order['card_id']}
            <input type="hidden" name="rebill_id" value="{$card->rebill_id}" />
            {/if}
        {/foreach}
        
        <input type="hidden" name="order_id" value="{$user->order['id']}" />
        <input type="hidden" name="card_id" value="{$user->order['card_id']}" />
        <input type="hidden" name="uid" value="{$user->uid}" />
        <input type="hidden" name="number" value="{$user->order['1c_id']}" />
        <input type="hidden" name="insurer" value="{$insurer}" />
        <input type="hidden" name="insure" value="{$insure}" />
        <input type="hidden" name="new_nk_flow_path" id="new_nk_flow_path" value="0" />
        
        <h2>К выплате {$user->order['approved_amount']} руб.</h2>
        <p>
            Подписать с помощью смс кода
            <br />
            {if $is_admin}
            <a href="{$config->root_url}/files/specials/dogovor_150222.pdf" target="_blank">Договор</a>
            {else}
            <a id="old_contract" href="{$config->root_url}/files/contracts/{$user->order['approved_file']}" target="_blank">Договор</a>
            <a id="gray_contract" style="display:none"  href="{$config->root_url}/files/contracts/{$user->order['gray_approved_file']}" target="_blank">Договор</a>
            <a id="green_contract" style="display:none"  href="{$config->root_url}/files/contracts/{$user->order['green_approved_file']}" target="_blank">Договор</a>
            {/if}
            {*if !$is_admin && $user->order['strah_file']} 
            <br />
            <a href="{$user->order['strah_file']}" target="_blank">Oферта о порядке заключения договоров добровольного страхования от несчастных случаев клиентов МКК ООО «Бустра» </a>
            {/if*}
            
        </p>

        <div class="accept_credit_actions">
            <div>
                <input type="text" name="sms_code" id="sms_code" value="" placeholder="Код из СМС" />
                <span class="sms-code-error"></span>
                <a href="javascript:void(0);" id="repeat_sms" data-phone="{$user->phone_mobile}">отправить код еще раз</a>
            </div>
            <div>
                <button class="button medium green" type="submit">Получить заём</button>
            </div>
        </div>
        
        <p>Подписывая договор я соглашаюсь и подписываю <a id="open_accept_documents" href="javascript:void(0);">документы</a><a style="display:none" id="open_accept_documents_new" href="javascript:void(0);">документы.</a></p>

    </form>
</div>
{literal}
<script type="text/javascript">
    function AcceptCreditApp()
    {
        var app = this;
        
        app.sms_timer;
        
        var _init_events = function(){
            
            $('#test_open_accept_green').click(function(){
                $('#new_buttons').hide();

                $('#old_contract').hide();
                $('#green_contract').fadeIn();
                $("#new_nk_flow_path").val("green");

                $('#accept_credit').fadeIn();

                $('#open_accept_documents').hide();
                $('#open_accept_documents_new').fadeIn();

                app.send_sms();                
            });

            $('#test_open_accept_gray').click(function(){
                $('#new_buttons').hide();
                $('#accept_credit').fadeIn();

                $('#old_contract').hide();
                $('#gray_contract').fadeIn();
                $("#new_nk_flow_path").val("gray");

                $('#open_accept_documents').hide();
                $('#open_accept_documents_new').fadeIn();

                app.send_sms();                
            });

            $('#open_accept_modal').click(function(){
            
                $(this).hide();
                $('#accept_credit').fadeIn();
                
                app.send_sms();
                
            });
            
            $('#open_accept_documents').click(function(){
                $('.checkbox-item').hide();
                $.magnificPopup.open({
            		items: {
            			src: '#accept'
            		},
            		type: 'inline',
                    showCloseBtn: true
            	});
            });

            $('#open_accept_documents_new').click(function(){
                $('#service_insurance_div').remove();
            
                $('.checkbox-item').hide();
                $.magnificPopup.open({
            		items: {
            			src: '#accept'
            		},
            		type: 'inline',
                    showCloseBtn: true
            	});
            });
            
            $('#repeat_sms').click(function(e){
                e.preventDefault();
                if (!$(this).hasClass('inactive'))
                    app.send_sms();
            })
            
            $('#accept_credit_form').submit(function(e){
                e.preventDefault();
                
                if ($('.js-need-verify').not(':checked').length > 0)
                {
                    $('#not_checked_info').show();
                    $.magnificPopup.open({
                		items: {
                			src: '#accept'
                		},
                		type: 'inline',
                        showCloseBtn: true
                	});
                    
                }
                else
                {
                    app.check_sms();
                }
            });
            
            $('#sms_code').keyup(function(){
                var _v = $(this).val();
                if (_v.length == 4)
                    app.check_sms();
            })
            
        };
        
        app.send_sms = function(){
            var _phone = $('#repeat_sms').data('phone')
            $.ajax({
                url: 'ajax/sms.php',
                data: {
                    action: 'send',
                    phone: _phone,
                    flag: 'АСП'
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
                            $('#sms_code').val(resp.developer_code);
                    }
                }
            });
        };
        
        app.check_sms = function(){
            var _data = {
                action: 'check',
                phone: $('#repeat_sms').data('phone'),
                code: $('#sms_code').val()
            };
            $.ajax({
                url: 'ajax/sms.php',
                data: _data,
                beforeSend: function(){
                    $('#accept_credit_form').addClass('loading')
                },
                success: function(resp){
                    if (resp.success)
                    {
                        app.hold();
                    }
                    else
                    {
                        // код не совпадает
                        $('.sms-code-error').html(resp.soap_fault ? resp.error : 'Код не совпадает').show();
                        $('#accept_credit_form').removeClass('loading')
                    }
                }
                
            });
        }        
        
        app.hold = function(){
            
            var $form = $('#accept_credit_form');
            var _user_id = $form.find('[name=user_id]').val();
            var _card_id = $form.find('[name=card_id]').val();
            var _rebill_id = $form.find('[name=rebill_id]').val();
            var _order_id = $form.find('[name=order_id]').val();
            
            $.ajax({
                url: 'ajax/payment.php',
                data: {
                    'action': 'hold',
                    'user_id': _user_id,
                    'card_id': _card_id,
                    'rebill_id': _rebill_id,
                    'order_id': _order_id,
                },
                beforeSend: function(){
                    
                },
                success: function(resp){
                    console.log($.inArray(resp.ErrorCode, ['1005', '1054', '1057', '1058', '1059']));
                    
                    if (!!resp.ErrorCode && ($.inArray(resp.ErrorCode, ['1005', '1054', '1057', '1058', '1059']) !== -1))
                    {
                        $('#accept_credit_form').html('<h2 style="color:red;margin:30px 0;">Требуется замена карты</h2>').removeClass('loading');
                        $('.accept_message').remove();
                    } else if (resp.soap_fault) {
                        $('#accept_credit_form').html('<h2 style="color:red;margin:30px 0;">' + resp.error + '</h2>').removeClass('loading');
                        $('.accept_message').remove();
                    } else {
                        app.approve();
                    }
                }
            })
        };
        
        app.set_timer = function(_seconds){

            clearInterval(app.sms_timer);
            
            app.sms_timer = setInterval(function(){
                _seconds--;
                if (_seconds > 0)
                {
                    var _str = '<span>Повторно отправить код можно через '+_seconds+'сек</span>';
                    $('#repeat_sms').addClass('inactive').html(_str).show();
                }
                else
                {
                    $('#repeat_sms').removeClass('inactive').html('<a class="js-send-repeat" href="#">Отправить код еще раз</a>').show();
                    
                    clearInterval(app.sms_timer);
                }
            }, 1000);
  
        };
        
        app.approve = function(){
            var _data = $('#accept_credit_form').serialize();
            $.ajax({
                url: 'ajax/accept_credit.php',
                data: _data,
                beforeSend: function(){
                    
                },
                success: function(resp){
                      if (!!resp.error)
                      {
                        if (!!resp.error.Message)
                        {
                            if (resp.error.Message == 'Недостаточно средств на счете компании')
                                alert('Произошла ошибка. Попробуйте повторить через 30 минут.');
                            else
                                alert(resp.error.Message);
                        }
                        else
                        {
                            alert(resp.error);
                        }
                      } 
                      else
                      {
                        new ExitpoolApp('{/literal}{$user->order["id"]}{literal}')
                      } 
                        
                }
            })
        };
        
        ;(function(){
            _init_events();
        })();
    }
    $(function(){
        new AcceptCreditApp();
        {/literal}
        {if $user->order['utm_source'] == 'crm_auto_approve'}
        $('#test_open_accept_green').click();
        {/if}
        {literal}
    });
</script>
{/literal}
