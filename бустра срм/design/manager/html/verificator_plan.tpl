{$meta_title = 'Планы сотрудников' scope=parent}

{capture name='page_scripts'}


{/capture}

{capture name='page_styles'}

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
                    Планы сотрудников
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Планы</li>
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
        <!-- Row -->
        <form class="" method="POST" >
            
            <div class="card">
                <div class="card-body">

                    
                    <div class="row">
                        <div class="col-12">
                            <h3 class="box-title">
                                Дневной план верификаторам по выдаче 
                            </h3>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Новые клиенты</label>
                                <div class="">
                                    <input type="text" class="form-control" name="verificator_daily_plan_nk" value="{$verificator_daily_plan_nk}" placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Повторные клиенты</label>
                                <div class="">
                                    <input type="text" class="form-control" name="verificator_daily_plan_pk" value="{$verificator_daily_plan_pk}" placeholder="">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                    <div class="row">
                        <div class="col-12">
                            <h3 class="box-title">
                                Дневной план по контакт-центру
                            </h3>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Пролонгации</label>
                                <div class="">
                                    <input type="text" class="form-control" name="cc_pr_prolongation_plan" value="{$cc_pr_prolongation_plan}" placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Закрытия</label>
                                <div class="">
                                    <input type="text" class="form-control" name="cc_pr_close_plan" value="{$cc_pr_close_plan}" placeholder="">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                </div>
            </div>
            
        
            <div class="col-12 grid-stack-item" data-gs-x="0" data-gs-y="0" data-gs-width="12">
                <div class="form-actions">
                    <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                </div>
            </div>
        </form>
        <!-- Row -->
        <!-- ============================================================== -->
        <!-- End PAge Content -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    {include file='footer.tpl'}
    <!-- ============================================================== -->
</div>