<div id="block_sbp">
    <div id="main_sbp">
        <h4>Пользуйтесь СБП для удобной оплаты</h4>
        <div class="logo-sector">
            <img class="sbp_logo" src="design/{$settings->theme|escape}/img/sbp_logo.png" alt="СБП"/>
        </div>
        {if $isTestUser}
        <div id="not_checked_info_sbp" style="display:none">
            <strong style="color:#f11">Вы должны согласиться с договором и нажать "Привязать счет СБП"</strong>
        </div>
        <div class="docs_wrapper docs_wrapper_sbp">
            <div class="conditions" style="max-width: none;">
                <h3>Я согласен со следующим</h3>
                <div style="max-width: none;">
                    <label class="spec_size">
                        <div class="checkbox" style="border-width:1px;width:16px!important;height:16px!important;">
                            <input id="recurrent_sbp" type="checkbox"
                                name="recurring_consent_sbp" value="1" checked="checked"/>
                            <span style="margin:0;width:100%;height:100%;top:0;left:0;"></span>
                        </div>
                    </label>
                    <p>Я согласен с <a href="/files/docs/soglashenie-o-regulyarnyh-rekurentnyh-platezhah.pdf" class="" target="_blank">cоглашением о применении регулярных (рекуррентных) платежах</a></p>
                </div>
                <div id="b2pay_sbp" style="max-width: none;">
                    <label class="spec_size">
                        <div class="checkbox" style="border-width:1px;width:16px!important;height:16px!important;">
                            <input class="js-need-verify-sbp js-agree-claim-value-sbp" type="checkbox"
                                name="agree_claim_value_sbp" value="0" checked="checked"/>
                            <span style="margin:0;width:100%;height:100%;top:0;left:0;"></span>
                        </div>
                    </label>
                    <p>Я согласен с <a href="files/docs/viploan/politika-bezopasnosti-platezhei-best2pay.pdf" class="" target="_blank">договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей» (Публичная оферта)</a></p>
                </div>
            </div>
        </div>
        {/if}
        <div class="block_sbp_footer">
            <button class="button green attach_sbp_btn" href="#">Привязать счет СБП</button>
        </div>
        <p style="margin: 10px 0; font-size: 12px; color: #333;">
            Убедитесь, что привязанный номер телефона<br>
            <b>+{$user->phone_mobile|substr:0:1} ({$user->phone_mobile|substr:1:3}) {$user->phone_mobile|substr:4:3}-{$user->phone_mobile|substr:7:2}-{$user->phone_mobile|substr:9:2}</b><br>
            совпадает с номером телефона СБП в выбранном банке!
        </p>
        <p class="sbp_description">Данные защищены сквозным шифрованием и передаются по безопасному соединению.</p>
    </div>
</div>
<style>
    .block_sbp_footer {
        text-align: center;
    }
    .block_sbp_footer button {
        padding: 0.4em 1.3em;
    }
    #main_sbp {
        width: 300px;
        margin-bottom: 15px;
    }
    #main_sbp>h4{
        font-size: 15px;
    }
    .sbp_logo {
        width: 150px !important;
        height: 30% !important;
        display: inline-block !important;
    }
    .logo-sector {
        text-align: center;
        width: 80%;
    }
    .sbp_description {
        font-size: 11px !important;
        margin: 0 !important;
        text-align: center;
    }
    @media (max-width:480px)
    {
        #main_sbp, .sbp_description {
            width: 100%;
        }
        .sbp_description {
            font-size: 1.3rem;
        }
        .logo-sector>img{
            width: 60% !important;
        }
    }
</style>