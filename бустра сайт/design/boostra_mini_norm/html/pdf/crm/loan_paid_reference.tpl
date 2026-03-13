<div style="text-align: center">
    <span style="font-style: italic">
        <h3>
            МИКРОКРЕДИТНАЯ КОМПАНИЯ<br>
            <span style="text-decoration: underline">ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "{$organization->short_name|mb_strtoupper}"</span>
        </h3>
    </span>
    <span>
        <span>{$organization->address}</span><br>
        <span>ИНН {$organization->inn} КПП {$organization->kpp} ОГРН {$organization->ogrn} </span>
    </span>
</div>
<hr>
<table>
    <tr>
        <td>{$close_date|date}</td>
        <td style="text-align: right">г.Самара</td>
    </tr>
</table>
<h1>&nbsp;</h1>
<table style="font-weight: bold">
    <tr>
        <td></td>
        <td style="text-align: right">{$lastname|escape} {$firstname|escape} {$patronymic|escape} {$birth|escape}</td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align: right">{$registration_address|escape}</td>
    </tr>
</table>
<h1>&nbsp;</h1>
<div style="text-align: center; font-weight: bold">
     {if $gender == 'male'}Уважаемый{elseif $gender == 'female'}Уважаемая{/if} {$firstname|escape} {$patronymic|escape}!
</div>

<p style="text-indent: 30px;">
{$date|date} г. между Вами и МКК ООО «{$organization->short_name}» был заключен договор займа № {$number|escape} на сумму
{$amount|escape} ({$amount_in_string|escape}) рублей. Сумма займа и процентов подлежала оплате {$plan_close_date|date}
 По состоянию на {$now|date} г. все обязательства по договору займа {$number|escape} от {$date|date} г.
исполнены в полном объеме.</p>
<h1>&nbsp;</h1>
<h1>&nbsp;</h1>
<h1>&nbsp;</h1>
<h1>&nbsp;</h1>
<h1>&nbsp;</h1>
<h1>&nbsp;</h1>
<h1>&nbsp;</h1>
<h1>&nbsp;</h1>
<h1>&nbsp;</h1>
<h1>&nbsp;</h1>
<table style="font-weight: normal; vertical-align: center">
    <tbody>
    <tr>
        <td style="text-align: right; width: 180px"><br />
            <br>
            <p>Директор МКК ООО «{$organization->short_name}»</p>
        </td>
        <td style="width: 150px;">
{*            <img src="{$root_url|escape}/design/boostra_mini_norm/html/pdf/i/stamp_boostra.png" width="105">*}
        </td>
        <td style="width: 100px">
{*            <img src="{$root_url|escape}/design/boostra_mini_norm/html/pdf/i/sign_voronoi.jpg" width="80" alt="">*}
        </td>
        <td style="width: 100px; text-align: left"><br />
            <br>
            <p>{$organization->director}</p>
        </td>
    </tr>
    </tbody>
</table>