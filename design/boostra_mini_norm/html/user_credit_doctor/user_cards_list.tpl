{if $cards}
    <h3><b>Доступные карты</b></h3>
    <ul class="payment-card-list">
        {foreach $cards as $key => $card}
            <li>
                <input type="radio" name="card_id" id="card_{$key}" value="{$card->CardId}"
                       {if ($card->checked)}checked{/if} />
                <label for="card_{$key}"><span>{$card->Pan}</span><span>{$card->expdate_formated}</span></label>
            </li>
        {/foreach}
    </ul>
{else}
    <h3><b>Нет доступных карт</b></h3>
{/if}

<label for="card_other" class="btn-secondary">
    <input type="radio" name="card_id" id="card_other" value="card_other"/>
    Оплатить с новой карты
</label>

