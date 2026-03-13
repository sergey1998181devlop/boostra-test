<table width="530" cellspacing="0" cellpadding="3" border="1">
    
        <tr>
            <td colspan="5">
                <strong>1.ПЕРСОНАЛЬНЫЕ ДАННЫЕ</strong>
            </td>
        </tr>
    
    
        <tr>
            <td width="10%">
                1.1.
            </td>
            <td width="22.5%">Фамилия</td>
            <td valign="middle" width="22.5%">{$lastname|escape}</td>
            <td  width="22.5%">Менялась ли фамилия?</td>
            <td  width="22.5%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                1.2.
            </td>
            <td >Имя</td>
            <td >{$firstname|escape}</td>
            <td >Менялось ли имя?</td>
            <td>&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                1.3.
            </td>
            <td >Отчество</td>
            <td >{$patronymic|escape}</td>
            <td >Менялось ли отчество?</td>
            <td >&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                1.4.
            </td>
            <td colspan="2" width="45%">Пол</td>
            <td colspan="2" width="45%">{if $gender == 'male'}мужской{elseif $gender == 'female'}женский{/if}</td>
        </tr>
    
    
        <tr>
            <td >
                1.5.
            </td>
            <td colspan="2" width="45%">Гражданство</td>
            <td colspan="2" width="45%">Российская Федерация</td>
        </tr>
    
    
        <tr>
            <td >
                1.6.
            </td>
            <td colspan="2" width="45%">Место рождения</td>
            <td colspan="2" width="45%">{$birth_place|escape}</td>
        </tr>
    
    
        <tr>
            <td >
                1.7.
            </td>
            <td colspan="2" width="45%">Дата рождения</td>
            <td colspan="2" width="45%">{if $birth}{$birth|date}{/if}</td>
        </tr>
    
    
        <tr>
            <td >
                1.8.
            </td>
            <td colspan="2" width="45%">Девичья фамилия матери</td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                1.9.
            </td>
            <td colspan="2" width="45%">Семейный статус</td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                1.10.
            </td>
            <td colspan="2" width="45%">Количество иждевенцев</td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                1.11.
            </td>
            <td colspan="2" width="45%">Образование</td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                1.12.
            </td>
            <td colspan="2" width="45%">ИНН</td>
            <td colspan="2" width="45%">{$inn|escape}</td>
        </tr>
    
    
        <tr>
            <td >
                1.13.
            </td>
            <td colspan="2" width="45%">Email</td>
            <td colspan="2" width="45%">{$email|escape}</td>
        </tr>
    
    
        <tr>
            <td >
                1.14.
            </td>
            <td colspan="2" width="45%">Кодовое слово</td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4" >
                <strong>2. ДОКУМЕНТЫ</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4" >
                <strong>ПАСПОРТ ГРАЖДАНИНА РОССИЙСКОЙ ФЕДЕРАЦИИ</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >
                2.1.
            </td>
            <td colspan="2" width="45%">
                Серия
            </td>
            <td colspan="2" width="45%">
                {$passport_serial|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                2.2.
            </td>
            <td colspan="2" width="45%">
                Номер
            </td>
            <td colspan="2" width="45%">
                {$passport_number|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                2.3.
            </td>
            <td colspan="2" width="45%">
                Дата выдачи
            </td>
            <td colspan="2" width="45%">
                {if $passport_date}{$passport_date|date}{/if}
            </td>
        </tr>
    
    
        <tr>
            <td >
                2.4.
            </td>
            <td colspan="2" width="45%">
                Кем выдан
            </td>
            <td colspan="2" width="45%">
                {$passport_issued|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                2.5.
            </td>
            <td colspan="2" width="45%">
                Код подразделения
            </td>
            <td colspan="2" width="45%">
                {$passport_code|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4" >
                <strong>СТРАХОВОЕ СВИДЕТЕЛЬСТВО</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="2" width="45%">&nbsp;</td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                2.6.
            </td>
            <td colspan="2" width="45%">
                Страховой номер индивидуального лицевого счета застрахованного лица в системе обязательного
                    пенсионного страхования (СНИЛС)
            </td>
            <td colspan="2" width="45%">
                {$snils|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                2.7.
            </td>
            <td colspan="2" width="45%">
                Дата регистрации
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="2" width="45%">&nbsp;</td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="2" width="45%">&nbsp;</td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4">
                <strong>3. АДРЕСА И КОНТАКТНАЯ ИНФОРМАЦИЯ</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4">
                <strong>АДРЕС РЕГИСТРАЦИИ</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >
                3.1.
            </td>
            <td colspan="2" width="45%">
                Страна места регистрации
            </td>
            <td colspan="2" width="45%">
                Россия
            </td>
        </tr>
    
    
        <tr>
            <td >
                3.2.
            </td>
            <td colspan="2" width="45%">
                Индекс
            </td>
            <td colspan="2" width="45%">
                {if $regindex}{$regindex|escape}{/if}
            </td>
        </tr>
    
    
        <tr>
            <td >
                3.3.
            </td>
            <td colspan="2" width="45%">
                Регион/Район
            </td>
            <td colspan="2" width="45%">
                {$regregion|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                3.4.
            </td>
            <td colspan="2" width="45%">
                Город/Нас.пункт
            </td>
            <td colspan="2" width="45%">
                {$regcity|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                3.5.
            </td>
            <td colspan="2" width="45%">
                Улица
            </td>
            <td colspan="2" width="45%">
                {$regstreet|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                3.6.
            </td>
            <td colspan="2" width="45%">
                Дом
            </td>
            <td colspan="2" width="45%">
                {$reghousing|escape}
                {if $regbuilding} стр.{$regbuilding|escape}{/if}
            </td>
        </tr>
    
    
        <tr>
            <td >
                3.7.
            </td>
            <td colspan="2" width="45%">
                Квартира
            </td>
            <td colspan="2" width="45%">
                {$regroom|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                3.8.
            </td>
            <td colspan="2" width="45%">
                Телефон
            </td>
            <td colspan="2" width="45%">
                &nbsp;
            </td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4">
                <strong>АДРЕС ФАКТИЧЕСКОГО ПРОЖИВАНИЯ</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >
                3.9.
            </td>
            <td colspan="2" width="45%">
                Совпадает с регистрацией
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                3.10.
            </td>
            <td colspan="2" width="45%">
                Страна места нахождения
            </td>
            <td colspan="2" width="45%">
                Россия
            </td>
        </tr>
    
    
        <tr>
            <td >
                3.11.
            </td>
            <td colspan="2" width="45%">
                Индекс
            </td>
            <td colspan="2" width="45%">
                {$faktindex|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                3.12.
            </td>
            <td colspan="2" width="45%">
                Регион/Район
            </td>
            <td colspan="2" width="45%">
                {$faktregion|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                3.13.
            </td>
            <td colspan="2" width="45%">
                Город/Нас.пункт
            </td>
            <td colspan="2" width="45%">
                {$faktcity|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                3.14.
            </td>
            <td colspan="2" width="45%">
                Улица
            </td>
            <td colspan="2" width="45%">
                {$faktstreet|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                3.15.
            </td>
            <td colspan="2" width="45%">
                Дом
            </td>
            <td colspan="2" width="45%">
                {$fakthousing|escape}
                {if $faktbuilding}, стр.{$faktbuilding|escape}{/if}
            </td>
        </tr>
    
    
        <tr>
            <td >
                3.16.
            </td>
            <td colspan="2" width="45%">
                Квартира
            </td>
            <td colspan="2" width="45%">
                {$faktroom|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                3.17.
            </td>
            <td colspan="2" width="45%">
                Телефон
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                3.18.
            </td>
            <td colspan="2" width="45%">
                Мобильный телефон
            </td>
            <td colspan="2" width="45%">
                {$phone|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                3.19.
            </td>
            <td colspan="2" width="45%">
                Тип собственности
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                3.20.
            </td>
            <td colspan="2" width="45%">
                Проживание (лет)
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                3.21.
            </td>
            <td colspan="2" width="45%">
                Проживание (Месяцев)
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4" >
                <strong>4. КОНТАКНЫЕ ЛИЦА</strong>
                <strong>(не заявитель, можно родственники, но телефоны не должны совпадать с ранее
                        указанными)</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4" >
                <strong>КОНТАКТНОЕ ЛИЦО №1</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >
                4.1.
            </td>
            <td colspan="2" width="45%">
                Фамилия
            </td>
            <td colspan="2" width="45%">
                {$contactperson_lastname|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                4.2.
            </td>
            <td colspan="2" width="45%">
                Имя
            </td>
            <td colspan="2" width="45%">
                {$contactperson_firstname|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                4.3.
            </td>
            <td colspan="2" width="45%">
                Отчество
            </td>
            <td colspan="2" width="45%">
                {$contactperson_patronymic|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                4.4.
            </td>
            <td colspan="2" width="45%">
                Степень родства
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                4.5.
            </td>
            <td colspan="2" width="45%">
                Телефон домашний
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                4.6.
            </td>
            <td colspan="2" width="45%">
                Телефон мобильный
            </td>
            <td colspan="2" width="45%">
                {$contactperson_phone|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4" >
                <strong>КОНТАКТНОЕ ЛИЦО №2</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >
                4.7.
            </td>
            <td colspan="2" width="45%">
                Фамилия
            </td>
            <td colspan="2" width="45%">
                {$contactperson2_lastname|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                4.8.
            </td>
            <td colspan="2" width="45%">
                Имя
            </td>
            <td colspan="2" width="45%">
                {$contactperson2_firstname|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                4.9.
            </td>
            <td colspan="2" width="45%">
                Отчество
            </td>
            <td colspan="2" width="45%">
                {$contactperson2_patronymic|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                4.10.
            </td>
            <td colspan="2" width="45%">
                Степень родства
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                4.11.
            </td>
            <td colspan="2" width="45%">
                Телефон домашний
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                4.12.
            </td>
            <td colspan="2" width="45%">
                Телефон мобильный
            </td>
            <td colspan="2" width="45%">
                {$contactperson2_phone|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4" >
                <strong>КОНТАКТНОЕ ЛИЦО №3</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >
                4.13.
            </td>
            <td colspan="2" width="45%">
                Фамилия
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                4.15.
            </td>
            <td colspan="2" width="45%">
                Имя
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                4.16.
            </td>
            <td colspan="2" width="45%">
                Отчество
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                4.17.
            </td>
            <td colspan="2" width="45%">
                Степень родства
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                4.18.
            </td>
            <td colspan="2" width="45%">
                Телефон домашний
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                4.19.
            </td>
            <td colspan="2" width="45%">
                Телефон мобильный
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4" >
                <strong>КОНТАКТНОЕ ЛИЦО №4</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >
                4.20.
            </td>
            <td colspan="2" width="45%">
                Фамилия
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                4.21.
            </td>
            <td colspan="2" width="45%">
                Имя
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                4.22.
            </td>
            <td colspan="2" width="45%">
                Отчество
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                4.23.
            </td>
            <td colspan="2" width="45%">
                Степень родства
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                4.24.
            </td>
            <td colspan="2" width="45%">
                Телефон домашний
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                4.25.
            </td>
            <td colspan="2" width="45%">
                Телефон мобильный
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4" >
                <strong>5. ДОХОДЫ</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4" >
                <strong>ПОСТОЯННАЯ ТЕКУЩАЯ ЗАНЯТОСТЬ</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >
                5.1.
            </td>
            <td colspan="2" width="45%">
                Ежемесячный доход
            </td>
            <td colspan="2" width="45%">
                {$income|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                5.2.
            </td>
            <td colspan="2" width="45%">
                Название организации
            </td>
            <td colspan="2" width="45%">
                {$workplace|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                5.3.
            </td>
            <td colspan="2" width="45%">
                Адрес организации
            </td>
            <td colspan="2" width="45%">
                
            </td>
        </tr>
    
    
        <tr>
            <td >
                5.4.
            </td>
            <td colspan="2" width="45%">
                График занятости
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                5.5.
            </td>
            <td colspan="2" width="45%">
                Должность
            </td>
            <td colspan="2" width="45%">
                {$profession|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                5.6.
            </td>
            <td colspan="2" width="45%">
                Стаж работы (лет)
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                5.7.
            </td>
            <td colspan="2" width="45%">
                Стаж работы (месяцев)
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                5.8.
            </td>
            <td colspan="2" width="45%">
                Сфера деятельности
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                5.9.
            </td>
            <td colspan="2" width="45%">
                Штат
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                5.10.
            </td>
            <td colspan="2" width="45%">
                ФИО руководителя
            </td>
            <td colspan="2" width="45%">
                {$chief_name|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                5.11.
            </td>
            <td colspan="2" width="45%">
                Телефон руководителя
            </td>
            <td colspan="2" width="45%">
                {$chief_phone|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >
                5.12.
            </td>
            <td colspan="2" width="45%">
                Телефон организации
            </td>
            <td colspan="2" width="45%">
                {$workphone|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4" >
                <strong>ЛИЧНЫЕ РАСХОДЫ</strong>
                <strong>(ежемесячные обязательные выплаты по кредитам, алименты, коммунальные платежи</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >
                5.13.
            </td>
            <td colspan="2" width="45%">
                Наличие просроченных задолженностей
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                5.14.
            </td>
            <td colspan="2" width="45%">
                Уточнение
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                5.15.
            </td>
            <td colspan="2" width="45%">
                Сумма расходов
            </td>
            <td colspan="2" width="45%">
                {$expenses|escape}
            </td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4" >
                <strong>ДОПОЛНИТЕЛЬНЫЙ ДОХОД</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >
                5.16.
            </td>
            <td colspan="2" width="45%">
                Тип дополнительного дохода
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                5.17.
            </td>
            <td colspan="2" width="45%">
                Сумма дополнительного дохода
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4" >
                <strong>СОБСТВЕННОСТЬ (если есть)</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >
                5.18.
            </td>
            <td colspan="2" width="45%">
                Марка автомобиля
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                5.19.
            </td>
            <td colspan="2" width="45%">
                Номер автомобиля
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                5.20.
            </td>
            <td colspan="2" width="45%">
                Адрес
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                5.21.
            </td>
            <td colspan="2" width="45%">
                Цели получения займа
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4" >
                <strong>6. ФИНАНСОВЫЕ ПОКАЗАТЕЛИ</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >
                6.1.
            </td>
            <td colspan="2" width="45%">
                Наличие банкротства
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                6.2.
            </td>
            <td colspan="2" width="45%">
                Наличие исполнительный производств
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                6.3.
            </td>
            <td colspan="2" width="45%">
                Достаточность дохода
            </td>
            <td colspan="2" width="45%">
                ДА
            </td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4" >
                <strong>7. ИНЫЕ СВЕДЕНИЯ</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >
                7.1.
            </td>
            <td colspan="2" width="45%">
                Степень (уровень) риска и обоснование отнесения Клиента к определенной степени (уровню) риска
                Высокий уровень присваивается при наличии факторов, указанных в Приложении № 2 Правил внутреннего
                    контроля.
                В остальных случаях - обычный
            </td>
            <td colspan="2" width="45%">
                &nbsp;
                <table cellpadding="0" style="border-collapse: collapse; width: 90%; height: 54px;" border="0">
                    
                        <tr style="">
                            <td style="width: 60%; ">&nbsp;</td>
                            <td style="">&nbsp;</td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">Обычный</td>
                            <td style=""> 
                                <table border="1" width="10" align="center"><tr><td>X</td></tr></table>
                            </td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">&nbsp;</td>
                            <td style="">&nbsp;</td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">Высокий</td>
                            <td style="">
                                <table border="1" width="10" align="center"><tr><td>&nbsp;</td></tr></table>
                            </td>
                        </tr>
                    
                </table>
            </td>
        </tr>
    
    
        <tr>
            <td >
                7.2.
            </td>
            <td colspan="2" width="45%">
                Степень ( уровень) риска использования услуг Кредитора в целях ОД/ФТ
                Высокий уровень риска присваивается, в случае если хотя бы одному кредитному продукту присвоен
                    &laquo;высокий&raquo; уровень риска.
                Обычный уровень присваивается, в случае если всем кредитным продуктам присвоен &laquo;обычный&raquo;
                    уровень риска
            </td>
            <td colspan="2" width="45%">
                <table style="border-collapse: collapse; width: 90%; height: 54px;" border="0">
                    
                        <tr style="">
                            <td style="width: 60%; ">&nbsp;</td>
                            <td style="">&nbsp;</td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">Обычный</td>
                            <td style=""> 
                                <table border="1" width="10" align="center"><tr><td>X</td></tr></table>
                            </td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">&nbsp;</td>
                            <td style="">&nbsp;</td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">Высокий</td>
                            <td style="">
                                <table border="1" width="10" align="center"><tr><td>&nbsp;</td></tr></table>
                            </td>
                        </tr>
                    
                </table>
            </td>
        </tr>
    
    
        <tr>
            <td >
                7.4.*
            </td>
            <td colspan="2" width="45%">
                Цель установления деловых отношений
            </td>
            <td colspan="2" width="45%">
                Заключение договора потребительского займа.
            </td>
        </tr>
    
    
        <tr>
            <td >
                7.5.*
            </td>
            <td colspan="2" width="45%">
                Предполагаемый характер деловых отношений
            </td>
            <td colspan="2" width="45%">
                <table style="border-collapse: collapse; width: 90%; height: 54px;" border="0">
                    
                        <tr style="">
                            <td style="width: 60%; ">&nbsp;</td>
                            <td style="">&nbsp;</td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">Длительный</td>
                            <td style=""> 
                                <table border="1" width="10" align="center"><tr><td>&nbsp;</td></tr></table>
                            </td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">&nbsp;</td>
                            <td style="">&nbsp;</td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">Краткосрочный</td>
                            <td style="">
                                <table border="1" width="10" align="center"><tr><td>X</td></tr></table>
                            </td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">&nbsp;</td>
                            <td style="">&nbsp;</td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">Разовый</td>
                            <td style="">
                                <table border="1" width="10" align="center"><tr><td>&nbsp;</td></tr></table>
                            </td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">&nbsp;</td>
                            <td style="">&nbsp;</td>
                        </tr>

                </table>
            </td>
        </tr>
    
    
        <tr>
            <td  height="12">
                7.6.*
            </td>
            <td colspan="2" width="45%">
                Финансовое положение
                Финансовое положение Клиента оценивается по следующим критериям:
                - при наличии признаков банкротства &ndash; финансовое положение определяется как кризисное;
                - при наличии задолженности перед любыми кредиторами Клиента, но без признаков банкротства -
                    финансовое положение определяется как неустойчивое;
                - в остальных случаях финансовое положение определяется как устойчивое.
            </td>
            <td colspan="2" width="45%">
                <table style="border-collapse: collapse; width: 90%; height: 54px;" border="0">
                    
                        <tr style="">
                            <td style="width: 60%; ">&nbsp;</td>
                            <td style="">&nbsp;</td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">Устойчивый</td>
                            <td style="">
                                <table border="1" width="10" align="center"><tr><td>X</td></tr></table>
                            </td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">&nbsp;</td>
                            <td style="">&nbsp;</td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">Неустойчивый</td>
                            <td style=""> 
                                <table border="1" width="10" align="center"><tr><td>&nbsp;</td></tr></table>
                            </td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">&nbsp;</td>
                            <td style="">&nbsp;</td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">Кризисный</td>
                            <td style="">
                                <table border="1" width="10" align="center"><tr><td>&nbsp;</td></tr></table>
                            </td>
                        </tr>
                </table>
                &nbsp;
            </td>
        </tr>
    
    
        <tr>
            <td >
                7.7.*
            </td>
            <td colspan="2" width="45%">
                Деловая репутация клиента
                Неудовлетворительная оценка присваивается при наличии двух и более фактов привлечения Клиента к
                    ответственности в судебном или административном порядке за календарный год, предшествующий дате
                    обращения в МКК.
                В остальных случаях Клиенту присваивается &laquo;удовлетворительная&raquo; оценка деловой репутации
                
            </td>
            <td colspan="2" width="45%">
                <table style="border-collapse: collapse; width: 90%; height: 58px;" border="0">
                    
                        <tr style="">
                            <td style="width: 60%; ">&nbsp;</td>
                            <td style="">&nbsp;</td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">Удовлетворительная</td>
                            <td style="">
                                <table border="1" width="10" align="center"><tr><td>X</td></tr></table>
                            </td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">&nbsp;</td>
                            <td style="">&nbsp;</td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">Неудовлетворительная</td>
                            <td style=""> 
                                <table border="1" width="10" align="center"><tr><td>&nbsp;</td></tr></table>
                            </td>
                        </tr>
                        <tr style="">
                            <td style="width: 60%; ">&nbsp;</td>
                            <td style="">&nbsp;</td>
                        </tr>
                    
                </table>
            </td>
        </tr>
    
    
        <tr>
            <td >
                7.8.
            </td>
            <td colspan="2" width="45%">
                Сведения о результатах проверки наличия (отсутствия) в отношении клиента информации о его
                    причастности к экстремистской деятельности или терроризму
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                7.9.
            </td>
            <td colspan="2" width="45%">
                Сведения о результатах проверки наличия (отсутствия) в отношении клиента информации о замораживании
                    (блокировании) денежных средств или иного имущества клиента.
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                7.10.
            </td>
            <td colspan="2" width="45%">
                Дата начала отношений с клиентом
            </td>
            <td colspan="2" width="45%">{$contract_date|date}</td>
        </tr>
    
    
        <tr>
            <td >
                7.11.
            </td>
            <td colspan="2" width="45%">
                Дата окончания отношений с клиентом
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td colspan="5" >
                <strong>8. СВЕДЕНИЯ О ПРЕДСТАВИТЕЛЕ КЛИЕНТА</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >
                8.1.**
            </td>
            <td colspan="2" width="45%">
                Фамилия
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                8.2.**
            </td>
            <td colspan="2" width="45%">
                Имя
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                8.3.**
            </td>
            <td colspan="2" width="45%">
                Отчество
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                8.4.**
            </td>
            <td colspan="2" width="45%">
                Гражданство
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                8.5.**
            </td>
            <td colspan="2" width="45%">
                Документ, удостоверяющий личность
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                8.6.**
            </td>
            <td colspan="2" width="45%">
                Адрес места жительства (пребывания)
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                8.7.**
            </td>
            <td colspan="2" width="45%">
                ИНН (при наличии)
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                8.8.**
            </td>
            <td colspan="2" width="45%">
                Страховой номер индивидуального лицевого счета застрахованного лица в системе обязательного
                    пенсионного страхования (СНИЛС) (при наличии)
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                8.9.**
            </td>
            <td colspan="2" width="45%">
                Номер телефон
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                9.7.***
            </td>
            <td colspan="2" width="45%">
                ИНН (при наличии)
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                9.8.***
            </td>
            <td colspan="2" width="45%">
                Страховой номер индивидуального лицевого счета застрахованного лица в системе обязательного
                    пенсионного страхования (СНИЛС) (при наличии)
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                9.9.***
            </td>
            <td colspan="2" width="45%">
                Номер телефон
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                9.10
            </td>
            <td colspan="2" width="45%">
                Меры, принятые по идентификации Бенефициарного владельца
            </td>
            <td colspan="2" width="45%">
                Устный опрос/анкетирование
            </td>
        </tr>
    
    
        <tr>
            <td >
                9.11.
            </td>
            <td colspan="2" width="45%">
                Обоснование принятого решения о признании физического лица Бенефициарным владельцем
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >&nbsp;</td>
            <td colspan="4" >
                <strong>10. СВЕДЕНИЯ ВЫГОДОПРИОБРЕТАТЕЛЕ</strong>
            </td>
        </tr>
    
    
        <tr>
            <td >
                10.1.****
            </td>
            <td colspan="2" width="45%">
                Фамилия
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                10.2.****.
            </td>
            <td colspan="2" width="45%">
                Имя
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                10.3.****
            </td>
            <td colspan="2" width="45%">
                Отчество
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                10.4.****
            </td>
            <td colspan="2" width="45%">
                Гражданство
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                10.5.****
            </td>
            <td colspan="2" width="45%">
                Документ, удостоверяющий личность
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                10.6.****
            </td>
            <td colspan="2" width="45%">
                Адрес места жительства (пребывания)
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                10.7.****
            </td>
            <td colspan="2" width="45%">
                ИНН (при наличии)
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                10.8.****
            </td>
            <td colspan="2" width="45%">
                Страховой номер индивидуального лицевого счета застрахованного лица в системе обязательного
                    пенсионного страхования (СНИЛС) (при наличии)
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td >
                10.9.****
            </td>
            <td colspan="2" width="45%">
                Номер телефон
            </td>
            <td colspan="2" width="45%">&nbsp;</td>
        </tr>
    
    
        <tr>
            <td  height="62">
                11
            </td>
            <td colspan="4" width="90%">
                * Сведения, предусмотренные пунктами 7.4.-7.7. настоящего подпункта, заполняются в отношении Клиентов
                    с высокой степенью (уровнем) риска клиента.
                **Сведения, указанные в п.п.8.1.-8.9. указываются, если обратился представитель клиента
                *** Сведения, указанные в п.п. 9.1-9.9 указываются при выявлении Бенефициарного владельца
                **** Сведения, указанные в п.п.10.1-10.9. указываются при выявлении Выгодоприобретателя
            </td>
        </tr>
    
    
        <tr>
            <td colspan="5" height="1">
                <table cellspacing="0" cellpadding="7">
                    
                        <tr>
                            <td>
                                <p>
                                Относитесь ли Вы к должностным лицам публичных международных организаций, к лицам
                                    замещающим (занимающим) государственные должности Российской Федерации, должности
                                    членов Совета директоров Центрального банка Российской Федерации, должности
                                    федеральной государственной службы, назначение на которые и освобождение от которых
                                    осуществляются Президентом Российской Федерации или Правительством Российской
                                    Федерации, должности в Центральном банке Российской Федерации, государственных
                                    корпорациях и иных организациях, созданных российской Федерацией на основании
                                    федеральных законов, включенные в перечни должностей, определяемые Президентом
                                    Российской Федерации?
                                </p>
                                <table style="border-collapse: collapse; width: 48.6972%; height: 8px;">
                                    
                                        <tr>
                                            <td style="width: 19.8387%;">ДА</td>
                                            <td style="width: 11.4516%; border-style: solid;">&nbsp;</td>
                                            <td style="width: 43.7097%; text-align: center;">НЕТ</td>
                                            <td style="width: 15.1163%; border-style: solid;">&nbsp;</td>
                                        </tr>
                                    
                                </table>
                                
                                <p>
                                Является ли Вы супругом/супругой, либо состоите ли Вы в родстве с лицом, являющимся
                                    иностранным публичным должностным лицом или занимающим вышеуказанные должности?
                                </p>
                                <table style="border-collapse: collapse; width: 48.6972%; height: 8px;">
                                    
                                        <tr>
                                            <td style="width: 19.8387%;">ДА</td>
                                            <td style="width: 11.4516%; border-style: solid;">&nbsp;</td>
                                            <td style="width: 43.7097%; text-align: center;">НЕТ</td>
                                            <td style="width: 15.1163%; border-style: solid;">&nbsp;</td>
                                        </tr>
                                    
                                </table>
                                <p>
                                Прошу выдать мне заем на услугу СМС-информирование в размере ХХХ руб.
                                Настоящим я подтверждаю, что мне известно, что Услуга является добровольной и отказ
                                    от Услуги не влияет на решение о заключении Договора.
                                </p>
                                <table style="border-collapse: collapse; width: 82.4588%; height: 53px;">
                                    
                                        <tr>
                                            <td style="width: 50%;">Согласен на оказание услуг</td>
                                            <td style="width: 6.95793%; border-style: solid;">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td style="width: 50%;">&nbsp;</td>
                                            <td style="width: 6.95793%;">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td style="width: 50%;">Не согласен на оказание услуг</td>
                                            <td style="width: 6.95793%; border-style: solid;">&nbsp;</td>
                                        </tr>
                                    
                                </table>
                                &nbsp;
                                <p>Подписав, настоящее заявление я:</p>
                                <p>1. Подтверждаю, что сведения, содержащиеся в настоящей анкете-заявлении, являются
                                    верными и точными.
                                </p>
                                <p>
                                    2.Даю согласие ООО "МКК "На личное +", расположенное по адресу: г. Самара, ул. Мориса
                                    Победы 86, оф.2.1 (Далее по тексту - Общество) на обработку моих персональных данных
                                    Ф.И.О; пол; дата рождения; место рождения; реквизиты документа, удостоверяющего
                                    личность (паспорт/иной документ), адрес постоянное регистрации и фактического
                                    проживания, идентификационной номер налогоплательщика (ИНН); СНИЛС; информация о
                                    семейном, социальном, имущественном отношении; контактная информация; данные о
                                    профессии, образовании и доходах, фотография, предоставленные Обществу, включая
                                    сбор, запись, систематизацию, накопление, хранение, уточнение (обновление,
                                    изменение), извлечение, использование, блокирование, удаление, уничтожение передачу
                                    (предоставление, доступ) с целью продвижения своих услуг (реклама), информирования
                                    меня о необходимости предоставить документы, о действующих договорах займа,
                                    заключения и исполнения договора потребительского займа. Обработка персональных
                                    данных производиться Обществом с использованием средств автоматизации
                                    (автоматизированная обработка) и без использования таковых (неавтоматизированная
                                    обработка).
                                    <br />
                                Настоящее согласие предоставлено на 5 лет и может быть отозвано мной путем
                                    направления мною соответствующего уведомления. Я уведомлен о том, что Обществ вправе
                                    продолжить обработку моих персональных данных без моего согласия в случаях,
                                    предусмотренных действующим законодательством.
                                </p>
                                <p>
                                    3. Даю согласие на запрос и получение Обществом из любого бюро кредитных историй
                                    кредитного отчета, содержащего в том числе основную часть кредитной истории,
                                    определенную в ст. 4 Федерального закона от 30.12.2004 г. № 218-ФЗ "О кредитных
                                    историях", в целях заключения и исполнения Договора потребительского займа, в том
                                    числе оценки кредитоспособности, расчета показателя долговой нагрузки.
                                </p>
                                <p>
                                    4. Понимаю, что Общество имеет права не информировать меня о причинах отказа в
                                    предоставлении займа.
                                </p>
                            </td>
                        </tr>
                    
                </table>
            </td>
        </tr>
        
    
    
        <tr>
            <td colspan="5" >
                <p>Подписано с использованием ПЭП</p>
                <p><strong>{$lastname|escape} {$firstname|escape} {$patronymic|escape}</strong></p>
                <p>Дата: <strong>{$created|date}</strong></p>
                <p>Телефон: <strong>{$phone|escape}</strong></p>
                <p>СМС-код: <strong>{$asp|escape}</strong></p>
            </td>
        </tr>
    
</table>