{* Шаблон страницы-прослойки Кредитного рейтинга для новых пользователкй *}
<style>
    @media screen and (max-width: 580px) {
        .title-cr {
            width: 100%;
            align-items: flex-start;
            flex-flow: column;
        }

        .title-cr hr {
            display: none !important;
        }

        .title-cr a {
            margin-top: 0.3rem;
        }

        #credit_rating_new_user {
            padding-top: 0px;
        }
    }
</style>
<div class="panel" id="credit_rating_new_user">
    <input name="use_b2p" value="{$use_b2p}" type="hidden"/>
    {if $is_credit_rating_page}
        <input type="hidden" name="is_credit_rating_page" value="1"/>
    {/if}
    <h1 style="margin-bottom:1rem">{$user_name|escape}</h1>
    <div class="flex-auto title-cr">
        <p class="text-bold">Повысьте возможную вероятность одобрения</p>
        <hr class="mr-x-10 skip-button-rating" width="1" size="25"/>
        <button
                class="btn-cr skip-button-rating btn-violet"
                onclick="user_cr_app.skip_rating_pay();"
                style="margin-top: 0;"
                href="javascript:void(0);">
            <small>подать заявку без рейтинга</small>
        </button>
    </div>
    <div id="user_lk_credit_rating_wrapper">
        {include 'credit_rating/credit_rating.tpl'}
    </div>
    <p>Ваш кредитный рейтинг составляет <span id="rating-ball" class="mr-x-10"></span> <a class="text-violet"
                                                                                          data-href="#user-credit-rating-cards">Получить
            рейтинг</a></p>
    <div class="rating_btn_wrapper flex-vertical">
        Персональный кредитный рейтинг - это оценка финансовой надёжности заёмщика, который показывает, насколько высоки
        шансы получить кредит.
        <button class="btn-cr btn-violet" data-href="#user-credit-rating-cards">Получить рейтинг сейчас за <strong>399</strong> рублей</button>
    </div>
    <div
            class="credit-rating-notice"
            style="margin-top: 60px; display: none;">
        <div>
            Введя персональный код, направленный мне в СМС сообщении, я соглашаюсь с оплатой
            услуги предоставления моего кредитного рейтинга в размере 399 рублей, предоставляемой
            в соответствии с
            <a href="https://www.boostra.ru/files/docs/usloviya-okazaniya-mkk-ooo-bustra-dopolnitelnoi-platnoi-uslugi-predostavlenie-kreditnogo-reitinga.pdf"
               target="_blank">
                "Условиями предоставления платной услуги"
            </a>
            и
            <a href="/user?action=download_credit_rating_contract" target="_blank">
                "Заявлением на услугу"
            </a>
        </div>
    </div>
    <div class="cards" style="display: none;" id="user-credit-rating-cards">
        <p>Оплатить при помощи:</p>
        {if $cards}
            <div class="credit-card-list">
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
                                    <input type="radio" name="card_pay_id" value="other"/>
                                    <span></span>
                                </div>
                                Оплатить новой картой
                            </label>
                        </li>
                    </ul>
                </div>
            </div>
        {else}
            <div class="nocards">
                <div class="credit-card-list">
                    <label>
                        <div class="split">
                            <ul>
                                <li class="">
                                    <label>
                                        <div class="radio">
                                            <input type="radio" checked name="card_pay_id" value="other"/>
                                            <span></span>
                                        </div>
                                        Оплатить новой картой
                                    </label>
                                </li>
                            </ul>
                        </div>
                    </label>
                </div>
            </div>
        {/if}
        <div class="user-cr-sms-block">
            <button onclick="user_cr_app.showSmsBlock(this);" class="btn-cr btn-violet">Получить код</button>
            <div id="user-cr-sms-send" style="display: none;">
                <div class="flex-auto">
                    <input name="sms" type="text" class="input-grey" placeholder="Введите код их СМС"/>
                    <a href="javascript:void(0);" onclick="user_cr_app.send_sms();" class="ml-10"><small>Получить код
                            ещё раз</small></a>
                    <span class="timer-text" style="display: none;"></span>
                </div>
                <p class="text-red" style="display: none;"><small></small></p>
                <button class="btn-cr btn-violet" id="send-pay-cr" onclick="user_cr_app.init_pay();"
                        style="display: none;">Отправить
                </button>
            </div>
        </div>
    </div>
    <input type="hidden" name="user_phone" value="{$user->phone_mobile}"/>
    <input type="hidden" name="insurer" value="{$insurer}"/>
</div>
<script src="design/{$settings->theme}/js/credit_rating_user_lk.js?v=1.012" type="text/javascript"></script>
