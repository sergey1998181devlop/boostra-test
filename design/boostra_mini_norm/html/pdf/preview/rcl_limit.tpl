<table width="530" style="font-size:8px;">
    <tr>
        <td>
            <h1 align="center" style="font-size:14px; margin:5px 0;"><strong>ЗАЯВЛЕНИЕ<br>на установку расходного кредитного лимита</strong></h1>

            <p style="margin:10px 0;">
                Я, {$lastname|escape} {$firstname|escape} {$patronymic|escape}, (далее - Заемщик), {$birth|escape} г.р.,
                паспорт: серия {$passport_serial|escape} № {$passport_number|escape} выдан {$passport_date|date} г. {$passport_issued|escape},
                зарегистрированный(ая) по адресу: {$regaddress_full|escape}, понимая значение своих действий и руководя ими,
                прошу {$organization->name} (ОГРН {$organization->ogrn}, ИНН {$organization->inn}, адрес: {$organization->address},
                {$organization->site}, телефон: {$organization->phone}, зарегистрированное Центральным Банком Российской Федерации
                в государственном реестре микрофинансовых организаций за номером {$organization->registry_number} от {$organization->registry_date|date_format:'%d.%m.%Y'})
                (далее - Компания) установить расходный лимит в размере {$amount} ({$amount_string}) рублей 00 копеек.
            </p>
        </td>
    </tr>
</table>
<br /><br />
<table border="1" width="530" cellspacing="0" cellpadding="5" style="font-size:8px;">
    <tr>
        <td>
            клиент (ФИО): {$lastname|escape} {$firstname|escape} {$patronymic|escape}, дата рождения: {$birth|escape} г.<br>
            паспорт серия {$passport_serial|escape} № {$passport_number|escape}<br>
            выдан {$passport_date|date} г. {$passport_issued|escape}<br>
            адрес регистрации: {$regaddress_full|escape}<br>
            {if $asp}АСП Клиента: {$asp}<br>{/if}
            дата подписания: {$created|date}
        </td>
    </tr>
</table>
