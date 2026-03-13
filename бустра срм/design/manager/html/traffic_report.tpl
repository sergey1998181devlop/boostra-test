{$meta_title='Отчет по трафику' scope=parent}

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
                    <span>Отчет по трафику</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отчет по трафику</li>
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
                        <h4 class="card-title">Отчет по трафику за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
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
                        <div class="row">
                            <div class="col-md-9">
                                <table class="table table-hover  table-bordered">
                                    
                                    <tr class="bg-grey text-center">
                                        <th>Период</th>
                                        <th>Источник</th>
                                        <th>Переходы</th>
                                        <th>Заявки</th>
                                        <th>Выдано</th>
                                        <th>Оплаты</th>
                                        <th>CV в заявку</th>
                                        <th>CV в выдачу</th>
                                        <th>EPC</th>
                                    </tr>
                                    
                                    <tr class="text-center">
                                        <td>{if $date_from}{$date_from|date}{/if} {if $date_from!=$date_to}- {$date_to|date}{/if}</td>
                                        <td>leadgid</td>
                                        <td>{$report->visitors}</td>
                                        <td>{$report->orders}</td>
                                        <td>{$report->getted}</td>
                                        <td>{$report->total_paid}</td>
                                        <td>{($report->orders/$report->visitors*100)|round:2}%</td>
                                        <td>{($report->getted/$report->visitors*100)|round:2}%</td>
                                        <td>{($report->total_paid/$report->visitors)|round:2}</td>
                                    </tr>
                                    
                                </table>    
                            </div>
                            <div class="col-md-3">
                                <table class="table table-bordered">
                                    <tr class="bg-grey text-center">
                                        <th>Скорбал</th>
                                        <th>Кол-во</th>
                                        <th>Ставка</th>
                                        <th>Сумма</th>
                                    </tr>
                                    
                                    {foreach $total_report as $tr}
                                    <tr class="text-center">
                                        <td>{$tr['from']} - {$tr['to']}</td>
                                        <td>{$tr['count_totals']}</td>
                                        <td>{$tr['price']}</td>
                                        <td>{$tr['price_totals']}</td>
                                    </tr>
                                    {/foreach}

                                    <tr class="text-center">
                                        <td>Итого</td>
                                        <td><strong>{$report->getted}</strong></td>
                                        <td></td>
                                        <td><strong>{$report->total_paid}</strong></td>
                                    </tr>

                                </table>
                            </div>
                        </div>      
                        
                        
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