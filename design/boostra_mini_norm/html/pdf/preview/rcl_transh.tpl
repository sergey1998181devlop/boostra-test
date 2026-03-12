<table width="530" style="font-size:8px;">
    <tr>
        <td>
            <h1 align="center" style="font-size:14px; margin:5px 0;"><strong>ЗАЯВЛЕНИЕ<br>на получение транша по договору потребительского займа<br>с лимитом кредитования</strong></h1>

            <p style="margin:10px 0;">
                Я, {$lastname|escape} {$firstname|escape} {$patronymic|escape}, (далее - Заемщик), {$birth|escape} г.р.,
                паспорт: серия {$passport_serial|escape} № {$passport_number|escape} выдан {$passport_date|date} г. {$passport_issued|escape},
                зарегистрированный(ая) по адресу: {$regaddress_full|escape}, понимая значение своих действий и руководя ими,
                прошу {$organization->name} (ИНН {$organization->inn}, ОГРН {$organization->ogrn}, адрес: {$organization->address},
                {$organization->site}, телефон {$organization->phone}, зарегистрированное в государственном реестре микрофинансовых организаций
                за номером {$organization->registry_number} от {$organization->registry_date|date_format:'%d.%m.%Y'}) (далее - Компания)
            </p>

            <p style="margin:10px 0;">
                Предоставить мне транш в рамках договора потребительского займа с лимитом кредитования {$loan_number} от {$loan_date|date}
                в размере {$amount} ({$amount_string}) рублей. Срок возврата транша: {$period} календарных дней.
            </p>

            <p style="margin:10px 0;">
                Настоящим Заявлением подтверждаю, что:<br>
                - мне известен размер доступного расходного лимита;<br>
                - запрашиваемая сумма транша не превышает доступный расходный лимит.
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
