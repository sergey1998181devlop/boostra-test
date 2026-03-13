<link href="design/{$settings->theme|escape}/css/refinance.css" />
<script src="design/{$settings->theme|escape}/js/refinance.app.js?v=1.003"></script>

{if isset($orderData)}
    {assign var="refinance_default_day" value=intval(date('d'))}
    {assign var="order_id" value=$orderData->order->id|default:$orderData->order->order_id}
    {assign var="refinance" value=$orderData->refinance}

    <div style="display: block">
        <div class="js-refinance-block">

            <div class="js-step-1 action" {if !empty($refinance['order'])} style="display:none" {/if}>
                <div class="refinance-info">
                    Рефинансирование - услуга, позволяющая разбить задолженность на несколько месяцев с начислением процентов
                </div>
                <button class="payment_button green button big p-4 refinance-button" id="show-refinance-button" type="button" data-number="{$orderData->balance->zaim_number}">
                    Рефинансирование
                </button>
            </div>

            <div class="js-step-2" style="display:none">
                <h4>Условия рефинансирования</h4>
                <ul class="refinance-list">
                    <li>
                        Регулярный платёж <span id="everypayment">{$refinance['everypayment']}</span> рублей каждые <span id="pay_period">{$refinance['pay_period']}</span> дней.
                    </li>
                    <li>
                        Процентная ставка <span class="refinance_percent">{$refinance['percent']}</span>% в день. Проценты начисляются на остаток основного долга.
                    </li>
                    <li>
                        Первый взнос <span id="first_pay">{$refinance['first_pay']}</span> руб.
                    </li>
                    <li>
                        В случае нарушения графика платежа производится расчёт с <span class="refinance_percent">{$refinance['percent']}</span>% на 15% в день.
                    </li>
                    <li class="refinance-error" style="display: none"></li>
                </ul>

                <div class="refinance-container">
                    <label for="refinanceSlider" class="label-text">Срок рефинансирования</label>

                    <div class="slider-wrapper">
                        <div class="flex">
                            <div class="text-muted-small mr-5">2 месяца</div>
                        </div>

                        <div style="display: flex; flex-direction: column">
                            <input type="range" min="2" max="6" class="range" step="1" value="2" id="refinanceSlider">
                            <div class="text-muted-small mt-1" id="monthsValue">2 месяца</div>
                        </div>

                        <div class="flex">
                            <div class="text-muted-small ml-5">6 месяцев</div>
                        </div>
                    </div>
                </div>

                <!-- временно скрываем этот блок -->
                <div class="refinance-container" style="display: none">
                    <label for="refinance-date" class="label-text">Ежемесячная дата оплаты</label>
                    <div>Каждый <input type="text" value="{$refinance_default_day}" id="refinance-date" min="1" max="31"> день месяца</div>
                </div>

                <div class="refinance-container">
                    <label for="refinance-card" class="label-text">Использовать карту</label>
                    {if $cards}
                    <ul class="payment-card-list">
                        {foreach $cards as $key => $card}
                        <li>
                            <input type="radio" name="card_id" id="card_{$key}" value="{$card->id}"
                                   {if ($card->checked)}checked{/if} />
                            <label for="card_{$key}"><span>{$card->pan}</span></label>
                        </li>
                        {/foreach}
                    </ul>
                    {else}
                    <h3><b>Нет доступных карт</b></h3>
                    {/if}
                </div>

                <div class="apply-refinance">
                    <button type="button" class="js-next-step-3 payment_button green button big p-4">
                        Применить рефинансирование
                    </button>
                    <p class="error-info" style="display:none"></p>
                </div>

            </div>
            <div class="js-step-3" {if empty($refinance['order']['accept_sms'])} style="display:none" {/if}>
                <h4>Подтверждение рефинансирования</h4>
                <div class="refinance-info">
                    Для завершения оформления рефинансирования подтвердите согласие с условиями и введите код из СМС.
                </div>
                <form id="refinance-confirm-form" class="accept_credit_form" autocomplete="off">
                    <div class="accept_credit_actions">
                        <div>
                            <label for="refinance_sms_code">Код из СМС</label>
                            <input type="text" inputmode="numeric" id="refinance_sms_code" name="sms_code" maxlength="6" required placeholder="Введите код" autocomplete="one-time-code" />
                            <div class="sms-code-error" style="color: #f11; margin-top: 0.5em;"></div>
                            <p class="error-info" style="display:none"></p>
                        </div>
                        <div class="apply-refinance">
                            <button type="button" class="get_refinance_btn payment_button green button big p-4 js-apply-refinance">
                                Получить рефинансирование
                            </button>
                        </div>

                        {if !empty($refinance['order'])}
                            <input type="hidden" name="refinance_card_id" class="refinance_card_id" value="{$refinance['order']['card_id']}" />
                        {/if}

                        <input type="hidden" name="order_id" class="order_id" value="{$order_id}" />
                        <input type="hidden" name="organization_id" class="organization_id" value="{$orderData->order->organization_id}" />
                        <input type="hidden" name="number" class="zaim_number" value="{$orderData->balance->zaim_number}" />
                        <input type="hidden" name="refinance_everypayment" class="refinance_everypayment_field" value="{$refinance['everypayment']}" />
                        <input type="hidden" name="refinance_pay_period" class="refinance_pay_period_field" value="{$refinance['pay_period']}" />
                        <input type="hidden" name="refinance_percent" class="refinance_percent_field" value="{$refinance['percent']}" />
                        <input type="hidden" name="refinance_first_pay" class="refinance_first_pay_field" value="{$refinance['first_pay']}" />
                    </div>
                    <div class="js-repeat-autoconfirm-sms"></div>
                    {if !empty($refinance['documents'])}
                    <div class="docs_wrapper">
                        <p class="toggle-conditions-accept toggle-conditions-accept">Я согласен со всеми условиями:
                            <span class="arrow">
                                <img src="{$config->root_url}/design/boostra_mini_norm/img/icons/chevron-svgrepo-com.svg" alt="Arrow" />
                            </span>
                        </p>
                        <div class="conditions">
                            {foreach $refinance['documents'] as $document_key => $document}

                            <div>
                                <label class="spec_size">
                                    <div class="checkbox" style="border-width:1px;width:16px!important;height:16px!important;">
                                        <input
                                                class="js-need-verify js-agree-claim-value"
                                                type="checkbox"
                                                value="0"
                                                id="{$document_key}"
                                        />
                                        <span style="margin:0;width:100%;height:100%;top:0;left:0;"></span>
                                    </div>
                                </label>
                                <p>
                                    <a href="{$document['link']}" target="_blank">{$document['name']}</a>
                                </p>
                            </div>

                            {/foreach}
                            <div id="not_checked_info" style="display:none">
                                <strong style="color:#f11">Вы должны согласиться с договором и нажать "Рефинанс"</strong>
                            </div>
                        </div>
                    </div>
                    {/if}
                </form>

                <form action="user/payment" method="POST" class="form-payment hidden">
                    <input type="hidden" name="number" class="number" value="" />
                    <input type="hidden" name="order_id" class="order_id" value="" />
                    <input type="hidden" name="amount" class="amount" value="" />
                    <input type="hidden" name="code" class="sms_code" value="" />
                    <input type="hidden" name="card_id" class="card_id" value="" />
                    <input type="hidden" name="from" value="refinance" />
                    <input type="hidden" name="refinance" class="refinance" value="" />
                </form>
            </div>
        </div>
    </div>


    <style>
        #refinanceSlider {
            padding-bottom: 0;
            margin-bottom: 0.5em;
            margin-top: 1.7em;
        }

        #monthsValue {
            text-align: center;
            vertical-align: bottom;
        }

        .payment-card-list {
            list-style: none; /* Убираем стандартные маркеры */
            padding: 0;
            margin: 0;
        }

        .payment-card-list li {
            display: flex; /* Включаем flex-расположение */
            align-items: center; /* Выравниваем элементы по вертикали */
            gap: 10px; /* Расстояние между radio и текстом */
            margin-bottom: 8px; /* Отступ между пунктами */
        }

        .payment-card-list input[type="radio"] {
            margin: 0; /* Убираем стандартные отступы */
        }

        .payment-card-list label {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding-left: 0.5em;
        }

        .refinance-container {
            margin-bottom: 2em;
            max-width: 600px;
            margin-left: 0
        }

        #refinance-date {
            width: 50px;
            border: 2px solid black;
            border-radius: 10px;
            padding: 5px;
            text-align: center;
        }

        .refinance-info {
            margin-bottom: 20px;
            border: 2px solid #080;
            border-radius: 10px;
            padding: 10px 15px;
            max-width: 300px;
            font-size: 1rem;
            line-height: 1.1rem;
            color: #080;
        }

        @media (max-width:600px){
            .refinance-info {
                margin-left:0;
                margin-top:10px;
            }
        }

        .refinance-error {
            color: #cc2222;
            font-weight: 600;
            list-style-type: none;
        }

        .refinance-button {
            height: 5em;
            width: 20em;
            font-weight: bold;
            font-size: large;
        }

        .apply-refinance {
            margin-bottom: 2em;
            margin-top: 1em;
        }

        .apply-refinance button {
            height: 5em;
            width: 20em;
            font-weight: bold;
            font-size: large;
        }

        .js-step-1 {
            margin-bottom: 1em;
        }

        .label-text {
            display: block;
            font-weight: 600;
            color: #1c1c2a;
            margin-bottom: 0.4em;
        }

        .text-muted-small {
            margin-right: 5px;
            font-size: 18px;
            color: #666;
        }

        .slider-wrapper {
            vertical-align: center;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .range {
            -webkit-appearance: none;
            appearance: none;
            background: transparent;
            cursor: pointer;
            width: 25rem;
        }

        /* Removes default focus */
        .range:focus {
            outline: none;
        }

        /******** Chrome, Safari, Opera and Edge Chromium styles ********/
        /* slider track */
        .range {
            background-color: #e7e6ef;
            border-radius: 0.5rem;
            height: 0.5rem;
        }

        /* slider thumb */
        .range::-webkit-slider-thumb {
            -webkit-appearance: none; /* Override default look */
            appearance: none;
            margin-top: -4px; /* Centers thumb on the track */
            background-color: #716f6f;
            border-radius: 0.5rem;
            height: 1rem;
            width: 1rem;
        }

        .range:focus::-webkit-slider-thumb {
            outline: 3px solid #716f6f;
            outline-offset: 0.125rem;
        }

        .range {
            border-bottom: none !important;
            width: 15em !important;
        }

        .ui-datepicker .ui-datepicker-header {
            display: none !important;
        }

        .ui-datepicker-calendar thead {
            display: none !important;
        }

        .js-refinance-block .error-info {
            color: #f11;
            margin: 0;
            padding: 10px 0 0 0;
            display: block !important;
        }

        .js-step-3 .accept_credit_actions {
            justify-content: unset !important;
        }

        .get_refinance_btn[disabled] {
            background-color: #6ABB6A !important;
            border-color: #6ABB6A !important;
        }

        .get_refinance_btn[disabled]:hover {
            background-color: #6ABB6A !important;
            border-color: #6ABB6A !important;
        }
    </style>

{/if}