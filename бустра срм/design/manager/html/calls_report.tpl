{$meta_title='Отчет по звонкам' scope=parent}

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
                    <span>Отчет по звонкам</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отчет по звонкам</li>
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
                        <h4 class="card-title">Звонки за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}</h4>
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
                        
                        {if $message_error}
                        <div class="alert alert-danger">
                            {$message_error}
                        </div>
                        {/if}
                            
                        
                        {if $verificator_calls}
                        <div>
                            <h2>Отдел продаж</h2>
                            <table class="table  table-hover table-bordered">
                                <tr class="bg-grey">
                                    <th>Менеджер</th>
                                    <th>Минут</th>
                                    <th>Звонков</th>
                                    <th>Недозвон</th>
                                    <th class="text-right">Первый звонок</th>
                                    <th class="text-right">Последний звонок</th>
                                </tr>
                                {foreach $verificator_calls as $call}
                                <tr>
                                    <td>{$managers[$call->id]->name_1c|escape}</td>
                                    <td class="text-center">{($call->total_seconds/60)|round:1}</td>
                                    <td class="text-center">{$call->total_calls}</td>
                                    <td class="text-center">{$call->total_missings}</td>
                                    <td class="text-right">{$call->first_call|date} {$call->first_call|time}</td>
                                    <td class="text-right">{$call->last_call|date} {$call->last_call|time}</td>
                                </tr>
                                {/foreach}
                                <tr class="bg-grey">
                                    <td>Итог по отделу</td>
                                    <td class="text-center"><strong>{($verificator_total_seconds/60)|round:1}</strong></td>
                                    <td class="text-center"><strong>{$verificator_total_calls}</strong></td>
                                    <td class="text-center"><strong>{$verificator_total_missings}</strong></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </table>
                            
                            <hr class="mt-4 mb-4" />
                        </div>
                        {/if}
                        
                        {if $cc_calls}
                        <div>
                            <h2>Отдел клиентского сервиса</h2>
                            <table class="table  table-hover table-bordered">
                                <tr class="bg-grey">
                                    <th>Менеджер</th>
                                    <th>Минут</th>
                                    <th>Звонков</th>
                                    <th>Недозвон</th>
                                    <th class="text-right">Первый звонок</th>
                                    <th class="text-right">Последний звонок</th>
                                </tr>
                                {foreach $cc_calls as $call}
                                <tr>
                                    <td>{$managers[$call->id]->name_1c|escape}</td>
                                    <td class="text-center">{($call->total_seconds/60)|round:1}</td>
                                    <td class="text-center">{$call->total_calls}</td>
                                    <td class="text-center">{$call->total_missings}</td>
                                    <td class="text-right">{$call->first_call|date} {$call->first_call|time}</td>
                                    <td class="text-right">{$call->last_call|date} {$call->last_call|time}</td>
                                </tr>
                                {/foreach}
                                <tr class="bg-grey">
                                    <td>Итог по отделу</td>
                                    <td class="text-center"><strong>{($cc_total_seconds/60)|round:1}</strong></td>
                                    <td class="text-center"><strong>{$cc_total_calls}</strong></td>
                                    <td class="text-center"><strong>{$cc_total_missings}</strong></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </table>
                        </div>
                        {/if}
                        
                        
                        
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