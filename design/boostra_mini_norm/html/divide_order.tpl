{literal}
    <style>
        #divide-order .btn-wrapper {
            display: grid;
            grid-template: 1fr / 1fr;
            grid-gap: 15px;
            margin-top: 15px;
            justify-items: center;
        }

        #divide-order .btn-wrapper  .btn-inner {
            display: flex;
            flex-flow: column;
            gap: 15px;
        }

        #divide-order .btn-wrapper .btn-inner > div{
            display: flex;
            gap: 15px;
            align-items: center;
        }

        #divide-order h5 {
            font-size: 32px;
        }

        #divide-order p,  #divide-order button {
            margin: 0 0 15px !important;
            font-size: 14px !important;
            line-height: 1 !important;
        }

        #divide-order button {
            font-size: 12px;
            margin-bottom: 0 !important;
        }

        @media screen and (max-width: 768px) {

        }
    </style>
{/literal}

{if $last_order['status'] != 3}

    <div id="divide-order" class="wrapper_border-green">
        {if $divide_order->data->auto_generate == 1}
            <h4 class="text-center animate_text"><b>Вам доступен второй займ прямо сейчас!</b></h4>
        {else}
            <p class="text-center">Ваш заём был разделён на 2 части</p>
        {/if}
        <div class="btn-wrapper">
            <div class="btn-inner">
                {if !$divide_order}
                    <div id="open_accept_modal_wrapper">
                        <div>
                            <h5>{$last_order['amount']}</h5>
                        </div>
                        <div>
                            <button id="open_accept_modal" onclick="sendMetric('reachGoal','divide_order_click_get_one')" class="green button">Забрать сейчас</button>
                        </div>
                    </div>
                {/if}
                <div>
                    <div>
                        <h5>
                            {if $divide_order->data->auto_generate == 1}
                                {$order_data->order->amount}
                            {else}
                                {$divide_pre_order->amount}
                            {/if}
                        </h5>
                    </div>
                    <div>
                        {if $divide_pre_order_accept_date}
                            <button class="bg-light-green button text-orange">Доступен с {$divide_pre_order_accept_date}</button>
                        {elseif ($divide_order->data->status == 'ISSUED' && $order_data->order->status_1c != '5.Выдан')}
                            <p class="text-orange" style="margin-bottom: 0 !important;">Ожидайте поступления денежных средств...</p>
                        {else}
                            <button onclick="sendMetric('reachGoal','divide_order_click_get_two')" class="animate_text green button open_calculator__pre_order">Забрать сейчас</button>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {if $divide_order->data->status === 'APPROVED'}{*Если второй займ одобрен, показываем калькулятор*}
        {include 'calculator_pre_order.tpl'}
    {/if}

{/if}