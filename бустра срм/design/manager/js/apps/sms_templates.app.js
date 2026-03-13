function SmsTemplatesApp()
{
    var app = this;            
    
    var _init_events = function(){
        
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
            var _name = $item.find('[name=name]').val();
            
            Swal.fire({
                text: "Вы действительно хотите удалить шаблон"+_name+"?",
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
                              text: 'Шаблон удален!',
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
            var _template = $item.find('[name=template]').val();
            var _type = $item.find('[name=type]').val() || 'from_tech';
            var _check_limit = $item.find('[name=check_limit]').is(':checked') ? 1 : 0;
            var _status = $item.find('[name=status]').is(':checked') ? 1 : 0;

            $.ajax({
                type: 'POST',
                data: {
                    action: 'update',
                    id: _id,
                    name: _name,
                    type: _type,
                    template: _template,
                    check_limit: _check_limit,
                    status: _status,
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
                        $item.find('[name=template]').val(resp.template);
                        $item.find('.js-text-template').html(resp.template);
                        $item.find('[name=type]').val(resp.type);

                        $item.find('.js-text-status').html(resp.status ? 'Активен' : 'Неактивен');
                        
                        $item.find('.js-text-type').html($item.find('[name=type] [value='+resp.type+']').text());

                        $item.find('.js-visible-edit').hide();
                        $item.find('.js-visible-view').fadeIn();
                        
                        if (resp.check_limit)
                            $item.find('.js-text-limit').html('Да');
                        else
                            $item.find('.js-text-limit').html('Нет');

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
            $('#modal_add_item').find('[name=template]').val('');

            var templateType = $(this).data('template-type');

            // Если это from_tech шаблон
            if (templateType === 'from_tech') {
                $('#modal_add_item').find('[name=type]').val(templateType);
                $('#modal_add_item').find('.js-type-select-group').hide();
            } else {
                $('#modal_add_item').find('[name=type]').val('');
                $('#modal_add_item').find('.js-type-select-group').show();
            }
            
            $('#modal_add_item').modal();
            
            $('#modal_add_item').find('[name=name]').focus();
        });
        
        // Сохранение новой записи
        $(document).on('submit', '#form_add_item', function(e){
            e.preventDefault();
            
            var $form = $(this);
            
            var _name = $form.find('[name=name]').val();
            var _type = $form.find('[name=type]').val();
            var _template = $form.find('[name=template]').val();
            var _check_limit = $form.find('[name=check_limit]').is(':checked') ? 1 : 0;
            
            $.ajax({
                type: 'POST',
                data: {
                    action: 'add',
                    name: _name,
                    type: _type,
                    template: _template,
                    check_limit: _check_limit,
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
                        new_row += '<input type="text" class="form-control form-control-sm" name="name" value="'+resp.name+'" />';
                        new_row += '</div>';
                        new_row += '</td>';
                        new_row += '<td>';
                        new_row += '<div class="js-visible-view js-text-template">'+resp.template+'</div>';
                        new_row += '<div class="js-visible-edit" style="display:none">';
                        new_row += '<input type="text" class="form-control form-control-sm" name="template" value="'+resp.template+'" />';
                        new_row += '</div>';
                        new_row += '</td>';

                        if (resp.type !== 'from_tech') {
                            new_row += '<td>';
                            new_row += '<div class="js-visible-view js-text-type">'+resp.type+'</div>';
                            new_row += '<div class="js-visible-edit" style="display:none">';
                            new_row += '<input type="text" class="form-control form-control-sm" name="type" value="'+resp.type+'" />';
                            new_row += '</div>';
                            new_row += '</td>';

                            new_row += '<td>';
                            new_row += '<div class="js-visible-view js-text-limit">';
                            if (resp.check_limit)
                                new_row += 'Да';
                            else
                                new_row += 'Нет';
                            new_row += '</div>';
                            new_row += '<div class="js-visible-edit" style="display:none">'
                            new_row += '<input name="check_limit" type="checkbox" value="1" '+(resp.check_limit ? 'checked' : '')+'/>'
                            new_row += '</div>';
                            new_row += '</td>';
                        } else {
                            new_row += '<td>';
                            new_row += '<div class="js-visible-view js-text-status">'+(parseInt(resp.status) ? 'Активен' : 'Неактивен')+'</div>';
                            new_row += '<div class="js-visible-edit" style="display:none">';
                            new_row += '<input name="status" type="checkbox" value="1" '+(parseInt(resp.status) ? 'checked' : '')+' />';
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

                        if (resp.type === 'from_tech') {
                            $('.js-fromtech-templates tbody').append(new_row);
                        } else {
                            $('#table-body').append(new_row);
                        }
                                                        
                        $('#modal_add_item').modal('hide');
                        Swal.fire({
                            timer: 5000,
                            text: 'Шаблон сообщения "'+resp.name+'" добавлен!',
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
    new SmsTemplatesApp();
})
