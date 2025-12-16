<table style="height: 2226px;" border="0" width="555" cellspacing="0">
<tbody>
<tr>
<td style="width: 17.4px;" align="left" height="16">&nbsp;</td>
<td style="width: 87.4833px;" align="left"><span style="font-size: small;">{$document_date|date}</span></td>
<td style="width: 44.6333px;" align="left">&nbsp;</td>
<td style="width: 44.65px;" align="left">&nbsp;</td>
<td style="width: 41.65px;" align="left">&nbsp;</td>
<td style="width: 108.45px;" align="left">&nbsp;</td>
<td style="width: 180.733px;" align="left">&nbsp;</td>
</tr>
<tr>
<td style="width: 17.4px;" align="left" height="46">&nbsp;</td>
<td style="width: 87.4833px;" align="left">&nbsp;</td>
<td style="width: 44.6333px;" align="left">&nbsp;</td>
<td style="width: 44.65px;" align="left">&nbsp;</td>
<td style="width: 41.65px;" align="left">&nbsp;</td>
<td style="width: 108.45px;" align="left">&nbsp;</td>
<td style="width: 180.733px;" colspan="5" align="left"><span style="font-size: small;">Мировому судье в {$tribunal}</span></td>
</tr>

<tr>
<td style="width: 17.4px;" align="left" height="121">&nbsp;</td>
<td style="width: 87.4833px;" align="left">&nbsp;</td>
<td style="width: 44.6333px;" align="left">&nbsp;</td>
<td style="width: 44.65px;" align="left">&nbsp;</td>
<td style="width: 41.65px;" align="left">&nbsp;</td>
<td style="width: 108.45px;" align="left" valign="top"><strong><u><span style="font-size: small;">Взыскатель:</span></u></strong></td>
<td style="width: 180.733px;" colspan="5" align="left"><span style="font-size: small;">ООО &laquo;Премьер номер 2&raquo;<br />344090, Ростовская обл., г.Ростов-На-Дону, Улица пр-кт, д. 7/9, офис 11.<br />Реквизиты:<br />ИНН/КПП 000000000/000000000, ОГРН 00000000000<br />Р/сч 000000000000000000<br />Филиал &laquo;ЦЕНТРАЛЬНЫЙ&raquo; Банка ВТБ (ПАО) г. Москва<br />к/счет 000000000000000000 БИК 000000000</span></td>
</tr>

<tr>
<td style="width: 17.4px;" align="left" height="151">&nbsp;</td>
<td style="width: 87.4833px;" align="left">&nbsp;</td>
<td style="width: 44.6333px;" align="left">&nbsp;</td>
<td style="width: 44.65px;" align="left">&nbsp;</td>
<td style="width: 41.65px;" align="left">&nbsp;</td>
<td style="width: 108.45px;" align="left" valign="top"><strong><u><span style="font-size: small;">Должник:</span></u></strong></td>
<td style="width: 180.733px;" colspan="5" align="left">
    <span style="font-size: small;">
        {$fio} {$birth} г.р. урож. {$birth_place}
        <br />
        Паспорт {$passport_series} № {$passport_number}
        <br />
        Выдан {$passport_issued}
        <br />
        Дата выдачи {$passport_date} г.
        <br />
        Код подразделения {$passport_code}
        <br />{$regaddress_full}
    </span>
</td>
</tr>

<tr>
<td style="width: 549px;" colspan="11" align="center" height="33"><span style="font-size: medium;"><strong>ЗАЯВЛЕНИЕ <br />О ВЫНЕСЕНИИ СУДЕБНОГО ПРИКАЗА </strong></span></td>
</tr>

<tr>
    <td style="width: 549px;" colspan="11" align="left" height="31">
        <span style="font-size: medium;"> 
            {$contract_date|date} г. ООО МКК "НА ЛИЧНОЕ+" и {$fio} {$birth} г.р. (далее - Должник) 
            заключили договор денежного займа № {$first_number}. 
        </span>
    </td>
</tr>
<tr>
    <td style="width: 549px;" colspan="11" align="left" height="61">
        <span style="font-size: medium;"> 
        В соответствии с вышеуказанным договором займа Должнику было предоставлено 
        {$body_summ} ({$body_summ|price_string}) рублей. сроком до {$return_date} 
        с ежедневным начислением процентов за пользование займом в размере 1 % (365% годовых). 
        Сумма займа была передана Заемщику по РКО ( приложение к договору ). 
        Таким образом, ООО МКК "НА ЛИЧНОЕ+" выполнила свои обязательства перед Заемщиком в полном объеме. 
        </span>
    </td>
</tr>
<tr>
<td style="width: 549px;" colspan="11" align="left" height="31"><span style="font-size: medium;"> 
    Должник обязан был обеспечить возврат (погашение) предоставленного займа путем оплаты единовременным платежом 
    в срок, указанный в Условиях договора микрозайма.</span></td>
</tr>
<tr>
<td style="width: 549px;" colspan="11" align="left" height="46"><strong><u><span style="font-size: medium;"> 
    В то же время Должник не исполнил обязанность по возврату всей суммы задолженности по договору займа, 
    по состоянию на {$document_date|date} размер его задолженности составлял 
    {$total_summ} ({$total_summ|price_string}) рублей, из которых
</span></u></strong></td>
</tr>
<tr>
<td style="width: 549px;" colspan="11" align="left" height="31"><em><span style="font-size: medium;"> 
    1. Сумма основного долга по состоянию на {$document_date|date} г. 
    в размере {$body_summ} (сумма займа по договору денежного займа № {$first_number}) рублей</span></em></td>
</tr>
<tr>
<td style="width: 549px;" colspan="11" align="left" height="89"><em><span style="font-size: medium;"> 
    2. Сумма процентов в размере 8 610 = 7 000 (сумма долга по договору цессии) * 1 %( процентная ставка)* 123 дней ( за период с 26.01.2020 г. по 29.05.2020 г.), 
    а, в соответствии с договором, максимальный размер процентов, неустойки (штрафы, пени), 
    иных мер ответственности по договору не может превышать полуторакратного размера суммы займа, 
    т.е. 10 500 рублей. <br />Таким образом, 7 000 (сумма займа по договору денежного займа № НП21-000000) рублей + 8 610 рублей (сумма процентов) &ndash; 0,00 рублей (сумма внесенных Заемщиком денежных средств за период с 20.02.20 г. по 29.05.2020 г.) = 15 610 рублей.</span></em></td>
</tr>
<tr>
<td style="width: 549px;" colspan="11" align="left" height="31"><span style="font-size: medium;"> В связи с неисполнением обязательств по оплате, задолженность была уступлена по договору цессии 000000-001 от 20.02.21 г. в ООО &laquo;Юридическая компания № 3&raquo; (№ 21)</span></td>
</tr>
<tr>
<td style="width: 549px;" colspan="11" align="left" height="31"><span style="font-size: medium;"> В связи с неисполнением обязательств по оплате, задолженность была уступлена по договору цессии 000000-003 от 20.05.21 г. в ООО &laquo;Премьер номер 2&raquo; (№ 145)</span></td>
</tr>
<tr>
<td style="width: 549px;" colspan="11" align="left" height="61"><span style="font-size: medium;"> Заявление о вынесении судебного приказа подается в суд по общим правилам подсудности, установленным в ГПК РФ, а именно по месту жительства Должника ( ст. 28 ГПК РФ). но в соответствии со статьей 32 ГПК РФ ( Договорная подсудность), стороны могут по соглашению между собой изменить территориальную подсудность для данного дела до принятия его судом к своему производству (п. 20 договора займа).</span></td>
</tr>
<tr>
<td style="width: 549px;" colspan="11" align="left" height="16"><span style="font-size: medium;"> 
    Кроме того, ООО &laquo;Премьер&raquo; оплатило за обращение в суд 
    государственную пошлину в размере 312,2 рублей. </span></td>
</tr>
<tr>
<td style="width: 549px;" colspan="11" align="left" height="16"><span style="font-size: medium;"> 
    На основании вышеизложенного и руководствуясь ст. ст. 809, 810, ГК РФ, ст. ст. 32, 121, 122, 123, 124 ГПК РФ, 
    </span></td>
</tr>

<tr>
<td style="width: 549px;" colspan="11" align="center" height="16"><strong><span style="font-size: medium;">
    ПРОШУ</span></strong></td>
</tr>
<tr>
<td style="width: 549px;" colspan="11" align="left" height="46"><span style="font-size: medium;"> 
    Взыскать с {$fio} в пользу ООО &laquo;Премьер&raquo; сумму задолженности 
    по договору денежного займа № {$first_number} от {$contract_date|date} г. за период с {$contract_date|date} г по {$document_date} 
    в размере {$total_summ} ({$total_summ|price_string}) рублей, 
    а также расходы по оплате госпошлины в размере {$poshlina} руб. </span></td>
</tr>
<tr>
<td style="width: 549px;" colspan="11" align="left" height="31">
    <span style="font-size: medium;"> 
        Вступивший в законную силу судебный приказ прошу выслать по адресу 
        344090, Ростовская обл., г.Ростов-На-Дону, Улицапр-кт, д. 1/9, офис 100.
    </span>
</td>
</tr>

<tr>
<td style="width: 549px;" colspan="11" align="center" height="16">
    <strong><span style="font-size: medium;">Приложение:</span></strong></td>
</tr>
<tr>
<td style="width: 17.4px;" align="left" height="106">&nbsp;</td>
<td style="width: 527.6px;" colspan="10" align="left">
    <span style="font-size: medium;">
        1. Платежное поручение об оплате госпошлины за рассмотрение заявления;<br />
        2. Бухгалтерская справка;<br />
        3. Копия договора займа;<br />
        4. Копия договора уступки прав;<br />
        5. Учредительные документы ООО &laquo;Премьер&raquo;;<br />
        6. Доверенность представителя.
    </span></td>
</tr>

<tr>
<td style="width: 17.4px;" align="left" height="31">&nbsp;</td>
<td style="width: 230.417px;" colspan="4" align="left"><strong><span style="font-size: medium;">Представитель по доверенности <br />{$exactor_phone}</span></strong></td>
<td style="width: 293.183px;" colspan="6" align="right"><strong><span style="font-size: medium;">{$exactor_name}</span></strong></td>
</tr>
</tbody>
</table>