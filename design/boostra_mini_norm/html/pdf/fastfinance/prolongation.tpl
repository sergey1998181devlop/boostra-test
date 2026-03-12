<style>
    body {
        font-family: Times New Roman, serif;
        font-size: 12pt;
        line-height: 1.3;
    }
    .header-right {
        text-align: right;
        font-weight: bold;
    }
    .header-client {
        text-align: right;
        font-weight: bold;
    }
    .title {
        text-align: center;
        font-weight: bold;
    }
    .subtitle {
        text-align: center;
        font-weight: bold;
    }
    .content {
        font-style: italic;
    }
    .section-title {
        font-weight: bold;
    }
    .list-item {
        text-align: justify;
    }
    .info-label {
        font-weight: bold;
    }
    .signature-line {
        text-align: left;
    }
</style>

<div class="header-right">
    Генеральному директору ООО МКК «Фаст Финанс»<br>
    Насибуллину Р.Р.
</div>

<div class="header-client">
    {$fullname}, {$birthday_date}
</div>

<div class="title">
    ЗАЯВЛЕНИЕ<br>
    о пролонгации (продлении) договора займа
</div>

<div class="content">
    Я, {$fullname}, {$birthday_date}, паспорт {$passport_serries} № {$passport_number}, выдан {$passport_issued_by} от {$passport_issued_date}, являясь заемщикоми по договору займа {$loan_number} от {$date_of_issue}, заключенному в электронной форме, прошу рассмотреть возможность пролонгации (продления) договора займа
</div>

<div class="section-title">
    ЗАЯВЛЕНИЕ И ПОДТВЕРЖДЕНИЯ<br>
    Настоящим подтверждаю:
</div>
<ul>
<li>Ознакомлен(а) с порядком пролонгации (продления) договора займа, согласно Индивидуальным условиям договора займа, Общими условиями договора займа и Правилами предоставления займа ООО МКК «Фаст Финанс», а также ст. 810 ГК РФ;</li>
<li>Настоящим подтверждаю, что до изменения условий Договора займа я ознакомлен(а) с новым размером полной стоимости займа, рассчитанным в соответствии с Федеральным законом от 21.12.2013 № 353-ФЗ «О потребительском кредите (займе)».</li>
</ul>
<div class="signature-line">
    Дата подписания: {$issued_date}
</div>

<div class="info-label">
    Заемщик:
</div>

<div class="signature-line">
    Ф.И.О.: {$fullname},
</div>

<div class="signature-line">
    Дата рождения: {$birthday_date},
</div>

<div class="signature-line">
    паспорт серия {$passport_serries} № {$passport_number}
</div>

<div class="signature-line">
    Выдан {$passport_issued_by} от {$passport_issued_date},
</div>

<div class="signature-line">
    адрес регистрации: {$registration_adress}
</div>

<div class="signature-line">
    Подпись Заявителя (АСП Заявителя: {$asp_code})
</div>
