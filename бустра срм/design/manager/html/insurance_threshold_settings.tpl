{$meta_title = 'Настройки порогов выданных страховок' scope=parent}

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
                    Настройки порогов выданных страховок
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Настройки порогов выданных страховок</li>
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
                            </h3>
                        </div>
                        <div class="col-md-6">
                            {$i = 1}
                            {foreach $settings->insurance_threshold_settings as $ip => $value}
                            <div class="form-group mb-3 row">
                                <label class="col-4 col-form-label">
                                    {$ip} ({$companies[$ip]['title']|escape})
                                </label>
                                <div class="col-4">
                                    <input type="text" class="form-control" name="insurance_threshold_settings[{$ip}]" value="{$settings->insurance_threshold_settings[$ip]}" placeholder="">
                                </div>
                                <div class="col-4">
                                    <input type="text" class="form-control" name="order[{$ip}]" value="{$i}" placeholder="">
                                </div>
                            </div>
                            {$i = $i + 1}
                            {/foreach}
                            
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