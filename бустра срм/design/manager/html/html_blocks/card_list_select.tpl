{if $card_list && $card_list|@count > 0}
    <select id="{$id_select}" class="form-control">
        <option disabled>--- Активные карты ---</option>
        {foreach $card_list as $card}
            {if $card->deleted}{continue}{/if}
            <option value="{$card->id}" {if $card->id == $order->card_id}selected{/if}>
                {$card->pan} {$card->expdate} ({$organizations[$card->organization_id]->short_name|escape})
            </option>
        {/foreach}
        <option disabled>--- Удаленные карты ---</option>
        {* Deleted cards *}
        {foreach $card_list as $card}
            {if ! $card->deleted}{continue}{/if}
            <option value="{$card->id}" {if $card->deleted}disabled{/if}>
                {$card->pan} {$card->expdate} ({$organizations[$card->organization_id]->short_name|escape})
            </option>
        {/foreach}
    </select>
{/if}