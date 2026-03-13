<style>
    .rotate {
        transform: rotateZ(90deg);
    }
</style>

<table>
    <tr>
        <td colspan="4" align="center">Расчет <br>
            начислений и поступивших платежей по договору № ??? от ???
        </td>
    </tr>
    <tr>
        <td colspan="4"></td>
    </tr>
    <tr>
        <td colspan="3">Заемщик (Ф.И.О.): {$order->lastname|escape} {$order->firstname|escape} {$order->patronymic|escape}</td>
        <td width="200px">Дата составления: ???</td>
    </tr>
    <tr>
        <td colspan="4"></td>
    </tr>
    <tr>
        <td colspan="2">Первоначальная сумма займа: ??? руб.</td>
        <td colspan="2">Оплачено всего: ??? руб.</td>
    </tr>
    <tr>
        <td colspan="2">Дата выдачи займа:???</td>
        <td colspan="2">Из них в погашение процентов ??? руб.</td>
    </tr>
    <tr>
        <td colspan="2">Срок займа (в днях): ???</td>
        <td colspan="2">в погашение основного долга ??? руб.</td>
    </tr>
    <tr>
        <td colspan="2">Процентная ставка (% в день) ???</td>
        <td colspan="2">в погашение штрафов ??? руб.</td>
    </tr>
    <tr>
        <td colspan="2">Процентная ставка (% годовых) ???</td>
        <td colspan="2">в погашение иных платежей ??? руб.</td>
    </tr>
    <tr>
        <td colspan="4"></td>
    </tr>
    <tr>
        <td colspan="2">Дата погашения займа: ???</td>
        <td colspan="2">Общая сумма задолженности: ??? руб.</td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td colspan="2">В том числе по основному долгу ??? руб.</td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td colspan="2">по процентам ??? руб.</td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td colspan="2">по штрафам ??? руб.</td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td colspan="2">по иным платежам ??? руб.</td>
    </tr>
</table>

<br><br>

<table border="2" cellpadding="2" align="center">
    <tr>
        <td colspan="7">Начислено</td>
    </tr>
    <tr>
        <td class="rotate">Дата расчета</td>
        <td>Количество дней с даты получения займа</td>
        <td>Процентная ставка в день, %</td>
        <td>Сумма начисленных процентов в день, руб.</td>
        <td>Сумма процентов накопительным итогом, руб.</td>
        <td>Сумма штрафов в день, руб.</td>
        <td>Сумма иных платежей в день, руб. (с указанием назначения платежа)</td>
    </tr>
    <tr>
        <td>???</td>
        <td>???</td>
        <td>???</td>
        <td>???</td>
        <td>???</td>
        <td>???</td>
        <td>???</td>
    </tr>
</table>

<br><br>

<table border="2" cellpadding="2" align="center">
    <tr>
        <td colspan="5">Оплачено</td>
    </tr>
    <tr>
        <td>Всего</td>
        <td>В счет основного долга</td>
        <td>В счет процентов</td>
        <td>В счет штрафов</td>
        <td>В счет иных платежей (с указанием назначения платежа)</td>
    </tr>
    <tr>
        <td>???</td>
        <td>???</td>
        <td>???</td>
        <td>???</td>
        <td>???</td>
    </tr>
</table>

<br><br>

<table border="2" cellpadding="2" align="center">
    <tr>
        <td colspan="5">Остаток задолженности</td>
    </tr>
    <tr>
        <td>Всего</td>
        <td>Основной долг</td>
        <td>Проценты</td>
        <td>Штрафы</td>
        <td>Иные платежи (с указанием назначения платежа)</td>
    </tr>
    <tr>
        <td>???</td>
        <td>???</td>
        <td>???</td>
        <td>???</td>
        <td>???</td>
    </tr>
</table>
