
{if $order._flags.is_1c_issued}
    {if empty($debtInDays) || ($debtInDays < 1 && $debtInDays > 3)}
        {include file='modals/user_feedback_modal.tpl' user_id=$user->id order_id=$order.id}
    {/if}
{/if}

{if $order._flags.is_wait_card}
    <div class="status-box status-box--warning">
        <div class="status-box__icon">
            <svg viewBox="0 0 24 24">
                <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
            </svg>
        </div>
        <div class="status-box__content">
            <h3 class="status-box__title">Требуется привязка карты</h3>
            <p class="status-box__text">Для завершения выдачи займа необходимо привязать карту.</p>
        </div>
    </div>

    <form id="confirm-card-form" class="confirm-card-form" data-order_id="{$order.id}" onsubmit="return false;">
        <input type="hidden" name="card_id" value="{$order.card_id}" />
        <input type="hidden" name="organization_id" value="{$order.organization_id}">

        <div class="card-confirm" id="card-confirm">
            <div class="request-error-block" style="display: none">
            </div>

            <button class="confirm-button" id="confirm-button">Привязать карту</button>
        </div>
    </form>
{/if}
{if !$is_admin && !$is_looker}
    <script>
        $(function () {
            {if $user->loan_history|count == 0}
                sendMetric('reachGoal', 'dogovor_podpisan_nk');
            {else}
                sendMetric('reachGoal', 'dogovor_podpisan_pk');
            {/if}
        });
    </script>
{/if}
