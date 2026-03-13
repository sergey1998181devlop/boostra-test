{$meta_title='Одобренные заявки' scope=parent}

{capture name='page_scripts'}

    <script src="design/manager/assets/plugins/moment/moment.js"></script>
    
    <script src="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/manager/assets/plugins/daterangepicker/daterangepicker.js"></script>
    <script>
    $(function(){
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
                    <span>Одобренные заявки</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Одобренные заявки</li>
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
                        <h4 class="card-title">Одобренные заявки</h4>
                        <form id="report_form">
                            <div class="row">
                                <div class="col-6 col-md-2" style="display: none;">
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
                                            <option value="">источники заявок</option>
                                            {foreach $utm_sources as $utm_source}
                                                <option value="{$utm_source->utm_source}" {if $filter_utm_source===$utm_source->utm_source}selected="selected"{/if}>{$utm_source->utm_source}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="mb-3">
                                        <select class="form-control" name="client_type">
                                            <option value="" {if !$filter_source}selected{/if}>Все</option>
                                            {foreach $client_types as $item}
                                                <option
                                                    value="{$item['type']}"
                                                    {if $filter_source == $item['type']}selected{/if}
                                                >{$item['title']}</option>
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
                                <th>ФИО</th>
                                <th class="text-center">
                                    Телефон
                                </th>
                                <th class="text-center">Источник заявки</th>
                                <th class="text-center">
                                    Дата окончания
                                </th>
                            </tr>
                            
                            {foreach $report as $item}
                            <tr>
                                <td>
                                    <strong >{$item->fullname}</strong>
                                </td>
                                <td>
                                    <strong >{$item->phone_mobile}</strong>
                                </td>
                                <td>
                                    <strong>{$item->utm_source}</strong>
                                </td>
                                <td>
                                    <strong >{$item->fin}</strong>
                                </td>
                            </tr>
                            {/foreach}
                            
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
