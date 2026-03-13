{capture name='page_styles'}
    <style>
        /* Стили для детальной статистики в темной теме */
        .month-row {
            font-weight: bold;
            cursor: pointer;
        }

        .month-row:hover {
            background-color: #2a2e33;
        }

        .detailed-day-row {
            background-color: #252529;
        }

        .detailed-day-row:hover {
            background-color: #2f2f35;
        }

        .toggle-icon {
            margin-right: 5px;
            color: #fff;
        }

        /* Обеспечиваем видимость текста в ячейках */
        .detailed-day-row td {
            color: #e2e2e2 !important;
        }

        .detailed-day-row td .text-muted {
            color: #8a8a8a !important;
        }

        /* Стили для заголовков статусов */
        #detailed th[style^="background-color:"] {
            opacity: 0.8;
        }

        /* Контрастность для ячеек с фоном */
        .detailed-day-row td[style^="background-color:"] {
            color: #e2e2e2 !important;
        }

        /* Стили для строки-плейсхолдера */
        .day-row-placeholder td {
            padding: 12px;
        }

        /* Стили кнопки повторной загрузки */
        .retry-load-days {
            cursor: pointer;
            color: #007bff;
        }

        /* Анимация загрузки */
        .loading-indicator {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
    </style>
{/capture}

<div class="tab-pane fade show active" id="detailed" role="tabpanel">
    <div class="overflow-auto">
        <table class="table table-bordered table-hover">
            <thead>
            <!-- Первая строка: Статусы -->
            <tr class="bg-light">
                <td colspan="{count($channels) + 3}" class="border"></td>
                {foreach $statuses as $status}
                    <td colspan="{count($mainSubjects) * (count($channels) + 2)}" class="text-center font-weight-bold border">
                        {$status->name}
                    </td>
                {/foreach}
            </tr>

            <!-- Вторая строка: Темы под каждым статусом -->
            <tr class="bg-light">
                <td colspan="{count($channels) + 3}" class="border"></td>
                {foreach $statuses as $status}
                    {foreach $mainSubjects as $subjectId => $subject}
                        <td colspan="{count($channels) + 2}" class="text-center border">
                            {$subject}
                        </td>
                    {/foreach}
                {/foreach}
                <!-- Колонки для дочерних тем -->
                {foreach $childSubjects as $subjectId => $subject}
                    <td colspan="{count($channels)}" class="text-center border">
                        {$subject}
                    </td>
                {/foreach}
            </tr>

            <!-- Третья строка: Каналы -->
            <tr class="bg-light">
                <td colspan="2" class="font-weight-bold border">Месяц/День</td>
                {foreach $channels as $channel}
                    <td class="text-center small border">
                        {$channel->name}
                    </td>
                {/foreach}
                <td class="font-weight-bold text-center border">Итого</td>
                {foreach $statuses as $status}
                    {foreach $mainSubjects as $subjectId => $subject}
                        {foreach $channels as $channel}
                            <td class="text-center small border">
                                {$channel->name}
                            </td>
                        {/foreach}
                        <td class="font-weight-bold text-center border">Итого</td>
                        <td class="font-weight-bold text-center border">%</td>
                    {/foreach}
                {/foreach}
                {foreach $childSubjects as $subjectId => $subject}
                    {foreach $channels as $channel}
                        <td class="text-center small border">
                            {$channel->name}
                        </td>
                    {/foreach}
                {/foreach}
            </tr>
            </thead>
            <tbody>
            {foreach $detailedStats.parentData as $month => $data}
                <!-- Строка месяца (кликабельная) -->
                <tr class="month-row" data-month="{$month}">
                    <td colspan="2" class="font-weight-bold border-right text-nowrap">
                        <i class="fa fa-plus-circle toggle-icon" data-month="{$month}"></i>
                        {$month}
                    </td>
                    <!-- Итоги по каналам -->
                    {foreach $channels as $channel}
                        <td class="text-center {if isset($data.total[$channel->id]) && $data.total[$channel->id] > 0}font-weight-bold{/if}">
                            {if isset($data.total[$channel->id]) && $data.total[$channel->id] > 0}
                                {$data.total[$channel->id]}
                            {else}
                                <span class="text-muted">0</span>
                            {/if}
                        </td>
                    {/foreach}

                    <td class="font-weight-bold text-center border-right">{$data.total_tickets}</td>

                    {foreach $statuses as $status}
                        {foreach $mainSubjects as $subjectId => $subject}
                            {foreach $channels as $channel}
                                <td class="text-center {if isset($data[$status->id][$subjectId][$channel->id]) && $data[$status->id][$subjectId][$channel->id] > 0}font-weight-bold{/if}">
                                    {if isset($data[$status->id][$subjectId][$channel->id]) && $data[$status->id][$subjectId][$channel->id] > 0}
                                        {$data[$status->id][$subjectId][$channel->id]}
                                    {else}
                                        <span class="text-muted">0</span>
                                    {/if}
                                </td>
                            {/foreach}

                            <!-- Итоги по теме -->
                            <td class="font-weight-bold text-center">
                                {if isset($data[$status->id][$subjectId]['total']) && $data[$status->id][$subjectId]['total'] > 0}
                                    {$data[$status->id][$subjectId]['total']}
                                {else}
                                    <span class="text-muted">0</span>
                                {/if}
                            </td>
                            <td class="font-weight-bold text-center border-right">
                                {if isset($data[$status->id][$subjectId]['percentage']) && $data[$status->id][$subjectId]['percentage'] > 0}
                                    {$data[$status->id][$subjectId]['percentage']|round:2}%
                                {else}
                                    <span class="text-muted">0%</span>
                                {/if}
                            </td>
                        {/foreach}
                    {/foreach}

                    <!-- Дочерние темы -->
                    {foreach $childSubjects as $subjectId => $subject}
                        {foreach $channels as $channel}
                            <td class="text-center {if isset($detailedStats.childData[$month][$subjectId][$channel->id]) && $detailedStats.childData[$month][$subjectId][$channel->id] > 0}font-weight-bold{/if}">
                                {if isset($detailedStats.childData[$month][$subjectId][$channel->id]) && $detailedStats.childData[$month][$subjectId][$channel->id] > 0}
                                    {$detailedStats.childData[$month][$subjectId][$channel->id]}
                                {else}
                                    <span class="text-muted">0</span>
                                {/if}
                            </td>
                        {/foreach}
                    {/foreach}
                </tr>

                <!-- Плейсхолдер для дневных строк -->
                <tr class="day-row-placeholder" data-month="{$month}" style="display: none;">
                    <td colspan="100%" class="text-center"></td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>