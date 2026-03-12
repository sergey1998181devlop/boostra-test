{if $user_order['user_amount'] > 5000}
    {$min_range = $user_order['user_amount']}
{else}
    {$min_range = 5000}
{/if}

{if $user_order['approve_max_amount'] <= 30000}
    {$max_range = $user_order['max_amount']}
{else}
    {$max_range = $user_order['approve_max_amount']}
{/if}
<div id="edit-amount">
    <h4 class="text-orange">Вы можете изменить сумму займа</h4>
    <div class="slider-box">
        <div class="slider-loan-type">
            <span></span>
        </div>
        <div class="money-edit">
            <span class="edit-amount-value">{$min_range|number_format:0:" ":" "}</span>
            <span class="ion-btn ion-minus ion-il"></span>
            <div>
                {*  !!!
                    Если меняете логику калькулятора - поменяйте и её проверку в UserView (edit_amount action)
                 *}
                <input type="text"
                       id="money-edit"
                       name="amount_edit"
                       data-max="{$max_range}"
                       data-min="{$min_range}"
                       data-step="1000"
                       data-init_value="{$user_order['approved_amount']}"
                       value="{$user_order['amount']}" />
            </div>
            <span class="ion-btn ion-plus ion-il"></span>
            <span class="edit-amount-value">{$max_range|number_format:0:" ":" "}</span>
        </div>
        <div class="time-edit">
            <span class="edit-period-value">5 дней</span>
            <span class="ion-btn ion-minus ion-il"></span>
            <div>
                <input type="text"
                       id="time-edit"
                       name="period_edit"
                       data-init_value="{$user_order['period']}"
                       value="{$user_order['period']}" />
            </div>
            <span class="ion-btn ion-plus ion-il"></span>
            <span class="edit-period-value">{($user_order['max_period']/7)} {($user_order['max_period']/7)|plural:' Неделя':' Недель':' Недели'}</span>
        </div>
        <div
            id="full-loan-info"
            data-percent="{$user_order['percent']}"
            data-period="{$user_order['period']}"
            data-amount="{$user_order['amount']}"
            data-promocode="{$user_order['promocode']}"
            style="font-size: 2rem!important;"
        ></div>
        <button
            type="button"
            class="button bg-orange"
            id="accept_edit_amount"
            data-order="{$user_order['id']}"
        >Подтвердить изменение</button>
    </div>
</div>

{capture name=page_scripts}
    <script>
        var calculator_il_params = {
            min_period: 5,
            max_period: {$user_order['max_period']},
            min_amount: 4000,
            max_amount: 100000,
            min_il_period: {$user_order['min_period']}
        };
    </script>
    <script src="design/{$settings->theme}/js/calculate_il.js?v=1.621" type="text/javascript"></script>

    {*Отправка метрики по кнопке получить займ в ЛК в зависимости от типа клиента https://trello.com/c/oL2cPB2c*}
    <script>
        $('#open_accept_modal').click(function(){
            {if $user->loan_history|count == 0}
                sendMetric('reachGoal', 'get_money_btn_nk');
            {else}
                sendMetric('reachGoal', 'get_money_btn_pk');
            {/if}
        });

        $('#autoapprove_card_reassign').click(function (){
            $(".cards").get(0).scrollIntoView( { behavior: 'smooth' } );
        });

        $('#autoapprove_card_modal_btn').click(function () {
            $('#autoapprove_card_modal').show();
            $.magnificPopup.open({
                items: {
                    src: '#autoapprove_card_modal'
                },
                type: 'inline',
                showCloseBtn: false,
                modal: true,
            });
        });

        $('#js-other-card-btn').click(function () {
            $.ajax({
                url: 'ajax/autoapprove_actions.php',
                data: {
                    'action': 'reject'
                },
                success: function(resp){
                    console.log(resp);
                    location.reload();
                }
            });
        });
    </script>
{/capture}
