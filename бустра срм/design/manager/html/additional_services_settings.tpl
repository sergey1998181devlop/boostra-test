{$meta_title = 'Настройки индивидуального рассмотрения' scope=parent}

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
                    Настройки дополните услуг
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Настройки индивидуального рассмотрения</li>
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
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Активировать</label>
                                <div class="">
                                    <div class="form-check form-check-inline">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="individual_enable" class="custom-control-input" name="additional_services_settings[enabled]" value="1" {if $settings->additional_services_settings['enabled']}checked="true"{/if} placeholder="">
                                            <label class="custom-control-label" for="individual_enable">Да</label>
                                        </div>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="individual_disable" class="custom-control-input" name="additional_services_settings[enabled]" value="0" {if !$settings->additional_services_settings['enabled']}checked="true"{/if} placeholder="">
                                            <label class="custom-control-label" for="individual_disable">Нет</label>
                                        </div>
                                    </div>                                    
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Срок кредита без скидки</label>
                                <div class="">
                                    <input type="text" class="form-control" name="additional_services_settings[configured_term]" value="{$settings->additional_services_settings['configured_term']}" placeholder="">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Установленная скидка</label>
                                <div class="">
                                    <input type="text" class="form-control" name="additional_services_settings[amount_of_discount]" value="{$settings->additional_services_settings['amount_of_discount']}" placeholder="">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Ставка после скидки</label>
                                <div class="">
                                    {1 -  (1 * $settings->additional_services_settings['amount_of_discount'] / 100)}
                                </div>
                            </div>
                        </div>

                        <!--
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">TINKOFF_TERMINAL_KEY</label>
                                <div class="">
                                    <input type="text" class="form-control" name="additional_services_settings[tinkoff_terminal_key]" value="{$settings->additional_services_settings['tinkoff_terminal_key']}" placeholder="">
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class=" col-form-label">TINKOFF_SECRET_KEY</label>
                                <div class="">
                                    <input type="text" class="form-control" name="additional_services_settings[tinkoff_secret_key]" value="{$settings->additional_services_settings['tinkoff_secret_key']}" placeholder="">
                                </div>
                            </div>
                        </div>
                        -->
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