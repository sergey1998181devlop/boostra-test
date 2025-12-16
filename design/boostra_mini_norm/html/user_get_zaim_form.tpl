{if !$have_issued_loans}

    {if $new_order_maratorium}
        <div style="color:red;font-size:1.2rem;margin:20px 0;">
            Вы можете повторно обратиться за займом : {$new_order_maratorium|date} {$new_order_maratorium|time} (мск)
        </div>
    {/if}

    <div class="clearfix about" id="user_get_zaim_form"
         {if !$user->not_rating_maratorium_valid && (($reason_block && $reason_block !== 999) || $repeat_loan_block || $new_order_maratorium)}style="display: none"{/if}>

        {if $success_add_data}
            <p class="success_add_data-par">
                Теперь Ваша анкета содержит все необходимые данные <br/>и Вы можете подать заявку
            </p>
        {/if}

        {if empty($cards) && empty($sbp_accounts)}
            {include file="no_cards.tpl" has_approved_order=false}
        {else}

            {if $need_add_fields|count > 0}
                <a
                        {if $user->fake_order_error > 0}style="display:none"{/if}
                        href="add_data"
                        class="button big button_get_zaim {if $config->snow}snow-relative primary{else}green{/if} bg-warning"
                >
                    {if $config->snow}
                        <img class="snow-man" src="design/orange_theme/img/holidays/snow/snow_man.png?v=2"
                             alt="Заявка на заём"/>
                    {/if}
                    Получить займ
                </a>
            {else}
{*                <a*}
{*                        {if $user->fake_order_error > 0 || $repeat_approve_message}style="display:none"{/if}*}
{*                        href="#"*}
{*                        class="button big {if $config->snow}snow-relative primary{else}green{/if} get_new_loan"*}
{*                        {if $is_need_choose_card == 1}  is_need_reassign=true {/if}*}
{*                >*}
{*                    {if $config->snow}*}
{*                        <img class="snow-man" src="design/orange_theme/img/holidays/snow/snow_man.png?v=2"*}
{*                             alt="Заявка на заём"/>*}
{*                    {/if}*}
{*                    Заявка на заём*}
{*                </a>*}
            {/if}
            <div id="is_need_reassign_block" class="mfp-hide">

                <div class="modal_title">
                    Перепривяжите карту!
                </div>

                <div style="text-align: center">
                    <p class="autoapprove_card_security">
                        <span class="autoapprove_card_security__title">Важно! Мы повысили уровень безопасности Ваших персональных данных.</span>
                        <br>Для дальнейшего совершения операций с денежными средствами необходимо перепривязать на СБП счет.
                        <br>Добавьте СБП счет. На него мы продолжим зачислять Вам деньги.
                        <br>При необходимости Вы можете добавить более одного счета и выбирать любой из них для зачисления/списания средств.
                    </p>
                </div>

            </div>
            {if !$need_add_fields}
                <div class="loan_form"
                     {if $user->fake_order_error > 0 || $repeat_approve_message}style="display:block"{/if}>
                    <form id="repeat_loan_form_approve" class="{if $autoconfirm_enabled}js-autoconfirm-form{/if}" action="{$smarty.server.REQUEST_URI}" method="POST">

                        {if $user->fake_order_error > 0}
                            <p style="color:#d22">
                                К сожалению Вам отказано.
                                <br/>Попробуйте отправить заявку повторно,
                                <br/>так как возможны технические сбои.</p>
                        {/if}

                        {if $notOverdueLoan}
                            <h2>
                                Отлично! Вы закрыли займ без просрочек и можете оформить новый на льготных условиях.
                            </h2>

                        {elseif $repeat_approve_message}
                            <p class="repeat_approve_message">
                                Вам предварительно одобрен займ на тех же условиях
                            </p>
                        {/if}

                        <input type="hidden" name="service_sms" value="0"/>
                        <input type="hidden" name="service_insurance" value="0"/>
                        <input type="hidden" name="service_reason" value="0"/>
                        {if ($user_return_credit_doctor)}
                            <input type="hidden" name="service_doctor" value="0" />
                        {else}
                            <input type="hidden" name="service_doctor" value="1" />
                        {/if}
                        <input type="hidden" name="service_recurent" value="1"/>

                        <input type="checkbox" id="service_insurance_check" value="1" checked="true" style="display:none"/>

                        <input type="hidden" name="juicescore_session_id" id="juicescore_session_id" value=""/>
                        <input type="hidden" name="juicescore_useragent" id="juicescore_useragent" value=""/>
                        <input type="hidden" name="finkarta_fp" id="finkarta_fp" value="" />
                        <input type="hidden" name="local_time" id="local_time" value=""/>
                        <input type="hidden" name="sms" id="sms" value=""/>
                        <input type="hidden" name="bank_id" id="bank_id" value="{if $selected_bank}{$selected_bank->id}{/if}"/>

                        {if in_array($user->utm_source, ['Boostra', '']) || $user_return_credit_doctor}
                            <input type="hidden" value="0" name="is_user_credit_doctor" id="credit_doctor_hidden"/>
                        {else}
                            <input type="hidden" value="1" name="is_user_credit_doctor" id="credit_doctor_hidden"/>
                        {/if}

                        {if $installment_enabled}
                            {include file = 'installment/calculator.tpl'}
                        {else}
                            <div id="calculator">
                                <input type="hidden" id="percent"
                                       value="{if $user_discount}{$user_discount->percent/100}{else}0.01{/if}"/>
                                <input type="hidden" id="max_period"
                                       value="{if $user_discount}{$user_discount->max_period}{else}{if $user->loan_history|count > 0}4{else}5{/if}{/if}"/>
                                <input type="hidden" id="have_close_credits"
                                       value="{if $user->loan_history|count > 0}1{else}0{/if}"/>
                                <div class="slider-box">
                                    <div class="money">
                                        <input type="text" id="money-range" name="amount"
                                               value="{if $smarty.session.fake_order_amount}{$smarty.session.fake_order_amount}{else}30000{/if}"/>
                                    </div>
                                    <div style="display: none !important;" class="period">
                                        <input type="text" id="time-range" name="period"
                                                {*                              value="{if $smarty.session.fake_order_period}{$smarty.session.fake_order_period}{else}16{/if}"/>*}
                                               value="16"/>
                                    </div>
                                </div>

                                <input type="hidden" name="card_id" id="selected_card_id" value="{if $selected_sbp_account_id}{$selected_sbp_account_id}{else}{$basicCard|default:''}{/if}" />
                                <input type="hidden" name="card_type" id="selected_card_type" value="{if $selected_sbp_account_id}sbp{else}card{/if}" />

                                {include file = 'show_cards.tpl'}

                                <div class="result">К возврату <span class="total"></span> руб. до <span class="date"></span></div>
                                {include file='promocode.tpl'}
                                {if $need_add_fields|count > 0}
                                    <a href="add_data"
                                       disabled="true"
                                       class="button big {if $config->snow}snow-relative primary{else}green{/if}">
                                        {if $user->fake_order_error > 0}
                                            Отправить повторно
                                        {else}
                                            {if $config->snow}
                                                <img class="snow-man"
                                                     src="design/orange_theme/img/holidays/snow/snow_man.png?v=2"
                                                     alt="Получить заём"/>
                                            {/if}
                                            Получить заём
                                        {/if}
                                    </a>
                                {else}
                                    <button type="submit" id="repeat_loan_submit"
                                            {if $can_add_sbp_account && !empty($b2p_sbp_banks)} onclick="generateAndOpenSbpLink(event)"{/if}
                                            class="{if $user->fake_order_error == 0}js-metrics-click-cash{/if} button big {if $config->snow}snow-relative primary{else}green{/if}">
                                        {if $user->fake_order_error > 0}
                                            Отправить повторно
                                        {else}
                                            {if $config->snow}
                                                <img class="snow-man"
                                                     src="design/orange_theme/img/holidays/snow/snow_man.png?v=2"
                                                     alt="Получить заём"/>
                                            {/if}
                                            Получить заём
                                        {/if}
                                    </button>
                                {/if}
                                <div>
                                    <a target="_blank" style="font-size:1rem" href="{$config->root_url}/files/docs/akvarius/discount_zero.pdf">Правила акции "Заём под 0%"</a>
                                    <div class="discount-zero-info"> 0% по займу первые 5 дней</div>
                                </div>
                                <div class="docs_wrapper">
                                    <label class="js-accept-block medium left" style="font-size: 20px;">
                                        <span class="error">Необходимо согласиться с условиями!</span>
                                    </label>

                                    <div>
                                        <label class="spec_size">
                                            <div class="checkbox"
                                                 style="border-width: 1px;width: 16px !important;height: 16px !important;">
                                                <input class="js-need-value" type="checkbox" />
                                                <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                                            </div>
                                        </label>
                                        <p>
                                            Нажимая "Получить заём", я соглашаюсь со <a href="#" data-btn-toggle="collapse">следующими условиями</a>
                                        </p>
                                    </div>

                                    <div class="collapse" data-target="collapse">
                                        <p>
                                            С <a href="{$config->root_url}/files/docs/asp_usage_policy.pdf" target="_blank">
                                                условиями использования аналога собственноручной подписи (АСП)
                                            </a> согласен
                                        </p>
                                        <p>
                                            Я согласен на <a href="{$config->root_url}/files/docs/personal_data_consent.pdf" target="_blank">
                                                обработку персональных данных
                                            </a> и
                                            <a href="{$config->root_url}/files/docs/marketing_consent.pdf" target="_blank">
                                                получение маркетинговых коммуникаций
                                            </a>
                                        </p>

                                        {if $autoconfirm_enabled || $is_old_client_or_old_register}
                                            <p>
                                                Я согласен на <a href="{$config->root_url}/preview/agreement_disagreement_to_receive_ko" target="_blank">
                                                    направление запросов в БКИ
                                                </a>
                                            </p>
                                            <p>
                                                {if $is_old_client_or_old_register}
                                                    <a href="{$individual_max_amount_doc_url}" target="_blank">Индивидуальные условия договора займа</a>
                                                {else}
                                                    <a class="js-dogovor-link" href="{$config->root_url}/preview/IND_USLOVIYA?user_id={$user->id}" target="_blank">
                                                        Индивидуальные условия договора займа
                                                    </a>
                                                {/if}
                                                <br/>
                                                <small>Сумма займа скорректирована на максимально доступное для выдачи значение с учетом результатов предварительного скоринга.</small>
                                            </p>
                                        {/if}
                                        {if !$isVirtualCardConsent && $is_virtual_card_checkbox}
                                            <div>
                                                <label class="spec_size">
                                                    <div class="checkbox"
                                                         style="border-width: 1px;width: 16px !important;height: 16px !important;">
                                                        <input name="virtual_card" value="1" checked="checked" type="checkbox" />
                                                        <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                                                    </div>
                                                </label>
                                                <p>
                                                    Я согласен с <a href="{$config->root_url}../../../share_docs/general/docs_uslugi_oferta_esp_0250725.pdf" data-btn-toggle="collapse">«Офертой ООО РНКО «Платежный конструктор» на выпуск виртуальной карты Boostra»</a>.
                                                </p>
                                            </div>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        {/if}
                    </form>
                </div>
            {/if}

        {/if}

    </div>
    <script>
        var user = {$user->id};
    </script>
{literal}
    <script>
        var _click_counter_doc = 9;
        $('#credit_doctor_check').live('change', function () {
            let is_new_client = $("input[name='is_new_client']").val();
            if (_click_counter_doc > 0 && is_new_client != 1) {
                $('#credit_doctor_check').attr('checked', true);
                _click_counter_doc--;
            }
            $('[name=is_user_credit_doctor]').val($(this).is(':checked') ? 1 : 0);
        });


        $(document).ready(function () {
            function makeAjaxRequest(amount, user) {
                $.ajax({
                    type: "POST",
                    url: 'ajax/calculate_cd.php',
                    data: {
                        action: "update_slider_values",
                        user: user,
                        amount: amount
                    },
                    success: function (response) {
                        const parsedResponse = JSON.parse(response);
                        const price = parsedResponse.result.price;

                        $('.dynamic-price').each(function () {
                            const extra = $(this).data('extra') || 0;
                            const newAmount = parseInt(price) + parseInt(extra);
                            $(this).text(newAmount + ' руб / 14 дней');
                        });
                        $('.credit_doctor_amount').text(price);

                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log('Error:', textStatus, errorThrown);
                    },
                    complete: function (jqXHR, textStatus) {
                        console.log('AJAX request completed:', textStatus);
                    }
                });
            }

            const initialAmount = $('#money-range').val();
            makeAjaxRequest(initialAmount, user);

            let timer;

            $('#money-range, #time-range').on('change', function () {
                clearTimeout(timer);
                timer = setTimeout(function () {
                    const amount = $('#money-range').val();
                    makeAjaxRequest(amount, user);
                }, 500);
            });

            $('#repeat_loan_terms').change(function () {
                var isChecked = $(this).is(':checked');
                $('#repeat_loan_submit').prop('disabled', !isChecked);

                if ($('#repeat_loan_submit').prop('disabled')) {
                    $('#repeat_loan_submit').addClass('disabled-button');
                } else {
                    $('#repeat_loan_submit').removeClass('disabled-button');
                }
            });

            $(document).ready(function () {
                $('.toggle-conditions').click(function () {
                    $('.conditions').slideToggle();
                });

                $('[data-btn-toggle="collapse"]').click(function (e) {
                    e.preventDefault();
                    $("[data-target='collapse']").slideToggle(300);
                });
            });
        });

    </script>
{/literal}

{literal}
    <script>
        (function(){
            function syncSelected() {
                var checked = document.querySelector('input[name="card"]:checked');
                var hidId = document.getElementById('selected_card_id');
                var hidType = document.getElementById('selected_card_type');
                if (!checked || !hidId || !hidType) return;

                var val = checked.value;
                if (val.indexOf('sbp:') === 0) {
                    hidType.value = 'sbp';
                    hidId.value = val.replace('sbp:', '');
                } else {
                    hidType.value = 'card';
                    hidId.value = val;
                }
            }

            window.syncUserSelectedCard = syncSelected;

            document.addEventListener('DOMContentLoaded', syncSelected, false);

            document.addEventListener('change', function(e){
                if (e.target && e.target.name === 'card') {
                    syncSelected();
                }
            }, false);
        })();
    </script>
{/literal}

{/if}
<div class="hidden">
    <div class="autoconfirm_sms_block js-autoconfirm-block" data-phone="{$user->phone_mobile}">
        <div class="autoconfirm_actions">
            <span class="info" id="accept_info">На Ваш телефон {$user->phone_mobile} было отправлено СМС-сообщение с кодом для подтверждения.</span>
            <div id="autoconfirm_sms">
                <div>
                    <input type="input" name="code" class="js-autoconfirm-sms" maxlength="4" placeholder="Код из СМС"/>
                    <span class="js-autoconfirm-error error-info"></span>
                </div>
                <div class="js-repeat-autoconfirm-sms"></div>
            </div>
        </div>
    </div>
</div>

{if !empty($b2p_sbp_banks)}
    {include file = 'modals/modal_choose_bank.tpl' show_close_button=true show_text_to_choose_sbp=true}
{/if}

{* Подключаем JavaScript для работы кнопки СБП *}
<script src="design/{$settings->theme}/js/sbp.js?v=1.007"></script>