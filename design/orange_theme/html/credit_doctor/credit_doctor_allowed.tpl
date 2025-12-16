{if $credit_doctor_allowed}
<div class="credit-doctor">
    <div class="credit-doctor-allowed">
        <div class="credit-doctor-title">
            Но есть отличная новость! Вам одобрен целевой заём на программу "Кредитный доктор"!
        </div>
        <div class="credit-doctor-description">
            <div class="credit-doctor-description-text">
                Кредитный доктор - это онлайн-сервис, который помогает нашим клиентам по двум направлениям.
            </div>
            <div class="credit-doctor-description-block-wrapper">
                <div class="credit-doctor-description-block">
                    <img src="design/{$settings->theme|escape}/img/svg/man-relaxing.svg" alt="man-relaxing">
                    <div>
                        <strong>Во-первых,</strong> с помощью внешнего финансового контроля снимает общую долговую нагрузку и минимизирует негатив от взаимодействия с различными отделами взыскания, вас больше не будут тревожить коллекторы.
                    </div>
                </div>
                <div class="credit-doctor-description-block">
                    <img src="design/{$settings->theme|escape}/img/svg/man-reading.svg" alt="man-reading">
                    <div>
                        <strong>Во-вторых,</strong> сервис даёт конкретные знания и навыки, как в будущем больше не оказаться в финансовой зависимости.
                    </div>
                </div>
                <div class="credit-doctor-description-block">
                    <img src="design/{$settings->theme|escape}/img/card-ok.svg" alt="man-reading">
                    <div>
                        <strong>В-третьих,</strong> при успешном прохождении программы «Кредитный доктор» Вы гарантировано получите следующий заём в компании «Бустра»
                    </div>
                </div>
            </div>
        </div>
        <div>
            Стоимость услуги - 9 000 руб. за 3 месяца обслуживания, оплата в рассрочку без переплаты по 3 000 руб. в месяц, это минимальный срок, необходимый для успешного снижения финансовой нагрузки. С четвёртого месяца оплата происходит на условиях ежемесячной подписки по 3 000 руб. в месяц с возможностью добровольно отменить подписку.
        </div>
        <div class="credit-doctor-form">
            <form action="/user" method="post">
                <input type="hidden" name="local_time" id="local_time" value="" />
                <input type="hidden" name="amount" value="9000">
                <input type="hidden" name="credit_doctor_form_submitted" value="1">
                {if $cards}
                    <label>
                        <div class="split">
                            {if $settings->b2p_enabled || $user->use_b2p}
                                <input type="hidden" name="b2p" value="1" />
                                <ul>
                                    {foreach $cards as $card}
                                        <li>
                                            <label>
                                                <div class="radio">
                                                    <input type="radio" name="card" value="{$card->id}" {if $card@first}checked="checked"{/if} />
                                                    <span></span>
                                                </div>
                                                {$card->pan}
                                            </label>
                                        </li>
                                    {/foreach}
                                </ul>
                            {else}
                                <input type="hidden" name="b2p" value="0" />
                                <ul>
                                    {foreach $cards as $card}
                                        {if $card->Status != 'D'}
                                            <li class="">
                                                <label>
                                                    <div class="radio">
                                                        <input type="radio" name="card" value="{$card->id}" {if $card@first}checked="checked"{/if} />
                                                        <span></span>
                                                    </div>
                                                    {$card->pan}
                                                </label>
                                            </li>
                                        {/if}
                                    {/foreach}
                                </ul>
                            {/if}
                        </div>
                    </label>
                {else}
                    Необходимо привязать карту.
                {/if}
                <button type="submit" name="credit_doctor_form_submitted" class="button medium">Оформить</button>
            </form>
        </div>
    </div>
</div>
<script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
<script src="design/{$settings->theme|escape}/js/credit_doctor.app.js?v=1.02" type="text/javascript"></script>
{/if}