                        {assign 'stopfactorsImportant' [
                            'Негатив по ФССП',
                            'Негатив по ЧС',
                            'Наличие исполнительных производств в ФССП',
                            'Нахождение в черном списке',
                            'Наличие судебных решений о взыскании задолженности',
                            'Наличие задолженности по налогам и сборам',
                            'Наличие признаков банкротства физического лица',
                            'Наличие сведений о причастности к экстремистской деятельности или терроризму'
                        ]}
                        <div data-order="{$order->order_id}" class="js-scorings-block {if $need_update_scorings}js-need-update{/if}" >

                            <h3 class="box-title mt-5">
                                <a href="javascript:void(0);" data-toggle="collapse" data-target="#scorings">
                                    <span>Скоринг тесты:</span>
                                </a>
                                {if $inactive_run_scorings}
                                    <a class="float-right btn btn-sm btn-outline-primary btn-rounded ">Выполняется</a>
                                {else}
                                    <a class="float-right btn btn-sm btn-outline-success btn-rounded js-run-scorings" href="javascript:void(0);" data-order="{$order->order_id}" data-type="free">Запустить б/п проверки</a>
                                {/if}

                            </h3>
                            <hr>
                            <div class="row {if !$open_scorings}collapse{/if}" id="scorings">
                                <div class="col-md-12">

                                    {$scor = ''}
                                    <table class="table">
                                        <tr>
                                            <th>Тип</th>
                                            <th>Дата</th>
                                            <th>Статус</th>
                                            <th>Результат</th>
                                            <th></th>
                                            <th></th>
                                        </tr>

                                        {foreach $scoring_types as $scoring_type}
                                            {if $scoring_type->id != 18}
                                                {if $scoring_type->name != 'svo' && ($scoring_type->name != 'work' || $manager->role != 'verificator_minus')}
                                                    {$scoring =  $scorings[$scoring_type->name]}

                                                    <tr>
                                                        <td>
                                                            {if in_array($scoring_type->name, ['efrsb', 'fssp', 'axilink', 'scorista', 'juicescore', 'blacklist', 'dbrain', 'dbrain_passport', 'dbrain_card', 'finkarta', 'egrul'])}
                                                                <a href="#" data-toggle="collapse" data-target="#scoring_{$scoring->id}" data-type="{$scoring_type->name}" data-id="{$scoring->id}">
                                                                    {$scoring_type->title}
                                                                    {if $scoring_type->name == 'scorista' || $scoring_type->name == 'axilink'}
                                                                        <small class="text-white">{$scoring->scorista_id}</small>
                                                                    {/if}
                                                                </a>
                                                            {else}
                                                                {$scoring_type->title}
                                                            {/if}
                                                        </td>
                                                        <td>
                                                            {if $scoring_type->name == 'scorista'}
                                                                {if $scoring->status_name == 'completed'}
                                                                    {$scoring->end_date|date} {$scoring->end_date|time}
                                                                {elseif $scoring->status_name == 'stopped'}
                                                                    {if $scoring->end_date}
                                                                        {$scoring->end_date|date} {$scoring->end_date|time}
                                                                    {else}
                                                                        {$scoring->start_date|date} {$scoring->start_date|time}
                                                                    {/if}
                                                                {elseif $scoring->status_name == 'error'}
                                                                    {$scoring->start_date|date} {$scoring->start_date|time}
                                                                {/if}
                                                            {else}
                                                                {if $scoring->created}
                                                                    {$scoring->created|date} {$scoring->created|time}
                                                                {/if}
                                                            {/if}
                                                        </td>
                                                        <td>
                                                            {if !$scoring}
                                                                <span class="label label-warning">Не проводился</span>
                                                            {elseif $scoring->status_name == 'new'}
                                                                <span class="label label-info" title="Скоринг находится в очереди на выполнение">Ожидание</span>
                                                            {elseif $scoring->status_name == 'process' || $scoring->status_name == 'import' || $scoring->status_name == 'wait'}
                                                                <span class="label label-primary">Выполняется</span>
                                                            {elseif $scoring->status_name == 'error'}
                                                                <span class="label label-danger">Ошибка</span>
                                                            {elseif $scoring->status_name == 'completed'}
                                                                <span class="label label-success">Завершен</span>
                                                            {elseif $scoring->status_name == 'stopped'}
                                                                <span class="label label-danger">Остановлен досрочно</span>
                                                            {/if}

                                                        </td>
                                                        <td>
                                                            {if $scoring->status_name == 'completed'}
                                                                {if $scoring->success}<span class="label label-success">Пройден</span>
                                                                {else}<span class="label label-danger">Не пройден</span>{/if}
                                                            {/if}
                                                        </td>
                                                        <td>
                                                            {if $scoring->type_name == 'scorista'}
                                                                {if $scoring->status_name == 'completed'}
                                                                    {if $scoring->success}
                                                                        <span class="label label-success">{$scoring->scorista_ball}</span>
                                                                    {else}
                                                                        <span class="label label-danger">{$scoring->scorista_ball}</span>
                                                                    {/if}

                                                                    {if $scoring->body->decision->decisionName == 'Отказ'}
                                                                        <span class="label label-danger">{$scoring->body->decision->decisionName}</span>
                                                                    {elseif $scoring->body->decision->decisionName}
                                                                        <span class="label label-info">{$scoring->body->decision->decisionName}</span>
                                                                    {/if}

                                                                    {if $order->loan_history|count == 0}
                                                                        {if $scoring->scorista_ball > 699}
                                                                            <p class="p-0 m-0 text-success"><small>МОЖНО ЗВОНИТЬ ТОЛЬКО КЛИЕНТУ</small></p>
                                                                        {elseif $scoring->scorista_ball > 499 && $scoring->scorista_status == 'Одобрено'}
                                                                            <p class="p-0 m-0 text-primary"><small>ЗВОНИМ КЛИЕНТУ И ОДНОМУ КОН. ЛИЦУ, либо 100 % работа , ищем телефон в анкете </small></p>
                                                                        {elseif $scoring->scorista_ball > 450}
                                                                            <p class="p-0 m-0 text-warning"><small>ЗВОНИМ КЛИЕНТУ, БЕРЕМ  1-2 КОНТ. ЛИЦА, ПРОЗВАНИВАЕМ  РАБОТУ - НУЖНО УБЕДИТЬСЯ ЧТО КЛИЕНТ ТАМ РАБОТАЕТ, ЕСЛИ ВСЕ УСЛОВИЯ ВЫПОЛНЕНЫ ВЫДАЕМ</small></p>
                                                                        {/if}
                                                                    {/if}
                                                                {else}
                                                                    <small>{$scoring->string_result}</small>
                                                                {/if}
                                                            {elseif $scoring->type_name == 'juicescore'}
                                                                {if $scoring->body}
                                                                    {if $scoring->success}
                                                                        <span class="label label-success">{if isset($scoring->body['AntiFraud score'])}{$scoring->body['AntiFraud score']}{/if}</span>
                                                                    {else}
                                                                        <span class="label label-danger">{if isset($scoring->body['AntiFraud score'])}{$scoring->body['AntiFraud score']}{/if}</span>
                                                                    {/if}
                                                                {/if}
                                                            {else}
                                                                <small>{$scoring->string_result|escape}</small>
                                                            {/if}

                                                        </td>
                                                        <td>
                                                            {if $scoring->status_name == 'new' || $scoring->status_name == 'process' || $scoring->status_name == 'import'}
                                                                <a class="float-right btn btn-xs btn-outline-primary btn-rounded btn-loading">Выполняется</a>
                                                            {else}
                                                                <a class="{*if !$scoring_type->active}hide{/if*} float-right btn btn-xs btn-outline-success btn-rounded js-run-scorings" href="javascript:void(0);" data-order="{$order->order_id}" data-type="{$scoring_type->name}">Запустить</a>
                                                            {/if}

                                                        </td>
                                                    </tr>

                                                    {if $scoring->type_name == 'blacklist'}
                                                        <tr class="collapse" id="scoring_{$scoring->id}">
                                                            <td colspan="6">
                                                                {if $scoring->body}
                                                                    <table class="table">
                                                                        {foreach $scoring->body as $key => $item}
                                                                            <tr>
                                                                                <td>{$item->created}</td>
                                                                                <td>{$item->block}</td>
                                                                                <td>{$item->text}</td>
                                                                            </tr>
                                                                        {/foreach}
                                                                    </table>
                                                                {else}
                                                                    Записей не найдено
                                                                {/if}
                                                            </td>
                                                        </tr>
                                                    {/if}

                                                    {if $scoring->type_name == 'dbrain_passport' || $scoring->type_name == 'dbrain_card'}
                                                        <tr class="collapse" id="scoring_{$scoring->id}">
                                                            <td colspan="6">
                                                                {if $scoring->body}
                                                                    <table class="table table-hover table-bordered">
                                                                        <tr>
                                                                            <th>Поле</th>
                                                                            <th>Клиент</th>
                                                                            <th>Распознано</th>
                                                                            <th>Точность</th>
                                                                        </tr>
                                                                        {foreach $scoring->body as $key => $item}
                                                                            <tr class="{if $item->success==1}text-success{else}text-danger{/if}">
                                                                                <td>{$key}</td>
                                                                                <td>{$item->order_value}</td>
                                                                                <td>{$item->text}</td>
                                                                                <td>{$item->confidence} %</td>
                                                                            </tr>
                                                                        {/foreach}
                                                                    </table>
                                                                {else}
                                                                    Записей не найдено
                                                                {/if}
                                                            </td>
                                                        </tr>
                                                    {/if}

                                                    {if $scoring->type_name == 'fssp'}
                                                        <tr class="collapse" id="scoring_{$scoring->id}">
                                                            <td colspan="6">
                                                                {if !empty($scoring->body->response->result[0]->result) && $scoring->body->response->result[0]->result|count > 0}
                                                                    <ul>
                                                                        {foreach $scoring->body->response->result as $key => $value}
                                                                            <li>
                                                                                <ul>
                                                                                    {foreach $value->result as $kk =>  $item}
                                                                                        <li>
                                                                                            <p>{$item->name}</p>
                                                                                            <p>{$item->exe_production}</p>
                                                                                            <p>{$item->details}</p>
                                                                                            <p>{$item->subject}</p>
                                                                                            <p>{$item->department}</p>
                                                                                            <p>{$item->bailiff}</p>
                                                                                            <p>{$item->ip_end}</p>
                                                                                        </li>
                                                                                    {/foreach}
                                                                                </ul>
                                                                            </li>
                                                                        {/foreach}
                                                                    </ul>
                                                                {elseif $scoring->status_name != 'error'}
                                                                    Производства не найдены
                                                                {/if}
                                                            </td>
                                                        </tr>
                                                    {/if}

                                                    {if !empty($scoring->body->additional->decisionSum)}
                                                        {$scor_amount = $scoring->body->additional->decisionSum}
                                                    {elseif !empty($scoring->body->sum)}
                                                        {$scor_amount = $scoring->body->sum}
                                                    {/if}
                                                    {if !empty($scoring->body->additional->decisionPeriod)}
                                                        {$scor_period = $scoring->body->additional->decisionPeriod}
                                                    {elseif !empty($scoring->body->limit_period)}
                                                        {$scor_period = $scoring->body->limit_period}
                                                    {/if}
                                                    {if !empty($scoring->body->additional->decisionMessage)}
                                                        {$scor_message = $scoring->body->additional->decisionMessage}
                                                    {elseif !empty($scoring->body->message)}
                                                        {$scor_message = $scoring->body->message}
                                                    {/if}
                                                    {if $scoring->type_name == 'scorista'}
                                                        <tr class="collapse" id="scoring_{$scoring->id}">
                                                            <td colspan="6">
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
                                                                                            <li>
                                                                                                {if is_object($item->result)}
                                                                                                    {$kk}<br />
                                                                                                    {foreach $item->result as $k => $v}
                                                                                                        {$k}: {$v}<br />
                                                                                                    {/foreach}
                                                                                                {else}
                                                                                                    {if $item->result > 0}
                                                                                                        <span
                                                                                                        {if in_array($item->description, $stopfactorsImportant)}
                                                                                                            class="text-danger"
                                                                                                        {else}
                                                                                                            class="text-info"
                                                                                                        {/if}
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
                                                            </td>
                                                        </tr>
                                                    {/if}
                                                    {if $scoring->type_name == 'dbrain'}
                                                        <tr class="collapse" id="scoring_{$scoring->id}">
                                                            <td colspan="6">
                                                                {if $scoring->status_name == 'error'}
                                                                    <pre class="text-white">{$scoring->body|var_dump}</pre>
                                                                {elseif $scoring->status_name == 'completed'}
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <p class="text-info m-0">Рекомендуемое решение: {$scoring->body->name}</p>
                                                                            <p class="text-info m-0">Рекомендуемая сумма: {if $scoring->body->final_limit}{$scoring->body->final_limit}{else}Нет{/if}</p>
                                                                            <p class="text-info">Скор балл: {if $scoring->body->score}{$scoring->body->score}{else}Нет{/if}</p>
                                                                            <p class="text-info">Балл НБКИ: {if !empty($scoring->body->nbki_score)}{$scoring->body->nbki_score}{else}Нет{/if}</p>
                                                                            <p class="text-info">Причина отказа: {if $scoring->body->message}{$scoring->body->message}{else}Нет{/if}</p>
                                                                        </div>
                                                                    </div>
                                                                {/if}
                                                            </td>
                                                        </tr>
                                                    {/if}
                                                    {if $scoring->type_name == 'juicescore'}
                                                        <tr class="collapse" id="scoring_{$scoring->id}">
                                                            <td colspan="6">

                                                                <ul>
                                                                    {foreach $scoring->body as $key => $item}
                                                                        {if $key == 'Predictors'}
                                                                            <li>
                                                                                <p>{$key}</p>
                                                                                <ul>
                                                                                    {foreach $item as $pkey => $pitem}
                                                                                        <li>{$pkey}: {$pitem}</li>
                                                                                    {/foreach}
                                                                                </ul>
                                                                            </li>
                                                                        {elseif is_object($item)}
                                                                            <li><span class="label-danger">{$scoring->string_result}</span></li>
                                                                        {else}
                                                                            <li>{$key}: {$item}</li>
                                                                        {/if}
                                                                    {/foreach}
                                                                </ul>
                                                            </td>
                                                        </tr>
                                                    {/if}

                                                    {if $scoring->type_name == 'axilink'}
                                                        <tr class="collapse" id="scoring_{$scoring->id}">
                                                            <td colspan="6">
                                                                {if $scoring->status_name == 'error'}
                                                                    <pre class="text-white">{$scoring->string_result}</pre>
                                                                {elseif $scoring->status_name == 'completed'}
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <p class="text-info m-0">Рекомендуемое решение: {if !empty($scoring->body->name)}{$scoring->body->name}{else}Нет{/if}</p>
                                                                            <p class="text-info">Рекомендуемая сумма: {if !empty($scoring->body->sum)}{$scoring->body->sum}{else}Нет{/if}</p>
                                                                            <p class="text-info">Рекомендуемый период: {if !empty($scoring->body->limit_period)}{$scoring->body->limit_period}{else}Нет{/if}</p>
                                                                            <p class="text-info">Балл: {$scoring->scorista_ball}</p>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            {if $scoring->body->message}
                                                                                <p class="box bg-primary m-0">{$scoring->body->message}</p>
                                                                            {/if}
                                                                        </div>
                                                                    </div>
                                                                {/if}
                                                            </td>
                                                        </tr>
                                                    {/if}

                                                    {if $scoring->type_name == 'efrsb'}
                                                        <tr class="collapse" id="scoring_{$scoring->id}">
                                                            <td colspan="6">
                                                                {if $scoring->body}
                                                                    {if is_array($scoring->body)}
                                                                        <span class="label label-danger">{$scoring->string_result}</span>
                                                                    {else}
                                                                        <a href="{$scoring->body}" target="_blank">{$scoring->body}</a>
                                                                    {/if}
                                                                {else}
                                                                    Производства не найдены
                                                                {/if}
                                                            </td>
                                                        </tr>
                                                    {/if}

                                                    {if $scoring->type_name == 'finkarta'}
                                                        <tr class="collapse" id="scoring_{$scoring->id}">
                                                            <td colspan="6">
                                                                {if $scoring->body}
                                                                    <table class="table table-hover table-bordered">
                                                                        <tr>
                                                                            <th>Проверка</th>
                                                                            <th>Результат</th>
                                                                        </tr>
                                                                        {foreach $scoring->body as $check_name => $data}
                                                                            <tr class="{if $data['success']}text-success{else}text-danger{/if}">
                                                                                <td>{$check_name}</td>
                                                                                <td>{$data['result']}</td>
                                                                            </tr>
                                                                        {/foreach}
                                                                    </table>
                                                                {else}
                                                                    Записей не найдено
                                                                {/if}
                                                            </td>
                                                        </tr>
                                                    {/if}

                                                    {if $scoring->type_name == 'egrul'}
                                                        <tr class="collapse" id="scoring_{$scoring->id}">
                                                            <td colspan="6">
                                                                {if $scoring->body}
                                                                    {foreach $scoring->body as $fields}
                                                                        <table class="table table-hover table-bordered">
                                                                            <tr>
                                                                                <th>Поле</th>
                                                                                <th>Значение</th>
                                                                            </tr>
                                                                            {foreach $fields as $field}
                                                                                {if $field["FieldTitle"] != 'Тип записи'}
                                                                                    <tr>
                                                                                        <td>{$field["FieldTitle"]}</td>
                                                                                        <td>{$field["FieldValue"]}</td>
                                                                                    </tr>
                                                                                {/if}
                                                                            {/foreach}
                                                                        </table>
                                                                    {/foreach}
                                                                {else}
                                                                    Пусто
                                                                {/if}
                                                            </td>
                                                        </tr>
                                                    {/if}

                                                {/if}
                                            {/if}
                                        {/foreach}
                                    </table>
                                    <a href="/changelogs/?search[order]={$order->order_id}&sort=date_desc" target="_blank">Смотреть логи по заявке</a>
                                </div>
                            </div>
                        </div>
