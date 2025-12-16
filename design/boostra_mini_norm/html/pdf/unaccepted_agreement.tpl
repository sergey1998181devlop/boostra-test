<div style="text-align: center; font-size: 10px">
    <b><em>МИКРОКРЕДИТНАЯ КОМПАНИЯ<br>ОБЩЕСТВО С ОГРАНИЧЕННОЙ ОТВЕТСТВЕННОСТЬЮ "{$organization->short_name}"</em></b><br>
    {$organization->address}<br>ИНН {$organization->inn} КПП {$organization->kpp} ОГРН {$organization->ogrn}
</div>
<div></div>
<div style="text-align: right; margin-top: 30px; font-weight: bold">
    Клиент {$full_name}
    <br>Адрес:
    {if $regindex}
        {$regindex},
    {/if}
    {if $regcity_shorttype}
        {$regcity_shorttype}.
    {else}
        г.
    {/if}
    {$regcity},
    {if $regstreet_shorttype}
        {$regstreet_shorttype}.
    {else}
        ул.
    {/if}
    {$regstreet}
    {if $regroom}, кв. {$regroom}{/if}
</div>
<div></div>
<div style="text-align: center; margin-top: 30px; font-size: 14px; font-weight: bold">
    Уведомление
</div>
<div></div>
<div style="text-align: justify">МКК ООО «Бустра» уведомляет Вас о смене Ваших персональных данных во внутренней системе Общества:
</div>
<div style="text-align: justify">Прежние данные:
    {$full_name}, {$birth} г.р.,
    паспорт серия {$old_passport_serial}
    номер {$old_passport_number},
    дата выдачи {$passport_date} г., код подразделения {$subdivision_code},
    выдан {$passport_issued}{if !empty($birth_place)}, место рождения {$birth_place}{/if}.
    Номер телефона {$user->phone_mobile}.
</div>
<div style="text-align: justify">Новые данные:
    {$new_full_name},
    {$new_birth} г.р.,
    паспорт серия {$new_passport_serial}
    номер {$new_passport_number},
    дата выдачи {$new_passport_date} г., код подразделения {$new_subdivision_code},
    выдан {$new_passport_issued}{if !empty($new_birth_place)}, место рождения {$new_birth_place}{/if}.
    Номер телефона {$new_phone_mobile}.
</div>
<div></div>
<div></div>
<table width="100%">
    <tr>
        <td>Директор {$organization->name}</td>
        <td style="text-align: right">{$organization->director}</td>
    </tr>
</table>