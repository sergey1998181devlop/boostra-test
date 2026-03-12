{$meta_title = "АСП договора" scope=parent}

{$docs_list = [
    ["title" => "Положение об использовании АСП", "url" => "{$config->root_url}/files/docs/asp_usage_policy.pdf"],
    ["title" => "Согласие на хранение и обработку персональных данных", "url" => "{$config->root_url}/files/docs/personal_data_consent.pdf"],
    ["title" => "Согласие на получение маркетинговых коммуникаций", "url" => "{$config->root_url}/files/docs/marketing_consent.pdf"],
    ["title" => "Индивидуальные условия договора займа", "url" => "{$individual_url}"]
]}

{assign var="final_approved_amount" value="{if $il_approved_amount && $settings->il_nk_loan_edit_amount['status'] && in_array($order->utm_source, $settings->il_nk_loan_edit_amount['utm_sources'])}{$il_approved_amount}{else}{$decisionSum}{/if}"}
<div id="autoconfirm">
    <div class="autoconfirm__container">
        {* Логотип boostra *}
        <img class="autoconfirm__logo" src="design/{$settings->theme|escape}/img/svg/logo.svg" alt="boostra logo" />
        
        {* Иконка с деньгами *}
        <img class="autoconfirm__icon" src="design/{$settings->theme|escape}/img/svg/confetti.svg" alt="Congratulations" />
        
        {* Блок с одобрением *}
        {if !empty($final_approved_amount)}
            <div class="autoconfirm__approved_notice">
                <h2>{$user->firstname}, отличные новости! Вам одобрено до <br>{$final_approved_amount|number_format:0:",":" "} рублей</h2>
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
                <button disabled type="button">
                    Подписать и получить
                    {if $order->loan_type == Orders::LOAN_TYPE_PDL}
                        {$decisionSum}
                    {else}
                        {$order->amount}
                    {/if}
                </button>
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
            {* Спойлер с документами *}
            <div class="documents-spoiler">
                <div class="documents-spoiler__header js-documents-spoiler-toggle">
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
                        {if !empty($rcl_loan) && $doc.title == 'Индивидуальные условия договора займа'}
                            <p class="rcl_disclaimer" style="text-align: left!important;">В договоре указана максимально доступная сумма Кредитной линии</p>
                        {/if}
                    {/foreach}
                    {foreach $rcl_docs as $key => $doc}
                        <div class="autoconfirm__list_item">
                            <label for="autoconfirm_rcl_{$key}">
                                <input type="checkbox" value="1" id="autoconfirm_rcl_{$key}" name="autoconfirm_item_rcl_{$key}" />
                                <div>
                                    <a id="{$doc.type}" target="_blank" href="{$doc.url}">{$doc.title}</a>
                                </div>
                            </label>
                        </div>
                    {/foreach}

                    {* Дополнительные услуги *}
                    {if $isOrganic}
                        <div class="autoconfirm__list_item autoconfirm__list_item--extra-service">
                            {include file="credit_doctor/credit_doctor_checkbox.tpl" idkey=$order->id}
                            <input type="hidden" id="credit_doctor_hidden{$order->id}" value="0" />
                        </div>
                        <div class="autoconfirm__list_item autoconfirm__list_item--extra-service">
                            {include file="tv_medical/tv_medical_checkbox.tpl" idkey=$order->id}
                            <input type="hidden" id="tv_medical_hidden{$order->id}" value="0" />
                        </div>
                    {/if}
                </div>
            </div>

            {if $rcl_loan}
                <div class="documents-spoiler">
                    <div class="documents-spoiler__header" onclick="toggleRclSpoiler()">
                        <span>Одобрили вам <span class="documents-spoiler__underlined">Кредитную линию</span></span>
                        <span class="documents-spoiler__arrow rcl-spoiler__arrow">
                            <img src="design/{$settings->theme|escape}/img/svg/up_arrow.svg" alt="arrow" />
                        </span>
                    </div>
                    <div class="documents-spoiler__content" id="rcl-spoiler-content" style="text-align: left!important;">
                        <p>- Максимальная сумма Кредитной линии {$rcl_max_amount} рублей</p>
                        <p>- Получение денег без лишних проверок</p>
                        <p>- Сами выбираете сумму, которая нужна сейчас</p>
                    </div>
                </div>
            {/if}

            {if $order->loan_type == Orders::LOAN_TYPE_PDL}
                {include 'promocode.tpl'}
            {/if}

            {* Временная проверка на лида *}
            {if $settings->il_nk_loan_edit_amount['status'] && in_array($order->utm_source, $settings->il_nk_loan_edit_amount['utm_sources'])}
                {* Если усть одобренная сумма ИЛ займа и она больше 30000, отобразим бегунок *}
                {if isset($il_approved_amount) && $il_approved_amount > Orders::PDL_MAX_AMOUNT  && $order->loan_type == Orders::LOAN_TYPE_PDL}
                    {include 'calculator/il_amount_slider.tpl'}
                {/if}
            {/if}
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
                is_user_credit_doctor: $('#credit_doctor_hidden{/literal}{$order->id}{literal}').val(),
                is_tv_medical: $('#tv_medical_hidden{/literal}{$order->id}{literal}').val(),
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
                        
                        // Показываем модалку кросс-ордера поверх всего (если она есть)
                        if ($('#auto_confirm_2_cross_order_modal').length > 0) {
                            setTimeout(function() {
                                $('#auto_confirm_2_cross_order_modal').fadeIn(300);
                            }, 300);
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

            $("input[name='credit_doctor_check'], input[name='tv_medical_check']").prop('checked', this.checked).trigger('change');
            validateCheckBox();
        });

        $(".autoconfirm__list_item input[name^='autoconfirm_item_']").on('change', function () {
            validateCheckBox();
        });

        $("#autoconfirm .autoconfirm__list_item button").on('click', function () {
            autoconfirm();
        });

        $(document).on('click', ".js-send-repeat", function (e){
            e.preventDefault();
            send_sms();
            return false;
        });

        // Обработчики для спойлеров
        $(document).on('click', '.js-documents-spoiler-toggle', function() {
            const content = document.getElementById('documents-spoiler-content');
            const arrow = document.querySelector('.documents-spoiler__arrow');

            if (content.classList.contains('open')) {
                content.classList.remove('open');
                arrow.classList.remove('open');
            } else {
                content.classList.add('open');
                arrow.classList.add('open');
            }
        });

        function toggleRclSpoiler() {
            const content = document.getElementById('rcl-spoiler-content');
            const arrow = document.querySelector('.rcl-spoiler__arrow');

            if (content.classList.contains('open')) {
                content.classList.remove('open');
                arrow.classList.remove('open');
            } else {
                content.classList.add('open');
                arrow.classList.add('open');
            }
        }

        $(document).on('click', '.js-rcl-spoiler-toggle', function() {
            const content = document.getElementById('rcl-spoiler-content');
            const arrow = document.querySelector('.rcl-spoiler__arrow');

            if (content.classList.contains('open')) {
                content.classList.remove('open');
                arrow.classList.remove('open');
            } else {
                content.classList.add('open');
                arrow.classList.add('open');
            }
        });

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
    <script src="design/{$settings->theme}/js/creditdoctor_modal.app.js?v=1.03" type="text/javascript"></script>
    {$credit_doctor_js_loaded = true scope=parent}
{/capture}

{include file="credit_doctor/credit_doctor_popup.tpl"}
{$credit_doctor_popup_loaded = true scope=parent}
<link rel="stylesheet" href="design/{$settings->theme}/css/autoconfirm_2_asp.css?v=1.00">
