{$meta_title='Конверсия в допы' scope=parent}

{capture name='page_styles'}

    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">

    <style>
        .table td {
            text-align:center!important;
        }
    </style>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Конверсия в допы</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Конверсия в допы</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Конверсия в допы за период {if $dateStart}{$dateStart|date} - {$dateEnd|date}{/if}</h4>
                        <form id="report_form">
                            <div class="row">
                                <div class="col-6 col-md-2" style="display: none;">
                                    <div class="input-group mb-2">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="mb-3">
                                        <select class="form-control" name="filter_type">
                                            {foreach $clientTypes as $type => $title}
                                                <option value="{$type}"
                                                        {if $filterType == $type}selected{/if}
                                                >{$title}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
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
                                    <button onclick="return showPreloader();" type="submit" class="btn btn-info"><i class="ti-reload"></i> Сформировать</button>
                                    <button onclick="return download();" type="button" class="btn btn-success"><i class="ti-save"></i> Выгрузить</button>
                                </div>
                            </div>

                        </form>

                            <table class="table table-hover">

                                {if $filterType === DopConversionReportView::NEW_CONTRACTS_TYPE}
                                    <tr>
                                        <th></th>
                                        <th class="text-center">Новые договоры</th>
                                        <th class="text-center">Доп на выдачи Страховка, шт</th>
                                        <th class="text-center">Доп на выдачи КД</th>
                                        <th class="text-center">Доп на выдачи Телемедицина</th>
                                        <th class="text-center">Доп на выдачи Консьерж сервис</th>
                                    </tr>
                                {/if}

                                {if $filterType === DopConversionReportView::PROLONGATIONS_TYPE}
                                    <tr>
                                        <th></th>
                                        <th>Пролонгация</th>
                                        <th>Доп на пролонгации Страховка, шт</th>
                                        <th>Доп на пролонгации КД</th>
                                        <th>Доп на пролонгации Телемедицина</th>
                                        <th>Доп на пролонгации Консьерж сервис</th>
                                    </tr>
                                {/if}

                                {if $report && $totals}
                                    {foreach $report as $item}
                                        <tr>
                                            <td>{$item->client_type}</td>
                                            <td>{$item->total_count}</td>
                                            <td>{$item->insurance_count}</td>
                                            <td>{$item->credit_doctor_count}</td>
                                            <td>{$item->telemedicina_count}</td>
                                            <td>{$item->multipolis_count}</td>
                                        </tr>
                                        <tr class="bg-secondary" style="font-size: 0.7em">
                                            <td>Проникновение</td>
                                            <td></td>
                                            <td>{math equation="x / y" x = $item->insurance_count y = $item->total_count format="%.2f"}</td>
                                            <td>{math equation="x / y" x = $item->credit_doctor_count y = $item->total_count format="%.2f"}</td>
                                            <td>{math equation="x / y" x = $item->telemedicina_count y = $item->total_count format="%.2f"}</td>
                                            <td>{math equation="x / y" x = $item->multipolis_count y = $item->total_count format="%.2f"}</td>
                                        </tr>
                                    {/foreach}
                                    <tr>
                                        <td>Итого</td>
                                        <td>{$totals->total}</td>
                                        <td>{$totals->totalInsurances}</td>
                                        <td>{$totals->totalCD}</td>
                                        <td>{$totals->totalTelemedicina}</td>
                                        <td>{$totals->totalMultipolis}</td>
                                    </tr>
                                {else}
                                    <tr>
                                        <td colspan="14" class="text-danger text-center">Данные не найдены</td>
                                    </tr>
                                {/if}

                            </table>
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
