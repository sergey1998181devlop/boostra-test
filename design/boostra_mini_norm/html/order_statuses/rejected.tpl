{* Используем флаги конкретной заявки вместо глобальных переменных *}
{assign var="order_reason_block" value=$order._flags.reason_block_date}

{if $order_reason_block}
    <span class="has-reason-block"></span>

    {if $show_moratorium_only || $rcl_loan}
        {* Только мораторий без отказа *}
        <div class="status-box status-box--warning">
            <div class="status-box__icon">
                <svg viewBox="0 0 24 24">
                    <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                </svg>
            </div>
            <div class="status-box__content">
                {if $order_reason_block == 999}
                    <h3 class="status-box__title">Подача заявки недоступна</h3>
                    <p class="status-box__text">Вы не можете оставить заявку.</p>
                {else}
                    <h3 class="status-box__title">Временное ограничение</h3>
                    {if $rcl_loan}
                        <p class="status-box__text">К сожалению, прямо сейчас мы не можем выдать вам деньги.<br>У вас есть одобренная Кредитная линия и вы сможете повторно запросить выдачу {$order_reason_block|date} {$order_reason_block|time} (мск).</p>
                    {else}
                        <p class="status-box__text">Вы можете повторно обратиться за займом: {$order_reason_block|date} {$order_reason_block|time} (мск)</p>
                    {/if}
                {/if}
            </div>
        </div>
    {else}
        <div class="status-box status-box--error">
            <div class="status-box__icon">
                <svg viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </div>
            <div class="status-box__content">
                <h3 class="status-box__title">К сожалению, по вашей заявке отказано</h3>
                {if $order.official_response}
                    <p class="status-box__text">Причина отказа: {$order.official_response}</p>
                {/if}
                {if $order_reason_block == 999}
                    <p class="status-box__text">Вы не можете оставить заявку.</p>
                {else}
                    <p class="status-box__text">Вы можете повторно обратиться за займом: {$order_reason_block|date} {$order_reason_block|time} (мск)</p>
                {/if}
            </div>
        </div>
        {include file='order_statuses/rejected_reason_button.tpl' order=$order}
    {/if}

{elseif $order._flags.is_rejected}
    {* Отказ без моратория *}
    
    {if $first_time_visit_after_rejection}
        <span class="first_time_visit_after_rejection"></span>
    {/if}

    {if $repeat_loan_block}
        <div class="status-box status-box--error">
            <div class="status-box__icon">
                <svg viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </div>
            <div class="status-box__content">
                <h3 class="status-box__title">К сожалению, по вашей заявке отказано</h3>
                {if $order.official_response}
                    <p class="status-box__text">Причина отказа: {$order.official_response}</p>
                {/if}
                <p class="status-box__text">Вы можете повторно обратиться за займом: {$repeat_loan_block|date} {$repeat_loan_block|time} (мск)</p>
            </div>
        </div>
        {include file='order_statuses/rejected_reason_button.tpl' order=$order}

    {elseif $next_loan_mandatory}
        <div class="status-box status-box--error">
            <div class="status-box__icon">
                <svg viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </div>
            <div class="status-box__content">
                <h3 class="status-box__title">К сожалению, по вашей заявке отказано</h3>
                <p class="status-box__text">Вы можете подать новую заявку прямо сейчас.</p>
            </div>
        </div>
    {else}
        {if $user->fake_order_error == 0}
            <div class="status-box status-box--error">
                <div class="status-box__icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </div>
                <div class="status-box__content">
                    <h3 class="status-box__title">К сожалению, по вашей заявке отказано</h3>
                    {if !$order._flags.is_not_issued
                        && !$user->file_uploaded
                        && !Helpers::isFilesRequired($user)
                        && ($quantity_loans_block || $redirect)
                        && !$order._flags.is_1c_closed}
                        <p class="status-box__text">Дата заявки: {$order.date|date}</p>
                    {/if}
                    {if $order.official_response}
                        <p class="status-box__text">Причина отказа: {$order.official_response}</p>
                    {/if}
                </div>
            </div>

            {include file='order_statuses/rejected_reason_button.tpl' order=$order}
        {/if}
    {/if}
{/if}
