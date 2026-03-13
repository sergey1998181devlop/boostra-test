{$meta_title = 'Настройки смс взыскание' scope=parent}

{capture name='page_styles'}

    <!--nestable CSS -->
    <link href="design/{$settings_notice_sms_approve->theme}/assets/plugins/nestable/nestable.css" rel="stylesheet" type="text/css" />

    <style>
        .onoffswitch {
            display:inline-block!important;
            vertical-align:top!important;
            width:60px!important;
            text-align:left;
        }
        .onoffswitch-switch {
            left:38px!important;
            border-width:1px!important;
        }
        .onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
            right:0px!important;
        }
        .onoffswitch-label {
            margin-bottom:0!important;
            border-width:1px!important;
        }
        .onoffswitch-inner::after,
        .onoffswitch-inner::before {
            height:18px!important;
            line-height:18px!important;
        }
        .onoffswitch-switch {
            width:20px!important;
            margin:1px!important;
        }
        .onoffswitch-inner::before {
            content:'ВКЛ'!important;
            padding-left: 10px!important;
            font-size:10px!important;
        }
        .onoffswitch-inner::after {
            content:'ВЫКЛ'!important;
            font-size:10px!important;
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
                    Настройки
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Настройки смс взыскание</li>
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
        <div class="row grid-stack" data-gs-width="12" data-gs-animate="yes">
            <div class="col-md-12">
                <div class="tab-content">
                    <div id="sms" class="tab-pane active" role="tabpanel">
                        <form class="" method="POST" >
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group row align-items-center">
                                        <div class="col-md-6">
                                            <label class="btn btn-primary">СМС-ЛК</label>
                                            <textarea name="sms-lk" class="form-control" placeholder="Поле для ввода текста">{$templates['sms-lk']->template}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="row">
                                        <div class="col-12 grid-stack-item" data-gs-x="0" data-gs-y="0" data-gs-width="12">
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <form class="" method="POST" >
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group row align-items-center">
                                        <div class="col-md-6">
                                            <label class="btn btn-primary">СМС-пролонгация</label>
                                            <textarea name="sms-prolongation" class="form-control" placeholder="Поле для ввода текста">{$templates['sms-prolongation']->template}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="row">
                                        <div class="col-12 grid-stack-item" data-gs-x="0" data-gs-y="0" data-gs-width="12">
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <form class="" method="POST" >
                            <div class="card">
                                <div class="card-body">
                                    <div class="form-group row align-items-center">
                                        <div class="col-md-6">
                                            <label class="btn btn-primary">СМС-оплата</label>
                                            <textarea name="sms-payment" class="form-control" placeholder="Поле для ввода текста">{$templates['sms-payment']->template}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="row">
                                        <div class="col-12 grid-stack-item" data-gs-x="0" data-gs-y="0" data-gs-width="12">
                                            <div class="form-actions">
                                                <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
            <span style="color: #01c0c8">Массовая отправка для минусовых дней </span>
                <div class="onoffswitch">
                    <input type="checkbox" name="" class="onoffswitch-checkbox" value="" id="status_sms" {if $templates['sms-prolongation']->status}checked{/if} />
                    <label class="onoffswitch-label" for="status_sms">
                        <span class="onoffswitch-inner"></span>
                        <span class="onoffswitch-switch"></span>
                    </label>
                </div>
            </div>
        </div>
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


{capture name='page_scripts'}
    <script>
        $(".onoffswitch input[type='checkbox']").on('change', function () {
            let value = $(this).prop('checked') ? 1 : 0
            $.ajax({
                type: 'POST',
                url:'ajax/update_sms_minus_days.php',
                data: { value: value},
                success:function (resp) {
                    console.log(resp)
                }
            })
        });
    </script>
{/capture}

