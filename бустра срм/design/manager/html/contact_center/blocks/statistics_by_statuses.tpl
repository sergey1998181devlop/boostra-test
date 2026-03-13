<div class="tab-pane fade" id="statistics-by-status-tab" role="tabpanel">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
            <tr>
                <th rowspan="3" class="align-middle">Дата</th>
                <th rowspan="3" class="align-middle">Тема</th>
                <th rowspan="2" colspan="2" class="text-center">Всего</th>

                {assign var="firstMonth" value=$statusStatistics|@reset}

                {foreach from=$firstMonth.total.by_status key=statusId item=status}
                    {if $statusId == 2 || $statusId == 4}
                        <th colspan="6" class="text-center" style="background-color: {$status.color}">
                            {$status.name}
                        </th>
                    {else}
                        <th colspan="4" class="text-center" style="background-color: {$status.color}">
                            {$status.name}
                        </th>
                    {/if}
                {/foreach}
            </tr>
            <tr>
                {foreach from=$firstMonth.total.by_status key=statusId item=status}
                    <th colspan="2" class="text-center">Кол-во</th>
                    <th colspan="2" class="text-center">%</th>
                    {if $statusId == 2 || $statusId == 4}
                        <th colspan="2" class="text-center">Обратная связь</th>
                    {/if}
                {/foreach}
            </tr>
            <tr>
                <th class="text-center">Всего</th>
                <th class="text-center">Повт.</th>
                {foreach from=$firstMonth.total.by_status key=statusId item=status}
                    <th class="text-center">Всего</th>
                    <th class="text-center">Повт.</th>
                    <th class="text-center">Всего</th>
                    <th class="text-center">Повт.</th>
                    {if $statusId == 2 || $statusId == 4}
                        <th class="text-center">Получена</th>
                        <th class="text-center">Не получена</th>
                    {/if}
                {/foreach}
            </tr>
            </thead>
            <tbody>
            {foreach $statusStatistics as $month => $monthData}
                {* Строка месяца - кликабельная для раскрытия дней *}
                <tr class="month-row font-weight-bold bg-light" style="cursor: pointer;" data-month="{$month}">
                    <td>
                        <i class="fa fa-plus-circle toggle-icon"></i> {$month}
                    </td>
                    <td>Всего</td>
                    <td class="text-center">{$monthData.total.count|number_format:0:'.':' '}</td>
                    <td class="text-center">{$monthData.repeats.total|number_format:0:'.':' '}</td>
                    {foreach $monthData.total.by_status as $statusId => $status}
                        <td class="text-center">{$status.count|number_format:0:'.':' '}</td>
                        <td class="text-center">{$status.repeat_count|number_format:0:'.':' '}</td>
                        <td class="text-center">
                            {if $monthData.total.count > 0}
                                {($status.count / $monthData.total.count * 100)|round:2}%
                            {else}0%{/if}
                        </td>
                        <td class="text-center">
                            {if $status.count > 0}
                                {($status.repeat_count / $status.count * 100)|round:2}%
                            {else}0%{/if}
                        </td>
                        {if $statusId == 2 || $statusId == 4}
                            <td class="text-center">{$status.feedback_received|number_format:0:'.':' '}</td>
                            <td class="text-center">{$status.feedback_not_received|number_format:0:'.':' '}</td>
                        {/if}
                    {/foreach}
                </tr>

                {* Статистика по темам месяца *}
                {assign var="subjectCount" value=$monthData.subjects|@count}
                {assign var="subjectIndex" value=0}
                {foreach $monthData.subjects as $subject}
                    <tr class="month-row" data-month="{$month}">
                        {if $subjectIndex == 0}
                            <td rowspan="{$subjectCount}" class="align-middle">Всего</td>
                        {/if}
                        <td>{$subject.name}</td>
                        <td class="text-center">{$subject.total|number_format:0:'.':' '}</td>
                        <td class="text-center">{$subject.repeat_count|number_format:0:'.':' '}</td>
                        {foreach $subject.by_status as $statusId => $status}
                            <td class="text-center" {if $status.count > 0}style="background-color: {$status.color}20"{/if}>{$status.count|number_format:0:'.':' '}</td>
                            <td class="text-center" {if $status.repeat_count > 0}style="background-color: {$status.color}20"{/if}>{$status.repeat_count|number_format:0:'.':' '}</td>
                            <td class="text-center" {if $status.count > 0}style="background-color: {$status.color}20"{/if}>
                                {if $subject.total > 0}{($status.count / $subject.total * 100)|round:2}%{else}0%{/if}
                            </td>
                            <td class="text-center" {if $status.repeat_count > 0}style="background-color: {$status.color}20"{/if}>
                                {if $status.count > 0}{($status.repeat_count / $status.count * 100)|round:2}%{else}0%{/if}
                            </td>
                            {if $statusId == 2 || $statusId == 4}
                                <td class="text-center" {if $status.feedback_received > 0}style="background-color: {$status.color}20"{/if}>{$status.feedback_received|number_format:0:'.':' '}</td>
                                <td class="text-center" {if $status.feedback_not_received > 0}style="background-color: {$status.color}20"{/if}>{$status.feedback_not_received|number_format:0:'.':' '}</td>
                            {/if}
                        {/foreach}
                    </tr>
                    {assign var="subjectIndex" value=$subjectIndex+1}
                {/foreach}

                {* Статистика по дням (по умолчанию скрыта) *}
                {if isset($monthData.days)}
                    {foreach $monthData.days as $day => $dayData}
                        {* По дате выводим статистику тем по дням *}
                        {assign var="subjectCount" value=$dayData.subjects|@count}
                        {assign var="subjectIndex" value=0}
                        {foreach $dayData.subjects as $subject}
                            <tr class="daily-details-row" data-month="{$month}" style="display:none;">
                                {if $subjectIndex == 0}
                                    <td rowspan="{$subjectCount}" class="align-middle">{$day}</td>
                                {/if}
                                <td>{$subject.name}</td>
                                <td class="text-center">{$subject.total|number_format:0:'.':' '}</td>
                                <td class="text-center">{$subject.repeat_count|number_format:0:'.':' '}</td>
                                {foreach $subject.by_status as $statusId => $status}
                                    <td class="text-center" {if $status.count > 0}style="background-color: {$status.color}20"{/if}>{$status.count|number_format:0:'.':' '}</td>
                                    <td class="text-center" {if $status.repeat_count > 0}style="background-color: {$status.color}20"{/if}>{$status.repeat_count|number_format:0:'.':' '}</td>
                                    <td class="text-center" {if $status.count > 0}style="background-color: {$status.color}20"{/if}>
                                        {if $subject.total > 0}{($status.count / $subject.total * 100)|round:2}%{else}0%{/if}
                                    </td>
                                    <td class="text-center" {if $status.repeat_count > 0}style="background-color: {$status.color}20"{/if}>
                                        {if $status.count > 0}{($status.repeat_count / $status.count * 100)|round:2}%{else}0%{/if}
                                    </td>
                                    {if $statusId == 2 || $statusId == 4}
                                        <td class="text-center" {if $status.feedback_received > 0}style="background-color: {$status.color}20"{/if}>{$status.feedback_received|number_format:0:'.':' '}</td>
                                        <td class="text-center" {if $status.feedback_not_received > 0}style="background-color: {$status.color}20"{/if}>{$status.feedback_not_received|number_format:0:'.':' '}</td>
                                    {/if}
                                {/foreach}
                            </tr>
                            {assign var="subjectIndex" value=$subjectIndex+1}
                        {/foreach}
                    {/foreach}
                {/if}
            {/foreach}
            </tbody>
        </table>
    </div>
</div>