⚠️ <b>Ошибка при расчете ПДН BOOSTRA</b>

📝 <b>Заявка:</b> <a href="https://manager.boostra.ru/order/{$order_id}/">{if !empty($order_number)}{$order_number}{else}{$order_id}{/if}</a>
❌ <b>Ошибка:</b> {$error|default:"Неизвестная ошибка"}
📅 <b>Дата:</b> {$calculate_date}

{* короткий номер заявки *}
{if !empty($order_number)}
    {assign var=order_short value=$order_number|regex_replace:"/-.*/":""}
{elseif !empty($order_id)}
    {assign var=order_short value=$order_id}
{else}
    {assign var=order_short value="unknown"}
{/if}

#{$order_short}_{$calculate_date|date_format:"%d_%m_%Y_BOOSTRA"}