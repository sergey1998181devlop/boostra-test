{$meta_title='Отвалы - эффективность КЦ' scope=parent}

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
                    <span>Отвалы - эффективность КЦ</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отвалы - эффективность КЦ</li>
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
                            <div class="row align-items-end">
                                <div class="col-6 col-md-3">
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
                                    <div class="form-group mb-3">
                                        <label>Минимальная длительность звонка</label>
                                        <div class="">
                                            <input type="number" name="filter_duration" class="form-control" value="30" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <div class="btn-group mb-3">
                                        <button type="button" onclick="loadData();" class="btn btn-info"><i class="ti-reload"></i> Сформировать</button>
                                    </div>
                                </div>
                            </div>

                        </form>
                        <div id="result" class="table-responsive">
                            <table class="table table-bordered table-hover">
                               {if $results}
                                    <thead>
                                        <tr>
                                            <th>Сотрудник</th>
                                            <th>Кол-во отвалов</th>
                                            <th>Кол-во контактов</th>
                                            <th>Кол-во оформленных займов (после звонка в этот же день)</th>
                                            <th>Сумма займов</th>
                                            <th>Конверсия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    {foreach $results as $result}
                                        <tr class="bg-dark">
                                            <td>{$result->name_1c} <b>(id: {$result->missing_manager_id})</b></td>
                                            <td>{$result->total_bad_orders}</td>
                                            <td>{$result->total_calls}</td>
                                            <td>{$result->total_orders_with_confirmed}</td>
                                            <td>{$result->total_amount}</td>
                                            <td>
                                                {if $result->total_calls}
                                                    {(($result->total_orders_with_confirmed / $result->total_calls) * 100)|round:2}%
                                                {/if}
                                            </td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
                                {else}
                                    <tfoot>
                                        <tr>
                                            <td colspan="5">
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
                timePicker: true,
                timePicker24Hour: true,
                timePickerIncrement: 5,
                locale: {
                    format: 'YYYY.MM.DD hh:mm'
                },
                default:''
            });
        });

        // подгрузка webmaster_id
        $('[name="filter_utm_source[]"]').on('change', function (e) {
            let $_input_utm_source_all = $('#filter_source_all'),
                $_button = $('#dropdown-button-webmaster_id'),
                $_checked_input = $('[name="filter_utm_source[]"]:checked'),
                is_all_checked = $_input_utm_source_all.prop('checked'),
                total_utm_source = $_checked_input.length;

            if (!is_all_checked && total_utm_source === 1) {
                $_button.prop('disabled', false);
                $("#dropdown-webmaster_id").load('{$smarty.server.REQUEST_URI}?ajax=1&action=getWebmasterIds&utm_source=' + $_checked_input.val());
            } else {
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
