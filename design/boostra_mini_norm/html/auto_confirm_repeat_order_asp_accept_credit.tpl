{literal}
    <style>
        #autoconfirm_repeat_order_modal {
            .modal-wrapper {
                max-width: 520px;
                position: relative;
                margin: auto;
                padding: 25px;
                background: #ffffff;
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                border: 1px solid rgba(0, 0, 0, 0.1);
                z-index: 1000;
                font-family: 'Arial', sans-serif;
                color: #333;
                animation: fadeIn 0.3s ease-out;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        #autoconfirm_repeat_order {
            --font-size: 16px;
            width: 100%;
            padding: 15px;
            box-sizing: border-box;

            #autoconfirm_sms {
                text-align: center;
                input {
                    text-align: center;
                }
            }

            h1 {
                font-size: 32px;
                margin-bottom: 5%;
            }
            a {
                color: #000000;
            }
            button {
                background: #0a53be;
                color: white;
                border-radius: 10px;
                padding: 14px 20px;
                font-size: var(--font-size);
            }
            button:disabled {
                opacity: .75;
                cursor: no-drop;
            }
        }

        #autoconfirm_repeat_order .continue_href {
            color: grey;
            font-size: 14px;
        }

        #autoconfirm_repeat_order .small-text {
            font-size: 12px;
            display: block;
        }

        #autoconfirm_repeat_order #autoconfirm__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        #autoconfirm_repeat_order::backdrop {
            background-color: #0b0b0b;
        }

        .autoconfirm__container {
            max-width: 720px;
            margin: auto;
        }
        .autoconfirm__list_item {
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .autoconfirm__list_item label {
            div {
                display: flex;
                align-items: center;
                gap: 20px;
                font-size: var(--font-size);
                span {
                    flex: 1;
                }
            }
            input {
                display: none;
            }
            div::before {
                content: "";
                height: 20px;
                width: 20px;
                border: 2px solid #1e262e;
                display: block;
                border-radius: 5px;
            }
            input:checked + div::before {
                content: "✓";
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background-color: #0a53be;
                border-color: #0a91ed;
                color: white;
                animation: checkAnim 0.3s ease;
            }
        }

        @keyframes checkAnim {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @media screen and (max-width: 768px){
            #autoconfirm {
                --font-size: 14px;
                h1 {
                    font-size: 24px;
                    text-align: center;
                }
                button {
                    width: 100%;
                }
            }
        }
        .promocodes {
            text-align: center;
        }
        .promo-block {
            padding: 15px;
            border: 1px black dashed;
            button {
                margin-top: 20px;
            }
        }

        #close_modal {
            font-size: xxx-large;
        }

        #autoconfirm_repeat_order .autoconfirm_sms_block_top {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 8px;
        }

        #autoconfirm_repeat_order .js-autoconfirm-error {
            color: #dc3545;
            display: block;
            margin-top: 10px;
            font-size: 14px;
        }

        #autoconfirm_repeat_order h1 {
            margin-bottom: 5%;
        }

        #close_modal_container {
            max-height: 15px;
        }
    </style>
{/literal}

<div id="autoconfirm_repeat_order">
    <div class="autoconfirm__container">
        <div id="close_modal_container">
            <a href="javascript:void(0);" title="Close" id="close_modal">×</a>
        </div>
        <h1>Подпишите документы</h1>

        <div style="display: none" class="autoconfirm_sms_block_top js-autoconfirm-block" data-phone="{$user->phone_mobile}">
            <div class="autoconfirm_actions">
                <span class="info" id="accept_info">На Ваш телефон {$user->phone_mobile} было отправлено СМС-сообщение с кодом для подтверждения.</span>
                <div id="autoconfirm_sms">
                    <div>
                        <input type="text" inputmode="numeric" class="sms_code_modal" id="sms_code_modal" placeholder="Код из СМС" maxlength="4" />
                        <div class="sms-code-error"></div>
                        <div id="not_checked_info_modal" style="display:none">
                            <strong style="color:#f11">Вы должны согласиться со всеми условиями и ввести СМС-код</strong>
                        </div>
                    </div>
                    <a href="javascript:void(0);" id="repeat_sms" class="repeat_sms" data-phone="{$user->phone_mobile}">отправить код еще раз</a>
                </div>
            </div>
        </div>

        <div class="autoconfirm__list_wrapper">
            <div class="autoconfirm__list_item">
                <div id="autoconfirm__header">
                    <button onclick="AutoConfirmRepeatApp.init_check_sms()" disabled type="button">Подписать <i class="bi bi-pencil-square"></i></button>
                </div>
            </div>
            <div class="autoconfirm_repeat_order-docs_wrapper">
                {include file="accept_credit/docs_list_main.tpl" docs_default=1 accept_contract_url=$individual_max_amount_doc_url}
            </div>
            <div class="autoconfirm_repeat_order_footer">
                <br/>
                <small class="small-text"><sup>*</sup>Сумма займа скорректирована на максимально доступное для выдачи значение с учетом результатов предварительного скоринга.</small>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var $block = $('#accept_block_{$user_order["id"]}');
    {literal}
    const AutoConfirmRepeatApp = {
        sms_timer: null,
        pay_form: null,

        initAutoConfirmRepeatOrder: function (form_id) {
            $.magnificPopup.open({
                items: {src: '#autoconfirm_repeat_order_modal'},
                type: 'inline',
                showCloseBtn: false,
                modal: true,
            });

            this.pay_form = $(`#${form_id}`);
        },

        init_check_sms: function () {
            $(".autoconfirm_repeat_order-docs_wrapper, #autoconfirm_repeat_order #autoconfirm__header, #autoconfirm_repeat_order h1, .autoconfirm_repeat_order_footer, #close_modal_container").hide();
            $("#autoconfirm_repeat_order .autoconfirm_sms_block_top").fadeIn();
            this.send_sms();
        },

        send_sms: function () {
            const _phone = $('#autoconfirm_repeat_order .js-autoconfirm-block').data('phone');

            $.ajax({
                url: 'ajax/sms.php',
                data: {
                    action: 'send',
                    phone: _phone,
                    flag: 'АСП',
                },
                success: function (resp) {
                    if (!!resp.error) {
                        if (resp.error == 'sms_time') {
                            AutoConfirmRepeatApp.set_timer(resp.time_left);
                        } else {
                            console.log(resp);
                        }
                    } else {
                        AutoConfirmRepeatApp.set_timer(resp.time_left);
                    }
                }
            });
        },

        check_sms: function () {
            // Проверяем главный чекбокс (ищем без префикса, так как он внутри include)
            const agreeAllElement = $('#agree_all');
            console.log('Agree all element found:', agreeAllElement.length);
            console.log('Agree all checked:', agreeAllElement.is(':checked'));

            if (agreeAllElement.length === 0) {
                console.error('Element #agree_all not found!');
            } else if (!agreeAllElement.is(':checked')) {
                $('#autoconfirm_repeat_order .js-autoconfirm-block').addClass('error');
                $('#autoconfirm_repeat_order .js-autoconfirm-error').html('Вы должны согласиться со всеми условиями');
                return;
            }

            var _data = {
                action: 'check',
                phone: $('#autoconfirm_repeat_order .js-autoconfirm-block').data('phone'),
                code: $('#autoconfirm_repeat_order #sms_code_modal').val(),
                check_asp: 1,
                order_id: $block.data('order')
            };

            $.ajax({
                url: 'ajax/sms.php',
                data: _data,
                dataType: 'json',
                beforeSend: function () {
                    $('#autoconfirm_repeat_order .js-autoconfirm-block').removeClass('error').addClass('loading');
                    $('#autoconfirm_repeat_order .js-autoconfirm-error').html('');
                },
                success: function (resp) {
                    $block.find('[name=sms_code]').val(_data.code);
                    $('#autoconfirm_repeat_order .js-autoconfirm-block').removeClass('loading');

                    if (resp.success) {
                        $('#autoconfirm_repeat_order .js-autoconfirm-form').removeClass('js-autoconfirm-form');
                        $('#autoconfirm_repeat_order .js-autoconfirm-block').removeClass('error');
                        $('#autoconfirm_repeat_order .js-autoconfirm-error').html('');

                        $(".autoconfirm_sms_block_top").html("<p>Происходит переход на оплату...</p>");
                        AutoConfirmRepeatApp.approve();
                        AutoConfirmRepeatApp.pay_form.submit();
                    } else {
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
                            location.href = '/account/logout'
                        }
                    }
                },
                error: function(xhr, status, error) {
                    $('#autoconfirm_repeat_order .js-autoconfirm-block').removeClass('loading').addClass('error');
                    $('#autoconfirm_repeat_order .js-autoconfirm-error').html('Ошибка проверки кода');
                    console.log('Ajax error:', error);
                }
            });
        },

        set_timer: function (_seconds) {
            clearInterval(this.sms_timer);
            this.sms_timer = setInterval(function () {
                _seconds--;
                if (_seconds > 0) {
                    let _str = '<span>Повторно отправить код можно через ' + _seconds + ' сек</span>';
                    $('#autoconfirm_repeat_order .js-repeat-autoconfirm-sms').addClass('inactive').html(_str).show();
                } else {
                    $('#autoconfirm_repeat_order .js-repeat-autoconfirm-sms').removeClass('inactive').html('<a class="js-send-repeat" href="#">Отправить код еще раз</a>').show();
                    clearInterval(AutoConfirmRepeatApp.sms_timer);
                }
            }, 1000);
        },

        approve: function(){
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
                        {/literal}
                            var isAutoAcceptCrossOrders = {if $isAutoAcceptCrossOrders && $user_order['utm_source'] != 'cross_order'}1{else}0{/if};
                        {literal}
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
        },

        validateCheckBox: function () {
            const hasUnchecked = $("#autoconfirm_repeat_order .docs_wrapper input").is(":not(:checked)");
            $("#autoconfirm_repeat_order button").prop('disabled', hasUnchecked);
        },

        continue: function () {
            this.pay_form.submit();
        },
    }

    $("#autoconfirm_repeat_order .accept_confirm_href").one('click', function () {
        sendMetric('reachGoal','ead_dogovor_repeat_pk');
    });

    // Обработка ввода кода
    $('#autoconfirm_repeat_order #sms_code_modal').on('keyup', function() {
        var _v = $(this).val();
        if (_v.length == 4) {
            var uncheckedVerifyCheckboxes = $block.find('.js-need-verify').not(':checked');

            if (uncheckedVerifyCheckboxes.length === $block.find('.js-need-verify').length) {
                $block.find('input[name="credit_doctor_check"]').prop('checked', true);
                $block.find('input[name="star_oracle_check"]').prop('checked', true)
                $block.find('input[name="is_user_credit_doctor"]').val('1');
                $block.find('input[name="is_star_oracle"]').val('1')
                $block.find('input[name="agree_claim_value"]').val('0')
            } else if (uncheckedVerifyCheckboxes.length > 0) {
                $block.find('#not_checked_info').show();
                $block.find('#not_checked_info_modal').show();
            } else {
                $block.find('#not_checked_info_modal, #not_checked_info').hide();
                AutoConfirmRepeatApp.check_sms();
            }
        }

    });

    $(document).ready(function () {
        const $block = $('#autoconfirm_repeat_order');
        $block.find('.toggle-conditions-accept').click(function () {
            $block.find('.conditions').slideToggle();
        });

        // Валидация чекбоксов
        $("#autoconfirm_repeat_order .docs_wrapper input, #agree_all").on('change', function () {
            AutoConfirmRepeatApp.validateCheckBox();

            // Убираем ошибку если юзер поставил главный чекбокс
            const isChecked = $('#agree_all').is(':checked');
            console.log('Checkbox changed, agree_all is:', isChecked);

            if (isChecked) {
                $('#autoconfirm_repeat_order .js-autoconfirm-block').removeClass('error');
                $('#autoconfirm_repeat_order .js-autoconfirm-error').html('');
            }
        });

        // Убираем ошибку при начале ввода нового кода
        $('#autoconfirm_repeat_order #sms_code_modal').on('input', function() {
            if ($(this).val().length < 4) {
                $('#autoconfirm_repeat_order .js-autoconfirm-block').removeClass('error');
                $('#autoconfirm_repeat_order .js-autoconfirm-error').html('');
            }
        });
    });
    {/literal}
</script>