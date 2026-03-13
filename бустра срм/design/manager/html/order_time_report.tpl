{$meta_title='Время обработки заявок' scope=parent}

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
        
        $('.js-scorista-open').click(function(e){
            e.preventDefault();
            
            var index = $(this).data('index');
            
            if ($(this).hasClass('active'))
            {
                $('.js-scorista-'+index).removeClass('open');
                $(this).removeClass('active');
                $(this).find('.fas').removeClass('fa-caret-down').addClass('fa-caret-down')
            }
            else
            {
                $('.js-scorista-'+index).addClass('open');
                $(this).find('.fas').removeClass('fa-caret-down').addClass('fa-caret-down')
                $(this).addClass('active');
            }
            
            
        });
    })
    </script>
{/capture}

{capture name='page_styles'}
    <link href="design/manager/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker plugins css -->
    <link href="design/manager/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/manager/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">
{/capture}

{literal}
    <style>
        tr.small td {
            padding: 0.25rem;
            font-size: 12px;
        }
        .table th {
            vertical-align: middle;
            border: 1px solid !important;
        }
        thead.position-sticky {
            top: 0;
            background-color: #272c33;
        }
    </style>
{/literal}

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
                    <span>Время обработки заявок</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Время обработки заявок</li>
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
                        <h4 class="card-title">
                            Время обработки одобренных заявок за период {if $date_from}{$date_from|date} - {$date_to|date}{/if}
                            {if $filter_source}
                                {foreach $filter_source as $fs}
                                    {$fs}{if !$fs@last}, {/if}
                                {/foreach}
                            {/if}
                        </h4>
                        <form>
                            <div class="row">
                                <div class="col-6 col-md-2">
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
                                    
                                    <div class="mb-3">
                                        <select class="form-control" name="filter_manager">
                                            <option value="" {if !$filter_manager}selected{/if}>Все менеджеры</option>
                                            {foreach $managers as $m}
                                            {if in_array($m->role, ['verificator', 'edit_verificator'])}
                                            <option value="{$m->id}" {if $filter_manager == $m->id}selected{/if}>{$m->name}</option>
                                            {/if}
                                            {/foreach}
                                        </select>
                                    </div>
                                    
                                </div>
                                <div class="col-6 col-md-2">
                                    <div class="mb-3">
                                        <select class="form-control" name="filter_source">
                                            <option value="" {if !$filter_source}selected{/if}>Все источники</option>
                                            {foreach $sources as $s}
                                            <option value="{$s}" {if $filter_source == $s}selected{/if}>{$s}</option>
                                            {/foreach}
                                        </select>                                        
                                    </div>
                                </div>
                                <div class="col">
                                    <button type="submit" class="btn btn-info">Сформировать</button>
                                </div>
                            </div>
                            
                        </form>
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered table-hover table-stripped">
                                        <thead class="position-sticky small">
                                            <tr>
                                                <th rowspan="2">Верификатор</th>
                                                <th rowspan="2">Время обработки заявок</th>
                                                <th rowspan="2">Итого среднее время обработки</th>
                                                <th rowspan="2">Количество заявок в фильтре</th>
                                                <th colspan="2">Сложная колонка</th>
                                            </tr>
                                            <tr>
                                                <th>НК</th>
                                                <th>ПК</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {if $manager_data}
                                                {foreach $manager_data as $manager}
                                                    <tr>
                                                        <td>{$manager->manager->name} <b>({$manager->manager->id})</b></td>
                                                        <td>{$manager->all_work_time}</td>
                                                        <td>{$manager->avg_work_time}</td>
                                                        <td>
                                                            {array_sum($manager->total_in_processed_orders)} / {array_sum($manager->total_finished_orders)}
                                                            {foreach $manager->waiting_orders as $status_key => $waiting_order}
                                                                <br/>
                                                                <small class="text-primary">
                                                                    {$statuses[$status_key]}: {$waiting_order}
                                                                </small>
                                                            {/foreach}
                                                        </td>
                                                        <td class="small">
                                                            {foreach $manager->percent_new_client as $key_percent => $value_percent}
                                                                <b>{$key_percent} мин - </b> {$value_percent}% <br/>
                                                            {/foreach}
                                                        </td>
                                                        <td class="small">
                                                            {foreach $manager->percent_repeat_client as $key_percent => $value_percent}
                                                                <b>{$key_percent} мин - </b> {$value_percent}% <br/>
                                                            {/foreach}
                                                        </td>
                                                    </tr>
                                                {/foreach}
                                            {else}
                                                <tr>
                                                    <td colspan="6">
                                                        <h4 class="text-danger text-center">Ничего не найдено</h4>
                                                    </td>
                                                </tr>
                                            {/if}
                                        </tbody>
                                        {if $totals}
                                            <tfoot>
                                                <tr class="text-success bg-dark">
                                                    <td>Итого</td>
                                                    <td>{$totals->total_all_time}</td>
                                                    <td>{$totals->total_avg_work_time}</td>
                                                    <td>
                                                        {$totals->total_in_processed_orders} / {$totals->total_finished_orders} <br/>
                                                        {foreach $totals->total_waiting_orders as $status_key => $total_waiting_order}
                                                            <br/>
                                                            <small class="text-primary">
                                                                {$statuses[$status_key]}: {$total_waiting_order}
                                                            </small>
                                                        {/foreach}
                                                    </td>
                                                    <td class="small">
                                                        {foreach $totals->percent_new_client as $key_percent => $value_percent}
                                                            <b>{$key_percent} мин - </b> {$value_percent}% <br/>
                                                        {/foreach}
                                                    </td>
                                                    <td class="small">
                                                        {foreach $totals->percent_repeat_client as $key_percent => $value_percent}
                                                            <b>{$key_percent} мин - </b> {$value_percent}% <br/>
                                                        {/foreach}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        {/if}
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