{$meta_title='Займы по одобренным' scope=parent}

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
                    <span>Займы по одобренным</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Займы по одобренным</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Займы по одобренным за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
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
                                        <select class="form-control" name="client_type">
                                            <option value="" {if !$filterSource}selected{/if}>Все</option>
                                            {foreach $clientTypes as $item}
                                                <option
                                                        value="{$item['type']}"
                                                        {if $filterSource == $item['type']}selected{/if}
                                                >{$item['title']}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="input-group mb-3">
                                        <input type="text" name="date_range" class="form-control daterange" value="{if $date_from}{$date_from|date_format:'%Y.%m.%d'} - {$date_to|date_format:'%Y.%m.%d'}{/if}">
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
                        <table class="table table-bordered table-hover">
                            {if $report}
                                <thead class="position-sticky">
                                    <tr>
                                       <th class="text-center" colspan="{$fields_name|count}">Когда забрали займы по этим заявкам</th>
                                    </tr>
                                    <tr class="text-warning bg-dark">
                                        <th>Итого:</th>
                                        {foreach $totals as $total}
                                            <th>{$total}</th>
                                        {/foreach}
                                    </tr>
                                </thead>
                            {/if}

                            <tr>
                                {foreach $fields_name as $field_name}
                                    <th>{$field_name}</th>
                                {/foreach}
                            </tr>

                            {if $report}
                                {foreach $report as $item}
                                    <tr>
                                        <td width="10%">{$item->approve_date_filter|date}</td>
                                        <td>{$item->approved_total}</td>
                                        <td>{$item->confirm_total}</td>
                                        <td>{($item->confirm_total * 100 / $item->approved_total)|round:2}</td>
                                        <td>{$item->same_day}</td>
                                        <td>{($item->same_day * 100 / $item->approved_total)|round:2}</td>
                                        <td>{$item->day1}</td>
                                        <td>{($item->day1 * 100 / $item->approved_total)|round:2}</td>
                                        <td>{$item->day2}</td>
                                        <td>{($item->day2 * 100 / $item->approved_total)|round:2}</td>
                                        <td>{$item->day3}</td>
                                        <td>{($item->day3 * 100 / $item->approved_total)|round:2}</td>
                                        <td>{$item->day4}</td>
                                        <td>{($item->day4 * 100 / $item->approved_total)|round:2}</td>
                                        <td>{$item->day5}</td>
                                        <td>{($item->day5 * 100 / $item->approved_total)|round:2}</td>
                                        <td>{$item->day6}</td>
                                        <td>{($item->day6 * 100 / $item->approved_total)|round:2}</td>
                                        <td>{$item->day7}</td>
                                        <td>{($item->day7 * 100 / $item->approved_total)|round:2}</td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr>
                                    <td colspan="14" class="text-danger text-center">Данные не найдены</td>
                                </tr>
                            {/if}

                        </table>
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
