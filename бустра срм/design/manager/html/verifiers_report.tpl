{$meta_title='Общая воронка' scope=parent}

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
                    <span>Отчет по верификаторам</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отчет по верификаторам</li>
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
                        <h4 class="card-title">Отчет по верификаторам за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
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
                        <table class="table table-hover">
                            
                            <tr>
                                <th>Верификатор</th>
                                <th class="text-center">
                                    Принятые
                                    <br />
                                    <small class="text-info">НК</small> / <small class="text-warning">ПК</small>
                                </th>
                                <th class="text-center">
                                    Одобренные
                                    <br />
                                    <small class="text-info">НК</small> / <small class="text-warning">ПК</small>
                                </th>
                                <th class="text-center">
                                    Выданные
                                    <br />
                                    <small class="text-info">НК</small> / <small class="text-warning">ПК</small>
                                </th>
                                <th class="text-center">
                                    Сумма выдачи
                                    <br />
                                    <small class="text-info">НК</small> / <small class="text-warning">ПК</small>
                                </th>
                                <th class="text-center">
                                    Средний чек
                                    <br />
                                    <small class="text-info">НК</small> / <small class="text-warning">ПК</small>
                                </th>
                                <th class="text-center">
                                    Конверсия
                                    <br />
                                    <small class="text-info">НК</small> / <small class="text-warning">ПК</small>
                                </th>
                                <th class="text-center">
                                    Отказы НК
                                </th>
                                <th class="text-center">
                                    Отказы ПК
                                </th>
                            </tr>
                            
                            {foreach $report as $item}
                            <tr>
                                <td>
                                    <strong >{$item['name']}</strong>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {$item['total_count_nk']}
                                    </strong>
                                        /
                                    <strong class="text-warning">
                                        {$item['total_count_pk']}
                                    </strong>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {$item['total_count_approved_nk']}
                                    </strong>
                                        /
                                    <strong class="text-warning">
                                        {$item['total_count_approved_pk']}
                                    </strong>
                                    <br/>
                                    <strong class="text-info">
                                        {($item['total_count_approved_nk'] / $item['total_count_nk'] * 100)|round:2} %
                                    </strong>
                                    /
                                    <strong class="text-warning">
                                        {($item['total_count_approved_pk'] / $item['total_count_pk'] * 100)|round:2} %
                                    </strong>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {$item['total_count_confirmed_nk']}
                                    </strong>
                                        /
                                    <strong class="text-warning">
                                        {$item['total_count_confirmed_pk']}
                                    </strong>
                                    <br/>
                                    <strong class="text-info">
                                        {($item['total_count_confirmed_nk'] / $item['total_count_approved_nk'] * 100)|round:2} %
                                    </strong>
                                    /
                                    <strong class="text-warning">
                                        {($item['total_count_confirmed_pk'] / $item['total_count_approved_pk'] * 100)|round:2} %
                                    </strong>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {$item['total_amount_nk']}
                                    </strong>
                                        /
                                    <strong class="text-warning">
                                        {$item['total_amount_pk']}
                                    </strong>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {$item['total_avg_nk']}
                                    </strong>
                                        /
                                    <strong class="text-warning">
                                        {$item['total_avg_pk']}
                                    </strong>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        {$item['total_cnv_nk']}
                                    </strong>
                                        /
                                    <strong class="text-warning">
                                        {$item['total_cnv_pk']}
                                    </strong>
                                </td>
                                <td>
                                    <strong class="text-info">
                                        Всего: {$item['total_count_reject_nk']}
                                    </strong>
                                    <br />
                                    {if ($reasons[$item['id']])}
                                        {foreach $reasons[$item['id']] as $reason}
                                            <small class="text-info clearfix">
                                                {$reasons_all[$reason['reason_id']]['admin_name']} - {$reason['cnt_nk']}
                                            </small>
                                        {/foreach}
                                    {/if}
                                </td>
                                <td>
                                    <strong class="text-info">
                                        Всего: {$item['total_count_reject_pk']}
                                    </strong>
                                    <br />
                                    {if ($reasons[$item['id']])}
                                        {foreach $reasons[$item['id']] as $reason}
                                            <small class="text-info clearfix">
                                                {$reasons_all[$reason['reason_id']]['admin_name']} - {$reason['cnt_pk']}
                                            </small>
                                        {/foreach}
                                    {/if}
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