{$meta_title = "АСП договора" scope=parent}

{literal}
    <style>
        body {
            height: 100vh;
            overflow: hidden;
        }
        #ind_usloviya {
            color: #1E262E;
        }
        #autoconfirm {
            --font-size: 16px;
            width: 100%;
            padding: 15px;
            max-width: 100%;
            box-sizing: border-box;
            h1 {
                font-size: 32px;
            }
            a {
                color: #0a91ed;
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
            
            .autoconfirm__list_item {
                margin-top: 12px;
                margin-bottom: 12px;
            }
            
            .autoconfirm__list_item label {
                div {
                    gap: 12px;
                    align-items: flex-start;
                }
                div::before {
                    margin-top: 1px;
                    min-width: 20px;
                    width: 20px;
                    height: 20px;
                }
                input:checked + div::before {
                    font-size: 12px; /* Меньше галочка на мобильных */
                }
            }
            
            .autoconfirm__approved_notice {
                font-size: 18px;
                padding: 16px 20px;
            }
        }
        
        @media screen and (max-width: 480px) {
            #autoconfirm {
                --font-size: 13px;
                padding: 8px;
            }
            
            .autoconfirm__list_item label {
                div {
                    gap: 10px;
                }
                div::before {
                    width: 18px;
                    height: 18px;
                    min-width: 18px;
                }
                input:checked + div::before {
                    font-size: 11px;
                }
            }
            
            .autoconfirm__container {
                padding: 0 5px; /* Меньше отступы по бокам */
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

<div id="autoconfirm">
    <div class="autoconfirm__container">
        <h1>Подпишите документы</h1>
        <div class="autoconfirm__list_wrapper">
            <div class="autoconfirm__list_item">
                <label for="autoconfirm_all">
                    <input type="checkbox" value="1" id="autoconfirm_all" name="autoconfirm_all" />
                    <div>
                        <span>Я подписываю указанные ниже документы аналогом собственноручной подписи и согласен с указанными в перечне условиями и документами</span>
                    </div>
                </label>
            </div>
            <div class="autoconfirm__list_item">
                <button disabled type="button">Подписать и отправить на рассмотрение</button>
                <div class="hidden">
                    <div class="autoconfirm_sms_block js-autoconfirm-block" data-phone="{$user->phone_mobile}">
                        <div class="autoconfirm_actions">
                            <span class="info" id="accept_info">На Ваш телефон {$user->phone_mobile} было отправлено СМС-сообщение с кодом для подтверждения.</span>
                            <div id="autoconfirm_sms">
                                <div>
                                    <input type="input" autocomplete="one-time-code" name="code" class="js-autoconfirm-sms" maxlength="4" placeholder="Код из СМС" />
                                    <span class="js-autoconfirm-error error-info"></span>
                                </div>
                                <div class="js-repeat-autoconfirm-sms"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {foreach $docs_list as $idx => $doc}
                <div class="autoconfirm__list_item">
                    {if $doc.type == 'ind_usloviya'}
                        <div>
                            <a id="{$doc.type}" target="_blank" href="{$doc.url}">{$doc.title}</a>
                        </div>
                    {else}
                        <label for="autoconfirm_{$idx}">
                            <input type="checkbox" value="1" id="autoconfirm_{$idx}" name="autoconfirm_item_{$idx}" />
                            <div>
                                <a id="{$doc.type}" target="_blank" href="{$doc.url}">{$doc.title}</a>
                            </div>
                        </label>
                    {/if}

                </div>
            {/foreach}
        </div>
        {include 'promocode.tpl'}
    </div>
</div>

{capture name=page_scripts}
    <script type="text/javascript">
        {literal}
        function autoconfirm() {
            $.magnificPopup.open({
                items: {src: '.autoconfirm_sms_block'},
                type: 'inline',
                showCloseBtn: false,
                modal: true,
            });
            send_sms();
        }

        $("#ind_usloviya").one('click', function () {
            $.cookie('autoconfirm_disabled', 1);
            sendMetric('reachGoal','read_dogovor_nk');
        });

        send_sms = function () {
            const _phone = $('.js-autoconfirm-block').data('phone');
            $.ajax({

                url: 'ajax/sms.php',
                data: {
                    action: 'send',
                    phone: _phone,
                    flag: 'autoconfirm',
                },

                success: function (resp) {
                    if (!!resp.error) {
                        if (resp.error == 'sms_time')
                            set_timer(resp.time_left);
                        else
                            console.log(resp);
                    } else {
                        set_timer(resp.time_left);
                    }
                }
            });
        };

        check_sms = function () {
            const _data = {
                action: 'check_autoconfirm',
                phone: $('.js-autoconfirm-block').data('phone'),
                code: $('.js-autoconfirm-sms').val(),
            };
            $.ajax({
                url: 'ajax/sms.php',
                data: _data,
                beforeSend: function () {
                    $('.js-autoconfirm-block').addClass('loading')
                },
                success: function (resp) {
                    if (resp.success) {
                        $('.js-autoconfirm-form').removeClass('js-autoconfirm-form');
                        $('.js-autoconfirm-block').removeClass('error');
                        $('.js-autoconfirm-error').html('');
                        $(".autoconfirm_sms_block").html("<p>Происходит переход в личный кабинет...</p>")

                        window.location.href = "/user";
                    } else {
                        $('.js-autoconfirm-block').removeClass('loading');
                        $('.js-autoconfirm-error').html(resp.error)
                        $('.js-autoconfirm-block').addClass('error');
                    }
                }
            });
        }


        let sms_timer;
        set_timer = function (_seconds) {
            clearInterval(sms_timer);
            sms_timer = setInterval(function () {
                _seconds--;
                if (_seconds > 0) {
                    var _str = '<span>Повторно отправить код можно через ' + _seconds + 'сек</span>';
                    $('.js-repeat-autoconfirm-sms').addClass('inactive').html(_str).show();
                } else {
                    $('.js-repeat-autoconfirm-sms').removeClass('inactive')
                        .html('<a class="js-send-repeat" href="#">Отправить код еще раз</a>').show();

                    clearInterval(sms_timer);
                }
            }, 1000);
        };

        $('.js-autoconfirm-sms').keyup(function() {
            const _v = $(this).val();
            if (_v.length === 4) {
                check_sms();
            }
        });

        function validateCheckBox() {
            const hasUnchecked = $(".autoconfirm__list_item input[name^='autoconfirm_item_']").is(":not(:checked)");
            $("#autoconfirm_all").prop('checked', !hasUnchecked);
            $("#autoconfirm button").prop('disabled', hasUnchecked);
        }

        $("#autoconfirm_all").on('change', function () {
            $(".autoconfirm__list_item input[name^='autoconfirm_item_']").prop('checked', this.checked);
            validateCheckBox();
        });

        $(".autoconfirm__list_item input[name^='autoconfirm_item_']").on('change', function () {
            validateCheckBox();
        });

        $("#autoconfirm .autoconfirm__list_item button").on('click', function () {
            autoconfirm();
        });

        $(document).on('click', ".js-send-repeat", function (){
            send_sms();
        });

        $(document).ready(function () {
            sendMetric('reachGoal', 'pos-av1')
        });
        {/literal}
    </script>
{/capture}

