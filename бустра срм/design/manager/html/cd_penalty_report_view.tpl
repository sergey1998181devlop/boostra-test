{$meta_title='Отчёт - штрафной КД на просрочках' scope=parent}

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
            <td rowspan="4" class="text-danger"><b>ИТОГО</b></td>
        </tr>

        {foreach $totals as $period => $totalsItems}
            <tr class="bg-inverse">
                {* Выводим период и добавляем к нему знак "+"*}
                <td class="text-warning"><b>{$period}+</b></td>
                {foreach $totalsItems as $totalKey => $totalValue}
                    {* Выводим итоговые данные о каждом периоде *}
                    <td class="text-warning"><b>{$totalValue}</b></td>
                {/foreach}
            </tr>
        {/foreach}
    {/if}
{/function}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Штрафной КД на просрочках</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Штрафной КД на просрочках</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Штрафной КД на просрочках
                        </h4>
                        <form id="report_form">
                            <div class="row">
                                <div class="col-6 col-md-3">
                                    <div class="input-group mb-3">
                                        <input type="text" name="date_range" class="form-control daterange" value="{if $dateStart}{$dateStart|date_format:'%Y.%m.%d'} - {$dateEnd|date_format:'%Y.%m.%d'}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <button onclick="return loadData();" type="button" class="btn btn-info"><i class="ti-reload"></i> Сформировать</button>
                                    <button onclick="return download();" type="button" class="btn btn-success"><i class="ti-save"></i> Выгрузить</button>
                                </div>
                            </div>
                        </form>
                        <h4 class="text-danger animate-flashing">Внимание! Минимальная дата для отчёта 2023.04.24</h4>
                        <div id="result" class="">
                            <table class="table table-bordered table-hover">
                                <thead class="position-sticky">
                                    <tr>
                                        <th>Период</th>
                                        <th>Просрочка</th>
                                        <th>Кол-во клиентов</th>
                                        <th>Кол-во подключений</th>
                                        <th>Кол-во списаний</th>
                                        <th>Сумма списания, рублей</th>
                                        <th>Конверсия в списание</th>
                                    </tr>
                                </thead>
                                <tbody>

                                <tr colspan="7">



                                </tr>
                                    {if $results}
                                        {foreach $results as $date => $periods}
                                            <tr>
                                                <td rowspan="{count($periods) + 1}">{$date}</td>
                                            </tr>
                                            {foreach $periods as $period}
                                                <tr>
                                                    <td>{$period->periodName}+</td>
                                                    <td>{$period->total_users}</td>
                                                    <td>{$period->insure}</td>
                                                    <td>{$period->total_count}</td>
                                                    <td>{$period->total_pays}</td>
                                                    <td>{$period->cv}</td>

                                                </tr>
                                            {/foreach}
                                        {/foreach}

                                    {else}
                                        <tr>
                                            <td colspan="6" class="text-danger text-center">Данные не найдены</td>
                                        </tr>
                                    {/if}
                                </tbody>
                                <tfoot>
                                    {generateTotals totals=$totals}
                                </tfoot>
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
                minDate: '2023.04.24',
                default: moment(),
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