{$meta_title = 'Настройки постбеков' scope=parent}

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
                    Настройки постбеков
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Настройки постбеков</li>
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
                    <h3>Настройте постбеки</h3>
                    <div class="row">
                        <div class="col-12 col-md-3">
                            <dl>
                                <dt>bonon</dt>
                                <dd>
                                    <div class="form-group">
                                        <label for="postback[bonon][amount]">Ставка</label>
                                        <input value="{$settings->postback.bonon.amount}" type="number" class="form-control" id="postback[bonon][amount]" name="postback[bonon][amount]">
                                        <small class="form-text text-muted">Укажите ставку при выдачи</small>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-12 col-md-3">
                            <dl>
                                <dt>kekas</dt>
                                <dd>
                                    <div class="form-group">
                                        <label for="postback[kekas][amount]">Ставка</label>
                                        <input value="{$settings->postback.kekas.amount}" type="number" class="form-control" id="postback[kekas][amount]" name="postback[kekas][amount]">
                                        <small class="form-text text-muted">Укажите ставку при выдачи</small>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-12 col-md-3">
                            <dl>
                                <dt>tpartners</dt>
                                <dd>
                                    <div class="form-group">
                                        <label for="postback[tpartners][amount]">Ставка</label>
                                        <input value="{$settings->postback.tpartners.amount}" type="number" class="form-control" id="postback[tpartners][amount]" name="postback[tpartners][amount]">
                                        <small class="form-text text-muted">Укажите ставку при выдачи</small>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-12 col-md-3">
                            <dl>
                                <dt>tbank</dt>
                                <dd>
                                    <div class="form-group">
                                        <label for="postback[tbank][amount]">Ставка</label>
                                        <input value="{$settings->postback.tbank.amount}" type="number" class="form-control" id="postback[tbank][amount]" name="postback[tbank][amount]">
                                        <small class="form-text text-muted">Укажите ставку при выдачи</small>
                                    </div>
                                </dd>
                            </dl>
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
        <!-- End Page Content -->
        <!-- ============================================================== -->
    </div>
    <!-- ============================================================== -->
    <!-- End Container fluid  -->
    <!-- ============================================================== -->
    {include file='footer.tpl'}
    <!-- ============================================================== -->
</div>
