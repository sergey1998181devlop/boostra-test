{$meta_title='Отчёт по отказам' scope=parent}

{capture name='page_styles'}

    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
{/capture}

<style>
    tr.small td {
        padding: 0.25rem;
    }
</style>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Отчёт по отказам</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отчёт по отказам</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Отчет за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}
                            {if $filter_source}
                                {foreach $filter_source as $fs}
                                    {$fs}{if !$fs@last}, {/if}
                                {/foreach}
                            {/if}
                        </h4>
                        <form id="report_form">
                            <div class="row">
                                <div class="col-6 col-md-2">
                                    <div class="input-group mb-3">
                                        <input type="text" name="date_range" class="form-control daterange" value="{if $from && $to}{$from}-{$to}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="mb-3">
                                        <div class="btn-group btn-block">
                                            <button class="btn btn-block btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="false" aria-expanded="true" data-flip="false">
                                                Источники
                                            </button>
                                            <div class="dropdown-menu p-2" >
                                                {foreach $sources as $source}
                                                    <div class="form-group">
                                                        <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                                            <input name="filter_utm_source[]" type="checkbox" class="custom-control-input" id="filter_source_{$source@index}" value="{$source}"  />
                                                            <label class="custom-control-label" for="filter_source_{$source@index}">
                                                                {$source}
                                                            </label>
                                                        </div>
                                                    </div>
                                                {/foreach}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="mb-3">
                                        <select class="form-control" name="filter_client">
                                            <option value="NK" {if $filter_client == 'NK'}selected{/if}>НК</option>
                                            <option value="PK_WITHOUT_AUTO_APPROVE" {if $filter_client == 'PK'}selected{/if}>ПК (без автозаявок)</option>
                                            <option value="PK_ONLY_AUTO_APPROVE" {if $filter_client == 'PK'}selected{/if}>ПК (только автозаявки)</option>
                                            <option value="PK_WITH_AUTO_APPROVE" {if $filter_client == 'PK'}selected{/if}>ПК (вместе с автозаявками)</option>
                                            <option value="ALL" {if $filter_client == 'ALL'}selected{/if}>НК + ПК</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <div class="mb-3">
                                        <select class="form-control" name="filter_group_by">
                                            <option value="" {if !$filter_group_by}selected{/if}>По дням</option>
                                            <option value="1" {if $filter_group_by}selected{/if}>Суммарно</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <div class="custom-control custom-checkbox">
                                        <input name="filter_stage_completed" checked type="checkbox" class="custom-control-input" id="filter_stage_completed" value="1"  />
                                        <label class="custom-control-label" for="filter_stage_completed">
                                            Как в листинге
                                        </label>
                                    </div>
                                    <span class="text-warning">(прошёл регистрацию)</span>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <div class="btn-group">
                                        <button type="button" onclick="loadData();" class="btn btn-info"><i class="ti-reload"></i> Сформировать</button>
                                        <button onclick="download();" type="button" class="btn btn-success"><i class="ti-save"></i> Выгрузить</button>
                                    </div>
                                </div>
                            </div>

                        </form>
                        <div id="result" class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <input type="hidden" value="{$results|@count}" name="total_rows" />
                                <thead>
                                    <tr>
                                        <th>Сегмент</th>
                                        <th>Причина</th>
                                        <th>Кол-во</th>
                                        <th>Конверсия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {if $results}
                                        {foreach $results as $key => $result}
                                            <tr class="text-warning bg-dark">
                                                {if !$filter_group_by}
                                                    <td style="vertical-align: middle; text-align: center;"
                                                        rowspan="{$result['total_items']}">{$key}</td>
                                                {/if}
                                                <td>Поступило заявок всего</td>
                                                <td></td>
                                                <td>{$total_orders[$key]['total_orders']|intval}</td>
                                                <td></td>
                                            </tr>
                                            {foreach $result as $key_array => $result_array}
                                                {foreach $result_array['items'] as $row}
                                                    <tr>
                                                        {if $row@first}
                                                            <td style="vertical-align: middle;" rowspan="{$result_array['items']|@count + $result_array['total']['count']|@count}">{$result_array['category']}</td>
                                                        {/if}
                                                        <td>{$row['admin_name']}</td>
                                                        <td>{$row['total']}</td>
                                                        {if $row@first && !$result_array['total']}
                                                            <td class="text-success" style="vertical-align: bottom;" rowspan="{$result_array['items']|@count + $result_array['total']['count']|@count}">
                                                                {$result_array['total']['cv']|round:1}
                                                            </td>
                                                        {else}
                                                            <td>{$row['cv']|round:2}%</td>
                                                        {/if}
                                                    </tr>
                                                {/foreach}

                                                {if $result_array['total']}
                                                    <tr {if $result_array['items']}class="text-success"{/if}>
                                                        {if !$result_array['items']}
                                                            <td style="vertical-align: middle;">{$result_array['category']}</td>
                                                        {/if}
                                                        <td {if !$result_array['items']}class="text-success"{/if}>Итого</td>
                                                        <td {if !$result_array['items']}class="text-success"{/if}>{$result_array['total']['count']}</td>
                                                        <td style="vertical-align: bottom;" class="text-success">{$result_array['total']['cv']|round:2}%</td>
                                                    </tr>
                                                {/if}
                                            {/foreach}
                                        {/foreach}
                                    {else}
                                        <tr>
                                            <td colspan="4">
                                                <h3 class="text-danger">Данные не найдены</h3>
                                            </td>
                                        </tr>
                                    {/if}
                                </tbody>
                            </table>
                        </div>
                        <strong class=""></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {include file='footer.tpl'}
</div>

{capture name='page_scripts'}

    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    <script src="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script src="design/manager/js/tinysort.min.js"></script>

    <script>
        $(function(){
            $('.daterange').daterangepicker({
                autoApply: true,
                locale: {
                    format: 'YYYY.MM.DD'
                },
                default:''
            });
        });

        function loadData(){
            $('.preloader').show();
            let filter_data = $('#report_form').serialize();
            $("#result").load('{$smarty.server.REQUEST_URI}?ajax=1&' + filter_data + ' #result table', function (response, status, xhr) {
                $('.preloader').hide();
                if (status == "error") {
                    alert('Произошла ошибка сервера подробности в консоли');
                    console.error('error load text: ' + xhr.status + " " + xhr.statusText);
                }
                initSortTable();
            });
        }

        function download() {
            let filter_data = $('#report_form').serialize();
            window.open(
                '{$smarty.server.REQUEST_URI}?action=download&' + filter_data,
                '_blank' // <- This is what makes it open in a new window.
            );
        }

        function initSortTable() {
            let table = $(document).find('#result table')[0],
                tableHead = table.querySelector('thead'),
                tableHeaders = tableHead.querySelectorAll('th'),
                tableBody = table.querySelector('tbody');

            tableHead.addEventListener('click', function (e) {
                let tableHeader = e.target,
                    tableHeaderIndex,
                    isAscending,
                    order;

                if (tableHeader.hasAttribute('data-sort')) {
                    while (tableHeader.nodeName !== 'TH') {
                        tableHeader = tableHeader.parentNode;
                    }
                    tableHeaderIndex = Array.prototype.indexOf.call(tableHeaders, tableHeader);
                    isAscending = tableHeader.getAttribute('data-order') === 'asc';
                    order = isAscending ? 'desc' : 'asc';
                    tableHeader.setAttribute('data-order', order);

                    let total_rows = $('[name="total_rows"]').val(),
                        view_all = parseInt($('[name="view_all"]').val()),
                        td_index = view_all === 0 ? tableHeaderIndex : tableHeaderIndex + 1;

                    for (let index = 1; index <= total_rows; index++) {

                        let row_span_td = $('tr[data-index="' + index + '"] td[rowspan]');

                        if (view_all === 0) {
                            $('tr[data-index="' + index + '"] td[rowspan]').remove();
                        }

                        tinysort(tableBody.querySelectorAll('tr[data-index="' + index + '"]'), {
                                selector: 'td:not([rowspan]):nth-child(' + (td_index) + ')',
                                order: order,
                            }
                        );

                        if (view_all === 0) {
                            $('tr[data-index="' + index + '"]').first().prepend(row_span_td);
                        }
                    }
                }
            });
        }
    </script>
{/capture}
