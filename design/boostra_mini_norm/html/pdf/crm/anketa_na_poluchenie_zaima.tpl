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
<table border="1" cellpadding="2">
    <tbody>
    <tr>
        <td style="text-align: center; background-color: #ffffaa;" colspan="5"><strong>1.ПЕРСОНАЛЬНЫЕ ДАННЫЕ</strong></td>
    </tr>
    <tr>
        <td width="25%">Фамилия</td>
        <td valign="middle" width="25%">{$order->lastname|escape}</td>
        <td width="25%">Фамилия при рождении</td>
        <td width="25%">&nbsp;</td>
    </tr>
    <tr>
        <td>Имя</td>
        <td>{$order->firstname|escape}</td>
        <td>Имя при рождении</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>Отчество</td>
        <td>{$order->patronymic|escape}</td>
        <td>Отчество при рождении</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Пол</td>
        <td colspan="2" width="50%">{if $order->gender == 'male'}мужской{elseif $order->gender == 'female'}женский{/if}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Место рождения</td>
        <td colspan="2" width="50%">{$order->birth_place|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Дата рождения</td>
        <td colspan="2" width="50%">{if $order->birth}{$order->birth|date}{/if}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Девичья фамилия матери</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Семейный статус</td>
        <td colspan="2" width="50%">{$order->marital_status}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Количество иждевенцев</td>
        <td colspan="2" width="50%">{if $order->childs_count}{$order->childs_count|intval}{/if}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Образование</td>
        <td colspan="2" width="50%">{if $order->education}{$order->education}{/if}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">ИНН</td>
        <td colspan="2" width="50%">{$order->inn|escape}</td>
    </tr>
    <tr>
        <td style="text-align: center; background-color: #ffffaa;" colspan="4"><strong>2. ДОКУМЕНТЫ УДОСТОВЕРЯЮЩИЕ ЛИЧНОСТЬ</strong></td>
    </tr>
    <tr>
        <td style="background-color: red; text-align: center;" colspan="4"><strong>ПАСПОРТ ГРАЖДАНИНА РОССИЙСКОЙ ФЕДЕРАЦИИ</strong></td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Серия</td>
        <td colspan="2" width="50%">{$order->passport_serial|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Номер</td>
        <td colspan="2" width="50%">{$order->passport_number|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Дата выдачи</td>
        <td colspan="2" width="50%">{if $order->passport_date}{$order->passport_date|date}{/if}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Кем выдан</td>
        <td colspan="2" width="50%">{$order->passport_issued|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Код подразделения</td>
        <td colspan="2" width="50%">{$order->subdivision_code|escape}</td>
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
        <td colspan="2" width="50%">{if $order->Regindex}{$order->Regindex|escape}{/if}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Регион/Район</td>
        <td colspan="2" width="50%">{$order->Regregion|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Город/Нас.пункт</td>
        <td colspan="2" width="50%">{$order->Regcity|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Улица</td>
        <td colspan="2" width="50%">{$order->Regstreet|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Дом</td>
        <td colspan="2" width="50%">{$order->Reghousing|escape} {if $order->Regbuilding} стр.{$order->Regbuilding|escape}{/if}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Квартира</td>
        <td colspan="2" width="50%">{$order->Regroom|escape}</td>
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
        <td colspan="2" width="50%">{$order->Faktindex|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Регион/Район</td>
        <td colspan="2" width="50%">{$order->Faktregion|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Город/Нас.пункт</td>
        <td colspan="2" width="50%">{$order->Faktcity|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Улица</td>
        <td colspan="2" width="50%">{$order->Faktstreet|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Дом</td>
        <td colspan="2" width="50%">{$order->Fakthousing|escape} {if $order->Faktbuilding}, стр.{$order->Faktbuilding|escape}{/if}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Квартира</td>
        <td colspan="2" width="50%">{$order->Faktroom|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Телефон</td>
        <td colspan="2" width="50%">{$order->phone|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Мобильный телефон</td>
        <td colspan="2" width="50%">{$order->phone_mobile|escape}</td>
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
        <td colspan="2" width="50%">{$order->income_base|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Название организации</td>
        <td colspan="2" width="50%">{$order->workplace|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Адрес организации</td>
        <td colspan="2" width="50%">{$order->work_address|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">График занятости</td>
        <td colspan="2" width="50%">&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Должность</td>
        <td colspan="2" width="50%">{$order->profession|escape}</td>
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
        <td colspan="2" width="50%">{$order->work_scope|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Штат</td>
        <td colspan="2" width="50%">{$order->work_staff|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">ФИО руководителя</td>
        <td colspan="2" width="50%">{$order->workdirector_name|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Телефон руководителя</td>
        <td colspan="2" width="50%">{$order->chief_phone|escape}</td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Телефон организации</td>
        <td colspan="2" width="50%">{$order->work_phone|escape}</td>
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
        <td colspan="2" width="50%">{$order->expenses|escape}</td>
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
        <td colspan="2" width="50%" style="text-align: left"></td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Проверка по перечню лиц, в отношении которых действует решение Комиссии о замораживании (блокировании) принадлежащих им денежных средств или иного имущества, произведена {''|date:'Y-m-d'}</td>
        <td colspan="2" width="50%" style="text-align: left"></td>
    </tr>
    <tr>
        <td colspan="2" width="50%">Проверка по перечню организаций и физических лиц, в отношении которых имеются сведения об их причастности к распространению оружия массового уничтожения, произведена {''|date:'Y-m-d'}</td>
        <td colspan="2" width="50%" style="text-align: left"></td>
    </tr>
    </tbody>
</table>
<p>&nbsp;</p>
<table >
    <tbody>
    <tr>
        <td>Заполнив и подписав настоящую анкету, я понимаю и соглашаюсь с тем, что:<br />
            1. Я предоставил Займодавцу Анкету-заявление на получение займа.<br />
            2. Информация, предоставленная мной Займодавцу (в том числе в устной форме), является полной, точной и достоверной во всех отношениях.<br />
            3. Я обязуюсь незамедлительно уведомлять Займодавца о любых изменениях в информации, предоставленной мною Займодавцу.<br />
            4. Я выражаю свое согласие на предоставление Займодавцем (в случае нарушения мной условий погашения займа и/или процентов, а также любых платежей по Договору Займа) информации, связанной с заключением и исполнением Договора Займа (в том числе о суммах задолженности), новому Кредитору в связи с уступкой Займодавцем требования к Заемщику.<br />
            5. Займодавец проводит любые требуемые, по своему мнению, проверки (в частности, может связаться в любой момент времени, (в том числе, в случае принудительного исполнения прав по Договору займа) с моим работодателем для проверки и получения любой необходимой информации). Данное согласие действует бессрочно со дня его оформления. Данное согласие сохраняет силу в течение всего срока действия Договора.<br />6. В случае принятия отрицательного решения, Займодавец не обязан возвращать мне настоящую Анкету-Заявление ни оригинал, ни копию.<br />7. Принятие к рассмотрению моей Анкеты &ndash; заявления не означает возникновения у Займодавца обязательства по предоставлению мне займа.<br />8. В случае отказа в предоставлении займа Займодавец не сообщает причин отказа.</td>
    </tr>
    <tr >
        <td style="font-size: 10px; width: 200px;"><br />
            <h4>Заявитель (ФИО полностью)</h4>
        </td>
        <td style="font-size: 10px; width: 120px;"><br />
            <h4>Подписано АСП {$order->asp|escape}</h4>
            <small>подпись</small>
        </td>
        <td style="font-size: 10px; width: 200px ;"><br />
            <h4>{$order->lastname|escape} {$order->firstname|escape} {$order->patronymic|escape}</h4>
            <small>ФИО</small>
        </td>
    </tr>
    <tr><td><p></p></td></tr>
    <tr>
        <td colspan="5" style="border-width: 1px 1px 1px 1px">
                Клиент: Ф.И.О.: {$order->lastname|escape} {$order->firstname|escape} {$order->patronymic|escape} Дата рождения: {$order->birth|date} Паспорт серия {$order->passport_serial|escape} <br>
                № {$order->passport_number|escape} Выдан {$order->passport_issued|escape} от {$order->passport_date|date} <br>
                Адрес регистрации: {$order->Regindex|escape}, {$order->Regregion|escape}, {$order->Regcity|escape}, {$order->Regstreet|escape} ул, д. {$order->Reghousing|escape}, кв. {$order->Regroom|escape} <br>
                АСП клиента: {$order->asp|escape} <br>
                Дата получения {$order->created|date}
        </td>
    </tr>
    <tr><td><p></p></td></tr>
    <tr>
        <td style="width: 150px;">
            <img src="{$order->config->root_url}/design/boostra_mini_norm/html/pdf/i/stamp_boostra.png" width="100"  alt=""/>
        </td>
        <td style="font-size: 10px; 50px;"><br />
            <h4>Директор</h4>
        </td>
        <td style="width: 100px">
            <img src="{$order->config->root_url}/design/boostra_mini_norm/html/pdf/i/signa_smelov.png" width="50" alt="">
        </td>
        <td style="font-size: 10px; width: 100px"><br />
            <h4>Смелов С.Б.</h4>
        </td>
    </tr>
    </tbody>
</table>