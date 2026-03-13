<style>
    * {
        font-size: 12px;
    }
</style>

<table style="line-height: 24px">
    <tr>
        <td align="right">??? <br></td>
    </tr>
    <tr>
        <td align="center">
            <strong>РЕШЕНИЕ</strong> <br>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-indent: 10px">На основании комплексного анализа информации, предоставленной ??? {$order->lastname|escape} {$order->firstname|escape} {$order->patronymic|escape},
            {$order->birth|date} г.р., паспорт: {$order->passport_issued}, выдан  {$order->passport_date} г.  {$order->passport_issued}, код подразделения:
            {$order->subdivision_code|escape}, зарегистрирован по адресу:  {if $order->regindex}{$order->regindex}, {/if}
            {if $order->regregion}{$order->regregion} {$order->regregion_shorttype}, {/if}
            {if $order->regcity}{$order->regcity} {$order->regcity_shorttype}, {/if}
            {if $order->regstreet}{$order->regstreet} {$order->regstreet_shorttype}, {/if}
            {if $order->reghousing}д. {$order->reghousing}, {/if}
            {if $order->regbuilding}стр. {$order->regbuilding}, {/if}
            {if $order->regroom}кв. {$order->regroom}, {/if} в анкете-заявлении, заявке на заем,
            сведений полученных из Бюро кредитных историй, учитывая предельную долговую нагрузки, а также иных сведений,
            правомерно полученных Обществом из открытых источников, Микрокредитной ком панией Обществом с ограниченной
            ответственностью «Бустра» принято решение о выдаче Вам займа в сумме: ??? (???) руб.
        </td>
    </tr>
</table>

<br><br><br>

<table>
    <tr>
        <td style="text-indent: 10px; width: 40%">
            <br><br>
            Директор МКК ООО «Бустра»
        </td>
        <td style="width: 30%" align="center">
            <img src="{$order->config->root_url}/design/boostra_mini_norm/html/pdf/i/signa_smelov.png" width="60" alt="">
        </td>
        <td align="right" style="width: 30%">
            <br><br>
            С.Б. Смелов
        </td>
    </tr>
</table>
