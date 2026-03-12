{assign var="active_automation_fail" value=false}
{assign var="automation_fail_text" value=""}
{foreach $automation_fails as $item}
    {if $item->is_active}
        {assign var="active_automation_fail" value=true}
        {assign var="automation_fail_text" value=$item->text}
        {break}
    {/if}
{/foreach}
<style>
    #usedesk-messenger textarea[name="message"] {
        font-size: 16px !important;
    }
</style>
<!-- Custom usedesk operator avatar -->
{if $usedesk_config.operatorAvatar}
    <link rel="stylesheet" type="text/css"
          href="design/{$settings->theme|escape}/css/usedesk-customizations.css?v=1.04"/>
    <style>
        :root {
            --usedesk-operator-avatar: url('{$usedesk_config.operatorAvatar|escape:'quotes'}');
        }

        /* Агрессивное переопределение для Usedesk аватаров */
        .uw__avatar,
        .uw__message-avatar,
        .uw__operator-avatar {
            background-image: url('{$usedesk_config.operatorAvatar|escape:'quotes'}') !important;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
        }
    </style>
{/if}
<script>
    window.usedeskConfig = {$usedesk_config_json};
</script>
<script src="design/{$settings->theme}/js/usedesk-validator.js?v=1.1"></script>

<script>
    window.serverTimeMsk = {$smarty.now * 1000};
</script>
<script>
    window.settings = window.settings || {literal}{}{/literal};
    {if $settings->site_warning_banner_config}
    window.settings.site_warning_banner_config = {$settings->site_warning_banner_config|json_encode};
    {/if}
    
    {if $active_automation_fail}
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

<div class="container">
    <article class="mobileBanner">
        <a href='https://apimp.boostra.ru/get_app.php' target='_blank' class="mobileBanner__wrapperLink">
            <div class="mobileBanner__leftSideWrapper">
                <button class="mobileBanner__closeBtn" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <g clip-path="url(#clip0_2070_224)">
                            <rect x="14.3643" y="0.22168" width="2" height="20" transform="rotate(45 14.3643 0.22168)" fill="white"/>
                            <rect x="15.7783" y="14.3643" width="2" height="20" transform="rotate(135 15.7783 14.3643)" fill="white"/>
                        </g>
                        <defs>
                            <clipPath id="clip0_2070_224">
                                <rect width="16" height="16" fill="white"/>
                            </clipPath>
                        </defs>
                    </svg>
                </button>
                <svg xmlns="http://www.w3.org/2000/svg" width="57" height="57" viewBox="0 0 57 57" fill="none">
                    <rect width="57" height="57" rx="14" fill="#E2E2E2"/>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M19.8681 6.56041L13.9749 35.9856C13.5496 37.9351 13.2459 39.8236 13.2459 41.8341C13.1851 47.0733 16.5874 50.4849 24.4855 50.4849C38.459 50.4849 44.7168 39.4581 44.7168 29.5279C44.7168 24.1668 41.8005 19.1712 35.7251 19.1712C32.2013 19.1712 29.0421 20.6333 27.0372 22.6437H26.9157L30.2572 6.56041H19.8681ZM30.6824 27.5784C28.1307 27.5784 25.5791 30.1371 24.7285 34.5844L24.3032 36.7166C24.2828 36.826 24.2616 36.9361 24.2402 37.047C24.071 37.9253 23.8916 38.8569 23.9994 39.8846C24.0602 41.7122 25.1538 42.687 26.7334 42.687C30.6824 42.687 33.8417 36.473 33.8417 31.8429C33.8417 29.2842 32.8089 27.5784 30.6824 27.5784Z" fill="#282735"/>
                </svg>
                <p class="mobileBanner__text">Открыть в приложении</p>
            </div>
            <img class="mobileBanner__woman" src="design/orange_theme/img/landing/mobileBanner-girl.png" />
            <p class="mobileBanner__downloadBtn">Скачать</p>
        </a>
    </article>
    <section class="root">
        <header class="header">
            <aside class="header-nav-overlay"></aside>
            <div class="header__wrap">
                <div class="container">
                    <div class="header__inner">
                        <div class="header__logo">
                            <a class="logo" href="">
                                <img alt=""
                                     loading="lazy"
                                     src="design/orange_theme/img/landing/logo.svg">
                            </a>
                            <p class="logo-additional-text-delimiter">/</p>
                            <p class="logo-additional-text" href="">Каталог финансовых <br> продуктов</p>
                        </div>

                        {include 'html_blocks/mobile_nav_menu.tpl'}

                        <a
                            class="header__button"
                            href="/user/login"
                            {if !$same_page && $mobile_browser}
                                {if !$is_developer} onclick="ym(45594498,'reachGoal','click_voity'); clickHunter?.(2);"{/if}
                                target="_blank"
                            {else}
                                {if !$is_developer} onclick="ym(45594498,'reachGoal','click_voity');"{/if}
                            {/if}
                            style="text-decoration: none">
                            <button class="loginBtn">
                        <span class="loginBtn__icon">
                  <svg fill="none" height="28" viewBox="0 0 28 28" width="28"
                       xmlns="http://www.w3.org/2000/svg">
                    <path
                            d="M15.0795 21.875C10.6863 21.875 7.125 18.3494 7.125 14C7.125 9.65064 10.6863 6.125 15.0795 6.125C16.3146 6.12408 17.5329 6.40832 18.6376 6.95514C19.7422 7.50195 20.7029 8.29628 21.4432 9.275H19.2875C18.3689 8.47314 17.2362 7.95071 16.0251 7.7704C14.8141 7.5901 13.5762 7.75958 12.46 8.25851C11.3439 8.75743 10.3968 9.56461 9.73256 10.5832C9.06829 11.6018 8.71501 12.7885 8.71511 14.0009C8.71521 15.2133 9.06869 16.3999 9.73313 17.4184C10.3976 18.4369 11.3447 19.2439 12.461 19.7426C13.5772 20.2414 14.8151 20.4106 16.0262 20.2301C17.2372 20.0496 18.3699 19.527 19.2883 18.725H21.444C20.7036 19.7038 19.7428 20.4982 18.638 21.045C17.5332 21.5919 16.3147 21.876 15.0795 21.875ZM20.6477 17.15V14.7875H14.2841V13.2125H20.6477V10.85L24.625 14L20.6477 17.15Z"

                            fill="#1E262E"/></svg></span>
                                <span class="loginBtn__text"
                                      onclick="sendMetric('reachGoal', 'popal_v_lk_is_vhoda')">Войти</span>
                            </button>
                        </a>

                        <button class="burger header__burger" type="button">
                            <div class="burger__icon">
                                <div class="burger__icon_open">
                                    <svg fill="none" height="10" viewBox="0 0 27 10" width="27"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <rect fill="#1E262E" height="2" rx="1" width="27" y="0.5"/>
                                        <rect fill="#1E262E" height="2" rx="1" width="20" y="7.5"/>
                                    </svg>
                                </div>
                                <div class="burger__icon_close">
                                    <svg fill="none" height="12" viewBox="0 0 12 12" width="12"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path
                                                d="M6 4.66688L10.6669 0L12 1.33312L7.33312 6L12 10.6669L10.6659 12L5.99906 7.33312L1.33312 12L0 10.6659L4.66688 5.99906L0 1.33218L1.33312 0.000942735L6 4.66688Z"
                                                fill="#02113B"/>
                                    </svg>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </header>
        <section class="hero">
            <img class="illustration" id="illustration_up" src="design/orange_theme/img/landing/illustration.png"/>
            <img class="illustration" id="illustration_down" src="design/orange_theme/img/landing/illustration_under.png"/>
            <div class="container">
                <div class="hero__inner">
                    <div class="hero__content">
                        <div class="hero__tags tags">
                            <span class="tag tag--primary">Деньги у Вас в <span class="js-date"></span></span>
                            <span class="tag tag--secondary">Займы под 0%</span>
                        </div>
                        <h1 class="hero__title">
                            Онлайн займы на&nbsp;карту до&nbsp;30&nbsp;000 рублей за 5&nbsp;минут
                        </h1>
                        <div class="hero__text">
                            <ul>
                                <li>— У нас уже {$count_of_clients}&nbsp;клиентов
                                <li>— Одни из лидеров по выдаче займов в России
                                <li>— Мгновенное зачисление денежных средств
                            </ul>
                        </div>
                        <div class="hero__footer">
                            <section class="payment-methods">
                                <div class="payment-methods__item">
                                    <img alt="visa" src="design/orange_theme/img/landing/visa-payment.svg"
                                         loading="lazy">
                                </div>
                                <div class="payment-methods__item">
                                    <img alt="mastercard" src="design/orange_theme/img/landing/mastercard-payment.svg"
                                         loading="lazy">
                                </div>
                                <div class="payment-methods__item">
                                    <img alt="mir" src="design/orange_theme/img/landing/mir-payment.svg" loading="lazy">
                                </div>
                                <div class="payment-methods__item">
                                    <img alt="best-2-pay" src="design/orange_theme/img/landing/best-2-pay-payment.svg"
                                         loading="lazy">
                                </div>
                            </section>
                        </div>
                    </div>
                    {include 'calculator/calculator.tpl'}
                </div>
            </div>
        </section>

        {include './mobile_banners/link_banner.tpl' banner_img_android="/design//boostra_mini_norm/assets/image/banner_rustore_img.png" banner_img_ios="/design//boostra_mini_norm/assets/image/banner_ios_img.png" banner_link='https://apimp.boostra.ru/get_app.php?slot=b2'}

        <main class="tail">
            {if $is_organic}
                {include file='main_page/partners_data.tpl'}
                <section class="section" id="about-as-section">
                    <div class="container">
                        <div class="section__inner">
                            <h2 class="section__title">О нас пишут</h2>
                            <div class="section__content">
                                <div class="article-wrapper">
                                    {foreach $about_as_pages as $page}
                                        <div class="article-card">
                                            <img src="{$page->logo}" alt="Изображение статьи">
                                            <div class="content">
                                                <div>
                                                    <h3>{$page->title}</h3>
                                                    {if $page->description}
                                                        <p>{$page->description}</p>
                                                    {/if}
                                                </div>
                                                <div class="article-btn">
                                                    <a href="{$page->url}" target="_blank">Читать далее</a>
                                                    <div class="likes">
                                                        <!-- SVG-иконка "палец вверх" -->
                                                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M1 21h4V9H1v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L14.17 1 7.59 7.59C7.22 7.95 7 8.45 7 9v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2z"/>
                                                        </svg>
                                                        <span>{$page->total_like}</span> <!-- Количество лайков -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            {/if}
            <section class="section">
                <div class="container">
                    <div class="section__inner">
                        <h2 class="section__title">Как получить заём?</h2>
                        <div class="section__content">
                            <section class="splide splide--mobile-overflowed" id="steps-slider">
                                <div class="splide__track">
                                    <div class="splide__list steps">
                                        <div class="splide__slide step-card">

                                            <div class="step-card__tags">
                                                <div class="tags">
                                                    <span class="tag tag--primary tag--small">Шаг 1</span>
                                                    <span class="tag tag--ghost tag--small">за 3-4 минуты</span>
                                                </div>
                                            </div>
                                            <p class="step-card__title">Оформление заявки</p>
                                            <p class="step-card__text">Заполнение займет не более 10 минут</p>
                                            <img alt="" class="step-card__pic"
                                                 src="design/orange_theme/img/landing/step-1.webp" loading="lazy">

                                        </div>

                                        <div class="splide__slide step-card">
                                            <div class="step-card__tags">
                                                <div class="tags">
                                                    <span class="tag tag--primary tag--small">Шаг 2</span>
                                                    <span class="tag tag--ghost tag--small">за 1 минуту</span>
                                                </div>
                                            </div>
                                            <p class="step-card__title">Дождитесь ответа</p>
                                            <p class="step-card__text">Ответим на заявку в течение 5 минут</p>
                                            <img alt="" class="step-card__pic"
                                                 src="design/orange_theme/img/landing/step-2.webp" loading="lazy">

                                        </div>
                                        <div class="splide__slide step-card">
                                            <div class="step-card__tags">
                                                <div class="tags">
                                                    <span class="tag tag--primary tag--small">Шаг 3</span>
                                                    <span class="tag tag--ghost tag--small">за 30 секунд</span>
                                                </div>
                                            </div>
                                            <p class="step-card__title">Мгновенный перевод</p>
                                            <p class="step-card__text">На банковскую карту</p>
                                            <img alt="" class="step-card__pic"
                                                 src="design/orange_theme/img/landing/step-3.webp" loading="lazy">

                                        </div>
                                        <div class="splide__slide step-card">
                                            <div class="step-card__tags">
                                                <div class="tags">
                                                    <span class="tag tag--primary tag--small">Шаг 4</span>
                                                    <span class="tag tag--ghost tag--small">за 3 минуты</span>
                                                </div>
                                            </div>
                                            <p class="step-card__title">Погасите заём</p>
                                            <p class="step-card__text">Любым удобным для Вас способом</p>
                                            <img alt="" class="step-card__pic"
                                                 src="design/orange_theme/img/landing/step-4.webp" loading="lazy">
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="container">
                    <div class="section__inner">
                        <h2 class="section__title">Нам доверяют более {$count_of_clients} клиентов</h2>
                        <div class="section__content">
                            <section class="advantages">
                                                           <div class="advantages-card">
                                                               <div class="advantages-card__icon">
                                                                   <img alt="" loading="lazy" src="design/orange_theme/img/landing/advantage-1.png">
                                                               </div>
                                                               <div class="advantages-card__content">
                                                                   <p class="advantages-card__title">Первый заём под 0% <br/> при соблюдении условий</p>
                                                                   <p class="advantages-card__text">
                                                                       Воспользуйтесь уникальным предложением и решите свои финансовые проблемы без дополнительных затрат
                                                                   </p>
                                                               </div>
                                                           </div>

                                <div class="advantages-card">
                                    <div class="advantages-card__icon">
                                        <img alt="" loading="lazy"
                                             src="design/orange_theme/img/landing/advantage-2.webp">
                                    </div>
                                    <div class="advantages-card__content">
                                        <p class="advantages-card__title">Простое оформление</p>
                                        <p class="advantages-card__text">
                                            Чтобы взять заём на карту, достаточно заполнить форму
                                        </p>
                                    </div>
                                </div>

                                <div class="advantages-card">
                                    <div class="advantages-card__icon">
                                        <img alt="" loading="lazy"
                                             src="design/orange_theme/img/landing/advantage-3.webp">
                                    </div>
                                    <div class="advantages-card__content">
                                        <p class="advantages-card__title">Мгновенное получение</p>
                                        <p class="advantages-card__text">Денежных средств <br>на банковскую карту</p>
                                    </div>
                                </div>

                                <div class="advantages-card">
                                    <div class="advantages-card__icon">
                                        <img alt="" loading="lazy"
                                             src="design/orange_theme/img/landing/advantage-4.webp">
                                    </div>
                                    <div class="advantages-card__content">
                                        <p class="advantages-card__title">Поддержка 24/7</p>
                                        <p class="advantages-card__text">
                                            Мы предлагаем поддержку, готовую ответить на все ваши вопросы 24/7
                                        </p>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </section>

            {include file='main_page/partners_trust_us.tpl'}

            <section class="section">
                <div class="container">
                    <section class="offer">
                        <span class="offer__tag tag tag--secondary">Займы под 0% при соблюдении условий</span>
                        <p class="offer__title">Деньги у Вас через 5&nbsp;минут</p>
                        <p class="offer__text">Быстрое рассмотрение заявки!</p>
                        <button class="button button--primary" onclick="submitOrder()">Получить деньги</button>
                        <picture>
                            <source media="(min-width: 991px)" srcset="design/orange_theme/img/landing/offer-pic.png">
                            <img alt="" class="offer__pic" loading="lazy"
                                 src="design/orange_theme/img/landing/offer-pic-m.webp">
                        </picture>
                    </section>
                </div>
            </section>

            <section class="section">
                <div class="container">
                    <div class="section__inner">
                        <h2 class="section__title">Требования к заёмщику</h2>
                        <div class="section__content">
                            <section class="requirements">
                                <div class="requirements-card">
                                    <div class="requirements-card__icon">
                                        <img alt="" loading="lazy"
                                             src="design/orange_theme/img/landing/requirements-1.webp">
                                    </div>
                                    <p class="requirements-card__title">Возраст заёмщика <br>от 18 лет</p>
                                </div>

                                <div class="requirements-card">
                                    <div class="requirements-card__icon">
                                        <img alt="" loading="lazy"
                                             src="design/orange_theme/img/landing/requirements-2.webp">
                                    </div>
                                    <p class="requirements-card__title">Активный <br>номер телефона</p>
                                </div>

                                <div class="requirements-card">
                                    <div class="requirements-card__icon">
                                        <img alt="" loading="lazy"
                                             src="design/orange_theme/img/landing/requirements-3.webp">
                                    </div>
                                    <p class="requirements-card__title">Именная <br>банковская карта </p>
                                </div>

                                <div class="requirements-card">
                                    <div class="requirements-card__icon">
                                        <img alt="" loading="lazy"
                                             src="design/orange_theme/img/landing/requirements-4.webp">
                                    </div>
                                    <p class="requirements-card__title">Паспорт <br>гражданина РФ</p>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="section__inner">
                    <div class="container">
                        <h2 class="section__title">Отзывы наших клиентов</h2>
                    </div>
                    <div class="section__content">
                        <div class="splide" id="feedbacks-slider">
                            <div class="splide__track">

                                <section class="splide__list feedbacks">
                                    <div class="splide__slide feedback-card">
                                        <header class="feedback-card__header">
                                            <div class="feedback-card__user">
                                                <div class="feedback-card__avatar">
                                                    <img alt="" src="design/orange_theme/img/landing/ava-2.webp"
                                                         loading="lazy">
                                                </div>
                                                <div class="feedback-card__userInfo">
                                                    <p class="feedback-card__name">Анна А.</p>
                                                    <p class="feedback-card__city">Волгоград</p>
                                                </div>
                                            </div>
                                            <aside class="feedback-card__rate stars" data-stars="5">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                            </aside>
                                        </header>
                                        <p class="feedback-card__title">Удобный сервис для онлайн-займов</p>
                                        <p class="feedback-card__text">Очень удобный сервис для тех, кто ищет микрозайм.
                                            Сейчас есть много МФО и кажется, что взять деньги не проблема, но на самом
                                            деле многие компании не способны предоставить качественную услугу и в тех
                                            или иных моментах разочаровывают. Отличный вариант, если нужны займы на
                                            карту онлайн. Рекомендую!</p>
                                        <p class="feedback-card__date">30.06.2024 </p>
                                    </div>

                                    <div class="splide__slide feedback-card">
                                        <header class="feedback-card__header">
                                            <div class="feedback-card__user">
                                                <div class="feedback-card__avatar">
                                                    <img alt="" src="design/orange_theme/img/landing/ava-3.jpg"
                                                         loading="lazy">
                                                </div>
                                                <div class="feedback-card__userInfo">
                                                    <p class="feedback-card__name">Ирина С.</p>
                                                    <p class="feedback-card__city">Тверь</p>
                                                </div>
                                            </div>
                                            <aside class="feedback-card__rate stars" data-stars="5">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                            </aside>
                                        </header>
                                        <p class="feedback-card__title">Оформила займ без проблем</p>
                                        <p class="feedback-card__text">Выражаю благодарность компании. Очень нужны были
                                            деньги, решила взять займ. Заполнила данные и в течении 2-3 минут прислали
                                            денежные средства на карту. Есть возможность пролонгировать кредит.
                                            Стандартная процентная ставка. В общем спасибо!</p>
                                        <p class="feedback-card__date">25.04.2024 </p>
                                    </div>

                                    <div class="splide__slide feedback-card">
                                        <header class="feedback-card__header">
                                            <div class="feedback-card__user">
                                                <div class="feedback-card__avatar">

                                                    <img alt="" src="design/orange_theme/img/landing/ava-4.jpg"
                                                         loading="lazy">
                                                </div>
                                                <div class="feedback-card__userInfo">
                                                    <p class="feedback-card__name">Дмитрий Н.</p>
                                                    <p class="feedback-card__city">Новосибирск</p>
                                                </div>
                                            </div>
                                            <aside class="feedback-card__rate stars" data-stars="5">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                            </aside>
                                        </header>
                                        <p class="feedback-card__title">Вопрос решили за пару минут</p>
                                        <p class="feedback-card__text">Я столкнулся с проблемой когда привязывал карту и
                                            мне оперативно помогли по телефону ее решить. После успешного добавления
                                            карты за 5 минут деньги получил. Круто, молодцы! Если вам нужен займ на
                                            карту срочно, этот сервис — отличный выбор!</p>
                                        <p class="feedback-card__date">27.01.2025 </p>
                                    </div>

                                    <div class="splide__slide feedback-card">
                                        <header class="feedback-card__header">
                                            <div class="feedback-card__user">
                                                <div class="feedback-card__avatar">

                                                    <img alt="" src="design/orange_theme/img/landing/ava-5.webp"
                                                         loading="lazy">
                                                </div>
                                                <div class="feedback-card__userInfo">
                                                    <p class="feedback-card__name">Лейсан Х.</p>
                                                    <p class="feedback-card__city">Санкт-Петербург</p>
                                                </div>
                                            </div>
                                            <aside class="feedback-card__rate stars" data-stars="5">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                            </aside>
                                        </header>
                                        <p class="feedback-card__title">Пользуюсь регулярно</p>
                                        <p class="feedback-card__text">В последнее время часто пользуюсь займами и
                                            полюбилась именно Бустра! Вроде все как у всех в других сервисах, но быстрее
                                            и удобнее для меня лично. Здесь можно получить займ онлайн быстро и без
                                            лишней суеты. Можно погасить досрочно или наоборот заплатить минимальную
                                            сумму, чтобы пролонгировать срок возврата крупного остатка.</p>
                                        <p class="feedback-card__date">30.09.2024 </p>
                                    </div>

                                    <div class="splide__slide feedback-card">
                                        <header class="feedback-card__header">
                                            <div class="feedback-card__user">
                                                <div class="feedback-card__avatar">

                                                    <img alt="" src="design/orange_theme/img/landing/ava-6.png"
                                                         loading="lazy">
                                                </div>
                                                <div class="feedback-card__userInfo">
                                                    <p class="feedback-card__name">Максим С.</p>
                                                    <p class="feedback-card__city">Екатеринбург</p>
                                                </div>
                                            </div>
                                            <aside class="feedback-card__rate stars" data-stars="5">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/empty-star-icon.svg"
                                                     loading="lazy">
                                            </aside>
                                        </header>
                                        <p class="feedback-card__title">Выручили в нужный момент</p>
                                        <p class="feedback-card__text">Понадобились деньги срочно, решил попробовать этот сервис. Оформление не самое простое, но всё получилось сделать с телефона. Деньги пришли быстро, без лишних вопросов. В целом удобно, если нужно решить вопрос здесь и сейчас.</p>
                                        <p class="feedback-card__date">15.02.2025 </p>
                                    </div>

                                    <div class="splide__slide feedback-card">
                                        <header class="feedback-card__header">
                                            <div class="feedback-card__user">
                                                <div class="feedback-card__avatar">

                                                    <img alt="" src="design/orange_theme/img/landing/ava-7.png"
                                                         loading="lazy">
                                                </div>
                                                <div class="feedback-card__userInfo">
                                                    <p class="feedback-card__name">Иван В.</p>
                                                    <p class="feedback-card__city">Казань</p>
                                                </div>
                                            </div>
                                            <aside class="feedback-card__rate stars" data-stars="5">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                            </aside>
                                        </header>
                                        <p class="feedback-card__title">Быстро и без лишних вопросов</p>
                                        <p class="feedback-card__text">Нужны были деньги до зарплаты, оформил займ онлайн. Всё прошло спокойно, без навязывания услуг. Деньги пришли на карту быстро, условия понятные. Удобный сервис, буду иметь в виду, если снова понадобится.</p>
                                        <p class="feedback-card__date">27.05.2025 </p>
                                    </div>

                                    <div class="splide__slide feedback-card">
                                        <header class="feedback-card__header">
                                            <div class="feedback-card__user">
                                                <div class="feedback-card__avatar">

                                                    <img alt="" src="design/orange_theme/img/landing/ava-8.png"
                                                         loading="lazy">
                                                </div>
                                                <div class="feedback-card__userInfo">
                                                    <p class="feedback-card__name">Виктор П.</p>
                                                    <p class="feedback-card__city">Тула</p>
                                                </div>
                                            </div>
                                            <aside class="feedback-card__rate stars" data-stars="5">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                            </aside>
                                        </header>
                                        <p class="feedback-card__title">Спокойно и без лишней суеты</p>
                                        <p class="feedback-card__text">Обратился за займом, когда срочно понадобились деньги. Оформление понятное, без сложных шагов. Деньги получил на карту быстро, условия прозрачные. Понравилось, что всё можно сделать не выходя из дома.</p>
                                        <p class="feedback-card__date">03.08.2025 </p>
                                    </div>

                                    <div class="splide__slide feedback-card">
                                        <header class="feedback-card__header">
                                            <div class="feedback-card__user">
                                                <div class="feedback-card__avatar">

                                                    <img alt="" src="design/orange_theme/img/landing/ava-9.png"
                                                         loading="lazy">
                                                </div>
                                                <div class="feedback-card__userInfo">
                                                    <p class="feedback-card__name">Елена М.</p>
                                                    <p class="feedback-card__city">Ярославль</p>
                                                </div>
                                            </div>
                                            <aside class="feedback-card__rate stars" data-stars="5">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                                <img alt="" src="design/orange_theme/img/landing/star-icon.svg"
                                                     loading="lazy">
                                            </aside>
                                        </header>
                                        <p class="feedback-card__title">Понятный сервис и быстрая помощь</p>
                                        <p class="feedback-card__text">Обращалась за займом впервые, немного переживала. Всё оказалось просто: заполнила заявку, условия сразу понятны. Деньги пришли на карту быстро. Понравилось, что можно продлить срок, если не успеваешь вернуть вовремя.</p>
                                        <p class="feedback-card__date">07.01.2026 </p>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section">
                <div class="container">
                    <section class="rules">
                        <div class="rules__excerpt">
                            <b>Условия получения займа на карту:</b> вы можете получить заём в размере от 1 000 до 100 000 рублей.
                            Срок займа составляет до 180 дней, а процентная ставка варьируется от 0% до 0,8% в день.
                            ПСК (полная стоимость кредита) составляет от 0,000% до 292,000%.
                            Вся сделка проводится удалённо через интернет. После одобрения заявки, которую вы подадите онлайн,
                            деньги мгновенно поступят на вашу банковскую карту.
                            Займы выдают зарегистрированные в Центральном банке микрокредитные организации (МКК).
                            Их деятельность регулируется Федеральным законом "О микрофинансовой деятельности и микрофинансовых организациях"
                            от 02.07.2010 N 151-ФЗ.
                        </div>
                        <div class="rules__content">
                            <br>
                            При соблюдении всех условий Положения об акции "заем под 0%" предоставляется 100% скидка на проценты за пользование займом.
                        </div>
                        <div class="rules__content">
                            <br>
                            Пример расчёта процентной ставки займа суммой 15 000 руб. на срок 30 дней:<br>
                            Процентная ставка за пользование займом в каждый из 30 дней — 0,8% в день;<br>
                            Полная стоимость займа 292% годовых;<br>
                            Сумма начисленных процентов за 30 дней пользования займом — 3 600 руб.<br>
                            Сумма к возврату: 15 000 + 3 600 = 18 600 руб.<br>
                            Проценты за пользование суммой займа при нарушении срока возврата и начисленных процентов за 10 дней −1 200 руб.<br>
                            Пеня, начисленная за просрочку возврата займа в размере 20% годовых от суммы займа за 10 дней — 82,1 руб.<br>
                            Сумма к возврату при нарушении срока возврата займа и начисленных процентов за 10 дней: 18 600 + 1 200 + 82,1 = 19 882,10 руб.<br>
                            Оценивайте свои финансовые возможности и риски.
                        </div>
                        <button class="button button--small rules__btn">Читать</button>
                    </section>
                </div>
            </section>

            <section class="section">
                <div class="container">
                    <div class="section__inner">
                        <h2 class="section__title">Частые вопросы</h2>
                        <div class="section__content">
                            <section class="faq">
                                <div class="faq-card">
                                    <button class="faq-card__btn"></button>
                                    <p class="faq-card__title">Кто может взять микрозаём?</p>
                                    <p class="faq-card__text"><br>
                                        Получить онлайн микрозаём у нас может любой гражданин России при соблюдении
                                        минимальных требований. Для того чтобы оформить заём на карту, достаточно быть
                                        старше 18 лет, иметь паспорт РФ, активный номер телефона и личную банковскую
                                        карту. Мы не требуем справок о доходах и не задаем лишних вопросов.
                                    </p>
                                </div>

                                <div class="faq-card">
                                    <button class="faq-card__btn"></button>
                                    <p class="faq-card__title">Как повысить одобрение?</p>
                                    <p class="faq-card__text"><br>
                                        Наш сервис одобряет онлайн заём даже в случаях, когда банки и другие МФО уже
                                        отказали. Мы лояльнее подходим к оценке клиентов. Чтобы увеличить шанс получения
                                        займа на карту, при подаче заявки рекомендуем воспользоваться услугой
                                        предоставления персонального кредитного рейтинга. Информация, полученная в
                                        результате анализа, положительно сказывается на вероятности получения займа.
                                        Получить займы на карту онлайн с нами — просто!</p>
                                </div>

                                <div class="faq-card">
                                    <button class="faq-card__btn"></button>
                                    <p class="faq-card__title">Как избежать просрочки?</p>
                                    <p class="faq-card__text"><br>
                                        Информация о дате платежа по займу указана в личном кабинете заёмщика. Кроме
                                        того, мы присылаем сообщение с напоминаем. И также рекомендуем отметить плановую
                                        дату платежа в своём календаре.
                                    </p>
                                </div>

                                <div class="faq-card">
                                    <button class="faq-card__btn"></button>
                                    <p class="faq-card__title">Как получить микрозаём?</p>
                                    <p class="faq-card__text"><br>
                                        Для того, чтобы <a href="{$config->root_url}/pages/articles/kak-vzyat-zaym/">оформить микрозаём</a>, необходимо зарегистрироваться на сайте и
                                        заполнить необходимые данные в анкете (если вы являетесь новым клиентом) либо
                                        выполнить авторизацию в личном кабинете, после чего подать заявку на получение
                                        денег на карту.
                                    </p>
                                </div>

                                <div class="faq-card">
                                    <button class="faq-card__btn"></button>
                                    <p class="faq-card__title">Как проверить статус моего займа?</p>
                                    <p class="faq-card__text"><br>
                                    1. Перейдите на сайт компании и нажмите кнопку «Войти» в правой верхней части страницы.</br>
                                    2. Введите номер телефона, который вы указывали при оформлении займа, убедитесь в корректности данных и кликните по кнопке «Войти».</br>
                                    3. Введите код из СМС, который придёт на ваш номер, чтобы подтвердить вход в личный кабинет.</br>
                                    4. После авторизации откройте раздел «Текущий заем» в меню личного кабинета.</br>
                                    5. В этом разделе вы увидите сумму к возврату, дату следующего платежа, общий срок займа, а также дополнительную информацию по вашему действующему договору.
                                    </p>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </section>

                   <section class="section">
                       <div class="container">
                           <section class="apps">
                           <div class="apps_circle"></div>
                               <p class="apps__title">Займы в вашем кармане!</p>
                               <p class="apps__text">Установите наше приложение и&nbsp;получите деньги за&nbsp;пару кликов</p>
                               <div class="apps__list apps-list">
                                    <a class="apps-list__item rect"
                                      href="https://redirect.appmetrica.yandex.com/serve/245726716671114815">
                         <span class="apps-list__pic">
                                        <img alt="" loading="lazy" src="design/boostra_mini_norm/assets/image/googlePlay-logo.png">
                         </span>
                                       <span class="apps-list__name">Google Play</span>
                                   </a>
                                   <a class="apps-list__item rect"
                                      href="https://redirect.appmetrica.yandex.com/serve/461849392538739421">
                         <span class="apps-list__pic">
                                        <img alt="" loading="lazy" src="design/boostra_mini_norm/assets/image/rustore-logo.png">
                         </span>
                                       <span class="apps-list__name">RuStore</span>
                                   </a>

                                   <a class="apps-list__item rect"
                                      href="https://store.nashstore.ru/store/6655e5700a39b29c04cd5ccf?referrer=appmetrica_tracking_id%3D965769215283862779%26ym_tracking_id%3D8538754551737958223">
                         <span class="apps-list__pic">
                                        <img alt="" loading="lazy" src="design/boostra_mini_norm/assets/image/nashstore-logo.png">
                         </span>
                                       <span class="apps-list__name">NashStore</span>
                                   </a>

                                   <a class="apps-list__item rect"
                                      href="https://redirect.appmetrica.yandex.com/serve/461366054585709806">
                         <span class="apps-list__pic">
                                        <img alt="" loading="lazy" src="design/boostra_mini_norm/assets/image/android-logo.png">
                         </span>
                                       <span class="apps-list__name">Android</span>
                                   </a>
                               </div>
                               <div class="apps_phone_images">
                                    <picture>
                                        <source media="(max-width: 991px)" srcset="design/boostra_mini_norm/assets/image/Apps_iPhone18_mobile.png"/>
                                        <img src="design/boostra_mini_norm/assets/image/Apps_iPhone18.png" alt="Демонстрация мобильного приложения boostra"/>
                                    </picture>
                                    <img src="design/boostra_mini_norm/assets/image/Apps_iPhone19.png" alt="Демонстрация мобильного приложения boostra"/>
                               </div>
                           </section>
                       </div>
                   </section>

        </main>
    </section>
    {include 'html_blocks/landing_footer.tpl'}
    {include 'modals/inactive_user_popup.tpl'}
    <script src="design/orange_theme/js/landing/splide.min.js"></script>
    <script async src="https://s3.usedesk.ru/lib/secure.usedesk.ru/widget_161404_53920.js"></script>

    {if $module == 'MainView' && $comeback_url && !$same_page && $smarty.get.utm_source != 'finuslugi'}
        <script>
            if(window.history) {
                window.history.pushState({ catchHistory: true }, '', window.location.href)
                window.history.pushState({ catchHistory: true }, '', window.location.href)
                window.addEventListener('popstate', (e) => {
                    if(e.state?.catchHistory) {
                    {if $user}
                        const href_append = '&p={$user->phone_mobile}&utm_source2={$user->utm_source}'
                    {else}
                        const href_append = '&utm_source2={$smarty.cookies.utm_source}'
                    {/if}
                        e.preventDefault();
                        sendMetric('reachGoal', 'decline_monitoring_1')
                    {if $smarty.get.utm_source == 'bankiru'}
                        window.location = 'https://bankipartners.ru/s/PiqeuvfvFr?statid=105_&erid=1';
                    {else}
                        invokeShopview('bonon-comeback:nk', '{$comeback_url}')
                        window.location = '{$comeback_url}' + href_append;
                    {/if}
                    }
                })
            }
        </script>
    {/if}
    <script lang="javascript">
        function clickHunter(source_id, back_url) {
            {if $smarty.get.utm_source == 'bankiru' || $same_page}
            {elseif $background_url}
                setTimeout(() => {
                {if $user}
                    const href_append = '&p={$user->phone_mobile}&utm_source2={$user->utm_source}'
                {else}
                    const href_append = '&utm_source2={$smarty.cookies.utm_source}'
                {/if}
                    invokeShopview('bonon-background:nk', (back_url ? back_url : "{$background_url}"))
                    sendMetric('reachGoal', 'decline_monitoring_' + source_id)
                    window.location.href = (back_url ? back_url : "{$background_url}") + href_append;
                }, 1000)
            {/if}
        }

      {if $is_after_scorista_sms}
          $(document).ready(function () {
              sendMetric('reachGoal', 'convizvhoda')
          });
      {/if}

        $(document).ready(function () {
            $('.is_noindex').removeClass('is_noindex');
            $('#cbr_link').click(function (event) {
                event.preventDefault();
                sendMetric('reachGoal', 'cb');
                const linkUrl = $(this).attr('href');
                $.ajax({
                    url: 'ajax/client_action_handler.php?action=clickCbrLink',
                    method: 'GET',
                    success: function (response) {
                        window.open(linkUrl, '_blank');
                    },
                    error: function (xhr, status, error) {
                        window.open(linkUrl, '_blank');
                    }
                });
            });
        });

        //баннер мобильного приложения    
        const banner = document.querySelector('.mobileBanner');
        function mobileBannerClose(e = null) {
            if(e !== null){
                e.preventDefault();
            }
            if(banner !== undefined){
                banner.classList.add('mobileBanner_hidden')
            }
        }
        
        const queryString = window.location.search;

        if(queryString.includes('utm')) {
            mobileBannerClose();
        }

        const mobileBannerCloseBtn = document.querySelector('.mobileBanner__closeBtn')
        mobileBannerCloseBtn.addEventListener('click', mobileBannerClose);
        if (navigator.userAgent.includes("iPhone")) {
            mobileBannerClose();
        }
    </script>
</div>
{* --- Cookie Banner --- *}

{* Захватываем CSS для вставки в <head> основного шаблона *}
<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/css/cookies.css?v=1.00"/>

{* Баннер *}
<div class="cookies" data-cookies>
    <div class="container">
        <div class="cookies-inner">
            <div class="cookies-main">
                <div class="cookies-title">Куки</div>
                <div class="cookies-desc">
                    Продолжая использовать данный сайт, Вы соглашаетесь со сбором файлов куки (cookies) для аналитики и корректной работы сайта в соответствии с
                    <a href="/files/docs/FTM01_2026-1_personal_data_policy_FintechMarket_090226_v4.pdf">
                        Политикой обработки персональных данных
                    </a>
                </div>
            </div>
            <button class="cookies-btn btn" data-cookies-close>Понятно</button>
        </div>
    </div>
</div>

{* JavaScript-код *}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cookiesBanner = document.querySelector('[data-cookies]');
        const closeTriggers = document.querySelectorAll('[data-cookies-close]');

        if (!cookiesBanner) {
            console.error('Баннер куки не найден (селектор [data-cookies])');
            return;
        }

        if (localStorage.getItem('cookiesAccepted') === 'true') {
            cookiesBanner.style.display = 'none';
            return;
        }

        cookiesBanner.style.display = 'block';

        closeTriggers.forEach(trigger => {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                localStorage.setItem('cookiesAccepted', 'true');
                cookiesBanner.style.transition = 'opacity 0.4s ease';
                cookiesBanner.style.opacity = '0';
                setTimeout(() => {
                    cookiesBanner.style.display = 'none';
                }, 400);
            });
        });
    });
</script>
