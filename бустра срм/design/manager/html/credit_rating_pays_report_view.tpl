{$meta_title='Отчёт по всем продажам КР' scope=parent}

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet"
          type="text/css"/>
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
                    <span>Отчёт по всем продажам КР</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отчёт по всем продажам КР</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <form id="report_form">
                            <div class="row">
                                <div class="col-6 col-md-3">
                                    <div class="input-group mb-3">
                                        <input type="text" name="date_range" class="form-control daterange"
                                               value="{if $dateStart}{$dateStart|date_format:'%Y.%m.%d'} - {$dateEnd|date_format:'%Y.%m.%d'}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <button onclick="return loadData();" type="button" class="btn btn-info"><i
                                                class="ti-reload"></i> Сформировать
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h4 class="text-danger">Внимание! Данные актуальны с 19.07.2023</h4>
                            </div>
                        </form>
                        <div id="result" class="">
                            <table class="table table-bordered table-hover">
                                <thead class="position-sticky">
                                    <tr>
                                        <th>ФИО</th>
                                        <th>Номер телефона</th>
                                    </tr>
                                    {if $results}
                                        <tr class="bg-inverse text-warning">
                                            <td colspan="2">Всего: <b>{$totals} ₽/ {$results|count}</b></td>
                                        </tr>
                                    {/if}
                                </thead>
                                <tbody>
                                {if $results}
                                    {foreach $results as $result}
                                        <tr>
                                            <td>{$result->fio}</td>
                                            <td>{$result->phone_mobile}</td>
                                        </tr>
                                    {/foreach}
                                {else}
                                    <tr>
                                        <td colspan="2" class="text-danger text-center">Данные не найдены</td>
                                    </tr>
                                {/if}
                                </tbody>
                                {if $results}
                                    <tfoot>
                                        <tr class="bg-inverse text-warning">
                                            <td colspan="2">Всего: <b>{$totals} ₽/ {$results|count}</b></td>
                                        </tr>
                                    </tfoot>
                                {/if}
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
        $(function () {
            $('.daterange').daterangepicker({
                autoApply: true,
                locale: {
                    format: 'YYYY.MM.DD'
                },
                minDate: '2023.05.29',
                default: moment(),
            });
        });

        function loadData() {
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