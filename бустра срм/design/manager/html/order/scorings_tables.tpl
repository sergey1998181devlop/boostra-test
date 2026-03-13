<div data-order="{$order->order_id}" class="js-scorings-block {if $need_update_scorings}js-need-update{/if}" >
    <h3 class="box-title mt-5">
        <span>Скоринг тесты</span>
    </h3>
    <hr>
    <div class="row" >
        <div class="col-md-12">
            <table class="table">
                <tr>
                    <th>Тип</th>
                    <th>Дата</th>
                    <th>Статус</th>
                    <th>Результат</th>
                    <th></th>
                    {*}<th></th>{*}
                </tr>
                <tr style="display: none">
                    <td colspan="5">
                        <a href="#" data-toggle="collapse" data-target="#tab_scoring_fssp">ФССП ст.46</a>
                    </td>
                </tr>
                <tr class="collapse" id="tab_scoring_fssp">
                    <td colspan="5">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center mb-4">
                                    <div class="col-auto">
                                        <span>Есть ФССП ст.46</span>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="onoffswitch">
                                            <input type="checkbox" name="fssp_status"
                                                   class="onoffswitch-checkbox" value="1"
                                                   id="fssp_{$order->order_id}"
                                                   {if $fssp_items}checked{/if}
                                            />
                                            <label class="onoffswitch-label"
                                                   for="fssp_{$order->order_id}">
                                                <span class="onoffswitch-inner"></span>
                                                <span class="onoffswitch-switch"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <table id="fssp_table" class="table table-bordered" style="display: none">
                                    <input type="hidden" value="{$order->order_id}" name="order_id" />
                                    <input type="hidden" value="{$order->user_id}" name="user_id" />
                                    <thead>
                                    <tr class="text-primary">
                                        <th>Причина</th>
                                        <th>Основание (ч.ст.46)</th>
                                        <th>Дата завершения</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    {if $fssp_items}
                                        {foreach $fssp_items as $fssp_item}
                                            <tr id="row_id_{$fssp_item@iteration}">
                                                <td>
                                                    {if $fssp_reasons}
                                                        <select name="fssp_order[{$fssp_item@iteration}][reason_id]" class="form-control">
                                                            {foreach $fssp_reasons as $fssp_reason}
                                                                <option
                                                                        {if $fssp_reason->id == $fssp_item->reason_id}selected{/if}
                                                                        value="{$fssp_reason->id}">{$fssp_reason->title}</option>
                                                            {/foreach}
                                                        </select>
                                                    {/if}
                                                </td>
                                                <td>
                                                    {if $fssp_basis}
                                                        <select name="fssp_order[{$fssp_item@iteration}][basis_id]" class="form-control">
                                                            {foreach $fssp_basis as $fssp_bas}
                                                                <option
                                                                        {if $fssp_bas->id == $fssp_item->basis_id}selected{/if}
                                                                        value="{$fssp_bas->id}">{$fssp_bas->title}</option>
                                                            {/foreach}
                                                        </select>
                                                    {/if}
                                                </td>
                                                <td>
                                                    <input value="{$fssp_item->date_end|date_format:'%Y.%m.%d'}"
                                                           type="text" name="fssp_order[{$fssp_item@iteration}][date_end]" class="form-control js-datepicker" placeholder="Сегодня">
                                                </td>
                                                <td>
                                                    {if !$fssp_item@first}
                                                        <button onclick="removeRow({$fssp_item@iteration})" class="btn btn-danger">
                                                            <i class="mdi mdi-beaker"></i>
                                                        </button>
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/foreach}
                                    {else}
                                        <tr id="row_id_1">
                                            <td>
                                                {if $fssp_reasons}
                                                    <select name="fssp_order[1][reason_id]" class="form-control">
                                                        {foreach $fssp_reasons as $fssp_reason}
                                                            <option
                                                                    {if $fssp_reason@first}selected{/if}
                                                                    value="{$fssp_reason->id}">{$fssp_reason->title}</option>
                                                        {/foreach}
                                                    </select>
                                                {/if}
                                            </td>
                                            <td>
                                                {if $fssp_basis}
                                                    <select name="fssp_order[1][basis_id]" class="form-control">
                                                        {foreach $fssp_basis as $fssp_bas}
                                                            <option
                                                                    {if $fssp_bas@first}selected{/if}
                                                                    value="{$fssp_bas->id}">{$fssp_bas->title}</option>
                                                        {/foreach}
                                                    </select>
                                                {/if}
                                            </td>
                                            <td>
                                                <input
                                                        value="{date('Y.m.d')}"
                                                        type="text" name="fssp_order[1][date_end]" class="form-control js-datepicker" placeholder="Сегодня">
                                            </td>
                                            <td></td>
                                        </tr>
                                    {/if}
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td>
                                            <button type="button" onclick="addRow()" class="btn btn-success">
                                                <i class="mdi mdi-plus-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    </tfoot>
                                </table>
                                <button type="button" onclick="saveFSSP()" class="btn btn-outline-success">
                                    Сохранить <i class="mdi mdi-check"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                {foreach $user_scorings as $scoring}
                    {if $scoring->type->name != 'svo' && ($scoring->type->name != 'work' || $manager->role != 'verificator_minus')}
                        <tr>
                            <td>
                                {if in_array($scoring->type->name, ['blacklist', 'axilink', 'fssp', 'scorista', 'juicescore', 'dbrain', 'dbrain_passport', 'dbrain_card', 'finkarta', 'egrul'])}
                                    <a href="#" data-toggle="collapse" data-target="#tab_scoring_{$scoring->id}">{$scoring->type->title}</a>
                                {else}
                                    {$scoring->type->title}
                                {/if}
                            </td>
                            <td>
                                {if $scoring->type->name == 'scorista'}
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
                                {elseif $scoring->status_name == 'import'}
                                    <span class="label label-info" title="Скоринг импортируется из 1C">Импорт</span>
                                {elseif $scoring->status_name == 'process' || $scoring->status_name == 'wait'}
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
                                {if $scoring->type->name == 'scorista'}
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
                                    {else}
                                        <small>{$scoring->string_result}</small>
                                    {/if}
                                {elseif $scoring->type->name == 'juicescore'}
                                {else}
                                    <small>{$scoring->string_result|escape}</small>
                                {/if}

                            </td>
                        </tr>

                        {if $scoring->type->name == 'blacklist'}
                            <tr class="collapse" id="tab_scoring_{$scoring->id}">
                                <td colspan="5">
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

                        {if $scoring->type->name == 'dbrain_passport' || $scoring->type->name == 'dbrain_card'}
                            <tr class="collapse" id="tab_scoring_{$scoring->id}">
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

                        {if $scoring->type->name == 'fssp'}
                            <tr class="collapse" id="tab_scoring_{$scoring->id}">
                                <td colspan="5">
                                    {if isset($scoring->body->result[0]->result)}
                                        <ul>
                                            {foreach $scoring->body->result as $key => $value}
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
                                    {else}
                                        Производства не найдены
                                    {/if}
                                </td>
                            </tr>
                        {/if}


                        {if $scoring->type->name == 'scorista'}
                            <tr class="collapse scorista_body_ajax" data-id="{$scoring->id}" data-table_name="{$scoring->table_name}" id="tab_scoring_{$scoring->id}">
                                <td colspan="6">
                                    {if $scoring->body}
                                        {include 'html_blocks/scorista_body_order.tpl'}
                                    {/if}
                                </td>
                            </tr>
                        {/if}

                        {if $scoring->type->name == 'juicescore'}
                            <tr class="collapse" id="tab_scoring_{$scoring->id}">
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

                        {if $scoring->type->name == 'axilink'}
                            <tr class="collapse" id="tab_scoring_{$scoring->id}">
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

                        {if $scoring->type_name == 'finkarta'}
                            <tr class="collapse" id="tab_scoring_{$scoring->id}">
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
                {/foreach}
            </table>

        </div>
    </div>
</div>