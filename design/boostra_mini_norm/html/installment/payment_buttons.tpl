{$pdp = $order_data->balance->details['ОбщийДолг'] - $order_data->balance->details['Баланс']}
{* Расчет multipolis_amount для следующего платежа *}
{if !$user_data['whitelist_dop'] || !$settings->whitelist_dop}
    {assign var="multipolis_for_next_payment" value=$order_data->balance->details['multipolis_amount']}
    {assign var="is_multipolis" value=$order_data->order->additional_service_multipolis|intval}
{else}
    {assign var="multipolis_for_next_payment" value=0}
    {assign var="is_multipolis" value=0}
{/if}

{$next_payment = $order_data->balance->details['БлижайшийПлатеж_Сумма'] + $order_data->balance->details['ПросроченныйДолг'] + $multipolis_for_next_payment}

{*{if $order_data->order->additional_service_repayment}
    {assign var="price" value=$vita_med->price}
{elseif $order_data->order->half_additional_service_repayment}
    {math equation="floor(price / 2)" price=$vita_med->price assign="price"}
{else}
    {assign var="price" value=0}
{/if}*}

{assign var="price" value=0}
{assign var="oracle_price" value=0}
{assign var="full_payment_multipolis_amount" value=0}

{* Расчет всех доп. услуг в одном блоке *}
{if !$user_data['whitelist_dop'] || !$settings->whitelist_dop}
    

    {* Multipolis для полного погашения *}
    {if $order_data->order->additional_service_multipolis|intval && !$srkv_concierge_blocked}
        {math
        assign="full_payment_multipolis_amount"
        equation="floor((a - b) * c)"
        a=$order_data->balance->details['ПроцентыДоПогашения']
        b=$order_data->balance->details['ОбщийДолг_Проценты']
        c=$order_data->order->additional_service_multipolis|intval
        }
    {/if}

{/if}

{assign var="full_amount_value" value=$pdp + $price + $full_payment_multipolis_amount}


{if $pdp>0}

    <div class="js-il-payment-buttons"
        data-pdp="{$pdp}"
        data-percent="{$order_data->balance->details['ПроцентыДоПогашения']}"
        data-percent-current="{$order_data->balance->details['ОстатокПроцентовНаДатуПлатежа']}"
        data-need-accept="{$order_data->balance->need_accept}"
        data-next-payment="{$next_payment}"
        data-phone="{$user->phone_mobile}"
        data-contract-number="{$order_data->balance->zaim_number}"
        data-contract-date="{$order_data->balance->zaim_date}"
        data-user-id="{$user->id}"
    >

        {if $order_data->balance->details['БлижайшийПлатеж_Дата'] && $order_data->balance->details['Баланс'] < $order_data->balance->details['БлижайшийПлатеж_Сумма']}

        <form method="POST" action="user/payment" style="margin-top:15px;" class="user_payment_form" >
            <input type="hidden" name="number" value="{$order_data->balance->zaim_number}" />
            <input type="hidden" name="order_id" value="{$order_data->order->order_id}" />
            <input type="hidden" name="multipolis_amount" value="{$multipolis_for_next_payment}"/>
            <input type="hidden" name="multipolis" value="{$is_multipolis}"/>
            <div class="action">
                <input style="display:none" class="payment_amount" data-order_id="{$order_data->balance->zaim_number}"
                       data-user_id="{$user->id}" type="text" name="amount"
                       value="{$next_payment}"/>
                <button class="payment_button green button medium js-save-click" data-user="{$user->id}" data-event="10" type="submit">
                    Оплатить текущий платеж {$next_payment} руб
                </button>
                {if $order_data->balance->details['ПросроченныйДолг'] > 0}
                    <br/>
                    <small class="red">(с учетом просроченного платежа {$order_data->balance->details['ПросроченныйДолг']} руб.)</small>
                {/if}
            </div>
        </form>
        {/if}

        <div class="user_payment_form">
            <div class="action">
                <button class="payment_button button medium button-inverse js-save-click js-open-chdp-form" data-user="{$user->id}" data-event="11" data-form-id="other_summ_{$order_data_index}" type="button">
                    Оплатить любую сумму
                </button>
            </div>
        </div>
        <form method="POST" action="user/payment" id="other_summ_{$order_data_index}" class="user_payment_form js-il-chdp-form" style="display:none;padding:40px 0;">
            <input type="hidden" name="number" value="{$order_data->balance->zaim_number}" />
            <input type="hidden" name="order_id" value="{$order_data->order->order_id}" />
            <input type="hidden" name="sms" value="" />

            {if $order_data->order->additional_service_partial_repayment || $order_data->order->half_additional_service_partial_repayment}
                <input type="hidden" name="tv_medical_amount" value="0"/>
                <input type="hidden" name="tv_medical" value="0"/>
                <input type="hidden" name="tv_medical_id" value="0"/>
            {/if}

            {if $order_data->order->additional_service_so_partial_repayment || $order_data->order->half_additional_service_so_partial_repayment}
                <input type="hidden" name="star_oracle_amount" value="0"/>
                <input type="hidden" name="star_oracle" value="0"/>
                <input type="hidden" name="star_oracle_id" value="0"/>
            {/if}

            <div class="action">
                <input class="payment_amount js-il-chdp-amount" data-order_id="{$order_data->balance->zaim_number}" data-user_id="{$user->id}" type="text" name="amount"
                       value="{$next_payment}" max="{$next_payment}" data-rec="{$next_payment}" min="1" />

                {if $order_data->order->additional_service_partial_repayment || $order_data->order->half_additional_service_partial_repayment || $order_data->order->additional_service_so_partial_repayment || $order_data->order->half_additional_service_so_partial_repayment}
                    <input class="hidden_amount" type="hidden" name="hidden_amount" id="hidden_amount_{$order_data_index}" value=""/>
                {/if}
                <button class="payment_button button medium js-save-click js-il-chdp-button" data-user="{$user->id}" data-event="12" type="button">Оплатить</button>
                <span class="js-il-chdp-amount-error il-chdp-amount-error">&nbsp;</span>
            </div>
            
            {* Предупреждающая надпись *}
            <div class="js-il-warning-message" style="display:none; color: #f22; margin-top: 10px; font-size: 0.9rem;">
                Денежные средства будут учтены в дату платежа по действующему графику
            </div>
            
            <div class="js-il-chdp-checkbox-block" style="display:none; margin-top: 10px;">
                <label class="spec_size">
                    <div class="checkbox"
                         style="border-width: 1px;width: 16px !important;height: 16px !important;">
                        <input class="js-il-chdp-checkbox" type="checkbox" value="1"
                               id="chdp_{$order_data_index}"
                               name="chdp" />
                        <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                    </div>
                    <a href="#" data-href="preview/chdp" target="_blank" class="js-il-document-link">
                        Заявление на частичное доcрочное погашение
                    </a>
                    <p class="js-il-document-link" style="max-width: 800px; font-size: 1.1rem;">
                      ВНИМАНИЕ!
                      Внесение частично-досрочного платежа не влияет на обязанность внесения ближайшего планового платежа.
                      Сумма ближайшего планового платежа будет перерассчитана с учетом внесенного частично-досрочного платежа.
                      Обновленный график платежей будет доступен в Вашем личном кабинете.
                    </p>
                </label>

            </div>
            <div class="js-il-chdp-accept-block" style="display:none">
                <p></p>

                <div style="padding-bottom:10px">
                    <input type="text" inputmode="numeric" name="sms_code" autocomplete="one-time-code" class="js-il-chdp-code" value="" placeholder="Код из СМС" />
                    <div class="js-il-chdp-code-error" style="color:red"></div>
                    <br />
                    <a href="javascript:void(0);" class="js-il-chdp-code-repeat">отправить код еще раз</a>
                </div>
                <div style="margin-top:10px;">
                    <button class="button medium js-il-chdp-code-button" type="button">
                        Оплатить
                    </button>
                </div>

            </div>
        </form>


        <div class="user_payment_form"  style="display:none;padding:40px 0;">
            <div class="action">
                <button class="payment_button button button-inverse btn-600 btn-fsize-14 js-save-click" data-user="{$user->id}" data-event="13" type="button">
                    Погасить заём досрочно
                </button>
            </div>
        </div>

        <form method="POST" action="user/payment" id="other_summ_{$order_data_index}" class="user_payment_form js-il-pdp-form" style="">
            <input type="hidden" name="number" value="{$order_data->balance->zaim_number}" />
            <input type="hidden" name="order_id" value="{$order_data->order->order_id}" />
            <input type="hidden" name="sms" value="" />

            <input type="hidden" name="tv_medical_amount" value="{$price}"/>
            <input type="hidden" name="tv_medical" value="{$price > 0|intval}"/>
            <input type="hidden" name="tv_medical_id" value="{$vita_med->id}"/>
            
            <input type="hidden" name="multipolis_amount" value="{$full_payment_multipolis_amount}"/>
            <input type="hidden" name="multipolis" value="1"/>

            <div class="action">
                <input class="payment_amount" data-order_id="{$order_data->balance->zaim_number}" data-user_id="{$user->id}"
                    type="hidden" name="amount"
                       value="{$full_amount_value}" max="{$full_amount_value}" data-rec="{$order_data->balance->details['БлижайшийПлатеж_Сумма']}" min="1"/>
                <button class="payment_button button medium button-inverse js-save-click js-il-pdp-button" data-user="{$user->id}" data-event="12" type="button">
                    Полное погашение займа {($full_amount_value * (1 + $order_data->balance->details['fee']))|number_format:2:'.':''} руб.
                </button>
            </div>
            <div class="js-il-pdp-accept-block" style="display:none">
                <p></p>

                <div style="padding-bottom:10px">
                    <input type="text" inputmode="numeric" autocomplete="one-time-code" name="sms_code" class="js-il-pdp-code" value="" placeholder="Код из СМС" />
                    <div class="js-il-pdp-code-error" style="color:red"></div>
                    <br />
                    <a href="javascript:void(0);" class="js-il-pdp-code-repeat">отправить код еще раз</a>
                </div>
                <label class="spec_size" style="">
                    <div class="checkbox"
                         style="border-width: 1px;width: 16px !important;height: 16px !important;">
                        <input class="js-il-pdp-checkbox" type="checkbox" value="1"
                               id="pdp_{$order_data_index}"
                               name="pdp" checked="" />
                        <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                    </div>
                    <a href="#" data-href="preview/pdp" target="_blank" class="js-il-document-link">
                        Заявление на полное доcрочное погашение
                    </a>
                </label>
                <div style="margin-top:10px;">
                    <button class="button medium js-il-pdp-code-button" type="button" disabled>
                        Оплатить
                    </button>
                </div>

            </div>
        </form>
    </div>

{/if}
