<form id="calculator_pre_order_form" action="{$smarty.server.REQUEST_URI}" method="POST" style="display: none;">
    <input type="hidden" name="order_id" value="{$order_data->order->order_id}" />
    <input type="hidden" name="period" value="{$order_data->order->period}" />
    <input type="hidden" name="percent" value="{$order_data->order->percent}" />
    <input type="hidden" name="local_time" value="" />
    <input type="hidden" name="juicescore_session_id" id="juicescore_session_id" value="" />
    <input type="hidden" name="juicescore_useragent" id="juicescore_useragent" value="" />
    <input type="hidden" name="finkarta_fp" id="finkarta_fp" value="" />

    <h4 class="text-orange">Вы можете изменить сумму займа</h4>
    <div id="calculator_pre_order">
        <div class="calculator_wrapper__full">
            <div class="ion_slider_wrapper">
                <span class="ion-btn ion-minus"></span>
                    <input type="text"
                           id="calculator_pre_order_money-range"
                           data-min="1000"
                           data-max="{$order_data->order->amount}"
                           data-step="1000"
                           name="amount"
                           value="{$order_data->order->amount}" />

                <span class="ion-btn ion-plus"></span>
            </div>
        </div>
        <div class="result">К возврату <span class="total"></span> ₽ до <span class="date"></span></div>
        <br />
        <button type="submit" class="button {if $config->snow}snow-relative primary{else}green{/if}">
            Получить заём <span class="calculator_pre_order__send_amount"></span> ₽
        </button>
    </div>
</form>
{literal}
    <script>
        function calculatePreOrder(amount) {
            let period = {/literal}{$order_data->order->period}{literal},
                percent = {/literal}{$order_data->order->percent * 0.01}{literal},
                now = new Date,
                payDate = new Date,
                total = amount * period * percent + amount;

            payDate.setDate(now.getDate() + parseInt(period));

            let month = [
                'января',   'февраля', 'марта',  'апреля',
                'мая',      'июня',    'июля',   'августа',
                'сентября', 'октября', 'ноября', 'декабря'
            ][payDate.getMonth()];

            $("#calculator_pre_order .result .total").text(total);
            $("#calculator_pre_order .result .date").text(payDate.getDate() + ' ' + month);
            $(".calculator_pre_order__send_amount").text(amount);
        }

        $(document).ready(function () {
            $("#calculator_pre_order_money-range ").ionRangeSlider({
                type: "single",
                postfix: "<span> ₽</span>",
                onChange: function (data) {
                    calculatePreOrder(data.from);
                },
                onStart: function (data) {
                    calculatePreOrder(data.from);
                },
                onUpdate: function (data) {
                    calculatePreOrder(data.from);
                },
            });

            const local_time = Math.floor((new Date()).getTime() / 1000);
            $("[name='local_time']").val(local_time);
        });
    </script>
    <style>
        #calculator_pre_order_form .calculator_wrapper__full {
            max-width: 80%;
            margin: auto;
        }
        
        .result > span {
            font-weight: 500;
            font-size: inherit;
        }

        #calculator_pre_order {
            max-width: 585px;
        }

        @media screen and (max-width: 758px) {
            #calculator_pre_order_form .irs-min, #calculator_pre_order_form .irs-max {
                display: none;
            }
        }
    </style>
{/literal}


<div id="accept_credit" style="display:none">
    {if !$settings->enable_loan_nk && !$order_data->order->have_close_credits}
        <p class="text-red">
            Произошла техническая ошибка.<br />Попробуйте повторить через час.
        </p>
    {else}
        <form id="accept_credit_form" onsubmit="ym(45594498,'reachGoal','click_cash'); return true;">

            {foreach $cards as $card}
                {if $card->id == $order_data->order->card_id}
                    <input type="hidden" name="rebill_id" value="{$card->rebill_id}" />
                {/if}
            {/foreach}

            <input type="hidden" name="order_id" value="{$order_data->order->order_id}" />
            <input type="hidden" name="card_id" value="{$order_data->order->card_id}" />
            <input type="hidden" name="uid" value="{$user->uid}" />
            <input type="hidden" name="number" value="{$order_data->order->id_1c}" />
            <input type="hidden" name="insurer" value="{$insurer}" />
            <input type="hidden" name="insure" value="{$insure}" />
            <input type="hidden" name="new_nk_flow_path" id="new_nk_flow_path" value="0" />
            <input type="hidden" name="service_recurent" value="1" />
            {if !($user_return_credit_doctor)}
            <input type="hidden" value="{$order_data->order->is_user_credit_doctor}" name="is_user_credit_doctor" id="credit_doctor_hidden" />
            {else}
                <input type="hidden" value="0" name="is_user_credit_doctor" id="credit_doctor_hidden" />
            {/if}

            <h2>К выплате {$order_data->order->amount} руб.</h2>
            <p>
                Подписать с помощью смс кода
                <br />
{*                <a class="contract_approve_file" href="{$config->root_url}/files/contracts/{$order_data->order->approved_file}" target="_blank">Договор</a>*}
            </p>

            <div class="accept_credit_actions">
                <div>
                    <input type="text" inputmode="numeric" autocomplete="one-time-code" name="sms_code" id="sms_code" value="" placeholder="Код из СМС" />
                    <div class="sms-code-error"></div>
                    <a href="javascript:void(0);" id="repeat_sms" data-phone="{$user->phone_mobile}">отправить код еще раз</a>
                </div>
                <div>
                    <button class="get_money_btn button medium {if $config->snow}snow-relative primary{else}green{/if}" type="submit">
                        {if $config->snow}
                            <img class="snow-man" src="design/orange_theme/img/holidays/snow/snow_man.png?v=2" alt="Получить деньги"/>
                        {/if}
                        Получить деньги
                    </button>
                </div>
            </div>

            <p>Подписывая договор я соглашаюсь и подписываю
                <a id="open_accept_documents" href="javascript:void(0);">документы</a>
                <a style="display:none" id="open_accept_documents_new" href="javascript:void(0);">документы.</a>
            </p>

        </form>
    {/if}
</div>
{literal}
<script type="text/javascript">
    function AcceptCreditApp()
    {
        let app = this;
        app.sms_timer;

        var _init_events = function(){

            app.show_accept_block = function () {
                $('#divide-order').hide();
                $('#accept_credit').show();
                app.send_sms();
            }

            $(".open_calculator__pre_order").on('click', function () {
                if ($(this).data('open_calc')) {
                    app.show_accept_block();
                } else {
                    $('#calculator_pre_order_form').show();
                    $(this).data('open_calc', 1)
                }
            });

            $(".open_accept_modal").on('click', function () {
                app.show_accept_block();
            });

            /**
             * Обновляем период и процент
             * чтобы взять период из заявки, отправьте только параметр percent или period = 0
             * @param percent
             * @param period
             * @param accept_button_name
             * @returns {{}}
             */
            app.updatePercentAndPeriod = function (data) {
                return $.ajax({
                    url: 'ajax/loan.php?action=change_period_and_percent',
                    data: data,
                    method: 'POST',
                    beforeSend: function () {
                        $('body').addClass('is_loading');
                    },
                    success: function (resp) {
                        if (resp['approved_file']) {
                            $('.contract_approve_file').attr('href', resp['approved_file']);
                            $('#divide-order').hide();
                            $('#accept_credit').show();
                            app.send_sms();
                        }
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

            $('#calculator_pre_order_form').on('submit', function (e) {
                e.preventDefault();
                let data = $(this).serialize();
                app.updatePercentAndPeriod(data);
            });

            var _click_counter_doc = 9;
            $('#credit_doctor_check').live('change', function(){
                let is_new_client = $("input[name='is_new_client']").val();
                if (_click_counter_doc > 0 && is_new_client != 1)
                {
                    $('#credit_doctor_check').attr('checked', true);
                    _click_counter_doc--;
                }
                {/literal}
                    {if !($user_return_credit_doctor)}
                        $('[name=is_user_credit_doctor]').val(1);
                    {/if}
                {literal}
            });

            $('#open_accept_modal').click(function(){
                $(this).closest('#open_accept_modal_wrapper').hide();
                $('#accept_credit').fadeIn();
                app.send_sms();
                {/literal}
                    {if $user->loan_history|count == 0}
                       sendMetric('reachGoal', 'get_money_btn_nk');
                    {else}
                       sendMetric('reachGoal', 'get_money_btn_pk');
                    {/if}
                {literal}
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
                code: $('#sms_code').val(),
                check_asp: 1,
                order_id: $('#accept_credit_form [name=order_id]').val()
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
                        if (resp.accept_try == 1)
                        {
                            $('.sms-code-error').html('Код не совпадает<br />У Вас осталась последняя попытка после чего аккаунт будет заблокирован').show();
                        }
                        else if (resp.accept_try > 1)
                        {
                            $('.sms-code-error').html('Код не совпадает<br />У Вас осталась попыток: '+resp.accept_try).show();
                        }
                        else
                        {
                            location.href = '/account/logout'
                        }
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
                        //new ExitpoolApp('{*/literal}{$order_data->order->order_id}{literal*}')
                        window.location.reload()
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
    });

    // рекурентные платежи
    $('#service_recurent_check').live('change', function(){
        if ($(this).is(':checked'))
            $('[name=service_recurent]').val(1);
        else
            $('[name=service_recurent]').val(0);
    });
</script>
{/literal}