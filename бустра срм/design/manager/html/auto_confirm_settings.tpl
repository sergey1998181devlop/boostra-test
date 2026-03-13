{$meta_title = 'Настройки автоодобрений' scope=parent}

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
                    Настройки автоодобрений
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Настройки автоодобрений</li>
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
        <form id="settings_form" method="POST" >
            {if $save_success}
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Настройки успешно сохранены.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            {/if}
            <div class="card">
                <div class="card-body">
                    <h5 class="text-danger animate-flashing">В выборку попадают заявки закрытые не ранее 18.10.2022</h5>
                    <div class="row align-items-end">
                        <div class="col-md-3 col-12">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Кол-во дней, после погашения</label>
                                <div class="">
                                    <input type="number" required class="form-control" name="auto_approve[days_after_closed]" value="{$days_after_closed}" placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="form-group mb-3">
                                <label class=" col-form-label">Кол-во дней, активности одобрения</label>
                                <div class="">
                                    <input type="number" required class="form-control" name="auto_approve[days_available]" value="{$days_available}" placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-12">
                            <div class="form-group" id="dropdown-menu">
                                <label class="col-form-label">В рамках года</label>
                                <button class="btn btn-block btn-secondary dropdown-toggle" type="button" data-toggle="dropdown">
                                    Типы клиентов
                                </button>
                                <div class="dropdown-menu p-2">
                                    {for $type=1 to 8}
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox mr-sm-2 mb-3">
                                                <input {if $type|@array_search:$client_types}checked{/if} name="auto_approve[client_types][{$type}]" type="checkbox" class="custom-control-input" id="client_types_{$type}" value="{$type}"  />
                                                <label class="custom-control-label" for="client_types_{$type}">
                                                    ПК{$type}
                                                </label>
                                            </div>
                                        </div>
                                    {/for}
                                </div>
                            </div>
                        </div>
                        {*<div class="col-12">
                            <h5 class="text-danger mb-3">Перед генерацией сохраните текущие настройки!</h5>
                        </div>*}

                        <div class="col-12"><p>Автоодобрения (НК+ПК) генерируется раз в 5 мин.</p></div>

                        <div class="col-12">
                            <div class="row">
                                <div class="col-12 col-md-auto grid-stack-item" data-gs-x="0" data-gs-y="0" data-gs-width="12">
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                        {*<button type="button" onclick="generateOrders()" class="btn btn-primary"> <i class="fas fa-sync-alt"></i> Сгенерировать автоодобрения</button>*}
                                    </div>
                                </div>

                                <div class="col-12 col-md-auto">
                                    <div class="onoffswitch">
                                        <input type="checkbox" name="auto_approve[status_nk]" class="onoffswitch-checkbox"
                                               value="1" id="status_nk" {if $status_nk}checked{/if} />
                                        <label class="onoffswitch-label" for="status_nk">
                                            <span class="onoffswitch-inner"></span>
                                            <span class="onoffswitch-switch"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
    {capture name='page_scripts'}
        <script>
            $('#dropdown-menu .dropdown-menu').click(function (e) {
                e.stopPropagation();
            });

            function generateOrders() {
                $.ajax({
                    url: '{$smarty.server.REQUEST_URI}?action=generateOrders',
                    method: 'POST',
                    dataType: 'json',
                    beforeSend: function () {
                        $('.preloader').show();
                    },
                    success: function (json) {
                        $("#settings_form").prepend(json.html);
                    }
                }).done(function () {
                    $('.preloader').hide();
                });
            }
        </script>
    {/capture}
</div>