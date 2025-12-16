<div class="hidden">

    <div id="prolongation_block">
        <form>
            <input type="hidden" id="user_phone" value="{$user->phone_mobile}" />
            <div class="prolongation_title">
                Пролонгация по договору {$user->balance->zaim_number}
            </div>
            <div>
                <p>
                    Сумма оплаты для пролонгации по договору составляет: 
                    <span id="in_insure">
                        {if $user->id|@array_search:[299082, 278878, 246778, 153750]}
                            <span class="user_amount_pay">{$user->balance->ostatok_percents}</span>
                        {else}
                            {$user->balance->prolongation_amount}
                        {/if}
                    </span>
                    {if 1 || $user->choose_insure}
                    <span id="out_insure" style="display:none">{$user->balance->prolongation_amount-$user->balance->prolongation_summ_insurance}</span>
                    {/if}
                    руб
                </p>
                <div class="accept-block">
                    Нажимая на кнопку "Принять" вы подтверждаете что ознакомлились и согласны со следующим:
                    <ul id="prolongation_documents"></ul>

                </div>
            </div>
            
            <div class="prolongation_actions">
                <button type="button" id="prolongation_cancel" class="button button-inverse medium">Отказаться</button>
                <button type="button" id="prolongation_accept" class="button medium">Принять</button>
            </div>
        </form>
    </div>

    <div id="prolongation_sms_block">
        <form method="POST" action="user/payment" id="prolongation_confirm_form">
            <input type="hidden" id="status_input" value="" />
            <input type="hidden" name="number" value="{$user->balance->zaim_number}" />
            <input type="hidden" name="amount" id="amount_input" value="{$user->balance->prolongation_amount}" />
            <input type="hidden" name="prolongation" value="1" />
            <input type="hidden" name="insure" id="insure_input" value="{$user->balance->prolongation_summ_insurance}" />
            <div class="prolongation_actions">
                
                <span class="info" id="accept_info">На Ваш телефон {$user->phone_mobile} было отправлено СМС-сообщение с кодом для подтверждения пролонгации.</span>
                
                <div id="prolongation_sms">
                    <div>
                        <input type="input" name="code" id="sms_code" placeholder="Код из СМС"/>
                        <span class="error-info"></span>
                    </div>
                    <div id="repeat_sms"></div>
                </div>
                {*
                <div class="prolongation_actions" id="prolongation_sms">
                    <button type="button" id="prolongation_sms_cancel" class="button button-inverse medium">Назад</button>
                    <button type="button" id="prolongation_sms_confirm" class="button medium">Подтвердить</button>
                </div>
                *}
            </div>
        </form>
    </div>
    
    
    <div id="document_wrapper">
    
        <div>
            <div>
                <p style="text-align:right;padding-bottom:10px;">
                Директору МКК ООО «Бустра»
                <br />
                Смелову Сергею Борисовичу
                </p>
                <p style="text-align:right;padding-bottom:10px;">
                {$user->lastname|escape} {$user->firstname|escape} {$user->patronymic|escape} {$user->birth} г.р.
                </p>
                <h3 style="padding:20px 0;text-align:center;">
                ЗАЯВЛЕНИЕ
                <br />
                о пролонгации договора микрозайма
                </h3>
                <p style="padding-bottom:10px;">
                Я, {$user->lastname|escape} {$user->firstname|escape} {$user->patronymic|escape} {$user->birth} г.р., 
                паспорт гражданина Российской Федерации; {$user->passport_serial}, 
                выдан {$user->passport_issued} {$user->passport_date}г.  ,  
                зарегистрирован  по  адресу:    
                {if $user->Regindex}{$user->Regindex}, {/if}
                {if $user->Regregion}{$user->Regregion} {$user->Regregion_shorttype}, {/if}
                {if $user->Regcity}{$user->Regcity} {$user->Regcity_shorttype}, {/if}
                {if $user->Regstreet}{$user->Regstreet} {$user->Regstreet_shorttype}, {/if}
                {if $user->Reghousing}д. {$user->Reghousing}, {/if}
                {if $user->Regbuilding}стр. {$user->Regbuilding}, {/if}
                {if $user->Regroom}кв. {$user->Regroom}, {/if}
                являясь Заемщиком по договору микрозайма No {$user->balance->zaim_number} от {$balance->zaim_date} г., 
                прошу рассмотреть возможность пролонгации данного договора.
                </p>
                <p style="padding-bottom:10px;">
                
                {if 1 || $user->choose_insure}
                <input type="checkbox" id="choose_insure" {if $user->id|@array_search:[299082, 278878, 246778, 153750]}checked="" {/if} data-ininsure="{$user->balance->prolongation_amount}" data-outinsure="{$user->balance->prolongation_amount-$user->balance->prolongation_summ_insurance}" />
                <label for="choose_insure"></label>
                {/if}
                
                Я проинформирован (-а) и согласен с тем, что стоимость дополнительной  
                услуги «Страхование от несчастного случая» составляет {$prolongation_insure_percent}% от непогашенной суммы займа.
                <br />
                Я  согласен  со  списанием  денежных  средств  с  банковской  карты,  
                прикрепленной  в  моем  Личном  кабинете,  
                в  счет  оплаты дополнительной услуги «Страхование от несчастного случая».
                </p>
                <br /><br />
                <p>
                    Подпись:  АСП
                </p>
            <div class="prolongation_actions">
                <button type="button" class="js-close-document button button-inverse medium">Отказаться</button>
                <button type="button" id="prolongation_accept" class="js-accept-document button medium">Принять</button>
            </div>
            </div>

            
        </div>
    </div>
    


</div>

<script type="text/javascript">
    ;function ProlongationApp()
    {
        var app = this;
        
        app.sms_sent = 0;
        app.sms_timer;

        app.insure_counter = {if $user->id == 153750} 0 {else} 9 {/if};
        
        var _init = function(){
            
            $('.js-prolongation-open-modal').click(function(e){
                e.preventDefault();
                
                var $this = $(this);
                var _number = $(this).data('number');
                
                if ($this.hasClass('loading'))
                    return false;
                
                $.ajax({
                    url: 'ajax/prolongation.php',
                    data: {
                        action: 'get_documents',
                        number: _number
                    },
                    beforeSend: function(){
                        $this.addClass('loading');
                        $('body').addClass('loading')
                    },
                    success: function(resp){

                        if(resp.error) {
                            alert(resp.error);
                            return;
                        }
                        
                        $('#prolongation_documents').html('');
                        if (!!resp.documents)
                        {
                            $.each(resp.documents, function(k, item){
                                var $li = '<li>';
                                $li += '<a href="'+item.file+'" class="js-open-document">'+item.name+'</a>'
                                $li += '</li>';
                                $('#prolongation_documents').append($li);
                                $('#document_frame').attr('src', item.file);
                            });
                        }

                        $this.removeClass('loading');

                        app.open_info_modal();
                    }
                })
                
                $('#choose_insure').change(function(){
                    if (app.insure_counter > 0)
                    {
                        $('#choose_insure').attr('checked', true);
                        app.insure_counter--;
                    }
                    else
                    {
                        var in_insure = $(this).data('ininsure');
                        var out_insure = $(this).data('outinsure');
                        if ($(this).is(':checked'))
                        {
                            $('#insure_input').val(1);
                            $('#in_insure').show();
                            $('#out_insure').hide();
                            $('#amount_input').val(in_insure)
                            
                        }
                        else
                        {
                            $('#insure_input').val(0);
                            $('#in_insure').hide();
                            $('#out_insure').show();
                            $('#amount_input').val(out_insure)
                        
                        }
                    }
                })
            });
            
            $('#prolongation_cancel').click(function(){
                $('#status_input').val(0);
                $('#accept_info').hide();
                $('#cancel_info').show();
                
                $.magnificPopup.close();
            });
            
            $('#prolongation_accept').click(function(){
                $('#status_input').val(1);
                $('#accept_info').show();
                $('#cancel_info').hide();
                
                if (!app.sms_sent)
                    app.send_sms();
                
                app.open_sms_modal();
            });
            
            $('#prolongation_sms_cancel').click(function(){
                app.open_info_modal();
            });
            
            $('#prolongation_sms_confirm').click(function(){
                if ($('#sms_code').val() == '')
                {
                    $('#prolongation_sms').addClass('error');
                    $('#prolongation_sms .error-info').html('Введите код из СМС');
                }
                else
                {
                    $('#prolongation_sms').removeClass('error')

                    app.check_sms();
                }
                
            });
            
            $('#sms_code').keyup(function(){
                var _v = $(this).val();
                if (_v.length == 4)
                    app.check_sms();
            })
            
        };
        
        
        
        app.approve = function(){
// здесь делаем переход на страницу оплаты            
            $('#prolongation_confirm_form').submit();
        };
        
        app.open_info_modal = function(){
            $.magnificPopup.open({
        		items: {
        			src: '#prolongation_block'
        		},
        		type: 'inline',
                showCloseBtn: false,
                modal:true,
        	});
        };
        
        app.open_sms_modal = function(){
            $.magnificPopup.open({
        		items: {
        			src: '#prolongation_sms_block'
        		},
        		type: 'inline',
                showCloseBtn: false,
                modal:true,
        	});
        };
        
        app.send_sms = function(){
            var _phone = $('#user_phone').val();
            $.ajax({
                url: 'ajax/sms.php',
                data: {
                    phone: _phone,
                    action: 'send'
                },
                beforeSend: function(){
                    
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
                            $('#sms_code').val(resp.developer_code).change();
                    }

                }
            });
        };

        app.check_sms = function(){
            var _data = {
                action: 'check',
                phone: $('#user_phone').val(),
                code: $('#sms_code').val()
            };
            $.ajax({
                url: 'ajax/sms.php',
                data: _data,
                beforeSend: function(){
                    
                },
                success: function(resp){
console.log(resp);
                    if (resp.success)
                    {
                        app.approve();
                    }
                    else
                    {
                        // код не совпадает
                        $('#prolongation_sms').addClass('error');
                        $('#prolongation_sms .error-info').html(resp.soap_fault ? resp.error : 'Код не совпадает');
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
                    var _str = '<span>Повторно отправить код можно через '+_seconds+'сек</span>';
                    $('#repeat_sms').html(_str).show();
                }
                else
                {
                    $('#repeat_sms').html('<a class="js-send-repeat" href="#">Отправить код еще раз</a>').show();
                    
                    clearInterval(app.sms_timer);
                }
            }, 1000);
  
        };
        
        app.init_send_repeat = function(){
            $(document).on('click', '.js-send-repeat', function(e){
                e.preventDefault();
                
                app.send_sms();
            });
        }
        
        app.init_open_document = function(){
            $('.js-open-document').live('click', function(e){
                e.preventDefault();

                $.magnificPopup.open({
            		items: {
            			src: '#document_wrapper'
            		},
            		type: 'inline',
                    showCloseBtn: true,
                    modal:false,
            	});
                
            });
            
            $('.js-close-document').live('click', function(e){
                e.preventDefault();

                $.magnificPopup.close();
            })
            
            $('.js-accept-document').live('click', function(e){
                e.preventDefault(); 
                
                $('#status_input').val(1);
                $('#accept_info').show();
                $('#cancel_info').hide();
                
                if (!app.sms_sent)
                    app.send_sms();
                
                app.open_sms_modal();
            });
        }
        
        ;(function(){
            _init();
            app.init_send_repeat();
            app.init_open_document();
        })();
    };
    
    $(function(){
        new ProlongationApp();
    });
    
</script>