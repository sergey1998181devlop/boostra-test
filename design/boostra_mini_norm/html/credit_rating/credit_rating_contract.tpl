<div>
    <div class="credit-rating-notice">
        <div>
            Введя персональный код, направленный мне в СМС сообщении, я соглашаюсь с оплатой
            услуги предоставления моего кредитного рейтинга в размере 399 рублей, предоставляемой
            в соответствии с
            <a href="https://www.boostra.ru/files/docs/usloviya-okazaniya-mkk-ooo-bustra-dopolnitelnoi-platnoi-uslugi-predostavlenie-kreditnogo-reitinga.pdf" target="_blank">
                "Условиями предоставления платной услуги"
            </a>
            и
            <a href="/user?action=download_credit_rating_contract" target="_blank">
                "Заявлением на услугу"
            </a>
        </div>
    </div>
    <div class="credit-rating-content">
        <div class="credit-rating-sms-block">
            <input name="use_b2p" value="{$use_b2p}" type="hidden" />
                    <div class="credit-card-pay-wrapper">
                        <h4>Выберите карту для оплаты услуг</h4>
                        <div class="credit-card-list">
                            {if $cards}
                                <label>
                                    <div class="split">
                                        <ul>
                                            {foreach $cards as $card}
                                                <li>
                                                    <label>
                                                        <div class="radio">
                                                            <input type="radio" name="card_pay_id"
                                                                   value="{$card->id}"
                                                                    {if ($card->default) || (!$has_default_card && $card@first)}
                                                                        checked="checked"
                                                                    {/if}
                                                            />
                                                            <span></span>
                                                        </div>
                                                        {$card->pan}
                                                    </label>
                                                </li>
                                            {/foreach}
                                            <li class="">
                                                <label>
                                                    <div class="radio">
                                                        <input type="radio" name="card_pay_id" value="" />
                                                        <span></span>
                                                    </div>
                                                    Оплатить новой картой
                                                </label>
                                            </li>
                                        </ul>
                                    </div>
                                </label>
                            {else}
                                Необходимо привязать карту.
                            {/if}
                        </div>
                    </div>

            <div class="user-cr-sms-block">
                <form action="/user&action=credit_rating_sign">
                    Код из СМС <input type="text" name="credit_rating_sms">
                    <div class="button medium credit-rating-check-sms disabled">Отправить</div>
                    <div class="sms-code-error">Код не совпадает</div>
                </form>
                <div class="button medium credit-rating-send-sms">Получить код</div>
            </div>
        </div>
        <div class="credit-rating-error"></div>
        <div class="credit-rating-cards"></div>
    </div>
</div>

<script type="text/javascript">
    $(document).on('change', "[name='card_pay_id']", function (){
        let card_id = $(this).val();
        $.cookie('card_pay_id', card_id, { expires: 365, path: '/' });
    });

    let use_b2p = $('[name=use_b2p]').val();
    $.cookie('use_b2p', use_b2p, { expires: 365, path: '/' });
</script>
