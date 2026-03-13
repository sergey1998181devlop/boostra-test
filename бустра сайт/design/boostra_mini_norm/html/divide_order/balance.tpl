{if $happy_new_year == true}
    {assign var="total_debt" value=$order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni - (($order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni) * 30 / 100)}
{else}
    {assign var="total_debt" value=$order_data->balance->ostatok_od + $order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni}
{/if}
{assign var="has_penalty" value=$total_debt >= 1001}

{assign var="prolongation_amount" value=$order_data->balance->ostatok_percents + $order_data->balance->ostatok_peni + $order_data->balance->calc_percents}

{if $order_data->vitamed_disabled}
    {assign var="vita_med" value='{}'|json_decode}
    {assign var="tv_medical_tariffs" value=array()}
    {assign var="tv_medical_price" value=0}
{/if}

{assign var="payment_ts" value=$order_data->balance->payment_date|replace:'T':' '|@strtotime}
{assign var="thirty_days" value=60*60*24*30}

{if (!$user_data['whitelist_dop'] || !$settings->whitelist_dop) && $payment_ts && ($smarty.now - $payment_ts) <= $thirty_days}
    {assign var="prolongation_amount" value=$prolongation_amount + ($order_data->order->additional_service_multipolis|intval * $order_data->multipolis_amount) + ($order_data->order->additional_service_tv_med|intval * $order_data->prolongation_tv_medical_price)}
{/if}


{function name="log_fields" button_name=""}
    <input type="hidden" name="ostatok_od" value="{$order_data->balance->ostatok_od}" />
    <input type="hidden" name="ostatok_percents" value="{$order_data->balance->ostatok_percents}" />
    <input type="hidden" name="ostatok_peni" value="{$order_data->balance->ostatok_peni}" />
    <input type="hidden" name="penalty" value="{$order_data->balance->penalty}" />
    <input type="hidden" name="total_debt" value="{$total_debt}" />
    <input type="hidden" name="button_name" value="{$button_name}" />
    <input type="hidden" name="half_additional_service_repayment" value="{$order_data->order->half_additional_service_repayment}" />
    <input type="hidden" name="additional_service_repayment" value="{$order_data->order->additional_service_repayment}" />
    <input type="hidden" name="half_additional_service_so_repayment" value="{$order_data->order->half_additional_service_so_repayment}" />
    <input type="hidden" name="additional_service_so_repayment" value="{$order_data->order->additional_service_so_repayment}" />
{/function}

{function name="add_hidden_input_if" condition="" inputName="" value=0}
    {if $condition}
        <input type="hidden" name="{$inputName}" value="{$value}"/>
    {/if}
{/function}

{if $order_data->balance->zaim_number && $order_data->balance->zaim_number!='Ошибка' && $order_data->balance->zaim_number!='Нет открытых договоров'}
    {if $order_data->balance->sale_info=='Договор продан'}
        {if !isset($user_data['show_cession_info']) || $user_data['show_cession_info'] != '0'}
            <div class="about" style="max-width: 512px;">
                <div class="status-box status-box--warning" style="display: flex; align-items: flex-start;">
                    <div class="status-box__icon" style="flex-shrink: 0; margin-right: 15px;">
                        <svg viewBox="0 0 24 24">
                            <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                        </svg>
                    </div>
                    <div class="status-box__content" style="flex: 1;">
                        <h3 class="status-box__title" style="margin-top: 0;">Ваш договор продан</h3>
                        <p class="status-box__text" style="margin-bottom: 0;">
                            Договор № {$order_data->balance->zaim_number} продан {$order_data->balance->buyer}, номер
                            для связи:
                            <a href="tel:{$order_data->balance->buyer_phone}"
                               style="color: inherit; text-decoration: underline; font-weight: bold;">
                                {$order_data->balance->buyer_phone}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            {if $order_data->balance->buyer == 'БИКЭШ' && !$order_data->balance->is_cession_shown && file_exists("{$config->root_dir}/files/contracts/Cess/{$order_data->balance->zaim_number}.pdf")}
                <div style="display:block;
                        position: fixed;
                        top: 0;
                        right: 0;
                        bottom: 0;
                        left: 0;
                        padding-top: 60px;
                        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
                        ">
                    <div class="cdoctor-modal" style="max-width: 90%;">
                        <div class="cdoctor-modal-title">Ваш договор продан</div>
                        <div class="cdoctor-modal-price" style="margin: 0px 0;">
                            <embed src="{$config->root_url}/files/contracts/Cess/{$order_data->balance->zaim_number}.pdf"
                                   style="max-width:100%;height:430px;" type="application/pdf">
                        </div>
                        <div class="cdoctor-modal-link">
                            <a class="button medium" href="user?cession=shown">Я ознакомлен. Закрыть</a>
                        </div>
                    </div>
                </div>
            {/if}
        {/if}
    {elseif $order_data->balance->zaim_number=='Ошибка. Обратитесь в офис'}
        <div class="about">
            <div>{$order_data->balance->zaim_number}</div>
        </div>

    {else}
        <div class="about about_zaim">
            <div class="zaim_title">Заём
                {$organizations[$order_data->order->organization_id]|escape}
                <span class="zaim-number">{$order_data->balance->zaim_number}</span>
            </div>
            {if $restricted_mode !== 1}
                <a class="button button-inverse btn-600 btn-fsize-14 btn-line-h-24 {*view-contract*} " target="_blank" href="user/docs" data-number="{$order_data->balance->zaim_number}">Смотреть договор</a>
            {/if}
        </div>
        {if !empty($order_data->balance->buyer) && (!isset($user_data['show_agent_info']) || $user_data['show_agent_info'] != '0')}
            {assign var="current_loan_buyer" value=null}
            {if $loan_buyers}
                {foreach $loan_buyers as $lb}
                    {if $lb.loan_number == $order_data->balance->zaim_number}
                        {assign var="current_loan_buyer" value=$lb}
                    {/if}
                {/foreach}
            {/if}
            <div class="about" style="max-width: 512px;">
                <div class="status-box status-box--warning" style="display: flex; align-items: flex-start;">
                    <div class="status-box__icon" style="flex-shrink: 0; margin-right: 15px;">
                        <svg viewBox="0 0 24 24">
                            <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                        </svg>
                    </div>
                    <div class="status-box__content" style="flex: 1;">
                        <h3 class="status-box__title" style="margin-top: 0;">Передача договора в работу</h3>
                        <p class="status-box__text" style="margin-bottom: 0;">
                            {if $current_loan_buyer}
                                Уведомляем Вас, что задолженность по договору займа № {$order_data->balance->zaim_number} от {$current_loan_buyer.loan_date} г.
                                передана {$current_loan_buyer.loan_buy_date} по агентскому договору в пользу {$current_loan_buyer.loan_buyer_name} с целью возврата просроченной задолженности.
                                <br>
                                <br>
                            {else}
                                Уведомляем Вас, что задолженность по договору займа № {$order_data->balance->zaim_number} передана по агентскому договору в пользу {$order_data->balance->buyer} с целью возврата просроченной задолженности.
                                <br>
                                <br>
                            {/if}
                            Номер для связи:
                            <a href="tel:{$order_data->balance->buyer_phone}"
                               style="color: inherit; text-decoration: underline; font-weight: bold;">
                                {$order_data->balance->buyer_phone}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        {/if}
    {/if}

    {if $order_data->due_days >= 1 && $order_data->due_days <= 3 && $progress_bar_available}
        {include file='partials/progress_bar.tpl' orderData=$order_data}
    {/if}
    {if $order_data->balance->sale_info!='Договор продан' && $order_data->balance->zaim_number != 'Ошибка. Обратитесь в офис'}

        {include file='user_loan/discount_zero.tpl'}


        {include file='user_loan_balance.tpl' user_balance=$order_data->balance orderData=$order_data}

        {if $order_data->balance->prolongation_amount == 0 && $order_data->balance->loan_type == 'PDL' }
            <div class="prolongation-notification-main">
                <div class="prolongation-notification">
                    <p>
                        К сожалению Вам сейчас <span class="prolongation-not-available">недоступна пролонгация</span>,
                        но закрывая текущий займ Вам будет доступен новый!
                    </p>
                </div>
                <div class="prolongation-notification-details">
                    <div>
                        <p>Пролонгация договора займа, на основании ст. 308.3, 309, 310, 314 и 811 Гражданского кодекса
                            Российской Федерации, является <b>правом</b>, а не <b>обязанностью</b> микрофинансовой организации.</p>
                        <p>Право требования представляет собой возможность на удовлетворение законного интереса одного
                            лица (Кредитора) другим лицом (Заемщиком) путём выполнения конкретных действий, <b>в данном
                                случае закрытие всей суммы займа</b> с начисленными по день исполнения требования
                            процентами.</p>
                    </div>
                    <button class="button button-inverse btn-close-prolongation-notification">Понятно</button>
                </div>
            </div>
        {elseif ($order_data->balance->prolongation_amount > 0 && ($user->balance->prolongation_count <= 5 || $order_data->is_rcl)) || $order_data->balance->calc_percents > 0}
            {if $order_data->balance->loan_type != 'IL'}
            <div class="user_payment_form">
                {if !!$smarty.cookies.error}
                    <h5 style="color:#d22;font-size:1.1rem;padding:0.5rem 1rem;display:block">
                                    {$smarty.cookies.error}
                                </h5>
                {/if}
                {if !$order_data->is_rcl}
                {if $order_data->balance->last_prolongation == 1}
                    <span style="color:#d22;font-size:1.1rem;padding:0.5rem 1rem;display:block">
                                    У вас осталась последняя пролонгация
                                </span>
                {/if}
                {if $order_data->balance->last_prolongation == 2}
                    <span style="color:#d22;font-size:1.1rem;padding:0.5rem 1rem;display:block">
                                    Уважаемый клиент, Вы использовали лимит пролонгаций по данному займу.
                                    <br />
                                    Для формирования позитивной кредитной истории срочно погасите заем!
                                </span>
                {/if}
                {/if}
                {if $order_data->balance->last_prolongation != 2 && !$friend_restricted_mode}
                    <div class="action flex-block action_minimal_payment">

                            {if ($user_data['show_order_information'] or ((time() - strtotime($order_data->balance->zaim_date)) / 3600) > 24) &&  $prolongation_amount>0}
                                <button id="button_{$counter}" class="payment_button green button big get_prolongation_modal js-save-click"
                                        data-order_id="{$order_data->order->order_id}"
                                        data-user="{$user->id}"
                                        data-event="1"
                                        type="button"
                                        data-number="{$order_data->balance->zaim_number}">
                                    Минимальный платеж
                                    <input type="hidden" id="number_{$counter}" value="{$order_data->balance->zaim_number}">
                                    {if $order_data->id|@array_search:[299082, 278878, 246778, 153750]}
                                        <span class="user_amount_pay">{$order_data->balance->ostatok_percents}</span>
                                    {else}
                                        <span class="payment_button__amount">
                                            {$prolongation_amount}
                                        </span> &nbsp;руб
                                    {/if}
                                </button>
                                <button style="display: none!important;" class="js-prolongation-open-modal js-save-click" data-order_id="{$order_data->order->order_id}" data-user="{$user->id}" data-event="1" type="button" data-number="{$order_data->balance->zaim_number}"></button>
                                {if isset($order_data) && isset($order_data->newyear_promo) && $order_data->newyear_promo}
                                    <div style="margin-top: 20px;">
                                        <link rel="stylesheet" href="design/{$settings->theme|escape}/css/newyear_promo_banner.css?v=8" />
                                        {include file='partials/newyear_promo_banner.tpl' orderData=$order_data}
                                        <script src="design/{$settings->theme|escape}/js/newyear_promo_banner.js?v=8"></script>
                                    </div>
                                {/if}
                            {/if}
                    </div>

                {/if}

            </div>
            {/if}
        {/if}

    {/if}

    {if $order_data->balance->sum_with_grace}

    {/if}
    {if $order_data->balance->sale_info!='Договор продан' && $order_data->balance->zaim_number && $order_data->balance->zaim_number!='Ошибка' && $order_data->balance->zaim_number!='Нет открытых договоров'}


        {if $order_data->balance->loan_type == 'IL'}
            {if $settings->il_enabled}
                {include file='installment/payment_buttons.tpl'}
            {else}
                <div class="about">
                    <div>Ведутся Технические работы</div>
                </div>
            {/if}
        {else}
        <div class="parent-wrapper">
            <div class="hidden">
                <div id="autoconfirm_repeat_order_modal" class="white-popup">
                    <div class="modal-wrapper">
                        <a href="javascript:void(0);" onclick="$.magnificPopup.close();" class="close">&times;</a>
                        {include 'auto_confirm_repeat_order_asp.tpl' current_order_id=$order_data->order->order_id}
                    </div>
                </div>
            </div>
        {if $order_data->balance->last_prolongation != 2}
            {if $order_data->balance->prolongation_amount > 0 && ($user->balance->prolongation_count <= 5 || $order_data->is_rcl)}
                <div class="user_payment_form" style="margin-top:20px;">
                    <div class="action">
                        {*Данная кнопка показывает скрытую форму оплаты*}
                        <button class="payment_button button button-inverse btn-600 btn-fsize-14 btn-line-h-24 js-save-click"
                                data-user="{$user->id}"
                                data-order-id="{$order_data->order->order_id}"
                                data-event="2"
                                type="button"
                                {if $restricted_mode == 1}
                                    onclick="$('#user_pay_form_1_{$order_data->order->order_id}').submit()"
                                    {else}
                                    onclick="AutoConfirmRepeatApp.initAutoConfirmRepeatOrder('user_pay_form_1_{$order_data->order->order_id}')"
                                {/if}
                        >
                            Погасить заём полностью
                        </button>

                        {if $order_data->due_days >= 1 && $order_data->due_days <= 3 && $wheel_available && $order_data->wheel_available}
                            <button type="button" class="wheel-open promo-badge" style="padding-left: 25px; position: relative">
                                <img src="design/{$settings->theme|escape}/img/wheel/wheel.png" class="wheel-icon" alt="Играть!">
                                &nbsp;
                                Играть!
                            </button>
                        {/if}
                    </div>
                </div>

            {else}
                <form method="POST" action="user/payment" class="user_payment_form" id="user_pay_form_2_{$order_data->order->order_id}">
                    <div class="action">
                        <input type="hidden" name="payment_type" value="prolongation"/>
                        <input type="hidden" name="number" value="{$order_data->balance->zaim_number}" />
                        <input type="hidden" name="order_id" value="{$order_data->order->order_id}" />
                        {if isset($order_data->newyear_promo) && $order_data->newyear_promo && $order_data->newyear_promo->discount_activated && $order_data->newyear_promo->is_active}
                            <input type="hidden" name="newyear_promo" value="1"/>
                            <input type="hidden" name="newyear_discount" value="{$order_data->newyear_promo->discount_amount}"/>
                        {/if}

                        {log_fields button_name="full_1"}

                        {if $order_data->order->additional_service_repayment || $order_data->order->half_additional_service_repayment}
                            {add_hidden_input_if condition=$is_recurring_payment_so_enabled inputName="recurring_payment_so" value=1}
                        {/if}

                        {assign var="price" value=0}
                        {assign var="oracle_price" value=0}
                        {if !$user_data['whitelist_dop'] || !$settings->whitelist_dop}

                            {if $order_data->order->additional_service_repayment}
                                {assign var="price" value=$vita_med->price}
                            {elseif $order_data->order->half_additional_service_repayment}
                                {math equation="floor(price / 2)" price=$vita_med->price assign="price"}
                            {/if}

                            {if $order_data->order->additional_service_so_repayment}
                                {assign var="oracle_price" value=$star_oracle->price}
                            {elseif $order_data->order->half_additional_service_so_repayment}
                                {math equation="floor(oracle_price / 2)" oracle_price=$star_oracle->price assign="oracle_price"}
                            {/if}

                        {/if}

                        {if $has_penalty}
                            <input type="hidden" name="tv_medical_amount" value="{$price}"/>
                            <input type="hidden" name="tv_medical" value="1"/>
                            <input type="hidden" name="tv_medical_id" value="{$vita_med->id}"/>
                            {assign var="amount_value" value=$total_debt + $price + $order_data->balance->penalty}
                        {else}
                            {assign var="amount_value" value=$total_debt + $order_data->balance->penalty}
                        {/if}
                        


                        <input style="display:none" class="payment_amount" data-order_id="{$order_data->balance->zaim_number}" data-user_id="{$user->id}" type="text" name="amount"
                               value="{$amount_value}"
                               max="{$amount_value}" min="1" />
                        <button class="payment_button button button-inverse btn-600 btn-fsize-14 btn-line-h-24 js-save-click pay-full"
                                data-user="{$user->id}"
                                data-order-id="{$order_data->order->order_id}"
                                data-event="5"
                                type="button"
                                {if $restricted_mode == 1}
                                    onclick="$('#user_pay_form_2_{$order_data->order->order_id}').submit()"
                                {else}
                                    onclick="AutoConfirmRepeatApp.initAutoConfirmRepeatOrder('user_pay_form_2_{$order_data->order->order_id}')"
                                {/if}
                        >Погасить заём полностью</button>
                    </div>
                </form>

            {/if}
        {/if}

        {* Если данные по рефинансу есть, и при этом сама заявка не является рефинансом *}
        {if !empty($order_data->refinance) && !empty($order_data->refinance['show_refinance']) && !$order_data->is_refinance && !empty($can_see_refinance_button)}
            {include file='refinance.tpl' orderData=$order_data}
        {/if}

        {if $order_data->due_days >= 1 && $order_data->due_days <= 3 && $wheel_available && $order_data->wheel_available}
            <div class="wheel-wrap" style="display:none"></div>
        {/if}

        </div>

        {*Эта форма показывается по клику кнопке Погасить заём полностью выше с комментарием*}
        <div id="close_credit_form_{$order_data_index}"  style="margin-top:15px;{if $order_data->balance->last_prolongation != 2}display:none{/if}">
            {if $order_data->balance->last_prolongation != 2 && ($user->balance->prolongation_count <= 5 || $order_data->is_rcl)}
                <div style="max-width:500px;margin-bottom:10px;">
                    <p style="color:#080;margin-bottom:10px;">
                        При оплате минимальной суммы ваша кредитная история станет лучше, а кредитный лимит максимальным
                    </p>
                    {if ($user_data['show_order_information'] or ((time() - strtotime($order_data->balance->zaim_date)) / 3600) > 24)  && $prolongation_amount > 0}
                        <button class="payment_button green button big js-prolongation-open-modal js-save-click" data-user="{$user->id}" data-event="3" type="button" data-number="{$order_data->balance->zaim_number}">
                            Минимальный платеж
                            {if $order_data->id|@array_search:[299082, 278878, 246778, 153750]}
                                <span class="user_amount_pay">{$order_data->balance->ostatok_percents}</span>
                            {else}
                                <span class="payment_button__amount">
                                   {$prolongation_amount}
                                </span>
                            {/if} &nbsp;руб

                        </button>
                    {/if}

                </div>
            {/if}
            <form method="POST" action="user/payment" class="user_payment_form" id="user_pay_form_1_{$order_data->order->order_id}">
                <div class="action">
                    <input type="hidden" name="payment_type" value="full"/>
                    <input type="hidden" name="number" value="{$order_data->balance->zaim_number}"/>
                    <input type="hidden" name="order_id" value="{$order_data->order->order_id}"/>
                    {if isset($order_data->newyear_promo) && $order_data->newyear_promo && $order_data->newyear_promo->discount_activated && $order_data->newyear_promo->is_active}
                        <input type="hidden" name="newyear_promo" value="1"/>
                        <input type="hidden" name="newyear_discount" value="{$order_data->newyear_promo->discount_amount}"/>
                    {/if}

                    {log_fields button_name="full_2"}
                    
                    {if $order_data->order->additional_service_repayment || $order_data->order->half_additional_service_repayment}
                        {add_hidden_input_if condition=$is_recurring_payment_so_enabled inputName="recurring_payment_so" value=1}

                        {assign var="price" value=0}
                        {assign var="oracle_price" value=0}

                        {* Далее ваша логика с условиями *}
                        {if !$user_data['whitelist_dop'] || !$settings->whitelist_dop}
                            {if $order_data->order->additional_service_repayment}
                                {assign var="price" value=$vita_med->price}
                            {elseif $order_data->order->half_additional_service_repayment}
                                {math equation="floor(price / 2)" price=$vita_med->price assign="price"}
                            {/if}

                            {if $order_data->order->additional_service_so_repayment}
                                {assign var="oracle_price" value=$star_oracle->price}
                            {elseif $order_data->order->half_additional_service_so_repayment}
                                {math equation="floor(oracle_price / 2)" oracle_price=$star_oracle->price assign="oracle_price"}
                            {/if}
                        {/if}

                        {if $has_penalty}
                            <input type="hidden" name="tv_medical_amount" value="{$price}"/>
                            <input type="hidden" name="tv_medical" value="1"/>
                            <input type="hidden" name="tv_medical_id" value="{$vita_med->id}"/>
                            {assign var="amount_value" value=$total_debt + $price + $order_data->balance->penalty}
                        {else}
                            {assign var="amount_value" value=$total_debt + $order_data->balance->penalty}
                        {/if}
                    {else}
                        {assign var="amount_value" value=$total_debt + $order_data->balance->penalty}
                    {/if}

                    <input style="display:none" class="payment_amount"
                           data-order_id="{$order_data->balance->zaim_number}" data-user_id="{$user->id}" type="text"
                           name="amount"
                           value="{$amount_value}"
                           max="{$amount_value}" min="1"/>
                    <button data-order_id="{$order_data->balance->zaim_number}" class="payment_button button button-inverse btn-600 js-save-click btn-fsize-14 full_payment_button pay-full" data-user="{$user->id}"
                            data-order-id="{$order_data->order->order_id}"
                            data-event="5" type="submit">Погасить заём полностью
                    </button>
                </div>
            </form>
        </div>

        <div class="user_payment_form" {if $order_data->due_days >= 1 && $order_data->due_days <= 3 && $progress_bar_available} style="margin-top: 25px" {/if}>
            <div class="action">
                <button class="payment_button button button-inverse btn-600 btn-fsize-14 btn-line-h-24 js-save-click" data-user="{$user->id}" data-event="6" onclick="$('#other_summ_{$order_data_index}').fadeIn('fast');$(this).hide()" type="button">Оплатить другую сумму</button>
            </div>
        </div>

            {if !$friend_restricted_mode}
                {include
                file='friend_payment.tpl'
                order_data=$order_data
                overdue_days=$order_data->due_days
                }
            {/if}

            <form method="POST" action="user/payment" id="other_summ_{$order_data_index}"
                  class="user_payment_form user_payment_form_other" style="display:none">

                <input type="hidden" name="payment_type" value="partial"/>
                <input type="hidden" name="number" value="{$order_data->balance->zaim_number}"/>
                <input type="hidden" name="order_id" value="{$order_data->order->order_id}"/>

                {log_fields button_name="partial"}
                
                <div class="action">
                    {if $order_data->balance->prolongation_amount > 0}
                        <div style="max-width:500px;">
                            <p class="payment_other_sum_par" style="margin-bottom:0;">Внимание, после оплаты дата возврата займа не изменится!
                                <br/>Во избежание возникновения просрочки и ухудшения вашей кредитной истории,
                                пожалуйста, убедитесь в том, что вы успеете полностью погасить заём
                                до {$order_data->balance->payment_date|date}.
                                {if !$friend_restricted_mode}
                                <br/>Если вы хотите пролонгировать заём, воспользуйтесь кнопкой «Минимальный платеж»
                                {/if}
                            </p>
                        </div>
                    {/if}
                    <p class="payment_other_sum_par">Другая сумма</p>
                    <div class="payment_other_sum_actions_wrapper">
                        <div>
                            <p class="payment_other_sum_label">Пожалуйста, введите сумму <span>*</span></p>

                            {if $total_debt >= 1001}
                                {if $order_data->order->additional_service_partial_repayment || $order_data->order->half_additional_service_partial_repayment}
                                    <input type="hidden" name="tv_medical_amount" value="0"/>
                                    <input type="hidden" name="tv_medical" value="0"/>
                                    <input type="hidden" name="tv_medical_id" value="0"/>
                                {/if}

                                {if $order_data->order->additional_service_so_partial_repayment || $order_data->order->half_additional_service_so_partial_repayment}
                                    {add_hidden_input_if condition=$is_recurring_payment_so_enabled inputName="recurring_payment_so" value=1}
                                    <input type="hidden" name="star_oracle_amount" value="0"/>
                                    <input type="hidden" name="star_oracle" value="0"/>
                                    <input type="hidden" name="star_oracle_id" value="0"/>
                                {/if}
                            {/if}
                            <input class="payment_amount payment_amount_input" inputmode="numeric" data-order_id="{$order_data->balance->zaim_number}"
                                data-user_id="{$user->id}" type="text" name="common_amount_formated" id="common_amount_{$order_data_index}_formated" value=""/>
                            <input class="payment_amount payment_amount_input hidden" data-order_id="{$order_data->balance->zaim_number}"
                                data-user_id="{$user->id}" type="text" name="common_amount" id="common_amount_{$order_data_index}" value="" min="1" max="{$total_debt}"/>
                            <input class="hidden_amount" type="hidden" name="amount" id="hidden_amount_{$order_data_index}" value=""/>
                        </div>
                        <button class="payment_button button medium js-save-click" data-user="{$user->id}" data-event="7"
                                type="submit">Оплатить
                        </button>
                    </div>
                </div>
            </form>

            {if $payment_methods_btn}
                <div class="user_payment_form">
                    <div class="action">
                        <button class="payment-methods-open payment_button button button-inverse btn-600 btn-fsize-14 btn-line-h-24" type="button">
                            Возможные способы оплаты
                        </button>
                    </div>
                </div>
                {include file='modals/payment_methods.tpl'}
            {/if}

            {if $canShowRefererBanner && !empty($referer_url)}
                {include file='partials/referer_banner.tpl' referer_url=$referer_url}
            {/if}
            <br>
            {if !$friend_restricted_mode && $order_data->clear_due_days|default:0 >= $findzen_overdue_days}
                {include file='partials/findzen_banner.tpl' findzen_url=$findzen_url}
            {/if}

        {/if}
    {/if}
{/if}

{if ($divide_pre_order_is_new && $order_data_index == 0) || ($order_data_index == 1 && in_array($divide_order->data->status, ['APPROVED', 'ISSUED']) && !in_array($order_data->order->status_1c, ['5.Выдан', '6.Закрыт']))}{*если новый разделенный займ одобрен*}
    {include 'divide_order.tpl'}
{/if}

{assign var="tv_medical_tariffs" value=$tv_medical_tariffs|json_encode}
{assign var="star_oracle_tariffs" value=$star_oracle_tariffs|json_encode}

<script type="text/javascript">
    {if isset($slider_interact) && $slider_interact}
        window.slider_interact = true;
    {/if}

    {if isset($click_info) && $click_info}
        window.click_info = true;
    {/if}

    document.addEventListener('DOMContentLoaded', function() {
        const formattedInput = $('input[name="common_amount_formated"]');
        formattedInput.on('input', (e) => {
            const rawInput = $('input[name="common_amount"]');
            // Remove all non-digit characters from input
            let valWithoutSpaces;
            if(e.target.value.split('.')[1]?.length === 1){
                valWithoutSpaces = e.target.value.split('.')[0];
            }
            else valWithoutSpaces = e.target.value.replace(/[^0-9]/g, '');

            if (valWithoutSpaces.startsWith('0')) {
                valWithoutSpaces = valWithoutSpaces.substring(1);
            }

            if(valWithoutSpaces > {$total_debt} ) {
                valWithoutSpaces = {$total_debt};
                valWithoutSpaces = valWithoutSpaces.toString()
            }
            // Update raw input with cleaned value
            rawInput.val(valWithoutSpaces.replace(/[^0-9.]/g, ''));
            
            // Apply formatting (spaces every 3 digits)
            if (valWithoutSpaces.length > 3) {
                let kopeck;
                const splittedByDot = valWithoutSpaces.split('.')
                if(splittedByDot.length > 1) {
                    kopeck = splittedByDot[1];
                    valWithoutSpaces = splittedByDot[0]
                }
                const firstGroupLength = valWithoutSpaces.length % 3;
                const formattedValue = valWithoutSpaces.split('').reduce((acc, curr, currInd) => {
                    return (currInd - firstGroupLength) % 3 === 0 && currInd !== 0 ? acc + " " + curr : acc + curr;
                }, '');
                if(splittedByDot.length > 1){
                    formattedInput.val(formattedValue + '.' + kopeck);
                }
                else {
                    formattedInput.val(formattedValue);
                }
            } else {
                formattedInput.val(valWithoutSpaces);
            }
            updateHiddenAmount();
        })

        const orderID= "other_summ_"+ "{$order_data_index|escape:'javascript'}";

        if (!orderID) {
            return;
        }

        const paymentForm= document.getElementById(orderID);
        if (!paymentForm) {
            return;
        }
        const orderDataIndex = paymentForm.id.replace('other_summ_', '');

        let amountInput = document.getElementById('common_amount_' + orderDataIndex);
        const amountInputIl = document.querySelector('input[name="amount"].js-il-chdp-amount');
        const hiddenAmountInput = document.getElementById('hidden_amount_' + orderDataIndex);
        const tvMedicalPriceInput = paymentForm.querySelector('input[name="tv_medical_amount"]');
        const tvMedicalValue = paymentForm.querySelector('input[name="tv_medical"]');
        const tvMedicalIdInput = paymentForm.querySelector('input[name="tv_medical_id"]');

        const starOraclePriceInput = paymentForm.querySelector('input[name="star_oracle_amount"]');
        const starOracleIdInput = paymentForm.querySelector('input[name="star_oracle_id"]');
        const starOracleValue = paymentForm.querySelector('input[name="star_oracle"]');
        const starOracleRecurringPaymentValue = paymentForm.querySelector('input[name="recurring_payment_so"]');

        if(!amountInput && amountInputIl) {
            amountInput = amountInputIl;
        }

        if (!amountInput || !hiddenAmountInput) {
            return;
        }

        const totalDebt = parseFloat("{$total_debt|escape:'javascript'}");
        const additionalServicePartialRepayment = "{$order_data->order->additional_service_partial_repayment|escape:'javascript'}";
        const additionalServiceHalfPartialRepayment = "{$order_data->order->half_additional_service_partial_repayment|escape:'javascript'}"

        const additionalServiceSOPartialRepayment = "{$order_data->order->additional_service_so_partial_repayment|escape:'javascript'}"
        const halfAdditionalServiceSOPartialRepayment = "{$order_data->order->half_additional_service_so_partial_repayment|escape:'javascript'}"

      const userDataWhitelistDOP = +"{$user_data['whitelist_dop']|escape:'javascript'}"
      const settingsWhitelistDOP = +"{$settings->whitelist_dop|escape:'javascript'}"
      
        const srkvTvMedBlocked = {if $srkv_tv_med_blocked}true{else}false{/if};

        function calculateTvMedicalPrice(enteredAmount) {
            if (srkvTvMedBlocked) {
                return { price: 0, id: 0, value: 0 };
            }

            const tariffs = JSON.parse('{$tv_medical_tariffs|escape:"javascript"}').map(tariff => {
                return {
                    min: +tariff.from_amount,
                    max: +tariff.to_amount,
                    price: +tariff.price,
                    id: +tariff.id,
                    value: +tariff.is_new
                };
            });

            const foundTariff = tariffs.find(tariff => enteredAmount >= tariff.min && enteredAmount <= tariff.max);
            return foundTariff ?? { price: 0, id: 0, value: 0 };
        }

        function calculateStarOraclePrice (enteredAmount) {
            const tariffs = JSON.parse('{$star_oracle_tariffs|escape:"javascript"}').map(tariff => {
                return {
                    min: +tariff.from_amount,
                    max: +tariff.to_amount,
                    price: +tariff.price,
                    id: +tariff.id,
                    value: +tariff.is_new
                }
            })

            const foundTariff = tariffs.find(tariff => enteredAmount >= tariff.min && enteredAmount <= tariff.max)
            return foundTariff ?? { price: 0, id: 0, value: 0 };
        }

        function setServiceValues(priceInput, idInput, valueInput, price, id, value) {
            if (priceInput) priceInput.value = price;
            if (idInput) idInput.value = id;
            if (valueInput) valueInput.value = value;
        }

        function clearServiceValues(priceInput, idInput, valueInput) {
            setServiceValues(priceInput, idInput, valueInput, 0, 0, 0);
        }

        function updateHiddenAmount() {
            let enteredAmount = parseFloat(amountInput.value);

            let oraclePrice = 0
            let tvMedPrice = 0

            if (enteredAmount > totalDebt) {
                enteredAmount = totalDebt;
                amountInput.value = totalDebt;
            }

            if (totalDebt >= 1001) {

              if ((additionalServicePartialRepayment == 1 || additionalServiceHalfPartialRepayment == 1) && (!userDataWhitelistDOP || !settingsWhitelistDOP)) {
                    const { price: originalPrice, id, value } = calculateTvMedicalPrice(enteredAmount)
                    const price = additionalServicePartialRepayment == 1 ? originalPrice : originalPrice / 2
                    tvMedPrice = price

                    setServiceValues(tvMedicalPriceInput, tvMedicalIdInput, tvMedicalValue, price, id, value);
                }

              

                // Если рекуррентный платеж включен то ЗО списывается отдельным платежем
                if(starOracleRecurringPaymentValue && !amountInputIl) {
                    clearServiceValues(starOraclePriceInput, starOracleIdInput, starOracleValue);
                    oraclePrice = 0;
                }

                hiddenAmountInput.value = enteredAmount + tvMedPrice

            } else {
                hiddenAmountInput.value = enteredAmount
                clearServiceValues(tvMedicalPriceInput, tvMedicalIdInput, tvMedicalValue);
                clearServiceValues(starOraclePriceInput, starOracleIdInput, starOracleValue);
            }
        }

        if(amountInputIl) {
            updateHiddenAmount();
        }

        amountInput.addEventListener('input', function() {
            let value = this.value;
            value = value.replace(/[^0-9]/g, '');

            if (value.startsWith('0')) {
                value = value.substring(1);
            }

            if (parseFloat(value) > totalDebt) {
                value = totalDebt.toString();
            }

            this.value = value;
            updateHiddenAmount();

            if (this.value) {
                amountInput.style.border = '';
                amountInput.style.borderRadius = '';
            }
        });

        paymentForm.addEventListener('submit', function(event) {
            if (!amountInput.value) {
                event.preventDefault();
                amountInput.style.border = '2px solid #ff0500';
                amountInput.style.borderRadius = '5px';
            } else {
                paymentForm.submit();
            }
        });
    });
</script>
