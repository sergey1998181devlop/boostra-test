{literal}
    <style>
        body {
            font-family: "Times New Roman", serif;
            line-height: 1.4;
            margin: 20px;
            color: #000;
        }

        .bg-color {
            background-color: #c4c4c4;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .title {
            font-size: 16pt;
            font-weight: bold;
        }

        .content {
            padding: 0 40px;
            text-align: justify;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            padding: 5px;
        }
    </style>
{/literal}

{* функция для сокращённого ФИО, если понадобится *}
{function name=getFioShort}
    {$lastname|escape} {$firstname|mb_substr:0:1}.{$patronymic|mb_substr:0:1}.
{/function}

<table border="0" cellpadding="0" cellspacing="0">


    <tr>
        <td class="right">
            {getFioShort},<br>
            паспорт гр. РФ {$passport_serial|escape}<br>
            выдан {$passport_issued|escape} {$subdivision_code|escape}
        </td>
    </tr>
    <tr>
        <td class="right">
            от Общества с ограниченной ответственностью<br>
            Микрокредитная компания «{$organization->name|escape}»
        </td>
    </tr>
    <br>
    
    <tr>
        <td class="center title">УВЕДОМЛЕНИЕ ЗАЕМЩИКУ</td>
    </tr>

    <tr>
        <td class="content">
            <p>
                В ответ на письмо с просьбой не осуществления деятельности по взысканию просроченной задолженности
                в рамках договора займа №&nbsp;{$contract_number|escape} от {$zaim_date|date_format:"%d.%m.%Y"|escape}
                сообщаем, что Ваша просьба не удовлетворена Кредитором.
            </p>
            <p>
                Ожидаем исполнения договорных обязательств в срок до «{$payment_date|date_format:"%d.%m.%Y"|escape}».
            </p>
        </td>
    </tr>
</table>
