{$meta_title='Воронка займы Новый клиент' scope=parent}

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
                    <span>Воронка займы Новый клиент</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Воронка займы Новый клиент</li>
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
                                                <div class="form-group">
                                                    <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                                        <input name="filter_utm_source[]" type="checkbox" class="custom-control-input" id="filter_source_all" value="all"  />
                                                        <label class="custom-control-label" for="filter_source_all">
                                                            ВСЕ
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <button type="button" onclick="loadData();" class="btn btn-info">Сформировать</button>
                                </div>
                            </div>

                        </form>
                        <div id="result" class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Дата</th>
                                        <th>Источник</th>
                                        <th>Переходы</th>
                                        <th>Получить заём</th>
                                        <th>ФИО, дата, Согласие</th>
                                        <th>Подтвердил телефон</th>
                                        <th>Паспорт</th>
                                        <th>Адрес</th>
                                        <th>Получить деньги после предв. одобрения</th>
                                        <th>Регистрация карты - "Добавить карту"</th>
                                        <th>Регистрация - Вход на страницу с фото</th>
                                        <th>Фото - "Далее после Фото"</th>
                                        <th>Получить деньги после Работа</th>
                                        <th>Заявки</th>
                                        <th>Одобрено</th>
                                        <th>Выдано</th>
                                    </tr>
                                </thead>
                                <tbody>
                                {if $metric}
                                    <tr class="bg-inverse">
                                        <td class="text-danger" style="vertical-align: middle;" rowspan="2">Итого</td>
                                        <td class="text-danger">Значения:</td>
                                        {foreach $totals['items']['total'] as $item}
                                            <td class="text-danger"><b>{$item}</b></td>
                                        {/fo
                                        reach}
                                    </tr>
                                    <tr class="bg-inverse">
                                        <td class="text-danger">CV:</td>
                                        {foreach $totals['cv']['total'] as $item}
                                            <td class="text-danger"><b>{$item}</b></td>
                                        {/foreach}
                                    </tr>
                                    {foreach $metric as $date => $metric_row}
                                        {if !$view_only_total}
                                            {foreach $metric_row as $utm_source => $metric_data}
                                                <tr>
                                                    {if $metric_data@first}
                                                        <td style="vertical-align: middle;" class="text-success" rowspan="{($metric_row|@count) * 2 + 2}"><b>{$date}</b></td>
                                                    {/if}
                                                    <td><b>{if $utm_source == 'cf'}ЦФ{else}{$utm_source}{/if}</b></td>
                                                    <td><b>{$metric_data.items.visitors}</b></td>
                                                    <td><b>{$metric_data.items.registration_click}</b></td>
                                                    <td><b>{$metric_data.items.fio}</b></td>
                                                    <td><b>{$metric_data.items.telephone}</b></td>
                                                    <td><b>{$metric_data.items.passport}</b></td>
                                                    <td><b>{$metric_data.items.address}</b></td>
                                                    <td><b>{$metric_data.items.predreshenie}</b></td>
                                                    <td><b>{$metric_data.items.reg_cards}</b></td>
                                                    <td><b>{$metric_data.items.page_photo}</b></td>
                                                    <td><b>{$metric_data.items.photo}</b></td>
                                                    <td><b>{$metric_data.items.work}</b></td>
                                                    <td><b>{$metric_data.items.orders_all}</b></td>
                                                    <td><b>{$metric_data.items.orders_approved}</b></td>
                                                    <td><b>{$metric_data.items.orders_issued}</b></td>
                                                </tr>
                                                <tr class="bg-grey small">
                                                    <td class="text-warning">CV</td>
                                                    <td class="text-warning">{$metric_data.cv.visitors}</td>
                                                    <td class="text-warning">{$metric_data.cv.registration_click}</td>
                                                    <td class="text-warning">{$metric_data.cv.fio}</td>
                                                    <td class="text-warning">{$metric_data.cv.telephone}</td>
                                                    <td class="text-warning">{$metric_data.cv.passport}</td>
                                                    <td class="text-warning">{$metric_data.cv.address}</td>
                                                    <td class="text-warning">{$metric_data.cv.predreshenie}</td>
                                                    <td class="text-warning">{$metric_data.cv.reg_cards}</td>
                                                    <td class="text-warning">{$metric_data.cv.page_photo}</td>
                                                    <td class="text-warning">{$metric_data.cv.photo}</td>
                                                    <td class="text-warning">{$metric_data.cv.work}</td>
                                                    <td class="text-warning">{$metric_data.cv.orders_all}</td>
                                                    <td class="text-warning">{$metric_data.cv.orders_approved}</td>
                                                    <td class="text-warning">{$metric_data.cv.orders_issued}</td>
                                                </tr>
                                            {/foreach}
                                        {/if}

                                        <tr class="bg-dark">
                                            {if $view_only_total}
                                                <td style="vertical-align: middle;" class="text-success" rowspan="2"><b>{$date}</b></td>
                                            {/if}

                                            <td class="text-success">Всего:</td>
                                            {foreach $totals['items']['date'][$date] as $item}
                                                <td class="text-success"><b>{$item}</b></td>
                                            {/foreach}
                                        </tr>
                                        <tr class="bg-dark">
                                            <td class="text-success">CV:</td>
                                            {foreach $totals['cv']['date'][$date] as $item}
                                                <td class="text-success"><b>{$item}</b></td>
                                            {/foreach}
                                        </tr>
                                    {/foreach}
                                {else}
                                    <td colspan="15">Данные не найдены</td>
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
    </script>
{/capture}
