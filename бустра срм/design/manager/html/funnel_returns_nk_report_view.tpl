{$meta_title='Отчёт Воронка по возвратам НК' scope=parent}

{capture name='page_styles'}

    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
{/capture}

<style>
    tr.small td {
        padding: 0.25rem;
    }
    .table thead th, .table th {
        border: 1px solid;
    }
</style>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Отчёты</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отчёт Воронка по возвратам НК</li>
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
                                <div class="col-6 col-md-4">
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="days_from_label">От</span>
                                                </div>
                                                <input type="number" name="days_from" required class="form-control" placeholder="1" aria-label="days_from" aria-describedby="days_from_label">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <div class="input-group mb-3">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="days_to_label">До</span>
                                                </div>
                                                <input type="number" name="days_to" required class="form-control" placeholder="5" aria-label="days_to" aria-describedby="days_to_label">
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                <div class="col-6 col-md-auto">
                                    <div class="btn-group">
                                        <button type="submit" class="btn btn-info"><i class="ti-reload"></i> Сформировать</button>
                                        <button onclick="download();" type="button" class="btn btn-success"><i class="ti-save"></i> Выгрузить</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <h4 class="text-danger animate-flashing">Внимание! Минимальная дата для отчёта 18.10.2022</h4>
                        <div id="result" class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>N</th>
                                        <th>Номера телефона клиентов, закрывших свой первый договоро в указанный период (НК)</th>
                                        <th>Кто из них заходил в ЛК после закрытия займа в интервале дат включительно</th>
                                        <th>Кто из них подал заявку на заём после закрытия займа в интервале дат включительно</th>
                                        <th>Кто из них получил Одобрение после закрытия займа в интервале дат включительно</th>
                                        <th>Кто из них получил заём после закрытия займа в интервале дат включительно</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {if $results}
                                        {foreach $results as $result}
                                            <tr>
                                                <td>{$result->day_close_after_approve}</td>
                                                <td>{$result->phone_mobile}</td>
                                                <td>{$result->visit_lk_after_closed_order}</td>
                                                <td>{$result->has_order_after_closed}</td>
                                                <td>{$result->has_approve_order_after_closed}</td>
                                                <td>{$result->has_confirm_order_after_closed}</td>
                                            </tr>
                                        {/foreach}
                                        <tr class="text-success bg-dark">
                                            <td>Итого</td>
                                            <td></td>
                                            <td>{$totals['visit_lk_after_closed_order']}</td>
                                            <td>{$totals['has_order_after_closed']}</td>
                                            <td>{$totals['has_approve_order_after_closed']}</td>
                                            <td>{$totals['has_confirm_order_after_closed']}</td>
                                        </tr>
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

        $("#report_form").on('submit', function (e) {
            e.preventDefault();
            loadData();
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
            });
        }

        function download() {
            let filter_data = $('#report_form').serialize();
            window.open(
                '{$smarty.server.REQUEST_URI}?action=download&' + filter_data,
                '_blank' // <- This is what makes it open in a new window.
            );
        }
    </script>
{/capture}
