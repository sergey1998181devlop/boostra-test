<style>
    body {
        font-family: Times New Roman, serif;
        font-size: 12pt;
        line-height: 1.3;
    }
    .header {
        text-align: center;
        font-weight: bold;
    }
    .content {
        text-align: justify;
        text-indent: 1.5em;
    }
    .client-info {
        text-align: left;
        text-indent: 0.5em;
    }
</style>

<div class="header">
    ЛИСТ ПРИСОЕДИНЕНИЯ<br>
    к СОГЛАШЕНИЮ ОБ ИСПОЛЬЗОВАНИИ АНАЛОГА СОБСТВЕННОРУЧНОЙ ПОДПИСИ ПРИ ДИСТАНЦИОННОМ ВЗАИМОДЕЙСТВИИ ООО МКК «ФАСТ ФИНАНС» №11/02/26 от11.02.2026 г.
</div>

<div class="content">
    Настоящим, я {$fullname}, {$birth_date} г.р. паспорт серия {$passport_serial} № {$passportNumber}, выдан {$passport_date} от {$passport_issued}, зарегистрированый(ая) по адресу: {$addressReg} именуемый(-ая) в дальнейшем «Клиент», подтверждаю свое присоединение к Соглашению об использовании аналога собственноручной подписи, утвержденному 23.01.2026 г. в ООО МКК «ФАСТ ФИНАНС» (ОГРН 1257700315619, ИНН 9722101935, адрес: 121615, г. Москва, вн.тер.г. муниципальный округ Кунцево, ш. Рублёвское, д. 16, к. 1, помещ. 200, https://mkkfastfinance.ru/, телефон: 8 800 333 30 73, зарегистрированное Центральным Банком Российской Федерации в государственном реестре микрофинансовых организаций за номером 2503045010184 от 16.09.2025) (далее – Соглашение), и полностью соглашаюсь с условиями, изложенными в Соглашении относительно использования аналога собственноручной подписи при подписании электронных документов.
</div>

<div class="client-info">
    клиент (ФИО): {$fullname}, дата рождения: {$birth_date} г.
</div>

<div class="client-info">
    паспорт серия {$passport_serial} № {$passportNumber}
</div>

<div class="client-info">
    выдан {$passport_date} г. {$passport_issued}
</div>

<div class="client-info">
    адрес регистрации: {$registration_adress}
</div>

<div class="client-info">
    {if !empty($accept_sms)}АСП Клиента: {$accept_sms}{/if}
</div>

<div class="client-info">
    {if !empty($sign_date)}Дата подписания: {$sign_date|date_format:"%d.%m.%Y"}<br>{/if}
</div>
