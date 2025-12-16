{$meta_title = "АСП договора" scope=parent}

{$docs_list = [
    ["title" => "Положение об использовании АСП", "url" => "{$config->root_url}/files/docs/asp_usage_policy.pdf"],
    ["title" => "Согласие на хранение и обработку персональных данных", "url" => "{$config->root_url}/files/docs/personal_data_consent.pdf"],
    ["title" => "Согласие на получение маркетинговых коммуникаций", "url" => "{$config->root_url}/files/docs/marketing_consent.pdf"],
    ["title" => "Согласие на направление запросов в БКИ", "url" => "{$config->root_url}/preview/agreement_disagreement_to_receive_ko"],
    ["title" => "Индивидуальные условия договора займа", "url" => "{$individual_url}"]
]}

{literal}
    <style>
        #autoconfirm {
            --font-size: 16px;
            width: 100%;
            padding: 15px;
            max-width: 100%;
            box-sizing: border-box;
            text-align: center;
            h1 {
                font-size: 32px;
            }
            a {
                color: #0a91ed;
            }
            button {
                background: #0A91ED;
                color: white;
                border-radius: 30px;
                padding: 16px 40px;
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                font-size: 14px;
                font-weight: 700;
                line-height: 24px;
                letter-spacing: 0px;
                text-align: center;
                vertical-align: middle;
                border: none;
                cursor: pointer;
                transition: background-color 0.3s;
                min-width: 280px;
            }
            button:hover:not(:disabled) {
                background: #0880D6;
            }
            button:disabled {
                opacity: .5;
                cursor: not-allowed;
            }
        }
        .autoconfirm__logo {
            display: block;
            max-width: 200px;
            height: auto;
            margin: 0 auto 20px;
            text-align: center;
        }
        .autoconfirm__icon {
            display: block;
            max-width: 120px;
            height: auto;
            margin: 0 auto 30px;
            text-align: center;
        }
        .autoconfirm__container {
            max-width: 720px;
            margin: auto;
        }
        .autoconfirm__list_item {
            margin-top: 15px;
            margin-bottom: 15px;
            text-align: left;
        }
        .autoconfirm__list_item label {
            div {
                display: flex;
                align-items: flex-start;
                gap: 15px;
                font-size: var(--font-size);
                line-height: 1.4;
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
                min-width: 20px;
                border: 2px solid #818C99;
                display: block;
                border-radius: 50%;
                margin-top: 2px;
                flex-shrink: 0;
            }
            input:checked + div::before {
                content: url('/design/boostra_mini_norm/assets/icons/checkbox-check.svg');
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background-color: #4E8FF5;
                border-color: #4E8FF5;
                color: white;
                font-size: 14px;
                font-weight: bold;
                animation: checkAnim 0.3s ease;
                /* box-shadow: inset 0 0 0 4px white; */
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
                padding: 10px;
                h1 {
                    font-size: 24px;
                    text-align: center;
                }
                button {
                    width: 100%;
                }
            }
            
            .autoconfirm__logo {
                max-width: 150px;
                margin-bottom: 15px;
            }
            
            .autoconfirm__icon {
                max-width: 90px;
                margin-bottom: 20px;
            }
            
            .autoconfirm__approved_notice h2 {
                font-size: 20px;
                line-height: 26px;
            }
            
            .autoconfirm__approved_notice p {
                font-size: 14px;
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
                /* input:checked + div::before {
                    box-shadow: inset 0 0 0 3px white;
                } */
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
            
            .autoconfirm__logo {
                max-width: 120px;
                margin-bottom: 10px;
            }
            
            .autoconfirm__icon {
                max-width: 70px;
                margin-bottom: 15px;
            }
            
            .autoconfirm__approved_notice h2 {
                font-size: 18px;
                line-height: 22px;
            }
            
            .autoconfirm__approved_notice p {
                font-size: 13px;
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
                /* input:checked + div::before {
                    box-shadow: inset 0 0 0 3px white;
                } */
            }

            .autoconfirm__container {
                padding: 0 5px; /* Меньше отступы по бокам */
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
        .autoconfirm__approved_notice {
            max-width: 720px;
            margin: 20px auto;
            background: transparent;
            color: #1E262E;
            padding: 0;
            border-radius: 0;
            font-weight: 400;
            text-align: center;
        }
        .autoconfirm__approved_notice h2 {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 20px;
            font-weight: 700;
            line-height: 26px;
            letter-spacing: 0px;
            text-align: center;
            vertical-align: middle;
            margin: 0 0 15px 0;
            color: #1E262E;
        }
        .autoconfirm__approved_notice p {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 14px;
            font-weight: 500;
            color: #6F7985;
            margin: 10px 0;
            line-height: 20px;
            letter-spacing: 0px;
            text-align: center;
            vertical-align: middle;
        }
        .autoconfirm__list_item a {
            text-align: left;
            display: block;
        }
        .autoconfirm__list_item--button {
            text-align: center;
        }

        /* Спойлер для документов */
        .documents-spoiler {
            margin: 25px 0;
            border: none;
            border-radius: 0;
            overflow: visible;
            width: 100%;
            box-sizing: border-box;
            background: transparent;
        }
        .documents-spoiler__header {
            background: transparent;
            padding: 0;
            cursor: pointer;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            font-weight: 400;
            font-size: 16px;
            color: #6F7985;
            transition: color 0.3s;
            text-align: left;
            text-decoration: none;
            margin-bottom: 15px;
        }
        .documents-spoiler__header:hover {
            background: transparent;
            color: #4E8FF5;
        }
        .documents-spoiler__underlined {
            text-decoration: underline;
            text-underline-offset: 3px;
        }
        .documents-spoiler__arrow {
            transition: transform 0.3s;
            margin-left: 8px;
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
            width: 16px;
            height: 16px;
        }
        .documents-spoiler__arrow img {
            width: 100%;
            height: 100%;
        }
        .documents-spoiler__arrow.open {
            transform: rotate(180deg);
        }
        .documents-spoiler__content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, padding 0.3s ease-out;
            padding: 0;
            width: 100%;
            box-sizing: border-box;
            background: transparent;
        }
        .documents-spoiler__content.open {
            max-height: 1000px;
            padding: 0;
        }
        .documents-spoiler__content .autoconfirm__list_item {
            width: 100%;
            box-sizing: border-box;
            margin: 12px 0;
        }
        .documents-spoiler__content .autoconfirm__list_item a {
            color: #4E8FF5;
            text-decoration: none;
        }
        .documents-spoiler__content .autoconfirm__list_item a:hover {
            text-decoration: underline;
        }
        .promo-button {
            margin-top: 20px;
            text-align: center;
        }
        .promo-button button {
            background: transparent;
            color: #4E8FF5;
            border: 2px solid #4E8FF5;
            padding: 14px 30px;
        }
        .promo-button button:hover {
            background: #F0F7FF;
        }
    </style>
{/literal}

<div id="autoconfirm">
    <div class="autoconfirm__container">
        {* Логотип boostra *}
        <img class="autoconfirm__logo" src="design/{$settings->theme|escape}/img/svg/logo.svg" alt="boostra logo" />
        
        {* Иконка с деньгами *}
        <img class="autoconfirm__icon" src="design/{$settings->theme|escape}/img/svg/confetti.svg" alt="Congratulations" />
        
        {* Блок с одобрением *}
        {if !empty($decisionSum)}
            <div class="autoconfirm__approved_notice">
                <h2>{$user->firstname}, отличные новости! Вам одобрено<br>{$decisionSum|number_format:0:",":" "} рублей на {$order->period|number_format:0:",":" "} дней</h2>
                <p>Осталось подписать документы и добавить карту<br>для получения выдачи</p>
            </div>
        {/if}
        
        <div class="autoconfirm__list_wrapper">
            {* Первый чекбокс - главный *}
            <div class="autoconfirm__list_item">
                <label for="autoconfirm_all">
                    <input type="checkbox" value="1" id="autoconfirm_all" name="autoconfirm_all" />
                    <div>
                        <span>Я подписываю указанные ниже документы аналогом собственноручной подписи и согласен с указанными в перечне условиями и документами</span>
                    </div>
                </label>
            </div>
            
            {* Кнопка подписания *}
            <div class="autoconfirm__list_item autoconfirm__list_item--button">
                <button disabled type="button">Подписать и получить</button>
                <div class="hidden">
                    <div class="autoconfirm_sms_block js-autoconfirm-block" data-phone="{$user->phone_mobile}">
                        <div class="autoconfirm_actions">
                            <span class="info" id="accept_info">На Ваш телефон {$user->phone_mobile} было отправлено СМС-сообщение с кодом для подтверждения.</span>
                            <div id="autoconfirm_sms">
                                <div>
                                    <input type="input" name="code" class="js-autoconfirm-sms" maxlength="4" placeholder="Код из СМС" />
                                    <span class="js-autoconfirm-error error-info"></span>
                                </div>
                                <div class="js-repeat-autoconfirm-sms"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {* Спойлер с документами *}
            <div class="documents-spoiler">
                <div class="documents-spoiler__header" onclick="toggleDocumentsSpoiler()">
                    <span>Я согласен <span class="documents-spoiler__underlined">со всеми условиями</span></span>
                    <span class="documents-spoiler__arrow">
                        <img src="design/{$settings->theme|escape}/img/svg/up_arrow.svg" alt="arrow" />
                    </span>
                </div>
                <div class="documents-spoiler__content" id="documents-spoiler-content">
                    {foreach $docs_list as $key => $doc}
                        <div class="autoconfirm__list_item">
                            <label for="autoconfirm_{$key}">
                                <input type="checkbox" value="1" id="autoconfirm_{$key}" name="autoconfirm_item_{$key}" />
                                <div>
                                    <a id="{$doc.type}" target="_blank" href="{$doc.url}">{$doc.title}</a>
                                </div>
                            </label>

                        </div>
                    {/foreach}
                </div>
            </div>
            {include 'promocode.tpl'}
        </div>
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


                        // В режиме автоподписания над привязкой карты: просто разблокируем привязку карты
                        try { $.magnificPopup.close(); } catch (e) {}

                        // Скрываем блок автоподписания и поздравление, показываем инфо о сумме/скоре
                        $('#autoconfirm, .autoconfirm__approved_notice').fadeOut(200, function() {
                            // После скрытия прокручиваем в начало страницы
                            window.scrollTo({top: 0, behavior: 'smooth'});
                        });
                        $('#score-info').fadeIn(200);

                        // Показываем блок выбора банка СБП (если он отрендерен), иначе — блок привязки карты
                        if ($('#sbp-bank-selection-wrapper').length > 0) {
                            $('#sbp-bank-selection-wrapper').fadeIn(200);
                        } else {
                            // Показываем секцию добавления карты
                            $('#card-add-section').removeClass('is-hidden').fadeIn(200);
                        }
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

        // Функция переключения спойлера документов
        function toggleDocumentsSpoiler() {
            const content = document.getElementById('documents-spoiler-content');
            const arrow = document.querySelector('.documents-spoiler__arrow');

            if (content.classList.contains('open')) {
                content.classList.remove('open');
                arrow.classList.remove('open');
            } else {
                content.classList.add('open');
                arrow.classList.add('open');
            }
        }

        // Инициализация стартового состояния блоков при активном автоподписании:
        $(function() {
            try {
                $('#sbp-bank-selection-wrapper').hide();
                $('#card-add-section').hide();
                window.scrollTo({top: 0, behavior: 'smooth'});
            } catch (e) { console.warn(e); }
        });
        
        // Функция для отображения поля ввода промокода
        function showPromoInput() {
            // Здесь можно добавить логику отображения модального окна или поля ввода промокода
            alert('Функция ввода промокода. Здесь будет форма для ввода промокода.');
            // Пример: можно открыть модальное окно или показать скрытое поле
        }

        $(document).ready(function () {
           sendMetric('reachGoal', 'pos-av2')
        });
        {/literal}
    </script>
{/capture}