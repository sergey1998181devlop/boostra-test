{$meta_title = 'Причины закрытия тикетов' scope=parent}

{capture name='page_styles'}
<link href="design/manager/assets/plugins/Magnific-Popup-master/dist/magnific-popup.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="design/manager/assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.css">
<link rel="stylesheet" type="text/css" href="design/manager/assets/plugins/datatables.net-bs4/css/responsive.dataTables.min.css">

<!-- Color picker plugins css -->
<link href="design/manager/assets/plugins/jquery-asColorPicker-master/dist/css/asColorPicker.css" rel="stylesheet">
    
<style>
    .js-text-admin-name,
    .js-text-client-name {
    }
    .color-badge {
        display:inline-block;
        width:64px;
        height:24px;
        margin-right:20px;
        vertical-align:top;
    }
</style>
{/capture}

{capture name='page_scripts'}

    <script src="design/manager/assets/plugins/Magnific-Popup-master/dist/jquery.magnific-popup.min.js"></script>
    <script src="design/manager/assets/plugins/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="design/manager/assets/plugins/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
    

    <!-- Plugin JavaScript -->
    <script src="design/manager/assets/plugins/moment/moment.js?v=1.1"></script>
    <script src="design/manager/assets/plugins/jquery-asColor/dist/jquery-asColor.js?v=1.1"></script>
    <script src="design/manager/assets/plugins/jquery-asColorPicker-master/dist/jquery-asColorPicker.min.js?v=1.1"></script>
    
    <script>
    function TicketReasonsApp()
    {
        var app = this;            
        
        var _init_events = function(){
            
            // редактирование записи
            $(document).on('click', '.js-edit-item', function(e){
                e.preventDefault();
                
                var $item = $(this).closest('.js-item');
                
                $item.find('.js-visible-view').hide();
                $item.find('.js-visible-edit').fadeIn();
                
                _init_colorpicker();
            });
            
            // Удаление записи
            $(document).on('click', '.js-delete-item', function(e){
                e.preventDefault();
                
                var $item = $(this).closest('.js-item');
                
                var _id = $item.find('[name=id]').val();
                var _name = $item.find('[name=name]').val();
                
                Swal.fire({
                    text: "Вы действительно хотите удалить причину"+_name+"?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Да, удалить!",
                    cancelButtonText: "Отмена",
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                    },
                    allowOutsideClick: () => !Swal.isLoading()
    
                }).then((result) => {
    
                    if (result.value) 
                    {
                        $.ajax({
                            type: 'POST',
                            data: {
                                action: 'delete',
                                id: _id
                            },
                            success: function(){
    
                                $item.remove();
    
                                Swal.fire({
                                  timer: 5000,
                                  text: 'Причина удалена!',
                                  type: 'success',
                                });                                
                            }
                        })
                    }
                });
            });
            
            
            // Сохранение редактируемой записи
            $(document).on('click', '.js-confirm-edit-item', function(e){
                e.preventDefault();
                
                var $item = $(this).closest('.js-item');
                
                var _id = $item.find('[name=id]').val();
                var _name = $item.find('[name=name]').val();
                
                $.ajax({
                    type: 'POST',
                    data: {
                        action: 'update',
                        id: _id,
                        name: _name,
                    },
                    success: function(resp){
                        if (!!resp.error)
                        {
                            Swal.fire({
                                text: resp.error,
                                type: 'error',
                            });                        
                        }
                        else
                        {
                            $item.find('[name=name]').val(resp.name);
                            $item.find('.js-text-name').html(resp.name);
                            
                            $item.find('.js-visible-edit').hide();
                            $item.find('.js-visible-view').fadeIn();
    
                        }
                    }
                });
                
            });
            
            // Отмена редактирования записи
            $(document).on('click', '.js-cancel-edit-item', function(e){
                e.preventDefault();
                
                var $item = $(this).closest('.js-item');
                
                $item.find('.js-visible-edit').hide();
                $item.find('.js-visible-view').fadeIn();
            });
            
            // Открытие окна для добавления
            $(document).on('click', '.js-open-add-modal', function(e){
                e.preventDefault();
                
                $('#modal_add_item').find('.alert').hide();
                $('#modal_add_item').find('[name=name]').val('');
                
                $('#modal_add_item').modal();
                
                $('#modal_add_item').find('[name=name]').focus();
            
                _init_colorpicker();
            });
            
            // Сохранение новой записи
            $(document).on('submit', '#form_add_item', function(e){
                e.preventDefault();
                
                var $form = $(this);
                
                var _name = $form.find('[name=name]').val();
                
                $.ajax({
                    type: 'POST',
                    data: {
                        action: 'add',
                        name: _name,
                    },
                    beforeSend: function(){
                        
                    },
                    success: function(resp){
                        if (!!resp.error)
                        {
                            $form.find('.alert').removeClass('alert-success').addClass('alert-danger').html(resp.error).fadeIn();
                        }
                        else
                        {
                            var new_row = '<tr class="js-item">';
                            new_row += '<td><div class="js-text-id">'+resp.id+'</div></td>';
                            new_row += '<td>';
                            new_row += '<div class="js-visible-view js-text-name">'+resp.name+'</div>';
                            new_row += '<div class="js-visible-edit" style="display:none">';
                            new_row += '<input type="hidden" name="id" value="'+resp.id+'" />';
                            new_row += '<input type="text" class="form-control form-control" name="name" value="'+resp.name+'" />';
                            new_row += '</div>';
                            new_row += '</td>';
    
                            new_row += '<td class="text-right">';
                            new_row += '<div class="js-visible-view">';
                            new_row += '<a href="#" class="text-info js-edit-item" title="Редактировать"><i class=" fas fa-edit"></i></a> ';
                            new_row += '<a href="#" class="text-danger js-delete-item" title="Удалить"><i class="far fa-trash-alt"></i></a>';
                            new_row += '</div>';
                            new_row += '<div class="js-visible-edit" style="display:none">';
                            new_row += '<a href="#" class="text-success js-confirm-edit-item" title="Сохранить"><i class="fas fa-check-circle"></i></a> ';
                            new_row += '<a href="#" class="text-danger js-cancel-edit-item" title="Отменить"><i class="fas fa-times-circle"></i></a>';
                            new_row += '</div>';
                            new_row += '</td>';
                            new_row += '</tr>';
                            
                            $('#table-body').append(new_row);
                            
                            $('#modal_add_item').modal('hide');
                            Swal.fire({
                                timer: 5000,
                                text: 'Причина "'+resp.name+'" добавлена!',
                                type: 'success',
                            });                                
    
                        }
                    }
                })
            });
        };
        
        ;(function(){
            _init_events();
        })();
    };
    $(function(){
        new TicketReasonsApp();
    })
    
    </script>
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
                <h3 class="text-themecolor mb-0 mt-0">Причины закрытия тикетов</h3>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="javascript:void(0)">Главная</a></li>
                    <li class="breadcrumb-item active">Причины закрытия тикетов</li>
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
                        <h4 class="card-title">Причины закрытия тикетов</h4>
                        <div class="table-responsive">
                            <div class="dataTables_wrapper container-fluid dt-bootstrap4 no-footer">    
                                <table id="config-table" class="table display table-striped dataTable">
                                    <thead>
                                        <tr>
                                            <th class="">ID</th>
                                            <th class="">Название</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="table-body">
                                        
                                        {foreach $items as $item}
                                        <tr class="js-item">
                                            <td>
                                                <div class="js-text-id">
                                                    {$item->id}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="js-visible-view js-text-name">
                                                    {$item->name|escape}
                                                </div>
                                                <div class="js-visible-edit" style="display:none">
                                                    <input type="hidden" name="id" value="{$item->id}" />
                                                    <input type="text" class="form-control form-control" name="name" value="{$item->name|escape}" />
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
                <h4 class="modal-title">Добавить новую причину закрытия тикетов</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="form_add_item">
                
                    <div class="alert" style="display:none"></div>
                    
                    <div class="form-group">
                        <label for="name" class="control-label">Название:</label>
                        <input type="text" class="form-control" name="name" id="name" value="" />
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