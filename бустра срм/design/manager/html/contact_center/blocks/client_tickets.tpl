<div class="card">
    <div class="card-body">
        <div class="mb-2">
            <button type="button" class="btn btn-info btn-sm" id="client-tickets-filter-btn">Отфильтровать</button>
            <button type="button" class="btn btn-secondary btn-sm" id="client-tickets-reset-btn">Сбросить</button>
        </div>

        <div class="client-tickets-scrollbar-top" id="client-tickets-scrollbar-top">
            <div class="dummy"></div>
        </div>
        <div class="table-responsive" id="client-tickets-table-container">
            <table class="table table-bordered table-striped" id="client-tickets-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Канал</th>
                    <th>Дата</th>
                    <th>Принято в работу</th>
                    <th>Тип обращения</th>
                    <th>Тема</th>
                    <th>Статус проработки</th>
                    <th>Приоритет</th>
                    <th>Статус обращения</th>
                    <th>Компания</th>
                    <th>Телефон</th>
                    <th>Регион</th>
                    <th>Дни просрочки</th>
                    <th>Исполнитель</th>
                    <th>Инициатор</th>
                    <th>Ответственный по договору</th>
                    <th>Группа</th>
                    <th>Описание</th>
                    <th>Результат отработки</th>
                </tr>
                <tr id="client-tickets-filter-row">
                    <th>
                        <input type="text" name="id" value="{$filters['id']|escape}" class="form-control input-sm" placeholder="#">
                    </th>
                    <th>
                        <select name="chanel_id" class="form-control">
                            <option value="">—</option>
                            {foreach $channels as $channel}
                                <option value="{$channel->id}" {if $channel->id == $filters['chanel_id']}selected{/if}>{$channel->name|escape}</option>
                            {/foreach}
                        </select>
                    </th>
                    <th>
                        <div class="input-group">
                            <input type="text" name="date_range" class="form-control client-tickets-daterange"
                                   placeholder="Период" value="{$filters['date_range']|escape}" autocomplete="off">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="far fa-calendar"></i></span>
                            </div>
                        </div>
                    </th>
                    <th>
                        <div class="input-group">
                            <input type="text" name="accepted_date_range" class="form-control client-tickets-daterange"
                                   placeholder="Период" value="{$filters['accepted_date_range']|escape}" autocomplete="off">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="far fa-calendar"></i></span>
                            </div>
                        </div>
                    </th>
                    <th>
                        <select name="subject_parent_id" class="form-control">
                            <option value="">—</option>
                            {foreach $subjects.main as $key => $subject}
                                <option value="{$key}" {if $key == $filters['subject_parent_id']}selected{/if}>{$subject|escape}</option>
                            {/foreach}
                        </select>
                    </th>
                    <th>
                        <select name="subject_id" class="form-control">
                            <option value="">—</option>
                            {foreach $subjects.child as $key => $subject}
                                <option value="{$key}" {if $key == $filters['subject_id']}selected{/if}>{$subject|escape}</option>
                            {/foreach}
                        </select>
                    </th>
                    <th>
                        <select name="status_id" class="form-control">
                            <option value="">—</option>
                            {foreach $statuses as $status}
                                <option value="{$status->id}" {if $status->id == $filters['status_id']}selected{/if}>{$status->name|escape}</option>
                            {/foreach}
                        </select>
                    </th>
                    <th>
                        <select name="priority_id" class="form-control">
                            <option value="">—</option>
                            {foreach $priorities as $priority}
                                <option value="{$priority->id}" {if $priority->id == $filters['priority_id']}selected{/if}>{$priority->name|escape}</option>
                            {/foreach}
                        </select>
                    </th>
                    <th></th>
                    <th>
                        <select name="company_id" class="form-control">
                            <option value="">—</option>
                            {foreach $companies as $company}
                                <option value="{$company->id}" {if $company->id == $filters['company_id']}selected{/if}>{$company->name|escape}</option>
                            {/foreach}
                        </select>
                    </th>
                    <th>
                        <input type="text" name="phone" value="{$filters['phone']|escape}" class="form-control input-sm" placeholder="Телефон">
                    </th>
                    <th></th>
                    <th></th>
                    <th>
                        <select name="manager_id" class="form-control">
                            <option value="">—</option>
                            {foreach $managers as $manag}
                                <option value="{$manag->id}" {if $manag->id == $filters['manager_id']}selected{/if}>{$manag->name|escape}</option>
                            {/foreach}
                        </select>
                    </th>
                    <th>
                        <select name="initiator_id" class="form-control">
                            <option value="">—</option>
                            {foreach $managers as $manag}
                                <option value="{$manag->id}" {if $manag->id == $filters['initiator_id']}selected{/if}>{$manag->name|escape}</option>
                            {/foreach}
                        </select>
                    </th>
                    <th>
                        <select name="responsible_person_name" class="form-control">
                            <option value="">—</option>
                            {foreach $responsible_persons as $uid => $name}
                                <option value="{$name|escape}" {if $name == $filters['responsible_person_name']}selected{/if}>{$name|escape}</option>
                            {/foreach}
                        </select>
                    </th>
                    <th>
                        <select name="responsible_group_name" class="form-control">
                            <option value="">—</option>
                            {foreach $responsible_groups as $uid => $name}
                                <option value="{$name|escape}" {if $name == $filters['responsible_group_name']}selected{/if}>{$name|escape}</option>
                            {/foreach}
                        </select>
                    </th>
                    <th>
                        <input type="text" name="description" value="{$filters['description']|escape}" class="form-control input-sm" placeholder="Описание">
                    </th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                {if $items && $items|@count > 0}
                    {foreach $items as $ticket}
                        <tr>
                            <td>
                                <a href="tickets/{$ticket->id}" target="_blank">
                                    {$ticket->id|escape}
                                </a>
                            </td>
                            <td>{$ticket->chanel_name|escape}</td>
                            <td>{$ticket->created_at|date_format:'%d.%m.%Y %H:%M'}</td>
                            <td>{if $ticket->accepted_at}{$ticket->accepted_at|date_format:'%d.%m.%Y %H:%M'}{/if}</td>
                            <td>{$ticket->subject_parent_name|escape}</td>
                            <td>{$ticket->ticket_subject|escape}</td>
                            <td>
                                <span class="badge badge-pill"
                                      style="background-color: {$ticket->status_color|escape:'htmlall'}">
                                    {$ticket->status_name|escape}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-pill"
                                      style="background-color: {$ticket->priority_color|escape:'htmlall'}"
                                      title="{$ticket->priority_name|escape}">
                                    {$ticket->priority_name|escape}
                                </span>
                            </td>
                            <td>
                                {if $ticket->is_repeat}
                                    <span class="badge badge-pill badge-danger">Повторное</span>
                                {else}
                                    <span class="badge badge-pill badge-primary">Первичное</span>
                                {/if}
                            </td>
                            <td>{$ticket->company_name|escape}</td>
                            <td>{$ticket->client_phone|replace:'false':''|escape}</td>
                            <td>{$ticket->client_region|escape}</td>
                            <td>
                                {if isset($ticket->data.overdue_days)}
                                    {$ticket->data.overdue_days}
                                {else}
                                    нет информации
                                {/if}
                            </td>
                            <td>{$ticket->name_manager|escape}</td>
                            <td>{$ticket->name_initiator|escape}</td>
                            <td>{$ticket->responsible_person_name|escape}</td>
                            <td>{$ticket->responsible_group_name|escape}</td>
                            <td class="text-clamp">
                                {$ticket->description|escape|make_urls_clickable}
                            </td>
                            <td>
                                {$ticket->final_comment|default:$ticket->last_comment|escape|make_urls_clickable}
                            </td>
                        </tr>
                    {/foreach}
                {else}
                    <tr>
                        <td colspan="19" class="text-center">
                            <div class="alert alert-info m-0">
                                Для этого клиента тикеты не найдены.
                            </div>
                        </td>
                    </tr>
                {/if}
                </tbody>
            </table>
        </div>
        {include file='html_blocks/table_pagination.tpl'}
    </div>
</div>

