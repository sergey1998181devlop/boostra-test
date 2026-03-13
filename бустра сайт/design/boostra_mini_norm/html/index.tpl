<!DOCTYPE html>
{*
Общий вид страницы
Этот шаблон отвечает за общий вид страниц без центрального блока.
*}

<html>
<head>
    <base href="{$config->root_url}/"/>
    <title>{$meta_title|escape}</title>

    {* Метатеги *}
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="title" content="{$meta_title2|escape}" />
    <meta name="description" content="{$meta_description|escape}" />
    <meta name="keywords"    content="{$meta_keywords|escape}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{$csrf_token}">

    {* Канонический адрес страницы *}
    {if isset($canonical)}<link rel="canonical" href="{$config->root_url}{$canonical}"/>{/if}

    <link rel="shortcut icon" type="image/ico" href="design/{$settings->theme|escape}/img/favicon.ico">

    <meta property="og:locale" content="ru_RU" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="" />
    <meta property="og:url" content="http://boostra.ru" />
    <meta property="og:site_name" content="boostra" />
    <meta property="og:image" content="design/{$settings->theme|escape}/img/favicon.png" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:description" content="{$meta_description|escape}" />
    <meta name="twitter:title" content="" />
    <meta name="twitter:image" content="design/{$settings->theme|escape}/img/favicon192x192.png" />
    <link rel="stylesheet" type="text/css" href="design/orange_theme/css/magnific-popup.css?v=1.00" />
    <link rel="stylesheet" type="text/css" href="design/orange_theme/css/modal.css?v=1.02" />
    <link rel="icon" href="design/{$settings->theme|escape}/img/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" href="design/{$settings->theme|escape}/img/favicon192x192.png" sizes="192x192" />
    <link rel="apple-touch-icon-precomposed" href="design/{$settings->theme|escape}/img/favicon180x180.png" />
    <meta name="msapplication-TileImage" content="design/{$settings->theme|escape}/img/favicon270x270.png" />
    <link rel="image_src" href="design/{$settings->theme|escape}/img/favicon.png" />
    <meta content="design/{$settings->theme|escape}/img/social.png" name="og:image" property="og:image">

    <link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/ion.rangeSlider.css?v=1.05"/>
    {if $add_order_css_js}
        <link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/jquery.kladr.min.css?v=1.12"/>
        <link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/order_status.css?v=1.00"/>
    {/if}
    <link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/owl.carousel.min.css"/>
    <link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/owl.theme.default.min.css"/>
    <link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/style.css?v=3.91"/>
    <link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/pages.css?v=3.11"/>
    <link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/media.css?v=2.915" />
    <link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/new-design-2025.css?v=1.10" />
    <link rel="stylesheet" href="design/{$settings->theme}/css/friend_payment.css?v=1.02">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
    <link
            href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
            rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/faq_highlight.css" />
    {if $usedesk_config.operatorAvatar}
        <link rel="stylesheet" type="text/css"
              href="design/{$settings->theme|escape}/css/usedesk-customizations.css?v=1.04"/>
        <style>
            :root {
                --usedesk-operator-avatar: url('{$usedesk_config.operatorAvatar|escape:'quotes'}');
            }
        </style>
    {/if}
    {$smarty.capture.page_styles}


    <link rel="stylesheet" type="text/css" href="design/orange_theme/css/bootstrap/bootstrap-icons-1.9.1/bootstrap-icons.css"/>
    <link rel="stylesheet" type="text/css" href="design/{$settings->theme}/js/jquery/jquery-ui.min.css"/>
    <link rel="stylesheet" href="design/{$settings->theme}/css/mobileBanners/img.css?v=1.1000">

    <script src="design/{$settings->theme}/js/jquery-2.1.3.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery/jquery-ui.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery/datepicker/jquery.ui.datepicker-ru.js" type="text/javascript"></script>

    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.10" type="text/javascript"></script>
    <script src="design/{$settings->theme|escape}/js/owl.carousel.min.js"></script>
    <script src="/design/{$settings->theme}/js/friend_payment.js?v=1.02"></script>
    <script defer src="design/boostra_mini_norm/js/email_feedback.js?v=1.03"></script>
    <script src="design/{$settings->theme|escape}/js/user_tickets.js" type="text/javascript"></script>
    <script src="design/{$settings->theme|escape}/js/faq_highlight.js" type="text/javascript"></script>
    <!--script src="https://cfv4.com/landings.js"></script-->
    <meta name="cmsmagazine" content="6f3ef3c26272e3290aa0580d7c8d86ce" />


    <script>
        window.siteConfig = {
            js_config_is_dev: {if $config->js_config_is_dev}{$config->js_config_is_dev|escape:'javascript'}{else}0{/if}
        }

            {if $is_developer}
            var is_developer = 1;
            console.info('is developer');
            {else}
            var is_developer = 0;
            {/if}

            {if $is_admin}
            var is_admin = 1;
            console.info('is admin');
            {else}
            var is_admin = 0;
            {/if}

            {if $is_CB}
            var is_CB = 1;
            {else}
            var is_CB = 0;
            {/if}
        </script>

    <script>
        var BASE_PERCENTS = {$base_percents};
    </script>

    <style>
        #kladr_autocomplete ul.autocomplete li:first-child {
            display:none
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            let recaptcha_callback;
            let recaptcha_buyclick;
            window.recaptchaOnloadCallback = function () {
                if ($('#recaptcha_feedback').length > 0) {
                    grecaptcha.render('recaptcha_feedback', { 'sitekey' : '{$settings->apikeys['recaptcha']['key']}' });
                    }
                };
            });

    </script>
    <script src='https://www.google.com/recaptcha/api.js?onload=recaptchaOnloadCallback&render=explicit' async defer></script>

    {if $module == 'MainView'}
        {include 'micro_markup.tpl'}
        <script>
            history.pushState(-1, null);
            if(window.history && history.pushState){
                window.addEventListener('load', function(){
                    history.pushState(-1, null);
                    history.pushState(0, null);
                    history.pushState(1, null);
                    history.go(-1);
                    this.addEventListener('popstate', function(event, state){
                        if(event.state == -1){
                            window.location.href = '{$settings->reject_link}';
                        }
                    }, false);
                }, false);
            }
        </script>
    {/if}
    {literal}
        <script type="text/javascript">!function(){var t=document.createElement("script");t.type="text/javascript",t.async=!0,t.src='https://vk.com/js/api/openapi.js?169',t.onload=function(){VK.Retargeting.Init("VK-RTRG-1440253-hcsa0"),VK.Retargeting.Hit()},document.head.appendChild(t)}();</script><noscript><img src="https://vk.com/rtrg?p=VK-RTRG-1440253-hcsa0" style="position:fixed; left:-999px;" alt=""/></noscript>
    {/literal}
    <script defer src="/js/alpine_3.14.3.cdn.min.js"></script>
    <script src="/js/pixel.js" async></script>
</head>
<body class="blue-theme {if $module=='MainPage'}main{/if} {if in_array($module, ["LoanView", "AccountView"])}get-loan{/if}" data-hh="{$settings->hui}">

<style>
    #admin_inform {
        position:relative;
        padding:7px 30px;
        background:#3d3;
        font-size:16px;
        color:#fff;
        font-weight:bold;
    }
    .button_login_wrapper {
        display: grid;
        grid-template: 1fr/1fr;
        grid-gap: 10px;
        justify-items: right;
    }
    .button_login_wrapper a {
        max-width: max-content;
        min-width: 100px;
        text-align: center;
        box-sizing: border-box;
    }
    @media screen and (min-width: 930px){
        .button_login_wrapper a {
            min-width: 205px;
        } }
    @media screen and (max-width: 576px){
        #inform {
            font-size: 10px;
            padding: 2px 10px;
        }
    }

    #usedesk-messenger textarea[name="message"] {
        font-size: 16px !important;
    }
</style>

{assign var="active_automation_fail" value=false}
{assign var="automation_fail_text" value=""}
{foreach $automation_fails as $item}
    {if $item->is_active}
        {assign var="active_automation_fail" value=true}
        {assign var="automation_fail_text" value=$item->text}
        {break}
    {/if}
{/foreach}

<script>
    window.serverTimeMsk = {$smarty.now * 1000};
</script>
<script>
    window.settings = window.settings || {literal}{}{/literal};
    {if $settings->site_warning_banner_config}
    window.settings.site_warning_banner_config = {$settings->site_warning_banner_config|json_encode};
    {/if}
    
    {if $active_automation_fail && ($module == 'MainPage' || $module == 'UserView')}
    window.settings.automation_fail = {
        enabled: true,
        message: {$automation_fail_text|json_encode},
        style: 'error',
        position: 'top',
        show_on_main_page: true,
        closeable: false,
        animation: 'slide',
        desktop: {
            background_color: '#F44336',
            text_color: '#ffffff',
            font_size: '16px',
            font_weight: 'normal',
            padding: '12px 20px',
            border_radius: '4px'
        },
        mobile: {
            background_color: '#F44336',
            text_color: '#ffffff',
            font_size: '14px',
            font_weight: 'normal',
            padding: '10px 15px',
            border_radius: '4px'
        }
    };
    {/if}
</script>
<script src="design/{$settings->theme|escape}/js/warning-banner.js"></script>

{if $is_developer}

    <div id="inform">
        <span>DEVELOPER MODE</span>
        <span>{$user->uid}</span>
    </div>
{/if}

{if $is_admin}
    <div id="admin_inform">
        <span>ADMIN MODE</span>
        {* <a id="close_inform" class="btn" href="javascript:void(0);">Понятно</a> *}
    </div>
{/if}

{if $is_looker}
    <div id="admin_inform">
        <span>ADMIN MODE</span>
        {* <a id="close_inform" class="btn" href="javascript:void(0);">Понятно</a> *}
    </div>
{/if}

{include 'layout/header.tpl'}

<div class="wrap">
    {$content}

    {if $pdn && !($is_admin || $is_looker)}
    <script defer src="design/boostra_mini_norm/js/pdn_excess.js" type="text/javascript"></script>
    <div id="modal_pdn_excess">
        <div id="pdn_excess">
            <div class="text-center">
                <div class="agreement">
                    <div class="agreement_header">
                        <div class="agreement_header_top">
                            Микрокредитная компания<br>
                            <u>Общество с ограниченной ответственностью "БУСТРА"</u>
                        </div>
                        <div class="agreement_header_bottom">
                            443099, Самарская область, г. Самара, ул. Фрунзе, дом 48, офис 10<br>
                            ИНН 6317102210 КПП 631701001 ОГРН 1146317004030
                        </div>
                    </div>
                    <h2>Уведомление</h2>
                    <p>
                        Уважаемый клиент {$user->lastname} {$user->firstname} {$user->patronymic}, уведомляем Вас, что при расчете показателя долговой нагрузки, величина вашего ПДН составила {$pdn}%.
                    </p>
                    <p>
                        В соответствии с пунктом 5 статьи 5.1 Федерального закона №353-ФЗ, о доведении до сведения заемщика - физического лица информации о значении показателя долговой нагрузки, рассчитанном в отношении него при принятии решения о предоставлении кредита (займа) или увеличении лимита кредитования», доводим до Вашего сведения, о наличии повышенного риска неисполнения Вами обязательств по потребительскому кредиту (займу), в связи с которым рассчитывался показатель долговой нагрузки, и риска применения за такое неисполнение штрафных санкций и возможности негативного влияния на условия кредитования.
                    </p>
                    <p>
                        Подписывая данное письмо вы подтверждаете ознакомление с уведомлением
                        в соответствии с  Федеральным законом от 29.12.2022 № 601-ФЗ
                    </p>
                    <div class="agreement_border">
                        Клиент: Ф.И.О.: {$user->lastname} {$user->firstname} {$user->patronymic}
                        Дата рождения: {$user->birth}
                        Паспорт серия {$passport_serial} № {$passport_number} Выдан
                        ОТДЕЛЕНИЕ<br>
                        {$user->passport_issued} от {$user->passport_date}
                        Адрес регистрации:
                        {$user->Regregion},
                        {$user->Regcity},
                        {$user->Regstreet},
                        д. {$user->Reghousing},
                        кв. {$user->Regroom}
                    </div>
                </div>

                <div class="modal_sms_footer">
                    <div class="accept_modal">
                        <button>Принять</button>
                    </div>
                    <div class="enter_sms" id="agreement-sms-block" style="display: none;">
                        <h3><b>Введите код из СМС</b></h3>
                        <div class="sms_input_row">
                            <input name="pdn_excess_sms_phone" type="text" hidden value="{$user->phone_mobile}">
                            <input name="pdn_excess_sms_code" autocomplete="one-time-code" inputmode="numeric" type="text" maxlength="4" placeholder="Код из смс" />
                        </div>
                        <div class="sms_repeat_row">
                            <span id="agreement-sms-timer"></span>
                            <a href="#" id="pdn_excess-sms-repeat" style="">Отправить повторно</a>
                        </div>
                        <div class="sms_error_row">
                            <div class="agreement_sms_error" id="pdn_wrong_code" style="display: none;">Введён неверный код</div>
                            <div class="agreement_sms_error" id="pdn_short_code" style="display: none;">Введён слишком короткий код</div>
                        </div>
                        <div class="verify_sms">
                            <button id="pdn_access_sms">Принять</button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

    <script type="text/javascript">
        $(document).ready(function () {
            $.magnificPopup.open({
                items: {
                    src: '#modal_pdn_excess'
                },
                type: 'inline',
                modal: true,
            });
        });
    </script>
{/if}

{if $has_unaccepted_agreement}
    <script defer src="design/boostra_mini_norm/js/unaccepted_agreement.js" type="text/javascript"></script>
    <div id="modal_unaccepted_agreement">
        <a onclick="$.magnificPopup.close();" class="close-modal" href="javascript:void();">
            <img src="design/{$settings->theme}/img/modal_icons/close_modal.png" width="17"/>
        </a>
        <div id="unaccepted_agreement">
            <div class="text-center">
                <p style="font-size: 24px"><b>Данные по вашему аккаунту указаны с ошибкой</b></p>
                <p style="font-size: 20px">Для исправления ошибки подпишите дополнительное соглашение к договору.</p>
                <div class="agreement">
                    <div class="agreement_header">
                        <div class="agreement_header_top">
                            Микрокредитная компания<br>
                            <u>Общество с ограниченной ответственностью "БУСТРА"</u>
                        </div>
                        <div class="agreement_header_bottom">
                            443099, Самарская область, г. Самара, ул. Фрунзе, дом 48, офис 10<br>
                            ИНН 6317102210 КПП 631701001 ОГРН 1146317004030
                        </div>
                    </div>
                    <div class="agreement_client">
                        Клиент {$user->lastname} {$user->firstname} {$user->patronymic}<br>
                        Адрес:
                        {if $user->Regindex}
                            {$user->Regindex},
                        {/if}
                        {if $user->Regcity_shorttype}
                            {$user->Regcity_shorttype}.
                        {else}
                            г.
                        {/if}
                        {$user->Regcity},
                        {if $user->Regstreet_shorttype}
                            {$user->Regstreet_shorttype}.
                        {else}
                            ул.
                        {/if}
                        {$user->Regstreet}
                        {if $user->Regroom}
                            , кв. {$user->Regroom}
                        {/if}
                    </div>
                    <h2>Уведомление</h2>
                    <p>
                        МКК ООО «Бустра» уведомляет Вас о смене Ваших персональных данных во внутренней системе Общества:
                    </p>
                    <p>
                        Прежние данные:
                        {$user->lastname} {$user->firstname} {$user->patronymic}, {$user->birth} г.р.,
                        {assign var="old_passport_serial" value=Helpers::splitPassportSerial($user->passport_serial)}
                        паспорт серия {$old_passport_serial["serial"]}
                        номер {$old_passport_serial["number"]},
                        дата выдачи {$user->passport_date} г., код подразделения {$user->subdivision_code},
                        выдан {$user->passport_issued}{if !empty($user->birth_place)}, место рождения {$user->birth_place}{/if}.
                        Номер телефона {$user->phone_mobile}.
                    </p>
                    <p>
                        Новые данные:
                        {$unaccepted_agreement->lastname} {$unaccepted_agreement->firstname} {$unaccepted_agreement->patronymic},
                        {$unaccepted_agreement->birth} г.р.,
                        {assign var="new_passport_serial" value=Helpers::splitPassportSerial($unaccepted_agreement->passport_serial)}
                        паспорт серия {$new_passport_serial["serial"]}
                        номер {$new_passport_serial["number"]},
                        дата выдачи {$unaccepted_agreement->passport_date} г., код подразделения {$unaccepted_agreement->subdivision_code},
                        выдан {$unaccepted_agreement->passport_issued}{if !empty($unaccepted_agreement->birth_place)}, место рождения {$unaccepted_agreement->birth_place}{/if}.
                        Номер телефона {$unaccepted_agreement->phone_mobile}.
                    </p>
                    <div class="agreement_footer">
                        <span>Директор МКК ООО «Бустра»</span>
                        <span>Вороной И. Ю.</span>
                    </div>
                </div>
            </div>
            <div class="agreement_acceptance">
                <button>Принять</button>
            </div>
            <div id="agreement-sms-block" style="display: none">
                <div class="sms-header">
                    <div>
                        <h3><b>Введите код из СМС</b></h3>
                    </div>
                    <div class="sms_code_wrapper">
                        <div>
                            <div>
                                <input name="agreement_sms_phone" type="text" hidden value="{$user->phone_mobile}">
                                <input name="agreement_sms_code" autocomplete="one-time-code" type="text" inputmode="numeric" maxlength="4" />
                                <a href="#" id="agreement-sms-repeat" style="display: none;">Отправить код повторно</a>
                            </div>
                            <span id="agreement-sms-timer"></span>
                        </div>
                        <div class="agreement_sms_error" id="agreement_wrong_code" style="display: none;">Введён неверный код</div>
                        <div class="agreement_sms_error" id="agreement_short_code" style="display: none;">Введён слишком короткий код</div>
                    </div>
                </div>
                <div class="sms-footer">
                    <button class="orange-btn" id="agreement_access_sms">Подтвердить</button>
                </div>
            </div>
        </div>
        <div id="accepted_agreement" style="display: none;">
            <div class="text-center">
                <h2>Данные подтверждены</h2>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function () {
            if (true) {
                $.magnificPopup.open({
                    items: {
                        src: '#modal_unaccepted_agreement'
                    },
                    type: 'inline',
                    showCloseBtn: true,
                    modal: true,
                });
            }
        });
    </script>
{/if}

{if $module|@array_search:['UserView']}
<section style="padding: 3rem; display: flex; align-items: center; justify-content: center;">
    <a class="btn btn-danger" style="text-decoration: none !important; display:flex; background: #dc3545; color: #fff; padding: 6px 12px; border: 1px; font-size: 1rem; line-height: 24px; border-radius: 0.375rem;" href="/complaint">
        <i class="bi bi-shield-fill-exclamation me-2" style="margin-right: 10px;"></i>
        <span>ПОЖАЛОВАТЬСЯ ФИНАНСОВОМУ ОМБУДСМЕНУ</span>
    </a>
</section>
{/if}

{if !in_array($module, [ 'AccountView', 'InitUserView'])}
  {if (in_array($module, []) || in_array($page->url, []) || (in_array($module, []) && !$user))}
  {include 'layout/landing_footer.tpl'}
  {elseif (in_array($module, [ 'RegisterView','NewOrderView', 'AccountContractView', 'AddDataView', 'Best2payCallback', 'UserExtraDocsView', 'UserAdditionalDocsView', 'SchedulePaymentsView', 'UserCreditRatingView', 'LoanHistoryView' , 'LoanView', 'AccountLoginView', 'UserView', 'UploadView', 'UserDocsView', 'UserTicketView', 'PaymentView','PartnersView', 'FaqView', 'ExceptionView', 'ComplaintView', 'AboutCompanyView']) || in_array($page->url, ['contacts', 'info', 'info_partners', '404']))}
  {include 'layout/lk_footer.tpl'}
  {else}
    <footer>
        <div class="footer-container">
            {if $module != 'AccountLoginView'}
                <ul class="footer-left">
                    {*<li><a href="/info">Информация</a></li>*}
                    <li><a href="/contacts">Связаться с нами</a></li>
                    {if $user}
                        <li><a href="/user/extra_docs">Прочее</a></li>
                        <li><a href="/user/additional_docs">Дополнительно</a></li>
                    {/if}
                    {*<li class=""><a href="info#info">Условия</a></li>*}
                    {if $user}
                        <li class=""><a href="{$lk_url}" >Личный кабинет</a></li>
                    {/if}
                    {if $all_orders->has_il_order}
                        <li class=""><a href="user/schedule_payments" >График платежей</a></li>
                    {/if}
                </ul>
            {/if}
            <div class="footer-right">
            <span class="copy">
                ООО «Финтех-Маркет» осуществляет деятельность в сфере IT
            </span>
            </div>
        </div>
        <div class="footer-left footer__contacts">
            {if $settings->header_email_block}
                <div>
                    <p style="display: block;text-align: center; margin: 0">Нажмите, чтобы направить обращение</p>
                    <a style="color: rgb(255, 119, 0) !important;text-align:center;display:block" href="mailto:{$settings->header_email|escape}">{$settings->header_email|escape}</a>
                    <small style="display: block;text-align: center;">Электронная почта <br> для обращений граждан/клиентов </small>
                    <p style="text-align: center;">
                        <a
                            href="/complaint"
                            {if $complaint_partner_href && !$same_page} target="_blank" onclick="clickHunter?.(14, window.complaint_partner_href);"{/if}
                            style="font-size: 11px; text-decoration: none; width: 100%; text-transform: uppercase;"
                        >Пожаловаться</a>
                    </p>
                </div>
            {/if}
            <div>
                <span style="display: block;text-align: center;">Возникли вопросы?</span>
                <div style="display: flex; align-items: center; justify-content: center;">
                    <div>Звони: </div>
                    <div style="display: flex; gap: 20px">
                        <a href="tel:88003333073">8 800 333 30 73</a>
                    </div>
                </div>
                <small style="display: block;text-align: center;">Клиентский сервис</small>
                <small style="display: block;text-align: center;">Время работы: круглосуточно</small>
            </div>
        </div>
    </footer>
    {/if}
{/if}

</div>

<div class="hidden">
    <div class="wait" id="gosuslugi_modal"></div>
</div>

{if $add_order_css_js}
    <div class="hidden">
        <div id="check" class="box">
            <h3>Проверка номера</h3>
            <p>На указанный Вами номер было отправлено<br/> SMS с кодом подтверждения.</p>

            <div id="confirm_error" style="color:#f00;margin-top:1rem"></div>

            <form action="#" method="post">
                <label>
                    <div class="plup">
                        <input type="text" autocomplete="one-time-code" inputmode="numeric" name="sign[code]" placeholder="Код из смс" required="" />
                    </div>
                    {*
                    <span class="time">00:00</span>
                    *}
                </label>
                <div>
                    <button class="medium">Подтвердить телефон</button>
                    <div class="repeat_sms">
                        {*<a href="#" class="new_sms">Отправить код еще раз</a>*}
                    </div>
                </div>
            </form>
        </div>
    </div>
{/if}

{if $is_developer && !$module|@array_search:['UserCreditDoctorInfoView', 'UserCreditDoctorView']}
    {include file='complain/complain_modal.tpl'}
{/if}

{if $user}
    {include 'modals/inactivity_modal.tpl'}
{/if}

{if $pixel}
    {$pixel}
{/if}

{if !$is_developer}

    <!-- Yandex.Metrika counter -->
{literal}
    <script type="text/javascript" >
        (function(m, e, t, r, i, k, a){m[i] = m[i] || function(){(m[i].a = m[i].a || []).push(arguments)};
        m[i].l = 1 * new Date(); k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)})
                (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
        ym(45594498, "init", {
        clickmap:true,
                trackLinks:true,
                accurateTrackBounce:true,
                webvisor:true,
                trackHash:true,
        {/literal}
        userParams: {
            {if $user}
                UserID: '{$user->id}',
                user_phone: '{$user->phone_mobile}',
                vip_status: false,
                child: 1,
                user_approved: {$user_approved},
            {/if}
                utm_source: '{$utm_source}',
                has_orders: {$has_orders},
                webmaster_id: '{$webmaster_id}',
                visit_id: '{$smarty.session.vid}',
           }
        {literal}
        });</script>
    <noscript><div><img src="https://mc.yandex.ru/watch/45594498" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->
{/literal}
{/if}
{if !$same_page}
<script lang="javascript">
    function clickHunter(source_id, background_href) {
        setTimeout(() => {
        {if $user}
            const href_append = '&p={$user->phone_mobile}&utm_source2={$user->utm_source}'
        {else}
            const href_append = '&utm_source2={$smarty.cookies.utm_source}'
        {/if}
            invokeShopview('bonon-background{$client_suffix}', background_href)
            sendMetric('reachGoal', 'decline_monitoring_' + source_id)
            window.location.href = background_href + href_append;
        }, 100)
    }
</script>
{/if}

<script src="design/{$settings->theme}/js/ion.rangeSlider.min.js" type="text/javascript"></script>
<script src="design/{$settings->theme}/js/response-nav.js" type="text/javascript"></script>
<script src="design/{$settings->theme}/js/calculate.js?v=1.624" type="text/javascript"></script>
<script src="design/{$settings->theme}/js/jquery.magnific-popup.min.js" type="text/javascript"></script>

{$smarty.capture.page_scripts}

{if $add_order_css_js}
    {* Скрипты раздела заявки *}
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.10" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.countdown.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/worksheet.validate.js?v=1.8.1" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.steps.js?v=1.03" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/plup.jquery.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.kladr.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/neworder.kladr.js?v=1.2" type="text/javascript"></script>
{if !$user->id}
{if !$order_js}
    <script src="design/{$settings->theme}/js/neworder.js?v=1.1" type="text/javascript"></script>
{else}
    <script src="design/{$settings->theme}/js/{$order_js}" type="text/javascript"></script>
{/if}
{/if}
{if !$step_js}
    <script src="design/{$settings->theme}/js/step.jquery.js?v=1.28" type="text/javascript"></script>
{else}
    <script src="design/{$settings->theme}/js/pts-tep.jquery.js?v=1.23" type="text/javascript"></script>
{/if}
{/if}

{* Скрипты раздела логин *}
{if $login_scripts}
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.10" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/login.app.js?v=2.719"></script>
{/if}

<script src="design/{$settings->theme}/js/b2p.app.js?v=1.07" type="text/javascript"></script>
<script src="/js/jquery.cookie.min.js" type="text/javascript"></script>
<script src="design/{$settings->theme}/js/metrics.js?v=1.007" type="text/javascript"></script>
<script src="design/{$settings->theme}/js/common.js?v=1.019" type="text/javascript"></script>
<script src="/js/functions.js?v=1.0002" type="text/javascript"></script>

<script type="text/javascript">
    let FormFiles = {};

            $(function () {
                $('#close_inform').click(function (e) {
                    e.preventDefault();
                    $('#inform').fadeOut('fast', function () {
                        $('#inform').remove();
                    });
                    document.cookie = "close_inform=1";
                });
            });

            $('#gosuslugi').click(function (e) {
                e.preventDefault();
                $('#gosuslugi_modal').html('').addClass('wait');
                $.magnificPopup.open({
                    items: {
                        src: '#gosuslugi_modal'
                    },
                    type: 'inline',
                    showCloseBtn: true
                });
                $.ajax({
                    url: 'ajax/gosuslugi.php',
                    success: function (resp) {
                        if (resp.error)
                            $('#gosuslugi_modal').removeClass('wait').html(resp.error);
                        else
                            $('#gosuslugi_modal').removeClass('wait').html(resp.success);
                    }
                })
            });
</script>

<script>
    window.usedeskConfig = {$usedesk_config_json};
</script>
<script src="design/{$settings->theme}/js/usedesk-validator.js?v=1.1"></script>

{if in_array($module, ["LoanView", "AccountView", "ShortRegisterView"])}
    <script type="text/javascript">
        let timeSpent = 0; // Время в секундах
        {literal}
            // Функция увеличения времени
            const timer = setInterval(() => {
                timeSpent++;
                if (timeSpent >= (60 * 3)) { // 60 секунд = 1 минута
                    clearInterval(timer);
                        {/literal}
                            const jsUsedeskUrl = {if $user->id}"https://lib.usedesk.ru/secure.usedesk.ru/widget_161404_58063.js"{else}"https://lib.usedesk.ru/secure.usedesk.ru/widget_161404_53920.js"{/if};
                        {literal}
                    const scriptElement = document.createElement('script');
                    scriptElement.src = jsUsedeskUrl;
                    scriptElement.onerror = function (error) {
                        console.log('Error new script file: ', error);
                    };
                    document.body.appendChild(scriptElement);
                }
            }, 1000);
        {/literal}
    </script>
{else}
    {if $user->id}
        <script async src="https://lib.usedesk.ru/secure.usedesk.ru/widget_161404_58063.js"></script>
    {else}
        <script async src="https://lib.usedesk.ru/secure.usedesk.ru/widget_161404_53920.js"></script>
    {/if}
{/if}
{if isset($debtInDays)}
    <script>
        navigator.serviceWorker.register('/design/{$settings->theme|escape}/js/sw.js').then(function(registration) {
            window.registration = registration;
        }).catch(function(err) {
            console.log('ServiceWorker registration failed: ', err);
        });

        window.applicationServerKey = '{$vapidPublicKey}';

        try {
            window.debtInDays = parseInt('{$debtInDays}');
            if (isNaN(window.debtInDays)) {
                window.debtInDays = null;
            }
        } catch (e) {
            window.debtInDays = null;
        }
    </script>
    <script src="design/{$settings->theme|escape}/js/notifications-subscribe.js" type="text/javascript" async></script>
{/if}
{if isset($debtInDays) && $debtInDays > 0}
    {if $debtInDays > 0}
        <link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/contact-me-notice.css?v=1.01"/>
        <script src="design/{$settings->theme|escape}/js/contact-me-notice.js" type="text/javascript" async></script>
    {/if}
{/if}

{*Выгружаем JS код в конец страницы*}
{foreach $footer_page_scripts as $footer_page_script}
    {$footer_page_script}
{/foreach}

{literal}
    <script type="text/template" id="preview-image-template">
        <div id="preview-image-%id%">
            <div>
                <img src="%filename%" alt="boostra" />
                <a class="delete-preview" href="javascript:void(0)" onclick="$('#preview-image-%id%').remove()">&#10006;</a>
            </div>
        </div>
    </script>
    <style>
        [id^="preview-image-"] > div {
            position: relative;
            max-width: max-content;
            margin: 10px auto;
        }

        [id^="preview-image-"] > div img {
            max-width: 240px;
            width: 240px;
        }

        [id^="preview-image-"] > div a {
            position: absolute;
            width: 25px;
            height: 25px;
            color: red;
            right: 0;
            top: 0;
        }
    </style>
{/literal}
{if isset($debtInDays) && $debtInDays > 0 && isset($notice_contact_me_available) && $notice_contact_me_available}
    <div id="contact-me-notice">
        <p id="contact-me-text">В Личном кабинете трудности при оплате? - Мы готовы помочь с этим</p>
        <p id="contact-me-wait" style="display: none">Мы свяжемся с Вами в скором времени</p>
        <button id="contact-me-button">Свяжитесь со мной</button>
        <button id="close-notice-button">&times;</button>
    </div>
{/if}
<div
    id="pixel2"
    data-pid="{$pixelConfig->pixel_pid|default:''}"
    data-uid="{$pixelConfig->pixel_uid|default:''}">
</div>

</body>
</html>

