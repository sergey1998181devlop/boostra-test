<table width="530">
    <tbody>
        <tr>
            <td>
                <p align="center"><strong>СОГЛАСИЕ</strong></p>
                <p align="center"><strong>на обработку персональных данных</strong></p>
                <p>&nbsp;</p>
                <p>&nbsp;</p>
                <p> 
                    Я, {$lastname|escape} {$firstname|escape} {$patronymic|escape}, 
                    паспорт серия {$passport_serial|escape}, номер {$passport_number|escape}, 
                    выдан {$passport_issued|escape} {$passport_code|escape}, дата выдачи {$passport_date|date}, 
                    зарегистрирован по адресу:
                    {$regregion|escape}, {$regcity|escape}, {$regstreet|escape}, д.{$reghousing|escape}
                    {if $regbuilding}, стр.{$regbuilding}{/if}
                    {if $regroom}, кв.{$regroom}{/if}                    
                     выражаю свое согласие ПАО &laquo;МегаФон&raquo; (127006, г. Москва,
                    Переулок Оружейный, дом 41) на обработку сведений обо мне, как об абоненте, включая, но не
                    ограничиваясь: абонентские номера, сведения об абонентском устройстве, другие данные, позволяющие
                    идентифицировать абонентское устройство, иные сведения об оказываемых услугах по договору об
                    оказании услуг связи, за исключением сведений, составляющих тайну связи; для передачи их и
                    результата обработки указанных сведений Обществу с ограниченной ответственностью Микрокредитная
                    компания &laquo;На личное+&raquo;, расположенному по адресу: : 443058, г. Самара, ул. Победы, 86,
                    офис 2.1.</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>&nbsp;</p>
                <table width="397" cellspacing="0" cellpadding="7">
                    <tbody>
                        <tr>
                            <td width="383" height="100">
                                <table width="397" cellspacing="0" cellpadding="7" border="1">
                                    <tbody>
                                        <tr>
                                            <td width="100%" height="98">
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
            </td>
        </tr>
    </tbody>
</table>