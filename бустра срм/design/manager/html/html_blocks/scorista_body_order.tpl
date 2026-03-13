{assign 'stopfactorsImportant' [
"Негатив по ФССП",
"Высокая доля просрочек в КИ за последние 2 года",
"Глубокая просрочка в КИ за последние 2 года",
"Глубокая просрочка по последним займам в КИ",
"Негативы последних займов в КИ",
"Высокая просрочек в КИ за последние 2 года",
"Высокая вероятность дефолта по КИ",
"Высокий риск банкротства в течении 2х месяцев",
"Банкротство в КИ",
"Подозрение на фрод",
"Черный список скористы",
"Несовпадение ФИО с данными официальных источников",
"Реквизиты паспорта не уникальны",
"Большое количество разных телефонов в заявках на текущий паспорт",
"Регион проживания не совпадает с регионом телефона и регистрации",
"Регион повышенного риска (Белгородская обл)",
"Регион военных действий",
"Регион вблизи военных действий",
"Беженцы с территорий боевых действий",
"Сомнительная серия паспорта",
"Регион повышенного риска (Дальний Восток)",
"Высокая долговая нагрузка по КИ",
"Дополнительная оценка первого займа",
"Высокая доля просрочек в КИ за последние 2 года"
]}

<div>
    {if $scoring->status_name == 'error'}
        <pre class="text-white">{$scoring->body|var_dump}</pre>
    {elseif $scoring->status_name == 'completed'}
        <div class="row">
            <div class="col-md-6">
                <p class="text-info m-0">Рекомендуемое решение: {$scoring->body->decision->decisionName}</p>
                <p class="text-info m-0">Рекомендуемая сумма: {if $scoring->body->additional->decisionSum}{$scoring->body->additional->decisionSum}{else}Нет{/if}</p>
                <p class="text-info">Рекомендуемый период: {if $scoring->body->additional->decisionPeriod}{$scoring->body->additional->decisionPeriod}{else}Нет{/if}</p>
            </div>
            <div class="col-md-6">
                {if $scoring->body->additional->decisionMessage}
                    <p class="box bg-primary m-0">{$scoring->body->additional->decisionMessage}</p>
                {/if}
            </div>
        </div>

        <ul>
            {foreach $scoring->body as $key => $value}
                <li>
                    {$key}
                    <ul>
                        {foreach $value as $kk =>  $item}
                            {if $item->description}
                                <li >
                                    {if is_object($item->result)}
                                        {$kk}<br />
                                        {foreach $item->result as $k => $v}
                                            {$k}: {$v}<br />
                                        {/foreach}
                                    {else}
                                        {if $item->result > 0}
                                            <span
                                                {if in_array($item->description, $stopfactorsImportant)} class="text-danger" {else} class="text-info" {/if}
                                            >
                                        {/if}
                                        <strong>{$item->description}</strong>:
                                        {if is_null($item->result)}-
                                        {else}
                                            {$item->result}
                                        {/if}
                                        {if $item->result > 0}
                                            </span>
                                        {/if}
                                    {/if}
                                </li>
                            {/if}
                        {/foreach}
                    </ul>
                </li>
            {/foreach}
        </ul>
    {/if}
</div>
