{$meta_title='Аналитика' scope=parent}

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/select2/dist/css/select2.min.css" rel="stylesheet"/>
    <style>
        @font-face {
            font-family: 'Font Awesome 5 Free';
            font-style: normal;
            font-weight: 400;
            src: url("/design/manager/scss/icons/font-awesome/webfonts/fa-regular-400.eot");
            src: url("/design/manager/scss/icons/font-awesome/webfonts/fa-regular-400.eot?#iefix") format("embedded-opentype"), url("/design/manager/scss/icons/font-awesome/webfonts/fa-regular-400.woff2") format("woff2"), url("/design/manager/scss/icons/font-awesome/webfonts/fa-regular-400.woff") format("woff"), url("/design/manager/scss/icons/font-awesome/webfonts/fa-regular-400.ttf") format("truetype"), url("/design/manager/scss/icons/font-awesome/webfonts/fa-regular-400.svg#fontawesome") format("svg");
        }

        th {
            border: 1px solid rgba(0, 0, 0, 0.4) !important;
        }

        thead tr:first-of-type {
            background-color: #1a1f27;
        }

        thead tr:nth-of-type(2) {
            background-color: #383f48;
        }

        .sortable::after {
            position: relative;
        }

        .sortable::after {
            font-family: 'Font Awesome 5 Free';
            content: '\f0dc';
            font-weight: 900;
            margin-left: 5px;
        }

        .asc::after {
            content: '\f0de';
            font-family: 'Font Awesome 5 Free';

        }

        .desc::after {
            content: '\f0dd';
            font-family: 'Font Awesome 5 Free';
        }

        th a, td a {
            color: inherit;
            font-weight: 500;
            text-decoration: none;
        }

        th a:hover, td a:hover {
            color: #ababab;
            text-decoration: none;
        }

        .scrollbar-top {
            overflow-x: auto;
            overflow-y: hidden;
        }
        .scrollbar-top .dummy {
            height: 10px;
        }
        .scrollbar-top::-webkit-scrollbar,
        .table-responsive::-webkit-scrollbar {
            height: 10px;
        }
        .scrollbar-top::-webkit-scrollbar-track,
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        .scrollbar-top::-webkit-scrollbar-thumb,
        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        .scrollbar-top::-webkit-scrollbar-thumb:hover,
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        .scrollbar-top,
        .table-responsive {
            scrollbar-width: thin;
            scrollbar-color: #888 #f1f1f1;
        }
    </style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-tdemecolor mb-0 mt-0">{$meta_title} <b>{$sla_statistic_quarter}</b></h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">{$meta_title}</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 d-flex align-self-center align-content-end justify-content-end">
                <form id="filter-form">
                    <h3 class="text-tdemecolor mb-0 mt-0">Квартал:</h3>
                    <select class="form-control" name="quarter">
                        {foreach $sla_quarters.quarters as $quarter}
                            <option value="{$quarter.id}" {if $quarter.selected === true} selected {/if}>{$quarter.name}</option>
                        {/foreach}
                    </select>

                    <select class="form-control" name="year">
                        {foreach $sla_quarters.years as $year}
                            <option value="{$year.value}" {if $year.selected === true} selected {/if}>{$year.value}</option>
                        {/foreach}
                    </select>
                    <button type="submit" class="btn btn-info">Отфильтровать</button>
                    <a href="#" class="btn btn-primary download-btn">
                        <i class="fas fa-file-excel"></i> Экспорт в Excel
                    </a>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="scrollbar-top" id="scrollbar-top">
                            <div class="dummy"></div>
                        </div>
                        <div class="table-responsive" id="table-container-sla-statistics">
                            <h2>Статистика SLA</h2>
                            <table id="priorities" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th scope="col" class="resizable" data-column="priority">
                                        <a href="#" class="sortable {if $sort == 'priority'}asc{elseif $sort == '-priority'}desc{/if}" data-sort="{($sort=='priority') ? '-priority' : 'priority'}">
                                            Приоритет
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="average-treatment">
                                        <a href="#" class="sortable {if $sort == 'average-treatment'}asc{elseif $sort == '-average-treatment'}desc{/if}" data-sort="{($sort=='average-treatment') ? '-average-treatment' : 'average-treatment'}">
                                            Среднее время реакции на обращение (мин.)
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="average-decision">
                                        <a href="#" class="sortable {if $sort == 'average-decision'}asc{elseif $sort == '-average-decision'}desc{/if}" data-sort="{($sort=='average-decision') ? '-average-decision' : 'average-decision'}">
                                            Среднее время решения обращения (час.)
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="percent-sla-reaction">
                                        <a href="#" class="sortable {if $sort == 'percent-sla-reaction'}asc{elseif $sort == '-percent-sla-reaction'}desc{/if}" data-sort="{($sort=='percent-sla-reaction') ? '-percent-sla-reaction' : 'percent-sla-reaction'}">
                                            % тикетов в SLA (по реакции)
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="percent-sla-resolution">
                                        <a href="#" class="sortable {if $sort == 'percent-sla-resolution'}asc{elseif $sort == '-percent-sla-resolution'}desc{/if}" data-sort="{($sort=='percent-sla-resolution') ? '-percent-sla-resolution' : 'percent-sla-resolution'}">
                                            % тикетов в SLA (по решению)
                                        </a>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $sla_statistic as $row}
                                    <tr>
                                        <td><span class="badge badge-pill"
                                                  style="background-color: {$row.priority_color}"
                                                  title="{$row.priority_name}">{$row.priority_name}</span></td>
                                        <td>{$row.average_reaction}</td>
                                        <td>{$row.average_resolve}</td>
                                        <td>{$row.percent_reaction}</td>
                                        <td>{$row.percent_resolve}</td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="scrollbar-top" id="scrollbar-top">
                            <div class="dummy"></div>
                        </div>
                        <div class="table-responsive" id="table-container-tickets-statistics">
                            <h2>Общая статистика по тикетам</h2>
                            <table id="tickets" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th scope="col" class="resizable" data-column="statistic-month">
                                        <a href="#" class="sortable {if $sort == 'priority'}asc{elseif $sort == '-priority'}desc{/if}" data-sort="{($sort=='priority') ? '-priority' : 'priority'}">
                                            Месяц
                                        </a>
                                    </th>

                                    {foreach $statuses as $status}
                                        <th scope="col" class="resizable" data-column="{$status.code}">
                                            <a href="#" class="sortable {if $sort == "{$status.code}"}asc{elseif $sort == "-{$status.code}"}desc{/if}" data-sort="{($sort=="{$status.code}") ? "-{$status.code}" : "{$status.code}"}">
                                                {$status.name}
                                            </a>
                                        </th>
                                    {/foreach}
                                    <th scope="col" class="resizable" data-column="statistic-average-reaction">
                                        <a href="#" class="sortable {if $sort == 'statistic-average-reaction'}asc{elseif $sort == '-statistic-average-reaction'}desc{/if}" data-sort="{($sort=='statistic-average-reaction') ? '-statistic-average-reaction' : 'statistic-average-reaction'}">
                                            Среднее время взятия в работу (мин.)
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="statistic-average-decision">
                                        <a href="#" class="sortable {if $sort == 'statistic-average-decision'}asc{elseif $sort == '-statistic-average-decision'}desc{/if}" data-sort="{($sort=='statistic-average-decision') ? '-statistic-average-decision' : 'statistic-average-decision'}">
                                            Среднее время закрытия тикета (час.)
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="statistic-average-reaction-percent">
                                        <a href="#" class="sortable {if $sort == 'statistic-average-reaction-percent'}asc{elseif $sort == '-statistic-average-reaction-percent'}desc{/if}" data-sort="{($sort=='statistic-average-reaction-percent') ? '-statistic-average-reaction-percent' : 'statistic-average-reaction-percent'}">
                                            Процент тикетов в SLA по реакции
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="statistic-average-resolution-percent">
                                        <a href="#" class="sortable {if $sort == 'statistic-average-resolution-percent'}asc{elseif $sort == '-statistic-average-resolution-percent'}desc{/if}" data-sort="{($sort=='statistic-average-resolution-percent') ? '-statistic-average-resolution-percent' : 'statistic-average-resolution-percent'}">
                                            Процент тикетов в SLA по решению
                                        </a>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $tickets_statistic as $row}
                                    <tr>
                                        <td>{$row.month}</td>
                                        {foreach $statuses as $status}
                                            <td>{$row[$status.code]}</td>
                                        {/foreach}
                                        <td>
                                            <span>
                                                {$row.average_reaction.minutes}
                                            </span>
                                        </td>
                                        <td>
                                            <span>
                                                {$row.average_resolve.hours}
                                            </span>
                                        </td>
                                        <td>
                                            <span style="color:{if $sla_min_reaction_percent > $row.average_reaction.percent}red{else}green{/if}">
                                                {$row.average_reaction.percent}
                                            </span>
                                        </td>
                                        <td>
                                            <span style="color:{if $sla_min_resolution_percent > $row.average_resolve.percent}red{else}green{/if}">
                                                {$row.average_resolve.percent}
                                            </span>
                                        </td>
                                    </tr>
                                {/foreach}
                                <tr>
                                    <td>Всего</td>
                                        {foreach $statuses as $status}
                                            <td>{$tickets_statistic_total.statuses[$status.code]}</td>
                                        {/foreach}
                                    <td>
                                        <span>
                                            {$tickets_statistic_total.average_reaction.minutes}
                                        </span>
                                    </td>
                                    <td>
                                        <span>
                                            {$tickets_statistic_total.average_resolve.hours}
                                        </span>
                                    </td>
                                    <td>
                                        <span style="color:{if $sla_min_reaction_percent > $tickets_statistic_total.average_reaction.percent}red{else}green{/if}">
                                            {$tickets_statistic_total.average_reaction.percent}
                                        </span>
                                    </td>
                                    <td>
                                        <span style="color:{if $sla_min_resolution_percent > $tickets_statistic_total.average_resolve.percent}red{else}green{/if}">
                                            {$tickets_statistic_total.average_resolve.percent}
                                        </span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="scrollbar-top" id="scrollbar-top">
                            <div class="dummy"></div>
                        </div>
                        <div class="table-responsive" id="table-container-shared-tickets-statistics">
                            <h2>Переданные тикеты</h2>
                            <table id="shared-tickets" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th scope="col" class="resizable" data-column="direction">
                                        <a href="#" class="sortable {if $sort == 'direction'}asc{elseif $sort == '-direction'}desc{/if}" data-sort="{($sort=='direction') ? '-direction' : 'direction'}">
                                            Команда
                                        </a>
                                    </th>

                                    <th scope="col" class="resizable" data-column="shared-tickets-count">
                                        <a href="#" class="sortable {if $sort == 'shared-tickets-count'}asc{elseif $sort == '-shared-tickets-count'}desc{/if}" data-sort="{($sort=='shared-tickets-count') ? '-shared-tickets-count' : 'shared-tickets-count'}">
                                            Передано тикетов
                                        </a>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $shared_tickets as $row}
                                    <tr>
                                        <td>{$row.direction}</td>
                                        <td>{$row.count}</td>
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
    {include file='footer.tpl'}
</div>


{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/table-manager.js"></script>
    <script src="design/manager/assets/plugins/select2/dist/js/select2.full.min.js"></script>

    <script>
        (function() {
            const topScroll = document.getElementById('scrollbar-top');
            const tableContainer = document.getElementById('table-container');

            topScroll.addEventListener('scroll', function() {
                tableContainer.scrollLeft = topScroll.scrollLeft;
            });

            tableContainer.addEventListener('scroll', function() {
                topScroll.scrollLeft = tableContainer.scrollLeft;
            });

            const table = document.getElementById('tickets');
            const dummy = topScroll.querySelector('.dummy');
            dummy.style.width = table.scrollWidth + 'px';
        })();
    </script>
{/capture}
