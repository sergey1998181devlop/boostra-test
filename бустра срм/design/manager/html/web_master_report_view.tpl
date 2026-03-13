{$meta_title='Анализ веб-мастеров' scope=parent}

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
</style>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Анализ веб-мастеров</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Анализ веб-мастеров</li>
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
                                        <div class="btn-group btn-block dropdown-click-over">
                                            <button class="btn btn-block btn-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="false" aria-expanded="true" data-flip="false">
                                                Источники
                                            </button>
                                            <div class="dropdown-menu p-2">
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
                                                            Все
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-info" id="utm_source_selected"></p>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="mb-3">
                                        <div class="btn-group btn-block dropdown-click-over">
                                            <button disabled class="btn btn-block btn-secondary dropdown-toggle" id="dropdown-button-webmaster_id" type="button" data-toggle="dropdown" aria-haspopup="false" aria-expanded="true" data-flip="false">
                                                Выбор web-id
                                            </button>
                                            <div class="dropdown-menu p-2" id="dropdown-webmaster_id"></div>
                                        </div>
                                    </div>
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
                               {if $results}
                                   <colgroup>
                                       <col />
                                   </colgroup>
                                    <thead>
                                        <tr class="small">
                                            <th>{$results['first_column_name']}</th>
                                            <th>Заявки НК</th>
                                            <th>Выдача НК</th>
                                            <th>CV в выдачу НК</th>
                                            <th>Выплаты</th>
                                            <th>Цена привлечения НК</th>
                                            <th>Заявки ПК</th>
                                            <th>Выдача ПК</th>
                                            <th>CV в выдачу ПК</th>
                                            <th>Средняя цена выдачи</th>
                                            <th>Стал ПК</th>
                                            <th>Выдано, рублей</th>
                                            <th>Страховка штук</th>
                                            <th>Страховка рублей</th>
                                            <th>Страховка с 1 клиента</th>
                                            <th>Страховка-Цена привлечения</th>
                                            <th>%%</th>
                                            <th>ОД в просрочке</th>
                                            <th>Доход</th>
                                            <th>Доход с 1 НК</th>
                                            <th>Доход с 1 НК с учётом просрочки и Цены привлечения</th>
                                            <th>Скорбалл выданных</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="small bg-dark text-warning position-sticky">
                                        <td></td>
                                        {foreach $results['totals'] as $total}
                                            <td>{$total}</td>
                                        {/foreach}
                                    </tr>
                                    {foreach $results['items'] as $key => $items}
                                        <tr class="small">
                                            <td>{$key}</td>
                                            {foreach $items as $item}
                                                <td>{$item}</td>
                                            {/foreach}
                                        </tr>
                                    {/foreach}
                                    <tr class="small bg-dark text-warning position-sticky">
                                        <td></td>
                                        {foreach $results['totals'] as $total}
                                            <td>{$total}</td>
                                        {/foreach}
                                    </tr>
                                    </tbody>
                                {else}
                                    <tfoot>
                                        <tr>
                                            <td colspan="12">
                                                <h3 class="text-danger">Данные не найдены</h3>
                                            </td>
                                        </tr>
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

        // подгрузка webmaster_id
        $('[name="filter_utm_source[]"]').on('change', function (e) {
            let $_input_utm_source_all = $('#filter_source_all'),
                $_button = $('#dropdown-button-webmaster_id'),
                $_utm_source_text = $("#utm_source_selected"),
                $_checked_input = $('[name="filter_utm_source[]"]:checked'),
                is_all_checked = $_input_utm_source_all.prop('checked'),
                total_utm_source = $_checked_input.length;

            if (!is_all_checked && total_utm_source === 1) {
                $_button.prop('disabled', false);
                $("#report_form").addClass('data-loading');
                $("#dropdown-webmaster_id").load('{$smarty.server.REQUEST_URI}?ajax=1&action=getWebmasterIds&utm_source=' + $_checked_input.val(), function () {
                    $("#report_form").removeClass('data-loading');
                });
                $_utm_source_text.text($_checked_input.val());
            } else {
                $_utm_source_text.empty();
                $_button.prop('disabled', true);
                $("#dropdown-webmaster_id").empty();
            }
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
