<style>
    h1 {
        text-align: center;
        font-size: 18px;
        text-transform: uppercase;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        padding: 2px;
    }

    th, td {
        border: 1px solid #444;
        vertical-align: top;
    }

    th {
        background-color: #ccc;
        text-align: left;
    }

    .section-title {
        background-color: #eee;
        font-weight: bold;
        text-align: left;
    }

    .double-col {
        font-weight: bold;
        background-color: #ddd;
        text-align: center;
    }
</style>

<h1>Лист оценки платежеспособности заемщика</h1>

<div style="text-align: center; font-weight: bold; font-size: 12px;">
    <p>{$full_name}</p>
    <p style="font-size: 9px; font-style: italic;">(ФИО заемщика)</p>
</div>

<table>
    <tr>
        <th colspan="2" class="section-title">ОСНОВНЫЕ ПАРАМЕТРЫ ЗАПРАШИВАЕМОГО ЗАЙМА:</th>
    </tr>
    <tr>
        <td>СУММА:</td>
        <td>{$loan_amount}</td>
    </tr>
    <tr>
        <td>СРОК:</td>
        <td>{$period} дней</td>
    </tr>
    <tr>
        <td>ПРОЦЕНТНАЯ СТАВКА:</td>
        <td>{$percent}</td>
    </tr>
</table>

<br><br><br><br>

<table>
    <tr>
        <th colspan="2" class="double-col">КРИТЕРИЙ</th>
        <th class="double-col">РЕЗУЛЬТАТ</th>
    </tr>
    <tr>
        <th rowspan="7" class="section-title">ОБЩИЕ СВЕДЕНИЯ ПО ЗАЕМЩИКУ</th>
        <td>ВОЗРАСТ</td>
        <td>{$age}</td>
    </tr>
    <tr>
        <td>КАТЕГОРИЯ ЗАЕМЩИКА</td>
        <td>ФЛ</td>
    </tr>
    <tr>
        <td>МЕСТО ПРОЖИВАНИЯ</td>
        <td>{$fakt_address}</td>
    </tr>
    <tr>
        <td>МЕСТО РЕГИСТРАЦИИ</td>
        <td>{$reg_address}</td>
    </tr>
    <tr>
        <td>КОНТАКТНЫЙ ТЕЛЕФОН</td>
        <td>{$phone_mobile}</td>
    </tr>
    <tr>
        <td>ФАКТ БАНКРОТСТВА</td>
        <td>Отсутствует</td>
    </tr>
    <tr>
        <td>НАЛИЧИЕ ИСПОЛНИТЕЛЬНЫХ ПРОИЗВОДСТВ</td>
        <td>0</td>
    </tr>
    <tr>
        <th rowspan="4" class="section-title">СООТВЕТСТВИЕ ДОКУМЕНТОВ ОБЩИМ ТРЕБОВАНИЯМ И ИНФОРМАЦИИ ИЗ ОТКРЫТЫХ
            ИСТОЧНИКОВ
        </th>
        <td>ПАСПОРТ</td>
        <td>{$passport}</td>
    </tr>
    <tr>
        <td>СВЕДЕНИЯ ОБ ОРГАНИЗАЦИИ РАБОТОДАТЕЛЯ ЗАЕМЩИКА</td>
        <td>{$workplace}</td>
    </tr>
    <tr>
        <td>СВЕДЕНИЯ О СООТВЕТСТВИИ ЗАЯВЛЕННОЙ ДОЛЖНОСТИ</td>
        <td>{$profession}</td>
    </tr>
    <tr>
        <td>ЕЖЕМЕСЯЧНЫЙ ДОХОД</td>
        <td>{$income}</td>
    </tr>

    <tr>
        <th class="section-title">ФИНАНСОВЫЕ ПОКАЗАТЕЛИ</th>
        <td>ПДН</td>
        <td>{$pdn}</td>
    </tr>

    <tr>
        <td rowspan="4" class="section-title">ВЫЯВЛЕННЫЕ РИСК-ФАКТОРЫ</td>
        <td colspan="2" style="padding: 10px">
            1) текущий активный договор займа – отсутствует<br>
            2) информация о недееспособности – отсутствует<br>
            3) нахождение в процедуре банкротства – Отсутствует<br>
            4) наличие исполнительных производств – 0
        </td>
    </tr>
</table>

<br><br><br><br><br>

<table>
    <tr>
        <th colspan="2" class="section-title">ОСНОВНЫЕ ПАРАМЕТРЫ ОДОБРЕННОГО ЗАЙМА:</th>
    </tr>
    <tr>
        <td>СУММА:</td>
        <td>{$approved_loan_amount}</td>
    </tr>
    <tr>
        <td>СРОК:</td>
        <td>{$period} дней</td>
    </tr>
    <tr>
        <td>ПРОЦЕНТНАЯ СТАВКА:</td>
        <td>{$percent}</td>
    </tr>
</table>
<br><br>
<table>
    <tr>
        <td style="width: 150px; border: 1px solid white">ДАТА: {$issuance_date}<br><hr></td>
        <td style="width: 50px; border: 1px solid white"></td>
        <td style="width: auto; border: 1px solid white"> ВЕРИФИКАТОР: {$verificator}<br><hr></td>
    </tr>
</table>

