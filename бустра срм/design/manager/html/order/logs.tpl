<div {*id="logs" class="tab-pane" role="tabpanel"*}>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3>Логирование</h3>
                    <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                        <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                            <table class="jsgrid-table table table-striped table-hover">
                                <tr class="jsgrid-header-row">
                                    <th style="width: 80px; max-width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'date_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'date_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                        {if $sort == 'date_desc'}<a href="{url sort='date_asc'}">Дата</a>{else}<a href="{url sort='date_desc'}">Дата</a>{/if}
                                    </th>
                                    <th style="width: 120px; max-width: 120px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'type_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'type_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                        {if $sort == 'type_desc'}<a href="{url sort='type_asc'}">Тип операции</a>{else}<a href="{url sort='type_asc'}">Тип операции</a>{/if}
                                    </th>
                                    <th style="width: 100px;  max-width: 100px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'manager_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'manager_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                        {if $sort == 'manager_desc'}<a href="{url sort='manager_asc'}">Менеджер</a>{else}<a href="{url sort='manager_asc'}">Менеджер</a>{/if}
                                    </th>
                                    <th style="width: 80px;" class="jsgrid-header-cell jsgrid-header-sortable {if $sort == 'order_asc'}jsgrid-header-sort jsgrid-header-sort-asc{elseif $sort == 'order_desc'}jsgrid-header-sort jsgrid-header-sort-desc{/if}">
                                        {if $sort == 'order_desc'}<a href="{url sort='order_asc'}">№ заказа</a>{else}<a href="{url sort='order_asc'}">№ заказа</a>{/if}
                                    </th>
                                </tr>
                                <tr class="jsgrid-filter-row" id="search_form">

                                    <td style="width: 80px; max-width: 80px;" class="jsgrid-cell jsgrid-align-right">
                                        <input type="hidden" name="sort" value="{$sort}" />
                                        <input type="text" name="date" value="{$search['date']}" class="form-control input-sm">
                                    </td>
                                    <td style="width: 120px;  max-width: 120px" class="jsgrid-cell">
                                        <select name="type" class="form-control input-sm">
                                            <option value=""></option>
                                            {foreach $changelog_types as $t_key => $t_name}
                                                <option value="{$t_key}" {if $t_key == $search['type']}selected="true"{/if}>{$t_name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </td>
                                    <td style="width: 100px;  max-width: 100px" class="jsgrid-cell">
                                        <select name="manager" class="form-control input-sm">
                                            <option value=""></option>
                                            {foreach $managers as $m}
                                                <option value="{$m->id}" {if $m->id == $search['manager']}selected="true"{/if}>{$m->name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </td>
                                    <td style="width: 80px; max-width: 80px;" class="jsgrid-cell">
                                        <input type="text" name="order" value="{$search['order']}" class="form-control input-sm">
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="jsgrid-grid-body">
                            <table class="jsgrid-table table table-striped table-hover ">
                                <tbody>
                                {if $changelogs}
                                    {foreach $changelogs as $changelog}
                                        <tr class="jsgrid-row">
                                            <td style="width: 80px; max-width: 80px;" class="jsgrid-cell jsgrid-align-right">
                                                <div class="button-toggle-wrapper">
                                                    <button class="js-open-order button-toggle" data-id="{$changelog->id}" type="button" title="Подробнее"></button>
                                                </div>
                                                <span>{$changelog->created|date}</span>
                                                {$changelog->created|time}
                                            </td>
                                            <td style="width: 120px; max-width: 120px;" class="jsgrid-cell">
                                                {if $changelog_types[$changelog->type]}{$changelog_types[$changelog->type]}
                                                {else}{$changelog->type|escape}{/if}
                                            </td>
                                            <td style="width: 100px; max-width: 100px;" class="jsgrid-cell">
                                                <a href="manager/{$changelog->manager->id}">{$changelog->manager->name|escape}</a>
                                            </td>
                                            <td style="width: 80px; max-width: 80px;" class="jsgrid-cell">
                                                <a href="order/{$changelog->order_id}">{$changelog->order_id}</a>
                                            </td>
                                        </tr>
                                        <tr class="order-details" id="changelog_{$changelog->id}" style="display:none">
                                            <td colspan="4">
                                                <div class="row">
                                                    <table class="table">
                                                        <tr>
                                                            <th>Параметр</th>
                                                            <th>Старое значение</th>
                                                            <th>Новое значение</th>
                                                        </tr>
                                                        {foreach $changelog->old_values as $field => $old_value}
                                                            <tr>
                                                                <td>{$field}</td>
                                                                <td>
                                                                    {if $field == 'status' && is_numeric($old_value)}
                                                                        {$order_statuses[$changelog->old_values[$field]]}
                                                                    {else}
                                                                        {$old_value|escape}
                                                                    {/if}
                                                                </td>
                                                                <td>
                                                                    {if $field == 'status'}
                                                                        {$order_statuses[$changelog->new_values[$field]]}
                                                                    {else}
                                                                        {$changelog->new_values[$field]|escape}
                                                                    {/if}
                                                                </td>
                                                            </tr>
                                                        {/foreach}
                                                    </table>

                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}
                                {/if}
                                </tbody>
                            </table>
                        </div>

                        <div class="jsgrid-load-shader" style="display: none; position: absolute; inset: 0px; z-index: 10;">
                        </div>
                        <div class="jsgrid-load-panel" style="display: none; position: absolute; top: 50%; left: 50%; z-index: 1000;">
                            Идет загрузка...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>