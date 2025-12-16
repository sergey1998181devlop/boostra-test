<!-- Шапка -->
<p style="text-align: center; font-weight: normal">(Форма заявления на возврат денежных средств за дополнительные услуги)</p>

<!-- От кого -->
<table style="font-weight: normal">
    <tr>
        <td></td>
        <td style="text-align: right">Директору {$organization->name} Вороному И.Ю</td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align: right">{$organization->address}</td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align: right">От {$user->lastname|escape} {$user->firstname|escape} {$user->patronymic|escape}, {$user->birth|escape} г.р.,паспорт {$user->pasport_serial|escape} выдан {$user->passport_date|escape} г. {$user->birth|escape}</td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align: right">{$user->passport_issued|escape}</td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align: right">Зарегистрированного по адресу: {$user->registration_address|escape}</td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align: right">Телефон: {$user->phone_mobile|escape}</td>
    </tr>
    {if $user->email}
        <tr>
            <td></td>
            <td style="text-align: right">E-mail: {$user->email|escape}</td>
        </tr>
    {/if}
</table>

<!-- Заявление -->
<h2 style="text-align: center; font-weight: normal">ЗАЯВЛЕНИЕ
    <br>
    <span style="font-size: 12px;">на возврат денежных средств за дополнительные услуги</span>
</h2>
<h1>&nbsp;</h1>
<h1>&nbsp;</h1>
<!-- Текст заявления -->
<p>{$service->date_added|date} между {$user->lastname|escape} {$user->firstname|escape} {$user->patronymic|escape}, {$user->birth|escape} г.р., паспорт: {$user->pasport_serial|escape}0000 001001 выдан {$user->pasport_date|escape} г. {$user->passport_issued|escape} и {$organization->name} был заключен договор займа {$loan->number|escape}.</p>
<p>При оформлении договора займа {$loan->number|escape} от {$service->date_added|date} мною была приобретена дополнительная услуга ({$service->title}) на сумму {$service->amount|escape} руб.</p>
<p>На основании изложенного прошу оформить отказ от дополнительной услуги и вернуть денежные средства в размере {if $service->discount_refunded} {$service->amount/2|escape} руб. (оставшуюся часть) {else} {$service->amount|escape} руб.{/if} </p>
<p>Направляя данное заявление я подтверждаю, что отказываюсь от приобретенной услуги и что согласен с возвратом денежных средств на мою банковскую карту, с которой была оплачена стоимость дополнительных услуг.</p>
<h1>&nbsp;</h1>
<h1>&nbsp;</h1>
<h1>&nbsp;</h1>
<h1>&nbsp;</h1>
<h1>&nbsp;</h1>
<p>Приложение:</p>
<p>Копия документа, подтверждающего оплату дополнительной услуги;</p>
<p>Копия паспорта заявителя.</p>
<h1>&nbsp;</h1>
<h1>&nbsp;</h1>
<div style="border-width: 1px 1px 1px 1px;">
    Клиент: Ф.И.О.: {$user->lastname|escape} {$user->firstname|escape} {$user->patronymic|escape} Дата рождения: {$user->birth|date} Паспорт серия {$user->passport_serial|escape} <br>
    № {$user->passport_number|escape} Выдан {$user->passport_issued|escape} от {$user->passport_date|date} <br>
    Адрес регистрации: {$user->registration_address|escape} <br>
    АСП клиента: {$asp->code|escape} <br>
    Дата получения {$asp->created|date}
</div>
