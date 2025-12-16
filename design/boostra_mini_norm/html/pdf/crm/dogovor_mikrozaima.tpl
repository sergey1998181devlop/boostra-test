<table border="0" cellpadding="0">
    <tr style="font-weight: bold">
        <td>{$order->regcity_shorttype}. {$order->regcity|escape}</td>
        <td style="text-align: right">"{$smarty.now|date_format:"%d"}" {$smarty.now|date_format:"%m"} {$smarty.now|date_format:"%Y"}г &emsp;</td>
    </tr>
</table>

<table border="0" cellpadding="0" style="font-size: 8px; font-weight: bold; border-collapse: separate; border-spacing: 5px 0;">
    <tr>
        <td>
            <br><br><br>
            <img src="{$order->config->root_url}/design/boostra_mini_norm/html/pdf/i/contract_qr_cd_full.png" width="120"
                 height="120" alt="qr_code">
        </td>
        <td style="text-align: center; border-width: 2px 2px 2px 2px;">
            <br><br><br><br><br><br><br>
            ПОЛНАЯ СТОИМОСТЬ ЗАЙМА СОСТАВЛЯЕТ: {$contract->base_percent*365}% ({($contract->base_percent*365)|percent_string|upper}) ПРОЦЕНТОВ ГОДОВЫХ
        </td>
        <td style="text-align: center; vertical-align: center; border-width: 2px 2px 2px 2px">
            <br><br><br><br><br>
            ПОЛНАЯ СТОИМОСТЬ
            ПОТРЕБИТЕЛЬСКОГО <br>
            ЗАЙМА В ДЕНЕЖНОМ ВЫРАЖЕНИИ <br>
            СОСТАВЛЯЕТ <br>
            {$contract->amount} руб. <br> ({$contract->amount|price_string|upper})
            <br><br><br><br>
        </td>
    </tr>
</table>

<br>

<p style="font-size: 7px; text-align: center">
    Я, {$order->lastname|upper} {$order->firstname|upper} {$order->patronymic|upper} {$order->birth|date} года рождения, паспорт гражданина Российской Федерации; серия {$order->passport_serial} номер {$order->passport_number}, выдан
    {$order->passport_issued} {$order->passport_date}г., зарегистрирован по адресу:
    {if $order->regindex}{$order->regindex}, {/if}
    {if $order->regregion}{$order->regregion} {$order->regregion_shorttype}, {/if}
    {if $order->regcity}{$order->regcity} {$order->regcity_shorttype}, {/if}
    {if $order->regstreet}{$order->regstreet} {$order->regstreet_shorttype}, {/if}
    {if $order->reghousing}д. {$order->reghousing}, {/if}
    {if $order->regbuilding}стр. {$order->regbuilding}, {/if}
    {if $order->regroom}кв. {$order->regroom}, {/if}
    («Заемщик») выражаю МКК ООО «БУСТРА» (зарегистрировано в реестре
    микрофинансовых
    организаций за номером 1703336008323 от 6 июня 2017 года, ИНН/ОГРН 6317102210/1146317004030, юридический адрес:
    443099,
    Самарская область, г.Самара, ул. Фрунзе, дом 48, оф.10), («Кредитор») свое согласие на заключение со мной договора займа на указанных ниже Индивидуальных условиях
    договора микрозайма<br>
    <strong>
        Максимальный размер процентов, неустойки (штрафы, пени), иных мер ответственности по договору, а также
        платежей за услуги, оказываемые кредитором заемщику за отдельную плату по договору потребительского
        кредита (займа) не может превышать полуторакратного размера суммы займа. С даты возникновения
        просрочки исполнения обязательств Заемщика по возврату суммы займа. Кредитор вправе начислять
        Заемщику неустойку (штрафы, пени) и применять иные меры ответственности только на не погашенную
        Заемщиком часть суммы основного долга.<br>
        Индивидуальные условия договора микрозайма {$order->number} от {$order->create_date|date} г.
    </strong>
</p>

<table border="0.5" cellpadding="3" style="font-size: 6px;">
    <tbody>
        <tr align="center">
            <td width="30" style="font-weight: bold; text-align: center">№</td>
            <td width="200" style="font-weight: bold;">Условие</td>
            <td width="300" style="font-weight: bold;">Содержание условия</td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">1</td>
            <td width="200">Сумма займа</td>
            <td width="300">{$contract->amount} руб. ({$contract->amount|price_string})</td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">2</td>
            <td width="200">Срок действия договора, срок возврата займа</td>
            <td width="300">Настоящий договор микрозайма вступает в силу с момента передачи
                денежных средств Заемщику или поступления денежных средств на
                счет Заемщика, открытый в кредитной организации, зарегистрированной
                на территории Российской Федерации, и действует до полного
                исполнения сторонами обязательств по нему (фактического возврата
                займа в полном объеме). Микрозайм подлежит возврату ??? г.
            </td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">3</td>
            <td width="200">Валюта, в которой предоставляется кредит заем</td>
            <td width="300">Российские рубль</td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">4</td>
            <td width="200">Процентная ставка</td>
            <td width="300">365,000 (Триста шестьдесят пять) процентов годовых (1 % в день)</td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">5</td>
            <td width="200">Порядок определения курса иностранной валюты при переводе денежных средств кредитором третьему лицу,
                    указанному заемщиком
            </td>
            <td width="300">Не применимо</td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">5.1</td>
            <td width="200">Указание на изменение суммы расходов заемщика при
                увеличении используемой в договоре потребительского
                кредита (займа) переменной процентной ставки
                потребительского кредита (займа) на один процентный пункт,
                начиная со второго очередного платежа, на ближайшую дату
                после предполагаемой даты заключения договора
                потребительского кредита (займа)
            </td>
            <td width="300">Отсутствует</td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">6</td>
            <td width="200">Количество, размер и периодичность (сроки) платежей Заемщика по договору или порядок определения этих
                    платежей
            </td>
            <td width="300">Сумма займа и процентов подлежат оплате единовременным платежом в срок, указанный в п. 2
                    настоящих условий.<br />Размер платежа к моменту возврата займа ???.00 руб.
                    (??? ??? 00 ???)
            </td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">7</td>
            <td width="200">Порядок изменения количества, размера и периодичности (сроков) платежей заемщика при частичном
                    досрочном возврате займа
            </td>
            <td width="300">Проценты начисляются на оставшуюся непогашенную часть суммы займа со дня, следующего за днем
                    частичного погашения. Оставшаяся задолженность в полном объеме должна быть погашена в дату,
                    указанную в п. 2 настоящей таблицы
            </td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">8</td>
            <td width="200">Способы исполнения заемщиком обязательств по договору</td>
            <td width="300">1. Наличными в кассу в любом офисе Кредитора
               2. Наличными через терминалы оплаты
               3. Безналичным платежом на расчетный счет Кредитора
                    40701810200000003493, в АО «Тинькофф Банк» корсчет
                    30101810145250000974, БИК 044525974
               4. Через платежный сервис в Личном кабинете Земщика на сайте www.boostra.ru
               5. Безналичным платежом через любой терминал АО "Почта Банк"
            </td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">8.1</td>
            <td width="200">Бесплатный способ исполнения заемщиком обязательств по договору</td>
            <td width="300">Заемщик бесплатно может исполнить свои обязательства по договору через любой терминал АО "Почта Банк".</td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">9</td>
            <td width="200">Обязанность заемщика заключить иные договоры</td>
            <td width="300">Не применимо</td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">10</td>
            <td width="200">Обязанность заемщика по предоставлению обеспечения исполнения обязательств по договору и требования к
                    такому обеспечению
            </td>
            <td width="300">Отсутствует</td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">11</td>
            <td width="200">Цели использования заемщиком потребительского займа</td>
            <td width="300">Не применимо</td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center"></td>
            <td width="200">Подписи сторон</td>
            <td width="300">
                Кредитор:_______Директор МКК ООО "Бустра" Смелов С.Б. <br>
                Заемщик:_______{$order->lastname|escape} {$order->firstname|escape} {$order->patronymic|escape}
                <br>
                <small>&emsp;&emsp;&emsp;&emsp;&emsp;(Подписано АСП {$order->asp|escape})</small>
            </td>
        </tr>
    </tbody>
</table>

<br><br><br><br><br><br><br>

<table border="0.5" width="530" cellspacing="0" cellpadding="2" style="font-size: 6px">
    <tbody>
        <tr align="center">
            <td width="30" style="font-weight: bold; text-align: center">№</td>
            <td width="200" style="font-weight: bold;">Условие</td>
            <td width="300" style="font-weight: bold;">Содержание условия</td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">12</td>
            <td width="200">Ответственность Заемщика за ненадлежащее исполнение
                    условий договора, размер неустойки (штрафа, пени) или
                    порядок их определения
            </td>
            <td width="300">ОтветственностьЗаемщика</td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center" rowspan="3">13</td>
            <td width="200" rowspan="3">Условие об уступке кредитором третьим лицам прав
                    (требований) по договору
            </td>
            <td width="300" style="text-align: left">Заемщик уведомлен о возможности запрета на переуступку Кредитором
                    прав требований на взыскание задолженности по договору займа
                    юридическим лицам, осуществляющим профессиональную
                    деятельность по предоставлению потребительских займов,
                    юридическим лицам, осуществляющим деятельность по возврату
                    просроченной задолженности физических лиц в качестве основного вида
                    деятельности.
            </td>
        </tr>
        <tr>
            <td width="280" style="text-align: center;">Заемщик согласен на переуступку прав на взыскание
                    задолженности по договору займа.
            </td>
            <td width="15" style="text-align: center"><p>V</p></td>
            <td width="5"></td>
        </tr>
        <tr>
            <td width="280" style="text-align: center;">Заемщик не согласен на переуступку прав на взыскание
                    задолженности по договору займа
            </td>
            <td width="15"></td>
            <td width="5"></td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">14</td>
            <td width="200">Согласие Заемщика с Общими условиями договора
                    микрозайма
            </td>
            <td width="300">Заемщик выражает свое согласие, с тем, что отношения сторон по
                    договору займа будут регулироваться, в том числе, положениями Общих
                    условий договора микрозайма, которые доступны  во всех офисах
                    Кредитора
            </td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">15</td>
            <td width="200">Услуги, оказываемые Кредитором Заемщику за отдельную
                    плату и необходимые для заключения договора, их цена или
                    порядок ее определения, а также согласие Заемщика на
                    оказание таких услуг
            </td>
            <td width="300">Отсутствует</td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">16</td>
            <td width="200">Способ обмена информацией между Кредитором и
                    Заемщиком
            </td>
            <td width="300">1. Личный кабинет Заемщика на сайте Кредитора www.boostra.ru
                    2. Почтовые и смс сообщения
                    3. Личный визит в офис Кредитора
                    4. По телефону «горячей линии» (Контакт-центр) Кредитора  –
                    88003333073 (звонок по России бесплатный)
                    5.Электронными сообщениями (по электронной почте) по следующему
                    адресу Кредитора: info@boostra.ru
            </td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">17</td>
            <td width="200">Способы получения Заемщиком уведомления об уступке
                    кредитором третьим лицам прав (требований) по договору
            </td>
            <td width="300">1. Заказной и простой письменной корреспонденцией посредством ее
                    направления через Почту России.
                    2. В личном кабинете Заемщика на официальном сайте МКК ООО
                    «Бустра» (https://www.boostra.ru//)
            </td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">18</td>
            <td width="200">Территориальная подсудность по искам Кредитора к
                    Заемщику
            </td>
            <td width="300">Суд в соответствии с установленной законодательством Российской
                    Федерации подведомственностью по месту регистрации Заемщика
            </td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">19</td>
            <td width="200">Заемщик подтверждает и соглашается, что:</td>
            <td width="300">1. Кредитор вправе направлять ему сообщения рекламного характера о
                    предоставляемых услугах
                    2. Заемщик проинформирован о том, что информация о нем, полученная
                    Кредитором, предоставляется Кредитором в бюро кредитных историй,
                    включенное в государственный реестр бюро кредитных историй, в
                    соответствии с законодательством РФ
                    3. Кредитор не несет ответственности за сбои в работе мобильных
                    операторов, кредитных организаций и платежных систем при обмене
                    информацией или используемых для погашения Займа
                    4. Предоставленный номер мобильного телефона зарегистрирован на
                    его имя и является его личным, а также обязуется обеспечить
                    невозможность доступа к его личному мобильному телефону третьих
                    лиц с целью несанкционированного доступа.
            </td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">20</td>
            <td width="200">Согласие субъекта кредитной истории (Заемщика) на
                    раскрытие информации, содержащейся в основной части
                    кредитной истории
            </td>
            <td width="300">Согласен</td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">21</td>
            <td width="200">Настоящие Индивидуальные условия подписаны:</td>
            <td width="300">ОтветственностьЗаемщика</td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">22</td>
            <td width="200">Лицо, подписавшее настоящее условие от имени Кредитора</td>
            <td width="300">Директор Вороной Игорь Юрьевич на основании Устава от 07.03.2017 г.</td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">23</td>
            <td width="200">Дата подписания настоящих условий и номер договора
                    микрозайма
            </td>
            <td width="300">"{$smarty.now|date_format:"%d"}" {$smarty.now|date_format:"%m"} {$smarty.now|date_format:"%Y"}г. № {$contract->number}</td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">24</td>
            <td width="200">Заемщик подтверждает и соглашается с тем, что</td>
            <td width="300">экземпляр Индивидуальных условий договора займа подготовленный и
                    напечатанный с использованием сервисов Личного кабинета Заемщика
                    имеет такую же юридическую силу, как если бы он был оформлен в
                    момент подписания Индивидуальных условий договора займа
                    Заемщиком лично в офисе Кредитора.
            </td>
        </tr>
        <tr>
            <td width="30" style="font-weight: bold; text-align: center">25</td>
            <td width="200">Подписи сторон</td>
            <td width="300">
                Кредитор:_______Директор МКК ООО "Бустра" Смелов С.Б. <br>
                Заемщик:_______{$order->lastname|escape} {$order->firstname|escape} {$order->patronymic|escape}
                <br>
                <small>&emsp;&emsp;&emsp;&emsp;&emsp;(Подписано АСП {$order->asp|escape})</small>
            </td>
        </tr>
    </tbody>
</table>

<br><br>

<table border="0">
    <tbody>
    <tr>
        <td></td>
        <td>
            <img src="{$order->config->root_url}/design/boostra_mini_norm/html/pdf/i/stamp_boostra.png" width="100">
        </td>
        <td></td>
    </tr>
    </tbody>
</table>


