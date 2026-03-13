{$meta_title = 'Справочник тикет систем' scope=parent}

{capture name='page_styles'}
    <link href="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css"
          rel="stylesheet"/>
    <link rel="stylesheet" type="text/css"
          href="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.css">
    <link rel="stylesheet" type="text/css"
          href="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css">
    <style>
        .js-item {
            cursor: pointer;
        }
    </style>
{/capture}

{capture name='page_scripts'}
    <script src="design/{$settings->theme|escape}/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="design/{$settings->theme|escape}/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.13.0/Sortable.min.js"></script>
    <script src="design/{$settings->theme|escape}/js/apps/tickets_guide.app.js"></script>
{/capture}

<div class="page-wrapper">
    <div class="container-fluid">

        <a href="#" data-toggle="collapse" data-target="#demo">
                Справочник тикет систем
            <i class="mdi mdi-arrow-expand"></i>
        </a>

        <div id="demo" class="collapse">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-row  justify-content-between">
                                <div>
                                    <h4 class="card-title">Справочник тикет систем</h4>

                                </div>
                                <div>
                                    <button class="btn float-right hidden-sm-down btn-success js-open-add-modal">
                                        <i class="mdi mdi-plus-circle"></i> Добавить
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                                    <table id="config-table" class="table display dataTable">
                                        <thead>
                                        <tr>
                                            <th class="w-25">Название:</th>
                                            <th class="w-25">Описание:</th>
                                            <th></th>
                                        </tr>
                                        </thead>

                                        <tbody id="table-body">
                                        {foreach $tickets as $ticket}
                                            <tr class="js-item">
                                                <td>
                                                    <div class="js-visible-view js-text-ticket-subject">
                                                        {$ticket->subject}
                                                    </div>
                                                    <div class="js-visible-edit" style="display:none">
                                                        <input type="hidden" name="id" value="{$ticket->id}"/>
                                                        <input type="text" class="form-control form-control-sm"
                                                               name="subject" value="{$ticket->subject|escape}"/>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="js-visible-view js-text-ticket-description">
                                                        {$ticket->description|escape}
                                                    </div>
                                                    <div class="js-visible-edit" style="display:none">
                                                        <input type="hidden" name="id" value="{$ticket->id}"/>
                                                        <input type="text" class="form-control form-control-sm"
                                                               name="description" value="{$ticket->description|escape}"/>
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <div class="js-visible-view">
                                                        <a href="#" class="text-info js-edit-item" title="Редактировать">
                                                            <button class="btn btn-warning">Редактировать</button>
                                                        </a>
                                                        <a href="#" class="text-danger js-delete-item" title="Удалить">
                                                            <button class="btn btn-danger">Удалить</button>
                                                        </a>
                                                    </div>
                                                    <div class="js-visible-edit text-right" style="display:none">
                                                        <a href="#" class="text-success js-confirm-edit-item"
                                                           title="Сохранить">
                                                            <button class="btn btn-success">Сохранить</button>
                                                        </a>
                                                        <a href="#" class="text-danger js-cancel-edit-item"
                                                           title="Отменить">
                                                            <button class="btn btn-danger">Отмена</button>
                                                        </a>
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
            </div>        </div>


    </div>

    {include file='footer.tpl'}
</div>
<div id="modal_add_item" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog"
     aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Создать новую тему</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_item" enctype="multipart/form-data">
                    <div class="alert" style="display:none"></div>
                    <div class="form-group">
                        <label for="subject" class="control-label">Название:</label>
                        <input type="text" class="form-control" name="subject" id="subject" required/>
                    </div>
                    <div class="form-group">
                        <label for="description" class="control-label">Описание:</label>
                        <textarea class="form-control" name="description" id="description" required></textarea>
                    </div>
                    <div class="form-action">
                        <button type="submit" class="btn btn-success waves-effect waves-light">Ок</button>
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal">Отмена</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
