{* Страница заказа *}

{$meta_title = "Заявка на заём | Boostra" scope=parent}
{*$add_order_css_js = true scope=parent*}
{assign var="currentPage" value="account_additional_data"}

{capture name=page_scripts}
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.10" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/worksheet.validate.js?v=1.8.0" type="text/javascript"></script>
    {*<script src="design/{$settings->theme}/js/jquery.kladr.min.js" type="text/javascript"></script>
    <link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/jquery.kladr.min.css?v=1.13"/>
    <script src="design/{$settings->theme}/js/neworder.kladr.js?v=1.64" type="text/javascript"></script>*}
    <script type="text/javascript" src="/js/autocomplete/jquery.autocomplete-min.js"></script>
    <link rel="stylesheet" href="/js/autocomplete/styles.css"/>
    <script src="design/{$settings->theme}/js/additional_data.app.js?v=1.78" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/dadata_init.js?v=1.14" type="text/javascript"></script>
{/capture}

{* Скрипт для определния, мол, можно ли показывать чекбокс кредитного доктора или нет. Показывать его можно только
 в будние дни для источника Boostra с 10 до 17 часов по Мск *}
<script>
    function shouldShowElements() {
        let utmSource = "{$user->utm_source|escape:'javascript'}";
        let hour = {("now"|date_format:"%H")};
        let day = new Date().getDay()
        let isBoostra = (utmSource.trim() === 'Boostra' || utmSource.trim() === '');
        let isOutsideRestrictedHours = (hour >= 10 && hour < 17); // С 10:00 до 17:00
        let isWeekday = (day !== 0 && day !== 6);
        let blackList = [522891];


      return isBoostra && isOutsideRestrictedHours && isWeekday && !blackList.includes({$user->id});
    }

    function toggleVisibility(elementId, shouldShow) {
        let element = document.getElementById(elementId);
        if (element) {
            element.style.display = shouldShow ? 'block' : 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        let shouldShow = shouldShowElements();
        toggleVisibility('credit_doctor_check_wrapper', shouldShow);
    });
</script>
<style>
    .docs_wrapper {
        display: flex;
        flex-direction: column;
    }

    .docs_wrapper label {
        font-size: 18px;
    }

    .docs_wrapper label .checkbox {
        width: 10px;
        height: 10px;
        border: 1px solid #2c2b39;
    }


    .docs_wrapper .spec_size {
        width: 10px !important;
        padding: 0 20px 0 0!important;
        margin: 0 0 10px 0!important;
    }

    .docs_wrapper div {
        text-align: left;
        display: flex;
        max-width: 500px;
        /*align-items: center;*/
    }

    .docs_wrapper div p {
        font-size: 18px!important;
        margin: 5px 5px!important;
    }

    .docs_wrapper div p a {
        color: #000000!important;
    }

    .docs_wrapper div .spec_size .checkbox {
        margin-top: 13px!important;
    }

    .docs_wrapper .docs-accept {
        text-align: center;
    }

    .workLabel {
        transition: all .3s;
        opacity: 1;
        max-height: 140px;
    }

    #worksheet #additional_data #steps fieldset label.workLabel.labelHidden {
        max-height: 0px;
        opacity: 0;
        margin: 0px;
    }

    {if $is_short_flow}
    fieldset {
        margin-top: 20px;
        padding: 0;
    }

    input[type="text"], textarea {
        padding: 0.5rem 0 !important;
    }
    {/if}
</style>
<section id="worksheet">
    <div>
        <div class="box">
            <hgroup>
                {if $is_short_flow}
                    <h5>Добавьте данные о работе</h5>
                {else}
{*                    <h5>Добавьте работу и второй контакт. Звоним только если не сможем связаться с Вами. Информацию по займу*}
{*                        не разглашаем.</h5>*}
                {/if}
            </hgroup>

            {include file='display_stages.tpl' current=6 percent=91 total_step=6}

            {include "ab_banner_registration.tpl"}

            <h1 class="reg_title">Завершите оформление и получите деньги </h1>

            <form method="post" id="additional_data" onsubmit="sendMetric('reachGoal', 'extra'); return true;">
                <div id="steps">

                    <fieldset style="display: block;;">

                        <input type="hidden" value="additional_data" name="stage"/>
                        <input type="hidden" value="" name="juicescore_session_id" id="juicescore_session_id"/>
                        <input type="hidden" value="" name="juicescore_useragent" id="juicescore_useragent"/>
                        <input type="hidden" name="finkarta_fp" id="finkarta_fp" value="" />
                        <input type="hidden" value="" name="local_time" id="local_time"/>
                        <input type="hidden" value="1" name="service_recurent" id="service_reccurent_hidden"/>
                        {assign var="now" value=("now"|date_format:"%H")}
                        {assign var="isOrganic" value=(in_array(trim($user->utm_source), ['Boostra', '', 'direct1', 'direct_seo', 'direct', 'direct3']))}
                        {assign var="isBetween8and19" value=($now >= "8" && $now <= "18")}
                        {assign var="shouldCheck" value=(!$isOrganic || ($isOrganic && !$isBetween8and19)) && (!$user_return_credit_doctor)}
                        <input type="hidden" value="{if $shouldCheck}1{else}0{/if}" name="is_user_credit_doctor" id="credit_doctor_hidden"/>


                        <div class="clearfix">

                            {$other_work_scope_active = 1}
                            {if !$user->work_scope}{$other_work_scope_active = 0}{/if}
                            {if $user->work_scope == 'Работаю официально'}{$other_work_scope_active = 0}{/if}
                            {if $user->work_scope == 'Работаю неофициально'}{$other_work_scope_active = 0}{/if}
                            {if $user->work_scope == 'Безработный'}{$other_work_scope_active = 0}{/if}
                            {if $user->work_scope == 'Пенсионер'}{$other_work_scope_active = 0}{/if}

                            <label
                                    class="workLabel full js-pensioner-hidden {if $error=='empty_workplace'}error{/if}"
                                    {if $user->work_scope == 'Пенсионер'}style="display:none"{/if}
                            >
                                <input type="text" name="workplace" value="{$user->workplace}" placeholder=""
                                       required="" class="js-chars" />
                                <span class="floating-label">Место работы</span>
                                {if $error=='empty_workplace'}<span class="error">Укажите сокращенное наименование организации</span>{/if}
                            </label>

                            <label class="workLabel full js-pensioner-hidden"
                                   {if $user->work_scope == 'Пенсионер'}style="display:none"{/if}>
								<textarea
                                        name="work_full_address"
                                        placeholder=""
                                        required=""
                                        rel="work_full_address"
                                        aria-required="true"
                                        style="resize: none;"
                                        rows="2"
                                >{$work_full_address}</textarea>
                                <span class="floating-label">Адрес организации</span>
                            </label>
                            <div style="clear:both"></div>
                            <label class="js-pensioner-hidden {if $error=='empty_profession'}error{/if}"
                                   {if $user->work_scope == 'Пенсионер'}style="display:none"{/if}>
                                <div class="select">
                                    <select name="profession">
                                        {foreach $professions as $profession}
                                            <option {if $user->profession === $profession} selected {/if} value="{$profession}">{$profession}</option>
                                        {/foreach}
                                    </select>
                                </div>
                                <span class="floating-label">Ваша должность</span>
                                {if $error=='empty_profession'}<span class="error">Укажите Вашу должность</span>{/if}
                            </label>

{*                            <label class="js-pensioner-hidden {if $error=='empty_work_phone'}error{/if}"*}
{*                                   {if $user->work_scope == 'Пенсионер'}style="display:none"{/if}>*}
{*                                <input type="text" class="" name="work_phone" value="{$user->work_phone}" placeholder=""*}
{*                                       required=""/>*}
{*                                <span class="floating-label">Телефон организации</span>*}
{*                                <small class="error">{if $error=='empty_work_phone'}Укажите рабочий телефон{/if}</small>*}
{*                            </label>*}

                            <label class="{if $error=='empty_income_base'}error{/if}">
                                <input type="text" inputmode="numeric" {* class="js-digits" *} name="income_base_formated" value="{$user->income_base}"
                                       placeholder="" required=""/>
                                <input type="text" class="js-digits hidden" name="income_base" value="{$user->income_base}"
                                       placeholder="" required=""/>
                                <span class="floating-label">Доход в месяц</span>
                                <small class="error">{if $error=='empty_income_base'}Укажите основной доход{/if}</small>
                            </label>

                            <div style="clear:both"></div>

                            <label class="{if $error=='empty_education'}error{/if}">
                                <div class="select">
                                    <select class="education" name="education">
                                        <option value="1" {if $user->education == 1}selected=""{/if}>Среднее</option>
                                        <option value="2" {if $user->education == 2}selected=""{/if}>Среднее специальное</option>
                                        <option value="3" {if $user->education == 3}selected=""{/if}>Незаконченное высшее</option>
                                        <option value="4" {if $user->education == 4}selected=""{/if}>Высшее профессиональное</option>
                                        <option value="5" {if $user->education == 5}selected=""{/if}>Другое</option>
                                    </select>
                                </div>
                                <span class="floating-label">Образование</span>
                                <small class="error">{if $error=='empty_education'}Укажите образование{/if}</small>
                            </label>
                        </div>

                        <div class="register js-pensioner-hidden" style="display:none">

                            <input type="hidden" name="Regroom" value="{$user->Workroom}"/>
                            <input type="hidden" name="Regbuilding" value="{$user->Workbuilding}"/>
                            <input type="hidden" name="Reghousing" value="{$user->Workhousing}"/>
                            <input type="hidden" name="Regstreet" value="{$user->Workstreet}"/>
                            <input type="hidden" name="Regcity" value="{$user->Workcity}"/>
                            <input type="hidden" name="Regregion" value="{$user->Workregion}"/>

                            <input type="hidden" name="Regindex" id="prop_zip" value="{$user->Workindex}">
                            <input type="hidden" name="Regregion_shorttype" id="regregion_shorttype"
                                   value="{$user->Regregion_shorttype}">
                            <input type="hidden" name="Regcity_shorttype" id="regcity_shorttype"
                                   value="{$user->Regcity_shorttype}">
                            <input type="hidden" name="Regstreet_shorttype" id="regstreet_shorttype"
                                   value="{$user->Regstreet_shorttype}">

                        </div>

{*                            <div class="docs_wrapper">*}
{*                                <p class="toggle-conditions">Я согласен со всеми условиями:*}
{*                                    <span class="arrow">*}
{*                                        <img src="{$config->root_url}/design/boostra_mini_norm/img/icons/chevron-svgrepo-com.svg" alt="Arrow" />*}
{*                                    </span>*}
{*                                </p>*}
{*                            <div class="conditions">*}

{*                                <div>*}
{*                                    <label class="spec_size">*}
{*                                        <div class="checkbox"*}
{*                                             style="border-width: 1px;width: 10px !important;height: 10px !important;">*}
{*                                            <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="0"*}
{*                                                   id="agreed_1"*}
{*                                                   name="agreed_1"/>*}
{*                                            <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>*}
{*                                        </div>*}
{*                                    </label>*}
{*                                    <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с*}
{*                                        <a href="http://www.boostra.ru/files/docs/obschie-usloviya.pdf" target="_blank">Общими*}
{*                                            условиями договора потребительского микрозайма</a></p>*}
{*                                </div>*}
{*                                <div>*}
{*                                    <label class="spec_size">*}
{*                                        <div class="checkbox"*}
{*                                             style="border-width: 1px;width: 10px !important;height: 10px !important;">*}
{*                                            <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1"*}
{*                                                   id="agreed_4"*}
{*                                                   name="agreed_4"/>*}
{*                                            <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>*}
{*                                        </div>*}
{*                                    </label>*}
{*                                    <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с*}
{*                                        <a href="http://www.boostra.ru/files/docs/pravila-predostavleniya.pdf"*}
{*                                           target="_blank">*}
{*                                            Правилами предоставления займов ООО МКК "Аквариус"*}
{*                                        </a></p>*}
{*                                </div>*}
{*                                <div>*}
{*                                    <label class="spec_size">*}
{*                                        <div class="checkbox"*}
{*                                             style="border-width: 1px;width: 10px !important;height: 10px !important;">*}
{*                                            <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1"*}
{*                                                   id="agreed_3"*}
{*                                                   name="agreed_3"/>*}
{*                                            <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>*}
{*                                        </div>*}
{*                                    </label>*}
{*                                    <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с*}
{*                                        <a href="https://www.boostra.ru/files/docs/informatsiyaobusloviyahpredostavleniyaispolzovaniyaivozvrata.pdf"*}
{*                                           target="_blank">*}
{*                                            Правилами обслуживания и пользования услугами ООО МКК "Аквариус"*}
{*                                        </a></p>*}
{*                                </div>*}
{*                                {if $pdn_doc > 50}*}
{*                                    <div>*}
{*                                        <label class="spec_size">*}
{*                                            <div class="checkbox"*}
{*                                                 style="border-width: 1px;width: 10px !important;height: 10px !important;">*}
{*                                                <input class="js-agreeed-asp js-need-verify-modal" type="checkbox"*}
{*                                                       value="1" id="agreed_10"*}
{*                                                       name="agreed_10"/>*}
{*                                                <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>*}
{*                                            </div>*}
{*                                        </label>*}
{*                                        <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с*}
{*                                            <a href="user/docs?action=pdn_excessed" target="_blank">*}
{*                                                Уведомлением о повышенном риске невыполнения кредитных обязательств*}
{*                                            </a></p>*}
{*                                    </div>*}
{*                                {/if}*}
{*                                <div>*}
{*                                    <label class="spec_size">*}
{*                                        <div class="checkbox"*}
{*                                             style="border-width: 1px;width: 10px !important;height: 10px !important;">*}
{*                                            <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1"*}
{*                                                   id="agreed_3"*}
{*                                                   name="agreed_3"/>*}
{*                                            <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>*}
{*                                        </div>*}
{*                                    </label>*}
{*                                    <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с*}
{*                                        <a href="user/docs?action=micro_zaim" target="_blank">Заявлением*}
{*                                            о предоставлении микрозайма</a></p>*}

{*                                </div>*}
{*                                <div>*}
{*                                    <label class="spec_size">*}
{*                                        <div class="checkbox"*}
{*                                             style="border-width: 1px;width: 10px !important;height: 10px !important;">*}
{*                                            <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1"*}
{*                                                   id="agreed_5"*}
{*                                                   name="agreed_5"/>*}
{*                                            <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>*}
{*                                        </div>*}
{*                                    </label>*}
{*                                    <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с*}
{*                                        <a href="http://www.boostra.ru/files/docs/politikakonfidentsialnosti.pdf"*}
{*                                           target="_blank">*}
{*                                            Политикой конфиденциальности ООО МКК "Аквариус"*}
{*                                        </a></p>*}
{*                                </div>*}
{*                                <div>*}
{*                                    <label class="spec_size">*}
{*                                        <div class="checkbox"*}
{*                                             style="border-width: 1px;width: 10px !important;height: 10px !important;margin-top: 5px;">*}
{*                                            <input class="js-agree-claim-value js-need-verify-modal" type="checkbox" value="1" id="agreed_11"*}
{*                                                   name="agreed_11"/>*}
{*                                            <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>*}
{*                                        </div>*}
{*                                    </label>*}
{*                                    <p>Отказываюсь от уступки права требования</p>*}
{*                                </div>*}
{*                                <div>*}
{*                                    <label class="spec_size">*}
{*                                        <div class="checkbox"*}
{*                                             style="border-width: 1px;width: 10px !important;height: 10px !important;">*}
{*                                            <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1"*}
{*                                                   id="agreed_9"*}
{*                                                   name="agreed_9"/>*}
{*                                            <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>*}
{*                                        </div>*}
{*                                    </label>*}
{*                                    <p>Настоящим выражаю свое <a*}
{*                                                href="http://www.boostra.ru/files/docs/soglasie-klienta-na-poluchenie-informatsii-iz-byuro-kreditnyh-istorij.pdf"*}
{*                                                target="_blank">согласие</a>*}
{*                                        на запрос кредитного отчета в бюро кредитных историй</p>*}

{*                                </div>*}

{*                                {include file="credit_doctor/credit_doctor_checkbox.tpl"}*}

{*                                <div>*}
{*                                    <label class="spec_size">*}
{*                                        <div class="checkbox"*}
{*                                             style="border-width: 1px;width: 10px !important;height: 10px !important;">*}
{*                                            <input class="js-service-recurent js-need-verify-modal" type="checkbox" value="1"*}
{*                                                   id="service_recurent_check"*}
{*                                            />*}
{*                                            <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>*}
{*                                        </div>*}
{*                                    </label>*}
{*                                    <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с*}
{*                                        <a class="block_1"*}
{*                                                href="http://www.boostra.ru/files/docs/soglashenie-o-regulyarnyh-rekurentnyh-platezhah.pdf"*}
{*                                                target="_blank">Соглашением о применении регулярных (рекуррентных)*}
{*                                            платежах</a>.</p>*}

{*                                </div>*}
{*                                <div>*}
{*                                    <label class="spec_size">*}
{*                                        <div class="checkbox"*}
{*                                             style="border-width: 1px;width: 10px !important;height: 10px !important;">*}
{*                                            <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="1"*}
{*                                                   id="agreed_8"*}
{*                                                   name="agreed_8"/>*}
{*                                            <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>*}
{*                                        </div>*}
{*                                    </label>*}
{*                                    <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с*}
{*                                        <a href="http://www.boostra.ru/files/docs/Договор_об_условиях_предоставления_Акционерное_общество_«Сургутнефтегазбанк».pdf"*}
{*                                           target="_blank">*}
{*                                            Договором об условиях предоставления Акционерное общество*}
{*                                            «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием*}
{*                                            реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей»*}
{*                                            (Публичная оферта)*}
{*                                        </a></p>*}
{*                                </div>*}
{*                                <div>*}
{*                                    <label class="spec_size">*}
{*                                        <div class="checkbox"*}
{*                                             style="border-width: 1px;width: 10px !important;height: 10px !important;">*}
{*                                            <input class="js-agreeed-asp" type="checkbox" value="1"*}
{*                                                   id="agreed_12"*}
{*                                                   name="agreed_12" />*}
{*                                            <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>*}
{*                                        </div>*}
{*                                    </label>*}
{*                                    <p>Настоящим подтверждаю, что полностью ознакомлен и согласен с подключением дополнительной услуги «Вита-мед» стоимостью 600 рублей*}
{*                                        </p>*}
{*                                </div>*}
{*                                <div id="not_checked_info" style="display:none">*}
{*                                    <strong style="color:#f11">Вы должны согласиться с вышестоящими условиями</strong>*}
{*                                </div>*}
{*                            </div>*}
{*                        </div>*}
                        <div class="next">
                            {if $is_developer}
                                <a class="button big button-inverse" id="" href="account?step=files">Назад</a>
                            {/if}
                            {if false}
                                <style>
                                    .service_insurance .col {
                                        display: inline-block;
                                        margin: 0 15px;
                                        width: 45%;
                                        vertical-align: top;
                                    }

                                    @media screen and (max-width: 768px) {
                                        .service_insurance .col {
                                            width: 100%;
                                            padding-bottom: 20px;
                                            border-bottom: 1px solid;
                                        }
                                    }

                                    .service_insurance .buttons {
                                        height: 100px;
                                        line-height: 100px;
                                        margin-bottom: 20px;
                                    }

                                    .service_insurance .buttons .button {
                                        background: -webkit-linear-gradient(356deg, rgb(33, 202, 80) 0%, rgb(66, 242, 115) 95%, rgb(68, 245, 117) 100%);
                                        background: -o-linear-gradient(356deg, rgb(33, 202, 80) 0%, rgb(66, 242, 115) 95%, rgb(68, 245, 117) 100%);
                                        background: -ms-linear-gradient(356deg, rgb(33, 202, 80) 0%, rgb(66, 242, 115) 95%, rgb(68, 245, 117) 100%);
                                        background: -moz-linear-gradient(356deg, rgb(33, 202, 80) 0%, rgb(66, 242, 115) 95%, rgb(68, 245, 117) 100%);
                                        background: linear-gradient(94deg, rgb(33, 202, 80) 0%, rgb(66, 242, 115) 95%, rgb(68, 245, 117) 100%);
                                        border: none;
                                    }

                                    .service_insurance .descriptions {
                                        text-align: justify;
                                    }

                                    .service_insurance .descriptions a {
                                        text-decoration: underline;
                                    }
                                </style>
                                <div class="service_insurance">
                                    <input type="hidden" id="service_insurance" name="service_insurance" value="0">
                                    <div class="col">
                                        <div class="buttons">
                                            <a href="" class="button big js-insurance">Получить заём на льготных
                                                условиях</a>
                                        </div>
                                        <div class="descriptions">
                                            Нажатием на эту кнопку
                                            выражаю свое желание и соглашаюсь с оплатой дополнительной
                                            услуги "Кредитный доктор" в размере {$credit_doctor_amount} рублей,
                                            предоставляемой в соответствии с
                                            "Условиями предоставления платной услуги", "Заявлением на услугу" и офертой
                                            ООО "Алфавит".
                                            <br/>
                                            <span>* Льгота в виде скидки предоставляется на ежедневную процентную ставку,
                                                которая после скидки составляет {$discount_rate}%</span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="buttons">
                                            <a href="" style="text-decoration: underline;">Заём на общих условиях</a>
                                        </div>
                                        <div class="descriptions">
                                            Я готов выполнять обязательства по займу без возникновения просроченной
                                            задолженности.
                                            В помощи для снижения своей долговой нагрузки не нуждаюсь.
                                            <br/>
                                            <span>* Ставка по займу 1% в день</span>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                    $(document).ready(function () {
                                        $('.service_insurance .buttons a').on('click', function () {
                                            //$('#service_insurance').val(+$(this).is('.js-insurance'));

                                            $(this).closest('form').submit();

                                            return false;
                                        });
                                    });
                                </script>
                            {else}
                                <button class="button big" id="doit" type="submit" name="neworder">Получить деньги
                                </button>
                            {/if}
                        </div>
                    </fieldset>
                </div>
            </form>
        </div>
    </div>
</section>

<div class="hidden">

    <div id="accept_order" class="accept_credit_modal white-popup mfp-hide">
        {* Добавляем скролл в модальное окно и изменяем его размер *}

        <div id="modal_error" style="display:none">
            <strong style="color:#f11">Необходимо согласиться с условиями</strong>
        </div>
        <div>
            <p> Я согласен со всеми условиями:</p>
        </div>
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="0" id="agreed_1"
                           name="agreed_1" checked=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>Настоящим подтверждаю, что полностью ознакомлен и согласен с <a href="http://www.boostra.ru/files/docs/obschie-usloviya.pdf" target="_blank">Общими условиями договора потребительского микрозайма</a>
        </div>
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="0" id="agreed_2"
                           name="agreed_2" checked=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            Настоящим подтверждаю, что полностью ознакомлен и согласен с
            <a href="https://www.boostra.ru/preview/ind_usloviya?user_id={$user->id}" target="_blank">
                Типовой формой индивидуальных условий договора потребительского микрозайма
            </a>
        </div>
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="0" id="agreed_4"
                           name="agreed_4" checked=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            Настоящим подтверждаю, что полностью ознакомлен и согласен с
            			<a href="http://www.boostra.ru/files/docs/pravila-predostavleniya.pdf" target="_blank">Правилами предоставления займов ООО МКК "Аквариус"</a>
        </div>
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;">
                    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="0"
                           id="agreed_3"
                           name="agreed_3" checked=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            Настоящим подтверждаю, что полностью ознакомлен и согласен с
                <a href="user/docs?action=micro_zaim" target="_blank">Заявлением
                    о предоставлении микрозайма</a>

        </div>
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="0" id="agreed_3"
                           name="agreed_3" checked=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            Настоящим подтверждаю, что полностью ознакомлен и согласен с
            <a href="https://www.boostra.ru/files/docs/informatsiyaobusloviyahpredostavleniyaispolzovaniyaivozvrata.pdf"
               target="_blank">
                Правилами обслуживания и пользования услугами ООО МКК "Аквариус"
            </a>
        </div>
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="0" id="agreed_5"
                           name="agreed_5" checked=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            Настоящим подтверждаю, что полностью ознакомлен и согласен с
            <a href="http://www.boostra.ru/files/docs/politikakonfidentsialnosti.pdf" target="_blank">
                Политикой конфиденциальности ООО МКК "Аквариус"
            </a>
        </div>

        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="0" id="agreed_9"
                           name="agreed_9" checked=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            Настоящим выражаю свое <a href="http://www.boostra.ru/files/docs/soglasie-klienta-na-poluchenie-informatsii-iz-byuro-kreditnyh-istorij.pdf" target="_blank">согласие</a> на запрос кредитного отчета в бюро кредитных историй
        </div>
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                    <input class="js-agree-claim-value js-need-verify-modal" type="checkbox" value="0" id="agreed_11"
                           name="agreed_11" checked=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            Отказываюсь от уступки права требования
        </div>
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="0" id="agreed_10"
                           name="agreed_10" checked=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            Настоящим подтверждаю, что полностью ознакомлен и согласен с
            <a href="http://www.boostra.ru/files/docs/soglashenie-ob-ispolzovanii-analoga-sobstvennoruchnoj-podpisi-pri-distantsionnom-vzaimodejstvii-mkk-ooo-bustra.pdf"
               target="_blank">
                Соглашением об использовании аналога собственноручной подписи при дистанционном взаимодействии
            </a>
        </div>
        {include file="credit_doctor/credit_doctor_checkbox.tpl"}
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                    <input class="js-service-recurent js-need-verify-modal" type="checkbox" value="0" id="service_recurent_check"
                           checked="true"/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            Настоящим подтверждаю, что полностью ознакомлен и согласен с
                <a class="block_1"
                   href="http://www.boostra.ru/files/docs/soglashenie-o-regulyarnyh-rekurentnyh-platezhah.pdf"
                   target="_blank">
                    Соглашением о применении регулярных (рекуррентных) платежах
                </a>
            <script>
                $(document).ready(function () {
                    $('.block_1').click(function () {
                        $('.content_block_1').slideToggle(300);
                        return false;
                    });
                });
            </script>

        </div>
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;margin-top: 5px;">
                    <input class="js-agreeed-asp js-need-verify-modal" type="checkbox" value="0" id="agreed_8"
                           name="agreed_8" checked=""/>
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            Настоящим подтверждаю, что полностью ознакомлен и согласен с
            			<a href="http://www.boostra.ru/files/docs/Договор_об_условиях_предоставления_Акционерное_общество_«Сургутнефтегазбанк».pdf" target="_blank">
                            Договором об условиях предоставления Акционерное общество «Сургутнефтегазбанк» услуги по переводу денежных средств с использованием реквизитов банковской карты с помощью Интернет-ресурса ООО «Бест2пей» (Публичная оферта)
                        </a>
        </div>
        <div>
            <label class="spec_size">
                <div class="checkbox"
                     style="border-width: 1px;width: 16px !important;height: 16px !important;">
                    <input class="js-agreeed-asp" type="checkbox" value="1"
                           id="agreed_12"
                           name="agreed_12" />
                    <span style="margin:0;width: 100%;height: 100%;top: 0;left: 0;"></span>
                </div>
            </label>
            Настоящим подтверждаю, что полностью ознакомлен и согласен с подключением ПО «ВитаМед» стоимостью 600 рублей
        </div>

        <button title="%title%" type="button" class="mfp-close"
                style="color: #fff;font-size: 20px;background: green;width: 48px;padding: 16px;height: 48px;right: 16px;">
            ОК
        </button>
    </div>
    {include file="credit_doctor/credit_doctor_popup.tpl"}
</div>
<script src="design/{$settings->theme}/js/creditdoctor_modal.app.js?v=1.03" type="text/javascript"></script>

<script>
    window.addEventListener('sessionready', function (e) {
        $('#juicescore_useragent').val(navigator.userAgent)

        if (typeof FingerprintID !== 'undefined' && FingerprintID)
            $('#finkarta_fp').val(FingerprintID);
    })

    $(document).ready(function () {
        $('.toggle-conditions').click(function () {
            $('.conditions').slideToggle();
        });
    });
</script>

{if $settings->additional_work_scope}
{literal}
<script>
    $(document).ready(function () {
        const profSelect = $('select[name="profession"]');
        const inputNames = ['workplace', 'work_full_address'];
        function updProfession(ev) {
            const isPensioner = profSelect.val() === 'пенсионер';
            const isSelfEmployed = profSelect.val() === 'самозанятость';
            const isNotEmployed = profSelect.val() === 'не работаю';
            const checked = (isPensioner || isSelfEmployed || isNotEmployed);
            inputNames.forEach(function (name) {
                const $input = $(`[name="${name}"]`);

                if ($input.length) {
                    $input.closest('label.error').removeClass('error');

                    if (checked) {
                        if(!$input.data('checked')) {
                            $input.data('saved-value', $input.val());
                            $input.data('checked', checked);
                        }
                        $input.val('');
                        $input.removeAttr('required').removeClass('error').removeAttr('aria-describedby').removeAttr('aria-invalid');
                        $input.attr('aria-required', 'false');
                        $input.parent().addClass('labelHidden');

                        $input.nextAll('.error').remove();
                    } else {
                        if($input.data('checked') && $input.data('saved-value')) {
                            $input.val($input.data('saved-value'));
                            $input.removeData('checked');
                            $input.removeData('saved-value');
                        }
                        $input.attr('required', 'required');
                        $input.attr('aria-required', 'true');
                        $input.parent().removeClass('labelHidden');
                    }
                }
            });
        }

        function onInputClick() {
            const isPensioner = profSelect.val() === 'пенсионер';
            const isSelfEmployed = profSelect.val() === 'самозанятость';
            const isNotEmployed = profSelect.val() === 'не работаю';
            const checked = (isPensioner || isSelfEmployed || isNotEmployed);
            if (checked) {
                inputNames.forEach(function (name) {
                    const $input = $(`[name="${name}"]`);
                    profSelect.val('');
                    $input.attr('required', 'required');
                    $input.attr('aria-required', 'true');
                });
            }
        }

        inputNames.forEach(function (name) {
            const $input = $(`[name="${name}"]`);
            $input.on('click', onInputClick);
        });
        updProfession();
        // work_scope checkbox toggle
        profSelect.change(updProfession);
    });
</script>
{/literal}
{/if}

{literal}
<script>
    $(document).ready(function () {
        const noWorkProfessions = ['не работаю', 'пенсионер', 'студент', 'самозанятость'];
        const workFieldNames = ['workplace', 'work_full_address'];

        function updProfessionRequired() {
            const profVal = $('[name="profession"]').val();
            const isNoWork = noWorkProfessions.indexOf(profVal) !== -1;

            workFieldNames.forEach(function (name) {
                const $input = $('[name="' + name + '"]');
                if (!$input.length) return;

                if (isNoWork) {
                    $input.removeAttr('required').removeAttr('aria-invalid').removeAttr('aria-describedby');
                    $input.attr('aria-required', 'false');
                    $input.closest('label').removeClass('error');
                    $input.nextAll('.error').remove();
                } else {
                    $input.attr('required', 'required');
                    $input.attr('aria-required', 'true');
                }
            });
        }

        $('[name="profession"]').on('change', updProfessionRequired);
        updProfessionRequired();
    });
</script>
{/literal}

{if !empty($check_scorings_nk)}
    <script>

      // Проверка готовности скорингов
      function checkScoringsComplete() {

        function checkScorings() {
          $.ajax({
            url: '/ajax/check_scorings_nk.php',
            data: {
              action: 'check',
              timeout: false
            },
            success: function (data) {

              if (data?.result?.ready) {
                clearInterval(timerInterval);

                if (data?.result?.decision === 'decline') {
                  location.reload();
                }
              }
            }
          });
        }

        const interval = 10000;
        let timePassed = 0;
        let timerInterval = setInterval(function () {
          timePassed += interval;
          checkScorings();
        }, interval);

        // 900 сек
        if (timePassed > 900000) {
          clearInterval(timerInterval);
        }
      }

      checkScoringsComplete();
    </script>
{/if}