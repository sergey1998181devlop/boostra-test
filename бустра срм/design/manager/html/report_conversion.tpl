{$meta_title='Отчет по конверсиям' scope=parent}

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
                    <span>Отчет по конверсиям</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отчет по конверсиям</li>
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
                        <h4 class="card-title">Отчет по конверсиям за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
                        <form>
                            <div class="row">
                                <div class="col-6 col-md-4">
                                    <div class="input-group mb-3">
                                        <input type="text" name="daterange" class="form-control daterange" value="{if $from && $to}{$from}-{$to}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <button type="submit" class="btn btn-info">Сформировать</button>
                                </div>
                            </div>
                            
                        </form>
                        {foreach $reports as $report}                        
                        <table class="table table-hover">
                            
                            <tr>
                                <th>
                                    {if $report->type == 'nk'}<span class="label label-primary">Новые клиенты</span><br /><i>Кр. доктор</i>{/if}
                                    {if $report->type == 'pk'}<span class="label label-success">Повторные клиенты</span><br /><i>Кр. доктор</i>{/if}
                                    {if $report->type == 'prolongation'}<span class="label label-info">Пролонгация</span><br /><i>Консьерж сервис</i>{/if}
                                </th>
                                {foreach $report->weeks as $week}
                                <th class="text-center">{$week['start']|date:'d.m'} - {$week['end']|date:'d.m'}</th>
                                {/foreach}
                            </tr>
                            <tr>
                                <td>Потенц. кол-во</td>
                                {foreach $report->weeks as $week}
                                <td class="text-center">{$week['total_clients']}</td>
                                {/foreach}
                            </tr>
                            <tr>
                                <td>Подключенные</td>
                                {foreach $report->weeks as $week}
                                <td class="text-center">{$week['agreed_clients']}</td>
                                {/foreach}
                            </tr>
                            <tr>
                                <td>CV Подключение</td>
                                {foreach $report->weeks as $week}
                                <td class="text-center">{$week['cv_agreed_clients']}%</td>
                                {/foreach}
                            </tr>
                            <tr>
                                <td>Списаные</td>
                                {foreach $report->weeks as $week}
                                <td class="text-center">{$week['complete_clients']}</td>
                                {/foreach}
                            </tr>
                            <tr>
                                <td>CV Списание</td>
                                {foreach $report->weeks as $week}
                                <td class="text-center">{$week['cv_complete_clients']}%</td>
                                {/foreach}
                            </tr>
                            <tr>
                                <td>Сумма</td>
                                {foreach $report->weeks as $week}
                                <td class="text-center">{$week['total_amount']}</td>
                                {/foreach}
                            </tr>
                            <tr>
                                <td>Ср.чек</td>
                                {foreach $report->weeks as $week}
                                <td class="text-center">{$week['average_amount']}</td>
                                {/foreach}
                            </tr>
                        </table>
                        {/foreach}
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