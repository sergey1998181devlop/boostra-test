<!DOCTYPE html>
{*
todo заменить тему после окончания редизайна всего сайта (orange_theme на {$settings->theme})
Общий вид страницы
Этот шаблон отвечает за общий вид страниц без центрального блока.
*}
{if !$is_landing}
<html>
<head>
    <base href="{$config->root_url}/"/>
    <title>{$meta_title|escape}</title>

    {* Метатеги *}
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="title" content="{$meta_title2|escape}"/>
    <meta name="description" content="{$meta_description|escape}"/>
    <meta name="keywords" content="{$meta_keywords|escape}"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {* Канонический адрес страницы *}
    {if isset($canonical)}
        <link rel="canonical" href="{$config->root_url}{$canonical}"/>{/if}

    <link rel="shortcut icon" type="image/ico" href="design/{$settings->theme|escape}/img/favicon.ico">

    <meta property="og:locale" content="ru_RU"/>
    <meta property="og:type" content="website"/>
    <meta property="og:title" content=""/>
    <meta property="og:url" content="http://boostra.ru"/>
    <meta property="og:site_name" content="boostra"/>
    <meta property="og:image" content="design/{$settings->theme|escape}/img/favicon.png"/>

    <meta name="twitter:card" content="summary_large_image"/>
    <meta name="twitter:description" content="{$meta_description|escape}"/>
    <meta name="twitter:title" content=""/>
    <meta name="twitter:image" content="design/{$settings->theme|escape}/img/favicon192x192.png"/>

    <link rel="icon" href="design/{$settings->theme|escape}/img/favicon-32x32.png" sizes="32x32"/>
    <link rel="icon" href="design/{$settings->theme|escape}/img/favicon192x192.png" sizes="192x192"/>
    <link rel="apple-touch-icon-precomposed" href="design/{$settings->theme|escape}/img/favicon180x180.png"/>
    <meta name="msapplication-TileImage" content="design/{$settings->theme|escape}/img/favicon270x270.png"/>
    <link rel="image_src" href="design/{$settings->theme|escape}/img/favicon.png"/>
    <meta content="design/{$settings->theme|escape}/img/social.png" name="og:image" property="og:image">

    <link rel="stylesheet" type="text/css" href="design/orange_theme/css/ion.rangeSlider.css?v=1.0001"/>
    {if $add_order_css_js}
        <link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/jquery.kladr.min.css?v=1.12"/>
    {/if}

    <link rel="stylesheet" type="text/css"
          href="design/orange_theme/css/bootstrap/bootstrap-icons-1.9.1/bootstrap-icons.css"/>
    <link rel="stylesheet" type="text/css" href="design/orange_theme/css/bootstrap/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="design/orange_theme/css/style.css?v=1.022"/>
    <link rel="stylesheet" type="text/css" href="design/orange_theme/css/media.css?v=1.006"/>
    <link rel="stylesheet" type="text/css" href="design/orange_theme/css/modal.css?v=1.00"/>
    <link rel="stylesheet" type="text/css" href="design/orange_theme/css/magnific-popup.css?v=1.00"/>

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
    <link
            href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
            rel="stylesheet">

    <script defer src="design/boostra_mini_norm/js/email_feedback.js?v=1.02"></script>

    <script src="design/{$settings->theme}/js/jquery-2.1.3.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.magnific-popup.min.js" type="text/javascript"></script>

    <!--script src="https://cfv4.com/landings.js"></script-->

    <meta name="cmsmagazine" content="6f3ef3c26272e3290aa0580d7c8d86ce"/>

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

    <!-- Feedback form captcha -->
    <script>
      document.addEventListener('DOMContentLoaded', (event) => {

        // Render
        window.recaptchaOnloadCallback = function () {
          if ($('#recaptcha_feedback').length > 0) {
            grecaptcha.render('recaptcha_feedback', { 'sitekey': "{$settings->apikeys['recaptcha']['key']}" });
          }
        };
      });
    </script>
    <script src='https://www.google.com/recaptcha/api.js?onload=recaptchaOnloadCallback&render=explicit' async
            defer></script>

    {if $module == 'MainView'}
        {include 'micro_markup.tpl'}
        <script>
          history.pushState(-1, null);
          if (window.history && history.pushState) {
            window.addEventListener('load', function () {
              history.pushState(-1, null);
              history.pushState(0, null);
              history.pushState(1, null);
              history.go(-1);
              this.addEventListener('popstate', function (event, state) {
                if (event.state == -1) {
                  window.location.href = 'https://365zaim.ru/9701/';
                }
              }, false);
            }, false);
          }
        </script>
    {/if}
    {literal}
        <script type="text/javascript">!function () {
            var t = document.createElement("script");
            t.type = "text/javascript", t.async = !0, t.src = 'https://vk.com/js/api/openapi.js?169', t.onload = function () {
              VK.Retargeting.Init("VK-RTRG-1440253-hcsa0"), VK.Retargeting.Hit()
            }, document.head.appendChild(t)
          }();</script>
        <noscript><img src="https://vk.com/rtrg?p=VK-RTRG-1440253-hcsa0" style="position:fixed; left:-999px;" alt=""/>
        </noscript>
    {/literal}
</head>
<body {if $body_class}class="{$body_class}" {/if}>
{include 'html_blocks/header.tpl' module=$module}
{if !$is_developer}
    {literal}
    <!-- Top.Mail.Ru counter -->
    <script type="text/javascript">
      var _tmr = window._tmr || (window._tmr = []);
      _tmr.push({id: "3621257", type: "pageView", start: (new Date()).getTime()});
      (function (d, w, id) {
        if (d.getElementById(id)) return;
        var ts = d.createElement("script"); ts.type = "text/javascript"; ts.async = true; ts.id = id;
        ts.src = "https://top-fwz1.mail.ru/js/code.js";
        var f = function () {var s = d.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ts, s);};
        if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); }
      })(document, window, "tmr-code");
    </script>
    <noscript><div><img src="https://top-fwz1.mail.ru/counter?id=3621257;js=na" style="position:absolute;left:-9999px;" alt="Top.Mail.Ru" /></div></noscript>
    <!-- /Top.Mail.Ru counter -->
    {/literal}
{/if}

<div class="container wrap">
    {$content}
</div>

{if $module == 'MainView'}
    {include 'html_blocks/footer.tpl'}
{else}
    {include 'html_blocks/footer_inner_page.tpl'}
{/if}

{if !$user}
    {include 'modals/modal_login_by_phone.tpl'}
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
<div id="contact-me-notice">
    <p id="contact-me-text">В Личном кабинете трудности при оплате? - Мы готовы помочь с этим</p>
    <p id="contact-me-wait" style="display: none">Мы свяжемся с Вами в скором времени</p>
    <button id="contact-me-button">Свяжитесь со мной</button>
    <button id="close-notice-button">&times;</button>
</div>
{/if}
</body>
{if !$is_developer}
    <!-- Yandex.Metrika counter -->
{literal}
    <script type="text/javascript">
      (function (m, e, t, r, i, k, a) {
        m[i] = m[i] || function () {
          (m[i].a = m[i].a || []).push(arguments)
        };
        m[i].l = 1 * new Date();
        k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
      })
      (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
      ym(45594498, "init", {
        clickmap: true,
        trackLinks: true,
        accurateTrackBounce: true,
        webvisor: true,
        trackHash: true,
          {/literal}
        userParams: {
            {if $user}
          UserID: '{$user->phone_mobile}',
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
    <noscript>
        <div><img src="https://mc.yandex.ru/watch/45594498" style="position:absolute; left:-9999px;" alt=""/></div>
    </noscript>
    <!-- /Yandex.Metrika counter -->
{/literal}
{/if}

<script src="design/orange_theme/js/ion.rangeSlider.min.js" type="text/javascript"></script>

{if $add_order_css_js}
    {* Скрипты раздела заявки *}
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.10" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.countdown.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/worksheet.validate.js?v=1.7.5" type="text/javascript"></script>
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
    <script src="design/{$settings->theme}/js/step.jquery.js?v=1.25" type="text/javascript"></script>
{else}
    <script src="design/{$settings->theme}/js/pts-tep.jquery.js?v=1.23" type="text/javascript"></script>
{/if}
{/if}

{* Скрипты раздела логин *}
{if $login_scripts}
    <script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.10" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/login.app.js?v=2.494" type="text/javascript"></script>
{/if}

    <script src="design/{$settings->theme}/js/b2p.app.js?v=1.001" type="text/javascript"></script>
    <script src="/js/jquery.cookie.min.js" type="text/javascript"></script>
    <script src="design/{$settings->theme}/js/metrics.js?v=1.006" type="text/javascript"></script>
    <script src="design/orange_theme/js/common.js?v=1.0016" type="text/javascript"></script>
    <script src="/js/functions.js?v=1.0001" type="text/javascript"></script>

<script src="design/orange_theme/js/bootstrap/bootstrap.bundle.min.js" type="text/javascript"></script>

{$smarty.capture.page_scripts}
</html>
{else}
<html>
<head>
    <base href="{$config->root_url}/"/>
    <title>{$meta_title|escape}</title>

    {* Метатеги *}
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="title" content="{$meta_title2|escape}"/>
    <meta name="description" content="{$meta_description|escape}"/>
    <meta name="keywords" content="{$meta_keywords|escape}"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    {* Канонический адрес страницы *}
    {if isset($canonical)}
        <link rel="canonical" href="{$config->root_url}{$canonical}"/>{/if}

    <link rel="shortcut icon" type="image/ico" href="design/{$settings->theme|escape}/img/favicon.ico">

    <meta property="og:locale" content="ru_RU"/>
    <meta property="og:type" content="website"/>
    <meta property="og:title" content=""/>
    <meta property="og:url" content="http://boostra.ru"/>
    <meta property="og:site_name" content="boostra"/>
    <meta property="og:image" content="design/{$settings->theme|escape}/img/favicon.png"/>

    <meta name="twitter:card" content="summary_large_image"/>
    <meta name="twitter:description" content="{$meta_description|escape}"/>
    <meta name="twitter:title" content=""/>
    <meta name="twitter:image" content="design/{$settings->theme|escape}/img/favicon192x192.png"/>

    <link rel="icon" href="design/{$settings->theme|escape}/img/favicon-32x32.png" sizes="32x32"/>
    <link rel="icon" href="design/{$settings->theme|escape}/img/favicon192x192.png" sizes="192x192"/>
    <link rel="apple-touch-icon-precomposed" href="design/{$settings->theme|escape}/img/favicon180x180.png"/>
    <meta name="msapplication-TileImage" content="design/{$settings->theme|escape}/img/favicon270x270.png"/>
    <link rel="image_src" href="design/{$settings->theme|escape}/img/favicon.png"/>
    <meta content="design/{$settings->theme|escape}/img/social.png" name="og:image" property="og:image">

    <script src="design/{$settings->theme}/js/jquery-2.1.3.min.js" type="text/javascript"></script>

    {if $module == "MainView"}
        <script src="https://dmp.one/sync?stock_key=0ba79d494948d74fe07105c451d79093" async referrerpolicy="no-referrer-when-downgrade" charset="UTF-8"></script>
        {include 'micro_markup.tpl'}
    {/if}

    {if !$is_developer}
        <!-- Yandex.Metrika counter -->
    {literal}
        <script src="design/boostra_mini_norm/js/mobile_download_banners/mobile_banners.js?v=1.002" type="text/javascript"></script>
        <script type="text/javascript">
          (function (m, e, t, r, i, k, a) {
            m[i] = m[i] || function () {
              (m[i].a = m[i].a || []).push(arguments)
            };
            m[i].l = 1 * new Date();
            k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
          })
          (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym");
          ym(45594498, "init", {
            clickmap: true,
            trackLinks: true,
            accurateTrackBounce: true,
            webvisor: true,
            trackHash: true,
              {/literal}
            userParams: {
                {if $user}
                  UserID: '{$user->phone_mobile}',
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
        <noscript>
            <div><img src="https://mc.yandex.ru/watch/45594498" style="position:absolute; left:-9999px;" alt=""/></div>
        </noscript>
        <!-- /Yandex.Metrika counter -->
    {/literal}
    {/if}

    <script>
        {if $is_developer}
            const is_developer = 1;
            console.info('is developer');
        {else}
            const is_developer = 0;
        {/if}
    </script>

    <script src="design/{$settings->theme}/js/metrics.js?v=1.006" type="text/javascript"></script>
    <!-- new design -->
    <meta charset="UTF-8"/>
    <link href="design/orange_theme/img/landing/logo.svg" rel="icon" type="image/svg+xml"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta content="ie=edge" http-equiv="X-UA-Compatible"/>
    <meta content="telephone=no" http-equiv="format-detection"/>
    <title>Vite App</title>
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin href="https://fonts.gstatic.com" rel="preconnect">
    <link
            href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
            rel="stylesheet">

    <link href="design/orange_theme/css/landing/splide.min.css" rel="stylesheet">
    <script type="module" src="design/orange_theme/js/landing/index-B5oBZb9-.js?v=20251210"></script>
    <link rel="stylesheet" href="design/orange_theme/css/landing/index-BWCMBxJe.css?v=1.1012">
    <style>
        .footer__disclaimers .partners-section {
            margin-top: 54px;
            font-size: 10px;
            font-weight: 400;
            line-height: 18px;
            letter-spacing: 0.2px;
            color: #6f7985;
        }

        .footer__disclaimers .partners-section .title {
            font-size: 10px;
            font-weight: 700;
            line-height: 18px;
            letter-spacing: 0.2px;
            margin-bottom: 20px;
        }
        #inform {
            position:relative;
            padding:7px 30px;
            background:#f00;
            font-size:14px;
            color:#fff;
            font-weight:bold;
            display:block;
            justify-content: space-between;
            text-align:center;
        }

        @media screen and (max-width: 576px){
            #inform {
                font-size: 10px;
                padding: 2px 10px;
            }
        }
    </style>
</head>
<body>
    {$content}
    {if !$is_developer}
        {literal}
        <!-- Top.Mail.Ru counter -->
        <script type="text/javascript">
          var _tmr = window._tmr || (window._tmr = []);
          _tmr.push({id: "3621257", type: "pageView", start: (new Date()).getTime()});
          (function (d, w, id) {
            if (d.getElementById(id)) return;
            var ts = d.createElement("script"); ts.type = "text/javascript"; ts.async = true; ts.id = id;
            ts.src = "https://top-fwz1.mail.ru/js/code.js";
            var f = function () {var s = d.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ts, s);};
            if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); }
          })(document, window, "tmr-code");
        </script>
        <noscript><div><img src="https://top-fwz1.mail.ru/counter?id=3621257;js=na" style="position:absolute;left:-9999px;" alt="Top.Mail.Ru" /></div></noscript>
        <!-- /Top.Mail.Ru counter -->
        {/literal}
    {/if}
<body>
</html>
{/if}
