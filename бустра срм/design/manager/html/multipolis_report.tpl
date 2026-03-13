{$meta_title='Продажи консьерж сервиса' scope=parent}

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
        <tr class="small bg-inverse text-danger">
            <td style="vertical-align: middle;" colspan="2" rowspan="2"><b>Всего:</b></td>
            {foreach $totals['items'] as $item}
                <td class="text-danger"><b>{$item}</b></td>
            {/foreach}
        </tr>
        <tr class="small bg-inverse text-warning">
            {foreach $totals['cv'] as $item}
                <td>{$item}</td>
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
                    <span>Продажи консьерж сервиса</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Продажи консьерж сервиса</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Отчет за период
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

                                <div class="col-6 col-md-2">
                                    <div class="mb-3">
                                        <select class="form-control" name="filter_group_by">
                                            <option value="" {if !$groupBy}selected{/if}>Без группировки</option>
                                            <option value="day" {if $groupBy == 'day'}selected{/if}>По дням</option>
                                            <option value="month" {if $groupBy == 'month'}selected{/if}>По месяцам</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-6 col-md-4">
                                    <button onclick="return showPreloader();" type="submit" class="btn btn-info"><i class="ti-reload"></i> Сформировать</button>
                                    <button onclick="return download();" type="button" class="btn btn-success"><i class="ti-save"></i> Выгрузить</button>
                                </div>

                                {if $groupBy === 'day' || $groupBy === 'month'}
                                    <div class="col-4 col-md-3">
                                        <span class="text-danger text-right">Количество непроданных полисов: {$unpaidPolisesCount}</span>
                                    </div>
                                {/if}
                            </div>

                        </form>
                        <div id="result" class="">
                            <table class="table table-bordered table-hover">
                                <thead class="position-sticky">
                                    <tr>
                                        {if !$groupBy}
                                            <th>Дата</th>
                                            <th>Полисы</th>
                                            <th>ФИО клиента</th>
                                            <th>Номер телефона клиента</th>
                                            <th>Отправлена заявка</th>
                                            <th>Возврат</th>
                                        {else}
                                            <th>Дата</th>
                                            <th>Полисы</th>
                                            <th>Отправлена заявка</th>
                                        {/if}
                                    </tr>
                                </thead>
                                <tbody>
                                {if $multipolisList}
                                    {if !$groupBy}
                                        {foreach $multipolisList as $multipolis}
                                            <tr>
                                                <td>{$multipolis->date_filter}</td>
                                                <td>{$multipolis->number}</td>
                                                <td>{$multipolis->username}</td>
                                                <td>{$multipolis->phone_mobile}</td>
                                                <td>{if $multipolis->is_sent == 1}Да{else}Нет{/if}</td>
                                                <td>-</td>
                                            </tr>
                                        {/foreach}
                                    {else}
                                        <tr>
                                            <td class="font-weight-bold">Итог</td>
                                            <td class="font-weight-bold">{$totalPolisesCount}</td>
                                            <td class="font-weight-bold">{$totalSentCount}</td>
                                        </tr>
                                        {foreach $multipolisList as $multipolis}
                                            <tr>
                                                <td>{$multipolis->date_filter}</td>
                                                <td>{$multipolis->polis_count}</td>
                                                <td>{$multipolis->sent_count}</td>
                                            </tr>
                                        {/foreach}
                                    {/if}
                                {else}
                                    <tr>
                                        <td colspan="14" class="text-danger text-center">Данные не найдены</td>
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
                '{$report_uri}?action=download&' + filter_data,
                '_blank'
            );
            return false;
        }
    </script>
{/capture}