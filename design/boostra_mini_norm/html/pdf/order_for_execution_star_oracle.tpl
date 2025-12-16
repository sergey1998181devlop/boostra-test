{literal}
    <style>
        .bg-color {background-color: #c4c4c4}
    </style>
{/literal}
{function name=getFio}
    {$lastname} {$firstname|mb_substr:0:1}.{$patronymic|mb_substr:0:1}.
{/function}

{assign var="org_name" value=$organization->name}
{assign var="director" value=$organization->director|fio:'dative'}
{assign var="address" value=$organization->address|wordwrap:70:"<br>"}
{assign var="inn" value=$organization->inn}
{assign var="kpp" value=$organization->kpp}
{assign var="ogrn" value=$organization->ogrn}

<table cellpadding="5" cellspacing="0" border="0" width="100%" style="line-height: 1.5">
    <tbody>
    <tr>
        <td align="right"> <br/>
            Генеральному директору <br/>
            {$org_name}<br/>
            {$director}<br/>
            Адрес: {$address}<br/>
            ИНН/КПП {$inn}/{$kpp}<br/>
            ОГРН {$ogrn}<br/>
        </td>
    </tr>
    <tr>
        <td align="center">
            <h3>Поручение об исполнении обязательства</h3>
        </td>
    </tr>
    <tr>
        <td>
            Я, {$lastname|escape} {$firstname|escape} {$patronymic|escape}, в соответствии со ст. 313 Гражданского кодекса РФ прошу перечислить денежные средства в размере {$amount} рублей по следующим реквизитам:<br/>

            ООО "ФИНТЕХ-МАРКЕТ", <br/>
            ИНН: 6317164496,<br/>
            КПП: 631701001,<br/>
            ОГРН: 1236300023849<br/>
            p/c 40702810929180016695 в ФИЛИАЛ "НИЖЕГОРОДСКИЙ" АО "АЛЬФА-БАНК",<br/>
            БИК 042202824<br/>
            K/c 30101810200000000824<br/>
            Назначение платежа: оплата по договору на приобретение ПО «Звездный Оракул» СВФСИС Nº26416 за  {$lastname|escape} {$firstname|escape} {$patronymic|escape}.<br/>
            Данную сумму прошу перечислить, в срок не позднее одного рабочего дня с момента подписания настоящего поручения.<br/>
            Я, подтверждаю, что согласен со стоимостью приобретаемой услуги и ознакомлен и согласен с Публичной офертой на оказание платных услуг сервиса, размещенной на сайте https://staroracle.ru.
        </td>
    </tr>
    <tr>
        <td style="line-height: 1">
            <p><b>Электронная подпись: </b></p>
            <p>Подписано простой электронной подписью</p>
            <p>Ф.И.О: <strong>{getFio}</strong></p>
            <p>Дата рождения: <strong>{$birth|date}</strong></p>
            <p>Паспорт: <strong>{$passport_serial|escape}, выдан {$passport_issued|escape} {$subdivision_code|escape}, дата выдачи {$passport_date|date}</strong></p>
            <p>Телефон: <strong>{$phone_mobile|escape}</strong></p>
            <p>Дата: <strong>{$document_created}</strong></p>
            <p>СМС-код: <strong>{$accept_sms|escape}</strong></p>
        </td>
    </tr>
    </tbody>
</table>
