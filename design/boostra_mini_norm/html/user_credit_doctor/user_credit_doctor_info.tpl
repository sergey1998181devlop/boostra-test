{* Шаблон страницы Credit Doctor с опросом*}

{$meta_title = "Услуга Кредитный доктор, предоставляемая ООО Алфавит, позволяет избавится от долгов в сжатые сроки | Boostra" scope=parent}
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
    #user_credit_doctor_info h2,  #user_credit_doctor_info h5 {
        margin: 2rem 0;
        text-align: center;
    }

    #prices-wrapper {
        min-height: auto !important;
    }
</style>

<div class="panel" id="user_credit_doctor_info">
    <div id="docs" >
        <h2>Услуга Кредитный доктор, предоставляемая ООО Алфавит, позволяет избавится от долгов в сжатые сроки</h2>
        <h5 style="text-align: center;">Документы</h5>
        <ul>
            <li><a href="/user/credit_doctor_info/dogovor_okazaniya_uslug_lidogeneracii_19_07_2022.docx">ДОГОВОР ОКАЗАНИЯ УСЛУГ ПО ЛИДОГЕНЕРАЦИИ</a></li>
            <li><a href="/user/credit_doctor_info/zayavlenie_na_otkaz_ot_uslugi_kredinyj_doktor_alfavit_19_07_20221.docx">Заявление об отказе от услуги «Кредитный доктор»</a></li>
            <li><a href="/user/credit_doctor_info/oferta_ooo_alfavit_20_07_2022.doc">Публичная оферта на оказание платных услуг сервиса</a></li>
            <li><a href="/user/credit_doctor_info/soglashenie_ob_ispol_zovanii_asp_11_07_2022.docx">СОГЛАШЕНИЕ ОБ ИСПОЛЬЗОВАНИИ АНАЛОГА СОБСТВЕННОРУЧНОЙ ПОДПИСИ ООО «АЛФАВИТ»</a></li>
        </ul>

        <div id="modal_survey" class="white-popup">
            <div class="modal-survey-content">
                <div data-modal_slide="0" class="active">
                    <div id="prices-wrapper">
                        <p>Цены тарифов обучения:</p>
                        <div class="select-order-wrapper">
                            {foreach $order_items as $key => $order_item}
                                <label for="order_item_{$key}">
                                    <input disabled type="radio" id="order_item_{$key}" name="order_item" value="{$key}" />
                                    <div class="select-order-item">
                                        <small>{$order_item.description}</small>
                                        <div><hr /></div>
                                        <p class="price">{$order_item.price|number_format:0:" ":" "} ₽</p>
                                    </div>
                                </label>
                            {/foreach}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ol>
            <li>Первый урок. Ответы на самые важные вопросы о долгах и деньгах.
                <br/>Практическое задание: Заполнить анкету, в конце которой поставить галочку «Я беру на себя обязательство больше
                никогда не брать деньги в долг».</li>
            <li>Второй урок. Какие установки в своём подходе к деньгам необходимо изменить, прежде чем заняться работой с
                долгами. <br/>Практическое задание: Объективно оценить свое отношение к деньгам по трём критериям: отдаю ли я большую часть
                зарплаты на погашение долгов, оплачиваю ли я продления займов, выхожу ли я на день выплаты займа без нужной
                суммы на руках.</li>
            <li>Третий урок. Три главных принципа, которые нужно всегда соблюдать, чтобы закрыть все долги.
                <br/>Практическое задание: Указать свою точную зарплату и взять на себя обязательство жить только на неё.</li>
            <li>Четвёртый урок. Работа с бюджетом на месяц. Распределение финансов. Как снова начать жить на свои деньги.
                <br/>Практическое задание: Скачать таблицу учёта финансов и внести в неё начальные данные - свою зарплату и общую
                сумму к выплате по долгам.</li>
            <li>Пятый урок. Как вести учёт доходов и расходов. Работа с таблицами и приложениями для учёта своих денег.
                <br/>Практическое задание: Неделю вести детальный учёт всех своих расходов и доходов. По итогу отчитаться куратору.</li>
            <li>Шестой урок. Как договариваться с кредиторами об изменении условий платежа. Обучение необходимым приёмам
                и основам переговоров.
                <br/>Практическое задание: Написать всем своим кредиторам и попросить реструктуризацию займа.</li>
            <li>Седьмой урок. Анализ своей карьеры. Как получать больше денег за свою работу, если сейчас их не хватает на
                выплату долгов.
                <br/>Практическое задание: Отправить свое резюме куратору. Затем поработать над ошибками после получения обратной
                связи от куратора.</li>
        </ol>
    </div>
</div>

<script src="design/{$settings->theme}/js/jquery.inputmask.min.js" type="text/javascript"></script>
