<style>
    .il-chdp-amount-error {
        display: block;
        color: #f22;
    }
</style>
<div class="split">
    <ul>
        <li>
            <div>Итоговая сумма ⃰
            </div>
            <div>
                {$user_balance->details['total_amount']}
            </div>
        </li>
        {if $user_balance->details['Баланс']}
        <li>
            <div>Сумма на балансе</div>
            <div>
                {$user_balance->details['Баланс']}
            </div>
        </li>
        {/if}
        <li>
            <div>
                Очередной платеж
                <br />
                <p><a href="user/schedule_payments">График платежей</a></p>
            </div>
            <div>
                {if $user_balance->details['БлижайшийПлатеж_Сумма'] > 0 && $user_balance->details['Баланс'] >= $user_balance->details['БлижайшийПлатеж_Сумма']}
                    Оплачен
                {else}
                    {if !$user_data['whitelist_dop'] || !$settings->whitelist_dop}
                        {assign var="multipolis_for_next_payment" value=$order_data->balance->details['multipolis_amount']}
                    {else}
                        {assign var="multipolis_for_next_payment" value=0}
                    {/if}
                    {$user_balance->details['БлижайшийПлатеж_Сумма'] + $user_balance->details['ПросроченныйДолг'] + $multipolis_for_next_payment}
                {/if}
            </div>
        </li>
        <li>
            {if $user_balance->details['БлижайшийПлатеж_Дата']}
            <div>Дата очередного платежа</div>
            <div>{$user_balance->details['БлижайшийПлатеж_Дата']|date}</div>
            {else}
            <div class="red text-left">Ваш заём просрочен</div>
            {/if}
        </li>
    </ul>
</div>
