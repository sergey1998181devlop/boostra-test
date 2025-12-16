<div id="calculator">
<script type="text/javascript">
    var calculator_il_params = {
        default_period: 16,
        min_period: 16, 
        max_period: 168,
        default_amount: 30000,
        min_amount: 1000,
        max_amount: 100000,
    };
</script>

    <input type="hidden" id="percent"
           value="{if $user_discount}{$user_discount->percent/100}{else}{$base_percents/100}{/if}"/>
    <input type="hidden" id="max_period"
           value="{if $user_discount}{$user_discount->max_period}{else}{if $user->loan_history|count > 0}4{else}5{/if}{/if}"/>
    <input type="hidden" id="have_close_credits" value="{if $user->loan_history|count > 0}1{else}0{/if}"/>
    <input type="hidden" name="card_id" id="selected_card_id" value="{if $selected_sbp_account_id}{$selected_sbp_account_id}{else}{$basicCard|default:''}{/if}" />
    <input type="hidden" name="card_type" id="selected_card_type" value="{if $selected_sbp_account_id}sbp{else}card{/if}" />
    <input type="hidden" name="bank_id" id="bank_id" value="{if $selected_bank}{$selected_bank->id}{/if}"/>

    <div class="slider-box">
        <div class="money">
            <input type="text" id="money-range" name="amount"
                   value="{if $smarty.session.fake_order_amount}{$smarty.session.fake_order_amount}{else}30000{/if}"/>
        </div>
        <div class="period">
            <input type="text" id="time-range" name="period"
                   value="{if $smarty.session.fake_order_period}{$smarty.session.fake_order_period}{else}16{/if}"/>
        </div>
    </div>

    {include file = 'show_cards.tpl'}

    <div class="result">
    </div>
    <br/>

    <div class="docs_wrapper">
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;">
                    <input class="js-service-doctor js-need-verify" type="checkbox" value="1"
                           id="service_doctor_check" name="service_doctor" checked=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>

            <!--a href="{$config->root_url}/files/specials/dogovor_150222.pdf" target="_blank">Договор</a-->
            <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с
                <a class="contract_approve_file"
                   href="{$config->root_url}/files/contracts/{$user->order['approved_file']}"
                   target="_blank">Договором</a></p>
        </div>

        <div id="not_checked_info" style="display:none">
            <strong style="color:#f11">Вы должны согласиться с договором</strong>
        </div>
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;">
                    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1"
                           id="agreed_1"
                           name="agreed_1" checked=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с
                <a href="http://www.boostra.ru/files/docs/obschie-usloviya.pdf" target="_blank">Общими
                    условиями договора потребительского микрозайма</a></p>
        </div>
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;">
                    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1"
                           id="agreed_4"
                           name="agreed_4" checked=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с
                <a href="http://www.boostra.ru/files/docs/pravila-predostavleniya.pdf"
                   target="_blank">
                    Правилами предоставления займов ООО МКК "Аквариус"
                </a></p>
        </div>
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;">
                    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1"
                           id="agreed_3"
                           name="agreed_3" checked=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с
                <a href="https://www.boostra.ru/files/docs/informatsiyaobusloviyahpredostavleniyaispolzovaniyaivozvrata.pdf"
                   target="_blank">
                    Правилами обслуживания и пользования услугами ООО МКК "Аквариус"
                </a></p>
        </div>
        {if $pdn_doc}
            <div>
                <label class="spec_size">
                    <div class="checkbox"
                         style="border-width: 1px;width: 16px !important;height: 16px !important;">
                        <input class="js-agreeed-asp js-need-verify-modal" type="checkbox"
                               value="1" id="agreed_10"
                               name="agreed_10" checked=""/>
                        <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                    </div>
                </label>
                <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с
                    <a href="user/docs?action=pdn_excessed" target="_blank">
                        Уведомлением о повышенном риске невыполнения кредитных обязательств
                    </a></p>
            </div>
        {/if}
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;">
                    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1"
                           id="agreed_3"
                           name="agreed_3" checked=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с
                <a href="user/docs?action=micro_zaim" target="_blank" class="micro-zaim-doc-js">Заявлением
                    о предоставлении микрозайма</a></p>
            <script defer>
                $('a.micro-zaim-doc-js').mousedown(function (e) {
                    e.preventDefault();
                    let loanAmount = $('#calculator .total').text();
                    if (!loanAmount) {
                        loanAmount = $('#approve_max_amount').text();
                    }
                    let is_user_credit_doctor = $('#credit_doctor_check').is(':checked') ? 1 : 0;
                    let newUrl = $(this).attr('href') + '&loan_amount=' + loanAmount + '&credit_doctor=' + is_user_credit_doctor;
                    window.open(newUrl, '_blank');
                })
            </script>
        </div>
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;">
                    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1"
                           id="agreed_5"
                           name="agreed_5" checked=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с
                <a href="http://www.boostra.ru/files/docs/politikakonfidentsialnosti.pdf"
                   target="_blank">
                    Политикой конфиденциальности ООО МКК "Аквариус"
                </a></p>
        </div>
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;">
                    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1"
                           id="agreed_9"
                           name="agreed_9" checked=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            <p>Настоящим выражаю свое <a
                        href="http://www.boostra.ru/files/docs/soglasie-klienta-na-poluchenie-informatsii-iz-byuro-kreditnyh-istorij.pdf"
                        target="_blank">согласие</a>
                на запрос кредитного отчета в бюро кредитных историй</p>

        </div>
        {include file="credit_doctor/credit_doctor_checkbox.tpl"}
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;">
                    <input class="js-service-recurent" type="checkbox" value="1"
                           id="service_recurent_check"
                           checked="true"/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с
                <a class="block_1"
                   href="http://www.boostra.ru/files/docs/soglashenie-o-regulyarnyh-rekurentnyh-platezhah.pdf"
                   target="_blank">Соглашением о применении регулярных (рекуррентных)
                    платежах</a>.</p>

        </div>
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;">
                    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1"
                           id="agreed_8"
                           name="agreed_8" checked=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с
                <a href="http://www.boostra.ru/files/docs/Договор_об_условиях_предоставления_Акционерное_общество_«Сургутнефтегазбанк».pdf"
                   target="_blank">
                    Договором об условиях предоставления Акционерное общество
                    «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием
                    реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей»
                    (Публичная оферта)
                </a></p>
        </div>

        <label class="js-accept-block medium left {if $error=='empty_accept'}error{/if}" style="display:flex; padding: 0">
            <div class="checkbox">
                <input class="js-input-accept" type="checkbox" value="1" id="repeat_loan_terms"
                       name="accept" {if $accept}checked="true"{/if} />
                <span></span>
            </div>
            Я ознакомлен и согласен с вышестоящими условиями&nbsp;
            <span class="error">Необходимо согласиться с условиями</span>
        </label>
        <label class="js-accept-block medium left {if $error=='empty_accept'}error{/if}" style="font-size: 18px;">
    </div>

    <div class="clearfix"></div>
    {if $need_add_fields|count > 0}
        <a href="add_data"
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
</div>

{if !empty($b2p_sbp_banks)}
    {include file = 'modals/modal_choose_bank.tpl' show_close_button=true show_text_to_choose_sbp=true}
{/if}