{$wrapper = '' scope=parent}


<!DOCTYPE html>
<html>
<head>
    <base href="https://boostra.ru/"/>
    <title>Быстрые займы - Бустра</title>


    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="title" content="" />
    <meta name="description" content="Микрозаймы по городам России. Быстрые деньги для занятых людей." />
    <meta name="keywords"    content="Быстрые займы - Бустра" />
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <link rel="canonical" href="https://boostra.ru"/>
    <link rel="shortcut icon" type="image/ico" href="design/boostra_mini_norm/img/favicon.ico">

    <meta property="og:locale" content="ru_RU" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="" />
    <meta property="og:url" content="http://boostra.ru" />
    <meta property="og:site_name" content="boostra" />
    <meta property="og:image" content="design/boostra_mini_norm/img/favicon.png" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:description" content="Микрозаймы по городам России. Быстрые деньги для занятых людей." />
    <meta name="twitter:title" content="" />
    <meta name="twitter:image" content="design/boostra_mini_norm/img/favicon192x192.png" />

    <link rel="icon" href="design/boostra_mini_norm/img/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" href="design/boostra_mini_norm/img/favicon192x192.png" sizes="192x192" />
    <link rel="apple-touch-icon-precomposed" href="design/boostra_mini_norm/img/favicon180x180.png" />
    <meta name="msapplication-TileImage" content="design/boostra_mini_norm/img/favicon270x270.png" />
    <link rel="image_src" href="design/boostra_mini_norm/img/favicon.png" />


    <link rel="stylesheet" type="text/css" href="design/orange_theme/css/ion.rangeSlider.css?v=1.00"/>

    <link rel="stylesheet" type="text/css" href="/design/orange_theme/css/bootstrap/bootstrap-icons-1.9.1/bootstrap-icons.css"/>
    <link rel="stylesheet" type="text/css" href="/design/orange_theme/css/bootstrap/bootstrap.min.css"/>
    <link rel="stylesheet" type="text/css" href="/design/orange_theme/css/style.css?v=1.024"/>
    <link rel="stylesheet" type="text/css" href="/design/orange_theme/css/media.css?v=1.008" />
    <link rel="stylesheet" type="text/css" href="/design/orange_theme/css/modal.css?v=1.00" />
    <link rel="stylesheet" type="text/css" href="/design/orange_theme/css/magnific-popup.css?v=1.00" />


    <script src="/design/boostra_mini_norm/js/jquery-2.1.3.min.js" type="text/javascript"></script>
    <script src="/design/boostra_mini_norm/js/jquery.magnific-popup.min.js" type="text/javascript"></script>

    <!--script src="https://cfv4.com/landings.js"></script-->

    <meta name="cmsmagazine" content="6f3ef3c26272e3290aa0580d7c8d86ce" />

    <script>
        window.siteConfig = {
            js_config_is_dev: 0            }

        var is_developer = 0;

        var is_admin = 0;

        var is_CB = 0;
    </script>

    <script>
        var BASE_PERCENTS = 0.8;
    </script>

    <!-- Feedback form captcha -->
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {

            // Render
            window.recaptchaOnloadCallback = function () {
                if ($('#recaptcha_feedback').length > 0) {
                    grecaptcha.render('recaptcha_feedback', { 'sitekey': "6LeXaIMcAAAAAB83AxY4R6bd0K5wBp4_RAt730DE" });
                }
            };
        });
    </script>
    <script src='https://www.google.com/recaptcha/api.js?onload=recaptchaOnloadCallback&render=explicit' async defer></script>

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

</head>
<body >


<header class="bg-white py-2 mb-md-5 mb-4">
    <nav class="navbar bg-white">
        <div class="container">
            <div
                    class="row w-100 row-cols-auto gy-md-2 gy-xl-0 align-items-center justify-content-between d-none d-md-flex">
                <div class="col-xl-2 col">

                    <img style="max-width: 160px" src="design/boostra_mini_norm/img/logo.svg" alt="boostra"
                         class="img-fluid" />
                </div>


                <div class="d-flex flex-column align-items-center gap-2 col-xl-auto col">
                    <div class="col-xl-auto col">
                        <a href="user/login" type="button" class="btn py-0 btn-primary border-2">
                            <small>Войти</small>
                        </a>
                    </div>
                </div>
            </div>

            <div class="row w-100 justify-content-between mx-auto d-flex d-md-none">

                <div class="col">

                    <img style="max-width: 150px;" src="design/boostra_mini_norm/img/logo.svg" alt="boostra"
                         class="img-fluid" />

                </div>
                <div class="col-auto pe-0 align-self-center">
                    <a href="user/login" type="button" class="btn py-0 btn-primary btn-sm py-0">
                        <small>Войти</small>
                    </a>
                </div>
            </div>

        </div>
    </nav>
</header>

<div class="container">
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
        .first_loan {
            display:none;
        }

        @media screen and (max-width: 600px) {
            .apps-btn-block{
                display: none;
            }

            .first_loan{
                display: block;
            }
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

    <section id="loan">
        <div class="row">

            <div class="col-12 mb-md-5 mb-3 d-flex align-items-center justify-content-center position-relative">
                <div class="bg-white text-center p-2 rounded-5 w-100">
                    <div class="item">
                        <p class="mb-0 text-black fw-bold"><span class="text-warning">
                        Кредитор в реестре МФО</span></p>
                    </div>
                    <div class="item">
                        <p class="mb-0 text-black fw-bold"><span class="text-warning">Бесплатный</span> займ с любой КИ</p>
                    </div>
                    <div class="item">
                        <p class="mb-0 text-black fw-bold"> Займы <span class='text-warning'>без страховок!</span></p>
                    </div>
                </div>
                <div class="dots position-absolute bottom-0 start-50 translate-middle-x">
                    <span class="dot active"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>
            </div>
            <div class="col-12 col-md" style="margin-top: 20px;">
                <div id="calculator_wrapper" class="row ms-md-5 row-cols-md-1 gy-2">
                    <div class="col-12 col-md">
                        <form target="_blank" id="main_page_form" action="init_user" method="get">
                            <span class="ion_slider_wrapper" style="display: none;">
                                                <input type="text" name="period" value="16" data-min="5" data-max="16"/>
                                            </span>
                            <div id="calculator" class="bg-white p-4 p-md-3 rounded border-1">
                                <input type="hidden" id="percent"
                                       value="0"/>
                                <input type="hidden" id="max_period"
                                       value="5"/>

                                <div class="col mb-3 mt-5">

                                    <div>
                                            <span style="text-align: center;">
                                                <h4 style="margin-top:-30px;">Первый займ <span style="font-weight:bold; color: orange;">бесплатно</span></h4><br>
                                            </span>
                                    </div>
                                </div>
                                <div class="row row-cols-1 gy-3">

                                    <div class="col">
                                        <div class="row font-size-small">
                                            <div class="col" style="font-size:20px;">Выберите сумму</div>
                                            <div class="col-auto fw-bold font-size-small amount_current" style="font-size:20px;">30000 ₽</div>
                                        </div>
                                        <div>
                                            <div class="ion_slider_wrapper">
                                                <input type="text" data-step="1000" name="amount" value="30000"
                                                       data-min="1000" data-max="30000"/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col "><br><br>
                                        <button onclick="sendMetric('reachGoal','main_page_get_zaim_new_design');"
                                                type="submit"
                                                class="btn btn-primary w-100 fs-10 btn-lg"
                                                id="get_zaim" >
                                            Получить бесплатно
                                        </button>
                                        <br>
                                    </div>
                                    <div class="col">
                                        <div class="row">
                                            <br><br>
                                            <div class="col text-left">
                                                <br>
                                                <p class="font-size-small mb-0">Вы берёте</p>
                                                <p class="fw-bold mb-0 amount_current">30000₽</p>
                                            </div>
                                            <!--<div class="col text-left">
                                                <br><br>
                                                <p class="font-size-small mb-0">До (включительно)</p>
                                                <p class="fw-bold mb-0 period_end_data">+16 дней</p>
                                            </div> -->

                                            <div class="col text-left"><br>
                                                <p class="font-size-small mb-0">Вы возвращаете</p>
                                                <p class="font-size-small mb-0"><s class="amount_discount">30570₽</s><span
                                                            class="text-warning mx-2 amount_total">30000₽</span><span
                                                            class="badge bg-success amount_percent">0%</span></p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-12 col-md">
                        <a href="/files/docs/zaim_0.pdf" target="_blank" class="text-dark font-size-small"><small>Условия акции займ под 0%*</small></a><br>

                    </div>
                </div>
            </div>
            <div class="col-12 col-md d-none d-md-block" style="position: relative;">

                <div class="row row-cols-1 gy-3">
                    <div class="col">
                        <img src="https://boostra.ru/design/orange_theme/img/main_page/main_page_girl.png?v=1" style="max-width: 390px;" alt="Интернет займы от Бустры" class="img-fluid" />
                    </div>
                </div>
            </div>
        </div>


        <div class="first_loan"><br>
            <h3 class="fw-bolder mb-4 mt-3">Онлайн займы на карту. Первый займ без процентов</h3>
            <hr><br>
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
                        <img src="design/boostra_mini_norm/img/icons/manager.png" alt="Возраст 18 лет">
                        <p class="mb-0">Возраст <br class="d-md-none"/>18 лет</p>
                    </div>
                </div>
                <div class="bg-white rounded p-3">
                    <div class="text-center">
                        <img src="design/boostra_mini_norm/img/icons/passport.png" alt="Паспорт гражданина РФ">
                        <p class="mb-0">Паспорт гражданина РФ</p>
                    </div>
                </div>
                <div class="bg-white rounded p-3">
                    <div class="text-center">
                        <img src="design/boostra_mini_norm/img/icons/phone.png" alt="Активный номер телефона">
                        <p class="mb-0">Активный номер телефона</p>
                    </div>
                </div>
                <div class="bg-white rounded p-3">
                    <div class="text-center">
                        <img src="design/boostra_mini_norm/img/icons/cards.png" alt="Именная банковская карта">
                        <p class="mb-0">Именная банковская карта</p>
                    </div>
                </div>
            </div>
        </section>



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

    <div id="new-year-modal-content" class="mfp-hide new-year-modal-content">
        <a id="close-modal" class="custom-close">X</a>
        <div class="new-year-modal-container">
            <img src="design/boostra_mini_norm/img/content/new_year/left_character.png" alt="Левый персонаж" class="new-year-character new-year-character-left">
            <img src="design/boostra_mini_norm/img/content/new_year/right_character.png" alt="Правый персонаж" class="new-year-character new-year-character-right">


            <div class="new-year-modal-text">
                <h4 style="font-weight: 700">Страшно милый</h4>
                <h4 style="font-weight: 700" class="new-year-modal-title">Новогодний розыгрыш 250 000 рублей</h4>
                <p><strong>Когда?</strong> 16 января 2024 года</p>
                <p><strong>Где?</strong> В официальном сообществе <a href="https://vk.com/boostra_zaim">Boostra - займы на карту</a></p>
                <p><strong>Как определим победителя?</strong> Случайным образом с помощью специального сервиса Randomus</p>
                <p><strong>Как стать участником?</strong></p>
                <ul>
                    <li><strong>Участник 1 уровня.</strong> Доступно участие в розыгрыше 10 призов по 10 000 рублей.
                        <p><strong>Что нужно сделать?</strong> Открыть 1 не первый договор займа, пользоваться займом не
                            менее
                            16 дней и без просрочек, закрыть до 15.01.2024 г.</p>
                    </li>
                    <li><strong>Участник 2 уровня.</strong> Доступно участие в розыгрыше 5 призов по 30 000 рублей.
                        <p><strong>Что нужно сделать?</strong> Выполнить условия Уровня 1. Открыть ещё 1 договор займа,
                            пользоваться займом не менее 16 дней и без просрочек, закрыть до 15.01.2024 г.</p>
                    </li>
                </ul>
            </div>
            <span>Забирай займы  - получай денежные призы!</span>
        </div>
        <div class="character-img-wrap">

            <img src="design/boostra_mini_norm/img/content/new_year/down_character.svg" alt="Нижний персонаж" class="new-year-character new-year-character-bottom">
        </div>
        <div class="new-year-snowflakes"></div>
    </div>

    <style>
        #close-modal {
            cursor: pointer;
            position: absolute;
            top: 15px;
            right: 15px;
            color: #000;
        }

        #new-year-modal-content {
            position: relative;
            background-color: #fff;
            color: #000;
            padding: 20px 40px;
            max-width: 500px;
            margin: 20px auto;
            border-radius: 10px;
            overflow: hidden;
        }

        .new-year-character {
            position: absolute;
        }

        .new-year-character-left {
            left: -40px;
            top: 37%;
            transform: translateY(-50%);
            width: 50px;
        }

        .new-year-character-right {
            right: -40px;
            top: 30%;
            transform: translateY(-50%);
            width: 102px;
        }

        .character-img-wrap {
            display: flex;
            justify-content: end;
            margin-right: 63px;
            margin-top: 40px;
        }

        .new-year-character-bottom {
            position: unset;
            width: 63px;
        }

        .new-year-modal-container {
            position: relative;
        }

        .new-year-modal-text h4 {
            text-align: center;
        }

        .new-year-modal-text a {
            color: #40C7F7;
        }

        .new-year-modal-text .new-year-modal-title {
            margin-bottom: 20px;
        }

        .new-year-modal-text p {
            margin: 0 0 10px;
        }

        .new-year-modal-text ul {
            list-style-type: none;
            padding: 0;
        }

        .new-year-modal-text li {
            margin-bottom: 10px;
        }

        .new-year-modal-text li p {
            margin: 0;
        }

        .new-year-modal-container span {
            display: flex;
            justify-content: center;
            text-align: center;
            font-weight: 600;
        }

        .new-year-snowflakes {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .new-year-snowflake {
            position: absolute;
            width: 25px;
            height: 25px;
            background: url('/design/boostra_mini_norm/img/content/new_year/snowflake_big.svg') no-repeat center center;
            background-size: cover;
            animation: new-year-snowfall linear infinite;
            top: -15px;
        }

        @keyframes new-year-snowfall {
            to {
                transform: translateY(100vh);
            }
        }

        @media screen and (max-width: 768px){
            .new-year-character-left {
                left: -40px;
                top: 35%;
                width: 40px;
            }

            .new-year-character-right {
                right: -40px;
                top: 30%;
                width: 90px;
            }
        }

    </style>


    <script src="design/boostra_mini_norm/js/login.app.js?v=2.56" type="text/javascript"></script>
    <script src="design/boostra_mini_norm/js/jquery.inputmask.min.js" type="text/javascript"></script>
    <script src="design/boostra_mini_norm/js/jquery.validate.min.js?v=2.01" type="text/javascript"></script>
    <!--<script src="design/orange_theme/js/calculate.js?v=1.006" type="text/javascript"></script> -->
    <script async src="https://lib.usedesk.ru/secure.usedesk.ru/widget_161404_53920.js"></script>
    <script>
        $(document).ready(function(){
            function setTimeGetMoney() {
                const date = new Date;
                date.setMinutes(date.getMinutes() + 20);
                let minutes = date.getMinutes();
                if (minutes < 10) {
                    minutes = '0' + minutes;
                }
                $(".get_money_time").text(date.getHours() + ':' + minutes);
            }

            setTimeGetMoney();

            $(".ion_slider_wrapper input").ionRangeSlider({
                skin: "round",
                type: "single",
                onStart: function(data) {
                    calculate(data);
                },
                onChange: function(data) {
                    calculate(data);
                }
            });

            function calculate(data) {


                if (window.innerWidth < 576) {
                    if (data.from === data.min && data.input.attr('name') === 'period') {
                        $(data.input).closest('.ion_slider_wrapper').find('.irs-min').hide();
                    } else {
                        $(data.input).closest('.ion_slider_wrapper').find('.irs-min').show();
                    }
                }

                let element_current = $('.' + data.input.attr('name') + '_current'),
                    postfix = data.input.attr('name') === 'amount' ? ' ₽' : ' РґРЅРµР№';
                element_current.text(data.from + postfix);

                let amount = parseInt($('.ion_slider_wrapper [name="amount"]').val()),
                    period = parseInt($('.ion_slider_wrapper [name="period"]').val()),
                    percent = parseFloat(BASE_PERCENTS) / 100,
                    discount_percent = parseFloat($("#percent").val()),
                    discount_period = parseInt($("#max_period").val()),
                    now = new Date;

                let percent_calculate = period > discount_period ? percent : discount_percent,
                    total = Math.round(amount * period * percent_calculate + amount),
                    total_without_discount = Math.round(amount * period * percent + amount);

                window.percent_calculate = percent_calculate;
                $.cookie('percent_calculate', percent_calculate, { expires: 365, path: '/' });

                /*if (period > discount_period) {
                    $("#calculator_wrapper").get(0).style.setProperty("--calculate_green", "25, 135, 84");
                    $("#get_zaim").text('Получить').removeClass('orange');
                    $(".amount_discount").hide();
                    $("#calculator .amount_percent").removeClass('orange');
                } else {
                    $("#calculator_wrapper").get(0).style.removeProperty("--calculate_green");
                    $("#get_zaim").text('Получить бесплатно').addClass('orange');
                    $(".amount_discount").text(total_without_discount + ' ₽').show();
                    $("#calculator .amount_percent").addClass('orange');
                }*/

                $("#calculator_wrapper").get(0).style.removeProperty("--calculate_green");
                $("#get_zaim").text('Получить').removeClass('orange');
                $(".amount_discount").text(total_without_discount + ' ₽').show();
                $("#calculator .amount_percent").addClass('orange');


                const payDate = new Date;
                payDate.setDate(now.getDate() + period);

                const month = [
                    'Января', 'Февраля', 'Марта', 'Апреля',
                    'Мая', 'Июня', 'Июля', 'Августа',
                    'Сентября', 'Октября', 'Ноября', 'Декабря'
                ][payDate.getMonth()];

                $("#calculator .amount_total").html(amount + ' ₽');
                $("#calculator .period_end_data").text(payDate.getDate() + ' ' + month);
                $("#calculator .amount_percent").text('0%');
            }
        });
    </script>

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

            setTimeout(() => {
                window.location.href = "https://zaimirubll.ru/onlain_zaime_pod_0";
            }, 1000)

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


</div>


<footer class="bg-white mt-5">
    <div class="container">
        <div class="row justify-content-between align-items-center text-md-start text-center">
            <div class="col-md col-12 order-2 order-md-1 mt-4 mt-md-0">
                <p class="lh-sm font-size-small mb-0">
                <div class="mb-2 small">Наши партнеры:</div>



                <div class="mb-2 small">
                    ООО МКК «Аквариус»
                    Полное (фирменное) наименование
                    Общество с ограниченной ответственностью Микрокредитная компания «Аквариус»
                    Краткое (фирменное) наименование
                    ООО МКК «Аквариус»
                    ИНН
                    9714011290
                    ОГРН
                    1237700365506
                    Адрес в пределах местонахождения
                    125319, ВН.ТЕР.Г. МУНИЦИПАЛЬНЫЙ ОКРУГ АЭРОПОРТ, УЛ. АКАДЕМИКА ИЛЬЮШИНА, Д. 12, ПОМЕЩ. 2/1

                    Учетный номер (номер лицензии, номер записи в реестре)
                    23-030-45-009968
                    Дата начала действия права (лицензии)
                    10.07.2023

                </div>
                <div class="mb-2 small">
                    МКК ООО «Бустра»
                    ИНН 6317102210
                    ОГРН 1146317004030
                    Сведения об МКК ООО «Бустра» исключены из государственного реестра микрофинансовых организаций на
                    основании Приказа Банка России № ОД-138 от 30.01.2024 в соответствии с пунктами 1, 4 части 1.1. и
                    частью 1.1 статьи 7 и пунктом 8 части 4 статьи 14 Федерального закона от 02.07.2010 № 151-ФЗ «О
                    микрофинансовой деятельности и микрофинансовых организациях». В соответствии с частью 5 статьи 7
                    Федерального закона от 02.07.2010 № 151-ФЗ «О микрофинансовой деятельности и микрофинансовых
                    организациях» все ранее заключенные МКК ООО «Бустра» договоры микрозайма сохраняют юридическую силу.
                </div>
                <div class="mb-2 small">Официальный сайт Банка России - <a href="https://cbr.ru/" id="cbr_link">https://cbr.ru/</a>
                </div>



                </p>
                <div class="mb-2 small">© 2024, ООО «ФИНТЕХ-МАРКЕТ».
                    <br/>При использовании материалов гиперссылка на boostra.ru обязательна.
                    <br/>ИНН 6317164496, ОГРН 1236300023849. 443001, Самарская область, г.о. Самара, вн.р-н Ленинский, г
                    Самара, ул Ярмарочная, д. 3, кв. 62.
                    <br/>ООО «ФИНТЕХ-МАРКЕТ» осуществляет деятельность в сфере IT
                </div>
                <button class="btn btn-primary orange action-scroll_to" scroll_to="#calculator_wrapper"
                        id="scroll_to_calculator_footer_button" type="button">Заявка на займ
                </button><br>

                <div class="row text-md-center logo_payment" style="margin: 0 auto; width: 380px;">
                    <div class="col-md-auto col">
                        <img class="img-fluid" src="/design/boostra_mini_norm/img/best2pay-logo.svg"
                             alt="Best2Pay" style="height: 30px;"/>
                    </div>
                    <div class="col-md-auto col">
                        <img class="img-fluid" src="/design/boostra_mini_norm/img/visa.svg" alt="VISA"/>
                    </div>
                    <div class="col-md-auto col">
                        <img class="img-fluid" src="/design/boostra_mini_norm/img/master-card.svg"
                             alt="MasterCard" style="height: 35px"/>
                    </div>
                    <div class="col-md-auto col">
                        <img class="img-fluid" src="/design/orange_theme/img/design/mir.svg" alt="Mir"/>
                    </div>
                </div>
                <br>


                <div class="mb-2 mt-5 small">
                </div>
            </div>

        </div>
    </div>
</footer>


</body>
<!-- Yandex.Metrika counter -->

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

        userParams: {
            utm_source: '',
            has_orders: 0,
            webmaster_id: '',
        }

    });</script>
<noscript>
    <div><img src="https://mc.yandex.ru/watch/45594498" style="position:absolute; left:-9999px;" alt=""/></div>
</noscript>
<!-- /Yandex.Metrika counter -->


<script src="/design/orange_theme/js/ion.rangeSlider.min.js" type="text/javascript"></script>




<script src="/design/boostra_mini_norm/js/b2p.app.js?v=1.03" type="text/javascript"></script>
<script src="/js/jquery.cookie.min.js" type="text/javascript"></script>
<script src="/design/boostra_mini_norm/js/metrics.js?v=1.007" type="text/javascript"></script>
<script src="/design/orange_theme/js/common.js?v=1.0017" type="text/javascript"></script>
<script src="/js/functions.js?v=1.0001" type="text/javascript"></script>

<script src="/design/orange_theme/js/bootstrap/bootstrap.bundle.min.js" type="text/javascript"></script>


</html>
