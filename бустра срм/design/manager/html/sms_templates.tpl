{$meta_title = 'Шаблоны сообщений' scope=parent}

{capture name='page_styles'}
<link href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.css">
<link rel="stylesheet" type="text/css" href="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css">
<style>
    .js-text-admin-name,
    .js-text-client-name {
    }
</style>
{/capture}

{capture name='page_scripts'}

    <script src="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    
    <script src="design/{$settings->theme|escape}/js/apps/sms_templates.app.js"></script>
    
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
                <h3 class="text-themecolor mb-0 mt-0">Шаблоны сообщений</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Главная</a></li>
                    <li class="breadcrumb-item active">Шаблоны сообщений</li>
                </ol>
            </div>
            <div class="col-md-6 col-4 align-self-center">
                <button class="btn float-right hidden-sm-down btn-success js-open-add-modal">
                    <i class="mdi mdi-plus-circle"></i> Добавить
                </button>
                
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
            
            <div class="card">
                    <div class="card-body">
                        <h4 class="card-title"></h4>
                        <h6 class="card-subtitle"></h6>
                        <div class="table-responsive m-t-40">
                            <div class="dataTables_wrapper container-fluid dt-bootstrap4 no-footer">
                                <form method="get" action="{$smarty.server.REQUEST_URI|regex_replace:'/\?.*$/':''}">
                                    <div class="form-group">
                                        <label for="site_id" class="control-label">Установить site_id:</label>
                                        <div class="input-group mb-3">
                                            <select type="text"
                                                    class="form-control"
                                                   aria-label="Recipient's username"
                                                   aria-describedby="button-addon2"
                                                   name="site_id"
                                                   id="site_id"
                                            >
                                                <option value="">Все сайты</option>
                                                {foreach $site_ids as $id}
                                                    <option value="{$id}" {if $site_id == $id}selected{/if}>{$id}</option>
                                                {/foreach}
                                            </select>
                                            <div class="input-group-append">
                                                <button class="btn btn-info" type="submit" id="button-addon2"><i class="ti-save"></i> Сохранить</button>
                                            </div>
                                        </div>
                                        <small id="emailHelp" class="form-text text-muted">Установи для какого сайта отображать и изменять шаблоны</small>
                                    </div>
                                </form>
                                <table id="config-table" class="table display table-striped dataTable">
                                    <thead>
                                        <tr>
                                            <th class="">ID</th>
                                            <th class="">Название</th>
                                            <th class="">Шаблон</th>
                                            <th class="">Тип</th>
                                            <th>Лимит</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="table-body">
                                        
                                        {foreach $sms_templates as $st}
                                            {if $st->type != 'from_tech'}
                                        <tr class="js-item">
                                            <td>
                                                <div class="js-text-id">
                                                    {$st->id}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-name">
                                                    {$st->name|escape}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="hidden" name="id" value="{$st->id}" />
                                                    <input type="text" class="form-control form-control-sm" name="name" value="{$st->name|escape}" />
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-template">
                                                    {$st->$template_value_field|escape}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="text" class="form-control form-control-sm" name="template" value="{$st->$template_value_field|escape}" />
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-type">
                                                    {$template_types[$st->type]|escape}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <select name="type" class="form-control form-control-sm">
                                                        {foreach $template_types as $ttk => $ttv}
                                                        <option value="{$ttk}" {if $ttk == $st->type}selected{/if}>{$ttv|escape}</option>
                                                        {/foreach}
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-limit">
                                                    {if $st->check_limit}Да
                                                    {else}Нет{/if}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input name="check_limit" type="checkbox" value="1" {if $st->check_limit}checked{/if} />
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
                                            {/if}
                                        {/foreach}
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Шаблоны From-tech</h4>
                        <button class="btn btn-success float-right js-open-add-modal" data-template-type="from_tech">
                            <i class="mdi mdi-plus-circle"></i> Добавить шаблон
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped js-fromtech-templates">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Шаблон</th>
                                    <th>Статус</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $sms_templates as $st}
                                    {if $st->type == 'from_tech'}
                                        <tr class="js-item">
                                            <td><div class="js-text-id">{$st->id}</div></td>
                                            <td>
                                                <div class="js-visible-view js-text-name">{$st->name|escape}</div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="hidden" name="id" value="{$st->id}" />
                                                    <input type="text" class="form-control form-control-sm" name="name" value="{$st->name|escape}" />
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-template">{$st->$template_value_field|escape}</div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="text" class="form-control form-control-sm" name="template" value="{$st->$template_value_field|escape}" />
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-status">
                                                    {if $st->status}Активен{else}Неактивен{/if}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input name="status" type="checkbox" value="1" {if $st->status}checked{/if} />
                                                </div>
                                            </td>
                                            <td class="text-right">
                                                <div class="js-visible-view">
                                                    <a href="#" class="text-info js-edit-item" title="Редактировать"><i class="fas fa-edit"></i></a>
                                                    <a href="#" class="text-danger js-delete-item" title="Удалить"><i class="far fa-trash-alt"></i></a>
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <a href="#" class="text-success js-confirm-edit-item" title="Сохранить"><i class="fas fa-check-circle"></i></a>
                                                    <a href="#" class="text-danger js-cancel-edit-item" title="Отменить"><i class="fas fa-times-circle"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    {/if}
                                {/foreach}
                                </tbody>
                            </table>
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
                <h4 class="modal-title">Добавить шаблон сообщения</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_item">
                
                    <div class="alert" style="display:none"></div>
                    
                    <div class="form-group">
                        <label for="name" class="control-label">Название:</label>
                        <input type="text" class="form-control" name="name" id="name" value="" />
                    </div>
                    <div class="form-group">
                        <label for="template" class="control-label">Сообщение:</label>
                        <input type="text" class="form-control" name="template" id="template" value="" />
                    </div>
                    <div class="form-group js-type-select-group">
                        <input type="hidden" name="type" value="">
                        <label for="type" class="control-label">Тип:</label>
                        <select name="type" class="form-control" id="type">
                            {foreach $template_types as $ttk => $ttv}
                            <option value="{$ttk}">{$ttv|escape}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group">
                        <div class="custom-checkbox">
                            <label for="limit" class="control-label ">Проверять лимит:</label>
                            <input type="checkbox" class="form-control-checkbox" name="check_limit" id="limit" value="1" />                        
                        </div>
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