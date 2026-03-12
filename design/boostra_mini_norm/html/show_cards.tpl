<style>
    .floating-label {
        font-size: 20px;
        font-weight: 700;
    }
</style>

<label>
    <span class="floating-label">
        {if $cards && $sbp_accounts}
            Получить на карту / СБП счет:
        {elseif $cards}
            Получить на карту:
        {elseif $sbp_accounts}
            Получить через СБП счет:
        {/if}
    </span>

    <div class="split">

        <input type="hidden" name="b2p" value="{$use_b2p}"/>

        {if $cards}
            <ul class="card-list-for-order">
                {foreach $cards as $card}
                    {if !$card->deleted && !$card->deleted_by_client}
                        <li data-card-id-deleting="{$card->id}">
                            <label>
                                <div class="radio">
                                    <input type="radio" name="card" value="{$card->id}"
                                           {if !$selected_sbp_account_id && $card@first}checked="true"{/if} />
                                    <span></span>
                                </div>
                                {$card->pan|regex_replace:'/^(\d{4})\d{2}(\*{6})(\d{4})$/':'$1********$3'}
                            </label>
                        </li>
                    {/if}
                {/foreach}
            </ul>
        {/if}

        {if $sbp_accounts}
            <ul class="card-list-for-order sbp-list">
                {foreach $sbp_accounts as $sbp}
                    {if !$sbp->deleted}
                        <li data-sbp-id="{$sbp->id}">
                            <label>
                                <div class="radio">
                                    <input type="radio" name="card" value="sbp:{$sbp->id}"
                                           {if $selected_sbp_account_id == $sbp->id}checked="true"{/if}
                                            {if !$selected_sbp_account_id && !$cards && $sbp@first}checked="true"{/if} />
                                    <span></span>
                                </div>
                                {$sbp->title} (СБП)
                            </label>
                        </li>
                    {/if}
                {/foreach}
            </ul>
        {/if}

    </div>
</label>

{if !$user_has_sbp && !$selected_bank}
    <div style="text-align:center;margin:15px 0;">
        <button class="button small choose_bank" type="button" onclick="changeSbpBank()" style="font-size:0.9rem;">
            Выбрать банк для выплаты
        </button>
    </div>
{/if}