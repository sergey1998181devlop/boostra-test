{$meta_title='Кредитный рейтинг Новый клиент' scope=parent}

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

{function name=generateTotals}
    {if $totals}
        <tr class="bg-inverse">
            <td class="text-danger" style="vertical-align: middle;" rowspan="2"><b>Всего:</b></td>
            {foreach $totals['items'] as $item}
                <td class="text-danger"><b>{$item}</b></td>
            {/foreach}
        </tr>
        <tr class="bg-inverse small">
            {foreach $totals['cv'] as $item}
                <td class="text-warning"><b>{$item}</b></td>
            {/foreach}
        </tr>
    {/if}
{/function}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Кредитный рейтинг Новый клиент</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Кредитный рейтинг Новый клиент</li>
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
                            <input type="hidden" name="ajax" value="1" />
                        </form>
                        <div class="mb-4">
                            <h4 class="text-danger">Внимание! Данные актуальны с 19.07.2023</h4>
                        </div>
                        <div id="result">
                            <table class="table table-bordered table-hover">
                                <thead class="position-sticky">
                                    <tr>
                                        <th>Дата</th>
                                        <th>НК посетил страницу рейтинга</th>
                                        <th>Получить рейтинг</th>
                                        <th>Получить код</th>
                                        <th>Регистрация смс-кода</th>
                                        <th>Отправить код (оплата)</th>
                                        <th>Фактически сделок</th>
                                        <th>Фактическая оплата, руб.</th>
                                    </tr>
                                    {generateTotals totals=$totals}
                                </thead>
                                <tbody>
                                {if $items}
                                    {foreach $items as $date => $values}
                                        <tr>
                                            <td style="vertical-align: middle;" class="text-success" rowspan="2">
                                                <b>{$date}</b>
                                            </td>
                                            {foreach $values['items'] as $value}
                                                <td>{$value}</td>
                                            {/foreach}
                                        </tr>
                                        <tr class="bg-dark">
                                            {foreach $values['cv'] as $value}
                                                <td class="small">{$value}</td>
                                            {/foreach}
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="8" class="text-danger">Данные не найдены</td>
                                    </tr>
                                {/if}
                                </tbody>
                                {if $items}
                                    <tfoot>
                                        {generateTotals totals=$totals}
                                    </tfoot>
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
            $("#result").load('{$smarty.server.REQUEST_URI}?' + filter_data + ' #result table', function (response, status, xhr) {
                $('.preloader').hide();
                if (status == "error") {
                    alert('Произошла ошибка сервера подробности в консоли');
                    console.error('error load text: ' + xhr.status + " " + xhr.statusText);
                }
            });
        }
    </script>
{/capture}
