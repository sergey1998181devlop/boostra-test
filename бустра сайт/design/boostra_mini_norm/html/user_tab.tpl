{if $restricted_mode === 1 && (in_array($due_days, [0])) && $due_days !== 'not'}
    <div class="container">
        <div class="row">
            <div class="col-md-6 row restrict_mode_panel">
                <div class="col-md-8 restrict_info">
                    <h2>{$salute_prefix|escape}, <span class="restrict_salute">{$salute|escape}</span>!</h2>
                    <div class="restrict_alert row">
                        <div class="col-md-3 hidden-xs">
                            <img src="design/{$settings->theme|escape}/img/restrict/alert1.png">
                        </div>
                        <div class="col-md-7">
                            Предлагаем Вам воспользоваться <span style="color: #684A2D; text-decoration: underline">уникальным предложением</span> для постоянных клиентов, которые ценят своё время и деньги.
                        </div>
                    </div>
                    {if !$friend_restricted_mode}
                        <div class="restrict_info_text">Мы подготовили для Вас заём с увеличенной суммой и уверены, что новый заём станет для Вас еще одним шагом к финансовому благополучию и поможет достичь тех целей, к которым Вы стремитесь.</div>
                    {/if}
                    <br>
                    <span class="restrict_divider"></span><br>
                    <div class="restrict_alert_text">
                        Помните, что каждое Ваше решение открывает двери к новым возможностям.
                    </div>
                    <div class="restrict_alert row">
                        <div class="col-md-3 hidden-xs">
                            <img src="design/{$settings->theme|escape}/img/restrict/alert2.png">
                        </div>
                        <div class="col-md-7">
                            Мы верим в Вас и Вашу способность делать правильные шаги на пути к успеху. Давайте вместе строить Ваше блестящее будущее.
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-md-offset-1 hidden-xs restrict_sidebar">
                    <img src="design/{$settings->theme|escape}/img/restrict/sidebar.png">
                </div>
                <div class="clear"></div>
                <div class="restrict_img_bg hidden-xs" style="background: url('design/{$settings->theme|escape}/img/restrict/bg_img.png')"></div>
                {foreach $all_orders as $key => $orders_data}
                    {foreach $orders_data as $order_data}
                        {if $order_data->balance->zaim_number != null}
                            {if $order_data->order->additional_service_repayment}
                                {if ($order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni >= 500)}
                                    <input type="hidden" name="tv_medical_amount" value="{$vita_med->price}"/>
                                    <input type="hidden" name="tv_medical" value="1"/>
                                    <input type="hidden" name="tv_medical_id" value="{$vita_med->id}"/>
                                    {assign var="amount_value" value=$order_data->balance->ostatok_od + $vita_med->price + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni + $order_data->balance->penalty}
                                {else}
                                    {assign var="amount_value" value=$order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni + $order_data->balance->penalty}
                                {/if}
                            {else}
                                {assign var="amount_value" value=$order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni + $order_data->balance->penalty}
                            {/if}
                            <br>
                            <div class="restrict_loan_info">
                                <div class="float_left_block" style="margin-right: 50px;">
                                    <p>Номер договора</p>
                                    <h3>{$order_data->balance->zaim_number}</h3>
                                </div>
                                <div class="float_left_block">
                                    <p>Сумма долга</p>
                                    <h3>{$order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni + $order_data->balance->penalty} руб.</h3>
                                </div>
                                <div class="clear"></div>
                                <div>
                                    <form method="POST" action="user/payment" class="user_payment_form" style="margin: 0;">
                                        <div class="action">
                                            {if $order_data->order->additional_service_repayment}
                                                {if ($order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni >= 500)}
                                                    <input type="hidden" name="tv_medical_amount" value="{$vita_med->price}"/>
                                                    <input type="hidden" name="tv_medical" value="1"/>
                                                    <input type="hidden" name="tv_medical_id" value="{$vita_med->id}"/>
                                                {/if}
                                            {/if}
                                            <input type="hidden" name="amthash" value="{base64_encode($amount_value)}">
                                            <input type="hidden" name="number" value="{$order_data->balance->zaim_number}"/>
                                            <input type="hidden" name="order_id" value="{$order_data->order->order_id}"/>
                                            <input style="display:none" class="payment_amount"
                                                   data-order_id="{$order_data->balance->zaim_number}" data-user_id="{$user->id}" type="text"
                                                   name="amount"
                                                   value="{$amount_value}"
                                                   max="{$amount_value}" min="1"/>
                                            <button class="restrict_button" data-user="{$user->id}"
                                                    data-event="4" type="submit">Заплатить и взять новый
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        {/if}
                    {/foreach}
                {/foreach}
            </div>
        </div>
    </div>
{else}
    {include 'user_current_loan_list.tpl'}
{/if}
