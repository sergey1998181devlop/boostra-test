<div class="clearfix about" id="user_get_zaim_form" {if !$user->not_rating_maratorium_valid && (($reason_block && $reason_block !== 999) || $repeat_loan_block)}style="display: none"{/if}>

    {if $success_add_data}
        <p style="color:#6F7985;width:100%">
            Теперь Ваша анкета содержит все необходимые данные <br />и Вы можете подать заявку
        </p>
    {/if}

    {if !$cards}
        <div style="color:red;font-size:1.5rem;">
            Для получения займа привяжите карту
        </div>

    {else}

        {if $need_add_fields|count > 0}
            <div {if $user->fake_order_error > 0 || $repeat_approve_message}style="display:none"{/if}>
                <a href="add_data" class="button medium ">Заявка на займ</a>
            </div>
        {else}
            <div {if $user->fake_order_error > 0 || $repeat_approve_message}style="display:none"{/if}>
                <a href="#" class="button medium  get_new_loan">Заявка на займ</a>
            </div>
        {/if}

        <div class="loan_form" {if $user->fake_order_error > 0 || $repeat_approve_message}style="display:block"{/if}>
            <form id="repeat_loan_form" action="{$smarty.server.REQUEST_URI}" method="POST">

                {if $user->fake_order_error > 0}
                    <p style="color:#d22">
                        К сожалению Вам отказано.
                        <br />Попробуйте отправить заявку повторно,
                        <br />так как возможны технические сбои.</p>
                {/if}

                {if $repeat_approve_message}
                    <p style="color:#6F7985">
                        Вам предварительно одобрен займ на тех же условиях
                    </p>
                {/if}

                <input type="hidden" name="service_recurent" value="1" />
                <input type="hidden" name="service_sms" value="0" />
                <input type="hidden" name="service_insurance" value="1" />
                <input type="hidden" name="service_reason" value="0" />
                {if ($user_return_credit_doctor)}
                    <input type="hidden" name="service_doctor" value="0" />
                {else}
                    <input type="hidden" name="service_doctor" value="1" />
                {/if}

                <input type="checkbox" id="service_insurance_check" value="1" checked="true" style="display:none" />

                <input type="hidden" name="juicescore_session_id" id="juicescore_session_id" value="" />
                <input type="hidden" name="juicescore_useragent" id="juicescore_useragent" value="" />
                <input type="hidden" name="finkarta_fp" id="finkarta_fp" value="" />
                <input type="hidden" name="local_time" id="local_time" value="" />

                <div id="calculator">
                    <input type="hidden" id="percent" value="{if $user_discount}{$user_discount->percent/100}{else}0.01{/if}" />
                    <input type="hidden" id="max_period" value="{if $user_discount}{$user_discount->max_period}{else}6{/if}" />
                    <input type="hidden" id="have_close_credits" value="{if $user->loan_history|count > 0}1{else}0{/if}" />
                    <div class="slider-box">
                        <div class="money">
                            <input type="text" id="money-range" name="amount" value="{if $smarty.session.fake_order_amount}{$smarty.session.fake_order_amount}{else}30000{/if}" />
                        </div>
                        <div class="period">
                            <input type="text" id="time-range" name="period" value="{if $smarty.session.fake_order_period}{$smarty.session.fake_order_period}{else}16{/if}" />
                        </div>
                    </div>

                    {if $cards}
                        <label>
                            <span class="floating-label">Получить на карту:</span>

                            <div class="split">

                                <input type="hidden" name="b2p" value="{$use_b2p}" />
                                <ul>
                                    {foreach $cards as $card}
                                        <li>
                                            <label>
                                                <div class="radio">
                                                    <input type="radio" name="card" value="{$card->id}" {if $card@first}checked="true"{/if} />
                                                    <span></span>
                                                </div>
                                                {$card->pan}
                                            </label>
                                        </li>
                                    {/foreach}
                                </ul>

                            </div>
                        </label>
                    {/if}

                    <div class="result">К возврату <span class="total"></span> руб. до <span class="date"></span></div>

                    <br />

                    <label class="js-accept-block medium left {if $error=='empty_accept'}error{/if}" >
                        <div class="checkbox">
                            <input class="js-input-accept" type="checkbox" value="1" id="repeat_loan_terms" name="accept" {if $accept}checked="true"{/if} />
                            <span></span>
                        </div>
                        Я ознакомлен и согласен <a href="javascript:void(0);" id="accept_link">со следующим</a>
                        <span class="error">Необходимо согласиться с условиями</span>
                    </label>

                    <div class="clearfix"></div>
                    {if $need_add_fields|count > 0}
                        <a href="add_data" class="button big">
                            {if $user->fake_order_error > 0}
                                Отправить повторно
                            {else}
                                Получить займ.
                            {/if}
                        </a>
                    {else}
                        <button type="submit" id="repeat_loan_submit" class="button big">
                            {if $user->fake_order_error > 0}
                                Отправить повторно
                            {else}
                                Получить займ.
                            {/if}
                        </button>
                    {/if}
                </div>
            </form>
        </div>
    {/if}

</div>