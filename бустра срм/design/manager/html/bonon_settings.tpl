{$meta_title = 'Настройки Bonon' scope=parent}

{capture name='page_styles'}
<link href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.css">
<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css">
{/capture}

{capture name='page_scripts'}

<script src="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
<script src="design/{$settings->theme|escape}/assets/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js"></script>

<script src="design/{$settings->theme|escape}/js/apps/bonon_settings.app.js"></script>

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
                    Настройки Bonon
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Главная</a></li>
                    <li class="breadcrumb-item active">
                        Настройки Bonon
                    </li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Настройка продаваемых источиков</h4>
                        <h6 class="card-subtitle">Карты отказных НК из указанных источников могут быть проданы в Bonon.</h6>
                        <h6 class="card-subtitle">Настройки для органики не действуют с 10 до 17 МСК. В этот промежуток органика всегда идёт по стандартному флоу без продажи.</h6>
                        <div class="row">
                            <div class="col-12 col-md-8 col-lg-6 mt-auto mb-auto">
                                <div class="custom-control custom-checkbox mr-sm-2">
                                    <input name="bonon_enabled" type="checkbox" class="custom-control-input js-settings-checkbox" id="bonon_enabled" value="1" {if $bonon_enabled}checked{/if} />
                                    <label class="custom-control-label" for="bonon_enabled">
                                        Продажа карт отказных НК клиентов (Глобальный вкл/выкл)
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 col-lg-6 align-self-center">
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
                                        <th><span class="text-muted">id</span></th>
                                        <th>Лидген <span class="text-muted">(utm_source)</span></th>
                                        <th>Вебмастер <span class="text-muted">(utm_medium)</span></th>
                                        <th>Срабатывание</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody id="table-body">

                                    {foreach $sources as $id => $source}
                                    <tr class="js-item">
                                        <td>
                                            <div class="js-visible-view js-text-id">
                                                <span class="text-muted">{$id}</span>
                                            </div>
                                            <div class="js-visible-edit" style="display:none">
                                                <span class="text-muted">{$id}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="js-visible-view js-text-utm_source">
                                                {if $source->utm_source == '*'}Любой <span class="text-muted">(*)</span>{else}{$source->utm_source}{/if}
                                            </div>
                                            <div class="js-visible-edit" style="display:none">
                                                <input type="text" class="form-control form-control-sm" name="utm_source" value="{$source->utm_source|escape}" />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="js-visible-view js-text-utm_medium">
                                                {if $source->utm_medium == '*'}Любой <span class="text-muted">(*)</span>{else}{$source->utm_medium}{/if}
                                            </div>
                                            <div class="js-visible-edit" style="display:none">
                                                <input type="text" class="form-control form-control-sm" name="utm_medium" value="{$source->utm_medium|escape}" />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="js-visible-view js-text-chance">
                                                {if $source->chance == 0}Всегда{elseif $source->chance == 1}50%{else}<span class="text-danger">На паузе</span>{/if}
                                            </div>
                                            <div class="js-visible-edit" style="display:none">
                                                <select name="chance" class="form-control input-sm">
                                                    <option value="0" {if $source->chance == 0}selected{/if}>Всегда</option>
                                                    <option value="1" {if $source->chance == 1}selected{/if}>50%</option>
                                                    <option value="2" {if $source->chance == 2}selected{/if}>На паузе</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <div class="js-visible-view">
                                                <a href="#" class="text-info js-edit-item" title="Редактировать"><i class=" fas fa-edit"></i></a>
                                                <a href="#" class="text-danger js-delete-item" title="Удалить"><i class="far fa-trash-alt"></i></a>
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
                <h4 class="modal-title">Добавить источник</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_item">
                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="utm_source" class="control-label text-white">utm_source (Лидген)</label>
                        <input type="text" class="form-control" name="utm_source" id="utm_source" value="" />
                        <span class="info">Укажите "*" если хотите выбрать ВСЕ лидгены.</span>
                    </div>

                    <div class="form-group">
                        <label for="utm_medium" class="control-label text-white">utm_medium (Вебмастер)</label>
                        <input type="text" class="form-control" name="utm_medium" id="utm_medium" value="" />
                        <span class="info">Укажите "*" если хотите выбрать ВСЕХ вебмастеров в рамках лидгена.</span>
                    </div>

                    <div class="form-group">
                        <label for="chance" class="control-label text-white">Срабатывание</label>
                        <select name="chance" class="form-control input-sm">
                            <option value="0">Всегда</option>
                            <option value="1">50%</option>
                            <option value="2">На паузе</option>
                        </select>
                    </div>

                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>