{$meta_title='Отвалы отчет' scope=parent}

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
                timePicker: true,
                timePicker24Hour: true,
                timePickerIncrement: 1,
                locale: {
                    format: 'YYYY.MM.DD HH:mm'
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
                    <span>Отвалы отчет</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Отвалы отчет</li>
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
                            Отвалы отчет за период
                            {if $date_from}{$date_from|date} - {$date_to|date}{/if}
                            {if $filter_manager_id}{$managers[$filter_manager_id]->name|escape}{/if}
                        </h4>
                        <form>
                            <div class="row">
                                <div class="col-4 col-md-3">
                                    <div class="input-group mb-3">
                                        <input type="text" name="daterange" class="form-control daterange" value="{if $from && $to}{$from}-{$to}{/if}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <span class="ti-calendar"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <select name="manager_id" class="form-control">
                                        <option value="" {if !$filter_manager_id}selected{/if}>Все</option>
                                        {foreach $managers as $m}
                                            {if !$m->blocked && in_array($m->role, ['contact_center','contact_center_plus'])}
                                                <option value="{$m->id}" {if $filter_manager_id==$m->id}selected{/if}>{$m->name|escape}</option>
                                            {/if}
                                        {/foreach}
                                    </select>
                                </div>
                                <div class="col-4 col-md-3">
                                    <button type="submit" class="btn btn-info">Сформировать</button>
                                </div>
                            </div>

                        </form>
                        <table class="table table-bordered">

                            <tr class="table-gray">
                                <th>Менеджер</th>
                                <th class="text-center">Всего</th>
                                <th class="text-center">Взято в работу</th>
                                <th class="text-center">Не обработано</th>
                                <th class="text-center">Заявки</th>
                                <th class="text-center">Конверсия</th>
                                <th class="text-center">Завершено из взятых в работу</th>
                                <th class="text-center">Эффективность сотрудника</th>
                                <th class="text-center">Выдано займов</th>
                                <th class="text-center">Сумма</th>
                            </tr>

                            {foreach $report as $day}
                                <tr class="table-gray-light">
                                    <td colspan="10" class="pt-1 pb-1" style="background: #3e3d46">
                                        <strong class="text-info">{$day.date}</strong>
                                    </td>
                                </tr>
                                {foreach $day.managers as $manager}
                                    <tr>
                                        <td>{$manager.manager_name|escape}</td>
                                        <td class="text-center">{$day.totals}</td>
                                        <td class="text-center">{$manager.inProgress}</td>
                                        <td class="text-center">{$day.unhandled}</td>
                                        <td class="text-center">{$day.totalCompleted}</td>
                                        <td class="text-center">{$day.conversion} %</td>
                                        <td class="text-center">{$manager.completed}</td>
                                        <td class="text-center">{$manager.managerEfficiency} %</td>
                                        <td class="text-center">{$manager.loans}</td>
                                        <td class="text-center">{$manager.amount} руб.</td>
                                    </tr>
                                {/foreach}
                            {/foreach}

                        </table>
                        <div class="text-danger">
                            <small>* Корректные данные доступны начиная с 08.05.2022</small>
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