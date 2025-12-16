<table width="530" style="font-size:8px;">
    <tbody>
    <tr>
        <td align="right">
            <p align="right"><b>Директору {$organization->name}</b></p>
            <p align="right"><b>Фетисовой Наталье Валерьевне</b></p>
            <p align="right"><b>От: {$lastname|escape} {$firstname|escape} {$patronymic|escape} {$birth|escape} г.р.</b></p>
        </td>
    </tr>
    <tr>
        <td align="center">
            <h2><b>Заявление</b></h2>
        </td>
    </tr>
    <tr>
        <td align="left">
            <p>
                Я, {$lastname|escape} {$firstname|escape} {$patronymic|escape} {$birth|escape} г.р.,
                паспорт серия {$passport_serial|escape}, номер {$passport_number|escape}, выдан
                {$passport_issued|escape} от {$passport_date|date}, прошу удалить мой личный кабинет на
                сайте {$organization->name}.
            </p>
        </td>
    </tr>
    <tr>
        <td align="left">
            <p>Дата: <b>{if !$date}{''|date:'d.m.Y'}{else}{$date}{/if}</b></p>
        </td>
    </tr>
    {if $asp}
        <tr>
            <td align="left">
                <p>АСП: {$asp}</p>
            </td>
        </tr>
    {/if}
    </tbody>
</table>
