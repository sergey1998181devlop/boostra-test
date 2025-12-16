<div class="no_cards" style="font-size: 1.5rem;">
    {if $has_approved_order}
        <div class="green">
            <h2 style="margin-bottom: 12px">Поздравляем! По вашей заявке одобрено {if $divide_pre_order}
                    {$divide_pre_order->amount + $user_order['amount']}
                {else}
                    {if $isAutoAcceptCrossOrders}
                        {$totalApproveAmount}
                    {else}
                        {$user_order['approve_max_amount']}
                    {/if}
                {/if} руб.
            </h2>
            <h2>Для получения займа привяжите СБП счет или добавьте карту в "Мои реквизиты".</h2>
        </div>
    {else}
        <div class="red">
            <h2>Для получения займа привяжите СБП счет или добавьте карту в "Мои реквизиты"</h2>
        </div>
    {/if}

    <div style="margin-top: 15px; font-weight: 700;">
        <p>Обращаем внимание, что номер телефона и ФИО в личном кабинете должны совпадать с номером телефона и ФИО, привязанные к счету.<br>
        Несоответствие данных может привести к невозможности получения займа.</p>
    </div>
</div>
