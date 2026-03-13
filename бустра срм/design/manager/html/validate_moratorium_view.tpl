{$meta_title='Валидация контактов' scope=parent}

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row page-titles">
            <div class="col-md-6 col-8 align-self-center">
                <h3 class="text-themecolor mb-0 mt-0">
                    <i class="mdi mdi-file-chart"></i>
                    <span>Валидация контактов</span>
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Валидация контактов</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Валидация контактов
                        </h4>
                        <form id="report_form" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-auto">
                                    <div class="btn-group">
                                        <label for="validate_file">Excel файл для валидации</label>
                                        <input type="file" class="form-control-file" name="validate_file" id="validate_file">
                                    </div>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <div class="btn-group">
                                        <button type="submit" class="btn btn-primary"><i class="ti-reload"></i> Проверить и выгрузить</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Валидация контактов 2.0
                        </h4>
                        <form id="report_form" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-auto">
                                    <div class="btn-group">
                                        <label for="validate_file">Excel файл для валидации</label>
                                        <input type="file" class="form-control-file" name="validate_file_second" id="validate_file_second">
                                    </div>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <div class="btn-group">
                                        <button type="submit" class="btn btn-primary"><i class="ti-reload"></i> Проверить и выгрузить</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <!-- Column -->
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Валидация контактов 3.0 (балл скористы)
                        </h4>
                        <form id="report_form" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-auto">
                                    <div class="btn-group">
                                        <label for="validate_file">Excel файл для валидации</label>
                                        <input type="file" class="form-control-file" name="validate_file_third" id="validate_file_third">
                                    </div>
                                </div>
                                <div class="col-6 col-md-auto">
                                    <div class="btn-group">
                                        <button type="submit" class="btn btn-primary"><i class="ti-reload"></i> Проверить и выгрузить</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        {if $error}
            <div class="text-center">
                <h4 class="text-danger">{$error}</h4>
            </div>
        {/if}
    </div>
    {include file='footer.tpl'}
</div>
