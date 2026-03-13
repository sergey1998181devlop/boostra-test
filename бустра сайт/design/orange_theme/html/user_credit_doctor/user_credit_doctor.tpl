{* Шаблон страницы Credit Doctor с опросом*}
<link rel="stylesheet" href="design/{$settings->theme}/css/user_credit_doctor.css?v=1.004" />

<style>
    .header_get_a_loan {
        display: none !important;
    }
    header > nav > ul > li:nth-child(2) > ul > li:nth-child(4) {
        display: none !important;
    }
    header > nav > ul > li:nth-child(2) > ul > li:nth-child(5) {
        display: none !important;
    }
</style>

<div class="panel" id="user_credit_doctor">
    <div id="survey-wrapper">
        <div id="survey-slider" class="loading">
            <div>
                <h1>Заполните простую анкету и узнайте как “Кредитный доктор” может вам помочь</h1>
            </div>
            <div class="survey-navigate">
                <ul>
                    <li data-slide="0" class="active"><a href="javascript:void(0)">1</a></li>
                    <li data-slide="1"><a href="javascript:void(0)">2</a></li>
                    <li data-slide="2"><a href="javascript:void(0)">3</a></li>
                    <li data-slide="3"><a href="javascript:void(0)">4</a></li>
                </ul>
            </div>
            <div data-slide_content="0" class="active">
                <h3>Какая общая сумма долга?</h3>
                <div class="survey-ui slider-content">
                    <div>
                        <input type="text" name="survey_amount" data-from="" value="10000" />
                    </div>
                </div>
            </div>
            <div data-slide_content="1">
                <h3>Есть ли у вас просроченные платежи по долгам?</h3>
                <div class="slider-content">
                    <div>
                        <ul>
                            <li>
                                <label>
                                    <div class="radio">
                                        <input type="radio" name="has_credit" value="1" />
                                        <span></span>
                                    </div>
                                    Да
                                </label>
                            </li>
                            <li>
                                <label>
                                    <div class="radio">
                                        <input type="radio" name="has_credit" value="0" />
                                        <span></span>
                                    </div>
                                    Нет
                                </label>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div data-slide_content="2">
                <h3>Сколько звонков от служб взыскания вы получили за прошлую неделю?</h3>
                <div class="slider-content">
                    <div>
                        <ul>
                            <li>
                                <label>
                                    <div class="radio">
                                        <input type="radio" name="count_calls" value="5"  />
                                        <span></span>
                                    </div>
                                    5 звонков
                                </label>
                            </li>
                            <li>
                                <label>
                                    <div class="radio">
                                        <input type="radio" name="count_calls" value="10" />
                                        <span></span>
                                    </div>
                                    10 звонков
                                </label>
                            </li>
                            <li>
                                <label>
                                    <div class="radio">
                                        <input type="radio" name="count_calls" value="999" />
                                        <span></span>
                                    </div>
                                    Более 10 звонков
                                </label>
                            </li>
                            <li>
                                <label>
                                    <div class="radio">
                                        <input type="radio" name="count_calls" value="0" />
                                        <span></span>
                                    </div>
                                    Ни одного
                                </label>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div data-slide_content="3">
                <h3>Сколько раз вы обычно занимаете деньги до 15 числа?</h3>
                <div class="slider-content">
                    <div>
                        <ul>
                            <li>
                                <label>
                                    <div class="radio">
                                        <input type="radio" name="count_take_money" value="1"  />
                                        <span></span>
                                    </div>
                                    1
                                </label>
                            </li>
                            <li>
                                <label>
                                    <div class="radio">
                                        <input type="radio" name="count_take_money" value="3" />
                                        <span></span>
                                    </div>
                                    3
                                </label>
                            </li>
                            <li>
                                <label>
                                    <div class="radio">
                                        <input type="radio" name="count_take_money" value="999" />
                                        <span></span>
                                    </div>
                                    Более 3
                                </label>
                            </li>
                            <li>
                                <label>
                                    <div class="radio">
                                        <input type="radio" name="count_take_money" value="0" />
                                        <span></span>
                                    </div>
                                    Нисколько
                                </label>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div id="survey-footer">
            <button disabled>Назад</button>
            <button>Далее</button>
        </div>
    </div>
</div>

<div id="modal_survey" class="mfp-hide white-popup">
    {*<div class="modal-survey-prev-btn"><img alt="Назад" src="/design/{$settings->theme}/img/user_credit_doctor/left_arrow_black.png" /> Назад</div>*}
    <div class="modal-survey-close-btn">Закрыть <img alt="Закрыть" src="/design/{$settings->theme}/img/user_credit_doctor/close.png" /></div>
    <div class="modal-survey-content">
        <div data-modal_slide="0" class="active">
            <div>
                <h2>Ваша долговая нагрузка находится на уровне <span id="quiz_level" class="text-orange">Х</span></h2>
                <p>В течение 6 месяцев, при сохранении текущих показателей, ваш долг увеличится на <span id="quiz_percent" class="text-orange">96%</span>, что составляет <span id="quiz_amount" class="text-orange">ХХХХ</span> рублей.
                    На таком же уровне долговой нагрузки находится <span class="text-orange">67%</span> наших участников.</p>
                <div class="select-order-wrapper">
                    {foreach $order_items as $key => $order_item}
                        <label for="order_item_{$key}">
                            <input type="radio" id="order_item_{$key}" name="order_item" value="{$key}" />
                            <div class="select-order-item">
                                <small>{$order_item.description}</small>
                                <div><hr /></div>
                                {* Для рекурентных платежей раскоментировать
                                <p class="price"><b><strike>{$order_item.price|number_format:0:" ":" "} ₽</strike></b></p>
                                <p class="discount-price">1 ₽<sup>*</sup></p>*}
                                <p class="discount-price"><b>{$order_item.price|number_format:0:" ":" "} ₽</b></p>
                            </div>
                        </label>
                    {/foreach}
                </div>
                <div class="footer-modal">
                    <p>Услуги оказывает ООО "Алфавит"</p>
                    <p class="small-text"><sup class="text-orange">*</sup> — Оставшаяся сумма будет автоматически списана после прохождения первого месяца курса, если Вы примите решение продолжить прохождения курса</p>
                </div>
            </div>
        </div>
        <div data-modal_slide="1">
            <div data-content="0" class="modal-tariff-item">
                <div>
                    <h2><b>Подписка на минимальный план</b></h2>
                </div>
                <p class="text-grey">Доступ к пошаговому плану по избавлению от долгов,
                    при точном соблюдении которого вы закроете 50% своих долгов
                    уже через полтора месяца и закрытому чату единомышленников, которые уже начали
                    избавляться от долгов и могут поделиться своим опытом,
                    рассказать полезные секреты и поделиться способами вернуть часть своих денег</p>
                <h2 class="text-orange"><b>{$order_items[1].price|number_format:0:" ":" "} ₽</b></h2>
                <div class="flex-center">
                    <ul>
                        <li>Бессрочный доступ к плану по эффективному избавлению от долгов</li>
                        <li>Удобные инструменты, которые помогут рассчитываться с долгами</li>
                        <li>Доступ к закрытому чату единомышленников на</li>
                        <li>Рекомендации по быстрому избавлению от долгов</li>
                        <li>Секретные приёмы, как вернуть часть своих денег</li>
                        <li>Советы, как запретить коллекторам звонить вашим близким</li>
                    </ul>
                </div>
                <p class="modal-href-footer">
                    <a href="javascript:void(0);" class="btn-sm" onclick="userCreditDoctor.changeModalStep(1, 1);">Смотреть предыдущий</a>
                </p>
                <div>
                    <button class="orange-btn survey-get-pay">Выбрать тариф</button>
                </div>
                <p class="modal-href-footer">
                    <a href="javascript:void(0);" class="btn-sm" onclick="userCreditDoctor.changeModalStep(2, 2);">Не подходит</a>
                </p>
                <div class="footer-modal">
                    <p>Полные <a target="_blank" href="/user/credit_doctor_info/oferta_ooo_alfavit_20_07_2022.doc">условия</a> оказания услуг от ООО "Алфавит" </p>
                </div>
            </div>
            <div data-content="1" class="modal-tariff-item">
                <div>
                    <h2><b>Подписка на план с куратором</b></h2>
                </div>
                <p class="text-grey">Работа с персональным куратором, который поможет вам договориться с кредиторами о лучших условиях погашения займа,
                    поможет избавиться от звонков коллекторов и будет давать советы, как быстрее закрыть каждый займ</p>
                <h2 class="text-orange"><b>{$order_items[2].price|number_format:0:" ":" "} ₽</b></h2>
                <div class="flex-center">
                    <ul>
                        <li>Персональный куратор, который каждый день будет отвечать на ваши вопросы, давать рекомендации и помогать договариваться с кредиторами о лучших условиях закрытия займа</li>
                        <li>Поможет составить и согласовать весь перечень необходимых юридических документов</li>
                        <li>Вместе с вами согласует новый график платежей с вашими кредиторами</li>
                        <li>Доступ к 8 занятиям с экспертом по избавлению от долгов, с помощью которых уже через 2 месяца вы закроете до 80% ваших долгов</li>
                    </ul>
                </div>
                <p class="modal-href-footer">
                    <a href="javascript:void(0);" class="btn-sm" onclick="userCreditDoctor.changeModalStep(1, 2);">Смотреть предыдущий</a>
                </p>
                <div>
                    <button class="orange-btn survey-get-pay">Выбрать тариф</button>
                </div>
                <p class="modal-href-footer">
                    <a href="javascript:void(0);" class="btn-sm" onclick="userCreditDoctor.changeModalStep(1, 0); userCreditDoctor.showSurveyTopBtn();">Не подходит</a>
                </p>
                <div class="footer-modal">
                    <p>Полные <a target="_blank" href="/user/credit_doctor_info/oferta_ooo_alfavit_20_07_2022.doc">условия</a> оказания услуг от ООО "Алфавит" </p>
                </div>
            </div>
            <div data-content="2" class="modal-tariff-item">
                <div>
                    <h2><b>Подписка на план со 100% гарантией закрытия долгов + услуга "Антиколлектор"</b></h2>
                </div>
                <p class="text-grey">Весь путь от текущей задолженности до полного закрытия всех долгов вы пройдёте с помощью личного ассистента,
                    который за вас составит все заявления, согласует юридические вопросы,
                    подготовит нужные документы и полностью возьмёт на себя всё взаимодействие с вашими кредиторами</p>
                <h2 class="text-orange"><b>{$order_items[3].price|number_format:0:" ":" "} ₽</b></h2>
                <div class="flex-center">
                    <ul>
                        <li>Коллекторы больше не будут звонить вам и вашим близким</li>
                        <li>Личный ассистент подготовит все необходимые документы за вас</li>
                        <li>Новый удобный график платежей</li>
                        <li>100% гарантия закрытия всех долгов</li>
                    </ul>
                </div>
                <div>
                    <button class="orange-btn survey-get-pay">Выбрать тариф</button>
                </div>
                <p class="modal-href-footer">
                    <a href="javascript:void(0);" class="btn-sm" onclick="userCreditDoctor.changeModalStep(1, 1);">Не подходит</a>
                </p>
                <div class="footer-modal">
                    <p>Полные <a target="_blank" href="/user/credit_doctor_info/oferta_ooo_alfavit_20_07_2022.doc">условия</a> оказания услуг от ООО "Алфавит" </p>
                </div>
            </div>
        </div>
        <div data-modal_slide="2">
            <div data-content="0">
                <div class="repeat_order">
                    <div>
                        <h3><b><span class="text-orange">Получи первый урок</span> по избавлению<br/>
                                от долгов <span class="text-orange">бесплатно!</span></b></h3>
                    </div>
                    <div>
                        <button class="orange-btn">Получить <img alt="Назад" src="/design/{$settings->theme}/img/user_credit_doctor/right_arrow.png" /></button>
                    </div>
                </div>
            </div>
            <div data-content="1">
                <div>
                    <h3><b>Укажите свой почтовый адрес</b><br/> и мы отправим вам обучающий материал бесплатно</h3>
                </div>
                <div class="form-control">
                    <input type="email" value="" name="survey_email" placeholder="Введите ваш e-mail" />
                </div>
                <div>
                    <button class="orange-btn" id="survey-get-free-lesson">Получить урок</button>
                </div>
            </div>
            <div data-content="2">
                <div>
                    <h3></h3>
                </div>
                <div>
                    <iframe
                            class="youtube"
                            width="100%"
                            src="https://www.youtube.com/embed/gqByJY6N0XM"
                            title="Вводный урок"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
                    </iframe>
                </div>
                <div>
                    <br/>
                    <a class="orange-btn btn xs-font-size" href="https://t.me/CreditDoctor_bot">Наша группа телеграм для единомышленников</a>
                </div>
            </div>
        </div>
        <div data-modal_slide="3">
            <div data-content="0" class="survey-card-list">
                {*<div id="cards_list"></div>
                <div>
                    <hr/>
                    <div id="sms_block"></div>
                    <button class="orange-btn survey-go-pay">Оплатить услугу</button>
                </div>*}
                <div id="sms_block"></div>
            </div>
        </div>
        <div data-modal_slide="4" class="survey-finish-wrapper">
            <div data-content="0">
                <div id="success-container"><span class="loading-pay-text">Происходит процесс оплаты, пожалуйста подождите.</span></div>
            </div>
        </div>
    </div>
</div>

<script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
<script src="design/{$settings->theme}/js/user_credit_doctor.js?v=1.010" type="text/javascript"></script>
