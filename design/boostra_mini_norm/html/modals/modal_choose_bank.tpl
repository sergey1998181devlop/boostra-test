<div id="sbp_banks_modal_overlay" style="display: none"></div>
<div id="sbp_banks_modal" class="modal" style="display: none">
    {if $show_close_button}
        <button class="close_choose_bank_button">×</button>
    {/if}
    <div class="modal-content">
        <div class="sbp_banks_modal_title">{if $show_text_to_choose_sbp && $user_has_sbp}Выберите привязанный СБП счет или банк, на который хотите получить деньги{else}Выберите банк с СБП счетом, на который хотите получить деньги{/if}</div>
        <div class="sbp_bank_list">
            {foreach $b2p_sbp_banks as $bank}
                <div class="sbp_bank" data-bank-id="{$bank->id}" {if $order_id_for_sbp}onclick="saveBank({$bank->id}, {$order_id_for_sbp})"{else}onclick="saveDefaultBank({$bank->id})"{/if}>
                    <div class="sbp_bank_logo">
                        <img src="https://sub.nspk.ru/proxyapp/logo/bank{$bank->id}.png" alt="{$bank->title}">
                    </div>
                    <span class="sbp_bank_title">{$bank->title}</span>
                    <svg width="15" height="22" viewBox="0 0 15 22" style="width: 9px;">
                        <path fill="currentColor" fill-opacity="0.7"
                              d="m4.431 21.4057 9.896-8.745c.8973-.7929.8973-2.0786 0-2.8715L3.922.5943C3.0253-.198 1.5692-.198.6725.5943s-.8967 2.0791 0 2.8715L9.453 11.225l-8.2713 7.3093c-.8967.7924-.8967 2.0791 0 2.8715.8967.7924 2.3527.7924 3.2494 0Z"></path>
                    </svg>
                </div>
            {/foreach}
        </div>
    </div>
</div>