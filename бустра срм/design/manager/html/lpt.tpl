{$meta_title='ЛПТ Лиды' scope=parent}

{capture name='page_scripts'}

    <script src="design/{$settings->theme|escape}/assets/plugins/moment/moment.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <!-- Date range Plugin JavaScript -->
    <script src="design/{$settings->theme|escape}/assets/plugins/timepicker/bootstrap-timepicker.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/daterangepicker/daterangepicker.js"></script>

    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/cctasks.app.js"></script>
    <script type="text/javascript" src="design/{$settings->theme|escape}/js/apps/movements.app.js"></script>

{/capture}

{capture name='page_styles'}
    <!-- Date picker plugins css -->
    <link href="design/{$settings->theme|escape}/assets/plugins/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <!-- Daterange picker plugins css -->
    <link href="design/{$settings->theme|escape}/assets/plugins/timepicker/bootstrap-timepicker.min.css" rel="stylesheet">
    <link href="design/{$settings->theme|escape}/assets/plugins/daterangepicker/daterangepicker.css" rel="stylesheet">

    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid.min.css" />
    <link type="text/css" rel="stylesheet" href="design/{$settings->theme|escape}/assets/plugins/jsgrid/jsgrid-theme.min.css" />
    <style>
        .jsgrid-table { margin-bottom:0}
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
                <h3 class="text-themecolor mb-0 mt-0"><i class="mdi mdi-closed-caption"></i> лпт лиды</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">лиды</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">

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
                        <h4 class="card-title">Мои задачи</h4>
                        <div id="basicgrid" class="jsgrid" style="position: relative; width: 100%;">
                            <div class="jsgrid-grid-header jsgrid-header-scrollbar">
                                <table class="jsgrid-table table table-striped table-hover">
                                    <tr>
                                        <th scope="col">id</th>
                                        <th scope="col">lpt_id</th>
                                        <th scope="col">user_balance_id</th>
                                        <th scope="col">json</th>
                                        <th scope="col">status</th>
                                        <th scope="col">tag</th>
                                        <th scope="col">custom_array</th>
                                        <th scope="col">created_at</th>
                                        <th scope="col">updated_at</th>
                                    </tr>
                                </table>
                            </div>
                            <div class="jsgrid-grid-body">
                                <table class="jsgrid-table table table-striped table-hover ">
                                    <tbody>
                                    {foreach $lpt_collection as $lpt}
                                        <tr>
                                            <td>{$lpt->id}</td>
                                            <td>{$lpt->lpt_id}</td>
                                            <td>{$lpt->user_balance_id}</td>
                                            <td><p title="{$lpt->json|escape}">json</p></td>
                                            <td>{$lpt->status}</td>
                                            <td>{$lpt->tag}</td>
                                            <td><p title="{$lpt->custom_array|escape}">все кастомные поля</p></td>
                                            <td>{$lpt->created_at}</td>
                                            <td>{$lpt->updated_at}</td>
                                        </tr>
                                    {/foreach}
                                    </tbody>
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