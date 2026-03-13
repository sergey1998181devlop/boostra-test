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

        /* Дополнительно: анимация появления */
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
                content: "✓"; /* Символ галочки */
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background-color: #0a53be;
                border-color: #0a91ed;
                color: white;
                animation: checkAnim 0.3s ease;
            }
        }

        .autoconfirm_repeat_order h1 {
            margin-bottom: 5%;
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

        .promo-block {
            padding: 15px;
            border: 1px black dashed;
            button {
                margin-top: 20px;
            }
        }
    </style>
{/literal}

<div id="autoconfirm_repeat_order">
    <div class="autoconfirm__container">
        <h1>Подпишите документы</h1>
        <div class="autoconfirm__list_wrapper">
            <div class="autoconfirm__list_item">
                <div id="autoconfirm__header">
                    <button onclick="AutoConfirmRepeatApp.init_check_sms()" disabled type="button">Подписать <i class="bi bi-pencil-square"></i></button>
                    <div id="continue_href_wrapper">
                        <a class="continue_href" href="javascript:void(0)" onclick="AutoConfirmRepeatApp.continue()">Пропустить</a>
                    </div>
                </div>
                <div style="display: none" class="autoconfirm_sms_block js-autoconfirm-block" data-phone="{$user->phone_mobile}">
                    <div class="autoconfirm_actions">
                        <span class="info" id="accept_info">На Ваш телефон {$user->phone_mobile} было отправлено СМС-сообщение с кодом для подтверждения.</span>
                        <div id="autoconfirm_sms">
                            <div>
                                <input type="text" autocomplete="one-time-code" name="code" maxlength="4" placeholder="Код из СМС" />
                                <span class="js-autoconfirm-error error-info"></span>
                            </div>
                            <div class="js-repeat-autoconfirm-sms"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="autoconfirm_repeat_order-docs_wrapper">
                {include file="accept_credit/docs_list_main.tpl" docs_default=1 accept_contract_url=$individual_max_amount_doc_url}
            </div>
            <div class="autoconfirm_repeat_order_footer">
                <br/>
                <small class="small-text"><sup>*</sup>Сумма {if $rcl_loan}транша{else}займа{/if} скорректирована на максимально доступное для выдачи значение с учетом результатов предварительного скоринга.</small>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
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
            $(".autoconfirm_repeat_order-docs_wrapper, #autoconfirm_repeat_order #autoconfirm__header, #autoconfirm_repeat_order h1, .autoconfirm_repeat_order_footer").hide();
            $("#autoconfirm_repeat_order .autoconfirm_sms_block").fadeIn();
            this.send_sms();
        },
        send_sms: function () {
            const _phone = $('#autoconfirm_repeat_order .js-autoconfirm-block').data('phone');

            $.ajax({

                url: 'ajax/sms.php',
                data: {
                    action: 'send',
                    phone: _phone,
                    flag: 'autoconfirm',
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
            const _data = {
                action: 'check_autoconfirm',
                phone: $('#autoconfirm_repeat_order .js-autoconfirm-block').data('phone'),
                code: $('#autoconfirm_repeat_order [name="code"]').val(),
                flag: 'repeat_new_order',
                current_order_id: '{/literal}{$current_order_id}{literal}',
            };
            $.ajax({
                url: 'ajax/sms.php',
                data: _data,
                beforeSend: function () {
                    $('#autoconfirm_repeat_order .js-autoconfirm-block').addClass('loading')
                },
                success: function (resp) {
                    if (resp.success) {
                        $('#autoconfirm_repeat_order .js-autoconfirm-form').removeClass('js-autoconfirm-form');
                        $('#autoconfirm_repeat_order .js-autoconfirm-block').removeClass('error');
                        $('#autoconfirm_repeat_order .js-autoconfirm-error').html('');
                        $("#autoconfirm_repeat_order .autoconfirm_sms_block").html("<p>Происходит переход на оплату...</p>")

                        AutoConfirmRepeatApp.pay_form.submit();
                    } else {
                        $('#autoconfirm_repeat_order .js-autoconfirm-block').removeClass('loading').addClass('error');
                        $('#autoconfirm_repeat_order .js-autoconfirm-error').html(resp.error)
                    }
                }
            });
        },
        set_timer: function (_seconds) {
            clearInterval(this.sms_timer);
            this.sms_timer = setInterval(function () {
                _seconds--;
                if (_seconds > 0) {
                    let _str = '<span>Повторно отправить код можно через ' + _seconds + 'сек</span>';
                    $('#autoconfirm_repeat_order .js-repeat-autoconfirm-sms').addClass('inactive').html(_str).show();
                } else {
                    $('#autoconfirm_repeat_order .js-repeat-autoconfirm-sms').removeClass('inactive').html('<a class="js-send-repeat" href="#">Отправить код еще раз</a>').show();
                    clearInterval(AutoConfirmRepeatApp.sms_timer);
                }
            }, 1000);
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

    $('#autoconfirm_repeat_order [name="code"]').keyup(function() {
        const _v = $(this).val();
        if (_v.length === 4) {
            AutoConfirmRepeatApp.check_sms();
        }
    });

    $("#autoconfirm_repeat_order .docs_wrapper input").on('change', function () {
        AutoConfirmRepeatApp.validateCheckBox();
    });

    $(document).ready(function () {
        const $block = $('#autoconfirm_repeat_order');
        $block.find('.toggle-conditions-accept').click(function () {
            $block.find('.conditions').slideToggle();
        });
    });
    {/literal}
</script>
