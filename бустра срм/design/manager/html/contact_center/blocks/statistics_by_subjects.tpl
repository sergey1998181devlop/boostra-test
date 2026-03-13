<div class="tab-pane fade" id="statistics-by-subject-tab" role="tabpanel">
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
            <tr>
                <th rowspan="2" style="min-width: 100px;" class="align-middle text-center month-header-col">Месяц</th>
                <th rowspan="2" style="min-width: 200px;" class="align-middle text-center type-header-col">Тип</th>
                <th rowspan="2" style="min-width: 100px;" class="align-middle text-center">Кол-во заявок</th>
                {foreach $childSubjects as $subjectId => $subjectName}
                    <th colspan="2" class="align-middle text-center">{$subjectName|escape}</th>
                {/foreach}
            </tr>
            <tr>
                {foreach $childSubjects as $subjectId => $subjectName}
                    <th style="min-width: 60px;" class="align-middle text-center">шт</th>
                    <th style="min-width: 60px;" class="align-middle text-center">%</th>
                {/foreach}
            </tr>
            </thead>
            <tbody>
            {foreach $subjectStatistics.data as $month => $monthData}
                {assign var="monthRowspan" value=(count($monthData) + 1)}
                {assign var="isFirstTypeInMonth" value=true}

                {* Расчеты итогов остаются такими же *}
                {assign var="monthTotalRequests" value=0}
                {assign var="monthSubjectCounts" value=[]}
                {foreach $monthData as $typeKey => $typeStats}
                    {$monthTotalRequests = $monthTotalRequests + $typeStats.total}
                    {foreach $typeStats.subjects as $subjectId => $counts}
                        {if !isset($monthSubjectCounts[$subjectId])}{$monthSubjectCounts[$subjectId] = 0}{/if}
                        {$monthSubjectCounts[$subjectId] = $monthSubjectCounts[$subjectId] + $counts.count}
                    {/foreach}
                {/foreach}

                {foreach $monthData as $typeKey => $typeStats}
                    <tr>
                        {if $isFirstTypeInMonth}
                            <td rowspan="{$monthRowspan}" class="month-data-cell align-middle text-center font-weight-bold">
                                {$month|date_format:'%Y-%m'}
                            </td>
                            {assign var="isFirstTypeInMonth" value=false}
                        {/if}
                        <td class="type-data-cell align-middle text-left">{$typeStats.name|escape}</td>
                        <td class="data-cell count-main align-middle text-center font-weight-bold">{$typeStats.total}</td>

                        {foreach $childSubjects as $subjectId => $subjectName}
                            {assign var="count" value=$typeStats.subjects[$subjectId].count|default:0}
                            {assign var="percentage" value=$typeStats.subjects[$subjectId].percentage|default:0}

                            <td class="data-cell {if $count > 0}count-cell{/if} align-middle text-center">
                                {if $count > 0}{$count}{else}<span class="text-muted-custom">0</span>{/if}
                            </td>
                            <td class="data-cell {if $percentage > 0}percent-cell{/if} align-middle text-center font-weight-bold">
                                {if $percentage > 0}{$percentage|string_format:"%.1f"}%{else}<span class="text-muted-custom">0%</span>{/if}
                            </td>
                        {/foreach}
                    </tr>
                {/foreach}

                <tr class="total-row">
                    <td class="type-data-cell total-type-cell align-middle text-left font-weight-bold">ИТОГО</td>
                    <td class="data-cell count-main total-requests-cell align-middle text-center font-weight-bold">{$monthTotalRequests}</td>

                    {foreach $childSubjects as $subjectId => $subjectName}
                        {assign var="subjectTotalCount" value=$monthSubjectCounts[$subjectId]|default:0}
                        {assign var="subjectTotalPercentage" value=0}
                        {if $monthTotalRequests > 0 && $subjectTotalCount > 0}
                            {$subjectTotalPercentage = ($subjectTotalCount / $monthTotalRequests * 100)}
                        {/if}
                        <td class="data-cell {if $subjectTotalCount > 0}count-cell{/if} align-middle text-center font-weight-bold">
                            {if $subjectTotalCount > 0}{$subjectTotalCount}{else}<span class="text-muted-custom">0</span>{/if}
                        </td>
                        <td class="data-cell {if $subjectTotalPercentage > 0}percent-cell{/if} align-middle text-center font-weight-bold">
                            {if $subjectTotalPercentage > 0}{$subjectTotalPercentage|string_format:"%.1f"}%{else}<span class="text-muted-custom">0%</span>{/if}
                        </td>
                    {/foreach}
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>