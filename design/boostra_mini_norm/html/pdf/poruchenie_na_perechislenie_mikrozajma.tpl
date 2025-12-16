{literal}
    <style>
        h1 {
            text-align: center;
            font-weight: normal;
        }
    </style>
{/literal}
{function name=getFio}
    {$lastname} {$firstname} {$patronymic}
{/function}
<h1>Поручение на перечисление микрозайма</h1>
<p>
    Я, {getFio}, ОГРНИП {$ogrnip}, (далее - Заявитель), {$birth} г.р., паспорт: серия {$passport_serial} № {$passport_number} выдан {$passport_issued} от {$passport_date}г.
    зарегистрирован(а) по адресу: {$reg_address}, тел. {$phone_mobile}.
</p>
<p>
    В случае одобрения со стороныООО МКК «Аквариус» (ОГРН 1237700365506, ИНН 9714011290) заявления на получение займа в прошу перечислить денежные средства на следующий расчетный счет:
</p>

<table cellspacing="1" cellpadding="2" width="100%" style="border: 1px solid black;">
    <tbody>
        <tr>
            <td style="font-size: 10pt; font-weight: bold; border-bottom: 1px solid black; border-right: 1px solid black;" align="left" valign="middle">Индивидуальный предприниматель</td>
            <td style="border-bottom: 1px solid black;" align="right" valign="middle">{getFio}</td>
        </tr>
        <tr>
            <td style="font-size: 10pt; font-weight: bold; border-bottom: 1px solid black; border-right: 1px solid black;" align="left" valign="middle">ИНН</td>
            <td style="border-bottom: 1px solid black;" align="right" valign="middle">{$inn}</td>
        </tr>
        <tr>
            <td style="font-size: 10pt; font-weight: bold; border-bottom: 1px solid black; border-right: 1px solid black;" align="left" valign="middle">ОГРНИП</td>
            <td style="border-bottom: 1px solid black;" align="right" valign="middle">{$ogrnip}</td>
        </tr>
        <tr>
            <td style="font-size: 10pt; font-weight: bold; border-bottom: 1px solid black; border-right: 1px solid black;" align="left" valign="middle">Наименование банка</td>
            <td style="border-bottom: 1px solid black;" align="right" valign="middle">{$bank_name}</td>
        </tr>
        <tr>
            <td style="font-size: 10pt; font-weight: bold; border-bottom: 1px solid black; border-right: 1px solid black;" align="left" valign="middle">Расположение банка (город)</td>
            <td style="border-bottom: 1px solid black;" align="right" valign="middle">{$bank_place}</td>
        </tr>
        <tr>
            <td style="font-size: 10pt; font-weight: bold; border-bottom: 1px solid black; border-right: 1px solid black;" align="left" valign="middle">Кор/сч банка</td>
            <td style="border-bottom: 1px solid black;" align="right" valign="middle">{$bank_cor_wallet}</td>
        </tr>
        <tr>
            <td style="font-size: 10pt; font-weight: bold; border-bottom: 1px solid black; border-right: 1px solid black;" align="left" valign="middle">Бик</td>
            <td style="border-bottom: 1px solid black;" align="right" valign="middle">{$bank_bik}</td>
        </tr>
        <tr>
            <td style="font-size: 10pt; font-weight: bold; border-right: 1px solid black;" align="left" valign="middle">р/сч</td>
            <td align="right" valign="middle">{$bank_user_wallet}</td>
        </tr>
    </tbody>
</table>
<br/>
<br/>
<br/>
<table cellspacing="0" cellpadding="10" border="0" width="100%">
    <tr>
        <td width="50%">
            Индивидуальный предприниматель
        </td>
        <td>
            ______________________
        </td>
        <td>
            ________________
        </td>
    </tr>
    <tr>
        <td width="50%">
            <i>(наименование организации)</i> М.П.
        </td>
        <td>
            (Ф.И.О.)(подпись)
        </td>
        <td>

        </td>
    </tr>
</table>
