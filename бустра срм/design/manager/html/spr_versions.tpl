{$meta_title = 'Версии СПР' scope=parent}

{capture name='page_styles'}
    <link href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css">
{/capture}

{capture name='page_scripts'}

    <script src="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js"></script>

    <script src="design/{$settings->theme|escape}/js/apps/spr_versions.app.js"></script>

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
                    Версии СПР
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Главная</a></li>
                    <li class="breadcrumb-item active">
                        Версии СПР
                    </li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Версии системы принятия решений</h4>
                        <div class="row">
                            <div class="col-12 align-self-end">
                                <button class="btn float-right hidden-sm-down btn-success js-open-add-modal">
                                    <i class="mdi mdi-plus-circle"></i> Добавить
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive m-t-40">
                            <div class="dataTables_wrapper container-fluid dt-bootstrap4 no-footer">
                                <table id="config-table" class="table display table-striped dataTable">
                                    <thead>
                                    <tr>
                                        <th>Версия</th>
                                        <th>Описание</th>
                                        <th>Дата</th>
                                        <th>Ответственный</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody id="table-body">

                                    {foreach $versions as $version}
                                        <tr class="js-item">
                                            <td>
                                                <div class="js-visible-view js-text-id">
                                                    <span class="text">{$version->id}</span>
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <span class="text-muted">{$version->id}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-description">
                                                    {$version->description}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <textarea type="text" class="form-control form-control-sm"
                                                              name="description" rows="5"
                                                    >{$version->description|escape}</textarea>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-created">
                                                    <span class="text">{$version->created}</span>
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <span class="text-muted">{$version->created}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-manager_id">
                                                    <span class="text">
                                                        <a target="_blank" href="/manager/{$version->manager_id}"> {$manager_names[$version->manager_id]}</a>
                                                    </span>
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <span class="text-muted">{$manager_names[$version->manager_id]}</span>
                                                </div>
                                            </td>
                                            <td class="text-right">
                                                <div class="js-visible-view">
                                                    <a href="#" class="text-info js-edit-item" title="Редактировать"><i class=" fas fa-edit"></i></a>
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <a href="#" class="text-success js-confirm-edit-item" title="Сохранить"><i class="fas fa-check-circle"></i></a>
                                                    <a href="#" class="text-danger js-cancel-edit-item" title="Отменить"><i class="fas fa-times-circle"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    {/foreach}

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    {include file='footer.tpl'}

</div>

<div id="modal_add_item" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Добавить версию</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_item">
                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="description" class="control-label text-white">Описание версии</label>
                        <textarea type="text" class="form-control" name="description" id="description" rows="5"></textarea>
                        <span class="info">Сюда можно приложить ссылку на задачу и полную документацию.</span>
                    </div>

                    <div class="form-group text-white">Номер версии назначится автоматически</div>

                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>