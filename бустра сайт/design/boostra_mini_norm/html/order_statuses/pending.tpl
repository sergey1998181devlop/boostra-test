
{if $view_fake_first_order}
    <div class="status-box status-box--error">
        <div class="status-box__icon">
            <svg viewBox="0 0 24 24">
                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
            </svg>
        </div>
        <div class="status-box__content">
            <h3 class="status-box__title">К сожалению, вам отказано</h3>
            <p class="status-box__text">Попробуйте отправить заявку повторно, так как возможны технические сбои.</p>
        </div>
    </div>

    <form method="POST" id="repeat_loan_form">
        <input type="hidden" name="service_recurent" value="1" />
        <input type="hidden" name="service_sms" value="1" />
        <input type="hidden" name="service_insurance" value="1" />
        <input type="hidden" name="service_reason" value="0" />
        {if ($user_return_credit_doctor)}
            <input type="hidden" name="service_doctor" value="0" />
        {else}
            <input type="hidden" name="service_doctor" value="1" />
        {/if}
        <input type="hidden" name="service_recurent" value="1" />
        <input type="hidden" value="1" name="repeat_first_loan" />
        <input type="hidden" value="{$order.id}" name="order_id" />

        <label class="js-accept-block medium left {if $error=='empty_accept'}error{/if}" >
            <div class="checkbox">
                <input class="js-input-accept" type="checkbox" value="1" id="repeat_loan_terms" name="accept" {if $accept}checked="true"{/if} />
                <span></span>
            </div>
            Я ознакомлен и согласен <a href="javascript:void(0);" id="accept_link">со следующим</a>
            <span class="error">Необходимо согласиться с условиями</span>
        </label>

        <p>
            <button type="submit" id="repeat_loan_submit" class="button big">
                Отправить повторно
            </button>
        </p>
    </form>
{else}
    {if $order.utm_source == 'crm_auto_approve' && $user->auto_approve_order->status != 'SUCCESS'}
        <div class="status-box status-box--info">
            <div class="status-box__icon">
                <svg viewBox="0 0 24 24">
                    <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.4 0-8-3.6-8-8s3.6-8 8-8 8 3.6 8 8-3.6 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z"/>
                </svg>
            </div>
            <div class="status-box__content">
                <h3 class="status-box__title">Определяем возможность выдачи</h3>
                <p class="status-box__text">Пожалуйста, подождите...</p>
            </div>
        </div>
    {else}
        {if $order.is_new_card_linked}
            <div class="status-box status-box--info">
                <div class="status-box__icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.4 0-8-3.6-8-8s3.6-8 8-8 8 3.6 8 8-3.6 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z"/>
                    </svg>
                </div>
                <div class="status-box__content">
                    <h3 class="status-box__title">Заявка № {$order.id} от {$order.date|date} на рассмотрении</h3>
                    <p class="status-box__text">Ваша заявка отправлена на повторное рассмотрение. Ожидайте!</p>
                </div>
            </div>
        {else}
            <div class="status-box status-box--info">
                <div class="status-box__icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.4 0-8-3.6-8-8s3.6-8 8-8 8 3.6 8 8-3.6 8-8 8zm.5-13H11v6l5.2 3.2.8-1.3-4.5-2.7V7z"/>
                    </svg>
                </div>
                <div class="status-box__content">
                    <h3 class="status-box__title">Ваша заявка № {$order.id} от {$order.date|date} на рассмотрении</h3>

                </div>
            </div>
            <div class="timer-wrapper">
                <p class="status-box__text">Деньги у вас через:</p>
                <div id="countdown-container">
                    <div class="countdown-item" id="minutes-item">
                        <span id="minutes">02</span>
                        <p>минут</p>
                    </div>
                    <div class="countdown-item" id="separator-item">
                        <span id="separator">:</span>
                        <p></p>
                    </div>
                    <div class="countdown-item" id="seconds-item">
                        <span id="seconds">59</span>
                        <p>секунд</p>
                    </div>
                </div>
            </div>
        {/if}

        {if $has_vk}
            {include "vk_group_bot_widget.tpl"}
        {else}
            {include "loan_game.tpl"}
        {/if}
    {/if}
{/if}
