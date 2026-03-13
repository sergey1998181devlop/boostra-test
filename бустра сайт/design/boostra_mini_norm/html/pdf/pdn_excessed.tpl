<div style="text-align: center; font-size: 10px">
    <b>ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ <em><br>МИКРОКРЕДИТНАЯ КОМПАНИЯ "{$organization->short_name}"</em></b><br>
    {$organization->address}<br>ИНН {$organization->inn}/ КПП {$organization->kpp}
</div>
<div></div>
<div style="text-align: center; margin-top: 30px; font-size: 14px; font-weight: bold">
    Уведомление
</div>
<div></div>
<div style="text-align: justify">
    Уважаемый клиент {$full_name}, уведомляем Вас, что при расчете показателя долговой нагрузки, величина вашего ПДН составила {$pdn}%.
</div>
<div style="text-align: justify">
    В соответствии с пунктом 5 статьи 5.1 Федерального закона №353-ФЗ, о доведении до сведения заемщика - физического лица информации о значении показателя долговой нагрузки, рассчитанном в отношении него при принятии решения о предоставлении кредита (займа) или увеличении лимита кредитования», доводим до Вашего сведения, о наличии повышенного риска неисполнения Вами обязательств по потребительскому кредиту (займу), в связи с которым рассчитывался показатель долговой нагрузки, и риска применения за такое неисполнение штрафных санкций и возможности негативного влияния на условия кредитования.
</div>

<div style="text-align: justify">
    Подписывая настоящее уведомление - Заявитель (Заемщик) подтверждает факт ознакомления с ним:
</div>
<div style="border-width: 1px; border-bottom-width: 1px; border-left-width: 1px; border-right-width: 1px">Клиент:
    Ф.И.О.: {$full_name}
    Дата рождения: {$birth}
    Паспорт серия {$passport_serial} № {$passport_number} Выдан
    {$passport_issued} от {$passport_date}
    Адрес регистрации:
    {$regregion},
    {$regcity},
    {$regstreet},
    д. {$reghousing},
    кв. {$regroom}
    {if !empty($sms) && $sms != 0}
        <br>
        АСП Клиента: {$sms}
    {/if}
    <br>Дата получения: {$receiving_date}
</div>