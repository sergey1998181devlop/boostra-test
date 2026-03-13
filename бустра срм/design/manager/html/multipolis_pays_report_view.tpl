{$meta_title=$title scope=parent}

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
        font-size: 10px;
    }
    thead.position-sticky {
        top: 0;
        background-color: #272c33;
    }
</style>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>{$title}</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">{$title}</li>
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
                                <div class="col-6 col-md">
                                    <div class="btn-group">
                                        <button type="button" onclick="loadData();" class="btn btn-info">Сформировать <i class="ti-dashboard"></i></button>
                                        <button type="button" onclick="download();" class="btn btn-success">Выгрузить <i class="ti-export"></i></button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div id="result">
                            <table class="table table-bordered table-hover">
                                <thead class="position-sticky">
                                    <tr>
                                        <th rowspan="2">Тип услуги</th>
                                        <th rowspan="2">ФИО</th>
                                        <th rowspan="2">Договор найма</th>
                                        <th rowspan="2">Номер ключа</th>
                                        <th colspan="3">Варианты события</th>
                                        <th colspan="3">Данные оплаты</th>
                                    </tr>
                                    <tr>
                                        <th>проданы (оплачены) и НЕ возвращены</th>
                                        <th>проданы (оплачены) и возвращены</th>
                                        <th>проданы (НЕ оплачены)</th>
                                        <th>дата платежа</th>
                                        <th>operation_id</th>
                                        <th>сумма</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {if $pays}
                                        {foreach $pays as $pay_values}
                                            <tr>
                                                <td style="vertical-align: middle" rowspan="2">{$pay_values->name_pay}</td>
                                                <td style="vertical-align: middle" rowspan="2">{$pay_values->fio}</td>
                                                <td style="vertical-align: middle" rowspan="2">{$pay_values->contract_number}</td>
                                                <td style="vertical-align: middle" rowspan="2">{$pay_values->multipolis_key}</td>

                                                {foreach $pay_values->action_variants as $action_variant}
                                                    <td style="vertical-align: middle" rowspan="2">{$action_variant}</td>
                                                {/foreach}

                                                <td>{$pay_values->pays_detail->sale_not_return['date']}</td>
                                                <td>{$pay_values->pays_detail->sale_not_return['operation_id']}</td>
                                                <td>{$pay_values->pays_detail->sale_not_return['amount']}</td>
                                            </tr>
                                            {foreach $pay_values->pays_detail as $pay_value}
                                                {if $pay_value@iteration != 1}
                                                    <tr>
                                                        <td>{$pay_value['date']}</td>
                                                        <td>{$pay_value['operation_id']}</td>
                                                        <td>{$pay_value['amount']}</td>
                                                    </tr>
                                                {/if}
                                            {/foreach}
                                        {/foreach}
                                    {else}
                                        <td colspan="10" class="text-danger">Данные не найдены</td>
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
            });
        }

        function download() {
            let filter_data = $('#report_form').serialize();
            window.open(
                '{$smarty.server.REQUEST_URI}?action=download&' + filter_data,
                '_blank'
            );
            return false;
        }
    </script>
{/capture}
