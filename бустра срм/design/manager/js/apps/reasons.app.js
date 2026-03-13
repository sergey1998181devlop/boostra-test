function ReasonsApp()
{
    var app = this;            
    
    var _init_events = function(){
        
        app.type = $('#page_type').val();
        
        // редактирование записи
        $(document).on('click', '.js-edit-item', function(e){
            e.preventDefault();
            
            var $item = $(this).closest('.js-item');
            
            $item.find('.js-visible-view').hide();
            $item.find('.js-visible-edit').fadeIn();
        });
        
        // Удаление записи
        $(document).on('click', '.js-delete-item', function(e){
            e.preventDefault();
            
            var $item = $(this).closest('.js-item');
            
            var _id = $item.find('[name=id]').val();
            var _admin_name = $item.find('[name=admin_name]').val();
            
            Swal.fire({
                text: "Вы действительно хотите удалить Причину отказа `"+_admin_name+"`?",
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
                              text: 'Причина отказа удалена!',
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
            var _admin_name = $item.find('[name=admin_name]').val();
            var _client_name = $item.find('[name=client_name]').val();
            var _maratory = $item.find('[name=maratory]').val();
            
            $.ajax({
                type: 'POST',
                data: {
                    action: 'update',
                    id: _id,
                    admin_name: _admin_name,
                    client_name: _client_name,
                    maratory: _maratory,
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
                        $item.find('[name=admin_name]').val(resp.admin_name);
                        $item.find('.js-text-admin-name').html(resp.admin_name);
                        $item.find('[name=client_name]').val(resp.client_name);
                        $item.find('.js-text-client-name').html(resp.client_name);
                        $item.find('[name=maratory]').val(resp.maratory);
                        $item.find('.js-text-maratory').html(resp.maratory);

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
            $('#modal_add_item').find('[name=admin_name]').val('');
            $('#modal_add_item').find('[name=client_name]').val('');
            $('#modal_add_item').find('[name=maratory]').val('');
            
            $('#modal_add_item').modal();
            
            $('#modal_add_item').find('[name=admin_name]').focus();
        });
        
        // Сохранение новой записи
        $(document).on('submit', '#form_add_item', function(e){
            e.preventDefault();
            
            var $form = $(this);
            
            var _admin_name = $form.find('[name=admin_name]').val();
            var _client_name = $form.find('[name=client_name]').val();
            var _maratory = $form.find('[name=maratory]').val();
            
            $.ajax({
                type: 'POST',
                data: {
                    action: 'add',
                    admin_name: _admin_name,
                    client_name: _client_name,
                    maratory: _maratory,
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
                        new_row += '<div class="js-visible-view js-text-admin-name">'+resp.admin_name+'</div>';
                        new_row += '<div class="js-visible-edit" style="display:none">';
                        new_row += '<input type="hidden" name="id" value="'+resp.id+'" />';
                        new_row += '<input type="text" class="form-control form-control-sm" name="admin_name" value="'+resp.admin_name+'" />';
                        new_row += '</div>';
                        new_row += '</td>';
                        new_row += '<td>';
                        new_row += '<div class="js-visible-view js-text-client-name">'+resp.client_name+'</div>';
                        new_row += '<div class="js-visible-edit" style="display:none">';
                        new_row += '<input type="text" class="form-control form-control-sm" name="client_name" value="'+resp.client_name+'" />';
                        new_row += '</div>';
                        new_row += '</td>';
                        if (app.type == 'reject')
                        {
                            new_row += '<td>';
                            new_row += '<div class="js-visible-view js-text-maratory">'+resp.maratory+'</div>';
                            new_row += '<div class="js-visible-edit" style="display:none">';
                            new_row += '<input type="text" class="form-control form-control-sm" name="maratory" value="'+resp.maratory+'" />';
                            new_row += '</div>';
                            new_row += '</td>';
                        }
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
                            text: 'Причина отказа "'+resp.admin_name+'" добавлена!',
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
    new ReasonsApp();
})
