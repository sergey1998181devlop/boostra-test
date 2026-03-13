{$meta_title='Переход НК в ПК' scope=parent}

{capture name='page_scripts'}

    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    
    <script src="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
    $(function(){
        $('.daterange').daterangepicker({
            autoApply: true,
            locale: {
                format: 'DD.MM.YYYY'
            },
            default:''
        });
        $('.datepick').daterangepicker({
            autoApply: true,
            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'DD.MM.YYYY'
            },
            default:''
        });
        function showPreloader() {
            $('.preloader').show();
        }
    })
    </script>
{/capture}

{capture name='page_styles'}
    
    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker plugins css -->
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">

    <style>
    .table td {
        text-align:center!important;
    }
    </style>
{/capture}

<div class="page-wrapper">
    <!-- ============================================================== -->
    <!-- Container fluid  -->
    <!-- ============================================================== -->
    <div class="container-fluid">
        <!-- ============================================================== -->
        <!-- Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i> 
                    <span>Переход НК в ПК (Данные в отчёте корректны с 01.08.2022)</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Переход НК в ПК</li>
                </ol>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- ============================================================== -->
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Переход НК в ПК за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
                        <form id="report_form">
                            <div class="row">
                                <div class="col-6 col-md-3">
                                    <div class="input-group mb-3">
                                        <input type="text" name="daterange" class="form-control daterange" value="{if $from && $to}{$from}-{$to}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="input-group mb-2">
                                        <input
                                            type="text"
                                            name="datelimit"
                                            class="form-control datepick"
                                            value="{if $datelimit}{$datelimit}{$datelimit}{/if}"
                                        >
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="mb-3">
                                        <select class="form-control" name="utm_source">
                                            <option value="" {if !$filter_source}selected{/if}>Все источники</option>
                                            {foreach $sources as $item}
                                                <option
                                                    value="{$item->source}"
                                                    {if $filter_source == $item->source}selected{/if}
                                                >{$item->source}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <button onclick="return showPreloader();" type="submit" class="btn btn-info"><i class="ti-reload"></i> Сформировать</button>
                                    <button onclick="return download();" type="button" class="btn btn-success"><i class="ti-save"></i> Выгрузить</button>
                                </div>
                            </div>
                            
                        </form>                        
                        <table class="table table-hover">
                            
                            <tr>
                                <th>Источник/web-id</th>
                                <th class="text-center">
                                    НК закрылись
                                </th>
                                <th class="text-center">
                                    Заходил в лк после закрытия 1-го договора
                                </th>
                                <th class="text-center">
                                    Заявки ПК1
                                </th>
                                <th class="text-center">
                                    ПК1
                                </th>
                                <th class="text-center">
                                    Заходил в лк после закрытия 2-го договора
                                </th>
                                <th class="text-center">
                                    Заявки ПК2
                                </th>
                                <th class="text-center">
                                    ПК2
                                </th>
                                <th class="text-center">
                                    Заходил в лк после закрытия 3-го договора
                                </th>
                                <th class="text-center">
                                    Заявки ПК3
                                </th>
                                <th class="text-center">
                                    ПК3
                                </th>
                            </tr>
                            <tr>
                                <td>
                                    <strong >Итого:</strong>
                                </td>
                                <td>
                                    <strong >{$totals['nk']}</strong>
                                </td>
                                <td>
                                    <strong >{$totals['visits1']}</strong>
                                </td>
                                <td>
                                    <strong >{$totals['orders1']}</strong>
                                </td>
                                <td>
                                    <strong >{$totals['pk1']}</strong>
                                </td>
                                <td>
                                    <strong >{$totals['visits2']}</strong>
                                </td>
                                <td>
                                    <strong >{$totals['orders2']}</strong>
                                </td>
                                <td>
                                    <strong >{$totals['pk2']}</strong>
                                </td>
                                <td>
                                    <strong >{$totals['visits3']}</strong>
                                </td>
                                <td>
                                    <strong >{$totals['orders3']}</strong>
                                </td>
                                <td>
                                    <strong >{$totals['pk3']}</strong>
                                </td>
                            </tr>
                            
                            {foreach $report as $item}
                            <tr>
                                <td>
                                    <strong >{$item['source']}</strong>
                                </td>
                                <td>
                                    <strong >{$item['nk']}</strong>
                                </td>
                                <td>
                                    <strong >{$item['visits1']}</strong>
                                </td>
                                <td>
                                    <strong >{$item['orders1']}</strong>
                                </td>
                                <td>
                                    <strong >{$item['pk1']}</strong>
                                </td>
                                <td>
                                    <strong >{$item['visits2']}</strong>
                                </td>
                                <td>
                                    <strong >{$item['orders2']}</strong>
                                </td>
                                <td>
                                    <strong >{$item['pk2']}</strong>
                                </td>
                                <td>
                                    <strong >{$item['visits3']}</strong>
                                </td>
                                <td>
                                    <strong >{$item['orders3']}</strong>
                                </td>
                                <td>
                                    <strong >{$item['pk3']}</strong>
                                </td>
                            </tr>
                            {/foreach}
                            <tr>
                                <td>
                                    <strong >Итого:</strong>
                                </td>
                                <td>
                                    <strong >{$totals['nk']}</strong>
                                </td>
                                <td>
                                    <strong >{$totals['visits1']}</strong>
                                </td>
                                <td>
                                    <strong >{$totals['orders1']}</strong>
                                </td>
                                <td>
                                    <strong >{$totals['pk1']}</strong>
                                </td>
                                <td>
                                    <strong >{$totals['visits2']}</strong>
                                </td>
                                <td>
                                    <strong >{$totals['orders2']}</strong>
                                </td>
                                <td>
                                    <strong >{$totals['pk2']}</strong>
                                </td>
                                <td>
                                    <strong >{$totals['visits3']}</strong>
                                </td>
                                <td>
                                    <strong >{$totals['orders3']}</strong>
                                </td>
                                <td>
                                    <strong >{$totals['pk3']}</strong>
                                </td>
                            </tr>
                            
                        </table>
                        <strong class=""></strong>
                    </div>
                </div>
                <!-- Column -->
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End PAge Content -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- footer -->
    <!-- ============================================================== -->
    {include file='footer.tpl'}
    <!-- ============================================================== -->
    <!-- End footer -->
    <!-- ============================================================== -->
</div>
<script>
    function download() {
        let filter_data = $('#report_form').serialize();
        window.open(
            '{$report_uri}?action=download&' + filter_data,
            '_blank' // <- This is what makes it open in a new window.
        );
        return false;
    }
</script>
