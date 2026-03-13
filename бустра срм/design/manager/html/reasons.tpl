{if $type == 'reject'}
    {$meta_title = 'Причины отказа' scope=parent}
{elseif $type == 'waiting'}
    {$meta_title = 'Причины ожидания' scope=parent}
{/if}
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
    
    <script src="design/{$settings->theme|escape}/js/apps/reasons.app.js"></script>
    
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
                    {if $type == 'reject'}
                        Причины отказа
                    {elseif $type == 'waiting'}
                        Причины ожидания
                    {/if}
                    <input type="hidden" name="page_type" id="page_type" value="{$type}" />
                </h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Главная</a></li>
                    <li class="breadcrumb-item active">
                        {if $type == 'reject'}
                            Причины отказа
                        {elseif $type == 'waiting'}
                            Причины ожидания
                        {/if}                    
                    </li>
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
                                <table id="config-table" class="table display table-striped dataTable">
                                    <thead>
                                        <tr>
                                            <th class="">ID</th>
                                            <th class="">Для&nbsp;CRM</th>
                                            <th class="">Для клиента</th>
                                            {if $type == 'reject'}
                                            <th class="">Мораторий<span class="text-warning">*</span></th>
                                            {/if}
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="table-body">
                                        
                                        {foreach $reasons as $reason}
                                        <tr class="js-item">
                                            <td>
                                                <div class="js-text-id">
                                                    {$reason->id}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-admin-name">
                                                    {$reason->admin_name|escape}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="hidden" name="id" value="{$reason->id}" />
                                                    <input type="text" class="form-control form-control-sm" name="admin_name" value="{$reason->admin_name|escape}" />
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-client-name">
                                                    {$reason->client_name|escape}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="text" class="form-control form-control-sm" name="client_name" value="{$reason->client_name|escape}" />
                                                </div>
                                            </td>
                                            {if $type == 'reject'}
                                            <td>
                                                <div class="js-visible-view js-text-maratory">
                                                    {$reason->maratory|escape}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="text" class="form-control form-control-sm" name="maratory" value="{$reason->maratory|escape}" />
                                                </div>
                                            </td>
                                            {/if}
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
                                {if $type == 'reject'}
                                <span class="info text-warning">* при значении 999 ставится бессрочный мораторий</span>
                                {/if}
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
                <h4 class="modal-title">Добавить причину отказа</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_item">
                
                    <div class="alert" style="display:none"></div>
                    
                    <div class="form-group">
                        <label for="admin_name" class="control-label">Для администратора:</label>
                        <input type="text" class="form-control" name="admin_name" id="admin_name" value="" />
                    </div>
                    <div class="form-group">
                        <label for="client_name" class="control-label">Для клиента:</label>
                        <input type="text" class="form-control" name="client_name" id="client_name" value="" />
                    </div>
                    {if $type == 'reject'}
                    <div class="form-group">
                        <label for="maratory" class="control-label">Мораторий, дней:</label>
                        <input type="text" class="form-control" name="maratory" id="maratory" value="" />
                        <span class="info text-warning">* при значении 999 ставится бессрочный мораторий</span>
                    </div>
                    {/if}
                    <div class="form-action">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                        <button type="submit" class="btn btn-success waves-effect waves-light">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>