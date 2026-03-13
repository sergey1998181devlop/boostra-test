{$meta_title = "Статистика менеджеров" scope=parent}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fas fa-chart-bar mr-2"></i>Статистика менеджеров
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="statistics-table-wrapper">
                            <table class="table table-bordered table-hover stats-table">
                                <thead>
                                <tr>
                                    <th rowspan="2" colspan="2" class="align-middle text-center">Дата</th>
                                    {foreach $managersWithTickets as $manager}
                                        <th colspan="3" class="text-center manager-header">{$manager->name}</th>
                                    {/foreach}
                                </tr>
                                <tr class="sub-header">
                                    {for $x=0, $y=count($managersWithTickets); $x<$y; $x++}
                                        <th class="text-center text-nowrap column-resolved">Урегулировано</th>
                                        <th class="text-center text-nowrap column-unresolved">Не урегулировано</th>
                                        <th class="text-center text-nowrap column-total">Всего</th>
                                    {/for}
                                </tr>
                                </thead>
                                <tbody>
                                    {foreach $statistics as $month => $statistic}
                                    <tr class="total-row">
                                        <td colspan="2" data-month="{$month}" class="text-nowrap toggle-month">
                                            <i class="fa fa-plus-circle toggle-icon mr-2" data-month="{$month}"></i>
                                            {$month}
                                        </td>

                                        {foreach $managersWithTickets as $manager}
                                            <td class="text-center">{($statistic.managers[$manager->id].resolved) ? $statistic.managers[$manager->id].resolved : '0'}</td>
                                            <td class="text-center">{($statistic.managers[$manager->id].unresolved) ? $statistic.managers[$manager->id].unresolved : '0'}</td>
                                            <td class="text-center">{($statistic.managers[$manager->id].total) ? $statistic.managers[$manager->id].total : '0'}</td>
                                        {/foreach}
                                    </tr>

                                    <tr class="day-row-placeholder" data-month="{$month}" style="display: none;">
                                        <td colspan="100%" class="text-center"></td>
                                    </tr>
                                    {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </div>
</div>

{capture name='page_scripts'}
    <script src="/design/{$settings->theme}/js/ticket_manager_statistics.js"></script>
{/capture}

{capture name='page_styles'}
    <style>
        .statistics-table-wrapper {
            overflow-x: auto;
        }

        .stats-table {
            font-size: 14px;
            border-collapse: collapse;
        }

        .stats-table thead {
            background-color: #2d3338;
        }

        .stats-table th {
            border: 1px solid #2d3338;
        }

        .stats-table th,
        .stats-table td {
            padding: 12px;
            vertical-align: middle;
        }

        .total-row {
            background-color: #2d3338;
            font-weight: bold;
        }

        .manager-header {
            background-color: #2d3338;
        }

        .column-resolved {
            background-color: #21897E;
        }

        .column-unresolved {
            background-color: #ED6A5A;
        }

        .column-total {
            background-color: #5a7ced;
        }

        /* Стили для группировки по месяцам */
        .month-group-row {
            background-color: #252529 !important;
            cursor: pointer;
        }

        .day-row {
            display: none;
        }
        
        .toggle-month:hover {
            cursor: pointer;
        }
    </style>
{/capture}