<table width="530">
    <tbody>
        <tr>
            <td width="70%"></td>
            <td width="30%"><strong>в ООО «СК Консалтинг»</strong><br /><br /></td>
        </tr>
        <tr>
            <td width="100%" align="center">
                <strong>
                ЗАЯВЛЕНИЕ № ??? от ???г.
                <br />
                на приобретение Сертификата на получение услуг
                </strong>
                <br />
            </td>
        </tr>
        <tr>
            <td width="100%">
                <table cellpadding="2" border="1" style="font-size:8px;">
                    <tr>
                        <td width="30%" align="right"><strong>ФАМИЛИЯ:</strong></td>
                        <td width="70%">{$order->lastname|escape}</td>
                    </tr>
                    <tr>
                        <td width="30%" align="right"><strong>ИМЯ:</strong></td>
                        <td width="70%">{$order->firstname|escape}</td>
                    </tr>
                    <tr>
                        <td width="30%" align="right"><strong>ОТЧЕСТВО:</strong></td>
                        <td width="70%">{$order->patronymic|escape}</td>
                    </tr>
                    <tr>
                        <td width="30%" align="right"><strong>Документ, <br />удостоверяющий личность</strong></td>
                        <td width="70%">Паспорт {$order->passport_serial|escape}, выдан {$order->passport_date|escape} {$order->passport_issued|escape}, {$order->subdivision_code|escape}</td>
                    </tr>
                    <tr>
                        <td width="30%" align="right"><strong>Адрес регистрации:</strong></td>
                        <td width="70%">{$order->regindex}
                {$order->regregion} {$order->regregion_shorttype},
                {$order->regcity} {$order->regcity_shorttype},
                {$order->regstreet} {$order->regstreet_shorttype},
                д.{$order->reghousing} {if $order->regroom}, кв.{$order->regroom}{/if}
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" align="right"><strong>Дата рождения:</strong></td>
                        <td width="70%">{$order->birth|escape}</td>
                    </tr>
                    <tr>
                        <td width="30%" align="right"><strong>Телефон:</strong></td>
                        <td width="20%">{$order->phone_mobile|escape}</td>
                        <td width="30%" align="right"><strong>E-mail:</strong></td>
                        <td width="20%">{$order->email|escape}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <br />
        <tr>
            <td width="100%">
            Настоящим даю свое согласие
                ???, адрес места нахождения: ??? (далее – «Агент»)
                , ООО «СК Консалтинг», адрес местонахождения: 109451, г. Москва, Мячковский б-р, д. 5, к. 1, 1/l ком. 5 (далее – «Компания») (ИНН 9723122494, ОГРН 1217700392942), 
                САО «ВСК», адрес местонахождения: 121552, г. Москва, ул. Островная, д.4  (ИНН  7710026574, ОГРН 1027700186062), 
                привлекаемым Компанией субисполнителям (Партнерам), если это необходимо для исполнения Договора, 
                на обработку, в том числе автоматизированную (сбор, уточнение, хранение, уничтожение) 
                моих персональных данных, указанных в настоящем Заявлении, в том числе фамилии, имени, отчества, иных сведений, 
                содержащихся в документе, удостоверяющем мою личность, биометрических персональных данных, 
                а также сведения о поле, дате рождения, иные сведения, сообщенные мной, в соответствии с 
                требованиями Федерального закона от 27.06.2006 года №152-ФЗ «О персональных данных». 
                Указанные мной персональные данные предоставлены в целях получения услуг Компании, а также для целей взаиморасчетов 
                между Агентом и Компанией, обмена отчетной документацией. 
                Мое согласие действует бессрочно может быть отозвано в любой момент путем предоставления в 
                Агенту/Компании заявления в простой письменной форме.
                <br /><br />
                <strong>Настоящим подтверждаю:</strong>            
                <br /><br />
                <table width="100%">
                    <tr>
                        <td width="5%"><img src="{$order->config->root_url}/design/boostra_mini_norm/html/pdf/i/checkbox_on.png" width="10px" /></td>
                        <td width="95%">Мне предоставлена исчерпывающая информация о предоставляемых ООО «СК Консалтинг» услугах и условиях их получения;</td>
                    </tr>
                    <tr>
                        <td width="5%"><img src="{$order->config->root_url}/design/boostra_mini_norm/html/pdf/i/checkbox_on.png" width="10px" /></td>
                        <td width="95%">Безоговорочно присоединяюсь с момента заключения договора оказания услуг с ООО «СК Консалтинг» к действующей редакции Правил комплексного абонентского обслуживания с присоединением к программе добровольного страхования от несчастных случаев и болезней САО «ВСК», размещенных на официальном сайте в сети Интернет https://www.vsk.ru/; </td>
                    </tr>
                    <tr>
                        <td width="5%"><img src="{$order->config->root_url}/design/boostra_mini_norm/html/pdf/i/checkbox_on.png" width="10px" /></td>
                        <td width="95%">Все положения Правил комплексного абонентского обслуживания с присоединением к программе добровольного страхования от несчастных случаев и болезней САО «ВСК мне известны и разъяснены в полном объеме;</td>
                    </tr>
                    <tr>
                        <td width="5%"><img src="{$order->config->root_url}/design/boostra_mini_norm/html/pdf/i/checkbox_on.png" width="10px" /></td>
                        <td width="95%">Мне предоставлена информация о том, что договор оказания услуг считается заключенным в момент оплаты стоимости Сертификата;</td>
                    </tr>
                    <tr>
                        <td width="5%"><img src="{$order->config->root_url}/design/boostra_mini_norm/html/pdf/i/checkbox_on.png" width="10px" /></td>
                        <td width="95%">С Тарифами ООО «СК Консалтинг» ознакомлен и согласен.</td>
                    </tr>
                    <tr>
                        <td width="5%"><img src="{$order->config->root_url}/design/boostra_mini_norm/html/pdf/i/checkbox_on.png" width="10px" /></td>
                        <td width="95%">С правилами расторжения договора оказания услуг ознакомлен и согласен.</td>
                    </tr>
                </table>
            </td>
        </tr>
        
        
    </tbody>
</table>

<table width="530">
    <tbody>
        
        
        
        
        <tr>
            <td>
                <table width="397" cellspacing="0" cellpadding="7">
                    <tbody>
                        <tr>
                            <td width="383" height="122">
                                <table width="397" cellspacing="0" cellpadding="7" border=1"">
                                    <tbody>
                                        <tr>
                                            <td width="100%" height="98">
                                                <p>Подписано с использованием ПЭП</p>
                                                <p><strong>{$order->lastname|escape} {$order->firstname|escape} {$order->patronymic|escape}</strong></p>
                                                <p>Дата: <strong>{$order->insurance->create_date|date}</strong></p>
                                                <p>Телефон: <strong>{$order->phone_mobile|escape}</strong></p>
                                                <p>СМС-код: <strong>
                                                    ???
                                                    </strong>
                                                </p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>