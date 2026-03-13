{$meta_title = 'Настройки продаж отказного трафика' scope=parent}

{capture name='page_styles'}
<link href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.css">
<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css">
{/capture}

{capture name='page_scripts'}

<script src="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
<script src="design/{$settings->theme|escape}/assets/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js"></script>

<script src="design/{$settings->theme|escape}/js/apps/bonon_settings.app.js?v=20251124"></script>

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
                    Настройки продаж отказного трафика
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">Главная</a></li>
                    <li class="breadcrumb-item active">Настройки продаж отказного трафика</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                <select name="sites_list" class="form-control input-sm">
                    {foreach $sites_list as $site}
                    <option value="{$site->site_id}" {if $site->site_id == $current_site}selected="true"{/if}>
                        {$site->title}
                    </option>
                    {/foreach}
                </select>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- End Bread crumb and right sidebar toggle -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- Start Page Content -->
        <!-- ============================================================== -->
        <!-- Row -->
            <ul class="mt-2 nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#general_view" role="tab" aria-selected="true">
                        <span class="hidden-sm-up"><i class="ti-home"></i></span>
                        <span class="hidden-xs-down">Общие настройки</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#shop_view" role="tab" aria-selected="true">
                        <span class="hidden-sm-up"><i class="ti-home"></i></span>
                        <span class="hidden-xs-down">Витрины</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#splits" role="tab" aria-selected="true">
                        <span class="hidden-sm-up"><i class="ti-bookmark-alt"></i></span>
                        <span class="hidden-xs-down">Сплиты</span>
                    </a>
                </li>
            </ul>
        
            <div class="tab-content ">
                
                <div id="general_view" class="tab-pane active" role="tabpanel">
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
                
                <div id="shop_view" class="tab-pane" role="tabpanel">
                    <form class="" method="POST" >
                        <div class="card">
                            <div class="card-body">
                                {foreach $href_keys as $link_type => $client_types}
                                    {foreach $client_types as $client_type => $title}
                                        {if isset($href_data["{$link_type}:{$client_type}"])}
                                        <div class="row">
                                            <div class="col-12 col-md-12">
                                                <h3 class="box-title">
                                                    {$title}
                                                </h3>
                                                <div class="form-group mb-3">
                                                    <div class="row pb-2">
                                                        <div class="col-12 col-md-12">
                                                            <input
                                                                class="form-control"
                                                                name="{$link_type}:{$client_type}:{$href_data["{$link_type}:{$client_type}"]->id}"
                                                                value="{$href_data["{$link_type}:{$client_type}"]->href}"
                                                                type="text"
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {/if}
                                    {/foreach}
                                {/foreach}
                                <div class="col-12 grid-stack-item" data-gs-x="0" data-gs-y="0" data-gs-width="12">
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-success"> <i class="fa fa-check"></i> Сохранить</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                
                    </form>
                </div>
                
                <div id="splits" class="tab-pane" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Настройка токенов</h4>
                            <div class="row">
                                <div class="col-12 col-md-4 col-lg-6 align-self-center">
                                    <button class="btn float-right hidden-sm-down btn-success js-open-add-token-modal">
                                        <i class="mdi mdi-plus-circle"></i> Добавить
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive m-t-40">
                                <div class="dataTables_wrapper container-fluid dt-bootstrap4 no-footer">
                                    <table id="config-tokens-table" data-paging="false" class="table display table-striped dataTable">
                                        <thead>
                                        <tr>
                                            <th><span class="text-muted">id</span></th>
                                            <th>Название</th>
                                            <th>Токен</th>
                                            <th>Тип</th>
                                            <th>Состояние</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody id="table-tokens-body">

                                        {foreach $tokens as $token}
                                        <tr class="js-token">
                                            <td>
                                                <div class="js-visible-view js-text-id">
                                                    {$token->id}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    {$token->id}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-token_name">
                                                    {$token->name}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="text" class="form-control form-control-sm" name="token_name" value="{$token->name|escape}" />
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-token_body">
                                                    {$token->token|truncate:70}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <textarea class="form-control form-control-sm" name="token_body">{$token->token}</textarea>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-token_type">
                                                    {if $token->app == 'bonon-pk'}
                                                    ПК
                                                    {elseif $token->app == 'bonon-nk-acc'}
                                                    НК из ЛК
                                                    {else}
                                                    НК
                                                    {/if}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <select name="token_type" class="form-control input-sm">
                                                        <option value="bonon-nk" {if $token->app == 'bonon-nk'}selected{/if}>НК</option>
                                                        <option value="bonon-nk-acc" {if $token->app == 'bonon-nk-acc'}selected{/if}>НК из ЛК</option>
                                                        <option value="bonon-pk" {if $token->app == 'bonon-pk'}selected{/if}>ПК</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-token_state">
                                                    {if $token->enabled == '0'}Отключен{else}Включен{/if}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <select name="token_state" class="form-control input-sm">
                                                        <option value="0" {if $token->enabled == '0'}selected{/if}>Отключен</option>
                                                        <option value="1" {if $token->enabled == '1'}selected{/if}>Включен</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td class="text-right">
                                                <div class="js-visible-view">
                                                    <a href="#" class="text-info js-edit-token" title="Редактировать"><i class=" fas fa-edit"></i></a>
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <a href="#" class="text-success js-confirm-edit-token" title="Сохранить"><i class="fas fa-check-circle"></i></a>
                                                    <a href="#" class="text-danger js-cancel-edit-token" title="Отменить"><i class="fas fa-times-circle"></i></a>
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

<div id="modal_add_token" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title">Добавить токен</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_token">
                    <div class="alert" style="display:none"></div>

                    <div class="form-group">
                        <label for="token_name" class="control-label text-white">Название токена</label>
                        <input type="text" class="form-control" name="token_name" id="token_name" value="" />
                    </div>

                    <div class="form-group">
                        <label for="token_body" class="control-label text-white">Токен</label>
                        <textarea class="form-control" name="token_body" id="token_body"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="token_type" class="control-label text-white">Тип токена</label>
                        <select name="token_type" class="form-control input-sm">
                            <option value="bonon-nk">НК</option>
                            <option value="bonon-nk-acc">НК из ЛК</option>
                            <option value="bonon-pk">ПК</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="token_state" class="control-label text-white">Состояние токена</label>
                        <select name="token_state" class="form-control input-sm">
                            <option value="0">отключен</option>
                            <option value="1">включен</option>
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