<table width="100%">
    <tbody>
    <tr>
        <td align="center" width="100%">
            <h3>АНКЕТА-ЗАЯВЛЕНИЕ на получение займа.</h3>
        </td>
    </tr>
    </tbody>
</table>
<div>&nbsp;</div>
<table border="1">
    <tbody>
    <tr>
        <td style="text-align: center; background-color: #ffffaa;" colspan="5"><strong>1.ПЕРСОНАЛЬНЫЕ ДАННЫЕ</strong></td>
    </tr>
    <tr>
        <td width="25%">Фамилия</td>
        <td valign="middle" width="25%">{$lastname|escape}</td>
        <td width="25%">Фамилия при рождении</td>
        <td width="25%">&nbsp;</td>
    </tr>
    <tr>
        <td>Имя</td>
        <td>{$firstname|escape}</td>
        <td>Имя при рождении</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>Отчество</td>
        <td>{$patronymic|escape}</td>
        <td>Отчество при рождении</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Пол</td>
        <td colspan="2" width="50%">{if $gender == 'male'}мужской{elseif $gender == 'female'}женский{/if}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Место рождения</td>
        <td colspan="2" width="50%">{$birth_place|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Дата рождения</td>
        <td colspan="2" width="50%">{if $birth}{$birth|date}{/if}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Девичья фамилия матери</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Семейный статус</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Количество иждевенцев</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Образование</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">ИНН</td>
        <td colspan="2" width="50%">{$inn|escape}</td>
    </tr>
    <tr>
        <td style="text-align: center; background-color: #ffffaa;" colspan="4"><strong>2. ДОКУМЕНТЫ УДОСТОВЕРЯЮЩИЕ ЛИЧНОСТЬ</strong></td>
    </tr>
    <tr>
        <td style="background-color: red; text-align: center;" colspan="4"><strong>ПАСПОРТ ГРАЖДАНИНА РОССИЙСКОЙ ФЕДЕРАЦИИ</strong></td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Серия</td>
        <td colspan="2" width="50%">{$passport_serial|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Номер</td>
        <td colspan="2" width="50%">{$passport_number|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Дата выдачи</td>
        <td colspan="2" width="50%">{if $passport_date}{$passport_date|date}{/if}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Кем выдан</td>
        <td colspan="2" width="50%">{$passport_issued|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Код подразделения</td>
        <td colspan="2" width="50%">{$passport_code|escape}</td>
    </tr>
    <tr>
        <td style="background-color: red; text-align: center;" colspan="4">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Серия</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Номер</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Дата выдачи</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Кем выдан</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Код подразделения</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td style="text-align: center; background-color: #ffffaa;" colspan="4"><strong>3. АДРЕСА И КОНТАКТНАЯ ИНФОРМАЦИЯ</strong></td>
    </tr>
    <tr>
        <td style="background-color: red; text-align: center;" colspan="4"><strong>АДРЕС РЕГИСТРАЦИИ</strong></td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Индекс</td>
        <td colspan="2" width="50%">{if $regindex}{$regindex|escape}{/if}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Регион/Район</td>
        <td colspan="2" width="50%">{$regregion|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Город/Нас.пункт</td>
        <td colspan="2" width="50%">{$regcity|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Улица</td>
        <td colspan="2" width="50%">{$regstreet|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Дом</td>
        <td colspan="2" width="50%">{$reghousing|escape} {if $regbuilding} стр.{$regbuilding|escape}{/if}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Квартира</td>
        <td colspan="2" width="50%">{$regroom|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Телефон</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td style="background-color: red; text-align: center;" colspan="4"><strong>АДРЕС ФАКТИЧЕСКОГО ПРОЖИВАНИЯ</strong></td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Совпадает с регистрацией</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Индекс</td>
        <td colspan="2" width="50%">{$faktindex|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Регион/Район</td>
        <td colspan="2" width="50%">{$faktregion|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Город/Нас.пункт</td>
        <td colspan="2" width="50%">{$faktcity|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Улица</td>
        <td colspan="2" width="50%">{$faktstreet|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Дом</td>
        <td colspan="2" width="50%">{$fakthousing|escape} {if $faktbuilding}, стр.{$faktbuilding|escape}{/if}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Квартира</td>
        <td colspan="2" width="50%">{$faktroom|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Телефон</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Мобильный телефон</td>
        <td colspan="2" width="50%">{$phone|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Тип собственности</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Проживание (лет)</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Проживание (Месяцев)</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td style="background-color: red; text-align: center;" colspan="4"><strong>Дополнительные номера для связи</strong></td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Телефон мобильный</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td style="text-align: center; background-color: #ffffaa;" colspan="4"><strong>4. ДОХОДЫ</strong></td>
    </tr>
    <tr>
        <td style="background-color: red; text-align: center;" colspan="4"><strong>ПОСТОЯННАЯ ТЕКУЩАЯ ЗАНЯТОСТЬ</strong></td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Ежемесячный доход</td>
        <td colspan="2" width="50%">{$income|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Название организации</td>
        <td colspan="2" width="50%">{$workplace|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Адрес организации</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">График занятости</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Должность</td>
        <td colspan="2" width="50%">{$profession|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Стаж работы (лет)</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Стаж работы (месяцев)</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Сфера деятельности</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Штат</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">ФИО руководителя</td>
        <td colspan="2" width="50%">{$chief_name|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Телефон руководителя</td>
        <td colspan="2" width="50%">{$chief_phone|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Телефон организации</td>
        <td colspan="2" width="50%">{$workphone|escape}</td>
    </tr>
    <tr>
        <td style="background-color: red; text-align: center;" colspan="4"><strong>ЛИЧНЫЕ РАСХОДЫ(ежемесячные обязательные выплаты по кредитам, алименты, коммунальные платежи и т.д.)</strong></td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Наличие просроченных задолженностей</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Уточнение</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Сумма расходов</td>
        <td colspan="2" width="50%">{$expenses|escape}</td>
    </tr>
    <tr>
        <td style="background-color: red; text-align: center;" colspan="4"><strong>ДОПОЛНИТЕЛЬНЫЙ ДОХОД</strong></td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Тип дополнительного дохода</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Сумма дополнительного дохода</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td style="background-color: red; text-align: center;" colspan="4"><strong>СОБСТВЕННОСТЬ (если есть)</strong></td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Марка автомобиля</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Номер автомобиля</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Адрес</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">&nbsp;</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td style="text-align: center; background-color: #ffffaa;" colspan="4"><strong>5. ПРОВЕРКА ПО ПЕРЕЧНЯМ РФМ</strong></td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Проверка по перечню организаций и физических лиц, в отношении которых имеются сведения об их причастности к экстремистской деятельности или терроризму, произведена {''|date:'Y-m-d'}</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Проверка по перечню лиц, в отношении которых действует решение Комиссии о замораживании (блокировании) принадлежащих им денежных средств или иного имущества, произведена {''|date:'Y-m-d'}</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Проверка по перечню организаций и физических лиц, в отношении которых имеются сведения об их причастности к распространению оружия массового уничтожения, произведена {''|date:'Y-m-d'}</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    </tbody>
</table>
<p>&nbsp;</p>
<table width="100%">
    <tbody>
    <tr>
        <td>Заполнив и подписав настоящую анкету, я понимаю и соглашаюсь с тем, что:<br />1. Я предоставил Займодавцу Анкету-заявление на получение займа.<br />2. Информация, предоставленная мной Займодавцу (в том числе в устной форме), является полной, точной и достоверной во всех отношениях.<br />3. Я обязуюсь незамедлительно уведомлять Займодавца о любых изменениях в информации, предоставленной мною Займодавцу.<br />4. Я выражаю свое согласие на предоставление Займодавцем (в случае нарушения мной условий погашения займа и/или процентов, а также любых платежей по Договору Займа) информации, связанной с заключением и исполнением Договора Займа (в том числе о суммах задолженности), новому Кредитору в связи с уступкой Займодавцем требования к Заемщику.<br />5. Займодавец проводит любые требуемые, по своему мнению, проверки (в частности, может связаться в любой момент времени, (в том числе, в случае принудительного исполнения прав по Договору займа) с моим работодателем для проверки и получения любой необходимой информации). Данное согласие действует бессрочно со дня его оформления. Данное согласие сохраняет силу в течение всего срока действия Договора.<br />6. В случае принятия отрицательного решения, Займодавец не обязан возвращать мне настоящую Анкету-Заявление ни оригинал, ни копию.<br />7. Принятие к рассмотрению моей Анкеты &ndash; заявления не означает возникновения у Займодавца обязательства по предоставлению мне займа.<br />8. В случае отказа в предоставлении займа Займодавец не сообщает причин отказа.</td>
    </tr>
    <tr style="width: 100%;">
        <td style="font-size: 10px; width: 130.491%;"><br />
            <h4>Заявитель (ФИО полностью)</h4>
        </td>
        <td style="width: 1.44509%;"><br />
            <h4>Подписано АСП {$asp|escape}</h4>
            <small>подпись</small></td>
        <td style="font-size: 10px; width: 30%;"><br />
            <h4>{$lastname|escape} {$firstname|escape} {$patronymic|escape}</h4>
            <small>ФИО</small></td>
    </tr>
    <tr>
        <td style="width: 164.827%;" colspan="5">
            <p>Клиент: Ф.И.О.: {$lastname|escape} {$firstname|escape} {$patronymic|escape} Дата рождения: {$birth|date} Паспорт серия {$passport_serial|escape} № {$passport_number|escape} Выдан {$passport_issued|escape} от {$passport_date|date}</p>
            <p>Адрес регистрации: {$regindex|escape}, {$regregion|escape}, {$regcity|escape}, {$regstreet|escape} ул, д. {$reghousing|escape}, кв. {$regroom|escape}</p>
            <p>АСП клиента: <strong>{$asp|escape}</strong></p>
            <p>Дата получения<strong>{$created|date}</strong></p>
        </td>
    </tr>
    <tr>
        <td style="width: 130.491%;"><img src="{$config-&gt;root_url}/design/boostra_mini_norm/html/pdf/i/stamp_boostra.png" width="100" /></td>
        <td style="font-size: 10px; width: 1.44509%;"><br />
            <h4>Директор</h4>
        </td>
        <td style="font-size: 10px; width: 30%;"><br />
            <h4>Смелов С.Б.</h4>
        </td>
    </tr>
    </tbody>
</table>