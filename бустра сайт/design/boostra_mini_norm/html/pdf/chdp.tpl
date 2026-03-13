<div style="text-align: center">
    <h3 style="">ЗАЯВЛЕНИЕ<br>
            о частичном досрочном погашении займа
    </h3>
</div>
<h1>&nbsp;</h1>
<p style="text-align: left;">
Я, {$lastname|escape} {$firstname|escape} {$patronymic|escape}, (далее - Заявитель), 
{$birth|escape} г.р., паспорт: серия {$passport_serial|escape} номер {$passport_number|escape}, выдан {$passport_date|escape}
{$passport_issued|escape}, зарегистрирован(а) по адресу: {$registration_address|escape}, 
фактически проживаю по адресу: {$fakt_address|escape}, {$phone_mobile|escape},
прошу {$organization->name|escape} (ОГРН {$organization->ogrn|escape}, ИНН {$organization->inn|escape}, адрес:
{$organization->address|escape}, {$organization->site|escape}, зарегистрировано Центральным Банком Российской Федерации в
государственном реестре микрофинансовых организаций за номером {$organization->mfo_number|escape} (далее - Компания)
разрешить мне частичное досрочное погашение задолженности по Договору займа No
{$contract_number} от {$contract_date|date} года:
- сумма погашения: {$payment_amount} рублей (сумма включает в себя сумму платежа по графику);
</p>
<p>Я подтверждаю, что осведомлен о том, что частичное досрочное погашение может быть осуществлено
только в дату очередного платежа по графику платежей.</p>

<h1>&nbsp;</h1>
<h1>&nbsp;</h1>

<table style="border: 1px solid black;">
    <tr><td>Клиент: Ф.И.О.: {$lastname|escape} {$firstname|escape} {$patronymic|escape}</td></tr>
    <tr><td>Дата рождения: {$birth|escape}</td></tr>
    <tr><td>Паспорт: серия {$passport_serial|escape} № {$passport_number|escape}</td></tr>
    <tr><td>Выдан: {$passport_issued|escape} {$passport_date|escape} г.</td></tr>
    <tr><td>Адрес регистрации: {$registration_address|escape}</td></tr>
    <tr><td>АСП Клиента: {$asp|escape}</td></tr>
    <tr><td>Дата получения: {$asp_date|escape}</td></tr>
</table>