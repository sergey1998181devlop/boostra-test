{$meta_title='SLA настройки' scope=parent}

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

        .overdue-ticket {
            border-left: 4px solid #ED6A5A;
        }

        .has-comments-after-closing {
            border-left: 4px solid #01c0c8;
        }
    </style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-tdemecolor mb-0 mt-0"><i class="mdi mdi-settings mr-2"></i>{$meta_title}</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item">Тех. поддержка</li>
                    <li class="breadcrumb-item active">{$meta_title}</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 d-flex align-self-center align-content-end justify-content-end">
                <form id="filter-form">
                    {*                    <button type="submit" class="btn btn-info">Отфильтровать</button>*}
                    <a href="technical-support/sla/create" class="btn hidden-sm-down btn-success js-open-add-modal mr-2">
                        <i class="mdi mdi-plus-circle"></i> Добавить
                    </a>
{*                    <a href="#" class="btn btn-primary download-btn">*}
{*                        <i class="fas fa-file-excel"></i> Экспорт в Excel*}
{*                    </a>*}
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
                            <h2>Настройки SLA</h2>
                            <h3>{$test}</h3>
                            <table id="tickets" class="table table-bordered table-striped">
                                <thead>
                                <tr>
                                    <th scope="col" class="resizable" data-column="priority">
                                        <a href="#" class="sortable {if $sort == 'priority'}asc{elseif $sort == '-priority'}desc{/if}" data-sort="{($sort=='priority') ? '-priority' : 'priority'}">
                                            Квартал
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="priority">
                                        <a href="#" class="sortable {if $sort == 'priority'}asc{elseif $sort == '-priority'}desc{/if}" data-sort="{($sort=='priority') ? '-priority' : 'priority'}">
                                            № квартала
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="priority">
                                        <a href="#" class="sortable {if $sort == 'priority'}asc{elseif $sort == '-priority'}desc{/if}" data-sort="{($sort=='priority') ? '-priority' : 'priority'}">
                                            Год
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="average-treatment">
                                        <a href="#" class="sortable {if $sort == 'average-treatment'}asc{elseif $sort == '-average-treatment'}desc{/if}" data-sort="{($sort=='average-treatment') ? '-average-treatment' : 'average-treatment'}">
                                            Приоритет
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="average-decision">
                                        <a href="#" class="sortable {if $sort == 'average-decision'}asc{elseif $sort == '-average-decision'}desc{/if}" data-sort="{($sort=='average-decision') ? '-average-decision' : 'average-decision'}">
                                            Лимит по времени реакции (мин.)
                                        </a>
                                    </th>
                                    <th scope="col" class="percent-sla" data-column="percent-sla">
                                        <a href="#" class="sortable {if $sort == 'percent-sla'}asc{elseif $sort == '-percent-sla'}desc{/if}" data-sort="{($sort=='percent-sla') ? '-percent-sla' : 'percent-sla'}">
                                            Плановый % попадания в SLA для реакции
                                        </a>
                                    </th>
                                    <th scope="col" class="resizable" data-column="average-decision">
                                        <a href="#" class="sortable {if $sort == 'average-decision'}asc{elseif $sort == '-average-decision'}desc{/if}" data-sort="{($sort=='average-decision') ? '-average-decision' : 'average-decision'}">
                                            Лимит по времени решения (мин.)
                                        </a>
                                    </th>
                                    <th scope="col" class="percent-sla" data-column="percent-sla">
                                        <a href="#" class="sortable {if $sort == 'percent-sla'}asc{elseif $sort == '-percent-sla'}desc{/if}" data-sort="{($sort=='percent-sla') ? '-percent-sla' : 'percent-sla'}">
                                            Плановый % попадания в SLA для решения
                                        </a>
                                    </th>
                                </tr>
                                <tr id="filter-row">
                                    <th>
                                        <select name="priority_id" class="form-control">
                                            <option value="">Выберите квартал</option>
                                            {foreach $priorities as $priority}
                                                <option value="{$priority->id}" {if $priority->id == $filters['priority_id']}selected{/if}>{$priority->name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </th>
                                    <th>
                                        <input disabled type="text" name="average-decision" value="{$filters['average-decision']}"
                                               class="form-control input-sm">
                                    </th>
                                    <th>
                                        <input disabled type="text" name="average-decision" value="{$filters['average-decision']}"
                                               class="form-control input-sm">
                                    </th>
                                    <th>
                                        <select name="priority_id" class="form-control">
                                            <option value="">Выберите приоритет</option>
                                            {foreach $priorities as $priority}
                                                <option value="{$priority->id}" {if $priority->id == $filters['priority_id']}selected{/if}>{$priority->name|escape}</option>
                                            {/foreach}
                                        </select>
                                    </th>
                                    <th>
                                        <input disabled type="text" name="average-treatment" value="{$filters['average-treatment']}"
                                               class="form-control input-sm">
                                    </th>
                                    <th>
                                        <input disabled type="text" name="average-decision" value="{$filters['average-decision']}"
                                               class="form-control input-sm">
                                    </th>
                                    <th>
                                        <input disabled type="text" name="percent-sla" value="{$filters['percent-sla']}"
                                               class="form-control input-sm">
                                    </th>
                                    <th>
                                        <input disabled type="text" name="percent-sla" value="{$filters['percent-sla']}"
                                               class="form-control input-sm">
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $sla as $row}
                                    <tr>
                                        <td>{$row.name}</td>
                                        <td>{$row.number}</td>
                                        <td>{$row.year}</td>
                                        <td><span class="badge badge-pill"
                                                  style="background-color: {$row.priority.color}"
                                                  title="{$row.priority.name}">
                                            {$row.priority.name}
                                        </span></td>
                                        <td>{$row.reactionMinutes}</td>
                                        <td>{$row.reactionPercent}</td>
                                        <td>{$row.resolutionMinutes}</td>
                                        <td>{$row.resolutionPercent}</td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>

                        {include file='html_blocks/table_pagination.tpl'}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>


{capture name='page_scripts'}
    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    <script src="design/manager/assets/plugins/select2/dist/js/select2.full.min.js"></script>
{/capture}
