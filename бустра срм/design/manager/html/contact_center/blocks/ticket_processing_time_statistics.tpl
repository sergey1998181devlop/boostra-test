<div class="tab-pane fade" id="timing" role="tabpanel">
    <div class="mb-3">
        <form method="get" action="{$report_uri}" class="form-inline">
            <label class="mr-2">Приоритет:</label>
            <select name="priority_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                <option value="">Все приоритеты</option>
                {if !empty($priorities)}
                    {foreach from=$priorities item=priority}
                        <option value="{$priority->id}"
                            {if $selected_priority_id == $priority->id}selected{/if}>
                            {$priority->name}
                        </option>
                    {/foreach}
                {/if}
            </select>
        </form>
    </div>

    <div class="mb-4">
        <h4 class="mb-3">Среднее время реакции на заявку</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="reaction-time-table">
                <thead class="thead-light">
                <tr>
                    <th>Месяц</th>
                    <th>Тип</th>
                    <th class="text-center">Количество заявок</th>
                    <th class="text-center">Среднее время реакции</th>
                    <th class="text-center">Среднее время первого ответа</th>
                    <th class="text-center">Среднее время решения</th>
                </tr>
                </thead>
                <tbody>
                {if !empty($ticketTimeMetrics.reaction_times.monthly)}
                    {foreach from=$ticketTimeMetrics.reaction_times.monthly key=month item=monthData}
                        {assign var="reactionTypeCount" value=$ticketTimeMetrics.reaction_times.types|@count}
                        {assign var="reactionRowspan" value=1 + $reactionTypeCount}

                        {* Основная строка месяца - кликабельная для раскрытия *}
                        <tr class="month-row" data-month="{$month}">
                            <td class="font-weight-bold" rowspan="{$reactionRowspan}">
                                <i class="fa fa-plus-circle toggle-icon" data-month="{$month}"></i>
                                {$month}
                            </td>
                            <td class="font-weight-bold">Всего</td>
                            <td class="text-center font-weight-bold">{$monthData.total.count|number_format:0:'.':' '}</td>
                            <td class="text-center font-weight-bold">{$monthData.total.average}</td>
                            <td class="text-center font-weight-bold">
                                {if isset($ticketTimeMetrics.first_response_times.monthly[$month])}
                                    {$ticketTimeMetrics.first_response_times.monthly[$month].total.average}
                                {else}
                                    <span class="text-muted">-</span>
                                {/if}
                            </td>
                            <td class="text-center font-weight-bold">
                                {if isset($ticketTimeMetrics.resolution_times.monthly[$month])}
                                    {$ticketTimeMetrics.resolution_times.monthly[$month].total.average}
                                {else}
                                    <span class="text-muted">-</span>
                                {/if}
                            </td>
                        </tr>

                        {* Строки с типами для месяца *}
                        {foreach from=$ticketTimeMetrics.reaction_times.types key=typeId item=typeName}
                            <tr class="type-detail-row" data-month="{$month}">
                                <td>{$typeName}</td>
                                {if isset($monthData.types[$typeId])}
                                    <td class="text-center">{$monthData.types[$typeId].count|number_format:0:'.':' '}</td>
                                    <td class="text-center">{$monthData.types[$typeId].average}</td>
                                {else}
                                    <td class="text-center"><span class="text-muted">0</span></td>
                                    <td class="text-center"><span class="text-muted">-</span></td>
                                {/if}
                                <td class="text-center">
                                    {if isset($ticketTimeMetrics.first_response_times.monthly[$month].types[$typeId])}
                                        {$ticketTimeMetrics.first_response_times.monthly[$month].types[$typeId].average}
                                    {else}
                                        <span class="text-muted">-</span>
                                    {/if}
                                </td>
                                <td class="text-center">
                                    {if isset($ticketTimeMetrics.resolution_times.monthly[$month].types[$typeId])}
                                        {$ticketTimeMetrics.resolution_times.monthly[$month].types[$typeId].average}
                                    {else}
                                        <span class="text-muted">-</span>
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}

                        {* Детализация по дням (изначально скрыта) *}
                        {if !empty($ticketTimeMetrics.reaction_times.daily)}
                            {foreach from=$ticketTimeMetrics.reaction_times.daily key=day item=dayData}
                                {if $dayData.month == $month}
                                    {* Строка с общими данными за день *}
                                    <tr class="daily-details-row" data-month="{$month}" style="display: none;">
                                        <td class="pl-4" rowspan="{count($ticketTimeMetrics.reaction_times.types) + 1}">
                                            {$day|date_format:"%d"}
                                        </td>
                                        <td class="font-weight-bold">Всего</td>
                                        <td class="text-center font-weight-bold">{$dayData.total.count|default:'0'}</td>
                                        <td class="text-center font-weight-bold">{$dayData.total.average|default:'-'}</td>
                                        <td class="text-center font-weight-bold">
                                            {if isset($ticketTimeMetrics.first_response_times.daily[$day])}
                                                {$ticketTimeMetrics.first_response_times.daily[$day].total.average|default:'-'}
                                            {else}
                                                -
                                            {/if}
                                        </td>
                                        <td class="text-center font-weight-bold">
                                            {if isset($ticketTimeMetrics.resolution_times.daily[$day])}
                                                {$ticketTimeMetrics.resolution_times.daily[$day].total.average|default:'-'}
                                            {else}
                                                -
                                            {/if}
                                        </td>
                                    </tr>

                                    {* Строки по типам обращений для этого дня *}
                                    {foreach from=$ticketTimeMetrics.reaction_times.types key=typeId item=typeName}
                                        <tr class="daily-details-row" data-month="{$month}" style="display: none;">
                                            <td>{$typeName}</td>
                                            {if isset($dayData.types[$typeId])}
                                                <td class="text-center">{$dayData.types[$typeId].count|default:'0'}</td>
                                                <td class="text-center">{$dayData.types[$typeId].average|default:'-'}</td>
                                            {else}
                                                <td class="text-center"><span class="text-muted">0</span></td>
                                                <td class="text-center"><span class="text-muted">-</span></td>
                                            {/if}
                                            <td class="text-center">
                                                {if isset($ticketTimeMetrics.first_response_times.daily[$day].types[$typeId])}
                                                    {$ticketTimeMetrics.first_response_times.daily[$day].types[$typeId].average|default:'-'}
                                                {else}
                                                    <span class="text-muted">-</span>
                                                {/if}
                                            </td>
                                            <td class="text-center">
                                                {if isset($ticketTimeMetrics.resolution_times.daily[$day].types[$typeId])}
                                                    {$ticketTimeMetrics.resolution_times.daily[$day].types[$typeId].average|default:'-'}
                                                {else}
                                                    <span class="text-muted">-</span>
                                                {/if}
                                            </td>
                                        </tr>
                                    {/foreach}
                                {/if}
                            {/foreach}
                        {/if}
                    {/foreach}
                {else}
                    <tr>
                        <td colspan="6" class="text-center">Нет данных за указанный период</td>
                    </tr>
                {/if}
                </tbody>
            </table>
        </div>
    </div>

    <div class="mb-4">
        <h4 class="mb-3">Среднее время обработки заявок по типам обращений</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="processing-time-table">
                <thead class="thead-light">
                <tr>
                    <th>Месяц</th>
                    <th>Тип обращения</th>
                    <th class="text-center">Количество заявок</th>
                    <th class="text-center">Среднее время обработки</th>
                </tr>
                </thead>
                <tbody>
                {if !empty($ticketTimeMetrics.processing_times.monthly)}
                    {foreach from=$ticketTimeMetrics.processing_times.monthly key=month item=monthData name="monthly_loop"}
                        {* Основная строка месяца - кликабельная для раскрытия *}
                        <tr class="month-row" data-month="{$month}">
                            <td class="font-weight-bold" rowspan="3">
                                <i class="fa fa-plus-circle toggle-icon" data-month="{$month}"></i>
                                {$month}
                            </td>
                            <td class="font-weight-bold">Всего</td>
                            <td class="text-center font-weight-bold">{$monthData.total.count|number_format:0:'.':' '}</td>
                            <td class="text-center font-weight-bold">{$monthData.total.average}</td>
                        </tr>

                        {* Строки с типами для этого месяца *}
                        {foreach from=$ticketTimeMetrics.processing_times.types key=typeId item=typeName}
                            {assign var="typeStats" value=$monthData[$typeId]|default:null}
                            <tr class="type-detail-row" data-month="{$month}">
                                <td>{$typeName}</td>
                                {if $typeStats && $typeStats.count > 0}
                                    <td class="text-center">{$typeStats.count|number_format:0:'.':' '}</td>
                                    <td class="text-center">{$typeStats.average}</td>
                                {else}
                                    <td class="text-center"><span class="text-muted">0</span></td>
                                    <td class="text-center"><span class="text-muted">-</span></td>
                                {/if}
                            </tr>
                        {/foreach}

                        {* Детализация по дням (изначально скрыта) *}
                        {if !empty($ticketTimeMetrics.processing_times.daily)}
                            {foreach from=$ticketTimeMetrics.processing_times.daily key=day item=dayData}
                                {if $dayData.month == $month}
                                    {* Строка с общими данными за день *}
                                    <tr class="daily-details-row" data-month="{$month}" style="display: none;">
                                        <td class="pl-4" rowspan="{count($ticketTimeMetrics.processing_times.types) + 1}">{$day|date_format:"%d"}</td>
                                        <td class="font-weight-bold">Всего</td>
                                        <td class="text-center font-weight-bold">{$dayData.total.count|default:'0'}</td>
                                        <td class="text-center font-weight-bold">{$dayData.total.average|default:'-'}</td>
                                    </tr>

                                    {* Строки по типам обращений для этого дня *}
                                    {foreach from=$ticketTimeMetrics.processing_times.types key=typeId item=typeName}
                                        <tr class="daily-details-row" data-month="{$month}" style="display: none;">
                                            <td>{$typeName}</td>
                                            {if isset($dayData[$typeId])}
                                                <td class="text-center">{$dayData[$typeId].count|default:'0'}</td>
                                                <td class="text-center">{$dayData[$typeId].average|default:'-'}</td>
                                            {else}
                                                <td class="text-center"><span class="text-muted">0</span></td>
                                                <td class="text-center"><span class="text-muted">-</span></td>
                                            {/if}
                                        </tr>
                                    {/foreach}
                                {/if}
                            {/foreach}
                        {/if}
                    {/foreach}
                {else}
                    <tr>
                        <td colspan="4" class="text-center">Нет данных за указанный период</td>
                    </tr>
                {/if}
                </tbody>
            </table>
        </div>
    </div>
</div>
