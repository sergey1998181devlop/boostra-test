<table width="535" cellspacing="0" cellpadding="2" border="0">
    <tbody>
        <tr>
            <td>
                <p align="center">СОГЛАСИЕ</p>
                <p>
                    Я, {$lastname|escape} {$firstname|escape} {$patronymic|escape}, 
                    паспорт серия {$passport_serial|escape}, номер {$passport_number|escape}, 
                    выдан {$passport_issued|escape} {$passport_code|escape}, дата выдачи {$passport_date|date}, 
                    , настоящим даю согласие
                    ООО МКК ""На личное+"" на автоматическое списание денежных средств с моего банковского счета, с
                    использованием банковской карты, реквизиты которой были мной предоставлены при оформлении Договора
                    потребительского займа № {$number} от {$create_date|date} (Далее по тексту - Договор) при
                    условии наличия денежных средств на таком счете. Списание денежных средств происходит в счет
                    погашения задолженности по Договору.</p>
                <p>В случае отсутствия денежных средств на счете на момент осуществления автоматического списания,
                    автоматическое списание может быть повторено до момента успешного списания средств.</p>
                <p></p>
            </td>
        </tr>
        <tr>
            <td>
                <table cellspacing="0" cellpadding="2" border="1">
                    <tbody>
                        <tr>
                            <td>
                                <p>Дата {$created|date}</p>
                                <p lang="ru-RU">{$lastname|escape} {$firstname|escape} {$patronymic|escape}</p>
                                <p>Подписано с использованием ПЭП: {$asp|escape}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>