<div id="accept_block_{$user_order['id']}" data-order="{$user_order['id']}" class="{if $user_order['utm_source'] == 'cross_order'}cross_order_accept{/if}">

    <p class="accept_message">
        {if empty($cards) && empty($sbp_accounts)}
            {include file='no_cards.tpl' has_approved_order=true}
        {else}
            {if $notOverdueLoan}
                <h2>
                    Отлично! Вы закрыли займ без просрочек и можете оформить новый на льготных условиях.
                </h2>
            {/if}

            {if $user_order['utm_source'] == 'cross_order'}
                {if !$isAutoAcceptCrossOrders}
                Вам дополнительно одобрено {$user_order['approve_max_amount']} руб <br />
                {/if}
                <button
                        type="button" class="
                    button big {if $config->snow}snow-relative primary{else}orange{/if}
                    {if $user_order['noactive'] && !$isAutoAcceptCrossOrders}noactive js-noactive{/if}
                    " id="open_accept_modal"
                    style="{if $isAutoAcceptCrossOrders}display:none!important;{/if}"
                    >
                    {if $config->snow}
                        <img class="snow-man" src="design/orange_theme/img/holidays/snow/snow_man.png?v=2" alt="Получить деньги"/>
                    {/if}
                    Получить ещё {$user_order['approve_max_amount']} руб
                </button>

            {else}
                Поздравляем! По вашей заявке одобрено <span id="approve_max_amount">
                {if $divide_pre_order}
                    {$divide_pre_order->amount + $user_order['amount']}
                {else}
                    {if $isAutoAcceptCrossOrders}
                        {$totalApproveAmount}
                    {else}
                        {$user_order['display_amount']|default:$user_order['approve_max_amount']}
                    {/if}
                {/if}
                </span> руб.
                {if $autoapprove_other_org}
                    на карту<br><span style="color: #FF0000">{$last_order_card->pan}</span>
                {/if}
                <br />
                {if 0 && $user_discount}
                Вы можете принять решение до {$user_discount->end_date|date}.
                {else}
                Вы можете принять решение до {$user_order['approved_period']}.
                {/if}
                <br />
                {if !$divide_pre_order}
                    <button type="button"
                            {if $can_add_sbp_account && empty($b2p_sbp_banks) && !$last_order_data['bank_id_for_sbp_issuance']} onclick="generateAndOpenSbpLink(event)"{/if}
                            class="get_money_btn button big {if $config->snow}snow-relative primary{else}green{/if}"
                        id="{if $autoapprove_card_reassign}autoapprove_card_reassign{elseif $autoapprove_wrong_card}autoapprove_card_modal_btn{else}open_accept_modal{/if}">
                        {if $config->snow}
                            <img class="snow-man" src="design/orange_theme/img/holidays/snow/snow_man.png?v=2" alt="Получить деньги"/>
                        {/if}
                        Получить займ
                    </button>
                    {if $can_add_sbp_account && empty($b2p_sbp_banks)}
                        <p style="margin: 10px 0; font-size: 12px; color: #333;">
                            Убедитесь, что привязанный номер телефона<br>
                            <b>+{$user->phone_mobile|substr:0:1} ({$user->phone_mobile|substr:1:3}) {$user->phone_mobile|substr:4:3}-{$user->phone_mobile|substr:7:2}-{$user->phone_mobile|substr:9:2}</b><br>
                            совпадает с номером телефона СБП в выбранном банке!
                        </p>
                    {/if}
                {else}
                    {include 'divide_order.tpl'}
                {/if}
            {/if}

            {if !$user_order['noactive']}
                {if ($user_order['approve_max_amount'] > $user_order['user_amount']) && ($user_order['approve_max_amount'] != 1000 && $user_order['have_close_credits'] == 1 || ($user_order['approve_max_amount'] > $user->first_loan_amount && !$divide_pre_order))}
                    {if $user_order['max_period'] > 0 && $user_order['max_amount'] > 30000}
                        {include file='installment/edit_amount.tpl'}
                    {/if}

                    {capture_array key="footer_page_scripts"}
                        <script>
                            $('#open_accept_modal, .get_money_btn').click(function(){
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
                    {/capture_array}
                {/if}
            {/if}
        {/if}
    </p>

    {if !$autoapprove_card_reassign && !$autoapprove_wrong_card}
    <div id="accept_credit" class="accept_credit" style="display:none">
        {if !$settings->enable_loan_nk && !$user_order['have_close_credits']}
        <p class="text-red">
            Произошла техническая ошибка.<br />Попробуйте повторить через час.
        </p>
        {else}
        <form id="accept_credit_form" class="accept_credit_form" onsubmit="ym(45594498,'reachGoal','click_cash'); return true;">
    
            {foreach $cards as $card}
                {if $card->id == $user_order['card_id']}
                <input type="hidden" name="rebill_id" value="{$card->rebill_id}" />
                {/if}
            {/foreach}
    
            <input type="hidden" name="order_id" value="{$user_order['id']}" />
            <input type="hidden" name="card_id" value="{$user_order['card_id']}" />
            <input type="hidden" name="card_type" value="{$user_order['card_type']}" />
            <input type="hidden" name="bank_id" id="bank_id" value="{$last_order_data['bank_id_for_sbp_issuance']}"/>
            <input type="hidden" name="uid" value="{$user->uid}" />
            <input type="hidden" name="number" value="{$user_order['1c_id']}" />
            <input type="hidden" name="insurer" value="{$insurer}" />
            <input type="hidden" name="insure" value="{$insure}" />
            <input type="hidden" name="new_nk_flow_path" id="new_nk_flow_path" value="0" />
            <input type="hidden" name="service_recurent" value="1" />
            <input type="hidden" value="1" name="agree_claim_value" id="agree_claim_value" />
{*            <input type="hidden" name="sms_code" id="sms_code" />*}

            {if $applied_promocode->disable_additional_services || (!empty($last_order_data) && isset($last_order_data['disable_additional_service_on_issue']) && $last_order_data['disable_additional_service_on_issue'] == 1)}
                <input type="hidden" value="0" name="is_user_credit_doctor" id="credit_doctor_hidden{$user_order['id']}"/>
            {else}
                <input type="hidden" value="{if $showExtraService['financial_doctor']['enable']}1{else}0{/if}" name="is_user_credit_doctor" id="credit_doctor_hidden{$user_order['id']}"/>
            {/if}

            <input type="hidden" value="{if $showExtraService['star_oracle']['enable']}1{else}0{/if}" name="is_star_oracle" id="star_oracle_hidden{$user_order['id']}"/>

            <h2>
                К получению
                <span id="amountToCard">
                    {$user_order['display_amount']|default:$user_order['amount']}
                </span> руб.
            </h2>
            <p>
                Подписать с помощью смс кода
                <br />
            </p>
    
            <div class="accept_credit_actions">
                <div>
                    <input type="text" inputmode="numeric" id="sms_code" name="sms_code" class="sms_code accept_credit_code" placeholder="Код из СМС" />
                    <div class="sms-code-error"></div>
                    <a href="javascript:void(0);" id="repeat_sms" class="repeat_sms" data-phone="{$user->phone_mobile}">отправить код еще раз</a>
                </div>
                <div>
                    <button id="telegram_banner_button_click" class="get_money_btn button medium {if $config->snow}snow-relative primary{else}green{/if}" type="submit">
                        {if $config->snow}
                            <img class="snow-man" src="design/orange_theme/img/holidays/snow/snow_man.png?v=2" alt="Получить деньги"/>
                        {/if}
                        Отправить
                    </button>
                </div>
            </div>
    
            <div id="not_checked_info" style="display:none">
                <strong style="color:#f11">Вы должны согласиться с договором и нажать "Получить деньги"</strong>
            </div>
            {if !$isSafetyFlow && $isAllowedTestLeadgid}
                <div id="backdrop_modal" style="display: none;"></div>
                <div id="open_modal">
                    {include 'auto_confirm_repeat_order_asp_accept_credit.tpl' user_order=$user_order}
                </div>
                <div id="main-checkbox-container">
                    <label>
                        <div class="checkbox">
                            <input type="checkbox" value="0" id="agree_all" class="js-agree-all-claim-value">
                            <span style="margin:0;width:100%;height:100%;top:0;left:0;"></span>
                        </div>
                        <p><a href="javascript:void(0)" id="show_docs_modal">Я согласен со следующим</a></p>
                    </label>
                </div>
            {else}
                {include file="accept_credit/docs_list_main.tpl"}
            {/if}
        </form>
        {/if}
    </div>
    {/if}


    <div style="display: none">
        <div id="accepted_first_order_divide" class="wrapper_border-green white-popup-modal wrapper_border-green mfp-hide">
            <div>
                <h4>
                    Не забудьте вернуться завтра за второй частью займа!
                </h4>
                <button class="green button" onclick="$.magnificPopup.close()">Хорошо</button>
            </div>
        </div>
    </div>

    <div id="autoapprove_card_modal" class="modal" style="display: none">
        <div class="modal-content autoapprove_card_modal">
            <div>
                <p>Для получения одобренного займа необходимо привязать карту <span style="color: #FF0000">{$last_order_card->pan}</span></p>
            </div>
            <div class="autoapprove_card_modal__buttons">
                <button class="button big green" id="js-assign-old-card-btn">Привязать</button>
                <button class="button big" id="js-other-card-btn">Хочу использовать другую карту</button>
            </div>
            <div>
                <p style="color: #FF0000">При использовании другой карты одобренная заявка аннулируется. Для получения займа необходимо будет подать новую заявку</p>
            </div>
        </div>
    </div>
</div>

{if !empty($b2p_sbp_banks)}
    {include file = 'modals/modal_choose_bank.tpl' show_close_button=false show_text_to_choose_sbp=false order_id_for_sbp=$order_for_choosing_card['id']}
{/if}

<script>
    var isOrganic = "{$isOrganic|escape:'javascript'}"
    // $('#telegram_banner_button_click').click(function() {
    //     window.open($('.telegram_banner a').attr('href'), '_blank')
    // });
</script>


<script type="text/javascript">

  $(function () {
        var _auto = {if $isAutoAcceptCrossOrders && $user_order['utm_source'] != 'cross_order'}1{else}0{/if};
        var $block =  $('#accept_block_{$user_order["id"]}');
        var AcceptCreditApp = new AcceptCredit($block, _auto);
        {if $user_order['utm_source'] != 'cross_order' && $asp_code_already_sent}
            AcceptCreditApp.open_accept_modal()
            $('html, body').animate({ scrollTop: $block.offset().top }, 'slow');
        {/if}
        {if $user_order['utm_source'] == 'crm_auto_approve'}
        $('#test_open_accept_green').click();
        {/if}
    });

</script>
