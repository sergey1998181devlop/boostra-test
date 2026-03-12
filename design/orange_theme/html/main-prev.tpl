<body>
<div class="site-menu">
    <div class="site-item logo">
        <img src="design/boostra_mini_norm/img/logo.png">
    </div>
    <div class="site-item">
        <div class="items">
            {*<div class="menu-item">
                <a href="info_partners">Партнеры</a>
            </div>*}
            <div class="menu-item">
                <a href="info#info">Условия</a>
            </div>
            <div class="menu-item">
                <a href="info#contacts">Связаться с нами</a>
            </div>
            <div class="menu-item">
                <a href="info#demands">Вопросы и ответы</a>
            </div>
            <div class="menu-item login-action" onclick="ym(45594498,'reachGoal','click_voity')">
                <img src="design/boostra_mini_norm/img/login-icon-1.png" class="login-icon">
                <a class="login-label" href="/user/login">Войти</a>
            </div>
        </div>
    </div>
</div>
<div class="site-menu__mobile">
    <div class="site-menu__mobile__header">
        <div class="logo">
            <img src="design/boostra_mini_norm/img/logo.png">
        </div>
        <div class="actions-group">
            <a class="login-action-btn" href="/user/login">
                <img src="design/boostra_mini_norm/img/login-icon-mobile.png">
            </a>
            <div class="burger">
                <div class="layer-1"></div>
                <div class="layer-2"></div>
            </div>
        </div>
    </div>
    <div class="site-menu__mobile__body">
        <a class="login-button" href="/user/login"  onclick="ym(45594498,'reachGoal','click_voity')">
            Личный кабинет
        </a>
        <div class="actions">
            {*<div class="actions__item">
                <a href="info_partners">Партнеры</a>
            </div>*}
            <div class="actions__item">
                <a href="info#info">Условия</a>
            </div>
            <div class="actions__item">
                <a href="info#contacts">Связаться с нами</a>
            </div>
            <div class="actions__item">
                <a href="info#demands">Вопросы и ответы</a>
            </div>
        </div>
        <hr>
        <div class="contacts-email">
            <div class="contacts-email__value">info@boostra.ru</div>
            <div class="contacts-email__label">Электронная почта для обращения</div>
        </div>
        <div class="contacts-other">
            <div class="phone">+7 800 333 30 73</div>
            <div class="phone">+7 800 350 66 91</div>
            <div class="phone-label">Круглосуточный клиентский сервис</div>
            <div class="social-networks">
{*                <a href="https://telegram.me/boostra_bot">*}
{*                    <img src="design/boostra_mini_norm/img/landing/menu/dzen.png">*}
{*                </a>*}
                <a href="https://watbot.ru/w/mjj">
                    <img src="design/boostra_mini_norm/img/landing/menu/whatsapp.png">
                </a>
                <a href="https://vk.com/write-212426324">
                    <img src="design/boostra_mini_norm/img/landing/menu/vk.png">
                </a>
{*                <a href="https://telegram.me/boostra_bot">*}
{*                    <img src="design/boostra_mini_norm/img/landing/menu/ok.png">*}
{*                </a>*}
{*                <a href="https://telegram.me/boostra_bot">*}
{*                    <img src="design/boostra_mini_norm/img/landing/menu/yt.png">*}
{*                </a>*}
                <a href="https://telegram.me/boostra_bot">
                    <img src="design/boostra_mini_norm/img/landing/menu/tg.png">
                </a>
            </div>
        </div>
    </div>
</div>
<div class="calculator-group">
    <div class="before-information">
        <div class="main-label">
            <div class="main-label-item" id="credit-time">Деньги у Вас в 00:00</div>
            <div class="main-label-item">Займы под 0%</div>
        </div>
        <div class="main-title">
            Онлайн займы на карту до 30 000 рублей за 5 минут
        </div>
        <p class="main-description">
            — Более 1 000 000 клиентов<br>
            — Одни из лидеров по выдаче займов в России <br>
            — Мгновенное зачисление денежных средств
        </p>
        <div class="payment-systems">
            <img src="design/boostra_mini_norm/img/landing/payment-systems/visa.png">
            <img src="design/boostra_mini_norm/img/landing/payment-systems/mastercard.png">
            <img src="design/boostra_mini_norm/img/landing/payment-systems/mir.png">
            <img src="design/boostra_mini_norm/img/landing/payment-systems/b2p.png">
        </div>
    </div>
    <div class="calculator" id="calculator">
        <input type="hidden" id="percent"
               value="{if $user_discount}{$user_discount->percent/100}{else}0{/if}"/>
        <input type="hidden" id="max_period"
               value="{if $user_discount}{$user_discount->max_period}{else}{$max_period}{/if}"/>
        <div class="calculator-title">
            Первый заём
            <div class="active-label"> бесплатно</div>
        </div>
        <p class="calculator-motivation-phrase">Высокое одобрение заявок - до 99%</p>
        <div class="calculator-input-label">
            <div class="calculator-input-label-description">Выберите сумму</div>
            <div class="calculator-input-label-sum" id="calc-sum-label">30 000 ₽</div>
        </div>
        <input class="calc-amount ion_slider_wrapper" type="text" id="calc-sum" data-step="1000" data-max="30000"
               value="30000"
               data-min="1000">

        <div class="calculator-label-range">
            <p>1 000</p>
            <p>30 000</p>
        </div>
        <div class="calculator-input-label" style="display: none;">
            <div class="calculator-input-label-description">Выберите срок</div>
            <div class="calculator-input-label-sum" id="calc-period-label">16 дней</div>
        </div>
        <input class="calc-amount ion_slider_wrapper" id="calc-period" type="text" data-step="1" data-max="16"
               data-min="5"
               value="16">
        <div class="calculator-label-range">
            <p>5</p>
            <p>16</p>
        </div>
        <div class="credit-details">
            <div class="credit-details__item">
                <div class="credit-details__item__label">
                    Вы вернете
                </div>
                <div class="credit-details__item__value" id="return-sum">
                    33 840 ₽
                </div>
            </div>
            <div class="credit-details__item">
                <div class="credit-details__item__label">
                    Ставка
                </div>
                <div class="credit-details__item__value" id="percent-value">
                     {$base_percents} %
                    Без процентов *
                </div>
            </div>
        </div>
        <div class="submit-credit" onclick="submitOrder()">
            Получить бесплатно
        </div>
    </div>
</div>
<div class="information-container">
    <div class="information-container__title">
        <p>Как получить заём?</p>
    </div>
    <div class="credit-get-steps">
        <div class="credit-get-items">
            <div class="credit-get-item">
                <div class="credit-get-item__decoration">
                    <div class="credit-get-item__decoration__item">Шаг 1</div>
                    <div class="credit-get-item__decoration__item time-label">За 3-4 минуты</div>
                </div>
                <div class="credit-get-item__title">
                    Оформление заявки
                </div>
                <div class="credit-get-item__description">
                    Заполнение займет <br/>
                    не более 10 минут
                </div>
            </div>
            <div class="credit-get-item">
                <div class="credit-get-item__decoration">
                    <div class="credit-get-item__decoration__item">Шаг 2</div>
                    <div class="credit-get-item__decoration__item time-label">За 1 минуту</div>
                </div>
                <div class="credit-get-item__title">
                    Дождитесь ответа
                </div>
                <div class="credit-get-item__description">
                    Ответим на заявку <br/>
                    в течение 5 минут
                </div>
            </div>
            <div class="credit-get-item">
                <div class="credit-get-item__decoration">
                    <div class="credit-get-item__decoration__item">Шаг 3</div>
                    <div class="credit-get-item__decoration__item time-label">За 30 секунд</div>
                </div>
                <div class="credit-get-item__title">
                    Мгновенный перевод
                </div>
                <div class="credit-get-item__description">
                    На банковскую карту
                </div>
            </div>
            <div class="credit-get-item">
                <div class="credit-get-item__decoration">
                    <div class="credit-get-item__decoration__item">Шаг 4</div>
                    <div class="credit-get-item__decoration__item time-label">за 3 минуты</div>
                </div>
                <div class="credit-get-item__title">
                    Погасите заём
                </div>
                <div class="credit-get-item__description">
                    Любым удобным для <br/>
                    Вас способом
                </div>
            </div>
        </div>
    </div>
    <div class="information-container__title deverie">
        <p>Нам доверяют более 1 000 000 клиентов</p>
    </div>
    <div class="credit-reasons">
        <div class="credit-reasons__item">
            <div class="credit-reasons__item__info">
                <div class="credit-reasons__item__info__title">
                    Первый заём — бесплатно*
                </div>
                <div class="credit-reasons__item__info__description">
                    Воспользуйтесь уникальным предложением и решите свои финансовые проблемы без дополнительных затрат
                </div>
            </div>
            <div class="credit-reasons__item__image">
                <img src="design/boostra_mini_norm/img/landing/credit-reasons/card.png">
            </div>
        </div>
        <div class="credit-reasons__item">
            <div class="credit-reasons__item__info">
                <div class="credit-reasons__item__info__title">
                    Простое оформление
                </div>
                <div class="credit-reasons__item__info__description">
                    Чтобы взять займ на карту, достаточно заполнить форму
                </div>
            </div>
            <div class="credit-reasons__item__image">
                <img src="design/boostra_mini_norm/img/landing/credit-reasons/calc.png">
            </div>
        </div>
    </div>
    <div class="credit-reasons">
        <div class="credit-reasons__item">
            <div class="credit-reasons__item__info">
                <div class="credit-reasons__item__info__title">
                    Первый заём — бесплатно*
                </div>
                <div class="credit-reasons__item__info__description">
                    Воспользуйтесь уникальным предложением и решите свои финансовые проблемы без дополнительных затрат
                </div>
            </div>
            <div class="credit-reasons__item__image">
                <img src="design/boostra_mini_norm/img/landing/credit-reasons/wallet-case.png">
            </div>
        </div>
        <div class="credit-reasons__item">
            <div class="credit-reasons__item__info">
                <div class="credit-reasons__item__info__title">
                    Простое оформление
                </div>
                <div class="credit-reasons__item__info__description">
                    Чтобы взять займ на карту, достаточно заполнить форму
                </div>
            </div>
            <div class="credit-reasons__item__image">
                <img src="design/boostra_mini_norm/img/landing/credit-reasons/cubes.png">
            </div>
        </div>
    </div>
    <div class="credit-additional-ad">
        <div class="description">
            <div class="main-label-item">
                Займы под 0% без отказа
            </div>
            <div class="description__title">
                Деньги у Вас через 5 минут
            </div>
            <div class="description__body">
                Быстрое рассмотрение заявки!
            </div>
            <a class="get-credit-button" href="#calculator">
                Получить деньги
            </a>
        </div>
        <div class="image">
            <img src="design/boostra_mini_norm/img/landing/additional-credit-add.png">
        </div>
    </div>
    <div class="information-container__title trebonija">
        <p>Требования к заёмщику</p>
    </div>
    <div class="customer-requirements">
        <div class="customer-requirements__item">
            <div class="customer-requirements__item__image">
                <img src="design/boostra_mini_norm/img/landing/customer_requirements/Age.png">
            </div>
            <div class="customer-requirements__item__description">
                Возраст заёмщика
                <br/>
                от 18 лет
            </div>
        </div>
        <div class="customer-requirements__item">
            <div class="customer-requirements__item__image">
                <img src="design/boostra_mini_norm/img/landing/customer_requirements/Active-Phone.png">
            </div>
            <div class="customer-requirements__item__description">
                Активный номер
                <br/>
                телефона
            </div>
        </div>
        <div class="customer-requirements__item">
            <div class="customer-requirements__item__image">
                <img src="design/boostra_mini_norm/img/landing/customer_requirements/Bank-Card.png">
            </div>
            <div class="customer-requirements__item__description">
                Именная банковская
                <br>
                карта
            </div>
        </div>
        <div class="customer-requirements__item">
            <div class="customer-requirements__item__image">
                <img src="design/boostra_mini_norm/img/landing/customer_requirements/Passport.png">
            </div>
            <div class="customer-requirements__item__description">
                Паспорт гражданина
                <br>
                РФ
            </div>
        </div>
    </div>

    <div class="information-container__title otzivy">
        <p>Отзывы наших клиентов</p>
    </div>

    <div class="customer-ratings">
        <div class="customer-ratings__item">
            <div class="customer-ratings__item__head">
                <div class="user">
                    <img src="design/boostra_mini_norm/img/landing/rating/avatar-1.png" class="avatar">
                    <div class="user-info">
                        <div class="name">Анна А.</div>
                        <div class="city">Волгоград</div>
                    </div>
                </div>
                <div class="stars">
                    <img src="design/boostra_mini_norm/img/landing/rating/star.png" class="star">
                    <img src="design/boostra_mini_norm/img/landing/rating/star.png" class="star">
                    <img src="design/boostra_mini_norm/img/landing/rating/star.png" class="star">
                    <img src="design/boostra_mini_norm/img/landing/rating/star.png" class="star">
                    <img src="design/boostra_mini_norm/img/landing/rating/star.png" class="star">
                </div>
            </div>
            <div class="customer-ratings__item__body">
                <div class="title">
                    Очень хорошо
                </div>
                <div class="description">
                    Сервис действительно удобный. В настоящее время существует множество
                    МФО, и на первый взгляд может показаться, что получить деньги — не
                    проблема. Но на деле многие компании не могут предоставить качественное обслуживание.
                </div>
            </div>
        </div>
        <div class="customer-ratings__item">
            <div class="customer-ratings__item__head">
                <div class="user">
                    <img src="design/boostra_mini_norm/img/landing/rating/avatar-2.png" class="avatar">
                    <div class="user-info">
                        <div class="name">Вероника В.</div>
                        <div class="city">Санкт-Петербург</div>
                    </div>
                </div>
                <div class="stars">
                    <img src="design/boostra_mini_norm/img/landing/rating/star.png" class="star">
                    <img src="design/boostra_mini_norm/img/landing/rating/star.png" class="star">
                    <img src="design/boostra_mini_norm/img/landing/rating/star.png" class="star">
                    <img src="design/boostra_mini_norm/img/landing/rating/star.png" class="star">
                    <img src="design/boostra_mini_norm/img/landing/rating/star.png" class="star">
                </div>
            </div>
            <div class="customer-ratings__item__body">
                <div class="title">
                    Лучшая компания
                </div>
                <div class="description">
                    Этот сервис предоставляет возможность получить займ даже с плохой кредитной историей, при этом всё
                    происходит быстро и без скрытых комиссий. Я очень благодарна вашей компании. Для меня важно, что
                    есть шанс
                    исправить свою финансовую ситуацию.
                </div>
            </div>
        </div>
    </div>

    <div class="conditions-additional-info">
        <p class="content">
            <span class="decoration">Условия получения займа на карту: </span>
            вы можете оформить заём от 3 000 до 100 000 рублей на срок до 24 недель. Процентная ставка составляет от 0%
            до
            0,8% в день, а полная стоимость кредита (ПСК) варьируется от 289,6% до 292%. Вся процедура проходит онлайн,
            и
            после одобрения заявки деньги мгновенно зачисляются на вашу банковскую карту. Займы предоставляют
            микрофинансовые организации (МФО), зарегистрированные в Центральном банке, их деятельность регулируется
            законом
            «О микрофинансовой деятельности».
        </p>
        <div class="read-btn">Читать</div>
    </div>

    <div class="information-container__title faq">
        <p>Частые вопросы</p>
    </div>

    <div class="faq-sector">
        <div class="faq-sector__question-item">
            <div class="question-group">
                <div class="wrapper">
                    <img src="design/boostra_mini_norm/img/landing/faq/open.png">
                </div>
                <div class="title">
                    Кто может взять микрозайм?
                </div>
            </div>
        </div>
        <div class="faq-sector__question-item">
            <div class="question-group">
                <div class="wrapper">
                    <img src="design/boostra_mini_norm/img/landing/faq/open.png">
                </div>
                <div class="title">
                    Как повысить одобрение?
                </div>
            </div>
        </div>
        <div class="faq-sector__question-item">
            <div class="question-group">
                <div class="wrapper">
                    <img src="design/boostra_mini_norm/img/landing/faq/open.png">
                </div>
                <div class="title">
                    Как избежать просрочки?
                </div>
            </div>
        </div>
    </div>

    <div class="mobile-app">
        <div class="title">Займы в вашем кармане!</div>
        <p class="description">Установите наше приложение и получите деньги за пару кликов</p>
        <div class="mobile-app__icons">
            <a target="_blank" class="item" href="">
                <img src="design/boostra_mini_norm/img/landing/mobile-app/ru-store.png">
            </a>
            <a target="_blank" class="item" href="">
                <img src="design/boostra_mini_norm/img/landing/mobile-app/nash-store.png">
            </a>
            <a target="_blank" class="item" href="">
                <img src="design/boostra_mini_norm/img/landing/mobile-app/local.png">
            </a>
        </div>
    </div>

    <div class="section-footer">
        <div class="social-block">
            <div class="logo">
                <img src="design/boostra_mini_norm/img/logo.png">
            </div>
            <div class="social-networks">
{*                <div class="item">*}
{*                    <img src="design/boostra_mini_norm/img/landing/social-networks/dzen.png">*}
{*                </div>*}
                <div class="item">
                    <a href="https://watbot.ru/w/mjj">
                        <img src="design/boostra_mini_norm/img/landing/social-networks/whatsapp.png">
                    </a>
                </div>
                <div class="item">
                    <a href=""></a>
                    <img src="design/boostra_mini_norm/img/landing/social-networks/vk.png">
                </div>
{*                <div class="item">*}
{*                    <img src="design/boostra_mini_norm/img/landing/social-networks/ok.png">*}
{*                </div>*}
{*                <div class="item">*}
{*                    <img src="design/boostra_mini_norm/img/landing/social-networks/yt.png">*}
{*                </div>*}
            </div>
        </div>
        <div class="submenu-block">
            <div class="submenu-block__item">Партнеры</div>
            <div class="submenu-block__item">Условия</div>
            <div class="submenu-block__item">Связаться с нами</div>
            <div class="submenu-block__item">Вопросы и ответы</div>
        </div>
        <hr/>
        <div class="callback-section">
            <div class="item">
                <div class="item__label">По всем вопросам</div>
                <div class="item__value">info@boostra.ru</div>
            </div>
            <div class="item">
                <div class="item__label">Телефон</div>
                <div class="item__value">8 800 333 30 73</div>
            </div>
        </div>
        <div class="partners-section">
            <p>Услуги по подбору микрозаймов оказывает ООО «Финтех-Маркет», <br>ИНН: 6317164496, ОГРН: 1236300023849.</p>

            <p>ООО «Финтех-Маркет» использует товарный знак (знак обслуживания) «boostra» на основании договора об отчуждении исключительного права на товарный знак <…> № РД0478972 от 17.09.2024,товарный знак «boostra» зарегистрирован  Федеральной службой по интеллектуальной собственности за № 575896 от 24.05.2016</p>

            <p>Юридический адрес: 443001, Самарская область, г.о. Самара, вн.р-н Ленинский, г Самара, ул Ярмарочная, д. 3, кв. 62</p>

            <p>ООО «Финтех-Маркет» не является Кредитором и не предоставляет финансовые услуги – финансовые услуги оказываются непосредственно микрофинансовыми организациями-партнерами ООО «Финтех-Маркет».</p>

            <p>Все партнеры Сервиса включены в реестр микрофинансовых организаций Банка России.</p>

            <p>© 2025, ООО «ФИНТЕХ-МАРКЕТ»</p>

            <p>При использовании материалов гиперссылка на boostra.ru обязательна.

            <p>Официальный сайт Банка России: <a href="https://cbr.ru/" class="cbr_link" target="_blank">https://cbr.ru/</a></p>

            <p>Интернет-приемная Банка России: <a href="https://cbr.ru/Reception" class="cbr_link" target="_blank">https://cbr.ru/Reception</a></p>

            <p>Реестр МФО Банка России: <a href="https://cbr.ru/microfinance/registry/" class="cbr_link" target="_blank">https://cbr.ru/microfinance/registry/</a></p>

            <p>ООО «ФИНТЕХ-МАРКЕТ» осуществляет деятельность в сфере IT</p>

            {*<p><a href="/info_partners" target="_blank">ПАРТНЕРЫ ООО «ФИНТЕХ-МАРКЕТ»</a></p>*}

            <p><a href="/files/docs/BEST2PAY_Offer.pdf" target="_blank">ОФЕРТА ОБ ИСПОЛЬЗОВАНИИ ПРОЦЕССИНГОВОГО ЦЕНТРА BEST2PAY</a></p>

            <p><a href="/files/docs/BEST2PAY_Security_Policy.pdf" target="_blank">ПОЛИТИКА БЕЗОПАСНОСТИ ПЛАТЕЖЕЙ BEST2PAY</a></p>
        </div>
    </div>
</div>
<script async src="https://lib.usedesk.ru/secure.usedesk.ru/widget_161404_53920.js"></script>
<script lang="js">
  let BASE_PERCENTS = {$base_percents};

  const updateCreditTime = () => {
    const dateNow = new Date();
    const newTime = dateNow.getTime() + 1000 * 5 * 60;
    const newDate = new Date(newTime);
    $('#credit-time').text('Деньги у вас в ' + newDate.getHours() + ':' + (newDate.getMinutes() < 10 ? ('0' + newDate.getMinutes()) : newDate.getMinutes()))
  }
  function clickHunter() {
      {if $settings->click_hunter && $settings->click_hunter['status']}
    setTimeout(() => {
      window.location.href = "{$settings->click_hunter['url']}";
    }, 1000)
      {/if}
  }

  function submitOrder() {
    let amount = parseInt($('#calc-sum').val()), period = parseInt($('#calc-period').val());
    window.open('/init_user?amount='+amount+'&period='+period, '_blank');
    ym(45594498,'reachGoal','main_page_get_zaim_new_design2')
    clickHunter();
  }

  function calculate() {
    let amount = parseInt($('#calc-sum').val()),
      period = parseInt($('#calc-period').val()),
      percent = parseFloat(BASE_PERCENTS) / 100,
      discount_percent = parseFloat($("#percent").val()),
      discount_period = parseInt($("#max_period").val());

    let percent_calculate = period > discount_period ? percent : discount_percent,
      total = Math.round(amount * period * percent_calculate + amount);

    if (period > discount_period) {
      $(".submit-credit").text('Получить деньги');
      $("#percent-value").text(BASE_PERCENTS + ' %');
    } else {
      $(".submit-credit").text('Получить бесплатно');
      $("#percent-value").text('Без процентов*');
    }

    $('#calc-sum-label').text(Intl.NumberFormat('ru-RU').format(+amount) + ' ₽');
    $('#calc-period-label').text(period+' дней');

    $("#return-sum").text(Intl.NumberFormat('ru-RU').format(total) + ' ₽')
  }

  $(document).ready(() => {
    setInterval(updateCreditTime, 1000);

    $('.burger').on('click', () => {
      $('.site-menu__mobile').toggleClass('open')
    });

    $(".ion_slider_wrapper").ionRangeSlider({
      skin: "round",
      type: "single",
      onStart: function (data) {
        calculate(data);
      },
      onChange: function (data) {
        calculate(data);
      }
    });

    $('#hero-range').on('change', function () {
      let v = $(this).val();
      $(".js-total-output").val(v);
    });
  });

  $('.cbr_link').click(function() {
      event.preventDefault();

      const linkUrl = $(this).attr('href');

      $.ajax({
          url: 'ajax/client_action_handler.php?action=clickCbrLink',
          method: 'GET',
          success: function(response) {
              window.open(linkUrl, '_blank');
          },
          error: function(xhr, status, error) {
              window.open(linkUrl, '_blank');
          }
      });
  });
</script>
</body>