{assign var="active_automation_fail" value=false}
{foreach $automation_fails as $item}
    {if $item->is_active}
        {assign var="active_automation_fail" value=true}
        {break}
    {/if}
{/foreach}

{if $active_automation_fail}
    <div id="inform">
        <strong>
            {foreach $automation_fails as $item}
                {if $item->is_active}
                    {$item->text|nl2br}.
                {/if}
            {/foreach}
        </strong>
    </div>
{/if}

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
                                <li>— Более 1 000 000&nbsp;клиентов
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
                    <div class="hero__calculator">
                        <div class="hero__calculator__slides" style="display: none">
                            <div class="hero__calculator__slide active" data-id="0">
                                <p>Займы для физ. лиц</p>
                            </div>
                            <div class="hero__calculator__slide" data-id="1">
                                <p>Займы для ИП</p>
                            </div>
                        </div>
                        <div class="calculator" data-percent="0"> <!--0.8-->
                            <p class="calculator__title">
                                Первый заём под 0% <br/> при соблюдении условий
                            </p>
                            <div class="zero-percent-notice" style="margin: 10px 0;">
                                <a href="/files/docs/polozhenie0.pdf" target="_blank">Положение о проведении акции</a>
                            </div>
                            <p class="calculator__text">
                                Высокое одобрение заявок - до 99%
                            </p>
                            <div class="calculator__slider">
                                <div class="calculator__slider-top">
                                    <span>Выберите сумму</span>
                                    <b><b class="js-hero-range-output">25 000</b> ₽</b>
                                </div>
                                <div class="range">
                                    <input id="hero-range" max="30000" min="1000" name="sum" step="1000"
                                           type="range"
                                           value="30000"/>
                                </div>
                                <div class="calculator__slider-bottom">
                                    <span>1 000</span>
                                    <span>30 000</span>
                                </div>
                            </div>
                            <div class="calculator__slider" id="period-slider" style="display: none">
                                <div class="calculator__slider-top">
                                    <span>Выберите срок</span>
                                    <b>
                                        <b class="js-hero-range-long-output">16</b>
                                        <span class="js-period-unit"> дней</span>
                                    </b>
                                </div>
                                <div class="range">
                                    <input id="hero-range-long"
                                           max="16"
                                           min="5"
                                           name="long"
                                           step="1"
                                           type="range"
                                           value="16"/>
                                </div>
                                <div class="calculator__slider-bottom">
                                    <span>5</span>
                                    <span>16</span>
                                </div>
                            </div>
                            <div class="calculator__stat">
                                <div class="calculator__stat-section">
                                    <span>Вы вернете</span>
                                    <b><b class="js-total-output">25 000</b> ₽</b>
                                </div>
                                <div class="calculator__stat-section">
                                    <span>Ставка</span>
                                    <b class="text-success js-withoutCoeff">Без процентов*</b>
                                    <b class="js-withCoeff"><b class="js-coeff"></b>&nbsp;%</b>
                                </div>
                            </div>
                            <div class="calculator__footer">
                                {if $t_bank_button_registration_access || $esia_button_registration_access}
                                    <div class="auth__buttons">
                                        {if $t_bank_button_registration_access}
                                            <a onclick="sendMetric('reachGoal', 'tid_main')" class="tid_button" href="{$t_id_auth_url}">
                                                Быстрее через
                                                <img src="/design/boostra_mini_norm/assets/image/tinkoff-id-small.png" alt="" />
                                                T-ID
                                            </a>
                                        {/if}
                                        {if $esia_button_registration_access}
                                            <a onclick="sendMetric('reachGoal', 'gu_main')" class="esia_button" href="{$esia_redirect_url}">
                                                Быстрее через
                                                <img class="" height="24" src="/design/boostra_mini_norm/assets/image/esia_logo.png" alt="" />
                                                Госуслуги
                                            </a>
                                        {/if}
                                    </div>
                                    <div class="divider">
                                        <span>ИЛИ</span>
                                    </div>
                                {/if}
                                <button class="button button--primary button--big calc_button" id="hero-btn">
                                    <span class="js-withoutCoeff">Получить бесплатно</span>
                                    <span class="js-withCoeff">Получить деньги</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {include './mobile_banners/link_banner.tpl' banner_img_android="/design//boostra_mini_norm/assets/image/banner_rustore_img.png" banner_img_ios="/design//boostra_mini_norm/assets/image/banner_ios_img.png" banner_link='https://apimp.boostra.ru/get_app.php?slot=b2'}

        <button id="floating-hero-btn" class="button button--primary" style="position: fixed; right: 20px; bottom: 30px; display: none; z-index: 1000;">
            Получить деньги
        </button>

        <main class="tail">
            {include file='main_page/partners_data.tpl'}
            {if $is_organic}
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
                                                    <span class="tag tag--ghost tag--small">за 10 минут</span>
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
                                                    <span class="tag tag--ghost tag--small">за 5 минут</span>
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
                                                    <span class="tag tag--ghost tag--small">за 3 минуты</span>
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
                                                    <span class="tag tag--ghost tag--small">за 10 минут</span>
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
                        <h2 class="section__title">Нам доверяют более 1&nbsp;000&nbsp;000&nbsp;клиентов</h2>
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
                                        <p class="feedback-card__title">Очень хорошо</p>
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
                                        <p class="feedback-card__title">Очень хорошо</p>
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
                                        <p class="feedback-card__title">Очень хорошо</p>
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
                                        <p class="feedback-card__title">Очень хорошо</p>
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
                                                    <img alt="" src="design/orange_theme/img/landing/ava-7.webp"
                                                         loading="lazy">
                                                </div>
                                                <div class="feedback-card__userInfo">
                                                    <p class="feedback-card__name">Дмитрий Т.</p>
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
                                        <p class="feedback-card__title">Очень быстрое оформление</p>
                                        <p class="feedback-card__text">Хотел оформить займ онлайн на небольшую сумму и попал на сайт boostra. Процесс оказался настолько простым, что я буквально за 10 минут получил необходимую сумму прямо на карту. Очень удобный сервис для тех, кто не хочет лишний раз ходить в банки.</p>
                                        <p class="feedback-card__date">03.11.2025</p>
                                    </div>

                                    <div class="splide__slide feedback-card">
                                        <header class="feedback-card__header">
                                            <div class="feedback-card__user">
                                                <div class="feedback-card__avatar">
                                                    <img alt="" src="design/orange_theme/img/landing/ava-9.webp"
                                                         loading="lazy">
                                                </div>
                                                <div class="feedback-card__userInfo">
                                                    <p class="feedback-card__name">Александр В.</p>
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
                                        <p class="feedback-card__title">Отличный сервис для срочных нужд</p>
                                        <p class="feedback-card__text">Когда понадобился микрозайм онлайн, мне было важно, чтобы не нужно было долго ждать. Boostra меня не разочаровала — оформил займ за несколько кликов, деньги пришли быстро. Теперь буду пользоваться этим сервисом постоянно, если возникнут срочные финансовые потребности.</p>
                                        <p class="feedback-card__date">02.11.2025</p>
                                    </div>

                                    <div class="splide__slide feedback-card">
                                        <header class="feedback-card__header">
                                            <div class="feedback-card__user">
                                                <div class="feedback-card__avatar">
                                                    <img alt="" src="design/orange_theme/img/landing/ava-11.webp"
                                                         loading="lazy">
                                                </div>
                                                <div class="feedback-card__userInfo">
                                                    <p class="feedback-card__name">Сергей Л.</p>
                                                    <p class="feedback-card__city">Ростов-на-Дону</p>
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
                                        <p class="feedback-card__title">Простота и удобство</p>
                                        <p class="feedback-card__text">Не думал, что буду оформлять займы на карту, но ситуация заставила. Процесс оказался простым и быстрым, не потребовались дополнительные документы. Все честно и без скрытых условий. Если снова понадобятся деньги срочно, точно буду обращаться сюда.</p>
                                        <p class="feedback-card__date">18.10.2025</p>
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
                               <p class="apps__title">Займы в вашем кармане!</p>
                               <p class="apps__text">Установите наше приложение и&nbsp;получите деньги за&nbsp;пару кликов</p>
                               <div class="apps__list apps-list">
                                   <a class="apps-list__item"
                                      href="https://redirect.appmetrica.yandex.com/serve/749596424009746204">
                         <span class="apps-list__pic">
                           <img alt="" loading="lazy" src="design/orange_theme/img/landing/rustore-logo.png">
                         </span>
                                       <span class="apps-list__name">RuStore</span>
                                   </a>

                                   <a class="apps-list__item"
                                      href="https://redirect.appmetrica.yandex.com/serve/965769215283862779">
                         <span class="apps-list__pic">
                                 <img alt="" loading="lazy" src="design/orange_theme/img/landing/nashstore-logo.png">
                         </span>
                                       <span class="apps-list__name">NashStore</span>
                                   </a>

                                   <a class="apps-list__item"
                                      href="https://redirect.appmetrica.yandex.com/serve/965769215283862779">
                         <span class="apps-list__pic">
                           <img alt="" loading="lazy" src="design/orange_theme/img/landing/android-logo.png">
                         </span>
                                       <span class="apps-list__name">Android</span>
                                   </a>
                               </div>
                               <picture>
                                   <source media="(min-width: 991px)" srcset="design/orange_theme/img/landing/apps-pic.png">
                                   <img alt="" class="apps__pic" loading="lazy"
                                        src="design/orange_theme/img/landing/apps-pic-m.png">
                               </picture>
                           </section>
                       </div>
                   </section>

        </main>
        <footer class="footer">
            <div class="container">
                <div class="footer__inner">
                    <section class="footer__top">
                        <div class="footer__logo">
                            <a class="logo" href="">
                                <img alt=""
                                     loading="lazy"
                                     src="design/orange_theme/img/landing/logo.svg">
                            </a>
                        </div>
                        <div class="footer__socials">
                            <section class="socials">

                                <!-- VK -->
                                <a href="https://vk.com/write-212426324">
                                    <svg fill="none" height="24" viewBox="0 0 24 24" width="24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path
                                                d="M12.7374 18.6331C5.37193 18.6331 1.17229 13.8146 1 5.78373H4.70429C4.82274 11.673 7.53635 14.1646 9.69001 14.6794V5.78373H13.1682V10.8596C15.2895 10.6434 17.5293 8.32683 18.2831 5.77344H21.7505C21.4678 7.09519 20.903 8.347 20.0915 9.45066C19.28 10.5543 18.2391 11.4861 17.034 12.1878C18.3789 12.8278 19.5666 13.7332 20.5188 14.8441C21.471 15.9551 22.166 17.2465 22.5581 18.6331H18.7354C17.917 16.1929 15.871 14.2985 13.1682 14.0411V18.6331H12.7482H12.7374Z"
                                                fill="#1E262E"/>
                                    </svg>
                                </a>

                            </section>
                        </div>
                    </section>
                    <nav class="footer__menu">
                        <ul>
                            <li>
                                <a href="/about/company">О компании</a>
                            </li>
                            <li>
                                <a href="/faq/main">Вопросы и ответы</a>
                            </li>
                            <li>
                                <a onclick="window.history.pushState(null, '', window.location.href)"
                                    href="#contacts">Контакты</a>
                            </li>
                            <li>
                                <a href="/user/contract">Внести платёж</a>
                            </li>
                            <li>
                                <a style="color: red;" href="/complaint">Пожаловаться</a>
                            </li>
                        </ul>
                    </nav>
                    <section class="footer__contacts" id="contacts">
                        <div class="footer__contacts-section">
                            <span>По всем вопросам</span>
                            <a href="mailto:info@boostra.ru">info@boostra.ru</a>
                        </div>
                        <div class="footer__contacts-section">
                            <span>Телефон</span>
                            <a href="tel:88003333073">8 800 333 30 73</a>
                        </div>
                    </section>
                    <section class="footer__disclaimers">

                        <div class="partners-section">

                            <div id="footer-urls-container">
                                {include 'main_page/page_urls.tpl'}
                                <div id="footer-copyright">
                                    <p>ООО «Финтех-Маркет» является правообладателем товарного знака (знака обслуживания) «BOOSTRA» № 575896</p>
                                </div>
                            </div>

                            <p>Общество с ограниченной ответственностью «Финтех-Маркет» (ООО «Финтех-Маркет»), ИНН 6317164496,</p>
                            <p>юридический и фактический адрес: 443001, САМАРСКАЯ ОБЛАСТЬ, Г.О. САМАРА, ВН.Р-Н ЛЕНИНСКИЙ, Г САМАРА, УЛ ЯРМАРОЧНАЯ, Д. 3, КВ. 62;</p>
                            <p>основной код ОКВЭД 62.01 - разработка компьютерного программного обеспечения</p>

                            <p>ООО «Финтех-Маркет» не является кредитором и не предоставляет финансовые услуги. Финансовые услуги
                                оказываются непосредственно микрофинансовыми организациями-партнерами ООО «Финтех-Маркет».</p>

                            <p>Все партнеры ООО «Финтех-Маркет» включены в реестр микрофинансовых организаций Банка России.</p>

                            <p>© 2025, ООО «Финтех-Маркет»</p>

                            <p>Физические лица, разрешившие распространение своих персональных данных на этом сайте, запретили их дальнейшую передачу любым третьим лицам, и обработку этими лицами (включая распространение).</p>

                            <p>Официальный сайт Банка России: <a href="https://cbr.ru/" class="cbr_link" id="cbr_link"
                                                                 target="_blank">https://cbr.ru/</a>
                            </p>

                            <p>Интернет-приемная Банка России: <a href="https://cbr.ru/Reception" class="cbr_link"
                                                                  target="_blank">https://cbr.ru/Reception</a></p>

                            <p>Реестр МФО Банка России: <a href="https://cbr.ru/microfinance/registry/" class="cbr_link"
                                                           target="_blank">https://cbr.ru/microfinance/registry/</a></p>

                            <p>ООО «Финтех-Маркет» осуществляет деятельность в сфере IT</p>

                            {*<p><a href="/info_partners" target="_blank">ПАРТНЕРЫ ООО «ФИНТЕХ-МАРКЕТ»</a></p>*}

                            <p><a href="/files/docs/BEST2PAY_Offer.pdf" target="_blank">ОФЕРТА ОБ ИСПОЛЬЗОВАНИИ
                                    ПРОЦЕССИНГОВОГО ЦЕНТРА BEST2PAY</a></p>

                            <p><a href="/files/docs/BEST2PAY_Security_Policy.pdf" target="_blank">ПОЛИТИКА БЕЗОПАСНОСТИ
                                    ПЛАТЕЖЕЙ BEST2PAY</a></p>

                            <p><a href="/files/docs/akvarius/26-uslovia-i-porjadok-predostavlenia-zaimov.pdf" target="_blank">ПОРЯДОК И УСЛОВИЯ ПРЕДОСТАВЛЕНИЯ ЗАЙМОВ</a></p>
                            <p><a href="/files/docs/akvarius/informatsiya_dlya_klientov_po_dopolnitelnym_platnym_uslugam_ftm.pdf" target="_blank">Информация для клиентов</a></p>
                        </div>
                        <br>
                        <button class="button button--secondary" onclick="submitOrder()">Получить бесплатно</button>
                        <br>
                        <br>
                        <p>На сайте используются файлы cookie и другие технологии, которые позволяют идентифицировать
                            вас, а также изучать, как вы используете веб-сайт. Дальнейшее использование этого сайта подразумевает
                            ваше согласие на использование этих технологий.</p>
                        <p>Оплатить заём можно с помощью банковских карт платёжных систем Visa, MasterCard, МИР. При
                            оплате банковской картой безопасность платежей гарантирует процессинговый центр Best2Pay. </p>
                        <p>Приём платежей происходит через защищённое безопасное соединение. Используется протокол TLS
                            1.2.
                            Компания Best2Pay соответствует международным требованиями PCI DSS, что обеспечивает безопасность
                            оплаты. Реквизиты карты, регистрационные данные и др. не поступают в Интернет-магазин. Их обработка
                            производится на стороне процессингового центра Best2Pay и полностью защищена.
                            Никто, в том числе ООО «Финтех-Маркет», не может получить банковские и персональные данные плательщика.</p>

                    </section>
                </div>
            </div>
        </footer>
    </section>
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
                        const href_append = '&p={$user->phone_mobile}'
                    {else}
                        const href_append = ''
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
                    const href_append = '&p={$user->phone_mobile}'
                {else}
                    const href_append = ''
                {/if}
                    invokeShopview('bonon-background:nk', (back_url ? back_url : "{$background_url}"))
                    sendMetric('reachGoal', 'decline_monitoring_' + source_id)
                    window.location.href = (back_url ? back_url : "{$background_url}") + href_append;
                }, 1000)
            {/if}
        }

        function submitOrder(_href) {
            let amount = parseInt($('#hero-range').val()), period = parseInt($('#hero-range-long').val());

            const $activeSlide = $('.hero__calculator__slide.active');
            const isLegalEntity = $activeSlide.data('id') === 1;

            {if !$is_developer}
            sendMetric('reachGoal', 'main_page_get_zaim_new_design2')
            {/if}

            if (isLegalEntity) {
                const redirectUrl = 'https://freecapital.ru/external?amount=' + amount + '&period=' + period +'&utm_source=boostra_calc';

                {if $smarty.get.utm_source == 'bankiru' || $same_page}
                window.location.href = (typeof _href === 'string' ? _href : redirectUrl);
                {else}
                window.open((typeof _href === 'string' ? _href : redirectUrl), '_blank');
                clickHunter(3);
                {/if}
            } else {
                const url = '/init_user?amount=' + amount + '&period=' + period;

                {if $smarty.get.utm_source == 'bankiru' || $same_page}
                window.location.href = (typeof _href == 'string' ? _href : url)
                {else}
                window.open((typeof _href == 'string' ? _href : url), '_blank');
                clickHunter(3);
                {/if}
            }
        }

      function handleCalcSlideClick() {
        const $el = $(this);
        const $parent = $($el.parent());
        const $activeElement = $($parent.find('.active'));

        if ($activeElement.data('id') === $el.data('id')) {
          return;
        }

        $('.hero__calculator__slide').toggleClass('active');

        const $input = $('#hero-range');
        const $inputPeriod = $('#hero-range-long');
        const $calculator = $('.calculator');
        const $calculatorCoef = $('.js-coeff');
        const $calculatorTitle = $('.calculator__title');
        const $calcRangeLabel = $('.calculator__slider:not("#period-slider") .calculator__slider-bottom');
        const $calcRangeLabelPeriod = $('#period-slider .calculator__slider-bottom');
        const $calcRenderedSum = $('.js-hero-range-output');
        const $calcRenderedPeriod = $('.js-hero-range-long-output');
        const $calcTotalReturn = $('.js-total-output');

        if ($el.data('id') === 0) {
          $input.attr('max', 60000);
          $input.attr('min', 1000);
          $input.attr('step', 1000);
          $input.val(30000);

          $inputPeriod.attr('max', 28);
          $inputPeriod.attr('min', 5);
          $inputPeriod.attr('step', 1);
          $inputPeriod.val(16);

          $calculator.data('percent', 0.8);
          $calculatorTitle.html('Первый заём под 0% <br/>при соблюдении условий');
          $calcRangeLabel.html('<span>1 000</span><span>30 000</span><span>100 000</span>');
          $calcRangeLabelPeriod.html('<span>5</span><span>16</span><span>180</span>');
          $calcRenderedPeriod.html('16');
          $calcRenderedSum.html('30&nbsp;000');
          $calcTotalReturn.html('33&nbsp;840');
          $('.calculator__text__info').show();
          $('.js-hero-range-long-output').html('16');
          $('.js-period-unit').text(' дней');
          $('.calculator input').css('background-size', '50% 100%');
          $('.zero-percent-notice').show();
          $('.auth__buttons').show();
          $('.divider').show();
        } else {
          $input.attr('max', 500000);
          $input.attr('min', 50000);
          $input.attr('step', 10000);
          $input.val($input.attr('max'));

          $inputPeriod.attr('max', 26);
          $inputPeriod.attr('min', 9);
          $inputPeriod.attr('step', 1);
          $inputPeriod.val($input.attr('max'));

          $calculator.data('percent', 1.85);
          $calculatorTitle.html('Онлайн заём от <b>' + $calculator.data('percent') + '%</b>');
          $calcRangeLabel.html('<span>50 000</span><span>500 000</span>');
          $calcRangeLabelPeriod.html('<span>9 недель</span><span>26 недель</span>');
          $calcRenderedPeriod.html('26');
          $('.js-period-unit').text(' недель');
          $calcRenderedSum.html('500&nbsp;000');
          $calcTotalReturn.html('1&nbsp;220&nbsp;000');
          $('.calculator__text__info').hide();
          $('.calculator input').css('background-size', '100% 100%');

          $('.zero-percent-notice').hide();

          $('.auth__buttons').hide();
          $('.divider').hide();

          $('.js-withoutCoeff').hide();
          $('.js-withCoeff').show();
        }

        $calculatorCoef.text($calculator.data('percent'));
      }

      $('#hero-btn').on('click', submitOrder);
      $('#floating-hero-btn').on('click', submitOrder);

      function initCalculator() {

          // Пересчет суммы к возврату и ставки
          function recalcCalculator() {
            const $calculator = $('.calculator');
            const amount = parseInt($('#hero-range').val(), 10) || 0;
            const period = parseInt($('#hero-range-long').val(), 10) || 0;

            const $activeSlide = $('.hero__calculator__slide.active');
            const isLegalEntity = $activeSlide.data('id') === 1;

            let percent = parseFloat($calculator.data('percent')) || 0;
            if (amount < 30000) {
              percent = 0.8;
            }
            let total = amount;

            if (amount < 30000) {
              total = amount + Math.round(amount * period * (percent / 100));
              $('.js-withoutCoeff').hide();
              $('.js-withCoeff').show();
              $('.js-coeff').text(percent);
            } else {
              if (!isLegalEntity) {
                $('.js-withCoeff').hide();
                $('.js-withoutCoeff').show();
              } else {
                $('.js-withoutCoeff').hide();
                $('.js-withCoeff').show();
              }
            }


            $('.js-total-output').html(total);
            $('.js-hero-range-output').html(amount);
            $('.js-hero-range-long-output').html(period.toString());
          }

          $('#hero-range, #hero-range-long').on('input', function () {
            recalcCalculator();
          });

            {if $is_organic }
          $('.hero__calculator__slide').on('click', handleCalcSlideClick);

          $('.calculator input').change(function () {
            const $activeMode = $('.hero__calculator__slide.active').data('id');

            if ($activeMode === 0) {
              const $input = $(this);

              const $inputName = $input.attr('name');

              if ($inputName === 'sum') {
                if ($input.val() > 30000) {
                  $input.val(30000);
                  $('.calculator__text__info').html('Займы на сумму более 30,000 рублей доступны со второго займа');
                  $input.css('background-size', '50% 100%');
                  $('.js-hero-range-output').html('30&nbsp;000');
                } else {
                  $('.calculator__text__info').html('&nbsp;');
                }
              }

              if ($inputName === 'long') {
                if ($input.val() > 16) {
                  $input.val(16);
                  $('.calculator__text__info').html('Займы на срок более 16 суток доступны со второго займа');
                  $input.css('background-size', '50% 100%');
                  $('.js-hero-range-long-output').html('16');
                } else {
                  $('.calculator__text__info').html('&nbsp;');
                }
              }
            }

            recalcCalculator();
          });

          $('.hero__calculator__slides').show();

          $('.tradingview-widget-container').show();

          const $input = $('#hero-range');
          const $inputPeriod = $('#hero-range-long');
          const $calculator = $('.calculator');
          const $calculatorTitle = $('.calculator__title');
          const $calcRangeLabel = $('.calculator__slider:not("#period-slider") .calculator__slider-bottom');
          const $calcRangeLabelPeriod = $('#period-slider .calculator__slider-bottom');
          const $calcRenderedSum = $('.js-hero-range-output');
          const $calcRenderedPeriod = $('.js-hero-range-long-output');
          const $calcTotalReturn = $('.js-total-output');

          $input.attr('max', 60000);
          $input.attr('min', 1000);
          $input.attr('step', 1000);
          $input.val(30000);

          $inputPeriod.attr('max', 28);
          $inputPeriod.attr('min', 5);
          $inputPeriod.attr('step', 1);
          $inputPeriod.val(16);

          $calculator.data('percent', 0.8);
          $calculatorTitle.html('Первый заём под 0% <br/>при соблюдении условий');
          $calcRangeLabel.html('<span>1 000</span><span>30 000</span><span>100 000</span>');
          $calcRangeLabelPeriod.html('<span>5</span><span>16</span><span>180</span>');
          $calcRenderedPeriod.html('16');
          $calcRenderedSum.html('30&nbsp;000');
          $calcTotalReturn.html('33&nbsp;840');
          $('.js-hero-range-long-output').html('16');
          $('.calculator input').css('background-size', '50% 100%');
          $('.calculator__stat').hide();
          $('.calculator__text').html('<span class="calculator__text__info">&nbsp;</span>');
          $('#period-slider').show();
          $('#partners-section').show();
            {/if}
      }

      if (document.readyState === 'complete' || document.readyState === 'interactive') {
        initCalculator();
      } else {
        document.addEventListener('DOMContentLoaded', initCalculator);
      }

      {if $is_after_scorista_sms}
          $(document).ready(function () {
              sendMetric('reachGoal', 'convizvhoda')
          });
      {/if}

      // Показывать/скрывать кнопку в моб версии на главной
      const heroBtn = document.getElementById('hero-btn');
      const heroRange = document.getElementById('hero-range');
      const floatingHeroBtn = document.getElementById('floating-hero-btn');

      function checkFloatingHeroBtnVisibility() {
          const rectBtn = heroBtn.getBoundingClientRect();
          const rectRange = heroRange.getBoundingClientRect();
          const isVisibleBtn = rectBtn.top >= 0 && rectBtn.bottom <= (window.innerHeight || document.documentElement.clientHeight);
          const isVisibleRange = rectRange.top >= 0 && rectRange.bottom <= (window.innerHeight || document.documentElement.clientHeight);

          const isMobile = window.innerWidth <= 768;

          const popup = document.getElementById('inactive-user-popup');
          const isPopupVisible = popup && popup.style.display === 'flex';

          if (!isMobile || isPopupVisible) {
              floatingHeroBtn.style.display = 'none';
              return;
          }

          if (isVisibleBtn || isVisibleRange) {
              floatingHeroBtn.style.display = 'none';
          } else {
              floatingHeroBtn.style.display = 'block';
          }
      }

      document.addEventListener('scroll', checkFloatingHeroBtnVisibility);
      window.addEventListener('load', checkFloatingHeroBtnVisibility);
      window.addEventListener('resize', checkFloatingHeroBtnVisibility);

        $(document).ready(function () {
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
