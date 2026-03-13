{if isset($orderData->newyear_promo) && $orderData->newyear_promo}
    {assign var="promo" value=$orderData->newyear_promo}

    <div class="newyear-promo-banner" data-order-id="{$orderData->order->order_id|default:$orderData->order->id}" data-user-id="{$orderData->balance->user_id}">
        <div class="newyear-promo-banner__bg" style="background-image: url('/design/boostra_mini_norm/assets/image/new_year_2025_promotion_banner_bg.png');">
            <div class="newyear-promo-banner__content">
                {if !$promo->discount_activated}
                    {* Первоначальное состояние - кнопка получения скидки *}
                    <div class="newyear-promo-banner__initial">
                        <p class="newyear-promo-banner__text">Завершите 2025 год с чистой <br> историей! Погасите задолженность
                            <br> с персональной скидкой!</p>
                        <button type="button" class="newyear-promo-banner__btn newyear-promo-banner__btn--get-discount" data-action="get_discount">
                            Получить скидку
                        </button>
                        <br>
                        {if $promo->pdf_url}
                            <a href="{$promo->pdf_url}" class="newyear-promo-banner__btn newyear-promo-banner__btn-small newyear-promo-banner__btn-m0" target="_blank">Условия</a>
                        {/if}
                    </div>
                {else}
                    {* Состояние после активации скидки *}
                    <div class="newyear-promo-banner__activated">
                        {assign var="newyear_amount" value=$orderData->balance->sum_od_with_grace + $orderData->balance->sum_percent_with_grace}

                        <p class="newyear-promo-banner__title">К оплате со скидкой: 
                            <span class="newyear-promo-banner__amount">{$newyear_amount|number_format:0:",":" "} рублей</span>
                        </p>
                        
                        {if $promo->is_active}
                            <div class="newyear-promo-banner__countdown" data-remaining="{$promo->remaining_time}">
                                {* Часы *}
                                <input type="text" class="newyear-promo-banner__countdown-digit" value="0" readonly>
                                <input type="text" class="newyear-promo-banner__countdown-digit" value="0" readonly>
                                <span class="newyear-promo-banner__countdown-separator">:</span>
                                {* Минуты *}
                                <input type="text" class="newyear-promo-banner__countdown-digit" value="0" readonly>
                                <input type="text" class="newyear-promo-banner__countdown-digit" value="0" readonly>
                                <span class="newyear-promo-banner__countdown-separator">:</span>
                                {* Секунды *}
                                <input type="text" class="newyear-promo-banner__countdown-digit" value="0" readonly>
                                <input type="text" class="newyear-promo-banner__countdown-digit" value="0" readonly>
                            </div>
                        {/if}

                        <form method="POST" action="user/payment" class="user_payment_form form-pay" style="display: inline-block;">
                            <input type="hidden" name="number" value="{$orderData->balance->zaim_number}"/>
                            <input type="hidden" name="newyear_payment" value="true"/>
                            <input type="hidden" name="order_id" value="{$orderData->order->order_id|default:$orderData->order->id}"/>
                            <input type="hidden" class="payment_amount" data-order_id="{$orderData->order->order_id|default:$orderData->order->id}"
                                data-user_id="{$orderData->balance->user_id}" name="amount"
                                value="{str_replace(',', '', number_format($newyear_amount, 2))}"
                                max="{str_replace(',', '', number_format($newyear_amount, 2))}"
                                min="1">
                            <button type="button" class="newyear-promo-banner__btn newyear-promo-banner__btn--pay" data-action="pay">
                                Оплатить со скидкой
                            </button>
                        </form>
                        <br>
                        {if $promo->pdf_url}
                            <a href="{$promo->pdf_url}" class="newyear-promo-banner__btn newyear-promo-banner__btn-small newyear-promo-banner__btn-m0" target="_blank">Условия</a>
                        {/if}
                    </div>
                {/if}
            </div>
        </div>
    </div>
    
{/if}

