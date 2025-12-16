<h3 style="text-align: center"><em><u>УВЕДОМЛЕНИЕ<br>О предоставлении дополнительных услуг (работ, товаров)</u></em></h3>
<p>
    <strong>Я,</strong> {$full_name}, (далее - Заявитель), {$birth} г.р., паспорт: серия {$passport_serial}
    номер {$passport_number},
    выдан {$passport_date} {$passport_issued}, зарегистрирован(а) по адресу: {$reg_city}, {$reg_street},
    {$reg_housing}, {$reg_room}, фактически проживаю по адресу: {$fakt_city}, {$fakt_street}, {$fakt_housing}
    {if !empty($fakt_room) && $reg_room != $fakt_room}{$fakt_room}, {/if}{$phone_mobile}, {$email},
    понимая значение своих действий и руководя ими, согласен на
    предоставление следующей услуги.</p>
<table style="border-top-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-right-width: 1px">
    <tr>
        <td style="border-right-width: 1px; width: 100%; text-align: justify;">
            Дополнительная финансовая услуга «Кредитный доктор», предоставляется ООО «КБН» (ОГРН
            1232900003863. ИНН 2902090888. адрес: 164505, Архангельская область, Г. СЕВЕРОДВИНСК, ПРКТ ТРУДА, Д. 61, КВ. 65, http://kreditoff-net.ru/ (далее - Компания),<br>
            Стоимость данной услуги {$credit_doctor_amount} рублей.
            <br>
            Содержание услуги (работы, товара) - Предметом настоящей услуги является предоставление
            Пользователям Услуг по доступу к Сервису для целей снижения финансовой нагрузки для
            должников.<br>
            Предельная дата для отказа от дополнительных услуг (работ, товаров), на оказание (выполнение,
            реализацию) которых получено согласие заемщика в отношении каждой из дополнительных услуг
            (работ, товаров) - {$date_plus_30_days}.<br>
            Отказ от дополнительных услуг (работ, товаров) не влияет на условия договора потребительского
            займа, в том числе на увеличение размера процентной ставки по такому договору относительно
            размера процентной ставки по договору потребительского займа, заключенному с
            предоставлением заемщику данных услуг (работ, товаров).
        </td>
{*        <td style="text-align: left;">Согласие Заемщика<br> на приобретение услуги<br> «Кредитный доктор»*}
{*            <br>*}
{*            <br>*}
{*            <br>*}
{*            <hr >*}
{*            <p>{if $is_user_credit_doctor}V {else}X{/if}</p>*}
{*        </td>*}
    </tr>

</table>
<p style="border-top-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-right-width: 1px">Клиент:
    Ф.И.О.: {$full_name}, (далее - Заявитель), {$birth} г.р., паспорт: серия {$passport_serial}
    номер {$passport_number},
    выдан {$passport_date} {$passport_issued}<br>Адрес регистрации: {$reg_city}, {$reg_street}, {$reg_housing},
    {$reg_room}{if !empty($asp)}, фактически проживаю по адресу: {$fakt_city}, {$fakt_street}, {$fakt_housing}
    {if !empty($fakt_room) && $reg_room != $fakt_room}{$fakt_room}, {/if}{$phone_mobile}, {$email}<br>
АСП Клиента: {$asp}{/if}<br>Дата получения: {$current_date}
</p>
<style>
    td {
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
</style>