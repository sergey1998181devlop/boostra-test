{$meta_title='Общий отчет' scope=parent}

{capture name='page_styles'}
    
    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i> 
                    <span>Отчёт кр покупка из лк</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отчёт кр покупка из лк</li>
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
                                        <select class="form-control" name="filter_client">
                                            <option value="" {if !$filter_client}selected{/if}>Все клиенты</option>
                                            <option value="unique" {if $filter_client == 'nk'}selected{/if}>Уникальные</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="mb-3">
                                        <select class="form-control" name="filter_offer">
                                            <option value="" {if !$filter_client}selected{/if}>Все офферы</option>
                                            <option value="0" {if $filter_client == 'nk'}selected{/if}>Отказники</option>
                                            <option value="1" {if $filter_client == 'nk'}selected{/if}>Апрувники</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="mb-3">
                                        <select class="form-control" name="filter_group_by">
                                            <option value="day" {if !$filter_client}selected{/if}>По дням</option>
                                            <option value="month" {if $filter_client == 'nk'}selected{/if}>По месяцам</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <button type="button" onclick="loadData();" class="btn btn-info">Сформировать</button>
                                </div>
                            </div>
                            
                        </form>
                        <div id="result">
                            <table class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>Дата</th>
                                    <th>Число показов</th>
                                    <th>Клики</th>
                                    <th>Запросы кода</th>
                                    <th>Подпись</th>
                                    <th>Оплата после подписи</th>
                                    <th>Всего сделок</th>
                                    <th>Фактическая оплата</th>
                                </tr>
                                </thead>
                                {foreach $metric.items as $date => $metric_row}
                                    <tr>
                                        <td {if $metric_row.total_view} rowspan="2"{/if} align="center" style="vertical-align: middle;">{$date}</td>
                                        <td {if $metric_row.total_view} rowspan="2"{/if} align="center" style="vertical-align: middle;">{$metric_row.total_view}</td>
                                        <td>{$metric_row.total_click}</td>
                                        <td>{$metric_row.total_request_code}</td>
                                        <td>{$metric_row.total_sign}</td>
                                        <td>{$metric_row.total_after_sign}</td>
                                        <td>{$metric_row.total_transactions}</td>
                                        <td>{$metric_row.total_fact_pay}</td>
                                    </tr>
                                    {if $metric_row.total_view}
                                        <tr>
                                            <td class="text-success">{($metric_row.total_click * 100 / $metric_row.total_view)|round:3}%</td>
                                            <td class="text-success">{($metric_row.total_request_code * 100 / $metric_row.total_view)|round:3}%</td>
                                            <td class="text-success">{($metric_row.total_sign * 100 / $metric_row.total_view)|round:3}%</td>
                                            <td class="text-success">{($metric_row.total_after_sign * 100 / $metric_row.total_view)|round:3}%</td>
                                            <td class="text-success">{($metric_row.total_transactions * 100 / $metric_row.total_view)|round:3}%</td>
                                            <td class="text-success"></td>
                                        </tr>
                                    {/if}
                                {/foreach}
                                <tr class="bg-gray">
                                    <td rowspan="2" align="center" style="vertical-align: middle;">Всего</td>
                                    <td rowspan="2" align="center" style="vertical-align: middle;"><strong class="text-warning">{$metric.totals.total_view}</strong></td>
                                    <td><strong class="text-warning">{$metric.totals.total_click}</strong></td>
                                    <td><strong class="text-warning">{$metric.totals.total_request_code}</strong></td>
                                    <td><strong class="text-warning">{$metric.totals.total_sign}</strong></td>
                                    <td><strong class="text-warning">{$metric.totals.total_after_sign}</strong></td>
                                    <td><strong class="text-warning">{$metric.totals.total_transactions}</strong></td>
                                    <td><strong class="text-warning">{$metric.totals.total_fact_pay}</strong></td>
                                </tr>
                                {if $metric.totals.total_view}
                                    <tr class="bg-gray">
                                        <td class="text-success">{($metric.totals.total_click * 100 / $metric.totals.total_view)|round:3}%</td>
                                        <td class="text-success">{($metric.totals.total_request_code * 100 / $metric.totals.total_view)|round:3}%</td>
                                        <td class="text-success">{($metric.totals.total_sign * 100 / $metric.totals.total_view)|round:3}%</td>
                                        <td class="text-success">{($metric.totals.total_after_sign * 100 / $metric.totals.total_view)|round:3}%</td>
                                        <td class="text-success">{($metric.totals.total_transactions * 100 / $metric.totals.total_view)|round:3}%</td>
                                        <td class="text-success"></td>
                                    </tr>
                                {/if}
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
            $("#result").load('{$ajax_url}?' + filter_data + ' #result table', function (response, status, xhr) {
                $('.preloader').hide();
                if (status == "error") {
                    alert('Произошла ошибка сервера подробности в консоли');
                    console.error('error load text: ' + xhr.status + " " + xhr.statusText);
                }
            });
        }
    </script>
{/capture}
