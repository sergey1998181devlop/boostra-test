
{if $order._flags.can_show_paid_reason}
    <a type="button" 
       href="javascript:void(0)" 
       order-id="{$order.id}" 
       order-number="{$user->balance->zaim_number}" 
       class="button medium green" 
       id="btn-modal-quick-approval"
       style="margin-bottom: 20px;"
    >
        Узнай причину отказа за 49 руб.
    </a>
    <span class="payment-block-error">
        Не удалось оплатить
    </span>
{/if}
