{* Главная страница магазина *}

{* Для того чтобы обернуть центральный блок в шаблон, отличный от index.tpl *}
{* Укажите нужный шаблон строкой ниже. Это работает и для других модулей *}
{$wrapper = 'index.tpl' scope=parent}

{* Канонический адрес страницы *}
{$canonical="" scope=parent}
{if $config->snow}
    <link rel="stylesheet" type="text/css" href="design/orange_theme/css/holidays/snow.css?v=1.36"/>
    {include file='holidays/snow.tpl'}
{/if}
{literal}
    <style>
        .banner-wrapper {
            position: relative;
            text-align: center;
        }

        .banner-wrapper img {
            max-width: 1000px;
            height: auto;
            border-radius: 12px;
        }

        .banner_square {
            border-radius: 12px;
            margin-top: 60px;
        }

        .btn-banner {

        }

        .modal.fade .modal-dialog {
            opacity: 0;
            transition: opacity 0.3s ease-out;
            transform: translate(0, -50%);
        }

        .modal.fade.show .modal-dialog {
            opacity: 1;
            transform: translate(0, 0);
        }

        .modal-content {
            background: #FFFFFF;
            border-radius: 12px;
        }

        .modal-body {
            text-align: center;
            position: relative;
        }

        .modal-body .btn-group-link {
            display: inline-block;
            background: #D24178;
            border-radius: 5px;
            padding: 8px 16px;
            color: #FFFFFF;
            text-decoration: none;
        }

        .modal-body .btn-group-link:hover {
            background: #C72D67;
        }

        .btn-banner {
            position: absolute;
            bottom: 10px;
            left: 20px;
            margin: 10px;
            z-index: 2;
            background: #D24178;
            border-radius: 5px;
            padding: 8px 16px;
            color: #FFFFFF;
        }

        .btn-banner:hover {
            background: #C72D67;
        }

        @media(max-width:768px){

            .banner-wrapper img {
                max-width: 100%!important;
                height: auto;
            }
            .banner-wrapper {
                margin-top: 1rem;
                margin-bottom: 1rem;
            }
        }

        .gradient-box {
            width: 100%;
            height: 124px;
            border-radius: 12px;
            background: linear-gradient(to bottom, #6DDFE6, #447AAC);
            padding: 20px;
            position: relative;
        }

        .gradient-box h3 {
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            color: #fff;
            margin: 0;
        }

        .snowflakes {
            position: relative;
            width: 100%;
            height: 124px;
            border-radius: 12px;
            background: linear-gradient(to bottom, #6DDFE6, #447AAC);
            padding: 20px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .gradient-box h2 {
            font-size: 37px;
            font-weight: bold;
            color: #fff;
            margin: 0;
        }

        .prize-text {
            text-align: center;
            color: #fff;
            /*margin: 10px 0 0;*/
            font-size: 29px;
        }

        .prize-amount {
            font-weight: bold;
        }

        .details-button {
            color: #FFFFFF;
            font-size: 14px;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            position: absolute;
            bottom: 5px;
            right: 20px;
        }

        .details-button:hover {
            color: #40C7F7;
        }

        .irs--round .irs-line {
            height: 6px;
        }

        .irs--round .irs-bar {
            height: 6px;
        }

        .snowflake {
            position: absolute;
            width: 15px;
            height: 15px;
            background: url('/design/orange_theme/img/holidays/snow/snowflake.png') no-repeat center center;;
            background-size: cover;
            animation: snowfall linear infinite;
            top: -15px;
        }

        @keyframes snowfall {
            to {
                transform: translateY(100vh);
            }
        }

        @media screen and (max-width: 768px){
            .details-button {
                bottom: -5px;
                right: -5px;
            }

            .gradient-box h3 {
                font-size: 1.4rem;
            }

            .prize-text {
                font-size: 1.4rem;
            }
        }

        @media screen and (max-width: 400px){

            .gradient-box h3 {
                font-size: 1.3rem;
            }

            .prize-text {
                font-size: 1.3rem;
                margin: 0;
            }
        }

        @media screen and (max-width: 376px){
            .gradient-box h3 {
                font-size: 1.2rem;
            }

            .prize-text {
                font-size: 1.2rem;
            }
        }

    </style>
{/literal}
<section id="loan">
    <div class="row">
        {*        <div class="col-12 mb-5 d-flex align-items-center justify-content-center">*}
        {*            <div class="snowflakes">*}
        {*                <div class="gradient-box">*}
        {*                    <h3>Страшно милый Новый год</h3>*}
        {*                    <p class="prize-text">Розыгрыш <span class="prize-amount">250 000 рублей!</span></p>*}
        {*                    <a class="details-button">Подробнее</a>*}
        {*                </div>*}
        {*            </div>*}
        {*        </div>*}

        <div class="col-12 mb-md-5 mb-3 d-flex align-items-center justify-content-center position-relative">
            <div class="bg-white text-center p-2 rounded-5 w-100">
                <div class="item">
                    <p class="mb-0 text-black fw-bold"><span class="text-warning">Бесплатный</span> заём с любой КИ</p>
                </div>
                <div class="item">
                    <p class="mb-0 text-black fw-bold"> Займы <span class='text-warning'>без страховок!</span></p>
                </div>
            </div>
            <div class="dots position-absolute bottom-0 start-50 translate-middle-x">
                <span class="dot active"></span>
                <span class="dot"></span>
            </div>
        </div>
        <div class="col-12 col-md">
            <div id="calculator_wrapper" class="row ms-md-5 row-cols-md-1 gy-2">
                {*<div class="col-12 text-center">
                    <h6 class="fw-bold text-danger fs-md-small">Руководитель службы заботы о клиентах</h6>
                    <p class="fs-md-small"><a class="text-muted" href="tel:89310094643">8-931-009-46-43</a></p>
                </div>*}
                <div class="row d-md-none">
                    <div class="col cloan-wrapper align-self-top d-md-block align-top">
                        <h3 class="fw-bolder mb-0 mb-md-4">Первый заём <br class="d-md-none"/> <span style="color: #FF7700; font-size: 1.25em">под 0%</span><br class="d-none d-md-block"/>
                            <div>
                                <div class="badge mt-2 mt-md-auto bg-warning font-size-small p-2"><small>Деньги у вас в <span class="get_money_time">13:16</span></small></div>
                            </div>
                        </h3>
                    </div>
                    <div class="col">
                        <div class="float-end" style="max-width: 175px">
                            <img style="max-width: 100%" src="https://boostra.ru/design/orange_theme/img/main_page/main_page_girl.png?v=1" alt="{$settings->site_name}" class="img-fluid" />
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md">
                    <form target="_blank" id="main_page_form" action="{if $user}user{else}init_user{/if}" method="get">
                        <div id="calculator" class="bg-white p-4 p-md-3 rounded border-1">
                            <input type="hidden" id="percent"
                                   value="{if $user_discount}{$user_discount->percent/100}{else}0{/if}"/>
                            <input type="hidden" id="max_period"
                                   value="{if $user_discount}{$user_discount->max_period}{else}{$max_period}{/if}"/>
                            <div class="row row-cols-1 gy-3">
                                <div class="col">
                                    <div class="row font-size-small">
                                        <div class="col">Выберите сумму</div>
                                        <div class="col-auto fw-bold font-size-small amount_current">30000 ₽</div>
                                    </div>
                                    <div>
                                        <div class="ion_slider_wrapper">
                                            <input type="text" data-step="1000" name="amount" value="30000"
                                                   data-min="1000" data-max="30000"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col mb-3 mt-5">
                                    <div class="row font-size-small">
                                        <div class="col">Выберите срок</div>
                                        <div class="col-auto fw-bold font-size-small period_current">10 дней</div>
                                    </div>
                                    <div>
                                        <div class="ion_slider_wrapper">
                                            <input type="text" name="period" value="5" data-min="5" data-max="16"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col {if $config->snow}snow-relative{/if}">
                                    {if $config->snow}
                                        <img class="snow-man"
                                             src="design/orange_theme/img/holidays/snow/snow_man.png?v=2"
                                             alt="Получить бесплатно"/>
                                    {/if}
                                    {if $user}
                                        <a href="{$lk_url}"
                                           target="_blank"
                                           onclick="sendMetric('reachGoal','main_page_get_zaim_new_design');"
                                           class="get_zaim_link btn btn-primary w-100 fs-6 btn-lg">Получить бесплатно2</a>
                                    {else}
                                        <button onclick="sendMetric('reachGoal','main_page_get_zaim_new_design');"
                                                type="submit"
                                                class="btn btn-primary w-100 fs-6 btn-lg"
                                                id="get_zaim">
                                            Получить бесплатно2
                                        </button>
                                    {/if}
                                </div>
                                <div class="col">
                                    <div class="row">
                                        <div class="col text-left">
                                            <p class="font-size-small mb-0">Вы берёте</p>
                                            <p class="fw-bold mb-0 amount_current">30000₽</p>
                                        </div>
                                        <div class="col text-left">
                                            <p class="font-size-small mb-0">До (включительно)</p>
                                            <p class="fw-bold mb-0 period_end_data">19 июля</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col text-left">
                                    <p class="font-size-small mb-0">Вы возвращаете</p>
                                    <p class="font-size-small mb-0"><s class="amount_discount">31200₽</s><span
                                                class="text-warning mx-2 amount_total">30000₽</span><span
                                                class="badge bg-success amount_percent">0%</span></p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-12 col-md">
                    <a href="/files/docs/zaim_0.pdf" target="_blank" class="text-dark font-size-small"><small>Положение
                            о займе под 0% для новых клиентов</small></a>

                    {if $settings->send_complaint}
                        <a onclick="sendMetric('reachGoal','main_page_report_button_click');" href="/user/login?page_action=send_complaint" class="btn btn-danger w-100 fs-6 btn-lg mt-2">Пожаловаться</a>
                    {/if}
                </div>
            </div>
        </div>
        <div class="col-12 col-md d-none d-md-block" style="position: relative;">
            <div class="block-messengers text-end">
                <a
                        class=""
                        href="https://telegram.me/boostra_bot"
                        target="_blank"
                ><img src="design/boostra_mini_norm/img/tg-48.png"/></a>
                <a
                        class=""
                        href="https://watbot.ru/w/mjj"
                        target="_blank"
                ><img src="design/boostra_mini_norm/img/viber-48.png"/></a>
                <a
                        class=""
                        href="https://vk.com/write-212426324"
                        target="_blank"
                ><img src="design/boostra_mini_norm/img/vk-48.png"/></a>
                <a
                        class=""
                        href="https://watbot.ru/w/mji"
                        target="_blank"
                ><img src="design/boostra_mini_norm/img/whatsapp-48.png"/></a>
            </div>
            <div class="row row-cols-1 gy-3">
                <div class="col">
                    <img src="https://boostra.ru/design/orange_theme/img/main_page/main_page_girl.png?v=1" style="max-width: 390px;" alt="{$settings->site_name}" class="img-fluid" />
                </div>
            </div>
        </div>
    </div>
    <div class="mt-3" id="app_download_buttons">
        <div class="row my-3 justify-content-center">
            <div class="col-auto">
                <a href="https://redirect.appmetrica.yandex.com/serve/965769215283862779" target="_blank">
                    <img style="max-width: 160px" class="img-fluid" src="design/boostra_mini_norm/img/nashstore_icon.png" alt="nashstore_icon"/>
                </a>
            </div>
            <div class="col-auto">
                <a href="https://redirect.appmetrica.yandex.com/serve/749596424009746204" target="_blank">
                    <img style="max-width: 160px" class="img-fluid" src="design/boostra_mini_norm/img/rustore_icon.png" alt="nashstore_icon"/>
                </a>
            </div>
        </div>
        <div class="row mt-3 justify-content-center">
            <div class="col-auto">
                <a class="btn btn-outline-secondary text-dark rounded-4" target="_blank" href="https://redirect.appmetrica.yandex.com/serve/461366054585709806">Скачать для Android</a>
            </div>
        </div>
    </div>
        <h3 class="fw-bolder mb-4 mt-3">Как это работает</h3>
        <div class="row gy-2 gy-md-0 gx-md-5 mb-4">
            <div class="col-12 col-md">
                <div class="bg-white p-3 rounded d-flex align-items-center d-md-block">
                    <div><p class="h4 text-warning fw-bold">01</p></div>
                    <div class="ms-4 ms-md-0">
                        <p class="h5 h6-md text-dark fw-bold mb-0 mb-md-2">Оформите заявку</p>
                        <p class="mb-0 mb-md-2">У Вас это займет не более 10 минут</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md">
                <div class="bg-white p-3 rounded d-flex align-items-center d-md-block">
                    <div><p class="h4 text-warning fw-bold">02</p></div>
                    <div class="ms-4 ms-md-0">
                        <p class="h5 h6-md text-dark fw-bold mb-0 mb-md-2">Дождитесь ответа</p>
                        <p class="mb-0 mb-md-2">Мы дадим ответ в течение 5 минут</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md">
                <div class="bg-white p-3 rounded d-flex align-items-center d-md-block">
                    <div><p class="h4 text-warning fw-bold">03</p></div>
                    <div class="ms-4 ms-md-0">
                        <p class="h5 h6-md text-dark fw-bold mb-0 mb-md-2">Мгновенно получите деньги</p>
                        <p class="mb-0 mb-md-2">На банковскую карту</p>
                    </div>
                </div>
            </div>
        </div>
        <section id="user-rules">
            <h3 class="fw-bolder mb-4">Требования к заёмщику</h3>
            <div class="d-grid">
                <div class="bg-white rounded p-3">
                    <div class="text-center">
                        <img src="design/{$settings->theme}/img/icons/manager.png" alt="Возраст 18 лет">
                        <p class="mb-0">Возраст <br class="d-md-none"/>18 лет</p>
                    </div>
                </div>
                <div class="bg-white rounded p-3">
                    <div class="text-center">
                        <img src="design/{$settings->theme}/img/icons/passport.png" alt="Паспорт гражданина РФ">
                        <p class="mb-0">Паспорт гражданина РФ</p>
                    </div>
                </div>
                <div class="bg-white rounded p-3">
                    <div class="text-center">
                        <img src="design/{$settings->theme}/img/icons/phone.png" alt="Активный номер телефона">
                        <p class="mb-0">Активный номер телефона</p>
                    </div>
                </div>
                <div class="bg-white rounded p-3">
                    <div class="text-center">
                        <img src="design/{$settings->theme}/img/icons/cards.png" alt="Именная банковская карта">
                        <p class="mb-0">Именная банковская карта</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="reviews mb-4">
            <h3 class="fw-bolder mb-4 mt-4">Отзывы</h3>
            <div class="owl-carousel">
                <div>
                    <div class="review-item bg-white rounded p-3">
                        <p class="text-dark">«Быстрое и надежное МФО без каких-либо подводных камней. Прошел легкую и
                            короткую регистрацию, подождал около 2-3 минут и получил одобрение на 9800 рублей. Еще попал на
                            акцию первого займа, поэтому даже проценты не надо платить»</p>
                        <p class="text-muted"><small>Анатолий, Самара</small></p>
                    </div>
                </div>
                <div>
                    <div class="review-item bg-white rounded p-3">
                        <p class="text-dark">«Обратилась впервые, посоветовал друг. Мне уже везде отказали из-за кредитной
                            истории, а тут получила одобрение с первого раза. Регистрация быстрая и понятная, вообще ничего
                            лишнего не просят заполнить, только самое необходимое»</p>
                        <p class="text-muted"><small>Евгения, Нижний Новгород</small></p>
                    </div>
                </div>
                <div>
                    <div class="review-item bg-white rounded p-3">
                        <p class="text-dark">«К компании никаких претензий нет. Деньги выплачивают быстро, а это очень важно
                            для меня. Процент в целом неплохой, есть куча компаний где проценты в разы больше. В будущем
                            если будет необходимость в деньгах, то обращусь снова сюда.»</p>
                        <p class="text-muted"><small>Анастасия, Тюмень</small></p>
                    </div>
                </div>
                <div>
                    <div class="review-item bg-white rounded p-3">
                        <p class="text-dark">«Одна из самых быстрых компаний. На все потратил максимум 15 минут, и деньги
                            уже были у меня на карте. Горячая линия помогла разобраться со всеми вопросами, особенно хочу
                            выделить Павла, который терпеливо отвечал на все мои вопросы.»</p>
                        <p class="text-muted"><small>Надежда, Смоленск</small></p>
                    </div>
                </div>
                <div>
                    <div class="review-item bg-white rounded p-3">
                        <p class="text-dark">«Мне одобрили с ужасной кредитной историей, причем с первого раза. Никаких
                            нареканий нет, отличная компания.»</p>
                        <p class="text-muted"><small>Марат, Челябинск</small></p>
                    </div>
                </div>
                <div>
                    <div class="review-item bg-white rounded p-3">
                        <p class="text-dark">«Просто качественное и надежное МФО. Никаких отрицательных впечатлений.
                            Обращаюсь к ним уже 5 раз, всегда одобряют. Причем с каждым займом статус лояльности повышается,
                            у меня уже платина. Всем советую, отличная компания!»</p>
                        <p class="text-muted"><small>Кристина, Санкт-Петербург</small></p>
                    </div>
                </div>
            </div>
        </section>
        <div class="row justify-content-between">
            <div class="col-md-5">
                <div class="bg-white border-1 p-3 rounded">
                    <p class="font-size-small lh-sm"><b>Онлайн-займы</b> — это микрокредиты на сумму от 1000 до 30 000
                        сроком от 1 до 16 дней
                        c процентной ставкой от 0% до 0.8% в день.
                        Заёмщик получает деньги на свою карту сразу после одобрения заявки, поданной онлайн на сайте.
                        Кредиты выдаются МКК (микрокредитными организациями), зарегистрированными в Центральном банке.</p>
                    <div class="text-more">
                        <a class="text-warning text-decoration-none text-more-btn" href="javascript:void(0);"><small>Читать
                                дальше <i class="bi bi-arrow-right"></i></small></a>
                        <div class="text-more-content" style="display: none;">
                            <p class="font-size-small lh-sm">Деятельность МКК регулируется законом «О микрофинансовой
                                деятельности".
                                Первый заём до 30 000 рублей в ООО МКК Аквариус клиент может получить бесплатно сроком до 16 дней.
                            </p>
                        </div>
                    </div>
                </div>
                <a href="/files/docs/zaim_0.pdf" target="_blank"
                   class="my-3 w-100 btn text-center fw-bold btn-block btn-lg bg-orange-gradient">
                    <div class="lh-md-1 py-md-1">Условия акции<br class="d-block d-md-none"> «Первый заём»</div>
                </a>
                <p class="lh-1 font-size-small"><small>Оформить заём можно круглосуточно, в выходные и праздники. Подать
                        заявку вы можете не только с компьютера, но и с любого мобильного устройства</small></p>
            </div>
            <div class="col-md-5">
                <div class="row row-cols-1 gy-2 gy-md-3 faq">
                    <div class="col">
                        <div class="bg-white p-3 rounded">
                            <div role="button" class="d-flex fw-bold justify-content-between align-items-center"
                                 data-bs-toggle="collapse" data-bs-target="#faq_1" aria-expanded="false"
                                 aria-controls="faq_1">
                                <div>
                                    Кто может взять микрозайм?
                                </div>
                                <div>
                                    <i class="bi bi-plus-lg text-warning fs-4"></i>
                                </div>
                            </div>
                            <div class="collapse multi-collapse" id="faq_1">
                                <div class="text-muted border-top">
                                    <p class="font-size-small lh-sm mt-2">Наши требования к заёмщикам минимальны: возраст от
                                        18 лет, наличие паспорта гражданина РФ,
                                        активный номер телефона, личная банковская карта.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="bg-white p-3 rounded">
                            <div role="button" class="d-flex fw-bold justify-content-between align-items-center"
                                 data-bs-toggle="collapse" data-bs-target="#faq_2" aria-expanded="false"
                                 aria-controls="faq_2">
                                <div>
                                    Как повысить вероятность одобрения?
                                </div>
                                <div>
                                    <i class="bi bi-plus-lg text-warning fs-4"></i>
                                </div>
                            </div>
                            <div class="collapse multi-collapse" id="faq_2">
                                <div class="text-muted border-top">
                                    <p class="font-size-small lh-sm mt-2">Наши требования к заемщикам и клиентам гораздо
                                        лояльнее, чем в банках.
                                        И даже если вам уже отказали несколько банков и микрофинансовых организаций - мы
                                        можем одобрить.</p>
                                    <p class="font-size-small lh-sm">При подаче заявки вы также можете воспользоваться
                                        услугой предоставления персонального кредитного рейтинга.
                                        Информация, полученная в результате анализа, положительно сказывается на вероятности
                                        получения займа</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="bg-white p-3 rounded">
                            <div role="button" class="d-flex fw-bold justify-content-between align-items-center"
                                 data-bs-toggle="collapse" data-bs-target="#faq_3" aria-expanded="false"
                                 aria-controls="faq_3">
                                <div>
                                    Как избежать просрочки?
                                </div>
                                <div>
                                    <i class="bi bi-plus-lg text-warning fs-4"></i>
                                </div>
                            </div>
                            <div class="collapse multi-collapse" id="faq_3">
                                <div class="text-muted border-top">
                                    <p class="font-size-small lh-sm mt-2">Информация о дате платежа указана в личном
                                        кабинете заёмщика.
                                        Кроме того мы присылаем сообщение с напоминаем. И также рекомендуем отметить
                                        плановую дату платежа в своём календаре.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</section>
<div class="modal fade" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Сделай лето жарче!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Открой новый договор займа в период</p>
                <p>с 15 июня по 30 июля</p>
                <p>Закрой его или пролонгируй без просрочки до 15 августа</p>
                <p>Подпишись на группу ВКонтакте Бустра-займы</p>
                <p><b>Участвуй в розыгрыше 250 000 рублей!</b></p>
                <p><a href="https://vk.com/boostra_zaim" class="btn btn-group-link" target="_blank">Перейти в группу</a>
                </p>
            </div>
        </div>
    </div>
</div>

{include file="design/boostra_mini_norm/html/modals/new_year_modal.tpl"}
<script src="design/{$settings->theme}/js/login.app.js?v=2.55" type="text/javascript"></script>
<script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
<script src="design/{$settings->theme}/js/jquery.validate.min.js?v=2.01" type="text/javascript"></script>
<script src="design/orange_theme/js/calculate.js?v=1.008" type="text/javascript"></script>
<script async src="https://lib.usedesk.ru/secure.usedesk.ru/widget_161404_53920.js"></script>

{literal}
    <style>
        .dots {
            margin-bottom: -20px;
        }

        .dot {
            height: 8px;
            width: 8px;
            background-color: #FF8500;
            border-radius: 50%;
            display: inline-block;
            transition: background-color 0.6s ease;
        }

        .active, .dot:hover {
            background-color: #0C1021;
        }
    </style>
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            const snowflakeContainer = document.querySelector('.snowflakes');

            for (let i = 0; i < 50; i++) {
                const snowflake = document.createElement('div');
                snowflake.className = 'snowflake';
                snowflake.style.left = Math.random() * 100 + '%';
                snowflake.style.animationDuration = Math.random() * 8 + 2 + 's';
                snowflake.style.animationDelay = Math.random() * 2 + 's';
                snowflakeContainer.appendChild(snowflake);
            }
        });

        function clickHunter() {
            {/literal}
                {if $settings->click_hunter && $settings->click_hunter['status']}
                    setTimeout(() => {
                        window.location.href = "{$settings->click_hunter['url']}";
                    }, 1000)
                {/if}
            {literal}
        }

        $(document).ready(function () {
            var slideIndex = 0;
            var slides = $(".item");
            var dots = $(".dot");
            var timer;

            showSlides();

            function showSlides() {
                for (var i = 0; i < slides.length; i++) {
                    slides[i].style.display = "none";
                }
                slideIndex++;
                if (slideIndex > slides.length) {
                    slideIndex = 1;
                }
                for (var i = 0; i < dots.length; i++) {
                    dots[i].className = dots[i].className.replace(" active", "");
                }
                slides[slideIndex - 1].style.display = "block";
                dots[slideIndex - 1].className += " active";
                timer = setTimeout(showSlides, 5000);
            }

            dots.click(function () {
                var index = $(this).index();
                slideIndex = index + 1;
                clearTimeout(timer);
                showSlides();
            });
            slides.click(function () {
                var index = $(this).index();
                slideIndex = index + 1;
                clearTimeout(timer);
                showSlides();
            });

            $("#main_page_form").on('submit', function (e) {
                clickHunter();
            });

            $(".get_zaim_link").on('click', function (e) {
                clickHunter();
            });
        });

    </script>
{/literal}
