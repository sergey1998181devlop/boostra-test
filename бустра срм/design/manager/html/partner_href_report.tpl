{$meta_title='Отказной трафик по ссылкам' scope=parent}

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
                    <span>Отказной трафик по ссылкам</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отказной трафик по ссылкам</li>
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
                                        <select class="form-control" name="filter_unique">
                                            <option value="" {if !$filter_unique}selected{/if}>Без уникальности</option>
                                            <option value="unique" {if $filter_unique == 'unique'}selected{/if}>Уникальные</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="mb-3">
                                        <select class="form-control" name="filter_group_by">
                                            <option value="day" {if !$filter_group_by}selected{/if}>По дням</option>
                                            <option value="month" {if $filter_group_by == 'month'}selected{/if}>По месяцам</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <button type="button" onclick="loadData();" class="btn btn-info">Сформировать</button>
                                </div>
                            </div>
                            <div id="result">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Дата</th>
                                            <th>Ссылка</th>
                                            <th>Показы</th>
                                            <th>Клики</th>
                                            <th>СV (конверсия)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {foreach $result as $date => $result_item}
                                            {foreach $result_item as $result_row}
                                                <tr>
                                                    <td>{$date}</td>
                                                    <td>{$result_row['href']->href} (<b>{$result_row['href']->name}</b>)</td>
                                                    <td>{$result_row['total_views']}</td>
                                                    <td>{$result_row['total_clicks']}</td>
                                                    <td>{$result_row['cv']}</td>
                                                </tr>
                                            {/foreach}
                                        {/foreach}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2" class="text-warning">
                                                Всего
                                            </td>
                                            <td class="text-success">{$totals['total_views']}</td>
                                            <td class="text-success">{$totals['total_clicks']}</td>
                                            <td class="text-success">{$totals['cv']}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <strong class=""></strong>
                        </form>
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
